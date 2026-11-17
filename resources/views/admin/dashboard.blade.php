@extends('layouts.app')

@section('title', 'Administration — TransportBus Gabon')

@section('content')
<nav class="subnav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
    <a href="{{ route('admin.trajets') }}" class="{{ request()->routeIs('admin.trajets*') ? 'active' : '' }}">Trajets</a>
    <a href="{{ route('admin.bus') }}" class="{{ request()->routeIs('admin.bus*') ? 'active' : '' }}">Bus</a>
    <a href="{{ route('admin.reservations') }}" class="{{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">Réservations</a>
</nav>

<h1>Tableau de bord</h1>
<p class="muted">Vue d'ensemble de l'activité de la gare routière.</p>

<div class="stats-grid mt-2">
    <div class="stat">
        <div class="valeur">{{ $nbTrajets }}</div>
        <div class="libelle">Trajets à venir</div>
    </div>
    <div class="stat">
        <div class="valeur">{{ $nbReservations }}</div>
        <div class="libelle">Réservations</div>
    </div>
    <div class="stat">
        <div class="valeur">{{ $placesVendues }}</div>
        <div class="libelle">Places vendues</div>
    </div>
    <div class="stat">
        <div class="valeur">{{ prix($ca) }}</div>
        <div class="libelle">Chiffre d'affaires</div>
    </div>
</div>

<div class="card">
    <h2>Prochains départs</h2>

    @if (count($prochains) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Trajet</th>
                    <th>Départ</th>
                    <th>Bus</th>
                    <th>Places</th>
                    <th>Prix</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($prochains as $trajet)
                    <tr>
                        <td>{{ $trajet->ville_depart }} → {{ $trajet->ville_arrivee }}</td>
                        <td>{{ date_fr($trajet->date_depart) }} · {{ heure_fr($trajet->heure_depart) }}</td>
                        <td>{{ $trajet->bus?->immatriculation }}</td>
                        <td>
                            {{ $trajet->placesRestantes() }} libre(s)
                            <span class="muted">/ {{ $trajet->bus?->nombre_places }}</span>
                        </td>
                        <td>{{ prix($trajet->prix) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">Aucun départ à venir. <a href="{{ route('admin.trajets') }}">Programmer un trajet</a>.</div>
    @endif
</div>
@endsection
