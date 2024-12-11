@extends('layouts.plantilla')

@section('content')
<div class="header-bg">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <h4 class="page-title">
                        <img src="/img/logo.png" width="200px" height="200px">	 
                        @yield('sub_title', 'JADMIN')</h4>
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
@endsection
