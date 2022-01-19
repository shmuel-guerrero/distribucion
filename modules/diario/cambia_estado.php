<?php
/**
 * Created by PhpStorm.
 * User: AMQ
 * Date: 26/02/2019
 * Time: 11:36
 */
echo $cod=trim($_REQUEST['cod']);
echo $est=trim($_REQUEST['est']);
if($est==1){
    $data = array(
        'estado' => 1
    );
}else{
    $data = array(
        'estado' => 0
    );
}
$db->where('codigo',$cod)->update('con_comprobante', $data);
