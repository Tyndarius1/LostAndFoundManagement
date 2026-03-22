<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClaimRequestController;
use App\Http\Controllers\Api\FoundItemController;
use App\Http\Controllers\Api\ImportExportController;
use App\Http\Controllers\Api\LostItemController;
use App\Http\Controllers\Api\ScanController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/scan/{referenceCode}', [ScanController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('categories', CategoryController::class);

        Route::post('claim-requests/{claimRequest}/approve', [ClaimRequestController::class, 'approve']);
        Route::post('claim-requests/{claimRequest}/reject', [ClaimRequestController::class, 'reject']);
        Route::post('claim-requests/{claimRequest}/release', [ClaimRequestController::class, 'release']);

        Route::post('found-items/{foundItem}/archive', [FoundItemController::class, 'archive']);

        Route::get('exports/categories', [ImportExportController::class, 'exportCategories']);
        Route::get('exports/lost-items', [ImportExportController::class, 'exportLostItems']);
        Route::get('exports/found-items', [ImportExportController::class, 'exportFoundItems']);
        Route::get('exports/claim-requests', [ImportExportController::class, 'exportClaimRequests']);

        Route::post('imports/categories', [ImportExportController::class, 'importCategories']);
    });

    Route::middleware('role:admin,staff,user')->group(function () {
        Route::apiResource('lost-items', LostItemController::class);
        Route::apiResource('claim-requests', ClaimRequestController::class);
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::apiResource('found-items', FoundItemController::class);
        Route::post('found-items/{foundItem}/regenerate-qr', [FoundItemController::class, 'regenerateQr']);
    });
});
