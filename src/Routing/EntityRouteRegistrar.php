<?php

declare(strict_types=1);

namespace Zeus\Laravel\Routing;

use Illuminate\Support\Facades\Route;
use Zeus\Laravel\Http\Controllers\EntityController;

class EntityRouteRegistrar
{
    public static function register(): void
    {
        Route::prefix('api/dynamic')->group(function () {
            Route::get('/{entityCode}', [EntityController::class, 'index']);
            Route::post('/{entityCode}', [EntityController::class, 'store']);
            Route::put('/{entityCode}/{id}', [EntityController::class, 'update']);
            Route::delete('/{entityCode}/{id}', [EntityController::class, 'destroy']);
        });
    }
}
