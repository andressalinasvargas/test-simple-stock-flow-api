<?php

declare(strict_types=1);

use App\Presentation\Http\Controller\HealthController;
use App\Presentation\Http\Controller\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['name' => 'Simple Stock Flow API', 'version' => '1.0.0']);
});

// E-14 & E-15: Rutas públicas directas en raíz
Route::get('/health', [HealthController::class, 'show']);
Route::get('/media/{key}', [MediaController::class, 'show']);
