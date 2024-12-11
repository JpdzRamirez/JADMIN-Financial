@extends('layouts.logeado')

@section('sub_title', 'Nuevo Crédito')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if($errors->first('sql') != null)
                <div class="alert alert-danger" style="margin:10px 0">
                    <h6>{{$errors->first('sql')}}</h6>
                </div>				
            @endif

            <form action="/creditos/solicitar" method="POST" id="formcredito" accept-charset="UTF-8">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <input type="hidden" name="rol" value="cahorsadm">

                <div class="form-group row">
                    {{ Form::label('cliente', 'Cliente', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-8">
                        <input type="text" name="cliente" id="cliente" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="col-md-2">                    
                        <a href="/clientes/nuevo" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuevo Cliente</a>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('nombres', 'Nombres', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="text" name="nombres" id="nombres" class="form-control" autocomplete="off" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('apellidos', 'Apellidos', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        <input type="text" name="apellidos" id="apellidos" class="form-control" autocomplete="off" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('tipo', 'Tipo de crédito', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                       <select name="tipo" id="tipo" class="form-control">
                           <option value="Plan Contractual y Extracontractual">Plan Contractual y Extracontractual</option>
                           <option value="Plan SOAT">Plan SOAT</option>                
                           <option value="Plan Individual Básico">Plan Individual Básico</option>
                           <option value="Libre Inversión">Libre Inversión</option>
                           <option value="Emprendimientos">Emprendimientos</option>
                       </select>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('monto', 'Monto', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::number('monto', null, ['required', 'min' => '100000', 'max' => '25000000', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('plazo', 'Plazo', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::number('plazo', null, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('tasa', 'Tasa (EA)', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::number('tasa', $tasa->valor, ['required', 'step' => '0.01', 'class' => 'form-control']) }}
                    </div>
                </div>
                <div class="form-group row">
                    {{ Form::label('desembolso', 'Fecha Desembolso', ['class' => 'col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::date('desembolso', null, ['class' => 'form-control']) }}
                    </div>
                </div>

                <div style="display: none" id="divpropietario">
                    <h5>Datos del Vehiculo</h5>
                    <hr>

                    <div class="form-group row">
                        {{ Form::label('placa', 'Placa', ['class' => 'col-md-2']) }}
                        <div class="col-md-4">
                        <select name="placa" id="placa" class="form-control">
                            
                        </select>
                        </div>
                    </div>
                </div>     
                
                <h5>Datos Personales</h5>
                <hr>
                <div class="form-group row">
                    {{ Form::label('celular', 'Celular', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('celular', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('email', 'Email', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::email('email', null, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>
                <div class="form-group row">
                    {{ Form::label('ocupacion', 'Ocupación', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('ocupacion',null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('ejerciendo', 'Tiempo Ejerciendo', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('ejerciendo', null, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>
                <div class="form-group row">
                    {{ Form::label('ingresos', 'Ingresos mensuales', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::number('ingresos', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('proveniencia', 'Ingresos provenientes de', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('proveniencia', null, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('civil', 'Estado civil', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {!! Form::select('civil', ['Soltero'=>'Soltero', 'Casado'=>'Casado', 'Viudo'=>'Viudo', 'Divorciado'=>'Divorciado', 'Unión Libre'=>'Unión Libre'], null, ['class'=>'form-control']) !!}
                    </div>
                    {{ Form::label('pareja', 'Tiempo con su pareja', ['class' => 'col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('pareja', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    
                </div>

                <div class="form-group row">
                    {{ Form::label('hijos', 'Número de hijos', ['class' => 'col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::number('hijos', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('vivienda', 'Tenencia vivienda', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {!! Form::select('vivienda', ['Propia (Totalmente pagada)'=>'Propia (Totalmente pagada)', 'Propia (Crédito hipotecario vigente)'=>'Propia (Crédito hipotecario vigente)', 'Familiar'=>'Familiar', 'Arriendo'=>'Arriendo'], null, ['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group row"> 
                    {{ Form::label('estrato', 'Estrato', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {!! Form::select('estrato', ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6'], null, ['class'=>'form-control']) !!}
                    </div>
                    {{ Form::label('escolaridad', 'Escolaridad', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {!! Form::select('escolaridad', ['Ninguno'=>'Ninguno', 'Primaria'=>'Primaria', 'Secundaria'=>'Secundaria', 'Técnica o tecnológica'=>'Técnica o tecnológica', 'Pregrado'=>'Pregrado', 'Postgrado'=>'Postgrado', 'Otro'=>'Otro'], null, ['class'=>'form-control']) !!}
                    </div>
                </div>
                
                
                <h5>Referencias familiares</h5>
                <hr>
                <div class="form-group row">
                    {{ Form::label('refnom0', 'Nombre', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::text('refnom0', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('refpar0', 'Parentesco', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::text('refpar0', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('reftel0', 'Celular', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::text('reftel0', null, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('refnom1', 'Nombre', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::text('refnom1', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('refpar1', 'Parentesco', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::text('refpar1', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('reftel1', 'Celular', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::text('reftel1', null, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>

                <h5>Referencia personal</h5>
                <hr>
                <div class="form-group row">
                    {{ Form::label('refnom2', 'Nombre', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('refnom2', null, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('reftel2', 'Celular/Teléfono', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('reftel2', null, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>

                <h5>Costos asociados</h5>
                <hr>

                @foreach ($costos as $costo)
                    <div class="form-group row">
                        <label for="costo{{$costo->id}}" class="col-md-2">{{$costo->descripcion}}</label>
                        <div class="col-md-1">
                            <input type="checkbox" class="form-control" name="costos[]" id="costo{{$costo->id}}" value="{{$costo->id}}">                     
                        </div>
                        <div class="col-md-2">
                            @if ($costo->tipo == "Porcentual")
                                <label style="color: navy">+ {{number_format($costo->valor, 2, ",", ".")}}%</label> 
                            @else
                                <label style="color: navy">+ ${{number_format($costo->valor,0, ",", ".")}}</label>
                            @endif
                        </div>
                    </div>
                @endforeach
                

                <div class="form-group text-center">
                    {!! Form::button('Simular', ['type' => 'submit', 'class' => 'btn btn-lg btn-dark']) !!}
                </div>
            </form>
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" style="min-width: 700px">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Simulación del Crédito</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <table class="table table-bordered" style="font-size: larger">
                    <tr>
                        <th>Total monto</th>
                        <td id="tmonto"></td>
                    </tr>
                    <tr>
                        <th>Plazo</th>
                        <td id="lplazo"></td>
                    </tr>
                    <tr>
                        <th>Tasa (EA)</th>
                        <td id="ltasa"></td>
                    </tr>
                    <tr style="text-align: center">
                        <td style="font-size: xx-large; color: blue" colspan="2" id="cuota"></td>
                    </tr>
                </table>
                <div class="modal-footer">
                    <button type="button" onclick="solicitar();" class="btn btn-lg btn-success">Solicitar</button>
                    <button type="button" onclick="retry();" class="btn btn-lg btn-default" data-dismiss="modal">Volver a simular</button>
                </div>
               <!-- </form> -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

        var validarform = 0;

        $("#tipo").change(function () { 
            let indice = $(this)[0].selectedIndex;
            if(indice == 0 || indice == 3){
                $("#monto").attr("min", 100000);
                $("#monto").attr("max", 2500000);
            }else if(indice == 1){
                $("#monto").attr("min", 100000);
                $("#monto").attr("max", 1000000);
            }else if(indice == 2){
                $("#monto").attr("min", 100000);
                $("#monto").attr("max", 600000);
            }else if(indice == 4){
                $("#monto").attr("min", 1000000);
                $("#monto").attr("max", 5000000);
            }    
        });

        $("#cliente").autocomplete({
      		source: function( request, response ) {
                $.ajax({
                    url: "/clientes/buscar",
                    dataType: "json",
                    data: {doc: request.term},
                    success: function(data) {
                        response( $.map(data, function (item) {
                            return{
                                label : item.nro_identificacion + "_" + item.primer_nombre + " " + item.primer_apellido,
                                value : item.nro_identificacion,
                                nombres : item.primer_nombre + " " + item.segundo_nombre,
                                apellidos : item.primer_apellido + " " + item.segundo_apellido,
                                referencias : item.referencias,
                                placas: item.placas,
                                celular: item.celular,
                                email: item.email,
                                personales: item.personales
                            }
                        }) );
                    }
                });
            },
            minLength: 3,
            select: function (event, ui) {
                $("#nombres").val(ui.item.nombres);
                $("#apellidos").val(ui.item.apellidos);
                let referencias = ui.item.referencias;
                if(referencias.length > 0){
                    for (let index = 0; index < referencias.length; index++) {
                        $("#refnom" + index).val(referencias[index].nombre);
                        $("#refpar" + index).val(referencias[index].parentesco);
                        $("#reftel" + index).val(referencias[index].celular);
                    }
                }

                let placas = ui.item.placas;
                $("#placa").empty();
                if(placas.length > 0){
                    $("#divpropietario").css("display", "block"); 
                    for (const key in placas) {
                        $("#placa").append('<option value="' + placas[key].id + '">' + placas[key].placa + '</option>');
                    }
                }else{
                    $("#divpropietario").css("display", "none");
                }
                $("#celular").val(ui.item.celular);
                $("#email").val(ui.item.email);
                if(ui.item.personales != null){
                    $("#civil").val(ui.item.personales.estado_civil);
                    $("#pareja").val(ui.item.personales.tiempo_pareja);
                    $("#hijos").val(ui.item.personales.hijos);
                    $("#vivienda").val(ui.item.personales.vivienda);
                    $("#estrato").val(ui.item.personales.estrato);
                    $("#escolaridad").val(ui.item.personales.escolaridad);
                    $("#ocupacion").val(ui.item.personales.ocupacion);
                    $("#ejerciendo").val(ui.item.personales.tiempo_ocupacion);
                    $("#ingresos").val(ui.item.personales.ingresos);
                    $("#proveniencia").val(ui.item.personales.proveniencia);
                }
            }
        });

        $("#formcredito").submit(function (e) { 
            if(validarform == 0){
                e.preventDefault(); 
            
                $.ajax({
                    type: "post",
                    url: "/creditos/simulador",
                    data: $(this).serialize(),
                    dataType: "json"
                }).done( function(data) {
                    $("#load").hide();

                    $("#tmonto").text("$" + data.tmonto);
                    $("#lplazo").text(data.plazo + " meses");
                    $("#ltasa").text(data.tasa);
                    $("#cuota").html('<span style="color: black">Cuota: </span>$ ' + data.cuota);
                    $("#Modal").modal('show');

                    validarform = 1;

                }).fail( function(jqXHR, textStatus, errorThrown) {
                    $("#load").hide();
                    Swal.fire(
                        'Simulación falló',
                        textStatus,
                        'error'
                    );
                });
            }
            
        });

        function solicitar() {
            $("#formcredito").submit();
        }

        function retry() {
            validarform = 0;
        }
    </script>
@endsection