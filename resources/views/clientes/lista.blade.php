@extends('layouts.logeado')

@section('sub_title', 'Clientes')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="align-center">
                <a href="/clientes/nuevo" class="btn btn-dark btn-sm">Nuevo Cliente</a>
				<button type="button" style="float: right; background-color: #00965e;" class="btn" onclick="toExcel();"><i class="fa fa-file-excel"></i></button>		
            </div>
			<form action="/clientes/filtrar" method="GET" id="formclientes"></form>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Identificación</th>
                            <th>Nombre</th>
                            <th>Celular</th>
                            <th>Condición</th>                  
						</tr>
						<tr>
							<th></th>
							<th><input type="text" class="form-control filt" name="identificacion" id="identificacion" form="formclientes"></th>
							<th></th>
							<th></th>
							<th>
								<select name="condicion" class="form-control" id="condicion" form="formclientes" onchange="this.form.submit()">
									<option value=""></option>
									<option value="Propietario">Propietario</option>
									<option value="Conductor">Conductor</option>
									<option value="Administrativo">Administrativo</option>
									<option value="Particular">Particular</option>
								</select>
							</th>
						</tr>
					</thead>
					<tbody>
						@forelse($clientes as $cliente)
							<tr>
								<td>{{ $cliente->id }}</td>
                                <td>{{ $cliente->nro_identificacion }}</td>
                                <td>{{ $cliente->primer_nombre }}  {{ $cliente->primer_apellido }}</td>
								<td>{{ $cliente->celular }}</td>
                                <td>{{ $cliente->condicion }}</td>
								<td>
									<a href="/clientes/{{$cliente->id}}/creditos" class="btn btn-primary btn-sm">Créditos</a><br>
									@if ($cliente->condicion == "Propietario")
									<!--	<a href="/vehiculos/ID" class="btn btn-success btn-sm">Vehículos</a><br>-->
									@endif
									<a href="/clientes/{{$cliente->id}}/editar" class="btn btn-warning btn-sm">Actualizar datos</a>
								</td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="5">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(method_exists($clientes,'links'))
					{{ $clientes->links() }}
				@endif			
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).ready(function () {
			@if(isset($ide))
				$("#identificacion").val("{{$ide}}");
			@endif
			@if(isset($cond))
				$("#condicion").val("{{$cond}}");
			@endif
		});


		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formclientes").submit();
    		}
		});

		function toExcel() {
			Swal.fire({
                title: '<strong>Exportando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
			$.ajax({
				type: "get",
				url: "/clientes/exportar"
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

                filename = "Clientes.xlsx";
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
	</script>
@endsection