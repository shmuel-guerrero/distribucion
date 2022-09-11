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