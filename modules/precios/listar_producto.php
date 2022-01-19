<?php
    $requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    $permisos = explode(',', permits);
    $permiso_imprimir = in_array('imprimir', $permisos);
    $permiso_cambiar = in_array('cambiar', $permisos);
    $permiso_ver = in_array('ver', $permisos);
    $permiso_asignar = true;
    $permiso_fijar = false;
    $permiso_quitar = true;

    $Campos=array(
        'p.id_producto',
        'p.codigo',
        'p.nombre',
        'p.nombre_factura',
        'p.descripcion',
        'u.unidad',
        'p.precio_actual',
        'c.categoria'
    );

    $Select="SELECT p.*,u.unidad,c.categoria";
    $From=" FROM inv_productos AS p";
    $Join=" LEFT JOIN inv_unidades AS u ON p.unidad_id=u.id_unidad
            LEFT JOIN inv_categorias c ON p.categoria_id=c.id_categoria";

    $almacenes=$db->from('inv_almacenes')->order_by('id_almacen')->fetch();
    foreach($almacenes as $nro => $almacen):
        $id = $almacen['id_almacen'];
        $Select.=",IFNULL(e$id.ingresos$id,0)AS ingresos$id,IFNULL(s$id.egresos$id,0)AS egresos$id";
        $Join.=" LEFT JOIN(
                    SELECT d.producto_id,sum(d.cantidad)AS ingresos$id 
                    FROM inv_ingresos_detalles d 
                    LEFT JOIN inv_ingresos i ON i.id_ingreso=d.ingreso_id 
                    WHERE  transitorio = 0 AND  i.almacen_id=$id 
                    GROUP BY d.producto_id
                )AS e$id on e$id.producto_id=p.id_producto";
        $Join.=" LEFT JOIN(
                    SELECT d.producto_id,sum(d.cantidad)AS egresos$id 
                    FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso=d.egreso_id 
                    WHERE e.almacen_id=$id and e.anulado != 3 
                    GROUP BY d.producto_id
                )AS s$id on s$id.producto_id=p.id_producto";
    endforeach;

    $Sentencia=$Select.$From.$Join;



    //FILTRO GENERAL
    $Sentencia.=" WHERE";
    if(!empty($ValorG)):
        $Sentencia.=" (p.codigo LIKE '%{$ValorG}%' OR
                    p.nombre LIKE '%{$ValorG}%' OR
                    p.nombre_factura LIKE '%{$ValorG}%' OR
                    p.descripcion LIKE '%{$ValorG}%' OR
                    c.categoria LIKE '%{$ValorG}%')";
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

    $Sentencia=rtrim($Sentencia,' WHERE');
    //ORDEN
    if(isset($requestData['order'][0]['column'])):
        $Columna=$requestData['order'][0]['column'];
        $Orden=$requestData['order'][0]['dir'];
        $Sentencia.=" ORDER BY {$Campos[$Columna]} {$Orden}";
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
        
        $nestedData= array();
        $nestedData[]=$requestData['start']+$kex+1;

        $nestedData[]=escape($Dato['codigo']);
        $nestedData[]=escape($Dato['nombre']);
        $nestedData[]=escape($Dato['nombre_factura']);
        $nestedData[]=escape($Dato['descripcion']);
        //$nestedData[]=cantidad_unidad($db,$Dato['id_producto'],9)?cantidad_unidad($db,$Dato['id_producto'],9).' '.nombre_unidad($db,$Dato['unidad_id']):'1 '.nombre_unidad($db,$Dato['unidad_id']);
        //$nestedData[]=escape($Dato['precio_actual']);
        $nestedData[]=escape($Dato['categoria']);

        foreach($almacenes as $nro=>$almacen):
            $id=$almacen['id_almacen'];
            $nestedData[]=escape($Dato['ingresos'.$id]-$Dato['egresos'.$id]);
        endforeach;
        
        $AuxPrecio = '';
        $DatoPrecio = '';
        $DatoPrecio=[escape($Dato['unidad']),escape($Dato['precio_actual'])];
        $AuxPrecio="<span  style='margin-right: 3px'  class='glyphicon glyphicon-remove-circle'></span>";
        if($permiso_cambiar):
            $AuxPrecio .= "<a style='margin-right: 3px' href='#' data-toggle='tooltip' data-title='Actualizar precio' data-cambiar='0' data-producto='{$Dato['id_producto']}'><span class='glyphicon glyphicon-refresh'></span></a>";
        endif;
        $AuxPrecio .= " {$DatoPrecio[0]}: <b  data-asignacion-precio='P' data-precio='{$Dato['id_producto']}'>{$DatoPrecio[1]}</b><br>";

        $ids_asignaciones = $db->select('*')
                               ->from('inv_asignaciones a')
                               ->join('inv_unidades b','a.unidad_id = b.id_unidad  AND a.visible = "s" ' )
                               ->where('a.producto_id',$Dato['id_producto'])
                               ->where('a.visible','s')
                               ->fetch();
        
        if (($ids_asignaciones) > 0) {
            foreach($ids_asignaciones as $i => $id_asignacion):                
            
                /*********PRECIO************/
                if(empty($ids_asignaciones)):
                    $AuxPrecio .= '<span>No asignado</span>';
                else:
                    if($permiso_quitar):
                        $AuxPrecio .= "<a style='margin-right: 3px' href='?/precios/quitar/{$id_asignacion['id_asignacion']}' class='underline-none' data-toggle='tooltip' data-title='Eliminar unidad' data-quitar='true'>
                                    <span class='glyphicon glyphicon-remove-circle'></span></a>";
                    endif;
                    if($permiso_cambiar):
                        $AuxPrecio .= "<a style='margin-right: 3px' href='#' data-toggle='tooltip' data-title='Actualizar precio' data-cambiar='{$id_asignacion['id_asignacion']}' data-producto='{$Dato['id_producto']}'><span class='glyphicon glyphicon-refresh'></span></a>";
                    endif;

                    $Extra=escape($id_asignacion['unidad']);
                    $AuxPrecio.="<span>{$Extra}:</span>";

                    if($permiso_fijar && false):
                        $Extra=escape($id_asignacion['otro_precio']);
                        $AuxPrecio .= "<a href='?/productos/fijar/{$id_asignacion['id_asignacion']}' class='underline-none text-primary' data-toggle='tooltip' data-title='Fijar precio' data-fijar='true'>
                                    <b data-asignacion-precio = '{$id_asignacion['id_asignacion']}' data-precio='{$Dato['id_producto']}'>{$Extra}</b></a>";
                    else:
                        $Extra=escape($id_asignacion['otro_precio']);
                        $AuxPrecio .= "<b data-asignacion-precio = '{$id_asignacion['id_asignacion']}' data-precio='{$Dato['id_producto']}'>{$Extra}</b>";
                    endif;
                    $AuxPrecio .= '<br>';
                endif;
            endforeach;            
        }       
        
        $nestedData[]=$AuxPrecio;


        //BOTONES DE ACCIONES ACTUALIZAR PRECIO DE UNIDAD
        $Aux='';
        if($permiso_ver || $permiso_cambiar):
            if($permiso_ver):
                $Aux.="<a href='?/precios/ver/{$Dato['id_producto']}' target='_blank' data-toggle='tooltip' data-title='Ver historial'><i class='glyphicon glyphicon-list-alt'></i></a>";
            endif;
            if($permiso_asignar):
                $Aux.="<a href='?/precios/asignar/{$Dato['id_producto']}' class='underline-none' data-toggle='tooltip' data-title='Asignar nuevo precio' data-asignar='true'>
                        <span class='glyphicon glyphicon-tag'></span>
                    </a>";
            endif;
        endif;






        
        $nestedData[]=$Aux;
        $nestedData[]=$Dato['id_producto'];
        $data[]=$nestedData;

        /*******************************************************/

        $ids_asignaciones = $db->select('*')
                               ->from('inv_asignaciones a')
                               ->join('inv_unidades b','a.unidad_id = b.id_unidad  AND a.visible = "s" ' )
                               ->where('a.producto_id',$Dato['id_producto'])
                               ->fetch();

        $AuxPrecio=[escape($Dato['unidad']),escape($Dato['precio_actual'])];
        $AuxPrecio="<span class='glyphicon glyphicon-remove-circle'></span> {$AuxPrecio[0]} : <b>{$AuxPrecio[1]}</b><br>";
            
                    
    endforeach;

    $json_data=array(
        'draw'           =>intval($requestData['draw']),
        'recordsTotal'   =>intval($totalData),
        'recordsFiltered'=>intval($totalFiltered),
        'data'           =>$data
    );

    echo json_encode($json_data);