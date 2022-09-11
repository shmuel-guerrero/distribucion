<?php
if(isset($_POST['nro'])){
    $nro = $_POST['nro']-1;
    $info = pathinfo($_FILES["xlss"]["name"]);
    $archivo = md5(rand().time()).".".$info['extension'];

    //$_FILES["foto_doc"]["type"];      //tipo
    //$_FILES["foto_doc"]["tmp_name"];  //nombre del archivo de la imagen temporal
    move_uploaded_file($_FILES["xlss"]["tmp_name"],        $archivo );
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Leer Archivo Excel</title>
</head>
<body>
<form action="index.php" method="post" enctype="multipart/form-data">
    <input type="text" name="nro" placeholder="Nro pagina" required/><br/>
    <input type="file" name="xlss" required/><br/>
    <input type="submit" value="Leer"/>
</form>
<h1>Leer Archivo Excel</h1>
<?php
require_once 'conexion.php';
require_once 'PHPExcel/Classes/PHPExcel.php';
//$archivo = "ASIETNCIA.xls";
if(isset($archivo)) {
    $inputFileType = PHPExcel_IOFactory::identify($archivo);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($archivo);
    $sheet = $objPHPExcel->setActiveSheetIndex($nro);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $list = ["A", "B", "C", "D", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "A0", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ"];

    $f = $sheet->getCell("G3")->getValue();
    $fecha = explode('~', $f);
    $r = 2;
    while($sheet->getCell("A".$r)->getValue()!=''){
        $codigo = $sheet->getCell("A".$r)->getValue();
        $nombre = $sheet->getCell("C".$r)->getValue();
        $tipo = $sheet->getCell("D".$r)->getValue();
        $color = $sheet->getCell("E".$r)->getValue();
        $unid = $sheet->getCell("G".$r)->getValue();
        $desc = $sheet->getCell("F".$r)->getValue();
        $tipo = str_replace(' ','',$tipo);
        $unid = str_replace(' ','',$unid);
        if($tipo=='') {
            $tipo = 'SIN TIPO';
        }
        $tip = $db->select('*')->from('inv_categorias')->where('categoria',$tipo)->fetch_first();
        $unidad = $db->select('*')->from('inv_unidades')->where('unidad',$unid)->fetch_first();

            $dataa=array(
                'codigo' => $codigo,
                'codigo_barras' => $codigo,
                'nombre' => $nombre,
                'nombre_factura' => $nombre,
                'color' => $color,
                'cantidad_minima' => 10,
                'descripcion' => $desc,
                'unidad_id' => $unidad['id_unidad'],
                'categoria_id' => $tip['id_categoria']
            );
            $db->insert('inv_productos',$dataa);
            echo '--'.$sheet->getCell("A".$r)->getValue().'<br>';
        $r++;
    }
}else{
    echo 'Seleccione un archivo excel';
}
?>
</body>
</html>
