<?php
/**
 * Modelo para proceso de compra
 *
 * @category     proceso de compra
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/12/2017
 * Fecha Modificación: 12/12/2017
 */
 // ini_set('display_errors', 1);
 // ini_set('log_errors', 1);
 // error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2291;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';


//$permiso = Havepermission ( $_SESSION ['UserID'], 244, $db ); // tenia 2006


$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$RootPath = "";
$Mensaje = "";
$a=1;
$SQL='';

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);
$info = array();
$proceso = $_POST['proceso'];

  
function fnObtenerDatosFormu(){
    $dataNombres =array();
    $dataValores=array();
    $datos=array();
    foreach ($_POST as $nombrePara => $valPara){
    $dataNombres += [$nombrePara => $valPara];
    $dataValores+=[$valPara];
 
    }
    $datos[]= $dataNombres;
    $datos[]=$dataValores;
    return $datos; 
        
}
$proceso=$_POST['proceso'];

switch ($proceso) {
    case 'altaTipoAdju':
   $datos= fnObtenerDatosFormu();
   $datosNombVal=$datos[0];
   //$cadenaDatosInsertar= substr($cadenaDatosInsertar, 0, -1);
   $SQL = "INSERT INTO tb_configuracion_adjudicacion (nu_rango_inicial,nu_rango_tope,txt_descripcion_adjudicacion,ln_usuario,ln_tipo_adjudicacion)
        VALUES('".$datosNombVal["rangoinicial"]."','".$datosNombVal["rangotope"]."','".$datosNombVal["descripcionadjudicacion"]."','".$_SESSION ["UserID"]."','".$datosNombVal["idadjudicacion"]."')";
          $ErrMsg = "Problema al cargar documento";
          $TransResult = DB_query($SQL, $db, $ErrMsg);
          $contenido = 'La adjudicación <b>'.$datosNombVal["descripcionadjudicacion"].' </b> creada.';
          $result = true; 
    break;

    case 'altaCampo':
    
    $datos= fnObtenerDatosFormu();
   $datosNombVal=$datos[0];
   //$cadenaDatosInsertar= substr($cadenaDatosInsertar, 0, -1);
  
   $SQL = "INSERT INTO tb_configuracion_adjudicacion_formulario (ln_tipo_campo,ind_obligatorio,ln_nombre_del_campo,ln_leyenda_etiqueta,ln_maximo_permitido,nu_orden,ln_id_tipo_adjudicacion,ln_usuario)
        VALUES('".$datosNombVal["tipocampo"]."','".$datosNombVal["obligatorio"]."','".$datosNombVal["nombrecampo"]."','".$datosNombVal["leyenda"]."','".$datosNombVal["maximo"]."','".$datosNombVal["orden"]."','".$datosNombVal["typead"]."','".$_SESSION ["UserID"]."')";
          $ErrMsg = "Problema al cargar documento";
          $TransResult = DB_query($SQL, $db, $ErrMsg);
          $contenido = 'Se guardo el campo <b>'.$datosNombVal["descripcionadjudicacion"].' </b> .';
          $result = true; 

    break;

    case  'typeAdform':
    $datos=array();

    $SQL="SELECT * FROM tb_configuracion_adjudicacion";

    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
 
    while ($myrow = DB_fetch_array($TransResult)) {

        $datos[] = array('rangoinicial'=>$myrow['nu_rango_inicial'],
                  'rangotope' =>$myrow['nu_rango_tope'],
                   'descripcion' =>$myrow['txt_descripcion_adjudicacion'],
                   'fecha'=>$myrow['dtm_fecharegistro'],
                   'usuario'=>$myrow['ln_usuario']
                ); 
    }

    $nombre='configuracionProceso'; //traeNombreFuncionGeneral($funcion, $db);
    $nombre=str_replace(" ", "_", $nombre);
    $nombreExcel = $nombre.'_'.date('dmY');
    $contenido = array('datos' => $datos,'nombreExcel' => $nombreExcel);

    $result = true;

    break;

}



$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
