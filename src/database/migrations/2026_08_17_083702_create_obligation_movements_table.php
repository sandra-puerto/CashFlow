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
        Schema::create('obligation_movements', function (Blueprint $table) {
            /**
             * Llave primaria
            */

            // Identificador único del movimiento
            $table->id();

            /**
             * Relación con la obligación (CXP/CXC)
            */

            // Obligación a la que pertenece este movimiento
            $table->foreignId('obligation_id')
                  ->comment('ID de la obligación')
                  ->constrained('obligations')
                  ->cascadeOnDelete();

            /**
             * Relación con el asiento contable
            */

            // Asiento que genera este movimiento (pago, cargo, ajuste, etc.)
            $table->foreignId('journal_entry_id')
                  ->comment('ID del asiento contable que genera este movimiento')
                  ->constrained('journal_entries')
                  ->noActionOnDelete();

            /**
             * Datos del movimiento
            */

            // Tipo de movimiento: payment (pago/abono), charge (cargo), adjustment (ajuste)
            $table->string('type', 20)->comment('Tipo: payment (pago/abono), charge (cargo), adjustment (ajuste)');

            // Monto del movimiento (siempre positivo)
            $table->decimal('amount', 15, 2)->comment('Monto del movimiento');

            // Fecha del movimiento (generalmente la misma del asiento)
            $table->date('date')->comment('Fecha del movimiento');

            // Descripción adicional (opcional)
            $table->string('description', 255)->nullable()->comment('Descripción adicional');

            /**
             * Auditoría NIIF de Saldos (Trazabilidad y Reconstrucción Histórica)
            */

            // Saldo de la obligación inmediatamente antes de este movimiento
            $table->decimal('previous_balance', 15, 2)->default(0)->comment('Saldo previo al movimiento');

            // Saldo de la obligación inmediatamente después de este movimiento
            $table->decimal('new_balance', 15, 2)->default(0)->comment('Saldo posterior al movimiento');

            /**
             * Auditoría NIIF (trazabilidad de creación y modificación)
            */

            // Usuario que creó el movimiento
            $table->unsignedBigInteger('created_by')->comment('Usuario que creó el movimiento');

            // Último usuario que modificó el movimiento
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Último usuario que modificó');

            $table->timestamps();

            /**
             * Llaves foráneas
            */

            // No permite eliminar un usuario que haya creado movimientos
            $table->foreign('created_by')->references('id')->on('users')->noActionOnDelete();

            // Si se elimina el usuario que modificó, se pone NULL
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Índices para consultas rápidas
            */

            // Historial cronológico de movimientos de una obligación (Kardex / Extracto de Cuenta)
            $table->index(['obligation_id', 'date'], 'om_obligation_date_index');

            // Consultas por asiento (para saber qué movimientos generó un asiento contable)
            $table->index('journal_entry_id');

            // Reportes de recaudo/pagos filtrados por tipo y rango de fechas
            $table->index(['type', 'date'], 'om_type_date_index');

            // Auditoría: movimientos creados por un usuario
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obligation_movements');
    }
};