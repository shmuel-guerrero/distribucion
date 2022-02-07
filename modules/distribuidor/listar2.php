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
$empleados = $db->select('z.id_empleado, z.nombres, z.paterno, z.materno, GROUP_CONCAT(a.ruta_id SEPARATOR "&") as emp, GROUP_CONCAT(a.grupo_id SEPARATOR "&") as emp2, z.fecha, u.username, r.nombre as ruta, r.dia')->from('gps_asigna_distribucion a')->join('sys_empleados z','a.distribuidor_id = z.id_empleado','INNER')->join('sys_users u','u.persona_id = z.id_empleado','LEFT')->join('gps_rutas r','a.ruta_id = r.id_ruta','LEFT')->where('a.estado',1)->group_by('a.distribuidor_id')->fetch();

//var_dump($empleados);
// Obtiene los permisos
$permisos = explode(',', permits);

// obtener las cantidades vendidas
$egresos = $db->select('a.*, SUM(b.monto_total) as total, count(b.id_egreso) as registros')->from('sys_empleados a')->join('inv_egresos b','a.id_empleado = b.empleado_id')->where('b.estadoe','1')->where('b.fecha_egreso>=',$fecha_inicial)->where('b.fecha_egreso<=',$fecha_final)->group_by('a.id_empleado')->fetch();
// var_dump($empleados);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_ver2 = in_array('vertodo', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_activar = in_array('activar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
    <div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Listar distribuidores</strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $empleados)) { ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <div class="text-label">Para agregar nuevos empleados hacer clic en el siguiente botón: </div>
                </div>
                <div class="col-xs-12 col-sm-4 text-right">
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
        <?php if ($empleados) { ?>
            <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                <thead>
                <tr class="active">
                    <th class="text-nowrap">#</th>
                    <th class="text-nowrap">Nombres</th>
                    <th class="text-nowrap">Rutas</th>
<!--                     <th class="text-nowrap">Apellido materno</th> -->
                    <th class="text-nowrap">Total</th>
                    <th class="text-nowrap">Registros</th>
                    <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                        <th class="text-nowrap">Opciones</th>
                    <?php } ?>
                </tr>
                </thead>
                <tfoot>
                <tr class="active">
                    <th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombres</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Rutas</th>
<!--                     <th class="text-nowrap text-middle" data-datafilter-filter="true">Apellido materno</th> -->
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                    <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                        <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                    <?php } ?>
                </tr>
                </tfoot>
                <tbody>
                <?php foreach ($empleados as $nro => $empleado) { ?>                    
                    <tr>
                        <th class="text-nowrap"><?= $nro + 1; ?></th>
                        <td class="text-nowrap text-uppercase"><?= escape($empleado['nombres']) . " " . escape($empleado['paterno']) . " " . escape($empleado['materno']); ?><br>
                            <small data-usuario="<?= $empleado['id_empleado']; ?>" class="text-success">usuario: <?= escape($empleado['username']) ?></small>
                        </td>
                        <td class="text-nowrap text-uppercase"><?= ($empleado['ruta']) ? escape($empleado['ruta']) : 'ruta no asignada'; ?><br>
                            <small class="text-success"><?php switch($empleado['dia']){case 0: echo "Domingo "; break; case 1: echo "Lunes"; break; case 2: echo "Martes"; break; case 3: echo "Miercoles"; break; case 4: echo "Jueves"; break; case 5: echo "Viernes"; break; case 6: echo "Sábado"; break; default: echo "DIA NO ASIGNADO";} ?></small>
                        </td>                      
                        <td class="text-nowrap"><?php
                            $emp = explode('&',$empleado['emp']);
                            $emp2 = explode('&',$empleado['emp2']);
//                             var_dump($emp);
                            $c = 0 ;
                            $total = 0;
                            $registros = 0;
                            $where = '';

                            if(date('Y-m-d')<=$fecha_final){

                            }else{
                                foreach($emp as $em){
                                    // var_dump($emp);
                                    if($emp[$c] != 0){
                                        $ruta = $db->select('*')->from('gps_rutas')->where('id_ruta',$emp[$c])->fetch_first();
//                                        var_dump($ruta);
                                        $fecha = $db->select('fecha')->from('sys_empleados')->where('id_empleado',$ruta['empleado_id'])->fetch_first();
                                        
                                        if($fecha['fecha'] >= date("Y-m-d")){
                                            $fecha = $fecha['fecha'];
                                        }else{
                                            $fecha = date("Y-m-d",strtotime(date('Y-m-d')."- 1 days"));
                                        }
                                        $egresos = $db->select(' SUM(b.monto_total) as total, count(b.id_egreso) as registros')->from('sys_empleados a')->join('inv_egresos b','a.id_empleado = b.empleado_id')->where('b.estadoe',2)->where('b.grupo','')->where('b.fecha_egreso<=',$fecha)->where('b.ruta_id',$emp[$c])->fetch_first();
//                                        echo $db->last_query();
//                                        var_dump($egresos);
//                                        var_dump($emp[$c]);
                                        $total = $total+$egresos['total'];
                                        $registros = $registros + $egresos['registros'];
                                    }
                                    $c++;
                                }
                                foreach($emp2 as $em){
                                    if($em != ''){
                                        $fecha = date("Y-m-d",strtotime(date('Y-m-d')));
                                        $egresos = $db->select(' SUM(b.monto_total) as total, count(b.id_egreso) as registros')->from('sys_empleados a')->join('inv_egresos b','a.id_empleado = b.empleado_id')->where('b.estadoe','2')->where('b.fecha_egreso<=',$fecha)->where('b.grupo',$em)->fetch_first();
                                        //var_dump($egresos);
                                        $total = $total+$egresos['total'];
                                        $registros = $registros + $egresos['registros'];
                                    }
                                }
                                echo $total;
                                //var_dump($egresos);
                            }
                             ?></td>
                        <td class="text-nowrap"><?= escape($registros); ?></td>
                        <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                            <td class="text-nowrap">
                                <?php if ($permiso_ver) { ?>
                                    <a href="?/distribuidor/visitas/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial; ?>/<?= $fecha_final; ?>" data-toggle="tooltip" style="margin-right: 3px;" data-title="Ver ruta"><i class="glyphicon glyphicon-search"></i></a>
                                <?php } ?>
                                <?php if ($permiso_ver2) { ?>
                                    <a href="?/distribuidor/ver2/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial; ?>/<?= $fecha_final; ?>" data-toggle="tooltip" style="margin-right: 3px;" data-title="Hoja de salida"><i class="glyphicon glyphicon-list-alt"></i></a>
                                <?php } ?>
                                <?php if($empleado && false){ ?>
                                    <a href="?/distribuidor/asignar/<?= $empleado['empleado_id']; ?>" class="underline-none" data-toggle="tooltip"  style="margin-right: 3px;" data-title="Asignar nuevo distribuidor" data-asignar="true">
                                        <i class="glyphicon glyphicon-bed"></i>
                                    </a>
                                <?php } ?>
                                <?php if ($permiso_imprimir) { ?>
                                    <a href="?/distribuidor/imprimir2/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial ?>/<?= $fecha_final ?>" target="_blank" data-toggle="tooltip" style="margin-right: 3px;" data-title="Imprimir Hoja de Salida" ><i class="glyphicon glyphicon-print"></i></a>
                                    <a href="?/distribuidor/imprimir1/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial ?>/<?= $fecha_final ?>" target="_blank" data-toggle="tooltip" style="margin-right: 3px;" data-title="Imprimir Notas de venta"><i class="glyphicon glyphicon-list"></i></a>
                                    <button type="button" onclick="imprimir(<?= $empleado['id_empleado']; ?>)" data-toggle="tooltip" style="margin-right: 3px;" data-title="Imprimir ventas (termico)"><i class="glyphicon glyphicon-list"></i></button>
                                    <!--<a href="?/distribuidor/imprimir1/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial ?>/<?= $fecha_final ?>" target="_blank" data-title="Imprimir ventas" ><i class="glyphicon glyphicon-list"></i></a>-->
                                <?php } ?>
                                <?php if ($permiso_activar) { ?>
                                    <?php if ($empleado['fecha'] != date('Y-m-d')) { ?>
                                        <a href="?/distribuidor/activar2/<?= $empleado['id_empleado']; ?>" class="text-info" data-toggle="tooltip" style="margin-right: 3px;" data-title="Cerrar distribucion" data-activar="true"><i class="glyphicon glyphicon-unchecked"></i></a>
                                        <a href="?/distribuidor/activar/<?= $empleado['id_empleado']; ?>" class="text-danger" data-toggle="tooltip" style="margin-right: 3px;" data-title="Cerrar distribucion (limpiar)" data-activar1="true"><i class="glyphicon glyphicon-unchecked"></i></a>
                                    <?php } else { ?>
                                        <a href="?/distribuidor/imprimir3/<?= $empleado['id_empleado']; ?>" class="btn btn-default btn-xs text-success" target="_blank" data-toggle="tooltip" style="margin-right: 3px;" data-title="Imprimir liquidación" ><i class="glyphicon glyphicon-print"></i></a>  
                                        <a href="?/distribuidor/activar3/<?= $empleado['id_empleado']; ?>" class="text-success" data-toggle="tooltip" style="margin-right: 3px;" data-title="Entrega realizada"><i class="glyphicon glyphicon-check"></i></a>
                                                                   
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>No existen empleados registrados en la base de datos, para crear nuevos empleados hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
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
                        <label for="unidad_id_asignar" class="control-label">Empleado:</label>
                        <select name="distribuidor_id" id="unidad_id_asignar" class="form-control" data-validation="required">
                            <option value="" selected="selected">Seleccionar</option>
                            <?php foreach ($empleados as $empleado) : ?>
                                <option value="<?= $empleado['id_empleado']; ?>"><?= escape($empleado['nombres'].' '.$empleado['paterno'].' '.$empleado['materno']); ?></option>
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
    <script src="<?= js; ?>/sweetalert2.all.min.js"></script>
    <script>
        function imprimir(distribuidor){
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '?/distribuidor/imprimir1_termico',
                data: {
                    distribuidor: distribuidor
                }
            }).done(function (ventas) {
                if (ventas) {
                    ventas.forEach(function (venta) {
                        $.ajax({
                            type: 'post',
                            dataType: 'json',
                            url: 'http://localhost:9000/sistema/nota.php',
                            data: venta
                        }).done(function (respuesta) {
                            console.log(respuesta);
                            $('#loader').fadeOut(100);
                            switch (respuesta.estado) {
                                case 's':
                                    $.notify({
                                        title: '<strong>Operación satisfactoria!</strong>',
                                        message: '<div>Imprimiendo factura...</div>'
                                    }, {
                                        type: 'success'
                                    });
                                    break;
                                default:
                                    $.notify({
                                        title: '<strong>Advertencia!</strong>',
                                        message: '<div>La impresora no responde, asegurese de que este conectada y vuelva a intentarlo nuevamente.</div>'
                                    }, {
                                        type: 'danger'
                                    });
                                    break;
                            }
                        }).fail(function () {
                            $('#loader').fadeOut(100);
                            $.notify({
                                title: '<strong>Error!</strong>',
                                message: '<div>Ocurrió un problema en el envio de la información, vuelva a intentarlo nuevamente y si persiste el problema contactese con el administrador.</div>'
                            }, {
                                type: 'danger'
                            });
                        });
                    });
                } else {
                    $('#loader').fadeOut(100);
                    $.notify({
                        title: '<strong>Error!</strong>',
                        message: '<div>Ocurrió un problema al obtener los datos de la venta e imprimir.</div>'
                    }, {
                        type: 'danger'
                    });
                }
            }).fail(function () {
                $('#loader').fadeOut(100);
                $.notify({
                    title: '<strong>Error!</strong>',
                    message: '<div>Ocurrió un problema al obtener los datos de la venta e imprimir.</div>'
                }, {
                    type: 'danger'
                });
            });
        }
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

                    window.location = '?/distribuidor/listar2' + inicial_fecha + final_fecha;
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
            <?php if ($permiso_activar) { ?>
                $('[data-activar]').on('click', function (e) {
                    e.preventDefault();
                    var url = $(this).attr('href');
                    let id_ususario = url.split("/");
                    id_usuario = (id_ususario[3]) ? id_ususario[3] : 0;

                    let $fila_tabla = $(this).parent().parent();
                    let dato_usuario = $fila_tabla.find("[data-usuario=" + id_usuario + "]").text();
                    let usuario_obtenido = dato_usuario.split(":");
                    usuario_obtenido = usuario_obtenido[1].toUpperCase();

                    Swal.fire({
                        title: '<h3>ESTA SEGURO DE CERRAR LA DISTRIBUCIÓN DE </h3><h3 class="text-danger">' + usuario_obtenido + ' ?</h3>',
                        width: 800,
                        html: "<h4 class='text-danger'>Esta operación cerrara la distribución. Acción irreversible!!</h4><h5>Esta operación repercutira en las operaciones del distribuidor en Dispositivo Movil</h5>",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        cancelButtonText: 'CANCELAR',
                        confirmButtonText: 'SI, CERRAR!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire(
                                'Cerrado!',
                                'La distribución fue cerrada.',
                                'success'
                            );
                            window.location = url;
                        }
                    });

                });
                $('[data-activar1]').on('click', function (e) {
                    e.preventDefault();
                    var url = $(this).attr('href');

                    let id_ususario = url.split("/");
                    id_usuario = (id_ususario[3]) ? id_ususario[3] : 0;

                    let $fila_tabla = $(this).parent().parent();
                    let dato_usuario = $fila_tabla.find("[data-usuario=" + id_usuario + "]").text();
                    let usuario_obtenido = dato_usuario.split(":");
                    usuario_obtenido = usuario_obtenido[1].toUpperCase();

                    Swal.fire({
                        title: '<h3>PRECAUCIÓN !!!    ESTA SEGURO DE LIMPIAR LA DISTRIBUCIÓN DE </h3><h3 class="text-primary">' + usuario_obtenido + ' ?</h3>',
                        width: 800,
                        html: "<h4 class='text-primary'>Esta operación limpiara la distribución. Acción irreversible!!</h4><h5>Esta operación repercutira en las operaciones del distribuidor en Dispositivo Movil; las entregas no ejecutadas volveran al almacen.</h5>",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        cancelButtonText: 'CANCELAR',
                        confirmButtonText: 'SI, LIMPIAR!',
                        background: 'rgba(235, 78, 90, 1)'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire(
                                'Limpiado!',
                                'se limpio la distribución.',
                                'success'
                            );
                            window.location = url;
                        }
                    });

                 

                });
            <?php } ?>
        });
    </script>
<?php require_once show_template('footer-configured'); ?>