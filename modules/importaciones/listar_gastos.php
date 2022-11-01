<?php
    $IdImportacion=isset($params[0])?$params[0]:false;
    if($IdImportacion):
        $Consulta=$db->query("SELECT ig.id_importacion_gasto,ig.nombre,ig.codigo,ig.fecha,e.nombres,e.paterno,e.materno
                            FROM inv_importacion_gasto AS ig
                            LEFT JOIN sys_empleados AS e ON ig.empleado_id=e.id_empleado
                            WHERE ig.importacion_id='{$IdImportacion}'")->fetch();
        for($i=0;$i<count($Consulta);++$i):
            $SubConsulta=$db->query("SELECT gasto,factura,costo_anadido,costo
                                    FROM inv_importacion_gasto_detalle
                                    WHERE importacion_gasto_id='{$Consulta[$i]['id_importacion_gasto']}'")->fetch();
            
            $Consulta[$i]['detalles']=$SubConsulta;
        
            for($j=0;$j<count($SubConsulta);++$j){
                //$Consulta[$i]['detalles']['costo']=number_format($Consulta[$i]['detalles']['costo'],2,',','.');
                $Consulta[$i]['detalles'][$j]['costo']=number_format($Consulta[$i]['detalles'][$j]['costo'],2,',','.');
            }
            
            $fecha_iniciov=explode(" ",$Consulta[$i]['fecha']);
            $Consulta[$i]['fecha']=date_decode($fecha_iniciov[0], $_institution['formato'])." ".$fecha_iniciov[1];
        
        endfor;
        echo json_encode($Consulta);
    else:
        require_once not_found();
	    die;
    endif;