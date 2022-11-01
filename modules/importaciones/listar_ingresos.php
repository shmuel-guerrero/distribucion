<?php
    $IdImportacion=isset($params[0])?$params[0]:false;
    if($IdImportacion):
        $Importacion=$db->query("SELECT total,total_gastos,total_costo
                                FROM inv_importacion
                                WHERE id_importacion='{$IdImportacion}'")->fetch_first();
        $DetalleImportacion=$db->query("SELECT SUM(total)AS ingreso,SUM(total_gasto)AS costo
                                        FROM inv_importacion_gasto
                                        WHERE importacion_id='{$IdImportacion}'
                                    ")->fetch_first();
        if(true):
            $total=$Importacion['total'];
            $total_gastos=$Importacion['total_gastos'];
            $total_costo=$Importacion['total_costo'];
            $Consulta=$db->query("  SELECT p.id_producto,p.codigo,p.nombre_factura as nombre,id.fechav,id.lote,u.unidad,id.precio_ingreso,id.precio_salida,id.cantidad,id.cantidad as cant_aux
                                    FROM tmp_ingreso_detalle AS id
                                    LEFT JOIN inv_productos AS p ON id.producto_id=p.id_producto
                                    LEFT JOIN inv_unidades AS u ON id.unidad_id=u.id_unidad
                                    WHERE id.importacion_id='{$IdImportacion}'
                                ")->fetch();
                                
            $TotalImportacion=$db->query("  SELECT SUM(total_gasto)AS total
                                            FROM inv_importacion_gasto
                                            WHERE importacion_id='{$IdImportacion}'
                                        ")->fetch_first()['total'];
                                        
            $Gastos=$db->query("SELECT cantidad,precio_ingreso
                        FROM tmp_ingreso_detalle
                        WHERE importacion_id='{$IdImportacion}'")->fetch();
            $TotalGasto=0;
            
            foreach($Gastos as $Fila=>$Gasto):
                $TotalGasto=$TotalGasto+($Gasto['cantidad']*$Gasto['precio_ingreso']);
            endforeach;
            
            for($i=0;$i<count($Consulta);++$i):
                $AuxCantidad=$Consulta[$i]['cantidad']*$Consulta[$i]['precio_ingreso'];
                $AuxCantidad=$AuxCantidad/$TotalGasto;
                $AuxCantidad=$AuxCantidad*$TotalImportacion;
                $gasto_individual = round($AuxCantidad,2);
                $AuxCantidad=$AuxCantidad/$Consulta[$i]['cantidad'];
                $AuxCantidad=$AuxCantidad+$Consulta[$i]['precio_ingreso'];
                $AuxCantidad=round($AuxCantidad,2);
                $Extra=[
                        'precio_venta'=>$AuxCantidad,
                        'total_importacion'=>$TotalImportacion,
                        'gatos_individual' => $gasto_individual
                        //'total_gasto'=>$TotalGasto,
                    ];
                $Consulta[$i]=array_merge($Consulta[$i],$Extra);
            endfor;
            
            
            
            echo json_encode($Consulta);
        else:
            require_once not_found();
	        die;
        endif;
    else:
        require_once not_found();
	    die;
    endif;