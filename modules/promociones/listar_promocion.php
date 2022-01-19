<?php
    $requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    $permisos = explode(',', permits);
    $permiso_crear = true;
    $permiso_editar = true;
    $permiso_ver = true;
    $permiso_eliminar = true;
    $permiso_imprimir = true;
    $permiso_cambiar = true;
    $permiso_distribuir = true;
    $permiso_promocion = true;
    $permiso_fijar = false;
    $permiso_quitar = true;
    $permiso_ver_precio = true;
    $permiso_asignar_precio = true;

    $Campos=array(
        'p.id_producto',
        'p.imagen',
        'p.codigo',
        'p.nombre',
        'p.nombre_factura',
        'c.categoria',
        'p.fecha_limite',
        'p.descripcion',
        'p.precio_actual',
        'u.unidad',
        'p.unidad_id'
    );

    $Sentencia='SELECT p.id_producto,p.imagen,p.codigo,p.nombre,p.nombre_factura,c.categoria,p.descripcion,p.precio_actual,p.fecha_limite,u.unidad,p.unidad_id,p.cantidad_minima,p.precio_sugerido,p.promocion,p.grupo
                FROM inv_productos p
                LEFT JOIN inv_unidades u ON p.unidad_id=u.id_unidad
                LEFT JOIN inv_categorias c ON p.categoria_id=c.id_categoria';

    //FILTRO GENERAL
    $Sentencia.=" WHERE u.unidad='promocion' ";
    if(!empty($ValorG)):
        $Sentencia.="AND (p.id_producto LIKE '%{$ValorG}%' OR
                    p.imagen LIKE '%{$ValorG}%' OR
                    p.codigo LIKE '%{$ValorG}%' OR
                    p.nombre LIKE '%{$ValorG}%' OR
                    p.nombre_factura LIKE '%{$ValorG}%' OR
                    c.categoria LIKE '%{$ValorG}%' OR
                    p.fecha_limite LIKE '%{$ValorG}%' OR
                    p.descripcion LIKE '%{$ValorG}%' OR
                    p.precio_actual LIKE '%{$ValorG}%' OR
                    u.unidad LIKE '%{$ValorG}%' OR
                    p.unidad_id LIKE '%{$ValorG}%')";
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

    //$Sentencia=rtrim($Sentencia,' WHERE');
    //ORDEN
    if(isset($columns[$requestData['order'][0]['column']])):
        $Columna=$columns[$requestData['order'][0]['column']];
        $Orden=$requestData['order'][0]['dir'];
        $Sentencia.=" ORDER BY {$Columna} {$Orden}";
    endif;

    $totalFiltered=count($db->query($Sentencia)->fetch());
    $totalData=$totalFiltered;

    //LIMITE
    $Sentencia.=" LIMIT {$requestData['start']},{$requestData['length']}";

    $Consulta=$db->query($Sentencia)->fetch();
    $data= array();

    foreach($Consulta as $key=>$Dato):
        $nestedData= array();
        $nestedData[]=$requestData['start']+$key+1;

        $Aux=($Dato['imagen']=='')?imgs.'/image.jpg':files.'/productos/'.$Dato['imagen'];
        $nestedData[]="<img src='{$Aux}'  class='img-rounded cursor-pointer' data-toggle='modal' data-target='#modal_mostrar' data-modal-size='modal-md' data-modal-title='Imagen' width='75' height='75'>";
        $Aux=escape($Dato['codigo']);
        $nestedData[]="<samp class='lead'>$Aux</samp>";
        $nestedData[]=escape($Dato['nombre']);
        $nestedData[]=escape($Dato['nombre_factura']);
        $nestedData[]=escape($Dato['categoria']);
        $nestedData[]=escape($Dato['fecha_limite']);
        $nestedData[]=escape($Dato['descripcion']);
        $nestedData[]=escape($Dato['precio_actual']);
        $nestedData[]=escape($Dato['unidad']);
        $nestedData[]=cantidad_unidad($db,$Dato['id_producto'],9)?cantidad_unidad($db,$Dato['id_producto'],9).' '.nombre_unidad($db,$Dato['unidad_id']):'1 '.nombre_unidad($db,$Dato['unidad_id']);

        $Extra=[escape('(1)'.$Dato['unidad']),escape($Dato['precio_actual'])];
        $Aux="<span class='glyphicon glyphicon-remove-circle'></span>{$Extra[0]}: <b>{$Extra[1]}</b><br>";
        $ids_asignaciones = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id = b.id_unidad' )->where('a.producto_id',$Dato['id_producto'])->fetch();
        foreach($ids_asignaciones as $i=>$id_asignacion):
            if(empty($ids_asignaciones)):
                $Aux.='<span>No asignado</span>';
            else:
                if($permiso_quitar):
                    $Aux.="<a href='?/promociones/quitar/{$id_asignacion['id_asignacion']}' class='underline-none' data-toggle='tooltip' data-title='Eliminar unidad' data-quitar='true'>
                                <span class='glyphicon glyphicon-remove-circle'></span>
                            </a>";
                endif;
                $Extra=escape('('.$id_asignacion['cantidad_unidad'].')'.$id_asignacion['unidad']);
                $Aux.="<span>{$Extra}:</span>";
                if($permiso_fijar):
                    $Extra=escape($id_asignacion['otro_precio']);
                    $Aux.="<a href='?/promociones/fijar/{$id_asignacion['id_asignacion']}' class='underline-none text-primary' data-toggle='tooltip' data-title='Fijar precio' data-fijar='true'>
                                <b>{$Extra}</b>
                            </a>";
                else:
                    $Extra=escape($id_asignacion['otro_precio']);
                    $Aux.="<b>{$Extra}</b>";
                endif;
                $Aux.='<br>';
            endif;
        endforeach;
        $nestedData[]=$Aux;

        $Aux='';
        if($permiso_ver||$permiso_editar||$permiso_eliminar||$permiso_cambiar):
            if($permiso_ver):
                if($Dato['promocion']==''):
                    $Aux.="<a href='?/promociones/ver/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Ver producto'><i class='glyphicon glyphicon-search'></i></a>";
                else:
                    $Aux.="<a href='?/promociones/ver_promocion/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Ver producto'><i class='glyphicon glyphicon-search'></i></a>";
                endif;
            endif;
            if($permiso_editar):
                if($Dato['promocion']==''):
//                    $Aux.="<a href='?/promociones/editar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Editar producto'><i class='glyphicon glyphicon-edit'></i></a>";
                else:
                    $verif = $db->query("SELECT * FROM inv_egresos e LEFT JOIN inv_egresos_detalles d ON d.egreso_id = e.id_egreso WHERE d.producto_id = {$Dato['id_producto']} ")->fetch_first();
                    if(!$verif):
                        $Aux.="<a href='?/promociones/editar_promocion/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Editar producto'><i class='glyphicon glyphicon-edit'></i></a>";
                    endif;
                endif;
            endif;
            if($permiso_eliminar):
                if($Dato['promocion']==''):
                    $Aux.="<a href='?/promociones/eliminar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Eliminar producto' data-eliminar='true'><i class='glyphicon glyphicon-trash'></i></a>";
                else:
                    $Aux.="<a href='?/promociones/eliminar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Eliminar producto' data-eliminar='true'><i class='glyphicon glyphicon-trash'></i></a>";
                endif;
            endif;
            if($permiso_cambiar):
                $Aux.="<a href='#' data-toggle='tooltip' data-title='Actualizar precio' data-actualizar='{$Dato['id_producto']}'><span class='glyphicon glyphicon-refresh'></span></a>";
            endif;
            if($permiso_distribuir && $Dato['promocion']==''):
                if($Dato['grupo']==''):
                    $Aux.="<a href='?/promociones/activar/{$Dato['id_producto']}' class='underline-none text-danger' data-toggle='tooltip' data-title='Asignar a un grupo' data-asignar='true'><i class='glyphicon glyphicon-bed'></i></a>";
                else:
                    $Aux.="<a href='?/promociones/activar/{$Dato['id_producto']}' class='text-success' data-toggle='tooltip' data-title='Quitar del grupo' data-activar='true'><i class='glyphicon glyphicon-bed'></i></a>   ";
                endif;
            endif;
            
        endif;
        $nestedData[]=$Aux;

        $nestedData[]=$Dato['id_producto'];
        $data[]=$nestedData;
    endforeach;

    $json_data=array(
        'draw'           =>intval($requestData['draw']),
        'recordsTotal'   =>intval($totalData),
        'recordsFiltered'=>intval($totalFiltered),
        'data'           =>$data
    );

    echo json_encode($json_data);