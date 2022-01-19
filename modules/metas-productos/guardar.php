<?php

    if(is_post()):
        if(isset($_POST['monto'])):
            $monto=trim($_POST['monto']);
            $fecha_ini=trim($_POST['fecha_ini']);
            $fecha_final=trim($_POST['fecha_final']);
            $producto=$_POST['producto'];
            $Datos=array(
                    'fecha_registro' => date('Y-m-d'),
                    'hora_registro' => date('H:i:s'),
                    'monto'=>$monto,
                    'fecha_inicio'=>$fecha_ini,
                    'fecha_fin'=>$fecha_final,
                    'producto_id'=>$producto,
                    'id_empleado_q_registro' => $_user['persona_id']
            );
            $id_meta=$db->insert('inv_meta_producto',$Datos);
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Se creo la nueva!',
                'message' => 'El registro se realizó correctamente.'
            );
            redirect('?/metas-productos/listar');
        else:
            echo 'error';
        endif;
    else:
        require_once not_found();
	    exit;
    endif;