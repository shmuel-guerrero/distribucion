<?php
/**
 * Created by PhpStorm.
 * User: AMQ
 * Date: 12/02/2019
 * Time: 16:31
 */


$id = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el almacén
$cuenta = $db->select('*')->from('con_plan')->where('id_plan',$id)->fetch_first();

if ($cuenta) {
    // Elimina el almacén
    $db->delete()->from('con_plan')->where('id_plan', $id)->limit(1)->execute();

    // Verifica si fue el almacén eliminado
    if ($db->affected_rows) {
        // Instancia variable de notificacion
        $_SESSION[TEMPORARY] = array(
            'alert' => 'success',
            'title' => 'Eliminación satisfactoria!',
            'message' => 'El registro fue eliminado correctamente.'
        );
    }

    // Redirecciona a la pagina principal
    redirect('?/cuentas/mostrar');
} else {
    // Error 404
    require_once not_found();
    exit;
}
