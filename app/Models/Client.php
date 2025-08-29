<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'type', 'telephone', 'email', 'identifiant_unique_somacif', 'password','entrepots_de_livraison',
    ];

    protected $hidden = [ 'password', 'remember_token' ];

    // CORRECTION : Un client a PLUSIEURS points de vente
    public function pointsDeVente(): HasMany
    {
        return $this->hasMany(PointDeVente::class, 'responsable_id');
    }

    // Un client a un inventaire via TOUS ses points de vente
    public function inventory(): HasMany
    {
        // On récupère les IDs de tous les points de vente de ce client
        $pointDeVenteIds = $this->pointsDeVente()->pluck('id');
        
        // On retourne toutes les lignes d'inventaire de ces points de vente
        return (new Inventory)->whereIn('point_de_vente_id', $pointDeVenteIds);
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(Reglement::class);
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}