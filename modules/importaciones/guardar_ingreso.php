<?php
    if(is_post()):
        if(isset($_POST['id_importacion'])):
            $id_importacion=trim($_POST['id_importacion']);
            $dui='';
            $contenedor=trim($_POST['contenedor']);
            $id_producto=isset($_POST['id_producto'])?$_POST['id_producto']:[];
            $lote=isset($_POST['lote'])?$_POST['lote']:[];
            $fechav=isset($_POST['fechav'])?$_POST['fechav']:[];
            
            $precio_nuevo=isset($_POST['precio_nuevo'])?$_POST['precio_nuevo']:[];
            $cantidad_recibida=isset($_POST['cantidad'])?$_POST['cantidad']:[];

            /******************************************************************/

            $Datos=[
                    'fecha_final'=>date('Y-m-d H:i:s'),
                    'estado' => 'inactivo',
                    'etapa' => 'concluido'
                ];
            $Condicion=[
                    'id_importacion'=>$id_importacion,
                ];
            $db->where($Condicion)->update('inv_importacion',$Datos);

            /******************************************************************/

            $Consulta=$db->query("  SELECT *
                                    FROM inv_importacion
                                    LEFT JOIN inv_proveedores ON inv_proveedores.id_proveedor=inv_importacion.id_proveedor
                                    WHERE id_importacion='{$id_importacion}'
                                ")->fetch_first();
            
            // $movimiento = $db->query("SELECT COUNT(nro_movimiento) as max FROM inv_ingresos WHERE tipo = 'Importacion'")->fetch_first()['max'];
            $movimiento = generarMovimiento($db, $_user['persona_id'], 'IM', $Consulta['almacen_id']);

            $Datos=[
                    'fecha_ingreso'=>date('Y-m-d'),
                    'hora_ingreso'=>date('H:i:s'),
                    'tipo'=>'Importacion',
                    'descripcion'=>$Consulta['descripcion'],
                    'monto_total'=>$Consulta['total'],
                    'descuento'=>'0',
                    'monto_total_descuento'=>$Consulta['total'],
                    'nombre_proveedor'=>$Consulta['proveedor'],
                    'proveedor_id'=>$Consulta['id_proveedor'],
                    'nro_registros'=>$Consulta['nro_registros'],
                    'almacen_id'=>$Consulta['almacen_id'],
                    'empleado_id'=>$Consulta['empleado_id'],
                    'transitorio'=>'0',
                    'des_transitorio'=>'0',
                    'plan_de_pagos'=>'no',
                    'importacion_id'=> $id_importacion,
                    // 'nro_movimiento' => $movimiento
                    'nro_movimiento' => $movimiento, // + 1
                ];
            $IdIngreso=$db->insert('inv_ingresos',$Datos);
            
            for($i=0;$i<count($id_producto);++$i):
                $Datos=[
                        'precio_salida'=>$precio_nuevo[$i],
                        'cantidad_recibida'=>$cantidad_recibida[$i],
                    ];
                $Condicion=[
                        'importacion_id'=>$id_importacion,
                        'producto_id'=>$id_producto[$i],
                    ];
                $db->where($Condicion)->update('tmp_ingreso_detalle',$Datos);
                
                $ProductoT=$db->query(" SELECT * 
                                        FROM tmp_ingreso_detalle 
                                        WHERE   producto_id='{$id_producto[$i]}' AND 
                                                fechav='{$fechav[$i]}' AND 
                                                lote='{$lote[$i]}' AND 
                                                importacion_id='{$id_importacion}'
                                        ")->fetch_first();
                                        
                $Lote=$db->query("  SELECT COUNT(id_detalle)AS cantidad 
                                    FROM inv_ingresos_detalles 
                                    WHERE producto_id={$id_producto[$i]}
                                ")->fetch_first()['cantidad'];
                                
                //$IdIngreso
                $Datos=[
                        'cantidad'=>$ProductoT['cantidad_recibida'],
                        'lote_cantidad'=>$ProductoT['cantidad_recibida'],
                        'costo'=>$ProductoT['precio_salida'],
                        'costo_sin_factura' => 0,
                        'lote'=>$ProductoT['lote'],
                        'lote2'=>$ProductoT['lote'],
                        'producto_id'=>$ProductoT['producto_id'],
                        'ingreso_id'=>$IdIngreso,
                        'vencimiento'=>$ProductoT['fechav'],
                        'dui'=>$dui,
                        'contenedor'=>$contenedor,
                        'factura'=>'',
                        'almacen_id'=>$Consulta['almacen_id'],
                        'asignacion_id'=>1
                    ];
                $db->insert('inv_ingresos_detalles',$Datos);
            endfor;
            
            $Datos1=[
                    'ingreso_id' => $IdIngreso
                ];
            $Condicion1=[
                    'id_importacion'=>$id_importacion,
                ];
            $db->where($Condicion1)->update('inv_importacion',$Datos1);
            
            echo json_encode(['success','Ingreso Registrado Exitosamente']);
        else:
            require_once bad_request();
		    die;
        endif;
    else:
        require_once not_found();
	    die;
    endif;