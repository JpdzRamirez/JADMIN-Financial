@extends('layouts.logeado')

@section('sub_title', 'Tasas de Interés')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if($errors->first('sql') != null)
                <div class="alert alert-danger" style="margin:10px 0">
                    <h6>{{$errors->first('sql')}}</h6>
                </div>				
            @endif
            <div class="align-center">
                <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Agregar tasa interés</a>			
            </div>
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Año</th>
                            <th>Mes</th>
                            <th>Valor</th>                
						</tr>
					</thead>
					<tbody>
						@forelse($tasas as $tasa)
							<tr>
								<td>{{ $tasa->id }}</td>
                                <td>{{ $tasa->year }}</td>
                                <td>{{ $tasa->mes }} </td>
								<td>{{ number_format($tasa->valor, 2, ',', '.') }} EA</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="5">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($tasas,'links'))
					{{ $tasas->links() }}
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
                <h4 class="modal-title">Nuevo tasa de interés</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="/tasas_interes/nuevo" method="POST">
            <div class="modal-body">
                <div class="row form-group">
                    <div class="col-md-3">
                        <label for="year" class="label-required">Año</label>                         
                    </div>
                    <div class="col-md-9">
                        <input type="number" step="1" min="2000" name="year" id="year" class="form-control" required>	
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label for="tipo" class="label-required">Mes</label>                         
                    </div>
                    <div class="col-md-9">
                        <select name="mes" id="mes" class="form-control">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label for="descripcion" class="label-required">Valor (EA)</label>                         
                    </div>
                    <div class="col-md-9">
                        <input type="number" step="0.01" name="valor" id="valor" class="form-control" required>	
                    </div>
                </div>					
            </div>
            <div class="modal-footer">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <button type="submit" class="btn btn-success">Guardar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection