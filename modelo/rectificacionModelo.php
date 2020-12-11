<?php
/**
 * Captura del Oficio de Rectificación
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/09/2018
 * Fecha Modificación: 12/09/2018
 * Modelos para las operaciones de la Captura del Oficio de Rectificación
 */
/////
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
$funcion=2461;
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
$añoGeneral = '2017';//date('Y');
$dataJsonMeses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'existeCompromiso') {
    $idcompromiso = $_POST['idcompromiso'];
    $selectTipo = $_POST['selectTipo'];

    $info = array();
    $SQL = "SELECT
    supptrans.type as nu_type,
    supptrans.transno as nu_transno,
    supptrans.hold as nu_estatus
    FROM supptrans
    JOIN suppallocs ON suppallocs.transid_allocfrom = supptrans.id
    JOIN supptrans supptransDocPago ON supptransDocPago.id = suppallocs.transid_allocto
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = supptrans.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = supptrans.tagref AND tb_sec_users_ue.ue = supptrans.ln_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    WHERE
    supptrans.type = 22
    AND supptransDocPago.type = '".$selectTipo."'
    AND supptrans.suppreference = '".$idcompromiso."'
    AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
    $ErrMsg = "No se obtuvo información del pago";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $myrow = DB_fetch_array($TransResult);
        $info['type'] = $myrow ['nu_type'];
        $info['transno'] = $myrow ['nu_transno'];
        $result = true;
    } else {
        // No existe pago
        $myrow = fnInformacionTipoDocumento($db, $selectTipo);
        $result = false;
        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Folio del Pagado '.$idcompromiso.' no existe. Para la operación '.$selectTipo.' - '.$myrow['typename'].'</p>';
    }

    $contenido = array('datos' => $info);
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
    
    $selectTipo = $_POST['selectTipo'];
    $tipoCom = $_POST['tipoCom'];
    $folCom = $_POST['folCom'];
    $nu_id_compromiso = $_POST['idCom'];
    $nu_id_devengado = "";
    $nu_idret = 0;

    $period = "";

    if ($selectTipo == '295' || $selectTipo == '296') {
        $type = $tipoCom;
        $transno = $folCom;
    }

    $res = true;

    $info = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento, '', '', '', 0, $nu_id_devengado, $nu_id_compromiso, $nu_idret);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info);
    $result = $res;
}

if ($option == 'cargarInfoNoCapturaCompromiso') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];
    $idcompromiso = $_POST['idcompromiso'];

    // Obtener claves del pago
    $info = array();
    $SQL = "SELECT
    distinct chartdetailsbudgetlog.cvefrom,
    CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento,
    chartdetailsbudgetlog.nu_id_compromiso,
    chartdetailsbudgetlog.nu_id_devengado,
    chartdetailsbudgetlog.nu_idret
    FROM chartdetailsbudgetlog
    WHERE
    chartdetailsbudgetlog.sn_disponible = 1
    AND chartdetailsbudgetlog.nu_tipo_movimiento = 265
    AND chartdetailsbudgetlog.type = '".$type."'
    AND transno = '".$transno."'
    ORDER BY idmov ASC
    ";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $clavesPago = "";
    $compromisosPago = "";
    while ($myrow = DB_fetch_array($TransResult)) {
        $clave = $myrow['cvefrom'];
        $account = "";
        $legalid = "";
        $period = "";
        $tipoAfectacion = "";
        $tipoMovimiento = $myrow['tipoMovimiento'];
        $nu_id_compromiso = $myrow['nu_id_compromiso'];
        $nu_id_devengado = $myrow['nu_id_devengado'];
        $nu_idret = $myrow['nu_idret'];

        $compromisosPago = $myrow['nu_id_compromiso'];

        if ($clavesPago == "") {
            $clavesPago .= "'".$clave."'";
        } else {
            $clavesPago .= ", '".$clave."'";
        }

        $infoDatos = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento, '', '', '', 0, $nu_id_devengado, $nu_id_compromiso, $nu_idret);
        $info[] = $infoDatos;
    }

    if (empty($clavesPago)) {
        $clavesPago = "''";
    }

    $infoClaves = array();
    $tipoCom = "";
    $folCom = "";
    if (!empty($compromisosPago)) {
        $SQL = "SELECT
        distinct
        chartdetailsbudgetlog.cvefrom
        FROM chartdetailsbudgetlog
        WHERE
        chartdetailsbudgetlog.nu_tipo_movimiento = '259'
        AND chartdetailsbudgetlog.nu_id_compromiso = '".$compromisosPago."'
        AND chartdetailsbudgetlog.cvefrom NOT IN (".$clavesPago.")
        GROUP BY chartdetailsbudgetlog.cvefrom";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $infoClaves[] = array(
                'accountcode' => $myrow ['cvefrom']
            );
        }

        $SQL = "SELECT 
        tb_compromiso.nu_type,
        tb_compromiso.nu_transno,
        tb_compromiso.nu_estatus,
        tb_botones_status.statusname
        FROM tb_compromiso 
        LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_compromiso.nu_estatus AND tb_botones_status.sn_funcion_id = tb_compromiso.sn_funcion_id
        WHERE tb_compromiso.nu_id_compromiso = '".$compromisosPago."'
        AND tb_compromiso.nu_tipo = 'O'";
        // AND tb_compromiso.nu_estatus = '4'
        $ErrMsg = "No se obtuvo información del compromiso";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) > 0) {
            $myrow = DB_fetch_array($TransResult);
            $tipoCom = $myrow ['nu_type'];
            $folCom = $myrow ['nu_transno'];
        }
    }

    //Obtener nombre de estatus
    $statusname = "";
    $estatus = "";
    $legalid = "";
    $tagref = "";
    $justificacion = "";
    $ln_ue = "";

    $fechaCaptura = date('d-m-Y');
    $txtIdCompromiso = '';

    $SQL = "SELECT
    legalbusinessunit.legalid, 
    tags.tagref,
    supptrans.hold as statusname,
    supptrans.hold as nu_estatus,
    '' as txt_justificacion,
    supptrans.ln_ue,
    DATE_FORMAT(NOW(), '%d-%m-%Y') as dtm_fecha,
    supptrans.suppreference as nu_id_compromiso,
    '' as sn_contrato,
    supptrans.supplierno as supplierid
    FROM supptrans
    JOIN suppallocs ON suppallocs.transid_allocfrom = supptrans.id
    JOIN supptrans supptransDocPago ON supptransDocPago.id = suppallocs.transid_allocto
    JOIN tags ON tags.tagref = supptrans.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    WHERE
    supptrans.type = '".$type."'
    AND supptrans.transno = '".$transno."'
    AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
    // , '---', supptrans.*
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
        $estatus = $myrow['nu_estatus'];
        $statusname = $myrow['statusname'];
        $justificacion = $myrow['txt_justificacion'];
        $ln_ue = $myrow['ln_ue'];

        $fechaCaptura = $myrow['dtm_fecha'];
        $txtIdCompromiso = $myrow['nu_id_compromiso'];
    }

    if (empty($justificacion)) {
        $justificacion = "";
    }

    $reponse['datos'] = $info;
    $reponse['legalid'] = $legalid;
    $reponse['tagref'] = $tagref;
    $reponse['ln_ue'] = $ln_ue;
    $reponse['transno'] = $transno;
    $reponse['type'] = $type;
    $reponse['estatus'] = $estatus;
    $reponse['statusname'] = $statusname;
    $reponse['justificacion'] = $justificacion;

    $reponse['fechaCaptura'] = $fechaCaptura;
    
    $reponse['txtIdCompromiso'] = $txtIdCompromiso;

    $reponse['infoClaves'] = $infoClaves;
    $reponse['tipoCom'] = $tipoCom;
    $reponse['folCom'] = $folCom;
    $reponse['idCom'] = $compromisosPago;

    $contenido = $reponse;
    $result = true;
}

if ($option == 'cargarInfoNoCaptura') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];

    // Obtener periodo actual para compromisos
    $periodoActual = GetPeriod(date('d/m/y'), $db);

    $infoRed = array();
    $SQL = "
    SELECT 
    distinct chartdetailsbudgetlog.cvefrom,
    CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento,
    chartdetailsbudgetlog.nu_id_compromiso,
    chartdetailsbudgetlog.nu_id_devengado,
    chartdetailsbudgetlog.nu_idret
    FROM chartdetailsbudgetlog
    JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
    WHERE chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."'
    AND chartdetailsbudgetlog.nu_tipo_movimiento = '265'
    -- AND chartdetailsbudgetlog.qty < 0
    ORDER BY idmov ASC
    ";
    //print_r($SQL);
    //exit();
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $searchRectic=" SELECT tb_rectificaciones.nu_type, tb_rectificaciones.nu_transno, tb_rectificaciones.sn_tagref,
                               tb_rectificaciones.ln_ue, tb_rectificaciones.nu_type_pago, tb_rectificaciones.nu_folio_pago,chartdetailsbudgetlog.cvefrom, CAST(SUM(chartdetailsbudgetlog.qty) as decimal(16,4)) AS total_rectifi
                               FROM tb_rectificaciones
                               JOIN chartdetailsbudgetlog ON tb_rectificaciones.nu_transno = chartdetailsbudgetlog.transno  AND tb_rectificaciones.nu_type = chartdetailsbudgetlog.type
                               WHERE tb_rectificaciones.nu_type_pago = 22 AND tb_rectificaciones.nu_transno = ".$transno." AND tb_rectificaciones.nu_type = ".$type."
                               AND tb_rectificaciones.nu_estatus = 4 AND chartdetailsbudgetlog.qty < 0 AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                               GROUP BY chartdetailsbudgetlog.cvefrom ";


    $ErrMsg = "No se realizo la Consulta";
    $resultRectificacionc = DB_query($searchRectic, $db, $ErrMsg);

    $clavesPago = "";
    $compromisosPago = "";
    while ($myrow = DB_fetch_array($TransResult)) {
        $clave = $myrow['cvefrom'];
        $account = "";
        $legalid = "";
        $period = "";
        $tipoAfectacion = "";
        $tipoMovimiento = $myrow['tipoMovimiento'];
        $nu_id_compromiso = $myrow['nu_id_compromiso'];
        $nu_id_devengado = $myrow['nu_id_devengado'];
        $nu_idret = $myrow['nu_idret'];

        $compromisosPago = $myrow['nu_id_compromiso'];

        if ($clavesPago == "") {
            $clavesPago .= "'".$clave."'";
        } else {
            $clavesPago .= ", '".$clave."'";
        }

        if(DB_num_rows($resultRectificacionc) > 0){

            while($rowsResults = DB_fetch_array($resultRectificacionc)){
                if($rowsResults['total_rectifi'] < 0){
                    $tipoMovimiento = 'Ampliacion';
                    $infoDatos = fnInfoPresupuesto($db,  $rowsResults['cvefrom'], $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento, '', '', '', 0, $nu_id_devengado, $nu_id_compromiso, $nu_idret);

                }


            }

        }

        $infoDatos = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento, '', '', '', 0, $nu_id_devengado, $nu_id_compromiso, $nu_idret);

        $infoRed[] = $infoDatos;
    }

    if (empty($clavesPago)) {
        $clavesPago = "''";
    }

    // print_r($infoRed);
    //exit();
    //Obtener nombre de estatus
    $statusname = "";
    $estatus = "";
    $legalid = "";
    $tagref = "";
    $justificacion = "";
    $ln_ue = "";

    $fechaCaptura = date('d-m-Y');
    $selectTipo = '';
    $txtIdCompromiso = '';
    $typePago = 0;
    $transnoPago = 0;

    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref, tb_botones_status.statusname, 
    tb_rectificaciones.nu_estatus,
    tb_rectificaciones.txt_justificacion, 
    tb_rectificaciones.ln_ue,
    DATE_FORMAT(tb_rectificaciones.dtm_fecha, '%d-%m-%Y') as dtm_fecha,
    tb_rectificaciones.nu_type_pago,
    tb_rectificaciones.nu_transno_pago,
    tb_rectificaciones.nu_folio_pago,
    tb_rectificaciones.nu_tipo
    FROM tb_rectificaciones
    JOIN tags ON tags.tagref = tb_rectificaciones.sn_tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_rectificaciones.nu_estatus AND tb_botones_status.sn_funcion_id = tb_rectificaciones.sn_funcion_id
    WHERE 
    tb_rectificaciones.nu_type = '".$type."'
    AND tb_rectificaciones.nu_transno = '".$transno."'
    AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
        $estatus = $myrow['nu_estatus'];
        $statusname = $myrow['statusname'];
        $justificacion = $myrow['txt_justificacion'];
        $ln_ue = $myrow['ln_ue'];

        $fechaCaptura = $myrow['dtm_fecha'];
        $txtIdCompromiso = $myrow['nu_folio_pago'];
        $selectTipo = $myrow['nu_tipo'];

        $typePago = $myrow['nu_type_pago'];
        $transnoPago = $myrow['nu_transno_pago'];
    }

    $infoClaves = array();
    $tipoCom = "";
    $folCom = "";
    if (!empty($compromisosPago)) {
        // Obtener claves del pago
        $info = array();
        $SQL = "SELECT
        distinct chartdetailsbudgetlog.cvefrom,
        CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento,
        chartdetailsbudgetlog.nu_id_compromiso,
        chartdetailsbudgetlog.nu_id_devengado,
        chartdetailsbudgetlog.nu_idret
        FROM chartdetailsbudgetlog
        WHERE
        chartdetailsbudgetlog.sn_disponible = 1
        AND chartdetailsbudgetlog.nu_tipo_movimiento = 265
        AND chartdetailsbudgetlog.type = '".$typePago."'
        AND transno = '".$transnoPago."'
        ORDER BY idmov ASC
        ";
        $ErrMsg = "No se obtuvieron los presupuestos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $clavesPago = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            $clave = $myrow['cvefrom'];
            
            if ($clavesPago == "") {
                $clavesPago .= "'".$clave."'";
            } else {
                $clavesPago .= ", '".$clave."'";
            }
        }

        $SQL = "SELECT
        distinct
        chartdetailsbudgetlog.cvefrom
        FROM chartdetailsbudgetlog
        WHERE
        chartdetailsbudgetlog.nu_tipo_movimiento = '259'
        AND chartdetailsbudgetlog.nu_id_compromiso = '".$compromisosPago."'
        AND chartdetailsbudgetlog.cvefrom NOT IN (".$clavesPago.")
        GROUP BY chartdetailsbudgetlog.cvefrom";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $infoClaves[] = array(
                'accountcode' => $myrow ['cvefrom']
            );
        }

        $SQL = "SELECT 
        tb_compromiso.nu_type,
        tb_compromiso.nu_transno,
        tb_compromiso.nu_estatus,
        tb_botones_status.statusname
        FROM tb_compromiso 
        LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_compromiso.nu_estatus AND tb_botones_status.sn_funcion_id = tb_compromiso.sn_funcion_id
        WHERE tb_compromiso.nu_id_compromiso = '".$compromisosPago."'
        AND tb_compromiso.nu_tipo = 'O'";
        // AND tb_compromiso.nu_estatus = '4'
        $ErrMsg = "No se obtuvo información del compromiso";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) > 0) {
            $myrow = DB_fetch_array($TransResult);
            $tipoCom = $myrow ['nu_type'];
            $folCom = $myrow ['nu_transno'];
        }
    }

    if (empty($justificacion)) {
        $justificacion = "";
    }

    $reponse['datos'] = $infoRed;
    $reponse['legalid'] = $legalid;
    $reponse['tagref'] = $tagref;
    $reponse['ln_ue'] = $ln_ue;
    $reponse['transno'] = $transno;
    $reponse['type'] = $type;
    $reponse['estatus'] = $estatus;
    $reponse['statusname'] = $statusname;
    $reponse['justificacion'] = $justificacion;

    $reponse['fechaCaptura'] = $fechaCaptura;
    
    $reponse['txtIdCompromiso'] = $txtIdCompromiso;
    $reponse['selectTipo'] = $selectTipo;

    $reponse['typePago'] = $typePago;
    $reponse['transnoPago'] = $transnoPago;

    $reponse['infoClaves'] = $infoClaves;
    $reponse['tipoCom'] = $tipoCom;
    $reponse['folCom'] = $folCom;
    $reponse['idCom'] = $compromisosPago;

    $contenido = $reponse;//array('datos' => $info);
    $result = true;
}

if ($option == 'guardarOperacion') {
    $datosReducciones = $_POST['datosCapturaReducciones'];
    $datosAmpliaciones = $_POST['datosCapturaAmpliaciones'];
    $datosImpuestos = $_POST['datosCapturaImpuestos'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $fechaAutorizacion = $_POST['fechaAutorizacion'];
    $typeSuficiencia = 265;
    $description = "Manual";
    $functionSuf = $funcion;
    $justificacion = $_POST['justificacion'];
    $ue = $_POST['ue'];
    $tipoSuficiencia = $_POST['selectTipo'];
    $txtIdCompromiso = $_POST['txtIdCompromiso'];
    $typePago = $_POST['typePago'];
    $transnoPago = $_POST['transnoPago'];

    $tipoCom = $_POST['tipoCom'];
    $folCom = $_POST['folCom'];
    $idCom = $_POST['idCom'];

    // echo "\n tipoSuficiencia: ".$tipoSuficiencia;
    // echo "\n tipoCom: ".$tipoCom;
    // echo "\n folCom: ".$folCom;
    // echo "\n idCom: ".$idCom;
    // exit();

    $nombreSuficiencia = "";

    $fechaCaptura = date_create($fechaCaptura);
    $fechaCaptura = date_format($fechaCaptura, 'Y-m-d');

    $fechaAutorizacion = date_create($fechaAutorizacion);
    $fechaAutorizacion = date_format($fechaAutorizacion, 'Y-m-d');
    
    if (empty($transno)) {        
        $transno = GetNextTransNo($type, $db);
        $datosPresupuesto['transno'] = $transno;
    }

    $SQL = "SELECT nu_type, nu_transno FROM tb_rectificaciones 
            WHERE 
            nu_type = '".$type."' 
            AND nu_transno = '".$transno."'
            AND nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'  ";
    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $sqlOperacion = 1;
    } else {
        $sqlOperacion = 2;
    }
    if ($sqlOperacion == 1) {
        // Agregar datos
        $SQL = "INSERT INTO tb_rectificaciones
        (dtm_fecha,
        sn_userid,
        txt_justificacion,
        nu_type,
        nu_transno,
        nu_estatus,
        sn_tagref,
        sn_funcion_id,
        ln_ue,
        dtm_fecha_autorizacion,
        nu_type_pago,
        nu_transno_pago,
        nu_folio_pago,
        nu_tipo,
        nu_anio_fiscal
        )
        VALUES
        (NOW(), 
        '".$_SESSION['UserID']."', 
        '".$justificacion."', 
        '".$type."', 
        '".$transno."', 
        '".$estatus."',
        '".$tagref."', 
        '".$functionSuf."', 
        '".$ue."',
        '".$fechaAutorizacion."',
        '".$typePago."',
        '".$transnoPago."',
        '".$txtIdCompromiso."',
        '".$tipoSuficiencia."',
        '".$_SESSION['ejercicioFiscal']."'
        )";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else if ($sqlOperacion == 2) {
        // Actualizar datos
        $SQL = "UPDATE tb_rectificaciones SET 
        sn_userid = '".$_SESSION['UserID']."', 
        txt_justificacion = '".$justificacion."', 
        nu_estatus = '".$estatus."', 
        sn_tagref = '".$tagref."', 
        sn_funcion_id = '".$functionSuf."', 
        ln_ue = '".$ue."', 
        dtm_fecha_autorizacion = '".$fechaAutorizacion."', 
        nu_type_pago = '".$typePago."', 
        nu_transno_pago = '".$transnoPago."', 
        nu_folio_pago = '".$txtIdCompromiso."',
        nu_tipo = '".$tipoSuficiencia."'
        WHERE 
        nu_type = '".$type."' 
        AND nu_transno = '".$transno."'
        AND nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    // Borrar registros en 0
    $SQL = "DELETE FROM chartdetailsbudgetlog 
    WHERE 
    chartdetailsbudgetlog.type = '".$type."' 
    AND chartdetailsbudgetlog.transno = '".$transno."'";
    $ErrMsg = "No se borraron los registros anteriores";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    // Log en negativo para la reduccion
    $operacionLog = -1;
    $ordenOperacion = 'DESC';
    $movimientoTipo = '';

    // Agregar información para reducciones
    foreach ($datosReducciones as $datosClave) {
        $clave = $datosClave['accountcode'];
        $ln_ue = fnObtenerUnidadEjecutoraClave($db, $clave);
        $description = "Rectificación en tramite. Folio ".$transno;
        $numMes = 1;
        foreach ($dataJsonMeses as $nameMes) {
            $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
            $cantidad = $datosClave[$nameMes] * $operacionLog;

            if (!empty($datosClave['noCompromiso'])) {
                $movimientoTipo = 'Compromiso';
            } else {
                $movimientoTipo = '';
            }
            
            if (abs($cantidad) != 0) {
                // Si tiene cantidad
                $respuesta = fnInsertPresupuestoLogAcomulado($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $typeSuficiencia, "", $description, 0, $estatus, 0, $ln_ue, $ordenOperacion, 'disponible', $movimientoTipo, 'Reduccion', 1, $datosClave['noDevengado'], $datosClave['noCompromiso'], $datosClave['noRetencion']);
            }
            
            $numMes ++;
        }
    }

    // Log en positivo para la ampliacion
    $operacionLog = 1;
    $ordenOperacion = 'ASC';
    $movimientoTipo = 'Pago';

    // Agregar información para ampliaciones
    foreach ($datosAmpliaciones as $datosClave) {
        $clave = $datosClave['accountcode'];
        $ln_ue = fnObtenerUnidadEjecutoraClave($db, $clave);
        $description = "Rectificación en tramite. Folio ".$transno;
        $numMes = 1;
        foreach ($dataJsonMeses as $nameMes) {
            $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
            $cantidad = $datosClave[$nameMes] * $operacionLog;
            
            if (abs($cantidad) != 0) {
                // Si tiene cantidad
                $respuesta = fnInsertPresupuestoLogAcomulado($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $typeSuficiencia, "", $description, 0, $estatus, 0, $ln_ue, $ordenOperacion, 'disponible', $movimientoTipo, 'Reduccion', 1, $datosClave['noDevengado'], $datosClave['noCompromiso'], $datosClave['noRetencion']);
            }
            
            $numMes ++;
        }
    }

    // echo "\n datosReducciones: \n";
    // print_r($datosReducciones);
    // echo "\n datosAmpliaciones: \n";
    // print_r($datosAmpliaciones);
    // exit();
    
    $Mensaje = "Se ha guardado exitosamente la Rectificación con Folio ".$transno;

    //Obtener nombre de estatus
    $statusname = "";
    $SQL = "SELECT statusname as statusname, statusname as statusname2
            FROM tb_botones_status WHERE statusid = '".$estatus."' AND tb_botones_status.sn_funcion_id = '".$funcion."'";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $statusname = $myrow['statusname'];
    }

    $datosPresupuesto['transno'] = $transno;
    $datosPresupuesto['type'] = $type;
    $datosPresupuesto['estatus'] = $estatus;
    $datosPresupuesto['statusname'] = $statusname;

    $datosPresupuesto['txtIdCompromiso'] = $txtIdCompromiso;

    $contenido = array('datos' => $datosPresupuesto);
    $result = true;
}

if ($option == 'eliminaPresupuesto') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $clave = $_POST['clave'];
    $ampliacionReduccion = $_POST['tipo'];
    $tipoAdecuacion = $_POST['tipoAdecuacion'];

    $SQL = "DELETE FROM chartdetailsbudgetlog 
            WHERE type = '".$type."' AND transno = '".$transno."' AND cvefrom = '".$clave."' ";
    $ErrMsg = "No se elimino la Clave Presupuestal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $Mensaje = "Eliminación correcta de la Clave ".$clave;

    $contenido = $Mensaje;
    $result = true;
}

function fnBusquedaDefault($db, $sqlWhere)
{
    $info = array();

    $SQL = "
    SELECT
    distinct
    chartdetailsbudgetbytag.*
    FROM chartdetailsbudgetbytag
    JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = chartdetailsbudgetbytag.tagref AND tb_sec_users_ue.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE 
    chartdetailsbudgetbytag.anho = '".date('Y')."'
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

    return $info;
}

if ($option == 'obtenerPresupuestosBusqueda') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $filtrosClave = $_POST['filtrosClave'];
    
    $sqlWhere = "";
    if ($legalid != "" and $legalid != '-1') {
        $sqlWhere .= " AND legalbusinessunit.legalid = '".$legalid."' ";
    }
    if ($tagref != "" and $tagref != '-1') {
        $sqlWhere .= " AND tags.tagref = '".$tagref."' ";
    }

    if ($ue != "" and $ue != '-1') {
        $sqlWhere .= " AND tb_sec_users_ue.ue = '".$ue."' ";
    }

    if (!empty($filtrosClave)) {
        foreach ($filtrosClave as $datosClave) {
            if ($datosClave['valor'] != '-1') {
                // echo "\n campoPresupuesto: ".$datosClave['campoPresupuesto'];
                $sqlWhere .= " AND chartdetailsbudgetbytag.".$datosClave['campoPresupuesto']." = '".$datosClave['valor']."' ";
            }
        }
    }

    $info = array();

    $info = fnBusquedaDefault($db, $sqlWhere);

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

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
