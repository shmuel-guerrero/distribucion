<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');

//$gestion_base = ($gestion - 16) . date('-m-d');
// $gestion_limite = ($gestion + 16) . date('-m-d');
$gestion_limite = date('Y-m-d');
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

// Obtiene las ventas
$ventas = $db->query("SELECT b.producto_id, c.nombre, c.nombre_factura, c.codigo, c.unidad_id, COUNT(a.id_egreso) as regist, GROUP_CONCAT(b.cantidad,'*',b.unidad_id,'*',b.precio) as precios, ca.categoria
              FROM inv_egresos a
              LEFT JOIN inv_egresos_detalles b ON a.id_egreso = b.egreso_id
              LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
              LEFT JOIN inv_categorias ca ON c.categoria_id = ca.id_categoria
              WHERE a.empleado_id = '$empleado' 
              
              AND a.fecha_egreso >= '$fecha_inicial' 
              AND a.fecha_egreso <= '$fecha_final' 
              AND b.promocion_id < 2 
              AND a.tipo = 'Venta'
              GROUP BY b.producto_id, b.unidad_id")->fetch();  // AND a.estadoe != 3 
              
            //   echo $db->last_query();
              
$descuentoTotal = $db->query("SELECT IFNULL(SUM(a.descuento_bs) ,0) as descuento
              FROM inv_egresos a
              WHERE a.empleado_id = '$empleado' 
              AND a.fecha_egreso >= '$fecha_inicial' 
              AND a.fecha_egreso <= '$fecha_final' 
              AND a.tipo = 'Venta'
              AND a.estadoe = 2")->fetch_first();
              
              
// echo json_encode($ventas);


$proformas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, f.motivo')
                ->from('inv_egresos i')
                ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
                ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
                ->join('gps_noventa_motivos f', 'i.motivo_id = f.id_motivo', 'left')
                ->where('i.fecha_egreso >= ', $fecha_inicial)
                ->where('i.fecha_egreso <= ', $fecha_final)
                ->where('i.estadoe>',0)
                ->where('i.empleado_id',$empleado)
                ->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();
$noventas = $db->select('*')->from('gps_no_venta')->join('gps_noventa_motivos','motivo_id = id_motivo')->where('empleado_id',$empleado)->fetch();

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
            <b>Lista de los productos vendidos</b>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($permiso_cambiar || $permiso_imprimir) { ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <div class="text-label">Para realizar una acción hacer clic en los siguientes botones: </div>
                </div>
                <div class="col-xs-12 col-sm-4 text-right">

                    <button class="btn btn-default" data-cambiar="true">
                        <span class="glyphicon glyphicon-calendar"></span>
                        <span class="hidden-xs">Cambiar</span>
                    </button>

                    <a href="?/vendedor/visitas/<?= $empleado ?>/<?= $fecha_inicial ?>/<?= $fecha_final ?>" class="btn btn-success" data-cambiar="true">
                        <span class="glyphicon glyphicon-map-marker"></span>
                        <span class="hidden-xs">Ir a mapa</span>
                    </a>

                    <?php if ($permiso_imprimir) { ?>
                        <a href="?/reporte/listar" class="btn btn-info" target="_blank">
                            <span class="glyphicon glyphicon-print"></span>
                            <span class="hidden-xs">Lista</span>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <hr>
        <?php } ?>
        <?php if ($ventas) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
                        <thead>
                        <tr class="active">
                            <th class="text-nowrap">#</th>
                            <th class="text-nowrap">Codigo</th>
                            <th class="text-nowrap">Producto</th>
                            <th class="text-nowrap">Nombre factura</th>
                            <th class="text-nowrap">Categoria</th>
                            <th class="text-nowrap">Cantidad</th>
                            <th class="text-nowrap">Unidad</th>
                            <th class="text-nowrap">Precio unitario</th>
                            <th class="text-nowrap">Precio</th>
                            <th class="text-nowrap">Registros</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr class="active">
                            <th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Codigo</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Producto</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre factura</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Precio unitario</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Precio</th>
                            <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                        </tr>
                        </tfoot>
                        <tbody>
                        <?php
                        $total = 0;
                        foreach ($ventas as $nro => $venta) {
                            $unidad_precios = explode(',',$venta['precios']);
                            // $unidad_precios = $venta['precios'];
                            $total1 = 0;
                            $unidad = '';
                            $total_cantidad = 0;
                            foreach($unidad_precios as $aux){
                                $aux1 = explode('*',$aux);
                                $total_cantidad = $total_cantidad + $aux1[0];
                                $canti = $aux1[0]/cantidad_unidad($db,$venta['producto_id'],$aux1[1]);
                                $total1 = $total1 + ($canti)*$aux1[2];
                                $unidad = $unidad .$canti.' '.nombre_unidad($db,$aux1[1]).'<br>';
                            }
                            $unidad_caja = $db->select('*')->from('inv_unidades')->where('unidad',$aux1[1])->fetch_first();
                            $cantidad_caja = cantidad_unidad($db,$venta['producto_id'],$unidad_caja['id_unidad']);
                            $mostrar = '';
                            $nombre_unidad = nombre_unidad($db,$venta['unidad_id']);
                            if(isset($cantidad_caja) && $cantidad_caja <= $total_cantidad){
                                $aux2 = (int)($total_cantidad / $cantidad_caja);
                                $mostrar = $mostrar.$aux2.' CAJA';
                                // if($cantidad_caja % $total_cantidad > 0 ){
                                //     $mostrar = $mostrar.'<br>'.($total_cantidad - ($cantidad_caja * $aux2)).' '.$nombre_unidad;
                                // }
                            }else{
                                $mostrar = $total_cantidad.' '.$nombre_unidad;
                            }
                            $total = $total + $total1;
                            $importe = $total1;
                            ?>
                            <tr>
                                <th class="text-nowrap"><?= $nro + 1; ?></th>
                                <td class="text-nowrap"><?= escape($venta['codigo']) ?></td>
                                <td class="text-nowrap"><?= escape($venta['nombre']); ?></td>
                                <td class="text-nowrap"><?= escape($venta['nombre_factura']); ?></td>
                                <td class="text-nowrap"><?= escape($venta['categoria']); ?></td>
                                <td class="text-nowrap"><b><?= $total_cantidad; // $mostrar; ?></b></td>
                                <td class="text-nowrap"><b><?= $nombre_unidad; ?></b></td>
                                <td class="text-nowrap"><b><?= number_format($importe/$total_cantidad ,2) ?></b></td>
                                <td class="text-nowrap" data-total="<?= $importe ?>" ><b><?= number_format($total1, 2, '.', ''); ?></b></td>
                                <td class="text-nowrap"><b><?= escape($venta['regist']); ?></b></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>No existen preventas registradas en la base de datos.</p>
            </div>
        <?php } ?>
        <div class="well">
            <p class="lead margin-none">
                <b>Empleado:</b>
                <?php $emp = $db->select('*')->from('sys_empleados')->where('id_empleado',$empleado)->fetch_first(); ?>
                <span><?= escape($emp['nombres'] . ' ' . $emp['paterno'] . ' ' . $emp['materno']); ?></span>
            </p>
            <p class="lead margin-none">
                <b>Total:</b>
                <u id="total">0.00</u>
                <span><?= escape($moneda); ?></span>
            </p>
            <p class="lead margin-none">
                <b>Descuento:</b>
                <u id="descuento"><?= $descuentoTotal['descuento']; ?></u>
                <span><?= escape($moneda); ?></span>
            </p>
            <p class="lead margin-none">
                <b>Total neto:</b>
                <u id="neto">0.00</u>
                <span><?= escape($moneda); ?></span>
            </p>
        </div>
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
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="empleado">Empleado:</label>
                                <input type="text" name="empleado" value="<?= $empleado ?>" id="empleado" readonly class="form-control" autocomplete="off" data-validation-optional="true">
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
    <script src="<?= js; ?>/jquery.dataFiltersCustom.min.js"></script>
    <script src="<?= js; ?>/moment.min.js"></script>
    <script src="<?= js; ?>/moment.es.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
    <script src="<?= js; ?>/leaflet.js"></script>
    <script src="<?= js; ?>/leaflet-routing-machine.js"></script>
    <script>
        $(function () {

            <?php if ($permiso_eliminar) { ?>
            $('[data-eliminar]').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                bootbox.confirm('Está seguro que desea eliminar la proforma y todo su detalle?', function (result) {
                    if(result){
                        window.location = url;
                    }
                });
            });
            <?php } ?>

            <?php if ($permiso_cambiar) { ?>
            var formato = $('[data-formato]').attr('data-formato');
            var mascara = $('[data-mascara]').attr('data-mascara');
            var gestion = $('[data-gestion]').attr('data-gestion');
            var $inicial_fecha = $('#inicial_fecha');
            var $final_fecha = $('#final_fecha');
            var $empleado = $('#empleado');

            $.validate({
                form: '#form_fecha',
                modules: 'date',
                onSuccess: function () {
                    var inicial_fecha = $.trim($('#inicial_fecha').val());
                    var empleado = $.trim($('#empleado').val());
                    var final_fecha = $.trim($('#final_fecha').val());
                    var vacio = gestion.replace(new RegExp('9', 'g'), '0');

                    inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
                    inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
                    final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
                    final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
                    vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
                    vacio = vacio.replace(new RegExp('/', 'g'), '-');
                    final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
                    inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : '');

                    window.location = '?/reporte/ver/' + empleado + inicial_fecha + final_fecha;
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

            <?php if ($proformas) { ?>

            var table = $('#table').on('draw.dt', function () {
                var suma = 0;
                var neto = 0;
                $('[data-total]:visible').each(function (i) {
                    var total = parseFloat($(this).attr('data-total'));
                    suma = suma + total;
                    
                    if(suma > 0) {
                        desc = parseFloat($('#descuento').text());
                        neto = suma - desc;
                    }
                });
                $('#total').text(suma.toFixed(2));
                $('#neto').text(neto.toFixed(2));
                
            }).DataFilter({
                filter: true,
                name: ' Productos vendidos por <?= escape($emp['nombres'] . ' ' . $emp['paterno'] . ' ' . $emp['materno']); ?>',
                reports: 'excel|word|pdf|html',
                total: 5,
                creacion: 'Para la fecha: ' + '<?= date('Y-m-d H:i') ?>',
                fechas: 'El reporte fue generado desde: <?= $fecha_inicial ?> hasta: <?= ($fecha_final == ((date('Y') + 16) . date('-m-d')) ) ? date('Y-m-d') : $fecha_final ?>',
            });

            $('#states_0').find(':radio[value="hide"]').trigger('click');
            <?php } ?>


            var latitudes = new Array(), longitudes = new Array(), estados = new Array();

            $('.coordenadas').each(function (i) {
                var latitud = $.trim($(this).find('.latitud').text());
                var longitud = $.trim($(this).find('.longitud').text());
                var estado = $.trim($(this).find('.estadoo').text());
                if (latitud != '0.0' && longitud != '0.0') {
                    latitudes.push(latitud);
                    longitudes.push(longitud);
                    estados.push(estado);
                }
            });



        });
    </script>
<?php require_once show_template('footer-advanced'); ?>