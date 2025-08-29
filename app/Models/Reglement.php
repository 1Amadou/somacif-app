<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reglement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'date_reglement',
        'montant_verse',
        'montant_calcule',
        'methode_paiement',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date_reglement' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailReglement::class);
    }
    public function orders()
{
    return $this->belongsToMany(Order::class);
}
}