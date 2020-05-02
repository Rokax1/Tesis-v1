<?php

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

/* Metodos HTTP
    Get : conseguir datos y recursos 
    Post : guardar datos o en el login principalmente 
    Put: Actualizar recursos o datos  
    Delete :Eliminar datos 

*/

Route::get('/', function () {
    return view('welcome');
});

Route::Post('Login','UserController@Login');
Route::Post('Register','UserController@Register');
Route::Post('user/update','UserController@update')->middleware('api.auth');
