@extends('layouts.logeado')

@section('sub_title', 'Pagos del crédito #' . $credito->numero)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" style="overflow-x: hidden">
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Fecha</th>
							<th>#Recibo</th>
							<th>Cliente</th>
							<th>Valor</th>
							<th>Cuotas abonadas</th>                       
                                     
						</tr>
					</thead>
					<tbody>
						@forelse ($credito->pagos as $pago)
							<tr>
								<td>{{ $pago->id }}</td>
                                <td>{{ $pago->fecha }}</td>
								<td>{{ $pago->recibo->prefijo }} {{ $pago->recibo->numero }}</td>
								<td>{{ $credito->cliente->nro_identificacion }}, {{ $credito->cliente->primer_nombre }} {{ $credito->cliente->primer_apellido }}</td>
                                <td>{{ number_format($pago->valor, 2, ",", ".") }}</td>
								<td>@php
										$cadcuotas = [];
										foreach ($pago->cuotas as $cuota) {
											$cadcuotas[] = $cuota->ncuota;
										}
									@endphp
									#{{ implode(",", $cadcuotas) }}
								</td>
								<td><a class="btn btn-sm btn-success" href="/pagos/{{$pago->id}}/descargar_recibo" target="_blank">Recibo</a></td>
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