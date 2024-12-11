@extends('layouts.logeado')

@section('style')
    <style>
        .row label{
            padding: 1rem .75rem;
        }
    </style>
@endsection
@section('sub_title', 'Pagar Cuota')

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
                <h5 class="form-control col-md-6 col-xs-12">{{ ucfirst($cuota->credito->cliente->primer_nombre) }} {{ ucfirst($cuota->credito->cliente->segundo_nombre) }} {{ ucfirst($cuota->credito->cliente->primer_apellido) }} {{ ucfirst($cuota->credito->cliente->segundo_apellido) }}</h5>
                <h5 class="form-control col-md-6 col-xs-12">{{ number_format($cuota->credito->cliente->nro_identificacion, 0, ",", ".") }} </h5>
                <h6 class="form-control col-md-6 col-xs-12">{{ $cuota->credito->cliente->condicion }}</h6>          
            <br>

            <h4 style="color: navy">Cuota</h4>
            <hr>
            <div class="row form-group">
                <label for="valor" class="col-md-2">Número de Cuota:</label>
                <h5 class="form-control col-md-4">
                    @if ($cuota->descripcion == null)
                        {{ $cuota->ncuota }}
                    @else
                        {{ $cuota->descripcion }}
                    @endif
                </h5>
                <label for="valor" class="col-md-2 text-center">Fecha de Vencimiento:</label>
                <h5 class="form-control col-md-4 col-xs-10">{{ $cuota->fecha_vencimiento }}</h5>  
            </div>

            <div class="row form-group">
                <label for="valor" class="col-md-2">Valor:</label>
                <h5 class="form-control col-md-4">${{ number_format($cuota->saldo_capital + $cuota->saldo_interes, 2, ",", ".") }}</h5>
                <label for="valor" class="col-md-2 text-center">Días de Mora:</label>
                <h5 class="form-control col-md-4 col-xs-10">{{ $cuota->mora }}</h5>
            </div>

            <div class="row form-group">
                <label for="valor" class="col-md-2">Interés de Mora:</label>
                <h5 class="form-control col-md-4 col-xs-10">${{ number_format($cuota->saldo_mora, 2, ",", ".") }}</h5>
            </div>

            <form action="/pagos/pagar_cuota" method="post" id="formpago">
                <input type="hidden" name="cuota" id="cuota" value="{{ $cuota->id }}">
                <input type="hidden" name="cliente" id="cliente" value="{{ $cuota->credito->users_id }}">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="row form-group">
                    <label for="valor" class="col-md-2">Tipo de pago:</label>
                    <div class="col-md-4 form-control">
                        <input type="radio" name="tipo" value="Completo" checked> Completo
                    </div>
                    <div class="col-md-4 form-control">
                        <input type="radio" name="tipo" value="Parcial"> Parcial
                    </div>
                </div>
                <div class="row form-group">
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
                    <label for="observaciones" class="col-md-2 ">Observaciones:</label>
                    <div class="col-md-4 col-xs-10" style="padding: 0">
                       <textarea name="observaciones" id="observaciones" class="form-control" rows="5"></textarea>
                    </div>
                </div>    
                <div class="row form-group">
                    <label for="valor" class="col-md-5 text-right" style="font-size: x-large; color: navy">Total a Pagar:</label>
                    <div class="col-md-7">
                        <input type="number" style="font-size: x-large; margin-top: 1rem" name="pago" id="pago" readonly required class="form-control" step="0.01" max="{{$cuota->saldo_capital + $cuota->saldo_interes+$cuota->saldo_mora}}" value="{{$cuota->saldo_capital + $cuota->saldo_interes+$cuota->saldo_mora}}">
                    </div>
                </div>

                <div class="text-center" style="margin-bottom: 50px">
                    <button type="button" class="btn btn-dark" onclick="confirmarPago();">Efectuar Pago</button>
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
        var total = {{$cuota->saldo_capital + $cuota->saldo_interes + $cuota->saldo_mora}};
        $('input[type=radio][name=tipo]').change(function () {
            if($(this).val() == "Parcial"){
                $("#pago").prop("readonly", false);
            }else{
                $("#pago").val(total);
                $("#pago").prop("readonly", true);
            }
        });

        function confirmarPago() {
            let pago = $('#pago').val();
            if(pago > total){
                Swal.fire(
                    'Error',
                    'El valor a pagar es mayor que el valor de la cuota',
                    'error'
                );
            }else{
                $('#txtvalor').text(new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP'}).format(pago));
                $('#txtforma').text("Por " + $("#forma option:selected" ).text());
                $('#Modal').modal('show');
            } 
        }
    </script>
@endsection