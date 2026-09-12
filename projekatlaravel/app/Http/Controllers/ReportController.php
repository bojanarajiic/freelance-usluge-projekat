<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    // GET /api/reports/contracts/fx-summary?to=EUR
    public function fxSummary(Request $r)
    {
        $to = strtoupper($r->query('to', 'EUR'));

        // Agregacija: ukupan iznos i broj ugovora po originalnoj valuti
        $sumsByCurrency = Contract::selectRaw('currency, SUM(agreed_amount) as total, COUNT(*) as contracts_count')
            ->groupBy('currency')
            ->get();

        $breakdown = [];
        $grandTotal = 0.0;

        foreach ($sumsByCurrency as $row) {
            $from   = strtoupper($row->currency);
            $amount = (float) $row->total;

            $rate      = $from === $to ? 1.0 : $this->fetchRate($from, $to);
            $converted = $rate !== null ? round($amount * $rate, 2) : null;

            if ($converted !== null) {
                $grandTotal += $converted;
            }

            $breakdown[] = [
                'currency'        => $from,
                'contracts_count' => (int) $row->contracts_count,
                'total_amount'    => round($amount, 2),
                'rate'            => $rate !== null ? round($rate, 6) : null,
                'converted'       => $converted,
            ];
        }

        return response()->json([
            'target_currency' => $to,
            'breakdown'       => $breakdown,
            'grand_total'     => round($grandTotal, 2),
        ]);
    }

    // Isti spoljni servis (i isti fallback) koji koristi IntegrationController@fxConvert
    private function fetchRate(string $from, string $to): ?float
    {
        $ff = Http::timeout(10)->retry(2, 200)
            ->get('https://api.frankfurter.app/latest', [
                'amount' => 1,
                'from'   => $from,
                'to'     => $to,
            ]);

        if ($ff->ok()) {
            $rate = $ff->json('rates.' . $to);
            if (is_numeric($rate)) {
                return (float) $rate;
            }
        }

        $fb = Http::timeout(10)->retry(2, 200)
            ->get("https://open.er-api.com/v6/latest/{$from}");

        if ($fb->ok()) {
            $rate = $fb->json('rates.' . $to);
            if (is_numeric($rate)) {
                return (float) $rate;
            }
        }

        return null;
    }
}
