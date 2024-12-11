@extends('layouts.logeado')

@section('sub_title', 'Documentos Soporte')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="text-left">
                <a href="/contabilidad/facturas/soportes/nuevo" class="btn btn-sm btn-dark">Nuevo Documento Soporte</a>
            </div>
            <form action="/contabilidad/facturas/soportes/filtrar" method="get" id="formsoportes"></form>
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
                        <th><input type="text" name="numero" id="numero" class="form-control filt" form="formsoportes"></th>
                        <th><input type="text" id="fecha" name="fecha" class="form-control" form="formsoportes" autocomplete="off"></th>
                        <th><input type="text" id="tercero" name="tercero" class="form-control filt" form="formsoportes"></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($soportes as $soporte)
                        <tr>
                            <td>{{ $soporte->prefijo }} {{ $soporte->numero }}</td>
                            <td>{{ $soporte->fecha }}</td>
                            <td>{{ $soporte->tercero->nombre }}</td>
                            <td>{{ $soporte->descripcion }}</td>                          
                            <td>${{ number_format($soporte->valor,2,",",".") }}</td>
                            <td>
                                @if ($soporte->cruzada == 0)
                                    <a href="/contabilidad/facturas/compras/{{$soporte->id}}/pagar" class="btn btn-sm btn-dark">Pagar</a>
                                @endif 
                                <a href="/contabilidad/facturas/compras/{{$soporte->id}}/imprimir" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-print" aria-hidden="true"></i></a>
                                <a href="/contabilidad/facturas/detalles/{{$soporte->id}}" class="btn btn-sm btn-success">Detalles</a>
                                
                            </td>
                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="3">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($soportes,'links'))
                {{ $soportes->links() }}
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
			$("#formsoportes").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});

        $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formsoportes").submit();
    		}
		});
    </script>
@endsection