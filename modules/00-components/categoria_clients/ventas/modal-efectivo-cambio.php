<?php
    global $db;

    // Obtiene la moneda oficial
    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

?>

<style>
    .modal {
  text-align: center;
}

@media screen and (min-width: 768px) { 
  .modal:before {
    display: inline-block;
    vertical-align: middle;
    content: " ";
    height: 100%;
  }
}

.modal-dialog {
  display: inline-block;
  text-align: left;
  vertical-align: middle;
}
</style>


<div id="modal_efectivo_cambio" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered  modal-lg">
        <form method="post" id="modal_efect_cambio" class="modal-content loader-wrapper form-horizontal" autocomplete="off">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Importe</h4>
            </div>
            <div class="modal-body">  
                <div class="">
                    
                        <div class="form-group">
                            <label for="importe_total" class="col-sm-12 col-md-5 control-label">
                                <span>IMPORTE TOTAL:</span>
                                <span class="text-primary"><?= $moneda; ?></span>
                            </label>
                            <div class="col-sm-12 col-md-7">
                                <div class="container">
                                    <div class="row">
                                        <input type="text" value="0" name="importeTotalModal"  id="importeTotalModal" class="form-control" readonly autocomplete="off" data-validation="required number" data-validation-allowing="range[0.01;10000.00],float" maxlength="5">
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- <div class="form-group">
                            <label for="descPorcentaje" class="col-sm-12 col-md-5 control-label">
                                <span>DESCUENTO %:</span>
                                <span class="text-primary"></span>
                            </label>
                            <div class="col-sm-12 col-md-7">
                                <input type="text" value="0" name="descPorcentaje" id="descPorcentajeModal" class="form-control" readonly autocomplete="off" data-validation="required number" data-validation-allowing="range[0.00;10000.00],float" maxlength="5">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="importe_desc" class="col-sm-12 col-md-5 control-label">
                                <span>IMPORTE TOTAL DESCUENTO APLICADO:</span>
                                <span class="text-primary"><?= $moneda; ?></span>
                            </label>
                            <div class="col-sm-12 col-md-7">
                                <input type="text" value="0" name="importe_desc" id="importeDescModal" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0.01;10000.00],float" maxlength="5">
                            </div>
                        </div> -->
                        <div class="form-group">
                            <label for="pagoEfectivo" class="col-sm-12 col-md-5 control-label">
                                <span>PAGO EFECTIVO:</span>
                                <span class="text-primary"><?= $moneda; ?></span>
                            </label>
                            <div class="col-sm-12 col-md-7">
                                <input type="text" value="0" name="pagoEfectivoModal" id="pagoEfectivoModal" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0.01;10000.00],float" maxlength="5">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cambio" class="col-sm-12 col-md-5 control-label">
                                <span>CAMBIO:</span>
                                <span class="text-primary"><?= $moneda; ?></span>
                            </label>
                            <div class="col-sm-12 col-md-7">
                                <input type="text" value="0" name="cambioModal" id="cambioModal" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000],float" maxlength="5">
                            </div>
                        </div>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                    <span>Guardar</span>
                </button>
                <!-- <button type="reset" class="btn btn-default">
                    <span class="glyphicon glyphicon-refresh"></span>
                    <span>Restablecer</span>
                </button> -->
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span>
                    <span>Cancelar</span>
                </button>
            </div>
            <div id="loader_asignar_precio" class="loader-wrapper-backdrop">
                <span class="loader"></span>
            </div>
        </form>
    </div>
</div>

