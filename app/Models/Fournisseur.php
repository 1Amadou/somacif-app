<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_entreprise',
        'nom_contact',
        'telephone_contact',
        'email_contact',
        'adresse',
        'notes',
    ];
}