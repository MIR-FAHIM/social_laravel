<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddFlagOnContent extends Model
{
    use HasFactory;

    protected $table = 'add_flag_on_contents';

    protected $fillable = [
        'content_id',
        'flag_id',
        'flagged_by',
        'is_reviewed',
        'comment',
        'review_note',
    ];

    // Default attributes
    protected $attributes = [
        'is_reviewed' => false,
    ];

    /**
     * Relationships
     */

    // Each flag belongs to one content
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    // Each flag belongs to one flag type (like “Misinformation”)
    public function flag()
    {
        return $this->belongsTo(ContentFlag::class, 'flag_id');
    }

    // User who flagged the content
    public function user()
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }
}
