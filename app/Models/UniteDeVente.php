<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniteDeVente extends Model
{
    use HasFactory;

    protected $table = 'unite_de_ventes';

    protected $fillable = [
        'product_id',
        'nom_unite',
        'prix_grossiste',
        'prix_hotel_restaurant',
        'prix_particulier',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}