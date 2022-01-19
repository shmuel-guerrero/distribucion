<?php

if(is_post()):
    if(isset($_POST['term'])):
        $Datos=$_POST['term'];
        $Datos=$db->query("SELECT * FROM (SELECT id_producto, codigo, nombre, m.fecha_registro, m.fecha_inicio, m.fecha_fin FROM inv_productos e 
        LEFT JOIN (SELECT MAX(fecha_registro)AS fecha_registro, producto_id, fecha_inicio, fecha_fin 
        FROM  inv_meta_producto GROUP BY producto_id) m 
        ON e.id_producto = m.producto_id																								
        WHERE  ( CURDATE() NOT BETWEEN  m.fecha_inicio AND  m.fecha_fin 
        OR m.fecha_registro IS NULL) ) E   
        WHERE  E.codigo LIKE '%{$Datos}%' OR E.nombre LIKE '%{$Datos}%'
        GROUP BY(E.nombre) LIMIT 20")->fetch();
        $json= array();
        if($Datos):
            foreach($Datos as $Nro=>$Dato):
                $json[]= array(
                    'id'=>$Dato['id_producto'],
                    'text'=>"{$Dato['nombre']} - {$Dato['codigo']}");
            endforeach;
        endif;
        echo json_encode($json);
    else:
        echo 'error';
    endif;
else:
    require_once not_found();
    exit;
endif;