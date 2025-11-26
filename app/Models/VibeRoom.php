<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VibeRoom extends Model
{
    use HasFactory;

    protected $table = 'vibe_rooms';

    protected $fillable = [
        'host_user_id',
        'mood_id',
        'room_title',
        'vibe_details',
        'expire_time',
        'allow_guessing',
        'allow_reveal',
        'is_active',
        'color',
    ];

    protected $casts = [
        'expire_time' => 'datetime',
        'allow_guessing' => 'boolean',
        'allow_reveal' => 'boolean',
        'is_active' => 'boolean',
    ];

    /* -----------------------------------------
    | Relationships
    |------------------------------------------*/

    // Room host (User)
    public function host()
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    // Mood association
    public function mood()
    {
        return $this->belongsTo(MoodMaster::class, 'mood_id');
    }

    // Room participants
    public function participants()
    {
        return $this->hasMany(VibeRoomParticipant::class, 'vibe_room_id');
    }

    // Messages / Vibes shared inside room
    public function messages()
    {
        return $this->hasMany(VibeRoomMessage::class, 'vibe_room_id');
    }

    /* -----------------------------------------
    | Scopes
    |------------------------------------------*/

    // Only active and not expired rooms
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where('expire_time', '>', Carbon::now());
    }

    /* -----------------------------------------
    | Helpers
    |------------------------------------------*/

    // Check if room is expired
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expire_time);
    }
}
