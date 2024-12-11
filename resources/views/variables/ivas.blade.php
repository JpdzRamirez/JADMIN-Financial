@extends('layouts.logeado')

@section('sub_title', 'Valores de IVA')

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
                            <th>Concepto</th>
                            <th>Valor</th>                  
						</tr>
					</thead>
					<tbody>
						@forelse($ivas as $iva)
							<tr>
								<td>{{ $iva->id }}</td>
                                <td>{{ $iva->concepto }}</td>                             
								<td>{{ number_format($iva->porcentaje, 2) }}%</td>
								<td>
									<button onclick="editar({{$iva->id}}, '{{$iva->concepto}}', {{$iva->porcentaje}}, {{$iva->venta->codigo}}, {{$iva->compra->codigo}})" class="btn btn-warning btn-sm">Editar</button>
								</td>
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
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="width: 40%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo iva</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/contabilidad/ivas/nuevo" method="POST">
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="concepto" class="col-md-3 label-required">Concepto</label>                         
                        <div class="col-md-9">
                            <input type="text" name="concepto" id="concepto" class="form-control" required>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="tipo" class="col-md-3 label-required">Valor</label>                         
                        <div class="col-md-9">
                            <input type="number" step="0.1" name="valor" id="valor" min="0" class="form-control" required>
                        </div>
                    </div>	
                    <div class="row form-group">
                        <label for="venta" class="col-md-3 label-required">Cuenta Venta</label>
                        <div class="col-md-9">
                            <input type="text" name="venta" id="venta" data-modal="#Modal" class="form-control newcuenta" autocomplete="off" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="compra" class="col-md-3 label-required">Cuenta Compra</label>
                        <div class="col-md-9">
                            <input type="text" name="compra" id="compra" data-modal="#Modal" class="form-control newcuenta" autocomplete="off" required>
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
                    <h4 class="modal-title">Editar iva</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/contabilidad/ivas/editar" method="POST">
                    <input type="hidden" name="idiva" id="idiva">
                <div class="modal-body">
                    <div class="row form-group">
                        <label for="econcepto" class="col-md-3 label-required">Concepto</label>                         
                        <div class="col-md-9">
                            <input type="text" name="econcepto" id="econcepto" class="form-control" required>	
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="evalor" class="col-md-3 label-required">Valor</label>                         
                        <div class="col-md-9">
                            <input type="number" step="0.1" name="evalor" id="evalor" min="0" class="form-control" required>
                        </div>
                    </div>	
                    <div class="row form-group">
                        <label for="eventa" class="col-md-3 label-required">Cuenta Venta</label>
                        <div class="col-md-9">
                            <input type="text" name="eventa" id="eventa" data-modal="#Modal" class="form-control edicuenta" autocomplete="off" required>
                        </div>	
                    </div>
                    <div class="row form-group">
                        <label for="ecompra" class="col-md-3 label-required">Cuenta Compra</label>
                        <div class="col-md-9">
                            <input type="text" name="ecompra" id="ecompra" data-modal="#Modal" class="form-control edicuenta" autocomplete="off" required>
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
        function editar(id, concepto, valor, venta, compra) {
            $("#idiva").val(id);
            $("#econcepto").val(concepto);
            $("#evalor").val(valor);
            $("#eventa").val(venta);
            $("#ecompra").val(compra);

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