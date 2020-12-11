<?php
/**
 * Panel Pagos Diversos (Compromisos, Directos, Viáticos, Subsidios, etc.)
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 13/08/2018
 * Fecha Modificación: 13/08/2018
 * Modelo para el proceso del panel de Pagos Diversos (Compromisos, Directos, Viáticos, Subsidios, etc.)
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
$funcion=2443;
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

if ($option == 'mostrarClabeProveedor') {
    $info = array();
    $proveedor = $_POST['supplierid'];
    $SQL = "SELECT
    tb_bancos_proveedores.nu_id as value,
    tb_bancos_proveedores.nu_clabe_interbancaria as texto
    FROM tb_bancos_proveedores
    WHERE
    tb_bancos_proveedores.ln_supplierid = '".$proveedor."'
    AND tb_bancos_proveedores.ln_activo = 1
    ORDER BY tb_bancos_proveedores.nu_clabe_interbancaria ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarSelectEstatus') {
    $info = array();
    $SQL = "SELECT 
    distinct statusid as value, sn_nombre_secundario as texto, statusid
    FROM tb_botones_status 
    WHERE 
    tb_botones_status.sn_funcion_id = '".$funcion."'
    AND sn_flag_disponible = '1' AND statusid < '90' 
    ORDER BY texto ASC";
    $ErrMsg = "No se obtuvo los Estatus";
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
    $tipoSuficiencia = $_POST['tipoSuficiencia'];
    $folio = $_POST['folio'];

    $txtProveedor = $_POST['txtProveedor'];
    $txtIdDevengado = $_POST['txtIdDevengado'];
    $selectEstatusCompromiso = $_POST['selectEstatusCompromiso'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }
    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND tb_pagos.sn_tagref IN (".$tagref.") ";
    }
    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_pagos.dtm_fecha between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND tb_pagos.dtm_fecha >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_pagos.dtm_fecha <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        $sqlWhere .= " AND (tb_pagos.ln_ue IN (".$ue.") OR chartdetailsbudgetlog.ln_ue IN (".$ue.")) ";
    }

    if ($tipoSuficiencia != '') {
        $sqlWhere .= " AND tb_pagos.nu_type IN (".$tipoSuficiencia.") ";
    }

    if ($folio != '') {
        // $sqlWhere .= " AND tb_pagos.nu_transno like '%".$folio."%' ";
        $sqlWhere .= " AND tb_pagos.nu_transno = '".$folio."' ";
    }

    if (!empty($txtProveedor)) {
        $sqlWhere .= " AND tb_pagos.supplierid = '".$txtProveedor."' ";
    }

    if (!empty($txtIdDevengado)) {
        $sqlWhere .= " AND tb_pagos.nu_id_devengado = '".$txtIdDevengado."' ";
    }

    if ($selectEstatusCompromiso != '') {
        $sqlWhere .= " AND tb_pagos.nu_estatus IN (".$selectEstatusCompromiso.") ";
    }
    
    // AND log.description not like 'Autorización Orden de Compra%' se agrega para no tomar en cuenta la autorizacion parcial y deja lo original visible
    $info = array();
    $SQL = "
    SELECT
    DISTINCT
    tb_pagos.nu_type,
    tb_pagos.nu_transno,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.type = tb_pagos.nu_type 
    AND log.transno = tb_pagos.nu_transno 
    AND log.nu_tipo_movimiento = 260
    ) as total,
    tb_pagos.nu_estatus,
    tb_pagos.sn_userid,
    www_users.realname,
    DATE_FORMAT(tb_pagos.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_pagos.sn_tagref,
    tags.tagname,
    tb_pagos.txt_justificacion as sn_description,
    CONCAT(tb_pagos.nu_type, ' - ', systypescat.typename) as nombreSuficiencia,
    CONCAT(tags.tagref, ' - ', tags.tagname) as tagname,
    tb_pagos.nu_estatus as statusid,
    tb_botones_status.statusname,
    tb_pagos.sn_tagref,
    tb_pagos.ln_ue,
    tb_pagos.nu_id_compromiso,
    tb_pagos.nu_id_devengado,
    CONCAT(tb_pagos.supplierid, ' - ', suppliers.suppname) as suppname
    FROM tb_pagos
    LEFT JOIN systypescat ON systypescat.typeid = tb_pagos.nu_type
    LEFT JOIN www_users ON www_users.userid = tb_pagos.sn_userid
    JOIN tags ON tags.tagref = tb_pagos.sn_tagref
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_pagos.nu_estatus AND tb_botones_status.sn_funcion_id = tb_pagos.sn_funcion_id
    LEFT JOIN suppliers ON suppliers.supplierid = tb_pagos.supplierid
    LEFT JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_pagos.nu_type AND chartdetailsbudgetlog.transno = tb_pagos.nu_transno
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_pagos.sn_tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND tb_pagos.sn_tagref = `tb_sec_users_ue`.`tagref` AND  tb_pagos.ln_ue = `tb_sec_users_ue`.`ue`
    WHERE 1 = 1 AND tb_pagos.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."' ".$sqlWhere."
    ORDER BY tb_pagos.nu_transno DESC
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
        
        $impresion = '<a type="button" id="btnImprimir'.$myrow['nu_transno'].'" name="btnImprimir'.$myrow['nu_transno'].'" href="impresionDevengado.php?'.$liga.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

        $info[] = array(
            'id1' =>false,
            'nu_type' => $myrow ['nu_type'],
            'nu_transno' => $myrow ['nu_transno'],
            'operacion' => $operacion,
            'requisitionno' => 'requisitionno', // $myrow ['requisitionno'],
            'total' => ($myrow ['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'total2' => ($myrow ['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'tipoSufuciencia' => $myrow['nombreSuficiencia'],
            'sn_description' => $myrow['sn_description'],
            'statusname' => $myrow ['statusname'],
            'realname' => $myrow ['realname'],
            'fecha_captura' => $myrow ['fecha_captura'],
            'sn_tagref' => $myrow ['sn_tagref'],
            'tagname' => $myrow ['tagname'],
            'nu_tipo' => $myrow ['nu_type'],
            'imprimir' => $impresion,
            'statusid' => $myrow['statusid'],
            'ur' => $myrow ['sn_tagref'],
            'ue' => $myrow ['ln_ue'],
            'idcompromiso' => $myrow ['nu_id_compromiso'],
            'iddevengado' => $myrow ['nu_id_devengado'],
            'suppname' => $myrow['suppname']
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoSufuciencia', type: 'string' },";
    $columnasNombres .= "{ name: 'iddevengado', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_transno', type: 'string' },";
    $columnasNombres .= "{ name: 'operacion', type: 'string' },";
    $columnasNombres .= "{ name: 'suppname', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_description', type: 'string' },";
    $columnasNombres .= "{ name: 'statusname', type: 'string' },";
    $columnasNombres .= "{ name: 'tagname', type: 'string' },";
    $columnasNombres .= "{ name: 'total', type: 'double' },";
    $columnasNombres .= "{ name: 'total2', type: 'double' },";
    $columnasNombres .= "{ name: 'imprimir', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_type', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_tagref', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_tipo', type: 'string' },";
    $columnasNombres .= "{ name: 'requisitionno', type: 'string' },";
    $columnasNombres .= "{ name: 'statusid', type: 'string' },";
    $columnasNombres .= "{ name: 'idcompromiso', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $colResumenTotal= ", aggregates: [{'<b>Total</b>' :".
                            "function (aggregatedValue, currentValue) {".
                                "var total = currentValue;".
                                "return aggregatedValue + total;".
                            "}".
                        "}] ";

    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: '', datafield: 'id1', width: '3%', editable: true, editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '3%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Operación', datafield: 'tipoSufuciencia', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Devengado', datafield: 'iddevengado', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'nu_transno', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'operacion', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Proveedor / Beneficiario', datafield: 'suppname', width: '14%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Justificación', datafield: 'sn_description', width: '22%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'statusname', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'tagname', width: '12%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total', width: '10%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total2', width: '10%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'imprimir', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'nu_type', datafield: 'nu_type', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'sn_tagref', datafield: 'sn_tagref', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'nu_tipo', width: '15%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Req', datafield: 'requisitionno', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'statusid', datafield: 'statusid', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'idcompromiso', datafield: 'idcompromiso', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

/**
 * Función para obtener los devengados de la trasacción
 * @param  [type] $db      Base de datos
 * @param  [type] $type    Tipo de docuemento
 * @param  [type] $transno No de operación
 * @return [type]          Cadena con devengados
 */
function fnObtenerIdDevengado($db, $type, $transno) {
    // Obtener id devengados para consulta
    $cadenaDevengados = "";
    $SQL = "SELECT
    DISTINCT
    chartdetailsbudgetlog.nu_id_devengado
    FROM chartdetailsbudgetlog
    WHERE
    chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."'";
    $ErrMsg = "No se obtuvieron los registros del detalle No. de Captura ".$datosClave ['transno'];
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($cadenaDevengados == "") {
            $cadenaDevengados .= "'".$myrow['nu_id_devengado']."'";
        } else {
            $cadenaDevengados .= ", '".$myrow['nu_id_devengado']."'";
        }
    }
    return $cadenaDevengados;
}

if ($option == 'validarDisponibleNoCaptura') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    $anioValidacion = 0;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;

        $movimientoTipo = '';
        $textoMensaje = 'disponible';
        if ($datosClave['type'] != '294') {
            // Si no es original validar compromiso
            $movimientoTipo = 'Compromiso';
            $textoMensaje = 'compromiso';
        }

        if ($datosClave['type'] == '298') {
            // Si pago de retenciones
            $movimientoTipo = 'Retenciones';
            $textoMensaje = 'disponible';
        }

        if ($datosClave['type'] == '299') {
            // Si pago de retenciones
            $movimientoTipo = 'Devengado';
            $textoMensaje = 'disponible';
        }

        if ($statusid == '100') {
            // Si generar el documento de pago
            if ($datosClave['type'] == '299') {
                // Si es decremento no puede generar documento de pago
                $myrow = fnInformacionTipoDocumento($db, $datosClave['type']);
                $result = false;
                $actualizar = 0;
                $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Operación '.$myrow['typeid'].' - '.$myrow['typename'].' con Folio '.$datosClave ['transno'].' no puede generar pago en tesoreria </p>';
            } else {
                // Validar disponible de pago
                $SQL = "SELECT
                tb_pagos.nu_estatus
                FROM tb_pagos
                WHERE
                tb_pagos.nu_type = '".$datosClave ['type']."'
                AND tb_pagos.nu_transno = '".$datosClave ['transno']."'";
                $ErrMsg = "No se obtuvo el estatus del pago";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                $myrow = DB_fetch_array($TransResult);
                if ($myrow['nu_estatus'] != 4) {
                    // No esta autorizado el pago
                    $myrow = fnInformacionTipoDocumento($db, $datosClave['type']);
                    $result = false;
                    $actualizar = 0;
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Operación '.$myrow['typeid'].' - '.$myrow['typename'].' con Folio '.$datosClave ['transno'].' no se encuentra autorizado </p>';
                } else {
                    // Si esta autorizado el pago
                    $SQL = "
                    SELECT supptrans.hold
                    FROM supptrans
                    WHERE 
                    (supptrans.hold = 0 or supptrans.hold = 1)
                    AND supptrans.type = '".$datosClave ['type']."'
                    AND supptrans.transno = '".$datosClave ['transno']."'";
                    $ErrMsg = "Validar documentos en tesoreria";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($TransResult) > 0) {
                        // tiene documentosç
                        $result = false;
                        $actualizar = 0;
                        $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Devengado '.$datosClave ['iddevengado'].' tiene una operación pendiente en tesoreria. Para realizar el proceso deberá ser rechazada </p>';
                    } else {
                        $cadenaDevengados = fnObtenerIdDevengado($db, $datosClave ['type'], $datosClave ['transno']);

                        if ($cadenaDevengados == "") {
                            // Si no obtuvo regitros devengados
                            $result = false;
                            $actualizar = 0;
                            $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Operación '.$myrow['typeid'].' - '.$myrow['typename'].' con Folio '.$datosClave ['transno'].' no puede generar pago en tesoreria. No se encontro información del devengado </p>';
                        } else {
                            // Si encontro información de devengados
                            $sqlWhere = " AND chartdetailsbudgetlog.nu_idret = 0 ";
                            if ($datosClave['type'] == '298') {
                                // Si es de impuestos
                                $sqlWhere = " AND chartdetailsbudgetlog.nu_idret != 0 ";
                            }
                            $SQL = "SELECT
                            SUM(chartdetailsbudgetlog.qty) as qty,
                            chartdetailsbudgetlog.cvefrom,
                            chartdetailsbudgetlog.nu_id_devengado,
                            chartdetailsbudgetlog.nu_idret
                            FROM chartdetailsbudgetlog
                            WHERE
                            chartdetailsbudgetlog.nu_id_devengado IN (".$cadenaDevengados.")
                            AND chartdetailsbudgetlog.nu_tipo_movimiento = 260 ".$sqlWhere."
                            GROUP BY chartdetailsbudgetlog.cvefrom, chartdetailsbudgetlog.nu_id_devengado, chartdetailsbudgetlog.nu_idret
                            HAVING qty < 0";
                            $ErrMsg = "No se obtuvieron los registros del detalle No. de Captura ".$datosClave ['transno']." para generar documento de pago";
                            $TransResult = DB_query($SQL, $db, $ErrMsg);
                            if (DB_num_rows($TransResult) == 0) {
                                // No tiene información
                                $result = false;
                                $actualizar = 0;
                                $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Operación '.$myrow['typeid'].' - '.$myrow['typename'].' con Folio '.$datosClave ['transno'].' no puede generar pago en tesoreria. Las claves presupuestales no tiene disponible en el Devengado </p>';
                            }
                        }
                    }
                }
            }
        } else {
            // Todos los pagos
            $SQL = "SELECT 
            tb_pagos.supplierid,
            tb_cat_partidaspresupuestales_partidagenerica.pargcalculado as categoryid,
            cat_Months.mes as mesName,
            chartdetailsbudgetbytag.anho,
            chartdetailsbudgetlog.* 
            FROM chartdetailsbudgetlog
            LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            LEFT JOIN tb_pagos ON tb_pagos.nu_type = chartdetailsbudgetlog.type AND tb_pagos.nu_transno = chartdetailsbudgetlog.transno
            LEFT JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
            LEFT JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
            LEFT JOIN tb_cat_partidaspresupuestales_partidagenerica ON tb_cat_partidaspresupuestales_partidagenerica.ccap = tb_cat_partidaspresupuestales_partidaespecifica.ccap AND tb_cat_partidaspresupuestales_partidagenerica.ccon = tb_cat_partidaspresupuestales_partidaespecifica.ccon AND tb_cat_partidaspresupuestales_partidagenerica.cparg = tb_cat_partidaspresupuestales_partidaespecifica.cparg
            WHERE
            chartdetailsbudgetlog.type = '".$datosClave ['type']."'
            AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."'
            ORDER BY chartdetailsbudgetlog.period ASC";
            // AND chartdetailsbudgetlog.qty < 0
            $ErrMsg = "No se obtuvieron los registros del No. de Captura ".$datosClave ['transno'];
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $infoClaves = array();
            while ($myrow = DB_fetch_array($TransResult)) {
                $nu_id_devengado = '';
                $nu_id_compromiso = '';
                $nu_idret = 0;
                $anioValidacion = $myrow['anho'];

                $infoClaves[] = array(
                    'accountcode' => $myrow ['cvefrom']
                );

                if ($datosClave['type'] == '298' || $datosClave['type'] == '299') {
                    // Si es pago retenciones
                    $nu_id_devengado = $myrow['nu_id_devengado'];
                    $nu_idret = $myrow['nu_idret'];
                }
                
                $disponible = fnInfoPresupuesto($db, $myrow['cvefrom'], '', '', '', 0, 0, '', $datosClave ['type'], $datosClave ['transno'], 'Reduccion', '', '', '', 1, $nu_id_devengado, $nu_id_compromiso, $nu_idret);

                foreach ($disponible as $dispo) {
                    // echo "\n ".$myrow['mesName'].": ".$dispo[$myrow['mesName'].$movimientoTipo];
                    // echo "\n sol: ".abs($myrow['qty']);
                    if ($dispo[$myrow['mesName'].$movimientoTipo] < abs($myrow['qty'])) {
                        $result = false;
                        $actualizar = 0;
                        $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Devengado '.$datosClave ['iddevengado'].' con Folio '.$datosClave ['transno'].' la clave presupuestal '.$myrow['cvefrom'].' en '.$myrow['mesName'].' el '.$textoMensaje.' es $ '.number_format($dispo[$myrow['mesName'].$movimientoTipo], $_SESSION['DecimalPlaces'], '.', ',').' y se solicita un monto de $ '.number_format(abs($myrow['qty']), $_SESSION['DecimalPlaces'], '.', ',').' </p>';
                    }
                }

                // Validar matriz del devengado, Inicio
                $ln_clave_iden = fnObtenerIdentificadorClavePrespuesto($db, $myrow['cvefrom']);
                $sqlWhere = "";
                if (trim($ln_clave_iden) != '') {
                    // Solo cuando se tiene identidicador
                    $sqlWhere = " AND ln_clave = '".$ln_clave_iden."' ";
                }
                $SQL = "SELECT stockact, accountegreso FROM stockcategory 
                WHERE 
                categoryid = '".$myrow['categoryid']."' 
                AND accountegreso IN (SELECT accountcode FROM accountxsupplier WHERE supplierid = '".$myrow['supplierid']."') ".$sqlWhere;
                $ErrMsg = "No se obtuvieron los registros de la matriz del devengado";
                $resultCuenta = DB_query($SQL, $db, $ErrMsg);
                if (DB_num_rows($resultCuenta) == 0) {
                    // No tiene configuración del devengado
                    $result = false;
                    $actualizar = 0;
                    if (strpos($mensajeErrores, $myrow['categoryid']) === false) {
                        $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La partida '.$myrow['categoryid'].' no esta configurada en la matriz del devengado</p>';
                    }
                }
                // Validar matriz del devengado, Fin
            }

            // Validar periodos contables
            $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
            if (!$respuesta['result']) {
                $result = false;
                $actualizar = 0;
                $mensajeErrores .= $respuesta['mensaje'];
            }
        }

        $info[] = array(
            'type' => $datosClave ['type'],
            'transno' => $datosClave ['transno'],
            'actualizar' => $actualizar
        );
    }
    
    $contenido = array('datos' => $info, 'mensajeErrores' => $mensajeErrores);
}

/**
 * Función para crear el documento de pago que se visualiza en tesoreria
 * @param  [type] $db      Base de datos
 * @param  [type] $type    Tipo de documento
 * @param  [type] $transno Folio de la operación
 * @return [type]          Si realizo operación
 */
function fnGenerarDocumentoPago($db, $type, $transno, $statusid = 0)
{
    // Genera el documento de pago, obtener registros de encabezado
    $SQL = "SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    tb_pagos.nu_type,
    tb_pagos.nu_transno,
    tb_pagos.sn_tagref,
    tb_pagos.ln_ue,
    tb_pagos.supplierid,
    tb_pagos.nu_id_devengado,
    tb_pagos.id_clabe,
    tb_pagos.sn_factura,
    tb_pagos.dtm_fecha_factura,
    tb_pagos.txt_justificacion,
    suppliers.currcode,
    currencies.rate,
    paymentterms.daysbeforedue,
    systypescat.typename
    -- , tb_pagos.*
    FROM tb_pagos
    JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_pagos.nu_type AND chartdetailsbudgetlog.transno = tb_pagos.nu_transno AND chartdetailsbudgetlog.nu_tipo_movimiento = 260
    JOIN suppliers ON suppliers.supplierid = tb_pagos.supplierid
    JOIN currencies ON currencies.currabrev = suppliers.currcode
    JOIN paymentterms ON paymentterms.termsindicator = suppliers.paymentterms
    JOIN systypescat ON systypescat.typeid = tb_pagos.nu_type
    WHERE
    tb_pagos.nu_type = '".$type."'
    AND tb_pagos.nu_transno = '".$transno."'";
    $ErrMsg = "No se obtuvo la información para el encabezado del documento de pago";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrowEncabezado = DB_fetch_array($TransResult);

    $cadenaDevengados = "";
    if ($statusid == 100) {
        // Si es generar documento con validaciones del devengado
        $cadenaDevengados = fnObtenerIdDevengado($db, $type, $transno);
        // echo "\n cadenaDevengados: ".$cadenaDevengados;

        $sqlWhere = " AND chartdetailsbudgetlog.nu_idret = 0 ";
        if ($type == '298') {
            // Si es de impuestos
            $sqlWhere = " AND chartdetailsbudgetlog.nu_idret != 0 ";
        }
        $SQL = "SELECT
        SUM(chartdetailsbudgetlog.qty) as qty,
        chartdetailsbudgetlog.cvefrom,
        chartdetailsbudgetlog.nu_id_devengado,
        chartdetailsbudgetlog.nu_idret
        FROM chartdetailsbudgetlog
        WHERE
        chartdetailsbudgetlog.nu_id_devengado IN (".$cadenaDevengados.")
        AND chartdetailsbudgetlog.nu_tipo_movimiento = 260 ".$sqlWhere."
        HAVING qty < 0";
        // GROUP BY chartdetailsbudgetlog.cvefrom, chartdetailsbudgetlog.nu_id_devengado, chartdetailsbudgetlog.nu_idret
        $ErrMsg = "No se obtuvieron los registros del detalle No. de Captura ".$transno." para generar documento de pago";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $myrow = DB_fetch_array($TransResult);
        $myrowEncabezado['qty'] = $myrow['qty'];
    }

    // Verificar si tiene retenciones
    $totalRetenciones = 0;
    if ($statusid != 100) {
        // Si no es generar documento
        $SQL = "SELECT
        SUM(nu_qty) as nu_qty
        FROM tb_pagos_retenciones
        WHERE
        tb_pagos_retenciones.nu_type = '".$type."'
        AND tb_pagos_retenciones.nu_transno = '".$transno."'";
        $ErrMsg = "No se obtuvo la información del total de la retenciones";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            // Si tiene información obtener total
            $totalRetenciones = $myrow['nu_qty'];
        }
    }

    // Almacenar información de encabezado
    $suppreference = ''; // $myrowEncabezado['nu_id_devengado']."|".$transno;
    if (!empty($myrowEncabezado['sn_factura'])) {
        // Si tiene folio de factura
        $suppreference = $myrowEncabezado['sn_factura'];
    }
    $SuppReferenceFiscal = '';
    $date_venc = date('Y-m-d', strtotime('+'. $myrowEncabezado['daysbeforedue'].' day'));
    $ovamount = abs($myrowEncabezado['qty']) - abs($totalRetenciones);
    $TaxTotal = 0;
    $Comments = $myrowEncabezado['typename'].' del Devengado '.$myrowEncabezado['nu_id_devengado'].' con Folio '.$transno;

    $Comments = $myrowEncabezado['txt_justificacion'];

    $SQL = "INSERT INTO supptrans (transno,
    tagref,
    type, 
    supplierno, 
    suppreference,
    reffiscal,
    origtrandate,
    trandate, 
    duedate, 
    ovamount, 
    ovgst, 
    rate, 
    transtext,
    currcode,
    alt_tagref,
    ln_ue,
    id_clabe,
    nu_anio_fiscal
    )
    VALUES (".$transno.",
    '".$myrowEncabezado['sn_tagref']."',
    ".$type.",
    '".$myrowEncabezado['supplierid']."',
    '".$suppreference."',
    '".$SuppReferenceFiscal."',
    NOW(),
    NOW(),
    '".$date_venc."',
    ".$ovamount.",
    ".$TaxTotal.",
    '".$myrowEncabezado['rate']."',
    '".$Comments."',
    '".$myrowEncabezado['currcode']."',
    '0',
    '".$myrowEncabezado['ln_ue']."',
    '".$myrowEncabezado['id_clabe']."',
    '".$_SESSION['ejercicioFiscal']."'
    )";
    $ErrMsg = "No se agrego información al encabezado del pago";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $SuppTransID = DB_Last_Insert_ID($db, 'supptrans', 'id');

    $sqlWhere = "";
    if ($statusid != 100) {
        // Si no es generar documento
        $SQL = "SELECT
        tb_pagos_retenciones.nu_qty
        FROM tb_pagos_retenciones
        WHERE
        tb_pagos_retenciones.nu_type = '".$type."'
        AND tb_pagos_retenciones.nu_transno = '".$transno."'";
        $ErrMsg = "No se obtuvo la información de la retención para el detalle del documento de pago";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) > 0) {
            // Si tiene retenciones no agregarlas al documento de pago
            $sqlWhere = " AND chartdetailsbudgetlog.nu_idret = 0 ";
        }
    }

    // Guardar detalle del documento de pago
    $SQL = "SELECT
    chartdetailsbudgetlog.idmov,
    chartdetailsbudgetlog.qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period,
    chartdetailsbudgetlog.ln_ue,
    chartdetailsbudgetlog.nu_id_compromiso,
    chartdetailsbudgetlog.nu_id_devengado,
    chartdetailsbudgetlog.nu_idret,
    chartdetailsbudgetbytag.tagref
    FROM chartdetailsbudgetlog
    JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
    WHERE
    chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."' ".$sqlWhere."
    AND chartdetailsbudgetlog.nu_tipo_movimiento = 260
    ORDER BY chartdetailsbudgetlog.idmov ASC";
    $ErrMsg = "No se obtuvo la información para el detalle del documento de pago";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $num = 1;

    if ($statusid == 100) {
        // Si es generar documento con validaciones del devengado
        $sqlWhere = " AND chartdetailsbudgetlog.nu_idret = 0 ";
        if ($type == '298') {
            // Si es de impuestos
            $sqlWhere = " AND chartdetailsbudgetlog.nu_idret != 0 ";
        }
        $SQL = "SELECT
        SUM(chartdetailsbudgetlog.qty) as qty,
        chartdetailsbudgetlog.cvefrom,
        chartdetailsbudgetlog.nu_id_devengado,
        chartdetailsbudgetlog.period,
        chartdetailsbudgetlog.ln_ue,
        chartdetailsbudgetlog.nu_id_compromiso,
        chartdetailsbudgetlog.nu_id_devengado,
        chartdetailsbudgetlog.nu_idret,
        chartdetailsbudgetbytag.tagref
        FROM chartdetailsbudgetlog
        JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
        WHERE
        chartdetailsbudgetlog.nu_id_devengado IN (".$cadenaDevengados.")
        AND chartdetailsbudgetlog.nu_tipo_movimiento = 260 ".$sqlWhere."
        GROUP BY chartdetailsbudgetlog.cvefrom, chartdetailsbudgetlog.nu_id_devengado, chartdetailsbudgetlog.period, chartdetailsbudgetlog.nu_idret
        HAVING qty < 0";
        $ErrMsg = "No se obtuvieron los registros del detalle No. de Captura ".$transno." para generar documento de pago";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }
    while ($myrow = DB_fetch_array($TransResult)) {
        // Almacenar detalle del documento de pago
        $totalRetencion = 0;

        $pricesupp = 1;
        $cantidad = abs($myrow['qty']) - abs($totalRetencion);
        $ln_clave_iden = fnObtenerIdentificadorClavePrespuesto($db, $myrow['cvefrom']);

        $SQL="INSERT INTO supptransdetails(supptransid,
        stockid,
        description,
        price,
        qty,
        orderno,
        grns,
        tagref_det, 
        clavepresupuestal,
        ln_clave_iden,
        requisitionno,
        comments,
        period,
        nu_id_compromiso,
        nu_id_devengado,
        nu_idret)
        VALUES(".$SuppTransID.",
        'Clave ".$num."',
        '".$myrow['cvefrom']."',
        '".$pricesupp."',
        '".$cantidad."',
        '0',
        '0',
        '".$myrow['tagref']."',
        '".$myrow['cvefrom']."',
        '".$ln_clave_iden."',
        '0',
        '".$Comments."',
        '".$myrow['period']."',
        '".$myrow['nu_id_compromiso']."',
        '".$myrow['nu_id_devengado']."',
        '".$myrow['nu_idret']."')";
        $ErrMsg = "No se guardo la información para el detalle del documento de pago";
        $TransResult2 = DB_query($SQL, $db, $ErrMsg);

        $num ++;
    }

    return true;
}

if ($option == 'actualizarEstatus') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $mensajeInfo = '';
    $info = array();

    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $SQL = "SELECT 
        tb_pagos.nu_estatus,
        tb_pagos.sn_tagref,
        tb_pagos.ln_ue,
        tb_pagos.txt_justificacion,
        tb_pagos.sn_folio_solicitud,
        tb_pagos.supplierid,
        tb_botones_status.statusname
        FROM tb_pagos 
        LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = '".$funcion."' AND tb_botones_status.statusid = tb_pagos.nu_estatus
        WHERE tb_pagos.nu_type = '".$datosClave ['type']."' AND tb_pagos.nu_transno = '".$datosClave ['transno']."'";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $tagref = "";
        $ln_ue = "";
        $estatusActual = "";
        $nombreEstatus = "";
        $justificacion = "";
        $sn_folio_solicitud = "";
        $supplierid = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            $tagref = $myrow['sn_tagref'];
            $ln_ue = $myrow['ln_ue'];
            $estatusActual = $myrow['nu_estatus'];
            $nombreEstatus = $myrow['statusname'];
            $justificacion = $myrow['txt_justificacion'];
            $sn_folio_solicitud = $myrow['sn_folio_solicitud'];
            $supplierid = $myrow['supplierid'];
        }

        $errorRechazo = 0;
        if ($statusid == '99') {
            // Si es rechazo validar si se tiene acceso a ese estatus
            $errorRechazo = 1;
            $SQL = "
            SELECT 
            tb_botones_status.statusid, 
            tb_botones_status.statusname, 
            tb_botones_status.functionid
            FROM tb_botones_status 
            WHERE 
            tb_botones_status.sn_funcion_id in (".$funcion.") 
            AND tb_botones_status.statusid < 90 
            AND tb_botones_status.statusid > '".$estatusActual."' 
            ORDER BY tb_botones_status.sn_funcion_id DESC, tb_botones_status.statusid ASC 
            ";
            $ErrMsg = "Permisos para Rechazo";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                // Validar si se tiene permiso para rechazar
                if (Havepermission($_SESSION['UserID'], $myrow['functionid'], $db) == 1) {
                    // Tiene permiso a un estatus superior
                    $errorRechazo = 0;
                }
            }
        }

        if (($estatusActual == '0' || $estatusActual == '4') && $statusid != 100) {
            // Si esta cancelada o autorizada
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Devengado '.$datosClave ['iddevengado'].' con Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        } else if ($errorRechazo == '1') {
            // Si esta rechazando y no tiene permiso a ese estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Devengado '.$datosClave ['iddevengado'].' con Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        } else if ($estatusActual == $statusid) {
            // Si se avanza al mismo estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> El Devengado '.$datosClave ['iddevengado'].' con Folio '.$datosClave['transno'].' ya se encuentra en '.$nombreEstatus.'</p>';
        } else {
            $msjConfigurado = "";
            if ($statusid != 100) {
                // Si no es generar documento
                $statusActualizacion = $statusid;
                if ($statusid == '99') {
                    // Obtener Estatus Anterior
                    $SQL = "SELECT distinct chartdetailsbudgetlog.estatus, tb_botones_status.sn_estatus_anterior
                    FROM chartdetailsbudgetlog 
                    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
                    WHERE chartdetailsbudgetlog.type = '".$datosClave ['type']."' 
                    AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."'";
                    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    while ($myrow = DB_fetch_array($TransResult)) {
                        $statusActualizacion = $myrow['sn_estatus_anterior'];
                    }

                    // Es rechazar y se regresa un estatus
                    $SQL = "UPDATE chartdetailsbudgetlog 
                            LEFT JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
                            SET chartdetailsbudgetlog.estatus = tb_botones_status.sn_estatus_anterior 
                            WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                } else {
                    $SQL = "UPDATE chartdetailsbudgetlog SET estatus = '".$statusid."' WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                }

                if ($statusid == '0' || $statusid == '4') {
                    //Borrar Registros en 0
                    $SQL = "DELETE FROM chartdetailsbudgetlog WHERE (qty = '0' OR qty = '-0') AND type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."'";
                    $ErrMsg = "No se eliminaron Registros en 0";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                }

                if ($statusid == '4' && $datosClave ['type'] != '298') {
                    // Si es autorización, y no es reteciones realizar movimientos contables y presupuestales
                    // Ya que los movimientos en retenciones ya se hicieron anteriormente
                    $SQL = "UPDATE chartdetailsbudgetlog SET sn_disponible = '1' WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $SQL = "SELECT
                    chartdetailsbudgetlog.estatus,
                    chartdetailsbudgetlog.qty,
                    chartdetailsbudgetlog.cvefrom,
                    chartdetailsbudgetlog.tagref,
                    chartdetailsbudgetlog.type,
                    chartdetailsbudgetlog.txt_justificacion,
                    chartdetailsbudgetlog.ln_ue,
                    chartdetailsbudgetlog.description,
                    chartdetailsbudgetlog.period,
                    chartdetailsbudgetlog.nu_id_compromiso,
                    chartdetailsbudgetlog.nu_id_devengado,
                    chartdetailsbudgetlog.nu_idret,
                    tb_cat_partidaspresupuestales_partidagenerica.pargcalculado as categoryid
                    FROM chartdetailsbudgetlog 
                    LEFT JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
                    LEFT JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
                    LEFT JOIN tb_cat_partidaspresupuestales_partidagenerica ON tb_cat_partidaspresupuestales_partidagenerica.ccap = tb_cat_partidaspresupuestales_partidaespecifica.ccap AND tb_cat_partidaspresupuestales_partidagenerica.ccon = tb_cat_partidaspresupuestales_partidaespecifica.ccon AND tb_cat_partidaspresupuestales_partidagenerica.cparg = tb_cat_partidaspresupuestales_partidaespecifica.cparg
                    WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    
                    // Array para folios agrupados (UR-UE)
                    $infoFolios = array();
                    $infoFoliosCompromiso = array();
                    $period = GetPeriod(date('d/m/Y'), $db);
                    while ($myrow = DB_fetch_array($TransResult)) {
                        $claveCreada = $myrow['cvefrom'];
                        $tagref = $myrow['tagref'];
                        $totalPoliza = abs($myrow['qty']);
                        $fechapoliza = date('Y-m-d');
                        $referencia = "Clave: ".$claveCreada;

                        $datoMultiplicar1 = -1;
                        $datoMultiplicar2 = 1;
                        $realizarPolizaCompromiso = 0;

                        $infoClaves = array();
                        $infoClaves[] = array(
                            'accountcode' => $myrow ['cvefrom']
                        );
                        $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                        $period = $respuesta['periodo'];
                        $fechapoliza = $respuesta['fecha'];

                        if ($datosClave ['type'] == '299') {
                            // Si es decremento
                            $datoMultiplicar1 = 1;
                            $datoMultiplicar2 = -1;

                            $SQL = "SELECT
                            tb_pagos.nu_type
                            FROM tb_pagos
                            WHERE
                            tb_pagos.nu_type != '299'
                            AND tb_pagos.nu_id_devengado = '".$myrow ['nu_id_devengado']."'";
                            $TransResultDevengado = DB_query($SQL, $db, $ErrMsg);
                            $myrowDevengado = DB_fetch_array($TransResultDevengado);
                            if ($myrowDevengado['nu_type'] == '294') {
                                // Si es pago directo
                                $realizarPolizaCompromiso = 1;
                            }
                        }

                        $datoUE = $myrow['ln_ue']; // fnObtenerUnidadEjecutoraClave($db, $claveCreada);

                        if ($datosClave ['type'] == '294' || $realizarPolizaCompromiso == 1) {
                            // Si es pago directo, realizar movientos de comprometido
                            // Ver si existe folio para movimientos
                            $folioPolizaUe = 0;
                            foreach ($infoFoliosCompromiso as $datosFolios) {
                                // Recorrer para ver si exi
                                if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                    // Si existe
                                    $type = $datosFolios['type'];
                                    $transno = $datosFolios['transno'];
                                    $folioPolizaUe = $datosFolios['folioPolizaUe'];
                                }
                            }
                            if ($folioPolizaUe == 0) {
                                // Si no existe folio sacar folio
                                // $transno = GetNextTransNo($type, $db);
                                // Folio de la poliza por unidad ejecutora
                                $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                                $infoFoliosCompromiso[] = array(
                                    'tagref' => $tagref,
                                    'ue' => $datoUE,
                                    'type' => $datosClave ['type'],
                                    'transno' => $datosClave ['transno'],
                                    'folioPolizaUe' => $folioPolizaUe
                                );
                            }

                            // Registro para el comprometido en negativo
                            $agregoLog = fnInsertPresupuestoLog($db, $datosClave ['type'], $datosClave ['transno'], $tagref, $claveCreada, $myrow['period'], abs($myrow['qty']) * $datoMultiplicar1, 259, "", $myrow['description'], 1, '', 0, $myrow['ln_ue'], $myrow ['nu_id_compromiso'], $myrow ['nu_id_devengado'], $myrow['nu_idret']); // Abono

                            if ($myrow['qty'] < 0) {
                                // Reducción
                                $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'POREJERCER', 'COMPROMETIDO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $justificacion, $datoUE, 1, 0, $folioPolizaUe);
                            } else {
                                // Ampliación
                                $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'COMPROMETIDO', 'POREJERCER', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $justificacion, $datoUE, 1, 0, $folioPolizaUe);
                            }
                        }

                        // Ver si existe folio para movimientos
                        $folioPolizaUe = 0;
                        foreach ($infoFolios as $datosFolios) {
                            // Recorrer para ver si exi
                            if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                // Si existe
                                $type = $datosFolios['type'];
                                $transno = $datosFolios['transno'];
                                $folioPolizaUe = $datosFolios['folioPolizaUe'];
                            }
                        }
                        if ($folioPolizaUe == 0) {
                            // Si no existe folio sacar folio
                            // $transno = GetNextTransNo($type, $db);
                            // Folio de la poliza por unidad ejecutora
                            $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                            $infoFolios[] = array(
                                'tagref' => $tagref,
                                'ue' => $datoUE,
                                'type' => $datosClave ['type'],
                                'transno' => $datosClave ['transno'],
                                'folioPolizaUe' => $folioPolizaUe
                            );
                        }

                        // Registro para el comprometido dejarlo en 0
                        $agregoLog = fnInsertPresupuestoLog($db, $datosClave ['type'], $datosClave ['transno'], $tagref, $claveCreada, $myrow['period'], abs($myrow['qty']) * $datoMultiplicar2, 259, "", $myrow['description'], 1, '', 0, $myrow['ln_ue'], $myrow ['nu_id_compromiso'], $myrow ['nu_id_devengado'], $myrow['nu_idret']); // Abono

                        if ($myrow['qty'] < 0) {
                            // Reducción
                            $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'COMPROMETIDO', 'DEVENGADO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $justificacion, $datoUE, 1, 0, $folioPolizaUe);
                        } else {
                            // Ampliación
                            $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'DEVENGADO', 'COMPROMETIDO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $justificacion, $datoUE, 1, 0, $folioPolizaUe);
                        }

                        // Movimientos matriz devengado, Inicio
                        $GLCode = "";
                        $cuentaAbonoProveedor = "";
                        $ln_clave_iden = fnObtenerIdentificadorClavePrespuesto($db, $claveCreada);
                        $sqlWhere = "";
                        if (trim($ln_clave_iden) != '') {
                            // Solo cuando se tiene identidicador
                            $sqlWhere = " AND ln_clave = '".$ln_clave_iden."' ";
                        }
                        $SQL = "SELECT stockact, accountegreso, adjglact FROM stockcategory 
                        WHERE 
                        categoryid = '".$myrow['categoryid']."' 
                        AND accountegreso IN (SELECT accountcode FROM accountxsupplier WHERE supplierid = '".$supplierid."') ".$sqlWhere;
                        $ErrMsg = "No se obtuvieron los registros de la matriz del devengado";
                        $resultCuenta = DB_query($SQL, $db, $ErrMsg);
                        while ($myrowCuenta=db_fetch_array($resultCuenta)) {
                            $GLCode = $myrowCuenta['stockact'];
                            if ($datosClave ['type'] == '297') {
                                // Si es de viaticos
                                $GLCode = $myrowCuenta['adjglact'];
                            }
                            $cuentaAbonoProveedor = $myrowCuenta['accountegreso'];
                        }
                        $SQL = "INSERT INTO gltrans (type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        amount,
                        tag,
                        dateadded,
                        userid,
                        posted,
                        ln_ue,
                        nu_folio_ue,
                        nu_devengado)
                        VALUES ('".$datosClave ['type']."',
                        '" . $datosClave ['transno'] . "',
                        '" . $fechapoliza . "',
                        '" . $period . "',
                        '" . $GLCode . "',
                        '" . $justificacion . "',
                        '" . $totalPoliza . "',
                        '" . $tagref . "',"
                        . "NOW(),"
                        . "'".$_SESSION['UserID']."',
                        '1',
                        '".$datoUE."',
                        '".$folioPolizaUe."',
                        '1'
                        )";
                        $ErrMsg = "No se agrego el primer registro de la póliza del devengado";
                        $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                        $SQL = "INSERT INTO gltrans (type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        amount,
                        tag,
                        dateadded,
                        userid,
                        posted,
                        ln_ue,
                        nu_folio_ue,
                        nu_devengado)
                        VALUES ('".$datosClave ['type']."',
                        '" . $datosClave ['transno'] . "',
                        '" . $fechapoliza . "',
                        '" . $period . "',
                        '" . $cuentaAbonoProveedor . "', 
                        '" . $justificacion . "',
                        '" . $totalPoliza * -1 . "',
                        '" . $tagref . "',"
                        . "NOW(),"
                        . "'".$_SESSION['UserID']."',
                        '1',
                        '".$datoUE."',
                        '".$folioPolizaUe."',
                        '1'
                        )";
                        $ErrMsg = "No se agrego el segundo registro de la póliza del devengado";
                        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                        // Movimientos matriz devengado, Fin
                    }

                    if ($datosClave ['type'] == '297') {
                        // Si es pago de viaticos
                        $SQL = "UPDATE tb_viaticos SET ind_momento_presupuestal = 5
                        WHERE sn_folio_solicitud = '".$sn_folio_solicitud."'";
                        $ErrMsg = "No se actualizó el estatus presupuestal de viaticos";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);
                    }
                }

                $estatus = $statusid;
                if ($statusid == '99') {
                    // Si es rechazar
                    $estatus = $statusActualizacion;
                }

                // Actualizar estatus tabla principal
                $SQL = "UPDATE tb_pagos SET nu_estatus = '".$estatus."', nu_id_compromiso = '".$datosClave ['idcompromiso']."', nu_id_devengado = '".$datosClave ['iddevengado']."' 
                WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                // Actualizar compromiso y devengado
                if ($datosClave ['type'] != '298' && $datosClave ['type'] != '299') {
                    // Si no es pago de impuestos
                    $SQL = "UPDATE chartdetailsbudgetlog SET nu_id_compromiso = '".$datosClave ['idcompromiso']."', nu_id_devengado = '".$datosClave ['iddevengado']."' 
                    WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                }

                // Actualizar estatus de las retenciones
                $SQL = "UPDATE tb_pagos_retenciones SET nu_estatus = '".$estatus."', nu_id_compromiso = '".$datosClave ['idcompromiso']."', nu_id_devengado = '".$datosClave ['iddevengado']."' 
                WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                if ($estatus == '4' && $datosClave ['type'] != '299') {
                    // Si es autorizar generar documento de pago
                    $respuesta = fnGenerarDocumentoPago($db, $datosClave ['type'], $datosClave ['transno']);
                    if (!$respuesta) {
                        // No guardo el pago correctamente
                        $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al generar el Devengado en Tesoreria. Devengado '.$datosClave ['iddevengado'].' con Folio '.$datosClave['transno'].'</p>';
                    }
                }

                // Mensaje Configurado
                $SQL = "SELECT 
                tb_botones_status.sn_mensaje_opcional, tb_botones_status.sn_mensaje_opcional2
                FROM chartdetailsbudgetlog
                LEFT JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
                WHERE chartdetailsbudgetlog.type = '".$datosClave ['type']."' AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."'
                LIMIT 1";
                $ErrMsg = "No se obtuvo información para mostrar mensaje del proceso ";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                while ($myrow = DB_fetch_array($TransResult)) {
                    if ($statusid == '99') {
                        $msjConfigurado = $myrow['sn_mensaje_opcional2'];
                    } else {
                        $msjConfigurado = $myrow['sn_mensaje_opcional'];
                    }
                }
            } else {
                // Si es generar documento
                $respuesta = fnGenerarDocumentoPago($db, $datosClave ['type'], $datosClave ['transno'], $statusid);

                $SQL = "SELECT
                tb_botones_status.sn_mensaje_opcional
                FROM tb_botones_status
                WHERE tb_botones_status.sn_funcion_id = '".$funcion."'
                AND tb_botones_status.statusid = '".$statusid."'";
                $ErrMsg = "No se obtuvo información para mostrar mensaje del proceso ";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $msjConfigurado = $myrow['sn_mensaje_opcional'];
                }
            }

            if (empty($msjConfigurado)) {
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para el Devengado '.$datosClave ['iddevengado'].'con Folio '.$datosClave['transno'].' correcta</p>';
            } else {
                $msjConfigurado = str_replace("XXX", $datosClave ['transno'], $msjConfigurado);
                $msjConfigurado = str_replace("ZZZ", $datosClave ['iddevengado'], $msjConfigurado);
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> '.$msjConfigurado.'</p>';
            }
        }

        $info[] = array(
            'transno' => $datosClave ['transno']
        );
    }

    $reponse['estatus'] = $statusid;
    $reponse['mensaje'] = $mensajeInfo;
    $reponse['datos'] = $info;

    $contenido = $reponse;
    $result = true;
}

if ($option == 'obtenerBotones') {
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
            AND (tb_botones_status.sn_panel_adecuacion_presupuestal = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."' AND sec_funxuser.permiso = 1)
            ) 
            ORDER BY tb_botones_status.statusid ASC
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

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
