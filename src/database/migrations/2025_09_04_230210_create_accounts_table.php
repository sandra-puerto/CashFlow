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
        Schema::create('accounts', function (Blueprint $table) {

            # Llave primaria
            $table->uuid('id')->primary();

            /* Campos Principales */

                // Campo: Codigo
                $table->unsignedInteger('code')->unique();

                // Campo: Nombre
                $table->string('name', 255);

                // Campo: Naturaleza
                $table->enum('nature', ['debit', 'credit'])->comment('Naturaleza de la cuenta');

                // Campo: Descripcion (Opcional)
                $table->string('description', 255)->nullable();

                // FK: ID cuenta padre (Opcional)
                $table->uuid('parent_id')->nullable()->comment('ID de la cuenta padre');

                // Campo: Estado (Activo / Inactivo)
                $table->boolean('is_active')->default(true)->comment('Estado de la cuenta');
            //

            // Campos create_at y update_at
            $table->timestamps();

            /* Llaves Foraneas */

                // ID de la cuenta padre
                $table->foreign('parent_id')->references('id')->on('accounts')->noActionOnDelete();
            //

            /* Indices Personalizados */

                // Indice compuesto: Nombre unico de cuenta por padre
                $table->unique(['parent_id', 'name']);
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
