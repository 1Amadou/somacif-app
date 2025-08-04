<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PointDeVente extends Model
{
    use HasFactory;
    protected $table = 'point_de_ventes';
    protected $fillable = ['nom', 'type', 'adresse', 'telephone', 'horaires', 'Maps_link'];

    // CORRECTION : On renomme la méthode de 'productsStock' en 'products'
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'inventory')
                    ->withPivot('quantite_stock')
                    ->withTimestamps();
    }
}