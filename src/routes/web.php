<?php

use App\Http\Controllers\web\TransactionsController;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return redirect()->route('web.transactions.index');
}); */

Route::resource('transactions', TransactionsController::class)->names('transactions');