<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('basic.auth')->group(function (): void {
     Route::get('data',[\App\Http\Controllers\DataController::class,'index']);
     Route::post('upload',[\App\Http\Controllers\ParseController::class,'upload']);
});
