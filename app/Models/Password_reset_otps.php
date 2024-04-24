<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Password_reset_otps extends Model
{
    use HasFactory;
    protected $table = "password_resets";
    protected $fillable = [
        'email', 'otp'
    ];
}
