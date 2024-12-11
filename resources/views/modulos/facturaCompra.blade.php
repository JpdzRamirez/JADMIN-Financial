@extends('layouts.logeado')

@section('sub_title', 'Factura Compra #' . $factura->prefijo . ' ' . $factura->numero)

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
				<div class="col-md-6" style="padding: 0">
                    <input type="number" class="form-control" name="pago" id="pago" value="{{$pagar}}">
				</div>
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
		var movimientos;
        var forma;
        var pago;
        var formater = new Intl.NumberFormat("es-CO", {maximumFractionDigits: 2});

        $("#pago").blur(function (e) { 
           $("#forma").trigger("change");
        });

		$("#forma").change(function (e) { 
            forma = $(this).val();
            if (forma != null) {
                pago = $('#pago').val();
                $.ajax({
                    type: "post",
                    url: "/contabilidad/facturas/compras/asiento",
                    data: {_token:'{{csrf_token()}}', factura:{{$factura->id}}, forma: forma, pago: pago},
                    dataType: "json",
                }).done(function (data) { 
                    $("#tbasiento").empty();
                        if(data.length > 0){
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
            } 
        });

		function enviar() {
			if(movimientos.length > 0){
                Swal.fire({
                    title: '<strong>Enviando...</strong>',
                    html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                    showConfirmButton: false,
                });
				$.ajax({
                    type: "post",
                    url: "/contabilidad/facturas/compras/registrar_pago",
                    data: {_token:'{{csrf_token()}}', factura:{{$factura->id}}, forma: forma, movimientos: JSON.stringify(movimientos), pago: pago, 'pagar': {{$pagar}}},
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
                            window.open("/contabilidad/comprobantes/" + data.msj + "/descargar");
                            location.href = "/contabilidad/facturas/compras";
                        });
                    }else{
                        Swal.fire(
                            data.msj,
                            "La emisión del egreso  falló",
                            data.respuesta
                        );
                    }
                }).fail(function () {  
                    Swal.close();
                });
			}else{
				Swal.fire(
					'Sin forma de pago',
					'Debe seleccionar una forma de pago',
					'error'
				);
			}
		}
	</script>
@endsection