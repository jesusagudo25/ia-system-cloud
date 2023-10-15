<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckDetailPreview extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_check_preview_id',
        'assignment_number',
        'date_of_service_provided',
        'location',
        'total_amount',
    ];

    public function BankCheckPreview()
    {
        return $this->belongsTo(BankCheckPreview::class);
    }
}
