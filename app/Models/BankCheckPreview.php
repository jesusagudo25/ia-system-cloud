<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankCheckPreview extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
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

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function checkDetailPreviews()
    {
        return $this->hasMany(CheckDetailPreview::class);
    }
}
