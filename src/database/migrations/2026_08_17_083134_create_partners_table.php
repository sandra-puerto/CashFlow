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
        Schema::create('partners', function (Blueprint $table) {
            /**
             * Llave primaria
            */

            // Identificador único del tercero
            $table->id();

            /**
             * Datos de identificación (NIIF / DIAN)
            */

            // Tipo de documento de identidad (CC, NIT, CE, PAS, etc.)
            $table->foreignId('document_type_id')
                  ->comment('Tipo de documento (CC, NIT, CE, etc.)')
                  ->constrained('document_types')
                  ->noActionOnDelete();

            // Número de identificación único
            $table->string('document_number', 20)->unique()->comment('Número de identificación');

            // Dígito de verificación para NIT (requerido por la DIAN)
            $table->char('dv', 1)->nullable()->comment('Dígito de verificación (para NIT)');

            // Razón social o nombre completo
            $table->string('name', 255)->comment('Razón social o nombre completo');

            // Nombre comercial (opcional)
            $table->string('commercial_name', 255)->nullable()->comment('Nombre comercial');

            /**
             * Clasificación del tercero
            */

            // Puede ser cliente, proveedor, empleado, o múltiples a la vez
            $table->boolean('is_customer')->default(false)->comment('Es cliente?');
            $table->boolean('is_supplier')->default(false)->comment('Es proveedor?');
            $table->boolean('is_employee')->default(false)->comment('Es empleado?');

            /**
             * Estado operativo
            */

            // Permite inhabilitar un tercero sin borrarlo (preserva trazabilidad NIIF)
            $table->boolean('is_active')->default(true)->comment('Estado operativo del tercero');

            /**
             * Datos de contacto
            */

            // Correo electrónico (facturación y notificaciones)
            $table->string('email', 255)->nullable()->comment('Correo electrónico');

            // Teléfono
            $table->string('phone', 20)->nullable()->comment('Teléfono');

            // Dirección física
            $table->string('address', 255)->nullable()->comment('Dirección');

            /**
             * Datos fiscales (opcional)
            */

            // Régimen tributario (ej. simplificado, común, gran contribuyente, etc.)
            $table->string('tax_regime', 50)->nullable()->comment('Régimen tributario');

            /**
             * Auditoría NIIF
            */

            // Usuario que creó el tercero
            $table->unsignedBigInteger('created_by')->nullable()->comment('Usuario que creó');

            // Último usuario que modificó
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Último usuario que modificó');

            $table->timestamps();

            /**
             * Llaves foráneas
            */

            // Auditoría: creado por
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            // Auditoría: modificado por
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Índices para consultas rápidas
            */

            // Búsqueda rápida de terceros por tipo y número de documento (frecuente)
            $table->index(['document_type_id', 'document_number']);

            // Búsquedas y autocompletado por razón social/nombre
            $table->index('name');

            // Filtros rápidos para autocompletar clientes o proveedores activos
            $table->index(['is_active', 'is_customer']);
            $table->index(['is_active', 'is_supplier']);
            $table->index(['is_active', 'is_employee']);

            // Auditoría
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};