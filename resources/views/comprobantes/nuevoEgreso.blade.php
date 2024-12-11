@extends('layouts.logeado')

@section('sub_title', 'Nuevo Egreso')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="row form-group">
				<label for="tercero" class="col-md-2">Tercero</label>
				<div class="col-md-6">
					<input type="text" name="tercero" id="tercero" class="form-control terce" autocomplete="off">
				</div>
			</div>
			<div class="row form-group">
				<label for="concepto" class="col-md-2">Concepto</label>
				<div class="col-md-6">
					<textarea name="concepto" id="concepto" rows="3" class="form-control"></textarea>
				</div>	
			</div>
			<hr>
			<div class="row form-group">
				<label for="movimiento" class="col-md-2">Movimiento</label>
				<div class="col-md-6">
					<select name="movimiento" id="movimiento" class="form-control">
						<option value="Crédito">Crédito</option>
						<option value="Débito">Débito</option>						
					</select>
				</div>	
			</div>
			<div class="row form-group">
				<label for="valor" class="col-md-2">Valor</label>
				<div class="col-md-6">
					<input type="number" step="0.01" min="0" name="valor" value="0" id="valor" class="form-control">
				</div>	
			</div>
			<div class="row form-group">
				<label for="tercero" class="col-md-2">Tercero</label>
				<div class="col-md-6">
					<input type="text" name="terceromov" id="terceromov" class="form-control terce" autocomplete="off">
				</div>
			</div>
			<div class="row form-group">
				<label for="cuenta" class="col-md-2">Cuenta</label>
				<div class="col-md-6">
					<input type="text" name="cuenta" id="cuenta" class="form-control" autocomplete="off">
				</div>	
			</div>
			<h5>Asiento Contable</h5>
			<hr>

			<table class="table table-bordered" style="table-layout: fixed" id="tbasiento">
				<thead>
					<tr style="background-color: lightgray">
						<th>Cuenta</th>
						<th>Descripción</th>
						<th>Débito</th>
						<th>Crédito</th>
						<th>Tercero</th>
					</tr>
				</thead>
			</table>

			<div class="text-center m-t-30">
				<button type="button" class="btn btn-dark" onclick="enviar();">Guardar</button>
			</div>
		</div>
	</div>
@endsection
@section('script')
    <script>
		var movimientos = new Array();
		var credito = 0;
		var debito = 0;
		var prefijo = "EC2";
		$("#cuenta").autocomplete({
      		source: function( request, response ) {
                $.ajax({
                    url: "/contabilidad/cuentas/buscar",
                    dataType: "json",
                    data: {cuenta: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.codigo + "_" + item.nombre;
							item.value = item.codigo + "_" + item.nombre;
							item.valor = $("#valor").val();
							item.movi = $("#movimiento").val();
							item.tercero = $("#terceromov").val();
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3,
            select: function (event, ui) {
				movimientos.push(ui.item);
                $fila = '<tr id="' + ui.item.codigo + '">';
				$fila = $fila + '<td>' + ui.item.codigo + '</td><td>' + ui.item.nombre + '</td><td>';
				if ($("#movimiento").val() == "Crédito") {
					credito = credito + parseFloat(ui.item.valor);
					$fila = $fila + '0</td><td>' + parseFloat($("#valor").val()).toLocaleString('es-Co') + '</td>';
				}else{
					debito = debito + parseFloat(ui.item.valor);
					$fila = $fila + parseFloat($("#valor").val()).toLocaleString('es-CO') + '</td><td>0</td>';
				}
				let tercero = ui.item.tercero.split("_");
				$fila = $fila + '<td>' + tercero[1] + '</td>';
				if(ui.item.codigo == "11050501"){
					prefijo = "EC1";
				}
				$fila = $fila + '<td><button class="btn btn-sm btn-danger" onclick="borrar(' + (ui.item.codigo) + ')">Borrar</button></td></tr>'
				$("#tbasiento").append($fila);
				$("#valor").val(0);
				$("#cuenta").val("");
				$("#terceromov").val("");
				event.preventDefault();
            }
        });

		function borrar(codigo) {
			for (const key in movimientos) {
				if(movimientos[key].codigo == codigo){
					if(movimientos[key].movi == "Crédito"){
						credito = credito - parseFloat(movimientos[key].valor);
					}else{
						debito = debito - parseFloat(movimientos[key].valor);
					}
					if(movimientos[key].codigo == "11050501"){
						prefijo = "EC2";
					}
					movimientos.splice(key, 1);
					$("#"+codigo).remove();
					break;
				}
			}
		}

		$(".terce").autocomplete({
      		source: function(request, response) {
                $.ajax({
                    url: "/contabilidad/terceros/buscar",
                    dataType: "json",
                    data: {tercero: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.nombre;
							item.value = item.id + "_" + item.nombre;
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

		

		function enviar() {
			if(movimientos.length > 0){
				if (credito == debito) {
					Swal.fire({
						title: '<strong>Enviando...</strong>',
						html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
						showConfirmButton: false,
					});
					$.ajax({
						type: "post",
						url: "/contabilidad/egresos/enviar",
						data: {	datos: JSON.stringify(movimientos), 
								concepto: $("#concepto").val(),
								tercero: $("#tercero").val(),
								_token: "{{csrf_token()}}",
								prefijo: prefijo,
								total: credito},
						        dataType: "json"
					}).done(function (data) {  
						Swal.close();
						if(data.respuesta == "success"){
							Swal.fire({
								title: "Egreso registrado",
								text: "El egreso fue emitido exitosamente",
								icon: data.respuesta,
								confirmButtonText: 'OK',
							}).then((result) => {
								location.href = "/contabilidad/comprobantes/" + data.msj + "/descargar";
							});
						}else{
							Swal.fire(
								data.msj,
								"La emisión del egreso  falló",
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
						'Asiento contable incorrecto',
						'El asiento no está cuadrado',
						'error'
					);
				}
			}else{
				Swal.fire(
					'Sin movimientos',
					'Debe ingresar movimientos de cuentas',
					'error'
				);
			}
		}
	</script>
@endsection