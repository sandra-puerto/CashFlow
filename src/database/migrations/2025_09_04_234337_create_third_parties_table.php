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
        Schema::create('third_parties', function (Blueprint $table) {

            # PK: Llave primaria
            $table->unsignedInteger('id')->primary()->autoIncrement();

            /* Campos Principales */

                // Campo: Nombres
                $table->string('names', 255);

                // Campo Apellidos (Opcional)
                $table->string('surnames', 255)->nullable();

                // FK: Tipo de Documento
                $table->unsignedInteger('doc_type_id');

                // Campo: Numero de Documento
                $table->unsignedBigInteger('doc_num')->unique();

                // Campo: Correo Electronico
                $table->string('email')->unique()->nullable();

                // Campo: Numero telefonico
                $table->unsignedBigInteger('phone')->unique();
            //

            // Campos create_at y update_at
            $table->timestamps();

            /* Llaves Foraneas */

                // Tipo de Documento
                $table->foreign('doc_type_id')->references('id')->on('document_types');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_parties');
    }
};
