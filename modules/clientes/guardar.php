<?php

// Verifica si es una peticion post
if (is_post()) {

    // Verifica la existencia de los datos enviados
    if (isset($_POST['nombre']) && isset($_POST['telefono']) && isset($_POST['descripcion'])) {

        // Importa la libreria para subir la imagen
        require_once libraries . '/upload-class/class.upload.php';

        // Define la ruta
        $ruta = files . '/tiendas/';

        $data = get_object_vars(json_decode($_POST['data']));
        // Obtiene los datos del cliente
        $nombres = trim($_POST['nombre']);
        $nombres_factura = trim($_POST['nombre_factura']);
        $ci = trim($_POST['ci']);
        $email = trim($_POST['email']);
        $clave = sha1(prefix . md5($ci));
        $direccion = trim($_POST['direccion']);
        $telefono = trim($_POST['telefono']);
        $tipo = trim($_POST['tipo']);
        $descripcion = trim($_POST['descripcion']);
        $coordenadas = trim($_POST['atencion']);
        $id_grupo = trim($_POST['id_grupo']);

        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
            if ($_POST['id_cliente'] != 0) {
                $id = $_POST['id_cliente'];

                if (isset($_FILES['imagen'])) {
                    $imagen = $_FILES['imagen'];

                    list($ancho, $alto) = getimagesize($imagen['tmp_name']);

                    $ancho = $ancho * $data['scale'];
                    $alto = $alto * $data['scale'];

                    // Define la extension de la imagen
                    $extension = 'jpg';

                    $imagen_final = md5(secret . random_string() . $nombres);

                    // Instancia la imagen
                    $imagen = new upload($imagen);

                    if ($imagen->uploaded) {
                        // Define los parametros de salida
                        $imagen->file_new_name_body = $imagen_final;
                        $imagen->image_resize = true;
                        $imagen->image_ratio_crop = true;

                        // Procesa la imagen
                        @$imagen->process($ruta);
                    }
                    if ($coordenadas == '') {
                        $cliente = array(
                            'cliente' => $nombres,
                            'imagen' => $imagen_final . '.' . $extension,
                            'nombre_factura' => $nombres_factura,
                            'nit' => $ci,
                            'tipo' => $tipo,
                            'cliente_grupo_id' => 0,
                            'direccion' => $direccion,
                            'descripcion' => $descripcion,
                            'telefono' => $telefono,
                            'estado' => 'si',
                            'email' => $email,
                            'clave' => $clave,
                        );
                    } else {
                        $cliente = array(
                            'cliente' => $nombres,
                            'imagen' => $imagen_final . '.' . $extension,
                            'nombre_factura' => $nombres_factura,
                            'nit' => $ci,
                            'tipo' => $tipo,
                            'cliente_grupo_id' => 0,
                            'direccion' => $direccion,
                            'descripcion' => $descripcion,
                            'ubicacion' => $coordenadas,
                            'telefono' => $telefono,
                            'estado' => 'si',
                            'email' => $email,
                            'clave' => $clave,
                        );
                    }
                    $db->where('id_cliente', $id)->update('inv_clientes', $cliente);
                } else {
                    if ($coordenadas == '') {
                        $cliente = array(
                            'cliente' => $nombres,
                            'nombre_factura' => $nombres_factura,
                            'nit' => $ci,
                            'tipo' => $tipo,
                            'cliente_grupo_id' => 0,
                            'direccion' => $direccion,
                            'descripcion' => $descripcion,
                            'telefono' => $telefono,
                            'estado' => 'si',
                            'email' => $email,
                            'clave' => $clave,
                        );
                    } else {
                        $cliente = array(
                            'cliente' => $nombres,
                            'nombre_factura' => $nombres_factura,
                            'nit' => $ci,
                            'tipo' => $tipo,
                            'cliente_grupo_id' => 0,
                            'direccion' => $direccion,
                            'descripcion' => $descripcion,
                            'ubicacion' => $coordenadas,
                            'telefono' => $telefono,
                            'estado' => 'si',
                            'email' => $email,
                            'clave' => $clave,
                        );
                    }
                    $db->where('id_cliente', $id)->update('inv_clientes', $cliente);
                }
                echo json_encode(array('estado' => 's'));
                
            } else {
                //obtiene el plan habilitado.
                $plan = $db->query("SELECT sp.plan FROM sys_planes sp WHERE sp.estado = 'Activo'")->fetch_first()['plan'];

                // se obtiene el limite permitido de creacion registros de clientes
                $limite = validar_plan($db, array('plan' => $plan, 'caracteristica' => 'clientes'));
                //obtiene la cantidad de registros en la base de datos
                $registros = $db->query("SELECT count(*)as nro_registros FROM inv_clientes")->fetch_first()['nro_registros'];

                //Valida que los registros sean menor o igual al limite del plan
                if ($registros <= $limite) {
                    $bus = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombres, 'nit' => $ci))->fetch_first();

                    if ($bus) {
                        echo json_encode(array('estado' => 'y'));
                    } else {
                        if (isset($_FILES['imagen'])) {
                            $imagen = $_FILES['imagen'];

                            list($ancho, $alto) = getimagesize($imagen['tmp_name']);

                            $ancho = $ancho * $data['scale'];
                            $alto = $alto * $data['scale'];

                            // Define la extension de la imagen
                            $extension = 'jpg';

                            $imagen_final = md5(secret . random_string() . $nombres);

                            // Instancia la imagen
                            $imagen = new upload($imagen);

                            if ($imagen->uploaded) {
                                // Define los parametros de salida
                                $imagen->file_new_name_body = $imagen_final;
                                $imagen->image_resize = true;
                                $imagen->image_ratio_crop = true;

                                // Procesa la imagen
                                @$imagen->process($ruta);
                            }
                            $cliente = array(
                                'cliente' => $nombres,
                                'nombre_factura' => $nombres_factura,
                                'nit' => $ci,
                                'tipo' => $tipo,
                                'cliente_grupo_id' => 0,
                                'direccion' => $direccion,
                                'descripcion' => $descripcion,
                                'ubicacion' => $coordenadas,
                                'imagen' => $imagen_final . '.' . $extension,
                                'telefono' => $telefono,
                                'estado' => 'si',
                                'email' => $email,
                                'clave' => $clave,
                            );
                        } else {
                            $cliente = array(
                                'cliente' => $nombres,
                                'nombre_factura' => $nombres_factura,
                                'nit' => $ci,
                                'tipo' => $tipo,
                                'cliente_grupo_id' => 0,
                                'direccion' => $direccion,
                                'descripcion' => $descripcion,
                                'ubicacion' => $coordenadas,
                                'imagen' => '',
                                'telefono' => $telefono,
                                'estado' => 'si',
                                'email' => $email,
                                'clave' => $clave,
                            );
                        }

                        $db->insert('inv_clientes', $cliente);
                        echo json_encode(array('estado' => 's'));
                    }
                } else {
                    echo json_encode(array('estado' => 'l'));
                }
            }
            
            //se cierra transaccion
			$db->commit();

            // Redirecciona a la pagina principal
        } catch (Exception $e) {
            $status = false;
            $error = $e->getMessage();
            //se cierra transaccion
            $db->rollback();

            // Instancia la variable de notificacion
            $_SESSION[temporary] = array(
                'alert' => 'danger',
                'title' => 'Problemas en el proceso de interacción con la base de datos.',
                'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
            );
            // Redirecciona a la pagina principal o anterior			
            return redirect(back());
            //Se devuelve el error en mensaje json
            //echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

        }
    } else {
        // Error 401
        require_once bad_request();
        exit;
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
