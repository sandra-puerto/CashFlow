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
        Schema::create('document_types', function (Blueprint $table) {
            
            # Llave primaria
            $table->uuid('id')->primary();

            /* Campos principales */

                // Campo: Nombre
                $table->string('name', 50)->unique();

                // Campo: Abreviatura
                $table->string('abbreviation', 50)->unique();
            //

            // Campos update_at y create_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
