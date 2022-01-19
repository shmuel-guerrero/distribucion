<?php
    header_remove();
    $fecha_inicial = (isset($params[0])) ? $params[0] : date('Y-m-d');
    $fecha_final = (isset($params[1])) ? $params[1] : date('Y-m-d');

    require libraries.'/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\IOFactory;


    $spread = new Spreadsheet();
    $spread->getProperties()
            ->setCreator('Checkcode')
            ->setLastModifiedBy('Cehckcode')
            ->setTitle('Excel creado con PhpSpreadSheet')
            ->setSubject('Excel de prueba')
            ->setDescription('Excel generado como prueba')
            ->setKeywords('PHPSpreadsheet')
            ->setCategory('Categoría de prueba');

    $sheet = $spread->getActiveSheet();
    $sheet  ->setCellValue('A1', 1)
            ->setCellValue('A2', 2)
            ->setCellValue('A3','=SUM(A1:A2)');

    /*$Consulta=$db->query("SELECT vencimiento,cantidad,costo,nro_autorizacion,nro_control,factura,nombre_proveedor
            FROM inv_ingresos_detalles
            LEFT JOIN inv_ingresos ON inv_ingresos.id_ingreso=inv_ingresos_detalles.ingreso_id
            WHERE factura!='' AND vencimiento BETWEEN '{$fecha_inicial}' AND '{$fecha_final}'")->fetch();

    if($Consulta):
        $Total=0;
        $Row=1;
        foreach($Consulta as $Nro=>$Dato):
            ++$Row;
            $SubTotal=$Dato['cantidad']*$Dato['costo'];
            $Total=$Total+$SubTotal;

            $sheet  ->setCellValue('A'.$Row, $Row)
                    ->setCellValue('B'.$Row, $Dato['vencimiento'])
                    ->setCellValue('C'.$Row, $Dato['nombre_proveedor'])
                    ->setCellValue('D'.$Row, $Dato['nombre_proveedor'])
                    ->setCellValue('E'.$Row, $Dato['factura'])
                    ->setCellValue('F'.$Row, $Dato['nro_autorizacion'])
                    ->setCellValue('G'.$Row, $Dato['nro_control'])
                    ->setCellValue('H'.$Row, $SubTotal)
                    ->setCellValue('I'.$Row, 0)
                    ->setCellValue('J'.$Row, 0)
                    ->setCellValue('K'.$Row, 0)
                    ->setCellValue('L'.$Row, 0);
        endforeach;
        $Ultimo=$Row-1;
        $sheet  ->setCellValue('H'.$Row, "=SUM(H1:H{$Ultimo})")
                ->setCellValue('I'.$Row, "=SUM(I1:I{$Ultimo})")
                ->setCellValue('J'.$Row, "=SUM(J1:J{$Ultimo})")
                ->setCellValue('K'.$Row, "=SUM(K1:K{$Ultimo})")
                ->setCellValue('L'.$Row, "=SUM(L1:L{$Ultimo})");
    endif;*/

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="libro_compra.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spread, 'Xlsx');
    $writer->save('php://output');
    exit;