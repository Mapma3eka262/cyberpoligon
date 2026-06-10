<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'surname',
        'name',
        'patronymic',
        'phone',
        'email',
        'role',
        'team_id',
        'temporary_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'temporary_password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCaptain()
    {
        return $this->role === 'captain';
    }

    public function isParticipant()
    {
        return $this->role === 'participant';
    }

    public function flagAttempts()
    {
        return $this->hasMany(FlagAttempt::class);
    }

    // OSINT
    public function userTasks()
    {
        return $this->hasMany(UserTask::class);
    }

    public function osintAnswers()
    {
        return $this->hasMany(OsintAnswer::class);
    }

    public function osintHintUsage()
    {
        return $this->hasMany(OsintHintUsage::class);
    }
}