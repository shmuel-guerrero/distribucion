<?php
    // Obtiene los formatos para la fecha
    $formato_textual = get_date_textual($_institution['formato']);
    $formato_numeral = get_date_numeral($_institution['formato']);
    // Obtiene los empleados
    $empleados = $db->query("SELECT e.id_empleado,e.nombres,e.paterno,e.materno , os.estado
                    FROM sys_empleados AS e
                    LEFT JOIN inv_ordenes_salidas AS os ON os.empleado_id=e.id_empleado
                    INNER JOIN sys_users AS u ON u.persona_id=e.id_empleado
                    WHERE rol_id='14' GROUP BY e.id_empleado")->fetch();

    $permisos = explode(',',permits);

    // Almacena los permisos en variables
    $permiso_crear = in_array('crear', $permisos);
    $permiso_editar = in_array('editar', $permisos);
    $permiso_ver = in_array('ver', $permisos);
    $permiso_ver2 = in_array('vertodo', $permisos);
    $permiso_eliminar = true;
    $permiso_imprimir = true;
    $permiso_activar = true;
    require_once show_template('header-configured');
?>
    <div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>" data-servidor="<?= ip_local . name_project . '/nota_venta.php'; ?>">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Listar AutoVendedores</strong>
        </h3>
    </div>
    <div class="panel-body">
            <?php
                if(isset($_SESSION[temporary])){
            ?>
                <div class="alert alert-<?= $_SESSION[temporary]['alert']?>">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong><?= $_SESSION[temporary]['title']?></strong>
                    <p><?= $_SESSION[temporary]['message']?></p>
                </div>
            <?php
                    unset($_SESSION[temporary]);
                }
                if ($empleados) { ?>
            <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                <thead>
                    <tr class="active">
                        <th class="text-nowrap">#</th>
                        <th class="text-nowrap">Nombres</th>
                        <th class="text-nowrap">Apellido paterno</th>
                        <th class="text-nowrap">Apellido materno</th>
                        <th class="text-nowrap">Total</th>
                        <th class="text-nowrap">Detalles</th>
                        <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                            <th class="text-nowrap">Opciones</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tfoot>
                    <tr class="active">
                        <th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombres</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Apellido paterno</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Apellido materno</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                        <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                            <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                        <?php } ?>
                    </tr>
                </tfoot>
                <tbody>
                <?php foreach ($empleados as $nro => $empleado) { ?>
                    <tr>
                        <th class="text-nowrap"><?= $nro + 1; ?></th>
                        <td class="text-nowrap"><?= escape($empleado['nombres']); ?></td>
                        <td class="text-nowrap"><?= escape($empleado['paterno']); ?></td>
                        <td class="text-nowrap"><?= escape($empleado['materno']); ?></td>
                        <?php
                            $IdEmpleado=$empleado['id_empleado'];
                            $Fecha=date('Y-m-d',strtotime(date('Y-m-d')));
                            $IdOrden=$db->query("SELECT id_orden
                                                FROM inv_ordenes_salidas
                                                WHERE estado='salida' AND empleado_id='{$IdEmpleado}' LIMIT 1")->fetch_first();
                            $Detalles=array('total'=>0,'registros'=>0);
                            if($IdOrden):
                                $IdOrden=$IdOrden['id_orden'];
                                $Detalles=$db->query("SELECT COUNT(*)AS registros,SUM(od.precio_id*od.cantidad)AS total
                                                    FROM inv_ordenes_detalles AS od
                                                    WHERE od.orden_salida_id='{$IdOrden}' LIMIT 1")->fetch_first();
                            endif;
                        ?>
                        <td class="text-nowrap"><?= escape($Detalles['total']); ?></td>
                        <td class="text-nowrap"><?= escape($Detalles['registros']); ?></td>
                        <?php
                        if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                            <td class="text-nowrap">
                            <?php
                                if($permiso_ver):
                            ?>
                                <a href='#' class='text-success' data-toggle="tooltip" data-title="Ver Productos" <?php if($IdOrden):?>data-listado='<?=$IdOrden?>' style='float:left'<?php else:?> style='display:none;'<?php endif;?>>
                                    <i class="glyphicon glyphicon-search"></i>
                                </a>
                                <a href='?/autoventas/imprimir_salida/<?=$IdOrden?>' class='text-success' style='float:left' data-toggle="tooltip" data-title="Imprimir">
                                    <i class="glyphicon glyphicon-print"></i>
                                </a>
                            <?php
                                endif;
                                if($permiso_editar):
                            ?>
                                <a <?php if($IdOrden):?>href='?/autoventas/editar_salida/<?=$IdOrden?>' style='float:left;'<?php else:?>style='display:none;float:left;'<?php endif;?> class='text-info' data-toggle='tooltip' data-title='Editar'>
                                    <i class='glyphicon glyphicon-edit'></i>
                                </a>
                            <?php
                                endif;
                                if($permiso_eliminar):
                            ?>
                            <form method='POST' action='?/autoventas/eliminar_salida' <?php if(!$IdOrden):?>style='display:none'<?php endif;?> style='float:left'>
                                    <input type='hidden' name='IdOrdenSalida' value='<?=$IdOrden?>'>
                                    <a href='#' onclick='parentNode.submit();' class='text-danger'>
                                        <i class="glyphicon glyphicon-trash"></i>
                                    </a>
                                <a href='#' class='text-danger' data-toggle="tooltip" data-title="Eliminar"></a>
                            </form>
                            <?php
                                endif;
                            ?>
                            <?php if ($permiso_activar) { ?>
                                    
                                    <?php if ($empleado['estado'] == 'salida') { ?>
                                        
                                        <a href="?/autoventas/activar2/<?= $empleado['id_empleado']; ?>" class="text-info" data-toggle="tooltip" data-title="Cerrar distribucion" data-activar="true"><i class="glyphicon glyphicon-unchecked"></i></a>
                                        <!--<a href="?/autoventas/activar/<?= $empleado['id_empleado']; ?>" class="text-danger" data-toggle="tooltip" data-title="Cerrar distribucion (limpiar)" data-activar="true"><i class="glyphicon glyphicon-unchecked"></i></a>-->
                                    <?php } else { ?>
                                        <a href="?/autoventas/liquidacion_autoventa/<?= $empleado['id_empleado']; ?>" class="text-success" target="_blank" data-toggle="tooltip" data-title="Imprimir liquidaci��n" ><i class="glyphicon glyphicon-print"></i></a>  
                                        
                                        <a href="?/autoventas/activar3/<?= $empleado['id_empleado']; ?>" class="text-success" data-toggle="tooltip" data-title="Entrega realizada"><i class="glyphicon glyphicon-check"></i></a>
                                                                   
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>No existen empleados registrados en la base de datos, para crear nuevos empleados hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
            </div>
        <?php } ?>
    </div>
    <?php if(get_notification()): ?>
        <div class="alert alert-<?= $message['type']; ?>">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong><?= $message['title']; ?></strong>
            <p><?= $message['content']; ?></p>
        </div>
    <?php endif; ?>
    <div id="modal_asignar" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content loader-wrapper">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Orden de Salida</h4>
                </div>
                <div class="modal-body">
                    <span class='text-left col-s6' id='TotalFL'>
                    </span>
                    <br>
                    <span class='text-right col-s6'>
                        Fecha: <?=date('Y-m-d')?>
                    </span>
                    <br>
                    <table class="table table-bordered table-condensed table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Codigo</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id='ListadoF'>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.es.js"></script>
    <script src="<?= js; ?>/jquery.base64.js"></script>
    <script src="<?= js; ?>/pdfmake.min.js"></script>
    <script src="<?= js; ?>/vfs_fonts.js"></script>
    <script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
    <script>
        var table = $('#table').DataFilter({
            filter: false,
            name: 'empleados',
            reports: 'xls|doc|pdf|html'
        });

        var $modal_asignar=$('#modal_asignar'),
            $modal_editar=$('#modal_editar'),
            $listado=$('[data-listado]'),
            $editar=$('[data-editar]'),
            $eliminar=$('[data-eliminar]');
        $listado.on('click',function(e){
            e.preventDefault();
            let IdOrdenSalida=$(this).attr('data-listado');
            $.ajax({
                type:'POST',
                dataType:'json',
                url:'?/autoventas/listar_detalle',
                data:{
                        'IdOrdenSalida':IdOrdenSalida
                    }
            }).done(function(Datos){
                let Listado=document.getElementById('ListadoF'),
                    Total=document.getElementById('TotalFL');
                Listado.innerHTML='';
                let TotalP=0;
                Datos.forEach(Dato=>{
                    TotalP=TotalP+parseFloat(Dato['subtotal']);
                    Listado.innerHTML+=`<tr>
                                            <td>${Dato['nombre']}</td>
                                            <td>${Dato['codigo']}</td>
                                            <td>${Dato['precio_id']}</td>
                                            <td>${Dato['cantidad']}</td>
                                            <td>${Dato['subtotal']}</td>
                                        </tr>`;
                });
                Total.innerHTML=`Total: ${TotalP}`;
                $modal_asignar.modal('show');
            }).fail(function (e) {
                $('#loader').fadeOut(100);
                $.notify({
                        message: 'No se Puede Listar los de talles de la Orden de Salida'
                    },{
                        type: 'danger'
                    });
            });
        });

        /*$eliminar.on('click',function(e){
            e.preventDefault();
            let IdOrdenSalida=$(this).attr('data-eliminar');
            bootbox.confirm('Está seguro que desea eliminar la Salida?', function (result) {
                if(result){
                    $.ajax({
                        type:'POST',
                        dataType:'json',
                        url:'?/autoventas/eliminar_salida',
                        data:{
                                'IdOrdenSalida':IdOrdenSalida
                            }
                    }).done(function(Datos){
                        console.log(Datos)
                    }).fail(function (e) {
                        $('#loader').fadeOut(100);
                        $.notify({
                                message: 'No se Puede Eliminar los detalles de la Orden de Salida'
                            },{
                                type: 'danger'
                            });
                    });
                }
            });
        });*/
    </script>
<?php require_once show_template('footer-configured'); ?>