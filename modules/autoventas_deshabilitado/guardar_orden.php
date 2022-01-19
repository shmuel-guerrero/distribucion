<?php
/**
 * SimplePHP - Simple Framework PHP
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 **/
if (is_ajax() && is_post()) {
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

        /*$Datos=[
            'fecha_egreso'          =>$Fecha,
            'hora_egreso'           =>$Hora,
            'tipo'                  =>'Venta',
            'provisionado'          =>'N',
            'descripcion'           =>'Salida de Productos',
            'nro_factura'           =>0,
            'nro_autorizacion'      =>'',
            'codigo_control'        =>'',
            'fecha_limite'          =>'0000-00-00',
            'monto_total'           =>$Total,
            'descuento_porcentaje'  =>0,
            'descuento_bs'          =>0,
            'monto_total_descuento' =>0,
            'cliente_id'            =>0,
            'nombre_cliente'        =>'',
            'nit_ci'                =>'',
            'nro_registros'         =>count($productos),
            'estadoe'               =>'',
            'coordenadas'           =>'',
            'observacion'           =>'',
            'dosificacion_id'       =>0,
            'almacen_id'            =>$IdAlmacen,
            'empleado_id'           =>$IdPersona,
            'motivo_id'             =>0,
            'duracion'              =>0,
            'cobrar'                =>0,
            'grupo'                 =>0,
            'descripcion_venta'     =>0,
            'ruta_id'               =>$IdRuta,
            'estado'                =>0,
            'plan_de_pagos'         =>'no'
        ];
        $IdEgreso=$db->insert('inv_egresos',$Datos);*/

        foreach($productos as $Fila=>$elemento):
            $id_unidad=$db->query("SELECT id_unidad FROM inv_unidades WHERE unidad='{$unidad}'")->fetch_first();
            $Promociones=$db->query("SELECT id_promocion FROM inv_promociones WHERE producto_id='{$productos[$nro]}'")->fetch();
            if($Promociones):
                foreach($Promociones as $Fil=>$Promocion):
                    //Ordenes Detalles
                    $Datos=array(
                            'orden_salida_id'=>$IdOrdenSalida,
                            'precio_id'=>$precios[$Fila],
                            'cantidad'=>$cantidades[$Fila],
                            'unidad_id'=>$id_unidad,//Unidad por defecto
                            'promocion_id'=>$Promocion['id_promocion'],
                            'producto_id'=>$productos[$Fila]
                    );
                    $db->insert('inv_ordenes_detalles', $promo);
                endforeach;
            else:
                $Promocion=0;
                //Ordenes Detalles
                $Datos=array(
                        'orden_salida_id'=>$IdOrdenSalida,
                        'precio_id'=>$precios[$Fila],
                        'cantidad'=>$cantidades[$Fila],
                        'unidad_id'=>$id_unidad,//Unidad por defecto
                        'promocion_id'=>$Promocion,
                        'producto_id'=>$productos[$Fila]
                );
                $db->insert('inv_ordenes_detalles',$Datos);
            endif;
            //Almacenes
            $Almacenes=$db->query("SELECT id_materiales FROM inv_materiales WHERE id_producto='{$productos[$Fila]}'")->fetch_first();
            if($Almacenes && $cantidades[$Fila]!=$cantidades_pres[$Fila]):
                $Datos=array(
                        'id_materiales'=>$Almacenes['id_materiales'],
                        'tipo'=>'salida',
                        'cantidad'=>$cantidades_pres[$Fila],
                        'stock'=>'egreso',
                        'cliente_id'=>0,
                        'empleado_id'=>$IdPersona,
                        'fecha_control'=>'0000-00-00',
                        'estado'=>'pendiente'
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