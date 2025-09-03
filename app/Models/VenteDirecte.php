<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\VenteDirecteItem; 

class VenteDirecte extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_facture', 'client_id', 'date_vente', 'montant_total', 'notes'
    ];

    protected $casts = [
        'date_vente' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VenteDirecteItem::class);
    }
}