<?php
    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
    if(!isset($params[0])):
        redirect('?/importaciones/gastos');
    endif;
    $id_importacion_gasto=trim($params[0]);
    $importacion_gasto=$db->query("SELECT importacion_id,nombre,codigo,fecha,total,total_gasto,tipo_pago,pago,estado,proveedor_id
                                    FROM inv_importacion_gasto
                                    WHERE id_importacion_gasto='{$id_importacion_gasto}'")->fetch_first();
    
    $importacion_gasto_detalle=$db->query("SELECT gasto,factura,costo_anadido,costo,gastos_id
                                        FROM inv_importacion_gasto_detalle
                                        WHERE importacion_gasto_id='{$id_importacion_gasto}'")->fetch();
    
    require_once show_template('header-advanced');
?>
<div class='row'>
    <div class='col-md-12'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    <span class='glyphicon glyphicon-list'></span>
                    <strong>Gastos de Importación</strong>
                </h3>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-6'>
                <div class='panel panel-default'>
                    <div class='panel-heading'>
                        <h3 class='panel-title'>
                            <span class='glyphicon glyphicon-list'></span>
                            <strong>Detalles de Gasto</strong>
                        </h3>
                    </div>
                    <div class='panel-body'>
                        <form class='form-horizontal' method='POST' action='?/importaciones/guardar_gastos' id='formularioF'>
                            <h2 class='lead'>Detalles de Gasto</h2>
                            <hr>
                            <input type='hidden' name='id_importacion' value='<?=trim($importacion_gasto['importacion_id'])?>'>
                            <input type='hidden' name='id_importacion_gasto' value='<?=trim($id_importacion_gasto)?>'>
                            <div class='form-group'>
                                <label for='nombreF' class='col-md-4 control-label'>Nombre: </label>
                                <div class='col-md-8'>
                                    <input type='text' name='nombre' value='<?=$importacion_gasto['nombre']?>' id='nombreF' class='form-control' data-validation='required letternumber' data-validation-allowing=' ,-.'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='codigoF' class='col-md-4 control-label'>Código: </label>
                                <div class='col-md-8'>
                                    <input type='text' name='codigo' value='<?=$importacion_gasto['codigo']?>' id='codigoF' class='form-control'  data-validation='required letternumber' data-validation-allowing=' -./'>
                                </div>
                            </div>
                            <?PHP                            
                                $Proveedores = $db->query('SELECT id_proveedor, proveedor, nit FROM inv_proveedores WHERE id_proveedor="'.$importacion_gasto['proveedor_id'].'"')->fetch_first();
                            ?>
                            <input type='hidden' name='id_importacion' value='<?=trim($params[0])?>'>
                            <div class='form-group'>
                                <label for='proveedorF' class='col-md-4 control-label'>Proveedor: </label>
                                <div class='col-md-8'>
                                    <p style="margin-top:7px"><?= $Proveedores['proveedor']?></p>
                                    
                                    <input type='hidden' name='id_proveedor' value='<?= $Proveedores['id_proveedor'] ?>'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='tipoPF' class='col-md-4 control-label'>Tipo de Pago: </label>
                                <div class='col-md-8'>
                                    <?php if($importacion_gasto['tipo_gasto']=='Contado'){ ?>
                                        <p style="margin-top:7px">Contado</p>
                                        <input type='hidden' name='tipoP' value='Contado'>
                                    <?php }else{ ?>
                                        <p style="margin-top:7px">A Credito</p>
                                        <input type='hidden' name='tipoP' value='A Credito'>                        
                                    <?php } ?>
                                </div>
                            </div>
                            
                            
                            
                            
                            
                            
                            
                            <!--<div class='form-group'>
                                <label for='tipoPF' class='col-md-4 control-label'>Tipo de Pago: </label>
                                <div class='col-md-8'>
                                    <select name='tipoP' id='tipoPF'class='form-control'>
                                        <option value='Contado' <?php if($importacion_gasto['tipo_pago']=='Contado'):echo 'selected';endif;?>>Contado</option>
                                        <option value='Cuotas' <?php if($importacion_gasto['tipo_pago']=='Cuotas'):echo 'selected';endif;?>>Cuotas</option>
                                        <option value='A Credito' <?php if($importacion_gasto['tipo_pago']=='A Credito'):echo 'selected';endif;?>>A Credito</option>
                                    </select>
                                </div>
                            </div>-->
                            <div class='form-group hidden' id='pagoCF'>
                                <label for='pagoF' class='col-md-4 control-label'>Pago: </label>
                                <div class='col-md-8'>
                                    <input type="hidden" id='tipoPF' name='tipoP' value='Contado'>
                                    <input type='text' name='pago' id='pagoF' value='<?=$importacion_gasto['pago']?>' class='form-control' data-validation='required number' data-validation-allowing='float'>
                                </div>
                            </div>
                            <div class='margin-none' style='overflow-x:auto'>
                                <table class='table table-bordered table-condensed table-restructured table-striped table-hover'>
                                    <thead>
                                        <tr class='active'>
                                            <th class='text-nowrap'>Gasto</th>
                                            <th class='text-nowrap'>Nro Documento</th>
                                            <th class='text-nowrap'>Costo Añadido (%)</th>
                                            <th class='text-nowrap' style='width:100px !important'>Costo <?= escape($moneda); ?></th>
                                            <th class='text-nowrap text-center'>
                                                <span class='glyphicon glyphicon-trash'></span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id='gastosRegistrosF'>
                                        <?php
                                            foreach($importacion_gasto_detalle as $Fila=>$Detalle):
                                        ?>
                                        <tr>
                                            <td>
                                                <input type='hidden' name='id_gasto[]' value='<?=$Detalle['gastos_id']?>'>
                                                <input type='text' name='gasto[]' value='<?=$Detalle['gasto']?>' data-validation='required' style='min-width:280px' <?php if($Detalle['gastos_id']):echo 'readonly';endif;?>>
                                            </td>
                                            <td>
                                                <input type='text' name='factura[]' value='<?=$Detalle['factura']?>' style='width:100px'>
                                            </td>
                                            <td>
                                                <input type='text' name='costo_anadido[]' onkeyup='calcularGasto()' value='<?=$Detalle['costo_anadido']?>' min='0' max='1' data-validation='required' style='width:100%;max-width:100px' <?php if($Detalle['gastos_id']):echo 'readonly';endif;?>>
                                            </td>
                                            <td>
                                                <input type='text' name='costo[]' onkeyup='calcularGasto()' value='<?=$Detalle['costo']?>' min='0' data-validation='required' style='width:100%;max-width:100px'>
                                            </td>
                                            <td>
                                                <button type='button' class='btn btn-danger btn-sm' onclick='borrarFila(this)'>
                                                    <span class='glyphicon glyphicon-trash'></span>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                            endforeach;
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class='active'>
                                            <th class='text-nowrap text-right'>Importe Total <?= escape($moneda); ?></th>
                                            <th class='text-nowrap text-right'>
                                                <input type='text' id='totalF' name='total' value='<?=$importacion_gasto['total']?>' style='width:100%;max-width:100px' readonly>
                                            </th>
                                            <th class='text-nowrap text-right'>Total Gasto <?= escape($moneda); ?></th>
                                            <th class='text-nowrap text-right'>
                                                <input type='text' id='totalGastoF' name='totalGasto' value='<?=$importacion_gasto['total_gasto']?>' style='width:100%;max-width:100px' readonly>
                                            </th>
                                            <th class='text-nowrap text-center'>
                                                <span class='glyphicon glyphicon-trash'></span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class='form-group'>
                                <div class='col-xs-12 text-right'>
                                    <button type='submit' class='btn btn-primary'>
                                        <span class='glyphicon glyphicon-floppy-disk'></span>
                                        <span>Guardar</span>
                                    </button>
                                    <button type='reset' class='btn btn-default'>
                                        <span class='glyphicon glyphicon-refresh'></span>
                                        <span>Restablecer</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class='col-md-6'>
                <div class='panel panel-default'>
                    <div class='panel-heading'>
                        <h3 class='panel-title'>
                            <span class='glyphicon glyphicon-list'></span>
                            <strong>Busqueda de Gastos</strong>
                        </h3>
                    </div>
                    <div class='panel-body'>
                        <h2 class='lead'>Busqueda de Gastos</h2>
                        <hr>
                        <div class='form-group text-right'>
                            <button type='submit' class='btn btn-primary' onclick='agregarGastoManual()'>
                                <span class='glyphicon glyphicon-plus'></span>
                                <span>Gasto Manual</span>
                            </button>
                        </div>
                        <div class='form-group has-feedback'>
                            <input type='text' id='busquedaF' class='form-control' placeholder='Buscar por gasto'>
                            <span class='glyphicon glyphicon-search form-control-feedback'></span>
                        </div>
                        <div class='margin-none' style='overflow-x:auto'>
                            <table class='table table-bordered table-condensed table-restructured table-striped table-hover'>
                                <thead>
                                    <tr class='active'>
                                        <th class='text-nowrap'>Nro</th>
                                        <th class='text-nowrap'>Gasto</th>
                                        <th class='text-nowrap'>Costo Añadido <?=$moneda?></th>
                                        <th class='text-nowrap'>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody id='gastosF'></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script>
    //BUSQUEDA DE GASTOS
    let Busqueda=document.getElementById('busquedaF');
    Busqueda.addEventListener('keyup',()=>{
        let Cadena=Busqueda.value.trim();
        if(Cadena!=''){
            $.ajax({
                data: {
                    'gasto':Cadena
                },
                type: 'POST',
                dataType: 'json',
                url: '?/importaciones/buscar_gasto',
            })
            .done(function(data,textStatus,jqXHR){
                let Gastos=document.getElementById('gastosF');
                Gastos.innerHTML='';
                data.forEach((Dato,index)=>{
                    Gastos.innerHTML+=`<tr>
                                    <td>${index+1}</td>
                                    <td>${Dato['gasto']}</td>
                                    <td>${Dato['costo_anadido']}</td>
                                    <td>
                                        <button class='btn btn-primary btn-sm' onclick='agregarGasto(${Dato['id_gastos']},this)'>
                                            <span class='glyphicon glyphicon-plus'></span>
                                        </button>
                                    </td>
                                </tr>`;
                });
            })
            .fail(function(jqXHR,textStatus,errorThrown) {
                console.log(textStatus)
            });
        }
        else{
            let Gastos=document.getElementById('gastosF');
            Gastos.innerHTML='';
        }
    });
    //AGREGADO Y ELIMINADO DE GASTOS
    function agregarGasto(id_gasto,elemento){
        let Hijos=elemento.parentNode.parentNode.children;
        let gasto=Hijos[1].innerText,
            costo_anadido=Hijos[2].innerText,
            gastosRegistro=document.getElementById('gastosRegistrosF'),
            filas=gastosRegistro.children.length;
        let Sw=false;
        for(let i=0;i<filas;++i){
            if(gastosRegistro.children[i].children[0].children[1].value==gasto){
                Sw=true;
                break;
            }
        }
        if(!Sw){
            gastosRegistro.insertAdjacentHTML('beforeend',`<tr>
                                    <td>
                                        <input type='hidden' name='id_gasto[]' value='${id_gasto}'>
                                        <input type='text' name='gasto[]' value='${gasto}' style='min-width:280px' readonly>
                                    </td>
                                    <td>
                                        <input type='text' name='factura[]' value='' style='width:100px'>
                                    </td>
                                    <td>
                                        <input type='text' name='costo_anadido[]' value='${costo_anadido}' style='width:100%;max-width:100px' readonly>
                                    </td>
                                    <td>
                                        <input type='text' name='costo[]' onkeyup='calcularGasto()' value='0' min='0'  data-validation='required' style='width:100%;max-width:100px'>
                                    </td>
                                    <td>
                                        <button type='button' class='btn btn-danger btn-sm' onclick='borrarFila(this)'>
                                            <span class='glyphicon glyphicon-trash'></span>
                                        </button>
                                    </td>
                                </tr>`);
        }
    }
    function borrarFila(e){
        document.getElementById('gastosRegistrosF').deleteRow(e.parentNode.parentNode.rowIndex-1);
        calcularGasto()
    }
    //Calculo Gasto Total y Gasto Añadido
    function calcularGasto(){
        let gastosRegistro=document.getElementById('gastosRegistrosF'),
            filas=gastosRegistro.children.length,
            Total=0,
            TotalCosto=0;
        for(let i=0;i<filas;++i){
            let fila=gastosRegistro.children[i],
                costo_anadido=(fila.children[2].children[0].value!='')?fila.children[2].children[0].value:0,
                importe=(fila.children[3].children[0].value!='')?fila.children[3].children[0].value:0;
            Total=parseFloat(Total)+parseFloat(importe);
            TotalCosto=parseFloat(TotalCosto)+parseFloat(importe*(costo_anadido/100));
        }
        document.getElementById('totalF').value=Total.toFixed(2);
        document.getElementById('totalGastoF').value=TotalCosto.toFixed(2);
    }
    //VALIDACION
    $(function(){
        $.validate({
            form: '#formularioF',
            modules: 'basic',
        });
    });
    //AGREGAR GASTO MANUAL
    function agregarGastoManual(){
        let gastosRegistro=document.getElementById('gastosRegistrosF');
        gastosRegistro.insertAdjacentHTML('beforeend',`<tr>
                                    <td>
                                        <input type='hidden' name='id_gasto[]' value='0'>
                                        <input type='text' name='gasto[]' value='' data-validation='required' style='min-width:280px'>
                                    </td>
                                    <td>
                                        <input type='text' name='factura[]' value='' style='width:100px'>
                                    </td>
                                    <td>
                                        <input type='text' name='costo_anadido[]' onkeyup='calcularGasto()' value='100.00' min='0' max='1' data-validation='required' style='width:100%;max-width:100px'>
                                    </td>
                                    <td>
                                        <input type='text' name='costo[]' onkeyup='calcularGasto()' value='0' min='0' data-validation='required' style='width:100%;max-width:100px'>
                                    </td>
                                    <td>
                                        <button type='button' class='btn btn-danger btn-sm' onclick='borrarFila(this)'>
                                            <span class='glyphicon glyphicon-trash'></span>
                                        </button>
                                    </td>
                                </tr>`);
    }
    //VISUALIZAR PAGO
    let SelectPago=document.getElementById('tipoPF');
    SelectPago.addEventListener('change',()=>{
        let pagoC=document.getElementById('pagoCF');
        if(SelectPago.value!='Contado')
            pagoC.classList.remove('hidden');
        else{
            document.getElementById('pagoF').value='0';
            pagoC.classList.add('hidden');
        }
    });
</script>
<?php
    require_once show_template('footer-advanced');