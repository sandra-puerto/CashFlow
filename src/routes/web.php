<?php

use App\Http\Controllers\AccountsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return redirect()->route('accounts.index'); });

Route::resource('accounts', AccountsController::class);