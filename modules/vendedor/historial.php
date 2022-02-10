<?php
// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

//moneda
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene el rango de fechas
$gestion = date('Y');
//$gestion_base = date('Y-m-d');
$gestion_base = date("d-m-Y", strtotime(date('Y-m-d')));

//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = $gestion_base;

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);
//var_dump($empleados);
// Obtiene los permisos
$permisos = explode(',', permits);
// Almacena los permisos en variables
$permiso_ver = in_array('ver', $permisos);


$Empleados=$db->query("SELECT e.id_empleado,e.nombres,e.paterno,e.materno,GROUP_CONCAT(te.distribuidor_estado,'-',te.monto_total SEPARATOR '|')AS entregas
                FROM sys_users AS u
                LEFT JOIN sys_empleados AS e ON u.persona_id=e.id_empleado
                LEFT JOIN tmp_egresos AS te ON e.id_empleado=te.empleado_id
                WHERE u.rol_id!='4' AND te.fecha_egreso BETWEEN '{$fecha_inicial}' AND '{$fecha_final}' GROUP BY e.id_empleado")->fetch();
?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Historial</strong>
    </h3>
</div>
<div class="panel-body">
    <?php if (($permiso_ver)) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para agregar nuevos empleados hacer clic en el siguiente botòn: </div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <button class="btn btn-default" data-cambiar="true">
                    <span class="glyphicon glyphicon-calendar"></span>
                    <span class="hidden-xs">Cambiar</span>
                </button>
            </div>
        </div>
        <hr>
    <?php } ?>
    <?php if (isset($_SESSION[temporary])) { ?>
        <div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong><?= $_SESSION[temporary]['title']; ?></strong>
            <p><?= $_SESSION[temporary]['message']; ?></p>
        </div>
        <?php unset($_SESSION[temporary]); ?>
    <?php } ?>
    <?php if ($Empleados) { ?>
        <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
            <thead>
                <tr class="active">
                    <th class="text-nowrap">#</th>
                    <th class="text-nowrap">Nombres</th>
                    <th class="text-nowrap">Apellido paterno</th>
                    <th class="text-nowrap">Apellido materno</th>
                    <th class="text-nowrap">Entregas <?= escape($moneda); ?></th>
                    <th class="text-nowrap">Devueltos <?= escape($moneda); ?></th>
                    <th class="text-nowrap">Efectividad</th>
                    <th class="text-nowrap">Utilidad</th>
                    <th class="text-nowrap">Rentabilidad</th>
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
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Entregas<?= escape($moneda); ?></th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Devueltos<?= escape($moneda); ?></th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Efectividad</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Utilidad</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Rentabilidad</th>
                    <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                        <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                    <?php } ?>
                </tr>
            </tfoot>
            <tbody>
                <?php
                    foreach($Empleados as $Nro=>$Empleado):
                        $Entrega=0;
                        $Devuelto=0;
                        $Efectividad=0;
                        $Utilidad=0;
                        $Rentabilidad=0;
                        $Entregas=explode('|',$Empleado['entregas']);
                        foreach($Entregas as $Monto):
                            $Valor=explode('-',$Monto);
                            if($Valor[0]=='ENTREGA')
                                $Entrega+=$Valor[1];
                            else
                                $Devuelto+=$Valor[1];
                        endforeach;
                        if($Entrega+$Devuelto!=0):
                            $Efectividad=($Entrega*100)/($Entrega+$Devuelto);
                            $Efectividad=number_format($Efectividad,2,'.','');
                        else:
                            $Efectividad=0;
                        endif;
                        $EgresosUtilidad=$db->query("SELECT ed.lote,ed.precio,(ed.cantidad/IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)) as cantidad,ed.producto_id
                                                        FROM inv_egresos_detalles AS ed
                                                        LEFT JOIN inv_egresos AS e ON e.id_egreso=ed.egreso_id
                                                        LEFT JOIN inv_asignaciones AS asi ON asi.producto_id=ed.producto_id AND asi.unidad_id = ed.unidad_id  AND asi.visible = 's' 
                                                        WHERE e.anulado != 1 AND e.empleado_id='{$Empleado['id_empleado']}' AND e.fecha_egreso BETWEEN '{$fecha_inicial}' AND '{$fecha_final}' AND asi.visible = 's' ")->fetch();
                        foreach($EgresosUtilidad as $Fila=>$EgresosU):
                            $Cantidad=$EgresosU['cantidad'];
                            $Precio=$EgresosU['precio'];
                            $SubTotal=0;
                            $Lotes=explode(',',$EgresosU['lote']);

                            //$Select="SELECT ";
                            //$From="FROM inv_ingresos_detalles ";
                            //$Where="WHERE producto_id='{$IdProducto}' AND (";
                            for($i=0;$i<count($Lotes);++$i):
                                $Lote=explode('-',$Lotes[$i]);
                                $IdProducto=$EgresosU['producto_id'];

                                //$Where.="lote='{$Lote[0]}' OR ";
                                $Ingreso=$db->query("SELECT costo
                                    FROM inv_ingresos_detalles
                                    WHERE producto_id='{$IdProducto}' AND lote='{$Lote[0]}' LIMIT 1")->fetch_first();
                                $Ingreso = ($Ingreso['costo']) ? $Ingreso['costo'] : 0;
                                // $SubTotal=$SubTotal+(($Precio-$Ingreso)*$Lote[1]);
                                $SubTotal=$SubTotal+(($Ingreso)*$Lote[1]);
                            endfor;
                            $SubTotal = ($Precio*$Cantidad) - $SubTotal;
                            
                            //$Where=rtrim($Where,' OR ');
                            //$Where.=')';


                            $Utilidad=$Utilidad+$SubTotal;
                        endforeach;
                        $Utilidad=number_format($Utilidad,2,'.','');
                        $Entrega=number_format($Entrega,2,'.','');
                        $Devuelto=number_format($Devuelto,2,'.','');
                        if($Entrega!=0):
                            $Rentabilidad=round((($Utilidad*100)/$Entrega),2);
                        else:
                            $Rentabilidad=0;
                        endif;
                ?>
                    <tr>
                        <th class="text-nowrap"><?=$Nro+1?></th>
                        <td class="text-nowrap"><?= escape($Empleado['nombres']); ?></td>
                        <td class="text-nowrap"><?= escape($Empleado['paterno']); ?></td>
                        <td class="text-nowrap"><?= escape($Empleado['materno']); ?></td>
                        <td class="text-nowrap"><?=$Entrega?></td>
                        <td class="text-nowrap"><?=$Devuelto?></td>
                        <td class="text-nowrap"><?=$Efectividad?>%</td>
                        <td class="text-nowrap"><?=$Utilidad?></td>
                        <td class="text-nowrap"><?=$Rentabilidad?></td>
                        <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                            <td class="text-nowrap">
                                <?php if ($permiso_ver) { ?>
                                    <a href="?/vendedor/detalle_historial/<?= $Empleado['id_empleado']; ?>/<?= $fecha_inicial; ?>/<?= $fecha_final; ?>" data-toggle="tooltip" data-title="Ver ruta"><i class="glyphicon glyphicon-search"></i></a>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="alert alert-danger">
            <strong>Advertencia!</strong>
            <p>No existen empleados registrados en la base de datos, para crear nuevos empleados hacer clic en el botòn nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
        </div>
    <?php } ?>
</div>
<div id="modal_asignar" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" id="form_asignar" class="modal-content loader-wrapper" autocomplete="off">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Asignar empleado</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="unidad_id_asignar" class="control-label">Empleado:</label>
                    <select name="distribuidor_id" id="unidad_id_asignar" class="form-control" data-validation="required">
                        <option value="" selected="selected">Seleccionar</option>
                        <?php foreach ($empleados as $empleado) : ?>
                            <option value="<?= $empleado['id_empleado']; ?>"><?= escape($empleado['nombres'] . ' ' . $empleado['paterno'] . ' ' . $empleado['materno']); ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                    <span>Guardar</span>
                </button>
            </div>

        </form>
    </div>
</div>
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
    $(function() {
        var formato = $('[data-formato]').attr('data-formato');
        var mascara = $('[data-mascara]').attr('data-mascara');
        var gestion = $('[data-gestion]').attr('data-gestion');
        var $inicial_fecha = $('#inicial_fecha');
        var $final_fecha = $('#final_fecha');

        $.validate({
            form: '#form_fecha',
            modules: 'date',
            onSuccess: function() {
                var inicial_fecha = $.trim($('#inicial_fecha').val());
                var final_fecha = $.trim($('#final_fecha').val());
                var vacio = gestion.replace(new RegExp('9', 'g'), '0');

                inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
                inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
                final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
                final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
                vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
                vacio = vacio.replace(new RegExp('/', 'g'), '-');
                final_fecha = (final_fecha != '') ? ('/' + final_fecha) : '';
                inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : '');

                window.location = '?/vendedor/historial' + inicial_fecha + final_fecha;
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

        $inicial_fecha.on('dp.change', function(e) {
            $final_fecha.data('DateTimePicker').minDate(e.date);
        });

        $final_fecha.on('dp.change', function(e) {
            $inicial_fecha.data('DateTimePicker').maxDate(e.date);
        });

        var $form_fecha = $('#form_fecha');
        var $modal_fecha = $('#modal_fecha');

        $form_fecha.on('submit', function(e) {
            e.preventDefault();
        });

        $modal_fecha.on('show.bs.modal', function() {
            $form_fecha.trigger('reset');
        });

        $modal_fecha.on('shown.bs.modal', function() {
            $modal_fecha.find('[data-aceptar]').focus();
        });

        $modal_fecha.find('[data-cancelar]').on('click', function() {
            $modal_fecha.modal('hide');
        });

        $modal_fecha.find('[data-aceptar]').on('click', function() {
            $form_fecha.submit();
        });

        $('[data-cambiar]').on('click', function() {
            $('#modal_fecha').modal({
                backdrop: 'static'
            });
        });

        var $modal_asignar = $('#modal_asignar'),
            $form_asignar = $('#form_asignar'),
            $asignar = $('[data-asignar]');
        $asignar.on('click', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $form_asignar.attr('action', href);
            $modal_asignar.modal('show');
        });

        <?php //if ($permiso_eliminar) { ?>
            $('[data-eliminar]').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                bootbox.confirm('Esta seguro que desea eliminar el empleado?', function(result) {
                    if (result) {
                        window.location = url;
                    }
                });
            });
        <?php //} ?>

        <?php //if ($permiso_crear) { ?>
            $(window).bind('keydown', function(e) {
                if (e.altKey || e.metaKey) {
                    switch (String.fromCharCode(e.which).toLowerCase()) {
                        case 'n':
                            e.preventDefault();
                            window.location = '?/empleados/crear';
                            break;
                    }
                }
            });
        <?php //} ?>

        <?php if (true) {
            $url = institucion . '/' . $_institution['imagen_encabezado'];
            $image = file_get_contents($url);
            if ($image !== false) {
                $imag = 'data:image/jpg;base64,' . base64_encode($image);
            }
        ?>
            var table = $('#table').DataFilter({
                filter: false,
                name: 'empleados',
                imag: '<?= imgs . '/logo-color.png'; ?>',
                imag2: '<?= $imag; ?>',
                empresa: '<?= $_institution['nombre']; ?>',
                direccion: '<?= $_institution['direccion'] ?>',
                telefono: '<?= $_institution['telefono'] ?>',
                reports: 'xls|doc|pdf|html'
            });
        <?php } ?>
        <?php //if ($permiso_activar) { ?>
            $('[data-activar]').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                bootbox.confirm('Esta seguro que desea cambiar el estado del distribuidor?', function(result) {
                    if (result) {
                        window.location = url;
                    }
                });
            });
        <?php //} ?>
    });
</script>
<?php require_once show_template('footer-configured'); ?>