<?php
    require(config.'/database.php');
    if(sizeof($params)>0):
        $Token=escape(trim($params[0]));
        $Consulta=$db->query("SELECT id_cliente AS id,cliente,imagen FROM inv_clientes WHERE token='{$Token}' LIMIT 1")->fetch_first();
        if($Consulta):
            $Condicion=array(
                    'id_cliente'=>$Consulta['id'],
            );
            $Datos=array(
                    'token'=>''
            );
            $db->where($Condicion)->update('inv_clientes',$Datos);
            //Redireccionar a la ruta del portal de pedidos
            redirect(url2.'/login');
        endif;
    else:
        redirect(url2);
    endif;