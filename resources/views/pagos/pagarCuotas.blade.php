@extends('layouts.logeado')

@section('style')
    <style>
        .row label{
            padding: 1rem .75rem;
        }
    </style>
@endsection
@section('sub_title', 'Pagar Cuotas')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if($errors->first('sql') != null)
                <div class="alert alert-danger" style="margin:10px 0">
                    <h6>{{$errors->first('sql')}}</h6>
                </div>				
            @endif

            <h4 style="color: navy">Cliente</h4>
            <hr>
                <h5 class="form-control col-md-6 col-xs-12">{{ ucfirst($cliente->primer_nombre) }} {{ ucfirst($cliente->segundo_nombre) }} {{ ucfirst($cliente->primer_apellido) }} {{ ucfirst($cliente->segundo_apellido) }}</h5>
                <h5 class="form-control col-md-6 col-xs-12">{{ number_format($cliente->nro_identificacion, 0, ",", ".") }} </h5>         
            <br>
            @php
                $total = 0;
            @endphp
            <div class="row form-group">
                <label class="col-md-1"># Crédito</label>
                <h5 class="col-md-2 form-control">{{ $credito->numero }}</h5>
                <label class="col-md-1 text-center">Destino</label>
                <h5 class="col-md-4 form-control">{{ $credito->tipo }}</h5>
                <label class="col-md-2 text-center">Factura</label>
                <h5 class="col-md-2 form-control">{{ $credito->factura->prefijo }} {{ $credito->factura->numero }}</h5>
            </div>
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th># Cuota</th>
                        <th>Valor</th>
                        <th>Fecha vencimiento</th>
                        <th>Interés Mora</th>
                        <th>Total cuota</th>
                        <th>Pagar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($credito->cuotas as $cuota)
                        @if($cuota->estado == "Vigente")
                            <tr style="background-color: lightblue">
                        @elseif($cuota->estado == "Vencida")
                            <tr style="background-color: lightcoral">
                        @else          
                            <tr>       
                        @endif
                        @php
                            $pago = $cuota->saldo_capital + $cuota->saldo_interes+$cuota->saldo_mora;
                            $total = $total + $pago;
                        @endphp
                            <td>{{ $cuota->ncuota }}</td>
                            <td>${{ number_format($cuota->saldo_capital + $cuota->saldo_interes, 0, ",", ".") }}</td>
                            <td>{{ $cuota->fecha_vencimiento }}</td>
                            <td>${{ number_format($cuota->saldo_mora, 0, ",", ".") }}</td>
                            <td>${{ number_format($pago, 0, ",", ".") }}</td>
                            <td style="background-color: white">
                                <input type="text" name="pagar[]" id="cuo-{{$cuota->id}}" value="{{$pago}}" readonly>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form action="/pagos/registrar_cuotas" method="post" id="formpago">
                <input type="hidden" value="{{$credito->id}}" name="credito" id="credito">
                <input type="hidden" value="{{$cliente->id}}" name="cliente" id="cliente">
                <input type="hidden" value="{{csrf_token()}}" name="_token">
                <input type="hidden" name="cuotas" id="cuotas">

                <div class="row form-group" style="margin-top: 30px">
                    <label for="valor" class="col-md-2 ">Forma de pago:</label>
                    <div class="col-md-4 col-xs-10" style="padding: 0">
                        <select name="forma" id="forma" class="form-control">
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
                    <label for="valor" class="col-md-4 text-right" style="font-size: x-large; color: navy">Total a Pagar:</label>
                    <div class="col-md-3">
                        <input type="number" style="font-size: x-large; margin-top: 1rem" name="pagov" id="pagov"  readonly required class="form-control" lang="en" value="{{$total}}">
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
                    <button type="submit" form="formpago" class="btn btn-success">Confirmar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var total = {{$total}};
        var cuotas = [];
        var formater = new Intl.NumberFormat("es-CO", {maximumFractionDigits: 0});
        var formater2 = new Intl.NumberFormat("en-US", {maximumFractionDigits: 2});
        var ajustado =  parseInt(formater.format(total).replaceAll(".", ""));
        var ajuste = formater2.format(ajustado - total);

        $(document).ready(function () {
            $("#load").hide();
            let inputs = $("input[id|='cuo']");
            for (const key in inputs) {
                if(inputs[key].id != undefined){
                    cuotas.push({ide: inputs[key].id, original: inputs[key].value, valor: inputs[key].value});
                    $("#"+inputs[key].id).val("$"+formater.format(inputs[key].value));
                }            
            } 
            $("#pagov").val(ajustado);
            $("#pago").val(ajustado);
            $("#ajuste").val(ajuste);
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
                $("#ajuste").val(ingresado-total);
                for (const key in cuotas) {
                    if(ingresado > parseFloat(cuotas[key].original)){
                        $("#"+cuotas[key].ide).val("$"+formater.format(cuotas[key].original));
                        ingresado = ingresado - cuotas[key].valor;
                    }else{
                        $("#"+cuotas[key].ide).val("$"+formater.format(ingresado));
                        cuotas[key].valor = ingresado;
                        ingresado = 0;
                    }
                }
            }else if(total - ingresado > 100){  
                for (const key in cuotas) {
                    if(ingresado > parseFloat(cuotas[key].original)){
                        $("#"+cuotas[key].ide).val("$"+formater.format(cuotas[key].valor));
                        ingresado = ingresado - cuotas[key].valor;
                    }else{
                        $("#"+cuotas[key].ide).val("$"+formater.format(ingresado));
                        cuotas[key].valor = ingresado;
                        ingresado = 0;
                    }
                }
                $("#ajuste").val(formater2.format(0));
            }else if(total - ingresado <= 100 && total - ingresado > 0){
                $("#ajuste").val(ingresado-total);
            }
            $("#pago").val(aux);
            $("#pagov").val(aux);
            $("#btnmod").attr("disabled", false);
            $("#btnaply").attr("disabled", true);
            $("#btnsubmit").attr("disabled", false);
            $("#pagov").attr("readonly", true);
        }

        $("#formpago").submit(function (e) {
            e.preventDefault();
            Swal.fire({
                title: '<strong>Enviando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
            $("#cuotas").val(JSON.stringify(cuotas));
            $.ajax({
                type: "post",
                url: $(this).attr("action"),
                data: $(this).serialize(),
                dataType: "json"
            }).done(function(data){
                Swal.close();
                    if(data.respuesta == "success"){
                        Swal.fire({
                            title: "Recibo realizado",
                            text: "El recibo fue emitido exitosamente",
                            icon: data.respuesta,
                            confirmButtonText: 'OK',
                        }).then((result) => {
                            window.open("/pagos/" + data.msj + "/descargar_recibo");
                            location.href = "/pagos/registrar";
                        });
                    }else{
                        Swal.fire(
                            data.msj,
                            "La emisión del recibo falló",
                            data.respuesta
                        );
                    }
            }).fail(function(jqXHR, textStatus, errorThrown){
                Swal.fire(
                    "error",
                    "La emisión del recibo falló",
                    errorThrown
                );
            });
        });

    </script>
@endsection
