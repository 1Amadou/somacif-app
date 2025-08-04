<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'titres',
        'contenus',
        'images',
        'meta_titre',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'titres' => 'array',
            'contenus' => 'array',
            'images' => 'array',
        ];
    }
}