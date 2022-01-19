<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_almacen']) && isset($_POST['almacen']) && isset($_POST['direccion']) && isset($_POST['telefono']) && isset($_POST['principal']) && isset($_POST['descripcion'])) {
		// Obtiene los datos del almacén
		$id_almacen = trim($_POST['id_almacen']);
		$almacen = trim($_POST['almacen']);
		$direccion = trim($_POST['direccion']);
		$telefono = trim($_POST['telefono']);
		$principal = trim($_POST['principal']);
		$descripcion = trim($_POST['descripcion']);
		
		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

		try { 

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

			// Instancia el almacén
			$almacen = array(
				'almacen' => $almacen,
				'direccion' => $direccion,
				'telefono' => $telefono,
				'principal' => $principal,
				'descripcion' => $descripcion
			);
		
			// Verifica si es creacion o modificacion
			if ($id_almacen > 0) {

				//actualiza el antiguo almacen principal si existe a secundario para modificar el nuevo almacen
				actualizar_principal($db, $principal, $id_almacen);
				// Genera la condicion
				$condicion = array('id_almacen' => $id_almacen);
				
				// Actualiza la informacion
				$id = $db->where($condicion)->update('inv_almacenes', $almacen);
				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"), 
					'proceso' => 'u',
					'nivel' => 'l',
					'direccion' => '?/almacenes/guardar',
					'detalle' => 'Se actualizo almacén con identificador número ' . $id_almacen ,
					'usuario_id' => $_SESSION[user]['id_user']			
				);			
				$db->insert('sys_procesos', $data) ;
				
				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Actualización satisfactoria!',
					'message' => 'El registro se actualizó correctamente.'
				);
			} else{ 

				//obtiene el plan habilitado.
				$plan = $db->query("SELECT sp.plan FROM sys_planes sp WHERE sp.estado = 'Activo'")->fetch_first()['plan'];
							
				// se obtiene el limite permitido de creacion registros de clientes
				$limite = validar_plan($db, array('plan' => $plan, 'caracteristica' => 'almacenes'));

				//obtiene la cantidad de registros en la base de datos
				$registros = $db->query("SELECT count(*)as nro_registros FROM inv_almacenes")->fetch_first()['nro_registros'];

				//Valida que los registros sean menor o igual al limite del plan
				if ($registros <= $limite) { 

					//actualiza el antiguo almacen principal si existe a secundario para insertar el nuevo almacen
					actualizar_principal($db, $principal, $id_almacen);
					// Guarda la informacion
					$id = $db->insert('inv_almacenes', $almacen);
					// Guarda Historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"), 
						'proceso' => 'c',
						'nivel' => 'l',
						'direccion' => '?/almacenes/guardar',
						'detalle' => 'Se creó almacen con identificador número ' . $id ,
						'usuario_id' => $_SESSION[user]['id_user']			
					);
					
					$db->insert('sys_procesos', $data) ; 
					
					// Instancia la variable de notificacion
					$_SESSION[temporary] = array(
						'alert' => 'success',
						'title' => 'Adición satisfactoria!',
						'message' => 'El registro se guardó correctamente.'
					);
				}else {

					// Instancia la variable de notificacion
					$_SESSION[temporary] = array(
						'alert' => 'danger',
						'title' => 'Adición restringida!',
						'message' => 'Excedio el limite de registros permitidos en el plan obtenido.'
					);
					
				}
			}
			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/almacenes/listar');

		} catch (Exception $e) {
            $status = false;
            $error = $e->getMessage();
			$db->rollback();

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Problemas en el proceso de interacción con la base de datos.',
				'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'
			);
			//se cierra transaccion
			// Redirecciona a la pagina principal o anterior			
			return redirect(back());
			//Se devuelve el error en mensaje json
			//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));
		
        }
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}


function actualizar_principal($db, $principal = 'N', $id_almacen = 0) {
	// Verifica si sera almacén principal
	if ($principal == 'S') {
		// Elimina almacenes principales
		$db->where('principal', 'S')->update('inv_almacenes', array('principal' => 'N'));
		// Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'u',
			'nivel' => 'l',
			'direccion' => '?/almacenes/guardar',
			'detalle' => 'Se actualizo almacén con identificador número ' . $id_almacen ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ;
	}
}

?>