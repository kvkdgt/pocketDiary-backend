<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrahminsForkarm extends Model
{
    use HasFactory;
    protected $table = "brahmins_for_karm";
    protected $fillable = [
        'brahmin_id', 'karm_id', "status"
    ];
    public function karm()
    {
        return $this->belongsTo(Karm::class, 'karm_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'brahmin_id');
    }
}
