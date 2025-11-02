<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentReaction extends Model
{
    use HasFactory;
    
        protected $fillable = [
            'user_id', 'content_id', 'reaction_type', 'isComment'
        ];
    
        // Relationships
        public function user() {
            return $this->belongsTo(User::class);
        }
    
        public function content() {
            return $this->belongsTo(Content::class);
        }
}
