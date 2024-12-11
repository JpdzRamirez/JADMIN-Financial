@extends('layouts.logeado')

@section('style')
    <style>
        #cuotas label{
            margin-top: 1rem;
        }
    </style>
@endsection
@section('sub_title', 'Facturas de Compra: Pagar')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if (session('cliente'))
                <div class="alert alert-danger">
                    {{ session('cliente') }}
                </div>
			@endif
            <p style="text-align: center; color: navy; font-size: medium">Digitar cédula del deudor</p>
            <form action="/facturas/compra/consultar_cobros" method="get">
                <div class="row form-group">
                    <label for="identificacion" class="col-md-2 text-center" style="font-size: large">Identificación:</label>
                    <div class="col-md-4">
                        <input type="number" name="identificacion" id="identificacion" class="form-control">
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
                    <br>
                    <h4 style="color: navy">Facturas vigentes</h4>
                    <hr>

                    @foreach ($facturas as $factura)
                        <form action="/facturas/compra/registrar_cuotas" method="get" id="{{$factura->id}}">
                            <input type="hidden" name="factura" value="{{$factura->id}}">
                            <input type="hidden" name="cliente" value="{{$cliente->id}}">
                        </form>
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
                                @foreach ($factura->cuotas as $cuota)
                                   <tr>
                                    @if ($cuota->descripcion == null)
                                        <td><input type="checkbox" class="form-control" name="idcuotas[]" form="{{$factura->id}}" value="{{$cuota->id}}"></td>
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
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="7"></td>
                                    <td> <a href="/pagos/pagar_factura/{{$factura->id}}" class="btn btn-sm btn-primary">Pagar Crédito</a></td>
                                </tr>
                            </tbody>
                        </table> 
                        <div class="text-center">
                            <button type="submit" form="{{$factura->id}}" class="btn btn-dark">Pagar Cuotas</button>
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