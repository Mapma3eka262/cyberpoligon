<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'captain_id'];

    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function members()
    {
        return $this->hasMany(User::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'team_task')
            ->using(TeamTask::class)
            ->withPivot(['start_time', 'end_time', 'flag1_found', 'flag2_found', 'wrong_attempts_count', 'score'])
            ->withTimestamps();
    }

    public function activeTask()
    {
        return $this->tasks()
            ->where('type', 'ctf')
            ->wherePivot('end_time', '>', now())
            ->orderBy('team_task.end_time', 'desc')
            ->first();
    }
}