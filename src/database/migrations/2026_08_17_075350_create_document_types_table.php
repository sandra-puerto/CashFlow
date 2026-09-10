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
            /**
             * Llave primaria
            */

            // Identificador único del tipo de documento
            $table->id();

            /**
             * Datos del tipo de documento
            */

            // Código oficial DIAN/Sistema para reportes y facturación electrónica (ej. 13=CC, 31=NIT, 22=CE)
            $table->string('code', 10)->nullable()->unique()->comment('Código oficial DIAN o de integración');

            // Nombre descriptivo (ej. Cédula de Ciudadanía, NIT, etc.)
            $table->string('name', 50)->unique()->comment('Nombre del documento (Cédula de Ciudadanía, NIT, etc.)');

            // Abreviatura corta (ej. CC, NIT, CE, etc.)
            $table->string('abbreviation', 10)->nullable()->unique()->comment('Abreviatura única (CC, NIT, CE, etc.)');

            /**
             * Banderas de control de estado y origen del sistema
            */

            // Estado operativo para disponibilidad en formularios
            $table->boolean('is_active')->default(true)->comment('Estado operativo del tipo de documento');

            // Protege los tipos de documento base sembrados por el sistema (CC, NIT, etc.) contra edición/borrado
            $table->boolean('is_primary')->default(false)->comment('Indica si es un tipo de documento base del sistema');

            /**
             * Auditoría NIIF (trazabilidad de creación y modificación)
            */

            // Usuario que creó el tipo de documento (opcional)
            $table->unsignedBigInteger('created_by')->nullable()->comment('Usuario que creó el tipo de documento');

            // Último usuario que modificó (opcional)
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Último usuario que modificó');

            $table->timestamps();

            /**
             * Llaves foráneas
            */

            // Si se elimina el usuario creador, se pone NULL
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            // Si se elimina el usuario que modificó, se pone NULL
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Índices para consultas rápidas
            */

            // Búsqueda rápida de tipos de documento activos para selectores de formularios
            $table->index(['is_active', 'name']);

            // Auditoría: tipos creados por un usuario
            $table->index('created_by');
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