<?php
    if(is_post()):
        if(isset($_POST['gasto'])):
            $gasto=trim($_POST['gasto']);
            $Consulta=$db->query("SELECT id_gastos,gasto,costo_anadido
                                FROM inv_gastos
                                WHERE gasto LIKE '%{$gasto}%'
                                ORDER BY gasto ASC LIMIT 15")->fetch();
            echo json_encode($Consulta);
        else:
            require_once bad_request();
		    die;
        endif;
    else:
        require_once not_found();
	    die;
    endif;