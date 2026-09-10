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
        Schema::create('journal_entries', function (Blueprint $table) {
            /**
             * Llave Primaria
             */
            $table->id();

            /**
             * Campos principales del asiento
            */

            // Número consecutivo del asiento
            $table->unsignedInteger('number')->comment('Número consecutivo del asiento');

            // Año contable del registro
            $table->unsignedSmallInteger('year')->comment('Año contable');

            // Fecha y hora exacta de contabilización del comprobante
            $table->datetime('datetime')->comment('Fecha y hora de contabilización');

            // Concepto o descripción general del asiento contable
            $table->string('concept', 255)->comment('Concepto o descripción general');

            /**
             * Clasificación y estado
            */

            // Tipo de comprobante o asiento (ej. diario, egreso, ingreso, ajuste, cierre)
            $table->string('type', 50)->default('diario')->comment('Tipo de comprobante/asiento');

            // Estado actual en el ciclo de vida del asiento contable
            $table->enum('status', ['borrador', 'contabilizado', 'anulado'])
                ->default('borrador')
                ->comment('Estado actual del asiento');

            /**
             * Auditoría NIIF de creación y modificación
            */

            // Usuario responsable de la creación del asiento
            $table->unsignedBigInteger('created_by')->nullable()->comment('Usuario que creó el asiento');

            // Último usuario que realizó modificaciones al registro
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Último usuario que modificó');

            /**
             * Trazabilidad inalterable para anulaciones (NIIF)
            */

            // Usuario que ejecutó la anulación del asiento
            $table->unsignedBigInteger('annulled_by')->nullable()->comment('Usuario que anuló el asiento');

            // Fecha y hora exacta en que se efectuó la anulación
            $table->timestamp('annulled_at')->nullable()->comment('Fecha y hora exacta de la anulación');

            // Motivo o justificación documentada de la anulación
            $table->string('annulment_reason', 255)->nullable()->comment('Motivo o justificación de la anulación');

            $table->timestamps();

            /**
             * Llaves Foráneas
            */

            // Si se elimina el usuario creador, se deja el campo en nulo para preservar el historial
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            // Si se elimina el usuario modificador, se deja el campo en nulo
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            // Si se elimina el usuario anulador, se preserva el registro de anulación dejando el campo en nulo
            $table->foreign('annulled_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Restricciones de unicidad
            */

            // Garantiza que el número consecutivo sea único por año y tipo de comprobante
            $table->unique(['number', 'year', 'type'], 'journal_entries_number_year_type_unique');

            /**
             * Índices de alto rendimiento para reportes contables
            */

            // Crucial: Los reportes (Balance, Libro Mayor) filtran por fecha Y solo incluyen estado 'contabilizado'
            $table->index(['status', 'datetime']);

            // Optimiza consultas agrupadas por periodo anual y tipo de asiento
            $table->index(['year', 'type']);

            // Índice para consultas de auditoría filtradas por el usuario creador
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};