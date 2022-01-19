<?php

// Obtiene el preventa
$id_egreso = (isset($params[0])) ? $params[0] : 0;

// Obtiene el preventa
$venta = $db->query("SELECT * FROM inv_egresos WHERE id_egreso = '{$id_egreso}'")->fetch_first();

// Verifica si es una peticion ajax y post
if ($venta && $venta['nro_autorizacion'] == '' && $venta['codigo_control'] == '') {

    // Importa la libreria para el codigo de control
    require_once libraries . '/controlcode-class/ControlCode.php';


    //Obtiene la fecha de hoy
    $hoy = date('Y-m-d');

    //Obtiene la dosificacion del periodo actual
    $dosificacion = $db->from('inv_dosificaciones')->where('fecha_registro <=', $hoy)->where('fecha_limite >=', $hoy)->where('activo', 'S')->fetch_first();

    if ($dosificacion && $venta) {

        // Obtiene los datos para el codigo de control
        $nro_autorizacion = $dosificacion['nro_autorizacion'];

        //se incrementa el numero de factura de la dosificacion
        $nro_factura = intval($dosificacion['nro_facturas']) + 1;

        $nit_cliente = ($venta['nit_ci']) ? $venta['nit_ci'] : 0;

        $fecha = date('Ymd');

        $total = round($venta['monto_total'], 0, PHP_ROUND_HALF_UP);

        $llave_dosificacion = base64_decode($dosificacion['llave_dosificacion']);

        // Genera el codigo de control
        $codigo_control = new ControlCode();
        $codigo_control = $codigo_control->generate($nro_autorizacion, $nro_factura, $nit_cliente, $fecha, $total, $llave_dosificacion);

        $datos_venta = array(
            'tipo' => 'Venta',
            'provisionado' => 'N',
            'descripcion' => 'Venta de productos con preventa',
            'nro_factura' => $nro_factura,
            'nro_autorizacion' => $nro_autorizacion,
            'codigo_control' => $codigo_control,
            'fecha_limite' => $dosificacion['fecha_limite'],
            'dosificacion_id' => $dosificacion['id_dosificacion'],
            'factura' => 'Factura'
        );


        //se crea backup de registros
        $verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $id_egreso, '', '', $_user['persona_id'], 'SI', 0, "Editado");

        //se convierte en factura
        $db->where('id_egreso', $id_egreso)->update('inv_egresos', $datos_venta);


        //se crea backup de registros
        $verifica = backup_registros($db, 'inv_egresos_detalles', 'egreso_id', $id_egreso, '', '', $_user['persona_id'], 'NO', $verifica_id, "Backup");

        historial_conversion($db, $id_egreso, 'Preventa', $id_egreso, 'Electronicas', $_user['persona_id'], "ConversionDirecta", $verifica_id, 'sinDatos');


        // Guarda en el historial
        $data = array(
            'fecha_proceso' => date("Y-m-d"),
            'hora_proceso' => date("H:i:s"),
            'proceso' => 'c',
            'nivel' => 'l',
            'direccion' => '?/operaciones/preventas_facturar_directo',
            'detalle' => 'Se convirtio preventa en factura con numero ' . $id_egreso,
            'usuario_id' => $_SESSION[user]['id_user']
        );
        $db->insert('sys_procesos', $data);

        // Verifica si fue el proforma eliminado
        if ($db->affected_rows) {
            // Instancia variable de notificacion
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Conversion a factura satisfactoria!',
                'message' => 'La preventa y su detalle fueron convertidos correctamente.'
            );
        }

        // Redirecciona a la pagina principal
        redirect('?/operaciones/preventas_listar');
    } else {

         // Instancia variable de notificacion
         $_SESSION[temporary] = array(
            'alert' => 'warning',
            'title' => 'Falla en conversión a factura satisfactoria!',
            'message' => 'Dosificación caducada o inexistente.'
        );
         // Redirecciona a la pagina principal
         redirect('?/operaciones/preventas_listar');
    }
} else {
      // Instancia variable de notificacion
      $_SESSION[temporary] = array(
        'alert' => 'danger',
        'title' => 'Conversión Erronea',
        'message' => 'La preventa no existe o tiene datos observados.'        
    );

    // Redirecciona a la pagina principal
    redirect('?/operaciones/preventas_listar');
}
