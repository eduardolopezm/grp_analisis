<?php
/**
 * ABC Geografico o entidad federativa
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 03/10/2017
 * Se realizan operación pero el Alta, Baja y Modificación, conforme a las validaciones creadas para la operación seleccionada
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
require $PathPrefix.'abajo.php';
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
/*if ($abajo) {
    include $PathPrefix . 'includes/LanguageSetup.php';
} */
$funcion=2311;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$columnasNombres = "";
$columnasNombresGrid = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['proceso'];

	switch ($option) {
			case 'layoutsQueExisten':
			
			        $datos= array();

			        $SQL = "SELECT DISTINCT tb_layouts.nu_funcion,tb_layouts.nu_tipo_doc, sec_functions.title ,systypesinvtrans.typename 
						FROM tb_layouts  
						INNER JOIN sec_functions ON tb_layouts.nu_funcion=sec_functions.functionid
						INNER JOIN systypesinvtrans ON tb_layouts.nu_tipo_doc=systypesinvtrans.typeid";
			        $ErrMsg = "No se obtuvo datos";

			        $TransResult = DB_query($SQL, $db, $ErrMsg);
					while ($myrow = DB_fetch_array($TransResult)) {
				          $datos[] = array( 'value' => $myrow ['nu_funcion'].'_'.$myrow ['nu_tipo_doc'], 'texto' => $myrow ['title'].':'.$myrow ['typename'] );
				        }
				       // print_r($datos);
			        $contenido = array('datos' =>$datos);
			        $result = true;
			break;

			case 'documentosQueExisten':
			
			        $datos= array();

			        //$SQL = "SELECT DISTINCT  nu_tipo_sys AS  value,systypesinvtrans.typename AS texto   FROM tb_archivos INNER JOIN  systypesinvtrans ON tb_archivos.nu_tipo_sys=systypesinvtrans.typeid  WHERE nu_tipo_sys>0 ORDER  BY nu_tipo_sys ASC";
			        
			        $SQL="SELECT DISTINCT  tb_layouts.nu_tipo_doc AS  value,systypesinvtrans.typename AS texto ,tb_layouts.nu_funcion, sec_funxprofile.profileid,tb_archivos.nu_tipo_sys 
FROM tb_archivos 
INNER JOIN  tb_layouts ON tb_archivos.nu_funcion=tb_layouts.nu_funcion
INNER JOIN  systypesinvtrans ON tb_layouts.nu_tipo_doc=systypesinvtrans.typeid  

INNER JOIN sec_profilexuser ON sec_profilexuser.userid = '". $_SESSION['UserID']."'
INNER JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid /*saco el  numero de perfil de acuerdo al id session */

WHERE  sec_funxprofile.functionid IN (SELECT DISTINCT nu_funcion FROM tb_layouts)/*FUNCIONES QUE generan layout */
AND nu_tipo_sys>0 
ORDER  BY nu_tipo_sys ASC";
			        $ErrMsg = "No se obtuvo datos";

			        $TransResult = DB_query($SQL, $db, $ErrMsg);
					while ($myrow = DB_fetch_array($TransResult)) {
				          $datos[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
				        }
				       // print_r($datos);
			        $contenido = array('datos' =>$datos);
			        $result = true;
			break;

			case 'filtrar':
			  $dateHasta=$_POST['dateHasta'];
			  $dateDesde=$_POST['dateDesde'];
			  $eslayout=$_POST['eslayout'];
			  
			  $tipo=$_POST['tipo'];
			  $SQLTIPO='';
			  $SQLESLAYOUT='';

			  if($tipo!='0'){
			  	$SQLTIPO.=" AND nu_tipo_sys='".$tipo."'";
			  }else{
			  	$SQLTIPO.='';
			  }
			 // if($eslayout=='true'){
			  	$SQLESLAYOUT.=" AND ind_es_layout='"."1"."'";
			  //}

			  $datos= array();
			  $SQL=" SELECT nu_id_documento,ln_userid,sn_tipo,ln_nombre,txt_url,dtm_fecharegistro,nu_funcion,sec_functions.title,nu_tipo_sys,systypesinvtrans.typename FROM tb_archivos  
						INNER JOIN sec_functions ON tb_archivos.nu_funcion=sec_functions.functionid 
						INNER JOIN systypesinvtrans ON tb_archivos.nu_tipo_sys=systypesinvtrans.typeid WHERE tb_archivos.dtm_fecharegistro BETWEEN  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d') AND STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d') ".$SQLTIPO.$SQLESLAYOUT;

		       $ErrMsg = "Sin archivos";

		        $TransResult = DB_query($SQL, $db, $ErrMsg);
		        while ( $myrow = DB_fetch_array($TransResult) ){
		            
		            $tipo=explode(".",  $myrow ['txt_url']);
		            $tipo=$tipo[1]; //'<input type="checkbox" value="'.$myrow['nu_id_documento'].'" class="datosArchivos" name="datoArchivo">'
		              $info[] = array( 'cajacheckbox'=>false,'id'=>$myrow['nu_id_documento'],'tipo' =>   $tipo,'nombre' => $myrow ['ln_nombre'],'funcion'=>$myrow['title'],'tipo_doc'=>$myrow['typename'],'usuario' => $myrow ['ln_userid'],'fecha'=>date("d-m-Y", strtotime($myrow['dtm_fecharegistro'])));

		        }
        $contenido = array('DatosArchivos' => $info);
        $result = true;

			break;
			    
			default:
			    // codigo futuro...
			    break;
	}

$dataObj = array('SQL'=>'','contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>