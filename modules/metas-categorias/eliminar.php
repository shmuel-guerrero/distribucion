<?php
    /**
     * SimplePHP - Simple Framework PHP
     * @package  SimplePHP
     * @author   Wilfredo Nina <wilnicho@hotmail.com>
     */
    if(is_post()):
        if(isset($_POST['id_meta'])):
            $id_meta=trim($_POST['id_meta']);
            $db->delete()->from('inv_meta_categoria')->where('id_meta_categoria',$id_meta)->limit(1)->execute();
            echo json_encode(true);
        else:
            echo 'error';
        endif;
    else:
        require_once not_found();
	    exit;
    endif;