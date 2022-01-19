<?php


if (!isset($params[0])) :
    require_once not_found();
    die();
endif;
// Obtiene los permisos
$permisos = explode(',', permits);
// Almacena los permisos en variables
$permiso_editar = in_array('notas_editar', $permisos);
$permiso_listar = in_array('notas_listar', $permisos);
$permiso_reimprimir = in_array('notas_obtener', $permisos);

$limite_monetario = 1000000;
$limite_monetario = number_format($limite_monetario, 2, '.', '');

$IdNotaRemision = $params[0];
//EGRESO
$Egreso = $db->query("SELECT e.id_egreso,e.monto_total,e.nro_registros,e.tipo,e.fecha_egreso,e.hora_egreso,e.descripcion,e.nro_factura,e.codigo_control,e.nombre_cliente,e.nit_ci,e.estadoe,
						CONCAT(se.nombres,' ',se.paterno,' ',se.materno) AS empleado, cl.credito, cl.dias, cl.nombre_factura, e.plan_de_pagos, e.cliente_id, 
						a.id_almacen,a.almacen,e.monto_total_descuento,e.descuento_bs,e.descuento_porcentaje
						FROM inv_egresos AS e
						LEFT JOIN sys_empleados AS se ON e.empleado_id=se.id_empleado
						LEFT JOIN inv_clientes as cl ON e.cliente_id = cl.id_cliente
						LEFT JOIN inv_almacenes AS a ON a.id_almacen=e.almacen_id
						WHERE e.id_egreso='{$IdNotaRemision}'")->fetch_first();

$DetalleEgreso = $db->query("SELECT a.*, b.*, a.unidad_id AS unidad_det, GROUP_CONCAT(c.unidad_id, '*',d.unidad, '*', c.otro_precio SEPARATOR '|') AS prec
                    	FROM inv_egresos_detalles a
                    	LEFT JOIN inv_productos b ON a.producto_id = b.id_producto
                    	LEFT JOIN inv_asignaciones c ON b.id_producto = c.producto_id AND c.visible = 's'
                    	LEFT JOIN inv_unidades d ON c.unidad_id = id_unidad
						WHERE a.egreso_id = '$IdNotaRemision' and a.promocion_id <2 AND c.visible = 's' GROUP BY a.id_detalle")->fetch();

$clientes = $db->query("select * FROM inv_clientes ORDER BY cliente asc, nit asc")->fetch();

$moneda = $db->query("SELECT sigla FROM inv_monedas WHERE oficial = 'S'")->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

$id_almacen = ($Egreso['id_almacen']) ? $Egreso['id_almacen']: 0;
//MAQUETACION
// $tmp = $db->query("SELECT * FROM tmp_egresos WHERE id_egreso = '$IdNotaRemision' AND distribuidor_estado = 'ENTREGA' AND estado = 3 ")->fetch_first();

//MAQUETACION
require_once show_template('header-advanced');

if($permiso_editar){//!$tmp['id_egreso']
    ?>
<div class='panel-heading' data-venta='<?= $id_venta ?>' data-servidor='<?= ip_local . name_project . '/factura.php'; ?>'>
    <h3 class='panel-title'>
        <span class='glyphicon glyphicon-option-vertical'></span>
        <strong>Notas de Remisión</strong>
    </h3>
</div>
<div class='panel-body'>
    <?php if (isset($_SESSION[temporary])) { ?>
        <div class='alert alert-<?= $_SESSION[temporary]['alert']; ?>'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <strong><?= $_SESSION[temporary]['title']; ?></strong>
            <p><?= $_SESSION[temporary]['message']; ?></p>
        </div>
        <?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<div class="alert alert-danger">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong>&iexcl;Advertencia!</strong>
		<ul>
			<li>La moneda con la que se est&aacute; trabajando es <?= escape($moneda); ?>.</li>
			<li>Al editar las cantidades de un producto y/o adicionar o eliminar productos, existir&aacute;n repercuciones en stocks de inventarios.</li>
			<li>Al editar las cantidades de un producto y/o adicionar o eliminar productos y/o modificar precios, existir&aacute;n repercuciones en cuentas por cobrar (si el cliente tiene cuentas pendientes) y montos en reportes.</li>
			<li>Al presionar el bot&oacute;n guardar se pierde cualquier descuento que haya existido por producto.</li>
		</ul>
	</div>
    
</div>
<div class='row'>
    <div class='col-md-6'>
        <div class='panel panel-warning'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    <span class='glyphicon glyphicon-option-vertical'></span>
                    <strong>Editar nota de remisi&oacute;n</strong>
                </h3>
            </div>
            <div class='panel-body'>
                <form id='formulario' class='form-horizontal' method='post' action='?/operaciones/notas_actualizar'>
                    <!-- Se escondió la busqueda de clientes pues si desean cambiar de cliente deberían hacer otra venta, pues es otro cliente ::BECA -->
                    
                    <!-- <div class='form-group'>
                        <label for='cliente' class='col-sm-4 control-label'>Buscar:</label>
                        <div class='col-sm-8'>
                            <select name='cliente' id='cliente' class='form-control text-uppercase' data-validation='letternumber' data-validation-allowing='-+./&() ' data-validation-optional='true'>
                                <option value=''>Buscar</option>
                                <?php foreach ($clientes as $cliente) { ?>
                                    <option value="<?= escape($cliente['nit']) . '|' . escape($cliente['cliente']) . '|' . escape($cliente['direccion']) . '|' . escape($cliente['ubicacion']) . '|' . escape($cliente['telefono']) . '|' . escape($cliente['id_cliente']) . '|' . escape($cliente['nombre_factura']) . '|' . escape($cliente['credito']) . '|' . escape($cliente['dias']); ?>"><?= escape($cliente['id_cliente']) . ' &mdash; ' . $cliente['nit'] . ' &mdash; ' . escape($cliente['cliente']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div> -->
                    <div class='form-group'>
                        <label for='nit_ci' class='col-sm-4 control-label'>NIT / CI:</label>
                        <div class='col-sm-8'>
                            <input type='text' value='<?=$Egreso['nit_ci']?>' name='nit_ci' id='nit_ci' class='form-control text-uppercase' autocomplete='off' data-validation='required number' readonly>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='nombre_cliente' class='col-sm-4 control-label'>Señor(es):</label>
                        <div class='col-sm-8'>
                            <input type='text' value='<?=$Egreso['nombre_cliente']?>' name='nombre_cliente' id='nombre_cliente' class='form-control text-uppercase' autocomplete='off' data-validation='required letternumber length' data-validation-allowing='-+./&() ' data-validation-length='max100' readonly>
                        </div>
                    </div>
                    <div class='table-responsive margin-none'>
                        <table id='ventas' class='table table-bordered table-condensed table-striped table-hover margin-none'>
                           <thead>
                                <tr class="active">
                                    <th class="text-nowrap">#</th>
                                    <th class="text-nowrap">Código</th>
                                    <th class="text-nowrap">Nombre</th>
                                    <th class="text-nowrap">Cantidad</th>
                                    <th class="text-nowrap">Unidad</th>
                                    <th class="text-nowrap">Precio</th>
                                    <th class="text-nowrap">Importe</th>
                                    <th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
                                </tr>
                            </thead>
                            <tbody>
								<?php foreach($DetalleEgreso as $key => $detalle){?>
									<?php									

										
										//stock dudoso copiado de kardex ::BECA
										$id_producto =$detalle['id_producto'];
										//obtiene stock por producto
										$ingreso_total = $db->query("SELECT p.id_producto, p.codigo, p.nombre_factura, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, (IFNULL(s.cantidad_egresos, 0)) AS cantidad_egresos
																		FROM inv_productos p
																		LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
																		FROM inv_ingresos_detalles d
																		LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
																		WHERE transitorio = 0 AND i.almacen_id = '{$id_almacen}' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
																		LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
																		FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
																		WHERE e.almacen_id = '{$id_almacen}' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto					 
																		WHERE ('2022-09-10' <= p.fecha_limite OR p.fecha_limite='1000-01-01') AND eliminado = 0 AND p.id_producto = '{$id_producto}'")->fetch_first();
										
										$resta_stock = (($ingreso_total['cantidad_ingresos'] - $ingreso_total['cantidad_egresos']) > 0) ? $ingreso_total['cantidad_ingresos'] - $ingreso_total['cantidad_egresos']: 0;
									?>
                                    <tr class="active" data-producto="<?= $detalle['producto_id'] ?>">
                                        <td class="text-nowrap"><?= $key +1 ?></td>
                                        <td class="text-nowrap"><input type="text" value="<?= $detalle['producto_id'] ?>" name="productos[]" id="producto" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número"><?= $detalle['codigo'] ?></td>
                                        <td><input type="text" value="<?= $detalle['nombre']?>" name="nombres[]" class="translate" tabindex="-1" data-validation="required"><?= $detalle['nombre'] ?></td>
                                        <td>
                                        	<input type="text" value="<?= $detalle['cantidad']/cantidad_unidad($db,$detalle['producto_id'],$detalle['unidad_det']) ?>" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;<?= $resta_stock; ?>]" data-validation-error-msg="Debe ingresar un número positivo entre 1 y <?= $resta_stock; ?>" calcular_importe(<?= $detalle['producto_id'] ?>)" >
                                        </td>
                                        <?php if(false){
//                                        if($detalle['prec']){?>
                                            <td>
                                                <select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control input-xs" onchange="agre()">';
                                                    <?php $aparte = explode('|',$detalle['prec']);
                                                    foreach($aparte as $parte){
                                                        $part = explode('*',$parte);?>
                                                    <option value="<?= $part[1] ?>" data-xyyz="" data-yyy="<?= $part[2] ?>" data-yyz="<?= $part[0] ?>" ><?= $part[1] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        <?php }else{ ?>
                                            <td><input type="text" value="<?= nombre_unidad($db,$detalle['unidad_det']); ?>" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="<?= $detalle['unidad_det'] ?>" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>
                                        <?php } ?>
                                        <td><input type="text" value="<?= $detalle['precio'] ?>" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="<?= $detalle['precio'] ?>" data-validation="required number" data-validation-allowing="range[0.1;10000000.00],float" data-validation-error-msg="Debe ingresar un número decimal positivo mayor que 0 y menor que 10000000" onkeyup="calcular_importe(<?= $detalle['producto_id'] ?>)"></td>
                                        <td class="hidden"><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="2" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="range[0;50]" data-validation-error-msg="Debe ser un número positivo entre 0 y 50" onkeyup="descontar_precio(<?= $detalle['producto_id'] ?>)"></td>
                                        <td class="text-nowrap text-right" data-importe="<?= ($detalle['cantidad']/cantidad_unidad($db,$detalle['producto_id'],$detalle['unidad_det']))*$detalle['precio'] ?>"><?= ($detalle['cantidad']/cantidad_unidad($db,$detalle['producto_id'],$detalle['unidad_det']))*$detalle['precio'] ?></td>
                                        <td class="text-nowrap text-center">
                                            <button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(<?= $detalle['producto_id'] ?>)"><span class="glyphicon glyphicon-remove"></span></button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr class="active">
                                    <th class="text-nowrap text-right" colspan="6">Importe total <?= escape($moneda); ?></th>
                                    <th class="text-nowrap text-right" data-subtotal="">0.00</th>
                                    <th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                        <div class="form-group">
							<div class="col-xs-12">
                                <!--Descuentos-->
                                <p>&nbsp;</p>
								<div class="col-xs-12">
									<div class="col-lg-3  col-md-3 col-xs-3">
										<label for="tipo" class="col-sm-6 control-label">Descuento:</label>
										<div class="col-sm-6">
											<select name="tipo" id="tipo" onchange="tipo_descuento()" class="calcular_descuento form-control">
												<option value="0" <?= $Egreso['descuento_porcentaje'] == 0 ? 'selected':'' ?>>Bs</option>
												<option value="1" <?= $Egreso['descuento_porcentaje'] != 0 ? 'selected':'' ?>>%</option>
											</select>
										</div>
									</div>
									<div class="col-lg-8 col-md-8 col-xs-8"></div>

									<div class="col-xs-3" id="div-descuento" <?= $Egreso['descuento_porcentaje'] == 0 ? 'style="display:none"':'' ?>>
										<label for="descuento" class="col-sm-4 control-label">(%):</label>
										<div class="col-sm-8">											
											<input type="number" name="descuento_porc" id="descuento_porc" value="0" onchange="calcular_descuento_total()" class="calcular_descuento form-control" data-validation="number" data-validation-allowing="range[0.00;100.00],float" data-validation-error-msg="Debe ingresar un número entre 0% y 100%" maxlength="3">
										</div>
									</div>
									<div class="col-xs-3">
										<label for="descuento" class="col-sm-4 control-label">(Bs):</label>
										<div class="col-sm-8">
											<input type="text" value="<?= $Egreso['descuento_bs'] ?>" name="descuento_bs" id="descuento_bs" onchange="calcular_descuento_total()" class="calcular_descuento form-control" data-validation="number" data-validation-allowing="range[0.00;<?= $limite_monetario; ?>],float">
										</div>
									</div>

									<div class="col-xs-3">
										<label for="importe_total_descuento" class="col-sm-4 control-label">Importe:</label>
										<div class="col-sm-8">
											<label id="importe_total_descuento" class="calcular_descuento col-sm-6 control-label"></label>
										</div>
										<input type="hidden" name="total_importe_descuento" id="total_importe_descuento">
									</div>
								</div>
								<p>&nbsp;</p>
								<!--/Descuentos-->
                            </div>
					    </div>
                    <div class='form-group'>
                        <div class='col-xs-12'>
                            <input type='text' name='almacen_id' value='<?= $Egreso['id_almacen'] ?>' class='translate' tabindex='-1' data-validation='required number' data-validation-error-msg='El almacén no esta definido'>
                            <input type='text' name='nro_registros' value='<?=$Detalle['nro_registros']?>' class='translate' tabindex='-1' data-ventas='<?=$Detalle['nro_registros']?>'>
                            <input type='text' name='monto_total' value='<?=$Detalle['precio']*$Detalle['cantidad']?>' class='translate' tabindex='-1' data-total='<?=$Detalle['precio']*$Detalle['cantidad']?>'>
                            <input type='text' name='total_importe_descuento' value='<?=$Detalle['precio']*$Detalle['cantidad']?>' class='translate' tabindex='-1' data-total-descuento='<?=$Detalle['precio']*$Detalle['cantidad']?>'>
                            
                            <input type='text' name='id_egreso' value='<?=$Egreso['id_egreso']?>' class='translate'  tabindex='-1' data-idegreso=''>
                            <input type="hidden" value="<?= $Egreso['cliente_id'] ?>" name="id_cliente" id="id_cliente">
                            <input type="hidden" value="<?= $Egreso['credito'] ?>" name="credito" id="credito">
                            <!-- <input type="hidden" value="0" name="descuento_bs" id="descuento_bs"> -->
                        </div>
                    </div>                    
                    <!-- cuentas --->
					<div id="credito_cliente">
					    <p class="text-info" id="cred">
					        <?php if($Egreso['plan_de_pagos'] == 'si') {
					            echo '<div class="alert alert-info" id="borrar"> <b>Forma de pago: </b> El cliente tiene un contrato de créditos de: '.$Egreso['dias']. ' días.  </div>';
					        } else {
					            echo '<div class="alert alert-success" id="borrar"> <b>Forma de pago: </b> El cliente no tiene contrato de créditos. </div>';
					        } ?>
					    </p>
					</div>
    					
                    <div class='form-group'>
                        <div class='col-xs-12'>
                            <div class='col-xs-6 text-right'></div>
                            <div class='col-xs-6 text-right'>
                                <button type='submit' class='btn btn-warning'>Guardar</button>
                                <button type='reset' class='btn btn-default'>Restablecer</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
		<div class='row'>
			<div class='col-md-12'> <!-- col-sm-10 col-sm-offset-1 -->
				<div class='panel panel-warning'>
					<div class='panel-heading'>
						<h3 class='panel-title'><i class='glyphicon glyphicon-log-out'></i> Información de la nota de remisión</h3>
					</div>
					<div class='panel-body'>
						<div class='form-horizontal'>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Fecha y hora:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= $Egreso['fecha_egreso'] ?> <small class='text-success'><?= escape($Egreso['hora_egreso']); ?></small></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Cliente:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['nombre_cliente']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Nombre de cliente en factura:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['nombre_factura']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>NIT / CI:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['nit_ci']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Tipo de egreso:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['tipo']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Número de factura:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['nro_factura']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Descripción:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['descripcion']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Monto total:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['monto_total']); ?></p>
								</div>
							</div>
							
							<div class='form-group'>
								<label class='col-md-3 control-label'>Número de registros:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['nro_registros']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Almacén:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['almacen']); ?></p>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 control-label'>Empleado:</label>
								<div class='col-md-9'>
									<p class='form-control-static'><?= escape($Egreso['empleado']); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div>
	
    <div class='col-md-6'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    <span class='glyphicon glyphicon-search'></span>
                    <strong>Búsqueda de productos</strong>
                </h3>
            </div>
            <div class='panel-body'>
                <h2 class='lead'>Búsqueda de productos</h2>
                <hr>
                <?php if ($permiso_listar) : ?>
                    <p class='text-right'>
                        <a href='?/operaciones/notas_listar' class='btn btn-warning'>Listado notas de remisi&oacute;n</a>
                    </p>
                <?php endif ?>
                <form method='post' action='?/notas/buscar' id='form_buscar_0' class='margin-bottom' autocomplete='off'>
                    <div class='form-group has-feedback'>
                        <input type='text' value='' name='busqueda' class='form-control' placeholder='Buscar por código' autofocus='autofocus'>
                        <span class='glyphicon glyphicon-barcode form-control-feedback'></span>
                    </div>
                    <button type='submit' class='translate' tabindex='-1'></button>
                </form>
                <form method='post' action='?/notas/buscar' id='form_buscar_1' class='margin-bottom' autocomplete='off'>
                    <div class='form-group has-feedback'>
                        <input type='text' value='' name='busqueda' class='form-control' placeholder='Buscar por código, producto o categoría'>
                        <span class='glyphicon glyphicon-search form-control-feedback'></span>
                    </div>
                    <button type='submit' class='translate' tabindex='-1'></button>
                </form>
                <div id='contenido_filtrar'></div>
            </div>
        </div>
    </div>
</div>
<div id="tabla_filtrar" class="hidden">
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap text-middle text-center width-none">Imagen</th>
					<th class="text-nowrap text-middle text-center">Código</th>
					<th class="text-nowrap text-middle text-center">Producto</th>
					<th class="text-nowrap text-middle text-center">Categoría</th>
					<th class="text-nowrap text-middle text-center">Stock</th>
					<th class="text-middle text-center" width="18%">Precio</th>
					<th class="text-nowrap text-middle text-center width-none">Acciones</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<table class="hidden">
	<tbody id="fila_filtrar" data-negativo="<?= imgs; ?>/" data-positivo="<?= files; ?>/productos/">
		<tr>
			<td class="text-nowrap text-middle text-center width-none">
				<img src="" class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="75" height="75">
			</td>
			<td class="text-nowrap text-middle" data-codigo=""></td>
			<td class="text-middle">
				<em></em>
				<span class="hidden" data-nombre=""></span>
			</td>
			<td class="text-nowrap text-middle"></td>
			<td class="text-nowrap text-middle text-right" data-stock=""></td>
			<td class="text-middle text-right" data-valor=""></td>
			<td class="text-nowrap text-middle text-center width-none">
				<button type="button" class="btn btn-warning" data-vender="" onclick="vender(this);">Vender</button>
				<button type="button" class="btn btn-default" data-actualizar="" onclick="actualizar(this, <?= $id_almacen ?>);calcular_descuento()">Actualizar</button>
			</td>
			<td class="hidden" data-cant=""></td>
			<td class="hidden" data-stock2=""></td>
		</tr>
		<tr>
			<td colspan="6" class="text-nowrap text-middle text-center width-none" data-desc="">
				<em2></em2>
			</td>
		</tr>
	</tbody>
</table>
<?php }else{?>
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="glyphicon glyphicon-home"></i> Notas</h3>
            </div>
            <div class="panel-body">
                <div class="alert alert-danger">
				<strong>&iexcl;Advertencia!</strong>
                    <p>Usted no puede realizar esta operación:</p>
                    <ul>
                        <li>No tiene permiso para editar.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php }?>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script src='<?= js; ?>/selectize.min.js'></script>
<script src='<?= js; ?>/bootstrap-notify.min.js'></script>
<script src='<?= js; ?>/buzz.min.js'></script>
<script>
    $(function() {
        calcular_total();		
        
        var $cliente = $('#cliente'),
            $nit_ci = $('#nit_ci'),
            $nombre_cliente = $('#nombre_cliente');
            
        var $id_cliente = $('#id_cliente');
        var $credito = $('#credito'); // para credito
    
        var blup = new buzz.sound('<?= media; ?>/blup.mp3');
		
        $cliente.selectize({
            persist: false,
            createOnBlur: true,
            create: false,
            onInitialize: function() {
                $cliente.css({
                    display: 'block',
                    left: '-10000px',
                    opacity: '0',
                    position: 'absolute',
                    top: '-10000px'
                });
            },
            onChange: function() {
                $cliente.trigger('blur');
            },
            onBlur: function() {
                $cliente.trigger('blur');
            }
        }).on('change', function(e) {
            var valor = $(this).val();
            valor = valor.split('|');
            $(this)[0].selectize.clear();
            if (valor.length != 1) {
                $nit_ci.prop('readonly', true);
                $nombre_cliente.prop('readonly', true);
                $nit_ci.val(valor[0]);
                $nombre_cliente.val(valor[1]);
                
                $id_cliente.val(valor[5]);
                
                $credito.val(valor[7]);
                $('#borrar').remove('#borrar')
                if(valor[7] == true || valor[7] == 1 || valor[7] == '1') {
                    $('#cred').prepend(' <div class="alert alert-info" id="borrar"> <b>Forma de pago: </b> El cliente tiene un contrato de créditos de: '+ valor[8] + ' días.  </div> ');
                } else {
                    $('#cred').prepend(' <div class="alert alert-success" id="borrar"> <b>Forma de pago: </b> El cliente no tiene contrato de créditos. </div> ');
                }
                calcular_descuento_total();
            
            
            } else {
                $nit_ci.prop('readonly', false);
                $nombre_cliente.prop('readonly', false);
                if (es_nit(valor[0])) {
                    $nit_ci.val(valor[0]);
                    $nombre_cliente.val('').focus();
                } else {
                    $nombre_cliente.val(valor[0]);
                    $nit_ci.val('').focus();
                }
            }
        });
        var $form_filtrar = $('#form_buscar_0, #form_buscar_1'),
            $contenido_filtrar = $('#contenido_filtrar'),
            $mensaje_filtrar = $('#mensaje_filtrar'),
            $fila_filtrar = $('#fila_filtrar'),
            $tabla_filtrar = $('#tabla_filtrar');
        $form_filtrar.on('submit', function(e) {
			e.preventDefault();
			var $this, url, busqueda;
			$this = $(this);
			url = $this.attr('action');
			busqueda = $this.find(':text').val();
			$this.find(':text').attr('value', '');
			$this.find(':text').val('');
			if ($.trim(busqueda) != '') {
				var id_almacen = "<?=$Egreso['id_almacen']?>";
				$.ajax({
					type: 'post',
					dataType: 'json',
					url: url,
					data: {
						busqueda: busqueda,
						almacen: id_almacen
					}
				}).done(function(productos) {
					if (productos.length) {
						var $ultimo;
						var $ultimo2;
						$contenido_filtrar.html($tabla_filtrar.html());
						for (var i in productos) {
							if ((parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos)) > 0) {
								productos[i].imagen = (productos[i].imagen == '') ? $fila_filtrar.attr('data-negativo') + 'image.jpg' : $fila_filtrar.attr('data-positivo') + productos[i].imagen;
								productos[i].codigo = productos[i].codigo;
								$contenido_filtrar.find('tbody').append($fila_filtrar.html());
								$contenido_filtrar.find('tbody tr:last').attr('data-busqueda', productos[i].id_producto);

								if (productos[i].promocion === 'si') {
									$ultimo = $contenido_filtrar.find('tbody tr:nth-last-child(2)').children().addClass('warning');
								} else {
									$ultimo = $contenido_filtrar.find('tbody tr:nth-last-child(2)').children();
								}
								$ultimo2 = $contenido_filtrar.find('tbody tr:last').children();
								$ultimo2.eq(0).find('em2').text(productos[i].descripcion);
								$ultimo.eq(0).find('img').attr('src', productos[i].imagen);
								$ultimo.eq(1).attr('data-codigo', productos[i].id_producto);
								$ultimo.eq(1).text(productos[i].codigo);
								$ultimo.eq(2).find('em').text(productos[i].nombre);
								$ultimo.eq(2).find('span').attr('data-nombre', productos[i].id_producto);
								$ultimo.eq(2).find('span').text(productos[i].nombre_factura);
								$ultimo.eq(3).text(productos[i].categoria);
								var str = productos[i].unidade;

								if (!str) {
									str = '';
									str = '*(1)' + productos[i].unidad + ':' + productos[i].precio_actual;
								} else {
									str = '*' + '(1)' + productos[i].unidad + ':' + productos[i].precio_actual + '\n ' + ' *(' + str;
								}
								var res = str.replace(/&/g, "\n *(");
								$ultimo.eq(4).attr('data-stock', productos[i].id_producto);
								$ultimo.eq(4).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
								$ultimo.eq(5).css("font-weight", "bold");
								$ultimo.eq(5).css("font-size", "0.8em");
								$ultimo.eq(5).attr('data-valor', productos[i].id_producto);
								$ultimo.eq(5).text(res);
								$ultimo.eq(6).find(':button:first').attr('data-vender', productos[i].id_producto);
								$ultimo.eq(6).find(':button:last').attr('data-actualizar', productos[i].id_producto);
								$ultimo.eq(7).attr('data-cant', productos[i].id_producto);
								$ultimo.eq(7).text(productos[i].cantidad2);
								$ultimo.eq(8).attr('data-stock2', productos[i].id_producto);
								$ultimo.eq(8).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
							}
						}
						if (productos.length == 1) {
							// $contenido_filtrar.find('table tbody tr button').trigger('click');
							adicionar_producto(productos[i].id_producto);
						}
						$.notify({
							message: 'La operación fue ejecutada con éxito, se encontraron ' + productos.length + ' resultados.'
						}, {
							type: 'success'
						});
						blup.stop().play();
					} else {
						$contenido_filtrar.html($mensaje_filtrar.html());
					}
				}).fail(function() {
					$contenido_filtrar.html($mensaje_filtrar.html());
					$.notify({
						message: 'La operación fallo exitosamente.'
					}, {
						type: 'danger'
					});
					blup.stop().play();
				});
			} else {
				$contenido_filtrar.html($mensaje_filtrar.html());
			}
		}).trigger('submit');
        var $modal_mostrar = $('#modal_mostrar'),
			$loader_mostrar = $('#loader_mostrar'),
			size, title, image;
		$modal_mostrar.on('hidden.bs.modal', function() {
			$loader_mostrar.show();
			$modal_mostrar.find('.modal-dialog').attr('class', 'modal-dialog');
			$modal_mostrar.find('.modal-title').text('');
		}).on('show.bs.modal', function(e) {
			size = $(e.relatedTarget).attr('data-modal-size');
			title = $(e.relatedTarget).attr('data-modal-title');
			image = $(e.relatedTarget).attr('src');
			size = (size) ? 'modal-dialog ' + size : 'modal-dialog';
			title = (title) ? title : 'Imagen';
			$modal_mostrar.find('.modal-dialog').attr('class', size);
			$modal_mostrar.find('.modal-title').text(title);
			$modal_mostrar.find('[data-modal-image]').attr('src', image);
		}).on('shown.bs.modal', function() {
			$loader_mostrar.hide();
		});

		$('.calcular_descuento').on('keyup blur', function() {
			calcular_descuento_total();
		})   
		
		$.validate({
            modules: 'basic'
        });

		$("#formulario").on('submit', function(e) {
			e.preventDefault();
		});

		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function() {
				guardar_nota();
			}
		});

    });


    function es_nit(texto) {
		var numeros = '0123456789';
		for (i = 0; i < texto.length; i++) {
			if (numeros.indexOf(texto.charAt(i), 0) != -1) {
				return true;
			}
		}
		return false;
	}

    function adicionar_producto(id_producto) {
		var $ventas = $('#ventas tbody');
		var $producto = $ventas.find('[data-producto=' + id_producto + ']');
		//id_producto = id_producto[id_producto.length - 1];
		//console.log(id_producto);
		//console.log('hola');
		var $cantidad = $producto.find('[data-cantidad]');
		var numero = $ventas.find('[data-producto]').size() + 1;
		var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
		var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
		var cantidad2 = $.trim($('[data-cant=' + id_producto + ']').text());
		var stock = $.trim($('[data-stock=' + id_producto + ']').text());
		var valor = $.trim($('[data-valor=' + id_producto + ']').text());
		var plantilla = '';
		var cantidad;
		var $modcant = $('#modcant');
		//console.log(aa);
		var posicion = valor.indexOf(':');
		var porciones = valor.split('*');

		cantidad2 = '1*' + cantidad2;
		z = 1;
		var porci2 = cantidad2.split('*');
		//console.log(porci2);
		if ($producto.size()) {
			cantidad = $.trim($cantidad.val());
			cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
			cantidad = (cantidad < 9999999) ? cantidad + 1 : cantidad;
			$cantidad.val(cantidad).trigger('blur');
			//console.log('hola');
		} else {
			plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
				'<td class="text-nowrap text-middle"><b>' + numero + '</b></td>' +
				'<td class="text-nowrap text-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
				'<td class="text-middle">'+nombre+'<input type="hidden" value=\'' + nombre + '\' name="nombres[]" class="form-control" data-validation="required"></td>' +
				'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
				
			if (porciones.length > 2) {
				plantilla = plantilla + '<td><select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control input-xs text-right"  >';
				aparte = porciones[1].split(':');
				for (var ic = 1; ic < porciones.length; ic++) {
					parte = porciones[ic].split(':');
					oparte = parte[0].split(')');
					plantilla = plantilla + '<option value="' + oparte[1] + '" data-xyyz="' + stock + '" data-yyy="' + $.trim(parte[1]) + '" data-yyz="' + porci2[ic - 1] + '" >' + oparte[1] + '</option>';
				}
				plantilla = plantilla + '</select></td>' +
					'<td><input type="text" value="' + $.trim(aparte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + $.trim(aparte[1]) + '" data-validation="required number" data-validation-allowing="range[0.1;10000000.00],float"    data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
			} else {
				sincant = porciones[1].split(')');
				//            console.log(sincant);
				parte = sincant[1].split(':');
				plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" data-xyyz="' + stock + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>' +
					'<td data-xyyz="' + stock + '" ><input type="text" value="' + $.trim(parte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off"  data-precio="' + $.trim(parte[1]) + '" data-cant2="1" data-validation="required number" data-validation-allowing="range[0.1;10000000.00],float" data-validation-error-msg="Debe ingresar un número decimal positivo mayor que 0 y menor que 10000000" onkeyup="calcular_importe(' + id_producto + ')"></td>';
			}
			//'<td class="text-middle"><input type="text" value="' + valor + '" name="precios[]" class="form-control text-right" style="width: 100px;" autocomplete="off" data-precio="' + valor + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
			plantilla = plantilla + '<td class="text-nowrap text-middle text-right" data-importe="">0.00</td>' +
				'<td class="text-nowrap text-middle text-center">' +
					'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
				'</td>' +
				// '<td class="text-nowrap text-center">'+
                //     '<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>'+
                // '</td>'+
				'</tr>';

			$ventas.append(plantilla);

			$ventas.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function() {
				$(this).select();
			});

			$ventas.find('[data-xxx]').on('change', function() {
				var v = $(this).find('option:selected').attr('data-yyy');

				var st = $(this).find('option:selected').attr('data-xyyz');

				$(this).parent().parent().find('[data-precio]').val(v);
				$(this).parent().parent().find('[data-precio]').attr(v);
				$(this).parent().parent().find('[data-precio]').attr(v);
				var z = $(this).find('option:selected').attr('data-yyz');
				var x = $.trim($('[data-stock2=' + id_producto + ']').text());
				var ze = Math.trunc(x / z);
				var zt = Math.trunc(st / z);
				$.trim($('[data-stock=' + id_producto + ']').text(ze));
				$(this).parent().parent().find('[data-cantidad]').attr('data-validation-allowing', 'range[1;' + zt + ']');
				$(this).parent().parent().find('[data-cantidad]').attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + zt);
				//console.log($(this).parent().parent().find('[data-cantidad]').attr('data-validation-allowing'));
				calcular_importe(id_producto);
			});

			$ventas.find('[title]').tooltip({
				container: 'body',
				trigger: 'hover'
			});

			$.validate({
				form: '#formulario',
				modules: 'basic',
				onSuccess: function() {
					guardar_nota();
				}
			});
		}

		calcular_importe(id_producto);
	}
    

    
	function eliminar_producto(id_producto) {
		bootbox.confirm('&iquest;Est&aacute; seguro que desea eliminar el producto?', function(result) {
			if (result) {
				$('[data-producto=' + id_producto + ']').remove();
				renumerar_productos();
				calcular_total();
				calcular_descuento_total();
			}
		});
	}

	function renumerar_productos() {
		var $ventas = $('#ventas tbody');
		var $productos = $ventas.find('[data-producto]');
		$productos.each(function(i) {
			$(this).find('td:first').text(i + 1);
		});
	}

	function descontar_precio(id_producto) {
		var $producto = $('[data-producto=' + id_producto + ']');
		var $precio = $producto.find('[data-precio]');
		var $descuento = $producto.find('[data-descuento]');
		var precio, descuento;

		precio = $.trim($precio.attr('data-precio'));
		precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0;
		descuento = $.trim($descuento.val());
		descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
		precio = precio - (precio * descuento / 100);
		$precio.val(precio.toFixed(2));

		calcular_importe(id_producto);
	}

	function calcular_importe(id_producto) {
		var $producto = $('[data-producto=' + id_producto + ']');
		var $cantidad = $producto.find('[data-cantidad]');
		var $precio = $producto.find('[data-precio]');
		var $descuento = $producto.find('[data-descuento]');
		var $importe = $producto.find('[data-importe]');
		var cantidad, precio, importe, fijo;

		fijo = $descuento.attr('data-descuento');
		fijo = ($.isNumeric(fijo)) ? parseFloat(fijo) : 0;
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		precio = $.trim($precio.val());
		precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
		descuento = $.trim($descuento.val());
		descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
		importe = cantidad * precio;
		importe = importe.toFixed(2);
		$importe.text(importe);

		calcular_total();        
		calcular_descuento_total();
	}

	function calcular_total() {
		var $ventas = $('#ventas tbody');
		var $total = $('[data-subtotal]:first');
		var $importes = $ventas.find('[data-importe]');
		var importe, total = 0;

		$importes.each(function(i) {
			importe = $.trim($(this).text());
			importe = parseFloat(importe);
			total = total + importe;
		});

		$total.text(total.toFixed(2));
		$('[data-ventas]:first').val($importes.size()).trigger('blur');
		$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
		$('[data-total-descuento]:first').val(total.toFixed(2)).trigger('blur');
		// ::BECA
		calcular_descuento_total();
	}

	function tipo_descuento() {
		var descuento = $('#tipo').val();
		$('#descuento_bs').val('0');
		$('#descuento_porc').val('0');		
		if (descuento == 0) {
			console.log(0);
			$('#div-descuento').hide();
		} else if (descuento == 1) {
			console.log(1);
			$('#div-descuento').show();
		}
		calcular_descuento_total();		
	}

	// function calcular_descuento() {
	// 	var $ventas = $('#ventas tbody');
	// 	var $total = $('[data-subtotal]:first');
	// 	var $importes = $ventas.find('[data-importe]');

	// 	var descuento = $('#descuento_porc').val();

	// 	var importe, total = 0;

	// 	$importes.each(function(i) {
	// 		importe = $.trim($(this).text());
	// 		importe = parseFloat(importe);
	// 		total = total + importe;
	// 	});
	// 	$total.text(total.toFixed(2));
	// 	var importe_total = total.toFixed(2);
	// 	//console.log(importe_total);

	// 	var total_descuento = 0,
	// 		formula = 0,
	// 		total_importe_descuento = 0;
	// 	console.log(descuento + 'jhfhgdghd');

    //     if (descuento == null) {
	// 		var descuento_bs = $('#descuento_bs').val();
	// 		//console.log(descuento_bs+'vacio');
	// 		// descuento_bs = (descuento_bs == 0 || descuento_bs == '')?0:descuento_bs;
	// 		total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

	// 		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
	// 		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));

	// 	} else if (descuento < 0) {
	// 		var descuento_bs = $('#descuento_bs').val();
	// 		//console.log(descuento_bs+'vacio');
	// 		// escuento_bs = (descuento_bs == 0 || descuento_bs == '')?0:descuento_bs;
	// 		total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

	// 		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
	// 		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));

	// 	} else if (descuento != "") {
	// 		//console.log(descuento+'dif vacio');
	// 		//var total_descuento=0, formula=0, total_importe_descuento=0;
	// 		//total_descuento=descuento*100;
	// 		//formula=(descuento/importe_total)*100;
	// 		formula = (descuento / 100) * importe_total;

	// 		total_importe_descuento = parseFloat(importe_total) - parseFloat(formula);

	// 		$('#descuento_bs').val(formula.toFixed(2));
	// 		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
	// 		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));
	// 	}
	// }

	function guardar_nota() {
		
		var data = $('#formulario').serialize();
		console.log(data);

		$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/operaciones/notas_actualizar',
			data: data
		}).done(function(venta) {

			switch (venta.status) {
				case 'success':
						$.notify({
								message: 'La nota de remisión fue realizada satisfactoriamente.'
							}, {
								type: 'success'
							});
						$('#loader').fadeOut(100);
						window.location.href = '?/operaciones/notas_listar';
					break;
				case 'invalid':
					$('#loader').fadeOut(100);
							$.notify({								
								message: 'Productos observados' + venta.responce
							}, {
								type: 'warning'
							});
					break;			
				default:
				$('#loader').fadeOut(100);
					$.notify({
						message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de remisión, verifique si la se guardó parcialmente.'
					}, {
						type: 'danger'
					});
					break;
			}

		}).fail(function() {
			$('#loader').fadeOut(100);
			$.notify({
				message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de remisión, verifique si la se guardó parcialmente.'
			}, {
				type: 'danger'
			});
		});
	}

	function imprimir_nota(nota) {
		$.open('?/notas/imprimir/' + nota, true);
		window.location.reload();
	}

	function vender(elemento) {
		var $elemento = $(elemento),
			vender;
		vender = $elemento.attr('data-vender');
		adicionar_producto(vender);
	}

	function actualizar(elemento, almacen) {
		var $elemento = $(elemento),
			actualizar;
		actualizar = $elemento.attr('data-actualizar');

		$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/notas/actualizar',
			data: {
				id_producto: actualizar,
				almacen: almacen
			}
		}).done(function(producto) {
			if (producto) {
				var $busqueda = $('[data-busqueda="' + producto.id_producto + '"]');
				var precio = parseFloat(producto.precio).toFixed(2);
				var stock = parseInt(producto.stock);

				$busqueda.find('[data-stock]').text(stock);
				$busqueda.find('[data-valor]').text(precio);

				var $producto = $('[data-producto=' + producto.id_producto + ']');
				var $cantidad = $producto.find('[data-cantidad]');
				var $precio = $producto.find('[data-precio]');

				if ($producto.size()) {
					$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
					$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock);
					$precio.val(precio);
					$precio.attr('data-precio', precio);
					descontar_precio(producto.id_producto);
				}

				$.notify({
					message: 'El stock y el precio del producto se actualizaron satisfactoriamente.'
				}, {
					type: 'success'
				});
			} else {
				$.notify({
					message: 'Ocurrió un problema durante el proceso, es posible que no existe un almacén principal.'
				}, {
					type: 'danger'
				});
			}
		}).fail(function() {
			$.notify({
				message: 'Ocurrió un problema durante el proceso, no se pudo actualizar el stock ni el precio del producto.'
			}, {
				type: 'danger'
			});
		}).always(function() {
			$('#loader').fadeOut(100);
		});
	}


	function calcular_descuento_total() {
		var $ventas = $('#ventas tbody');
		var $total = $('[data-subtotal]:first');
		var $importes = $ventas.find('[data-importe]');

		var descuento = $('#descuento_porc').val();

		var importe, total = 0;

		$importes.each(function(i) {
			importe = $.trim($(this).text());
			importe = parseFloat(importe);
			total = total + importe;
		});
		if(total == 0){
			alert('¡Advertencia! El monto total no debe ser igual a 0, si preciona guardar no habrá cambios en la base de datos.');			
		}
		
		$total.text(total.toFixed(2));
		var importe_total = total.toFixed(2);
		//console.log(importe_total);

		var total_descuento = 0,
			formula = 0,
			total_importe_descuento = 0;
		// Éste código ya no es necesario pues los valores siempre serán 0 (nunca habrá valores null o menor que 0) ::BECA
		if (descuento == null || descuento == '') {
			var descuento_bs = $('#descuento_bs').val();
			//console.log(descuento_bs+'vacio');
			// descuento_bs = (descuento_bs == 0 || descuento_bs == '')?0:descuento_bs;
			total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

			$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
			$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));

		} else if (descuento < 0) {
			var descuento_bs = $('#descuento_bs').val();
			//console.log(descuento_bs+'vacio');
			// escuento_bs = (descuento_bs == 0 || descuento_bs == '')?0:descuento_bs;
			total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

			$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
			$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));		

		} else if (descuento != "") {

			//console.log(descuento+'dif vacio');
			//var total_descuento=0, formula=0, total_importe_descuento=0;
			//total_descuento=descuento*100;
			//formula=(descuento/importe_total)*100;

			if(descuento == 0){
				var descuento_bs = $('#descuento_bs').val();
				if(descuento_bs == null || descuento_bs == ''){
					descuento_bs = 0;
				}			
				total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);
								
				if(total_importe_descuento < 0){
					alert('El descuento está excediendo el Monto Total');
					var aux = 0;
					$('#descuento_bs').val(aux);
				}

				$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
				$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));
			}else{
				formula = (descuento / 100) * importe_total;				
				total_importe_descuento = parseFloat(importe_total) - parseFloat(formula);

				$('#descuento_bs').val(formula.toFixed(2));
				$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
				$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));
			}
			
		} 
	}
</script>
<?php
require_once show_template('footer-advanced');