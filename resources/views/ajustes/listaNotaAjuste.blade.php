@extends('layouts.logeado')

@section('sub_title', 'Notas Ajuste')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="text-left">
                <a href="/contabilidad/notas_contables/nueva" class="btn btn-dark">Nueva Nota</a>
            </div>
            <form action="/contabilidad/notas_contables/filtrar" method="get" id="formNota"></form>
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Número</th>            
                        <th>Fecha</th>       
                        <th>Concepto</th>            
                    </tr>
                    <tr>
                        <th><input type="number" class="form-control filt" name="numero" id="numero" form="formNota"></th>
                        <th><input type="text" class="form-control" name="fecha" id="fecha" form="formNota" autocomplete="off"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notas as $nota)
                        <tr>
                            <td>{{ $nota->id }}</td>
                            <td>{{ $nota->fecha }}</td>   
                            <td>{{ $nota->concepto }}</td>  
                            <td>
                                <a href="/contabilidad/notas_contables/{{$nota->id}}/detalles" class="btn btn-sm btn-primary">Detalles</a>
                                <a href="/contabilidad/notas_contables/{{$nota->id}}/descargar" target="_blank" class="btn btn-sm btn-dark">Imprimir</a>
                            </td>                              
                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="3">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($notas,'links'))
                {{ $notas->links() }}
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
        });

        $("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formNota").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});

        $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formNota").submit();
    		}
		});
    </script>
@endsection