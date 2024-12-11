@extends('layouts.logeado')

@section('sub_title', 'Créditos Aprobados')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<form action="/creditos_aprobados/filtrar" method="GET" id="formaprobados"></form>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Número</th>
                            <th>Cliente</th>
                            <th>Monto</th>
							<th>Monto a financiar</th>
                            <th>Plazo</th>
                            <th>Fecha de aprobación</th>               
						</tr>
						<tr>
							<th><input type="number" class="form-control filt" name="numero" id="numero" form="formaprobados"></th>
							<th><input type="text" class="form-control filt" name="identificacion" id="identificacion" form="formaprobados"></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse($creditos as $credito)
							<tr>
								<td>{{ $credito->numero }}</td>
                                <td>{{ $credito->cliente->nro_identificacion }}</td>
                                <td>${{ number_format($credito->monto, "0", ",", ".") }}</td>
								<td>${{ number_format($credito->monto_total, "0", ",", ".") }}</td>
								<td>{{ $credito->plazo }} meses</td>
                                <td>{{ $credito->fecha_resultado }}</td>
								<td>
									<a href="/solicitudes/{{$credito->id}}/colocar" class="btn btn-primary btn-sm">Colocar</a>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="6">No hay datos</td>
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
		});


		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formaprobados").submit();
    		}
		});
	</script>
@endsection