<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Bus;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Trip;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $nbTrajets = Trip::query()
            ->where('date_depart', '>=', now()->toDateString())
            ->count();

        $nbReservations = Reservation::query()->count();

        $ca = Payment::query()->where('statut', 'reussi')->sum('montant');

        $placesVendues = Reservation::query()
            ->whereIn('statut', ['payee', 'utilisee'])
            ->sum('nombre_places');

        $prochains = Trip::query()
            ->where('date_depart', '>=', now()->toDateString())
            ->whereHas('bus', fn ($q) => $q->where('statut', 'actif'))
            ->with('bus')
            ->orderBy('date_depart')
            ->orderBy('heure_depart')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'nbTrajets',
            'nbReservations',
            'ca',
            'placesVendues',
            'prochains'
        ));
    }

    public function bus(Request $request)
    {
        if ($request->isMethod('post')) {
            $action = $request->input('action');

            if ($action === 'ajouter') {
                $request->merge([
                    'immatriculation' => mb_strtoupper((string) $request->input('immatriculation')),
                ]);

                $validated = $request->validate([
                    'immatriculation' => 'required|string|min:2|max:20|unique:buses,immatriculation',
                    'nombre_places' => 'required|integer|min:1|max:80',
                    'classe' => 'nullable|string|max:20',
                    'statut' => 'required|in:actif,en_maintenance,inactif',
                    'agence_id' => 'required|exists:agencies,id',
                ]);

                $validated['classe'] = ($validated['classe'] ?? '') === '' ? null : $validated['classe'];

                Bus::create($validated);

                return redirect()->route('admin.bus')->with('success', 'Bus ajouté avec succès.');
            }

            if ($action === 'statut') {
                $validated = $request->validate([
                    'bus_id' => 'required|exists:buses,id',
                    'statut' => 'required|in:actif,en_maintenance,inactif',
                ]);

                Bus::query()->whereKey($validated['bus_id'])->update(['statut' => $validated['statut']]);

                return redirect()->route('admin.bus')->with('success', 'Statut du bus mis à jour.');
            }

            return redirect()->route('admin.bus')->with('error', 'Action invalide.');
        }

        $bus = Bus::query()->with('agence')->orderBy('immatriculation')->get();
        $agences = Agency::query()->orderBy('nom')->get();

        return view('admin.bus', compact('bus', 'agences'));
    }

    public function trajets(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->merge([
                'prix' => str_replace(',', '.', (string) $request->input('prix')),
            ]);

            $validated = $request->validate([
                'ville_depart' => 'required|string|max:100',
                'ville_arrivee' => 'required|string|max:100|different:ville_depart',
                'date_depart' => 'required|date_format:Y-m-d',
                'heure_depart' => ['required', 'regex:/^\d{2}:\d{2}$/'],
                'prix' => 'required|numeric|min:0.01',
                'bus_id' => [
                    'required',
                    Rule::exists('buses', 'id')->where('statut', 'actif'),
                ],
            ]);

            $validated['heure_depart'] .= ':00';

            Trip::create($validated);

            return redirect()->route('admin.trajets')->with('success', 'Trajet programmé avec succès.');
        }

        $trajets = Trip::query()
            ->where('date_depart', '>=', now()->toDateString())
            ->with('bus')
            ->orderBy('date_depart')
            ->orderBy('heure_depart')
            ->get();

        $bus = Bus::query()->where('statut', 'actif')->orderBy('immatriculation')->get();

        return view('admin.trajets', compact('trajets', 'bus'));
    }

    public function trajetsDestroy(Trip $trip)
    {
        try {
            $trip->delete();
        } catch (QueryException $exception) {
            return redirect()->route('admin.trajets')
                ->with('error', 'Impossible de supprimer : des réservations existent.');
        }

        return redirect()->route('admin.trajets')->with('success', 'Trajet supprimé.');
    }

    public function reservations()
    {
        $reservations = Reservation::query()
            ->with(['trajet.bus', 'user', 'payment', 'passagers'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $caParOperateur = Payment::query()
            ->where('statut', 'reussi')
            ->selectRaw('operateur, SUM(montant) as total')
            ->groupBy('operateur')
            ->pluck('total', 'operateur');

        $caTotal = Payment::query()->where('statut', 'reussi')->sum('montant');

        return view('admin.reservations', compact('reservations', 'caParOperateur', 'caTotal'));
    }

    public function reservation(Reservation $reservation)
    {
        $reservation->load(['trajet.bus.agence', 'user', 'passagers', 'payment']);

        return view('admin.reservation', compact('reservation'));
    }

    public function documents(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'action' => 'required|in:valider,refuser,refuse',
        ]);

        if ($reservation->type_reservation !== 'famille') {
            return back()->with('error', 'Cette réservation ne comporte pas de pièces à vérifier.');
        }

        $valide = $validated['action'] === 'valider';

        $reservation->update(['documents_statut' => $valide ? 'valide' : 'refuse']);

        return back()->with('success', 'Les pièces justificatives ont été ' . ($valide ? 'validées' : 'refusées') . '.');
    }
}
