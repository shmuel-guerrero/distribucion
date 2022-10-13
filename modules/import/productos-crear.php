<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('subir-compra', $permisos);

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';


?>
<?php require_once show_template('header-configured'); ?>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Crear producto</strong>
	</h3>
</div>
<div class="panel-body">
    <?php if (false) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para realizar acciones hacer click en el siguiente boton:</div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <a href="?/productos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
            </div>
        </div>
        <?php } ?>
        
        <hr>
        <div class="col-md-3  col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Importacion de Excel - Compras</div>
                <div class="panel-body">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <form method="post" action="?/import/subir-compra" enctype="multipart/form-data" id="formulario" autocomplete="off">
                                <div class="form-group">
                                    <label for="archivo" class="control-label font-weight-normal">Archivo Excel:</label>
                                    <input type="file" name="archivo" id="archivo" class="form-control" accept=".xls,.xlsx" data-validation="required size" data-validation-allowing="xls, xlsx" data-validation-max-size="1M">
                                </div>
                                <div class="form-group">
                                    <label for="nro_hoja" class="control-label font-weight-normal">Nro de hoja:</label>
                                    <input type="text" value="1" name="nro_hoja" id="nro_hoja" class="form-control" autocomplete="off" data-validation="required number">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-ok"></span>
                                        <span>Guardar</span>
                                    </button>
                                    <button type="reset" class="btn btn-default">
                                        <span class="glyphicon glyphicon-repeat"></span>
                                        <span>Restablecer</span>
                                    </button>
                                </div>
                            </form>
                            
                
                        </div>
                    </div>            
                </div>
            </div>
        </div>
        <div class="col-md-9 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Datos de compras - Información subida</div>
                <div class="panel-body">
                <div class="col-sm-12 col-md-12">
                        <div class="form-horizontal" id="section_action" hidden>
                            <div class="col-sm-6 col-md-6 text-right">
                                <form action="?/import/confirm-importacion-productos" method="post" id="confirmar-form">
                                    <input type="hidden" name="id_import_ingreso">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-ok"></span>
                                        <span>Confirmar</span>
                                    </button>                                  
                                </form>
                            </div>
                            <div class="col-sm-6 col-md-6 text-left">
                                <form action="?/import/eliminar-importacion-productos" method="post" id="eliminar-form">                                
                                    <input type="hidden" name="id_import_ingreso">
                                    <button type="submit" class="btn btn-danger">
                                        <span class="glyphicon glyphicon-remove"></span>
                                        <span>Eliminar</span>
                                    </button>                                  
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-12">
                        <div class="form-horizontal" id="dats_compra">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Fecha y hora:</label>
                                    <div class="col-md-9">
                                        <p class="form-control-static" id="fecha_compra"></p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Proveedor:</label>
                                    <div class="col-md-9">
                                        <p class="form-control-static" id="proveedor_compra"></p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Tipo de ingreso:</label>
                                    <div class="col-md-9">
                                        <p class="form-control-static">Compra</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Descripción:</label>
                                    <div class="col-md-9">
                                        <p class="form-control-static" id="descrip_compra"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Monto total:</label>
                                    <div class="col-md-9">
                                        <p class="form-control-static" id="total_compra"></p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Almacén:</label>
                                    <div class="col-md-9">
                                        <p class="form-control-static" id="almacen_compra"></p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Empleado:</label>
                                    <div class="col-md-9">
                                        <p class="form-control-static" id="emple_compra"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="ms_clear" hidden>
                            <div class="alert alert-danger">
                                <strong>Advertencia!</strong>
                                <p>No existen ingresos importados.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-12">
                        <div class="table-responsive" id="section_table">
                            <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                                <thead>
                                    <tr class="active">
                                        <th class="text-nowrap">#</th>
                                        <th class="text-nowrap">Código</th>
                                        <th class="text-nowrap">Nombre</th>
                                        <th class="text-nowrap text-right">Categoria</th>
                                        <th class="text-nowrap text-right">Precio </th>
                                        <th class="text-nowrap text-right">Unidad </th>                
                                    </tr>
                                </thead>
                                <tbody>
                                   
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
                                        <th class="text-nowrap text-right"  data-importe></th>                                        
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
					</div>
                </div>
            </div>
        </div>
</div>


<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/sweetalert2.all.min.js"></script>

<!-- <script src="<?= js; ?>/bootstrap-dropzone.min.js"></script> -->

<script>
$(function () {
	$.validate({
		modules: 'basic,file'
	});

    let $loader_mostrar = $('[data-spinner]:first');

    window.addEventListener('load', ()=>{
        
        $.ajax({
            url: '?/import/ultima-importacion-productos',
            type: 'POST',
            success: function(resp){
                let id_importacion = resp;
                $loader_mostrar.show();
                if (id_importacion > 0) {
                    $.ajax({
                        url: '?/import/compra-excel',
                        type: 'POST',
                        data: { id_iimport: id_importacion, tipo_importacion: 'productos'},
                        beforeSend:function(){
                            $loader_mostrar.show();
                        },
                        drawCallback: function(settings) {
                            $loader_mostrar.hide();
                        },
                        success:function(dats){
                            
                            $loader_mostrar.hide();
                            let respuesta = JSON.parse(dats);
                            let compra = respuesta.data.compra;
                            let detalles = respuesta.data.productos;
                            
                            document.querySelector("#fecha_compra").innerHTML = `${compra.fecha_ingreso} <small id="hora_compra">${compra.hora_ingreso} </small>`;
                                document.querySelector("#proveedor_compra").innerHTML = `${compra.nombre_proveedor}`;
                                document.querySelector("#descrip_compra").innerHTML = `${compra.descripcion}`;
                                document.querySelector("#total_compra").innerHTML = `${compra.monto_total}`;
                                document.querySelector("#almacen_compra").innerHTML = `${compra.almacen}`;
                                document.querySelector("#emple_compra").innerHTML = `${compra.empleado}`;   
                                    
                            let tabla = document.querySelector("#table tbody");
                            let cantFilas = detalles.length;
                            let fila = ``;
                            let import_total = 0;
                            detalles.forEach((element, id )=> {                                
                                fila = `<td>${id+1}</td><td>Codigo: ${element.codigo}<br>Cod Barras: <small class="text-success">${element.codigo_barras}</small></td>
                                            <td>${element.nombre}<br>Comprobantes: <small class="text-success">${element.nombre_factura}</small></td>
                                            <td class="text-right ${validarinfo(element.categoria)}">${element.categoria}</td>
                                            <td class="text-right ${validarinfo(element.descripcion)}">${element.precio_actual}</td>
                                            <td class="text-right ${validarinfo(element.unidad)}">${(element.unidad)}</td>`;
                                    tabla.insertAdjacentHTML('beforeend', fila);
                                
                            });

                            document.querySelector("#table tfoot [data-importe]").innerHTML = '';                                
                            document.querySelector("#table tfoot [data-importe]").insertAdjacentHTML('beforeend', (import_total).toFixed(2)); 
                            
                            document.querySelector("#dats_compra").removeAttribute('hidden');
                            document.querySelector("#section_action").removeAttribute('hidden');
                            document.querySelector("#section_table").removeAttribute('hidden');
                            document.querySelector("#ms_clear").setAttribute('hidden', true);
                            document.querySelectorAll("input[name=id_import_ingreso]")[0].value = id_importacion;
                            document.querySelectorAll("input[name=id_import_ingreso]")[1].value = id_importacion;
                        },
                        error:function(){
                            $loader_mostrar.hide();
                        }
                    });
                }else{
                    let dats_compra = document.getElementById("dats_compra");        
                    document.querySelector("#fecha_compra").innerHTML = `fecha <small id="hora_compra">hora</small>`;
                    document.querySelector("#proveedor_compra").innerHTML = `proveedor`;
                    document.querySelector("#descrip_compra").innerHTML = `descrip`;
                    document.querySelector("#total_compra").innerHTML = `total`;
                    document.querySelector("#almacen_compra").innerHTML = `almacen`;
                    document.querySelector("#emple_compra").innerHTML = `emple`;     
                    $loader_mostrar.hide();
                    document.querySelector("#table tfoot [data-importe]").insertAdjacentHTML('beforeend', (0).toFixed(2));
                    let proveedor = document.querySelector("#proveedor_compra").textContent;
                    if (proveedor == 'proveedor') {
                        document.querySelector("#dats_compra").setAttribute('hidden', true);
                        document.querySelector("#section_action").setAttribute('hidden', true);
                        document.querySelector("#section_table").setAttribute('hidden', true);
                        document.querySelector("#ms_clear").removeAttribute('hidden');
                    }
                }
            }
        });

    }); 



    document.getElementById("archivo").addEventListener("change", ()=>{
        let fileName = document.getElementById("archivo").value;
        let idxDot = fileName.lastIndexOf(".") + 1;
        let extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
        if (extFile == "xlsx" || extFile == "xlsb" ) {
            
        }else{
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: `MENSAJE DE ADVERTENCIA EXTENSION NO PERMITIDA ${extFile}`
                });
        }
    });

    document.getElementById("confirmar-form").addEventListener("submit",(event)=>{
        event.preventDefault();
        
        let formularioConfirm = document.getElementById("confirmar-form");
        let actionForm = formularioConfirm.getAttribute("action") ;
        let idIngresoImport = formularioConfirm.querySelector("input[name=id_import_ingreso]").value;
        action_ingreso_importadp(actionForm, idIngresoImport, accion='Confirmar');
    });



    document.getElementById("eliminar-form").addEventListener("submit", (event)=>{
        event.preventDefault();
        let formularioConfirm = document.getElementById("eliminar-form");
        let actionForm = formularioConfirm.getAttribute("action") ;
        let idIngresoImport = formularioConfirm.querySelector("input[name=id_import_ingreso]").value;
        action_ingreso_importadp(actionForm, idIngresoImport, accion='Eliminar');

    });


    document.getElementById("formulario").addEventListener("submit", (e) =>{
        e.preventDefault();
        let archivo = document.getElementById("archivo").value;
        if (archivo.length == 0) {
            return Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: `Debe adjuntar un archivo excel.`
                });
        }

        $loader_mostrar.show();

        var formulario = $("#formulario")[0];
        let formData = new FormData(formulario);
        /* let excel = $("#archivo")[0].files[0];
        formData.append('excel', excel);
 */
        $.ajax({
            url:'?/import/subir-productos',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend:function(){
                $loader_mostrar.show();
            },
            drawCallback: function(settings) {
                $loader_mostrar.hide();
            },
            success:function (resp) {
                let resps = JSON.parse(resp);

                $loader_mostrar.hide();
                switch (resps.estado) {
                    case 'success':
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: `Se subio archivo correctamente.`,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        let datos = resps.responce;
                        document.getElementById("formulario").reset();
                        $loader_mostrar.show();
                        $.ajax({
                            url: '?/import/compra-excel',
                            type: 'POST',
                            data: { id_iimport: datos, tipo_importacion: 'productos'},
                            beforeSend:function(){
                                $loader_mostrar.show();
                            },
                            drawCallback: function(settings) {
                                $loader_mostrar.hide();
                            },
                            success:function(dats){
                                $loader_mostrar.hide();
                                let respuesta = JSON.parse(dats);
                                let compra = respuesta.data.compra;
                                let detalles = respuesta.data.productos;
                                
                                document.querySelector("#fecha_compra").innerHTML = `${compra.fecha_ingreso} <small id="hora_compra">${compra.hora_ingreso} </small>`;
                                document.querySelector("#proveedor_compra").innerHTML = `${compra.nombre_proveedor}`;
                                document.querySelector("#descrip_compra").innerHTML = `${compra.descripcion}`;
                                document.querySelector("#total_compra").innerHTML = `${compra.monto_total}`;
                                document.querySelector("#almacen_compra").innerHTML = `${compra.almacen}`;
                                document.querySelector("#emple_compra").innerHTML = `${compra.empleado}`;    

                                       
                                let tabla = document.querySelector("#table tbody");
                                let cantFilas = detalles.length;
                                let fila = ``;
                                let import_total = 0;
                                detalles.forEach((element, id )=> {
                                    fila = `<td>${id+1}</td><td>Codigo: ${element.codigo}<br>Cod Barras: <small class="text-success">${element.codigo_barras}</small></td>
                                            <td>${element.nombre}<br>Comprobantes: <small class="text-success">${element.nombre_factura}</small></td>
                                            <td class="text-right ${validarinfo(element.categoria)}">${element.categoria}</td>
                                            <td class="text-right ${validarinfo(element.descripcion)}">${element.precio_actual}</td>
                                            <td class="text-right ${validarinfo(element.unidad)}">${(element.unidad)}</td>`;
                                    tabla.insertAdjacentHTML('beforeend', fila);
                                    
                                });
                                document.querySelector("#table tfoot [data-importe]").innerHTML = '';                                
                                document.querySelector("#table tfoot [data-importe]").insertAdjacentHTML('beforeend', (0).toFixed(2)); 
                                
                                document.querySelector("#dats_compra").removeAttribute('hidden');
                                document.querySelector("#section_action").removeAttribute('hidden');
                                document.querySelector("#section_table").removeAttribute('hidden');
                                document.querySelector("#ms_clear").setAttribute('hidden', true);
                                document.querySelectorAll("input[name=id_import_ingreso]")[0].value = `${compra.id_ingreso}`;
                                document.querySelectorAll("input[name=id_import_ingreso]")[1].value = `${compra.id_ingreso}`; 

                            },
                            error:function(){
                                $loader_mostrar.hide();
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'error',
                                    title: `ERROR: CONEXION A BASE DE DATOS.`,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        });


                        break;
                    case 'warning':
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: `OBSERVACION: ${resps.responce}`,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        break;			
                    case 'error':
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: `ERROR: ${resps.responce}`,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        break;			
                    default:
                        //console.log(resps);
                        Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: `ERROR EN EL FORMULARIO <br> <h1 class="text-danger">${resps.msg} </h1>`,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            break;
			    }    
            },
            error:function(e){
                $loader_mostrar.hide();
                //console.log(e);
                Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: `ERROR: CONEXION A BASE DE DATOS.`,
                                showConfirmButton: false,
                                timer: 1500
                            });
            }
        })

    });

});


function action_ingreso_importadp(actionForm = '', idIngresoImport = 0, accion='') {

    let $loader_mostrar = $('[data-spinner]:first');

    let titulo = (accion == 'Confirmar') ? 'Ingreso Registrado!': 'Ingreso Eliminado';
    let descrip = (accion == 'Confirmar') ? 'El ingreso y sus detalles se registraron el la base de datos.': 'El ingreso y sus detalles importados se eliminaron.';
    let tipo = (accion == 'Confirmar') ? 'success': 'error';

    let tituloConfirm = (accion == 'Confirmar') ? '<h2 class="text-success">Esta seguro de guardar el ingreso en la base de datos?</h2>': '<h2 class="text-danger"> Esta seguro de eliminar el ingreso importado?</h2>';
    let descripconfirm = (accion == 'Confirmar') ? 'Esta accion es irreversible, ya que afectara el inventario real del sistema!': 'Esta accion no repercute en el inventario.';

    Swal.fire({
        title: tituloConfirm,
        width: 800,
        text: descripconfirm,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, estoy seguro!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    titulo,
                    descrip,
                    tipo
                )
                    //console.log("confirmacion");
                    //debugger;
                $loader_mostrar.show();
                $.ajax({
                    url: actionForm,
                    type: 'POST',
                    data: { idIngresoImport : idIngresoImport},
                    beforeSend:function(){
                        $loader_mostrar.show();
                    },
                    drawCallback: function(settings) {
                        $loader_mostrar.hide();
                    },
                    success:function (resp){
                        console.log(resp);
                        $loader_mostrar.hide();
                        //console.log("se guarda en base de datos");
                        Swal.fire({
                                position: 'top-end',
                                icon: tipo,
                                title: titulo,                                
                                showConfirmButton: false,
                                timer: 2500
                            });    
                        setInterval(location.reload(), 5000);
                    },
                    error:function(e){
                        //console.log(e);
                        $loader_mostrar.hide();
                        Swal.fire({
                                    position: 'top-end',
                                    icon: 'error',
                                    title: `ERROR: CONEXION A BASE DE DATOS.`,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                    }
                }); 
            }
        })
}


function validarinfo(dato) {
    //dato = +dato;
    return (dato != '' && dato > 0 && (Number.isInteger(dato) || !isNaN(dato) || parseInt(dato) || parseFloat(dato))) ? '': 'text-danger';    
}
</script>
<?php require_once show_template('footer-configured'); ?>