<?php
    if(is_ajax()&&is_post()):
        if(isset($params)):
            $IdOrdenSalida=$_POST['IdOrdenSalida'];
            $Fecha=isset($_POST['Fecha'])?$_POST['Fecha']:date('Y-m-d');
            $Responsables=$db->query("SELECT p.nombre,p.codigo,od.precio_id,od.cantidad,(od.precio_id*od.cantidad)AS subtotal
                                    FROM inv_productos AS p
                                    LEFT JOIN inv_ordenes_detalles AS od ON od.producto_id=p.id_producto
                                    LEFT JOIN inv_ordenes_salidas AS os ON od.orden_salida_id=os.id_orden
                                    WHERE os.id_orden='{$IdOrdenSalida}' ")->fetch();
            echo json_encode($Responsables);
            return;
        endif;
        require_once bad_request();
		exit;
    else:
        require_once not_found();
	    exit;
    endif;