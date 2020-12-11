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
 * Modelo para el panel de Captura Aviso de Reintegros
 */

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2412;
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

if(isset($_GET['option'])) {

    $option = $_GET['option'];


    if($option == 'typeRefund'){

        $info = array();
        $SQL = "SELECT id as value, name as texto, status FROM tb_cat_refunds WHERE status = '1' ORDER BY value ASC";
        $ErrMsg = "No se obtuvo los Estatus";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
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

    if($option == 'obtenerBotones') {
        $sqlWhere = " AND tb_botones_status.statusid < '90' ";

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
                tb_botones_status.functionid IN (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."')
                ) ".$sqlWhere."
                ORDER BY tb_botones_status.functionid ASC
                ";

        //

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

        $result = true;
        $contenido = array('datos' => $info,'result' => $result);

        echo json_encode($contenido);
    }

    if($option == 'loadURL'){

        $idRef = mysqli_real_escape_string($db, $_GET['idRefund']);
        $typeRef = mysqli_real_escape_string($db, $_GET['refundType']);
       // $typePay = mysqli_real_escape_string($db, $_GET['TypePayment']);

        $ErrMsg = "No se realizo la Consulta";
        $dtaResult = array(); // date_format($issue_date, 'Y-m-d');

        $totalGeneral_query = 0;

        $queryRefund = "SELECT * FROM tb_refunds_notice WHERE id = '".$idRef."'";
        $resultID = DB_query($queryRefund,$db,$ErrMsg);

        while ($rowsRefund = DB_fetch_array($resultID)){


            $querySearchQTY = "SELECT supptrans.id, supptransDocPago.type, supptrans.tagref, supptrans.type, supptrans.transno, suppallocs.transid_allocfrom, suppallocs.transid_allocto,
                               supptransdetails.supptransid, supptransdetails.description, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
                               CAST(SUM(supptransdetails.qty * supptransdetails.price) as decimal(16,4)) AS totalGeneral, SUM(supptransdetails.price) AS Prices, tags.legalid FROM supptrans 
                               JOIN suppallocs ON suppallocs.transid_allocfrom = supptrans.id
                               JOIN supptransdetails ON suppallocs.transid_allocto = supptransdetails.supptransid
                               JOIN supptrans supptransDocPago ON supptransDocPago.id = supptransdetails.supptransid
                               JOIN tags ON tags.tagref = supptrans.tagref
                               WHERE supptrans.type = 22 AND supptrans.suppreference = '".$rowsRefund['folio_invoice_transfer']."' AND supptransDocPago.type = '".$rowsRefund['type_payment']."' AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                               GROUP BY supptransdetails.clavepresupuestal";



            $ErrMsg = "No se realizo la Consulta";
            $ResultSearch = DB_query($querySearchQTY, $db, $ErrMsg);


            while ($rowsQty = DB_fetch_array($ResultSearch)){
                $totalGeneral_query = $rowsQty['totalGeneral'];
            }

            $dtaResult[] = array(
                'f_id' => $rowsRefund['id'],
                'ur' => $rowsRefund['ur_id'],
                'ue' => $rowsRefund['ue_id'],
                'tracking_code' => $rowsRefund['tracking_code'],
                'process_siaff' => $rowsRefund['process_siaff'],
                'transfer_number' => $rowsRefund['transfer_number'],
                'line_capture_TESOFE' => $rowsRefund['line_capture_TESOFE'],
                'refund_id' => $rowsRefund['refund_id'],
                'folioRefunds' => $rowsRefund['folio_invoice_transfer'],
                'type_payments' => $rowsRefund['type_payment'],
                'justification' => $rowsRefund['justification'],
                'dateStar' => date("d-m-Y", strtotime($rowsRefund['issue_date'])),
                'dateEnd' => $rowsRefund['auth_date'],
                'status_refund' => $rowsRefund['status_refund'],
                'ttlGeneral' => $totalGeneral_query,
                'authDate' => date("d-m-Y", strtotime($rowsRefund['auth_date']))
                //'cancelDate' => date("d-m-Y", strtotime($rowsRefund['cancel_date']))
               // 'cancelDate' => date("d-m-Y", strtotime($rowsRefund['cancel_date']))
            );

        }

        $resultData = array('searchResults' => $dtaResult);
        echo json_encode($resultData);

    }




}//fin GET


if(isset($_POST['fundata'])){
    if($_POST['fundata'] == 'storeData'){

        try{

            $arrayMes = [];
            $arrayTotal = [];
            $totalMonth = 12;
            $dataJsonMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $ErrMsg = "No se pudo almacenar la información";
            $arrayError = [];

            $ur_id = mysqli_real_escape_string($db, $_POST['ur_id']);
            $ue_id = mysqli_real_escape_string($db, $_POST['ue_id']);
            $line_Tesofe = mysqli_real_escape_string($db, $_POST['lineTesofe']);
            $tracking_code = mysqli_real_escape_string($db, $_POST['tracking_code']);
            $process_siaff = mysqli_real_escape_string($db, $_POST['process_siaff']);
            $transfer_number = mysqli_real_escape_string($db, $_POST['transfer_number']);
            $refund_id = mysqli_real_escape_string($db, $_POST['refund_id']);
            $folio_viatics_invoice_transfer = mysqli_real_escape_string($db, $_POST['folio_viatics_invoice_transfer']);
            $justification = mysqli_real_escape_string($db, $_POST['justification']);
            $issue_date = $_POST['issue_date'];
            $auth_date = $_POST['auth_date'];
            $period = mysqli_real_escape_string($db, $_POST['period']);
            $type = mysqli_real_escape_string($db, $_POST['type']);
            $transno = mysqli_real_escape_string($db, $_POST['transno']);
            $status_refund = 1;
            $valueTotalGeneral = mysqli_real_escape_string($db, $_POST['valueTotalG']);
            $type_payments = mysqli_real_escape_string($db, $_POST['typePayments']);
            $modes = mysqli_real_escape_string($db, $_POST['mode_refund']);


            $dtaReducciones = $_POST['infoReduct'];
            $dtRedc = $_POST['infoReduct'];

            $arrayFolio = $_POST['folioselect'];

           if($modes > 1){

               if($refund_id == 1){ //inicio tipo 1

                   $arrReduc = json_decode($dtRedc,true);
                   $dtaReducciones = $arrReduc;


                   if($refund_id == 1 || $refund_id == 2){

                       $folio_viatics = 0;
                       $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                   }else{

                       $folio_viatics = $folio_viatics_invoice_transfer;
                       $folio_invoice_transfer = 0;
                   }

                   $issue_date = date_create($issue_date);
                   $issue_date = date_format($issue_date, 'Y-m-d');

                   $auth_date = date_create($auth_date);
                   $auth_date = date_format($auth_date, 'Y-m-d');

                   $keytable = 1;
                   $rowItems = 0;

                   foreach ($dtaReducciones as $val){
                     //  foreach ($val as $values){
                           for($x=0;$x<count($dataJsonMeses);$x++){

                               $mesData = (int)$val['mes'];

                               if($mesData == $x+1){

                                    if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                        if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                        // $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                            $arrayError[] = array('message' => 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                        }

                                    }else{

                                        if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                        // $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                            $arrayError[] = array('message' => 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                        }else{
                                            if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){

                                                if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                // $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                    $arrayError[] = array('message' => 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                                }

                                            }else{
                                                if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){
                                                    //$arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                    $arrayError[] = array('message' => 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);

                                                }
                                            }
                                        }

                                    }//If Principal

                               }//

                           }


                          if($val['sequence_siaff'] == '' || $val['sequence_siaff'] == null || $val['sequence_siaff'] == 0 || $val['sequence_siaff'] == '0'){
                            // array_push($arrayError,$arrayError[$rowItems]['message'] = 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                               $arrayError[] = array('message' => 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                           }

                   //    } $cad = substr ($cad, 0, -1);

                       $keytable++;
                       $rowItems++;

                   }


                   if(count($arrayError) > 0){

                       $msg = array('message' => $arrayError,'tipo' => 'error');
                       echo json_encode($msg);
                       exit();

                   }else{

                       $arrFolio = json_decode($arrayFolio,true);
                       $folArray = $arrFolio;

                       if(count($folArray) > 0){

                           $allFolio = '';

                            for($q=0;$q<count($folArray);$q++){
                              $allFolio .= $folArray[$q].",";
                            }

                           $allFolio = trim($allFolio, ',');

                           $folio_viatics_invoice_transfer = $allFolio;

                       }

                       //.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'

                       $SQL = "INSERT INTO tb_refunds_notice (ur_id, ue_id, line_capture_TESOFE, tracking_code, process_siaff, transfer_number, refund_id, folio_viatics, folio_invoice_transfer, type_payment, justification, sn_funcion_id, issue_date, status_refund, nu_anio_fiscal) VALUES ('".$ur_id."', null, '".$line_Tesofe."' ,'".$tracking_code."', null, null, '".$refund_id."', null, '".$folio_viatics_invoice_transfer."', null, '".$justification."', '".$funcion."', '".$issue_date."', '".$status_refund."','".$_SESSION['ejercicioFiscal']."')";

                       $ErrMsg = "No se pudo almacenar la información";
                       $TransResult = DB_query($SQL, $db, $ErrMsg);
                   }


                   if($TransResult == true){ // Si la Sentencia Insert es Satisfactoria

                       $SelectLastID = "SELECT MAX(id) FROM tb_refunds_notice";
                       $ResultLastSelect = DB_query($SelectLastID, $db, $ErrMsg);
                       while($rowIdLast=DB_fetch_array($ResultLastSelect)){
                           $idLast = trim($rowIdLast[0]);
                       }

                       //insert Partidas

                       foreach($dtaReducciones as $valu){
                          // foreach($valu as $items){
                               for($v=0;$v<count($dataJsonMeses);$v++){

                                   $mesData = (int)$valu['mes'];


                                   if($mesData == $v+1){

                                       if($dataJsonMeses[$v] == 'Enero'){

                                           $dateP = $valu['año'].'-'.'01';
                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                               $periodS = $rowsPeriod['periodno'];
                                           }
                                       }else{
                                           if($dataJsonMeses[$v] == 'Febrero'){

                                               $dateP = $valu['año'].'-'.'02';
                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                   $periodS = $rowsPeriod['periodno'];
                                               }
                                           }else{
                                               if($dataJsonMeses[$v] == 'Marzo'){

                                                   $dateP = $valu['año'].'-'.'03';
                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                       $periodS = $rowsPeriod['periodno'];
                                                   }
                                               }else{
                                                   if($dataJsonMeses[$v] == 'Abril'){
                                                       $dateP = $valu['año'].'-'.'04';
                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                           $periodS = $rowsPeriod['periodno'];
                                                       }

                                                   }else{
                                                       if($dataJsonMeses[$v] == 'Mayo'){
                                                           $dateP = $valu['año'].'-'.'05';
                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                               $periodS = $rowsPeriod['periodno'];
                                                           }
                                                       }else{
                                                           if($dataJsonMeses[$v] == 'Junio'){
                                                               $dateP = $valu['año'].'-'.'06';
                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                   $periodS = $rowsPeriod['periodno'];
                                                               }
                                                           }else{
                                                               if($dataJsonMeses[$v] == 'Julio'){
                                                                   $dateP = $valu['año'].'-'.'07';
                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                       $periodS = $rowsPeriod['periodno'];
                                                                   }
                                                               }else{
                                                                   if($dataJsonMeses[$v] == 'Agosto'){
                                                                       $dateP = $valu['año'].'-'.'08';
                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                           $periodS = $rowsPeriod['periodno'];
                                                                       }
                                                                   }else{
                                                                       if($dataJsonMeses[$v] == 'Septiembre'){
                                                                           $dateP = $valu['año'].'-'.'09';
                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                               $periodS = $rowsPeriod['periodno'];
                                                                           }

                                                                       }else{
                                                                           if($dataJsonMeses[$v] == 'Octubre'){
                                                                               $dateP = $valu['año'].'-'.'10';
                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                               }
                                                                           }else{
                                                                               if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                   $dateP = $valu['año'].'-'.'11';
                                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                       $periodS = $rowsPeriod['periodno'];
                                                                                   }
                                                                               }else{
                                                                                   if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                       $dateP = $valu['año'].'-'.'12';
                                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                           $periodS = $rowsPeriod['periodno'];
                                                                                       }
                                                                                   }
                                                                               }
                                                                           }
                                                                       }
                                                                   }
                                                               }
                                                           }
                                                       }
                                                   }
                                               }
                                           }
                                       }


                                       $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                       $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                       $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                       $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                       $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                       $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                       $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                       $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                       $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                       $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                       $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                       $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                       $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                       $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                      // $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 266;
                                       $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 268;
                                       $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                       $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                       $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                       $arrayTotal[$dataJsonMeses[$v]]['transno'] = $idLast;
                                       $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                       $arrayTotal[$dataJsonMeses[$v]]['sequenceSIAFF'] = $valu['sequence_siaff'];
                                       $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];


                                   }

                               }
                         //  }

                             foreach ($arrayTotal as $valItems){
                                    /* fnAgregarPresupuestoGeneral($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'],
                                       $valItems['period'], $valItems['qty'], 1, $valItems['tipoAfectacion'], $valItems['tipoMovimiento'],
                                       $valItems['partida_esp'], 265, 265,'Reintegro en tramite con folio de Operacion  :'.$idLast);*/  // $folio_viatics_invoice_transfer
                                       if($valItems['sequenceSIAFF'] == '' || $valItems['sequenceSIAFF'] == null){
                                           $sqsSIAFF = 0;
                                        }else{
                                            $sqsSIAFF = $valItems['sequenceSIAFF'];
                                        }
                                      
                                       fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0,
                                                                   1, 0, '', 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'], $sqsSIAFF, $valItems['folio_Tranfer']);


                          }

                       }

                       //insert

                       $msg = array('message' => 'El reintegro con número de folio: '.$idLast.', '.'se registro correctamente','tipo'=>'success');
                       echo json_encode($msg);

                   }else{

                       $msg = array('message' => $ErrMsg,'tipo'=>'error');
                       echo json_encode($msg);
                   }

                //fin tipo 1
               }else{
                   if($refund_id == 3){

                       $arrReduc = json_decode($dtRedc,true);
                       $dtaReducciones = $arrReduc;


                       if($refund_id == 1 || $refund_id == 2){

                           $folio_viatics = 0;
                           $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                       }else{

                           $folio_viatics = $folio_viatics_invoice_transfer;
                           $folio_invoice_transfer = 0;
                       }

                       $issue_date = date_create($issue_date);
                       $issue_date = date_format($issue_date, 'Y-m-d');

                       $auth_date = date_create($auth_date);
                       $auth_date = date_format($auth_date, 'Y-m-d');

                       $keytable = 1;
                       $rowItems = 0;

                       foreach ($dtaReducciones as $val){
                          // foreach ($val as $values){
                               for($x=0;$x<count($dataJsonMeses);$x++){

                                   $mesData = (int)$val['mes'];

                                   if($mesData == $x+1){

                                       if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                           if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                               $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                           }

                                       }else{

                                           if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                               $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                           }else{
                                               if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){

                                                   if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                       $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                   }

                                               }else{
                                                   if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){
                                                       $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;

                                                   }
                                               }
                                           }

                                       }//If Principal

                                   }

                               }

                          // }

                           $keytable++;
                           $rowItems++;

                       }

                       if(count($arrayError) > 0){

                           $msg = array('message' => $arrayError,'tipo' => 'error');
                           echo json_encode($msg);
                           exit();
                       }else{

                           $arrFolio = json_decode($arrayFolio,true);
                           $folArray = $arrFolio;

                           if(count($folArray) > 0){

                               $allFolio = '';

                               for($q=0;$q<count($folArray);$q++){
                                   $allFolio .= $folArray[$q].",";
                               }

                               $allFolio = trim($allFolio, ',');

                               $folio_viatics_invoice_transfer = $allFolio;

                           }

                           //nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'

                           $SQL = "INSERT INTO tb_refunds_notice (ur_id, ue_id, line_capture_TESOFE, tracking_code, process_siaff, transfer_number, refund_id, folio_viatics, folio_invoice_transfer, type_payment, justification, sn_funcion_id, issue_date, status_refund, nu_anio_fiscal) VALUES ('".$ur_id."', '".$ue_id."', null,'".$tracking_code."', null, null, '".$refund_id."', '".$folio_viatics."', '".$folio_viatics_invoice_transfer."', '".$type_payments."', '".$justification."', '".$funcion."', '".$issue_date."', '".$status_refund."', '".$_SESSION['ejercicioFiscal']."')";

                           $ErrMsg = "No se pudo almacenar la información";
                           $TransResult = DB_query($SQL, $db, $ErrMsg);
                       }


                       if($TransResult == true){ // Si la Sentencia Insert es Satisfactoria

                           $SelectLastID = "SELECT MAX(id) FROM tb_refunds_notice";
                           $ResultLastSelect = DB_query($SelectLastID, $db, $ErrMsg);
                           while($rowIdLast=DB_fetch_array($ResultLastSelect)){
                               $idLast = trim($rowIdLast[0]);
                           }


                           foreach($dtaReducciones as $valu){

                               //foreach($valu as $items){
                                   for($v=0;$v<count($dataJsonMeses);$v++){

                                       if($valu[$dataJsonMeses[$v].'Sel'] != 0){

                                           if($dataJsonMeses[$v] == 'Enero'){

                                               $dateP = $valu['año'].'-'.'01';
                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                   $periodS = $rowsPeriod['periodno'];
                                               }
                                           }else{
                                               if($dataJsonMeses[$v] == 'Febrero'){

                                                   $dateP = $valu['año'].'-'.'02';
                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                       $periodS = $rowsPeriod['periodno'];
                                                   }
                                               }else{
                                                   if($dataJsonMeses[$v] == 'Marzo'){

                                                       $dateP = $valu['año'].'-'.'03';
                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                           $periodS = $rowsPeriod['periodno'];
                                                       }
                                                   }else{
                                                       if($dataJsonMeses[$v] == 'Abril'){
                                                           $dateP = $valu['año'].'-'.'04';
                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                               $periodS = $rowsPeriod['periodno'];
                                                           }

                                                       }else{
                                                           if($dataJsonMeses[$v] == 'Mayo'){
                                                               $dateP = $valu['año'].'-'.'05';
                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                   $periodS = $rowsPeriod['periodno'];
                                                               }
                                                           }else{
                                                               if($dataJsonMeses[$v] == 'Junio'){
                                                                   $dateP = $valu['año'].'-'.'06';
                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                       $periodS = $rowsPeriod['periodno'];
                                                                   }
                                                               }else{
                                                                   if($dataJsonMeses[$v] == 'Julio'){
                                                                       $dateP = $valu['año'].'-'.'07';
                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                           $periodS = $rowsPeriod['periodno'];
                                                                       }
                                                                   }else{
                                                                       if($dataJsonMeses[$v] == 'Agosto'){
                                                                           $dateP = $valu['año'].'-'.'08';
                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                               $periodS = $rowsPeriod['periodno'];
                                                                           }
                                                                       }else{
                                                                           if($dataJsonMeses[$v] == 'Septiembre'){
                                                                               $dateP = $valu['año'].'-'.'09';
                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                               }
                                                                           }else{
                                                                               if($dataJsonMeses[$v] == 'Octubre'){
                                                                                   $dateP = $valu['año'].'-'.'10';
                                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                       $periodS = $rowsPeriod['periodno'];
                                                                                   }
                                                                               }else{
                                                                                   if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                       $dateP = $valu['año'].'-'.'11';
                                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                           $periodS = $rowsPeriod['periodno'];
                                                                                       }
                                                                                   }else{
                                                                                       if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                           $dateP = $valu['año'].'-'.'12';
                                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                               $periodS = $rowsPeriod['periodno'];
                                                                                           }
                                                                                       }
                                                                                   }
                                                                               }
                                                                           }
                                                                       }
                                                                   }
                                                               }
                                                           }
                                                       }
                                                   }
                                               }
                                           }


                                           $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                           $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                           $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                           $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                           $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                           $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                           $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                           $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                           $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                           $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                           $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                           $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                           $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                           $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                           $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 265;
                                           $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                           $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                           $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                           $arrayTotal[$dataJsonMeses[$v]]['transno'] = $idLast;
                                           $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];
                                           $arrayTotal[$dataJsonMeses[$v]]['no_Devengado'] = $valu['noDevengado'];
                                           $arrayTotal[$dataJsonMeses[$v]]['no_Compromiso'] = $valu['noCompromiso'];
                                           $arrayTotal[$dataJsonMeses[$v]]['no_Retencion'] = $valu['noRetencion'];

                                       }



                                   }
                              // }

                               /*fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0,
                                   1, 0, '', 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $folio_viatics_invoice_transfer, $valItems['type_refund'], $valItems['sequenceSIAFF']);*/

                               foreach ($arrayTotal as $valItems){
                                   fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0, 1, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1,'', '', 0, $valItems['folio_Tranfer'], $refund_id,0,$valItems['folio_Tranfer'],0,$type_payments);
                               }


                           }

                           $msg = array('message' => 'El reintegro con número de folio: '.$idLast.', '.'se registro correctamente','tipo'=>'success');
                           echo json_encode($msg);

                       }else{

                           $msg = array('message' => $ErrMsg,'tipo'=>'error');
                           echo json_encode($msg);
                       }

                   }else{
                       if($refund_id == 2){
                           // CODIGO RADICADO //

                           $arrReduc = json_decode($dtRedc,true);
                           $dtaReducciones = $arrReduc;

                           if($refund_id == 1 || $refund_id == 2){

                               $folio_viatics = 0;
                               $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                           }else{

                               $folio_viatics = $folio_viatics_invoice_transfer;
                               $folio_invoice_transfer = 0;
                           }

                           $issue_date = date_create($issue_date);
                           $issue_date = date_format($issue_date, 'Y-m-d');

                           $auth_date = date_create($auth_date);
                           $auth_date = date_format($auth_date, 'Y-m-d');

                           $keytable = 1;
                           $rowItems = 0;


                           foreach ($dtaReducciones as $val){
                              // foreach ($val as $values){
                                   for($x=0;$x<count($dataJsonMeses);$x++){

                                       $mesData = (int)$val['mes'];

                                       if($mesData == $x+1){

                                           if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                               if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                   $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                               }

                                           }else{

                                               if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                                   $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                               }else{
                                                   if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){

                                                       if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                           $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                       }

                                                   }else{
                                                       if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){
                                                           $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;

                                                       }
                                                   }
                                               }

                                           }//If Principal

                                       }

                                   }

                              // }

                               $keytable++;
                               $rowItems++;

                           }

                           if(count($arrayError) > 0){

                               $msg = array('message' => $arrayError,'tipo' => 'error');
                               echo json_encode($msg);
                               exit();
                           }else{

                               $arrFolio = json_decode($arrayFolio,true);
                               $folArray = $arrFolio;

                               if(count($folArray) > 0){

                                   $allFolio = '';

                                   for($q=0;$q<count($folArray);$q++){
                                       $allFolio .= $folArray[$q].",";
                                   }

                                   $allFolio = trim($allFolio, ',');

                                   $folio_viatics_invoice_transfer = $allFolio;

                               }

                              // nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'

                               $SQL = "INSERT INTO tb_refunds_notice (ur_id, ue_id, line_capture_TESOFE, tracking_code, process_siaff, transfer_number, refund_id, folio_viatics, folio_invoice_transfer, type_payment, justification, sn_funcion_id, issue_date, status_refund, nu_anio_fiscal) VALUES ('".$ur_id."', '".$ue_id."', null, '".$tracking_code."', null, '".$transfer_number."', '".$refund_id."', '".$folio_viatics."', '".$folio_viatics_invoice_transfer."', null, '".$justification."', '".$funcion."', '".$issue_date."', '".$status_refund."', '".$_SESSION['ejercicioFiscal']."')";

                               $ErrMsg = "No se pudo almacenar la información";
                               $TransResult = DB_query($SQL, $db, $ErrMsg);
                           }


                           if($TransResult == true){ // Si la Sentencia Insert es Satisfactoria

                               $SelectLastID = "SELECT MAX(id) FROM tb_refunds_notice";
                               $ResultLastSelect = DB_query($SelectLastID, $db, $ErrMsg);
                               while($rowIdLast=DB_fetch_array($ResultLastSelect)){
                                   $idLast = trim($rowIdLast[0]);
                               }

                               //insert Partidas

                               foreach($dtaReducciones as $valu){
                                   //foreach($valu as $items){
                                       for($v=0;$v<count($dataJsonMeses);$v++){

                                           $mesData = (int)$valu['mes'];


                                           if($mesData == $v+1){

                                               if($dataJsonMeses[$v] == 'Enero'){

                                                   $dateP = $valu['año'].'-'.'01';
                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                       $periodS = $rowsPeriod['periodno'];
                                                   }
                                               }else{
                                                   if($dataJsonMeses[$v] == 'Febrero'){

                                                       $dateP = $valu['año'].'-'.'02';
                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                           $periodS = $rowsPeriod['periodno'];
                                                       }
                                                   }else{
                                                       if($dataJsonMeses[$v] == 'Marzo'){

                                                           $dateP = $valu['año'].'-'.'03';
                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                               $periodS = $rowsPeriod['periodno'];
                                                           }
                                                       }else{
                                                           if($dataJsonMeses[$v] == 'Abril'){
                                                               $dateP = $valu['año'].'-'.'04';
                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                   $periodS = $rowsPeriod['periodno'];
                                                               }

                                                           }else{
                                                               if($dataJsonMeses[$v] == 'Mayo'){
                                                                   $dateP = $valu['año'].'-'.'05';
                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                       $periodS = $rowsPeriod['periodno'];
                                                                   }
                                                               }else{
                                                                   if($dataJsonMeses[$v] == 'Junio'){
                                                                       $dateP = $valu['año'].'-'.'06';
                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                           $periodS = $rowsPeriod['periodno'];
                                                                       }
                                                                   }else{
                                                                       if($dataJsonMeses[$v] == 'Julio'){
                                                                           $dateP = $valu['año'].'-'.'07';
                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                               $periodS = $rowsPeriod['periodno'];
                                                                           }
                                                                       }else{
                                                                           if($dataJsonMeses[$v] == 'Agosto'){
                                                                               $dateP = $valu['año'].'-'.'08';
                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                               }
                                                                           }else{
                                                                               if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                   $dateP = $valu['año'].'-'.'09';
                                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                       $periodS = $rowsPeriod['periodno'];
                                                                                   }

                                                                               }else{
                                                                                   if($dataJsonMeses[$v] == 'Octubre'){
                                                                                       $dateP = $valu['año'].'-'.'10';
                                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                           $periodS = $rowsPeriod['periodno'];
                                                                                       }
                                                                                   }else{
                                                                                       if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                           $dateP = $valu['año'].'-'.'11';
                                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                               $periodS = $rowsPeriod['periodno'];
                                                                                           }
                                                                                       }else{
                                                                                           if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                               $dateP = $valu['año'].'-'.'12';
                                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                                               }
                                                                                           }
                                                                                       }
                                                                                   }
                                                                               }
                                                                           }
                                                                       }
                                                                   }
                                                               }
                                                           }
                                                       }
                                                   }
                                               }


                                               $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                               $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                               $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                               $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                               $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                               $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                               $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                               $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                               $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                               $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                               $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                               $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                               $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                               $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                              // $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 269;
                                               $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 271;
                                               $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                               $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                               $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                               $arrayTotal[$dataJsonMeses[$v]]['transno'] = $idLast;
                                               $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                               $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];


                                           }



                                       }
                                  // }

                                   foreach ($arrayTotal as $valItems){

                                       fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0,
                                           1, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'],0,$valItems['folio_Tranfer']);

                                   }

                               }

                               //insert

                               $msg = array('message' => 'El reintegro con número de folio: '.$idLast.', '.'se registro correctamente','tipo'=>'success');
                               echo json_encode($msg);

                           }else{

                               $msg = array('message' => $ErrMsg,'tipo'=>'error');
                               echo json_encode($msg);
                           }




                       }//fin de Save 2
                   }
               }


           }else{

               if($modes <= 1){


                   if($refund_id == 1){


                       $arrReduc = json_decode($dtRedc,true);
                       $dtaReducciones = $arrReduc;

                       if($refund_id == 1 || $refund_id == 2){

                           $folio_viatics = 0;
                           $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                       }else{

                           $folio_viatics = $folio_viatics_invoice_transfer;
                           $folio_invoice_transfer = 0;
                       }

                       $issue_date = date_create($issue_date);
                       $issue_date = date_format($issue_date, 'Y-m-d');

                       $auth_date = date_create($auth_date);
                       $auth_date = date_format($auth_date, 'Y-m-d');

                       $keytable = 1;
                       $rowItems = 0;

                       foreach ($dtaReducciones as $val){
                          // foreach ($val as $values){
                               for($x=0;$x<count($dataJsonMeses);$x++){

                                   $mesData = (int)$val['mes'];

                                   if($mesData == $x+1){

                                        if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                            if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                            // $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                $arrayError[] = array('message' => 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                            }

                                        }else{

                                            if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                            // $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                $arrayError[] = array('message' => 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                            }else{
                                                if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){

                                                    if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                    //  $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                        $arrayError[] = array('message' => 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                                    }

                                                }else{
                                                    if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){
                                                    // $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                        $arrayError[] = array('message' => 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);

                                                    }
                                                }
                                            }

                                        }//If Principal

                                   }

                               }

                               if($val['sequence_siaff'] == '' || $val['sequence_siaff'] == null || $val['sequence_siaff'] == 0 || $val['sequence_siaff'] == '0'){
                                // array_push($arrayError,$arrayError[$rowItems]['message'] = 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                                $arrayError[] = array('message' => 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                            }

                          // }

                           $keytable++;
                           $rowItems++;
                           $folioTR = $val['folioTranfer'];
                       }

                       if(count($arrayError) > 0){

                           $msg = array('message' => $arrayError,'tipo' => 'error');
                           echo json_encode($msg);
                           exit();
                       }else{


                           $arrFolio = json_decode($arrayFolio,true);
                           $folArray = $arrFolio;

                           if(count($folArray) > 0){

                               $allFolio = '';

                               if(in_array($folioTR,$folArray)){
                                   $allFolio = $folioTR;
                               }

                               // for($q=0;$q<count($folArray);$q++){
                               //   $allFolio .= $folArray[$q].",";
                               // }

                               //$allFolio = trim($allFolio, ',');

                               $folio_viatics_invoice_transfer = $allFolio;

                           }

                           // nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'



                           $SQL = "INSERT INTO tb_refunds_notice (ur_id, ue_id, line_capture_TESOFE, tracking_code, process_siaff, transfer_number, refund_id, folio_viatics, folio_invoice_transfer, type_payment, justification, sn_funcion_id, issue_date, status_refund, nu_anio_fiscal) VALUES ('".$ur_id."', null, '".$line_Tesofe."', '".$tracking_code."', null, null, '".$refund_id."', null, '".$folio_viatics_invoice_transfer."', null, '".$justification."', '".$funcion."', '".$issue_date."', '".$status_refund."', '".$_SESSION['ejercicioFiscal']."')";

                           $ErrMsg = "No se pudo almacenar la información";
                           $TransResult = DB_query($SQL, $db, $ErrMsg);
                       }



                       //Selecionar Ultimo Registro

                       if($TransResult == true){ // Si la Sentencia Insert es Satisfactoria

                           $SelectLastID = "SELECT MAX(id) FROM tb_refunds_notice";
                           $ResultLastSelect = DB_query($SelectLastID, $db, $ErrMsg);
                           while($rowIdLast=DB_fetch_array($ResultLastSelect)){
                               $idLast = trim($rowIdLast[0]);
                           }

                           //insert Partidas

                           foreach($dtaReducciones as $valu){
                              // foreach($valu as $items){
                                   for($v=0;$v<count($dataJsonMeses);$v++){

                                       $mesData = (int)$valu['mes'];


                                       if($mesData == $v+1){

                                           if($dataJsonMeses[$v] == 'Enero'){

                                               $dateP = $valu['año'].'-'.'01';
                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                   $periodS = $rowsPeriod['periodno'];
                                               }
                                           }else{
                                               if($dataJsonMeses[$v] == 'Febrero'){

                                                   $dateP = $valu['año'].'-'.'02';
                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                       $periodS = $rowsPeriod['periodno'];
                                                   }
                                               }else{
                                                   if($dataJsonMeses[$v] == 'Marzo'){

                                                       $dateP = $valu['año'].'-'.'03';
                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                           $periodS = $rowsPeriod['periodno'];
                                                       }
                                                   }else{
                                                       if($dataJsonMeses[$v] == 'Abril'){
                                                           $dateP = $valu['año'].'-'.'04';
                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                               $periodS = $rowsPeriod['periodno'];
                                                           }

                                                       }else{
                                                           if($dataJsonMeses[$v] == 'Mayo'){
                                                               $dateP = $valu['año'].'-'.'05';
                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                   $periodS = $rowsPeriod['periodno'];
                                                               }
                                                           }else{
                                                               if($dataJsonMeses[$v] == 'Junio'){
                                                                   $dateP = $valu['año'].'-'.'06';
                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                       $periodS = $rowsPeriod['periodno'];
                                                                   }
                                                               }else{
                                                                   if($dataJsonMeses[$v] == 'Julio'){
                                                                       $dateP = $valu['año'].'-'.'07';
                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                           $periodS = $rowsPeriod['periodno'];
                                                                       }
                                                                   }else{
                                                                       if($dataJsonMeses[$v] == 'Agosto'){
                                                                           $dateP = $valu['año'].'-'.'08';
                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                               $periodS = $rowsPeriod['periodno'];
                                                                           }
                                                                       }else{
                                                                           if($dataJsonMeses[$v] == 'Septiembre'){
                                                                               $dateP = $valu['año'].'-'.'09';
                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                               }

                                                                           }else{
                                                                               if($dataJsonMeses[$v] == 'Octubre'){
                                                                                   $dateP = $valu['año'].'-'.'10';
                                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                       $periodS = $rowsPeriod['periodno'];
                                                                                   }
                                                                               }else{
                                                                                   if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                       $dateP = $valu['año'].'-'.'11';
                                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                           $periodS = $rowsPeriod['periodno'];
                                                                                       }
                                                                                   }else{
                                                                                       if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                           $dateP = $valu['año'].'-'.'12';
                                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                               $periodS = $rowsPeriod['periodno'];
                                                                                           }
                                                                                       }
                                                                                   }
                                                                               }
                                                                           }
                                                                       }
                                                                   }
                                                               }
                                                           }
                                                       }
                                                   }
                                               }
                                           }


                                           $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                           $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                           $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                           $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                           $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                           $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                           $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                           $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                           $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                           $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                           $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                           $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                           $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                           $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                           //$arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 266;
                                           $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 268;
                                           $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                           $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                           $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                           $arrayTotal[$dataJsonMeses[$v]]['transno'] = $idLast;
                                           $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                           $arrayTotal[$dataJsonMeses[$v]]['sequenceSIAFF'] = $valu['sequence_siaff'];
                                           $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];

                                       }

                                   }
                              // }



                               foreach ($arrayTotal as $valItems){

                                        if($valItems['sequenceSIAFF'] == '' || $valItems['sequenceSIAFF'] == null){
                                            $sqsSIAFF = 0;
                                        }else{
                                            $sqsSIAFF = $valItems['sequenceSIAFF'];
                                        }

                                      fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0,
                                       1, 0, '', 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $folio_viatics_invoice_transfer, $valItems['type_refund'],$sqsSIAFF,$valItems['folio_Tranfer'],0);

                               }

                           }

                           $msg = array('message' => 'El reintegro con número de folio: '.$idLast.', '.' se registro correctamente','tipo'=>'success');
                           echo json_encode($msg);

                       }else{

                           $msg = array('message' => $ErrMsg,'tipo'=>'error');
                           echo json_encode($msg);
                       }

                   }else{
                       if($refund_id == 2){
                          // CODIGO RADICADO


                           $arrReduc = json_decode($dtRedc,true);
                           $dtaReducciones = $arrReduc;

                           if($refund_id == 1 || $refund_id == 2){

                               $folio_viatics = 0;
                               $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                           }else{

                               $folio_viatics = $folio_viatics_invoice_transfer;
                               $folio_invoice_transfer = 0;
                           }

                           $issue_date = date_create($issue_date);
                           $issue_date = date_format($issue_date, 'Y-m-d');

                           $auth_date = date_create($auth_date);
                           $auth_date = date_format($auth_date, 'Y-m-d');

                           $keytable = 1;
                           $rowItems = 0;

                           foreach ($dtaReducciones as $val){
                               //foreach ($val as $values){
                                   for($x=0;$x<count($dataJsonMeses);$x++){

                                       $mesData = (int)$val['mes'];

                                       if($mesData == $x+1){

                                           if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                               if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                   $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                               }

                                           }else{

                                               if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                                   $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                               }else{
                                                   if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){

                                                       if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                           $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                       }

                                                   }else{
                                                       if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){
                                                           $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;

                                                       }
                                                   }
                                               }

                                           }//If Principal

                                       }

                                   }

                             //  }

                               $keytable++;
                               $rowItems++;
                               $folioTR = $val['folioTranfer'];
                           }

                           if(count($arrayError) > 0){

                               $msg = array('message' => $arrayError,'tipo' => 'error');
                               echo json_encode($msg);
                               exit();
                           }else{

                               $arrFolio = json_decode($arrayFolio,true);
                               $folArray = $arrFolio;

                               if(count($folArray) > 0){

                                   $allFolio = '';

                                   if(in_array($folioTR,$folArray)){
                                       $allFolio = $folioTR;
                                   }



                                   // for($q=0;$q<count($folArray);$q++){
                                   //   $allFolio .= $folArray[$q].",";
                                   // }

                                   //$allFolio = trim($allFolio, ',');

                                   $folio_viatics_invoice_transfer = $allFolio;

                               }

                               // nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                               $SQL = "INSERT INTO tb_refunds_notice (ur_id, ue_id, line_capture_TESOFE, tracking_code, process_siaff, transfer_number, refund_id, folio_viatics, folio_invoice_transfer, type_payment, justification, sn_funcion_id, issue_date, status_refund, nu_anio_fiscal) VALUES ('".$ur_id."', '".$ue_id."', null, '".$tracking_code."', null, '".$transfer_number."', '".$refund_id."', '".$folio_viatics."', '".$folio_viatics_invoice_transfer."', null, '".$justification."', '".$funcion."', '".$issue_date."', '".$status_refund."', '".$_SESSION['ejercicioFiscal']."')";

                               $ErrMsg = "No se pudo almacenar la información";
                               $TransResult = DB_query($SQL, $db, $ErrMsg);
                           }


                           if($TransResult == true){ // Si la Sentencia Insert es Satisfactoria

                               $SelectLastID = "SELECT MAX(id) FROM tb_refunds_notice";
                               $ResultLastSelect = DB_query($SelectLastID, $db, $ErrMsg);
                               while($rowIdLast=DB_fetch_array($ResultLastSelect)){
                                   $idLast = trim($rowIdLast[0]);
                               }

                               //insert Partidas

                               foreach($dtaReducciones as $valu){
                                   //foreach($valu as $items){
                                       for($v=0;$v<count($dataJsonMeses);$v++){

                                           $mesData = (int)$valu['mes'];


                                           if($mesData == $v+1){

                                               if($dataJsonMeses[$v] == 'Enero'){

                                                   $dateP = $valu['año'].'-'.'01';
                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                       $periodS = $rowsPeriod['periodno'];
                                                   }
                                               }else{
                                                   if($dataJsonMeses[$v] == 'Febrero'){

                                                       $dateP = $valu['año'].'-'.'02';
                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                           $periodS = $rowsPeriod['periodno'];
                                                       }
                                                   }else{
                                                       if($dataJsonMeses[$v] == 'Marzo'){

                                                           $dateP = $valu['año'].'-'.'03';
                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                               $periodS = $rowsPeriod['periodno'];
                                                           }
                                                       }else{
                                                           if($dataJsonMeses[$v] == 'Abril'){
                                                               $dateP = $valu['año'].'-'.'04';
                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                   $periodS = $rowsPeriod['periodno'];
                                                               }

                                                           }else{
                                                               if($dataJsonMeses[$v] == 'Mayo'){
                                                                   $dateP = $valu['año'].'-'.'05';
                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                       $periodS = $rowsPeriod['periodno'];
                                                                   }
                                                               }else{
                                                                   if($dataJsonMeses[$v] == 'Junio'){
                                                                       $dateP = $valu['año'].'-'.'06';
                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                           $periodS = $rowsPeriod['periodno'];
                                                                       }
                                                                   }else{
                                                                       if($dataJsonMeses[$v] == 'Julio'){
                                                                           $dateP = $valu['año'].'-'.'07';
                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                               $periodS = $rowsPeriod['periodno'];
                                                                           }
                                                                       }else{
                                                                           if($dataJsonMeses[$v] == 'Agosto'){
                                                                               $dateP = $valu['año'].'-'.'08';
                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                               }
                                                                           }else{
                                                                               if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                   $dateP = $valu['año'].'-'.'09';
                                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                       $periodS = $rowsPeriod['periodno'];
                                                                                   }

                                                                               }else{
                                                                                   if($dataJsonMeses[$v] == 'Octubre'){
                                                                                       $dateP = $valu['año'].'-'.'10';
                                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                           $periodS = $rowsPeriod['periodno'];
                                                                                       }
                                                                                   }else{
                                                                                       if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                           $dateP = $valu['año'].'-'.'11';
                                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                               $periodS = $rowsPeriod['periodno'];
                                                                                           }
                                                                                       }else{
                                                                                           if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                               $dateP = $valu['año'].'-'.'12';
                                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                                               }
                                                                                           }
                                                                                       }
                                                                                   }
                                                                               }
                                                                           }
                                                                       }
                                                                   }
                                                               }
                                                           }
                                                       }
                                                   }
                                               }


                                               $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                               $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                               $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                               $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                               $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                               $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                               $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                               $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                               $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                               $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                               $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                               $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                               $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                               $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                               //$arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 269;
                                               $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 271;
                                               $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                               $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                               $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                               $arrayTotal[$dataJsonMeses[$v]]['transno'] = $idLast;
                                               $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                               $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];


                                           }



                                       }
                                  // }

                                   foreach ($arrayTotal as $valItems){

                                       fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0,
                                           1, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $folio_viatics_invoice_transfer, $valItems['type_refund'],0,$valItems['folio_Tranfer'],0);

                                   }

                               }

                               //insert

                               $msg = array('message' => 'El reintegro con número de folio: '.$idLast.', '.' se registro correctamente','tipo'=>'success');
                               echo json_encode($msg);

                           }else{

                               $msg = array('message' => $ErrMsg,'tipo'=>'error');
                               echo json_encode($msg);
                           }


                          // FIN CODIGO RADICADO
                       }else{
                           if($refund_id == 3){

                               $arrReduc = json_decode($dtRedc,true);
                               $dtaReducciones = $arrReduc;

                               if($refund_id == 1 || $refund_id == 2){

                                   $folio_viatics = 0;
                                   $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                               }else{

                                   $folio_viatics = $folio_viatics_invoice_transfer;
                                   $folio_invoice_transfer = 0;
                               }

                               $issue_date = date_create($issue_date);
                               $issue_date = date_format($issue_date, 'Y-m-d');

                               $auth_date = date_create($auth_date);
                               $auth_date = date_format($auth_date, 'Y-m-d');

                               // Inicio Insert Enzabezado

                               $keytable = 1;
                               $rowItems = 0;

                               foreach ($dtaReducciones as $val){
                                 //  foreach ($val as $values){
                                       for($x=0;$x<count($dataJsonMeses);$x++){

                                           $mesData = (int)$val['mes'];

                                           if($mesData == $x+1){

                                               if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                                   if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                       $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                   }

                                               }else{

                                                   if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                                       $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                   }else{
                                                       if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){

                                                           if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                               $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                           }

                                                       }else{
                                                           if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] == 0){
                                                               $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;

                                                           }
                                                       }
                                                   }

                                               }//If Principal



                                           }

                                       }

                                  // }

                                   $keytable++;
                                   $rowItems++;

                                   $folioTR = $val['folioTranfer'];

                               }


                               if(count($arrayError) > 0){

                                   $msg = array('message' => $arrayError,'tipo' => 'error');
                                   echo json_encode($msg);
                                   exit();
                               }else{


                                   $arrFolio = json_decode($arrayFolio,true);
                                   $folArray = $arrFolio;

                                   if(count($folArray) > 0){

                                       $allFolio = '';

                                       if(in_array($folioTR,$folArray)){
                                           $allFolio = $folioTR;
                                       }

                                      // for($q=0;$q<count($folArray);$q++){
                                        //   $allFolio .= $folArray[$q].",";
                                      // }

                                       //$allFolio = trim($allFolio, ',');

                                       $folio_viatics_invoice_transfer = $allFolio;

                                   }
                                  // print_r($folio_viatics_invoice_transfer);
                                   //exit();
                                   // nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'

                                   $SQL = "INSERT INTO tb_refunds_notice (ur_id, ue_id, line_capture_TESOFE, tracking_code, process_siaff, transfer_number, refund_id, folio_viatics, folio_invoice_transfer, type_payment, justification, sn_funcion_id, issue_date, status_refund, nu_anio_fiscal) VALUES ('".$ur_id."', '".$ue_id."', null, '".$tracking_code."', null, null, '".$refund_id."', '".$folio_viatics."', '".$folio_viatics_invoice_transfer."', '".$type_payments."', '".$justification."', '".$funcion."', '".$issue_date."', '".$status_refund."', '".$_SESSION['ejercicioFiscal']."')";

                                   $ErrMsg = "No se pudo almacenar la información";
                                   $TransResult = DB_query($SQL, $db, $ErrMsg);
                               }



                               if($TransResult == true){ // Si la Sentencia Insert es Satisfactoria

                                   $SelectLastID = "SELECT MAX(id) FROM tb_refunds_notice";
                                   $ResultLastSelect = DB_query($SelectLastID, $db, $ErrMsg);
                                   while($rowIdLast=DB_fetch_array($ResultLastSelect)){
                                       $idLast = trim($rowIdLast[0]);
                                   }


                                   foreach($dtaReducciones as $valu){
                                       //foreach($valu as $items){
                                           for($v=0;$v<count($dataJsonMeses);$v++){

                                               if($valu[$dataJsonMeses[$v].'Sel'] != 0){

                                                   if($dataJsonMeses[$v] == 'Enero'){

                                                       $dateP = $valu['año'].'-'.'01';
                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                           $periodS = $rowsPeriod['periodno'];
                                                       }
                                                   }else{
                                                       if($dataJsonMeses[$v] == 'Febrero'){

                                                           $dateP = $valu['año'].'-'.'02';
                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                               $periodS = $rowsPeriod['periodno'];
                                                           }
                                                       }else{
                                                           if($dataJsonMeses[$v] == 'Marzo'){

                                                               $dateP = $valu['año'].'-'.'03';
                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                   $periodS = $rowsPeriod['periodno'];
                                                               }
                                                           }else{
                                                               if($dataJsonMeses[$v] == 'Abril'){
                                                                   $dateP = $valu['año'].'-'.'04';
                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                       $periodS = $rowsPeriod['periodno'];
                                                                   }

                                                               }else{
                                                                   if($dataJsonMeses[$v] == 'Mayo'){
                                                                       $dateP = $valu['año'].'-'.'05';
                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                           $periodS = $rowsPeriod['periodno'];
                                                                       }
                                                                   }else{
                                                                       if($dataJsonMeses[$v] == 'Junio'){
                                                                           $dateP = $valu['año'].'-'.'06';
                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                               $periodS = $rowsPeriod['periodno'];
                                                                           }
                                                                       }else{
                                                                           if($dataJsonMeses[$v] == 'Julio'){
                                                                               $dateP = $valu['año'].'-'.'07';
                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                               }
                                                                           }else{
                                                                               if($dataJsonMeses[$v] == 'Agosto'){
                                                                                   $dateP = $valu['año'].'-'.'08';
                                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                       $periodS = $rowsPeriod['periodno'];
                                                                                   }
                                                                               }else{
                                                                                   if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                       $dateP = $valu['año'].'-'.'09';
                                                                                       $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                       $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                       while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                           $periodS = $rowsPeriod['periodno'];
                                                                                       }
                                                                                   }else{
                                                                                       if($dataJsonMeses[$v] == 'Octubre'){
                                                                                           $dateP = $valu['año'].'-'.'10';
                                                                                           $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                           $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                           while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                               $periodS = $rowsPeriod['periodno'];
                                                                                           }
                                                                                       }else{
                                                                                           if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                               $dateP = $valu['año'].'-'.'11';
                                                                                               $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                               $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                               while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                   $periodS = $rowsPeriod['periodno'];
                                                                                               }
                                                                                           }else{
                                                                                               if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                                   $dateP = $valu['año'].'-'.'12';
                                                                                                   $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                   $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                   while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                       $periodS = $rowsPeriod['periodno'];
                                                                                                   }
                                                                                               }
                                                                                           }
                                                                                       }
                                                                                   }
                                                                               }
                                                                           }
                                                                       }
                                                                   }
                                                               }
                                                           }
                                                       }
                                                   }


                                                   $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 265;
                                                   $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                                   $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                                   $arrayTotal[$dataJsonMeses[$v]]['transno'] = $idLast;
                                                   $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['no_Devengado'] = $valu['noDevengado'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['no_Compromiso'] = $valu['noCompromiso'];
                                                   $arrayTotal[$dataJsonMeses[$v]]['no_Retencion'] = $valu['noRetencion'];



                                               }



                                           }
                                      // }
                                       foreach ($arrayTotal as $valItems){
                                           fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0, 1, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1,'', '', 0, $valItems['folio_Tranfer'], $refund_id,0,$valItems['folio_Tranfer'],0,$type_payments);
                                          // fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$idLast, 0, 1, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1,'', '', 0, $valItems['folio_Tranfer'], $refund_id,0,$valItems['folio_Tranfer']);

                                       }

                                   }

                                   $msg = array('message' => 'El reintegro con número de folio: '.$idLast.', '.' se registro correctamente','tipo'=>'success');
                                   echo json_encode($msg);

                               }else{

                                   $msg = array('message' => $ErrMsg,'tipo'=>'error');
                                   echo json_encode($msg);
                               }

                           }//fin tipo 3
                       }//fin tipo 2
                   }//fin tipo 1



               }//fin Mode <=1
           }

        } catch (Exception $error){

           $msg = array('message' => $error->getMessage(),'tipo'=>'error');
           echo json_encode($msg);

           //echo 'Excepcion Capturada: ', $error->getMessage(), "\n";

        }
    }
}

// Busqueda de informacion Para Realiazar Reintegros

if(isset($_GET['FolioTransf'])){

    try{

        $typeRefundss = mysqli_real_escape_string($db, $_GET['typeRefunds']);

        if($typeRefundss == 1){

            $sqlQuery = "";

            $NumberFolioTransf = mysqli_real_escape_string($db, $_GET['FolioTransf']);
            $UnitBusiness = mysqli_real_escape_string($db, $_GET['unitBusiness']);

            $queryHeader =  "SELECT tb_ministracion.id, tb_ministracion.folio, tb_ministracion.ln_clcSiaff, tb_ministracion.ln_clcGRP, tb_ministracion.ln_clcSicop, tb_ministracion.estatus, tb_ministracion_detalle.presupuesto, tb_ministracion_detalle.autorizado, 
                             chartdetailsbudgetlog.transno, chartdetailsbudgetlog.type, SUM(chartdetailsbudgetlog.qty) AS QTR, chartdetailsbudgetlog.period, chartdetailsbudgetlog.nu_secuencia_siaff FROM tb_ministracion
                             JOIN tb_ministracion_detalle ON tb_ministracion.id = tb_ministracion_detalle.idMinistracion
                             JOIN chartdetailsbudgetlog ON tb_ministracion.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 291 AND chartdetailsbudgetlog.qty > 0 AND tb_ministracion_detalle.presupuesto = chartdetailsbudgetlog.cvefrom
                             WHERE tb_ministracion.ln_ur = '".$UnitBusiness."' AND tb_ministracion.folio = ".$NumberFolioTransf." AND tb_ministracion.estatus = 5 AND tb_ministracion.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                             GROUP BY tb_ministracion_detalle.presupuesto";

            $ErrMsg = "No se realizo la Consulta";
            $ResultHeader = DB_query($queryHeader, $db, $ErrMsg);

            $info = [];
           // $legalid = $rowresults['legalid'];
            $datosClave = 1;

            while ($rowHeader = DB_fetch_array($ResultHeader)) {
                $IdHeader = $rowHeader['id'];
                $folioH= $rowHeader['folio'];
                $clave = $rowHeader['presupuesto'];
                $period = $rowHeader['period'];
                $total = $rowHeader['QTR'];
                $ln_ln_clcSiaff = $rowHeader['ln_clcSiaff'];
                $ln_ln_clcGRP = $rowHeader['ln_clcGRP'];
                $ln_ln_clcSicop = $rowHeader['ln_clcSicop'];
                $sequence_Siaff = $rowHeader['nu_secuencia_siaff'];

                $info[] = fnInfoPresupuesto($db, $clave, '', '', '', $datosClave, 0, '', 293, $IdHeader, 'Ampliacion', $folioH, $UnitBusiness, '',0,'','','',$typeRefundss, $ln_ln_clcSiaff, $ln_ln_clcGRP, $ln_ln_clcSicop, $sequence_Siaff,1);

            }

            $resultData = array('info' => $info, 'type_refunds' => $typeRefundss);
            echo json_encode($resultData);

        }else{

            if($typeRefundss == 3){


                $sqlQuery = "";
                //$searchRecti = "";
                $totalRegister=0;

                $NumberFolioTransf = mysqli_real_escape_string($db, $_GET['FolioTransf']);
                $UnitBusiness = mysqli_real_escape_string($db, $_GET['unitBusiness']);
                $UnitExecuting = mysqli_real_escape_string($db, $_GET['unitExecuting']);
                $typePayment = mysqli_real_escape_string($db, $_GET['typepayment']);
                $typeRefundss = mysqli_real_escape_string($db, $_GET['typeRefunds']);

                $totalRectificaciones = 0;

                if($NumberFolioTransf != ''){
                    $sqlQuery .= " AND supptrans.suppreference = ".$NumberFolioTransf." ";
                }

                if($UnitBusiness != ''){
                    $sqlQuery .= " AND supptrans.tagref = '".$UnitBusiness."' ";
                }

                if($UnitExecuting != ''){
                    $sqlQuery .= " AND supptrans.ln_ue = ".$UnitExecuting." ";
                }


                   $querySearch = "SELECT supptrans.id, supptrans.tagref, supptrans.type, supptrans.transno, supptrans.ln_ue, suppallocs.transid_allocfrom, suppallocs.transid_allocto,
                                    supptransdetails.supptransid, supptransdetails.description, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
                                    CAST(SUM(supptransdetails.qty * supptransdetails.price) as decimal(16,4)) AS totalGeneral, SUM(supptransdetails.price) AS Prices, tags.legalid
                                    FROM supptrans 
                                    JOIN suppallocs ON suppallocs.transid_allocfrom = supptrans.id
                                    JOIN supptransdetails ON suppallocs.transid_allocto = supptransdetails.supptransid
                                    JOIN supptrans supptransDocPago ON supptransDocPago.id = supptransdetails.supptransid
                                    JOIN tags ON tags.tagref = supptrans.tagref
                                    WHERE supptrans.type = 22 ".$sqlQuery." AND supptransDocPago.type = ".$typePayment." AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                    GROUP BY supptransdetails.clavepresupuestal";


                $ErrMsg = "No se realizo la Consulta";
                $ResultSearch = DB_query($querySearch, $db, $ErrMsg);

                $period = '';
                $info = [];
                $totalGeneral_query = 0;
                $totalQTY = 0;
                $dtaTotal = 0;
                $rectiTotal = 0;
                while ($rowresults = DB_fetch_array($ResultSearch)) {

                    $types = mysqli_real_escape_string($db, $rowresults['type']);
                    $transno = mysqli_real_escape_string($db, $rowresults['transno']);

                    $dtaTotal = $rowresults['totalGeneral'];

                    if($period == ''){

                        $querySearchtype = "SELECT DISTINCT period, lastdate_in_period FROM chartdetailsbudgetlog 
                                       JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
                                       WHERE type = '".$types."' AND transno = '".$transno."'";

                        $ResultPeriod = DB_query($querySearchtype, $db, $ErrMsg);
                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                            $period = $rowsPeriod['period'];
                            $datePeriod = $rowsPeriod['lastdate_in_period'];
                        }

                        $month = strtotime($datePeriod);
                        $mes = date("m", $month);

                        if($mes != '10' || $mes != '11' || $mes != '12'){
                            $mes = substr($mes,1);
                        }
                        $queryPeriod = "SELECT mes AS Mo FROM cat_Months WHERE u_mes = '".$mes."'";
                        $ResultMonth = DB_query($queryPeriod, $db, $ErrMsg);
                        while ($rowsMonth = DB_fetch_array($ResultMonth)){
                            $monthPeriod = $rowsMonth['Mo'];
                        }

                    }

                    $clave = $rowresults['clavepresupuestal'];
                    $legalid = $rowresults['legalid'];
                    $datosClave = 1;


                    $queryHeaderRefunds = "SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.ue_id, tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.status_refund,
                                            chartdetailsbudgetlog.type, chartdetailsbudgetlog.estatus, SUM(chartdetailsbudgetlog.qty) AS TQ, chartdetailsbudgetlog.period FROM tb_refunds_notice
                                            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                                            WHERE chartdetailsbudgetlog.type = 293 AND chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.period = '".$period."'
                                            AND tb_refunds_notice.status_refund = 4 AND tb_refunds_notice.folio_invoice_transfer = '".$NumberFolioTransf."' 
                                            AND tb_refunds_notice.ur_id = '".$UnitBusiness."' AND tb_refunds_notice.ue_id = '".$UnitExecuting."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                            GROUP BY chartdetailsbudgetlog.estatus";

                    $resultQueryQTY = DB_query($queryHeaderRefunds, $db, $ErrMsg);

                    if(DB_num_rows($resultQueryQTY) > 0){

                        while ($rowResultQTY = DB_fetch_array($resultQueryQTY)){
                            $totalQTY = $rowResultQTY['TQ'];
                            //$totalGeneral_query = $rowresults['totalGeneral'] - $rowResultQTY['qty'];
                        }

                    }else{

                        $totalQTY = 0;
                    }



                    $searchRecti="SELECT tb_rectificaciones.nu_type, tb_rectificaciones.nu_transno, tb_rectificaciones.sn_tagref, 
                              tb_rectificaciones.ln_ue, tb_rectificaciones.nu_type_pago, tb_rectificaciones.nu_folio_pago, chartdetailsbudgetlog.transno,
                              chartdetailsbudgetlog.type, 
                              CAST(SUM(chartdetailsbudgetlog.qty) as decimal(16,4)) AS total_rectifi, chartdetailsbudgetlog.cvefrom, 
                              chartdetailsbudgetlog.partida_esp
                              FROM tb_rectificaciones
                              LEFT JOIN chartdetailsbudgetlog ON tb_rectificaciones.nu_type = chartdetailsbudgetlog.type AND tb_rectificaciones.nu_transno = chartdetailsbudgetlog.transno
                              WHERE tb_rectificaciones.nu_type_pago = 22 AND tb_rectificaciones.nu_folio_pago = ".$NumberFolioTransf."
                              AND tb_rectificaciones.sn_tagref = '".$UnitBusiness."' AND tb_rectificaciones.ln_ue = ".$UnitExecuting."
                              AND tb_rectificaciones.nu_estatus = 4 AND chartdetailsbudgetlog.qty > 0 AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                              GROUP BY chartdetailsbudgetlog.cvefrom";

                    $ErrMsg = "No se realizo la Consulta";
                    $resultRectificacion = DB_query($searchRecti, $db, $ErrMsg);


                   // $arr = Array();

                    if(DB_num_rows($resultRectificacion) > 0){

                        while($rowRectificacion = DB_fetch_array($resultRectificacion)){

                            if($clave == $rowRectificacion['cvefrom']){

                                 $searchRectic="SELECT tb_rectificaciones.nu_type, tb_rectificaciones.nu_transno, tb_rectificaciones.sn_tagref,
                                                tb_rectificaciones.ln_ue, tb_rectificaciones.nu_type_pago, tb_rectificaciones.nu_folio_pago, chartdetailsbudgetlog.transno,
                                                chartdetailsbudgetlog.type, CAST(SUM(chartdetailsbudgetlog.qty) as decimal(16,4)) AS total_rectifi, chartdetailsbudgetlog.cvefrom, 
                                                chartdetailsbudgetlog.partida_esp
                                                FROM tb_rectificaciones
                                                LEFT JOIN chartdetailsbudgetlog ON tb_rectificaciones.nu_type = chartdetailsbudgetlog.type AND tb_rectificaciones.nu_transno = chartdetailsbudgetlog.transno
                                                WHERE tb_rectificaciones.nu_type_pago = 22 AND tb_rectificaciones.nu_folio_pago = ".$NumberFolioTransf."
                                                AND tb_rectificaciones.sn_tagref = '".$UnitBusiness."' AND tb_rectificaciones.ln_ue = ".$UnitExecuting."
                                                AND tb_rectificaciones.nu_estatus = 4 AND chartdetailsbudgetlog.qty < 0 AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                                GROUP BY chartdetailsbudgetlog.cvefrom ";

                                $ErrMsg = "No se realizo la Consulta";
                                $resultRectificacionc = DB_query($searchRectic, $db, $ErrMsg);

                                while($rowRectificacionc = DB_fetch_array($resultRectificacionc)){

                                        $totalClaves = $dtaTotal + $rowRectificacionc['total_rectifi'];

                                        if($totalClaves == 0){

                                            $clave = $rowRectificacionc['cvefrom'];

                                        }else{

                                            if($totalClaves > 0){

                                                $info[] = fnInfoPresupuesto($db, $rowRectificacionc['cvefrom'], '', '', $legalid, $datosClave, 0, '', 293, '', 'Ampliacion', $NumberFolioTransf, $UnitBusiness, $UnitExecuting,0,'','','',$typeRefundss,'','','',0,1);

                                            }


                                        }

                                }
                            }

                        }

                    }

                    $totalGeneral_query = $dtaTotal - $totalQTY;

                    $info[] = fnInfoPresupuesto($db, $clave, '', '', $legalid, $datosClave, 0, '', 293, '', 'Ampliacion', $NumberFolioTransf, $UnitBusiness, $UnitExecuting,0,'','','',$typeRefundss,'','','',0,1);

                }


                $resultData = array('info' => $info, 'period' => $period,' datePeriod' => $datePeriod, 'mes' => $mes, 'month' => $monthPeriod,'types' => $types, 'transno' => $transno, 'ttlGeneral' => $totalGeneral_query, 'RR' => $ResultSearch, 'tt' =>$dtaTotal,'ttquery'=>$totalQTY, 'type_refunds' => $typeRefundss);
                echo json_encode($resultData);

            }else{
                if($typeRefundss == 2){

                    $sqlQuery = "";

                    $NumberFolioTransf = mysqli_real_escape_string($db, $_GET['FolioTransf']);
                    $UnitBusiness = mysqli_real_escape_string($db, $_GET['unitBusiness']);
                    $UnitExecuting = mysqli_real_escape_string($db, $_GET['unitExecuting']);
                    $typePayment = mysqli_real_escape_string($db, $_GET['typepayment']);
                    $typeRefundss = mysqli_real_escape_string($db, $_GET['typeRefunds']);


                    $queryHeader = "SELECT tb_radicacion.id,tb_radicacion.ln_ur,tb_radicacion.ln_ue,tb_radicacion.folio,tb_radicacion.estatus,tb_radicacion_detalle.presupuesto,tb_radicacion_detalle.autorizado,
                                    chartdetailsbudgetlog.transno, chartdetailsbudgetlog.type, SUM(chartdetailsbudgetlog.qty) AS QTR, chartdetailsbudgetlog.period FROM tb_radicacion
                                    JOIN tb_radicacion_detalle ON tb_radicacion.id = tb_radicacion_detalle.idRadicacion
                                    JOIN chartdetailsbudgetlog ON tb_radicacion.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 292 AND chartdetailsbudgetlog.qty > 0 AND tb_radicacion_detalle.presupuesto = chartdetailsbudgetlog.cvefrom
                                    WHERE tb_radicacion.ln_ur = '".$UnitBusiness."' AND tb_radicacion.ln_ue = ".$UnitExecuting." AND tb_radicacion.folio = ".$NumberFolioTransf." AND tb_radicacion.estatus = 5 AND tb_radicacion.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                    GROUP BY tb_radicacion_detalle.presupuesto";


                    $ErrMsg = "No se realizo la Consulta";
                    $ResultHeader = DB_query($queryHeader, $db, $ErrMsg);

                    $info = [];
                    // $legalid = $rowresults['legalid'];
                    $datosClave = 1;

                    while ($rowHeader = DB_fetch_array($ResultHeader)) {
                        $IdHeader = $rowHeader['id'];
                        $folioH= $rowHeader['folio'];
                        $clave = $rowHeader['presupuesto'];
                        $period = $rowHeader['period'];
                        $total = $rowHeader['QTR'];


                        $info[] = fnInfoPresupuesto($db, $clave, '', '', '', $datosClave, 0, '', 293, $IdHeader, 'Ampliacion', $folioH, $UnitBusiness, $UnitExecuting,0,'','','',$typeRefundss,'','','',0,1);

                    }

                    $resultData = array('info' => $info, 'type_refunds' => $typeRefundss);
                    echo json_encode($resultData);

                }
            }
        }

    }catch (Exception $error){
        echo 'Excepcion Capturada: ', $error->getMessage(), "\n";
    }

}

if(isset($_GET['FolioTransfNoCapture'])){

   try{

       $NumberFolioTransf = mysqli_real_escape_string($db, $_GET['FolioTransfNoCapture']);
       $transnO = mysqli_real_escape_string($db, $_GET['idRefund']);
       $typE = mysqli_real_escape_string($db, $_GET['refundType']);
       $UnitBusiness = mysqli_real_escape_string($db, $_GET['unitBusiness']);
       $UnitExecuting = mysqli_real_escape_string($db, $_GET['unitExecuting']);
       $tpgs = mysqli_real_escape_string($db, $_GET['tpg']);

       $Tr = mysqli_real_escape_string($db, $_GET['typeunitRefund']);

      /* print_r($NumberFolioTransf);
       echo "\n\n\n";
       print_r($transnO);
       echo "\n\n\n";
       print_r($typE);
       echo "\n\n\n";
       print_r($UnitBusiness);
       echo "\n\n\n";
       print_r($UnitExecuting);
       echo "\n\n\n";
       print_r($Tr);
       echo "\n\n\n";
       print_r($tpgs);

       exit();*/

if($Tr == 1){

        $fols = explode(",", $NumberFolioTransf);
        $info = [];


        for($a=0;$a<count($fols);$a++){

                $queryHeader =  "SELECT tb_ministracion.id, tb_ministracion.folio, tb_ministracion.ln_clcSiaff, tb_ministracion.ln_clcGRP, tb_ministracion.ln_clcSicop, tb_ministracion.estatus, tb_ministracion_detalle.presupuesto, tb_ministracion_detalle.autorizado, 
                                         chartdetailsbudgetlog.transno, chartdetailsbudgetlog.type, SUM(chartdetailsbudgetlog.qty) AS QTR, chartdetailsbudgetlog.period, chartdetailsbudgetlog.nu_secuencia_siaff FROM tb_ministracion
                                         JOIN tb_ministracion_detalle ON tb_ministracion.id = tb_ministracion_detalle.idMinistracion
                                         JOIN chartdetailsbudgetlog ON tb_ministracion.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 291 AND chartdetailsbudgetlog.qty > 0 AND tb_ministracion_detalle.presupuesto = chartdetailsbudgetlog.cvefrom
                                         WHERE tb_ministracion.ln_ur = '".$UnitBusiness."' AND tb_ministracion.folio = ".$fols[$a]." AND tb_ministracion.estatus = 5 AND tb_ministracion.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                         GROUP BY tb_ministracion_detalle.presupuesto";

                $ErrMsg = "No se realizo la Consulta";
                $ResultHeader = DB_query($queryHeader, $db, $ErrMsg);

                // $legalid = $rowresults['legalid'];
                $datosClave = 1;

                while ($rowHeader = DB_fetch_array($ResultHeader)) {

                        $IdHeader = $rowHeader['id'];
                        $folioH= $rowHeader['folio'];
                        $clave = $rowHeader['presupuesto'];
                        $period = $rowHeader['period'];
                        $total = $rowHeader['QTR'];
                        $transNOF = $rowHeader['transno'];
                        $ln_ln_clcSiaff = $rowHeader['ln_clcSiaff'];
                        $ln_ln_clcGRP = $rowHeader['ln_clcGRP'];
                        $ln_ln_clcSicop = $rowHeader['ln_clcSicop'];
                        $sequence_Siaff = $rowHeader['nu_secuencia_siaff'];


                    $info[] = fnInfoPresupuesto($db, $clave, '', '', '', $datosClave, 0, '', 293, $transnO, 'Ampliacion', $folioH, $UnitBusiness, '',0,'','','', $Tr, $ln_ln_clcSiaff, $ln_ln_clcGRP, $ln_ln_clcSicop, $sequence_Siaff,1);

                }
        }


    }else{

        if($Tr == 2){

            $fols = explode(",", $NumberFolioTransf);
            $info = [];


            for($a=0;$a<count($fols);$a++) {

                $queryHeader = "SELECT tb_radicacion.id,tb_radicacion.ln_ur,tb_radicacion.ln_ue,tb_radicacion.folio,tb_radicacion.estatus,tb_radicacion_detalle.presupuesto,tb_radicacion_detalle.autorizado,
                                    chartdetailsbudgetlog.transno, chartdetailsbudgetlog.type, SUM(chartdetailsbudgetlog.qty) AS QTR, chartdetailsbudgetlog.period FROM tb_radicacion
                                    JOIN tb_radicacion_detalle ON tb_radicacion.id = tb_radicacion_detalle.idRadicacion
                                    JOIN chartdetailsbudgetlog ON tb_radicacion.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 292 AND chartdetailsbudgetlog.qty > 0 AND tb_radicacion_detalle.presupuesto = chartdetailsbudgetlog.cvefrom
                                    WHERE tb_radicacion.ln_ur = '" . $UnitBusiness . "' AND tb_radicacion.ln_ue = " . $UnitExecuting . " AND tb_radicacion.folio = ".$fols[$a]." AND tb_radicacion.estatus = 5 AND tb_radicacion.nu_anio_fiscal = '" . $_SESSION['ejercicioFiscal'] . "'
                                    GROUP BY tb_radicacion_detalle.presupuesto";


                $ErrMsg = "No se realizo la Consulta";
                $ResultHeader = DB_query($queryHeader, $db, $ErrMsg);

               // $info = [];
                // $legalid = $rowresults['legalid'];
                $datosClave = 1;

                while ($rowHeader = DB_fetch_array($ResultHeader)) {
                    $IdHeader = $rowHeader['id'];
                    $folioH = $rowHeader['folio'];
                    $clave = $rowHeader['presupuesto'];
                    $period = $rowHeader['period'];
                    $total = $rowHeader['QTR'];
                    $transNOF = $rowHeader['transno'];


                  /*    print_r($IdHeader);
                    echo "\n\n\n";
                   print_r($folioH);
                    echo "\n\n\n";
                   print_r($clave);
                    echo "\n\n\n";
                   print_r($total);
                    echo "\n\n\n";
                   print_r($transNOF);
                   */
                    $info[] = fnInfoPresupuesto($db, $clave, '', '', '', $datosClave, 0, '', 293, $transnO, 'Ampliacion', $folioH, $UnitBusiness, $UnitExecuting, 0, '', '', '', $Tr, '', '', '', '',1);

                }
            }

        }else{

            if($Tr == 3){

                $fols = explode(",", $NumberFolioTransf);
                $info = [];

                for($a=0;$a<count($fols);$a++) {

                    $searchRecti = "SELECT tb_rectificaciones.nu_type, tb_rectificaciones.nu_transno, tb_rectificaciones.sn_tagref,
                              tb_rectificaciones.ln_ue, tb_rectificaciones.nu_type_pago, tb_rectificaciones.nu_folio_pago, chartdetailsbudgetlog.transno,
                              chartdetailsbudgetlog.type, 
                              CAST(SUM(chartdetailsbudgetlog.qty) as decimal(16,4)) AS total_rectifi, chartdetailsbudgetlog.cvefrom, 
                              chartdetailsbudgetlog.partida_esp
                              FROM tb_rectificaciones
                              LEFT JOIN chartdetailsbudgetlog ON tb_rectificaciones.nu_type = chartdetailsbudgetlog.type AND tb_rectificaciones.nu_transno = chartdetailsbudgetlog.transno
                              WHERE tb_rectificaciones.nu_type_pago = 22 AND tb_rectificaciones.nu_folio_pago = ".$fols[$a]."
                              AND tb_rectificaciones.sn_tagref = '" . $UnitBusiness . "' AND tb_rectificaciones.ln_ue = " . $UnitExecuting . "
                              AND tb_rectificaciones.nu_estatus = 4 AND chartdetailsbudgetlog.qty < 0 AND tb_rectificaciones.nu_anio_fiscal = '" . $_SESSION['ejercicioFiscal'] . "'
                              GROUP BY chartdetailsbudgetlog.cvefrom";


                    $ErrMsg = "No se realizo la Consulta";
                    $resultRectificacion = DB_query($searchRecti, $db, $ErrMsg);


                    $querySearch = "SELECT supptrans.id, supptrans.tagref, supptrans.type, supptrans.transno, supptrans.ln_ue, suppallocs.transid_allocfrom, suppallocs.transid_allocto,
                            supptransdetails.supptransid, supptransdetails.description, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
                            CAST(SUM(supptransdetails.qty * supptransdetails.price) as decimal(16,4)) AS totalGeneral, SUM(supptransdetails.price) AS Prices, tags.legalid
                            FROM supptrans 
                            JOIN suppallocs ON suppallocs.transid_allocfrom = supptrans.id
                            JOIN supptransdetails ON suppallocs.transid_allocto = supptransdetails.supptransid
                            JOIN tags ON tags.tagref = supptrans.tagref
                            WHERE type = 22 AND supptrans.suppreference = ".$fols[$a]." AND supptrans.tagref = '".$UnitBusiness."' AND supptrans.ln_ue = ".$UnitExecuting."  AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                            GROUP BY supptransdetails.clavepresupuestal";

                    // JOIN supptrans supptransDocPago ON supptransDocPago.id = supptransdetails.supptransid
                    // AND supptrans.tagref = '".$UnitBusiness."' AND supptrans.ln_ue = ".$UnitExecuting." AND supptransDocPago.type = ".$tpgs."
                    $ErrMsg = "No se realizo la Consulta";
                    $ResultSearch = DB_query($querySearch, $db, $ErrMsg);

                    $period = '';
                   // $info = [];
                    $totalGeneral_query = 0;
                    while ($rowresults = DB_fetch_array($ResultSearch)) {

                        $types = $typE;
                        $transno = $transnO;

                        $dtaTotal = $rowresults['totalGeneral'];

                        if ($period == '') {

                            $querySearchtype = "SELECT DISTINCT period, lastdate_in_period FROM chartdetailsbudgetlog 
                                           JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
                                           WHERE type = '" . $types . "' AND transno = '" . $transno . "'";

                            $ResultPeriod = DB_query($querySearchtype, $db, $ErrMsg);
                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)) {
                                $period = $rowsPeriod['period'];
                                $datePeriod = $rowsPeriod['lastdate_in_period'];
                            }

                            $month = strtotime($datePeriod);
                            $mes = date("m", $month);

                            if ($mes != '10' || $mes != '11' || $mes != '12') {
                                $mes = substr($mes, 1);
                            }
                            $queryPeriod = "SELECT mes FROM cat_Months WHERE u_mes = '" . $mes . "'";
                            $ResultMonth = DB_query($queryPeriod, $db, $ErrMsg);
                            while ($rowsMonth = DB_fetch_array($ResultMonth)) {
                                $monthPeriod = $rowsMonth['mes'];
                            }

                        }

                        $clave = $rowresults['clavepresupuestal'];
                        $legalid = $rowresults['legalid'];
                        $datosClave = 1;


                        $queryHeaderRefundsAuth = "SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.line_capture_TESOFE, tb_refunds_notice.ue_id, tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.status_refund,
                                                            chartdetailsbudgetlog.type, chartdetailsbudgetlog.estatus, SUM(chartdetailsbudgetlog.qty) AS QTYS, chartdetailsbudgetlog.period FROM tb_refunds_notice
                                                            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                                                            WHERE tb_refunds_notice.folio_invoice_transfer LIKE '%".$fols[$a]."%' AND chartdetailsbudgetlog.type = 293 AND chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.period = '" . $period . "'
                                                            AND tb_refunds_notice.status_refund = 4  AND tb_refunds_notice.ur_id = '" . $UnitBusiness . "' AND tb_refunds_notice.nu_anio_fiscal = '" . $_SESSION['ejercicioFiscal'] . "'
                                                            GROUP BY chartdetailsbudgetlog.estatus";

                        $ErrMsg = "No se pudo almacenar la información";
                        $ResultAuth = DB_query($queryHeaderRefundsAuth, $db, $ErrMsg);

                        if (DB_num_rows($ResultAuth) > 0) {
                            while ($myrowResAuth = DB_fetch_array($ResultAuth)) {
                                $totalAuth = $myrowResAuth['QTYS'];
                            }
                        } else {
                            $totalAuth = 0;
                        }


                        // $arr = Array();   GROUP BY chartdetailsbudgetlog.cvefrom CONCAT('%',$fols[$a],'%')

                        if (DB_num_rows($resultRectificacion) > 0) {

                            while ($rowRectificacion = DB_fetch_array($resultRectificacion)) {

                                $sht = "SELECT chartdetailsbudgetlog.cvefrom 
                               FROM chartdetailsbudgetlog 
                               WHERE  chartdetailsbudgetlog.folio = ".$fols[$a]." AND chartdetailsbudgetlog.transno = " . $transnO . "
                               AND chartdetailsbudgetlog.type = " . $typE . " AND chartdetailsbudgetlog.cvefrom = '" . $rowRectificacion['cvefrom'] . "'
                                GROUP BY chartdetailsbudgetlog.cvefrom";


                                $ErrMsg = "No se realizo la Consulta";
                                $rview = DB_query($sht, $db, $ErrMsg);


                                if (DB_num_rows($rview) > 0) {

                                    if ($rowsViews['cvefrom'] = $rowRectificacion['cvefrom']) {

                                        while ($rowsViews = DB_fetch_array($rview)) {
                                            $clave_2 = $rowsViews['cvefrom'];
                                            $info[] = fnInfoPresupuesto($db, $clave_2, '', '', $legalid, $datosClave, 0, '', $typE, $transnO, 'Ampliacion', $fols[$a], $UnitBusiness, $UnitExecuting, 0, '', '', '', $Tr,'','','',0,1,$tpgs);


                                        }


                                    }

                                }


                            }

                        }


                        $totalGeneral_query = $dtaTotal - $totalAuth;

                        $info[] = fnInfoPresupuesto($db, $clave, '', '', $legalid, $datosClave, 0, '', $typE, $transnO, 'Ampliacion', $fols[$a], $UnitBusiness, $UnitExecuting, 0, '', '', '', $Tr,'','','',0,1,$tpgs);


                    }

                }

                //var_dump($info);
               // exit();

        }//fin 3
    }//fin 2
}//fin 1


       $resultData = array('info' => $info, 'period' => $period,'datePeriod' => $datePeriod, 'mes' => $mes, 'month' => $monthPeriod, 'types' => $typE, 'transno' => $transnO, 'ttlGeneral' => $totalGeneral_query, 'rauth' => $totalAuth,'foliosData' => $fols);
       echo json_encode($resultData);


   }catch(Exception $error){

       echo 'Excepcion Capturada: ', $error->getMessage(), "\n";
   }

}

if(isset($_POST['fundataup'])) {
    if ($_POST['fundataup'] == 'updateData') {

        try{

            $arrayMes = [];
            $arrayTotal = [];
            $totalMonth = 12;
            $dataJsonMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $ErrMsg = "No se pudo almacenar la información";
            $arrayError = [];


            $ur_id = mysqli_real_escape_string($db, $_POST['ur_id']);
            $ue_id = mysqli_real_escape_string($db, $_POST['ue_id']);
            $line_Tesofe = mysqli_real_escape_string($db, $_POST['lineTesofe']);
            $tracking_code = mysqli_real_escape_string($db, $_POST['tracking_code']);
            $process_siaff = mysqli_real_escape_string($db, $_POST['process_siaff']);
            $transfer_number = mysqli_real_escape_string($db, $_POST['transfer_number']);
            $refund_id = mysqli_real_escape_string($db, $_POST['refund_id']);
            $folio_viatics_invoice_transfer = mysqli_real_escape_string($db, $_POST['folio_viatics_invoice_transfer']);
            $justification = mysqli_real_escape_string($db, $_POST['justification']);
            $issue_date = $_POST['issue_date'];
            $auth_date = $_POST['auth_date'];
            $period = mysqli_real_escape_string($db, $_POST['period']);
            $type = mysqli_real_escape_string($db, $_POST['type']);
            $transno = mysqli_real_escape_string($db, $_POST['transno']);
            $status_refund = mysqli_real_escape_string($db, $_POST['idStatus']);
            $valueTotalGeneral = mysqli_real_escape_string($db, $_POST['valueTotalG']);
            $modes = mysqli_real_escape_string($db, $_POST['mode_refund']);

           // $dtaReducciones = $_POST['infoReduct'];
            $dtRedc = $_POST['infoReduct'];

            $arrayFolio = $_POST['folioselect'];


            //print_r($vclaves);
            //print_r($type);
            //print_r($transno);
            //print_r($dtRedc);

//////////////////////////////
            if($modes > 1){

                if($refund_id == 1){

                    $arrReduc = json_decode($dtRedc,true);
                    $dtaReducciones = $arrReduc;


                    if($refund_id == 1 || $refund_id == 2){

                        $folio_viatics = 0;
                        $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                    }else{

                        $folio_viatics = $folio_viatics_invoice_transfer;
                        $folio_invoice_transfer = 0;
                    }

                    $issue_date = date_create($issue_date);
                    $issue_date = date_format($issue_date, 'Y-m-d');

                    $auth_date = date_create($auth_date);
                    $auth_date = date_format($auth_date, 'Y-m-d');

                    $keytable = 1;
                    $rowItems = 0;


                    foreach ($dtaReducciones as $val){
                       // foreach ($val as $values){
                            for($x=0;$x<count($dataJsonMeses);$x++){

                                $mesData = (int)$val['mes'];

                                if($mesData == $x+1){

                                    if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                        if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                           // $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                            $arrayError[] = array('message' => 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                        }

                                    }else{

                                        if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                           // $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                            $arrayError[] = array('message' => 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                        }

                                    }//If Principal

                                }

                            }

                     //   }

                     
                        if($val['sequence_siaff'] == '' || $val['sequence_siaff'] == null || $val['sequence_siaff'] == 0 || $val['sequence_siaff'] == '0'){
                            // array_push($arrayError,$arrayError[$rowItems]['message'] = 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                            $arrayError[] = array('message' => 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                        }


                        $keytable++;
                        $rowItems++;
                        $folioTR[] = $val['folioTranfer'];
                    }


                    if(count($arrayError) > 0){

                        $msg = array('message' => $arrayError,'tipo' => 'error');
                        echo json_encode($msg);
                        exit();

                    }else{

                        $vclaves = '';

                       /*
                          AND chartdetailsbudgetlog.cvefrom = '".$vclaves."'
                       */

                        foreach($dtaReducciones as $vcla){

                            $vclaves = $vcla['accountcode'];

                            $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."'  ";

                            $ErrMsg = "No se pudo Eliminar la información";
                            $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);
                        }

                        if($ResultDelete == true){

                           /* $sqlUpdateHeader = "UPDATE tb_refunds_notice SET  tracking_code = '".$tracking_code."', process_siaff = '".$process_siaff."',
                                                          transfer_number = '".$transfer_number."', issue_date = '".$issue_date."', justification = '".$justification."', auth_date = null, status_refund = '".$status_refund."' updateData
                                                          WHERE id = '".$transno."'"; */

                            /*$arrFolio = json_decode($arrayFolio,true);
                            $folArray = $arrFolio;

                            if(count($folArray) > 0){

                                $allFolio = '';

                                for($q=0;$q<count($folArray);$q++){
                                    $allFolio .= $folArray[$q].",";
                                }

                                $allFolio = trim($allFolio, ',');

                                $folio_viatics_invoice_transfer = $allFolio;

                            }*/

                            $unicos = array_unique($folioTR);
                            $unicomas = array_values($unicos);
                            if(count($unicomas) > 1){

                                $allFolio = '';

                                for($q=0;$q<count($unicomas);$q++){
                                    $allFolio .= $unicomas[$q].",";
                                }

                                $allFolio = trim($allFolio, ',');

                                $folio_viatics_invoice_transfer = $allFolio;

                            }else{
                                if(count($unicos)<=1){
                                    $allFolio = '';

                                    for($q=0;$q<count($unicos);$q++){
                                        $allFolio .= $unicos[$q].",";
                                    }

                                    $allFolio = trim($allFolio, ',');

                                    $folio_viatics_invoice_transfer = $allFolio;
                                }


                            }

                            $sqlUpdateHeader = "UPDATE tb_refunds_notice SET folio_invoice_transfer = '".$folio_viatics_invoice_transfer."', line_capture_TESOFE = '".$line_Tesofe."', tracking_code = '".$tracking_code."', issue_date = '".$issue_date."', auth_date = null, justification = '".$justification."' WHERE id = '".$transno."'";


                            $ErrMsg = "No se pudo almacenar la información";
                            $TransResult = DB_query($sqlUpdateHeader, $db, $ErrMsg);

                            //insert Partidas

                            foreach($dtaReducciones as $valu){
                               // foreach($valu as $items){
                                    for($v=0;$v<count($dataJsonMeses);$v++){

                                        $mesData = (int)$valu['mes'];

                                        if($mesData == $v+1){

                                            if($dataJsonMeses[$v] == 'Enero'){

                                                $dateP = $valu['año'].'-'.'01';
                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                    $periodS = $rowsPeriod['periodno'];
                                                }
                                            }else{
                                                if($dataJsonMeses[$v] == 'Febrero'){

                                                    $dateP = $valu['año'].'-'.'02';
                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                        $periodS = $rowsPeriod['periodno'];
                                                    }
                                                }else{
                                                    if($dataJsonMeses[$v] == 'Marzo'){

                                                        $dateP = $valu['año'].'-'.'03';
                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                            $periodS = $rowsPeriod['periodno'];
                                                        }
                                                    }else{
                                                        if($dataJsonMeses[$v] == 'Abril'){
                                                            $dateP = $valu['año'].'-'.'04';
                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                $periodS = $rowsPeriod['periodno'];
                                                            }

                                                        }else{
                                                            if($dataJsonMeses[$v] == 'Mayo'){
                                                                $dateP = $valu['año'].'-'.'05';
                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                    $periodS = $rowsPeriod['periodno'];
                                                                }
                                                            }else{
                                                                if($dataJsonMeses[$v] == 'Junio'){
                                                                    $dateP = $valu['año'].'-'.'06';
                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                        $periodS = $rowsPeriod['periodno'];
                                                                    }
                                                                }else{
                                                                    if($dataJsonMeses[$v] == 'Julio'){
                                                                        $dateP = $valu['año'].'-'.'07';
                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                            $periodS = $rowsPeriod['periodno'];
                                                                        }
                                                                    }else{
                                                                        if($dataJsonMeses[$v] == 'Agosto'){
                                                                            $dateP = $valu['año'].'-'.'08';
                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                $periodS = $rowsPeriod['periodno'];
                                                                            }
                                                                        }else{
                                                                            if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                $dateP = $valu['año'].'-'.'09';
                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                }

                                                                            }else{
                                                                                if($dataJsonMeses[$v] == 'Octubre'){
                                                                                    $dateP = $valu['año'].'-'.'10';
                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                    }
                                                                                }else{
                                                                                    if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                        $dateP = $valu['año'].'-'.'11';
                                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                            $periodS = $rowsPeriod['periodno'];
                                                                                        }
                                                                                    }else{
                                                                                        if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                            $dateP = $valu['año'].'-'.'12';
                                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                $periodS = $rowsPeriod['periodno'];
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }


                                            $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                            $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                            $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                            $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                            $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                            $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                            $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                            $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                            $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                            $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                            $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                            $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                            $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                            $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                            //$arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 266;
                                            $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 268;
                                            $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                            $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                            $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                            $arrayTotal[$dataJsonMeses[$v]]['transno'] = $transno;
                                            $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                            $arrayTotal[$dataJsonMeses[$v]]['sequenceSIAFF'] = $valu['sequence_siaff'];
                                            $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];

                                        }



                                    }
                              //  }

                                foreach ($arrayTotal as $valItems){

                                    if($valItems['sequenceSIAFF'] == '' || $valItems['sequenceSIAFF'] == null){
                                        $sqsSIAFF = 0;
                                    }else{
                                        $sqsSIAFF = $valItems['sequenceSIAFF'];
                                    }

                                    fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$valItems['transno'], 0, $status_refund, 0, '', 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'],$sqsSIAFF,$valItems['folio_Tranfer']);

                                }

                            }



                        }


                    }

                   $msg = array('message' => 'Registros Actualizados Correctamente','tipo'=>'success');
                   echo json_encode($msg);


                }else{
                    if($refund_id == 2){
                        // Codigo RADICADO UPDATE


                        $arrReduc = json_decode($dtRedc,true);
                        $dtaReducciones = $arrReduc;


                        if($refund_id == 1 || $refund_id == 2){

                            $folio_viatics = 0;
                            $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                        }else{

                            $folio_viatics = $folio_viatics_invoice_transfer;
                            $folio_invoice_transfer = 0;
                        }

                        $issue_date = date_create($issue_date);
                        $issue_date = date_format($issue_date, 'Y-m-d');

                        $auth_date = date_create($auth_date);
                        $auth_date = date_format($auth_date, 'Y-m-d');

                        $keytable = 1;
                        $rowItems = 0;

                        foreach ($dtaReducciones as $val){
                          //  foreach ($val as $values){
                                for($x=0;$x<count($dataJsonMeses);$x++){

                                    $mesData = (int)$val['mes'];

                                    if($mesData == $x+1){

                                        if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                            if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                            }

                                        }else{

                                            if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                                $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                            }

                                        }//If Principal

                                    }

                                }

                          //  }

                            $keytable++;
                            $rowItems++;
                            $folioTR[] = $val['folioTranfer'];
                        }

                        if(count($arrayError) > 0){

                            $msg = array('message' => $arrayError,'tipo' => 'error');
                            echo json_encode($msg);
                            exit();

                        }else{

                            $vclaves = '';

                            foreach($dtaReducciones as $vcla){

                                $vclaves = $vcla['accountcode'];

                                $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' AND chartdetailsbudgetlog.cvefrom = '".$vclaves."' ";

                                $ErrMsg = "No se pudo Eliminar la información";
                                $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);
                            }


                          /*  $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' ";

                            $ErrMsg = "No se pudo Eliminar la información";
                            $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);*/


                            if($ResultDelete == true){

                               /* $sqlUpdateHeader = "UPDATE tb_refunds_notice SET ue_id = null , tracking_code = '".$tracking_code."', process_siaff = '".$process_siaff."',
                                                           transfer_number = '".$transfer_number."', issue_date = '".$issue_date."', auth_date = null, status_refund = '".$status_refund."' 
                                                           WHERE id = '".$transno."'";*/

                               /* $arrFolio = json_decode($arrayFolio,true);
                                $folArray = $arrFolio;

                                if(count($folArray) > 0){

                                    $allFolio = '';

                                    for($q=0;$q<count($folArray);$q++){
                                        $allFolio .= $folArray[$q].",";
                                    }

                                    $allFolio = trim($allFolio, ',');

                                    $folio_viatics_invoice_transfer = $allFolio;

                                }*/

                                $unicos = array_unique($folioTR);
                                $unicomas = array_values($unicos);
                                if(count($unicomas) > 1){

                                    $allFolio = '';

                                    for($q=0;$q<count($unicomas);$q++){
                                        $allFolio .= $unicomas[$q].",";
                                    }

                                    $allFolio = trim($allFolio, ',');

                                    $folio_viatics_invoice_transfer = $allFolio;

                                }else{
                                    if(count($unicos)<=1){
                                        $allFolio = '';

                                        for($q=0;$q<count($unicos);$q++){
                                            $allFolio .= $unicos[$q].",";
                                        }

                                        $allFolio = trim($allFolio, ',');

                                        $folio_viatics_invoice_transfer = $allFolio;
                                    }


                                }

                                $sqlUpdateHeader="UPDATE tb_refunds_notice SET folio_invoice_transfer = '".$folio_viatics_invoice_transfer."', ur_id = '".$ur_id."', ue_id = '".$ue_id."', tracking_code = '".$tracking_code."', 
                                                         transfer_number = '".$transfer_number."', justification = '".$justification."',
                                                        issue_date = '".$issue_date."'
                                                        WHERE id = '".$transno."'";

                                $ErrMsg = "No se pudo almacenar la información";
                                $TransResult = DB_query($sqlUpdateHeader, $db, $ErrMsg);


                                //insert Partidas

                                foreach($dtaReducciones as $valu){
                                   // foreach($valu as $items){
                                        for($v=0;$v<count($dataJsonMeses);$v++){

                                            $mesData = (int)$valu['mes'];

                                            if($mesData == $v+1){

                                                if($dataJsonMeses[$v] == 'Enero'){

                                                    $dateP = $valu['año'].'-'.'01';
                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                        $periodS = $rowsPeriod['periodno'];
                                                    }
                                                }else{
                                                    if($dataJsonMeses[$v] == 'Febrero'){

                                                        $dateP = $valu['año'].'-'.'02';
                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                            $periodS = $rowsPeriod['periodno'];
                                                        }
                                                    }else{
                                                        if($dataJsonMeses[$v] == 'Marzo'){

                                                            $dateP = $valu['año'].'-'.'03';
                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                $periodS = $rowsPeriod['periodno'];
                                                            }
                                                        }else{
                                                            if($dataJsonMeses[$v] == 'Abril'){
                                                                $dateP = $valu['año'].'-'.'04';
                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                    $periodS = $rowsPeriod['periodno'];
                                                                }

                                                            }else{
                                                                if($dataJsonMeses[$v] == 'Mayo'){
                                                                    $dateP = $valu['año'].'-'.'05';
                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                        $periodS = $rowsPeriod['periodno'];
                                                                    }
                                                                }else{
                                                                    if($dataJsonMeses[$v] == 'Junio'){
                                                                        $dateP = $valu['año'].'-'.'06';
                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                            $periodS = $rowsPeriod['periodno'];
                                                                        }
                                                                    }else{
                                                                        if($dataJsonMeses[$v] == 'Julio'){
                                                                            $dateP = $valu['año'].'-'.'07';
                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                $periodS = $rowsPeriod['periodno'];
                                                                            }
                                                                        }else{
                                                                            if($dataJsonMeses[$v] == 'Agosto'){
                                                                                $dateP = $valu['año'].'-'.'08';
                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                }
                                                                            }else{
                                                                                if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                    $dateP = $valu['año'].'-'.'09';
                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                    }

                                                                                }else{
                                                                                    if($dataJsonMeses[$v] == 'Octubre'){
                                                                                        $dateP = $valu['año'].'-'.'10';
                                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                            $periodS = $rowsPeriod['periodno'];
                                                                                        }
                                                                                    }else{
                                                                                        if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                            $dateP = $valu['año'].'-'.'11';
                                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                $periodS = $rowsPeriod['periodno'];
                                                                                            }
                                                                                        }else{
                                                                                            if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                                $dateP = $valu['año'].'-'.'12';
                                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }


                                                $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                                $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                                $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                                $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                                $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                                $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                                $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                                $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                                $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                                $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                                $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                                $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                                $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                                $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                                //$arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 269;
                                                $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 271;
                                                $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                                $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                                $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                                $arrayTotal[$dataJsonMeses[$v]]['transno'] = $transno;
                                                $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                                $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];


                                            }



                                        }
                                   // }

                                    foreach ($arrayTotal as $valItems){

                                        fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$valItems['transno'], 0, $status_refund, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'],0,$valItems['folio_Tranfer']);

                                    }

                                }

                            }


                        }

                        $msg = array('message' => 'Registros Actualizados Correctamente','tipo'=>'success');
                        echo json_encode($msg);

                        // FIN CODIGO RADICADO UPDATE
                    }else{
                        if($refund_id == 3){

                            $arrReduc = json_decode($dtRedc,true);
                            $dtaReducciones = $arrReduc;


                            if($refund_id == 1 || $refund_id == 2){

                                $folio_viatics = 0;
                                $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                            }else{

                                $folio_viatics = $folio_viatics_invoice_transfer;
                                $folio_invoice_transfer = 0;
                            }

                            $issue_date = date_create($issue_date);
                            $issue_date = date_format($issue_date, 'Y-m-d');

                            $auth_date = date_create($auth_date);
                            $auth_date = date_format($auth_date, 'Y-m-d');

                            $keytable = 1;
                            $rowItems = 0;

                            foreach ($dtaReducciones as $val){
                               // foreach ($val as $values){
                                    for($x=0;$x<count($dataJsonMeses);$x++){

                                        $mesData = (int)$val['mes'];

                                        if($mesData == $x+1){

                                            if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                                if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                    $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                }

                                            }else{

                                                if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                                    $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                }

                                            }//If Principal

                                        }

                                    }

                             //   }

                                $keytable++;
                                $rowItems++;
                                $folioTR[] = $val['folioTranfer'];
                            }// FIN FOR VALIDACIONES


                            if(count($arrayError) > 0){
                                $msg = array('message' => $arrayError,'tipo' => 'error');
                                echo json_encode($msg);
                                exit();

                            }else{

                                $vclaves = '';

                                foreach($dtaReducciones as $vcla){

                                    $vclaves = $vcla['accountcode'];

                                    $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' ";

                                    $ErrMsg = "No se pudo Eliminar la información";
                                    $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);
                                }

                               /* $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' ";

                                $ErrMsg = "No se pudo Eliminar la información";
                                $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);*/

                                if($ResultDelete == true){

                                    $unicos = array_unique($folioTR);
                                    $unicomas = array_values($unicos);
                                    if(count($unicomas) > 1){

                                        $allFolio = '';

                                        for($q=0;$q<count($unicomas);$q++){
                                            $allFolio .= $unicomas[$q].",";
                                        }

                                        $allFolio = trim($allFolio, ',');

                                        $folio_viatics_invoice_transfer = $allFolio;

                                    }else{
                                        if(count($unicos)<=1){
                                            $allFolio = '';

                                            for($q=0;$q<count($unicos);$q++){
                                                $allFolio .= $unicos[$q].",";
                                            }

                                            $allFolio = trim($allFolio, ',');

                                            $folio_viatics_invoice_transfer = $allFolio;
                                        }


                                    }

                                   // $arrFolio = json_decode($arrayFolio,true);
                                   // $folArray = $arrFolio

                                   /* $arrFolio = json_decode($arrayFolio,true);
                                    $folArray = $arrFolio;

                                    if(count($folArray) > 0){

                                        $allFolio = '';

                                        for($q=0;$q<count($folArray);$q++){
                                            $allFolio .= $folArray[$q].",";
                                        }

                                        $allFolio = trim($allFolio, ',');

                                        $folio_viatics_invoice_transfer = $allFolio;

                                    }*/

                                    $sqlUpdateHeader = "UPDATE tb_refunds_notice SET folio_invoice_transfer = '".$folio_viatics_invoice_transfer."', ur_id = '".$ur_id."', ue_id = '".$ue_id."', tracking_code = '".$tracking_code."', 
                                                        justification = '".$justification."',
                                                        issue_date = '".$issue_date."'
                                                        WHERE id = '".$transno."'";

                                    $ErrMsg = "No se pudo almacenar la información";
                                    $TransResult = DB_query($sqlUpdateHeader, $db, $ErrMsg);


                                        //insert Partidas

                                    foreach($dtaReducciones as $valu){
                                        //foreach($valu as $items){
                                            for($v=0;$v<count($dataJsonMeses);$v++){

                                                $mesData = (int)$valu['mes'];

                                                if($mesData == $v+1){

                                                    if($dataJsonMeses[$v] == 'Enero'){

                                                        $dateP = $valu['año'].'-'.'01';
                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                            $periodS = $rowsPeriod['periodno'];
                                                        }
                                                    }else{
                                                        if($dataJsonMeses[$v] == 'Febrero'){

                                                            $dateP = $valu['año'].'-'.'02';
                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                $periodS = $rowsPeriod['periodno'];
                                                            }
                                                        }else{
                                                            if($dataJsonMeses[$v] == 'Marzo'){

                                                                $dateP = $valu['año'].'-'.'03';
                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                    $periodS = $rowsPeriod['periodno'];
                                                                }
                                                            }else{
                                                                if($dataJsonMeses[$v] == 'Abril'){
                                                                    $dateP = $valu['año'].'-'.'04';
                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                        $periodS = $rowsPeriod['periodno'];
                                                                    }

                                                                }else{
                                                                    if($dataJsonMeses[$v] == 'Mayo'){
                                                                        $dateP = $valu['año'].'-'.'05';
                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                            $periodS = $rowsPeriod['periodno'];
                                                                        }
                                                                    }else{
                                                                        if($dataJsonMeses[$v] == 'Junio'){
                                                                            $dateP = $valu['año'].'-'.'06';
                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                $periodS = $rowsPeriod['periodno'];
                                                                            }
                                                                        }else{
                                                                            if($dataJsonMeses[$v] == 'Julio'){
                                                                                $dateP = $valu['año'].'-'.'07';
                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                }
                                                                            }else{
                                                                                if($dataJsonMeses[$v] == 'Agosto'){
                                                                                    $dateP = $valu['año'].'-'.'08';
                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                    }
                                                                                }else{
                                                                                    if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                        $dateP = $valu['año'].'-'.'09';
                                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                            $periodS = $rowsPeriod['periodno'];
                                                                                        }

                                                                                    }else{
                                                                                        if($dataJsonMeses[$v] == 'Octubre'){
                                                                                            $dateP = $valu['año'].'-'.'10';
                                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                $periodS = $rowsPeriod['periodno'];
                                                                                            }
                                                                                        }else{
                                                                                            if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                                $dateP = $valu['año'].'-'.'11';
                                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                                }
                                                                                            }else{
                                                                                                if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                                    $dateP = $valu['año'].'-'.'12';
                                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }


                                                    $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 265;
                                                    $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                                    $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                                    $arrayTotal[$dataJsonMeses[$v]]['transno'] = $transno;
                                                    $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                                    $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];


                                                }



                                            }
                                       // }

                                        foreach ($arrayTotal as $valItems){

                                            fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$valItems['transno'], 0, $status_refund, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'],0,$valItems['folio_Tranfer']);

                                        }

                                    }

                                    $msg = array('message' => 'Registros Actualizados Correctamente','tipo'=>'success');
                                    echo json_encode($msg);

                                  // FIn Insert Partidas
                                }
                            }

                        }//Fin If UPDATE REFUNDS TIPO 3
                    }
                }

            }else{
                if($modes <= 1 ){

                    if($refund_id == 1){

                        $arrReduc = json_decode($dtRedc,true);
                        $dtaReducciones = $arrReduc;


                        if($refund_id == 1 || $refund_id == 2){

                            $folio_viatics = 0;
                            $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                        }else{

                            $folio_viatics = $folio_viatics_invoice_transfer;
                            $folio_invoice_transfer = 0;
                        }

                        $issue_date = date_create($issue_date);
                        $issue_date = date_format($issue_date, 'Y-m-d');

                        $auth_date = date_create($auth_date);
                        $auth_date = date_format($auth_date, 'Y-m-d');

                        $keytable = 1;
                        $rowItems = 0;

                        foreach ($dtaReducciones as $val){
                           // foreach ($val as $values){
                                for($x=0;$x<count($dataJsonMeses);$x++){

                                    $mesData = (int)$val['mes'];

                                    if($mesData == $x+1){

                                        if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                            if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                //$arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                $arrayError[] = array('message' => 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                            }

                                        }else{

                                            if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                             //   $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                $arrayError[] = array('message' => 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable);
                                            }

                                        }//If Principal

                                    }

                                }

                          //  }

                                if($val['sequence_siaff'] == '' || $val['sequence_siaff'] == null || $val['sequence_siaff'] == 0 || $val['sequence_siaff'] == '0'){
                                    // array_push($arrayError,$arrayError[$rowItems]['message'] = 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                                    $arrayError[] = array('message' => 'La secuencia CLC SIAFF de la Partida'.' '.$val['partida_esp'].' '.'en el renglon'.' '.$keytable.' '.'no debe ser 0 o vacia');
                                }

                            $keytable++;
                            $rowItems++;
                            $folioTR = $val['folioTranfer'];
                        }// FIN FOR VALIDACIONES

                        if(count($arrayError) > 0){

                            $msg = array('message' => $arrayError,'tipo' => 'error');
                            echo json_encode($msg);
                            exit();

                        }else{

                            $vclaves = '';

                            foreach($dtaReducciones as $vcla){

                                $vclaves = $vcla['accountcode'];

                            // AND chartdetailsbudgetlog.cvefrom = '".$vclaves."'
                            $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' ";

                            $ErrMsg = "No se pudo Eliminar la información";
                            $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);
                            }

                            if($ResultDelete == true){

                               // $msg = array('tracking_code' =>$tracking_code, 'process_siaff' => $process_siaff, 'transfer_number' => $transfer_number, 'issue_date' =>$issue_date, 'auth_date' => null, 'status_refund' => $status_refund, 'id' => $transno);
                               //print_r($msg);
                               // exit();

                               /* $arrFolio = json_decode($arrayFolio,true);
                                $folArray = $arrFolio;

                                if(count($folArray) > 0){

                                    $allFolio = '';

                                    for($q=0;$q<count($folArray);$q++){
                                        $allFolio .= $folArray[$q].",";
                                    }

                                    $allFolio = trim($allFolio, ',');

                                    $folio_viatics_invoice_transfer = $allFolio;

                                }*/

                                $arrFolio = json_decode($arrayFolio,true);
                                $folArray = $arrFolio;

                                if(count($folArray) > 0){

                                    $allFolio = '';

                                    if(in_array($folioTR,$folArray)){
                                        $allFolio = $folioTR;
                                    }



                                    // for($q=0;$q<count($folArray);$q++){
                                    //   $allFolio .= $folArray[$q].",";
                                    // }

                                    //$allFolio = trim($allFolio, ',');  $folioTR = $val['folioTranfer'];

                                    $folio_viatics_invoice_transfer = $allFolio;

                                }

                                $sqlUpdateHeader = "UPDATE tb_refunds_notice SET folio_invoice_transfer = '".$folio_viatics_invoice_transfer."', line_capture_TESOFE = '".$line_Tesofe."', tracking_code = '".$tracking_code."', issue_date = '".$issue_date."', auth_date = null, justification = '".$justification."' WHERE id = '".$transno."'";

                                $ErrMsg = "No se pudo almacenar la información";
                                $TransResult = DB_query($sqlUpdateHeader, $db, $ErrMsg);


                                //insert Partidas

                                foreach($dtaReducciones as $valu){

                                        for($v=0;$v<count($dataJsonMeses);$v++){

                                            $mesData = (int)$valu['mes'];

                                            if($mesData == $v+1){

                                                if($dataJsonMeses[$v] == 'Enero'){

                                                    $dateP = $valu['año'].'-'.'01';
                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                        $periodS = $rowsPeriod['periodno'];
                                                    }
                                                }else{
                                                    if($dataJsonMeses[$v] == 'Febrero'){

                                                        $dateP = $valu['año'].'-'.'02';
                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                            $periodS = $rowsPeriod['periodno'];
                                                        }
                                                    }else{
                                                        if($dataJsonMeses[$v] == 'Marzo'){

                                                            $dateP = $valu['año'].'-'.'03';
                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                $periodS = $rowsPeriod['periodno'];
                                                            }
                                                        }else{
                                                            if($dataJsonMeses[$v] == 'Abril'){
                                                                $dateP = $valu['año'].'-'.'04';
                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                    $periodS = $rowsPeriod['periodno'];
                                                                }

                                                            }else{
                                                                if($dataJsonMeses[$v] == 'Mayo'){
                                                                    $dateP = $valu['año'].'-'.'05';
                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                        $periodS = $rowsPeriod['periodno'];
                                                                    }
                                                                }else{
                                                                    if($dataJsonMeses[$v] == 'Junio'){
                                                                        $dateP = $valu['año'].'-'.'06';
                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                            $periodS = $rowsPeriod['periodno'];
                                                                        }
                                                                    }else{
                                                                        if($dataJsonMeses[$v] == 'Julio'){
                                                                            $dateP = $valu['año'].'-'.'07';
                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                $periodS = $rowsPeriod['periodno'];
                                                                            }
                                                                        }else{
                                                                            if($dataJsonMeses[$v] == 'Agosto'){
                                                                                $dateP = $valu['año'].'-'.'08';
                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                }
                                                                            }else{
                                                                                if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                    $dateP = $valu['año'].'-'.'09';
                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                    }

                                                                                }else{
                                                                                    if($dataJsonMeses[$v] == 'Octubre'){
                                                                                        $dateP = $valu['año'].'-'.'10';
                                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                            $periodS = $rowsPeriod['periodno'];
                                                                                        }
                                                                                    }else{
                                                                                        if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                            $dateP = $valu['año'].'-'.'11';
                                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                $periodS = $rowsPeriod['periodno'];
                                                                                            }
                                                                                        }else{
                                                                                            if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                                $dateP = $valu['año'].'-'.'12';
                                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }


                                                $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                                $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                                $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                                $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                                $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                                $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                                $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                                $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                                $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                                $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                                $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                                $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                                $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                                $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                                //$arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 266;
                                                $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 268;
                                                $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                                $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                                $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                                $arrayTotal[$dataJsonMeses[$v]]['transno'] = $transno;
                                                $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                                $arrayTotal[$dataJsonMeses[$v]]['sequenceSIAFF'] = $valu['sequence_siaff'];
                                                $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];

                                            }

                                        }

                                }

                                    foreach ($arrayTotal as $valItems){

                                        if($valItems['sequenceSIAFF'] == '' || $valItems['sequenceSIAFF'] == null){
                                            $sqsSIAFF = 0;
                                        }else{
                                            $sqsSIAFF = $valItems['sequenceSIAFF'];
                                        }

                                        fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$valItems['transno'], 0, $status_refund, 0, '', 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'],$sqsSIAFF,$valItems['folio_Tranfer']);

                                    }


                                /////////// FIN DE Actualizador
                            }


                        }//fin else Principal

                        $msg = array('message' => 'Registros Actualizados Correctamente','tipo'=>'success');
                        echo json_encode($msg);

                    }else{
                        if($refund_id == 2){
                           // CODIGO UPDATE REFUNDS TIPO RADICADO

                            $arrReduc = json_decode($dtRedc,true);
                            $dtaReducciones = $arrReduc;


                            if($refund_id == 1 || $refund_id == 2){

                                $folio_viatics = 0;
                                $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                            }else{

                                $folio_viatics = $folio_viatics_invoice_transfer;
                                $folio_invoice_transfer = 0;
                            }

                            $issue_date = date_create($issue_date);
                            $issue_date = date_format($issue_date, 'Y-m-d');

                            $auth_date = date_create($auth_date);
                            $auth_date = date_format($auth_date, 'Y-m-d');

                            $keytable = 1;
                            $rowItems = 0;

                            foreach ($dtaReducciones as $val){
                               // foreach ($val as $values){
                                    for($x=0;$x<count($dataJsonMeses);$x++){

                                        $mesData = (int)$val['mes'];

                                        if($mesData == $x+1){

                                            if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                                if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                    $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                }

                                            }else{

                                                if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                                    $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                }

                                            }//If Principal

                                        }

                                    }

                              //  }

                                $keytable++;
                                $rowItems++;
                                $folioTR = $val['folioTranfer'];
                            }



                            if(count($arrayError) > 0){

                                $msg = array('message' => $arrayError,'tipo' => 'error');
                                echo json_encode($msg);
                                exit();

                            }else{


                                $vclaves = '';

                                foreach($dtaReducciones as $vcla){

                                    $vclaves = $vcla['accountcode'];


                                    $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."'  ";

                                    $ErrMsg = "No se pudo Eliminar la información";
                                    $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);
                                }


                               /* $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' ";

                                $ErrMsg = "No se pudo Eliminar la información";
                                $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);*/


                                if($ResultDelete == true){

                                   /* $arrFolio = json_decode($arrayFolio,true);
                                    $folArray = $arrFolio;

                                    if(count($folArray) > 0){

                                        $allFolio = '';

                                        for($q=0;$q<count($folArray);$q++){
                                            $allFolio .= $folArray[$q].",";
                                        }

                                        $allFolio = trim($allFolio, ',');

                                        $folio_viatics_invoice_transfer = $allFolio;

                                    }*/

                                    $arrFolio = json_decode($arrayFolio,true);
                                    $folArray = $arrFolio;

                                    if(count($folArray) > 0){

                                        $allFolio = '';

                                        if(in_array($folioTR,$folArray)){
                                            $allFolio = $folioTR;
                                        }



                                        // for($q=0;$q<count($folArray);$q++){
                                        //   $allFolio .= $folArray[$q].",";
                                        // }

                                        //$allFolio = trim($allFolio, ',');  $folioTR = $val['folioTranfer'];

                                        $folio_viatics_invoice_transfer = $allFolio;

                                    }

                                    $sqlUpdateHeader="UPDATE tb_refunds_notice SET folio_invoice_transfer = '".$folio_viatics_invoice_transfer."', ur_id = '".$ur_id."', ue_id = '".$ue_id."', tracking_code = '".$tracking_code."', 
                                                        transfer_number = '".$transfer_number."', justification = '".$justification."',
                                                        issue_date = '".$issue_date."'
                                                        WHERE id = '".$transno."'";

                                    $ErrMsg = "No se pudo almacenar la información";
                                    $TransResult = DB_query($sqlUpdateHeader, $db, $ErrMsg);


                                    //insert Partidas

                                    foreach($dtaReducciones as $valu){
                                       // foreach($valu as $items){
                                            for($v=0;$v<count($dataJsonMeses);$v++){

                                                $mesData = (int)$valu['mes'];

                                                if($mesData == $v+1){

                                                    if($dataJsonMeses[$v] == 'Enero'){

                                                        $dateP = $valu['año'].'-'.'01';
                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                            $periodS = $rowsPeriod['periodno'];
                                                        }
                                                    }else{
                                                        if($dataJsonMeses[$v] == 'Febrero'){

                                                            $dateP = $valu['año'].'-'.'02';
                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                $periodS = $rowsPeriod['periodno'];
                                                            }
                                                        }else{
                                                            if($dataJsonMeses[$v] == 'Marzo'){

                                                                $dateP = $valu['año'].'-'.'03';
                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                    $periodS = $rowsPeriod['periodno'];
                                                                }
                                                            }else{
                                                                if($dataJsonMeses[$v] == 'Abril'){
                                                                    $dateP = $valu['año'].'-'.'04';
                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                        $periodS = $rowsPeriod['periodno'];
                                                                    }

                                                                }else{
                                                                    if($dataJsonMeses[$v] == 'Mayo'){
                                                                        $dateP = $valu['año'].'-'.'05';
                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                            $periodS = $rowsPeriod['periodno'];
                                                                        }
                                                                    }else{
                                                                        if($dataJsonMeses[$v] == 'Junio'){
                                                                            $dateP = $valu['año'].'-'.'06';
                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                $periodS = $rowsPeriod['periodno'];
                                                                            }
                                                                        }else{
                                                                            if($dataJsonMeses[$v] == 'Julio'){
                                                                                $dateP = $valu['año'].'-'.'07';
                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                }
                                                                            }else{
                                                                                if($dataJsonMeses[$v] == 'Agosto'){
                                                                                    $dateP = $valu['año'].'-'.'08';
                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                    }
                                                                                }else{
                                                                                    if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                        $dateP = $valu['año'].'-'.'09';
                                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                            $periodS = $rowsPeriod['periodno'];
                                                                                        }

                                                                                    }else{
                                                                                        if($dataJsonMeses[$v] == 'Octubre'){
                                                                                            $dateP = $valu['año'].'-'.'10';
                                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                $periodS = $rowsPeriod['periodno'];
                                                                                            }
                                                                                        }else{
                                                                                            if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                                $dateP = $valu['año'].'-'.'11';
                                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                                }
                                                                                            }else{
                                                                                                if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                                    $dateP = $valu['año'].'-'.'12';
                                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }


                                                    $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                                    //$arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 269;
                                                    $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 271;
                                                    $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                                    $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                                    $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                                    $arrayTotal[$dataJsonMeses[$v]]['transno'] = $transno;
                                                    $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                                    $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];


                                                }



                                            }
                                       // }

                                        foreach ($arrayTotal as $valItems){

                                            fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$valItems['transno'], 0, $status_refund, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'],0,$valItems['folio_Tranfer']);

                                        }

                                    }

                                    /////////// FIN DE Actualizador
                                }


                            }

                            $msg = array('message' => 'Registros Actualizados Correctamente','tipo'=>'success');
                            echo json_encode($msg);



                            // FIN CODIGO RADICADO UPDATE
                        }else{
                            if($refund_id == 3){


                                $arrReduc = json_decode($dtRedc,true);
                                $dtaReducciones = $arrReduc;



                                if($refund_id == 1 || $refund_id == 2){

                                    $folio_viatics = 0;
                                    $folio_invoice_transfer =  $folio_viatics_invoice_transfer;

                                }else{

                                    $folio_viatics = $folio_viatics_invoice_transfer;
                                    $folio_invoice_transfer = 0;
                                }

                                $issue_date = date_create($issue_date);
                                $issue_date = date_format($issue_date, 'Y-m-d');

                                $auth_date = date_create($auth_date);
                                $auth_date = date_format($auth_date, 'Y-m-d');

                                $keytable = 1;
                                $rowItems = 0;

                                foreach ($dtaReducciones as $val){
                                    //foreach ($val as $values){
                                        for($x=0;$x<count($dataJsonMeses);$x++){

                                            $mesData = (int)$val['mes'];

                                            if($mesData == $x+1){

                                                if($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){

                                                    if($val[$dataJsonMeses[$x].'Sel'] > $val[$dataJsonMeses[$x].'Reintegro']){
                                                        $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de'." ".$dataJsonMeses[$x]." el cual es de $".$val[$dataJsonMeses[$x].'Reintegro']." "." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                    }

                                                }else{

                                                    if($val[$dataJsonMeses[$x].'Sel'] == 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0){
                                                        $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de'." ".$dataJsonMeses[$x]." de la Partida"." ".$val['partida_esp']." "."En el Renglón"." ".$keytable;
                                                    }

                                                }//If Principal

                                            }

                                        }

                                  //  }

                                    $keytable++;
                                    $rowItems++;
                                    $folioTR[] = $val['folioTranfer'];

                                }// FIN FOR VALIDACIONES


                                if(count($arrayError) > 0){

                                    $msg = array('message' => $arrayError,'tipo' => 'error');
                                    echo json_encode($msg);
                                    exit();

                                }else{

                                   /* $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' ";

                                    $ErrMsg = "No se pudo Eliminar la información";
                                    $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);*/

                                    $vclaves = '';

                                    foreach($dtaReducciones as $vcla){

                                        $vclaves = $vcla['accountcode'];


                                        $Deleterows = "DELETE FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.type = ".$type." AND chartdetailsbudgetlog.transno = '".$transno."' ";

                                        $ErrMsg = "No se pudo Eliminar la información";
                                        $ResultDelete = DB_query($Deleterows, $db, $ErrMsg);
                                    }

                                    if($ResultDelete == true){

                                      /*  $arrFolio = json_decode($arrayFolio,true);
                                        $folArray = $arrFolio;

                                        if(count($folArray) > 0){

                                            $allFolio = '';

                                            if(in_array($folioTR,$folArray)){
                                                $allFolio = $folioTR;
                                            }



                                            // for($q=0;$q<count($folArray);$q++){
                                            //   $allFolio .= $folArray[$q].",";
                                            // }

                                            //$allFolio = trim($allFolio, ',');  $folioTR = $val['folioTranfer'];

                                            $folio_viatics_invoice_transfer = $allFolio;

                                        }*/

                                        $unicos = array_unique($folioTR);
                                        $unicomas = array_values($unicos);
                                        if(count($unicomas) > 1){

                                            $allFolio = '';

                                            for($q=0;$q<count($unicomas);$q++){
                                                $allFolio .= $unicomas[$q].",";
                                            }

                                            $allFolio = trim($allFolio, ',');

                                            $folio_viatics_invoice_transfer = $allFolio;

                                        }else{
                                            if(count($unicos)<=1){
                                                $allFolio = '';

                                                for($q=0;$q<count($unicos);$q++){
                                                    $allFolio .= $unicos[$q].",";
                                                }

                                                $allFolio = trim($allFolio, ',');

                                                $folio_viatics_invoice_transfer = $allFolio;
                                            }


                                        }

                                        $sqlUpdateHeader = "UPDATE tb_refunds_notice SET folio_invoice_transfer = '".$folio_viatics_invoice_transfer."', ur_id = '".$ur_id."', ue_id = '".$ue_id."', tracking_code = '".$tracking_code."', 
                                                            justification = '".$justification."', issue_date = '".$issue_date."' 
                                                            WHERE id = '".$transno."'";

                                        $ErrMsg = "No se pudo almacenar la información";
                                        $TransResult = DB_query($sqlUpdateHeader, $db, $ErrMsg);


                                        //insert Partidas

                                        foreach($dtaReducciones as $valu){
                                            //foreach($valu as $items){
                                                for($v=0;$v<count($dataJsonMeses);$v++){

                                                    $mesData = (int)$valu['mes'];

                                                    if($mesData == $v+1){

                                                        if($dataJsonMeses[$v] == 'Enero'){

                                                            $dateP = $valu['año'].'-'.'01';
                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                $periodS = $rowsPeriod['periodno'];
                                                            }
                                                        }else{
                                                            if($dataJsonMeses[$v] == 'Febrero'){

                                                                $dateP = $valu['año'].'-'.'02';
                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                    $periodS = $rowsPeriod['periodno'];
                                                                }
                                                            }else{
                                                                if($dataJsonMeses[$v] == 'Marzo'){

                                                                    $dateP = $valu['año'].'-'.'03';
                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                        $periodS = $rowsPeriod['periodno'];
                                                                    }
                                                                }else{
                                                                    if($dataJsonMeses[$v] == 'Abril'){
                                                                        $dateP = $valu['año'].'-'.'04';
                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                            $periodS = $rowsPeriod['periodno'];
                                                                        }

                                                                    }else{
                                                                        if($dataJsonMeses[$v] == 'Mayo'){
                                                                            $dateP = $valu['año'].'-'.'05';
                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                $periodS = $rowsPeriod['periodno'];
                                                                            }
                                                                        }else{
                                                                            if($dataJsonMeses[$v] == 'Junio'){
                                                                                $dateP = $valu['año'].'-'.'06';
                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                }
                                                                            }else{
                                                                                if($dataJsonMeses[$v] == 'Julio'){
                                                                                    $dateP = $valu['año'].'-'.'07';
                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                    }
                                                                                }else{
                                                                                    if($dataJsonMeses[$v] == 'Agosto'){
                                                                                        $dateP = $valu['año'].'-'.'08';
                                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                            $periodS = $rowsPeriod['periodno'];
                                                                                        }
                                                                                    }else{
                                                                                        if($dataJsonMeses[$v] == 'Septiembre'){
                                                                                            $dateP = $valu['año'].'-'.'09';
                                                                                            $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                            $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                            while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                $periodS = $rowsPeriod['periodno'];
                                                                                            }

                                                                                        }else{
                                                                                            if($dataJsonMeses[$v] == 'Octubre'){
                                                                                                $dateP = $valu['año'].'-'.'10';
                                                                                                $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                    $periodS = $rowsPeriod['periodno'];
                                                                                                }
                                                                                            }else{
                                                                                                if($dataJsonMeses[$v] == 'Noviembre'){
                                                                                                    $dateP = $valu['año'].'-'.'11';
                                                                                                    $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                    $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                    while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                        $periodS = $rowsPeriod['periodno'];
                                                                                                    }
                                                                                                }else{
                                                                                                    if($dataJsonMeses[$v] == 'Diciembre'){
                                                                                                        $dateP = $valu['año'].'-'.'12';
                                                                                                        $searchPeriod = "SELECT periodno, lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$dateP."%'";

                                                                                                        $ResultPeriod = DB_query($searchPeriod, $db, $ErrMsg);
                                                                                                        while ($rowsPeriod = DB_fetch_array($ResultPeriod)){
                                                                                                            $periodS = $rowsPeriod['periodno'];
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }


                                                        $arrayTotal[$dataJsonMeses[$v]]['qty'] = $valu[$dataJsonMeses[$v].'Sel'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['accountcode'] = $valu['accountcode'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['año'] = $valu['año'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['budget'] = $valu['budget'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['budgetid'] = $valu['budgetid'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['claveCorta'] = $valu['claveCorta'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['claveLarga'] = $valu['claveLarga'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['fecha_modificacion'] = $valu['fecha_modificacion'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['idClavePresupuesto'] = $valu['idClavePresupuesto'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['legalid'] = $valu['legalid'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['mes'] = $valu['mes'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['modified'] = $valu['modified'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['tagref'] = $valu['tagref'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['tipoAfectacion'] = $valu['tipoAfectacion'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['tipoMovimiento'] = 265;
                                                        $arrayTotal[$dataJsonMeses[$v]]['period'] = $periodS;
                                                        $arrayTotal[$dataJsonMeses[$v]]['partida_esp'] = $valu['partida_esp'];
                                                        $arrayTotal[$dataJsonMeses[$v]]['type'] = 293;
                                                        $arrayTotal[$dataJsonMeses[$v]]['transno'] = $transno;
                                                        $arrayTotal[$dataJsonMeses[$v]]['type_refund'] = $refund_id;
                                                        $arrayTotal[$dataJsonMeses[$v]]['folio_Tranfer'] = $valu['folioTranfer'];


                                                    }



                                                }
                                           // }


                                            foreach ($arrayTotal as $valItems){

                                                fnInsertPresupuestoLogAcomulado($db, $valItems['type'], $valItems['transno'], $valItems['tagref'], $valItems['accountcode'], $valItems['period'], $valItems['qty'], $valItems['tipoMovimiento'], $valItems['partida_esp'], 'Reintegro en tramite con folio de Operacion  :'.$valItems['transno'], 0, $status_refund, 0, $ue_id, 'ASC', 'disponible', 'Reintegro', 'Ampliacion', 1, '', '', 0, $valItems['folio_Tranfer'], $valItems['type_refund'],0,$valItems['folio_Tranfer']);

                                            }

                                        }

                                        $msg = array('message' => 'Registros Actualizados Correctamente','tipo'=>'success');
                                        echo json_encode($msg);

                                        // FIn Insert Partidas
                                    }
                                }

                            }//fin UPDATE 3
                        }
                    }//fin tipos de Refunds


                } //Fin Mode
            }


        } catch(Exception $error){
            //echo 'Excepcion Capturada: ', $error->getMessage(), "\n";
            $msg = array('message' => $error->getMessage(),'tipo'=>'error');
            echo json_encode($msg);
        }

    }
}

if(isset($_POST['optionUpdateStatus'])) {
    if ($_POST['optionUpdateStatus'] == 'UpdateStatusRefundsAuth') {

        try {

            //variables

            $arrayMes = [];
            $arrayTotal = [];
         //   $totalMonth = 12;
            $dataJsonMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
         //   $ErrMsg = "No se pudo almacenar la información";
            $arrayError = [];


            $typeRefunds = mysqli_real_escape_string($db, $_POST['tipo']);
            // $folio = mysqli_real_escape_string($db, $_POST['folio']);
            $folio = $_POST['folio'];
            $valueStatus = mysqli_real_escape_string($db, $_POST['statusid']);
            $payment = mysqli_real_escape_string($db, $_POST['pago']);
            $ur = mysqli_real_escape_string($db, $_POST['ur']);
            $ue = mysqli_real_escape_string($db, $_POST['ue']);
            $transno = mysqli_real_escape_string($db, $_POST['transno']);

            $arrayItems = $_POST['dataJsonNoCapturaSeleccionados'];

            $fecha1 = date('Y-m-d');

            $xx = array();

            $codigocr = $_POST['cr'];
            $cavect = $_POST['ct'];
            $numeront = $_POST['nt'];

            $keytable = 1;
            $rowItems = 0;


            foreach ($arrayItems as $val) {

                for ($x = 0; $x < count($dataJsonMeses); $x++) {

                    $mesData = (int)$val['mes'];

                    if ($mesData == $x-1) {

                        if ($val[$dataJsonMeses[$x].'Sel'] != 0 && $val[$dataJsonMeses[$x].'Reintegro'] != 0) {

                            if ($val[$dataJsonMeses[$x] . 'Sel'] > $val[$dataJsonMeses[$x] . 'Reintegro']) {
                                $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de' . " " . $dataJsonMeses[$x] . " el cual es de $" . $val[$dataJsonMeses[$x] . 'Reintegro'] . " " . " de la Partida" . " " . $val['partida_esp'] . " " . "En el Renglón" . " " . $keytable;
                            }

                        } else {

                            if ($val[$dataJsonMeses[$x] . 'Sel'] == 0 && $val[$dataJsonMeses[$x] . 'Reintegro'] != 0) {
                                $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de' . " " . $dataJsonMeses[$x] . " de la Partida" . " " . $val['partida_esp'] . " " . "En el Renglón" . " " . $keytable;
                            } else {
                                if ($val[$dataJsonMeses[$x] . 'Sel'] != 0 && $val[$dataJsonMeses[$x] . 'Reintegro'] == 0) {

                                    if ($val[$dataJsonMeses[$x] . 'Sel'] > $val[$dataJsonMeses[$x] . 'Reintegro']) {
                                        $arrayError[$rowItems]['message'] = 'El Monto es Superior al establecido del mes de' . " " . $dataJsonMeses[$x] . " el cual es de $" . $val[$dataJsonMeses[$x] . 'Reintegro'] . " " . " de la Partida" . " " . $val['partida_esp'] . " " . "En el Renglón" . " " . $keytable;
                                    }

                                } else {
                                    if ($val[$dataJsonMeses[$x] . 'Sel'] == 0 && $val[$dataJsonMeses[$x] . 'Reintegro'] == 0) {
                                        $arrayError[$rowItems]['message'] = 'El Monto es Cero del mes de' . " " . $dataJsonMeses[$x] . " de la Partida" . " " . $val['partida_esp'] . " " . "En el Renglón" . " " . $keytable;

                                    }
                                }
                            }

                        }//If Principal

                    }//

                }

                $keytable++;
                $rowItems++;

            }

            if (count($arrayError) > 0) {
                $msg = array('message' => $arrayError, 'status' => 'error');
                echo json_encode($msg);
                exit();

            } else {


            if ($valueStatus == 4) {


                if ($typeRefunds == 3) {

                    $sqlUpdateHeader = "UPDATE tb_refunds_notice SET  status_refund = '" . $valueStatus . "', auth_date = '" . $fecha1 . "', tracking_code = '" . $codigocr . "' WHERE id = '" . $transno . "' AND ur_id = '" . $ur . "' AND ue_id = '" . $ue . "' ";
                } else {
                    if ($typeRefunds == 1) {
                        $sqlUpdateHeader = "UPDATE tb_refunds_notice SET  status_refund = '" . $valueStatus . "', auth_date = '" . $fecha1 . "', line_capture_TESOFE = '" . $cavect . "' WHERE id = '" . $transno . "' AND ur_id = '" . $ur . "'";
                    } else {
                        if ($typeRefunds == 2) {
                            $sqlUpdateHeader = "UPDATE tb_refunds_notice SET  status_refund = '" . $valueStatus . "', auth_date = '" . $fecha1 . "', tracking_code = '" . $codigocr . "', transfer_number = '" . $numeront . "' WHERE id = '" . $transno . "' AND ur_id = '" . $ur . "' AND ue_id = '" . $ue . "' ";
                        }
                    }
                }


                $ErrMsg = "No se pudo almacenar la información";
                $TransResult = DB_query($sqlUpdateHeader, $db, $ErrMsg);


                if ($TransResult == true) {

                    /* for ($s = 0; $s < count($dtaItems); $s++) {

                         //print_r($dtaItems[$s]['folioTranfer'] );

                         $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '".$valueStatus."', sn_disponible = 1 WHERE transno = '".$transno."' AND type = 293 ";

                         $ErrMsg = "No se pudo actualizar la información";
                         $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);

                     }*/

                    $updateDetailsRefund = "UPDATE chartdetailsbudgetlog SET estatus = '" . $valueStatus . "', sn_disponible = 1 WHERE transno = '" . $transno . "' AND type = 293 ";

                    $ErrMsg = "No se pudo actualizar la información";
                    $TransResultDetails = DB_query($updateDetailsRefund, $db, $ErrMsg);

                    if ($typeRefunds == 3 || $typeRefunds == 2) {

                        for ($v = 0; $v < count($folio); $v++) {

                            $selectUpdate = "SELECT chartdetailsbudgetlog.type,chartdetailsbudgetlog.transno,chartdetailsbudgetlog.qty,chartdetailsbudgetlog.tagref,chartdetailsbudgetlog.cvefrom,chartdetailsbudgetlog.ln_ue,
                              tb_refunds_notice.justification
                             FROM chartdetailsbudgetlog
                             JOIN tb_refunds_notice ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                             WHERE chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.transno = '" . $transno . "' AND chartdetailsbudgetlog.folio = '" . $folio[$v] . "' ";

                            $ErrMsg = "No se obtuvieron los Registros para el proceso";
                            $TransResult = DB_query($selectUpdate, $db, $ErrMsg);

                            //  $periodNo = GetPeriod(date('d/m/Y'), $db);

                            while ($myrow = DB_fetch_array($TransResult)) {

                                $Transtype = $myrow['type'];
                                $TransNo = $myrow['transno'];
                                $importe = $myrow['qty'];
                                $Tagref = $myrow['tagref'];
                                $clave = $myrow['cvefrom'];
                                $luue = $myrow['ln_ue'];
                                $description = $myrow['justification'];


                                $infoClaves = array();
                                $infoClaves[] = array(
                                    'accountcode' => $clave
                                );

                                $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                                $periodNo = $respuesta['periodo'];

                                //
                                $folioPolizaUe = 0;
                                foreach ($infoFoliosCompromiso as $datosFolios) {
                                    // Recorrer para ver si exi
                                    if ($datosFolios['tagref'] == $Tagref && $datosFolios['ue'] == $luue) {
                                        // Si existe
                                        $folioPolizaUe = $datosFolios['folioPolizaUe'];
                                    }
                                }
                                if ($folioPolizaUe == 0) {
                                    // Si no existe folio sacar folio
                                    // $transno = GetNextTransNo($type, $db);
                                    // Folio de la poliza por unidad ejecutora
                                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                    $infoFoliosCompromiso[] = array(
                                        'tagref' => $Tagref,
                                        'ue' => $luue,
                                        'type' => $Transtype,
                                        'transno' => $TransNo,
                                        'folioPolizaUe' => $folioPolizaUe
                                    );
                                }
                                //

                                $folioPolizaUe2 = 0;
                                foreach ($infoFoliosCompromiso2 as $datosFolios2) {
                                    // Recorrer para ver si exi
                                    if ($datosFolios2['tagref'] == $Tagref && $datosFolios2['ue'] == $luue) {
                                        // Si existe

                                        $folioPolizaUe2 = $datosFolios2['folioPolizaUe'];
                                    }
                                }
                                if ($folioPolizaUe2 == 0) {
                                    // Si no existe folio sacar folio
                                    // $transno = GetNextTransNo($type, $db);
                                    // Folio de la poliza por unidad ejecutora
                                    $folioPolizaUe2 = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                    $infoFoliosCompromiso2[] = array(
                                        'tagref' => $Tagref,
                                        'ue' => $luue,
                                        'type' => $Transtype,
                                        'transno' => $TransNo,
                                        'folioPolizaUe' => $folioPolizaUe2
                                    );
                                }

                                //
                                $folioPolizaUe3 = 0;
                                foreach ($infoFoliosCompromiso3 as $datosFolios3) {
                                    // Recorrer para ver si exi
                                    if ($datosFolios3['tagref'] == $Tagref && $datosFolios3['ue'] == $luue) {
                                        // Si existe

                                        $folioPolizaUe3 = $datosFolios3['folioPolizaUe'];
                                    }
                                }
                                if ($folioPolizaUe3 == 0) {
                                    // Si no existe folio sacar folio
                                    // $transno = GetNextTransNo($type, $db);
                                    // Folio de la poliza por unidad ejecutora
                                    $folioPolizaUe3 = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                    $infoFoliosCompromiso3[] = array(
                                        'tagref' => $Tagref,
                                        'ue' => $luue,
                                        'type' => $Transtype,
                                        'transno' => $TransNo,
                                        'folioPolizaUe' => $folioPolizaUe3
                                    );
                                }
                                //
                                $folioPolizaUe4 = 0;
                                foreach ($infoFoliosCompromiso4 as $datosFolios4) {
                                    // Recorrer para ver si exi
                                    if ($datosFolios4['tagref'] == $Tagref && $datosFolios4['ue'] == $luue) {
                                        // Si existe

                                        $folioPolizaUe4 = $datosFolios4['folioPolizaUe'];
                                    }
                                }
                                if ($folioPolizaUe4 == 0) {
                                    // Si no existe folio sacar folio
                                    // $transno = GetNextTransNo($type, $db);
                                    // Folio de la poliza por unidad ejecutora
                                    $folioPolizaUe4 = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                    $infoFoliosCompromiso4[] = array(
                                        'tagref' => $Tagref,
                                        'ue' => $luue,
                                        'type' => $Transtype,
                                        'transno' => $TransNo,
                                        'folioPolizaUe' => $folioPolizaUe4
                                    );
                                }
                                //


                                /*   print_r($Transtype);
                                   print_r($TransNo);
                                   print_r($periodNo);
                                   print_r($importe);
                                   print_r($Tagref);
                                   print_r($fecha1);
                                   print_r($clave);
                                   print_r($description);
                                   print_r($luue);
                                   print_r($folioPolizaUe);
                                   print_r($folioPolizaUe2);
                                   print_r($folioPolizaUe3);
                                   print_r($folioPolizaUe4);
                                   exit();
*/
                                GeneraMovimientoContablePresupuesto($Transtype, "PAGADO", "EJERCIDO", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe);//$fechacheque_contable
                                GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "DEVENGADO", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe2);//$fechacheque_contable
                                GeneraMovimientoContablePresupuesto($Transtype, "DEVENGADO", "COMPROMETIDO", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe3);//$fechacheque_contable
                                GeneraMovimientoContablePresupuesto($Transtype, "COMPROMETIDO", "POREJERCER", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe4);//$fechacheque_contable

                            }
                        }

                    } else {

                        if ($typeRefunds == 1) {

                            for ($v = 0; $v < count($folio); $v++) {

                                $selectUpdate = "SELECT chartdetailsbudgetlog.type,chartdetailsbudgetlog.transno,chartdetailsbudgetlog.qty,chartdetailsbudgetlog.tagref,chartdetailsbudgetlog.cvefrom,chartdetailsbudgetlog.ln_ue,
                                                 tb_refunds_notice.justification
                                                 FROM chartdetailsbudgetlog
                                                 JOIN tb_refunds_notice ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                                                 WHERE chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.transno = '" . $transno . "' AND chartdetailsbudgetlog.folio = '" . $folio[$v] . "' ";


                                $ErrMsg = "No se obtuvieron los Registros para el proceso";
                                $TransResult = DB_query($selectUpdate, $db, $ErrMsg);

                               // $periodNo = GetPeriod(date('d/m/Y'), $db);

                                while ($myrow = DB_fetch_array($TransResult)) {

                                    $Transtype = $myrow['type'];
                                    $TransNo = $myrow['transno'];
                                    $importe = $myrow['qty'];
                                    $Tagref = $myrow['tagref'];
                                    $clave = $myrow['cvefrom'];
                                    $luue = $myrow['ln_ue'];
                                    $description = $myrow['justification'];


                                    $infoClaves = array();
                                    $infoClaves[] = array(
                                        'accountcode' => $clave
                                    );

                                    $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                                    $periodNo = $respuesta['periodo'];

                                    //
                                    $folioPolizaUe = 0;
                                    foreach ($infoFoliosCompromiso as $datosFolios) {
                                        // Recorrer para ver si exi
                                        if ($datosFolios['tagref'] == $Tagref && $datosFolios['ue'] == $luue) {
                                            // Si existe
                                            $folioPolizaUe = $datosFolios['folioPolizaUe'];
                                        }
                                    }
                                    if ($folioPolizaUe == 0) {
                                        // Si no existe folio sacar folio
                                        // $transno = GetNextTransNo($type, $db);
                                        // Folio de la poliza por unidad ejecutora
                                        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                        $infoFoliosCompromiso[] = array(
                                            'tagref' => $Tagref,
                                            'ue' => $luue,
                                            'type' => $Transtype,
                                            'transno' => $TransNo,
                                            'folioPolizaUe' => $folioPolizaUe
                                        );
                                    }
                                    //

                                    $folioPolizaUe2 = 0;
                                    foreach ($infoFoliosCompromiso2 as $datosFolios2) {
                                        // Recorrer para ver si exi
                                        if ($datosFolios2['tagref'] == $Tagref && $datosFolios2['ue'] == $luue) {
                                            // Si existe

                                            $folioPolizaUe2 = $datosFolios2['folioPolizaUe'];
                                        }
                                    }
                                    if ($folioPolizaUe2 == 0) {
                                        // Si no existe folio sacar folio
                                        // $transno = GetNextTransNo($type, $db);
                                        // Folio de la poliza por unidad ejecutora
                                        $folioPolizaUe2 = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                        $infoFoliosCompromiso2[] = array(
                                            'tagref' => $Tagref,
                                            'ue' => $luue,
                                            'type' => $Transtype,
                                            'transno' => $TransNo,
                                            'folioPolizaUe' => $folioPolizaUe2
                                        );
                                    }

                                    //
                                    $folioPolizaUe3 = 0;
                                    foreach ($infoFoliosCompromiso3 as $datosFolios3) {
                                        // Recorrer para ver si exi
                                        if ($datosFolios3['tagref'] == $Tagref && $datosFolios3['ue'] == $luue) {
                                            // Si existe

                                            $folioPolizaUe3 = $datosFolios3['folioPolizaUe'];
                                        }
                                    }
                                    if ($folioPolizaUe3 == 0) {
                                        // Si no existe folio sacar folio
                                        // $transno = GetNextTransNo($type, $db);
                                        // Folio de la poliza por unidad ejecutora
                                        $folioPolizaUe3 = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                        $infoFoliosCompromiso3[] = array(
                                            'tagref' => $Tagref,
                                            'ue' => $luue,
                                            'type' => $Transtype,
                                            'transno' => $TransNo,
                                            'folioPolizaUe' => $folioPolizaUe3
                                        );
                                    }
                                    //
                                    $folioPolizaUe4 = 0;
                                    foreach ($infoFoliosCompromiso4 as $datosFolios4) {
                                        // Recorrer para ver si exi
                                        if ($datosFolios4['tagref'] == $Tagref && $datosFolios4['ue'] == $luue) {
                                            // Si existe

                                            $folioPolizaUe4 = $datosFolios4['folioPolizaUe'];
                                        }
                                    }
                                    if ($folioPolizaUe4 == 0) {
                                        // Si no existe folio sacar folio
                                        // $transno = GetNextTransNo($type, $db);
                                        // Folio de la poliza por unidad ejecutora
                                        $folioPolizaUe4 = fnObtenerFolioUeGeneral($db, $Tagref, $luue, $Transtype);
                                        $infoFoliosCompromiso4[] = array(
                                            'tagref' => $Tagref,
                                            'ue' => $luue,
                                            'type' => $Transtype,
                                            'transno' => $TransNo,
                                            'folioPolizaUe' => $folioPolizaUe4
                                        );
                                    }
                                    //


                                    GeneraMovimientoContablePresupuesto($Transtype, "PAGADO", "EJERCIDO", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe);//$fechacheque_contable
                                    GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "DEVENGADO", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe2);//$fechacheque_contable
                                    GeneraMovimientoContablePresupuesto($Transtype, "DEVENGADO", "COMPROMETIDO", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe3);//$fechacheque_contable
                                    GeneraMovimientoContablePresupuesto($Transtype, "COMPROMETIDO", "POREJERCER", $TransNo, $periodNo, $importe, $Tagref, $fecha1, $clave, 0, $db, false, '', '', $description, $luue, 1, 0, $folioPolizaUe4);//$fechacheque_contable


                                }


                            }//for


                        }//mas


                    }


                }

            }


            $msg = array('message' => 'Registros Autorizados Correctamente', 'status' => 'success');
            echo json_encode($msg);


        }


        } catch (Exception $err) {
            $msg = array('message' => $err->getMessage(), 'status' => 'error');
            echo json_encode($msg);
        }

    }
}

if(isset($_GET['permissionUser'])){

    try{


        $type = mysqli_real_escape_string($db, $_GET['tipoDocumento']);
        $transno = mysqli_real_escape_string($db, $_GET['transnoReintegro']);


        $fechaActualAde = date('d-m-Y');
        $autorizarGeneral = 0; // Variable deshabilitar general
        $permisoEditarEstCapturado = 0; // Havepermission($_SESSION ['UserID'], 2283, $db);
        $soloActFoliosAutorizada = 0;

        $estatusAdecuacionGeneral = "";
        $funcionPermisoMod = 0;


        $SQL = "SELECT tb_refunds_notice.status_refund, tb_refunds_notice.refund_id ,tb_botones_statusSig.functionid FROM tb_refunds_notice
                LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_status.statusid = tb_refunds_notice.status_refund 
                LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_refunds_notice.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
                WHERE tb_refunds_notice.id = '".$transno."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."' ";


        $transResult = DB_query($SQL, $db);
        while ($myrow = DB_fetch_array($transResult)) {
            $estatusAdecuacionGeneral = $myrow['status_refund'];
            // $tipo_reintegro = $myrow['nu_tipo'];
            $funcionPermisoMod = $myrow['functionid'];
            $tipoReintegro = $myrow['refund_id'];
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

        $resultQuery = array('permisoMod' => $permisoMod, 'autorizarGeneral' => $autorizarGeneral, 'tipoReintegro' => $tipoReintegro);
        echo json_encode($resultQuery);

    }catch (Exception $error){

        $msg = array('message' => $err->getMessage(), 'tipo' => 'error');
        echo json_encode($msg);
    }

}