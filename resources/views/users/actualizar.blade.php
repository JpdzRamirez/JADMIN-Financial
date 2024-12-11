@extends('layouts.logeado')

@section('style')
    <style>
        .tip {
            position: relative;
            display: inline-block;
        }

        .tip .tiptext {
            visibility: hidden;
            width: 50%;
            background-color: lightgray;
            color: red;
            text-align: center;
            padding: 5px 0;
            border-radius: 6px;
        
            /* Position the tooltip text */
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 10%;
            margin-left: -60px;
        
            /* Fade in tooltip */
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tip .tiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 10%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .tip:hover .tiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
@endsection
@section('sub_title', 'Actualizar datos')

@section('sub_content')
<div class="card">
	<div class="card-body">
		{{ Form::model($user, ['route' => $route, 'method' => $method, 'id' => 'formuser'] ) }}
        {{ Form::hidden('id', null) }}
        <div class="form-group row">
            {{ Form::label('usuario', 'Usuario', ['class' => 'col-md-2']) }}
            <div class="col-md-6">
                {{ Form::text('usuario', null, ['readonly', 'class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            {{ Form::label('direccion', 'Dirección', ['class' => 'col-md-2']) }}
            <div class="col-md-6">
                {{ Form::text('direccion', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            {{ Form::label('barrio', 'Barrio', ['class' => 'col-md-2']) }}
            <div class="col-md-6">
                {{ Form::text('barrio', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            {{ Form::label('municipio', 'Municipio', ['class' => 'col-md-2']) }}
            <div class="col-md-6">
                {{ Form::text('municipio', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            {{ Form::label('celular', 'Celular', ['class' => 'col-md-2']) }}
            <div class="col-md-6">
                {{ Form::text('celular', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            {{ Form::label('email', 'Email', ['class' => 'col-md-2']) }}
            <div class="col-md-6">
                {{ Form::text('email', null, ['class' => 'form-control']) }}
            </div>
        </div>
		<div class="form-group row m-b-30">
            {{ Form::label('password', 'Contraseña', ['class' => 'col-md-2']) }}
            <div class="col-md-6 tip">
                <input type="password" class="form-control" minlength="8" name="password" id="password">
                <div id="toolinfo" class="tiptext">
					<h6 style="color: black !important">La contraseña debe tener los siguientes requisitos:</h6>
					<ul style="text-align: left">
						<li id="letter">Al menos <strong>una letra minúscula</strong></li>
						<li id="capital">Al menos <strong>una letra mayúscula</strong></li>
						<li id="number">Al menos <strong>un número</strong></li>
						<li id="length">Por lo menos <strong>8 caracteres</strong></li>
					</ul>
				</div>
            </div>
            <button type="button" class="btn btn-outline-primary fa fa-eye-slash clpass col-md-1"></button>
        </div>
        <div class="form-group row m-b-30">
            {{ Form::label('password2', 'Repetir contraseña', ['class' => 'col-md-2']) }}
            <div class="col-md-6">
                <input type="password" class="form-control" minlength="8" name="password2" id="password2">
            </div>
            <button type="button" class="btn btn-outline-primary fa fa-eye-slash clpass col-md-1"></button>
        </div>
		<div class="form-group text-center">
			{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
		</div>
		{{ Form::close() }}
	</div>
</div>
@endsection
@section('script')
	<script>
		var pasar;
        $('input[type=password]').keyup(function() {
            var pswd = $(this).val();
            pasar = 0;

            if ( pswd.length > 8 ) {
                $('#length').css('color', 'green');
                pasar++;
            } else {
                $('#length').css('color', 'red');
            }

            if ( pswd.match(/[a-z]/) ) {
                $('#letter').css('color', 'green');
                pasar++;
            } else {
                $('#letter').css('color', 'red');
            }

            if ( pswd.match(/[A-Z]/) ) {
                $('#capital').css('color', 'green');
                pasar++;
            } else {
                $('#capital').css('color', 'red');
            }

            if ( pswd.match(/\d/) ) {
                $('#number').css('color', 'green');
                pasar++;
            } else {
                $('#number').css('color', 'red');
            }
        });

        $(".clpass").on("click", function () {
			let boton = $(this).prev().children((":first"))[0];
			if(boton.type == "password"){
				boton.type = "text";
				$(this).removeClass('fa fa-eye-slash').addClass('fa fa-eye');				
			}else{
				boton.type = "password";
				$(this).removeClass('fa fa-eye').addClass('fa fa-eye-slash');
			}
		});

        $("#formuser").submit(function (ev) {
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
