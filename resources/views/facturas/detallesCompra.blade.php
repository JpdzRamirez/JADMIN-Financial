@extends('layouts.logeado')

@section('sub_title', 'Detalles Factura de Compra #' . $factura->numero)

@section('style')
    <style>
        #tbdetalles th{
            background-color: aliceblue;
            font-weight: bolder;
        }
    </style>
@endsection

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <table class="table table-bordered" id="tbdetalles">
                <tr>
                    <th>Concepto</th>
                    <td>{{$factura->descripcion}}</td>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <td>{{$factura->fecha}}</td>
                </tr>
                <tr>
                    <th>Tercero</th>
                    <td>{{$factura->tercero->nombre}}</td>
                </tr>
                <tr>
                    <th>Valor</th>
                    <td>{{number_format($factura->valor, 2, ",", ".")}}</td>
                </tr>
            </table>
            
            <h5>Asiento contable</h5>
            <hr>
            <table class="table table-bordered" >
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Nombre</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($factura->movimientos as $movimiento)
                    <tr>
                        <td>{{$movimiento->cuenta->codigo}}</td>
                        <td>{{$movimiento->cuenta->nombre}}</td>
                        @if ($movimiento->naturaleza == "Crédito")
                            <td>0</td>
                            <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>
                        @else
                            <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>
                            <td>0</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
                
            </table>
		</div>
	</div>
@endsection