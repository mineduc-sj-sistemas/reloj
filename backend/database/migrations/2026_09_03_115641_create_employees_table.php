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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('pin')->unique(); // Legajo / ID en el reloj ZKTeco
            $table->string('name')->nullable(); // Nombre y Apellido
            $table->string('dni')->nullable();
            $table->string('department')->nullable();
            $table->string('card_number')->nullable();
            $table->integer('privilege')->default(0); // 0: Usuario normal, 14: Administrador
            $table->string('password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
