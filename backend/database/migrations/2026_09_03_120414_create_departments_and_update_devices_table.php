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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej. Ministerio de Educación, Hospital Central, Mesa de Entradas
            $table->string('code')->nullable(); // Ej. MIN-EDU-01
            $table->string('legal_instrument')->nullable(); // Ej. Decreto N° 1245/2020, Resolución 452
            $table->string('address')->nullable(); // Dirección física
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete(); // Jerarquía
            $table->timestamps();
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('location_description')->nullable(); // Ej. Puerta Principal, Acceso Guardia
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'location_description']);
        });

        Schema::dropIfExists('departments');
    }
};
