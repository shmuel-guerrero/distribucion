<?php
    //ARCHIVOS UTILIZADOS
    //?/importaciones/buscar_gasto
    //?/importaciones/guardar_gastos
    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
    if(!isset($params[0])):
        redirect('?/importaciones/gastos');
    endif;
    require_once show_template('header-advanced');

    $Proveedores = $db->query(' SELECT id_proveedor, proveedor, nit 
                                FROM inv_proveedores 
                                WHERE tipo="servicio" 
                                ORDER BY proveedor')->fetch();
?>
<div class='panel panel-default'>
    <div class='panel-heading'>
        <h3 class='panel-title'>
            <span class='glyphicon glyphicon-list'></span>
            <strong>Gastos de Importaci&oacute;n</strong>
        </h3>
    </div>
</div>
<div class='panel-body'>
    <!--<div class='col-md-12'>-->
        
        <div class="row">
    		<div class="col-sm-8 hidden-xs">
    			<div class="text-label">Para ir al listado hacer clien en el boton: </div>
    		</div>
    		<div class="col-xs-12 col-sm-4 text-right">
    			<a href="?/importaciones/gastos" class="btn btn-primary">
    				<span class="glyphicon glyphicon-menu-left"></span>
    				<span>Listar</span>
    			</a>
    		</div>
    	</div>
    	<br>
    	
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
                            <input type='hidden' name='id_importacion' value='<?=trim($params[0])?>'>
                            <div class='form-group'>
                                <label for='proveedorF' class='col-md-4 control-label'>Proveedor: </label>
                                <div class='col-md-8'>
                                    <select name='id_proveedor' id='proveedorF' class='form-control' data-validation='required number' onchange="get_proveedor();">
                                        <option value=''>Seleccionar</option>
                                        <?php
                                        foreach ($Proveedores as $Fila => $Proveedor) :
                                        ?>
                                            <option value='<?= $Proveedor['id_proveedor'] ?>'><?= $Proveedor['proveedor'] . ' (' . $Proveedor['nit'] . ')' ?></option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='nombreF' class='col-md-4 control-label'>Titulo: </label>
                                <div class='col-md-8'>
                                    <input type='text' name='nombre' id='nombreF' class='form-control' data-validation='required letternumber length' data-validation-allowing=' ,-.' data-validation-length='max99'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='codigoF' class='col-md-4 control-label'>Nro documento: </label>
                                <div class='col-md-8'>
                                    <input type='text' name='codigo' id='codigoF' autocomplete="off" class='form-control'  data-validation='required letternumber length' data-validation-allowing=' -/' data-validation-length='max99'>
                                </div>
                            </div>
                            <!--div class='form-group'>
                                <label for='tipoPF' class='col-md-4 control-label'>Tipo de Pago: </label>
                                <div class='col-md-8'>
                                    <select name='tipoP' id='tipoPF'class='form-control'>
                                        <option value='Contado'>Contado</option>
                                        <option value='A Credito'>A Credito</option>
                                    </select>
                                </div>
                            </div-->
                            
                            <div class='form-group hidden' id='pagoCF'>
                                <label for='pagoF' class='col-md-4 control-label'>Pago: </label>
                                <div class='col-md-8'>
                                    <input type='text' name='pago' id='pagoF' value='0' class='form-control' data-validation='required number' data-validation-allowing='float'>
                                </div>
                            </div>
                            <div class='margin-none' style='overflow-x:auto'>
                                <table class='table table-bordered table-condensed table-restructured table-striped table-hover'>
                                    <thead>
                                        <tr class='active'>
                                            <th class='text-nowrap'>Gasto</th>
                                            <th class=''>Nro Documento</th>
                                            <th class=''>Costo A&ntilde;adido (%)</th>
                                            <th class='text-nowrap' style='!important'>Costo <?= escape($moneda); ?></th>
                                            <th class='text-nowrap text-center'>
                                                <span class='glyphicon glyphicon-trash'></span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id='gastosRegistrosF'>

                                    </tbody>
                                    <tfoot>
                                        <tr class='active'>
                                            <th class='text-nowrap text-right'>Importe Total <?= escape($moneda); ?></th>
                                            <th class='text-nowrap text-right'>
                                                <input type='text' id='totalF' name='total' value='0' style='width:100%;max-width:100px' readonly>
                                            </th>
                                            <th class='text-nowrap text-right'>Total Gasto <?= escape($moneda); ?></th>
                                            <th class='text-nowrap text-right'>
                                                <input type='text' id='totalGastoF' name='totalGasto' value='0' style='width:100%;max-width:100px' readonly>
                                            </th>
                                            <th class='text-nowrap text-center'>
                                                <span class='glyphicon glyphicon-trash'></span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <br>
                        
                            <!---------------------------------------------------->
                            
                            <div class="form-group">
                                <label  class="col-md-4 control-label">Tipo de pago:</label>
                                <div class="col-md-8">
                                    <select name="forma_pago" id="forma_pago" class="col-md-4 form-control" data-validation="required number" onchange="set_plan_pagos()">
                                        <option value="1">Contado</option>
                                        <option value="2">Plan de Pagos</option>
                                        <option value="3">Pago Anticipado</option>
                                    </select>
                                </div>    
                            </div>    
                        
                            <div id="al_contado" style="display:block;">
                                <div class="form-group">
                					<label for="categoria_id" class="col-md-4 control-label">Tipo de Transaccion:</label>
                					<div class="col-md-8">
                						<select name="tipo_pago" id="tipo_pago" class="form-control" data-validation="required">
                							<option value=""> </option>
                							<option value="cheque">Cheque</option>
                							<option value="transferencia">Transferencia</option>
                						</select>
                					</div>
                				</div>
                				<div class="form-group">
                					<label for="categoria_id" class="col-md-4 control-label">Cuenta Bancaria:</label>
                					<div class="col-md-8">
                						<select name="banco_id" id="banco_id" class="form-control" data-validation="required" onchange="get_banco();">
                							<option value=""></option>
                							<?php 
                							// Obtiene el modelo categorias
                                            $bancos = $db->from('inv_bancos')
                                                         ->order_by('banco')
                                                         ->fetch();
                                            
                                            foreach ($bancos as $elemento) { ?>
                								<option value="<?= $elemento['id_banco']; ?>"><?= $elemento['banco']." - ".$elemento['cuenta'] ?></option>
                							<?php } ?>
                						</select>
                
                						<?php 
                						    foreach ($bancos as $elemento) { 
                    							$movimientos = $db->query(" SELECT ifnull(SUM(monto_deposito),0) as monto_deposito, ifnull(SUM(monto_deposito2),0) as monto_deposito2
                                                        					FROM(
                                                        						SELECT ifnull(SUM(d.monto_deposito),0) as monto_deposito, 0 as monto_deposito2 
                                                        						FROM inv_deposito d
                                                        						WHERE banco='".$elemento['id_banco']."'
                                                        						
                                                        						UNION
                                                
                                                        						SELECT 0 as monto_deposito, ifnull(SUM(pd.monto),0) as monto_deposito2
                                                        						FROM inv_pagos p
                                                        						LEFT JOIN inv_pagos_detalles pd ON id_pago=pago_id
                                                        						WHERE   p.tipo='Ingreso' 
                                                        						        AND banco_id='".$elemento['id_banco']."'
                                                        					)x
                                                        				")->fetch_first();
                                            ?>
                							<span id="banco_<?= $elemento['id_banco']; ?>" style="display:none;"><?= ($movimientos['monto_deposito']-$movimientos['monto_deposito2']); ?></span>
                						<?php } ?>
                					</div>
                				</div>
                				<div class="form-group">
                					<label for="nombre" class="col-md-4 control-label">Numero de documento:</label>
                					<div class="col-md-8">
                						<input type="text" value="" name="nro_doc" id="nombre" class="form-control" data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º"()¨ ' data-validation-length="max100">
                					</div>
                				</div>
                				<div class="form-group">
                					<label for="nombre" class="col-md-4 control-label">Glosa:</label>
                					<div class="col-md-8">
                						<input type="text" value="" name="observacion" id="observacion" class="form-control" data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º"()¨ ' data-validation-length="max100">
                					</div>
                				</div>
                            </div>
        
                            <div id="plan_de_pagos" style="display:none;">
                                <div class="form-group">
                                    <label for="almacen" class="col-md-4 control-label">Nro Cuotas:</label>
                                    <div class="col-md-8">
                                        <input type="text" value="1" id="nro_cuentas" name="nro_cuentas" class="form-control text-right" autocomplete="off" data-cuentas="" data-validation="required number" data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo" onkeyup="set_cuotas()">
                                    </div>
                                </div>
                                
                                <table id="cuentasporpagar" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
                                    <thead>
                                        <tr class="active">
                                            <th class="text-nowrap text-center col-xs-4">Detalle</th>
                                            <th class="text-nowrap text-center col-xs-4">Fecha</th>
                                            <th class="text-nowrap text-center col-xs-4">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for($i=1;$i<=36;$i++){ ?>
                                            <tr class="active cuotaclass">
                                                <td class="text-nowrap" valign="center">
                                                    <div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Cuota <?= $i ?>:</div>
                                                </td>
                                                <td>
                                                    <div data-cuota="<?= $i ?>" class="cuota_div">
                                                        <div class="col-sm-12">
                                                            <input type="date" id="inicial_fecha_<?= $i ?>" name="fecha[]" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" class="form-control" autocomplete="off" data-validation-format="<?= $formato_textual; ?>" onchange="javascript:change_date(<?= $i ?>);" <?php if($i==1){ ?> data-validation="required" <?php } ?> onblur="javascript:change_date(<?= $i ?>);" 
                                                            <?php // if($i==1){ disabled="disabled"?>  <?php // } ?>
                                                            >
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><div data-cuota="<?= $i ?>" class="cuota_div"><input type="text" value="0" name="cuota[]" class="form-control text-right monto_cuota" maxlength="7" autocomplete="off" data-montocuota="" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onchange="javascript:calcular_cuota('<?= $i ?>');"></div></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="active">
                                            <th class="text-nowrap text-center" colspan="2">Importe total <?= escape($moneda); ?></th>
                                            <th class="text-nowrap text-right" data-totalcuota="">0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <br>
                            </div>

                            <div id="anticipado" style="display:none;">
                                <div class="form-group">
                					<label for="categoria_id" class="col-md-4 control-label">Pago anticipado:</label>
                					<div class="col-md-8">
                						<?php 
                						foreach ($Proveedores as $Fila => $Proveedor){
                                        ?>    
                    						<select name="pago_id_<?= $Proveedor['id_proveedor'] ?>" id="pago_id_<?= $Proveedor['id_proveedor'] ?>" class="form-control pago_id" data-validation="required" 
                    						        onchange="get_pago(<?= $Proveedor['id_proveedor'] ?>);" style="display:none;">
                    							<option value=""></option>
                    							<?php    
                    							// Obtiene el modelo categorias
                                                $bancos = $db->query('  SELECT id_pago, pd.observacion, pd.monto, pd.tipo_pago, pd.nro_pago, b.banco, b.cuenta
                                                                        FROM inv_pagos
                                                                        INNER JOIN inv_pagos_detalles pd ON id_pago=pago_id
                                                                        INNER JOIN inv_bancos b ON b.id_banco=banco_id
                                                                        INNER JOIN inv_ingresos i ON i.id_ingreso=movimiento_id AND i.tipo="Otros"
                                                                        WHERE i.proveedor_id="'.$Proveedor['id_proveedor'].'"
                                                                        ')
                                                             ->fetch();
                                                
                                                foreach ($bancos as $elemento) { ?>
                    								<option value="<?= $elemento['id_pago']; ?>"><?= $elemento['observacion'] ?></option>
                    							<?php } ?>
                    						</select>
                    						
                    						<?php foreach ($bancos as $elemento) { ?>
                								<input type="hidden" id="obs_<?= $elemento['id_pago']; ?>" value="<?= $elemento['observacion'] ?>">
                								<input type="hidden" id="nro_<?= $elemento['id_pago']; ?>" value="<?= $elemento['nro_pago'] ?>">
                								<input type="hidden" id="tipo_<?= $elemento['id_pago']; ?>" value="<?= $elemento['tipo_pago'] ?>">
                								<input type="hidden" id="cuenta_<?= $elemento['id_pago']; ?>" value="<?= $elemento['banco']." - ".$elemento['cuenta'] ?>">
                								<input type="hidden" id="monto_<?= $elemento['id_pago']; ?>" value="<?= $elemento['monto'] ?>">
                							<?php } ?>

                    					<?php 
                    					} 
                    					?>	
                					</div>
                				</div>
                				
                                <div class="form-group">
                					<label for="categoria_id" class="col-md-4 control-label">Tipo de Transaccion:</label>
                					<div class="col-md-8">
                						<p class="tipoo" style="margin-top:9px;"></p>
                					</div>
                				</div>
                				<div class="form-group">
                					<label for="categoria_id" class="col-md-4 control-label">Cuenta Bancaria:</label>
                					<div class="col-md-8">
                						<p class="nrocta" style="margin-top:9px;"></p>
                					</div>
                				</div>
                				<div class="form-group">
                					<label for="nombre" class="col-md-4 control-label">Numero de documento:</label>
                					<div class="col-md-8">
                						<p class="nrodoc" style="margin-top:9px;"></p>
                					</div>
                				</div>
                				<div class="form-group">
                					<label for="categoria_id" class="col-md-4 control-label">Monto:</label>
                					<div class="col-md-8">
                						<p class="montooo" style="margin-top:9px;"></p>
                					</div>
                				</div>
                				<div class="form-group">
                					<label for="nombre" class="col-md-4 control-label">Glosa:</label>
                					<div class="col-md-8">
                						<p class="obsss" style="margin-top:9px;" class=" control-label"></p>
                					</div>
                				</div>
                            </div>
                            
                            <!---------------------------------------------------->

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
                                        <th class='text-nowrap'>Costo A&ntilde;adido <?=$moneda?></th>
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
    <!--</div>-->
</div>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script>

    $('#proveedorF').selectize({
            persist: false,
            createOnBlur: true,
            create: false,
            onInitialize: function() {
                $('#proveedorF').css({
                    display: 'block',
                    left: '-10000px',
                    opacity: '0',
                    position: 'absolute',
                    top: '-10000px'
                });
            },
            onChange: function() {
                $('#proveedorF').trigger('blur');
            },
            onBlur: function() {
                $('#proveedorF').trigger('blur');
            }
    });

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
                                        <input type='hidden' name='id_gasto[]' value='${id_gasto}' data-validation='required letternumber length' data-validation-allowing=' -/'  data-validation-length='max30'>
                                        <input type='text' name='gasto[]' value='${gasto}' onblur="calcularGasto()" style='min-width:80px' readonly>
                                    </td>
                                    <td>
                                        <input type='text' name='factura[]' autocomplete="off" value='' style='width:50px' data-validation='required letternumber length' data-validation-allowing=' -/'  data-validation-length='max30'>
                                    </td>
                                    <td>
                                        <input type='text' name='costo_anadido[]' value='${costo_anadido}' autocomplete='off' data-validation='required' style='width:100%;max-width:100px' >
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

    //Calculo Gasto Total y Gasto A単adido
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
        
        set_plan_pagos();
        set_cuotas();
        get_proveedor();
    });
    //AGREGAR GASTO MANUAL
    function agregarGastoManual(){
        let gastosRegistro=document.getElementById('gastosRegistrosF');
        gastosRegistro.insertAdjacentHTML('beforeend',`<tr>
                                    <td>
                                        <input type='hidden' name='id_gasto[]' value='0'>
                                        <input type='text' name='gasto[]' value='' style='min-width:80px' data-validation='required letternumber length' data-validation-allowing=' -/'  data-validation-length='max30'>
                                    </td>
                                    <td>
                                        <input type='text' name='factura[]' value='' style='width:100px' data-validation='required letternumber length' data-validation-allowing=' -/'  data-validation-length='max30'>
                                    </td>
                                    <td>
                                        <input type='text' name='costo_anadido[]' data-validation='number' data-validation-allowing='range[0.00;100.00],float' onkeyup='calcularGasto()' value='100.00' min='0' max='1' data-validation='required' style='width:100%;max-width:100px'>
                                    </td>
                                    <td>
                                        <input type='text' name='costo[]' onkeyup='calcularGasto()' data-validation='number' data-validation-allowing='range[0.01;10000000.00],float' value='0' min='0' data-validation='required' style='width:100%;max-width:100px'>
                                    </td>
                                    <td>
                                        <button type='button' class='btn btn-danger btn-sm' onclick='borrarFila(this)'>
                                            <span class='glyphicon glyphicon-trash'></span>
                                        </button>
                                    </td>
                                </tr>`);
    }
    //VISUALIZAR PAGO
    //let SelectPago=document.getElementById('tipoPF');
    //SelectPago.addEventListener('change',()=>{
      //  let pagoC=document.getElementById('pagoCF');
        //if(SelectPago.value!='Contado')
          //  pagoC.classList.remove('hidden');
        //else{
          //  document.getElementById('pagoF').value='0';
        //    pagoC.classList.add('hidden');
      //  }
    //});
    
function get_banco(){
    monto=parseFloat($('[data-total]:first').val());
    
    i=$('#banco_id').val();
    ix=parseInt($("#banco_"+i).html());

    if( (ix-monto)<0 ){
        alert("Cuenta Bancaria sin fondos");
    }
}
function get_pago(id_proveedor){
    pago=$('#pago_id_'+id_proveedor).val();

    $('.obsss').html( $('#obs_'+pago).val() );
    $('.nrocta').html( $('#cuenta_'+pago).val() );
    $('.nrodoc').html( $('#nro_'+pago).val() );
    $('.montooo').html( $('#monto_'+pago).val() );
    $('.tipoo').html( $('#tipo_'+pago).val() );
}
function get_proveedor(){
    id_proveedor=$("#proveedorF").val()
    $('.pago_id').css({'display':'none'});
    $('#pago_id_'+id_proveedor).css({'display':'block'});
}
/****/
function set_cuotas() {
// console.log($('#nro_cuentas').val());
    
    var cantidad = $('#nro_cuentas').val();
    var $compras = $('#cuentasporpagar tbody');
    
    $("#nro_plan_pagos").val(cantidad);

    if(cantidad>36){
        cantidad=36;
        $('#nro_cuentas').val("36")
    }
    for(i=1;i<=cantidad;i++){
        $('[data-cuota=' + i + ']').css({'height':'auto', 'overflow':'visible'});               
        $('[data-cuota2=' + i + ']').css({'margin-top':'10px;'});               
        $('[data-cuota=' + i + ']').parent('td').css({'height':'auto', 'border-width':'1px','padding':'5px'});              
    }
    for(i=parseInt(cantidad)+1;i<=36;i++){
        $('[data-cuota=' + i + ']').css({'height':'0px', 'overflow':'hidden'});             
        $('[data-cuota2=' + i + ']').css({'margin-top':'0px;'});                
        $('[data-cuota=' + i + ']').parent('td').css({'height':'0px', 'border-width':'0px','padding':'0px'});
    }
    set_cuotas_val();
    calcular_cuota(1000);
}
function set_cuotas_val() {
    nro=$('#nro_cuentas').val();
    
    valorG=parseFloat($('#totalF').val());
    valor=valorG/nro;
    for(i=1;i<=nro;i++){
        if(i==nro){
            final=valorG-(valor.toFixed(1)*(i-1));
            $('[data-cuota=' + i + ']').children('.monto_cuota').val(final.toFixed(1)+"0");
        }else{
            $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
        }
    }       
}
function set_plan_pagos(){
    if($("#forma_pago").val()==1){
        $('#anticipado').css({'display':'none'});
        $('#plan_de_pagos').css({'display':'none'});
        $('#al_contado').css({'display':'block'});
    }
    if($("#forma_pago").val()==2){
        $('#anticipado').css({'display':'none'});
        $('#plan_de_pagos').css({'display':'block'});
        $('#al_contado').css({'display':'none'});
    }
    if($("#forma_pago").val()==3){
        $('#anticipado').css({'display':'block'});
        $('#plan_de_pagos').css({'display':'none'});
        $('#al_contado').css({'display':'none'});
    }
}
function calcular_cuota(x) {
    var cantidad = $('#nro_cuentas').val();
    var total = 0;
    
    for(i=1;i<=x && i<=cantidad;i++){
        importe=$('[data-cuota=' + i + ']').children('.monto_cuota').val();
        importe = parseFloat(importe);
        total = total + importe;
    }
    
    valorTotal=parseFloat($('#totalF').val());
    if(nro>x){
        valor=(valorTotal-total)/(nro-x);
    }
    else{
        valor=0;
    }

    for(i=(parseInt(x)+1);i<=cantidad;i++){
        if(valor>=0){
            if(i==cantidad){
                valor=valorTotal-total;
                $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
            }
            else{
                $('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
            }
            total = total + (valor.toFixed(1)*1);
        }
        else{
            $('[data-cuota=' + i + ']').children('.monto_cuota').val("0.00");
        }
    }   
    
    $('[data-totalcuota]').text( total.toFixed(2) );
    valor=parseFloat($('[data-total]:first').val());
    
    //alert(valor+" - - - "+total );

    if(valor==total.toFixed(2) ){
        $('[data-total-pagos]:first').val(1);
        $('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg",""); 
    }
    else{
        $('[data-total-pagos]:first').val(0);   
        $('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg","La suma de las cuotas es diferente al costo total « "+total.toFixed(2)+" / "+valor+" »");   
    }
}
function change_date(x){
    if($('#inicial_fecha_'+x).val()!=""){
        if(x<36){
            $('#inicial_fecha_'+(x+1)).removeAttr("disabled");
        }
    }   
    else{
        for(i=x;i<=35;i++){
            $('#inicial_fecha_'+(i+1)).val("");
            $('#inicial_fecha_'+(i+1)).attr("disabled","disabled");
        }
    }
}
</script>
<?php
    require_once show_template('footer-advanced');