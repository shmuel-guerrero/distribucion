<?php


/*
+--------------------------------------------------------------------------
| Redireciona a una pagina en especifica
+--------------------------------------------------------------------------
*/

function redirect($url) {
	header('Location: ' . $url);
	exit;
}


/*
+--------------------------------------------------------------------------
| Devuelve el texto con los caracteres especiales escapados
+--------------------------------------------------------------------------
*/

function escape($text) {
	$text = htmlspecialchars($text, ENT_QUOTES);
	$text = addslashes($text);
	return $text;
}

/*
+--------------------------------------------------------------------------
| Devuelve el texto con el primer caracter en mayuscula y sin lineas
+--------------------------------------------------------------------------
*/

function strtocapitalize($text) {
	$text = strtoupper(substr($text, 0, 1)) . substr($text, 1);
	$text = str_replace('_', ' ', $text);
	return $text;
}

/*
+--------------------------------------------------------------------------
| Convierte una fecha al formato yyyy-mm-dd
+--------------------------------------------------------------------------
*/

function date_encode($date) {
	if (is_numeric(substr($date, 2, 1))) {
		$day = substr($date, 8, 2);
		$month = substr($date, 5, 2);
		$year = substr($date, 0, 4);
	} else {
		$day = substr($date, 0, 2);
		$month = substr($date, 3, 2);
		$year = substr($date, 6, 4);
	}
	return $year . '-' . $month . '-' . $day;
}

/*
+--------------------------------------------------------------------------
| Convierte una fecha al formato yyyy/mm/dd
+--------------------------------------------------------------------------
*/

function date_decode($date, $format = 'Y-m-d') {
	$format = ($format == '') ? 'Y-m-d' : $format;
	$date = explode('-', $date);
	$format = str_replace('Y', $date[0], $format);
	$format = str_replace('m', $date[1], $format);
	$format = str_replace('d', $date[2], $format);
	return $format;
}

/*
+--------------------------------------------------------------------------
| Verifica si es una fecha
+--------------------------------------------------------------------------
*/

function is_date($date) {
	if (preg_match('/^((1|2)[0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date) || preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-((1|2)[0-9]{3})$/', $date)){
		$date = explode('-', $date);
		if (checkdate($date[1], $date[2], $date[0]) || checkdate($date[1], $date[0], $date[2])) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/*
+--------------------------------------------------------------------------
| Obtiene el formato numeral de una fecha
+--------------------------------------------------------------------------
*/

function get_date_numeral($format = 'Y-m-d') {
	$format = ($format == '') ? 'Y-m-d' : $format;
	$format = str_replace('Y', '9999', $format);
	$format = str_replace('m', '99', $format);
	$format = str_replace('d', '99', $format);
	return $format;
}

/*
+--------------------------------------------------------------------------
| Obtiene el formato textual de una fecha
+--------------------------------------------------------------------------
*/

function get_date_textual($format = 'Y-m-d') {
	$format = ($format == '') ? 'Y-m-d' : $format;
	$format = str_replace('Y', 'yyyy', $format);
	$format = str_replace('m', 'mm', $format);
	$format = str_replace('d', 'dd', $format);
	return $format;
}

/*
|------------------------------------------------------------
| Retorna la fecha actual
|------------------------------------------------------------
*/

function now($format = 'Y-m-d') {
	return date($format);
}

/*
|--------------------------------------------------------------------------
| Retorna el nombre del dia de una fecha
|--------------------------------------------------------------------------
*/

function get_date_literal($date) {
	$days = array(1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo');
	$months = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');
	$day = $days[date('N', strtotime($date))];
	$date = explode('-', $date);
	return $day . ' ' . intval($date[2]) . ' de ' . $months[intval($date[1])] . ' de ' . intval($date[0]);
}

/*
|------------------------------------------------------------
| Retorna una fecha con la suma de x dias
|------------------------------------------------------------
*/

function add_day($date, $day = 1) { 
	$date = strtotime('+' . $day . ' day', strtotime($date));
	return date('Y-m-d', $date);
}

/*
+--------------------------------------------------------------------------
| Verifica si una peticion es por medio de ajax
+--------------------------------------------------------------------------
*/

function is_ajax() {
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/*
+--------------------------------------------------------------------------
| Verifica si una peticion llego por el metodo post
+--------------------------------------------------------------------------
*/

function is_post() {
	return $_SERVER['REQUEST_METHOD'] == 'POST';
}

/*
+--------------------------------------------------------------------------
| Muestra la vista 404
+--------------------------------------------------------------------------
*/

function show_template($template) {
	return templates . '/' . $template . '.php';
}

/*
+--------------------------------------------------------------------------
| Muestra la vista 400 bad request
+--------------------------------------------------------------------------
*/

function bad_request() {
	return show_template('400');
}

/*
+--------------------------------------------------------------------------
| Muestra la vista 401 unauthorized
+--------------------------------------------------------------------------
*/

function unauthorized() {
	return show_template('401');
}

/*
+--------------------------------------------------------------------------
| Muestra la vista 404 not found
+--------------------------------------------------------------------------
*/

function not_found() {
	return show_template('404');
}

/*
+--------------------------------------------------------------------------
| Devuelve la url de la pagina anterior
+--------------------------------------------------------------------------
*/

function back() {
    if (isset($_SERVER['HTTP_REFERER'])) {
        $back = $_SERVER['HTTP_REFERER'];
        $back = explode('?', $back);
        $back = '?' . $back[1];
        return $back;
    } else {
        return index_public;
    }
}

/*
|------------------------------------------------------------
| Crea una notificacion de error
|------------------------------------------------------------
*/

function set_notification($type = 'info', $title = 'title', $content = 'content') {
    $_SESSION[temporary] = array(
        'type' => $type,
        'title' => $title,
        'content' => $content
    );
}

/*
|------------------------------------------------------------
| Elimina y obtiene una notificacion de error
|------------------------------------------------------------
*/

function get_notification() {
    if (isset($_SESSION[temporary])) {
        $notification = $_SESSION[temporary];
        unset($_SESSION[temporary]);
    } else {
        $notification = array();
    }
    return $notification;
}
/*
+--------------------------------------------------------------------------
| Verifica si un menu tiene predecesores
+--------------------------------------------------------------------------
*/

function verificar_submenu($menus, $id) {
	foreach ($menus as $menu) {
		if ($menu['antecesor_id'] == $id) {
			return true;
		}
	}
	return false;
}

/*
+--------------------------------------------------------------------------
| Construye el menu
+--------------------------------------------------------------------------
*/

function construir_menu($menus, $antecesor = 0) {
	$html = '';
	foreach ($menus as $menu) {
		if ($menu['antecesor_id'] != null) {
			if ($menu['antecesor_id'] == $antecesor) {
				if (verificar_submenu($menus, $menu['id_menu'])) {
					if ($antecesor == 0) {
						$html .= '<li class="dropdown"><a href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span></span> <span class="hidden-sm">' . escape($menu['menu']) . '</span><span class="glyphicon glyphicon-menu-down visible-xs-inline pull-right"></span></a>';
						$html .= '<ul class="dropdown-menu">';
						$html .= '<li class="dropdown-header visible-sm-block"><span>' . escape($menu['menu']) . '</span></li>';
					} else {
						$html .= '<li class="dropdown-submenu"><a href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span>' . escape($menu['menu']) . '</span><span class="glyphicon glyphicon-menu-down visible-xs-inline pull-right"></span></a>';
						$html .= '<ul class="dropdown-menu">';
					}
					$html .= construir_menu($menus, $menu['id_menu']);
					$html .= '</ul></li>';
				} else {
					if ($antecesor == 0) {
						$html .= '<li><a href="' . (($menu['ruta'] == '') ? '#' : escape($menu['ruta'])) . '"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span class="hidden-sm">' . escape($menu['menu']) . '</span></a></li>';
					} else {
						$html .= '<li><a href="' . (($menu['ruta'] == '') ? '#' : escape($menu['ruta'])) . '"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span>' . escape($menu['menu']) . '</span></a></li>';
					}
				}
			}
		} else {
			$html = '';
			break;
		}
	}
	return $html;
}

/*
+--------------------------------------------------------------------------
| Construye el menu
+--------------------------------------------------------------------------
*/

function construir_navbar($menus, $antecesor = 0) {
	$html = '';
	foreach ($menus as $menu) {
		if ($menu['antecesor_id'] != null) {
			if ($menu['antecesor_id'] == $antecesor) {
				if (verificar_submenu($menus, $menu['id_menu'])) {
					if ($antecesor == 0) {
						$html .= '<li><a href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span class="hidden-sm">' . escape($menu['menu']) . '</span> <span class="glyphicon glyphicon-menu-down visible-xs-inline pull-right"></span></a>';
						$html .= '<ul class="dropdown-menu">';
						$html .= '<li class="dropdown-header visible-sm-block"><span>' . escape($menu['menu']) . '</span></li>';
					} else {
						$html .= '<li class="dropdown-submenu"><a href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span>' . escape($menu['menu']) . '</span> <span class="glyphicon glyphicon-menu-down visible-xs-inline pull-right"></span></a>';
						$html .= '<ul class="dropdown-menu">';
					}
					$html .= construir_navbar($menus, $menu['id_menu']);
					$html .= '</ul></li>';
				} else {
					if ($antecesor == 0) {
						$html .= '<li><a href="' . (($menu['ruta'] == '') ? '#' : $menu['ruta']) . '"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span class="hidden-sm">' . escape($menu['menu']) . '</span></a></li>';
					} else {
						$html .= '<li><a href="' . (($menu['ruta'] == '') ? '#' : $menu['ruta']) . '"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span>' . escape($menu['menu']) . '</span></a></li>';
					}
				}
			}
		} else {
			$html = '';
			break;
		}
	}
	return $html;
}

function construir_sidebar($menus, $antecesor = 0) {
	$html = '';
	foreach ($menus as $menu) {
		if ($menu['antecesor_id'] != null) {
			if ($menu['antecesor_id'] == $antecesor) {
				if (verificar_submenu($menus, $menu['id_menu'])) {
					$html .= '<li><a href="#" class="text-truncate pull-right-container"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span>' . escape($menu['menu']) . '</span> <span class="glyphicon glyphicon-menu-right pull-right"></span></a><ul class="nav sidebar-nav animated fadeIn">' . construir_sidebar($menus, $menu['id_menu']) . '</ul></li>';
				} else {
					$html .= '<li><a href="' . (($menu['ruta'] == '') ? '#' : $menu['ruta']) . '" class="text-truncate"><span class="glyphicon glyphicon-' . escape($menu['icono']) . '"></span> <span>' . escape($menu['menu']) . '</span></a></li>';
				}
			}
		} else {
			$html = '';
			break;
		}
	}
	return $html;
}

/*
+--------------------------------------------------------------------------
| Devuelve el menu ordenado
+--------------------------------------------------------------------------
*/

function ordenar_menu($menus, $antecesor = 0, $lista = array()) {
	foreach ($menus as $menu) {
		if ($menu['antecesor_id'] == $antecesor) {
			if (verificar_submenu($menus, $menu['id_menu'])) {
				$menu['antecesor'] = 1;
				array_push($lista, $menu);
				$lista = ordenar_menu($menus, $menu['id_menu'], $lista);
			} else {
				$menu['antecesor'] = 0;
				array_push($lista, $menu);
			}
		}
	}
	return $lista;
}

/*
+--------------------------------------------------------------------------
| Devuelve un array con los directorios de una ubicacion
+--------------------------------------------------------------------------
*/

function get_directories($route) {
	if (is_dir($route)) {
		$array_directories = array();
		$directories = opendir($route);
		while ($directory = readdir($directories)) {
			if ($directory != '.' && $directory != '..' && is_dir($route . '/' . $directory)) {
				//$array_directories[] = $directory;
				array_push($array_directories, $directory);
			}
		}
		closedir($directories);
		return $array_directories;
	} else {
		return false;
	}
}

/*
+--------------------------------------------------------------------------
| Devuelve un array con los archivos de un directorio
+--------------------------------------------------------------------------
*/

function get_files($route) {
	if (is_dir($route)) {
		$array_files = array();
		$files = opendir($route);
		while ($file = readdir($files)) {
			if ($file != '.' && $file != '..' && !is_dir($route . '/' . $file)) {
				$extention = substr($file, -4);
				$file = substr($file, 0, -4);
				if ($file != 'index' && $extention == '.php') {
					$array_files[] = $file;
				}
			}
		}
		closedir($files);
		return $array_files;
	} else {
		return false;
	}
}

/*
+--------------------------------------------------------------------------
| Crea un archivo
+--------------------------------------------------------------------------
*/

function file_create($route) {
	if (!file_exists($route)) {
		$file = fopen($route, 'x');
		fclose($file);
	}
}

/*
+--------------------------------------------------------------------------
| Elimina un archivo
+--------------------------------------------------------------------------
*/

function file_delete($route) {
	if (file_exists($route)) {
		unlink($route);
	}
}

/*
|------------------------------------------------------------
| Retorna un texto con los espacios limpios
|------------------------------------------------------------
*/

function clear($text) {
	$text = preg_replace('/\s+/', ' ', $text);
	$text = trim($text);
	$text = addslashes($text);
	return $text;
}

/*
|------------------------------------------------------------
| Retorna un texto convertido en mayusculas
|------------------------------------------------------------
*/

function upper($text) {
	$text = mb_strtoupper($text, 'UTF-8');
	return $text;
}

/*
|------------------------------------------------------------
| Retorna un texto convertido en minusculas
|------------------------------------------------------------
*/

function lower($text) {
	$text = mb_strtolower($text, 'UTF-8');
	return $text;
}

/*
|------------------------------------------------------------
| Retorna un texto convertido en minusculas excepto la primera
|------------------------------------------------------------
*/

function capitalize($text) {
	$text = upper(mb_substr($text, 0, 1, 'UTF-8')) . lower(mb_substr($text, 1, mb_strlen($text), 'UTF-8'));
	return $text;
}

/*
+--------------------------------------------------------------------------
| Devuelve un string con caracteres aleatorios
+--------------------------------------------------------------------------
*/

function random_string($length = 6) {
	$text = '';
	$characters = '0123456789abcdefghijkmnopqrstuvwxyz';
	$nro = 0;
	while ($nro < $length) {
		$caracter = substr($characters, mt_rand(0, strlen($characters)-1), 1);
		$text .= $caracter;
		$nro++;
	}
	return $text;
}

/*
+--------------------------------------------------------------------------
| devuelve la cantidad de unidades de un producto
+--------------------------------------------------------------------------
*/

function cantidad_unidad($db, $id, $unidad){
    $producto = $db->select('unidad_id')->from('inv_productos')->where('id_producto',$id)->fetch_first();
    if($producto['unidad_id']!=$unidad){
        $otra_unidad = $db->select('cantidad_unidad')->from('inv_asignaciones')->where(array('unidad_id' => $unidad, 'producto_id' => $id, 'visible' => 's'))->fetch_first();
        return $otra_unidad['cantidad_unidad'];
    }else{
        return 1;
    }
}

/*
+--------------------------------------------------------------------------
| devuelve la unidad de un producto
+--------------------------------------------------------------------------
*/

function nombre_unidad($db, $id_unidad){
    $unidad = $db->select('unidad')->from('inv_unidades')->where('id_unidad',$id_unidad)->fetch_first();
    if($unidad){
        return $unidad['unidad'];
    }else{
        return 'SIN UNIDAD';
    }
}

/*
+--------------------------------------------------------------------------
| devuelve el precio de la unidad del producto
+--------------------------------------------------------------------------
*/

function precio_unidad($db, $id, $unidad){
    $producto = $db->select('unidad_id, precio_actual')->from('inv_productos')->where('id_producto',$id)->fetch_first();
    if($producto['unidad_id']!=$unidad){
        $otra_unidad = $db->select('cantidad_unidad, otro_precio')->from('inv_asignaciones')->where(array('unidad_id' => $unidad, 'producto_id' => $id, 'visible' => 's'))->fetch_first();
        return $otra_unidad['otro_precio'];
    }else{
        return $producto['precio_actual'];
    }
}

/*
+--------------------------------------------------------------------------
| devuelve si esta dentro de una coordenada
+--------------------------------------------------------------------------
*/

function pointInPolygon($point, $polygon, $pointOnVertex = true) {
    $this->pointOnVertex = $pointOnVertex;

    // Transformar la cadena de coordenadas en matrices con valores "x" e "y"
    $point = $this->pointStringToCoordinates($point);
    $vertices = array();
    foreach ($polygon as $vertex) {
        $vertices[] = $this->pointStringToCoordinates($vertex);
    }

    // Checar si el punto se encuentra exactamente en un vértice
    if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
        return "vertex";
    }

    // Checar si el punto está adentro del poligono o en el borde
    $intersections = 0;
    $vertices_count = count($vertices);

    for ($i=1; $i < $vertices_count; $i++) {
        $vertex1 = $vertices[$i-1];
        $vertex2 = $vertices[$i];
        if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Checar si el punto está en un segmento horizontal
            return "boundary";
        }
        if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
            $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
            if ($xinters == $point['x']) { // Checar si el punto está en un segmento (otro que horizontal)
                return "boundary";
            }
            if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                $intersections++;
            }
        }
    }
    // Si el número de intersecciones es impar, el punto está dentro del poligono.
    if ($intersections % 2 != 0) {
        return "dentro";
    } else {
        return "fuera";
    }
}

function pointOnVertex($point, $vertices) {
    foreach($vertices as $vertex) {
        if ($point == $vertex) {
            return true;
        }
    }

}

function pointStringToCoordinates($pointString) {
    $coordinates = explode(" ", $pointString);
    return array("x" => $coordinates[0], "y" => $coordinates[1]);
}

/*
+--------------------------------------------------------------------------
| devuelve el precio de un producto
+--------------------------------------------------------------------------
*/

function loteProducto($db, $id_producto, $id_almacen) {
    $egreso = $db->query("SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
		FROM inv_egresos e
		LEFT JOIN inv_egresos_detalles d ON e.id_egreso = d.egreso_id
		WHERE e.almacen_id = '$id_almacen' AND d.producto_id = '$id_producto'
		GROUP BY d.producto_id")->fetch_first();
    $ingresos = $db->query("SELECT * FROM inv_ingresos a LEFT JOIN inv_ingresos_detalles b ON a.id_ingreso = b.ingreso_id WHERE a.almacen_id = '$id_almacen' AND b.producto_id = '$id_producto'")->fetch();
    $sum = 0;
    $aux = array();
    foreach($ingresos as $ingreso){
        if($sum < $egreso['cantidad_egresos']){
            $aux = $ingreso;
            $sum = $sum + $egreso['cantidad_egresos'];
        }
    }
    return $aux;
}

/*
+--------------------------------------------------------------------------
| utilidad por vendedor
+--------------------------------------------------------------------------
*/

function utilidadvendedor($db, $fecha_inicial, $fecha_final, $vendedor)
{
    $costoSSST = 0;
    $importeSSST = 0;

    $query = "SELECT *, cantidad AS cantidadAcumul, precio*cantidad/IF(a.cantidad_unidad IS NULL, 1, a.cantidad_unidad) AS importeAcumul
            FROM inv_productos p
            INNER JOIN inv_egresos_detalles vd ON vd.producto_id = p.id_producto
            INNER JOIN inv_egresos v ON vd.egreso_id=v.id_egreso
            LEFT JOIN inv_asignaciones a ON a.producto_id = vd.producto_id AND a.unidad_id = vd.unidad_id
            LEFT JOIN inv_unidades u ON u.id_unidad = vd.unidad_id
            WHERE v.fecha_egreso >= '$fecha_inicial' and v.fecha_egreso <= '$fecha_final' AND v.tipo='Venta' AND a.visible = 's' 
            ";
    $ventas = $db->query($query)->fetch();

    $costoTotalAcumulado = 0;
    $precioTotalAcumulado = 0;
    $utilidadTotalAcumulado = 0;

    $total = 0;
    $total_utilidad = 0;
    foreach ($ventas as $nro => $venta) {

        $cantidadTotal = escape($venta['cantidadAcumul']);
        $precio = escape($venta['precio']);
        $importeTotal = escape($venta['importeAcumul']);
        $total = $total + $importeTotal;

        // Obtiene las ventas y salidas anteriores a la fecha inicial
        $cantidad_ventas = 0;
        $query = "SELECT SUM(cantidad)as cantidadAnterior ";
        $query .= " FROM inv_egresos_detalles vd ";
        $query .= " INNER JOIN inv_egresos v ON (egreso_id=id_egreso) ";
        $query .= " LEFT JOIN inv_asignaciones a ON a.producto_id = vd.producto_id AND a.unidad_id = vd.unidad_id ";
        $query .= " LEFT JOIN inv_unidades u ON u.id_unidad=vd.unidad_id ";
        $query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_egreso < '$fecha_inicial' AND a.visible = 's' ";

        $vAntiguos = $db->query($query)->fetch();
        foreach ($vAntiguos as $nro2 => $vAntiguo) {
            $cantidad_ventas = escape($vAntiguo['cantidadAnterior']);
        }

        $costo = 0;
        $costoTotal = 0;
        $tamanio = 0;
        $unidad = "";
        $prodIngresados = 0;
        $saldo = 0;
        $prodAc = 0;                        //
        $ingresoSW = true;                //se termino de obtener los costos

        $ultimoSaldo = 0;
        $ultimoTamanio = 0;
        $ultimoCosto = 0;
        $ultimaUnidad = "";
        $nrocompras = 0;

        //se obtiene las compras desde inicio de la empresa hasta la fecha limite solicitada por el usuario
        $query = "SELECT  *, 1 as tamanio, u.unidad ";
        $query .= " FROM inv_ingresos_detalles vd ";
        $query .= " INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
        $query .= " INNER JOIN inv_productos p ON p.id_producto=vd.producto_id ";

        $query .= " INNER JOIN inv_unidades u ON u.id_unidad=p.unidad_id ";
        $query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' ";
        //$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_ingreso <= '$fecha_final' ";
        $query .= " ORDER BY fecha_ingreso, u.tamanio, u.unidad ";
        $iAntiguos = $db->query($query)->fetch();
        foreach ($iAntiguos as $nro3 => $iAntiguo) {
            $prodIngresados = $prodIngresados + ($iAntiguo['cantidad'] * $iAntiguo['tamanio']);
            //se compara los productos previamente vendidos y costos antiguos
            //para obtener la utilidad de los ultimos productos comprados VS los productos vendidos.
            if ($prodIngresados > $cantidad_ventas AND $ingresoSW) {
                //verificar si es el primer Ingreso
                if ($saldo > 0) {
                    $saldo = $prodIngresados - $cantidad_ventas;
                } else {
                    $saldo = $iAntiguo['cantidad'] * $iAntiguo['tamanio'];
                }

                if ($prodAc + $saldo <= $cantidadTotal) {
                    $saldo = $saldo;
                } else {
                    $saldo = $cantidadTotal - $prodAc;
                    $ingresoSW = false;
                }

                $prodAc = $prodAc + $saldo;

                $costoTotal += $saldo * ($iAntiguo['costo'] / $iAntiguo['tamanio']);
                $costo = $iAntiguo['costo'];
                $tamanio = $iAntiguo['tamanio'];
                $unidad = $iAntiguo['unidad'];

                //verificar si hay un nuevo Costo
                if (($ultimoCosto != $costo && $ultimoCosto != 0) || ($ultimaUnidad != "" && $ultimaUnidad != $unidad)) {
                    $subtotal = $ultimoCosto * $ultimoSaldo / $ultimoTamanio;
                    $subtotal = number_format($subtotal, 2, ".", " ");
                    $ultimoSaldo = Fracciones2($ultimoSaldo, $ultimoTamanio);
                    $ultimoSaldo = $saldo;
                    $ultimoCosto = $costo;
                    $ultimaUnidad = $unidad;
                    $ultimoTamanio = $tamanio;
                    $nrocompras++;
                } else {
                    $ultimoSaldo += $saldo;
                    $ultimoCosto = $costo;
                    $ultimaUnidad = $unidad;
                    $ultimoTamanio = $tamanio;
                }

            }
        }

        if ($ultimoSaldo != 0) {
            $subtotal = $ultimoCosto * $ultimoSaldo / $ultimoTamanio;
            $subtotal = number_format($subtotal, 2, ".", " ");
            $ultimoSaldo = Fracciones2($ultimoSaldo, $ultimoTamanio);
        }

        $swCostoEstimado = false;

        if ($cantidadTotal > $prodAc) {
            $query = "SELECT  costo, u.unidad ";
            $query .= " FROM inv_ingresos_detalles vd ";
            $query .= " INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
            $query .= " INNER JOIN inv_productos p ON p.id_producto=vd.producto_id ";
            $query .= " INNER JOIN inv_unidades u ON u.id_unidad=p.unidad_id ";
            $query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_ingreso <= '$fecha_final' ";
            $query .= " ORDER BY fecha_ingreso DESC ";
            $iUltimo = $db->query($query)->fetch_first();

            if ($iUltimo) {
                $ultimoSaldo = $cantidadTotal - $saldo;
                $ultimaUnidad = $iUltimo['unidad'];
                $ultimoCosto = $iUltimo['costo'];
                $subtotal = $ultimoSaldo * $ultimoCosto;

                $swCostoEstimado = true;

                $costoTotal += $subtotal;
            } else {
                $query = "SELECT  costo, u.unidad, 1 as tamanio ";
                $query .= " FROM inv_ingresos_detalles vd ";
                $query .= " INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";
                $query .= " INNER JOIN inv_productos p ON p.id_producto=vd.producto_id ";
                $query .= " INNER JOIN inv_unidades u ON u.id_unidad=p.unidad_id ";
                $query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_ingreso <= '$fecha_final' ";
                $query .= " ORDER BY fecha_ingreso DESC ";
                $iUltimo = $db->query($query)->fetch_first();

                $ultimoSaldo = $cantidadTotal - $saldo;
                $ultimaUnidad = $iUltimo['unidad'];
                $ultimoCosto = $iUltimo['costo'];
                $subtotal = $ultimoSaldo * ($ultimoCosto / $iUltimo['tamanio']);

                $ultimoSaldo = Fracciones2($ultimoSaldo, $iUltimo['tamanio']);


                $swCostoEstimado = true;

                $costoTotal += $subtotal;
            }
        }

        if ($swCostoEstimado) {
        }//Los detalle de compra no se muestran

        $detalle = "";
        //Listar los diferentes precios a los que se a 		vendido un producto		o salio un producto
        $query = "SELECT u.unidad, u.tamanio,  precio, SUM(cantidad) as cantidadXprecio ";
        $query .= " FROM inv_egresos_detalles vd ";
        $query .= " LEFT JOIN inv_egresos v ON (egreso_id=id_egreso) ";

        $query .= " LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
        $query .= " LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";

        $query .= " WHERE vd.producto_id='" . $venta['id_producto'] . "' AND v.fecha_egreso >= '$fecha_inicial' and v.fecha_egreso <= '$fecha_final' AND v.tipo='venta' AND a.visible = 's' ";
        $query .= " GROUP BY precio, u.tamanio, unidad ";

        $vventas = $db->query($query)->fetch();
        $nroventas = 0;
        foreach ($vventas as $nro3 => $vventa) {
            $nroventas++;
            $subtotal = $vventa['precio'] * $vventa['cantidadXprecio'];
        }
        if($venta['empleado_id'] == $vendedor && $venta['estadoe'] == 3){
            $total_utilidad = $total_utilidad + ($importeTotal - $costoTotal);
        }
    }
    return $total_utilidad;
}
function Fracciones2($ultimoSaldo,$ultimoTamanio){
    $str="";
    if($ultimoTamanio!=1){
        $modulo=$ultimoSaldo%$ultimoTamanio;
        $entero=($ultimoSaldo-$modulo)/$ultimoTamanio;

        if($entero!=0){
            $str.=$entero;
        }
        if($entero!=0 && $modulo!=0){
            $str.=" ";
        }
        if($modulo!=0){
            $str.="<span>".$modulo." / ".$ultimoTamanio."</span>";
        }
    }
    else{
        $str=$ultimoSaldo;
    }
    return $str;
}
//CONTABILIDAD
//1 -> Ventas Computarizadas
//2 -> Ventas manuales
//3 -> Notas de remisión
//4 -> Egresos
//5 -> Ingresos
//6 -> Preventas
//7 -> Importacion
//7 -> Importacion Pago
function Contabilidad($db,$tipo,$almacen_id,$id_proceso,$monto_total,$menu,$Usuario){
	$almacen=$db->query("SELECT * FROM inv_almacenes WHERE id_almacen='{$almacen_id}'")->fetch_first();
	$auto=$db->query("SELECT * FROM con_asientos_menus a
					LEFT JOIN con_asientos_automaticos b ON a.automatico_id = b.id_automatico
					WHERE b.estado='si' AND a.menu_id={$menu}")->fetch_first();
	if($auto){
	    $accion='';
    	switch($menu):
    		case 1:
    			$accion='Ventas Computarizadas';
    		break;
    		case 2:
    			$accion='Ventas manuales';
    		break;
    		case 3:
    			$accion='Notas de remisión';
    		break;
    		case 4:
    			$accion='Egresos';
    		break;
    		case 5:
    			$accion='Ingresos';
    		break;
    		case 6:
    			$accion='Preventas';
    		break;
    		case 7:
    			$accion='Importacion';
    		break;
    		case 8:
    			$accion='Importacion Pago';
    		break;
    	endswitch;
    	$nome=$db->query("SELECT * FROM con_tipo_moneda WHERE id_moneda=2")->fetch_first();//Sacar la Conabilidad En Dolar
    	$comp = $db->select('codigo')->from('con_comprobante')->order_by('codigo','desc')->fetch_first();
    	$data = array(
    			'codigo'=> $comp['codigo'] + 1,
    			'tipo'  => $tipo,
    			'glosa' => "Venta de productos con  nota de venta ({$accion}) del almacen {$almacen['almacen']} por el usuario {$Usuario}",
    			'fecha' => date('Y-m-d'),
    			'dolar' => $nome['valor'],
    			'operacion_id' => $id_proceso
    		);
    	$idd=$db->insert('con_comprobante', $data);
    	$id_automatico=$auto['id_automatico'];
    	////////////////////////////////////////////////////////////////////////////////////
    	$debe =$db->query("SELECT*FROM con_detalles_automaticos a WHERE a.automatico_id={$id_automatico} AND a.tipo=1")->fetch();
    	foreach($debe as $deb){
    		$a = $deb['plan_id'];
    		$c = $deb['porcentaje']/100;
    		$c = $c * $monto_total;
    		if ($c != 0) {
    			$data2 = array(
    				'cuenta' => $a,
    				'debe' => $c,
    				'haber' => 0,
    				'comprobante' => $idd
    			);
    			$db->insert('con_asiento', $data2);
    		}
    	}
    	$haber=$db->query("SELECT*FROM con_detalles_automaticos a WHERE a.automatico_id={$id_automatico} AND a.tipo=2")->fetch();
    	foreach($haber as $habe){
    		$a=$habe['plan_id'];
    		$c=$habe['porcentaje']/100;
    		$c=$c * $monto_total;
    		if ($c != 0) {
    			$data2 = array(
    				'cuenta' => $a,
    				'debe' => 0,
    				'haber' => $c,
    				'comprobante' => $idd
    			);
    			$db->insert('con_asiento', $data2);
    		}
    	}
	}/////////////////////////////////////////////////////////////////////////////////
}

/*
+--------------------------------------------------------------------------
| INSERTA EN TABLA BACKUP REGISTROS ANTES DE SER ELIMINADOS 
+--------------------------------------------------------------------------
*/
function backup_registros($db, $tabla, $campo, $id, $campo_aux = '', $id_aux = '', $id_empleado_reset, $principal = 'NO', $id_principal = 0, $accion = "Backup"){
    $cant_insertada = 0;
    $registros = array();
    $id_registrado = 0;

    //validar condiciones auxiliares
    if ($campo_aux == '' || $campo_aux == null || $id_aux == 0 || $id_aux == null) {        
        $registros = $db->select('*')->from($tabla)->where($campo, $id)->fetch();
    }else{
      $condicion = array(  $campo => $id);
      if (($campo_aux && $id_aux) && (strlen($campo_aux) > 0 && strlen($id_aux) > 0) && $campo_aux != '' && $campo_aux != null && $id_aux != 0 && $id_aux != null ) {
        $condicion[$campo_aux] = $id_aux;
           $registros = $db->select('*')->from($tabla)->where($condicion)->fetch();
      }
    }

    //validamos que exita registros
    if (count($registros) > 0) {        
        foreach ($registros as $key => $value) {              
            $registros[$key]['delet_fecha_egreso'] = date("Y-m-d");
            $registros[$key]['delet_hora_egreso'] = date("H:i:s");
            $registros[$key]['delet_empleado_id'] = ($id_empleado_reset) ? $id_empleado_reset : 0;
            
            //valida si es tabla secundaria para insertar el id recibido de la tabla princippal
            if ($principal == 'NO') {
                $registros[$key]['accion_id_backup'] = ($id_principal) ? $id_principal : 0;                    
            }
            $registros[$key]['accion_backup'] = ($accion) ? $accion : 'Backup';                    
            
            $id_registrado = $db->insert('backup_' . $tabla, $registros[$key]);
            $cant_insertada ++;
            $id_registrado = ($principal == 'SI') ? $id_registrado : $cant_insertada; 
        }
    }

    //validamos cantidad de registros insertados
    if($id_registrado > 0){        
        return $id_registrado;
    }else{
        return 0;
    } 
    return true;
}

/*
+--------------------------------------------------------------------------
| validar si es posible el cambio de unidad-tamanio
+--------------------------------------------------------------------------
*/
function validar_cambio_unidad($db, $tabla, $id_detalle, $cantidad, $unidad_id){
    //se obtien el detalle
    $detalle = $db->from($tabla)->where('id_detalle', $id_detalle)->fetch_first();   
    
    //se prepara cantidad actual
    $datos = array('producto_id' => $detalle['producto_id'], 
                    'unidad_id' => $detalle['unidad_id'],
                    'visible' => 's');

    $cantidad_unidad_actual = $db->from('inv_asignaciones')->where($datos)->fetch_first();
    $cantidad_total_actual = $detalle['cantidad'];      
    
    //se prepara cantidad nueva
    $datos1 = array('producto_id' => $detalle['producto_id'], 
                    'unidad_id' => $unidad_id,
                    'visible' => 's');
    
    $cantidad_nueva = $db->select('*')->from('inv_asignaciones')->where($datos1)->fetch_first();
    
    $cantidad_total_nueva = ($cantidad_nueva) ? $cantidad * $cantidad_nueva['cantidad_unidad'] : $cantidad;
   

    if ($cantidad_total_nueva <= $cantidad_total_actual) {
        return true;
    }else {
        return false;
    }
}


/*
+--------------------------------------------------------------------------
| validar si es posible el registro de una salida de inventario
+--------------------------------------------------------------------------
*/
function validar_stock($db, $id_productos = array(), $cantidades = array(), $unidades = array(), $id_almacen = 0){

    $datos = array();

    //Validar que el array tenga mas de un elemento
    if (count($id_productos) > 0) {
        
        //iterar los productos a validar el stock
        foreach ($id_productos as $key => $value) {
    
            //se obtiene el stock
            $consulta_stock = $db->query("SELECT p.id_producto, p.codigo, p.nombre_factura, 
                        p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, 
                        (IFNULL(s.cantidad_egresos, 0)) AS cantidad_egresos
                        FROM inv_productos p
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
                        FROM inv_ingresos_detalles d
                        LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
                        WHERE transitorio = 0 AND i.almacen_id = '{$id_almacen}' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
                        FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                        WHERE e.almacen_id = '{$id_almacen}' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto    
                        WHERE ('2021-10-04'<=p.fecha_limite OR p.fecha_limite='1000-01-01') AND eliminado = 0 
                        AND p.id_producto = '{$id_productos[$key]}' ")->fetch_first();
            
            if (count($consulta_stock) > 0) {
                
                //obtener id de unidad
                $id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidades[$key])->fetch_first()['id_unidad'];
                
                //obtiene stock de producto
                $stock = (($consulta_stock['cantidad_ingresos'] - $consulta_stock['cantidad_egresos']) > 0) ? $consulta_stock['cantidad_ingresos'] - $consulta_stock['cantidad_egresos'] : 0;
                
                 if (count($id_unidad) > 0) {
                    
                    //obtiene stock en unidad(1)
                    $cantidad_egresar = $cantidades[$key] / cantidad_unidad($db, $id_productos[$key], $id_unidad);
                    
                    // se valida que el stock es o menor a la cantidad solicitada
                    if (($stock < $cantidad_egresar) || !$stock || !$cantidad_egresar || $stock <= 0 || $cantidad_egresar <= 0 ) {
                        //se prepara respuesta 
                        $datos[] = array('id_producto' => $consulta_stock['id_producto'], 
                                            'codigo' => $consulta_stock['id_producto'],
                                            'producto' => $consulta_stock['nombre_factura'],
                                            'stock' => $stock,
                                            'stock_solicitado' => $cantidad_egresar);
                    }
                }
            }
        }
    }

    return (count($datos)> 0) ? $datos : array();
}


/*
+--------------------------------------------------------------------------
| preparar mensaje  de productos observados
+--------------------------------------------------------------------------
*/
function preparar_mensaje($productos = array()){

    $datos = array();
    $menssage = "";
    
    //Validar que el array tenga mas de un elemento
    if (count($productos) > 0) {
                       
        //se  prepara el mensaje con los productos observados
        $menssage .= "<br><ol>";
        //iteramos los productos observados
        foreach ($productos as $key => $value) {
            $menssage .= "<li>Codigo: " . $value['codigo'] . " | Producto: " . $value['producto'] . " | Stock: " . $value['stock'] . " | Stock solicitado: " . $value['stock_solicitado'] ." </li>";
        }
        $menssage .= "<ol>";
        $menssage .= "<br> <b class='text-uppercase'>Favor actualizar y/o modificar el stock de los productos observados. disculpe las molestias.</b>";            
    }

    return ($menssage != "" && $menssage != null) ? $menssage : "";
}


/*
+--------------------------------------------------------------------------
| Validar tipo de plan obtenido
+--------------------------------------------------------------------------
*/
function validar_plan($db, $datos_plan = array()){

    $datos = array();
    $plan = $datos_plan["plan"];
    $caracteristica = $datos_plan["caracteristica"];

    //obtiene datos del plan
    $limite = $db->query("SELECT sd.limite FROM sys_planes sp                           
                            LEFT JOIN sys_planes_detalles sd on sp.id_plan = sd.plan_id                     
                            LEFT JOIN sys_planes_caracteristicas sc on sc.id_caracteristica = sd.caracteristica_id 
                            WHERE sp.estado = 'Activo' AND sp.plan = '{$plan}' AND sc.caracteristicas = '{$caracteristica}'")->fetch_first()['limite'];
    
    return ($limite != "" && $limite != null && $limite >= 0) ? $limite : 10;
}

/*
+--------------------------------------------------------------------------
| Validar unico registro del cliente insertado en la jornada 
+--------------------------------------------------------------------------
*/
function validar_registro_cliente($db, $campo = '', $id = '', $campo_aux = '', $id_aux = ''){ 

    $hoy = date('Y-m-d');
    if ($campo_aux == '' || $campo_aux == null || $id_aux == 0 || $id_aux == null) {        
        $registros = $db->select('*')->from('inv_egresos')->where($campo, $id)->where('fecha_egreso', $hoy)->where('estadoe!=', 0)->fetch();
    }else{
      $condicion = array( $campo => $id);
      if (($campo_aux && $campo) && (strlen($campo_aux) > 0 && strlen($campo) > 0) 
            && $campo_aux != '' && $campo_aux != null && $id != '' && $id != null && $id_aux != null ) {
        $condicion[$campo] = $id;
        $condicion[$campo_aux] = ($id_aux >= 0) ? $id_aux: 0;
        $condicion['fecha_egreso'] = $hoy;
           $registros = $db->select('*')->from('inv_egresos')->where($condicion)->fetch();
      }
    }    
    return (count($registros) > 0) ? false : true;
}


/*
+--------------------------------------------------------------------------
| validar stock de productos devueltos
+--------------------------------------------------------------------------
*/
function validar_stock_devueltos($db, $id_producto = 0, $cantidad = 0, $unidad = 0, $id_distribuidor = 0){

    $respuesta = false;

    //Validar que el array tenga mas de un elemento
    if ($id_producto && $cantidad && $unidad && $id_distribuidor) {
            
        //se obtiene el stock
        $consulta_stock = $db->query("SELECT B.producto_id, ROUND((IFNULL(B.total_devuelto, 0) - IFNULL(C.total_venta_directa, 0)), 2) AS total_entrega FROM 
                        (SELECT ed.producto_id, IFNULL(SUM((ed.cantidad/(IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)))), 0) AS total_devuelto
                        FROM tmp_egresos_detalles AS ed
                        LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id
                        LEFT JOIN tmp_egresos AS e ON ed.tmp_egreso_id = e.id_tmp_egreso
                        WHERE ed.producto_id = '{$id_producto}'
                        AND e.distribuidor_estado NOT IN ('ENTREGA', 'VENTA') 
                        AND e.estado = 3  AND e.distribuidor_id = '{$id_distribuidor}' AND e.anulado = 0 AND asi.visible = 's' 
                        AND ed.promocion_id != 1) B 
                                                
                        LEFT JOIN (SELECT ed.producto_id, IFNULL(SUM((ed.cantidad/(IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)))), 0) AS total_venta_directa
                        FROM tmp_egresos_detalles AS ed
                        LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id
                        LEFT JOIN tmp_egresos AS e ON ed.tmp_egreso_id = e.id_tmp_egreso
                        WHERE ed.producto_id = '{$id_producto}' AND e.distribuidor_id = '{$id_distribuidor}'
                        AND e.distribuidor_estado IN ('VENTA') AND asi.visible = 's' 
                        AND ed.promocion_id != 1 AND e.anulado = 0) C ON C.producto_id = B.producto_id ")->fetch_first()['total_entrega'];


        if ($consulta_stock > 0 && $consulta_stock != null) {
    
            //obtener id de unidad
            $id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('id_unidad', $unidad)->fetch_first()['id_unidad'];
            
            //obtiene stock de producto
            $stock = ($consulta_stock > 0 && $consulta_stock != null) ? $consulta_stock: 0;
            
            if ($id_unidad > 0 && $id_unidad != null) {
                
                //obtiene stock en unidad(1)
                $cantidad_egresar = $cantidad * cantidad_unidad($db, $id_producto, $id_unidad);
                
                // se valida que el stock es o menor a la cantidad solicitada
                $respuesta = (($stock < $cantidad_egresar) || !$stock || !$cantidad_egresar || $stock <= 0 || $cantidad_egresar <= 0 ) ? false : true;
            }
        }
        
    }
    return $respuesta;
}

/*
+--------------------------------------------------------------------------
| Validar unico registro del cliente insertado en la jornada 
+--------------------------------------------------------------------------
*/
function validar_atributo($db, $plan = '', $modulo = '', $archivo = '', $atributo = ''){ 
    
    $respuesta = false;

    //valida los datos recibidos
    if (($plan != '' && $plan != null) && 
    ($modulo != '' && $modulo != null) && 
    ($archivo != '' && $archivo != null) && 
    ($atributo != '' && $atributo != null)) {    

        //obtiene el plan, modulo archivo y atributo 
        $registros = $db->query("SELECT sp.plan, sp.estado, spg.modulo, spcd.archivo, spa.atributo FROM sys_planes sp
                            LEFT JOIN sys_planes_config spg ON sp.id_plan = spg.plan_id
                            LEFT JOIN sys_planes_config_detalles spcd ON spcd.config_id = spg.id_config
                            LEFT JOIN sys_planes_atributos spa ON spa.id_atributo = spcd.atributo_id
                            WHERE sp.plan = '{$plan}' AND sp.estado = 'Activo' AND spg.modulo = '{$modulo}' 
                            AND spcd.archivo = '{$archivo}' AND spa.atributo = '{$atributo}'
                            AND spa.estado = 'Visible'")->fetch_first();
        
        // selavlida que el atributo obtenido sea igual al recibido
        $respuesta = ($registros['atributo'] == $atributo) ? true: false;
    }  
    return $respuesta;
}

/*
+--------------------------------------------------------------------------
| INSERTA EN TABLA DE ACUERDO A CADA ACCION REALIZADA POR EL PREVENTISTA O  DISTRIBUIDOR 
+--------------------------------------------------------------------------
*/

function registros_historial($db, $accion, $tabla, $campo, $id, $campo_aux = '', $id_aux = '', $id_empleado_accion = 0, $principal = 'NO', $id_principal = 0){
    $cant_insertada = 0;
    $registros = array();
    $id_registrado = 0;
    
    //validar campos requeridos
    if ($db && $accion && $tabla && $campo && $id) {        
        //validar condiciones auxiliares
        if ($campo_aux == '' || $campo_aux == null || $id_aux == 0 || $id_aux == null) {        
            $registros = $db->select('*')->from($tabla)->where($campo, $id)->fetch();
        }else{
            $condicion = array(	$campo => $id);
            if (($campo_aux && $id_aux) && (strlen($campo_aux) > 0 && strlen($id_aux) > 0) && $campo_aux != '' && $campo_aux != null && $id_aux != 0 && $id_aux != null ) {
                $condicion[$campo_aux] = $id_aux;
                $registros = $db->select('*')->from($tabla)->where($condicion)->fetch();
            }
        }

        //validamos que exista registros
        if (count($registros) > 0) {        
            foreach ($registros as $key => $value) {              
                $registros[$key]['fecha_registro'] = date("Y-m-d");
                $registros[$key]['hora_registro'] = date("H:i:s");
                $registros[$key]['empleado_id_accion'] = ($id_empleado_accion) ? $id_empleado_accion : 0;

                //valida si es tabla secundaria para insertar el id recibido de la tabla princippal
                if ($principal == 'NO') {
                    $registros[$key]['accion_id'] = ($id_principal) ? $id_principal : 0;                    
                }

                $id_registrado = $db->insert($tabla . $accion, $registros[$key]);
                $cant_insertada ++;
                $id_registrado = ($principal == 'SI') ? $id_registrado : $cant_insertada; 
            }
        } 
    }

    //validamos cantidad de registros insertados
  if($id_registrado > 0){        
        return $id_registrado;
    }else{
        return 0;
    }  
    //return true;
}



/*
+--------------------------------------------------------------------------
| OBTENER TOTAL DE MOVIMIENTO
+--------------------------------------------------------------------------
*/

function obtener_total($db, $cadena_ids = 0){
    $monto_total = 0;
    $subtotal = 0;   

        //validamos que exista registros
        if ($db && $cadena_ids != 0 && $cadena_ids != '') {      
            
            $monto_total = $db->query("SELECT 
                                ROUND(IFNULL(SUM(d.precio * (d.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS precio_total
                                FROM inv_egresos_detalles d 
                                LEFT JOIN inv_asignaciones a ON a.producto_id = d.producto_id AND a.unidad_id = d.unidad_id
                                LEFT JOIN inv_unidades u ON u.id_unidad = d.unidad_id            
                                WHERE d.egreso_id IN ({$cadena_ids}) AND a.visible = 's' ")->fetch_first();

        } 

    //validamos cantidad de registros insertados
  if($monto_total > 0){        
        return $monto_total;
    }else{
        return 0;
    }  
    //return true;
}




/*
+--------------------------------------------------------------------------
| validar si es posible el registro de una salida de inventario
+--------------------------------------------------------------------------
*/
function validar_stock_edicion_egreso($db, $id_productos = array(), $cantidades = array(), $unidades = array(), $id_egreso = 0, $id_almacen = 0){

    $datos = array();
    $id_productos_obs = array();
    $cantidades_obs = array();
    $unidades_obs = array();

    //Validar que el array tenga mas de un elemento
    if ($id_egreso > 0) {        
             
        //iterar los productos a validar el stock
        foreach ($id_productos as $key => $value) {
    
            //se obtiene el stock
            $consulta_stock = $db->query("SELECT p.id_producto, p.codigo, p.nombre_factura, 
                        p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, 
                        (IFNULL(s.cantidad_egresos, 0)) AS cantidad_egresos
                        FROM inv_productos p
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
                        FROM inv_ingresos_detalles d
                        LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
                        WHERE transitorio = 0 AND i.almacen_id = '{$id_almacen}' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
                        FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                        WHERE e.almacen_id = '{$id_almacen}' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto    
                        WHERE ('2021-10-04'<=p.fecha_limite OR p.fecha_limite='1000-01-01') AND eliminado = 0 
                        AND p.id_producto = '{$id_productos[$key]}' ")->fetch_first();
            
            if (count($consulta_stock) > 0) {
                
                //obtener id de unidad
                $id_asignacion = $db->from('inv_asignaciones')
                                ->where(array('unidad_id' => $unidades[$key], 'producto_id' => $id_productos[$key], 'visible' => 's'))
                                ->fetch_first();

                $id_unidad = $id_asignacion['unidad_id'];
                
                //obtiene stock de producto
                $stock = (($consulta_stock['cantidad_ingresos'] - $consulta_stock['cantidad_egresos']) > 0) ? $consulta_stock['cantidad_ingresos'] - $consulta_stock['cantidad_egresos'] : 0;
                
                if (count($id_asignacion) > 0) {
                    
                    //obtiene stock en unidad(1)
                    $cantidad_egresar = ($id_asignacion) ? $cantidades[$key] * $id_asignacion['cantidad_unidad'] : 0;
                    
                    // se valida que el stock es o menor a la cantidad solicitada
                    if (($cantidad_egresar > $stock) || !$stock || !$cantidad_egresar || $stock <= 0 || $cantidad_egresar <= 0 ) {

                        $id_productos_obs[] = $id_productos[$key];
                        $cantidades_obs[] = $cantidades[$key];
                        $unidades_obs[] = $unidades[$key];

                        //se prepara respuesta 
                        $datos[] = array('id_producto' => $consulta_stock['id_producto'], 
                                            'codigo' => $consulta_stock['id_producto'],
                                            'producto' => $consulta_stock['nombre_factura'],
                                            'stock' => $stock,
                                            'stock_solicitado' => $cantidad_egresar);
                    }
                }
            }
        }

        //validamos la no existencia  de productos observados
        if (count($id_productos_obs) > 0) {
            
            $detalles = $db->query("SELECT d.*, p.codigo, p.nombre_factura from inv_egresos_detalles d
                                    LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id 
                                    LEFT JOIN inv_productos p ON p.id_producto = d.producto_id
                                    WHERE d.egreso_id = '$id_egreso'
                                    AND d.producto_id IN ({$id_productos_obs})
                                    AND e.anulado = '0' AND e.estadoe = '2'")->fetch_first();
    
    
            // se itera los registros obtenidos de la base de datos
            foreach ($detalles as $key => $detalle) {
                
                // se obtiene los id_productos considentes con los registros de la base de datos
                $id_obtenidos = array_keys($id_productos_obs, $detalle['producto_id']);
    
                //itera los resultados de la busqueda
                foreach ($id_obtenidos as $key => $value) {
                    $id_producto = $id_obtenidos[$key];
                    $id_prod = $id_productos_obs[$id_producto];
                    $cant = $cantidades_obs[$id_producto];
                    $unid = $unidades_obs[$id_producto];
    
                    //se prepara cantidad nueva
                    $datos1 = array('producto_id' => $id_prod, 
                                    'unidad_id' => $unid,
                                    'visible' => 's');
    
                    $cantidad_nueva = $db->select('*')->from('inv_asignaciones')->where($datos1)->fetch_first();
                    $cantidad_total_nueva = ($cantidad_nueva) ? $cant * $cantidad_nueva['cantidad_unidad'] : 0;
    
                    //se obtien el detalle
                    $cantidad_total_actual = $detalle['cantidad'];     
    
                    //se valida la exitencia de stock
                    if ($cantidad_total_nueva >= $cantidad_total_actual) {
                        //se prepara respuesta 
                        $datos[] = array('id_producto' => $id_prod, 
                                            'codigo' => $detalle['codigo'],
                                            'producto' => $detalle['nombre_factura'],
                                            'stock' => $detalle['cantidad'],
                                            'stock_solicitado' => $cant);
                    }              
        
                }
            }
        }       
       
    }

    return (count($datos)> 0) ? $datos : array();
}


/*
+--------------------------------------------------------------------------
| GUARDAR PROCESO
+--------------------------------------------------------------------------
*/

function save_process($db, $proceso = 'a', $direccion = '', $accion = 'accion', $id_egreso = 0, $id_user = 0, $token = ''){

    if ($token) {
        $imei = $db->query("SELECT sd.model, sd.imei, sd.user_id AS user_id_principal, sd.token AS token_principal, sdd.user_id AS user_id_secundario, sdd.token AS token_secundario 
                        FROM sys_users_devices sd 
                        LEFT JOIN sys_users_devices_detalles sdd ON sd.id_device = sdd.device_id
                        WHERE (sd.token = '{$token}' || sdd.token = '{$token}') 
                        AND (sd.user_id = '{$id_user}' || sdd.user_id = '{$id_user}')
                        GROUP BY sd.user_id")->fetch_first()['imei'];
    }

    $imei = ($imei) ? $imei : null;

    // Guarda Historial
    $data = array(
                'fecha_proceso' => date("Y-m-d H:i:s"),
                'hora_proceso' => date("H:i:s"),
                'proceso' => $proceso,
                'nivel' => 'l',
                'direccion' => $direccion,
                'detalle' => 'Se realizo ' . $accion . ' con identificador numero ' . $id_egreso,
                'id_movimiento' => ($id_egreso > 0) ? $id_egreso : 0,
                'usuario_id' => $id_user,
                'imei' => $imei
            );

    $id = $db->insert('sys_procesos_device', $data);

    //validamos cantidad de registros insertados
  if($id > 0){        
        return true;
    }else{
        return false;
    }  
    //return true;
}



/*
+--------------------------------------------------------------------------
| CONFIGURAR ATRIBUTO
+--------------------------------------------------------------------------
*/

function configurar_atributo($db, $reporte = '', $modulo = '', $archivo = '', $atributo = ''){

    $dato = '';

    if ($reporte && $modulo && $archivo) {
        
        $id_detalle = $db->query("SELECT * FROM sys_reportes r WHERE r.reporte = '{$reporte}' 
                        AND r.modulo = '{$modulo}' and r.archivo = '{$archivo}' AND r.habilitado = 'Si'")->fetch_first()['id_reporte'];
        
        if ($id_detalle && $atributo) {
            $dato = $db->query("SELECT * FROM sys_reportes_detalles d WHERE d.reporte_id = '{$id_detalle}' AND d.atributo = '{$atributo}'")->fetch_first()['detalle'];
        }
    }


    //validamos cantidad de registros insertados
    if($dato){        
        return $dato;
    }else{
        return '';
    }  
}



/*
+--------------------------------------------------------------------------
| CONFIGURAR ATRIBUTO
+--------------------------------------------------------------------------
*/

function historial_conversion($db, $id_origen = 0, $origen = '', $id_destino = 0, $destino = '', $id_empleado = 0, $tipo = "", $id_backup = 0, $ids_backups = ''){


    if ($id_origen > 0 && $origen != null && $origen != '' && $id_destino > 0 && $tipo != null && $tipo != '' && $id_backup > 0) {
                        
        $datos = array(
            'fecha_registro' => date('Y-m-d'),
            'hora_registro' => date('H:i:s'),
            'id_origen' => $id_origen,
            'origen_movimiento' => $origen,
            'id_destino' => $id_destino,
            'destino_movimiento' => $destino,
            'empleado_id' => ($id_empleado > 0) ? $id_empleado : 0,
            'tipo' => $tipo,
            'id_backup_egreso' => $id_backup,
            'ids_backup_detalles' => $ids_backups
        );    
        
        $id_conversion = $db->insert('hist_conversiones', $datos);
    }

    //validamos cantidad de registros insertados
    if($id_conversion){        
        return true;
    }else{
        return false;
    }  
}


/*
+--------------------------------------------------------------------------
| VALIDAR CONVERSION DE NOTAS A  FACTURAS
+--------------------------------------------------------------------------
*/

function validar_conversion($db, $id_egreso = 0, $id_destino = 0, $origen_tipo = 'Preventa'){

    $respuesta = false;

    $cantidad_notas = 3;

    // validamos la existencia de datos
    if ($id_egreso != null && $id_egreso != '' && $origen_tipo != '' && $origen_tipo != null) {
        
        $cantidad_notas = $db->query("
        SELECT count(*)nro_registros FROM hist_conversiones hc 
        WHERE hc.id_origen = '{$id_egreso}'
        AND hc.origen_movimiento = '{$origen_tipo}' AND hc.destino_movimiento = 'Electronicas'")->fetch_first()['nro_registros'];
    }
    
    //validamos que es el resultdo sea menor al permitido
    $respuesta = ($cantidad_notas <= 2) ? true : false;

    return $respuesta;
}


?>

