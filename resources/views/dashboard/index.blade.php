@extends('layouts.logeado')

@section('sub_title', 'Dashboard')

@section('sub_content')
	<div class="row">
			<div class="col-md-3">
				<div class="card text-center m-b-30">
					<div class="mb-2 card-body text-muted">
						<h3><a href="#"  class="text-success">0</a></h3>
							Créditos pendientes
					</div>
				</div>
			</div>
			<div class="col-md-3">
                <div class="card text-center m-b-30">
                    <div class="mb-2 card-body text-muted">
                        <h3><a href="#" class="text-warning">0</a></h3>
                            Créditos finalizados
                    </div>
                </div>
			</div>
			<div class="col-md-3">
                <div class="card text-center m-b-30">
                    <div class="mb-2 card-body text-muted">
                        <h3><a href="#" class="text-default">0</a></h3>
                            Créditos atrasados
                    </div>
                </div>
			</div>
			<div class="col-md-3">
				<div class="card text-center m-b-30">
					<div class="mb-2 card-body text-muted">
						<h3><a href="#" class="text-danger">0</a></h3>
							Total de créditos
					</div>
				</div>
			</div>
		
	</div>

	<div class="row" style="margin-top:30px">
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h4 class="mt-0 m-b-30 header-title" style="color: darkblue">Créditos en el Mes</h4>
					<div id="distribucion"></div>
				</div>
			</div>
		</div>	 
    </div>

<div class="row" style="margin-top: 30px">
	<div class="col-md-12">
	<div class="card">
		<div class="card-body">
			<h4 class="mt-0 m-b-30 header-title" style="color: darkblue">Historial de créditos</h4>
			<div id="historial"></div>
		</div>
	</div>
</div>
</div>
@endsection
@section('script')
    <script src="{{ mix('/js/apexchart/apexcharts.js') }}"></script>
	<script>

		$(document).ready(function () {
			var options = {
            chart: {
                width: 450,
                type: 'pie',
            },
            labels: ['Aprobados', 'Rechazados'],
            series: [34, 21],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        }

        var chart = new ApexCharts(
            document.querySelector("#distribucion"),
            options
        );

        chart.render();
		});

		var meses = "Enero,Febrero,Marzo,Abril,Mayo,Junio";

		var options = {
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            series: [{
                name: "Créditos",
                data: [23,34,21,14,32,25],
            }],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: meses.split(','),
            }
        }

        var chart = new ApexCharts(
            document.querySelector("#historial"),
            options
        );

        chart.render();
		
	</script>
@endsection
