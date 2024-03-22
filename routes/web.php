<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


/*--------------------Admin Routes----------------------*/

Route::group(['middleware' => ['role:admin','auth'],'prefix' => 'admin'], function () {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
    Route::get('/','ProductController@index')->name('index');
    Route::get('/export-product','ProductController@export')->name('export-product');


});

/*--------------------Admin Routes----------------------*/


/*--------------------customer Routes----------------------*/

Route::group(['middleware' => ['role:customer','auth']], function () {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
    Route::get('/','ProductController@index')->name('index');
    Route::resource('product', ProductController::class);
    
    Route::get('/export-product','ProductController@export')->name('export-product');
    
    
    
});

Auth::routes();
/*--------------------Admin Routes----------------------*/

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
