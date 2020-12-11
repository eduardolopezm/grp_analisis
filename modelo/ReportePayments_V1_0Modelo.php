<?php
/**
 * Reporte de Cheques y transferencias
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 03/10/2018
 * Fecha Modificación: 03/10/2018
 * Modelo Reporte de Cheques y transferencias
 */
//
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
$funcion=370;
include $PathPrefix."includes/SecurityUrl.php";
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

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'mostrarTipoPago') {
    $info = array();
    $SQL = "SELECT paymentmethodssat.paymentid as value,
    paymentmethodssat.paymentname as texto
    FROM paymentmethodssat
    WHERE paymentmethodssat.sn_tesoreriaTipoPago = 1
    ORDER BY paymentmethodssat.paymentname ASC";
    $ErrMsg = "No se obtuvieron las cuentas de bancos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarBancos') {
    $info = array();
    $SQL = "SELECT banks.bank_id as value,
    banks.bank_shortdescription as texto
    FROM banks
    WHERE banks.bank_active = 1
    ORDER BY banks.bank_shortdescription ASC";
    $ErrMsg = "No se obtuvieron los bancos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarBancosCuentas') {
    $sqlWhere = '';
    if (isset($_POST['bancos'])) {
        // Si trae bancos
        $sqlWhere = " AND bankaccounts.bankid IN (".$_POST['bancos'].") ";
    }
    $info = array();
    $SQL="SELECT 
    distinct bankaccounts.accountcode as value,
    bankaccountname as texto,
    bankaccounts.currcode as moneda
    FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
    WHERE bankaccounts.accountcode=chartmaster.accountcode 
    AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
    AND tagsxbankaccounts.tagref = sec_unegsxuser.tagref 
    AND sec_unegsxuser.userid = '". $_SESSION['UserID'] ."' ".$sqlWhere."
    ORDER BY bankaccountname ASC";
    $ErrMsg = "No se obtuvieron las cuentas de bancos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerSificiencia') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    $ue = $_POST['ue'];
    $tipoPago = $_POST['tipoPago'];
    $folio = $_POST['folio'];

    $bancos = $_POST['bancos'];
    $cuentasBancos = $_POST['cuentasBancos'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }

    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND supptrans.tagref IN (".$tagref.") ";
    }

    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND banktrans.transdate between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND banktrans.transdate >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND banktrans.transdate <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        $sqlWhere .= " AND supptrans.ln_ue IN (".$ue.") ";
    }

    if ($tipoPago != '') {
        // $sqlWhere .= " AND tb_pagos.nu_type IN (".$tipoPago.") ";
        $dato = "";
        if (strpos($tipoPago, '02') !== false) {
            // Cheque
            $dato .= "'Cheque'";
        } else if (strpos($tipoPago, '03') !== false) {
            // Transferencia
            $dato .= "'Transferencia'";
        }

        if ($dato != "") {
            $sqlWhere .= " AND banktrans.banktranstype IN (".$dato.") ";
        }
    }

    if ($folio != '') {
        $sqlWhere .= " AND banktrans.chequeno = '".$folio."' ";
    }

    if ($cuentasBancos != '') {
        $sqlWhere .= " AND banktrans.bankact IN (".$cuentasBancos.") ";
    }

    if ($bancos != '') {
        $sqlWhere .= " AND bankaccounts.bankid IN (".$bancos.") ";
    }
    
    $info = array();
    $SQL = "
    SELECT
    DISTINCT
    DATE_FORMAT(supptrans.trandate, '%d-%m-%Y') as trandate,
    supptrans.tagref,
    supptrans.ln_ue,
    banktrans.banktranstype,
    banktrans.chequeno,
    supptrans.txt_referencia,
    supptrans.txt_clave_rastreo,
    supptrans.ovamount as total
    FROM banktrans
    INNER JOIN supptrans on banktrans.transno = supptrans.transno AND banktrans.nu_type = supptrans.type
    INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
    JOIN tags ON tags.tagref = supptrans.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN bankaccounts ON bankaccounts.accountcode = banktrans.bankact
    WHERE 
    1 = 1 AND supptrans.hold IN(2,3,6,7) ".$sqlWhere."
    ORDER BY banktrans.chequeno DESC
    ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $operacion = "";
        $seleccionar = "";

        $urlGeneral = "&transno=>" . $myrow['nu_transno'] . "&type=>" . $myrow['nu_type'];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $liga= "URL=" . $url;
        $operacion = '<a type="button" id="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" name="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" href="pagos.php?'.$liga.'" title="Detalle Pago" style="color: blue;">'.$myrow ['nu_transno'].'</a>'; // target="_blank"
        
        $impresion = '<a type="button" id="btnImprimir'.$myrow['nu_transno'].'" name="btnImprimir'.$myrow['nu_transno'].'" href="impresion_suficiencia.php?'.$liga.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

        $info[] = array(
            'id1' =>false,
            'ur' => $myrow ['tagref'],
            'ue' => $myrow ['ln_ue'],
            'fecha_captura' => $myrow ['trandate'],
            'medioPago' => $myrow ['banktranstype'],
            'folio' => $myrow ['chequeno'],
            'referencia' => $myrow ['txt_referencia'],
            'claveRastreo' => $myrow ['txt_clave_rastreo'],
            'total' => ($myrow ['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'total2' => ($myrow ['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0)
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    // $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    $columnasNombres .= "{ name: 'medioPago', type: 'string' },";
    $columnasNombres .= "{ name: 'folio', type: 'string' },";
    $columnasNombres .= "{ name: 'referencia', type: 'string' },";
    $columnasNombres .= "{ name: 'claveRastreo', type: 'string' },";
    $columnasNombres .= "{ name: 'total', type: 'double' },";
    $columnasNombres .= "{ name: 'total2', type: 'double' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $colResumenTotal= ", aggregates: [{'<b>Total</b>' :".
                            "function (aggregatedValue, currentValue) {".
                                "var total = currentValue;".
                                "return aggregatedValue + total;".
                            "}".
                        "}] ";

    $columnasNombresGrid .= "[";
    // $columnasNombresGrid .= " { text: '', datafield: 'id1', width: '3%', editable: true, editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha', datafield: 'fecha_captura', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Medio de Pago', datafield: 'medioPago', width: '15%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio', width: '15%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Referencia', datafield: 'referencia', width: '17%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave de Rastreo', datafield: 'claveRastreo', width: '18%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total', width: '15%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total2', width: '15%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
