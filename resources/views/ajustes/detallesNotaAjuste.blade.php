@extends('layouts.logeado')

@section('sub_title', 'Detalles Nota Contable #' . $nota->id)

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row form-group">
                <label for="" class="col-md-3"><h5>Numero</h5></label>
                <div class="col-md-9 form-control">{{$nota->id}}</div>
            </div>
            <div class="row form-group">
                <label for="" class="col-md-3"><h5>Fecha</h5></label>
                <div class="col-md-9 form-control">{{$nota->fecha}}</div>
            </div>
            <div class="row form-group">
                <label for="" class="col-md-3"><h5>Concepto</h5></label>
                <div class="col-md-9 form-control">{{$nota->concepto}}</div>
            </div>
            <div class="row form-group">
                <label for="" class="col-md-3"><h5>Tercero</h5></label>
                <div class="col-md-9 form-control">{{$nota->tercero->documento}}-{{$nota->tercero->nombre}}</div>
            </div>
            
            <hr>
            <div class="text-center"><h5>Asiento contable</h5></div>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Nombre</th>
                        <th>Tipo movimiento</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nota->movimientos as $movimiento)
                        <tr>
                            <td>{{$movimiento->cuenta->codigo}}</td>
                            <td>{{$movimiento->cuenta->nombre}}</td>
                            <td>{{$movimiento->naturaleza}}</td>
                            <td>{{number_format($movimiento->valor, 2, ",", ".")}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
		</div>
	</div>
@endsection
