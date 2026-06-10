<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlagAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_task_id',
        'flag_number',
        'attempt_value',
        'is_correct',
    ];

    protected $casts = [
        'flag_number' => 'integer',
        'is_correct' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teamTask()
    {
        return $this->belongsTo(TeamTask::class);
    }
}
