<?php
/**
 * Modelo para REPORTE DE PAGOS A PROVEEDORES
 *
 * @category     GeneralPaymentsDetails
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 11/08/2017
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;

$PathPrefix = '../';
require $PathPrefix."includes/SecurityUrl.php";
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=244;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';

//$permiso = Havepermission ( $_SESSION ['UserID'], 244, $db ); // tenia 2006
//$permisomostrar=Havepermission($_SESSION['UserID'], 1420, $db);
$permisomostrar= Havepermission($_SESSION ['UserID'], 244, $db); // tenia 2006
//$permisomostrar=1;

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
 $SQL24="";
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$proceso = $_POST['proceso'];

$info = array();
$infoTotalesPagos=array();
$infoTotalesFacturas=array();
$infoTotalesPendientes=array();



//totales de pagos no aplicados
$subtotalPago = 0;
$ivaPago = 0;
$totalPago = 0;
//fin totales de pagos

// totales de pagos aplicados
$subtotalApli = 0;
$ivaApli = 0;
$totalApli = 0;
$totalIVAAplicado = 0;
// fin totales de pagos aplicados
function fnHistorialCancelado($banktransno, $db)
{

    $datos= array();

    $SQL = "SELECT  ln_chequeno,nu_transno,ln_tipo_pago,ln_tipo_cr,ln_justificacion,dtm_fecharegistro AS fecha FROM tb_cheques_cr WHERE nu_transno ='".$banktransno."'";
    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);


    while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] = array(
                              'cheque' => $myrow ['ln_chequeno'],
                              'tipoPago' => $myrow ['ln_tipo_pago'],
                              'justificacion' => $myrow ['ln_justificacion'],
                              'tipo'=>$myrow ['ln_tipo_cr'],
                              'fecha'=>$myrow['fecha']
                          );
    }

    return $datos;
}
function fnValidarMatrizPagado($db,$transno_act){
  $flag=false;
            
  for($x1=0; $x1<count($transno_act); $x1++) {      
      $consulta = "SELECT supptransdetails.stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
      ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
      JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
      WHERE stockid=stk),2) 
      AS impuesto,
      supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
      supptrans.ln_ue,
      supptrans.supplierno,
      supptransdetails.ln_clave_iden,
      tb_cat_partidaspresupuestales_partidagenerica.pargcalculado as categoryid,
      supptrans.supplierno,
      supptransdetails.stockid as itemcode
      FROM supptrans
      INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
      JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = supptransdetails.clavepresupuestal
      JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
      JOIN tb_cat_partidaspresupuestales_partidagenerica ON
      tb_cat_partidaspresupuestales_partidagenerica.ccap = tb_cat_partidaspresupuestales_partidaespecifica.ccap
      AND tb_cat_partidaspresupuestales_partidagenerica.ccon = tb_cat_partidaspresupuestales_partidaespecifica.ccon
      AND tb_cat_partidaspresupuestales_partidagenerica.cparg = tb_cat_partidaspresupuestales_partidaespecifica.cparg
      WHERE id = ('".$transno_act[$x1]."')";

      $consulta = "SELECT distinct supptrans.supplierno,
      tb_cat_partidaspresupuestales_partidagenerica.pargcalculado as categoryid,
      supptransdetails.ln_clave_iden
      FROM supptrans
      INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
      JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = supptransdetails.clavepresupuestal
      JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
      JOIN tb_cat_partidaspresupuestales_partidagenerica ON
      tb_cat_partidaspresupuestales_partidagenerica.ccap = tb_cat_partidaspresupuestales_partidaespecifica.ccap
      AND tb_cat_partidaspresupuestales_partidagenerica.ccon = tb_cat_partidaspresupuestales_partidaespecifica.ccon
      AND tb_cat_partidaspresupuestales_partidagenerica.cparg = tb_cat_partidaspresupuestales_partidaespecifica.cparg
      WHERE id = ('".$transno_act[$x1]."')";
      $resultado = DB_query($consulta, $db);
      while ($registro = DB_fetch_array($resultado)) {
        if (trim($registro['ln_clave_iden']) != '') {
          $sqlWhere = " AND ln_clave = '".$registro['ln_clave_iden']."' ";
        }

        $SQL = "SELECT stockact, accountegreso FROM tb_matriz_pagado WHERE categoryid = '".$registro['categoryid']."' AND stockact IN (SELECT accountcode FROM accountxsupplier WHERE supplierid = '".$registro["supplierno"]."') ".$sqlWhere;
        if ($_SESSION['UserID'] == 'desarrollo') {
          //print_r($SQL);
        }
        try{
          $resultCuenta = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
          if (DB_num_rows($resultCuenta) == 0) {
            $mensajeCuentas .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La partida '.$registro['categoryid'].' no esta configurada en la matriz del pagado.</p>';
          }
        }catch (Exception $excepcion){
          $ErrMsg .= $excepcion->getMessage(); 
        }

        // $i=0;
        // while ($myrowCuenta=db_fetch_array($resultCuenta)) {

        //   $GLCode = $myrowCuenta['accountegreso'];
        //   $cuentaAbonoProveedor = $myrowCuenta['stockact'];
        //   // print_r($GLCode);
        //   // print_r( $cuentaAbonoProveedor);
        //   // exit();
        //   if((is_null($GLCode) || empty($GLCode) || $GLCode=='') || (is_null($cuentaAbonoProveedor) || empty($cuentaAbonoProveedor) || $cuentaAbonoProveedor=='') ){
        //       $mensajeCuentas .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$registro['itemcode'].' No esta configurado en la matriz del devengado</p>';
        //   }

        //   print_r($GLCode);
        //   print_r( $cuentaAbonoProveedor);
        //   $i++;
        //  print_r($i);
        //     // if(($GLCode !='') && ($cuentaAbonoProveedor!='') ){

        //     // print_r($GLCode);
        //     //  print_r( $cuentaAbonoProveedor);

        //     // }else{
        //     //   print_r("error");
        //     //    print_r($GLCode);
        //     //  print_r( $cuentaAbonoProveedor);
        //     //   $mensajeCuentas .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$registro['itemcode'].' No esta configurado en la matriz del devengado</p>';
        //     // }

        // } // fin while de  checar cuentas
      }// fin while
  }// fin for para cada movimiento
  
  //print_r($datosFacturaAut);
  $datos=array();
  $datos[]=$mensajeCuentas;

  if($mensajeCuentas!=''){
    $flag=false;
  }else{
    $flag=true;
  }

  $datos[]=$flag;

  return $datos;
}
function GetMovsContablesYCancelar($nCheque,$TransNo,$fecha1,$PeriodNo,$type="284",$db,$justificacion, $folioPolizaUe=0){

 // falta validar que existan los movimientos contables en caso de borrar base de datos
     $consulta="SELECT * FROM gltrans  WHERE chequeno='".$nCheque."'  AND account  NOT  LIKE '8.%' AND type='281'  GROUP BY account,amount";
     $resultado = DB_query($consulta, $db);
     $valores='';
      while ($registros = DB_fetch_array($resultado)) {
          $valores.="(
          '".$type."',
          '" . $registros['typeno'] . "',
          '" . $fecha1 . "',
          '" . $PeriodNo . "',
          '" . $registros['account'] . "',
          '" . $registros['narrative'] .$justificacion. "',
          '" . ($registros['amount']*-1) . "',
          '" . $registros['tag'] . "',
          '" . $nCheque. "',
          '" . $_SESSION['UserID'] . "',
          now(),
          '".$registros['supplier']."',
          '".$registros['narrative'].$justificacion."',
          '".$registros['ln_ue']."',
          '1',
          '".$folioPolizaUe."'
          ),";
      }
      $valores=substr($valores, 0, -1);

      $SQL1="INSERT INTO "."gltrans"." ( 
      type,
      typeno,
      trandate,
      periodno,
      account,
      narrative,
      amount,
      tag,
      chequeno,
      userid,
      dateadded,
      supplier,
      descripcion,
      ln_ue,
      posted,
      nu_folio_ue
      ) VALUES ".$valores;
                    
      $resultado = DB_query($SQL1, $db); 
    //return $SQL1;
}
function fnRegresarEstadoAnterior($estado,$id,$db){
     $consulta="UPDATE supptrans SET hold='".$estado."' WHERE id='".$id."'";
     $resultado = DB_query($consulta, $db);
}

function fn_CancelarMovsCheques_ANT($NumeroCheque, $transno_act, $fechas, $tagref, $cancelacionTotal = 0, $db, $justificacion, $hold)
{
    $var=$fechas;
    $ratefactura=1;
    $Transtype='284'; // tipo cancelacion cheque

    // $cancelacionTotal = 1 - Cancelación por reposición de cheque
    // $cancelacionTotal = 2 - Cancelación total
    // $hold = 2 - Autorizado
    // $hold = 3 - Pagado
       
    for ($x1=0; $x1<count($transno_act); $x1++) {
        $fecha=date("d-m-Y", strtotime($var[$x1]));
        $fecha1=date("Y-m-d", strtotime($var[$x1]));
        $datosFecha=explode('-', $fecha);
        $diaP=$datosFecha[0]; //date('d');
        $mesP= $datosFecha[1];//date('m');
        $anioP= $datosFecha[2]; //date('Y');
        $ratefactura=1;
        $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);
        $TransNo = GetNextTransNo($Transtype, $db);
        $ErrMsg='';
        
        $value='';
        $descrip='';
        $ueDecrip='';
        $descriptionLog = "Cancelacion cheque.";

        // Folio de la poliza por unidad ejecutora
        $consulta= "SELECT stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
        ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
        JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
        WHERE stockid=stk),2) 
        AS impuesto,
        supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
        supptrans.ln_ue,
        hold as estatus
        FROM supptrans
        INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
        WHERE id = ('".$transno_act[$x1]."')  ";
        $resultado = DB_query($consulta, $db);
        $registro = DB_fetch_array($resultado);
        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref[$x1], $registro ['ln_ue'], $Transtype);

        // while ($registro = DB_fetch_array($resultado)) {
            
        //cancelacion de  contable de autorizado que es por reposicion
        if(($cancelacionTotal=='1') && ($hold[$x1]=='2')){
            GetMovsContablesYCancelar($NumeroCheque[$x1],$transno_act[$x1],$fecha1,$PeriodNo,$Transtype,$db,$justificacion, $folioPolizaUe);
            fnRegresarEstadoAnterior("6",$transno_act[$x1],$db);
        }

        //cancelacion total de autorizado
        if(($cancelacionTotal=='2') && ($hold[$x1]=='2')){
            $consulta= "SELECT stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
            ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
            JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
            WHERE stockid=stk),2) 
            AS impuesto,
            supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
            supptrans.ln_ue,
            hold as estatus,
            supptransdetails.period,
            supptransdetails.nu_id_compromiso,
            supptransdetails.nu_id_devengado,
            supptransdetails.nu_idret
            FROM supptrans
            INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
            WHERE id = ('".$transno_act[$x1]."')  ";
            $resultado = DB_query($consulta, $db);
            while ($registro = DB_fetch_array($resultado)){
                $importe =( ( (  ($registro["price"])* ($registro["qty"]))  * (1 + 0)) / $ratefactura);

                GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "DEVENGADO", $TransNo, $PeriodNo, $importe, $tagref[$x1], $fecha1, $registro["clavepresupuestal"], $TransNo, $db, false, '', '', '', $registro ['ln_ue'], 1, $NumeroCheque[$x1], $folioPolizaUe);

                $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagref[$x1], $registro["clavepresupuestal"], $registro['period'], $importe, 261, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Abono

                $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagref[$x1], $registro["clavepresupuestal"], $registro['period'], $importe * -1, 260, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Cargo
            }
            
            GetMovsContablesYCancelar($NumeroCheque[$x1],$transno_act[$x1],$fecha1,$PeriodNo,$Transtype,$db,$justificacion, $folioPolizaUe);

            fnRegresarEstadoAnterior("5",$transno_act[$x1],$db);
        }//fin cancelacion  total  estatus  autorizado

        // cancelacion por reposicion del pagado
        if (($cancelacionTotal=='1') && ( $hold[$x1]==3)) {
            GetMovsContablesYCancelar($NumeroCheque[$x1],$transno_act[$x1],$fecha1,$PeriodNo,$Transtype,$db,$justificacion, $folioPolizaUe);

            fnRegresarEstadoAnterior("6",$transno_act[$x1],$db);
        }//cancelacion por reposicion  estatus pagado


        if (($cancelacionTotal=='2')&&( $hold[$x1]==3)){
            $consulta= "SELECT stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
            ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
            JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
            WHERE stockid=stk),2) 
            AS impuesto,
            supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
            supptrans.ln_ue,
            hold as estatus,
            supptransdetails.period,
            supptransdetails.nu_id_compromiso,
            supptransdetails.nu_id_devengado,
            supptransdetails.nu_idret
            FROM supptrans
            INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
            WHERE id = ('".$transno_act[$x1]."')  ";
            $resultado = DB_query($consulta, $db);
            while ($registro = DB_fetch_array($resultado)){
                $importe =( ( (  ($registro["price"])* ($registro["qty"]))  * (1 + 0)) / $ratefactura);

                GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, ($importe*-1),$tagref[$x1], $fecha1, $registro["clavepresupuestal"], $TransNo, $db, false, $supplierid, $descrip, $datosRequi[1], $registro ['ln_ue'], 1, $NumeroCheque[$x1], $folioPolizaUe);//$fechacheque_contable

                // Log Presupuesto
                $descriptionLog = "Cancelacón de la Generación de Pago";
                $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagref, $registro["clavepresupuestal"], $registro['period'], $importe, 265, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Abono
                $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagref, $registro["clavepresupuestal"], $registro['period'], $importe * -1, 261, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Cargo
            }
            GetMovsContablesYCancelar($NumeroCheque[$x1],$TransNo,$fecha1,$PeriodNo,$Transtype,$db,$justificacion, $folioPolizaUe);
            fnRegresarEstadoAnterior("5",$transno_act[$x1],$db);
        }//cancelacion por reposicion  estatus pagado
        
        // } // fin while
    }// fin for para cada movimiento
}

function fnCancelarMovsCheques($NumeroCheque, $transno_act, $fechas, $tagref, $cancelacionTotal = 0, $db, $justificacion, $hold)
{
    $var=$fechas;
    $ratefactura=1;
    $Transtype='284'; // tipo cancelacion cheque

    // $cancelacionTotal = 1 - Cancelación por reposición de cheque
    // $cancelacionTotal = 2 - Cancelación total
    // $hold = 2 - Autorizado
    // $hold = 3 - Pagado
       
    for ($x1=0; $x1<count($transno_act); $x1++) {
        $fecha=date("d-m-Y", strtotime($var[$x1]));
        $fecha1=date("Y-m-d", strtotime($var[$x1]));
        $datosFecha=explode('-', $fecha);
        $diaP=$datosFecha[0]; //date('d');
        $mesP= $datosFecha[1];//date('m');
        $anioP= $datosFecha[2]; //date('Y');
        $ratefactura=1;
        $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);
        $TransNo = GetNextTransNo($Transtype, $db);
        $ErrMsg='';
        
        $value='';
        $descrip='';
        $ueDecrip='';
        $descriptionLog = "Cancelacion cheque.";

        $consulta= "SELECT stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
        ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
        JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
        WHERE stockid=stk),2) 
        AS impuesto,
        supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
        supptrans.ln_ue,
        hold as estatus,
        supptransdetails.period,
        supptransdetails.nu_id_compromiso,
        supptransdetails.nu_id_devengado,
        supptransdetails.nu_idret,
        supptransdetails.detailid
        FROM supptrans
        INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
        WHERE id = ('".$transno_act[$x1]."')  ";
        $resultado = DB_query($consulta, $db);
        // Array para folios agrupados (UR-UE)
        $infoFolios = array();
        while ($registro = DB_fetch_array($resultado)){
            $importe =( ( (  ($registro["price"])* ($registro["qty"]))  * (1 + 0)) / $ratefactura);

            // Cancelacion contable
            $consulta="SELECT * FROM gltrans 
            WHERE 
            chequeno='".$NumeroCheque[$x1]."'
            AND type='281'
            AND nu_supptrans_detailid = '".$registro['detailid']."'
            AND nu_pagado = 1
            ORDER BY counterindex ASC";
            // AND account NOT LIKE '8.%' 
            // echo "\n consulta: ".$consulta;
            $resultado2 = DB_query($consulta, $db);
            $valores='';
            while ($registros = DB_fetch_array($resultado2)) {
                // Ver si existe folio para movimientos
                $folioPolizaUe = 0;
                foreach ($infoFolios as $datosFolios) {
                    // Recorrer para ver si exi
                    if ($datosFolios['tagref'] == $registros['tag'] && $datosFolios['ue'] == $registros['ln_ue']) {
                        // Si existe
                        $folioPolizaUe = $datosFolios['folioPolizaUe'];
                    }
                }
                if ($folioPolizaUe == 0) {
                    // Si no existe folio sacar folio
                    // $transno = GetNextTransNo($type, $db);
                    // Folio de la poliza por unidad ejecutora
                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $registros['tag'], $registros['ln_ue'], $Transtype);
                    $infoFolios[] = array(
                        'tagref' => $registros['tag'],
                        'ue' => $registros['ln_ue'],
                        'type' => $Transtype,
                        'transno' => $TransNo,
                        'folioPolizaUe' => $folioPolizaUe
                    );
                }
                $valores.="(
                '".$Transtype."',
                '" . $TransNo . "',
                '" . $fecha1 . "',
                '" . $PeriodNo . "',
                '" . $registros['account'] . "',
                '" . $registros['narrative'] .$justificacion. "',
                '" . ($registros['amount']*-1) . "',
                '" . $registros['tag'] . "',
                '" . $NumeroCheque[$x1]. "',
                '" . $_SESSION['UserID'] . "',
                now(),
                '".$registros['supplier']."',
                '".$registros['narrative'].$justificacion."',
                '".$registros['ln_ue']."',
                '1',
                '".$folioPolizaUe."',
                '".$registros['nu_supptrans_detailid']."',
                '".$registros['nu_pagado']."'
                ),";
            }
            $valores=substr($valores, 0, -1);

            $SQL1="INSERT INTO gltrans ( 
            type,
            typeno,
            trandate,
            periodno,
            account,
            narrative,
            amount,
            tag,
            chequeno,
            userid,
            dateadded,
            supplier,
            descripcion,
            ln_ue,
            posted,
            nu_folio_ue,
            nu_supptrans_detailid,
            nu_pagado
            ) VALUES ".$valores;
            // echo "\n valores: ".$valores;
            $resultado2 = DB_query($SQL1, $db);
        }
        fnRegresarEstadoAnterior("6",$transno_act[$x1],$db);
        // } // fin while
    }// fin for para cada movimiento
}

function fnFeriadoOfin($ndias, $db)
{

     $fecha= date("Y-m-d", strtotime(' +'.$ndias.' day'))." "."00:00:00";
    
    $SQL="  SELECT FinDeSemana,Feriado FROM  DWH_Tiempo where Fecha='".$fecha."' ORDER BY FinDeSemana DESC LIMIT 1 ";
    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    $feriado='';
    $fin='';
    
    while ($myrow = DB_fetch_array($TransResult)) {
        $feriado=$myrow['Feriado'];
        $fin=$myrow['FinDeSemana'];
    }

    if (($feriado=='1') || ($fin=='1')) {
        //print($ndias);
        $ndias=$ndias+1;
        return  (fnFeriadoOfin($ndias, $db));
    } else {
        $fecha=   date("d-m-Y", strtotime(' +'.$ndias.' day'));
        
        return $fecha;
    }
}

function fnFeriadoOfin1($ndias, $db, $fechas, $a)
{

    $fecha= date("Y-m-d", strtotime(' +'.$ndias.' day'))." "."00:00:00";
    $aux=array();
 //count($fechas)
    $fin=0;
    $feriado=0;
    $fecha2='';
//$fecha="'"$fecha."'";
//
    while ($a<count($fechas)) {
        $aux=$fechas[$a];
        $fecha2= $aux['fecha'];
        $fin=$aux['fin'];
        $feriado=$aux['feriado'];
       
        if ($fecha2==$fecha) {//if($fecha2==$fecha && ( ($fin=='1') || ( ($feriado=='1')  ) )  ){
            echo " concidencia ".$fecha2;
           // print($fecha2);
           // print($fin);
           // print($feriado);
           
              $ndias=$ndias+1;
              
              $a++;
             return  (fnFeriadoOfin1($ndias, $db, $fechas, $a));
        } else {
            $fecha=   date("d-m-Y", strtotime(' +'.$ndias.' day'));
           // echo "no coincidencia".$fecha;
          
            $a++;
        }
    }
 
     return $fecha;
}

function fnGetUe($ur, $ue, $db)
{
    $retorno='';
  
    $SQL="SELECT ue,desc_ue FROM tb_cat_unidades_ejecutoras where ur='".$ur."' and ue='".$ue."' LIMIT 1";
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

        

    while ($myrow = DB_fetch_array($TransResult)) {
        $retorno=$myrow['ue']."-".$myrow['desc_ue'];
    }
    return $retorno;
}
function fnDatosRequiParaRectificado($requi, $db)
{
    $retorno =array();
    
    $clavePresupuestal=array();
    $cantidadSolicitada=array();
    $precioUnitario=array();

    $SQL="SELECT 
    purchorderdetails.clavepresupuestal AS clavePresupuestal, 
    tb_partida_articulo.partidaEspecifica AS idPartida, 
    purchorderdetails.itemcode AS idItem, 
    stockmaster.units AS unidad, 
    stockmaster.mbflag AS tipo,
    purchorderdetails.unitprice AS precio, 
    purchorderdetails.quantityord AS cantidad,
    purchorderdetails.total_quantity AS total, 
    -- if(almacen.existencia = 0,'No Disponible','Disponible') AS existencia,
    purchorderdetails.orderlineno_ AS orden, 
    purchorderdetails.orderno AS idRequisicion
    FROM purchorderdetails 
    INNER JOIN purchorders ON  purchorderdetails.orderno= purchorders.orderno
    JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
    JOIN tb_partida_articulo on (tb_partida_articulo.eq_stockid = stockmaster.eq_stockid)
    JOIN tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
    LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='desarrollo'
    GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
    where purchorders.requisitionno =".$requi ." AND purchorderdetails.status ='2'
    ORDER BY orden;";
    $ErrMsg="Error al obtener datos de la requisicion";
     $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $clavePresupuestal[]= $myrow['clavePresupuestal'];
        $cantidadSolicitada[]=$myrow['cantidad'];
        $precioUnitario[]=    $myrow['precio'];
    }

     $retorno[]= $clavePresupuestal;
     $retorno[]= $cantidadSolicitada;
     $retorno[]= $precioUnitario;
     
    return  $retorno;
}
function fnMontoTotalPorClave($arrayBuscar, $claveBuscar, $arrayCantidad, $arrayPrecios)
{

    $monto=0;

    for ($a=0; $a<count($arrayBuscar); $a++) {
        if ($arrayBuscar[$a] == $claveBuscar) {
            $monto+=($arrayCantidad[$a])*($arrayPrecios[$a]);
        }
    }


    return $monto;
}
function fnChecarExistencia($arrayBuscar, $claveBuscar)
{
    $retorno=array();
    $contador=0;
    $posicion=0;

    for ($a=0; $a<count($arrayBuscar); $a++) {
        if ($arrayBuscar[$a] == $claveBuscar) {
            $posicion=$a;
            $contador++;
        }
    }

    $retorno[]=$contador;
    $retorno[]=$posicion;
    return $retorno;
}
function fnChecarRequis($requi, $db)
{
    $datos=array();
    $claves=array();
    $retorno=array();
    $montos=array();
    $datos= fnDatosRequiParaRectificado($requi, $db);
   
    $claves=$datos[0];
    $cantidades=$datos[1];
    $precios=$datos[2];
    $clavesUnicas=array();

    // antes aqui de entrar aqui quitar claves repetidas
    $clavesUnicas=array_unique($claves);
    //buscar cantidad solicitada
   
    for ($a=0; $a<count($clavesUnicas); $a++) {
        $montos[]=fnMontoTotalPorClave($claves, $clavesUnicas[$a], $cantidades, $precios);
    }
    
    $retorno[]=$clavesUnicas;
    $retorno[]=$montos;

    return $retorno;
}


function fnEstado($estado, $sinliga = 0)
{
        /*$estatusValue['all'] = -1;
        $estatusValue['pend'] = 0;
        $estatusValue['prog'] = 1;
        $estatusValue['auth'] = 2;
        $estatusValue['exec'] = 3;
        */
        $estadoStr='';
        $estadoSinliga='';
        $retorno='';
    switch ($estado) {
        case '-1':
            $estadoStr='<div class="estatusTeso estadoSid"><span>Sin definir</span></div>';
            $estadoSinliga='Sin definir';

            break;
        case '0':
            $estadoStr='<div class="estatusTeso estadoPen"><span>Pendiente de pago</span></div>';
             $estadoSinliga='Pendiente de pago';
            break;

        case '1':
            $estadoStr='<div class="estatusTeso estadoPro"><span>Programado</span></div>';
            $estadoSinliga='Programado';
            break;

        case '2':
            $estadoStr='<div class="estatusTeso estadoAut"><span>Autorizado</span></div>';
            $estadoSinliga='Autorizado';
            break;
        case '3':
            $estadoStr='Pagado'; /*Agregado */
            $estadoSinliga='Pagado';
            break;
        case '4':
            $estadoStr='<div class="estatusTeso estadoEje"><span>Enviado a SICOP</span></div>';
            break;
        case '5':
            $estadoStr='<div class="estatusTeso estadoCancelaTotal"><span>Cancelación Total</span></div>';
            $estadoSinliga='Cancelación Total';
            break;
         case '6':
            $estadoStr='<div class="estatusTeso estadoCancelaTotal"><span>Cancelación por Reposición</span></div>';
            $estadoSinliga='Cancelación por Reposición';
            break;
         case '7':
            $estadoStr='<div class="estatusTeso estadoCancelaTotal"><span>Cancelación por Reposición</span></div>';
            $estadoSinliga='Cancelación por Reposición';
            break;

            case '-8': //pendiente de pago del  reposicion de un autorizado cancelado
            $estadoStr='<div class="estatusTeso estadoPen"><span>Pendiente de pago</span></div>';
             $estadoSinliga='Pendiente de pago';
            break;

            case '-9': ////pendiente de pago del  reposicion de un autorizado cancelado
            $estadoStr='<div class="estatusTeso estadoPro"><span>Programado</span></div>';
            $estadoSinliga='Programado';
            break;
         

        default:
            break;
    }
    if ($sinliga==1) {
         $retorno=$estadoSinliga;
    } else if ($sinliga==0) {
        $retorno=$estadoStr;
    }

        return $retorno;
}

function fnLiga($id, $db)
{
    $Transtype='22';
    //$SQL="      SELECT transno FROM supptrans WHERE ref2='".$id."'  AND type=22";
    //$SQL="SELECT transid_allocto FROM suppallocs where transid_allocfrom ='".$id."' ORDER BY datealloc DESC LIMIT 1";
    $SQL="SELECT transno FROM supptrans where id=(SELECT transid_allocfrom FROM suppallocs where transid_allocto='".$id."' ORDER BY datealloc DESC limit 1)";
    $ErrMsg = "no requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $initransno ='';
    $liga='';
    $datos=array();

    while ($myrow = DB_fetch_array($TransResult)) {
        //$initransno=$myrow['transid_allocto'];
        $initransno=$myrow['transno'];
    }
    //print_r($initransno);

    if ($initransno!='') { // con esto  chequco  si  existe ya el pago  y si no existe  no hay enlace para mostrar


        $link1="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $Transtype. "&TransNo=" . $initransno ;
        $liga='<a  target="_blank" href="'.$link.'">'.'<img src="images/imprimir_25.png" title="' . _('FORMATO PRE-IMPRESO CON DETALLE CONTABLE')
        . '" alt="">' . ' ' ._('').'</a>';
    //echo '<td class="numero_normal">'.$liga.'</td>';

    //Formato de cheque sin movimientos contables
        $link="PDFCheque.php?type=" . $Transtype. "&TransNo=" . $initransno ;
        $liga='<a  target="_blank" href="'.$link.'">'.'<img src="images/imprimir_25.png" title="' . _('FORMATO PRE-IMPRESO')
        . '" alt="">' . ' ' ._('').'</a>';
    // echo '<td class="numero_normal">'.$liga.'</td>';

                //Poliza
                $enc = new Encryption;
                $url =  "FromCust=>1&ToCust=>1&PrintPDF=>Yes&type=>".$Transtype."&TransNo=>".$initransno."&periodo=>". "&trandate=>";
                $url = $enc->encode($url);
                $ligaEncriptada= "URL=" . $url;

                /*$liga="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=".$Transtype. "&TransNo=" .$initransno. "&periodo=". "&trandate=";
                */
                $liga="PrintJournal.php?".$ligaEncriptada;
    }
    $datos[]=$liga;
    $datos[]=$initransno;
    
    return $datos;
 
}
function fnLiga2($id, $db){
    $Transtype='22';
    //$SQL="      SELECT transno FROM supptrans WHERE ref2='".$id."'  AND type=22";
    //$SQL="SELECT transid_allocto FROM suppallocs where transid_allocfrom ='".$id."' ORDER BY datealloc DESC LIMIT 1";
    $SQL="SELECT transno FROM supptrans where id=(SELECT transid_allocfrom FROM suppallocs where transid_allocto='".$id."' ORDER BY datealloc DESC limit 1)";
    $ErrMsg = "no requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $initransno ='';
    $liga='';
    $datos=array();

    while ($myrow = DB_fetch_array($TransResult)) {
        //$initransno=$myrow['transid_allocto'];
        $initransno=$myrow['transno'];
    }
   
    $datos[]=$initransno;
    
    return $datos;
  //return $link1;
}
function fnTraerTipoPago($db, $transno, $visual = 0, $nu_type = 0)
{
    $SQL='';
    if($visual==0){
        $SQL="SELECT banktrans.banktranstype as tipoPago, banktrans.chequeno as folio , banktrans.amount as monto1,banktrans.bankact as bancoOrigen 
        FROM banktrans
        WHERE banktrans.transno='".$transno."' 
        AND banktrans.nu_type = '".$nu_type."' 
        LIMIT 1";
    }else{
        $SQL="SELECT banktrans.banktranstype as tipoPago, banktrans.chequeno as folio , banktrans.amount as monto1,banktrans.bankact as bancoOrigen 
        FROM banktrans 
        WHERE banktrans.transno='".$transno."' 
        AND banktrans.nu_type = '".$nu_type."'";
    }

    $SQL="SELECT banktrans.banktranstype as tipoPago, banktrans.chequeno as folio , banktrans.amount as monto1,banktrans.bankact as bancoOrigen 
    FROM banktrans 
    WHERE 
    ABS(banktrans.amount) <> 0
    AND banktrans.transno='".$transno."' 
    AND banktrans.nu_type = '".$nu_type."'";
    $montoBanco=0;
    $bancoOrigen='';
    $ErrMsg = "no folio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $tipoPago='';
    $folio='';
    $datos=array();
    $Ids=array();
    $ids1=array();
    while ($myrow = DB_fetch_array($TransResult)) {
        $tipoPago=$myrow['tipoPago'];
        $folio=$myrow['folio'];
        $montoBanco=(($myrow['monto1'])* (-1));
        $bancoOrigen=$myrow['bancoOrigen'];
        $Ids= fnGetIds($folio."-".$bancoOrigen, $db);
    }

    // para traer el tipo de pago
    $total=0;
    $SQL="SELECT DISTINCT supptrans.ovamount as monto       
    FROM  banktrans 
    INNER JOIN supptrans on banktrans.transno = supptrans.transno AND banktrans.nu_type = supptrans.type
    INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
    WHERE banktrans.bankact='".$bancoOrigen."' AND chequeno = '".$folio."'";
    $ErrMsg = "no folio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $monto=(($myrow['monto'])* (-1));
        $total+=$monto;
    }

    $cadenaIds='';
    $ids1=$Ids[0];
    for ($a=0; $a<count($ids1); $a++) {
        $cadenaIds.=$ids1[$a].",";
    }
    $cadenaIds=substr($cadenaIds, 0, -1);

    $datos[]=$tipoPago;//0
    $datos[]=$folio;//1
    if ($montoBanco!=0) {
        $datos[]=$total;//2
    } else {
        $datos[]=0;//2
    }
    $datos[]=$bancoOrigen; //3
    $datos[]=$cadenaIds; //4
    
    return $datos;
}
function fnCancelarCheque($type = '20', $tipoCR, $db)
{
    $flag=false;
    $contador=0;
    if (isset($_POST['ChequeNum']) && isset($_POST['justificacion'])){
        $transnoFolios = $_POST['transno'];
        $cheques=$_POST['ChequeNum'];
        $origen=$_POST['origen'];
        $cadenaCheques='';
        $ids=$_POST['ids'];
        $fechas=$_POST['fechas'];
        $cadenaBancosOrigen='';
        $tagref=$_POST['tag'];
        $tipoCanelacion=$_POST['tipoCancel'];
        $canelacion=$_POST['cancelacion'];
        $hold=$_POST['status2'];

        $types=$_POST['type'];
        $nu_types=$_POST['nu_type'];

        $contador=0;

        $infoCancelacion = array();

        for ($a=0; $a<count($cheques); $a++) {
            $sql = "SELECT ln_chequeno FROM tb_cheques_cr WHERE ln_chequeno='".$cheques[$a]."'";
            $result = DB_query($sql, $db);
            $x=DB_num_rows($result);
            if($x ==0 ){ // si no existe en cheuqes_cr
                $cadenaCheques.="'".$cheques[$a]."',";
                $cadenaBancosOrigen.="'".$origen[$a]."',";
                
                $infoCancelacion[] = array(
                    'chequeno' => $cheques[$a],
                    'bankact' => $origen[$a],
                    'transno' => $transnoFolios[$a],
                    'type' => $types[$a],
                    'nu_type' => $nu_types[$a]
                );

                $contador++;
            }
        }

        // echo "\n cadenaCheques: ".$cadenaCheques;
        // echo "\n cadenaBancosOrigen: ".$cadenaBancosOrigen;
        // echo "\n infoCancelacion num: ".count($infoCancelacion);
        
        if(count($infoCancelacion) > 0){ // si no existe en cheques _cr
            $cadenaCheques=substr($cadenaCheques, 0, -1);
            $cadenaBancosOrigen=substr($cadenaBancosOrigen, 0, -1);

            // echo "\n cadenaCheques 2: ".$cadenaCheques;
            // echo "\n cadenaBancosOrigen 2: ".$cadenaBancosOrigen;
            // echo "\n\n";
            // print_r($infoCancelacion);

            $valores='';
            foreach ($infoCancelacion as $datos) {
                // Datos de cancelacion cheques
                $SQL = "UPDATE banktrans
                SET ref=concat('CANCELADO','-',amount) ,amount=0
                WHERE 
                banktrans.chequeno = '".$datos['chequeno']."' 
                AND banktrans.bankact = '".$datos['bankact']."' 
                and banktrans.type = '".$datos['type']."'
                and banktrans.transno = '".$datos['transno']."'
                and banktrans.nu_type = '".$datos['nu_type']."' ";
                // echo "\n SQL: ".$SQL;
                $ErrMsg='Error al cancelar cheque';
                $r = DB_query($SQL, $db, $ErrMsg);

                //extraigo informacion para insertar en la  tabla de  cheques_cr
                $SQL="SELECT DISTINCT banktrans.chequeno,banktrans.banktranstype,supptrans.transno,supptrans.type,banktrans.banktransid
                FROM  banktrans 
                INNER JOIN supptrans on banktrans.transno = supptrans.transno AND banktrans.nu_type = supptrans.type
                INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
                WHERE 
                banktrans.chequeno = '".$datos['chequeno']."' 
                AND banktrans.bankact = '".$datos['bankact']."' 
                and banktrans.type = '".$datos['type']."'
                and banktrans.transno = '".$datos['transno']."'
                and banktrans.nu_type = '".$datos['nu_type']."' ";
                // echo "\n SQL: ".$SQL;
                $ErrMsg = "No se obtuvo datos.";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                $cheque='';
                $tipoPago='';
                $transno='' ;
                $idbank='';
                $nu_type='';
                while ($myrow = DB_fetch_array($TransResult)) {
                    $cheque=$myrow['chequeno'];
                    $tipoPago=$myrow['banktranstype'];
                    $transno=$myrow['transno'];
                    $idbank=$myrow['banktransid'];
                    $nu_type=$myrow['type'];

                    $valores.="('".$cheque."','".$tipoPago."','". $transno."','". $idbank."','".$_POST['justificacion']."','".$tipoCR."','".$canelacion."','".$nu_type."'),";
                }
            }
            $valores=substr($valores, 0, -1);
            // echo "\nvalores: ".$valores;

            $SQL = "INSERT INTO tb_cheques_cr (ln_chequeno,ln_tipo_pago,nu_transno,nu_id_bank,ln_justificacion,ln_tipo_cr,nu_tipo_cr,nu_type)  VALUES ".$valores;
            // echo "\n SQL: ".$SQL;
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $justificacion=$_POST['justificacion'];
            fnCancelarMovsCheques($cheques, $ids, $fechas, $tagref, $canelacion, $db, $justificacion, $hold);

            $n=count($cheques);
            if($n==$contador){
              $flag=true;
            }
        }// fin construccion $cadenaCheques si es diferente de ='' entonces cancela cheques
    } // fin si existe cheque  y  tipocancelacion

    return $flag; // regresa false si  el cheque fue encontrado en cheques_cr
    /*
    $SQL="SELECT DISTINCT supptrans.trandate, supptrans.tagref, purchorders.requisitionno AS requi,supptrans.hold as estatus, banktrans.chequeno,banktrans.banktranstype,supptransid,supptrans.transno,supptrans.suppreference,supptrans.ovamount,  purchorders.comments as obs,  
      IF( banktrans.ref LIKE '%CANCELADO%','Cancelado','Normal') AS estatusCheque
      FROM  banktrans 
      INNER JOIN supptrans on banktrans.transno  =supptrans.transno 
      INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
      INNER JOIN purchorders ON purchorders.orderno=supptransdetails.orderno
      where chequeno='".$ChequeNum."'";

      $ErrMsg = "No se obtuvo datos.";
      $TransResult = DB_query($SQL, $db, $ErrMsg);

      while ($myrow = DB_fetch_array($TransResult)) {
      
          $datos[] = array('id1'=>false,
                    
                     'fecha'=>$myrow['trandate'],//.' '.$myrow['tagdescription'],
                     'tag'=>$myrow['tagref'],
                     'requi'=>$myrow['requi'],
                     'estatus'=>fnEstado($myrow['estatus']),
                     'chequeno'=>$myrow['chequeno'],
                     'tipoPago'=>$myrow['banktranstype'],
                     'estatusCheque'=>$myrow['estatusCheque'],
                     'factura'=>$myrow['suppreference'],
                     'monto'=>$myrow['ovamount'],
                     'observaciones'=>$myrow['obs'],
                     'id'=>$myrow['id'],
                     'transno'=>$myrow['transno']
      );
      }
    */

    /* $qry    = "SELECT id,tagref,DATE_FORMAT(trandate, '%d/%m/%Y') as trandate
              FROM supptrans
              WHERE type = '$type' and transno = '$typeno'";
    $rsid     = DB_query($qry, $db);
    $row      = DB_fetch_array($rsid);
    $id501    = $row['id'];
    $PeriodNo = GetPeriod($row['trandate'], $db, $row['tagref']); 

    $SQL = "UPDATE supptrans
              SET transtext=concat('CANCELADO',' ',transtext,' ',ovamount,'+',ovgst),ovamount=0,ovgst=0,alloc=0
              where transno = " . $_POST['ChequeNum'] . " and type = " . $type;
    // echo $SQL . "<br>";
    $r = DB_query($SQL, $db, $ErrMsg);

    /*
    * $SQL = "delete from gltrans where type =" .$type." and typeno = " . $_POST['ChequeNum'];
    */

    /*$SQL = "INSERT INTO gltrans(counterindex, type, typeno, chequeno, trandate, periodno, account, narrative, amount, posted, jobref,tag,lasttrandate)
              select null, " . $type . ", " . $_POST['ChequeNum'] . ", 0, trandate, periodno ,account, CONCAT(narrative,' @ Movimiento Cancelado'),(amount*-1),0,0, tag,now()
              FROM gltrans
              WHERE type=" . $type . " and typeno=" . $_POST['ChequeNum'];

    $r = DB_query($SQL, $db, $ErrMsg); */
}

function fnGenerarChequeBanco($db, $TransNo, $Transtype, $bankaccount, $narrative, $ExRate, $FunctionalExRate, $fechacheque, $Tipopago, $saldocheque2, $moneda, $tagref, $Beneficiario, $ue, $ChequeNum = '', $nu_type = 0)
{
    /*$TransNo =$_POST['transno']; //GetNextTransNo($type, $db);
    $bankaccount =$_POST['bankaccount'];
    $tagref =$_POST['tagref'];

    $initransno = $TransNo;
    $saldocheque2=$_POST['saldo'];
    */
    //$supplierid . "@" . (($saldo) * (-1));
    // $ChequeNum='';
    if ($ChequeNum=='') {
      $ChequeNum = GetNextChequeNo($bankaccount, $db);
    }
    $SQL24="INSERT INTO banktrans (transno,
    type,
    bankact,
    ref,
    exrate,
    functionalexrate,
    transdate,
    banktranstype,
    amount,
    currcode,
    tagref,
    beneficiary,
    chequeno,
    usuario,
    ln_ue,
    nu_type,
    nu_anio_fiscal
    )
    VALUES ('" . $TransNo . "',
    '" . $Transtype . "',
    '" . $bankaccount . "',
    '" . $narrative . "',
    '" . $ExRate . "' , 
    '" . $FunctionalExRate . "',
    '".$fechacheque."',
    '" . $Tipopago. "',";
    //  $SQL .=    ($saldocheque2) * (-1) . ",
    $SQL24 .=    "'" .( ($saldocheque2) * (-1) ) ."',
    '" . $moneda . "',
    '" . $tagref . "',
    '" . $Beneficiario . "',
    '" . $ChequeNum . "',
    '" . $_SESSION['UserID'] . "',
    '" . $ue . "',
    '" . $nu_type . "',
    '" . $_SESSION['ejercicioFiscal'] . "'
    )";

    $ErrMsg = _('No pude insertar la transaccion bancaria porque');
    $DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');
    DB_query($SQL24, $db);
    return  $ChequeNum;
    //$result = DB_query($SQL24, $db, $ErrMsg, $DbgMsg, true);
}
function requisicion($transno, $type, $db)
{
    $requi= '';
    $info=array();
    $SQL="SELECT supptransdetails.requisitionno AS requi, SUBSTRING(supptransdetails.comments,1,254) as obs FROM supptrans  
    INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
    WHERE supptrans.type = '".$type."' AND supptrans.transno='".$transno."'";

    if ($_SESSION['UserID'] == 'desarrollo') {
      // echo "\n <pre>SQL: ".$SQL;
    }
  
    $ErrMsg = "no requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[]=$myrow['requi'];
        $info[]=$myrow['obs'];
    }
    return $info;
}
function fnVerficarSiEsUnificadi($sent, $folio)
{
    // $myrow['sent']<0 && !is_null($myrow['sent'])) ? ($myrow['sent']*-1 )  : $datosPago[1]
    if ($sent <0 && !is_null($sent)) {
        $sent=  ($sent*-1 ); // el menos  es el numero de cheque
    } else if ($sent >0 && !is_null($sent)) {
         $sent=  ($sent); // un numero de cheque es el numero de transaccione
    } else {
        $sent=$folio;
    }

    return  $sent;
}

function fnUnificadoOnormal($cheques, $bancosOrigen, $db)
{
    //pude  haber  hecho esto en el grid pero es mejor en el modelo
    $retorno=array();
    $cadenaCheques='';
    $cadenaBancosOrigen='';
    $union=array();
    for ($a=0; $a<count($cheques); $a++) {
        $cadenaCheques.="'".$cheques[$a]."',";
        $cadenaBancosOrigen.="'".$bancosOrigen[$a]."',";
        $union[]=$cheques[$a]."-".$bancosOrigen[$a];
    }
    $cadenaCheques=substr($cadenaCheques, 0, -1);
    $cadenaBancosOrigen=substr($cadenaBancosOrigen, 0, -1);
    //$SQL=" SELECT COUNT(*) AS total,chequeno,supptrans.id,banktrans.transno FROM banktrans INNER JOIN supptrans ON banktrans.transno=  supptrans.transno WHERE chequeno IN (".$cadenaCheques.") GROUP BY chequeno,banktrans.transno,supptrans.id ORDER BY chequeno ASC;";
    //antesde que hubiese varios bancos
    // $SQL="    SELECT COUNT(chequeno) as total,chequeno  FROM banktrans INNER JOIN supptrans ON banktrans.transno=  supptrans.transno WHERE chequeno IN (".$cadenaCheques.") GROUP BY chequeno ORDER BY chequeno";

    // con la  consulta  cuento cuentos cheques  existen con el mismo numero de cheque
    $SQL="SELECT COUNT(chequeno) as total, chequeno, banktrans.bankact, CONCAT(chequeno,\"-\",banktrans.bankact) as union1
    FROM banktrans 
    INNER JOIN supptrans ON banktrans.transno=  supptrans.transno AND supptrans.type = banktrans.nu_type
    WHERE chequeno IN (".$cadenaCheques.") AND banktrans.bankact IN (".$cadenaBancosOrigen.") 
    GROUP BY chequeno, banktrans.bankact ORDER BY chequeno;";
    $ErrMsg = "no requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    // echo "\n <pre>SQL ".$SQL."\n";
    //print_r($SQL);
    //$chequesNormalesIds=array();
    $chequesNormalesFolio=array();
    $chequesUnificados=array();
    while ($myrow = DB_fetch_array($TransResult)) {
        for ($a=0; $a<count($union); $a++) {
            if ($union[$a]==$myrow['union1']) {
                // if ($myrow['total']==1) {
                //     $chequesNormalesFolio[]=$myrow['union1'];
                // } else {
                //     $chequesUnificados[]=$myrow['union1'];
                // }
            }//fin if
        }//fin for
        if ($myrow['total']==1) {
            $chequesNormalesFolio[]=$myrow['union1'];
        } else {
            $chequesUnificados[]=$myrow['union1'];
        }
    }

    //$retorno[]=  $chequesNormalesIds;
    $retorno[]=  $chequesNormalesFolio;
    $retorno[]=  $chequesUnificados;

    return $retorno;
}

function fnGetIds($cheques, $db)
{
//pude  haber  hecho esto en el grid pero es mejor en el modelo
    $retorno=array();
    $cadenaCheques='';
    $cadenaBancosOrigen='';
    $SQL='';
    if (is_array($cheques)) {
        for ($a=0; $a<count($cheques); $a++) {
            $aux= explode("-", $cheques[$a]);
            $cadenaCheques.="'".$aux[0]."',";
            $cadenaBancosOrigen.="'".$aux[1]."',";
        }
        $cadenaCheques=substr($cadenaCheques, 0, -1);
        $cadenaBancosOrigen=substr($cadenaBancosOrigen, 0, -1);

        $SQL="  SELECT banktrans.chequeno,supptrans.id,banktrans.transno,banktrans.bankact 
        FROM banktrans 
        INNER JOIN supptrans ON banktrans.transno=  supptrans.transno AND banktrans.nu_type = supptrans.type
        WHERE chequeno IN (".$cadenaCheques.") 
        AND banktrans.bankact IN (".$cadenaBancosOrigen.") 
        AND supptrans.hold != '-2'
        GROUP BY banktrans.transno,banktrans.chequeno,supptrans.id,banktrans.bankact 
        ORDER BY banktrans.chequeno ASC;";
    } else {
        $aux= explode("-", $cheques);
      // $cadenaCheques="'".$cheques."'";
        $SQL="  SELECT banktrans.chequeno,supptrans.id,banktrans.transno,banktrans.bankact  
        FROM banktrans 
        INNER JOIN supptrans ON banktrans.transno=  supptrans.transno AND banktrans.nu_type = supptrans.type
        WHERE chequeno IN ('". $aux[0]."') 
        AND banktrans.bankact IN ('".$aux[1]."')
        AND supptrans.hold != '-2'
        GROUP BY banktrans.transno,banktrans.chequeno,supptrans.id,banktrans.bankact 
        ORDER BY banktrans.chequeno ASC;";
    }

    // echo "\n <pre>SQL: ".$SQL."\n";

    $ErrMsg = "no requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $ids=array();
    $cheques=array();
    $bancoOrigen=array();
    $cadenaChequesRes = "";
    while ($myrow = DB_fetch_array($TransResult)) {
      $ids[]=$myrow['id'];
      if (strpos($cadenaChequesRes, $myrow['chequeno']) === false) {
          $cheques[]=$myrow['chequeno'];
      }
      $cadenaChequesRes .= ",".$myrow['chequeno'];
      $bancoOrigen[]=$myrow['bankact'];
    }
    $retorno[]=$ids;
    $retorno[]=$cheques;
    $retorno[]=$bancoOrigen;
 
    return $retorno;
}
switch ($proceso) {
    case 'getFechaServidor':
        $info[] = array('fechaDMY'=>date("d-m-Y"),'fechaMdy'=>date("m-d-Y"));
        $contenido = array('Fecha'=> $info);
        $result = true;

        break;

    case 'getFechaServidorSiguiente':
        $ndias=$_POST['numerodias'];
   //$fecha= fnFeriadoOfin($ndias,$db);
        $fechas=array();
        $SQL="SELECT Fecha,FinDeSemana,Feriado FROM  DWH_Tiempo where Anio='".date("Y")."' AND (FinDeSemana=1 OR Feriado=1 ) ORDER BY FinDeSemana ASC";

        $ErrMsg = "No se obtuvo datos.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        while ($myrow = DB_fetch_array($TransResult)) {
            $fechas[]= array('fecha' =>$myrow['Fecha'],'fin' =>$myrow['FinDeSemana'],'feriado' =>$myrow['Feriado']  );
        }
        $fecha= fnFeriadoOfin1(0, $db, $fechas, 0);
        print_r("fin-...");
        exit();
    $info[] = array('fechaDMY'=>$fecha);

    //exit();
    $contenido = array('Fecha'=> $info);
    $result = true;

    break;

    case 'getMesServidor':
        $contenido = array('MesActual'=> (date("m")),'anioActual'=> (date("Y")) );
        $result = true;

        break;

    case 'datosConstruirCalendario': // mes a construir
        $mes=$_POST['mes'];
        $anio=$_POST['anio'];
        if ($anio==0) {
            $anioActual=date('Y');
        } else {
            $anioActual=$anio;
        }
    
        $fecha = '01-'.$mes.'-'.$anioActual;
        $nombreDiaInicioMes = date('D', strtotime($fecha));

        $totalDeDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anioActual);
        $info[] = array('totalDeDiasMes'=> $totalDeDiasMes,'nombreDiaInicioMes'=>$nombreDiaInicioMes );

        $contenido = array('construirCalendario'=> $info);
        $result = true;
        break;

    case 'historial':
        $datos=array();
        if (isset($_POST['transno'])) {
            $datos=fnHistorialCancelado($_POST['transno'], $db);
        }

            $contenido = array('historial'=> $datos);
            $result = true;


        break;

    case 'diasFeriadosDelMes':
        $dias=array();
        //$anioActual=date('Y');
        $mes=$_POST['mes'];
        $anioActual=$_POST['anio'];

        $SQL="SELECT  dia from DWH_Tiempo where   Fecha >=STR_TO_DATE('".$anioActual."-".$mes."-01','%Y-%m-%d %H:%i:%s') and  Fecha <= STR_TO_DATE('".$anioActual."-".$mes."-31','%Y-%m-%d %H:%i:%s') and Feriado=1  and NombreDia!='Domingo'";

        $ErrMsg = "No hay dias feriados";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $dias[] =$myrow['dia'];
        }
        $contenido = array('DiasFeriadosDelMes'=> $dias);
        $result = true;
        break;

    case 'calendario':
        $datosReqi=array();
        //$anioActual=date('Y');
        $anioActual=$_POST['anio'];
        $mes=$_POST['mes'];

        $SQL="
        SELECT
        supptrans.hold,
        supptrans.id as movimiento,
        supptrans.trandate as fecha,
        day(supptrans.trandate) as daytrandate,
        month(supptrans.trandate) as monthtrandate,
        year(supptrans.trandate) as yeartrandate,
        tags.tagdescription,
        supptrans.type as tipoDoc,
        supptrans.transtext as invtext,
        suppliers.suppname as name,
        systypescat.typename,
        supptrans.transno,
        supptrans.type
        supptrans
        FROM  tags 
        INNER JOIN supptrans on supptrans.tagref=tags.tagref
        JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = supptrans.tagref AND tb_sec_users_ue.ue = supptrans.ln_ue AND tb_sec_users_ue.userid = '" . $_SESSION['UserID'] . "'
        INNER JOIN systypescat on systypescat.typeid=supptrans.type
        , sec_unegsxuser, suppliers, legalbusinessunit 
        WHERE supptrans.type IN(SELECT distinct typeid FROM systypescat WHERE nu_tesoreria_pagos = 1)
        AND supptrans.tagref = tags.tagref
        AND (supptrans.hold ='1' OR  supptrans.hold ='2' OR supptrans.hold ='-9')
        and supptrans.supplierno = suppliers.supplierid
        and supptrans.trandate between  STR_TO_DATE('".$anioActual."-".$mes."-01', '%Y-%m-%d')
        and STR_TO_DATE('".$anioActual."-".$mes."-31', '%Y-%m-%d')
        and sec_unegsxuser.tagref = tags.tagref
        and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
        and legalbusinessunit.legalid = tags.legalid
        AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";
        $ErrMsg = "No se encontraron elementos para mostrar";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            // $info[] = array('title' =>$myrow['movimiento'].'/'.$myrow['name'],'start' =>$myrow['fecha'] );
            // $datosRequi =requisicion($myrow['transno'], $myrow['type'], $db);
            $info[] = array('titulo' => $datosReqi[0].$myrow['movimiento'].'/'.$myrow['name'],'dia' =>$myrow['daytrandate'],'fechaTran'=>($myrow['monthtrandate']."-".$myrow['daytrandate']."-".$myrow['yeartrandate']) );
        }
        $contenido = array('DatosCalendario'=> $info);
        $result = true;

        break;

    case 'facturasDias':

        $fecha=$_POST['fecha'];

        $SQL = "SELECT supptrans.tagref,
        supptrans.transno,
        supptrans.hold as estado,
        supptrans.supplierno,
        supptrans.supplierno as branchcode,
        supptrans.suppreference as reference,
        abs(supptrans.ovamount) as ovamount,
        supptrans.transtext as invtext,
        abs(supptrans.ovgst) as ovgst,
        abs(supptrans.alloc) as alloc,
        tags.tagdescription,
        suppliers.supplierid as supplierno,
        suppliers.suppname as name,
        supptrans.trandate,
        day(supptrans.trandate) as daytrandate,
        month(supptrans.trandate) as monthtrandate,
        year(supptrans.trandate) as yeartrandate,
        case when instr(folio,'|')>0 then folio else concat(type,'-',transno) end as folio,
        supptrans.id,
        legalbusinessunit.taxid,
        legalbusinessunit.address5,
        tags.tagdescription,
        tags.typeinvoice,
        supptrans.id as recibo,
        systypescat.typename
        FROM  tags INNER JOIN supptrans on supptrans.tagref=tags.tagref
        INNER JOIN systypescat on systypescat.typeid=supptrans.type
        , sec_unegsxuser, suppliers, legalbusinessunit
        WHERE supptrans.type IN(SELECT distinct typeid FROM systypescat WHERE nu_tesoreria_pagos = 1)
        AND supptrans.tagref = tags.tagref
        AND (supptrans.hold ='1' OR supptrans.hold ='2'  OR supptrans.hold ='-9')
        and supptrans.supplierno = suppliers.supplierid
        and supptrans.trandate between  STR_TO_DATE('" . $fecha . "', '%Y-%m-%d')
        and STR_TO_DATE('" . $fecha . "', '%Y-%m-%d')
        and sec_unegsxuser.tagref = tags.tagref
        and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' 
        and legalbusinessunit.legalid = tags.legalid ";

        /*
        WHERE supptrans.type IN('20')
        AND supptrans.tagref = tags.tagref
        AND supptrans.hold ='1' */

        $ErrMsg = "No se encontraron elementos para mostrar";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            //$info[] = array('title' =>$myrow['movimiento'].'/'.$myrow['name'],'start' =>$myrow['fecha'] );
            $info[] = array(  'id,2%,,'=>'<input type="checkbox" value="'.$myrow['id'].'"  name="selMovimientoDia"  class="selMovimientoDia">',
            'estado,4%,Estado,' => fnEstado($myrow['estado']),
                          'un,20%,URG,' => $myrow['tagdescription'],
                          'fo,20%,Folio,' => $myrow['folio'],
                          'ref,20%,Ref.proveedor,' =>$myrow['reference'],
                          'fecha,20%,Fecha,' =>$myrow['trandate'],
                          'prov,20%,Proveedor,' =>$myrow['name'],
                        
                          'ova,20%,Monto,' =>number_format($myrow['ovamount'], 2),
                          'ovg,20%,IVA,' =>number_format($myrow['ovgst'], 2),
                          'ova,20%,Total,' =>number_format(($myrow['ovamount']  + $myrow['ovgst']), 2)

                          );
        }
    
        $contenido = array('DatosDiasFactura'=> $info);
        $result = true;

        break;

    case 'buscarDatos':
        $anioActual=date('Y');
        $mesActual=date('m');
        $dateDesde='';
        $dateHasta='';
        if (!empty($_POST['dateDesde'])) {
            $dateDesde= date("Y-m-d", strtotime($_POST['dateDesde']));
        } else {
            $dateDesde=0; //$anioActual.'-'.$mesActual.'-'.'01';
        }
    
        if (!empty($_POST['dateHasta'])) {
            $dateHasta= date("Y-m-d", strtotime($_POST['dateHasta']));
        } else {
            $dateHasta=0;//$anioActual.'-'.$mesActual.'-'.'31';
        }
    
        /*
        $recibo= $_POST['recibo'];
        $proveedor= $_POST['proveedor'];
        $razonSocial= $_POST['razonSocial'];
        $tipoDocumento= $_POST['tipoDocumento']; */

        //$recibo=isset($_POST['recibo']);
        $proveedor='';
        //$razonSocial='';
        $tipoDocumento='';
        $estatusF='';

        if (isset($_POST['recibo'])) {
            $recibo= $_POST['recibo'];
        } else {
            $recibo=null;
        }
        if ($recibo=='' || $recibo==0) {
            $recibo=null;
        }

        if (isset($_POST['proveedor'])) {
            $proveedor= $_POST['proveedor'];
        } else {
            $proveedor='';
        }


        if (isset($_POST['razonSocial'])) {
            $razonSocial= $_POST['razonSocial'];
        } else {
            $razonSocial='';
        }

        if (isset($_POST['estatus'])) {
            $estatusF= $_POST['estatus'];
        } else {
            $estatusF='';
        }

        $tipoOperacion = $_POST['tipoOperacion'];
        $tagref = $_POST['tagref'];
        $ue = $_POST['ue'];

    $noCompromiso = $_POST['noCompromiso'];
    $noDevengado = $_POST['noDevengado'];
    $reciboTransferencia = $_POST['reciboTransferencia'];
    if ($reciboTransferencia=='' || $reciboTransferencia==0) {
            $reciboTransferencia=null;
        }

        /*if(isset($_POST['tipoDocumento'])) {
        $tipoDocumento= $_POST['tipoDocumento'];

        }
        else
        {
        $tipoDocumento='';
        } */

        $SQL = "SELECT 
        DISTINCT
        supptrans.tagref as UR,
        CONCAT(supptrans.tagref,' - ',tags.tagdescription) as urnombre,
        supptrans.transno,
        supptrans.hold as estado,
        supptrans.supplierno,
        supptrans.supplierno as branchcode,
        supptrans.suppreference as reference,
        abs(supptrans.ovamount) as ovamount,
        supptrans.transtext as invtext,
        abs(supptrans.ovgst) as ovgst,
        abs(supptrans.alloc) as alloc,
        tags.tagdescription,
        suppliers.supplierid as supplierno,
        CONCAT(suppliers.supplierid, ' - ', suppliers.suppname) as name,
        supptrans.trandate,
        supptrans.sent,
        day(supptrans.trandate) as daytrandate,
        month(supptrans.trandate) as monthtrandate,
        year(supptrans.trandate) as yeartrandate,
        supptrans.id,
        legalbusinessunit.taxid,
        legalbusinessunit.address5,
        tags.tagdescription,
        tags.typeinvoice,
        supptrans.id as recibo,
        supptrans.ln_ue,
        supptrans.type,
        systypescat.typename,
        supptrans.ref1,
        supptrans.ref2,
        supptrans.txt_referencia,
        supptrans.txt_clave_rastreo,
        CONCAT(supptrans.ln_ue,' - ',tb_cat_unidades_ejecutoras.desc_ue) as uenombre
        FROM  tags 
        INNER JOIN supptrans on supptrans.tagref=tags.tagref
        JOIN supptransdetails ON supptransdetails.supptransid = supptrans.id
        JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = supptrans.tagref AND tb_sec_users_ue.ue = supptrans.ln_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."' 
        JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = supptrans.tagref AND tb_cat_unidades_ejecutoras.ue = supptrans.ln_ue";

        if (!is_null($recibo)) {
            // $SQL.=" LEFT JOIN banktrans on supptrans.transno = banktrans.transno ";
        }

        $SQL.=" LEFT JOIN banktrans on supptrans.transno = banktrans.transno AND supptrans.type = banktrans.nu_type ";

        $SQL.=" INNER JOIN systypescat on systypescat.typeid=supptrans.type,sec_unegsxuser,suppliers, legalbusinessunit ";

        //  WHERE supptrans.type IN('480','24','121',501,20,22)
        $SQL = $SQL . "  WHERE supptrans.tagref = tags.tagref and supptrans.supplierno = suppliers.supplierid ";

        $SQL .=" AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."' ";

        if ($tipoOperacion != '') {
            $SQL .=" AND supptrans.type IN(".$tipoOperacion.") ";
        } else {
            // Si no tiene todos los pagos
            $SQL .=" AND supptrans.type IN(SELECT distinct typeid FROM systypescat WHERE nu_tesoreria_pagos = 1) ";
        }

        if ($tagref != '') {
            $SQL .=" AND supptrans.tagref IN(".$tagref.") ";
        }

        if ($ue != '') {
            $SQL .=" AND supptrans.ln_ue IN(".$ue.") ";
        }

        if ($dateDesde!=0 && $dateHasta!=0) {
            $SQL .=" and supptrans.trandate >=  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d') and supptrans.trandate <= STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d') ";
            //$SQL .=" and supptrans.trandate between  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')
            //and STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d') ";
            //$condicion .= " AND purchorders.orddate between '".$fechaini." 00:00:00' AND '".$fechafin." 23:59:59' ";
        } else if ($dateDesde!=0 && $dateHasta==0) {
            $SQL .=" and supptrans.trandate >=  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')";
        } else if ($dateDesde==0 && $dateHasta!=0) {
            $SQL .=" and supptrans.trandate <=  STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d')";
        }

        $SQL.=" and sec_unegsxuser.tagref = tags.tagref and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' and legalbusinessunit.legalid = tags.legalid ";

        if ($proveedor!='') {
            //$SQL .= " and supptrans.supplierno = '" .$proveedor."'"; LIKE 'a%';
            $SQL .= " and (supptrans.supplierno  LIKE '%".$proveedor."%' or suppliers.suppname  LIKE '%".$proveedor."%') ";
        }

        if ($estatusF!='' && $estatusF>=0) {
            $SQL .= " and supptrans.hold ='" . $estatusF."'";
        }

        /* se deshabilito
        if($razonSocial!='')
        {
        $SQL .= " and legalbusinessunit.legalid = '".$razonSocial."'";
        //$SQL .= " AND legalbusinessunit.legalid = '$razonSocial'";
        }*/
        // se deshabilito por el momento tipo documento
        /*if($tipoDocumento!='0' && ($tipoDocumento!='otros')) {

            $SQL .= " and systypescat.typeid = '" . $tipoDocumento . "'";

        }
        else if($tipoDocumento == 'otros') {
            $SQL .= " and systypescat.typeid NOT IN('480','22','24','121',501)";
        }*/

        if (!is_null($recibo)) {
            $SQL.=" AND banktrans.chequeno='".$recibo."' ";
            $SQL .= " AND banktrans.banktranstype = 'Cheque' ";
        }

        if (!is_null($reciboTransferencia)) {
          // Folio transferencia
            $SQL.=" AND banktrans.chequeno='".$reciboTransferencia."' ";
            $SQL .= " AND banktrans.banktranstype = 'Transferencia' ";
        }

        if ($noCompromiso !='' && $noCompromiso !='0') {
          // No comprimso
          $SQL.=" AND supptransdetails.nu_id_compromiso = '".$noCompromiso."' ";
        }

        if ($noDevengado !='' && $noDevengado !='0') {
          // No devengado
          $SQL.=" AND supptransdetails.nu_id_devengado = '".$noDevengado."' ";
        }

        // Condicición para que no salgan docuementos reversados, tienen estatus -2
        $SQL .= " AND supptrans.hold != '-2' ";
  
        $SQL.= " order by supptrans.id desc ";

        $ErrMsg = "No se encontraron elementos para mostrar";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        // $sql2=$SQL .$recibo.$proveedor.$razonSocial.$razonUnidadNegocio.$tipoDocumento;
        $nFila=0;
        $datosRequi=array();

        if ($_SESSION['UserID'] == 'desarrollo') {
          // echo "<pre>SQL: ".$SQL."\n";
          // exit();
        }

        $diaP= date('d');
        $mesP= date('m');
        $anioP= date('Y');
        $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);

        while ($myrow = DB_fetch_array($TransResult)) {
            //datos que se desean mostrar
            $nChequeAux=0;
            $idAux=0;
            $transAux=0;
            $cheuqeRepo=0;
            $Nvisual='';
            if($myrow['estado']!='7'){
              if ($_SESSION['UserID'] == 'desarrollo') {
                // echo "\n entra if";
              }
               $idAux=$myrow['id'];
               $transAux=$myrow['transno'];
            } else {
              if ($_SESSION['UserID'] == 'desarrollo') {
                // echo "\n entra else";
              }
                 $aux2=$myrow['ref2'];
                 $aux2=explode("-",$aux2 );
                 $idAux=$myrow['ref1'];
                 $transAux=$aux2[0];
                 $cheuqeRepo=$aux2[1];
            }
            //print($transAux);
            $datosRequi=  requisicion($transAux, $myrow['type'], $db);
            $liga="<a id='ligadetalle".$nFila."' data-info='".$idAux."' href='#' style='color: blue;'>".$datosRequi[0]."</a>";
            if ($_SESSION['UserID'] == 'desarrollo') {
              // echo "\n transAux: ".$transAux." - type: ".$myrow['type'];
              // print_r($datosRequi);
              // echo "\n";
              // echo "\n pos 0: ".$datosRequi[0];
              // echo "\n liga: ".$liga;
            }
            $datosPago = fnTraerTipoPago($db, $transAux, 0, $myrow['type']);
            // print_r($datosPago);
            //tipo pago
            //folio
            $ligaImprimir='';
            $ligaPoliza='';
            $estado=fnEstado($myrow['estado']);

            if ($estado=='Pagado') {
                 // $ligaPoliza=fnLiga($idAux, $db);
                 // $estado='<div class="estatusTeso"><a  id="ligaPoliza'.$nFila.'" href="'.$ligaPoliza[0].'" style="color: blue;"><u> Pagado</u></a></div>';
            }

             if ($estado=='Autorizado') {
                 $ligaPoliza=fnLiga($idAux, $db); 
            }
       
            //AND ($estado=='Finalizado')
            if (($datosPago[0]=="Cheque" )) { 
                //if (($datosPago[0]=="Cheque" and $ligaPoliza[0]!=''))
                if ($datosPago[2]!=0) { //EVITO cancelado o montos en cero
                    // $ligaImprimir='<a  id="ligaCheque'.$nFila.'" href="PrintCheque_01.php?PrintPDF=1&TransNo='.$datosPago[1].'&type=281&folio='.$datosPago[1].'&periodno='.$PeriodNo.'&fecha='.$myrow['trandate'].'&monto='.($datos[2]).'"  style="color: blue;"><u>'. $datosPago[0].'</u></a>';
                    $texto='';
                    $enc = new Encryption;

                    $concepto='';
                    if($myrow['ref1']!=NULL){
                        $Nvisual=$cheuqeRepo; //$datosPago[1];
                        $texto=$myrow['invtext']." ";
                        $concepto=$texto; //.$datosRequi[1];
                    }else{
                        $x=fnTraerTipoPago($db, $transAux, 2, $myrow['type']);
                        $Nvisual=$x[1];
                        $texto=$myrow['invtext']." ";
                        $concepto=$texto; //.$datosRequi[1];
                    }
                    // $url =  "&TransNo=>".$ligaPoliza[1]."&type=>22&folio=>".$datosPago[1]."periodo=>".$PeriodNo.'&fecha=>'.$myrow['trandate'].'&monto=>'.($myrow['ovamount']  + $myrow['ovgst']).'&beneficiario=>'.$myrow['name'].'&UR=>'.$myrow['urnombre'].'&concepto=>'.$datosRequi[1].'&numerocheque=>'.$datosPago[1];
                    $url =  "&TransNo=>".$datosPago[1]."&type=>281&folio=>".$datosPago[1]."periodo=>".$PeriodNo.'&fecha=>'.$myrow['trandate'].'&monto=>'.($datosPago[2]).'&beneficiario=>'.$myrow['name'].'&ur=>'.$myrow['UR'].'&urName=>'.$myrow['urnombre'].'&ue=>'.$myrow['ln_ue'].'&ueName=>'.$myrow['uenombre'].'&concepto=>'.$concepto.'&numerocheque=>'.$Nvisual;//
                    $urlSinCryp=  "&TransNo=".$transAux."&type=281&folio=".$datosPago[1]."periodo=".$PeriodNo.'&fecha='.$myrow['trandate'].'&monto='.($datosPago[2]).'&beneficiario='.$myrow['name'].'&ur='.$myrow['UR'].'&urName='.$myrow['urnombre'].'&ue='.$myrow['ln_ue'].'&ueName='.$myrow['uenombre'].'&concepto='.$datosRequi[1].'&numerocheque='.$datosPago[1];//

                    //fnVerficarSiEsUnificadi($myrow['sent'],$datosPago[1] );
                    $url = $enc->encode($url);
                    $ligaEncriptada="URL=" . $url;


                    $ligaImprimir='<a  id="ligaCheque'.$nFila.'" href="PrintCheque_01.php?'.$ligaEncriptada .'"  style="color: blue;" href="#">'. $datosPago[0].'</a>';
                } else {
                    $ligaImprimir=$datosPago[0];
                }
            } else if (($datosPago[0]=="Transferencia" and $ligaPoliza[0]!='')) {
                if ($datosPago[2]!=0) {
                    $ligaImprimir='<a id="ligaTransferencia'.$nFila.'"  style="color:blue" data-id="'.$datosPago[4].'" ><u>'.$datosPago[0].'</u></a>';
                } else {
                    $ligaImprimir=$datosPago[0];
                }
            } else {
                $ligaImprimir=$datosPago[0];
            }
            
            if(($myrow['estado']>6)){
              //    $aux3=$myrow['ref2'];
              //    $aux3=explode("-",$aux2 );
              //    $sql = "SELECT ln_chequeno FROM tb_cheques_cr WHERE nu_transno='".$aux3[0]."' order by ln_chequeno DESC LIMIT 1";
              //    $result = DB_query($sql, $db);
              // while($fila = DB_fetch_array($result)){
              //   $nChequeAux=$fila['ln_chequeno'];
              // }
              $nChequeAux=$Nvisual;
            }else{
               //  AQUI METO CODIGO PARA QUE SE MUESTRE VISUAL
               if($myrow['ref1']!=NULL){
                // $nChequeAux=";24";$datosPago[1];
               }else{
                $x=fnTraerTipoPago($db, $transAux, 2, $myrow['type']);
                $nChequeAux= $x[1];
               }
            }

            // Obtener compromiso y devengado cuando no se retenciones
            $noCompromiso = "";
            $noDevengado = "";
            if ($myrow['type'] != '298') {
              // si no es impuestos
              $SQL = "SELECT nu_id_compromiso, nu_id_devengado
              FROM supptransdetails
              WHERE supptransid = '".$myrow['id']."'";
              $resultComDev = DB_query($SQL, $db, $ErrMsg);
              $myrowComDev = DB_fetch_array($resultComDev);
              $noCompromiso = $myrowComDev['nu_id_compromiso'];
              $noDevengado = $myrowComDev['nu_id_devengado'];
            }

            if ($myrow['type'] == '501') {
                // Obtener folio del viatico
                $SQL = "SELECT sn_folio_solicitud FROM tb_viaticos WHERE systypeno = '".$myrow['transno']."'";
                $resultComDev = DB_query($SQL, $db, $ErrMsg);
                $myrowComDev = DB_fetch_array($resultComDev);
                $noDevengado = $myrowComDev['sn_folio_solicitud'];
            }
            
            $info[] = array(
              'checkPagos'=>false,
              /*'id'=>'<input type="checkbox" value="'.$myrow['id'].'"  name="selMovimiento"  class="selMovimiento" id="selMInput'.$nFila.'">', */
              'id'=>$idAux.'-'.$nFila.'-'.$datosRequi[0],
              'un' => $myrow['UR'],//$myrow['tagdescription'],
              'ue' => $myrow['ln_ue'],//$myrow['tagdescription'],
              'requi' => ($datosRequi[0] != 0 ? $liga : ''),
              'requiSinliga'=>($datosRequi[0] != 0 ? $datosRequi[0] : ''),
              'estado' => $estado,
              'estadoSinliga'=>fnEstado($myrow['estado'], 1),
              'tipoOperacion'=>$myrow['type'].' - '.$myrow['typename'],
              /*'ref' =>$myrow['reference'], */
              'tipoPago'=>  $ligaImprimir,
              'tipoPagoSinliga'=>$datosPago[0],
              //el checar el sent para checar si es unificado
              'fo2'=> $nChequeAux,//fnVerficarSiEsUnificadi($myrow['sent'],$datosPago[1] ), //folio
              'fecha' =>$myrow['trandate'],
              'prov' =>$myrow['name'],
              'fact' =>$myrow['reference'],
              'ova' =>$myrow['ovamount'],
              'ovg' =>$myrow['ovgst'],
              'ovat' =>$myrow['ovamount']  + $myrow['ovgst'],
              'obs'=>" ".$datosRequi[1],
              'idprov'=>$myrow['supplierno'],
              'bancoOrigen'=>$datosPago[3],
              'estatusN' => $myrow['estado'],
              'type' => $myrow['type'],
              'noCompromiso' => $noCompromiso,
              'noDevengado' => $noDevengado,
              'referencia' => $myrow['txt_referencia'],
              'rastreo' => $myrow['txt_clave_rastreo']
            );

            //totales
            $subtotalPago += $myrow['ovamount'];
            $ivaPago += $myrow['ovgst'];
            $totalPago += $myrow['ovamount']  + $myrow['ovgst'];
            //fin totales
            $nFila++;

            // facturas aplicadas

            $sqlfactura="SELECT  case when instr(folio,'|')>0 then folio else concat(type,'-',transno) end as folio,
                ((amt/rate)+abs(diffonexch)) as amt,tagdescription,trandate,transno,transtext as invtext,typename,day(trandate) as daytrandate,
                month(trandate) as monthtrandate,year(trandate) as yeartrandate,typename,supptrans.suppreference,
                supptrans.ovgst/supptrans.ovamount as porciva
                 FROM supptrans INNER JOIN suppallocs on suppallocs.transid_allocto=supptrans.id
                INNER JOIN tags on tags.tagref=supptrans.tagref
                INNER JOIN systypescat on systypescat.typeid=supptrans.type
                 where  suppallocs.transid_allocfrom=".$myrow['recibo'];
            $resultbanco=DB_query($sqlfactura, $db, $ErrMsg);
            while ($myrowfacturas=DB_fetch_array($resultbanco)) {
                /*echo "<tr bgcolor=#F7F8E0>";
                echo "<td style='font-size:7pt;'>" . $myrowfacturas['tagdescription'] . "</td>";
                echo "<td style='font-size:7pt;' nowrap>" . $myrowfacturas['daytrandate'] . " - " . glsnombremescorto($myrowfacturas['monthtrandate']) . " - " . $myrowfacturas['yeartrandate'] . "</td>";
                echo "<td style='font-size:7pt; text-align:center;'>" . $myrowfacturas['folio'] . "</td>";
                echo "<td style='font-size:7pt; text-align:center;'>" . $myrowfacturas['suppreference'] . "</td>";
                echo "<td style='font-size:7pt;' colspan=3>" . $myrowfacturas['invtext'] . "</td>";
                echo "<td style='font-size:7pt;' colspan=2>Ad24". $myrowfacturas['typename'] . "</td>";
                echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrowfacturas['amt']/(1+$myrowfacturas['porciva']),2) . "</td>";
                echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrowfacturas['amt']/(1+$myrowfacturas['porciva'])*$myrowfacturas['porciva'],2) . "</td>";
                echo "<td style='font-size:7pt; text-align:right;'>" . number_format(($myrowfacturas['amt']),2) . "</td>";
                //echo "<td colspan=2 style='font-size:7pt; text-align:right;'></td>";
                echo "<tr>";
                */
            
                $subtotalApli += $myrowfacturas['amt']/(1+$myrowfacturas['porciva']);
                $ivaApli += $myrowfacturas['amt']/(1+$myrowfacturas['porciva'])*$myrowfacturas['porciva'];
                $totalApli += $myrowfacturas['amt'];
            
                $totalIVAAplicado += $myrowfacturas['amt']/(1+$myrowfacturas['porciva'])*$myrowfacturas['porciva'];
            }
            // fin facturas aplicadas
        }


        $infoTotalesPagos[]=array('subtotalPago'=>number_format($subtotalPago, 2),'ivaPago'=>number_format($ivaPago, 2),'totalPago'=>number_format(($totalPago), 2) );


        $infoTotalesFacturas[]=array('subtotalAplicado'=>number_format($subtotalApli, 2),'ivaPagoAplicado'=>number_format($ivaApli, 2),'totalPagoAplicado'=>number_format(($totalApli), 2) );

        $infoTotalesPendientes[]=array('subtotalPendiente'=>number_format(($subtotalPago-$subtotalApli), 2),'ivaPagoPendiente'=>number_format(($ivaPago-$ivaApli), 2),'totalPagoPendiente'=>number_format(($totalPago-$totalApli), 2) );


        $colRtotal= ", aggregates: [{'<b>Total</b>' :".
                            "function (aggregatedValue, currentValue) {".
                                "var total = currentValue;".
                                "return aggregatedValue + total;".
                            "}".
                        "}] ";

    // Columnas para el GRID
        $columnasNombres='';
        $columnasNombresGrid ='';
        $columnasNombres .= "[";
        $columnasNombres .= "{ name: 'checkPagos', type: 'bool' },";
        $columnasNombres .= "{ name: 'idprov', type: 'string' },";
        $columnasNombres .= "{ name: 'id', type: 'string' },";
        $columnasNombres .= "{ name: 'un', type: 'string' },";
        $columnasNombres .= "{ name: 'ue', type: 'string' },";
        $columnasNombres .= "{ name: 'fecha', type: 'string' },"; /*fecha que corresponde de almacen */
        $columnasNombres .= "{ name: 'requi', type: 'string' },"; /*folio requi */
        $columnasNombres .= "{ name: 'requiSinliga', type: 'string' },"; /*folio requi */

        $columnasNombres .= "{ name: 'noCompromiso', type: 'string' },";
        $columnasNombres .= "{ name: 'noDevengado', type: 'string' },";

        $columnasNombres .= "{ name: 'tipoOperacion', type: 'string' },";

        $columnasNombres .= "{ name: 'estado', type: 'string' },";
        $columnasNombres .= "{ name: 'estadoSinliga', type: 'string' },";
    
        $columnasNombres .= "{ name: 'tipoPago', type: 'string' },";/*tipo docu */
        $columnasNombres .= "{ name: 'tipoPagoSinliga', type: 'string' },";
    /*$columnasNombres .= "{ name: 'ref', type: 'string' },"; */
        $columnasNombres .= "{ name: 'fo2', type: 'string' },"; /*folio docu */
        $columnasNombres .= "{ name: 'fact', type: 'string' },";
        $columnasNombres .= "{ name: 'prov', type: 'string' },";

        $columnasNombres .= "{ name: 'referencia', type: 'string' },";
        $columnasNombres .= "{ name: 'rastreo', type: 'string' },";

        $columnasNombres .= "{ name: 'ova', type: 'number' },";
        $columnasNombres .= "{ name: 'ovg', type: 'number' },";
        $columnasNombres .= "{ name: 'ovat', type: 'number' },";
        $columnasNombres .= "{ name: 'obs', type: 'string' },";
        $columnasNombres .= "{ name: 'bancoOrigen', type: 'string' },";
        $columnasNombres .= "{ name: 'estatusN', type: 'string' },";
        $columnasNombres .= "{ name: 'type', type: 'string' }";
    
        $columnasNombres .= "]";
    // Columnas para el GRID
        $columnasNombresGrid .= "[";
        $columnasNombresGrid .= " { text: '', datafield: 'checkPagos', width: '3%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
        $columnasNombresGrid .= " { text: 'idprov', datafield: 'idprov', width: '19%', cellsalign: 'center', align: 'center', hidden:true},";
        $columnasNombresGrid .= " { text: '', datafield: 'id', width: '0%', cellsalign: 'center', align: 'center', hidden:true},";
        $columnasNombresGrid .= " { text: 'UR', datafield: 'un', width: '4%', cellsalign: 'center', align: 'center', hidden:false },";
        $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '3%', cellsalign: 'center', align: 'center', hidden:false },";
        $columnasNombresGrid .= " { text: 'Fecha ', datafield: 'fecha', width: '7%', cellsalign: 'center', align: 'center', hidden:false},";
        $columnasNombresGrid .= " { text: 'Requisición', datafield: 'requi', width: '7%', cellsalign: 'center', align: 'center', hidden:false},";
        $columnasNombresGrid .= " { text: 'Requisición', datafield: 'requiSinliga', width: '7%', cellsalign: 'center', align: 'center', hidden:true},";

        $columnasNombresGrid .= " { text: 'No. Compromiso', datafield: 'noCompromiso', width: '9%', cellsalign: 'center', align: 'center', hidden:false},";
        $columnasNombresGrid .= " { text: 'No. Devengado', datafield: 'noDevengado', width: '8%', cellsalign: 'center', align: 'center', hidden:false},";

        $columnasNombresGrid .= " { text: 'Operación', datafield: 'tipoOperacion', width: '10%', cellsalign: 'center', align: 'center', hidden:false},";

        $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estado', width: '10%', cellsalign: 'center', align: 'center', hidden:false},";
        $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estadoSinliga', width: '10%', align: 'center', hidden:true},";

        $columnasNombresGrid .= " { text: 'Tipo', datafield: 'tipoPago', width: '8%', align: 'center',cellsalign: 'center', hidden:false},";
        $columnasNombresGrid .= " { text: 'Tipo', datafield: 'tipoPagoSinliga', width: '10%', align: 'center',cellsalign: 'center', hidden:true},";
    /*$columnasNombresGrid .= " { text: 'Referencia', datafield: 'ref', width: '12%', cellsalign: 'center', align: 'center', hidden:false},";
    */
        $columnasNombresGrid .= " { text: 'Folio', datafield: 'fo2', width: '6%', cellsalign: 'center', align: 'center', hidden:false},";

        $columnasNombresGrid .= " { text: 'Factura', datafield: 'fact', width: '10%', cellsalign: 'center', align: 'center', hidden:false},";

        $columnasNombresGrid .= " { text: 'Proveedor', datafield: 'prov', width: '11%', cellsalign: 'center', align: 'center', hidden:false},";

        $columnasNombresGrid .= " { text: 'Referencia', datafield: 'referencia', width: '10%', cellsalign: 'center', align: 'center', hidden:false},";
        $columnasNombresGrid .= " { text: 'Clave Rastreo', datafield: 'rastreo', width: '10%', cellsalign: 'center', align: 'center', hidden:false},";

        $columnasNombresGrid .= " { text: 'Subtotal', datafield: 'ova', width: '10%', cellsalign: 'right', align: 'center', hidden:true,cellsformat: 'C2'".$colRtotal."},";
        $columnasNombresGrid .= " { text: 'IVA', datafield: 'ovg', width: '10%', cellsalign: 'right', align: 'center', hidden:true,cellsformat: 'C2'".$colRtotal."},";
        $columnasNombresGrid .= " { text: 'Total', datafield: 'ovat', width: '12%', cellsalign: 'right', align: 'center',cellsformat: 'C2', hidden:false".$colRtotal."},";
        $columnasNombresGrid .= " { text: 'Observaciones', datafield: 'obs', width: '19%', cellsalign: 'left', align: 'center', hidden:false},";
        $columnasNombresGrid .= " { text: 'Banco origen', datafield: 'bancoOrigen', width: '19%', cellsalign: 'center', align: 'center', hidden:true},";
        $columnasNombresGrid .= " { text: 'estatusN', datafield: 'estatusN', width: '10%', cellsalign: 'center', align: 'center', hidden:true},";
        $columnasNombresGrid .= " { text: 'type', datafield: 'type', width: '10%', cellsalign: 'center', align: 'center', hidden:true}";
   
        $columnasNombresGrid .= "]";

       // $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
        $funcion = 244;
        $nombre= traeNombreFuncionGeneral($funcion, $db);
        $nombre=str_replace(" ", "_", $nombre);
        $nombreExcel = $nombre.'_'.date('dmY');

        $contenido = array('DatosPagos' => $info,'DatosTotales'=>$infoTotalesPagos,'DatosTotalesAplicados'=>$infoTotalesFacturas,'DatosTotalesPendientes'=> $infoTotalesPendientes, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    
        $result = true;

        break;

    case 'detallesPagoProgramado':
        //$transno= array();
        $titulo = $_POST['titulo'];
        //$transno=6301;
        //$porciones = explode(" ", $pizza);
        //$transno = explode('/', $titulo);
        //$transno =str_split("aabbccdd", 2);
        $transno= explode('/', $_POST['titulo']);

        $SQL = "SELECT supptrans.tagref,
        supptrans.transno,
        supptrans.supplierno,
        supptrans.supplierno as branchcode,
        supptrans.suppreference as reference,
        abs(supptrans.ovamount) as ovamount,
        supptrans.transtext as invtext,
        abs(supptrans.ovgst) as ovgst,
        abs(supptrans.alloc) as alloc,
        tags.tagdescription,
        suppliers.supplierid as supplierno,
        suppliers.suppname as name,
        supptrans.trandate,
        day(supptrans.trandate) as daytrandate,
        month(supptrans.trandate) as monthtrandate,
        year(supptrans.trandate) as yeartrandate,
        case when instr(folio,'|')>0 then folio else concat(type,'-',transno) end as folio,
        supptrans.id,
        legalbusinessunit.taxid,
        legalbusinessunit.address5,
        tags.tagdescription,
        tags.typeinvoice,
        supptrans.id as recibo,
        supptrans.type,
        systypescat.typename,
        supptrans.ln_ue
        FROM  tags INNER JOIN supptrans on supptrans.tagref=tags.tagref
        INNER JOIN systypescat on systypescat.typeid=supptrans.type
        , sec_unegsxuser, suppliers, legalbusinessunit 
        WHERE supptrans.type IN(SELECT distinct typeid FROM systypescat WHERE nu_tesoreria_pagos = 1)
        and supptrans.id='".$transno[0]."'
        AND supptrans.tagref = tags.tagref
        and supptrans.supplierno = suppliers.supplierid
        and sec_unegsxuser.tagref = tags.tagref
        and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
        and legalbusinessunit.legalid = tags.legalid ";
        $ErrMsg = "No se encontraron elementos para mostrar";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
          $datosReqi=requisicion($myrow['transno'], $myrow['type'], $db);
          $info[] = array(
            'urg' =>$myrow['tagref']."-".$myrow['tagdescription'],
            'requi' =>$datosReqi[0],'reference'=>$myrow['reference'],
            'proveedor'=>$myrow['name'],
            'monto'=>number_format($myrow['ovamount'], $_SESSION['DecimalPlaces'], '.', ','),
            'iva'=>number_format($myrow['ovgst'], $_SESSION['DecimalPlaces'], '.', ','),
            'total'=>number_format(($myrow['ovamount']  + $myrow['ovgst']), $_SESSION['DecimalPlaces'], '.', ','),
            'obs'=>$datosReqi[1],
            'ue' =>"'".fnGetUe($myrow['tagref'], $myrow['ln_ue'], $db) ."'",
            'fecha'=>$myrow['trandate'],
            'tipoOperacion'=>$myrow['type'].' - '.$myrow['typename']
          ); //$myrow['ue'] );
          //  number_format($totalEstadoPresupuesto, $_SESSION['DecimalPlaces'], '.', ',');
        }
        $contenido = array('DetallesPago'=> $info);
        $result = true;

        break;
    case 'programarPago':
        $transno_act=$_POST['transno_act'];
        $tagref=$_POST['urs'];
        $importe=$_POST['montos'];
        $estatusN=$_POST['sn'];

        $referencia = $_POST['referencia'];
   
        $var=$_POST['FechaPago'];
        $fecha=date("d-m-Y", strtotime($var));
        $fecha1=date("Y-m-d", strtotime($var));
        $ratefactura="1";
        $datosFecha=explode('-', $fecha);
        $diaP=$datosFecha[0]; //date('d');
        $mesP= $datosFecha[1];//date('m');
        $anioP= $datosFecha[2]; //date('Y');
   
        $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);
       
        //$facturasAprogramar='';
        // quite esta parte por que ya con los estados de la reimpresion no se puede hacer asi
        // for ($x=0; $x<count($transno_act); $x++) {
        //     $facturasAprogramar.="'".$transno_act[$x]."',";
        // }
        // $facturasAprogramar=substr($facturasAprogramar, 0, -1);
        // $SQL="UPDATE supptrans set hold='1', trandate=STR_TO_DATE('".$fecha."','%d-%m-%Y %H:%i:%s') WHERE id IN (". $facturasAprogramar.")";
        //      $ErrMsg = "No se programo el movimiento ";
        //      
        $n='';
        for ($x=0; $x<count($transno_act); $x++) {
          if($estatusN[$x]=='-8'){
            $n='-9';
          }else{
            $n='1';
          }
          $SQL="UPDATE supptrans 
          set 
          hold='".$n."', 
          txt_referencia = '".$referencia."', 
          trandate=STR_TO_DATE('".$fecha."','%d-%m-%Y %H:%i:%s') 
          WHERE id = '".$transno_act[$x]."'";
          $ErrMsg = "No se programo el movimiento ";
          $TransResult = DB_query($SQL, $db, $ErrMsg);
        }
        $contenido = "Se programo pago.";
        $result = true;

        break;

    case 'reprogramarPago':
        $transno_act=$_POST['transno_act'];
        $var=$_POST['FechaPago'];
        $fecha=date("d-m-Y", strtotime($var));
        $referencia = $_POST['referencia'];
    
        $facturasAprogramar='';
        for ($x=0; $x<count($transno_act); $x++) {
            $facturasAprogramar.="'".$transno_act[$x]."',";
        }

        $facturasAprogramar=substr($facturasAprogramar, 0, -1);
        $SQL="UPDATE  supptrans set trandate=STR_TO_DATE('".$fecha."','%d-%m-%Y %H:%i:%s'), txt_referencia = '".$referencia."' 
        WHERE id IN (". $facturasAprogramar.")";
        $ErrMsg = "No se programo el movimiento ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $Transtype='281';

        $contenido = "Se programo pago.";
        $result = true;

        break;
    case 'getBanco':
        $info=array();
        $tagref=0;
        $supplierno=0;
        // Se quita esta tabla
        // tagsxbankaccounts, -- AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
        $SQL="SELECT 
        distinct bankaccounts.accountcode as cuenta,
        bankaccountname as banco,
        bankaccounts.currcode as moneda
        FROM bankaccounts, chartmaster, sec_unegsxuser, tb_sec_users_ue
        WHERE bankaccounts.accountcode=chartmaster.accountcode 
        AND bankaccounts.nu_activo = 1
        AND bankaccounts.tagref = sec_unegsxuser.tagref 
        AND tb_sec_users_ue.tagref = bankaccounts.tagref
        AND tb_sec_users_ue.ue = bankaccounts.ln_ue
        AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
        AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'";
        
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array('banco' =>$myrow['banco'],'cuenta' =>$myrow['cuenta']);
        }



        $contenido = array('DatosBanco' => $info);
    
        $result = true;

        break;

    case 'datosUnificados':
        try {
            $validacion=array();
            $ids=$_POST['ids'];
            $validacion=fnValidarMatrizPagado($db,$ids);

            // Validar estatus del registro para realizar actualizacion
            for ($x=0; $x<count($ids); $x++) {
                $SQL = "SELECT hold FROM supptrans WHERE id = '".$ids[$x]."'";
                $SQL = "SELECT supptrans.hold, supptransdetails.clavepresupuestal
                FROM supptrans
                JOIN supptransdetails ON supptrans.id = supptransdetails.supptransid
                WHERE supptrans.id = '".$ids[$x]."'";
                $ErrMsg = "No se obtuvo el estatus para validación";
                $TransResult = DB_query($SQL, $db);
                $mensajeEstatus = 0;
                $infoClaves = array();
                while ($myrow = DB_fetch_array($TransResult)) {
                    if (($myrow['hold'] != '1' && $myrow['hold'] != '-9') && $mensajeEstatus == 0) {
                        // No se programado
                        $validacion[0] = $validacion[0] . '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Un pago seleccionado se encuentra en estatus '.fnEstado($myrow['hold'], 1).', no puede ser autorizado</p>';
                        $validacion[1] = false;
                        $mensajeEstatus = 1;
                    }

                    $infoClaves[] = array(
                        'accountcode' => $myrow ['clavepresupuestal']
                    );
                }

                $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                if (!$respuesta['result']) {
                    $validacion[1] = false;
                    $validacion[0] .= $respuesta['mensaje'];
                }
            }
            // print_r($_POST);
            // exit();
            if($validacion[1]==true){
                // echo "\n proceso bien";
                // exit();
                $cadenaInsertar='';
                if (isset($_POST['ids'])) {
                    $ids=$_POST['ids'];
                    $requis=$_POST['requis'];
                    $tags=$_POST['tags'];
                    $total=$_POST['total'];
                    $banco=$_POST['banco'];
                    $cadenaIds='';
                    $tipopago=$_POST['tipoPago'];
                    $numeroReferencia=-1;
                    $claveRastreo = $_POST['claveRastreo'];

                    $total = trim($total, "'");
                    $total = trim($total, '"');

                    for ($a=0; $a<count($ids); $a++) {
                        $id = trim($ids[$a], "'");
                        $id = trim($id, '"');

                        $requi = trim($requis[$a], "'");
                        $requi = trim($requi, '"');

                        $cadenaInsertar.="('".$id."','".$requi."','".$tags[$a]."','".$total."','".$_SESSION['UserID']."'),";
                        $cadenaIds.="'".$id ."',";
                    }
                    $cadenaInsertar=substr($cadenaInsertar, 0, -1);
                    $cadenaIds=substr($cadenaIds, 0, -1);

                    //deberia  validar a partir del id supptrans si el pago ya fue unificado o que no existe
                    // $SQL= "INSERT INTO  tb_unificados (nu_id_transaccion,nu_requisicion,ln_tag,nu_total,ln_usuario)  VALUES ".$cadenaInsertar;

                    // $ErrMsg = "No se eliminó ";
                    // $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $SQL3="UPDATE supptrans set hold='2', txt_clave_rastreo = '".$claveRastreo."' WHERE id IN (".$cadenaIds.")";
                    $ErrMsg = "No se autorizo factura en pago unificado";
                    $TransResult = DB_query($SQL3, $db);

                    $ChequeNum = GetNextChequeNo($banco, $db);
                    // insertar a tipo de pago  cheque o transferencia
                    //print($chequenum);
                    $SQL4="  SELECT
                    supptrans.id as id,
                    supptrans.trandate as fecha,
                    tags.tagdescription,
                    supptrans.type as tipoDoc,
                    /*supptrans.transtext as invtext, */
                    supptrans.hold as status,
                    supptrans.transno,
                    supptrans.suppreference,
                    supptrans.ovamount,
                    supptrans.ovgst,
                    supptrans.alloc,
                    supptrans.supplierno,
                    supptrans.tagref,
                    supptrans.rate,
                    supptrans.diffonexch,
                    supptrans.ln_ue,
                    systypescat.typename
                    FROM  tags INNER JOIN supptrans on supptrans.tagref=tags.tagref
                    INNER JOIN systypescat on systypescat.typeid=supptrans.type
                    , sec_unegsxuser, suppliers, legalbusinessunit 
                    WHERE supptrans.id IN (". $cadenaIds.")
                    and supptrans.supplierno = suppliers.supplierid
                    and sec_unegsxuser.tagref = tags.tagref
                    and sec_unegsxuser.userid = '{$_SESSION ['UserID']}'
                    and legalbusinessunit.legalid = tags.legalid";
                    $ErrMsg = "fallo en obtener  datos de factura";
                    $TransResult = DB_query($SQL4, $db);
                    $datosFacturaAut =array();
                    while ($myrow = DB_fetch_array($TransResult)) {
                        // print($tipopago);
                        // exit();
                        // $datosFacturaAut[] = array('ovamount' =>$myrow['ovamount'],'ovgst' =>$myrow['ovgst'],'alloc' =>$myrow['alloc'],
                        //  'status' =>$myrow['status'],'supplierno' =>$myrow['supplierno'],'tagref' =>$myrow['tagref'],'transno' =>$myrow['transno'],'rate' =>$myrow['rate'],'diffonexch' =>$myrow['diffonexch'],'suppreference' =>$myrow['suppreference']);

                        switch ($tipopago) {
                            case '01':
                                //orden de pago sn definir
                                break;

                            case '02':
                                $rate='';
                                $TransNo='';
                                $bankaccount='';
                                $tagref='';
                                $saldocheque2='';
                                $rate='';
                                //$TransNo,$Transtype ,$bankaccount,$narrative,$ExRate ,$FunctionalExRate,$fechacheque,$Tipopago,$saldocheque2, $moneda,$tagref,$Beneficiario
                                $saldo= ($myrow['ovamount'] + $myrow['ovgst']);
                                $numeroReferencia= fnGenerarChequeBanco($db, $myrow['transno'], '281', $banco, 'narrativa', $myrow['rate'], $myrow['rate'], $myrow['fecha'], 'Cheque', $saldo, "MXN", $myrow['tagref'], 'beneficiario', $myrow['ln_ue'], $ChequeNum, $myrow['tipoDoc']);
                                break;

                            case '03':
                                //  $saldo= ($myrow['ovamount'] + ($myrow['ovgst'] - $myrow['alloc']));
                                $saldo= ($myrow['ovamount'] + $myrow['ovgst']);
                                $numeroReferencia=   fnGenerarChequeBanco($db, $myrow['transno'], '281', $banco, 'narrativa', $myrow['rate'], $myrow['rate'], $myrow['fecha'], 'Transferencia', $saldo, "MXN", $myrow['tagref'], 'beneficiario', $myrow['ln_ue'], $ChequeNum, $myrow['tipoDoc']);
                                // fnGenerarChequeBanco($db, $myrow['transno'],'20','0123456789','narrativa',$myrow['rate'],$myrow['rate'],$myrow['fecha'],'Cheque',$myrow['ovamount'],"MXN",$myrow['tagref'],'beneficiario',$banco);
                                break;
                        }
                    }

                    /*$SQL2="UPDATE supptrans SET sent='".($numeroReferencia*-1)."'  WHERE id IN (".$cadenaIds.")";

                    $ErrMsg = "No se guardo información";
                    $TransResult = DB_query($SQL2, $db, $ErrMsg);*/

                    $contenido ='Se guardo datos de pago unificado';
                    $result = true;
                }// fin if

                $transno_act=$_POST['ids'];
                $tagref=$_POST['tags'];
                //$importe=$_POST['montos'];

                $importe=0;
                $ratefactura="1";
                $var=$_POST['fechas'];

                $Transtype='281';
                //movimientos  ejercido
                for ($x1=0; $x1<count($transno_act); $x1++) {
                    $fecha=date("d-m-Y", strtotime($var[$x1]));
                    $fecha1=date("Y-m-d", strtotime($var[$x1]));
     
                    $datosFecha=explode('-', $fecha);
                    $diaP=$datosFecha[0]; //date('d');
                    $mesP= $datosFecha[1];//date('m');
                    $anioP= $datosFecha[2]; //date('Y');
       
                    $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);

                    $TransNo = GetNextTransNo($Transtype, $db);

                    // Folio de la poliza por unidad ejecutora
                    $folioPolizaUe = 0; // fnObtenerFolioUeGeneral($db, $tagref[$x1], $registro ['ln_ue']);

                    $consulta= "SELECT stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
                    ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
                    JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
                    WHERE stockid=stk),2) 
                    AS impuesto,
                    supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
                    supptrans.ln_ue,
                    supptrans.supplierno,
                    supptransdetails.ln_clave_iden,
                    tb_cat_partidaspresupuestales_partidagenerica.pargcalculado as categoryid,
                    supptransdetails.period,
                    supptransdetails.nu_id_compromiso,
                    supptransdetails.nu_id_devengado,
                    supptransdetails.nu_idret,
                    supptransdetails.detailid,
                    chartdetailsbudgetbytag.tagref as tagrefClave
                    FROM supptrans
                    INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
                    JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = supptransdetails.clavepresupuestal
                    JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
                    JOIN tb_cat_partidaspresupuestales_partidagenerica ON tb_cat_partidaspresupuestales_partidagenerica.ccap = tb_cat_partidaspresupuestales_partidaespecifica.ccap AND tb_cat_partidaspresupuestales_partidagenerica.ccon = tb_cat_partidaspresupuestales_partidaespecifica.ccon AND tb_cat_partidaspresupuestales_partidagenerica.cparg = tb_cat_partidaspresupuestales_partidaespecifica.cparg
                    WHERE id = ('".$transno_act[$x1]."')";
                    $resultado = DB_query($consulta, $db);
                    // Array para folios agrupados (UR-UE)
                    $infoFolios = array();
                    while ($registro = DB_fetch_array($resultado)) {
                        $tagrefClave = $registro['tagrefClave'];
                        $datosReqi= requisicion($registro['transno'], $registro['type'], $db);
                        
                        // Ver si existe folio para movimientos
                        $folioPolizaUe = 0;
                        foreach ($infoFolios as $datosFolios) {
                            // Recorrer para ver si exi
                            if ($datosFolios['tagref'] == $tagrefClave && $datosFolios['ue'] == $registro ['ln_ue']) {
                                // Si existe
                                $folioPolizaUe = $datosFolios['folioPolizaUe'];
                            }
                        }
                        if ($folioPolizaUe == 0) {
                            // Si no existe folio sacar folio
                            // $transno = GetNextTransNo($type, $db);
                            // Folio de la poliza por unidad ejecutora
                            $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagrefClave, $registro ['ln_ue'], $Transtype);
                            $infoFolios[] = array(
                                'tagref' => $tagrefClave,
                                'ue' => $registro ['ln_ue'],
                                'type' => $Transtype,
                                'transno' => $TransNo,
                                'folioPolizaUe' => $folioPolizaUe
                            );
                        }

                        $infoClaves = array();
                        $infoClaves[] = array(
                            'accountcode' => $registro ['clavepresupuestal']
                        );
                        $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                        $PeriodNo = $respuesta['periodo'];

                        $importe = ( (  ($registro["price"])* ($registro["qty"]))  * (1 + 0)) / $ratefactura;
                        // momentaneo
                        
                        // $montopresupuestal= truncateFloat(($montocom ), $digitos);
                        GeneraMovimientoContablePresupuesto($Transtype, "DEVENGADO", "EJERCIDO", $TransNo, $PeriodNo, $importe, $tagref[$x1], $fecha1, $registro["clavepresupuestal"], $TransNo, $db, false, '', '', '', $registro ['ln_ue'], 1, $ChequeNum, $folioPolizaUe, $registro["detailid"]);

                        // Log Presupuesto
                        $descriptionLog = "Autorización de pago.";
                        //$tagref[$x1]
                        $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagref[$x1], $registro["clavepresupuestal"], $registro['period'], $importe, 260, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Abono

                        $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagref[$x1], $registro["clavepresupuestal"], $registro['period'], $importe * -1, 261, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Cargo
                        //fin momentaneo
                        //fin movimientos ejercidos
                        
                        if (trim($registro['ln_clave_iden']) != '') {
                            $sqlWhere = " AND ln_clave = '".$registro['ln_clave_iden']."' ";
                        }
                        $SQL = "SELECT stockact, accountegreso FROM tb_matriz_pagado WHERE categoryid = '".$registro['categoryid']."' AND stockact IN (SELECT accountcode FROM accountxsupplier WHERE supplierid = '".$registro["supplierno"]."') ".$sqlWhere;
                        $resultCuenta = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

                        while ($myrowCuenta=db_fetch_array($resultCuenta)) {
                            $GLCode = $myrowCuenta['accountegreso'];
                            $cuentaAbonoProveedor = $myrowCuenta['stockact'];
                        }

                        $SQL1="INSERT INTO  gltrans ( type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        amount,
                        tag,
                        chequeno,
                        userid,
                        dateadded,
                        supplier,
                        descripcion,
                        ln_ue,
                        posted,
                        nu_folio_ue,
                        nu_supptrans_detailid,
                        nu_pagado
                        ) VALUES (
                        '".$Transtype."',
                        '" . $TransNo . "',
                        '" . $fecha1 . "',
                        '" . $PeriodNo . "',
                        '" . $cuentaAbonoProveedor . "',
                        '" . $datosReqi[1]. "',
                        '" . $importe . "',
                        '" . $tagrefClave . "',
                        '" . $ChequeNum . "',
                        '" . $_SESSION['UserID'] . "',
                        now(),
                        '".$registro['supplierno']."',
                        '".$datosReqi[1]."',
                        '".$registro ['ln_ue']."',
                        '1',
                        '".$folioPolizaUe."',
                        '".$registro['detailid']."',
                        '1'
                        )";
                        $ErrMsg = _('no se pudo insertar la transacion');
                        $DbgMsg = _('no se pudo  insertar la transacion SQL');
                        // Se comenta para que la poliza se realice al hacer el pago
                        $result = DB_query($SQL1, $db);

                        $SQL2="INSERT INTO gltrans ( type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        amount,
                        tag,
                        chequeno,
                        userid,
                        dateadded,
                        supplier,
                        cuentabanco,
                        descripcion,
                        ln_ue,
                        posted,
                        nu_folio_ue,
                        nu_supptrans_detailid,
                        nu_pagado
                        ) VALUES (
                        '".$Transtype."',
                        '" . $TransNo . "',
                        '" . $fecha1 . "',
                        '" . $PeriodNo . "',
                        '" . $GLCode . "',
                        '" . $datosReqi[1] . "',
                        '" . -1*$importe . "',
                        '" . $tagrefClave . "',
                        '" . $ChequeNum . "',
                        '" . $_SESSION['UserID'] . "',
                        now(),
                        '".$registro['supplierno']."',
                        '"."1"."',
                        '".$datosReqi[1]."',
                        '".$registro ['ln_ue']."',
                        '1',
                        '".$folioPolizaUe."',
                        '".$registro['detailid']."',
                        '1'
                        )";
                        $ErrMsg = _('no se pudo insertar la transacion');
                        $DbgMsg = _('no se pudo  insertar la transacion SQL');
                        // Se comenta para que la poliza se realice al hacer el pago
                        $result = DB_query($SQL2, $db);
                        //}
                        //fin contables
                    }
                }// fin for para cada movimiento
                //print_r($datosFacturaAut);
                $contenido ="Se unificaron y autorizaron los pagos correctamente";
                $result = true;
            } else {
                $contenido =$validacion[0];
                $TransResult = true;
            }
        } catch (Exception $excepcion) {
            $ErrMsg .= $excepcion->getMessage();
        }
        break;

    case 'datosFactura':
        $datosFact= array();
        $idfactura=$_POST['idfactura'];
        //query arturo para obtener datos
        $SQL="  SELECT
        supptrans.id as id,
        supptrans.trandate as fecha,
        tags.tagdescription,
        supptrans.type as tipoDoc,
        /*supptrans.transtext as invtext, */
        supptrans.hold as status,
        supptrans.transno,
        supptrans.suppreference,
        supptrans.ovamount,
        supptrans.ovgst,
        supptrans.alloc,
        supptrans.supplierno,
        supptrans.tagref,
        supptrans.rate,
        supptrans.diffonexch,
        systypescat.typename
        FROM  tags INNER JOIN supptrans on supptrans.tagref=tags.tagref
        INNER JOIN systypescat on systypescat.typeid=supptrans.type
        , sec_unegsxuser, suppliers, legalbusinessunit 
        WHERE supptrans.id='{$idfactura}'
        and supptrans.supplierno = suppliers.supplierid
        and sec_unegsxuser.tagref = tags.tagref
        and sec_unegsxuser.userid = '{$_SESSION ['UserID']}'
        and legalbusinessunit.legalid = tags.legalid";
        /*supptrans.type IN('22') */
        /*AND supptrans.tagref = tags.tagref */
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        while ($myrow = DB_fetch_array($TransResult)) {
            $datosFact[] = array('ovamount' =>$myrow['ovamount'],'ovgst' =>$myrow['ovgst'],'alloc' =>$myrow['alloc'],
            'status' =>$myrow['status'],'supplierno' =>$myrow['supplierno'],'tagref' =>$myrow['tagref'],'transno' =>$myrow['transno'],'rate' =>$myrow['rate'],'diffonexch' =>$myrow['diffonexch'],'suppreference' =>$myrow['suppreference']);
        }

        $contenido = array('DatosFactura'=>$datosFact);
        $result = true;

        break;

    case 'autorizar':
      $validacion=array();
      $banco=array();
      $descrip='';
      $ids=$_POST['id'];
      $facturasAutorizar='';
      $tipopago=$_POST['tipoPago'];
      $banco= $_POST['banco'];
      $NumeroCheque=array();
      $lnUe='';
      $ctaxtipoproveedor='';
      $estatus=$_POST['sn'];
      $transno_act=$_POST['id'];
      $folios=$_POST['folios'];
      $validacion=fnValidarMatrizPagado($db,$ids);
      $claveRastreo = $_POST['claveRastreo'];

      // Validar estatus del registro para realizar actualizacion
      for ($x=0; $x<count($ids); $x++) {
          $SQL = "SELECT hold FROM supptrans WHERE id = '".$ids[$x]."'";
          $SQL = "SELECT supptrans.hold, supptransdetails.clavepresupuestal
          FROM supptrans
          JOIN supptransdetails ON supptrans.id = supptransdetails.supptransid
          WHERE supptrans.id = '".$ids[$x]."'";
          $ErrMsg = "No se obtuvo el estatus para validación";
          $TransResult = DB_query($SQL, $db);
          $mensajeEstatus = 0;
          $infoClaves = array();
          while ($myrow = DB_fetch_array($TransResult)) {
              if (($myrow['hold'] != '1' && $myrow['hold'] != '-9') && $mensajeEstatus == 0) {
                  // No se programado
                  $validacion[0] = $validacion[0] . '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Un pago seleccionado se encuentra en estatus '.fnEstado($myrow['hold'], 1).', no puede ser autorizado</p>';
                  $validacion[1] = false;
                  $mensajeEstatus = 1;
              }

              $infoClaves[] = array(
                  'accountcode' => $myrow ['clavepresupuestal']
              );
          }

          $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
          if (!$respuesta['result']) {
              $validacion[1] = false;
              $validacion[0] .= $respuesta['mensaje'];
          }
      }

      if (!empty($claveRastreo)) {
          // Validar clave de rastreo
          $SQL = "SELECT txt_clave_rastreo FROM supptrans WHERE txt_clave_rastreo = '".$claveRastreo."'";
          $ErrMsg = "No se pudo validar clave de rastreo";
          $TransResult = DB_query($SQL, $db);
          if (DB_num_rows($TransResult) > 0) {
              $validacion[0] .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ya existe la Clave de Rastreo '.$claveRastreo.'</p>';
              $validacion[1] = false;
          }
      }

      if($validacion[1]==true){
        for ($x=0; $x<count($ids); $x++) {
          //creando  cheuques  
          $datosRequi=array();
          // for ($x=0; $x<count($ids); $x++) {
          //     $facturasAutorizar.="'".$ids[$x]."',";
          // }
          //actualizo estado
          //$facturasAutorizar=substr($facturasAutorizar, 0, -1);
          // se quito por el estatus que se manda
          // 
          $SQL="UPDATE supptrans set hold='2', txt_clave_rastreo = '".$claveRastreo."' WHERE id = '".$ids[$x]."'";
          $ErrMsg = "No se autorizo factura";
          $TransResult = DB_query($SQL, $db);

          // insertar a tipo de pago  cheque o transferencia
          if($estatus[$x]!='-9'){ // diferente  de pago autorizado cancelado por reposicion
            $SQL="  SELECT
            supptrans.id as id,
            supptrans.trandate as fecha,
            tags.tagdescription,
            supptrans.type as tipoDoc,
            /*supptrans.transtext as invtext, */
            supptrans.hold as status,
            supptrans.transno,
            supptrans.suppreference,
            supptrans.ovamount,
            supptrans.ovgst,
            supptrans.alloc,
            supptrans.supplierno,
            supptrans.tagref,
            supptrans.rate,
            supptrans.diffonexch,
            supptrans.ln_ue,
            supptrans.ref1,
            supptrans.ref2,
            systypescat.typename
            FROM  tags INNER JOIN supptrans on supptrans.tagref=tags.tagref
            INNER JOIN systypescat on systypescat.typeid=supptrans.type
            , sec_unegsxuser, suppliers, legalbusinessunit 
            WHERE supptrans.id ='". $ids[$x]."'
            and supptrans.supplierno = suppliers.supplierid
            and sec_unegsxuser.tagref = tags.tagref
            and sec_unegsxuser.userid = '{$_SESSION ['UserID']}'
            and legalbusinessunit.legalid = tags.legalid";
            $ErrMsg = "fallo en obtener  datos de factura";
            $TransResult = DB_query($SQL, $db);
            $datosFacturaAut =array();
            while ($myrow = DB_fetch_array($TransResult)) {
              switch ($tipopago) {
                case '01':
                  //orden de pago sn definir
                break;

                case '02':
                  $rate='';
                  $TransNo='';
                  $bankaccount='';
                  $tagref='';
                  $saldocheque2='';
                  $rate='';
                  //$TransNo,$Transtype ,$bankaccount,$narrative,$ExRate ,$FunctionalExRate,$fechacheque,$Tipopago,$saldocheque2, $moneda,$tagref,$Beneficiario

                  $saldo= ($myrow['ovamount'] + ($myrow['ovgst'] - $myrow['alloc']));
                  $NumeroCheque[]=  fnGenerarChequeBanco($db, $myrow['transno'], '281', $banco[$x], 'narrativa', $myrow['rate'], $myrow['rate'], $myrow['fecha'], 'Cheque', $myrow['ovamount'], "MXN", $myrow['tagref'], 'beneficiario', $myrow['ln_ue'], '', $myrow['tipoDoc']);
                break;

                case '03':
                  $saldo= ($myrow['ovamount'] + ($myrow['ovgst'] - $myrow['alloc']));
                  $NumeroCheque[]=  fnGenerarChequeBanco($db, $myrow['transno'], '281', $banco[$x], 'narrativa', $myrow['rate'], $myrow['rate'], $myrow['fecha'], 'Transferencia', $myrow['ovamount'], "MXN", $myrow['tagref'], 'beneficiario', $myrow['ln_ue'], '', $myrow['tipoDoc']);
                break;
              }// fin swicth
            }// fin while
          }else{
              $SQL = "SELECT alloc FROM supptrans WHERE id = '".$ids[$x]."'";
              $TransResult = DB_query($SQL, $db);
              $myrow = DB_fetch_array($TransResult);
              if ($myrow['alloc'] != '0') {
                  // Pagar en automatico
                  $SQL="UPDATE supptrans set hold='3', txt_clave_rastreo = '".$claveRastreo."' WHERE id = '".$ids[$x]."'";
                  $ErrMsg = "No se autorizo factura";
                  $TransResult = DB_query($SQL, $db);
              }
              $NumeroCheque[]=$folios[$x];
          }// fin si
        }//fin for

        //fin creando  cheuques  

        $tagref=$_POST['ur'];
        $importe=0;
        $ratefactura="1";
        $var=$_POST['fechas'];

        $Transtype='281';
        $transnoCheque='';
        //movimientos  ejercido
        for ($x1=0; $x1<count($transno_act); $x1++) {
            $fecha=date("d-m-Y", strtotime($var[$x1]));
            $fecha1=date("Y-m-d", strtotime($var[$x1]));

            $datosFecha=explode('-', $fecha);
            $diaP=$datosFecha[0]; //date('d');
            $mesP= $datosFecha[1];//date('m');
            $anioP= $datosFecha[2]; //date('Y');

            $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);

            $TransNo = GetNextTransNo($Transtype, $db);

            // Folio de la poliza por unidad ejecutora
            $folioPolizaUe = 0; // fnObtenerFolioUeGeneral($db, $tagref[$x1], $registro ['ln_ue']);

            $consulta= "SELECT supptransdetails.stockid AS stk,supptrans.id, 
            supptrans.transno, 
            supptrans.type,
            supptrans.ovamount,(price*qty) AS precio2,
            ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
            JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
            WHERE stockid=stk),2) 
            AS impuesto,
            supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
            supptrans.ln_ue,
            supptrans.supplierno,
            supptransdetails.ln_clave_iden,
            tb_cat_partidaspresupuestales_partidagenerica.pargcalculado as categoryid,
            supptrans.supplierno,
            supptransdetails.stockid as itemcode,
            supptransdetails.period,
            supptransdetails.nu_id_compromiso,
            supptransdetails.nu_id_devengado,
            supptransdetails.nu_idret,
            supptransdetails.detailid,
            chartdetailsbudgetbytag.tagref as tagrefClave
            FROM supptrans
            INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
            JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = supptransdetails.clavepresupuestal
            JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
            JOIN tb_cat_partidaspresupuestales_partidagenerica ON
            tb_cat_partidaspresupuestales_partidagenerica.ccap = tb_cat_partidaspresupuestales_partidaespecifica.ccap
            AND tb_cat_partidaspresupuestales_partidagenerica.ccon = tb_cat_partidaspresupuestales_partidaespecifica.ccon
            AND tb_cat_partidaspresupuestales_partidagenerica.cparg = tb_cat_partidaspresupuestales_partidaespecifica.cparg
            WHERE id = ('".$transno_act[$x1]."')";
            // print_r($consulta);
            $resultado = DB_query($consulta, $db);
            // Array para folios agrupados (UR-UE)
            $infoFolios = array();
            while ($registro = DB_fetch_array($resultado)) {
                $GLCode = "";//   de donde sale la cuenta
                $cuentaAbonoProveedor = ""; //beneficiario
                $sqlWhere = "";
                $lnUe=$registro ['ln_ue'];
                $transnoCheque=$registro ['transno'];
                $tagrefClave = $registro['tagrefClave'];
                $datosReqi= requisicion($registro['transno'], $registro['type'], $db);
                $importe = ( (  ($registro["price"])* ($registro["qty"]))  * (1 + 0)) / $ratefactura;
                //momentaneo
                
                // Ver si existe folio para movimientos
                $folioPolizaUe = 0;
                foreach ($infoFolios as $datosFolios) {
                    // Recorrer para ver si exi
                    if ($datosFolios['tagref'] == $tagrefClave && $datosFolios['ue'] == $registro ['ln_ue']) {
                        // Si existe
                        $folioPolizaUe = $datosFolios['folioPolizaUe'];
                    }
                }
                if ($folioPolizaUe == 0) {
                    // Si no existe folio sacar folio
                    // $transno = GetNextTransNo($type, $db);
                    // Folio de la poliza por unidad ejecutora
                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagrefClave, $registro ['ln_ue'], $Transtype);
                    $infoFolios[] = array(
                        'tagref' => $tagrefClave,
                        'ue' => $registro ['ln_ue'],
                        'type' => $Transtype,
                        'transno' => $TransNo,
                        'folioPolizaUe' => $folioPolizaUe
                    );
                }

                $infoClaves = array();
                $infoClaves[] = array(
                    'accountcode' => $registro ['clavepresupuestal']
                );
                $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                $PeriodNo = $respuesta['periodo'];
                
                if($estatus[$x1]!='-9'){ // si no viene un autotizado por reposicion
                    //      $montopresupuestal= truncateFloat(($montocom ), $digitos);
                    GeneraMovimientoContablePresupuesto($Transtype, "DEVENGADO", "EJERCIDO", $TransNo, $PeriodNo, $importe, $tagrefClave, $fecha1, $registro["clavepresupuestal"], $TransNo, $db, false, '', '',$datosReqi[1], $registro ['ln_ue'], 1, $NumeroCheque[$x1], $folioPolizaUe, $registro["detailid"]);

                    // Log Presupuesto
                    $descriptionLog = "Autorización de pago.";
                    //$tagref[$x1]
                    $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagrefClave, $registro["clavepresupuestal"], $registro['period'], $importe, 260, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Abono

                    $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagrefClave, $registro["clavepresupuestal"], $registro['period'], $importe * -1, 261, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Cargo

                    // $SQL = "SELECT nu_id_devengado FROM tb_pagos 
                    // WHERE nu_type = '".$registro['type']."' AND nu_transno = '".$registro['transno']."'";
                    // $resultDevengado = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
                    // if (DB_num_rows($resultDevengado) > 0) {
                    //   // Es devengado, actualizar registros del devengado para descontar al disponible
                    //   $myrowDevengado = db_fetch_array($resultDevengado);

                    //   $SQL = "UPDATE chartdetailsbudgetlog SET nu_id_devengado = '".$myrowDevengado['nu_id_devengado']."'
                    //   WHERE type = '".$Transtype."' AND transno = '".$TransNo."'
                    //   AND nu_tipo_movimiento = 260";
                    //   $resultDevengado2 = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
                    // }
                }
                //fin momentaneo
                //fin movimientos ejercidos

                // //contables

                if (trim($registro['ln_clave_iden']) != '') {
                  $sqlWhere = " AND ln_clave = '".$registro['ln_clave_iden']."' ";
                }
                $SQL = "SELECT stockact, accountegreso FROM tb_matriz_pagado WHERE categoryid = '".$registro['categoryid']."' AND stockact IN (SELECT accountcode FROM accountxsupplier WHERE supplierid = '".$registro["supplierno"]."') ".$sqlWhere;
                $resultCuenta = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

                while ($myrowCuenta=db_fetch_array($resultCuenta)) {
                  $GLCode = $myrowCuenta['accountegreso'];
                  $cuentaAbonoProveedor = $myrowCuenta['stockact'];
                }

                if (empty($value)) {
                  $value='x';
                }

                $SQL1="INSERT INTO  gltrans ( type,
                typeno,
                trandate,
                periodno,
                account,
                narrative,
                amount,
                tag,
                chequeno,
                userid,
                dateadded,
                supplier,
                descripcion,
                ln_ue,
                posted,
                nu_folio_ue,
                nu_supptrans_detailid,
                nu_pagado
                ) VALUES (
                '".$Transtype."',
                '" . $TransNo . "',
                '" . $fecha1 . "',
                '" . $PeriodNo . "',
                '" . $cuentaAbonoProveedor . "',
                '" . $datosReqi[1]. "',
                '" . $importe . "',
                '" . $tagrefClave . "',
                '" . $NumeroCheque[$x1] . "',
                '" . $_SESSION['UserID'] . "',
                now(),
                '".$registro['supplierno']."',
                '".$datosReqi[1]."',
                '". $lnUe."',
                '1',
                '".$folioPolizaUe."',
                '".$registro['detailid']."',
                '1'
                )";
                $ErrMsg = _('no se pudo insertar la transacion');
                $DbgMsg = _('no se pudo  insertar la transacion SQL');


                // Se comenta para que la poliza se realice al hacer el pago
                $result = DB_query($SQL1, $db);

                $SQL2="INSERT INTO gltrans ( type,
                typeno,
                trandate,
                periodno,
                account,
                narrative,
                amount,
                tag,
                chequeno,
                userid,
                dateadded,
                supplier,
                cuentabanco,
                descripcion,
                ln_ue,
                posted,
                nu_folio_ue,
                nu_supptrans_detailid,
                nu_pagado
                ) VALUES (
                '".$Transtype."',
                '" . $TransNo . "',
                '" . $fecha1 . "',
                '" . $PeriodNo . "',
                '" . $GLCode . "',
                '" . $datosReqi[1] . "',
                '" . -1*$importe . "',
                '" . $tagrefClave . "',
                '" . $NumeroCheque[$x1] . "',
                '" . $_SESSION['UserID'] . "',
                now(),
                '".$registro['supplierno']."',
                '"."1"."',
                '".$datosReqi[1]."',
                '". $lnUe."',
                '1',
                '".$folioPolizaUe."',
                '".$registro['detailid']."',
                '1'
                )";
                $ErrMsg = _('no se pudo insertar la transacion');
                $DbgMsg = _('no se pudo  insertar la transacion SQL');

                // Se comenta para que la poliza se realice al hacer el pago
                $result = DB_query($SQL2, $db);
                //}
                //fin contables
            }// fin while

            $contenido ="Se autorizaron pagos correctamente";
            $result = true;
        }// fin for para cada movimiento
        //print_r($datosFacturaAut);
      }else{ // fin validar matriz  pagado
        $contenido =$validacion[0];
        $TransResult = true;
      }
      break;

    case 'validarMatrizPagado':
      $transno_act=$_POST['id'];
      $mensajeCuentas='';
      $datos=fnValidarMatrizPagado($db,$transno_act);
      $contenido =$datos;
      $result = true;
      break;

    case 'checarRadicado':
        $datos=array();
        $mensajeNo='';
        $mensajeInfo='';
        $mensaje='';
        if (isset($_POST['requis'])) {
            $requis=$_POST['requis'];
            $fechas=$_POST['fechas'];
            $disponibleRadicado='';
            $noAlcanza=array();
            $detalleNoAlcanza=array();
          // print_r($requis);
            for ($d=0; $d<count($requis); $d++) { // por cada requisiscion
                $aux= explode("-", $fechas[$d]);
                $mensajeInfo.='La requisición <b>'.$requis[$d].'</b><br>';
                $PeriodNo = GetPeriod($aux[2].'/'.$aux[1].'/'.$aux[0], $db);
           
                $datos= fnChecarRequis($requis[$d], $db);
                $claves=$datos[0];
                $montosRequeridos=$datos[1];
                $mes=fnGetMes($aux[1]); // La funcion esta en el SQLCommonFunctions
            
                for ($a=0; $a<count($claves); $a++) { //por cada presupuestal de  una requisicion
                    $mensajeInfo.='para la clave presupuestal <u>'.$claves[$a]."</u><br>";
                    $mensajeInfo.='<li> el <b>monto requerido es '. $montosRequeridos[$a]."</b>";  // no

                    $mensajeNo='en la clave presupuestal <u>'.$claves[$a]."</u><br>".'<li> el <b>monto requerido es '. $montosRequeridos[$a]."</b>";  // no
                    $radicado = fnInfoPresupuestoRadicado($db, $claves[$a], $PeriodNo);
                   // print_r($radicado);
                    $disponibleRadicado= ( $radicado[0][$mes]);
                    $mensajeInfo.=' y su <b>radicado es '.$disponibleRadicado.'</b></li>';  //no
                    $mensajeNo.=' y su <b>radicado es '.$disponibleRadicado.'</b></li>';  //no
                    if ($montosRequeridos[$a]>$disponibleRadicado) {
                        $noAlcanza[]=$requis[$d];
                        $detalleNoAlcanza[]= $mensajeNo;
                    }
                }
                //$mensaje.='<br>';
            }// fin  por cada requisiscion
          //print($mensaje);
            for ($j=0; $j<count($requis); $j++) {
                $existe=fnChecarExistencia($noAlcanza, $requis[$j]);
                if ($existe[0]>=1) {
                    $mensaje.="No hay radicado para la <b>requesición:".$requis[$j]."</b> ya que <br> ";
                    $mensaje.=$detalleNoAlcanza[$existe[1]];
                   // $mensaj.=
                }
            }
        
           // print_r($mensajeNo);

           // print("no alcanza  en ------------");
           // print_r($noAlcanza);
        }
   
        $contenido = array('datosRadicado' => $mensajeInfo,'datosRadicadoNoAlcanza' => $mensaje);
        $result = true;

        break;

    case 'pago2':
      $datos=array();
      $datosU=array();
      $chequesN=array();
      $ids=$_POST['ids'];
      $cheques=$_POST['folios'];
      $bancosOrigen=$_POST['bancosOrigen'];

      $fechas=$_POST['fechas'];

      $validacion = 1;
      $mensajeValidacion = '';

      // Validar estatus del registro para realizar actualizacion
      for ($x=0; $x<count($ids); $x++) {
          $SQL = "SELECT hold FROM supptrans WHERE id = '".$ids[$x]."'";
          $SQL = "SELECT supptrans.hold, supptransdetails.clavepresupuestal
          FROM supptrans
          JOIN supptransdetails ON supptrans.id = supptransdetails.supptransid
          WHERE supptrans.id = '".$ids[$x]."'";
          $ErrMsg = "No se obtuvo el estatus para validación";
          $TransResult = DB_query($SQL, $db);
          $mensajeEstatus = 0;
          $infoClaves = array();
          while ($myrow = DB_fetch_array($TransResult)) {
              if ($myrow['hold'] != '2' && $mensajeEstatus == 0) {
                  // No se programado
                  $mensajeValidacion .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Un pago seleccionado se encuentra en estatus '.fnEstado($myrow['hold'], 1).', no puede ser pagado</p>';
                  $validacion = 0;
                  $mensajeEstatus = 1;
              }

              $infoClaves[] = array(
                  'accountcode' => $myrow ['clavepresupuestal']
              );
          }

          $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
          if (!$respuesta['result']) {
              $validacion = 0;
              $mensajeValidacion .= $respuesta['mensaje'];
          }
      }

      if($validacion == 1){
          $datos=fnUnificadoOnormal($cheques, $bancosOrigen, $db); //determino si es el pago del cheque es  normal o si el numero del cheque ha sido unificado
          //print_r($datos[0]);
          if (count($datos[0])>0) {
              $ids= fnGetIds($datos[0], $db);
              // echo "\n 111111 \n";
              // print_r($ids);
              // exit();
              fnPagoNormal($ids[0], $db, $ids[1], $ids[2], $fechas);
          }
          if (count($datos[1])>0) {
              // echo "\n datos \n";
              // print_r($datos);
              $chequesU=$datos[1];
              // echo "\n chequesU \n";
              // print_r($chequesU);
              for ($a=0; $a<count($chequesU); $a++) {
                  $datosU= fnGetIds($chequesU[$a], $db);
                  // echo "\n entra 11111 \n";
                  // echo "\n datosU \n";
                  // print_r($datosU);
                  // exit();
                  fnPagoUnificado($datosU[0], $chequesU[$a], $datosU[2], $db);
              }
          }

          $contenido=$liga;
          $TransResult =true;
      }else{
          $contenido =$mensajeValidacion;
          $TransResult = false;
      }
      break;

    case 'detalleCancelacionCR':
        if (isset($_POST['transno']) && isset($_POST['ncheque'])) {
            $datos=array();
            $SQL="SELECT ln_tipo_pago as tipoPago,ln_justificacion as justificacion,dtm_fecharegistro as fecha FROM tb_cheques_cr  WHERE nu_transno='".$_POST['transno']."' and ln_chequeno='".$_POST['ncheque']."'";
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $datos[]= array('tipoPago'=>$myrow['tipoPago'],
                                'justificacion'=>$myrow['justificacion'],
                                'fecha'=>$myrow['fecha']);
            }
    
            $contenido = array('datos' => $datos);
            $result = true;
        }
        break;
    case 'buscarChequesCR':
        $infoCheque=array();
        $visibleCheques= Havepermission($_SESSION ['UserID'], 2339, $db);
        if ($visibleCheques==1) {
            $datos=array();
            $ChequeNum='';
            $banco='';

            $anioActual=date('Y');
            $mesActual=date('m');
            $dateDesde='';
            $dateHasta='';
            if (!empty($_POST['desdeCancelar'])) {
                $dateDesde= date("Y-m-d", strtotime($_POST['desdeCancelar']));
            } else {
                $dateDesde=0;
            }
    
            if (!empty($_POST['hastaCancelar'])) {
                $dateHasta= date("Y-m-d", strtotime($_POST['hastaCancelar']));
            } else {
                $dateHasta=0;
            }
       
            if (isset($_POST['cheque']) && !empty($_POST['cheque']) && $_POST['cheque']!='') {
                $ChequeNum= $_POST['cheque'];
                $ChequeNum=" AND chequeno='".$ChequeNum."'";
            } else {
                $ChequeNum='';
            }

            if (isset($_POST['banco']) && !empty($_POST['banco']) && $_POST['banco']!='') {
                $banco= $_POST['banco'];
                $banco=" AND banktrans.bankact='".$banco."'";
            } else {
                  $banco='';
            }
        
            if ($dateDesde!=0 && $dateHasta!=0) {
                $SQL .=" and supptrans.trandate >=  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')
              and supptrans.trandate <= STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d') ";
            } else if ($dateDesde!=0 && $dateHasta==0) {
                $SQL .=" and supptrans.trandate >=  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')";
            } else if ($dateDesde==0 && $dateHasta!=0) {
                $SQL .=" and supptrans.trandate <=  STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d')";
            }
            $SQL="SELECT 
            DISTINCT 
            supptrans.trandate, 
            supptrans.tagref, supptransdetails.requisitionno AS requi,supptrans.hold as estatus, banktrans.chequeno,banktrans.bankact,(SELECT bankaccountname FROM bankaccounts where bankaccounts.accountcode=banktrans.bankact) as banco,banktrans.banktranstype,supptransid,supptrans.transno,supptrans.suppreference,supptrans.ovamount,
            supptransdetails.comments as obs,  
            IF( banktrans.ref LIKE '%CANCELADO%','Cancelado','Normal') AS estatusCheque,
            supptrans.hold,
            banktrans.type,
            banktrans.nu_type
            FROM  banktrans 
            INNER JOIN supptrans on banktrans.transno  =supptrans.transno AND banktrans.nu_type = supptrans.type
            INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
            WHERE banktrans.transdate between  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')
            AND STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d')
            AND supptrans.hold IN(2,3,6,7)
            AND banktrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
            ".$ChequeNum.$banco;
            // exit();
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
      
            while ($myrow = DB_fetch_array($TransResult)) {
             //$infoCheque= fnHistorialCancelado($myrow['transno'],$db);
                $historial='';

              //if(count($infoCheque)>0){
                    $historial="<span style=\"color:blue;\"> <u>"."Historial"."</u></span>";
                   // }else{
                   //  $historial="";
                   // }
                $esatus=0;
                if($myrow['estatusCheque']=='Cancelado'){
                 $esatus= fnEstado('6');
                }else{
                   $esatus= fnEstado($myrow['estatus']);
                  }
                $datos[] = array('id1'=>false,
                      
                   'fecha'=>$myrow['trandate'],//.' '.$myrow['tagdescription'],
                   'tag'=>$myrow['tagref'],
                   'requi'=>$myrow['requi'],
                   'estatus'=>$esatus,
                   'chequeno'=>$myrow['chequeno'],
                   'tipoPago'=>$myrow['banktranstype'],
                   'banco'=>$myrow['banco'],
                   'estatusCheque' =>"<span style=\"color:blue;\"> <a href='#'>".$myrow['estatusCheque']."</a></span>",
                   //'estatusCheque'=>$myrow['estatusCheque'],
                   // if(count($info)>0){'historial'=>"<span style=\"color:blue;\"> <u>"."Historial"."</u></span>"
                   // }else{
                   //  'historial'=>""
                   // },
                   'historial'=>$historial,
                   'factura'=>$myrow['suppreference'],
                   'monto'=>$myrow['ovamount'],
                   'observaciones'=>$myrow['obs'],
                   'id'=>$myrow['supptransid'],
                   'transno'=>$myrow['transno'],
                   'origen'=>$myrow['bankact'],
                   'status2'=>$myrow['hold'],
                   'type'=>$myrow['type'],
                   'nu_type'=>$myrow['nu_type']
                );
            }

            $funcion = 244;
            $nombre=traeNombreFuncionGeneral($funcion, $db);
            $nombre=str_replace(" ", "_", $nombre);
            $nombreExcel = $nombre.'_'.date('dmY');
       
            $contenido = array('datos' => $datos,'nombreExcel' => $nombreExcel);
            $result = true;
        }
        break;

    case 'cancelarCheque':
        $contenido='';
        $flag=false;
        if ((isset($_POST['ChequeNum'])) && !empty($_POST['ChequeNum'])) {
            $ChequeNum=$_POST['ChequeNum'];
            //fnCancelarCheque($type='20',$db);
            $flag=  fnCancelarCheque('20', $_POST['tipoCancel'], $db);
            if($flag==true){
                $contenido = 'Cheque cancelado correctamente.';
            }else{
                $contenido = 'Verifique que no haya seleccionado cheques ya cancelados.';
            }
        }
        $result = true;
        break;

    case 'nuevoFolio':
        $type=20;
        if (isset($_POST['ChequeNum'])) {
            $cheques=$_POST['ChequeNum'];
            $origen=$_POST['origen'];

            $transnoFolios=$_POST['transno'];
            $ids=$_POST['ids'];
            $types=$_POST['type'];
            $nu_types=$_POST['nu_type'];

            $cadenaCheques='';
            $cadenaBancosOrigen='';
            $montoAnt='';
            $tipo='';
            $ChequeNum=0;
            $contenido='';
            for ($a=0; $a<count($cheques); $a++) {
                $cadenaCheques.="'".$cheques[$a]."',";
                $cadenaBancosOrigen.="'".$origen[$a]."',";
                $ChequeNum = GetNextChequeNo($origen[$a], $db);

                $SQL1="SELECT DISTINCT supptrans.ovamount,  supptransdetails.comments as obs, 
                banktrans.transno, 
                banktrans.type,
                banktrans.nu_type,
                banktrans.ln_ue,supptrans.rate,banktrans.transdate,banktrans.tagref,banktrans.beneficiary,
                tb_cheques_cr.nu_tipo_cr,    
                IF( banktrans.ref LIKE '%CANCELADO%','Cancelado','Normal') AS estatusCheque
                FROM  banktrans 
                INNER JOIN tb_cheques_cr on banktrans.transno=tb_cheques_cr.nu_transno
                INNER JOIN supptrans on banktrans.transno  =supptrans.transno 
                INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
                WHERE 
                banktrans.chequeno = '".$cheques[$a]."' 
                AND banktrans.bankact = '".$origen[$a]."' 
                and banktrans.type = '".$types[$a]."'
                and banktrans.transno = '".$transnoFolios[$a]."'
                and banktrans.nu_type = '".$nu_types[$a]."' ";
                // echo "\n SQL1: ".$SQL1;
                $resultado = DB_query($SQL1, $db);
                while ($myrow = DB_fetch_array($resultado)) {
                    $tipo=$myrow['nu_tipo_cr']; // tipo de cancelacion si por reposicion de cheque
                    if($tipo==1){
                        $texoAnterior='';
                        $consulta="SELECT * FROM supptrans  WHERE type = '".$myrow['nu_type']."' AND transno='".$myrow['transno']."'";
                        // echo "\n consulta: ".$consulta;
                        $resultado2 = DB_query($consulta, $db);
                        $valores='';
                        while ($fila = DB_fetch_array($resultado2)) {
                            $TransNo2 = GetNextTransNo($fila['type'], $db);

                            if (empty($fila['id_clabe'])) {
                                // Si va vacio poner 0
                                $fila['id_clabe'] = 0;
                            }

                            $texoAnterior=$fila['transtext'];
                            $valores.="(
                            ".$TransNo2. ", 
                            '".$fila['tagref']. "',
                            '".$fila['type']. "',
                            '".$fila['supplierno']. "',
                            '".$fila['suppreference']. "',
                            '".$fila['trandate']. "',
                            '".$fila['origtrandate']. "',
                            '".$fila['duedate']. "',
                            '".$fila['promisedate']. "',
                            '".$fila['settled']. "',
                            '".$fila['rate']. "',
                            '".$fila['ovamount']. "',
                            '".$fila['ovgst']. "',
                            '".$fila['diffonexch']. "',
                            '".$fila['alloc']. "',
                            '"."Cancelación del cheque:".$cheques[$a]." ".$texoAnterior."',
                            '"."7". "',
                            '".$fila['currcode']. "',
                            '".$fila['order_']. "',
                            '".$fila['lasttrandate']. "',
                            "."0". ",
                            '".$fila['priority']. "',
                            '".$fila['activo']. "',
                            '".$fila['reffiscal']. "',
                            '".$fila['ln_ue']. "',
                            '".$fila['id']. "',
                            '".$myrow['transno']."-".$cheques[$a]. "',
                            '".$fila['id_clabe']. "'
                            )";
                        }

                        $sql3="INSERT INTO supptrans (transno,tagref,type,supplierno,suppreference,trandate,origtrandate,duedate,promisedate,settled,rate,ovamount,ovgst,diffonexch,alloc,transtext,hold,currcode,order_,lasttrandate,sent,priority,activo,reffiscal,ln_ue,ref1,ref2, id_clabe) VALUES ".$valores;
                        // echo "\n sql3: ".$sql3;
                        $resultado = DB_query($sql3, $db); 

                        fnGenerarChequeBanco($db, $myrow['transno'], '281', $origen[$a], 'narrativa', $myrow['rate'], $myrow['rate'], $myrow['transdate'], 'Cheque', $myrow['ovamount'], "MXN", $myrow['tagref'],$myrow['beneficiary'], $myrow['ln_ue'], $ChequeNum, $myrow['nu_type']);

                        $SQL = "UPDATE tb_cheques_cr SET nu_tipo_cr='"."3"."' WHERE ln_chequeno ='".$cheques[$a]."'";
                        // echo "\n SQL: ".$SQL;
                        $r = DB_query($SQL, $db);

                        $SQL = "UPDATE supptrans SET supptrans.hold='"."-8"."', supptrans.transtext='Reposición del cheque:".$cheques[$a]." ".$texoAnterior."' WHERE supptrans.transno ='".$myrow['transno']."'";
                        // echo "\n SQL: ".$SQL;
                        $r = DB_query($SQL, $db);
                    }
                }
            }

            if($tipo=='1'){
                if($ChequeNum!=0){
                    $contenido ='Cheque generado con folio:'.$ChequeNum;
                }
            }elseif ($tipo=='3') {
                $contenido ='El cheque ya fue reimpreso.';
            } else{
                $contenido ='Solo se puede generar folio nuevo de una de cancelación por reposición.';
            }

            $result = true;
        }//if
        break;

    default:
        // codigo futuro...
        break;
}

function fnPagoNormal($ids, $db, $NumeroCheque, $bancosOrigen, $fechas)
{
    $permisomostrar=1;
    $ueDecrip='';
    $ChequeNum='';
    if (isset($_SESSION['TruncarDigitos'])) {
        $digitos=$_SESSION['TruncarDigitos'];
    } else {
        $digitos=4;
    }

    if (isset($_POST['FechaPago'])) {
        $fechapago = $_POST['FechaPago'];
    } elseif (isset($_GET['FechaPago'])) {
        $fechapago = $_GET['FechaPago'];
    } else {
        $fechapago = date("Y-m-d");//
    }


    if (strpos($_SESSION['DefaultDateFormat'], '/')) {
        $flag = "/";
    } elseif (strpos($_SESSION['DefaultDateFormat'], '-')) {
        $flag = "-";
    } elseif (strpos($_SESSION['DefaultDateFormat'], '.')) {
        $flag = ".";
    }
    $diafecha = substr($_POST['FechaPago'], 8, 2);
    $mesfecha = substr($_POST['FechaPago'], 5, 2);
    $aniofecha = substr($_POST['FechaPago'], 0, 4);
    $trandatef = $diafecha.$flag.$mesfecha.$flag.$aniofecha;

    $diaP= date('d');
    $mesP= date('m');
    $anioP= date('Y');


    //$transnos[] = $TransNo;

    //if ($_POST['tipocambio'] == 'No') {
    $moneda = 'MXN';
    /*}elseif($_POST['tipocambio'] == 'Yes') {
    $moneda = 'USD';
    }; */

    //$fechacheque =$anioP."-".$mesP."-".$diaP;
    //$bankaccount = $_POST['BankAccount'];
    //$ChequeNum = $_POST['ChequeNum'];
    //$Tipopago = $_POST['Tipopago'];

    /*Get the bank account currency and set that too */
    /*$ErrMsg = _('No pude obtener la moneda de la cuenta del banco seleccionada');
    $result = DB_query("SELECT currcode FROM bankaccounts WHERE accountcode = '" . $bankaccount . "'", $db, $ErrMsg);
    $myrow = DB_fetch_row($result);
    $monedabanco = $myrow[0]; */
    //echo "<br>monedabanco: " . $monedabanco;

    /*********FUNCIONES PARA OBTENER RATE****/
    /*Get the exchange rate between the functional currency and the payment currency*/
    $result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $moneda . "'", $db);
    //echo "<br>" . "SELECT rate FROM currencies WHERE currabrev='" . $moneda . "'";
    $myrow = DB_fetch_row($result);
    $tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency

    $_POST['FunctionalExRate'] = 1;

    if ($moneda == $monedabanco) {
        $_POST['ExRate']=1;
        $SuggestedExRate=1;
    }
    if ($monedabanco==$_SESSION['CompanyRecord']['currencydefault']) {
        $_POST['FunctionalExRate']=1;
        $SuggestedFunctionalExRate =1;
        $SuggestedExRate = $tableExRate;
    } else {
        /*Get suggested FunctionalExRate */
        //echo "<br>1.- " . "SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'";
        $result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'", $db);
        $myrow = DB_fetch_row($result);
        $SuggestedFunctionalExRate = $myrow[0];

        /*Get the exchange rate between the functional currency and the payment currency*/
        //echo "<br>2.- " . "select rate FROM currencies WHERE currabrev='" . $moneda . "'";
        $result = DB_query("select rate FROM currencies WHERE currabrev='" . $moneda . "'", $db);
        $myrow = DB_fetch_row($result);
        $tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
        /*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
        $SuggestedExRate = $tableExRate/1; //$SuggestedFunctionalExRate;
    }

    if ($monedabanco != $moneda and isset($monedabanco)) {
        if ($_POST['ExRate']==1 and isset($SuggestedExRate)) {
            $_POST['ExRate'] = $SuggestedExRate;
        }
    }

    if ($monedabanco != $_SESSION['CompanyRecord']['currencydefault'] and isset($monedabanco)) {
        if ($_POST['FunctionalExRate']==1 and isset($SuggestedFunctionalExRate)) {
            $_POST['FunctionalExRate'] = $SuggestedFunctionalExRate;
        }
    }


    $inisupplierid = "";
    $initransno = "";
    $prevdiffonexch = 0;
    $InputError = 0;
    //echo "<br>" . count($_POST['selMovimiento']); tagsxbankaccounts.tagref
    /*$sqlaccoutnt = "SELECT tagsxbankaccounts.tagref
    FROM bankaccounts, chartmaster, tagsxbankaccounts
    WHERE bankaccounts.accountcode=chartmaster.accountcode 
    AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
    AND bankaccounts.accountcode = '".$_POST['BankAccount']."'";
    $resultaccount = DB_query($sqlaccoutnt, $db);
    $tagrefcheque = array();
    while($myrowaccoutn = DB_fetch_array($resultaccount)){
    $tagrefcheque[] = $myrowaccoutn['tagref'];
    } */
    $conte=0;
    //$ids=$_POST['ids'];
    $totaldocumentos = count($ids); //$totaldocumentos = count($_POST['selMovimiento']);
    
    $idFacturaNueva='';
    $type=22;
    $TransNo='';

    /* if(isset($_POST['unificar'])){
    $v=$_POST['unficar'];
    if($v==true){
    $TransNo = GetNextTransNo($type, $db);
    }
    } */

    for ($i=0; $i <$totaldocumentos; $i++) {
        //if( $TransNo==''){
        $fecha =date("d-m-Y", strtotime($fechas[$i]));

        $fechacheque=date("Y-m-d", strtotime($fecha));
        //$ratefactura="1";
        $datosFecha=explode('-', $fecha);
        $diaP=$datosFecha[0]; //date('d');
        $mesP= $datosFecha[1];//date('m');
        $anioP= $datosFecha[2]; //date('Y');

        $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);
    
        $ChequeNum=$NumeroCheque[$i];
        $TransNo = GetNextTransNo($type, $db);
    
        // }
        // $saldonotacargo=0;
        /*if($unificarPD ){
        $type=32;
        if($totaldocumentos==$i)
        {
        $type=34;
        $facturasafectadas='';
        $saldosum='';
        for ($j=0;$j <= $totaldocumentos-1; $j++) {
        $saldosum +=$_POST['saldo'][$_POST['selMovimiento'][$j]];
        $facturasafectadas .= $_POST['selMovimiento'][$j].'-';
        $umovto = $_POST['selMovimiento'][$i];
        $facturasafectadastypetransno.=$_POST['tagref'][$umovto].'-20 |';
        }
        $saldonotacargo=$saldosum;
        $TransNo = GetNextTransNo($type, $db);

        $SQL="SELECT chartsupplierstype.gl_unificarpagos as cuenta,suppliers.suppname  as nombre
        FROM suppliers
        INNER JOIN supplierstype
        ON suppliers.typeid = supplierstype.typeid
        INNER JOIN chartsupplierstype
        ON chartsupplierstype.typedebtorid = supplierstype.typeid
        WHERE suppliers.supplierid ='".$_POST['UnificarPagoselect']."'";
        $resultadoe=  DB_query($SQL, $db);
        $rowe = DB_fetch_array($resultadoe);

        $tipoproveedor = ExtractTypeSupplier($_POST['UnificarPagoselect'],$db);
        $ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
        $id= GenerarNotadeCargo($_POST['UnificarPagoselect'], 'Factura Proveedores Multiples', 'MXN', $saldosum, $rowe['cuenta'], $type, $_POST['tagref2'],4,$TransNo,$PeriodNo,$ctaxtipoproveedor,$db,$facturasafectadas);
        */
        $SQL = "SELECT 
        transno,
        tagref,
        type,
        supplierno,
        suppreference,
        trandate,
        origtrandate,
        duedate,
        promisedate,
        settled,
        rate,
        ovamount,
        ovgst,
        diffonexch,
        alloc,
        transtext,
        hold,
        id,
        folio,
        ref1,
        ref2,
        currcode,
        sent,
        activo,
        originalinvoice,
        u_typeoperation,
        retencionIVA,
        retencionISR,
        retencionCedular, 
        retencionFletes,
        retencionComisiones,
        retencionArrendamiento, 
        retencionIVAArrendamiento,
        reffiscal,
        ln_ue
        FROM supptrans 
        WHERE id = '".$ids[$i]."'";
        $result=  DB_query($SQL, $db);
        $row= DB_fetch_array($result);
        //$_POST['selMovimiento'][$i]=$row['id'];
        $_POST['selMovimiento']=$row['id'];
        $_POST['tagref']=$row['tagref'];
        $_POST['status']=$row['hold'];
        $_POST['saldo']=$saldosum;
        //$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
        $_POST['supplierid']=$row['supplierno'];
        $_POST['rate']=$row['rate'];
        $_POST['idfactura']=$row['id'];
        $_POST['diffonexch']=$row['diffonexch'];
        $provee=$row['supplierno'];
        $_POST['saldo']=$row['ovamount'];
        $_POST['currcode']=$row['currcode'];
        /* }
        }else
        { */
        // ad}

        $table='gltrans';
        $umovto =$i; //$_POST['selMovimiento'][$i];
        $umovtones[]=$umovto;
        $tagref = $_POST['tagref'];//$_POST['tagref'][$umovto];
         
        $status = $_POST['status'];//$_POST['status'][$umovto];
        $saldo = $_POST['saldo'];//$_POST['saldo'][$umovto];
        //$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
        $supplierid = $_POST['supplierid']; ////$_POST['status'][$umovto]
        $ratefactura = $_POST['rate']; ////$_POST['status'][$umovto]
        $idfactura = $row['id']; //
      
        $diffonexch = $_POST['diffonexch']; //[$umovto]

        $procesar=true;
        $procesarobtenernum=true;
        $procesarprimero=true;
        $saldosum=0;
        $value=$provee;

        $ueDecrip = $row ['ln_ue'];

        if (true) {
            $inisupplierid = $supplierid;

            $saldofactura =  ($saldo / $ratefactura);

            $initransno = $TransNo;
            
            //ad24}
            /*$bankaccount = $_POST['BankAccount'];
            if ($Tipopago == 'Cheque') {
            $SQL="SELECT  chequeno
            FROM gltrans_polisa 
            WHERE supplier='".$supplierid."'
            and chequeno >0";
            $result=  DB_query($SQL, $db);
            if(DB_num_rows($result)>0) {
            $row=  DB_fetch_array($result);
            $ChequeNum = $row['chequeno'];
            }
            else
            {
            $ChequeNum = GetNextChequeNo($_POST['BankAccount'], $db);
            }
            }else{
            $ChequeNum = $Tipopago;
            }*/

            //Si el campo de numero de cheque no esta vacio y el tipo de pago es cheque entonces seCambia el numero de cheque por el escrito.

            /*if  (!empty($_POST['numchequeuser']) && $_POST['Tipopago']=='Cheque') {
            $ChequeNum=$_POST['numchequeuser'];
            }*/
            //echo "<br>Cheque num: $ChequeNum";
            $narrative  = "Pago de factura";
            $Transtype = $type;
            /* CREAR UN REGISTRO DEL PAGO DE PROVEEDOR           */
            /* Create a SuppTrans entry for the supplier payment */
            $ratecheque = ($_POST['ExRate']/$_POST['FunctionalExRate']);
            $saldocheque = ($saldo / ($_POST['ExRate']/$_POST['FunctionalExRate']));
            if (!$procesar) {
              $saldocheque2=($saldosum / ($_POST['ExRate']/$_POST['FunctionalExRate']));
            } else {
              $saldocheque2=$saldocheque;
            }

            $ctaxtipoproveedor=traeCuentaProveedor($supplierid, $db); // codigo 20/10 otubre 2017;

            $consulta= "SELECT stockid AS stk,supptrans.id, 
            supptrans.transno, 
            supptrans.type,
            supptrans.ovamount,(price*qty) AS precio2,
            ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
            JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
            WHERE stockid=stk),2) 
            AS impuesto,
            supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
            supptrans.ln_ue,
            supptransdetails.period,
            supptransdetails.nu_id_compromiso,
            supptransdetails.nu_id_devengado,
            supptransdetails.nu_idret,
            chartdetailsbudgetbytag.tagref as tagrefClave
            FROM supptrans
            INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
            JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = supptransdetails.clavepresupuestal
            WHERE id = ('".$ids[$i]."')";
            //echo '<br<<pre>'.$consulta;
            $resultado = DB_query($consulta, $db);
            $datosRequi=array();
            // Array para folios agrupados (UR-UE)
            $infoFolios = array();
            while ($registro = DB_fetch_array($resultado)) {
                $datosRequi=  requisicion($registro['transno'], $registro['type'], $db);
                $tagrefClave = $registro['tagrefClave'];
                /*print_r($datosRequi);
                exit();*/
                // Generacion del momento contable para el pago
                //$importe = (($registro["precio2"] * $registro["qty"]) * (1 + $registro["impuesto"])) / $ratefactura;
                //$importe = ( (  ($registro["price"])* ($registro["qty"]))  * (1 + $registro["impuesto"])) / $ratefactura;
                
                // Ver si existe folio para movimientos
                $folioPolizaUe = 0;
                foreach ($infoFolios as $datosFolios) {
                    // Recorrer para ver si exi
                    if ($datosFolios['tagref'] == $tagrefClave && $datosFolios['ue'] == $registro ['ln_ue']) {
                        // Si existe
                        $folioPolizaUe = $datosFolios['folioPolizaUe'];
                    }
                }
                if ($folioPolizaUe == 0) {
                    // Si no existe folio sacar folio
                    // $transno = GetNextTransNo($type, $db);
                    // Folio de la poliza por unidad ejecutora
                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagrefClave, $registro ['ln_ue'], $Transtype);
                    $infoFolios[] = array(
                        'tagref' => $tagrefClave,
                        'ue' => $registro ['ln_ue'],
                        'type' => $Transtype,
                        'transno' => $TransNo,
                        'folioPolizaUe' => $folioPolizaUe
                    );
                }

                $infoClaves = array();
                $infoClaves[] = array(
                    'accountcode' => $registro ['clavepresupuestal']
                );
                $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                $PeriodNo = $respuesta['periodo'];
                $fechacheque = $respuesta['fecha'];

                $importe = ( (  ($registro["price"])* ($registro["qty"]))  * (1 + 0)) / $ratefactura;

                $importe=truncateFloat($importe, $digitos);
                if (!$unificarPD) {
                    if ($procesar) {
                        //exit();
                        /*GeneraMovimientoContablePresupuesto($tipomovimiento, $tipo_abono, $tipo_cargo, $transno, $periodo, $total, $unidadnegocio, $fecha, $clavepresupuestal= "", $referencia= "", &$db, $tablaalterna=false, $idsupplier='', $descripcion='', $narrativaAlter="",
                        $ue="", $posteo=1) */
                        GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagrefClave, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db, false, $supplierid, $descrip, $datosRequi[1], $registro ['ln_ue'], 1, $ChequeNum, $folioPolizaUe);//$fechacheque_contable
                        // ,false,'','','',$registro ['ln_ue']
                    } else {
                        //exit();
                        GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagrefClave, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db, false, $supplierid, $descrip, $datosRequi[1], $registro ['ln_ue'], 1, $ChequeNum, $folioPolizaUe);//$fechacheque_contable
                    }

                    // Log Presupuesto
                    $descriptionLog = "Generación de Pago";
                    $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagrefClave, $registro["clavepresupuestal"], $registro['period'], $importe, 261, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Abono
                    $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagrefClave, $registro["clavepresupuestal"], $registro['period'], $importe * -1, 265, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Cargo

                    // Registros matriz pagado inicio
                    // Movimiento de proveedor
                    $SQL1="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion,
                    ln_ue,
                    posted,
                    nu_folio_ue,
                    nu_pagado
                    ) VALUES (
                    '".$Transtype."',
                    '" . $TransNo . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $ctaxtipoproveedor . "',
                    '" . $datosRequi[1] . "',
                    '" . $importe . "',
                    '" . $tagrefClave . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$supplierid."',
                    '".$descrip."',
                    '".$registro ['ln_ue'] ."',
                    '1',
                    '".$folioPolizaUe."',
                    '1'
                    )";
                    // $result = DB_query($SQL1, $db);
                    // Movimiento de banco
                    $bankaccount=$bancosOrigen[0];
                    $SQL2="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    cuentabanco,
                    descripcion,
                    ln_ue,
                    posted,
                    nu_folio_ue,
                    nu_pagado
                    ) VALUES (
                    '".$Transtype."',
                    '" . $TransNo . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $bankaccount . "',
                    '" . $datosRequi[1] . "',
                    '" . -1*($importe). "',
                    '" . $tagrefClave . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$supplierid."',
                    1 ,
                    '".$descrip."',
                    '".$registro ['ln_ue']."',
                    '1',
                    '".$folioPolizaUe."',
                    '1'
                    )";
                    // $result = DB_query($SQL2, $db);
                    // Registros matriz pagado fin
                }
                /*ad24 if($unificarPD AND $totaldocumentos>$i)
                {
                    GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagref, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db,true,$_POST['UnificarPagoselect'],$descrip);//$fechacheque_contable
                }*/
            }//fin while

            if (!$procesar) {
                $saldosuma=$saldosum;
            } else {
                $saldosuma=$saldo;
            }
            /* print($NumeroCheque[$i]);
            print($ChequeNum); */
            $SQL = "INSERT INTO supptrans 
            (transno,
            type,
            supplierno,
            trandate,
            suppreference,
            rate,
            currcode,
            ovamount,
            transtext,
            tagref,
            origtrandate,
            ref1,
            alloc,
            settled, 
            ref2,
            ln_ue,
            nu_anio_fiscal
            )
            VALUES 
            (" . $TransNo. ",
            '".$type."',  
            '" . $supplierid . "',
            '" . $fechacheque . "',
            '" . $ChequeNum . "',
            '" . (($_POST['ExRate'])/($_POST['FunctionalExRate'])) . "',
            '".$_POST['currcode']."',
            '" . (-1)*$saldosuma . "',
            '" . $narrative . "',
            '" . $tagref . "',
            now(),
            '" . $ChequeNum . "',
            '" . (-1)*$saldosuma . "',
            " . "1" . ",
            '".$ids[$i]."',
            '".$ueDecrip."',
            '".$_SESSION['ejercicioFiscal']."'
            )";
            //[$umovto]
            $ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque AD24');
            $DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

            $prevdiffonexch = $prevdiffonexch +  ($_POST['ExRate']/$_POST['FunctionalExRate']);
            if ($procesarobtenernum and $procesarprimero) {
                $idcheque = DB_Last_Insert_ID($db, 'supptrans', 'id');
                $idchequeproveedor[$supplierid]=$idcheque;
            }
            $idchequess = DB_Last_Insert_ID($db, 'supptrans', 'id');
            $idFacturaNueva=$idchequess;
            
            $docs[]=array($TransNo,$type,  DB_Last_Insert_ID($db, 'supptrans', 'id'));
            //////////////////////////////////////////////////////////////////////////

            $SQL = "UPDATE suppliers SET
            lastpaiddate = '" . $fechacheque . "',
            lastpaid='" . $saldo ."'
            WHERE suppliers.supplierid='" . $supplierid . "'";
            $ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
            $DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
            //echo "<pre>" . $SQL;
            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

            $SQL = "INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto)
            VALUES ('" . $fechacheque . "','" . $saldo ."','". $idcheque ."', 
            '". $idfactura ."')";
            /* $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  _('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' ._('could not be inserted because');
            $DbgMsg = _('The following SQL to insert the allocation record was used'); */
            $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            //echo "<pre>" . $SQL;
            //prnMsg(_('Inserto nueva aplicacion...'),'success');

            $SQL = "UPDATE supptrans
            SET diffonexch='". $diffonexch ."', 
            alloc = '".  $saldo ."', 
            settled = '". "1" ."' 
            WHERE id = '". $idfactura."'";
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
            $DbgMsg = _('The following SQL to update the debtor transaction record was used');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            //echo "<pre>" . $SQL;
            /***/

            if ($_SESSION['CompanyRecord']['gllink_creditors']==1) {
                // //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
                // //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
               
                $narrative =$datosRequi[1]; // $inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura);
                $totalimpuestosretencion = 0;
                if (isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivahonorarios'];
                }
                if (isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrhonorarios'];
                }
                if (isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivaarrendamiento'];
                }
                if (isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrarrendamiento'];
                }
                if (isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionfletes'];
                }
                if (isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencioncedular'];
                } 

                $saldocheque = $saldocheque - $totalimpuestosretencion;
                $saldocheque2 = $saldocheque2 - $totalimpuestosretencion;

                // echo $saldocheque .'='.$saldocheque.'-'.$saldocheque;
                // // DE QUE CUENTA SALE EL DINERO
               
                /*********INICIO PERDIDA CAMBIARIA***************/
                $utilidadperdida = $saldofactura - $saldocheque;
                $saldofactura2 = $saldofactura - $totalimpuestosretencion;
                if ($saldocheque!=$saldofactura2) {
                    if (abs($utilidadperdida) > .1) {
                        if ($utilidadperdida < 0) {
                            $perdida = abs($utilidadperdida);

                            $ctautilidadperdida = $_SESSION['CompanyRecord']['purchasesexchangediffact'];
                            $reference = $supplierid . "@UTIL/PERD CAMBIARIA@" . $perdida;

                            $SQL_up = "INSERT INTO ".$table." (type, 
                            typeno, 
                            trandate, 
                            periodno, 
                            account, 
                            narrative, 
                            amount,
                            tag,
                            chequeno,
                            userid,
                            dateadded,
                            supplier,
                            descripcion
                            ) 
                            VALUES ('".$type."', 
                            '" . $initransno . "', 
                            '" . $fechacheque . "', 
                            '" . $PeriodNo . "', 
                            '" . $ctautilidadperdida . "', 
                            '". $reference . "', 
                            '" . $perdida . "',
                            '" . $tagref . "',
                            '" . $ChequeNum . "',
                            '" . $_SESSION['UserID'] . "',
                            now(),
                            '".$value."',
                            '".$descrip."'
                            )";
                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
                            _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                            $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, true);
                        } else {
                            $utilidad = abs($utilidadperdida);
                            $ctautilidadperdida = $_SESSION['CompanyRecord']['gllink_purchasesexchangediffactutil'];

                            $reference = $supplierid . "@UTIL/PERD CAMBIARIA@" . $utilidad;
                            $SQL_up = "INSERT INTO ".$table." (type, 
                            typeno, 
                            trandate, 
                            periodno, 
                            account, 
                            narrative, 
                            amount,
                            tag,
                            chequeno,
                            userid,
                            dateadded,
                            supplier,
                            descripcion
                            ) 
                            VALUES ('".$type."', 
                            '" . $initransno . "', 
                            '" . $fechacheque . "', 
                            '" . $PeriodNo . "', 
                            '" . $ctautilidadperdida . "', 
                            '". $reference . "', 
                            '" . $utilidad . "',
                            '" . $tagref . "',
                            '" . $ChequeNum . "',
                            '" . $_SESSION['UserID'] . "',
                            now(),
                            '".$value."',
                            '".$descrip."'
                            )";
                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
                            _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                            $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, true);
                        }
                    }
                    /********FIN PERDIDA CAMBIARIA***************/
                }

                /**************************************************/
                /*MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
                $saldo = $_POST['saldo']; //[$umovto]
                $taxrate = .16;

                $CreditorTotal = ($saldo/$_POST['ExRate'])/$_POST['FunctionalExRate'];

                $SQL = 'select * from taxauthorities where taxid=1';
                $result2 = DB_query($SQL, $db);
                if ($TaxAccs = DB_fetch_array($result2)) {
                    $taximpuesto=($CreditorTotal / (1 + $taxrate));
                    $taximpuesto=$CreditorTotal-$taximpuesto;
                    $narrative = $supplierid . "@IMPUESTOA@" . (($taximpuesto*-1));
                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $TaxAccs['purchtaxglaccount'] . "',
                    '" . $narrative . "',
                    '" . ($taximpuesto*-1) . "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    '".$descrip."'
                    )";
                    $ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
                    $DbgMsg = _('El SQL utilizado fue');
                    //echo "<br>" . $SQL;
                    /*$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);*/
                    $narrative = $supplierid . "@IMPUESTOA@" . ($taximpuesto);
                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $TaxAccs['purchtaxglaccountPaid'] . "',
                    '" . $narrative . "',
                    '" . $taximpuesto . "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    '".$descrip."'
                    )";
                    $ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
                    $DbgMsg = _('El SQL utilizado fue');
                    //echo "<br>" . $SQL;
                    /*$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);*/
                } //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS

                /* Obtiene datos de la factura pagada*/
                $sqlfact="SELECT suppreference,
                type,
                transno
                FROM supptrans
                WHERE id = '".$idfactura."'";
                $resultfact = DB_query($sqlfact, $db);
                while ($myrowfact = DB_fetch_array($resultfact)) {
                    $foliorefe = $myrowfact['suppreference'];
                    $typerefe = $myrowfact['type'];
                    $transorefe = $myrowfact['type'];
                }

                $narrative ="Pago factura"; /*$inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura)."folio factura ".$foliorefe." fologierp".$typerefe." ".$transorefe; */
                /* Movimiento Contable de Retencion Honorarios iva */
                if (isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> "") {
                    $retencionivahonorarios = $_SESSION['CompanyRecord']['gllink_retencioniva'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionivahonorarios . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionivahonorarios']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    '".$descrip."'
                    )";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion Honorarios ISR */
                if (isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> "") {
                    $retencionisrhonorarios = $_SESSION['CompanyRecord']['gllink_retencionhonorarios'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES ( 
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionisrhonorarios . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionisrhonorarios']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion Arrendamiento IVA */
                if (isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> "") {
                    $retencionivaarrendamiento = $_SESSION['CompanyRecord']['gllink_retencionIVAarrendamiento'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionivaarrendamiento . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionivaarrendamiento']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."' )";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion Arrendamiento ISR */
                if (isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> "") {
                    $retencionisrarrendamiento = $_SESSION['CompanyRecord']['gllink_retencionarrendamiento'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionisrarrendamiento . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionisrarrendamiento']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion fletes */
                if (isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> "") {
                    $retencionfletes = $_SESSION['CompanyRecord']['gllink_retencionFletes'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionfletes . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionfletes']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion cedular */
                if (isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> "") {
                    $retencioncedular = $_SESSION['CompanyRecord']['gllink_retencionCedular'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencioncedular . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencioncedular']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }
            } // fin gllink_creditors
          
            $sql="select *
            FROM bankaccounts
            WHERE accountcode='" . $bankaccount . "'";
            $Result = DB_query($sql, $db);
            $myrow = DB_fetch_array($Result);
            $pdfprefix=$myrow['pdfprefix'];
            if ($pdfprefix == null) {
              $pdfprefix = "";//
            }

            //actualiza estatus del documento a Ejecutado
            $sql = "UPDATE supptrans
            SET hold ="."3"."  
            WHERE id = '". $ids[$i] ."'";
            $resul = DB_query($sql, $db);
            /***** FIN IMPRESION DE CHEQUE *******************/
            /***/
        }// fin FOR
    }//fin inputerroR 0
}

function fnPagoUnificado($idsUnificados, $ChequeNum1, $bancoOrigen, $db)
{
    $cuenta=$bancoOrigen[0];
    $bancoOrigen= explode("-", $cuenta);
    $ids=$idsUnificados;
    $permisomostrar=1;
    $ueDecrip='';
    $Cheque=explode("-", $ChequeNum1);
    $ChequeNum=$Cheque[0];
    if (isset($_SESSION['TruncarDigitos'])) {
        $digitos=$_SESSION['TruncarDigitos'];
    } else {
        $digitos=4;
    }

    if (isset($_POST['FechaPago'])) {
        $fechapago = $_POST['FechaPago'];
    } elseif (isset($_GET['FechaPago'])) {
        $fechapago = $_GET['FechaPago'];
    } else {
        $fechapago = date("Y-m-d");//
    }

    if (strpos($_SESSION['DefaultDateFormat'], '/')) {
        $flag = "/";
    } elseif (strpos($_SESSION['DefaultDateFormat'], '-')) {
        $flag = "-";
    } elseif (strpos($_SESSION['DefaultDateFormat'], '.')) {
        $flag = ".";
    }
    $diafecha = substr($_POST['FechaPago'], 8, 2);
    $mesfecha = substr($_POST['FechaPago'], 5, 2);
    $aniofecha = substr($_POST['FechaPago'], 0, 4);
    $trandatef = $diafecha.$flag.$mesfecha.$flag.$aniofecha;

    $diaP= date('d');
    $mesP= date('m');
    $anioP= date('Y');

    $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);
    //$transnos[] = $TransNo;

    //if ($_POST['tipocambio'] == 'No') {
    $moneda = 'MXN';
    /*}elseif($_POST['tipocambio'] == 'Yes') {
    $moneda = 'USD';
    }; */

    $fechacheque =$anioP."-".$mesP."-".$diaP;
    //$bankaccount = $_POST['BankAccount'];
    //$ChequeNum = $_POST['ChequeNum'];
    //$Tipopago = $_POST['Tipopago'];

    /*Get the bank account currency and set that too */
    /*$ErrMsg = _('No pude obtener la moneda de la cuenta del banco seleccionada');
    $result = DB_query("SELECT currcode FROM bankaccounts WHERE accountcode = '" . $bankaccount . "'", $db, $ErrMsg);
    $myrow = DB_fetch_row($result);
    $monedabanco = $myrow[0]; */
    //echo "<br>monedabanco: " . $monedabanco;

    /*********FUNCIONES PARA OBTENER RATE****/
    /*Get the exchange rate between the functional currency and the payment currency*/
    $result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $moneda . "'", $db);
    //echo "<br>" . "SELECT rate FROM currencies WHERE currabrev='" . $moneda . "'";
    $myrow = DB_fetch_row($result);
    $tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency

    $_POST['FunctionalExRate'] = 1;

    if ($moneda == $monedabanco) {
        $_POST['ExRate']=1;
        $SuggestedExRate=1;
    }
    if ($monedabanco==$_SESSION['CompanyRecord']['currencydefault']) {
        $_POST['FunctionalExRate']=1;
        $SuggestedFunctionalExRate =1;
        $SuggestedExRate = $tableExRate;
    } else {
        /*Get suggested FunctionalExRate */
        //echo "<br>1.- " . "SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'";
        $result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'", $db);
        $myrow = DB_fetch_row($result);
        $SuggestedFunctionalExRate = $myrow[0];

        /*Get the exchange rate between the functional currency and the payment currency*/
        //echo "<br>2.- " . "select rate FROM currencies WHERE currabrev='" . $moneda . "'";
        $result = DB_query("select rate FROM currencies WHERE currabrev='" . $moneda . "'", $db);
        $myrow = DB_fetch_row($result);
        $tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
        /*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
        $SuggestedExRate = $tableExRate/1; //$SuggestedFunctionalExRate;
    }

    if ($monedabanco != $moneda and isset($monedabanco)) {
        if ($_POST['ExRate']==1 and isset($SuggestedExRate)) {
            $_POST['ExRate'] = $SuggestedExRate;
        }
    }

    if ($monedabanco != $_SESSION['CompanyRecord']['currencydefault'] and isset($monedabanco)) {
        if ($_POST['FunctionalExRate']==1 and isset($SuggestedFunctionalExRate)) {
            $_POST['FunctionalExRate'] = $SuggestedFunctionalExRate;
        }
    }

    $inisupplierid = "";
    $initransno = "";
    $prevdiffonexch = 0;
    $InputError = 0;

    $conte=0;
    //$ids=$_POST['ids'];
    $totaldocumentos = count($ids); //$totaldocumentos = count($_POST['selMovimiento']);
    
    $idFacturaNueva='';
    $type=22;
    $TransNo='';
    $montotUnificado=0;
    //
    $montos=$_POST['montos'];
    //print_r($montos);
    //exit();
    for ($a=0; $a<count($montos); $a++) {
        $montotUnificado= (($montotUnificado)+$montos[$a]);
    }

    $TransNo = GetNextTransNo($type, $db); //unificadoscode

    $SQL = "INSERT INTO supptrans (transno,
    type,
    supplierno,
    trandate,
    suppreference,
    rate,
    currcode,
    ovamount,
    transtext,
    tagref,
    origtrandate,
    ref1,
    alloc,
    settled, 
    ref2, 
    nu_anio_fiscal
    ) ";
    $SQL = $SQL . " VALUES (" . $TransNo. ",
    '".$type."',  
    '" . $supplierid . "',
    '" . $fechacheque . "',
    '" . $ChequeNum . "',
    '" . (($_POST['ExRate'])/($_POST['FunctionalExRate'])) . "',
    'MXN',
    '" . (-1)*$saldosuma . "',
    '" . $narrative . "',
    '" . $tagref . "',
    now(),
    '" . $ChequeNum . "',
    '" . (-1)*$montotUnificado . "',
    " . "1" . ",
    '".$ids[0]."',
    '".$_SESSION['ejercicioFiscal']."'
    )";
    //[$umovto]
    $ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque AD24');
    $DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
    // echo "\n <pre>SQL: ".$SQL."\n";
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    $idcheque = DB_Last_Insert_ID($db, 'supptrans', 'id');

    for ($i=0; $i <$totaldocumentos; $i++) {
        $SQL = "SELECT 
        transno,
        tagref,
        type,
        supplierno,
        suppreference,
        trandate,
        origtrandate,
        duedate,
        promisedate,
        settled,
        rate,
        ovamount,
        ovgst,
        diffonexch,
        alloc,
        transtext,
        hold,
        id,
        folio,
        ref1,
        ref2,
        currcode,
        sent,
        activo,
        originalinvoice,
        u_typeoperation,
        retencionIVA,
        retencionISR,
        retencionCedular, 
        retencionFletes,
        retencionComisiones,
        retencionArrendamiento, 
        retencionIVAArrendamiento,
        reffiscal,
        ln_ue
        FROM supptrans 
        WHERE id = '".$ids[$i]."'";
        $result=  DB_query($SQL, $db);
        $row= DB_fetch_array($result);
        //$_POST['selMovimiento'][$i]=$row['id'];
        $_POST['selMovimiento']=$row['id'];
        $_POST['tagref']=$row['tagref'];
        $_POST['status'] = $row['hold'];
        $_POST['saldo']=$saldosum;
        //$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
        $_POST['supplierid']=$row['supplierno'];
        $_POST['rate']=$row['rate'];
        $_POST['idfactura']=$row['id'];
        $_POST['diffonexch']=$row['diffonexch'];
        $provee=$row['supplierno'];
        $_POST['saldo']=$row['ovamount'];
        $_POST['currcode']=$row['currcode'];
        /* }

        }else
        { */

        // ad}

        $table='gltrans';
        $umovto =$i; //$_POST['selMovimiento'][$i];
        $umovtones[]=$umovto;
        $tagref = $_POST['tagref'];//$_POST['tagref'][$umovto];
         
        $status = $_POST['status'];//$_POST['status'][$umovto];
        $saldo = $_POST['saldo'];//$_POST['saldo'][$umovto];
        //$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
        $supplierid = $_POST['supplierid']; ////$_POST['status'][$umovto]
        $ratefactura = $_POST['rate']; ////$_POST['status'][$umovto]
        $idfactura = $row['id']; //

        $diffonexch = $_POST['diffonexch']; //[$umovto]

        $procesar=true;
        $procesarobtenernum=true;
        $procesarprimero=true;
        $saldosum=0;
        $value=$provee;

        $ueDecrip = $row ['ln_ue'];

        // Actualizar ue al pago
        $SQL = "UPDATE supptrans SET ln_ue = '".$ueDecrip."' WHERE id = '".$idcheque."'";
        $result = DB_query($SQL, $db);

        if (true) {
            $inisupplierid = $supplierid;

            $saldofactura =  ($saldo / $ratefactura);

            $initransno = $TransNo;
           
            $narrative  = "Pago de factura";
            $Transtype = $type;
            /* CREAR UN REGISTRO DEL PAGO DE PROVEEDOR           */
            /* Create a SuppTrans entry for the supplier payment */
            $ratecheque = ($_POST['ExRate']/$_POST['FunctionalExRate']);
            $saldocheque = ($saldo / ($_POST['ExRate']/$_POST['FunctionalExRate']));
            if (!$procesar) {
                $saldocheque2=($saldosum / ($_POST['ExRate']/$_POST['FunctionalExRate']));
            } else {
                $saldocheque2=$saldocheque;
            }

            $ctaxtipoproveedor=traeCuentaProveedor($inisupplierid, $db); // codigo 20/10 otubre 2017;

            $consulta= "SELECT stockid AS stk,supptrans.id, 
            supptrans.transno, 
            supptrans.type, 
            supptrans.ovamount,(price*qty) AS precio2,
            ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
            JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
            WHERE stockid=stk),2) 
            AS impuesto,
            supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal,
            supptrans.ln_ue,
            supptransdetails.period,
            supptransdetails.nu_id_compromiso,
            supptransdetails.nu_id_devengado,
            supptransdetails.nu_idret,
            chartdetailsbudgetbytag.tagref as tagrefClave
            FROM supptrans
            INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
            JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = supptransdetails.clavepresupuestal
            WHERE id = ('".$ids[$i]."')";
            // echo "\n <pre>consulta: ".$consulta."\n";
            $resultado = DB_query($consulta, $db);
            // Array para folios agrupados (UR-UE)
            $infoFolios = array();
            while ($registro = DB_fetch_array($resultado)) {
                $datosRequi=  requisicion($registro['transno'], $registro['type'], $db);
                $tagrefClave = $registro['tagrefClave'];
                // Generacion del momento contable para el pago
                //$importe = (($registro["precio2"] * $registro["qty"]) * (1 + $registro["impuesto"])) / $ratefactura;

                //$importe = ( (  ($registro["price"])* ($registro["qty"]))  * (1 + $registro["impuesto"])) / $ratefactura;
                
                // Ver si existe folio para movimientos
                $folioPolizaUe = 0;
                foreach ($infoFolios as $datosFolios) {
                    // Recorrer para ver si exi
                    if ($datosFolios['tagref'] == $tagrefClave && $datosFolios['ue'] == $registro ['ln_ue']) {
                        // Si existe
                        $folioPolizaUe = $datosFolios['folioPolizaUe'];
                    }
                }
                if ($folioPolizaUe == 0) {
                    // Si no existe folio sacar folio
                    // $transno = GetNextTransNo($type, $db);
                    // Folio de la poliza por unidad ejecutora
                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagrefClave, $registro ['ln_ue'], $Transtype);
                    $infoFolios[] = array(
                        'tagref' => $tagrefClave,
                        'ue' => $registro ['ln_ue'],
                        'type' => $Transtype,
                        'transno' => $TransNo,
                        'folioPolizaUe' => $folioPolizaUe
                    );
                }
                
                $infoClaves = array();
                $infoClaves[] = array(
                    'accountcode' => $registro ['clavepresupuestal']
                );
                $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
                $PeriodNo = $respuesta['periodo'];

                $importe = ( (  ($registro["price"])* ($registro["qty"]))  * (1 + 0)) / $ratefactura;

                $importe=truncateFloat($importe, $digitos);
                $mensajeUnificado="Pago unificado No. cheque";
              
                if (!$unificarPD) {
                    if ($procesar) {
                        //exit();
                        GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagrefClave, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db, false, $supplierid, $descrip, $datosRequi[1], $registro ['ln_ue'], 1, $ChequeNum, $folioPolizaUe);//$fechacheque_contable
                       // ,false,'','','',$registro ['ln_ue']
                    } else {
                        //exit();
                        GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagrefClave, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db, false, $supplierid, $descrip, $datosRequi[1], $registro ['ln_ue'], 1, $ChequeNum, $folioPolizaUe);//$fechacheque_contable
                    }

                    // Log Presupuesto
                    $descriptionLog = "Generación de Pago";
                    // echo "\n clavepresupuestal: ".$registro['clavepresupuestal']."\n";
                    // echo "\n importe: ".$importe."\n";
                    $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagrefClave, $registro["clavepresupuestal"], $registro['period'], $importe, 261, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Abono
                    $agregoLog = fnInsertPresupuestoLog($db, $Transtype, $TransNo, $tagrefClave, $registro["clavepresupuestal"], $registro['period'], $importe * -1, 265, "", $descriptionLog, 1, '', 0, $registro ['ln_ue'], $registro ['nu_id_compromiso'], $registro ['nu_id_devengado'], $registro ['nu_idret']); // Cargo

                    $SQL1="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion,
                    ln_ue,
                    posted,
                    nu_folio_ue,
                    nu_pagado
                    ) VALUES (
                    '".$Transtype."',
                    '" . $TransNo . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $ctaxtipoproveedor . "',
                    '" . $datosRequi[1] . "',
                    '" . $importe . "',
                    '" . $tagrefClave . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    '".$descrip."',
                    '".$registro ['ln_ue']."',
                    '1',
                    '".$folioPolizaUe."',
                    '1'
                    )";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    // $result = DB_query($SQL1, $db, $ErrMsg, $DbgMsg, true);

                    // // DE  QUE CUENTA SALE EL DINERO
                    $bankaccount=$bancoOrigen[0];
                    if (empty($value)) {
                        $value="x";
                    }
                    $SQL2="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    cuentabanco,
                    descripcion,
                    ln_ue,
                    posted,
                    nu_folio_ue,
                    nu_pagado
                    ) VALUES (
                    '".$Transtype."',
                    '" . $TransNo . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $bankaccount . "',
                    '" . $datosRequi[1] . "',
                    '" . -1*($importe). "',
                    '" . $tagrefClave . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    1 ,
                    '".$descrip."',
                    '".$registro ['ln_ue']."',
                    '1',
                    '".$folioPolizaUe."',
                    '1'
                    )";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    // $result = DB_query($SQL2, $db, $ErrMsg, $DbgMsg, true);
                }
            }//fin while

            if (!$procesar) {
                $saldosuma=$saldosum;
            } else {
                $saldosuma=$saldo;
            }

           //unificadoscode aqui iba el supptrans

            //ad24}

            $prevdiffonexch = $prevdiffonexch +  ($_POST['ExRate']/$_POST['FunctionalExRate']);
            //unificadoscode
            /*if($procesarobtenernum and $procesarprimero) {
                $idcheque = DB_Last_Insert_ID($db, 'supptrans', 'id');
                $idchequeproveedor[$supplierid]=$idcheque;
            }*/
            //unificadoscode fin
            $idchequess = DB_Last_Insert_ID($db, 'supptrans', 'id');
            $idFacturaNueva=$idchequess;
            

            $docs[]=array($TransNo,$type,  DB_Last_Insert_ID($db, 'supptrans', 'id'));
            //////////////////////////////////////////////////////////////////////////

            $SQL = "UPDATE suppliers SET
                                            lastpaiddate = '" . $fechacheque . "',
                                            lastpaid='" . $saldo ."'
                                            WHERE suppliers.supplierid='" . $supplierid . "'";


            $ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
            $DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
            //echo "<pre>" . $SQL;
            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

            $SQL = "INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto)
                                            VALUES ('" . $fechacheque . "','" . $saldo ."','". $idcheque ."', 
                                            '". $idfactura ."')";
                                           

            
            $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
           

            $SQL = "UPDATE supptrans
                                            SET diffonexch='". $diffonexch ."', 
                                                alloc = '".  $saldo ."', 
                                                settled = '". "1" ."' 
                                            WHERE id = '". $idfactura."'";


            $ErrMsg = _('ERROR CRITICO') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
            $DbgMsg = _('The following SQL to update the debtor transaction record was used');

            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            //echo "<pre>" . $SQL;
            /***/
            if ($_SESSION['CompanyRecord']['gllink_creditors']==1) {
                //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
                //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
                if ($inisupplierid != '') {
                    $tipoproveedor = ExtractTypeSupplier($inisupplierid, $db);
                    $ctaxtipoproveedor = SupplierAccount($tipoproveedor, "gl_accountsreceivable", $db);
                    if ($unificarPD) {
                        $tipoproveedorpuente = ExtractTypeSupplier($_POST['UnificarPagoselect'], $db);
                        $ctaxtipoproveedorpuente = SupplierAccount($tipoproveedorpuente, "gl_accountsreceivable", $db);
                    }
                } else {
                    $ctaxtipoproveedor = $_SESSION['CompanyRecord']['creditorsact'];
                }
                
                $narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . ($saldofactura);
                /*ad24 if($unificarPD && $totaldocumentos==$i)
                {
                    $inisupplierid . "-" . "PAGO DE FACTURAS@" . ($saldofactura).' '.$facturasafectadastypetransno;
                } */
                // A QUIEN SE LA PAGA
                if (empty($value)) {
                    $value='x';
                }
                

                $narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura);
                $totalimpuestosretencion = 0;
                if (isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivahonorarios'];
                }
                if (isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrhonorarios'];
                }
                if (isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivaarrendamiento'];
                }
                if (isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrarrendamiento'];
                }
                if (isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionfletes'];
                }
                if (isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> "") {
                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencioncedular'];
                }

                $saldocheque = $saldocheque - $totalimpuestosretencion;
                $saldocheque2 = $saldocheque2 - $totalimpuestosretencion;
                
                /*********INICIO PERDIDA CAMBIARIA***************/
                $utilidadperdida = $saldofactura - $saldocheque;
                $saldofactura2 = $saldofactura - $totalimpuestosretencion;
                if ($saldocheque!=$saldofactura2) {
                    if (abs($utilidadperdida) > .1) {
                        if ($utilidadperdida < 0) {
                            $perdida = abs($utilidadperdida);

                            $ctautilidadperdida = $_SESSION['CompanyRecord']['purchasesexchangediffact'];
                            $reference = $supplierid . "@UTIL/PERD CAMBIARIA@" . $perdida;

                            $SQL_up = "INSERT INTO ".$table." (type, 
                            typeno, 
                            trandate, 
                            periodno, 
                            account, 
                            narrative, 
                            amount,
                            tag,
                            chequeno,
                            userid,
                            dateadded,
                            supplier,
                            descripcion
                            ) 
                            VALUES ('".$type."', 
                            '" . $initransno . "', 
                            '" . $fechacheque . "', 
                            '" . $PeriodNo . "', 
                            '" . $ctautilidadperdida . "', 
                            '". $reference . "', 
                            '" . $perdida . "',
                            '" . $tagref . "',
                            '" . $ChequeNum . "',
                            '" . $_SESSION['UserID'] . "',
                            now(),
                            '".$value."',
                            '".$descrip."'
                            )";
                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
                            _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                            $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, true);
                        } else {
                            $utilidad = abs($utilidadperdida);
                            $ctautilidadperdida = $_SESSION['CompanyRecord']['gllink_purchasesexchangediffactutil'];

                            $reference = $supplierid . "@UTIL/PERD CAMBIARIA@" . $utilidad;
                            $SQL_up = "INSERT INTO ".$table." (type, 
                            typeno, 
                            trandate, 
                            periodno, 
                            account, 
                            narrative, 
                            amount,
                            tag,
                            chequeno,
                            userid,
                            dateadded,
                            supplier,
                            descripcion
                            ) 
                            VALUES ('".$type."', 
                            '" . $initransno . "', 
                            '" . $fechacheque . "', 
                            '" . $PeriodNo . "', 
                            '" . $ctautilidadperdida . "', 
                            '". $reference . "', 
                            '" . $utilidad . "',
                            '" . $tagref . "',
                            '" . $ChequeNum. "',
                            '" . $_SESSION['UserID'] . "',
                            now(),
                            '".$value."',
                            '".$descrip."'
                            )";
                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
                            _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                            $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, true);
                        }
                    }
                    /********FIN PERDIDA CAMBIARIA***************/
                }

                /**************************************************/
                /*MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
                $saldo = $_POST['saldo']; //[$umovto]
                $taxrate = .16;

                $CreditorTotal = ($saldo/$_POST['ExRate'])/$_POST['FunctionalExRate'];

                $SQL = 'select * from taxauthorities where taxid=1';
                $result2 = DB_query($SQL, $db);
                if ($TaxAccs = DB_fetch_array($result2)) {
                    $taximpuesto=($CreditorTotal / (1 + $taxrate));
                    $taximpuesto=$CreditorTotal-$taximpuesto;
                    $narrative = $supplierid . "@IMPUESTOA@" . (($taximpuesto*-1));
                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $TaxAccs['purchtaxglaccount'] . "',
                    '" . $narrative . "',
                    '" . ($taximpuesto*-1) . "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    '".$descrip."'
                    )";
                    $ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
                    $DbgMsg = _('El SQL utilizado fue');
                    //echo "<br>" . $SQL;
                    /*$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);*/
                    $narrative = $supplierid . "@IMPUESTOA@" . ($taximpuesto);
                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $TaxAccs['purchtaxglaccountPaid'] . "',
                    '" . $narrative . "',
                    '" . $taximpuesto . "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    '".$descrip."'
                    )";
                    $ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
                    $DbgMsg = _('El SQL utilizado fue');
                    //echo "<br>" . $SQL;
                    /*$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);*/
                } //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS

                /* Obtiene datos de la factura pagada*/
                $sqlfact="SELECT suppreference,
                type,
                transno
                FROM supptrans
                WHERE id = '".$idfactura."'";
                $resultfact = DB_query($sqlfact, $db);
                while ($myrowfact = DB_fetch_array($resultfact)) {
                    $foliorefe = $myrowfact['suppreference'];
                    $typerefe = $myrowfact['type'];
                    $transorefe = $myrowfact['type'];
                }

                $narrative ="Pago factura"; /*$inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura)."folio factura ".$foliorefe." fologierp".$typerefe." ".$transorefe; */
                /* Movimiento Contable de Retencion Honorarios iva */
                if (isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> "") {
                    $retencionivahonorarios = $_SESSION['CompanyRecord']['gllink_retencioniva'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionivahonorarios . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionivahonorarios']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."',
                    '".$descrip."'
                    )";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion Honorarios ISR */
                if (isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> "") {
                    $retencionisrhonorarios = $_SESSION['CompanyRecord']['gllink_retencionhonorarios'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES ( 
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionisrhonorarios . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionisrhonorarios']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion Arrendamiento IVA */
                if (isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> "") {
                    $retencionivaarrendamiento = $_SESSION['CompanyRecord']['gllink_retencionIVAarrendamiento'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionivaarrendamiento . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionivaarrendamiento']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."' )";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion Arrendamiento ISR */
                if (isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> "") {
                    $retencionisrarrendamiento = $_SESSION['CompanyRecord']['gllink_retencionarrendamiento'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionisrarrendamiento . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionisrarrendamiento']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion fletes */
                if (isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> "") {
                    $retencionfletes = $_SESSION['CompanyRecord']['gllink_retencionFletes'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencionfletes . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencionfletes']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }

                /* Movimiento Contable de Retencion cedular */
                if (isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> "") {
                    $retencioncedular = $_SESSION['CompanyRecord']['gllink_retencionCedular'];

                    $SQL="INSERT INTO ".$table." ( type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    chequeno,
                    userid,
                    dateadded,
                    supplier,
                    descripcion) ";
                    $SQL=$SQL . "VALUES (
                    '".$type."',
                    '" . $initransno . "',
                    '" . $fechacheque . "',
                    '" . $PeriodNo . "',
                    '" . $retencioncedular . "',
                    '" . $narrative . "',
                    '" . -1*($_POST['retencioncedular']). "',
                    '" . $tagref . "',
                    '" . $ChequeNum . "',
                    '" . $_SESSION['UserID'] . "',
                    now(),
                    '".$value."','".$descrip."')";
                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }
            } // fin gllink_creditors
            
            $sql="select *
            FROM bankaccounts
            WHERE accountcode='" . $bankaccount . "'";
            $Result = DB_query($sql, $db);
            $myrow = DB_fetch_array($Result);
            $pdfprefix=$myrow['pdfprefix'];
            if ($pdfprefix == null) {
                $pdfprefix = "";//
            }

            //actualiza estatus del documento a Ejecutado
            $sql = "UPDATE supptrans
            SET hold ="."3"."  
            WHERE id = '". $ids[$i] ."'";
            $resul = DB_query($sql, $db);
            /***** FIN IMPRESION DE CHEQUE *******************/
            /***/
        }// fin FOR
    }//fin inputerroR 0
    $SQL="UPDATE supptrans set supplierno='".$supplierid ."',tagref='".$tagref."' WHERE transno='".$TransNo."' AND type = '".$type."'";

    $ErrMsg = "No se actualizao el estatus.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
}

if ($proceso == 'validarEstatusReversar') {
    // Validar estatus de los documentos seleccionados para rechazar
    $infoDoc = $_POST['infoDoc'];
    $TransResult = true;
    $Mensaje = "";

    $sqlDatos = "";
    foreach ($infoDoc as $dato) {
        // echo "\n dato: ".$dato;
        if ($sqlDatos == "") {
            $sqlDatos .= "'".$dato."'";
        } else {
            $sqlDatos .= ", '".$dato."'";
        }
    }

    $SQL = "SELECT 
    DISTINCT
    supptrans.type, 
    supptrans.transno, 
    supptrans.supplierno, 
    supptrans.suppreference,
    supptrans.id,
    supptrans.hold,
    supptransdetails.requisitionno,
    systypescat.typename,
    systypescat.nu_panel_pagos
    FROM supptrans
    JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
    JOIN systypescat ON systypescat.typeid = supptrans.type
    WHERE supptrans.id IN (".$sqlDatos.")
    AND supptrans.hold != 0";
    $ErrMsg = "Validar estatus de los documentos seleccionados";
    $TransResult2 = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult2)) {
        // Documentos seleccionados
        $TransResult = false;
        // $Mensaje .= '<p>La factura '.$myrow['suppreference'].' de la requisisción '.$myrow['requisitionno'].' su estatus es: '.fnEstado($myrow['hold'], 1).'</p>';
        $Mensaje .= '<p>Operación '.$myrow['type'].' - '.$myrow['typename'].' con el Folio '.$myrow['transno'].' su estatus es: '.fnEstado($myrow['hold'], 1).'</p>';
    }
}

if ($proceso == 'reversarDocumentos') {
    // Validar estatus de los documentos seleccionados para rechazar
    $infoDoc = $_POST['infoDoc'];
    $TransResult = true;
    $Mensaje = "<p>Reversa realizada</p>";

    foreach ($infoDoc as $dato) {
        // Obtener información para realizar movimientos de reversa
        $SQL = "SELECT
        supptrans.type,
        supptrans.transno,
        systypescat.typename,
        systypescat.nu_panel_pagos
        FROM supptrans
        JOIN systypescat ON systypescat.typeid = supptrans.type
        WHERE supptrans.id = '".$dato."'";
        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
        $myrow = DB_fetch_array($TransResult2);
        $typeDoc = $myrow['type'];
        $transnoDoc = $myrow['transno'];
        $typenameDoc = $myrow['typename'];
        $panelDoc = $myrow['nu_panel_pagos'];

        if ($typeDoc == '20') {
            // Si es factura de compra
            $typeNuevo = 289;
            $transnoNuevo = GetNextTransNo($typeNuevo, $db);

            $SQL = "SELECT 
            supptrans.type,
            supptrans.transno,
            supptrans.supplierno, 
            suppliers.suppname,
            supptrans.suppreference,
            supptrans.id,
            supptrans.hold,
            supptransdetails.requisitionno,
            supptransdetails.stockid,
            supptransdetails.price,
            supptransdetails.qty,
            supptransdetails.orderno,
            supptransdetails.grns,
            grns.podetailitem,
            supptrans.ln_ue,
            supptrans.tagref
            FROM supptrans
            JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
            JOIN suppliers ON suppliers.supplierid = supptrans.supplierno
            LEFT JOIN grns ON grns.grnno = supptransdetails.grns
            WHERE supptrans.id IN ('".$dato."')";
            $TransResult2 = DB_query($SQL, $db, $ErrMsg);
            $transnoAnt = 0;
            $requisitionno = 0;
            $suppreference = '';
            $tagref = 0;
            $ln_ue = 0;
            while ($myrow = DB_fetch_array($TransResult2)) {
                $transnoAnt = $myrow['transno'];
                $requisitionno = $myrow['requisitionno'];
                $suppreference = $myrow['suppreference'];

                $tagref = $myrow['tagref'];
                $ln_ue = $myrow['ln_ue'];

                // Actualizar detalle de la orden de compra
                $SQL = "UPDATE purchorderdetails SET qtyinvoiced = qtyinvoiced - " . $myrow['qty'] ."
                WHERE podetailitem = '" . $myrow['podetailitem'] . "'";
                $ErrMsg = 'No se actualizó el detalle de la orden de compra';
                $result = DB_query($SQL, $db, $ErrMsg);

                // Actualizar registros de la recepción
                $SQL = "UPDATE grns SET quantityinv = quantityinv - " . $myrow['qty'] . "
                WHERE grnno = '" . $myrow['grns'] . "'";
                $ErrMsg = 'No se actualizó el detalle de la recepeción de orden de compra';
                $result = DB_query($SQL, $db, $ErrMsg);
            }

            // Folio de la poliza por unidad ejecutora
            $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $ln_ue, $typeNuevo);

            // Actualizar estatus de la factura de pago
            $SQL = "UPDATE supptrans SET hold = '-2', ovamount = '0', ovgst = '0' WHERE id = '".$dato."'";
            $ErrMsg = 'No se actualizó el estatus de la factura de pago';
            $result = DB_query($SQL, $db, $ErrMsg);

            // Movimientos contrarios de la póliza
            fnInsertPolizaMovContrarios($db, 20, $transnoAnt, $typeNuevo, $transnoNuevo, $folioPolizaUe);

            // Agregar descripción de reversada
            $SQL = "UPDATE gltrans SET narrative = CONCAT('Factura ".$suppreference." reversada. ', narrative) 
            WHERE type = '".$typeNuevo."' AND typeno = '".$transnoNuevo."'";
            $ErrMsg = 'No se actualizó el estatus de la factura de pago';
            $result = DB_query($SQL, $db, $ErrMsg);

            // Moviemintos contrarios del log presupuestal
            fnInsertPresupuestoLogMovContrarios($db, 20, $transnoAnt, $typeNuevo, $transnoNuevo);

            // Agregar descripción de reversada a log presupuestal
            $SQL = "UPDATE chartdetailsbudgetlog SET description = CONCAT('Movimientos de reversa Factura de Pago ".$transnoAnt.". ', description)
            WHERE type = '".$typeNuevo."' AND transno = '".$transnoNuevo."'";
            $ErrMsg = 'No se actualizó el estatus de la factura de pago';
            $result = DB_query($SQL, $db, $ErrMsg);

            // $Mensaje .= '<p>La factura '.$suppreference.' de la requisisción '.$requisitionno.' ha sido reversada</p>';
        } else {
            // Documento del panel de devengados
            $SQL = "UPDATE supptrans SET hold = '-2' WHERE id = '".$dato."'";
            $TransResult2 = DB_query($SQL, $db, $ErrMsg);
        }

        $Mensaje .= '<p>Operación '.$typeDoc.' - '.$typenameDoc.' con el Folio '.$transnoDoc.' ha sido rechazado</p>';
    }
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
