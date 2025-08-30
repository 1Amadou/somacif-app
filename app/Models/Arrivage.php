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
        'details_produits', // Le JSON contenant les produits reçus
        'notes',
        'user_id',          // Qui a enregistré l'arrivage
        'decharge_signee_path',
    ];

    protected $casts = [
        'date_arrivage' => 'datetime',
        'details_produits' => 'array',
    ];

    /**
     * L'utilisateur (employé) qui a créé l'enregistrement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Le fournisseur qui a livré la marchandise.
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }
}