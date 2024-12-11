@extends('layouts.logeado')

@section('sub_title', 'Renovar Seguros')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if (session("error"))
                <div class="alert alert-danger">
                    <h5>Error: {{session("error")}}</h5>
                </div>
            @endif
            <form action="/seguros/facturar" method="post" id="formrenovar">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="row">
                    <label for="concepto" class="col-3">Concepto</label>
                    <div class="col-9">
                        <input type="text" name="concepto" id="concepto" class="form-control">
                    </div>
                </div>
            </form>
            <table class="table table-bordered mt-3" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tercero</th>
                        <th>Primer Vencimiento</th>
                        <th>Facturadas</th>
                        <th>Pagadas</th>
                        <th>Renovar</th>       
                    </tr>
                </thead>
                <tbody>
                    @forelse($seguros as $seguro)
                        <tr>
                            <td>{{ $seguro->id }}</td>
                            <td>{{ $seguro->vencimiento }}</td>                             
                            <td>{{ $seguro->tercero->documento }}-{{ $seguro->tercero->nombre }}</td>
                            <td>{{ $seguro->facturadas}}</td>
                            <td>{{ $seguro->pagadas}}</td>                                    
                            <td>
                                <input type="checkbox" name="seguros[]" value="{{$seguro->id}}" form="formrenovar" class="form-control">
                            </td>
                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="6">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="text-center">
                <button type="submit" form="formrenovar" class="btn btn-dark">Renovar seleccionados</button>
            </div>			
		</div>
	</div>
@endsection