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
                    <td colspan="3" style="font-size: 12pt; text-align: left">Resoluciones próximas a vencer</td>
                </tr>
                <tr>
                    <th>Prefijo</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                </tr>
                @foreach ($resoluciones as $resolucion)
                    @if ($resolucion->alertar)
                        <tr class="fila">
                            <td>{{$resolucion->prefijo}}</td>
                            <td>{{$resolucion->fechain}}</td>
                            <td>{{$resolucion->fechafi}}</td>
                        </tr>
                    @endif
                @endforeach
                <tr style="text-align: center; height: 50px;">
                    <td colspan="3"><a href="http://jadmin/contabilidad/resoluciones" style="color: white" class="boton">Ir a resoluciones</a></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>