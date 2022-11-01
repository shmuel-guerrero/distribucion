<?php
    if(!isset($params[0])):
        require_once not_found();
	    die;
    endif;
    
    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ?$moneda['sigla']: '';
    $IdImportacion=trim($params[0]);
    require_once libraries . '/tcpdf/tcpdf.php';
    //require_once libraries . '/tcpdf/tcpdf_barcodes_2d.php';
    require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setFontSubsetting(true);
    $pdf->AddPage();
    $pdf->SetFont('times', '', 20);
    $pdf->Cell(0, 0, 'DATOS INICIALES', 0, 1, 'C', 0, '', 0);
    //INFORMACION IMPORTACION
    
    $Importacion=$db->query("SELECT i.fecha_inicio,i.fecha_final, fecha_factura, nro_factura,
                                    total,total_gastos,total_costo,i.descripcion,
                                    i.estado,nro_registros,almacen,nombres,paterno,materno
                            FROM inv_importacion as i
                            LEFT JOIN sys_empleados ON i.empleado_id=sys_empleados.id_empleado
                            LEFT JOIN inv_almacenes ON i.almacen_id=inv_almacenes.id_almacen
                            WHERE id_importacion='{$IdImportacion}' 
                            LIMIT 1
                            ")->fetch_first();
    $pdf->SetFont('times', '', 11);
    $fecha_iniciov=explode(" ",$Importacion['fecha_inicio']);
    $fecha_inicio=date_decode($fecha_iniciov[0], $_institution['formato'])." ".$fecha_iniciov[1];
    $Importacion="
        <tr>
            <td>Fecha:</td>
            <td>{$fecha_inicio}</td>
            <td>Almacen:</td>
            <td>{$Importacion['almacen']}</td>
        </tr>
        <tr>
            <td>Nro de Factura:</td>
            <td>{$Importacion['nro_factura']}</td>
            <td>Fecha de Factura:</td>
            <td>".date_decode($Importacion['fecha_factura'], $_institution['formato'])."</td>
        </tr>
        <tr>
            <td>Empleado:</td>
            <td>{$Importacion['nombres']} {$Importacion['paterno']} {$Importacion['materno']}</td>
            <td>Nro Productos:</td>
            <td>{$Importacion['nro_registros']}</td>
        </tr>
        <tr>
            <td>Total Productos:</td>
            <td>".number_format(($Importacion['total']) ,2 ,',','.')." {$moneda}</td>
        </tr>
    ";
    //INFORMACION PRODUCTOS
    $Consulta=$db->query("SELECT precio_ingreso,cantidad,fechav,lote,codigo,nombre_factura
                            FROM tmp_ingreso_detalle
                            LEFT JOIN inv_productos ON inv_productos.id_producto=tmp_ingreso_detalle.producto_id
                            WHERE importacion_id='{$IdImportacion}'")->fetch();
    $Productos='';
    foreach($Consulta as $Fila=>$Dato):
        $Productos.="<tr>
                    <td style=\"font-size:9px\">{$Dato['codigo']}</td>
                    <td style=\"font-size:9px\">{$Dato['nombre']}</td>
                    <td style=\"font-size:9px\">{$Dato['lote']}</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Dato['precio_ingreso']}</td>
                    <td style=\"font-size:9px\">{$Dato['cantidad']}</td>
                    <td style=\"font-size:9px\">".date_decode($Dato['fechav'], $_institution['formato'])."</td>
                </tr>";
    endforeach;
    
    $ImportacionD=$db->query("SELECT total,total_gastos,total_costo
                            FROM inv_importacion
                            WHERE id_importacion='{$IdImportacion}'")->fetch_first();
    $total=$ImportacionD['total'];
    
    $total_gastos=$ImportacionD['total_gastos'];
    $total_costo=$ImportacionD['total_costo'];
    
    $Consulta=$db->query("SELECT p.id_producto,p.codigo,p.nombre_factura,id.fechav,id.lote,u.unidad,id.precio_ingreso,id.precio_salida,id.cantidad
                    FROM tmp_ingreso_detalle AS id
                    LEFT JOIN inv_productos AS p ON id.producto_id=p.id_producto
                    LEFT JOIN inv_unidades AS u ON id.unidad_id=u.id_unidad
                    WHERE id.importacion_id='{$IdImportacion}'")->fetch();
    
    $TotalImportacion=$db->query("SELECT SUM(total)AS total
                            FROM inv_importacion_gasto
                            WHERE importacion_id='{$IdImportacion}'")->fetch_first()['total'];
    
    $TotalCantidad=$db->query("SELECT SUM(cantidad)AS cantidad
                            FROM tmp_ingreso_detalle
                            WHERE importacion_id='{$IdImportacion}'")->fetch_first()['cantidad'];
    for($i=0;$i<count($Consulta);++$i):
        $Aux=$Consulta[$i]['precio_salida']*$Consulta[$i]['cantidad'];
        $Aux=$Aux/$total;
        $Aux=$Aux*$total_costo;
        $Aux=$Aux/$Consulta[$i]['cantidad'];
        $Aux=$Aux+$Consulta[$i]['precio_salida'];
        $Aux=round($Aux,2);
        $Consulta[$i]=array_merge($Consulta[$i],['precio_venta'=>$Aux]);
    endfor;
    
    $TotalImportacion=0;
    foreach($Consulta as $Fila=>$Dato):
        $TotalImportacion=$TotalImportacion+($Dato['precio_ingreso']*$Dato['cantidad']);
    endforeach;
    
    $Productos='';
    $totala=0;
    foreach($Consulta as $Fila=>$Dato):
        $totala = $totala + ($Dato['precio_ingreso']*$Dato['cantidad']);
        $Totala_format = number_format($totala , 2, ',','.');
        
        $fecha_iniciov=explode(" ",$Dato['fechav']);
        $Datofechav=date_decode($fecha_iniciov[0], $_institution['formato'])." ".$fecha_iniciov[1];
    
        $Productos.="<tr>
                    <td style=\"font-size:9px\">{$Dato['codigo']}</td>
                    <td style=\"font-size:9px\">{$Dato['nombre_factura']}</td>

                    <td style=\"font-size:9px\">{$Dato['lote']}</td>
                    <td style=\"font-size:9px\">".$Datofechav."</td>

                    <td style=\"font-size:9px\" align=\"right\">".number_format($Dato['precio_ingreso'] , 2, ',','.')."</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Dato['cantidad']}</td>
                    <td style=\"font-size:9px\" align=\"right\">".number_format( ($Dato['precio_ingreso']*$Dato['cantidad']) , 2, ',','.')."</td>
                </tr>";
    endforeach;
    
    $Productos.="<tr class=\"cabecera\">
                <td colspan=\"6\">TOTAL</td>
                <td style=\"font-size:9px\" align=\"right\">".number_format($totala , 2, ',','.')."</td>
            </tr>";

    $html = <<<EOD
        <style>
            .center{
                font-size:15px;
                text-align:center;
                padding:50px;
            }
            .cabecera{
                background-color: #DDDDDD;
            }
            .peque{
                font-size:10px;
            }
        </style>
        <hr>
        <table cellpadding="5">
            $Importacion
        </table>
        <div class="center">
            LISTADO DE PRODUCTOS
        </div>
        <table border="1" cellpadding="5">
            <tr class="cabecera">
                <td width="18%" style="font-size:10px">CÓDIGO</td>
                <td width="35%" style="font-size:10px">NOMBRE</td>
                <td width="7%" style="font-size:10px">LOTE</td>
                <td width="10%" style="font-size:10px">FECHA</td>
                <td width="10%" style="font-size:10px">PRECIO COMPRA</td>
                <td width="8%" style="font-size:10px">CANT.</td>
                <td width="12%" style="font-size:10px">TOTAL</td>
            </tr>
            $Productos
        </table>
EOD;
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    $pdf->Output('example_001.pdf', 'I');