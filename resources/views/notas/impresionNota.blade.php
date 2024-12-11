<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Nota {{mb_strtoupper($nota->tipo)}}</title>
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

        .centrar{
            text-align: center;
        }

        body{
            font-size: 10pt;
            margin: 0.5cm 1cm 1cm 1cm;          
        }

        @page {
            margin: 0cm 0cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        table{
            width: 100%;             
        }

        .tb-tercero th{
            text-align: left;
        }

        .tb-border, .tb-border th, .tb-border td{
            border: 1px solid black;
            border-collapse: collapse;
        }

        .tb-detalle td{
            text-align: center;
        }

    </style>
    <body>
        <hr>
        <table>
            <tr>
                <th style="width: 20%" rowspan="2">{{$fecha->format("Y/m/d")}}</th>
                <th style="width: 60%" rowspan="2">
                    CAHOR S.A.S. <br>
                    Nit: 901318591-6 <br>
                    CARRERA 33 # 49-35 OFICINA 300-7 CABECERA DEL LLANO Tel:6076339215
                </th>
                <th style="width: 20%">NOTA DE {{mb_strtoupper($nota->tipo)}}</th>
            </tr>
            <tr>
                <td style="background-color: lightgray; text-align: center">{{$nota->prefijo}} {{$nota->numero}}</td>
            </tr>
        </table>
        <hr>
        <div>
            <table style="border: 1px solid black" class="tb-tercero">
                <tr>
                    <th>Fecha:</th>
                    <td>{{$nota->fecha}}</td>
                </tr>
                <tr>
                    <th>Tercero:</th>
                    <td>{{$nota->factura->tercero->nombre}}</td>
                </tr>
                <tr>
                    <th>Concepto:</th>
                    <td>{{$nota->concepto}}</td>
                </tr>
            </table>
        </div>
        <br>
        <div>
            <table class="table tb-border" style="font-size: 8pt">
                <tr style="background-color: lightgray">
                    <th>Código</th>
                    <th>Cuenta</th>
                    <th>Tercero</th>
                    <th>Débito</th>
                    <th>Crédito</th>
                </tr>
                @php
                    $suma = 0;
                @endphp
                @foreach ($nota->movimientos as $movimiento)
                    <tr>
                        <td>{{$movimiento->cuenta->codigo}}</td>
                        <td>{{$movimiento->cuenta->nombre}}</td>
                        <td>{{$movimiento->tercero->documento}}-{{$movimiento->tercero->nombre}}</td>
                        @if ($movimiento->naturaleza == "Débito")
                            <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>
                            <td>0</td>
                            @php
                                $suma = $suma + $movimiento->valor;
                            @endphp
                        @else
                            <td>0</td>
                            <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>
                        @endif
                    </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td></td>
                    <th>Sumas iguales</th>
                    <td>{{number_format($suma, 2, ",", ".")}}</td>
                    <td>{{number_format($suma, 2, ",", ".")}}</td>
                </tr>
            </table>
        </div>
        <br>
        <br>
        <br>
        <br>
        <div>
            <table class="table">
                <tr style="vertical-align: bottom; text-align: center">
                    <td>___________________</td>
                    <td>___________________</td>
                    <td>___________________</td>
                    <td>___________________</td>
                </tr>
                <tr style="vertical-align: top; text-align: center">
                    <td>Elaborado por</td>
                    <td>Revisado por</td>
                    <td>Autorizado por</td>
                    <td>Firma y sello</td>
                </tr>
            </table>
        </div>
        
    </body>
</html>