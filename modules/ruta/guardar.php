<?php


// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_empleado']) && isset($_POST['nombres']) && isset($_POST['paterno']) && isset($_POST['materno']) && isset($_POST['genero']) && isset($_POST['fecha_nacimiento']) && isset($_POST['telefono']) && isset($_POST['cargo'])) {
		// Obtiene los datos del empleado
		$id_empleado = trim($_POST['id_empleado']);
		$nombres = trim($_POST['nombres']);
		$paterno = trim($_POST['paterno']);
		$materno = trim($_POST['materno']);
		$genero = trim($_POST['genero']);
		$fecha_nacimiento = trim($_POST['fecha_nacimiento']);
		$telefono = trim($_POST['telefono']);
		$cargo = trim($_POST['cargo']);
		
		// Instancia el empleado
		$empleado = array(
			'nombres' => $nombres,
			'paterno' => $paterno,
			'materno' => $materno,
			'genero' => $genero,
			'fecha_nacimiento' => date_encode($fecha_nacimiento),
			'telefono' => $telefono,
			'cargo' => $cargo
		);
		
		// Verifica si es creacion o modificacion
		if ($id_empleado > 0) {
			// Genera la condicion
			$condicion = array('id_empleado' => $id_empleado);
			
			// Actualiza la informacion
			$db->where($condicion)->update('sys_empleados', $empleado);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualizacion satisfactoria!',
				'message' => 'El registro se actualizo correctamente.'
			);
		}else{

			//obtiene el plan habilitado.
			$plan = $db->query("SELECT sp.plan FROM sys_planes sp WHERE sp.estado = 'Activo'")->fetch_first()['plan'];
						
			// se obtiene el limite permitido de creacion registros de clientes
			$limite = validar_plan($db, array('plan' => $plan, 'caracteristica' => 'rutas'));

			//obtiene la cantidad de registros en la base de datos
			$registros = $db->query("SELECT count(*)as nro_registros FROM gps_rutas")->fetch_first()['nro_registros'];

			//Valida que los registros sean menor o igual al limite del plan
			if ($registros <= $limite) { 
				// Guarda la informacion
				$id = $db->insert('sys_empleados', $empleado);
				
				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"), 
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/ruta/guardar',
					'detalle' => 'Se creo empleado con identificador numero ' . $id ,
					'usuario_id' => $_SESSION[user]['id_user']			
				);
				
				$db->insert('sys_procesos', $data) ; 
				
				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Adicion satisfactoria!',
					'message' => 'El registro se guardo correctamente.'
				);
			}else {
				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'danger',
					'title' => 'Adicion restringida!',
					'message' => 'Excedio el limite de registros permitidos en el plan obtenido.'
				);
			}
		}
		
		// Redirecciona a la pagina principal
		redirect('?/empleados/listar');
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