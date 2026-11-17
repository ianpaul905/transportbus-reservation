@extends('layouts.app')

@section('title', 'Inscription — TransportBus Gabon')

@section('content')
<h1>Créer un compte</h1>

<div class="card auth-card">
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="field">
            <label for="nom">Nom complet</label>
            <input type="text" name="nom" id="nom" maxlength="100" required value="{{ old('nom') }}">
        </div>
        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input type="email" name="email" id="email" required value="{{ old('email') }}">
        </div>
        <div class="field">
            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone" maxlength="30" required value="{{ old('telephone') }}">
        </div>
        <div class="field">
            <label for="mot_de_passe">Mot de passe (6 caractères minimum)</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>
        </div>
        <div class="field">
            <label for="confirmation">Confirmer le mot de passe</label>
            <input type="password" name="confirmation" id="confirmation" required>
        </div>

        <button type="submit" class="btn btn-block">Créer mon compte</button>
    </form>

    <p class="muted mt-2">Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a></p>
    <p class="muted">Comptes de démonstration : admin@transportbus.ga / admin123, client@transportbus.ga / client123</p>
</div>
@endsection
