<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agency_id',
        'interpreter_id',
        'coordinator_id',
        'total_amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interpreter()
    {
        return $this->belongsTo(Interpreter::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function tempoInvoiceDetails()
    {
        return $this->hasMany(TempoInvoiceDetail::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
