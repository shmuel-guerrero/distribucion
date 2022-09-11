<div class="form-group" style="display:none">
        <label for="ubicacion" class="col-sm-4 control-label">Ubicación:</label>
        <div class="col-sm-8">
            <select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true">
                <option value="">Buscar</option>
                <?php foreach ($clientes as $cliente) { ?>
                    <option value="<?= escape($cliente['nit_ci']) . '|' . escape($cliente['nombre_cliente']) . '|' . escape($cliente['id_cliente']) . '|' . escape($cliente['direccion']) . '|' . escape($cliente['telefono']) . '|' . escape($cliente['ubicacion']) . '|' . escape($cliente['credito']) . '|' . escape($cliente['dias']); ?>"><?= escape($cliente['codigo_cliente']) . ' &mdash; ' . escape($cliente['nombre_cliente']) . ' &mdash; ' . escape($cliente['nit_ci']); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>