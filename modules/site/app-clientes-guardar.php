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
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
date_default_timezone_set('America/La_Paz');

if(is_post()) {
    if (isset($_POST['cliente']) && isset($_POST['telefono']) && isset($_POST['descripcion']) && isset($_POST['imagen']) && isset($_POST['id_user'])) {

        require config . '/database.php';
        require_once libraries . '/upload-class/class.upload.php';

        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $usuario = $db->select('*')->from('sys_users a')->join('sys_empleados b','a.persona_id = b.id_empleado')->where('a.id_user',$_POST['id_user'])->fetch_first();
            
            if($usuario['fecha'] != date('Y-m-d')){

                // if(true){
                $cliente = $_POST['cliente'];
                $nombre_factura = $_POST['nombre_factura'];
                $nit = $_POST['nit'];
                $telefono = $_POST['telefono'];
                $direccion = $_POST['direccion'];
                $descripcion = $_POST['descripcion'];
                $ubicacion = $_POST['latitud'].','.$_POST['longitud'];
                $imagen = $_POST['imagen'];
                $tipo = $_POST['tipo_cliente'];
                $correo = ($_POST['correo']!='')?$_POST['correo']:'';

                $id_cliente = $_POST['id_cliente'];
                $token = (isset($_POST['token'])) ? $_POST['token'] : '';

                if ($id_cliente) {

                    //Datos a ingresar
                    $datos = array(
                        'fecha_modificacion' => date("Y-m-d H:i:s"),
                        'cliente' => $cliente,
                        'nombre_factura' => $nombre_factura,
                        'nit' => $nit,
                        'estado' => 'si',
                        'telefono' => $telefono,
                        'direccion' => $direccion,
                        'descripcion' => $descripcion,
                        'email' => $correo,
                        'ubicacion' => $ubicacion,                        
                        'tipo' => $tipo
                    );

                    //validar si se envio imagen
                    if($imagen != ''){
                        $imagen_final = md5(secret . random_string() . 'miimagen');
                        $extension = 'jpg';
                        $ruta = files . '/tiendas/' . $imagen_final . '.' . $extension;
                        $n_imagen = $imagen_final . '.' . $extension;        
                        file_put_contents($ruta, base64_decode($imagen));

                        $datos['imagen'] = $n_imagen;

                        $ruta_imagen = tiendas . '/' . $n_imagen;
                    }else {

                        $datos['imagen'] = $n_imagen;
                        $ruta_imagen = '';
                    }                    

                    $db->where('id_cliente',$id_cliente)->update('inv_clientes',$datos);
                    $id = $id_cliente;

                     //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                     save_process($db, 'u', '?/site/app-clientes-guardar', 'modifico cliente', $id, $usuario['id_user'], $token);

                }else {   
                    
                    //obtiene el plan habilitado.
                    $plan = $db->query("SELECT sp.plan FROM sys_planes sp WHERE sp.estado = 'Activo'")->fetch_first()['plan'];
                    
                    // se obtiene el limite permitido de creacion registros de clientes
                    $limite = validar_plan($db, array('plan' => $plan, 'caracteristica' => 'clientes'));

                    //obtiene la cantidad de registros en la base de datos
                    $registros = $db->query("SELECT count(*)as nro_registros FROM inv_clientes")->fetch_first()['nro_registros'];

                    //Valida que los registros sean menor o igual al limite del plan
                    if ($registros <= $limite) {
                        
                        //Se prepara la imagen
                        $imagen_final = md5(secret . random_string() . 'miimagen');
                        $extension = 'jpg';
                        $ruta = files . '/tiendas/' . $imagen_final . '.' . $extension;
                        $n_imagen = $imagen_final.'.'.$extension;
                        file_put_contents($ruta, base64_decode($imagen));

                        $datos = array(
                            'fecha_registro' => date("Y-m-d"),
                            'hora_registro' => date("H:i:s"),
                            'cliente' => $cliente,
                            'nombre_factura' => $nombre_factura,
                            'nit' => $nit,
                            'estado' => 'si',
                            'telefono' => $telefono,
                            'direccion' => $direccion,
                            'descripcion' => $descripcion,
                            'email' => $correo,
                            'ubicacion' => $ubicacion,
                            'imagen' => $n_imagen,
                            'tipo' => $tipo
                        );
                        $id = $db->insert('inv_clientes',$datos);
                        $ruta_imagen = tiendas . '/' . $n_imagen;

                         //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                        save_process($db, 'c', '?/site/app-clientes-guardar', 'creo cliente', $id, $usuario['id_user'], $token);
                        
                        //se cierra transaccion
                        $db->commit();                    
                    }else {
                        //se cierra transaccion
                        $db->commit();                    
                            //Se devuelve el error en mensaje json
                        echo json_encode(array('estado' => 'n',
                                                'msg' => 'Excedio el limite de registros permitidos en el plan obtenido.'));
                        exit;
                    }

                }
                
                if($id){
                    $clientee[0] = array(
                        'estado' => 'v',
                        'id_cliente' => $id,
                        'fecha_registro' => date("Y-m-d"),
                        'hora_registro' => date("H:i:s"),
                        'cliente' => $cliente,
                        'nombre_factura' => $nombre_factura,
                        'nit' => $nit,
                        'telefono' => $telefono,
                        'direccion' => $direccion,
                        'descripcion' => $descripcion,
                        'latitud' => $_POST['latitud'],
                        'longitud' => $_POST['longitud'],
                        'imagen' => $ruta_imagen,
                        'tipo_cliente' => $tipo,
                        'estadoe' => 0
                    );

                    $respuesta = array(
                        'estado' => 's',
                        'cliente' => $clientee
                    );
                    
                    //Se devuelve el mensaje json
                    echo json_encode($respuesta);
                }else{
                    //Se devuelve el error en mensaje json
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'El cliente no se guardo.'));
                }
                
            }else{
                //se cierra transaccion
				$db->commit();
                echo json_encode(array('estado' => 'Inactivo'));
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
                                'msg' => 'Datos no definidos.'));
    }
}else{
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definnido.'));
}
?>