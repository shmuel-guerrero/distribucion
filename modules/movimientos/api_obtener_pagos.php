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
        $arr_pagos = $db->query("
                            select 
                                /* pago */
                                p.id_pago, 
                                p.movimiento_id,
                                /* detalle de pago */
                                pd.tipo_pago, 
                                pd.fecha_pago,
                                /* ingreso */
                                i.id_ingreso, 
                                i.fecha_ingreso,
                                i.nombre_proveedor,
                                i.monto_total,
                                i.tipo,
                                ifnull(monto,0) as subtotal,
                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado 
                            from inv_pagos p
                            left join inv_pagos_detalles pd on p.id_pago= pd.pago_id 
                            left join inv_ingresos i on i.id_ingreso=p.movimiento_id
                            left join sys_empleados em on em.id_empleado = pd.empleado_id
                            where p.tipo='Ingreso' 
                                and i.fecha_ingreso!=pd.fecha_pago
                                and pd.estado='1'
                                and pd.fecha_pago='$fecha'
                                and pd.empleado_id = $empleado_id 
                        ")->fetch();
    } else {
        $arr_pagos = $db->query("
                            select 
                                /* pago */
                                p.id_pago, 
                                p.movimiento_id,
                                /* detalle de pago */
                                pd.tipo_pago, 
                                pd.fecha_pago,
                                /* ingreso */
                                i.id_ingreso, 
                                i.fecha_ingreso,
                                i.nombre_proveedor,
                                i.monto_total,
                                i.tipo,
                                ifnull(monto,0) as subtotal,
                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado 
                            from inv_pagos p
                            left join inv_pagos_detalles pd on p.id_pago= pd.pago_id 
                            left join inv_ingresos i on i.id_ingreso=p.movimiento_id
                            left join sys_empleados em on em.id_empleado = pd.empleado_id
                            where p.tipo='Ingreso' 
                                and i.fecha_ingreso!=pd.fecha_pago
                                and pd.estado='1'
                                and pd.fecha_pago='$fecha'
                        ")->fetch();
    }

	// Envia respuesta
    echo json_encode([
		'arr_pagos'   => $arr_pagos,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}