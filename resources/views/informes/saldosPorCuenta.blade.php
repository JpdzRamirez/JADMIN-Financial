@extends('layouts.logeado')

@section('sub_title', 'Saldos Por Cuenta')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <form action="/contabilidad/informes/mostrar_saldos" method="get" id="formsaldos">
                <div class="row form-group">
                    <label for="fechain" class="col-md-2">Fecha final</label>
                    <div class="col-md-6">
                        @if (isset($fechafi))
                            <input type="date" name="fechafi" id="fechafi" value="{{explode(" ", $fechafi)[0]}}" class="form-control">
                        @else
                            <input type="date" name="fechafi" id="fechafi" value="{{$fecha->format('Y-m-d')}}" class="form-control">
                        @endif
                        
                    </div>
                </div>
                <div class="row form-group">
                    <label for="cuentain" class="col-md-2">Cuenta</label>
                    <div class="col-md-6">
                        <input type="text" name="cuenta" id="cuenta" class="cta form-control">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-dark">Mostrar</button>
                </div>
            </form>
            @if (isset($terceros))
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Identificacion</th>
                            <th>Nombre</th>
                            <th>Débitos</th>
                            <th>Créditos</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($terceros as $tercero)
                            @php
                                $debitos = 0;
                                $creditos = 0;
                                foreach ($tercero->movimientos as $movimiento) {
                                    if($movimiento->naturaleza == "Débito"){
                                        $debitos = $debitos + $movimiento->valor;
                                    }else{
                                        $creditos = $creditos + $movimiento->valor;
                                    }
                                }
                            @endphp
                            @if ($debitos-$creditos != 0)
                                <tr>
                                    <td>{{$tercero->documento}}</td>
                                    <td>{{$tercero->nombre}}</td>
                                
                                    <td>{{number_format($debitos, 2, ",", ".")}}</td>
                                    <td>{{number_format($creditos, 2, ",", ".")}}</td>
                                    <td>{{number_format($debitos-$creditos, 2, ",", ".")}}</td>
                                </tr>
                            @endif  
                        @endforeach
                    </tbody>
                </table> 
            @endif
		</div>
	</div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $("#load").remove();
            @if(isset($terceros))
                $("#cuenta").val({{$cuenta}});
                $("#fechafi").val('{{explode(" ", $fechafi)[0]}}');
            @endif
        });

        $(".cta").autocomplete({
      		source: function( request, response ) {
                $.ajax({
                    url: "/contabilidad/cuentas/buscar",
                    dataType: "json",
                    data: {cuenta: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.codigo + "_" + item.nombre;
							item.value = item.codigo;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3
        });
        
        $(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formfiltro").submit();
    		}
		});
    </script>
@endsection