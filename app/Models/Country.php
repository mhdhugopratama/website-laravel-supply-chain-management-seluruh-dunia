<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name', 'iso2', 'iso3', 'capital', 'region', 'subregion',
        'population', 'latitude', 'longitude', 'currency_code',
        'currency_name', 'currency_symbol', 'flag_emoji', 'area', 'languages',
    ];

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }
}
