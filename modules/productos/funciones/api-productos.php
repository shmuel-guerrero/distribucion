<?php

class productos{


    function __construct()
    {
        
    }

    function registroTipoPrecio($id_producto = 0, $tipo_precio = 0){
        global $db;
        if ($tipo_precio > 0) {
            $datos = array(
                'asignacion_id' => 0, 
                'producto_id' => $id_producto, 
                'cliente_tipo_id' => $tipo_precio, 
                'nivel' => 'Principal'
            );
            
            $id_tipo_precio = $db->insert('inv_asignacion_tipo_precio', $datos);
        }else {
            
            $nro_registros =  $db->query("SELECT GROUP_CONCAT(tc.id_tipo_cliente)AS id_tipo_clientes, COUNT(*)AS nro_clientes FROM inv_tipos_clientes tc")->fetch_first();
            $nro_regist = ($nro_registros['nro_clientes'] > 0) ? $nro_registros['nro_clientes']:0;
    
            $tipo_clientes =   explode(',', $nro_registros['id_tipo_clientes']);
    
            if ($nro_regist) {
                for ($i=0; $i < $nro_regist; $i++) { 
                    
                    $datos = array(
                        'asignacion_id' => 0, 
                        'producto_id' => $id_producto, 
                        'cliente_tipo_id' => $tipo_clientes[$i], 
                        'nivel' => ($i == 0) ? 'Principal': 'Secundario'
                    );
                    
                    $id_tipo_precio = $db->insert('inv_asignacion_tipo_precio', $datos);
                }		
            }
        }
        return ($id_tipo_precio) ? true: false;
    }
}