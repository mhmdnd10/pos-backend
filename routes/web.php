<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{token}', [AuthController::class, 'verifyEmail'])->name('verify.email');
