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


            if ($datosFila[4][0]) {                
                                
                if ($ingreso_id) {
                    
                    foreach ($datosFila as $key => $value) {
    
                        if ($value[0] && $key > 0 ) {
                            $codigo          	= (isset($value[0]) && $value[0]) ? iconv("UTF-8", "UTF-8//IGNORE", $value[0]) : '--sincodigo--'; 
                            $codigo_barras      = (isset($value[1])) ? iconv("UTF-8", "UTF-8//IGNORE", $value[1]) : '--sin codigo--'; 
                            $nombre 				= (isset($value[2])) ? iconv("UTF-8", "UTF-8//IGNORE", $value[2]) : 0; 
                            $categoria_id = 0;
                            $unidad_id = 0;
    
                            $producto_id = $db->query("SELECT * FROM import_inv_productos WHERE codigo = '{$codigo}' OR  codigo_barras= '{$codigo_barras}'")->fetch_first();
                            $producto_id = (isset($producto_id['id_producto'])) ? $producto_id['id_producto'] : '';
    
                            if ($producto_id) {
                                $random = mt_rand(100, 1500);
                                $codigo = $codigo . '-' . $random;
                                $codigo_barras = $codigo_barras . '-' . $random;
                            }
    
                            if ($codigo && $codigo_barras && $nombre) {
    
                                if (!$categoria_id) {
                                    $categoria_id = $db->query("SELECT * FROM inv_categorias")->fetch_first()['id_categoria'];
                                }
                                if (!$unidad_id) {
                                    $unidad_id = $db->query("SELECT * FROM inv_unidades")->fetch_first()['id_unidad'];                                
                                }
                                
                                if (!$categoria_id || !$unidad_id) {
                                    $classProduct = new MyClassProductos();
                                    $atributos = $classProduct->verificarAtributosProducto();
                                    $categoria_id = $atributos['categoria_id'];
                                    $unidad_id = $atributos['unidad_id'];
                                }else {
                                    $categoria_id = $db->from('inv_categorias')->where(array('id_categoria' => $categoria_id))->fetch_first()['id_categoria'];
                                    $unidad_id = $db->from('inv_unidades')->where(array('id_unidad' => $unidad_id))->fetch_first()['id_unidad'];
                                }
    
                                // Instancia el producto
                                $producto = array(
                                    'codigo' => ($codigo) ? $codigo: '',
                                    'codigo_barras' => ($codigo_barras) ? $codigo_barras : '',
                                    'nombre' => ($nombre) ? $nombre : '',
                                    'nombre_factura' => (isset($nombre_factura)) ? $nombre_factura:(($nombre) ? $nombre : 'Sin nombre de fatura'),
                                    'precio_actual' => (isset($precio_actual)) ? $precio_actual : 0,
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
    
                            }else {
                                $arrayDetallesObs[$key] = $datosFila[$key];                            
                            }                                              
                        }
                    }
                }


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