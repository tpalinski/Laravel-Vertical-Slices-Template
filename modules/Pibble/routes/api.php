<?php

use Illuminate\Support\Facades\Route;
use Modules\Pibble\Architecture\Controller\PibbleController;

Route::prefix('api/pibble/v1')->name('pibble.v1.')->group(function () {
    Route::post('/belly', [PibbleController::class, 'postBelly']);
    Route::get('/greet', [PibbleController::class, 'getGreet']);
    Route::get('/{name}', [PibbleController::class, 'getPibble'])->whereAlpha('name');
    Route::post('/{name}', [PibbleController::class, 'postPibble'])->whereAlpha('name');
});
