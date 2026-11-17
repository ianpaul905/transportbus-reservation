@extends('layouts.app')

@section('title', 'Paiement — ' . $reservation->reference)

@section('content')
<h1>Paiement Mobile Money</h1>

<div class="card">
    <h2>Récapitulatif</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Réservation</th>
                <td>{{ $reservation->reference }}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>
                    <span class="badge {{ $reservation->type_reservation === 'famille' ? 'badge-bleu' : 'badge-gris' }}">{{ libelle_type_reservation($reservation->type_reservation) }}</span>
                </td>
            </tr>
            <tr>
                <th>{{ $reservation->type_reservation === 'famille' ? 'Famille' : 'Passager' }}</th>
                <td>
                    {{ $reservation->type_reservation === 'famille' ? $reservation->nom_famille : $reservation->client_nom }}
                    · {{ $reservation->client_telephone }}
                </td>
            </tr>
            <tr>
                <th>Trajet</th>
                <td>
                    {{ $reservation->trajet->ville_depart }} → {{ $reservation->trajet->ville_arrivee }}<br>
                    {{ date_fr($reservation->trajet->date_depart) }} à {{ heure_fr($reservation->trajet->heure_depart) }}
                    · Bus {{ $reservation->trajet->bus?->immatriculation }}
                </td>
            </tr>
            <tr>
                <th>Places</th>
                <td>{{ $reservation->nombre_places }}</td>
            </tr>
            <tr>
                <th>Montant total</th>
                <td><span class="price">{{ prix($reservation->trajet->prix * $reservation->nombre_places) }}</span></td>
            </tr>
        </table>
    </div>

    @if ($reservation->type_reservation === 'famille')
        <details class="mt-2">
            <summary>Voir les {{ count($reservation->passagers) }} voyageur(s) de la famille</summary>

            <div class="table-wrap mt-2">
                <table>
                    <thead>
                    <tr>
                        <th>Voyageur</th>
                        <th>Pièce</th>
                        <th>Numéro</th>
                        <th>Justificatif</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reservation->passagers as $passager)
                        <tr>
                            <td>{{ $passager->nom_complet }}</td>
                            <td>{{ libelle_piece($passager->type_piece) }}</td>
                            <td>{{ masquer_piece($passager->numero_piece) }}</td>
                            <td>
                                @if ($passager->fichier)
                                    <span class="badge badge-vert">Fourni</span>
                                @else
                                    <span class="badge badge-gris">Non fourni</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <p class="muted mt-2">
                Les pièces seront vérifiées par l'agence :
                <span class="badge {{ classe_documents($reservation->documents_statut) }}">{{ libelle_documents($reservation->documents_statut) }}</span>
            </p>
        </details>
    @endif
</div>

<div class="card">
    <h2>Simulation de paiement</h2>
    <p class="muted">Simulation de paiement — Aucun argent réel n'est débité.</p>

    <form method="POST" action="{{ route('payment.payer', $reservation->id) }}">
        @csrf

        <div class="form-grid">
            <div>
                <label for="operateur">Opérateur</label>
                <select name="operateur" id="operateur" required>
                    <option value="">— Choisir —</option>
                    <option value="airtel_money" @selected(old('operateur') === 'airtel_money')>Airtel Money</option>
                    <option value="moov_money" @selected(old('operateur') === 'moov_money')>Moov Money</option>
                </select>
            </div>
            <div>
                <label for="telephone">Numéro de téléphone</label>
                <input type="text" name="telephone" id="telephone" maxlength="30" required
                       value="{{ old('telephone', $reservation->client_telephone) }}">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-lg">Payer {{ prix($reservation->trajet->prix * $reservation->nombre_places) }}</button>
            <a class="btn btn-secondary" href="{{ route('home') }}">Annuler</a>
        </div>
    </form>
</div>
@endsection
