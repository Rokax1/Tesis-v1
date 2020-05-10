<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Modelos\Actividades;
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
        ]]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $actividades = Actividades::all()->Load('usuario', 'area');

        return response()->json([
            'code' => '200',
            'status' => 'success',
            'actividades' => $actividades,
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
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            $user = $this->ObtenerIdentidad($request);

            $validate = \Validator::make($params_array, [
                'titulo' => 'required',
                'archivo' => 'required',
                'descripcion' => 'required',
                'area_actividad' => 'required',
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha guardado la actividad ,faltan datos',
                    'fails' => $validate->errors(),
                ];
            } else {

                $actividad = new Actividades();

                $actividad->titulo = $params->titulo;
                $actividad->archivo = $params->archivo;
                $actividad->descripcion = $params->descripcion;
                $actividad->fk_area_actividad = $params->area_actividad;
                $actividad->fk_user_creador_actividad = $user->sub;

                if (array_key_exists('user_encargado', $params_array)) {

                    $actividad->fk_user_encargado = $params->user_encargado;
                    $actividad->estado_actividad = 'enviada';
                } else {
                    //$actividad->fk_user_encargado = $params->user_encargado;
                    $actividad->estado_actividad = 'creada';
                }
                $actividad->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => $actividad,
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
                'actividad' => $actividades->Load('usuario', 'area'),
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
                'fk_user_encargado' => 'required',
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            unset($params_array['id_actividad']);
            unset($params_array['created_at']);
            unset($params_array['estado_confirmacion_creador']);

            $actividad = Actividades::where('id_actividad', $id)
                ->where('fk_user_creador_actividad', $user->sub)
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

    private function ObtenerIdentidad($request)
    {

        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);

        $user = $jwtAuth->CheckToken($token, true);
        return $user;

    }
}
