<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostoController extends Controller
{
    public function lista()
    {
        $costos = Costo::get();

        return view('costos.lista', compact('costos'));
    }

    public function store(Request $request)
    {
        $costo = new Costo();
        $costo->descripcion = $request->input('descripcion');
        $costo->valor = $request->input('valor');
        $costo->tipo = $request->input('tipo');
        if($request->filled('iva')){
            $costo->iva = 1;
        }else{
            $costo->iva = 0;
        }
        $costo->iva = $request->input('iva');
        $costo->save();

        return redirect('/costos');
    }

    public function editar(Request $request)
    {
        $costo = Costo::find($request->input('idcosto'));
        $costo->valor = $request->input('evalor');
        if($request->filled('eiva')){
            $costo->iva = 1;
        }else{
            $costo->iva = 0;
        }
        $costo->save();
        
        return redirect('/costos');
    }
}