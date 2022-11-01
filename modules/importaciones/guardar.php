<?php
if(is_post()):
    if(isset($_POST['descripcion'])): // isset($_POST['nombre_proveedor'])&&
        // echo json_encode($_POST); die();
        $id_proveedor=trim($_POST['nombre_proveedor']);
        $id_almacen  =trim($_POST['almacen_id']);

        // $id_proveedor  =trim($_POST['id_proveedor']);
        $descripcion =trim($_POST['descripcion']);
        $total       =trim($_POST['monto_total']);

        $nro_facturag = trim($_POST['nro_facturag']);
		$fecha_factura = trim($_POST['fecha_factura']);;
		$fecha_factura = $fecha_format=(is_date($fecha_factura)) ? date_encode($fecha_factura): "0000-00-00";
		
        $nombre_proveedor = trim($_POST['nombre_proveedor']);
        $VecProveedor=explode("|",$nombre_proveedor);
        if(isset($VecProveedor[1])){
            $nombre_proveedor = $VecProveedor[1];  
        }
        
        if( is_numeric($VecProveedor[0]) ){
            $id_proveedor = trim($VecProveedor[0]);
        }
        else{
            $ver_proveedor = $db->query("SELECT id_proveedor 
                                         FROM inv_proveedores 
                                         WHERE proveedor = '$id_proveedor' 
                                        ")->fetch_first();
            
            if(!$ver_proveedor['id_proveedor']){
    		    $id_proveedor = $db->insert('inv_proveedores',array('proveedor'=> $nombre_proveedor,'direccion'=>''));
    		}else{
    		    $id_proveedor = trim($ver_proveedor['id_proveedor']);
    		}
        }

        $fechai=date('Y-m-d H:i:s');
        if(isset($_POST['fechai'])):
            $fechai=trim($_POST['fechai']);
        endif;

        $nro_cuentas = trim($_POST['nro_cuentas']);
		
		$plan = trim($_POST['forma_pago']); //1 contado //2 plan de pagos //3 pago anticipado
		switch($plan){
		    case 1:
    			$tipoP="Contado";
    			break;
		    case 2:
    			$fechas = (isset($_POST['fecha'])) ? $_POST['fecha']: array();
    			$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota']: array();
    			$tipoP="A Credito";
    			break;
		    case 3:
    			$tipoP="Pago Anticipado";
    			break;
		}
		
    	$fechav     = (isset($_POST['fechas'])) ? $_POST['fechas'] : array();
        $lote       = (isset($_POST['lotes'])) ? $_POST['lotes'] : array();
        $cantidad   = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $costo      = (isset($_POST['costos'])) ? $_POST['costos'] : array();
        $producto_id= (isset($_POST['productos'])) ? $_POST['productos'] : array();
        
        $importe    = isset($_POST['importe'])    ?$_POST['importe']    :[];
        $unidad_id  = isset($_POST['unidad_id'])  ?$_POST['unidad_id']  :[];

        $observacion=$_POST['observacion'];
    	$banco=$_POST['banco_id'];
    	$nro_doc=$_POST['nro_doc'];
    	$tipo_pago=$_POST['tipo_pago'];
	
        $id_importacion=isset($_POST['id_importacion'])?$_POST['id_importacion']:0;
        
        if($id_importacion!=0):
            $db->delete()->from('inv_importacion')->where('id_importacion',$id_importacion)->limit(1)->execute();
            $db->delete()->from('tmp_ingreso_detalle')->where('importacion_id',$id_importacion)->execute();
        endif;

        $IdUsuario=$_SESSION[user]['id_user'];
        $IdEmpleado=$db->query("SELECT persona_id FROM sys_users WHERE id_user='{$IdUsuario}'")->fetch_first()['persona_id'];
        $nro_correlativo = $db->query("SELECT MAX(nro_correlativo) as max FROM inv_importacion")->fetch_first()['max'];
        if($nro_correlativo == ''){
            $nro_correlativo = 0;    
        }
        
        $Datos=[
                'fecha_inicio'=>$fechai,
                'fecha_final'=>$fechai,
                'total'=>$total,
                'descripcion'=>$descripcion,
                'nro_registros'=>count($fechav),
                'id_proveedor'=>$id_proveedor,
                'almacen_id'=>$id_almacen,
                'fecha_factura'=>$fecha_factura,
    			'nro_factura' => $nro_facturag,
    			'empleado_id'=>$IdEmpleado,
    			'nro_correlativo'=>$nro_correlativo +1
            ];
        $IdImportacion=$db->insert('inv_importacion',$Datos);

        for($i=0;$i<count($fechav);++$i):
            
            $vec_fecha=explode("/",$fechav[$i]);
            $nueva_fecha=$vec_fecha[2]."-".$vec_fecha[1]."-".$vec_fecha[0];
            
            $Datos=[
                    'precio_ingreso'=>$costo[$i],
                    'precio_salida'=>$costo[$i],
                    'cantidad'=>$cantidad[$i],
                    'fechav'=>$nueva_fecha,
                    'lote'=>$lote[$i],
                    'importacion_id'=>$IdImportacion,
                    'producto_id'=>$producto_id[$i],
                    'unidad_id'=>$unidad_id[$i]
                ];
            $db->insert('tmp_ingreso_detalle',$Datos);
        endfor;

        if($id_importacion!=0):
            $Condicion=[
                    'importacion_id'=>$id_importacion,
                ];
            $Datos=[
                    'importacion_id'=>$IdImportacion,
                ];
            $db->where($Condicion)->update('inv_importacion_gasto',$Datos);
            $Consulta=$db->query("  SELECT IFNULL(SUM(total),0)AS total,IFNULL(SUM(total_gasto),0)AS total_gasto 
                                    FROM inv_importacion_gasto 
                                    WHERE importacion_id='{$IdImportacion}'
                                ")->fetch_first();
            $Condicion=[
                    'id_importacion'=>$IdImportacion,
                ];
            $Datos=[
                    'total_gastos'=>$Consulta['total'],
                    'total_costo'=>$Consulta['total_gasto'],
                ];
            $db->where($Condicion)->update('inv_importacion',$Datos);
        endif;





        /************************************************************************/
        // ARMAMOS EN PLAN DE PAGOS
		switch($plan){
    		case 1:
            	$detallePlan = array(
        			'movimiento_id'=>$IdImportacion,
        			'interes_pago'=>0,
        			'tipo'=>'Importacion',
        		);
        		// Guarda la informacion
            	$id_pago=$db->insert('inv_pagos', $detallePlan);
            
            	$detallePlan = array(
            			'pago_id'=>$id_pago,
            			'fecha' => date('Y-m-d'),
            			'monto' => $total,			
            			'monto_programado' => $total,			
            			'estado' => 1,		
            			'fecha_pago' => date('Y-m-d'),
            			'hora_pago' => date("H:i:s"),
            			
            			'tipo_pago' => $tipo_pago,
            			'nro_pago' => $nro_doc,			
            			'nro_cuota'=>0,
            			'empleado_id'=>$_user['persona_id'],
            			'deposito'=>"inactivo",
            			'fecha_deposito'=>'0000-00-00',
            
                		'codigo'=>0,	
                		'observacion_anulado'=>0,	
                		'fecha_anulado'=>'0000-00-00',	
                		'coordenadas'=>"",	
                		'ingreso_id'=>0,
            			'banco_id' => $banco,			
    
            			'observacion'=>$observacion,
            		);
            		
            		// Guarda la informacion
            	$db->insert('inv_pagos_detalles', $detallePlan);
            	
        		// Redirecciona a la pagina principal
        		//redirect('?/ingresos/listar/'.$id_pago);
        		break;
		
		    case 2:
            	// Instancia el ingreso
    			$ingresoPlan = array(
    				'movimiento_id' => $IdImportacion,
    				'interes_pago' => 0,
    				'tipo' => 'Importacion',
    			);
    			// Guarda la informacion del ingreso general
    			$ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
    					
    			$nro_cuota=0;
    			for($nro2=0; $nro2<$nro_cuentas; $nro2++) {
    				$fecha_format=(is_date($fechas[$nro2])) ? date_encode($fechas[$nro2]): "0000-00-00";
    
    				$nro_cuota++;
    				
    				$detallePlan = array(
    					'nro_cuota' => $nro_cuota,
    					'pago_id' => $ingreso_id_plan,
    					'fecha' => $fecha_format,
    					'fecha_pago' => $fecha_format,
    					'empleado_id' => $_user['persona_id'],
    					'tipo_pago' => "",
    					'monto' => (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0,
    					'monto_programado' => (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0,
    					'estado'  => '0',
    					'nro_pago' => 0,
    				);
    
    				// Guarda la informacion
    				$db->insert('inv_pagos_detalles', $detallePlan);
    			}
        		// Redirecciona a la pagina principal
        		//redirect('?/ingresos/listar');
			
			case 3:
    			    $pago_id= trim($_POST['pago_id_'.$id_proveedor]);
            
                    if($pago_id!="") {
                        $pago_id1= trim($_POST['obs_'.$pago_id]);
                        $pago_id2= trim($_POST['nro_'.$pago_id]);
                        $pago_id3= trim($_POST['tipo_'.$pago_id]);
                        $pago_id4= trim($_POST['cuenta_'.$pago_id]);
                        $pago_id5= trim($_POST['monto_'.$pago_id]);
                        
                        $Condicion=[
                            'id_pago'=>$pago_id,
                        ];
                        $Datos=[
                			'movimiento_id'=>$IdImportacion,
                			'tipo'=>'Importacion',
                        ];
                        $db->where($Condicion)->update('inv_pagos',$Datos);
                    }
        			break;
		}
        /************************************************************************/





        echo json_encode(['success','Preparacion Realizada Exitosamente']);
        //redirect('?/importaciones/gastos');

    else:
        // require_once bad_request();
	    // die;
    endif;
else:
    // require_once not_found();
    // die;
endif;
?>