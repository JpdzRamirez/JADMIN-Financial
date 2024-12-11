@extends('layouts.logeado')

@section('sub_title', 'Segmentos de la variable ' . $variable->nombre)

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h3>Inferior</h3>
                </div>
                <div class="col-md-3">
                    <h3>Superior</h3>
                </div>
                <div class="col-md-3">
                    <h3>Puntaje</h3>
                </div>  
            </div>
			<form action="/scoring/{{$variable->id}}/segmentos/editar" method="POST">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div id="contenedor">
                    <div class="row form-group">
                        <div class="col-md-3">
                            <input type="number" name="inferiores[]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="superiores[]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="puntajes[]" class="form-control">
                        </div>  
                    </div>
                    <div class="row form-group">
                        <div class="col-md-3">
                            <input type="number" name="inferiores[]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="superiores[]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="puntajes[]" class="form-control">
                        </div>  
                    </div>
                </div>
                <button type="button" onclick="addItem()" class="btn btn-sm btn-success">Agregar item</button>
                <div class="text-center">
                    <button type="submit" class="btn btn-dark">Enviar</button>
                </div>
            </form>          
		</div>
	</div>
@endsection
@section('script')
    <script>
        function addItem() {
            $("#contenedor").append('<div class="row form-group"><div class="col-md-3"><input type="number" name="inferiores[]" class="form-control"></div><div class="col-md-3"><input type="number" name="superiores[]" class="form-control"></div><div class="col-md-3"><input type="number" name="puntajes[]" class="form-control"></div><div class="col-md-2"><button type="button" class="btn btn-sm btn-danger">Remover</button></div></div></div>');
        }

        $("body").on('click', '.btn-danger', function (ev) {
            $(this).parent().parent().remove();
        });
    </script>
@endsection