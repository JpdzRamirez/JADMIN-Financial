<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Placa;
use App\Models\Tercero;
use App\Models\TerceroCahors;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function register(Request $request)
    {
        $usuario = User::where('nro_identificacion', $request->input('numid'))->first();

        if ($usuario != null) {
            return back()->withErrors(["sql" => "La identificación " . $usuario->nro_identificacion . " pertenece a un usuario ya registrado"]);
        } else {
            $nombres = explode(" ", $request->input('nombres'));
            $apellidos = explode(" ", $request->input('apellidos'));
            $tercero = new TerceroCahors();
            $tercero->tipo = "Persona";
            $usuario = new User();
            $usuario->tipo_identificacion = $request->input('tipoid');
            $usuario->nro_identificacion = $request->input('numid');
            $usuario->usuario = $request->input('numid');
            $usuario->primer_nombre = $nombres[0];
            if (count($nombres) > 1) {
                array_shift($nombres);
                $usuario->segundo_nombre = implode(" ", $nombres);
            }
            $usuario->primer_apellido = $apellidos[0];
            if (count($apellidos) > 1) {
                array_shift($apellidos);
                $usuario->segundo_apellido = implode(" ", $apellidos);
            }
            $usuario->celular = $request->input('celular');
            $usuario->email = $request->input('email');
            $usuario->estado = "2";
            $usuario->rol = "2";
            $usuario->password = Hash::make($request->input('password'));

            $iden = $usuario->nro_identificacion;
            $vehiculos = Vehiculo::with('empresa')->whereHas('propietario', function ($q) use ($iden) {
                $q->whereHas('tercero', function ($r) use ($iden) {
                    $r->where('NRO_IDENTIFICACION', $iden);
                });
            })->orWhereHas('otrosPropietarios', function ($q) use ($iden) {
                $q->whereHas('tercero', function ($r) use ($iden) {
                    $r->where('NRO_IDENTIFICACION', $iden);
                });
            })->get();
            if (count($vehiculos) > 0) {
                $usuario->condicion = "Propietario";
            } else {
                $usuario->condicion = "Particular";
            }
            $terceroIcon = Tercero::with('municipio')->where('NRO_IDENTIFICACION', $iden)->first();
            if ($terceroIcon != null) {
                $usuario->municipio = $terceroIcon->municipio->DESCRIPCION;
                $usuario->direccion = $terceroIcon->DIRECCION;
                $usuario->barrio = $terceroIcon->BARRIO;
            }
            $tercero->documento = $usuario->nro_identificacion;
            $tercero->nombre = $usuario->primer_nombre . " " . $usuario->segundo_nombre . " " . $usuario->primer_apellido . " " . $usuario->segundo_apellido;
            $tercero->save();
            $usuario->terceros_id = $tercero->id;

            $usuario->verificacion = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $usuario->save();

            foreach ($vehiculos as $vehiculo) {
                $placa = new Placa();
                $placa->placa = $vehiculo->PLACA;
                $placa->empresa = $vehiculo->empresa->SIGLA;
                $placa->users_id = $usuario->id;
                $placa->save();
            }
            //$this->enviarSMS($usuario->celular, $usuario->verificacion);


            return redirect("/register/codigo_verificacion")->with("verificarRegistro", $usuario->id);
        }
    }

    public function enviarSMS($celular, $codigo)
    {
        $texto = "El código de verificación para tu registro en Cahors es: " . $codigo;
        $connection = fopen(
            'https://portal.bulkgate.com/api/1.0/simple/transactional',
            'r',
            false,
            stream_context_create(['http' => [
                'method' => 'POST',
                'header' => [
                    'Content-type: application/json'
                ],
                'content' => json_encode([
                    'application_id' => '22484',
                    'application_token' => '8f1lnuLJwXRwXFzGnwbqbGw8p1WtQZ39U2lqwYLJG46pNLo6Ct',
                    'unicode' => '1',
                    'number' => '57' . $celular,
                    'text' => $texto,
                    'sender_id' => 'gOwn',
                    'sender_id_value' => '573183731974'
                ]),
                'ignore_errors' => true
            ]])
        );

        $logFile = fopen(storage_path() . DIRECTORY_SEPARATOR . "SMSCahors.txt", 'a') or die("Error creando archivo");

        if ($connection) {
            //$response = json_decode(stream_get_contents($connection));        
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . stream_get_contents($connection) . " Número: " . $celular) or die("Error escribiendo en el archivo");
            fclose($connection);
        } else {
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . "Falla conexión: " . $celular) or die("Error escribiendo en el archivo");
        }
        fclose($logFile);
    }

    public function redirectTo()
    {
        Session::flush();
        if (Auth::check()) {
            if (Auth::user()->rol == 2) {
                return redirect('/mis_creditos');
            } elseif (Auth::user()->rol == 3) {
                return redirect('/pagos/registrar');
            } elseif (Auth::user()->rol == 1) {
                return redirect('/creditos_cobro');
            }
        }
    }

    function codigoVerificacion()
    {
        Session::put('verificarRegistro');
        $idUsuario = Session::get('verificarRegistro');
        if ($idUsuario == null) {
            abort(400, 'Error: Ruta no permitida');
        }

        return view('auth.codigo');
    }

    function confirmarRegistro(Request $request)
    {
        $idUsuario = Session::get('verificarRegistro');
        if ($idUsuario != null) {
            $usuario = User::find($idUsuario);
            if ($usuario->verificacion == $request->input('1') . $request->input('2') . $request->input('3') . $request->input('4') . $request->input('5') . $request->input('6')) {
                $usuario->estado = 1;
                $usuario->save();

                return redirect('/');
               
            } else {
                return back()->withErrors(['error' => 'El código ingresado es incorrecto']);
            }
            return redirect('/creditos/nuevo');
        } else {
            abort(400, 'Error: Ruta no permitida');
        }
    }
}
