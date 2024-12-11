@extends('layouts.logeado')

@section('sub_title', 'Vehiculos de ' . $cliente->primer_nombre . " " . $cliente->primer_apellido)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Placa</th>
                            <th>Empresa</th>               
						</tr>
					</thead>
					<tbody>
						@forelse($cliente->placas as $placa)
							<tr>
								<td>{{ $placa->id }}</td>
                                <td>{{ $placa->placa }}</td>
                                <td>{{ $placa->empresa }} </td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="3">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>		
			</div>
		</div>
	</div>
@endsection