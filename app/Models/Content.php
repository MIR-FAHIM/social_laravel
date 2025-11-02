<?php

namespace App\Models;
use App\Models\User;
use App\Models\ContentLike;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Content extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'text_content',
        'text_title',
        'text_url',
        'isGeneral',
        'isDiscussion',
        'isNews',
        'isEducation',
        'single_image',
        'isFired',
        'isBurnt',
        'score',
        'view_count',
        'comment_count',
        'like_count',
        'is_author_writting',
        'is_authenticated',
        'is_debate',
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

    public function collections()
    {
        return $this->hasMany(Collection::class, 'content_id');
    }
}
