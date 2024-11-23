<?php

namespace App\Models;
use App\Models\User;
use App\Models\Content;
use App\Models\ContentLike;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'content_id',
    ];
    public function likes()
    {
        return $this->hasMany(ContentLike::class, 'content_id');
    }
    // Optionally, you can define relationships or additional methods here
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
