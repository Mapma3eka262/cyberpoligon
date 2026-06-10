<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Отключаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Внешние ключи для users (team_id -> teams.id)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'team_id_foreign')) {
                $table->foreign('team_id')
                      ->references('id')
                      ->on('teams')
                      ->onDelete('set null');
            }
        });

        // Внешние ключи для teams (captain_id -> users.id)
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'captain_id_foreign')) {
                $table->foreign('captain_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }
        });

        // Внешние ключи для team_task
        Schema::table('team_task', function (Blueprint $table) {
            if (!Schema::hasColumn('team_task', 'team_id_foreign')) {
                $table->foreign('team_id')
                      ->references('id')
                      ->on('teams')
                      ->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('team_task', 'task_id_foreign')) {
                $table->foreign('task_id')
                      ->references('id')
                      ->on('tasks')
                      ->onDelete('cascade');
            }
        });

        // Внешние ключи для flag_attempts
        Schema::table('flag_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('flag_attempts', 'user_id_foreign')) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('flag_attempts', 'team_task_id_foreign')) {
                $table->foreign('team_task_id')
                      ->references('id')
                      ->on('team_task')
                      ->onDelete('cascade');
            }
        });

        // Включаем проверку внешних ключей обратно
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('flag_attempts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['team_task_id']);
        });

        Schema::table('team_task', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['task_id']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['captain_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};