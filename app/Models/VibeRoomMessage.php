<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VibeRoomMessage extends Model
{
    use HasFactory;

    protected $table = 'vibe_room_messages';

    protected $fillable = [
        'vibe_room_id',
        'user_id',
        'participant_id',
        'message_content',
        'is_anonymous',
        'reactions',
        'guess_progress',
        'is_flagged',
        'is_hidden',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'reactions' => 'array',
        'is_flagged' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    /* -------------------------------------------
        Relationships
    --------------------------------------------*/

    public function room()
    {
        return $this->belongsTo(VibeRoom::class, 'vibe_room_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participant()
    {
        return $this->belongsTo(VibeRoomParticipant::class, 'participant_id');
    }

    /* -------------------------------------------
        Accessors (Smart)
    --------------------------------------------*/

    // Automatically mask username when anonymous
    public function getDisplayNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Anonymous User';
        }

        return $this->sender?->name ?? 'User';
    }

    /* -------------------------------------------
        Global Scopes
    --------------------------------------------*/

    protected static function booted()
    {
        // Hide messages that are removed or flagged
        static::addGlobalScope('visibleOnly', function ($query) {
            $query->where('is_hidden', false);
        });
    }
}
