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

            # Llave primaria
            $table->unsignedInteger('id', true);

            /* Campos Personalizados */

                // Campo: Nombre
                $table->string('name')->unique();

                // Campo: Correo
                $table->string('email')->unique();

                // Campo: Fecha de verificación de correo
                $table->timestamp('email_verified_at')->nullable();

                // Campo: Hash de Contraseña
                $table->string('password');
            //

            // Campo: Token 'recuerdame'
            $table->rememberToken();

            // Campos updated_at y created_at
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

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
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
