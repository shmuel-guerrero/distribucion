<?php
    /**
     * SimplePHP - Simple Framework PHP
     * @package  SimplePHP
     * @author   Wilfredo Nina <wilnicho@hotmail.com>
     */
    if(is_post()):
        if(isset($_POST['id_meta_producto'])):
            $id_meta=trim($_POST['id_meta_producto']);
            $db->delete()->from('inv_meta_producto')->where('id_meta_producto',$id_meta)->limit(1)->execute();
            echo json_encode(true);
        else:
            echo 'error';
        endif;
    else:
        require_once not_found();
	    exit;
    endif;