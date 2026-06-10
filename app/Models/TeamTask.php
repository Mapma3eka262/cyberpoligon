<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamTask extends Pivot
{
    protected $table = 'team_task';
    
    public $incrementing = true;
    
    protected $casts = [
        'flag1_found' => 'boolean',
        'flag2_found' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function flagAttempts()
    {
        return $this->hasMany(FlagAttempt::class, 'team_task_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
