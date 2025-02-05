<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::get('/users/search/name/{name}', [UserController::class, 'searchByName']);
    Route::get('/users/search/nim/{nim}', [UserController::class, 'searchByNIM']);
    Route::get('/users/search/ymd/{ymd}', [UserController::class, 'searchByYMD']);
    Route::get('/external-data', [UserController::class, 'fetchExternalData']);
    Route::get('/externaldata-todatabase', [UserController::class, 'fetchExternaltoDatabese']);
});