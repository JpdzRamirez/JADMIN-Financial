<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Recibo de caja</title>
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
                <th style="width: 20%" rowspan="2">{{$fecha->format("Y-m-d")}}</th>
                <th style="width: 60%" rowspan="2">
                    CAHORS S.A.S. <br>
                    CARRERA 33 # 49-35 OFICINA 300-7 CABECERA DEL LLANO <br>
                    Tel: 6076339215
                </th>
                <th style="width: 20%">RECIBO CAJA</th>
            </tr>
            <tr>
                <td style="background-color: lightgray; text-align: center">{{$recibo->prefijo}} {{$recibo->numero}}</td>
            </tr>
        </table>
        <hr>
        <div>
            <table style="border: 1px solid black" class="tb-tercero">
                <tr>
                    <th>Tercero:</th>
                    <td>{{$recibo->facturas[0]->tercero->nombre}}</td>
                    <th>C.C:</th>
                    <td>{{$recibo->facturas[0]->tercero->documento}}</td>
                </tr>
                <tr>
                    <th>Dirección:</th>
                    <td>{{strtoupper($direccion)}}</td>
                    <th>Fecha:</th>
                    <td>{{$recibo->fecha}}</td>
                </tr>
                <tr>
                    <th>Ciudad:</th>
                    <td>{{mb_strtoupper($municipio)}}</td>
                    <th>Teléfono:</th>
                    <td>{{strtoupper($celular)}}</td>
                </tr>
            </table>
        </div>
        <br>
        <div>
            <table class="table tb-border">
                <tr style="background-color: lightgray">
                    <th>Forma de pago</th>
                    <th>Valor</th>
                    <th>Observaciones</th>
                </tr>
                <tr>
                    <td>{{$recibo->formaPago->cuenta->nombre}}</td>
                    <td>{{number_format($recibo->valor,2,",",".")}}</td>
                    <td>{{$recibo->observaciones}}</td>
                </tr>
                @if ($recibo->retenciones > 0)
                <tr>
                    <td>RETENCIONES</td>
                    <td>{{number_format($recibo->retenciones,2,",",".")}}</td>
                    <td></td>
                </tr>
                @endif
            </table>
        </div>
        <br>
        <div style="width: 100%">
            <div class="col-md-2">
                Son:
            </div>
            <div class="col-md-10">
                {{$letras}}
            </div>
        </div>
        <div style="width: 100%">
            <div class="col-md-2">
                Observaciones:
            </div>
            <div class="col-md-10">
                {{$recibo->observaciones}}
            </div>
        </div>
        <br>
        <div>
            <table class="tb-border tb-detalle">
                <tr style="background-color: lightgray">
                    <th colspan="4">Detalle de Abonos</th>
                </tr>
                <tr style="background-color: lightgray">
                    <th>Factura</th>
                    <th>Abono</th>
                    <th>Mora</th>
                    <th>Saldo</th>
                </tr>
                @foreach ($recibo->facturas as $factura)
                    <tr>
                        <td>{{$factura->prefijo}} {{$factura->numero}}</td>
                        <td>${{number_format($factura->pivot->abono,2,",",".")}}</td>
                        <td>${{number_format($factura->pivot->mora,2,",",".")}}</td>
                        <td>${{number_format($factura->pivot->saldo,2,",",".")}}</td>
                    </tr>
                @endforeach
                
            </table>
        </div>
    </body>
</html>