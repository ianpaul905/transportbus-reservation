@extends('layouts.app')

@section('title', 'Réserver — ' . $trip->ville_depart . ' → ' . $trip->ville_arrivee)

@section('content')
@php
    $mode = old('type_reservation', 'individuel');
    $nomsPassagers = old('passagers_nom', []);
    $typesPassagers = old('passagers_type', []);
    $numerosPassagers = old('passagers_numero', []);
@endphp

<h1>Réserver un billet</h1>

<div class="card card-accent">
    <h2>{{ $trip->ville_depart }} → {{ $trip->ville_arrivee }}</h2>
    <p class="muted">
        {{ date_fr($trip->date_depart) }} à {{ heure_fr($trip->heure_depart) }}
        · {{ $trip->bus?->agence?->nom ?? $trip->bus?->agency?->nom }}
        · Bus {{ $trip->bus?->immatriculation }}
        @if ($trip->bus?->classe) · {{ $trip->bus->classe }} @endif
    </p>
    <p>
        <span class="price">{{ prix($trip->prix) }}</span> par place
        · <span class="badge badge-vert">{{ $trip->placesRestantes() }} place(s) libre(s)</span>
    </p>
</div>

<div class="card">
    <h2>Informations du passager</h2>

    <form method="POST" action="{{ route('reservation.store', $trip) }}" enctype="multipart/form-data" id="form-reservation">
        @csrf
        <input type="hidden" name="trip_id" value="{{ $trip->id }}">

        <div class="mode-choix">
            <label class="mode-option">
                <input type="radio" name="type_reservation" value="individuel" data-mode-individuel @checked($mode === 'individuel')>
                <span>
                    <strong>Billet individuel</strong>
                    <small>— De 1 à 6 places pour un voyage.</small>
                </span>
            </label>
            <label class="mode-option">
                <input type="radio" name="type_reservation" value="famille" data-mode-famille @checked($mode === 'famille')>
                <span>
                    <strong>Réservation famille</strong>
                    <small>— Nom de famille, voyageurs et pièces justificatives.</small>
                </span>
            </label>
        </div>

        <div class="field">
            <label for="client_telephone">Téléphone du responsable</label>
            <input type="text" name="client_telephone" id="client_telephone" maxlength="30" required
                   value="{{ old('client_telephone', auth()->user()->telephone ?? '') }}">
        </div>

        <div id="bloc-individuel" @if ($mode === 'famille') hidden @endif>
            <div class="form-grid">
                <div>
                    <label for="client_nom">Nom complet</label>
                    <input type="text" name="client_nom" id="client_nom" maxlength="100" required
                           @disabled($mode !== 'individuel')
                           value="{{ old('client_nom', auth()->user()->nom ?? '') }}">
                </div>
                <div>
                    <label for="nombre_places">Nombre de places</label>
                    <input type="number" name="nombre_places" id="nombre_places" min="1" max="6" required
                           @disabled($mode !== 'individuel')
                           value="{{ old('nombre_places', 1) }}">
                </div>
            </div>
            <p class="muted mt-2" id="total" data-prix="{{ $trip->prix }}">Total : <strong>{{ prix($trip->prix) }}</strong></p>
        </div>

        <div id="bloc-famille" @if ($mode !== 'famille') hidden @endif>
            <div class="field">
                <label for="nom_famille">Nom de la famille</label>
                <input type="text" name="nom_famille" id="nom_famille" maxlength="100" required
                       @disabled($mode !== 'famille')
                       placeholder="Ex. Famille Alou"
                       value="{{ old('nom_famille') }}">
            </div>

            <div class="passagers-entete">
                <h3>Voyageurs</h3>
                <span class="muted"><strong id="compteur-passagers">2</strong> / 6</span>
            </div>

            <p class="muted">Une place par voyageur. Justificatif facultatif : JPG, PNG ou PDF (2 Mo max).</p>

            <div id="passagers">
                @foreach ([0, 1] as $i)
                    <div class="passager-row" data-numero="Voyageur {{ $i + 1 }}">
                        <span class="passager-numero">Voyageur {{ $i + 1 }}</span>
                        <input type="text" name="passagers_nom[]" maxlength="100" required
                               @disabled($mode !== 'famille')
                               aria-label="Nom complet du voyageur" placeholder="Nom complet"
                               value="{{ $nomsPassagers[$i] ?? '' }}">
                        <select name="passagers_type[]" required @disabled($mode !== 'famille') aria-label="Type de pièce">
                            <option value="">— Pièce —</option>
                            @foreach (types_pieces() as $valeur => $libelle)
                                <option value="{{ $valeur }}" @selected(($typesPassagers[$i] ?? '') === $valeur)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="passagers_numero[]" maxlength="60" required
                               @disabled($mode !== 'famille')
                               aria-label="Numéro de la pièce" placeholder="Numéro de pièce"
                               value="{{ $numerosPassagers[$i] ?? '' }}">
                        <input type="file" name="fichiers[]" accept=".jpg,.jpeg,.png,.pdf"
                               @disabled($mode !== 'famille') aria-label="Justificatif">
                        <button type="button" class="btn btn-sm btn-secondary retirer-passager"
                                data-retirer-passager title="Retirer ce voyageur" @disabled($mode !== 'famille')>✕</button>
                    </div>
                @endforeach
            </div>

            <template id="modele-passager">
                <div class="passager-row" data-numero="Voyageur 3">
                    <span class="passager-numero">Voyageur 3</span>
                    <input type="text" name="passagers_nom[]" maxlength="100" required
                           aria-label="Nom complet du voyageur" placeholder="Nom complet">
                    <select name="passagers_type[]" required aria-label="Type de pièce">
                        <option value="">— Pièce —</option>
                        @foreach (types_pieces() as $valeur => $libelle)
                            <option value="{{ $valeur }}">{{ $libelle }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="passagers_numero[]" maxlength="60" required
                           aria-label="Numéro de la pièce" placeholder="Numéro de pièce">
                    <input type="file" name="fichiers[]" accept=".jpg,.jpeg,.png,.pdf" aria-label="Justificatif">
                    <button type="button" class="btn btn-sm btn-secondary retirer-passager"
                            data-retirer-passager title="Retirer ce voyageur">✕</button>
                </div>
            </template>

            <div class="passagers-pied">
                <button type="button" class="btn btn-sm btn-secondary" id="ajouter-passager"
                        data-ajouter-passager @disabled($mode !== 'famille')>+ Ajouter un voyageur</button>
                <span class="muted">Places : <strong id="places-famille">2</strong></span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-lg">Continuer vers le paiement</button>
            <a class="btn btn-secondary" href="{{ route('home') }}">Annuler</a>
        </div>
    </form>
</div>
@endsection
