<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Modelos\ActividadDetalle;
use App\Modelos\Actividades;
use App\Modelos\Mensajes;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ActividadesController extends Controller
{
/*
estados de las actividades
-creada
-enviada
-aceptada/en curso
-finalizada

 */
    public function __construct()
    {

        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getArchivo',
           
        ]]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $this->ObtenerIdentidad($request);

        $actividad = Actividades::where('user_creador', $user->sub)->get();

        $actividad->load('area','userEncargado');

        return response()->json([
            'code' => '200',
            'status' => 'success',
            'actividades' => $actividad,
        ], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return 'crear  actividades';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //dd($request);
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //dd($json);
        if (!empty($params_array)) {

            $user = $this->ObtenerIdentidad($request);

            $validate = \Validator::make($params_array, [
                'titulo' => 'required',
                'descripcion' => 'required',
                'fk_area_actividad' => 'required',
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha guardado la actividad ,faltan datos',
                    'fails' => $validate->errors(),
                ];
            } else {

                if (!(array_key_exists('fk_user_encargado', $params_array)) || $params->fk_user_encargado == "") {

                    $actividadDevuelta = $this->agregarActividad($params, 'creada', $user->sub);

                    $this->crearDetalleActividad($actividadDevuelta->id_actividad, null);

                } else {

                    $actividadDevuelta = $this->agregarActividad($params, 'enviada', $user->sub);

                    $this->crearDetalleActividad($actividadDevuelta->id_actividad, $params->fk_user_encargado);

                }

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'actividad' => $actividadDevuelta,
                ];

            }

        } else {

            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'envia los datos correctamente',
            ];
        }

        return response()->json($data, $data['code']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $actividades = Actividades::find($id);

        if (is_object($actividades)) {

            $data = [
                'code' => 200,
                'status' => 'success',
                'actividad' => $actividades->Load('userEncargado', 'area'),
            ];
        } else {

            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'la actividad no existe',
            ];

        }
        return response()->json($data, $data['code']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = $this->ObtenerIdentidad($request);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        ///datos parra devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'datos enviados incorrectamente ',
        );

        if (!empty($params_array)) {

            $validate = \Validator::make($params_array, [
                'titulo' => 'required',
                'archivo' => 'required',
                'descripcion' => 'required',
                'fk_area_actividad' => 'required',
               
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            unset($params_array['id_actividad']);
            unset($params_array['created_at']);
            unset($params_array['estado_actividad']);
            unset($params_array['user_encargado']);

            $actividad = Actividades::where('id_actividad', $id)
                ->where('user_creador', $user->sub)
                ->first();

            if (!empty($actividad) && is_object($actividad)) {
                //  dd($actividad,$params_array);
                $actividad->update($params_array);
                // devolver los datos
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'actividad' => $actividad,
                    'changes' => $params_array,
                );
            } else {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'error' => 'no puedes editar esta actividad',

                );

            }

        }

        return response()->json($data, $data['code']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {

        //conseguir ususario identificado
        $user = $this->ObtenerIdentidad($request);

        //conseguir  el registro

        $detalle = ActividadDetalle::where('id_actividad', $id)
            ->where('id_usuario', null)
            ->first();
        $actividad = Actividades::where('id_actividad', $id)
            ->where('fk_user_creador_actividad', $user->sub)
            ->first();

        if (!empty($actividad)) {
            //borrarlo
            $actividad->delete();
            //devolver datos

            $data = [
                'code' => 200,
                'status' => 'success',
                'actividad' => $actividad,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'la actividad no existe',
            ];

        }

        return response()->json($data, $data['code']);
    }

    public function SubirArchivo(Request $request)
    {
        //recoger la imagen de la peticion
        $archivo = $request->file('file0');

        //validar imagen n
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|file|mimes:pdf,doc,docx',
        ]);
        //guardar imagen

        if (!$archivo || $validate->fails()) {

            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'error al subir el archivo',
            );

        } else {
            $archivo_name = time() . $archivo->getClientOriginalName();

            \Storage::disk('Actividades')->put($archivo_name, \File::get($archivo));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'archivo' => $archivo_name,
            );
        }

        // devolver datos
        return response()->json($data, $data['code']);

    }

    public function getArchivo($filename)
    {

        $isset = \Storage::disk('Actividades')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('Actividades')->get($filename);
            return new Response($file, 200);

        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'image' => 'El arhivo no existe no existe',

            );

        }

        return response()->json($data, $data['code']);

    }

    public function AddUserActivity(Request $request)
    {
        $user = $this->ObtenerIdentidad($request);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'datos enviados incorrectamente ',
        );

        if (!empty($params_array)) {

            $validate = \Validator::make($params_array, [
                'id_Actividad' => 'required',
                'id_user' => 'required',
            ]);

            if ($validate->fails()) {

                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            $ActividadUserExist = ActividadDetalle::where('id_Actividad', $params_array['id_Actividad'])->where('id_usuario', $params_array['id_user'])->get();

            if (count($ActividadUserExist) >= 1) {

                $data['message'] = 'La actividad ya fue asignada';
                return response()->json($data, $data['code']);
            }

            $this->crearDetalleActividad($params_array['id_Actividad'], $params_array['id_user']);

            $data = array(
                'code' => 200,
                'status' => 'success',
            );

        } else {

            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'envia los datos correctamente',
            ];
        }

        return response()->json($data, $data['code']);

    }

    public function DeleteUserActivity(Request $request)
    {
        $user = $this->ObtenerIdentidad($request);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'datos enviados incorrectamente ',
        );

        if (!empty($params_array)) {

            $validate = \Validator::make($params_array, [
                'id_Actividad' => 'required',
                'id_user' => 'required',
            ]);

            if ($validate->fails()) {

                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }


            $this->EliminarDetalleActividad($params_array['id_Actividad'],$params_array['id_user'],$user->sub);

            $data = array(
                'code' => 200,
                'status' => 'success',
            );


        } else {

            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'envia los datos correctamente',
            ];
        }

        return response()->json($data, $data['code']);

    }


    public function ActividadesDeUsuarioEncargado(Request $request,$id ){

        //$actividades = User::find($id)->;

    }
    public function ActividadesDeUnUsuario()
    {
        $user = $this->ObtenerIdentidad($request);

        $actividad = User::find($user->sub)->ActividadesCreadas;
        //dd($actividad);
        $actividad->load('area', 'userEncargado');

    }

    private function agregarActividad($params, $estado, $user)
    {

        $actividad = new Actividades();
        $actividad->titulo = $params->titulo;
        $actividad->archivo = $params->archivo;
        $actividad->descripcion = $params->descripcion;
        $actividad->fk_area_actividad = $params->fk_area_actividad;
        $actividad->user_creador = $user;
        $actividad->estado_actividad = $estado;
        $actividad->save();

        //dd($actividad);
        return $actividad;
    }

    public function crearDetalleActividad($id_actividad_devuelta, $fk_user_encargado = null)
    {

        $ActividadDetalle = new ActividadDetalle();

        $ActividadDetalle->id_actividad = $id_actividad_devuelta;
        $ActividadDetalle->id_usuario = $fk_user_encargado;
        //$params->fk_user_encargado;
        $ActividadDetalle->save();

    }

    public function EliminarDetalleActividad($id_actividad, $id_user,$id_creador){
      //  $msjj= Mensajes::where('fk_actividad',$id_actividad)->get();

        $msj = Mensajes::where('fk_actividad',$id_actividad)->where('fk_user',$id_user)->get();
        $msj2 = Mensajes::where('fk_actividad',$id_actividad)->where('fk_user',$id_creador)->get();
        //dd(empty($msj),$msj2);
       // dd($msj,$msj2);
        if (!($msj->isEmpty())) {
            $msj = Mensajes::where('fk_actividad',$id_actividad)->where('fk_user',$id_user)->delete();
        }
        
        if (!($msj2->isEmpty())) {
            $msj2 = Mensajes::where('fk_actividad',$id_actividad)->where('fk_user',$id_creador)->delete();
        }
       
        //$msj,$msj2,$id_actividad,$id_user,$id_creador
   
        
       
        
       $actividadDetalle= ActividadDetalle::where('id_Actividad',$id_actividad)
                                            ->where('id_usuario', $id_user)->first(); 
        $actividadDetalle->delete();
        
        
    }


    public function getActividadesUserEncargado(Request $request , $id){
        $user = $this->ObtenerIdentidad($request);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if($id != null){


            $actividadesDetalle = ActividadDetalle::where('id_usuario', $id)->get();

            $id_actividades=[];

            for ($i = 0; $i < count($actividadesDetalle); $i++) {

                $id_actividades[] = $actividadesDetalle[$i]->id_actividad;

            }

            if ( !empty($id_actividades)) {

                if($id_actividades[0] == null){
                    unset($id_users[0]); 
                }
             }

             $actividades= Actividades::whereIn('id_actividad',$id_actividades)->get();

             $data = array(
                'code' => 200,
                'status' => 'success',
                'actividades'=>$actividades
            );
             
             return response()->json($data, $data['code']);

        }


    }





    private function ObtenerIdentidad($request)
    {

        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);

        $user = $jwtAuth->CheckToken($token, true);
        return $user;

    }



  
}
