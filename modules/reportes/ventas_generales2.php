<?php
    $requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    // Obtiene los permisos
    $permisos = explode(',', permits);
    // Almacena los permisos en variables
    $permiso_cambiar = true;

    $fecha_inicial=$params[0];
    $fecha_final=$params[1];

    $Campos=array(
        'i.id_egreso',
        'i.fecha_egreso',
        //'i.codigo',
        'i.tipo',
        'i.cliente_id',
        'i.nombre_cliente',
        'i.nit_ci',
        'i.nro_factura',
        'i.monto_total',
        'i.nro_registros',
        'a.almacen',
        'e.nombres'
    );

    $Sentencia="SELECT i.*,a.almacen,a.principal,e.nombres,e.paterno,e.materno FROM inv_egresos i
				LEFT JOIN inv_almacenes a ON i.almacen_id=a.id_almacen
				LEFT JOIN sys_empleados e ON i.empleado_id=e.id_empleado
                WHERE i.tipo='Venta' AND i.fecha_egreso>='{$fecha_inicial}' AND i.fecha_egreso<='{$fecha_final}'";

    //FILTRO GENERAL
    if(!empty($ValorG)):
        $Sentencia.=" AND (i.fecha_egreso LIKE '%{$ValorG}%' OR
                    i.tipo LIKE '%{$ValorG}%' OR
                    i.cliente_id LIKE '%{$ValorG}%' OR
                    i.nombre_cliente LIKE '%{$ValorG}%' OR
                    i.nit_ci LIKE '%{$ValorG}%' OR
                    i.nro_factura LIKE '%{$ValorG}%' OR
                    i.monto_total LIKE '%{$ValorG}%' OR
                    i.nro_registros LIKE '%{$ValorG}%')";
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

    //ORDEN
    if(isset($columns[$requestData['order'][0]['column']])):
        $Columna=$columns[$requestData['order'][0]['column']];
        $Orden=$requestData['order'][0]['dir'];
        $Sentencia.=" ORDER BY {$Columna} {$Orden}";
    endif;

    $totalFiltered=count($db->query($Sentencia)->fetch());
    $totalData=$totalFiltered;

    //LIMITE
    $Inicio=$requestData['start']?$requestData['start']:0;
    $Final=$requestData['length']?$requestData['length']:50;
    
    if($Final != '-1'){
        $Sentencia.=" LIMIT {$Inicio},{$Final}";
    }
    
    
    // $Sentencia.=" LIMIT {$Inicio},{$Final}";

    //echo $db->last_query();

    $Consulta=$db->query($Sentencia)->fetch();
    $data= array();

    foreach($Consulta as $key=>$Dato):
        $nestedData = array();
        $nestedData[]=$requestData['start']+$key+1;
        $Aux=escape(date_decode($Dato['fecha_egreso'],$_institution['formato']));
        $nestedData[]="{$Aux} <small class=''>{$Dato['hora_egreso']}</small>";
        $Aux=$Dato['tipo'].' ';
        if($Dato['codigo_control']!=''):
            $Aux.='electrónica';
        elseif($Dato['nro_autorizacion']!=''):
            $Aux.='manual';
        elseif($Dato['estadoe']==0):
            $Aux.='nota remisión';
        elseif($Dato['estadoe']>1):
            $Aux.='preventa';
        endif;
        $nestedData[]=$Aux;
        $nestedData[]=escape($Dato['cliente_id']);
        $nestedData[]=escape($Dato['nombre_cliente']);
        $nestedData[]=escape($Dato['nit_ci']);
        $nestedData[]=escape($Dato['nro_factura']);
        $nestedData[]=escape($Dato['monto_total']);
        $nestedData[]=escape($Dato['nro_registros']);
        $nestedData[]=escape($Dato['almacen']);
        $nestedData[]=escape($Dato['nombres'].' '.$Dato['paterno'].' '.$Dato['materno']);
        $data[]=$nestedData;
    endforeach;

    $json_data=array(
        'draw'           =>intval($requestData['draw']),
        'recordsTotal'   =>intval($totalData),
        'recordsFiltered'=>intval($totalFiltered),
        'data'           =>$data
    );

    echo json_encode($json_data);