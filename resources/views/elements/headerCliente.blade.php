<header id="topnav"><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
	<div class="topbar-main">
		<div class="container-fluid">
			<div class="logo">
				<a href="{{ route('home') }}" class="logo">
					JADMIN
				</a>
			</div>
			<div class="menu-extras topbar-custom">
				<ul class="list-inline float-right mb-0">
					<li class="list-inline-item dropdown notification-list">
						<a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown"
							href="#" role="button" aria-haspopup="false" aria-expanded="false">
							<img src="/img/user.png" alt="user" class="rounded-circle">
							<span class="ml-1">{{ Auth::user()->primer_nombre }} {{ Auth::user()->primer_apellido }}<i class="mdi mdi-chevron-down"></i> </span>
						</a>
						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item" href="{{ route('users.editcuenta', ['user' => Auth::id()]) }}">
								<i class="fa fa-cog text-muted"></i> Ajustes
							</a>
							<a class="dropdown-item" href="{{ route('logout') }}">
								<i class="dripicons-exit text-muted"></i> Cerrar sesión
							</a>
						</div>
					</li>
					<li class="menu-item list-inline-item">
						<a class="navbar-toggle nav-link">
							<div class="lines">
								<span></span>
								<span></span>
								<span></span>
							</div>
						</a>
					</li>
				</ul>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="navbar-custom">
		<div class="container-fluid">
			<div id="navigation">
				<ul class="navigation-menu">

					<li class="has-submenu">
						<a href="/mis_creditos"><i class="fa fa-money" aria-hidden="true"></i> Mis créditos</a>
					</li>

					<li class="has-submenu">
						<a href="/mis_creditos/nuevo"><i class="fa fa-handshake-o" aria-hidden="true"></i> Solicitar Crédito</a>
					</li>

					@if (Auth::user()->condicion == "Propietario")
						<li class="has-submenu">
							<a href="/vehiculos/{{Auth::user()->id}}"><i class="fa fa-car" aria-hidden="true"></i> Mis vehiculos</a>
						</li>
					@endif
					
					<li class="has-submenu">
						<a href="/pagos/historial"><i class="fa fa-usd" aria-hidden="true"></i> Historial pagos</a>
					</li>
					
				</ul>
			</div>
		</div>
	</div>
</header>