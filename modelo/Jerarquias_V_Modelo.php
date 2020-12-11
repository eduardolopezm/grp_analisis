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
 * Fecha Creación: 05/07/2018

 * Se realizan operación pero el Alta, Baja y Modificación Jerarquia
 */


session_start();
$PageSecurity = 11;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

$funcion = 2402;
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


if ($option == 'modificarJerarquia') {
    $arrayJerarquia = $_POST['arrayJerarquia'];
    for ($a=0; $a<count($arrayJerarquia); $a++) {
        $jerarquia                = $arrayJerarquia['jerarquia'];
        $tipoSol                  = $arrayJerarquia['tipoSol'];
        $tipoGasto                = $arrayJerarquia['tipoGasto'];
        $monto                    = $arrayJerarquia['monto'];
        $id_nu_monto_jerarquia    = $arrayJerarquia['id_nu_monto_jerarquia'];
        
         
        $cadena = "amt_importe = '".$monto."'";
    }
 
    $sql = "UPDATE tb_monto_jerarquia SET ".$cadena." WHERE id_nu_monto_jerarquia = '".$id_nu_monto_jerarquia."'" ;
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    $contenido = $stockid;
    $result = true;
}

if ($option == 'guardarJerarquia') {
    $arrayJerarquia = $_POST['arrayJerarquia'];
    $result         = false; 
    $fechaHoy       = date('Y-d-m');

    $jerarquia                = $arrayJerarquia['jerarquia'];
    $tipoSol                  = $arrayJerarquia['tipoSol'];
    $tipoGasto                = $arrayJerarquia['tipoGasto'];
    $monto                    = $arrayJerarquia['monto']; 
         
    $cadena = "($jerarquia ,$monto,$tipoSol,1,'".$fechaHoy."','".$fechaHoy."',$tipoGasto)";
    $validarExistente = fnValidarExistente($arrayJerarquia, $db); 
 
    if (!$validarExistente) {
        $validarInactivo  = fnValidarInactivo($arrayJerarquia, $db); 

        if ($validarInactivo > 0){
            $sql = " UPDATE tb_monto_jerarquia SET ind_activo = 1, amt_importe = $monto  WHERE id_nu_monto_jerarquia = $validarInactivo ";
            $InsResult = DB_query($sql, $db);
            $result =true; 
        }else{
            $sql = "INSERT INTO tb_monto_jerarquia (
                id_nu_jerarquia,
                amt_importe,
                ind_tipo,
                ind_activo,
                dtm_fecha_alta,
                dtm_fecha_actualizacion,
                id_zona_economica )
                VALUES ". $cadena; 
                $InsResult = DB_query($sql, $db);
                $result =true; 
        }
    }
}

if ($option == 'obtenerDatos') {
    $idJerarquia = $_POST['idJerarquia'];
    $contenido = array('datos' => fnDatos($idJerarquia, $db));
    $result = true;
}


if ($option == 'mostrarJerarquia') {
    $info = array();
    $SQL = "SELECT id_nu_jerarquia as value, ln_descripcion as texto
        FROM tb_cat_jerarquia
        WHERE ind_activo=1
        ORDER BY ln_descripcion ASC";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoGasto') {
    $info = array();
    $SQL = "SELECT id_nu_zona_economica as value,  ln_descripcion as texto FROM tb_cat_zonas_economicas WHERE ind_activo=1 ORDER BY ln_descripcion";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoSol') {
    $sql        = "Sin Query"; 
    $tipoSol[]  = array( 'value' =>1, 'texto' =>"Nacional" );
    $tipoSol[]  = array( 'value' =>2, 'texto' =>"Internacional" );
    $result     = true; 
    $contenido  = array('datos' => $tipoSol);
}


function fnValidarExistente($arrayJerarquia,$db){
    $validarExistente         = false; 
    $sqlWhere       = ''; 

    $jerarquia                = $arrayJerarquia['jerarquia'];
    $tipoSol                  = $arrayJerarquia['tipoSol'];
    $tipoGasto                = $arrayJerarquia['tipoGasto'];
    $monto                    = $arrayJerarquia['monto'];
    $sqlWhere .= " ind_activo= 1 AND id_nu_jerarquia = $jerarquia ";

    if ($tipoSol != '') {
        $sqlWhere .= " AND ind_tipo = $tipoSol";
    }
    if ($tipoGasto > 0  ) {

        $sqlWhere .= " AND id_zona_economica = $tipoGasto";
        
    }else{
        $sqlWhere .= " AND id_zona_economica is null";
    }
    $SQL = "SELECT id_nu_monto_jerarquia  FROM tb_monto_jerarquia WHERE $sqlWhere";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($TransResult);
     
    if ($myrow[0]>0) {
        $contenido = 1;
        $validarExistente = true;
    }
    return $validarExistente; 
}
function fnValidarInactivo($arrayJerarquia,$db){
    $validarExistente         = 0; 
    $sqlWhere                 = ''; 

    $jerarquia                = $arrayJerarquia['jerarquia'];
    $tipoSol                  = $arrayJerarquia['tipoSol'];
    $tipoGasto                = $arrayJerarquia['tipoGasto'];
    $monto                    = $arrayJerarquia['monto'];

    $sqlWhere .= " ind_activo= 0 AND  id_nu_jerarquia = $jerarquia ";

    if ($tipoSol != '') {
        $sqlWhere .= " AND ind_tipo = $tipoSol";
    }
    if ($tipoGasto > 0  ) {

        $sqlWhere .= " AND id_zona_economica = $tipoGasto";
        
    }else{
        $sqlWhere .= " AND id_zona_economica is null";
    }
    $SQL = "SELECT id_nu_monto_jerarquia  FROM tb_monto_jerarquia WHERE $sqlWhere";
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($TransResult);
     
    if ($myrow[0]>0) {
       
        $validarExistente    = $myrow["id_nu_monto_jerarquia"];
    }
    return $validarExistente; 
}


function fnDatos($idJerarquia, $db)
{
    $arrayDatos = array();
    $sql = "SELECT id_nu_monto_jerarquia,id_nu_jerarquia,
                   amt_importe,ind_tipo,id_zona_economica
            FROM tb_monto_jerarquia s
            WHERE id_nu_monto_jerarquia = '$idJerarquia'";
    $result = DB_query($sql, $db);

    if (DB_num_rows($result) > 0) {
        while ($myrow = DB_fetch_array($result)) {
           
            $arrayDatos[] = array(
                'id_nu_monto_jerarquia' => $myrow ['id_nu_monto_jerarquia'],
                'id_nu_jerarquia' => $myrow ['id_nu_jerarquia'],
                'amt_importe' => number_format($myrow ["amt_importe"], 2, '.', ''),
                'ind_tipo' => $myrow ['ind_tipo'],
                'id_zona_economica' => $myrow ['id_zona_economica']);
        }
    } else {
        $arrayDatos = "";
    }
    
    return $arrayDatos;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);
