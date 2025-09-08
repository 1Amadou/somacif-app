<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfertDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_transfert_details';

    public $timestamps = false;

    protected $fillable = [
        'stock_transfert_id',
        'unite_de_vente_id',
        'quantite',
    ];

    public function stockTransfert(): BelongsTo
    {
        return $this->belongsTo(StockTransfert::class);
    }

    public function uniteDeVente(): BelongsTo
    {
        return $this->belongsTo(UniteDeVente::class);
    }
}