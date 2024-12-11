@extends('layouts.logeado')

@section('style')
    <style>
        .form-group{
            font-size: large;
        }

        .form-control{
            height: auto;
        }

        #one
        {
            box-shadow: 0px 0px 5px 2px rgba(0,0,0,0.2);
        }
        .it .btn-orange
        {
            background-color: transparent;
            border-color: #777!important;
            color: #777;
            text-align: left;
            width:100%;
        }
        .it input.form-control
        {
            height: 54px;
            border:none;
            margin-bottom:0px;
            border-radius: 0px;
            border-bottom: 1px solid #ddd;
            box-shadow: none;
        }
        .it .form-control:focus
        {
            border-color: #ff4d0d;
            box-shadow: none;
            outline: none;
        }
        .fileUpload {
            position: relative;
            overflow: hidden;
            margin: 10px;
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

        .it .fa-times{
            color: red;
            transform: scale(1.5);
        }
        
        .it .btn-new, .it .btn-next
        {
            margin: 30px 0px;
            border-radius: 0px;
            background-color: navy;
            color: #f5f5f5 !important;
            font-size: 16px;
            width: 155px;
        }
        .it .btn-next
        {
            background-color: #ff4d0d;
            color: #fff;
        }
        .it .btn-check
        {
            cursor:pointer;
            line-height:54px;
            color:red;
        }
        .it .uploadDoc
        {
            margin-bottom: 20px;
        }
        .it .btn-orange img {
            width: 30px;
        }

        .it #uploader .docErr
        {
            position: absolute;
            right:auto;
            left: 10px;
            top: -56px;
            padding: 10px;
            font-size: 15px;
            background-color: #fff;
            color: red;
            box-shadow: 0px 0px 7px 2px rgba(0,0,0,0.2);
            display: none;
        }
        .it #uploader .docErr:after
        {
            content: '\f0d7';
            display: inline-block;
            font-family: FontAwesome;
            font-size: 50px;
            color: #fff;
            position: absolute;
            left: 30px;
            bottom: -40px;
            text-shadow: 0px 3px 6px rgba(0,0,0,0.2);
        }

    </style>
@endsection

@section('sub_title', 'Evaluar Crédito ID: ' . $credito->id)

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

            @if ($credito->cliente->condicion == "Propietario" && $credito->placas != null)
                <div class="form-group row">
                    <label class="col-md-2">Placa:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->placas}}
                    </div>
                </div>
            @endif

            @if ($credito->cliente->personales != null)
            <br>
            <h4 style="color: navy">Caracterización</h3>
                <hr>
                <div class="form-group row">
                    <label class="col-md-2">Ocupación:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->cliente->personales->ocupacion}}
                    </div>
                    <label class="col-md-2">Tiempo Ejerciendo:</label>
                    <div class="col-md-4 form-control">
                       {{$credito->cliente->personales->tiempo_ocupacion}}
                    </div>
                </div>
    
                <div class="form-group row">
                    <label class="col-md-2">Ingresos mensuales:</label>
                    <div class="col-md-4 form-control">
                        ${{number_format($credito->cliente->personales->ingresos, 0, ",", ".")}}
                    </div>
                    <label class="col-md-2">Ingresos provenientes de:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->cliente->personales->proveniencia}}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2">Número de hijos:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->cliente->personales->hijos}}
                    </div>
                    <label class="col-md-2">Tenencia vivienda:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->cliente->personales->vivienda}}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2">Estado civil:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->cliente->personales->estado_civil}}
                    </div>
                    <label class="col-md-2">Tiempo con su pareja:</label>
                    <div class="col-md-4 form-control">
                       {{$credito->cliente->personales->tiempo_pareja}}
                    </div> 
                </div>
    
                <div class="form-group row">
                    <label class="col-md-2">Estrato:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->cliente->personales->estrato}}
                    </div>
                    <label class="col-md-2">Escolaridad:</label>
                    <div class="col-md-4 form-control">
                        {{$credito->cliente->personales->escolaridad}}
                    </div>
                </div>
            @endif         

            <h5 style="color: navy">Referencias familiares</h5>
            <br>
            <div class="form-group row">
                <label class="col-md-2">Nombre:</label>
                <div class="col-md-2 form-control">
                    {{$credito->cliente->referencias[0]->nombre}}
                </div>
                <label class="col-md-2 text-center">Parentesco:</label>
                <div class="col-md-2 form-control">
                    {{$credito->cliente->referencias[0]->parentesco}}
                </div>
                <label class="col-md-2 text-center">Celular:</label>
                <div class="col-md-2 form-control">
                    {{$credito->cliente->referencias[0]->celular}}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2">Nombre:</label>
                <div class="col-md-2 form-control">
                    {{$credito->cliente->referencias[1]->nombre}}
                </div>
                <label class="col-md-2 text-center">Parentesco:</label>
                <div class="col-md-2 form-control">
                    {{$credito->cliente->referencias[1]->parentesco}}
                </div>
                <label class="col-md-2 text-center">Celular:</label>
                <div class="col-md-2 form-control">
                    {{$credito->cliente->referencias[1]->celular}}
                </div>
            </div>

            <h5 style="color: navy">Referencia personal</h5>
            <br>
            <div class="form-group row">
                <label class="col-md-2">Nombre:</label>
                <div class="col-md-4 form-control">
                    {{$credito->cliente->referencias[2]->nombre}}
                </div>
                <label class="col-md-2 text-center">Celular:</label>
                <div class="col-md-4 form-control">
                    {{$credito->cliente->referencias[2]->celular}}
                </div>
            </div>

            @if ($vinculacion != null)
                <br>
                <h4 style="color: navy">Datos Vinculación</h3>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        @if ($vinculacion->tipo == "Conductor")
                            <table class="table table-bordered">
                                <tr>
                                    <th>Fecha vinculación</th>
                                    <td>{{$vinculacion->fecha}}</td>
                                </tr>
                                <tr>
                                    <th>Promedio mensual de vales (Últimos 3 meses)</th>
                                    <td>${{number_format($vinculacion->promedio, 0)}}</td>
                                </tr>
                            </table> 
                        @elseif($vinculacion->tipo == "Propietario")
                            <table class="table table-bordered">
                                <tr>
                                    <th>Placa</th>
                                    <th>Fecha vinculación</th>
                                </tr>
                                @foreach ($vinculacion->placas as $placa)
                                    <tr>
                                        <td>{{$placa->placa}}</td>
                                        <td>{{$placa->fecha}}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif 
                    </div>
                </div>    
            @endif
            <br>
            <h4 style="color: navy">Datos del Crédito</h3>
            <hr>

            <div class="form-group row">
                <label class="col-md-2">Destino:</label>
                <div class="col-md-4 form-control">
                   {{$credito->tipo}}
                </div>
                <label class="col-md-2 text-center">Monto solicitado:</label>
                <div class="col-md-4 form-control">
                    ${{ number_format($credito->monto, 0, ",", ".") }}
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2">Monto a financiar:</label>
                <div class="col-md-4 form-control">
                   ${{ number_format($credito->monto_total, 0, ",", ".") }}
                </div>
                <label class="col-md-2 text-center">Plazo:</label>
                <div class="col-md-4 form-control">
                    {{$credito->plazo}}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2">Cuota:</label>
                <div class="col-md-4 form-control">
                   ${{ number_format($credito->pago, 0, ",", ".") }}
                </div>
                <label class="col-md-2 text-center">Tasa (EA):</label>
                <div class="col-md-4 form-control">
                    {{ number_format($credito->tasa, 2, ",", ";") }}
                </div>
            </div>

            <form action="/solicitudes/{{ $credito->id }}/evaluacion" method="POST" enctype="multipart/form-data" id="formsolicitud">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <input type="hidden" name="decision" id="decision">
                <div class="form-group row">
                    <label class="offset-md-3 col-md-1">Score:</label>
                    <div class="col-md-4">
                    <input type="number" step="1" min="100" value="{{$credito->score}}" max="900" name="score" id="score" class="form-control" required>
                    </div>
                </div>
            </form>

            <h4 style="color: navy">Evidencias</h3>
            <hr>
            
            <div class="row it">
                <div class="col-md-12" id="one">
                    <div id="uploader">
                        <div class="row uploadDoc">
                            <div class="col-md-4">
                                <div class="docErr">Por favor subir archivo válido</div>
                                <div class="fileUpload btn btn-orange">
                                    <img src="https://image.flaticon.com/icons/svg/136/136549.svg" class="icon">
                                    <span class="upl" id="upload">Cargar documento</span>
                                    <input form="formsolicitud" name="evidencias[]" type="file" class="upload up" id="up" onchange="readURL(this);" />
                                </div>
                            </div>
                            <div class="col-md-2"><a class="btn-check"><i class="fa fa-times"></i></a></div>
                        </div>
                    </div>
                    <div class="text-center">
                    <a class="btn btn-new"><i class="fa fa-plus"></i> Nuevo</a>
                    </div>
                </div>
            </div>
            <br>
            <div class="text-right">
                <button type="button" class="btn btn-primary" onclick="descargar('{{$credito->id}}');">Descargar Formulario</button>
                <button type="button" form="formsolicitud" class="btn btn-lg btn-dark" onclick="continuar();">Continuar</button>
            </div>

		</div>
	</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Evaluación de Crédito</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row form-group">
                    <h4 class="col-md-6 text-right">Calificación:</h4>                         
                    <div class="col-md-6">
                        <h4 id="calificacion" style="color: navy"></h4>
                    </div>
                </div>
                @if ($vinculacion != null)
                <div class="row form-group">
                    <h4 class="col-md-6 text-right">Tiempo de vinculación:</h4>                         
                    <div class="col-md-6">
                        @if ($vinculacion->tipo == "Propietario")
                            @if ($vinculacion->tiempo >= 360)
                                <div class="text-success">
                                    <h4>El tiempo de vinculación es  {{$vinculacion->tiempo}} días.</h4>
                                </div>
                            @else
                                <div class="text-danger">
                                    <h4>El tiempo de vinculación de  {{$vinculacion->tiempo}} días es inferior al requerido</h4>
                                </div>
                            @endif
                        @elseif($vinculacion->tipo == "Conductor")
                            @if ($vinculacion->tiempo >= 180)
                                <div class="text-success">
                                    <h4>El tiempo de vinculación es  {{$vinculacion->tiempo}} días.</h4>
                                </div>
                            @else
                                <div class="text-danger">
                                    <h4>El tiempo de vinculación de  {{$vinculacion->tiempo}} días es inferior al requerido</h4>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>	
                @endif
                <div class="form-group row">
                    <h4 class="col-md-6 text-right">Sugerencia:</h4>                         
                    <div class="col-md-6">
                       @if ($credito->score < 630)
                        <div class="text-danger">
                            <h4>DENEGAR</h4>
                        </div>
                       @else
                       <div class="text-success">
                            <h4>APROBAR</h4>
                        </div>
                       @endif
                    </div>
                </div>	
            </div>
            <div class="modal-footer">                         
                <button type="button" onclick="enviar(0);" class="btn btn-lg btn-danger">Denegar</button>
                <button type="button" onclick="enviar(1);" class="btn btn-lg btn-success">Aprobar</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script>
        var fileTypes = ['pdf', 'jpg', 'png'];

        function continuar() {
            var score = parseInt($("#score").val());
            var calificacion;
            if(score == ""){
                Swal.fire(
                    'Error',
                    'Debe diligenciar el score de la central',
                    'error'
                );
            }else{
                if(score < 520){
                    calificacion = "Riesgo Alto";
                }else if(score < 630){
                    calificacion = "Riesgo Medio Alto";
                }else if(score < 740){
                    calificacion = "Riesgo Medio";
                }else if(score < 780){
                    calificacion = "Riesgo Medio Bajo";
                }else{
                    calificacion = "Riesgo Bajo";
                }

                $("#score").val(score);
                $("#calificacion").text(calificacion);
                $("#Modal").modal('show');
            }
        }

        function enviar(decision) {
            $("#decision").val(decision);
            $("#formsolicitud").submit();
        }

        function readURL(input) {
            if (input.files && input.files[0]) {
                var extension = input.files[0].name.split('.').pop().toLowerCase(); 
                isSuccess = fileTypes.indexOf(extension) > -1; 

                if (isSuccess) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        if (extension == 'pdf'){
                            $(input).closest('.fileUpload').find(".icon").attr('src','https://image.flaticon.com/icons/svg/179/179483.svg');
                        }
                        else if (extension == 'png'){ 
                            $(input).closest('.fileUpload').find(".icon").attr('src','https://image.flaticon.com/icons/svg/136/136523.svg'); 
                        }
                        else if (extension == 'jpg'){
                            $(input).closest('.fileUpload').find(".icon").attr('src','https://image.flaticon.com/icons/svg/136/136524.svg');
                        }
                        else {
                            $(input).closest('.uploadDoc').find(".docErr").slideUp('slow');
                        }
                    }

                    reader.readAsDataURL(input.files[0]);
                }
                else {
                    $(input).closest('.uploadDoc').find(".docErr").fadeIn();
                    setTimeout(function() {
                        $('.docErr').fadeOut('slow');
                    }, 5000);
                }
            }
        }
        
        $(document).on('change','.up', function(){
            var id = $(this).attr('id'); /* gets the filepath and filename from the input */
            var profilePicValue = $(this).val();
            var fileNameStart = profilePicValue.lastIndexOf('\\'); /* finds the end of the filepath */
            profilePicValue = profilePicValue.substr(fileNameStart + 1).substring(0,45); /* isolates the filename */
            //var profilePicLabelText = $(".upl"); /* finds the label text */
            if (profilePicValue != '') {
                //console.log($(this).closest('.fileUpload').find('.upl').length);
                $(this).closest('.fileUpload').find('.upl').html(profilePicValue); /* changes the label text */
            }
        });

        $(".btn-new").on('click',function(){
            $("#uploader").append('<div class="row uploadDoc"><div class="col-md-4"><div class="docErr">Por favor subir archivo válido</div><div class="fileUpload btn btn-orange"><img src="https://image.flaticon.com/icons/svg/136/136549.svg" class="icon"><span class="upl" id="upload">Cargar documento</span><input type="file" form="formsolicitud" name="evidencias[]" class="upload up" id="up" onchange="readURL(this);" /></div></div><div class="col-md-2"><a class="btn-check"><i class="fa fa-times"></i></a></div></div>');
        });
            
        $(document).on("click", "a.btn-check" , function() {
            if($(".uploadDoc").length>1){
                $(this).closest(".uploadDoc").remove();
            }else{
                alert("Se debe subir al menos un documento");
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

                filename = "Formulario evaluacion.xlsx";
                File = new Blob([byteArray], {type:'application/vnd.ms-excel'});
                downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(File);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();

                Swal.close();

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