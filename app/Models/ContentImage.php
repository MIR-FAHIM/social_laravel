<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentImage extends Model
{
    use HasFactory;
    protected $fillable = ['content_id', 'image_path', 'user_id', 'media_type'];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
