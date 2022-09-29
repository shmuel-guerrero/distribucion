<?php



if (is_post()) {
    
    if (isset($_POST['id_iimport']) && isset($_POST['tipo_importacion'])) {

        
        $tipo_importacion = (isset($_POST['tipo_importacion'])) ? $_POST['tipo_importacion']: '';

                //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {
 
			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

    
            switch ($tipo_importacion) {
                case 'ingreso':
                    $id_ingresoi = $_POST['id_iimport'];
        
                    $compra = $db->query("SELECT ii.*, a.almacen, GROUP_CONCAT(e.nombres, ' ', e.paterno)as empleado FROM inv_ingresos_import ii 
                                            LEFT JOIN inv_almacenes a ON ii.almacen_id = a.id_almacen
                                            LEFT JOIN sys_empleados e ON ii.empleado_id = e.id_empleado
                                            WHERE ii.id_ingreso='{$id_ingresoi}' AND estado_import = 'Import'")->fetch_first();
        
        
                    $detalles = $db->query("SELECT di.*, p.codigo, p.nombre, nombre_factura FROM inv_ingresos_detalles_import di 
                                            LEFT JOIN inv_productos p ON di.producto_id = p.id_producto
                                            WHERE di.ingreso_id = '{$id_ingresoi}'")->fetch();
        
                    $respuesta = array(
                        'compra' => $compra, 
                        'detalles' => $detalles
                    );
        
        
                    echo json_encode($respuesta);
                    
        
                    break;
                case 'productos':
                    $id_ingresoi = $_POST['id_iimport'];
                    
                    $id_general = $db->query("SELECT * FROM imports_generals WHERE id_general = '{$id_ingresoi}'")->fetch_first();
                    $id_general = (isset($id_general['id_general'])) ? $id_general['id_general'] : '';
                    

                    if ($id_general) {

                        $imports_general = $db->query("SELECT g.*, e.nombres FROM imports_generals g 
                                            LEFT JOIN sys_empleados e ON e.id_empleado = g.empleado_id WHERE id_general='{$id_ingresoi}'")->fetch_first();
                                            
                        $productos = $db->query("SELECT p.*, c.categoria, u.unidad FROM import_inv_productos p 
                                                LEFT JOIN inv_categorias c ON p.categoria_id = c.id_categoria 
                                                LEFT JOIN inv_unidades u ON p.unidad_id = u.id_unidad
                                                WHERE general_id='{$id_ingresoi}'")->fetch();
                        
                        $datoImport = array(
                            'fecha_ingreso' => $imports_general['fecha_import'], 
                            'hora_ingreso' => $imports_general['hora_import'], 
                            'nombre_ingreso' => $imports_general['tipo_tabla'], 
                            'descripcion' => '', 
                            'monto_total' => 0, 
                            'almacen' => '', 
                            'empleado' =>  $imports_general['nombres'],
                            'nombre_proveedor' => $imports_general['nombres']
                        );
                        $datos = array(
                            'compra' => ($datoImport) ? $datoImport : array(),
                            'productos' => ($productos) ? $productos : array()
                        );
                        
                        $respuesta = array(
                            'status' => 'success', 
                            'msg' => 'Operacion satisfactoria',
                            'data'=> $datos
                        );
        
                        echo json_encode($respuesta);
                    }else {
                        $respuesta = array(
                            'status' => 'success', 
                            'msg' => 'Error al obtener los registros',
                            'data'=> array()
                        );
                    }
        
        
                    break;
                case 'otra_opcion':
                    # code...
                    break;
                default:
                    $respuesta = array(
                        'status' => 'failed', 
                        'msg' => 'Operacion observada',
                        'data'=> array()
                    );
                    
                    echo json_encode($respuesta);
                    break;
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
        $respuesta = array(
            'status' => 'failed', 
            'msg' => 'Datos erroneos',
            'data'=> array()
        );
        
        echo json_encode($respuesta);
    }
}else {
    $respuesta = array(
        'status' => 'failed', 
        'msg' => 'Metodo no definido',
        'data'=> array()
    );
    
    echo json_encode($respuesta);
}

