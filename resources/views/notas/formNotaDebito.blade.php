@extends('layouts.logeado')

@section('sub_title', 'Nota Débito: Factura de ' . $factura->tipo . ' #' . $factura->numero )

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<form action="/contabilidad/notas_debito/generar" method="post" id="formNota">
				<input type="hidden" name="factura" id="factura" value="{{$factura->id}}">
				<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
				<input type="hidden" name="cuentas" id="cuentas">
			</form>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Cuenta</th>
						<th>Nombre</th>
						<th>Tipo</th>
						<th>Valor</th>
						<th>@if ($factura->tipo == "Venta")
								Valor a aumentar
							@else
								Valor a disminuir
							@endif
						</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($factura->movimientos as $movimiento)
						<tr>
							<td>{{$movimiento->cuenta->codigo}}</td>
							<td>{{$movimiento->cuenta->nombre}}</td>
							<td>{{$movimiento->naturaleza}}</td>
							<td>{{number_format($movimiento->valor, 2 , ",", ".")}}</td>
							<td>
								<input type="number" step="0.1" value="0" id="{{$movimiento->id}}" form="formNota" class="form-control">
							</td>
						</tr>
					@endforeach
				</tbody>		
			</table>
			<div class="text-center">
				<button type="submit" class="btn btn-dark" form="formNota">Generar Nota</button>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script>
		var cuentas = new Array();

		$("#formNota").submit(function (evt) {
			$("#load").hide();
			var movimientos = $(':input[type="number"]');
			for (let index = 0; index < movimientos.length; index++) {
				let cuenta = new Object();
				cuenta.id = movimientos[index].id;
				cuenta.valor = movimientos[index].value;
				cuentas.push(cuenta);
			}
			$("#cuentas").val(JSON.stringify(cuentas))
		});
	</script>
@endsection