<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interpreter extends Model
{
    use HasFactory;

    protected $fillable = [
        'lenguage_id',
        'full_name',
        'phone_number',
        'email',
        'ssn',
        'address',
        'city',
        'state',
        'zip_code',
    ];

    public function lenguage()
    {
        return $this->belongsTo(Lenguage::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
