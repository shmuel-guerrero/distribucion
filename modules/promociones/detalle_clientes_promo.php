p
<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

$id_cliente = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el empleado

$cliente = $db->query("SELECT a.cliente,a.id_cliente,a.nombre_factura,a.nit,a.direccion,
a.telefono,sum(d.precio*d.cantidad) as saldo_acumulado,o.nombre,b.fecha_egreso,
m.item_promo,m.monto_promo 
FROM inv_clientes a 
LEFT OUTER JOIN inv_egresos b ON a.cliente = b.nombre_cliente 
LEFT OUTER JOIN inv_egresos_detalles d ON d.egreso_id = b.id_egreso
LEFT OUTER JOIN inv_requisitos_promo r ON r.productos_id = d.producto_id
LEFT OUTER JOIN inv_promociones_monto m ON m.id_promocion = r.promocion_monto_id
LEFT OUTER JOIN inv_productos o ON o.id_producto = d.producto_id
LEFT OUTER JOIN inv_participantes_promos p on  a.id_cliente=p.cliente_id
LEFT OUTER JOIN inv_clientes_grupos g on a.cliente_grupo_id=p.cliente_grupo_id
where a.id_cliente=".$id_cliente." group by a.cliente, a.nit")->fetch_first();

//var_dump($cliente);die();

$productos = $db->query("select p.id_producto, p.descripcion, p.imagen, p.codigo, p.nombre, p.nombre_factura, 
    p.cantidad_minima, p.precio_actual, p.unidad_id,
    ifnull(s.cantidad_egresos, 0) as cantidad_egresos,  u.unidad, u.sigla, c.categoria,a.id_asignacion
    from inv_productos p
    
    left join inv_requisitos_promo r on r.productos_id=p.id_producto
   
    left join (select d.producto_id, sum(d.cantidad) as cantidad_egresos, e.cliente_id from inv_egresos_detalles d 
    left join inv_egresos e on e.id_egreso = d.egreso_id 
    where e.almacen_id = 1 group by d.producto_id ) as s on s.producto_id = p.id_producto 
    
    left join inv_clientes l on l.id_cliente= s.cliente_id
    
    left join inv_asignaciones a on a.producto_id = p.id_producto  AND a.visible = 's'
    
    left join inv_unidades u on u.id_unidad = p.unidad_id 
    left join inv_categorias c on c.id_categoria = p.categoria_id
 
    where l.id_cliente=".$id_cliente." ")->fetch();
    


// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = in_array('proformas_ver', $permisos);
$permiso_eliminar = in_array('proformas_eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_facturar = in_array('proformas_facturar', $permisos);
$permiso_cambiar = true;

?>
<?php require_once show_template('header-advanced'); ?>
<link rel="stylesheet" href="<?= css; ?>/leaflet.css">
<link rel="stylesheet" href="<?= css; ?>/leaflet-routing-machine.css">
<link rel="stylesheet" href="<?= css; ?>/site.css">
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
    .leaflet-control-attribution,
    .leaflet-routing-container {
        display: none;
    }
</style>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <b>Ver cliente <?= $_institution['empresa1'] ?></b>
    </h3>
</div>
<div class="panel-body">

    <div class="row">
        <div class="col-sm-6">
            <form method="post" id="cliente_form" class="form-horizontal" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombres" class="col-md-3 control-label">Cliente:</label>
                    <div class="col-md-9">
                        <input type="hidden" id="punto" value="<?php if($cliente['ubicacion']!=''){ echo $cliente['ubicacion'];}else{ echo '-16.503961,-68.162241';} ?>"/>
                        <input type="hidden" value="<?= $id_cliente; ?>" name="id_cliente22" id="id_cliente22" data-validation="required number">
                        <input type="text" value="<?= $cliente['cliente'] ?>" name="nombres" id="nombres" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="- " data-validation-length="max100" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="nombres_factura" class="col-md-3 control-label">Nombres de factura:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['nombre_factura'] ?>" name="nombres_factura" id="nombres_factura" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing=" " data-validation-length="max100" data-validation-optional="true" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ci" class="col-md-3 control-label">CI/NIT:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['nit'] ?>" name="ci" id="ci" class="form-control" autocomplete="off" data-validation="letternumber length" data-validation-allowing=" " data-validation-length="max100" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Dirección:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['direccion'] ?>" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="letternumber length" data-validation-allowing=" " data-validation-length="max100" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="telefono" class="col-md-3 control-label">Telefono:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['telefono'] ?>" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="number length" data-validation-allowing=" " data-validation-length="max100" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="telefono" class="col-md-3 control-label">Imagen:</label>
                   
                    <div class="col-md-3 card" >
                        <img src="<?= ($cliente['imagen'] == '') ? imgs . '/image.jpg' : files . '/tiendas/' . $cliente['imagen']; ?>"  class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="100%">
                    </div>
                </div>
             
                <div class="form-group">
                    <label for="item" class="col-sm-3 control-label">Premio Item:</label>
                    <div class="col-sm-9">
                        <?php  
                        
                            if($cliente['item_promo']){
                                $item = $db->query("select nombre from inv_productos where id_producto=".$cliente['item_promo']."")->fetch_first(); 
                            }
                        
                        ?>
                         <input type="text" value="<?= $item['nombre']; ?>" name="item" id="item" class="form-control" autocomplete="off" data-validation="number length" data-validation-allowing=" " data-validation-length="max100" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="item_monto" class="col-sm-3 control-label">Premio Monto:</label>
                    <div class="col-sm-9">
                         <input type="text" value="<?= $cliente['monto_promo'] ?>" name="item_monto" id="item_monto" class="form-control" autocomplete="off" data-validation="number length" data-validation-allowing=" " data-validation-length="max100" readonly>
                    </div>
                </div>

                 <div class="form-group">
                    <label for="tipo" class="col-sm-3 control-label">Saldo Acumulado:</label>
                    <div class="col-sm-9">
                         <input type="text" value="<?= $cliente['saldo_acumulado'] ?>" name="saldo" id="saldo" class="form-control" autocomplete="off" data-validation="number length" data-validation-allowing=" " data-validation-length="max100" readonly>
                    </div>
                </div>
                 <div class="form-group">
                    <label for="tipo" class="col-sm-3 control-label">Fecha:</label>
                    <div class="col-sm-9">
                         <input type="text" value="<?= $cliente['fecha_egreso'] ?>" name="fecha" id="fecha" class="form-control" autocomplete="off" data-validation="number length" data-validation-allowing=" " data-validation-length="max100" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                        <div class="col-xs-12 col-sm-3 text-right">
                            <a href="?/promociones/listar_clientes_promo" type="button" id="listar" class="btn btn-primary" >Volver al listado</a>
                        </div>
                       
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div class="panel-body">
                   
                <?php if ($productos) { ?>
                <table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
                    <thead>
                        <tr class="active">
                            <th class="text-nowrap">Imagen</th>
                            <th class="text-nowrap">Código</th>
                            <th class="text-nowrap">Nombre</th>
                            <th class="text-nowrap">Descripción</th>
                            <th class="text-nowrap">Tipo</th>
                            <th class="text-nowrap">Stock</th>
                            <th class="text-nowrap">Costo</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $nro => $producto) {
                            $otro_precio = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id=b.id_unidad  AND a.visible = "s" ')->where('a.producto_id',$producto['id_producto'])->fetch();
                            ?>
                        <tr>
                            <td class="text-nowrap"><img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" width="75" height="75"></td>
                            <td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
                            <td>
                                <span><?= escape($producto['nombre']); ?></span>
                                <span class="hidden" data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre_factura']); ?></span>
                            </td>
                            <td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
                            <td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
                            <td class="text-nowrap text-right" data-stock="<?= $producto['id_producto']; ?>"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
                            <td class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>">
                                *<?= escape($producto['unidad'].': '); ?><b><?= escape($producto['costo_ingresos']); ?></b>
                            </td>
                        </tr>

                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                <div class="alert alert-danger">
                    <strong>Advertencia!</strong>
                    <p>No existen productos registrados en la base de datos.</p>
                </div>
                <?php } ?>
            </div>
        </div>   
    </div>
</div>


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

<script>

function validaForm(){
    // Campos de texto
    if($("#nombres").val() == ""){
        $("#nombres").focus();       
        return false;
    }
    if($("#ci").val() == ""){
        $("#ci").focus();      .
        return false;
    }
    if($("#direccion").val() == ""){
        $("#direccion").focus();      
        return false;
    }
    if($("#telefono").val() == ""){
        $("#telefono").focus();       
        return false;
    }
    if($("#descripcion").val() == ""){
        $("#descripcion").focus();       
        return false;
    }
    return true;
}

</script>
<?php require_once show_template('footer-advanced'); ?>