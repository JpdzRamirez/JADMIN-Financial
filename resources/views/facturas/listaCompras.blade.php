@extends('layouts.logeado')

@section('sub_title', 'Facturas de Compra')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row mb-1">
                <div class="col-md-2">
                <a href="/contabilidad/facturas/compras/nueva" class="btn btn-sm btn-dark">Nueva Factura</a>
                </div>
                <div class="col-md-2 offset-md-8">
                    <select name="envyear" id="envyear" class="form-control" form="formcompras">
                        @for ($i = 2022; $i <= $fechaenv->year; $i++)
                            @if ($i == $envyear)
                                <option value="{{$i}}" selected>{{$i}}</option>
                            @else   
                                <option value="{{$i}}">{{$i}}</option>                     
                            @endif
                        @endfor        
                    </select>
                </div>
            </div>
            <form action="/contabilidad/facturas/compras/filtrar" method="get" id="formcompras"></form>
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Tercero</th>
                        <th>Concepto</th>
                        <th>Valor</th>                   
                    </tr>
                    <tr>
                        <th><input type="text" name="numero" id="numero" class="form-control filt" form="formcompras"></th>
                        <th><input type="text" id="fecha" name="fecha" class="form-control" form="formcompras" autocomplete="off"></th>
                        <th><input type="text" id="tercero" name="tercero" class="form-control filt" form="formcompras"></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td>{{ $factura->prefijo }} {{ $factura->numero }}</td>
                            <td>{{ $factura->fecha }}</td>
                            <td>{{ $factura->tercero->nombre }}</td>
                            <td>{{ $factura->descripcion }}</td>                          
                            <td>${{ number_format($factura->valor,2,",",".") }}</td>
                            <td>
                                @if ($factura->cruzada == 0)
                                    <a href="/contabilidad/facturas/compras/{{$factura->id}}/pagar" class="btn btn-sm btn-dark">Pagar</a>
                                @endif 
                                <a href="/contabilidad/facturas/compras/{{$factura->id}}/imprimir" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-print" aria-hidden="true"></i></a>
                                <a href="/contabilidad/facturas/detalles/{{$factura->id}}" class="btn btn-sm btn-success">Detalles</a>
                                <a href="/contabilidad/notas_credito/{{$factura->id}}/nueva" class="btn btn-sm btn-danger">NC</a>
                                <a href="/contabilidad/notas_debito/{{$factura->id}}/nueva" class="btn btn-sm btn-warning">ND</a>
                                
                            </td>
                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="3">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($facturas,'links'))
                {{ $facturas->links() }}
            @endif			
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

            @if(isset($numero))
              $("#numero").val({{$numero}});
            @endif

            @if(isset($fecha))
              $("#fecha").val("{{$fecha}}");
            @endif

            @if(isset($tercero))
              $("#tercero").val("{{$tercero}}");
            @endif
        });

        $("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formcompras").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});

        $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formcompras").submit();
    		}
		});
    </script>
@endsection