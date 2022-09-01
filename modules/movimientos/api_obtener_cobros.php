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
        $arr_cobros = $db->query("
                            select 
                                /* pago */
                                p.id_pago,
                                p.movimiento_id,
                                /* detalle de pago */
                                pd.tipo_pago, 
                                pd.fecha_pago,
                                /* egreso */
                                e.nro_factura, 
                                e.fecha_egreso,
                                e.nombre_cliente,
                                e.nit_ci, 
                                e.monto_total,
                                e.descuento_bs as descuento, 
                                e.tipo, 
                                ifnull(monto,0) as subtotal,

                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado
                            from inv_pagos p 
                            left join inv_pagos_detalles pd on p.id_pago= pd.pago_id 
                            left join inv_egresos e on e.id_egreso=p.movimiento_id
                            
                            left join sys_empleados em on em.id_empleado = pd.empleado_id
                            where p.tipo='Egreso'
                                and e.fecha_egreso != pd.fecha_pago 
                                and pd.estado='1' 
                                and pd.fecha_pago='$fecha'
                                and pd.empleado_id = $empleado_id        
                        ")->fetch();
    } else {
        $arr_cobros = $db->query("
                            select 
                                /* pago */
                                p.id_pago,
                                p.movimiento_id,
                                /* detalle de pago */
                                pd.tipo_pago, 
                                pd.fecha_pago,
                                /* egreso */
                                e.nro_factura, 
                                e.fecha_egreso,
                                e.nombre_cliente,
                                e.nit_ci, 
                                e.monto_total,
                                e.descuento,  
                                e.tipo, 
                                ifnull(monto,0) as subtotal,
                                /* tipo de descuento */
                                tc.sigla,
                                /* empleado */
                                concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado
                            from inv_pagos p 
                            left join inv_pagos_detalles pd on p.id_pago= pd.pago_id 
                            left join inv_egresos e on e.id_egreso=p.movimiento_id
                            left join inv_tipo_calculo tc on tc.id = e.tipo_calculo_id 
                            left join sys_empleados em on em.id_empleado = pd.empleado_id
                            where p.tipo='Egreso' 
                                and e.fecha_egreso != pd.fecha_pago 
                                and pd.estado = '1' 
                                and pd.fecha_pago = '$fecha'
                                and pd.empleado_id = $empleado_id   
                        ")->fetch();    
    }

	// Envia respuesta
    echo json_encode([
		'arr_cobros'   => $arr_cobros,
	]);
} else {
	// Error 404
	require_once not_found();
	exit;
}