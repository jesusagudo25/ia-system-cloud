<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'suffix_id',
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

    public function bankCheckPreviews()
    {
        return $this->hasMany(BankCheckPreview::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function car_wizards()
    {
        return $this->hasMany(CARWizard::class);
    }
}
