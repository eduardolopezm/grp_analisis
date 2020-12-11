<?php
/**
 * Suficiencia Manual
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones del panel de Suficiencia Manual
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
$funcion=2302;
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

$tipoMovGenReduccion = "254"; // Tipo Movimiento Reduccion
$tipoMovGenAmpliacion = "253"; // Tipo Movimiento Ampliacion

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'obtenerSificiencia') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    $ue = $_POST['ue'];
    $tipoSuficiencia = $_POST['tipoSuficiencia'];
    $folio = $_POST['folio'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }
    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND tb_suficiencias.sn_tagref IN (".$tagref.") ";
    }
    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_suficiencias.dtm_fecha between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND tb_suficiencias.dtm_fecha >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_suficiencias.dtm_fecha <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        $sqlWhere .= " AND tb_suficiencias.ln_ue IN (".$ue.") ";
    }

    if ($tipoSuficiencia != '') {
        $sqlWhere .= " AND tb_suficiencias.nu_tipo IN (".$tipoSuficiencia.") ";
    }

    if ($folio != '') {
        // $sqlWhere .= " AND tb_suficiencias.nu_transno like '%".$folio."%' ";
        $sqlWhere .= " AND tb_suficiencias.nu_transno = '".$folio."' ";
    }

    // AND log.description not like 'Autorización Orden de Compra%' se agrega para no tomar en cuenta la autorizacion parcial y deja lo original visible
    $info = array();
    $SQL = "
    SELECT 
    tb_suficiencias.nu_type,
    tb_suficiencias.nu_transno,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.type = tb_suficiencias.nu_type 
    AND log.transno = tb_suficiencias.nu_transno 
    AND log.nu_tipo_movimiento = 263
    AND log.qty < 0
    AND log.description not like 'Autorización Orden de Compra%'
    ) as total,
    tb_suficiencias.nu_estatus,
    tb_suficiencias.sn_userid,
    www_users.realname,
    DATE_FORMAT(tb_suficiencias.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_suficiencias.sn_tagref,
    tags.tagname,
    tb_suficiencias.sn_description,
    tb_suficiencias.nu_tipo,
    tb_suficiencias_cat.sn_nombre as nombreSuficiencia,
    CONCAT(tags.tagref, ' - ', tags.tagname) as tagname,
    tb_suficiencias.nu_estatus as statusid,
    tb_botones_status.statusname,
    purchorders.requisitionno
    FROM tb_suficiencias
    LEFT JOIN tb_suficiencias_cat ON tb_suficiencias_cat.nu_tipo = tb_suficiencias.nu_tipo
    LEFT JOIN www_users ON www_users.userid = tb_suficiencias.sn_userid
    JOIN tags ON tags.tagref = tb_suficiencias.sn_tagref
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_suficiencias.nu_estatus AND tb_botones_status.sn_funcion_id = tb_suficiencias.sn_funcion_id
    LEFT JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_suficiencias.sn_tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND tb_suficiencias.sn_tagref = `tb_sec_users_ue`.`tagref` AND  tb_suficiencias.ln_ue = `tb_sec_users_ue`.`ue`
    WHERE tb_suficiencias.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."' and tb_suficiencias.nu_type = 263 ".$sqlWhere."
    ORDER BY tb_suficiencias.nu_transno DESC
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
        $operacion = '<a type="button" id="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" name="btnAbrirAutorizarAdecuacion_'.$myrow['nu_transno'].'" href="suficiencia_manual.php?'.$liga.'" title="Detalle Suficiencia" style="color: blue;">'.$myrow ['nu_transno'].'</a>'; // target="_blank"
        
        $impresion = '<a type="button" id="btnImprimir'.$myrow['nu_transno'].'" name="btnImprimir'.$myrow['nu_transno'].'" href="impresion_suficiencia.php?'.$liga.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

        $info[] = array(
            'sel' => $seleccionar,
            'nu_type' => $myrow ['nu_type'],
            'nu_transno' => $myrow ['nu_transno'],
            'operacion' => $operacion,
            'requisitionno' => $myrow ['requisitionno'],
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
            'statusid' => $myrow['statusid']
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'sel', type: 'string' },";
    $columnasNombres .= "{ name: 'requisitionno', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_transno', type: 'string' },";
    $columnasNombres .= "{ name: 'operacion', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoSufuciencia', type: 'string' },";
    $columnasNombres .= "{ name: 'statusname', type: 'string' },";
    $columnasNombres .= "{ name: 'tagname', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_description', type: 'string' },";
    // $columnasNombres .= "{ name: 'realname', type: 'string' },";
    $columnasNombres .= "{ name: 'total', type: 'number' },";
    $columnasNombres .= "{ name: 'total2', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_type', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_tagref', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_tipo', type: 'string' },";
    $columnasNombres .= "{ name: 'imprimir', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $colResumenTotal= ", aggregates: [{'<b>Total</b>' :".
                            "function (aggregatedValue, currentValue) {".
                                "var total = currentValue;".
                                "return aggregatedValue + total;".
                            "}".
                        "}] ";
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Sel', datafield: 'sel', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Req', datafield: 'requisitionno', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'nu_transno', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'operacion', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'tipoSufuciencia', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'statusname', width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'tagname', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Justificación', datafield: 'sn_description', width: '23%', cellsalign: 'center', align: 'center', hidden: false },";
    // $columnasNombresGrid .= " { text: 'Usuario', datafield: 'realname', width: '11%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total2', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true },";
    $columnasNombresGrid .= " { text: 'nu_type', datafield: 'nu_type', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'sn_tagref', datafield: 'sn_tagref', width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'nu_tipo', width: '15%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'imprimir', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = "Suficiencia_Presupuestaria_".date('dmY');

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
        $SQL = "SELECT 
                cat_Months.mes as mesName,
                chartdetailsbudgetlog.* 
                FROM chartdetailsbudgetlog
                LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
                LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
                WHERE
                chartdetailsbudgetlog.type = '".$datosClave ['type']."'
                AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."'
                AND chartdetailsbudgetlog.qty < 0";
        $ErrMsg = "No se obtuvieron los registros del No. de Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $disponible = fnInfoPresupuesto($db, $myrow['cvefrom'], $myrow['period']);
            foreach ($disponible as $dispo) {
                //echo "\n ".$myrow['mesName'].": ".$dispo[$myrow['mesName']];
                //echo "\n solicitado: ".$myrow['qty'];
                if ($dispo[$myrow['mesName']] < abs($myrow['qty'])) {
                    $result = false;
                    $actualizar = 0;
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No. Captura '.$datosClave ['transno'].' la Clave Presupuestal '.$myrow['cvefrom'].' en '.$myrow['mesName'].' el disponible es '.$dispo[$myrow['mesName']].' y se solicita Reducción de '.abs($myrow['qty']).' </p>';
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

if ($option == 'rechazarSuficienciaOrdenCompra') {
    // Rechazar la suficiencia desde la Orden de Compra (PO_Items.php)
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $requisitionno = $_POST['requisitionno'];

    $mensajeInfo = '';
    $sn_cancel = 0;

    $SQL = "SELECT sn_cancel FROM tb_suficiencias WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $ErrMsg = "No se Obtuvo Información de la Suficiencia";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $sn_cancel = $myrow['sn_cancel'];
    }

    if ($sn_cancel == 1) {
        // Ya se Rechazo la Suficiencia
        $mensajeInfo = 'Ya se encuentra Rechazada la Suficiencia';
        $result = false;
    } else {
        // Rechazar Suficiencia
        $description = 'Rechazada. Diferencias con la Requisición '.$requisitionno;
        $SQL = "UPDATE tb_suficiencias SET sn_cancel = 1, sn_description = '".$description."' WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
        $ErrMsg = "No se Rechazo la Suficiencia";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $mensajeInfo = 'La Suficiencia con Folio '.$transno.' se Rechazo';
        $result = true;
    }

    $reponse['mensaje'] = $mensajeInfo;
    $contenido = $reponse;
}

if ($option == 'actualizarEstatus') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $mensajeInfo = '';
    $info = array();

    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        //$SQL = "SELECT estatus, tagref FROM chartdetailsbudgetlog WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' LIMIT 1";
        $SQL = "SELECT tb_suficiencias.nu_estatus, tb_suficiencias.nu_tipo, tb_suficiencias.sn_description, tb_suficiencias.sn_funcion_id, tb_suficiencias.nu_estatus, tb_suficiencias.sn_tagref, tb_suficiencias.sn_orderno, purchorders.requisitionno, tb_suficiencias.ln_ue, tb_botones_status.statusname
        FROM tb_suficiencias 
        LEFT JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
        LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = '".$funcion."' AND tb_botones_status.statusid = tb_suficiencias.nu_estatus
        WHERE tb_suficiencias.nu_type = '".$datosClave ['type']."' and tb_suficiencias.nu_transno = '".$datosClave ['transno']."'";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $estatusActual = "";
        $tagref = "";
        $tipoSuficiencia = 2;
        $sn_orderno = 0;
        $mensajeSuficiencia = "Manual";
        $functionSuf = 0;
        $requisitionno = 0;
        $ln_ue = "";
        $nombreEstatus = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            // $estatusActual = $myrow['nu_estatus'];
            // $tagref = $myrow['sn_tagref'];

            $tipoSuficiencia = $myrow['nu_tipo'];
            $sn_orderno = $myrow['sn_orderno'];

            $mensajeSuficiencia = $myrow['sn_description'];
            $functionSuf = $myrow['sn_funcion_id'];
            $estatusActual = $myrow['nu_estatus'];
            $tagref = $myrow['sn_tagref'];

            $requisitionno = $myrow['requisitionno'];

            $ln_ue = $myrow['ln_ue'];
            $nombreEstatus = $myrow['statusname'];
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
            -- , tb_botones_status.*
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

        if ($tipoSuficiencia != 2 && $statusid == '0') {
            // Si desea cancelar y es de una requisicion
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].' no es posible cuenta con la Requisición '.$requisitionno.'</p>';
        } else if (($estatusActual == '0') && $tipoSuficiencia == 2) {
            // Si esta cancelado o autorizado no cambiar estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra</p>';
        } else if (($estatusActual == '4') && $tipoSuficiencia == 2 && $statusid == '99') {
            // Si esta autorizada y quiere rechazar
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra</p>';
        } else if ($errorRechazo == '1' && $tipoSuficiencia == 2) {
            // Si esta rechazando y no tiene permiso a ese estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> La Suficiencia Presupuestal Manual '.$datosClave['transno'].' no puede ser rechazada en el estatus actual</p>';
        } else if ($estatusActual == $statusid && $tipoSuficiencia == 2) {
            // Si esta rechazando y no tiene permiso a ese estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> La Suficiencia Presupuestal Manual '.$datosClave['transno'].' ya se encuentra en '.$nombreEstatus.'</p>';
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

            //$mensajeSuficiencia = "Manual";

            if ($statusid == '0' || $statusid == '4') {
                //Borrar Registros en 0
                $SQL = "DELETE FROM chartdetailsbudgetlog WHERE (qty = '0' OR qty = '-0') AND type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."'";
                $ErrMsg = "No se eliminaron Registros en 0";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }

            if ($statusid == '0') {
                $mensajeSuficiencia .= " Cancelada";
                // Log del presupuesto
                $SQL = "SELECT type, transno, tagref, cvefrom, period, qty, nu_tipo_movimiento, partida_esp, description, estatus, sn_disponible, sn_funcion_id 
                FROM chartdetailsbudgetlog WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $agrego = fnInsertPresupuestoLog($db, $myrow['type'], $myrow['transno'], $myrow['tagref'], $myrow['cvefrom'], $myrow['period'], $myrow['qty'] * -1, $myrow['nu_tipo_movimiento'], $myrow['partida_esp'], $myrow['description'], $myrow['sn_disponible'], $myrow['estatus'], $myrow['sn_funcion_id']);
                }
            }

            if ($statusid == '4' && $tipoSuficiencia == 2) {
                $SQL = "UPDATE chartdetailsbudgetlog SET sn_disponible = '1' WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }

            fnAgregarSuficienciaGeneral($db, $datosClave ['type'], $datosClave ['transno'], $mensajeSuficiencia, $statusActualizacion, $tagref, $tipoSuficiencia, $funcion, $sn_orderno, $ln_ue);

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
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para el folio '.$datosClave['transno'].'</p>';
            } else {
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> '.str_replace("XXX", $datosClave ['transno'], $msjConfigurado).'</p>';
            }
            // $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].'</p>';
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
