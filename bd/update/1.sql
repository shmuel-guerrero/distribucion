SELECT  a.producto_id, 				
GROUP_CONCAT( u.id_unidad ORDER BY a.id_asignacion SEPARATOR '|') AS arr_unidad_id,
GROUP_CONCAT( u.unidad ORDER BY a.id_asignacion SEPARATOR '|') AS arr_unidad,
GROUP_CONCAT( a.tamanio ORDER BY a.id_asignacion SEPARATOR '|') AS arr_tamanio,
GROUP_CONCAT( a.id_asignacion ORDER BY a.id_asignacion SEPARATOR '|') AS arr_asignacion_id,
GROUP_CONCAT( a.precio_actual ORDER BY a.id_asignacion SEPARATOR '|') AS arr_precio_actual,
GROUP_CONCAT( a.costo_actual ORDER BY a.id_asignacion SEPARATOR '|') AS arr_costo_actual			
FROM  inv_asignaciones a 
LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
WHERE a.producto_id = 1
GROUP BY  a.producto_id
				