<?php

use Illuminate\Support\Facades\Route;
use Modules\{Module}\Http\Controllers\{Model}Controller;

Route::middleware(['auth', 'verified'])->group(function() {
    Route::resource('{module}/{model}', {Model}Controller::class)
        ->names('{module}::{model}')
        ->parameters(['{model}' => '{model}']);
});
