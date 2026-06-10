<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type'); // cpu, memory, disk, connections
            $table->string('forecast_method'); // linear_regression, moving_average, exponential_smoothing
            $table->decimal('forecast_value', 10, 2);
            $table->decimal('lower_bound', 10, 2)->nullable(); // 95% доверительный интервал
            $table->decimal('upper_bound', 10, 2)->nullable();
            $table->timestamp('forecasted_at'); // на какое время прогноз
            $table->integer('horizon_minutes'); // на сколько минут вперед
            $table->json('metadata')->nullable(); // дополнительные параметры
            $table->timestamps();
            
            $table->index(['metric_type', 'forecasted_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
