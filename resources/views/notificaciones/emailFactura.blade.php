<html>
    <head>
        <style>
            .boton {
                border: 1px solid #2e518b; 
                padding: 10px; 
                background-color: #2e518b; 
                color: #ffffff; 
                text-decoration: none; 
                text-transform: uppercase;
                font-family: 'Helvetica', sans-serif; 
                border-radius: 50px;
                width: 90%;
                display: inline-block;
                white-space: nowrap;
                line-height: 1.5;
                vertical-align: middle;
                font-size: 1rem;
            }

            td{
                text-align: center;
            }

            .fila{
                background-color: lightgray;
            }
        </style>
    </head>
    <body>
        <table style="width: 50%; margin: 0 auto">
            <tbody>
                <tr>
                    <td colspan="2" style="font-size: 12pt; text-align: left">Apreciado(a)</td>
                </tr>
                <tr class="fila">
                    <th colspan="2" style="text-align: center; font-size: 12pt">
                        @if ($factura->tercero != null)
                            {{ strtoupper($factura->tercero->nombre)}}
                        @else
                            {{ strtoupper($factura->credito->cliente->primer_nombre . ' ' . $factura->credito->cliente->segundo_nombre . ' ' . $factura->credito->cliente->primer_apellido . ' ' . $factura->credito->cliente->segundo_apellido)}}
                        @endif
                    </th>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center">
                        <img src="img/logo.png" alt="jadmin" style="height:100%">
                    </td>
                </tr>
                <tr class="fila">
                    <th>Tipo de Documento:</th>
                    <td>Factura de Venta</td>
                </tr>
                <tr class="fila">
                    <th>Número de Documento:</th>
                    <td>{{$factura->prefijo}}{{$factura->numero}}</td>
                </tr>
                <tr class="fila">
                    <th>Fecha de Expedición:</th>
                    <td>{{$factura->fecha}}</td>
                </tr>
                <tr class="fila">
                    <th>Valor:</th>
                    <td >{{number_format($factura->valor, 2, ".", ",")}}</td>
                </tr>
                <tr>
                    <td colspan="2" style="margin: 10px">
                    <br> Según sus políticas puede continuar con el proceso de aceptación o rechazo del documento </td> 
                </tr>
                <tr style="text-align: center; height: 50px;">
                    <td><a href="http://jadmin/facturas/{{$factura->id}}/procesar?decision=Aceptar" style="color: white" class="boton">Aceptar documento</a></td>
                    <td><a href="http://jadmin/facturas/{{$factura->id}}/procesar?decision=Rechazar" style="color: white" class="boton">Rechazar documento</a></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>