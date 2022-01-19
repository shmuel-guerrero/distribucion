<?php
/**
 * Created by PhpStorm.
 * User: AMQ
 * Date: 12/02/2019
 * Time: 16:31
 */
if (is_post()) {
    $aa=$_POST['ida'];
    $fecha = $_POST['fecha'];
    $nit = $_POST['nit'];
    $razon = $_POST['razon'];
    $fac = $_POST['fac'];
    $auto = $_POST['auto'];
    $cod = $_POST['cod'];
    $tot = $_POST['tot'];
    $imp = $_POST['imp'];
    $ice = $_POST['ICE'];
    $tipo = $_POST['tipo'];

    $data = array(
        'fecha_f' => $fecha,
        'nit_f' => $nit,
        'nombre_f' => $razon,
        'nro_f' => $fac,
        'autorizacion_f' => $auto,
        'codigo_f' => $cod,
        'total_f' => $tot,
        'importes_f' => $imp,
        'ice_f' => $ice,
        'tipo_f' => $tipo
    );

    $id = $db->insert('con_factura', $data);

    echo "<script>
opener.document.form1.fact_".$aa.".value = ".$id.";
opener.document.form1.facto_".$aa.".value = ".$id.";
close();</script>";

}else {
    // Error 404
    require_once not_found();
    exit;
}

