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
        /* Tabla de almacenamiento de caché (clave-valor) */
        Schema::create('cache', function (Blueprint $table) {
            
            /* Llave primaria: clave única de caché */
            $table->string('key')->primary()->comment('Clave única del elemento en caché');

            /* Valor almacenado (puede ser serializado) */
            $table->mediumText('value')->comment('Valor del elemento en caché (serializado)');

            /* Tiempo de expiración (timestamp Unix) para limpieza automática */
            $table->bigInteger('expiration')->index()->comment('Timestamp de expiración (Unix)');
        });

        /* Tabla de bloqueos atómicos para caché (cache locks) */
        Schema::create('cache_locks', function (Blueprint $table) {

            /* Llave primaria: nombre del bloqueo */
            $table->string('key')->primary()->comment('Nombre único del bloqueo');

            /* Propietario del bloqueo (identificador del proceso) */
            $table->string('owner')->comment('Identificador del proceso que adquirió el bloqueo');

            /* Tiempo de expiración del bloqueo (timestamp Unix) */
            $table->bigInteger('expiration')->index()->comment('Timestamp de expiración del bloqueo (Unix)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};