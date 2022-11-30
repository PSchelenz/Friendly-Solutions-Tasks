<?php

use Illuminate\Support\Facades\Route;
use Modules\Importer\Http\Controllers\ImporterController;

Route::name('importer.')->prefix('importer')->group(function () {
    Route::get('/', [ImporterController::class, 'index'])->name('index');
    Route::post('/create', [ImporterController::class, 'store'])->name('store');
});