<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_task', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('task_id');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->boolean('flag1_found')->default(false);
            $table->boolean('flag2_found')->default(false);
            $table->integer('wrong_attempts_count')->default(0);
            $table->integer('score')->default(0);
            $table->timestamps();
            
            $table->unique(['team_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_task');
    }
};