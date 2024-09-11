<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = "admin_users";
    protected $fillable = ['name', 'email', 'password'];
   
}
