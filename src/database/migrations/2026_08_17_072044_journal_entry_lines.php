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
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            /**
             * Llave Primaria
            */
            $table->id();

            /**
             * Campos principales de la línea del asiento
            */

            // Asiento contable al que pertenece esta línea
            $table->unsignedBigInteger('journal_entry_id')->comment('ID del asiento');

            // Cuenta contable afectada (catálogo PUC)
            $table->unsignedBigInteger('account_id')->comment('ID de la cuenta');

            // Número secuencial de la línea dentro del asiento (1, 2, 3...)
            $table->unsignedSmallInteger('line_number')->default(1)->comment('Número de línea dentro del asiento');

            // Descripción opcional para detallar la partida
            $table->string('description', 255)->nullable()->comment('Detalle de la partida');

            // Montos de la partida (siempre positivos)
            $table->decimal('debit', 15, 2)->default(0)->comment('Monto débito');
            $table->decimal('credit', 15, 2)->default(0)->comment('Monto crédito');

            /**
             * Auditoría de saldos (NIIF) – para trazabilidad y reconstrucción histórica
            */

            // Saldo de la cuenta antes de este movimiento
            $table->decimal('previous_balance', 15, 2)->default(0)->comment('Saldo antes del movimiento');

            // Saldo de la cuenta después de este movimiento
            $table->decimal('new_balance', 15, 2)->default(0)->comment('Saldo después del movimiento');

            $table->timestamps();

            /**
             * Llaves Foraneas
            */

            // Si se elimina el asiento, se eliminan sus líneas en cascada
            $table->foreign('journal_entry_id')
                  ->references('id')->on('journal_entries')
                  ->onDelete('cascade');

            // No permite borrar una cuenta que ya ha sido usada en líneas
            $table->foreign('account_id')
                  ->references('id')->on('accounts')
                  ->noActionOnDelete();

            /**
             * Índices para optimizar consultas
            */

            // Unicidad del consecutivo de línea dentro del mismo asiento
            $table->unique(['journal_entry_id', 'line_number']);

            // Consultas frecuentes por asiento y cuenta (reportes, auditoría)
            $table->index(['journal_entry_id', 'account_id']);

            // Consulta estrella para el Auxiliar de Cuenta (Libro Mayor por fechas)
            $table->index(['account_id', 'created_at']);

            // Índice para reconstrucción de saldos e historial de movimientos por cuenta
            $table->index(['account_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};