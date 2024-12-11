@extends('layouts.logeado')

@section('sub_title', 'Cartera Generica')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <form action="/contabilidad/informes/cartera_generica" method="get" id="formcartera">
                <div class="row form-group">
                    <label for="tercero" class="col-md-2">Tercero</label>
                    <div class="col-md-6">
                        <input type="text" name="tercero" id="tercero" class="form-control">
                    </div>
                </div>
                <div class="text-center" style="margin-top: 30px">
                    <button type="button" class="btn btn-dark" onclick="descargar();"><i class="fa fa-download" aria-hidden="true"></i> Descargar</button>
                </div>
            </form>
		</div>
	</div>
@endsection
@section('script')
    <script>

        function descargar() {
            Swal.fire({
                title: '<strong>Generando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
            $.ajax({
                type: "get",
                url: "/contabilidad/informes/descargar_cartera",
                data: $("#formcartera").serialize()
            }).done(function (data) {
                Swal.close();
                const byteCharacters = atob(data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);

                var csvFile;
                var downloadLink;

                filename = "Cartera generica.xlsx";
                csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
                downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();

            }).fail(function () {  
                Swal.close();
                Swal.fire('Error', 'No fue posible descargar el informe', 'error');
            });
        }

        $("#tercero").autocomplete({
      		source: function(request, response) {
                $.ajax({
                    url: "/contabilidad/terceros/buscar",
                    dataType: "json",
                    data: {tercero: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.documento + "-" + item.nombre;
							item.value = item.documento;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3
        });
        
    </script>
@endsection