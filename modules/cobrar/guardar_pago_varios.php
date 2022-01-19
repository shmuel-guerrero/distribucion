<?php

// echo json_encode($_POST); die();

if (is_post()) {
	if ( isset($_POST['id_egreso'])  ) {
	    
	        
	   $egresos = (isset($_POST['id_egreso'])) ? $_POST['id_egreso'] : array();
	   
	   if (count($egresos) > 0) {
	       foreach ($egresos as $egreso_id){
	           
	           $pagos = $db->from('inv_pagos')->where('movimiento_id', $egreso_id)->where('tipo', 'Egreso')->fetch_first();
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
        		'title' => 'Cobros registrados satisfactoriamente!',
        		'message' => 'Los cobros se registraron correctamente.'
        	);
        	
	    } else {
	        // Instancia la variable de notificacion
    		$_SESSION[temporary] = array(
    			'alert' => 'danger',
    			'title' => 'Accion insatisfactoria!',
    			'message' => 'Los cobros no se registraron.'
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