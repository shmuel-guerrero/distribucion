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

    function crearCategoria($categoria = 'SIN CATEGORIA'){
        global $db;
        $categoria = trim($categoria);
        
    
        $categoriaVerificada = $db->query("SELECT * FROM inv_categorias c WHERE c.categoria='{$categoria}'")->fetch_first();
        
    
        if (!$categoriaVerificada) {
            $datos = array(
                'categoria' => ($categoria) ? $categoria: 'Nueva Categoria',
                'descripcion' => 'Sin descripcion'
            );    
            $id_categoria = $db->insert("inv_categorias", $datos);            
        }else {
            $id_categoria = (isset($categoriaVerificada['id_categoria'])) ? $categoriaVerificada['id_categoria'] : 0;
        }
        
        return ($id_categoria) ? $id_categoria : 0;
    }
    
    
    
    
    
    function crearUnidad($unidad = 'SIN UNIDAD'){
        global $db;
    
        $unidad = trim($unidad);
    
        $unidadVerificada = $db->query("SELECT * FROM inv_unidades u WHERE u.unidad='{$unidad}'")->fetch_first();
    
        if (!$unidadVerificada) {
            $datos = array(
                'unidad' => ($unidad) ? $unidad: 'Nueva Unidad',
                'sigla' => ($unidad) ? $unidad: 'SS',
                'descripcion' => 'Sin descripcion'
            );    
            $id_unidad = $db->insert("inv_unidades", $datos);            
        }else {
            $id_unidad = (isset($unidadVerificada['id_unidad'])) ? $unidadVerificada['id_unidad'] : 0;
        }
        return ($id_unidad) ? $id_unidad : 0;
    }


    function verificarProducto($producto = array()){
        global $db;

        if (isset($producto['codigo']) && isset($producto['codigo_barras']) && isset($producto['nombre'])) {            

            $codigo = $producto['codigo'];
            $codigo_barras = $producto['codigo_barras'];
            $nombre = $producto['nombre'];
        
            $product = $db->query("SELECT * FROM inv_productos WHERE codigo = '{$codigo}' OR codigo_barras = '{$codigo_barras}' OR nombre = '{$nombre}'")->fetch_first();

            $id_producto = (isset($product['id_producto'])) ? false : true;
        }else {
            $id_producto = false;
        }
        return $id_producto;
    }
}


