<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerSkillConnect extends Model
{
    use HasFactory;

    protected $table = 'answer_skill_connects';

    /**
     * Fields that can be mass assigned
     */
    protected $fillable = [
        'question_id',
        'user_id',
        'answer',
        'is_bullet',
        'type',
        'is_active',
    ];

    /**
     * Type casting for model attributes
     */
    protected $casts = [
        'is_bullet' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    // Each answer belongs to one SkillConnect question
    public function question()
    {
        return $this->belongsTo(QuestionsSkillConnect::class, 'question_id');
    }

    // Each answer belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }
}
