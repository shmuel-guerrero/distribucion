<?php

require libraries . "/PhpSpreadsheet/vendor/autoload.php";

class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter{

    public function readCell($columnAddress, $row, $worksheetName = '')
    {
        if ($row >=1) {
            return true;
        }
        return false;
    }
}



use PhpOffice\PhpSpreadsheet\IOFactory;

if (!empty($_FILES['archivo'])) {

    //$archivo = __DIR__ ."/documents/". $_FILES["archivo"]["name"];
    $archivo = files . "/documents/xls/". $_FILES["archivo"]["name"];
    require libraries ."/mis_clases/class_productos.php";

    
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo)) { 


        $inputFileType 	= IOFactory::identify($archivo);
        $objReader 		= IOFactory::createReader($inputFileType);

        // se define lectura de los datos de excel
        $objReader->setReadFilter(new MyReadFilter);

        $spreadsheet = $objReader->load($archivo);

        //se obtiene en la variable todos los datos del archivo excel
        $datosFila = $spreadsheet->getActiveSheet()->toArray();

        $Obs = array();
        $arrayObs = array();
        $arrayDetallesObs = array();


        $datoGenerals = array(
            'fecha_import' => date('Y-m-d'), 
            'hora_import' => date('H:i:s'), 
            'tipo_tabla' => 'Productos', 
            'empleado_id' => $_user['persona_id'], 
            'estado_importacion' => 'Import' 
        );
        
        $ingreso_id = $db->insert('imports_generals', $datoGenerals);


        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {
 
			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

            $classProduct = new MyClassProductos();

            if ($datosFila[4][0]) {                
                                
                if ($ingreso_id) {
                    
                    foreach ($datosFila as $key => $value) {
    
                        if ($value[0] && $key > 0 ) {
                            $codigo          	= (isset($value[0]) && $value[0]) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9 \_\-\/\:])', '', $value[0])) : '--sincodigo--'; 
                            $codigo_barras      = (isset($value[1])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9 \_\-\/\:])', '', $value[1])) : 'SCOD'; 
                            $nombre 			= (isset($value[2])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9 \_\-\/\:])', '', $value[2])) : 0; 
                            $nombre_factura 	= (isset($value[3])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9 \_\-\/\:])', '', $value[3])) : 0; 
                            $descripcion 		= (isset($value[4])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9 \_\-\/\:])', '', $value[4])) : 0; 
                            $categoria 			= (isset($value[5])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9 \_\-\/\:])', '', $value[5])) : 0; 
                            $unidad 			= (isset($value[6])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9])', '', $value[6])) : 0; 
                            $precio_base 		= (isset($value[7])) ? iconv("UTF-8", "UTF-8//IGNORE", floatval($value[7])) : 0; 
                            
                            $unidad2 			= (isset($value[8])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9])', '', $value[8])) : ''; 
                            $tamanio2 			= (isset($value[9])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9])', '', $value[9])) : 0; 
                            $precio_2 			= (isset($value[10])) ? iconv("UTF-8", "UTF-8//IGNORE", floatval($value[10])) : 0; 

                            $categoria_id = 0;
                            $unidad_id = 0;

                            $categoria_id =  $classProduct->crearCategoria($categoria);
                            $unidad_id = $classProduct->crearUnidad($unidad);

    
                            $producto_id = $db->query("SELECT * FROM import_inv_productos WHERE codigo = '{$codigo}' OR  codigo_barras= '{$codigo_barras}'")->fetch_first();
                            $producto_id = (isset($producto_id['id_producto'])) ? $producto_id['id_producto'] : '';
    
                            if ($producto_id) {
                                $random = mt_rand(100, 1500);
                                $codigo = $codigo . '-' . $random;
                                $codigo_barras = $codigo_barras . '-' . $random;
                            }
    
                            if (!$codigo && !$codigo_barras && !$nombre ) {                            
                                // Envia respuesta
                                echo json_encode(array('estado' => 'error', 'responce' => "Datos incompletos; favor proporcinar todos los datos necesarios para la importacion."));                                                         
                                return;
                            }  

    
                                if (!$categoria_id) {
                                    $categoria_id = $db->query("SELECT * FROM inv_categorias")->fetch_first()['id_categoria'];
                                }
                                if (!$unidad_id) {
                                    $unidad_id = $db->query("SELECT * FROM inv_unidades")->fetch_first()['id_unidad'];                                
                                }
                                
                                if (!$categoria_id || !$unidad_id) {
                                    
                                    $atributos = $classProduct->verificarAtributosProducto();
                                    $categoria_id = $atributos['categoria_id'];
                                    $unidad_id = $atributos['unidad_id'];
                                }else {
                                    $categoria_id = $db->from('inv_categorias')->where(array('id_categoria' => $categoria_id))->fetch_first()['id_categoria'];
                                    $unidad_id = $db->from('inv_unidades')->where(array('id_unidad' => $unidad_id))->fetch_first()['id_unidad'];
                                }
                                
                            if ($codigo && $codigo_barras && $nombre ) { 
                                // Instancia el producto
                                $producto = array(
                                    'codigo' => ($codigo) ? $codigo: '',
                                    'codigo_barras' => ($codigo_barras) ? $codigo_barras : '',
                                    'nombre' => ($nombre) ? $nombre : '',
                                    'nombre_factura' => (isset($nombre_factura)) ? $nombre_factura:(($nombre) ? $nombre : 'Sin nombre de fatura'),
                                    'fecha_registro' => date('Y-m-d'),
                                    'hora_registro' => date('H:i:s'),
                                    'precio_actual' => (isset($precio_base)) ? $precio_base : 0,
                                    'descripcion' => ($descripcion) ? $descripcion : '',
                                    'precio_sugerido' => (isset($precio_sugerido)) ? $precio_sugerido : 0,
                                    'cantidad_minima' => (isset($cantidad_minima)) ? $cantidad_minima : 10,
                                    'ubicacion' => (isset($ubicacion)) ? $ubicacion : '',
                                    'descripcion' => (isset($descripcion)) ? $descripcion : '',
                                    'unidad_id' => ($unidad_id) ? $unidad_id : 0,
                                    'categoria_id' => ($categoria_id) ? $categoria_id : 0,
                                    'marca_id' => (isset($marca_id)) ? $marca_id : 0,
                                    'general_id' => $ingreso_id
                                );
    
                                // Guarda la informacion
                                $id_producto = $db->insert('import_inv_productos', $producto);

                                //Registro de 2da unidad de venta
                                if ($id_producto && $unidad2 && $tamanio2 && $precio_2) {
                                    
                                    $unidad_id2 = $classProduct->crearUnidad($unidad2);

                                    $dats2 = array(
                                        'producto_id' => ($id_producto > 0) ? $id_producto : 0, 
                                        'unidad_id' => ($unidad_id2 > 0)? $unidad_id2 : 0, 
                                        'cantidad_unidad' => ($tamanio2 > 0) ? $tamanio2 : 0, 
                                        'otro_precio' => ($precio_2 > 0) ? round($precio_2, 1) : 0, 
                                        'visible' => 's'
                                    );
                                    $id_asig = $db->insert('import_inv_asignaciones', $dats2);

                                    registroUnidades ($id_producto, $classProduct, $value);
                                }
                            }
                                                                        
                        }
                    }
                }

                //se cierra transaccion
                $db->commit();

                // Envia respuesta
                echo json_encode(array('estado' => 'success', 'responce' => $ingreso_id));
                exit;
            }


        } catch (Exception $e) {
            $status = false;
            $error = $e->getMessage();
        
            // Instancia la variable de notificacion
            $_SESSION[temporary] = array(
                'alert' => 'danger',
                'title' => 'Problemas en el proceso de interacción con la base de datos.',
                'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'
            );
            // Redirecciona a la pagina principal
            //redirect('?/notas/mostrar');
            //Se devuelve el error en mensaje json
            echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));
        
            //se cierra transaccion
            $db->rollback();
        }
        
    }else{

        // Envia respuesta
        echo json_encode(array('estado' => 'error', 'responce' => 'Error al subir archivo'));
    }
}else {
    // Envia respuesta
    echo json_encode(array('estado' => 'error', 'responce' => 'Error, archivo observado.'));
}





function registroUnidades ($id_producto = 0, $classProduct, $value = array()){
    $i = 11;
    global $db;

    for ($j=0; $j < 8; $j++) { 
        $unidad2 				= (isset($value[$i])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9])', '', $value[$i])) : ''; 
        $tamanio2 				= (isset($value[$i+1])) ? iconv("UTF-8", "UTF-8//IGNORE", preg_replace('([^A-Za-z0-9])', '', $value[$i+1])) : 0; 
        $precio_2 				= (isset($value[$i+2])) ? iconv("UTF-8", "UTF-8//IGNORE", floatval($value[$i+2])) : 0; 
    
        //Registro de 2da unidad de venta
        if ($id_producto && $unidad2 && $tamanio2 && $precio_2) {
                                        
            $unidad_id2 = $classProduct->crearUnidad($unidad2);
    
            $dats2 = array(
                'producto_id' => ($id_producto > 0) ? $id_producto : 0, 
                'unidad_id' => ($unidad_id2 > 0)? $unidad_id2 : 0, 
                'cantidad_unidad' => ($tamanio2 > 0) ? $tamanio2 : 0, 
                'otro_precio' => ($precio_2 > 0) ? round($precio_2, 1) : 0, 
                'visible' => 's'
            );
            $id_asig = $db->insert('import_inv_asignaciones', $dats2);
        }
        $i = $i + 3;
    }

}
