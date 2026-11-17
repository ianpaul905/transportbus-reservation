<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    public $timestamps = false;

    protected $table = 'trajets';

    protected $fillable = [
        'id',
        'ville_depart',
        'ville_arrivee',
        'date_depart',
        'heure_depart',
        'prix',
        'bus_id',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'trajet_id');
    }

    public function placesRestantes(): int
    {
        $capacite = (int) ($this->bus?->nombre_places ?? 0);
        $occupees = (int) $this->reservations()
            ->where('statut', '!=', 'annulee')
            ->sum('nombre_places');

        return $capacite - $occupees;
    }

    public function estComplet(): bool
    {
        return $this->placesRestantes() <= 0;
    }
}
