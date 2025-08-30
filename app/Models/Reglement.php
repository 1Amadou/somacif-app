<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reglement extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'client_id',
        'user_id',
        'montant_verse',
        'montant_calcule', 
        'methode_paiement',
        'notes',
        'date_reglement', 
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_reglement');
    }

    public function details(): HasMany
    {
        
        return $this->hasMany(DetailReglement::class);
    }
}