<?php

// var_dump(date('Y-m-d').' '.date('H:i:s'));die();
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la proforma
        $nit_ci = trim($_POST['nit_ci']);
        $id_cliente = trim($_POST['id_cliente']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $nro_factura = trim($_POST['nro_factura']);
        $motivo = trim($_POST['motivo']);
        $descripcion = trim($_POST['descripcion']);
        $tipo = 'Baja';

        $monto_total = trim($_POST['monto_total']);
        $almacen_id = trim($_POST['almacen_id']);
        $nro_registros = trim($_POST['nro_registros']);

        $telefono = trim($_POST['telefono_cliente']);
        $validez = trim($_POST['validez']);
        $observacion = trim($_POST['observacion']);
        $direccion = trim($_POST['direccion']);
        $atencion = trim($_POST['atencion']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad']: array();
        $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
        $descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
        $id_egreso = (isset($_POST['id_egreso'])) ? $_POST['id_egreso'] : 0;

        //obtiene al cliente

        if($id_egreso > 0){

            $anterior = $db->select('*')->from('inv_egresos')->where('id_egreso', $id_egreso)->fetch_first();
            
            //modificacion de que uno de sus registro se devolvio para que ya no se genere mas devoluciones
            $db->where('id_egreso', $id_egreso)->update('inv_egresos', array('evento' => 'Devuelto'));

            $datos_distribuidor = $db->query("SELECT * FROM tmp_egresos te WHERE te.distribuidor_fecha = '{$anterior['fecha_egreso']}' 
                AND te.empleado_id = '{$anterior['empleado_id']}' AND ruta_id = '{$anterior['ruta_id']}' GROUP BY te.ruta_id")->fetch_first();
            
            $egreso = array(
                //datostmp_devoluciones
                'fecha_reposicion' => date('Y-m-d'),
                'hora_reposicion' => date('H:i:s'),
                'empleado_id_reposicion' => $_user['persona_id'],
                //datos de egreso
                'id_egreso' => $id_egreso,
                'fecha_egreso' => $anterior['fecha_egreso'],
                'hora_egreso' => $anterior['hora_egreso'],
                'tipo' => $anterior['tipo'],
                'provisionado' => $anterior['provisionado'],
                'descripcion' => $descripcion,
                'nro_factura' => $anterior['nro_factura'],
                'nro_autorizacion' => $anterior['nro_factura'],
                'codigo_control' => $anterior['codigo_control'],
                'fecha_limite' => $anterior['fecha_limite'],
                'monto_total' => $monto_total,
                'descuento_porcentaje' => 0,
                'descuento_bs' => 0,
                'monto_total_descuento' => 0,
                'cliente_id' => $anterior['cliente_id'],
                'nit_ci' => $anterior['nit_ci'],
                'nombre_cliente' => strtoupper($anterior['nombre_cliente']),
                'nro_registros' => $nro_registros,
                'estadoe' => $anterior['estadoe'],
                'coordenadas' => $anterior['coordenadas'],
                'observacion' => 'DEVOLUCION',
                // 'empleado_id' => $_user['persona_id'], // se nulara para poder llevar el control de las devoluciones por empleado
                'empleado_id' => $anterior['empleado_id'],
                'dosificacion_id' => 0,
                'almacen_id' => $almacen_id,
                'motivo_id' => 0,
                'motivo' => $motivo,
                'duracion' => '00:00:00',
                'cobrar' => '',
                'descripcion_venta' => 'DEVOLUCION',
                'ruta_id' => ($datos_distribuidor['ruta_id']) ? $datos_distribuidor['ruta_id'] : 0,
                'distribuidor_fecha' => ($datos_distribuidor['ruta_id']) ? $datos_distribuidor['distribuidor_fecha'] : date('Y-m-d'),
                'distribuidor_hora' => ($datos_distribuidor['distribuidor_hora']) ? $datos_distribuidor['distribuidor_hora'] : date('H:i:s'),
                'distribuidor_id' => ($datos_distribuidor['distribuidor_id']) ? $datos_distribuidor['distribuidor_id'] : 0,
                'grupo' => ''
            );

            // Guarda la informacion
            $id = $db->insert('tmp_reposiciones', $egreso);
             // Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/operaciones/guardar_devolucion',
				'detalle' => 'Se creo inventario egreso con identificador numero ' . $id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);
			
			$db->insert('sys_procesos', $data) ; 

            foreach($productos as $nro => $producto){
                $unidad2 = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidad[$nro])->fetch_first();
                $unidad3 = $unidad2['id_unidad'];
                $cantidad = cantidad_unidad($db, $productos[$nro], $unidad3)*$cantidades[$nro];
                
                /////////////////////////////////////////////////////////////////////////////////////////
                    $Lote = '';
                    $CantidadAux = $cantidad;
                    $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                    foreach ($Detalles as $Fila => $Detalle) :
                        if ($CantidadAux >= $Detalle['lote_cantidad']) :
                            $Datos = [
                                'lote_cantidad' => 0,
                            ];
                            $Cant = $Detalle['lote_cantidad'];
                        elseif ($CantidadAux > 0) :
                            $Datos = [
                                'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                            ];
                            $Cant = $CantidadAux;
                        else :
                            break;
                        endif;
                        $Condicion = [
                            'id_detalle' => $Detalle['id_detalle'],
                        ];
                        $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                        $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                        $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                    endforeach;
                    $Lote = trim($Lote, ',');
                /////////////////////////////////////////////////////////////////////////////////////////
                $id_detalle = $db->select('*')->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->where('producto_id', $productos[$nro])->fetch_first()['id_detalle'];

                $detalle = array(
                    'tmp_reposiciones_id' => $id,
                    'id_detalle' => $id_detalle,
                    'precio' => $precios[$nro],
                    'unidad_id' => $unidad3,
                    'cantidad' => $cantidad,
                    'descuento' => $descuentos[$nro],
                    'producto_id' => $productos[$nro],
                    'egreso_id' => $id_egreso,
                    'lote' => $Lote
                );

                // Guarda la informacion
                $id_detalle = $db->insert('tmp_reposiciones_detalles', $detalle);
                 // Guarda Historial
    			$data = array(
    				'fecha_proceso' => date("Y-m-d"),
    				'hora_proceso' => date("H:i:s"), 
    				'proceso' => 'c',
    				'nivel' => 'l',
    				'direccion' => '?/operaciones/guardar_devolucion',
    				'detalle' => 'Se creo inventario egreso  detalle con identificador numero ' . $id_detalle ,
    				'usuario_id' => $_SESSION[user]['id_user']
    			);
    			$db->insert('sys_procesos', $data) ; 
            }
        }
        $_SESSION[temporary] = array(
            'alert' => 'success',
            'title' => 'Se realizo la devolucion!',
            'message' => 'El registro se realizó correctamente.'
        );
        redirect('?/operaciones/preventas_listar');

		// Envia respuesta
		echo json_encode($respuesta);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>