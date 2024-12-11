@extends('layouts.logeado')

@section('sub_title', 'Nueva Factura de Venta')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="row form-group">
				<label for="tercero" class="col-md-2 label-required">Tercero</label>
				<div class="col-md-6">
					<input type="text" name="tercero" id="tercero" class="form-control" autocomplete="off">
				</div>
			</div>
			<div class="row form-group">
				<label for="placa" class="col-md-2">Placa</label>
				<div class="col-md-6">
					<input type="text" name="placa" id="placa" class="form-control" minlength="7" maxlength="7">
				</div>
			</div>
			<div class="row form-group">
				<label for="concepto" class="col-md-2 label-required">Concepto</label>
				<div class="col-md-6">
					<textarea name="concepto" id="concepto" rows="3" class="form-control"></textarea>
				</div>	
			</div>
			<div class="row form-group">
				<label for="concepto" class="col-md-2 label-required">Forma de Pago</label>
				<div class="col-md-6">
					<select name="formapago" id="formapago" class="form-control">
						<option value="FECR">Crédito</option>
						<option value="FECT">Contado</option>
					</select>
				</div>	
			</div>
			<hr>

			<div class="row form-group">
				<label for="producto" class="col-md-2 label-required">Producto</label>
				<div class="col-md-6">
					<select name="producto" id="producto" class="form-control">
						<option value="0" selected disabled>Seleccionar producto</option>
						@foreach ($productos as $producto)
							<option value="{{$producto->id}}">{{$producto->nombre}}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="row form-group">
				<label for="cantidad" class="col-md-2 label-required">Cantidad</label>
				<div class="col-md-6">
					<input type="number" step="1" min="1" name="cantidad" value="1" id="cantidad" class="form-control">
				</div>	
			</div>
			<div class="row form-group">
				<label for="valor" class="col-md-2 label-required">Valor</label>
				<div class="col-md-6">
					<input type="number" step="0.01" min="0" name="valor" value="0" id="valor" class="form-control">
				</div>	
			</div>
			<div class="row form-group">
				<label for="iva" class="col-md-2">IVA</label>
				<div class="col-md-6">
					<input type="checkbox" name="iva" id="iva" class="form-control">
				</div>	
			</div>

			<div class="row form-group">
				<label for="retefuente" class="col-md-2">Retefuente</label>
				<div class="col-md-6">
					<select name="retefuente" id="retefuente" class="form-control">
						<option value="0">No</option>
						@foreach ($retefuentes as $retefuente)
							<option value="{{$retefuente->id}}">{{$retefuente->concepto}}</option>
						@endforeach
					</select>
				</div>	
			</div>
			<div class="row form-group">
				<label for="reteica" class="col-md-2">Reteica</label>
				<div class="col-md-6">
					<select name="reteica" id="reteica" class="form-control">
						<option value="0">No</option>
						@foreach ($reteicas as $reteica)
							<option value="{{$reteica->id}}">{{$reteica->concepto}}</option>
						@endforeach
					</select>
				</div>	
			</div>
			<div id="extras" style="display: none">
				@foreach ($extras as $extra)
					<div class="form-group row">
						<label for="{{$extra->id}}" class="col-md-2">{{$extra->nombre}}</label>
						<div class="col-md-6">
							<input type="checkbox" name="extras" value="{{$extra->id}}" class="form-control">
						</div>	
					</div>
				@endforeach
			</div>
			<div>
				<button type="button" onclick="agregar();" class="btn btn-dark">Agregar</button>
			</div>
			<hr>

			<ul class="nav nav-pills" id="myTab" role="tablist">
				<li class="nav-item" role="presentation">
				  <a class="nav-link active" id="productos-tab" data-toggle="pill" href="#productos" role="tab" aria-controls="productos" aria-selected="true">Productos</a>
				</li>
				<li class="nav-item" role="presentation">
				  <a class="nav-link" id="asiento-tab" data-toggle="pill" href="#asiento" role="tab" aria-controls="asiento" aria-selected="false">Asiento contable</a>
				</li>
			  </ul>
			  <div class="tab-content" id="myTabContent" style="min-height: 200px">
				<div class="tab-pane fade show active" id="productos" role="tabpanel" aria-labelledby="productos-tab">
					<table class="table table-bordered" style="table-layout: fixed">
						<thead>
							<tr style="background-color: lightgray">
								<th>Producto</th>
								<th>Valor</th>
								<th>Cantidad</th>
								<th>IVA</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody id="tbproductos"></tbody>
					</table>
				</div>
				<div class="tab-pane fade" id="asiento" role="tabpanel" aria-labelledby="asiento-tab">
					<table class="table table-bordered" style="table-layout: fixed">
						<thead>
							<tr style="background-color: lightgray">
								<th>Cuenta</th>
								<th>Descripción</th>
								<th>Débito</th>
								<th>Crédito</th>
							</tr>
						</thead>
						<tbody id="tbasiento"></tbody>
					</table>		
				</div>
			  </div>
			
			<div class="text-center m-t-30">
				<button type="button" class="btn btn-dark" onclick="enviar();">Guardar</button>
			</div>
		</div>
	</div>
@endsection
@section('script')
    <script>
		var productos = new Array();
		var movimientos = new Array();
		var totalFactura = 0;

		function agregar() {
			let producto = $("#producto").val();
			if (producto != 0 && $("#valor").val() > 0) {
				let cantidad = parseInt($("#cantidad").val());
				let valor = $("#valor").val() * cantidad;
				let contra = parseFloat(valor);
				let checks = document.getElementsByName("extras");
				let extras = [];
				for (let index = 0; index < checks.length; index++) {
					if(checks[index].checked == true){
						extras.push(checks[index].value);
					}
				}
				$.ajax({
					type: "get",
					url: "/contabilidad/facturas/ventas/calculos",
					data: {producto:$("#producto").val(), iva:$("#iva").val(), retefuente:$("#retefuente").val(), 
					reteica:$("#reteica").val(), exicas:extras},
					dataType: "json"
				}).done(function (datos) {  
					if(datos != null){
						let posicion = productos.length;
						datos.producto.cuenta.valor = valor;
						datos.producto.cuenta.tipo = "Crédito";
						datos.producto.cuenta.producto = posicion;
						movimientos.push(datos.producto.cuenta)
						if($("#iva").is(':checked')) {
							datos.iva.valor = valor*0.19;
							contra = contra + parseFloat(datos.iva.valor);
							datos.iva.tipo = "Crédito";
							datos.iva.producto = posicion;
							datos.producto.iva = datos.iva.valor;
							movimientos.push(datos.iva);
						}
						if(datos.retefuente != null){
							datos.retefuente.venta.valor = valor*(datos.retefuente.porcentaje/100);
							datos.retefuente.venta.tipo = "Débito";
							contra = contra - parseFloat(datos.retefuente.venta.valor);
							datos.retefuente.venta.producto = posicion;
							movimientos.push(datos.retefuente.venta);
						}
						if(datos.reteica != null){
							datos.reteica.venta.valor = valor*(datos.reteica.porcentaje/100);
							datos.reteica.venta.tipo = "Débito";
							contra = contra - parseFloat(datos.reteica.venta.valor);
							datos.reteica.venta.producto = posicion;
							movimientos.push(datos.reteica.venta);

							for (const key in datos.extras) {
								datos.extras[key].venta.valor = datos.reteica.venta.valor * (datos.extras[key].porcentaje/100);
								datos.extras[key].venta.tipo = "Débito";
								contra = contra - parseFloat(datos.extras[key].venta.valor);
								datos.extras[key].venta.producto = posicion;
								movimientos.push(datos.extras[key].venta);
							}
						}
						datos.producto.contrapartida.valor = contra;
						datos.producto.contrapartida.tipo = "Débito";
						datos.producto.contrapartida.producto = posicion;
						movimientos.push(datos.producto.contrapartida);
						
						datos.producto.cantidad = cantidad;
						datos.producto.valor = valor;
						productos.push(datos.producto);

						graficar();

						$("#producto").val(0);
						$("#valor").val(0);
						$("#cantidad").val(1);
						$("#retefuente").val(0);
						$("#reteica").val(0);
						$("#iva").attr("checked", false);
						$("#extras").css("display", "none");
					}
				}).fail(function () {  

				});
			}else{
				Swal.fire(
					'Datos incorrectos',
					'Asegurese de seleccionar un producto e ingresarel valor',
					'warning'
				);
			}
		}	

		function graficar() {
			$("#tbproductos").empty();
			for (const key in productos) {			
				$fila = '<tr id="' + productos[key].id + '">';
				$fila = $fila + '<td>' + productos[key].nombre + '</td><td>' + (productos[key].valor/productos[key].cantidad).toLocaleString('es-Co') + '</td>';
				$fila = $fila + '<td>' + productos[key].cantidad + '</td>';
				if(productos[key].iva != null){
					$fila = $fila + '<td>' + productos[key].iva.toLocaleString('es-Co') + '</td>';
				}else{
					$fila = $fila + '<td>0</td>';
				}
				$fila = $fila + '<td>' + (productos[key].valor+productos[key].iva).toLocaleString('es-Co') + '</td>';
				$fila = $fila + '<td><button type="button" class="btn btn-sm btn-danger" onclick="borrar(' + key + ')">Borrar</button></td></tr>';
				$("#tbproductos").append($fila);
			}
			
			$("#tbasiento").empty();
			totalFactura = 0;
			for (const key in movimientos) {
				movimientos[key].dibujado = null;
			}
			for (let i = 0; i < movimientos.length; i++) {		
				if(movimientos[i].dibujado == null){
					let total = 0;
					for (let j = 0; j < movimientos.length; j++) {
						if(movimientos[i].id == movimientos[j].id){
							movimientos[j].dibujado = 1;
							total = total + movimientos[j].valor;
						}
					}
					$fila = '<tr id="' + movimientos[i].codigo + '">';
					$fila = $fila + '<td>' + movimientos[i].codigo + '</td><td>' + movimientos[i].nombre + '</td><td>';
					if (movimientos[i].tipo == "Crédito") {
						totalFactura = totalFactura + parseFloat(total);
						$fila = $fila + '0</td><td>' + parseFloat(total).toLocaleString('es-Co') + '</td>';
					}else{
						$fila = $fila + parseFloat(total).toLocaleString('es-CO') + '</td><td>0</td>';
					}
					$("#tbasiento").append($fila);
				}
			}
		}

		$("#reteica").change(function () {  
			if($(this).val() != 0){
				$("#extras").css("display", "block");
			}else{
				$("#extras").css("display", "none");
			}
		});

		$("#tercero").autocomplete({
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

		function borrar(posicion) {
			largo = movimientos.length;
			indice = 0;
			while(indice < largo){
				if(movimientos[indice].producto == posicion){
					movimientos.splice(indice, 1);
					largo = movimientos.length;
				}else{
					indice++;
				}	
			}
			productos.splice(posicion, 1);
			graficar();
		}

		function enviar() {
			if ($("#tercero").val() != "" && $("#concepto").val() != "" && movimientos.length > 0){
				Swal.fire({
					title: '<strong>Enviando...</strong>',
					html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
					showConfirmButton: false,
				});
				$.ajax({
					type: "post",
					url: "/contabilidad/facturas/ventas/enviar",
					data: {	datos: JSON.stringify(movimientos), 
							concepto: $("#concepto").val(),
							tercero: $("#tercero").val(),
							formapago: $("#formapago").val(),
							placa: $("#placa").val(),
							productos: JSON.stringify(productos),
							_token: "{{csrf_token()}}",
							total: totalFactura},
					dataType: "json"
				}).done(function (data) {  
					Swal.close();
					if(data.respuesta == "success"){
						Swal.fire({
							title: "Factura emitida",
							text: "La factura fue emitida exitosamente",
							icon: data.respuesta,
							confirmButtonText: 'OK',
						}).then((result) => {
							location.href = "/contabilidad/facturas/ventas/"+ data.msj + "/imprimir";
						});
					}else{
						Swal.fire(
							data.msj,
							"La emisión de la factura falló",
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
			}else{
				Swal.fire(
					'Datos incompletos',
					"Diligencie tercero, concepto y productos",
					'error'
				);
			}
		}
	</script>
@endsection