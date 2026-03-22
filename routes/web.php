<?php

use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/print/claims/{claimRequest}', [PrintController::class, 'claimReceipt']);
    Route::get('/print/found-items/{foundItem}', [PrintController::class, 'foundItemSlip']);
    Route::get('/print/lost-items/{lostItem}', [PrintController::class, 'lostItemReport']);
    Route::get('/print/reports/claims', [PrintController::class, 'claimsSummary']);
});
