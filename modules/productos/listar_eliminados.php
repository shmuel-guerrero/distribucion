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

    $Campos=array(
        'p.id_producto',
        'p.imagen',
        'p.codigo',
        'p.nombre',
        'p.nombre_factura',
        'c.categoria',
        'p.descripcion',
        'p.precio_actual',
        'u.unidad',
        'u.unidad',//
        'p.cantidad_minima'
    );

    $Sentencia="SELECT p.*,u.unidad,c.categoria
                FROM inv_productos p
                LEFT JOIN inv_unidades u ON p.unidad_id=u.id_unidad
                LEFT JOIN inv_categorias c ON p.categoria_id=c.id_categoria";

    //FILTRO GENERAL
    // $Sentencia.=" WHERE";
    // ocultamos productos con estado eliminado = true
    $Sentencia.=" WHERE p.eliminado = 1 ";
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

    foreach($Consulta as $key=>$Dato):
        $nestedData= array();

        $nestedData[]=$requestData['start']+$key+1;
        $Aux=($Dato['imagen']=='')?imgs.'/image.jpg':files.'/productos/'.$Dato['imagen'];
        $nestedData[]="<img src='{$Aux}'  class='img-rounded cursor-pointer' data-toggle='modal' data-target='#modal_mostrar' data-modal-size='modal-md' data-modal-title='Imagen' width='75' height='75'>";
        $Aux=escape($Dato['codigo']);
        $nestedData[]="<samp class='lead'>{$Aux}</samp>";
        $nestedData[]=escape($Dato['nombre']);
        $nestedData[]=escape($Dato['nombre_factura']);
        $nestedData[]=escape($Dato['categoria']);
        $nestedData[]=escape($Dato['descripcion']);
        $nestedData[]=escape($Dato['precio_actual']);
        $nestedData[]=escape($Dato['unidad']);
        $nestedData[]=cantidad_unidad($db,$Dato['id_producto'],9)?cantidad_unidad($db,$Dato['id_producto'],9).' '.nombre_unidad($db,$Dato['unidad_id']):'1 '.nombre_unidad($db,$Dato['unidad_id']);
        $nestedData[]=escape($Dato['cantidad_minima']);

        $Aux=[escape('(1)'.$Dato['unidad']),escape($Dato['precio_actual'])];
        $Aux="<span class='glyphicon glyphicon-remove-circle'></span> {$Aux[0]}: <b>{$Aux[1]}</b><br>";
        $ids_asignaciones=$db->select('*')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id = b.id_unidad' )->where('a.producto_id',$Dato['id_producto'])->fetch();
        foreach($ids_asignaciones as $i=>$id_asignacion):
            if(empty($ids_asignaciones)):
                $Aux.='<span>No asignado</span>';
            else:
                $Extra=escape('('.$id_asignacion['cantidad_unidad'].')'.$id_asignacion['unidad']);
                $Aux.="<span>{$Extra}:</span>";
                if($permiso_fijar):
                    $Extra=escape($id_asignacion['otro_precio']);
                    // $Aux.="<a href='?/productos/fijar/{$id_asignacion['id_asignacion']}' class='underline-none text-primary' data-toggle='tooltip' data-title='Fijar precio' data-fijar='true'>
                    //             <b>{$Extra}</b>
                    //         </a>";
                else:
                    $Extra=escape($id_asignacion['otro_precio']);
                    $Aux.="<b>{$Extra}</b>";
                endif;
            endif;
            $Aux.='<br>';
        endforeach;
        $nestedData[]=$Aux;

        $Aux='';
        if($permiso_ver || $permiso_eliminar ):
            if($permiso_ver):
                if($Dato['promocion']==''):
                    //$Aux.="<a href='?/productos/ver/{$Dato['id_producto']}' data-toggle='tooltip' target='_blanck' title='Ver producto' data-title='Ver producto'><i class='glyphicon glyphicon-search'></i></a>";
                else:
                    //$Aux.="<a href='?/productos/ver_promocion/{$Dato['id_producto']}' data-toggle='tooltip' target='_blanck' title='Ver producto' data-title='Ver producto'><i class='glyphicon glyphicon-search'></i></a>";
                endif;
            endif;
            if($permiso_eliminar):
                if($Dato['promocion']==''):
                   $Aux.=" <a href='?/productos/eliminar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Restaurar producto' title='Restaurar producto' data-eliminar='true'><i class='glyphicon glyphicon-refresh'></i></a>";
                else:
                    //$Aux.=" <a href='?/productos/eliminar/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Restaurar producto' title='Restaurar producto' data-eliminar='true'><i class='glyphicon glyphicon-refresh'></i></a>";
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