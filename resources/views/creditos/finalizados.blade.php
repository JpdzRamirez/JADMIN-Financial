@extends('layouts.logeado')

@section('sub_title', 'Créditos Finalizados')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<form action="/creditos_finalizados/filtrar" method="GET" id="formfinalizados"></form>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Número</th>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Plazo original</th>
							<th>Cuotas pagadas</th>
                            <th>Tasa</th>
							<th>Estado</th>                
						</tr>
						<tr>
							<th><input type="number" class="form-control filt" name="numero" id="numero" form="formfinalizados"></th>
							<th><input type="text" class="form-control filt" name="identificacion" id="identificacion" form="formfinalizados"></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th>
								<select name="estado" class="form-control" id="estado" form="formfinalizados" onchange="this.form.submit()">
									<option value=""></option>
									<option value="Finalizado">Finalizado</option>
									<option value="Rechazado">Rechazado</option>
								</select>
							</th>								
						</tr>
					</thead>
					<tbody>
						@forelse($creditos as $credito)
							<tr>
								<td>{{ $credito->numero }}</td>
                                <td>{{ $credito->cliente->nro_identificacion }}</td>
                                <td>{{ number_format($credito->monto, 2, ",", ".") }}</td>
								<td>{{ $credito->plazo }}</td>
								<td>{{ $credito->pagadas }}</td>
                                <td>{{ $credito->tasa }} EA</td>
								<td>{{ $credito->estado }}</td>
								<td>
									<a href="/creditos/{{$credito->id}}/plan_pagos" class="btn btn-success btn-sm"> Amortización</a><br>
									<a href="/pagos/credito/{{$credito->id}}" class="btn btn-primary btn-sm"> Ver Pagos</a>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="7">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($creditos,'links'))
					{{ $creditos->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).ready(function () {
			@if(isset($num))
				$("#numero").val("{{$num}}");
			@endif
			@if(isset($ide))
				$("#identificacion").val("{{$ide}}");
			@endif
			@if(isset($est))
				$("#estado").val("{{$est}}");
			@endif
		});


		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formfinalizados").submit();
    		}
		});
	</script>
@endsection