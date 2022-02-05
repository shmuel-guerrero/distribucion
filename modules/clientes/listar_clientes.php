<?php

$requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    // Obtiene los permisos
    $permisos = explode(',', permits);
    $permiso_imprimir = in_array('imprimir', $permisos);
    $permiso_eliminar = in_array('eliminar', $permisos);
    $permiso_modificar = in_array('editar', $permisos);

    $Campos=array(
        'a.id_cliente',
        'a.imagen',
        'a.id_cliente',
        'a.cliente',
        'a.nit',
        'a.telefono',
        'a.direccion',
        'a.tipo'
    );

    $Sentencia="SELECT a.id_cliente,a.imagen,a.cliente, a.nombre_factura, a.nit,a.telefono,a.direccion,a.tipo,a.estado
    FROM inv_clientes AS a";
    /*$Sentencia="SELECT a.id_cliente,a.imagen,a.cliente,a.nit,a.telefono,a.direccion,a.tipo,a.estado,(SELECT COUNT(*) FROM inv_egresos AS b WHERE b.cliente_id=a.id_cliente)AS nro_visitas
        FROM inv_clientes AS a";*/
    //FILTRO GENERAL
    $Sentencia.=" WHERE";
    if(!empty($ValorG)):
    $Sentencia.=" (a.cliente LIKE '%{$ValorG}%' OR
            a.nombre_factura LIKE '%{$ValorG}%' OR
            a.id_cliente LIKE '%{$ValorG}%' OR
            a.nit LIKE '%{$ValorG}%' OR
            a.telefono LIKE '%{$ValorG}%' OR
            a.direccion LIKE '%{$ValorG}%' OR
            a.tipo LIKE '%{$ValorG}%')";
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
    if(isset($columns[$requestData['order'][0]['column']])):
        $Columna=$columns[$requestData['order'][0]['column']];
        $Orden=$requestData['order'][0]['dir'];
        $Sentencia.=" ORDER BY {$Columna} {$Orden}";
    endif;
    $Sentencia.=" GROUP BY a.cliente, a.nit";

    $totalFiltered=count($db->query($Sentencia)->fetch());
    $totalData=$totalFiltered;


    //LIMITE
    if($requestData['length']!='-1'):
        $Inicio=$requestData['start']?$requestData['start']:0;
        $Final=$requestData['length']?$requestData['length']:25;
        $Sentencia.=" LIMIT {$Inicio},{$Final}";
    endif;

    $Consulta=$db->query($Sentencia)->fetch();
    $data=array();

    foreach($Consulta as $key=>$Dato):
        $nestedData=array();
        $nestedData[]=$requestData['start']+$key+1;

        $Aux=($Dato['imagen']=='')?imgs.'/image.jpg':files.'/tiendas/'.$Dato['imagen'];
        $Aux="<img src='{$Aux}' class='img-rounded cursor-pointer' data-toggle='modal' data-target='#modal_mostrar' data-modal-size='modal-md' data-modal-title='Imagen' width='75' height='75'>";
        $nestedData[]=$Aux;

        $nestedData[]=escape($Dato['id_cliente']);
        $nestedData[]=escape($Dato['cliente']) . "<br><small class='text-success text-uppercase'>razón social : " . escape($Dato['nombre_factura']) . "</small>";
        $nestedData[]=escape($Dato['nit']);
        $nestedData[]=escape($Dato['telefono']);
        $nestedData[]=escape($Dato['direccion']);
        $nestedData[]=escape($Dato['tipo']);
        //$nestedData[]=escape($Dato['nro_visitas']);

        $Aux='';
        if($permiso_modificar || $permiso_eliminar):
            if($permiso_modificar):
                $Aux.="<a href='?/clientes/editar/{$Dato['id_cliente']}' data-toggle='tooltip' data-title='Modificar cliente'><span class='glyphicon glyphicon-edit'></span></a>";
            endif;
            if($permiso_eliminar):
                $Aux.="<a href='?/clientes/eliminar/{$Dato['id_cliente']}' data-toggle='tooltip' data-title='Eliminar cliente' data-eliminar='true'><span class='glyphicon glyphicon-trash'></span></a>";
            endif;
        endif;
        $nestedData[]=$Aux;

        $data[]=$nestedData;
    endforeach;


    $json_data=array(
        'draw'           =>intval($requestData['draw']),
        'recordsTotal'   =>intval($totalData),
        'recordsFiltered'=>intval($totalFiltered),
        'data'           =>$data
    );

    echo json_encode($json_data);