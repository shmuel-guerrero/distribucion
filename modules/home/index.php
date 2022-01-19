<?php
$dat = date("Y-m-d");
$houur = date("H:i:s");

$verifica = false;
if($_user['rol'] == 'Superusuario' || $_user['rol'] == 'superusuario' || $_user['rol'] == 'Administrador' || $_user['rol'] == 'administrador' || $_user['rol'] == 'Almacenero' || $_user['rol'] == 'almacenero' || $_user['rol'] == 'Despacho' || $_user['rol'] == 'despacho') {
    $verifica = true;
}

if($verifica == true) {
    function dif_Hour($h1, $h2)
    {
    	$v1 = explode(":", $h1);
    	$v2 = explode(":", $h2);
    
    	if (count($v1) > 2 && count($v2) > 2) {
    		$minuto = $v2[1] - $v1[1];
    		$hora = $v2[0] - $v1[0];
    		if ($minuto < 0) {
    			$minuto = $minuto + 60;
    			$hora = $hora - 1;
    		}
    		if ($hora != 0) {
    			if ($hora == 1) {
    				return "Hace " . $hora . " hora";
    			} else {
    				return "Hace " . $hora . " horas";
    			}
    		} else {
    			if ($minuto == 0) {
    				return "En este momento";
    			} else {
    				if ($minuto == 1) {
    					return "Hace " . $minuto . " minuto";
    				} else {
    					return "Hace " . $minuto . " minutos";
    				}
    			}
    		}
    		return $hora . ":" . $minuto;
    	} else {
    		return " Hace más de 1 dia";
    	}
    }
    
    //calculo ventas y minutos
    $ventas_hoy =  $db->query("SELECT count(id_egreso) countt, MAX(hora_egreso) hourr
    	FROM inv_egresos
    	WHERE fecha_egreso='$dat' AND tipo='Venta' AND anulado != 3")->fetch_first();
    $clientes_hoy =  $db->query("SELECT count(id_egreso) countt, MAX(hora_egreso) hourr
    	FROM inv_egresos
    	WHERE fecha_egreso='$dat' AND anulado != 3 AND nit_ci NOT IN (
    	SELECT nit_ci
    	FROM inv_egresos
    	WHERE fecha_egreso<'$dat' AND anulado != 3)")->fetch_first();
    
    $facturas_hoy =  $db->query("SELECT count(id_egreso) countt
    	FROM inv_egresos
    	WHERE tipo='Venta' AND codigo_control!='' AND provisionado='N' AND anulado != 3
    	ORDER BY fecha_egreso DESC, hora_egreso DESC")->fetch_first();
    
    $facturas_hoy2 =  $db->query("SELECT hora_egreso as hourr
    	FROM inv_egresos
    	WHERE tipo='venta' AND codigo_control!='' AND provisionado='N' AND fecha_egreso='$dat' AND anulado != 3
    	ORDER BY hora_egreso DESC
    	LIMIT 1 ")->fetch();
    
    $compras_hoy =  $db->query("SELECT count(id_ingreso) countt, MAX(hora_ingreso) hourr
    	FROM inv_ingresos
    	WHERE fecha_ingreso='$dat' AND tipo='compra' 
    	ORDER BY fecha_ingreso DESC, hora_ingreso DESC")->fetch_first();
    	$fecha_actual = date("d-m-Y");
    	$DD[0] = date("Y-m-d", strtotime($fecha_actual . "- 0 days"));
    	$DD[1] = date("Y-m-d", strtotime($fecha_actual . "- 1 days"));
    	$DD[2] = date("Y-m-d", strtotime($fecha_actual . "- 2 days"));
    	$DD[3] = date("Y-m-d", strtotime($fecha_actual . "- 3 days"));
    	$DD[4] = date("Y-m-d", strtotime($fecha_actual . "- 4 days"));
    	$DD[5] = date("Y-m-d", strtotime($fecha_actual . "- 5 days"));
    	$DD[6] = date("Y-m-d", strtotime($fecha_actual . "- 6 days"));
    	$DD[7] = date("Y-m-d", strtotime($fecha_actual . "- 7 days"));
    	
    	$DD2[0] = date("d/m/Y", strtotime($fecha_actual . "- 0 days"));
    	$DD2[1] = date("d/m/Y", strtotime($fecha_actual . "- 1 days"));
    	$DD2[2] = date("d/m/Y", strtotime($fecha_actual . "- 2 days"));
    	$DD2[3] = date("d/m/Y", strtotime($fecha_actual . "- 3 days"));
    	$DD2[4] = date("d/m/Y", strtotime($fecha_actual . "- 4 days"));
    	$DD2[5] = date("d/m/Y", strtotime($fecha_actual . "- 5 days"));
    	$DD2[6] = date("d/m/Y", strtotime($fecha_actual . "- 6 days"));
    	$DD2[7] = date("d/m/Y", strtotime($fecha_actual . "- 7 days"));
    	$productos =  $db->query("SELECT *, SUM(d.cantidad*d.precio) as costomax
    	from inv_productos p
    	LEFT join inv_egresos_detalles d ON p.id_producto=d.producto_id
    	LEFT join inv_egresos e ON e.id_egreso=d.egreso_id
    	LEFT join inv_asignaciones a ON a.id_asignacion=d.asignacion_id AND a.visible = 's'
    	LEFT join inv_unidades u ON u.id_unidad=a.unidad_id
    	WHERE e.tipo = 'Venta' AND e.anulado != 3 AND a.visible = 's'
    	group by p.id_producto
    	order by costomax DESC
    	limit 5")->fetch();
    
    $empleados =  $db->query("SELECT *, SUM(i.monto_total*(1-i.descuento_porcentaje/100) ) as costomax
    	from sys_empleados e
    	INNER join inv_egresos i ON empleado_id=id_empleado
    	INNER join inv_egresos_detalles d ON i.id_egreso=d.egreso_id
    	WHERE i.tipo = 'Venta' AND i.anulado != 3
    	group by id_empleado
    	order by costomax DESC
    	limit 5")->fetch();
    
    $clientes =  $db->query("SELECT *, (SUM(i.monto_total*(1-i.descuento_porcentaje/100) )-SUM(x.monto2))as costomax, SUM(i.monto_total*(1-i.descuento_porcentaje/100) )as costomax1, SUM(x.monto2)as costomax2
    	FROM inv_egresos i
    	INNER join (
    		SELECT *, SUM(monto) as monto2
    		FROM inv_pagos p
    		INNER join inv_pagos_detalles pd ON pd.pago_id=p.id_pago AND pd.estado='1'
    		WHERE p.tipo='egreso'
    		GROUP BY pago_id
    	)as x on x.movimiento_id=i.id_egreso
    	WHERE plan_de_pagos='si' AND anulado != 3
    	group by nombre_cliente, NIT_CI
    	order by costomax DESC")->fetch();
    
    $proveedores =  $db->query("SELECT *, (SUM(i.monto_total)-SUM(x.monto2))as costomax, SUM(i.monto_total)as costomax1, SUM(x.monto2)as costomax2
    	FROM inv_ingresos i
    	INNER join (
    		SELECT *, SUM(monto) as monto2
    		FROM inv_pagos p
    		INNER join inv_pagos_detalles pd ON pd.pago_id=p.id_pago AND pd.estado='1'
    		WHERE p.tipo='ingreso'
    		GROUP BY pago_id
    	)as x on x.movimiento_id=i.id_ingreso
    	WHERE plan_de_pagos='si'
    	group by nombre_proveedor
    	order by costomax DESC")->fetch();
    
    $sucursal_ventas = $db->query("SELECT almacen, SUM(i.monto_total*(1-i.descuento_porcentaje/100) )as costomax
    	FROM inv_egresos i 
    	INNER JOIN inv_almacenes s ON id_almacen=almacen_id
    	WHERE i.anulado != 3
    	group by almacen
    	order by id_almacen ASC")->fetch();
    
    $estadisticas_stock = $db->query("SELECT nombre,stock
    	FROM (
    		SELECT I.producto_id,I.cantidad_ingresos,I.costo_ingreso,E.cantidad_egresos,(I.cantidad_ingresos - IFNULL(E.cantidad_egresos,0)) AS stock
    		FROM(
    			SELECT d.producto_id,SUM(d.cantidad*u.tamanio) AS cantidad_ingresos,SUM(d.costo) AS costo_ingreso
    			FROM inv_ingresos_detalles d
    			LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
    			LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id AND a.visible = 's'
    			LEFT JOIN inv_unidades u ON u.id_unidad= a.unidad_id
    			WHERE i.almacen_id=1  AND a.visible = 's'
    			GROUP BY d.producto_id
    		) I
    		LEFT JOIN(
    			SELECT d.producto_id,SUM(d.cantidad * u.tamanio) AS cantidad_egresos
    			FROM inv_egresos_detalles d
    			LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
    			LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id AND a.visible = 's'
    			LEFT JOIN inv_unidades u ON u.id_unidad= a.unidad_id
    			WHERE e.almacen_id = 1 AND e.anulado != 3 AND a.visible = 's'
    			GROUP BY d.producto_id
    		) E ON E.producto_id = I.producto_id
    	) A LEFT JOIN inv_productos p ON A.producto_id=p.id_producto
    	LIMIT 10")->fetch();
    
    $formato_textual = get_date_textual($_institution['formato']);
    $formato_numeral = get_date_numeral($_institution['formato']);
    
    $utilidadTotal = $ultimoCosto = $ultimoSaldo = 0;
    
    // Obtiene el rango de fechas
    $gestion = date('Y');
    $gestion_base = date('Y-m-d');
    
    // Obtiene fecha inicial
    $fecha_inicial = date_encode($gestion_base);
    // Obtiene fecha final
    $fecha_final = date_encode($gestion_base);
    
    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
    $nroSucursal = 0;
    
    // Obtiene las ventas
    $query = "SELECT * ";
    $query .= " FROM inv_almacenes ";
    
    $vSucursales = $db->query($query)->fetch();
    
    foreach ($vSucursales as $nro => $vSucursal) {
    
    	$nroSucursal++;
    	$costoSTotal = 0;
    	$importeSTotal = 0;
    	$id_sucursal = $vSucursal['id_almacen'];
    
    	// Obtiene las ventas
    	$query = "SELECT fecha_egreso 
    			FROM inv_egresos
    			WHERE fecha_egreso between '$fecha_inicial' and '$fecha_final' AND estado='V' AND tipo='Venta' AND almacen_id='$id_sucursal'
    			GROUP BY fecha_egreso
    			ORDER BY fecha_egreso ";
    	$vFechas = $db->query($query)->fetch();
    
    	foreach ($vFechas as $nro => $vFecha) {
    
    		// Obtiene las ventas
    		$query = "SELECT  *, SUM(cantidad*tamanio)as cantidadAcumul, SUM(precio*cantidad)as importeAcumul ";
    		$query .= " FROM inv_productos p ";
    
    		$query .= " INNER JOIN inv_egresos_detalles vd ON vd.producto_id=p.id_producto ";
    		$query .= " INNER JOIN inv_egresos v ON (vd.egreso_id=v.id_egreso AND estado='V' AND almacen_id='" . $id_sucursal . "') ";
    
    		$query .= " LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id  AND a.visible = 's' ";
    		$query .= " LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";
    
    		$query .= " WHERE v.fecha_egreso = '" . $vFecha['fecha_egreso'] . "' AND v.tipo='Venta' AND a.visible = 's' ";
    		$query .= " GROUP BY p.id_producto ";
    		$ventas = $db->query($query)->fetch();
    
    		foreach ($ventas as $nro1 => $venta) {
    			$cantidadTotal = escape($venta['cantidadAcumul']);
    			$precio = escape($venta['precio']);
    			$importeTotal = escape($venta['importeAcumul']);
    
    			$cantidadAnterior = 0;
    			$query = "SELECT SUM(vd.cantidad*u.tamanio)as cantidadAnterior ";
    			$query .= " FROM inv_egresos_detalles vd ";
    
    			$query .= " INNER JOIN inv_egresos v ON (egreso_id=id_egreso AND estado='V' AND almacen_id='" . $id_sucursal . "') ";
    
    			$query .= " LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id  AND a.visible = 's' ";
    			$query .= " LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";
    
    			$query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_egreso < '" . $vFecha['fecha_egreso'] . "' AND a.visible = 's' ";
    			$vAntiguos = $db->query($query)->fetch();
    			foreach ($vAntiguos as $nro2 => $vAntiguo) {
    				$cantidadAnterior = $vAntiguo['cantidadAnterior'];
    			}
    
    			$costo = 0;
    			$costoTotal = 0;
    			$prodIngresados = 0;
    			$saldo = 0;
    			$prodAc = 0;						//
    			$ingresoSW = true;				//se termino de obtener los costos
    			$unidad = "";
    
    			$ultimoSaldo = 0;
    			$ultimoCosto = 0;
    			$ultimaUnidad = "";
    			$ultimoTamanio = 1;
    
    			$detalleCompra = "";
    			$nrocompras = 0;
    
    			//se obtiene las compras desde inicio de la empresa hasta la fecha limite solicitada por el usuario
    			$query = "SELECT  *, u.tamanio, u.unidad ";
    			$query .= " FROM inv_ingresos_detalles vd ";
    			$query .= " INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
    
    			$query .= " LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id  AND a.visible = 's' ";
    			$query .= " LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";
    
    			$query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_ingreso <= '" . $vFecha['fecha_egreso'] . "' AND a.visible = 's' ";
    			$query .= " ORDER BY fecha_ingreso, u.tamanio, u.unidad ";
    
    			$iAntiguos = $db->query($query)->fetch();
    			foreach ($iAntiguos as $nro3 => $iAntiguo) {
    				$prodIngresados = $prodIngresados + $iAntiguo['cantidad'] * $iAntiguo['tamanio'];
    				//se compara los productos previamente vendidos y costos antiguos
    				//para obtener la utilidad de los ultimos productos comprados VS los productos vendidos.
    				if ($prodIngresados > $cantidadAnterior and $ingresoSW) {
    					//verificar si es el primer Ingreso
    					if ($saldo > 0) {
    						$saldo = $prodIngresados - $cantidadAnterior;
    					} else {
    						$saldo = $iAntiguo['cantidad'] * $iAntiguo['tamanio'];
    					}
    
    					if ($prodAc + $saldo <= $cantidadTotal) {
    						$saldo = $saldo;
    					} else {
    						$saldo = $cantidadTotal - $prodAc;
    						$ingresoSW = false;
    					}
    
    					$prodAc = $prodAc + $saldo;
    					$costoTotal += $saldo * ($iAntiguo['costo'] / $iAntiguo['tamanio']);
    					$costo = $iAntiguo['costo'];
    					$unidad = $iAntiguo['unidad'];
    
    					//verificar si hay un nuevo Costo
    					if (($ultimoCosto != $costo && $ultimoCosto != 0) || ($ultimaUnidad != "" && $ultimaUnidad != $unidad)) {
    						$detalleCompra .= "<b>" . $ultimaUnidad . "</b> " . " a " . $ultimoCosto . " " . $moneda . "<br>";
    						$ultimoSaldo = $saldo;
    						$ultimoCosto = $costo;
    						$ultimaUnidad = $unidad;
    						$nrocompras++;
    					} else {
    						$ultimoSaldo += $saldo;
    						$ultimoCosto = $costo;
    						$ultimaUnidad = $unidad;
    					}
    				}
    			}
    
    			if ($ultimoSaldo != 0) {
    				$subtotal = $ultimoCosto * $ultimoSaldo / $ultimoTamanio;
    			}
    
    			//en caso de no existir el suficiente stock, estimamos los costos segun al ultimo precio de compra
    			if ($cantidadTotal > $prodAc) {
    				$query = "SELECT  costo, u.unidad ";
    				$query .= " FROM inv_ingresos_detalles vd ";
    				$query .= " INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
    				$query .= " INNER JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id  AND a.visible = 's' ";
    				$query .= " INNER JOIN inv_unidades u ON u.id_unidad=a.unidad_id AND tamanio='1' ";
    				$query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_ingreso <= '$fecha_final' AND a.visible = 's' ";
    				$query .= " ORDER BY fecha_ingreso DESC ";
    				$iUltimo = $db->query($query)->fetch_first();
    
    				//calcularemos usando el precio de 
    				if ($iUltimo) {
    					$ultimoSaldo = $cantidadTotal - $saldo;
    					$ultimoCosto = $iUltimo['costo'];
    					$subtotal = $ultimoSaldo * $ultimoCosto;
    					$costoTotal += $subtotal;
    				} else {
    					$query = "SELECT  costo, u.unidad, u.tamanio ";
    					$query .= " FROM inv_ingresos_detalles vd ";
    					$query .= " INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
    					$query .= " INNER JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id  AND a.visible = 's' ";
    					$query .= " INNER JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";
    					$query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_ingreso <= '$fecha_final' AND a.visible = 's' ";
    					$query .= " ORDER BY fecha_ingreso DESC ";
    					$iUltimo = $db->query($query)->fetch_first();
    					$ultimoSaldo = $cantidadTotal - $saldo;
    					$ultimoCosto = $iUltimo['costo'];
    					$subtotal = (!$iUltimo['tamanio']) ? $ultimoSaldo * ($ultimoCosto / 1) : $ultimoSaldo * ($ultimoCosto / $iUltimo['tamanio']);
    					$costoTotal += $subtotal;
    				}
    			}
    
    			$costoSTotal += $costoTotal;
    			$importeSTotal += $importeTotal;
    		}
    		//number_format($costoSTotal,2,"."," "); 
    
    		//number_format($importeSTotal,2,"."," "); 
    
    		$utilidad[$nroSucursal] = number_format(($importeSTotal - $costoSTotal), 0, "", "");
    		//$utilidadTotal=$utilidadTotal+$importeSTotal-$costoSTotal;
    	}
    	$utilidad[$nroSucursal] = number_format(($importeSTotal - $costoSTotal), 0, "", "");
    	$utilidadName[$nroSucursal] = $vSucursal['almacen'];
    }
}
	require_once show_template('header-advanced'); 
?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Escritorio</strong>
	</h3>
</div>
<style>
	.medida {
		height: 300px;
		overflow: scroll;
	}

	.medida2 {
		height: 200px;
		overflow: scroll;
	}
	
	#diario{ 
       display:none
    }
    
    #caja{ 
       display:none
    }
</style>
<div class="panel-body">
	<div class="row">
	    <!-- <div class="col-md-12">
	        <div class="alert alert-warning">
              <b>Señores usuarios:</b> <br>
              <p>
                    Se les informa que se hizo un actualización el dia viernes 23 de julio a horas 16:30 p.m. <br>
                    
                    <b>NOTA:</b> la no actualizacion de la Aplicacion ANDROID causara que no se listen las promociones en versiones anteriores.</p>
            </div>
	    </div> -->
		<div class="col-sm-4 col-md-3">
			<div class="row margin-bottom">
				<div class="col-xs-10 col-xs-offset-1">

					<img src="<?= (escape($_institution['imagen_encabezado']) != '') ? imgs . '/logo-color.png': imgs . '/logo-color.png'; ?>" class="img-responsive">
				</div>
			</div>
			<div class="well text-center">
				<?php if ($_user['persona_id']) : ?>
					<h4 class="margin-none">Bienvenido al sistema!</h4>
					<p>
						<strong><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></strong>
					</p>
				<?php else : ?>
					<h4 class="margin-none">Bienvenido al sistema!</h4>
					<p>
						<strong><?= escape($_user['username']); ?></strong>
					</p>
				<?php endif ?>
				<p>
					<img src="<?= ($_user['avatar'] == '') ? imgs . '/avatar.jpg' : profiles . '/' . $_user['avatar']; ?>" class="img-circle" width="128" height="128" data-toggle="modal" data-target="#modal_mostrar">
				</p>
				<p class="margin-none">
					<strong><?= escape($_user['email']); ?></strong>
					<br>
					<span class="text-success">en línea</span>
				</p>
			</div>
			<div class="list-group">
				<a href="../sistema-app/storage/Checkcode_distribucion.apk" class="list-group-item">
					<span>Descargar aplicacion <b>PreventasApp</b></span>
					<span class="glyphicon glyphicon-menu-right pull-right"></span>
				</a>
				<a href="?/home/perfil_ver" class="list-group-item">
					<span>Mostrar mi perfil</span>
					<span class="glyphicon glyphicon-menu-right pull-right"></span>
				</a>
				<a href="?/site/logout" class="list-group-item">
					<span>Cerrar mi sesión</span>
					<span class="glyphicon glyphicon-menu-right pull-right"></span>
				</a>
			</div>
			<div class="list-group" id="diario">
				<a href="?/cuentas/mostrar" class="list-group-item">
					<span>Plan de cuentas</span>
				</a>
				<a href="?/diario/listar" class="list-group-item">
					<span>Libro diario</span>
				</a>
				<a href="?/asientos/listar" class="list-group-item">
					<span>Configurar asientos</span>
				</a>
				<a href="?/balances/inicial" class="list-group-item">
					<span>Balance de apertura</span>
				</a>
				<a href="?/balances/comparativo" class="list-group-item">
					<span>Balance comparativo</span>
				</a>
				<a href="?/balances/general" class="list-group-item">
					<span>Balance general</span>
				</a>
				<a href="?/balances/resultados" class="list-group-item">
					<span>Estado de redultados</span>
				</a>
				<a href="?/balances/sumas" class="list-group-item">
					<span>Sumas y saldos</span>
				</a>
				<a href="?/hoja_trabajo/hoja10" class="list-group-item">
					<span>Hoja de 8 columnas</span>
				</a>
				<a href="?/hoja_trabajo/hoja8" class="list-group-item">
					<span>Hoja de 6 columnas</span>
				</a>
				<a href="?/estados_financieros/patrimonio" class="list-group-item">
					<span>Cambio de patrimonio</span>
				</a>
				<a href="?/estados_financieros/flujo" class="list-group-item">
					<span>Estado de flujo</span>
				</a>
			</div>
			<div class="list-group" id="caja">
				<a href="?/movimientos/ingresos_listar" class="list-group-item">
					<span>Ingreso de dinero a caja</span>
				</a>
				<a href="?/movimientos/egresos_listar" class="list-group-item">
					<span>Egreso de dinero de caja</span>
				</a>
				<a href="?/movimientos/gastos_listar" class="list-group-item">
					<span>Registro de gastos</span>
				</a>
				<a href="?/movimientos/cerrar" class="list-group-item">
					<span>Cierre de caja</span>
				</a>
				<a href="?/movimientos/mostrar" class="list-group-item">
					<span>Reporte general de caja</span>
				</a>
			</div>
		</div>
		<div class="col-sm-8 col-md-9">
		    <?php if($verifica == true) { ?>
    			<!--------------------------------------------------->
    			<div class="row">
    				<div class="col-sm-12 col-md-6 col-lg-3">
    					<div data-enlace="?/reportes/ventas_generales" class="alert alert-info cursor-pointer">
    						<div class="row align-items-center" style='padding:10px'>
    							<div class="col-auto pr-0" style='float:left;padding:10px'>
    								<span class="h1">
    									<span class="glyphicon glyphicon-user"></span>
    								</span>
    							</div>
    							<div class="col">
    								<b>Nuevas ventas</b>
    								<br>
    								<em><?= dif_Hour($ventas_hoy['hourr'], $houur) ?></em>
    								<br>
    								<em class="h1 m-0"><?= $ventas_hoy['countt'] ?></em>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-sm-12 col-md-6 col-lg-3">
    					<div class="alert alert-warning">
    						<div class="row align-items-center" style='padding:10px'>
    							<div class="col-auto pr-0" style='float:left;padding:10px'>
    								<span class="h1">
    									<span class="glyphicon glyphicon-star"></span>
    								</span>
    							</div>
    							<div class="col">
    								<b>Nuevos clientes</b>
    								<br>
    								<em><?= dif_Hour($clientes_hoy['hourr'], $houur) ?></em>
    								<br>
    								<em class="h1 m-0"><?= $clientes_hoy['countt'] ?></em>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-sm-12 col-md-6 col-lg-3">
    					<div data-enlace="?/operaciones/facturas_listar" class="alert alert-success cursor-pointer">
    						<div class="row align-items-center">
    							<div class="col-auto pr-0" style='float:left;padding:10px'>
    								<span class="h1">
    									<span class="glyphicon glyphicon-star"></span>
    								</span>
    							</div>
    							<div class="col">
    								<b>Facturas emitidas</b>
    								<br>
    								<em><?php
    								$x = 0;
    								foreach ($facturas_hoy2 as $facturas_hoy222) {
    									echo dif_Hour($facturas_hoy222['hourr'], $houur);
    									$x++;
    								}
    								if ($x == 0) {
    									echo " Hace más de 1 dia";
    								}
    								?></em>
    								<br>
    								<em class="h1 m-0"><?= $facturas_hoy['countt'] ?></em>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="col-sm-12 col-md-6 col-lg-3">
    					<div class="alert alert-danger">
    						<div class="row align-items-center" style='padding:10px'>
    							<div class="col-auto pr-0" style='float:left;padding:10px'>
    								<span class="h1">
    									<span class="glyphicon glyphicon-star"></span>
    								</span>
    							</div>
    							<div class="col">
    								<b>Nuevas compras</b>
    								<br>
    								<em><?= dif_Hour($compras_hoy['hourr'], $houur) ?></em>
    								<br>
    								<em class="h1 m-0"><?= $compras_hoy['countt'] ?></em>
    							</div>
    						</div>
    					</div>
    				</div>
    			</div>
    			<div class="row">
    				<div class="col-lg-6">
    					<div id="estadisticas_ventas">
    						<table class="table table-bordered table-condensed hidden">
    							<thead>
    								<tr>
    									<th>Fecha</th>
    									<th>Ventas</th>
    								</tr>
    							</thead>
    							<tbody>
    								<?php
    								for ($i = 7; $i >= 0; $i--) {
    									$venta_dia =  $db->query("SELECT *, SUM(i.monto_total*(1-i.descuento_porcentaje/100) )as costomax
    												FROM inv_egresos i
    												WHERE fecha_egreso='" . $DD[$i] . "'")->fetch_first();
    								?>
    									<tr>
    										<th><?= $DD2[$i]; ?></th>
    										<td><?= $venta_dia['costomax'] ?></td>
    									</tr>
    								<?php
    								}
    								?>
    							</tbody>
    						</table>
    						<p class="lead">Venta semanal</p>
    						<div class="well">
    							<canvas></canvas>
    						</div>
    					</div>
    				</div>
    				<div class="col-lg-6">
    					<div id="estadisticas_sucursales">
    						<table class="table table-bordered table-condensed hidden">
    							<thead>
    								<tr>
    									<th>Sucursal</th>
    									<th>Ventas</th>
    								</tr>
    							</thead>
    							<tbody>
    								<tr>
    									<th></th>
    									<td></td>
    								</tr>
    								<?php
    								foreach ($sucursal_ventas as $sucursal) {
    								?>
    									<tr>
    										<th><?= $sucursal['almacen'] ?></th>
    										<td><?= $sucursal['costomax'] ?></td>
    									</tr>
    								<?php } ?>
    								<tr>
    									<th></th>
    									<td></td>
    								</tr>
    							</tbody>
    						</table>
    						<p class="lead">Ventas por sucursal</p>
    						<div class="well">
    							<canvas></canvas>
    						</div>
    					</div>
    				</div>
    				<div class="col-lg-6">
    					<div id="estadisticas_stock">
    						<table class="table table-bordered table-condensed hidden">
    							<thead>
    								<tr>
    									<th>Sucursal</th>
    									<th>Stock</th>
    								</tr>
    							</thead>
    							<tbody>
    								<tr>
    									<th></th>
    									<td></td>
    								</tr>
    								<?php foreach ($estadisticas_stock as $stock) { ?>
    									<tr>
    										<th><?= $stock['nombre'] ?></th>
    										<td><?= $stock['stock'] ?></td>
    									</tr>
    								<?php } ?>
    								<tr>
    									<th></th>
    									<td></td>
    								</tr>
    							</tbody>
    						</table>
    						<p class="lead">Stock de productos</p>
    						<div class="well">
    							<canvas></canvas>
    						</div>
    					</div>
    				</div>
    				<div class="col-lg-6">
    					<div id="utilidad_sucursales">
    						<table class="table table-bordered table-condensed hidden">
    							<thead>
    								<tr>
    									<th>Sucursal</th>
    									<th>Ventas</th>
    								</tr>
    							</thead>
    							<tbody>
    								<tr>
    									<th></th>
    									<td></td>
    								</tr>
    								<?php
    								foreach ($sucursal_ventas as $sucursal) {
    								?>
    									<tr>
    										<th><?= $sucursal['almacen'] ?></th>
    										<td><?= $sucursal['costomax'] ?></td>
    									</tr>
    								<?php } ?>
    								<tr>
    									<th></th>
    									<td></td>
    								</tr>
    							</tbody>
    						</table>
    						<p class="lead">Utilidades por sucursal</p>
    						<div class="well">
    							<canvas></canvas>
    						</div>
    					</div>
    				</div>
    			</div>
    			<div class="row">
    				<div class="col-lg-4">
    					<p class="lead">Ranking de ventas por empleado</p>
    					<ul class="list-group">
    						<?php
    						$max = 0;
    						foreach ($empleados as $empleado) :
    							if ($max == 0) {
    								$max = $empleado['costomax'];
    							}
    						?>
    							<li class="list-group-item py-2">
    								<div class="row align-items-center">
    									<div class="col-auto pr-0" style='float:left;margin:10px'>
    										<img src="<?= imgs; ?>/avatar.jpg" class="rounded-circle" height="48" data-toggle="lightbox" data-lightbox-size="md" data-lightbox-content="<?= escape($cliente['nombre_cliente']); ?>">
    									</div>
    									<div class="col pull-right-container">
    										<div class="text-primary"><?= escape($empleado['nombres'] . " " . $empleado['paterno'] . " " . $empleado['materno']); ?></div>
    										<div class="h3" style='margin:0 !important'><?= number_format(escape($empleado['costomax']), 2, '.', ''); ?></div>
    										<span class="pull-right" style='margin-right:10px'>
    											<?php
    											$limit = round(($empleado['costomax'] / $max) * 5);
    											for ($i = 1; $i <= $limit; $i++) {
    											?>
    												<span class="glyphicon glyphicon-star text-danger"></span>
    											<?php
    											}
    											for ($i = $limit + 1; $i <= 5; $i++) {
    											?>
    												<span class="glyphicon glyphicon-star"></span>
    											<?php
    											}
    											?>
    										</span>
    									</div>
    								</div>
    							</li>
    						<?php endforeach ?>
    					</ul>
    				</div>
    				<div class="col-lg-4">
    					<p class="lead">Resumen de saldo de clientes</p>
    					<ul class="list-group">
    						<?php
    						foreach ($clientes as $cliente) {
    						?>
    							<li class="list-group-item list-group-item-success pull-right-container">
    								<span><?= $cliente["nombre_cliente"]; ?></span>
    								<span class="pull-right lead"><?= number_format($cliente["costomax"], 2, '.', ''); ?> </span>
    							</li>
    						<?php } ?>
    					</ul>
    					<p class="lead">Resumen de saldo a proveedores</p>
    					<ul class="list-group">
    						<?php
    						foreach ($proveedores as $proveedor) {
    						?>
    							<li class="list-group-item list-group-item-success pull-right-container">
    								<span><?= $proveedor["nombre_proveedor"]; ?></span>
    								<span class="pull-right lead"><?= number_format($proveedor["costomax"], 2, '.', ''); ?> </span>
    							</li>
    						<?php } ?>
    					</ul>
    				</div>
    				<div class="col-lg-4">
    					<p class="lead">Ranking de productos más vendidos</p>
    					<ul class="list-group">
    						<?php
    						$max = 0;
    						foreach ($productos as $producto) :
    							if ($max == 0)
    								$max = $producto['costomax'];
    						?>
    							<li class="list-group-item py-2">
    								<div class="row align-items-center">
    									<div class="col-auto pr-0" style='float:left;margin:10px'>
    										<img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" class="img-rounded" height="64" data-toggle="lightbox" data-lightbox-size="md" data-lightbox-content="<div class='text-center'><p class='lead m-0'><?= escape($producto['nombre']); ?></p><p class='m-0'><?= escape($producto['nombre']); ?></p><p class='h1 m-0'>55 Bs.</p></div>">
    									</div>
    									<div class="col pull-right-container">
    										<div class="text-primary"><?= escape($producto['nombre']); ?></div>
    										<div class="h6 m-0"><?= escape($producto['codigo']); ?></div>
    										<span class="pull-right">
    											<?php
    											$limit = round(($producto['costomax'] / $max) * 5);
    											for ($i = 1; $i <= $limit; $i++) {
    											?>
    												<span class="glyphicon glyphicon-star text-danger"></span>
    											<?php
    											}
    											for ($i = $limit + 1; $i <= 5; $i++) {
    											?>
    												<span class="glyphicon glyphicon-star"></span>
    											<?php
    											}
    											?>
    										</span>
    									</div>
    								</div>
    							</li>
    						<?php endforeach ?>
    					</ul>
    				</div>
    			</div>
    			<!--------------------------------------------------->
    			<!-- <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <span class="glyphicon glyphicon-search"></span>
                            <strong>Fecha de vencimiento cercana</strong>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php
                        $almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
                        $id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;
                        $productosv = $db->query("
                    					SELECT * FROM (SELECT DATE(DATE_ADD(now(), INTERVAL 4 MONTH)) as nueva_fecha, p.id_producto, p.promocion, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, IFNULL(s.cantidad_egresos, 0) AS cantidad_egresos, (IFNULL(e.cantidad_ingresos, 0) - IFNULL(s.cantidad_egresos, 0)) AS cantidad_total, u.unidad, u.sigla, c.categoria, e.vencimiento2, e.detalles_ingreso, s.detalles_egreso
    					FROM inv_productos p
    					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos, MIN(d.vencimiento) as vencimiento2, GROUP_CONCAT(i.id_ingreso, '*', d.cantidad, '*', d.vencimiento ORDER BY i.id_ingreso ASC SEPARATOR '|' ) as detalles_ingreso
    						   FROM inv_ingresos_detalles d
    						   LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
    						   WHERE i.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
    					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos, GROUP_CONCAT(e.id_egreso, '*', d.cantidad ORDER BY e.id_egreso ASC SEPARATOR '|' ) as detalles_egreso
    						   FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
    						   WHERE e.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
    					LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id
                        LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id) as ct WHERE ct.cantidad_total != 0 AND ct.vencimiento2 < ct.nueva_fecha
                    					")->fetch();
                        ?>
                        <div class="table-responsive medida2" id="medida2">
                            <table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
                                <thead>
                                <tr class="active">
                                    <th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">#</th>
                                    <th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Código</th>
                                    <th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Nombre comercial</th>
                                    <th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Nombre genérico</th>
                                    <th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Vencimiento</th>
                                    <th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Stock</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($productosv as $nro => $producto) { ?>
    
                                    <?php
                                    $detalle_ingreso = explode('|',$producto['detalles_ingreso']);
                                    $detalle_egreso = explode('|',$producto['detalles_egreso']);
                                    $sw = 0;
                                    $mostrar = '';
                                    for($i = 0; $i < count($detalle_ingreso); $i ++){
                                        if($sw == 0){
                                            $valor1 = explode('*',$detalle_ingreso[$i]);
                                            $canti = $valor1[1];
                                            $venci = $valor1[2];
                                            foreach($detalle_egreso as $valores2){
                                                $valor2 = explode('*',$valores2);
                                                if($canti >= $valor2[1]){
                                                    $canti = $canti - $valor2[1];
                                                }else{
                                                    $i = $i + 1;
                                                    $valor1 = explode('*',$detalle_ingreso[$i]);
                                                    $canti = $canti + $valor1[1];
                                                    $venci = $valor1[2];
                                                    if($canti > $valor2[1]){
                                                        $canti = $canti - $valor2[1];
                                                    }else{
                                                        $i = $i + 1;
                                                        $valor1 = explode('*',$detalle_ingreso[$i]);
                                                        $canti = $canti + $valor1[1];
                                                        $venci = $valor1[2];
                                                        if($canti > $valor2[1]){
                                                            $canti = $canti - $valor2[1];
                                                        }else{
                                                            $i = $i + 1;
                                                            $valor1 = explode('*',$detalle_ingreso[$i]);
                                                            $canti = $canti + $valor1[1];
                                                            $venci = $valor1[2];
                                                            if($canti > $valor2[1]){
                                                                $canti = $canti - $valor2[1];
                                                            }else{
                                                                $i = $i + 1;
                                                                $valor1 = explode('*',$detalle_ingreso[$i]);
                                                                $canti = $canti + $valor1[1];
                                                                $venci = $valor1[2];
                                                                if($canti > $valor2[1]){
                                                                    $canti = $canti - $valor2[1];
                                                                }else{
                                                                    $i = $i + 1;
                                                                    $valor1 = explode('*',$detalle_ingreso[$i]);
                                                                    $canti = $canti + $valor1[1];
                                                                    $venci = $valor1[2];
                                                                    if($canti > $valor2[1]){
                                                                        $canti = $canti - $valor2[1];
                                                                    }else{
                                                                        $i = $i + 1;
                                                                        $valor1 = explode('*',$detalle_ingreso[$i]);
                                                                        $canti = $canti + $valor1[1];
                                                                        $venci = $valor1[2];
                                                                        if($canti > $valor2[1]){
                                                                            $canti = $canti - $valor2[1];
                                                                        }else{
    
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            $sw = 1;
                                            if($canti > 0){
                                                $mostrar = $mostrar.' ('.$canti.') '.$venci.'<br>';
                                            }
                                        }else{
                                            $valor1 = explode('*',$detalle_ingreso[$i]);
                                            $canti = $valor1[1];
                                            $venci = $valor1[2];
                                            $mostrar = $mostrar.' ('.$canti.') '.$venci.'<br>';
                                        }
                                    }
    
                                    $fecha_vencimiento = $producto['vencimiento'];
                                    $fecha_actual = date('d-m-Y');
    
                                    $datetime1 = date_create($fecha_actual);
                                    $datetime2 = date_create($fecha_vencimiento);
                                    $interval = date_diff($datetime1, $datetime2);
                                    $interval = $interval->format('%R%a');
                                    $interval = str_replace("+", "", $interval);
                                    $stock = $producto['cantidad_ingresos'] - $producto['cantidad_egresos'];
                                    if ($interval <= 90 && $stock != 0) {
                                        $fecha_vencimiento = date('d-m-Y', strtotime($producto['vencimiento']));
                                        ?>
                                        <tr>
                                            <td><?= $nro + 1; ?></td>
                                            <td><?= $producto['codigo']; ?></td>
                                            <td><?= $producto['nombre']; ?></td>
                                            <td><?= $producto['nombre_factura']; ?></td>
                                            <td><?= $mostrar; ?></th>
                                            <td><?= $stock;?> </td>
                                        </tr>
                                        <?php
                                        // }
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
    
    			<div class="panel panel-success">
    				<div class="panel-heading">
    					<h3 class="panel-title">
    						<span class="glyphicon glyphicon-search"></span>
    						<strong>Nuevos productos</strong>
    					</h3>
    				</div>
    				<div class="panel-body">
    					<?php
    					$productos = $db->query("SELECT *
                                    FROM inv_productos p
                                    ORDER BY fecha_registro DESC, id_producto DESC")->fetch();
    					?>
    					<?php if ($productos) { ?>
    						<div class="table-responsive medida2" id="medida2">
    							<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
    								<thead>
    									<tr class="active">
    										<th class="text-nowrap" style="width: 12%; background-color: #222; color:#fff; text-align: center; font-weight: bold;">Fecha</th>
    										<th class="text-nowrap" style="width: 22%; background-color: #222; color:#fff; text-align: center; font-weight: bold;">Nombre</th>
    										<th class="text-nowrap" style="width: 12%; background-color: #222; color:#fff; text-align: center; font-weight: bold;">Precio actual</th>
    										<th class="text-nowrap" style="width: 54%; background-color: #222; color:#fff; text-align: center; font-weight: bold;">Descripcion</th>
    									</tr>
    								</thead>
    								<tbody>
    									<?php foreach ($productos as $nro => $producto) { ?>
    										<tr>
    											<td><?= $producto['fecha_registro']; ?></td>
    											<td><?= $producto['nombre']; ?></td>
    											<td><?= $producto['precio_actual']; ?></td>
    											<td><?= $producto['descripcion']; ?></td>
    										</tr>
    									<?php } ?>
    								</tbody>
    							</table>
    						</div>
    					<?php } ?>
    				</div>
    			</div>
    			<div class="panel panel-primary">
    				<div class="panel-heading">
    					<h3 class="panel-title">
    						<span class="glyphicon glyphicon-search"></span>
    						<strong>Productos con bajo STOCK</strong>
    					</h3>
    				</div>
    				<div class="panel-body">
    					<?php
    					$select = " SELECT p.id_producto, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, c.categoria, e.ingresos,s.egresos
    								FROM inv_productos p 
    								LEFT JOIN inv_categorias c on c.id_categoria = p.categoria_id
    								LEFT JOIN (
    									SELECT d.producto_id, IFNULL(SUM(d.cantidad),0) as ingresos
    									FROM inv_ingresos_detalles d
    									left join inv_ingresos i on i.id_ingreso = d.ingreso_id
    									LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id  AND a.visible = 's'
    									LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id 
										WHERE a.visible = 's'
    									group by d.producto_id
    								) as e on e.producto_id = p.id_producto
    								LEFT JOIN (
    									SELECT d.producto_id, IFNULL(SUM(d.cantidad),0) as egresos 
    									FROM inv_egresos_detalles d 
    									left join inv_egresos e on e.id_egreso = d.egreso_id 
    									LEFT JOIN inv_asignaciones a ON a.id_asignacion = d.asignacion_id AND a.visible = 's'
    									LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id 
    									WHERE e.anulado = 0 AND a.visible = 's'
    									group by d.producto_id
    								) as s on s.producto_id = p.id_producto
    								ORDER BY p.nombre";
    
    					$productos22 = $db->query($select)->fetch();
    					?>
    					<div class="table-responsive medida2" id="medida2">
    
    						<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
    							<thead>
    								<tr class="active">
    									<th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">#</th>
    									<th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Código</th>
    									<th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Nombre comercial</th>
    									<th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Nombre genérico</th>
    									<th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Mínimo</th>
    									<th class="text-nowrap" style="background-color: #222; color:#fff; text-align: center; font-weight: bold;">Total existencias</th>
    								</tr>
    							</thead>
    							<tfoot>
    								<tr class="active">
    									<th class="text-nowrap text-middle" data-datafilter-filter="true">#</th>
    									<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
    									<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre comercial</th>
    									<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre genérico</th>
    									<th class="text-nowrap text-middle" data-datafilter-filter="true">Mínimo</th>
    									<th class="text-nowrap text-middle" data-datafilter-filter="true">Total existencias</th>
    								</tr>
    							</tfoot>
    							<tbody>
    								<?php
    								foreach ($productos22 as $nro => $producto) {
    									$ing = intval($producto['ingresos']);
    									$egr = intval($producto['egresos']);
    
    									if ($producto['cantidad_minima'] > ($ing - $egr)) {
    								?>
    										<tr>
    											<th class="text-nowrap"><?= $nro + 1; ?></th>
    											<td class="text-nowrap"><?= escape($producto['codigo']); ?></td>
    
    											<td class="width-lg"><?= escape($producto['nombre']); ?></td>
    											<td class="width-lg"><?= escape($producto['nombre_factura']); ?></td>
    
    											<td class="text-nowrap text-right"><?= escape($producto['cantidad_minima']); ?></td>
    											<td class="text-nowrap text-right"><strong class="text-primary"><?php echo ($ing - $egr); ?></strong></td>
    										</tr>
    								<?php
    									}
    								}
    								?>
    							</tbody>
    						</table>
    					</div>
    				</div>
    			</div> -->
    			<!--------------------------------------------------->
    		<?php } ?>
		</div>
	</div>
</div>
<?php if($verifica == true) { ?>

    <script src="<?= js; ?>/Chart.min.js"></script>
    <script>
    	$(function() {
    
    		var $estadisticas_ventas = $('#estadisticas_ventas'),
    			$fila, contexto, grafico, nombre, valor, nombres = [],
    			valores = [];
    
    		$estadisticas_ventas.find('div').css('height', 256);
    
    		nombre = $.trim($($estadisticas_ventas.find('table thead tr').children().get(0)).text());
    		valor = $.trim($($estadisticas_ventas.find('table thead tr').children().get(1)).text());
    
    		$estadisticas_ventas.find('table tbody tr').each(function(i) {
    			$fila = $(this);
    			nombres.push($.trim($($fila.children().get(0)).text()));
    			valores.push($.trim($($fila.children().get(1)).text()));
    		});
    
    		contexto = $estadisticas_ventas.find('canvas').get(0).getContext('2d');
    
    		grafico = new Chart(contexto, {
    			type: 'line',
    			data: {
    				labels: nombres,
    				datasets: [{
    					label: valor,
    					data: valores,
    					borderColor: 'rgba(217, 83, 79, 1)',
    					backgroundColor: 'rgba(217, 83, 79, 0.2)',
    					borderWidth: 2,
    					pointRadius: 1,
    					pointHoverRadius: 1,
    					fill: true,
    					lineTension: 0.2
    				}]
    			},
    			options: {
    				responsive: true,
    				maintainAspectRatio: false,
    				scales: {
    					xAxes: [{
    						scaleLabel: {
    							display: false,
    							labelString: nombre
    						}
    					}],
    					yAxes: [{
    						scaleLabel: {
    							display: false,
    							labelString: valor
    						}
    					}]
    				}
    			}
    		});
    
    		var $estadisticas_sucursales = $('#estadisticas_sucursales'),
    			$fila, contexto, grafico, nombre, valor, nombres = [],
    			valores = [];
    
    		$estadisticas_sucursales.find('div').css('height', 256);
    
    		nombre = $.trim($($estadisticas_sucursales.find('table thead tr').children().get(0)).text());
    		valor = $.trim($($estadisticas_sucursales.find('table thead tr').children().get(1)).text());
    
    		$estadisticas_sucursales.find('table tbody tr').each(function(i) {
    			$fila = $(this);
    			nombres.push($.trim($($fila.children().get(0)).text()));
    			valores.push($.trim($($fila.children().get(1)).text()));
    		});
    
    		contexto = $estadisticas_sucursales.find('canvas').get(0).getContext('2d');
    
    		grafico = new Chart(contexto, {
    			type: 'line',
    			data: {
    				labels: nombres,
    				datasets: [{
    					label: valor,
    					data: valores,
    					borderColor: 'rgba(2, 117, 216, 1)',
    					backgroundColor: 'rgba(2, 117, 216, 0.2)',
    					borderWidth: 2,
    					pointRadius: 1,
    					pointHoverRadius: 1,
    					fill: true,
    					lineTension: 0.2
    				}]
    			},
    			options: {
    				responsive: true,
    				maintainAspectRatio: false,
    				scales: {
    					xAxes: [{
    						scaleLabel: {
    							display: false,
    							labelString: nombre
    						}
    					}],
    					yAxes: [{
    						scaleLabel: {
    							display: false,
    							labelString: valor
    						}
    					}]
    				}
    			}
    		});
    
    		var $estadisticas_stock = $('#estadisticas_stock'),
    			$fila, contexto, grafico, nombre, valor, nombres = [],
    			valores = [];
    		$estadisticas_stock.find('div').css('height', 256);
    
    		nombre = $.trim($($estadisticas_stock.find('table thead tr').children().get(0)).text());
    		valor = $.trim($($estadisticas_stock.find('table thead tr').children().get(1)).text());
    
    		$estadisticas_stock.find('table tbody tr').each(function(i) {
    			$fila = $(this);
    			nombres.push($.trim($($fila.children().get(0)).text()));
    			valores.push($.trim($($fila.children().get(1)).text()));
    		});
    		contexto = $estadisticas_stock.find('canvas').get(0).getContext('2d');
    
    		grafico = new Chart(contexto, {
    			type: 'pie',
    			data: {
    				labels: nombres,
    				datasets: [{
    					label: valor,
    					data: valores,
    					backgroundColor: [
    						'rgba(255, 99, 132, 0.5)',
    						'rgba(54, 162, 235, 0.2)',
    						'rgba(255, 206, 86, 0.2)',
    						'rgba(75, 192, 192, 0.2)',
    						'rgba(175, 242, 208, 0.2)',
    						'rgba(255, 255, 153, 0.2)',
    						'rgba(73, 47, 243, 0.2)',
    						'rgba(32, 142, 252, 0.2)',
    						'rgba(252, 222, 32, 0.2)',
    						'rgba(252, 83, 32, 0.2)'
    					],
    					borderColor: [
    						'rgba(255,99,132,1)',
    						'rgba(54, 162, 235, 1)',
    						'rgba(255, 206, 86, 1)',
    						'rgba(75, 192, 192, 1)',
    						'rgba(175, 242, 208, 1)',
    						'rgba(255, 255, 153, 1)',
    						'rgba(73, 47, 243, 1)',
    						'rgba(32, 142, 252, 1)',
    						'rgba(252, 222, 32, 1)',
    						'rgba(252, 83, 32, 1)'
    					],
    				}]
    			},
    			options: {
    				responsive: true,
    				maintainAspectRatio: false
    			}
    		});
    
    
    		var $utilidad_sucursales = $('#utilidad_sucursales'),
    			$fila, contexto, grafico, nombre, valor, nombres = [],
    			valores = [];
    
    		$utilidad_sucursales.find('div').css('height', 256);
    
    		nombre = $.trim($($utilidad_sucursales.find('table thead tr').children().get(0)).text());
    		valor = $.trim($($utilidad_sucursales.find('table thead tr').children().get(1)).text());
    
    
    		<?php
    		for ($ii = 1; $ii <= $nroSucursal; $ii++) {
    		?>
    			nombres.push('<?php echo $utilidadName[$ii]; ?>');
    			valores.push(<?php echo $utilidad[$ii]; ?>);
    		<?php
    		}
    		?>
    
    		contexto = $utilidad_sucursales.find('canvas').get(0).getContext('2d');
    
    
    		grafico = new Chart(contexto, {
    			type: 'pie',
    			data: {
    				labels: nombres,
    				datasets: [{
    					label: valor,
    					data: valores,
    					borderColor: ["rgb(255, 99, 132)", "rgb(54, 162, 235)", "rgb(255, 205, 86)"],
    					backgroundColor: ["rgba(255, 99, 132,0.5)", "rgba(54, 162, 235,0.5)", "rgba(255, 205, 86,0.5)"],
    					borderWidth: 0.2,
    					pointRadius: 0.2,
    					pointHoverRadius: 0.1,
    					fill: true,
    					lineTension: 0.2
    				}]
    			},
    			options: {
    				responsive: true,
    				maintainAspectRatio: false
    			}
    		});
    
    		$('[data-enlace]').on('click', function(e) {
    			e.preventDefault();
    			var enlace = $(this).attr('data-enlace');
    			window.location = enlace;
    		});
    	});
    	$(window).bind('keydown', function (e) {
    		if (e.altKey || e.metaKey) {
    			switch (String.fromCharCode(e.which).toLowerCase()) {
    				case 'a':
    					e.preventDefault();
    					$('#diario').show(1000);
    				break;
    				case 'b':
    					e.preventDefault();
    					$('#diario').hide(1000);
    				break;
    				case 'c':
    					e.preventDefault();
    					$('#caja').show(1000);
    				break;
    				case 'd':
    					e.preventDefault();
    					$('#caja').hide(1000);
    				break;
    			}
    		}
    	});
    </script>
<?php } ?>
<?php require_once show_template('footer-advanced'); ?>