<?php
// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = date('Y-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene las ventas
$IdUsuario=$_user['id_user'];
$ventas = $db->query("SELECT e.fecha_egreso, e.hora_egreso, e.descripcion, e.nro_factura as nro_movimiento, 
                        e.nombre_cliente, e.nit_ci, cl.id_cliente as codigo, cl.direccion,
                        e.tipo, d.*, d.unidad_id as unidad_otra, e.monto_total, p.nombre, ca.categoria, 
						p.descripcion AS descripcionp, concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado, d.producto_id, em.cargo
                    FROM inv_egresos_detalles d
                    INNER JOIN inv_egresos e ON d.egreso_id = e.id_egreso
                    LEFT JOIN inv_clientes cl ON e.cliente_id = cl.id_cliente
                                    
                LEFT JOIN inv_productos p ON d.producto_id = p.id_producto
                LEFT JOIN inv_categorias ca ON p.categoria_id = ca.id_categoria
                LEFT JOIN sys_empleados em ON e.empleado_id = em.id_empleado
                WHERE e.tipo = 'Venta' AND e.fecha_egreso = '$fecha_inicial' AND e.fecha_egreso<='$fecha_final'
                ORDER BY hora_egreso
                ")->fetch();

//                WHERE e.tipo = 'Venta' AND e.fecha_egreso between '$fecha_inicial' AND '$fecha_final'


$fecha_inicial_return=date("d-m-Y",strtotime($fecha_inicial."+ 1 days")); 
$fecha_final_return=date("d-m-Y",strtotime($fecha_final."+ 0 days")); 

$fecha_inicial_return222=date("Y-m-d",strtotime($fecha_inicial."+ 1 days")); 

$fecha_inicialx = strtotime( $fecha_inicial_return222 );
$fecha_finalx = strtotime( $fecha_final );
    
if($fecha_inicialx <= $fecha_finalx) {
	$strReturn=$fecha_inicial_return222.'|';
}
else{
	$strReturn='0000-00-00|';
}
 
foreach ($ventas as $nro => $venta) { 
	$cantidad = escape($venta['cantidad'] / cantidad_unidad($db, $venta['producto_id'], $venta['unidad_otra']));
    $precio = escape($venta['precio']);
    $importe = $cantidad * $precio;
    $total = $total + $importe;
    

	$Aux1=escape(date_decode($venta['fecha_egreso'],$_institution['formato']));
	$Aux2=$venta['tipo'].' ';
		if($venta['codigo_control']!=''):
		    $Aux.='electrónica';
		elseif($venta['nro_autorizacion']!=''):
		    $Aux.='manual';
		elseif($venta['estadoe']==0):
		    $Aux.='nota remisión';
		elseif($venta['estadoe']>1):
		    $Aux.='preventa';
		endif;
	if($venta['cargo']==1){ $Aux3=$_institution['empresa1']; }else{ $Aux3=$_institution['empresa2']; }

	$strReturn.='<tr>
				<td class="text-nowrap">'.$Aux1.' <br><small class="text-success">'.$venta['hora_egreso'].'</small></td>
				<td class="text-nowrap">'.$venta['codigo'].'</td>
				<td class="text-nowrap">'.$venta['nombre_cliente'].'</td>				
				<td class="text-nowrap">'.$venta['nit_ci'].'</td>
				<td style="font-size: 6 px;">'.$venta['direccion'].'</td>			
				<td class="text-nowrap">'.$Aux2.'</td>
				<td class="text-nowrap">'.$venta['nro_movimiento'].'</td>
				<td class="text-nowrap">'.$venta['nombre'].'</td>
				<td class="text-nowrap">'.$venta['descripcionp'].'</td>
				<td class="text-nowrap">'.$venta['categoria'].'</td>
				
				<td class="text-nowrap">'.escape($cantidad . ' ' . nombre_unidad($db, $venta['unidad_otra']) ).'</td>
				<td class="text-nowrap">'.$precio.'</td>
				<td class="text-nowrap">'.$venta['descuento'].'</td>
				<td class="text-nowrap">'.$importe.'</td>
				
				<td class="text-nowrap">'.$venta['empleado'].'</td>
				<td class="text-nowrap">'.$Aux3.'</td>';
}

echo $strReturn;
?>
