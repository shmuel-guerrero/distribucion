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
    
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo)) { 


        $inputFileType 	= IOFactory::identify($archivo);
        $objReader 		= IOFactory::createReader($inputFileType);

        // se define lectura de los datos de excel
        $objReader->setReadFilter(new MyReadFilter);

        $spreadsheet = $objReader->load($archivo);

        //se obtiene en la variable todos los datos del archivo excel
        $cantidad = $spreadsheet->getActiveSheet()->toArray();

        $almacen        = (isset($cantidad[0][1]) && $cantidad[0][1]) ? iconv("UTF-8", "UTF-8//IGNORE", $cantidad[0][1]) : '';
        $proveedor      = (isset($cantidad[1][1]) && $cantidad[1][1]) ? iconv("UTF-8", "UTF-8//IGNORE", $cantidad[1][1]) : '';
        $descripcion    = (isset($cantidad[2][1]) && $cantidad[2][1]) ? iconv("UTF-8", "UTF-8//IGNORE", $cantidad[2][1]) : '';
        $imp_total      = (isset($cantidad[3][1]) && $cantidad[3][1]) ? trim($cantidad[3][1]) : 0;
        
        $Obs = array();
        $arrayObs = array();
        $arrayDetallesObs = array();

        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {
 
			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

        
            if (!$almacen || !$proveedor || !$descripcion || !$imp_total) {
                $arrayObs = array(
                    'almacen' => (!$almacen) ? 'Informacion Incorrecta' : 'Informacion satisfactoria', 
                    'proveedor' => (!$proveedor) ? 'Informacion Incorrecta' : 'Informacion satisfactoria', 
                    'descripcion' => (!$descripcion) ? 'Informacion Incorrecta' : 'Informacion satisfactoria', 
                    'importe_total' => (!$imp_total) ? 'Informacion Incorrecta' : 'Informacion satisfactoria'
                );
                $Obs['datos'] = $arrayObs;
                // Envia respuesta
                echo json_encode(array('estado' => 'warning', 'responce' => $Obs));            
                exit;
            }

            if (!$cantidad[4][0]) {
                
                foreach ($cantidad as $key => $value) {

                    if ($value[0] && $key > 4 ) {
                        $codigo          	= (isset($value[0]) && $value[0]) ? iconv("UTF-8", "UTF-8//IGNORE", $value[0]) : ''; 
                        $cantidad           = (isset($value[2])) ? iconv("UTF-8", "UTF-8//IGNORE", $value[2]) : 0; 
                        $costo 				= (isset($value[3])) ? iconv("UTF-8", "UTF-8//IGNORE", $value[3]) : 0; 
                        $importe       		= (isset($value[4])) ? iconv("UTF-8", "UTF-8//IGNORE", $value[4]) : 0;
        
                        $producto_id = $db->query("SELECT * FROM inv_productos WHERE codigo = '{$codigo}' ")->fetch_first()['id_producto'];

                        if (!$producto_id || !$cantidad || !$costo || !$importe) {
                            $arrayDetallesObs[$key] = $cantidad[$key];
                        }                                              
                    }
                }

                $Obs['elemts'] = (count($arrayDetallesObs) > 0) ? $arrayDetallesObs : array();

                $duplicates = array();
                $elemts_unique = array_unique($cantidad);
                $duplicates = array_diff_key($cantidad, $elemts_unique);
                if (count($duplicates) > 0) {
                    $Obs['duplicts'] = $duplicates;
                }

                // Envia respuesta
                echo json_encode(array('estado' => 'warning', 'responce' => $Obs));            
                exit;
            }

        

            // se verifica la existencia en base de datos de el almacen
            $almacen_verificado = $db->from('inv_almacenes')->where(array('almacen' => $almacen ))->fetch_first();
            $almacen = (isset($almacen_verificado['almacen'])) ? $almacen_verificado['almacen']: '';
            $almacen_id = (isset($almacen_verificado['id_almacen'])) ? $almacen_verificado['id_almacen']: '';
            if(!$almacen && !$almacen_id) throw new Exception('No se tiene el almacen registrado.');

            // se verifica la existencia en base de datos del proveedor
            $proveedor_verificado = $db->from('inv_proveedores')->where(array('proveedor' => $proveedor ))->fetch_first();
            $proveedor = (isset($proveedor_verificado['proveedor'])) ? $proveedor_verificado['proveedor']: $proveedor;
            $proveedor_id = (isset($proveedor_verificado['id_proveedor'])) ? $proveedor_verificado['id_proveedor']: 0;        
            if(!$proveedor || !$proveedor_id){
                $datoProveedor = array(
                    'proveedor' => $proveedor, 
                    'nit' => 0
                );
                $proveedor_id = $db->insert('inv_proveedores', $datoProveedor);
                $proveedor_id = (isset($proveedor_id)) ? $proveedor_id: 0;   
                //echo "nuevo proveedor registrado";
            }


            if(!$proveedor && !$proveedor_id) throw new Exception('No se tiene el proveeedor registrado.');

            // se verifica la existencia del importe                
            $imp_total = ($imp_total  && $imp_total > 0) ? iconv("UTF-8", "UTF-8//IGNORE", $imp_total): 0;       
            if(!$imp_total) throw new Exception('El importe total no tiene la informacion correcta');

        

            $datos_compra = array(
                'fecha_ingreso' => date('Y-m-d'), 
                'hora_ingreso' => date('H:i:s'), 
                'tipo' => 'Compra', 
                'descripcion' => ($descripcion) ? iconv("UTF-8", "UTF-8//IGNORE", $descripcion): '', 
                'monto_total' => ($imp_total) ? $imp_total: 0, 
                'descuento' => 0, 
                'monto_total_descuento' => 0, 
                'nombre_proveedor' => ($proveedor) ? iconv("UTF-8", "UTF-8//IGNORE", $proveedor): 'Proveedor no identificado', 
                'nro_registros' => (isset($nro_filas)) ? $nro_filas - 4: 0, 
                'almacen_id' => $almacen_id, 
                'empleado_id' => $_user['persona_id'], 
                'transitorio' => 0,
                'des_transitorio' => 0,
                'plan_de_pagos' => 'no',
                'proveedor_id' => $proveedor_id
            );

            $ingreso_id = $db->insert('inv_ingresos_import', $datos_compra);

            $a = 0;
            $importe_total = 0;
            foreach ($cantidad as $key => $value) {
                
                if ($value[0] && $key > 4 ) {
                    $codigo          	= (isset($value[0]) && $value[0]) ? trim($value[0]) : ''; 
                    //$producto           = (isset($value[1])) ? trim($value[1]) : ''; 
                    $cantidad           = (isset($value[2])) ? trim($value[2]) : 0; 
                    $costo 				= (isset($value[3])) ? trim($value[3]) : 0; 
                    $importe       		= (isset($value[4])) ? trim($value[4]) : 0;
                    
                    if ($codigo && $codigo != '') {
                        
                        $producto_id = $db->query("SELECT * FROM inv_productos WHERE codigo = '{$codigo}' ")->fetch_first();
                        $producto_id = (isset($producto_id['id_producto'])) ? $producto_id['id_producto'] : 0;
                        
                        if ($producto_id && 
                            $cantidad != '' && $cantidad > 0 && (is_numeric($cantidad) || is_int($cantidad) || is_float($cantidad)) && 
                            $costo != '' && $costo > 0  && (is_numeric($costo) || is_int($costo) || is_float($costo))) {
                            $a++;
                            
                            $datos = array(
                                'cantidad' => ($cantidad) ? $cantidad : 0, 
                                'costo' => ($costo) ? $costo : 0, 
                                'lote2'=> (isset($lote[$key])) ? $lote[$key] : '',
                                'elaboracion'=>(isset($elaboracion[$key])) ? $elaboracion[$key] : '0000-00-00',
                                'factura' => (isset($facturas[$key])) ? $facturas[$key] : 0,
                                'contenedor' => (isset($contenedores[$key])) ? $contenedores[$key] : 0,
                                'producto_id' => (is_numeric($producto_id)) ? $producto_id: 1,
                                'ingreso_id' => $ingreso_id,
                                'lote'=>'lt'.($cantidad+1),
                                'lote_cantidad'=>(isset($cantidad)) ? $cantidad : 0,
                                'vencimiento' => (isset($vencimientos[$key])) ? $vencimientos[$key] : 0
                            );
                
                            // Guarda la informacion
                            $id_detalle = $db->insert('inv_ingresos_detalles_import', $datos); 
                            $importe_total += ($costo*$cantidad);
                        }
            
                    }

                }
            } 

            $db->where(array('id_ingreso' => $ingreso_id ))->update('inv_ingresos_import', array('nro_registros' => $a, 'monto_total' => $importe_total ));
            
            // Envia respuesta
            echo json_encode(array('estado' => 'success', 'responce' => $ingreso_id));

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
