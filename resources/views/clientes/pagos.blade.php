@extends('layouts.logeado')

@section('sub_title', 'Plan de pagos crédito #' . $credito->numero . ", Factura: " . $credito->factura->prefijo . $credito->factura->numero . ". " . $credito->factura->tercero->nombre)

@section('sub_content')
	<div class="card">
		<div class="card-body">
				<table class="table table-bordered">
					<thead>
						<tr>
                            <th># Cuota</th>
                            <th>A. capital</th>
							<th>Saldo capital</th>
                            <th>Interés</th>
							<th>Fecha de vencimiento</th>
							<th>Días Mora</th>
							<th>Interés Mora</th>
                            <th>Total cuota</th>                                             
                            <th>Estado</th>                  
						</tr>
					</thead>
					<tbody>
						@php
							$saldo = 0;
							$mora = 0;
						@endphp
						@forelse($credito->cuotas as $cuota)
                            @if ($cuota->estado == "Pagada")
                                <tr style="background-color: mediumseagreen">
                            @elseif($cuota->estado == "Vigente")
                                <tr style="background-color: lightblue">
                            @elseif($cuota->estado == "Vencida")
                                <tr style="background-color: lightcoral">
                            @else          
                                <tr>       
                            @endif
								<td>@if ($cuota->descripcion == null)
									{{ $cuota->ncuota }}
								@else
									{{ $cuota->descripcion }}
								@endif
								</td> 				                     						
								@if ($cuota->estado == "Pagada")
									<td>${{ number_format($cuota->abono_capital, 0, ",", ".") }}</td>
									<td>${{ number_format($cuota->saldo_capital, 0, ",", ".") }}</td>
									<td>${{ number_format($cuota->interes, 0, ",", ".") }} </td>
									<td>{{ $cuota->fecha_vencimiento }}</td>
									<td>{{ $cuota->mora }}</td>
									<td>{{ number_format($cuota->interes_mora, 0, ",", ".") }}</td>
									<td>${{ number_format($cuota->abono_capital+$cuota->interes+$cuota->interes_mora, 0, ",", ".") }}</td>
								@else
									<td>${{ number_format($cuota->abono_capital, 0, ",", ".") }}</td>
									<td>${{ number_format($cuota->saldo_capital, 0, ",", ".") }}</td>
									<td>${{ number_format($cuota->saldo_interes, 0, ",", ".") }} </td>
									<td>{{ $cuota->fecha_vencimiento }}</td>
									<td>{{ $cuota->mora }}</td>
									<td>{{ number_format($cuota->saldo_mora, 0, ",", ".") }}</td>
									<td>${{ number_format($cuota->saldo_capital+$cuota->saldo_interes+$cuota->saldo_mora, 0, ",", ".") }}</td>
									@php
										$mora = $mora + $cuota->saldo_mora;
										$saldo = $saldo + $cuota->saldo_capital+$cuota->saldo_interes+$cuota->saldo_mora;
									@endphp
								@endif	
                                <td>{{ $cuota->estado }}</td>
								<td style="background-color: white">
									@if ($cuota->estado == "Vencida" && Auth::user()->rol == 0)
										@if ($cuota->estado_mora == 1)
											<a href="/contabilidad/vencidas/{{$cuota->id}}/mora" class="btn btn-sm btn-danger">Inactivar mora</a>
											<a href="#" onclick="editarMora({{$cuota->id}}, {{$cuota->saldo_mora}})" class="btn btn-sm btn-warning">Editar mora</a>
										@else
											<a href="/contabilidad/vencidas/{{$cuota->id}}/mora" class="btn btn-sm btn-success">Activar mora</a>
										@endif
									@endif
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="5">No hay datos</td>
							</tr>
						@endforelse
						<tr>
							<td colspan="5"></td>
							<th>Saldo</th>
							<th>${{number_format($mora, 0, ",", ".")}}</th>
							<th>${{number_format($saldo, 0, ",", ".")}}</th>
						</tr>
						<tr>
							@if ($mora > 0  && Auth::user()->rol == 0)
									<td colspan="6"></td>
									<th>
									<a href="#" onclick="editarTotalMora({{$credito->id}}, {{$mora}})" class="btn btn-sm btn-warning">Editar mora</a>
									</th>
							@endif
						</tr>
					</tbody>
				</table>		
		</div>
	</div>
<!-- Modal editar mora -->
<div id="ModEditar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Editar Mora</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<form action="/contabilidad/vencidas/editar_mora" method="POST">
				<input type="hidden" name="idcuota" id="idcuota">
			<div class="modal-body">
				<div class="row form-group">
					<label for="descripcion" class="col-md-3 label-required">Interes de mora</label>                         
					<div class="col-md-9">
						<input type="number" step="0.01" name="esaldomora" id="esaldomora" min="0" class="form-control" >	
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
<!-- Modal editar total mora -->
<div id="ModEditarTotalMora" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Editar Mora Total</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<form action="/contabilidad/vencidas/editar_moraTotal" method="POST" name="formEditarMora" id="formEditarMora">
				<input type="hidden" name="idcredito" id="idcredito">
				<input type="hidden" name="oldmora" id="oldmora">
			<div class="modal-body">
				<div class="row form-group">
					<label for="descripcion" class="col-md-3 label-required">Saldo</label>                         
					<div class="col-md-9">
						<input type="number" step="0.01" name="esaldomoraTotal" id="esaldomoraTotal" min="0" class="form-control">	
					</div>
				</div>		
			</div>
			<div class="modal-footer">
				<input type="hidden" name="_token" value="{{csrf_token()}}">
				<button type="button" class="btn btn-success" name="guardarMora" id="guardarMora" onclick="guardarNuevaMora()">Guardar</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
			</div>
			</form>
		</div>
	</div>
</div>
@endsection
@if (session("factura"))
	@section('script')
		<script>
			$(document).ready(function () {
				window.open("/contabilidad/facturas/ventas/{{session('factura')}}/imprimir", "_blank");
			});	
		</script>
	@endsection
@endif
@if (session("pago"))
	@section('script')
		<script>
			$(document).ready(function () {
				window.open("/pagos/{{session('pago')}}/descargar_recibo", "_blank");
			});	
		</script>
	@endsection
@endif
@section('script')
	<script>
		$("#esaldomoraTotal").keyup(function () {
 
 		var valor = $(this).prop("value");
 		if (valor < 0)
	 	$(this).prop("value", " ");
		});

		function editarMora(id, interesmora) {
			$("#idcuota").val(id);
			$("#esaldomora").val(interesmora);
			$("#ModEditar").modal("show");
		}


		function editarTotalMora(creditoId, saldoOldMora) {
			$("#idcredito").val(creditoId);
			$("#oldmora").val(saldoOldMora);
			$("#esaldomoraTotal").val(saldoOldMora);
			$("#ModEditarTotalMora").modal("show");
		}
		
           
        function guardarNuevaMora(){
			idcredito = $('#idcredito').val();
			saldoOldMora = $('#oldmora').val();
			esaldomoraTotal = $('#esaldomoraTotal').val();

			if(esaldomoraTotal > saldoOldMora){
				Swal.fire({
                            //data.msj,
							icon: 'error',
							title:"Error al editar la mora",
                            text: "La nueva mora no puede ser mayor a la actual",
							confirmButtonText: 'OK',
				})
						
			}
			else{
					document.getElementById("formEditarMora").submit();
				}
			}

	</script>
@endsection

