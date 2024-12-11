@extends('layouts.logeado')

@if ($method == "post")
    @section('sub_title', 'Nuevo cliente')
@else
    @section('sub_title', 'Editar cliente: ' . $user->nro_identificacion)
@endif
@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if($errors->first('sql') != null)
                <div class="alert alert-danger" style="margin:10px 0">
                    <h6>{{$errors->first('sql')}}</h6>
                </div>				
            @endif

            {{ Form::model($user, ['route' => $route, 'method' => $method, 'id' => 'formuser'] ) }}
            {{ Form::hidden('id', null) }}

            <div class="form-group row">
                {{ Form::label('tipo_identificacion', 'Tipo de identificación', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    <select class="form-control edit" name="tipo_identificacion" id="tipo_identificacion" required>
                        <option value="Cédula de ciudadanía">Cédula de ciudadanía</option>
                        <option value="Cédula de extranjería">Cédula de extranjería</option>
                    </select>
                </div>
            
                {{ Form::label('nro_identificacion', 'Identificación', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('nro_identificacion', null, ['required', 'class' => 'form-control edit']) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('primer_nombre', 'Primer nombre', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('primer_nombre', null, ['required', 'class' => 'form-control edit']) }}
                </div>

                {{ Form::label('segundo_nombre', 'Segundo nombre', ['class' => 'col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('segundo_nombre', null, ['class' => 'form-control edit']) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('primer_apellido', 'Primer apellido', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('primer_apellido', null, ['required', 'class' => 'form-control edit']) }}
                </div>

                {{ Form::label('segundo_apellido', 'Segundo apellido', ['class' => 'col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('segundo_apellido', null, ['class' => 'form-control edit']) }}
                </div>
            </div>  

            <div class="form-group row">
                {{ Form::label('email', 'Email', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::email('email', null, ['required', 'class' => 'form-control']) }}
                </div>

                {{ Form::label('celular', 'Celular', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('celular', null, ['required', 'class' => 'form-control']) }}
                </div>
			</div>

            <div class="form-group row">
                {{ Form::label('direccion', 'Dirección', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('direccion', null, ['required', 'class' => 'form-control']) }}
                </div>
                {{ Form::label('barrio', 'Barrio', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('barrio', null, ['required', 'class' => 'form-control']) }}
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('municipio', 'Municipio', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-4">
                    {{ Form::text('municipio', null, ['required', 'class' => 'form-control']) }}
                </div>           
            </div>

            <div class="form-group row">
                {{ Form::label('condicion', 'Condición', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-6 col-xs-10">
                   {!! Form::select('condicion', $condiciones, $user->condicion, ['required', 'class' => 'form-control']) !!}
                </div>
			</div>

            <div class="form-group row">
                {{ Form::label('proceso', 'En Proceso?', ['class' => 'col-md-2']) }}
                <div class="col-md-4">
                    @if ($user->proceso == 1)
                        <input type="checkbox" id="proceso" name="proceso" class="form-control" checked>
                    @else
                        <input type="checkbox" id="proceso" name="proceso" class="form-control">
                    @endif
                </div>           
            </div>

            <div class="form-group text-center">
                {!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn m-t-30 btn-lg btn-dark']) !!}
            </div>
		    {{ Form::close() }}
		</div>
	</div>
@endsection

@section('script')
    <script>

        $(document).ready(function () {

            @if($method == "put")
                $(".edit").attr("disabled", true);
            @endif
        });    
    </script>
@endsection