<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Trip;
use App\Services\AlternativeDatesService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function create(Trip $trip, AlternativeDatesService $alternativesService)
    {
        $trip->load('bus');

        if (! $trip->estComplet()) {
            return view('reservation.form', compact('trip'));
        }

        $date = $trip->date_depart instanceof \DateTimeInterface
            ? $trip->date_depart->format('Y-m-d')
            : substr((string) $trip->date_depart, 0, 10);

        $alternatives = $alternativesService->suggerer(
            $trip->ville_depart,
            $trip->ville_arrivee,
            $date
        )['alternatives'];

        return view('reservation.complet', compact('trip', 'alternatives'));
    }

    public function store(Request $request, Trip $trip)
    {
        $type = $request->input('type_reservation');

        $regles = [
            'type_reservation' => 'required|in:individuel,famille',
            'client_telephone' => ['required', 'regex:/^[0-9 +().-]{6,30}$/'],
        ];

        if ($type === 'famille') {
            $noms = $request->input('passagers_nom', []);
            $nombre = is_array($noms) ? count($noms) : 0;

            $regles['nom_famille'] = 'required|string|min:2|max:100';
            $regles['passagers_nom'] = 'required|array|min:2|max:6';
            $regles['passagers_nom.*'] = 'required|string|min:2|max:100';
            $regles['passagers_type'] = ['required', 'array', 'size:' . $nombre];
            $regles['passagers_type.*'] = ['required', Rule::in(array_keys(types_pieces()))];
            $regles['passagers_numero'] = ['required', 'array', 'size:' . $nombre];
            $regles['passagers_numero.*'] = ['required', 'regex:/^[A-Za-z0-9 .,\/-]{3,60}$/'];
        } else {
            $regles['client_nom'] = 'required|string|min:2|max:100';
            $regles['nombre_places'] = 'required|integer|min:1|max:6';
        }

        $validated = $request->validate($regles);

        $places = $type === 'famille'
            ? count($validated['passagers_nom'])
            : (int) $validated['nombre_places'];

        if ($places > $trip->placesRestantes()) {
            $champ = $type === 'famille' ? 'passagers_nom' : 'nombre_places';

            return back()->withErrors([
                $champ => 'Il ne reste que ' . $trip->placesRestantes() . ' place(s) sur ce départ.',
            ])->withInput();
        }

        $documentsCrees = [];
        $documents = new DocumentService();

        try {
            $reservation = DB::transaction(function () use ($request, $trip, $validated, $type, $places, $documents, &$documentsCrees) {
                $reference = 'RES-' . strtoupper(bin2hex(random_bytes(4)));

                $reservation = Reservation::create([
                    'reference' => $reference,
                    'trajet_id' => $trip->id,
                    'user_id' => auth()->id(),
                    'client_nom' => $type === 'famille' ? $validated['nom_famille'] : $validated['client_nom'],
                    'client_telephone' => $validated['client_telephone'],
                    'nombre_places' => $places,
                    'type_reservation' => $type,
                    'nom_famille' => $type === 'famille' ? $validated['nom_famille'] : null,
                    'documents_statut' => $type === 'famille' ? 'en_attente' : 'non_requis',
                    'statut' => 'en_attente',
                ]);

                if ($type === 'famille') {
                    $fichiers = $request->file('fichiers', []);

                    if ($fichiers instanceof UploadedFile) {
                        $fichiers = [$fichiers];
                    }

                    foreach ($validated['passagers_nom'] as $index => $nom) {
                        $document = null;

                        if (isset($fichiers[$index]) && $fichiers[$index] instanceof UploadedFile) {
                            $document = $documents->enregistrer($fichiers[$index]);

                            if ($document !== null) {
                                $documentsCrees[] = $document['fichier'];
                            }
                        }

                        $reservation->passagers()->create([
                            'nom_complet' => $nom,
                            'type_piece' => $validated['passagers_type'][$index],
                            'numero_piece' => $validated['passagers_numero'][$index],
                            'fichier' => $document['fichier'] ?? null,
                            'fichier_nom' => $document['fichier_nom'] ?? null,
                            'fichier_type' => $document['fichier_type'] ?? null,
                            'fichier_taille' => $document['fichier_taille'] ?? null,
                            'fichier_donnees' => $document['fichier_donnees'] ?? null,
                        ]);
                    }
                }

                return $reservation;
            });
        } catch (\Throwable $exception) {
            foreach ($documentsCrees as $fichier) {
                $documents->supprimer($fichier);
            }

            return back()->with('error', $exception->getMessage())->withInput();
        }

        session()->push('acces_reservations', $reservation->id);

        return redirect()->route('payment.form', ['reservation' => $reservation->id])
            ->with('success', 'Réservation ' . $reservation->reference . ' enregistrée. Procédez au paiement.');
    }

    public function mesReservations()
    {
        $reservations = Reservation::query()
            ->where('user_id', auth()->id())
            ->with(['trajet.bus', 'payment', 'passagers'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('mes-reservations', compact('reservations'));
    }

    public function annuler(Request $request, Reservation $reservation)
    {
        if ((int) $reservation->user_id !== (int) auth()->id() || $reservation->statut !== 'en_attente') {
            return redirect()->route('reservations.mes')
                ->with('error', 'Cette réservation ne peut pas être annulée.');
        }

        $reservation->update(['statut' => 'annulee']);

        return redirect()->route('reservations.mes')
            ->with('success', 'Réservation annulée. Les places sont de nouveau disponibles.');
    }
}
