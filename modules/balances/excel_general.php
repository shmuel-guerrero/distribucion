<?php
// Importa la libreria para generar el reporte
require_once libraries . '/phpexcel/PHPExcel.php';

function convercion($mon, $tip, $db)
{
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
    if ($tip > 1) {
        $tipob = $db->select('*')->from('con_tipo_moneda')->where('id_moneda', $tip)->fetch_first();
        $mon = $mon / $tipob['valor'];
    }

    $res = $mon / $tipo['valor'];

    return number_format($res, 2);
}

function sumar_vec($vec, $sum1)
{
    $vct = 0;
    foreach ($vec as $vc) {
        $vct = $vct + $vc[$sum1];
    }
    return $vct;
}

function fechaCastellano($fecha)
{
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    return $nombredia . " " . $numeroDia . " de " . $nombreMes . " de " . $anio;
}

$cuenta = $db->select('con_plan.*')->from('con_asiento')->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->fetch();
$g = 0;
$comp1 = $db->select('*')->from('con_comprobante')->where('tipo', 1)->order_by('fecha', 'desc')->fetch();
$comp2 = $db->select('*')->from('con_comprobante')->where('tipo', 2)->order_by('fecha', 'desc')->fetch();

if (sizeof($params) > 0) {
    $a = $params[0];
    $b = $params[1];
} else {
    if (isset($comp1[0]['codigo'])) {
        $a = $comp1[0]['codigo'];
        $b = 100000;
    } else {
        $a = 0;
        $b = 100000;
    }
}
$where = array(
    'tipo' => 1,
    'codigo>=' => $a,
    'codigo<=' => $b
);
$comp = $db->select('*')->from('con_comprobante')->where($where)->fetch_first();
$date = $comp['fecha'];
$where2 = array(
    'tipo' => 2,
    'codigo>=' => $a,
    'codigo<=' => $b
);
$comp2 = $db->select('*')->from('con_comprobante')->where($where2)->fetch_first();
$date2 = $comp2['fecha'];

$mon = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();

$datos = $db->select('*')->from('con_datos_empresa')->where('id_empresa', 1)->fetch_first();

$institucion = $db->select('*')->from('sys_instituciones')->fetch_first();

$objPHPExcel = new PHPExcel();
// Establecer propiedades

$objPHPExcel->getProperties()
    ->setCreator($institucion['nombre'])
    ->setLastModifiedBy($institucion['lema'])
    ->setTitle("Balance general")
    ->setSubject("Balance general")
    ->setDescription("Balance general")
    ->setKeywords("Excel Office 2007 php")
    ->setCategory("Balance");

// Define el titulo del documento
$objPHPExcel->getActiveSheet()->mergeCells('A2:D2')->setCellValue('A2', 'Balance general');
$objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle("A2:D3")->getFont()->setBold(true)->setName('Verdana')->setSize(12)->getColor()->setRGB('6F6F6F');
$fec = fechaCastellano($date);
if ($b > 1000) {
    $fec = $fec . ' a la fecha actual';
} else {
    $fec = $fec . ' al ' . fechaCastellano($date2);
}
$objPHPExcel->getActiveSheet()->mergeCells('A3:D3')->setCellValue('A3', $fec);
$objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->getFill()->getStartColor()->setARGB('000000');

//ancho de la celda
$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);
$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(30);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5', 'Cuenta')->mergeCells('B5:C5')->setCellValue('B5', 'Detalle')->setCellValue('D5', 'Totales');

$t1 = 0;
$t2 = 0;
$n = 6;

$estilo = array(
    'borders' => array(
        'outline' => array(
            'style' => PHPExcel_Style_Border::BORDER_DOUBLE
        )
    )
);

$aux2 = $db->select('*')->from('con_cuenta')->where('id_cuenta<', 4)->fetch();
foreach ($aux2 as $au2) {
    $cue = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '" . $au2['id_cuenta'] . "%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
    if ($au2['id_cuenta'] == 1) {
        $totc = sumar_vec($cue, 'SUM(a.debe)') - sumar_vec($cue, 'SUM(a.haber)');
        $t1 = $t1 + $totc;
    } else {
        $totc = sumar_vec($cue, 'SUM(a.haber)') - sumar_vec($cue, 'SUM(a.debe)');
        $t2 = $t2 + $totc;
    }
    $objPHPExcel->getActiveSheet()->getStyle('A' . $n . ':D' . $n)->applyFromArray($estilo);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $n, $au2['n_cuenta'])->setCellValue('B' . $n, $au2['cuenta'])->setCellValue('D' . $n, convercion($totc, 1, $db));

    $aux1 = $db->select('*')->from('con_plan')->where('tipo', '2')->where('nodo', $au2['id_cuenta'])->fetch();
    $n = $n + 1;

    foreach ($aux1 as $aux) {
        $act = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '" . $aux['n_plan'] . "%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
        if ($aux['nodo'] == 1) {
            $tot = sumar_vec($act, 'SUM(a.debe)') - sumar_vec($act, 'SUM(a.haber)');
        } else {
            $tot = sumar_vec($act, 'SUM(a.haber)') - sumar_vec($act, 'SUM(a.debe)');
        }

        $auxn = str_replace('.', '', $aux['n_plan']);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $n, $auxn)->setCellValue('B' . $n, $aux['plan_cuenta'])->setCellValue('D' . $n, convercion($tot, 1, $db));
        $n = $n + 1;

        foreach ($act as $ac) {
            if ($aux['nodo'] == 1) {
                $acd = $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
            } else {
                $acd = $ac['SUM(a.haber)'] - $ac['SUM(a.debe)'];
            };
            if ($acd != 0) {
                $e = convercion($acd, 1, $db);
                $acn = str_replace('.', '', $ac['n_plan']);

                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $n, $acn)->setCellValue('B' . $n, $ac['plan_cuenta'])->setCellValue('D' . $n, $e);
                $n = $n + 1;

            }
        }

    }
    if ($au2['id_cuenta'] == 1) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $n, $au2['n_cuenta'])->setCellValue('B' . $n, 'TOTAL '.$au2['cuenta'])->setCellValue('D' . $n, convercion($totc, 1, $db));
        $n = $n + 1;
    }
}
if ($t1 != $t2) {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $n, 'UTILIDAD NETA')->setCellValue('D' . $n, convercion($t1-$t2,1,$db));
    $n = $n + 1;
    $t2 = $t2 + $t1 - $t2;
}
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $n, 'TOTAL PASIVO Y ' . $au2['cuenta'])->setCellValue('D' . $n, convercion($t2, 1, $db));
$n = $n + 1;


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="general' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
