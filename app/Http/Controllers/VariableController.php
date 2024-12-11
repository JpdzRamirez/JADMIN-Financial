<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\Factura;
use App\Models\FormaPago;
use App\Models\Producto;
use App\Models\Resolucion;
use App\Models\Retefuente;
use App\Models\Reteica;
use App\Models\Reteiva;
use App\Models\TerceroCahors;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VariableController extends Controller
{
    public function retefuentes()
    {
        $retefuentes = Retefuente::with('venta', 'compra')->get();

        return view('variables.retefuentes', compact('retefuentes'));
    }

    public function registrarRetefuente(Request $request)
    {
        try {
            $retefuente = new Retefuente();
            $retefuente->concepto = $request->input('concepto');
            $retefuente->porcentaje = $request->input('valor');
            $venta = Cuenta::where('codigo', $request->input('venta'))->first();
            if($venta != null){
                $retefuente->cuentas_id = $venta->id;
            }else{
                return back()->with('error', "El codigo de la cuenta de venta es incorrecto");
            }
            $compra = Cuenta::where('codigo', $request->input('compra'))->first();
            if($compra != null){
                $retefuente->cuentas_id1 = $compra->id;
            }else{
                return back()->with('error', "El codigo de la cuenta de compra es incorrecto");
            }
            $retefuente->save();

            return back()->with('ok', "Retefuente registrado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
        
    }

    public function editarRetefuente(Request $request)
    {
        try {
            $retefuente = Retefuente::find($request->input('idretefuente'));
            $retefuente->concepto = $request->input('econcepto');
            $retefuente->porcentaje = $request->input('evalor');
            $venta = Cuenta::where('codigo', $request->input('eventa'))->first();
            if($venta != null){
                $retefuente->cuentas_id = $venta->id;
            }else{
                return back()->with('error', "El código de la cuenta de venta es incorrecto");
            }
            $compra = Cuenta::where('codigo', $request->input('ecompra'))->first();
            if($compra != null){
                $retefuente->cuentas_id1 = $compra->id;
            }else{
                return back()->with('error', "El código de la cuenta de compra es incorrecto");
            }
            $retefuente->save();

            return back()->with('ok', "Retefuente editado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
        
    }

    public function reteicas()
    {
        $reteicas = Reteica::with('venta', 'compra')->get();

        return view('variables.reteicas', compact('reteicas'));
    }

    public function registrarReteica(Request $request)
    {
        try {
            $reteica = new Reteica();
            $reteica->concepto = $request->input('concepto');
            $reteica->porcentaje = $request->input('valor');
            $venta = Cuenta::where('codigo', $request->input('venta'))->first();
            if($venta != null){
                $reteica->cuentas_id = $venta->id;
            }else{
                return back()->with('error', "El codigo de la cuenta de venta es incorrecto");
            }
            $compra = Cuenta::where('codigo', $request->input('compra'))->first();
            if($compra != null){
                $reteica->cuentas_id1 = $compra->id;
            }else{
                return back()->with('error', "El codigo de la cuenta de compra es incorrecto");
            }
            $reteica->save();

            return back()->with('ok', "Reteica registrado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
        
    }

    public function editarreteica(Request $request)
    {
        try {
            $reteica = reteica::find($request->input('idreteica'));
            $reteica->concepto = $request->input('econcepto');
            $reteica->porcentaje = $request->input('evalor');
            $venta = Cuenta::where('codigo', $request->input('eventa'))->first();
            if($venta != null){
                $reteica->cuentas_id = $venta->id;
            }else{
                return back()->with('error', "El código de la cuenta de venta es incorrecto");
            }
            $compra = Cuenta::where('codigo', $request->input('ecompra'))->first();
            if($compra != null){
                $reteica->cuentas_id1 = $compra->id;
            }else{
                return back()->with('error', "El código de la cuenta de compra es incorrecto");
            }
            $reteica->save();

            return back()->with('ok', "Reteica editado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
        
    }






    public function reteivas()
    {
        $reteivas = Reteiva::with('compra')->get();

        return view('variables.reteivas', compact('reteivas'));
    }

    public function registrarReteiva(Request $request)
    {
        try {
            $reteiva = new Reteiva();
            $reteiva->concepto = $request->input('concepto');
            $reteiva->porcentaje = $request->input('valor');
           
            $compra = Cuenta::where('codigo', $request->input('compra'))->first();
            if($compra != null){
                $reteiva->cuentas_id1 = $compra->id;
            }else{
                return back()->with('error', "El codigo de la cuenta de compra es incorrecto");
            }
            $reteiva->save();

            return back()->with('ok', "Reteiva registrado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
        
    }

    public function editarReteiva(Request $request)
    {
        try {
            $reteiva = Reteiva::find($request->input('idreteiva'));
            $reteiva->concepto = $request->input('econcepto');
            $reteiva->porcentaje = $request->input('evalor');
            $compra = Cuenta::where('codigo', $request->input('ecompra'))->first();
            if($compra != null){
                $reteiva->cuentas_id1 = $compra->id;
            }else{
                return back()->with('error', "El código de la cuenta de compra es incorrecto");
            }
            $reteiva->save();

            return back()->with('ok', "Reteiva editado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
        
    }






    public function productos()
    {
        $productos = Producto::paginate(10);

        return view('variables.productos', compact('productos'));
    }

    public function registrarProducto(Request $request)
    {
        try {
            $producto = new Producto();
            $producto->nombre = $request->input('nombre');
            $cuenta = Cuenta::where('codigo', $request->input('cuenta'))->first();
            if($cuenta != null){
                $producto->cuentas_id = $cuenta->id;
            }else{
                return back()->with('error', "El codigo de la cuenta es incorrecto");
            }
            $contrapartida = Cuenta::where('codigo', $request->input('contrapartida'))->first();
            if($contrapartida != null){
                $producto->cuentas_id1 = $contrapartida->id;
            }else{
                return back()->with('error', "El codigo de la contrapartida es incorrecto");
            }
            $producto->save();

            return back()->with('ok', "Producto registrado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
    }

    public function editarProducto(Request $request)
    {
        try {
            $producto = Producto::find($request->input('idproducto'));
            $producto->nombre = $request->input('enombre');
            $cuenta = Cuenta::where('codigo', $request->input('ecuenta'))->first();
            if($cuenta != null){
                $producto->cuentas_id = $cuenta->id;
            }else{
                return back()->with('error', "El codigo de la cuenta es incorrecto");
            }
            $contrapartida = Cuenta::where('codigo', $request->input('econtrapartida'))->first();
            if($contrapartida != null){
                $producto->cuentas_id1 = $contrapartida->id;
            }else{
                return back()->with('error', "El codigo de la contrapartida es incorrecto");
            }
            $producto->save();

            return back()->with('ok', "Producto registrado exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
    }

    public function formasPago()
    {
        $formas = FormaPago::paginate(10);

        return view('variables.formasPago', compact('formas'));
    }

    public function registrarFormaPago(Request $request)
    {
        try {
            $forma = new FormaPago();
            $forma->nombre = $request->input('nombre');
            $forma->prefijo = $request->input('prefijo');
            $cuenta = Cuenta::where('codigo', $request->input('cuenta'))->first();
            if($cuenta != null){
                $forma->cuentas_id = $cuenta->id;
            }else{
                return back()->with('error', "El codigo de la cuenta es incorrecto");
            }
            $forma->save();

            return back()->with('ok', "Forma de pago registrada exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
    }

    public function editarFormaPago(Request $request)
    {
        try {
            $forma = FormaPago::find($request->input('idforma'));
            $forma->nombre = $request->input('enombre');
            $forma->prefijo = $request->input('eprefijo');
            $cuenta = Cuenta::where('codigo', $request->input('ecuenta'))->first();
            if($cuenta != null){
                $forma->cuentas_id = $cuenta->id;
            }else{
                return back()->with('error', "El codigo de la cuenta es incorrecto");
            }
            $forma->save();

            return back()->with('ok', "Forma de pago registrada exitosamente");
        } catch (Exception $ex) {
            return back()->with('error', $ex->getMessage());
        }
    }

    public function formasPagoGet(Request $request)
    {
        $forma = FormaPago::with('cuenta')->find($request->input('forma'));

        return json_encode($forma);
    }

    function extraContableAsiento(Request $request)
    {
        $movimientos = json_decode($request->input('movimientos'));
        $cuenta = Cuenta::where('codigo', $request->input('cuenta'))->first();
        $contrapartida = Cuenta::where('codigo', $request->input('contrapartida'))->first();
        $tercero = TerceroCahors::where('documento', $request->input('tercero'))->first();
        $valor = $request->input('valor');

        $mov = (object) ["id"=>$cuenta->id, "codigo"=>$cuenta->codigo, "nombre"=>$cuenta->nombre, "tipo"=>"Crédito", "valor"=>$valor, "tercero"=>$tercero->documento . "-" . $tercero->nombre, "idtercero"=>$tercero->id, "extra"=>1];
        $movimientos[] = $mov;
        $mov = (object) ["id"=>$contrapartida->id, "codigo"=>$contrapartida->codigo, "nombre"=>$contrapartida->nombre, "tipo"=>"Débito", "valor"=>$valor, "tercero"=>$tercero->documento . "-" . $tercero->nombre, "idtercero"=>$tercero->id, "extra"=>1];
        $movimientos[] = $mov;

        return json_encode($movimientos);
    }

    public function respuestaFactura(Request $request, Factura $factura)
    {
        $factura->decision = $request->input('decision');

        return view('notificaciones.respuestaFactura', compact('factura'));
    }

    public function resoluciones()
    {
        $resoluciones = Resolucion::get();
        foreach ($resoluciones as $resolucion) {
            $ultima = Factura::select('id', 'prefijo', 'numero')->where('prefijo', $resolucion->prefijo)->orderBy('numero', 'DESC')->first();
            if($ultima != null){
                $resolucion->actual = $ultima->numero;
            }
        }

        return view('variables.resoluciones', compact('resoluciones'));
    }

    public function vcard()
    {
        $excel = IOFactory::load(storage_path() . "/matriz.xlsx");
        $vcard = "";
        for ($i=1; $i < 4; $i++) { 
            $hoja = $excel->setActiveSheetIndex($i);
            $filas = $hoja->getHighestRow();

            for ($j=2; $j < $filas; $j++) { 
                $vcard = $vcard . "BEGIN:VCARD\nVERSION:2.1\n";
                $placas = $hoja->getCell('B'.$j)->getValue();
                if(strlen($placas) > 15){
                    $placas = substr($placas, 0, 15);
                }
                $vcard = $vcard . "N:" . $placas . ";" . $hoja->getCell('A'.$j)->getValue() . ";;;\n";
                $telefonos = explode("-", $hoja->getCell('D'.$j)->getValue());
                if(count($telefonos) > 1){
                    $vcard = $vcard .  "TEL;CELL:" . $telefonos[0] . "\n";
                    $vcard = $vcard .  "TEL;CELL:" . $telefonos[1] . "\n";
                }else{
                    $vcard = $vcard . "TEL;CELL:" . $telefonos[0] . "\n" ;
                }
                $vcard = $vcard . "END:VCARD\n";
            }
        }

        file_put_contents("contactos.vcf", $vcard);

        return "Listo";
    }
}
