<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_user']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['rol_id']) && isset($_POST['active'])) {
		// Obtiene los datos del user
		$id_user = trim($_POST['id_user']);
		$username = trim($_POST['username']);
		$password = $_POST['password'];
		$email = trim($_POST['email']);
		$active = trim($_POST['active']);
		$rol_id = trim($_POST['rol_id']);
		$almacen_id = trim($_POST['id_almacen']);

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();
	
			// Verifica si es creacion o modificacion
			if ($id_user > 0) {

				// Verifica si existe la contraseña
				if ($password == '') {
					// Instancia el user
					$user = array(
						'username' => trim($username),
						'email' => trim($email),
						'active' => trim($active),
						'rol_id' => trim($rol_id),
						'almacen_id' => $almacen_id
	
					);
				} else {
					// Instancia el user
					$user = array(
						'username' => trim($username),
						'password' => sha1(prefix . md5($password)),
						'email' => trim($email),
						'active' => trim($active),
						'rol_id' => trim($rol_id),
						'almacen_id' => $almacen_id
					);
				}

				// Genera la condicion
				$condicion = array('id_user' => $id_user);
	
				// Actualiza la informacion
				$db->where($condicion)->update('sys_users', $user);

				/* $device = $db->query("SELECT * FROM sys_users_devices WHERE user_id = '{$id_user}'")->fetch_first();
				if (!$device) {
					$dato = array(
						'user_id' => $id_user,
					);
					$id_device = $db->insert('sys_users_devices', $dato);
				} */


				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"), 
					'proceso' => 'u',
					'nivel' => 'l',
					'direccion' => '?/usuarios/guardar',
					'detalle' => 'Se actualizo usuario con identificador número ' . $id_user ,
					'usuario_id' => $_SESSION[user]['id_user']			
				);			
				$db->insert('sys_procesos', $data) ; 
	
				// Define la variable para mostrar los cambios
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Actualización satisfactoria!',
					'message' => 'El registro se actualizó correctamente.'
				);
			} else{

		 		//obtiene el plan habilitado.
				$plan = $db->query("SELECT sp.plan FROM sys_planes sp WHERE sp.estado = 'Activo'")->fetch_first()['plan'];
						
				// se obtiene el limite permitido de creacion registros de clientes
				$limite = validar_plan($db, array('plan' => $plan, 'caracteristica' => 'usuarios'));

				//obtiene la cantidad de registros en la base de datos
				$registros = $db->query("SELECT count(*)as nro_registros FROM sys_users")->fetch_first()['nro_registros'];

				//Valida que los registros sean menor o igual al limite del plan
            	if ($registros <= $limite) { 
					// Instancia el user
					$user = array(
						'username' => trim($username),
						'password' => sha1(prefix . md5($password)),
						'email' => trim($email),
						'avatar' => '',
						'active' => trim($active),
						'login_at' => '0000-00-00 00:00:00',
						'logout_at' => '0000-00-00 00:00:00',
						'rol_id' => trim($rol_id),
						'persona_id' => 0,
						'almacen_id' => $almacen_id
					);
		
					// Guarda la informacion
					$id_user = $db->insert('sys_users', $user);

					/* $dato = array(
						'user_id' => $id_user,
					);
					$id_device = $db->insert('sys_users_devices', $dato); */
					
					// Guarda Historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"), 
						'proceso' => 'c',
						'nivel' => 'l',
						'direccion' => '?/usuarios/guardar',
						'detalle' => 'Se creó usuario con identificador numero ' . $id_user ,
						'usuario_id' => $_SESSION[user]['id_user']			
					);
					
					$db->insert('sys_procesos', $data) ; 
		
					// Define la variable para mostrar los cambios
					$_SESSION[temporary] = array(
						'alert' => 'success',
						'title' => 'Adición satisfactoria!',
						'message' => 'El registro se guardó correctamente.'
					);
				}else {
					// Define la variable para mostrar los cambios
					$_SESSION[temporary] = array(
						'alert' => 'danger',
						'title' => 'Limite de registros!',
						'message' => 'Excedio el limite de registros permitidos en el plan obtenido.'
					);		
				}
			}
			
			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/usuarios/listar');	
		
		} catch (Exception $e) {
			$status = false;
			$error = $e->getMessage();
			//se cierra transaccion
			$db->rollback();

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Problemas en el proceso de interacción con la base de datos.',
				'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
			);
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

?>