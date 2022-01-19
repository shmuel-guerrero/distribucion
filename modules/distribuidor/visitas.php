<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$empleado = (isset($params[0])) ? $params[0] : 0;

// Obtiene fecha inicial
$fecha_inicial = (isset($params[1])) ? $params[1] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[2])) ? $params[2] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);


$usuarios = $db->select('*')->from('gps_asigna_distribucion')->where('distribuidor_id',$empleado)->where('estado',1)->fetch();

// Obtiene las ventas


// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = in_array('proformas_ver', $permisos);
$permiso_eliminar = in_array('proformas_eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_facturar = in_array('proformas_facturar', $permisos);
$permiso_cambiar = true;

?>
<?php require_once show_template('header-advanced'); ?>
    <link rel="stylesheet" href="<?= css; ?>/leaflet.css">
    <link rel="stylesheet" href="<?= css; ?>/leaflet-routing-machine.css">
    <link rel="stylesheet" href="<?= css; ?>/site.css">
    <style>
        .table-xs tbody {
            font-size: 12px;
        }
        .width-sm {
            min-width: 150px;
        }
        .width-md {
            min-width: 200px;
        }
        .width-lg {
            min-width: 250px;
        }
        .leaflet-control-attribution,
        .leaflet-routing-container {
            display: none;
        }
    </style>
    <div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <b>Lista de todas las preventas</b>
        </h3>
    </div>
    <div class="panel-body">
    <?php if ($permiso_cambiar || $permiso_imprimir) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para realizar una acci贸n hacer clic en los siguientes botones: </div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <input type="hidden" id="lugares1" value="<?= $lugares ?>"/>
                <?php if ($permiso_cambiar) { ?>
                    <button class="btn btn-default" data-cambiar="true">
                        <span class="glyphicon glyphicon-calendar"></span>
                        <span class="hidden-xs">Cambiar</span>
                    </button>
                <?php } ?>
                <?php if ($permiso_imprimir) { ?>
                    <a href="?/distribuidor/listar2" class="btn btn-info">
                        <span class="glyphicon glyphicon-list"></span>
                        <span class="hidden-xs">Listar</span>
                    </a>
                <?php } ?>
            </div>
        </div>
        <hr>
    <?php } ?>
    <?php if ($usuarios) { ?>
        <div class="row">
        <div class="col-sm-6">
            <table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
                <thead>
                <tr class="active">
                    <th class="text-nowrap">#</th>
                    <th class="text-nowrap">Fecha</th>
                    <th class="">Id cliente</th>
                    <th class="text-nowrap">Cliente</th>
                    <!-- <th class="text-nowrap">NIT/CI</th> -->
                    <th class="text-nowrap">Preventa</th>
                    <th class="text-nowrap">Prioridad</th>
                    <th class="text-nowrap">Empleado</th>
                    <th class="text-nowrap">Motivo</th>
                    <?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
                        <th class="text-nowrap">Opciones</th>
                    <?php } ?>
                </tr>
                </thead>
                <tfoot>
                <tr class="active">
                    <th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
                    <th class="text-middle" data-datafilter-filter="true">Id cliente</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
                    <!-- <th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th> -->
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Preventa</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Prioridad</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Motivo</th>
                    <?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
                        <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                    <?php } ?>
                </tr>
                </tfoot>
                <tbody>
                <?php
                $nro2 = 1;
                foreach ($usuarios as $usuario){
                    $fecha = $db->select('a.fecha')->from('sys_empleados a')->join('gps_rutas b','a.id_empleado = b.empleado_id')->where('b.id_ruta',$usuario['ruta_id'])->fetch_first();
                    if($fecha['fecha'] >= date('Y-m-d')){
                        $fecha = $fecha['fecha'];
                    }else{
                        $fecha = date("Y-m-d",strtotime(date('Y-m-d')."- 1 days"));
                    }

                    if($usuario['ruta_id'] != 0){
                        $proformas = $db->select('*')->from('inv_egresos a')->join('gps_rutas w', 'a.ruta_id = w.id_ruta')->join('sys_empleados e', 'w.empleado_id = e.id_empleado')->join('inv_clientes c', 'a.cliente_id = c.id_cliente')->where('a.ruta_id',$usuario['ruta_id'])->where('a.grupo','')->where('a.fecha_egreso <=',$fecha)->where('a.estadoe =',2)->fetch();
                        $proformas2 = $db->select('*')->from('tmp_egresos a')->join('gps_noventa_motivos f','a.motivo_id = f.id_motivo')->join('gps_rutas w', 'a.ruta_id = w.id_ruta')->join('sys_empleados e', 'w.empleado_id = e.id_empleado')->join('inv_clientes c', 'a.cliente_id = c.id_cliente')->where('a.ruta_id',$usuario['ruta_id'])->where('a.estadoe>',1)->where('a.fecha_egreso <=',$fecha)->where('a.estadoe >',1)->where('a.estado=',3)->fetch();
                    }else{
                        $proformas = $db->select('*')->from('inv_egresos a')->join('sys_empleados e', 'a.empleado_id = e.id_empleado')->join('inv_clientes c', 'a.cliente_id = c.id_cliente')->where('a.grupo',$usuario['grupo_id'])->where('a.estadoe>',1)->where('a.grupo','')->where('a.fecha_egreso <=',$fecha)->fetch();
                    }
                    //var_dump($proformas);


                    foreach ($proformas2 as $nro => $proforma) {

                        ?>
                        <tr>
                            <th class="text-nowrap"><?= $nro2++; ?></th>
                            <td class="text-nowrap"><?= escape(date_decode($proforma['distribuidor_fecha'], $_institution['formato'])); ?> <br> <small class="text-success"><?= escape($proforma['distribuidor_hora']); ?></small></td>
                            <td class="text-nowrap"><?= escape($proforma['cliente_id']); ?></td>
                            <td class="text-nowrap"><?= escape($proforma['nombre_cliente']); ?> <br>
                                <small class="text-success"><?= escape($proforma['nit_ci']); ?></small>
                            </td>
<!--                             <td class="text-nowrap"></td> -->
                            <td class="text-nowrap text-right"><?= escape($proforma['nro_factura']); ?></td>
                            <td class=" text-center text-middle coordenadas">
                                <?php $ubi = explode(',', $proforma['ubicacion']) ?>
                                <span class="latitud hidden"><?= $ubi[0] + 0.00005; ?></span>
                                <span class="longitud hidden"><?= $ubi[1] - 0.00003; ?></span>
                                <span class="id_c hidden"><?= $proforma['cliente_id'] ?></span>
                                <span class="nombre hidden"><?= $proforma['nombre_cliente'] ?></span>
                                <span class="estadoo hidden"><?= $proforma['distribuidor_estado'] ?></span>
                                <span><?= $proforma['observacion'] ?></span>
                            </td>
                            <td class="text-uppercase"><?= escape($proforma['nombres'] . ' ' . $proforma['paterno'] . ' ' . $proforma['materno']); ?></td>
                            <td class="text-nowrap text-right"><?= escape($proforma['motivo']); ?></td>
                            <?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
                                <td class="text-nowrap">
                                    <?php if ($permiso_facturar) { ?>
                                        <a href="?/operaciones/proformas_facturar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Convertir en facturar"><span class="glyphicon glyphicon-qrcode"></span></a>
                                    <?php } ?>
                                    <?php if ($permiso_ver) { ?>
                                        <a href="?/operaciones/proformas_ver/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de la proforma"><span class="glyphicon glyphicon-list-alt"></span></a>
                                    <?php } ?>
                                    <?php if ($permiso_eliminar) { ?>
                                        <a href="?/operaciones/proformas_eliminar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Eliminar proforma" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
                                    <?php } ?>
                                    <?php if ($permiso_imprimir) {
                                        if($proforma['distribuidor_estado']=='ENTREGA'){?>
                                            <a href="?/distribuidor/imprimir5/<?= $proforma['id_egreso']; ?>/<?= $proforma['id_tmp_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:green"></span></a>
                                        <?php } if($proforma['distribuidor_estado']=='DEVUELTO'){?>
                                            <a href="?/distribuidor/imprimir5/<?= $proforma['id_egreso']; ?>/<?= $proforma['id_tmp_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:red"></span></a>
                                        <?php }if($proforma['distribuidor_estado']=='ALMACEN'){ ?>
                                            <a href="?/distribuidor/imprimir5/<?= $proforma['id_egreso']; ?>/<?= $proforma['id_tmp_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:blue"></span></a>
                                        <?php }if($proforma['distribuidor_estado']=='NO ENTREGA'){ ?>
                                            <a href="?/distribuidor/imprimir5/<?= $proforma['id_egreso']; ?>/<?= $proforma['id_tmp_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:black"></span></a>
                                        <?php    }} ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                    foreach ($proformas as $nro => $proforma) {

                        ?>
                        <tr>
                            <th class="text-nowrap"><?= $nro2++; ?></th>
                            <td class="text-nowrap"><?= escape(date_decode($proforma['fecha_egreso'], $_institution['formato'])); ?><br> <small class="text-success"><?= escape($proforma['hora_egreso']); ?></small></td>
                            <td class="text-nowrap" ><?= escape($proforma['cliente_id']); ?></td>
                            <td class="text-nowrap"><?= escape($proforma['nombre_cliente']); ?> <br>
                                <small class="text-success">NIT / CI : <?= escape($proforma['nit_ci']); ?></small>
                            </td>
                            <!-- <td class="text-nowrap"></td> -->
                            <td class="text-nowrap text-right"><?= escape($proforma['nro_factura']); ?></td>
                            <td class=" text-center text-middle coordenadas">
                                <?php $ubi = explode(',', $proforma['ubicacion']) ?>
                                <span class="latitud hidden"><?= $ubi[0] ?></span>
                                <span class="longitud hidden"><?= $ubi[1] ?></span>
                                <span class="id_c hidden"><?= $proforma['cliente_id'] ?></span>
                                <span class="nombre hidden"><?= $proforma['nombre_cliente'] ?></span>
                                <span class="estadoo hidden">2</span>
                                <span><?= $proforma['observacion'] ?></span>
                            </td>
                            <td class="text-uppercase"><?= escape($proforma['nombres'] . ' ' . $proforma['paterno'] . ' ' . $proforma['materno']); ?></td>
                            <td class="text-nowrap text-right"></td>
                            <?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
                                <td class="text-nowrap">
                                    <?php if ($permiso_facturar) { ?>
                                        <a href="?/operaciones/proformas_facturar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Convertir en facturar"><span class="glyphicon glyphicon-qrcode"></span></a>
                                    <?php } ?>
                                    <?php if ($permiso_ver) { ?>
                                        <a href="?/operaciones/proformas_ver/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de la proforma"><span class="glyphicon glyphicon-list-alt"></span></a>
                                    <?php } ?>
                                    <?php if ($permiso_eliminar) { ?>
                                        <a href="?/operaciones/proformas_eliminar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Eliminar proforma" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
                                    <?php } ?>
                                    <?php if ($permiso_imprimir) {
                                        if($proforma['estadoe']==2){?>
                                            <a href="?/distribuidor/imprimir4/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:blue"></span></a>
                                        <?php } if($proforma['estadoe']==1){?>
                                            <a href="?/distribuidor/imprimir4/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:red"></span></a>
                                        <?php }if($proforma['estadoe']==3){ ?>
                                            <a href="?/distribuidor/imprimir4/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:blue"></span></a>
                                        <?php    }} ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                    if($usuario['grupo_id']!=''){
                        $proformas3 = $db->select('*')->from('inv_egresos a')->join('sys_empleados e', 'a.empleado_id = e.id_empleado')->join('inv_clientes c', 'a.cliente_id = c.id_cliente')->where('a.grupo',$usuario['grupo_id'])->where('a.fecha_egreso <=',$fecha)->where('a.estadoe =',2)->fetch();
                        foreach ($proformas3 as $nro => $proforma) {

                            ?>
                            <tr>
                                <th class="text-nowrap"><?= $nro2++; ?></th>
                                <td class="text-nowrap"><?= escape(date_decode($proforma['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($proforma['hora_egreso']); ?></small></td>
                                <td class="text-nowrap" ><?= escape($proforma['cliente_id']); ?></td>
                                <td class="text-nowrap"><?= escape($proforma['nombre_cliente']); ?> <br> 
                                    <small class="text-success"><?= escape($proforma['nit_ci']); ?></small>
                                </td>
                                <!-- <td class="text-nowrap"></td> -->
                                <td class="text-nowrap text-right"><?= escape($proforma['nro_factura']); ?></td>
                                <td class=" text-center text-middle coordenadas">
                                    <?php $ubi = explode(',', $proforma['ubicacion']) ?>
                                    <span class="latitud hidden"><?= $ubi[0] ?></span>
                                    <span class="longitud hidden"><?= $ubi[1] ?></span>
                                    <span class="id_c hidden"><?= $proforma['cliente_id'] ?></span>
                                    <span class="nombre hidden"><?= $proforma['nombre_cliente'] ?></span>
                                    <span class="estadoo hidden">2</span>
                                    <span><?= $proforma['observacion'] ?></span>
                                </td>
                                <td class="text-uppercase"><?= escape($proforma['nombres'] . ' ' . $proforma['paterno'] . ' ' . $proforma['materno']); ?></td>
                                <td class="text-nowrap text-right"></td>
                                <?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
                                    <td class="text-nowrap">
                                        <?php if ($permiso_facturar) { ?>
                                            <a href="?/operaciones/proformas_facturar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Convertir en facturar"><span class="glyphicon glyphicon-qrcode"></span></a>
                                        <?php } ?>
                                        <?php if ($permiso_ver) { ?>
                                            <a href="?/operaciones/proformas_ver/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de la proforma"><span class="glyphicon glyphicon-list-alt"></span></a>
                                        <?php } ?>
                                        <?php if ($permiso_eliminar) { ?>
                                            <a href="?/operaciones/proformas_eliminar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Eliminar proforma" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
                                        <?php } ?>
                                        <?php if ($permiso_imprimir) {
                                            if($proforma['estadoe']==2){?>
                                                <a href="?/distribuidor/imprimir4/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:blue"></span></a>
                                            <?php } if($proforma['estadoe']==1){?>
                                                <a href="?/distribuidor/imprimir4/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:red"></span></a>
                                            <?php }if($proforma['estadoe']==3){ ?>
                                                <a href="?/distribuidor/imprimir4/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" target="_blank"><span class="glyphicon glyphicon-print" style="color:blue"></span></a>
                                            <?php    }} ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } } }?>
                </tbody>
            </table>
        </div>
        <div class="col-sm-6">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="lead">Ruta de preventas</h4>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="?/distribuidor/ver/<?= $empleado ?>/<?= $fecha_inicial ?>/<?= $fecha_final ?>" class="btn btn-success" target="_blank">
                        <span class="glyphicon glyphicon-fullscreen"></span>
                        <span class="hidden-xs">Expandir</span>
                    </a>
                </div>
            </div>
            <hr>
            <div id="map" class="embed-responsive embed-responsive-16by9"></div>
        </div>
        </div>
    <?php } else { ?>
        <div class="alert alert-danger">
            <strong>Advertencia!</strong>
            <p>No existen proformas registradas en la base de datos.</p>
        </div>
    <?php } ?>
    </div>

    <!-- Inicio modal fecha -->
<?php if ($permiso_cambiar) { ?>
    <div id="modal_fecha" class="modal fade">
        <div class="modal-dialog">
            <form id="form_fecha" class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Cambiar fecha</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="inicial_fecha">Fecha inicial:</label>
                                <input type="text" name="inicial" value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="final_fecha">Fecha final:</label>
                                <input type="text" name="final" value="<?= ($fecha_final != $gestion_limite) ? date_decode($fecha_final, $_institution['formato']) : ''; ?>" id="final_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
                                <input type="hidden" name="empleado" value="<?= $empleado ?>" id="empleado" readonly class="form-control" autocomplete="off" data-validation-optional="true">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-aceptar="true">
                        <span class="glyphicon glyphicon-ok"></span>
                        <span>Aceptar</span>
                    </button>
                    <button type="button" class="btn btn-default" data-cancelar="true">
                        <span class="glyphicon glyphicon-remove"></span>
                        <span>Cancelar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
    <!-- Fin modal fecha -->

    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.es.js"></script>
    <script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
    <script src="<?= js; ?>/jquery.base64.js"></script>
    <script src="<?= js; ?>/pdfmake.min.js"></script>
    <script src="<?= js; ?>/vfs_fonts.js"></script>
    <script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
    <script src="<?= js; ?>/moment.min.js"></script>
    <script src="<?= js; ?>/moment.es.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
    <script src="<?= js; ?>/leaflet.js"></script>
    <script src="<?= js; ?>/leaflet-routing-machine.js"></script>
    <script src="<?= js; ?>/Leaflet.Icon.Glyph.js"></script>

    <script>
        $(function () {


            <?php if ($permiso_cambiar) { ?>
            var formato = $('[data-formato]').attr('data-formato');
            var mascara = $('[data-mascara]').attr('data-mascara');
            var gestion = $('[data-gestion]').attr('data-gestion');
            var $inicial_fecha = $('#inicial_fecha');
            var $final_fecha = $('#final_fecha');

            $.validate({
                form: '#form_fecha',
                modules: 'date',
                onSuccess: function () {
                    var inicial_fecha = $.trim($('#inicial_fecha').val());
                    var final_fecha = $.trim($('#final_fecha').val());
                    var empleado = $.trim($('#empleado').val());
                    var vacio = gestion.replace(new RegExp('9', 'g'), '0');

                    inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
                    inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
                    final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
                    final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
                    vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
                    vacio = vacio.replace(new RegExp('/', 'g'), '-');
                    final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
                    inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : '');

                    window.location = '?/distribuidor/visitas/' + empleado + inicial_fecha + final_fecha;
                }
            });

            //$inicial_fecha.mask(mascara).datetimepicker({
            $inicial_fecha.datetimepicker({
                format: formato
            });

            //$final_fecha.mask(mascara).datetimepicker({
            $final_fecha.datetimepicker({
                format: formato
            });

            $inicial_fecha.on('dp.change', function (e) {
                $final_fecha.data('DateTimePicker').minDate(e.date);
            });

            $final_fecha.on('dp.change', function (e) {
                $inicial_fecha.data('DateTimePicker').maxDate(e.date);
            });

            var $form_fecha = $('#form_fecha');
            var $modal_fecha = $('#modal_fecha');

            $form_fecha.on('submit', function (e) {
                e.preventDefault();
            });

            $modal_fecha.on('show.bs.modal', function () {
                $form_fecha.trigger('reset');
            });

            $modal_fecha.on('shown.bs.modal', function () {
                $modal_fecha.find('[data-aceptar]').focus();
            });

            $modal_fecha.find('[data-cancelar]').on('click', function () {
                $modal_fecha.modal('hide');
            });

            $modal_fecha.find('[data-aceptar]').on('click', function () {
                $form_fecha.submit();
            });

            $('[data-cambiar]').on('click', function () {
                $('#modal_fecha').modal({
                    backdrop: 'static'
                });
            });
            <?php } ?>


            var table = $('#table').DataFilter({
                filter: true,
                name: 'proformas',
                reports: 'excel|word|pdf|html'
            });

            $('#states_0').find(':radio[value="hide"]').trigger('click');



            var latitudes = new Array(), longitudes = new Array(), estados = new Array(), nombres = new Array(), lugar = new Array();

            $('.coordenadas').each(function (i) {
                var latitud = $.trim($(this).find('.latitud').text());
                var longitud = $.trim($(this).find('.longitud').text());
                var luga = $.trim($(this).find('.id_c').text());
                var estado = $.trim($(this).find('.estadoo').text());
                var nombre = $.trim($(this).find('.nombre').text());
                if (latitud != '0.0' && longitud != '0.0') {
                    latitudes.push(latitud);
                    longitudes.push(longitud);
                    estados.push(estado);
                    nombres.push(nombre);
                    lugar.push(luga);
                    if($("#table tbody tr").length === 1){
                        latitudes.push(latitud);
                        longitudes.push(longitud);
                        estados.push(estado);
                        nombres.push(nombre);
                        lugar.unshift(0)
                    }
                }
            });
            console.log(latitudes);
            console.log(longitudes);
            console.log(estados);
            if (latitudes.length != 0 && longitudes.length != 0) {

                var LeafIcon = L.Icon.extend({
                    options: {
                        iconSize: [25, 41],
                        iconAnchor:  [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize:  [41, 41],
                        // 		iconUrl: 'glyph-marker-icon.png',
                        // 		iconSize: [35, 45],
                        // 		iconAnchor:   [17, 42],
                        // 		popupAnchor: [1, -32],
                        // 		shadowAnchor: [10, 12],
                        // 		shadowSize: [36, 16],
                        // 		bgPos: (Point)
                        className: '',
                        prefix: '',
                        glyph: 'home',
                        glyphColor: 'white',
                        glyphSize: '11px',	// in CSS units
                        glyphAnchor: [0, -7]
                    }
                });
                var greenIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/green.png' ?>'}),
                    redIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/red.png' ?>'}),
                    blueIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/blue.png' ?>'});

                function handleError(e) {
                    if (e.error.status === -1) {
                        // HTTP error, show our error banner
                        document.querySelector('#osrm-error').style.display = 'block';
                        L.DomEvent.on(document.querySelector('#osrm-error-close'), 'click', function(e) {
                            document.querySelector('#osrm-error').style.display = 'none';
                            L.DomEvent.preventDefault(e);
                        });
                    }
                }


                console.log(latitudes[4]);

                var waypoints1 = new Array();

                var centerPoint = [latitudes[0], longitudes[0]];

                // Create leaflet map.
                var map = L.map('map').setView(centerPoint, 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                }).addTo(map);

                for (var i=0 ; latitudes.length > i; i++) {
                    console.log('hola');
                    if(estados[i] === '2'){
                        L.marker([latitudes[i], longitudes[i]], {icon: blueIcon}).bindPopup(nombres[i]+'<br>'+lugar[i]).addTo(map);
                    }else if(estados[i] === 'ENTREGA'){
                        L.marker([latitudes[i], longitudes[i]], {icon: greenIcon}).bindPopup(nombres[i]+'<br>'+lugar[i]).addTo(map);
                    }else if(estados[i] === 'DEVUELTO'){
                        L.marker([latitudes[i], longitudes[i]], {icon: redIcon}).bindPopup(nombres[i]+'<br>'+lugar[i]).addTo(map);
                    }else{
                        L.marker([latitudes[i], longitudes[i]], {icon: redIcon}).bindPopup(nombres[i]+'<br>'+lugar[i]).addTo(map);
                    }
                }
            }
        });
    </script>
<?php require_once show_template('footer-advanced'); ?>