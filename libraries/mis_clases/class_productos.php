<?php

class MyClassProductos
{
    function __construct()
    {
        
    }

    function verificarAtributosProducto(){
        global $db;
        $id_categoria = $db->query("SELECT * FROM inv_categorias")->fetch_first()['id_categoria'];
        if (!$id_categoria) {
            $datos = array(
                'categoria' => 'PRODUCTOS',
                'descripcion' => '' 
            );
            $categoria_id = $db->insert('inv_categorias', $datos);
        }

        $id_unidad = $db->query("SELECT * FROM inv_unidades WHERE unidad = 'UNIDAD'")->fetch_first()['id_unidad'];

        if (!$id_unidad) {
            $datos = array(
                'unidad' => 'UNIDAD',
                'sigla' => 'U',
                'descripcion' => 'Unidad creada para  inicio de actividades',
                'tamanio' => '1' 
            );
            $unidad_id = $db->insert('inv_unidades', $datos);
        }

        $respuesta = array(
            'categoria_id' => $categoria_id, 
            'unidad_id' => $unidad_id 
        );

        return $respuesta;

    }

}


