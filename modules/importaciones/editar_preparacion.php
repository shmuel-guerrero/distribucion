<?php
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
$Almacenes = $db->query('SELECT id_almacen,almacen FROM inv_almacenes')->fetch();
//$IdAlmacen=1;
$Proveedores = $db->query('SELECT id_proveedor,proveedor,nit FROM inv_proveedores')->fetch();

$IdImportacion=isset($params[0])?$params[0]:false;
if(!$IdImportacion):
	redirect('?/importaciones/gastos');
endif;
$Importacion=$db->query("SELECT almacen_id,id_proveedor,descripcion,total,DATE_FORMAT(fecha_inicio,'%Y-%m-%d\T%H:%i')AS fecha_inicio
                        FROM inv_importacion
                        WHERE id_importacion='{$IdImportacion}'
                        LIMIT 1")->fetch_first();

$Detalles=$db->query("  SELECT id.id_tmp_ingreso_detalle,id.precio_ingreso,id.cantidad,id.fechav,id.lote,p.id_producto,p.codigo,p.nombre_factura as nombre,id.unidad_id
                        FROM tmp_ingreso_detalle AS id
                        LEFT JOIN inv_productos AS p ON id.producto_id=p.id_producto
                        WHERE id.importacion_id='{$IdImportacion}'
                        ORDER BY id_tmp_ingreso_detalle
                    ")->fetch();
//LEFT JOIN inv_unidades AS u ON u.id_unidad=id.unidad_id

require_once show_template('header-empty');
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Preventas</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="col-sm-8 hidden-xs">
				    <a class="btn btn-info btn-md" href="?/importaciones/gastos"> <span class="glyphicon glyphicon-list"></span> Listar </a>
				</div>
				<div class="col-sm-4 hidden-xs  text-right">
					<div class="form-check form-check-inline">
						<label class="form-check-label" for="inlineCheckbox1">Busqueda de Productos</label>
						<input class="form-check-input" type="checkbox" id="inlineCheckbox1" onchange='sidenav()' checked>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row" id='ContenedorF'>
    <div class='col-md-6'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    <span class='glyphicon glyphicon-list'></span>
                    <strong>Datos de la Preparación</strong>
                </h3>
            </div>
            <div class='panel-body'>
                <h2 class='lead'>Detalles Preparación</h2>
                <hr>
                <input type='hidden' id='AuxAlmacen'>
                <form class='form-horizontal' id='formulario'>
                    <input type='hidden' name='id_importacion' value='<?=$IdImportacion?>'>
                    <div class='form-group'>
                        <label for='almacenF' class='col-md-4 control-label'>Almacen: </label>
                        <div class='col-md-8'>
                            <select name='id_almacen' id='almacenF' class='form-control' data-validation='required number' onchange='AuxAlmacen.value=this.value'>
                                <option value=''>Seleccionar</option>
                                <?php
                                foreach ($Almacenes as $Fila => $Almacen) :
                                ?>
                                    <option value='<?= $Almacen['id_almacen'] ?>' <?php if($Importacion['almacen_id']==$Almacen['id_almacen']):echo 'selected';endif;?>><?= $Almacen['almacen'] ?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='proveedorF' class='col-md-4 control-label'>Proveedor: </label>
                        <div class='col-md-8'>
                            <select name='id_proveedor' id='proveedorF' class='form-control' data-validation='required number'>
                                <option value=''>Seleccionar</option>
                                <?php
                                foreach ($Proveedores as $Fila => $Proveedor) :
                                ?>
                                    <option value='<?= $Proveedor['id_proveedor'] ?>' <?php if($Importacion['id_proveedor']==$Proveedor['id_proveedor']):echo 'selected';endif;?>><?= $Proveedor['proveedor'] . ' (' . $Proveedor['nit'] . ')' ?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='fechai' class='col-sm-4 control-label'>Fecha: </label>
                        <div class='col-sm-8'>
                            <input type="datetime-local" class="form-control" id="fechai" name="fechai" value="<?=$Importacion['fecha_inicio']?>">
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='descripcionF' class='col-sm-4 control-label'>Descripción:</label>
                        <div class='col-sm-8'>
                            <textarea name='descripcion' id='descripcionF' class='form-control' autocomplete='off' data-validation='letternumber' data-validation-allowing='+-/.,:;#º()\n ' data-validation-optional='true'><?=$Importacion['descripcion']?></textarea>
                        </div>
                    </div>
                    <div class='margin-none' style='overflow-x:auto'>
                        <table id='preparacion' class='table table-bordered table-condensed table-restructured table-striped table-hover'>
                            <thead>
                                <tr class='active'>
                                    <th class='text-nowrap'>Código</th>
                                    <th class='text-nowrap'>Nombre</th>
                                    <th class='text-nowrap'>Vencimiento</th>
                                    <th class='text-nowrap'>Lote</th>
                                    <th class='text-nowrap'>Cantidad</th>
                                    <th class='text-nowrap'>Costo</th>
                                    <th class='text-nowrap'>Importe</th>
                                    <th class='text-nowrap text-center'><span class='glyphicon glyphicon-trash'></span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach($Detalles as $Fila=>$Detalle):
                                ?>
                                    <tr data-producto="<?= $Fila."_".$Fila ?>">
                                        <td>
                                            <input style="width:60px" type="hidden" id="importe<?=$Fila?>" name="importe[]" value="<?=$Detalle['cantidad']*$Detalle['precio_ingreso']?>">
                                            <input style="width:60px" type="hidden" name="producto_id[]" value="<?=$Detalle['id_producto']?>">
                                            <input style="width:60px" type="hidden" name="unidad_id[]" value="<?=$Detalle['unidad_id']?>">
                                            <?=$Detalle['codigo']?>
                                        </td>
                                        <td><?=$Detalle['nombre']?></td>
                                        <td><input style="width:120px" type="date" id="fechav<?=$Fila?>" onchange="actualizar('fechav',<?=$Fila?>,this.value)" name="fechav[]" value="<?=$Detalle['fechav']?>"></td>
                                        <td><input style="width:120px" type="text" name="lote[]" value="<?=$Detalle['lote']?>"></td>
                                        <td><input style="width:60px" type="text" id="cantidad<?=$Fila?>" data-cantidad="" onkeyup="calcular_importe('<?= $Fila."_".$Fila ?>')" name="cantidad[]" value="<?=$Detalle['cantidad']?>"></td>
                                        <td><input style="width:60px" type="text" id="costo<?=$Fila?>" data-costo="" onkeyup="calcular_importe('<?= $Fila."_".$Fila ?>')" name="costo[]" value="<?=$Detalle['precio_ingreso']?>"></td>
                                        <td data-importe="">
                                            <?=$Detalle['cantidad']*$Detalle['precio_ingreso']?>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-sm" onclick="eliminar(this,<?=$Fila?>)">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php
                                    endforeach;
                                ?>
                            </tbody>
                            <tfoot>
                                <tr class='active'>
                                    <th class='text-nowrap text-right' colspan='5'>Importe total <?= escape($moneda); ?></th>
                                    <th class='text-nowrap text-right' colspan='2'><input type='text' id='totalF' name='total' value='<?=$Importacion['total']?>' style='width:80px' readonly></th>
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
                            <a href="?/importaciones/gastos" class='btn btn-warning'>
                                <span class='glyphicon glyphicon-refresh'></span>
                                <span>Cancelar</span>
                            </a>
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
                    <strong>Busqueda de Productos</strong>
                </h3>
            </div>
            <div class='panel-body'>
                <h2 class='lead'>Búsqueda de productos</h2>
                <hr>
                <div class='form-group has-feedback'>
                    <input type='text' id='busquedaF' onkeyup='buscarProductos(this.value)' class='form-control' placeholder='Buscar por código, producto o categoría'>
                    <span class='glyphicon glyphicon-search form-control-feedback'></span>
                </div>
                <div class='margin-none' style='overflow-x:auto'>
                    
                    
                        <div class='table-responsive margin-none'>
                            <table id='productos' class='table table-bordered table-condensed table-striped table-hover margin-none'>
                                <thead>
                                    <tr class='active'>
                                        <th class='hidden text-nowrap text-center width-collapse'>#</th>
                                        <th class='text-nowrap text-center width-collapse'>CÓDIGO</th>
                                        <th class='text-nowrap text-center'>PRODUCTO</th>
                                        <th class='text-center width-collapse' width='8%'>CANTIDAD</th>
                                        <th class='hidden text-nowrap text-center '>UNIDAD</th>
                                        <th class='text-nowrap text-center '>COSTO</th>
                                        <th class='text-nowrap text-center width-collapse'>IMPORTE</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script src='<?= js; ?>/bootstrap-notify.min.js'></script>
<script>
    //Validacion
    $.validate({
        modules: 'basic'
    });
    //Busqueda de Productos
    // const busqueda=document.getElementById('busquedaF');

    let Productos=[];
    // // window.addEventListener('load',()=>{
    //     $.ajax({
    //         data: {
    //                 'id_importacion':<?=$IdImportacion?>,
    //                 'id_almacen':<?=$Importacion['almacen_id']?>,
    //             },
    //         type: 'POST',
    //         dataType: 'json',
    //         url: '?/importaciones/buscar_productos_existentes',
    //     })
    //     .done(function(data,textStatus,jqXHR){
    //         data.forEach(Dato=>{
    //             agregar(Dato,false);
    //         })
    //     })
    //     .fail(function(jqXHR,textStatus,errorThrown) {
    //         console.log(textStatus)
    //     });
    // });

    // busqueda.addEventListener('keyup',(e)=>{
    //     let cadena=e.target.value.trim();
    //     $('#busqueda tbody').empty();
    //     if(cadena!='')
    //         buscarResultado(cadena);
    // });
    
    // function buscarResultado(cadena){
    //     $.ajax({
    //         data: {
    //                 'cadena':cadena,
    //                 'id_almacen':document.getElementById('AuxAlmacen').value,
    //             },
    //         type: 'POST',
    //         dataType: 'json',
    //         url: '?/importaciones/buscar_productos',
    //     })
    //     .done(function(data,textStatus,jqXHR){
    //         let a=document.getElementById('busqueda');
    //         a.children[1].innerHTML='';
    //         dibujarBusqueda(data);
    //     })
    //     .fail(function(jqXHR,textStatus,errorThrown) {
    //         console.log(textStatus)
    //     });
    // }
    // function dibujarBusqueda(Datos){
    //     let productos=$('#busqueda tbody'),
    //         Contenido='';
    //     Datos.forEach(Dato=>{
    //         const Imagen=(Dato['imagen']=='')?'<?=imgs?>/image.jpg':'<?=files?>/productos/'+$Dato['imagen'];
    //         Contenido+=`<tr>
    //                         <td><img src='${Imagen}' class='img-rounded cursor-pointer' width='75' height='75'></td>
    //                         <td>${Dato['codigo']}</td>
    //                         <td>${Dato['nombre']}</td>
    //                         <td>${Dato['categoria']}</td>
    //                         <td>${Dato['ingresos']-Dato['egresos']}</td>
    //                         <td>${Dato['precio_actual']}</td>
    //                         <td>
    //                             <button type='button' class='btn btn-success' onclick='agregar(${JSON.stringify(Dato)})'>
    //                                 <span class='glyphicon glyphicon-shopping-cart'></span>
    //                             </button>
    //                         </td>
    //                     </tr>`;
    //     });
    //     productos.append(Contenido);
    // }
    // function agregar(Dato,Opcion=true){
    //     let Sw=false,
    //         Posicion=0;
    //     for(let i=0;i<Productos.length;++i){
    //         if(Productos[i].id_producto===Dato.id_producto){
    //             Sw=true;
    //             Posicion=i;
    //             break;
    //         }
    //     }
    //     if(!Sw)
    //         Productos.push(Dato);
    //     else{
    //         ++Productos[Posicion].cantidad;
    //         Productos[Posicion].importe=Productos[Posicion].cantidad*Productos[Posicion].costo;
    //     }
    //     if(Opcion)
    //         dibujar();
    // }
    // function dibujar(){
    //     let Tabla=$('#preparacion tbody'),
    //         Contenido='';
    //     Tabla.empty();
    //     Productos.forEach((Producto,index)=>{
    //         Contenido+=`<tr>
    //                         <td>${Producto['codigo']}</td>
    //                         <td>${Producto['nombre']}</td>
    //                         <td><input style='width:120px' type='date' id='fechav${index}' onchange='actualizar("fechav",${index},this.value)' name='fechav[]' value='${Producto['fechav']}'></td>
    //                         <td><input style='width:120px' type='text' name='lote[]' onchange='actualizar("lote",${index},this.value)' value='${Producto['lote']}'></td>
    //                         <td><input style='width:60px' type='text' id='cantidad${index}' onkeyup='actualizar("cantidad",${index},this.value)' name='cantidad[]' value='${Producto['cantidad']}'></td>
    //                         <td><input style='width:60px' type='text' id='costo${index}' onkeyup='actualizar("costo",${index},this.value)' name='costo[]' value='${Producto['costo']}'></td>
    //                         <td>
    //                             <input style='width:60px' type='hidden' id='importe${index}' name='importe[]' value='${Producto['importe']}'>
    //                             <input style='width:60px' type='hidden' name='producto_id[]' value='${Producto['id_producto']}'>
    //                             <input style='width:60px' type='hidden' name='unidad_id[]' value='${Producto['id_unidad']}'>
    //                             <span id='import${index}'>${Producto['importe']}</span>
    //                         </td>
    //                         <td>
    //                             <button class='btn btn-danger btn-sm' onclick='eliminar(this,${index})'>
    //                                 <span class='glyphicon glyphicon-trash'></span>
    //                             </button>
    //                         </td>
    //                     </tr>`;
    //     });
    //     Tabla.append(Contenido);
    //     calcular();
    // }
    
    function eliminar(boton,index){
        let table=boton.parentNode.parentNode.parentNode,
            tr=boton.parentNode.parentNode;
        table.removeChild(tr);
        Productos.splice(index,1);
        calcular();
    }
    function calcular(){
        let Total=0;
        Productos.forEach(Producto=>{
            let SubTotal=(parseFloat(Producto.cantidad)*parseFloat(Producto.costo)).toFixed(2);
            Total=parseFloat(Total)+parseFloat(SubTotal);
        });
        document.getElementById('totalF').value=Total;
    }
    //Enviar Preparacion
    const formulario=document.getElementById('formulario');
    formulario.addEventListener('submit',e=>{
        $.ajax({
            type: 'POST',
            data: $('#formulario').serialize(),
            dataType: 'json',
            url: '?/importaciones/guardar_editar_preparacion',
        })
        .done(function(data,textStatus,jqXHR){
            // console.log(data);
            $.notify({
				message: data[1]
			}, {
				type: data[0]
            });
            if(data[0]=='success'){
                // window.location.replace("?/importaciones/gastos");
                setTimeout(function(){ window.location = "?/importaciones/gastos"; },3000);
                let Tabla=$('#preparacion tbody').empty();
                Productos=[];
                $('#formulario')[0].reset();
            }
        })
        .fail(function(jqXHR,textStatus,errorThrown) {
            console.log(textStatus);
        });
        e.preventDefault();
    });

    function sidenav(){
		let contenedor=document.getElementById('ContenedorF');
		if(contenedor.children[0].classList.contains('col-md-6')){
			contenedor.children[0].classList.remove('col-md-6');
			contenedor.children[0].classList.add('col-md-12');
			contenedor.children[1].classList.add('hidden');
		}
		else{
			contenedor.children[0].classList.remove('col-md-12');
			contenedor.children[0].classList.add('col-md-6');
			contenedor.children[1].classList.remove('hidden');
		}
	}
	
	function buscarProductos(cadena){
        let id_almacen=document.getElementById('almacenF').value,
            id_proveedor=document.getElementById('proveedorF').value,
            productos=document.getElementById('productos');
            cadena=cadena.trim();
        
        if(id_almacen!=='' && cadena!==''){
            $.ajax({
                data: {cadena,id_almacen,id_proveedor},
                type: 'POST',
                dataType: 'json',
                url: '?/importaciones/servicio_buscar',
            })
            .done(function(data,textStatus,jqXHR){
                productos.children[1].innerHTML='';
                data.forEach((Dato,index)=>{
                    productos.children[1].innerHTML+=`<tr>
                            <td class="hidden">${index+1}</td>
                            <td data-codigo="${Dato['id_producto']}">${Dato['codigo']}</td>
                            <td data-nombre="${Dato['id_producto']}">${Dato['nombre']}</td>
                            <td>${Dato['total']}</td>
                            <td class="hidden">${Dato['unidad']}</td>
                            <td data-precio="${Dato['id_producto']}">${(Dato['factura_v'] == true)?parseFloat(Dato['costo']-(Dato['costo']*0.13)).toFixed(2):parseFloat(Dato['costo']).toFixed(2)}</td>
                            <td class="hidden" data-unidad="${Dato['id_producto']}">${Dato['unidadd_idd']}</td>
                            <td>
                                <button class='btn btn-success btn-sm' data-comprar="${Dato['id_producto']}" onclick='adicionar_producto("${Dato['id_producto']}")'>
                                    <span class='glyphicon glyphicon-plus'></span>
                                </button>
                            </td>
                        </tr>`;                        
                });
            })
            .fail(function(e) {
                console.log(e)
            });
        }
        else if(id_almacen===''){
            $.notify({
                message: 'Debe Seleccionar un Almacen'
            }, {
                type: 'warning'
            });
        }
        else{
            productos.children[1].innerHTML='';
        }
    }

    function adicionar_producto(id_producto) {
        var d = new Date();
        const seg = '_' + d.getMilliseconds();
        var $producto = $('[data-producto=' + id_producto + ']');
        var $cantidad = $producto.find('[data-cantidad]');
        var $compras = $('#preparacion tbody');
        var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
        var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
        var precio = $.trim($('[data-precio=' + id_producto + ']').text());
        var unidad = $.trim($('[data-unidad=' + id_producto + ']').text());
        var plantilla = '';
        var cantidad;
        var formato = $('[data-formato]').attr('data-formato');

        if ($producto.size()) {
            cantidad = $.trim($cantidad.val());
            cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
            cantidad = (cantidad < 9999999) ? cantidad + 1 : cantidad;
            $cantidad.val(cantidad).trigger('blur');
        } else {
            plantilla = '<tr class="active" data-producto="' + id_producto + seg + '">' +
                '<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="producto_id[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
                '<td>' + nombre + '</td>' +
                '<td><div class="row"><div class="col-xs-12"><input type="date" name="fechav[]" value="<?= date('Y/m/d'); ?>" class="form-control input-xs text-right" data-fecha="" data-validation="required"></div></div></td>' +
                '<td><input type="text" value="" name="lote[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-contenedor="" data-validation="required "  data-validation-error-msg="Debe ingresar el Lote" ></td>' +
                
                '<td><input type="text" value="1" name="cantidad[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-error-msg="Debe ser número entero positivo" onkeyup="calcular_importe(\'' + id_producto + seg + '\')"></td>' +
                

                '<td><input type="text" value="' + precio + '" name="costo[]" class="form-control input-xs text-right" autocomplete="off" data-costo="" data-validation="required number" data-validation-allowing="rnge[0.01;10000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onkeyup="calcular_importe(\'' + id_producto + seg + '\')" onblur="redondear_importe(\'' + id_producto + seg + '\')"><input style="width:60px" type="hidden" name="unidad_id[]" value="' + unidad + '"></td>' +
                
                '<td class="text-nowrap text-right" data-importe="">0.00</td>' +
                
                '<td class="text-nowrap text-center">' +
                '<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(\'' + id_producto + seg + '\')"><span class="glyphicon glyphicon-remove"></span></button>' +
                '</td>' +
                '</tr>';

                // <div class="input-group">
                //         <div class="input-group-prepend">
                //             <div class="input-group-text">
                //             <input type='checkbox' name='factura_val[]' checked>
                //             </div>
                //         </div>
                //         <input type='text' value='' name='facturas[]' class='form-control input-xs text-right' maxlength='7' autocomplete='off' data-contenedor='' data-validation="required number" data-validation-error-msg='Debe ser número entero positivo'>
                    // </div>
            $compras.append(plantilla);

            $compras.find('[data-cantidad], [data-costo]').on('click', function() {
                $(this).select();
            });

            // $compras.find('[data-fecha]').datetimepicker({
            //     format: formato,
            //     minDate: '<?php // date('Y-m-d') ?>'
            // });

            $compras.find('[title]').tooltip({
                container: 'body',
                trigger: 'hover'
            });
        }
        calcular_importe(id_producto);
    }
    function calcular_importe(id_producto) {
        SwGuardar=false;
        
        var $producto = $('[data-producto=' + id_producto + ']');
        var $cantidad = $producto.find('[data-cantidad]');
        var $costo = $producto.find('[data-costo]');
        var $importe = $producto.find('[data-importe]');
        var cantidad, costo, importe;

        cantidad = $.trim($cantidad.val());
        cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
        costo = $.trim($costo.val());
        costo = ($.isNumeric(costo)) ? parseFloat(costo) : 0.00;
        importe = cantidad * costo;
        importe = importe.toFixed(2);
        $importe.text(importe);

        calcular_total();
    }

    function calcular_total() {
        var $compras = $('#preparacion tbody');
        var $total = $('[data-subtotal]:first');
        var $importes = $compras.find('[data-importe]');
        var importe, total = 0;

        $importes.each(function(i) {
            importe = $.trim($(this).text());
            importe = parseFloat(importe);
            total = total + importe;
        });

        $total.text(total);
        $('[data-compras]:first').val($importes.size()).trigger('blur');
        
        $('#totalF').val(total.toFixed(2)).trigger('blur');
        
        //set_cuotas();
    }
    function redondear_importe(id_producto) {
        var $producto = $('[data-producto=' + id_producto + ']');
        var $costo = $producto.find('[data-costo]');
        var costo;

        costo = $.trim($costo.val());
        costo = ($.isNumeric(costo)) ? parseFloat(costo).toFixed(2) : costo;
        $costo.val(costo);

        calcular_importe(id_producto);
    }
</script>
<?php
require_once show_template('footer-advanced');
