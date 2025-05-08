<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'lenguage_id',
        'agency_id',
        'price_per_hour',
        'price_per_hour_interpreter',
    ];

    public function lenguage()
    {
        return $this->belongsTo(Lenguage::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
