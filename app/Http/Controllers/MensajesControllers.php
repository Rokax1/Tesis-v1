<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Modelos\Mensajes;
use Illuminate\Http\Request;

class MensajesControllers extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function Mensajes($id, Request $request)
    {

        //$user = $this->ObtenerIdentidad($request);

        $msj = Mensajes::where('fk_actividad', $id)
            ->orderBy('created_at', 'asc')
            ->get();

            //dd($msj[0]->created_at);
        //$msj = Mensajes::all();

        return response()->json([
            'code' => '200',
            'status' => 'success',
            'msj' => $msj,
        ], 200);

    }

    public function CrearMensaje(Request $request)
    {

        //dd($request);
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //dd($json);
        if (!empty($params_array)) {

            $user = $this->ObtenerIdentidad($request);

            $validate = \Validator::make($params_array, [
                'fk_actividad' => 'required',
                'descripcion_mensaje' => 'required',
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha guardado la actividad ,faltan datos',
                    'fails' => $validate->errors(),
                ];
            } else {

                $msj = new Mensajes();
                $msj->fk_actividad = $params->fk_actividad;
                $msj->fk_user = $user->sub;
                $msj->descripcion_mensaje = $params->descripcion_mensaje;

                $msj->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'msj' => $msj,
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

    private function ObtenerIdentidad($request)
    {

        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);

        $user = $jwtAuth->CheckToken($token, true);
        return $user;

    }

}
