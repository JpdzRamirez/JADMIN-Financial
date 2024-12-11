@extends('layouts.logeado')

@section('sub_title', 'Usuarios')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            @if (session('usuario'))
                <div class="alert alert-success">
                    <h5>El usuario {{ session('usuario') }} ha sido creado.</h5>
                </div>
			@endif
            <div class="align-center">
                <a href="/users/nuevo" class="btn btn-dark btn-sm">Nuevo Usuario</a>
            </div>
            <form action="/users/filtrar" id="formusers" method="GET"></form>
            <table class="table  table-bordered" id="tab_listar" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Rol</th>
                    </tr>
                    <tr>
                        <th><input type="text" name="usuario" class="form-control" form="formusers"></th>
                        <th><input type="text" name="nombres" class="form-control" form="formusers"></th>
                        <th>
                            <select id="estado" name="estado" class="form-control" form="formusers" onchange="this.form.submit()">
                                <option value="sinfiltro"></option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </th>
                        <th>
                            <select id="rol" name="rol" class="form-control" form="formusers" onchange="this.form.submit()">
                                <option value="sinfiltro"></option>
                                <option value="1">Finanzas</option>
                                <option value="3">Caja</option>
                                <option value="4">Contabilidad</option>
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->usuario }}</td>
                            <td>{{ $user->primer_nombre }} {{ $user->primer_apellido }}</td>
                            <td>@if ($user->estado == 1)
                                    Activo
                                @else
                                    Inactivo
                                @endif
                            </td>         
                            <td>@switch($user->rol)
                                    @case(1)
                                        Finanzas
                                        @break
                                    @case(3)
                                        Caja
                                        @break
                                    @case(4)
                                        Contabilidad
                                    @break
                                @endswitch
                            </td>
                            <td>
                                <a href="/users/{{$user->id}}/editar" class="btn btn-warning btn-sm">Actualizar</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="align-center">
                            <td colspan="4">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
		</div>
	</div>
@endsection