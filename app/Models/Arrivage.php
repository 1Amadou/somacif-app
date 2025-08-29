<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Arrivage extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_arrivage',
        'fournisseur_id',
        'numero_bon_livraison',
        'details_produits',
        'notes',
        'user_id',
        'decharge_signee_path',
    ];

    protected $casts = [
        'date_arrivage' => 'datetime',
        'details_produits' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }
}