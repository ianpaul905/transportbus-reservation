@extends('layouts.app')

@section('title', 'Résultats — ' . $ville_depart . ' → ' . $ville_arrivee)

@section('content')
<h1>Trajets disponibles</h1>
<p class="muted">{{ $ville_depart }} → {{ $ville_arrivee }} · {{ date_fr($date_depart) }}</p>

@forelse ($trajets as $t)
    <div class="mt-2">
        <div class="trip-card">
            <div class="trip-main">
                <div class="route">{{ $t->ville_depart }} → {{ $t->ville_arrivee }}</div>
                <div class="meta">
                    {{ date_fr($t->date_depart) }} à {{ heure_fr($t->heure_depart) }}
                    · {{ $t->bus?->agence?->nom ?? $t->bus?->agency?->nom }}
                    · Bus {{ $t->bus?->immatriculation }}
                    @if ($t->bus?->classe) · {{ $t->bus->classe }} @endif
                </div>
            </div>
            <div class="trip-side">
                <span class="price">{{ prix($t->prix) }}</span>
                @if ($t->placesRestantes() > 0)
                    <span class="badge badge-vert">{{ $t->placesRestantes() }} place(s) libre(s)</span>
                    <a class="btn btn-sm" href="{{ route('reservation.create', $t->id) }}">Réserver</a>
                @else
                    <span class="badge badge-rouge">Complet</span>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="empty">Aucun départ n'est programmé pour cette date.</div>
@endforelse

@if ($complet)
    <div class="card mt-2">
        <h2>Prochains départs disponibles</h2>
        <p class="muted">Le départ choisi est complet ou indisponible. Voici les prochaines dates disponibles sur le même axe.</p>

        @forelse ($alternatives as $t)
            <div class="trip-card">
                <div class="trip-main">
                    <div class="route">{{ $t->ville_depart }} → {{ $t->ville_arrivee }}</div>
                    <div class="meta">
                        {{ date_fr($t->date_depart) }} à {{ heure_fr($t->heure_depart) }}
                        · {{ $t->bus?->agence?->nom ?? $t->bus?->agency?->nom }}
                    </div>
                </div>
                <div class="trip-side">
                    <span class="price">{{ prix($t->prix) }}</span>
                    <span class="badge badge-vert">{{ $t->placesRestantes() }} place(s) libre(s)</span>
                    <a class="btn btn-sm" href="{{ route('reservation.create', $t->id) }}">Réserver</a>
                </div>
            </div>
        @empty
            <div class="empty">Aucune alternative n'est programmée pour le moment sur cet axe.</div>
        @endforelse
    </div>
@endif

<p class="mt-2"><a href="{{ route('home') }}">← Nouvelle recherche</a></p>
@endsection
