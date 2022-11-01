<?php
    if(is_post()):
        if(isset($_POST['id_importacion']) && isset($_POST['id_proveedor']) && isset($_POST['descripcion'])):
            
            $id_importacion  = trim($_POST['id_importacion'])?$_POST['id_importacion']:0;
            $id_almacen      = trim($_POST['id_almacen']);
            $id_proveedor    = trim($_POST['id_proveedor']);
            $fecha           = trim($_POST['fechai']);
            $descripcion     = trim($_POST['descripcion']);
            $total           = trim($_POST['total']);


            $fecha=date('Y-m-d H:i:s');
            if(isset($_POST['fechai'])):
                $fecha=trim($_POST['fechai']);
            endif;

            $fechav     = (isset($_POST['fechav'])) ? $_POST['fechav'] : array();
            $lote       = (isset($_POST['lote'])) ? $_POST['lote'] : array();
            $cantidad   = (isset($_POST['cantidad'])) ? $_POST['cantidad'] : array();
            $costo      = (isset($_POST['costo'])) ? $_POST['costo'] : array();
            $importe    = (isset($_POST['importe'])) ? $_POST['importe'] : array();
            $producto_id= (isset($_POST['producto_id'])) ? $_POST['producto_id'] : array();
            $unidad_id  = (isset($_POST['unidad_id'])) ? $_POST['unidad_id'] : array();

            $IdUsuario=$_SESSION[user]['id_user'];
            $IdEmpleado=$db->query("SELECT persona_id FROM sys_users WHERE id_user='{$IdUsuario}'")->fetch_first()['persona_id'];

            $nroCorrelativo=$db->query("SELECT nro_correlativo FROM inv_importacion WHERE id_importacion='$id_importacion'")->fetch_first()['nro_correlativo'];
                
            $Datos=[
                    'fecha_inicio'=>$fecha,
                    'fecha_final'=>$fecha,
                    'total'=>$total,
                    'descripcion'=>$descripcion,
                    'nro_registros'=>count($fechav),
                    'id_proveedor'=>$id_proveedor,
                    'almacen_id'=>$id_almacen,
                    'empleado_id'=>$IdEmpleado,
                    'nro_correlativo'=>$nroCorrelativo,
                ];
            $IdImportacion=$db->insert('inv_importacion',$Datos);

            for($i=0;$i<count($fechav);++$i):
                
                $Datos=[
                        'precio_ingreso'=>$costo[$i],
                        'precio_salida'=>$costo[$i],
                        'cantidad'=>$cantidad[$i],
                        'fechav'=>$fechav[$i],
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
                $Consulta=$db->query("SELECT IFNULL(SUM(total),0)AS total,IFNULL(SUM(total_gasto),0)AS total_gasto FROM inv_importacion_gasto WHERE importacion_id='{$IdImportacion}'")->fetch_first();
                $Condicion=[
                        'id_importacion'=>$IdImportacion,
                    ];
                $Datos=[
                        'total_gastos'=>$Consulta['total'],
                        'total_costo'=>$Consulta['total_gasto'],
                    ];
                $db->where($Condicion)->update('inv_importacion',$Datos);
            endif;

            if($id_importacion!=0):
                $db->delete()->from('inv_importacion')->where('id_importacion',$id_importacion)->limit(1)->execute();
                $db->delete()->from('tmp_ingreso_detalle')->where('importacion_id',$id_importacion)->execute();
            endif;

            
            echo json_encode(['success','Preparacion Realizada Exitosamente']);
            // redirect('?/importaciones/gastos');

        else:
            require_once bad_request();
		    die;
        endif;
    else:
        require_once not_found();
	    die;
    endif;