<?php
    $requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    $permisos = explode(',', permits);
    $permiso_crear = in_array('crear', $permisos);
    $permiso_editar = in_array('editar', $permisos);
    $permiso_ver = in_array('ver', $permisos);
    $permiso_eliminar = in_array('eliminar', $permisos);
    $permiso_imprimir = in_array('imprimir', $permisos);
    $permiso_cambiar = in_array('cambiar', $permisos);
    $permiso_distribuir = in_array('activar', $permisos);
    $permiso_promocion = in_array('promocion', $permisos);
    $permiso_fijar = false;
    $permiso_quitar = in_array('quitar', $permisos);
    $permiso_ver_precio = true;
    $permiso_asignar_precio = true;
    // Obtiene la moneda oficial
    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

    

    $Campos=array(
        'p.id_producto',
        'p.imagen',
        'p.codigo',
        'p.nombre',
        // 'p.nombre_factura',
        'c.categoria',
        'p.descripcion',
        'u.unidad',
        'u.unidad',//cantidad en caja
        'p.cantidad_minima',
        'p.precio_actual'
    );

    $Sentencia="SELECT p.*,u.unidad,c.categoria, m.marca
                FROM inv_productos p
                LEFT JOIN inv_unidades u ON p.unidad_id=u.id_unidad
                LEFT JOIN inv_categorias c ON p.categoria_id=c.id_categoria
                LEFT JOIN inv_marcas m ON p.marca_id = m.id_marca";

    //FILTRO GENERAL
    // $Sentencia.=" WHERE";
    // ocultamos productos con estado eliminado = true
    $Sentencia.=" WHERE p.eliminado = 0 ";
    if(!empty($ValorG)):
        $Sentencia.=" AND (p.codigo LIKE '%{$ValorG}%' OR
                    p.nombre LIKE '%{$ValorG}%' OR
                    p.nombre_factura LIKE '%{$ValorG}%' OR
                    c.categoria LIKE '%{$ValorG}%' OR
                    p.descripcion LIKE '%{$ValorG}%' OR
                    p.precio_actual LIKE '%{$ValorG}%' OR
                    u.unidad LIKE '%{$ValorG}%' OR
                    p.cantidad_minima LIKE '%{$ValorG}%')";
    endif;


    //FILTRO INDEPENDIENTE
    foreach($Campos as $Nro=>$Campo):
        if($Campo):
            $filtro=$requestData['columns'][$Nro]['search']['value'];
            $filtro=str_replace('.*(','',$filtro);
            $filtro=str_replace(').*','',$filtro);
            if($filtro!='' && substr($Sentencia,-5)!='WHERE')
                $Sentencia.=' AND';
            if($filtro!='')
                $Sentencia.=" {$Campo} LIKE '%{$filtro}%'";
        endif;
    endforeach;

    $Sentencia=rtrim($Sentencia,' AND');

    //ORDEN
    if(isset($columns[$requestData['order'][0]['column']])):
        $Columna=$columns[$requestData['order'][0]['column']];
        $Orden=$requestData['order'][0]['dir'];
        $Sentencia.=" ORDER BY {$Columna} {$Orden}";
    endif;

    $totalFiltered=count($db->query($Sentencia)->fetch());
    $totalData=$totalFiltered;

    //LIMITE
    if($requestData['length']!='-1'):
        $Inicio=$requestData['start']?$requestData['start']:0;
        $Final=$requestData['length']?$requestData['length']:25;
        $Sentencia.=" LIMIT {$Inicio},{$Final}";
    endif;

    $Consulta=$db->query($Sentencia)->fetch();
    $data= array();

    $kex=-1;

    foreach($Consulta as $key=>$Dato):        
        $kex++;
        $Aux = $Aux2 = "";
        $Aux = escape($Dato['precio_actual']);
        $unidad_principal = ($Dato['unidad']) ? escape($Dato['unidad']) : '';
        $Aux="<b class='text-uppercase text-primary'>Precio - Unidad Principal</b><br><span class='glyphicon glyphicon-remove-circle'></span> <a href='#' data-toggle='tooltip' data-title='Actualizar precio'  data-cambiar='0' data-actualizar='{$Dato['id_producto']}'><span class='glyphicon glyphicon-refresh'></span></a>{$moneda}<span data-asig='0' data-precio='{$Dato['id_producto']}'>{$Aux}</span> <b>{$unidad_principal} (1)</b><br>";
        $nestedData= array();
        $nestedData[]=$requestData['start']+$kex+1;

        $imagen=($Dato['imagen']=='')? imgs.'/image.jpg' : files . '/productos/' . $Dato['imagen'];
        $imagen = file_exists($imagen) ? $imagen : imgs.'/image.jpg'; 
        $nestedData[] = "<img src='{$imagen}'  class='img-rounded cursor-pointer' data-toggle='modal' data-target='#modal_mostrar' data-modal-size='modal-md' data-modal-title='Imagen' width='75' height='75'>";
        $codigo = escape($Dato['codigo']);
        $nestedData[] = "<samp class='lead' data-codigo='{$Dato['id_producto']}'>{$codigo}</samp>";
        $nestedData[] = escape($Dato['nombre']) . "<br><small class='text-capitalize text-success'>" . escape($Dato['nombre_factura']) . "</small>";
        $nestedData[] = escape($Dato['categoria']) . ' <small class="text-success"> ' . (($Dato['marca']) ? escape($Dato['marca']) : '') . '</small>';
        $nestedData[] = escape($Dato['descripcion']);
        $nestedData[] = escape($Dato['unidad']);
        $unidad_id = $db->select('unidad_id')->from('inv_productos')->where('id_producto',$Dato['id_producto'])->fetch_first()['unidad_id'];
        $nestedData[] = cantidad_unidad($db,$Dato['id_producto'], $unidad_id)?cantidad_unidad($db,$Dato['id_producto'],$unidad_id).' '.nombre_unidad($db,$Dato['unidad_id']):'1 '.nombre_unidad($db,$Dato['unidad_id']);
        $nestedData[] = escape($Dato['cantidad_minima']);

        $ids_asignaciones=$db->select('*')
                             ->from('inv_asignaciones a')
                             ->join('inv_unidades b','a.unidad_id = b.id_unidad  AND a.visible = "s" ' )
                             ->where('a.producto_id',$Dato['id_producto'])
                             ->fetch();

        foreach($ids_asignaciones as $i => $id_asignacion):
            $Aux .= ($i == 0) ? "<b class='text-uppercase text-primary'>Precio(s) - Unidad(es) Secundaria(s)</b><br>" : "";            
            $kex++;
            if(empty($ids_asignaciones)):
                $Aux .= '<span>No asignado</span>';
            else:
                if($permiso_quitar):
                    $Aux .= ($i > 0 ) ? '<br>':'';
                    $Aux.="<a href='?/productos/quitar/{$id_asignacion['id_asignacion']}' class='underline-none' data-toggle='tooltip' data-title='Eliminar unidad' data-quitar='true'>
                    <span class='glyphicon glyphicon-remove-circle'></span>
                    </a>";
                endif;
                if($permiso_cambiar):
                    $Aux .= "<a style='margin-right: 3px' href='#' data-toggle='tooltip' data-title='Actualizar precio' data-cambiar='{$id_asignacion['id_asignacion']}' data-actualizar='{$Dato['id_producto']}'><span class='glyphicon glyphicon-refresh'></span></a>";
                endif;
                $Extra =  escape($id_asignacion['otro_precio']);
                $Aux .= $moneda . "<span data-asig='{$id_asignacion['id_asignacion']}' data-precio='{$Dato['id_producto']}'>{$Extra}</span> ";
                $Aux .= " <span>" . escape($id_asignacion['unidad']) . "</span>" . " (" . escape($id_asignacion['cantidad_unidad']) . ")";
                if($permiso_fijar):
                    $Aux .= "<a href='?/productos/fijar/{$id_asignacion['id_asignacion']}' class='underline-none text-primary' data-toggle='tooltip' data-title='Fijar precio' data-fijar='true'>
                    <!--<b>{$Extra}</b>-->
                    </a>";
                else:
                    $Extra = escape($id_asignacion['otro_precio']);
                endif;

            endif;
        endforeach; 

        $nestedData[] = $Aux;

        $links='';
        if($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar):
            if($permiso_ver):
                if($Dato['promocion']==''):
                    $links .= " <a href='?/productos/ver/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Ver producto'><i class='glyphicon glyphicon-search'></i></a> ";
                else:
                    $links .= " <a href='?/productos/ver_promocion/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Ver producto'><i class='glyphicon glyphicon-search'></i></a> ";
                endif;
            endif;
            if($permiso_editar):
                if($Dato['promocion']==''):
                    $links .= " <a href='?/productos/editar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Editar producto'><i class='glyphicon glyphicon-edit'></i></a> ";
                else:
                    $links .= " <a href='?/promociones/editar_promocion/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Editar producto'><i class='glyphicon glyphicon-edit'></i></a> ";
                endif;
            endif;

            if(false):
                if($Dato['grupo']==''):
                    $links .= " <a href='?/productos/activar/{$Dato['id_producto']}' class='underline-none text-danger' data-toggle='tooltip' data-title='Asignar a un grupo' data-asignar='true'><i class='glyphicon glyphicon-bed'></i></a> ";
                else:
                    $links .= " <a href='?/productos/activar/{$Dato['id_producto']}' class='text-success' data-toggle='tooltip' data-title='Quitar del grupo' data-activar='true'><i class='glyphicon glyphicon-bed'></i></a> ";
                endif;
            endif;
            if($permiso_ver_precio):
                $links .= " <a href='?/precios/ver/{$Dato['id_producto']}' target='_blank' data-toggle='tooltip' data-title='Ver historial'><i class='glyphicon glyphicon-list-alt'></i></a> ";
            endif;
            if($permiso_asignar_precio):
                $links .= " <a href='?/productos/asignar/{$Dato['id_producto']}' class='underline-none' data-toggle='tooltip' data-title='Asignar nuevo precio' data-asignar-precio='true'><span class='glyphicon glyphicon-tag'></span></a> ";
            endif;
            //verifica si el producto tiene un ingreso en algún almacen
            $existe = $db->query("SELECT id_detalle	from inv_ingresos_detalles
                                    where producto_id = ".$Dato['id_producto']." LIMIT 1")->fetch();

            $existe = count($existe);
            if($permiso_eliminar && $existe == 0):
                if($Dato['promocion']==''):
                    $links .= " <a href='?/productos/eliminar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Eliminar producto' data-eliminar='true'><i class='glyphicon glyphicon-trash  text-danger'></i></a> ";
                else:
                    $links .= " <a href='?/productos/eliminar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Eliminar producto' data-eliminar='true'><i class='glyphicon glyphicon-trash text-danger'></i></a> ";
                endif;
            endif;
        endif;
        $nestedData[]=$links;
        $nestedData[]=$Dato['id_producto'];

        $data[]=$nestedData;




        
    endforeach;

    $json_data=array(
        'draw'           =>intval($requestData['draw']),
        'recordsTotal'   =>intval($totalData),
        'recordsFiltered'=>intval($totalFiltered),
        'data'           =>$data
    );
    // var_dump($data);

    echo json_encode($json_data);