<?php
    $IdOrdenSalida=(isset($params[0]))?$params[0]:0;
    if(!$IdOrdenSalida):
        require_once bad_request();
        die();//Detiene el modulo en caso de ausencia de datos
    endif;
    require(libraries.'/tcpdf/tcpdf.php');
    require(libraries.'/numbertoletter-class/NumberToLetterConverter.php');
    $moneda=$db->from('inv_monedas')->where('oficial','S')->fetch_first();
    $moneda=($moneda)?'('.$moneda['sigla'].')':'';
    //Consulta Orden de Salida y Empleados
    //DATE_FORMAT(fecha_orden,'%d/%m/%Y')
    $Sentencia="SELECT os.id_orden,os.fecha_orden,os.hora_orden,
            e.nombres AS nombre,e.paterno AS paterno,e.materno AS materno,
            se.nombres AS nombred,se.paterno AS paternod,se.materno AS maternod
        FROM inv_ordenes_salidas AS os
        LEFT JOIN sys_empleados AS e ON os.empleado_id=e.id_empleado
        LEFT JOIN sys_empleados AS se ON  os.empleado_regitro_id=se.id_empleado
        WHERE os.id_orden='{$IdOrdenSalida}' LIMIT 1";
    $Consulta=$db->query($Sentencia)->fetch_first();
    if(!$Consulta):
        require_once bad_request();
        die();//Detiene el modulo en caso de ausencia de datos
    endif;
    //Consulta Detalle Orden de Salida
    $Sentencia="SELECT od.cantidad,u.unidad,p.nombre,p.descripcion,od.precio_id,c.categoria
        FROM inv_ordenes_detalles AS od
        LEFT JOIN inv_productos AS p ON od.producto_id=p.id_producto
        LEFT JOIN inv_categorias AS c ON p.categoria_id=c.id_categoria
        LEFT JOIN inv_unidades AS u ON od.unidad_id=u.id_unidad
        WHERE od.orden_salida_id='{$Consulta['id_orden']}'";
    $SubConsulta=$db->query($Sentencia)->fetch();
    //Inicio de TCPDF
    class MYPDF extends TCPDF {
        public function Header(){}
        public function Footer(){}
    }
    $pdf = new MYPDF('P', 'pt', array(612,935), true, 'UTF-8', false);
    $pdf->AddPage();
    $pdf->SetPageOrientation('P');
    // Asigna la informacion al documento
    $pdf->SetCreator(name_autor);
    $pdf->SetAuthor(name_autor);
    $pdf->SetTitle($_institution['nombre']);
    $pdf->SetSubject($_institution['propietario']);
    $pdf->SetKeywords($_institution['sigla']);
    // Asignamos margenes
    $pdf->SetMargins(30, 10 , 30);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(true, 55);
    $pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);
    // Titulo del documento
    $pdf->Cell(0, 5, '', 0, true, 'C', false, '', 0, false, 'T', 'M');
    // Establece la fuente del contenido
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);
    $Body='';
    $Total=0;
    foreach($SubConsulta as $Fila=>$Dato):
        $SubTotal=$Dato['precio_id']*$Dato['cantidad'];
$Body.=<<<EOD
    <tr height="2%">
        <td class="left-right bot" align="right">{$Dato['cantidad']}</td>
        <td class="left-right bot">{$Dato['unidad']}</td>
        <td class="left-right bot" align="left">{$Dato['nombre']}</td>
        <td class="left-right bot" align="right">{$Dato['descripcion']}</td>
        <td class="left-right bot" align="right">{$Dato['precio_id']}</td>
        <td class="left-right bot" align="right">{$SubTotal}</td>
    </tr>
EOD;
        $Total+=$SubTotal;
    endforeach;
    $Total=number_format($Total,2,'.','');
    $Conversor=new NumberToLetterConverter();
    $MontoTextual=explode('.',$Total);
    $MontoNumeral=$MontoTextual[0];
    $MontoDecimal=$MontoTextual[1];
    $MontoLiteral=strtoupper($Conversor->to_word($MontoNumeral));
$Body=<<<EOD
    <style>
        th {
            background-color: #66CDAA;
            font-weight: bold;
        }
        .left-right {
            border-left: 1px solid #444;
            border-right: 1px solid #444;
        }
        .none {
            border: 1px solid #fff;
        }
        .all {
            border: 1px solid #444;
        }
        .bot{
            border-top: 1px solid #444;
        }
    </style>
    <table cellpadding="1">
		<tr>
            <td width="15%" class="none"><b>DISTRIBUIDOR:</b></td>
            <td width="35%" class="none">{$Consulta['nombred']} {$Consulta['paternod']} {$Consulta['maternod']}</td>
            <td width="50%" class="none" align="center"><h1>HOJA DE ORDEN DE SALIDA</h1></td>
        </tr>
		<tr>
			<td width="15%" class="none"><b>VENDEDORES:</b></td>
            <td width="35%" class="none"></td>
            <td width="15%" class="none"><b>FECHA SALIDA:</b></td>
            <td width="35%" class="none">{$Consulta['fecha_orden']} {$Consulta['hora_orden']}</td>
		</tr>
	</table>
    <br><br>
    <table cellpadding="3" class="bor">
		<tr>
            <th width="10%" class="all" align="left">CANT.</th>
			<th width="10%" class="all" align="left">UNIDAD</th>
            <th width="30%" class="all" align="left">PRODUCTO</th>
            <th width="30%" class="all" align="left">DETALLE</th>
            <th width="10%" class="all" align="left">PRECIO</th>
			<th width="10%" class="all" align="right">IMPORTE {$moneda}</th>
		</tr>
		{$Body}
		<tr>
			<th class="all" align="left" colspan="5">IMPORTE TOTAL {$moneda}</th>
			<th class="all" align="right">{$Total}</th>
		</tr>
	</table>
    <p align="right">{$MontoLiteral} {$MontoDecimal}/100</p>
    <table cellpadding="1">
        <tr>
            <td width="50%" class="none" align="center" ></td>
            <td width="50%" class="none" align="center" ></td>
        </tr>
        <tr>
            <td width="50%" class="none" align="center" ></td>
            <td width="50%" class="none" align="center" ></td>
        </tr>
        <tr>
            <td width="50%" class="none" align="center" ></td>
            <td width="50%" class="none" align="center" ></td>
        </tr>
        <tr>
            <td width="50%" class="none" align="center" >------------------------------------------------------</td>
            <td width="50%" class="none" align="center" >------------------------------------------------------</td>
        </tr>
        <tr>
            <td width="50%" class="none" align="center" >Recibí conforme:<br>$valor_empleado</td>
            <td width="50%" class="none" align="center" >Entregué conforme:<br>Nombre:_________________________ </td>
        </tr>
        <tr>
            <td width="50%" class="none" align="center" ></td>
            <td width="50%" class="none" align="center" ></td>
        </tr>
        <tr>
            <td width="50%" class="none" align="center" ></td>
            <td width="50%" class="none" align="center" ></td>
        </tr>
    </table>
EOD;
    $pdf->writeHTML($Body,true,false,false,false,'');
    // Genera el nombre del archivo
    $nombre='orden_salida'.$id_orden.'_'.date('Y-m-d_H-i-s').'.pdf';
    // Cierra y devuelve el fichero pdf
    $pdf->Output($nombre,'I');