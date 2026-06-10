<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Forecast extends Model
{
    use HasFactory;

    protected $table = 'forecasts';
    
    protected $fillable = [
        'metric_type',
        'forecast_method',
        'forecast_value',
        'lower_bound',
        'upper_bound',
        'forecasted_at',
        'horizon_minutes',
        'metadata',
    ];

    protected $casts = [
        'forecast_value' => 'decimal:2',
        'lower_bound' => 'decimal:2',
        'upper_bound' => 'decimal:2',
        'forecasted_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function accuracyRecords()
    {
        return $this->hasMany(ForecastAccuracy::class, 'forecast_id');
    }

    public function getLatestAccuracy(): ?ForecastAccuracy
    {
        return $this->accuracyRecords()->latest('evaluated_at')->first();
    }
}
