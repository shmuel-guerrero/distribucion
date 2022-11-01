<?php

    // echo json_encode($_POST); die();
    if(is_post()):
        if(isset($_POST['id_proveedor']) && isset($_POST['codigo'])):
            $id_importacion= trim($_POST['id_importacion']);
            $nombre        = trim($_POST['nombre']);
            $codigo        = trim($_POST['codigo']);
            
            $pago          = 0;
            
            $total         = trim($_POST['total']);
            $totalGasto    = trim($_POST['totalGasto']);
            $id_gasto      = isset($_POST['id_gasto'])     ?$_POST['id_gasto']     :[];
            $gasto         = isset($_POST['gasto'])        ?$_POST['gasto']        :[];
            $factura       = isset($_POST['factura'])      ?$_POST['factura']      :[];
            $costo_anadido = isset($_POST['costo_anadido'])?$_POST['costo_anadido']:[];
            $costo         = isset($_POST['costo'])        ?$_POST['costo']        :[];
            $id_proveedor  = isset($_POST['id_proveedor']) ?$_POST['id_proveedor'] :[];
            
            $tipo_pago  = trim($_POST['tipo_pago']);
            $nro_pago   = isset($_POST['nro_doc']) ? $_POST['nro_doc'] :0;

            /****************************************/

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
    		
            $observacion=$_POST['observacion'];
        	$banco=$_POST['banco_id'];
        	$nro_doc=$_POST['nro_doc'];
        	$tipo_pago=$_POST['tipo_pago'];

            /****************************************/

            $id_usuario=$_SESSION[user]['id_user'];
            $id_empleado=$db->query("SELECT persona_id 
                                     FROM sys_users 
                                     WHERE id_user='{$id_usuario}'
                                    ")->fetch_first()['persona_id'];
            $estado='activo';
            if($tipoP=='Contado'):
                $pago=$total;
                $estado='inactivo';
            endif;

            if(isset($_POST['id_importacion_gasto'])):
                $id_importacion_gasto=trim($_POST['id_importacion_gasto']);

                //ELIMINA inv_importacion_gasto_detalle
                $Condicion=[
                        'importacion_gasto_id'=>$id_importacion_gasto,
                    ];
                $db->delete()->from('inv_importacion_gasto_detalle')->where($Condicion)->execute();
                //OBTIENE
                $Importacion=$db->query("SELECT costo_anadido,costo
                                         FROM inv_importacion_gasto_detalle
                                         LEFT JOIN inv_importacion_gasto ON inv_importacion_gasto.id_importacion_gasto=inv_importacion_gasto_detalle.importacion_gasto_id
                                         WHERE importacion_id='{$id_importacion}' AND importacion_gasto_id!='{$id_importacion_gasto}'
                                        ")->fetch();
                //
                $Costo1=0;
                $Costo2=0;
                foreach($Importacion as $Fila=>$Dato):
                    $Costo1=$Costo1+$Dato['costo'];
                    $Costo2=$Costo2+($Dato['costo']*($Dato['costo_anadido']/100));
                endforeach;
                $Costo1=round($Costo1,2);
                $Costo2=round($Costo2,2);
                $Condicion=[
                        'id_importacion'=>$id_importacion,
                    ];
                $Datos=[
                        'total_gastos'=>$total+$Costo1,
                        'total_costo'=>$totalGasto+$Costo2,
                    ];
                $db->where($Condicion)->update('inv_importacion',$Datos);

                $Datos=[
                        'nombre'        =>$nombre,
                        'codigo'        =>$codigo,
                        'fecha'         =>date('Y-m-d H:i:s'),
                        'total'         =>$total,
                        'total_gasto'   =>$totalGasto,
                        'tipo_pago'     =>$tipoP,
                        'pago'          =>$pago,
                        'estado'        =>$estado,
                        'importacion_id'=>$id_importacion,
                        'empleado_id'   =>$id_empleado,
                        'proveedor_id'  =>$id_proveedor
                    ];
                $Condicion=[
                        'id_importacion_gasto'=>$id_importacion_gasto,
                    ];
                $db->where($Condicion)->update('inv_importacion_gasto',$Datos);
                
                for($i=0;$i<count($id_gasto);++$i):
                    $Datos=[
                            'gasto'=>$gasto[$i],
                            'factura'=>$factura[$i],
                            'costo_anadido'=>$costo_anadido[$i],
                            'costo'=>$costo[$i],
                            'gastos_id'=>$id_gasto[$i],
                            'importacion_gasto_id'=>$id_importacion_gasto,
                        ];
                    $db->insert('inv_importacion_gasto_detalle',$Datos);
                endfor;
            else:
                $Importacion=$db->query("SELECT total_gastos,total_costo FROM inv_importacion WHERE id_importacion='{$id_importacion}'")->fetch_first();
                $Condicion=[
                        'id_importacion'=>$id_importacion,
                    ];
                $Datos=[
                        'total_gastos'=>$Importacion['total_gastos']+$total,
                        'total_costo'=>$Importacion['total_costo']+$totalGasto,
                    ];
                $db->where($Condicion)->update('inv_importacion',$Datos);

                $Datos=[
                        'nombre'        =>$nombre,
                        'codigo'        =>$codigo,
                        'fecha'         =>date('Y-m-d H:i:s'),
                        'total'         =>$total,
                        'total_gasto'   =>$totalGasto,
                        'tipo_pago'     =>$tipoP,
                        'pago'          =>$pago,
                        'estado'        =>$estado,
                        'importacion_id'=>$id_importacion,
                        'empleado_id'   =>$id_empleado,
                        'proveedor_id'  =>$id_proveedor
                    ];
                $IdImportacioGasto=$db->insert('inv_importacion_gasto',$Datos);
                // if($pago!=0):
                    $Datos=[
                            'fecha'=>date('Y-m-d'),
                            'monto'=>$pago,
                            'forma_pago'=> $tipo_pago, // ($tipoP == 'Contado')
                            'comprobante'=> $nro_pago, // ($tipoP == 'Contado')
                            'importacion_gasto_id'=>$IdImportacioGasto,
                            'empleado_id'=>$id_empleado,
                        ];
                    $db->insert('inv_importacion_pagos',$Datos);
                // endif;

                for($i=0;$i<count($id_gasto);++$i):
                    $Datos=[
                            'gasto'=>$gasto[$i],
                            'factura'=>$factura[$i],
                            'costo_anadido'=>$costo_anadido[$i],
                            'costo'=>$costo[$i],
                            'gastos_id'=>$id_gasto[$i],
                            'importacion_gasto_id'=>$IdImportacioGasto,
                        ];
                    $db->insert('inv_importacion_gasto_detalle',$Datos);
                endfor;
            endif;
            if($db->affected_rows):
                $_SESSION[temporary] = array(
                    'alert' => 'success',
                    'title' => 'Actualización satisfactoria!',
                    'message' => 'El registro fue actualizado correctamente.'
                );
            endif;
        
        
        
        
        
        
        
            /************************************************************************/
            // ARMAMOS EN PLAN DE PAGOS
    		switch($plan){
    		    case 1:
            		$detallePlan = array(
            			'movimiento_id'=>$IdImportacioGasto,
            			'interes_pago'=>0,
            			'tipo'=>'Gastos',
            		);
            		// Guarda la informacion
                	$id_pago=$db->insert('inv_pagos', $detallePlan);
                
                	$detallePlan = array(
                			'pago_id'=>$id_pago,
                			'fecha' => date('Y-m-d'),
                			'monto' => $pago,			
                			'monto_programado' => $pago,			
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
        				'movimiento_id' => $IdImportacioGasto,
        				'interes_pago' => 0,
        				'tipo' => 'Gastos',
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
    			break;
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
                			'movimiento_id'=>$IdImportacioGasto,
                			'tipo'=>'Gastos',
                        ];
                        $db->where($Condicion)->update('inv_pagos',$Datos);
                    }
        			break;
    		}
            /************************************************************************/

            redirect('?/importaciones/gastos');
        else:
            require_once bad_request();
		    die;
        endif;
    else:
        require_once not_found();
	    die;
    endif;