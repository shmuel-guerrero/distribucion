<?php


// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	if (isset($params)) {
		if (isset($_POST['busqueda'])) {
			$busqueda = trim($_POST['busqueda']);
			$id_usuario = trim($_POST['id_usuario']);
			$Fecha = date('Y-m-d');
			$id_orden = $db->query("SELECT id_orden
					FROM inv_ordenes_salidas AS os
					LEFT JOIN sys_empleados AS e ON os.empleado_id=e.id_empleado
					LEFT JOIN sys_users AS u ON e.id_empleado=u.persona_id
					WHERE os.fecha_orden='{$Fecha}' AND u.id_user='{$id_usuario}' AND os.estado='salida' LIMIT 1")->fetch_first();
			
			$id_orden = ($id_orden['id_orden']) ? $id_orden['id_orden'] : 0;
			if (!$id_orden) :
				echo json_encode([]);
				die();
			endif;
			$productos = $db->query("SELECT od.orden_salida_id,od.id_orden_detalle,p.id_producto,p.imagen,p.codigo,p.nombre,c.categoria,od.cantidad,od.precio_id AS precio,u.unidad,os.almacen_id
					FROM inv_ordenes_detalles AS od
					LEFT JOIN inv_productos AS p ON od.producto_id=p.id_producto
					LEFT JOIN inv_categorias AS c ON p.categoria_id=c.id_categoria
					LEFT JOIN inv_unidades AS u ON od.unidad_id=u.id_unidad
					LEFT JOIN inv_ordenes_salidas AS os ON os.id_orden=od.orden_salida_id
					WHERE od.orden_salida_id='{$id_orden}'")->fetch();
			$Datos= array();
			foreach($productos as $Fila=>$producto):
				$IdProducto=$producto['id_producto'];
				$material=$db->query("SELECT c.id_control,m.nombre AS nombre_m,ca.categoria AS categoria_m,c.cantidad AS cantidad_m,m.precio AS precio_m,u.unidad AS unidad_m,c.id_materiales
					FROM inv_control AS c
					LEFT JOIN inv_materiales AS m ON m.id_materiales=c.id_materiales
					LEFT JOIN inv_productos AS p ON p.id_producto=m.id_producto
					LEFT JOIN inv_categorias AS ca ON p.categoria_id=ca.id_categoria
					LEFT JOIN inv_unidades AS u ON m.id_unidad=u.id_unidad
					WHERE c.ordenes_salidas_id='{$id_orden}' AND  p.id_producto='{$IdProducto}'")->fetch_first();
				if(!$material):
					$material=array(
							'id_control'=>0,
							'nombre_m'=>0,
							'categoria_m'=>0,
							'cantidad_m'=>0,
							'precio_m'=>0,
							'unidad_m'=>0,
							'id_materiales'=>0,
					);
				endif;
				$producto=array_merge($producto,$material);
				$Datos[]=$producto;
			endforeach;
			echo json_encode($Datos);
		} else {
			require_once bad_request();
			exit;
		}
	} else {
		require_once bad_request();
		exit;
	}
} else {
	require_once not_found();
	exit;
}
