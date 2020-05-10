<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\User;

class UserController extends Controller
{

/*  METODO CUANDO SE QUIERE PEDIR EL TOKEN DE AUTORIZACION EN ALGUN METODO 
    public function __construct(){

        $this->middleware('api.auth',['except'=>[
            'index',
            'show',
            'getImage',
            'getPostByCategory',
            'getPostByUser'
            ]]);
    }*/

    public function Login(Request $request)
    {
        // dd($request);
        $jwtAuth = new \JwtAuth();
        //recibir el post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //validar los datos
        $validate = \Validator::make($params_array, [
            'correo' => 'required|email', //existe el ususario  ? unique
            'password' => 'required',

        ]);

        if ($validate->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se a podido logear',
                'errors' => $validate->errors(),
            );

        } else {
            //cifrar la contraseña
            $pwd = hash('sha256', $params->password);
            //devolver token  o datos
            $signup = $jwtAuth->signup($params->correo, $pwd);
            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup($params->correo, $pwd, true);
            }
        }

        return response()->json($signup, 200);

    }

    public function Register(Request $request)
    {

        //Recorger datos
        $json = $request->input('json', null);

        $params = json_decode($json); // convertir el json string en un objeto de php
        $params_array = json_decode($json, true); //array
        //validar datos

        //limpiar datos
        if (!empty($params) && !empty($params_array)) {
            $params_array = array_map('trim', $params_array);

            $validate = \Validator::make($params_array, [
                'nombre' => 'required|alpha',
                'apellido' => 'required|alpha',
                'correo' => 'required|email|unique:users', //existe el ususario  ? unique
                'password' => 'required',

            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se a creado',
                    'errors' => $validate->errors(),
                );
            } else {
                //validacion ok

                //cifrar la contraceña
                $pwd = hash('sha256', $params->password);
                //Crear el usuario
                $user = new User();
                $user->nombre = $params_array['nombre'];
                $user->apellido = $params_array['apellido'];
                $user->correo = $params_array['correo'];
                $user->password = $pwd;
                $user->estado = '';
                //$user->role = 'ROLE_USER';
                //guardar usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'el ususario se a creado correctamente',
                    'user' => $user,
                );
            }
        } else {

            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'los datos no son correctos',

            );
        }

        return response()->json($data, $data['code']);
    }

    public function update(Request $request)
    {
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'El usuario al update',
        );

        return response()->json($data, $data['code']);
    }
}
