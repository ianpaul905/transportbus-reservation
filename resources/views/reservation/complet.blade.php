@extends('layouts.app')

@section('title', 'Départ complet — TransportBus Gabon')

@section('content')
<h1>Réserver un billet</h1>

<div class="alert alert-warning">
    Le départ choisi est complet ou indisponible.
</div>

<div class="card card-accent">
    <h2>{{ $trip->ville_depart }} → {{ $trip->ville_arrivee }}</h2>
    <p class="muted">
        {{ date_fr($trip->date_depart) }} à {{ heure_fr($trip->heure_depart) }}
        · Bus {{ $trip->bus?->immatriculation }}
    </p>
    <p>
        <span class="price">{{ prix($trip->prix) }}</span> par place
        · <span class="badge badge-rouge">Complet</span>
    </p>
</div>

<div class="card">
    <h2>Prochains départs disponibles</h2>

    @forelse ($alternatives as $alternative)
        <div class="trip-card">
            <div class="trip-main">
                <div class="route">{{ $alternative->ville_depart }} → {{ $alternative->ville_arrivee }}</div>
                <div class="meta">
                    {{ date_fr($alternative->date_depart) }} à {{ heure_fr($alternative->heure_depart) }}
                    · {{ $alternative->bus?->agence?->nom ?? $alternative->bus?->agency?->nom }}
                </div>
            </div>
            <div class="trip-side">
                <span class="price">{{ prix($alternative->prix) }}</span>
                <span class="badge badge-vert">{{ $alternative->placesRestantes() }} place(s) libre(s)</span>
                <a class="btn btn-sm" href="{{ route('reservation.create', $alternative->id) }}">Réserver</a>
            </div>
        </div>
    @empty
        <div class="empty">Aucun autre départ n'est disponible sur cet axe pour le moment.</div>
    @endforelse
</div>

<p><a href="{{ route('home') }}">← Rechercher un autre trajet</a></p>
@endsection
