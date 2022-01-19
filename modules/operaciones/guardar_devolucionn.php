<?php
/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
//var_dump($_POST);exit();
// Verifica si es una peticion ajax y post
var_dump($_POST);
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la proforma
        $nit_ci = trim($_POST['nit_ci']);
        $id_cliente = trim($_POST['id_cliente']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $nro_factura = trim($_POST['nro_factura']);
        $motivo = trim($_POST['motivo']);
        $descripcion = trim($_POST['descripcion']);
        $tipo = 'Baja';

        $monto_total = trim($_POST['monto_total']);
        $almacen_id = trim($_POST['almacen_id']);
        $nro_registros = trim($_POST['nro_registros']);

        $telefono = trim($_POST['telefono_cliente']);
        $validez = trim($_POST['validez']);
        $observacion = trim($_POST['observacion']);
        $direccion = trim($_POST['direccion']);
        $atencion = trim($_POST['atencion']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad']: array();
        $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
        $descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();

//        var_dump($_POST);exit();
        //obtiene al cliente

        if($_POST['id_egreso'] > 0){
            $egreso = array(
                'fecha_egreso' => date('Y-m-d'),
                'hora_egreso' => date('H:i:s'),
                'tipo' => 'Devolucion',
                'provisionado' => 'N',
                'descripcion' => $descripcion,
                'nro_factura' => $nro_factura,
                'nro_autorizacion' => '',
                'codigo_control' => '',
                'fecha_limite' => '0000-00-00',
                'monto_total' => $monto_total,
                'descuento_porcentaje' => 0,
                'descuento_bs' => 0,
                'monto_total_descuento' => 0,
                'cliente_id' => $id_cliente,
                'nit_ci' => $nit_ci,
                'nombre_cliente' => strtoupper($nombre_cliente),
                'nro_registros' => $nro_registros,
                'estadoe' => 0,
                'coordenadas' => '',
                'observacion' => '',
                'empleado_id' => $_user['persona_id'],
                'dosificacion_id' => 0,
                'almacen_id' => $almacen_id,
                'motivo_id' => 0,
                'duracion' => '00:00:00',
                'cobrar' => '',
                'grupo' => '',
                'descripcion_venta' => 'DEVOLUCION'
            );

            // Guarda la informacion
            $id = $db->insert('inv_egresos', $egreso);
             // Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/operaciones/guardar_devolucion',
				'detalle' => 'Se creo inventario egreso con identificador numero ' . $id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);
			
			$db->insert('sys_procesos', $data) ; 

            foreach($productos as $nro => $producto){
                $unidad2 = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidad[$nro])->fetch_first();
                $unidad3 = $unidad2['id_unidad'];
                $cantidad = cantidad_unidad($db, $productos[$nro], $unidad3)*$cantidades[$nro];
                $detalle = array(
                    'cantidad' => $cantidad,
                    'unidad_id' => $unidad3,
                    'precio' => $precios[$nro],
                    'descuento' => $descuentos[$nro],
                    'producto_id' => $productos[$nro],
                    'egreso_id' => $id
                );

                // Guarda la informacion
                $id_detalle = $db->insert('inv_egresos_detalles', $detalle);
                 // Guarda Historial
    			$data = array(
    				'fecha_proceso' => date("Y-m-d"),
    				'hora_proceso' => date("H:i:s"), 
    				'proceso' => 'c',
    				'nivel' => 'l',
    				'direccion' => '?/operaciones/guardar_devolucion',
    				'detalle' => 'Se creo inventario egreso  detalle con identificador numero ' . $id_detalle ,
    				'usuario_id' => $_SESSION[user]['id_user']
    			);
    			$db->insert('sys_procesos', $data) ; 
            }
        }
        $_SESSION[temporary] = array(
            'alert' => 'success',
            'title' => 'Se realizo la devolucion!',
            'message' => 'El registro se realizó correctamente.'
        );
        redirect('?/operaciones/preventas_listar');

		// Envia respuesta
		echo json_encode($respuesta);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>