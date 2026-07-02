<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // GET /reports/contracts/fx-summary?to=RSD
    // Sabira/računa statistiku nad contracts.agreed_amount konvertovanih u ciljnu valutu.
    // Ako nemaš kolonu 'currency' u contracts, tretira sve kao EUR (adjust po potrebi).
    public function contractsFxSummary(Request $r, CurrencyService $fx)
    {
        $target = strtoupper($r->query('to', 'EUR'));

        // Ako već imaš currency kolonu, povuci je; ovde pretpostavimo EUR kao izvor
        $contracts = Contract::query()->select(['id','agreed_amount'])->get();

        $converted = $contracts->map(fn($c) => $fx->convert((float)$c->agreed_amount, 'EUR', $target));

        if ($converted->isEmpty()) {
            return response()->json([
                'to' => $target, 'count' => 0, 'sum' => 0, 'avg' => 0, 'min' => 0, 'max' => 0,
                'note' => 'No contracts found'
            ]);
        }

        return response()->json([
            'to'   => $target,
            'count'=> $converted->count(),
            'sum'  => round($converted->sum(), 2),
            'avg'  => round($converted->avg(), 2),
            'min'  => round($converted->min(), 2),
            'max'  => round($converted->max(), 2),
            'source' => 'exchangerate.host'
        ]);
    }
}
