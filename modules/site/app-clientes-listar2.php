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
date_default_timezone_set('America/La_Paz');

// Verifica la peticion post
if (is_post()) {
    
    // Verifica la existencia de datos
    if (isset($_POST['id_user'])) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
        require config . '/poligono.php';
  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
            $id_user = $_POST['id_user'];
            $dia = date('w');

            $area = $db->select('*')->from('sys_users a')->join('sys_empleados b', 'a.persona_id = b.id_empleado')->join('gps_rutas c','b.id_empleado = c.empleado_id')
            ->where('a.id_user',$id_user)->where('c.dia',$dia)->fetch_first();

            // Obtiene los usuarios que cumplen la condicion
            $usuario = $db->from('sys_users')->join('sys_empleados','persona_id = id_empleado')->where('id_user',$_POST['id_user'])->fetch_first();
            $emp = $usuario['id_empleado'];
            // se obtiene la ruta del distribuidor(empleado_id)
            $ruta = $db->from('gps_rutas')->where('empleado_id',$emp)->fetch_first();
            $fecha_actual = "".date('Y-m-d')."";
            //buscar si es vendedor o distribuidor
            if($usuario['rol_id'] == 4){
                
                // se obtiene los registros de 
                $dis = $db->query('SELECT b.id_egreso, SUM(b.monto_total)AS monto_total, e.nombres, e.paterno, b.observacion as estadod, 
                    c.id_cliente, c.fecha_registro, c.hora_registro, c.cliente, c.nombre_factura, b.plan_de_pagos as cuentas_por_cobrar, c.nit, c.telefono, c.direccion, 
                    c.descripcion, c.imagen, c.tipo, c.ubicacion AS latitud, c.ubicacion AS longitud,  IF(ED.nro_registros>0,b.estadoe, 0) AS estadoe, b.plan_de_pagos, 
                    b.factura, b.fecha_egreso, b.ruta_id, c.credito, c.dias
                    FROM gps_asigna_distribucion a
                    LEFT JOIN gps_rutas d ON a.ruta_id = d.id_ruta
                    LEFT JOIN sys_empleados e ON d.empleado_id = e.id_empleado
                    LEFT JOIN inv_egresos b ON d.id_ruta = b.ruta_id
                    LEFT JOIN inv_clientes c ON b.cliente_id = c.id_cliente
                    LEFT JOIN (SELECT COUNT(*)AS nro_registros, egreso_id  FROM inv_egresos_detalles d GROUP  BY d.egreso_id) ED ON ED.egreso_id = b.id_egreso
                    WHERE a.distribuidor_id = '.$emp.' AND a.estado = 1 AND b.grupo="" AND b.estadoe > 1 AND b.estadoe < 3 
                    AND (b.fecha_egreso < CURDATE() OR b.fecha_egreso <= e.fecha) GROUP BY b.cliente_id')->fetch();
                
                // se obtiene los registros de 
                $dis1 = $db->query('SELECT b.id_egreso, b.monto_total, e.nombres, e.paterno, b.observacion AS estadod, 
                                c.id_cliente, c.fecha_registro, c.hora_registro, c.cliente, c.nombre_factura, b.plan_de_pagos as cuentas_por_cobrar, c.nit, c.telefono, 
                                c.direccion, c.descripcion, c.imagen, c.tipo, c.ubicacion AS latitud, c.ubicacion AS longitud,  
                                IF(b.distribuidor_estado = "ENTREGA","3",b.estadoe) as estadoe, b.plan_de_pagos, c.credito, c.dias 
                                , b.factura
                                FROM gps_asigna_distribucion a
                                LEFT JOIN gps_rutas d ON a.ruta_id = d.id_ruta
                                LEFT JOIN sys_empleados e ON d.empleado_id = e.id_empleado
                                LEFT JOIN tmp_egresos b ON d.id_ruta = b.ruta_id
                                LEFT JOIN inv_clientes c ON b.cliente_id = c.id_cliente                                
                                WHERE a.distribuidor_id = '. $emp .' AND a.estado = 1 AND b.grupo="" AND b.estadoe > 1 
                                AND (b.distribuidor_estado = "NO ENTREGA" OR b.distribuidor_estado = "ENTREGA") 
                                AND b.estado = 3 GROUP BY b.cliente_id ORDER BY b.estadoe DESC')->fetch();

                // se combina los dos arrays
                $dis = array_merge ($dis, $dis1);
                
                $id_movimientos = array();

                foreach ($dis as $nro => $di) {

                    //valida si es plan de pagos
                    if($di['plan_de_pagos']=='si'){
                        $deuda = $db->select('*')->from('inv_egresos a')
                                                ->join('inv_pagos b','b.movimiento_id = a.id_egreso')
                                                ->join('inv_pagos_detalles c','c.pago_id = b.id_pago')
                                                ->where('c.estado',0)->where('a.plan_de_pagos','si')
                                                ->where_in('a.estadoe', array('3'))
                                                ->where('a.cliente_id',$di['id_cliente'])->fetch_first();
                        //COMENTADO: LA RAZON YA NO SE HACE USO DE ESTADOE = 5            
                        //$dis[$nro]['estadoe'] = ($di['estadoe']==0)? 0 : $di['estadoe']; //: $di['estadoe'] + 3
                        //var_dump($deuda ,$di['cliente_id']); exit;
                        $dis[$nro]['cuentas_por_cobrar'] = ($deuda) ? 'si' : 'no'; 
                    }
                    
                    //Valida tipo de documento
                    if($di['factura'] =='Factura'){
                        $dis[$nro]['facturaEstado'] = 'Si';    
                    }elseif($di['factura'] == 'Nota'){
                        $dis[$nro]['facturaEstado'] = 'No';    
                    }else {
                        $dis[$nro]['facturaEstado'] = '';    
                    } 
                    
                    $dis[$nro]['imagen'] = ($di['imagen'] == '') ? imgs2 . '/image.jpg' : tiendas . '/' . $di['imagen'];
                    $a = explode(',',$di['latitud']);
                    $dis[$nro]['latitud'] = (float)$a[0];
                    $dis[$nro]['longitud'] = (float)$a[1];

                    $id_movimientos[$key] = $di['id_egreso'];
                }

                $id_movimientos = implode(',',$id_movimientos);


                $aux = array();
                foreach ($dis as $nro => $di) {
                    if(false){

                    }else{
                        array_push($aux,$di);
                    }
                }

                if (count($aux) > 0) {
                    $respuesta = array(
                        'estado' => 'd',
                        'cliente' => $aux
                    );
                    echo json_encode($respuesta);                    
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg'=> 'Sin datos'));
                }                
            }else{
                
                $id_almacen = $usuario['almacen_id'];
                // Obtiene los productos
                $fech = date('Y-m-d');
                $clientes = $db->query("SELECT id_cliente, cliente, nombre_factura, nit, telefono, direccion, descripcion, tipo, imagen, ubicacion, 
                                        ubicacion as latitud, ubicacion as longitud, ubicacion as area, e.estadoe, e.estadoe as estadod, a.credito, 
                                        a.dias, a.fecha_registro, a.hora_registro
                                        FROM inv_clientes a
                                        LEFT JOIN (
                                            SELECT b.cliente_id, b.estadoe
                                            FROM inv_egresos b
                                            WHERE b.fecha_egreso = '$fech' AND b.estadoe > 0) AS e ON a.id_cliente = e.cliente_id GROUP BY a.id_cliente")->fetch();

                $polygon = explode('*',$area['coordenadas']);

                foreach ($polygon as $nro => $poly) {
                    $aux = explode(',',$poly);
                    $aux2 = (round($aux[0],6)-0.000044).','.(round($aux[1],6)+0.00003);
                    $polygon[$nro] = str_replace(',', ' ', $aux2);
                }

                $polygon[0] = str_replace(',', ' ', $polygon[$nro]);
                $pointLocation = new pointLocation();
                $clientes2 = array();
                // Reformula los productos
                foreach ($clientes as $nro => $cliente) {
                    $a = explode(',',$cliente['ubicacion']);
                    $point = $a[0].' '.$a[1];
                    $punto = $pointLocation->pointInPolygon($point, $polygon);
                    if($punto == 'dentro'){
                        $cliente['latitud'] = (float)$a[0];
                        $cliente['longitud'] = (float)$a[1];
                        //$cliente['nombres'] = '';
                        //$cliente['paterno'] = '';
                        $cliente['imagen'] = ($cliente['imagen'] == '') ? imgs2 . '/image.jpg' : tiendas . '/' . $cliente['imagen'];

                        if(!$cliente['estadoe'] || $cliente['estadoe'] == null){$cliente['estadoe'] = 0;}

                        //verifica si posee estadoe >= 0
                        if($cliente['estadoe'] >= 0){
                            array_push($clientes2, $cliente);
                        }
                    }
                }

                if (count($clientes2) > 0) {                    
                    // Instancia el objeto
                    $respuesta = array(
                        'estado' => 'v',
                        'cliente' => $clientes2
                    );
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }else {
                     // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg'=> 'Sin datos'));
                }
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
        // Devuelve los resultados
        echo json_encode(array('estado' => 'n',
                                'msg'=> 'Datos no definidos'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido'));
}


?>