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
        /* Tabla de trabajos en cola (Queue Jobs) */
        Schema::create('jobs', function (Blueprint $table) {
            
            /* Llave primaria: ID autoincremental del trabajo */
            $table->id()->comment('Identificador único del trabajo');

            /* Nombre de la cola (para separar trabajos por prioridad) */
            $table->string('queue')->index()->comment('Nombre de la cola (ej. high, low, default)');

            /* Datos serializados del trabajo (clase, método, parámetros) */
            $table->longText('payload')->comment('Datos del trabajo en formato serializado');

            /* Número de intentos realizados */
            $table->unsignedSmallInteger('attempts')->comment('Cantidad de intentos de ejecución');

            /* Timestamps para control de ejecución */
            $table->unsignedInteger('reserved_at')->nullable()->comment('Timestamp Unix cuando el trabajo fue reservado');
            $table->unsignedInteger('available_at')->comment('Timestamp Unix desde cuando está disponible para ejecutar');
            $table->unsignedInteger('created_at')->comment('Timestamp Unix de creación');
        });

        /* Tabla de lotes de trabajos (Job Batches) */
        Schema::create('job_batches', function (Blueprint $table) {

            /* Llave primaria: ID único del lote (UUID) */
            $table->string('id')->primary()->comment('Identificador único del lote de trabajos');

            /* Nombre del lote (para identificación) */
            $table->string('name')->comment('Nombre descriptivo del lote');

            /* Contadores de trabajos en el lote */
            $table->integer('total_jobs')->comment('Total de trabajos en el lote');
            $table->integer('pending_jobs')->comment('Trabajos pendientes de ejecutar');
            $table->integer('failed_jobs')->comment('Trabajos fallidos en el lote');

            /* IDs de trabajos fallidos (serializados) */
            $table->longText('failed_job_ids')->comment('IDs de los trabajos que fallaron (serializado)');

            /* Opciones adicionales (serializadas) */
            $table->mediumText('options')->nullable()->comment('Opciones del lote (serializado)');

            /* Timestamps de control del lote */
            $table->integer('cancelled_at')->nullable()->comment('Timestamp Unix de cancelación');
            $table->integer('created_at')->comment('Timestamp Unix de creación');
            $table->integer('finished_at')->nullable()->comment('Timestamp Unix de finalización');
        });

        /* Tabla de trabajos fallidos (Failed Jobs) */
        Schema::create('failed_jobs', function (Blueprint $table) {

            /* Llave primaria: ID autoincremental */
            $table->id()->comment('Identificador único del trabajo fallido');

            /* UUID del trabajo fallido (para seguimiento) */
            $table->string('uuid')->unique()->comment('UUID único del trabajo fallido');

            /* Datos del fallo */
            $table->string('connection')->comment('Conexión de cola usada');
            $table->string('queue')->comment('Cola de la que provenía');
            $table->longText('payload')->comment('Payload del trabajo al fallar');
            $table->longText('exception')->comment('Excepción que causó el fallo');

            /* Fecha del fallo */
            $table->timestamp('failed_at')->useCurrent()->comment('Fecha y hora del fallo (por defecto ahora)');

            /* Índices para consultas rápidas de fallos por conexión/cola/fecha */
            $table->index(['connection', 'queue', 'failed_at'])->comment('Índice compuesto para búsquedas frecuentes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};