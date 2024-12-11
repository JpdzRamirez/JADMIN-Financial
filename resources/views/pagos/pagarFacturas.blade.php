@extends('layouts.logeado')

@section('style')
    <style>
        .row label{
            padding: 1rem .75rem;
        }
    </style>
@endsection
@section('sub_title', 'Pagar Facturas')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <ul class="nav nav-pills" id="myTab" role="tablist">
				<li class="nav-item" role="presentation">
				  <a class="nav-link active" id="cobrar-tab" data-toggle="pill" href="#cobrar" role="tab" aria-controls="cobrar" aria-selected="true">Cobrar</a>
				</li>
				<li class="nav-item" role="presentation">
				  <a class="nav-link" id="asiento-tab" data-toggle="pill" href="#asiento" role="tab" aria-controls="asiento" aria-selected="false">Asiento contable</a>
				</li>
			</ul>
            <div class="tab-content" id="myTabContent" style="min-height: 200px">
                <div class="tab-pane fade show active" id="cobrar" role="tabpanel" aria-labelledby="cobrar-tab">
                    @if($errors->first('sql') != null)
                        <div class="alert alert-danger" style="margin:10px 0">
                            <h6>{{$errors->first('sql')}}</h6>
                        </div>				
                    @endif
                    
                    <h4 style="color: navy">Cliente</h4>
                    <hr>
                    <h5 class="form-control col-md-6 col-xs-12">{{ ucfirst($tercero->nombre) }} </h5>
                    <h5 class="form-control col-md-6 col-xs-12">{{ number_format($tercero->documento, 0, ",", ".") }} </h5>         
                    <br>
                    @php
                        $total = 0;
                    @endphp
                    
                    <h4 style="color: navy">Facturas</h4>
                    <hr>
                    <table class="table table-bordered" style="table-layout: fixed">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Valor</th>
                                <th>Fecha vencimiento</th>
                                <th>Interés Mora</th>
                                <th>Total cuota</th>
                                <th>Pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facturas as $factura)
                                @if($factura->mora > 0)
                                    <tr style="background-color: lightcoral">
                                @else          
                                    <tr style="background-color: lightblue">
                                @endif
                                @php
                                    $pago = $factura->cobrar + $factura->mora;
                                    $total = $total + $pago;
                                @endphp
                                    <td>{{ $factura->prefijo }} {{ $factura->numero }}</td>
                                    <td>${{ number_format($factura->cobrar, 0, ",", ".") }}</td>
                                    <td>{{ $factura->vencimiento }}</td>
                                    <td>${{ number_format($factura->mora, 0, ",", ".") }}</td>
                                    <td>${{ number_format($pago, 0, ",", ".") }}</td>
                                    <td style="background-color: white">
                                        <input type="text" name="pagar[]" id="fac-{{$factura->id}}" value="{{$pago}}" readonly>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    <form action="/pagos/registrar_facturas" method="post" id="formpago">
                        <input type="hidden" value="{{$tercero->id}}" name="cliente" id="cliente">
                        <input type="hidden" value="{{csrf_token()}}" name="_token">
                        <input type="hidden" name="facturas" id="facturas">

                        <div class="row form-group">
                            <label for="retefuente" class="col-md-2">Retefuente</label>
                            <div class="col-md-4 col-xs-10" style="padding: 0">
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
                            <div class="col-md-4 col-xs-10" style="padding: 0">
                                <select name="reteica" id="reteica" class="form-control">
                                    <option value="">No</option>
                                    @foreach ($reteicas as $reteica)
                                        <option value="{{$reteica->id}}">{{$reteica->concepto}}</option>
                                    @endforeach
                                </select>
                            </div>	
                        </div>
                        <div class="row form-group">
                            <label for="reteiva" class="col-md-2">Reteiva</label>
                            <div class="col-md-4 col-xs-10" style="padding: 0">
                                <select name="reteiva" id="reteiva" class="form-control">
                                    <option value="">No</option>
                                    @foreach ($reteivas as $reteiva)
                                        <option value="{{$reteiva->id}}">{{$reteiva->concepto}}</option>
                                    @endforeach
                                </select>
                            </div>	
                        </div>
                        <div id="extras" style="display: none">
                            @foreach ($extras as $extra)
                                <div class="form-group row" style="padding: 0">
                                    <label for="{{$extra->id}}" class="col-md-2">{{$extra->nombre}}</label>
                                    <div class="col-md-4 col-xs-10" style="padding: 0">
                                        <input type="checkbox" name="extras" value="{{$extra->id}}" class="form-control">
                                    </div>	
                                </div>
                            @endforeach
                        </div>

                        <div class="row form-group">
                            <label for="forma" class="col-md-2">Forma de pago:</label>
                            <div class="col-md-4 col-xs-10" style="padding: 0">
                                <select name="forma" id="forma" class="form-control">
                                    <option value="0" selected disabled>Seleccionar</option>
                                    @foreach ($formas as $forma)
                                        <option value="{{$forma->id}}">{{$forma->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>  
                        <div class="row form-group">
                            <label for="valor" class="col-md-2 ">Ajuste:</label>
                            <div class="col-md-4 col-xs-10" style="padding: 0">
                                <input type="number" class="form-control" value="0" step="0.01" name="ajuste" id="ajuste" readonly>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="observaciones" class="col-md-2 ">Observaciones:</label>
                            <div class="col-md-4 col-xs-10" style="padding: 0">
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="5"></textarea>
                            </div>
                        </div>    
                        <div class="row form-group">
                            <label for="pago" class="col-md-4 text-right" style="font-size: x-large; color: navy">Total a Pagar:</label>
                            <div class="col-md-3">
                                <input type="number" style="font-size: x-large; margin-top: 1rem" name="pagov" id="pagov" readonly required class="form-control" lang="en" value="{{$total}}">
                                <input type="hidden" name="pago" id="pago" value="{{$total}}">
                            </div>
                            <div class="col-md-3">
                                <button type="button" onclick="modificar();" id="btnmod" class="btn btn-warning" style="margin-top: 1rem">Modificar</button>
                                <button type="button" onclick="aplicar();" id="btnaply" class="btn btn-success" style="margin-top: 1rem" disabled>Aplicar</button>
                            </div>
                        </div>

                        <div class="text-center" style="margin-bottom: 50px">
                            <button type="button" id="btnsubmit" class="btn btn-dark" onclick="confirmarPago();">Efectuar Pago</button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="asiento" role="tabpanel" aria-labelledby="asiento-tab">
                    <br>
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
                </div>
            </div>
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirmar Pago</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row form-group text-center">
                        <h4 class="col-md-12">El pago a efectuar es: </h4>                         
                    </div>
                    <div class="row form-group text-center">
                        <h2 id="txtvalor" class="col-md-12" style="color: navy"></h2>                         
                    </div>
                    <hr>
                    <div class="row form-group text-center">
                        <h2 id="txtforma" class="col-md-12"></h2>                         
                    </div>				
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="enviar();">Confirmar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var total = {{$total}};
        var facturas = [];
        var formater = new Intl.NumberFormat("es-CO", {maximumFractionDigits: 0});
        var movimientos = new Array();
        var forma;
        var retenciones;
        var valor;
        var formater2 = new Intl.NumberFormat("en-US", {maximumFractionDigits: 2});
        var ajustado = parseInt(formater.format(total).replaceAll(".", ""));
        var ajuste = formater2.format(ajustado - total);

        $(document).ready(function () {
            let inputs = $("input[id|='fac']");
            for (const key in inputs) {
                if(inputs[key].id != undefined){
                    facturas.push({ide: inputs[key].id, original: inputs[key].value, valor: inputs[key].value});
                    $("#"+inputs[key].id).val("$"+formater.format(inputs[key].value));
                }            
            }
            $("#pagov").val(ajustado);
            $("#pago").val(ajustado);
            $("#ajuste").val(ajuste);
        });

        $("#forma").change(function (e) {    
            if($("#pago").val() > 0){
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
                data: {_token:'{{csrf_token()}}', facturas:JSON.stringify(facturas), forma: $(this).val(), 
                        valor: $("#pago").val(), exicas:extras, retefuente:$("#retefuente").val(), 
                        reteica:$("#reteica").val(),reteiva:$("#reteiva").val(), ajuste:$("#ajuste").val()},
                dataType: "json"
                }).done(function (data) {
                    $("#tbasiento").empty();
                        if(data.asiento.length > 0){
                            valor = $("#pago").val();
                            for (const key in data.asiento) {
                                let fila = '<tr><td>' + data.asiento[key].codigo + '</td><td>' + data.asiento[key].nombre + '</td>';
                                if (data.asiento[key].tipo == "Débito") {
                                    fila = fila + '<td>' + formater2.format(data.asiento[key].valor) + '</td><td>0</td>';
                                }else{
                                    fila = fila + '<td>0</td><td>' + formater2.format(data.asiento[key].valor) + '</td>';
                                }
                                $("#tbasiento").append(fila);
                            }
                            movimientos = data.asiento;
                            retenciones = data.retenciones;
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
        
        function confirmarPago() {
            let pago = parseInt($('#pago').val());

            $('#txtvalor').text(formater.format(pago));
            $('#txtforma').text("Por " + $("#forma option:selected").text());
            $('#Modal').modal('show');
        }

        function modificar() {
            $("#pagov").val(ajustado);
            $("#pago").val(ajustado);
            $("#pagov").attr("readonly", false);
            $("#btnmod").attr("disabled", true);
            $("#btnaply").attr("disabled", false);
            $("#btnsubmit").attr("disabled", true);
        }

        function aplicar() {
            let ingresado = parseFloat($("#pagov").val());
            let aux = ingresado;
            if(ingresado > total){
                $("#ajuste").val(formater2.format(ingresado-total));
            }else if(total - ingresado > 100){    
                for (const key in facturas) {
                    if(ingresado > parseFloat(facturas[key].original)){
                        $("#"+facturas[key].ide).val("$"+formater.format(facturas[key].valor));
                        ingresado = ingresado - facturas[key].valor;
                    }else{
                        $("#"+facturas[key].ide).val("$"+formater.format(ingresado));
                        facturas[key].valor = ingresado;
                        ingresado = 0;
                    }
                }
                $("#ajuste").val(formater2.format(0));
            }else if(total - ingresado <= 100 && total - ingresado > 0){
                $("#ajuste").val(formater2.format(ingresado-total));
            }

            $("#pago").val(aux);
            $("#pagov").val(aux);
            $("#btnmod").attr("disabled", false);
            $("#btnaply").attr("disabled", true);
            $("#btnsubmit").attr("disabled", false);
            $("#pago").attr("readonly", true);
            if($("#forma").val() != undefined){
                $("#forma").trigger("change");
            }
        }

        function enviar() {
			if(movimientos.length > 0 && valor == $("#pago").val()){
                Swal.fire({
                    title: '<strong>Enviando...</strong>',
                    html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                    showConfirmButton: false,
                });
				$.ajax({
                    type: "post",
                    url: "/contabilidad/facturas/ventas/registrar_cobro",
                    data: {_token:'{{csrf_token()}}', facturas:JSON.stringify(facturas), movimientos: JSON.stringify(movimientos), 
							forma: $("#forma").val(), cobrar: {{$total}}, retenciones: retenciones,
							valor: $("#pago").val(), observaciones: $("#observaciones").val()},
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
                            location.href = "/pagos/registrar";
                        });
                    }else{
                        Swal.fire(
                            data.msj,
                            "La emisión del recibo falló",
                            data.respuesta
                        );
                    }
                }).fail(function () {  
                    Swal.fire(
                        "error",
                        "La emisión del recibo falló",
                        errorThrown
                    );
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