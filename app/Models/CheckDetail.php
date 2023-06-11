<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_check_id',
        'assignment_number',
        'date_of_service_provided',
        'location',
        'total_amount',
    ];

    public function bankCheck()
    {
        return $this->belongsTo(BankCheck::class);
    }
}
