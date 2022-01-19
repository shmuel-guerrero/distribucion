<?php
    $Fecha=date('Y-m-d');
    $Responsables=$db->query("SELECT e.id_empleado,e.nombres,e.paterno,e.materno,
        IFNULL((SELECT true FROM inv_ordenes_salidas WHERE fecha_orden='{$Fecha}' AND estado='salida' AND empleado_id=e.id_empleado LIMIT 1),false) AS disponible
        FROM sys_users AS u
        LEFT JOIN sys_empleados AS e ON u.persona_id=e.id_empleado
        LEFT JOIN inv_ordenes_salidas AS os ON os.empleado_id=e.id_empleado
        WHERE u.rol_id='14' GROUP BY os.empleado_id")->fetch();
    echo json_encode($Responsables);