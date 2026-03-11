<?php

use Illuminate\Support\Facades\Route;
use Modules\Pibble\Architecture\Controller\PibbleController;

Route::prefix('api/v1/pibble')->name('pibble.')->group(function () {
    Route::post('/belly', [PibbleController::class, 'postBelly']);
    Route::get('/greet', [PibbleController::class, 'getGreet']);
    Route::get('/{name}', [PibbleController::class, 'getPibble'])->whereAlpha('name');
    Route::post('/{name}', [PibbleController::class, 'postPibble'])->whereAlpha('name');
});
