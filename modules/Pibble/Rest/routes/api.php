<?php

use Illuminate\Support\Facades\Route;
use Modules\Pibble\Rest\Controller\PibbleController;

Route::prefix('api/v1/pibble')->name('pibble.')->group(function () {
    // Define Pibble module routes here
    Route::get('/', [PibbleController::class, 'greetPibble']);
});
