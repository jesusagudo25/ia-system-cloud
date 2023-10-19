<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CARWizard extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'request_id',
        'user_id',
        'action',
        'comment'
    ];

    protected $table = 'car_wizards';

    public function user()
    {
        return $this->belongsTo(User::class);
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
