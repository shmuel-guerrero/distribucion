<?php
    if(is_post()):
        if(isset($_POST['id_importacion_gasto'])&&isset($_POST['pago'])):
            $id_importacion=trim($_POST['id_importacion']);
            $id_importacion_gasto=(isset($_POST['id_importacion_gasto']))?$_POST['id_importacion_gasto']:[];
            $pago=(isset($_POST['pago']))?$_POST['pago']:[];
            $tipo_pago=(isset($_POST['tipo_pago']))?$_POST['tipo_pago']:[];
            $nro_pago=(isset($_POST['nro_pago']))?$_POST['nro_pago']:[];
            $pago=(isset($_POST['pago']))?$_POST['pago']:[];
            $IdUsuario=$_SESSION[user]['id_user'];
            $IdEmpleado=$db->query("SELECT persona_id FROM sys_users WHERE id_user='{$IdUsuario}'")->fetch_first()['persona_id'];
            for($i=0;$i<count($pago);++$i):
                if($pago[$i]!=0):
                    $pago_anterior=$db->query("SELECT pago FROM inv_importacion_gasto WHERE id_importacion_gasto='$id_importacion_gasto[$i]' LIMIT 1")->fetch_first()['pago'];
                    $Datos=[
                            'pago'=>$pago_anterior+$pago[$i],
                        ];
                    $Condicion=[
                            'id_importacion_gasto'=>$id_importacion_gasto[$i],
                        ];
                    $db->where($Condicion)->update('inv_importacion_gasto',$Datos);
                    $Datos=[
                            'fecha'=>date('Y-m-d H:i:s'),
                            'monto'=>$pago[$i],
                            'forma_pago'=>$tipo_pago[$i],
                            'comprobante'=>$nro_pago[$i],
                            'importacion_gasto_id'=>$id_importacion_gasto[$i],
                            'empleado_id'=>$IdEmpleado,
                        ];
                    $db->insert('inv_importacion_pagos',$Datos);
                endif;
            endfor;
            if($db->affected_rows):
                $_SESSION[temporary] = array(
                    'alert' => 'success',
                    'title' => 'Registro satisfactorio!',
                    'message' => 'El registro fue realizado correctamente.'
                );
            endif;
            redirect('?/importaciones/pagos_pendientes/'.$id_importacion);
        else:
            require_once bad_request();
		    die;
        endif;
    else:
        require_once not_found();
	    die;
    endif;