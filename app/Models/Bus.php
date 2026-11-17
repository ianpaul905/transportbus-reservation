<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    public $timestamps = false;

    protected $table = 'bus';

    protected $fillable = [
        'id',
        'immatriculation',
        'nombre_places',
        'classe',
        'statut',
        'agence_id',
    ];

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agence_id');
    }

    public function trajets(): HasMany
    {
        return $this->hasMany(Trip::class, 'bus_id');
    }
}
