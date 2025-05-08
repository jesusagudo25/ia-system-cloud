<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone_number',
        'city',
        'state',
        'zip_code',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function specialPrices()
    {
        return $this->hasMany(SpecialPrice::class);
    }

    public function lenguagesWithSpecialPrices()
    {
        return $this->belongsToMany(Lenguage::class, 'special_prices')
            ->withPivot(['price_per_hour', 'price_per_hour_interpreter'])
            ->withTimestamps();
    }
}
