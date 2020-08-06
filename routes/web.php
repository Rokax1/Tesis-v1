<?php

use Illuminate\Support\Facades\Route;
//Route::Post('user/update','UserController@update')->middleware('api.auth');
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


// rutas de login y registro 
Route::Post('Login','UserController@Login');
Route::Post('Register','UserController@Register');

//rutas para subir y ver archivos (PDF)
Route::Post('Actividades/Archivo', 'ActividadesController@SubirArchivo');
Route::get('Actividades/Archivo/{filename}', 'ActividadesController@getArchivo');

//rutas resource para las rutas 
Route::resource('Actividades', 'ActividadesController');
Route::resource('Users', 'UserController');
Route::resource('Areas', 'AreasController');

//Rutas para mensajes 
Route::get('Mensajes/{id}','MensajesControllers@Mensajes');
Route::Post('Mensajes/crear','MensajesControllers@CrearMensaje');


//rutas con funciones especificas

Route::get('Users/Encargados/{id}', 'UserController@UsuariosConActividad');
Route::get('User/GetUser/{id}', 'UserController@getUser');
Route::Post('Actividades/AddUserActivity', 'ActividadesController@AddUserActivity');

Route::get('Actividades/ActividadesEncargados/{id}', 'ActividadesController@getActividadesUserEncargado');
Route::Post('Actividades/DeleteUserActivity', 'ActividadesController@DeleteUserActivity');