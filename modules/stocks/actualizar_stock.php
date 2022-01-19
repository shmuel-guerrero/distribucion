<?php
    if(is_post()):
        if(isset($_POST['id_almacen'])&&isset($_POST['producto'])&&isset($_POST['stocka'])&&isset($_POST['stock'])&&isset($_POST['observacion'])&&isset($_POST['precio_actual'])):
            $id_almacen=trim($_POST['id_almacen']);
            $producto=trim($_POST['producto']);
            $stocka=trim($_POST['stocka']);
            $stock=trim($_POST['stock']);
            $precio_actual=trim($_POST['precio_actual']);
            $observacion=trim($_POST['observacion']);
            if($stocka>$stock):
                //Descuento, baja
                $Cantidad=$stocka-$stock;
                $Datos=array(
                        'fecha_egreso'=>date('Y-m-d'),
                        'hora_egreso'=>date('H:i:s'),
                        'tipo'=>'Baja',
                        'provisionado'=>'N',
                        'descripcion'=>'Se realizo la baja de algunos productos',
                        'nro_factura'=>0,
                        'nro_autorizacion'=>0,
                        'codigo_control'=>0,
                        'fecha_limite'=>'1000-01-01',
                        'monto_total'=>$Cantidad*$precio_actual,
                        'descuento_porcentaje'=>0,
                        'descuento_bs'=>0,
                        'monto_total_descuento'=>0,
                        'cliente_id'=>0,
                        'nombre_cliente'=>'',
                        'nit_ci'=>'',
                        'nro_registros'=>1,
                        'estadoe'=>0,
                        'coordenadas'=>'',
                        'observacion'=>$observacion,
                        'dosificacion_id'=>0,
                        'almacen_id'=>$id_almacen,
                        'empleado_id'=>$_user['persona_id'],
                        'motivo_id'=>0,
                        'duracion'=>'00:00:00',
                        'cobrar'=>'',
                        'grupo'=>'',
                        'descripcion_venta'=>'',
                        'ruta_id'=>0,
                        'estado'=>0,
                        'plan_de_pagos'=>'no',
                );
                $IdEgreso=$db->insert('inv_egresos',$Datos);
                /////////////////////////////////////////////////////////////////////////////////////////
                $Lote='';
                $CantidadAux=$Cantidad;
                $Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$producto' AND lote_cantidad>0 ORDER BY id_detalle ASC LIMIT 3")->fetch();
                foreach($Detalles as $Fila=>$Detalle):
                    if($CantidadAux>=$Detalle['lote_cantidad']):
                        $Datos=array(
                            'lote_cantidad'=>0,
                        );
                        $Cant=$Detalle['lote_cantidad'];
                    elseif($CantidadAux>0):
                        $Datos=array(
                            'lote_cantidad'=>$Detalle['lote_cantidad']-$CantidadAux,
                        );
                        $Cant=$CantidadAux;
                    else:
                        break;
                    endif;
                    $Condicion=array(
                            'id_detalle'=>$Detalle['id_detalle'],
                    );
                    $db->where($Condicion)->update('inv_ingresos_detalles',$Datos);
                    $CantidadAux=$CantidadAux-$Detalle['lote_cantidad'];
                    $Lote.=$Detalle['lote'].'-'.$Cant.',';
                endforeach;
                $Lote=trim($Lote,',');
                /////////////////////////////////////////////////////////////////////////////////////////
                $Datos=array(
                        'precio'=>$precio_actual,
                        'unidad_id'=>1,
                        'cantidad'=>$Cantidad,
                        'descuento'=>0,
                        'producto_id'=>$producto,
                        'egreso_id'=>$IdEgreso,
                        'promocion_id'=>0,
                        'asignacion_id'=>0,
                        'lote'=>$Lote,
                );
                $db->insert('inv_egresos_detalles',$Datos);
                $Mensaje=array(
                        'alert' => 'success',
                        'title' => 'Baja Exitosa',
                        'message' => 'La baja se realizo exitosamente'
                );
            elseif($stocka<$stock):
                $Cantidad=$stock-$stocka;
                //Agregado, salida
                $Datos=array(
                        'fecha_ingreso'=>date('Y-m-d'),
                        'hora_ingreso'=>date('H:i:s'),
                        'tipo'=>'Traspaso',
                        'descripcion'=>$observacion,
                        'monto_total'=>$Cantidad*$precio_actual,
                        'descuento'=>0,
                        'monto_total_descuento'=>0,
                        'nombre_proveedor'=>'',
                        'nro_registros'=>1,
                        'almacen_id'=>$id_almacen,
                        'empleado_id'=>$_user['persona_id'],
                        'transitorio'=>0,
                        'des_transitorio'=>'',
                        'plan_de_pagos'=>'no',
                );
                $IdIngreso=$db->insert('inv_ingresos',$Datos);
                $Lotes=$db->query("SELECT COUNT(id_detalle)AS cantidad FROM inv_ingresos_detalles WHERE producto_id='{$producto}'")->fetch_first();
                $Lotes = ($Lotes['cantidad']) ? $Lotes['cantidad'] : 0;
                ++$Lotes;
                $Datos=array(
                        'cantidad'=>$Cantidad,
                        'costo'=>$precio_actual,
                        'lote'=>'lt'.$Lotes,
                        'lote_cantidad'=>$Cantidad,
                        'producto_id'=>$producto,
                        'ingreso_id'=>$IdIngreso,
                        'vencimiento'=>'1000-01-01',
                        'nro_autorizacion'=>'',
                        'contenedor'=>'',
                        'factura'=>'',
                        'almacen_id'=>$id_almacen,
                        'asignacion_id'=>0,
                        'nro_control'=>'',
                );
                $db->insert('inv_ingresos_detalles',$Datos);
                $Mensaje=array(
                        'alert' => 'success',
                        'title' => 'Ingreso Exitoso',
                        'message' => 'El Ingreso se realizo Exitosamente'
                );
            else:
                $Mensaje=array(
                        'alert' => 'danger',
                        'title' => 'Error',
                        'message' => 'No se modifico la cantidad del stock'
                );
            endif;
            if($db->affected_rows):
                $_SESSION[temporary]=$Mensaje;
            endif;
            redirect('?/stocks/listar');
        else:
            // Error 404
            require_once not_found();
            exit;
        endif;
    else:
        // Error 404
        require_once not_found();
        exit;
    endif;