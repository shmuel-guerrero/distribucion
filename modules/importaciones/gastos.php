<?php
    if(isset($params[0])){
        if (trim($params[0]) == 1) {
            set_notification('success', 'Acción satisfactoria', 'La preparación de importación se registró correctamente!');
            redirect('?/importaciones/gastos');
        }
    }

    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
    $Consulta = $db->query("SELECT i.nro_correlativo,i.id_importacion,i.fecha_inicio,i.total,i.total_gastos,i.total_costo,i.descripcion,i.nro_registros,a.almacen,e.nombres,e.paterno,e.materno,p.proveedor,i.estado,ig.gasto_total,ig.pago, i.etapa
                            FROM inv_importacion AS i
                            LEFT JOIN inv_almacenes AS a ON i.almacen_id=a.id_almacen
                            LEFT JOIN sys_empleados AS e ON i.empleado_id=e.id_empleado
                            LEFT JOIN inv_proveedores AS p ON i.id_proveedor=p.id_proveedor
                            LEFT JOIN(
                                SELECT importacion_id,SUM(total)AS gasto_total,SUM(pago)AS pago
                                FROM inv_importacion_gasto
                                GROUP BY(importacion_id)
                            )AS ig ON i.id_importacion=ig.importacion_id
                            ORDER BY i.fecha_inicio DESC")->fetch();

    // Obtiene los permisos
    $permisos=explode(',',permits);
    $permiso_editar_preparacion=in_array('editar_preparacion',$permisos);
    $permiso_listar_productos=in_array('listar_productos',$permisos);
    $permiso_listar_gastos=in_array('listar_gastos',$permisos);
    $permiso_historial=in_array('historial',$permisos);
    $permiso_nuevos_gastos=in_array('nuevos_gastos',$permisos);
    $permiso_pagos_pendientes=in_array('pagos_pendientes',$permisos);
    $permiso_listar_ingresos=in_array('listar_ingresos',$permisos);
    $permiso_preparacion=in_array('preparacion',$permisos);
    require_once show_template('header-configured');
?>
<div class='row'>
    <div class='col-md-12'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    <span class='glyphicon glyphicon-list'></span>
                    <strong>Gastos de Importación</strong>
                </h3>
            </div>
        </div>
        <div class='panel-body'>
            <?php if (isset($_SESSION[temporary])) { ?>
                <div class="alert alert-<?= $_SESSION[temporary]['type']; ?>">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong><?= $_SESSION[temporary]['title']; ?></strong>
                    <p><?= $_SESSION[temporary]['content']; ?></p>
                </div>
                <?php unset($_SESSION[temporary]); ?>
            <?php } ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <h2 class='lead'>Listado de Importaciones</h2>
                </div>
                <div class="col-xs-12 col-sm-4 text-right">
                    <?php if ($permiso_preparacion) { ?>
                        <div class="btn-group">
                            <a href="?/importaciones/preparacion" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span>
                                <span>Preparar importación</span>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <hr>

            <input type='hidden' id='FilaF'>
            <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
	            <thead>
                    <tr>
                        <th class="text-nowrap hidden">#</th>
                        <th>Fecha Inicial</th>
                        <!-- <th>Nro. movimiento</th> -->
                        <th>Total Productos <?=$moneda?></th>
                        <th>Total Gastos <?=$moneda?></th>
                        <th>Total Gasto Añadido <?=$moneda?></th>
                        <th>Descripción</th>
                        <th>Nro de Registros</th>
                        <th>Empleado</th>
                        <th>Almacen</th>
                        <th>Proveedor</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="text-nowrap hidden">#</th>
                        <th>Fecha Inicial</th>
                        <!-- <th>Nro. movimiento</th> -->
                        <th>Total Productos <?=$moneda?></th>
                        <th>Total Gastos <?=$moneda?></th>
                        <th>Total Gasto Añadido <?=$moneda?></th>
                        <th>Descripción</th>
                        <th>Nro de Registros</th>
                        <th>Empleado</th>
                        <th>Almacen</th>
                        <th>Proveedor</th>
                        <th data-datafilter-filter="false">Opciones</th>
                    </tr>
                </tfoot>
                <tbody id='DatosF'>
                    <?php
                    foreach ($Consulta as $Fila => $Dato) :
                        $Aux='';
                        if($Dato['gasto_total']>$Dato['pago']):
                            $Aux='text-danger';
                        endif;
                        
                        $datexplode=explode(" ",$Dato['fecha_inicio']);
                    ?>
                        <tr>
                            <th class="text-nowrap hidden"><?= $Fila; ?></th>
                            <td><?= escape(date_decode($datexplode[0], $_institution['formato'])); ?> <small class="text-success"><?= escape($datexplode[1]); ?></small></td>
                            <!-- <td><?= $Dato['nro_movimiento'] ?></td> -->
                            <td class="text-right"><?= number_format($Dato['total'],2,',','.'); ?></td>
                            <td class="text-right"><?= number_format($Dato['total_gastos'],2,',','.'); ?></td>
                            <td class="text-right"><?= number_format($Dato['total_costo'],2,',','.'); ?></td>
                            <td><?= $Dato['descripcion'] ?></td>
                            <td><?= $Dato['nro_registros'] ?></td>
                            <td><?= "{$Dato['nombres']} {$Dato['paterno']} {$Dato['materno']}" ?></td>
                            <td><?= $Dato['almacen'] ?></td>
                            <td><?= $Dato['proveedor'] ?></td>
                            <td>
                                <?php
                                if($permiso_editar_preparacion && $Dato['etapa'] == 'preparacion'):
                                ?>
                                    <a href='?/importaciones/editar_preparacion/<?=$Dato['id_importacion']?>' data-toggle='tooltip' data-placement='top' title='Editar Productos'>
                                        <i class='glyphicon glyphicon-edit'></i>
                                    </a>
                                <?php
                                endif;
                                if($permiso_listar_productos):
                                ?>
                                    <a href='#' onclick="detalleProductos(<?= $Dato['id_importacion'] ?>)" data-toggle='tooltip' data-placement='top' title='Productos'>
                                        <i class='glyphicon glyphicon-search'></i>
                                    </a>
                                <?php
                                if($permiso_historial):
                                ?>
                                    <a target='_blank' href='?/importaciones/compra_inicial/<?=$Dato['id_importacion']?>' data-toggle='tooltip' data-placement='top' title='Historial'>
                                        <i class='glyphicon glyphicon-folder-open'></i>
                                    </a>
                                <?php
                                endif;
                                ?>
                                
                                
                                
                                <?php
                                endif;
                                if($permiso_listar_gastos):
                                ?>
                                    <a href='#' onclick="detalleGastos(<?= $Dato['id_importacion'] ?>)" data-toggle='tooltip' data-placement='top' title='Listar Gastos'>
                                        <i class='glyphicon glyphicon-list-alt'></i>
                                    </a>
                                <?php
                                endif;
                                if($permiso_historial):
                                ?>
                                    <a target='_blank' href='?/importaciones/historial/<?=$Dato['id_importacion']?>' data-toggle='tooltip' data-placement='top' title='Historial'>
                                        <i class='glyphicon glyphicon-book'></i>
                                    </a>
                                <?php
                                endif;
                                if($permiso_nuevos_gastos && $Dato['etapa'] == 'preparacion'):
                                ?>
                                    <a href='?/importaciones/nuevos_gastos/<?= $Dato['id_importacion'] ?>' data-toggle='tooltip' data-placement='top' title='Nuevos Gastos'>
                                        <i class='glyphicon glyphicon-pencil'></i>
                                    </a>
                                <?php
                                endif;
                                if($permiso_pagos_pendientes):
                                ?>
                                    <a class='<?=$Aux?>' href='?/importaciones/pagos_pendientes/<?= $Dato['id_importacion'] ?>' data-toggle='tooltip' data-placement='top' title='Pagos Pendientes'>
                                        <i class='glyphicon glyphicon-usd'></i>
                                    </a>
                                <?php
                                endif;
                                if($permiso_listar_ingresos && $Dato['estado'] == 'activo'):
                                ?>
                                    <a href='#' onclick="ingresarProductos(<?= $Dato['id_importacion'] ?>,<?= $Fila ?>)" data-toggle='tooltip' data-placement='top' title='Ingresar Productos'>
                                        <i class='glyphicon glyphicon-check'></i>
                                    </a>
                                <?php
                                endif;
                                ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class='modal fade' id='VerProductos' tabindex='-1' role='dialog' aria-labelledby='VerModalTitulo' aria-hidden='true'>
        <div class='modal-dialog modal-lg' role='document'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                    <h4 class='modal-title'>Listado de Productos</h4>
                </div>
                <div class='modal-body'>
                    <table class='table table-sm'>
                        <thead>
                            <tr>
                                <th scope='col'>#</th>
                                <th scope='col'>Código</th>
                                <th scope='col'>Nombre</th>
                                <th scope='col'>Vencimiento</th>
                                <th scope='col'>Unidad</th>
                                <th scope='col'>Cantidad</th>
                                <th scope='col'>Precio <?=$moneda?></th>
                                <th scope='col'>SubTotal <?=$moneda?></th>
                            </tr>
                        </thead>
                        <tbody id='ProductosM'>
                        </tbody>
                    </table>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' data-dismiss='modal'>Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class='modal fade' id='VerGastos' tabindex='-1' role='dialog' aria-labelledby='VerModalTitulo' aria-hidden='true'>
        <div class='modal-dialog modal-lg' role='document'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                    <h4 class='modal-title'>Listado de Gastos</h4>
                </div>
                <div class='modal-body margin-none' style='overflow-x:auto'>
                        <table id='GastosM' class='table table-bordered table-condensed table-restructured table-striped table-hover'>

                        </table>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' data-dismiss='modal'>Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class='modal fade' id='IngresoProductos' tabindex='-1' role='dialog' aria-labelledby='VerModalTitulo' aria-hidden='true'>
        <div class='modal-dialog modal-lg' role='document'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                    <h4 class='modal-title'>Listado de Productos</h4>
                </div>
                <form id='ingresoF'>
                    <div class='modal-body'>
                        <div class='form-horizontal'>
                            <div class='form-group'>
                                <label for='contenedorF' class='col-sm-4 control-label'>Contenedor:</label>
                                <div class='col-sm-8'>
                                    <input type='hidden' name='contenedor' id='contenedorF' value='1' class='form-control' autocomplete='off' data-validation='letternumber' data-validation-allowing='+-/.,:;#º()\n ' data-validation-optional='true'>
                                </div>
                            </div>
                            <input type='hidden' name='id_importacion' id='IdImportacionF'>
                        </div>
                        <div style='overflow-x:auto'>
                            <table class='table table-sm'>
                                <thead>
                                    <tr>
                                        <th scope='col'>#</th>
                                        <th scope='col'>Código</th>
                                        <th scope='col'>Nombre</th>
                                        <th scope='col'>Unidad</th>
                                        <th scope='col'>Precio Compra <?=$moneda?></th>
                                        <th scope='col'>Cantidad Solicitada</th>
                                        <th scope='col'>Cantidad Recibida</th>
                                        <th scope='col'>Gastos</th>
                                        <th scope='col'>Costo Venta  <?=$moneda?></th>
                                    </tr>
                                </thead>
                                <tbody id='ProductosIM'>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='submit' class='btn btn-primary'>Ingresar</button>
                        <button type='button' class='btn btn-warning' data-dismiss='modal'>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>

<script src='<?= js ?>/jquery.form-validator.min.js'></script>
<script src='<?= js ?>/jquery.form-validator.es.js'></script>
<script src='<?= js ?>/bootstrap-notify.min.js'></script>
<script>
    $(function () {
    	var table = $('#table').DataFilter({
    		filter: true,
    		name: 'categorias',
    		reports: 'excel|word|pdf|html'
    	});
    });

    let Sal=[];
    //Listado Productos
    function detalleProductos(idImportacion){
        $.ajax({
            data: {},
            type: 'GET',
            dataType: 'json',
            url: '?/importaciones/listar_productos/'+idImportacion,
        })
        .done(function(data,textStatus,jqXHR){
            mostrarProductos(data);
            $('#VerProductos').modal();
        })
        .fail(function(jqXHR,textStatus,errorThrown) {
            console.log(textStatus)
        });
    }
    function mostrarProductos(Datos){
        const Producto=document.getElementById('ProductosM');
        Producto.innerHTML='';
        Datos.forEach((Dato,index)=>{
            
            if(Dato['precio_ingreso']==Dato['precio_salida'] || Dato['precio_salida']==0){
                SubTotal=(Dato['cantidad']*Dato['precio_ingreso']).toFixed(2);
                precio_mostrar=Dato['precio_ingreso'];
            }
            else{
                SubTotal=(Dato['cantidad']*Dato['precio_salida']).toFixed(2);
                precio_mostrar=Dato['precio_salida'];
            }

            Producto.innerHTML+=`<tr>
                        <td>${index+1}</td>
                        <td>${Dato['codigo']}</td>
                        <td>${Dato['nombre']}</td>
                        <td>${Dato['fechav']}</td>
                        <td>${Dato['unidad']}</td>
                        <td>${Dato['cantidad']}</td>
                        <td>`+precio_mostrar+`</td>
                        <td>`+SubTotal+`</td>
                    </tr>`;
        });
    }
    //Listado Gastos
    function detalleGastos(idImportacion){
        $.ajax({
            data: {},
            type: 'GET',
            dataType: 'json',
            url: '?/importaciones/listar_gastos/'+idImportacion,
        })
        .done(function(data,textStatus,jqXHR){
            mostrarGastos(data);
            $('#VerGastos').modal();
        })
        .fail(function(jqXHR,textStatus,errorThrown) {
            console.log(textStatus)
        });
    }
    function mostrarGastos(Datos){
        const Gastos=document.getElementById('GastosM');
        Gastos.innerHTML='';
        Datos.forEach((Dato,index)=>{
            Gastos.innerHTML+=`<tr>
                        <th>${Dato['nombre']}</th>
                        <th>${Dato['codigo']}</th>
                        <th>${Dato['fecha']}</th>
                        <th>${Dato['nombres']} ${Dato['paterno']} ${Dato['materno']}</th>
                        <th>
                        <?php if($Dato['etapa'] == 'preparacion'): ?>
                            <a class='btn btn-primary btn-sm' title='Editar gasto' href='?/importaciones/editar_gastos/${Dato['id_importacion_gasto']}'>
                                <i class='glyphicon glyphicon-edit'></i>
                            </a>
                        <?php endif; ?>
                        </th>
                    </tr>
                    <tr>
                        <th scope='col'>#</th>
                        <th scope='col'>Gasto</th>
                        <th scope='col'>Factura</th>
                        <th scope='col'>Costo Añadido (%)</th>
                        <th scope='col'>Costo <?=$moneda?></th>
                    </tr>`;
                    Dato['detalles'].forEach((detalles,index)=>{
                        Gastos.innerHTML+=`<tr>
                                <td>${index+1}</td>
                                <td>${detalles['gasto']}</td>
                                <td>${detalles['factura']}</td>
                                <td>${detalles['costo_anadido']}</td>
                                <td>${detalles['costo']}</td>
                            </tr>`;
                    });
        });
    }
    //ingresar Productos
    function ingresarProductos(idImportacion,index){
        document.getElementById('FilaF').value=index;
        document.getElementById('IdImportacionF').value=idImportacion;
        $.ajax({
            data: {},
            type: 'GET',
            dataType: 'json',
            url: '?/importaciones/listar_ingresos/'+idImportacion,
        })
        .done(function(data,textStatus,jqXHR){
            visualizarProductos(data);
            $('#IngresoProductos').modal();
        })
        .fail(function(jqXHR,textStatus,errorThrown) {
            console.log(textStatus)
        });
    }
    function visualizarProductos(Datos){
        // console.log(Datos);
        const Producto=document.getElementById('ProductosIM');
        Producto.innerHTML='';

        Sal=Datos;
        Datos.forEach((Dato,index)=>{
            // console.log(Dato);
            Producto.innerHTML+=`<tr>
                        <td>${index+1}</td>
                        <td>${Dato['codigo']}</td>
                        <td>${Dato['nombre']}</td>
                        <td>${Dato['unidad']}</td>
                        <td>${Dato['precio_ingreso']}</td>
                        <td>${Dato['cantidad']}</td>
                        <td>
                            <input type='text' name='cantidad[]' onkeyup='calcular(${index},this)' value='${Dato['cantidad']}' data-validation='required number' style='width:80px'>
                        </td>
                        <td>${Dato['gatos_individual']}</td>
                        <td>
                            <input type='hidden' name='id_producto[]' value='${Dato['id_producto']}'>
                            <input type='hidden' name='fechav[]' value='${Dato['fechav']}'>
                            <input type='hidden' name='lote[]' value='${Dato['lote']}'>

                            <input type='text' name='precio_nuevo[]' value='${Dato['precio_venta']}' data-validation='required number' style='width:80px'>
                        </td>
                    </tr>`;
        });
    }
    //Registrar Ingreso
    document.getElementById('ingresoF').onsubmit=()=>{
        $.ajax({
            type: 'POST',
            data: $('#ingresoF').serialize(),
            dataType: 'json',
            url: '?/importaciones/guardar_ingreso',
        })
        .done(function(data,textStatus,jqXHR){
            $.notify({
                message: data[1]
            }, {
                type: data[0]
            });
            const productosIM=document.getElementById('DatosF'),
                  index=document.getElementById('FilaF').value;
            productosIM.removeChild(productosIM.children[index]);
            $('#IngresoProductos').modal('hide');
            location.reload();
        })
        .fail(function(e) {
            console.log(e)
        });
        return false;
    }
    //Calcular nuevo valor
    function calcular(i,elemento){
        let TotalImportacion=0,
            Producto=document.getElementById('ProductosIM');

        // console.log("element: "+ elemento.value);
        Sal.forEach((Dato,index)=>{
            // console.log(Dato);
            if(i==index)
                Dato['cantidad']= (elemento.value)? elemento.value:0;
            TotalImportacion=TotalImportacion+(Dato['cantidad']*Dato['precio_ingreso']);
            // console.log(TotalImportacion);
        });
        Sal.forEach((Dato,index)=>{
            // FORMULA JOSEMA
            var p_compra = Dato['precio_ingreso'];
            var c_sol = Dato['cant_aux'];
            var gasto = Dato['gatos_individual'];
            var cantidad = (Dato['cantidad'] == '') ? 0 : Dato['cantidad'];
            let AuxCantidad = ((p_compra * c_sol) + gasto) / cantidad;
            AuxCantidad=parseFloat(AuxCantidad).toFixed(2);
            // FORMULA FABIO
            // let AuxCantidad=parseFloat(Dato['cantidad'])*parseFloat(Dato['precio_ingreso']);
            // console.log("cantidad1: " + AuxCantidad);
            // AuxCantidad=parseFloat(AuxCantidad/TotalImportacion).toFixed(2);
            // console.log("cantidad2: " + AuxCantidad);
            // // AuxCantidad=parseFloat(AuxCantidad)*parseFloat(Dato['total_importacion']);  // NO EXISTE ESTA VARIABLE, POR ESO CAUSA EL ERROR
            // AuxCantidad=(parseFloat(Dato['cantidad']))? parseFloat(AuxCantidad)/parseFloat(Dato['cantidad']):0;
            // console.log("cantidad3: " + AuxCantidad);
            // AuxCantidad=parseFloat(AuxCantidad)+parseFloat(Dato['precio_ingreso']);
            // console.log("cantidad4: " + AuxCantidad);
            // AuxCantidad=parseFloat(AuxCantidad).toFixed(2);
            // console.log("cantidad5: " + AuxCantidad);
            // // console.log(AuxCantidad + '6');
            // // console.log(Producto.children[index].children[8].lastChild.value = AuxCantidad);
            // // Producto.children[index].children[6].children[1].value=AuxCantidad;
            Producto.children[index].children[8].children[1].value = AuxCantidad;
            // console.log(Producto.children[index].children[8].children[1].value);
        })
    }
</script>
<?php
require_once show_template('footer-configured');
