<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'receiver_id',
        'sender_id',
        'content_id',
        'friend_req_id',
        'type',
        'title',
        'body',
        'is_read',
    ];

    // Define relationships if needed
    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
