<?php

// app/Models/Badge.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Badges extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'role', 'icon', 'power', 'limitation',
        'is_active', 'count', 'rules', 'tips',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'count'     => 'integer',
        'rules'     => 'array',
        'tips'      => 'array',
    ];
}

