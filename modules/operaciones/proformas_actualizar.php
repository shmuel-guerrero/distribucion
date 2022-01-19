<?php
/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// echo json_encode($_POST); die();
// Verifica si es una peticion ajax y post
if(is_post()) {
    // Verifica la existencia de los datos enviados
    if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
        // Importa la libreria para convertir el numero a letra
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

        // Obtiene los datos de la nota
        $nit_ci = trim($_POST['nit_ci']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $id_cliente = trim($_POST['cliente_id']);
        $productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
        $nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
        $cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
        $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();        
        $nro_registros = count($_POST['productos']);
        $monto_total = trim($_POST['monto_total']);                
        $almacen_id = trim($_POST['almacen_id']);
        
        $id_proforma=trim($_POST['id_proforma']);
        
        //descuento                
        $descuento_porc = (isset($_POST['descuento_porc'])) ? clear($_POST['descuento_porc']) : 0;
        $descuento_bs = (isset($_POST['descuento_bs'])) ? clear($_POST['descuento_bs']) : 0;                
        $monto_total_descuento = $monto_total - $descuento_bs;                
       

        // Obtiene la moneda
        $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
        $moneda = ($moneda) ? $moneda['moneda'] : '';

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $monto_total);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

        // Instancia la nota
        $proforma = array(                                      
            'monto_total' => $monto_total,
            'descuento_porcentaje' => $descuento_porc,
            'descuento_bs' => $descuento_bs,
            'monto_total_descuento' => $monto_total_descuento,
            // 'nit_ci' => $nit_ci,
            // 'nombre_cliente' => strtoupper($nombre_cliente),
            'nro_registros' => $nro_registros,            
            'almacen_id' => $almacen_id,            
            'empleado_id' => $_user['persona_id']
        );
        // Guarda la informacion
        $db->where('id_proforma',$id_proforma)->update('inv_proformas', $proforma);
        
        //eliminamos los detalles del proforma editado
        $db->delete()->from('inv_proformas_detalles')->where('proforma_id',$id_proforma)->execute();

        // Recorre los productos
        foreach ($productos as $nro => $elemento) {
                
            $id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidad[$nro])->fetch_first();
            $cantidad = $cantidades[$nro] * cantidad_unidad($db, $productos[$nro], $id_unidad['id_unidad']);

            /////////////////////////////////////////////////////////////////////////////////////////                
            // $Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
            // foreach($Detalles as $Fila=>$Detalle){                    
            //     $detalle = array(
            //         'cantidad' => $cantidad,
            //         'precio' => $precios[$nro],
            //         'unidad_id' => $id_unidad['id_unidad'],
            //         'descuento' => 0,
            //         'producto_id' => $productos[$nro],
            //         'egreso_id' => $id_proforma,
            //         'lote'=>''
            //     );
            //     // var_dump($aux);die();
            //     // Guarda la informacion
            //     $id = $db->insert('inv_egresos_detalles', $detalle);
            // }
            $detalle = array(
                'cantidad' => $cantidad,
                'precio' => $precios[$nro],
                'unidad_id' => $id_unidad['id_unidad'],
                'descuento' => 0,
                'producto_id' => $productos[$nro],
                'proforma_id' => $id_proforma,              
            );
            // var_dump($aux);die();
            // Guarda la informacion
            $id = $db->insert('inv_proformas_detalles', $detalle);
                
        }
            
        // Guarda en el historial
        $data = array(
            'fecha_proceso' => date("Y-m-d"),
            'hora_proceso' => date("H:i:s"),
            'proceso' => 'c',
            'nivel' => 'l',
            'direccion' => '?/operaciones/proformas_actualizar',
            'detalle' => 'Se inserto los productos del detalle con identificador numero ' . $id,
            'usuario_id' => $_SESSION[user]['id_user']
        );
        $db->insert('sys_procesos', $data);
                
        // Envia respuesta
        //echo json_encode($id_egreso);
        $_SESSION[temporary] = array(
            'alert' => 'success',
            'title' => 'La actualizaci&oacute;n se realiz&oacute; satisfactoriamente.',
            'message' => 'La actualizaci&oacute;n se guard&oacute; en la base de datos.'
        );        
        header('Location:?/operaciones/proformas_listar');
        
    } else {
        // Envia respuesta
        $_SESSION[temporary] = array(
            'alert' => 'danger',
            'title' => 'No se actualiz&oacute; la proforma correctamente.',
            'message' => 'Revise si se actualiz&oacute; alg&uacute;n dato.'
        );
        return redirect(back());
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
?>
<?php
// //28 -80 - 87
// /**
//  * SimplePHP - Simple Framework PHP
//  * 
//  * @package  SimplePHP
//  * @author   Wilfredo Nina <wilnicho@hotmail.com>
//  */

// // Verifica si es una peticion ajax y post
// if (is_ajax() && is_post()) {
// 	// Verifica la existencia de los datos enviados
// 	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['atencion']) && isset($_POST['validez']) && isset($_POST['observacion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
// 		// Importa la libreria para convertir el numero a letra
// 		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// 		// Obtiene los datos de la proforma
// 		$nit_ci = trim($_POST['nit_ci']);
// 		$nombre_cliente = trim($_POST['nombre_cliente']);
// 		$atencion = trim($_POST['atencion']);
// 		$validez = trim($_POST['validez']);
// 		$observacion = trim($_POST['observacion']);
// 		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
// 		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
// 		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
// 		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
// 		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
// 		$unidades = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
// 		$nro_registros = trim($_POST['nro_registros']);
// 		$monto_total = trim($_POST['monto_total']);
// 		$almacen_id = trim($_POST['almacen_id']);
		
// 		$descuento_porc = isset($_POST['descuento_porc'])?trim($_POST['descuento_porc']):0;
//         $descuento_bs = trim($_POST['descuento_bs']);
//         $total_importe_descuento =$_POST['total_importe_descuento']==''?0:trim($_POST['total_importe_descuento']);

// 		// Convierte a mayusculas
// 		$nombre_cliente = upper($nombre_cliente);
// 		$atencion = upper($atencion);
// 		$observacion = upper($observacion);

// 		// Obtiene el numero de la proforma
// 		$nro_proforma = $db->query("select ifnull(max(nro_proforma), 0) + 1 as nro_proforma from inv_proformas")->fetch_first();
// 		$nro_proforma = $nro_proforma['nro_proforma'];

// 		// Define la variable de subtotales
// 		$subtotales = array();

// 		// Obtiene la moneda
// 		$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
// 		$moneda = ($moneda) ? $moneda['moneda'] : '';

// 		// Obtiene los datos del monto total
// 		$conversor = new NumberToLetterConverter();
// 		$monto_textual = explode('.', $monto_total);
// 		$monto_numeral = $monto_textual[0];
// 		$monto_decimal = $monto_textual[1];
// 		$monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

// 		if(isset($descuento_bs)){
			
// 		}else{
// 			$descuento_bs = 0;
// 		}
// 		// Instancia la proforma
// 		$proforma = array(
// 			'fecha_proforma' => date('Y-m-d'),
// 			'hora_proforma' => date('H:i:s'),
// 			'descripcion' => $atencion,
// 			'nro_proforma' => $nro_proforma,
// 			'monto_total' => $monto_total,
// 			'nombre_cliente' => $nombre_cliente,
// 			'nit_ci' => $nit_ci,
// 			'nro_registros' => $nro_registros,
// 			'validez' => $validez,
// 			'observacion' => $observacion,
// 			'almacen_id' => $almacen_id,
// 			'empleado_id' => $_user['persona_id'],
// 			'adelanto'=>0,
// 			'descuento_porcentaje' => $descuento_porc,
//             'descuento_bs' => $descuento_bs,
//             'monto_total_descuento' => $total_importe_descuento
// 		);

// 		// Guarda la informacion
// 		$proforma_id = $db->insert('inv_proformas', $proforma);

// 		// Recorre los productos
// 		foreach ($productos as $nro => $elemento) {
// 			// Forma el detalle
// 			$IdUnidad=$db->query("SELECT id_unidad FROM inv_unidades WHERE unidad='{$unidades[$nro]}'")->fetch_first()['id_unidad'];
// 			$detalle = array(
// 				'cantidad' => $cantidades[$nro],
// 				'precio' => $precios[$nro],
// 				'descuento' => $descuentos[$nro],
// 				'producto_id' => $productos[$nro],
// 				'proforma_id' => $proforma_id,
// 				'unidad_id'=>$IdUnidad
// 			);

// 			// Genera los subtotales
// 			$precios[$nro] = (float)$precios[$nro];
// 			$cantidades[$nro] = (float)$cantidades[$nro];
// 			$subtotales[$nro] = $precios[$nro] * $cantidades[$nro];					
// 			// var_dump($subtotales[$nro]);die();

// 			// Guarda la informacion
// 			$db->insert('inv_proformas_detalles', $detalle);
// 		}

// 		// Instancia la respuesta
// 		$respuesta = array(
// 			'papel_ancho' => 10,
// 			'papel_alto' => 30,
// 			'papel_limite' => 576,
// 			'empresa_nombre' => $_institution['nombre'],
// 			'empresa_sucursal' => 'SUCURSAL Nº 1',
// 			'empresa_direccion' => $_institution['direccion'],
// 			'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
// 			'empresa_ciudad' => 'LA PAZ - BOLIVIA',
// 			'empresa_actividad' => $_institution['razon_social'],
// 			'empresa_nit' => $_institution['nit'],
// 			'proforma_titulo' => 'P  R  O  F  O  R  M  A',
// 			'proforma_numero' => $proforma['nro_proforma'],
// 			'proforma_fecha' => date_decode($proforma['fecha_proforma'], 'd/m/Y'),
// 			'proforma_hora' => substr($proforma['hora_proforma'], 0, 5),
// 			'cliente_nit' => $proforma['nit_ci'],
// 			'cliente_nombre' => $proforma['nombre_cliente'],
// 			'venta_titulos' => array('CANTIDAD', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
// 			'venta_cantidades' => $cantidades,
// 			'venta_detalles' => $nombres,
// 			'venta_precios' => $precios,
// 			'venta_subtotales' => $subtotales,
// 			'venta_total_numeral' => $proforma['monto_total'],
// 			'venta_total_literal' => $monto_literal,
// 			'venta_total_decimal' => $monto_decimal . '/100',
// 			'venta_moneda' => $moneda,
// 			'impresora' => $_terminal['impresora']
// 		);

// 		// Envia respuesta
// 		echo json_encode($proforma_id);
// 	} else {
// 		// Envia respuesta
// 		echo 'error';
// 	}
// } else {
// 	// Error 404
// 	require_once not_found();
// 	exit;
// }
?>