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
            // Identificador único de la cuenta contable
            $table->id();

            /**
             * Campos principales del PUC (Plan Único de Cuentas)
            */

            // Código único de la cuenta según el PUC (ej. 110505)
            $table->string('code', 10)->unique()->comment('Código PUC completo sin puntos');

            // Nombre descriptivo de la cuenta
            $table->string('name', 255)->comment('Nombre de la cuenta');

            // Naturaleza: Débito (activos/gastos) o Crédito (pasivos/ingresos)
            $table->enum('nature', ['debit', 'credit'])->comment('Naturaleza de la cuenta');

            // Nivel jerárquico (1=Clase, 2=Grupo, 3=Cuenta, 4=Subcuenta, 5=Auxiliar)
            $table->tinyInteger('level')->unsigned()->comment('Nivel jerárquico (1-5)');

            // Descripción adicional (opcional)
            $table->string('description', 255)->nullable()->comment('Descripción adicional');

            // Relación recursiva: cuenta padre (para mantener la jerarquía)
            $table->unsignedBigInteger('parent_id')->nullable()->comment('ID de la cuenta padre');

            /**
             * Banderas de control de estado y origen del PUC
            */

            // Estado operativo de la cuenta (disponibilidad para registrar nuevos asientos)
            $table->boolean('is_active')->default(true)->comment('Estado operativo de la cuenta');

            // Protege la estructura original del PUC contra ediciones indebidas
            $table->boolean('is_primary')->default(false)->comment('Indica si es cuenta base del PUC original');

            /**
             * Saldo actual (para consultas rápidas en O(1))
            */
            $table->decimal('current_balance', 15, 2)->default(0)->comment('Saldo actual de la cuenta');

            /**
             * Auditoría NIIF (trazabilidad de creación y modificación)
            */
            $table->unsignedBigInteger('created_by')->nullable()->comment('Usuario que creó la cuenta');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Último usuario que modificó');
            $table->timestamps();

            /**
             * Llaves foráneas
            */
            $table->foreign('parent_id')->references('id')->on('accounts')->noActionOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Restricciones de unicidad e índices para rendimiento
            */
            
            // Evita nombres duplicados bajo una misma cuenta padre
            $table->unique(['parent_id', 'name']);

            // Índice compuesto para autocompletado y búsquedas de cuentas activas
            $table->index(['is_active', 'code']);

            // Índice para construcción de árbol jerárquico
            $table->index(['parent_id', 'level']);

            // Índices adicionales para filtrados rápidos y reportes
            $table->index('is_primary');
            $table->index('code');
            $table->index('current_balance');
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