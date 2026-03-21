<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//API Routes
use App\Http\Controllers\Api\AuthController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/admin-only', function () {
        return response()->json([
            'status' => true,
            'message' => 'Welcome Admin',
        ]);
    })->middleware('role:admin');

    Route::get('/staff-only', function () {
        return response()->json([
            'status' => true,
            'message' => 'Welcome Staff',
        ]);
    })->middleware('role:staff');

    Route::get('/user-only', function () {
        return response()->json([
            'status' => true,
            'message' => 'Welcome User',
        ]);
    })->middleware('role:user');

    Route::get('/admin-or-staff', function () {
        return response()->json([
            'status' => true,
            'message' => 'Welcome Admin or Staff',
        ]);
    })->middleware('role:admin,staff');
});