@extends('layouts.logeado')

@section('sub_title', 'Notas Débito')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <form action="/contabilidad/notas_debito/filtrar" method="get" id="formNota"></form>
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Tipo Factura</th> 
                        <th>#Factura</th>                    
                    </tr>
                    <tr>
                        <th><input type="number" class="form-control filt" name="numero" id="numero" form="formNota"></th>
                        <th><input type="text" class="form-control" name="fecha" id="fecha" form="formNota" autocomplete="off"></th>
                        <th><input type="text" class="form-control filt" name="factura" id="factura" form="formNota"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notasDebito as $nota)
                        <tr>
                            <td>{{ $nota->numero }}</td>
                            <td>{{ $nota->fecha }}</td>   
                            <td>{{ $nota->factura->tipo }}</td>                          
                            <td>{{ $nota->factura->prefijo }}{{ $nota->factura->numero }}</td>      
                            <td>
                                <a href="/contabilidad/notas/{{$nota->id}}/descargar" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-print" aria-hidden="true"></i></a>
                                <a href="/contabilidad/notas/{{$nota->id}}/detalles" class="btn btn-sm btn-success">Detalles</a>
                            </td>     
                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="3">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($notasDebito,'links'))
                {{ $notasDebito->links() }}
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

            @if(isset($factura))
              $("#factura").val("{{$factura}}");
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