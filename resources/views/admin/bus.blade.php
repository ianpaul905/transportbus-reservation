@extends('layouts.app')

@section('title', 'Bus — Administration')

@section('content')
<nav class="subnav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
    <a href="{{ route('admin.trajets') }}" class="{{ request()->routeIs('admin.trajets*') ? 'active' : '' }}">Trajets</a>
    <a href="{{ route('admin.bus') }}" class="{{ request()->routeIs('admin.bus*') ? 'active' : '' }}">Bus</a>
    <a href="{{ route('admin.reservations') }}" class="{{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">Réservations</a>
</nav>

<h1>Gestion des bus</h1>

@php
    $libellesStatut = [
        'actif' => 'Actif',
        'en_maintenance' => 'En maintenance',
        'inactif' => 'Inactif',
    ];
@endphp

<div class="card">
    <h2>Ajouter un bus</h2>

    <form method="POST" action="{{ route('admin.bus') }}">
        @csrf
        <input type="hidden" name="action" value="ajouter">

        <div class="form-grid">
            <div>
                <label for="immatriculation">Immatriculation</label>
                <input type="text" name="immatriculation" id="immatriculation" maxlength="20" required value="{{ old('immatriculation') }}">
            </div>
            <div>
                <label for="nombre_places">Nombre de places</label>
                <input type="number" name="nombre_places" id="nombre_places" min="1" max="80" required value="{{ old('nombre_places') }}">
            </div>
            <div>
                <label for="classe">Classe (optionnel)</label>
                <input type="text" name="classe" id="classe" maxlength="20" value="{{ old('classe') }}">
            </div>
            <div>
                <label for="statut">Statut</label>
                <select name="statut" id="statut" required>
                    @foreach ($libellesStatut as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected(old('statut', 'actif') === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="agence_id">Agence</label>
                <select name="agence_id" id="agence_id" required>
                    <option value="">— Choisir —</option>
                    @foreach ($agences as $agence)
                        <option value="{{ $agence->id }}" @selected(old('agence_id') == $agence->id)>
                            {{ $agence->nom }} ({{ $agence->ville }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Ajouter le bus</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Flotte</h2>

    @if (count($bus) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Immatriculation</th>
                    <th>Agence</th>
                    <th>Places</th>
                    <th>Classe</th>
                    <th>Statut</th>
                    <th>Changer le statut</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($bus as $vehicule)
                    <tr>
                        <td><strong>{{ $vehicule->immatriculation }}</strong></td>
                        <td>{{ $vehicule->agence?->nom ?? $vehicule->agency?->nom ?? '—' }}</td>
                        <td>{{ $vehicule->nombre_places }}</td>
                        <td>{{ $vehicule->classe ?: '—' }}</td>
                        <td>
                            <span class="badge {{ $vehicule->statut === 'actif' ? 'badge-vert' : ($vehicule->statut === 'en_maintenance' ? 'badge-jaune' : 'badge-gris') }}">
                                {{ $libellesStatut[$vehicule->statut] ?? $vehicule->statut }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.bus') }}" class="inline-form">
                                @csrf
                                <input type="hidden" name="action" value="statut">
                                <input type="hidden" name="bus_id" value="{{ $vehicule->id }}">
                                <select name="statut" data-auto-submit>
                                    @foreach ($libellesStatut as $valeur => $libelle)
                                        <option value="{{ $valeur }}" @selected($vehicule->statut === $valeur)>{{ $libelle }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-secondary">OK</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">Aucun bus enregistré.</div>
    @endif
</div>
@endsection
