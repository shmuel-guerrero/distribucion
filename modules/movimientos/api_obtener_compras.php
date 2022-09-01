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
        $arr_compras = $db->query("
                            select
                                /* ingreso */
                                i.id_ingreso,
                                i.fecha_ingreso,
                                i.nombre_proveedor,
                                i.tipo,
                                i.plan_de_pagos,
                                i.monto_total,
                                IF(i.plan_de_pagos='si','Credito','Efectivo') as tipo_de_pago,
                                /* planes de pago */
                                p.id_pago, 
                                p.movimiento_id, 
                                pd.tipo_pago,
                                pd.fecha_pago,
                                ifnull(sum(IF(pd.estado=1,pd.monto,0)),0) subtotal,      
                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado
                            from inv_ingresos i
                            left join inv_pagos p on p.movimiento_id = i.id_ingreso 
                            left join inv_pagos_detalles pd on pd.pago_id = p.id_pago
                            left join sys_empleados em on em.id_empleado = i.empleado_id
                            where i.tipo='Compra'
                                
                                and i.fecha_ingreso = '$fecha' 
                                and i.empleado_id = $empleado_id
                            group by 
                                i.id_ingreso
                            order by
                                i.id_ingreso, 
                                i.plan_de_pagos desc
                        ")->fetch();
    } else {
        $arr_compras = $db->query("
                            select
                                /* ingreso */
                                i.id_ingreso,
                                i.fecha_ingreso,
                                i.nombre_proveedor,
                                i.plan_de_pagos,
                                i.monto_total,
                                /* planes de pago */
                                p.id_pago, 
                                p.movimiento_id, 
                                pd.tipo_pago,
                                pd.fecha_pago,
                                ifnull(sum(pd.monto),0) subtotal,        
                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado
                            from inv_ingresos i
                            left join inv_pagos p on p.movimiento_id = i.id_ingreso 
                            left join inv_pagos_detalles pd on pd.pago_id = p.id_pago
                            left join sys_empleados em on em.id_empleado = i.empleado_id
                            where i.tipo='Compra'
                                and if(i.plan_de_pagos = 'no', i.estado = 'V', pd.estado = 1) 
                                and i.fecha_ingreso = '$fecha' 
                                and i.empleado_id = $empleado_id
                            group by 
                                i.id_ingreso,
                                pd.tipo_pago
                            order by
                                i.id_ingreso, 
                                i.plan_de_pagos desc
                        ")->fetch();
    }

	// Envia respuesta
    echo json_encode([
		'arr_compras'   => $arr_compras,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}