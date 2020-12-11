<?php
/**
 * Panel Compromisos
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelo para el proceso del panel de Compromisos
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
$funcion=2409;
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

if ($option == 'mostrarTipoCompromiso') {
    $info = array();
    $SQL = "SELECT nu_tipo as value, CONCAT(nu_tipo, ' - ', sn_nombre) as texto FROM tb_tipo_compromiso_cat ORDER BY nu_orden ASC";
    $ErrMsg = "No se obtuvieron los tipos de suficiencia";
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
    $txtIdCompromiso = $_POST['txtIdCompromiso'];
    $selectEstatusCompromiso = $_POST['selectEstatusCompromiso'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }
    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND tb_compromiso.sn_tagref IN (".$tagref.") ";
    }
    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_compromiso.dtm_fecha between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND tb_compromiso.dtm_fecha >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_compromiso.dtm_fecha <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        $sqlWhere .= " AND chartdetailsbudgetlog.ln_ue IN (".$ue.") ";
    }

    if ($tipoSuficiencia != '') {
        $sqlWhere .= " AND tb_compromiso.nu_tipo IN (".$tipoSuficiencia.") ";
    }

    if ($folio != '') {
        // $sqlWhere .= " AND tb_compromiso.nu_transno like '%".$folio."%' ";
        $sqlWhere .= " AND tb_compromiso.nu_transno = '".$folio."' ";
    }

    if (!empty($txtProveedor)) {
        $sqlWhere .= " AND tb_compromiso.supplierid = '".$txtProveedor."' ";
    }

    if (!empty($txtIdCompromiso)) {
        $sqlWhere .= " AND tb_compromiso.nu_id_compromiso = '".$txtIdCompromiso."' ";
    }

    if ($selectEstatusCompromiso != '') {
        $sqlWhere .= " AND tb_compromiso.nu_estatus IN (".$selectEstatusCompromiso.") ";
    }
    
    // AND log.description not like 'Autorización Orden de Compra%' se agrega para no tomar en cuenta la autorizacion parcial y deja lo original visible
    $info = array();
    $SQL = "
    SELECT 
    DISTINCT
    tb_compromiso.nu_type,
    tb_compromiso.nu_transno,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.type = tb_compromiso.nu_type 
    AND log.transno = tb_compromiso.nu_transno 
    AND log.nu_tipo_movimiento = 259
    ) as total,
    tb_compromiso.nu_estatus,
    tb_compromiso.sn_userid,
    www_users.realname,
    DATE_FORMAT(tb_compromiso.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_compromiso.sn_tagref,
    tags.tagname,
    tb_compromiso.txt_justificacion as sn_description,
    tb_compromiso.nu_tipo,
    CONCAT(tb_compromiso.nu_tipo, ' - ', tb_tipo_compromiso_cat.sn_nombre) as nombreSuficiencia,
    CONCAT(tags.tagref, ' - ', tags.tagname) as tagname,
    tb_compromiso.nu_estatus as statusid,
    tb_botones_status.statusname,
    tb_compromiso.sn_tagref,
    tb_compromiso.ln_ue,
    tb_compromiso.nu_id_compromiso,
    CONCAT(tb_compromiso.supplierid, ' - ', suppliers.suppname) as suppname
    FROM tb_compromiso
    LEFT JOIN tb_tipo_compromiso_cat ON tb_tipo_compromiso_cat.nu_tipo = tb_compromiso.nu_tipo
    LEFT JOIN www_users ON www_users.userid = tb_compromiso.sn_userid
    JOIN tags ON tags.tagref = tb_compromiso.sn_tagref
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_compromiso.nu_estatus AND tb_botones_status.sn_funcion_id = tb_compromiso.sn_funcion_id
    LEFT JOIN suppliers ON suppliers.supplierid = tb_compromiso.supplierid
    LEFT JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_compromiso.nu_type AND chartdetailsbudgetlog.transno = tb_compromiso.nu_transno
    WHERE tb_compromiso.nu_type = 259 ".$sqlWhere."
    ORDER BY tb_compromiso.nu_transno DESC
    ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $operacion = "";
        $seleccionar = "";

        if (($myrow ['statusid'] != '0' && $myrow ['statusid'] != '4') || ($myrow ['nu_tipo'] == 2 && $myrow ['statusid'] == '4')) {
            // Si no es 0 = Cancelado, 4 = Autorizado
            $seleccionar = '<input type="checkbox" id="checkbox_'.$myrow ['nu_transno'].'" name="checkbox_'.$myrow ['nu_transno'].'" title="Seleccionar" value="'.$myrow ['statusid'].'" onchange="fnValidarProcesoCambiarEstatus()" />';
        }

        $urlGeneral = "&transno=>" . $myrow['nu_transno'] . "&type=>" . $myrow['nu_type'];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $liga= "URL=" . $url;
        $operacion = '<a type="button" id="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" name="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" href="compromiso.php?'.$liga.'" title="Detalle Suficiencia" style="color: blue;">'.$myrow ['nu_transno'].'</a>'; // target="_blank"
        
        $impresion = '<a type="button" id="btnImprimir'.$myrow['nu_transno'].'" name="btnImprimir'.$myrow['nu_transno'].'" href="impresion_suficiencia.php?'.$liga.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

        $info[] = array(
            // 'sel' => $seleccionar,
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
            'nu_tipo' => $myrow ['nu_tipo'],
            'imprimir' => $impresion,
            'statusid' => $myrow['statusid'],
            'ur' => $myrow ['sn_tagref'],
            'ue' => $myrow ['ln_ue'],
            'idcompromiso' => $myrow ['nu_id_compromiso'],
            'suppname' => $myrow['suppname']
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    // $columnasNombres .= "{ name: 'sel', type: 'string' },";
    $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoSufuciencia', type: 'string' },";
    $columnasNombres .= "{ name: 'idcompromiso', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_transno', type: 'string' },";
    $columnasNombres .= "{ name: 'operacion', type: 'string' },";
    $columnasNombres .= "{ name: 'suppname', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_description', type: 'string' },";
    $columnasNombres .= "{ name: 'statusname', type: 'string' },";
    $columnasNombres .= "{ name: 'tagname', type: 'string' },";
    $columnasNombres .= "{ name: 'total', type: 'number' },";
    $columnasNombres .= "{ name: 'total2', type: 'string' },";
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
    // $columnasNombresGrid .= " { text: 'Sel', datafield: 'sel', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: '', datafield: 'id1', width: '3%', editable: true, editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '3%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Operación', datafield: 'tipoSufuciencia', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Compromiso', datafield: 'idcompromiso', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
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
    $columnasNombresGrid .= " { text: 'statusid', datafield: 'statusid', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'validarDisponibleNoCaptura') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;

        $movimientoTipo = '';
        if ($datosClave['nu_tipo'] == 'D') {
            // Si es Decremento
            $movimientoTipo = 'Compromiso';
        }

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
        // AND chartdetailsbudgetlog.qty < 0
        $ErrMsg = "No se obtuvieron los registros del No. de Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            // $disponible = fnInfoPresupuesto($db, $myrow['cvefrom'], $myrow['period']);
            $disponible = fnInfoPresupuesto($db, $myrow['cvefrom'], '', '', '', 0, 0, '', $datosClave ['type'], $datosClave ['transno'], 'Reduccion');
            foreach ($disponible as $dispo) {
                if ($dispo[$myrow['mesName'].$movimientoTipo] < abs($myrow['qty'])) {
                    $result = false;
                    $actualizar = 0;
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Compromiso '.$datosClave ['idcompromiso'].' con Folio '.$datosClave ['transno'].' la clave presupuestal '.$myrow['cvefrom'].' en '.$myrow['mesName'].' el disponible es $ '.number_format($dispo[$myrow['mesName'].$movimientoTipo], $_SESSION['DecimalPlaces'], '.', ',').' y se solicita un monto de $ '.number_format(abs($myrow['qty']), $_SESSION['DecimalPlaces'], '.', ',').' </p>';
                }
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

if ($option == 'actualizarEstatus') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $mensajeInfo = '';
    $info = array();

    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $SQL = "SELECT 
        tb_compromiso.nu_estatus,
        tb_compromiso.nu_tipo,
        tb_compromiso.sn_tagref,
        tb_compromiso.ln_ue,
        tb_compromiso.txt_justificacion,
        tb_botones_status.statusname
        FROM tb_compromiso 
        LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = '".$funcion."' AND tb_botones_status.statusid = tb_compromiso.nu_estatus
        WHERE tb_compromiso.nu_type = '".$datosClave ['type']."' AND tb_compromiso.nu_transno = '".$datosClave ['transno']."'";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $tagref = "";
        $ln_ue = "";
        $estatusActual = "";
        $tipoSuficiencia = '';
        $nombreEstatus = "";
        $justificacion = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            $tagref = $myrow['sn_tagref'];
            $ln_ue = $myrow['ln_ue'];
            $estatusActual = $myrow['nu_estatus'];
            $tipoSuficiencia = $myrow['nu_tipo'];
            $nombreEstatus = $myrow['statusname'];
            $justificacion = $myrow['txt_justificacion'];
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
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Compromiso '.$datosClave ['idcompromiso'].' con Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        } else if ($errorRechazo == '1') {
            // Si esta rechazando y no tiene permiso a ese estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Compromiso '.$datosClave ['idcompromiso'].' con Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        } else if ($estatusActual == $statusid) {
            // Si se avanza al mismo estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> El Compromiso '.$datosClave ['idcompromiso'].' con Folio '.$datosClave['transno'].' ya se encuentra en '.$nombreEstatus.'</p>';
        } else {
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
                // Si es autorización
                $SQL = "UPDATE chartdetailsbudgetlog SET sn_disponible = '1' WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $SQL = "SELECT estatus, qty, cvefrom, tagref, type, txt_justificacion, ln_ue FROM chartdetailsbudgetlog WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                
                // Array para folios agrupados (UR-UE)
                $infoFolios = array();
                $period = GetPeriod(date('d/m/Y'), $db);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $claveCreada = $myrow['cvefrom'];
                    $tagref = $myrow['tagref'];
                    $totalPoliza = abs($myrow['qty']);
                    $fechapoliza = date('Y-m-d');
                    $referencia = "Clave: ".$claveCreada;

                    $datoUE = $myrow['ln_ue']; // fnObtenerUnidadEjecutoraClave($db, $claveCreada);

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

                    if ($myrow['qty'] < 0) {
                        // Reducción
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'POREJERCER', 'COMPROMETIDO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $justificacion, $datoUE, 1, 0, $folioPolizaUe);
                    } else if ($myrow['qty'] > 0) {
                        // Ampliación
                        $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'COMPROMETIDO', 'POREJERCER', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $justificacion, $datoUE, 1, 0, $folioPolizaUe);
                    }
                }
            }

            $estatus = $statusid;
            if ($statusid == '99') {
                // Si es rechazar
                $estatus = $statusActualizacion;
            }
            // Actualizar estatus tabla principal
            $SQL = "UPDATE tb_compromiso SET nu_estatus = '".$estatus."' WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."' ";
            $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            // Mensaje Configurado
            $SQL = "SELECT 
            tb_botones_status.sn_mensaje_opcional, tb_botones_status.sn_mensaje_opcional2
            FROM chartdetailsbudgetlog
            LEFT JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
            WHERE chartdetailsbudgetlog.type = '".$datosClave ['type']."' AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."'
            LIMIT 1";
            $ErrMsg = "No se obtuvo información para mostrar mensaje del proceso ";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $msjConfigurado = "";
            while ($myrow = DB_fetch_array($TransResult)) {
                if ($statusid == '99') {
                    $msjConfigurado = $myrow['sn_mensaje_opcional2'];
                } else {
                    $msjConfigurado = $myrow['sn_mensaje_opcional'];
                }
            }

            if (empty($msjConfigurado)) {
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para el Compromiso '.$datosClave ['idcompromiso'].'con Folio '.$datosClave['transno'].' correcta</p>';
            } else {
                $msjConfigurado = str_replace("XXX", $datosClave ['transno'], $msjConfigurado);
                $msjConfigurado = str_replace("ZZZ", $datosClave ['idcompromiso'], $msjConfigurado);
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
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid)
            ) 
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
