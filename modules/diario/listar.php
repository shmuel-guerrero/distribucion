<?php

$cuenta=$db->select('*')->from('con_cuenta')->fetch();
function convercion($mon,$tip,$fra,$db){

    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();
    if($tipo['id_moneda']>1){
        $res = $mon/$fra;
    }
    else{
        $res = $mon;
    }
    echo number_format($res,2);
}
$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$g=0;
$comp1=$db->select('*')->from('con_comprobante')->where('tipo',1)->order_by('fecha','desc')->fetch();
$comp2=$db->select('*')->from('con_comprobante')->where('tipo',2)->order_by('fecha','desc')->fetch();

if(sizeof($params) > 0){
    $a=$params[0];
    $b=$params[1];
}else{
    if(isset($comp1[0]['codigo'])){
        $a=$comp1[0]['codigo'];
        $b=100000;
    }else{
        $a=0;
        $b=100000;
    }
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = true;
$permiso_editar = true;
$permiso_ver = true;
$permiso_eliminar = true;
$permiso_imprimir = true;

?>
<?php require_once show_template('header-configured'); ?>

<div class="panel-heading">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Cuentas</strong>
    </h3>
</div>
<div class="panel-body">
<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear)) { ?>
    <div class="row">
        <div class="col-sm-8 hidden-xs">
            <div class="text-label">Para agregar nuevos almacenes hacer clic en el siguiente botón: </div>
        </div>
        <div class="col-xs-12 col-sm-4 text-right">
            <?php if ($permiso_imprimir) { ?>
                <a href="?/diario/imprimir_inicial/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
            <?php } ?>
            <?php if ($permiso_crear) { ?>
                <a href="?/diario/crear/<?= $a ?>/<?= $b ?>" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span><span> Nuevo</span></a>
            <?php } ?>
        </div>
    </div>
    <div class="dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            Seleccionar periodo
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
            <li><a href="?/diario/listar/<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g]['fecha'] ?> - Actual</a></li>
            <?php
            $g++;
            foreach($comp2 as $cp2){?>
                <li><a href="?/diario/listar/<?= $comp1[$g]['codigo'] ?>/<?= $cp2['codigo'] ?>"><?= $comp1[$g]['fecha'] ?> - al - <?= $cp2['fecha'] ?></a></li>
                <?php $g++;
            }
            ?>
        </ul>
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
    <div class="table-responsive" style="overflow-x: hidden;">
        <table class="table">
            <tr class="osc">
                <th >Fecha</th>
                <th >Tipo</th>
                <th >Codigo</th>
                <th >Clasificación</th>
                <th>Cuenta</th>
                <th class="text-center">Debe</th>
                <th class="text-center">Haber</th>
                <th>Factura</th>
                <th class="text-center">Opciones</th>
            </tr>
            <?php
            $where = array(
                'codigo >=' => $a,
                'codigo <=' => $b
            );
            $comprob=$db->select('*')->from('con_comprobante')->where($where)->fetch();

            //preguntamos el tipo de moneda
            $tm = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();

            foreach($comprob as $comp){ ?>
                <tr class="osc">
                    <th colspan="9" class="text-center" style="background-color: #eee;"><b>ASIENTO Nº <?= $comp['codigo'] ?> </b></th>
                </tr>
                <?php
                $asiento=$db->select('*')->from('con_asiento')->where('comprobante',$comp['codigo'])->join('con_plan', 'con_plan.n_plan = con_asiento.cuenta')->fetch();
                $sw=1;$sw1=1;
                foreach($asiento as $asi){ $cu=$db->select('*')->from('con_cuenta')->where('n_cuenta',$asi['nodo'])->fetch_first()?>
                    <tr>
                        <?php if($sw==1){ $sw=0; ?>
                            <td rowspan="<?= count($asiento) ?>" style="border-left:2px solid #ddd;"><?= $comp['fecha'] ?></td>
                            <td rowspan="<?= count($asiento) ?>"><p>
                                <?php 
                                if($comp['tipo']==1){       echo 'Apertura';
                                }else{
                                    if($comp['tipo']==2){   echo 'Cierre';
                                    }else{                  echo 'Contable';
                                    }
                                }  ?>                                    
                            </p></td>
                        <?php } ?>
                        <td><?= $asi['cuenta'] ?></td>
                        <td><?= $cu['cuenta'] ?></td>
                        <td><?= $asi['plan_cuenta'] ?></td>
                        <td class="text-right"> <?php if($asi['debe']!=0){echo convercion($asi['debe'],'1',$comp['dolar'],$db).' '.$tm['sigla'];}else{echo number_format(0,2);} ?> </td>
                        <td class="text-right"> <?php if($asi['haber']!=0){echo convercion($asi['haber'],'1',$comp['dolar'],$db).' '.$tm['sigla'];}else{echo number_format(0,2);} ?> </td>
                        <td>
                            <?php if($asi['factura']!=0){ ?>
                                <a data-toggle="modal" data-target="#myModal"  onclick="ver(<?= $asi['factura'] ?>)" ><i class="glyphicon glyphicon-file"></i></a>
                            <?php } ?>
                        </td>
                        <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) {
                            if($sw1==1){ $sw1=0; ?>
                            <td rowspan="<?= count($asiento) ?>" class="text-center" style="border-right:2px solid #ddd;">
                                <?php if ($permiso_editar) { ?>
                                    <?php if($comp['estado']==0){?>
                                        <a onclick="deshabilitar(<?= $comp['codigo'] ?>)" data-toggle="tooltip" data-title="Deshabilitar asiento"><i class="glyphicon glyphicon-ok-circle" style="color:rgb(53, 111, 5);"></i></a>
                                    <?php }else{ ?><a onclick="habilitar(<?= $comp['codigo'] ?>)" data-toggle="tooltip" data-title="Habilitar asiento"><i class="glyphicon glyphicon-remove-circle" style="color:rgb(253, 78, 83);"></i></a>
                                    <?php } ?>
                                    <a href="?/diario/editar/<?= $comp['codigo'] ?>" data-toggle="tooltip" data-title="Modificar asiento" ><span class="glyphicon glyphicon-edit"></span></a>
                                <?php } ?>
                                <?php if ($permiso_eliminar) { ?>
                                    <a href="?/diario/eliminar/<?= $comp['codigo'] ?>" data-toggle="tooltip" data-title="Eliminar asiento" ><span class="glyphicon glyphicon-trash"></span></a>
                                <?php } ?>
                            </td>
                            <?php }
                        } ?>
                    </tr>
                <?php }?>
                <tr><td colspan="9" style="border-left:2px solid #ddd; border-right:2px solid #ddd;"><b>Glosa: <?= $comp['glosa'] ?></b></td></tr>
                <tr><td colspan="9"><br></td></tr>
            <?php }
            ?>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Factura</h4>
                </div>
                <div class="modal-body" id="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    function sumar_d (valor) {
        var total = 0;

        valor = parseInt(valor); // Convertir el valor a un entero (número).


        total = $("#debe").attr('value');

        // Aquí valido si hay un valor previo, si no hay datos, le pongo un cero "0".
        total = (total == null || total == undefined || total == "") ? 0 : total;

        /* Esta es la suma. */
        total = (parseInt(total) + parseInt(valor));

        // Colocar el resultado de la suma en el control "span".
        //document.getElementById('debe').attr('value',tatal);
        $("#debe").attr('value',total);
    }
    function sumar_h (valor) {
        var total = 0;

        valor = parseInt(valor); // Convertir el valor a un entero (número).


        total = $("#haber").attr('value');

        // Aquí valido si hay un valor previo, si no hay datos, le pongo un cero "0".
        total = (total == null || total == undefined || total == "") ? 0 : total;

        /* Esta es la suma. */
        total = (parseInt(total) + parseInt(valor));

        // Colocar el resultado de la suma en el control "span".
        //document.getElementById('debe').attr('value',tatal);
        $("#haber").attr('value',total);
    }
    function ver(a){
        $.ajax({
            url: '?/diario/ver_factura',
            type: 'POST',
            data: {a:a},
            success:function(data){
                //alert(cadena);
                document.getElementById("modal-body").innerHTML=data;
                //iniciar();
            }
        });
    }


    function eliminar(a,b){
        var opcion = confirm("Eliminar "+b);
        if (opcion == true) {
            $.ajax({
                url: 'asiento_eli.php',
                type: 'POST',
                data: {a:a},
                success:function(data){
                    document.getElementById("extra").innerHTML=data;
                }
            });
        }
    }
    function cierre(a,b){
        $.ajax({
            url: 'asiento_cierre.php',
            type: 'POST',
            data: {a:a,b:b},
            success:function(data){
                //alert(cadena);
                document.getElementById("extra").innerHTML=data;
                //iniciar();
                suma_d ();
                suma_h ();
            }
        });
    }
    function suma_d () {
        var total = 0;
        //numero de inputs
        Cont = $("#nro").attr('value');

        for(k=1;k<=Cont;k++){
            valor = $("#debe_"+k).attr('value'); // Convertir el valor a un entero (número).
            total = $("#debe").attr('value');
            // Aquí valido si hay un valor previo, si no hay datos, le pongo un cero "0".
            total = (total == null || total == undefined || total == "") ? 0 : total;
            /* Esta es la suma. */
            total = (parseInt(total) + parseInt(valor));

            // Colocar el resultado de la suma en el control "span".
            //document.getElementById('debe').attr('value',tatal);
            $("#debe").attr('value',total);
        }
    }
    function suma_h () {
        var total = 0;
        Cont = $("#nro").attr('value');
        for(h=1;h<=Cont;h++) {
            valor = $("#haber_"+h).attr('value'); // Convertir el valor a un entero (número).

            total = $("#haber").attr('value');

            // Aquí valido si hay un valor previo, si no hay datos, le pongo un cero "0".
            total = (total == null || total == undefined || total == "") ? 0 : total;

            /* Esta es la suma. */
            total = (parseInt(total) + parseInt(valor));

            // Colocar el resultado de la suma en el control "span".
            //document.getElementById('debe').attr('value',tatal);
            $("#haber").attr('value', total);
        }
    }
    function deshabilitar(cod){
        $.post('?/diario/cambia_estado',{cod:cod ,est:1},function(){
            location.reload();
        });
    }
    function habilitar(cod){
        $.post('?/diario/cambia_estado',{cod:cod,est:0},function(){
            location.reload();
        });
    }
    var parametro;
    function popup(aa)
    {
        parametro = window.open("form_factura.php"+"?ida="+aa,"","width=400,height=700");
        parametro.document.getElementById('1').value = "num" ;
    }
</script>
    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/jquery.base64.js"></script>
    <script src="<?= js; ?>/pdfmake.min.js"></script>
    <script src="<?= js; ?>/vfs_fonts.js"></script>
    <script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
    <script>
        $(function () {
            <?php if ($permiso_eliminar) { ?>
            $('[data-eliminar]').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                bootbox.confirm('Está seguro que desea eliminar la cuenta?', function (result) {
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
                            window.location = '?/almacenes/crear';
                            break;
                    }
                }
            });
            <?php } ?>
            var table = $('#table').DataFilter({
                filter: false,
                name: 'almacenes',
                reports: 'xls|doc|pdf|html'
            });
        });
    </script>
<?php require_once show_template('footer-configured'); ?>