@extends('layouts.logeado')

@section('sub_title', 'Nota Crédito: Factura de ' . $factura->tipo . ' #' . $factura->numero )

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<form action="/contabilidad/notas_credito/generar" method="post" id="formNota">
				<input type="hidden" name="factura" id="factura" value="{{$factura->id}}">
				<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                <input type="hidden" name="productos" id="productos">
                @if($factura->credito != null)
                    <input type="hidden" name="tipopro" id="tipopro" value="Costos">
                @else
                    <input type="hidden" name="tipopro" id="tipopro" value="Productos">
                @endif
               
                <div class="row form-group">
					<label for="motivo" class="col-md-2">Motivo:</label>
					<div class="col-md-6">
						<select name="motivo" id="motivo" class="form-control">
                            <option value="1">Devolución parcial de los bienes y/o no aceptación parcial del servicio</option>
                            <option value="2">Anulación de factura electrónica</option>
                            <option value="3">Rebaja o descuento parcial o total</option>
                            <option value="4">Ajuste de precio</option>
                            <option value="5">Otros</option>
                        </select>
					</div>
				</div>
				<div class="row form-group">
					<label for="concepto" class="col-md-2">Concepto:</label>
					<div class="col-md-6">
						<input type="text" id="concepto" name="concepto" class="form-control">
					</div>
				</div>
			</form>
			
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Producto</th>
                        <th>Valor Venta</th>
						<th>IVA</th>
						<th>Cantidad a devolver</th>
						<th>Valor a devolver</th>
					</tr>
				</thead>
				<tbody>
                    @if ($factura->credito != null)
                        <tr>
                            <td>Préstamo {{$factura->credito->tipo}}</td>
                            <td>{{number_format($factura->credito->monto, 2, ",", ".")}}</td>
                            <td>0
                                <input type="hidden" value="0" name="0-iva" form="formNota">
                            </td>
							<td>
                                <input type="number" step="1" name="0-cantidad" form="formNota" class="form-control">
                            </td>
							<td>
								<input type="number" step="0.1" name="0-valor" form="formNota" class="form-control">
							</td>
                        </tr>
                        @foreach ($factura->credito->costos as $costo)
                            <tr>
                                <td>{{$costo->descripcion}}</td>
                                <td>{{number_format($costo->pivot->valor, 2 , ",", ".")}}</td>
                                <td>@if ($costo->iva == 1)
                                        {{number_format($costo->pivot->valor*0.19, 2 , ",", ".")}}
                                        <input type="hidden" value="1" name="{{$costo->id}}-iva"  form="formNota">
                                    @else
                                        0
                                        <input type="hidden" value="0" name="{{$costo->id}}-iva"  form="formNota">
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="1" name="{{$costo->id}}-cantidad" form="formNota" class="form-control">
                                </td>
                                <td>
                                    <input type="number" step="0.1" name="{{$costo->id}}-valor" form="formNota" class="form-control">
                                </td>
                            </tr>
                        @endforeach
                    @else
                        @foreach ($factura->productos as $producto)
                            <tr>
                                <td>{{$producto->nombre}}</td>
                                <td>{{number_format($producto->pivot->valor, 2 , ",", ".")}}</td>
                                <td>@if ($producto->pivot->iva > 0)
                                        {{number_format($producto->pivot->iva, 2 , ",", ".")}}
                                        <input type="hidden" name="{{$producto->id}}" value="1" name="{{$producto->id}}-iva" form="formNota">
                                    @else
                                        <input type="hidden" name="{{$producto->id}}" value="0" name="{{$producto->id}}-iva" form="formNota">
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="1" name="{{$producto->id}}" name="{{$producto->id}}-cantidad" form="formNota" class="form-control">
                                </td>
                                <td>
                                    <input type="number" step="0.1" name="{{$producto->id}}" name="{{$producto->id}}-valor" form="formNota" class="form-control">
                                </td>
                            </tr>
                        @endforeach
                    @endif
					
				</tbody>		
			</table>
			<div class="text-center">
				<button type="button" class="btn btn-dark" onclick="enviar();">Generar Nota</button>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script>
        function enviar() {
            Swal.fire({
                title: '<strong>Enviando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
            var datos = $("#formNota").serializeArray();
            var productos = new Array();
            for (let index = 0; index < datos.length; index++) {
                if(index >= 6){
                    if(datos[index+1].value != "" && datos[index+2].value != ""){
                        let prod = new Object();
                        let partes = datos[index].name.split("-");
                        prod.id = partes[0];
                        prod.iva = datos[index].value;
                        prod.cantidad = datos[index+1].value;
                        prod.valor = datos[index+2].value;
                        productos.push(prod);
                    }  
                    index = index + 2;
                }
            }
            $("#productos").val(JSON.stringify(productos));

            $.ajax({
                type: "post",
                url: "/contabilidad/notas_credito/generar_nuevos",
                data: $("#formNota").serialize(),
                dataType: "json"
            }).done(function (data) {  
                Swal.close();
                if(data.respuesta == "success"){
                    Swal.fire({
                        title: "Nota Credito emitida",
                        text: "La nota fue emitida exitosamente",
                        icon: data.respuesta,
                        confirmButtonText: 'OK',
                    }).then((result) => {
                        window.open("/contabilidad/notas/" + data.msj + "/descargar");
                        location.href = "/contabilidad/notas/" + data.msj + "/detalles";
                    });
                }else{
                    Swal.fire(
                        "Error",
                        data.msj,
                        data.respuesta
                    );
                }
            }).fail(function (jqXHR, textStatus, errorThrown) { 
                Swal.close();
                Swal.fire(
                    'Error enviando los datos',
                    textStatus,
                    'error'
                );
            });
        }
	</script>
@endsection