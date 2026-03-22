<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LostItemController;
use App\Http\Controllers\Api\FoundItemController;
use App\Http\Controllers\Api\ClaimRequestController;




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('categories', CategoryController::class);

        Route::post('claim-requests/{claimRequest}/approve', [ClaimRequestController::class, 'approve']);
        Route::post('claim-requests/{claimRequest}/reject', [ClaimRequestController::class, 'reject']);
        Route::post('claim-requests/{claimRequest}/release', [ClaimRequestController::class, 'release']);
    
        Route::post('found-items/{foundItem}/archive', [FoundItemController::class, 'archive']);
    });

    Route::middleware('role:admin,staff,user')->group(function () {
        Route::apiResource('lost-items', LostItemController::class);
        Route::apiResource('claim-requests', ClaimRequestController::class);
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::apiResource('found-items', FoundItemController::class);
    });
});