<?php

// echo json_encode($_POST); die();

if (is_post()) {
	if ( isset($_POST['id_ingreso'])  ) {
	    
	        
	   $ingresos = (isset($_POST['id_ingreso'])) ? $_POST['id_ingreso'] : array();
	   
	   if (count($ingresos) > 0) {
	       foreach ($ingresos as $ingreso_id){
	           
	           $pagos = $db->from('inv_pagos')->where('movimiento_id', $ingreso_id)->where('tipo', 'Ingreso')->fetch_first();
	           $detallesPagos = $db->from('inv_pagos_detalles')->where('pago_id', $pagos['id_pago'])->fetch();
	           foreach($detallesPagos as $detalle){
	               if ($detalle['estado'] == 0) {
	                   $data = array(
            				'fecha_pago' => date('Y-m-d'),
            				'tipo_pago' => 'Efectivo',
            				'estado' => 1
            		    );
            		    $db->where('id_pago_detalle', $detalle['id_pago_detalle'])->update('inv_pagos_detalles', $data);
	               }
	            }
	        }
	        
	        // Instancia la variable de notificacion
        	$_SESSION[temporary] = array(
        		'alert' => 'success',
        		'title' => 'Pagos registrados satisfactoriamente!',
        		'message' => 'Los pagos se registraron correctamente.'
        	);
        	
	    } else {
	        // Instancia la variable de notificacion
    		$_SESSION[temporary] = array(
    			'alert' => 'danger',
    			'title' => 'Accion insatisfactoria!',
    			'message' => 'Los pagos no se registraron.'
    		);
	    }

		// Redirecciona a la pagina principal
		redirect(back());

	} else {
		// Error 404
		require_once not_found();
		exit;
	}

} else {
	// Error 404
	require_once not_found();
	exit;
}

?>