@extends('layouts.logeado')

@section('sub_title', 'Pagos Realizados')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" style="overflow-x: hidden">
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Fecha</th>
                            <th>Valor</th>
                            <th>Crédito</th>
                            <th>Estado</th>            
						</tr>
					</thead>
					<tbody>
						@forelse($pagos as $pago)
							<tr>
								<td>{{ $pago->id }}</td>
                                <td>{{ $pago->fecha }}</td>
                                <td>{{ number_format($pago->valor) }}</td>
								<td>{{ $pago->cuota->credito->fecha_aprobado }}</td>
                                <td>{{ $pago->estado }}</td>
								<td>
									<a href="/mis_pagos/{{$pago->id}}/pagos" class="btn btn-info btn-sm">Pagos</a>
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