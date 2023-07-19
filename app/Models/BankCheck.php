<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'date',
        'pay_to',
        'ssn',
        'amount',
        'amount_in_words',
        'for',
        'address',
        'city',
        'state',
        'zip',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function checkDetails()
    {
        return $this->hasMany(CheckDetail::class);
    }
}
