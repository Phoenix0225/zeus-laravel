<?php

declare(strict_types=1);

namespace Zeus\Laravel\Routing;

use Illuminate\Support\Facades\Route;
use Zeus\Laravel\Http\Controllers\UiController;

class UiRouteRegistrar
{
    public static function register(): void
    {
        Route::prefix('api/ui')->group(function () {
            Route::get('/config', [UiController::class, 'config']);
            Route::get('/screens/{id}', [UiController::class, 'screen']);
        });
    }
}
