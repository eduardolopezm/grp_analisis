<?php
/**
 * Modelo para el ABC de Finalidad
 *
 * @category ABC
 * @package ap_grp
 * @author Luis Aguilar Sandoval <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

 //ini_set('display_errors', 1);
 //ini_set('log_errors', 1);
 //error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

session_start();
$PageSecurity = 4;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
/*
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix.'abajo.php');
include 'includes/DefinePOClass.php';
include 'includes/session.inc';

include 'includes/header.inc';
$funcion = 29;

if ($abajo) {
include($PathPrefix . 'includes/LanguageSetup.php');
}

include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc'); */

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion = 2265;
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
$proveedor = '111111';
$cvePresupuestal = 'ClavePresupuestal';
$usuarioIniciador = $_SESSION['UserID'];
$usuarioAutorizador = 'Autorizador';
$statusRequisicion = 'Creada';
$periodo = GetPeriod(date('d/m/Y'), $db);
$type = 19;


if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != 0 && !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
            FROM sec_unegsxuser u,tags t 
            join areas ON t.areacode = areas.areacode  
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' " . $sqlWhere . "
            ORDER BY t.tagref, areas.areacode ";
    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'tagref' => $myrow ['tagref'], 'tagdescription' => $myrow ['tagdescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerBotones') {
    $info = array();
    $SQL = "SELECT 
            tb_botones_status.statusid,
            tb_botones_status.statusname,
            tb_botones_status.namebutton,
            tb_botones_status.functionid,
            tb_botones_status.adecuacionPresupuestal,
            tb_botones_status.clases
            FROM tb_botones_status
            JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE 
            (tb_botones_status.sn_captura_requisicion = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid)
            )
            GROUP BY tb_botones_status.functionid
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'statusid' => $myrow ['statusid'],
            'statusname' => $myrow ['statusname'],
            'namebutton' => $myrow ['namebutton'],
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidaCveArticulo') {
    $cvePA = $_POST['dato'];
    $tagref = $_POST['datotagref'];
    $orderno = $_POST['orderno'];
    $info = array();
    $SQL= "SELECT tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,    
            tb_partida_articulo.descPartidaEspecifica AS descPartidaEspecifica, stockmaster.stockid AS idProducto, 
            stockmaster.description AS descripcionProducto, stockmaster.units AS unidad, clave.cvefrom AS cvePresupuestal
            FROM tb_partida_articulo
            INNER JOIN stockmaster ON tb_partida_articulo.eq_stockid= stockmaster.eq_stockid
            INNER JOIN (SELECT DISTINCT cvefrom FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.tagref='$tagref' AND chartdetailsbudgetlog.partida_esp= '$cvePA' AND chartdetailsbudgetlog.period= '$periodo') AS clave ON 1=1
            WHERE  tb_partida_articulo.partidaEspecifica = '$cvePA' AND stockmaster.mbflag = 'B' 
            AND stockmaster.stockid NOT IN (SELECT itemcode FROM purchorderdetails WHERE orderno='".$orderno."' AND STATUS NOT IN (0,3))
            ORDER BY idProducto";

    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idProducto' => $myrow ['idProducto'],
            'descripcionProducto' => $myrow ['descripcionProducto'],
            'unidad' => $myrow ['unidad'],
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'cvePresupuestal' => $myrow ['cvePresupuestal']  );
    }

    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'mostrarCveArticuloDatos') {
    $cveArt = $_POST['datocveart'];
    $descArt = $_POST['datodescart'];
    $tagref = $_POST['datotagref'];
    $ordenacion= "idProducto";
    $info = array();

    if (!empty($_POST['datodescart'])) {
        $cveArt = $_POST['datodescart'];
        $ordenacion= "descripcionProducto";
    }

    $SQL= "SELECT stockmaster.stockid AS idProducto, 
            stockmaster.description AS descripcionProducto, 
            stockmaster.units AS unidad,
            IFNULL(ultimo_costo.lastcost, 0) AS precioEstimado, 
            tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,
            tb_partida_articulo.descPartidaEspecifica AS descPartidaEspecifica, 
            existencia.quantity AS existencia
            FROM stockmaster 
            JOIN tb_partida_articulo ON (stockmaster.eq_stockid =  tb_partida_articulo.eq_stockid)
            LEFT JOIN (SELECT stockcostsxlegal.stockid, stockcostsxlegal.lastcost
            FROM sec_loccxusser 
            INNER JOIN locations USING (loccode)
            INNER JOIN tags ON locations.tagref= tags.tagref
            INNER JOIN stockcostsxlegal ON tags.legalid= stockcostsxlegal.legalid
            WHERE sec_loccxusser.userid='".$_SESSION['UserID']."') ultimo_costo ON stockmaster.stockid= ultimo_costo.stockid
            LEFT JOIN (SELECT locstock.loccode, locstock.stockid, locstock.quantity
            FROM locstock
            INNER JOIN sec_loccxusser USING (loccode)
            WHERE sec_loccxusser.userid= '".$_SESSION['UserID']."') existencia ON stockmaster.stockid= existencia.stockid
            WHERE (stockmaster.stockid= '".$cveArt."' OR stockmaster.description LIKE '%".$cveArt."%') ORDER BY ".$ordenacion;

    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idProducto' => $myrow ['idProducto'],
            'descripcionProducto' => $myrow ['descripcionProducto'],
            'unidad' => $myrow ['unidad'],
            'precioEstimado' => $myrow ['precioEstimado'],
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'existencia' => $myrow ['existencia'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}
if ($option == 'obtenerPresupuesto') {
    $clave = $_POST['clave'];

    $period = GetPeriod(date('d/m/Y'), $db);

    $nombreMes = "";
    $SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' AND periods.periodno = '".$period."'
            ORDER BY periods.lastdate_in_period asc";
    $resultPeriods = DB_query($SQL, $db, $ErrMsg);
    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $res = true;

    $info = fnInfoPresupuesto($db, $clave, $period);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info, 'nombreMes' => $nombreMes);
    $result = $res;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);
