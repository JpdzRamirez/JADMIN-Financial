@extends('layouts.logeado')

@section('sub_title', 'Balance de comprobación')

@section('sub_content')
	<div class="card">
		<div class="card-body">
            <form action="/contabilidad/informes/balance" method="get" id="formbalance">
                <div class="row form-group">
                    <label for="fechain" class="col-md-2">Fecha incial</label>
                    <div class="col-md-6">
                        <input type="date" name="fechain" value="{{$fecha->format('Y-m-d')}}" id="fechain" class="form-control">
                    </div>
                </div>
                <div class="row form-group">
                    <label for="fechain" class="col-md-2">Fecha final</label>
                    <div class="col-md-6">
                        <input type="date" name="fechafi" id="fechafi" value="{{$fecha->format('Y-m-d')}}" class="form-control">
                    </div>
                </div>
                <div class="row form-group">
                    <label for="cuentain" class="col-md-2">Cuenta</label>
                    <div class="col-md-6">
                        <input type="text" name="cuentain" id="cuentain" class="cta form-control">
                    </div>
                </div>
               <div class="row form-group">
                    <label for="cuentafi" class="col-md-2">Cuenta final</label>
                    <div class="col-md-6">
                        <input type="text" name="cuentafi" id="cuentafi" class="cta form-control">
                    </div>
                </div>
                <div class="row form-group">
                    <label for="terceros" class="col-md-2">Incluir terceros</label>
                    <div class="col-md-6">
                        <select name="terceros" id="terceros" class="form-control">
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
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
        var fecha = new Date();

        function descargar() {
            Swal.fire({
                title: '<strong>Generando...</strong>',
                html:'<img src="/img/carga.gif" height="60px" class="img-responsive" alt="Enviando">',
                showConfirmButton: false,
            });
            $.ajax({
                type: "get",
                url: "/contabilidad/informes/descargar_balance",
                data: $("#formbalance").serialize()
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

                filename = "Balance" + fecha.toLocaleDateString() + ".xlsx";
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

        $(".cta").autocomplete({
      		source: function( request, response ) {
                $.ajax({
                    url: "/contabilidad/cuentas/buscar",
                    dataType: "json",
                    data: {cuenta: request.term},
                    success: function(data) {
                        response($.map(data, function (item) {
							item.label = item.codigo + "_" + item.nombre;
							item.value = item.codigo;
                            return item;
                        }) );
                    }
                });
            },
            minLength: 3
        });
        
    </script>
@endsection