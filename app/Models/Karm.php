<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karm extends Model
{
    use HasFactory;
    protected $table = "karm";
    protected $fillable = [
        'prayog_name', 'prayog_date', "place","remarks","created_by","manual_brahmins"
    ];
    public function brahminsForKarm()
    {
        return $this->hasMany(BrahminsForkarm::class, 'karm_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
