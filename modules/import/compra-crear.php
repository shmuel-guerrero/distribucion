<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('subir-compra', $permisos);


?>
<?php require_once show_template('header-configured'); ?>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Crear producto</strong>
	</h3>
</div>
<div class="panel-body">
    <?php if (false) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para realizar acciones hacer click en el siguiente boton:</div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <a href="?/productos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
            </div>
        </div>
        <?php } ?>
        
        <hr>
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">Importacion de Excel - Compras</div>
                <div class="panel-body">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <form method="post" action="?/import/subir-compra" enctype="multipart/form-data" id="formulario" autocomplete="off">
                                <div class="form-group">
                                    <label for="archivo" class="control-label font-weight-normal">Archivo Excel:</label>
                                    <input type="file" name="archivo" id="archivo" class="form-control" accept=".xls,.xlsx" data-validation="required size" data-validation-allowing="xls, xlsx" data-validation-max-size="1M">
                                </div>
                                <div class="form-group">
                                    <label for="nro_hoja" class="control-label font-weight-normal">Nro de hoja:</label>
                                    <input type="text" value="1" name="nro_hoja" id="nro_hoja" class="form-control" autocomplete="off" data-validation="required number">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-ok"></span>
                                        <span>Guardar</span>
                                    </button>
                                    <button type="reset" class="btn btn-default">
                                        <span class="glyphicon glyphicon-repeat"></span>
                                        <span>Restablecer</span>
                                    </button>
                                </div>
                            </form>
                            
                
                        </div>
                    </div>            
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">Datos de compras - Información subida</div>
                <div class="panel-body">
                    <div class="col-sm-12 col-md-12">
                    <div class="form-horizontal">
						<div class="form-group">
							<label class="col-md-3 control-label">Fecha y hora:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape(date_decode($ingreso['fecha_ingreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($ingreso['hora_ingreso']); ?></small></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Proveedor:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nombre_proveedor']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Tipo de ingreso:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['tipo']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Descripción:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['descripcion']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Monto total:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['monto_total']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Descuento:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['descuento']); ?> %</p>
							</div>
						</div>
						<?php if ($ingreso['monto_total_descuento']>0) {  
							$descuento= $ingreso['monto_total_descuento'];
						} else {
                            $descuento= $ingreso['monto_total'];
						}?>
						<div class="form-group">
							<label class="col-md-3 control-label">Monto total con Descuento:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($descuento); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Número de registros:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nro_registros']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Almacén:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['almacen']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Empleado:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($ingreso['nombres'] . ' ' . $ingreso['paterno'] . ' ' . $ingreso['materno']); ?></p>
							</div>
						</div>
					</div>
                    </div>
                    <div class="col-sm-12 col-md-12">
                        <div class="table-responsive">
                            <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                                <thead>
                                    <tr class="active">
                                        <th class="text-nowrap">#</th>
                                        <th class="text-nowrap">Código</th>
                                        <th class="text-nowrap">Nombre</th>
                                        <th class="text-nowrap">Cantidad</th>
                                        <!-- <th class="text-nowrap">F. elaboración</th>
                                        <th class="text-nowrap">F. vencimiento</th>
                                        <th class="text-nowrap">Nro Lote</th>
                                        <th class="text-nowrap">Nro DUI</th>
                                        <th class="text-nowrap">Contenedor</th> -->
                                        <th class="text-nowrap">Costo <?= escape($moneda); ?></th>
                                        <th class="text-nowrap">Importe <?= escape($moneda); ?></th>
                                        <?php if ($permiso_suprimir) { ?>
                                        <!--<th class="text-nowrap">Opciones</th>-->
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $total = 0; ?>
                                    <?php foreach ($detalles as $nro => $detalle) { ?>
                                    <tr>
                                        <?php $cantidad = escape($detalle['cantidad']); ?>
                                        <?php $costo = escape($detalle['costo']); ?>
                                        <?php $importe = $cantidad * $costo; ?>
                                        <?php $total = $total + $importe; ?>
                                        <th class="text-nowrap"><?= $nro + 1; ?></th>
                                        <td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
                                        <td class="text-nowrap"><?= escape($detalle['nombre']); ?></td>
                                        <td class="text-nowrap text-right"><?= $cantidad; ?></td>
                                        <!-- <td class="text-nowrap text-right"><?= $detalle['elaboracion']; ?></td>
                                        <td class="text-nowrap text-right"><?= $detalle['vencimiento']; ?></td>
                                        <td class="text-nowrap text-right"><?= $detalle['lote2']; ?></td>
                                        <td class="text-nowrap text-right"><?= $detalle['dui']; ?></td>
                                        <td class="text-nowrap text-right"><?= $detalle['contenedor']; ?></td> -->
                                        <td class="text-nowrap text-right"><?= $costo; ?></td>
                                        <td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
                                        <?php if ($permiso_suprimir) { ?>
                                        <!--<td class="text-nowrap">-->
                                        <!--	<a href="?/ingresos/suprimir/<?= $ingreso['id_ingreso']; ?>/<?= $detalle['id_detalle']; ?>" data-toggle="tooltip" data-title="Eliminar detalle del ingreso" data-suprimir="true"><span class="glyphicon glyphicon-trash"></span></a>-->
                                        <!--</td>-->
                                        <?php } ?>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
                                        <th class="text-nowrap text-right"><?= number_format($total, 2, '.', ''); ?></th>
                                        <?php if ($permiso_suprimir) { ?>
                                        <!--<th class="text-nowrap">Opciones</th>-->
                                        <?php } ?>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
					</div>
                </div>
            </div>
        </div>
</div>

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/sweetalert2.all.min.js"></script>
<!-- <script src="<?= js; ?>/bootstrap-dropzone.min.js"></script> -->

<script>
$(function () {
	$.validate({
		modules: 'basic,file'
	});

/* 	$('#archivo').dropzone({
		boxClass: 'alert text-center',
		childTemplate: '<div class="col"></div>'
	}); */


    document.getElementById("archivo").addEventListener("change", ()=>{
        let fileName = document.getElementById("archivo").value;
        let idxDot = fileName.lastIndexOf(".") + 1;
        let extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
        if (extFile == "xlsx" || extFile == "xlsb" ) {
            
        }else{
            Swal.fire(`MENSAJE DE ADVERTENCIA EXTEION NO PERMITIDA ${extFile}`, "warning");
        }
    });


    document.getElementById("formulario").addEventListener("submit", (e) =>{
        e.preventDefault();
        let archivo = document.getElementById("archivo").value;
        if (archivo.length == 0) {
            return Swal.fire(`DEBE CARGAR UN ARCHIVO`, "warning");
        }

        var formulario = $("#formulario")[0];
        let formData = new FormData(formulario);
        /* let excel = $("#archivo")[0].files[0];
        formData.append('excel', excel);
 */
        $.ajax({
            url:'?/import/subir-compra',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success:function (resp) {
                console.log(resp);
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: `Se subio archivo correctamente ${resp}`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                
            },
            error:function(e){
                console.log(e);
            }
        })

    });

});
</script>
<?php require_once show_template('footer-configured'); ?>