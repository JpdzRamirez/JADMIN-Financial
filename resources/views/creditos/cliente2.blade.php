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

            <form action="/mis_creditos/solicitar" method="POST" id="formcredito" accept-charset="UTF-8">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <input type="hidden" name="rol" value="cliente">

                <div class="form-group row">
                    {{ Form::label('tipo', 'Tipo de crédito', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                       <select name="tipo" id="tipo" class="form-control">
                            <option value="" disabled selected>Seleccionar plan</option>
                           @if ($condicion == "Propietario")
                                <option value="Plan Contractual y Extracontractual">Plan Contractual y Extracontractual</option>
                                <option value="Plan SOAT">Plan SOAT</option>
                                <option value="Nanocrédito">Nanocrédito</option>
                                <option value="Libre Inversión">Libre Inversión</option>
                                <option value="Emprendimientos">Emprendimientos</option>
                            @elseif($condicion == "Conductor")
                                <option value="Nanocrédito">Nanocrédito</option>
                            @elseif($condicion == "Particular")
                                <option value="Libre Inversión">Libre Inversión</option>
                                <option value="Nanocrédito">Nanocrédito</option>
                           @endif
                       </select>
                    </div>
                    <div class="col-md-2">                    
                        <a href="#" class="btn btn-sm btn-primary open-modal" data-toggle="modal" data-target="#ModalPlanes"><i class="fa fa-question-circle-o" aria-hidden="true"></i> Ver Planes</a>
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('monto', 'Monto', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::number('monto', null, ['required', 'min' => '500000', 'max' => '25000000', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('plazo', 'Plazo', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::number('plazo', null, ['required', 'min' => '2', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('tasa', 'Tasa (EA)', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-6 col-xs-10">
                        {{ Form::number('tasa', $tasa->valor, ['required', 'readonly', 'class' => 'form-control']) }}
                    </div>
                </div>

                @if ($cliente->condicion == "Propietario")
                    <div style="display: none" id="divpropietario">
                        <h5>Datos del Vehiculo</h5>
                        <hr>

                        <div class="form-group row">
                            {{ Form::label('placa', 'Placa', ['class' => 'col-md-2']) }}
                            <div class="col-md-4">
                                <select name="placa" id="placa" class="form-control">
                                    @foreach ($cliente->placas as $placa)
                                        <option value="{{$placa->id}}">{{$placa->placa}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <h5>Datos Personales</h5>
                <hr>
                <div class="form-group row">
                    {{ Form::label('celular', 'Celular', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('celular', $cliente->celular, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('email', 'Email', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::email('email', $cliente->email, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>
                <div class="form-group row">
                    {{ Form::label('civil', 'Estado civil', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {!! Form::select('civil', ['Soltero'=>'Soltero', 'Casado'=>'Casado', 'Viudo'=>'Viudo', 'Divorciado'=>'Divorciado', 'Unión Libre'=>'Unión Libre'], $cliente->personales->estado_civil, ['class'=>'form-control']) !!}
                    </div>
                    {{ Form::label('pareja', 'Tiempo con su pareja', ['class' => 'col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::text('pareja', $cliente->personales->tiempo_pareja, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('hijos', 'Número de hijos', ['class' => 'col-md-1']) }}
                    <div class="col-md-3">
                        {{ Form::number('hijos', $cliente->personales->hijos, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('vivienda', 'Tenencia vivienda', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {!! Form::select('vivienda', ['Propia (Totalmente pagada)'=>'Propia (Totalmente pagada)', 'Propia (Crédito hipotecario vigente)'=>'Propia (Crédito hipotecario vigente)', 'Familiar'=>'Familiar', 'Arriendo'=>'Arriendo'], $cliente->personales->vivienda, ['class'=>'form-control']) !!}
                    </div>
                    {{ Form::label('estrato', 'Estrato', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {!! Form::select('estrato', ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6'], $cliente->personales->estrato, ['class'=>'form-control']) !!}
                    </div>
                    {{ Form::label('escolaridad', 'Escolaridad', ['class' => 'label-required col-md-1']) }}
                    <div class="col-md-3">
                        {!! Form::select('escolaridad', ['Ninguno'=>'Ninguno', 'Primaria'=>'Primaria', 'Secundaria'=>'Secundaria', 'Técnica o tecnológica'=>'Técnica o tecnológica', 'Pregrado'=>'Pregrado', 'Postgrado'=>'Postgrado', 'Otro'=>'Otro'], $cliente->personales->escolaridad, ['class'=>'form-control']) !!}
                    </div>
                </div>
                
                <div class="form-group row">
                    {{ Form::label('ocupacion', 'Ocupación', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('ocupacion', $cliente->personales->ocupacion, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('ejerciendo', 'Tiempo Ejerciendo', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('ejerciendo', $cliente->personales->tiempo_ocupacion, ['required', 'class' => 'form-control']) }}
                    </div>
                </div>

                <div class="form-group row">
                    {{ Form::label('ingresos', 'Ingresos mensuales', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::number('ingresos', $cliente->personales->ingresos, ['required', 'class' => 'form-control']) }}
                    </div>
                    {{ Form::label('proveniencia', 'Ingresos provenientes de', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-4">
                        {{ Form::text('proveniencia', $cliente->personales->proveniencia, ['required', 'class' => 'form-control']) }}
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
                        <div class="col-md-2 col-xs-10">
                            <input class="form-control" type="checkbox" name="costos[]" id="costo{{$costo->id}}" value="{{$costo->id}}" readonly>                               
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
        <div class="modal-dialog" style="min-width: 60%">
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
            </div>
        </div>
    </div>
    <div id="ModalPlanes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="min-width: 60%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Planes de Crédito</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered" style="font-size: medium; width: 90%; margin-left: 5%; text-align: center">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Plan Contractual y Extracontractual</th>
                            <th>Plan SOAT</th>
                            <th>Nanocrédito</th>
                            <th>Libre Inversión</th>
                            <th>Emprendimientos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Monto mínimo</th>
                            <td>500.000</td>
                            <td>100.000</td>
                            <td>50.000</td>
                            <td>500.000</td>
                            <td>1.000.000</td>
                        </tr>
                        <tr>
                            <th>Monto máximo</th>
                            <td>2.500.000</td>
                            <td>1.000.000</td>
                            <td>300.000</td>
                            <td>2.500.000</td>
                            <td>5.000.000</td>
                        </tr>
                        <tr>
                            <th>Plazo</th>
                            <td>Hasta 10 meses</td>
                            <td>Hasta 8 meses</td>
                            <td>Hasta 8 meses</td>
                            <td>Hasta 10 meses</td>
                            <td>Hasta 12 meses</td>
                        </tr>
                        <tr>
                            <th>Soporte</th>
                            <td colspan="5">7.000 + IVA</td>
                        </tr>
                        <tr>
                            <th>Seguro</th>
                            <td colspan="5">13.000 + IVA</td>
                        </tr>
                        <tr>
                            <th>Inclusión financiera</th>
                            <td colspan="4">No aplica</td>
                            <td>12%</td>
                        </tr>
                        <tr>
                            <th>Comisión</th>
                            <td>1% para mayores a 1.500.000</td>
                            <td colspan="2">No aplica</td>
                            <td>1% para mayores a 1.500.000</td>
                            <td>No aplica</td>
                        </tr>
                        <tr>
                            <th>Procesamiento de datos Plataforma</th>
                            <td colspan="5">7.500 + IVA</td>
                        </tr>
                        <tr>
                            <th>Soporte 2</th>
                            <td colspan="5">5.000 + IVA</td>
                        </tr>
                        <tr>
                            <th>Consultas Centrales</th>
                            <td colspan="5">8.000 + IVA</td>
                        </tr>
                    </tbody>              
                </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

        var validarform = 0;
        var planes = [{nombre: "Plan Contractual y Extracontractual", montomin: 500000, montomax:2500000, plazo:10, divprop: "block", costo1: true, costo2: true, costo3: false, costo4: false, costo5: false, costo6: false, costo7: false},
        {nombre: "Libre Inversión", montomin: 500000, montomax:2500000,  plazo:12, divprop: "none", costo1: true, costo2: true, costo3: false, costo4: false, costo5: false, costo6: false, costo7: false},
        {nombre: "Plan SOAT", montomin: 100000, montomax:1000000, plazo:8, divprop: "true", costo1: true, costo2: true, costo3: false, costo4: false, costo5: false, costo6: false, costo7: false},
        {nombre: "Nanocrédito", montomin: 200000, montomax:{{$cupo}}, plazo:5, divprop: "none", costo1: false, costo2: true, costo3: false, costo4: false, costo5: false, costo6: false, costo7: false},
        {nombre: "Emprendimientos", montomin: 1000000, montomax:5000000, plazo:12, divprop: "none", costo1: true, costo2: true, costo3: false, costo4: true, costo5: false, costo6: false, costo7: false}];

        $("#tipo").change(function () { 
            let indice = $(this).val();
            for (const key in planes) {
                if(planes[key].nombre == indice){
                    $("#monto").attr("min", planes[key].montomin);
                    $("#monto").attr("max", planes[key].montomax);
                    $("#plazo").attr("max", planes[key].plazo);
                    $("#costo1").prop('checked', planes[key].costo1);
                    $("#costo2").prop('checked', planes[key].costo2);
                    $("#costo3").prop('checked', planes[key].costo3);
                    $("#costo4").prop('checked', planes[key].costo4);
                    $("#costo5").prop('checked', planes[key].costo5);
                    $("#costo6").prop('checked', planes[key].costo6);
                    $("#costo7").prop('checked', planes[key].costo7);
                    $("#divpropietario").css("display", planes[key].divprop);
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
                    let tasa = (Math.pow(1+(data.tasa/100), 1/12)-1)*100;
                    $("#ltasa").text(data.tasa + " (" + tasa.toFixed(2) + " MV)");
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

        $('input[type="checkbox"]').on('click keyup keypress keydown', function (event) {
            if($(this).is('[readonly]')) { return false; }
        });

        $("#monto").on('keyup', function (event) {
            let valor = $(this).val();
            if(valor > 1500000){
                $("#costo3").prop('checked', true);
            }else{
                $("#costo3").prop('checked', false);
            }
        });

        function solicitar() {
            $("#formcredito").submit();
        }

        function retry() {
            validarform = 0;
        }

        $(document).ready(function () {
            @if(count($cliente->referencias) > 0)
                $('#refnom0').val("{{ $cliente->referencias[0]->nombre }}");
                $('#refpar0').val("{{ $cliente->referencias[0]->parentesco }}");
                $('#reftel0').val("{{ $cliente->referencias[0]->celular }}");
                $('#refnom1').val("{{ $cliente->referencias[1]->nombre }}");
                $('#refpar1').val("{{ $cliente->referencias[1]->parentesco }}");
                $('#reftel1').val("{{ $cliente->referencias[1]->celular }}");
                $('#refnom2').val("{{ $cliente->referencias[2]->nombre }}");
                $('#reftel2').val("{{ $cliente->referencias[2]->celular }}");
            @endif

            @if($cliente->personales != null)
                $('#refnom0').val("{{ $cliente->personales->hijos }}");
                $('#refpar0').val("{{ $cliente->personales->estado_civil }}");
                $('#reftel0').val("{{ $cliente->personales->pareja }}");
                $('#refnom1').val("{{ $cliente->personales->tiempo_pareja }}");
                $('#refpar1').val("{{ $cliente->personales->ocupacion }}");
                $('#reftel1').val("{{ $cliente->personales->tiempo_ocupacion }}");
                $('#refnom2').val("{{ $cliente->personales->estrato }}");
                $('#reftel2').val("{{ $cliente->personales->vivienda }}");
                $('#refnom2').val("{{ $cliente->personales->escolaridad }}");
                $('#reftel2').val("{{ $cliente->personales->proveniencia }}");
            @endif
        });

        
    </script>
@endsection