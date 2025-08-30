<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title', 
        'slug',
        'content', 
        'image',
        'published_at', 
        'status', 
        'meta_titre',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime', 
            'status' => 'string',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}