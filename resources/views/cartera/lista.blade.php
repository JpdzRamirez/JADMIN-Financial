@extends('layouts.logeado')

@section('sub_title', 'Cartera')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="align-center">	
                <a href="/cartera/descargar" class="btn btn-dark btn-sm"><i class="fa fa-download"></i> Cartera</a>	
            </div>
			<form action="/cartera/filtrar" method="GET" id="formcartera"></form>
			<table class="table table-bordered" style="table-layout: fixed">
				<thead>
					<tr>
						<th>Identificación</th>
						<th>Nombre</th>
						<th>Créditos</th>
						<th>Cuotas vencidas</th>
						<th>Total</th>            
					</tr>
					<tr>
						<th><input type="text" class="form-control filt" name="identificacion" id="identificacion" form="formcartera"></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@forelse($clientes as $cliente)
						<tr>
							<td>{{ $cliente->nro_identificacion }}</td>
							<td>{{ $cliente->primer_nombre }} {{ $cliente->primer_apellido }}</td>
							<td>{{ count($cliente->creditos) }}</td>
							@php
								$cuotas = 0;
								$total = 0;
								foreach ($cliente->creditos as $credito) {
									$cuotas = $cuotas + count($credito->cuotas);
									foreach ($credito->cuotas as $cuota) {
										$total = $total + $cuota->saldo_capital + $cuota->saldo_interes + $cuota->saldo_mora;
									}
								}
							@endphp
							<td>{{ $cuotas }}</td>
							<td>${{ number_format($total, 2, ",", ".") }}</td>
							<td><a href="/clientes/{{$cliente->id}}/creditos" class="btn btn-sm btn-warning">Créditos</a><br>
								<a href="/clientes/{{$cliente->id}}/editar" class="btn btn-sm btn-info">Ver Cliente</a>
							</td>
						</tr>
					@empty
						<tr class="align-center">
							<td colspan="5">No hay datos</td>
						</tr>
					@endforelse
				</tbody>
			</table>		
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).ready(function () {
			@if(isset($ide))
				$("#identificacion").val("{{$ide}}");
			@endif
		});


		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formcartera").submit();
    		}
		});

	</script>
@endsection