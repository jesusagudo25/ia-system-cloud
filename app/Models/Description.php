<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Description extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function tempoInvoiceDetails()
    {
        return $this->hasMany(TempoInvoiceDetail::class);
    }
}
