<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OsintAnswer extends Model
{
    use HasFactory;

    protected $table = 'osint_answers';

    protected $fillable = [
        'task_id',
        'user_id',
        'question_key',
        'user_answer',
        'is_correct',
        'points_awarded',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'integer',
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