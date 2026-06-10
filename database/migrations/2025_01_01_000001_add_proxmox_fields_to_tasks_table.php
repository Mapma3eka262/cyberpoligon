// database/migrations/2025_01_01_000001_add_proxmox_fields_to_tasks_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('vm_id')->nullable()->after('target_ip');
            $table->string('proxmox_node')->nullable()->after('vm_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['vm_id', 'proxmox_node']);
        });
    }
};
