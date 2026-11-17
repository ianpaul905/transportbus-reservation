<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required|string',
        ]);

        if (! Auth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('mot_de_passe'),
        ])) {
            return back()->withErrors([
                'email' => 'E-mail ou mot de passe incorrect.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('reservations.mes');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (! $request->has('confirmation') && $request->has('mot_de_passe_confirmation')) {
            $request->merge(['confirmation' => $request->input('mot_de_passe_confirmation')]);
        }

        $validated = $request->validate([
            'nom' => 'required|string|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'telephone' => ['required', 'regex:/^[0-9 +().-]{6,30}$/'],
            'mot_de_passe' => 'required|string|min:6',
            'confirmation' => 'required|same:mot_de_passe',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'mot_de_passe' => Hash::make($validated['mot_de_passe']),
            'role' => 'client',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('reservations.mes')
            ->with('success', 'Compte créé. Bienvenue ' . $validated['nom'] . ' !');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Vous êtes déconnecté.');
    }
}
