<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// // Public routes
// Route::post('register', [AuthController::class, 'register']);
// Route::post('login', [AuthController::class, 'login']);

// // Protected routes
Route::middleware('auth:sanctum')->group(function () {
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
});
// Route::resource('accounts', AccountController::class);