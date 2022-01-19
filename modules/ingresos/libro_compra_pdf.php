<?php
    require_once libraries.'/tcpdf/tcpdf.php';

    $fecha_inicial = (isset($params[0])) ? $params[0] : date('Y-m-d');
    $fecha_final = (isset($params[1])) ? $params[1] : date('Y-m-d');
    // Define variables globales
    define('NOMBRE', escape($_institution['nombre']));
    define('IMGS', escape($_institution['imagen_encabezado']));
    define('PROPIETARIO', escape($_institution['propietario']));
    define('PIE', escape($_institution['pie_pagina']));
    define('FECHA', 'Fecha de Impresion: '.date(escape($_institution['formato'])) . ' ' . date('H:i:s'));
    define('CONSULTA',"Fecha Consulta: $fecha_inicial - $fecha_final");
    class MYPDF extends TCPDF{
        public function Header(){
            $this->Ln(5);
            $this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
            $this->Cell(0, 5, NOMBRE, 0, true, 'R', false, '', 0, false, 'T', 'M');
            $this->Cell(0, 5, PROPIETARIO, 0, true, 'R', false, '', 0, false, 'T', 'M');
            $this->Cell(0, 5, FECHA, 0, true, 'R', false, '', 0, false, 'T', 'M');
            $this->Cell(0, 5, CONSULTA, 'B', true, 'R', false, '', 0, false, 'T', 'M');
            $imagen = (IMGS != '') ? institucion . '/' . IMGS : imgs . '/empty.jpg';
            $this->Image($imagen, PDF_MARGIN_LEFT, 5, '', 14, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        public function Footer(){
            $this->SetY(-10);
            $this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
            $length = ($this->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT) / 2;
            $number = $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
            $this->Cell($length, 5, $number, 'T', false, 'L', false, '', 0, false, 'T', 'M');
            $this->Cell($length, 5, PIE, 'T', true, 'R', false, '', 0, false, 'T', 'M');
        }
    }
    // Instancia el documento PDF
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // Asigna la informacion al documento
    $pdf->SetCreator(name_autor);
    $pdf->SetAuthor(name_autor);
    $pdf->SetTitle($_institution['nombre']);
    $pdf->SetSubject($_institution['propietario']);
    $pdf->SetKeywords($_institution['sigla']);
    // Asignamos margenes
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    //$pdf->SetPageOrientation('L');
	// Adiciona la pagina
    //$pdf->AddPage();
    $pdf->AddPage('L');
    // Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, false, PDF_FONT_SIZE_MAIN);
    // Titulo del documento
	$pdf->Cell(0, 10, 'LIBRO DE COMPRAS', 0, true, 'C', false, '', 0, false, 'T', 'M');
	// Salto de linea
    $pdf->Ln(5);

    $Contenido='';
    // $Consulta=$db->query("SELECT vencimiento,cantidad,costo,nro_autorizacion,nro_control,factura,nombre_proveedor
    //                     FROM inv_ingresos_detalles
    //                     LEFT JOIN inv_ingresos ON inv_ingresos.id_ingreso=inv_ingresos_detalles.ingreso_id
    //                     WHERE factura!='' AND vencimiento BETWEEN '{$fecha_inicial}' AND '{$fecha_final}'")->fetch();
    $Consulta=$db->query("SELECT inv_ingresos.fecha_ingreso,inv_proveedores.proveedor, inv_proveedores.nit, inv_ingresos.monto_total
                        FROM inv_ingresos 
                        LEFT JOIN inv_proveedores ON inv_ingresos.nombre_proveedor = inv_proveedores.proveedor 
                        WHERE inv_ingresos.tipo = 'Compra'
                        AND inv_ingresos.fecha_ingreso BETWEEN '{$fecha_inicial}' AND '{$fecha_final}' 
                        GROUP BY inv_ingresos.fecha_ingreso, inv_ingresos.nombre_proveedor")->fetch();
    if($Consulta):
        $Total=0;$CreditoT=0;$ValidoT=0;
        foreach($Consulta as $Nro=>$Dato):
            $Fila=$Nro+1;
            // $SubTotal=$Dato['cantidad']*$Dato['costo'];
            // $Total=$Total+$SubTotal;
            // ADICIONADO
            $SubTotal = $Dato['monto_total'];
            $Total=$Total+$SubTotal;
            $Credito = round($SubTotal*0.13,2);
            $CreditoT = $CreditoT +  $Credito;
            $Valido = round(($SubTotal-($SubTotal*0.13)),2);
            $ValidoT = $ValidoT + $Valido;
            $nro_factura = ($Dato['factura']) ? $Dato['factura'] : 'S/N';
            $nro_autorizacion = ($Dato['nro_autorizacion']) ? $Dato['nro_autorizacion'] : 'S/N';
            $nro_control = ($Dato['nro_control']) ? $Dato['nro_control'] : 'S/N';
            $Contenido.=<<<EOD
                <tr>
                    <td style="text-align: center;">{$Fila}</td>
                    <td style="text-align: center;">{$Dato['fecha_ingreso']}</td>
                    <td style="text-align: center;">{$Dato['nit']}</td>
                    <td style="text-align: center;">{$Dato['proveedor']}</td>
                    <td style="text-align: center;">{$nro_factura}</td>
                    <td style="text-align: center;">{$nro_autorizacion}</td>
                    <td style="text-align: center;">{$nro_control}</td>
                    <td style="text-align: center;">{$SubTotal}</td>
                    <td style="text-align: center;">{$Credito}</td>
                    <td style="text-align: center;">{$Valido}</td>
                </tr>
EOD;
        endforeach;
        $Contenido.=<<<EOD
                <tr>
                    <td class="titulo" style="text-align: right;" colspan="7">TOTALES: </td>
                    <td class="titulo" style="text-align: center;">{$Total}</td>
                    <td class="titulo" style="text-align: center;">{$CreditoT}</td>
                    <td class="titulo" style="text-align: center;">{$ValidoT}</td>
                </tr>
EOD;
    endif;
    $Tabla=<<<EOD
    <style>
        table {
            border: 1px solid #444;
        }
        .titulo {
            background-color: #ccc;
            font-weight: bold;
            border: 1px solid #444;
        }
        td {
            border: 1px solid #444;
            font-size: 9px;
        }
	</style>
    <table cellpadding="5">
        <thead>
            <tr>
                <td class="titulo" style="text-align: center;">#</td>
                <td class="titulo" style="text-align: center;">FECHA</td>
                <td class="titulo" style="text-align: center;">NIT</td>
                <td class="titulo" style="text-align: center;">Nombre o Razon Social del Proveedor</td>
                <td class="titulo" style="text-align: center;">Nro. Factura</td>
                <td class="titulo" style="text-align: center;">Nro. Autorizacion</td>
                <td class="titulo" style="text-align: center;">Código Control</td>
                <td class="titulo" style="text-align: center;">Total Factura</td>
                <td class="titulo" style="text-align: center;">Crédito Fiscal </td>
                <td class="titulo" style="text-align: center;">Total Válido</td>
            </tr>
        </thead>
        <tbody>
            {$Contenido}
        </tbody>
    </table>
EOD;
    $pdf->writeHTML($Tabla, true, false, false, false, '');
    $nombre = 'compra_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($nombre, 'I');