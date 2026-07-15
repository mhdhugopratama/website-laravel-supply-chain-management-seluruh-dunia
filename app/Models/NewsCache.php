<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'news_cache';

    protected $fillable = [
        'cache_key', 'articles', 'positive_pct',
        'neutral_pct', 'negative_pct', 'cached_at',
    ];

    protected $casts = [
        'cached_at' => 'datetime',
        'articles'  => 'array',
    ];
}
