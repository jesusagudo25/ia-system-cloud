<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_check_id',
        'assignment',
        'closing_date',
        'date_service',
        'total_amount',
    ];

    public function bankCheck()
    {
        return $this->belongsTo(BankCheck::class);
    }
}
