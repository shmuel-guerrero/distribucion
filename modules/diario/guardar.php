<?php
/**
 * Created by PhpStorm.
 * User: AMQ
 * Date: 12/02/2019
 * Time: 16:31
 */

function convercion($mon, $db)
{
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
    $res = $mon * $tipo['valor'];
    return round($res, 2);
}

if (is_post()) {
    $fe = $_POST['fecha'];
    $aj = $_POST['ajuste'];
    $gl = trim($_POST['glosa']);
    $nro = $_POST['nro'];
    $mone = $db->select('*')->from('con_tipo_moneda')->where('id_moneda', 2)->fetch_first();

    if (isset($_POST['id'])) {
        //edita los datos del asiento
        $idd = $_POST['id'];
        $data = array(
            'tipo' => $aj,
            'glosa' => $gl,
            'fecha' => $fe,
        );
        $ide = $db->where('codigo', $idd)->update('con_comprobante', $data);

        $db->delete()->from('con_asiento')->where('comprobante', $idd)->execute();
    } else {
        //guarda los datos del asiento
        $comp = $db->select('codigo')->from('con_comprobante')->order_by('codigo', 'desc')->fetch_first();

        $data = array(
            'codigo' => $comp['codigo'] + 1,
            'tipo' => $aj,
            'glosa' => $gl,
            'fecha' => $fe,
            'dolar' => $mone['valor']
        );
        $idd = $db->insert('con_comprobante', $data);
    }

    for ($i = 1; $i <= $nro; $i++) {
        $a = $_POST['sele_' . $i];
        $b = $_POST['debe_' . $i];
        $c = $_POST['haber_' . $i];
        if ($_POST['fact_' . $i] != 0 || $_POST['fact_' . $i] != null) {
            $d = $_POST['fact_' . $i];
        } else {
            $d = 0;
        }

        if ($b == 0 && $c == 0) {

        } else {
            $b = convercion($b, $db);
            $c = convercion($c, $db);
            $data2 = array(
                'cuenta' => $a,
                'debe' => $b,
                'haber' => $c,
                'factura' => $d,
                'comprobante' => $idd
            );
            $db->insert('con_asiento', $data2);
        }

    }
    if ($aj == 2) {
        echo $an = $_POST['an'];
        echo $bn = $_POST['bn'];
        $act = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>='" . $an . "' AND a.comprobante<'" . $bn . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

        $data = array(
            'tipo' => 1,
            'glosa' => $gl,
            'fecha' => $fe,
            'dolar' => $mone['valor']
        );
        $ide = $db->insert('con_comprobante', $data);
        foreach ($act as $ac) {
            if ($ac['nodo'] == 1 || $ac['nodo'] == 2 || $ac['nodo'] == 3) {
                //if($ac['SUM(a.debe)']-$ac['SUM(a.haber)']!=0){
                if ($ac['SUM(a.debe)'] > $ac['SUM(a.haber)']) {
                    $sum = $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                    $data2 = array(
                        'cuenta' => $ac['n_plan'],
                        'debe' => $sum,
                        'haber' => 0,
                        'comprobante' => $ide,
                    );
                    $db->insert('con_asiento', $data2);
                } else {
                    $sum = $ac['SUM(a.haber)'] - $ac['SUM(a.debe)'];
                    $data2 = array(
                        'cuenta' => $ac['n_plan'],
                        'debe' => 0,
                        'haber' => $sum,
                        'comprobante' => $ide,
                    );
                    $db->insert('con_asiento', $data2);
                }
                //}
            }
        }

    }
    redirect('?/diario/listar');
} else {
    // Error 404
    require_once not_found();
    exit;
}
