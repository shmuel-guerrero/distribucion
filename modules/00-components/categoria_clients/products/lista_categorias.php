<?php 
    global $db;
    $tipos_clients = $db->query('SELECT * FROM inv_tipos_clientes tc ')->fetch();
    /* $tipos_clients = $db->query('SELECT tc.*, u.cliente_tipo_id FROM inv_tipos_clientes tc 
    LEFT JOIN inv_unidades u ON tc.id_tipo_cliente = u.id_unidad WHERE u.cliente_tipo_id IS  NULL')->fetch(); */
?>


<div class="form-group">
    <label for="tipo_precio" id="tipo_precio_label" class="col-md-3 control-label">Tipo de precio:</label>
    <div class="col-md-9">
        <select name="tipo_precio" id="tipo_precio" class="form-control text-uppercase" data-validation="required">
            <option value="">Seleccionar</option>
            <?php foreach ($tipos_clients as $elemento) { ?>
            <option value="<?= $elemento['id_tipo_cliente']; ?>"><?= escape($elemento['tipo_cliente']); ?></option>
            <?php } ?>
        </select>
    </div>
</div>