@extends('layouts.logeado')

@section('sub_title', 'Notas Contables')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row mb-1">
                <div class="col-md-4">
                    <a href="/contabilidad/notas_contables/nueva" class="btn btn-sm btn-dark">Nueva Nota</a>
                    <a href="#" class="btn btn-dark btn-sm open-modal" data-toggle="modal" data-target="#Modal">Importar Nota</a>	
                </div>
                <div class="col-md-2 offset-md-6">
                    <select name="envyear" id="envyear" class="form-control" form="formNota">
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
            <form action="/contabilidad/notas_contables/filtrar" method="get" id="formNota"></form>
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Prefijo</th>
                        <th>Número</th>            
                        <th>Fecha</th>       
                        <th>Concepto</th>
                        <th>Estado</th>        
                    </tr>
                    <tr>
                        <th><input type="text" class="form-control filt" name="prefijo" id="prefijo" form="formNota"></th>
                        <th><input type="number" class="form-control filt" name="numero" id="numero" form="formNota"></th>
                        <th><input type="text" class="form-control" name="fecha" id="fecha" form="formNota" autocomplete="off"></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notas as $nota)
                        <tr>
                            <td>{{ $nota->prefijo }}</td>
                            <td>{{ $nota->numero }}</td>
                            <td>{{ $nota->fecha }}</td>   
                            <td>{{ $nota->concepto }}</td>
                            <td>{{ $nota->estado }}</td>
                            <td>
                                <a href="/contabilidad/notas_contables/{{$nota->id}}/descargar" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-print" aria-hidden="true"></i></a>
                                <a href="/contabilidad/notas_contables/{{$nota->id}}/detalles" class="btn btn-sm btn-success">Detalles</a>
                                @if (Auth::user()->rol == 0 && $nota->estado == "Activo")
                                    <a href="/contabilidad/notas_contables/{{$nota->id}}/editar" class="btn btn-sm btn-warning">Editar</a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="anular('{{$nota->prefijo}}',{{$nota->numero}}, {{$nota->id}})">Anular</button>      
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
            @if(method_exists($notas,'links'))
                {{ $notas->links() }}
            @endif			
		</div>
	</div>
@endsection
@section('modal')
<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Importar Nota Contable</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="/importar_notas" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="row form-group">
                    <div class="col-md-3">
                        <label for="concepto" class="label-required">Concepto</label>                         
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="concepto" id="concepto" class="form-control" required>	
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label for="filenota" class="label-required">Archivo</label>                         
                    </div>
                    <div class="col-md-9">
                        <input type="file" name="filenota" id="filenota" class="form-control" required>
                    </div>
                </div>				
            </div>
            <div class="modal-footer">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <button type="submit" class="btn btn-success">Guardar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div id="modalAnular" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
    <div class="modal-dialog" style="width: 40%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="titulo">Anular Nota</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="/contabilidad/notas_contables/anular" method="POST" id="formanular">
                <input type="hidden" name="nota" id="nota">
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

            @if(isset($numero))
              $("#numero").val({{$numero}});
            @endif

            @if(isset($fecha))
              $("#fecha").val("{{$fecha}}");
            @endif

            @if(isset($prefijo))
              $("#prefijo").val("{{$prefijo}}");
            @endif
        });

        $("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formNota").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});

        function anular(prefijo, numero, id) {
        $("#nota").val(id);
        $("#titulo").text("Anular Nota " + prefijo + " " + numero);
        $("#modalAnular").modal("show");
        }

        $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formNota").submit();
    		}
		});
    </script>
@endsection