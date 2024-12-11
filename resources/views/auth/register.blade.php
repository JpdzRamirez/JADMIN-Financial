@extends('layouts.plantilla')

@section('content')
	<div class="accountbg"></div>
	<div style="margin: 5.5% auto; position: relative;">
		<div class="card">
			<div class="card-body">
				@if($errors->first('sql') != null)
					<div class="alert alert-danger m-t-10">
						<h6>{{$errors->first('sql')}}</h6>
					</div>				
				@endif
				<div class="row">
					<div class="col-md-6" style="border: 3px double blue">
						<div class="p-3">							
							<h3 class="m-b-5 text-center" style="color: rgb(46,59,116)">Regístrate en Cahors</h3>
							{{ Form::open(['url' => route('register'), 'method' => 'post', 'id' => 'formreg', 'class' => 'form-horizontal m-t-30']) }}
								<div class="form-group">
									{{ Form::label('tipoid', 'Tipo de identificación') }}
									<select name="tipoid" id="tipoid" class="form-control">
										<option value="Cédula de ciudadanía">Cédula de ciudadanía</option>
										<option value="Cédula de extranjería">Cédula de extranjería</option>
									</select>
								</div>
								<div class="form-group">
									{{ Form::label('numid', 'Número de identificación') }}
									<input type="number" class="form-control" name="numid" id="numid" required>
								</div>
								<div class="form-group">
									{{ Form::label('nombres', 'Nombres') }}
									<input type="text" class="form-control" name="nombres" id="nombres" required>
								</div>
								<div class="form-group">
									{{ Form::label('apellidos', 'Apellidos') }}
									<input type="text" class="form-control" name="apellidos" id="apellidos" required>
								</div>
								<div class="form-group">
									{{ Form::label('celular', 'Celular') }}
									<input type="number" class="form-control" name="celular" id="celular">
								</div>
								<div class="form-group">
									{{ Form::label('email', 'Email') }}
									<input type="email" class="form-control" name="email" id="email">
								</div>
								<div class="form-group m-b-30">
									{{ Form::label('password', 'Contraseña') }}
									<input type="password" class="form-control" minlength="8" name="password" id="password" required>
									<button type="button" class="btn btn-outline-primary fa fa-eye-slash clpass" style="float: right"></button>
								</div>
								<div class="form-group m-b-30">
									{{ Form::label('password2', 'Repetir contraseña') }}
									<input type="password" class="form-control" minlength="8" name="password2" id="password2" required>
									<button type="button" class="btn btn-outline-primary fa fa-eye-slash clpass" style="float: right"></button>
								</div>
								<div class="text-center m-t-40">
									<button type="submit" class="btn btn-dark">Registrarse</button>
								</div>
							{{ Form::close() }}
						</div>
					</div>
					<div class="col-md-6" style="margin: auto">
						<img src="/img/logo_cahors.png" class="rounded mx-auto d-block" alt="Taxiseguro">
						<div class="text-center">
							<ul style="color: rgb(46,59,116);list-style-type: none;font-size: x-large;font-weight: bold;">
								<li>Préstamo libre inversión</li>
								<li>Solicitud en línea</li>
							</ul>
							<a href="/" class="btn btn-lg btn-primary w-md waves-effect waves-light" style="width: 100%; margin-bottom: 20px">Ingresar</a>
						</div>					
					</div>
				</div>				
			</div>
		</div>
		<div class="m-t-40 text-center">
			<p>© 2021 CAHORS
		</div>
	</div>
@endsection
@section('script')
	<script>
		$(".clpass").on("click", function () {
			let boton = $(this).prev()[0];
			if(boton.type == "password"){
				boton.type = "text";
				$(this).removeClass('fa fa-eye-slash').addClass('fa fa-eye');				
			}else{
				boton.type = "password";
				$(this).removeClass('fa fa-eye').addClass('fa fa-eye-slash');
			}
		});

		$("#formreg").submit(function (ev) {
			let datos = {}; 
			$(this).serializeArray().map(function(x){datos[x.name] = x.value;});

			if(datos.password != datos.password2){
				ev.preventDefault();
				$("#load").hide();
				Swal.fire(
					'Contraseñas incorrectas',
					'Las contraseñas ingresadas no coinciden',
					'error'
				);
			}	
		});
	</script>
@endsection