<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointDeVente extends Model
{
    use HasFactory;
    
    protected $table = 'point_de_ventes';
    
    protected $fillable = [
        'nom', 
        'type', 
        'adresse', 
        'telephone', 
        'horaires', 
        'Maps_link',
        'responsable_id',
    ];

    /**
     * Définit la relation avec le client qui est responsable du point de vente.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'responsable_id');
    }

    /**
     * CORRIGÉ : C'est la seule relation nécessaire pour l'inventaire.
     * Un point de vente possède plusieurs lignes d'inventaire.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}