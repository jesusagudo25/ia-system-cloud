<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WizardView extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment',
        'date_of_service',
        'agency',
        'interpreter',
        'status',
        'total_amount',
    ];
}
