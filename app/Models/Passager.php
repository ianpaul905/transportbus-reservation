<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passager extends Model
{
    public $timestamps = false;

    protected $table = 'passagers';

    protected $fillable = [
        'id',
        'reservation_id',
        'nom_complet',
        'type_piece',
        'numero_piece',
        'fichier',
        'fichier_nom',
        'fichier_type',
        'fichier_taille',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }
}
