<?php
    $Datos=$_POST['term'];
    $Datos=$db->query("SELECT cliente,nit, id_cliente, credito, dias
                    FROM inv_clientes
                    WHERE cliente LIKE '%{$Datos}%' OR nit LIKE '%{$Datos}%'
                    GROUP BY(cliente)
                    ORDER BY cliente ASC,nit ASC
                    LIMIT 20")->fetch();
    $json= array();
    if($Datos):
        foreach($Datos as $Nro=>$Dato):
            $json[]= array('id'=>"{$Dato['nit']}|{$Dato['cliente']}|{$Dato['id_cliente']}|{$Dato['credito']}|{$Dato['dias']}",
                            'text'=>"{$Dato['id_cliente']} - {$Dato['nit']} - {$Dato['cliente']}");
        endforeach;
    endif;
    echo json_encode($json);