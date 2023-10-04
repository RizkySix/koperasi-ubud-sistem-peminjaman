<?php

use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\OtpController;
use App\Http\Controllers\Authentication\RegisterController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//GUEST ENDPOINT
Route::controller(RegisterController::class)->group(function() {
    Route::post('/register/nasabah'  , 'register_nasabah')->name('register.nasabah');
    Route::post('/register/admin'  , 'register_admin')->name('register.admin');
});
Route::controller(LoginController::class)->group(function() {
    Route::post('/login' , 'login')->name('login');
});


//AUTH ENDPOINT
Route::middleware(['auth:sanctum'])->group(function() {
    
    //UN VERIFIED PHONE NUMBER ENDPOINT
    Route::middleware(['un.verified'])->group(function() {
        Route::controller(OtpController::class)->group(function() {
            Route::post('/otp/resend' , 'resend_otp')->middleware('throttle:resend.otp')->name('resend.otp');
            Route::post('/otp/send' , 'send_otp')->name('send.otp');
        });
    });

    //VERIFIED PHONE NUMBER ENDPOINT
    Route::middleware(['is.verified'])->group(function () {
        Route::controller(LoginController::class)->group(function() {
            Route::post('/logout' , 'logout')->name('logout');
            Route::get('/test' , 'test_data')->name('test');
        });
    });
});
