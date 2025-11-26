<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoodMaster extends Model
{
    use HasFactory;

    protected $table = 'mood_masters';

    protected $fillable = [
        'mood_name',
        'mood_icon',
        'mood_color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Always return color in lowercase & valid format.
     */
    public function getMoodColorAttribute($value)
    {
        return $value ? strtolower(trim($value)) : null;
    }

    /**
     * Scope: only active moods
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relationship: A mood can be used in many vibe rooms
     */
    public function vibeRooms()
    {
        return $this->hasMany(VibeRoom::class, 'mood_id');
    }
}
