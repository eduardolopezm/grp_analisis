<?php
/**
 * Modelo Captura de Póliza Manual
 *
 * @category modelo
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2017
 * Fecha Modificación: 06/11/2017
 * Modelo Captura de Póliza Manual
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=371;
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

if ($option == 'mostrarIdentificadorCuentasLista') {
    $identificador = $_POST['identificador'];
    $sqlWhere = "1 = 1";
    if (trim($identificador) != '') {
        $sqlWhere = "`chartmaster`.`ln_clave` = '$identificador'";
    }
    
    // Primer array de datos, Nivel 1 - 6
    $info1 = array();
    $SQL = "SELECT DISTINCT `chartmaster`.`accountcode`, CONCAT(`chartmaster`.`accountcode`, ' - ', `chartmaster`.`accountname`) AS `accountname`, `chartmaster`.`group_` AS `padre`

            FROM `chartmaster`
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` LIKE '$_SESSION[UserID]'

            WHERE `chartmaster`.`nu_nivel` <= '6'
            AND ( `chartmaster`.`nu_nivel` <= '5'
            OR `chartmaster`.`ln_clave` = `tb_sec_users_ue`.`ue` )
            AND ( `chartmaster`.`nu_nivel` <= '5'
            OR $sqlWhere )
            AND `chartmaster`.`tipo` != '4'
            AND `chartmaster`.`tipo` != '5'
            ORDER BY `chartmaster`.`group_`, `chartmaster`.`accountcode`";
    $ErrMsg = "No se obtuvieron las Cuentas con Identificador";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info1[] = array( 'value' => $myrow ['accountcode'], 'texto' => $myrow ['accountname'] );
    }

    // Segundo array de datos, Nivel 7 - 9
    $info2 = array();
    $SQL = "SELECT DISTINCT `chartmaster`.`accountcode`, CONCAT(`chartmaster`.`accountcode`, ' - ', `chartmaster`.`accountname`) AS `accountname`, `chartmaster`.`group_` AS `padre`

            FROM `chartmaster`
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` LIKE '$_SESSION[UserID]'

            WHERE `chartmaster`.`nu_nivel` >= '7' AND `chartmaster`.`nu_nivel` <= '9'
            AND `chartmaster`.`ln_clave` = `tb_sec_users_ue`.`ue`
            AND $sqlWhere

            ORDER BY `chartmaster`.`group_`, `chartmaster`.`accountcode`";
    $ErrMsg = "No se obtuvieron las Cuentas con Identificador";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info2[] = array( 'value' => $myrow ['accountcode'], 'texto' => $myrow ['accountname'] );
    }

    // Tercer array de datos, Nivel 9 en adelante
    $info3 = array();
    $SQL = "SELECT DISTINCT `chartmaster`.`accountcode`, CONCAT(`chartmaster`.`accountcode`, ' - ', `chartmaster`.`accountname`) AS `accountname`, `chartmaster`.`group_` AS `padre`

            FROM `chartmaster`
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` LIKE '$_SESSION[UserID]'

            WHERE `chartmaster`.`nu_nivel` > '9'
            AND `chartmaster`.`ln_clave` = `tb_sec_users_ue`.`ue`
            AND $sqlWhere

            ORDER BY `chartmaster`.`group_`, `chartmaster`.`accountcode`";
    $ErrMsg = "No se obtuvieron las Cuentas con Identificador";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info3[] = array( 'value' => $myrow ['accountcode'], 'texto' => $myrow ['accountname'] );
    }

    $contenido = array('datos1' => $info1, 'datos2' => $info2, 'datos3' => $info3);
    $result = true;
}

if ($option == 'mostrarIdentificadorCuentas') {
    $identificador = $_POST['identificador'];
    $sqlWhere = "";
    if (trim($identificador) != '') {
        $sqlWhere = " AND ln_clave = '".$identificador."' ";
    }
    $info = array();
    $SQL = "SELECT accountcode, CONCAT(accountcode, ' - ', accountname) as accountname, group_ as padre
    FROM chartmaster
    WHERE nu_nivel = 4 ".$sqlWhere."
    ORDER BY group_, accountcode";
    $ErrMsg = "No se obtuvieron las Cuentas con Identificador";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['accountcode'], 'texto' => $myrow ['accountname'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option=="DeleteFile") {
    // Borrar archivo cargado para lo póliza
    $SQL="DELETE FROM gltrans_files WHERE id = '".$_POST['idFile']."'";
    DB_query($SQL, $db);

    $contenido = '<h4>Eliminación Correcta</h4>';
    $result = true;
}

if ($option == 'obtenerCuentas') {
    // Obtener datos de la cuenta para las polizas extra presupueatales
    $clave = trim($_POST['clave']);
    $cuentaCargo = "";
    $cuentaAbono = "";

    $SQL = "SELECT `tb_matriz_extraptal`.`stockact`, `tb_matriz_extraptal`.`accountegreso`, 
            `datoCargo`.`accountname` AS `accountnameCar`, `datoAbono`.`accountname` AS `accountnameAbo` 

            FROM `tb_matriz_extraptal`
            LEFT JOIN `chartmaster` AS `datoCargo` ON `datoCargo`.`accountcode` = `tb_matriz_extraptal`.`stockact`
            LEFT JOIN `chartmaster` AS `datoAbono` ON `datoAbono`.`accountcode` = `tb_matriz_extraptal`.`accountegreso`

            WHERE `tb_matriz_extraptal`.`id` = '$clave'";
    $ErrMsg = "No se Obtuvieron las cuentas ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $cuentaCargo = "$myrow[stockact] - $myrow[accountnameCar]";
        $cuentaAbono = "$myrow[accountegreso] - $myrow[accountnameAbo]";
    }

    $reponse['cuentaCargo'] = $cuentaCargo;
    $reponse['cuentaAbono'] = $cuentaAbono;

    $contenido = $reponse;
    $result = true;
}

if ($option == 'ultimoNivelCuenta') {
    // Validar si la cuenta es la del ultimo nivel
    $cuenta = $_POST['cuenta'];

    $ultimoNivel = 1;
    $SQL = "SELECT accountcode FROM chartmaster WHERE accountcode like '".$cuenta.".%'";
    $ErrMsg = "No se Obtuviero información de la cuenta ".$cuenta;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $ultimoNivel = 0;
    }

    $reponse['ultimoNivel'] = $ultimoNivel;
    $contenido = $reponse;
    $result = true;
}

if($option=="consultaMatriz"){
    // Obtenemos los elementos del identificador
    $UR = trim($_POST['ur']);
    $UE = trim($_POST['ue']);
    $PE = trim($_POST['pe']);

    $identificador = "$UR-$UE-$PE";

    $datosBusqueda = array();

    $SQL = "SELECT `mep`.`id` AS `valor`, `mep`.`categoryid`, CONCAT(`mep`.`categorydescription`,
            IF(`mep`.`categoryid`, CONCAT(' - ',`mep`.`categoryid`), ''),
            IF(`tb_matriz_extraptal_origen`.`txt_descripcion` IS NOT NULL, CONCAT(' - ',`tb_matriz_extraptal_origen`.`txt_descripcion`), '')
            ) AS `label`

            FROM `tb_matriz_extraptal` AS `mep`
            JOIN `tb_sec_users_ue` ON `mep`.`ln_clave` LIKE CONCAT('%-',`tb_sec_users_ue`.`ue`,'-%') AND `userid` LIKE '$_SESSION[UserID]'
            LEFT JOIN `tb_matriz_extraptal_origen` ON `tb_matriz_extraptal_origen`.`nu_reg` = `mep`.`factesquemadoancho`
            LEFT JOIN `tb_matriz_extraptal_proceso` ON `tb_matriz_extraptal_proceso`.`nu_reg` = `mep`.`factesquemadoalto`

            WHERE `mep`.`ln_clave` LIKE '$identificador'

            ORDER BY `label`";

    if($UR&&$UE&&$PE){
        $ErrMsg = "No se Obtuvieron registros con el identificador $identificador";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while($myrow = DB_fetch_array($TransResult)){
            $datosBusqueda[] = [
                'value' => utf8_encode($myrow['valor']),
                'text' => $myrow['label']
            ];
        }

        $result = true;
    }

    $reponse['datosBusqueda'] = $datosBusqueda;
    $contenido = $reponse;
}

if ($option == 'obtenerPresupuestosBusquedaEgresos') {

    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $tipo = $_POST['tipo'];

    $sqlWhere = "";
    
    if ($tagref != "" and $tagref != '-1') {
        $sqlWhere .= " AND tags.tagref = '".$tagref."' ";
    }

    if ($ue != "" and $ue != '-1') {
        $sqlWhere .= " AND tb_sec_users_ue.ue = '".$ue."' ";
    }

    if ($tipo != "" and $tipo != '-1') {
        $sqlWhere .= " AND budgetConfigClave.tipo_config = '".$tipo."' ";
    }
    


    $info = array();

    $SQL = "SELECT distinct
    chartdetailsbudgetbytag.*
    FROM chartdetailsbudgetbytag
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    JOIN budgetConfigClave ON chartdetailsbudgetbytag.idClavePresupuesto = budgetConfigClave.idClavePresupuesto
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = chartdetailsbudgetbytag.tagref AND tb_sec_users_ue.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE 
    chartdetailsbudgetbytag.anho = '".$_SESSION['ejercicioFiscal']."'
    AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."' ".$sqlWhere."
    ORDER BY chartdetailsbudgetbytag.accountcode ASC";
    $ErrMsg = "No se obtuvieron los Presupuestos para la Búsqueda";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $value = "";
        $SQL = "SELECT campoPresupuesto FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow['idClavePresupuesto']."' AND adecuacion_presupuesto = '1' ";
        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
        while ($myrow2 = DB_fetch_array($TransResult2)) {
            if (empty($value)) {
                $value = $myrow [$myrow2['campoPresupuesto']];
            } else {
                $value .= '-'.$myrow [$myrow2['campoPresupuesto']];
            }
        }

        $datos['value'] = $value;
        $datos['accountcode'] = $myrow ['accountcode'];
        $datos['valorLista'] = $value;

        $claveCorta = "";
        $SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
        $resultClave = DB_query($SQL, $db, $ErrMsg);
        while ($rowClave = DB_fetch_array($resultClave)) {
            if (empty($claveCorta)) {
                $claveCorta = $myrow [$rowClave ['campoPresupuesto']];
            } else {
                $claveCorta .= "-".$myrow [$rowClave ['campoPresupuesto']];
            }
        }

        $datos['claveCorta'] = $claveCorta;

        $claveLarga = "";
        $SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
        $resultClave = DB_query($SQL, $db, $ErrMsg);
        while ($rowClave = DB_fetch_array($resultClave)) {
            if (empty($claveLarga)) {
                $claveLarga = $myrow [$rowClave ['campoPresupuesto']];
            } else {
                $claveLarga .= "-".$myrow [$rowClave ['campoPresupuesto']];
            }
        }

        $datos['claveLarga'] = $claveLarga;

        $info[] = $datos;
    }

    $contenido = array('datos' => $info);
    $result = true;
}


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);