<?php
/**
 * Panel Oficios de Rectificación (Compromisos, Directos, Viáticos, Subsidios, etc.)
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/09/2018
 * Fecha Modificación: 12/09/2018
 * Modelo para el proceso del panel de Oficios de Rectificación (Compromisos, Directos, Viáticos, Subsidios, etc.)
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
$funcion=2461;
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

    $selectEstatusCompromiso = $_POST['selectEstatusCompromiso'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }
    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND tb_rectificaciones.sn_tagref IN (".$tagref.") ";
    }
    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_rectificaciones.dtm_fecha between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND tb_rectificaciones.dtm_fecha >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_rectificaciones.dtm_fecha <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        $sqlWhere .= " AND (tb_rectificaciones.ln_ue IN (".$ue.") OR chartdetailsbudgetlog.ln_ue IN (".$ue.")) ";
    }

    if ($tipoSuficiencia != '') {
        $sqlWhere .= " AND tb_rectificaciones.nu_tipo IN (".$tipoSuficiencia.") ";
    }

    if ($folio != '') {
        // $sqlWhere .= " AND tb_rectificaciones.nu_transno like '%".$folio."%' ";
        $sqlWhere .= " AND tb_rectificaciones.nu_transno = '".$folio."' ";
    }

    if ($selectEstatusCompromiso != '') {
        $sqlWhere .= " AND tb_rectificaciones.nu_estatus IN (".$selectEstatusCompromiso.") ";
    }
    
    $info = array();
    $SQL = "SELECT
    DISTINCT
    tb_rectificaciones.nu_type,
    tb_rectificaciones.nu_transno,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.type = tb_rectificaciones.nu_type 
    AND log.qty > 0
    AND log.transno = tb_rectificaciones.nu_transno 
    AND log.nu_tipo_movimiento = 265
    ) as ampliacion,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.type = tb_rectificaciones.nu_type 
    AND log.qty < 0
    AND log.transno = tb_rectificaciones.nu_transno 
    AND log.nu_tipo_movimiento = 265
    ) as reduccion,
    tb_rectificaciones.nu_estatus,
    tb_rectificaciones.sn_userid,
    www_users.realname,
    DATE_FORMAT(tb_rectificaciones.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_rectificaciones.sn_tagref,
    tags.tagname,
    tb_rectificaciones.txt_justificacion as sn_description,
    CONCAT(tb_rectificaciones.nu_type, ' - ', systypescat.typename) as nombreSuficiencia,
    CONCAT(tags.tagref, ' - ', tags.tagname) as tagname,
    tb_rectificaciones.nu_estatus as statusid,
    tb_botones_status.statusname,
    tb_rectificaciones.sn_tagref,
    tb_rectificaciones.ln_ue
    FROM tb_rectificaciones
    LEFT JOIN systypescat ON systypescat.typeid = tb_rectificaciones.nu_type
    LEFT JOIN www_users ON www_users.userid = tb_rectificaciones.sn_userid
    JOIN tags ON tags.tagref = tb_rectificaciones.sn_tagref
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_rectificaciones.nu_estatus AND tb_botones_status.sn_funcion_id = tb_rectificaciones.sn_funcion_id
    LEFT JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_rectificaciones.nu_type AND chartdetailsbudgetlog.transno = tb_rectificaciones.nu_transno  
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_rectificaciones.sn_tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tb_rectificaciones.sn_tagref AND tb_sec_users_ue.ue = tb_rectificaciones.ln_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    WHERE 1 = 1 ".$sqlWhere." AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
    ORDER BY tb_rectificaciones.dtm_fecha DESC";
    $ErrMsg = "No se obtuvieron los registros";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $operacion = "";
        $seleccionar = "";

        $urlGeneral = "&transno=>" . $myrow['nu_transno'] . "&type=>" . $myrow['nu_type'];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $liga= "URL=" . $url;
        $operacion = '<a type="button" id="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" name="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" href="rectificacion.php?'.$liga.'" title="Detalle Rectificación" style="color: blue;">'.$myrow ['nu_transno'].'</a>'; // target="_blank"
        
        $impresion = '<a type="button" id="btnImprimir'.$myrow['nu_transno'].'" name="btnImprimir'.$myrow['nu_transno'].'" href="impresion_suficiencia.php?'.$liga.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

        $info[] = array(
            'id1' =>false,
            'nu_type' => $myrow ['nu_type'],
            'nu_transno' => $myrow ['nu_transno'],
            'operacion' => $operacion,
            'requisitionno' => 'requisitionno', // $myrow ['requisitionno'],
            'ampliacion' => ($myrow ['ampliacion'] != "" ? abs(number_format($myrow ['ampliacion'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'ampliacion2' => ($myrow ['ampliacion'] != "" ? abs(number_format($myrow ['ampliacion'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'reduccion' => ($myrow ['reduccion'] != "" ? abs(number_format($myrow ['reduccion'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'reduccion2' => ($myrow ['reduccion'] != "" ? abs(number_format($myrow ['reduccion'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
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
            'ue' => $myrow ['ln_ue']
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoSufuciencia', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_transno', type: 'string' },";
    $columnasNombres .= "{ name: 'operacion', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_description', type: 'string' },";
    $columnasNombres .= "{ name: 'statusname', type: 'string' },";
    $columnasNombres .= "{ name: 'tagname', type: 'string' },";
    $columnasNombres .= "{ name: 'ampliacion', type: 'double' },";
    $columnasNombres .= "{ name: 'ampliacion2', type: 'double' },";
    $columnasNombres .= "{ name: 'reduccion', type: 'double' },";
    $columnasNombres .= "{ name: 'reduccion2', type: 'double' },";
    $columnasNombres .= "{ name: 'imprimir', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_type', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_tagref', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_tipo', type: 'string' },";
    $columnasNombres .= "{ name: 'requisitionno', type: 'string' },";
    $columnasNombres .= "{ name: 'statusid', type: 'string' }";
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
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Operación', datafield: 'tipoSufuciencia', width: '12%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'nu_transno', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'operacion', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Justificación', datafield: 'sn_description', width: '22%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'statusname', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'tagname', width: '12%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Dice', datafield: 'ampliacion', width: '11%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Dice', datafield: 'ampliacion2', width: '11%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true },";
    $columnasNombresGrid .= " { text: 'Debe Decir', datafield: 'reduccion', width: '11%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Debe Decir', datafield: 'reduccion2', width: '11%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'imprimir', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'nu_type', datafield: 'nu_type', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'sn_tagref', datafield: 'sn_tagref', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'nu_tipo', width: '15%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Req', datafield: 'requisitionno', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'statusid', datafield: 'statusid', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
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

    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;

        $SQL = "SELECT 
        cat_Months.mes as mesName,
        chartdetailsbudgetlog.* 
        FROM chartdetailsbudgetlog
        LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
        LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
        WHERE
        chartdetailsbudgetlog.type = '".$datosClave ['type']."'
        AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."'
        ORDER BY chartdetailsbudgetlog.period ASC";
        $ErrMsg = "No se obtuvieron los registros del No. de Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $infoClaves = array();
        while ($myrow = DB_fetch_array($TransResult)) {
            $nu_id_devengado = $myrow['nu_id_devengado'];
            $nu_id_compromiso = $myrow['nu_id_compromiso'];
            $nu_idret = $myrow['nu_idret'];

            $infoClaves[] = array(
                'accountcode' => $myrow ['cvefrom']
            );
            
            $disponible = fnInfoPresupuesto($db, $myrow['cvefrom'], '', '', '', 0, 0, '', $datosClave ['type'], $datosClave ['transno'], 'Reduccion', '', '', '', 1, $nu_id_devengado, $nu_id_compromiso, $nu_idret);

            $movimientoTipo = '';
            $textoMensaje = 'disponible';
            $panel = 'DEBE DECIR';

            if ($myrow['qty'] > 0) {
                // Es amplicacion
                $movimientoTipo = 'Pago';
                $textoMensaje = 'pagado';
                $panel = 'DICE';
            }

            if ($myrow['qty'] < 0 && !empty($nu_id_compromiso)) {
                $movimientoTipo = 'Compromiso';
            }

            foreach ($disponible as $dispo) {
                // echo "\n ******************";
                // echo "\n ".$myrow['mesName'].": ".$dispo[$myrow['mesName'].$movimientoTipo];
                // echo "\n sol: ".($myrow['qty']);
                // echo "\n movimientoTipo: ".$movimientoTipo;
                if ($dispo[$myrow['mesName'].$movimientoTipo] < abs($myrow['qty'])) {
                    $result = false;
                    $actualizar = 0;
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Rectificación con Folio '.$datosClave ['transno'].' en '.$panel.' la clave presupuestal '.$myrow['cvefrom'].' en '.$myrow['mesName'].' el '.$textoMensaje.' es $ '.number_format($dispo[$myrow['mesName'].$movimientoTipo], $_SESSION['DecimalPlaces'], '.', ',').' y se solicita un monto de $ '.number_format(abs($myrow['qty']), $_SESSION['DecimalPlaces'], '.', ',').' </p>';
                }
            }
        }

        // Validar periodos contables
        $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
        if (!$respuesta['result']) {
            $result = false;
            $actualizar = 0;
            $mensajeErrores .= $respuesta['mensaje'];
        }

        // Validar que los montos no sean diferentes (Ampliaciones y Reducciones)
        $SQL = "SELECT
        (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
        WHERE log.type = tb_rectificaciones.nu_type 
        AND log.qty > 0
        AND log.transno = tb_rectificaciones.nu_transno 
        AND log.nu_tipo_movimiento = 265
        ) as ampliacion,
        (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
        WHERE log.type = tb_rectificaciones.nu_type 
        AND log.qty < 0
        AND log.transno = tb_rectificaciones.nu_transno 
        AND log.nu_tipo_movimiento = 265
        ) as reduccion
        FROM tb_rectificaciones
        WHERE
        tb_rectificaciones.nu_type = '".$datosClave ['type']."'
        AND tb_rectificaciones.nu_transno = '".$datosClave ['transno']."' 
        AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
        $ErrMsg = "No se obtuvieron los registros del No. de Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            // echo "\n ampliacion: ".number_format($myrow['ampliacion'], $_SESSION['DecimalPlaces'], '.', '');
            // echo "\n reduccion: ".number_format($myrow['reduccion'], $_SESSION['DecimalPlaces'], '.', '');
            if (number_format(abs($myrow['ampliacion']), $_SESSION['DecimalPlaces'], '.', '') != number_format(abs($myrow['reduccion']), $_SESSION['DecimalPlaces'], '.', '')) {
                $result = false;
                $actualizar = 0;
                $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Rectificación con Folio '.$datosClave ['transno'].' el monto total de DICE y DEBE DECIR deben ser iguales </p>';
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
    AND tb_pagos.nu_transno = '".$transno."'
    AND tb_pagos.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
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
    nu_anio_fiscal)
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
    '".$_SESSION['ejercicioFiscal']."')";
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
        tb_rectificaciones.nu_estatus,
        tb_rectificaciones.sn_tagref,
        tb_rectificaciones.ln_ue,
        tb_rectificaciones.txt_justificacion,
        tb_rectificaciones.nu_tipo,
        tb_botones_status.statusname
        FROM tb_rectificaciones 
        LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_rectificaciones.sn_funcion_id AND tb_botones_status.statusid = tb_rectificaciones.nu_estatus
        WHERE tb_rectificaciones.nu_type = '".$datosClave ['type']."' AND tb_rectificaciones.nu_transno = '".$datosClave ['transno']."' AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $tagref = "";
        $ln_ue = "";
        $estatusActual = "";
        $nombreEstatus = "";
        $justificacion = "";
        $selectTipo = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            $tagref = $myrow['sn_tagref'];
            $ln_ue = $myrow['ln_ue'];
            $estatusActual = $myrow['nu_estatus'];
            $nombreEstatus = $myrow['statusname'];
            $justificacion = $myrow['txt_justificacion'];
            $selectTipo = $myrow['nu_tipo'];
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

        if ($estatusActual == '0' || $estatusActual == '4') {
            // Si esta cancelada o autorizada
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para la Rectificación con Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        } else if ($errorRechazo == '1') {
            // Si esta rechazando y no tiene permiso a ese estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para la Rectificación con Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        } else if ($estatusActual == $statusid) {
            // Si se avanza al mismo estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> La Rectificación con Folio '.$datosClave['transno'].' ya se encuentra en '.$nombreEstatus.'</p>';
        } else {
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

            if ($statusid == '4') {
                // Si es autorización realizar movimientos contables y presupuestales
                // Ya que los movimientos en retenciones ya se hicieron anteriormente
                $SQL = "UPDATE chartdetailsbudgetlog SET sn_disponible = '1' WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $SQL = "SELECT estatus, qty, cvefrom, tagref, type, txt_justificacion, ln_ue, description, period, nu_id_compromiso, nu_id_devengado, nu_idret, CASE WHEN qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento, idmov
                FROM chartdetailsbudgetlog 
                WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."'
                ORDER BY tipoMovimiento ASC, idmov ASC";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                
                // Array para folios agrupados (UR-UE)
                $infoFolios = array();
                $infoFolios2 = array();
                $infoFolios3 = array();
                $infoFolios4 = array();
                $infoFolios5 = array();
                $infoFolios6 = array();
                $infoFolios7 = array();
                $infoFolios8 = array();
                //$period = GetPeriod(date('d/m/Y'), $db);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $claveCreada = $myrow['cvefrom'];
                    $tagref = $myrow['tagref'];
                    $totalPoliza = abs($myrow['qty']);
                    $fechapoliza = date('Y-m-d');
                    $referencia = "Clave: ".$claveCreada;
                    $datoMultiplicar2 = 1;
                    $datoUE = $myrow['ln_ue'];

                    $infoClaves = array();
                    $infoClaves[] = array(
                        'accountcode' => $myrow ['cvefrom']
                    );
                    $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                    $period = $respuesta['periodo'];
                    $fechapoliza = $respuesta['fecha'];

                    // Registro para el comprometido dejarlo en 0
                    // $agregoLog = fnInsertPresupuestoLog($db, $datosClave ['type'], $datosClave ['transno'], $tagref, $claveCreada, $myrow['period'], abs($myrow['qty']) * $datoMultiplicar2, 259, "", $myrow['description'], 1, '', 0, $myrow['ln_ue'], $myrow ['nu_id_compromiso'], $myrow ['nu_id_devengado'], $myrow['nu_idret']); // Abono

                    if ($myrow['qty'] < 0) {
                        // Reducción
                        $descriptionDice = 'DEBE DECIR: '.$claveCreada;
                        if (!empty($myrow ['nu_id_compromiso'])) {
                            // Si tiene compromiso, se agrega al disponible
                            $agregoLog = fnInsertPresupuestoLog($db, $datosClave ['type'], $datosClave ['transno'], $tagref, $claveCreada, $myrow['period'], ($myrow['qty'] * - 1), 259, "", $myrow['description'], 1, '', 0, $myrow['ln_ue'], $myrow ['nu_id_compromiso'], $myrow ['nu_id_devengado'], $myrow['nu_idret']);
                        } else {
	                        // Ver si existe folio para movimientos
	                        $folioPolizaUe5 = 0;
	                        foreach ($infoFolios5 as $datosFolios) {
	                            // Recorrer para ver si exi
	                            if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
	                                // Si existe
	                                $type = $datosFolios['type'];
	                                $transno = $datosFolios['transno'];
	                                $folioPolizaUe5 = $datosFolios['folioPolizaUe'];
	                            }
	                        }
	                        if ($folioPolizaUe5 == 0) {
	                            // Si no existe folio sacar folio
	                            $folioPolizaUe5 = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
	                            $infoFolios5[] = array(
	                                'tagref' => $tagref,
	                                'ue' => $datoUE,
	                                'type' => $datosClave ['type'],
	                                'transno' => $datosClave ['transno'],
	                                'folioPolizaUe' => $folioPolizaUe5
	                            );
	                        }
	                        // Movimiento del compromiso
	                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'POREJERCER', 'COMPROMETIDO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe5);
	                    }

                        // Ver si existe folio para movimientos
                        $folioPolizaUe6 = 0;
                        foreach ($infoFolios6 as $datosFolios) {
                            // Recorrer para ver si exi
                            if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                // Si existe
                                $type = $datosFolios['type'];
                                $transno = $datosFolios['transno'];
                                $folioPolizaUe6 = $datosFolios['folioPolizaUe'];
                            }
                        }
                        if ($folioPolizaUe6 == 0) {
                            // Si no existe folio sacar folio
                            $folioPolizaUe6 = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                            $infoFolios6[] = array(
                                'tagref' => $tagref,
                                'ue' => $datoUE,
                                'type' => $datosClave ['type'],
                                'transno' => $datosClave ['transno'],
                                'folioPolizaUe' => $folioPolizaUe6
                            );
                        }
                        // Movimiento del devengado
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'COMPROMETIDO', 'DEVENGADO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe6);

                        // Ver si existe folio para movimientos
                        $folioPolizaUe7 = 0;
                        foreach ($infoFolios7 as $datosFolios) {
                            // Recorrer para ver si exi
                            if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                // Si existe
                                $type = $datosFolios['type'];
                                $transno = $datosFolios['transno'];
                                $folioPolizaUe7 = $datosFolios['folioPolizaUe'];
                            }
                        }
                        if ($folioPolizaUe7 == 0) {
                            // Si no existe folio sacar folio
                            $folioPolizaUe7 = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                            $infoFolios7[] = array(
                                'tagref' => $tagref,
                                'ue' => $datoUE,
                                'type' => $datosClave ['type'],
                                'transno' => $datosClave ['transno'],
                                'folioPolizaUe' => $folioPolizaUe7
                            );
                        }
                        // Movimiento del ejercido
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'DEVENGADO', 'EJERCIDO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe7);

                        // Ver si existe folio para movimientos
                        $folioPolizaUe8 = 0;
                        foreach ($infoFolios8 as $datosFolios) {
                            // Recorrer para ver si exi
                            if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                // Si existe
                                $type = $datosFolios['type'];
                                $transno = $datosFolios['transno'];
                                $folioPolizaUe8 = $datosFolios['folioPolizaUe'];
                            }
                        }
                        if ($folioPolizaUe8 == 0) {
                            // Si no existe folio sacar folio
                            $folioPolizaUe8 = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                            $infoFolios8[] = array(
                                'tagref' => $tagref,
                                'ue' => $datoUE,
                                'type' => $datosClave ['type'],
                                'transno' => $datosClave ['transno'],
                                'folioPolizaUe' => $folioPolizaUe8
                            );
                        }
                        // Movimiento del ejercido
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'EJERCIDO', 'PAGADO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe8);
                    } else {
                        // Ampliación
                    	$descriptionDice = 'DICE: '.$claveCreada;
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
                            $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                            $infoFolios[] = array(
                                'tagref' => $tagref,
                                'ue' => $datoUE,
                                'type' => $datosClave ['type'],
                                'transno' => $datosClave ['transno'],
                                'folioPolizaUe' => $folioPolizaUe
                            );
                        }
                        // Reversa del pagado
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'PAGADO', 'EJERCIDO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe);

                        // Ver si existe folio para movimientos
                        $folioPolizaUe2 = 0;
                        foreach ($infoFolios2 as $datosFolios) {
                            // Recorrer para ver si exi
                            if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                // Si existe
                                $type = $datosFolios['type'];
                                $transno = $datosFolios['transno'];
                                $folioPolizaUe2 = $datosFolios['folioPolizaUe'];
                            }
                        }
                        if ($folioPolizaUe2 == 0) {
                            // Si no existe folio sacar folio
                            $folioPolizaUe2 = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                            $infoFolios2[] = array(
                                'tagref' => $tagref,
                                'ue' => $datoUE,
                                'type' => $datosClave ['type'],
                                'transno' => $datosClave ['transno'],
                                'folioPolizaUe' => $folioPolizaUe2
                            );
                        }
                        // Reversa del ejercido
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'EJERCIDO', 'DEVENGADO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe2);

                        // Ver si existe folio para movimientos
                        $folioPolizaUe3 = 0;
                        foreach ($infoFolios3 as $datosFolios) {
                            // Recorrer para ver si exi
                            if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                // Si existe
                                $type = $datosFolios['type'];
                                $transno = $datosFolios['transno'];
                                $folioPolizaUe3 = $datosFolios['folioPolizaUe'];
                            }
                        }
                        if ($folioPolizaUe3 == 0) {
                            // Si no existe folio sacar folio
                            $folioPolizaUe3 = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                            $infoFolios3[] = array(
                                'tagref' => $tagref,
                                'ue' => $datoUE,
                                'type' => $datosClave ['type'],
                                'transno' => $datosClave ['transno'],
                                'folioPolizaUe' => $folioPolizaUe3
                            );
                        }
                        // Reversa del ejercido
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'DEVENGADO', 'COMPROMETIDO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe3);

                        if (!empty($myrow ['nu_id_compromiso'])) {
                            // Si tiene compromiso, se agrega al disponible
                            $agregoLog = fnInsertPresupuestoLog($db, $datosClave ['type'], $datosClave ['transno'], $tagref, $claveCreada, $myrow['period'], ($myrow['qty'] * - 1), 259, "", $myrow['description'], 1, '', 0, $myrow['ln_ue'], $myrow ['nu_id_compromiso'], $myrow ['nu_id_devengado'], $myrow['nu_idret']);
                        } else {
                            // Ver si existe folio para movimientos
                            $folioPolizaUe4 = 0;
                            foreach ($infoFolios4 as $datosFolios) {
                                // Recorrer para ver si exi
                                if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                                    // Si existe
                                    $type = $datosFolios['type'];
                                    $transno = $datosFolios['transno'];
                                    $folioPolizaUe4 = $datosFolios['folioPolizaUe'];
                                }
                            }
                            if ($folioPolizaUe4 == 0) {
                                // Si no existe folio sacar folio
                                $folioPolizaUe4 = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                                $infoFolios4[] = array(
                                    'tagref' => $tagref,
                                    'ue' => $datoUE,
                                    'type' => $datosClave ['type'],
                                    'transno' => $datosClave ['transno'],
                                    'folioPolizaUe' => $folioPolizaUe4
                                );
                            }
                            // Reversa del compromiso
                            $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'COMPROMETIDO', 'POREJERCER', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $descriptionDice, $datoUE, 1, 0, $folioPolizaUe4);
                        }
                    }
                }
            }

            $estatus = $statusid;
            if ($statusid == '99') {
                // Si es rechazar
                $estatus = $statusActualizacion;
            }

            // Actualizar estatus tabla principal
            $SQL = "UPDATE tb_rectificaciones SET nu_estatus = '".$estatus."'
            WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."' ";
            $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            // Mensaje Configurado
            $msjConfigurado = "";
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

            if (empty($msjConfigurado)) {
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para la Rectificación con Folio '.$datosClave['transno'].' correcta</p>';
            } else {
                $msjConfigurado = str_replace("XXX", $datosClave ['transno'], $msjConfigurado);
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
