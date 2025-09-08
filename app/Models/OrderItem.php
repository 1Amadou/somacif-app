<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;
    protected $table = 'order_items';
    protected $fillable = [ 'order_id', 'unite_de_vente_id', 'quantite', 'prix_unitaire' ];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function uniteDeVente(): BelongsTo { return $this->belongsTo(UniteDeVente::class); }
}