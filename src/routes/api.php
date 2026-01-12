<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de Autenticación (Limite de 4 intentos por minuto)
*/
Route::middleware(['throttle:4,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:api'); 
});

Route::middleware(['auth:api'])->group(function () {

    /**
     * CRUD de Cuentas Contables
    */
    Route::apiResource('accounts', AccountsController::class);

    /**
     * Rutas Personalizadas de Cuentas Contables
    */
    Route::controller(AccountsController::class)->group(function () {

        // Listar las cuentas asociadas a la cuenta padre
        Route::get('/accounts/by_parent/{parent_id}', 'getByParent')->name('accounts.by_parent');
    });

    /**
     * Rutas personalizadas de Transacciones
    */
    Route::controller(TransactionsController::class)->group(function () {

        // Transferencia interna entre cuentas
        Route::post('/transactions/transfer/internal', 'internalTransfer')->name('transactions.transfer.internal');
        
    })->middleware(['account.exists']);
});

