<?php
    $IdImportacion=isset($params[0])?$params[0]:false;
    if($IdImportacion):
        $Consulta=$db->query("SELECT p.id_producto,p.codigo,p.nombre_factura as nombre,id.fechav,id.lote,u.unidad,id.precio_ingreso,id.precio_salida,id.cantidad
                            FROM tmp_ingreso_detalle AS id
                            LEFT JOIN inv_productos AS p ON id.producto_id=p.id_producto
                            LEFT JOIN inv_unidades AS u ON id.unidad_id=u.id_unidad
                            WHERE id.importacion_id='{$IdImportacion}'")->fetch();
                            
        for($i=0;$i<count($Consulta);++$i):
            $Consulta[$i]['fechav']=date_decode($Consulta[$i]['fechav'], $_institution['formato'])." ".$fecha_iniciov[1];
        endfor;
        
        echo json_encode($Consulta);
    else:
        require_once not_found();
	    die;
    endif;