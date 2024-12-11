@extends('layouts.logeado')

@section('sub_title', 'Solicitudes de crédito')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="align-center">
                <a href="#" class="btn btn-dark btn-sm">Nueva Solicitud</a>			
            </div>
			<form action="/solicitudes_credito/filtrar" method="GET" id="formsolicitudes"></form>
				<table class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr>
							<th>ID</th>
                            <th>Identificación Cliente</th>
                            <th>Nombre Cliente</th>                
                            <th>Monto</th>
                            <th>Plazo</th>                  
						</tr>
						<tr>
							<th></th>
							<th><input type="text" class="form-control filt" name="identificacion" id="identificacion" form="formsolicitudes"></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse($solicitudes as $solicitud)
							<tr>
								<td>{{ $solicitud->id }}</td>
                                <td>{{ $solicitud->cliente->nro_identificacion }}</td>
                                <td>{{ $solicitud->cliente->primer_nombre }}  {{ $solicitud->cliente->primer_apellido }}</td>
								<td>${{ number_format($solicitud->monto, 0, ",", ".") }}</td>
                                <td>{{ $solicitud->plazo }}</td>
								<td><a href="/solicitudes/{{ $solicitud->id }}/evaluar" class="btn btn-primary">Evaluar</a></td>
							</tr>
						@empty
							<tr class="align-center">
								<td colspan="5">No hay datos</td>
							</tr>
						@endforelse
					</tbody>
				</table>			
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).ready(function () {
			@if(isset($ide))
				$("#identificacion").val("{{$ide}}");
			@endif
		});


		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formsolicitudes").submit();
    		}
		});

		setInterval(cambios, 30000);

		var sincro = 1;
	    
	    function cambios(){
			if(sincro == 1){
				$.ajax({
                type: "GET",
				dataType: "json",
                url: "/solicitudes/nuevas",
				}).done(function (data, textStatus, jqXHR) {
					if(data.length > 0){
						sincro = 0;
						alertar();
						Swal.fire({
							title: "Alerta Solicitudes de Crédito",
							text: "Se han realizado " + data.length + " nuevas solicitudes de crédito",
							icon: "warning",
							confirmButtonText: 'Revisar',
						}).then((result) => {
							sincro = 1;
							for (const key in data) {			
								window.open("/solicitudes/" + data[key].id + "/evaluar");
							}
						});
					}	
				}).fail(function (jqXHR, textStatus, errorThrown) {
					
				});
			}
	    }

		function alertar() {
			var aud = document.createElement('audio');
			aud.src = "/sounds/solicitud.mp3";
			aud.autoplay = true;
			aud.muted = true;
			document.body.appendChild(aud);
			aud.muted = false;
			aud.loop = true;
			aud.play();
		}

	</script>
@endsection