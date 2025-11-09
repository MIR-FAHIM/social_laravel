<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionsSkillConnect extends Model
{
    use HasFactory;

    protected $table = 'questions_skill_connects';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'question',
        'hint_answer',
        'order',
        'is_active',
    ];

    /**
     * Default attribute casting.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Default sorting: always show active questions by order.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->orderBy('order', 'asc');
        });
    }

    /**
     * Scope to fetch only active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
