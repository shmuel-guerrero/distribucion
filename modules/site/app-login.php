<?php

/**
 * Returns the requested information after verification of received data.
 * performs actions on the database. 
 * CAUTION IN DATA HANDLING
 *
 * @access protected
 * @param Simple-Service-Web 
 * @author Revision Shmuel Guerrero  
 * @return json
 * @static
 * @version @Revision v1 2021-08
 */

// Define las cabeceras
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: application/json; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');


if(is_post()) {

    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['imei']) && isset($_POST['model'])) {
        require config . '/database.php';
  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );


        $usuario = trim($_POST['username']);
        $password = trim($_POST['password']);
        $contrasenia = sha1(prefix . md5($password));
        $imei = trim($_POST['imei']);
        $imei = preg_replace('([^A-Za-z0-9])', '', $imei);
        $model = trim($_POST['model']);
        $estado_device = 'Restringido';
        $token_obtenido = '';

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
            $usuario = $db->query("select id_user,a.persona_id, a.avatar, b.nombres, b.paterno, b.materno, b.genero, b.telefono, b.fecha, a.rol_id 
                                    FROM sys_users a 
                                    LEFT JOIN sys_empleados b ON a.persona_id = b.id_empleado 
                                    WHERE (md5(a.username) = md5('$usuario') or md5(a.email) = md5('$usuario')) 
                                    AND a.password = '$contrasenia' 
                                    AND a.active = '1' limit 1")->fetch_first();

            $id_device = $db->query("SELECT * FROM sys_users_devices WHERE model = '{$model}' AND imei = '{$imei}'")->fetch_first();     

            if ($usuario) {

                //se vrerifica si existe registro de imei y modelo de celular en la BD
                if ((!$id_device['model'] || $id_device['model'] == null) && (!$id_device['imei'] || $id_device['imei'] == null) && $usuario['id_user']) {
                    
                    //SI NO EXISTE REGISTRO SE INSERTA EN LA BASE DE DATOS
                    $fecha = date('Y-m-d');
                    $hora = date('H:i:s');
                    $token_device = sha1(prefix . md5($fecha . $hora . $model . $imei));
                    $datos = array(
                        'fecha_registro' => $fecha,
                        'hora_registro' => $hora,
                        'model' => $model,
                        'user_id' => $usuario['id_user'],
                        'token' => $token_device, 
                        'imei' => $imei
                    );
                    
                    $id_device = $db->insert('sys_users_devices', $datos);
                    $estado_device = 'Registrado';
                    $token_obtenido = $token_device;

                  //SI EXISTE REGISTRO SE VERIFICA SI HAY RELACION ENTRE USUARIO-REGISTRADO Y USUARIO EN SESION
                }else if($id_device['user_id'] == $usuario['id_user']){
                    
                    $estado_device = 'Session';
                    $token_obtenido = $id_device['token'];

                    //SI NO ES EL MISMO USUARIO SE VERIFICA SI EL REGISTRO ESTA EN MODO PREMIUM
                }else if($id_device['tipo'] == 'Premium'){

                    //SE OBTIENE EL REGISTRO DE UN DISPOSITIVO 
                    $registro = $db->query("SELECT * FROM sys_users_devices_detalles WHERE user_id = '{$usuario['id_user']}' AND device_id = '{$id_device['id_device']}'")->fetch_first(); 

                    //SI NO SE TIENE REGISTRO SE INSERTA EL NUEVO REGISTRO
                    if (!$registro) {
                        $fecha = date('Y-m-d');
                        $hora = date('H:i:s');
                        $token_device = sha1(prefix . md5($fecha . $hora . $id_device['model'] . $id_device['imei']));
                        $datos = array(
                            'fecha_registro' => $fecha,
                            'hora_registro' => $hora,
                            'user_id' => $usuario['id_user'],
                            'token' => $token_device,
                            'device_id' => $id_device['id_device']
                        );
                        
                        $id_device = $db->insert('sys_users_devices_detalles', $datos);
                        $estado_device = 'Registrado';
                        $token_obtenido = $token_device;                  
                    
                    //SI SE TIENE REGISTRO SE DEVUELVE VALORES
                    }else {
                        $estado_device = 'Session';
                        $token_obtenido = $registro['token'];
                    }

                }else {

                    //se cierra transaccion
                    $db->commit();
        
                    //se cierra transaccion
                    $db->commit();
                    echo json_encode(array('estado' => 'n',
                                            'estado_device' => $estado_device,
                                            'msg' => 'El usuario esta ligado a otro dispositivo. Acción restringida.'));
                    exit;
                }
               

                if ($usuario['fecha'] != date('Y-m-d')) {

                    $usuario['avatar'] = ($usuario['avatar'] == '') ? imgs2 . '/avatar.jpg' : profiles2 . '/' . $usuario['avatar'];
                    $usuario['id_user'] = (int)$usuario['id_user'];
                    $emp = $usuario['persona_id'];

                    if($usuario['rol_id'] == 4){

                        $dis=$db->query('SELECT b.fecha_egreso, b.monto_total, b.observacion as estadod, c.*, c.ubicacion AS latitud, c.ubicacion AS longitud, b.fecha_egreso, b.estadoe, b.empleado_id FROM gps_asigna_distribucion a
                        LEFT JOIN gps_rutas d ON a.ruta_id = d.id_ruta
                        LEFT JOIN sys_empleados e ON d.empleado_id = e.id_empleado
                        LEFT JOIN inv_egresos b ON d.id_ruta = b.ruta_id
                        LEFT JOIN inv_clientes c ON b.cliente_id = c.id_cliente
                        WHERE a.distribuidor_id = '.$emp.' AND a.estado = 1 AND b.grupo="" AND b.estadoe > 1 AND (b.fecha_egreso < CURDATE() OR b.fecha_egreso <= e.fecha) GROUP BY b.cliente_id')->fetch();

                        $dis1 = $db->query('SELECT b.fecha_egreso, b.monto_total, b.observacion AS estadod, c.*, c.ubicacion AS latitud, c.ubicacion AS longitud, b.fecha_egreso, b.estadoe, b.empleado_id FROM gps_asigna_distribucion a
                        LEFT JOIN gps_rutas d ON a.ruta_id = d.id_ruta
                        LEFT JOIN tmp_egresos b ON d.id_ruta = b.ruta_id
                        LEFT JOIN inv_clientes c ON b.cliente_id = c.id_cliente
                        WHERE a.distribuidor_id = '. $emp .' AND a.estado = 1 AND b.grupo="" AND b.estadoe > 1 AND b.distribuidor_estado = "NO ENTREGA" AND b.estado = 3 GROUP BY b.cliente_id ORDER BY b.estadoe DESC')->fetch();

                        $dis = array_merge ($dis, $dis1);

                        $dis2 = $db->query('SELECT c.*, c.ubicacion AS latitud, c.ubicacion AS longitud, b.fecha_egreso, b.estadoe, b.empleado_id, b.observacion as estadod FROM gps_asigna_distribucion a
                        LEFT JOIN inv_egresos b ON a.grupo_id = b.grupo
                        LEFT JOIN inv_clientes c ON b.cliente_id = c.id_cliente
                        WHERE a.distribuidor_id = '. $emp .' AND a.estado = 1 AND b.grupo!="" AND b.estadoe > 1 AND b.fecha_egreso <= CURDATE() GROUP BY b.cliente_id')->fetch();

                        $dis3 = $db->query('SELECT c.*, c.ubicacion AS latitud, c.ubicacion AS longitud, b.fecha_egreso, b.estadoe, b.empleado_id, b.observacion as estadod FROM gps_asigna_distribucion a
                        LEFT JOIN tmp_egresos b ON a.grupo_id = b.grupo
                        LEFT JOIN inv_clientes c ON b.cliente_id = c.id_cliente
                        WHERE a.distribuidor_id = '. $emp .' AND a.estado = 1 AND b.grupo!="" AND b.estadoe > 1 AND b.distribuidor_estado = "NO ENTREGA" AND b.estado = 3 GROUP BY b.cliente_id')->fetch();
                        //  echo json_encode($dis);exit();

                        $dis2 = array_merge ($dis2, $dis3);

                        $dis = array_merge ($dis, $dis2);
                        
                        $aux = array();
                        foreach ($dis as $nro => $di) {
                            if($usuario['fecha'] >= $di['fecha_egreso'] && $di['estadoe'] == 3){

                            }else{
                                array_push($aux,$dis[$nro]);
                            }
                        }

                         //se cierra transaccion
				        $db->commit();
                        
                        if($usuario){
                            $usuario['ruta'] = '';
                            $usuario['token'] = $token_obtenido;
                            $respuesta = array(
                                'estado' => 's',
                                'estado_device' => $estado_device,
                                'vendedor' => $usuario
                            );
                        }else{
                            $respuesta = array('estado' => 'n',
                                                'msg' => 'No tiene clientes que repartir');
                        }
                    }else{
                        $dia = date('w');
                        $area = $db->select('*')->from('sys_users a')
                                    ->join('sys_empleados b', 'a.persona_id = b.id_empleado')
                                    ->join('gps_rutas c','b.id_empleado = c.empleado_id')
                                    ->where('a.id_user',$usuario['id_user'])
                                    ->where('c.dia',$dia)->fetch_first();
                        
                        //se cierra transaccion
				        $db->commit();
                        $usuario['token'] = $token_obtenido;
                                    
                        if($area){
                            $usuario['ruta'] = $area['nombre'];
                            $respuesta = array(
                                'estado' => 's',
                                'estado_device' => $estado_device,
                                'vendedor' => $usuario
                            );
                        }else{
                            $usuario['ruta'] = '';
                            $respuesta = array(
                                'estado' => 'sr',
                                'estado_device' => $estado_device,
                                'vendedor' => $usuario
                            );
                        }
                    }
                    echo json_encode($respuesta);
                }else{
                    echo json_encode(array('estado' => 'sv'));
                }
            }else{
                //se cierra transaccion
				$db->commit();
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'Datos incorrectos.'));
            }
        } catch (Exception $e) {
            $status = false;
            $error = $e->getMessage();

            //Se devuelve el error en mensaje json
            echo json_encode(array("estado" => 'n', 'msg'=>$error));

            //se cierra transaccion
            $db->rollback();
        }    
        
    } else {
        echo json_encode(array('estado' => 'n',
                                'msg' => 'Datos no definidos'));
    }
}else{
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}
?>