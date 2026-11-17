@extends('layouts.app')

@section('title', 'Mes réservations — TransportBus Gabon')

@section('content')
<h1>Mes réservations</h1>
<p class="muted">Retrouvez vos billets et poursuivez les paiements en attente.</p>

@if (count($reservations) > 0)
    <div class="table-wrap mt-2">
        <table>
            <thead>
            <tr>
                <th>Référence</th>
                <th>Trajet</th>
                <th>Départ</th>
                <th>Places</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($reservations as $r)
                <tr>
                    <td>
                        <strong>{{ $r->reference }}</strong><br>
                        @if ($r->type_reservation === 'famille')
                            <span class="badge badge-bleu">Famille</span>
                            <span class="muted">{{ $r->nom_famille }}</span>
                            @if ($r->documents_statut)
                                <br><span class="badge {{ classe_documents($r->documents_statut) }}">{{ libelle_documents($r->documents_statut) }}</span>
                            @endif
                        @else
                            <span class="badge badge-gris">Individuel</span>
                        @endif
                    </td>
                    <td>{{ $r->trajet->ville_depart }} → {{ $r->trajet->ville_arrivee }}</td>
                    <td>{{ date_fr($r->trajet->date_depart) }} · {{ heure_fr($r->trajet->heure_depart) }}</td>
                    <td>{{ $r->nombre_places }}</td>
                    <td>{{ prix($r->trajet->prix * $r->nombre_places) }}</td>
                    <td><span class="badge {{ classe_statut($r->statut) }}">{{ libelle_statut($r->statut) }}</span></td>
                    <td>
                        @if ($r->statut === 'en_attente')
                            <a class="btn btn-sm" href="{{ route('payment.form', $r->id) }}">Payer</a>
                            <form method="POST" action="{{ route('reservation.annuler', $r->id) }}" class="inline-form" data-confirm="Annuler cette réservation ?">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Annuler</button>
                            </form>
                        @else
                            <a class="btn btn-sm btn-secondary" href="{{ route('billet', $r->id) }}">Voir le billet</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty">Vous n'avez encore aucune réservation. <a href="{{ route('home') }}">Rechercher un trajet</a>.</div>
@endif
@endsection
