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
        Schema::create('funds', function (Blueprint $table) {

            # Llave primaria
            $table->uuid('id')->primary();

            /* Campos principales */

                // Campo: Nombre
                $table->string('name', 255)->unique();

                // Campo: Descripcion (Opcional)
                $table->string('descripcion', 255)->nullable();
            //

            // Campos create_at y update_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funds');
    }
};
