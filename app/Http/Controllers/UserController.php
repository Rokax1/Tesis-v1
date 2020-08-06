<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Modelos\ActividadDetalle;
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

    public function __construct()
    {

        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'Login',
            'Register',
            'getUser'
        ]]);
    }

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
            'correo' => 'required|email',
            'password' => 'required',
        ]);
        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se a podido logear',
                'errors' => $validate->errors(),
            );
        } else {
            //cifrar la contraseña
            $pwd = hash('sha256', $params->password);
            //devolver token  o datos
            $data = $jwtAuth->signup($params->correo, $pwd);
            if (!empty($params->gettoken)) {
                $data = $jwtAuth->signup($params->correo, $pwd, true);
            }
        }

        return response()->json($data, 200);
    }



    public function update(Request $request ,$id){
        

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //validar los datos
        $validate = \Validator::make($params_array, [
            'nombre'=>'required',
            'apellido'=>'required',
            'correo' => 'required|email',
            
        ]);

        if ($validate->fails()) {

            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se a podido Modificar',
                'errors' => $validate->errors(),
            );
            return response()->json($data, $data['code']);

        } else {

            unset($params_array['id']);
            unset($params_array['password']);

            
            $user= User::where('id',$id)->first();

            if (!empty($user) && is_object($user)) {

                $pwd = hash('sha256', $params->password);
                
                $user->update($params_array);
                $user->password = $pwd;
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'usuario modificado',
                    'changes'=>$params_array
                );


            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'ha ocurrido un error',
                    
                );

            }
        }


        return response()->json($data, $data['code']);

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
/*
    public function update(Request $request)
    {
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'El usuario al update',
        );

        return response()->json($data, $data['code']);
    }*/

    public function index(Request $request)
    {

        $jwtAuth = new JwtAuth();
        $identidad = $jwtAuth->ObtenerIdentidadHelper($request);

        if (!is_object($identidad)) {
            $user = User::all();

        } else {
            // dd($identidad);
            $id[] = $identidad->sub;
            $user = User::whereNotIn('id', $id)->get();
        }

        $data = array(
            'code' => 200,
            'status' => 'success',
            'users' => $user,
        );

        return response()->json($data, $data['code']);

    }

    public function UsuariosConActividad(Request $request, $id)
    {

        $jwtAuth = new JwtAuth();
        $identidad = $jwtAuth->ObtenerIdentidadHelper($request);

        $users = ActividadDetalle::where('id_actividad', $id)->get();
        $id_users = [];
        // dd(count($users),$users);

        for ($i = 0; $i < count($users); $i++) {

            $id_users[] = $users[$i]->id_usuario;

        }

        if (!empty($id_users)) {

            if ($id_users[0] == null) {
                unset($id_users[0]);
            }
        }

        $usersEncargados = $this->usersArray($id_users, true);
        $usersNoEncargados = $this->usersArray($id_users, false);
        //dd($usersEncargados,$usersNoEncargados);

        $data = array(
            'code' => 200,
            'status' => 'success',
            'data' => [
                'encargado' => $usersEncargados,
                'noEncargados' => $usersNoEncargados,
            ],

        );
        return response()->json($data, $data['code']);

    }

    public function usersArray($data, $flaj = true)
    {

        $users = [];

        if ($flaj) {

            $users = User::whereIn('id', $data)->get();

            // dd("primer if");

        } else {

            if ($data == null) {

                $users = User::all();
                //  dd("else segundo if");

            }

            $users = User::whereNotIn('id', $data)->get();

            // dd("else",$users);

        }

        return $users;
    }

    public function getUser($id)
    {
        if ($id != null ) {

            $user= User::where('id',$id)->get();

            if($user->isEmpty()){

                $data = array(
                    'code' => 400,
                    'status' => 'no se ha encontrado el usuario',
                );

            }else{

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'user' => $user,
                );

            }
        }else{

            $data = array(
                'code' => 400,
                'status' => 'error',
            );

        }

        return response()->json($data, $data['code']);


        
    }

}
