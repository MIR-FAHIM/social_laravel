<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HarmonyOfLife extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'happiness', 'sadness', 'joyfulness', 'excitement', 'calmness', 'fear', 'anger', 'surprise', 'harmony_percentage'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
