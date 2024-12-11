<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Factura</title>
    </head>

    <style>

        .bordes{
            outline: 1px solid gray;
            min-height: 16pt;
        }

        .derecha{
            text-align: right;
        }

        .col-md-4{
            display: inline-block;
            width: 33.3333%;
        }

        .col-md-3{
            display: inline-block;
            width:25%;
        }

        .col-md-5{
            display: inline-block;
            width: 41.6666%;
        }

        .col-md-6{
            display: inline-block;
            width: 50%;
        }

        .col-md-8{
            display: inline-block;
            width: 66.6666%;
        }

        .col-md-9{
            display: inline-block;
            width: 74.9999%;
        }

        .col-md-10{
            display: inline-block;
            width: 82.2222%;
        }

        .col-md-2{
            display: inline-block;
            width: 16.6666%;
        }

        .col-md-1{
            display: inline-block;
            width: 8.3333%;
        }

        .caja{
            margin-top: 4pt; 
            margin-left: 2pt;
        }

        br{
           line-height: 60%;
        }

        .centrar{
            text-align: center;
        }

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

        .tb-noborder td{
            border: none;
        }

        .tb-border, .tb-border th, .tb-border td{
            border: 1px solid black;
            border-collapse: collapse;
        }

        #filatotal td{
            height: 20pt;
        }
    </style>
    <body>
        <table class="tb-noborder">
            <tr>
                <td style="width: 15%" rowspan="2"><img height="80px" src="{{public_path()}}/img/logo.png" alt="Logo"></td>
                <td style="text-align: center; width: 65%" rowspan="2"><b style="font-size: 12pt">JADMIN</b><br>
                    Nit:  - 6 IVA RÉGIMEN COMÚN
                    <br><br>
                    DIRECCION <br>
                    Tel. : 57 TEL <br>
                    CIUDAD</td>
                <th style="text-align: center;width: 20%;font-size: 10pt"> Factura de venta</th>
            </tr>
            <tr><td style="background-color: lightgray; text-align: center;font-size: 10pt">{{$factura->prefijo}} {{$factura->numero}}</td></tr>
        </table> 
         
        <br><br>
        <div>
            <div class="col-md-1">
               <b>Fecha</b> 
            </div>
            <div class="col-md-3 bordes">
               <div class="caja"> {{ $factura->fecha }} </div>
            </div>
            <div class="col-md-2 centrar">
                <b>Tipo de pago</b> 
            </div>
            <div class="col-md-2 bordes">
                <div class="caja">{{$factura->formapago}}</div> 
            </div>
        </div>
        <br>
        <div>
            <div class="col-md-1">
                <b>Cliente</b>
            </div>
            <div class="col-md-6 bordes">
                <div class="caja">{{ strtoupper($factura->tercero->nombre) }}</div>
            </div>
            <div class="col-md-2 centrar"> <b>C.C.</b></div>
            <div class="col-md-2 bordes">
                <div class="caja">{{ $factura->tercero->documento }}</div>
            </div>
        </div> 
        <br>
        <div>
            <div class="col-md-1">
                <b>Placa</b>
            </div>
            <div class="col-md-4 bordes">
                <div class="caja">
                    @if ($factura->credito != null)
                        @if ($factura->credito->placas != null)
                            {{ $factura->credito->placas }}
                        @endif
                    @endif
                </div>
            </div>
            <div class="col-md-2 centrar">
                <b>Dirección</b>
            </div>
            <div class="col-md-4 bordes">
                <div class="caja">{{ strtoupper($factura->tercero->direccion) }}, {{ $factura->tercero->municipio }}
                </div>
            </div>
        </div> 
        <br>
        <div>
            <div class="col-md-1">
                <b>Celular</b>
            </div>
            <div class="col-md-4 bordes">
                <div class="caja">{{ strtoupper($factura->tercero->celular) }}</div>
            </div>
            <div class="col-md-2 centrar">
                <b>Email</b>
            </div>
            <div class="col-md-4 bordes">
                <div class="caja">{{ strtoupper($factura->tercero->email) }}</div>
            </div>
        </div>
        <br><br>
        <div>
            @php
                $subtotal = 0;
                $iva = 0;
            @endphp
            <table class="tb-border">
                <thead>
                    <tr style="background-color: lightgray">
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Valor Unitario</th>
                        <th>I.V.A</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($factura->credito != null)
                        <tr>
                            <td>Préstamo {{ $factura->credito->tipo }}</td>
                            <td class="derecha">1</td>
                            <td class="derecha">{{ number_format($factura->credito->monto, 2, ",", ".") }}</td>
                            <td class="derecha">0</td>
                            <td class="derecha">{{ number_format($factura->credito->monto, 2, ",", ".") }}</td>
                            @php
                                $subtotal = $subtotal + $factura->credito->monto
                            @endphp
                        </tr>
                        @foreach ($factura->credito->costos as $costo)
                            <tr>
                                <td>{{ $costo->descripcion }}</td>
                                <td class="derecha">1</td>
                                <td class="derecha">{{ number_format($costo->valor, 2, ",", ".") }}</td>
                                @if ($costo->iva == "1")
                                    <td class="derecha">{{ number_format($costo->valor*0.19, 2, ",", ".") }}</td>
                                    <td class="derecha">{{ number_format($costo->valor*1.19, 2, ",", ".") }}</td>
                                    @php
                                        $iva = $iva + $costo->valor*0.19;
                                    @endphp
                                @else
                                    <td class="derecha">0</td>
                                    <td class="derecha">{{ number_format($costo->valor, 2, ",", ".") }}</td>                   
                                @endif
                                @php
                                    $subtotal = $subtotal + $costo->valor;
                                @endphp
                            </tr>
                        @endforeach
                    @else
                        @foreach ($factura->productos as $producto)
                        <tr>
                            <td>{{ $producto->nombre }}</td>
                            <td class="derecha">{{$producto->pivot->cantidad}}</td>
                            <td class="derecha">{{ number_format($producto->pivot->valor/$producto->pivot->cantidad, 2, ",", ".") }}</td>
                            @if ($producto->pivot->iva > 0)
                                <td class="derecha">{{ number_format($producto->pivot->iva, 2, ",", ".") }}</td>
                                <td class="derecha">{{ number_format($producto->pivot->valor + $producto->pivot->iva, 2, ",", ".") }}</td>
                                @php
                                    $iva = $iva + $producto->pivot->iva;
                                @endphp
                            @else
                                <td class="derecha">0</td>
                                <td class="derecha">{{ number_format($producto->pivot->valor, 2, ",", ".") }}</td>                   
                            @endif
                            @php
                                $subtotal = $subtotal + $producto->pivot->valor;
                            @endphp
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <hr>
        <br>
        <table class="tb-border">
            <tr>
                <td><b>Son</b></td>
                <td colspan="3">{{ $letras }}</td>
            </tr>
            <tr>
                <td><b>Observaciones</b></td>
                <td colspan="3">
                    @if ($factura->credito != null)
                        Financiación crédito {{$factura->credito->tipo}} a {{ $factura->credito->plazo }} meses. 
                        @if ($factura->credito->placas != null)
                            {{ $factura->credito->placas }}
                        @endif 
                    @else
                        {{$factura->descripcion}}
                    @endif  
                </td>
            </tr>
            <tr>
                <td rowspan="2" class="centrar" style="vertical-align: bottom; height: 40pt;">{{ $factura->fecha }}</td>
                <td rowspan="2"></td>
                <td class="derecha">Subtotal</td>
                <td class="derecha">{{ number_format($subtotal,2,",",".") }}</td>
            </tr>
            <tr>
                <td class="derecha">I.V.A</td>
                <td class="derecha">{{ number_format($iva,2,",",".") }}</td>
            </tr>
            <tr id="filatotal">
                <td class="centrar">Firma y Sello Entregada</td>
                <td class="centrar">Firma y Sello Recibido</td>
                <td class="derecha"><b>TOTAL</b></td>
                <td class="derecha">{{ number_format($subtotal+$iva,2,",",".") }}</td>
            </tr>
        </table>
        <br>
        <div style="font-size: 6pt">
            De Conformidad con el Art. 2 de la Ley 1231 de 2008; la factura se considera irrevocablemente aceptada por el comprador, si dentro de los diez (10) días calendario siguientes a su recepción no presenta
            reclamo alguno sobre su contenido.
        </div>
        
    </body>
</html>