@extends('layouts.logeado')

@section('sub_title', 'Editar Recibo: ' . $recibo->prefijo . ' ' . $recibo->numero)

@section('sub_content')
	<div class="card">
		<div class="card-body">
                <div class="row form-group">
                    <label for="tercero" class="col-md-2">Tercero</label>
                    <div class="col-md-6">
                        <input type="text" name="tercero" value="{{$recibo->facturas[0]->tercero->documento}}" id="tercero" class="form-control terce" autocomplete="off">
                    </div>
                </div>
                <div class="row form-group">
                    <label for="fecha" class="col-md-2">Fecha</label>
                    <div class="col-md-6">
                        <input type="date" name="fecha" value={{$recibo->fecha}} id="fecha" class="form-control">
                        
                    </div>
                </div>
                <div class="row form-group">
                    <label for="concepto" class="col-md-2">Observaciones</label>
                    <div class="col-md-6">
                        <textarea name="concepto" id="concepto" rows="3" class="form-control">{{$recibo->observaciones}}</textarea>
                    </div>	
                </div>
			<hr>
            <table class="table table-bordered">
                <tr>
                    <th>Cuenta</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Tercero</th>
                </tr>
                <tbody id="tb-movs">
                @for ($i=0; $i < count($recibo->movimientos); $i++)
                    <tr id="{{$recibo->movimientos[$i]->id}}">
                        <td><input type="text" id="{{$recibo->movimientos[$i]->id}}-cuenta" value={{$recibo->movimientos[$i]->cuenta->codigo}} class="form-control cta" autocomplete="off"></td>
                        <td id="{{$recibo->movimientos[$i]->id}}-lbl">{{$recibo->movimientos[$i]->cuenta->nombre}}</td>
                        <td><select id="{{$recibo->movimientos[$i]->id}}-tipo" value={{$recibo->movimientos[$i]->naturaleza}} class="form-control">
                                @if ($recibo->movimientos[$i]->naturaleza == "Crédito")
                                    <option value="Crédito" selected>Crédito</option>
                                    <option value="Débito">Débito</option>
                                @else
                                    <option value="Crédito">Crédito</option>
                                    <option value="Débito" selected>Débito</option>
                                @endif
                            </select>
                        </td>
                        <td><input type="text" id="{{$recibo->movimientos[$i]->id}}-valor" value={{$recibo->movimientos[$i]->valor}} class="form-control"></td>
                        <td><input type="text" id="{{$recibo->movimientos[$i]->id}}-tercero" value={{$recibo->movimientos[$i]->tercero->documento}} class="form-control terce" autocomplete="off"></td>
                        @if ($i >= 2)
                            <td>
                                <button type="button" onclick="borrar({{$recibo->movimientos[$i]->id}}, 0)" class="btn btn-sm btn-danger">Borrar</button>
                            </td>
                        @endif
                    </tr>
                @endfor
                </tbody>
            </table>

            <button type="button" onclick="agregar();" class="btn btn-sm btn-primary">Agregar</button>

			<div class="text-center m-t-30">
				<button type="button" class="btn btn-dark" onclick="enviar();">Guardar</button>
			</div>
		</div>
	</div>
@endsection
@section('script')
    <script>
        var movimientos;
        var borrados = new Array();
        var lineas = new Array();
        var ids = 0;

        $(document).ready(function () {
            movimientos = {{$recibo->movimientos->pluck("id")}};
        });

		$(".terce").autocomplete({
      		source: function(request, response) {
                $.ajax({
                    url: "/contabilidad/terceros/buscar",
                    dataType: "json",
                    data: {tercero: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.documento + "-" + item.nombre;
							item.value = item.documento;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3,
			change: function (event, ui) {
				if(ui.item == null){
					$(this).val("");
				}else{
                    if($(this).attr("id") == "tercero"){
                        $(".terce").val(ui.item.value);
                    }
                }
			}
        });

        $(document).on('keydown.autocomplete', '.cta', function () {  
            $(this).autocomplete({
                source: function( request, response ) {
                    $.ajax({
                        url: "/contabilidad/cuentas/buscar",
                        dataType: "json",
                        data: {cuenta: request.term},
                        success: function(data) {
                            response($.map(data, function (item) {
                                item.label = item.codigo + "_" + item.nombre;
                                item.nombre = item.nombre;
                                item.value = item.codigo;
                                return item;
                            }) );
                        }
                    });
                },
                minLength: 3,
                change: function name(event, ui) {
                    let cta = $(this).attr("id").split("-");
                    if (cta.length == 2) {
                        $("#"+cta[0]+"-lbl").text(ui.item.nombre);
                    }else{
                        $("#nuevo-"+cta[1]+"-lbl").text(ui.item.nombre);
                    }  
                }
            });
        });    

        
		function agregar() {
            let fila = '<tr id="nuevo' + ids + '">';
            fila = fila + '<td><input type="text" id="nuevo-' + ids + '-cuenta"  class="form-control cta" autocomplete="off"></td>';
            fila = fila + '<td id="nuevo-' +ids + '-lbl"></td>';
            fila = fila + '<td><select id="nuevo-' + ids + '-tipo"  class="form-control"><option value="Crédito">Crédito</option><option value="Débito">Débito</option></select></td>';
            fila = fila + '<td><input type="text" id="nuevo-' + ids + '-valor" value="0" class="form-control"></td>';
            fila = fila + '<td><input type="text" id="nuevo-' + ids + '-tercero" value="{{$recibo->facturas[0]->tercero->documento}}" class="form-control terce" autocomplete="off"></td>';
            fila = fila + '<td><button type="button" onclick="borrar(' + ids + ', 1)" class="btn btn-sm btn-danger">Borrar</button></td></tr>';
            $("#tb-movs").append(fila);
            lineas.push(ids);
            ids++;
        }

        function borrar(indice, tipo) {
            if(tipo == 0){
                borrados.push(indice);
                $("#"+indice).remove();
            }else{
                $("#nuevo"+indice).remove();
                lineas.splice(indice, 1);
            }
        }

		function enviar() {
            var editados = new Array();
            for (const key in movimientos) {
                if(borrados.indexOf(movimientos[key]) == -1){
                    let editado = new Object();
                    editado.id = movimientos[key];
                    editado.cuenta = $("#"+movimientos[key]+"-cuenta").val();
                    editado.tercero = $("#"+movimientos[key]+"-tercero").val();
                    editado.tipo = $("#"+movimientos[key]+"-tipo").val();
                    editado.valor = $("#"+movimientos[key]+"-valor").val();
                    editados.push(editado);
                } 
            }
            var nuevos = new Array();
            for (let index = 0; index < lineas.length; index++) {
                let elemento = "#nuevo-" + lineas[index];
                if($(elemento + "-cuenta").val() != ""){
                    let nuevo = new Object();
                    nuevo.cuenta = $(elemento+"-cuenta").val();
                    nuevo.tercero = $(elemento+"-tercero").val();
                    nuevo.tipo = $(elemento+"-tipo").val();
                    nuevo.valor = $(elemento+"-valor").val();
                    nuevos.push(nuevo);
                }
            }

			if(editados.length > 0){
                Swal.fire({
                    title: '<strong>Enviando...</strong>',
                    html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                    showConfirmButton: false,
                });
                $.ajax({
                    type: "post",
                    url: "/contabilidad/notas_contables/editar",
                    data: {	editados: JSON.stringify(editados),
                            nuevos: JSON.stringify(nuevos),
                            borrados: borrados,
                            recibo: {{$recibo->id}},
                            concepto: $("#concepto").val(),
                            tercero: $("#tercero").val(),
                            fecha: $("#fecha").val(),
                            _token: "{{csrf_token()}}"},
                            dataType: "json"
                }).done(function (data) {  
                    Swal.close();
                    if(data.respuesta == "success"){
                        Swal.fire({
                            title: "Recibo editado",
                            text: "El recibo fue editado exitosamente",
                            icon: data.respuesta,
                            confirmButtonText: 'OK',
                        }).then((result) => {
                            window.open(data.msg);
                            location.reload();
                        });
                    }else{
                        Swal.fire(
                            data.msj,
                            "La edición del recibo falló",
                            data.respuesta
                        );
                    }
                }).fail(function (jqXHR, textStatus, errorThrown) { 
                    Swal.close();
                    Swal.fire(
                        'Error enviando los datos',
                        textStatus,
                        'error'
                    );s
                });
			}else{
				Swal.fire(
					'Sin movimientos',
					'Debe ingresar movimientos de cuentas',
					'error'
				);
			}
		}
	</script>
@endsection