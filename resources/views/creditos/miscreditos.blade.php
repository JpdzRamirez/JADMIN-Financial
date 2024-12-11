@extends('layouts.logeado')

@section('sub_title', 'Mis Créditos')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@if (session('creditos'))
					<div class="alert alert-danger">
						<h5>{{ session('creditos') }}</h5>						
					</div>
			@endif
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Monto</th>
                            <th>Cuotas</th>
                            <th>Tasa</th> 
                            <th>Estado</th>            
						</tr>
					</thead>
					<tbody>
						@forelse($creditos as $credito)
							<tr>
								<td>{{ $credito->id }}</td>
                                <td>${{ number_format($credito->monto, 0, ",", ".") }}</td>
								<td>@if ($credito->estado == "Solicitado" || $credito->estado == "Evaluando" || $credito->estado == "Aprobado")
										0/{{ $credito->plazo }}
									@else
										{{ $credito->pagadas }}/{{ count($credito->cuotas) }}
									@endif									
								</td>
                                <td>{{ number_format($credito->tasa, 2, ",", ".") }} EA</td>
                                <td>{{ $credito->estado }}</td>
								<td>
									@if ($credito->estado == "En cobro" || $credito->estado == "Finalizado")
										<a href="/creditos/{{$credito->id}}/plan_pagos" class="btn btn-success btn-sm"> Amortización</a><br>
										<a href="/pagos/credito/{{$credito->id}}" class="btn btn-primary btn-sm">Pagos realizados</a>
									@endif
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