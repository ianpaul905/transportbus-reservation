@extends('layouts.app')

@section('title', 'Connexion — TransportBus Gabon')

@section('content')
<h1>Connexion</h1>

<div class="card auth-card">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input type="email" name="email" id="email" required value="{{ old('email') }}">
        </div>
        <div class="field">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>
        </div>

        <button type="submit" class="btn btn-block">Se connecter</button>
    </form>

    <p class="muted mt-2">Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a></p>
    <p class="muted">Comptes de démonstration : admin@transportbus.ga / admin123, client@transportbus.ga / client123</p>
</div>
@endsection
