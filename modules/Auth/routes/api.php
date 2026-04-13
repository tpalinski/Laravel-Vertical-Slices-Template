<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Architecture\Controller\AuthController;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('token', [AuthController::class, 'postToken']);
    Route::get('authorize', [AuthController::class, 'getAuthorize']);
    Route::post('login', [AuthController::class, 'postLogin']);
});
