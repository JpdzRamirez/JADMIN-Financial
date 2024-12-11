@extends('layouts.logeado')

@section('sub_title', 'Seguros')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if (session("error"))
                <div class="alert alert-danger">
                    <h5>Error: {{session("error")}}</h5>
                </div>
            @endif
            @if (session("ok"))
                <div class="alert alert-success">
                    <h5>{{session("ok")}}</h5>
                </div>
            @endif
            <div class="align-center">
                <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Nuevo Seguro</a>
                <a href="/seguros/renovar" class="btn btn-dark btn-sm">Renovar Seguros</a>		
                <a href="/seguros/prefactura" class="btn btn-dark btn-sm"><i class="fa fa-download"></i> Prefactura</a>	
            </div>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Tercero</th>
                            <th>Último Vencimiento</th>
                            <th>Facturadas</th>
                            <th>Pagadas</th>
                            <th>Estado</th>           
						</tr>
					</thead>
					<tbody>
						@forelse($seguros as $seguro)
							<tr>
								<td>{{ $seguro->id }}</td>
                                <td>{{ $seguro->tercero->documento }}-{{ $seguro->tercero->nombre }}</td>
                                <td>{{ $seguro->vencimiento }}</td>                             
                                <td>{{ $seguro->facturadas}}</td>
                                <td>{{ $seguro->pagadas}}</td>                                    
                                <td>{{ $seguro->estado}}</td>
                                <td>
                                    @if ($seguro->estado == "Activo")
                                        <button type="button" class="btn btn-sm btn-danger" onclick="inactivar({{$seguro->id}});">Inactivar</button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editar({{$seguro->id}}, '{{$seguro->tercero->nombre}}', {{$seguro->valor}}, {{$seguro->interes}}, {{$seguro->costos}}, '{{$seguro->vencimiento}}');">Editar</button>
                                </td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="6">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
                @if(method_exists($seguros,'links'))
					{{ $seguros->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="width: 70%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo Seguro</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/seguros/registrar" method="POST">
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="tipos" class="col-md-3 label-required">Tipo de Seguro</label>                         
                        <div class="col-md-9">
                           <select name="tipos" id="tipos" class="form-control">
                               <option value="Nuevo">Nuevo</option>
                               <option value="Antiguo">Antiguo</option>
                            </select>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="tercero" class="col-md-3 label-required">Tercero</label>                         
                        <div class="col-md-9">
                            <input type="text" name="tercero" id="tercero" class="form-control" autocomplete="off" required>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="valor" class="col-md-3 label-required">Valor</label>
                        <div class="col-md-9">
                            <input type="number" step="0.1" name="valor" id="valor" class="form-control" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="interes" class="col-md-3 label-required">Interés</label>
                        <div class="col-md-9">
                            <input type="number" step="0.01" name="interes" id="interes" class="form-control" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="costos" class="col-md-3 label-required">Costos</label>
                        <div class="col-md-9">
                            <input type="number" step="0.01" name="costos" id="costos" class="form-control" required>
                        </div>	
                    </div>	
                    <div class="row form-group">
                        <label for="vencimiento" class="col-md-3 label-required">Primer Vencimiento</label>
                        <div class="col-md-9">
                            <input type="date" name="vencimiento" id="vencimiento"  class="form-control" required>
                        </div>	
                    </div>
                    <div class="row form-group" id="antiguo" style="display: none">
                        <label for="pagadas" class="col-md-3 label-required">Cuotas pagadas</label>
                        <div class="col-md-9">
                            <input type="number" step="0.1" name="pagadas" id="pagadas"  class="form-control">
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
        <div class="modal-dialog" style="width: 70%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Seguro</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/seguros/editar" method="POST">
                <div class="modal-body">
                    <div class="row form-group">                       
                        <div class="col-md-9">
                            <input type="text" name="idseguro" id="idseguro" class="form-control" autocomplete="off" required hidden>
                        </div>
                    </div>
            
                    <div class="row form-group">
                        <label for="tercero" class="col-md-3 label-required">Tercero</label>                         
                        <div class="col-md-9">
                            <input type="text" name="editercero" id="editercero" class="form-control" autocomplete="off" required disabled>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="valor" class="col-md-3 label-required">Valor</label>
                        <div class="col-md-9">
                            <input type="number" step="0.1" name="edivalor" id="edivalor" class="form-control" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="interes" class="col-md-3 label-required">Interés</label>
                        <div class="col-md-9">
                            <input type="number" step="0.01" name="ediinteres" id="ediinteres" class="form-control" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="costos" class="col-md-3 label-required">Costos</label>
                        <div class="col-md-9">
                            <input type="number" step="0.01" name="edicostos" id="edicostos" class="form-control" required>
                        </div>	
                    </div>	
                    <div class="row form-group">
                        <label for="vencimiento" class="col-md-3 label-required">Primer Vencimiento</label>
                        <div class="col-md-9">
                            <input type="date" name="edivencimiento" id="edivencimiento"  class="form-control" required>
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
        $("#tercero").autocomplete({
            appendTo: "#Modal",
      		source: function( request, response ) {
                $.ajax({
                    url: "/contabilidad/terceros/buscar",
                    dataType: "json",
                    data: {tercero: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.documento + "_" + item.nombre;
							item.value = item.documento;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3
        });

        function inactivar(seguro) {
            Swal.fire({
                title: 'Inactivar el seguro #' + seguro,
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: `Cancelar`,
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.href = "/seguros/"+seguro+"/inactivar";
                    }
                });
        }

        $("#tipos").change(function (e) { 
            if($(this).val() == "Antiguo"){
                $("#antiguo").css("display", "flex");
            }else{
                $("#antiguo").css("display", "none");
            }
        });

        function editar(idseguro, tercero, valor, interes, costos, vencimiento){
            $("#idseguro").val(idseguro);
            $("#editercero").val(tercero);
            $("#edivalor").val(valor);
            $("#ediinteres").val(interes);
            $("#edicostos").val(costos);
            $("#edivencimiento").val(vencimiento);
            
            $("#ModEditar").modal("show");

        }
    </script>
@endsection