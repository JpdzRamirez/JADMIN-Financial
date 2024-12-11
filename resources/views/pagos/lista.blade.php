@extends('layouts.logeado')

@section('sub_title', 'Pagos Realizados')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<form action="/pagos/filtrar" method="GET" id="formpagos"></form>
			<div class="table-responsive" style="overflow-x: hidden">
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Fecha</th>
							<th>#Recibo</th>
							<th>Vehículo</th>
                            <th>Cliente</th>                         
                            <th>Valor</th>         
						</tr>
						<tr>
							<th></th>
							<th></th>
							<th></th>
							<th><input type="text" maxlength="8" name="placa" class="form-control filt" id="placa" form="formpagos"></th>
							<th><input type="text" name="cliente" class="form-control filt" id="cliente" form="formpagos"></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse($pagos as $pago)
							<tr>
								<td>{{ $pago->id }}</td>
                                <td>{{ $pago->fecha }}</td>
								<td>{{ $pago->recibo->prefijo }} {{ $pago->recibo->numero }}</td>
								<td>@if ($pago->credito->placas != null)
										{{ $pago->credito->placas }}
									@endif
								</td>
                                <td>{{ $pago->cliente->nro_identificacion }}, {{ $pago->cliente->primer_nombre }} {{ $pago->cliente->primer_apellido }}</td>
                                <td>{{ number_format($pago->valor, 2, ",", ".") }}</td>
								<td><a href="/creditos/{{ $pago->credito->id }}/plan_pagos" class="btn btn-sm btn-primary">Ver crédito</a><br>
								<a class="btn btn-sm btn-success" href="/pagos/{{$pago->id}}/descargar_recibo" target="_blank">Recibo</a></td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="6">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>		
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).ready(function () {
			@if(isset($placa))
				$("#placa").val("{{$placa}}");
			@endif
			@if(isset($cliente))
				$("#cliente").val("{{$cliente}}");
			@endif
		});

		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formpagos").submit();
    		}
		});
	</script>
@endsection