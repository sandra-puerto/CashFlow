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
            $table->unsignedInteger('id')->primary()->autoIncrement();

            /* Campos Principales */

                // Campo: Codigo
                $table->unsignedInteger('code')->unique();

                // Campo: Nombre
                $table->string('name', 255)->unique();

                // Campo: Descripcion (Opcional)
                $table->string('description', 255)->nullable();

                // FK: ID cuenta padre (Opcional)
                $table->unsignedInteger('parent_id')->nullable()->comment('ID de la cuenta padre');
            //

            // Campos create_at y update_at
            $table->timestamps();

            /* Llaves Foraneas */

                // ID de la cuenta padre
                $table->foreign('parent_id')->references('id')->on('accounts')->noActionOnDelete();
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
