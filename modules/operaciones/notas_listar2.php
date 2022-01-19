<?php
    $requestData=$_REQUEST;
    $ValorG=$requestData['search']['value'];

    $formato_textual = get_date_textual($_institution['formato']);
    $formato_numeral = get_date_numeral($_institution['formato']);

    // Obtiene los permisos
    $permisos = explode(',', permits);
    // Almacena los permisos en variables
    $permiso_crear = in_array('notas_crear', $permisos);
    $permiso_ver = in_array('notas_ver', $permisos);
    $permiso_eliminar = in_array('notas_eliminar', $permisos);
    $permiso_imprimir = in_array('notas_imprimir', $permisos);
    $permiso_devolucion = in_array('notas_devolucion', $permisos);
    $permiso_cambiar = true;

    $fecha_inicial=$params[0];
    $fecha_final=$params[1];

    $Campos=array(
        'i.id_egreso',
        'i.fecha_egreso',
        'i.id_egreso',
        'i.nombre_cliente',
        'i.nit_ci',
        'i.nro_factura',
        'i.monto_total',
        'i.nro_registros',
        'a.almacen',
        'e.nombres'
    );

    $Sentencia="SELECT i.*,a.almacen,a.principal,e.nombres,e.paterno,e.materno
        FROM inv_egresos i
        LEFT JOIN inv_almacenes a ON i.almacen_id=a.id_almacen
        LEFT JOIN sys_empleados e ON i.empleado_id=e.id_empleado
        WHERE i.tipo='Venta' AND
            i.codigo_control='' AND
            i.provisionado='S' AND
            i.fecha_egreso>='{$fecha_inicial}' AND
            i.fecha_egreso<='{$fecha_final}'";

    if(!empty($ValorG)):
        $Sentencia.=" AND (i.id_egreso LIKE '%{$ValorG}%' OR
                    i.fecha_egreso LIKE '%{$ValorG}%' OR
                    i.nombre_cliente LIKE '%{$ValorG}%' OR
                    i.nit_ci LIKE '%{$ValorG}%' OR
                    i.nro_factura LIKE '%{$ValorG}%' OR
                    i.monto_total LIKE '%{$ValorG}%' OR
                    i.nro_registros LIKE '%{$ValorG}%' OR
                    a.almacen LIKE '%{$ValorG}%' OR
                    e.nombres LIKE '%{$ValorG}%')";
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
    $Sentencia.=" LIMIT {$Inicio},{$Final}";

    $Consulta=$db->query($Sentencia)->fetch();
    $data= array();

    foreach($Consulta as $key=>$Dato):
        $nestedData= array();
        $nestedData[]=$requestData['start']+$key+1;

        $Aux=escape(date_decode($Dato['fecha_egreso'],$_institution['formato']));
        $nestedData[]="{$Aux}<small class='text-success'>{$Dato['hora_egreso']}</small>";
        $nestedData[]='Nota de remisión';
        $nestedData[]=escape($Dato['nombre_cliente']);
        $nestedData[]=escape($Dato['nit_ci']);
        $nestedData[]=escape($Dato['nro_factura']);
        $nestedData[]=escape($Dato['monto_total']);
        $nestedData[]=escape($Dato['nro_registros']);
        $nestedData[]=escape($Dato['almacen']);
        $nestedData[]=escape($Dato['nombres'].' '.$Dato['paterno'].' '.$Dato['materno']);
        $Aux='';
        if($permiso_ver):
            $Aux.="<a href='?/operaciones/notas_ver/{$Dato['id_egreso']}' data-toggle='tooltip' data-title='Ver detalle de nota de remisión'><i class='glyphicon glyphicon-list-alt'></i></a>";
        endif;
        if($permiso_eliminar):
            $Aux.="<a href='?/operaciones/notas_eliminar/{$Dato['id_egreso']}' data-toggle='tooltip' data-title='Eliminar nota de remisión' data-eliminar='true'><span class='glyphicon glyphicon-trash'></span></a>";
        endif;
        if(true):
            $Aux.="<a href='?/operaciones/notas_devolucion/{$Dato['id_egreso']}' data-toggle='tooltip' data-title='devolución'><span class='glyphicon glyphicon-transfer'></span></a>";
        endif;
        $nestedData[]=$Aux;
        $nestedData[]="<input type='checkbox' data-toggle='tooltip' data-title='Seleccionar' data-seleccionar='{$Dato['id_egreso']}'>";
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