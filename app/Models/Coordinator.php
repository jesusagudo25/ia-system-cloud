<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coordinator extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'zip_code',
        'phone_number',
        'email',
        'ssn',
        'address',
        'city',
        'state',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
