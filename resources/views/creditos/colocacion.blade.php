@extends('layouts.logeado')

@section('style')
    <style>
        .form-group{
            font-size: large;
        }
        
        .fileUpload {
            position: relative;
            overflow: hidden;
            background-color: transparent;
            border-color: #777!important;
            color: #777;
            text-align: left;
            width:100%;
        }
        .fileUpload input.upload {
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            padding: 0;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            filter: alpha(opacity=0);
        }
        

    </style>
@endsection

@section('sub_title', 'Colocar Crédito #' . $credito->numero)

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<h4 style="color: navy">Datos del Cliente</h3>
            <hr>
            <div class="form-group row">
                <label class="col-md-2">Tipo de identificación:</label>
                <div class="col-md-4 form-control">
                   {{$credito->cliente->tipo_identificacion}}
                </div>
                <label class="col-md-2 text-center">Número de identificación:</label>
                <div class="col-md-4 form-control">
                    {{$credito->cliente->nro_identificacion}}
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2">Nombres:</label>
                <div class="col-md-4 form-control">
                    {{ucfirst($credito->cliente->primer_nombre)}} {{ucfirst($credito->cliente->segundo_nombre)}}
                </div>
                <label class="col-md-2 text-center">Apellidos:</label>
                <div class="col-md-4 form-control">
                    {{ucfirst($credito->cliente->primer_apellido)}} {{ucfirst($credito->cliente->segundo_apellido)}}
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2">Celular:</label>
                <div class="col-md-4 form-control">
                    {{$credito->cliente->celular}}
                </div>
                <label class="col-md-2 text-center">Condición:</label>
                <div class="col-md-4 form-control">
                    {{$credito->cliente->condicion}}
                </div>
            </div>

            @if ($credito->placas != null)
                <div class="form-group row">
                    <label class="col-md-2">Placa:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->placas}}
                    </div>
                </div>
            @endif

            <h4 style="color: navy">Datos del Crédito</h3>
            <hr>

            <div class="form-group row">
                <label class="col-md-2">Fecha aprobación:</label>
                <div class="col-md-4 form-control">
                   {{$credito->fecha_resultado}}
                </div>
                <label class="col-md-2 text-center">Monto solicitado:</label>
                <div class="col-md-4 form-control">
                    ${{ number_format($credito->monto, 0, ",", ".") }}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2">Cuota:</label>
                <div class="col-md-4 form-control">
                   ${{ number_format($credito->pago, 0, ",", ".") }}
                </div>
                <label class="col-md-2 text-center">Destino:</label>
                <div class="col-md-4 form-control">
                   {{ $credito->tipo }}
                </div>
            </div>

            <form action="/solicitudes/{{ $credito->id }}/colocar" method="POST" enctype="multipart/form-data" id="formsolicitud">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <input type="hidden" name="credito" value="{{$credito->id}}">
                    <div class="row form-group">
                        <label class="col-md-2">Pagaré:</label>
                        <div class="col-md-4" style="padding: 0">
                            <div class="fileUpload btn">
                                <img src="https://image.flaticon.com/icons/svg/136/136549.svg" class="icon" width="30px">
                                <span class="upl" id="upload">Cargar pagaré</span>
                                <input name="pagare" type="file" class="upload up" id="up" onchange="readURL(this);" />
                            </div>
                        </div>       
                    </div>

                    <br>
                <div class="text-center">
                    <button type="button" class="btn btn-primary" onclick="descargar('{{$credito->id}}');">Descargar Formulario</button>
                    <button type="submit" class="btn btn-dark" id="btnsubmit" disabled>Guardar</button>
                </div>
            </form>       

		</div>
	</div>
@endsection
@section('script')
    <script>

        $("#formsolicitud").submit(function () {
            window.location.href =  "/creditos_cobro";
        });

        function enviar(decision) {
            $("#decision").val(decision);
            $("#formsolicitud").submit();
        }

        function readURL(input) {
            if (input.files && input.files[0]) {
                var extension = input.files[0].name.split('.').pop().toLowerCase();

                if (extension == "pdf") {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $(input).closest('.fileUpload').find(".icon").attr('src','https://image.flaticon.com/icons/svg/179/179483.svg');
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }
        }
        
        $(document).on('change','.up', function(){
            var id = $(this).attr('id');
            var profilePicValue = $(this).val();
            var fileNameStart = profilePicValue.lastIndexOf('\\');
            profilePicValue = profilePicValue.substr(fileNameStart + 1).substring(0,45); 
            if (profilePicValue != '') {
                $(this).closest('.fileUpload').find('.upl').html(profilePicValue);
            }
        });

        function descargar(credito) {

            Swal.fire({
				title: '<strong>Generando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" alt="Generando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/solicitudes/{{$credito->id}}/descargar_formulario"
			})
			.done(function (data, textStatus, jqXHR) {
				const byteCharacters = atob(data);
				const byteNumbers = new Array(byteCharacters.length);
				for (let i = 0; i < byteCharacters.length; i++) {
					byteNumbers[i] = byteCharacters.charCodeAt(i);
				}
				const byteArray = new Uint8Array(byteNumbers);

				var File;
				var downloadLink;

				filename = "Formulario #{{$credito->numero}}.xlsx";
				File = new Blob([byteArray], {type:'application/vnd.ms-excel'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(File);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();

				Swal.close();

                $("#btnsubmit").attr("disabled", false);
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});
        }

    </script>
@endsection