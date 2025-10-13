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
        Schema::create('fund_movements', function (Blueprint $table) {

            # Llave primaria
            $table->unsignedBigInteger('id')->primary()->autoIncrement();

            /* Campos principales */

                // FK: ID de transaccion asociada (Opcional)
                $table->unsignedBigInteger('transaction_id')->nullable();

                // Campo: Tipo de Movimiento
                $table->enum('type', ['asignacion', 'egreso', 'reintegro', 'ajuste']);

                // Campo: Descripcion (Opcional)
                $table->string('descripcion', 255)->nullable();

                // Campo: Saldo post-transaccion
                $table->decimal('total');
            //

            // Campos create_at y update_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_movements');
    }
};
