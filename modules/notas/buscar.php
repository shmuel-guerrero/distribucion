<?php


// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de parametros
	if (isset($params)) {
		// Verifica la existencia de datos
		if (isset($_POST['busqueda'])) {
			// Obtiene los datos
			$busqueda = trim($_POST['busqueda']);
			$id_almacen = trim($_POST['almacen']);

			// Obtiene los productos con el valor buscado
			$Fecha=date('Y-m-d');
				$productos = $db->query("SELECT p.id_producto, p.promocion, z.id_asignacion, z.unidad_id, z.unidade, z.cantidad2, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, (IFNULL(s.cantidad_egresos, 0) + IFNULL(sp.cantidad_promocion, 0) + IFNULL(spr.cantidad_venta_promo, 0)) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
					FROM inv_productos p
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
						   FROM inv_ingresos_detalles d
						   LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
						   WHERE transitorio = 0 AND i.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
						   FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
						   WHERE e.almacen_id = '$id_almacen' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
                    LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_venta_promo
							FROM inv_egresos_detalles d 
                            LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                            LEFT JOIN inv_productos pr ON pr.id_producto = d.promocion_id
						   	WHERE e.almacen_id = '$id_almacen' AND  d.promocion_id > 2 AND e.anulado != 3 AND pr.fecha_limite < CURDATE() GROUP BY d.producto_id) AS spr ON spr.producto_id = p.id_producto
                    LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad*a.cantidad) AS cantidad_promocion
						    FROM inv_ingresos_detalles a 
                            LEFT JOIN inv_ingresos b on b.id_ingreso = a.ingreso_id 
                            INNER JOIN inv_promociones d ON d.id_promocion = a.producto_id
                            INNER JOIN inv_productos c ON c.id_producto = d.id_promocion
                            INNER JOIN inv_productos e ON e.id_producto = d.producto_id
                            WHERE transitorio = 0 AND b.almacen_id = '$id_almacen' AND e.fecha_limite > CURDATE() GROUP BY d.producto_id) AS sp ON sp.producto_id = p.id_producto
					LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
					LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.cantidad_unidad,')',w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade, GROUP_CONCAT(w.cantidad_unidad SEPARATOR '*') AS cantidad2
					   FROM (SELECT *
							FROM inv_asignaciones q
								  LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad
								  
										 ORDER BY u.unidad DESC) w GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id
					WHERE ('$Fecha'<=p.fecha_limite OR p.fecha_limite='1000-01-01') AND eliminado = 0 AND (p.codigo_barras like '%" . $busqueda . "%' OR p.codigo like '%" . $busqueda . "%' OR p.nombre_factura like '%" . $busqueda . "%' OR c.categoria like '%" . $busqueda . "%') order by p.nombre asc")->fetch();

				foreach ($productos as $key => $value) {
					$productos[$key]['imagen'] = (file_exists(files . '/productos/' . $productos[$key]['imagen'])) ? $productos[$key]['imagen'] : '';
				}

			// Devuelve los resultados
			echo json_encode($productos);
		} else {
			// Error 401
			require_once bad_request();
			exit;
		}		
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>