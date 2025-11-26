<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VibeRoomParticipant extends Model
{
    use HasFactory;

    protected $table = 'vibe_room_participants';

    protected $fillable = [
        'vibe_room_id',
        'user_id',
        'role',
        'is_anonymous',
        'guess_progress',
        'is_kicked',
        'is_banned',
        'last_active_at',
    ];

    protected $casts = [
        'is_anonymous'   => 'boolean',
        'is_kicked'      => 'boolean',
        'is_banned'      => 'boolean',
        'guess_progress' => 'integer',
        'last_active_at' => 'datetime',
    ];

    // hide any sensitive fields if needed
    protected $hidden = [
        // nothing for now
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function room()
    {
        return $this->belongsTo(VibeRoom::class, 'vibe_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    public function getIsOnlineAttribute()
    {
        if (!$this->last_active_at) return false;

        // Active if within last 60 seconds
        return $this->last_active_at->gt(Carbon::now()->subSeconds(60));
    }
}
