<?php
    $requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    $id_almacen=(isset($params[0]))?$params[0]:0;
    if ($id_almacen == 0):
        $almacenes = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();
    else:
        $id_almacen = explode('-', $id_almacen);
        $almacenes = $db->from('inv_almacenes')->where_in('id_almacen', $id_almacen)->order_by('id_almacen')->fetch();
    endif;
    if(!$almacenes):
        require_once not_found();
        exit;
    endif;
    $select = "SELECT p.id_producto, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, p.descripcion, u.unidad, u.sigla, c.categoria";
    $from = " from inv_productos p left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id";
    $join = "";
    foreach ($almacenes as $nro => $almacen):
        $id = $almacen['id_almacen'];
        $select = $select . ", ifnull(e$id.ingresos$id, 0) as ingresos$id, ifnull(s$id.egresos$id, 0) as egresos$id";
        $join = $join . " left join (select d.producto_id, sum(d.cantidad) as ingresos$id from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id where transitorio = 0 AND i.almacen_id = $id group by d.producto_id) as e$id on e$id.producto_id = p.id_producto";
        $join = $join . " left join (select d.producto_id, sum(d.cantidad) as egresos$id from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = $id and e.anulado != 3 group by d.producto_id) as s$id on s$id.producto_id = p.id_producto";
        //agregar array con los nombres de los campos de los almacenes
    endforeach;

    $Campos=array(
        'p.id_producto',
        'p.codigo',
        'p.nombre',
        'p.nombre_factura',
        'p.descripcion',
        'c.categoria',
        'p.cantidad_minima'
    );

    $Sentencia=$select.$from.$join;

    //FILTRO GENERAL
    $Sentencia.=" WHERE";
    if(!empty($ValorG)):
        $Sentencia.=" (p.id_producto LIKE '%{$ValorG}%' OR
                    p.codigo LIKE '%{$ValorG}%' OR
                    p.nombre LIKE '%{$ValorG}%' OR
                    p.nombre_factura LIKE '%{$ValorG}%' OR
                    p.descripcion LIKE '%{$ValorG}%' OR
                    c.categoria LIKE '%{$ValorG}%' OR
                    p.cantidad_minima LIKE '%{$ValorG}%' OR
                    p.precio_actual LIKE '%{$ValorG}%')";
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
    if(isset($Campos[$requestData['order'][0]['column']])):
        $Columna=$Campos[$requestData['order'][0]['column']];
        $Orden=$requestData['order'][0]['dir'];
        $Sentencia.=" ORDER BY {$Columna} {$Orden}";
    endif;

    $totalFiltered=count($db->query($Sentencia)->fetch());
    $totalData=$totalFiltered;

    //LIMITE
    //$Sentencia.=" LIMIT {$requestData['start']},{$requestData['length']}";
    
    if($requestData['length']!='-1'):
        $Inicio=$requestData['start']?$requestData['start']:0;
        $Final=$requestData['length']?$requestData['length']:25;
        $Sentencia.=" LIMIT {$Inicio},{$Final}";
    endif;

    $Consulta=$db->query($Sentencia)->fetch();
    $data= array();

    // echo $db->last_query();

    foreach($Consulta as $key=>$Dato):
        $costo = $db->select('costo')->from('inv_ingresos_detalles')->where('producto_id',$Dato['id_producto'])->order_by('id_detalle','desc')->fetch_first();
        $costo = ($costo['costo']) ? $costo['costo'] : 0;
    
        $nestedData= array();
        $nestedData[]=$requestData['start']+$key+1;

        $nestedData[]=escape($Dato['codigo']);
        $nestedData[]=escape($Dato['nombre']);
        $nestedData[]=escape($Dato['nombre_factura']);
        $nestedData[]=escape($Dato['descripcion']);
        $nestedData[]=escape($Dato['categoria']);
        $nestedData[]=escape($Dato['cantidad_minima']);

        $total=0;
        foreach ($almacenes as $nro => $almacen):
            $stock=escape($Dato['ingresos'.$almacen['id_almacen']] - $Dato['egresos'.$almacen['id_almacen']]);
            // if($stock == 6){var_dump($Dato['ingresos'.$almacen['id_almacen']] .'-'. $Dato['egresos'.$almacen['id_almacen']]);die();}            
            $Extra=($stock<escape($Dato['cantidad_minima']))?'text-danger':'text-success';
            $nestedData[]="<strong class='{$Extra}'>{$stock}</strong>";
            $total=$total+$stock;
        endforeach;
        $nestedData[]="<strong class='text-primary'>{$total}</strong>";
        $Extra=number_format(escape($Dato['precio_actual'])*$total,2,'.','');
        $Extra2=number_format(($costo)*$total,2,'.','');
        $nestedData[]=escape($Dato['precio_actual']);
        $nestedData[]=$Extra;
        $nestedData[]=escape($costo);
        $nestedData[]=$Extra2;
        $nestedData[]=escape($Dato['unidad']);

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