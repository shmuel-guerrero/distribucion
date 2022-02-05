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
    if (isset($_POST['id_user']) && isset($_POST['id_detalle']) && isset($_POST['cantidad']) && isset($_POST['unidad_id']) && $_POST['cantidad'] != 0) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';

  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {
            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $id_detalle = $_POST['id_detalle'];
            $cantidad = $_POST['cantidad'];
            $id_unidad = $_POST['unidad_id'];
            $token = (isset($_POST['token'])) ? $_POST['token'] : '';
            
            $id_user_recibido = ($_POST['id_user']) ? $_POST['id_user'] : 0;
            $empleado = $db->select('persona_id')->from('sys_users')->where('id_user', $_POST['id_user'])->fetch_first();
            $id_user = $empleado['persona_id'];

            if(!$empleado){
                //se cierra transaccion
                $db->commit();
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'Usuario no registrado.'));
                exit();
            }

            //buscamos el detalle y sus datos
            $detalle = $db->select('*, id_detalle as tmp_egreso_id')->from('inv_egresos_detalles')->where('id_detalle', $id_detalle)->fetch_first();

            /**Validar stock Inicio */
                        
            $id_egreso = ($detalle['egreso_id']) ? $detalle['egreso_id'] : 0;
            //obtenemos el egreso
            $almacen_id = $db->query("SELECT * FROM inv_egresos WHERE id_egreso = '{$id_egreso}'")->fetch_first()['almacen_id'];

            $detalle_producto = array( 0 => $detalle['producto_id']);
            $detalle_cantidad = array( 0 => $cantidad);
            $detalle_unidad = array( 0 => $id_unidad);

            /**Validar stock Fin */

            //validar si es promocion
            if($detalle['promocion_id'] != 0 && $detalle['promocion_id'] != ''){
                //se cierra transaccion
                $db->commit();
                echo json_encode(array('estado' => 'promocion'));
            }else{
                $id_egreso = $detalle['egreso_id'];

                //detalles del egreso  SE AÑADIO LA VALIDACION ==>>> ESTADO != 3
                $egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.id_egreso',$id_egreso)->where('b.estadoe != ', 3)->fetch_first();

                if ($egreso) {                            

                    //detalles productos
                    //$producto = $db->select('*')->from('inv_productos')->where('id_producto',$detalle['producto_id'])->fetch_first();

                    $precio = $detalle['precio'];
                    $precio2 = precio_unidad($db,$detalle['id_producto'],$id_unidad);
                    $monto_sub_total = $cantidad * $precio;
                    //$cantidad_detalle = $detalle['cantidad'] / cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']);
                    $cantidad_detalle = $detalle['cantidad'];
                    $cantidad_aValidar = $cantidad / cantidad_unidad($db, $detalle['producto_id'], $id_unidad);

                    if (validar_cambio_unidad($db, 'inv_egresos_detalles', $id_detalle, $cantidad, $id_unidad)) {
                        if (false) {
                        //if($cantidad_aValidar > $cantidad_detalle){
                            //se cierra transaccion
                            $db->commit();
                            echo json_encode(array('estado' => 'n',
                                                    'msg' => 'Es una cantidad mayor al stock.'));
                            exit;
                        } else { // para mayor 
                                //se crea backup de registros//////
                                $verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $id_egreso, '', '', $empleado['persona_id'], 'SI', 0, "Editado");
                             
                            //////////////////////////////////////////////////////////////////////////
                                $Lotes=$db->query("SELECT producto_id,lote,unidad_id
                                                    FROM inv_egresos_detalles AS ed
                                                    LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
                                                    WHERE id_detalle='$id_detalle'")->fetch();
                                foreach($Lotes as $Fila=>$Lote):
                                    $IdProducto=$Lote['producto_id'];
                                    $UnidadId=$Lote['unidad_id'];
                                    $LoteGeneral=explode(',',$Lote['lote']);
                    
                                    for($i=0;$i<=count($LoteGeneral);++$i):
                                        $SubLote=explode('-',$LoteGeneral[$i]);
                                        $Lot=$SubLote[0];
                                        $Cantidad=$SubLote[1];
                                        $DetalleIngreso=$db->query("SELECT id_detalle,lote_cantidad
                                                                FROM inv_ingresos_detalles
                                                                WHERE producto_id='{$IdProducto}' AND lote='{$Lot}'
                                                                LIMIT 1")->fetch_first();
                                        $CantidadAux=$Cantidad;
                                        $Datos=array(
                                            'lote_cantidad'=>(strval($DetalleIngreso['lote_cantidad'])+strval($CantidadAux)),
                                        );
                                        $db->where('id_detalle', $DetalleIngreso['id_detalle'])->where('lote', $Lot)->update('inv_ingresos_detalles',$Datos);
                                    endfor;
                                endforeach;
                                
                                //se crea backup de registros a eliminar
                                $verifica = backup_registros($db, 'inv_egresos_detalles', 'id_detalle', $id_detalle, '', '', $empleado['persona_id'], 'NO', $verifica_id, "Eliminado");
                                //se eliminan registro
                                $db->delete()->from('inv_egresos_detalles')->where('id_detalle', $id_detalle)->execute(); // eliminamos el detalle
                                // Guarda Historial
                                $data = array(
                                    'fecha_proceso' => date("Y-m-d"),
                                    'hora_proceso' => date("H:i:s"),
                                    'proceso' => 'u',
                                    'nivel' => 'l',
                                    'direccion' => '?/site/app-editar-detalle',
                                    'detalle' => 'Se elimino inventario egreso detalle con identificador numero' . $id_detalle,
                                    'usuario_id' => $id_user
                                );
                                $db->insert('sys_procesos', $data);
                                // -------------------------------*------------------------------
                                $unidad3 = $id_unidad;
                                $cant_uni = cantidad_unidad($db, $detalle['producto_id'], $unidad3) * $cantidad;
                                $Lote='';
                                $CantidadAux=$cant_uni;
                                $Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad
                                                    FROM inv_ingresos_detalles
                                                    WHERE producto_id='{$detalle["producto_id"]}' AND lote_cantidad>0
                                                    ORDER BY id_detalle ASC")->fetch();
                                foreach($Detalles as $Fila=>$Detalle):
                                    if($CantidadAux>=$Detalle['lote_cantidad']):
                                        $Datos=array(
                                            'lote_cantidad'=>0,
                                        );
                                        $Cant=$Detalle['lote_cantidad'];
                                        $db->where('id_detalle', $Detalle['id_detalle'])->update('inv_ingresos_detalles',$Datos);
                                        $CantidadAux=$CantidadAux-$Detalle['lote_cantidad'];
                                        $Lote.=$Detalle['lote'].'-'.$Cant.',';
                                    elseif($CantidadAux>0):
                                        $Datos=array(
                                            'lote_cantidad'=>$Detalle['lote_cantidad']-$CantidadAux,
                                        );
                                        $Cant=$CantidadAux;
                                        $db->where('id_detalle', $Detalle['id_detalle'])->update('inv_ingresos_detalles',$Datos);
                                        $CantidadAux=$CantidadAux-$Detalle['lote_cantidad'];
                                        $Lote.=$Detalle['lote'].'-'.$Cant.',';
                                    else:
                                        break;
                                    endif;
                                endforeach;
                                $Lote=trim($Lote,','); 
                                
                                //validar si la unidad registrada es igual a la nueba unidad
                                if($id_unidad == $detalle['unidad_id']){
                                    
                                    //validadmos si la cantidad nueva es menor a la registrada
                                    if($cantidad < $cantidad_detalle){
                                        //reducimos la cantidad y el precio
                    
                                        $monto_total = $egreso['monto_total'] - (($cantidad_detalle - $cantidad) * $precio);
                                        $aux = $cantidad * cantidad_unidad($db, $detalle['producto_id'], $id_unidad);
                                        
                                        $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $monto_total, 'monto_total_descuento' => ($monto_total - $egreso['descuento_bs']), 'descuento_bs' => $egreso['descuento_bs']));
                                        // $db->where('id_detalle',$id_detalle)->update('inv_egresos_detalles',array('cantidad' => $aux));
                                        $detalle_nuevo = array(
                                            'cantidad' => $aux,
                                            'unidad_id' => $unidad3,
                                            'precio' => ($precio)? $precio : 0,
                                            'descuento' => ($detalle['descuento']) ? $detalle['descuento'] : 0,
                                            'producto_id' => trim($detalle['producto_id']),
                                            'egreso_id' => $id_egreso,
                                            'lote'=>$Lote
                                        );
                    
                                        // echo json_encode($Lote); die();
                                        // Guarda la informacion
                                        $id = $db->insert('inv_egresos_detalles', $detalle_nuevo);
                                        $id_detalle_historial = $id;
                    
                                        //datos tmp
                                        $egreso['monto_total'] = (($cantidad_detalle - $cantidad) * $precio);
                                        $egreso['nro_registros'] = 1;
                                        $egreso['distribuidor_fecha'] = date('Y-m-d');
                                        $egreso['distribuidor_hora'] = date('H:i:s');
                                        $egreso['distribuidor_estado'] = 'DEVUELTO';
                                        $egreso['accion'] = 'Devuelto';
                                        $egreso['distribuidor_id'] = $id_user;
                                        $egreso['estado'] = 3;
                                        $id = $db->insert('tmp_egresos', $egreso);
                                        
                                        $detalle['tmp_egreso_id'] = $id;
                                        $detalle['cantidad'] = $detalle['cantidad'] - ($cantidad * cantidad_unidad($db, $detalle['producto_id'], $id_unidad));
                                        $detalle['unidad_id'] = $id_unidad;
                                        $detalle['precio'] = $precio;
                                        $id = $db->insert('tmp_egresos_detalles', $detalle);
                                        
                                    }elseif($cantidad == $detalle['cantidad']){ //validar si la nueva cantidad es igual a la registrada
                                        //no se realiza ningun cambio
                                        $detalle_nuevo_igual = array(
                                            'cantidad' => $cant_uni,
                                            'unidad_id' => $unidad3,
                                            'precio' => ($precio)? $precio : 0,
                                            'descuento' => $detalle['descuento'],
                                            'producto_id' => $detalle['producto_id'],
                                            'egreso_id' => $id_egreso,
                                            'lote'=>$Lote
                                        );
                                        
                                        // Guarda la informacion
                                        $id = $db->insert('inv_egresos_detalles', $detalle_nuevo_igual);
                                        $id_detalle_historial = $id;
                                    }
                                }else{
                                    $cantidad_nueva = $cantidad_registrada = $cantidad_nueva_total = 0;
                                    $id_unidad_nueva = $id_unidad;
                                    $cantidad_nueva = $cantidad;
                                    //calcular cantidad registrada en unidades
                                    $cantidad_registrada = cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']) * $detalle['cantidad'];
                                    //calcular cantidad nueva en unidades
                                    $cantidad_nueva_total = cantidad_unidad($db, $detalle['producto_id'], $id_unidad_nueva) * $cantidad_nueva;                                

                                    //se validad si la nueva (tamanio) es igual antigua tamanio registrado (cantidad*unidad)
                                    if ($cantidad_nueva_total == $cantidad_registrada) {

                                        //OBTIEN () CANTIDAD DIVIDIDA ENTRE TAMANIO DE LA UNIDAD REDISTRADA ) * PRECIO
                                        $monto_total_anterior_detalle = ($detalle['cantidad'] / cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id'])) * $precio;
                                        //OBTIENE PRECIO DE LA NUEVA UNIDAD
                                        $nuevo_precio_unidad = precio_unidad($db, $detalle['producto_id'], $id_unidad_nueva);
                                        //OBTIENE EL TAMANIO DE LA NUEVA UNIDAD
                                        $nueva_cant_unidad = cantidad_unidad($db, $detalle['producto_id'], $id_unidad_nueva);
                                        //OBTIENE MONTO REGISTRADO - MONTO DEL DETALLE(ANTERIOR) + (CANTIDAD NUEVA * PRECIO NUEVO)
                                        $nuevo_monto_total = $egreso['monto_total'] - $monto_total_anterior_detalle + ($cantidad_nueva * $nuevo_precio_unidad);
                                        $monto_total = $nuevo_monto_total;
                                      
                                        //SE ACTUALIZA EL PRECIO DEL MOVIMIENTO
                                        $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $nuevo_monto_total, 'monto_total_descuento' => ($monto_total - $egreso['descuento_bs']), 'descuento_bs' => $egreso['descuento_bs']));
                                        
                                        //DATOS DEL NUEVO DETALLE A INSERTAR
                                        $detalle_nuevo = array(
                                            'cantidad' => $cantidad_nueva * $nueva_cant_unidad,
                                            'unidad_id' => $id_unidad_nueva,
                                            'precio' => ($nuevo_precio_unidad) ? $nuevo_precio_unidad:0,
                                            'descuento' => ($detalle['descuento']) ? $detalle['descuento']:0,
                                            'producto_id' => trim($detalle['producto_id']),
                                            'egreso_id' => $id_egreso,
                                            'lote'=>$Lote
                                        );
                                        // Guarda la informacion
                                        $id = $db->insert('inv_egresos_detalles', $detalle_nuevo);
                                        $id_detalle_historial = $id;
                        
                                    }else {
                                        //monto total registrado en la base de datos
                                        $monto_total_anterior_detalle = ($detalle['cantidad'] / cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id'])) * $detalle['precio'];
                                        //obtien el precio de la nueva unidad
                                        $nuevo_precio_unidad = precio_unidad($db, $detalle['producto_id'], $id_unidad_nueva);
                                        //obtien el tamanio de la nueva unidad
                                        $nueva_cant_unidad = cantidad_unidad($db, $detalle['producto_id'], $id_unidad_nueva);
                                        //obtiene la cantidad registrada en unidades(1) operacion = cantidad_registrada (tamanio_registrado_unidad)
                                        $anterior_cant_unidad_total = $detalle['cantidad'] * cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']); 
                                        //calcula monto registrado - monto_DEL ITEM REGISTRADO(precio ANTIGUA UNIDAD) + (cantidadnueva * nuevo precio de unidad) 
                                        $nuevo_monto_total = ($egreso['monto_total'] - $monto_total_anterior_detalle) + ($cantidad_nueva * $nuevo_precio_unidad);
                                        $monto_total = $nuevo_monto_total;

                                        //ACTUALIZA EL PRECIO DEL MOVIMIENTO MAS EL CALCULO DEL NUEVO PRECIO
                                        $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $nuevo_monto_total, 'monto_total_descuento' => ($monto_total - $egreso['descuento_bs']), 'descuento_bs' => $egreso['descuento_bs']));
                                        
                                        //ARRAY DE DATOS CON LA NUEVA UNIDA Y PRECIO
                                        $detalle_nuevo = array(
                                            'cantidad' => $cantidad_nueva * $nueva_cant_unidad,
                                            'unidad_id' => $id_unidad_nueva,
                                            'precio' => ($nuevo_precio_unidad) ? $nuevo_precio_unidad:0,
                                            'descuento' => ($detalle['descuento']) ? $detalle['descuento']:0,
                                            'producto_id' => trim($detalle['producto_id']),
                                            'egreso_id' => $id_egreso,
                                            'lote'=>$Lote
                                        );
                                        // Guarda la informacion
                                        $id = $db->insert('inv_egresos_detalles', $detalle_nuevo);
                                        $id_detalle_historial = $id;
                                        
                                        $anterior_cant_unidad_total = 0;

                                        //TAMANIO DE LA NUEVA UNIDAD
                                        $nueva_tamanio_unidad = cantidad_unidad($db, $detalle['producto_id'], $id_unidad_nueva);
                                        //TAMANIO DE ANTERIOR UNIDAD
                                        $anterior_tamanio_unidad = cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']);

                                        //validad  si la nueva (tamanio) es mayor a la registrada (tamanio) para guardar el residuo en el tmp_egresos
                                        if ($nueva_tamanio_unidad >= $anterior_tamanio_unidad) {
                                            
                                            //detales de tmp
                                            $egreso['monto_total'] = ($cantidad_nueva_total < $cantidad_registrada) ?  ($detalle['cantidad'] - ($cantidad_nueva * $nueva_cant_unidad)) * $detalle['precio'] : $cantidad_nueva * $nuevo_precio_unidad;
                                            $egreso['nro_registros'] = 1;
                                            $egreso['distribuidor_fecha'] = date('Y-m-d');
                                            $egreso['distribuidor_hora'] = date('H:i:s');
                                            $egreso['distribuidor_estado'] = 'DEVUELTO';
                                            $egreso['accion'] = 'Devuelto';
                                            $egreso['distribuidor_id'] = $id_user;
                                            $egreso['estado'] = 3;
                                            //se inserta nuevo registro en tabla temporal de los productos devueltos
                                            $id = $db->insert('tmp_egresos', $egreso);
                            
                                            $detalle['tmp_egreso_id'] = $id;
                                            $detalle['cantidad'] = $detalle['cantidad'] - ($cantidad_nueva * $nueva_cant_unidad);
                                            $detalle['unidad_id'] = ($cantidad_nueva_total < $cantidad_registrada) ? $detalle['unidad_id'] : $id_unidad_nueva;
                                            $detalle['precio'] = ($cantidad_nueva_total < $cantidad_registrada) ? $detalle['precio'] : $nuevo_precio_unidad;
                                            //se inserta nuevo registro del detalle en tabla temporal 
                                            $id = $db->insert('tmp_egresos_detalles', $detalle); 
                                        } else {
                                            $residuo = 0;
                                            $residuo = ($detalle['cantidad'] * cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id'])) - ($nueva_cant_unidad * $cantidad_nueva);
                                            if (($residuo % $anterior_tamanio_unidad) == 0) {

                                                $cantidad_actualizar = (!$anterior_tamanio_unidad) ? round(($residuo / $anterior_tamanio_unidad), 0) : 1;
                                                //detales de tmp
                                                $egreso['monto_total'] = $cantidad_actualizar * $$detalle['precio'];
                                                $egreso['nro_registros'] = 1;
                                                $egreso['distribuidor_fecha'] = date('Y-m-d');
                                                $egreso['distribuidor_hora'] = date('H:i:s');
                                                $egreso['distribuidor_estado'] = 'DEVUELTO';
                                                $egreso['distribuidor_id'] = $id_user;
                                                $egreso['estado'] = 3;
                                                //se inserta nuevo registro en tabla temporal de los productos devueltos
                                                $id = $db->insert('tmp_egresos', $egreso);
                                
                                                $detalle['tmp_egreso_id'] = $id;
                                                $detalle['cantidad'] = $cantidad_actualizar;
                                                $detalle['unidad_id'] = $detalle['unidad_id'];
                                                $detalle['precio'] = $detalle['precio'];
                                                //se inserta nuevo registro del detalle en tabla temporal 
                                                $id = $db->insert('tmp_egresos_detalles', $detalle);                                                                                    
                                            }else {
                                                $cantidad_actualizar = (!$anterior_tamanio_unidad) ? round(($residuo / $anterior_tamanio_unidad), 0) : 1;
                                                $sobra = $detalle['cantidad'] - ($cantidad_nueva * $nueva_cant_unidad);
                                                //detales de tmp
                                                $egreso['monto_total'] = $cantidad_actualizar * $detalle['precio'];
                                                $egreso['nro_registros'] = 1;
                                                $egreso['distribuidor_fecha'] = date('Y-m-d');
                                                $egreso['distribuidor_hora'] = date('H:i:s');
                                                $egreso['distribuidor_estado'] = 'DEVUELTO';
                                                $egreso['distribuidor_id'] = $id_user;
                                                $egreso['estado'] = 3;
                                                //se inserta nuevo registro en tabla temporal de los productos devueltos
                                                $id = $db->insert('tmp_egresos', $egreso);
                                
                                                $detalle['tmp_egreso_id'] = $id;
                                                $detalle['cantidad'] = $cantidad_actualizar;
                                                $detalle['unidad_id'] = $detalle['unidad_id'];
                                                $detalle['precio'] = $detalle['precio'];
                                                //se inserta nuevo registro del detalle en tabla temporal 
                                                $id = $db->insert('tmp_egresos_detalles', $detalle);  
                                            }

                                        }                                   
                                    }                
                                }
                                
                                /**
                                 * se inserta la entrega y su detalle en la tabla de inv_egresos_entregas 
                                 * con la finalidad de generar reportes mas exactos
                                 */
                                $id_movimiento = registros_historial($db, '_editar_previo', 'inv_egresos', 'id_egreso', $id_egreso, '', '', $id_user, 'SI', 0);
                                $movimiento = registros_historial($db, '_editar_previo', 'inv_egresos_detalles', 'id_detalle', $id_detalle_historial , '', '', $id_user, 'NO', $id_movimiento);

                                //Cuentas manejo de HGC
                                $id_cli = (int)$egreso['cliente_id'];
                                $clienteCred = $db->from('inv_clientes')->where('id_cliente', $id_cli)->fetch_first();
                                $credito = $clienteCred['credito'];

                                if ($credito == '1' || $credito = 1) {
                                    
                                    $creditoEx = $db->from('inv_pagos')->where('tipo', 'Egreso')->where('movimiento_id', $id_egreso)->fetch_first(); 
                                    if ($creditoEx){
                                        //se crea backup de registros a eliminar
                                        $verifica_id = backup_registros($db, 'inv_pagos', 'movimiento_id', $id_egreso, 'tipo', 'Egreso', $empleado['persona_id'], 'SI', 0, "Eliminado");
                                        //se eliminan registro
                                        $db->delete()->from('inv_pagos')->where('movimiento_id', $id_egreso)->where( 'tipo', 'Egreso')->execute();
                                        //se crea backup de registros a eliminar
                                        $verifica = backup_registros($db, 'inv_pagos_detalles', 'pago_id', $creditoEx['id_pago'], '', '', $empleado['persona_id'], 'NO', $verifica_id, "Eliminado");
                                        //se eliminan registro
                                        $db->delete()->from('inv_pagos_detalles')->where('pago_id', $creditoEx['id_pago'])->execute();
                                    }
                                    
                                    // Instancia el ingreso
                                    $ingresoPlan = array(
                                        'movimiento_id' => $id_egreso,
                                        'interes_pago' => 0,
                                        'tipo' => 'Egreso'
                                    );
                                    // Guarda la informacion del ingreso general
                                    $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
                                    
                                    $parafecha = $db->select('fecha_egreso')->from('inv_egresos')->where('id_egreso', $id_egreso)->fetch_first();
                                    $parafecha = ($parafecha['fecha_egreso']) ? $parafecha['fecha_egreso'] : date('Y-m-d');
                                    
                                    // $Date = date('Y-m-d');
                                    $Date = $parafecha;
                                    $Dias = ' + ' . $clienteCred['dias'] .' days';
                                    $Fecha_pago = date('Y-m-d', strtotime($Date. $Dias));
                                    
                                    $detallePlan = array(
                                        'nro_cuota' => 1,
                                        'pago_id' => $ingreso_id_plan,
                                        'fecha' => $Fecha_pago,
                                        'fecha_pago' => $Fecha_pago,
                                        'monto' => ($monto_total - $egreso['descuento_bs']),
                                        'tipo_pago' => '',
                                        'empleado_id' => $empleado['persona_id'],
                                        'estado'  => '0'
                                    );
                                    // Guarda la informacion
                                    $db->insert('inv_pagos_detalles', $detallePlan);
                                }
                                $deuda = $db->select('*')->from('inv_egresos a')
                                            ->join('inv_pagos b','b.movimiento_id = a.id_egreso')
                                            ->join('inv_pagos_detalles c','c.pago_id = b.id_pago')
                                            ->where('c.estado',0)
                                            ->where('a.plan_de_pagos','si')
                                            ->where('a.cliente_id',$egreso['cliente_id'])->fetch_first();
                                
                                            
                                            // $eg = $db->selet('monto_total, estadoe, plan_de_pagos as cuentas_por_cobrar')->from('tmp_egresos')->where('distribuidor_estado', 'ENTREGA')->where('id_egreso', $id_egreso)->fetch_first();
                                            $monto_total = $db->query("select SUM(monto_total) AS monto_total, 
                                            SUM(monto_total_descuento) AS monto_total_descuento 
                                            FROM inv_egresos WHERE id_egreso='{$id_egreso}' and cliente_id = {$id_cli}")->fetch_first()['monto_total'];
                                

                                //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                                save_process($db, 'u', '?/site/app-editar-detalle', 'edito item de movimiento previa entrega', $id_egreso, $id_user_recibido, $token);                          

                                //se cierra transaccion
                                $db->commit();

                                
                                // $eg = $db->selet('monto_total, estadoe, plan_de_pagos as cuentas_por_cobrar')->from('tmp_egresos')->where('distribuidor_estado', 'ENTREGA')->where('id_egreso', $id_egreso)->fetch_first();
                                $respuesta = array(
                                    'estado' => 's',
                                    'estadoe' => 2,
                                    'cuentas_por_cobrar' => ($deuda)? 'si':'no',
                                    'monto_total' => number_format($monto_total, 2),
                                    'TESTEO' => $mensajito . " " . $monto_total_anterior_detalle
                                );
                                echo json_encode($respuesta);
                        } // fin mayor
                    }else {
                        //se cierra transaccion
                        $db->commit();
                        echo json_encode(array('estado' => 'n',
                                                'msg' => 'Es una cantidad mayor al stock. Verifique que la unidad sea menor o igual tamanio al actual'));
                    }
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'No existe registro.'));
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
                                'msg' => 'datos no definidos.'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido'));
}

?>