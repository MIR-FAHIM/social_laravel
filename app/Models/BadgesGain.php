<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadgesGain extends Model
{
    use HasFactory;

    protected $table = 'badges_gains';

    protected $fillable = [
        'user_id',
        'badge_id',
        'is_active',
        'percentage',
        'count',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'percentage' => 'float',
        'count' => 'integer',
    ];

    /**
     * Relationships
     */

    // Each badge gain belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Each badge gain belongs to a specific badge
    public function badge()
    {
        return $this->belongsTo(Badges::class);
    }

    /**
     * Accessors & Helpers
     */

    // Example: display badge progress as a formatted string
    public function getProgressLabelAttribute(): string
    {
        return number_format($this->percentage, 1) . '% complete';
    }

    // Example: determine if user has fully unlocked the badge
    public function getIsUnlockedAttribute(): bool
    {
        return $this->percentage >= 100;
    }
}
