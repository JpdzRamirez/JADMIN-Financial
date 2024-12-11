<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Libro Auxiliar</title>
    </head>

    <style>
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
            font-size: 7pt;
            margin: 0.5cm 1cm 1cm 1cm;          
        }

        @page {
            margin: 0cm 0cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        table{
            width: 100%;             
        }

        .tb-border, .tb-border th, .tb-border td{
            border: 1px solid black;
            border-collapse: collapse;
        }

    </style>
    <body>
        <div class="centrar">
            <h3>Libro Auxiliar</h3>
            <p>Desde {{$cuentain}} hasta {{$cuentafi}} <br>
                Desde {{$fechain}} hasta {{$fechafi}} 
                @if ($tercero != null)
                    <br>Tercero : {{$tercero->documento}}, {{$tercero->nombre}}
                @endif
            </p>
        </div> 
        @foreach ($cuentas as $cuenta)
        @php
            if($tercero != null){
                $parcialDebito = \App\Models\Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Débito')->whereDate('fecha', '<', $fechain)->where('terceros_id', $tercero->id)->where('estado', 1)->sum('valor');
                $parcialCredito = App\Models\Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Crédito')->whereDate('fecha', '<', $fechain)->where('terceros_id', $tercero->id)->where('estado', 1)->sum('valor');
            }else{
                $parcialDebito = \App\Models\Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Débito')->whereDate('fecha', '<', $fechain)->where('estado', 1)->sum('valor');
                $parcialCredito = App\Models\Movimiento::where('cuentas_id', $cuenta->id)->where('naturaleza', 'Crédito')->whereDate('fecha', '<', $fechain)->where('estado', 1)->sum('valor');
            }
            $saldo = $parcialDebito - $parcialCredito;
        @endphp
        @if ($saldo != 0 || count($cuenta->movimientos) > 0)
            <div class="centrar">
                <h5>{{$cuenta->codigo}}-{{$cuenta->nombre}}</h5>   
            </div>
            <table class="table tb-border">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Observación</th>        
                        <th>Tercero</th> 
                        <th>Débito</th>
                        <th>Crédito</th>
                        <th>Saldo</th>
                    </tr>    
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Parciales</th>
                        <th>{{number_format($parcialDebito, 2, ",", ".")}}</th>
                        <th>{{number_format($parcialCredito, 2, ",", ".")}}</th>
                        <th>{{number_format($saldo, 2, ",", ".")}}</th>
                    </tr>
                    @php
                        $debitos = 0;
                        $creditos = 0;
                        $saldo = $saldo;
                    @endphp
                    @if ($cuenta != null) 
                        @forelse ($cuenta->movimientos as $movimiento)
                            <tr>
                                <td>{{$movimiento->fecha}}</td>
                                <td>{{$movimiento->concepto}}</td>
                                <td>
                                    {{$movimiento->tercero->documento}} - {{$movimiento->tercero->nombre}}
                                </td>
                                @if ($movimiento->naturaleza == "Débito")
                                    <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>
                                    <td>0</td>
                                    <td>{{number_format($saldo + $movimiento->valor, 2, ",", ".")}}</td>
                                    @php
                                        $debitos = $debitos + $movimiento->valor;
                                        $saldo = $saldo + $movimiento->valor;
                                    @endphp
                                @else
                                    <td>0</td>
                                    <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td> 
                                    <td>{{number_format($saldo - $movimiento->valor, 2, ",", ".")}}</td>
                                    @php
                                        $creditos = $creditos + $movimiento->valor;
                                        $saldo = $saldo - $movimiento->valor;
                                    @endphp
                                @endif  
                            </tr>  
                        @empty
                            <tr class="centrar">
                                <td colspan="6">No hay datos</td>
                            </tr>
                        @endforelse
                    @else
                        <tr class="centrar">
                            <td colspan="6">No hay datos</td>
                        </tr>
                    @endif
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Parciales</th>
                        <th>{{number_format($debitos, 2, ",", ".")}}</th>
                        <th>{{number_format($creditos, 2, ",", ".")}}</th>
                        <th>{{number_format($saldo, 2, ",", ".")}}</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <th>Totales</th>
                        <th>{{number_format($debitos+$parcialDebito, 2, ",", ".")}}</th>
                        <th>{{number_format($creditos+$parcialCredito, 2, ",", ".")}}</th>
                        <th>{{number_format($saldo, 2, ",", ".")}}</th>
                    </tr>
                </tbody>              
            </table>
        @endif        
        @endforeach 
    </body>
</html>