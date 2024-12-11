@extends('layouts.logeado')

@section('sub_title', 'Costos asociados')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="align-center">
                <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Nuevo costo</a>			
            </div>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Descripción</th>
                            <th>Valor</th> 
                            <th>IVA</th>                    
						</tr>
					</thead>
					<tbody>
						@forelse($costos as $costo)
							<tr>
								<td>{{ $costo->id }}</td>
                                <td>{{ $costo->descripcion }}</td>                             
								<td>@if ($costo->tipo == "Absoluto")
                                        ${{ number_format($costo->valor) }}
                                    @else
                                        {{ number_format($costo->valor) }}%
                                    @endif
                                </td>
                                <td>
                                    @if ($costo->iva == "1")
                                        SI
                                    @else
                                        NO
                                    @endif
                                </td>
								<td>
									<button onclick="editar({{$costo->id}},'{{$costo->descripcion}}','{{$costo->tipo}}',{{$costo->valor}}, {{$costo->iva}})" class="btn btn-warning btn-sm">Editar</button>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="3">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($costos,'links'))
					{{ $costos->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="width: 40%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo costo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/costos/nuevo" method="POST">
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="descripcion" class="col-md-3 label-required">Descripción del costo</label>                         
                        <div class="col-md-9">
                            <input type="text" name="descripcion" id="descripcion" class="form-control">	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="descripcion" class="col-md-3 label-required">Tipo de valor del costo</label>                         
                        <div class="col-md-9">
                            <select name="tipo" id="tipo" class="form-control">
                                <option value="Absoluto">Absoluto</option>
                                <option value="Porcentual">Porcentual</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="iva" class="col-md-3 label-required">I.V.A</label>                         
                        <div class="col-md-9">
                            <input type="checkbox" name="iva" id="iva" class="form-control">
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="tipo" class="col-md-3 label-required">Valor</label>                         
                        <div class="col-md-9">
                            <input type="number" step="0.1" name="valor" id="valor" min="0" class="form-control">
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

    <div id="ModEditar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar costo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/costos/editar" method="POST">
                    <input type="hidden" name="idcosto" id="idcosto">
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="descripcion" class="col-md-3 label-required">Descripción del costo</label>                         
                        <div class="col-md-9">
                            <input type="text" name="edescripcion" id="edescripcion" class="form-control" readonly>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="descripcion" class="col-md-3 label-required">Tipo de valor del costo</label>                         
                        <div class="col-md-9">
                            <select name="etipo" id="etipo" class="form-control" disabled>
                                <option value="Absoluto">Absoluto</option>
                                <option value="Porcentual">Porcentual</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="iva" class="col-md-3 label-required">I.V.A</label>                         
                        <div class="col-md-9">
                            <input type="checkbox" name="eiva" id="eiva" class="form-control">
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="tipo" class="col-md-3 label-required">Valor</label>                         
                        <div class="col-md-9">
                            <input type="number" step="0.1" name="evalor" id="evalor" min="0" class="form-control">
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
@section('script')
    <script>
        function editar(id, descripcion, tipo, valor, iva) {
            $("#idcosto").val(id);
            $("#edescripcion").val(descripcion);
            $("#evalor").val(valor);
            $("#tipo").val(tipo);
            if(iva == 1){
                console.log("checar");
                $("#eiva").prop('checked', true);
            }else{
                $("#eiva").prop('checked', false);
            }

            $("#ModEditar").modal("show");
        }
    </script>
@endsection