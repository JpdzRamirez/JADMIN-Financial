<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\EmpresaJADMIN;
use App\Models\Placa;
use App\Models\Tercero;
use App\Models\TerceroJADMIN;
use App\Models\User;
use App\Models\Vehiculo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function listarClientes()
    {
        $clientes = User::where('rol', '2')->paginate(10);

        return view('clientes.lista', compact('clientes'));
    }

    public function nuevoCliente()
    {
        $user = new User();
        $condiciones = ["Conductor"=>"Conductor", "Propietario"=>"Propietario", "Particular"=>"Particular", "Administrativo"=>"Administrativo"];

        return view('clientes.form', ['user' => $user, 'condiciones'=>$condiciones, 'method' => 'post', 'route' => ['clientes.registrar']]);
    }

    public function registrarCliente(Request $request)
    {
        try {
            $user = User::where('nro_identificacion', $request->input('identificacion'))->first();
            if($user == null){
                $tercero = new TerceroJADMIN();
                $tercero->tipo = "Persona";
                $user = new User();
                $user->tipo_identificacion = $request->input('tipo_identificacion');
                $user->nro_identificacion = $request->input('nro_identificacion');
                $user->primer_nombre = ucfirst($request->input('primer_nombre'));
                $user->segundo_nombre = ucfirst($request->input('segundo_nombre'));
                $user->primer_apellido = ucfirst($request->input('primer_apellido'));
                $user->segundo_apellido = ucfirst($request->input('segundo_apellido'));
                $user->usuario = $user->nro_identificacion;
                $user->email = $request->input('email');
                $user->celular = $request->input('celular');
                $user->direccion = $request->input('direccion');
                $user->barrio = $request->input('barrio');
                $user->municipio = $request->input('municipio');
                $user->condicion = $request->input('condicion');
                $user->estado = 1;
                $user->rol = 2;
                $user->password = Hash::make($user->nro_identificacion);
                
                $tercero->documento = $user->nro_identificacion;
                $tercero->nombre = $user->primer_nombre . " " . $user->segundo_nombre . " " . $user->primer_apellido . " " . $user->segundo_apellido;
                $tercero->save();

                $user->terceros_id = $tercero->id;
                $user->save();
                return redirect('/clientes');

            }else{
                return back()->withErrors(["sql" => "Ya existe un cliente con la identificación:" . $request->input('identificacion')]);
            }
        } catch (Exception $ex) {
            return back()->withErrors(["sql" => $ex->getMessage()]);
        }
    }

    public function editarCliente($user)
    {
        $user = User::find($user);
        $condiciones = ["Conductor"=>"Conductor", "Propietario"=>"Propietario", "Particular"=>"Particular", "Administrativo"=>"Administrativo"];
        $route = ["clientes.actualizar"];
        $method = "put";

        return view('clientes.form', compact('route', 'method', 'condiciones', 'user'));
    }

    public function actualizarCliente(Request $request)
    {
        $user = User::with('placas')->find($request->input('id'));
        $user->email = $request->input('email');
        $user->celular = $request->input('celular');
        $user->direccion = $request->input('direccion');
        $user->barrio = $request->input('barrio');
        $user->municipio = $request->input('municipio');
        $user->condicion = $request->input('condicion');
        if($request->filled('proceso')){
            $user->proceso = 1;
        }else{
            $user->proceso = 0;
        }
        $user->save();

        return redirect('/clientes');
    }

    public function buscarClientes(Request $request)
    {
        $clientes = User::with('referencias', 'personales')->where('nro_identificacion', 'like', $request->input('doc') . '%')->get();

        return json_encode($clientes);
    }

    public function creditosCliente($cliente)
    {
        $cliente = User::with('creditos')->find($cliente);

        return view('clientes.creditos', compact('cliente'));
    }

    public function editcuenta(User $user)
    {
        if($user->id == Auth::user()->id){
            return view('users.actualizar', ['user' => $user,  'method' => 'put', 'route' => ['users.updatecuenta', $user->id]]);
        }else{
            abort(404);
        }    
    }

    public function updatecuenta(Request $request, $user)
    {
        $user = User::find($user);
        $user->email = $request->input('email');
        $user->celular = $request->input('celular');
        $user->direccion = $request->input('direccion');
        $user->barrio = $request->input('barrio');
        $user->municipio = $request->input('municipio');
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();

        return redirect('/users/actualizar/' . Auth::user()->id);
    }

    public function loginConductor($conductor)
    {
        $cliente = User::where('nro_identificacion', $conductor)->first();
        if($cliente == null){
            $conductor = Conductor::with('municipio')->where('NUMERO_IDENTIFICACION', $conductor)->first();
            $tercero = new TerceroJADMIN();
            $tercero->tipo = "Persona";
            $cliente = new User();
            $cliente->primer_nombre = $conductor->PRIMER_NOMBRE;
            $cliente->segundo_nombre = $conductor->SEGUNDO_NOMBRE;
            $cliente->primer_apellido = $conductor->PRIMER_APELLIDO;
            $cliente->segundo_apellido = $conductor->SEGUNDO_APELLIDO;
            if ($conductor->TIPO_IDENTIFICACION == "01") {
                $cliente->tipo_identificacion = "Cédula de ciudadanía";
            }else{
                $cliente->tipo_identificacion = "Cédula de extranjería";
            }
            $cliente->nro_identificacion = $conductor->NUMERO_IDENTIFICACION;
            $cliente->email = $conductor->EMAIL;
            $cliente->celular = $conductor->CELULAR;
            $cliente->direccion = $conductor->DIRECCION;
            $cliente->barrio = $conductor->BARRIO;
            $cliente->condicion = "Conductor";
            if($conductor->municipio != null){
                $cliente->municipio = $conductor->municipio->DESCRIPCION;
            }
            $cliente->estado = 1;
            $cliente->rol = 2;
            $cliente->usuario = $cliente->nro_identificacion;
            $cliente->password = Hash::make(strtolower($cliente->primer_apellido) . $cliente->nro_identificacion);
            
            $tercero->documento = $cliente->nro_identificacion;
            $tercero->nombre = $cliente->primer_nombre . " " . $cliente->segundo_nombre . " " . $cliente->primer_apellido . " " . $cliente->segundo_apellido;
            $tercero->save();

            $cliente->terceros_id = $tercero->id;
            $cliente->save();
        }

        Auth::login($cliente);

        return redirect('mis_creditos/nuevo');
    }

    public function terceros()
    {
        $terceros = TerceroJADMIN::paginate(10);

        return view('users.listaTerceros', compact('terceros'));
    }

    public function nuevoTercero()
    {
        return view('users.formTercero');
    }

    public function registrarTercero(Request $request)
    {
        try {
            $tercero = new TerceroJADMIN();
            if($request->input('tipo') == "Empresa"){
                $tercero->tipo = "Empresa";
                $empresa = new EmpresaJADMIN();
                $empresa->nit = $request->input('nit');
                $empresa->razon_social = $request->input('razon');
                $empresa->dv = $request->input('dv');
                $empresa->telefono = $request->input('telefono');
                $empresa->email = $request->input('email');
                $empresa->direccion = $request->input('direccion');
                $empresa->municipio = ucfirst($request->input('municipio'));
                $tercero->nombre = $empresa->razon_social;
                $tercero->documento = $empresa->nit;
                $tercero->save();
                $empresa->terceros_id = $tercero->id;
                $empresa->save();
            }else{
                $tercero->tipo = "Persona";
                $persona = new User();
                $persona->tipo_identificacion = $request->input('tipoide');
                $persona->nro_identificacion = $request->input('numide');
                $persona->primer_apellido = ucfirst($request->input('primer_apellido'));
                $persona->primer_nombre = ucfirst($request->input('primer_nombre'));
                $persona->segundo_apellido = ucfirst($request->input('segundo_apellido'));
                $persona->segundo_nombre = ucfirst($request->input('segundo_nombre'));
                $persona->email = $request->input('email');
                $persona->celular = $request->input('telefono');
                $persona->direccion = $request->input('direccion');
                $persona->municipio = ucfirst($request->input('municipio'));
                $persona->condicion = "Particular";
                $persona->estado = 0;
                $persona->rol = 2;
                $tercero->documento = $persona->nro_identificacion;
                $tercero->nombre = str_replace("  ", " ", $persona->primer_nombre . " " . $persona->segundo_nombre . " " .  $persona->primer_apellido . " " . $persona->segundo_apellido);
                $tercero->save();
                $persona->terceros_id = $tercero->id;
                $persona->save();
            }
    
            return redirect('/contabilidad/terceros')->with('creado', 'ok'); 
        } catch (Exception $ex) {
            return redirect('/contabilidad/terceros')->with('creado', $ex->getMessage());
        }
    }

    public function editarTercero($tercero)
    {
        $tercero = TerceroJADMIN::with('usuario', 'empresa')->find($tercero);

        return view('users.formTercero', compact('tercero'));
    }

    public function actualizarTercero(Request $request)
    {
        $tercero = TerceroJADMIN::with('usuario', 'empresa')->find($request->input('tercero'));
        if($tercero->usuario != null){
            $tercero->usuario->celular = $request->input('telefono');
            $tercero->usuario->email = $request->input('email');
            $tercero->usuario->direccion = $request->input('direccion');
            $tercero->usuario->municipio = $request->input('municipio');
            $tercero->usuario->save();
        }else{
            $tercero->empresa->telefono = $request->input('telefono');
            $tercero->empresa->email = $request->input('email');
            $tercero->empresa->direccion = $request->input('direccion');
            $tercero->empresa->municipio = $request->input('municipio');
            $tercero->empresa->save();
        }

        return redirect('/contabilidad/terceros');
    }
    public function buscarTercero(Request $request)
    {
        $terceros = TerceroJADMIN::where('nombre', 'like', '%' . $request->input('tercero') . '%')->orWhere('documento', 'like', $request->input('tercero') . '%')->get();

        return json_encode($terceros);
    }

    public function importarCiudades()
    {
        ini_set('max_execution_time', 0);
        $usuarios = User::whereBetween('id', [1, 43])->get();
        foreach ($usuarios as $usuario) {
            try {
                $terce = Tercero::with('municipio')->where('NRO_IDENTIFICACION', $usuario->nro_identificacion)->first();
                if($terce != null){
                    if($terce->municipio != null){
                        $usuario->municipio = utf8_encode($terce->municipio->DESCRIPCION);
                        $usuario->save();
                    }
                }
            } catch (Exception $ex) {
               return $ex->getMessage() . $ex->getLine();
            }
        }

        return "Listo";
    }

    public function listaUsuarios()
    {
        $users = User::whereIn('rol', [1,3,4])->paginate(10);

        return view('users.lista', compact('users'));
    }

    public function nuevoUsuario()
    {
        $user = new User();
        $metodo = "post";

        return view('users.form', compact('user', 'metodo'));
    }

    public function registrarUsuario(Request $request)
    {
        try {
            $user = new User();
            $user->tipo_identificacion = 'Cédula de ciudadanía';
            $user->nro_identificacion = $request->input('identificacion');
            $user->primer_nombre = strtoupper($request->input('primer_nombre'));
            $user->segundo_nombre = strtoupper($request->input('segundo_nombre'));
            $user->primer_apellido = strtoupper($request->input('primer_apellido'));
            $user->segundo_apellido = strtoupper($request->input('segundo_apellido'));
            $num = 0;
            do {
                if ($num > 0) {
                    $user->usuario = $user->primer_nombre . "." . $user->primer_apellido . $num;
                }else{
                    $user->usuario = $user->primer_nombre . "." . $user->primer_apellido;
                }
                $num = $num+1;
            } while (User::where('usuario', $user->usuario)->first() != null);
            $user->usuario = strtolower($user->usuario);
            $user->password = Hash::make($request->input('password'));
            $user->condicion = "Administrativo";
            $user->rol = $request->input('rol');
            $user->estado = $request->input('estado');
            $user->save();

            return redirect('/users')->with('usuario', $user->usuario);
        } catch (Exception $ex) {
            return back()->withErrors(["sql"=>$ex->getMessage()]);
        } 
    }

    public function editarUsuarioVista(User $user)
    {
        $metodo = "put";

        return view('users.form', compact('user', 'metodo'));
    }

    public function editarUsuario(Request $request)
    {
        $user = User::find($request->input('usuario'));
        $user->condicion = "Administrativo";
        $user->rol = $request->input('rol');
        $user->estado = $request->input('estado');
        if($request->filled('password')){
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();

        return redirect('/users');
    }
}
