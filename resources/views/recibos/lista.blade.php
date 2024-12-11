@extends('layouts.logeado')

@section('sub_title', 'Recibos')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row mb-1">
                <div class="col-md-2 offset-md-10">
                    <select name="envyear" id="envyear" class="form-control" form="formrecibos">
                        @for ($i = 2022; $i <= $fecha->year; $i++)
                            @if ($i == $envyear)
                                <option value="{{$i}}" selected>{{$i}}</option>
                            @else   
                                <option value="{{$i}}">{{$i}}</option>                     
                            @endif
                        @endfor        
                    </select>
                </div>
            </div>
			<form action="/contabilidad/recibos/filtrar" method="GET" id="formrecibos"></form>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Número</th>
                            <th>Fecha</th>
                            <th>Facturas</th>
                            <th>Tercero</th>
                            <th>Valor</th>
                            <th>Estado</th>       
						</tr>
						<tr>
							<th><input type="number" name="numero" class="form-control filt" id="numero" form="formrecibos"></th>
							<th><input type="text" class="form-control" name="fecha" id="fecha" form="formrecibos" autocomplete="off"></th>
							<th><input type="number" name="facturas" class="form-control filt" id="facturas" form="formrecibos"></th>
                            <th><input type="text" name="tercero" class="form-control filt" id="tercero" form="formrecibos"></th>
							<th></th>
                            <th>
                                <select name="estado" id="estado" class="form-control" form="formrecibos" onchange="this.form.submit()">
                                    <option value=""></option>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </th>
						</tr>
					</thead>
					<tbody>
						@forelse($recibos as $recibo)
							<tr>
								<td>{{ $recibo->prefijo }} {{ $recibo->numero }}</td>
                                <td>{{ $recibo->fecha }}</td>
                                <td>@foreach ($recibo->facturas as $factura)
                                        {{$factura->prefijo}}{{$factura->numero}} <br>
                                    @endforeach
                                </td>
								<td>{{ $recibo->facturas[0]->tercero->documento }}-{{ $recibo->facturas[0]->tercero->nombre }}</td>
                                <td>${{number_format($recibo->valor, 0, ",", ".") }}</td>
                                <td>{{$recibo->estado}}</td>
								<td>
                                    @if ($recibo->pago != null)
                                        <a class="btn btn-sm btn-primary" href="/pagos/{{$recibo->pago->id}}/descargar_recibo" target="_blank"><i class="fa fa-print" aria-hidden="true"></i></a>
                                    @else
                                        <a class="btn btn-sm btn-primary" href="/contabilidad/ingresos/{{$recibo->id}}/imprimir" target="_blank"><i class="fa fa-print" aria-hidden="true"></i></a> 
                                    @endif
                                    <a href="/contabilidad/recibos/{{$recibo->id}}/detalles" class="btn btn-sm btn-success">Detalles</a>
                                    @if ($recibo->estado == "Activo" && Auth::user()->rol == 0)
                                        <button type="button" class="btn btn-sm btn-danger" onclick="anular({{$recibo->id}});">Anular</button>
                                    @endif
                                    
                                </td>
								</tr>
						@empty
							<tr class="align-center">
								<td colspan="6">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
                @if(method_exists($recibos,'links'))
					{{ $recibos->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog" style="width: 40%">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="titulo">Anular Recibo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/contabilidad/recibos/anular" method="POST" id="formanular">
                    <input type="hidden" name="recibo" id="recibo">
                    <div class="modal-body">
                        <div class="row form-group">
                            <label for="motivo" class="col-md-3 label-required">Motivo</label>                         
                            <div class="col-md-9">
                                <input type="text" name="motivo" id="motivo" class="form-control" required>	
                            </div>
                        </div>					
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <button type="submit" class="btn btn-success">Confirmar</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript" src="/js/moment.min.js"></script>
    <script type="text/javascript" src="/js/daterangepicker.js"></script>
	<script>
		$(document).ready(function () {
            $("#fecha").daterangepicker({
				autoUpdateInput: false,
    			timePicker: true,
				timePicker24Hour: true,			
                locale: {
                    format: "YYYY/MM/DD HH:mm",
                    separator: " - ",
                    applyLabel: "Aplicar",
                    cancelLabel: "Cancelar",
                    fromLabel: "Desde",
                    toLabel: "Hasta",
                    customRangeLabel: "Custom",
                    daysOfWeek: [
                        "Do",
                        "Lu",
                        "Ma",
                        "Mi",
                        "Ju",
                        "Vi",
                        "Sa"
                    ],
                    monthNames: [
                        "Enero",
                        "Febrero",
                        "Marzo",
                        "Abril",
                        "Mayo",
                        "Junio",
                        "Julio",
                        "Agosto",
                        "Septiembre",
                        "Octubre",
                        "Noviembre",
                        "Diciembre"
                    ],
                    firstDay: 1
                }				
  			});
			@isset($filtro)
                $("#{{$filtro[0]}}").val('{{$filtro[1]}}');
            @endisset
		});

        $("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formrecibos").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});

        function anular(recibo) {
            $("#recibo").val(recibo);
            $("#titulo").text("Anular Recibo RC1 " + recibo);
            $("#Modal").modal("show");
        }

		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formrecibos").submit();
    		}
		});
	</script>
@endsection