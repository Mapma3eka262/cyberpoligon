<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target_ip',
        'subnet_mask',
        'flag1_hash',
        'flag2_hash',
        'time_limit_minutes',
        'is_active',
        'vm_id',
        'proxmox_node',
        'type',
        'osint_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'osint_data' => 'array',
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_task')
            ->using(TeamTask::class)
            ->withPivot(['start_time', 'end_time', 'flag1_found', 'flag2_found', 'wrong_attempts_count', 'score'])
            ->withTimestamps();
    }

    public function userTasks()
    {
        return $this->hasMany(UserTask::class);
    }

    public function isCtf()
    {
        return $this->type === 'ctf' || $this->type === null;
    }

    public function isOsint()
    {
        return $this->type === 'osint';
    }
}