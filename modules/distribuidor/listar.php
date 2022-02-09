<?php
// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
//$gestion_base = date('Y-m-d');
$gestion_base = date("d-m-Y",strtotime(date('Y-m-d')."- 1 days"));

//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = $gestion_base;

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene los empleados
$empleados = $db->select('z.*')->from('sys_empleados z')->join('sys_users a','z.id_empleado = a.persona_id')->where('a.rol_id !=',4)->order_by('z.id_empleado')->fetch();
$rutas = $db->select('z.*, a.nombres, a.paterno, a.fecha as fechav, u.username')->from('gps_rutas z')->join('sys_empleados a','z.empleado_id = a.id_empleado')->join('sys_users u', 'u.persona_id = a.id_empleado')->where('z.estado>',0)->order_by('z.id_ruta')->fetch();
$distribuidores = $db->select('z.*')->from('sys_empleados z')->join('sys_users a','z.id_empleado = a.persona_id')->where('a.rol_id',4)->order_by('z.id_empleado')->fetch();
//var_dump($rutas);
// Obtiene los grupos
$grupos = $db->select('grupo, SUM(monto_total) as total, count(id_egreso) as registros')->from('inv_egresos')->where('grupo!=','')->where('fecha_egreso<=',date('Y-m-d'))->where('estadoe',2)->group_by('grupo')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

//var_dump($egresos);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
    <div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Asignar rutas</strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $empleados)) { ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <div class="text-label">Para realizar acciones click en el siguiente botón: </div>
                </div>
                <div class="col-xs-12 col-sm-4 text-right">
                    <?php if ($permiso_imprimir) { ?>
                        <a href="?/empleados/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
                    <?php } ?>
                    <button class="btn btn-default" data-cambiar="true">
                        <span class="glyphicon glyphicon-calendar"></span>
                        <span class="hidden-xs">Cambiar</span>
                    </button>
                </div>
            </div>
            <hr>
        <?php } ?>
        <?php if (isset($_SESSION[temporary])) { ?>
            <div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><?= $_SESSION[temporary]['title']; ?></strong>
                <p><?= $_SESSION[temporary]['message']; ?></p>
            </div>
            <?php unset($_SESSION[temporary]); ?>
        <?php } ?>
        <?php if ($rutas || $grupos) { ?>
            <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                <thead>
                <tr class="active">
                    <th class="text-nowrap">#</th>
                    <th class="text-nowrap">Ruta</th>
                    <!-- <th class="text-nowrap">Grupo</th> -->
                    <th class="text-nowrap">Registros</th>
                    <th class="text-nowrap">Total</th>
                    <th class="text-nowrap">Empresa</th>
                    <!-- <th class="text-nowrap">Día</th> -->
                    <th class="text-nowrap">Preventista</th>
                    <th class="text-nowrap">Distribuidor</th>
                    <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                        <th class="text-nowrap">Opciones</th>
                    <?php } ?>
                </tr>
                </thead>
                <tfoot>
                <tr class="active">
                    <th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Ruta</th>
                    <!-- <th class="text-nowrap text-middle" data-datafilter-filter="true">Grupo</th> -->
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Empresa</th>
                    <!-- <th class="text-nowrap text-middle" data-datafilter-filter="true">Día</th> -->
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Preventista</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Distribuidor</th>
                    <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                        <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                    <?php } ?>
                </tr>
                </tfoot>
                <tbody>
                <?php foreach ($rutas as $nro => $ruta) {
                    //$id_empleado = $ruta['id_empleado'];
//                    $fecha = date('Y-m-d',$ruta['fechav']);
                    $fecha2 = date_create($ruta['fechav']);
                    $fecha = date_format($fecha2, 'Y-m-d');
//                    $fecha->format('Y-m-d');
                    // obtener las cantidades vendidas
                    $egreso = $db->select('SUM(b.monto_total) as total, count(b.id_egreso) as registros')
                        ->from('inv_egresos b')->where('b.estadoe','2')->where('b.grupo','')->where('b.ruta_id',$ruta['id_ruta'])->where('b.fecha_egreso <=',$fecha)->fetch_first();
//                    echo $db->last_query();
                    if(true){
                        ?>
                        <tr>
                            <th class="text-nowrap"><?= $nro + 1; ?></th>
                            <td class="text-nowrap text-uppercase">
                                <?= $ruta['nombre']; ?> - 
                                <small class="text-success">
                                    <?php switch($ruta['dia']){case 0: echo "Domingo"; break; case 1: echo "Lunes"; break; case 2: echo "Martes"; break; case 3: echo "Miercoles"; break; case 4: echo "Jueves"; break; case 5: echo "Viernes"; break; case 6: echo "Sábado"; break;} ?>
                                </small>
                            </td>
                            <!-- <td class="text-nowrap"></td> -->
                            <td class="text-nowrap"><?= escape($egreso['registros']); ?></td>
                            <td class="text-nowrap"><?= escape($egreso['total']); ?></td>
                            <td class="text-nowrap"><?php if($ruta['estado']==1){echo $_institution['empresa1'];}else{echo $_institution['empresa2'];}; ?></td>
                            <!-- <td class="text-nowrap">
                            </td> -->
                            <td class="text-nowrap text-uppercase">
                                <?= escape($ruta['nombres'].' '.$ruta['paterno']); ?> - <small class="text-success"><?= $ruta['username'] ?></small>
                            </td>
                            <td class="text-nowrap text-uppercase">
                                <b>
                                    <?php $dist = $db->select('e.nombres, e.paterno, u.username')->from('gps_asigna_distribucion ga')->join('sys_empleados e', 'ga.distribuidor_id = e.id_empleado')->join('sys_users u', 'u.persona_id = id_empleado')->where(array('ruta_id'=>$ruta['id_ruta'],'estado'=>1))->fetch_first();
                                    echo $dist['nombres'].' '.$dist['paterno'] ?>
                                </b> -
                                <small class="text-success"><?= $dist['username']; ?></small>
                            </td>
                            <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                                <td class="text-nowrap">
                                    <?php if ($permiso_ver) { ?>
                                        <a href="?/distribuidor/visitas2/<?= $ruta['id_ruta']; ?>/<?= $fecha_inicial; ?>/<?= $fecha_final; ?>" data-toggle="tooltip" data-title="Ver empleado"><i class="glyphicon glyphicon-search"></i></a>
                                    <?php } ?>
                                    <?php if(true){ ?>
                                        <a href="?/distribuidor/asignar/<?= $ruta['id_ruta']; ?>" class="underline-none" data-toggle="tooltip" data-title="Asignar nuevo distribuidor" data-asignar="true">
                                            <i class="glyphicon glyphicon-bed"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                } ?>
                <?php foreach ($grupos as $grupo) {
                    if($grupo['total']){
                        ?>
                        <tr>
                            <th class="text-nowrap"><?= $nro + 1; ?></th>
                            <td class="text-nowrap">----------</td>
                            <td class="text-nowrap"><?= $grupo['grupo'] ?></td>
                            <td class="text-nowrap"><?= escape($grupo['total']); ?></td>
                            <td class="text-nowrap"><?= escape($grupo['registros']); ?></td>
                            <td class="text-nowrap">----------</td>
                            <td class="text-nowrap">----------</td>
                            <td class="text-nowrap">----------
                            </td>
                            <td class="text-nowrap">
                                <b>
                                    <?php $dist = $db->select('*')->from('gps_asigna_distribucion')->join('sys_empleados', 'distribuidor_id = id_empleado')->where(array('grupo_id'=>$grupo['grupo'],'estado'=>1))->fetch_first();
                                    echo $dist['nombres'].' '.$dist['paterno'] ?>
                                </b>
                            </td>
                            <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                                <td class="text-nowrap">
                                    <?php if (false){
                                    //($permiso_ver) { ?>
                                        <a href="?/distribuidor/visitas2/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial; ?>/<?= $fecha_final; ?>" data-toggle="tooltip" data-title="Ver empleado"><i class="glyphicon glyphicon-search"></i></a>
                                    <?php } ?>
                                    <?php $grupo_id = str_replace(' ','--',$grupo['grupo']);?>
                                        <a href="?/distribuidor/asignar2" class="underline-none" data-toggle="tooltip" data-grupo="<?php echo $grupo_id; ?>" data-title="Asignar nuevo distribuidor" data-asignar="true">
                                            <i class="glyphicon glyphicon-bed"></i>
                                        </a>

                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>No existen distribuidores registrados en la base de datos, registrar un nuevo usuario de rol distribuidor para obtener, generar información o reportes.</kbd>.</p>
            </div>
        <?php } ?>
    </div>
    <div id="modal_asignar" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" id="form_asignar" class="modal-content loader-wrapper" autocomplete="off">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Asignar empleado</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" value="" name="grupo_asignar" id="grupo_asignar"/>
                        <label for="unidad_id_asignar" class="control-label">Empleado:</label>
                        <select name="distribuidor_id" id="unidad_id_asignar" class="form-control" data-validation="required">
                            <option value="" selected="selected">Seleccionar</option>
                            <?php foreach ($distribuidores as $distribuidor) : ?>
                                <option value="<?= $distribuidor['id_empleado']; ?>"><?= escape($distribuidor['nombres'].' '.$distribuidor['paterno'].' '.$distribuidor['materno']); ?></option>
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
    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.es.js"></script>
    <script src="<?= js; ?>/jquery.base64.js"></script>
    <script src="<?= js; ?>/pdfmake.min.js"></script>
    <script src="<?= js; ?>/vfs_fonts.js"></script>
    <script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>

    <script> 
        $(function () {
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

                    window.location = '?/distribuidor/listar' + inicial_fecha + final_fecha;
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

            var $modal_asignar = $('#modal_asignar'), $form_asignar = $('#form_asignar'), $asignar = $('[data-asignar]');
            $asignar.on('click', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var grupo = $(this).attr('data-grupo');
                $('#grupo_asignar').val(grupo);
                $form_asignar.attr('action', href);
                $modal_asignar.modal('show');
            });

            <?php if ($permiso_eliminar) { ?>
            $('[data-eliminar]').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                bootbox.confirm('Está seguro que desea eliminar el empleado?', function (result) {
                    if(result){
                        window.location = url;
                    }
                });
            });
            <?php } ?>

            $.validate({
                form: '#modal_asignar',
                modules: 'basic'
            });

            <?php if ($permiso_crear) { ?>
            $(window).bind('keydown', function (e) {
                if (e.altKey || e.metaKey) {
                    switch (String.fromCharCode(e.which).toLowerCase()) {
                        case 'n':
                            e.preventDefault();
                            window.location = '?/empleados/crear';
                            break;
                    }
                }
            });
            <?php } ?>

            <?php if ($empleados) { ?>
            var table = $('#table').DataFilter({
                filter: false,
                name: 'empleados',
                reports: 'xls|doc|pdf|html'
            });
            <?php } ?>
        });
    </script>
<?php require_once show_template('footer-configured'); ?>