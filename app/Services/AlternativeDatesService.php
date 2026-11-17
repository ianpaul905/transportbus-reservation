<?php

namespace App\Services;

use App\Models\Trip;

class AlternativeDatesService
{
    public function suggerer(string $depart, string $arrivee, string $date, int $limite = 5): array
    {
        $alternatives = Trip::query()
            ->where('ville_depart', $depart)
            ->where('ville_arrivee', $arrivee)
            ->where('date_depart', '>', $date)
            ->whereHas('bus', fn ($q) => $q->where('statut', 'actif'))
            ->with('bus')
            ->orderBy('date_depart')
            ->orderBy('heure_depart')
            ->get()
            ->filter(fn (Trip $trip) => $trip->placesRestantes() > 0)
            ->take($limite)
            ->values();

        return ['alternatives' => $alternatives];
    }
}
