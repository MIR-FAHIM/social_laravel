<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentFlag extends Model
{
    use HasFactory;

    protected $table = 'content_flags';

    protected $fillable = [
        'flag_name',
        'note',
        'score',
        'is_positive',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'score' => 'integer',
    ];
}
