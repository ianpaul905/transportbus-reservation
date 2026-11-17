<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    public $timestamps = false;

    protected $table = 'agences';

    protected $fillable = [
        'id',
        'nom',
        'ville',
        'telephone',
    ];

    public function bus(): HasMany
    {
        return $this->hasMany(Bus::class, 'agence_id');
    }
}
