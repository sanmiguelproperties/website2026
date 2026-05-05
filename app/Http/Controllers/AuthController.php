<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ], [
            'email.required' => 'Ingresa tu correo.',
            'email.email' => 'Formato de correo invalido.',
            'password.required' => 'Ingresa tu contrasena.',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withErrors(['email' => 'Tu usuario esta desactivado.'])
                    ->onlyInput('email');
            }

            // Keep a maximum of two active API tokens per user.
            $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();
            if ($tokens->count() >= 2) {
                $tokens->last()->delete();
            }

            $token = $user->createToken('API Token')->accessToken;

            $request->session()->put('passport_token', $token);

            return redirect()->intended('/admin/funnel');
        }

        return back()
            ->withErrors(['email' => 'Credenciales invalidas.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
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
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'Tu usuario esta desactivado.',
                ], 403);
            }

            // Keep a maximum of two active API tokens per user.
            $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();
            if ($tokens->count() >= 2) {
                $tokens->last()->delete();
            }

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
            'message' => 'Credenciales invalidas.',
        ], 401);
    }
}
