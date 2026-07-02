<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IntegrationController extends Controller
{
    // GET /integrations/fx/convert?amount=123.45&from=EUR&to=RSD
    public function fxConvert(Request $r, CurrencyService $fx)
    {
        $data = $r->validate([
            'amount' => 'required|numeric|min:0',
            'from'   => 'required|string|size:3',
            'to'     => 'required|string|size:3',
        ]);

        $converted = $fx->convert((float)$data['amount'], $data['from'], $data['to']);

        return response()->json([
            'amount'     => (float)$data['amount'],
            'from'       => strtoupper($data['from']),
            'to'         => strtoupper($data['to']),
            'converted'  => $converted,
            'rate'       => $fx->rate($data['from'], $data['to']),
            'source'     => 'exchangerate.host'
        ]);
    }

    // GET /integrations/avatar/url?email=a@b.com&size=128
    public function avatarUrl(Request $r)
    {
        $data = $r->validate([
            'email' => 'required|email',
            'size'  => 'nullable|integer|min:24|max:512',
            'default' => 'nullable|string', // npr. identicon, retro, mp
        ]);

        $size = $data['size'] ?? 128;
        $def  = $data['default'] ?? 'identicon';

        $hash = md5(strtolower(trim($data['email'])));
        $url  = "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$def}";

        return response()->json([
            'email'  => $data['email'],
            'avatar' => $url,
            'fallback' => "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode(Str::before($data['email'], '@')),
            'source' => 'gravatar.com | dicebear.com'
        ]);
    }

    // (opciono) redirect direktno na sliku avatara (HTTP 302)
    // GET /integrations/avatar/image?email=a@b.com&size=128
    public function avatarRedirect(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'size'  => 'nullable|integer|min:24|max:512',
        ]);
        $size = $r->integer('size', 128);
        $hash = md5(strtolower(trim($r->input('email'))));
        $url  = "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
        return redirect()->away($url, Response::HTTP_FOUND);
    }
}
