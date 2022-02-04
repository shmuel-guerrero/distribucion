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
	.position-left-bottom {
		bottom: 0;
		left: 0;
		position: fixed;
		z-index: 1030;
	}
	.margin-all {
		margin: 15px;
	}
	.display-table {
		display: table;
	}
	.display-cell {
		display: table-cell;
		text-align: center;
		vertical-align: middle;
	}
	.btn-circle {
		border-radius: 50%;
		height: 75px;
		width: 75px;
	}
</style>
<?php
    $IdSalida=(sizeof($params)>0)?$params[0]:0;

    $almacen=$db->from('inv_almacenes')->where('principal','S')->fetch_first();
    $id_almacen=($almacen)?$almacen['id_almacen']:0;

    if($id_almacen!=0):
        $productos=$db->query("SELECT p.id_producto,p.descripcion,p.imagen,p.codigo,p.nombre,p.nombre_factura,p.cantidad_minima,p.precio_actual,ifnull(e.cantidad_ingresos,0)AS cantidad_ingresos,IFNULL(s.cantidad_egresos,0)AS cantidad_egresos,u.unidad,u.sigla,c.categoria
                            FROM inv_productos AS p
                            LEFT JOIN(
                                SELECT d.producto_id,sum(d.cantidad)AS cantidad_ingresos
                                FROM inv_ingresos_detalles d
                                LEFT JOIN inv_ingresos i ON i.id_ingreso=d.ingreso_id
                                WHERE i.almacen_id=$id_almacen GROUP BY d.producto_id
                            )AS e ON e.producto_id=p.id_producto
                            LEFT JOIN(
                                SELECT d.producto_id,sum(d.cantidad)AS cantidad_egresos
                                FROM inv_egresos_detalles d
                                LEFT JOIN inv_egresos e ON e.id_egreso=d.egreso_id
                                WHERE e.almacen_id=$id_almacen GROUP BY d.producto_id
                            )AS s ON s.producto_id=p.id_producto
                            LEFT JOIN inv_unidades u ON u.id_unidad=p.unidad_id
                            LEFT JOIN inv_categorias c ON c.id_categoria=p.categoria_id")->fetch();
    else:
        $productos=null;
    endif;

    $Salida=$db->query("SELECT os.empleado_entrega_id
                        FROM inv_ordenes_salidas AS os
                        INNER JOIN sys_empleados AS e
                        WHERE os.id_orden='{$IdSalida}' AND os.estado='salida'")->fetch_first();
    $DetallesSalidas=$db->query("SELECT od.id_orden_detalle,od.precio_id,od.cantidad,p.id_producto,p.codigo,p.nombre,p.precio_actual
                                FROM inv_ordenes_detalles AS od
                                LEFT JOIN inv_productos AS p ON p.id_producto=od.producto_id
                                WHERE od.orden_salida_id='{$IdSalida}'")->fetch();
    $Responsables=$db->query("SELECT e.id_empleado,e.nombres,e.paterno,e.materno
                            FROM sys_empleados AS e
                            LEFT JOIN inv_ordenes_salidas AS os ON os.empleado_entrega_id=e.id_empleado
                            INNER JOIN sys_users AS u ON u.persona_id=e.id_empleado
                            WHERE rol_id='14' GROUP BY e.id_empleado")->fetch();

    $moneda=$db->from('inv_monedas')->where('oficial','S')->fetch_first();
    $moneda=($moneda)?'('.$moneda['sigla'].')':'';

    $Fecha=date('Y-m-d');

    require_once show_template('header-configured');
?>
<div class='row'>
    <?php
        if($almacen):
    ?>
    <div class='col-md-6'>
		<div class='panel panel-default'>
			<div class='panel-heading'>
				<h3 class='panel-title'>
					<span class='glyphicon glyphicon-list'></span>
					<strong>Datos de la venta</strong>
				</h3>
			</div>
			<div class='panel-body'>
                <form id='formulario' method='post' action='?/autoventas/guardar' class='form-horizontal'>
                    <div class='form-group'>
						<label class='col-sm-4 control-label'>Buscar:</label>
                        <div class='col-sm-8'>
                            <select name='responsable' class='form-control text-uppercase' required>
                                <option value='' disables>Buscar</option>
                            <?php foreach($Responsables as $Fila=>$Responsable):?>
                                <option value='<?= $Responsable['id_empleado']?>' <?php if($Salida['empleado_entrega_id']==$Responsable['id_empleado']):echo 'selected';endif;?> ><?php echo "{$Responsable['nombres']} {$Responsable['paterno']} {$Responsable['materno']}";?></option>
                            <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                    <div class='table-responsive margin-none'>
						<table id='ventas' class='table table-bordered table-condensed table-striped table-hover table-xs margin-none'>
                            <thead>
								<tr class="active">
									<th class="text-nowrap text-center width-collapse">#</th>
									<th class="text-nowrap text-center width-collapse">CÓDIGO</th>
									<th class="text-nowrap text-center">PRODUCTO</th>
                                    <th class="text-center width-collapse" width="8%">CANTIDAD </th>
									<th class="text-center width-collapse" width="8%">MATERIAL PRESTAMO</th>
									<th class="text-nowrap text-center ">PRECIO</th>
									<th class="text-nowrap text-center width-collapse">IMPORTE</th>
									<th class="text-nowrap text-center width-collapse">ACCIONES</th>
								</tr>
							</thead>
                            <tbody>
                            <?php
                                $Total=0;
                                foreach($DetallesSalidas as $Nro=>$Detalle):
                            ?>
                                <tr class='active' data-producto='<?=$Detalle['id_producto']?>'>
                                    <td class='text-nowrap'><?=$Nro+1?></td>
                                    <td class='text-nowrap'>
                                        <input type="text" value="<?=$Detalle['id_producto']?>" name="productos[]" class="translate" tabindex="-1" data-validation="required number"><?=$Detalle['codigo']?>
                                    </td>
                                    <td class='text-nowrap'>
                                        <input type="text" value="<?=$Detalle['nombre']?>" name="nombres[]" class="translate" tabindex="-1" data-validation="required"><?=$Detalle['nombre']?>
                                    </td>
                                    <td>
                                        <input type="text" value="<?=$Detalle['cantidad']?>" name="cantidades[]" class="form-control text-right" style="width: 100px;" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;1000000]" data-validation-error-msg="Debe ser un número positivo entre 1 y 2" onkeyup="Pres<?=$Detalle['id_producto']?>.value=this.value;calcular_importe(<?=$Detalle['id_producto']?>)">
                                    </td>
                                    <td>
                                        <input id="Pres<?=$Detalle['id_producto']?>" type="text" value="1" name="cantidades_pres[]" class="form-control text-right" style="width: 100px;" maxlength="10" autocomplete="off" data-cantidad_pres="" data-validation="required number" onkeyup="calcular_importe(<?=$Detalle['id_producto']?>)">
                                    </td>
                                    <td>
                                        <input type="text" value="<?=$Detalle['precio_actual']?>" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="<?=$Detalle['precio_actual']?>" data-validation="required number" readonly data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(<?=$Detalle['id_producto']?>)">
                                    </td>
                                    <td class="text-nowrap text-middle text-right" data-importe="">
                                        <?=$Detalle['precio_actual']*$Detalle['cantidad']?>
                                    </td>
                                    <input type="hidden" value="0" name="descuentos[]" class="form-control text-right" style="width: 100px;" maxlength="10" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="float,range[-100.00;100.00],negative" data-validation-error-msg="Debe ser un número entre -100.00 y 100.00" onkeyup="descontar_precio(<?=$Detalle['id_producto']?>)">
                                    <td class="text-nowrap text-middle text-center">
                                        <button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(<?=$Detalle['id_producto']?>)">
                                            <span class="glyphicon glyphicon-remove"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php
                                    $Total=$Total+($Detalle['precio_actual']*$Detalle['cantidad']);
                                endforeach;
                            ?>
                            </tbody>
                            <tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal=""><?=$Total?></th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class='form-group'>
						<div class='col-xs-12'>
							<input type='text' name='almacen_id' value='<?= $almacen['id_almacen']; ?>' class='translate' tabindex='-1' data-validation='required number' data-validation-error-msg='El almacén no esta definido'>
							<input type='text' name='nro_registros' value='0' class='translate' tabindex='-1' data-ventas='' data-validation='required number'>
							<input type='text' name='monto_total' value='<?=$Total?>' class='translate' tabindex='-1' data-total='' data-validation='required number' >
						</div>
					</div>
					<div class='form-group'>
						<div class='col-xs-12 text-right'>
                            <input type='hidden' name='IdOrdenSalida' value='<?=$IdSalida?>'>
							<button type='submit' class='btn btn-success'>Cerrar venta</button>
							<button type='reset' class='btn btn-default'>Restablecer</button>
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
				<?php if ($permiso_mostrar) { ?>
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/manuales/mostrar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Ventas personales</span></a>
					</div>
				</div>
				<hr>
				<?php } ?>
				<?php if ($productos) { ?>
				<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
					<thead>
						<tr class="active">
							<th class="text-nowrap">Imagen</th>
							<th class="text-nowrap">Código</th>
							<th class="text-nowrap">Nombre</th>
                            <th class="text-nowrap">Descripción</th>
                            <th class="text-nowrap">Tipo</th>
							<th class="text-nowrap">Stock</th>
							<th class="text-nowrap">Precio</th>
							<th class="text-nowrap"><i class="glyphicon glyphicon-cog"></i></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($productos as $nro => $producto) { ?>
						<tr>
							<td class="text-nowrap"><img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" width="75" height="75"></td>
							<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
							<td>
								<span><?= escape($producto['nombre']); ?></span>
								<span class="hidden" data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre_factura']); ?></span>
							</td>
                            <td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
                            <td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
							<td class="text-nowrap text-right" data-stock="<?= $producto['id_producto']; ?>"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
							<td class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>"><?= escape($producto['precio_actual']); ?></td>
							<td class="text-nowrap">
								<button type="button" class="btn btn-xs btn-primary" data-vender="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Vender"><span class="glyphicon glyphicon-share-alt"></span></button>
								<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
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
    <?php
        else:
    ?>
    <div class='col-xs-12'>
		<div class='panel panel-default'>
			<div class='panel-heading'>
				<h3 class='panel-title'><i class='glyphicon glyphicon-home'></i> Editar Salidas</h3>
			</div>
			<div class='panel-body'>
				<div class='alert alert-danger'>
					<strong>Advertencia!</strong>
					<p>Usted no puede realizar esta operación, verifique que todo este en orden tomando en cuenta las siguientes sugerencias:</p>
					<ul>
						<li>No existe el almacén principal de la cual se puedan tomar los productos necesarios para la venta, debe fijar un almacén principal.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
    <?php
        endif;
    ?>
</div>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script src='<?= js; ?>/jquery.dataTables.min.js'></script>
<script src='<?= js; ?>/dataTables.bootstrap.min.js'></script>
<script src='<?= js; ?>/selectize.min.js'></script>
<script src='<?= js; ?>/bootstrap-notify.min.js'></script>
<script>
    table = $('#productos').DataTable({
		info: false,
		lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
		order: []
	});
    $('[data-vender]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-vender')));
	});
    function adicionar_producto(id_producto) {
        var $ventas = $('#ventas tbody');
        var $producto = $ventas.find('[data-producto=' + id_producto + ']');
        var $cantidad = $producto.find('[data-cantidad]');
        var numero = $ventas.find('[data-producto]').size() + 1;
        var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
        var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
        var stock = $.trim($('[data-stock=' + id_producto + ']').text());
        var valor = $.trim($('[data-valor=' + id_producto + ']').text());
        var plantilla = '';
        var cantidad;
        console.log(stock)
        console.log(id_producto)
        if ($producto.size()) {
            cantidad = $.trim($cantidad.val());
            cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
            cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
            $cantidad.val(cantidad).trigger('blur');
        } else {
            plantilla=`<tr class='active' data-producto='${id_producto}'>
                            <td class='text-nowrap'>${numero}</td>
                            <td class='text-nowrap'>
                                <input type='text' value='${id_producto}' name='productos[]' class='translate' tabindex='-1' data-validation='required'>${codigo}
                            </td>
                            <td>
                                <input type='text' value='${nombre}' name='nombres[]' class='translate' tabindex='-1' data-validation='required'>${nombre}
                            </td>
                            <td class='text-middle'>
                                <input type='text' value='1' name='cantidades[]'  class="form-control text-right" style='width: 100px;' maxlength='10' autocomplete='off' data-cantidad='' data-validation='required number' data-validation-allowing='range[1;${stock}]' data-validation-error-msg='Debe ser un número positivo entre 1 y ${stock}' onkeyup="Pres${id_producto}.value=this.value;calcular_importe('${id_producto}')">
                            </td>
                            <td class='text-middle'>
                                <input id='Pres${id_producto}' type='text' value='1' name='cantidades_pres[]'  class='form-control text-right' style='width: 100px;' maxlength='10' autocomplete='off' data-cantidad_pres='' data-validation='required number' data-validation-allowing='range[1;${stock}]' data-validation-error-msg='Debe ser un número positivo entre 1 y ${stock}' onkeyup="calcular_importe('${id_producto}')">
                            </td>
                            <td>
                                <input type='text' value='${valor}' name='precios[]' class='form-control input-xs text-right' autocomplete='off' data-precio='${valor}' data-validation='required number' readonly data-validation-allowing='range[0.01;1000000.00],float' data-validation-error-msg='Debe ser un número decimal positivo' onkeyup="calcular_importe('${id_producto}')">
                            </td>
                            <td class='text-nowrap text-right' data-importe=''>0.00</td>
                            <td class='text-nowrap text-center'>
                                <button type='button' class='btn btn-xs btn-danger' data-toggle='tooltip' data-title='Eliminar producto' tabindex='-1' onclick='eliminar_producto(${id_producto})'>
                                    <span class='glyphicon glyphicon-remove'></span>
                                </button>
                            </td>
                        </tr>
                        `;

            $ventas.append(plantilla);
            $ventas.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function () {
                $(this).select();
            });
            $ventas.find('[title]').tooltip({
                container: 'body',
                trigger: 'hover'
            });
            $.validate({
                form: '#formulario',
                modules: 'basic',
                onSuccess: function () {
                    guardar_factura();
                }
            });
        }

        calcular_importe(id_producto);
    }
    function eliminar_producto(id_producto) {
        bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
            if(result){
                $('[data-producto=' + id_producto + ']').remove();
                renumerar_productos();
                calcular_total();
            }
        });
    }
    function renumerar_productos() {
        var $ventas = $('#ventas tbody');
        var $productos = $ventas.find('[data-producto]');
        $productos.each(function (i) {
            $(this).find('td:first').text(i + 1);
        });
    }
    function calcular_importe(id_producto) {
        var $producto = $('[data-producto=' + id_producto + ']');
        var $cantidad = $producto.find('[data-cantidad]');
        var $precio = $producto.find('[data-precio]');
        var $descuento = $producto.find('[data-descuento]');
        var $importe = $producto.find('[data-importe]');
        var cantidad, precio, importe, fijo;

        fijo = $descuento.attr('data-descuento');
        fijo = ($.isNumeric(fijo)) ? parseInt(fijo) : 0;
        cantidad = $.trim($cantidad.val());
        cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
        precio = $.trim($precio.val());
        precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
        descuento = $.trim($descuento.val());
        descuento = ($.isNumeric(descuento)) ? parseInt(descuento) : 0;
        importe = cantidad * precio;
        importe = importe.toFixed(2);
        $importe.text(importe);

        calcular_total();
    }
    function calcular_total() {
        var $ventas = $('#ventas tbody');
        var $total = $('[data-subtotal]:first');
        var $importes = $ventas.find('[data-importe]');
        var importe, total = 0;

        $importes.each(function (i) {
            importe = $.trim($(this).text());
            importe = parseFloat(importe);
            total = total + importe;
        });

        $total.text(total.toFixed(2));
        $('[data-ventas]:first').val($importes.size()).trigger('blur');
        $('[data-total]:first').val(total.toFixed(2)).trigger('blur');
    }

    var $formulario = $('#formulario');
    $formulario.on('submit', function (e) {
        e.preventDefault();
        guardar_proforma();
    });
    $formulario.on('reset', function () {

    }).trigger('reset');

    function guardar_proforma() {
        var data = $('#formulario').serialize();
        console.log(data)

        $('#loader').fadeIn(100);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?/autoventas/guardar',
            data: data
        }).done(function (proforma) {
            console.log(proforma);
            if (proforma) {
                $.notify({
                    message: 'La autoventa fue realizada satisfactoriamente.'
                }, {
                    type: 'success'
                });
                $('#formulario').trigger('reset');
                //imprimir_proforma(proforma);
                $('#loader').fadeOut(100);

                ActualizarAutoVendedor();
            } else {
                $('#loader').fadeOut(100);
                $.notify({
                    message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la autoventa, verifique si la se guardó parcialmente.'
                }, {
                    type: 'danger'
                });
            }
        }).fail(function () {
            $('#loader').fadeOut(100);
            $.notify({
                message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la autoventa, verifique si la se guardó parcialmente.'
            }, {
                type: 'danger'
            });
        });
    }
</script>
<?php
    require_once show_template('footer-configured');