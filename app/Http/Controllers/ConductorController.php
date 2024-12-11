<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\CalificacionCli;
use Illuminate\Http\Request;
use App\Models\Conductor;
use App\Models\ConductorIcon;
use App\Models\Contrato_vale;
use App\Models\Cancelacion;
use App\Models\Contrato_vale_ruta;
use App\Models\Cuentac;
use App\Models\Inactivacion;
use App\Models\Mensaje;
use App\Models\Registro;
use App\Models\Sancion;
use App\Models\Seguimiento;
use App\Models\Vehiculo;
use App\Models\User;
use App\Models\Servicio;
use App\Models\Suspension;
use App\Models\Tercero;
use App\Models\Transaccion;
use App\Models\Vale;
use App\Models\Valeav;
use App\Models\Version;
use App\Models\Ws_servicioIcon;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SoapClient;
use SoapFault;
use stdClass;

class ConductorController extends Controller
{

    public function editar($conductor)
    {

        $conductor = Conductor::with(['cuentac'=>function($q){$q->with(['calificaciones', 'inactivaciones'=>function($s){$s->with('operador1', 'operador2');}, 'suspensiones'=>function($r){$r->with('sancion', 'operador1', 'operador2');}]);}])->whereHas('cuentac', function ($q) use ($conductor) {
            $q->where('id', $conductor);
        })->first();

        $sanciones = Sancion::get();

        if (count($conductor->cuentac->calificaciones) == 0) {
            $amarillas = 5;
            $grises = 0;
        } else {
            $amarillas = round($conductor->cuentac->calificaciones->avg('puntaje'));
            $grises = 5 - $amarillas;
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('conductores.form', ['conductor' => $conductor, 'sanciones' => $sanciones, 'amarillas' => $amarillas, 'grises' => $grises, 'usuario' => $usuario, 'method' => 'put', 'route' => ['conductores.actualizar', $conductor->CONDUCTOR]]);
    }

    public function update(Request $request, $conductor)
    {

        $conductor = Conductor::with(['cuentac'=>function($q){$q->select('id', 'password', 'fechabloqueo', 'estado', 'conductor_CONDUCTOR');}])->where('CONDUCTOR', $conductor)->first();

        $conductor->DIRECCION = $request->input('DIRECCION');
        $conductor->CELULAR = $request->input('CELULAR');
        $conductor->TELEFONO = $request->input('TELEFONO');
        $conductor->EMAIL = $request->input('EMAIL');
        $cuenta = $conductor->cuentac;
        if ($request->filled('password')) {
            $cuenta->password = $request->input('password');
        }
        if ($request->input('ESTADO') == "Bloqueado") {
            if ($cuenta->estado != "Bloqueado") {
                $fecha = Carbon::now('-05:00');
                $sancion = Sancion::find($request->input('sanciones'));
                if ($sancion->unidad == "Horas") {
                    $cuenta->fechabloqueo = $fecha->addHours($sancion->cantidad);
                } elseif ($sancion->unidad == "Dias") {
                    $cuenta->fechabloqueo = $fecha->addDays($sancion->cantidad);
                } elseif ($sancion->unidad == "Semanas") {
                    $cuenta->fechabloqueo = $fecha->addWeeks($sancion->cantidad);
                }
                $cuenta->razon = $sancion->descripcion;
                $cuenta->estado = "Bloqueado";
            
                $suspension = new Suspension();
                $suspension->fechabloqueo = Carbon::now('-05:00');
                $suspension->fechadesbloqueo = $cuenta->fechabloqueo;
                $suspension->sanciones_id = $sancion->id;
                $suspension->users_id = Auth::user()->id;
                $suspension->cuentasc_id = $cuenta->id;
                $suspension->save();
            }else{
                $cuenta->fechabloqueo = $cuenta->fechabloqueo;
            }
        } elseif ($request->input('ESTADO') == "Activo") {
            if($cuenta->estado == "Bloqueado"){
                $fecha = Carbon::now('-05:00');
                $suspension = Suspension::whereDate('fechadesbloqueo', '>', $fecha->toDateString())->where('cuentasc_id', $cuenta->id)->first();
                if($suspension != null){
                    $suspension->fechadesbloqueo = $fecha;
                    $suspension->users2_id = Auth::user()->id;
                    $suspension->save();
                }
            }else if($cuenta->estado == "Inactivo"){
                $inactivacion = Inactivacion::where('cuentasc_id', $cuenta->id)->orderBy('id', 'DESC')->first();
                if($inactivacion != null){
                    $inactivacion->reactivacion = Carbon::now();
                    $inactivacion->users2_id = Auth::user()->id;
                    $inactivacion->save();
                }
            }

            $cuenta->fechabloqueo = null;
            $cuenta->razon = null;
            $cuenta->estado = "No disponible";
        } else if($request->input('ESTADO') == "Inactivo") {
            $fecha = Carbon::now();
            $inactivacion = new Inactivacion();
            $inactivacion->fecha = $fecha;
            $inactivacion->motivo = $request->input('motivo');
            $inactivacion->cuentasc_id = $cuenta->id;
            $inactivacion->users_id = Auth::user()->id;
            $inactivacion->save();
            $cuenta->estado = "Inactivo";
        }
        if ($request->hasFile('foto')) {
            if ($request->file('foto')->isValid()) {
                $foto = $request->file('foto');
                $cuenta->foto = base64_encode(file_get_contents($foto));
            }
        }
        $cuenta->save();
        //$conductor->save();

        $conductorI = ConductorIcon::find($conductor->CONDUCTOR);
        $conductorI->DIRECCION = $conductor->DIRECCION;
        $conductorI->CELULAR = $conductor->CELULAR;
        $conductorI->TELEFONO = $conductor->TELEFONO;
        $conductorI->EMAIL = $conductor->EMAIL;
        $conductorI->save();

        return redirect('afiliados');
    }

    public function vehiculos($conductor)
    {

        $conductor = Conductor::with(['vehiculos'=>function($q){$q->with(['propietario.tercero', 'marca']);}])->where('CONDUCTOR', $conductor)->first();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('conductores.vehiculos', compact('conductor', 'usuario'));
    }

    public function crearcuentas()
    {
        $conductores = Conductor::wheredoesnthave('cuentac')->where('CONDUCTOR', '!=', 1)->whereHas('vehiculos', function ($r) {
            $r->where('SW_ACTIVO_NUEVO_CRM', "1");
        })->get();

        foreach ($conductores as $conductor) {
            if ($conductor->cuentac == null) {
                $cuenta = new Cuentac();
                $cuenta->saldo = 0;
                $cuenta->saldovales = 0;
                $cuenta->estado = "No disponible";
                $cuenta->password = $conductor->NUMERO_IDENTIFICACION;
                $cuenta->conductor_CONDUCTOR = $conductor->CONDUCTOR;
                $cuenta->save();
            }
        }

        return redirect('cuentas_afiliados');
    }

    public function login(Request $request)
    {
        $conductor = Conductor::with('cuentac', 'vehiculos')->where('NUMERO_IDENTIFICACION', $request->input('documento'))->first();

        if (!empty($conductor) && $conductor != null) {
            $cuenta = $conductor->cuentac;

            if ($cuenta->estado == "Libre" || $cuenta->estado == "Ocupado" || $cuenta->estado == "Ocupado propio") {
                $ahora = Carbon::now('-05:00');
                if ($ahora->diffInSeconds($cuenta->ultimasesion) < 10) {
                    if ($cuenta->idandroid != $request->input('idandroid')) {
                        $cuenta->estado = "Singleton";

                        return json_encode($cuenta);
                    }
                }
            } else {
                if ($cuenta->estado == "Inactivo" || $cuenta->estado == "Bloqueado") {
                    return json_encode($cuenta);
                } else {
                    $cuenta->idandroid = $request->input('idandroid');
                    $cuenta->estado = "Libre";
                    $cuenta->save();
                }
            }

            $placas = [];
            $ocupadas = [];
            foreach ($conductor->vehiculos as $vehiculo) {
                if ($vehiculo->pivot->SW_ACTIVO_NUEVO_CRM == 1) {
                    if ($vehiculo->PLACA != $cuenta->placa) {
                        $cplacas = Cuentac::where('placa', $vehiculo->PLACA)->where(function($q){$q->where('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio');})->count();
                        if ($cplacas > 0) {
                            $ocupadas[] = $vehiculo->PLACA;
                        } else {
                            $placas[] = $vehiculo->PLACA;
                        }
                    } else {
                        $placas[] = $vehiculo->PLACA;
                    }
                }
            }
            if (count($placas) > 0) {
                $cuenta->placa = $placas[0];
                $cuenta->ultimasesion = Carbon::now('-05:00');
                $cuenta->save();
            }

            $cuenta->nombre = $conductor->NOMBRE;
            $cuenta->identificacion = $conductor->NUMERO_IDENTIFICACION;
            $cuenta->placas = implode("_", $placas);
            $cuenta->ocupadas = implode("_", $ocupadas);

            if($request->filled('plataforma')){
                $version = Version::find($request->input('plataforma'));
                $cuenta->version = $version->numero;
            }

            return json_encode($cuenta);
        } else {
            $vacio = [];
            return json_encode($vacio);
        }
    }

    public function servicioslibres(Request $request)
    {
        try {
            $cuenta = Cuentac::select('id', 'estado', 'placa', 'latitud', 'longitud', 'ultimasesion')->find($request->input('id'));
            $vehiculo = Vehiculo::where('PLACA', $cuenta->placa)->first();
            if ($vehiculo != null) {
                $record = $this->distancia($request->input('latitud'), $request->input('longitud'), $cuenta->latitud, $cuenta->longitud);
                $vehiculo->kilometros = $vehiculo->kilometros + $record;
                $vehiculo->save(); 
            }            
            $cercanos = [];
            if ($cuenta->estado == "Libre" || $cuenta->estado == "No disponible") {
                $cuenta->latitud = $request->input('latitud');
                $cuenta->estado = "Libre";
                $cuenta->ultimasesion = Carbon::now('-05:00');
                $cuenta->longitud = $request->input('longitud');
                $cuenta->save();

                $servicios = Servicio::with(['cliente'=>function($q){$q->select('id', 'nombres');}, 'vale'=>function($r){$r->select('id', 'servicios_id');}, 'valeav'=>function($s){$s->select('id', 'servicios_id');}])->select('id', 'direccion', 'adicional', 'pago', 'cobro', 'usuarios', 'observaciones', 'latitud', 'longitud', 'clientes_id')->where('estado', "Pendiente")->where('asignacion', 'Normal')->whereNull('fechaprogramada')->get();
                foreach ($servicios as $servicio) {
                    $distancia = $this->distancia($servicio->latitud, $servicio->longitud, $cuenta->latitud, $cuenta->longitud);
                    if ($distancia <= 1) {
                        if ($servicio->vale != null) {
                            $servicio->valesid = $servicio->vale->id;
                        }elseif($servicio->valeav != null){
                            $servicio->valesavid = $servicio->valeav->id;
                        }
                        $cercanos[] = $servicio;
                    }
                }

                $fecha = Carbon::now();
                $servicios = Servicio::with(['cliente'=>function($q){$q->select('id', 'nombres');}, 'vale'=>function($r){$r->select('id', 'servicios_id');}, 'valeav'=>function($s){$s->select('id', 'servicios_id');}])->select('id', 'direccion', 'adicional', 'pago', 'cobro', 'usuarios', 'observaciones', 'latitud', 'longitud', 'fechaprogramada', 'clientes_id')->where('estado', "Pendiente")->where('asignacion', 'Normal')->whereNotNull('fechaprogramada')->get();
                foreach ($servicios as $servicio) {
                    $fechpro = Carbon::parse($servicio->fechaprogramada);
                    $dif = $fecha->diffInMinutes($fechpro);
                    if ($dif <= 15) {
                        $distancia = $this->distancia($servicio->latitud, $servicio->longitud, $cuenta->latitud, $cuenta->longitud);
                        if ($distancia <= 1) {
                            if ($servicio->vale != null) {
                                $servicio->valesid = $servicio->vale->id;
                            }elseif($servicio->valeav != null){
                                $servicio->valesavid = $servicio->valeav->id;
                            }
                            $cercanos[] = $servicio;
                        }
                    }
                }

                $servicios = Servicio::with(['cliente'=>function($q){$q->select('id', 'nombres');}, 'vale'=>function($r){$r->select('id', 'servicios_id');}, 'valeav'=>function($s){$s->select('id', 'servicios_id');}])->select('id', 'direccion', 'adicional', 'pago', 'cobro', 'usuarios', 'observaciones', 'latitud', 'longitud', 'fechaprogramada', 'clientes_id')->where('estado', 'Pendiente')->where('asignacion', 'Directo')->where('cuentasc_id', $cuenta->id)->get();
                foreach ($servicios as $servicio) {
                    if ($servicio->fechaprogramada == null) {
                        if ($servicio->vale != null) {
                            $servicio->valesid = $servicio->vale->id;
                        }elseif($servicio->valeav != null){
                            $servicio->valesavid = $servicio->valeav->id;
                        }
                        $cercanos[] = $servicio;
                    } else {
                        $fechpro = Carbon::parse($servicio->fechaprogramada);
                        $dif = $fecha->diffInMinutes($fechpro);
                        if ($dif <= 15) {
                            if ($servicio->vale != null) {
                                $servicio->valesid = $servicio->vale->id;
                            }elseif($servicio->valeav != null){
                                $servicio->valesavid = $servicio->valeav->id;
                            }
                            $cercanos[] = $servicio;
                        }
                    }
                }

                $servicios = Servicio::with(['cliente'=>function($q){$q->select('id', 'nombres');}, 'vale'=>function($r){$r->select('id', 'servicios_id');}, 'valeav'=>function($s){$s->select('id', 'servicios_id');}])->select('id', 'direccion', 'adicional', 'pago', 'cobro', 'usuarios', 'observaciones', 'latitud', 'longitud', 'clientes_id')->where('estado', "Libre")->get();
                foreach ($servicios as $servicio) {
                    $distancia = $this->distancia($servicio->latitud, $servicio->longitud, $cuenta->latitud, $cuenta->longitud);
                    if ($distancia <= 2) {
                        if ($servicio->vale != null) {
                            $servicio->valesid = $servicio->vale->id;
                        }elseif($servicio->valeav != null){
                            $servicio->valesavid = $servicio->valeav->id;
                        }
                        $cercanos[] = $servicio;
                    }
                }
            }
            return json_encode($cercanos);
        } catch (Exception $e) {
            $logFile = fopen("../storage/logsincro.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). $e->getMessage()) or die("Error escribiendo en el archivo");
            fclose($logFile);
        }    
    }

    function distancia($point1_lat, $point1_long, $point2_lat, $point2_long, $decimals = 3)
    {
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat)) * sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat)) * cos(deg2rad($point2_lat)) * cos(deg2rad($point1_long - $point2_long)))));
        $distance = $degrees * 111.13384;
        return round($distance, $decimals);
    }

    public function cuentascorrientes()
    {
        $cuentas = Cuentac::with(["transacciones" => function ($q) {$q->where('tipo', 'Servicio con vale');
        }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.afiliados', compact('cuentas', 'usuario'));
    }

    public function filtrar(Request $request)
    {
        if ($request->filled('id')) {
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('id', $request->input('id'))->get();
            $filtro = array('ID', $request->input('id'));
        } elseif ($request->filled('identificacion')) {
            $cc = $request->input('identificacion');
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('conductor', function ($q) use ($cc) {
                $q->where('NUMERO_IDENTIFICACION', $cc);
            })->get();
            $filtro = array('Identificación', $request->input('identificacion'));
        } elseif ($request->filled('nombre')) {
            $nom = $request->input('nombre');
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('conductor', function ($q) use ($nom) {
                $q->where('NOMBRE', 'like', '%' . $nom . '%');
            })->paginate(30)->appends($request->query());
            $filtro = array('Nombre', $request->input('nombre'));
        } elseif ($request->filled('vale')) {
            $valor = $request->input('vale');
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('transacciones', function ($q) use ($valor) {
                $q->where('valor', '<=', $valor);
            })->paginate(30)->appends($request->query());
            $filtro = array('Último vale', $request->input('vale'));
        } elseif ($request->filled('fechavale')) {
            $fecha = $request->input('fechavale');
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('transacciones', function ($q) use ($fecha) {
                $q->whereDate('fecha', $fecha);
            })->paginate(30)->appends($request->query());
            $filtro = array('Fecha último vale', $request->input('fechavale'));
        } elseif ($request->filled('saldovales')) {
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('saldovales', '<=', $request->input('saldovales'))->paginate(30)->appends($request->query());
            $filtro = array('Saldo cuenta', $request->input('saldovales'));
        } elseif ($request->filled('recargas')) {
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('saldo', '<=', $request->input('recargas'))->paginate(30)->appends($request->query());
            $filtro = array('Saldo recargas', $request->input('recargas'));
        } elseif ($request->filled('estado')) {
            if ($request->input('estado') == "Inactivo") {
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('estado', 'Inactivo')->paginate(30)->appends($request->query());
            } else {
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('estado', '!=', 'Inactivo')->paginate(30)->appends($request->query());
            }
            $filtro = array('Estado', $request->input('estado'));
        } else {
            return redirect('cuentas_afiliados');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.afiliados', compact('cuentas', 'usuario', 'filtro'));
    }

    public function exportar(Request $request)
    {

        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == "ID") {
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('id', $filtro[1])->get();
            } elseif ($filtro[0] == "Identificación") {
                $cc = $filtro[1];
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('conductor', function ($q) use ($cc) {
                    $q->where('NUMERO_IDENTIFICACION', $cc);
                })->get();
            } elseif ($filtro[0] == "Nombre") {
                $nom = $filtro[1];
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('conductor', function ($q) use ($nom) {
                    $q->where('NOMBRE', 'like', '%' . $nom . '%');
                })->get();
            } elseif ($filtro[0] == "Último vale") {
                $valor = $filtro[1];
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('servicios', function ($q) use ($valor) {
                    $q->where('valor', '<=', $valor);
                })->get();
            } elseif ($filtro[0] == "Fecha último vale") {
                $fecha = $filtro[1];
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('servicios', function ($q) use ($fecha) {
                    $q->whereDate('fecha', $fecha);
                })->get();
            } elseif ($filtro[0] == "Saldo cuenta") {
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('saldovales', '<=', $filtro[1])->get();
            } elseif ($filtro[0] == "Saldo recargas") {
                $cuentas = Cuentac::with(["transacciones" => function ($q) {
                    $q->where('tipo', 'Servicio con vale');
                }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('saldo', '<=', $filtro[1])->get();
            } elseif ($filtro[0] == "Estado") {
                if ($filtro[1] == "Inactivo") {
                    $cuentas = Cuentac::with(["transacciones" => function ($q) {
                        $q->where('tipo', 'Servicio con vale');
                    }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('estado', 'Inactivo')->get();
                } else {
                    $cuentas = Cuentac::with(["transacciones" => function ($q) {
                        $q->where('tipo', 'Servicio con vale');
                    }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->where('estado', '!=', 'Inactivo')->get();
                }
            }
        } else {
            $cuentas = Cuentac::with(["transacciones" => function ($q) {
                $q->where('tipo', 'Servicio con vale');
            }, 'conductor'])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("C1:F1");
        $sheet->setCellValue("C1", "Cuentas Corriente Afiliados");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("C1:F1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Identificación");
        $sheet->setCellValue("C2", "Nombre");
        $sheet->setCellValue("D2", "Último vale");
        $sheet->setCellValue("E2", "Fecha último vale");
        $sheet->setCellValue("F2", "Saldo cuenta");
        $sheet->setCellValue("G2", "Saldo recargas");
        $sheet->setCellValue("H2", "Estado");
        $sheet->getStyle("A1:H2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($cuentas as $cuenta) {
            $sheet->setCellValue("A" . $indice, $cuenta->id);
            $sheet->setCellValue("B" . $indice, $cuenta->conductor->NUMERO_IDENTIFICACION);
            $sheet->setCellValue("C" . $indice, $cuenta->conductor->NOMBRE);
            $totra = count($cuenta->transacciones);
            if ($totra > 0) {
                $sheet->setCellValue("D" . $indice, $cuenta->transacciones[$totra-1]->valor);
                $sheet->setCellValue("E" . $indice, $cuenta->transacciones[$totra-1]->fecha);
            } else {
                $sheet->setCellValue("D" . $indice, "");
                $sheet->setCellValue("E" . $indice, "");
            }

            $sheet->setCellValue("F" . $indice, $cuenta->saldovales);
            $sheet->setCellValue("G" . $indice, $cuenta->saldo);
            if ($cuenta->estado == "Inactivo") {
                $sheet->setCellValue("H" . $indice, "Inactiva");
            } else {
                $sheet->setCellValue("H" . $indice, "Activa");
            }
            $indice++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Cuentas_afiliados.xlsx');
        $archivo = file_get_contents('Cuentas_afiliados.xlsx');
        unlink('Cuentas_afiliados.xlsx');

        return base64_encode($archivo);
    }

    public function tomarservicio(Request $request)
    {
        try {
            $servi = Servicio::has('valeav')->select('id')->find($request->input('idservicio'));
            if($servi == null){
                $servicio = Servicio::with(['vale.valera.cuentae.agencia.tercero', 'flota'])->select('id', 'direccion', 'adicional', 'usuarios', 'latitud', 'longitud', 'estado', 'pago', 'cobro', 'placa', 'cuentasc_id', 'flotas_id', 'clientes_id')->where('id', $request->input('idservicio'))->first();
                if($servicio->vale != null){
                    $valeraid = $servicio->vale->valera->id;
                }              
            }else{
                $servicio = Servicio::with(['valeav.valera.cuentae.agencia.tercero', 'flota'])->select('id', 'direccion', 'adicional', 'usuarios', 'latitud', 'longitud', 'estado', 'pago', 'cobro', 'placa', 'cuentasc_id', 'flotas_id', 'clientes_id')->where('id', $request->input('idservicio'))->first();
                $valeraid = $servicio->valeav->valera->id;
                $contrato = Contrato_vale_ruta::where('CONTRATO_VALE', $servicio->valeav->contrato)->where('SECUENCIA', $servicio->valeav->secuencia)->first();
                $ruta = $contrato->ORIGEN . ' --- ' . $contrato->DESTINO;

            }
            $cuentac = Cuentac::with(['conductor.bloqueadas'])->select('id', 'estado', 'saldo', 'saldovales', 'placa', 'conductor_CONDUCTOR')->find($request->input('idtaxista'));

            if ($cuentac->estado == "Libre" || $cuentac->estado == "No disponible") {
                if ($cuentac->saldo < 700) {
                    $servicio = new stdClass();
                    $servicio->tomar = "sinsaldo";

                    return json_encode($servicio);
                }

                if ($servicio->pago == "Vale electrónico") {
                    foreach ($cuentac->conductor->bloqueadas as $agencia) {
                        if ($agencia->id == $valeraid && $agencia->pivot->estado=="Bloqueado") {
                            $servicio = new stdClass();
                            $servicio->tomar = "listanegra";

                            return json_encode($servicio);
                        }
                    }
                }

                if ($servicio->flota != null) {
                    $flota = $servicio->flotas_id;
                    $vehiculo = Vehiculo::where('PLACA', $request->input('placataxista'))->whereHas('flotas', function ($q) use ($flota) {
                        $q->where('flotas_id', $flota);
                    })->first();
                    if ($vehiculo == null) {
                        $servicio2 = new stdClass();
                        $servicio2->tomar = "noflota";
                        $servicio2->flotades = $servicio->flota->descripcion;

                        return json_encode($servicio2);
                    }
                }

                if ($servicio->estado == "Pendiente" || $servicio->estado == "Libre") {
                    if ($cuentac->estado != "Bloqueado") {
                        $servicio->cuentasc_id = $request->input('idtaxista');
                        $servicio->estado = "Asignado";
                        $servicio->placa = $request->input('placataxista');
                        $servicio->save();
                        $servicio->tomar = "tomado";

                        if ($servicio->vale != null) {
                            $servicio->valesid = $servicio->vale->id;
                            $servicio->empresa = $servicio->vale->valera->cuentae->agencia->tercero->RAZON_SOCIAL . " " . $servicio->vale->valera->nombre;
                            /*if($servicio->vale->valeras_id == 101){
                                $servicio->passvale = $servicio->vale->clave;
                            }*/
                        }elseif($servicio->valeav != null){
                            $servicio->rutas = $ruta;
                            $servicio->valesavid = $servicio->valeav->id;
                            $servicio->empresa = $servicio->valeav->valera->cuentae->agencia->tercero->RAZON_SOCIAL . " " . $servicio->valeav->valera->nombre;
                        } else {
                            $servicio->valesid = 0;
                            $servicio->valesavid = 0;
                        }

                        $registro = new Registro();
                        $registro->fecha = Carbon::now();
                        $registro->evento = "Acepta";
                        $registro->servicios_id = $servicio->id;
                        $registro->save();

                        $cuentac->estado = "Ocupado";
                        $cuentac->saldo = $cuentac->saldo - 700;
                        $cuentac->save();

                        $transaccion = new Transaccion();
                        $transaccion->tipo = "Consumo";
                        $transaccion->valor = 700;
                        $transaccion->fecha = Carbon::now();
                        $transaccion->cuentasc_id = $cuentac->id;
                        $transaccion->save();           

                        return json_encode($servicio);
                    } else {
                        $servicio = new stdClass();
                        $servicio->tomar = "bloqueado";

                        return json_encode($servicio);
                    }
                } else {
                    $servicio = new stdClass();
                    $servicio->tomar = "ocupado";

                    return json_encode($servicio);
                }
            } else {
                $servicio = new stdClass();
                $servicio->tomar = "ocupado";

                return json_encode($servicio);
            }
        } catch (Exception $e) {
            $servicio = new stdClass();
            $servicio->tomar = "ocupado";

            $logFile = fopen("../storage/logtomar.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). json_encode($request->input())) or die("Error escribiendo en el archivo");
            fclose($logFile);
        }
    }

    public function arribo(Request $request)
    {
        $servicio = Servicio::with(["cuentac" => function($q){$q->select('id', 'latitud', 'longitud');}])->find($request->input('idservicio'));
        $fecha = Carbon::now('-05:00');

        if ($servicio != null) {
            $registro = new Registro();
            $registro->fecha = $fecha;
            $registro->evento = "Arribo";
            $registro->servicios_id = $servicio->id;
            $registro->save();

            $servicio->estado = "En curso";
            $servicio->save();

            $registro = new Registro();
            $registro->fecha = $fecha;
            $registro->evento = "Inicio";
            $registro->servicios_id = $servicio->id;
            $registro->save();

            if ($servicio->cuentac != null) {
                $seguimiento = new Seguimiento();
                $seguimiento->fecha = $fecha;
                $seguimiento->latitud = $servicio->cuentac->latitud;
                $seguimiento->longitud = $servicio->cuentac->longitud;
                $seguimiento->servicios_id = $servicio->id;
                $seguimiento->save();
            }
        }

        return "OK";
    }

    public function cancelar(Request $request)
    {
        $servicio = Servicio::select('id', 'estado')->with('vale', 'valeav')->find($request->input('idservicio'));
        if ($servicio->estado == "Asignado" || $servicio->estado == "En curso") {
            $servicio->estado = "Cancelado";
            $servicio->save();

            if($servicio->vale != null){
                if ($servicio->vale->centrocosto != null) {
                    $servicio->vale->estado = "Asignado";
                } else {
                    $servicio->vale->estado = "Libre";
                }
                $servicio->vale->servicios_id = null;
                $servicio->vale->save();
            }elseif ($servicio->valeav != null) {
                    $servicio->valeav->estado = "Libre";
                    $servicio->valeav->centrocosto = null;
                    $servicio->valeav->contrato = null;
                    $servicio->valeav->secuencia = null;
                    $servicio->valeav->vuelo = null;
                    $servicio->valeav->tipo = null;
                    $servicio->valeav->tiposer = null;
                    $servicio->valeav->servicios_id = null;
                    //$servicio->valeav->firma = null;
                    $servicio->valeav->save();      
            }
            $cancelacion = Cancelacion::where('servicios_id' , $servicio->id)->first();
            if($cancelacion == null){
                $cancelacion = new Cancelacion();
            }
            
            $cancelacion->razon = $request->input('razon');
            $cancelacion->fecha = Carbon::now('-05:00');
            $cancelacion->servicios_id = $servicio->id;
            $cancelacion->save();

            $cuentac = Cuentac::select('id', 'estado')->find($request->input('idtaxista'));
            if($servicio->estado != "Bloqueado"){
                $cuentac->estado = "Libre";
                $cuentac->save();    
            }
            
            return "OK";
        } else {
            return $servicio->estado;
        }
    }

    public function inicioservicio(Request $request)
    {
        $servicio = Servicio::select('id', 'estado')->find($request->input('idservicio'));
        $servicio->estado = "En curso";
        $servicio->save();

        $registro = new Registro();
        $registro->fecha = Carbon::now('-05:00');
        $registro->evento = "Inicio";
        $registro->servicios_id = $servicio->id;
        $registro->save();

        return "OK";
    }

    public function seguimiento(Request $request)
    {
        $cuentac = Cuentac::select('id', 'latitud', 'placa', 'longitud')->find($request->input('idtaxista'));
        $hora = Carbon::now();
        if ($cuentac == null) {
            $servicio = Servicio::select('id', 'estado', 'placa', 'cuentasc_id')->with(['cuentac' => function($q){$q->select('id', 'latitud', 'placa', 'longitud');}, 'ruta'])->find($request->input('idservicio'));
            $cuentac = $servicio->cuentac;
        }else{
            $servicio = Servicio::select('id', 'estado', 'placa', 'cuentasc_id')->with(['cuentac' => function($q){$q->select('id', 'latitud', 'placa', 'longitud');}, 'ruta'])->find($request->input('idservicio'));
        }
        $seguimiento = new Seguimiento();
        $seguimiento->fecha = $hora;
        $seguimiento->latitud = $request->input('latitud');
        $seguimiento->longitud = $request->input('longitud');
        $seguimiento->servicios_id = $request->input('idservicio');
        $seguimiento->save();

        $cuentac->latitud = $request->input('latitud');
        $cuentac->longitud = $request->input('longitud');
        $cuentac->save();

        /*$vehiculo = Vehiculo::where('PLACA', $cuentac->placa)->first();
        //if ($vehiculo != null) {
            $record = $this->distancia($request->input('latitud'), $request->input('longitud'), $cuentac->latitud, $cuentac->longitud);
            $vehiculo->kilometros = $vehiculo->kilometros + $record;
            $vehiculo->save(); 
        //}    */

        return "OK";
    }

    public function preServicio(Request $request)
    {
        $servicio = Servicio::with('vale.valera.cuentae.agencia')->find($request->input('idservicio'));

        if ($servicio->estado == "En curso" || $servicio->estado == "Asignado") {
            if ($servicio->pago == "Vale electrónico") {
                //$hoy = date_parse_from_format('Y-m-d H:i:s', Carbon::now('-05:00'));
                $servicio->unidades = $request->input('unidades');
                if ($request->input('cobro') == "Unidades") {
                    $servicio->cobro = "Unidades";
                    $contrato = Contrato_vale::with('tarifa.unidadvalores')->where('TERCERO', $servicio->vale->valera->cuentae->agencia_tercero_TERCERO)->orderBy('CONTRATO_VALE', 'DESC')->first();
                    foreach ($contrato->tarifa->unidadvalores as $valor) {
                        if ($servicio->unidades >= $valor->UNIDAD_INICIO && $servicio->unidades <= $valor->UNIDAD_FIN) {
                            $servicio->valor = $valor->VALOR;
                            break;
                        }
                    }
                    if ($servicio->valor > 50000) {
                        return "Exceso_El valor de un vale finalizado por unidades no puede superar los $ 50,000 pesos";
                    }
                } elseif ($request->input('cobro') == "Horas" && $servicio->vale != null) {
                    if($servicio->estadocobro == 1){
                        $servicio->cobro = "Minutos";
                        $contrato = Contrato_vale::where('TERCERO', $servicio->vale->valera->cuentae->agencia_tercero_TERCERO)->orderBy('CONTRATO_VALE', 'DESC')->first();
                        $servicio->valor = round(($servicio->unidades / 60) * $contrato->TARIFA_COBRO_RECORRIDO);
                    }else{
                        return "Falla icon_Debe notificar a la central para finalizar por Horas";
                    }
                } else {
                    if($servicio->estadocobro == 1){
                        $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $request->input('contrato'))->where('SECUENCIA', $request->input('secuencia'))->first();
                        $servicio->cobro = "Ruta";
                        $servicio->valor = round($ruta->TARIFA_COBRO);
                    }else{
                        return "Falla icon_Debe notificar a la central para finalizar por Ruta";;
                    }
                }
                $servicio->estado = "Finalizado";

                $vale = $servicio->vale;

                if ($vale->clave == strtolower($request->input('clavevale'))) {

                    return $servicio->valor;

                } else {
                    return "Clave incorrecta";
                }
            }
        } else {
            return "Finalizado";
        }
    }

    public function finservicio(Request $request)
    {
        set_time_limit(60);
        $registro = new Registro();
        $registro->fecha = Carbon::now('-05:00');
        $registro->evento = "Fin";
        $registro->servicios_id = $request->input('idservicio');

        $servicio = Servicio::with('vale.valera.cuentae.agencia')->find($request->input('idservicio'));
        

        if ($servicio->estado == "En curso" || $servicio->estado == "Asignado") {
            if ($servicio->pago == "Vale electrónico") {
                //$hoy = date_parse_from_format('Y-m-d H:i:s', Carbon::now('-05:00'));
                $servicio->unidades = $request->input('unidades');
                if ($request->input('cobro') == "Unidades") {
                    $servicio->cobro = "Unidades";
                    $contrato = Contrato_vale::with('tarifa.unidadvalores')->where('TERCERO', $servicio->vale->valera->cuentae->agencia_tercero_TERCERO)->orderBy('CONTRATO_VALE', 'DESC')->first();
                    foreach ($contrato->tarifa->unidadvalores as $valor) {
                        if ($servicio->unidades >= $valor->UNIDAD_INICIO && $servicio->unidades <= $valor->UNIDAD_FIN) {
                            $servicio->valor = $valor->VALOR;
                            break;
                        }
                    }
                    if ($servicio->valor > 50000) {
                        return "Exceso_El valor de un vale finalizado por unidades no puede superar los $ 50,000 pesos";
                    }
                    $soapunidades = $servicio->unidades;
                    $soaphoras = "0";
                    $soapminutos = "0";
                    $soapruta = "";
                } elseif ($request->input('cobro') == "Horas" && $servicio->vale != null) {
                    $servicio->cobro = "Minutos";
                    $contrato = Contrato_vale::where('TERCERO', $servicio->vale->valera->cuentae->agencia_tercero_TERCERO)->orderBy('CONTRATO_VALE', 'DESC')->first();
                    $servicio->valor = round(($servicio->unidades / 60) * $contrato->TARIFA_COBRO_RECORRIDO);
                    $soaphoras = floor($servicio->unidades / 60);
                    $soapminutos = $servicio->unidades % 60;
                    $soapunidades = "0";
                    $soapruta = "";
                } else {
                    $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $request->input('contrato'))->where('SECUENCIA', $request->input('secuencia'))->first();
                    $servicio->cobro = "Ruta";
                    $servicio->valor = round($ruta->TARIFA_COBRO);
                    $soapruta = "R." . $request->input('secuencia');
                    $soapunidades = "0";
                    $soaphoras = "0";
                    $soapminutos = "0";
                    
                }
                $servicio->estado = "Finalizado";

                $vale = $servicio->vale;

                if ($vale->clave == strtolower($request->input('clavevale'))) {
                    $vale->estado = "Usado";
                    $cuentac = Cuentac::with(['conductor' => function($q)
                    {$q->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');}])->select('id', 'estado', 'saldo', 'saldovales', 'bono', 'conductor_CONDUCTOR')->find($servicio->cuentasc_id);

                    if ($request->input('cobro') == "Horas") {
                        $pagoc = $contrato->TARIFA_PAGO_RECORRIDO * ($servicio->unidades / 60);
                        $cuota = round(($pagoc - ($pagoc * 0.07)), 0, PHP_ROUND_HALF_DOWN);
                    } elseif ($request->input('cobro') == "Rutas") {
                        $cuota = round(($ruta->TARIFA_PAGO - ($ruta->TARIFA_PAGO * 0.07)), 0, PHP_ROUND_HALF_DOWN);
                    } else {
                        $cuota = round(($servicio->valor - ($servicio->valor * 0.07)), 0, PHP_ROUND_HALF_DOWN);
                    }
                    $uno = $cuota % 100;
                    $cuota = $cuota - $uno;

                    $cuentac->saldovales = $cuentac->saldovales + $cuota;
                    if($cuentac->estado != "Bloqueado"){
                        $cuentac->estado = "Libre";
                    }                    
                    $servicio->valorc = $cuota;

                    $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

                    try {
                        $client = new SoapClient($url, ['exceptions' => true]);
                        $result = $client->registrarTicket();
                        $dia = date_parse_from_format('Y-m-d H:i:s', $servicio->fecha);
                        if ($dia["month"] < 10) {
                            $dia["month"] = "0" . $dia["month"];
                        }
                        if ($dia["day"] < 10) {
                            $dia["day"] = "0" . $dia["day"];
                        }

                        $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionEmpresa" => $vale->valera->cuentae->agencia->NRO_IDENTIFICACION, "nombreValera" => $vale->valera->nombre, "codigoVale" => $vale->codigo, "fechaServicio" => $dia["year"] . "/" . $dia["month"] . "/" . $dia["day"],  "horaServicio" => $dia["hour"] . ":" . $dia["minute"] . ":" . $dia["second"], "usuarioVale" => $servicio->usuarios, "placa" => $servicio->placa, "numeroIdentificacionConductor" => $cuentac->conductor->NUMERO_IDENTIFICACION, "unidades" => $soapunidades, "horaMovimiento" => $soaphoras, "minutoMovimiento" => $soapminutos, "horaEspera" => "0", "minutoEspera" => "0", "rutas" => $soapruta, "valor" => $servicio->valor, "centroCosto" => $vale->centrocosto, "referenciado" => $vale->referenciado);
                        $peticion = $client->registrarVale($parametros);
                        if ($peticion->registrarValeReturn->codigoError == "0000") {
                        //if (true) {
                            $transaccion = new Transaccion();
                            $transaccion->tipo = "Servicio con vale";
                            $transaccion->valor = $cuota;
                            $transaccion->fecha = Carbon::now('-05:00');
                            $transaccion->cuentasc_id = $cuentac->id;
                            $transaccion->save();

                            $registro->save();
                            $servicio->save();
                            $vale->save();
                            
                            if($servicio->cobro == "Unidades" && $servicio->unidades <= 83){
                                $cuentac->bono = $cuentac->bono + 1;
                                $mensaje = new Mensaje();
                                $mensaje->fecha = Carbon::now();
                                $mensaje->sentido = "Recibido";
                                $mensaje->estado = "Pendiente";
                                $mensaje->cuentasc_id = $cuentac->id;
                                if($cuentac->bono == 10){
                                    $transaccion = new Transaccion();
                                    $transaccion->tipo = "Recarga";
                                    $transaccion->tiporecarga = "Cortesía";
                                    $transaccion->fecha = Carbon::now();
                                    $transaccion->valor = 3500;
                                    $transaccion->cuentasc_id = $cuentac->id;
                                    $transaccion->save();
                                    $cuentac->saldo = $cuentac->saldo + 3500;
                                    $mensaje->texto = "Felicidades, ganaste una recarga de $3.500 por completar 10 servicios de tarifa mínima";
                                    $cuentac->bono = 0;
                                }else{
                                    $mensaje->texto = "Has acumulado " . $cuentac->bono . " servicios de tarifa mínima. Llega hasta 10 para obtener una recarga de Cortesía por $3.500";
                                }
                                $mensaje->save();
                            }
                            $cuentac->save();

                            return $servicio->valor;
                        } else {
                            $wsservicio=Ws_servicioIcon::where('DATOS', 'like', '%|' . $servicio->vale->valera->nombre . '|' . $servicio->vale->codigo . '|%')->Where('CODIGO_ERROR', '!=', '0000')->delete();
                            return "Falla icon_" . $peticion->registrarValeReturn->mensajeError;                       
                        }
                    } catch (SoapFault $e) {
                        return "Falla icon_" . $e->getMessage();
                    } catch (Exception $e) {
                        return "Falla icon_" . $e->getMessage();
                    }           
                } else {
                    return "Clave incorrecta";
                }
            } else {
                $servicio->estado = "Finalizado";
                $servicio->save();

                $registro = new Registro();
                $registro->fecha = Carbon::now();
                $registro->evento = "Fin";
                $registro->servicios_id = $request->input('idservicio');
                $registro->save();

                $cuentac = Cuentac::select('id', 'estado')->find($request->input('idtaxista'));
                if($cuentac->estado != "Bloqueado"){
                    $cuentac->estado = "Libre";
                }   
                $cuentac->save();

                return "OK";
            }
        } else {
            return "Finalizado";
        }
    }

    public function alerta(Request $request)
    {
        $alerta = new Alerta();
        $alerta->latitud = $request->input('latitud');
        $alerta->longitud = $request->input('longitud');
        $alerta->fecha = Carbon::now('-05:00');
        $alerta->tipo = $request->input('tipo');
        $alerta->placa = $request->input('placa');
        $alerta->cuentasc_id = $request->input('id');
        $alerta->estado = "Pendiente";
        $alerta->save();

        return "OK";
    }

    public function cambiarestado(Request $request)
    {
        $cuentac = Cuentac::select('id', 'estado')->find($request->input('idtaxista'));
        if($cuentac->estado != "Bloqueado"){
            $cuentac->estado = $request->input('estado');
        }    
        $cuentac->save();

        return "OK";
    }

    public function getrutas(Request $request)
    {
        $vale = Vale::with('valera.cuentae')->find($request->input('idvale'));

        $tercero = Tercero::with('contratovale')->has('contratovale')->where('TERCERO', $vale->valera->cuentae->agencia_tercero_TERCERO)->first();
        $cantidad = count($tercero->contratovale);
        $rutas = Contrato_vale_ruta::where('CONTRATO_VALE', $tercero->contratovale[$cantidad - 1]->CONTRATO_VALE)->where('SW_ACTIVO', '1')->get();

        return json_encode($rutas);
    }

    public function getsaldos(Request $request)
    {
        $cuentac = Cuentac::select('id', 'estado', 'saldo', 'saldovales')->find($request->input('id'));
        if($cuentac->estado != "Bloqueado"){
            $cuentac->estado = "Libre";
        }  
        $cuentac->save();

        return json_encode($cuentac);
    }

    public function servicioincompleto(Request $request)
    {

        $servi = Servicio::has('valeav')->select('id')->find($request->input('idservicio'));
        if($servi == null){
            $servicio = Servicio::with(['cuentac' => function($q){$q->select('id', 'estado');},'vale.valera.cuentae.agencia.tercero'])->where('id', $request->input('idservicio'))->first();
        }else{
            $servicio = Servicio::with(['cuentac' => function($q){$q->select('id', 'estado');},'valeav.valera.cuentae.agencia.tercero'])->where('id', $request->input('idservicio'))->first();
            $vales = Valeav::where('servicios_id', $servicio->id)->get();
            $rutas = "";
            foreach ($vales as $vale) {
                $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $vale->contrato)->where('SECUENCIA', $vale->secuencia)->first();
                $rutas = $rutas . $ruta->ORIGEN . ' --- ' . $ruta->DESTINO . '\n';
            }
        }
        //$servicio = Servicio::with(['cuentac' => function($q){$q->select('id', 'estado');}, 'vale.valera.cuentae.agencia.tercero', 'cliente'])->find($request->input('idservicio'));

        if ($servicio->vale != null) {
            $servicio->valesid = $servicio->vale->id;
            $servicio->empresa = $servicio->vale->valera->cuentae->agencia->tercero->RAZON_SOCIAL . " " . $servicio->vale->valera->nombre;              
            /*if($servicio->vale->valeras_id == 101){
                $servicio->passvale = $servicio->vale->clave;
            }*/
        } elseif($servicio->valeav != null){
            $servicio->rutas = $rutas;
            $servicio->valesavid = $servicio->valeav->id;
            $servicio->empresa = $servicio->valeav->valera->cuentae->agencia->tercero->RAZON_SOCIAL . " " . $servicio->valeav->valera->nombre;
        }else {
            $servicio->valesid = 0;
            $servicio->valesavid = 0;
        }

        if ($servicio->estado == "En curso" || $servicio->estado == "Asignado") {
            $servicio->cuentac->estado = "Ocupado";
            $servicio->cuentac->save();
        }

        return json_encode($servicio);
    }

    public function revisarservicio(Request $request)
    {
        $servicio = Servicio::with(['cuentac'=>function($q){$q->select('id', 'estado', 'latitud', 'placa', 'longitud');}, 'ruta'])->find($request->input('idservicio'));
        $cuentac = $servicio->cuentac;

        if ($servicio->estado == "Asignado" || $servicio->estado == "En curso") {
            $hora = Carbon::now();
            if ($request->filled('seguir')) {
                $seguimiento = new Seguimiento();
                $seguimiento->fecha = $hora;
                $seguimiento->latitud = $request->input('latitud');
                $seguimiento->longitud = $request->input('longitud');
                $seguimiento->servicios_id = $request->input('idservicio');
                $seguimiento->save();
                
                if($servicio->ruta != null){
                    if($servicio->ruta->llegada == null){
                       
                        $lat = 7.062580;
                        $lng = -73.124414;
                        if($this->distancia($seguimiento->latitud, $seguimiento->longitud, $lat, $lng) <= 0.12){                       
                            $servicio->ruta->llegada = $hora->format('H:i');
                            $servicio->ruta->save();
                        }     
                    }
                }
            }

            if ($request->filled('latitud')) {
                $cuentac->latitud = $request->input('latitud');
                $cuentac->longitud = $request->input('longitud');
                $cuentac->estado = "Ocupado";
                $cuentac->save();
            }

            /*$vehiculo = Vehiculo::where('PLACA', $cuentac->placa)->first();
            if ($vehiculo != null) {
                $record = $this->distancia($request->input('latitud'), $request->input('longitud'), $cuentac->latitud, $cuentac->longitud);
                $vehiculo->kilometros = $vehiculo->kilometros + $record;
                $vehiculo->save(); 
            }  */
        } else {
            if($cuentac->estado != "Bloqueado"){
                $cuentac->estado = "Libre";
            }  
            $cuentac->save();
        }

        return json_encode($servicio);
    }

    public function historial(Request $request)
    {
        $servicios = Servicio::select('id', 'estado', 'direccion', 'pago', 'valorc', 'fecha', 'fechaprogramada')->where("cuentasc_id", $request->input('idtaxista'))->orderBy('id', 'DESC')->get()->take(50);

        foreach ($servicios as $servicio) {
            if ($servicio->fechaprogramada == null) {
                $servicio->pago = $servicio->fecha . ", " . $servicio->pago . ": $" . number_format($servicio->valorc);
            }else{
                $servicio->pago = $servicio->fechaprogramada . ", " . $servicio->pago . ": $" . number_format($servicio->valorc);
            }
            $servicio->direccion = $servicio->direccion . " - " . $servicio->estado;         
        }

        return json_encode($servicios);
    }

    public function informacion(Request $request)
    {
        $cuentac = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'DIRECCION', 'TELEFONO', 'CELULAR', 'EMAIL');}])->find($request->input('idtaxista'));
        $cuentac->direccion = $cuentac->conductor->DIRECCION;
        $cuentac->telefono =  $cuentac->conductor->TELEFONO;
        $cuentac->celular =  $cuentac->conductor->CELULAR;
        $cuentac->email =  $cuentac->conductor->EMAIL;

        return json_encode($cuentac);
    }

    public function updatecuenta(Request $request)
    {
        $cuentac = Cuentac::select('id', 'password', 'conductor_CONDUCTOR')->with('conductor')->find($request->input('idtaxista'));

        if ($request->filled('celular')) {
            $cuentac->conductor->CELULAR = $request->input('celular');
        }
        if ($request->filled('telefono')) {
            $cuentac->conductor->TELEFONO = $request->input('telefono');
        }
        if ($request->filled('email')) {
            $cuentac->conductor->EMAIL = $request->input('email');
        }
        if ($request->filled('direccion')) {
            $cuentac->conductor->DIRECCION = $request->input('direccion');
        }

        if ($request->hasFile('foto')) {
            if ($request->file('foto')->isValid()) {
                $foto = $request->file('foto');
                $cuentac->foto = base64_encode(file_get_contents($foto));
            }
        }
        if ($request->filled('clave')) {
            $cuentac->password = $request->input('clave');
        }

        //$cuentac->conductor->save();
        $cuentac->save();

        $conductorI = ConductorIcon::find($cuentac->conductor->CONDUCTOR);
        $conductorI->DIRECCION = $cuentac->conductor->DIRECCION;
        $conductorI->CELULAR = $cuentac->conductor->CELULAR;
        $conductorI->TELEFONO = $cuentac->conductor->TELEFONO;
        $conductorI->EMAIL = $cuentac->conductor->EMAIL;
        $conductorI->save();

        return "OK";
    }

    public function recargar(Request $request)
    {
        $logFile = fopen("../storage/logRecargas.txt", 'a') or die("Error creando archivo");
        $cuentac = Cuentac::with(['conductor' => function($q)
        {$q->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');}])->select('id', 'estado', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->find($request->input('idtaxista'));
        set_time_limit(60);
        if($request->input('valor') >= 11900){
            if ($request->input('valor') <= $cuentac->saldovales) {
                $cuentac->saldo = $cuentac->saldo + $request->input('valor');
                $cuentac->saldovales = $cuentac->saldovales - $request->input('valor');
    
                $transaccion = new Transaccion();
                $transaccion->tipo = "Transferencia";
                $transaccion->valor = $request->input('valor');
                $transaccion->fecha = Carbon::now('-05:00');
                $transaccion->comentarios = "Transferencia entre saldos desde la aplicación";
                $transaccion->cuentasc_id = $cuentac->id;
    
                $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";
    
                try {
                    $client = new SoapClient($url, ['exceptions' => true]);
                    $result = $client->registrarTicket();
                    $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionConductor" => $cuentac->conductor->NUMERO_IDENTIFICACION, "numeroIdentificacionEmpresa" => "900886956", "monto" => $request->input('valor'), "tipo" => "2");
                    $peticion = $client->registrarRecarga($parametros);
    
                    fwrite($logFile, "\n".date("d/m/Y H:i:s"). json_encode($peticion->registrarRecargaReturn)) or die("Error escribiendo en el archivo");
                    fclose($logFile);
                    
                    if ($peticion->registrarRecargaReturn->codigoError != "0000") {
                        return "Falla icon_" . $peticion->registrarRecargaReturn->mensajeError;
                    }
                } catch (SoapFault $e) {
                    fwrite($logFile, "\n".date("d/m/Y H:i:s"). $e->getMessage() . "--" . $e->getLine()) or die("Error escribiendo en el archivo");
                    fclose($logFile);

                    return "Falla icon_" . $e->getMessage();
                } catch(Exception $e){
                    fwrite($logFile, "\n".date("d/m/Y H:i:s"). $e->getMessage(). "--" . $e->getLine()) or die("Error escribiendo en el archivo");
                    fclose($logFile);

                    return "Falla icon_" . $e->getMessage();
                }
    
                $cuentac->save();
                $transaccion->save();
    
                return "OK";
            } else {
                return "saldo";
            }
        }else{
            return "Falla icon_La recarga mínima es de 11.900";
        }
    }

    public function salir(Request $request)
    {
        $cuentac = Cuentac::select('id', 'estado')->find($request->input('idtaxista'));
        if($cuentac->estado != "Bloqueado"){
            $cuentac->estado = "No disponible";
            $cuentac->save();
        }         
    }

    public function cambiarPlaca(Request $request)
    {
        $cuentac = Cuentac::select('id', 'estado', 'placa')->find($request->input('idtaxista'));
        $cplacas = Cuentac::where('placa', $request->input('placa'))->where('id', '<>', $cuentac->id)->where(function ($q) {
            $q->where('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio');
        })->count();

        if ($cplacas == 0) {
            $cuentac->placa = $request->input('placa');
            $cuentac->save();

            return "OK";
        } else {

            return "ocupada";
        }
    }

    public function logout(Request $request)
    {
        $cuentac = Cuentac::select('id', 'estado')->find($request->input('idtaxista'));
        if ($cuentac != null) {
            if($cuentac->estado != "Bloqueado"){
                $cuentac->estado = "No disponible";
                $cuentac->save();
            }           
        }

        return "OK";
    }

    public function consultarServicio(Request $request)
    {
        $servi = Servicio::has('valeav')->where('cuentasc_id', $request->input('idtaxista'))->where(function ($q) {$q->where('estado', 'Asignado')->orWhere('estado', 'En curso');})->first();            
        if($servi == null){
            $servicio = Servicio::with(['cuentac'=>function($q){$q->select('id', 'estado');}, 'vale.valera.cuentae.agencia.tercero'])->where('cuentasc_id', $request->input('idtaxista'))->where(function ($q) {$q->where('estado', 'Asignado')->orWhere('estado', 'En curso');})->first();            
        }else{
            $servicio = Servicio::with(['cuentac'=>function($q){$q->select('id', 'estado');}, 'valeav.valera.cuentae.agencia.tercero'])->where('cuentasc_id', $request->input('idtaxista'))->where(function ($q) {$q->where('estado', 'Asignado')->orWhere('estado', 'En curso');})->first();            
            $vales = Valeav::where('servicios_id', $servicio->id)->get();
            $rutas = "";
            foreach ($vales as $vale) {
                $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $vale->contrato)->where('SECUENCIA', $vale->secuencia)->first();
                $rutas = $rutas . $ruta->ORIGEN . ' --- ' . $ruta->DESTINO . PHP_EOL;
            }
        }
        
        if ($servicio != null) {
            if ($servicio->vale != null) {
                $servicio->valesid = $servicio->vale->id;
                $servicio->empresa = $servicio->vale->valera->cuentae->agencia->tercero->RAZON_SOCIAL . " " . $servicio->vale->valera->nombre;
                if($servicio->vale->valera->cuentae->agencia_tercero_TERCERO == 3408){
                    $servicio->passvale = $servicio->vale->clave;
                }
            }elseif($servicio->valeav != null){
                $servicio->rutas = $rutas;
                $servicio->valesavid = $servicio->valeav->id;
                $servicio->empresa = $servicio->valeav->valera->cuentae->agencia->tercero->RAZON_SOCIAL . " " . $servicio->valeav->valera->nombre;
            } else {
                $servicio->valesid = 0;
                $servicio->valesavid = 0;
            }

            if($servicio->cuentac->estado != "Bloqueado"){
                $servicio->cuentac->estado = "Ocupado";
            }
            
            $servicio->cuentac->save();
        }

        return json_encode($servicio);
    }

    public function calificar(Request $request)
    {
        $servicio = Servicio::select('id', 'clientes_id')->find($request->input('servicio'));

        if($servicio != null){
            $calificacion = new CalificacionCli();
            $calificacion->puntaje = $request->input('puntaje');
            $calificacion->clientes_id = $servicio->clientes_id;
            $calificacion->save();
        }
        
        return "OK";

    }
}
