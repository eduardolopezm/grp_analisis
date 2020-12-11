<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Modelos para las operaciones del panel de adecuaciones presupuestales
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
$funcion=2263;
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

$permisoUsarLayoutGeneral = Havepermission($_SESSION['UserID'], 2331, $db);

if ($option == 'autorizarAdecuacion') {
    $pSicop = $_POST['pSicop'];
    $fMap = $_POST['fMap'];
    $fecha = $_POST['fecha'];
    $noCaptura = $_POST['noCaptura'];
    $statusid = 7;

    $mensajeInfo = '';
    $info = array();

    foreach ($noCaptura as $datosClave) {
        $SQL = "SELECT estatus, qty, cvefrom, tagref, type, txt_justificacion FROM chartdetailsbudgetlog WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
        $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $num = 1;
        $validacionEstatus = 0;
        // Array para folios agrupados (UR-UE)
        $infoFolios = array();
        while ($myrow = DB_fetch_array($TransResult)) {
            $estatusActual = $myrow['estatus'];

            if (($estatusActual == '7' || $estatusActual == '0') && $num == 1) {
                // echo "\n entra if autorizado o cancelado";
                $mensajeInfo .= '<h4><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Autorización para el folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra</h4>';
                $validacionEstatus = 1;
            }
            
            if ($validacionEstatus == 0) {
                // Actualizar datos clave nueva Inicio, se comenta codigo ya que se toma la clave desde un inicio
                // $SQL = "SELECT accountcode FROM chartdetailsbudgetbytag WHERE nu_transno = '".$datosClave['transno']."'";
                // $ErrMsg = "";
                // $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                // while ($myrow2 = DB_fetch_array($TransResult2)) {
                //     $claveSeparada = explode('-', $myrow2['accountcode']);
                //     $claveNueva = "";
                //     for ($elemento = 0; $elemento < count(explode('-', $myrow2['accountcode'])); $elemento ++) {
                //         if ($claveNueva == '') {
                //             $claveNueva = date('Y');
                //         } else {
                //             $claveNueva = $claveNueva . '-' . $claveSeparada[$elemento];
                //         }
                //     }

                //     $SQL = "UPDATE chartdetailsbudgetbytag SET anho = '".date('Y')."', accountcode = '".$claveNueva."' WHERE accountcode = '".$myrow2['accountcode']."'";
                //     $ErrMsg = "Eliminar adiciones";
                //     $TransResult3 = DB_query($SQL, $db, $ErrMsg);

                //     $SQL = "UPDATE chartdetailsbudgetlog SET cvefrom = '".$claveNueva."' WHERE cvefrom = '".$myrow2['accountcode']."' AND type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                //     $ErrMsg = "Eliminar adiciones";
                //     $TransResult3 = DB_query($SQL, $db, $ErrMsg);
                // }
                // Actualizar datos clave nueva Fin

                $claveCreada = $myrow['cvefrom'];
                $tagref = $myrow['tagref'];
                $totalPoliza = abs($myrow['qty']);
                $fechapoliza = date('Y-m-d');
                $period = GetPeriod(date('d/m/Y'), $db);
                $referencia = "Clave: ".$claveCreada;

                $infoClaves = array();
                $infoClaves[] = array(
                    'accountcode' => $myrow ['cvefrom']
                );
                $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                $period = $respuesta['periodo'];
                $fechapoliza = $respuesta['fecha'];

                $datoUE = fnObtenerUnidadEjecutoraClave($db, $claveCreada);

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
                    // echo "\n reduccion";
                    $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'POREJERCER', 'MODIFICADO', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', 'Reducción - '.$claveCreada, $datoUE, 1, 0, $folioPolizaUe);
                } else if ($myrow['qty'] > 0) {
                    // Ampliación
                    // echo "\n ampliacion";
                    $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], 'MODIFICADO', 'POREJERCER', $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', 'Ampliación - '.$claveCreada, $datoUE, 1, 0, $folioPolizaUe);
                }
                
                if ($num == 1) {
                    //Borrar Registros en 0
                    $SQL = "DELETE FROM chartdetailsbudgetlog WHERE (qty = '0' OR qty = '-0') AND type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."'";
                    $ErrMsg = "No se eliminaron Registros en 0";
                    $TransResultUpdate = DB_query($SQL, $db, $ErrMsg);
                    
                    $liga="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $datosClave ['type'] . "&TransNo=" . $datosClave ['transno'] . "&periodo=" . $period . "&trandate=" . date('Y-m-d');
                    $reimprimir = "<a target='_blank' href='" . $liga . "'><img src='images/printer.png' title='" . _('Imprimir Poliza') . "' alt=''></a>";
                    
                    $SQL = "UPDATE chartdetailsbudgetlog SET 
                    estatus = '".$statusid."', 
                    txt_proceso_sicop = '".$pSicop."', 
                    txt_folio_map = '".$fMap."', 
                    dtm_aplicacion = STR_TO_DATE('".$fecha."','%d-%m-%Y %H:%i:%s'),
                    sn_disponible = '1' 
                    WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                    $TransResultUpdate = DB_query($SQL, $db, $ErrMsg);

                    $mensajeInfo .= '<h4><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Autorización para el folio '.$datosClave['transno'].'. '.$reimprimir.'</h4>';
                }
            }

            $num ++;
        }

        if ($validacionEstatus == 0) {
            // Log del presupuesto
            $SQL = "SELECT type, transno, tagref, cvefrom, period, qty, nu_tipo_movimiento, partida_esp, description 
            , estatus, sn_adecuacion, numero_oficio, nu_centro_contable, nu_tipo_reg
            , nu_cat_jusr, txt_dictamen_upi, txt_control_interno, txt_justificacion
            , nu_tipo_solicitud, nu_r23, txt_proceso_sicop, txt_folio_map, sn_tagref_receptora, ln_ue
            FROM chartdetailsbudgetlog WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
            $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                // Dejar en 0 la ampliacion y reduccion en tramite
                $agrego = fnInsertPresupuestoLog($db, $myrow['type'], $myrow['transno'], $myrow['tagref'], $myrow['cvefrom'], $myrow['period'], $myrow['qty'] * -1, $myrow['nu_tipo_movimiento'], $myrow['partida_esp'], $myrow['description'], 1, '', 0, $myrow['ln_ue']);
                if ($myrow['qty'] < 0) {
                    // Agregar reducción
                    $agrego = fnInsertPresupuestoLog($db, $myrow['type'], $myrow['transno'], $myrow['tagref'], $myrow['cvefrom'], $myrow['period'], $myrow['qty'], 254, $myrow['partida_esp'], $myrow['description'], 1, '', 0, $myrow['ln_ue']);
                } else {
                    // Agregar ampliacion
                    $agrego = fnInsertPresupuestoLog($db, $myrow['type'], $myrow['transno'], $myrow['tagref'], $myrow['cvefrom'], $myrow['period'], $myrow['qty'], 253, $myrow['partida_esp'], $myrow['description'], 1, '', 0, $myrow['ln_ue']);
                }

                // Actualizacion Datos Generales
                $SQL = "UPDATE chartdetailsbudgetlog 
                        SET 
                        chartdetailsbudgetlog.estatus = '".$myrow['estatus']."', 
                        chartdetailsbudgetlog.sn_adecuacion = '".$myrow['sn_adecuacion']."', 
                        chartdetailsbudgetlog.numero_oficio = '".$myrow['numero_oficio']."', 
                        chartdetailsbudgetlog.nu_centro_contable = '".$myrow['nu_centro_contable']."', 
                        chartdetailsbudgetlog.nu_tipo_reg = '".$myrow['nu_tipo_reg']."', 
                        chartdetailsbudgetlog.nu_cat_jusr = '".$myrow['nu_cat_jusr']."', 
                        chartdetailsbudgetlog.txt_dictamen_upi = '".$myrow['txt_dictamen_upi']."', 
                        chartdetailsbudgetlog.txt_control_interno = '".$myrow['txt_control_interno']."', 
                        chartdetailsbudgetlog.txt_justificacion = '".$myrow['txt_justificacion']."', 
                        chartdetailsbudgetlog.nu_tipo_solicitud = '".$myrow['nu_tipo_solicitud']."', 
                        chartdetailsbudgetlog.nu_r23 = '".$myrow['nu_r23']."', 
                        chartdetailsbudgetlog.txt_proceso_sicop = '".$myrow['txt_proceso_sicop']."', 
                        chartdetailsbudgetlog.txt_folio_map = '".$myrow['txt_folio_map']."', 
                        chartdetailsbudgetlog.sn_tagref_receptora = '".$myrow['sn_tagref_receptora']."',
                        chartdetailsbudgetlog.sn_reglas_validadas = '2',
                        chartdetailsbudgetlog.userid = '".$_SESSION['UserID']."'
                        WHERE chartdetailsbudgetlog.type = '".$myrow['type']."' AND chartdetailsbudgetlog.transno = '".$myrow['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros en Generales";
                $TransResultUpdate = DB_query($SQL, $db, $ErrMsg);
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

if ($option == 'ValidarFolioSicopMap') {
    $pSicop = $_POST['pSicop'];
    $fMap = $_POST['fMap'];
    $noCaptura = $_POST['noCaptura'];

    $mensajeInfo = "";

    if (!empty(trim($pSicop))) {
        $SQL = "SELECT txt_proceso_sicop, transno FROM chartdetailsbudgetlog WHERE type=250 and txt_proceso_sicop = '".trim($pSicop)."' and transno <> '".$noCaptura."'";
        $ErrMsg = "No se Obtuvo información del Folio Sicop ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        if (DB_num_rows($TransResult) > 0) {
            $myrow = DB_fetch_array($TransResult);
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El P SICOP '.$pSicop.' ya existe en la Adecuación con Folio '.$myrow['transno'].' </p>';
        }
    }

    if (!empty(trim($fMap))) {
        $SQL = "SELECT txt_folio_map, transno FROM chartdetailsbudgetlog WHERE type=250 and txt_folio_map = '".trim($fMap)."' and transno <> '".$noCaptura."'";
        $ErrMsg = "No se Obtuvo información del Folio Sicop ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        if (DB_num_rows($TransResult) > 0) {
            $myrow = DB_fetch_array($TransResult);
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El F MAP '.$fMap.' ya existe en la Adecuación con Folio '.$myrow['transno'].' </p>';
        }
    }

    $reponse['mensaje'] = $mensajeInfo;
    $reponse['datos'] = $info;

    $contenido = $reponse;

    $result = true;
}

if ($option == 'obtenerAdecuaciones') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $tipoAdecuacion = $_POST['tipoAdecuacion'];
    $estatus = $_POST['estatus'];
    $tipoSolicitud = $_POST['tipoSolicitud'];
    $folio = $_POST['folio'];
    $transno = $_POST['transno'];
    $noOficio = $_POST['noOficio'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }

    if (!empty($tagref) and $tagref != '-1') {
        $sqlWhere .= " AND chartdetailsbudgetlog.tagref IN (".$tagref.") ";
    }

    if (!empty($tipoAdecuacion) and $tipoAdecuacion != '0') {
        $sqlWhere .= " AND chartdetailsbudgetlog.sn_adecuacion IN (".$tipoAdecuacion.") ";
    }

    if (!empty($estatus)) {
        $sqlWhere .= " AND chartdetailsbudgetlog.estatus IN (".$estatus.") ";
    }

    if (!empty($tipoSolicitud)) {
        $sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_solicitud IN (".$tipoSolicitud.") ";
    }

    if (!empty($folio)) {
        // $sqlWhere .= " AND chartdetailsbudgetlog.folio like '%".$folio."%' ";
        $sqlWhere .= " AND chartdetailsbudgetlog.folio = '".$folio."' ";
    }

    if (!empty($transno)) {
        // $sqlWhere .= " AND chartdetailsbudgetlog.transno like '%".$transno."%' ";
        $sqlWhere .= " AND chartdetailsbudgetlog.transno = '".$transno."' ";
    }

    if (!empty($noOficio)) {
        $sqlWhere .= " AND chartdetailsbudgetlog.numero_oficio like '%".$noOficio."%' ";
    }

    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND chartdetailsbudgetlog.fecha_captura between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND chartdetailsbudgetlog.fecha_captura >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND chartdetailsbudgetlog.fecha_captura <= '".$fechaHasta." 23:59:59' ";
    }

    // JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
    // JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
    // AND
    // (tb_botones_status.functionid = sec_funxprofile.functionid
    // OR
    // tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND userid = '".$_SESSION['UserID']."')
    // )
    // chartdetailsbudgetlog.userid,
    // www_users.realname,
    // LEFT JOIN www_users ON www_users.userid = chartdetailsbudgetlog.userid

    $info = array();
    $SQL = "
    SELECT 
    distinct chartdetailsbudgetlog.type,
    chartdetailsbudgetlog.transno,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.qty < 0 AND log.nu_tipo_movimiento = 256 AND log.type = chartdetailsbudgetlog.type AND log.transno = chartdetailsbudgetlog.transno
    ) as totalReduccion,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.qty > 0 AND log.nu_tipo_movimiento = 257 AND log.type = chartdetailsbudgetlog.type AND log.transno = chartdetailsbudgetlog.transno
    ) as totalAmpliacion,
    tb_botones_status.statusid,
    tb_botones_status.statusname,
    (SELECT SUM(log.qty) FROM chartdetailsbudgetlog log 
    WHERE log.qty > 0 AND log.type = chartdetailsbudgetlog.type AND log.transno = chartdetailsbudgetlog.transno
    ) as userid,
    '' as fecha_captura,
    tb_tipo_adecuacion.txt_descripcion as nameAdecuacion,
    tb_tipo_solicitud.txt_descripcion as nameTipoSolicitud,
    chartdetailsbudgetlog.sn_reglas_validadas
    FROM chartdetailsbudgetlog
    JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'     
    LEFT JOIN tb_tipo_adecuacion ON tb_tipo_adecuacion.nu_adecuacion = chartdetailsbudgetlog.sn_adecuacion
    JOIN tags ON tags.tagref = chartdetailsbudgetlog.tagref
    LEFT JOIN tb_tipo_solicitud ON tb_tipo_solicitud.nu_tipo_solicitud = chartdetailsbudgetlog.nu_tipo_solicitud
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = chartdetailsbudgetlog.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = chartdetailsbudgetlog.tagref AND tb_sec_users_ue.ue = chartdetailsbudgetlog.ln_ue_creadora AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    WHERE 
    chartdetailsbudgetlog.type IN (250)
    AND chartdetailsbudgetlog.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
    " . $sqlWhere . " 
    having nameAdecuacion != ''
    ORDER BY transno DESC
    ";
    //////
    // echo "<pre>".$SQL;
    // exit();
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $operacion = "";
        $seleccionar = "";
        if ($myrow ['statusid'] != '0' && $myrow ['statusid'] != '5' && $myrow ['statusid'] != '6') {
            $enc = new Encryption;
            $url = "&transno=>" . $myrow['transno'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            $operacion = '<a type="button" id="btnModificar_'.$myrow['transno'].'" name="btnModificar_'.$myrow['transno'].'" class="glyphicon glyphicon-edit btn-xs botonVerde" href="GLBudgetsByTagV2.php?'.$liga.'" title="Modificar"></a>'; // target="_blank"
        }

        if ($myrow ['statusid'] == '5' || $myrow ['statusid'] == '6') {
            $operacion = '<button type="button" id="btnGenerarLayout_'.$myrow['transno'].'" name="btnGenerarLayout_'.$myrow['transno'].'" class="glyphicon glyphicon-folder-open btn-xs botonVerde" onclick="fnArchivosLayout(this, '.$myrow ['transno'].')" title="Archivos"></button>';
        }

        if ($myrow ['statusid'] == '6') {
            // Operacion para Panel
            //$operacion .= '<button type="button" id="btnAbrirAutorizarAdecuacion_'.$myrow['transno'].'" name="btnAbrirAutorizarAdecuacion_'.$myrow['transno'].'" class="glyphicon glyphicon-ok-sign btn-xs botonVerde" onclick="fnAutorizarAdecuacionModal(this, '.$myrow ['transno'].', '.$myrow ['type'].')" title="Autorizar Adecuación"></button>';
            // Operacion para Adecuaciones
            $enc = new Encryption;
            $url = "&transno=>" . $myrow['transno'] . "&autorizar=>1";
            $url = $enc->encode($url);
            $liga= "URL=" . $url;
            $operacion .= '<a type="button" id="btnAbrirAutorizarAdecuacion_'.$myrow['transno'].'" name="btnAbrirAutorizarAdecuacion_'.$myrow['transno'].'" class="glyphicon glyphicon-ok-sign btn-xs botonVerde" href="GLBudgetsByTagV2.php?'.$liga.'" title="Autorizar Adecuación"></a>'; // target="_blank"
        }

        // if ($myrow ['statusid'] == '5') {
        //     // Imprimir poliza
        //     $enc = new Encryption;
        //     $url = "&FromCust=>1&ToCust=>1&PrintPDF=>Yes&type=>" . $myrow ['type'] . "&TransNo=>" . $myrow['transno'];
        //     $url = $enc->encode($url);
        //     $liga= "URL=" . $url;
        //     $operacion .= '<a type="button" id="btnVerPoliza_'.$myrow['transno'].'" name="btnVerPoliza_'.$myrow['transno'].'" class="glyphicon glyphicon-print btn-xs botonVerde" href="PrintJournal.php?'.$liga.'" title="Imprimir Poliza" target="_blank"></a>';
        // }

        if ($myrow ['statusid'] != '0' && $myrow ['statusid'] != '6' && $myrow ['statusid'] != '7') {
            $seleccionar = '<input type="checkbox" id="checkbox_'.$myrow ['transno'].'" name="checkbox_'.$myrow ['transno'].'" title="Seleccionar" value="'.$myrow ['statusid'].'" onchange="fnValidarProcesoCambiarEstatus()" />';
        }

        $urlGeneral = "&transno=>" . $myrow['transno'];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $liga= "URL=" . $url;
        $operacion = '<a type="button" id="btnAbrirAutorizarAdecuacion_'.$myrow['transno'].'" name="btnAbrirAutorizarAdecuacion_'.$myrow['transno'].'" href="GLBudgetsByTagV2.php?'.$liga.'" title="Detalle Adecuación" style="color: blue;">'.$myrow ['transno'].'</a>'; // target="_blank"

        $urlGeneral = "&transno=>" . $myrow['transno'] . "&type=>" . $myrow['type'];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $liga= "URL=" . $url;
        $impresion = '<a type="button" id="btnImprimir'.$myrow['transno'].'" name="btnImprimir'.$myrow['transno'].'" href="impresionAdecuacion.php?'.$liga.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

        // Obtener Usuario de Captura
        $SQL = "SELECT
        www_users.realname, DATE_FORMAT(chartdetailsbudgetlog.fecha_captura, '%d-%m-%Y') as fecha_captura
        FROM chartdetailsbudgetlog
        LEFT JOIN www_users ON www_users.userid = chartdetailsbudgetlog.userid
        WHERE chartdetailsbudgetlog.type = '".$myrow['type']."'
        AND chartdetailsbudgetlog.transno = '".$myrow['transno']."'
        ORDER BY chartdetailsbudgetlog.idmov asc
        LIMIT 1";
        $ErrMsg = "No se obtuvieron los botones para el proceso";
        $TransResultUsuario = DB_query($SQL, $db, $ErrMsg);
        while ($myrowUsuario = DB_fetch_array($TransResultUsuario)) {
            $myrow ['realname'] = $myrowUsuario['realname'];
            $myrow ['fecha_captura'] = $myrowUsuario['fecha_captura'];
        }

        $info[] = array(
            'sel' => $seleccionar,
            'id1' =>false,
            'type' => $myrow ['type'],
            'transno' => $myrow ['transno'],
            'operacion' => $operacion,
            'totalReduccion' => number_format(($myrow ['totalReduccion'] != "" ? abs($myrow ['totalReduccion']) : 0), $_SESSION['DecimalPlaces'], '.', ''),
            'totalAmpliacion' => number_format(($myrow ['totalAmpliacion'] != "" ? abs($myrow ['totalAmpliacion']) : 0), $_SESSION['DecimalPlaces'], '.', ''),
            'statusid' => $myrow ['statusid'],
            'reglasValidadas' => $myrow ['sn_reglas_validadas'],
            'statusname' => $myrow ['statusname'],
            'userid' => $myrow ['userid'],
            'realname' => $myrow ['realname'],
            'fecha_captura' => $myrow ['fecha_captura'],
            'nameAdecuacion' => $myrow ['nameAdecuacion'],
            'nameTipoSolicitud' => $myrow ['nameTipoSolicitud'],
            'imprimir' => $impresion
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    // $columnasNombres .= "{ name: 'sel', type: 'string' },";
    $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'type', type: 'string' },";
    $columnasNombres .= "{ name: 'transno', type: 'string' },";
    $columnasNombres .= "{ name: 'operacion', type: 'string' },";
    $columnasNombres .= "{ name: 'totalReduccion', type: 'float' },";
    $columnasNombres .= "{ name: 'totalAmpliacion', type: 'float' },";
    $columnasNombres .= "{ name: 'statusid', type: 'string' },";
    $columnasNombres .= "{ name: 'statusname', type: 'string' },";
    $columnasNombres .= "{ name: 'userid', type: 'string' },";
    $columnasNombres .= "{ name: 'realname', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    //$columnasNombres .= "{ name: 'folio', type: 'string' },";
    $columnasNombres .= "{ name: 'nameAdecuacion', type: 'string' },";
    $columnasNombres .= "{ name: 'nameTipoSolicitud', type: 'string' },";
    $columnasNombres .= "{ name: 'imprimir', type: 'string' },";
    $columnasNombres .= "{ name: 'reglasValidadas', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    // $columnasNombresGrid .= " { text: 'Sel', datafield: 'sel', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: '', datafield: 'id1', width: '3%', editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    $columnasNombresGrid .= " { text: 'type', datafield: 'type', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'transno', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'operacion', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Reducción', datafield: 'totalReduccion', width: '12%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ampliación', datafield: 'totalAmpliacion', width: '12%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus No.', datafield: 'statusid', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'statusname', width: '14%', editable: false, cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'userid', datafield: 'userid', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Usuario', datafield: 'realname', width: '15%', editable: false, cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'F. Captura', datafield: 'fecha_captura', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Folio', datafield: 'folio', width: '10%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clase', datafield: 'nameAdecuacion', width: '12%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo Sol', datafield: 'nameTipoSolicitud', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'imprimir', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'reglasValidadas', datafield: 'reglasValidadas', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
    $columnasNombresGrid .= "]";

    $nombreExcel = "Adecuaciones_".date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
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
        tb_botones_status.functionid,
        tb_botones_status.statusname,
        chartdetailsbudgetlog.* 
        FROM chartdetailsbudgetlog
        LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
        LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
        JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
        WHERE
        chartdetailsbudgetlog.type = '".$datosClave ['type']."'
        AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."'";
        // AND chartdetailsbudgetlog.qty < 0
        $ErrMsg = "No se obtuvieron los registros del No. de Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $infoClaves = array();
        $estatusActual = '';
        $nombreEstatus = '';
        while ($myrow = DB_fetch_array($TransResult)) {
            $infoClaves[] = array(
                'accountcode' => $myrow ['cvefrom']
            );
            if ($myrow['qty'] < 0) {
                $estatusActual = $myrow['estatus'];
                $nombreEstatus = $myrow['statusname'];
                $disponible = fnInfoPresupuesto($db, $myrow['cvefrom'], $myrow['period']);
                foreach ($disponible as $dispo) {
                    //echo "\n ".$myrow['mesName'].": ".$dispo[$myrow['mesName']];
                    //echo "\n solicitado: ".$myrow['qty'];
                    $dispo[$myrow['mesName']] = $dispo[$myrow['mesName']] + abs($myrow['qty']); // Se suma ya que es redución en tramite para ver que alcance el presupuesto
                    if ($dispo[$myrow['mesName']] < abs($myrow['qty'])) {
                        $result = false;
                        $actualizar = 0;
                        $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No. Captura '.$datosClave ['transno'].' la Clave Presupuestal '.$myrow['cvefrom'].' en '.$myrow['mesName'].' el disponible es '.$dispo[$myrow['mesName']].' y se solicita Reducción de '.abs($myrow['qty']).' </p>';
                    }
                }
            }
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
            AND tb_botones_status.statusid >= '".$estatusActual."' 
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

        if ($errorRechazo == '1') {
            // Si esta rechazando y no tiene permiso a ese estatus
            $result = false;
            $actualizar = 0;
            $mensajeErrores .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        }

        // Validar periodos contables
        $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
        if (!$respuesta['result']) {
            $result = false;
            $actualizar = 0;
            $mensajeErrores .= $respuesta['mensaje'];
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
        $SQL = "SELECT chartdetailsbudgetlog.estatus, tb_botones_status.statusname
        FROM chartdetailsbudgetlog 
        JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
        WHERE chartdetailsbudgetlog.type = '".$datosClave ['type']."' 
        AND chartdetailsbudgetlog.transno = '".$datosClave ['transno']."' LIMIT 1";
        $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $estatusActual = "";
        $nombreEstatus = '';
        while ($myrow = DB_fetch_array($TransResult)) {
            $estatusActual = $myrow['estatus'];
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
            FROM tb_botones_status 
            WHERE 
            tb_botones_status.sn_funcion_id in (".$funcion.") 
            AND tb_botones_status.statusid < 90 
            AND tb_botones_status.statusid >= '".$estatusActual."' 
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

        if ($errorRechazo == '1') {
            // Si esta rechazando y no tiene permiso a ese estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra ('.$nombreEstatus.')</p>';
        } else if ($estatusActual == '7' && !isset($_POST['sStatus'])) {
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra</p>';
        } else {
            if ($statusid == '99') {
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

            if ($statusid == '0') {
                //Borrar Registros en 0
                $SQL = "DELETE FROM chartdetailsbudgetlog WHERE (qty = '0' OR qty = '-0') AND type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."'";
                $ErrMsg = "No se eliminaron Registros en 0";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                
                // Log del presupuesto
                $SQL = "SELECT type, transno, tagref, cvefrom, period, qty, nu_tipo_movimiento, partida_esp, description, sn_disponible, estatus
                FROM chartdetailsbudgetlog WHERE type = '".$datosClave ['type']."' AND transno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $agrego = fnInsertPresupuestoLog($db, $myrow['type'], $myrow['transno'], $myrow['tagref'], $myrow['cvefrom'], $myrow['period'], $myrow['qty'] * -1, $myrow['nu_tipo_movimiento'], $myrow['partida_esp'], $myrow['description'], $myrow['sn_disponible'], $myrow['estatus']);
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
            //$mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para el folio '.$datosClave['transno'].'</p>';
        }

        $info[] = array(
            'transno' => $datosClave ['transno']
        );
    }

    // Obtener si genera layout el estatus
    $generaLayout = 0;
    $tipoLayout = 1;
    $SQL = "SELECT nu_generar_layout, nu_tipo_layout FROM tb_botones_status WHERE statusid = '".$statusid."'AND sn_funcion_id = '".$funcion."'";
    $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $estatusActual = "";
    while ($myrow = DB_fetch_array($TransResult)) {
        $generaLayout = $myrow['nu_generar_layout'];
        $tipoLayout = $myrow['nu_tipo_layout'];
    }

    if ($permisoUsarLayoutGeneral == '0') {
        $generaLayout = 0;
    }

    $reponse['estatus'] = $statusid;
    $reponse['mensaje'] = $mensajeInfo;
    $reponse['datos'] = $info;
    $reponse['generaLayout'] = $generaLayout;
    $reponse['tipoLayout'] = $tipoLayout;

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
            tb_botones_status.clases,
            tb_botones_status.sn_estatus_siguiente
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
            ORDER BY tb_botones_status.statusid ASC, tb_botones_status.statusname ASC
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($myrow ['sn_estatus_siguiente'] == '5' && $permisoUsarLayoutGeneral == '0') {
            $myrow ['sn_estatus_siguiente'] = '6';
        }
        $mostrarBoton = 1;

        if ($myrow['statusid'] == '5' && $permisoUsarLayoutGeneral == '0') {
            // Si es boton de generar layout y no tiene usuario dgpop quitar boton
            $mostrarBoton = 0;
        }

        if ($mostrarBoton == 1) {
            $info[] = array(
                'statusid' => $myrow ['sn_estatus_siguiente'],
                'statusname' => $myrow ['statusname'],
                'namebutton' => $myrow ['namebutton'],
                'functionid' => $myrow ['functionid'],
                'clases' => $myrow ['clases']
            );
        }
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
    tb_botones_status.sn_funcion_id = '2263'
    AND adecuacionPresupuestal = '1' AND statusid < '90' 
    AND statusid != 2
    AND statusid != 5
    ORDER BY texto ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != 0 && !empty(trim($legalid))) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
            FROM sec_unegsxuser u,tags t 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' " . $sqlWhere . "
            ORDER BY t.tagref";
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
