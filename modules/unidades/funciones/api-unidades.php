<?php

class unidades{

    function __construct()
    {
        
    }

    function registroVariasUnidades($unidad = null){
        global $db;
        $tipo_clientes =  $db->from("inv_tipos_clientes")->fetch();

        if ($tipo_clientes) {

            foreach ($tipo_clientes as $key => $value) {
                $datos = array(
                    'unidad' => strtoupper($unidad . "|" . $value['tipo_cliente']), 
                    'sigla' => strtoupper(substr($unidad, 0) . substr($value['tipo_cliente'], 0)), 
                    'descripcion' => 'Unidad creada de forma automatica en relacion al tipo de clientes.'
                );
                
                if ($this->validar_existencia($datos)) {
                    $id_unidad = $db->insert('inv_unidades', $datos);
                }
            }	
        }
        return ($id_unidad) ? true: false;
    }

    function validar_existencia($array = array() ){
        global $db;

        $datos = array(
            'unidad' => $array['unidad']
        );
        //
        $result = $db->from ('inv_unidades')->where($datos)->fetch();

        return (!$result) ? true: false;
    }
}