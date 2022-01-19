<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_user']) && isset($_POST['haberpor']) && isset($_POST['debepor']) && isset($_POST['haberasi']) && isset($_POST['debeasi'])) {
		// Importa la libreria para convertir el numero a letra

		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la nota
		$nom_asi = trim($_POST['id_user']);
		$porcentaje_haber = (isset($_POST['haberpor'])) ? $_POST['haberpor'] : array();
		$porcentaje_debe = (isset($_POST['debepor'])) ? $_POST['debepor'] : array();
		$cuentas_haber = (isset($_POST['haberasi'])) ? $_POST['haberasi'] : array();
		$cuentas_debe = (isset($_POST['debeasi'])) ? $_POST['debeasi'] : array();

        if(isset($_POST['id_auto'])){
            $id_aut = $_POST['id_auto'];
            // Instancia la nota
            $automatico = array(
                'titulo_automatico' => $nom_asi,
                'detalle_automatico' => '',
                'estado' => 'no'
            );

            // Guarda la informacion de debe
            $aut_id = $id_aut;
            $db->where('id_automatico',$id_aut)->update('con_asientos_automaticos', $automatico);
            $db->delete()->from('con_detalles_automaticos')->where('automatico_id', $id_aut)->execute();
        }else{
            // Instancia la nota
            $automatico = array(
                'titulo_automatico' => $nom_asi,
                'detalle_automatico' => '',
                'estado' => 'no'
            );
            // Guarda la informacion de debe
            $aut_id = $db->insert('con_asientos_automaticos', $automatico);
        }

        $d = 0;
		// Recorre los productos
        foreach ($cuentas_debe as $nro => $cuentas_debes) {
            // Forma el detalle
            $detalle = array(
                'automatico_id' => $aut_id,
                'plan_id' => $cuentas_debe[$d],
                'porcentaje' => $porcentaje_debe[$d],
                'tipo' => 1,
                'id_detalle'=>0
            );
            $d = $d + 1;
            // Guarda la informacion
            $db->insert('con_detalles_automaticos', $detalle);
        }
        $h = 0;
        // Guarda la informacion de debe
        foreach ($cuentas_haber as $nro => $cuentas_habe) {
            // Forma el detalle
            $detalle = array(
                'automatico_id' => $aut_id,
                'plan_id' => $cuentas_haber[$h],
                'porcentaje' => $porcentaje_haber[$h],
                'tipo' => 2,
                'id_detalle'=>0
            );
            $h = $h + 1;
            // Guarda la informacion
            $db->insert('con_detalles_automaticos', $detalle);
        }

		// Envia respuesta
		echo json_encode($automatico);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>