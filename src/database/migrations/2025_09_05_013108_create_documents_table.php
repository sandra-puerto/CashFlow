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
        Schema::create('documents', function (Blueprint $table) {

            # Llave primaria
            $table->uuid('id')->primary();

            /* Campos Personalizados */

                // FK: ID Transaccion asociada
                $table->uuid('transaction_id');

                // Campo: Tipo
                $table->enum('type', ['factura', 'recibo', 'comprobante de pago']);
            //

            // Campos create_at y update_at
            $table->timestamps();

            /* Llaves foraneas */

                // ID Transaccion asociada
                $table->foreign('transaction_id')->references('id')->on('transactions');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
