<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointDeVente extends Model
{
    use HasFactory;
    protected $table = 'point_de_ventes';
    protected $fillable = ['nom', 'type', 'adresse', 'telephone', 'horaires', 'Maps_link','responsable_id',];

    // CORRECTION : On renomme la méthode de 'productsStock' en 'products'
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'inventory')
                    ->withPivot('quantite_stock')
                    ->withTimestamps();
    }
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'responsable_id');
    }
    public function inventory(): HasMany
{
    return $this->hasMany(Inventory::class);
}
public function getInventoryStock(int $uniteDeVenteId): int
    {
        return $this->inventory()
                    ->where('unite_de_vente_id', $uniteDeVenteId)
                    ->value('stock') ?? 0;
    }
    
}