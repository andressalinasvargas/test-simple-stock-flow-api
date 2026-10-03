<?php

declare(strict_types=1);

use App\Presentation\Http\Controller\AuthController;
use App\Presentation\Http\Controller\CategoryController;
use App\Presentation\Http\Controller\HealthController;
use App\Presentation\Http\Controller\MediaController;
use App\Presentation\Http\Controller\ProductController;
use App\Presentation\Http\Controller\ReportController;
use App\Presentation\Http\Controller\SaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Simple Stock Flow (E-01 a E-15)
|--------------------------------------------------------------------------
*/

// E-01: Login (Anónimo)
Route::post('/auth/login', [AuthController::class, 'login']);

// E-02: Register (Admin únicamente)
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('jwt.auth:admin');

// E-09: Categorías (Autenticado cualquier rol)
Route::get('/categories', [CategoryController::class, 'index'])->middleware('jwt.auth');

// Productos (E-03 a E-08)
Route::prefix('products')->middleware('jwt.auth')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // E-03
    Route::get('/{id}', [ProductController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}'); // E-04

    // Acciones reservadas a admin (CA-07.4, D-C8)
    Route::post('/', [ProductController::class, 'store'])->middleware('jwt.auth:admin'); // E-05
    Route::put('/{id}', [ProductController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}')->middleware('jwt.auth:admin'); // E-06
    Route::delete('/{id}', [ProductController::class, 'destroy'])->where('id', '[0-9a-fA-F-]{36}')->middleware('jwt.auth:admin'); // E-07
    Route::post('/{id}/image', [ProductController::class, 'uploadImage'])->where('id', '[0-9a-fA-F-]{36}')->middleware('jwt.auth:admin'); // E-08
});

// Ventas (E-10 a E-12) (Autenticado cualquier rol)
Route::prefix('sales')->middleware('jwt.auth')->group(function () {
    Route::get('/', [SaleController::class, 'index']); // E-11
    Route::post('/', [SaleController::class, 'store']); // E-10
    Route::get('/{id}', [SaleController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}'); // E-12
});

// Reportes (E-13) (Autenticado cualquier rol)
Route::prefix('reports')->middleware('jwt.auth')->group(function () {
    Route::get('/sales', [ReportController::class, 'sales']); // E-13
});

// Alias públicos (E-14 y E-15)
Route::get('/health', [HealthController::class, 'show']); // E-14
Route::get('/media/{key}', [MediaController::class, 'show']); // E-15

