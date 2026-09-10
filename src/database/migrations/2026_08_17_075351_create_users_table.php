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
        Schema::create('users', function (Blueprint $table) {
            /**
             * Llave primaria
            */

            // Identificador único del usuario
            $table->id();

            /**
             * Datos de identificación (NIIF)
            */

            // Tipo de documento de identidad (CC, NIT, CE, etc.)
            $table->foreignId('document_type_id')
                  ->comment('Tipo de documento (CC, NIT, CE, etc.)')
                  ->constrained('document_types')
                  ->noActionOnDelete();

            // Número de identificación único (CC, NIT, etc.)
            $table->string('document_number', 20)->unique()->comment('Número de identificación');

            /**
             * Datos básicos de autenticación y estado
            */

            // Nombre completo del usuario
            $table->string('name', 255)->comment('Nombre completo del usuario');

            // Correo electrónico (único para login)
            $table->string('email', 255)->unique()->comment('Correo electrónico (único)');

            // Fecha de verificación del correo (nullable)
            $table->timestamp('email_verified_at')->nullable()->comment('Fecha de verificación del correo');

            // Contraseña hasheada
            $table->string('password')->comment('Contraseña hasheada');

            // Token para "recordarme" (remember me)
            $table->rememberToken()->comment('Token para "recordarme"');

            // Estado de acceso del usuario al sistema (inactivación sin borrado)
            $table->boolean('is_active')->default(true)->comment('Estado de acceso del usuario');

            /**
             * Auditoría NIIF (trazabilidad de creación y modificación)
            */

            // Usuario que creó este registro (auto-referencia)
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID del usuario que creó este registro');

            // Último usuario que modificó este registro
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID del último usuario que modificó');

            $table->timestamps();

            /**
             * Llaves foráneas
            */

            // Auto-referencia para auditoría (created_by)
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            // Auto-referencia para auditoría (updated_by)
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            /**
             * Índices para búsquedas frecuentes
            */

            // Búsqueda combinada de documento para autocompletar o validar terceros
            $table->index(['document_type_id', 'document_number']);

            // Filtro rápido de usuarios activos por correo/nombre (para listados)
            $table->index(['is_active', 'email']);

            // Auditoría: usuarios creados por un usuario
            $table->index('created_by');
        });

        /**
         * Tablas auxiliares de Laravel (no alteradas)
        */

        // Tabla de restablecimiento de contraseñas
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Tabla de sesiones
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};