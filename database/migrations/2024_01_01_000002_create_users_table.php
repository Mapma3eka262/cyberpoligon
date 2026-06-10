<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('surname');
            $table->string('name');
            $table->string('patronymic')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'captain', 'participant'])->default('participant');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->string('temporary_password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};