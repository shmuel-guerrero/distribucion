<?php
    header('Content-Type: application/json');
    require(config.'/database.php');
    require(libraries.'/PHPMailer/index.php');
    if(is_post()):
        if(isset($_POST['nombre']) && isset($_POST['email']) && isset($_POST['clave'])):
            $nombre=escape(trim($_POST['nombre']));
            $email=escape(trim($_POST['email']));
            $clave=escape(trim($_POST['clave']));
            $clave=sha1(prefix.md5($clave));
            $Consulta=$db->query("SELECT email FROM inv_clientes WHERE email='{$email}' LIMIT 1")->fetch_first();
            if(!$Consulta):
                $Token=md5(openssl_random_pseudo_bytes(4));
                $Datos=array(
                        'cliente'=>$nombre,
                        'nombre_factura'=>$nombre,
                        'nit'=>'',
                        'estado'=>'si',
                        'telefono'=>'',
                        'direccion'=>'',
                        'descripcion'=>'',
                        'ubicacion'=>'',
                        'imagen'=>'',
                        'categoria'=>0,
                        'tipo'=>'',
                        'cliente_grupo_id'=>0,
                        'email'=>$email,
                        'clave'=>$clave,
                        'token'=>$Token
                );
                $id_cliente=$db->insert('inv_clientes',$Datos);
                $url1=url1.'/sistema/?/site/client-confirm/';
                $Mensaje="<html>
                        <body>
                            <div>
                                <img src='cid:logo_php' align='left' width='100' border='0' hspace='10'>
                            </div>
                            <p>
                                <strong>
                                    <span style='font-size: 14pt;'>
                                        <span style='font-family:helvetica,arial,sans-serif;color: #123456;'>Confirmar Correo Electrónico</span>
                                    </span>
                                </strong>
                            </p>
                            <p>
                                <span style='font-family:helvetica,arial,sans-serif;color:#333333'>
                                    Estimado/a {$nombre}
                                    <br>
                                    Gracias por abrir una cuenta de MI EMPRESA. Para utilizar su cuenta, primero deberá confirmar su correo electrónico haciendo click en el botón a continuación.
                                </span>
                            </p>
                            <p>
                                <a href='{$url1}{$Token}' style='font-family:helvetica,arial,sans-serif;background:#123456;color:#FFF;padding:8px;border-radius:5px;border:none;text-decoration:none'>
                                    Confirme su correo electrónico
                                </a>
                            </p>
                            <p>
                                <span style='font-family:helvetica,arial,sans-serif;color:#333333;'>Gracias</span>
                            </p>
                            <p>
                                <span style='font-family:helvetica,arial,sans-serif;color:#333333;'>Su equipo Mi Empresa</span>
                            </p>
                        </body>
                    </html>";
                EnviarCorreo($email,$nombre,'Verificación de correo',$Mensaje);
                echo json_encode([
                        'ok'=>true,
                        'info'=>[
                            'title'=>'Exitoso',
                            'message'=>'Cuenta registrada exitosamente',
                            'image'=>'success',
                        ],
                    ]);
            else:
                echo json_encode([
                        'ok'=>false,
                        'info'=>[
                            'title'=>'Duplicado',
                            'message'=>'Ya existe este correo',
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
                'title'=>'Fallido',
                'message'=>'Ocurrio un error inesperado',
                'image'=>'error',
            ]
        ]);