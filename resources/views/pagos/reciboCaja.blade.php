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
                <td style="background-color: lightgray; text-align: center">{{$pago->recibo->prefijo}} {{$pago->recibo->numero}}</td>
            </tr>
        </table>
        <hr>
        <div>
            <table style="border: 1px solid black" class="tb-tercero">
                <tr>
                    <th>Tercero:</th>
                    <td>{{strtoupper($pago->credito->cliente->primer_nombre)}} {{strtoupper($pago->credito->cliente->primer_apellido)}}</td>
                    <th>C.C:</th>
                    <td>{{strtoupper($pago->credito->cliente->nro_identificacion)}}</td>
                </tr>
                <tr>
                    <th>Dirección:</th>
                    <td>{{strtoupper($pago->credito->cliente->direccion)}}</td>
                    <th>Fecha:</th>
                    <td>{{$pago->recibo->fecha}}</td>
                </tr>
                <tr>
                    <th>Ciudad:</th>
                    <td>{{strtoupper($pago->credito->cliente->municipio)}}</td>
                    <th>Teléfono:</th>
                    <td>{{strtoupper($pago->credito->cliente->celular)}}</td>
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
                    <td>{{$pago->formaPago->cuenta->nombre}}</td>
                    <td>{{number_format($pago->valor,2,".",",")}}</td>
                    <td>{{$pago->observaciones}}
                    </td>
                </tr>
                @if ($pago->descuento > 0)
                <tr>
                    <td>DESCUENTO</td>
                    <td>{{number_format($pago->descuento,2,",",".")}}</td>
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
                {{strtoupper($pago->observaciones)}}
            </div>
        </div>
        <br>
        <div>
            <table class="tb-border tb-detalle">
                <tr style="background-color: lightgray">
                    <th colspan="5">Detalle de Abonos</th>
                </tr>
                <tr style="background-color: lightgray">
                    <th>Factura</th>
                    <th>Vehículo</th>
                    <th>Abono</th>
                    <th>Mora</th>
                    <th>Saldo</th>
                </tr>
                <tr>
                    <td>{{$pago->recibo->facturas[0]->prefijo}} {{$pago->recibo->facturas[0]->numero}}</td>
                    <td>@if ($pago->credito->placas != null)
                            {{$pago->credito->placas}}
                        @endif
                    </td>
                    <td>${{number_format($ultimo->abono,2,",",".")}}</td>
                    <td>${{number_format($ultimo->mora,2,",",".")}}</td>
                    <td>${{number_format($ultimo->saldo,2,",",".")}}</td>
                </tr>
            </table>
        </div>
    </body>
</html>