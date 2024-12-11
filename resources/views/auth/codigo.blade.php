@extends('layouts.plantilla')
@section('style')
	<style>
		input {
			margin: 0 5px;
			text-align: center;
			box-shadow: 0 0 5px #ccc inset;
			font-size: 40px !important;
			height: auto !important;

			&:focus {
				border-color: purple;
				box-shadow: 0 0 5px purple inset;
			}
			
			&::selection {
				background: transparent;
			}
      	}
	</style>
@endsection
@section('content')
	<div class="accountbg"></div>
	<div style="margin: 5.5% auto; position: relative;">
		<div class="card">
			<div class="card-body">
				@if($errors->first('error') != null)
					<div class="alert alert-danger text-center m-t-10">
						<h6>{{$errors->first('error')}}</h6>
					</div>				
				@endif
				<div class="row">
					<div class="col-md-6">
						<img src="/img/logo_cahors.png" class="rounded mx-auto d-block" alt="Taxiseguro">
						<div class="text-center">
							<ul style="color: rgb(46,59,116);list-style-type: none;font-size: x-large;font-weight: bold;">
								<li>Préstamo libre inversión</li>
								<li>Solicitud en línea</li>
							</ul>
						</div>
					</div>
					<div class="col-md-6" style="border: 3px double blue">
						<div class="p-3">							
							<h3 class="m-b-5 text-center" style="color: rgb(46,59,116)">Verificación en Cahors</h3>
							{{ Form::open(['url' => '/register/codigo_verificacion', 'method' => 'post', 'id' => 'formcod', 'class' => 'form-horizontal m-t-30']) }}
								<div class="row">
									<div class="col-12 text-center">
										<label>Ingresa el código enviado a tu celular</label>
									</div>
								</div>
								<div class="row mt-3 text-center no-gutters">
									<div class="col-2">
										<input type="text" class="form-control" name="1" id="1" maxLength="1" pattern="[0-9]{1}" required>
									</div>
									<div class="col-2">
										<input type="text" class="form-control" name="2" id="2" maxLength="1" pattern="[0-9]{1}" required>
									</div>
									<div class="col-2">
										<input type="text" class="form-control" name="3" id="3" maxLength="1" pattern="[0-9]{1}" required>
									</div>
									<div class="col-2">
										<input type="text" class="form-control" name="4" id="4" maxLength="1" pattern="[0-9]{1}"  required>
									</div>
									<div class="col-2">
										<input type="text" class="form-control" name="5" id="5" maxLength="1" pattern="[0-9]{1}" required>
									</div>
									<div class="col-2">
										<input type="text" class="form-control" name="6" id="6" maxLength="1" pattern="[0-9]{1}" required>
									</div>
								</div>

								<div class="text-center m-t-40">
									<button type="submit" class="btn btn-dark">Confirmar registro</button>
								</div>
							{{ Form::close() }}
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
		$(function() {
			'use strict';

			var body = $('body');

			function goToNextInput(e) {
				var key = e.which,
				idTarget = e.target.id,
				sib = $("#" +  (Number(idTarget) + 1));

				if (key != 9 && (key < 48 || key > 57)) {
					e.preventDefault();
					return false;
				}

				if (key === 9) {
					return true;
				}

				if(idTarget == 6){
					sib = $("#1");
				}
				sib.select().focus();
			}

			function onKeyDown(e) {
				var key = e.which;
				if (key === 9 || (key >= 48 && key <= 57)) {
					return true;
				}

				e.preventDefault();
				return false;
			}
			
			function onFocus(e) {
				$(e.target).select();
			}

			body.on('keyup', 'input', goToNextInput);
			body.on('keydown', 'input', onKeyDown);
			body.on('click', 'input', onFocus);
		});
	</script>
@endsection