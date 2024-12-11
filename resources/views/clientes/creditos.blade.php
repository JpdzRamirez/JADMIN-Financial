@extends('layouts.logeado')

@section('sub_title', 'Créditos cliente: ' . $cliente->nro_identificacion)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" style="overflow-x: hidden">
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Número</th>
                            <th>Fecha desembolso</th>
                            <th>Cuotas</th>
                            <th>Monto</th>
                            <th>Estado</th>          
						</tr>
					</thead>
					<tbody>
						@forelse($cliente->creditos as $credito)
							<tr>
								<td>{{ $credito->numero }}</td>
                                <td>{{ $credito->fecha_prestamo }}</td>
                                <td>{{ $credito->pagadas }} / {{ count($credito->cuotas) }}</td>
								<td>{{ number_format($credito->monto, 0, ",", ".") }}</td>
                                <td>{{ $credito->estado }}</td>
								<td>
									<a href="/creditos/{{$credito->id}}/plan_pagos" class="btn btn-primary btn-sm">Amortización</a>
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
	</div>
@endsection