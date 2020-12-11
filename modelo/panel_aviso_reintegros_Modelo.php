<?php
/**
 * Panel Reintegros
 *
 * @category Panel
 * @package ap_grp
 * @author Jose Raul Lopez Vazquez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/07/2018
 * Fecha Modificación: 21/07/2018
 * Modelo para el panel Aviso de Reintegros
 */

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2412;
$typeData = 293;
include $PathPrefix."includes/SecurityUrl.php";
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

/*ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);*/

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

if(isset($_GET['option'])) {

    $option = $_GET['option'];

    if ($option == 'mostrarSelectEstatus') {
        $info = array();
        $SQL = "SELECT 
    distinct statusid as value, sn_nombre_secundario as texto, statusid
    FROM tb_botones_status 
    WHERE 
    tb_botones_status.sn_funcion_id = '" . $funcion . "'
    AND sn_flag_disponible = '1' AND statusid < '90' 
    ORDER BY texto ASC";
        $ErrMsg = "No se obtuvo los Estatus";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array('value' => $myrow ['value'], 'texto' => $myrow ['texto']);
        }

        $Status = array('datos' => $info);
        $result = true;

        $dataObjEstatus = array('Status' => $Status, 'result' => $result);
        echo json_encode($dataObjEstatus);

    }


    if ($option == 'tipodeReintegro') {

        $info = array();
        $SQL = "SELECT id as value, name as texto, status FROM tb_cat_refunds WHERE status = '1' ORDER BY value ASC";
        $ErrMsg = "No se obtuvo los Estatus";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array('value' => $myrow ['value'], 'texto' => $myrow ['texto']);
        }

        $Status = array('datos' => $info);
        $result = true;

        $dataObjTipoReintegro = array('Status' => $Status, 'result' => $result);
        echo json_encode($dataObjTipoReintegro);

    }


    if($option == 'typePayment'){

        $info = array();
        $SQL = "SELECT typeid as value, CONCAT(typeid, ' - ', typename) as texto FROM systypescat WHERE nu_tesoreria_pagos = 1 ORDER BY typeid ASC";
        $ErrMsg = "No se obtuvieron los tipos de pagos de tesoreria";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
        }

        $contenido = array('datos' => $info);
        $result = true;

        $dataObjTipoPago = array('datatype' => $contenido, 'results' => $result);

        echo json_encode($dataObjTipoPago);

    }


    if ($option == 'ResultsSearch') {

        $Ur = $_GET['ur'];
        $Ue = $_GET['ue'];
        $StatusRefunds = $_GET['statusRefunds'];
        $FolioRefunds = mysqli_real_escape_string($db, $_GET['folioRefunds']);
        $TypeRefunds = $_GET['typeRefunds'];
        $StarDate = $_GET['starDate'];
        $EndDate = $_GET['endDate'];
        $Tpayment = $_GET['typePayment'];

        $sqlWhere = "";
        $columnasNombres = '';
        $columnasNombresGrid = '';

        if ($Ur != '') {
            $sqlWhere .= " AND tb_refunds_notice.ur_id IN (" . $Ur . ")";
        }

        if ($Ue != '') {
            $sqlWhere .= " AND tb_refunds_notice.ue_id IN (" . $Ue . ")";
        }

        if ($FolioRefunds != '') {
            $sqlWhere .= " AND tb_refunds_notice.id = " . $FolioRefunds . "";
        }

        if ($StatusRefunds != '') {
            $sqlWhere .= " AND tb_refunds_notice.status_refund IN (" . $StatusRefunds . ")";
        }

        if ($TypeRefunds != '') {
            $sqlWhere .= " AND tb_refunds_notice.refund_id IN (" . $TypeRefunds . ")";
        }

        if($Tpayment != ''){
            $sqlWhere .= " AND tb_refunds_notice.type_payment IN (" . $Tpayment . ")";
        }

        if (!empty($StarDate) && !empty($EndDate)) {
            $StarDate = date_create($StarDate);
            $StarDate = date_format($StarDate, 'Y-m-d');

            $EndDate = date_create($EndDate);
            $EndDate = date_format($EndDate, 'Y-m-d');

            $sqlWhere .= " AND tb_refunds_notice.issue_date between '" . $StarDate . " 00:00:00' AND '" . $EndDate . " 23:59:59'";

        } elseif (!empty($StarDate)) {
            $StarDate = date_create($StarDate);
            $StarDate = date_format($StarDate, 'Y-m-d');

            $sqlWhere .= " AND tb_refunds_notice.issue_date >= '" . $StarDate . " 00:00:00'";

        } elseif (!empty($EndDate)) {
            $EndDate = date_create($EndDate);
            $EndDate = date_format($EndDate, 'Y-m-d');

            $sqlWhere .= " AND tb_refunds_notice.issue_date <= '" . $EndDate . " 23:59:59'";
        }

     //   print_r($sqlWhere);
        $info = array();

        $querySQL = "SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.ue_id, tb_refunds_notice.issue_date,
                            tb_refunds_notice.refund_id, tb_refunds_notice.folio_viatics,
                            tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.justification, tb_refunds_notice.status_refund,
                            SUM(chartdetailsbudgetlog.qty) AS total, chartdetailsbudgetlog.description, chartdetailsbudgetlog.transno,
                            chartdetailsbudgetlog.type, chartdetailsbudgetlog.period, tb_cat_unidades_ejecutoras.ue, tb_cat_refunds.name, tb_botones_status.statusname
                            FROM tb_refunds_notice 
                            JOIN chartdetailsbudgetlog ON tb_refunds_notice.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 293
                            LEFT JOIN tb_cat_unidades_ejecutoras ON tb_refunds_notice.ue_id = tb_cat_unidades_ejecutoras.ue
                            JOIN tb_cat_refunds ON tb_refunds_notice.refund_id = tb_cat_refunds.id
                            LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_refunds_notice.status_refund AND tb_botones_status.sn_funcion_id = " . $funcion . "                           
                            JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_refunds_notice.ur_id AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
                            LEFT JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tb_refunds_notice.ur_id AND tb_sec_users_ue.ue = tb_refunds_notice.ue_id AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'                         
                            WHERE chartdetailsbudgetlog.type = 293 " . $sqlWhere . " AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                            GROUP BY tb_refunds_notice.id 
                            ORDER BY tb_refunds_notice.id DESC ";

        $ErrMsg = "Error Al Consultar la base de Datos";
        $TransResult = DB_query($querySQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {

            if ($myrow ['status_refund'] != '0' && $myrow ['status_refund'] != '4') {
                // Si no es 0 = Cancelado, 4 = Autorizado
                $seleccionar = '<input type="checkbox" id="checkbox_' . $myrow ['id'] . '" name="checkbox_' . $myrow ['id'] . '" title="Seleccionar" value="' . $myrow ['status_refund'] . '" onchange="fnValidarProcesoCambiarEstatus()" />';
            }

            $updt = 1;

            $luser = $_SESSION['UserID'];

            $urlGeneral = "&transno=>" . $myrow['id'] . "&type=>" . $typeData . "&upd=>" . $updt . "&typeUser=>" . $luser;

            $urlImpresion = "&transno=>" . $myrow['id'] . "&type=>" . $typeData . "&Total=>" . $myrow['total'] . "&RMP=>" . $myrow['refund_id'] . "&status=>".$myrow['statusname'];

            $enc = new Encryption;
            $encImp = new Encryption;

            $url = $enc->encode($urlGeneral);
            $urlImp = $encImp->encode($urlImpresion);

            $liga = "URL=" . $url;
            $ligaImp = "URL=" . $urlImp;

            $operacion = '<a type="button" id="btnAbrirCapturaReintegros_'.$myrow['id'].'" name="btnAbrirCapturaReintegros_'.$myrow['id'].'" href="captura_aviso_reintegros.php?' . $liga . '" title="Detalle Captura Reintegro" style="color: blue;">' . $myrow ['id'] . '</a>'; // target="_blank"

            $impresion = '<a type="button" id="btnImprimir'.$myrow['id'].'" name="btnImprimir'.$myrow['id'].'" href="reporte_reintegro.php?'.$ligaImp.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

            $info[] = array(

                'idCheck' => false,
                'ur' => $myrow['ur_id'],
                'ue' => $myrow['ue'],
                'fecha_captura' => date('d-m-Y', strtotime($myrow['issue_date'])),
                //'folio' => $myrow['id'],
                'folio' => $operacion,
                'folioExcel' => $myrow['id'],
                'tipo' => $myrow['name'],
                'status' => $myrow['statusname'],
                'statusUp' => $myrow['status_refund'],
                'justificacion' => $myrow['justification'],
                'total' => ($myrow['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
                'totalExcel' => ($myrow['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
                'idrefunds' => $myrow['id'],
                'period' => $myrow['period'],
                'folioTransfer' => $myrow['folio_invoice_transfer'],
                'type_refund' => $myrow['refund_id'],
                'impresion' => $impresion
            );
        }


        // Columnas para el GRID
        $columnasNombres .= "[";
        $columnasNombres .= "{ name: 'idCheck', type: 'bool'},";
        $columnasNombres .= "{ name: 'ur', type: 'string' },";
        $columnasNombres .= "{ name: 'ue', type: 'string' },";
        $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
        $columnasNombres .= "{ name: 'folio', type: 'string' },";
        $columnasNombres .= "{ name: 'folioExcel', type: 'string' },";
        $columnasNombres .= "{ name: 'tipo', type: 'string' },";
        $columnasNombres .= "{ name: 'status', type: 'string' },";
        $columnasNombres .= "{ name: 'statusUp', type: 'string' },";
        $columnasNombres .= "{ name: 'justificacion', type: 'string' },";
        $columnasNombres .= "{ name: 'total', type: 'float' },";
        $columnasNombres .= "{ name: 'totalExcel', type: 'string' },";
        $columnasNombres .= "{ name: 'idrefunds', type: 'string' },";
        $columnasNombres .= "{ name: 'period', type: 'string' },";
        $columnasNombres .= "{ name: 'folioTransfer', type: 'string' },";
        $columnasNombres .= "{ name: 'type_refund', type: 'string' },";
        $columnasNombres .= "{ name: 'impresion', type: 'string' }";
        $columnasNombres .= "]";

        // Columnas para el GRID
        $colResumenTotal = ", aggregates: [{'<b>Total</b>' :" .
            "function (aggregatedValue, currentValue) {" .
            "var total = currentValue;" .
            "return aggregatedValue + total;" .
            "}" .
            "}] ";

        $columnasNombresGrid .= "[";
        $columnasNombresGrid .= " { text: '', datafield: 'idCheck', width: '3%', editable: true, editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
        $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Folio', datafield: 'folioExcel', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'Tipo', datafield: 'tipo', width: '21%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Estatus', datafield: 'status', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'EstatusUP', datafield: 'statusUp', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'Justificación', datafield: 'justificacion', width: '25%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total', width: '8%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false" . $colResumenTotal . " },";
        $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'totalExcel', width: '8%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true},";
        $columnasNombresGrid .= " { text: 'Reintegro ID', datafield: 'idrefunds', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'Periodo', datafield: 'period', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'No Transfer', datafield: 'folioTransfer', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'tipo_reintegro', datafield: 'type_refund', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'Impresion', datafield: 'impresion', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false }";
        $columnasNombresGrid .= "]";


        $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)) . '_' . date('dmY');

        $result = true;
        $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel, 'ur' => $Ur, 'ue' => $Ue, 'statusRefunds' => $StatusRefunds, 'folioRefunds' => $FolioRefunds, 'typeRefunds' => $TypeRefunds, 'starDate' => $StarDate, 'endDate' => $EndDate, 'result' => $result, 'query' => $querySQL);

        $dataObjSearch = array('contenido' => $contenido);
        echo json_encode($dataObjSearch);


    }

    if ($option == 'obtenerBotones') {

        $info = array();
       /* $SQL = "SELECT
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
            tb_botones_status.functionid IN (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid)
            ) 
            ORDER BY tb_botones_status.functionid ASC";*/


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
                WHERE (tb_botones_status.sn_funcion_id = '".$funcion."')
                AND (tb_botones_status.sn_flag_disponible = 1)
                AND (tb_botones_status.sn_panel_adecuacion_presupuestal = 1)
                AND (tb_botones_status.functionid = sec_funxprofile.functionid OR tb_botones_status.functionid IN (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."')) 
                 ORDER BY tb_botones_status.statusid ASC";


        // //ORDER BY tb_botones_status.functionid ASC

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

        $dataObj = array('contenido' => $contenido, 'result' => $result, 'ErrMsg' => $ErrMsg);
        echo json_encode($dataObj);
    }


}


if(isset($_POST['optionUpdate'])){

    if($_POST['optionUpdate'] == 'UpdateStatusRefunds'){

        $dtaJson = $_POST['dataJsonNoCapturaSeleccionados'];
        $idStatus = mysqli_real_escape_string($db, $_POST['statusid']);
        $funcionRegistro =  mysqli_real_escape_string($db, $_POST['tipoFuncion']);

//print_r($funcionRegistro);
//exit();
        try{

            $arraytipoEstatus = array();

            for($s=0;$s<count($dtaJson);$s++){

                    if($dtaJson[$s]['typeR'] == 1){
                        // CODIGO MINISTRADO ESTATUS

                        if($idStatus == 0){

                              if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4){

                                  if($dtaJson[$s]['statusUp'] == 0 ){
                                      $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].'  se encuentra Cancelado';
                                      $resultData = array('msg'=>$message,'status'=>'error');
                                      array_push($arraytipoEstatus,$resultData);
                                      echo json_encode($arraytipoEstatus);
                                      exit();
                                  }else{
                                      if($dtaJson[$s]['statusUp'] == 4){
                                          $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                          $resultData = array('msg'=>$message,'status'=>'error');
                                          array_push($arraytipoEstatus,$resultData);
                                          echo json_encode($arraytipoEstatus);
                                          exit();

                                      }
                                  }

                              }else{


                                  $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                  $ErrMsg = "No se pudo actualizar la información";
                                  $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                  if($TransResult == true){

                                     $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                      $ErrMsg = "No se pudo actualizar la información";
                                      $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                  }


                              }

                            $resultData = array('msg'=>'Reintegro Cancelado Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');

                            array_push($arraytipoEstatus,$resultData);

                           // echo json_encode($resultData);

                        }else{
                            if($idStatus == 2){

                                if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                    if($dtaJson[$s]['statusUp'] == 0 ){
                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                        $resultData = array('msg'=>$message,'status'=>'error');
                                        array_push($arraytipoEstatus,$resultData);
                                        echo json_encode($arraytipoEstatus);
                                        exit();
                                    }else{
                                        if($dtaJson[$s]['statusUp'] == 4){
                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                            $resultData = array('msg'=>$message,'status'=>'error');
                                            array_push($arraytipoEstatus,$resultData);
                                            echo json_encode($arraytipoEstatus);
                                            exit();

                                        }else{
                                            if($dtaJson[$s]['statusUp'] == $idStatus){
                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Validar';
                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                array_push($arraytipoEstatus,$resultData);
                                                echo json_encode($arraytipoEstatus);
                                                exit();
                                            }
                                        }
                                    }
                                }else{

                                    $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                    $ErrMsg = "No se pudo actualizar la información";
                                    $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                    if($TransResult == true){

                                        $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                        $ErrMsg = "No se pudo actualizar la información";
                                        $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                    }
                                }

                                $resultData = array('msg'=>'Cambio de estatus de Reintegro por validar Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                array_push($arraytipoEstatus,$resultData);

                            }else{
                                if($idStatus == 3){

                                    if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                        if($dtaJson[$s]['statusUp'] == 0 ){
                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                            $resultData = array('msg'=>$message,'status'=>'error');
                                            array_push($arraytipoEstatus,$resultData);
                                            echo json_encode($arraytipoEstatus);
                                            exit();
                                        }else{
                                            if($dtaJson[$s]['statusUp'] == 4){
                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                array_push($arraytipoEstatus,$resultData);
                                                echo json_encode($arraytipoEstatus);
                                                exit();

                                            }else{
                                                if($dtaJson[$s]['statusUp'] == $idStatus){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Autorizar';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();
                                                }
                                            }
                                        }
                                    }else{


                                            $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                            $ErrMsg = "No se pudo actualizar la información";
                                            $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                            if($TransResult == true){

                                                $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                $ErrMsg = "No se pudo actualizar la información";
                                                $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                            }



                                      /*  $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW()
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                        $ErrMsg = "No se pudo actualizar la información";
                                        $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                        if($TransResult == true){

                                            $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                            $ErrMsg = "No se pudo actualizar la información";
                                            $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                        }*/
                                    }

                                    $resultData = array('msg'=>'Cambio de estatus de Reintegro por autorizar Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                    array_push($arraytipoEstatus,$resultData);
                                  //  echo json_encode($resultData);


                                }else{
                                    if($idStatus == 99){

                                   /*   $funcionPermisoMod= 0;

                                      $funct = "SELECT tb_refunds_notice.status_refund, tb_refunds_notice.refund_id ,tb_botones_statusSig.functionid,tb_botones_status.sn_estatus_siguiente,tb_botones_status.sn_estatus_anterior FROM tb_refunds_notice
                                                  LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_status.statusid = tb_refunds_notice.status_refund
                                                  LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
                                                  WHERE tb_refunds_notice.id = '".$dtaJson[$s]['idRefunds']."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";

                                        $transResultQQ = DB_query($funct, $db);

                                        while ($myrow = DB_fetch_array($transResultQQ)) {
                                            $funcionPermisoMod = $myrow['functionid'];
                                        }*/


                                    /*   print_r($dtaJson[$s]);
                                        print_r($funcionRegistro);
                                        print_r($funct);
                                        print_r($funcionPermisoMod);
                                        exit();
*/
//|| $funcionRegistro != $funcionPermisoMod
                                        if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4){

                                            if($dtaJson[$s]['statusUp'] == 0 ){
                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                array_push($arraytipoEstatus,$resultData);
                                                echo json_encode($arraytipoEstatus);
                                                exit();
                                            }else{
                                                if($dtaJson[$s]['statusUp'] == 4){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();

                                                }
                                              /*  else{
                                                    if($funcionRegistro != $funcionPermisoMod){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' no se puede Rechazar por su Estatus';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();
                                                    }
                                                }*/
                                            }

                                        }else{

                                            if($dtaJson[$s]['statusUp'] == 2){

                                                    $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 1, cancel_date = NOW() 
                                                                           WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                     $ErrMsg = "No se pudo actualizar la información";
                                                     $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                     if($TransResult == true){

                                                         $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 1 WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";
                                                         $ErrMsg = "No se pudo actualizar la información";
                                                         $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                     }


                                            }else{
                                                if($dtaJson[$s]['statusUp'] == 3){



                                                        $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 2, cancel_date = NOW() 
                                                             WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                        $ErrMsg = "No se pudo actualizar la información";
                                                        $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                        if($TransResult == true){

                                                            $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 2 WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                            $ErrMsg = "No se pudo actualizar la información";
                                                            $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                        }



                                                }else{
                                                    if($dtaJson[$s]['statusUp'] == 5){



                                                            $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 3, cancel_date = NOW() 
                                                                                   WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                            $ErrMsg = "No se pudo actualizar la información";
                                                            $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                            if($TransResult == true){

                                                                $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 3 WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                                $ErrMsg = "No se pudo actualizar la información";
                                                                $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                            }

                                                    }
                                                }
                                            }

                                        }

                                        $resultData = array('msg'=>'Reintegro Rechazado Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                        array_push($arraytipoEstatus,$resultData);
                                      //  echo json_encode($resultData);


                                    }else{
                                        if($idStatus == 5){

                                            if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                                if($dtaJson[$s]['statusUp'] == 0 ){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();
                                                }else{
                                                    if($dtaJson[$s]['statusUp'] == 4){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();

                                                    }else{
                                                        if($dtaJson[$s]['statusUp'] == $idStatus){
                                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Solicitar';
                                                            $resultData = array('msg'=>$message,'status'=>'error');
                                                            array_push($arraytipoEstatus,$resultData);
                                                            echo json_encode($arraytipoEstatus);
                                                            exit();
                                                        }
                                                    }
                                                }
                                            }else{

                                                $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                $ErrMsg = "No se pudo actualizar la información";
                                                $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                if($TransResult == true){

                                                    $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                    $ErrMsg = "No se pudo actualizar la información";
                                                    $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                }

                                            }

                                        }

                                        $resultData = array('msg'=>'Cambio de estatus de Reintegro por Solicitar fue  Correcto con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                        array_push($arraytipoEstatus,$resultData);
                                        //echo json_encode($resultData);
                                    }
                                }
                            }
                        }


                      /*  $queryQTYitems = "SELECT * FROM chartdetailsbudgetlog WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND tagref = '".$dtaJson[$s]['ur']."' AND type = 293 ";

                        $ErrMsg = "No se pudo almacenar la información";
                        $TResult = DB_query($queryQTYitems, $db, $ErrMsg);

                        $totalRow = mysqli_num_rows($TResult);

                        print_r();
                        exit();
*/
                    }else{
                        if($dtaJson[$s]['typeR'] == 2){
                            // CODIGO RADICADO ESTATUS

                            if($idStatus == 0){

                                if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4){

                                    if($dtaJson[$s]['statusUp'] == 0 ){
                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                        $resultData = array('msg'=>$message,'status'=>'error');
                                        array_push($arraytipoEstatus,$resultData);
                                        echo json_encode($arraytipoEstatus);
                                        exit();
                                    }else{
                                        if($dtaJson[$s]['statusUp'] == 4){
                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                            $resultData = array('msg'=>$message,'status'=>'error');
                                            array_push($arraytipoEstatus,$resultData);
                                            echo json_encode($arraytipoEstatus);
                                            exit();

                                        }
                                    }
                                }else{

                                    $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                    $ErrMsg = "No se pudo actualizar la información";
                                    $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                    if($TransResult == true){

                                        $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                        $ErrMsg = "No se pudo actualizar la información";
                                        $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                    }


                                }

                                $resultData = array('msg'=>'Reintegro Cancelado Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                array_push($arraytipoEstatus,$resultData);
                               // echo json_encode($resultData);

                            }else{
                                if($idStatus == 2){

                                    if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                        if($dtaJson[$s]['statusUp'] == 0 ){
                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                            $resultData = array('msg'=>$message,'status'=>'error');
                                            array_push($arraytipoEstatus,$resultData);
                                            echo json_encode($arraytipoEstatus);
                                            exit();
                                        }else{
                                            if($dtaJson[$s]['statusUp'] == 4){
                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                array_push($arraytipoEstatus,$resultData);
                                                echo json_encode($arraytipoEstatus);
                                                exit();

                                            }else{
                                                if($dtaJson[$s]['statusUp'] == $idStatus){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Validar';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();
                                                }
                                            }
                                        }
                                    }else{
                                        $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                        $ErrMsg = "No se pudo actualizar la información";
                                        $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                        if($TransResult == true){

                                            $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                            $ErrMsg = "No se pudo actualizar la información";
                                            $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                        }
                                    }

                                    $resultData = array('msg'=>'Cambio de estatus de Reintegro por validar Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                    array_push($arraytipoEstatus,$resultData);
                                  //  echo json_encode($resultData);

                                }else{
                                    if($idStatus == 3){

                                        if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                            if($dtaJson[$s]['statusUp'] == 0 ){
                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                array_push($arraytipoEstatus,$resultData);
                                                echo json_encode($arraytipoEstatus);
                                                exit();
                                            }else{
                                                if($dtaJson[$s]['statusUp'] == 4){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();

                                                }else{
                                                    if($dtaJson[$s]['statusUp'] == $idStatus){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Autorizar';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();
                                                    }
                                                }
                                            }
                                        }else{


                                                $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '" . $idStatus . "', cancel_date = NOW() 
                                                         WHERE id = '" . $dtaJson[$s]['idRefunds'] . "' AND ur_id = '" . $dtaJson[$s]['ur'] . "' ";

                                                $ErrMsg = "No se pudo actualizar la información";
                                                $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                if ($TransResult == true) {

                                                    $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '" . $idStatus . "' WHERE transno = '" . $dtaJson[$s]['idRefunds'] . "' AND type = 293 ";

                                                    $ErrMsg = "No se pudo actualizar la información";
                                                    $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                }

                                        }

                                        $resultData = array('msg'=>'Cambio de estatus de Reintegro por autorizar Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                        array_push($arraytipoEstatus,$resultData);
                                       // echo json_encode($resultData);


                                    }else{
                                        if($idStatus == 99){

                                          /*  $funcionPermisoMod= 0;

                                            $funct = "SELECT tb_refunds_notice.status_refund, tb_refunds_notice.refund_id ,tb_botones_statusSig.functionid,tb_botones_status.sn_estatus_siguiente,tb_botones_status.sn_estatus_anterior FROM tb_refunds_notice
                                                  LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_status.statusid = tb_refunds_notice.status_refund
                                                  LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
                                                  WHERE tb_refunds_notice.id = '".$dtaJson[$s]['idRefunds']."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";

                                            $transResultQQ = DB_query($funct, $db);

                                            while ($myrow = DB_fetch_array($transResultQQ)) {
                                                $funcionPermisoMod = $myrow['functionid'];
                                            }*/
                                          // || $funcionRegistro != $funcionPermisoMod

                                            if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4){

                                                if($dtaJson[$s]['statusUp'] == 0 ){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();
                                                }else{
                                                    if($dtaJson[$s]['statusUp'] == 4){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();

                                                    }
                                                    /*else{
                                                        if($funcionRegistro != $funcionPermisoMod){
                                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' no se puede Rechazar por su Estatus';
                                                            $resultData = array('msg'=>$message,'status'=>'error');
                                                            array_push($arraytipoEstatus,$resultData);
                                                            echo json_encode($arraytipoEstatus);
                                                            exit();
                                                        }
                                                    }*/
                                                }
                                            }else{


                                                if($dtaJson[$s]['statusUp'] == 2){



                                                        $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 1, cancel_date = NOW() 
                                                             WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                        $ErrMsg = "No se pudo actualizar la información";
                                                        $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                        if($TransResult == true){

                                                            $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 1 WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                            $ErrMsg = "No se pudo actualizar la información";
                                                            $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                        }


                                                }else{
                                                    if($dtaJson[$s]['statusUp'] == 3){

                                                            $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 2, cancel_date = NOW() 
                                                                                   WHERE id = '" . $dtaJson[$s]['idRefunds'] . "' AND ur_id = '" . $dtaJson[$s]['ur'] . "' ";

                                                            $ErrMsg = "No se pudo actualizar la información";
                                                            $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                            if ($TransResult == true) {

                                                                $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 2 WHERE transno = '" . $dtaJson[$s]['idRefunds'] . "' AND type = 293 ";

                                                                $ErrMsg = "No se pudo actualizar la información";
                                                                $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                            }



                                                    }else{
                                                        if($dtaJson[$s]['statusUp'] == 5){





                                                                $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 3, cancel_date = NOW() 
                                                                                       WHERE id = '" . $dtaJson[$s]['idRefunds'] . "' AND ur_id = '" . $dtaJson[$s]['ur'] . "' ";

                                                                $ErrMsg = "No se pudo actualizar la información";
                                                                $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                                if ($TransResult == true) {

                                                                    $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 3 WHERE transno = '" . $dtaJson[$s]['idRefunds'] . "' AND type = 293 ";

                                                                    $ErrMsg = "No se pudo actualizar la información";
                                                                    $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                                }

                                                        }
                                                    }
                                                }

                                            }

                                            $resultData = array('msg'=>'Reintegro Rechazado Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                            array_push($arraytipoEstatus,$resultData);
                                           // echo json_encode($resultData);

                                        }else{
                                            if($idStatus == 5){

                                                if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                                    if($dtaJson[$s]['statusUp'] == 0 ){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();
                                                    }else{
                                                        if($dtaJson[$s]['statusUp'] == 4){
                                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                            $resultData = array('msg'=>$message,'status'=>'error');
                                                            array_push($arraytipoEstatus,$resultData);
                                                            echo json_encode($arraytipoEstatus);
                                                            exit();

                                                        }else{
                                                            if($dtaJson[$s]['statusUp'] == $idStatus){
                                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Solicitar';
                                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                                array_push($arraytipoEstatus,$resultData);
                                                                echo json_encode($arraytipoEstatus);
                                                                exit();
                                                            }
                                                        }
                                                    }
                                                }else{

                                                    $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                    $ErrMsg = "No se pudo actualizar la información";
                                                    $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                    if($TransResult == true){

                                                        $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                        $ErrMsg = "No se pudo actualizar la información";
                                                        $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                    }

                                                }

                                            }

                                            $resultData = array('msg'=>'Cambio de estatus de Reintegro por Solicitar fue  Correcto con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                            array_push($arraytipoEstatus,$resultData);
                                           // echo json_encode($resultData);
                                        }
                                    }
                                }
                            }

                            // FIN CODIGO RADICADO
                        }else{
                            if($dtaJson[$s]['typeR'] == 3){
                                // CODIGO PROVEEDORES ESTATUS

                                if($idStatus == 0){

                                    if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4){

                                        if($dtaJson[$s]['statusUp'] == 0 ){
                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                            $resultData = array('msg'=>$message,'status'=>'error');
                                            array_push($arraytipoEstatus,$resultData);
                                            echo json_encode($arraytipoEstatus);
                                            exit();
                                        }else{
                                            if($dtaJson[$s]['statusUp'] == 4){
                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                array_push($arraytipoEstatus,$resultData);
                                                echo json_encode($arraytipoEstatus);
                                                exit();

                                            }
                                        }
                                    }else{

                                        $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                        $ErrMsg = "No se pudo actualizar la información";
                                        $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                        if($TransResult == true){

                                            $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                            $ErrMsg = "No se pudo actualizar la información";
                                            $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                        }


                                    }

                                    $resultData = array('msg'=>'Reintegro Cancelado Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                    array_push($arraytipoEstatus,$resultData);
                                  //  echo json_encode($resultData);

                                }else{
                                    if($idStatus == 2){

                                        if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                            if($dtaJson[$s]['statusUp'] == 0 ){
                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                array_push($arraytipoEstatus,$resultData);
                                                echo json_encode($arraytipoEstatus);
                                                exit();
                                            }else{
                                                if($dtaJson[$s]['statusUp'] == 4){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();

                                                }else{
                                                    if($dtaJson[$s]['statusUp'] == $idStatus){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Validar';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();
                                                    }
                                                }
                                            }
                                        }else{
                                            $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                            $ErrMsg = "No se pudo actualizar la información";
                                            $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                            if($TransResult == true){

                                                $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                $ErrMsg = "No se pudo actualizar la información";
                                                $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                            }
                                        }

                                        $resultData = array('msg'=>'Cambio de estatus de Reintegro por validar Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                        array_push($arraytipoEstatus,$resultData);
                                     //   echo json_encode($resultData);

                                    }else{
                                        if($idStatus == 3){

                                            if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                                if($dtaJson[$s]['statusUp'] == 0 ){
                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    echo json_encode($arraytipoEstatus);
                                                    exit();
                                                }else{
                                                    if($dtaJson[$s]['statusUp'] == 4){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();

                                                    }else{
                                                        if($dtaJson[$s]['statusUp'] == $idStatus){
                                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Autorizar';
                                                            $resultData = array('msg'=>$message,'status'=>'error');
                                                            array_push($arraytipoEstatus,$resultData);
                                                            echo json_encode($arraytipoEstatus);
                                                            exit();
                                                        }
                                                    }
                                                }
                                            }else{


                                                    $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '" . $idStatus . "', cancel_date = NOW() 
                                                         WHERE id = '" . $dtaJson[$s]['idRefunds'] . "' AND ur_id = '" . $dtaJson[$s]['ur'] . "' ";

                                                    $ErrMsg = "No se pudo actualizar la información";
                                                    $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                    if ($TransResult == true) {

                                                        $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '" . $idStatus . "' WHERE transno = '" . $dtaJson[$s]['idRefunds'] . "' AND type = 293 ";

                                                        $ErrMsg = "No se pudo actualizar la información";
                                                        $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                    }

                                            }

                                            $resultData = array('msg'=>'Cambio de estatus de Reintegro por autorizar Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                            array_push($arraytipoEstatus,$resultData);
                                           // echo json_encode($resultData);


                                        }else{
                                            if($idStatus == 99){

                                            /*    $funcionPermisoMod= 0;

                                                $funct = "SELECT tb_refunds_notice.status_refund, tb_refunds_notice.refund_id ,tb_botones_statusSig.functionid,tb_botones_status.sn_estatus_siguiente,tb_botones_status.sn_estatus_anterior FROM tb_refunds_notice
                                                  LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_status.statusid = tb_refunds_notice.status_refund
                                                  LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
                                                  WHERE tb_refunds_notice.id = '".$dtaJson[$s]['idRefunds']."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."' ";

                                                $transResultQQ = DB_query($funct, $db);

                                                while ($myrow = DB_fetch_array($transResultQQ)) {
                                                    $funcionPermisoMod = $myrow['functionid'];
                                                }*/

                                              //  || $funcionRegistro != $funcionPermisoMod

                                                if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4){

                                                    if($dtaJson[$s]['statusUp'] == 0 ){
                                                        $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                        $resultData = array('msg'=>$message,'status'=>'error');
                                                        array_push($arraytipoEstatus,$resultData);
                                                        echo json_encode($arraytipoEstatus);
                                                        exit();
                                                    }else{
                                                        if($dtaJson[$s]['statusUp'] == 4){
                                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                            $resultData = array('msg'=>$message,'status'=>'error');
                                                            array_push($arraytipoEstatus,$resultData);
                                                            echo json_encode($arraytipoEstatus);
                                                            exit();

                                                        }
                                                        /*else{
                                                            if($funcionRegistro != $funcionPermisoMod){
                                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' no se puede Rechazar por su Estatus';
                                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                                array_push($arraytipoEstatus,$resultData);
                                                                echo json_encode($arraytipoEstatus);
                                                                exit();
                                                            }
                                                        }*/
                                                    }
                                                }else{


                                                    if($dtaJson[$s]['statusUp'] == 2){

                                                      //  print_r($dtaJson[$s]['statusUp']);
                                                      //  exit();

                                                            $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 1, cancel_date = NOW() 
                                                             WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                            $ErrMsg = "No se pudo actualizar la información";
                                                            $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                            if($TransResult == true){

                                                                $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 1 WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                                $ErrMsg = "No se pudo actualizar la información";
                                                                $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                            }




                                                    }else{
                                                        if($dtaJson[$s]['statusUp'] == 3){


                                                                $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 2, cancel_date = NOW() 
                                                                                   WHERE id = '" . $dtaJson[$s]['idRefunds'] . "' AND ur_id = '" . $dtaJson[$s]['ur'] . "' ";

                                                                $ErrMsg = "No se pudo actualizar la información";
                                                                $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                                if ($TransResult == true) {

                                                                    $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 2 WHERE transno = '" . $dtaJson[$s]['idRefunds'] . "' AND type = 293 ";

                                                                    $ErrMsg = "No se pudo actualizar la información";
                                                                    $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                                }


                                                        }else{
                                                            if($dtaJson[$s]['statusUp'] == 5){





                                                                    $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = 3, cancel_date = NOW() 
                                                                                   WHERE id = '" . $dtaJson[$s]['idRefunds'] . "' AND ur_id = '" . $dtaJson[$s]['ur'] . "' ";

                                                                    $ErrMsg = "No se pudo actualizar la información";
                                                                    $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                                    if ($TransResult == true) {

                                                                        $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = 3 WHERE transno = '" . $dtaJson[$s]['idRefunds'] . "' AND type = 293 ";

                                                                        $ErrMsg = "No se pudo actualizar la información";
                                                                        $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                                    }

                                                            }
                                                        }
                                                    }

                                                }

                                                $resultData = array('msg'=>'Reintegro Rechazado Correctamente con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                                array_push($arraytipoEstatus,$resultData);
                                              //  echo json_encode($resultData);


                                            }else{

                                                if($idStatus == 5){

                                                    if($dtaJson[$s]['statusUp'] == 0 || $dtaJson[$s]['statusUp'] == 4 || $dtaJson[$s]['statusUp'] == $idStatus){

                                                        if($dtaJson[$s]['statusUp'] == 0 ){
                                                            $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Cancelado';
                                                            $resultData = array('msg'=>$message,'status'=>'error');
                                                            array_push($arraytipoEstatus,$resultData);
                                                            echo json_encode($arraytipoEstatus);
                                                            exit();
                                                        }else{
                                                            if($dtaJson[$s]['statusUp'] == 4){
                                                                $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' se encuentra Autorizado';
                                                                $resultData = array('msg'=>$message,'status'=>'error');
                                                                array_push($arraytipoEstatus,$resultData);
                                                                echo json_encode($arraytipoEstatus);
                                                                exit();

                                                            }else{
                                                                if($dtaJson[$s]['statusUp'] == $idStatus){
                                                                    $message = 'EL aviso de reintegro con número de folio: '.$dtaJson[$s]['folio'].' ya se encuentra con el estatus por Solicitar';
                                                                    $resultData = array('msg'=>$message,'status'=>'error');
                                                                    array_push($arraytipoEstatus,$resultData);
                                                                    echo json_encode($arraytipoEstatus);
                                                                    exit();
                                                                }
                                                            }
                                                        }
                                                    }else{

                                                        $updateHeaderRefund = "UPDATE tb_refunds_notice SET status_refund = '".$idStatus."', cancel_date = NOW() 
                                                         WHERE id = '".$dtaJson[$s]['idRefunds']."' AND ur_id = '".$dtaJson[$s]['ur']."' ";

                                                        $ErrMsg = "No se pudo actualizar la información";
                                                        $TransResult = DB_query($updateHeaderRefund, $db, $ErrMsg);

                                                        if($TransResult == true){

                                                            $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$idStatus."' WHERE transno = '".$dtaJson[$s]['idRefunds']."' AND type = 293 ";

                                                            $ErrMsg = "No se pudo actualizar la información";
                                                            $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);
                                                        }

                                                    }

                                                    $resultData = array('msg'=>'Cambio de estatus de Reintegro por Solicitar fue  Correcto con Folio: '.$dtaJson[$s]['folio'],'status'=>'success');
                                                    array_push($arraytipoEstatus,$resultData);
                                                    //echo json_encode($resultData);

                                                }
                                            }
                                        }
                                    }
                                }

                                //CODIGO PROVEEDORES ESTATUS

                            }
                        }
                    }

            }


            echo json_encode($arraytipoEstatus);

        }catch (Exception $error){

            $resultData = array('msg'=>$error->getMessage(),'status'=>'error');
            echo json_encode($resultData);

        }

    }
}

function isHomogenous($arr) {
    $firstValue = current($arr);
    foreach ($arr as $val) {
        if ($firstValue !== $val) {
            return false;
        }
    }
    return true;
}




function permissionUser($transR){

    try{

        $fechaActualAde = date('d-m-Y');
        $autorizarGeneral = 0; // Variable deshabilitar general
        $permisoEditarEstCapturado = 0; // Havepermission($_SESSION ['UserID'], 2283, $db);
        $soloActFoliosAutorizada = 0;

        $estatusAdecuacionGeneral = "";
        $funcionPermisoMod = 0;


        $SQL =  "SELECT tb_refunds_notice.status_refund, tb_refunds_notice.refund_id ,tb_botones_statusSig.functionid,tb_botones_status.statusid AS raul FROM tb_refunds_notice
            LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_status.statusid = tb_refunds_notice.status_refund 
            LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
            WHERE tb_refunds_notice.id = ".$transR." AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."' ";

        $transResult = DB_query($SQL, $db);

        while ($myrow = DB_fetch_array($transResult)) {
            $estatusAdecuacionGeneral = $myrow['status_refund'];
            // $tipo_reintegro = $myrow['nu_tipo'];
            $funcionPermisoMod = $myrow['functionid'];
            //$tipoReintegro = $myrow['refund_id'];
            $permisoX = $myrow['raul'];
        }

        if ($estatusAdecuacionGeneral == '0' || $estatusAdecuacionGeneral == '4') {
            // 0 = Cancelado, 4 = Autorizado
            $autorizarGeneral = 1;
        }


        if (!empty(trim($funcionPermisoMod)) && $funcionPermisoMod != '0') {
            // Validar si puede modificarla, Tiene permiso para el siguiente Estatus
            $permisoMod = Havepermission($_SESSION['UserID'], $funcionPermisoMod, $db);

            if ($permisoMod == '0') {
                // Solo mostrar informacion
                $autorizarGeneral = 1;
            }
        }

          return $permisoX;

   }catch (Exception $error){

        return $error->getMessage;
    }


}