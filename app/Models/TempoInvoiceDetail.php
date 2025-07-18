<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempoInvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description_id',
        'date_of_service_provided',
        'arrival_time',
        'start_time',
        'end_time',
        'travel_time_to_assignment',
        'time_back_from_assignment',
        'travel_mileage',
        'cost_per_mile',
        'total_amount_miles',
        'total_amount_hours',
        'total_interpreter',
        'total_coordinator',
        'address_id',
        'comments',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function description()
    {
        return $this->belongsTo(Description::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
