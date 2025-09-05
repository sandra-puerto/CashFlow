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
        Schema::create('transactions', function (Blueprint $table) {

            # Llave Primaria
            $table->unsignedBigInteger('id')->primary();

            /* Campos principales */

                // FK: ID de la cuenta contable afectada
                $table->unsignedInteger("account_id")->comment("ID de la cuenta contable afectada");

                // FK: Transacción origen enlazada a la actual
                $table->unsignedBigInteger('flow_id')->comment('ID de la transacción origen');

                // Campo: Fecha y hora de registro de la transaccion
                $table->datetime('datetime');

                // Campo: Descripcion (Opcional)
                $table->string('description', 255)->nullable();

                // Campo: Debe/Debito
                $table->decimal('debit');

                // Campo: Haber/Credito
                $table->decimal('credit');

                // Campo: Saldo total de la cuenta contable afectada post-transaccion
                $table->decimal('total')->comment("Total post-transaccion");
            //

            // Campos create_at y update_at
            $table->timestamps();

            /* Llaves foraneas */

                // FK: ID de la cuenta contable afectada
                $table->foreign('account_id')->references('id')->on('accounts')->noActionOnDelete();

                // FK: Transacción origen enlazada a la actual
                $table->foreign('flow_id')->references('id')->on('transactions')->noActionOnDelete();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
