<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public $timestamps = false;

    protected $table = 'paiements';

    protected $fillable = [
        'id',
        'reservation_id',
        'operateur',
        'telephone',
        'montant',
        'reference',
        'statut',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }
}
