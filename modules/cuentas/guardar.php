<?php
/**
 * Created by PhpStorm.
 * User: AMQ
 * Date: 12/02/2019
 * Time: 16:31
 */
if (is_post()) {
    $pad=$_POST['padre1'];
    $ncuen=$_POST['n_cuenta'];
    $tip=explode('.', $ncuen);
    $tip=count(array_filter($tip, "strlen"));
    $cuen=$_POST['cuenta'];
    $fe=date('Y-m-d');
    if(!isset($_POST['ajuste']) || $_POST['ajuste']==0){
        $act = 0;
    }else{
        $act = $_POST['ajuste'];
    }
    if(!isset($_POST['virtual']) || $_POST['virtual']==0){
        $act = 0;
    }else{
        $act = $_POST['virtual'];
    }
    if(!isset($_POST['efectivo']) || $_POST['efectivo']==0){
        $act = 0;
    }else{
        $act = $_POST['efectivo'];
    }
    if($_POST['utilidad']!=0){
        $act = $_POST['utilidad'];
    }
    $data = array(
        'n_plan' => $ncuen,
        'plan_cuenta' => $cuen,
        'estado' => 1,
        'nodo' => $pad,
        'tipo' => $tip,
        'fecha' => $fe,
        'actividadc' => $act
    );
    if(isset($_POST['id_plan']) && $_POST['id_plan']>0){
        $a=$_POST['id_plan'];
        $id = $db->where('id_plan',$a)->update('con_plan', $data);
    }else{
        $db->insert('con_plan', $data);
    }
        redirect('?/cuentas/mostrar');
} else {
    // Error 404
    require_once not_found();
    exit;
}
