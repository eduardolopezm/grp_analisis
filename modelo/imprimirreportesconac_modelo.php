<?php
/**
 * Panel Visualizar Reportes
 *
 * @category Panel
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Visualizar reportes conac y ldf
 */

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2261;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

include $PathPrefix . "includes/SecurityUrl.php";
$enc = new Encryption;


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
$SQL = "";
# opcion a ser ejecutada
$option = $_POST['option'];

if ($option == 'cargarReportesConfigurados') {
    $result = true;
    $contenido = [];
    $conac = []; 
    $ldf = [];
    $ngrp = [[ 'label'=> 'Seleccione una opción', 'value'=> 0 ]];
    $sql = "SELECT DISTINCT `ln_reporte` as value, `ln_descripcion` as text, `sn_tipo` as tipo FROM `tb_cat_reportes_conac` WHERE `ind_activo` = '1' ORDER BY ind_orden";
    $resultset = DB_query($sql, $db);
    while ($rs = DB_fetch_array($resultset)) {
        if (empty($rs['tipo'])) {
            $ngrp[] = [ 'label'=> $rs['text'], 'value'=> $rs['value'] ];
            continue;
        }
        if ($rs['tipo'] == 'conac') {
            $conac[] = [ 'label'=> $rs['text'], 'value'=> $rs['value'] ];
            continue;
        }
        if ($rs['tipo'] == 'ldf') {
            $ldf[] = [ 'label'=> $rs['text'], 'value'=> $rs['value'] ];
            continue;
        }
    }
    $ngrp[] = ['label'=>'CONAC','children'=>$conac];
    $ngrp[] = ['label'=>'LDF','children'=>$ldf];
    $contenido = $ngrp;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != '0' && !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
            FROM sec_unegsxuser u,tags t 
            join areas ON t.areacode = areas.areacode  
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere." 
            ORDER BY t.tagref, areas.areacode ";
    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'tagref' => $myrow ['tagref'], 'tagdescription' => $myrow ['tagdescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}
/*
/* Encriptar URL
*/
if($option == 'encryptarURL'){
    $url = $_POST['url'];
    $url = $enc->encode($url);
    $liga= "URL=". $url;
    $Mensaje='PrintSituacionFinanciera.php?'.$liga;
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje, 'url' => $_POST['url']);
echo json_encode($dataObj);
