<?php

use Illuminate\Support\Facades\Route;
use Modules\{Module}\Http\Controllers\Api\V1\{Model}Controller;

Route::middleware(['auth:api'])->group(function () {
    Route::ApiResource('{module}/{model}', {Model}Controller::class, ['as' => 'api.v1']);
});