<?php
/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

$fecha = isset($_POST['fecha']) ? $_POST['fecha']: date('Y-m-d');
$personal_report = isset($_POST['personal']) ? true : false;

$empleado_id = $_user['persona_id']; // usuario sesion actual

// Verifica si es una peticion ajax
if (is_ajax()) {
    if ($personal_report) {
        $arr_ingresos = $db->query("
                            select
                                row_number() over(
                                    order by
                                        m.fecha_movimiento asc,
                                        m.hora_movimiento asc
                                ) as nro,
                                /* movimiento */
                                m.id_movimiento,
                                m.fecha_movimiento,
                                m.hora_movimiento,
                                m.nro_comprobante,
                                m.tipo,
                                m.monto,
                                m.concepto,
                                m.observacion,
                                m.empleado_id,
                                'Efectivo' as tipo_pago,
                                /* empleado */
                                concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado
                            from caj_movimientos m
                            left join sys_empleados e ON m.empleado_id = e.id_empleado
                            where m.tipo = 'i'
                                and m.fecha_movimiento='$fecha'
                                and m.empleado_id = $empleado_id
                            order by
                                m.fecha_movimiento desc,
                                m.hora_movimiento desc
                        ")->fetch();
    } else {
        $arr_ingresos = $db->query("
                            select
                                row_number() over(
                                    order by
                                        m.fecha_movimiento asc,
                                        m.hora_movimiento asc
                                ) as nro,
                                /* movimiento */
                                m.id_movimiento,
                                m.fecha_movimiento,
                                m.hora_movimiento,
                                m.nro_comprobante,
                                m.tipo,
                                m.monto,
                                m.concepto,
                                m.observacion,
                                m.empleado_id,
                                'Efectivo' as tipo_pago,
                                /* empleado */
                                concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado
                            from caj_movimientos m
                            left join sys_empleados e ON m.empleado_id = e.id_empleado
                            where m.tipo = 'i'
                                and m.fecha_movimiento='$fecha'
                            order by
                                m.fecha_movimiento desc,
                                m.hora_movimiento desc
                        ")->fetch();
    }

	// Envia respuesta
    echo json_encode([
		'arr_ingresos'   => $arr_ingresos,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}