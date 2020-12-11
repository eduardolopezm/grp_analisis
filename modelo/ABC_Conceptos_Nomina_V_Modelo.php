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

$funcion = 3061;
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


if ($option == 'modificarConceptos') {
    $arrayConcepto = $_POST['arrayConceptos'];
    $concepto      = $arrayConcepto['concepto']; 
    $idConcepto    = $_POST['idConcepto']; 


    $sql = "UPDATE tb_cat_concepto_nomina SET desc_concepto = '$concepto' WHERE id_concepto_nomina = $idConcepto " ;
    $ErrMsg = "No se obtuvo los Tipos de Conceptos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    $contenido = $stockid;
    $result = true;
}

if ($option == 'guardarConcepto') {
    $arrayConcepto = $_POST['arrayConceptos'];
    $result         = false; 

    $pp                       = $arrayConcepto['pp'];
    $partida                  = $arrayConcepto['partida'];
    $claveConcepto            = $arrayConcepto['claveConcepto'];
    $concepto                 = $arrayConcepto['concepto']; 
    $cuentaContable           = $arrayConcepto['cuentaContable'];
    $tipoConcepto             = $arrayConcepto['tipoConcepto'];
         
    $cadena = "('".$pp."' ,$partida,$claveConcepto,'".$concepto."','".$tipoConcepto."','".$cuentaContable."',1)";
    $validarExistente = fnValidarExistente($arrayConcepto, $db); 

    if (!$validarExistente) {
        $validarInactivo  = fnValidarInactivo($arrayConcepto, $db); 

        if ($validarInactivo > 0){
            $sql = " UPDATE tb_cat_concepto_nomina SET activo = 1, desc_concepto = '$concepto', cta_contable = '$cuentaContable', tipo_concepto = '$tipoConcepto' WHERE pp = '$pp' AND partida =$partida AND clave_concepto = $claveConcepto  ";
            $InsResult = DB_query($sql, $db);
            $result =true; 
        }else{
            $sql = "INSERT INTO tb_cat_concepto_nomina (
                pp,
                partida,
                clave_concepto,
                desc_concepto,
                tipo_concepto,
                cta_contable,
                activo )
                VALUES ". $cadena; 

                $InsResult = DB_query($sql, $db);
                $result =true; 
        }
    }
}

if ($option == 'obtenerDatos') {
    $idConcepto = $_POST['idConcepto'];
    $contenido = array('datos' => fnDatos($idConcepto, $db));
    $result = true;
}


if ($option == 'mostrarPp') {
    $info = array();
    $SQL = "SELECT cppt as value, cppt as texto
    FROM tb_cat_programa_presupuestario
    WHERE id_clasprog <> 27
    ORDER BY cppt ASC";
    $ErrMsg = "No se obtuvo los Tipos de pp";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartida') {
    $info = array();
    $SQL = "SELECT partidacalculada as value,  partidacalculada as texto FROM tb_cat_partidaspresupuestales_partidaespecifica WHERE ccap=1 ORDER BY partidacalculada";
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


function fnValidarExistente($arrayConcepto,$db){
    $validarExistente         = false; 
    $sqlWhere       = ''; 

    $pp                       = $arrayConcepto['pp'];
    $partida                  = $arrayConcepto['partida'];
    $claveConcepto            = $arrayConcepto['claveConcepto'];
    $concepto                 = $arrayConcepto['concepto']; 
    $cuentaContable           = $arrayConcepto['cuentaContable'];
    $tipoConcepto             = $arrayConcepto['tipoConcepto'];
    $sqlWhere .= " AND pp = '$pp' AND partida =$partida AND clave_concepto = $claveConcepto  ";

    $SQL = " SELECT id_concepto_nomina  FROM tb_cat_concepto_nomina WHERE activo = 1 $sqlWhere";

    $ErrMsg = "No se obtuvo los conceptos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($TransResult);
     
    if ($myrow[0]>0) {
        $validarExistente = true;
    }
    return $validarExistente; 
}
function fnValidarInactivo($arrayConcepto,$db){
    $validarExistente         = 0; 
    $sqlWhere                 = ''; 

    $pp                       = $arrayConcepto['pp'];
    $partida                  = $arrayConcepto['partida'];
    $claveConcepto            = $arrayConcepto['claveConcepto'];
    $concepto                 = $arrayConcepto['concepto']; 
    $cuentaContable           = $arrayConcepto['cuentaContable'];
    $tipoConcepto             = $arrayConcepto['tipoConcepto'];

    
    $sqlWhere .= " AND pp = '$pp' AND partida =$partida AND clave_concepto = $claveConcepto  ";

    $SQL = "SELECT id_concepto_nomina  FROM tb_cat_concepto_nomina WHERE activo = 0  $sqlWhere ";
    $ErrMsg = "No se obtuvo los Conceptos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($TransResult);
     
    if ($myrow[0]>0) {
       
        $validarExistente    = $myrow["id_concepto_nomina"];
    }
    return $validarExistente; 
}


function fnDatos($idConcepto, $db)
{
    $arrayDatos = array();
    $sql = "SELECT pp, partida, clave_concepto, desc_concepto, tipo_concepto,cta_contable
            FROM tb_cat_concepto_nomina 
            WHERE id_concepto_nomina = $idConcepto";
    $result = DB_query($sql, $db);

    if (DB_num_rows($result) > 0) {
        while ($myrow = DB_fetch_array($result)) {
           
            $arrayDatos[] = array(
                'pp'            => $myrow ['pp'],
                'partida'       => $myrow ['partida'],
                'clave_concepto'=>$myrow ['clave_concepto'],
                'desc_concepto' => $myrow ['desc_concepto'],
                'tipo_concepto' => $myrow ['tipo_concepto'],
                'cta_contable'  => $myrow ['cta_contable']);
        }
    } else {
        $arrayDatos = "";
    }
    
    return $arrayDatos;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);
