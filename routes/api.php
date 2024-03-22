<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Auth::routes();

Route::group(['middleware' => 'role:customer'], function () {

    Route::post('/product','ProductController@store')->name('create-product');
    Route::post('/product-import','ProductController@import')->name('import-product');
    Route::post('/delete/{product}','ProductController@destroy')->name('delete-product');
    Route::post('/delete-thumbnail','ProductController@deleteProductThumnail')->name('delete-product-thumnail');
    Route::post('/product/{product}','ProductController@update')->name('update-product');

});

