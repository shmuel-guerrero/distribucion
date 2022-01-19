<?php
    $IdCliente = isset($params[0])?$params[0]:false;
    $nuevoEstado = isset($params[1])?$params[1]:false;
    
    if($IdCliente && $nuevoEstado):         
        $Datos=array(
                'cuentas_por_cobrar'=>$nuevoEstado,
        );
         
        $Condicion=array(
                'id_cliente'=>$IdCliente
        );
         
        $db->where($Condicion)->update('inv_clientes',$Datos);

        echo json_encode(array(
                'ok'=>true,
                'message'=>array(
                    'title'=>'Exitoso',
                    'message'=>'Cliente Actualizado Exitosamente',
                    'image'=>'success',
                )
        ));
    else:
        require_once not_found();
	    die;
    endif;