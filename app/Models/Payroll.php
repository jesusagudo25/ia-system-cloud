<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'start_date',
        'end_date',
        'total_amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankChecks()
    {
        return $this->hasMany(BankCheck::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
