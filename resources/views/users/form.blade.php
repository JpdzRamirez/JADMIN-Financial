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

@if ($metodo == "post")
    @section('sub_title', 'Nuevo Usuario')
@else
    @section('sub_title', 'Actualizar Usuario')
@endif

@section('sub_content')
<div class="card">
	<div class="card-body" id="cardb">
        @if($errors->first('sql') != null)
            <div class="alert alert-danger" style="margin:5px 0">
                <h6>{{$errors->first('sql')}}</h6>
            </div>				
        @endif
        @if ($metodo == "post")
            <form action="/users/registrar" method="post">    
        @else
            <form action="/users/edicion" method="post">
                <input type="hidden" name="usuario" id="usuario" value="{{$user->id}}">
        @endif
        <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
        <div id="noedit">
            <div class="form-group row">
                {{ Form::label('identificacion', 'Identificación', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('identificacion', $user->nro_identificacion, ['required', 'class' => 'form-control']) }}
                </div>
            </div>
            <div class="form-group row">
                {{ Form::label('primer_nombre', 'Primer Nombre', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('primer_nombre', $user->primer_nombre, ['required', 'class' => 'form-control']) }}
                </div>
                {{ Form::label('segundo_nombre', 'Segundo Nombre', ['class' => 'col-md-2 text-center']) }}
                <div class="col-md-4">
                    {{ Form::text('segundo_nombre', $user->segundo_nombre, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="form-group row">
                {{ Form::label('primer_apellido', 'Primer Apellido', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('primer_apellido', $user->primer_apellido, ['required', 'class' => 'form-control']) }}
                </div>
                {{ Form::label('segundo_apellido', 'Segundo Apellido', ['class' => 'col-md-2 text-center']) }}
                <div class="col-md-4">
                    {{ Form::text('segundo_apellido', $user->segundo_apellido, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
		<div class="form-group row">
			{{ Form::label('password', 'Contraseña', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-5 tip">
				{{ Form::password('password', ['class'=>'form-control', 'style'=>'width:85%;display:inline']) }}
                <button class="btn-info btn-sm" style="width: 10%" type="button" onclick="mostrarpassword()"><span class="fa fa-eye-slash" id="eye"></span></button>
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
            
		</div>
		<div class="form-group row">
			{{ Form::label('password2', 'Confirmar contraseña', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-5">
				{{ Form::password('password2', ['class'=>'form-control', 'style'=>'width:85%;display:inline']) }}
				<button class="btn-info btn-sm" style="width: 10%" type="button" onclick="mostrarpassword2()"><span class="fa fa-eye-slash" id="eye2"></span>
			</div>
		</div>

		<div class="form-group row">
			{{ Form::label('estado', 'Estado', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-4">
				{!! Form::select('estado', [1=>'Activo', 0=>'Inactivo'], $user->estado, ['class'=>'form-control']) !!}
			</div>
		</div>

        <div class="form-group row">
            {{ Form::label('rol', 'Rol', ['class' => 'label-required col-md-2']) }}
            <div class="col-md-4">
                {!! Form::select('rol', [1=>'Finanzas', 3=>'Caja', 4=>'Contabilidad'], $user->rol, ['class'=>'form-control']) !!}
            </div>
        </div>

		<div class="form-group text-center">
			{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
		</div>
        </form>

	</div>
</div>
@endsection

@section('script')
	<script src="{{ mix('/js/formsuser.js') }}"></script>
    <script>
        var pasar;
        $(document).ready(function(){			
            
            @if($metodo == "put")
                $("#noedit :input").attr("readonly", true);
            @endif

            $('#password').keyup(function() {
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
        });	
    </script>
@endsection
