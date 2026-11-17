@extends('layouts.app')

@section('title', 'Réservation ' . $reservation->reference . ' — Administration')

@section('content')
@php
    $operateurs = [
        'airtel_money' => 'Airtel Money',
        'moov_money' => 'Moov Money',
    ];
@endphp

<nav class="subnav">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
    <a href="{{ route('admin.trajets') }}" class="{{ request()->routeIs('admin.trajets*') ? 'active' : '' }}">Trajets</a>
    <a href="{{ route('admin.bus') }}" class="{{ request()->routeIs('admin.bus*') ? 'active' : '' }}">Bus</a>
    <a href="{{ route('admin.reservations') }}" class="{{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">Réservations</a>
</nav>

<p><a href="{{ route('admin.reservations') }}">← Toutes les réservations</a></p>

<h1>Réservation {{ $reservation->reference }}</h1>

<div class="card">
    <h2>Récapitulatif</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Type</th>
                <td>
                    <span class="badge {{ $reservation->type_reservation === 'famille' ? 'badge-bleu' : 'badge-gris' }}">{{ libelle_type_reservation($reservation->type_reservation) }}</span>
                    @if ($reservation->nom_famille) · {{ $reservation->nom_famille }} @endif
                </td>
            </tr>
            <tr>
                <th>Responsable</th>
                <td>{{ $reservation->client_nom }} · {{ $reservation->client_telephone }}</td>
            </tr>
            <tr>
                <th>Compte client</th>
                <td>{{ $reservation->user?->name ?? $reservation->user?->nom ?? 'Réservation sans compte' }}</td>
            </tr>
            <tr>
                <th>Trajet</th>
                <td>
                    {{ $reservation->trajet->ville_depart }} → {{ $reservation->trajet->ville_arrivee }}<br>
                    {{ date_fr($reservation->trajet->date_depart) }} à {{ heure_fr($reservation->trajet->heure_depart) }}
                    · {{ $reservation->trajet->bus?->agence?->nom ?? $reservation->trajet->bus?->agency?->nom }}
                    · Bus {{ $reservation->trajet->bus?->immatriculation }}
                </td>
            </tr>
            <tr>
                <th>Places</th>
                <td>{{ $reservation->nombre_places }}</td>
            </tr>
            <tr>
                <th>Montant</th>
                <td>{{ prix($reservation->trajet->prix * $reservation->nombre_places) }}</td>
            </tr>
            <tr>
                <th>Statut</th>
                <td><span class="badge {{ classe_statut($reservation->statut) }}">{{ libelle_statut($reservation->statut) }}</span></td>
            </tr>
            @if ($reservation->payment)
                <tr>
                    <th>Paiement</th>
                    <td>{{ $operateurs[$reservation->payment->operateur] ?? $reservation->payment->operateur }} · {{ $reservation->payment->reference }}</td>
                </tr>
            @endif
            <tr>
                <th>Pièces justificatives</th>
                <td>
                    @if ($reservation->type_reservation === 'famille')
                        <span class="badge {{ classe_documents($reservation->documents_statut) }}">{{ libelle_documents($reservation->documents_statut) }}</span>
                    @else
                        <span class="muted">Non requises</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

@if ($reservation->type_reservation === 'famille')
    <div class="card">
        <h2>Voyageurs et pièces</h2>

        @if (count($reservation->passagers) > 0)
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Voyageur</th>
                        <th>Pièce</th>
                        <th>Numéro</th>
                        <th>Justificatif</th>
                        <th>Taille</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reservation->passagers as $p)
                        <tr>
                            <td>{{ $p->nom_complet }}</td>
                            <td>{{ libelle_piece($p->type_piece) }}</td>
                            <td>{{ $p->numero_piece }}</td>
                            <td>
                                @if ($p->fichier)
                                    <a href="{{ route('document', $p->id) }}" target="_blank" rel="noopener">Voir le justificatif</a>
                                    @if ($p->fichier_nom)
                                        <br><span class="muted">{{ $p->fichier_nom }}</span>
                                    @endif
                                @else
                                    <span class="badge badge-gris">Aucun fichier</span>
                                @endif
                            </td>
                            <td>
                                @if ($p->fichier_taille)
                                    {{ number_format((int) $p->fichier_taille / 1024, 0, ',', ' ') }} Ko
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($reservation->documents_statut === 'en_attente')
                <div class="form-actions mt-2">
                    <form method="POST" action="{{ route('admin.documents', $reservation->id) }}" class="inline-form">
                        @csrf
                        <input type="hidden" name="action" value="valider">
                        <button type="submit" class="btn btn-sm">Valider les pièces</button>
                    </form>
                    <form method="POST" action="{{ route('admin.documents', $reservation->id) }}" class="inline-form">
                        @csrf
                        <input type="hidden" name="action" value="refuser">
                        <button type="submit" class="btn btn-sm btn-danger">Refuser les pièces</button>
                    </form>
                </div>
            @endif
        @else
            <div class="empty">Aucun voyageur enregistré pour cette réservation.</div>
        @endif
    </div>
@endif
@endsection
