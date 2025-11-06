<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'image_path', 'serial', 'isPublic', 'status', 'is_default'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
