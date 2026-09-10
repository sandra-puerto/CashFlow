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
        Schema::create('documents', function (Blueprint $table) {
            /**
             * Llave primaria ULID (ordenable y seguro)
            */
            
            // Identificador único del documento, usando ULID para ofuscación y orden cronológico
            $table->ulid('id')->primary();

            /**
             * Relación con el asiento contable
            */

            // Asiento al que pertenece este documento soporte
            $table->unsignedBigInteger('journal_entry_id')->comment('ID del asiento al que pertenece este documento');

            /**
             * Datos del archivo y almacenamiento
            */

            // Nombre original del archivo subido por el usuario
            $table->string('original_name', 255)->comment('Nombre original del archivo');

            // Disco de almacenamiento en Laravel (ej. local, public, s3, minio)
            $table->string('disk', 50)->default('local')->comment('Disco de almacenamiento (local, s3, etc.)');

            // Ruta interna donde se almacena el archivo en el sistema de archivos
            $table->string('file_path', 500)->comment('Ruta interna en el almacenamiento');

            // Hash del archivo (SHA-256) para auditoría de integridad y evitar adjuntos duplicados
            $table->string('file_hash', 64)->nullable()->comment('Hash SHA-256 para verificación de integridad');

            // Tamaño del archivo en bytes (para control de cuota de almacenamiento)
            $table->unsignedInteger('file_size')->comment('Tamaño en bytes (hasta 4GB)');

            // Tipo MIME del archivo (ej. application/pdf, image/jpeg)
            $table->string('mime_type', 100)->comment('Tipo MIME del archivo');

            /**
             * Clasificación del documento (genérica y desacoplada)
            */

            // Tipo de documento: factura, soporte, etc. Se usa string para flexibilidad
            $table->string('type', 50)->default('other')->comment('Tipo de documento (ej. factura, soporte, etc.)');

            /**
             * Metadatos en JSON (datos adicionales)
            */

            // Campos adicionales como número de factura, NIT, etc. en formato JSON
            $table->json('metadata')->nullable()->comment('Datos adicionales en formato JSON');

            /**
             * Auditoría NIIF (trazabilidad de creación y modificación)
            */

            // Usuario que subió el documento (obligatorio)
            $table->unsignedBigInteger('created_by')->comment('Usuario que subió el documento');

            // Último usuario que modificó el documento (opcional)
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Último usuario que modificó el documento');

            $table->timestamps();

            /**
             * Llaves foráneas
            */

            // Si se elimina el asiento, se eliminan sus documentos en cascada
            $table->foreign('journal_entry_id')
                  ->references('id')->on('journal_entries')
                  ->onDelete('cascade');

            // No permite eliminar un usuario que haya subido documentos
            $table->foreign('created_by')->references('id')->on('users')->noActionOnDelete();

            // Si se elimina el usuario que modificó, se pone NULL
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Índices para optimizar consultas
            */

            // Búsqueda de documentos de un asiento por tipo (ej. ver solo las facturas del asiento X)
            $table->index(['journal_entry_id', 'type']);

            // Búsqueda rápida de duplicados o validaciones por Hash de archivo
            $table->index('file_hash');

            // Auditoría: documentos subidos por un usuario
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};