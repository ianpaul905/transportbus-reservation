@extends('layouts.app')

@section('title', 'Réservations — Administration')

@section('content')
@php
    $operateurs = [
        'airtel_money' => 'Airtel Money',
        'moov_money' => 'Moov Money',
    ];

    $lignesCA = collect($caParOperateur)->map(function ($ligne, $cle) {
        if (is_array($ligne) || is_object($ligne)) {
            return (object) [
                'operateur' => data_get($ligne, 'operateur', $cle),
                'nombre' => data_get($ligne, 'nombre', data_get($ligne, 'count', 0)),
                'total' => data_get($ligne, 'total', 0),
            ];
        }

        return (object) [
            'operateur' => $cle,
            'nombre' => 0,
            'total' => $ligne,
        ];
    });
@endphp

<nav class="subnav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
    <a href="{{ route('admin.trajets') }}" class="{{ request()->routeIs('admin.trajets*') ? 'active' : '' }}">Trajets</a>
    <a href="{{ route('admin.bus') }}" class="{{ request()->routeIs('admin.bus*') ? 'active' : '' }}">Bus</a>
    <a href="{{ route('admin.reservations') }}" class="{{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">Réservations</a>
</nav>

<h1>Réservations</h1>

<div class="card">
    <h2>Chiffre d'affaires par opérateur</h2>

    @if (count($lignesCA) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Opérateur</th>
                    <th>Paiements</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($lignesCA as $ligne)
                    <tr>
                        <td>{{ $operateurs[$ligne->operateur] ?? $ligne->operateur }}</td>
                        <td>{{ $ligne->nombre }}</td>
                        <td><strong>{{ prix($ligne->total) }}</strong></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">Aucun paiement enregistré.</div>
    @endif

    <p class="muted mt-2">Total : <strong class="price">{{ prix($caTotal) }}</strong></p>
</div>

<div class="card">
    <h2>Toutes les réservations</h2>

    @if (count($reservations) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Référence</th>
                    <th>Type</th>
                    <th>Client</th>
                    <th>Pièces</th>
                    <th>Trajet</th>
                    <th>Départ</th>
                    <th>Places</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Créée le</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reservations as $r)
                    <tr>
                        <td>
                            <strong>{{ $r->reference }}</strong><br>
                            <a href="{{ route('admin.reservation', $r->id) }}">Détail</a>
                        </td>
                        <td>
                            <span class="badge {{ $r->type_reservation === 'famille' ? 'badge-bleu' : 'badge-gris' }}">{{ libelle_type_reservation($r->type_reservation) }}</span>
                            @if ($r->nom_famille)
                                <br><span class="muted">{{ $r->nom_famille }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $r->client_nom }}<br>
                            <span class="muted">{{ $r->client_telephone }}</span>
                        </td>
                        <td>
                            @if ($r->type_reservation === 'famille')
                                <span class="badge {{ classe_documents($r->documents_statut) }}">{{ libelle_documents($r->documents_statut) }}</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td>{{ $r->trajet->ville_depart }} → {{ $r->trajet->ville_arrivee }}</td>
                        <td>{{ date_fr($r->trajet->date_depart) }} · {{ heure_fr($r->trajet->heure_depart) }}</td>
                        <td>{{ $r->nombre_places }}</td>
                        <td>{{ prix($r->trajet->prix * $r->nombre_places) }}</td>
                        <td><span class="badge {{ classe_statut($r->statut) }}">{{ libelle_statut($r->statut) }}</span></td>
                        <td class="muted">{{ $r->created_at instanceof \Carbon\CarbonInterface ? $r->created_at->format('d/m/Y H:i') : $r->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">Aucune réservation pour le moment.</div>
    @endif
</div>
@endsection
