<?php
    if(is_post()):
        if(isset($params)):
            $IdOrdenSalida=$_POST['IdOrdenSalida'];
            $db->delete()->from('inv_ordenes_salidas')->where('id_orden',$IdOrdenSalida)->limit(1)->execute();
            $db->delete()->from('inv_ordenes_detalles')->where('orden_salida_id',$IdOrdenSalida)->execute();
            //set_notification('success', 'Eliminacion Exitosa!', 'Se elimino la salida.');
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Eliminacion satisfactoria!',
                'message' => 'El registro fue eliminado correctamente.'
            );
            // Redirecciona la pagina
            redirect('?/autoventas/listar_distribucion');
            return;
        endif;
        require_once bad_request();
		exit;
    else:
        require_once not_found();
	    exit;
    endif;