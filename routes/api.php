<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayaController;

Route::prefix('sheba')->group(function () {
    Route::post('/', [PayaController::class, 'store']);
    Route::post('/{request}', [PayaController::class, 'update']);
    Route::get('/', [PayaController::class, 'index']);
});
