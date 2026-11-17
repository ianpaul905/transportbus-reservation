@extends('layouts.app')

@section('title', 'Trajets — Administration')

@section('content')
<nav class="subnav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
    <a href="{{ route('admin.trajets') }}" class="{{ request()->routeIs('admin.trajets*') ? 'active' : '' }}">Trajets</a>
    <a href="{{ route('admin.bus') }}" class="{{ request()->routeIs('admin.bus*') ? 'active' : '' }}">Bus</a>
    <a href="{{ route('admin.reservations') }}" class="{{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">Réservations</a>
</nav>

<h1>Gestion des trajets</h1>

<div class="card">
    <h2>Programmer un trajet</h2>

    <form method="POST" action="{{ route('admin.trajets') }}">
        @csrf
        <div class="form-grid">
            <div>
                <label for="ville_depart">Ville de départ</label>
                <input type="text" name="ville_depart" id="ville_depart" maxlength="100" required value="{{ old('ville_depart') }}">
            </div>
            <div>
                <label for="ville_arrivee">Ville d'arrivée</label>
                <input type="text" name="ville_arrivee" id="ville_arrivee" maxlength="100" required value="{{ old('ville_arrivee') }}">
            </div>
            <div>
                <label for="date_depart">Date</label>
                <input type="date" name="date_depart" id="date_depart" required min="{{ now()->toDateString() }}" value="{{ old('date_depart', now()->toDateString()) }}">
            </div>
            <div>
                <label for="heure_depart">Heure</label>
                <input type="time" name="heure_depart" id="heure_depart" required value="{{ old('heure_depart', '06:00') }}">
            </div>
            <div>
                <label for="prix">Prix (FCFA)</label>
                <input type="number" name="prix" id="prix" min="100" step="100" required value="{{ old('prix') }}">
            </div>
            <div>
                <label for="bus_id">Bus</label>
                <select name="bus_id" id="bus_id" required>
                    <option value="">— Choisir —</option>
                    @foreach ($bus as $vehicule)
                        <option value="{{ $vehicule->id }}" @selected(old('bus_id') == $vehicule->id)>
                            {{ $vehicule->immatriculation }} · {{ $vehicule->nombre_places }} places
                            @if ($vehicule->classe) · {{ $vehicule->classe }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Ajouter le trajet</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Trajets programmés</h2>

    @if (count($trajets) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Trajet</th>
                    <th>Départ</th>
                    <th>Bus</th>
                    <th>Prix</th>
                    <th>Places</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($trajets as $t)
                    <tr>
                        <td>{{ $t->ville_depart }} → {{ $t->ville_arrivee }}</td>
                        <td>{{ date_fr($t->date_depart) }} · {{ heure_fr($t->heure_depart) }}</td>
                        <td>{{ $t->bus?->immatriculation }}</td>
                        <td>{{ prix($t->prix) }}</td>
                        <td>
                            {{ $t->placesRestantes() }} / {{ $t->bus?->nombre_places }}
                            @if ($t->placesRestantes() === 0)
                                <span class="badge badge-rouge">Complet</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.trajets.destroy', $t->id) }}" class="inline-form" data-confirm="Supprimer ce trajet ?">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">Aucun trajet à venir.</div>
    @endif
</div>
@endsection
