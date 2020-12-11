<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category Panel
 * @package ap_grp
 * @author Eduardo López Morales <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Modelos para las operaciones del panel de adecuaciones presupuestales
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
$funcion=371;
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

if ($option == 'actualizarEstatus') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $mensajeInfo = '';
    $info = array();

    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $SQL = "SELECT gltrans.posted, gltrans.tag, gltrans.ln_ue FROM gltrans WHERE gltrans.type = '".$datosClave ['type']."' and gltrans.typeno = '".$datosClave ['transno']."' LIMIT 1";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $estatusActual = "";
        $tagref = "";
        $ln_ue = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            $estatusActual = $myrow['posted'];
            $tagref = $myrow['tag'];
            $ln_ue = $myrow['ln_ue'];
        }

        if ($estatusActual == '1' || $estatusActual == '5') {
            // Si esta cancelado o autorizado no cambiar estatus
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].' no es posible en el estatus que se encuentra</p>';
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
                $SQL = "UPDATE gltrans SET posted = '".$statusid."' WHERE type = '".$datosClave ['type']."' AND typeno = '".$datosClave ['transno']."' ";
                $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }

            if ($statusid == 1) {
                // Es autorizar
                // Folio de la poliza por unidad ejecutora
                $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $ln_ue, $datosClave ['type']);

                $SQL = "UPDATE gltrans SET nu_folio_ue = '".$folioPolizaUe."' WHERE type = '".$datosClave ['type']."' AND typeno = '".$datosClave ['transno']."' ";
                $result = DB_query($SQL, $db);
            }

            // Mensaje Configurado
            $SQL = "SELECT 
            tb_botones_status.sn_mensaje_opcional, tb_botones_status.sn_mensaje_opcional2
            FROM tb_botones_status
            WHERE tb_botones_status.sn_funcion_id = '".$funcion."'
            AND tb_botones_status.statusid = '".$statusid."'";
            $ErrMsg = "No se obtuvo información para mostrar mensaje del proceso ";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $msjConfigurado = "";
            while ($myrow = DB_fetch_array($TransResult)) {
                if ($statusid == '99') {
                    // Es rechazar
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

if ($option == 'obtenerPolizas') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $tipoPoliza = $_POST['tipoPoliza'];
    $noPoliza = $_POST['noPoliza'];
    $noPolizaFolio = $_POST['noPolizaFolio'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    $ue = $_POST['ue'];

    $sqlWhere = "";

    // separar la seleccion multiple de las dependecias
    $datos = "";
    foreach ($legalid as $key) {
        if (empty($datos)) {
            $datos .= "'".$key."'";
        } else {
            $datos .= ", '".$key."'";
        }
    }
    if ($datos != '') {
        $sqlWhere .= " AND tags.legalid IN (".$datos.") ";
    }

    // separar la seleccion multiple de las unidades responsables
    $datos = "";
    foreach ($tagref as $key) {
        if (empty($datos)) {
            $datos .= "'".$key."'";
        } else {
            $datos .= ", '".$key."'";
        }
    }
    if ($datos != '') {
        $sqlWhere .= " AND gltrans.tag IN (".$datos.") ";
    }

    // separar la seleccion multiple de las unidades ejecutoras
    $datosUE = "";
    foreach ($ue as $key) {
        if (empty($datosUE)) {
            $datosUE .= "'".$key."'";
        } else {
            $datosUE .= ", '".$key."'";
        }
    }
    if ($datosUE != '') {
        $sqlWhere .= " AND gltrans.ln_ue IN (".$datosUE.") ";
    }

    // separar la seleccion multiple de tipos de poliza
    $datos = "";
    foreach ($tipoPoliza as $key) {
        if (empty($datos)) {
            $datos .= "'".$key."'";
        } else {
            $datos .= ", '".$key."'";
        }
    }
    if ($datos != '') {
        $sqlWhere .= " AND gltrans.type IN (".$datos.") ";
    }

    if (trim($noPoliza) != '') {
        // $sqlWhere .= " AND gltrans.typeno like '%".$noPoliza."%' ";
        $sqlWhere .= " AND gltrans.typeno = '".$noPoliza."' ";
    }

    if (trim($noPolizaFolio) != '') {
        // $sqlWhere .= " AND gltrans.typeno like '%".$noPolizaFolio."%' ";
        $sqlWhere .= " AND gltrans.nu_folio_ue = '".$noPolizaFolio."' ";
    }

    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND gltrans.trandate between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND gltrans.trandate >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND gltrans.trandate <= '".$fechaHasta." 23:59:59' ";
    }

    $info = array();
    $SQL = "SELECT
    gltrans.type,
    gltrans.typeno,
    gltrans.tag,
    DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') as trandate,
    gltrans.periodno,
    sum(CASE WHEN gltrans.amount > 0 THEN gltrans.amount ELSE 0 END) AS amount,
    tags.tagdescription,
    day(gltrans.trandate) AS daytrandate,
    month(gltrans.trandate) AS monthtrandate,
    year(gltrans.trandate) AS yeartrandate,
    legalbusinessunit.taxid,
    legalbusinessunit.address5,
    systypescat.typename,
    tb_botones_status.statusname as estatusname,
    gltrans.posted,
    gltrans.nu_folio_ue as folioUe,
    tb_cat_poliza_visual.ln_nombre as polizaUe,
    gltrans.ln_ue
    FROM tags,
    sec_unegsxuser,
    gltrans,
    legalbusinessunit,
    systypescat,
    tb_botones_status,
    tb_cat_poliza_visual,
    tb_sec_users_ue
    WHERE gltrans.tag = tags.tagref
    AND sec_unegsxuser.tagref = tags.tagref
    AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
    AND legalbusinessunit.legalid = tags.legalid
    AND systypescat.typeid = gltrans.type 
    AND tb_botones_status.sn_funcion_id = '".$funcion."'
    AND tb_botones_status.statusid = gltrans.posted
    AND tb_cat_poliza_visual.id = systypescat.nu_poliza_visual
    AND tb_sec_users_ue.tagref = gltrans.tag 
    AND tb_sec_users_ue.ue = gltrans.ln_ue 
    AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    ".$sqlWhere."
    GROUP BY gltrans.type,
    gltrans.typeno,
    gltrans.tag,
    gltrans.trandate,
    gltrans.periodno,
    tags.tagdescription,
    legalbusinessunit.taxid,
    legalbusinessunit.address5,
    systypescat.typename,
    estatusname,
    gltrans.posted,
    gltrans.ln_ue,
    folioUe,
    polizaUe
    ORDER BY gltrans.trandate,
    gltrans.type,
    gltrans.typeno";
    // Se comenta ya que la poliza del
    // AND CASE WHEN gltrans.type ='281' THEN gltrans.account NOT LIKE '8.%' ELSE 1=1 END 
    //echo "<pre>".$SQL;
    // exit();
    $ErrMsg = "No se obtuvo información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $imprimir = "";
        $seleccionar = "";
        $opciones = $myrow ['typeno'];
        if ($myrow['posted'] != '1' && $myrow['posted'] != '5' && ($myrow['type'] == '0' || $myrow['type'] == '282' || $myrow['type'] == '287' || $myrow['type'] == '120')) {
            // Que no este autorizado ni cancelado
            $seleccionar = '<input type="checkbox" id="checkbox_'.$myrow ['typeno'].'" name="checkbox_'.$myrow ['typeno'].'" title="Seleccionar" value="'.$myrow ['posted'].'" onchange="fnValidarProcesoCambiarEstatus()" />';

            $url = "&NewJournal=>Yes&typeno=>".$myrow['typeno']."&type=>".$myrow['type']."&tag=>".$myrow['tag']."&ue=>".$myrow['ln_ue'];
            $enc = new Encryption;
            $url = $enc->encode($url);
            $liga= "URL=" . $url;
            // $liga = "&NewJournal=Yes&typeno=".$myrow['typeno']."&type=".$myrow['type']."&tag=".$myrow['tag'];
            $opciones = "<a id='idBtnAltaFacturaOC' target='_blank' href='GLJournal.php?".$liga."' style='color: blue;'><span class=''></span> ".$myrow ['typeno']."</a>";
        }

        $url = "&FromCust=>1&ToCust=>1&PrintPDF=>Yes&type=>" . $myrow['type'] . "&TransNo=>" . $myrow['typeno'] . "&periodo=>" . $myrow['periodno'] . "&trandate=>" . $myrow['trandate'] . "&folioUe=>" . $myrow['folioUe'] . "&ue=>" . $myrow['ln_ue'];
        $enc = new Encryption;
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        $imprimir = "<a target='_blank' href='PrintJournal.php?" . $liga . "'><span class='glyphicon glyphicon glyphicon-print'></span></a>";
        
        $info[] = array(
            'sel' => $seleccionar,
            'type' => $myrow ['type'],
            'ur' => $myrow ['tag'],
            'ue' => $myrow ['ln_ue'],
            'folioUe' => $myrow ['folioUe'],
            'polizaUe' => $myrow ['polizaUe'],
            'transno' => $myrow ['typeno'],
            'transnoLink' => $opciones,
            'typename' => $myrow ['typename'],
            'tagname' => $myrow ['tag'].' - '.$myrow ['tagdescription'],
            'statusid' => $myrow['posted'],
            'estatusname' => $myrow ['estatusname'],
            'trandate' => $myrow ['trandate'],
            'totalMonto' => ($myrow ['amount']),
            'totalMonto2' => ($myrow ['amount']),
            'imprimir' => $imprimir
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'sel', type: 'string' },";
    $columnasNombres .= "{ name: 'type', type: 'string' },";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'folioUe', type: 'string' },";
    $columnasNombres .= "{ name: 'polizaUe', type: 'string' },";
    $columnasNombres .= "{ name: 'transno', type: 'string' },";
    $columnasNombres .= "{ name: 'transnoLink', type: 'string' },";
    $columnasNombres .= "{ name: 'typename', type: 'string' },";
    $columnasNombres .= "{ name: 'tagname', type: 'string' },";
    $columnasNombres .= "{ name: 'estatusname', type: 'string' },";
    $columnasNombres .= "{ name: 'trandate', type: 'string' },";
    $columnasNombres .= "{ name: 'totalMonto', type: 'string' },";
    $columnasNombres .= "{ name: 'totalMonto2', type: 'number' },";
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
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'type', width: '8%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'folioUe', width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo Póliza', datafield: 'polizaUe', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'No. Operación', datafield: 'transno', width: '9%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'No. Operación', datafield: 'transnoLink', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo Operación', datafield: 'typename', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Unidad Responsable', datafield: 'tagname', width: '30%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estatusname', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha', datafield: 'trandate', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Total', datafield: 'totalMonto', width: '12%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true },";
    $columnasNombresGrid .= " { text: 'Total', datafield: 'totalMonto2', width: '12%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'imprimir', width: '5%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    //$nombreExcel = "Adecuaciones_".date('dmY');
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
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

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
