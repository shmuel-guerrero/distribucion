<?php


if (is_post()) {
    
    if (isset($_POST['idIngresoImport'])) {

       //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {
 
			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction(); 

            require libraries ."/mis_clases/class_productos.php";
            $classProduct = new MyClassProductos();

            $id_ingreso_import = $_POST['idIngresoImport'];

            $datos_import = $db->from('imports_generals')->where(array('id_general' => $id_ingreso_import, 'estado_importacion' => 'Import'))->fetch_first();

            $detalles = $db->from('import_inv_productos')->where(array('general_id' => $id_ingreso_import))->fetch();

            $db->where(array('id_general' => $id_ingreso_import))->update('imports_generals', array('estado_importacion' => 'Confirmado') );
            //echo "Affected Rows : " . $db->affected_rows;

            $id_ingreso = $id_ingreso_import;
            
            if ($detalles) {
                
                foreach ($detalles as $value) {
                    if ($classProduct->verificarProducto($value)) {                        
                        $value['fecha_registro'] = date('Y-m-d');
                        $value['hora_registro'] = date('H:i:s');
                        unset($value['general_id'], $value['fecha_vencimiento'], $value['fecha_limite']);
                        $db->insert('inv_productos', $value);
                        //var_dump($classProduct->verificarProducto($value));

                        $asignacion = $db->from('import_inv_asignaciones')
                                        ->where(array('producto_id' => $value['id_producto']))->fetch();

                        if ($asignacion) {
                            foreach ($asignacion as $key => $value) {                                
                                unset($value['id_asignacionimport']);
                                $db->insert('inv_asignaciones', $value);
                            }
                        }
                    
                    }
                }


            }

            //se cierra transaccion
            $db->commit();

            echo json_encode($id_ingreso);

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

    }else {
        
    }
}else {
    
}

