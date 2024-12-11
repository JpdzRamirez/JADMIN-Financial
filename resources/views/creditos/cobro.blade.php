@extends('layouts.logeado')

@section('sub_title', 'Créditos En Cobro')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			<div class="text-right">
				<span style="font-weight:bold;margin-top:0.5rem">Exportar: </span>
				<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px" onclick="toExcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    			
			</div>
			<form action="/creditos_cobro/filtrar" method="GET" id="formcobro"></form>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>Número</th>
							<th>Identificación</th>
                            <th>Nombre</th>
                            <th>Monto</th>
                            <th>Cuotas</th>
                            <th>Tasa</th>
							<th>Fecha desembolso</th>
							<th>Tipo</th>            
						</tr>
						<tr>
							<th><input type="number" class="form-control filt" name="numero" id="numero" form="formcobro"></th>
							<th><input type="text" class="form-control filt" name="identificacion" id="identificacion" form="formcobro"></th>
							<th><input type="text" class="form-control filt" name="nombre" id="nombre" form="formcobro"></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th>
								<select name="tipo" class="form-control" id="tipo" form="formcobro" onchange="this.form.submit()">
									<option value=""></option>
									<option value="Plan Contractual y Extracontractual">Plan Contractual y Extracontractual</option>
									<option value="Plan SOAT">Plan SOAT</option>
									<option value="Nanocrédito">Nanocrédito</option>
									<option value="Libre Inversión">Libre Inversión</option>
									<option value="Emprendimientos">Emprendimientos</option>
								</select>
							</th>
						</tr>
					</thead>
					<tbody>
						@forelse($creditos as $credito)
							<tr>
								<td>{{ $credito->numero }}</td>
                                <td>{{ $credito->cliente->nro_identificacion }}</td>
								<td>{{ ucfirst($credito->cliente->primer_nombre) }}  {{ ucfirst($credito->cliente->primer_apellido) }}</td>
                                <td>${{ number_format($credito->monto_total, 0, ",", ".") }}</td>
								<td>{{ $credito->pagadas }}/{{ count($credito->cuotas) }}</td>
                                <td>{{ number_format($credito->tasa, 2, ",", ".")}} EA</td>
								<td>{{ $credito->fecha_prestamo }}</td>
								<td>{{ $credito->tipo }}</td>
								<td>
									<a href="/creditos/{{$credito->id}}/plan_pagos" class="btn btn-success btn-sm">Amortización</a>
									<a href="/pagos/credito/{{$credito->id}}" class="btn btn-primary btn-sm">Pagos</a>
									<a href="/contabilidad/facturas/ventas/{{$credito->factura->id}}/imprimir" class="btn btn-sm btn-secondary" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Factura</a>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="5">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($creditos,'links'))
					{{ $creditos->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).ready(function () {
			@if(isset($filtro))
				$("#{{$filtro[0]}}").val("{{$filtro[1]}}");
			@endif
		});

		function toExcel() {
			Swal.fire({
                title: '<strong>Exportando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
			$.ajax({
				type: "get",
				url: "/creditos_cobro/exportar",
				data: $("#formcobro").serialize()
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

                filename = "Creditos en cobro.xlsx";
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
        		$("#formcobro").submit();
    		}
		});
	</script>
@endsection