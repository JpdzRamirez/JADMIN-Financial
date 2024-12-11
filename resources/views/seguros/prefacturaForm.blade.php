@extends('layouts.logeado')

@section('sub_title', 'Prefactura Aurora')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <form action="/seguros/prefactura" method="post">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="row form-group">
                    <label for="inimes" class="col-md-2 label-required">Mes Inicial</label>
                    <div class="col-md-6 col-xs-10">
                        <input type="month" class="form-control" name="inimes" id="inimes">
                    </div>
                </div>
                <div class="row form-group">
                    <label for="finmes" class="col-md-2 label-required">Mes Final</label>
                    <div class="col-md-6 col-xs-10">
                        <input type="month" class="form-control" name="finmes" id="finmes">
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-dark">Enviar</button>
                </div>
            </form>      
		</div>
	</div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $("#load").remove();
        });
    </script>
@endsection