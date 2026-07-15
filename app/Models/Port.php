<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = [
        'name', 'country_code', 'country_name',
        'latitude', 'longitude', 'un_locode', 'type',
    ];
}
