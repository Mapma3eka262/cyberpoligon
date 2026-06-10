<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserTask extends Model
{
    use HasFactory;

    protected $table = 'user_tasks';

    protected $fillable = [
        'user_id',
        'task_id',
        'start_time',
        'end_time',
        'score',
        'completed',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function answers()
    {
        return $this->hasMany(OsintAnswer::class, 'task_id', 'task_id')
            ->where('user_id', $this->user_id);
    }

    public function hintUsage()
    {
        return $this->hasMany(OsintHintUsage::class, 'task_id', 'task_id')
            ->where('user_id', $this->user_id);
    }

    public function isActive()
    {
        if ($this->completed) return false;
        if ($this->end_time && now()->gt($this->end_time)) return false;
        return true;
    }

    public function getRemainingTime()
    {
        if (!$this->end_time) return null;
        if (now()->gt($this->end_time)) return ['is_expired' => true];
        
        $diff = $this->end_time->diffInSeconds(now());
        return [
            'hours' => floor($diff / 3600),
            'minutes' => floor(($diff % 3600) / 60),
            'seconds' => $diff % 60,
            'is_expired' => false,
        ];
    }
}