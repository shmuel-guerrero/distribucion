<?php
require config . '/poligono.php';
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

$gestion = date('Y');
// obtienes las metas vigentes
$Consulta = $db->query("SELECT m.id_meta, m.monto, m.fecha_inicio, m.fecha_fin, m.empleado_id, e.nombres, e.paterno, e.materno
                        FROM inv_meta AS m
                        LEFT JOIN sys_empleados AS e ON e.id_empleado=m.empleado_id
                        WHERE m.fecha_fin BETWEEN CURDATE() AND m.fecha_fin
                        AND YEAR(m.fecha_inicio) = '{$gestion}' AND YEAR(m.fecha_fin) = '{$gestion}'
                        ORDER BY m.id_meta DESC")->fetch();

$hoy = date('Y-m-d');
require_once show_template('header-configured');
?>
<div class='panel-heading'>
    <h3 class='panel-title'>
        <span class='glyphicon glyphicon-option-vertical'></span>
        <strong>Metas vendedor</strong>
    </h3>
</div>
<div class='panel-body'>
    <div class='row'>

        <div class='col-sm-8 hidden-xs'>
            <div class='text-label'>Para agregar nuevas metas hacer clic en el siguiente botón: </div>
        </div>
        <div class='col-xs-12 col-sm-4 text-right'>
            <a href='?/metas/crear' class='btn btn-primary' data-toggle="tooltip" data-placement="top" title="Nueva Meta (Alt+N)"><i class='glyphicon glyphicon-plus'></i><span> Nuevo</span></a>
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
                    <th class='text-nowrap text-middle'>Personal</th>
                    <th class='text-middle'>Ventas del día<?= escape($moneda); ?></th>
                    <th class='text-middle'>Presupuesto<?= escape($moneda); ?></th>
                    <th class='text-middle'>Ejecutado<?= escape($moneda); ?></th>
                    <th class='text-middle'>Devoluciones<?= escape($moneda); ?></th>
                    <th class='text-middle'>Por vender<?= escape($moneda); ?></th>
                    <th class='text-middle'>Avance(%)</th>
                    <th class='text-middle'>Tendencia <?= escape($moneda) ?></th>
                    <th class='text-middle'>Tendencia (%)</th>
                    <th class='text-middle'>Clientes asignados del día</th>
                    <th class='text-middle'>Clientes activos</th>
                    <th class='text-middle'>Clientes inactivos</th>
                    <th class='text-middle'>Clientes no visitados</th>
                    <th class='text-middle'>Fecha Inicial</th>
                    <th class='text-middle'>Fecha Final</th>
                    <th class='text-nowrap'>Opciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr class='active'>
                    <th class='text-nowrap' data-datafilter-filter="false">#</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Personal</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Ventas del día<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Presupuesto<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Ejecutado<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Devoluciones<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Por vender<?= escape($moneda); ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Avance(%)</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Tendencia <?= escape($moneda) ?></th>
                    <th class='text-nowrap' data-datafilter-filter="true">Tendencia (%)</th>
                    <th class='text-nowrap' data-datafilter-filter="true">Clientes asignados del día</th>
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
                foreach ($Consulta as $Fila => $Dato) :
                    /** 
                     * SECCION 1 EJECUTADO REAL MAS DEVOLUCIONES REALIZADAS POR EL DISTRIBUIDOR
                     * */
                    //obtien las ventas efectivas realizadas
                    $Conseguido = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM inv_egresos WHERE anulado != 3 AND tipo='Venta' AND 
                                    empleado_id = '{$Dato['empleado_id']}' AND estadoe != 0 AND fecha_egreso BETWEEN '{$Dato['fecha_inicio']}' 
                                    AND '{$Dato['fecha_fin']}'")->fetch_first();
                    $Conseguido = ($Conseguido['total']) ? $Conseguido['total'] : 0;

                    //obtiene todas las DEVOLUCIONES =  devoluciones menos las ventas directas realizadas
                    $devolucion_venta = $db->query("SELECT IFNULL((A.total_devolucion - AB.total_venta_directa), 0) AS devolucion_real  
                                        FROM (SELECT SUM(a.monto_total) AS total_devolucion, a.distribuidor_id
                                        FROM tmp_egresos a
                                        LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
                                        LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
                                        LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
                                        WHERE a.estado = 2 AND a.empleado_id = '{$Dato['empleado_id']}' AND 
                                        a.distribuidor_estado NOT IN ('ENTREGA', 'VENTA') and b.promocion_id != 1 AND 
                                        a.fecha_egreso BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'
                                        ) A                     
                                        LEFT JOIN (SELECT SUM(a.monto_total)AS total_venta_directa, a.distribuidor_id
                                        FROM tmp_egresos a
                                        LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
                                        LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
                                        LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
                                        WHERE a.estado = 2 AND a.distribuidor_estado = 'VENTA' and b.promocion_id != 1 AND 
                                        a.fecha_egreso BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'
                                        ) AB ON A.distribuidor_id = AB.distribuidor_id")->fetch_first()['devolucion_real']; 
                    $devolucion_venta = ($devolucion_venta) ? $devolucion_venta : 0;
    
                    //VENTAS EFECTIVAS REALIZADAS + DEVOLUCIONES
                    $Conseguido = $Conseguido + $devolucion_venta;

                    /** 
                     * SECCION 2 DEVOLUCIONES
                     * */

  /*                   $devoluciones = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM tmp_egresos 
                    WHERE distribuidor_estado NOT IN ('ENTREGA', 'VENTA') AND empleado_id = '{$Dato['empleado_id']}' 
                    AND fecha_egreso  BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'")->fetch_first()['total'];
                    $devoluciones = ($devoluciones >= 0) ? $devoluciones : 0;
 */
                    //obtiene reposiciones
                    $retornos = $db->query("SELECT IFNULL(SUM(B.monto_total), 0)total_retorno FROM
                                                (
                                                    SELECT id_egreso, nro_factura, 
                                                IF(provisionado='S',nro_factura, nro_autorizacion) AS nro_autorizacion, cliente_id
                                                FROM (SELECT te.id_egreso, te.monto_total, te.nro_factura, te.nro_autorizacion, te.provisionado, 
                                                IF(te.cliente_id=0,te.empleado_id,te.cliente_id)AS cliente_id, 
                                                IF(te.cliente_id=0,'distribuidor_id','cliente_id') AS persona
                                                FROM tmp_egresos te
                                                WHERE te.anulado != 3 AND te.distribuidor_estado IN ('ENTREGA', 'VENTA') 
                                                AND te.empleado_id = '{$Dato['empleado_id']}' 
                                                AND te.fecha_egreso  BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}') A
                                                GROUP BY A.cliente_id, A.nro_autorizacion, A.nro_factura 
                                                    
                                                    ) AB                                                 
                                                LEFT JOIN (SELECT tr.id_egreso, tr.nro_factura, tr.nro_autorizacion, 
                                                IF(tr.cliente_id=0,tr.empleado_id,tr.cliente_id)AS cliente_id, 
                                                tr.monto_total 
                                                FROM tmp_reposiciones tr
                                                ) B ON  B.nro_factura = AB.nro_factura AND B.cliente_id = AB.cliente_id            
                                                AND B.id_egreso = AB.id_egreso")->fetch_first()['total_retorno'];
                    $retornos = ($retornos >= 0) ? $retornos : 0;
                    
                    //TOTAL DEVOLUCIONES = devoluciones mas retornos
                    $total_devoluciones = $devolucion_venta + $retornos;                  
                    
                    /** 
                     * SECCION 3 - VENTAS DEL DIA
                     * */
                    $ventas_dia = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM inv_egresos WHERE anulado != 3 AND 
                                            tipo='Venta' AND estadoe != 0 AND empleado_id='{$Dato['empleado_id']}' 
                                            AND fecha_egreso = '{$hoy}'")->fetch_first();
                    $ventas_dia = ($ventas_dia['total']) ? $ventas_dia['total'] : 0;

                    /**
                     * SECCION 4 POR VENDER
                     */
                    $por_vender = $Conseguido - $total_devoluciones;
                    $por_vender = (($Conseguido - $total_devoluciones) >= 0) ? $Conseguido - $total_devoluciones : 0;

                    $por_vender = ($Dato['monto'] >= $por_vender) ? ((($Dato['monto'] - $por_vender) >= 0) ? $Dato['monto'] - $por_vender : 0) : (($Dato['monto'] - $por_vender) * (-1));
                    $por_vender = ($por_vender != '' && $por_vender != null) ? $por_vender : 0;
                    $bandera = ($Dato['monto'] >= $por_vender) ? true : false;
                    /**
                     * SECCION 5  AVANCE %
                     */
                    $porc = ((($Conseguido - $total_devoluciones >= 0) ? ($Conseguido - $total_devoluciones) : 0) * 100) / $Dato['monto'];
                    $porc = ($porc >= 0) ? $porc : 0;

                    /**
                     * SECCION 6 TENDENCIAS
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

                    $tendencia_bs = ($dias_hoy > 0) ? (($Conseguido / $dias_hoy) * $dias) : 0;
                    $tendencia_pc = ($tendencia_bs / $Dato['monto']) * 100;

                    /**
                     * SECCION 7 CLIENTES ASIGNADOS, ACTIVOS, INCATIVOS Y VISITADOS
                     */

                    // para sacar el los clientes asignados
                    $total_clientes = 0;
                    $rutas = $db->select('*')->from('gps_rutas')->where('dia', date('w'))->where('empleado_id', $Dato['empleado_id'])->fetch();

                    $rutas1 = 0;
                    $rutas2 = 0;
                    foreach ($rutas as $key => $ruta) {
                        $polygon = explode('*', $ruta['coordenadas']);
                        foreach ($polygon as $nro => $poly) {
                            $aux = explode(',', $poly);
                            $aux2 = (round($aux[0], 6) - 0.000044) . ',' . (round($aux[1], 6) + 0.00003);
                            $polygon[$nro] = str_replace(',', ' ', $aux2);
                        }
                        $polygon[0] = str_replace(',', ' ', $polygon[$nro]);
                        $pointLocation = new pointLocation();

                        // Obtiene los clientes
                        $clientes = $db->select('*')->from('inv_clientes')->fetch();
                        foreach ($clientes as $cliente) {
                            $aux2 = explode(',', $cliente['ubicacion']);
                            $aux3 = $aux2[0] + 0.00005;
                            $aux4 = $aux2[1] - 0.00003;
                            $point = $aux3 . ' ' . $aux4;
                            $punto = $pointLocation->pointInPolygon($point, $polygon);
                            if ($punto == 'dentro') {
                                $total_clientes = $total_clientes + 1;
                            }
                        }
                        $id_ruta = $ruta['id_ruta'];
                        $rutas2a  = $db->query("SELECT a.*, COUNT(id_egreso) as contador_no_ventas  FROM gps_rutas a 
                                                LEFT JOIN (SELECT id_egreso, ruta_id FROM inv_egresos WHERE ruta_id > 0 
                                                AND fecha_egreso BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}' 
                                                GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.id_ruta WHERE a.id_ruta = '$id_ruta' 
                                                GROUP BY a.id_ruta ORDER BY fecha")->fetch_first();

                        $rutas1a = $db->query("SELECT a.*, COUNT(id_egreso) as contador_ventas  
                                            FROM gps_rutas a LEFT JOIN (SELECT id_egreso, ruta_id FROM inv_egresos 
                                            WHERE ruta_id > 0 AND fecha_egreso BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}' 
                                            AND tipo = 'Venta' GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.id_ruta 
                                            WHERE a.id_ruta = '$id_ruta' GROUP BY a.id_ruta ORDER BY fecha")->fetch_first();

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
                        <td class='text-nowrap text-right'><?= number_format($total_devoluciones, 2, '.', '') ?></td>
                        <td class='text-nowrap text-right'><?= (($bandera) ? number_format($por_vender, 2, '.', '') : "<b class='text-success'><i class='glyphicon glyphicon-plus-sign' style='margin-right: 3px'></i>" . number_format($por_vender, 2, '.', '')) . '</b>' ?></td>
                        <td class='text-nowrap'><?= number_format($porc, 2, '.', '') ?> %</td>
                        <td class='text-nowrap text-right'><?= ($tendencia_bs >= 0) ? number_format($tendencia_bs, 2) : number_format(0, 2, '.', '') ?></td>
                        <td class='text-nowrap'><?= ($tendencia_pc >= 0) ? number_format($tendencia_pc, 2, '.', '') : number_format(0, 2, '.', '') ?> %</td>
                        <td class='text-nowrap text-right'><?= $total_clientes ?></td>
                        <td class='text-nowrap text-right'><?= $rutas1; ?></td>
                        <td class='text-nowrap text-right'><?= $rutas2; ?></td>
                        <td class='text-nowrap text-right'><?= ($total_clientes - ($rutas1 + $rutas2) > 0) ? ($total_clientes - ($rutas1 + $rutas2)) : 0 ?></td>
                        <td class='text-nowrap'><?= escape(date_decode($Dato['fecha_inicio'], $_institution['formato'])) ?></td>
                        <td class='text-nowrap'><?= escape(date_decode($Dato['fecha_fin'], $_institution['formato'])) ?></td>
                        <td class='text-nowrap'>
                            <a href='?/metas/ver/<?= $Dato['id_meta'] ?>' data-toggle='tooltip' data-title='Ver Meta' data-ver='true'><i class='glyphicon glyphicon-search text-success'></i></a>
                            <a href='?/metas/eliminar-<?= $Dato['id_meta'] ?>' data-toggle='tooltip' data-title='Eliminar Meta' data-eliminar='true'><i class='glyphicon glyphicon-trash text-danger'></i></a>
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
            <p>No existen metas vigentes, para crear nuevas metas hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
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
