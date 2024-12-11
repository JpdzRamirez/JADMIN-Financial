@extends('layouts.logeado')

@section('style')
   <style>
    .treeview-animated {
        font-size: 16px;
        font-weight: 400;
        background: rgba(225, 225, 225, 0.2);
    }

    .treeview-animated hr {
        border-color: white;
    }

    .treeview-animated.w-20 {
        width: 20rem;
    }

    .treeview-animated h6 {
        font-size: 1.4em;
        font-weight: 500;
        color: white;
    }

    .treeview-animated ul {
        position: relative;
        list-style: none;
        padding-left: 0;
    }

    .treeview-animated-list ul {
        padding-left: 1em;
        margin-top: 0.1em;
        background: rgba(225, 225, 225, 0.2);
    }

    .treeview-animated-element {
        padding: 0.2em 0.2em 0.2em 1em;
        cursor: pointer;
        transition: all .1s linear;
        border: 2px solid transparent;
        border-right: 0px solid transparent;
    }

    .treeview-animated-element:hover {
    background-color: lightblue;
    }

    .treeview-animated-element.opened {
        color: navy;
        border: 2px solid navy;
        border-right: 0px solid transparent;
        background-color: lightblue;
    }

    .treeview-animated-element.opened:hover {
        color: navy;
    }

    .treeview-animated-items-header {
        display: block;
        padding: 0.4em;
        margin-right: 0;
        border-bottom: 2px solid transparent;
    }


    .treeview-animated-items-header:hover {
        background-color: lightblue
    }

    .treeview-animated-items-header.open {
        transition: all .1s linear;
        border-bottom: 2px solid navy;
    }

    .treeview-animated-items-header.open span {
        color: navy;
    }

    .treeview-animated-items-header.open:hover {
        color: navy;
    }

    .treeview-animated-items-header.open div:hover {

    }

    .treeview-animated-items-header .fa-angle-right {
        transition: all .1s linear;
        font-size: .8rem;
    }

    .treeview-animated-items-header .fas {
        position: relative;
        transition: all .2s linear;
        transform: rotate(90deg);
        color: navy;
    }

    .treeview-animated-items-header .fa-minus-circle {
        position: relative;
        color: navy;
        transform: rotate(180deg);
    }
   </style>
@endsection

@section('sub_title', 'Plan de Cuentas')

@section('sub_content')
	<div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-7">
              <div class="treeview-animated border border-secondary">
                <hr>
                <ul class="treeview-animated-list mb-3">
                    @foreach ($cuentas as $cuenta)
                        <li class="treeview-animated-items">
                            <a class="treeview-animated-items-header">
                                <i class="fas fa-plus-circle"></i>
                                <span><i class="far fa-folder ic-w mx-1"></i>{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</span>
                            </a>
                            <ul class="nested" id="{{ $cuenta->id }}"></ul>
                        </li>
                    @endforeach
                </ul>
              </div>
            </div>
            <div class="col-md-5">
                <form action="/contabilidad/plan_cuentas/modificar" method="POST" id="formcuenta">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <input type="hidden" name="tipo" id="tipo">
                    <div class="row form-group">
                        <label for="padre" class="col-md-3">Cuenta padre</label>
                        <div class="col-md-9"><input type="text" class="form-control" name="padre" id="padre"></div>
                    </div>
                    <div class="row form-group">
                        <label for="codigo" class="col-md-3">Código</label>
                        <div class="col-md-9"><input type="text" class="form-control" id="codigo" name="codigo"></div>
                    </div>
                    <div class="row form-group">
                        <label for="descripcion" class="col-md-3">Descripción</label>
                        <div class="col-md-9"><input type="text" class="form-control" id="descripcion" name="descripcion"></div>
                    </div>
                    <div class="row form-group">
                        <label for="aturaleza" class="col-md-3">Naturaleza</label>
                        <div class="col-md-9">
                            <select id="naturaleza" name="naturaleza" class="form-control">
                                <option value="Débito">Débito</option>
                                <option value="Crédito">Crédito</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center m-t-30">
                        <button type="button" onclick="accion(1);" class="btn btn-sm btn-primary">Actualizar</button>
                        <button type="button" onclick="accion(2);" class="btn btn-sm btn-success">Agregar</button>
                    </div>
                </form>
            </div>
          </div>
            
        </div>
    </div> 
            		
@endsection
@section('modal')
    <div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo costo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="/costos/nuevo" method="POST">
                    <div class="modal-body">
                        <div class="row form-group">
                            <label for="descripcion" class="col-md-3 label-required">Descripción del costo</label>                         
                            <div class="col-md-9">
                                <input type="text" name="descripcion" id="descripcion" class="form-control">	
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="descripcion" class="col-md-3 label-required">Tipo de valor del costo</label>                         
                            <div class="col-md-9">
                                <select name="tipo" id="tipo" class="form-control">
                                    <option value="Absoluto">Absoluto</option>
                                    <option value="Porcentual">Porcentual</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="tipo" class="col-md-3 label-required">Valor</label>                         
                            <div class="col-md-9">
                                <input type="number" step="0.1" name="valor" id="valor" min="0" class="form-control">
                            </div>
                        </div>					
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <button type="submit" class="btn btn-success">Guardar</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

        function accion(tipo) {
            if(tipo == 1){
                $("#tipo").val("1");
            }else{
                $("#tipo").val("2");
            }
            $("#formcuenta").submit();
        }

        let $allPanels = $('.nested').hide();
       //let $elements = $('.treeview-animated-element');

        $(document).on("click", ".treeview-animated-items-header", async function () {
            $("#formcuenta")[0].reset();
            $this = $(this);            
            $target = $this.siblings('.nested');
            infoNodo($target.attr("id"));
            $pointerPlus = $this.children('.fa-plus-circle');
            $pointerMinus = $this.children('.fa-minus-circle');

            $pointerPlus.removeClass('fa-plus-circle');
            $pointerPlus.addClass('fa-minus-circle');
            $pointerMinus.removeClass('fa-minus-circle');
            $pointerMinus.addClass('fa-plus-circle');
            $this.toggleClass('open')
            if (!$target.hasClass('active')) {
                $this.children('span').children(':first').removeClass("fa-folder").addClass("fa-folder-open");
                if($target.is(':empty')){          
                    await nodos($target.attr("id"));
                }
                $target.addClass('active').slideDown();           
            } else {
                $this.children('span').children(':first').removeClass("fa-folder-open").addClass("fa-folder");                             
                $target.removeClass('active').slideUp();                  
            }

            return false;
        });

        $(document).on("click", ".treeview-animated-element", function () {
            $("#formcuenta")[0].reset();
            $this = $(this);
            infoNodo($this.attr("id"));
            let $elements = $('.treeview-animated-element');
            if ($this.hasClass('opened')) {
              $elements.removeClass('opened');
            } else {
              $elements.removeClass('opened');
              $this.addClass('opened');
            }
        });

        function infoNodo(id) {
            return $.ajax({
                type: "get",
                url: "/contabilidad/info_nodo/" + id,
                dataType: "json"
            }).done(function (data) {
                if(data != null){
                    $("#codigo").val(data.codigo);
                    $("#descripcion").val(data.nombre);
                    $("#naturaleza").val(data.naturaleza);
                    if(data.padre != null){
                        $("#padre").val(data.padre.codigo);
                    }
                }
            });
        }

        function nodos(id) {
            return $.ajax({
                type: "get",
                url: "/contabilidad/get_nodos/" + id,
                dataType: "json"
            }).done(function (data) {
                if(data.length > 0){
                    for (const key in data) {
                      if(data[key].cuentas.length > 0){
                        $("#"+id).append('<li class="treeview-animated-items"><a class="treeview-animated-items-header"><i class="fas fa-plus-circle"></i>' +
                                '<span><i class="far fa-folder ic-w mx-1"></i>' + data[key].codigo + ' - ' + data[key].nombre + '</span></a>' +
                                '<ul class="nested" id="' + data[key].id + '"></ul></li>');
                      }else{
                        $("#"+id).append('<li><div class="treeview-animated-element" id="' + data[key].id + '"><i class="far fa-folder ic-w mr-1"></i>' + data[key].codigo + ' - ' + data[key].nombre + '</li>');
                      }
                    }
                }else{
                  console.log("0 nodos");
                }
            });
        }
    </script>
@endsection