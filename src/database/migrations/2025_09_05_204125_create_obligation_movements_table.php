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
            
            # Llave primaria
            $table->unsignedBigInteger('id')->primary()->autoIncrement();

            /* Campos principales */

                // FK: ID de la obligacion
                $table->unsignedInteger('obligation_id');

                // FK: ID de la transaccion asociada a la novedad (Opcional)
                $table->unsignedBigInteger('transaction_id')->nullable();

                // Campo: Tipo de novedad
                $table->enum('type', ['apertura','abono','cancelacion','condonacion','ajuste','interes']);

                // Campo: Descripcion (Opcional)
                $table->string('description', 255)->nullable();
            //

            // Campos update_at y create_at
            $table->timestamps();

            /* Llaves foraneas */

                // ID de la obligacion
                $table->foreign('obligation_id')->references('id')->on('obligations')->onDelete('cascade');

                // ID de la transaccion asociada a la novedad
                $table->foreign('transaction_id')->references('id')->on('transactions')->noActionOnDelete();
            //
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
