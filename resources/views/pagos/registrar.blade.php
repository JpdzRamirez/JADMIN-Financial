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
                    @if ($tercero != null)
                        <h5 class="form-control col-md-6 col-xs-12">{{ ucfirst($tercero->nombre) }} </h5>
                        <h5 class="form-control col-md-6 col-xs-12">{{ number_format($tercero->documento, 0, ",", ".") }} </h5>


                        @if ($tercero->usuario != null)
                            @if ($tercero->usuario->proceso == 1)
                                <div class="alert alert-danger">
                                <h4>Este cliente tiene proceso abierto</h4> 
                                </div>
                            @endif
                        @endif
                    @else
                        <h5 class="form-control col-md-6 col-xs-12"></h5>
                        <h5 class="form-control col-md-6 col-xs-12"></h5>
                    @endif
                    
                    <br>
                    <h4 style="color: navy">Créditos vigentes</h4>
                    <hr>

                    @foreach ($creditos as $credito)
                        <form action="/pagos/registrar_cuotas" method="get" id="{{$credito->id}}">
                            <input type="hidden" name="credito" value="{{$credito->id}}">
                            <input type="hidden" name="cliente" value="{{$tercero->id}}">
                        </form>
                        <div class="row form-group">
                            <label class="col-md-1"># Crédito</label>
                            <h5 class="col-md-2 form-control">{{ $credito->numero }}</h5>
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
                    
                    <form action="/pagos/registrar_facturas" method="get" id="formfacturas">
                        @if ($tercero != null)
                            <input type="hidden" name="cliente" value="{{$tercero->id}}">
                        @endif
                    </form>
                    <table class="table table-bordered" style="table-layout: fixed">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Factura</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facturas as $factura)
                            <tr>
                                <td><input type="checkbox" class="form-control" name="idfacturas[]" form="formfacturas" value="{{$factura->id}}"></td>
                                <td>{{$factura->prefijo}} {{$factura->numero}}</td>
                                <td>{{$factura->fecha}}</td>
                                <td>{{$factura->descripcion}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-center">
                        <button type="submit" form="formfacturas" class="btn btn-dark">Pagar Facturas</button>
                    </div>  
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