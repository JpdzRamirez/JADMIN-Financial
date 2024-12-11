<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Factura de venta</title>
    </head>

    <style>

        body{
            font-size: 8pt;
            margin: 0.5cm 1cm 1cm 1cm;          
        }

        @page {
            margin: 0cm 0cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        table{
            width: 100%;             
        }

        #tbtercero th, #tbtercero td{
            text-align: left;
        }

        #tbtercero{
            border-collapse: collapse;
        }

        #tbtercero .ajustartd th,  #tbtercero .ajustartd td{
            width: 25%;
        }

        #tbproductos, #tbproductos th, #tbproductos td{
            border: 1px solid gray;
            border-collapse: collapse;
        }

        .tdtotales{
            background-color: ghostwhite;
            height: 5%; 
            border-radius: 5px;
            text-align: right;
        }

    </style>
    <body>
        <table>
            <tr>
                <td style="width: 30%">
                    <table>
                        <tr><td style="text-align: center"><img height="100px" src="{{public_path()}}/img/logo_cahors.png" alt="Logo"></td></tr>
                        <tr><td style="font-size: 6pt">ACTIVIDAD ICA BUCARAMANGA Act 321 9*1000</td></tr>
                    </table>  
                </td>
                <td style="width: 35%"><table>
                    <tr><td><b style="font-size: 10pt">CAHORS S.A.S </b> </td></tr>
                    <tr><td>NIT 901318591-6</td></tr>
                    <tr><td>Persona Jurídica</td></tr>
                    <tr><td>Responsable de IVA</td> </tr>
                    <tr><td>REGIMEN COMÚN</td></tr>
                    <tr><td>Carrera 33 # 49-35 Oficina 300 Centro Comercial Cabecera II</td></tr>
                    <tr><td>Bucaramanga</td></tr>
                    <tr><td>607 633 92 15</td></tr>
                    <tr><td>gestion@cahors.co</td></tr>
                    </table>
                </td>
                <td style="width: 35%">
                    <table>
                        <tr>
                            <tr><th colspan="2" style="font-size: 10pt">FACTURA ELECTRONICA DE VENTA</th></tr>
                            <tr><td colspan="2" style="height: 5px"></td></tr>
                            <tr><th colspan="2" style="font-size: 10pt;background-color: lightgray; border-radius: 5px; padding: 5px">{{$factura->prefijo}}{{$factura->numero}}</th></tr>
                            <tr><td colspan="2" style="height: 5px"></td></tr>
                            <tr><th>No. Autorización</th><td>{{$resolucion->autorizacion}}</td></tr>
                            <tr><th>Rango autorizado</th> <td>Desde {{$resolucion->prefijo}} {{$resolucion->numero}} hasta {{$resolucion->prefijo}} {{$resolucion->numero}}</td></tr>
                            <tr><th>Expedida</th> <td>{{$resolucion->fechain}}</td></tr>
                            <tr><th>Vigencia</th> <td>12 meses</td></tr>
                            <tr><th>Vencimiento</th> <td>{{$resolucion->fechafi}}</td></tr>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <hr>
        <div style="background-color: gainsboro; border-radius: 10px; padding: 10px">
            <table id="tbtercero">
                <tr style="background-color: ghostwhite; font-size: 10pt">
                    <th style="border-radius: 5px">Señor(es):</th>
                    <td colspan="3" style="border-radius: 5px">{{$factura->tercero->nombre}}</td>
                </tr>
                <tr><td style="height: 5px"></td></tr>
                <tr class="ajustartd">
                    <th>NIT o CC:</th>
                    <td>{{$factura->tercero->documento}}</td>
                    <th>Dirección:</th>
                    <td>@if ($factura->tercero->usuario != null)
                            {{$factura->tercero->usuario->direccion}}
                        @else
                            {{$factura->tercero->empresa->direccion}}
                        @endif
                    </td>
                </tr>
                <tr class="ajustartd">
                    <th>Teléfono:</th>
                    <td>
                        @if ($factura->tercero->usuario != null)
                            {{$factura->tercero->usuario->celular}}
                        @else
                            {{$factura->tercero->empresa->telefono}}
                        @endif
                    </td>
                    <th>Ciudad:</th>
                    <td>@if ($factura->tercero->usuario != null)
                            {{$factura->tercero->usuario->municipio}}
                        @else
                            {{$factura->tercero->empresa->municipio}}
                        @endif
                    </td>
                </tr>
                <tr class="ajustartd">
                    <th>Correo:</th>
                    <td>
                        @if ($factura->tercero->usuario != null)
                            {{$factura->tercero->usuario->email}}
                        @else
                            {{$factura->tercero->empresa->email}}
                        @endif
                    </td> 
                </tr>
            </table>
        </div>
        <br>
        <div style="background-color: gainsboro; border-radius: 10px; padding: 10px">
            <table>
                <tr>
                    <th>Fecha y Hora de Generación</th>
                    <th>Fecha y Hora de Expedición</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Forma de Pago</th>
                    <th>Medio de Pago</th>
                </tr>
                <tr style="text-align: center; background-color: ghostwhite">
                    <td>{{$hoy->format("Y-m-d H:i:s")}}</td>
                    <td>{{$hoy->format("Y-m-d H:i:s")}}</td>
                    <td>{{$vencimientoMes->format("Y-m-d")}}</td>
                    <td>{{$factura->formapago}}</td>
                    <td>No definido</td>
                </tr>
            </table>
        </div>
        <br>
        <table id="tbproductos">
            @php
                $subtotal = 0;
                $iva = 0;
                $otros = 0;
            @endphp
            <tr style="background-color: gainsboro">
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Valor Unitario</th>
                <th>Valor Total</th>
            </tr>
            @if ($factura->credito != null)
                @php
                    $subtotal = $subtotal + $factura->credito->monto;
                @endphp
                <tr>
                    <td>Préstamo a corto plazo</td>
                    <td style="text-align: right">1</td>
                    <td style="text-align: right">{{number_format($factura->credito->monto, 2, ",", ".")}}</td>
                    <td style="text-align: right">{{number_format($factura->credito->monto, 2, ",", ".")}}</td>
                </tr>
                @foreach ($factura->credito->costos as $costo)
                    <tr>
                        <td>{{$costo->descripcion}}</td>
                        <td style="text-align: right">1</td>
                        <td style="text-align: right">{{number_format($costo->pivot->valor, 2, ",", ".")}}</td>
                        <td style="text-align: right">{{number_format($costo->pivot->valor, 2, ",", ".")}}</td>
                    </tr>
                    @php
                        $subtotal = $subtotal + $costo->pivot->valor;
                        if($costo->iva == 1){
                            $iva = $iva + ($costo->pivot->valor * 0.19);
                        }
                    @endphp
                @endforeach
            @else
                @foreach ($factura->productos as $producto)
                    <tr>
                        <td>{{$producto->nombre}}</td>
                        <td style="text-align: right">{{$producto->pivot->cantidad}}</td>
                        <td style="text-align: right">{{number_format($producto->pivot->valor/$producto->pivot->cantidad, 2, ",", ".")}}</td>
                        <td style="text-align: right">{{number_format($producto->pivot->valor, 2, ",", ".")}}</td>
                    </tr>
                    @php
                        $subtotal = $subtotal + $producto->pivot->valor;
                        $iva = $iva + $producto->pivot->iva;
                    @endphp
                @endforeach
            @endif       
        </table>
        <hr>
        <br>
        <div style="background-color: gainsboro; border-radius: 10px; padding: 10px">
            <table>
                <tr>
                    <td style="text-align: center; width: 20%">
                        <img src="data:image/png;base64, {{$imgqr}}" alt="qrcode" style="height: 100px">
                    </td>
                    <td style="width: 40%">
                        <table>
                            <tr>
                                <th style="width: 20%">Valor en Letras</th>
                                <td style="background-color: ghostwhite; height: 15%; width: 80%; border-radius: 5px; vertical-align: top">{{$letras}}</td>
                            </tr>
                            <tr>
                                <th style="width: 20%">Observaciones</th>
                                <td style="background-color: ghostwhite; height: 15%; width: 80%; border-radius: 5px; vertical-align: top">{{$factura->descripcion}}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 40%">
                        <table>
                            <tr>
                                <th>Subtotal</th>
                                <td class="tdtotales">${{number_format($subtotal, 2, ",", ".")}}</td>
                            </tr>
                            <tr>
                                <th>Descuento</th>
                                <td class="tdtotales">$0.00</td>
                            </tr>
                            <tr>
                                <th>IVA</th>
                                <td class="tdtotales">${{number_format($iva, 2, ",", ".")}}</td>
                            </tr>
                            <tr>
                                <th>Otros impuestos</th>
                                <td class="tdtotales">${{number_format($otros, 2, ",", ".")}}</td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td class="tdtotales">${{number_format($subtotal+$iva+$otros, 2, ",", ".")}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td colspan="2" rowspan="2" style="word-wrap: break-word; vertical-align: bottom">cufe: {{$cufe}}</td>
                    <td style="text-align: center">Firma y sello del adquiriente</td>
                </tr>
                <tr>
                    <td style="height: 50px; background-color: ghostwhite"></td>
                </tr>
            </table>
        </div>    
    </body>
</html>