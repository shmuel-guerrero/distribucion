<?php
/**
 * SimplePHP - Simple Framework PHP
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 **/
if (is_ajax() && is_post()) {
    /*print_r($_POST);
    die();*/
    if (isset($_POST['responsable'])) {
        $IdAlmacen = trim($_POST['almacen_id']);
        $Total = trim($_POST['monto_total']);
        $IdLiquidador=0;
        $IdResponsable=$_POST['responsable'];
        $IdPersona=$_SESSION[user]['id_user'];
        $IdRuta=$db->query("SELECT id_ruta FROM gps_rutas WHERE empleado_id='{$IdPersona}' LIMIT 1")->fetch_first();
        $IdRuta=$IdRuta['id_ruta'];
        $Fecha=date('Y-m-d');
        $Hora=date('H:i:s');

        //Actualizar
        $Sentencia="SELECT id_orden FROM inv_ordenes_salidas WHERE estado='salida' AND empleado_id='{$IdResponsable}' LIMIT 1";
        $Consulta=$db->query($Sentencia)->fetch_first();
        if($Consulta):
            $Cambios=array(
                    'estado'=>'entregado'
            );
            $Condicion=array(
                    'estado'=>'salida',
                    'empleado_id'=>$IdResponsable
            );
            $db->where($Condicion)->update('inv_ordenes_salidas',$Cambios);
        endif;

        if(isset($_POST['IdOrdenSalida'])):
            $IdOrdenSalida=$_POST['IdOrdenSalida'];
            $db->delete()->from('inv_ordenes_salidas')->where('id_orden',$IdOrdenSalida)->limit(1)->execute();
            $db->delete()->from('inv_ordenes_detalles')->where('orden_salida_id',$IdOrdenSalida)->execute();
        endif;

        //Registro de la Orden de Salida
        $Datos=array(
                'fecha_orden'=>$Fecha,
                'hora_orden'=>$Hora,
                'empleado_id'=>$IdResponsable,
                'estado'=>'salida',
                'almacen_id'=>$IdAlmacen,
                'empleado_regitro_id'=>$IdPersona,
                'empleado_entrega_id'=>$IdLiquidador
        );
        $IdOrdenSalida=$db->insert('inv_ordenes_salidas',$Datos);

        // Guarda Historial
        $Datos=array(
                'fecha_proceso' => $Fecha,
                'hora_proceso' => $Hora,
                'proceso' => 'c',
                'nivel' => 'l',
                'direccion' => '?/proformas/guardar',
                'detalle' => 'Se creó una orden de salida con identificador número '.$IdOrdenSalida,
                'usuario_id' => $_SESSION[user]['id_user']
        );
        $db->insert('sys_procesos',$Datos);

        //Datos del Carrito de Ordenes de Salida
        $productos      =(isset($_POST['productos']))      ? $_POST['productos']       :[];
        $nombres        =(isset($_POST['nombres']))        ? $_POST['nombres']         :[];
        $cantidades     =(isset($_POST['cantidades']))     ? $_POST['cantidades']      :[];
        $cantidades_pres=(isset($_POST['cantidades_pres']))? $_POST['cantidades_pres'] :[];
        $unidad         =(isset($_POST['unidad']))         ? $_POST['unidad']          :[];
        $precios        =(isset($_POST['precios']))        ? $_POST['precios']         :[];
        $descuentos     =(isset($_POST['descuentos']))     ? $_POST['descuentos']      :[];

        foreach($productos as $Fila=>$elemento):
            $IdUnidad=$db->query("SELECT id_unidad FROM inv_unidades WHERE unidad='{$unidad[$Fila]}' LIMIT 1")->fetch_first();
            $IdUnidad = ($IdUnidad['id_unidad']) ? $IdUnidad['id_unidad'] : 0;
            $Datos=array(
                    'orden_salida_id'   =>$IdOrdenSalida,
                    'precio_id'         =>$precios[$Fila],
                    'cantidad'          =>$cantidades[$Fila],
                    'unidad_id'         =>$IdUnidad,//Unidad por defecto
                    'promocion_id'      =>0,
                    'producto_id'       =>$productos[$Fila],
            );
            $db->insert('inv_ordenes_detalles',$Datos);
            //Almacenes
            //if($Almacenes && $cantidades[$Fila]!=$cantidades_pres[$Fila]):
            if($cantidades_pres[$Fila]!=0):
                $Almacenes=$db->query("SELECT id_materiales FROM inv_materiales WHERE id_producto='{$productos[$Fila]}'")->fetch_first();
                $Datos=array(
                        'id_materiales'     =>$Almacenes['id_materiales'],
                        'tipo'              =>'salida',
                        'cantidad'          =>$cantidades_pres[$Fila],
                        'stock'             =>'egreso',
                        'cliente_id'        =>0,
                        'empleado_id'       =>$IdPersona,
                        'fecha_control'     =>date('Y-m-d'),//'0000-00-00'
                        'estado'            =>'pendiente',
                        'proveedor'         =>'',
                        'ordenes_salidas_id'=>$IdOrdenSalida,
                        'egreso_id'         =>0,
                );
                $db->insert('inv_control',$Datos);
            endif;
        endforeach;
        echo json_encode(true);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}