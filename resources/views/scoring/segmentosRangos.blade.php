@extends('layouts.logeado')

@section('sub_title', 'Segmentos de la variable ' . $scoring->nombre)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="table-responsive" id="listar">
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
                            <th>Inferior</th>
                            <th>Superior</th>
                            <th>Puntaje</th>                     
						</tr>
					</thead>
					<tbody>
						@forelse($scoring->segmentos as $segmento)
							<tr>
								<td>{{ $segmento->inicio }}</td>
                                <td>{{ $segmento->fin }}</td>
                                <td>{{ $segmento->score }}</td>
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