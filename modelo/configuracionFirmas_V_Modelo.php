<?php
/**
 * Modelo para el ABC de Finalidad
 *
 * @category ABC
 * @package ap_grp
 * @author Japheth Calzada López <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 11/07/2018

 * Se realizan operación pero el Alta, Baja y Modificación Jerarquia
 */


session_start();
$PageSecurity = 11;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

$funcion = 2404;
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];


if ($option == 'actualizarFirmante') {
    $idFirmante               = $_POST['idFirmante'];
    $titulo                   = $_POST['titulo'];    
    $informacion              = $_POST['informacion'];        
    $cadena                   = "titulo = '".$titulo."', informacion = '$informacion'";

    $SQL            = " UPDATE tb_detalle_firmas SET ".$cadena." WHERE id_nu_detalle_firmas = '".$idFirmante."'" ;
    $ErrMsg         = "No se obtuvo Información modificar";   
    $TransResult    = DB_query($SQL, $db, $ErrMsg);
    $contenido      = "";
    $result = true;
}

if ($option == 'validarFirmanteRepetido') { 
    $ur             = $_POST["unidadNegocio"];
    $ue             = $_POST["unidadEjecutora"];
    $selectUsuario = $_POST["selectUsuario"];
    $arrayUsuario   = explode("_",$selectUsuario);
    $usuario        = $arrayUsuario[0];

    $SQL            = "SELECT id_nu_detalle_firmas FROM tb_detalle_firmas  WHERE ur='$ur' AND ue = '$ue' AND id_nu_empleado=$usuario  ";

    $ErrMsg         = "No se obtuvo las información";
    $TransResult    = DB_query($SQL, $db, $ErrMsg);
    $myrow          = DB_fetch_array($TransResult); 
    if ($myrow[0]>0) { 
        $result  = true;
    }

}


if ($option == 'guardarFirma') {

    $unidadNegocio                = $_POST['unidadNegocio'];
    $unidadEjecutora              = $_POST['unidadEjecutora'];
    $selectUsuario                = $_POST['selectUsuario'];
    $arrayUsuario                 = explode("_",$selectUsuario);
    $usuario                      = $arrayUsuario[0];
    $titulo                       = $_POST['titulo'];
    $informacion                  = $_POST['informacion'];
    $values                       = "(".$usuario.","."'".$titulo."','".$informacion."',"."'".$unidadNegocio."',"."'$unidadEjecutora')";
    
    $result                       = false; 

    $SQL = "INSERT INTO tb_detalle_firmas (id_nu_empleado,titulo,informacion,ur, ue )
    VALUES ". $values; 
    $InsResult = DB_query($SQL, $db);
    if($InsResult)
        $result =true; 
    
}
function fnRepetidasReportesConac($unidadNegocio,$unidadEjecutora, $reportes,$db){
    $validarExistene  = false;
    $idReporte        = fnObtenerIdReporte($reportes,$db);

    $sqlWhere    = "WHERE id_nu_reportes_conac = $idReporte and ur ='$unidadNegocio' and ue ='$unidadEjecutora'";
    $SQL         = "SELECT id_nu_reportes_conac_firmas  FROM tb_reportes_conac_firmas $sqlWhere";
    $ErrMsg      = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow       = DB_fetch_array($TransResult); 
    if ($myrow[0]>0) {
        $contenido = 1;
        $validarExistente = true;
        $result = false ; 
        $ErrMsg = "Ya existen firmantes en este reporte"; 
    }
    return $validarExistente; 

}
if ($option == 'guardarFirmaReporte'){
    $arrayFirmantes    = $_POST["arrayFirmas"];
    
    $unidadNegocio   = $arrayFirmantes["selectUnidadNegocio"];
    $unidadEjecutora = $arrayFirmantes["selectUnidadEjecutora"];
    $reportes        = $arrayFirmantes["selectReportes"]; 
    $arrayFirmas     = $arrayFirmantes["firmas"];
    $totalFirmas     = count($arrayFirmas); 
    $validarRepetido = fnRepetidasReportesConac($unidadNegocio,$unidadEjecutora, $reportes,$db);
    if (!$validarRepetido){
        $insertarReportesConac = fninsertarReportesConac($unidadNegocio,$unidadEjecutora, $reportes,$db); 
        if ($insertarReportesConac){
        $maxConacFirmas   =   fnMaxConacFirmas($db);
        for ($x=0;$x<$totalFirmas; $x++){
            $idFirma    = $arrayFirmas[$x]  ; 
            $values     = "($maxConacFirmas,$idFirma)";
            $SQL = "INSERT INTO tb_reporte_firmas (id_nu_reportes_conac_firmas,id_nu_detalle_firmas )
                VALUES ". $values;  
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        }
        if ($TransResult)
            $result   = true; 
        }
    }
}

if ($option == 'obtenerEmpleados') {
    $ur         = $_POST["ur"];
    $ue         = $_POST["ue"];
    $sqlWhere   = " WHERE ind_activo = 1 ";

    $sqlWhere   .= " AND tagref = '$ur' ";
    $sqlWhere   .= " AND ue = $ue ";

    $info = array();
    $SQL = "SELECT id_nu_empleado, ln_nombre, sn_primer_apellido, sn_segundo_apellido, id_nu_puesto
            FROM tb_empleados $sqlWhere
            ORDER BY ln_nombre, sn_primer_apellido, sn_segundo_apellido ASC";
    $ErrMsg = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_nu_empleado' => $myrow ['id_nu_empleado'],'ln_nombre' => $myrow ['ln_nombre'], 'sn_primer_apellido' => $myrow ['sn_primer_apellido'],'sn_segundo_apellido' => $myrow ['sn_segundo_apellido'],'id_nu_puesto' => $myrow ['id_nu_puesto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerPuesto') {
    $usuario        = $_POST["usuario"];
    $arrayUsuario   = explode("_",$usuario);
    $puesto         = $arrayUsuario[1];
    $sqlWhere       = " WHERE ind_activo = 1 ";

    $sqlWhere   .= " AND id_nu_puesto = $puesto"; 

    $info = array();
    $SQL = "SELECT id_nu_puesto, ln_descripcion
            FROM tb_cat_puesto $sqlWhere ";
    $ErrMsg = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_nu_puesto' => $myrow ['id_nu_puesto'],'ln_descripcion' => $myrow ['ln_descripcion'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerDatos'){
    $idFirma        = $_POST["idFirma"];
    $SQL            = " SELECT (select ln_reporte from tb_cat_reportes_conac where id_nu_reportes_conac = tb_reportes_conac_firmas.id_nu_reportes_conac) as ln_reporte,
    ur,ue FROM tb_reportes_conac_firmas WHERE id_nu_reportes_conac_firmas =$idFirma  ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if ( $TransResult)
        $result = true; 
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'ur' => $myrow ['ur'],'ue' => $myrow ['ue'] ,'ln_reporte' => $myrow ['ln_reporte'] );
    }
    $contenido = array('datos' => $info);
}
if ($option == 'obtenerDatosFirmantes') {
    
    $id_nu_detalle_firmas = $_POST["id_firmante"]; 
    $contadcor            = 1; 
    $SQL = " SELECT id_nu_empleado,titulo,informacion, ur,ue,
                    (SELECT id_nu_puesto FROM tb_empleados WHERE id_nu_empleado = tb_detalle_firmas.id_nu_empleado) AS id_nu_puesto
                    FROM tb_detalle_firmas WHERE id_nu_detalle_firmas =  $id_nu_detalle_firmas"; 
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if ( $TransResult)
        $result = true; 
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_nu_empleado' => $myrow ['id_nu_empleado'],'titulo' => $myrow ['titulo'], 'informacion' => $myrow ['informacion'],'ur' => $myrow ['ur'],'ue' => $myrow ['ue'] ,'id_nu_puesto' => $myrow ['id_nu_puesto'] );
    }
    $contenido = array('datos' => $info);
}
if ($option == 'modificarFirmantes'){
    $arrayFirmante    = $_POST["arrayFirmas"];
    
    $unidadNegocio   = $arrayFirmante["selectUnidadNegocio"];
    $unidadEjecutora = $arrayFirmante["selectUnidadEjecutora"];
    $totalFirmas     = $arrayFirmante["totalFirmas"];
    $arrayFirmas     = $arrayFirmante["firmas"];
    $idFirma         = $_POST["idFirma"];
     
    
    $EliminarFirmantes = fnInactivarFirmantes($idFirma,$db);
    if ( $EliminarFirmantes) {
        for ($x=0;$x<$totalFirmas; $x++){
            $idFirmaDetalle    = $arrayFirmas[$x]  ; 
            $values            = "($idFirma,$idFirmaDetalle)";

            $SQL = "INSERT INTO tb_reporte_firmas (id_nu_reportes_conac_firmas,id_nu_detalle_firmas )
                VALUES ". $values;   
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        }
        if ($TransResult)
            $result   = true; 
    }
}
if ( $option == 'obtenerFirmantesModificar'){
    $ue        = $_POST["ue"];
    $idFirma   = $_POST["idFirma"];
    $contador  = 1; 
    $SQL     = "  SELECT tab2.id_nu_detalle_firmas,id_nu_empleado,nombre_completo,titulo,informacion,firmados 
                    FROM  
                    ( SELECT  det.id_nu_detalle_firmas,'firmados'
                        FROM tb_detalle_firmas det, tb_reportes_conac_firmas conac, tb_reporte_firmas firmas     
                        WHERE det.ind_activo= 1 AND conac.id_nu_reportes_conac_firmas = firmas.id_nu_reportes_conac_firmas AND firmas.id_nu_detalle_firmas = det.id_nu_detalle_firmas
                        AND conac.id_nu_reportes_conac_firmas =  $idFirma AND firmas.ind_activo = 1) tab1  RIGHT JOIN 
                        (SELECT id_nu_detalle_firmas,id_nu_empleado,
                                        (SELECT CONCAT( ln_nombre ,' ', sn_primer_apellido,' ',sn_segundo_apellido) AS nombre FROM tb_empleados WHERE  id_nu_empleado = tb_detalle_firmas.id_nu_empleado) AS nombre_completo,
                                        titulo,informacion
                                        FROM tb_detalle_firmas
                                        WHERE ue = '$ue' and ind_activo = 1) tab2 ON tab1.id_nu_detalle_firmas = tab2.id_nu_detalle_firmas order by firmados desc";
   
    $ErrMsg       = "No se obtuvieron los botones para el proceso";
    $TransResult  = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        if ( $myrow ['firmados'] == null ){
            $checkbox   =false;
        }else{
            $checkbox   =true;
        }
        $info[] = array(
            'idRow'           => $myrow ['id_nu_detalle_firmas'],
            'contador'           => $contador,
            'nombre'             => $myrow ['nombre_completo'],
            'titulo'             => $myrow ['titulo'],
            'informacion'        => $myrow ['informacion'],
            'Modificar'          => '<a href="javascript:modificarFirmante('.$myrow ['id_nu_detalle_firmas'].');"><span class="glyphicon glyphicon-edit"></span></a>',
            'agregados'          => $checkbox 
        );
        $contador++; 

    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idRow', type: 'string' },";
    $columnasNombres .= "{ name: 'contador', type: 'string' },";
    $columnasNombres .= "{ name: 'nombre', type: 'string' },";
    $columnasNombres .= "{ name: 'titulo', type: 'string' },";
    $columnasNombres .= "{ name: 'informacion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'agregados', type: 'bool' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'Sel', datafield: 'agregados',editable:true,columntype: 'checkbox', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'No.', datafield: 'contador', width: '5%', cellsalign: 'left', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'idRow', datafield: 'idRow', width: '5%', cellsalign: 'left', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'nombre', width: '40%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Titulo', datafield: 'titulo', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Información', datafield: 'informacion', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'firmas'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;

}
if ( $option == 'obtenerFirmantesVista'){
    $idFirma        = $_POST["idFirma"];
    $sqlWhere       = " AND conac.id_nu_reportes_conac_firmas = $idFirma AND firmas.ind_activo = 1"; 
    $contador       = 1; 
    $SQL     = "SELECT  
            (SELECT CONCAT( ln_nombre ,' ', sn_primer_apellido,' ',sn_segundo_apellido) AS nombre FROM tb_empleados WHERE  id_nu_empleado = det.id_nu_empleado) AS nombre,
            titulo,informacion
        FROM tb_detalle_firmas det, tb_reportes_conac_firmas conac, tb_reporte_firmas firmas  
        WHERE conac.id_nu_reportes_conac_firmas = firmas.id_nu_reportes_conac_firmas AND firmas.id_nu_detalle_firmas = det.id_nu_detalle_firmas  $sqlWhere";
   
    $ErrMsg       = "No se obtuvieron los botones para el proceso";
    $TransResult  = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $info[] = array(
            'contador'           => $contador,
            'nombre'             => $myrow ['nombre'],
            'titulo'             => $myrow ['titulo'],
            'informacion'        => $myrow ['informacion']
        );
        $contador++; 

    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'contador', type: 'string' },";
    $columnasNombres .= "{ name: 'nombre', type: 'string' },";
    $columnasNombres .= "{ name: 'titulo', type: 'string' },";
    $columnasNombres .= "{ name: 'informacion', type: 'string' },";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'No.', datafield: 'contador', width: '5%', cellsalign: 'left', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'nombre', width: '60%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Titulo', datafield: 'titulo', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Información', datafield: 'informacion', width: '20%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'firmas'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;

}
if ($option == 'obtenerFirmantes') {
    $ur              = $_POST["ur"];
    $ue              = $_POST["ue"];
    $ligaVer         = "";
    $ligaMod         = "";
    $sqlWhere        = ""; 
    $contador        = 1;

    $longUr  = strlen ($ur); 
    if ($longUr > 2){
        $sqlWhere .= "  WHERE  ur = '$ur'";
    }

    $subUe    = substr ($ue , 0, 1 ); 
    if ($subUe != "-" ){
    $sqlWhere        .= " AND ue = '$ue' AND ind_activo = 1 "; 
    }

    $SQL = " SELECT id_nu_detalle_firmas,id_nu_empleado,
                    (Select CONCAT( ln_nombre ,' ', sn_primer_apellido,' ',sn_segundo_apellido) as nombre FROM tb_empleados where  id_nu_empleado = tb_detalle_firmas.id_nu_empleado) as nombre_completo,
                    titulo,informacion
                    FROM tb_detalle_firmas $sqlWhere"; 

    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {

        $enc        = new Encryption;
        $urlVer     = "id=".$myrow['id_nu_detalle_firmas']."&ver=1";
        $ligaVer    =  $urlVer;

        $urlMod        = "id=".$myrow['id_nu_detalle_firmas']."&modificar=1";
        $ligaMod       =  $urlMod;

        $info[] = array(
            'idRow'              => $myrow ['id_nu_detalle_firmas'],
            'contador'           => $contador,
            'nombre'             => $myrow ['nombre_completo'],
            'titulo'             => $myrow ['titulo'],
            'informacion'        => $myrow ['informacion'],
            'Modificar'          => '<a href="javascript:modificarFirmante('.$myrow ['id_nu_detalle_firmas'].');"><span class="glyphicon glyphicon-edit"></span></a>',
            //'Agregar'            => '<input type=checkbox name="firma[]" value='.$myrow ['id_nu_detalle_firmas'].' id= "firma" class="firmas" /> '
            'agregados'            => false
        );
        $contador++; 

    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idRow', type: 'string' },";
    $columnasNombres .= "{ name: 'contador', type: 'string' },";
    $columnasNombres .= "{ name: 'nombre', type: 'string' },";
    $columnasNombres .= "{ name: 'titulo', type: 'string' },";
    $columnasNombres .= "{ name: 'informacion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'agregados', type: 'bool' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'Sel', datafield: 'agregados',editable:true,columntype: 'checkbox', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'No.', datafield: 'contador', width: '5%', cellsalign: 'left', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'idRow', datafield: 'idRow', width: '5%', cellsalign: 'left', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'nombre', width: '40%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Titulo', datafield: 'titulo', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Información', datafield: 'informacion', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false }";

     $columnasNombresGrid .= "]";

    $nombreExcel = 'firmas'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;

}
function fnObtenerIdReporte($reportes,$db){
    $resultReporte     = 0; 
    $SQL    = " SELECT id_nu_reportes_conac FROM tb_cat_reportes_conac WHERE ln_reporte ='$reportes'";
    $ErrMsg = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {

        $resultReporte =  $myrow ['id_nu_reportes_conac'];
     }
     return $resultReporte; 
}
function fnMaxConacFirmas($db){
    $resultMax   = 0;
    $SQL        = " SELECT MAX(id_nu_reportes_conac_firmas) as id_nu_reportes_conac_firmas FROM tb_reportes_conac_firmas ";
    $ErrMsg = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {

        $resultMax =  $myrow ['id_nu_reportes_conac_firmas'];
     }
     return $resultMax; 
} 
function fninsertarReportesConac($unidadNegocio,$unidadEjecutora, $reportes,$db){
    $resultInsert     = false; 
    $idReporte        = fnObtenerIdReporte($reportes,$db); 
    $values           = "($idReporte,'$unidadNegocio','$unidadEjecutora',1)";
    $SQL = "INSERT INTO tb_reportes_conac_firmas (id_nu_reportes_conac,ur, ue,ind_activo)
    VALUES ". $values; 
    $InsResult = DB_query($SQL, $db);

    if($InsResult){ 
        $resultInsert = true;

    }
    
    return $resultInsert; 
}
function fnInactivarFirmantes($idFirma,$db){
    $resultInactivar = false;  
    $SQL             = "UPDATE tb_reporte_firmas  SET ind_activo= 0 where id_nu_reportes_conac_firmas =$idFirma ";
    $ErrMsg          = "No se obtuvo Información";
    $TransResult     = DB_query($SQL, $db, $ErrMsg);
    $resultInactivar = true;
    return $resultInactivar;
}


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
