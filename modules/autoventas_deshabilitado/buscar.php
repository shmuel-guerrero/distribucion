<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	if (isset($params)) {
		if (isset($_POST['busqueda'])) {
			$busqueda = trim($_POST['busqueda']);
			$id_almacen=$_POST['IdAlmacen'];
			$productos=$db->query("SELECT p.id_producto,p.promocion, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual,
						IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos,
						IFNULL(s.cantidad_egresos, 0) AS cantidad_egresos, u.unidad, u.sigla, c.categoria,
						IFNULL((SELECT m.id_materiales FROM inv_materiales AS m WHERE m.id_producto=p.id_producto),false)AS materiales,
						IFNULL((SELECT true FROM inv_materiales WHERE id_producto=p.id_producto LIMIT 1),false)AS prestamo,
						IFNULL((SELECT ms.stock FROM inv_materiales AS m LEFT JOIN inv_materiales_stock AS ms ON ms.materiales_id=m.id_materiales WHERE m.id_producto=p.id_producto AND ms.almacen_id='$id_almacen'),false)AS stockp
					FROM inv_productos p
					LEFT JOIN (
						SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
						FROM inv_ingresos_detalles d
						LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
						WHERE i.almacen_id = '$id_almacen'
						GROUP BY d.producto_id
					) AS e ON e.producto_id = p.id_producto
					LEFT JOIN (
						SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
						FROM inv_egresos_detalles d
						LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
						WHERE e.almacen_id = '$id_almacen'
						GROUP BY d.producto_id
					) AS s ON s.producto_id = p.id_producto
					LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id
					LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
					WHERE p.codigo LIKE '%$busqueda%' OR p.nombre_factura LIKE '%$busqueda%' OR c.categoria LIKE '%$busqueda%' ORDER BY p.nombre ASC")->fetch();
			echo json_encode($productos);
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
