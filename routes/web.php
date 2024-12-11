<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CarteraController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\ContabilidadController;
use App\Http\Controllers\CostoController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\ExportarController;
use App\Http\Controllers\FiltroController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\TasaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VariableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LoginController::class, 'inicio'])->name('inicio');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('autologin_conductor/{conductor}', [UserController::class, 'loginConductor']);
Route::get('/vcard', [VariableController::class, 'vcard']);

Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');

    return "Cleared!";
});

Route::get('sistema_operativo', function (Request $request) {
    $agent = $request->header('User-Agent');
    $plataformas = array(
        'Windows 10' => 'Windows NT 10.0+',
        'Windows 8.1' => 'Windows NT 6.3+',
        'Windows 8' => 'Windows NT 6.2+',
        'Windows 7' => 'Windows NT 6.1+',
        'Windows Vista' => 'Windows NT 6.0+',
        'Windows XP' => 'Windows NT 5.1+',
        'iPhone' => 'iPhone',
        'iPad' => 'iPad',
        'Mac OS X' => '(Mac OS X+)|(CFNetwork+)',
        'Mac otros' => 'Macintosh',
        'Android' => 'Android',
        'Linux' => 'Linux',
    );
    foreach ($plataformas as $plataforma => $pattern) {
        if (preg_match('/(?i)' . $pattern . '/', $agent))
            return $plataforma;
    }
});

Auth::routes();
Route::get('/register/codigo_verificacion', [RegisterController::class, 'codigoVerificacion']);
Route::post('/register/codigo_verificacion', [RegisterController::class, 'confirmarRegistro']);

Route::get('/facturas/{factura}/procesar', [VariableController::class, 'respuestaFactura']);

//Aplicacion
Route::post('/integracion/solicitar_credito', [CreditoController::class, 'solicitarCreditoApp']);
Route::post('/integracion/simular_credito', [CreditoController::class, 'simularCreditoApp']);

Route::middleware(['auth', 'administrador'])->group(function () {

    Route::get('home', [HomeController::class, 'index'])->name('home');

    //Usuarios
    Route::get('users', [UserController::class, 'listaUsuarios']);
    Route::get('users/nuevo/', [UserController::class, 'nuevoUsuario']);
    Route::post('users/registrar', [UserController::class, 'registrarUsuario']); 
    Route::get('users/{user}/editar/', [UserController::class, 'editarUsuarioVista']);
    Route::post('users/edicion', [UserController::class, 'editarUsuario']);
    Route::get('users/actualizar/{user}', [UserController::class, 'editcuenta'])->name('users.editcuenta');
    Route::put('users/actualizar/{user}', [UserController::class, 'updatecuenta'])->name('users.updatecuenta');

    //Seguros
    Route::get('seguros', [CarteraController::class, 'listarSeguros']);
    Route::get('seguros/{seguro}/inactivar', [CarteraController::class, 'inactivarSeguro']);
    Route::post('seguros/registrar', [CarteraController::class, 'registrarSeguro']);
    Route::get('seguros/renovar', [CarteraController::class, 'renovarSeguros']);
    Route::post('seguros/facturar', [CarteraController::class, 'facturarSeguros']);
    Route::post('seguros/editar', [CarteraController::class, 'editarSeguro']);
    Route::get('seguros/prefactura', [CarteraController::class, 'prefacturaVista']);
    Route::post('seguros/prefactura', [CarteraController::class, 'descargarPrefactura']);

    //Costos
    Route::get('costos', [CostoController::class, 'lista']);
    Route::post('costos/nuevo', [CostoController::class, 'store']);
    Route::post('costos/editar', [CostoController::class, 'editar']);

    //Clientes
    Route::get('clientes', [UserController::class, 'listarClientes']);
    Route::get('clientes/nuevo', [UserController::class, 'nuevoCliente']);
    Route::post('clientes/nuevo', [UserController::class, 'registrarCliente'])->name('clientes.registrar');
    Route::get('clientes/{user}/editar', [UserController::class, 'editarCliente']);
    Route::put('clientes/editar', [UserController::class, 'actualizarCliente'])->name('clientes.actualizar');
    Route::get('clientes/buscar', [UserController::class, 'buscarClientes']);
    Route::get('clientes/{cliente}/creditos', [UserController::class, 'creditosCliente']);

    //Solicitudes
    Route::get('solicitudes_credito', [CreditoController::class, 'listarSolicitudes']);
    Route::get('solicitudes/{solicitud}/evaluar', [CreditoController::class, 'evaluarCredito']);
    Route::post('solicitudes/{solicitud}/evaluacion', [CreditoController::class, 'evaluacionCredito']);
    Route::get('solicitudes/{credito}/colocar', [CreditoController::class, 'colocarCredito']);
    Route::get('solicitudes/{credito}/descargar_formulario', [CreditoController::class, 'bajarFormulario']);
    Route::post('solicitudes/{credito}/colocar', [CreditoController::class, 'colocacionCredito']);
    Route::get('solicitudes/nuevas', [CreditoController::class, 'getSolicitudes']);

    //Creditos
    Route::get('creditos_finalizados', [CreditoController::class, 'listarFinalizados']);
    Route::get('creditos_cobro', [CreditoController::class, 'listarCobro']);
    Route::get('creditos_aprobados', [CreditoController::class, 'listarAprobados']);
    Route::get('creditos/nuevo', [CreditoController::class, 'nuevoCredito']);
    Route::post('creditos/simulador', [CreditoController::class, 'simularCredito']);
    Route::post('creditos/solicitar', [CreditoController::class, 'solicitarCredito']);
    Route::get('creditos/{credito}/descargar_factura', [CreditoController::class, 'descargarFactura']);
    Route::get('importar_creditos', [TasaController::class, 'importarCreditos']);
    Route::get('importar_cuentas', [TasaController::class, 'importarCuentas']);
    Route::get('creditos/obtener_tipos', [TasaController::class, 'tiposCredito']);

    //Pagos
    Route::get('pagos/historial', [PagoController::class, 'pagosCliente']);
    Route::get('pagos/realizados', [PagoController::class, 'pagosRealizados']);
    Route::get('pagos/registrar', [PagoController::class, 'registrarPagoView']);
    Route::get('pagos/credito/{credito}', [PagoController::class, 'pagosPorCredito']);
    Route::get('pagos/pagar_cuota/{cuota}', [PagoController::class, 'pagarCuotaview']);
    Route::post('pagos/pagar_cuota', [PagoController::class, 'pagarCuota']);
    Route::get('pagos/pagar_credito/{credito}', [PagoController::class, 'pagarCreditoView']);
    Route::get('pagos/{pago}/descargar_recibo', [PagoController::class, 'descargarRecibo']);
    Route::post('pagos/pagar_credito', [PagoController::class, 'pagarCredito']);
    Route::get('pagos/registrar_cuotas', [PagoController::class, 'pagarCuotasView']);
    Route::post('pagos/registrar_cuotas', [PagoController::class, 'pagarCuotas']);
    Route::get('pagos/registrar_facturas', [PagoController::class, 'pagarFacturasView']);
    Route::post('pagos/registrar_facturas', [PagoController::class, 'pagarFacturas']);

    //Tasas
    Route::get('tasas_interes', [TasaController::class, 'listarInteres']);
    Route::post('tasas_interes/nuevo', [TasaController::class, 'addTasaInteres']);
    Route::get('tasas_mora', [TasaController::class, 'listarMora']);
    Route::post('tasas_mora/nuevo', [TasaController::class, 'addTasaMora']);
    Route::post('importar_notas', [TasaController::class, 'importarNotas']);
    Route::get('importar_compras', [TasaController::class, 'importarCompras']);
    Route::get('firmar_factura', [TasaController::class, 'firmarXml']);
    Route::get('encontrar_descuadres', [TasaController::class, 'encontrarDescuadres']);

    //Filtros
    Route::get('creditos_cobro/filtrar', [FiltroController::class, 'filtrarCreditosCobro']);
    Route::get('creditos_aprobados/filtrar', [FiltroController::class, 'filtrarCreditosAprobados']);
    Route::get('creditos_finalizados/filtrar', [FiltroController::class, 'filtrarCreditosFinalizados']);
    Route::get('solicitudes_credito/filtrar', [FiltroController::class, 'filtrarSolicitudes']);
    Route::get('clientes/filtrar', [FiltroController::class, 'filtrarClientes']);
    Route::get('cartera/filtrar', [FiltroController::class, 'filtrarCartera']);
    Route::get('contabilidad/notas_credito/filtrar', [FiltroController::class, 'filtrarNotasCredito']);
    Route::get('contabilidad/notas_debito/filtrar', [FiltroController::class, 'filtrarNotasDebito']);
    Route::get('contabilidad/facturas/compras/filtrar', [FiltroController::class, 'filtrarFacturasCompra']);
    Route::get('contabilidad/facturas/ventas/filtrar', [FiltroController::class, 'filtrarFacturasVenta']);
    Route::get('contabilidad/notas_contables/filtrar', [FiltroController::class, 'filtrarNotasContables']);
    Route::get('contabilidad/recibos/filtrar', [FiltroController::class, 'filtrarRecibos']);
    Route::get('contabilidad/ingresos/filtrar', [FiltroController::class, 'filtrarIngresos']);
    Route::get('contabilidad/egresos/filtrar', [FiltroController::class, 'filtrarEgresos']);
    Route::get('contabilidad/terceros/filtrar', [FiltroController::class, 'filtrarTerceros']);
    Route::get('pagos/filtrar', [FiltroController::class, 'filtrarpagos']);

    //Exportar
    Route::get('creditos_cobro/exportar', [ExportarController::class, 'creditosEnCobro']);
    Route::get('facturas/ventas/exportar', [ExportarController::class, 'facturasVenta']);
    Route::get('clientes/exportar', [ExportarController::class, 'clientes']);

    //Vehiculos
    Route::get('vehiculos/{cliente}', [TasaController::class, 'placasPorCliente']);

    //Cartera
    Route::get('cartera', [CarteraController::class, 'listarCartera']);
    Route::get('/contabilidad/vencidas/{cuota}/mora', [CarteraController::class, 'cambiarMora']);
    Route::post('/contabilidad/vencidas/editar_mora', [CarteraController::class, 'editarMora']);
    Route::post('/contabilidad/vencidas/editar_moraTotal', [CarteraController::class, 'editarTotalMora']);
    Route::get('cartera/descargar', [CarteraController::class, 'descargarCartera']);

    //Contabilidad
    Route::get('contabilidad/get_nodos/{nodo}', [ContabilidadController::class, 'getNodos']);
    Route::get('contabilidad/info_nodo/{nodo}', [ContabilidadController::class, 'infoNodo']);
    Route::get('contabilidad/plan_cuentas', [ContabilidadController::class, 'listarCuentas']);
    Route::post('contabilidad/plan_cuentas/modificar', [ContabilidadController::class, 'modificarCuentas']);
    Route::get('contabilidad/cuentas/buscar', [ContabilidadController::class, 'buscarCuenta']);
    Route::get('contabilidad/informes/libro_auxiliar', [InformeController::class, 'formLibroAuxiliar']);
    Route::get('contabilidad/informes/generar_libro', [InformeController::class, 'getLibroAuxiliar']);
    Route::get('contabilidad/informes/descargar_libro', [InformeController::class, 'descargarLibroAuxiliar']);
    Route::get('contabilidad/informes/cartera_generica', [InformeController::class, 'formCarteraGenerica']);
    Route::get('contabilidad/informes/descargar_cartera', [InformeController::class, 'descargarCarteraGenerica']);
    Route::get('contabilidad/informes/balance', [InformeController::class, 'formBalance']);
    Route::get('contabilidad/informes/descargar_balance', [InformeController::class, 'descargarBalance']);
    Route::get('contabilidad/informes/saldos_cuenta', [InformeController::class, 'formSaldoPorCuenta']);
    Route::get('contabilidad/informes/mostrar_saldos', [InformeController::class, 'saldosPorCuenta']);
    Route::get('contabilidad/informes/creditos_cobro', [InformeController::class, 'formCreditosEnCobro']); //nuevo Carlos
    Route::get('contabilidad/informes/descargar_creditosencobro', [InformeController::class, 'descargarCreditosEnCobro']);

    //Facturas
    Route::get('contabilidad/facturas/compras', [ContabilidadController::class, 'facturasCompra']);
    Route::get('contabilidad/facturas/compras/nueva', [ContabilidadController::class, 'nuevaCompra']);
    Route::get('contabilidad/facturas/detalles/{factura}', [ContabilidadController::class, 'detallesFactura']);
    Route::post('contabilidad/facturas/compras/enviar', [ContabilidadController::class, 'guardarCompra']);
    Route::get('contabilidad/facturas/compras/calculos', [ContabilidadController::class, 'getCalculosCompra']);
    Route::get('contabilidad/facturas/compras/get', [ContabilidadController::class, 'getFacturaCompra']);
    Route::get('contabilidad/facturas/compras/{factura}/imprimir', [ContabilidadController::class, 'descargarCompra']);
    Route::get('contabilidad/facturas/compras/{factura}/pagar', [ContabilidadController::class, 'pagarCompra']);
    Route::post('contabilidad/facturas/compras/asiento', [ContabilidadController::class, 'asientoPagoCompra']);
    Route::post('contabilidad/facturas/compras/registrar_pago', [ContabilidadController::class, 'registrarPagoCompra']);
    Route::get('contabilidad/facturas/ventas', [ContabilidadController::class, 'facturasVenta']);
    Route::get('contabilidad/facturas/ventas/nueva', [ContabilidadController::class, 'nuevaVenta']);
    Route::get('contabilidad/facturas/ventas/calculos', [ContabilidadController::class, 'getCalculosVenta']);
    Route::post('contabilidad/facturas/ventas/enviar', [ContabilidadController::class, 'guardarVenta']);
    Route::get('contabilidad/facturas/ventas/{factura}/imprimir    ', [ContabilidadController::class, 'descargarVenta']);
    Route::get('contabilidad/facturas/ventas/enviar_factura', [ContabilidadController::class, 'enviarFactura']);
    Route::get('contabilidad/facturas/ventas/{factura}/cobrar', [ContabilidadController::class, 'cobrarVenta']);
    Route::post('contabilidad/facturas/ventas/asiento', [ContabilidadController::class, 'asientoCobroVenta']);
    Route::post('contabilidad/facturas/ventas/registrar_cobro', [ContabilidadController::class, 'registrarCobroVenta']);
    
    Route::get('contabilidad/facturas/soportes', [SoporteController::class, 'soportes']);
    Route::get('contabilidad/facturas/soportes/nuevo', [SoporteController::class, 'nuevoSoporte']);

    Route::post('contabilidad/facturas/soportes/enviar', [SoporteController::class, 'guardarSoporte']);


    //Terceros
    Route::get('contabilidad/terceros', [UserController::class, 'terceros']);
    Route::get('contabilidad/terceros/nuevo', [UserController::class, 'nuevoTercero']);
    Route::get('contabilidad/terceros/{tercero}/editar', [UserController::class, 'editarTercero']);
    Route::get('contabilidad/terceros/buscar', [UserController::class, 'buscarTercero']);
    Route::post('contabilidad/terceros/registrar', [UserController::class, 'registrarTercero']);
    Route::post('contabilidad/terceros/actualizar', [UserController::class, 'actualizarTercero']);

    //Variables
    Route::get('contabilidad/productos', [VariableController::class, 'productos']);
    Route::get('contabilidad/productos/buscar', [VariableController::class, 'buscarProducto']);
    Route::post('contabilidad/productos/registrar', [VariableController::class, 'registrarProducto']);
    Route::post('contabilidad/productos/editar', [VariableController::class, 'editarProducto']);
    Route::post('contabilidad/extras_contables/calcular', [VariableController::class, 'extraContableAsiento']);

    //Notas
    Route::get('contabilidad/notas_credito', [NotaController::class, 'NotasCredito']);
    Route::get('contabilidad/notas_debito', [NotaController::class, 'NotasDebito']);
    Route::get('contabilidad/notas_credito/{factura}/nueva', [NotaController::class, 'nuevaNotaCredito']);
    Route::get('contabilidad/notas_debito/{factura}/nueva', [NotaController::class, 'nuevaNotaDebito']);
    Route::get('contabilidad/notas/{nota}/detalles', [NotaController::class, 'detallesNota']);
    Route::post('contabilidad/notas_credito/generar', [NotaController::class, 'generarNotaCredito']);
    Route::post('contabilidad/notas_credito/generar_antiguos', [NotaController::class, 'generarNotaCreditoAntigua']);
    Route::post('contabilidad/notas_credito/generar_nuevos', [NotaController::class, 'generarNotaCreditoNueva']);
    Route::post('contabilidad/notas_debito/generar', [NotaController::class, 'generarNotaDebito']);
    Route::post('contabilidad/notas_debito/generarDIAN', [NotaController::class, 'generarNotaDebitoDIAN']);
    Route::get('contabilidad/notas/{nota}/descargar', [NotaController::class, 'descargarNota']);
    Route::get('contabilidad/notas_contables', [NotaController::class, 'NotasContables']);
    Route::get('contabilidad/notas_contables/nueva', [NotaController::class, 'nuevaNotaContable']);
    Route::post('contabilidad/notas_contables/enviar', [NotaController::class, 'generarNotaContable']);
    Route::get('contabilidad/notas_contables/{nota}/detalles', [NotaController::class, 'detallesNotaContable']);
    Route::get('contabilidad/notas_contables/{nota}/descargar', [NotaController::class, 'descargarNotaContable']);
    Route::get('contabilidad/notas_contables/{nota}/editar', [NotaController::class, 'editarNotaContable']);
    Route::post('contabilidad/notas_contables/editar', [NotaController::class, 'actualizarNotaContable']);
    Route::post('contabilidad/notas_contables/anular', [NotaController::class, 'anularNotaContable']);

    //Contabilizacion
    Route::get('contabilidad/reteivas', [VariableController::class, 'reteivas']);
    Route::post('contabilidad/reteivas/nuevo', [VariableController::class, 'registrarReteiva']);
    Route::post('contabilidad/reteivas/editar', [VariableController::class, 'editarReteiva']);


    Route::get('contabilidad/retefuentes', [VariableController::class, 'retefuentes']);
    Route::post('contabilidad/retefuentes/nuevo', [VariableController::class, 'registrarRetefuente']);
    Route::post('contabilidad/retefuentes/editar', [VariableController::class, 'editarRetefuente']);


    Route::get('contabilidad/reteicas', [VariableController::class, 'reteicas']);
    Route::post('contabilidad/reteicas/nuevo', [VariableController::class, 'registrarReteica']);
    Route::post('contabilidad/reteicas/editar', [VariableController::class, 'editarReteica']);
    Route::get('contabilidad/ivas', [VariableController::class, 'ivas']);
    Route::get('contabilidad/formas_pago', [VariableController::class, 'formasPago']);
    Route::get('contabilidad/formas_pago/get', [VariableController::class, 'formasPagoGet']);
    Route::post('contabilidad/formas_pago/nuevo', [VariableController::class, 'registrarFormaPago']);
    Route::post('contabilidad/formas_pago/editar', [VariableController::class, 'editarFormaPago']);

    //Recibos
    Route::get('contabilidad/recibos', [ReciboController::class, 'listaRecibos']);
    Route::get('contabilidad/recibos/{recibo}/detalles', [ReciboController::class, 'detallesRecibo']);
    Route::post('contabilidad/recibos/anular', [ReciboController::class, 'anularRecibo']);


    //Comprobantes
    Route::get('contabilidad/egresos', [ComprobanteController::class, 'listaEgresos']);
    Route::get('contabilidad/egresos/nuevo', [ComprobanteController::class, 'nuevoEgreso']);
    Route::post('contabilidad/egresos/enviar', [ComprobanteController::class, 'registrarEgreso']);
    Route::get('contabilidad/comprobantes/{comprobante}/descargar', [ComprobanteController::class, 'descargarComprobante']);
    Route::get('contabilidad/ingresos', [ComprobanteController::class, 'listaIngresos']);
    Route::get('contabilidad/ingresos/nuevo', [ComprobanteController::class, 'nuevoIngreso']);
    Route::post('contabilidad/ingresos/enviar', [ComprobanteController::class, 'registrarIngreso']);
    Route::get('contabilidad/ingresos/{recibo}/imprimir', [ComprobanteController::class, 'imprimirAbono']);
    Route::post('contabilidad/ingresos/anular', [ComprobanteController::class, 'anularIngreso']);
    Route::post('contabilidad/egresos/anular', [ComprobanteController::class, 'anularEgreso']);
    Route::get('contabilidad/comprobantes/{comprobante}/editar', [ComprobanteController::class, 'editarComprobante']);
    Route::post('contabilidad/comprobantes/editar', [ComprobanteController::class, 'actualizarComprobante']);

    //Resoluciones
    Route::get('contabilidad/resoluciones', [VariableController::class, 'resoluciones']);
});

Route::middleware(['auth'])->group(function () {

    Route::get('users/actualizar/{user}', [UserController::class, 'editcuenta'])->name('users.editcuenta');
    Route::put('users/actualizar/{user}', [UserController::class, 'updatecuenta'])->name('users.updatecuenta');

    Route::get('mis_creditos', [CreditoController::class, 'misCreditos']);
    Route::get('mis_creditos/nuevo', [CreditoController::class, 'nuevoCreditoCliente']);
    Route::post('mis_creditos/solicitar', [CreditoController::class, 'solicitarCreditoCliente']);
    Route::get('creditos/{credito}/plan_pagos', [CreditoController::class, 'planPagos']);
    Route::post('creditos/simulador', [CreditoController::class, 'simularCredito']);

    Route::get('vehiculos/{cliente}', [TasaController::class, 'placasPorCliente']);

    Route::get('pagos/historial', [PagoController::class, 'pagosCliente']);
    Route::get('pagos/realizados', [PagoController::class, 'pagosRealizados']);
    Route::get('pagos/credito/{credito}', [PagoController::class, 'pagosPorCredito']);
});
