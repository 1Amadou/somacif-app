<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfert extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'new_order_id',
        'source_point_de_vente_id',
        'destination_point_de_vente_id',
        'user_id',
        'notes',
        'details',
    ];

    /**
     * Convertit automatiquement la colonne 'details' entre tableau PHP et JSON.
     * C'est essentiel pour la correction.
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    // --- RELATIONS ---
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class, 'order_id'); }
    public function newOrder(): BelongsTo { return $this->belongsTo(Order::class, 'new_order_id'); }
    public function sourcePointDeVente(): BelongsTo { return $this->belongsTo(PointDeVente::class, 'source_point_de_vente_id'); }
    public function destinationPointDeVente(): BelongsTo { return $this->belongsTo(PointDeVente::class, 'destination_point_de_vente_id'); }
}
