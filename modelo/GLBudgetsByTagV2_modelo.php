<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Modelos para las operaciones de Adecuaciones Presupuestales
 */
////
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
$funcion=2263;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$añoGeneral = date('Y');
$dataJsonMeses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

$tipoMovGenReduccion = "256"; // Tipo Movimiento Reduccion
$tipoMovGenAmpliacion = "257"; // Tipo Movimiento Ampliacion

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'validarEstructuras') {
    $estProgramatica = $_POST['estProgramatica'];
    $estEconomica = $_POST['estEconomica'];
    $estAdministrativa = $_POST['estAdministrativa'];
    $estPartida = $_POST['estPartida'];
    $mensajeInicial = $_POST['mensajeInicial'];

    $contenido = '';
    $res = true;

    // Validar si existe la clave
    $numRegistros = compruebaClaveProgramatica($db, $estProgramatica);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Estructura Programática no encontrada '.$estProgramatica.'</p>';
    }
    // Validar si existe la clave
    $numRegistros = compruebaClaveEconomica($db, $estEconomica);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Estructura Económica no encontrada '.$estEconomica.'</p>';
    }
    // Validar si existe la clave
    $numRegistros = compruebaClaveAdministrativa($db, $estAdministrativa);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Estructura Administrativa no encontrada '.$estAdministrativa.'</p>';
    }
    // Validar si existe la clave
    $numRegistros = compruebaClaveRelacionPpPartida($db, $estPartida);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Relación PP-Partida no encontrada '.$estPartida.'</p>';
    }

    $result = $res;
}

if ($option == 'validarClaveAdicion') {
    $accountcode = $_POST['accountcode'];
    $transno = $_POST['transno'];
    $mensajeInicial = $_POST['mensajeInicial'];

    $contenido = '';
    $res = true;

    // Validar si existe la clave
    $sqlWhere = "";
    if (!empty($transno)) {
        $sqlWhere = " AND CASE WHEN chartdetailsbudgetbytag.nu_transno is null THEN 0 ELSE chartdetailsbudgetbytag.nu_transno END != ".$transno." ";
    }
    $SQL = "SELECT chartdetailsbudgetbytag.accountcode FROM chartdetailsbudgetbytag
    WHERE chartdetailsbudgetbytag.accountcode = '".$accountcode."'".$sqlWhere;
    $ErrMsg = "No se obtuviero información de claves para validación";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' la clave ya existe en el sistema</p>';
    }

    $result = $res;
}

if ($option == 'obtenerPresupuesto') {
    $clave = $_POST['clave'];
    $account = $_POST['account'];
    $legalid = $_POST['legalid'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];
    $tipoAfectacion = $_POST['tipoAfectacion'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $tipoMovimiento = $_POST['tipoMovimiento'];
    $period = "";

    $res = true;

    $info = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info);
    $result = $res;
}

if ($option == 'cargarInfoNoCaptura') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];

    $info = array();
    // GROUP BY chartdetailsbudgetlog.cvefrom, tipoMovimiento
    $SQL = "
    SELECT 
    distinct chartdetailsbudgetlog.cvefrom,
    -- chartdetailsbudgetlog.period,
    chartdetailsbudgetlog.nu_afectacion,
    -- chartdetailsbudgetlog.qty,
    -- CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento
    CASE WHEN chartdetailsbudgetlog.nu_tipo_movimiento = ".$tipoMovGenReduccion." THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."'
    AND CASE WHEN chartdetailsbudgetlog.estatus = 7 THEN chartdetailsbudgetlog.nu_afectacion <> '' ELSE 1 = 1 END
    ORDER BY chartdetailsbudgetlog.idmov ASC
            ";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $clave = $myrow['cvefrom'];
        $account = "";
        $legalid = "";
        $period = "";
        $tipoAfectacion = $myrow['nu_afectacion'];
        $tipoMovimiento = $myrow['tipoMovimiento'];
        $infoDatos = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento);
        $info[] = $infoDatos;
    }

    //Obtener nombre de estatus
    $statusname = "";
    $estatus = "";
    $tipoAdecuacion = "";
    $legalid = "";
    $tagref = "";
    $noOficio = "";
    $folio = "";
    $fechaAplicacion = "";
    $centroContable = "";
    $tipoReg = "";
    $jusR = "";
    $dicatenUpi = "";
    $controlInterno = "";
    $justificacion = "";
    $tipoSolicitud = "";
    $concR23 = "";
    $procesoSicop = "";
    $txtFolioMap = "";
    $tagrefReceptora = "";
    $ueCreadora = "";
    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetlog.estatus, tb_botones_status.statusname, 
            chartdetailsbudgetlog.sn_adecuacion, chartdetailsbudgetlog.numero_oficio, chartdetailsbudgetlog.folio,
            DATE_FORMAT(chartdetailsbudgetlog.dtm_aplicacion, '%d-%m-%Y') as dtm_aplicacion,
            chartdetailsbudgetlog.nu_centro_contable,
            chartdetailsbudgetlog.nu_tipo_reg,
            chartdetailsbudgetlog.nu_cat_jusr,
            chartdetailsbudgetlog.txt_dictamen_upi,
            chartdetailsbudgetlog.txt_control_interno,
            chartdetailsbudgetlog.txt_justificacion,
            chartdetailsbudgetlog.nu_tipo_solicitud,
            chartdetailsbudgetlog.nu_r23,
            chartdetailsbudgetlog.txt_proceso_sicop,
            chartdetailsbudgetlog.txt_folio_map,
            chartdetailsbudgetlog.sn_tagref_receptora,
            chartdetailsbudgetlog.ln_ue_creadora
            FROM chartdetailsbudgetlog
            JOIN tags ON tags.tagref = chartdetailsbudgetlog.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            LEFT JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
            WHERE 
            chartdetailsbudgetlog.type = '".$type."'
            AND chartdetailsbudgetlog.transno = '".$transno."'
            LIMIT 1";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
        $estatus = $myrow['estatus'];
        $statusname = $myrow['statusname'];
        $tipoAdecuacion = $myrow['sn_adecuacion'];
        $noOficio = $myrow['numero_oficio'];
        $folio = $myrow['folio'];
        $fechaAplicacion = $myrow['dtm_aplicacion'];
        $centroContable = $myrow['nu_centro_contable'];
        $tipoReg = $myrow['nu_tipo_reg'];
        $jusR = $myrow['nu_cat_jusr'];
        $dicatenUpi = $myrow['txt_dictamen_upi'];
        $controlInterno = $myrow['txt_control_interno'];
        $justificacion = $myrow['txt_justificacion'];
        $tipoSolicitud = $myrow['nu_tipo_solicitud'];
        $concR23 = $myrow['nu_r23'];
        $procesoSicop = $myrow['txt_proceso_sicop'];
        $txtFolioMap = $myrow['txt_folio_map'];
        $tagrefReceptora = $myrow['sn_tagref_receptora'];
        $ueCreadora = $myrow['ln_ue_creadora'];
    }

    if (empty($noOficio)) {
        $noOficio = "";
    }
    if ($fechaAplicacion == '00-00-0000') {
        $fechaAplicacion = date('d-m-Y');
    }
    if (empty($centroContable)) {
        $centroContable = '000';
    }
    if (empty($tipoReg)) {
        $tipoReg = '9';
    }
    if (empty($jusR)) {
        $jusR = '099';
    }
    if (empty($dicatenUpi)) {
        $dicatenUpi = "";
    }
    if (empty($controlInterno)) {
        $controlInterno = "";
    }
    if (empty($justificacion)) {
        $justificacion = "";
    }
    if (empty($tipoSolicitud)) {
        $tipoSolicitud = '0';
    }
    if (empty($concR23)) {
        $concR23 = '0';
    }
    if (empty($procesoSicop)) {
        $procesoSicop = '';
    }
    if (empty($txtFolioMap)) {
        $txtFolioMap = '';
    }

    $reponse['datos'] = $info;
    $reponse['legalid'] = $legalid;
    $reponse['tagref'] = $tagref;
    $reponse['transno'] = $transno;
    $reponse['type'] = $type;
    $reponse['estatus'] = $estatus;
    $reponse['statusname'] = $statusname;
    $reponse['fechaCaptura'] = date('d-m-Y');
    $reponse['tipoAdecuacion'] = $tipoAdecuacion;
    $reponse['noOficio'] = $noOficio;
    $reponse['folio'] = $folio;

    if ($estatus == '7') {
        $reponse['fechaAplicacion'] = $fechaAplicacion;
    } else {
        $reponse['fechaAplicacion'] = date('d-m-Y');
    }
    $reponse['centroContable'] = $centroContable;
    $reponse['tipoReg'] = $tipoReg;
    $reponse['jusR'] = $jusR;
    $reponse['dicatenUpi'] = $dicatenUpi;
    $reponse['controlInterno'] = $controlInterno;
    $reponse['justificacion'] = $justificacion;
    $reponse['tipoSolicitud'] = $tipoSolicitud;
    $reponse['concR23'] = $concR23;
    $reponse['procesoSicop'] = $procesoSicop;
    $reponse['txtFolioMap'] = $txtFolioMap;
    $reponse['tagrefReceptora'] = $tagrefReceptora;
    $reponse['ueCreadora'] = $ueCreadora;

    $contenido = $reponse;//array('datos' => $info);
    $result = true;
}

function fnPresupuestoEliminar($db, $type, $transno, $clave, $ampliacionReduccion, $tipoAdecuacion)
{
    $sqlWhere = " AND qty < 0 "; // Reduccion
    if ($ampliacionReduccion == "Ampliacion") {
        $sqlWhere = " AND qty > 0 "; // Ampliacion
    }

    if ($ampliacionReduccion == 'Reduccion/Ampliacion') {
        // Validacion tipo 2
        // Validacion tipo 4
        // Validacion tipo EC
        // Validacion tipo IC
        // Validacion tipo MC
        $sqlWhere = "";
    }

    if ($tipoAdecuacion == '2' or $tipoAdecuacion == '4') {
        // Validacion tipo 2
        // Validacion tipo 4
        $sqlWhere = "";
    }

    $SQL = "DELETE FROM chartdetailsbudgetlog 
            WHERE type = '".$type."' AND transno = '".$transno."' AND cvefrom = '".$clave."' ".$sqlWhere;
    $ErrMsg = "No se elimino la Clave Presupuestal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    return true;
}

if ($option == 'actualizarAdecAutizada') {
    $pSicop = $_POST['pSicop'];
    $fMap = $_POST['fMap'];
    $fecha = $_POST['fecha'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];

    // Actualizacion Datos Generales
    $SQL = "UPDATE chartdetailsbudgetlog 
            SET 
            chartdetailsbudgetlog.dtm_aplicacion = STR_TO_DATE('".$fecha."','%d-%m-%Y %H:%i:%s'),
            chartdetailsbudgetlog.txt_proceso_sicop = '".$pSicop."',
            chartdetailsbudgetlog.txt_folio_map = '".$fMap."'
            WHERE chartdetailsbudgetlog.type = '".$type."' AND chartdetailsbudgetlog.transno = '".$transno."' ";
    $ErrMsg = "No se Actualizaron Registros en Generales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $reponse['mensaje'] = '<h4>Proceso realizado correctamente para el Folio '.$transno.'</h4>';

    $contenido = $reponse;
    $result = true;
}

if ($option == 'capturaPresupuesto') {
    $datosReducciones = $_POST['datosCapturaReducciones'];
    $datosAmpliaciones = $_POST['datosCapturaAmpliaciones'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $tipoAdecuacion = $_POST['tipoAdecuacion'];
    $noOficio = $_POST['noOficio'];
    $fechaAplicacion = $_POST['fechaAplicacion'];
    $centroContable = $_POST['centroContable'];
    $tipoReg = $_POST['tipoReg'];
    $jusR = $_POST['jusR'];
    $dicatenUpi = $_POST['dicatenUpi'];
    $controlInterno = $_POST['controlInterno'];
    $justificacion = $_POST['justificacion'];
    $tipoSolicitud = $_POST['tipoSolicitud'];
    $concR23 = $_POST['concR23'];
    $procesoSicop = $_POST['procesoSicop'];
    $txtFolioMap = $_POST['txtFolioMap'];
    $tagrefReceptora = $_POST['tagrefReceptora'];
    $estatusAdecuacionGeneral = $_POST['estatusAdecuacionGeneral'];
    $ueCreadora = $_POST['ueCreadora'];

    if (empty($transno)) {
        $transno = GetNextTransNo($type, $db);
        $datosPresupuesto['transno'] = $transno;
    }

    if (empty($tagref) or $tagref == '-1' or $tagref == '0') {
        $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
                FROM sec_unegsxuser u, tags t 
                WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "'  
                ORDER BY t.tagref LIMIT 1 ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $tagref = $myrow ['tagref'];
        }
    }

    $resultOperacion = true;

    $SQL = "SELECT accountcode FROM chartdetailsbudgetbytag WHERE nu_transno = '".$transno."'";
    $ErrMsg = "";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        // Borrar datos de las claves de adicion
        $SQL = "DELETE FROM chartdetailsbudgetbytag WHERE nu_transno = '".$transno."'";
        $ErrMsg = "Eliminar adiciones";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQL = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = '".$type."' AND chartdetailsbudgetlog.transno = '".$transno."'";
        $ErrMsg = "Eliminar adiciones";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    foreach ($datosReducciones as $datosClave) {
        $clave = $datosClave['accountcode'];
        $numMes = 1;
        foreach ($dataJsonMeses as $nameMes) {
            $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
            $cantidad = $datosClave[$nameMes] * -1;
            //$disponible = fnInfoPresupuesto($db, $clave, $periodo);
            $disponible = fnInfoPresupuesto($db, $clave, $periodo, "", "", 0, 0, "", $type, $transno, 'Reduccion');
            foreach ($disponible as $dispo) {
                if ($dispo[$nameMes] >= abs($cantidad)) {
                    fnAgregarPresupuestoGeneral($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $estatus, $datosClave['tipoAfectacion'], 'Reduccion', $datosClave['partida_esp'], $tipoMovGenReduccion, $tipoMovGenAmpliacion);
                } else if (abs($cantidad) > 0) {
                    $resultOperacion = false;
                    $Mensaje .= '<br>Sin disponible para el mes de '.$nameMes;
                }
                //echo "\n nameMes: ".$nameMes." - periodo: ".$periodo." - disponible: ".$dispo[$nameMes]." - cantidad: ".$cantidad;
            }
            $numMes ++;
        }
    }
    
    foreach ($datosAmpliaciones as $datosClave) {
        $clave = $datosClave['accountcode'];
        $numMes = 1;
        foreach ($dataJsonMeses as $nameMes) {
            $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
            $cantidad = $datosClave[$nameMes];
            fnAgregarPresupuestoGeneral($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $estatus, $datosClave['tipoAfectacion'], 'Ampliacion', $datosClave['partida_esp'], $tipoMovGenReduccion, $tipoMovGenAmpliacion);
            $numMes ++;
        }

        // Valudidar si es clave nueva Adicion
        $claveNuevaAdicion = 0;
        $SQL = "SELECT nu_claveNueva FROM tb_tipo_afectacion WHERE nu_afectacion = '".$datosClave['tipoAfectacion']."'";
        $ErrMsg = "No obtuvo si es clave nueva en tipo de afectación";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $claveNuevaAdicion = $myrow['nu_claveNueva'];
        }

        if ($claveNuevaAdicion == 1) {
            // Si es clave nueva agregar clave desglosada
            $datosClaveDesglosada = $datosClave['datosClave'];
            // print_r($datosClaveDesglosada);
            $sqlCampos = "anho";
            $sqlValores = date('Y');//"0000";
            foreach ($datosClaveDesglosada as $datos) {
                // echo "\n ".$datos['nombre'];
                $sqlCampos .= ', '.$datos['nombreCampo'];
                $sqlValores .= ", '".$datos['valor']."'";
            }

            $SQL = "INSERT INTO `chartdetailsbudgetbytag` 
            (`accountcode`, `budget`, `original`, `enero`, `febrero`,
            `marzo`, `abril`, `mayo`, `junio`, `julio`, 
            `agosto`, `septiembre`, `octubre`, `noviembre`, `diciembre`, 
            `idClavePresupuesto`, `sn_inicial`, `fecha_modificacion`, `fecha_captura`, `fecha_sistema`, 
            `txt_userid` , nu_transno, ".$sqlCampos.")
            VALUES
            ('".$clave."', '0', '0', '0', '0',
            '0', '0', '0', '0', '0',
            '0', '0', '0', '0', '0', 
            '".$datosClave['idClavePresupuesto']."', '2', NOW(), NOW(), NOW(), 
            '".$_SESSION['UserID']."', '".$transno."', ".$sqlValores."
            )";
            $ErrMsg = "No se guardo la información de la clave nueva";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $SQL = "UPDATE chartdetailsbudgetlog SET ln_ue = '".fnObtenerUnidadEjecutoraClave($db, $clave)."'
            WHERE cvefrom = '".$clave."'";
            $ErrMsg = "No se guardo la información de la clave nueva";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        }
    }

    // Validar si tiene registros con cantidad diferente a 0, para borrar los registros que no se usan
    $SQL = "SELECT chartdetailsbudgetlog.qty FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.type = '".$type."' AND chartdetailsbudgetlog.transno = '".$transno."' AND chartdetailsbudgetlog.qty <> 0
    ";
    $ErrMsg = "Validación para eliminar registros en 0";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        //Borrar Registros en 0
        $SQL = "DELETE FROM chartdetailsbudgetlog WHERE (qty = '0' OR qty = '-0') AND type = '".$type."' AND transno = '".$transno."'";
        $ErrMsg = "No se eliminaron Registros en 0";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }
    
    if ($resultOperacion) {
        $Mensaje = "El Folio de la Adecuación es ".$transno;
    }

    if (empty($fechaAplicacion)) {
        $fechaAplicacion = date('d-m-Y');//'00-00-0000';
    }

    // Validar si tiene un estatus avanzado
    $estatusActu = $estatus;
    if ($estatusAdecuacionGeneral != '') {
        // No tiene estatus
        $estatusActu = $estatusAdecuacionGeneral;
    }

    // Actualizacion Datos Generales
    $SQL = "UPDATE chartdetailsbudgetlog 
            SET 
            chartdetailsbudgetlog.sn_disponible = '1', 
            chartdetailsbudgetlog.estatus = '".$estatusActu."',
            chartdetailsbudgetlog.sn_adecuacion = '".$tipoAdecuacion."', 
            chartdetailsbudgetlog.tagref = '".$tagref."',
            chartdetailsbudgetlog.numero_oficio = '".$noOficio."',
            chartdetailsbudgetlog.dtm_aplicacion = STR_TO_DATE('".$fechaAplicacion."','%d-%m-%Y %H:%i:%s'),
            chartdetailsbudgetlog.nu_centro_contable = '".$centroContable."',
            chartdetailsbudgetlog.nu_tipo_reg = '".$tipoReg."',
            chartdetailsbudgetlog.nu_cat_jusr = '".$jusR."',
            chartdetailsbudgetlog.txt_dictamen_upi = '".$dicatenUpi."',
            chartdetailsbudgetlog.txt_control_interno = '".$controlInterno."',
            chartdetailsbudgetlog.txt_justificacion = '".$justificacion."',
            chartdetailsbudgetlog.nu_tipo_solicitud = '".$tipoSolicitud."',
            chartdetailsbudgetlog.fecha_captura = NOW(),
            chartdetailsbudgetlog.nu_r23 = '".$concR23."',
            chartdetailsbudgetlog.txt_proceso_sicop = '".$procesoSicop."',
            chartdetailsbudgetlog.txt_folio_map = '".$txtFolioMap."',
            chartdetailsbudgetlog.sn_tagref_receptora = '".$tagrefReceptora."',
            chartdetailsbudgetlog.sn_reglas_validadas = '".$estatus."',
            chartdetailsbudgetlog.ln_ue_creadora = '".$ueCreadora."',
            chartdetailsbudgetlog.userid = '".$_SESSION['UserID']."'
            WHERE chartdetailsbudgetlog.type = '".$type."' AND chartdetailsbudgetlog.transno = '".$transno."' ";
    $ErrMsg = "No se Actualizaron Registros en Generales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    //Obtener nombre de estatus
    $statusname = "";
    $SQL = "SELECT statusid, CONCAT(statusid, ' - ', statusname) as statusname 
            FROM tb_botones_status WHERE statusid = '".$estatus."' AND tb_botones_status.sn_funcion_id = '".$funcion."'";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $statusname = $myrow['statusname'];
    }

    //Datos generales
    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref
            FROM chartdetailsbudgetlog 
            JOIN tags ON tags.tagref = chartdetailsbudgetlog.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE type = '".$type."' AND transno = '".$transno."' ";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
    }

    $datosPresupuesto['legalid'] = $legalid;
    $datosPresupuesto['tagref'] = $tagref;
    $datosPresupuesto['transno'] = $transno;
    $datosPresupuesto['type'] = $type;
    $datosPresupuesto['estatus'] = $estatus;
    $datosPresupuesto['statusname'] = $statusname;
    $datosPresupuesto['fechaCaptura'] = date('d-m-Y');
    $datosPresupuesto['tipoAdecuacion'] = $tipoAdecuacion;
    $datosPresupuesto['noOficio'] = $noOficio;

    $datosPresupuesto['fechaAplicacion'] = $fechaAplicacion;
    $datosPresupuesto['centroContable'] = $centroContable;
    $datosPresupuesto['tipoReg'] = $tipoReg;
    $datosPresupuesto['jusR'] = $jusR;
    $datosPresupuesto['dicatenUpi'] = $dicatenUpi;
    $datosPresupuesto['controlInterno'] = $controlInterno;
    $datosPresupuesto['justificacion'] = $justificacion;
    $datosPresupuesto['tipoSolicitud'] = $tipoSolicitud;

    $datosPresupuesto['concR23'] = $concR23;
    $datosPresupuesto['procesoSicop'] = $procesoSicop;
    $datosPresupuesto['txtFolioMap'] = $txtFolioMap;
    $datosPresupuesto['tagrefReceptora'] = $tagrefReceptora;
    $datosPresupuesto['ueCreadora'] = $ueCreadora;

    $contenido = array('datos' => $datosPresupuesto);
    $result = $resultOperacion;
}

if ($option == 'eliminaPresupuesto') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $clave = $_POST['clave'];
    $ampliacionReduccion = $_POST['tipo'];
    $tipoAdecuacion = $_POST['tipoAdecuacion'];
    $claveNuevaAdicion = $_POST['claveNuevaAdicion'];
    $claveNueva = $_POST['claveNueva'];

    $info = fnPresupuestoEliminar($db, $type, $transno, $clave, $ampliacionReduccion, $tipoAdecuacion);

    if ($claveNuevaAdicion == 1) {
        // Eliminar registro de la clave si es adicion
        $SQL = "DELETE FROM chartdetailsbudgetbytag WHERE accountcode = '".$claveNueva."' AND nu_transno = '".$transno."'";
        $ErrMsg = "No se eliminó la clave ".$clave;
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    if ($info) {
        $Mensaje = "La clave presupuestal se ha eliminado";
    } else {
        $Mensaje = "Ocurrió un error al procesar la información";
    }

    $contenido = $Mensaje;
    $result = $info;
}

function fnBusquedaDefault($db, $sqlWhere)
{
    $info = array();

    // JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
    // sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    $SQL = "
    SELECT
    distinct 
    chartdetailsbudgetbytag.*
    FROM chartdetailsbudgetbytag
    JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = chartdetailsbudgetbytag.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.userid = '".$_SESSION['UserID']."' AND chartdetailsbudgetbytag.tagref = tb_sec_users_ue.tagref AND  tb_cat_unidades_ejecutoras.ue = tb_sec_users_ue.ue
    WHERE 1 = 1 ".$sqlWhere."
    ORDER BY chartdetailsbudgetbytag.accountcode ASC ";
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

    return $info;
}

if ($option == 'obtenerPresupuestosBusqueda') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $tipoAdecuacion = $_POST['tipoAdecuacion'];
    $tipoSolicitud = $_POST['tipoSolicitud'];
    $concR23 = $_POST['concR23'];
    $filtrosClave = $_POST['filtrosClave'];
    $ramoBusqueda = $_POST['ramoBusqueda'];
    
    $sqlWhere = " AND chartdetailsbudgetbytag.anho = '".$_SESSION['ejercicioFiscal']."' ";
    if ($legalid != "" and $legalid != '-1') {
        // Filtrar por Dependica
        $sqlWhere .= " AND legalbusinessunit.legalid = '".$legalid."' ";
    }
    if ($tagref != "" and $tagref != '-1') {
        // Filtrar por UR
        $sqlWhere .= " AND tags.tagref = '".$tagref."' ";
    }
    if ($concR23 != "") {
        // Validacion tipo EI
        $sqlWhere .= " AND chartdetailsbudgetbytag.cve_ramo = '23' ";
    }
    if (trim($ramoBusqueda) != '0') {
        // Filtrar por Ramo
        $sqlWhere .= " AND chartdetailsbudgetbytag.cve_ramo = '".$ramoBusqueda."' ";
    }
    if (!empty($filtrosClave)) {
        // Filtrar por filtros
        foreach ($filtrosClave as $datosClave) {
            if ($datosClave['valor'] != '-1') {
                // echo "\n campoPresupuesto: ".$datosClave['campoPresupuesto'];
                $sqlWhere .= " AND chartdetailsbudgetbytag.".$datosClave['campoPresupuesto']." = '".$datosClave['valor']."' ";
            }
        }
    }

    if ($tipoAdecuacion == '3') {
        // Validacion tipo 3
        //$sqlWhere .= " AND chartdetailsbudgetbytag.accountcode NOT IN (SELECT cvefrom FROM chartdetailsbudgetlog WHERE type = '".$type."' AND transno = '".$transno."') ";
    }

    $info = array();

    $info = fnBusquedaDefault($db, $sqlWhere);

    if (1 == 2 && ($tipoAdecuacion == "" or $tipoAdecuacion == '0' or $tipoSolicitud = "" or $tipoSolicitud == '0')) {
        $info = fnBusquedaDefault($db, $sqlWhere);
    } else if (1 == 2) {
        // JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
        // sec_unegsxuser.userid = '%s'
        $SQL = "SELECT txt_sql FROM tb_tipo_adecuacion_validaciones WHERE nu_clase = '".$tipoAdecuacion."' AND nu_tipo_solicitud = '".$tipoSolicitud."'";
        $ErrMsg = "No se obtuvieron los Presupuestos para la Búsqueda";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) > 0) {
            while ($myrow = DB_fetch_array($TransResult)) {
                $SQL = $myrow ['txt_sql']." ".$sqlWhere." ORDER BY chartdetailsbudgetbytag.accountcode ASC ";
                //$SQL = sprintf($myrow ['txt_sql'].$sqlWhere, $_SESSION['UserID']);
                
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                while ($myrow2 = DB_fetch_array($TransResult2)) {
                    $value = "";
                    $SQL = "SELECT campoPresupuesto FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow2['idClavePresupuesto']."' AND adecuacion_presupuesto = '1' ";
                    $TransResult3 = DB_query($SQL, $db, $ErrMsg);
                    while ($myrow3 = DB_fetch_array($TransResult3)) {
                        if (empty($value)) {
                            $value = $myrow2 [$myrow3['campoPresupuesto']];
                        } else {
                            $value .= '-'.$myrow2 [$myrow3['campoPresupuesto']];
                        }
                    }

                    $datos['value'] = $value;
                    $datos['accountcode'] = $myrow2 ['accountcode'];
                    $datos['valorLista'] = $value;

                    $claveCorta = "";
                    $SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow2 ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
                    $resultClave = DB_query($SQL, $db, $ErrMsg);
                    while ($rowClave = DB_fetch_array($resultClave)) {
                        if (empty($claveCorta)) {
                            $claveCorta = $myrow2 [$rowClave ['campoPresupuesto']];
                        } else {
                            $claveCorta .= "-".$myrow2 [$rowClave ['campoPresupuesto']];
                        }
                    }

                    $datos['claveCorta'] = $claveCorta;

                    $claveLarga = "";
                    $SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow2 ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
                    $resultClave = DB_query($SQL, $db, $ErrMsg);
                    while ($rowClave = DB_fetch_array($resultClave)) {
                        if (empty($claveLarga)) {
                            $claveLarga = $myrow2 [$rowClave ['campoPresupuesto']];
                        } else {
                            $claveLarga .= "-".$myrow2 [$rowClave ['campoPresupuesto']];
                        }
                    }

                    $datos['claveLarga'] = $claveLarga;

                    $info[] = $datos;
                }
            }
        } else {
            $info = fnBusquedaDefault($db, $sqlWhere);
        }
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != '-1' and !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref as value, CONCAT(t.tagref, ' - ', t.tagdescription) as texto, t.tagref
            FROM sec_unegsxuser u,tags t 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere." 
            ORDER BY t.tagref ";
    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}
if ($option == 'mostrarUnidadEjecutora') {
    $tagref = $_POST['tagref'];
    $multiple = 0;

    if (isset($_POST['multiple'])) {
        $multiple = $_POST['multiple'];
    }

    $sqlWhere = "";
    if ($tagref != '-1' and !empty($tagref) and $multiple == '0') {
        $sqlWhere = " AND t.tagref IN('".$tagref."') ";
    } else if ($tagref != '-1' and !empty($tagref) and $multiple == '1') {
        $sqlWhere = " AND t.tagref IN(".$tagref.") ";
    }

    $info = array();
    /*$SQL = "SELECT  t.tagref as value, CONCAT(t.tagref, ' - ', t.tagdescription) as texto, t.tagref
            FROM sec_unegsxuser u,tags t 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere." 
            ORDER BY t.tagref ";*/
    $SQL= "SELECT t.legalid as dependencia,  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription,  tce.ue as value, CONCAT(tce.ue, ' - ', tce.desc_ue) as texto 
    FROM sec_unegsxuser u
    INNER JOIN tags t on (u.tagref = t.tagref)
    INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
    WHERE tce.active = 1 and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere."
    ORDER BY t.tagref";
    $ErrMsg = "No se obtuvieron las UE";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'], 'dependencia' => $myrow ['dependencia'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerBotones') {
    $autorizarGeneral = $_POST['autorizarGeneral'];
    $soloActFoliosAutorizada = $_POST['soloActFoliosAutorizada'];
    $sqlWhere = " AND tb_botones_status.statusid <> '98' ";
    if ($autorizarGeneral == '1') {
        // Si es autorizar solo mostrar rechazar y autorizar
        $sqlWhere = " AND tb_botones_status.statusid IN ('5', '99') ";
    }

    if ($soloActFoliosAutorizada == '1') {
        // Estatus 7 todo finalizado
        $sqlWhere = " AND tb_botones_status.statusid IN ('98') ";
    }

    $info = array();
    $SQL = "SELECT 
            distinct tb_botones_status.functionid,
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
            (tb_botones_status.sn_funcion_id = '".$funcion."')
            AND (tb_botones_status.sn_flag_disponible = 1)
            AND (tb_botones_status.sn_adecuacion_presupuestal = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."' AND sec_funxuser.permiso = 1)
            ) ".$sqlWhere."
            ORDER BY tb_botones_status.functionid ASC
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

if ($option == 'mostrarRazonSocial') {
    $ramo = $_POST['ramo'];
    $sqlWhere = "";
    if (!empty($ramo) and $ramo != '-1') {
        $sqlWhere = " AND legalbusinessunit.cve_ramo IN (".$ramo.") ";
    }
    $info = array();
    // GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname
    $SQL = "SELECT distinct legalbusinessunit.legalid, CONCAT(legalbusinessunit.legalid, ' - ', legalbusinessunit.legalname) as legalname 
            FROM sec_unegsxuser u, tags t 
            JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' " . $sqlWhere . "
            ORDER BY legalbusinessunit.legalid ";
    $ErrMsg = "No se obtuvieron las razones sociales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['legalid'], 'texto' => $myrow ['legalname'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoAdecuacion') {
    $info = array();
    // GROUP BY nu_adecuacion
    $SQL = "SELECT distinct nu_adecuacion as value, CONCAT(sn_clave, ' - ', txt_descripcion) as texto, nu_adecuacion
    FROM tb_tipo_adecuacion 
    WHERE sn_activo = '1' ORDER BY nu_adecuacion ASC";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoReg') {
    $info = array();
    // GROUP BY nu_adecuacion
    $SQL = "SELECT distinct nu_tipo_reg as value, CONCAT(nu_tipo_reg, ' - ', txt_descripcion) as texto, nu_tipo_reg
    FROM tb_treg 
    WHERE sn_activo = '1' ORDER BY nu_tipo_reg ASC";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarJusR') {
    $info = array();
    // GROUP BY nu_adecuacion
    $SQL = "SELECT distinct nu_cat_jusr as value, CONCAT(nu_cat_jusr, ' - ', txt_descripcion) as texto, nu_cat_jusr
    FROM tb_jusr 
    WHERE sn_activo = '1' ORDER BY nu_cat_jusr ASC";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarConcR23') {
    $info = array();
    // GROUP BY nu_adecuacion
    $SQL = "SELECT distinct nu_r23 as value, CONCAT(nu_r23, ' - ', txt_descripcion) as texto, nu_r23
    FROM tb_conc_r23 
    WHERE sn_activo = '1' ORDER BY nu_r23 ASC";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarURSinRestricccion') {
    $info = array();
    // GROUP BY nu_adecuacion
    $SQL = "SELECT distinct tagref as value, CONCAT(tagref, ' - ', tagdescription) as texto, tagref
    FROM tags WHERE tagactive = 1
    ORDER BY tagref ASC";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoDocumentoPartida') {
    $info = array();
    $SQL = "SELECT nu_afectacion as value, CONCAT(nu_afectacion, ' - ', txt_descripcion) as texto2, nu_afectacion as texto, nu_claveNueva FROM tb_tipo_afectacion WHERE sn_activo = '1' AND sn_reduccion = '1' ORDER BY nu_afectacion ASC";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'], 'nu_claveNueva' => $myrow ['nu_claveNueva'] );
    }

    $info2 = array();
    $SQL = "SELECT nu_afectacion as value, CONCAT(nu_afectacion, ' - ', txt_descripcion) as texto2, nu_afectacion as texto, nu_claveNueva FROM tb_tipo_afectacion WHERE sn_activo = '1' AND sn_ampliacion = '1' ORDER BY nu_afectacion ASC";
    $ErrMsg = "No se obtuvo los Tipos de Documentos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info2[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'], 'nu_claveNueva' => $myrow ['nu_claveNueva'] );
    }

    $contenido = array('datosReduccion' => $info, 'datosAmpliacion' => $info2);
    $result = true;
}

if ($option == 'datosConfiguracionClave') {
    $info = array();
    $SQL = "SELECT 
            campoPresupuesto,
            tabla,
            campo,
            nombre,
            txt_sql_nueva,
            orden
            FROM budgetConfigClave 
            WHERE sn_filtro_adecuacion = '1' AND nu_anio = '".$añoGeneral."' ORDER BY orden ASC ";
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

        $info[] = array( 'campoPresupuesto' => $myrow ['campoPresupuesto'], 'tabla' => $myrow ['tabla'], 'campo' => $myrow ['campo'], 'nombre' => $myrow ['nombre'], 'infoSelect' => $infoSelect );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarRamo') {
    $info = array();
    // GROUP BY cve_ramo
    $SQL = "SELECT distinct cve_ramo as value, CONCAT(cve_ramo, ' - ', desc_ramo) as texto 
    FROM g_cat_ramo WHERE active = '1' ORDER BY cve_ramo ASC";
    $ErrMsg = "No se obtuvo los Ramos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarTipoSolicitud') {
    $tipoAdecuacion = $_POST['tipoAdecuacion'];
    $sqlWhere = "";
    // if (!empty($tipoAdecuacion) && $tipoAdecuacion != '0') {
    //     $sqlWhere = " AND nu_adecuacion like'%".$tipoAdecuacion."%' ";
    // }
    if (!empty($tipoAdecuacion)) {
        $sqlWhere = " AND tb_adecuacion_solicitud.nu_adecuacion IN (".$tipoAdecuacion.") ";
    }
    $info = array();
    // GROUP BY nu_tipo_solicitud
    $SQL = "SELECT distinct tb_tipo_solicitud.nu_tipo_solicitud as value, CONCAT(tb_tipo_solicitud.sn_clave, ' - ', tb_tipo_solicitud.txt_descripcion) as texto, tb_tipo_solicitud.sn_clave
    FROM tb_tipo_solicitud 
    JOIN tb_adecuacion_solicitud ON tb_adecuacion_solicitud.nu_tipo_solicitud = tb_tipo_solicitud.nu_tipo_solicitud
    WHERE tb_tipo_solicitud.sn_activo = '1' ".$sqlWhere."
    ORDER BY tb_tipo_solicitud.sn_clave ASC";
    $ErrMsg = "No se obtuvo los Tipos de Solicitud";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
