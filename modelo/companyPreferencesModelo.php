<?php
/**
 * Preferencías de la Empresa
 *
 * @category Configuración
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/09/2017
 * Fecha Modificación: 20/09/2017
 * Modelo de la configuración de movimientos contables
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
$funcion=87;
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

function fnObtenerDatosConfig($db, $cuentas = '', $ur = '', $ue = '')
{
    $info = array();

    $SQL = "SELECT
    chartmaster.accountcode,
    CONCAT(chartmaster.accountcode, ' - ', chartmaster.accountname) as accountname    
    FROM chartmaster
    WHERE
    chartmaster.accountcode like '".$cuentas."%'
    AND chartmaster.nu_nivel >= 4
    AND CASE WHEN chartmaster.nu_nivel < 5 THEN 1 = 1 ELSE chartmaster.tagref = '".$ur."' AND chartmaster.ln_clave = '".$ue."' END
    ORDER BY chartmaster.accountcode ASC";
    $ErrMsg = "No se obtuvieron los Presupuestos para la Búsqueda";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $datos['value'] = $myrow ['accountcode'];
        $datos['accountcode'] = $myrow ['accountcode'];
        $datos['accountname'] = $myrow ['accountname'];

        $info[] = $datos;
    }

    return $info;
}

if ($option == 'obtClavesConfig') {
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    
    $respuesta['ingresoEstimado'] = fnObtenerDatosConfig($db, '8.1.1', $ur, $ue);
    $respuesta['ingresoPorEjecutar'] = fnObtenerDatosConfig($db, '8.1.2', $ur, $ue);
    $respuesta['ingresoModificado'] = fnObtenerDatosConfig($db, '8.1.3', $ur, $ue);
    $respuesta['ingresoDevengado'] = fnObtenerDatosConfig($db, '8.1.4', $ur, $ue);
    $respuesta['ingresoRecaudado'] = fnObtenerDatosConfig($db, '8.1.5', $ur, $ue);

    $respuesta['egresoAprobado'] = fnObtenerDatosConfig($db, '8.2.1', $ur, $ue);
    $respuesta['egresoPorEjercer'] = fnObtenerDatosConfig($db, '8.2.2', $ur, $ue);
    $respuesta['egresoModificado'] = fnObtenerDatosConfig($db, '8.2.3', $ur, $ue);
    $respuesta['egresoComprometido'] = fnObtenerDatosConfig($db, '8.2.4', $ur, $ue);
    $respuesta['egresoDevengado'] = fnObtenerDatosConfig($db, '8.2.5', $ur, $ue);
    $respuesta['egresoEjercido'] = fnObtenerDatosConfig($db, '8.2.6', $ur, $ue);
    $respuesta['egresoPagado'] = fnObtenerDatosConfig($db, '8.2.7', $ur, $ue);

    $contenido = array('datos' => $respuesta);
    $result = true;
}

if ($option == 'mostrarCatalogo') {
    $sqlWhere = "";
    if (isset($_POST['ur']) && isset($_POST['ue'])) {
        $sqlWhere = " WHERE tb_momentos_presupuestales.ln_ur = '".$_POST['ur']."' AND tb_momentos_presupuestales.ln_ue = '".$_POST['ue']."'";
    }

    $info = array();
    $SQL = "SELECT
	tb_momentos_presupuestales.ln_ur as ur,
	tb_momentos_presupuestales.ln_ue as ue,
	tb_momentos_presupuestales.ln_presupuestalingreso as ingresoEstimado,
	tb_momentos_presupuestales.ln_presupuestalingresoEjecutar as ingresoPorEjecutar,
	tb_momentos_presupuestales.ln_presupuestalingresoModificado as ingresoModificado,
	tb_momentos_presupuestales.ln_presupuestalingresoDevengado as ingresoDevengado,
	tb_momentos_presupuestales.ln_presupuestalingresoRecaudado as ingresoRecaudado,
	tb_momentos_presupuestales.ln_presupuestalegreso as egresoAprobado, 
	tb_momentos_presupuestales.ln_presupuestalegresoEjercer as egresoPorEjercer, 
	tb_momentos_presupuestales.ln_presupuestalegresoModificado as egresoModificado,
	tb_momentos_presupuestales.ln_presupuestalegresocomprometido as egresoComprometido, 
	tb_momentos_presupuestales.ln_presupuestalegresodevengado as egresoDevengado, 
	tb_momentos_presupuestales.ln_presupuestalegresoejercido as egresoEjercido,
	tb_momentos_presupuestales.ln_presupuestalegresopagado as egresoPagado,
	CONCAT(tb_momentos_presupuestales.ln_ur, ' - ', tags.tagdescription) as urName,
	CONCAT(tb_momentos_presupuestales.ln_ue, ' - ', tb_cat_unidades_ejecutoras.desc_ue) as ueName
	FROM tb_momentos_presupuestales
	JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_momentos_presupuestales.ln_ur AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
	JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tb_momentos_presupuestales.ln_ur AND tb_sec_users_ue.ue = tb_momentos_presupuestales.ln_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
	JOIN tags ON tags.tagref = tb_momentos_presupuestales.ln_ur
	JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = tb_momentos_presupuestales.ln_ur AND tb_cat_unidades_ejecutoras.ue = tb_momentos_presupuestales.ln_ue
	".$sqlWhere."
	ORDER BY tb_momentos_presupuestales.ln_ur ASC, tb_momentos_presupuestales.ln_ue ASC";
    $ErrMsg = "No se obtuvieron los Ramos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'ur' => $myrow ['ur'],
            'ue' => $myrow ['ue'],
            'ingresoEstimado' => $myrow ['ingresoEstimado'],
            'ingresoPorEjecutar' => $myrow ['ingresoPorEjecutar'],
            'ingresoModificado' => $myrow ['ingresoModificado'],
            'ingresoDevengado' => $myrow ['ingresoDevengado'],
            'ingresoRecaudado' => $myrow ['ingresoRecaudado'],
            'egresoAprobado' => $myrow ['egresoAprobado'],
            'egresoPorEjercer' => $myrow ['egresoPorEjercer'],
            'egresoModificado' => $myrow ['egresoModificado'],
            'egresoComprometido' => $myrow ['egresoComprometido'],
            'egresoDevengado' => $myrow ['egresoDevengado'],
            'egresoEjercido' => $myrow ['egresoEjercido'],
            'egresoPagado' => $myrow ['egresoPagado'],
            'urName' => $myrow ['urName'],
            'ueName' => $myrow ['ueName'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['ur'].'\', \''.$myrow ['ue'].'\')"><span class="glyphicon glyphicon-edit"></span></a>'
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'ingresoEstimado', type: 'string' },";
    $columnasNombres .= "{ name: 'ingresoPorEjecutar', type: 'string' },";
    $columnasNombres .= "{ name: 'ingresoModificado', type: 'string' },";
    $columnasNombres .= "{ name: 'ingresoDevengado', type: 'string' },";
    $columnasNombres .= "{ name: 'ingresoRecaudado', type: 'string' },";
    $columnasNombres .= "{ name: 'egresoAprobado', type: 'string' },";
    $columnasNombres .= "{ name: 'egresoPorEjercer', type: 'string' },";
    $columnasNombres .= "{ name: 'egresoModificado', type: 'string' },";
    $columnasNombres .= "{ name: 'egresoComprometido', type: 'string' },";
    $columnasNombres .= "{ name: 'egresoDevengado', type: 'string' },";
    $columnasNombres .= "{ name: 'egresoEjercido', type: 'string' },";
    $columnasNombres .= "{ name: 'egresoPagado', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ingreso Estimado', datafield: 'ingresoEstimado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ingreso Por Ejecutar', datafield: 'ingresoPorEjecutar', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ingreso Modificado', datafield: 'ingresoModificado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ingreso Devengado', datafield: 'ingresoDevengado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ingreso Recaudado', datafield: 'ingresoRecaudado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Egreso Aprobado', datafield: 'egresoAprobado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Egreso Por Ejercer', datafield: 'egresoPorEjercer', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Egreso Modificado', datafield: 'egresoModificado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Egreso Comprometido', datafield: 'egresoComprometido', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Egreso Devengado', datafield: 'egresoDevengado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Egreso Ejercido', datafield: 'egresoEjercido', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Egreso Pagado', datafield: 'egresoPagado', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datosCatalogo' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $proceso = $_POST['proceso'];
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];

    $txtIngresoEstimado = $_POST['txtIngresoEstimado'];
    $txtIngresoPorEjecutar = $_POST['txtIngresoPorEjecutar'];
    $txtIngresoModificado = $_POST['txtIngresoModificado'];
    $txtIngresoDevengado = $_POST['txtIngresoDevengado'];
    $txtIngresoRecaudado = $_POST['txtIngresoRecaudado'];

    $txtEgresoAprobado = $_POST['txtEgresoAprobado'];
    $txtEgresoPorEjercer = $_POST['txtEgresoPorEjercer'];
    $txtEgresoModificado = $_POST['txtEgresoModificado'];
    $txtEgresoComprometido = $_POST['txtEgresoComprometido'];
    $txtEgresoDevengado = $_POST['txtEgresoDevengado'];
    $txtEgresoEjercido = $_POST['txtEgresoEjercido'];
    $txtEgresoPagado = $_POST['txtEgresoPagado'];

    $validacion  = fnValidarCuentaContable($db, $txtIngresoEstimado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ingreso Estimado '.$txtIngresoEstimado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Ingreso Estimado '.$txtIngresoEstimado.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtIngresoPorEjecutar);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ingreso Por Ejecutar '.$txtIngresoPorEjecutar.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Ingreso Por Ejecutar '.$txtIngresoPorEjecutar.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtIngresoModificado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ingreso Modificado '.$txtIngresoModificado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Ingreso Modificado '.$txtIngresoModificado.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtIngresoDevengado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ingreso Devengado '.$txtIngresoDevengado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Ingreso Devengado '.$txtIngresoDevengado.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtIngresoRecaudado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ingreso Recaudado '.$txtIngresoRecaudado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Ingreso Recaudado '.$txtIngresoRecaudado.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtEgresoAprobado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Egreso Aprobado '.$txtEgresoAprobado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Egreso Aprobado '.$txtEgresoAprobado.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtEgresoPorEjercer);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Egreso Por Ejercer '.$txtEgresoPorEjercer.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Egreso Por Ejercer '.$txtEgresoPorEjercer.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtEgresoModificado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Egreso Modificado '.$txtEgresoModificado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Egreso Modificado '.$txtEgresoModificado.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtEgresoComprometido);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Egreso Comprometido '.$txtEgresoComprometido.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Egreso Comprometido '.$txtEgresoComprometido.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtEgresoDevengado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Egreso Devengado '.$txtEgresoDevengado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Egreso Devengado '.$txtEgresoDevengado.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtEgresoEjercido);
    if (!$validacion['success']) {
        $result = false;
        //$Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Egreso Ejercido '.$txtEgresoEjercido.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Egreso Ejercido '.$txtEgresoEjercido.' no existe  en el Plan de Cuentas</p></div>';
    }

    $validacion  = fnValidarCuentaContable($db, $txtEgresoPagado);
    if (!$validacion['success']) {
        $result = false;
        // $Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Egreso Pagado '.$txtEgresoPagado.' no existe  en el Plan de Cuentas</p>';
        $Mensaje .= '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Egreso Pagado '.$txtEgresoPagado.' no existe  en el Plan de Cuentas</p></div>';
    }

    if (empty($Mensaje)) {
        $SQL = "SELECT ln_ur, ln_ue FROM tb_momentos_presupuestales WHERE ln_ur = '$ur' AND ln_ue = '$ue'";
        $ErrMsg = "No se obtuvieron los Ramos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO tb_momentos_presupuestales (ln_ur, ln_ue,
            ln_presupuestalingreso, ln_presupuestalingresoEjecutar, ln_presupuestalingresoModificado, ln_presupuestalingresoDevengado, ln_presupuestalingresoRecaudado,
            ln_presupuestalegreso, ln_presupuestalegresoEjercer, ln_presupuestalegresoModificado, ln_presupuestalegresocomprometido, ln_presupuestalegresodevengado, ln_presupuestalegresoejercido, ln_presupuestalegresopagado)
            VALUES ('".$ur."', '".$ue."',
            '".$txtIngresoEstimado."', '".$txtIngresoPorEjecutar."', '".$txtIngresoModificado."', '".$txtIngresoDevengado."', '".$txtIngresoRecaudado."',
            '".$txtEgresoAprobado."', '".$txtEgresoPorEjercer."', '".$txtEgresoModificado."', '".$txtEgresoComprometido."', '".$txtEgresoDevengado."', '".$txtEgresoEjercido."', '".$txtEgresoPagado."')";
            $ErrMsg = "No se agrego la información de ur: ".$ur." - ue: ".$ue;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            if ($proceso == 'Modificar') {
                $Mensaje = "Se modificó la configuración de UR: ".$ur." - UE: ".$ue." con éxito";
            } else {
                $Mensaje = "Se agregó la configuración de UR: ".$ur." - UE: ".$ue." con éxito";
            }
            $result = true;
        } else {
            if ($proceso == 'Modificar') {
                $SQL = "UPDATE tb_momentos_presupuestales 
                SET 
                ln_presupuestalingreso = '$txtIngresoEstimado',
                ln_presupuestalingresoEjecutar = '$txtIngresoPorEjecutar',
                ln_presupuestalingresoModificado = '$txtIngresoModificado',
                ln_presupuestalingresoDevengado = '$txtIngresoDevengado',
                ln_presupuestalingresoRecaudado = '$txtIngresoRecaudado',

                ln_presupuestalegreso = '$txtEgresoAprobado',
                ln_presupuestalegresoEjercer = '$txtEgresoPorEjercer',
                ln_presupuestalegresoModificado = '$txtEgresoModificado',
                ln_presupuestalegresocomprometido = '$txtEgresoComprometido',
                ln_presupuestalegresodevengado = '$txtEgresoDevengado',
                ln_presupuestalegresoejercido = '$txtEgresoEjercido',
                ln_presupuestalegresopagado = '$txtEgresoPagado'
                WHERE ln_ur = '$ur' AND ln_ue = '$ue'";
                $ErrMsg = "No se modificó la información de ur: ".$ur." - ue: ".$ue;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $Mensaje = "Se modificó la configuración de UR: ".$ur." - UE: ".$ue." con éxito";
                $result = true;
            } else {
                $Mensaje = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>La configuración de UR: '.$ur.' - UE: '.$ue.' ya existe</p></div>';
                $result = false;
            }
        }
    }
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
