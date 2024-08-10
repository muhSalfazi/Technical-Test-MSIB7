<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\DevisiController;
use App\Http\Controllers\api\PegawaiController;

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

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // 10 requests per minute

Route::middleware('auth.jwt')->group(function () {
    // api devisi
    Route::get('/divisions', [DevisiController::class, 'index']);
    
    // api pegawai
    Route::get('/employees', [PegawaiController::class, 'index']);
    Route::post('/employees', [PegawaiController::class, 'store'])->middleware('throttle:10,1'); // 10 requests per minute
    Route::post('/employees/{uuid_pegawai}', [PegawaiController::class, 'update'])->middleware('throttle:10,1');
    Route::delete('/employees/{uuid_pegawai}', [PegawaiController::class, 'destroy'])->middleware('throttle:10,1');

    // logout
    Route::post('/logout', [AuthController::class, 'logout']);
});