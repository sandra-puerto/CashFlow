<?php

use App\Http\Controllers\AccountsController;
use Illuminate\Support\Facades\Route;

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