@extends('layouts.logeado')

@section('sub_title', 'Productos')

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
                <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Nuevo</a>			
            </div>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Nombre</th>
                            <th>Cuenta</th>
                            <th>Contrapartida</th>              
						</tr>
					</thead>
					<tbody>
						@forelse($productos as $producto)
							<tr>
								<td>{{ $producto->id }}</td>
                                <td>{{ $producto->nombre }}</td>                             
								<td>{{ $producto->cuenta->codigo }}-{{ $producto->cuenta->nombre }}</td>
                                <td>{{ $producto->contrapartida->codigo }}-{{ $producto->contrapartida->nombre }}</td>
								<td>
									<button onclick="editar({{$producto->id}}, '{{$producto->nombre}}', {{$producto->cuenta->codigo}}, {{$producto->contrapartida->codigo}})" class="btn btn-warning btn-sm">Editar</button>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="3">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
                @if(method_exists($productos,'links'))
					{{ $productos->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="width: 50%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo Producto</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/contabilidad/productos/registrar" method="POST">
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="nombre" class="col-md-3 label-required">Nombre</label>                         
                        <div class="col-md-9">
                            <input type="text" name="nombre" id="nombre" class="form-control" required>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="cuenta" class="col-md-3 label-required">Cuenta</label>
                        <div class="col-md-9">
                            <input type="text" name="cuenta" id="cuenta" data-modal="#Modal" class="form-control newcuenta" autocomplete="off" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="contrapartida" class="col-md-3 label-required">Contrapartida</label>
                        <div class="col-md-9">
                            <input type="text" name="contrapartida" id="contrapartida" data-modal="#Modal" class="form-control newcuenta" autocomplete="off" required>
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
                    <h4 class="modal-title">Editar producto</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/contabilidad/productos/editar" method="POST">
                    <input type="hidden" name="idproducto" id="idproducto">
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="enombre" class="col-md-3 label-required">Nombre</label>                         
                        <div class="col-md-9">
                            <input type="text" name="enombre" id="enombre" class="form-control" required>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="ecuenta" class="col-md-3 label-required">Cuenta</label>
                        <div class="col-md-9">
                            <input type="text" name="ecuenta" id="ecuenta" class="form-control edicuenta" autocomplete="off" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="econtrapartida" class="col-md-3 label-required">Contrapartida</label>
                        <div class="col-md-9">
                            <input type="text" name="econtrapartida" id="econtrapartida" class="form-control edicuenta" autocomplete="off" required>
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
        function editar(id, nombre, cuenta, contrapartida) {
            $("#idproducto").val(id);
            $("#enombre").val(nombre);
            $("#ecuenta").val(cuenta);
            $("#econtrapartida").val(contrapartida);

            $("#ModEditar").modal("show");
        }

        $(".newcuenta").autocomplete({
            appendTo: "#Modal",
      		source: function( request, response ) {
                $.ajax({
                    url: "/contabilidad/cuentas/buscar",
                    dataType: "json",
                    data: {cuenta: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.codigo + "_" + item.nombre;
							item.value = item.codigo;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3
        });

        $(".edicuenta").autocomplete({
            appendTo: "#ModEditar",
      		source: function( request, response ) {
                $.ajax({
                    url: "/contabilidad/cuentas/buscar",
                    dataType: "json",
                    data: {cuenta: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.codigo + "_" + item.nombre;
							item.value = item.codigo;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3
        });
    </script>
@endsection