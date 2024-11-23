<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_guard',
        'is_fireman',
        'isAuthor',
        'is_fire_fighter',
        'is_peace_keeper',
        'is_guardian_angel',
        'is_weesdom_keeper',
        'is_shadow_hunter',
        'is_whistleblower',
        'fcm_token',
        'is_community_builder',
        'mobile',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_guard' => 'boolean',
        'is_fireman' => 'boolean',
        'is_fire_fighter' => 'boolean',
        'is_peace_keeper' => 'boolean',
        'is_guardian_angel' => 'boolean',
        'isAuthor' => 'boolean',
        'is_weesdom_keeper' => 'boolean',
        'is_shadow_hunter' => 'boolean',
        'is_whistleblower' => 'boolean',
        'is_community_builder' => 'boolean',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }
  
}
