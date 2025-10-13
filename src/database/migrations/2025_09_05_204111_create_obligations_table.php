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

            # Llave primaria
            $table->unsignedInteger('id')->primary()->autoIncrement();

            /* Campos principales */

                // FK: ID del tercero asociado
                $table->unsignedInteger('third_party_id')->comment("ID del tercero asociado");

                // FK: ID del asiento inicial de la obligacion
                $table->unsignedBigInteger('transaction_id')->comment("ID del asiento inicial de la obligacion");

                // Campo: Tipo de Obligacion
                $table->enum('type', ["CXC", "CXP"]);

                // Campo: Monto total
                $table->decimal('total_amount');

                // Campo: Saldo pendiente
                $table->decimal('pending_amount');

                // Campo: Fecha de vencimiento (Opcional)
                $table->datetime('expiration_date')->nullable();

            //

            // Campos create_at y update_at
            $table->timestamps();

            /* Llaves foraneas */

                // ID del tercero asociado
                $table->foreign('third_party_id')->references('id')->on('third_parties')->noActionOnDelete();

                // ID del asiento inicial de la obligacion
                $table->foreign('transaction_id')->references('id')->on('transactions')->noActionOnDelete();
            //
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
