<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'full_name', 'email', 'phone_number', 'password', 'profile_picture', 'fcm_token'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static $rules = [
        'full_name' => 'required|string',
        'email' => 'required|string|email|unique:users',
        'phone_number' => 'required|string|unique:users',
        'password' => 'required|string|min:6',
    ];

    public function brahminsForKarm()
    {
        return $this->hasMany(BrahminsForKarm::class, 'brahmin_id');
    }

    public function createdKarms()
    {
        return $this->hasMany(Karm::class, 'created_by');
    }

    public function sentContacts()
    {
        return $this->hasMany(Contacts::class, 'sender_id');
    }

    public function receivedContacts()
    {
        return $this->hasMany(Contacts::class, 'receiver_id');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }
    public function countAcceptedContacts()
{
    return Contacts::where(function ($query) {
        $query->where('sender_id', $this->id)
              ->orWhere('receiver_id', $this->id);
    })->where('status', 'accepted')->count();
}
}
