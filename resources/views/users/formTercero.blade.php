@extends('layouts.logeado')

@if (isset($tercero))
    @section('sub_title', 'Editar Tercero')  
@else
    @section('sub_title', 'Nuevo Tercero')
@endif


@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if (isset($tercero))
                <form action="/contabilidad/terceros/actualizar" method="post">
                    <input type="hidden" name="tercero" id="tercero" value="{{$tercero->id}}">
            @else
                <form action="/contabilidad/terceros/registrar" method="post">    
            @endif
                <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">

                <div class="row form-group">
                    <label for="tipo" class="col-md-2 required">Tipo de tercero</label>
                    <div class="col-md-6">
                        <select name="tipo" id="tipo" class="form-control">
                            <option value="Empresa" selected>Empresa</option>    
                            <option value="Persona">Persona</option>                        
                        </select>
                    </div>
                </div>

                <div id="divpersona" style="display: none">
                    <div class="row form-group">
                        <label for="tipoide" class="col-md-2 required">Tipo de identificación</label>
                        <div class="col-md-6">
                            <select name="tipoide" id="tipoide" class="form-control">
                                <option value="Cédula de ciudadanía">Cédula de ciudadanía</option>
                                <option value="Cédula de extranjería">Cédula de extranjería</option>
                                <option value="Pasaporte">Pasaporte</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="numide" class="col-md-2 required">Número de identificación</label>
                        <div class="col-md-6">
                            <input type="number" name="numide" id="numide" class="form-control" disabled required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="primer_apellido" class="col-md-2 required">Primer apellido</label>
                        <div class="col-md-6">
                            <input type="text" name="primer_apellido" id="primer_apellido" class="form-control" disabled required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="segundo_apellido" class="col-md-2">Segundo apellido</label>
                        <div class="col-md-6">
                            <input type="text" name="segundo_apellido" id="segundo_apellido" class="form-control">
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="primer_nombre" class="col-md-2 required">Primer nombre</label>
                        <div class="col-md-6">
                            <input type="text" name="primer_nombre" id="primer_nombre" class="form-control" disabled required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="segundo_nombre" class="col-md-2">Segundo nombre</label>
                        <div class="col-md-6">
                            <input type="text" name="segundo_nombre" id="segundo_nombre" class="form-control">
                        </div>
                    </div>
                </div>

                <div id="divempresa">
                    <div class="row form-group">
                        <label for="nit" class="col-md-2 required">NIT</label>
                        <div class="col-md-6">
                            <input type="text" name="nit" id="nit" class="form-control" required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="dv" class="col-md-2 required">Dígito verificación</label>
                        <div class="col-md-6">
                            <input type="number" name="dv" id="dv" class="form-control" required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="razon" class="col-md-2 required">Razon social</label>
                        <div class="col-md-6">
                            <input type="text" name="razon" id="razon" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div id="editables">
                    <div class="row form-group">
                        <label for="telefono" class="col-md-2">Teléfono</label>
                        <div class="col-md-6">
                            <input type="text" name="telefono" id="telefono" class="form-control" required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="email" class="col-md-2">Email</label>
                        <div class="col-md-6">
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="direccion" class="col-md-2">Dirección</label>
                        <div class="col-md-6">
                            <input type="text" name="direccion" id="direccion" class="form-control" required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="municipio" class="col-md-2">Municipio</label>
                        <div class="col-md-6">
                            <input type="text" name="municipio" id="municipio" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-dark">Registrar</button>
                </div>
            </form> 
		</div>
	</div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            @if(isset($tercero))
                @if($tercero->usuario != null)
                    $("#tipo").val('Persona');
                    $("#numide").val('{{$tercero->usuario->nro_identificacion}}');
                    $("#tipoide").val('{{$tercero->usuario->tipo_identificacion}}');
                    $("#primer_apellido").val('{{$tercero->usuario->primer_apellido}}');
                    $("#segundo_apellido").val('{{$tercero->usuario->segundo_apellido}}');
                    $("#primer_nombre").val('{{$tercero->usuario->primer_nombre}}');
                    $("#segundo_nombre").val('{{$tercero->usuario->segundo_nombre}}');
                    $("#telefono").val('{{$tercero->usuario->celular}}');
                    $("#email").val('{{$tercero->usuario->email}}');
                    $("#direccion").val('{{$tercero->usuario->direccion}}');
                    $("#municipio").val('{{$tercero->usuario->municipio}}');
                    $("#divpersona").css("display", "block");
                    $("#divempresa").css("display", "none");
                    $("#divpersona :input").attr("disabled", true);
                    $("#divempresa :input").attr("disabled", true);
                @else
                    $("#tipo").val('Empresa');
                    $("#nit").val('{{$tercero->empresa->nit}}');
                    $("#dv").val('{{$tercero->empresa->dv}}');
                    $("#razon").val('{{$tercero->empresa->razon_social}}');
                    $("#telefono").val('{{$tercero->empresa->telefono}}');
                    $("#email").val('{{$tercero->empresa->email}}');
                    $("#direccion").val('{{$tercero->empresa->direccion}}');
                    $("#municipio").val('{{$tercero->empresa->municipio}}');
                    $("#divpersona").css("display", "none");
                    $("#divempresa").css("display", "block");
                    $("#divpersona :input").attr("disabled", true);
                    $("#divempresa :input").attr("disabled", true);
                @endif
                $("#tipo").attr("disabled", true);
            @endif
        });

        $("#tipo").change(function (e) { 
            if($(this).val() == "Persona"){
                $("#divpersona").css("display", "block");
                $("#divpersona :input").attr("disabled", false);
                $("#divempresa").css("display", "none");
                $("#divempresa :input").attr("disabled", true);
            }else{
                $("#divpersona").css("display", "none");
                $("#divpersona :input").attr("disabled", true);
                $("#divempresa").css("display", "block");
                $("#divempresa :input").attr("disabled", false);
            }
        });
    </script>
@endsection