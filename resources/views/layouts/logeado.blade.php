@extends('layouts.plantilla')

@section('content')
	<div class="header-bg">
		@if (Auth::user()->rol == 1)
			@include('elements.header')
		@elseif(Auth::user()->rol == 3)
			@include('elements.headerCaja')
		@elseif(Auth::user()->rol == 0)
	        @include('elements.headerTotal')
		@elseif(Auth::user()->rol == 4)
			@include('elements.headerContabilidad')
		@else
			@include('elements.headerCliente')
		@endif
		
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-12">
					<div class="page-title-box">
						<h4 class="page-title">
							<img src="/img/logo.png" width="120px" height="144px">
							@yield('sub_title', 'JADMIN')
						</h4>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="container-fluid">
			@yield('sub_content')
		</div>
	</div>
	<footer class="footer">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					© 2021 JADMIN
				</div>
			</div>
		</div>
	</footer>
@endsection
