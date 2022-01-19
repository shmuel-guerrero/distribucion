<?php
    $requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    $Campos=array(
        'p.id_producto',
        'p.codigo',
        'p.nombre',
        'p.nombre_factura',
        'p.descripcion',
        'c.categoria'
    );

    $Sentencia='SELECT p.id_producto,p.codigo,p.nombre,p.nombre_factura,p.descripcion,c.categoria FROM inv_productos p LEFT JOIN inv_categorias c ON p.categoria_id = c.id_categoria';

    //FILTRO GENERAL
    $Sentencia.=" WHERE";
    if(!empty($ValorG)):
        $Sentencia.=" (p.id_producto LIKE '%{$ValorG}%' OR
                    p.codigo LIKE '%{$ValorG}%' OR
                    p.nombre LIKE '%{$ValorG}%' OR
                    p.nombre_factura LIKE '%{$ValorG}%' OR
                    p.descripcion LIKE '%{$ValorG}%' OR
                    c.categoria LIKE '%{$ValorG}%')";
    endif;
    //FILTRO INDEPENDIENTE
    $almacenes = $db->get('inv_almacenes');
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
    if(isset($columns[$requestData['order'][0]['column']])):
        $Columna=$columns[$requestData['order'][0]['column']];
        $Orden=$requestData['order'][0]['dir'];
        $Sentencia.=" ORDER BY {$Columna} {$Orden}";
    endif;

    $totalFiltered=count($db->query($Sentencia)->fetch());
    $totalData=$totalFiltered;

    //LIMITE
    //$Sentencia.=" LIMIT {$requestData['start']},{$requestData['length']}";
    //$Sentencia.=" LIMIT 0,5";
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

        $nestedData[]=escape($Dato['codigo']);//text-nowrap
        $nestedData[]=escape($Dato['nombre']);
        $nestedData[]=escape($Dato['nombre_factura']);
        $nestedData[]=escape($Dato['descripcion']);
        $nestedData[]=escape($Dato['categoria']);

        foreach($almacenes as $nro=>$almacen):
            $nestedData[]="<a href='?/kardex/detallar/{$almacen['id_almacen']}/{$Dato['id_producto']}' data-toggle='tooltip' data-title='Ver kardex'>
                                <span class='glyphicon glyphicon-book'></span>
                            </a>";
        endforeach;

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