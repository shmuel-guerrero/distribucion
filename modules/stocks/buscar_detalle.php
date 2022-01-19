<?php
    $id_almacen=$params[0]??0;
    $id_producto=$params[1]??0;
    $producto=$db->query("SELECT codigo,nombre,precio_actual FROM inv_productos WHERE id_producto='$id_producto'")->fetch_first();
    $almacen=$db->query("SELECT almacen FROM inv_almacenes WHERE id_almacen='$id_almacen'")->fetch_first();
    $respuesta=array_merge($producto,$almacen);
    echo json_encode($respuesta);