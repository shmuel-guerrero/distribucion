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
        $arr_cronogramas = $db->query("
                                select
                                    /* cronograma */
                                    cc.id_cronograma_cuentas,
                                    cc.fecha_pago,
                                    c.periodo,
                                    cc.detalle, 
                                    cc.monto,
                                    cc.tipo_pago,
                                    /* empleado */
                                    concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado
                                from cronograma c 
                                left join cronograma_cuentas cc on c.id_cronograma = cc.cronograma_id 
                                left join sys_empleados e ON cc.empleado_id = e.id_empleado
                                where cc.estado='1' 
                                    and cc.fecha_pago = '$fecha'
                                    and cc.empleado_id = $empleado_id
                                group by c.id_cronograma
                        ")->fetch();
    } else {
        $arr_cronogramas = $db->query("
                                select
                                    /* cronograma */
                                    cc.id_cronograma_cuentas,
                                    cc.fecha_pago,
                                    c.periodo,
                                    cc.detalle, 
                                    cc.monto,
                                    cc.tipo_pago,
                                    /* empleado */
                                    concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado
                                from cronograma c 
                                left join cronograma_cuentas cc on c.id_cronograma = cc.cronograma_id 
                                left join sys_empleados e ON cc.empleado_id = e.id_empleado
                                where cc.estado='1' 
                                    and cc.fecha_pago = '$fecha'
                                group by c.id_cronograma
                        ")->fetch();
    }

	// Envia respuesta
    echo json_encode([
		'arr_cronogramas'   => $arr_cronogramas,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}