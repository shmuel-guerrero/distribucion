<?php

// Obtiene el id_ingreso
$id_ingreso = (isset($params[0])) ? $params[0] : 0;

// Verificamos que el ingreso no tenga cuotas pagadas
$pagado = $db->query("SELECT COUNT(id_pago) as pagado FROM inv_pagos p LEFT JOIN inv_pagos_detalles d ON p.id_pago = d.pago_id WHERE p.tipo = 'Ingreso' AND p.movimiento_id = $id_ingreso  AND d.estado = 1")->fetch_first();

if($pagado['pagado'] > 1) {
    // Define la variable para mostrar los cambios
    $_SESSION[temporary] = array(
        'alert' => 'danger',
        'title' => 'Adición insatisfactoria!',
        'message' => 'El registro no puede ser editado porque ya cuenta con pagos de cuotas.'
    );
    redirect('?/ingresos/listar');
}

// Obtiene el ingreso
$ingreso = $db->select('i.*, a.principal')
              ->from('inv_ingresos i')
              ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
              ->where('i.id_ingreso', $id_ingreso)
              ->fetch_first();
$detalles = $db->query('select * from inv_ingresos_detalles where ingreso_id='.$ingreso['id_ingreso'].'')->fetch();

$pagos = $db->from('inv_pagos p')
            ->join('inv_pagos_detalles d','p.id_pago = d.pago_id','left')
            ->where('p.movimiento_id', $ingreso["id_ingreso"])
            ->where('p.tipo', 'Ingreso')
            ->order_by('d.id_pago_detalle', 'ASC')
            ->fetch();

// echo json_encode($pagos); die();

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Verifica si existe el almacen
if ($id_almacen != 0) {
    // Obtiene los productos
    $productos = $db->query("SELECT p.id_producto, p.promocion, z.id_asignacion, z.unidad_id, z.unidade, z.cantidad2, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, (IFNULL(s.cantidad_egresos, 0) + IFNULL(sp.cantidad_promocion, 0) + IFNULL(spr.cantidad_venta_promo, 0)) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
					FROM inv_productos p
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
						   FROM inv_ingresos_detalles d
						   LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
						   WHERE i.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
						   FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
						   WHERE e.almacen_id = '$id_almacen' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
                    LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_venta_promo
							FROM inv_egresos_detalles d 
                            LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                            LEFT JOIN inv_productos pr ON pr.id_producto = d.promocion_id
						   	WHERE e.almacen_id = '$id_almacen' AND  d.promocion_id > 2 AND e.anulado != 3 AND pr.fecha_limite < CURDATE() GROUP BY d.producto_id) AS spr ON spr.producto_id = p.id_producto
                    LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad*a.cantidad) AS cantidad_promocion
						    FROM inv_ingresos_detalles a 
                            LEFT JOIN inv_ingresos b on b.id_ingreso = a.ingreso_id 
                            INNER JOIN inv_promociones d ON d.id_promocion = a.producto_id
                            INNER JOIN inv_productos c ON c.id_producto = d.id_promocion
                            INNER JOIN inv_productos e ON e.id_producto = d.producto_id
                            WHERE b.almacen_id = '$id_almacen' AND e.fecha_limite > CURDATE() GROUP BY d.producto_id) AS sp ON sp.producto_id = p.id_producto
					LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
					LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.cantidad_unidad,')',w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade, GROUP_CONCAT(w.cantidad_unidad SEPARATOR '*') AS cantidad2
					   FROM (SELECT *
							FROM inv_asignaciones q
								  LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad AND q.visible = 's' 
										 ORDER BY u.unidad DESC) w GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id WHERE p.promocion != 'si' AND p.eliminado = 0")->fetch();
} else {
    $productos = null;
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene el modelo almacenes
$almacenes = $db->from('inv_almacenes')->order_by('almacen')->fetch();

// Obtiene los proveedores
// $proveedores = $db->select('id_proveedor, proveedor as nombre_proveedor')
//                   ->from('inv_proveedores')
//                   ->group_by('proveedor')
//                   ->order_by('proveedor asc')
//                   ->fetch();

$proveedores = $db->select('id_proveedor, proveedor as nombre_proveedor')->from('inv_proveedores')->order_by('proveedor asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
    <style>
        .table-xs tbody {
            font-size: 12px;
        }
        .input-xs {
            height: 22px;
            padding: 1px 5px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 3px;
        }
    </style>
    <div class="row" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="glyphicon glyphicon-list"></span>
                        <strong>Datos del ingreso</strong>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-danger">
                        <strong>Advertencia!</strong>
                        <p>
                            <ul>
                                <li>La modificación del registro y sus detalles repercutira en el sotck de inventarios y los reportes correspondientes.</li>
                                <li>La modificación del registro y sus detalles repercutira en el crédito si posee.</li>
                            </ul>                            
                        </p>
                    </div>
                    <form method="post" action="?/ingresos/guardar" id="formulario" class="form-horizontal">
                        <div class="form-group hidden">
                            <label for="almacen" class="col-md-4 control-label">Almacén:</label>
                            <div class="col-md-8">
                                <select name="almacen_id" id="almacen" class="form-control" data-validation="required number">
                                    <option value="">Seleccionar</option>
                                    <?php  //var_dump($ingreso);die();
                                    foreach ($almacenes as $elemento) {

                                        if($ingreso['almacen_id'] == $elemento['id_almacen']) {
                                        ?>
                                        <option value="<?= $elemento['id_almacen']; ?>" selected="selected"><?= escape($elemento['almacen'] . (($elemento['principal'] == 'S') ? ' (principal)' : '')); ?></option>
                                    <?php } else { ?>

                                        <option value="<?= $elemento['id_almacen']; ?>"><?= escape($elemento['almacen'] . (($elemento['principal'] == 'S') ? ' (principal)' : '')); ?></option>
                                        <?php
                                    }
                                } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="proveedor" class="col-sm-4 control-label">Proveedor:</label>
                            <div class="col-sm-8">
                                <select name="id_proveedor" id="proveedor" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100">
                                    <!--<option value="">Buscar</option>-->
                                    <?php 
                                    foreach ($proveedores as $elemento) {
                                        
                                        if( $elemento['nombre_proveedor'] == $ingreso['nombre_proveedor'])
                                        { 
                                        ?>
                                            <option value="<?= escape($elemento['id_proveedor']); ?>" selected="selected"><?= escape($elemento['nombre_proveedor']); ?></option>
                                        <?php 
                                        } else {
                                        ?>
                                            <option value="<?= escape($elemento['id_proveedor']); ?>"><?= escape($elemento['nombre_proveedor']); ?></option>
                                        <?php
                                        }
                                    } 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion" class="col-sm-4 control-label">Descripción:</label>
                            <div class="col-sm-8">
                                <textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true"><?= $ingreso['descripcion']?></textarea>

                                <input type="hidden" name="id_ingreso" value="<?= $ingreso['id_ingreso']?>">
                            </div>

                        </div>
                        <div class=" margin-none">
                            <table id="compras" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
                                <thead>
                                <tr class="active">
                                    <th class="text-nowrap">Código</th>
                                    <th class="text-nowrap">Nombre</th>
                                    <th class="text-nowrap hidden">Lote</th>
                                    <th class="text-nowrap hidden">F. elaboración</th>
                                    <th class="text-nowrap hidden">F. vencimiento</th>
                                    <th class="text-nowrap hidden">Nro. DUI</th>
                                    <th class="text-nowrap hidden">Contenedor</th>
                                    <th class="text-nowrap hidden">Factura</th>
                                    <th class="text-nowrap">Cantidad</th>
                                    <th class="text-nowrap">Costo</th>
                                    <th class="text-nowrap">Importe</th>
                                    <th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr class="active">
                                    <th class="text-nowrap text-right" colspan="4">Importe total <?= escape($moneda); ?></th>
                                    <th class="text-nowrap text-right" data-subtotal="">0.00</th>
                                    <th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
                                </tr>
                                </tfoot>
                                <tbody>

                                <?php foreach ($detalles as $detalle) {    ?>
                                    
                                <tr class="active" data-producto="<?= $detalle['producto_id']?>">

                                    <td class="text-nowrap">
                                        <input type="text" value="<?= $detalle['producto_id']; ?>" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">
                                        <?php $cod = $db->query('select codigo from inv_productos where id_producto='.$detalle['producto_id'].'')->fetch_first();
                                        echo $cod['codigo']; ?>
                                    </td>
                                    <td>
                                        <?php $nom = $db->query('select nombre from inv_productos where id_producto='.$detalle['producto_id'].'')->fetch_first();
                                        echo $nom['nombre']; ?>
                                    </td>
                                    <td class="hidden">
                                        <input type="text" value="<?= $detalle['lote2']; ?>" name="lote[]" class="form-control input-xs text-right">
                                        <?php if($detalle['lote_cantidad'] == $detalle['cantidad']){ ?>
                                        <input type="hidden" value="a" name="lote_cantidad[]" class="form-control input-xs text-right">
                                        <input type="hidden" value="0" name="cantidad_ant[]" class="form-control input-xs text-right">
                                            <input type="hidden" value="0" name="lote_ant[]" class="form-control input-xs text-right">
                                        <?php }else{ ?>
                                            <input type="hidden" value="<?= $detalle['lote_cantidad']; ?>" name="lote_cantidad[]" class="form-control input-xs text-right">
                                            <input type="hidden" value="<?= $detalle['cantidad']; ?>" name="cantidad_ant[]" class="form-control input-xs text-right">
                                            <input type="hidden" value="<?= $detalle['lote']; ?>" name="lote_ant[]" class="form-control input-xs text-right">
                                        <?php } ?>
                                    </td>
                                    <td class="hidden">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <input type="text" name="elaboracion[]" value="<?= $detalle['elaboracion']; ?>" class="form-control input-xs text-right" data-fecha="">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="hidden">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <input type="text" name="fechas[]" value="<?= $detalle['vencimiento']; ?>" class="form-control input-xs text-right" data-fecha="">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="hidden">
                                        <input type="text" value="<?= $detalle['factura']; ?>" name="facturas[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-contenedor="" data-validation-error-msg="Debe ser número entero positivo">
                                    </td>
                                    <td>
                                        <?php
                                        if($detalle['cantidad'] == $detalle['lote_cantidad']){
                                            $stocks = 1;
                                        }else{
                                            $stocks = $detalle['cantidad'] - $detalle['lote_cantidad'];
                                        }
                                        ?>
                                        <input type="text" value="<?= $detalle['cantidad']; ?>" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[<?= $stocks; ?>;1000000]" data-validation-error-msg="Debe ser un número positivo entre <?= $stocks; ?> y 1000000" onkeyup="calcular_importe(<?= $detalle['producto_id']?>)">
                                    </td>

                                    <td>
                                        <input type="text" value="<?= $detalle['costo']; ?>" name="costos[]" class="form-control input-xs text-right" autocomplete="off" data-costo="" data-validation="required number" data-validation-allowing="range[0.00;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onkeyup="calcular_importe(<?= $detalle['producto_id']?>)" onblur="redondear_importe(<?= $detalle['producto_id']?>)">
                                    </td>

                                    <td class="text-nowrap text-right" data-importe="<?= ($detalle['costo']*$detalle['cantidad']) ?>"><?= number_format($detalle['costo']*$detalle['cantidad'] ,2,'.','') ?></td>

                                    <td class="text-nowrap text-center"><?php if($detalle['cantidad'] == $detalle['lote_cantidad']){ ?><button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(<?= $detalle['producto_id']?>)"><span class="glyphicon glyphicon-remove"></span></button><?php } ?></td>
                                </tr>

                            <?php } ?>

                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-compras="" data-validation="required number" data-validation-allowing="range[1;150]" data-validation-error-msg="Debe existir como mínimo 1 producto y como máximo 50 productos">
                                <input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="El costo total de la compra debe ser mayor a cero y menor a 1000000.00">
                            </div>
                        </div>
                        <div class="">
                            <div class="col-xs-6 text-left hidden">
                                <label for="almacen" class="col-md-5 control-label">Almacén transitorio:</label>
                                <div class="col-md-7 right">
                                    <div class="input-group">
                                  <span class="input-group-addon">
                                    <input type="checkbox" name="reserva" aria-label="...">
                                  </span>
                                        <input type="text" name="des_reserva" placeholder="Motivo" class="form-control" aria-label="...">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="almacen" class="col-md-4 control-label">Forma de Pago:</label>
                                <div class="col-md-8">
                                    <select name="forma_pago" id="forma_pago" class="form-control" data-validation="required number" onchange="set_plan_pagos()">
                                        <option value="1" <?= ($ingreso['plan_de_pagos'] == 'no') ? 'selected' : '' ?>>Pago Completo</option>
                                        <option value="2" <?= ($ingreso['plan_de_pagos'] == 'si') ? 'selected' : '' ?> >crédito</option>
                                    </select>
                                </div>
                            </div>

                            <div id="plan_de_pagos" style="display:none;">
                                <div class="form-group">
                                    <label for="almacen" class="col-md-4 control-label">Nro Cuotas:</label>
                                    <div class="col-md-8">
                                        <input type="text" value="<?= ($ingreso['plan_de_pagos'] == 'si') ? count($pagos) : '1' ?>" id="nro_cuentas" name="nro_cuentas" class="form-control text-right" autocomplete="off" data-cuentas="" data-validation="required number" data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo" onkeyup="set_cuotas()">
                                    </div>
                                </div>
                                <table id="cuentasporpagar" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
                                    <thead>
                                        <tr class="active">
                                            <th class="text-nowrap text-center col-xs-4">Detalle</th>
                                            <th class="text-nowrap text-center col-xs-4">Fecha</th>
                                            <th class="text-nowrap text-center col-xs-4">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for($i=1;$i<=3;$i++){ ?>
                                            <tr class="active cuotaclass">
                                                <?php if($i==1){ ?>
                                                    <td class="text-nowrap h3 text-success text-right text-uppercase" valign="center">
                                                        <div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Pago Inicial:</div>
                                                    </td>
                                                <?php } else{ ?>
                                                    <td class="text-nowrap text-right text-uppercase" valign="center">
                                                        <div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Cuota <?= $i ?>:</div>
                                                    </td>
                                                <?php } ?>
                                                <td><div data-cuota="<?= $i ?>" class="cuota_div"><div class="col-sm-12">
                                                    <input id="inicial_fecha_<?= $i ?>" name="fecha[]" value="<?= (count($pagos) > 0 && count($pagos) >= $i) ? $pagos[$i-1]['fecha'] : '' ?>" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" onchange="javascript:change_date(<?= $i ?>);" <?php if($i==1){ ?> data-validation="required" <?php } ?> onblur="javascript:change_date(<?= $i ?>);" 
                                                            <?php if($i>1){ ?>
                                                            disabled="disabled"
                                                            <?php } ?>
                                                    >
                                                </div></div></td>
                                                <td><div data-cuota="<?= $i ?>" class="cuota_div"><input type="text" value="<?= (count($pagos) > 0) ? $pagos[$i-1]['monto'] : '' ?>" name="cuota[]" class="form-control text-right monto_cuota" maxlength="7" autocomplete="off" data-montocuota="" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onchange="javascript:calcular_cuota('<?= $i ?>');"></div></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="active">
                                            <th class="text-nowrap text-right" colspan="2">Importe total <?= escape($moneda); ?></th>
                                            <th class="text-nowrap text-right" data-totalcuota=""><?= (count($pagos) > 0) ? $ingreso["monto_total"] : '0.00' ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <br>
                            </div>



                            <div class="col-xs-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <span class="glyphicon glyphicon-floppy-disk"></span>
                                    <span>Guardar</span>
                                </button>
                                <button type="reset" class="btn btn-default">
                                    <span class="glyphicon glyphicon-refresh"></span>
                                    <span>Restablecer</span>
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="glyphicon glyphicon-search"></span>
                        <strong>Búsqueda de productos</strong>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 text-right">
                            <a href="?/ingresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado de ingresos</span></a>
                        </div>
                    </div>
                    <hr>
                    <?php if ($productos) { ?>
                        <table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
                            <thead>
                            <tr class="active">
                                <th class="text-nowrap">Código</th>
                                <th class="text-nowrap">Nombre</th>
                                <th class="text-nowrap">Descripción</th>
                                <!--<th class="text-nowrap">Color</th>-->
                                <th class="text-nowrap">Tipo</th>
                                <th class="text-nowrap">Stock</th>
                                <th class="text-nowrap">Costo</th>
                                <th class="text-nowrap"><i class="glyphicon glyphicon-cog"></i></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($productos as $nro => $producto) { ?>
                                <tr class="<?php if($producto['promocion']=='si'){echo 'warning';}?>" >
                                    <td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
                                    <td data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre']); ?></td>
                                    <td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
                                    <!--<td class="text-nowrap"><?= escape($producto['color']); ?></td>-->
                                    <td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
                                    <td class="text-nowrap text-right"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>

                                    <?php 
                                    $precio_r = $db->query("SELECT d.costo FROM inv_ingresos_detalles d LEFT JOIN inv_ingresos i on i.id_ingreso = d.ingreso_id WHERE d.producto_id=".$producto['id_producto']." ORDER BY d.id_detalle DESC")->fetch_first();

                                    ?>
                                    <td class="text-nowrap text-right">
                                    <span style="display: none;" data-precio="<?= $producto['id_producto']; ?>"><?=$precio_r['costo'];?></span><?= escape($precio_r['costo']); ?></td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-xs btn-primary" data-comprar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Comprar"><span class="glyphicon glyphicon-share-alt"></span></button>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <div class="alert alert-danger">
                            <strong>Advertencia!</strong>
                            <p>No existen productos registrados en la base de datos.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= js; ?>/jquery.form-validator.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.es.js"></script>
    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/selectize.min.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
    <script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
    <script src="<?= js; ?>/moment.min.js"></script>
    <script src="<?= js; ?>/moment.es.js"></script>
    <script>
        $(function () {
            // set_cuotas();
            // 
            var formato = $('[data-formato]').attr('data-formato');
            var $inicial_fecha = new Array();

            for(i=1;i<36;i++){
                $inicial_fecha[i] = $('#inicial_fecha_'+i+'');
                $inicial_fecha[i].datetimepicker({
                    format: formato
                });
            }
            calcular_total();
            set_cuotas();
            set_plan_pagos();

            $('[data-comprar]').on('click', function () {
                adicionar_producto($.trim($(this).attr('data-comprar')));
            });

            $('#productos').dataTable({
                info: false,
                lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
                order: []
            });

            $('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');

            $.validate({
                modules: 'basic'
            });

            $('#formulario').on('reset', function () {
                //$('#compras tbody').find('[data-importe]').text('0.00');
                calcular_total();
            });

            $('#formulario :reset').trigger('click');

            calcular_total();
        });

        function adicionar_producto(id_producto) {
            var $producto = $('[data-producto=' + id_producto + ']');
            var $cantidad = $producto.find('[data-cantidad]');
            var $compras = $('#compras tbody');
            var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
            var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
            var precio = $.trim($('[data-precio=' + id_producto + ']').text());
            var plantilla = '';
            var cantidad;
            var formato = $('[data-formato]').attr('data-formato');

            if ($producto.size()) {
                cantidad = $.trim($cantidad.val());
                cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
                cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
                $cantidad.val(cantidad).trigger('blur');
            } else {
                plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
                '<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
                '<td>' + nombre + '</td>' +
                '<td class="hidden"><input type="text" value="" name="lote[]" class="form-control input-xs text-right"><input type="hidden" value="a" name="lote_cantidad[]" class="form-control input-xs text-right"></td><input type="hidden" value="0" name="cantidad_ant[]" class="form-control input-xs text-right"><input type="hidden" value="0" name="lote_ant[]" class="form-control input-xs text-right">'+
                '<td class="hidden"><div class="row"><div class="col-xs-12"><input type="text" name="elaboracion[]" value="<?= date('Y/m/d'); ?>" class="form-control input-xs text-right" data-fecha="" ></div></div></td>' +
                '<td class="hidden"><div class="row"><div class="col-xs-12"><input type="text" name="fechas[]" value="<?= date('Y/m/d'); ?>" class="form-control input-xs text-right" data-fecha="" ></div></div></td>' +
                '<td class="hidden"><input type="text" value="" name="facturas[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-contenedor=""  data-validation-error-msg="Debe ser número entero positivo" ></td>' +
                '<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-error-msg="Debe ser número entero positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
                '<td><input type="text" value="'+precio+'" name="costos[]" class="form-control input-xs text-right" autocomplete="off" data-costo="" data-validation="required number" data-validation-allowing="rnge[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')" onblur="redondear_importe(' + id_producto + ')"></td>' +
                '<td class="text-nowrap text-right" data-importe="">0.00</td>' +
                '<td class="text-nowrap text-center">' +
                '<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
                '</td>' +
                '</tr>';

                $compras.append(plantilla);

                $compras.find('[data-cantidad], [data-costo]').on('click', function () {
                    $(this).select();
                });

                $compras.find('[data-fecha]').datetimepicker({
                    format: formato
                });

                $compras.find('[title]').tooltip({
                    container: 'body',
                    trigger: 'hover'
                });

                $.validate({
                    modules: 'basic'
                });
            }

            calcular_importe(id_producto);
        }

        function eliminar_producto(id_producto) {
            bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
                if(result){
                    $('[data-producto=' + id_producto + ']').remove();
                    calcular_total();
                }
            });
        }

        function redondear_importe(id_producto) {
            var $producto = $('[data-producto=' + id_producto + ']');
            var $costo = $producto.find('[data-costo]');
            var costo;

            costo = $.trim($costo.val());
            costo = ($.isNumeric(costo)) ? parseFloat(costo).toFixed(2) : costo;
            $costo.val(costo);

            calcular_importe(id_producto);
        }

        function calcular_importe(id_producto) {
            var $producto = $('[data-producto=' + id_producto + ']');
            var $cantidad = $producto.find('[data-cantidad]');
            var $costo = $producto.find('[data-costo]');
            var $importe = $producto.find('[data-importe]');
            var cantidad, costo, importe;

            cantidad = $.trim($cantidad.val());
            cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
            costo = $.trim($costo.val());
            costo = ($.isNumeric(costo)) ? parseFloat(costo) : 0.00;
            importe = cantidad * costo;
            importe = importe.toFixed(2);
            $importe.text(importe);

            calcular_total();
        }

        function calcular_total() {
            var $compras = $('#compras tbody');
            var $total = $('[data-subtotal]:first');
            var $importes = $compras.find('[data-importe]');
            var importe, total = 0;

            $importes.each(function (i) {
                importe = $.trim($(this).text());
                importe = parseFloat(importe);
                total = total + importe;
            });

            $total.text(total.toFixed(2));
            $('[data-compras]:first').val($importes.size()).trigger('blur');
            $('[data-total]:first').val(total.toFixed(2)).trigger('blur');
        }


         /****/
        function set_cuotas() {
            
            var cantidad = $('#nro_cuentas').val();
            var $compras = $('#cuentasporpagar tbody');
            
            $("#nro_plan_pagos").val(cantidad);

            if(cantidad>3){
                cantidad=3;
                $('#nro_cuentas').val("3")
            }   
            for(i=1;i<=cantidad;i++){
                $('[data-cuota=' + i + ']').css({'height':'auto', 'overflow':'visible'});               
                $('[data-cuota2=' + i + ']').css({'margin-top':'10px;'});               
                $('[data-cuota=' + i + ']').parent('td').css({'height':'auto', 'border-width':'1px','padding':'5px'});              
            }
            for(i=parseInt(cantidad)+1;i<=36;i++){
                $('[data-cuota=' + i + ']').css({'height':'0px', 'overflow':'hidden'});             
                $('[data-cuota2=' + i + ']').css({'margin-top':'0px;'});                
                $('[data-cuota=' + i + ']').parent('td').css({'height':'0px', 'border-width':'0px','padding':'0px'});
            }
            set_cuotas_val();
            calcular_cuota(1000);
        }
        function set_cuotas_val() {
            nro=$('#nro_cuentas').val();
            
            valorG=parseFloat($('[data-total]:first').val());
            valor=valorG/nro;
            for(i=1;i<=nro;i++){
                if(i==nro){
                    final=valorG-(valor.toFixed(1)*(i-1));
                    $('[data-cuota=' + i + ']').children('.monto_cuota').val(final.toFixed(1)+"0");
                }else{
                    $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
                }
            }       
        }
        function set_plan_pagos(){
            if($("#forma_pago").val()==1){
                $('#plan_de_pagos').css({'display':'none'});
            }
            else{
                $('#plan_de_pagos').css({'display':'block'});
            }
        }
        function calcular_cuota(x) {
            var cantidad = $('#nro_cuentas').val();
            var total = 0;
            
            for(i=1;i<=x && i<=cantidad;i++){
                importe=$('[data-cuota=' + i + ']').children('.monto_cuota').val();
                importe = parseFloat(importe);
                total = total + importe;
            }
            
            valorTotal=parseFloat($('[data-total]:first').val());
            if(nro>x){
                valor=(valorTotal-total)/(nro-x);
            }
            else{
                valor=0;
            }

            for(i=(parseInt(x)+1);i<=cantidad;i++){
                if(valor>=0){
                    if(i==cantidad){
                        valor=valorTotal-total;
                        $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
                    }
                    else{
                        $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
                    }
                    total = total + (valor.toFixed(1)*1);
                }
                else{
                    $('[data-cuota=' + i + ']').children('.monto_cuota').val("0.00");
                }
            }   
            
            $('[data-totalcuota]').text( total.toFixed(2) );
            valor=parseFloat($('[data-total]:first').val());
            
            //alert(valor+" - - - "+total );

            if(valor==total.toFixed(2) ){
                $('[data-total-pagos]:first').val(1);
                $('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg",""); 
            }
            else{
                $('[data-total-pagos]:first').val(0);   
                $('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg","La suma de las cuotas es diferente al costo total « "+total.toFixed(2)+" / "+valor+" »");   
            }
        }
        function change_date(x){
            if($('#inicial_fecha_'+x).val()!=""){
                if(x<36){
                    $('#inicial_fecha_'+(x+1)).removeAttr("disabled");
                }
            }   
            else{
                for(i=x;i<=35;i++){
                    $('#inicial_fecha_'+(i+1)).val("");
                    $('#inicial_fecha_'+(i+1)).attr("disabled","disabled");
                }
            }
        }

    </script>
<?php require_once show_template('footer-configured'); ?>