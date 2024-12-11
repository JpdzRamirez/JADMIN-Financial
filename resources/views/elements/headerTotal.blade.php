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
							<span class="ml-1">{{ Auth::user()->usuario }} <i class="mdi mdi-chevron-down"></i> </span>
						</a>
						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item" href="/users/actualizar/{{Auth::user()->id}}">
								<i class="fa fa-cog text-muted"></i> Ajustes
							</a>
							<a class="dropdown-item" href="/users">
								<i class="fa fa-users" aria-hidden="true"></i> Usuarios
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
						<a href="/contabilidad/plan_cuentas"><i class="fa fa-bar-chart" aria-hidden="true"></i> PUC</a>
					</li>
					<li class="has-submenu">
						<a href="/contabilidad/terceros"><i class="fa fa-users" aria-hidden="true"></i> Terceros</a>
					</li>
					<li class="has-submenu">
						<a href="#">
							<i class="fa fa-book" aria-hidden="true"></i> Contabilización
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/contabilidad/productos">Productos</a>
							</li>
							<li>
								<a href="/contabilidad/reteicas">Reteica</a>
							</li>
							<li>
								<a href="/contabilidad/retefuentes">Retefuente</a>
							</li>
							<li>
								<a href="/contabilidad/reteivas">Reteiva</a>
							</li>
							<li>
								<a href="/contabilidad/formas_pago">Formas de pago</a>
							</li>
							<li>
								<a href="/contabilidad/resoluciones">Resoluciones</a>
							</li>
						</ul>
					</li>
					<li class="has-submenu">
						<a href="#">
							<i class="fa fa-book" aria-hidden="true"></i> Facturas
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/contabilidad/facturas/compras">Compra</a>
							</li>
							<li>
								<a href="/contabilidad/facturas/ventas">Venta</a>
							</li>
							<li>
								<a href="/contabilidad/facturas/soportes">Documentos Soporte</a>
							</li>
						</ul>
					</li>
					<li class="has-submenu">
						<a href="#">
							<i class="fa fa-sliders" aria-hidden="true"></i> Notas
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/contabilidad/notas_credito">Crédito</a>
							</li>
							<li>
								<a href="/contabilidad/notas_debito">Débito</a>
							</li>
							<li>
								<a href="/contabilidad/notas_contables">Contables</a>
							</li>

						</ul>
					</li>
					<li class="has-submenu">
						<a href="#">
							<i class="fa fa-exchange" aria-hidden="true"></i> Comprobantes
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/contabilidad/ingresos">Ingreso</a>
							</li>
							<li>
								<a href="/contabilidad/recibos">Recibos</a>
							</li>
							<li>
								<a href="/contabilidad/egresos">Egreso</a>
							</li>
						</ul>
					</li>
					<li class="has-submenu">
						<a href="#">
							<i class="fa fa-money" aria-hidden="true"></i> Créditos
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/creditos_cobro">En cobro</a>
							</li>
							<li>
								<a href="/creditos_aprobados">Aprobados</a>
							</li>
							<li>
								<a href="/creditos_finalizados">Finalizados</a>
							</li>
							<li>
								<a href="/seguros">Seguros</a>
							</li>
						</ul>
					</li>

					<li class="has-submenu">
						<a href="/creditos/nuevo"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuevo crédito</a>
					</li>

					<li class="has-submenu">
						<a href="/solicitudes_credito"><i class="fa fa-list-alt" aria-hidden="true"></i> Solicitudes</a>
					</li>

					<li class="has-submenu">
						<a href="/pagos/registrar"><i class="fa fa-usd" aria-hidden="true"></i> Registrar pago</a>
					</li>

                    <li class="has-submenu">
						<a href="/pagos/realizados"><i class="fa fa-usd" aria-hidden="true"></i> Pagos</a>
					</li>

					<li class="has-submenu">
						<a href="/clientes"><i class="fa fa-user" aria-hidden="true"></i> Clientes</a>
					</li>

					<li class="has-submenu">
						<a href="/costos"><i class="fa fa-comment-dollar"></i> Costos</a>
					</li>

					<li class="has-submenu">
						<a href="#">
							<i class="fa fa-balance-scale" aria-hidden="true"></i> Tasas
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/tasas_interes">Interés</a>
							</li>
							<li>
								<a href="/tasas_mora">Mora</a>
							</li>
						</ul>
					</li>
					
					<li class="has-submenu">
						<a href="/cartera"><i class="fa fa-suitcase" aria-hidden="true"></i> Cartera</a>
					</li>

					</li>

					<li class="has-submenu">
						<a href="#">
							<i class="fa-solid fa-circle-info"></i> Informes
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/contabilidad/informes/libro_auxiliar">Libro auxiliar</a>
							</li>
							<li>
								<a href="/contabilidad/informes/cartera_generica">Cartera genérica</a>
							</li>
							<li>
								<a href="/contabilidad/informes/balance">Balance</a>
							</li>
							<li>
								<a href="/contabilidad/informes/saldos_cuenta">Saldos por cuenta</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</div>
</header>