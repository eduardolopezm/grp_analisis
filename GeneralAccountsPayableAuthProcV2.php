<?php
/* $Revision: 1.13 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
2.- se arreglo la variable $funcion
*/
/*
 * AHA
* 5-Nov-2014
* Cambio de ingles a espa�ol los mensajes de usuario de tipo error,info,warning, y success.
*/
/*
 * AHA 	28-ENE-2015
 * 		Cambio al nuevo diseño visual 
 */
/*
 *  
 */

$PageSecurity = 2;
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
/*error_reporting(E_ALL);

                ini_set('display_errors', '1');

                ini_set('log_errors', 1);

                ini_set('error_log', dirname(__FILE__) . '/error_log.txt');*/

$funcion=634;
include('includes/SecurityFunctions.inc');
/*
Actualizacion a bankaccounts
ALTER TABLE `erptecnoaplicada2012`.`bankaccounts` ADD COLUMN `pdfprefix` varchar(10) AFTER `tagref`, ADD COLUMN `chequeno` int(11) NOT NULL DEFAULT '0' AFTER `pdfprefix`;

funcion Function GetNextChequeNo en includes/SQLCommonFunctions.inc

*/

//FECHA DEL CHEQUE
if (isset($_POST['chkFromYear'])) {
    $chkFromYear=$_POST['chkFromYear'];
} elseif(isset($_GET['chkFromYear'])) {
    $chkFromYear=$_GET['chkFromYear'];
}else{
    $chkFromYear=date('Y');
}
$permisomostrar=Havepermission($_SESSION['UserID'], 1420, $db);
$permisomostrar=1;
if (isset($_POST['chkFromMes'])) {
    $chkFromMes=$_POST['chkFromMes'];
}elseif(isset($_GET['chkFromMes'])) {
    $chkFromMes=$_GET['chkFromMes'];
} else {
    $chkFromMes=date('m');
}
if(strlen($chkFromMes) == 1) {
    $chkFromMes = '0' . $chkFromMes; 
}	
if (isset($_POST['chkFromDia'])) {
    $chkFromDia=$_POST['chkFromDia'];
}elseif(isset($_GET['chkFromDia'])) {
    $chkFromDia=$_GET['chkFromDia'];
} else {
    $chkFromDia=date('d');
}

if(strlen($chkFromDia) == 1) {
    $chkFromDia = '0' . $chkFromDia; 
}



/* OBTENGO FECHAS*/

if (isset($_POST['FromYear'])) {
    $FromYear=$_POST['FromYear'];
} else {	
    $d=date("Y-m-d");
    $delta = date("w",strtotime($d)) - 1;
    if ($delta <0) $delta = 6;
    $FromYear=date("Y", mktime(0,0,0,date('m'), date('d')-$delta, date('Y') ));
}

if (isset($_POST['FromMes'])) {
    $FromMes=$_POST['FromMes'];
} else {
    $d=date("Y-m-d");
    $delta = date("w",strtotime($d)) - 1;
    if ($delta <0) $delta = 6;
    $FromMes=date("m", mktime(0,0,0,date('m'), date('d')-$delta, date('Y') ));
}

if (isset($_POST['FromDia'])) {
    $FromDia=$_POST['FromDia'];
} else {
    $d=date("Y-m-d");
    $delta = date("w",strtotime($d)) - 1;
    if ($delta <0) $delta = 6;
    $FromDia=date("d", mktime(0,0,0,date('m'), date('d')-$delta, date('Y') ));
}

if (isset($_POST['ToYear'])) {
    $ToYear=$_POST['ToYear'];
} else {
    $d=date("Y-m-d");
    $delta = date("w",strtotime($d)) - 1;
    if ($delta <0) $delta = 6-$delta;	
    $ToYear=date("Y", mktime(0,0,0,date('m'), date('d')+$delta, date('Y') ));
}

if (isset($_POST['ToMes'])) {
    $ToMes=$_POST['ToMes'];
} else {
    $d=date("Y-m-d");
    $delta = date("w",strtotime($d)) - 1;
    if ($delta <0) $delta = 6-$delta;	
    $ToMes=date("m", mktime(0,0,0,date('m'), date('d')+$delta, date('Y') ));
}
if (isset($_POST['ToDia'])) {
    $ToDia=$_POST['ToDia'];
} else {
    $d=date("Y-m-d");
    $delta = date("w",strtotime($d)) - 1;
    if ($delta <0) $delta = 6-$delta;	
    $ToDia=date("d", mktime(0,0,0,date('m'), date('d')+$delta, date('Y') ));
}

if (isset($_POST['PromesaYear'])) {
    $PromesaYear=$_POST['PromesaYear'];
} else {
    $d = date("Y-m-d");
    $newdate = strtotime ( '+1 week' , strtotime ( $d ) ) ;
    $PromesaYear = date ('Y', $newdate);
}

if (isset($_POST['PromesaMes'])) {
    $PromesaMes=$_POST['PromesaMes'];
} else {
    $d = date("Y-m-d");
    $newdate = strtotime ( '+1 week' , strtotime ( $d ) ) ;
    $PromesaMes = date ('m', $newdate);
}

if (isset($_POST['PromesaDia'])) {
	$PromesaDia=$_POST['PromesaDia'];
} else {
	$d = date("Y-m-d");
	$newdate = strtotime ( '+1 week' , strtotime ( $d ) ) ;
	$PromesaDia = date ('d', $newdate);
}

if(isset($_POST['FechaAuto'])){
    $fechaauto = $_POST['FechaAuto'];
}elseif(isset($_GET['FechaAuto'])){
    $fechaauto = $_GET['FechaAuto'];
}else{
    $fechaauto = date("Y-m-d");
}


if(isset($_POST['FechaPago'])){
    $fechapago = $_POST['FechaPago'];
}elseif(isset($_GET['FechaPago'])){
    $fechapago = $_GET['FechaPago'];
}else{
    $fechapago = date("Y-m-d");//
}

if(Havepermission($_SESSION['UserID'], 1479, $db) == 1 or Havepermission($_SESSION['UserID'], 1480, $db)){
    if(isset($_POST['FechaAuto'])){//
        $fechacheque_contable = $_POST['FechaAuto'];
        $fechachequeperiod = rtrim($chkFromDia).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromYear);
        //$fechacheque = rtrim($chkFromYear) . "-" . rtrim($chkFromMes) . "-" . rtrim($chkFromDia);
        //$fechacheque_contable= rtrim($chkFromYear).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromDia);sadsdsdsad
    }else{
        $fechachequeperiod = rtrim($chkFromDia).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromYear);
        //$fechacheque = rtrim($chkFromYear) . "-" . rtrim($chkFromMes) . "-" . rtrim($chkFromDia);
        $fechacheque_contable= rtrim($chkFromYear).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromDia);
    }
    
    if(isset($_POST['FechaPago'])){
            
        $fechachequeperiod = rtrim($chkFromDia).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromYear);
        $fechacheque = $_POST['FechaPago'];
        //$fechacheque_contable= rtrim($chkFromYear).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromDia);
    }else{
        $fechachequeperiod = rtrim($chkFromDia).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromYear);
        $fechacheque = rtrim($chkFromYear) . "-" . rtrim($chkFromMes) . "-" . rtrim($chkFromDia);
       // $fechacheque_contable= rtrim($chkFromYear).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromDia);
    }
}else{
    $fechachequeperiod = rtrim($chkFromDia).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromYear);
    $fechacheque = rtrim($chkFromYear) . "-" . rtrim($chkFromMes) . "-" . rtrim($chkFromDia);
    $fechacheque_contable= rtrim($chkFromYear).'/'.rtrim($chkFromMes).'/'.rtrim($chkFromDia);
}




$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
$fechafin= rtrim($ToYear). '-'.rtrim($ToMes).'-'.rtrim($ToDia);
$fechaprom= rtrim($PromesaYear).'-'.rtrim($PromesaMes).'-'.rtrim($PromesaDia);

$fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
$fechafinc=mktime(23,59,59,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
$fechapromc=mktime(0,0,0,rtrim($PromesaMes),rtrim($PromesaDia),rtrim($PromesaYear));

$fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);
$fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
$fechaprom= rtrim($PromesaYear).'-'.add_ceros(rtrim($PromesaMes),2).'-'.add_ceros(rtrim($PromesaDia),2);

$estatusValue['all'] = -1;
$estatusValue['pend'] = 0;
$estatusValue['prog'] = 1;
$estatusValue['auth'] = 2;
$estatusValue['exec'] = 3;

// permisos
$pfechaauto = Havepermission($_SESSION['UserID'], 1479, $db);
$pfechapago = Havepermission($_SESSION['UserID'], 1480, $db);

if(isset($_POST['btnRetenciones'])){
   $_POST['ReportePantalla'] = _('Despliega Cuentas X Pagar'); 
}

if(isset($_SESSION['TruncarDigitos']))
{
	$digitos=$_SESSION['TruncarDigitos'];
}else{
	$digitos=4;
}

if (!isset($_POST['PrintEXCEL'])) {
	
	$title = _('Autorizaci&oacute;n y Programaci&oacute;n de Cuentas por Pagar');
	include('includes/header.inc');
	$debug = 1;
	// Tabla para el titulo de la pagina
	echo "<table align='center' border=0 width=100% nowrap>";
	echo '	<tr>
    		<td class="fecha_titulo">
    			<img src="images/aut_prog_30.png" alt="">' . $title . '<br>
    		</td>';
	echo '	</tr>
	  </table><br>';
// 	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br></p>';

	$stringFind = "";
	
	if (isset($_POST['ProcesaPromesa'])) {
            if (isset($_POST['selMovimiento'])) 
            {
                for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {

                    $umovto = $_POST['selMovimiento'][$i];

                    //String con todas las id seleccionadas para volver a marcarlas como
                    //seleccionadas una vez terminadas las operaciones con ellas !!!
                    $stringFind = $stringFind . '-' . $umovto;

                        if ($fechaprom < date('Y-m-d')) {
                                prnMsg(_('Fecha promesa no puede ser menor que hoy !') .  ' ' . DB_error_msg($db),'error');
                        } else {

                                $sql = "update supptrans
                                                set promisedate = '".$fechaprom."'";
                                $sql = $sql . " where id = ". $umovto ."";
                                $resul = DB_query($sql,$db);
                        }
                }
            }
	} 
        elseif (isset($_POST['ProcesaAutorizacion'])) 
        {
            if (isset($_POST['selMovimiento']))
            {
                for ($i=0; $i<=count($_POST['selMovimiento'])-1; $i++)
                {
                    $umovto = $_POST['selMovimiento'][$i];
                    $unidad_negocio= $_POST['tagref'][$umovto];
                    $estatus_movto= $_POST['status'][$umovto];
                                        
                    //String con todas las id seleccionadas para volver a marcarlas como
                    //seleccionadas una vez terminadas las operaciones con ellas !!!
                    $stringFind = $stringFind . '-' . $umovto;
                    
                    

                    if ($_POST['valorAutorizacion'] == '*') {
                        prnMsg(_('No se selecciono tipo de autorizacion !') .  ' ' . DB_error_msg($db),'error');
                    } 
                    else 
                    {
                         if (strpos($_SESSION['DefaultDateFormat'],'/')) {
                            $flag = "/";
                        } elseif (strpos ($_SESSION['DefaultDateFormat'],'-')) {
                            $flag = "-";
                        } elseif (strpos ($_SESSION['DefaultDateFormat'],'.')) {
                            $flag = ".";
                        }
                        $diafecha = substr($_POST['FechaAuto'], 8, 2);
                        $mesfecha = substr($_POST['FechaAuto'], 5, 2);
                        $aniofecha = substr($_POST['FechaAuto'], 0, 4);//
                        $trandatef = $diafecha.$flag.$mesfecha.$flag.$aniofecha;
                        $PeriodNo = GetPeriod($trandatef, $db, $unidad_negocio);  // obtiene el periodo fiscal
                        
                        //$importe= $_POST['saldo'][$umovto] / $_POST['rate'][$umovto];
                        
                        $consulta= "SELECT stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
                                                ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
                                                    JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
                                                    WHERE stockid=stk),2) 
                                                AS impuesto,
                                                 supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, purchorderdetails.clavepresupuestal
                                                FROM supptrans
                                                INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
                                                INNER JOIN purchorderdetails ON supptransdetails.orderno= purchorderdetails.orderno AND supptransdetails.stockid= purchorderdetails.itemcode
                                                WHERE id in ('".$umovto."')";
                        $resultado= DB_query($consulta, $db);
                        
                        if ($estatus_movto == 2 && $estatusValue[$_POST['valorAutorizacion']] < 2){
                            $num_transaccion = GetNextTransNo(91, $db);  // obtiene un numero de transaccion
                            $transno_movto= $_POST['transno'][$umovto];
                            
                            while ($registro= DB_fetch_array($resultado)) {
//                                $importe= ($registro["price"] * $registro["qty"]) * (1 + $registro["porcentaje_iva"]);
                                $importe= ($registro["precio2"] * $registro["qty"]) * (1 + $registro["impuesto"]);
                                $importe=truncateFloat($importe, $digitos);
                                GeneraMovimientoContablePresupuesto(91, "EJERCIDO", "DEVENGADO", $num_transaccion, $PeriodNo, $importe, $unidad_negocio, $fechacheque_contable, $registro["clavepresupuestal"], $transno_movto, $db);//$fechacheque_contable
                            }
                        }
                            
                        // validar el estatus de autorizado
                        if ($estatusValue[$_POST['valorAutorizacion']] == 2){
                            $num_transaccion = GetNextTransNo(91, $db);  // obtiene un numero de transaccion                                                
                            
                            while ($registro= DB_fetch_array($resultado)) {
                                $importe= ($registro["precio2"] * $registro["qty"]) * (1 + $registro["impuesto"]);
                                $importe=truncateFloat($importe, $digitos);
                                GeneraMovimientoContablePresupuesto(91, "DEVENGADO", "EJERCIDO", $num_transaccion, $PeriodNo, $importe, $unidad_negocio, $fechacheque_contable, $registro["clavepresupuestal"], $registro["transno"], $db);//$fechacheque_contable
                                //Insert_Gltrans(91, $num_transaccion, $fechachequeperiod, $PeriodNo, $Account, $Narrative, $Tag, $Userid, $Rate, $sql);
                            }                            
                        }
                        
                        $sql = "UPDATE supptrans
                                SET hold = ".$estatusValue[$_POST['valorAutorizacion']]." 
                                WHERE id = '". $umovto ."'";
                        
                        $resul = DB_query($sql,$db);
                    }
                } 
            } else {
                prnMsg("Favor de seleccionar un elemento para cambiar de Estatus...", "info");
            }
	} 
        elseif (isset($_POST['GeneraCheque'])) 
        {
         
		if (isset($_POST['selMovimiento'])) 
                {
                    if (strpos($_SESSION['DefaultDateFormat'],'/')) {
                            $flag = "/";
                        } elseif (strpos ($_SESSION['DefaultDateFormat'],'-')) {
                            $flag = "-";
                        } elseif (strpos ($_SESSION['DefaultDateFormat'],'.')) {
                            $flag = ".";
                        }
                        $diafecha = substr($_POST['FechaPago'], 8, 2);
                        $mesfecha = substr($_POST['FechaPago'], 5, 2);
                        $aniofecha = substr($_POST['FechaPago'], 0, 4);
                        $trandatef = $diafecha.$flag.$mesfecha.$flag.$aniofecha;
                    $PeriodNo = GetPeriod($trandatef,$db); 
                    $transnos[] = $TransNo;

                    if ($_POST['tipocambio'] == 'No'){
                        $moneda = 'MXN';
                    }elseif($_POST['tipocambio'] == 'Yes'){
                        $moneda = 'USD';
                    };

                    $bankaccount = $_POST['BankAccount'];
                    $ChequeNum = $_POST['ChequeNum'];
                    $Tipopago = $_POST['Tipopago'];

                    /*Get the bank account currency and set that too */
                    $ErrMsg = _('No pude obtener la moneda de la cuenta del banco seleccionada');
                    $result = DB_query("SELECT currcode FROM bankaccounts WHERE accountcode = '" . $bankaccount . "'",$db,$ErrMsg);
                    $myrow = DB_fetch_row($result);
                    $monedabanco = $myrow[0];
                    //echo "<br>monedabanco: " . $monedabanco;

                    /*********FUNCIONES PARA OBTENER RATE****/
                    /*Get the exchange rate between the functional currency and the payment currency*/
                    $result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $moneda . "'",$db);
                    //echo "<br>" . "SELECT rate FROM currencies WHERE currabrev='" . $moneda . "'";
                    $myrow = DB_fetch_row($result);
                    $tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency

                    $_POST['FunctionalExRate'] = 1;

                    if ($moneda == $monedabanco){
                            $_POST['ExRate']=1;
                            $SuggestedExRate=1;
                    }
                    if ($monedabanco==$_SESSION['CompanyRecord']['currencydefault']){
                            $_POST['FunctionalExRate']=1;
                            $SuggestedFunctionalExRate =1;
                            $SuggestedExRate = $tableExRate;
                    } else {
                            /*Get suggested FunctionalExRate */
                            //echo "<br>1.- " . "SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'";
                            $result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'",$db);
                            $myrow = DB_fetch_row($result);
                            $SuggestedFunctionalExRate = $myrow[0];

                            /*Get the exchange rate between the functional currency and the payment currency*/
                            //echo "<br>2.- " . "select rate FROM currencies WHERE currabrev='" . $moneda . "'";
                            $result = DB_query("select rate FROM currencies WHERE currabrev='" . $moneda . "'",$db);
                            $myrow = DB_fetch_row($result);
                            $tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
                            /*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
                            $SuggestedExRate = $tableExRate/$SuggestedFunctionalExRate;
                    }

                    if ($monedabanco != $moneda AND isset($monedabanco)){
                        if ($_POST['ExRate']==1 AND isset($SuggestedExRate)){
                            $_POST['ExRate'] = $SuggestedExRate;
                        }
                    }

                    if ($monedabanco != $_SESSION['CompanyRecord']['currencydefault'] AND isset($monedabanco)){
                        if ($_POST['FunctionalExRate']==1 AND isset($SuggestedFunctionalExRate)){
                            $_POST['FunctionalExRate'] = $SuggestedFunctionalExRate;
                        }
                    }

                    //echo "<br>ExRate:" . $_POST['ExRate'];
                    //echo "<br>FunctionalExRate:" . $_POST['FunctionalExRate'];
                    //********************************************
                    /*
                    echo "<pre>";
                    print_r($_POST['selMovimiento']);
                    print_r($_POST['status']);
                    die();
                    */
                    /*
                    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {
                            $umovto = $_POST['selMovimiento'][$i];
                            $status = $_POST['status'][$umovto];

                            if ($status==2){
                                    $saldo = $_POST['saldo'][$umovto];
                                    $TotalAmount += $saldo;
                                    $TotalDescuento = 0;
                                    $sumaRetenciones = 0;
                                    //$TotalDescuento += $PaymentItem->Discountsupp;
                                    //$sumaRetenciones += ($PaymentItem->RetencionIVA);



                                    //if ($_POST['valorAutorizacion'] == '*') {
                                    //	prnMsg(_('No se selecciono tipo de autorizacion !') .  ' ' . DB_error_msg($db),'error');
                                    //} else {
                                            $sql = "update supptrans
                                                            set hold = " . $estatusValue[$_POST['valorAutorizacion']] ."";
                                            $sql = $sql . " where id = ". $umovto ."";
                                            //$resul = DB_query($sql,$db);
                                            //echo "<br>" . $sql;
                                    //}
                            }

                    }
                    */	
                    $inisupplierid = "";
                    $initransno = "";
                    $prevdiffonexch = 0;
                    $InputError = 0;
                    //echo "<br>" . count($_POST['selMovimiento']); tagsxbankaccounts.tagref
                    $sqlaccoutnt = "SELECT tagsxbankaccounts.tagref
                                    FROM bankaccounts, chartmaster, tagsxbankaccounts
                                    WHERE bankaccounts.accountcode=chartmaster.accountcode 
                                    AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
                                    AND bankaccounts.accountcode = '".$_POST['BankAccount']."'";
                    $resultaccount = DB_query($sqlaccoutnt, $db);
                    $tagrefcheque = array();
                    while($myrowaccoutn = DB_fetch_array($resultaccount)){
                        $tagrefcheque[] = $myrowaccoutn['tagref'];
                    }
                    $conte=0;
                    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) 
                    {
                        $umovto = $_POST['selMovimiento'][$i];
                        $tagref = $_POST['tagref'][$umovto];
                        $folioreference = $_POST['foliorefe'][$umovto];
                        if(1==0){
                            $InputError = 1;
                            
                            prnMsg("La unidad de negocio de la factura con el folio".$folioreference." no es del mismo tipo de gasto de la chequera favor de verificarlo". "error");
                        }
                        if($_POST['status'][$umovto] != 2 && $conte==0)
                        {
                            $InputError = 1;
                            prnMsg("Para procesar es necesario que esten en el estatus de 'Autorizado'.". "error");
                           $conte=1 ;
                        }
                        if(empty($_POST['tagref2']) or $_POST['tagref2']=='*'){
                            $InputError = 1;
                            prnMsg("Para procesar ".$_POST['selMovimiento'][$i]." es necesario elegir una unidad de negocio antes de continuar.","error");
                        }
                    }
                    
                    
                    //Validar que el número de cheque no este en el sistema
                    $_POST['numchequeuser']=trim($_POST['numchequeuser']);
                    if (!empty($_POST['numchequeuser']) and $_POST['Tipopago']==='Cheque') {
                            $SQL="SELECT * from banktrans where chequeno='".$_POST['numchequeuser']."'";
                            $result=  DB_query($SQL, $db);
                            if (DB_num_rows($result)>0) {
                                $inputError=1;
                                prnMsg("El numero de chueque utilizado ya esta en uso, elija otro antes de continuar. ","error");
                            }

                        } 
                                
                    // =(^.^)= ERCH
                    if($InputError == 0){
                        if(isset($_POST['UnificarPagoProveedoresDiversos']))
                        {$unificarPD=true;}
                        else
                        {$unificarPD=false;}    
                        
                        $arrayverificadoslog;
                        $idcheque='';
                        $idchequeproveedor;
                        $suplierrepetidos;
                        $provee='';
                        $descrip = $_POST['UnificarPagodescripcion'];
                        if($unificarPD)
                        {
                                    $SQL="SELECT chartsupplierstype.gl_unificarpagos as cuenta,suppliers.suppname  as nombre
                                    FROM suppliers 
                                    INNER JOIN supplierstype 
                                                    ON suppliers.typeid = supplierstype.typeid 
                                    INNER JOIN chartsupplierstype 
                                                    ON chartsupplierstype.typedebtorid = supplierstype.typeid
                                    WHERE suppliers.supplierid ='".$_POST['UnificarPagoselect']."'";
                                    $resultadoe=  DB_query($SQL, $db);
                                    $rowe = DB_fetch_array($resultadoe);
                                    if(empty($rowe['cuenta']))
                                    {
                                        prnMsg(_('Configura la cuentas para el proveedor puente!'),'error');
                                        exit();
                                    } 
                                    if(empty($_POST['tagref']) or $_POST['tagref']=='*')
                                    {
                                        prnMsg(_('Seleccionar una Unidad de Negocio para el pago!'),'error');
                                        exit();  
                                    }
                            $mas=0; 
                        }
                        else
                        { 
                            $mas= 1;
                        }
                       $totaldocumentos = count($_POST['selMovimiento']);
                       $totaldocumentos = $totaldocumentos-$mas;
                       $idFacturaNueva='';
                       for ($i=0;$i <=$totaldocumentos ; $i++) {
                           $saldonotacargo=0;
                            if($unificarPD ){
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
                                   
                                    $SQL = "select `transno`,"
                                            . " `tagref`, "
                                            . "`type`,"
                                            . " `supplierno`, "
                                            . "`suppreference`, "
                                            . "`trandate`, "
                                            . "`origtrandate`, "
                                            . "`duedate`, "
                                            . "`promisedate`, "
                                            . "`settled`, "
                                            . "`rate`, "
                                            . "`ovamount`, "
                                            . "`ovgst`, "
                                            . "`diffonexch`, "
                                            . "`alloc`, "
                                            . "`transtext`, "
                                            . "`hold`, "
                                            . "`id`, "
                                            . "`folio`, "
                                            . "`ref1`, "
                                            . "`ref2`, "
                                            . "`currcode`, "
                                            . "`sent`, "
                                            . "`activo`,
                                            `originalinvoice`,
                                            `u_typeoperation`, `retencionIVA`, `retencionISR`, `retencionCedular`, `retencionFletes`, `retencionComisiones`, `retencionArrendamiento`, `retencionIVAArrendamiento`, `reffiscal` from supptrans where id='".$id."'";
                                    $result=  DB_query($SQL, $db);
                                    $row= DB_fetch_array($result);
                                    $_POST['selMovimiento'][$i]=$row['id'];
                            $_POST['tagref'][$row['id']]=$row['tagref'];
                            $_POST['status'][$row['id']] = 2;
                            $_POST['saldo'][$row['id']]=$saldosum;
                                    //$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
                                    $_POST['supplierid'][$row['id']]=$row['supplierno'];
                                    $_POST['rate'][$row['id']]=$row['rate'];
                                    $_POST['idfactura'][$row['id']]=$row['id'];
                                    $_POST['diffonexch'][$row['id']]=$row['diffonexch'];
                                $provee=$row['supplierno'];
                                } 
                                
                            }else{$type=22;}
                           
                            $table='gltrans';
                            $umovto = $_POST['selMovimiento'][$i];
                            $umovtones[]=$umovto;
                            $tagref = $_POST['tagref'][$umovto];
                          
                            $status = $_POST['status'][$umovto];
                            $saldo = $_POST['saldo'][$umovto];
                                    //$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
                                    $supplierid = $_POST['supplierid'][$umovto];
                                    $ratefactura = $_POST['rate'][$umovto];
                                    $idfactura = $_POST['idfactura'][$umovto];
                                    if($unificarPD && $totaldocumentos==$i){  
                                    $idfactura = $umovto;
                                    }
                                    $diffonexch = $_POST['diffonexch'][$umovto];
                             
                            $procesar=true;
                            $procesarobtenernum=true;
                            $procesarprimero=true;
                            $saldosum=0;
                            $value=$provee;
                            
                             
                            
                            if(isset($_POST['UnificarPago']))
                            {
                                unset($arrayverificados);
                                
                                for ($j=0;$j <= count($_POST['selMovimiento'])-1; $j++) {
                                    if($_POST['supplierid'][$umovto]==$_POST['supplierid'][$_POST['selMovimiento'][$j]]  and $tagref == $_POST['tagref'][$_POST['selMovimiento'][$j]])//and $_POST['tagref'][$umovto]==$_POST['tagref'][$_POST['selMovimiento'][$j]] )
                                    {
                                       $arrayverificados[] = $_POST['selMovimiento'][$j]; 
                                       $saldosum +=$_POST['saldo'][$_POST['selMovimiento'][$j]];
                                    }
                                }
                                if(count($arrayverificados)>1)
                                {
                                $procesar=false;
                                //$table='gltrans_polisa';
                                $table='gltrans'; 
                                $value=$supplierid;
                                 if(isset($idchequeproveedor[$supplierid]) and $idchequeproveedor[$supplierid] != '')
                                        {
                                          $idcheque = $idchequeproveedor[$supplierid];
                                          $procesarobtenernum = false;
                                        }
                                }
                                   if(in_array($_POST['supplierid'][$umovto], $arrayverificadoslog))//no procesar si ya se genero un movimiento para este proveedor
                                   {
                                       $procesarprimero=false;
                                       
                                   }
                            }
                            
                            

            
                            if(true){  
                            if ($status==2)
                            {
                                     if($unificarPD AND $totaldocumentos==$i)
                                        {
                                         $type=22;
                                        }
                                    $inisupplierid = $supplierid;

                                    $saldofactura =  ($saldo / $ratefactura);                                    
                                   
                                   
                                     $SQL="SELECT  typeno
                                        FROM gltrans_polisa 
                                        WHERE supplier='".$supplierid."'
                                        and typeno order by typeno";
                                       $result=  DB_query($SQL, $db);
                                       if(DB_num_rows($result)>0 AND !$unificarPD)
                                       { 
                                         $row=  DB_fetch_array($result);
                                          $initransno = $row['typeno'];	
                                       }
                                       else
                                       {
                                          $TransNo = GetNextTransNo($type, $db);
                                          $initransno = $TransNo;
                                       }  
                                       
                                       
                                    $bankaccount = $_POST['BankAccount'];
                                    if ($Tipopago == 'Cheque'){
                                       $SQL="SELECT  chequeno
                                        FROM gltrans_polisa 
                                        WHERE supplier='".$supplierid."'
                                        and chequeno >0";
                                       $result=  DB_query($SQL, $db);
                                       if(DB_num_rows($result)>0)
                                       {
                                         $row=  DB_fetch_array($result);
                                         $ChequeNum = $row['chequeno'];	
                                       }
                                       else
                                       {
                                         $ChequeNum = GetNextChequeNo($_POST['BankAccount'], $db);  
                                       }  
                                    }else{
                                            $ChequeNum = $Tipopago;
                                    }
                                    //Si el campo de numero de cheque no esta vacio y el tipo de pago es cheque entonces seCambia el numero de cheque por el escrito.
                                    if  (!empty($_POST['numchequeuser']) && $_POST['Tipopago']=='Cheque') {
                                            $ChequeNum=$_POST['numchequeuser'];
                                    }
                                    //echo "<br>Cheque num: $ChequeNum";
                                    $narrative  = "Pago de factura";
                                    $Transtype = $type;
                                    /* CREAR UN REGISTRO DEL PAGO DE PROVEEDOR           */
                                    /* Create a SuppTrans entry for the supplier payment */
                                    $ratecheque = ($_POST['ExRate']/$_POST['FunctionalExRate']);
                                    $saldocheque = ($saldo / ($_POST['ExRate']/$_POST['FunctionalExRate']));
                                    if(!$procesar)
                                    {
                                        $saldocheque2=($saldosum / ($_POST['ExRate']/$_POST['FunctionalExRate']));
                                    }
                                    else
                                    {
                                        $saldocheque2=$saldocheque;
                                    }
                                   
                                    /* if(isset($_POST['UnificarPago']))
                                     {
                                        $umovto=  implode("','", $arrayverificados);
                                     }*/
                                    $consulta= "SELECT stockid AS stk,supptrans.id, supptrans.transno, supptrans.ovamount,(price*qty) AS precio2,
                                                ROUND((SELECT CASE WHEN taxvalue IS NULL THEN 0 ELSE taxvalue END AS taxvalue FROM stockmaster
                                                    JOIN taxcategories ON stockmaster.taxcatid=taxcategories.taxcatid
                                                    WHERE stockid=stk),2) 
                                                AS impuesto,
                                                 supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, purchorderdetails.clavepresupuestal
                                                FROM supptrans
                                                INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
                                                INNER JOIN purchorderdetails ON supptransdetails.orderno= purchorderdetails.orderno AND supptransdetails.stockid= purchorderdetails.itemcode
                                                WHERE id in ('".$umovto."')";
                                    echo '<br<<pre>'.$consulta;
                                    $resultado = DB_query($consulta, $db);                                    
                                   
                                    while ($registro = DB_fetch_array($resultado)){
                                        // Generacion del momento contable para el pago
                                        $importe = (($registro["precio2"] * $registro["qty"]) * (1 + $registro["impuesto"])) / $ratefactura;
                                        
                                        $importe=truncateFloat($importe, $digitos);
                                        if(!$unificarPD)
                                        {
                                            if($procesar)
                                            {
                                                //exit();
                                            GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagref, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db,false,'',$descrip);//$fechacheque_contable
                                            }
                                            else {
                                                //exit();
                                            GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagref, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db,true,$supplierid,$descrip);//$fechacheque_contable
                                            }
                                        }
                                        if($unificarPD AND $totaldocumentos>$i)
                                        {
                                         GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagref, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db,true,$_POST['UnificarPagoselect'],$descrip);//$fechacheque_contable
                                        }
                                    }
                                   
                                    if(!$procesar)
                                    {
                                        $saldosuma=$saldosum;
                                    }
                                    else
                                    {
                                        $saldosuma=$saldo;
                                    }
                                        
                                    $SQL = "INSERT INTO supptrans (transno,
                                            type,
                                            supplierno,
                                            trandate,
                                            suppreference,
                                            rate,
                                            ovamount,
                                            transtext,
                                            tagref,
                                            origtrandate,
                                            ref1,
                                            alloc,
                                            settled, ref2
                                            ) ";
                                    $SQL = $SQL . 'VALUES (' . $TransNo . ",
                                            '".$type."',	
                                            '" . $supplierid . "',
                                            '" . $fechacheque . "',
                                            '" . $ChequeNum . "',
                                            '" . ($_POST['ExRate']/$_POST['FunctionalExRate']) . "',
                                            " . (-1)*$saldosuma . ",
                                            '" . $narrative . "',
                                            " . $tagref . ",
                                            now(),
                                            '" . $ChequeNum . "',
                                            " . (-1)*$saldosuma . ",
                                            " . "1" . ",
                                            ".$umovto.")";
                                     
                                    $ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
                                    $DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
                                    //echo "<pre>SQL: " . $SQL;
                                    
                                
                                if($procesarprimero) 
                                {
                                    if(!in_array($supplierid, $suplierrepetidos))
                                    { 
                                      $suplierrepetidos[] = $supplierid; //almacenamos los proveedores que tienen registros repetidos
                                    }
                                   $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                   
                                }
                                
                                    $prevdiffonexch = $prevdiffonexch +  ($_POST['ExRate']/$_POST['FunctionalExRate']);
                                    if($procesarobtenernum and $procesarprimero)
                                    { 
                                       $idcheque = DB_Last_Insert_ID($db,'supptrans','id');
                                       $idchequeproveedor[$supplierid]=$idcheque;
                                    }
                                 $idchequess = DB_Last_Insert_ID($db,'supptrans','id');
                                 $idFacturaNueva=$idchequess;
                                    /* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
                                    /*Update the supplier master with the date and amount of the last payment made */
                                 
                                 /////// Agrega los registros a la tabla que relaciona los documentos
                                 if($unificarPD AND $totaldocumentos==$i)
                                    {$idfacpuente=DB_Last_Insert_ID($db, 'supptrans', 'id');
                                    $contador=0;
                                        foreach ($docs as $doc) {
                                            $SQLdocxdocs="SELECT type,transno FROM supptrans where id='".$umovtones[$contador]."' limit 1";
                                            $resultdocxdocs=  DB_query($SQLdocxdocs, $db);
                                            $rowsdocxdocs=  DB_fetch_array($resultdocxdocs);
                                            
                                            $SQLdoccheq="SELECT type,transno FROM supptrans where id='".$umovto."' limit 1";
                                            $resultdocche=  DB_query($SQLdoccheq, $db);
                                            $rowsdocche=  DB_fetch_array($resultdocche);
                                            //-- 
                                            $SQLdocxdoc="INSERT into suppdocpaydocs(notaid,notatype,notatransno,
                                                faciniid, facinitype,facinitransno,
                                                facpuenteid,facpuentetype,facpuentetransno,
                                                chequeid,chequetype,chequetransno)
                                                  VALUES('".$doc[2]."','".$doc[1]."','".$doc[0]."',"
                                                    . "'".$umovtones[$contador]."','".$rowsdocxdocs['type']."','".$rowsdocxdocs['transno']."',"
                                                    . "'". $umovto."','".$rowsdocche[0]."','".$rowsdocche[1]."',"
                                                    . "'". $idfacpuente ."','".$type."','".$TransNo."'"
                                                    . ")";
                                            $result=  DB_query($SQLdocxdoc, $db);
                                            $contador++;
                                        }
                                    }
                                $docs[]=array($TransNo,$type,  DB_Last_Insert_ID($db, 'supptrans', 'id'));
                                //////////////////////////////////////////////////////////////////////////
                                
                                    $SQL = "UPDATE suppliers SET
                                            lastpaiddate = '" . $fechacheque . "',
                                            lastpaid=" . $saldo ."
                                            WHERE suppliers.supplierid='" . $supplierid . "'";

                                    $ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
                                    $DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
                                    //echo "<pre>" . $SQL;
                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                                    $SQL = "INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto)
                                            VALUES ('" . $fechacheque . "', 
                                            " . $saldo . ', 
                                            ' . $idcheque . ', 
                                            ' . $idfactura . ')';

                                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  _('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' ._('could not be inserted because');
                                    $DbgMsg = _('The following SQL to insert the allocation record was used');
                                    $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                                    //echo "<pre>" . $SQL;
                                    //prnMsg(_('Inserto nueva aplicacion...'),'success');

                                    $SQL = 'UPDATE supptrans
                                            SET diffonexch=' . $diffonexch . ', 
                                                alloc = ' .  $saldo . ', 
                                                settled = ' . "1" . ' 
                                            WHERE id = ' . $idfactura;

                                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
                                    $DbgMsg = _('The following SQL to update the debtor transaction record was used');

                                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                                    //echo "<pre>" . $SQL;
/***/
                                    if ($_SESSION['CompanyRecord']['gllink_creditors']==1){

                                            //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
                                           //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
                                                if ($inisupplierid != ''){
                                                        $tipoproveedor = ExtractTypeSupplier($inisupplierid,$db);
                                                        $ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
                                                        if($unificarPD )
                                                        {
                                                            $tipoproveedorpuente = ExtractTypeSupplier($_POST['UnificarPagoselect'],$db);
                                                            $ctaxtipoproveedorpuente = SupplierAccount($tipoproveedorpuente,"gl_accountsreceivable",$db);
                                                        }
                                                }else{
                                                        $ctaxtipoproveedor = $_SESSION['CompanyRecord']['creditorsact'];
                                                }

                                            $narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . ($saldofactura);
                                           if($unificarPD && $totaldocumentos==$i)
                                           {
                                               $inisupplierid . "-" . "PAGO DE FACTURAS@" . ($saldofactura).' '.$facturasafectadastypetransno;
                                           }
//
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
                                                        descripcion
                                            		) VALUES (
                                                    '".$type."',
                                                    '" . $initransno . "',
                                                    '" . $fechacheque . "',
                                                    '" . $PeriodNo . "',
                                                    '" . $ctaxtipoproveedor . "',
                                                    '" . $narrative . "',
                                                    '" . $saldofactura . "',
                                                    '" . $tagref . "',
                                                    '" . $ChequeNum . "',
                                                     '" . $_SESSION['UserID'] . "',
                                                     now(),
                                                     '".$value."',
                                                     '".$descrip."'
                                                    		)"; 
                                            $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                            $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');					
                                            //echo "<pre>" . $SQL;
                                            if($unificarPD)
                                            {
                                                  if($totaldocumentos!=$i)
                                                  {
                                                   $bankaccount=$ctaxtipoproveedor;
                                                  }
                                                  $result = DB_query($SQL1,$db,$ErrMsg,$DbgMsg,true);
                                            }
                                            else
                                            {
                                                   $result = DB_query($SQL1,$db,$ErrMsg,$DbgMsg,true);
                                            }
                                            

                                            $narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura);	
                                            $totalimpuestosretencion = 0;
                                            if(isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> ""){
                                                $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivahonorarios'];
                                            }
                                            if(isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> ""){
                                                $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrhonorarios'];
                                            }
                                            if(isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> ""){
                                                $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivaarrendamiento'];
                                            }
                                            if(isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> ""){
                                                $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrarrendamiento'];
                                            }
                                            if(isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> ""){
                                                $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionfletes'];
                                            }
                                            if(isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> ""){
                                                $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencioncedular'];
                                            }
                                            
                                            $saldocheque = $saldocheque - $totalimpuestosretencion;
                                            $saldocheque2 = $saldocheque2 - $totalimpuestosretencion;
                                            
                                           // echo $saldocheque .'='.$saldocheque.'-'.$saldocheque;
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
                                                        cuentabanco,descripcion
                                            		) VALUES (
                                                    '".$type."',
                                                    '" . $initransno . "',
                                                    '" . $fechacheque . "',
                                                    '" . $PeriodNo . "',
                                                    '" . $bankaccount . "',
                                                    '" . $narrative . "',
                                                    '" . -1*($saldocheque). "',
                                                    '" . $tagref . "',
                                                    '" . $ChequeNum . "',
                                                    '" . $_SESSION['UserID'] . "',
                                                     now(),
                                                     '".$value."',
                                                         1 ,
                                                         '".$descrip."'
                                                    )";
                                            $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                            $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                                            //echo "<pre>" . $SQL;
                                            if($unificarPD)
                                            {
                                                  //if($totaldocumentos==$i)
                                                  {
                                                   $result = DB_query($SQL2,$db,$ErrMsg,$DbgMsg,true);
                                                  }
                                            }
                                            else
                                            {
                                                   $result = DB_query($SQL2,$db,$ErrMsg,$DbgMsg,true);
                                            }
                                            /*********INICIO PERDIDA CAMBIARIA***************/
                                            $utilidadperdida = $saldofactura - $saldocheque;
                                            $saldofactura2 = $saldofactura - $totalimpuestosretencion;
                                            if($saldocheque!=$saldofactura2){
                                                if (abs($utilidadperdida) > .1)
                                            {
                                                    if ($utilidadperdida < 0)
                                                    {
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
                                                            $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, True);


                                                    }else{
                                                        
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
                                                            $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, True);
                                                    }
                                            }
                                            /********FIN PERDIDA CAMBIARIA***************/
                                            }
                                            
                                            
                                            
                                         
                                            


                                            /**************************************************/
                                            /*MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
                                            $saldo = $_POST['saldo'][$umovto];
                                            $taxrate = .16;

                                            $CreditorTotal = ($saldo/$_POST['ExRate'])/$_POST['FunctionalExRate'];

                                            $SQL = 'select * from taxauthorities where taxid=1';
                                            $result2 = DB_query($SQL,$db);
                                            if ($TaxAccs = DB_fetch_array($result2)){
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
                                            while($myrowfact = DB_fetch_array($resultfact)){
                                                $foliorefe = $myrowfact['suppreference'];
                                                $typerefe = $myrowfact['type'];
                                                $transorefe = $myrowfact['type'];
                                            }
                                            
                                              $narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura)."folio factura ".$foliorefe." fologierp".$typerefe." ".$transorefe;	  
                                            /* Movimiento Contable de Retencion Honorarios iva */
                                            if(isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> ""){
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
                                                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                            }
                                            
                                            /* Movimiento Contable de Retencion Honorarios ISR */
                                            if(isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> ""){
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
                                                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                               
                                            }
                                            
                                            /* Movimiento Contable de Retencion Arrendamiento IVA */
                                            if(isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> ""){
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
                                                                    '".$value."','".$descrip."'	)";
                                                $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                                $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                                                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                                
                                            }
                                            
                                            /* Movimiento Contable de Retencion Arrendamiento ISR */
                                            if(isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> ""){
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
                                                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                                
                                            }
                                            
                                             /* Movimiento Contable de Retencion fletes */
                                            if(isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> ""){
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
                                                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                                
                                            }
                                            
                                            /* Movimiento Contable de Retencion cedular */
                                            if(isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> ""){
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
                                                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                                
                                            }

                                                    $SQL= "SELECT suppname
                                                    FROM suppliers
                                                    WHERE supplierid='" . $supplierid . "'";
                                                    $Result = DB_query($SQL, $db);
                                                    if (DB_num_rows($Result)==0){
                                                            prnMsg( _('El codigo de Proveedor con el que esta pagina fue llamada, no existe en base de datos de Proveedores') . '. ' . _('Si esta pagina es llamada desde la pagina de Proveedores, esto garantiza que el proveedor existe!'),'warn');
                                                            include('includes/footer.inc');
                                                            exit;
                                                    } else {
                                                            /*CODIGO DE PROVEEDOR VALIDO*/
                                                            $myrow = DB_fetch_array($Result);
                                                            $Beneficiario = $myrow['suppname'];

                                                    }



                                            //if(empty($transnos) == FALSE) {
                                            //	$Beneficiario = $Beneficiario . '@' . implode('|', $transnos);	
                                            //} else {
                                                    //$Beneficiario = "Beneficiario";
                                            //}
                                            $narrative = $supplierid . "@" . (($saldo) * (-1));
                                        
                                            $SQL="INSERT INTO banktrans (transno,
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
                                                            chequeno) ";
                                            $SQL= $SQL . "VALUES ('" . $initransno . "',
                                                    '" . $Transtype . "',
                                                   '" . $bankaccount . "',
                                                    '" . $narrative . "',
                                                    '" . $_POST['ExRate'] . "' ,
                                                    '" . $_POST['FunctionalExRate'] . "',
                                                    '".$fechacheque."',
                                                    '" . $Tipopago. "',";
                                            $SQL .=	($saldocheque2) * (-1) . ",
                                                    '" . $moneda . "',
                                                    '" . $tagref . "',
                                                    '" . $Beneficiario . "',
                                                    '" . $ChequeNum . "'
                                            )";
                                            $ErrMsg = _('No pude insertar la transaccion bancaria porque');
                                            $DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');
                                            if($unificarPD)
                                            {
                                                if($totaldocumentos==$i)
                                                  {
                                                   $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                                  }
                                            }
                                            else
                                            {
                                                if($procesarprimero)
                                               {
                                                   $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                               }
                                            }
                                            //echo "<br>" . $SQL;
                                    }

                                    /***** INICIO IMPRESION DE CHEQUE******************/
                                    prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('al proveedor') . ' ' . $Beneficiario . ' ' . _('ha sido exitosamente procesado'),'success');

                                    //$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);
                                    //$lasttag=$_POST['tag'];


                                    $liga = GetUrlToPrint($tagref,$Transtype,$db);

                                    /* BUSCA SI CHEQUERA UTILIZA UN FORMATO ESPECIAL DE IMPRESION */
                                    $sql="select *
                                            FROM bankaccounts
                                            WHERE accountcode='" . $bankaccount . "'";
                                    $Result = DB_query($sql, $db);
                                    $myrow = DB_fetch_array($Result);
                                    $pdfprefix=$myrow['pdfprefix'];
                                    if ($pdfprefix == null)
                                            $pdfprefix = "";//

                                    /*Set up a newy in case user wishes to enter another */
                                    
                                    //if($_SESSION['subirxmlprov'] == 1){
					//$liga2="SubirXMLProveedor.php?debtorno=".$_SESSION['SuppTrans']->SupplierID."&propietarioid=".$_SESSION['SuppTrans']->SupplierID."&NoOrden=".$SuppTransID."&tipopropietarioid=6&muetraarchivos=0";
					//echo "<br><div class='centre'><a TARGET='_blank' href='" . $liga2 . "'>" . _('SubirXML') . "</a></div>";
				//}
                                              //actualiza estatus del documento a Ejecutado
                                    $sql = "UPDATE supptrans
                                            SET hold = " . $estatusValue['exec'] ."
                                            WHERE id = ". $umovto ."";
                                    $resul = DB_query($sql,$db);
                        

                                    /***** FIN IMPRESION DE CHEQUE *******************/
/***/

                        // Agregar aqui las opciones finales del proceso or  ($unificarPD and $totaldocumentos==$i)
                                   
                        if($procesarprimero or  ($unificarPD and $totaldocumentos==$i))
                        {
                        echo "<br>";                
                       echo '<table cellpadding="0" cellspacing="0" width="50%" border="1" bordercolor="lightgray">';
                           echo '<tr>';
                               echo '<td class="titulos_principales">'._("Pago").'</td>';
                               echo '<td class="titulos_principales">'._("Proveedor").'</td>';
                               if($permisomostrar == 1)
                               {
                               echo '<td class="titulos_principales">'._("Formato").'</td>';
                               echo '<td class="titulos_principales">'._("Cheque").'</td>';
                               }
                               echo '<td class="titulos_principales">'._("Poliza").'</td>';
                               echo '<td class="titulos_principales">'._("Acciones Siguientes").'</td>';
                            echo '</tr>';
                            echo '<tr>'; 
                               echo '<td class="numero_normal">'.$TransNo.'</td>';
                               //Busqueda de proveedor
                                    $SQLseasupp="SELECT suppname FROM suppliers WHERE supplierid='".$supplierid."';";
                                    $rowseasupp=  DB_fetch_array(DB_query($SQLseasupp, $db));
                                    $suppliername=$rowseasupp['suppname'];
                                    echo '<td class="texto_normal2" nowrap>'.$suppliername."</td>";
                                    if($permisomostrar == 1)
                               {
                                    //Formato de cheque CON movimientos contables
                                    $link="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $Transtype. "&TransNo=" . $initransno ;
                                    $liga='<a  target="_blank" href="'.$link.'">'.'<img src="images/imprimir_25.png" title="' . _('FORMATO PRE-IMPRESO CON DETALLE CONTABLE')
                                    . '" alt="">' . ' ' ._('').'</a>'; 
                                    echo '<td class="numero_normal">'.$liga.'</td>';
                                    
                                    //Formato de cheque sin movimientos contables
                                    $link="PDFCheque.php?type=" . $Transtype. "&TransNo=" . $initransno ;
                                    $liga='<a  target="_blank" href="'.$link.'">'.'<img src="images/imprimir_25.png" title="' . _('FORMATO PRE-IMPRESO')
                                    . '" alt="">' . ' ' ._('').'</a>'; 
                                    echo '<td class="numero_normal">'.$liga.'</td>';
                               }
                                    //Poliza
                                    $liga="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=".$Transtype. "&TransNo=" .$initransno. "&periodo=". "&trandate=";
                                    echo "<td class='numero_normal'><a TARGET='_blank' href='" . $liga . "'>" . _('') . "<img src='images/imprimir_25.png' title='" . _('POLIZA') . "' alt=''></a></td>";
                                    echo '</tr>';
                        }  

                        }else{
                                    prnMsg(_('Debe seleccionar un movimiento Autorizado') .  ' ' . $_POST['selMovimiento'][$i],'error');	
                            }
                            }
                            if(isset($_POST['UnificarPago']))
                            {
                                $arrayverificadoslog[]=$_POST['supplierid'][$umovto];	
                            }
			}
                        if(isset($_POST['UnificarPagoProveedoresDiversos']))
                        {
                            $SQL = "select * from supptrans where id='".$idFacturaNueva."'";
                          
                            $result=  DB_query($SQL, $db);
                                    $row= DB_fetch_array($result);
                            $SQL="INSERT INTO `gltrans` ( `type`, `typeno`, `chequeno`, `trandate`, `periodno`, `account`, `narrative`, `amount`, `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`, `dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco)
                                    SELECT  '".$row['type']."', '".$row['transno']."', `chequeno`, `trandate`, `periodno`, `account`, `narrative`,`amount`, `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`,`dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco
                                    FROM gltrans_polisa WHERE supplier='".$_POST['UnificarPagoselect']."'";
                             DB_query($SQL, $db); 
                             $SQL="DELETE FROM `gltrans_polisa` WHERE supplier='".$_POST['UnificarPagoselect']."'";
                             DB_query($SQL, $db);   
                        }
                        else
                          {//Actualizacion gltrans para proceso unificado
                             $SQL="Select * FROM `gltrans_polisa`";
                             $result=  DB_query($SQL, $db);
                             if(DB_num_rows($result)>0)
                             {
                             $SQL=" INSERT INTO `gltrans` ( `type`, `typeno`, `chequeno`, `trandate`, `periodno`, `account`, `narrative`, `amount`, `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`, `dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco)
                                    SELECT  `type`, `typeno`, `chequeno`, `trandate`, `periodno`, `account`, `narrative`,`amount`, `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`,`dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco
                                    FROM gltrans_polisa";
                            DB_query($SQL, $db); 
                           
                            $SQL="DELETE FROM `gltrans_polisa`";
                            DB_query($SQL, $db); 
                            }
                        }
                        
                    }
                    
                    
                    
                    
                    //este proceso se corre cuando el pago es a proveedores diversos =(^.^)= 
                    if($InputError == 20 and isset($_POST['UnificarPagoProveedoresDiversos'])){
                        if(1==0)
                        {
                            
                            if(isset($_POST['UnificarPagodescripcion']) and isset($_POST['UnificarPagoselect']))
                            {
                                $SQL="SELECT chartsupplierstype.gl_unificarpagos as cuenta,suppliers.suppname  as nombre
                                FROM suppliers 
                                INNER JOIN supplierstype 
                                                ON suppliers.typeid = supplierstype.typeid 
                                INNER JOIN chartsupplierstype 
                                                ON chartsupplierstype.typedebtorid = supplierstype.typeid
                                WHERE suppliers.supplierid ='".$_POST['UnificarPagoselect']."'";
                                $resultadoe=  DB_query($SQL, $db);
                                $rowe = DB_fetch_array($resultadoe);
                                if(empty($rowe['cuenta']))
                                {
                                    prnMsg(_('Configura la cuentas para el proveedor puente!'),'error');
                                    exit();
                                } 
                                if(empty($_POST['tagref']) or $_POST['tagref']=='*')
                                {
                                    prnMsg(_('Seleccionar una Unidad de Negocio para el pago!'),'error');
                                    exit();  
                                }
                                    
                            $arrayverificadoslog;
                            $idcheque='';
                            $suplierrepetidos;
                            $generaridcheque=true; //variable para generar idde cheque una sola vez para todos las polisas
                            $generanumerodecheque=true;
                            $generatransno=true;
                            $crearMovimiento=true;
                            $procesarprimero=true;
                            $descrip = $_POST['UnificarPagodescripcion'];
                            $saldosum=0;
                            $type=34;
                            for ($j=0;$j <= count($_POST['selMovimiento'])-1; $j++) {
                                           $saldosum +=$_POST['saldo'][$_POST['selMovimiento'][$j]];
                            }
                            GenerarNotadeCargo($_POST['UnificarPagoProveedoresDiversos'], 'Factura Proveedores Multiples', 'MXN', $saldosum, $rowe['cuenta'], $type, $_POST['tagref'],$taxcatid);
                            for ($i=100;$i <= 2; $i++) {
                                $numregistros = count($_POST['selMovimiento'])-1;
                                $table='gltrans';
                                $umovto = $_POST['selMovimiento'][$i];
                                $tagref = $_POST['tagref'][$umovto];
                                $status = $_POST['status'][$umovto];
                                $procesar=true;
                                $procesarobtenernum=true;
                                $supplierid = $_POST['supplierid'][$umovto];
                                $value=$_POST['UnificarPagoselect'];
                                
                                if(true){  
                                if ($status==2)
                                { 
                                        $saldo = $_POST['saldo'][$umovto];
                                        $tagref = $_POST['tagref'][$umovto];
                                        //$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
                                        $supplierid = $_POST['supplierid'][$umovto];
                                        $ratefactura = $_POST['rate'][$umovto];
                                        $idfactura = $_POST['idfactura'][$umovto];
                                        $diffonexch = $_POST['diffonexch'][$umovto];
                                        $inisupplierid = $supplierid;

                                        $saldofactura =  ($saldo / $ratefactura);        

                                           if($generatransno)
                                           { 
                                              $generatransno=false;
                                              $TransNo = GetNextTransNo(22, $db);
                                              $initransno = $TransNo;
                                           }  
                                        $bankaccount = $_POST['BankAccount'];
                                        if ($Tipopago == 'Cheque'){
                                           if($generanumerodecheque)
                                           {
                                            $generanumerodecheque=false;
                                             $ChequeNum = GetNextChequeNo($_POST['BankAccount'], $db);  
                                           }  
                                        }else{
                                                $ChequeNum = $Tipopago;
                                        }
                                        //echo "<br>Cheque num: $ChequeNum";
                                        $narrative  = "Pago de factura";
                                        $Transtype = $type;
                                        /* CREAR UN REGISTRO DEL PAGO DE PROVEEDOR           */
                                        /* Create a SuppTrans entry for the supplier payment */
                                        $ratecheque = ($_POST['ExRate']/$_POST['FunctionalExRate']);
                                        $saldocheque = ($saldo / ($_POST['ExRate']/$_POST['FunctionalExRate']));

                                            $saldocheque2=($saldosum / ($_POST['ExRate']/$_POST['FunctionalExRate']));

                                        /* if(isset($_POST['UnificarPago']))
                                         {
                                            $umovto=  implode("','", $arrayverificados);
                                         }*/
                                        $consulta= "SELECT supptrans.id, supptrans.transno, supptrans.ovamount, supptrans.ovgst, supptrans.ovgst/supptrans.ovamount AS porcentaje_iva, supptransdetails.price, supptransdetails.qty, purchorderdetails.clavepresupuestal
                                                    FROM supptrans
                                                    INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid
                                                    INNER JOIN purchorderdetails ON supptransdetails.orderno= purchorderdetails.orderno AND supptransdetails.stockid= purchorderdetails.itemcode
                                                    WHERE id in ('".$umovto."')";
                                        $resultado = DB_query($consulta, $db);                                    

                                        while ($registro = DB_fetch_array($resultado)){
                                            // Generacion del momento contable para el pago
                                            $importe= (($registro["price"] * $registro["qty"]) * (1 + number_format($registro["porcentaje_iva"],2))) / $ratefactura;
                                            $importe=truncateFloat($importe, $digitos);
                                            if(true){
                                            GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagref, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db,false,'',$descrip,$value);//$fechacheque_contable
                                            }
                                            else {
                                            GeneraMovimientoContablePresupuesto($Transtype, "EJERCIDO", "PAGADO", $TransNo, $PeriodNo, $importe, $tagref, $fechacheque, $registro["clavepresupuestal"], $TransNo, $db,true,$supplierid,$descrip,$value);//$fechacheque_contable
                                            }
                                        }   
                                        $SQL = "INSERT INTO supptrans (transno,
                                                type,
                                                supplierno,
                                                trandate,
                                                suppreference,
                                                rate,
                                                ovamount,
                                                transtext,
                                                tagref,
                                                origtrandate,
                                                ref1, 
                                                alloc,
                                                settled, ref2
                                                ) ";
                                        $SQL = $SQL . 'VALUES (' . $TransNo . ",
                                                $type,	
                                                '".$_POST['UnificarPagoselect']."',
                                                '" . $fechacheque . "',
                                                '" . $ChequeNum . "',
                                                '" . ($_POST['ExRate']/$_POST['FunctionalExRate']) . "',
                                                " . (-1)*$saldosum . ",
                                                '" . $narrative . "',
                                                " . $tagref . ",
                                                now(),
                                                '" . $ChequeNum . "',
                                                " . (-1)*$saldosum . ",
                                                " . "0" . ",
                                                ".$umovto.")";
                                        $ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
                                        $DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
                                        //echo "<pre>SQL: " . $SQL;
                                       if($crearMovimiento)
                                       {
                                       $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                       $crearMovimiento=false;
                                       }
                                        $prevdiffonexch = $prevdiffonexch +  ($_POST['ExRate']/$_POST['FunctionalExRate']);
                                        if($generaridcheque)
                                        {
                                           $idcheque = DB_Last_Insert_ID($db,'supptrans','id');
                                           $generaridcheque=false;
                                        }

                                        /* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
                                        /*Update the supplier master with the date and amount of the last payment made */
                                        $SQL = "UPDATE suppliers SET
                                                lastpaiddate = '" . $fechacheque . "',
                                                lastpaid=" . $saldo ."
                                                WHERE suppliers.supplierid='" . $supplierid . "'";

                                        $ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
                                        $DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
                                        //echo "<pre>" . $SQL;
                                        $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                                        $SQL = "INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto)
                                                VALUES ('" . $fechacheque . "', 
                                                " . $saldo . ', 
                                                ' . $idcheque . ', 
                                                ' . $idfactura . ')';

                                        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  _('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' ._('could not be inserted because');
                                        $DbgMsg = _('The following SQL to insert the allocation record was used');
                                        $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                                        //echo "<pre>" . $SQL;
                                        //prnMsg(_('Inserto nueva aplicacion...'),'success');

                                        $SQL = 'UPDATE supptrans
                                                SET diffonexch=' . $diffonexch . ', 
                                                    alloc = ' .  $saldo . ', 
                                                    settled = ' . "1" . ' 
                                                WHERE id = ' . $idfactura;

                                        $ErrMsg = _('ERROR CRITICO') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
                                        $DbgMsg = _('The following SQL to update the debtor transaction record was used');

                                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                                        //echo "<pre>" . $SQL;
    /***/
                                        if ($_SESSION['CompanyRecord']['gllink_creditors']==1){

                                                //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
                                                if ($inisupplierid != ''){
                                                        $tipoproveedor = ExtractTypeSupplier($inisupplierid,$db);
                                                        $ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
                                                        $tipoproveedorpuente = ExtractTypeSupplier($_POST['UnificarPagoselect'],$db);
                                                        $ctaxtipoproveedorpuente = SupplierAccount($tipoproveedorpuente,"gl_accountsreceivable",$db);
                                                }else{
                                                        $ctaxtipoproveedor = $_SESSION['CompanyRecord']['creditorsact'];
                                                }

                                                $narrative = $inisupplierid . "-" . "PAGO DE VARIAS FACTURAS@" . ($saldofactura);
    //
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
                                                            supplier,descripcion
                                                            ) VALUES (
                                                        $type,
                                                        '" . $initransno . "',
                                                        '" . $fechacheque . "',
                                                        '" . $PeriodNo . "',
                                                        '" . $ctaxtipoproveedor . "',
                                                        '" . $narrative . "',
                                                        '" . $saldofactura . "',
                                                        '" . $tagref . "',
                                                        '" . $ChequeNum . "',
                                                         '" . $_SESSION['UserID'] . "',
                                                         now(),
                                                         '".$value."','".$descrip."'
                                                        )";
                                                $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                                $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');					
                                                //echo "<pre>" . $SQL;
                                                $result = DB_query($SQL1,$db,$ErrMsg,$DbgMsg,true);

                                                $narrative = $inisupplierid . "-" . "PAGO DE VARIAS FACTURAS@" . (-$saldofactura);	
                                                $totalimpuestosretencion = 0;
                                                if(isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> ""){
                                                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivahonorarios'];
                                                }
                                                if(isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> ""){
                                                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrhonorarios'];
                                                }
                                                if(isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> ""){
                                                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionivaarrendamiento'];
                                                }
                                                if(isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> ""){
                                                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionisrarrendamiento'];
                                                }
                                                if(isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> ""){
                                                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencionfletes'];
                                                }
                                                if(isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> ""){
                                                    $totalimpuestosretencion = $totalimpuestosretencion + $_POST['retencioncedular'];
                                                }

                                                $saldocheque = $saldocheque - $totalimpuestosretencion;
                                                $saldocheque2 = $saldocheque2 - $totalimpuestosretencion;
                                               // echo $saldocheque .'='.$saldocheque.'-'.$saldocheque;
                                                $SQL2="INSERT INTO ".$table." (
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
                                                            cuentabanco,descripcion
                                                            ) VALUES (
                                                        $type,
                                                        '" . $initransno . "',
                                                        '" . $fechacheque . "',
                                                        '" . $PeriodNo . "',
                                                        '" . $rowe['cuenta'] . "',
                                                        '" . $narrative . "',
                                                        '" . -1*($saldocheque). "',
                                                        '" . $tagref . "',
                                                        '" . $ChequeNum . "',
                                                        '" . $_SESSION['UserID'] . "',
                                                         now(),
                                                         '".$value."',
                                                             1
                                                             ,'".$descrip."'
                                                        )";
                                                $ErrMsg = _('No se pudo insertar a la cuenta puente.');
                                                $DbgMsg = _('No se completo la transaccion.');

                                                $result = DB_query($SQL2,$db,$ErrMsg,$DbgMsg,true);


                                                if($numregistros==$i)
                                                {
                                                     $narrative = $inisupplierid . "-" . "PAGO DE FACTURA UNIFICADO@" . ($saldofactura);
    //
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
                                                            supplier,descripcion
                                                            ) VALUES (
                                                        $type,
                                                        '" . $initransno . "',
                                                        '" . $fechacheque . "',
                                                        '" . $PeriodNo . "',
                                                        '" . $rowe['cuenta'] . "',
                                                        '" . $narrative . "',
                                                        '" . 1*$saldosum . "',
                                                        '" . $tagref . "',
                                                        '" . $ChequeNum . "',
                                                         '" . $_SESSION['UserID'] . "',
                                                         now(),
                                                         '".$value."','".$descrip."'
                                                        )";
                                                $ErrMsg = _('Cuenta puente');
                                                $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');					
                                                //echo "<pre>" . $SQL;
                                                $result = DB_query($SQL1,$db,$ErrMsg,$DbgMsg,true);
                                                }



                                                if($numregistros==$i)
                                                {
                                                     $narrative = $inisupplierid . "-" . "PAGO DE FACTURA UNIFICADO@" . ($saldofactura);
    //
                                                $SQL2="INSERT INTO ".$table." (
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
                                                            cuentabanco,descripcion
                                                            ) VALUES (
                                                        $type,
                                                        '" . $initransno . "',
                                                        '" . $fechacheque . "',
                                                        '" . $PeriodNo . "',
                                                        '" . $bankaccount . "',
                                                        '" . $narrative . "',
                                                        '" . -1*($saldosum). "',
                                                        '" . $tagref . "',
                                                        '" . $saldosum . "',
                                                        '" . $_SESSION['UserID'] . "',
                                                         now(),
                                                         '".$value."',
                                                             1
                                                             ,'".$descrip."'
                                                        )";
                                                $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                                $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');					
                                                //echo "<pre>" . $SQL;
                                                $result = DB_query($SQL2,$db,$ErrMsg,$DbgMsg,true);
                                                }


                                                /*********INICIO PERDIDA CAMBIARIA***************/
                                                $utilidadperdida = $saldofactura - $saldocheque;
                                                $saldofactura2 = $saldofactura - $totalimpuestosretencion;
                                                if($saldocheque!=$saldofactura2){
                                                    if (abs($utilidadperdida) > .1)
                                                {
                                                        if ($utilidadperdida < 0)
                                                        {
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
                                                                        VALUES ($type, 
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
                                                                $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, True);


                                                        }else{

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
                                                                        VALUES ($type, 
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
                                                                $Result_up = DB_query($SQL_up, $db, $ErrMsg, $DbgMsg, True);
                                                        }
                                                }
                                                /********FIN PERDIDA CAMBIARIA***************/
                                                }







                                                /**************************************************/
                                                /*MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
                                                $saldo = $_POST['saldo'][$umovto];
                                                $taxrate = .16;

                                                $CreditorTotal = ($saldo/$_POST['ExRate'])/$_POST['FunctionalExRate'];

                                                $SQL = 'select * from taxauthorities where taxid=1';
                                                $result2 = DB_query($SQL,$db);
                                                if ($TaxAccs = DB_fetch_array($result2)){
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
                                                                        $type,
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
                                                                                                            descripcion)";
                                                        $SQL=$SQL . "VALUES (
                                                                        $type,
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
                                                while($myrowfact = DB_fetch_array($resultfact)){
                                                    $foliorefe = $myrowfact['suppreference'];
                                                    $typerefe = $myrowfact['type'];
                                                    $transorefe = $myrowfact['type'];
                                                }

                                                  $narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura)."folio factura ".$foliorefe." fologierp".$typerefe." ".$transorefe;	  
                                                /* Movimiento Contable de Retencion Honorarios iva */
                                                if(isset($_POST['retencionivahonorarios']) and $_POST['retencionivahonorarios'] <> ""){
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
                                                                                                            dateadded,
                                                                                                            supplier,
                                                                                                            descripcion) ";
                                                    $SQL=$SQL . "VALUES (
                                                                        $type,
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
                                                                        '".$descrip."'		)";
                                                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                                }

                                                /* Movimiento Contable de Retencion Honorarios ISR */
                                                if(isset($_POST['retencionisrhonorarios']) and $_POST['retencionisrhonorarios'] <> ""){
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
                                                                                                            descripcion
                                                                                                            ) ";
                                                    $SQL=$SQL . "VALUES (
                                                                        $type,
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
                                                                        '".$value."',
                                                                        '".$descrip."')";
                                                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                                                }

                                                /* Movimiento Contable de Retencion Arrendamiento IVA */
                                                if(isset($_POST['retencionivaarrendamiento']) and $_POST['retencionivaarrendamiento'] <> ""){
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
                                                                        $type,
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
                                                                        '".$value."','".$descrip."')";
                                                    $ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
                                                    $DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
                                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                                                }

                                                /* Movimiento Contable de Retencion Arrendamiento ISR */
                                                if(isset($_POST['retencionisrarrendamiento']) and $_POST['retencionisrarrendamiento'] <> ""){
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
                                                                        $type,
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
                                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                                                }

                                                 /* Movimiento Contable de Retencion fletes */
                                                if(isset($_POST['retencionfletes']) and $_POST['retencionfletes'] <> ""){
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
                                                                                                            dateadded,
                                                                                                            supplier,
                                                                                                            descripcion) ";
                                                    $SQL=$SQL . "VALUES (
                                                                        $type,
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
                                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                                                }

                                                /* Movimiento Contable de Retencion cedular */
                                                if(isset($_POST['retencioncedular']) and $_POST['retencioncedular'] <> ""){
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
                                                                        $type,
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
                                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                                                }

                                                        $SQL= "SELECT suppname
                                                        FROM suppliers
                                                        WHERE supplierid='".$_POST['UnificarPagoselect']."'";
                                                        $Result = DB_query($SQL, $db);
                                                        if (DB_num_rows($Result)==0){ 
                                                                prnMsg( _('El codigo de Proveedor con el que esta pagina fue llamada, no existe en base de datos de Proveedores') . '. ' . _('Si esta pagina es llamada desde la pagina de Proveedores, esto garantiza que el proveedor existe!'),'warn');
                                                                include('includes/footer.inc');
                                                                exit;
                                                        } else {
                                                                /*CODIGO DE PROVEEDOR VALIDO*/
                                                                $myrow = DB_fetch_array($Result);
                                                                $Beneficiario = $myrow['suppname'];

                                                        }



                                                //if(empty($transnos) == FALSE) {
                                                //	$Beneficiario = $Beneficiario . '@' . implode('|', $transnos);	
                                                //} else {
                                                        //$Beneficiario = "Beneficiario";
                                                //}
                                                $narrative = $supplierid . "@" . (($saldo) * (-1));

                                                $SQL="INSERT INTO banktrans (transno,
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
                                                                chequeno)";
                                                $SQL= $SQL . "VALUES ('" . $initransno . "',
                                                        '" . $Transtype . "',
                                                       '" . $bankaccount . "',
                                                        '" . $narrative . "',
                                                        '" . $_POST['ExRate'] . "' ,
                                                        '" . $_POST['FunctionalExRate'] . "',
                                                        '".$fechacheque."',
                                                        '" . $Tipopago. "',";
                                                $SQL .=    abs($saldocheque2) * (1) . ",
                                                        '" . $moneda . "',
                                                        '" . $tagref . "',
                                                        '" . $Beneficiario . "',
                                                        '" . $ChequeNum . "'
                                                )";
                                                $ErrMsg = _('No pude insertar la transaccion bancaria porque');
                                                $DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');

                                                 if($numregistros == $i)
                                                {
                                                    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                                }



                                                //echo "<br>" . $SQL;
                                        }
                            // Agregar aqui las opciones finales del proceso
                            if($procesarprimero)
                            {
                                        /***** INICIO IMPRESION DE CHEQUE******************/
                                        prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('al proveedor') . ' ' . $Beneficiario . ' ' . _('ha sido exitosamente procesado'),'success');

                                        //$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);
                                        //$lasttag=$_POST['tag'];


                                        $liga = GetUrlToPrint($tagref,$Transtype,$db);

                                        /* BUSCA SI CHEQUERA UTILIZA UN FORMATO ESPECIAL DE IMPRESION */
                                        $sql="select *
                                                FROM bankaccounts
                                                WHERE accountcode='" . $bankaccount . "'";
                                        $Result = DB_query($sql, $db);
                                        $myrow = DB_fetch_array($Result);
                                        $pdfprefix=$myrow['pdfprefix'];
                                        if ($pdfprefix == null)
                                                $pdfprefix = "";//

                                        /*Set up a newy in case user wishes to enter another */

                                        //if($_SESSION['subirxmlprov'] == 1){
                                            //$liga2="SubirXMLProveedor.php?debtorno=".$_SESSION['SuppTrans']->SupplierID."&propietarioid=".$_SESSION['SuppTrans']->SupplierID."&NoOrden=".$SuppTransID."&tipopropietarioid=6&muetraarchivos=0";
                                            //echo "<br><div class='centre'><a TARGET='_blank' href='" . $liga2 . "'>" . _('SubirXML') . "</a></div>";
                                    //}
                                                  //actualiza estatus del documento a Ejecutado
                                        $sql = "UPDATE supptrans
                                                SET hold = " . $estatusValue['exec'] ."
                                                WHERE id = ". $umovto ."";
                                        $resul = DB_query($sql,$db);


                                        /***** FIN IMPRESION DE CHEQUE *******************/
    /***/


                            $procesarprimero=false;
                            echo "<br>";                
                           echo '<table cellpadding="0" cellspacing="0" width="50%" border="1" bordercolor="lightgray">';
                               echo '<tr>';
                                   echo '<td class="titulos_principales">'._("Pago").'</td>';
                                   echo '<td class="titulos_principales">'._("Proveedor").'</td>';
                                   echo '<td class="titulos_principales">'._("Formato").'</td>';
                                   echo '<td class="titulos_principales">'._("Cheque").'</td>';
                                   echo '<td class="titulos_principales">'._("Poliza").'</td>';
                                   echo '<td class="titulos_principales">'._("Acciones Siguientes").'</td>';
                                echo '</tr>';
                                echo '<tr>';
                                   echo '<td class="numero_normal">'.$TransNo.'</td>';
                                   //Busqueda de proveedor
                                        $SQLseasupp="SELECT suppname FROM suppliers WHERE supplierid='".$_POST['UnificarPagoselect']."';";
                                        $rowseasupp=  DB_fetch_array(DB_query($SQLseasupp, $db));
                                        $suppliername=$rowseasupp['suppname'];
                                        echo '<td class="texto_normal2" nowrap>'.$suppliername."</td>";

                                        //Formato de cheque CON movimientos contables
                                        $link="PrintJournalCh.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $Transtype. "&TransNo=" . $initransno ;
                                        $liga='<a  target="_blank" href="'.$link.'">'.'<img src="images/imprimir_25.png" title="' . _('FORMATO PRE-IMPRESO CON DETALLE CONTABLE')
                                        . '" alt="">' . ' ' ._('').'</a>'; 
                                        echo '<td class="numero_normal">'.$liga.'</td>';

                                        //Formato de cheque sin movimientos contables
                                        $link="PDFCheque.php?type=" . $Transtype. "&TransNo=" . $initransno ;
                                        $liga='<a  target="_blank" href="'.$link.'">'.'<img src="images/imprimir_25.png" title="' . _('FORMATO PRE-IMPRESO')
                                        . '" alt="">' . ' ' ._('').'</a>'; 
                                        echo '<td class="numero_normal">'.$liga.'</td>';

                                        //Poliza
                                        $liga="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=".$Transtype. "&TransNo=" .$initransno. "&periodo=". "&trandate=";
                                        echo "<td class='numero_normal'><a TARGET='_blank' href='" . $liga . "'>" . _('') . "<img src='images/imprimir_25.png' title='" . _('POLIZA') . "' alt=''></a></td>";
                            }                

                            }else{
                                        prnMsg(_('Debe seleccionar un movimiento Autorizado') .  ' ' . $_POST['selMovimiento'][$i],'error');	
                                }
                                }
                                if(isset($_POST['UnificarPago']))
                                {
                                    $arrayverificadoslog[]=$_POST['supplierid'][$umovto];	
                                } 
                            }
                             foreach ($suplierrepetidos as $value) {//Actualizacion gltrans para proceso unificado
                                 $SQL="Select * FROM `gltrans_polisa` WHERE supplier='".$value."'";
                                 $result=  DB_query($SQL, $db);
                                 if(DB_num_rows($result)>0)
                                 {
                                 $SQL=" INSERT INTO `gltrans` ( `type`, `typeno`, `chequeno`, `trandate`, `periodno`, `account`, `narrative`, `amount`, `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`, `dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco)
                                        SELECT  `type`, `typeno`, `chequeno`, `trandate`, `periodno`, `account`, `narrative`,`amount`, `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`,`dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco
                                        FROM gltrans_polisa 
                                        WHERE supplier='".$value."'  AND cuentabanco != 1 ";
                                DB_query($SQL, $db); 
                                $SQL=" INSERT INTO `gltrans` ( `type`, `typeno`, `chequeno`, `trandate`, `periodno`, `account`, `narrative`, `amount`, `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`, `dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco)
                                        SELECT  `type`, `typeno`, `chequeno`, `trandate`, `periodno`, `account`, `narrative`,sum(`amount`), `posted`, `jobref`, `tag`, `lasttrandate`, `amountpaid`, `branchno`, `userid`, `rate`, `complemento`, `cat_cuenta`, `loccode`, `flagdiot`, `typediot`, `debtorno`,`dolares`, `percentpaid`, `payapplies`, `typepaid`, `suppno`, `grns`, `purchno`, `stockid`, `qty`, `standardcost`, `lastusermod`, `lastdatemod`, `dateadded`, `cuentadestino`, `bancodestino`, `rfcdestino`, `clavepresupuestal`, `uuid`, `supplier`,cuentabanco
                                        FROM gltrans_polisa 
                                        WHERE supplier='".$value."'  AND cuentabanco = 1 ";
                                DB_query($SQL, $db); 
                                $SQL="DELETE FROM `gltrans_polisa` WHERE supplier='".$value."'";
                                DB_query($SQL, $db); 
                                }
                            }

                    }
                            else
                            {
                                prnMsg(_('La descripcion y el proveedor fuente son requeridos'),'error');
                            }
                        }
                    }

			echo '<td class="texto_normal2"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Revisi&oacute;n y Aprobaci&oacute;n Pagos CxP') . '</a></td>';
                        echo '</table>'; 
			//echo '<br><a href="' . $rootpath . '/GeneralAccountsPayableAuthProcV2.php" >' . _('Revision y Aprobacion PagosCxP') . '</a>';
			exit;
		}
	}
	
	
	/* OBTENGO  FECHAS */
		
	/*if $FromCriteria is not set then show a form to allow input	*/
	
		echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
				echo '<fieldset class="cssfieldset" style="width:50%">
						<legend>Criterio de Consulta</legend>
						<table>';
		
		/*
		echo '<tr><td colspan=2><p align=center><b>** SELECCIONA EL CRITERIO DE BUSQUEDA</b><br><br></td>';
		echo '</tr>';
		*/
	
	/* SELECCIONA EL RANGO DE FECHAS */
	
	/*
		       echo '<tr>';
		       echo '<td colspan=2>
				<table>
				     <tr>';
					    echo '<td>' . _('Desde:') . '</td>
					    <td><select Name="FromDia">';
						 $sql = "SELECT * FROM cat_Days";
						 $dias = DB_query($sql,$db);
						 while ($myrowdia=DB_fetch_array($dias,$db)){
						     $diabase=$myrowdia['DiaId'];
						     if (rtrim(intval($FromDia))==rtrim(intval($diabase))){ 
							 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
						     }else{
							 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
						     }
						 }
					    echo'</td>'; 
					    echo '<td><select Name="FromMes">';
						      $sql = "SELECT * FROM cat_Months";
						      $Meses = DB_query($sql,$db);
						      while ($myrowMes=DB_fetch_array($Meses,$db)){
							  $Mesbase=$myrowMes['u_mes'];
							  if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){ 
							      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
							  }else{
							      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
							  }
						      }
						      
						      echo '</select>';
						      echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
							      
					      echo '</td>
					    <td>
						 &nbsp;
					    </td>
					    <td>' . _('Hasta:') . '</td>';
					    echo'<td><select Name="ToDia">';
						      $sql = "SELECT * FROM cat_Days";
						      $Todias = DB_query($sql,$db);
						      while ($myrowTodia=DB_fetch_array($Todias,$db)){
							  $Todiabase=$myrowTodia['DiaId'];
							  if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
							      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
							  }else{
							      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
							  }
						      }
					    echo '</td>';
					    echo'<td>';
						 echo'<select Name="ToMes">';
						 $sql = "SELECT * FROM cat_Months";
						 $ToMeses = DB_query($sql,$db);
						 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
						     $ToMesbase=$myrowToMes['u_mes'];
						     if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
							 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
						     }else{
							 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
						     }
						 }
						 echo '</select>';
						 echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
						 
					    echo'</td>';
					echo '</tr>';
				echo '<table>';			
			   echo '</td>';	
		       echo '</tr>';
	
	
		       echo '<tr>';
		       echo '<td colspan=2>';
		       echo '&nbsp;</td></tr>';
	
	*/
	/* SELECCIONA EL RANGO DE FECHAS */
	
	
	
		/************************************/
		//SELECCION RAZON SOCIAL
		echo '	<tr>
					<td class="texto_lista">'._('Raz&oacute;n Social:').'<td><select name="legalid">';
			
		///Pinta las razones sociales
		$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
    $SQL = $SQL . " FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
    $SQL = $SQL . " WHERE u.tagref = t.tagref ";
    $SQL = $SQL . " and u.userid = '" . $_SESSION['UserID'] . "'
				  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";

    $result = DB_query($SQL, $db);
    echo "<option selected value='0'>Todas a las que tengo acceso...</option>";
    while ($myrow = DB_fetch_array($result)) {
        if (isset($_POST['legalid']) and $_POST['legalid'] == $myrow["legalid"]) {
            echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'] . ' - ' . $myrow['legalname'];
        } else {
            echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'] . ' - ' . $myrow['legalname'];
        }
    }
    echo '</select></td>
    			</tr>';
    /*echo "<tr>";

   echo "<td>"._('Unidades de Negocio')."</td>" . "<td><select name='unidadnegocio'>";

    $SQL = "SELECT tagref,tagdescription FROM tags ORDER BY tagref";
    $result2 = DB_query($SQL, $db);
    
    echo "<option selected value='0'>Todas a las que tengo acceso...</option>";
    while($myrow2 = DB_fetch_array($result2)){
            echo '<option value=' .$myrow2['tagref'] . '>' .$myrow2['tagref'] . ' - ' . $myrow2['tagdescription'];

            }


     echo '</select></td></tr>';
*/
    echo "<tr>"; 
                
                echo "<td class='texto_lista'>"._('Tipo de Gasto:')."</td>";
                $SQL = "SELECT tagref,
                                tagdescription
                        FROM tags ORDER BY tagref";
                $result = DB_query($SQL, $db);
                echo"<td><select name=unidadnegocio>";
                echo "<option selected value=0>"._('Todas las unidades de negocio')."</option>";
                while($myrow = DB_fetch_array($result)){
                    if($_POST['unidadnegocio'] == $myrow['tagref']){
                        echo "<option selected value='".$myrow['tagref']."'>".$myrow['tagdescription']."</option>";
                    }else{
                        echo "<option  value='".$myrow['tagref']."'>".$myrow['tagdescription']."</option>";
                    }
                }
                echo "</select></td>";
                echo "</tr>";
	
		echo "<input type=hidden name=xRegion value=0>";
		echo "<input type=hidden name=xArea value=0>";
		echo "<input type=hidden name=xDepartamento value=0>";
		//echo "<input type=hidden name=unidadnegocio value=0>";//
		
		
	
	
	
		/************************************/
		/* SELECCION DEL PROVEEDOR */
		echo "<tr><td class='texto_lista'>" . _('Proveedor:') . ":</td><td>";
		echo "<select name='proveedor'>";
		$SQL = "SELECT  supplierid,suppname ";
			$SQL = $SQL .	" FROM suppliers where suppname<>'' ";
			$SQL = $SQL .	" ORDER BY suppname"; 
					
	
		
		$ErrMsg = _('No transactions were returned by the SQL because');
		$TransResult = DB_query($SQL,$db,$ErrMsg);
		
		echo "<option selected value='0'>Todos a las que tengo accceso...</option>";
		
		while ($myrow=DB_fetch_array($TransResult)) {
			if ($myrow['supplierid'] == $_POST['proveedor']){
				echo "<option selected value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";	
			}else{
				echo "<option value='" . $myrow['supplierid'] . "'>" . $myrow['suppname'] . "</option>";
			}
		}
		 
		echo "</select>";
		echo "</td></tr>";
		/************************************/
		
		echo '<tr><td><br>** Detalle del Reporte</td>
			<td>';
		echo '</td></tr>';
		
		echo '<tr><td class="texto_lista">' . _('Estado de Aprobaci&oacute;n') . ':' . "</td>
			<td><select tabindex='5' name='DetailedReport'>";
		
		if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'all')
			echo "<option selected value='all'>" . _('Todos los pagos...');
		else
			echo "<option value='all'>" . _('Todos los pagos...');
			
		if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'pend')
			echo "<option selected value='pend'>" . _('Pendiente');
		else
			echo "<option value='pend'>" . _('Pendiente');
			
		if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'prog')
			echo "<option selected value='prog'>" . _('Programado');
		else
			echo "<option value='prog'>" . _('Programado');
		
		if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'auth')
			echo "<option selected value='auth'>" . _('Autorizado');
		else
			echo "<option value='auth'>" . _('Autorizado');
			
		if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'exec')
			echo "<option selected value='exec'>" . _('Ejecutado');
		else
			echo "<option value='exec'>" . _('Ejecutado');
			
		echo '</select></td></tr>';
		
		/************************************/
		/* SELECCION DEL TIPO CAMBIO*/
		
		echo "<tr><td class='texto_lista'>" . _('Tipo Moneda') . ":</td><td>";
		echo "<select name='tipocambio' >";
		
		if (isset($_POST['tipocambio']) and $_POST['tipocambio'] == 'No')
			echo "<option selected value='No'>" . _('MX');
		else
			echo "<option value='No'>" . _('MX');
		
		if (isset($_POST['tipocambio']) and $_POST['tipocambio'] == 'Yes')
			echo "<option selected value='Yes'>" . _('US');
		else
			echo "<option value='Yes'>" . _('US');
			
		echo '</select></td></tr>';
		
		/************************************/	
		
		echo '</table>';
		echo '</fieldset>
			<br><div class="centre"><button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="ReportePantalla" title="DESPLIEGA CUENTAS POR PAGAR" value="Despliega Cuentas X Pagar">
						<img src="images/buscar_25.png" title="Buscar">
					</button></div><br>';
		/**echo '<br><div class="centre"><input tabindex="7" type=hidden name="PrintPDF" value="' . _('Imprime Archivo PDF') . '"></div>';
		echo '<br><div class="centre"><input tabindex="7" type=submit name="PrintEXCEL" value="' . _('Exportar a Excel') . '"></div>';*/
}

If (isset($_POST['ReportePantalla']) OR isset($_POST['ProcesaOperacion']) OR isset($_POST['ProcesaPromesa']) OR isset($_POST['ProcesaAutorizacion']) OR isset($_POST['EnviarEmail'])) {
	if (isset($_POST['PrintEXCEL'])) {
	
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=ReporteCXPProveedor.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	}		
	
	$SQL = "SELECT tags.tagdescription as name, systypescat.typename, supptrans.supplierno,
		       suppliers.suppname, suppliers.taxid, suppliers.lastpaiddate, lastpaid,
		       supptrans.trandate, supptrans.origtrandate, supptrans.id,
		       supptrans.duedate, supptrans.promisedate,
		       supptrans.ovamount/supptrans.rate as ovamount,
		       supptrans.ovgst/supptrans.rate as ovgst, supptrans.alloc/supptrans.rate as alloc,
		       suppliers.bankact,suppliers.bankpartics, supptrans.suppreference, supptrans.transno,
		       supptrans.hold,
		       CASE WHEN supptrans.promisedate = '0000-00-00' THEN -1
			ELSE DATEDIFF(supptrans.promisedate,supptrans.duedate) END as atraso,		
			CASE WHEN supptrans.duedate < '".$fechaini."' THEN 0
			ELSE CASE WHEN supptrans.duedate >= '".$fechaini."' and supptrans.duedate <= '".$fechafin."' THEN 1
			ELSE 2 END END as EstadoVencimiento,
			supptrans.tagref,
			supptrans.rate as rate,
			supptrans.diffonexch as diffonexch			
		FROM supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
		      JOIN tags ON supptrans.tagref = tags.tagref
		      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		      JOIN areas ON tags.areacode = areas.areacode
		      JOIN regions ON areas.regioncode = regions.regioncode
		      JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
		WHERE (abs((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc) > .01) and 
		      supptrans.type in ('470','20','34','117','121') 
		      and (suppliers.supplierid = '".$_POST['proveedor']."' or '".$_POST['proveedor']."'= '0') 
		      and (tags.legalid = '".$_POST['legalid']."' or '".$_POST['legalid']."'= '0') 
		      and (areas.regioncode = '".$_POST['xRegion']."' or '".$_POST['xRegion']."'= '0') 
		      and (supptrans.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'= '0') 
		      and (supptrans.hold = '".$estatusValue[$_POST['DetailedReport']]."' or '".$estatusValue[$_POST['DetailedReport']]."' = '-1') 
		      /*and supptrans.duedate >= '".$fechaini."' and supptrans.duedate <= '".$fechafin."'*/
		      Order by EstadoVencimiento,
                        atraso desc,
                        supptrans.duedate asc,
                        (supptrans.ovamount+supptrans.ovgst-supptrans.alloc/supptrans.rate) desc";
      //echo $SQL;//desarrollo
        
        
        /* DESDE - HASTA lo estoy eliminando para que siempre aparezca lo que esta pendiente... */
	//echo "<br>".$SQL;//
        
	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
            $title = _('Estado General de Cuentas por Pagar') . ' - ' . _('Reporte de Problema') ;
            prnMsg(_('Los detalles de proveedores no se pudieron recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');

            if ($debug==1){
                //echo "<br>".$SQL;
            }
            exit;
	}
	
	$DoctoTotPagos = 0;
	$VenceTotPagos = 0;
	$ProvTotPagos = 0;
	$RegionTotPagos = 0;
	
	$TotPagos = 0;
	
	$estatusName['pend'] = 'Pendientes de Pago';
	$estatusName['prog'] = 'Pagos Programados';
	$estatusName['auth'] = 'Pagos Autorizados';
	$estatusName['exec'] = 'Pagos Ejecutados';
	
	$estatusLong[0] = 'Pendiente';
	$estatusLong[1] = 'Programado';
	$estatusLong[2] = 'Autorizado';
	$estatusLong[3] = 'Ejecutado';
	
	$estatusLongVence[0] = 'Vencidas';
	$estatusLongVence[1] = 'Al Corriente';
	$estatusLongVence[2] = 'Vencen Despues';
	
	$estatusColor[0] = '#b2b2b2';//Gris
	$estatusColor[1] = '#f8f493';//Amarillo
	$estatusColor[2] = '#7CFFA4';//Verde
	$estatusColor[3] = '#d27786';//Rojo
	
	$estatusColorVence[0] = '#FF6060';
	$estatusColorVence[1] = '#90FF90';
	$estatusColorVence[2] = '#FFFFFF';
		
	/* DESPLIEGA TITULOS*/
	echo '<table cellpadding=2 border=0>';
	echo "<tr><td>";
	if ($_POST['DetailedReport'] != 'all') {
            echo "<p style='font-size:14px;color:blue'><b>*".$estatusName[$_POST['DetailedReport']]."</b></p>";
	}
	echo "</td></tr>";
	echo '</table>';
	//
	echo '<table cellspacing=0 border=1 align="center" bordercolor=lightgray cellpadding=3>';
	
	$sendMail     = FALSE;
	$mailMessage .= "<h2>Pagos Autorizados</h2>";
	$mailMessage .= "<table border='1'>";
	$mailMessage .= "<tr style='text-align:left; font-size:1.2em; font-weight:bold'>";
	$mailMessage .= "<th style='padding:.2em'>Proyecto</th>";
	$mailMessage .= "<th style='padding:.2em'>CVE</th>";
	$mailMessage .= "<th style='padding:.2em'>Nombre</th>";
	$mailMessage .= "<th style='padding:.2em'>Folio</th>";
	$mailMessage .= "<th style='padding:.2em'>Emitido</th>";
	$mailMessage .= "<th style='padding:.2em'>Vence</th>";
	$mailMessage .= "<th style='padding:.2em'>Promesa (atraso)</th>";
	$mailMessage .= "<th style='padding:.2em'>Saldo</th>";
	$mailMessage .= "</tr>";
	
	$headerLineaProductos = '<tr>
			<th class="titulos_principales">' . _('SEL') . '</th>
			<th class="titulos_principales">' . _('Proyecto') . '</th>
			<th class="titulos_principales">' . _('Autorizado') . '</th>
			<th class="titulos_principales">' . _('CVE') . '</th>
			<th class="titulos_principales">' . _('Nombre') . '</th>
			<th class="titulos_principales">' . _('Folio') . '</th>
			<th class="titulos_principales" nowrap>' . _('Emitido') . '</th>
			<th class="titulos_principales" nowrap>' . _('Vence') . '</th>
			<th class="titulos_principales" nowrap>' . _('Promesa<br>(atraso)') . '</th>
			<th class="titulos_principales" nowrap>' . _('Saldo') . '</th>';
	
        if($_SESSION['subirxmlprov'] == 1){
            $headerLineaProductos = $headerLineaProductos.'<th class="titulos_principales" nowrap>' . _('XML') . '</th>';
	}
        
        $headerLineaProductos = $headerLineaProductos.'</tr>';
			
        /*<th><b>' . _('RFC') . '</b></th>
         *<th><b>' . _('Compra') . '</b></th>
        <th><b>' . _('Tipo') . '</b></th>
        <th><b>' . _('Monto') . '</b></th>
        <th><b>' . _('Abono') . '</b></th>
        */

	$i = 0;
	
	$ii = 0;
	
	$antRegion = '';
	$antProv = '';
	$antVence = '';
	$antNombre = '';
	
	$lineasConMismoDocumento = 0;
	$primeraEntrada = 1;
	
	echo $headerLineaProductos;
	
	//echo "<INPUT type=hidden id='chk0' name='selMovimiento[]' value='0'>";
	$strtags = "";
	
        
            $cantidad = count($_POST['selMovimiento']);
            if($cantidad>=2){
                prnMsg(_("Solo se puede procesar un cheque con retenciones"),"Info");    
            }
        
	while ($InvAnalysis = DB_fetch_array($ReportResult,$db))
        {
            if ($strtags == ""){
                $strtags = $InvAnalysis['tagref'];
            }else{
                $strtags = $strtags . "," . $InvAnalysis['tagref'];
            }
		
            $ii = $ii + 1;

            $thisVence = $InvAnalysis['duedate'];
            $thisRegion = $InvAnalysis['name'];
            $thisProv = $InvAnalysis['taxid'];
            $thisNombre = $InvAnalysis['suppname'];

            $lineasConMismoDocumento = $lineasConMismoDocumento + 1;

            $primeraEntrada = 0;		
            if ($i == 0) {
                echo '<tr class="EvenTableRows" bgcolor=#eeeeee>';
                $i = 1;
            } else {
                echo '<tr class="OddTableRows">';
                $i = 0;
            }

            $DoctoTotPagos = $DoctoTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst']);
            $VenceTotPagos = $VenceTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst']);
            $ProvTotPagos = $ProvTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst']);
            $RegionTotPagos = $RegionTotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst']);
            $TotPagos = $TotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst']);

            $DoctoTotAbonos = $DoctoTotAbonos + ($InvAnalysis['alloc']);
            $VenceTotAbonos = $VenceTotAbonos + ($InvAnalysis['alloc']);
            $ProvTotAbonos = $ProvTotAbonos + ($InvAnalysis['alloc']);
            $RegionTotAbonos = $RegionTotAbonos + ($InvAnalysis['alloc']);
            $TotAbonos = $TotAbonos + ($InvAnalysis['alloc']);

            $iddocto=$InvAnalysis['id'];
            $Compra="";
            $SQL="select * from supptransdetails where supptransid=".$iddocto;

            $resultsupp = DB_query($SQL,$db);
            if ($myrowsupp = DB_fetch_array($resultsupp, $db)) {
                $Compra = 'OC: '.$myrowsupp['orderno'].' RC:'. $myrowsupp['grns'];	
            } else {
                $Compra = 'SIN';
            }

            $enable="";
            if ($InvAnalysis['hold']==3)//ejecutado
                $enable = "disabled";
        
            $i = count($_POST['selMovimiento']);
            $checked = '';
           for($i=0;$i<=count($_POST['selMovimiento'])-1; $i++){
               if($_POST['selMovimiento'][$i]==$InvAnalysis['id']){
                   $checked = 'checked';
               }
           }
            
            echo "<td class='numero_normal'><INPUT $enable type=checkbox name='selMovimiento[]' value='".$InvAnalysis['id']."' ".$checked." ></td>";
                        
         
            echo '<td class="texto_normal2">'.$InvAnalysis['name'].'</td>
                <td class="texto_normal2" style="background-color:'.$estatusColor[$InvAnalysis['hold']].'">'.$estatusLong[$InvAnalysis['hold']].'</td>
                <td class="texto_normal2">'.$InvAnalysis['supplierno'].'</td>
                <td class="texto_normal2">'.$InvAnalysis['suppname'].'</td>
                <td class="texto_normal2">'.substr($InvAnalysis['suppreference'],0,12).'</td>
                <td class="numero_normal" nowrap>'.$InvAnalysis['trandate'].'</td>
                <td class="numero_normal" nowrap style="background-color:'.$estatusColorVence[$InvAnalysis['EstadoVencimiento']].'">'.$InvAnalysis['duedate'].'</td>
                <td class="texto_normal2">'.$InvAnalysis['promisedate'].' ('.$InvAnalysis['atraso'].')</td>
                <td class="numero_celda">'.'$ '. number_format(($InvAnalysis['ovamount']+$InvAnalysis['ovgst']) - ($InvAnalysis['alloc']),2);

            if($_SESSION['subirxmlprov'] == 1){
                //$liga2="proveedor_ABCDocumentos.php?debtorno=".$InvAnalysis['supplierno']."&propietarioid=".$InvAnalysis['id']."&tipopropietarioid=6&muetraarchivos=1";
                $liga2="SubirXMLProveedor.php?debtorno=".$InvAnalysis['supplierno']."&propietarioid=".$InvAnalysis['id']."&NoOrden=-1&tipopropietarioid=6&muetraarchivos=0";
                echo "<td nowrap class='numero_normal'><a TARGET='_blank' href='" . $liga2 . "'><img src='images/subir_xml_25.png' title='SUBIR XML'></a></td>";
            }
            
            echo '<input type="hidden" name="saldo['.$InvAnalysis['id'].']" value="' . (($InvAnalysis['ovamount']+$InvAnalysis['ovgst']) - ($InvAnalysis['alloc'])) . '">
                    <input type="hidden" name="status['.$InvAnalysis['id'].']" value="' . $InvAnalysis['hold'] . '">
                    <input type="hidden" name="supplierid['.$InvAnalysis['id'].']" value="' . $InvAnalysis['supplierno'] . '">
                    <input type="hidden" name="tagref['.$InvAnalysis['id'].']" value="' . $InvAnalysis['tagref'] . '">
                    <input type="hidden" name="rate['.$InvAnalysis['id'].']" value="' . $InvAnalysis['rate'] . '">
                    <input type="hidden" name="idfactura['.$InvAnalysis['id'].']" value="' . $InvAnalysis['id'] . '">
                    <input type="hidden" name="diffonexch['.$InvAnalysis['id'].']" value="' . $InvAnalysis['diffonexch'] . '">
                    <input type="hidden" name="transno['.$InvAnalysis['id'].']" value="' . $InvAnalysis['transno'] . '">
                    <input type="hidden" name="foliorefe['.$InvAnalysis['id'].']" value="' . $InvAnalysis['suppreference'] . '">
                    </b></td>
                    </tr>';

            if($estatusLong[$InvAnalysis['hold']] == 'Autorizado') {
                $sendMail     = TRUE;
                $mailMessage .= "<td style='padding:.2em'>" . $InvAnalysis['name'] . "</td>";
                $mailMessage .= "<td style='padding:.2em'>" . $InvAnalysis['supplierno'] . "</td>";
                $mailMessage .= "<td style='padding:.2em'>" . $InvAnalysis['suppname'] . "</td>";
                $mailMessage .= "<td style='padding:.2em'>" . substr($InvAnalysis['suppreference'], 0, 12) . "</td>";
                $mailMessage .= "<td style='padding:.2em'>" . $InvAnalysis['trandate'] . "</td>";
                $mailMessage .= "<td style='padding:.2em'>" . $InvAnalysis['duedate'] . "</td>";
                $mailMessage .= "<td style='padding:.2em'>" . $InvAnalysis['promisedate'] . ' (' . $InvAnalysis['atraso'] . ')' . "</td>";
                $mailMessage .= "<td style='padding:.2em'>" . number_format(($InvAnalysis['ovamount'] + $InvAnalysis['ovgst']) - ($InvAnalysis['alloc']), 2) . "</td>";
            }

            /*<td class=peque>'.$InvAnalysis['taxid'].'</td>
            <td class=peque >'.substr($Compra,0,20).'</td>
            <td class=peque >'.substr($InvAnalysis['typename'],0,12).'</td>
            <td class=pequenum>'.'$ '.number_format(($InvAnalysis['ovamount']+$InvAnalysis['ovgst']),2).'</td>
            <td class=pequenum>'.'$ '.number_format(($InvAnalysis['alloc']),2).'</td>
            <td class=peque >'.substr($InvAnalysis['transno'],0,12).'</td>
            */
			
	} /*end while loop */
  	
	if($sendMail && isset($_POST['EnviarEmail']) && empty($_SESSION['FactoryManagerEmail']) == FALSE) {
	
		$mailMessage .= "</table>";
		require_once('./includes/mail.php');
	
		$mail 	= new Mail();
		$to		= "jahepi@gmail.com";
	
		$mail->setTo($_SESSION['FactoryManagerEmail']);
		$mail->setFrom("soporte@tecnoaplicada.com");
		$mail->setSender("Soporte");
		$mail->setSubject("Aprobaci�n de Pagos");
		$mail->setHtml($mailMessage);
		$mail->send();
	}
	
	echo '<tr>';
	/*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
	echo '<td class="pie_derecha" colspan=9>Total General</td>
	      <td class="pie_derecha">'.number_format($TotPagos-$TotAbonos,2).'</td>
	      <td class="pie_derecha"></td>
	      </tr>
	      </table><br> ';
	
	/**************************************/
	/* OPERACIONES */
	echo '<table align="center" bgcolor="#eeeeee" cellspacing=0 bordercolor=#eeeeee border="1">';
	echo '	<tr>';
	//echo '<td></td>';
	echo '		<td class="fecha_titulo" colspan="6">Operaciones</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td class="texto_lista">Cambio de Autorizacion:</td>
				<td>';
		
		echo "<select tabindex='5' name='valorAutorizacion'>";
		echo "<option value='*'>" . _('Seleccione Autorizacion...');
		echo "<option value='pend'>" . _('Pendiente');
		echo "<option value='prog'>" . _('Programado');
		echo "<option value='auth'>" . _('Autorizado');
		echo "<option value='exec'>" . _('Ejecutado');
		echo '</select>';
		
	
        if($pfechaauto == 1){
            echo "</td>";
            echo "<td colspan=4></td>";
            echo "</tr>";
            echo "<tr>";
            echo '<td class="texto_lista">'._('Fecha ').':</td>';
            
            echo "<td>";
                echo '<input type="text" id="datepicker" name="FechaAuto" value="' . $fechaauto . '" READONLY=false> yyyy-mm-dd</input>';
            echo '</td>
	      		<td colspan=4><button type="submit" style="cursor:pointer; border:0; background-color:transparent" name="ProcesaAutorizacion" ><img src="images/procesar_25.png" title="PROCESA CAMBIO" height="20"></button></td>';
            echo "</tr>";
        }else{
            echo '		</td>
	      		<td colspan=4><button type="submit" style="cursor:pointer; border:0; background-color:transparent" name="ProcesaAutorizacion" ><img src="images/procesar_25.png" title="PROCESA CAMBIO" height="20"></button></td>';
            echo "</tr>";
        }
	
//	echo '<tr><td class="texto_lista">Cambio de Promesa:</td>
//				<td nowrap>';
//		
//		echo  '<select Name="PromesaDia">';
//			$sql = "SELECT * FROM cat_Days";
//			$dias = DB_query($sql,$db);
//			while ($myrowdia=DB_fetch_array($dias,$db)){
//			    $diabase=$myrowdia['DiaId'];
//			    if (rtrim(intval($PromesaDia))==rtrim(intval($diabase))){ 
//				echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
//			    }else{
//				echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
//			    }
//			}
//		   echo '</select>';
//		   echo '<select Name="PromesaMes">';
//			     $sql = "SELECT * FROM cat_Months";
//			     $Meses = DB_query($sql,$db);
//			     while ($myrowMes=DB_fetch_array($Meses,$db)){
//				 $Mesbase=$myrowMes['u_mes'];
//				 if (rtrim(intval($PromesaMes))==rtrim(intval($Mesbase))){ 
//				     echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
//				 }else{
//				     echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
//				 }
//			     }
//			     
//			     echo '</select>';
//			     echo '<input name="PromesaYear" type="text" size="4" value='.$PromesaYear.'>';
//		
//	echo '		</td>
//	      		<td colspan=4><button type="submit" style="cursor:pointer; border:0; background-color:transparent" name="ProcesaPromesa" ><img src="images/procesar_25.png" title="PROCESA CAMBIO" height="20"></td>
//	      	</tr>';
	
	echo "	<tr>";
		echo "<td colspan='6'><hr color='#355420' width='70%'></td>";
	echo "	</tr>";
	echo '	<tr>';//
        echo "<td nowrap class='texto_lista'>". _('Selecciona Chequera') . ":</td><td>";
        $SQL = 'SELECT bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode
		FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
		WHERE bankaccounts.accountcode=chartmaster.accountcode 
                AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
                AND tagsxbankaccounts.tagref = sec_unegsxuser.tagref 
                AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"';
	if ($strtags != ""){
            $SQL = $SQL . " and tagsxbankaccounts.tagref in (" . $strtags . ")";
	}//
        if($_POST['unidadnegocio'] <> 0){
            $SQL = $SQL." and tagsxbankaccounts.tagref = '".$_POST['unidadnegocio']."'";
        }
        $SQL = $SQL . " GROUP BY bankaccountname,
                                bankaccounts.accountcode,
                                bankaccounts.currcode";
	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
        echo "<select name='BankAccount' id='BankAccount'>";//<td class=norm colspan=2 nowrap>
        $cont=1;
        $banco='';
        while ($myrow=DB_fetch_array($AccountsResults)){
            if($cont==1)
            {
                $cont=0;
                $banco=$myrow['accountcode'];
            }
            if (!isset($_POST['BankAccount']) AND $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
                $_POST['BankAccount']=$myrow['accountcode'];
            }
            if ($_POST['BankAccount']==$myrow['accountcode']){
                $banco=$myrow['accountcode'];
                echo '<option selected VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
            } else {
                echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
            }
        }
        echo '</select>';
        echo '</td><td>';
        echo "<button type='submit' style='cursor:pointer; border:0; background-color:transparent;' name='btnRetenciones'><img src='images/retenciones_25.png' title='TIENE RETENCIONES' height='20'></button></td>";
        if($cantidad<2){
         if(isset($_POST['btnRetenciones'])){
             echo "<td><table>";
             echo "<tr>";
             echo "<td class='texto_lista'>"._('Retencion de IVA Honorarios:')."</td>";
             echo "<td><input type=text name=retencionivahonorarios value=></td>";
             echo "</tr>";
             echo "<tr>";
             echo "<td class='texto_lista'>"._('Retencion de ISR Honorarios:')."</td>";
             echo "<td><input type=text name=retencionisrhonorarios value=></td>";
             echo "</tr>";
             echo "<tr>";
             echo "<td class='texto_lista'>"._('Retencion de IVA Arrendamiento:')."</td>";
             echo "<td><input type=text name=retencionivaarrendamiento value=></td>";
             echo "</tr>";
             echo "<tr>";
             echo "<td class='texto_lista'>"._('Retencion de ISR Arrendamiento:')."</td>";
             echo "<td><input type=text name=retencionisrarrendamiento value=></td>";
             echo "</tr>";
             echo "<tr>";
             echo "<td class='texto_lista'>"._('Retencion de Fletes:')."</td>";
             echo "<td><input type=text name=retencionfletes value=></td>";
             echo "</tr>";
             echo "<tr>";
             echo "<td class='texto_lista'>"._('Retencion de Cedular:')."</td>";
             echo "<td><input type=text name=retencioncedular value=>
             </td>";
             echo "</tr>";
             echo "</table></td>";
         }else{
             echo "<td></td>";//
         }   
        }
         
        echo "</tr>";
	echo "		<tr>";
        echo "		<td class='texto_lista'>"._('Tipo Pago:')."</td><td><select name='Tipopago' onchange=\"if(this.value=='Cheque'){document.getElementById('numchequeuser').disabled=false;} else if(this.value!='Cheque'){document.getElementById('numchequeuser').disabled=true;}\">";
        if ($_POST['Tipopago'] == 'Cheque'){
            echo "<option selected value='Cheque'>" . _('Cheque') . "</option>";	
	}else{
            echo "<option value='Cheque'>" . _('Cheque') . "</option>";
	}
	if ($_POST['Tipopago'] == 'Transferencia'){
            echo "<option selected value='Transferencia'>" . _('Transferencia') . "</option>";	
	}else{
            echo "<option value='Transferencia'>" . _('Transferencia') . "</option>";
	}
	if ($_POST['Tipopago'] == 'Efectivo'){
            echo "<option selected value='Efectivo'>" . _('Efectivo') . "</option>";	
	}else{
            echo "<option value='Efectivo'>" . _('Efectivo') . "</option>";
	}
        echo "</select>
        	</td>";
        echo "<tr>";
        echo "<td class='texto_lista'>Numero Cheque</td>";
            echo "<td>"; 
                echo "<input title='Este campo  solo funciona cuando esta selleecionado como tipo de pago un cheque. ' type='text' name='numchequeuser' id='numchequeuser' value='".$_POST['numchequeuser']."'>";
            echo "</td>";
        echo "</tr><tr>";
        if($pfechapago == 1){
            echo "<td colspan=2></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td class='texto_lista'>"._('Fecha Pago:')."</td>";
            echo "<td>";//
            echo '<input type="text" id="datepicker2" name="FechaPago" value="' . $fechapago . '" READONLY=false> yyyy-mm-dd</input>';
            echo "</td>";
            echo '		<td colspan=2><button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="GeneraCheque"><img src="images/generar_pa_25.png" title="GENERAR PAGO" height="20"></button></td>';
            echo "</tr>";
            
            echo "	<tr>";
            echo "<td class='texto_lista'>"._('Concepto Poliza:')."</td>";
            echo "<td >";
            echo '<input name="UnificarPagodescripcion" value="'.$_POST['UnificarPagodescripcion'].'" type="text" placeholder="Descripcion">';
            echo "</td>"; 
            echo "<td>"; 
            echo "</td>"; 
            echo "	</tr>";
        }else{
            echo '		<td colspan=2><button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="GeneraCheque"><img src="images/generar_pa_25.png" title="GENERAR PAGO" height="20"></button></td>';
           echo "	</tr>";
        }//
        echo '</table>';
        echo '<table align=center>';
        echo "	<tr>";
        echo "<td colspan=4 style=text-align:center>";
        echo '<input name="UnificarPago" value="Unificar" type="checkbox" id="UnificarPago" onchange="onchangeselect(this.id)">Unificar pago por proveedor';
        echo "</td>";
        echo "	</tr>";
        echo "	<tr>";
        echo "<td colspan=4 style=text-align:center>";
        echo '<input name="UnificarPagoProveedoresDiversos" value="Unificar" type="checkbox" id="UnificarPagoProveedoresDiversos" onchange="onchangeselect(this.id)">Unificar pago proveedor diversos';
        echo "</td>";
        echo "	</tr>";

        
         echo '<tr><td class="texto_lista">' . _('Unidad de Negocio:') . "</td>";
					echo '<td>';
                                            echo '<select name="tagref2" id="tagref" style="width:250px">';
                                                $SQLgr="SELECT tagsxbankaccounts.tagref,tagdescription
                                                        FROM bankaccounts, chartmaster, tagsxbankaccounts
                                                        LEFT JOIN tags on tags.tagref=tagsxbankaccounts.tagref
                                                        WHERE bankaccounts.accountcode=chartmaster.accountcode 
                                                        AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
                                                        AND bankaccounts.accountcode = '".$banco."'";
                                                $resultgr=  DB_query($SQLgr, $db);
                                                
                                                echo '<option value="*">-Seleccione...</option>';
                                                while ($myrowgr=  DB_fetch_array($resultgr)) {
                                                    if ($myrowgr['tagref']==$_POST['tagref2']) {
                                                        echo '<option selected value="'.$myrowgr['tagref'].'">'.$myrowgr['tagdescription'].'</option>';
                                                    }else{
                                                        echo '<option value="'.$myrowgr['tagref'].'">'.$myrowgr['tagdescription'].'</option>';
                                                    }
                                                }
                                            echo '<script type="text/javascript" charset="utf-8">
                                                    $(document).ready(
                                                  function()   
                                                  {
                                                    // Parametros para el combo
                                                     $("#BankAccount").change(
                                                          function () 
                                                          {
                                                        $("#BankAccount option:selected").each(
                                                                  function () 
                                                                  { 
                                                                  elegido=$(this).val();
                                                                  $.post("combo_autoUnidadNegocio.php", { elegido: elegido }, 
                                                                  function(data){
                                                                   $("#tagref").html(data);
                                                                  });     
                                                          }); 
                                                          });     
                                                  });
                                                  </script>';
                                            echo '</select>';
					echo "</td></tr>";
        
        
        
        echo "	<tr>";

        
        $SQL= ' SELECT suppliers.supplierid,chartsupplierstype.gl_unificarpagos as cuenta,suppliers.suppname  as nombre
                FROM suppliers 
                INNER JOIN supplierstype 
                                ON suppliers.typeid = supplierstype.typeid 
                INNER JOIN chartsupplierstype 
                                ON chartsupplierstype.typedebtorid = supplierstype.typeid ';
        $resultadoe=  DB_query($SQL, $db);
        echo "<td ><b>"._('Proveedor Puente:')."</b></td><td><select name='UnificarPagoselect'>";
        while ($row = DB_fetch_array($resultadoe)) {
            if ($_POST['UnificarPagoselect'] == $row['supplierid']){
                   echo "<option selected value='".$row['supplierid']."'>" . _($row['nombre']) . "</option>";	
            }else{
                   echo "<option value='".$row['supplierid']."'>" . _($row['nombre']) . "</option>";
            }
       }    
        echo "</select>
        	</td>";
        echo "</tr>";
        echo "	<tr>";
        echo "<td colspan=4 style=text-align:center>";
        echo "<button type='submit' style='cursor:pointer; border:0; background-color:transparent;' name='EnviarEmail'><img src='images/enviar_correo_25.png' title='ENVIAR CORREO DE NOTIFICAION DE APROBACIONES'></button>";
        echo "</td>";
        echo "</tr>";
        echo '</table>';         

	/* DESPLIEGA TABLA DE RESUMEN DE AUTORIZACIONES*/
	$SQL = "Select CASE WHEN supptrans.duedate < '".$fechaini."' THEN
				0
			ELSE CASE WHEN supptrans.duedate >= '".$fechaini."' and supptrans.duedate <= '".$fechafin."' THEN
				1
			ELSE 2 END END as EstadoVencimiento,
		       sum(supptrans.ovamount/supptrans.rate) as ovamount,
		       sum(supptrans.ovgst/supptrans.rate) as ovgst,
		       sum(supptrans.alloc/supptrans.rate) as alloc
		       
		from supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
		      JOIN tags ON supptrans.tagref = tags.tagref
		      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		      JOIN areas ON tags.areacode = areas.areacode
		      JOIN regions ON areas.regioncode = regions.regioncode
		      JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
		where (abs((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc) > .01) and
		      supptrans.type in ('470','20','34','117','121')
		      and (suppliers.supplierid = '".$_POST['proveedor']."' or '".$_POST['proveedor']."'= '0')
		      and (tags.legalid = '".$_POST['legalid']."' or '".$_POST['legalid']."'= '0')
		      and (areas.regioncode = '".$_POST['xRegion']."' or '".$_POST['xRegion']."'= '0')
		      and (supptrans.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."' = '0')
		      /* and supptrans.duedate >= '".$fechaini."' and supptrans.duedate <= '".$fechafin."' */
		Group By EstadoVencimiento
		Order by EstadoVencimiento";
	
	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Estado General de Cuentas por Pagar') . ' - ' . _('Reporte de Problema') ;
	  //include("includes/header.inc");
	  prnMsg(_('El resumen x aprobacion no se pudo recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');

	   if ($debug==1){
		echo "<br>".$SQL;
	   }
	   exit;
	}
	
	echo '<br>';
	echo '<table border=0>
			<tr>
			<td valign="top">';
	echo '<table cellspacing=0 border=1 align="center" bordercolor=lightgray cellpadding=3>';
	$headerLineaProductos = '	<tr>			
									<th class="titulos_principales" colspan=2>' . _('Resumen Vencimientos') . '</th>			
								</tr>';
			
	echo $headerLineaProductos;
	
	$i = 0;
	While ($InvAnalysis = DB_fetch_array($ReportResult,$db)){
	
		  $primeraEntrada = 0;		
		  if ($i == 0) {
			  echo '<tr class="EvenTableRows" style="background-color:white">';
			  $i = 1;
		  } else {
			  echo '<tr class="OddTableRows" style="background-color:#eeeeee">';
			  $i = 0;
		  }
		
		$TotPagos = $TotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst']);		
		$TotAbonos = $TotAbonos + ($InvAnalysis['alloc']);
			        
		
		echo '	<td class="texto_normal2" style="background-color:'.$estatusColorVence[$InvAnalysis['EstadoVencimiento']].'">'.$estatusLongVence[$InvAnalysis['EstadoVencimiento']].'</td>			
				<td class="numero_celda">'.'$ '.number_format(($InvAnalysis['ovamount']+$InvAnalysis['ovgst']) - ($InvAnalysis['alloc']),2).'</td>
			</tr>';			
			
	} /*end while loop */
	
	echo '</table>';
	echo '</td>';
	echo '<td>&nbsp;</td>';	
	echo '<td>';
	
	/* DESPLIEGA TABLA DE RESUMEN DE AUTORIZACIONES*/
	$SQL = "Select supptrans.hold,
		       sum(supptrans.ovamount/supptrans.rate) as ovamount,
		       sum(supptrans.ovgst/supptrans.rate) as ovgst,
		       sum(supptrans.alloc/supptrans.rate) as alloc
		       
		from supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
		      JOIN tags ON supptrans.tagref = tags.tagref
		      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		      JOIN areas ON tags.areacode = areas.areacode
		      JOIN regions ON areas.regioncode = regions.regioncode
		      JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
		where (abs((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc) > .01) and
		      supptrans.type in ('470','20','34','117','121')
		      and (suppliers.supplierid = '".$_POST['proveedor']."' or '".$_POST['proveedor']."'= '0')
		      and (tags.legalid = '".$_POST['legalid']."' or '".$_POST['legalid']."'= '0')
		      and (areas.regioncode = '".$_POST['xRegion']."' or '".$_POST['xRegion']."'= '0')
		      and (supptrans.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'= '0')
		      /* and supptrans.duedate >= '".$fechaini."' and supptrans.duedate <= '".$fechafin."' */
		Group By supptrans.hold
		Order by supptrans.hold";
	
	$ReportResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Estado General de Cuentas por Pagar') . ' - ' . _('Reporte de Problema') ;
	  //include("includes/header.inc");
	  prnMsg(_('El resumen x aprobacion no se pudo recuperar porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');

	   if ($debug==1){
		echo "<br>".$SQL;
	   }
	   exit;
	}
	
	echo '<table cellspacing=0 border=1 align="center" bordercolor=lightgray cellpadding=3>';
			
	$headerLineaProductos = '	<tr>			
									<th class="titulos_principales" colspan=2>' . _('Resumen de Autorizaci&oacute;n') . '</th>			
								</tr>';
			
	echo $headerLineaProductos;
	
	$i = 0;
	While ($InvAnalysis = DB_fetch_array($ReportResult,$db)){
	
		  $primeraEntrada = 0;		
		  if ($i == 0) {
			  echo '<tr class="EvenTableRows" style="background-color:white">';
			  $i = 1;
		  } else {
			  echo '<tr class="OddTableRows" style="background-color:#eeeeee">';
			  $i = 0;
		  }
		
		$TotPagos = $TotPagos + ($InvAnalysis['ovamount']+$InvAnalysis['ovgst']);		
		$TotAbonos = $TotAbonos + ($InvAnalysis['alloc']);
			        
		
		echo '	<td class="texto_normal2" style="background-color:'.$estatusColor[$InvAnalysis['hold']].'">'.$estatusLong[$InvAnalysis['hold']].'</td>			
				<td class="numero_celda">'.'$ '.number_format(($InvAnalysis['ovamount']+$InvAnalysis['ovgst']) - ($InvAnalysis['alloc']),2).'</td>
			</tr>';			
			
	} /*end while loop */
	echo '</table>';
	
	echo '</td></tr></table>';
	
	if (isset($_POST['PrintEXCEL'])) {
		exit;
	}			
} elseIf (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){


	include('includes/PDFStarter.php');

	$FontSize=12;
	$pdf->addinfo('Title',_('Listado Antiguedad de Saldos'));
	$pdf->addinfo('Subject',_('Antiguedad Saldos Proveedores'));

	$PageNumber=0;
	$line_height=12;

      /*Now figure out the aged analysis for the Supplier range under review */

	if ($_POST['All_Or_Overdues']=='All'){
		$SQL = "SELECT suppliers.supplierid, suppliers.suppname, currencies.currency, paymentterms.terms,
	SUM(supptrans.ovamount + supptrans.ovgst  - supptrans.alloc) as balance,
	SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	ELSE
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	END) AS due,
	Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	ELSE
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') ."), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	END) AS overdue1,
	Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	ELSE
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
	END) AS overdue2
	FROM suppliers, paymentterms, currencies,  supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
	WHERE suppliers.paymentterms = paymentterms.termsindicator
	AND suppliers.currcode = currencies.currabrev
	AND suppliers.supplierid = supptrans.supplierno
	AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
	AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
	AND  suppliers.currcode ='" . $_POST['Currency'] . "'
	AND (supptrans.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'= '0')
	GROUP BY suppliers.supplierid,
		suppliers.suppname,
		currencies.currency,
		paymentterms.terms,
		paymentterms.daysbeforedue,
		paymentterms.dayinfollowingmonth
	HAVING Sum(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) <>0";
	
	} else {

	      $SQL = "SELECT suppliers.supplierid,
	      		suppliers.suppname,
			currencies.currency,
			paymentterms.terms,
			SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			ELSE
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			END) AS due,
			Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			END) AS overdue1,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			END) AS overdue2
			FROM suppliers,
				paymentterms,
				currencies,
				supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE suppliers.paymentterms = paymentterms.termsindicator
			AND suppliers.currcode = currencies.currabrev
			and suppliers.supplierid = supptrans.supplierno
			AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
			AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
			AND suppliers.currcode ='" . $_POST['Currency'] . "'
			AND (supptrans.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'= '0')
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				paymentterms.terms,
				paymentterms.daysbeforedue,
				paymentterms.dayinfollowingmonth
			HAVING Sum(IF (paymentterms.daysbeforedue > 0,
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END,
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END)) > 0";

	}

	$SupplierResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Analisis de Antiguedad de Saldos Proveedores') . ' - ' . _('Reporte de Problema') ;
	  include("includes/header.inc");
	  prnMsg(_('Los detalles de los proveedores no pudieron ser recuperados porque el SQL fallo') .  ' ' . DB_error_msg($db),'error');
	   echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Regrsar al Menu...') . '</a>';
	   if ($debug==1){
		echo "<br>$SQL";
	   }
	   include('includes/footer.inc');
	   exit;
	}

	include ('includes/PDFAgedSuppliersPageHeader.inc');
	$TotBal = 0;
	$TotDue = 0;
	$TotCurr = 0;
	$TotOD1 = 0;
	$TotOD2 = 0;

	While ($AgedAnalysis = DB_fetch_array($SupplierResult,$db)){

		$DisplayDue = number_format($AgedAnalysis['due']-$AgedAnalysis['overdue1'],2);
		$DisplayCurrent = number_format($AgedAnalysis['balance']-$AgedAnalysis['due'],2);
		$DisplayBalance = number_format($AgedAnalysis['balance'],2);
		$DisplayOverdue1 = number_format($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2'],2);
		$DisplayOverdue2 = number_format($AgedAnalysis['overdue2'],2);

		$TotBal += $AgedAnalysis['balance'];
		$TotDue += ($AgedAnalysis['due']-$AgedAnalysis['overdue1']);
		$TotCurr += ($AgedAnalysis['balance']-$AgedAnalysis['due']);
		$TotOD1 += ($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2']);
		$TotOD2 += $AgedAnalysis['overdue2'];

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$AgedAnalysis['supplierid'] . ' - ' . $AgedAnalysis['suppname'],'left');
		$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
		$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
		$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
		$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

		$YPos -=$line_height;
		if ($YPos < $Bottom_Margin + $line_height){
		      include('includes/PDFAgedSuppliersPageHeader.inc');
		}

		if ($_POST['DetailedReport']=='Yes'){

		   $FontSize=6;
		   /*draw a line under the Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);

		   $sql = "SELECT systypescat.typename, supptrans.suppreference, supptrans.trandate,
			   (supptrans.ovamount + supptrans.ovgst - supptrans.alloc) as balance,
			   CASE WHEN paymentterms.daysbeforedue > 0 THEN
			   	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   ELSE
			   	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   END AS due,
			   CASE WHEN paymentterms.daysbeforedue > 0 THEN
			   	CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	   AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   ELSE
			   	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   END AS overdue1,
			   CASE WHEN paymentterms.daysbeforedue > 0 THEN
			   	CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   ELSE
			   	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
			   END AS overdue2
			   FROM suppliers,
			   	paymentterms,
				systypescat, supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			   WHERE systypescat.typeid = supptrans.type
			   AND (supptrans.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'= '0')
			   AND suppliers.paymentterms = paymentterms.termsindicator
			   AND suppliers.supplierid = supptrans.supplierno
			   AND ABS(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) >0.009
			   AND supptrans.settled = 0
			   AND supptrans.supplierno = '" . $AgedAnalysis["supplierid"] . "'";

		    $DetailResult = DB_query($sql,$db,'','',False,False); /*dont trap errors - trapped below*/
		    if (DB_error_no($db) !=0) {
			$title = _('Aged Supplier Account Analysis - Problem Report');
			include('includes/header.inc');
			echo '<br>' . _('The details of outstanding transactions for Supplier') . ' - ' . $AgedAnalysis['supplierid'] . ' ' . _('could not be retrieved because') . ' - ' . DB_error_msg($db);
			echo "<br><a href='$rootpath/index.php'>" . _('Back to the menu') . '</a>';
			if ($debug==1){
			   echo '<br>' . _('The SQL that failed was') . '<br>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		    }

		    while ($DetailTrans = DB_fetch_array($DetailResult)){

			    $LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,60,$FontSize,$DetailTrans['typename'],'left');
			    $LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,50,$FontSize,$DetailTrans['suppreference'],'left');
			    $DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
			    $LeftOvers = $pdf->addTextWrap($Left_Margin+105,$YPos,70,$FontSize,$DisplayTranDate,'left');

			    $DisplayDue = number_format($DetailTrans['due']-$DetailTrans['overdue1'],2);
			    $DisplayCurrent = number_format($DetailTrans['balance']-$DetailTrans['due'],2);
			    $DisplayBalance = number_format($DetailTrans['balance'],2);
			    $DisplayOverdue1 = number_format($DetailTrans['overdue1']-$DetailTrans['overdue2'],2);
			    $DisplayOverdue2 = number_format($DetailTrans['overdue2'],2);

			    $LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			    $LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
			    $LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
			    $LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
			    $LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

			    $YPos -=$line_height;
			    if ($YPos < $Bottom_Margin + $line_height){
				$PageNumber++;
				include('includes/PDFAgedSuppliersPageHeader.inc');
				$FontSize=6;
			    }
		    } /*end while there are detail transactions to show */
		    /*draw a line under the detailed transactions before the next Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);
		   $FontSize=8;
		} /*Its a detailed report */
	} /*end Supplier aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include('includes/PDFAgedSuppliersPageHeader.inc');
	} elseif ($_POST['DetailedReport']=='Yes') {
		//dont do a line if the totals have to go on a new page
		$pdf->line($Page_Width-$Right_Margin, $YPos+10 ,220, $YPos+10);
	}

	$DisplayTotBalance = number_format($TotBal,2);
	$DisplayTotDue = number_format($TotDue,2);
	$DisplayTotCurrent = number_format($TotCurr,2);
	$DisplayTotOverdue1 = number_format($TotOD1,2);
	$DisplayTotOverdue2 = number_format($TotOD2,2);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');
	$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayTotCurrent,'right');
	$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayTotDue,'right');
	$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayTotOverdue1,'right');
	$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayTotOverdue2,'right');

	$YPos -=$line_height;
	$pdf->line($Page_Width-$Right_Margin, $YPos ,220, $YPos);

	$buf = $pdf->output();
	$len = strlen($buf);
	header('Content-type: application/pdf');
	header("Content-Length: $len");
	header('Content-Disposition: inline; filename=AgedSuppliers.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();

} else { /*The option to print PDF was not hit */
	

} /*end of else not PrintPDF */


function GenerarNotadeCargo($SupplierID,$concepto,$Moneda,$monto,$cuenta_notacredito,$type,$tagref,$taxcatid,$transnot,$PeriodNo,$ctaxtipoproveedor,$db,$facturasafectadas){
   
    $InputError = 0;
    if (strlen($SupplierID)==0 or $SupplierID=='')
    {
        prnMsg( _('Seleccione el Proveedor al que se le aplicara la nota de credito'),'error');
        $InputError=1;
    }
    if ($concepto=='')
    {
        prnMsg( _('El concepto de la Nota de Credito no puede ir vac�o. Capturar Concepto'),'error');
        $InputError=1;
    }
    if ($Moneda=='')
    {
        prnMsg( _('Especifique la moneda con la que se realizara la transaccion'),'error');
        $InputError=1;
    }
    
    if ($monto<=0)
    {
        prnMsg( _('El monto de la Nota de Credito no puede ser menor o igual a CERO. Capturar un monto mayor a CERO'),'error');
        $InputError=1;
    }
    
    if ($cuenta_notacredito=='')
    {
        prnMsg( _('Especifique la cuenta de credito a la que va a afectar la nota de credito'),'error');
        $InputError=1;
    }
   
    if ($InputError!=1)
      {
        # Obtiene el trans no que le corsesponde en base al tagref y al $systype_doc
        //$transno = GetNextTransNo(32,$db);
        
	$taxrate = 0;
	$montoiva=0;
	
	$rate=1;
        $monedanota="MXN";
        $sqlmoneda="SELECT *
		FROM currencies
		WHERE currabrev='" . $Moneda."'";
        $Result2 = DB_query($sqlmoneda,$db);
        $myrow = DB_fetch_row($Result2);
        //$rate = $myrow[4];
        $monedanota=$myrow[1]; 
	$rate = (1/1);
		$sqliva="SELECT taxrate,purchtaxglaccount,purchtaxglaccountPaid
			 FROM taxauthrates, taxauthorities
			WHERE taxauthrates.taxauthority=taxauthorities.taxid
			AND taxauthrates.taxcatid ='".$taxcatid."'";
		$Result2 = DB_query($sqliva,$db);
		$myrow = DB_fetch_row($Result2);
		$taxrate = $myrow[0];
		$taxglcode=$myrow[1];
		$taxglcodepaid=$myrow[2];
	
	
	
	
	//calcula iva y desglosa de iva
	//$montoiva=$monto*($taxrate);
	//$monto=$monto-$montoiva;
        
        $montosiniva=$monto/(1+$taxrate);
	$montoiva = $monto - $montosiniva;

        # Datos del Periodo y fecha ***
          $DefaultDispatchDate=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
          //$PeriodNo = GetPeriod($DefaultDispatchDate, $db,$tagref);
        
          $DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
        # *****************************
        $Result = DB_Txn_Begin($db);    
        # Realiza el insert en la tabla de debtortrans
	//$rate=1;
		$SQL = 'INSERT INTO supptrans ( type,
						transno,
						tagref,
						supplierno,
						suppreference,
						trandate,
						duedate,
						ovamount,
						ovgst,
						rate,
						transtext,
						origtrandate,currcode, hold
						)
			VALUES ('.$type.',
				'.$transnot.',
				'.$tagref.",
				'".$SupplierID."',
				'".$facturasafectadas."',
				now(),
				now(),
				".($monto/$rate) . ',
				0, 
				'.$rate.",
				'".$concepto."', now(),'" . $Moneda . "',2)";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
		$DbgMsg = _('El SQL utilizado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');
		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		if (isset($_POST['TaxCat']) and $_POST['TaxCat']!=""){
			$SQL = 'INSERT INTO supptranstaxes (supptransid,
							taxauthid, 
							taxamount)
				VALUES (' . $SuppTransID . ',
					' . $_POST['TaxCat'] . ',
					' . ($montoiva/$rate) . ')';
		
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
 			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
        # ***************************************************************************************************
        # **** AFECTACIONES CONTABLES ****
	# ***************************************************************************************************
            
            # Obtiene la cuentas contables que se afectar�n
            # *****************************************
            # Se afecta la cuenta de CxC
            # *****************************************
            //OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
            //$cuenta_cxc=$_SESSION['CompanyRecord']['creditorsact'];
	     /* if ($montoiva!=0 and 1==2) {
		      $SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag
				)
			VALUES (
				" . $type . ",
				" . $transnot . ", 
				now(),
				" . $PeriodNo . ",
				'" . $taxglcode . "',
				'" . $SupplierID. " No. de Nota de Cargo: ". $transnot . " @".$nombrecliente." ',
				" . ($montoiva/$rate) . ",
				" . $tagref . "
			)";
			$Result = DB_query($SQL ,$db);  
		} */
		# Obtiene la cuentas contables que se afectar�n
		#$cuenta_notacredito=$_SESSION['CompanyRecord']['gllink_creditors'];
                # *****************************************
                # Se afecta la cuenta de CxC
                # *****************************************
                    $SQL = "INSERT INTO gltrans (
                            type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative,
                            amount,
                            tag
                            )
                    VALUES (
                            " . $type . ",
                            " . $transnot . ",
                            now(),
                            " . $PeriodNo . ",
                            '" . $ctaxtipoproveedor . "',
                            '" . $SupplierID. " No. de Nota de Cargo: ". $transnot . " @".$nombrecliente." ',
                            " . -($monto/$rate) . ",
                            " . $tagref . "
                    )";
                    #echo $SQL;
                    $Result = DB_query($SQL ,$db);
                                        
                # *****************************************
                # Se afecta la cuenta de Notas de Credito
                # *****************************************
                  // afecta cuentas de notas de credito    
                    $SQL = "INSERT INTO gltrans (
                            type,
                            typeno,
                            trandate, 
                            periodno,
                            account,
                            narrative,
                            amount,
                            tag
                            )
                    VALUES (
                            ".$type.",
                            ".$transnot.",
                            now(),
                            " . $PeriodNo . ",
                            '" . $cuenta_notacredito . "',
                            '" . $SupplierID . " No. de Nota de Cargo: ". $transnot . " @".$nombrecliente." ',
                            " . (($monto/$rate)) . ",
                            " . $tagref . "
                    )";
                    $msgexito = '<b>LA FACTURA GENERADO EXITOSAMENTE...';
                    $Result = DB_query($SQL ,$db,$msgexito);
		    /*
                    if ($montoiva!=0) {
			$SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag
				)
			VALUES (
				" . $systype_doc . ",
				" . $transno . ",
				'" . $DefaultDispatchDate . "',
				" . $PeriodNo . ",
				" . $taxglcodepaid . ",
				'" . $SupplierID. " No. de Nota de Cargo: ". $transno . " @".$nombrecliente." ',
				" . (($montoiva/$rate)*-1) . ",
				" . $tagref . "
			)";
			$msgexito = '<b>LA NOTA DE CARGO SE HA GENERADO EXITOSAMENTE...';
			$Result = DB_query($SQL ,$db,$msgexito);  
		      
		    }
                    */
		    $Result = DB_Txn_Commit($db);
                   // prnMsg(_($msgexito),'success');
                    
                    //$liga = "PDFCreditDirectSupplier.php";
		    //$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID . '?tipo='.$systype_doc.'&area='.$area.'&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'">'. _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' .'</a>';
                
                    //echo '<p><div align="center">';
                     // echo $liga;
                    //echo '</div>'; 
                    
		    
		    
	      /*
  		echo '<p><div align="center">';
		  echo $liga;
		echo '</div>'; 
		//Actualizar el documento para folio
		$SQL="UPDATE debtortrans
		      SET folio='" . $serie.'|'.$folio . "'
		      WHERE transno=".$transno." and type=".$systype_doc;
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
		$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	      */    
        # ***************************************************************************************************
        # ***************************************************************************************************
        # ***************************************************************************************************       
      
                    return $SuppTransID;
              }
              return 'false'; 
      
}


include('includes/footer.inc');

?> 
<script>
    function onchangeselect(id)
    {
        var item=document.getElementById(id).checked;
        if (item==true)
        {
        if ('UnificarPago'==id)
        document.getElementById('UnificarPagoProveedoresDiversos').checked=false;
        else
        document.getElementById('UnificarPago').checked=false;
        }
    }
    </script>