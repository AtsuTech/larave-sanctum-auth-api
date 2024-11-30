<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//registre
Route::post('/register', [AuthController::class, 'register'])->name('verification.verify');

//verify register
Route::get('email/verify/{id}',[AuthController::class,'verify'])->name('verification.verify');


//verify mail resend
Route::get('email/resesnd',[AuthController::class,'resend'])->name('verification.resend');

//login
Route::post('/login', [AuthController::class, 'login']);

//me
Route::post('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');


//send reset password mail
Route::post('/password/forgot',[AuthController::class,'sendemail']);

//reset password action
Route::post('/password/reset',[AuthController::class,'passwordreset'])->name('password.reset');
