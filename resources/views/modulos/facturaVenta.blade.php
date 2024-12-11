@extends('layouts.logeado')

@section('sub_title', 'Factura Venta #' . $factura->prefijo . ' ' . $factura->numero)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="row form-group">
				<label for="tercero" class="col-md-2">Tercero</label>
				<div class="col-md-6 form-control">
					{{$factura->tercero->nombre}}
				</div>
			</div>
            <div class="row form-group">
				<label for="concepto" class="col-md-2">Concepto</label>
				<div class="col-md-6 form-control">
					{{$factura->descripcion}}
				</div>	
			</div>
            <div class="row form-group">
				<label for="valor" class="col-md-2">Saldo</label>
				<div class="col-md-6 form-control">
					{{number_format($cobrar+$factura->mora, 2, ",", ".")}}
				</div>
			</div>
            <div class="row form-group">
				<label for="vencimiento" class="col-md-2">Fecha de vencimiento</label>
				<div class="col-md-4 form-control">
					{{$factura->vencimiento}}
				</div>
				<label for="mora" class="col-md-2" style="text-align: center">Mora</label>
				<div class="col-md-4 form-control">
					{{number_format($factura->mora, 2, ",", ".")}}
				</div>
			</div>
            <div class="row form-group">
				<label for="valor" class="col-md-2">Abono</label>
				<div class="col-md-6" style="padding: 0">
					<input type="number" step="0.01" name="valor" id="valor" placeholder="Pago total: {{number_format($cobrar+$factura->mora,2,',','.')}}" class="form-control">
				</div>
			</div>
            <div class="row form-group">
				<label for="retefuente" class="col-md-2">Retefuente</label>
				<div class="col-md-6" style="padding: 0">
					<select name="retefuente" id="retefuente" class="form-control">
						<option value="">No</option>
						@foreach ($retefuentes as $retefuente)
							<option value="{{$retefuente->id}}">{{$retefuente->concepto}}</option>
						@endforeach
					</select>
				</div>	
			</div>
			<div class="row form-group">
				<label for="reteica" class="col-md-2">Reteica</label>
				<div class="col-md-6" style="padding: 0">
					<select name="reteica" id="reteica" class="form-control">
						<option value="">No</option>
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
            <div class="row form-group">
                <label for="valor" class="col-md-2 ">Forma de pago:</label>
                <div class="col-md-6 col-xs-10" style="padding: 0">
                    <select name="forma" id="forma" class="form-control">
                        <option value="0" disabled selected>Seleccionar forma de pago</option>
                        @foreach ($formas as $forma)
                            <option value="{{$forma->id}}">{{$forma->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
			<div class="row form-group">
                <label for="observaciones" class="col-md-2 ">Observaciones:</label>
                <div class="col-md-6 col-xs-10" style="padding: 0">
                    <textarea name="observaciones" id="observaciones" rows="5" class="form-control"></textarea>
                </div>
            </div>
            
			<hr>
			<h5>Asiento Contable</h5>
			<hr>

			<table class="table table-bordered" style="table-layout: fixed">
				<thead>
					<tr style="background-color: lightgray">
						<th>Cuenta</th>
						<th>Descripción</th>
						<th>Débito</th>
						<th>Crédito</th>
					</tr>
				</thead>
                <tbody id="tbasiento">

                </tbody>
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
        var forma;
        var formater = new Intl.NumberFormat("es-CO", {maximumFractionDigits: 2});
        var cobrar = {{$cobrar+$factura->mora}};
        var valor;

		$("#forma").change(function (e) {    
            if($("#valor").val() > 0 && $("#valor").val() <= cobrar){
                forma = $(this).val();
                let checks = document.getElementsByName("extras");
				let extras = [];
				for (let index = 0; index < checks.length; index++) {
					if(checks[index].checked == true){
						extras.push(checks[index].value);
					}
				}
                $.ajax({
                type: "post",
                url: "/contabilidad/facturas/ventas/asiento",
                data: {_token:'{{csrf_token()}}', factura:{{$factura->id}}, 
                    forma: $(this).val(), valor: $("#valor").val(), exicas:extras,
                    retefuente:$("#retefuente").val(), reteica:$("#reteica").val()},
                dataType: "json"
                }).done(function (data) {
                    $("#tbasiento").empty();
                        if(data.length > 0){
                            valor = $("#valor").val();
                            for (const key in data) {
                                let fila = '<tr><td>' + data[key].codigo + '</td><td>' + data[key].nombre + '</td>';
                                if (data[key].tipo == "Débito") {
                                    fila = fila + '<td>' + formater.format(data[key].valor) + '</td><td>0</td>';
                                }else{
                                    fila = fila + '<td>0</td><td>' + formater.format(data[key].valor) + '</td>';
                                }
                                $("#tbasiento").append(fila);
                            }
                            movimientos = data;
                        }
                }).fail(function () {  
                    Swal.fire(
                        'Asiento no disponible',
                        'No se pudo calcular el asiento',
                        'error'
                    );
                });
            }else{
                Swal.fire(
                    'Abono incorrecto',
                    'La cantidad a abonar debe ser mayor que cero y no mayor al saldo',
                    'error'
                );
            }
        });

        $("#reteica").change(function () {  
			if($(this).val() != 0){
				$("#extras").css("display", "block");
			}else{
				$("#extras").css("display", "none");
			}
		});

        $("#valor").blur(function (param) { 
            if($("#forma").val() != null && $(this).val() > 0){
                $("#forma").trigger("change");
            }
        });

		function enviar() {
			if(movimientos.length > 0 && valor == $("#valor").val()){
                Swal.fire({
                    title: '<strong>Enviando...</strong>',
                    html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                    showConfirmButton: false,
                });
				$.ajax({
                    type: "post",
                    url: "/contabilidad/facturas/ventas/registrar_cobro",
                    data: {_token:'{{csrf_token()}}', factura:{{$factura->id}}, movimientos: JSON.stringify(movimientos), 
							'forma': $("#forma").val(), 'cobrar': {{$cobrar+$factura->mora}}, 
							'valor': $("#valor").val(), 'observaciones': $("#observaciones").val()},
                    dataType: "json"
                }).done(function (data) {
                    Swal.close();
                    if(data.respuesta == "success"){
                        Swal.fire({
                            title: "Recibo realizado",
                            text: "El recibo fue emitido exitosamente",
                            icon: data.respuesta,
                            confirmButtonText: 'OK',
                        }).then((result) => {
                            window.open("/contabilidad/ingresos/" + data.msj + "/imprimir");
                            location.href = "/contabilidad/facturas/ventas";
                        });
                    }else{
                        Swal.fire(
                            data.msj,
                            "La emisión del recibo falló",
                            data.respuesta
                        );
                    }
                }).fail(function () {  
                    Swal.close();
                });
			}else{
				Swal.fire(
					'Datos incompletos',
					'Debe seleccionar una forma de pago e ingresar un valor',
					'error'
				);
			}
		}
	</script>
@endsection