<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniteDeVente extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'nom_unite',
        'prix_unitaire',
        'stock',
        'calibre',
        'prix_grossiste',
        'prix_hotel_restaurant',
        'prix_particulier',
    ];

    protected $with = ['product'];

    public function getNomCompletAttribute(): string
    {
        return "{$this->product->nom} - {$this->calibre} ({$this->nom_unite})";
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}