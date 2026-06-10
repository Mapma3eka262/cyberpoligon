<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'cpu_usage',
        'memory_usage',
        'disk_usage',
        'active_connections',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'cpu_usage' => 'decimal:2',
        'memory_usage' => 'decimal:2',
        'disk_usage' => 'decimal:2',
    ];
}