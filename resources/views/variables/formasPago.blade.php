@extends('layouts.logeado')

@section('sub_title', 'Formas de pago')

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
                            <th>Prefijo</th>             
						</tr>
					</thead>
					<tbody>
						@forelse($formas as $forma)
							<tr>
								<td>{{ $forma->id }}</td>
                                <td>{{ $forma->nombre }}</td>                             
								<td>{{ $forma->cuenta->codigo }}-{{ $forma->cuenta->nombre }}</td>
                                <td>{{ $forma->prefijo}}</td>
								<td>
									<button onclick="editar({{$forma->id}}, '{{$forma->nombre}}', {{$forma->cuenta->codigo}}, '{{$forma->prefijo}}')" class="btn btn-warning btn-sm">Editar</button>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="3">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
                @if(method_exists($formas,'links'))
					{{ $formas->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="width: 50%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nueva Forma de Pago</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/contabilidad/formas_pago/nuevo" method="POST">
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
                        <label for="prefijo" class="col-md-3 label-required">Prefijo</label>
                        <div class="col-md-9">
                            <select name="prefijo" id="prefijo" class="form-control">
                                <option value="Caja">Caja</option>
                                <option value="Banco">Banco</option>
                            </select>
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
                    <h4 class="modal-title">Editar Forma de Pago</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/contabilidad/formas_pago/editar" method="POST">
                    <input type="hidden" name="idforma" id="idforma">
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
                        <label for="eprefijo" class="col-md-3 label-required">Prefijo</label>
                        <div class="col-md-9">
                            <select name="eprefijo" id="eprefijo" class="form-control">
                                <option value="Caja">Caja</option>
                                <option value="Banco">Banco</option>
                            </select>
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
        function editar(id, nombre, cuenta, prefijo) {
            $("#idforma").val(id);
            $("#enombre").val(nombre);
            $("#ecuenta").val(cuenta);
            $("#eprefijo").val(prefijo);

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