<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\AlternativeDatesService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $villes = villes();

        $trajets = Trip::query()
            ->whereHas('bus', fn ($q) => $q->where('statut', 'actif'))
            ->where(function ($q) {
                $q->where('date_depart', '>', now()->toDateString())
                    ->orWhere(function ($q) {
                        $q->where('date_depart', now()->toDateString())
                            ->where('heure_depart', '>=', now()->format('H:i'));
                    });
            })
            ->with('bus')
            ->orderBy('date_depart')
            ->orderBy('heure_depart')
            ->get()
            ->filter(fn (Trip $trip) => $trip->placesRestantes() > 0)
            ->take(5)
            ->values();

        return view('home', compact('villes', 'trajets'));
    }

    public function rechercher(Request $request, AlternativeDatesService $alternativesService)
    {
        if (! $request->isMethod('post')) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'ville_depart' => 'required|string',
            'ville_arrivee' => 'required|string|different:ville_depart',
            'date_depart' => 'required|date_format:Y-m-d',
        ]);

        $trajets = Trip::query()
            ->where('ville_depart', $validated['ville_depart'])
            ->where('ville_arrivee', $validated['ville_arrivee'])
            ->where('date_depart', $validated['date_depart'])
            ->whereHas('bus', fn ($q) => $q->where('statut', 'actif'))
            ->with('bus')
            ->orderBy('heure_depart')
            ->get();

        $complet = $trajets->every(fn (Trip $trip) => $trip->placesRestantes() <= 0);

        $alternatives = $complet
            ? $alternativesService->suggerer(
                $validated['ville_depart'],
                $validated['ville_arrivee'],
                $validated['date_depart']
            )['alternatives']
            : collect();

        $ville_depart = $validated['ville_depart'];
        $ville_arrivee = $validated['ville_arrivee'];
        $date_depart = $validated['date_depart'];

        return view('search-results', compact(
            'trajets',
            'alternatives',
            'ville_depart',
            'ville_arrivee',
            'date_depart',
            'complet'
        ));
    }
}
