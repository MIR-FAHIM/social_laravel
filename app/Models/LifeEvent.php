<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LifeEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'description', 'date', 'start_date', 'end_date', 'event_type', 'user_id',
        'isEducation', 'isOffice', 'isGeneral', 'icon_id', 'status', 'isPublic'
    ];
}
