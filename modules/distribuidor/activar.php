<?php

/**
 * TRABAJA CON LOS DATOS REGISTRADOS EN LA TABLA DE EGRESOS CON ESTADO = 2 (PREVENTS), REGISTRANDOLOS EN LA TABLA TEMPORAL COMO DISTRIBUIDOR_ESTADO_ALMACEN
 *  CAMBIANDOLOS DE ESTADO; Y POSTERIOR ELIMINACION DEL REGISTRO PARA REGRESO A ALMACEN Y ACTUALIZANDO LA FECHA DEL DISTRIBUIDOR ESTO CON EL FIN DE GENARAR LA LIQUIDACION
 * 
 */

// Obtiene el id_user
$id_user = (sizeof($params) > 0) ? $params[0] : 0;


// Obtiene el user
$user = $db->from('sys_empleados')->where('id_empleado', $id_user)->fetch_first();
$id_user = $user['id_empleado'];

// Verifica si el user existe
if ($user) {

	//obtiene las preventas registradas estado = 2 asignadas a la ruta del distribuidor
	$egresos = $db->query('SELECT  b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, estadoe as estado
    FROM gps_asigna_distribucion a
    LEFT JOIN inv_egresos b ON a.ruta_id = b.ruta_id
    WHERE a.distribuidor_id = ' . $id_user . ' AND b.grupo = "" AND a.estado = 1 AND b.estadoe = 2 AND b.fecha_egreso < CURDATE()')->fetch();

	$db->where(array('estado' => 2, 'distribuidor_id' => $id_user))->update('tmp_egresos', array('estado' => 1))->execute();
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"),
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/distribuidor/activar',
		'detalle' => 'Se actualizo estado 2 ditribuidor con identificador número ' . $id_user,
		'usuario_id' => $_SESSION[user]['id_user']
	);
	$db->insert('sys_procesos', $data);

	$db->where(array('estado' => 3, 'distribuidor_id' => $id_user))->update('tmp_egresos', array('estado' => 2))->execute();
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"),
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/distribuidor/activar',
		'detalle' => 'Se actualizo estado 3  ditribuidor con identificador número ' . $id_user,
		'usuario_id' => $_SESSION[user]['id_user']
	);
	$db->insert('sys_procesos', $data);



	//    $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $monto_total));
	foreach ($egresos as $nro => $egreso) {
		$egreso['distribuidor_fecha'] = date('Y-m-d');
		$egreso['distribuidor_hora'] = date('H:i:s');
		$egreso['distribuidor_estado'] = 'ALMACEN';
		$egreso['distribuidor_id'] = $id_user;
		$egreso['estado'] = 2;

		//inserta las preventas en estado = 2 en la tabla temporal distribuidor estado = almacen
		$id = $db->insert('tmp_egresos', $egreso);
		// Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"),
			'proceso' => 'c',
			'nivel' => 'm',
			'direccion' => '?/distribuidor/activar',
			'detalle' => 'Se creó egreso con identificador número ' . $id,
			'usuario_id' => $_SESSION[user]['id_user']
		);
		$db->insert('sys_procesos', $data);

		$id_egreso = $egreso['id_egreso'];

		//obtiene los detalles del movimiento
		$egresos_detalles = $db->select('*, egreso_id as tmp_egreso_id')->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->fetch();

		foreach ($egresos_detalles as $nr => $detalle) {
			$detalle['tmp_egreso_id'] = $id;

			//inserrta el detalle de la preventa en  tmp_detalle
			$id_detalle = $db->insert('tmp_egresos_detalles', $detalle);
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'm',
				'direccion' => '?/distribuidor/activar',
				'detalle' => 'Se creó egreso detalle con identificador número ' . $id_detalle,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
		}

		//valida si  la preventa se inserto en temporal
		if ($id) {

			//elimina preventa
			$db->delete()->from('inv_egresos')->where('id_egreso', $id_egreso)->limit(1)->execute();
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/distribuidor/activar',
				'detalle' => 'Se elimino egreso con identificador número' . $id_egreso,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			// Elimina los detalles de la preventa
			$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/distribuidor/activar',
				'detalle' => 'Se elimino egreso detalle con identificador número' . $id_egreso,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
		}
	}

	//obtiene todos los registros de las preventas del distribuidor si existera un grupo de clientes
	$egresos2 = $db->query('SELECT  b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, 
	b.almacen_id as distribuidor_id, b.estadoe as estado FROM gps_asigna_distribucion a 
    LEFT JOIN inv_egresos b ON a.grupo_id = b.grupo
    WHERE a.distribuidor_id = ' . $id_user . ' AND b.grupo != "" AND a.estado = 1 AND b.estadoe = 2 AND b.fecha_egreso < CURDATE()')->fetch();

	foreach ($egresos2 as $nro2 => $egreso2) {
		$egreso2['distribuidor_fecha'] = date('Y-m-d');
		$egreso2['distribuidor_hora'] = date('H:i:s');
		$egreso2['distribuidor_estado'] = 'ALMACEN';
		$egreso2['distribuidor_id'] = $id_user;
		$egreso2['estado'] = 2;

		//inserta en temporal los datos de la preventa
		$id2 = $db->insert('tmp_egresos', $egreso2);

		$id_egreso = $egreso2['id_egreso'];

		//obtiene el detalle de la preventa
		$egresos_detalles2 = $db->from('inv_egresos_detalles')->where('egreso_id', $egreso2['id_egreso'])->fetch();

		foreach ($egresos_detalles2 as $nr => $detalle2) {

			//inserta el detalle en temporal
			$id = $db->insert('tmp_egresos_detalles', $detalle2);
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/distribuidor/activar',
				'detalle' => 'Se creó egreso detalle con identificador número ' . $id,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
		}

		//valida si se guardo registro en temporakl
		if ($id2) {

			//elimina registro de las preventas a grupo de clientes
			$db->delete()->from('inv_egresos')->where('id_egreso', $id_egreso)->limit(1)->execute();
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'd',
				'nivel' => 'l',
				'direccion' => '?/distribuidor/activar',
				'detalle' => 'Se elimino egreso con identificador número' . $id_egreso,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
			/////////////////////////////////////////////////////////////////////
            $Lotes=$db->query("SELECT producto_id,lote,unidad_id
                                FROM inv_egresos_detalles AS ed
                                LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
                                WHERE egreso_id='{$id_egreso}'")->fetch();
            foreach($Lotes as $Fila=>$Lote):
                $IdProducto=$Lote['producto_id'];
                $UnidadId=$Lote['unidad_id'];
                $LoteGeneral=explode(',',$Lote['lote']);
                for($i=0;$i<count($LoteGeneral);++$i):
                    $SubLote=explode('-',$LoteGeneral[$i]);
                    $Lot=$SubLote[0];
                    $Cantidad=$SubLote[1];
                    $DetalleIngreso=$db->query("SELECT id_detalle,lote_cantidad
                                                FROM inv_ingresos_detalles
                                                WHERE producto_id='{$IdProducto}' AND lote='{$Lot}'
                                                LIMIT 1")->fetch_first();
                    $Condicion=array(
                            'id_detalle'=>$DetalleIngreso['id_detalle'],
                            'lote'=>$Lot,
					);
                    $CantidadAux=$Cantidad;
                    $Datos=array(
                            'lote_cantidad'=>(strval($DetalleIngreso['lote_cantidad'])+strval($CantidadAux)),
					);
                    $db->where($Condicion)->update('inv_ingresos_detalles',$Datos);
                endfor;
            endforeach;
            /////////////////////////////////////////////////////////////////////
			// Elimina los detalles
			$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/distribuidor/activar',
				'detalle' => 'Se elimino egreso_detalle con identificador número' . $id_egreso,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
		}
	}


	// Obtiene el nuevo estado
	$fecha_actual = date("Y-m-d");
	$nuevo = date("Y-m-d", strtotime($fecha_actual . "- 1 days"));
	$estado = ($user['fecha'] == date('Y-m-d')) ? $nuevo : date('Y-m-d');

	// Instancia el user
	$user = array(
		'fecha' => $estado,
		'hora' => date('H:i:s')
	);
	// Genera la condicion
	$condicion = array('id_empleado' => $id_user);

	// Actualiza la informacion
	$db->where($condicion)->update('sys_empleados', $user);

	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"),
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/distribuidor/activar',
		'detalle' => 'Se actualizo empleado con identificador número ' . $id_user,
		'usuario_id' => $_SESSION[user]['id_user']
	);
	$db->insert('sys_procesos', $data);


	// Redirecciona a la pagina principal
	redirect('?/distribuidor/listar2');
} else {
	// Error 404
	require_once not_found();
	exit;
}
