<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'type', 'status', 'telephone', 'email', 
        'identifiant_unique_somacif', 'entrepots_de_livraison',
        'contract_path', 'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'entrepots_de_livraison' => 'array',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
     public function loginLogs(): HasMany {
        return $this->hasMany(ClientLoginLog::class);
    }
}