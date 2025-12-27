<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','min:6'],
        ], [
            'email.required' => 'Ingresa tu correo.',
            'email.email'    => 'Formato de correo inválido.',
            'password.required' => 'Ingresa tu contraseña.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Gestionar tokens: mantener máximo 2 tokens por usuario
            $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();
            if ($tokens->count() >= 2) {
                // Borrar el más antiguo (el último en la lista ordenada desc)
                $tokens->last()->delete();
            }

            // Crear nuevo token
            $token = $user->createToken('API Token')->accessToken;

            // Guardar token en sesión
            $request->session()->put('passport_token', $token);

            return redirect()->intended('/admin/funnel');
        }

        return back()
            ->withErrors(['email' => 'Credenciales inválidas.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Revocar todos los tokens del usuario al cerrar sesión
            $user->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','min:6'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Gestionar tokens: mantener máximo 2 tokens por usuario
            $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();
            if ($tokens->count() >= 2) {
                // Borrar el más antiguo
                $tokens->last()->delete();
            }

            // Crear nuevo token
            $token = $user->createToken('API Token')->accessToken;

            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenciales inválidas.',
        ], 401);
    }
}