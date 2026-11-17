@extends('layouts.app')

@section('title', 'E-billet — ' . $reservation->reference)

@section('content')
@php
    $operateurs = [
        'airtel_money' => 'Airtel Money',
        'moov_money' => 'Moov Money',
    ];
    $operateur = $reservation->payment ? ($operateurs[$reservation->payment->operateur] ?? $reservation->payment->operateur) : '';
@endphp

<h1 class="text-center">E-billet</h1>
<p class="muted text-center">Référence {{ $reservation->reference }}</p>

@if ($reservation->statut === 'en_attente')
    <div class="alert alert-warning">
        Cette réservation n'est pas encore payée.
        <a href="{{ route('payment.form', $reservation->id) }}">Finaliser le paiement</a>.
    </div>
@elseif ($reservation->statut === 'annulee')
    <div class="alert alert-error">Cette réservation a été annulée.</div>
@endif

<div class="ticket">
    <div class="ticket-head">
        <h2>{{ $reservation->trajet->ville_depart }} → {{ $reservation->trajet->ville_arrivee }}</h2>
        <p>{{ $reservation->trajet->bus?->agence?->nom ?? $reservation->trajet->bus?->agency?->nom }} · Bus {{ $reservation->trajet->bus?->immatriculation }}</p>
    </div>

    <div class="ticket-body">
        <div class="ticket-row">
            <span>Type</span>
            <strong><span class="badge {{ $reservation->type_reservation === 'famille' ? 'badge-bleu' : 'badge-gris' }}">{{ libelle_type_reservation($reservation->type_reservation) }}</span></strong>
        </div>

        @if ($reservation->type_reservation === 'famille')
            <div class="ticket-row">
                <span>Famille</span>
                <strong>{{ $reservation->nom_famille }}</strong>
            </div>
            <div class="ticket-row">
                <span>Voyageurs</span>
                <strong>{{ count($reservation->passagers) }}</strong>
            </div>
        @else
            <div class="ticket-row">
                <span>Passager</span>
                <strong>{{ $reservation->client_nom }}</strong>
            </div>
        @endif

        <div class="ticket-row">
            <span>Téléphone</span>
            <strong>{{ $reservation->client_telephone }}</strong>
        </div>
        <div class="ticket-row">
            <span>Date et heure</span>
            <strong>{{ date_fr($reservation->trajet->date_depart) }} · {{ heure_fr($reservation->trajet->heure_depart) }}</strong>
        </div>
        <div class="ticket-row">
            <span>Places</span>
            <strong>{{ $reservation->nombre_places }}</strong>
        </div>
        <div class="ticket-row">
            <span>Montant</span>
            <strong>{{ prix($reservation->trajet->prix * $reservation->nombre_places) }}</strong>
        </div>
        <div class="ticket-row">
            <span>Statut</span>
            <strong><span class="badge {{ classe_statut($reservation->statut) }}">{{ libelle_statut($reservation->statut) }}</span></strong>
        </div>

        @if ($reservation->payment)
            <div class="ticket-row">
                <span>Paiement</span>
                <strong>{{ $operateur }} · {{ $reservation->payment->reference }}</strong>
            </div>
        @endif

        @if ($reservation->type_reservation === 'famille')
            <div class="passager-billet">
                <h3>Voyageurs</h3>
                <ul>
                    @foreach ($reservation->passagers as $passager)
                        <li>
                            <strong>{{ $passager->nom_complet }}</strong>
                            <span class="muted">{{ libelle_piece($passager->type_piece) }} · {{ masquer_piece($passager->numero_piece) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-2">
            <div class="qr" role="img" aria-label="Code de contrôle de démonstration">
                @foreach ($qr as $ligne)
                    @foreach ($ligne as $noir)<i class="{{ $noir ? 'on' : '' }}"></i>@endforeach
                @endforeach
            </div>
            <p class="qr-legende">Code de contrôle de démonstration (non scannable)</p>
        </div>
    </div>
</div>

<div class="form-actions mt-2" style="justify-content:center">
    <button type="button" class="btn" data-print>Imprimer le billet</button>
    @auth
        <a class="btn btn-secondary" href="{{ route('reservations.mes') }}">Mes réservations</a>
    @endauth
</div>
@endsection
