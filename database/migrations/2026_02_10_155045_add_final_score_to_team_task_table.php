<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('team_task', function (Blueprint $table) {
            if (!Schema::hasColumn('team_task', 'final_score')) {
                $table->integer('final_score')->nullable()->after('wrong_attempts_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_task', function (Blueprint $table) {
            //
        });
    }
};
