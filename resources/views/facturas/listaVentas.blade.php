@extends('layouts.logeado')

@section('sub_title', 'Facturas de venta')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="align-center">
                <a href="/contabilidad/facturas/ventas/nueva" style="float: left" class="btn btn-sm btn-dark">Nueva Factura</a>
                <button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toExcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>
                <span style="font-weight:bold;margin-top:0.5rem;float: right">Exportar: </span>	                    			
            </div>
            <form action="/contabilidad/facturas/ventas/filtrar" method="get" id="formventas"></form>
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Valor</th> 
                        <th>Tercero</th>
                        <th>Estado</th>                    
                    </tr>
                    <tr>
                        <th><input type="text" name="numero" id="numero" class="form-control filt" form="formventas"></th>
                        <th><input type="text" id="fecha" name="fecha" class="form-control" form="formventas" autocomplete="off" onchange="this.form.submit()"></th>
                        <th><input type="text" name="concepto" id="concepto" class="form-control filt" form="formventas"></th>
                        <th></th>
                        <th><input type="text" name="cliente" id="cliente" class="form-control filt" form="formventas"></th>
                        <th><select name="estado" id="estado" class="form-control" form="formventas" onchange="this.form.submit()">
                                <option value=""></option>
                                <option value="1">Cobrada</option>
                                <option value="0">Pendiente</option>
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td>{{ $factura->prefijo }} {{ $factura->numero }}</td>
                            <td>{{ $factura->fecha }}</td> 
                            <td>{{ $factura->descripcion }}</td>                            
                            <td>${{ number_format($factura->valor,2,",",".") }}</td>
                            <td>{{ $factura->tercero->documento }}, {{$factura->tercero->nombre}}</td>
                            <td>@if ($factura->cruzada == 1)
                                    Cobrada
                                @else
                                    Pendiente
                                @endif
                            </td>
                            <td>
                                <a href="/contabilidad/facturas/ventas/{{$factura->id}}/imprimir" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-print" aria-hidden="true"></i></a>
                                <a href="/contabilidad/facturas/detalles/{{$factura->id}}" class="btn btn-sm btn-success">Detalles</a>
                                @if ($factura->cruzada == 0)
                                    <a href="/contabilidad/notas_credito/{{$factura->id}}/nueva" class="btn btn-sm btn-danger">NC</a>
                                    <a href="/contabilidad/notas_debito/{{$factura->id}}/nueva" class="btn btn-sm btn-warning">ND</a>
                                @endif
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

            @if(isset($cliente))
              $("#cliente").val({{$cliente}});
            @endif

            @if(isset($estado))
              $("#estado").val({{$estado}});
            @endif

            @if(isset($concepto))
              $("#concepto").val("{{$concepto}}");
            @endif

        });

        $("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formventas").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});

        function toExcel() {
			Swal.fire({
                title: '<strong>Exportando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
			$.ajax({
				type: "get",
				url: "/facturas/ventas/exportar",
				data: $("#formventas").serialize()
			}).done(function (data) {  
				Swal.close();
                const byteCharacters = atob(data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);

                var csvFile;
                var downloadLink;

                filename = "Facturas venta.xlsx";
                csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
                downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();

			}).fail(function () {  
				Swal.close();
                Swal.fire('Error', 'No fue posible exportar', 'error');
			});
		}

          $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formventas").submit();
    		}
		});
    </script>
@endsection