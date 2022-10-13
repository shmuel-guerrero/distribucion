<?php

class ventasClass
{
    function __construct()
    {
        
    }

    public function verificaVenta($idEgreso = 0){

        global $db;
        $condicion = array(
            'movimiento_id' => $idEgreso, 
            'tipo_movimiento' => 'Egreso',
            'estado' => 'Pedido'
        );
        $ventaEfect = $db->from('inv_egresos_efectivo')->where($condicion)->fetch_first();

        return ($ventaEfect) ? true : false;
    }
    
}
