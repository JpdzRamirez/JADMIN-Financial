@extends('layouts.logeado')

@section('sub_title', 'Variables de Scoring')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="align-center">
                <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Nueva Variable</a>			
            </div>
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo de segmento</th>                     
						</tr>
					</thead>
					<tbody>
						@forelse($variables as $variable)
							<tr>
								<td>{{ $variable->id }}</td>
                                <td>{{ $variable->nombre }}</td>
								<td>{{ $variable->tipo }}</td>
								<td>
									<a href="/scoring/{{$variable->id}}/segmentos" class="btn btn-info btn-sm">Segmentos</a>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="3">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($variables,'links'))
					{{ $variables->links() }}
				@endif			
			</div>
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nueva Variable</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/scoring/nuevo" method="POST">
                <div class="modal-body">
                    <div class="row form-group">
                        <div class="col-md-3">
                            <label for="nombre" class="label-required">Nombre de la variable</label>                         
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="nombre" id="nombre" class="form-control" required>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-3">
                            <label for="tipo" class="label-required">Tipo de segmentos</label>                         
                        </div>
                        <div class="col-md-9">
                            <select name="tipo" class="form-control" id="tipo">
                                <option value="Valores fijos">Valores fijos</option>
                                <option value="Rangos">Rangos</option>
                            </select>
                        </div>
                    </div>					
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <button type="submit" class="btn btn-success">Continuar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection