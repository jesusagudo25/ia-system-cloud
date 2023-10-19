<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_id',
        'suffix_id',
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

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function car_wizards()
    {
        return $this->hasMany(CARWizard::class);
    }
}
