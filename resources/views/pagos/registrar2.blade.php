@extends('layouts.logeado')

@section('style')
    <style>
        #cuotas label{
            margin-top: 1rem;
        }
    </style>
@endsection
@section('sub_title', 'Registrar Pago')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if (session('cliente'))
                <div class="alert alert-danger">
                    {{ session('cliente') }}
                </div>
			@endif
            <p style="text-align: center; color: navy; font-size: medium">Digitar cédula del deudor ó placa del vehículo</p>
            <form action="/pagos/registrar" method="get">
                <div class="row form-group">
                    <label for="identificacion" class="col-md-2 text-center" style="font-size: large">Identificación:</label>
                    <div class="col-md-4">
                        <input type="number" name="identificacion" id="identificacion" class="form-control">
                    </div>

                    <label for="placa" class="col-md-2 text-center" style="font-size: large">Placa:</label>
                    <div class="col-md-4">
                        <input type="text" name="placa" id="placa" class="form-control">
                    </div>
                </div>
                <br>
                <div class="text-center">
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search" aria-hidden="true"></i> Consultar</button>
                </div>
            </form>

            @if (isset($busq))
                <br>
                
                <div id="cuotas" style="margin-bottom: 50px">           
                    <h4 style="color: navy">Cliente</h4>
                    <hr>
                    <h5 class="form-control col-md-6 col-xs-12">{{ ucfirst($cliente->primer_nombre) }} {{ ucfirst($cliente->segundo_nombre) }} {{ ucfirst($cliente->primer_apellido) }} {{ ucfirst($cliente->segundo_apellido) }}</h5>
                    <h5 class="form-control col-md-6 col-xs-12">{{ number_format($cliente->nro_identificacion, 0, ",", ".") }} </h5>
                    <h6 class="form-control col-md-6 col-xs-12">{{ $cliente->condicion }}</h6>
                    
                    <br>
                    <h4 style="color: navy">Créditos vigentes</h4>
                    <hr>

                    @foreach ($creditos as $credito)
                        <form action="/pagos/registrar_cuotas" method="get" id="{{$credito->id}}">
                            <input type="hidden" name="credito" value="{{$credito->id}}">
                            <input type="hidden" name="cliente" value="{{$cliente->id}}">
                        </form>
                        <div class="row form-group">
                            <label class="col-md-1">ID Crédito</label>
                            <h5 class="col-md-2 form-control">{{ $credito->id }}</h5>
                            <label class="col-md-1 text-center">Destino</label>
                            <h5 class="col-md-4 form-control">{{ $credito->tipo }}</h5>
                            <label class="col-md-2 text-center">Desembolso</label>
                            <h5 class="col-md-2 form-control">{{ $credito->fecha_prestamo }}</h5>
                        </div>
                        <table class="table table-bordered" style="table-layout: fixed">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th># Cuota</th>
                                    <th>Valor</th>
                                    <th>Fecha vencimiento</th>
                                    <th>Días Mora</th>
                                    <th>Interés Mora</th>
                                    <th>Total cuota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($credito->cuotas as $cuota)
                                    @if ($cuota->estado == "Pagada")
                                        <tr style="background-color: mediumseagreen">
                                    @elseif($cuota->estado == "Vigente")
                                        <tr style="background-color: lightblue">
                                    @elseif($cuota->estado == "Vencida")
                                        <tr style="background-color: lightcoral">
                                    @else          
                                        <tr>       
                                    @endif
                                        @if ($cuota->descripcion == null)
                                            <td><input type="checkbox" class="form-control" name="idcuotas[]" form="{{$credito->id}}" value="{{$cuota->id}}"></td>
                                            <td>{{ $cuota->ncuota }}</td>
                                        @else
                                            <td></td>
                                            <td>{{ $cuota->descripcion }}</td>
                                        @endif
                                        <td>${{ number_format($cuota->saldo_capital + $cuota->saldo_interes, 2, ",", ".") }}</td>
                                        <td>{{ $cuota->fecha_vencimiento }}</td>
                                        <td>{{ $cuota->mora }}</td>
                                        <td>${{ number_format($cuota->saldo_mora, 2, ",", ".") }}</td>
                                        <td>${{ number_format($cuota->saldo_capital + $cuota->saldo_interes+$cuota->saldo_mora, 2, ",", ".") }}</td>
                                        <!--<td style="background-color: white">
                                            @if ($cuota->estado == "Vencida" || $cuota->estado == "Vigente")
                                                <a href="/pagos/pagar_cuota/{{ $cuota->id }}" class="btn btn-sm btn-success">Pagar cuota</a>
                                            @endif
                                        </td>-->
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="7"></td>
                                    <td> <a href="/pagos/pagar_credito/{{$credito->id}}" class="btn btn-sm btn-primary">Pagar Crédito</a></td>
                                </tr>
                            </tbody>
                        </table> 
                        <div class="text-center">
                            <button type="submit" form="{{$credito->id}}" class="btn btn-dark">Pagar Cuotas</button>
                        </div>                      
                    @endforeach

                    <br>
                    <h4 style="color: navy">Facturas vigentes</h4>
                    <hr>

                    <table class="table table-bordered" style="table-layout: fixed">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facturas as $factura)
                            <tr>
                                <td>{{$factura->prefijo}} {{$factura->numero}}</td>
                                <td>{{$factura->fecha}}</td>
                                <td>{{$factura->descripcion}}</td>
                                <td>
                                    <a href="/contabilidad/facturas/ventas/{{$factura->id}}/cobrar" target="_blank" class="btn btn-success btn-sm">Cobrar</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
            @endif          
		</div>
	</div>
@endsection
@section('script')
    <script>
        @if(isset($busq))
            $(document).ready(function () {
                $("#load").hide();
                $("html,body").animate({scrollTop: $("#cuotas").offset().top}, 1500);	
            });
        @endif
    </script>
@endsection