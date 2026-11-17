@extends('layouts.app')

@section('title', 'Accueil — TransportBus Gabon')

@section('content')
<section class="hero">
    <h1>Voyagez sans file d'attente</h1>
    <p>Réservez votre billet de bus interurbain et payez par Mobile Money (Airtel Money ou Moov Money), puis présentez votre e-billet à l'embarquement.</p>
</section>

<div class="card">
    <h2>Rechercher un trajet</h2>

    <form method="POST" action="{{ route('resultats') }}">
        @csrf
        <div class="form-grid">
            <div>
                <label for="ville_depart">Ville de départ</label>
                <select name="ville_depart" id="ville_depart" required>
                    <option value="">— Choisir —</option>
                    @foreach ($villes as $ville)
                        <option value="{{ $ville }}" @selected(old('ville_depart') === $ville)>{{ $ville }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ville_arrivee">Ville d'arrivée</label>
                <select name="ville_arrivee" id="ville_arrivee" required>
                    <option value="">— Choisir —</option>
                    @foreach ($villes as $ville)
                        <option value="{{ $ville }}" @selected(old('ville_arrivee') === $ville)>{{ $ville }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_depart">Date de départ</label>
                <input type="date" name="date_depart" id="date_depart"
                       value="{{ old('date_depart', now()->toDateString()) }}"
                       min="{{ now()->toDateString() }}" required>
            </div>
            <div>
                <button type="submit" class="btn btn-lg btn-block">Rechercher les trajets</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <h2>Prochains départs</h2>
    <p class="muted">Les prochains trajets programmés, avec leur prix et les places restantes.</p>

    @forelse ($trajets as $t)
        <div class="trip-card">
            <div class="trip-main">
                <div class="route">{{ $t->ville_depart }} → {{ $t->ville_arrivee }}</div>
                <div class="meta">
                    {{ date_fr($t->date_depart) }} à {{ heure_fr($t->heure_depart) }}
                    · {{ $t->bus?->agence?->nom ?? $t->bus?->agency?->nom }}
                    @if ($t->bus?->classe) · {{ $t->bus->classe }} @endif
                    · Bus {{ $t->bus?->immatriculation }}
                </div>
            </div>
            <div class="trip-side">
                <span class="price">{{ prix($t->prix) }}</span>
                <span class="badge badge-vert">{{ $t->placesRestantes() }} place(s) libre(s)</span>
                <a class="btn btn-sm" href="{{ route('reservation.create', $t->id) }}">Réserver</a>
            </div>
        </div>
    @empty
        <div class="empty">Aucun départ à venir pour le moment.</div>
    @endforelse
</div>

<div class="card">
    <h2>Comment ça marche ?</h2>
    <div class="steps">
        <div class="step">
            <strong>1</strong>
            <p>Recherchez un trajet selon la ville de départ, la ville d'arrivée et la date.</p>
        </div>
        <div class="step">
            <strong>2</strong>
            <p>Consultez les places restantes et le prix du billet.</p>
        </div>
        <div class="step">
            <strong>3</strong>
            <p>Réservez et payez par Airtel Money ou Moov Money (paiement simulé).</p>
        </div>
        <div class="step">
            <strong>4</strong>
            <p>Recevez votre e-billet avec un code de contrôle à présenter au contrôleur.</p>
        </div>
    </div>
</div>
@endsection
