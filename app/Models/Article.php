<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt',
        'body', 'cover_image', 'status',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
