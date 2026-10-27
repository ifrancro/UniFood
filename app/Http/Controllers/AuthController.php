<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function formLogin()
    {
        if (Auth::check()) {
            return Auth::user()->rol === 'estudiante'
                ? redirect('/catalogo')
                : redirect('/panel/pedidos');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->rol === 'estudiante') {
                return redirect()->intended('/catalogo');
            }

            return redirect()->intended('/panel/pedidos');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function formRegistro()
    {
        if (Auth::check()) {
            return redirect('/catalogo');
        }

        return view('auth.registro');
    }

    public function registro(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => 'estudiante',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/catalogo')->with('success', '¡Registro exitoso! Bienvenido a UniFood.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Sesión cerrada correctamente.');
    }
}
