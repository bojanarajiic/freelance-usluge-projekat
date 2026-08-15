<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // POST /api/auth/forgot-password { email }
    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required','email']]);

        // Ne otkrivamo da li mejl postoji (bezbednije)
        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => __($status) === Password::RESET_LINK_SENT
                ? 'If that email exists, a reset link has been sent.'
                : 'If that email exists, a reset link has been sent.'
        ]);
    }

    // POST /api/auth/reset-password { email, token, password, password_confirmation }
    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => ['required','string'],
            'email'                 => ['required','email'],
            'password'              => ['required','string','min:8','confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Poništi sve postojeće API tokene (Sanctum) nakon reseta:
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset.']);
        }

        // npr. invalid token / email mismatch
        return response()->json(['message' => __($status)], 422);
    }
}
