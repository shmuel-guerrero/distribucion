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
    $pdf->Cell(0, 0, 'HISTORIAL IMPORTACIÓN', 0, 1, 'C', 0, '', 0);
    //INFORMACION IMPORTACION
    $Importacion=$db->query("SELECT i.fecha_inicio,i.fecha_final, fecha_factura, nro_factura,
                                    total,total_gastos,total_costo,i.descripcion,i.estado,nro_registros,almacen,nombres,paterno,materno
                            FROM inv_importacion i
                            LEFT JOIN sys_empleados ON i.empleado_id=sys_empleados.id_empleado
                            LEFT JOIN inv_almacenes ON i.almacen_id=inv_almacenes.id_almacen
                            WHERE id_importacion='{$IdImportacion}' LIMIT 1")->fetch_first();
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
            <td>{$Importacion['fecha_factura']}</td>
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
            <td>Total Gastos:</td>
            <td>".number_format((($Importacion['total_gastos'])) ,2 ,',','.' )." {$moneda}</td>
        </tr>
        <tr>
            <td>Total:</td>
            <td>".number_format(($Importacion['total']+$Importacion['total_gastos']) ,2 ,',','.')." {$moneda}</td>
            <td>Total Añadido:</td>
            <td>".number_format((($Importacion['total']+$Importacion['total_costo'])) ,2 ,',','.' )." {$moneda}</td>
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
    //DETALLE DE LOS GASTOS
    $Gastos='';
    $Consulta=$db->query("SELECT ig.id_importacion_gasto,ig.nombre,ig.codigo,ig.fecha,e.nombres,e.paterno,e.materno
                            FROM inv_importacion_gasto AS ig
                            LEFT JOIN sys_empleados AS e ON ig.empleado_id=e.id_empleado
                            WHERE ig.importacion_id='{$IdImportacion}'")->fetch();

    foreach($Consulta as $Fila=>$Dato):
        $fecha_iniciov=explode(" ",$Dato['fecha']);
        $Datofechav=date_decode($fecha_iniciov[0], $_institution['formato'])." ".$fecha_iniciov[1];
        
        $Gastos.="<tr>
                <th>{$Dato['nombre']}</th>
                <th>{$Dato['codigo']}</th>
                <th>{$Datofechav}</th>
                <th colspan=\"2\">{$Dato['nombres']} {$Dato['paterno']} {$Dato['materno']}</th>
            </tr>
            <tr class=\"cabecera\">
                <th>GASTO</th>
                <th>FACTURA</th>
                <th>COSTO AÑADIDO (%)</th>
                <th>IMPORTE {$moneda}</th>
                <th>COSTO AL PRODUCTO {$moneda}</th>
            </tr>";
        $IdImportacionGasto=$Dato['id_importacion_gasto'];
        $SubConsulta=$db->query("SELECT gasto,factura,costo_anadido,costo
                                FROM inv_importacion_gasto_detalle
                                WHERE importacion_gasto_id='{$IdImportacionGasto}'")->fetch();
        $Total1=0;
        $Total2=0;
        foreach($SubConsulta as $Nro=>$SubDato):
            $CostoAlProducto=($SubDato['costo_anadido']*0.01)*$SubDato['costo'];
            $CostoAlProducto=round($CostoAlProducto,2);
            $CAP = number_format($CostoAlProducto ,2 ,',','.');
            $Gastos.="<tr>
                    <td style=\"font-size:9px\">{$SubDato['gasto']}</td>
                    <td style=\"font-size:9px\">{$SubDato['factura']}</td>
                    <td style=\"font-size:9px\" align=\"right\">{$SubDato['costo_anadido']}</td>
                    <td style=\"font-size:9px\" align=\"right\">{$SubDato['costo']}</td>
                    <td style=\"font-size:9px\" align=\"right\">{$CAP}</td>
                </tr>";
            $Total1=$Total1+$SubDato['costo'];
            $Total2=$Total2+$CostoAlProducto;
        endforeach;
        $T1 = number_format($Total1 ,2,',','.');
        $T2 = number_format($Total2 ,2,',','.');
        $Gastos.="<tr>
                    <td colspan=\"3\"></td>
                    <td align=\"right\">{$T1}</td>
                    <td align=\"right\">{$T2}</td>
                </tr>";
    endforeach;
    //DEUDAS
    $Deudas='<tr class="cabecera">
                <td>NOMBRE</td>
                <td>CÓDIGO</td>
                <td>FECHA</td>
                <td>TOTAL</td>
                <td>PENDIENTE</td>
            </tr>';
    $Consulta=$db->query("SELECT ig.id_importacion_gasto,ig.nombre,ig.codigo,ig.fecha,ig.total,ig.total_gasto,ip.actual
                        FROM inv_importacion_gasto AS ig
                        LEFT JOIN(
                            SELECT importacion_gasto_id,SUM(monto)AS actual
                            FROM inv_importacion_pagos
                            GROUP BY importacion_gasto_id
                        )AS ip ON ip.importacion_gasto_id=ig.id_importacion_gasto
                        WHERE ig.importacion_id='{$IdImportacion}'")->fetch();

    foreach($Consulta as $Fila=>$Dato):
        $Pendiente=$Dato['total']-$Dato['actual'];
        $Total_format = number_format($Dato['total'] ,2 ,',','.');
        $Pendiente_format = number_format($Pendiente ,2 ,',','.');
        $fecha_iniciov=explode(" ",$Dato['fecha']);
        $Datofechav=date_decode($fecha_iniciov[0], $_institution['formato'])." ".$fecha_iniciov[1];
        $Deudas.="<tr>
                    <td style=\"font-size:9px\">{$Dato['nombre']}</td>
                    <td style=\"font-size:9px\">{$Dato['codigo']}</td>
                    <td style=\"font-size:9px\">".$Datofechav."</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Total_format}</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Pendiente_format}</td>
                </tr>";
    endforeach;
    //PAGOS
    $Pagos='<tr class="cabecera">
                <td width="16%" style=\"font-size:10px\">NOMBRE</td>
                <td width="10%" style=\"font-size:10px\">CÓDIGO</td>
                <td width="12%" style=\"font-size:10px\">FECHA</td>
                <td width="12%" style=\"font-size:10px\">MONTO</td>
                <td width="17%" style=\"font-size:10px\">FORMA PAGO</td>
                <td width="18%" style=\"font-size:10px\">COMPROBANTE</td>
                <td width="15%" style=\"font-size:10px\">EMPLEADO</td>
            </tr>';
    $Consulta=$db->query("SELECT ig.nombre,ig.codigo,ip.fecha,ip.monto,e.nombres,e.paterno,e.materno,ip.forma_pago, ip.comprobante
                        FROM inv_importacion_pagos AS ip
                        LEFT JOIN inv_importacion_gasto AS ig ON ip.importacion_gasto_id=ig.id_importacion_gasto
                        LEFT JOIN sys_empleados AS e ON ip.empleado_id=e.id_empleado
                        WHERE ig.importacion_id='{$IdImportacion}'")->fetch();
    foreach($Consulta as $Fila=>$Dato):
        $Monto_format = number_format($Dato['monto'] ,2 ,',','.');
        $fecha_iniciov=explode(" ",$Dato['fecha']);
        $Datofechav=date_decode($fecha_iniciov[0], $_institution['formato'])." ".$fecha_iniciov[1];
        $Pagos.="<tr>
                    <td style=\"font-size:9px\">{$Dato['nombre']}</td>
                    <td style=\"font-size:9px\">{$Dato['codigo']}</td>
                    <td style=\"font-size:9px\">".$Datofechav."</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Monto_format}</td>
                    <td style=\"font-size:9px\">{$Dato['forma_pago']}</td>
                    <td style=\"font-size:9px\">{$Dato['comprobante']}</td>
                    <td style=\"font-size:9px\">{$Dato['nombres']} {$Dato['paterno']} {$Dato['materno']}</td>
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
        /*$AuxCantidad=$Consulta[$i]['cantidad']/$TotalCantidad;
        $AuxCantidad=$AuxCantidad*$TotalImportacion;
        $AuxCantidad=$AuxCantidad/$Consulta[$i]['cantidad'];
        $AuxCantidad=$AuxCantidad+$Consulta[$i]['precio_salida'];
        $AuxCantidad=round($AuxCantidad,2);
        $Consulta[$i]=array_merge($Consulta[$i],['precio_venta'=>$AuxCantidad]);*/
    endfor;
    $TotalImportacion=0;
    foreach($Consulta as $Fila=>$Dato):
        $TotalImportacion=$TotalImportacion+($Dato['precio_ingreso']*$Dato['cantidad']);
    endforeach;
    $Productos='';
    foreach($Consulta as $Fila=>$Dato):
        $Porcentaje=$Dato['precio_ingreso']*$Dato['cantidad'];
        $Porcentaje=$Porcentaje/$TotalImportacion;
        $Porcentaje=$Porcentaje*100;
        $Porcentaje=round($Porcentaje,2);
        $totala = $Dato['precio_salida']*$Dato['cantidad'];
        $Totala_format = number_format($totala , 2, ',','.');
        
        $fecha_iniciov=explode(" ",$Dato['fechav']);
        $Datofechav=date_decode($fecha_iniciov[0], $_institution['formato'])." ".$fecha_iniciov[1];
    
        $Productos.="<tr>
                    <td style=\"font-size:9px\">{$Dato['codigo']}</td>
                    <td style=\"font-size:9px\">{$Dato['nombre_factura']}</td>
                    <td style=\"font-size:9px\">{$Dato['lote']}</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Dato['precio_ingreso']}</td>
                    <td style=\"font-size:9px\">{$Dato['cantidad']}</td>
                    <td style=\"font-size:9px\">".$Datofechav."</td>
                    <td style=\"font-size:9px\">{$Porcentaje} %</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Dato['precio_salida']}</td>
                    <td style=\"font-size:9px\" align=\"right\">{$Totala_format}</td>
                </tr>";
    endforeach;

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
                <td width="10%" style="font-size:10px">CÓDIGO</td>
                <td width="25%" style="font-size:10px">NOMBRE</td>
                <td width="7%" style="font-size:10px">LOTE</td>
                <td width="10%" style="font-size:10px">PRECIO COMPRA</td>
                <td width="8%" style="font-size:10px">CANT.</td>
                <td width="10%" style="font-size:10px">FECHA</td>
                <td width="10%" style="font-size:10px">PORCENTAJE</td>
                <td width="10%" style="font-size:10px">PRECIO REAL</td>
                <td width="10%" style="font-size:10px">TOTAL</td>
            </tr>
            $Productos
        </table>
        <div class="center">
            LISTADO DE GASTOS
        </div>
        <table border="1" cellpadding="5">
            $Gastos
        </table>
        <div class="center">
            LISTADO DE DEUDAS
        </div>
        <table border="1" cellpadding="5">
            $Deudas
        </table>
        <div class="center">
            LISTADO DE PAGOS
        </div>
        <table border="1" cellpadding="5">
            $Pagos
        </table>
EOD;
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    $pdf->Output('example_001.pdf', 'I');