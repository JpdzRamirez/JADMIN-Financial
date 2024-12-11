@extends('layouts.logeado')

@section('sub_title', 'Detalles Recibo ' . $recibo->prefijo . ' #' . $recibo->numero)

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row form-group">
                <label for="" class="col-md-3"><h5>Número</h5></label>
                <div class="col-md-9 form-control">{{$recibo->numero}}</div>
            </div>
            <div class="row form-group">
                <label for="" class="col-md-3"><h5>Fecha</h5></label>
                <div class="col-md-9 form-control">{{$recibo->fecha}}</div>
            </div>
            <div class="row form-group">
                <label for="" class="col-md-3"><h5>Facturas</h5></label>
                <div class="col-md-9 form-control">
                    @foreach ($recibo->facturas as $factura)
                        {{$factura->prefijo}} {{$factura->numero}},
                    @endforeach
                </div>
            </div>
            
            <hr>
            <div class="text-center"><h5>Asiento contable</h5></div>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Nombre</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recibo->movimientos as $movimiento)
                        <tr>
                            <td>{{$movimiento->cuenta->codigo}}</td>
                            <td>{{$movimiento->cuenta->nombre}}</td>
                            @if ($movimiento->naturaleza == "Débito")     
                                <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>
                                <td>0</td>
                            @else
                                <td>0</td>
                                <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>  
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
		</div>
	</div>
@endsection
