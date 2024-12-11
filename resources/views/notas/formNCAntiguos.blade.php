@extends('layouts.logeado')

@section('sub_title', 'Nota Crédito: Factura de ' . $factura->tipo . ' #' . $factura->numero )

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<form action="/contabilidad/notas_credito/generar_antiguos" method="post" id="formNota">
				<input type="hidden" name="factura" id="factura" value="{{$factura->id}}">
				<input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                <div class="row form-group">
                    <label for="concepto" class="col-md-2">Concepto:</label>
                    <div class="col-md-6">
                        <input type="text" id="concepto" name="concepto" class="form-control">
                    </div>
                </div>
			</form>
            <div class="row form-group">
                <label for="meses" class="col-md-2">Meses a descontar:</label>
                <div class="col-md-6">
                    <input type="number" id="meses" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="button" onclick="calcular();" class="btn btn-dark">Calcular</button>
                </div>
            </div>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Cuenta</th>
						<th>Nombre</th>
						<th>Tipo</th>
						<th>Valor</th>
						<th>Valor a disminuir</th>
					</tr>
				</thead>
				<tbody>
                    <tr>
                        <td>28050510</td>
                        <td>INTERESES CORRIENTE LIBRE INVERSION</td>
                        <td>Crédito</td>
                        <td>18.678,00</td>
                        <td><input type="number" step="0.01" value="0" id="28050510" name="28050510" form="formNota" class="form-control"></td>
                    </tr>
					<tr>
                        <td>415535</td>
                        <td>PROCESAMIENTO DE DATOS-PLATAFORMA</td>
                        <td>Crédito</td>
                        <td>7.500,00</td>
                        <td><input type="number" step="0.01" value="0" id="415535" name="415535" form="formNota" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>415550</td>
                        <td>SOPORTE Y ASESORÍA</td>
                        <td>Crédito</td>
                        <td>5.000,00</td>
                        <td><input type="number" step="0.01" value="0" id="415550" name="415550" form="formNota" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>415555</td>
                        <td>CONSULTA CENTRALES</td>
                        <td>Crédito</td>
                        <td>8.000,00</td>
                        <td><input type="number" step="0.01" value="0" id="415555" name="415555" form="formNota" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>240801</td>
                        <td>IVA GENERADO (EN VENTAS Y SERVICIOS)</td>
                        <td>Crédito</td>
                        <td>3.895,00</td>
                        <td><input type="number" step="0.01" value="0" id="240801" name="240801" form="formNota" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>28151005</td>
                        <td>SEGUROS DE VIDA LA AURORA</td>
                        <td>Crédito</td>
                        <td>120.000,00</td>
                        <td><input type="number" step="0.01" value="0" id="28151005" name="28151005" form="formNota" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>13050501</td>
                        <td>CLIENTES CREDITOS</td>
                        <td>Débito</td>
                        <td>163.073,00</td>
                        <td><input type="number" step="0.01" value="0" id="13050501" name="13050501" form="formNota" class="form-control"></td>
                    </tr>
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
        function calcular() {
            let meses = $("#meses").val();
            $("#28050510").val((18678/12)*meses);
            $("#415535").val((7500/12)*meses);
            $("#415550").val((5000/12)*meses);
            $("#415555").val((8000/12)*meses);
            $("#240801").val((3895/12)*meses);
            $("#28151005").val((120000/12)*meses);
            $("#13050501").val((163073/12)*meses);
        }

        function enviar() {
            Swal.fire({
                title: '<strong>Enviando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
            $.ajax({
                type: "post",
                url: "/contabilidad/notas_credito/generar_antiguos",
                data: $("#formNota").serialize(),
                dataType: "json"
            }).done(function (data) {  
                Swal.close();
                if(data.respuesta == "success"){
                    Swal.fire({
                        title: "Nota Crédito",
                        text: "La nota crédito fue emitida exitosamente",
                        icon: data.respuesta,
                        confirmButtonText: 'OK',
                    }).then((result) => {
                        window.open("/contabilidad/notas/" + data.msj + "/descargar")
                        location.href = "/contabilidad/facturas/ventas";
                    });
                }else{
                    Swal.fire(
                        data.msj,
                        "La emisión de la nota falló",
                        data.respuesta
                    );
                }
            }).fail(function () {  

            });
        }
	</script>
@endsection