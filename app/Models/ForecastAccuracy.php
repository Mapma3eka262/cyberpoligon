<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ForecastAccuracy extends Model
{
    use HasFactory;

    protected $table = 'forecast_accuracy';
    
    protected $fillable = [
        'forecast_id',
        'actual_value',
        'absolute_error',
        'percentage_error',
        'squared_error',
        'evaluation_method',
        'metrics_summary',
        'evaluated_at',
    ];

    protected $casts = [
        'actual_value' => 'decimal:2',
        'absolute_error' => 'decimal:2',
        'percentage_error' => 'decimal:2',
        'squared_error' => 'decimal:2',
        'metrics_summary' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function forecast()
    {
        return $this->belongsTo(Forecast::class);
    }
}
