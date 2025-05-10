<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\prueba;
use App\Http\Controllers\Services\TypeServiceController;
use App\Http\Controllers\Services\ServiceController;
use App\Http\Controllers\TimeBlocks\TimeBlocksController;
use App\Http\Controllers\Products\InventoryMovementController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Evaluation\EvaluationController;
use App\Http\Controllers\Notifications\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [LoginController::class, 'register']);
Route::get('/login', function () {
    return response()->json(['error' => 'Debe autenticarse para acceder a este recurso.'], 401);
})->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/services', [ServiceController::class, 'getUserServices']);
    Route::get('/user/services/{id}', [ServiceController::class, 'ServiceById']);


    Route::prefix('notifications')->group(function () {
        Route::get('/read', [NotificationController::class, 'getReadNotifications']);
        Route::get('/unread', [NotificationController::class, 'getUnreadNotifications']);
        Route::patch('/{notificationId}/mark-read', [NotificationController::class, 'markAsRead']);
    });

    Route::middleware('role:Tecnico')->group(function () {
        Route::post('/services/update-history', [ServiceController::class, 'updateServiceHistory']);
    });

    Route::middleware('role:Cliente')->group(function () {
        Route::get('/type-services', [TypeServiceController::class, 'index']);
        Route::post('/create-services', [ServiceController::class, 'store']);
        Route::post('/time-blocks/available', [TimeBlocksController::class, 'getAvailableTimeBlocks']);
        Route::post('/services/add-products', [InventoryMovementController::class, 'addProductsToService']);
        Route::get('/products', [ProductController::class, 'getAllProducts']);
        Route::get('/products/{id}', [ProductController::class, 'getProductById']);
        Route::get('/services/{serviceId}/products', [ProductController::class, 'getServiceProducts']);
        Route::get('/evaluation/service/{serviceId}', [EvaluationController::class, 'getServiceEvaluations']);
        Route::post('/evaluation', [EvaluationController::class, 'createEvaluation']);

    });


    Route::post('logout', [LoginController::class, 'logout']);
});

