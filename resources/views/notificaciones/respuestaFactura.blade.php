@extends('layouts.notificaciones')

@section('sub_title', "Factura Electrónica de Venta: " . $factura->prefijo . " " . $factura->numero)

@section('sub_content')
	<div class="card">
		<div class="card-body" style="min-height: 200px">
            @if ($factura->decision == "Rechazar")
                <div class="alert alert-danger text-center" style="min-height: 80px; font-size: x-large">
                    La notificación de rechazo para la factura {{$factura->prefijo}} {{$factura->numero}} fue enviada.
                </div>
            @elseif($factura->decision == "Aceptar")
                <div class="alert alert-success text-center" style="min-height: 80px; font-size: x-large">
                    La notificación de aceptación para la factura {{$factura->prefijo}} {{$factura->numero}} fue enviada.
                </div>
			@endif
		</div>
	</div>
@endsection