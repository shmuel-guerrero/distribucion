<?php
    header('Content-Type: application/json');
    require(config.'/database.php');
    require(libraries.'/JWT/jwt.php');
    $headers=apache_request_headers();
    if(isset($headers['Token'])):
        $auth=new Auth;
        $Token=escape(trim($headers['Token']));
        $Verificar=$auth->Check($Token);
        if($Verificar):
            $Recuperado=$auth->GetData($Token);
            $Recuperado=get_object_vars($Recuperado);
            $IdCliente=$Recuperado['id'];
            $Datos=$db->query("SELECT cliente,telefono,direccion,ubicacion FROM inv_clientes WHERE id_cliente='{$IdCliente}' LIMIT 1")->fetch_first();
            echo json_encode([
                    'ok'=>true,
                    'data'=>$Datos
                ]);
            return;
        endif;
        echo json_encode([
                'ok'=>false,
                'info'=>[
                    'title'=>'Error',
                    'message'=>'Acceso Prohibido',
                    'image'=>'error',
                ],
            ]);
        return;
    endif;
    echo json_encode([
            'ok'=>false,
            'info'=>[
                'title'=>'Error',
                'message'=>'Ocurrio un Error Inesperado',
                'image'=>'error',
            ],
        ]);