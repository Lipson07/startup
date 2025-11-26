<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/create', [AuthController::class, 'create']);
Route::get('/check-user', [AuthController::class, 'checkUser']);
Route::post('/login', [AuthController::class, 'login']);