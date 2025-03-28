<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\StatementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Public routes
Route::middleware('throttle:60,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);

    // Account routes
    Route::post('accounts', [AccountController::class, 'store']);
    Route::get('accounts/{account_number}', [AccountController::class, 'show']);
    Route::put('accounts/{account_number}', [AccountController::class, 'update']);
    Route::delete('accounts/{account_number}', [AccountController::class, 'destroy']);

    // Transaction routes
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::get('transactions', [TransactionController::class, 'index']);

    // Transfer routes
    Route::post('transfers', [TransferController::class, 'store']);

    // Webhook routes
    Route::get('webhooks', [WebhookController::class, 'index']);
    Route::post('webhooks', [WebhookController::class, 'store']);
    Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy']);

    // Statement routes
    Route::get('accounts/{account_number}/statement', [StatementController::class, 'generate']);
});