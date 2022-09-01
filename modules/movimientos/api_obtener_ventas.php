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
        $arr_ventas = $db->query("
                            select
                                /* egreso */
                                e.id_egreso,
                                e.nro_factura,
                                e.fecha_egreso,
                                e.nombre_cliente,
                                e.nit_ci,
                                e.tipo,
                                e.plan_de_pagos,
                                e.monto_total,
                                e.descuento_bs as descuento,
                                IF(e.plan_de_pagos='si','Credito','Efectivo') AS tipo_de_pago,
                                /* tipo de descuento */
                                /* tc.sigla,*/
                                /* planes de pago */
                                p.id_pago, 
                                p.movimiento_id, 
                                IF(e.plan_de_pagos='si','Credito','Efectivo') AS tipo_pago,
                                pd.fecha_pago,
                                ifnull(sum(IF(pd.estado=1,pd.monto,0)),0) subtotal,
                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado
                            from inv_egresos e
                            /* left join inv_tipo_calculo tc on tc.id = e.tipo_calculo_id */
                            left join inv_pagos p on p.movimiento_id = e.id_egreso 
                            left join inv_pagos_detalles pd on pd.pago_id = p.id_pago
                            left join sys_empleados em on em.id_empleado = e.empleado_id
                            where e.tipo='Venta' 
                                and e.estadoe = 0
                                and e.fecha_egreso = '$fecha'
                                and e.empleado_id = $empleado_id
                            group by 
                                e.id_egreso,
                                pd.tipo_pago
                            order by
                                e.id_egreso, 
                                e.plan_de_pagos desc
                        ")->fetch();
    } else {
        $arr_ventas = $db->query("
                            select
                                /* egreso */
                                e.id_egreso,
                                e.nro_factura,
                                e.fecha_egreso,
                                e.nombre_cliente,
                                e.nit_ci,
                                e.tipo,
                                e.plan_de_pagos,
                                e.monto_total,
                                e.descuento,
                                e.tipo_de_pago,
                                /* tipo de descuento */
                                tc.sigla,
                                /* planes de pago */
                                p.id_pago, 
                                p.movimiento_id, 
                                pd.tipo_pago,
                                pd.fecha_pago,
                                ifnull(sum(pd.monto),0) subtotal,
                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado
                            from inv_egresos e
                            left join inv_tipo_calculo tc on tc.id = e.tipo_calculo_id 
                            left join inv_pagos p on p.movimiento_id = e.id_egreso 
                            left join inv_pagos_detalles pd on pd.pago_id = p.id_pago
                            left join sys_empleados em on em.id_empleado = e.empleado_id
                            where e.tipo='Venta' 
                                and if(e.plan_de_pagos = 'no', e.estado = 'V', pd.estado = 1)
                                and e.fecha_egreso = '$fecha'
                            group by 
                                e.id_egreso, 
                                pd.tipo_pago
                            order by 
                                e.id_egreso, 
                                e.plan_de_pagos desc
                        ")->fetch();
    }

	// Envia respuesta
    echo json_encode([
		'arr_ventas'   => $arr_ventas,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}