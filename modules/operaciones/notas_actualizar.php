<?php

// echo json_encode($_POST); die();
// Verifica si es una peticion ajax y post
if(is_post()) {
    // Verifica la existencia de los datos enviados
    if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
        // Importa la libreria para convertir el numero a letra
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

        // Obtiene los datos de la nota
        $nit_ci = trim($_POST['nit_ci']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        // $id_cliente = trim($_POST['id_cliente']);
        $productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
        $nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
        $cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
        $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
        $descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
        $nro_registros = count($_POST['productos']);//trim($_POST['nro_registros']);
        $monto_total = trim($_POST['monto_total']);
        $des_reserva = trim($_POST['des_reserva']);
        //$reserva = isset($_POST['reserva'])?$_POST['reserva']:'';
        $almacen_id = trim($_POST['almacen_id']);
        
        $id_egreso=trim($_POST['id_egreso']);

        if(isset($_POST['reserva']))
            $reserva = 'si';
        else
            $reserva = 'no';
            
            // Para creditos HGC
        $credito = trim($_POST['credito']);
        $id_cliente = $_POST['id_cliente'];

        //descuento                
        $descuento_porc = (isset($_POST['descuento_porc'])) ? clear($_POST['descuento_porc']) : 0;
        $descuento_bs = (isset($_POST['descuento_bs'])) ? clear($_POST['descuento_bs']) : 0;                
        $monto_total_descuento = $monto_total - $descuento_bs;                

        // Define la variable de subtotales
        $subtotales = array();


        //se envia datos a validar el stock de los productos
		$validar_stock_productos = validar_stock($db, $productos, $cantidades, $unidad, $almacen_id);
        $message = "";

        //validar que se e¡tiene elementos con stock por debajo de lo requerido
		if (count($validar_stock_productos) > 0) {			
			// Instancia la variable de notificacion

            $message = preparar_mensaje($validar_stock_productos);
			$_SESSION[temporary] = array(
                'alert' => 'warning',
                'title' => 'No se puede guardar los registro del formulario; existen observaciones.',
                'message' => $message
            );
            redirect('?/operaciones/notas_editar/'. $id_egreso);
			exit;			
		}

        // Obtiene la moneda
        $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
        $moneda = ($moneda) ? $moneda['moneda'] : '';

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $monto_total);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

        //se guarda una copia de la factura original
        $verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $id_egreso, '', '', $_user['persona_id'], 'SI', 0, "Editado");

        // Instancia la nota
        $nota = array(
            'fecha_egreso' => date('Y-m-d'),
            'hora_egreso' => date('H:i:s'),
            'tipo' => 'Venta',
            'provisionado' => 'S',
            'fecha_limite' => '0000-00-00',
            'monto_total' => $monto_total,
            'descuento_porcentaje' => $descuento_porc,
            'descuento_bs' => $descuento_bs,
            'monto_total_descuento' => $monto_total_descuento,
            'nit_ci' => $nit_ci,
            'nombre_cliente' => strtoupper($nombre_cliente),
            'nro_registros' => $nro_registros,
            'dosificacion_id' => 0,
            'almacen_id' => $almacen_id,
            'cobrar' => $reserva,
            'observacion' => $des_reserva,
            'empleado_id' => $_user['persona_id'],
            'plan_de_pagos' => ($credito == '1' || $credito == 1)?'si':'no', //$plan
        );
        // Guarda la informacion
        $db->where('id_egreso',$id_egreso)->update('inv_egresos', $nota);

        // Devolvemos los productos al lote correspondiente
        /////////////////////////////////////////////////////////////////////
        $Lotes=$db->query("SELECT producto_id,lote,unidad_id
                            FROM inv_egresos_detalles AS ed
                            LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
                            WHERE egreso_id='{$id_egreso}'")->fetch();
        foreach($Lotes as $Fila=>$Lote):
            $IdProducto=$Lote['producto_id'];
            $UnidadId=$Lote['unidad_id'];
            $LoteGeneral=explode(',',$Lote['lote']);
            for($i=0;$i<count($LoteGeneral);++$i):
                $SubLote=explode('-',$LoteGeneral[$i]);
                $Lot=$SubLote[0];
                $Cantidad=$SubLote[1];
                $DetalleIngreso=$db->query("SELECT id_detalle,lote_cantidad
                                            FROM inv_ingresos_detalles
                                            WHERE producto_id='{$IdProducto}' AND lote='{$Lot}'
                                            LIMIT 1")->fetch_first();
                $Condicion=array(
                    'id_detalle'=>$DetalleIngreso['id_detalle'],
                    'lote'=>$Lot,
                );
                $CantidadAux=$Cantidad;
                $Datos=array(
                    'lote_cantidad'=>(strval($DetalleIngreso['lote_cantidad'])+strval($CantidadAux)),
                );
                $db->where($Condicion)->update('inv_ingresos_detalles',$Datos);
            endfor;
        endforeach;
        /////////////////////////////////////////////////////////////////////

        // guarda backup de lo que sea que se haya eliminado
        $verifica = backup_registros($db, 'inv_egresos_detalles', 'egreso_id', $id_egreso, '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");
        
        //eliminamos los detalles del egreso editado
        $db->delete()->from('inv_egresos_detalles')->where('egreso_id',$id_egreso)->execute();

        // Recorre los productos
        foreach ($productos as $nro => $elemento) {
            // Forma el detalle
            $aux = $db->select('*')->from('inv_productos')->where('id_producto', $productos[$nro])->fetch_first();

            if ($aux['promocion'] == 'si') {
                // Forma el detalle
                $prod = $productos[$nro];
                $promos = $db->select('producto_id, precio, unidad_id, cantidad, descuento , id_promocion as egreso_id, cantidad as promocion_id')
                            ->from('inv_promociones')->where('id_promocion', $prod)->fetch();
                /////////////////////////////////////////////////////////////////////////////////////////
                $Lote='';
                $CantidadAux=$cantidades[$nro];
                $Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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
                $detalle = array(
                    'cantidad' => $cantidades[$nro],
                    'precio' => $precios[$nro],
                    'descuento' => 0,
                    'unidad_id' => 7,
                    'producto_id' => $productos[$nro],
                    'egreso_id' => $id_egreso,
                    'promocion_id' => 1,
                    'lote'=>$Lote
                );
                // Guarda la informacion
                $id = $db->insert('inv_egresos_detalles', $detalle);

                // Guarda en el historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"),
                    'proceso' => 'c',
                    'nivel' => 'l',
                    'direccion' => '?/operaciones/notas_actualizar',
                    'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $id,
                    'usuario_id' => $_SESSION[user]['id_user']
                );
                $db->insert('sys_procesos', $data);

                foreach ($promos as $key => $promo) {
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $Lote='';
                    $CantidadAux=$promo['cantidad'] * $cantidades[$nro];
                    $prodpromo = $promo['producto_id'];
                    $Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$prodpromo' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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
                    $promo['lote'] = $Lote;
                    $promo['egreso_id'] = $id_egreso;
                    $promo['promocion_id'] = $productos[$nro];
                    $promo['cantidad'] = $promo['cantidad'] * $cantidades[$nro];

                    // Guarda la informacion
                    $db->insert('inv_egresos_detalles', $promo);
                    // Guarda en el historial
                    $data = array(
                        'fecha_proceso' => date("Y-m-d"),
                        'hora_proceso' => date("H:i:s"),
                        'proceso' => 'c',
                        'nivel' => 'l',
                        'direccion' => '?/operaciones/notas_actualizar',
                        'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $egreso_id,
                        'usuario_id' => $_SESSION[user]['id_user']
                    );
                    $db->insert('sys_procesos', $data);
                }
            } else {
                $id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidad[$nro])->fetch_first();
                $cantidad = $cantidades[$nro] * cantidad_unidad($db, $productos[$nro], $id_unidad['id_unidad']);

                /////////////////////////////////////////////////////////////////////////////////////////
                $Lote='';
                $CantidadAux=$cantidad;
                $Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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

                $detalle = array(
                    'cantidad' => $cantidad,
                    'precio' => $precios[$nro],
                    'unidad_id' => $id_unidad['id_unidad'],
                    'descuento' => ($descuentos[$nro] && $descuentos[$nro] != '') ? $descuentos[$nro] : 0,
                    'producto_id' => $productos[$nro],
                    'egreso_id' => $id_egreso,
                    'lote'=>$Lote
                );
                // Genera los subtotales
                $subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

                // Guarda la informacion
                $id = $db->insert('inv_egresos_detalles', $detalle);
                // Guarda en el historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"),
                    'proceso' => 'c',
                    'nivel' => 'l',
                    'direccion' => '?/operaciones/notas_actualizar',
                    'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $id,
                    'usuario_id' => $_SESSION[user]['id_user']
                );
                $db->insert('sys_procesos', $data);
            }
        }
        
        
        //Cuentas manejo de HGC
        if ($credito == '1' || $credito = 1) {
            
            $creditoEx = $db->from('inv_pagos')->where('tipo', 'Egreso')->where('movimiento_id', $_POST['id_egreso'])->fetch_first(); 
            if ($creditoEx){
                
                // guarda backup de lo que sea que se haya eliminado
                $verifica_id = backup_registros($db, 'inv_pagos', 'movimiento_id', $id_egreso, 'tipo', 'egreso', $_user['persona_id'], 'SI', 0, "Eliminado");
                $verifica = backup_registros($db, 'inv_pagos_detalles', 'pago_id', $creditoEx['id_pago'], '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");
                //se eliminan registro                
                $db->delete()->from('inv_pagos')->where('movimiento_id', $_POST['id_egreso'])->where( 'tipo', 'Egreso')->execute();
                $db->delete()->from('inv_pagos_detalles')->where('pago_id', $creditoEx['id_pago'])->execute();
            }
            
            
            $id_cli = (int)$_POST['id_cliente'];
            $clienteCred = $db->from('inv_clientes')->where('id_cliente', $id_cli)->fetch_first();

            // Instancia el ingreso
            $ingresoPlan = array(
                'movimiento_id' => $_POST['id_egreso'],
                'interes_pago' => 0,
                'tipo' => 'Egreso'
            );
            // Guarda la informacion del ingreso general
            $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
            
            $parafecha = $db->select('fecha_egreso')->from('inv_egresos')->where('id_egreso', $_POST['id_egreso'])->fetch_first();
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
                'monto' => $monto_total,
                'tipo_pago' => '',
                'empleado_id' => $_user['persona_id'],
                'estado'  => '0'
            );
            // Guarda la informacion
            $db->insert('inv_pagos_detalles', $detallePlan);
                    
        }
        
        // Envia respuesta
        echo json_encode(array('status' => 'success', 'responce' =>$id_egreso));

        $_SESSION[temporary] = array(
            'alert' => 'success',
            'title' => 'La actualizaci&oacute;n se realiz&oacute; satisfactoriamente.',
            'message' => 'La actualizaci&oacute;n se guard&oacute; en la base de datos.'
        );
        // header('Location:?/operaciones/notas_editar/'.$id_egreso);
        //header('Location:?/operaciones/notas_listar');
        
    } else {
        // Envia respuesta
        $_SESSION[temporary] = array(
            'alert' => 'danger',
            'title' => 'No se actualiz&oacute; la nota de remisi&oacute;n correctamente.',
            'message' => 'Revise si se actualiz&oacute; alg&uacute; dato.'
        );
        return redirect(back());
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
