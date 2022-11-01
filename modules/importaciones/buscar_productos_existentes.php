<?php
    if(isset($_POST['id_importacion'])&&isset($_POST['id_almacen'])):
        $IdImportacion=trim($_POST['id_importacion']);
        $IdAlmacen=trim($_POST['id_almacen']);
        $Fecha=date('Y-m-d');
        $Consulta=$db->query("SELECT p.id_producto,p.imagen,p.nombre,p.codigo,c.id_categoria,c.categoria,IFNULL(i.ingresos,0)AS ingresos,IFNULL(e.egresos,0)AS egresos,p.precio_actual,u.id_unidad,u.unidad,id.cantidad AS cantidad,id.precio_ingreso AS costo,(id.precio_ingreso*id.cantidad) AS importe,id.fechav AS fechav,id.lote
            FROM inv_productos AS p
            LEFT JOIN inv_categorias AS c ON p.categoria_id=c.id_categoria
            LEFT JOIN inv_unidades AS u ON p.unidad_id=u.id_unidad
            LEFT JOIN(
                SELECT cid.producto_id,SUM(cid.cantidad)AS ingresos
                FROM inv_ingresos_detalles AS cid
                LEFT JOIN inv_ingresos AS ci ON ci.id_ingreso=cid.ingreso_id
                WHERE ci.almacen_id='{$IdAlmacen}'
                GROUP BY cid.producto_id
            )AS i ON i.producto_id = p.id_producto
            LEFT JOIN(
                SELECT ced.producto_id,SUM(ced.cantidad)AS egresos
                FROM inv_egresos_detalles AS ced
                LEFT JOIN inv_egresos AS ce ON ce.id_egreso=ced.egreso_id
                WHERE ce.almacen_id='{$IdAlmacen}'
                GROUP BY ced.producto_id
            )AS e ON e.producto_id = p.id_producto
            LEFT JOIN tmp_ingreso_detalle AS id ON p.id_producto=id.producto_id
            WHERE id.importacion_id='{$IdImportacion}'")->fetch();
        echo json_encode($Consulta);
    else:
        require_once not_found();
	    die;
    endif;