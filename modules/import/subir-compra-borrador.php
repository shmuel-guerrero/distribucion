<?php  

//var_dump($_POST['nro_hoja']);
//error_reporting(0);

require_once libraries .'/PHPExcel/Classes/PHPExcel.php';   
//var_dump($_FILES);exit(); && !empty($_POST['nro_registro'])
if (!empty($_FILES['archivo']) && !empty($_POST['nro_hoja'])) {


	$archivo = files . "/documents/xls/". $_FILES["archivo"]["name"];
	$nro     = $_POST['nro_hoja']-1; 
    $nro_registro    = 5000; //aleman
	
	if (move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo)) { 

		$inputFileType 	= PHPExcel_IOFactory::identify($archivo);
	    $objReader 		= PHPExcel_IOFactory::createReader($inputFileType);
        
	    //Cargando la hoja de calculo
	    $objPHPExcel 	= $objReader->load($archivo); 
	    //Asignar hoja de calculo activa
	    $sheet 			= $objPHPExcel->setActiveSheetIndex($nro);
	    $highestRow 	= $sheet->getHighestRow();  

        $nro_filas = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();     
        $nro_filas = ($nro_filas) ? $nro_filas + 1 : 0;   

	    $list = ["A", "B", "C", "D","E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE","AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "A0", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ"];
        $a=0; $b=1; $c=2; $d=3; $e=4; $f=5; $g=6; $h=7; $ii=8; $j=9; $k=10; $l=11; $m=12; $n=13; $o=14;   $p=15; $q=16;

        $almacen        = trim($sheet->getCell($list[$b] . 1)->getValue());
        $proveedor      = trim($sheet->getCell($list[$b] . 2)->getValue());
        $descripcion    = trim($sheet->getCell($list[$b] . 3)->getValue());
        $imp_total      = trim($sheet->getCell($list[$b] . 4)->getValue());  

        // se verifica la existencia en base de datos de el almacen
        $almacen_verificado = $db->from('inv_almacenes')->where(array('almacen' => $almacen ))->fetch_first();
        $almacen = (isset($almacen_verificado['almacen'])) ? $almacen_verificado['almacen']: '';
        $almacen_id = (isset($almacen_verificado['id_almacen'])) ? $almacen_verificado['id_almacen']: '';
        if(!$almacen && !$almacen_id) throw new Exception('No se tiene el almacen registrado.');
        
        // se verifica la existencia en base de datos del proveedor
        $proveedor_verificado = $db->from('inv_proveedores')->where(array('proveedor' => $proveedor ))->fetch_first();
        $proveedor = (isset($proveedor_verificado['proveedor'])) ? $proveedor_verificado['proveedor']: $proveedor;
        if(!$proveedor && !$proveedor_id){
            $datoProveedor = array(
                'proveedor' => $proveedor, 
                'nit' => 0
            );
            $proveedor_id = $db->insert('inv_proveedores', $datoProveedor);
            $proveedor_id = (isset($proveedor_id)) ? $proveedor_id: 0;        
        }else {            
            $proveedor_id = (isset($proveedor_verificado['id_proveedor'])) ? $proveedor_verificado['id_proveedor']: 0;        
        }
        if(!$proveedor && !$proveedor_id) throw new Exception('No se tiene el proveeedor registrado.');

        // se verifica la existencia del importe                
        $imp_total = ($imp_total  && $imp_total > 0) ? $imp_total: 0;       
        if(!$imp_total && !is_float($imp_total)) throw new Exception('El importe total no tiene la informacion correcta');

        
        $datos_compra = array(
            'fecha_ingreso' => date('Y-m-d'), 
            'hora_ingreso' => date('H:i:s'), 
            'tipo' => 'Compra', 
            'descripcion' => ($descripcion) ? iconv("UTF-8", "UTF-8//IGNORE", $descripcion): '', 
            'monto_total' => ($imp_total && is_float($imp_total) && $imp_total > 0) ? $imp_total: 0, 
            'descuento' => 0, 
            'monto_total_descuento' => 0, 
            'nombre_proveedor' => ($proveedor) ? iconv("UTF-8", "UTF-8//IGNORE", $proveedor): 'Proveedor no identificado', 
            'nro_registros' => (is_numeric($nro_filas)) ? $nro_filas - 4: 0, 
            'almacen_id' => $almacen_id, 
            'empleado_id' => $_user['persona_id'], 
            'transitorio' => 0,
            'des_transitorio' => 0,
            'plan_de_pagos' => 0,
            'proveedor_id' => $proveedor_id
        );
        var_dump($datos_compra);
        echo "<br>";

        $id_ingreso = $db->insert('inv_ingresos', $datos_compra);


	    for ($i=6; $i < $nro_filas; $i++) {  
            $a=0; $b=1; $c=2; $d=3; $e=4; $f=5; $g=6; $h=7; $ii=8; $j=9; $k=10; $l=11; $m=12; $n=13; $o=14;   $p=15; $q=16;

	    	$codigo          	= trim($sheet->getCell($list[$a] . $i)->getValue());
	    	$producto           = trim($sheet->getCell($list[$b] . $i)->getValue());
	    	$cantidad           = trim($sheet->getCell($list[$c] . $i)->getValue());
	    	$costo 				= trim($sheet->getCell($list[$d] . $i)->getValue());
	    	$importe       		= trim($sheet->getCell($list[$e] . $i)->getValue());
/* 	    	$complemento   		= trim($sheet->getCell($list[$f] . $i)->getValue());
	    	$genero   			= trim($sheet->getCell($list[$g] . $i)->getValue());
	    	$celular   		    = trim($sheet->getCell($list[$h] . $i)->getValue());
	    	$fecha_nac   		= trim($sheet->getCell($list[$ii] . $i)->getValue());
	    	$correo   			= trim($sheet->getCell($list[$j] . $i)->getValue());

	    	$aula				= trim($sheet->getCell($list[$k] . $i)->getValue());
	    	$paralelo			= trim($sheet->getCell($list[$l] . $i)->getValue());
	    	$nivel    			= trim($sheet->getCell($list[$m] . $i)->getValue());
	    	$turno   			= trim($sheet->getCell($list[$n] . $i)->getValue());
	    	$materia    		= trim($sheet->getCell($list[$o] . $i)->getValue());

	    	$usuario_docente    = trim($sheet->getCell($list[$p] . $i)->getValue());
	    	$contrasenia        = trim($sheet->getCell($list[$q] . $i)->getValue()); */

            $datos = array(
                'cantidad' => ($cantidad && is_numeric($cantidad)) ? $cantidad : 0, 
                'costo' => ($costo && is_float($costo) ? $costo : 0), 
                'lote2'=>$lote[$nro],
                'elaboracion'=>($elaboracion[$nro]!='') ? $vencimientos[$nro] : '0000-00-00',
                'factura' => (isset($facturas[$nro])) ? $facturas[$nro] : 0,
                'contenedor' => (isset($contenedores[$nro])) ? $contenedores[$nro] : 0,
                'producto_id' => $productos[$nro],
                'ingreso_id' => $ingreso_id,
                'lote'=>'lt'.($Cantidad+1),
                'lote_cantidad'=>(isset($cantidades[$nro])) ? $cantidades[$nro] : 0,
                'vencimiento' => (isset($vencimientos[$nro])) ? $vencimientos[$nro] : 0
            );

        // Guarda la informacion
        $id_detalle = $db->insert('inv_ingresos_detalles', $detalle);




            var_dump($codigo, $producto, $cantidad,$costo,$importe);
            echo "<br>";
 

			//$a++; $b++; $c++; $d++; $e++; $f++; $g++; $h++; $ii++; $j++; $k++; $l++; $m++; $n++; $o++;
	    }
	    exit();
	    
		echo json_encode(['uploaded' => $archivo]);
	} else {

	}
	
} else {
	echo json_encode(['error'=>'Introduzca número de hoja.']);
}

?>