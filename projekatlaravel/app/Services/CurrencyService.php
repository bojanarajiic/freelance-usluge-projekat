<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    // Vraća kurs (1 FROM = ? TO), kešira 12h
    public function rate(string $from, string $to): float
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        return Cache::remember("fx:{$from}:{$to}", now()->addHours(12), function () use ($from, $to) {
            $res = Http::timeout(5)->get('https://api.exchangerate.host/convert', [
                'from' => $from,
                'to'   => $to,
                'amount' => 1,
            ])->throw()->json();

            return (float)($res['result'] ?? 0.0);
        });
    }

    // Konvertuje iznos amount iz FROM u TO
    public function convert(float $amount, string $from, string $to): float
    {
        $rate = $this->rate($from, $to);
        return round($amount * $rate, 2);
    }
}
