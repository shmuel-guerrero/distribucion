<?php
$cuent = $db->select('*')->from('con_cuenta')->fetch();
$mone = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();
//$plan=$db->select('*')->from('con_plan')->fetch();
$a=(sizeof($params) > 0) ? $params[0] : 0;
$b=(sizeof($params) > 0) ? $params[1] : 0;

function convercionm($mon,$tip,$db){
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();
    if($tip>1){
        $tipob = $db->select('*')->from('con_tipo_moneda')->where('id_moneda',$tip)->fetch_first();
        $mon = $mon/$tipob['valor'];
    }
    $res = $mon  / $tipo['valor'];

    echo $res;
}
// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading" onload=";suma_d();suma_h();">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Asiento de Cierre</strong>
    </h3>
</div>
<div class="panel-body">
    <?php if ($permiso_listar) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para regresar al listado de cuentas hacer clic en el siguiente botón:</div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <a href="?/diario/listar" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span><span> Listado</span></a>
            </div>
        </div>
        <hr>
    <?php } ?>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <form method="post" action="?/diario/guardar" class="form-horizontal">
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Fecha:</label>
                    <div class="col-md-9">
                        <input type="date" class="form-control" name="fecha" id="fecha" required value=<?= date("Y-m-d")?>>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Fecha:</label>
                    <div class="col-md-9">
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" value="0" onchange="location = '?/diario/crear/<?= $a ?>/<?= $b ?>'"> Contable
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" value="1" onchange="location = '?/diario/crear/<?= $a ?>/<?= $b ?>'"> Apertura
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" value="2" checked="checked" > Cierre
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Comentarios:</label>
                    <div class="col-md-9">
                        <textarea name="glosa" class="form-control" maxlength="500" cols="80" rows="6" id="glosa" required="required">Asiento de cierre de <?= date('d/m/Y')?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">CUENTA</div>
                    <div class="col-md-3">DEBE</div>
                    <div class="col-md-3">HABER</div>
                </div>

                <?php
                    $act=$db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=".$a." AND a.comprobante<".$b." AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
                    $i=0;
                    $deb=0;
                    $hab=0;
                    foreach($act as $ac){if($ac['nodo']==1 || $ac['nodo']==2 || $ac['nodo']==3){}else{$i++;?>
                        <div class="form-group">
                            <div class="col-md-6">
                                <select name="sele_<?= $i ?>" id="sele_<?= $i ?>" class="form-control" required >
                                    <?php foreach($cuent as $cue){
                                        echo "<option value=".$cue['n_cuenta']." disabled style='background: rgba(0,0,0,0.3);color: rgba(255,255,255,1);' >".$cue['cuenta']."</option>";
                                        $ar=$cue['n_cuenta'];
                                        $order_by = array(
                                            'n_plan' => 'asc'
                                        );
                                        $plan=$db->select('*')->from('con_plan')->like('n_plan',$ar, 'after')->order_by($order_by)->fetch();
                                        foreach($plan as $pl){
                                            if($ac['n_plan']==$pl['n_plan']) {
                                                echo "<option value=" . $pl['n_plan'] . " selected>" . $pl['plan_cuenta'] . "</option>";
                                            }else{
                                                echo "<option value=" . $pl['n_plan'] . " >" . $pl['plan_cuenta'] . "</option>";
                                            }
                                        }
                                    }?>

                                </select>
                            </div>
                            <?php if($ac['SUM(a.haber)']>$ac['SUM(a.debe)']){ $deb=$deb+$ac['SUM(a.haber)']-$ac['SUM(a.debe)'];?>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                        <input type="number" class="form-control" name="debe_<?= $i ?>" id="debe_<?= $i ?>" required value="<?=  convercionm($ac['SUM(a.haber)']-$ac['SUM(a.debe)'],1,$db) ?>" onchange="sumar_d(<?= $i ?>)" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                        <input type="number" class="form-control" name="haber_<?= $i ?>" id="haber_<?= $i ?>" required value="0" onchange="sumar_d(<?= $i ?>)" />
                                    </div>
                                </div>
                            <?php }else{ $hab=$hab-$ac['SUM(a.haber)']+$ac['SUM(a.debe)'];?>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                        <input type="number" class="form-control" name="debe_<?= $i ?>" id="debe_<?= $i ?>" required value="0" onchange="sumar_h(<?= $i ?>)" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                        <input type="number" class="form-control" name="haber_<?= $i ?>" id="haber_<?= $i ?>" required value="<?= convercionm($ac['SUM(a.debe)']-$ac['SUM(a.haber)'],1,$db) ?>" onchange="sumar_h(<?= $i ?>)" />
                                    </div>
                                </div>
                            <?php }?>
                        </div>
                    <?php }}$i=$i+1;?>
                    <div class="form-group">
                        <?php if($deb>$hab){?>
                            <div class="col-md-6">
                                <select name="sele_<?= $i ?>" id="sele_<?= $i ?>" class="form-control" required >
                                    <option value="3.1.1.7" selected >UTILIDAD NETA DE LA GESTION</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="debe_<?= $i ?>" id="debe_<?= $i ?>" required value="0" onchange="sumar_d(<?= $i ?>)" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="haber_<?= $i ?>" id="haber_<?= $i ?>" required value="<?= convercionm($deb-$hab,1,$db) ?>" onchange="sumar_d(<?= $i ?>)" />
                                </div>
                            </div>
                        <?php }else{ ?>
                            <div class="col-md-6">
                                <select name="sele_<?= $i ?>" id="sele_<?= $i ?>" class="form-control" required >
                                    <option value="3.1.1.8" selected disabled>PERDIDA NETA DE LA GESTION</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="debe_<?= $i ?>" id="debe_<?= $i ?>" required value="<?= convercionm($hab-$deb,1,$db) ?>" onchange="sumar_h(<?= $i ?>)" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="haber_<?= $i ?>" id="haber_<?= $i ?>" required value="0" onchange="sumar_h(<?= $i ?>)" />
                                </div>
                            </div>
                        <?php }?>
                    </div>
                    <input type="hidden" name="nro" id="nro" value="<?= $i ?>"/>
                    <input type="hidden" name="an" id="an" value="<?= $a ?>" />
                    <input type="hidden" name="bn" id="bn" value="<?= $b ?>" />
                    <div class="row total form-group">
                        <div class="col-md-6">

                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                <input type="text" value="0" class="debe form-control" id="debe"
                                               disabled="disabled">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                <input type="text" value="0" class="haber form-control" id="haber"
                                       disabled="disabled">
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
        <hr>
        <div class="form-group">
            <div class="col-md-9 col-md-offset-3">
                <button type="submit" class="btn btn-primary">
                    <span class="<?= ICON_SUBMIT; ?>"></span>
                    <span>Guardar</span>
                </button>
                <button type="button" onclick="location= '?/diario/crear/<?= $a ?>/<?= $b ?>'" class="btn btn-default">
                    <span class="<?= ICON_RESET; ?>"></span>
                    <span>Restablecer</span>
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

        $.validate({
            modules: 'basic'
        });

        $('#telefono').selectize({
            persist: false,
            createOnBlur: true,
            create: true,
            onInitialize: function () {
                $('#telefono').css({
                    display: 'block',
                    left: '-10000px',
                    opacity: '0',
                    position: 'absolute',
                    top: '-10000px'
                });
            },
            onChange: function () {
                $('#telefono').trigger('blur');
            },
            onBlur: function () {
                $('#telefono').trigger('blur');
            }
        });

        $(':reset').on('click', function () {
            $('#telefono')[0].selectize.clear();
        });

        $('.form-control:first').select();
        suma_d();
        suma_h();
    });
</script>
<script>
    function suma_d () {
        var total = 0;
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
    function cierre(a,b){
        $.post('?/diario/asiento_cierre',{a:a,b:b},function(){
            suma_d ();
            suma_h ();
        });
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
    var parametro;
    function popup(aa)
    {
        parametro = window.open("?/diario/form_factura/"+aa,"","width=400,height=700");
        parametro.document.getElementById('1').value = "num" ;
    }
</script>
<?php require_once show_template('footer-advanced'); ?>


