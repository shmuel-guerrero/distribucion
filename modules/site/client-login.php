<?php
    header('Content-Type: application/json');
    require(config.'/database.php');
    require(libraries.'/JWT/jwt.php');
    if(is_post()):
        if(isset($_POST['email']) && isset($_POST['clave'])):
            $email=escape(trim($_POST['email']));
            $clave=escape(trim($_POST['clave']));
            $clave=sha1(prefix.md5($clave));
            $Consulta=$db->query("SELECT id_cliente AS id,cliente,imagen FROM inv_clientes WHERE email='{$email}' AND clave='{$clave}' AND token='' LIMIT 1")->fetch_first();
            if($Consulta):
                $auth=new Auth;
                $Crear=$auth->SignIn($Consulta);
                echo json_encode([
                        'ok'=>true,
                        'token'=>$Crear,
                    ]);
            else:
                echo json_encode([
                        'ok'=>false,
                        'info'=>[
                            'title'=>'Error',
                            'message'=>'Usuario o Contraseña Incorrecta',
                            'image'=>'warning',
                        ],
                    ]);
            endif;
            return;
        endif;
    endif;
    echo json_encode([
            'ok'=>false,
            'info'=>[
                'title'=>'Error',
                'message'=>'Ocurrio un Error Inesperado',
                'image'=>'error',
            ],
        ]);