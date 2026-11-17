<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'reference',
        'client_nom',
        'client_telephone',
        'nombre_places',
        'type_reservation',
        'nom_famille',
        'documents_statut',
        'statut',
        'trajet_id',
        'user_id',
    ];

    public function trajet(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trajet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function passagers(): HasMany
    {
        return $this->hasMany(Passager::class, 'reservation_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'reservation_id');
    }
}
