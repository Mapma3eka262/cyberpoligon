<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_accuracy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('forecast_id');
            $table->decimal('actual_value', 10, 2);
            $table->decimal('absolute_error', 10, 2);
            $table->decimal('percentage_error', 10, 2)->nullable();
            $table->decimal('squared_error', 10, 2);
            $table->string('evaluation_method'); // mape, mae, rmse, r2
            $table->json('metrics_summary')->nullable(); // накопленная статистика
            $table->timestamp('evaluated_at');
            $table->timestamps();
            
            $table->foreign('forecast_id')->references('id')->on('forecasts')->onDelete('cascade');
            $table->index('evaluated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_accuracy');
    }
};
