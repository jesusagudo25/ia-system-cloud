<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lenguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_per_hour',
        'price_per_hour_interpreter',
    ];

    public function interpreters()
    {
        return $this->hasMany(Interpreter::class);
    }

    public function specialPrices()
    {
        return $this->hasMany(SpecialPrice::class);
    }

    public function agenciesWithSpecialPrices()
    {
        return $this->belongsToMany(Agency::class, 'special_prices')
            ->withPivot(['price_per_hour', 'price_per_hour_interpreter'])
            ->withTimestamps();
    }
}
