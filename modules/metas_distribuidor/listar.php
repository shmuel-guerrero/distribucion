<?php
require config . '/poligono.php';
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

$gestion = date('Y');
$Consulta = $db->query("SELECT m.id_meta, m.monto, m.fecha_inicio, m.fecha_fin, m.distribuidor_id, e.nombres, e.paterno, e.materno, e.id_empleado
                        FROM inv_metas_distribuidor AS m
                        LEFT JOIN sys_empleados AS e ON e.id_empleado=m.distribuidor_id
                        WHERE m.fecha_fin BETWEEN CURDATE() AND m.fecha_fin
                        AND YEAR(m.fecha_inicio) = '{$gestion}' AND YEAR(m.fecha_fin) = '{$gestion}'
                        ORDER BY m.id_meta DESC")->fetch();
// echo json_encode($Consulta);
$hoy = date('Y-m-d');
require_once show_template('header-configured');
?>
<div class='panel-heading'>
    <h3 class='panel-title'>
        <span class='glyphicon glyphicon-option-vertical'></span>
        <strong>Metas por distribuidor</strong>
    </h3>
</div>
<div class='panel-body'>
    <div class='row'>
        <div class='col-sm-8 hidden-xs'>
            <div class='text-label'>Para agregar nuevas metas hacer clic en el siguiente botón: </div>
        </div>
        <div class='col-xs-12 col-sm-4 text-right'>
            <a href='?/metas_distribuidor/crear' class='btn btn-primary' data-toggle="tooltip" data-placement="top" title="Nueva Meta (Alt+N)"><i class='glyphicon glyphicon-plus'></i><span> Nuevo</span></a>
        </div>
    </div>
    <hr>
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Información</strong>
        <ul>
            <li>Los registros listados son metas vigentes.</li>
        </ul>
    </div>
    <?php
    if ($Consulta) :
    ?>
        <table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
            <thead>
                <tr class='active'>
                    <th class='text-middle'>#</th>
                    <th class='text-nowrap text-middle'>Distribuidor</th>
                    <th class='text-middle'>Distribuciones del día<?= escape($moneda); ?></th>
                    <th class='text-middle'>Presupuesto<?= escape($moneda); ?></th>
                    <th class='text-middle'>Ejecutado<?= escape($moneda); ?></th>
                    <th class='text-middle'>Devueltos<?= escape($moneda); ?></th>
                    <th class='text-middle'>Por distribuir <?= escape($moneda); ?></th>
                    <th class='text-middle'>Avance(%)</th>
                    <th class='text-middle'>Tendencia <?= escape($moneda) ?></th>
                    <th class='text-middle'>Tendencia (%)</th>
                    <th class='text-middle'>Clientes asignados del día</th>
                    <th class='text-middle'>Clientes activos</th>
                    <th class='text-middle'>Clientes inactivos</th>
                    <th class='text-middle'>Clientes no visitados</th>
                    <th class='text-middle'>Fecha Inicial</th>
                    <th class='text-middle'>Fecha Final</th>
                    <th class='text-middle'>Opciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr class='active'>
                    <th class='text-nowrap' data-datafilter-filter="true">#</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Distribuidor</th>
                    <th class='text-middle' data-datafilter-filter="true">Distribuciones del día<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Presupuesto<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Ejecutado<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Devueltos<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Por distribuir <?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Avance(%)</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Tendencia <?= escape($moneda) ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Tendencia (%)</th>
                    <th class='text-middle' data-datafilter-filter="true">Clientes asignados del día</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Clientes activos</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Clientes inactivos</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Clientes no visitados</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Fecha Inicial</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Fecha Final</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Opciones</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                //se itera todas las metas vigentes de los distribuidores
                foreach ($Consulta as $Fila => $Dato) :
                    /**
                     * SECCION 1 EJECUTADO
                     */
                    //obtiene suma  total de las entregas no anuladas en rango de fecha
                    //falta descontar los retornos(reposicion)
                    $Conseguido = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM tmp_egresos 
                                    WHERE anulado != 3 AND distribuidor_estado IN ('ENTREGA', 'VENTA') AND distribuidor_id='{$Dato['distribuidor_id']}' 
                                    AND distribuidor_fecha  BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'")->fetch_first();
                    $Conseguido = ($Conseguido['total'] >= 0) ? $Conseguido['total'] : 0;


                    /** SECCION 2 INICIO DISTRIBUCIONES DEL DIA
                     * OBTIENE EL TOTAL DE ENTREGAS DE LA JORNADA
                     */
                    //Obtiene la suma de las entregas no anuladas en la jornada
                    $ventas_dia = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM tmp_egresos 
                                        WHERE anulado != 3 AND distribuidor_estado IN ('ENTREGA', 'VENTA') AND distribuidor_id='{$Dato['distribuidor_id']}' 
                                        AND distribuidor_fecha  = '{$hoy}'")->fetch_first();
                    $ventas_dia = ($ventas_dia['total']) ? $ventas_dia['total'] : 0;

                    $devoluciones_jornada = $db->query("SELECT IFNULL(SUM(te.monto_total), 0) total_devuelto
                                    FROM tmp_egresos te 
                                    LEFT JOIN tmp_egresos_detalles td ON te.id_tmp_egreso=td.tmp_egreso_id        
                                    WHERE te.estado = 2 AND te.distribuidor_id = '{$Dato['distribuidor_id']}' 
                                    AND te.distribuidor_estado NOT IN ('ENTREGA', 'VENTA') 
                                    AND (te.accion IN ('VentaDevuelto') OR (te.estadoe = 3 AND te.estado = 2 AND te.distribuidor_estado = 'DEVUELTO'))
                                    AND te.distribuidor_fecha = CURDATE() 
                                    AND td.promocion_id != 1")->fetch_first()['total_devuelto'];
                    $devoluciones_jornada = ($devoluciones_jornada >= 0) ? $devoluciones_jornada : 0;

                    $ventas_dia = (($ventas_dia - $devoluciones_jornada) >= 0) ? $ventas_dia - $devoluciones_jornada : (($ventas_dia >= 0) ? $ventas_dia : 0);

                    /**  SECCION 3 -  DEVUELTOS
                     *  
                     */
                    // obtiene la suma de las entregas con estado almacen, no entrega odevuelto en rango de fechas  -  retornos DISTRIBUIDOR
                    $devolucion = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM tmp_egresos 
                                    WHERE distribuidor_estado NOT IN ('ENTREGA', 'VENTA') AND distribuidor_id='{$Dato['distribuidor_id']}' 
                                    AND distribuidor_fecha  BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'")->fetch_first();
                    $devolucion = ($devolucion['total']) ? $devolucion['total'] : 0;

                    $ventas_directas = $db->query("SELECT IFNULL(SUM(a.monto_total), 0) AS total_directas
                                        FROM tmp_egresos a
                                        WHERE a.estado = 2 AND a.distribuidor_id = '{$Dato['distribuidor_id']}' AND a.distribuidor_estado = 'VENTA'
                                        AND  a.distribuidor_fecha BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'")->fetch_first()['total_directas'];
                    $ventas_directas = ($ventas_directas >= 0) ? $ventas_directas : 0;

                    $retorno = $db->query("SELECT IFNULL(SUM(B.monto_total), 0)total_retorno FROM (SELECT nro_factura, 
                                    IF(provisionado='S',nro_factura, nro_autorizacion) AS nro_autorizacion, cliente_id
                                    FROM (SELECT te.monto_total, te.nro_factura, te.nro_autorizacion, te.provisionado, 
                                    IF(te.cliente_id=0,te.empleado_id,te.cliente_id)AS cliente_id, 
                                    IF(te.cliente_id=0,'distribuidor_id','cliente_id') AS persona
                                    FROM tmp_egresos te
                                    WHERE te.anulado != 3 AND te.distribuidor_estado IN ('ENTREGA', 'VENTA') 
                                    AND te.distribuidor_id = '{$Dato['distribuidor_id']}' 
                                    AND te.distribuidor_fecha  BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}' ) A
                                    GROUP BY A.cliente_id, A.nro_autorizacion, A.nro_factura) AB                     
                                    LEFT JOIN (SELECT tr.nro_factura, tr.nro_autorizacion, 
                                    IF(tr.cliente_id=0,tr.empleado_id,tr.cliente_id)AS cliente_id, 
                                    tr.monto_total 
                                    FROM tmp_reposiciones tr
                                    ) B ON  B.nro_factura = AB.nro_factura AND B.cliente_id = AB.cliente_id 
                                    ")->fetch_first()['total_retorno'];
                    $retorno = ($retorno >= 0) ? $retorno : 0;

                    // se realiza los calculos para obtener monto total retorno
                    $retorno = ($devolucion - $ventas_directas) + $retorno;
                    $retorno = ($retorno >= 0) ? $retorno : 0;


                    /** SECCION 4 - POR DISTRIBUIR */
                    $por_distribuir = (($Conseguido - $retorno) >= 0) ? ($Conseguido - $retorno) : 0;
                    $por_distribuir = ($Dato['monto'] >= $por_distribuir) ? ((($Dato['monto'] - $por_distribuir) >= 0) ? $Dato['monto'] - $por_distribuir : 0) : (($Dato['monto'] - $por_distribuir) * (-1));
                    $por_distribuir = ($por_distribuir != '' && $por_distribuir != null) ? $por_distribuir : 0;
                    $bandera = ($Dato['monto'] >= $por_distribuir) ? true : false;

                    // para sacar el los clientes asignados
                    $total_clientes = 0;

                    //se calcula el porcentaje obtenido de la meta total
                    $porc = ((($Conseguido - $retorno >= 0) ? ($Conseguido - $retorno) : 0) * 100) / $Dato['monto'];
                    $porc = ($porc >= 0) ? $porc : 0;
                                                                  
                    $tendencia = (($Conseguido - $retorno) >= 0) ? ($Conseguido - $retorno) : 0;
                    $tendencia = ($tendencia >= 0) ? ($tendencia) : 0;

                    /**
                     * SECCION 6 CALCULO DE TENDENCIAS DE BS Y PORCENTAJE 
                    */

                    $fecha1 = new DateTime($Dato['fecha_inicio']);
                    $fecha2 = new DateTime($Dato['fecha_fin']);
                    $diff = $fecha1->diff($fecha2);
                    $quitar = ($diff->days) / 7;
                    $dias = ($diff->days) - round($quitar);

                    $fechaA = new DateTime($Dato['fecha_inicio']);
                    $fechaA2 = new DateTime(date('Y-m-d'));
                    $diffA = $fechaA->diff($fechaA2);
                    $quitarA = ($diffA->days) / 7;
                    $dias_hoy = ($diffA->days) - round($quitarA);
                    
                    $tendencia_bs = ($dias_hoy > 0) ? (($tendencia / $dias_hoy) * $dias) : 0;

                    $tendencia_pc = ($tendencia_bs / $Dato['monto']) * 100;     
                    
                    /**
                     * SECCION 7 CALCULOS DE CLIENTES ASIGNADOS, ACTIVOS, INACTIVOS, VISITADOS
                     */
                    //obtiene todas las rutas asignadas al distribuidor en rango de fechas
                    $rutas = $db->select('gps_rutas.*')->from('gps_asigna_distribucion as ad')
                        ->join('gps_rutas', 'ad.ruta_id = gps_rutas.id_ruta')
                        ->where('ad.distribuidor_id', $Dato['distribuidor_id'])
                        ->where('ad.fecha_ini >=', $Dato["fecha_inicio"])
                        ->where('ad.fecha_fin <=', $Dato["fecha_fin"])->fetch();

                    $rutas1 = 0;
                    $rutas2 = 0;

                    //se itera las rutas obtenidas
                    foreach ($rutas as $key => $ruta) {
                        $polygon = explode('*', $ruta['coordenadas']);

                        //se itera las coordenadas para preparar los puntos del polygono
                        foreach ($polygon as $nro => $poly) {
                            $aux = explode(',', $poly);
                            $aux2 = (round($aux[0], 6) - 0.000044) . ',' . (round($aux[1], 6) + 0.00003);
                            $polygon[$nro] = str_replace(',', ' ', $aux2);
                        }

                        $polygon[0] = str_replace(',', ' ', $polygon[$nro]);
                        $pointLocation = new pointLocation();

                        // Obtiene los clientes
                        $clientes = $db->select('*')->from('inv_clientes')->fetch();

                        //se itera los clientes validando si estan dentro del poligono
                        foreach ($clientes as $cliente) {
                            $aux2 = explode(',', $cliente['ubicacion']);
                            $aux3 = $aux2[0] + 0.00005;
                            $aux4 = $aux2[1] - 0.00003;
                            $point = $aux3 . ' ' . $aux4;
                            $punto = $pointLocation->pointInPolygon($point, $polygon);

                            //si esta dentro del poligono se increenta variable de cantidad de clientes
                            if ($punto == 'dentro') {
                                $total_clientes = $total_clientes + 1;
                            }
                        }

                        //id de la ruta iterada
                        $id_ruta = $ruta['id_ruta'];

                        //obtiene, cuenta todas las no entregas en rango de fecha del distribuidor; de las asignaciones realizadas a la ruta
                        $rutas2a = $db->query("SELECT a.*, COUNT(id_egreso) as contador_no_ventas
                                                FROM gps_asigna_distribucion a
                                                LEFT JOIN (SELECT id_egreso, ruta_id
                                                        FROM tmp_egresos
                                                        WHERE ruta_id > 0
                                                        AND distribuidor_fecha
                                                        BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'
                                                        AND distribuidor_estado='NO ENTREGA'
                                                        AND distribuidor_id='{$Dato['distribuidor_id']}'
                                                        GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.ruta_id
                                                WHERE a.ruta_id = '$id_ruta'
                                                GROUP BY a.ruta_id")->fetch_first();

                        //obtiene, cuenta todas las entregas en rango de fecha del distribuidor; de las asignaciones realizadas a la ruta
                        $rutas1a = $db->query("SELECT a.*, COUNT(id_egreso) as contador_ventas
                                                FROM gps_asigna_distribucion a
                                                LEFT JOIN (SELECT id_egreso, ruta_id
                                                        FROM tmp_egresos
                                                        WHERE ruta_id > 0
                                                        AND distribuidor_fecha
                                                        BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'
                                                        AND distribuidor_estado='ENTREGA'
                                                        AND distribuidor_id='{$Dato['distribuidor_id']}'
                                                        AND tipo = 'Venta'
                                                        GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.ruta_id
                                                WHERE a.ruta_id = '$id_ruta'
                                                GROUP BY a.ruta_id")->fetch_first();

                        $rutas2 = $rutas2 + $rutas2a['contador_no_ventas'];
                        $rutas1 = $rutas1 + $rutas1a['contador_ventas'];
                    }

                ?>
                    <tr>
                        <td class='text-nowrap'><?= $Fila + 1 ?></td>
                        <td class='text-nowrap text-uppercase'><?= $Dato['nombres'] . ' ' . $Dato['paterno'] . ' ' . $Dato['materno'] ?></td>
                        <td class='text-nowrap text-right'><?= number_format($ventas_dia, 2, '.', '') ?></td>
                        <td class='text-nowrap text-right'><?= number_format($Dato['monto'], 2, '.', '') ?></td>
                        <td class='text-nowrap text-right'><?= number_format($Conseguido, 2, '.', '') ?></td>
                        <td class='text-nowrap text-right'><?= number_format($retorno, 2, '.', '') ?></td>
                        <td class='text-nowrap text-right'><?= ($bandera) ? number_format($por_distribuir, 2, '.', '') : "<b class='text-success'><i class='glyphicon glyphicon-plus-sign' style='margin-right: 3px'></i>" . number_format($por_distribuir, 2, '.', '') . '</b>' ?></td>
                        <td class='text-nowrap'><?= number_format($porc, 2, '.', '') ?> %</td>
                        <td class='text-nowrap text-right'><?= ($tendencia_bs > 0) ? number_format($tendencia_bs, 2, '.', '') : number_format(0, 2, '.', '') ?></td>
                        <td class='text-nowrap'><?= ($tendencia_pc > 0) ? number_format($tendencia_pc, 2) : number_format(0, 2, '.', '')  ?> %</td>
                        <td class='text-nowrap text-right'><?= $total_clientes ?></td>
                        <td class='text-nowrap text-right'><?= $rutas1; ?></td>
                        <td class='text-nowrap text-right'><?= $rutas2; ?></td>
                        <td class='text-nowrap text-right'><?= ($total_clientes - ($rutas1 + $rutas2) > 0) ? ($total_clientes - ($rutas1 + $rutas2)) : 0 ?></td>
                        <td class='text-nowrap'><?= escape(date_decode($Dato['fecha_inicio'], $_institution['formato'])) ?></td>
                        <td class='text-nowrap'><?= escape(date_decode($Dato['fecha_fin'], $_institution['formato'])) ?></td>
                        <td class='text-nowrap'>
                            <a href='?/metas_distribuidor/ver/<?= $Dato['id_meta'] ?>' data-toggle='tooltip' data-title='Ver Meta' data-ver='true'><i class='glyphicon glyphicon-search'></i></a>
                            <a href='?/metas_distribuidor/eliminar-<?= $Dato['id_meta'] ?>' data-toggle='tooltip' data-title='Eliminar Meta' data-eliminar='true'><i class='glyphicon glyphicon-trash'></i></a>
                        </td>
                    </tr>
                <?php
                endforeach;
                ?>
            </tbody>
        </table>
    <?php
    else :
    ?>
        <div class="alert alert-danger">
            <strong>Advertencia!</strong>
            <p>No existen metas registradas en la base de datos, para crear nuevas metas hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
        </div>
    <?php
    endif;
    ?>
</div>
<script src='<?= js; ?>/jquery.dataTables.min.js'></script>
<script src='<?= js; ?>/dataTables.bootstrap.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script src='<?= js; ?>/jquery.maskedinput.min.js'></script>
<script src='<?= js; ?>/jquery.base64.js'></script>
<script src='<?= js; ?>/pdfmake.min.js'></script>
<script src='<?= js; ?>/vfs_fonts.js'></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src='<?= js; ?>/moment.min.js'></script>
<script src='<?= js; ?>/moment.es.js'></script>
<script src='<?= js; ?>/bootstrap-datetimepicker.min.js'></script>
<script>
    $(document).on('click', '[data-eliminar]', function(e) {
        e.preventDefault();
        let fila = this.parentNode.parentNode;
        let url = $(this).attr('href');
        url = url.split('-');
        bootbox.confirm('Está seguro que desea eliminar esta meta?', function(result) {
            if (result) {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: url[0],
                    data: {
                        'id_meta': url[1]
                    }
                }).done(function(ruta) {
                    if (ruta) {
                        $.notify({
                            message: 'La ruta fue registrada satisfactoriamente.'
                        }, {
                            type: 'success'
                        });
                        fila.parentNode.removeChild(fila);
                    } else {
                        $.notify({
                            message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos, verifique si la se guardó parcialmente.'
                        }, {
                            type: 'danger'
                        });
                    }
                }).fail(function() {
                    $.notify({
                        message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos, verifique si la se guardó parcialmente.'
                    }, {
                        type: 'danger'
                    });
                });
            }
        });
    });
    $(window).bind('keydown', function(e) {
        if (e.altKey || e.metaKey) {
            switch (String.fromCharCode(e.which).toLowerCase()) {
                case 'n':
                    e.preventDefault();
                    window.location = '?/metas/crear';
                    break;
            }
        }
    });
    $(function() {

        var table = $('#table').DataFilter({
            filter: true,
            name: 'reporte_ventas_manuales',
            reports: 'xls|doc|pdf|html'
        });

    });
</script>
<?php require_once show_template('footer-configured');
