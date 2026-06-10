<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OsintHintUsage extends Model
{
    use HasFactory;

    protected $table = 'osint_hint_usage';

    protected $fillable = [
        'task_id',
        'user_id',
        'hint_index',
        'penalty_points',
    ];

    protected $casts = [
        'penalty_points' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}