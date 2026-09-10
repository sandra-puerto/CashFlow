<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\JournalEntryController;
use Illuminate\Support\Facades\Route;

/**
 * Gestión del Plan de Cuentas Contables (PUC)
*/
Route::controller(AccountsController::class)
    ->prefix('accounts')
    ->name('accounts.')
    ->group(function () {

        /**
         * URL: GET /accounts
         * Name: accounts.index
         * Desc: Lista paginada de cuentas contables con filtros opcionales.
         */
        Route::get('/', 'index')->name('index');

        /**
         * URL: GET /accounts/search
         * Name: accounts.search
         * Desc: Búsqueda y filtrado de cuentas contables para autocompletado.
         */
        Route::get('search', 'search')->name('search');

        /**
         * URL: POST /accounts
         * Name: accounts.store
         * Desc: Almacena una nueva cuenta contable en el PUC.
        */
        Route::post('/', 'store')->name('store');

        /**
         * URL: GET /accounts/{account}
         * Name: accounts.show
         * Desc: Muestra el detalle de una cuenta contable específica.
         */
        Route::get('{account}', 'show')->name('show');

        /**
         * URL: PUT/PATCH /accounts/{account}
         * Name: accounts.update
         * Desc: Actualiza una cuenta contable existente (excepto primarias).
         */
        Route::put('{account}', 'update')->name('update');

        /**
         * URL: DELETE /accounts/{account}
         * Name: accounts.destroy
         * Desc: Elimina una cuenta contable si no es primaria ni tiene asientos asociados.
        */
        Route::delete('{account}', 'destroy')->name('destroy');

        /**
         * URL: GET /accounts/{account}/children
         * Name: accounts.children
         * Desc: Obtiene las subcuentas o cuentas hijas de una cuenta padre.
        */
        Route::get('{account}/children', 'children')->name('children');

        /**
         * URL: GET /accounts/{account}/balance
         * Name: accounts.balance
         * Desc: Obtiene el saldo actual detallado de una cuenta.
        */
        Route::get('{account}/balance', 'getBalance')->name('balance');

    }
);

/**
 * Gestión del Libro Diario (Asientos Contables)
*/
Route::controller(JournalEntryController::class)
    ->prefix('journal-entries')
    ->name('journal-entries.')
    ->group(function () {

        /**
         * URL: GET /journal-entries
         * Name: journal-entries.index
         * Desc: Muestra la lista paginada de asientos contables con filtros opcionales por fecha.
        */
        Route::get('/', 'index')->name('index');

        /**
         * URL: POST /journal-entries
         * Name: journal-entries.store
         * Desc: Contabiliza un nuevo asiento contable cumpliendo con la partida doble y el PUC.
        */
        Route::post('/', 'store')->name('store');

        /**
         * URL: GET /journal-entries/{id}
         * Name: journal-entries.show
         * Desc: Muestra los detalles y líneas de un asiento contable específico.
        */
        Route::get('{id}', 'show')->name('show');

    }
);