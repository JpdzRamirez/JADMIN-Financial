@extends('layouts.logeado')

@section('sub_title', 'Comprobantes de Egreso')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row mb-1">
                <div class="col-md-2">
                    <a href="/contabilidad/egresos/nuevo" class="btn btn-dark">Nuevo Egreso</a>
                </div>
                <div class="col-md-2 offset-md-8">
                    <select name="envyear" id="envyear" class="form-control" form="formegreso">
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
            <form action="/contabilidad/egresos/filtrar" method="get" id="formegreso"></form>
            <table class="table table-bordered" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Factura</th>
                        <th>Tercero</th>           
                        <th>Fecha</th>       
                        <th>Concepto</th>
                        <th>Estado</th>        
                    </tr>
                    <tr>
                        <th><input type="number" class="form-control filt" name="numero" id="numero" form="formegreso"></th>
                        <th><input type="number" class="form-control filt" name="factura" id="factura" form="formegreso"></th>
                        <th><input type="text" maxlength="8" name="tercero" class="form-control filt" id="tercero" form="formegreso"></th>
                        <th><input type="text" class="form-control" name="fecha" id="fecha" form="formegreso" autocomplete="off"></th>
                        <th></th>
                        <th>
                            <select name="estado" id="estado" class="form-control" form="formegreso" onchange="this.form.submit()">
                                <option value=""></option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($egresos as $egreso)
                        <tr>
                            <td>{{ $egreso->prefijo }} {{ $egreso->numero }}</td>
                            <td>@if ($egreso->factura != null)
                                    {{ $egreso->factura->prefijo }} {{ $egreso->factura->numero }}
                                @endif                              
                            </td>
                            <td>{{ $egreso->tercero->nombre}}</td>
                            <td>{{ $egreso->fecha }}</td>   
                            <td>{{ $egreso->concepto }}</td>  
                            <td>{{ $egreso->estado }}</td>
                            <td><a href="/contabilidad/comprobantes/{{$egreso->id}}/descargar" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-print" aria-hidden="true"></i></a>
                                @if (Auth::user()->rol == 0)
                                    <a href="/contabilidad/comprobantes/{{$egreso->id}}/editar" class="btn btn-sm btn-warning">Editar</a>
                                    @if ($egreso->estado == "Activo")
                                        <button type="button" class="btn btn-sm btn-danger" onclick="anular('{{$egreso->prefijo}}', {{$egreso->numero}}, {{$egreso->id}});">Anular</button>
                                    @endif
                                @endif
                                
                            </td>

                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="5">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($egresos,'links'))
                {{ $egresos->links() }}
            @endif			
		</div>
	</div>
@endsection
@section('modal')
        <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
            <div class="modal-dialog" style="width: 40%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="titulo">Anular Egreso</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="/contabilidad/egresos/anular" method="POST" id="formanular">
                        <input type="hidden" name="egreso" id="egreso">
                        <input type="hidden" name="prefijo" id="prefijo">
                        <input type="hidden" name="comprobante" id="comprobante">
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

        function anular(prefijo, egreso, id) {
            $("#comprobante").val(id);
            $("#egreso").val(egreso);
            $("#prefijo").val(prefijo);
            $("#titulo").text("Anular Egreso " + prefijo + " " + egreso);
            $("#Modal").modal("show");
        }

        $("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formegreso").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});

        $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formegreso").submit();
    		}
		});
    </script>
@endsection