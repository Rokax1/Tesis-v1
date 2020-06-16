<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modelos\AreaActividad;


class AreasController extends Controller
{
    public function __construct()
    {

        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
        ]]);
    }


    public function index(){

        $areas= AreaActividad::all();

        $data = array(
            'code' => 200,
            'status' => 'success',
            'areas' => $areas,
        );

        return response()->json($data,$data['code']);
    }

//fddddddddddddddddd


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


        $validate = \Validator::make($params_array, [
            'nombre_area' => 'required',
            'descripcion' => 'required',
        ]);

        if ($validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'no se ha guardado el area ,faltan datos',
                'fails' => $validate->errors(),
            ];
        } else {

            $area = new AreaActividad();

            $area->nombre_area = $params->nombre_area;
            $area->descripcion = $params->descripcion;

            $area->save();

            $data = [
                'code' => 200,
                'status' => 'success',
                'area' => $area,
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
    $area = AreaActividad::find($id);

    if (is_object($area)) {

        $data = [
            'code' => 200,
            'status' => 'success',
            'area' => $area
        ];
    } else {

        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'El area no existe',
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
   // $user = $this->ObtenerIdentidad($request);

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
            'nombre_area' => 'required',
            'descripcion' => 'required',


        ]);
        if ($validate->fails()) {
            $data['errors'] = $validate->errors();
            return response()->json($data, $data['code']);
        }

        unset($params_array['id_area_actividad']);
        unset($params_array['created_at']);

        $area = AreaActividad::where('id_area_actividad', $id);

        if (!empty($area) && is_object($area)) {
            //  dd($actividad,$params_array);
            $area->update($params_array);
            // devolver los datos
            $data = array(
                'code' => 200,
                'status' => 'success',
                'area' => $area,
                'changes' => $params_array,
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'error' => 'no puedes editar esta area',

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
    //$user = $this->ObtenerIdentidad($request);

    //conseguir  el registro
    $area = AreaActividad::where('id_area_actividad', $id)
        ->first();

    if (!empty($area)) {
        //borrarlo
        $area->delete();
        //devolver datos

        $data = [
            'code' => 200,
            'status' => 'success',
            'area' => $area,
        ];
    } else {
        $data = [
            'code' => 404,
            'status' => 'error',
            'message' => 'El area no existe',
        ];

    }

    return response()->json($data, $data['code']);
}




   

}
