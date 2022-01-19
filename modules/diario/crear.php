<?php

$cuent = $db->select('*')->from('con_cuenta')->fetch();
$mone = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();
//$plan=$db->select('*')->from('con_plan')->fetch();
$a=(sizeof($params) > 0) ? $params[0] : 0;
$b=(sizeof($params) > 0) ? $params[1] : 0;

$ini=$db->select('*')->from('con_comprobante')->where('tipo',1)->fetch_first();
// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>

<style>
.div_line{
    display:none;
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom:0;
}
#div_line_1,
#div_line_2{
    display:block;
    margin-bottom:-1;
}
</style>

<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Crear asiento</strong>
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
            <form method="post" action="?/diario/guardar" name="form1" id="formulario" class="form-horizontal">
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Fecha:</label>
                    <div class="col-md-9">
                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Tipo:</label>
                    <div class="col-md-9">
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" value="0" <?php if($ini['codigo']==null){echo 'disabled';}else{echo 'checked';} ?> > Contable
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" value="1" <?php if($ini['codigo']>0){echo 'disabled';}else{echo 'checked';} ?> > Apertura
                        </label>
                        <label class="radio-inline">
                            <input type="radio" onchange="location = '?/diario/asiento_cierre/<?= $a ?>/<?= $b ?>'" name="ajuste" value="2" <?php if($ini['codigo']==null){echo 'disabled';} ?> > Cierre
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Comentarios(Glosa):</label>
                    <div class="col-md-9">
                        <textarea name="glosa" class="form-control" maxlength="500" cols="80" rows="6" id="glosa" required="required"></textarea>
                    </div>
                </div>

                <div class="form-group div_line" style="margin-bottom: -1px; display: block; background-color: #eee;">
                    <div class="col-md-6">CUENTA</div>
                    <div class="col-md-3">DEBE</div>
                    <div class="col-md-3">HABER</div>
                </div>
                <div id="lista">
                    <?php $nro=30;?>
                    <input type="hidden" name="nro" value="<?= $nro ?>"/>
                    <?php 
                    for($i=1;$i<=$nro;$i++){
                        if($i<=2){
                        ?>
                        <div class="form-group div_line" id="div_line_<?php echo $i; ?>">
                            <div class="col-md-4">                                                        
                                <div class="input-group" style="width: 100%;">
                                    <select class="form-control text-uppercase" required data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true" name="sele_<?= $i ?>" id="sele_<?= $i ?>" onchange="setNewLine('<?= $i ?>');" required>
                                        <option value=""></option>
                                        <?php foreach($cuent as $cue){
                                            echo "<option value=".$cue['n_cuenta']." disabled style='background: rgba(0,0,0,0.3);color: rgba(255,255,255,1);' >".$cue['cuenta']."</option>";
                                            $a=$cue['n_cuenta'];
                                            $order_by = array(
                                                'n_plan' => 'asc'
                                            );
                                            $plan=$db->select('*')->from('con_plan')->like('n_plan',$a, 'after')->order_by($order_by)->fetch();
                                            foreach($plan as $pl){
                                                echo "<option value=".$pl['n_plan'].">".$pl['n_plan']." - ".$pl['plan_cuenta']."</option>";
                                            }
                                        }?>
                                    </select>
                                </div>    
                            </div>                            
                            <div class="col-md-1">
                                <a onClick="popup(<?= $i ?>)" data-toggle="tooltip" data-title="Añadir factura"><i class="glyphicon glyphicon-file" style="font-size: 2em;"></i></a>
                            </div>
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="facto_<?= $i ?>" id="facto_<?= $i ?>" disabled />
                                <input type="hidden"  name="fact_<?= $i ?>" id="fact_<?= $i ?>" />
                            </div>
                            <!-- los campos obligatorios -->
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="debe_<?= $i ?>" id="debe_<?= $i ?>" required placeholder="0" onchange="sumar_d(<?= $i ?>)"  />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="haber_<?= $i ?>" id="haber_<?= $i ?>" required placeholder="0" onchange="sumar_h(<?= $i ?>)"/>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="form-group div_line" id="div_line_<?php echo $i; ?>">
                            <div class="col-md-4">
                                <div class="input-group" style="width: 100%;">
                                    <select name="sele_<?= $i ?>" id="sele_<?= $i ?>" class="form-control"  onchange="setNewLine('<?= $i ?>');">
                                        <option value=""></option>                                        
                                        <?php foreach($cuent as $cue){
                                            echo "<option value=".$cue['n_cuenta']." disabled style='background: rgba(0,0,0,0.3);color: rgba(255,255,255,1);' >".$cue['cuenta']."</option>";
                                            $a=$cue['n_cuenta'];
                                            $order_by = array(
                                                'n_plan' => 'asc'
                                            );
                                            $plan=$db->select('*')->from('con_plan')->like('n_plan',$a, 'after')->order_by($order_by)->fetch();
                                            foreach($plan as $pl){
                                                echo "<option value=".$pl['n_plan'].">".$pl['plan_cuenta']."</option>";
                                            }
                                        }?>
                                    </select>
                                </div>    
                            </div>
                            <div class="col-md-1">
                                <a onClick="popup(<?= $i ?>);"><i class="glyphicon glyphicon-file" data-toggle="tooltip" data-title="Añadir factura" style="font-size: 2em;"></i></a>
                            </div>
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="facto_<?= $i ?>" id="facto_<?= $i ?>" disabled />
                                <input type="hidden" name="fact_<?= $i ?>" id="fact_<?= $i ?>" />
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="debe_<?= $i ?>" id="debe_<?= $i ?>"  onchange="sumar_d(<?= $i ?>);"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                    <input type="number" class="form-control" name="haber_<?= $i ?>" id="haber_<?= $i ?>"  onchange="sumar_h(<?= $i ?>);"/>
                                </div>
                            </div>
                        </div>
                    <?php }}?>

                    <div class="form-group row total div_line" style="margin-bottom: -1px; display: block;">
                        <div class="col-md-6"></div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                <input type="text" value="0" class="debe form-control" id="debe" disabled="disabled">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <div class="input-group-addon"><?= $mone['sigla'] ?></div>
                                <input type="text" value="0" class="haber form-control" id="haber" disabled="disabled">
                            </div>
                        </div>

                        <input type="text" class="form-control" name="totalx" id="totalx" value="0" data-validation="required number" data-validation-allowing="range[1;1]" data-validation-error-msg="Los totales en Debe y Haber no son iguales" style="width: 0; height: 0; padding: 0;">

                    </div>
                </div>

                <hr>
                <div class="form-group">
                    <div class="col-md-12" style="text-align:right;">
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
    
        for(i=1;i<=30;i++){
            $('#sele_'+i).selectize({
                persist: false,
                createOnBlur: true,
                create: true,
                onInitialize: function () {
                    $('#sele_'+i).css({
                        display: 'block',
                        left: '-10000px',
                        opacity: '0',
                        position: 'absolute',
                        top: '-10000px'
                    });
                },
                onChange: function () {
                    $('#sele_'+i).trigger('blur');
                },
                onBlur: function () {
                    $('#sele_'+i).trigger('blur');
                }
            });
        }
        $('.form-control:first').select();
    });
    function suma_d () {
        var total = 0;
        //numero de inputs
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
    function suma_h () {
        var total = 0;

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

    function Validar_Total(){        
        a=$("#debe").val();
        b=$("#haber").val();

        if(a==b){
            $("#totalx").val("1");
        }
        else{
            $("#totalx").val("0");
        }
        // alert(a+" - "+b+" - "+$("#totalx").val());
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

        Validar_Total();
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

        Validar_Total();
    }
    var parametro;
    function popup(aa)
    {
        parametro = window.open("?/diario/factura/"+aa,"","width=400,height=700");

    }
    function setNewLine(nro){
        nro++;
        $("#div_line_"+nro).css({'display':'block', 'margin-bottom':'-1px'});
    }
</script>
<?php require_once show_template('footer-advanced'); ?>

