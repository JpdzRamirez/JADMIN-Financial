@extends('layouts.logeado')

@section('sub_title', 'Resoluciones')

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
                            <th>Prefijo</th>
                            <th>Vencimiento</th>
                            <th>Límite</th>
                            <th>Actual</th>                 
						</tr>
					</thead>
					<tbody>
						@forelse($resoluciones as $resolucion)
							<tr>
                                <td>{{ $resolucion->prefijo }}</td>                             
								<td>{{ $resolucion->fechafi }}</td>
                                <td>{{ $resolucion->fin }}</td>
                                <td>{{ $resolucion->actual }}</td>
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