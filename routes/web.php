<?php

use Code16\JockoClient\Http\Controllers\PreviewController;
use Code16\JockoClient\Http\Middleware\PreviewAuthenticate;
use Illuminate\Support\Facades\Route;


Route::middleware('web')
    ->group(function() {
        Route::get('/preview', [PreviewController::class, 'show'])
            ->withoutMiddleware(PreviewAuthenticate::class)
            ->name('jocko.preview.show');
    });

