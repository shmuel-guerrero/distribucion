<?php

function convercionm($mon, $tip, $db)
{
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
    if ($tip > 1) {
        $tipob = $db->select('*')->from('con_tipo_moneda')->where('id_moneda', $tip)->fetch_first();
        $mon = $mon / $tipob['valor'];
    }

    $res = $mon / $tipo['valor'];

    echo $res;
}

//$plan=$db->select('*')->from('con_plan')->fetch();
$a = (sizeof($params) > 0) ? $params[0] : 0;
//$b=$_POST['b'];
$cuent = $db->select('*')->from('con_cuenta')->fetch();
$mone = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
$comp = $db->select('*')->from('con_comprobante')->where('codigo', $a)->fetch_first();
$cts = $db->select('*')->from('con_asiento')->where('comprobante', $a)->fetch();
// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Editar asiento</strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_listar) { ?>
            <div class="row">
                <div class="col-sm-7 col-md-6 hidden-xs">
                    <div class="text-label">Para realizar una acción hacer clic en los botones:</div>
                </div>
                <div class="col-xs-12 col-sm-5 col-md-6 text-right">
                    <?php if ($permiso_crear) { ?>
                        <a href="?/diario/crear" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span><span
                                class="hidden-xs hidden-sm"> Nuevo</span></a>
                    <?php } ?>
                    <?php if ($permiso_eliminar) { ?>
                        <a href="?/diario/eliminar/<?= $a; ?>" class="btn btn-danger"
                           data-eliminar="true"><span class="glyphicon glyphicon-trash"></span><span class="hidden-xs hidden-sm"> Eliminar</span></a>
                    <?php } ?>
                    <?php if ($permiso_listar) { ?>
                        <a href="?/diario/listar" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span><span
                                class="hidden-xs"> Listado</span></a>
                    <?php } ?>
                </div>
            </div>
            <hr>
        <?php } ?>
        <div class="row" id="edi">
            <div class="col-sm-8 col-sm-offset-2">
                <form method="post" action="?/diario/guardar" class="form-horizontal">
                    <div class="form-group">
                        <label for="almacen" class="col-md-3 control-label">Fecha:</label>

                        <div class="col-md-9">
                            <input type="date" class="form-control" name="fecha" id="fecha" required
                                   value="<?= $comp['fecha'] ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="almacen" class="col-md-3 control-label">Tipo:</label>

                        <div class="col-md-9">
                            <label class="radio-inline">
                                <input type="radio" name="ajuste" value="0" <?php if ($comp['tipo'] == 0) {
                                    echo 'checked';
                                } ?> > Contable
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="ajuste" value="1" <?php if ($comp['tipo'] == 1) {
                                    echo 'checked';
                                } ?>> Apertura
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="ajuste" value="2"
                                       onchange="cierre(<?= $a ?>,<?= $b ?>)" <?php if ($comp['tipo'] == 2) {
                                    echo 'checked';
                                } ?>> Cierre
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="almacen" class="col-md-3 control-label">Comentarios:</label>

                        <div class="col-md-9">
                            <textarea name="glosa" maxlength="500" cols="80" rows="6" id="glosa"
                                      required="required"
                                      class="form-control"><?= utf8_decode($comp['glosa']) ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">CUENTA</div>
                        <div class="col-md-3">DEBE</div>
                        <div class="col-md-3">HABER</div>
                    </div>
                    <?php $i = 0;
                    foreach ($cts as $ct) {
                        $i++; ?>
                        <div class="form-group">
                            <div class="col-md-6">
                                <select name="sele_<?= $i ?>" id="sele_<?= $i ?>" class="form-control" required>
                                    <?php foreach ($cuent as $cue) {
                                        echo "<option value=" . $cue['n_cuenta'] . " disabled style='background: rgba(0,0,0,0.3);color: rgba(255,255,255,1);' >" . $cue['cuenta'] . "</option>";
                                        $ac = $cue['n_cuenta'];
                                        $order_by = array(
                                            'n_plan' => 'asc'
                                        );
                                        $plan = $db->select('*')->from('con_plan')->like('n_plan', $ac, 'after')->order_by($order_by)->fetch();
                                        foreach ($plan as $pl) {
                                            if ($pl['n_plan'] == $ct['cuenta']) {
                                                echo "<option value=" . $pl['n_plan'] . " selected >" . $pl['plan_cuenta'] . "</option>";
                                            } else {
                                                echo "<option value=" . $pl['n_plan'] . ">" . $pl['plan_cuenta'] . "</option>";
                                            }
                                        }
                                    }?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" name="debe_<?= $i ?>" class="form-control"
                                           id="debe_<?= $i ?>" required
                                           value="<?= convercionm($ct['debe'], '1', $db) ?>"
                                           onchange="sumar_d(<?= $i ?>)"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" name="haber_<?= $i ?>" class="form-control"
                                           id="haber_<?= $i ?>" required
                                           value="<?= convercionm($ct['haber'], '1', $db) ?>"
                                           onchange="sumar_h(<?= $i ?>)"/>
                                </div>
                            </div>
                        </div>

                    <?php } ?>
                    <div class="form-group">
                        <div class="col-md-6">

                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                <input type="text" value="0" class="form-control" id="debe" disabled="disabled"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                <input type="text" value="0" class="form-control" id="haber" disabled="disabled"/>
                                <input type="hidden" name="id" value="<?= $a ?>"/>
                                <input type="hidden" id="nro" name="nro" value="<?= $i ?>"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">
                                <span class="<?= ICON_SUBMIT; ?>"></span>
                                <span>Guardar</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= js; ?>/jquery.form-validator.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.es.js"></script>
    <script src="<?= js; ?>/selectize.min.js"></script>
    <script>
        $(function () {
            suma_d();
            suma_h();
            $.validate({
                modules: 'basic'
            });

            $(':reset').on('click', function () {
                $('#telefono')[0].selectize.clear();
            });

            $('.form-control:first').select();

            <?php if ($permiso_eliminar) { ?>
            $('[data-eliminar]').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                bootbox.confirm('Está seguro que desea eliminar el almacén?', function (result) {
                    if (result) {
                        window.location = url;
                    }
                });
            });
            <?php } ?>
        });
    </script>
    <script>

        function suma_d () {
            var total = 0;
            //numero de inputs
            Cont=document.getElementsByName("nro")[0].value;

            for(k=1;k<=Cont;k++){
                valor = document.getElementsByName("debe_"+k)[0].value; // Convertir el valor a un entero (número).
                if(valor != ''){
                    total = (parseInt(total) + parseInt(valor));
                }
            }
            $("#debe").val(total);
        }
        function suma_h () {
            var total = 0;
            Cont=document.getElementsByName("nro")[0].value;

            for(k=1;k<=Cont;k++){
                valor = document.getElementsByName("haber_"+k)[0].value; // Convertir el valor a un entero (número).
                if(valor != ''){
                    total = (parseInt(total) + parseInt(valor));
                }
            }
            $("#haber").val(total);
        }
        function sumar_d (ic) {
            var total = 0;

            $("#haber_"+ic).val(0);
            suma_h();

            Cont=document.getElementsByName("nro")[0].value;

            for(k=1;k<=Cont;k++){
                valor = document.getElementsByName("debe_"+k)[0].value; // Convertir el valor a un entero (número).
                if(valor != ''){
                    total = (parseInt(total) + parseInt(valor));
                    // Colocar el resultado de la suma en el control "span".
                    //document.getElementById('debe').attr('value',tatal);
                }
            }
            $("#debe").val(total);
        }
        function sumar_h (ic) {
            var total = 0;

            $("#debe_"+ic).val(0);
            suma_d();

            Cont=document.getElementsByName("nro")[0].value;

            for(k=1;k<=Cont;k++){
                valor = document.getElementsByName("haber_"+k)[0].value; // Convertir el valor a un entero (número).
                if(valor != ''){
                    total = (parseInt(total) + parseInt(valor));
                    // Colocar el resultado de la suma en el control "span".
                    //document.getElementById('debe').attr('value',tatal);
                }
            }
            $("#haber").val(total);

        }
    </script>

<?php require_once show_template('footer-configured'); ?>