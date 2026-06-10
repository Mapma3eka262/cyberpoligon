<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamTasksTable extends Migration
{
    public function up()
    {
        Schema::create('team_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('flag1_found')->default(false);
            $table->boolean('flag2_found')->default(false);
            $table->integer('score')->default(0);
            $table->unsignedInteger('wrong_attempts_count')->default(0);
            $table->integer('final_score')->nullable();
            $table->timestamps();
            
            $table->unique(['team_id', 'task_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_tasks');
    }
}
