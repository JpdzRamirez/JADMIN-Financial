@extends('layouts.logeado')

@section('sub_title', 'Nuevo Documento Soporte')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="row form-group">
				<label for="fecha" class="col-md-2 label-required">Fecha</label>
				<div class="col-md-6">
					<input type="datetime-local" name="fecha" id="fecha" value="{{$fecha->format('Y-m-d').'T'.$fecha->format('H:i:s')}}" class="form-control">
				</div>
			</div>
			<div class="row form-group">
				<label for="tercero" class="col-md-2 label-required">Tercero</label>
				<div class="col-md-6">
					<input type="text" name="tercero" id="tercero" class="form-control terce" autocomplete="off">
				</div>
			</div>
			<div class="row form-group">
				<label for="concepto" class="col-md-2 label-required">Concepto</label>
				<div class="col-md-6">
					<textarea name="concepto" id="concepto" rows="3" class="form-control"></textarea>
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
				<label for="terceromov" class="col-md-2">Tercero</label>
				<div class="col-md-6">
					<input type="text" name="terceromov" id="terceromov" class="form-control terce" autocomplete="off">
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

			<div class="row form-group">
				<label for="reteiva" class="col-md-2">Reteiva</label>
				<div class="col-md-6">
					<select name="reteiva" id="reteiva" class="form-control">
						<option value="0">No</option>
						@foreach ($reteivas as $reteiva)
							<option value="{{$reteiva->id}}">{{$reteiva->concepto}}</option>
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
					<table style="width: 100%; margin-top: 1rem">
						<tr>
							<th style="text-align: center">Cuenta</th>
							<td><input type="text" name="extcuenta" id="extcuenta" class="form-control cta" autocomplete="off"></td>
							<th style="text-align: center">Contrapartida</th>
							<td><input type="text" name="extcontra" id="extcontra" class="form-control cta" autocomplete="off"></td>
						</tr>
						<tr style="height: 1rem"></tr>
						<tr>
							<th style="text-align: center">Tercero</th>
							<td><input type="text" name="exttercero" id="exttercero" class="form-control terce" autocomplete="off"></td>
							<th style="text-align: center">Valor</th>
							<td><input type="number" name="extvalor" id="extvalor" class="form-control"></td>
						</tr>
					</table>
					<div class="text-right m-t-10">
						<button type="button" class="btn btn-sm btn-dark" onclick="addExtra();">Agregar extra</button>
					</div>
					<table class="table table-bordered m-t-30" style="table-layout: fixed">
						<thead>
							<tr style="background-color: lightgray">
								<th>Cuenta</th>
								<th>Descripción</th>
								<th>Tercero</th>
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
			let predeterminado = $("#tercero").val()
			if (producto != 0 && $("#valor").val() > 0 && predeterminado != "") {
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
					url: "/contabilidad/facturas/compras/calculos",
					data: {producto:$("#producto").val(), iva:$("#iva").val(), retefuente:$("#retefuente").val(), 
						reteica:$("#reteica").val(),reteiva:$("#reteiva").val(), predeterminado:predeterminado, tercero:$("#terceromov").val(), exicas:extras},
					dataType: "json"
				}).done(function (datos) {  
					if(datos != null){
						let posicion = productos.length;
						datos.producto.cuenta.valor = valor;
						datos.producto.cuenta.tipo = "Débito";
						datos.producto.cuenta.tercero = datos.tercero.documento + "-" + datos.tercero.nombre;
						datos.producto.cuenta.idtercero = datos.tercero.id;
						datos.producto.cuenta.producto = posicion;
						movimientos.push(datos.producto.cuenta)
						if($("#iva").is(':checked')) {
							datos.iva.valor = valor*0.19;
							contra = contra + parseFloat(datos.iva.valor);
							datos.iva.tipo = "Débito";
							datos.iva.tercero = datos.tercero.documento + "-" + datos.tercero.nombre;
							datos.iva.idtercero = datos.tercero.id;
							datos.iva.producto =posicion;
							movimientos.push(datos.iva);
						}
						if(datos.retefuente != null){
							datos.retefuente.compra.valor = valor*(datos.retefuente.porcentaje/100);
							datos.retefuente.compra.tipo = "Crédito";
							datos.retefuente.compra.tercero = datos.tercero.documento + "-" + datos.tercero.nombre;
							datos.retefuente.compra.idtercero = datos.tercero.id;
							contra = contra - parseFloat(datos.retefuente.compra.valor);
							datos.retefuente.compra.producto = posicion;
							movimientos.push(datos.retefuente.compra);
						}
						if(datos.reteica != null){
							datos.reteica.compra.valor = valor*(datos.reteica.porcentaje/100);
							datos.reteica.compra.tipo = "Crédito";
							datos.reteica.compra.tercero = datos.tercero.documento + "-" + datos.tercero.nombre;
							datos.reteica.compra.idtercero = datos.tercero.id;
							contra = contra - parseFloat(datos.reteica.compra.valor);
							datos.reteica.compra.producto = posicion;
							movimientos.push(datos.reteica.compra);

							for (const key in datos.extras) {
								datos.extras[key].compra.valor = datos.reteica.compra.valor * (datos.extras[key].porcentaje/100);
								datos.extras[key].compra.tipo = "Crédito";
								datos.extras[key].compra.tercero = datos.tercero.documento + "-" + datos.tercero.nombre;
								datos.extras[key].compra.idtercero = datos.tercero.id;
								contra = contra - parseFloat(datos.extras[key].compra.valor);
								datos.extras[key].compra.producto = posicion;
								movimientos.push(datos.extras[key].compra);
							}
						}
						if(datos.reteiva != null){
							datos.reteiva.compra.valor = valor*(datos.reteiva.porcentaje/100);
							datos.reteiva.compra.tipo = "Crédito";
							datos.reteiva.compra.tercero = datos.tercero.documento + "-" + datos.tercero.nombre;
							datos.reteiva.compra.idtercero = datos.tercero.id;
							contra = contra - parseFloat(datos.reteiva.compra.valor);
							datos.reteiva.compra.producto = posicion;
							movimientos.push(datos.reteiva.compra);
						}
						datos.producto.contrapartida.valor = contra;
						datos.producto.contrapartida.tipo = "Crédito";
						datos.producto.contrapartida.producto = posicion;
						datos.producto.contrapartida.tercero = datos.predeterminado.documento + "-" + datos.predeterminado.nombre;
						datos.producto.contrapartida.idtercero = datos.predeterminado.id;
						movimientos.push(datos.producto.contrapartida);
						datos.producto.iva = datos.iva.valor;
						datos.producto.cantidad = cantidad;
						datos.producto.valor = valor;
						productos.push(datos.producto);

						graficar();

						$("#producto").val(0);
						$("#valor").val(0);
						$("#terceromov").val("");
						$("#cantidad").val(1);
						$("#retefuente").val(0);
						$("#reteica").val(0);
						$("#reteiva").val(0),
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
					$fila = $fila + '<td></td>';
				}
				$fila = $fila + '<td>' + productos[key].valor.toLocaleString('es-Co') + '</td>';
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
								if(movimientos[i].idtercero == movimientos[j].idtercero){
									movimientos[j].dibujado = 1;
									if(movimientos[i].tipo != movimientos[j].tipo){
										total = total - movimientos[j].valor;
									}else{
										total = total + movimientos[j].valor;
									}
								}else{
									if(movimientos[j].extra == "1"){
										movimientos[j].dibujado = 1;
										if(movimientos[i].tipo != movimientos[j].tipo){
											total = total - movimientos[j].valor;
										}else{
											total = total + movimientos[j].valor;
										}
									}
								}	
							}
						}
					$fila = '<tr id="' + movimientos[i].codigo + '">';
					$fila = $fila + '<td>' + movimientos[i].codigo + '</td><td>' + movimientos[i].nombre + '</td><td>';
					$fila = $fila + movimientos[i].tercero +  '</td><td>';
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

		$(".terce").autocomplete({
      		source: function( request, response ) {
                $.ajax({
                    url: "/contabilidad/terceros/buscar",
                    dataType: "json",
                    data: {tercero: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.documento + "-" + item.nombre;
							item.value = item.documento;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3,
            change: function (event, ui) {
				if(ui.item == null){
					$(this).val("");
				}
			}
        });

		$(".cta").autocomplete({
      		source: function(request, response ) {
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

		function addExtra() {
			if(movimientos.length > 0){
				$.ajax({
					type: "post",
					url: "/contabilidad/extras_contables/calcular",
					data: { movimientos: JSON.stringify(movimientos),
							_token: "{{csrf_token()}}",
							cuenta: $("#extcuenta").val(), 
							contrapartida: $("#extcontra").val(),
							tercero: $("#exttercero").val(),
							valor: $("#extvalor").val()
					},
					dataType: "json"
				}).done(function (data) {
					movimientos = data;
					$("#extcuenta").val(""); 
					$("#extcontra").val("");
					$("#exttercero").val("");
					$("#extvalor").val("");
					graficar();
				}).fail(function () {  
					Swal.fire(
						'Error',
						'No se pudo obtener el asiento',
						'error'
					);
				});
			}else{
				Swal.fire(
					'Sin movimientos',
					'Debe agregar por lo menos un producto',
					'warning'
				);
			}
		}

		function borrar(posicion) {
			largo = movimientos.length;
			indice = 0;
			while(indice < largo){
				if(movimientos[indice].producto == posicion || movimientos[indice].extra == 1){
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
					url: "/contabilidad/facturas/soportes/enviar",
					data: {	datos: JSON.stringify(movimientos),
							productos: JSON.stringify(productos),
							concepto: $("#concepto").val(),
							tercero: $("#tercero").val(),
							fecha: $("#fecha").val(),
							_token: "{{csrf_token()}}",
							total: totalFactura},
					dataType: "json"
				}).done(function (data) {  
					Swal.close();
					if(data.respuesta == "success"){
						Swal.fire({
							title: "Documento soporte emitido",
							text: "El Documento soporte fue emitido exitosamente",
							icon: data.respuesta,
							confirmButtonText: 'OK',
						}).then((result) => {
							window.open("/contabilidad/facturas/compras/"+ data.msj + "/imprimir")
							location.href = "/contabilidad/facturas/soportes";
						});
					}else{
						Swal.fire(
							data.msj,
							"La emisión del documento soporte falló",
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