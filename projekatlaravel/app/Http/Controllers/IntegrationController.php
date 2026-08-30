<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IntegrationController extends Controller
{
    // GET /api/integrations/fx/convert?amount=123.45&from=EUR&to=RSD
    public function fxConvert(Request $r)
    {
        $data = $r->validate([
            'amount' => 'required|numeric|min:0',
            'from'   => 'required|string|size:3',
            'to'     => 'required|string|size:3',
        ]);

        $from   = strtoupper($data['from']);
        $to     = strtoupper($data['to']);
        $amount = (float) $data['amount'];

        if ($from === $to) {
            return response()->json([
                'amount'    => $amount,
                'from'      => $from,
                'to'        => $to,
                'converted' => $amount,
                'rate'      => 1.0,
                'source'    => 'self',
            ]);
        }

        // === POKUŠAJ 1: Frankfurter (bez ključa) ===
        // Primer: https://api.frankfurter.app/latest?amount=123.45&from=EUR&to=RSD
        // Odgovor: { "amount":123.45,"base":"EUR","date":"...","rates":{"RSD": <konvertovano> } }
        $ff = Http::timeout(10)->retry(2, 200)
            ->get('https://api.frankfurter.app/latest', [
                'amount' => $amount,
                'from'   => $from,
                'to'     => $to,
            ]);

        if ($ff->ok()) {
            $j = $ff->json();
            $rates = $j['rates'] ?? [];
            $converted = $rates[$to] ?? null;

            if (is_numeric($converted)) {
                // Iz Frankfurtera dobijamo već preračunat iznos; kurs = converted / amount
                $rate = $amount > 0 ? ((float)$converted / $amount) : null;

                return response()->json([
                    'amount'    => (float)$j['amount'],
                    'from'      => strtoupper($j['base'] ?? $from),
                    'to'        => $to,
                    'converted' => round((float)$converted, 2),
                    'rate'      => is_numeric($rate) ? round((float)$rate, 6) : null,
                    'source'    => 'frankfurter.app',
                ]);
            }
        }

        // === POKUŠAJ 2: open.er-api.com (bez ključa) ===
        // Primer: https://open.er-api.com/v6/latest/EUR -> {"rates":{"RSD": 117.xx, ...}}
        $fb = Http::timeout(10)->retry(2, 200)
            ->get("https://open.er-api.com/v6/latest/{$from}");

        if ($fb->ok()) {
            $j = $fb->json();
            $rate = $j['rates'][$to] ?? null;

            if (is_numeric($rate)) {
                $rate = (float)$rate;
                $converted = round($amount * $rate, 2);

                return response()->json([
                    'amount'    => $amount,
                    'from'      => $from,
                    'to'        => $to,
                    'converted' => $converted,
                    'rate'      => round($rate, 6),
                    'source'    => 'open.er-api.com',
                ]);
            }
        }

        // Ako propadnu oba
        return response()->json([
            'message' => 'FX provider unavailable for this pair.',
            'from'    => $from,
            'to'      => $to,
        ], 502);
    }
}
