<?php
/**
 * Panel de Disposición Final de Bienes
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 16/10/2018
 * Fecha Modificación: 16/10/2018
 * Modelo para el proceso del Panel de Disposición Final de Bienes
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
$funcion=2487;
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

if ($option == 'obtenerSificiencia') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    $ue = $_POST['ue'];
    $tipoSuficiencia = $_POST['tipoSuficiencia'];
    $folio = trim($_POST['folio']);

    $txtArticulo = trim($_POST['txtArticulo']);
    $selectEstatusCompromiso = $_POST['selectEstatusCompromiso'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }

    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND tb_dp.sn_tagref IN (".$tagref.") ";
    }

    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_dp.dtm_fecha between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND tb_dp.dtm_fecha >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_dp.dtm_fecha <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        $sqlWhere .= " AND (tb_dp.ln_ue IN (".$ue.") OR tb_dp.ln_ue IN (".$ue.")) ";
    }

    if ($tipoSuficiencia != '') {
        $sqlWhere .= " AND tb_dp.nu_tipo_bien IN (".$tipoSuficiencia.") ";
    }

    if ($folio != '') {
        $sqlWhere .= " AND tb_dp.nu_transno = '".$folio."' ";
    }

    if (!empty($txtArticulo)) {
        $sqlWhere .= " AND (fixedassets.assetid like '%".$txtArticulo."%' or fixedassets.barcode like '%".$txtArticulo."%' or fixedassets.description like '%".$txtArticulo."%') ";
    }

    if ($selectEstatusCompromiso != '') {
        $sqlWhere .= " AND tb_dp.nu_estatus IN (".$selectEstatusCompromiso.") ";
    }
    
    $info = array();
    $SQL = "
    SELECT
        DISTINCT
        tb_dp.nu_type,
        tb_dp.nu_transno,
        tb_dp.nu_transno_baja,
        coalesce(sum(fixedassets.accumdepn),'0.00') as total,
        tb_dp.nu_estatus,
        tb_dp.sn_userid,
        www_users.realname,
        DATE_FORMAT(tb_dp.dtm_fecha, '%d-%m-%Y') as fecha_captura,
        tb_dp.sn_tagref,
        tags.tagname,
        fixedAssetCategoryBien.description as tipoBien,
        CONCAT(tb_dp.nu_type, ' - ', systypescat.typename) as nombreSuficiencia,
        CONCAT(tags.tagref, ' - ', tags.tagname) as tagname,
        tb_dp.nu_estatus as statusid,
        tb_botones_status.statusname,
        tb_dp.sn_tagref,
        tb_dp.ln_ue,
        tb_dp.txt_justificacion
    FROM tb_Fixed_Disposicion_Patrimonial as tb_dp
    LEFT JOIN tb_Fixed_Disposicion_Patrimonial_Detalle as tb_dp_Detalle ON tb_dp_Detalle.nu_type = tb_dp.nu_type AND tb_dp_Detalle.nu_transno = tb_dp.nu_transno
    LEFT JOIN fixedassets ON fixedassets.assetid = tb_dp_Detalle.nu_assetid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_dp.sn_tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tb_dp.sn_tagref AND tb_sec_users_ue.ue = tb_dp.ln_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    LEFT JOIN systypescat ON systypescat.typeid = tb_dp.nu_type
    LEFT JOIN www_users ON www_users.userid = tb_dp.sn_userid
    JOIN tags ON tags.tagref = tb_dp.sn_tagref
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_dp.nu_estatus AND tb_botones_status.sn_funcion_id = tb_dp.sn_funcion_id
    LEFT JOIN fixedAssetCategoryBien ON fixedAssetCategoryBien.id = tb_dp.nu_tipo_bien
    WHERE 1 = 1 ".$sqlWhere."
    GROUP BY tb_dp.nu_type,
             tb_dp.nu_transno
    ORDER BY tb_dp.nu_transno desc
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
        $operacion = '<a type="button" id="btnAbrirDetalle_'.$myrow['nu_transno'].'" name="btnAbrirDetalle_'.$myrow['nu_transno'].'" href="disposicionPatrimonial.php?'.$liga.'" title="Detalle Pago" style="color: blue;">'.$myrow ['nu_transno'].'</a>'; // target="_blank"
        
        $impresion = '<a type="button" id="btnImprimir'.$myrow['nu_transno'].'" name="btnImprimir'.$myrow['nu_transno'].'" href="impresion_suficiencia.php?'.$liga.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

        $info[] = array(
            'id1' =>false,
            'nu_type' => $myrow ['nu_type'],
            'nu_transno' => $myrow ['nu_transno'],
            'nu_transno_baja' => $myrow ['nu_transno_baja'],
            'operacion' => $operacion,
            'total' => ($myrow ['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'total2' => ($myrow ['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'tipoBien' => $myrow['tipoBien'],
            'sn_description' => $myrow['txt_justificacion'],
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
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_transno', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_transno_baja', type: 'string' },";
    $columnasNombres .= "{ name: 'operacion', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoBien', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_description', type: 'string' },";
    $columnasNombres .= "{ name: 'tagname', type: 'string' },";
    $columnasNombres .= "{ name: 'total', type: 'double' },";
    $columnasNombres .= "{ name: 'total2', type: 'double' },";
    $columnasNombres .= "{ name: 'statusname', type: 'string' },";
    $columnasNombres .= "{ name: 'imprimir', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_type', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_tagref', type: 'string' },";
    $columnasNombres .= "{ name: 'nu_tipo', type: 'string' },";
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
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'nu_transno', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'operacion', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio Baja', datafield: 'nu_transno_baja', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo Bien', datafield: 'tipoBien', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Justificación', datafield: 'sn_description', width: '35%', editable: false, cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'tagname', width: '12%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Valor', datafield: 'total', width: '10%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Valor', datafield: 'total2', width: '11%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'statusname', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'imprimir', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'nu_type', datafield: 'nu_type', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'sn_tagref', datafield: 'sn_tagref', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'nu_tipo', width: '15%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'statusid', datafield: 'statusid', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'validarDisponibleNoCaptura') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];
    $cancelar = $_POST['cancelar'];
    $autorizar = $_POST['autorizar'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;

        
           $SQL="UPDATE tb_Fixed_Disposicion_Patrimonial SET nu_estatus = '".$statusid."' WHERE nu_type = '".$datosClave['type']."' AND nu_transno='".$datosClave['transno']."'";
        
        //echo "sql". $SQL;
        $result = DB_query($SQL,$db); 

        if($cancelar == "1"){
            $SQL ="UPDATE tb_Fixed_Baja_Patrimonial_Detalle
                LEFT JOIN  (SELECT tb_dp.nu_type,
                                    tb_dp.nu_transno, 
                                    tb_dp.nu_transno_baja, 
                                    tb_dp_detalle.nu_assetid
                            FROM tb_Fixed_Disposicion_Patrimonial tb_dp
                            LEFT JOIN tb_Fixed_Disposicion_Patrimonial_Detalle tb_dp_detalle ON tb_dp.nu_type = tb_dp_detalle.nu_type and tb_dp.nu_transno = tb_dp_detalle.nu_transno
                            WHERE tb_dp.nu_type  = '".$datosClave['type']."' and tb_dp.nu_transno = '".$datosClave['transno']."') tb_dis
                ON tb_Fixed_Baja_Patrimonial_Detalle.nu_transno=tb_dis.nu_transno_baja and tb_Fixed_Baja_Patrimonial_Detalle.nu_assetid = tb_dis.nu_assetid
                SET afectacion_disposicion = 0
                WHERE tb_Fixed_Baja_Patrimonial_Detalle.nu_transno  in(SELECT  nu_transno_baja
                                                                        FROM tb_Fixed_Disposicion_Patrimonial
                                                                        WHERE nu_type  = '".$datosClave['type']."' AND nu_transno = '".$datosClave['transno']."')";
            $result = DB_query($SQL,$db);

        }else if($autorizar=="1"){
            $SQL ="UPDATE fixedassets
                inner JOIN  (SELECT tb_dp.nu_type,
                                    tb_dp.nu_transno, 
                                    tb_dp.nu_transno_baja, 
                                    tb_dp.nu_type_disposicion,
                                    tb_dp_detalle.nu_assetid
                            FROM tb_Fixed_Disposicion_Patrimonial tb_dp
                            LEFT JOIN tb_Fixed_Disposicion_Patrimonial_Detalle tb_dp_detalle ON tb_dp.nu_type = tb_dp_detalle.nu_type and tb_dp.nu_transno = tb_dp_detalle.nu_transno
                            WHERE tb_dp.nu_type  = '".$datosClave['type']."' and tb_dp.nu_transno = '".$datosClave['transno']."') tb_dis
                ON fixedassets.assetid=tb_dis.nu_assetid 
                SET fixedassets.status = tb_dis.nu_type_disposicion
                WHERE fixedassets.assetid  in (SELECT tb_dp_detalle.nu_assetid
                            FROM tb_Fixed_Disposicion_Patrimonial tb_dp
                            LEFT JOIN tb_Fixed_Disposicion_Patrimonial_Detalle tb_dp_detalle ON tb_dp.nu_type = tb_dp_detalle.nu_type and tb_dp.nu_transno = tb_dp_detalle.nu_transno
                            WHERE tb_dp.nu_type  = '".$datosClave['type']."' and tb_dp.nu_transno = '".$datosClave['transno']."')";
            $result = DB_query($SQL,$db);

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
        tb_Fixed_Disposicion_Patrimonial.nu_estatus,
        tb_botones_status.statusname,
        tb_botones_status.sn_estatus_anterior
        FROM tb_Fixed_Disposicion_Patrimonial 
        LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_Fixed_Disposicion_Patrimonial.sn_funcion_id AND tb_botones_status.statusid = tb_Fixed_Disposicion_Patrimonial.nu_estatus
        WHERE tb_Fixed_Disposicion_Patrimonial.nu_type = '".$datosClave ['type']."' AND tb_Fixed_Disposicion_Patrimonial.nu_transno = '".$datosClave ['transno']."'";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $estatusActual = "";
        $nombreEstatus = "";
        $statusAnterior = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            $estatusActual = $myrow['nu_estatus'];
            $nombreEstatus = $myrow['statusname'];
            $statusAnterior = $myrow['sn_estatus_anterior'];
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
        $statusActualizacion = $statusid;

            $msjConfigurado = "";
            $SQL = "SELECT 
            tb_botones_status.sn_mensaje_opcional, tb_botones_status.sn_mensaje_opcional2
            FROM tb_botones_status
            WHERE tb_botones_status.sn_funcion_id = '".$funcion."' AND tb_botones_status.statusid = '".$statusActualizacion."'
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
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para la Disposicion Patrimonial con Folio '.$datosClave['transno'].' correcta</p>';
            } else {
                $msjConfigurado = str_replace("XXX", $datosClave ['transno'], $msjConfigurado);
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> '.$msjConfigurado.'</p>';
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
            LEFT JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            LEFT JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE 
            (tb_botones_status.sn_funcion_id = '".$funcion."')
            AND (tb_botones_status.sn_flag_disponible = 1)
            AND (tb_botones_status.sn_panel_adecuacion_presupuestal = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."')
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
