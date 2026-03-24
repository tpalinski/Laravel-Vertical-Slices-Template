<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Architecture\Controller\AuthController;

Route::prefix('api/auth/v1')->name('auth.')->group(function () {
    Route::post('token', [AuthController::class, 'postToken']);
});
