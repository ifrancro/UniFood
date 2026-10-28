<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function formOlvide()
    {
        return view('auth.olvide');
    }

    public function enviarEnlace(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'No encontramos ningún usuario con ese correo electrónico.',
        ]);

        // Use Laravel's hashed, expiring tokens and request throttle.
        $status = Password::sendResetLink($request->only('email'), function (User $user, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $user->email]);
            // Local demonstration delivery; never publish application logs.
            Log::info("UniFood - Enlace de recuperación de contraseña para {$user->email}: {$url}");
        });

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['email' => 'Espera un minuto antes de solicitar otro enlace.']);
        }

        return back()->with('success', 'Enlace de recuperación generado. Por favor revisa el archivo storage/logs/laravel.log');
    }

    public function formReset(Request $request, string $token)
    {
        return view('auth.reset', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'El enlace de recuperación es inválido o ha expirado.']);
        }

        return redirect('/login')->with('success', 'Tu contraseña ha sido restablecida con éxito. Ya puedes iniciar sesión.');
    }
}
