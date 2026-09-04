<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->unique();
            $table->string('alias')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('push_version')->nullable();
            $table->integer('user_count')->default(0);
            $table->integer('finger_count')->default(0);
            $table->integer('face_count')->default(0);
            $table->integer('att_count')->default(0);
            $table->string('status')->default('online');
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn')->index();
            $table->string('user_pin')->index();
            $table->dateTime('punch_time')->index();
            $table->integer('status')->default(0); // 0: Check-In, 1: Check-Out, 2: Break-Out, 3: Break-In, 4: Overtime-In, 5: Overtime-Out
            $table->integer('verify_type')->default(0); // 0: Password, 1: Fingerprint, 2: Card, 15: Face
            $table->string('work_code')->nullable();
            $table->string('reserved_1')->nullable();
            $table->string('reserved_2')->nullable();
            $table->text('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['device_sn', 'user_pin', 'punch_time'], 'device_user_punch_unique');
        });

        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn')->index();
            $table->string('command_id')->nullable();
            $table->text('command');
            $table->string('status')->default('pending'); // pending, sent, executed, failed
            $table->text('response')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_commands');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('devices');
    }
};
