<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_entreprise',
        'nom_contact',
        'telephone',
        'email',
        'secteur_activite',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }
}