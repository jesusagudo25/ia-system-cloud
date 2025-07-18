<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $primaryKey = 'email';

    protected $table = 'password_reset_tokens';

    protected $fillable = [
        'email',
        'token'
    ];

    public $incrementing = false;
    

    const UPDATED_AT = null;
    
}
