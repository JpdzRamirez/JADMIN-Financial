@extends('layouts.logeado')
@section('style')
    <style>
        label{
            margin-top: 1rem;
        }
    </style>
@endsection
@section('sub_title', 'Pagar Crédito # ' . $credito->numero)

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <h4 style="color: navy">Cliente</h4>
            <hr>
                <h5 class="form-control col-md-6 col-xs-12">{{ ucfirst($credito->cliente->primer_nombre) }} {{ ucfirst($credito->cliente->segundo_nombre) }} {{ ucfirst($credito->cliente->primer_apellido) }} {{ ucfirst($credito->cliente->segundo_apellido) }}</h5>
                <h5 class="form-control col-md-6 col-xs-12">{{ number_format($credito->cliente->nro_identificacion, 0, ",", ".") }} </h5>
                <h6 class="form-control col-md-6 col-xs-12">{{ $credito->cliente->condicion }}</h6>          
            <br>

            <h4 style="color: navy">Crédito</h4>
            <hr>
            <div class="row form-group">
                <label for="valor" class="col-md-2">Saldo Insoluto:</label>
                <h5 class="form-control col-md-4">{{ number_format($credito->insoluto, 2, ",", ".") }}</h5>
            </div>

            <div class="row form-group">
                <label for="valor" class="col-md-2">Interés Ordinario:</label>
                <h5 class="form-control col-md-4">{{ number_format($credito->interes, 2, ",", ".") }}</h5>
            </div>

            <div class="row form-group">
                <label for="valor" class="col-md-2">Interés de Mora:</label>
                <h5 class="form-control col-md-4 col-xs-10">{{ number_format($credito->mora, 2, ",", ".") }}</h5>
            </div>

            <form action="/pagos/pagar_credito" method="post" id="formpago">
                <input type="hidden" name="credito" id="credito" value="{{ $credito->id }}">
                <input type="hidden" name="cliente" id="cliente" value="{{ $credito->users_id }}">
                <input type="hidden" name="_token" value="{{csrf_token()}}">

                <div class="row form-group">
                    <label for="descuento" class="col-md-2">Descuento:</label>
                    <h5 class="col-md-4 col-xs-10" style="padding: 0">
                        <input type="number" name="descuento" id="descuento" step="0.01" value="0" min="0" class="form-control">
                    </h5>
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
                        <input type="number" style="font-size: x-large; margin-top: 1rem" name="pago" id="pago" readonly required class="form-control" step="0.01" max="{{ $credito->deuda }}" value="{{ $credito->deuda }}">
                    </div>
                </div>

                <div class="text-center" style="margin-bottom: 50px; margin-top: 30px">
                    <button type="button" onclick="confirmarPago();" class="btn btn-dark">Efectuar Pago</button>
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

        $(document).ready(function () {
            $("html,body").animate({scrollTop: $("#formpago").offset().top}, 2000);	
        });

        var total = {{ $credito->deuda }};

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