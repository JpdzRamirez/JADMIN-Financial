@extends('layouts.logeado')

@section('sub_title', 'Terceros')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@if (session("creado"))
				@if (session("creado") == "ok")
					<div class="alert alert-success">
						<h5>Tercero registrado exitosamente</h5>
					</div>
				@else
					<div class="alert alert-danger">
						<h5>Error: {{session("creado")}}</h5>
					</div>
				@endif
				
			@endif
            <div>
                <a href="/contabilidad/terceros/nuevo" class="btn btn-sm btn-dark">Nuevo Tercero</a>
            </div>
			<form action="/contabilidad/terceros/filtrar" method="get" id="formterceros"></form>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>ID</th>
						<th>Tipo</th>
						<th>Identificación</th>
						<th>Nombre</th>               
					</tr>
					<tr>
						<th></th>
						<th></th>
						<th><input type="text" name="identificacion" class="form-control filt" id="identificacion" form="formterceros"></th>
						<th><input type="text" name="nombre" class="form-control filt" id="nombre" form="formterceros"></th>
					</tr>
				</thead>
				<tbody>
					@forelse($terceros as $tercero)
						<tr>
							<td>{{ $tercero->id }}</td>
							<td>{{ $tercero->tipo }}</td>
							<td>{{$tercero->documento}}</td>
							<td>{{ $tercero->nombre }} </td>
							<td><a href="/contabilidad/terceros/{{$tercero->id}}/editar" class="btn btn-sm btn-warning">Editar</a></td>
						</tr>
					@empty
						<tr class="align-center">
							<td colspan="3">No hay datos</td>
						</tr>
					@endforelse
				</tbody>
			</table>
			@if(method_exists($terceros,'links'))
				{{ $terceros->links() }}
			@endif			
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(document).ready(function () {
			@isset($filtro)
                $("#{{$filtro[0]}}").val('{{$filtro[1]}}');
            @endisset
		});

		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formterceros").submit();
    		}
		});
		
	</script>
@endsection