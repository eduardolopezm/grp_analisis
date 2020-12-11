<?php
/**
 * Modelo para el ABC de Ramo
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */
 
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2246;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

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

if ($option == 'guardarClaveNueva') {
    $clave = $_POST['clave'];
    $nombreElementosClaveNueva = $_POST['nombreElementosClaveNueva'];
    $idClavePresupuesto = $_POST['idClavePresupuesto'];

    $info = array();
    $SQL = "SELECT accountcode FROM chartdetailsbudgetbytag WHERE accountcode = '".$clave."'";
    $ErrMsg = "No se obtuvo la consulta para comparar el presupuesto";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($transResult)==0) {
        $sqlCampos = "";
        $sqlValores = "";
        foreach ($nombreElementosClaveNueva as $datosClave) {
            if (empty($sqlCampos)) {
                $sqlCampos .= $datosClave['campoPresupuesto'];
                $sqlValores .= "'".$datosClave['valor']."'";
            } else {
                $sqlCampos .= ", ".$datosClave['campoPresupuesto'];
                $sqlValores .= ", '".$datosClave['valor']."'";
            }
        }
        
        $SQL = "INSERT INTO `chartdetailsbudgetbytag` 
			(`accountcode`, `budget`, `original`, `enero`, `febrero`,
			`marzo`, `abril`, `mayo`, `junio`, `julio`, 
			`agosto`, `septiembre`, `octubre`, `noviembre`, `diciembre`, 
			`idClavePresupuesto`, `sn_inicial`, `fecha_modificacion`, `fecha_captura`, `fecha_sistema`, 
			`txt_userid`, ".$sqlCampos.")
			VALUES
				('".$clave."', '0', '0', '0', '0',
				'0', '0', '0', '0', '0',
				'0', '0', '0', '0', '0', 
				'".$idClavePresupuesto."', '2', NOW(), NOW(), NOW(), 
				'".$_SESSION['UserID']."', ".$sqlValores."
				)";
        $ErrMsg = "No se agrego la Clave Presupuestal";
        $transResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = 'La Clave Presupuestal se agrego correctamente';
        $result = true;
    } else {
        $contenido = 'La Clave ya existe en el sistema, no se puede agregar';
        $result = false;
    }
}

if ($option == 'obtenerClavesNuevas') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];

    $sqlWhere = "";
    if ($legalid != "" and $legalid != '-1') {
        $sqlWhere .= " AND legalbusinessunit.legalid = '".$legalid."' ";
    }
    if ($tagref != "" and $tagref != '-1') {
        $sqlWhere .= " AND tags.tagref = '".$tagref."' ";
    }

    $info = array();
    $SQL = "
        SELECT
        www_users.realname,
        DATE_FORMAT(chartdetailsbudgetbytag.fecha_modificacion, '%d-%m-%Y') as fechaMod,
        tags.tagdescription,
        chartdetailsbudgetbytag.*
        FROM chartdetailsbudgetbytag
        JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
        JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
        JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
        LEFT JOIN www_users ON www_users.userid = chartdetailsbudgetbytag.txt_userid
        WHERE 
        sec_unegsxuser.userid = '".$_SESSION['UserID']."' 
        AND chartdetailsbudgetbytag.sn_inicial = 2 ".$sqlWhere;
    $ErrMsg = "No se obtuvieron los Presupuestos para la Búsqueda";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $dato['accountcode'] = $myrow ['accountcode'];
        $dato['fechaMod'] = $myrow ['fechaMod'];
        $dato['realname'] = $myrow ['realname'];
        $dato['tagdescription'] = $myrow ['tagdescription'];
        $info[] = $dato;
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'accountcode', type: 'string' },";
    $columnasNombres .= "{ name: 'fechaMod', type: 'string' },";
    $columnasNombres .= "{ name: 'tagdescription', type: 'string' },";
    $columnasNombres .= "{ name: 'realname', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'accountcode', width: '40%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Agrego', datafield: 'fechaMod', width: '10%', hidden: false },";
    $columnasNombresGrid .= " { text: 'URG', datafield: 'tagdescription', width: '25%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Usuario', datafield: 'realname', width: '25%', hidden: false }";
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;
}

if ($option == 'datosConfiguracionClave') {
    $idClavePresupuesto = $_POST['idClavePresupuesto'];
    $info = array();
    $SQL = "SELECT 
			campoPresupuesto,
			tabla,
			campo,
			nombre,
			txt_sql_nueva,
            nu_tam_est_ejer
			FROM budgetConfigClave 
			WHERE idClavePresupuesto = '".$idClavePresupuesto."' ORDER BY orden ASC ";
    $ErrMsg = "No se obtuvo la configuración del presupuesto detallada";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $infoSelect = array();

        if ($myrow ['campoPresupuesto'] == 'anho') {
            $infoSelect[] = array( 'value' => date('Y'), 'texto' => '' );
        } else {
            //$SQL = "SELECT ".$myrow ['campo']." FROM ".$myrow ['tabla']." GROUP BY ".$myrow ['campo']." ORDER BY ".$myrow ['campo'];
            if (!empty(trim($myrow ['txt_sql_nueva']))) {
                $SQL = $myrow ['txt_sql_nueva'];
                $ErrMsg = "No se obtuvo la configuración de ".$myrow ['nombre']." ";
                $transResultConfig = DB_query($SQL, $db, $ErrMsg);
                while ($myrowConfig = DB_fetch_array($transResultConfig)) {
                    //$infoSelect[] = array( 'value' => $myrowConfig [$myrow ['campo']], 'texto' => $myrowConfig [$myrow ['campo']] );
                    $infoSelect[] = array( 'value' => $myrowConfig ['value'], 'texto' => $myrowConfig ['texto'] );
                }
            } else {
                $infoSelect[] = array( 'value' => '', 'texto' => 'Sin Configuración' );
            }
        }

        $info[] = array( 'campoPresupuesto' => $myrow ['campoPresupuesto'], 'tabla' => $myrow ['tabla'], 'campo' => $myrow ['campo'], 'nombre' => $myrow ['nombre'], 'tamEstEjercicio' => $myrow ['nu_tam_est_ejer'], 'infoSelect' => $infoSelect );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarConfiguracionClave') {
    $info = array();
    $SQL = "SELECT DISTINCT idClavePresupuesto as value, CONCAT(idClavePresupuesto, ' - ', nombreConfig) as texto, idClavePresupuesto 
    FROM budgetConfigClave WHERE sn_activo = '1' ORDER BY idClavePresupuesto ASC";
    $ErrMsg = "No se obtuvo la configuración del presupuesto";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != '-1' && !empty($legalid)) {
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

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
