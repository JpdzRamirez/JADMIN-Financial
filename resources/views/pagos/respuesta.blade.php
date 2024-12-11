@extends('layouts.logeado')

@section('sub_title', 'Pagar cuota')

@section('sub_content')
	<div class="card">
		<div class="card-body" style="min-height: 200px">
            @if ($respuesta == "error")
                <div class="alert alert-danger text-center" style="min-height: 80px; font-size: x-large">
                    Esta cuota no está disponible
                </div>
            @elseif($respuesta == "correcto")
                <div class="alert alert-success text-center" style="min-height: 80px; font-size: x-large">
                    El pago de la cuota fue exitoso.
                </div>
			@endif
		</div>
	</div>
@endsection