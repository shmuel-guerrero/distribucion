<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene las ventas
//$proformas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_proformas i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('i.empleado_id', $_user['persona_id'])->where('i.fecha_proforma >= ', $fecha_inicial)->where('i.fecha_proforma <= ', $fecha_final)->order_by('i.fecha_proforma desc, i.hora_proforma desc')->fetch();
$rutas = $db->select('i.*')->from('gps_rutas i')->order_by('i.fecha')->fetch();

//listar todos los empleados
$empleados = $db->select('*')->from('sys_empleados')->join('sys_users','id_empleado = persona_id')->where('rol_id !=',4)->order_by('nombres')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_ver2 = in_array('vertodo', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_historial = in_array('historial', $permisos);
$permiso_asignar = true;
$permiso_asignar_dia = true;
$permiso_cambiar = true;

?>
<?php require_once show_template('header-configured'); ?>
    <style>
        .table-xs tbody {
            font-size: 12px;
        }
        .width-sm {
            min-width: 150px;
        }
        .width-md {
            min-width: 200px;
        }
        .width-lg {
            min-width: 250px;
        }
    </style>
    <div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Lista de rutas</strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($permiso_crear || $permiso_crear2) { ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <div class="text-label">Para realizar acciones clic en el siguiente botón(es): </div>
                </div>
                <div class="col-xs-12 col-sm-4 text-right">
                    <?php if ($permiso_crear) { ?>
                        <a href="?/control/crear" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span><span class="hidden-xs"> Crear ruta</span></a>
                    <?php } ?>
                    <?php if ($permiso_ver2) { ?>
                        <a href="?/control/vertodo" class="btn btn-success"><span class="glyphicon glyphicon-map-marker"></span><span class="hidden-xs"> Ver rutas</span></a>
                    <?php } ?>
                </div>
            </div>
            <hr>
        <?php } ?>
        <div class="alert alert-warning">
            <strong>Recomendaciones!</strong>
            <ul>
                <li>A pesar de que el sistema le permite crear un poligono a necesidad del cliente. Es necesario tomar en cuenta a momento de crear, que el area designada del poligono(ruta); mientras mas grande sea, afectara en la respuesta de los servidores a los datos requeridos por la aplicación.</li>
                <li>Los poligonos no deberan entrecruzarse para evitar duplicidad de datos, o restricciones en los datos brindados por el sistema.</li>
            </ul>
    	</div>
        <?php if ($rutas) { ?>
            <table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
                <thead>
                <tr class="active">
                    <th class="text-nowrap">#</th>
                    <th class="text-nowrap">Fecha</th>
                    <th class="text-nowrap">Ruta</th>
                    <th class="text-nowrap" >Empleado - Usuario</th>
                    <th class="text-nowrap" >Dia</th>
                    <th class="text-nowrap" >Empresa</th>
                    <?php if ($permiso_ver || $permiso_eliminar) { ?>
                        <th class="text-nowrap">Opciones</th>
                    <?php } ?>
                </tr>
                </thead>
                <tfoot>
                <tr class="active">
                    <th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Ruta</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true"  >Empleado - Usuario</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true"  >Día</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true"  >Empresa</th>
                    <?php if ($permiso_ver || $permiso_eliminar) { ?>
                        <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                    <?php } ?>
                </tr>
                </tfoot>
                <tbody>
                <?php
                $em = '';
                $em1 = '';
                foreach ($rutas as $nro => $ruta) { ?>
                    <tr>
                        <th class="text-nowrap"><?= $nro + 1; ?></th>
                        <td class="text-nowrap text-uppercase"><?= escape(date_decode($ruta['fecha'])); ?></small></td>
                        <td class="text-nowrap text-uppercase h4"><?= escape($ruta['nombre']); ?></td>
                        <?php if($ruta['empleado_id']!=0){ $empl = $db->select('e.*, u.username')->from('sys_empleados e')->join('sys_users u', 'u.persona_id = id_empleado', 'left')->where('e.id_empleado',$ruta['empleado_id'])->fetch_first(); $em = $empl['nombres'].' '.$empl['paterno'].' '. $empl['materno'] . ' - <small class="text-success">' . $empl['username'] . '</small>'; $em1 = $empl['cargo'];}else{$em = '';$em1 = '';}?>
                        <td class="text-nowrap text-uppercase">
                            <?php if ($permiso_asignar) : ?>
                                <a href="?/control/asignar/<?= $ruta['id_ruta']; ?>" style="margin-right: 3px" class="underline-none" data-toggle="tooltip" data-title="Asignar nuevo empleado" data-asignar="true">
                                    <span class="glyphicon glyphicon-user"></span>
                                </a>
                            <?php endif ?>
                            <b><?= $em ?></b>
                        </td>
                        <td class="text-nowrap text-uppercase">
                            <?php if ($permiso_asignar_dia) : ?>
                                <a href="?/control/asignar_dia/<?= $ruta['id_ruta']; ?>" style="margin-right: 3px" class="underline-none" data-toggle="tooltip" data-title="Asignar nuevo día" data-asignar2="true">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </a>
                            <?php endif ?>
                            <b><?php switch($ruta['dia']){case 0: echo "Domingo"; break; case 1: echo "Lunes"; break; case 2: echo "Martes"; break; case 3: echo "Miercoles"; break; case 4: echo "Jueves"; break; case 5: echo "Viernes"; break; case 6: echo "Sábado"; break; default: echo "<b class='text-danger'>DIA NO ASIGNADO</b>"; break;} ?></b>
                        </td>
                        <td class="text-nowrap"><b><?php if($ruta['estado']==1){echo $_institution['empresa1'];}else{echo $_institution['empresa2'];}; ?></b></td>
                        <?php if ($permiso_ver || $permiso_eliminar) { ?>
                            <td class="text-nowrap">
                                <?php if ($permiso_ver) { ?>
                                    <a href="?/control/ver/<?= $ruta['id_ruta']; ?>" style="margin-right: 3px" data-toggle="tooltip" data-title="Ver detalle de la ruta"><i class="glyphicon glyphicon-search"></i></a>
                                <?php } ?>
                                <?php if ($permiso_editar) { ?>
                                    <a href="?/control/editar/<?= $ruta['id_ruta']; ?>" style="margin-right: 3px" data-toggle="tooltip" data-title="Editar la ruta"><i class="glyphicon glyphicon-edit"></i></a>
                                <?php } ?>
                                <?php if ($permiso_eliminar) { ?>
                                    <a href="?/control/eliminar/<?= $ruta['id_ruta']; ?>" style="margin-right: 3px" data-toggle="tooltip" data-title="Eliminar ruta" data-eliminar="true"><span class="glyphicon glyphicon-trash text-danger"></span></a>
                                <?php } ?>
                                <?php if ($permiso_historial) : ?>
                                    <a href="?/control/historial/<?= $ruta['id_ruta']; ?>" style="margin-right: 3px" class="underline-none" data-title="Historial de la ruta" >
                                        <span class="glyphicon glyphicon-tag"></span>
                                    </a>
                                <?php endif ?>

                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>No existen rutas registrados en la base de datos.</p>
            </div>
        <?php } ?>
    </div>
<?php if ($permiso_asignar) : ?>
    <div id="modal_asignar" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" id="form_asignar" class="modal-content loader-wrapper" autocomplete="off">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Asignar empleado</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unidad_id_asignar" class="control-label">Empleado:</label>
                        <select name="unidad_id" id="unidad_id_asignar" class="form-control" data-validation="required">
                            <option value="" selected="selected">Seleccionar</option>
                            <?php foreach ($empleados as $empleado) : ?>
                                <option value="<?= $empleado['id_empleado']; ?>"><?= escape($empleado['nombres'].' '.$empleado['paterno'].' '.$empleado['materno']); ?><?php if($empleado['cargo']==1){echo ' ('.$_institution['empresa1'].')';}else{echo ' ('.$_institution['empresa2'].')';} ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                        <span>Guardar</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
<?php endif ?>
    <!-- Fin modal modal-->
    <!-- Modal de cambio de dia -->
<?php if ($permiso_asignar_dia) : ?>
    <div id="modal_asignar2" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" id="form_asignar2" class="modal-content loader-wrapper" autocomplete="off">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Asignar empleado</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unidad_id_asignarr" class="control-label">Día:</label>
                        <select name="unidad_id" id="unidad_id_asignarr" class="form-control" data-validation="required">
                            <option value="" selected="selected">Seleccionar</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="0">Domingo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                        <span>Guardar</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
<?php endif ?>
    <!-- Fin modal modal -->
    <!-- Inicio modal fecha -->
<?php if ($permiso_cambiar) { ?>
    <div id="modal_fecha" class="modal fade">
        <div class="modal-dialog">
            <form id="form_fecha" class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Cambiar fecha</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="inicial_fecha">Fecha inicial:</label>
                                <input type="text" name="inicial" value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="final_fecha">Fecha final:</label>
                                <input type="text" name="final" value="<?= ($fecha_final != $gestion_limite) ? date_decode($fecha_final, $_institution['formato']) : ''; ?>" id="final_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-aceptar="true">
                        <span class="glyphicon glyphicon-ok"></span>
                        <span>Aceptar</span>
                    </button>
                    <button type="button" class="btn btn-default" data-cancelar="true">
                        <span class="glyphicon glyphicon-remove"></span>
                        <span>Cancelar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
    <!-- Fin modal fecha -->

    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.es.js"></script>
    <script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
    <script src="<?= js; ?>/jquery.base64.js"></script>
    <script src="<?= js; ?>/pdfmake.min.js"></script>
    <script src="<?= js; ?>/vfs_fonts.js"></script>
    <script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
    <script src="<?= js; ?>/moment.min.js"></script>
    <script src="<?= js; ?>/moment.es.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
    <script src="<?= js; ?>/selectize.min.js"></script>
    <script>
        $(function () {
            var table = $('#table').DataFilter({
               filter: true,
                name: 'Lista de rutas',
                lengthMenu: [[10, 50, 100, 500, -1], [10, 50, 100, 500, 'Todos']],
                reports: 'xls|doc|pdf|html'
            });


            var $modal_asignar = $('#modal_asignar'), $modal_asignar2 = $('#modal_asignar2'), $form_asignar = $('#form_asignar'), $form_asignar2 = $('#form_asignar2'), $asignar = $('#table'), $asignar2 = $('#table');
            $asignar.on('click','[data-asignar]', function (e) {
                console.log("sdfsd");
                e.preventDefault();
                var href = $(this).attr('href');
                $form_asignar.attr('action', href);
                $modal_asignar.modal('show');
            });

            $asignar2.on('click','[data-asignar2]', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                $form_asignar2.attr('action', href);
                $modal_asignar2.modal('show');
            });

            $.validate({
                form: '#form_asignar',
                modules: 'basic'
            });

            $.validate({
                form: '#form_asignar2',
                modules: 'basic'
            });

            var $unidad_id_asignar = $('#unidad_id_asignar'), $unidad_id_asignar2 = $('#unidad_id_asignarr');
            $('#unidad_id_asignar').selectize({
                persist: true,
                createOnBlur: false,
                create: false,
                maxOptions: 7,
                onInitialize: function () {
                    $unidad_id_asignar.show().addClass('translate');
                },
                onChange: function () {
                    $unidad_id_asignar.trigger('blur');
                },
                onBlur: function () {
                    $unidad_id_asignar.trigger('blur');
                }
            }); 

            $unidad_id_asignar2.selectize({
                persist: false,
                createOnBlur: false,
                create: false,
                maxOptions: 7,
                onInitialize: function () {
                    $unidad_id_asignar2.show().addClass('translate');
                },
                onChange: function () {
                    $unidad_id_asignar2.trigger('blur');
                },
                onBlur: function () {
                    $unidad_id_asignar2.trigger('blur');
                }
            }); 


            <?php if ($permiso_crear) { ?>
            $(window).bind('keydown', function (e) {
                if (e.altKey || e.metaKey) {
                    switch (String.fromCharCode(e.which).toLowerCase()) {
                        case 'n':
                            e.preventDefault();
                            window.location = '?/proformas/crear';
                            break;
                    }
                }
            });
            <?php } ?>

            <?php if ($permiso_eliminar) { ?>
            $('[data-eliminar]').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                bootbox.confirm('Está seguro que desea eliminar la proforma y todo su detalle?', function (result) {
                    if(result){
                        window.location = url;
                    }
                });
            });
            <?php } ?>

            <?php if ($permiso_cambiar) { ?>
            var formato = $('[data-formato]').attr('data-formato');
            var mascara = $('[data-mascara]').attr('data-mascara');
            var gestion = $('[data-gestion]').attr('data-gestion');
            var $inicial_fecha = $('#inicial_fecha');
            var $final_fecha = $('#final_fecha');

            $.validate({
                form: '#form_fecha',
                modules: 'date',
                onSuccess: function () {
                    var inicial_fecha = $.trim($('#inicial_fecha').val());
                    var final_fecha = $.trim($('#final_fecha').val());
                    var vacio = gestion.replace(new RegExp('9', 'g'), '0');

                    inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
                    inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
                    final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
                    final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
                    vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
                    vacio = vacio.replace(new RegExp('/', 'g'), '-');
                    final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
                    inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : '');

                    window.location = '?/proformas/mostrar' + inicial_fecha + final_fecha;
                }
            });

            //$inicial_fecha.mask(mascara).datetimepicker({
            $inicial_fecha.datetimepicker({
                format: formato
            });

            //$final_fecha.mask(mascara).datetimepicker({
            $final_fecha.datetimepicker({
                format: formato
            });

            $inicial_fecha.on('dp.change', function (e) {
                $final_fecha.data('DateTimePicker').minDate(e.date);
            });

            $final_fecha.on('dp.change', function (e) {
                $inicial_fecha.data('DateTimePicker').maxDate(e.date);
            });

            var $form_fecha = $('#form_fecha');
            var $modal_fecha = $('#modal_fecha');

            $form_fecha.on('submit', function (e) {
                e.preventDefault();
            });

            $modal_fecha.on('show.bs.modal', function () {
                $form_fecha.trigger('reset');
            });

            $modal_fecha.on('shown.bs.modal', function () {
                $modal_fecha.find('[data-aceptar]').focus();
            });

            $modal_fecha.find('[data-cancelar]').on('click', function () {
                $modal_fecha.modal('hide');
            });

            $modal_fecha.find('[data-aceptar]').on('click', function () {
                $form_fecha.submit();
            });

            $('[data-cambiar]').on('click', function () {
                $('#modal_fecha').modal({
                    backdrop: 'static'
                });
            });
            <?php } ?>


        });
    </script>
<?php require_once show_template('footer-configured'); ?>