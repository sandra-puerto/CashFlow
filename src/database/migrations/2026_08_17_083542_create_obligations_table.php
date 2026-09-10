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
        Schema::create('obligations', function (Blueprint $table) {
            /**
             * Llave primaria
            */

            // Identificador único de la obligación
            $table->id();

            /**
             * Relación con el origen contable (opcional)
            */

            // Asiento contable que originó esta obligación (opcional para trazabilidad NIIF)
            $table->foreignId('journal_entry_id')
                  ->nullable()
                  ->comment('Asiento contable origen (opcional)')
                  ->constrained('journal_entries')
                  ->nullOnDelete();

            /**
             * Datos de la obligación (CXP / CXC)
            */

            // Tipo: CXP (cuenta por pagar) o CXC (cuenta por cobrar)
            $table->string('type', 10)->comment('Tipo: CXP (pagar) o CXC (cobrar)');

            // Tercero asociado (cliente o proveedor)
            $table->foreignId('partner_id')
                  ->comment('Tercero asociado (proveedor/cliente)')
                  ->constrained('partners')
                  ->noActionOnDelete();

            // Número de documento soporte (factura, cuenta de cobro, etc.)
            $table->string('document_number', 50)->comment('Número de documento soporte');

            // Fecha de emisión del documento
            $table->date('issue_date')->comment('Fecha de emisión');

            // Fecha de vencimiento para gestión de cartera
            $table->date('due_date')->nullable()->comment('Fecha de vencimiento');

            // Monto total original de la obligación
            $table->decimal('total_amount', 15, 2)->comment('Monto total original');

            // Saldo pendiente actual (se actualiza con abonos/pagos)
            $table->decimal('balance', 15, 2)->comment('Saldo pendiente actual');

            /**
             * Estado de la obligación
            */

            // Estado operativo: pending (pendiente), partial (parcial), paid (pagada), cancelled (anulada)
            $table->string('status', 20)->default('pending')->comment('Estado: pending, partial, paid, cancelled');

            /**
             * Auditoría NIIF (trazabilidad de creación y modificación)
            */

            // Usuario que creó la obligación (obligatorio)
            $table->unsignedBigInteger('created_by')->comment('Usuario que creó la obligación');

            // Último usuario que modificó (opcional)
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Último usuario que modificó');

            $table->timestamps();

            /**
             * Llaves foráneas
            */

            // Auditoría: creado por
            $table->foreign('created_by')->references('id')->on('users')->noActionOnDelete();

            // Auditoría: modificado por
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Restricciones e Índices para alto rendimiento
            */

            // Evita duplicar el mismo documento para el mismo tercero y tipo de obligación (ej. Factura 1020 de Proveedor X)
            $table->unique(['partner_id', 'type', 'document_number'], 'obligations_partner_type_doc_unique');

            // Reporte estrella de Cartera / Vencimientos (Filtrar CXC/CXP pendientes ordenadas por fecha de vencimiento)
            $table->index(['type', 'status', 'due_date'], 'obligations_type_status_due_index');

            // Consulta rápida de estado de cuenta de un tercero específico (cuánto debe o se le debe a X tercero)
            $table->index(['partner_id', 'status'], 'obligations_partner_status_index');

            // Auditoría: obligaciones creadas por un usuario
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obligations');
    }
};