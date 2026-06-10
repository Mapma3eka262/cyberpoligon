<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('osint_hint_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('hint_index');
            $table->integer('penalty_points');
            $table->timestamps();
            
            $table->unique(['task_id', 'user_id', 'hint_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osint_hint_usage');
    }
};