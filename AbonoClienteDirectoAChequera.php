<?php
$PageSecurity = 4;
include('includes/session.inc');
$title = _('Abono a Cliente Directo a Cuenta de Cheques');
include('includes/header.inc');
$funcion=960;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include ('Numbers/Words.php');
include('includes/Functions.inc');
$XSAInvoicing="XSAInvoicing.inc";
$SendInvoicing="SendInvoicingV6_0.php";
include ('includes/'.$XSAInvoicing);
include ('includes/'.$SendInvoicing);

//error_reporting(E_ALL);
//ini_set('display_errors', '1');
//echo '<pre>'.print_r($_POST);

if (isset($_POST['DebtorNo'])){
    $DebtorNo = $_POST['DebtorNo'];
}else{
    $DebtorNo = $_GET['DebtorNo'];
}

if (isset($_POST['tagref'])){
  $tagref = $_POST['tagref'];
}else{
  $tagref = $_GET['tagref'];
}

if (isset($_POST['concepto'])){
  $concepto = trim($_POST['concepto']);
}else{
  $concepto = trim($_GET['concepto']);
}

if (isset($_POST['monto'])){
  $monto = $_POST['monto'];
}else{
  $monto = $_GET['monto'];
}

if (isset($_POST['GLCode'])){
  $cuenta_notacredito = $_POST['GLCode'];
}else{
  $cuenta_notacredito = $_GET['GLCode'];
}

if ($_SERVER['SERVER_NAME'] == "erp.servillantas.com"){
	$ambiente = "produccion";
}else{
	$ambiente = "desarrollo";
}

if ($monto==''){
  $monto=0;
}

/* OBTENGO FECHAS*/
if (isset($_POST['FromYear'])) {
	$FromYear=$_POST['FromYear'];
} else {
	$FromYear=date('Y');
}

if (isset($_POST['FromMes'])) {
    $FromMes=$_POST['FromMes'];
} else {
    $FromMes=date('m');
}

if (isset($_POST['FromDia'])) {
    $FromDia=$_POST['FromDia'];
} else {
    $FromDia=date('d');
}
$fecha = $FromYear . "-" . add_ceros(rtrim($FromMes),2) . "-" . add_ceros(rtrim($FromDia),2);
$fechaperiodo = add_ceros(rtrim($FromDia),2) . "/" . add_ceros(rtrim($FromMes),2) . "/" .  $FromYear;

if ($_POST['moneda']!=$_SESSION['CountryOfOperation']){
    $ratepago=GetCurrencyRateByDate($fecha,$_POST['moneda'],$db);
    if($ratepago!=0){
        $tipocambio=number_format(1/$ratepago,4);
    }
    if(isset($_POST['tipocambio']) && $_POST['tipocambio']>1 ){
	$tipocambio=number_format($_POST['tipocambio'],4);
    }
}else{
    $tipocambio=1;
}

if($tipocambio==''){
	$tipocambio=1;
}

$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
$fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
$fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);

$InputError = 0;

if(isset($_POST['GeneraAnt'])){
    $systype_doc = 85;
}else{
    $systype_doc=200;
}

$sql='SELECT name';
$sql.=' FROM debtorsmaster';
$sql.=' WHERE debtorno="'.$DebtorNo.'"';
$sql;
$result = DB_query($sql ,$db);
$myrow = DB_fetch_row($result);
$nombrecliente=$myrow[0];

if ($_POST['procesar']=='PROCESAR'){
    $InputError = 0;
    
    if (strlen($DebtorNo)==0 or $DebtorNo==''){
        prnMsg( _('Seleccione el cliente al que se le aplicara el Abono Directo a Cuenta de Cheques'),'error');
        $InputError=1;
    }
    
    if ($concepto==''){
        prnMsg( _('El concepto del Abono Directo a Cuenta de Cheques no puede ir vac�o. Capturar Concepto'),'error');
        $InputError=1;
    }
    
    if ($monto<=0){
        prnMsg( _('El monto del Abono Directo a Cuenta de Cheques no puede ser menor o igual a CERO. Capturar un monto mayor a CERO'),'error');
        $InputError=1;
    }
    
    if ($cuenta_notacredito==''){
        prnMsg( _('Especifique la cuenta de banco a la que va a afectar del Abono Directo a Cuenta de Cheques'),'error');
        $InputError=1;
    }

    if ($tagref=='0'){
    	prnMsg( _('Se debe de seleccionar una Unidad de Negocio'),'error');
    	$InputError=1;
    }

    if ($_POST['GLCode']=='0'){
    	prnMsg( _('Se debe de seleccionar una cuenta'),'error');
    	$InputError=1;
    }
	
    $sqlval = " SELECT *
                FROM bankaccounts
                WHERE bankaccounts.accountcode = '".$_POST['GLCode']."'
                AND bankaccounts.currcode = '".$_POST['moneda']."'";
    
    $resultval = DB_query($sqlval, $db);
    $myrowCH=DB_fetch_array($resultval,$db);
    $monedaChequera = $myrowCH['currcode'];   
    
    if(DB_num_rows($resultval) == 0){
    	prnMsg('La moneda seleccionada no corresponde con la moneda de la cuenta del banco');
    	$InputError=1;
    }
    
    /*Valida que la clave presupuestal no venga vacia*/
    if(!isset($_POST['clavepresupuestal_1']) or empty($_POST['clavepresupuestal_1']) or strlen($_POST['clavepresupuestal_1'] < 80)){
        prnMsg('No agrego clave presupuestal, favor de verificarlo','error');
        $InputError=1;
    }
    
    /*Valida que la clave presupuestal seleccionada corresponda a la unidad de negocio*/
    $SQLiva = "SELECT *
                FROM chartdetailsbudgetbytag
                WHERE accountcode = '".$_POST['clavepresupuestal_1']."'
                AND tagref = '".$tagref."'";
    
    $result = DB_query($SQLiva, $db);
    if(DB_num_rows($result) == 0){
        prnMsg('la clave presupuestal no corresponde con la dependencia seleccionada','error');
        $InputError=1;
    }
   
    if ($InputError!=1) {
      	
        # Obtiene el trans no que le corsesponde en base al tagref y al $systype_doc
        $transno = GetNextTransNo($systype_doc,$db);
        $taxrate = 0;
        $montoiva=0;
        $rate = (1/$tipocambio);
        if (isset($_POST['TaxCat']) and $_POST['TaxCat']!=""){
            $sqliva="SELECT taxrate,
                            taxglcode,taxglcodepaid
                    FROM taxauthrates, taxauthorities
                    WHERE taxauthrates.taxauthority=taxauthorities.taxid
                    AND taxauthrates.taxcatid =" . $_POST['TaxCat'];
            $result = DB_query($sqliva,$db);
            $myrow = DB_fetch_row($result);
            $taxrate = $myrow[0];
            $taxglcode=$myrow[1];
            $taxglcodepaid=$myrow[2];
        }

        //calcula iva y desglosa de iva
        $montosiniva=$monto/(1+$taxrate);
        $montoiva=$monto- $montosiniva;
	    
        $PeriodNo = GetPeriod($fechaperiodo, $db, $tagref);
        $DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
        
       // EXTRAE TERMINO DE PAGO
        $sqlapl = "SELECT AplPagosTimbrar.flagstimbrar
                    FROM AplPagosTimbrar
                    WHERE AplPagosTimbrar.idaplpagostimbrar = '".$_POST['aplparcial']."'";
        $resultpar = DB_query($sqlapl, $db);
        $myrowpar = DB_fetch_array($resultpar);
        $timbrar=$myrowpar['flagstimbrar'] ;
        
        if($myrowpar['flagstimbrar'] == 1){          
            $totpagos=$_POST['noparcialidad'];
            if ($totpagos>1){
                $terminospago = "Pago parcial 1 de ".$totpagos;
            }else{
                $terminospago="Pago en una sola exhibicion";
            }
        }else{
            $terminospago="Pago en una sola exhibicion";
        }
          
        # Realiza el insert en la tabla de debtortrans
	$SQL= "INSERT INTO debtortrans (tagref,
                                        transno,
                                        type,
                                        debtorno,
                                        branchcode,
                                        origtrandate,
                                        trandate,
                                        prd,
                                        settled,
                                        reference,
                                        tpe,
                                        order_,
                                        rate,
                                        ovamount,
                                        ovgst,
                                        ovfreight,
                                        ovdiscount,
                                        diffonexch,
                                        alloc,
                                        invtext,
                                       shipvia,
                                       edisent,
                                       consignment,
                                       folio,
                                       ref1,
                                       ref2,
                                       currcode,
                                       observf)";
        $SQL.= " VALUES ('".$tagref."',
                        '".$transno."',
                        '".$systype_doc."',
                        '".$DebtorNo."',
                        '".$DebtorNo."',
                        '" .$fecha."',
                        '".$fecha."',
                        '".$PeriodNo."',
                        '0',
                        '".$concepto."',
                        '',
                        '0',
                        '".$rate."',
                        '".($montosiniva*-1)."',
                        '".($montoiva*-1)."',
                        '0',
                        '0',
                        '0',
                        '0',
                        '',
                        '1',
                        '0',
                        '', 
                        'PDR',
                        'PDR',
                        'PDR',
                        '".$_POST['moneda']."',
                        '".$terminospago."')";
        $result = DB_query($SQL ,$db);
        $DebtorID = DB_Last_Insert_ID($db,'debtortrans','id');
        # ***************************************************************************************************
        # **** AFECTACIONES CONTABLES ****
        # ***************************************************************************************************
        # Obtiene la cuentas contables que se afectar�n
        # *****************************************
        # Se afecta la cuenta de CxC
        # *****************************************/
	$tipocliente = ExtractTypeClient($DebtorNo,$db);
	if(isset($_POST['GeneraAnt'])){
            $cuenta_cxc = ClientAccount($tipocliente,"gl_debtoradvances",$db);
	}else{
            $cuenta_cxc = ClientAccount($tipocliente,"gl_accountsreceivable",$db);
        }
		
        $rmontosiniva = ($montosiniva/$rate);
        $rmontoiva = ($montoiva/$rate);
        $rmonto = ($monto/$rate);

        if ($montoiva!=0 and $_POST['TaxCat']!=5) {//

            $SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag,
                                userid,
                                dateadded)
                    VALUES (
				'" . $systype_doc . "',
				'" . $transno . "',
				'" . $fecha . "',
				'" . $PeriodNo . "',
				'" . $taxglcode . "',
				'" . $DebtorNo. "- No. de Abono Directo a Cuenta Cheques: ". $transno . " @".$nombrecliente." Concepto:".$concepto."',
				'" . ($rmontoiva) . "',
				'" . $tagref . "',
                                '".$_SESSION['UserID']."',
                                NOW())";
            $result = DB_query($SQL ,$db);
            
            $SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag,
                                userid,
                                dateadded)
			VALUES (
				" . $systype_doc . ",
				" . $transno . ",
				'" . $fecha . "',
				" . $PeriodNo . ",
				'" . $taxglcodepaid . "',
				'" . $DebtorNo. "- No. de Abono Directo a Cuenta Cheques: ". $transno . " @".$nombrecliente." Concepto:".$concepto." ',
				" . -($rmontoiva) . ",
				" . $tagref . ",
                                '".$_SESSION['UserID']."',
                                NOW())";
            $result = DB_query($SQL ,$db);
             echo 'sql iva'.$SQL;//
        } 
	    
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
                            tag,
                            userid,
                            dateadded)
                VALUES(
                            " . $systype_doc . ",
                            " . $transno . ",
                            '" . $fecha . "',
                            " . $PeriodNo . ",
                            '" . $cuenta_cxc . "',
                            '" . $DebtorNo. "- No. de Abono Directo a Cuenta Cheques: ". $transno . " @".$nombrecliente." Concepto:".$concepto." ',
                            " . ($rmonto*-1) . ",
                            " . $tagref . ",
                            '".$_SESSION['UserID']."',
                            NOW())";
        $result = DB_query($SQL ,$db);
                                        
        # *****************************************
        # Se afecta la cuenta de Notas de Credito
        # *****************************************
                
        $DebtorTransID = DB_Last_Insert_ID($db,'gltrans','counterindex');
                 
        $SQL= "INSERT INTO debtortransmovs(
                                            tagref,
                                            transno,
                                            type,
                                            debtorno,
                                            branchcode,
                                            origtrandate,
                                            trandate,
                                            prd,
                                            settled,
                                            reference,
                                            tpe,
                                            order_,
                                            rate,
                                            ovamount,
                                            ovgst,
                                            ovfreight,
                                            ovdiscount,
                                            diffonexch,
                                            alloc,
                                            invtext,
                                            shipvia,
                                            edisent,
                                            consignment,
                                            folio,
                                            ref1,
                                            ref2,
                                            currcode,
                                            idgltrans,
                                            userid)";
        $SQL.= " VALUES (
                        '".$tagref."',
                        '".$transno."',
                        '".$systype_doc."',
                        '".$DebtorNo."',
                        '".$DebtorNo."',
                        '" . $fecha . "',
                        now(),
                        '".$PeriodNo."',
                        '0',
                        '".$concepto."',
                        '',
                        '0',
                        '1',
                        '".($montosiniva*-1)."',
                        '".($montoiva*-1)."',
                        '0',
                        '0',
                        '0',
                        '0',
                        '',
                        '1',
                        '0',
                        '', 
                        'PDR',
                        'PDR',
                        'PDR',
                        'MXN',
                        '".$DebtorTransID."',
                        '".$_SESSION['UserID']."')";
        $result = DB_query($SQL ,$db);

        // afecta cuentas de notas de credito    
        $SQL = "INSERT INTO gltrans (
                            type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative,
                            amount,
                            tag,
                            userid,
                            dateadded
                            )
                    VALUES (
                            '" . $systype_doc . "',
                            '" . $transno . "',
                            '" . $fecha . "',
                            '" . $PeriodNo . "',
                            '" . $cuenta_notacredito . "',
                            '" . $DebtorNo. "- No. de Abono Directo a Cuenta Cheques: ". $transno . " @".$nombrecliente." Concepto:".$concepto." ',
                            '" . $rmonto . "',
                            '" . $tagref . "',
                            '".$_SESSION['UserID']."',
                            NOW())";
        $msgexito = '<b>EL ABONO DIRECTO A CUENTA DE CHEQUES SE HA GENERADO EXITOSAMENTE...';
        $result = DB_query($SQL ,$db, $msgexito);
		     
        //INSERTA EN BANKTRANS
        $narrative = "Abono a cliente: " . $nombrecliente.' Concepto: '.$concepto;
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
	$SQL= $SQL . "VALUES ('" . $transno . "',
                            '" . $systype_doc . "',
                            '" . $cuenta_notacredito . "',
                            '" . $narrative . "',
                            1,
                            1,
                            '" . $fecha . "',
                            'ABONO DIRECTO',
                            '" . $rmonto . "',
                            '".$_POST['moneda']."',
                            '" . $tagref . "',
                            '" . $nombrecliente . "','0')";
        
	$ErrMsg = _('No pude insertar la transaccion bancaria porque');
	$DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		//    
        // funcion para generar los momentos presupuestales
        $resultado= GeneraMovimientoContablePresupuesto($systype_doc, "INGRESO_RECAUDADO", "INGRESO_DEVENGADO", $transno, $PeriodNo, $rmonto, $tagref, $fecha, $_POST['clavepresupuestal_1'], $DebtorID, $db);
	prnMsg(_($msgexito),success);
                    
	// imprimir datos de la nota de credito
        if (!isset($legaid) or $legaid=='' or !isset($area) or $area=='') {
            $sql="  SELECT legalid,
                            areacode 
                        FROM tags 
                        WHERE tagref=".$tagref;
            $result = DB_query($sql, $db);
            while ($myrow=DB_fetch_array($result,$db)) {
                $legaid=$myrow['legalid'];
                $area=$myrow['areacode'];
            }
        }
		    
	// Consulta el rfc y clave de facturacion electronica
	$SQL="  SELECT l.taxid,
                    l.address5,
                    t.tagname,
                    legalname,
                    typeinvoice
                FROM legalbusinessunit l, tags t
                WHERE l.legalid=t.legalid 
                AND tagref='".$tagref."'";
        $Result= DB_query($SQL,$db);
        if (DB_num_rows($Result)==1) {
            $myrowtags = DB_fetch_array($Result);
            $rfc=trim($myrowtags['taxid']);
            $keyfact=trim($myrowtags['address5']);
            $nombre=trim($myrowtags['tagname']);
            $legalname=trim($myrowtags['legalname']);
            $tipofacturacionxtag=$myrowtags['typeinvoice'];
        }

	if($timbrar==1 and ($tipofacturacionxtag<>2 and $tipofacturacionxtag<>1 and $tipofacturacionxtag<>3)){
	
            if($_SESSION['DocumentNextByType']==1){
                $InvoiceNoTAG = DocumentNextByType(12, $tagref,$area,$legaid, $db);
            }else{
                $InvoiceNoTAG = DocumentNext(12, $tagref,$area,$legaid, $db);
            }	
                            
            $separa = explode('|',$InvoiceNoTAG);
            $serie = $separa[1];
            $folio = $separa[0];
            $OrderNo=0;
            $factelectronica= XSAInvoicingCreditdirect($transno, $OrderNo , $DebtorNo, $systype_doc,$tagref,$serie,$folio, $db);
            // Envia  los datos al archivooooo
            $factelectronica=utf8_encode($factelectronica);
            $empresa=$keyfact.'-'.$rfc;
            $nombre=$nombre;
            $factelectronica=$factelectronica;
            $param=array('in0'=>$empresa, 'in1'=>$nombre,'in2'=>$tipo,'in3'=>$myfile,'in4'=>$factelectronica);
            if ($tipofacturacionxtag==1){
		try{
                    $client = new SoapClient($_SESSION['XSA']."xsamanager/services/FileReceiverService?wsdl");
                    $codigo=$client->guardarDocumento($param);
                }catch (SoapFault $exception) {
                    $errorMessage = $exception->getMessage();
                }
                $liga=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
                $liga='<p>' . ' ' . '<a  target="_blank" href="' .$liga . '">'. _('') . ' (' . _('') . ')' .'<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt=""></a>';
            } elseif($tipofacturacionxtag==2){
                $arrayGeneracion=generaXML($factelectronica,'egreso',$tagref,$serie,$folio,$DebtorID,'NCreditoDirect',$OrderNo,$db);
                $XMLElectronico=$arrayGeneracion["xml"];
                //Se agrega la generacion de xml_intermedio
                $array=generaXMLIntermedio($factelectronica,$XMLElectronico,$arrayGeneracion["cadenaOriginal"],utf8_encode($arrayGeneracion["cantidadLetra"]), $DebtorID,$db,13,$tagref);
                $xmlImpresion= utf8_decode($array["xmlImpresion"]);
		$rfcEmisor=$array["rfcEmisor"];
		$fechaEmision=$array["fechaEmision"];
		//Almacenar XML
		$flagsendfiscal=1;
		$query="INSERT INTO Xmls(transNo,
                                        type,
                                        rfcEmisor,
                                        fechaEmision,
                                        xmlSat,
                                        xmlImpresion,
                                        fiscal)
			VALUES('".$transno."',
                                '".$systype_doc."',
                                '".$rfcEmisor."',
                                '".$fechaemision."',
                                '".$XMLElectronico."',
                                '".$xmlImpresion."'
                                '".$flagsendfiscal."');";
                $Result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);
		
                $liga="PDFInvoice.php";
		$liga='<p>' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID . '?Type='.$systype_doc.'&tipodocto=1&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'&OrderNo='.$OrderNo.'">'. _('') . ' ' . _('Laser') . '' .'<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt=""></a>';
                
            }elseif($tipofacturacionxtag==3){
	
                $liga = "PDFNoteCreditDirectTemplate.php";
		$liga='<p>' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID . '&tipo='.$systype_doc.'&area='.$area.'&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'">'. _('') . ' ' . _('') . '' .'<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt=""></a>';
            }elseif($tipofacturacionxtag==4){
			    		
                $success= false;
		$config	= $_SESSION;
		$arrayGeneracion = generaXMLCFDI($factelectronica,'egreso',$tagref,$serie,$folio,$DebtorID,'NCreditoDirect',$OrderNo,$db);
		$XMLElectronico=$arrayGeneracion["xml"];
		$XMLElectronico = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $XMLElectronico);
		include_once 'timbradores/TimbradorFactory.php';
		$timbrador = TimbradorFactory::getTimbrador($config);
		if($timbrador != null) {
                    $timbrador->setRfcEmisor($rfc);
                    $timbrador->setDb($db);
                    $cfdi = $timbrador->timbrarDocumento($XMLElectronico);
                    $success = ($timbrador->tieneErrores() == false);
		
                    foreach($timbrador->getErrores() as $error) {
			prnMsg($error, 'error');
                    }
		} else {
                    prnMsg(_('No hay un timbrador configurado en el sistema'), 'error');
		}
			    		
		if($success) {
                    $DatosCFDI = TraeTimbreCFDI($cfdi);
                    if (strlen($DatosCFDI['FechaTimbrado'])>0){
                        $cadenatimbre='||1.0|'.$DatosCFDI['UUID'].'|'.$DatosCFDI['FechaTimbrado'].'|'.$DatosCFDI['selloCFD'].'|'.$DatosCFDI['noCertificadoSAT'].'||';
                        $sql="UPDATE debtortrans
				SET fechatimbrado='".$DatosCFDI['FechaTimbrado']."',
                                    uuid='".$DatosCFDI['UUID']."',
                                    timbre='".$DatosCFDI['selloSAT']."',
                                    cadenatimbre='".$cadenatimbre."'
				WHERE id=".$DebtorID;
			$ErrMsg=_('El Sql que fallo fue');
			$DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
			$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$XMLElectronico = $cfdi;
			//Guardamos el XML una vez que se agrego el timbre fiscal
			$carpeta='NCreditoDirect';
			$dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/XML/".$carpeta."/";
			$nufa = $serie.$folio;
			$mitxt=$dir.$nufa.".xml";
			unlink($mitxt);
			$fp = fopen($mitxt,"w");
			fwrite($fp,$XMLElectronico);
			fclose($fp);
			$fp = fopen($mitxt . '.COPIA',"w");
			fwrite($fp,$XMLElectronico);
			fclose($fp);
			//Se agrega la generacion de xml_intermedio
			$array=generaXMLIntermedio($factelectronica,$XMLElectronico,$arrayGeneracion["cadenaOriginal"],utf8_encode($arrayGeneracion["cantidadLetra"]), $DebtorID,$db,13,$tagref);
			$xmlImpresion= utf8_decode($array["xmlImpresion"]);
			$rfcEmisor=$array["rfcEmisor"];
			$fechaEmision=$array["fechaEmision"];
			//Almacenar XML
			$flagsendfiscal=1;
			$query="INSERT INTO Xmls(transNo,
                                                type,
                                                rfcEmisor,
                                                fechaEmision,
                                                xmlSat,
                                                xmlImpresion,
                                                fiscal)
				VALUES('".$transno."',
                                        '".$systype_doc."',
                                        '".$rfcEmisor."',
                                        '".$fechaEmision."',
                                        '".$XMLElectronico."',
                                        '".$xmlImpresion."',
                                        '".$flagsendfiscal."');
                        $Result= DB_query($query,$db,$ErrMsg,$DbgMsg,true)";
			$liga="PDFInvoice.php";
			$liga='<p>' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID . '?Type='.$systype_doc.'&tipodocto=1&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'&OrderNo='.$OrderNo.'">'. _('') . ' ' . _('') . '' .'<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt=""></a>';
                    }else{
			prnMsg(_('No fue posible realizar el timbrado del documento, verifique con el administrador; el numero de error es:').$cfdi,'error');
                    }
		}
            } else {
		$liga='PDFCreditDirect.php';
		$liga='<p>' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID . '&tipo='.$systype_doc.'&area='.$area.'&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'">'. _('') . ' ' . _('') . '' .'<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt=""></a>';
            }
	}else{
            if($_SESSION['DocumentNextByType']==1){
                $InvoiceNoTAG = DocumentNextByType(200, $tagref,$area,$legaid, $db);
            }else{
		$InvoiceNoTAG = DocumentNext(200, $tagref,$area,$legaid, $db);
            }
            //$InvoiceNoTAG = DocumentNext(200, $tagref,$area,$legaid, $db);
            $separa = explode('|',$InvoiceNoTAG);
            $serie = $separa[1];
            $folio = $separa[0];
            $OrderNo=0;
            $factelectronica= XSAInvoicingCreditdirect($transno, $OrderNo , $DebtorNo, $systype_doc,$tagref,$serie,$folio, $db);
            // Envia  los datos al archivooooo//
            $factelectronica=utf8_encode($factelectronica);
            $empresa=$keyfact.'-'.$rfc;
            $nombre=$nombre;
            $arrayGeneracion=generaXMLCFDI($factelectronica,'egreso',$tagref,$serie,$folio,$DebtorID,'NCreditoDirect',$OrderNo,$db);
            $XMLElectronico=$arrayGeneracion["xml"];
            //Se agrega la generacion de xml_intermedio
            $array=generaXMLIntermedio($factelectronica,$XMLElectronico,$arrayGeneracion["cadenaOriginal"],utf8_encode($arrayGeneracion["cantidadLetra"]), $DebtorID,$db,13,$tagref);
            $xmlImpresion= utf8_decode($array["xmlImpresion"]);
            $rfcEmisor=$array["rfcEmisor"];
            $fechaEmision=$array["fechaEmision"];
            //Almacenar XML
            $flagsendfiscal=0;
            $query="INSERT INTO Xmls(transNo,
                                    type,
                                    rfcEmisor,
                                    fechaEmision,
                                    xmlSat,
                                    xmlImpresion,
                                    fiscal)
                    VALUES('".$transno."',
                            '".$systype_doc."',
                            '".$rfcEmisor."',
                            '".$fechaemision."',
                            '".$XMLElectronico."',
                            '".$xmlImpresion."',
                            '".$flagsendfiscal."')";
            $Result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);
            $liga="PDFInvoice.php";
            $liga='<p>' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID . '?Type='.$systype_doc.'&tipodocto=1&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'&OrderNo='.$OrderNo.'">'. _('') . ' ' . _('') . '' .'<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt=""></a>';
		 	
        }
		        
	$PaginaAplicacion=HavepermissionURL($_SESSION['UserID'],11, $db);
	unset($_SESSION['AllocTransCustomer']);
	unset($_SESSION['AllocCustomer']);
	unset($_POST['AllocTrans']);
	
//	echo '<p><div align="center">';
//	echo $liga;
//	echo '</div>';
        echo '<table cellpadding=1 width="50%" border=1 style="border-collapse: collapse; border-color:lightgray;">';
            echo '<tr>';
                echo '<td align="center" width="12%" colspan=2 style="text-align:center;"><b>'._("Pago").'</b></td>';
                echo '<td align="center" width="12%" colspan=1 style="text-align:center;"><b>'._("Poliza").'</b></td>';
                echo '<td align="center" width="12%" colspan=1><b>'._("Acciones Siguientes").'</b></td>';
             echo '</tr>';
             echo '<tr>';
                echo '<td align="center" width="8%" style="text-align:center;"><b>'.$transno."</b></td>";
                echo '<td align="center" width="8%" style="text-align:center;"><b>'.$liga."</b></td>";
                echo '<td align="center" width="8%" style="text-align:center;"><a href="PrintJournal.php?' . SID .'FromCust=1&ToCust=1&PrintPDF=Yes'.'&TransNo='.$transno.'&type=200" target="_blank"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Poliza') . '" alt=""></a></td>';
                echo '<td align="center" width="8%" style="text-align:left;"><a href="'.$PaginaAplicacion.'?' . SID . '&noaplicacion=' . $transno . '&currcode='.$monedaChequera.'"><b>' . _('Aplicacion de Pagos') . '</b></td>';
            echo '</tr>';
            echo '<tr>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                //echo '<td align="center" width="8%" style="text-align:center;"><a href="SelectCustomer.php?' . SID . '&Select=' . $cus .'">' . _('Opciones del cliente') . '</td>';
            
                        
	//Actualizar el documento para folio
	$SQL="UPDATE debtortrans
		SET folio='" . $serie.'|'.$folio . "'
		WHERE transno=".$transno."
                AND type=".$systype_doc;
	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
	$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    }  
}



echo '<p><td align="center" width="8%" style="text-align:left;">';
echo '<a href="SelectCustomer.php?modulosel=2"><b>';
echo '<font size=2 face="arial">';
echo _('Regresar a Opciones del Cliente');
echo '</font>';
echo '</b></a></td>';
echo '</tr>';
echo '</table>';
if ($_POST['procesar']!='PROCESAR' or $InputError){

    echo "<form id='form1' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post>";
    echo '<input type=hidden name="DebtorNo" value="'.$DebtorNo.'">';
    
    echo '<table width=80% border=0 cellpadding=4 align=center>';
    echo '<tr>';
    echo '<td style="text-align:center" colspan=2>';
    echo '<font size=3 face="arial">';
    echo '<b>'. _('Abono Directo a Cuenta Cheques:') . $nombrecliente .'</b>';
    echo '</font>';
    echo '</td>';
    echo '</tr>';
      
    /* SELECCIONA EL RANGO DE FECHAS */
    echo '<tr>';
    echo '<td colspan=2>';
    echo '&nbsp;</td></tr>';
    echo '<tr>';
    echo '<td  style="text-align:right">' . _('Fecha de Aplicacion:') . '</td>';
    echo'<td><select Name="FromDia" id="FromDia">';
    $sql = "SELECT *
            FROM cat_Days";
    $dias = DB_query($sql,$db);
    while ($myrowdia=DB_fetch_array($dias,$db)){
        $diabase=$myrowdia['DiaId'];
        if (rtrim(intval($FromDia))==rtrim(intval($diabase))){ 
            echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
        }else{
            echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
        }
    }
    echo '</select>';    
    echo '&nbsp;<select Name="FromMes" id="FromMes">';

    $sql = "SELECT *
           FROM cat_Months";
    $Meses = DB_query($sql,$db);
    while ($myrowMes=DB_fetch_array($Meses,$db)){
        $Mesbase=$myrowMes['u_mes'];
        if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){ 
            echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" selected>' .$myrowMes['mes'];
        }else{
            echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
        }
    }
    echo '</select>';
    echo '&nbsp;<input name="FromYear" id="FromYear" type="text" size="4" value='.$FromYear.'>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan=2>';
    echo '&nbsp;</td></tr>';
    echo '<tr>';
    echo '<td class=number nowrap ><font size=2 face="arial">'._('Razon Social').':</font>[<font color=red title="'._('Campo obligatorio').'">*</font>]</td>';
    echo'<td  nowrap><select name="legalid">';
    $SQL = "SELECT legalbusinessunit.legalid,
                    legalbusinessunit.
                    legalname
            FROM sec_unegsxuser u,tags t 
            JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
            WHERE u.tagref = t.tagref
            AND u.userid = '" . $_SESSION['UserID'] . "'
            GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname 
            ORDER BY t.tagref";
    $result=DB_query($SQL,$db);
    echo '<option selected value="-1">'._('Todas Las Razones Sociales...').'</option>';
    while ($myrow=DB_fetch_array($result)){
        if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
            echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
        } else {
            echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
      	}
    }
    echo '</select>';
    echo "<input type=submit name='selLegalid' VALUE='" . _('->') . "'>";
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="text-align:right" width=40%>';
    echo '<font size=2 face="arial">';
    echo _('Unidad de Negocio'). ' :';
    echo '</font>';
    echo '[<font color=red title="'._('Campo obligatorio').'">*</font>]</td>';
    echo '<td>';
    echo '<select Name="tagref">';
    $sql='SELECT t.tagref,
                tagdescription
          FROM tags t, sec_unegsxuser uxu
          WHERE t.tagref=uxu.tagref
          AND userid="'.$_SESSION['UserID'].'"';
    if(isset($_POST['legalid']) && $_POST['legalid']!='-1'){
        $sql = $sql ." AND legalid=".$_POST['legalid'];
    }
    $sql=$sql." ORDER BY tagdescription";
    $result = DB_query($sql ,$db);
    echo '<option  VALUE="0" selected>Seleccionar</option>';
    while ($myrow=DB_fetch_array($result,$db)){
        echo '<option  VALUE="' . $myrow[0] .  '"';
        if ($tagref==$myrow[0]){
            echo ' selected';
        }
        echo '>' .$myrow[1] . '</option>';
    }
    echo '</select>';
    echo '<input type="submit" name="btnTagref" value="->"></td>';
    echo '</tr>';
    echo '<tr><td style="text-align:right">' . _('Selecciona la Cuenta de Banco') . ':[<font color=red title="'._('Campo obligatorio').'">*</font>]</td>';
    echo '<td colspan=3><select name="GLCode">';

    $SQL = 'SELECT bankaccountname as accountname,
                    bankaccounts.accountcode as accountcode,
                    bankaccounts.currcode
            FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
            WHERE bankaccounts.accountcode=chartmaster.accountcode 
            AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
            AND tagsxbankaccounts.tagref = sec_unegsxuser.tagref 
            AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" 
            AND tagsxbankaccounts.tagref="'.$_POST['tagref'].'"
            GROUP BY bankaccountname, bankaccounts.accountcode, bankaccounts.currcode';
    $result=DB_query($SQL,$db);
    if($_POST[tagref]==0){
        echo '<option  VALUE="0" selected>Seleccione unidad de negocio.</option>';
    }else{
        if (DB_num_rows($result)==0){
            echo '</select></td></tr>';
            prnMsg(_('No se an configurado las cuentas de bancos') . ' - ' . _('pagos no se pueden analizar contra cuentas si no estan dadas de alta'),'error');
        } else {
            echo '<option  VALUE="0" selected>Seleccionar</option>';
            while ($myrow=DB_fetch_array($result)){
                if (isset($_POST['GLCode']) and $_POST['GLCode']==$myrow["accountcode"]){
                    echo '<option selected value=' . $myrow['accountcode'] . '>(' .$myrow['currcode'] .')'. $myrow['accountcode'] . ' - ' . $myrow['accountname'];
                } else {
                    echo '<option value=' . $myrow['accountcode'] . '>(' .$myrow['currcode'] .')' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
                }
            }
            echo '</select></td></tr>';
        }
    }
    
    echo "<tr>";
    echo "<td style='text-align:right'>"._('Clave Presupuestal').":[<font color=red title='"._('Campo obligatorio')."'>*</font>]</td>";
    echo "<td nowrap>
        <input type='text' name='clavepresupuestal_1' id='clavepresupuestal_1' value='".$clavepresupuestal."' size='90' style='font-weight: bold;background-color: #FAFAFA;'>
        <a href=\"javascript:openNewWindow('ConsultaClavePresuestal.php?&linea=1&tagref=".$_POST['tagref']."&tipo=1&separado=1');\"><font size=3>Buscar Clave</font></a></td>";
    echo "</tr>";
    
    echo '<tr>';
    echo '<td style="text-align:right">';
    echo '<font size=2 face="arial">'._('Concepto'). ' :</font>[<font color=red title="'._('Campo obligatorio').'">*</font>]</td>';
    echo '<td><textarea name="concepto" rows="4" cols="50">'.$_POST['concepto'].'</textarea></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="text-align:right"><font size=2 face="arial">'._('Monto').':</font>[<font color=red title="'._('Campo obligatorio').'">*</font>]</td>';
    echo '<td><input class="number" type="text" name="monto" id="monto" class="Number" style="text-align:right" value="'.$monto.'" size=15 maxlength=15></td>';
    echo '</tr>';
    echo '<tr>';
    
    echo '<td style="text-align:right"><font size=2 face= "arial">'._('Moneda').'</font></td>';
    $sqlmoneda = "SELECT currencies.currabrev,
                        currencies.currency
                    FROM currencies";
    $resultmoneda = DB_query($sqlmoneda, $db);
    if($_POST['moneda'] == ''){
        $_POST['moneda'] == 'MXN';
    }
    echo '<td><select name="moneda">';
    while($myrowmoneda = DB_fetch_array($resultmoneda)){
        if($_POST['moneda'] == $myrowmoneda['currabrev']){
            echo '<option selected value="'.$myrowmoneda['currabrev'].'">'.$myrowmoneda['currency'].'</option>';
        }else{
            echo '<option value="'.$myrowmoneda['currabrev'].'">'.$myrowmoneda['currency'].'</option>';
        }
    }
    echo'</select><input type="submit" value="->" name="btnTag"></td></tr>';
    echo '<tr>';
    echo '<td style="text-align:right"><font size=2 face="arial">'._('T.C.'). ' :</font></td>';
    echo '<td><input class="number" type="text" name="tipocambio" class="Number" style="text-align:right" value="'.$tipocambio.'" size=9 maxlength=15></td>';
    echo '</tr>';
    echo '<tr><td style="text-align:right">' . _('Categoria Impuestos') . ':</td>';
    $sql = 'SELECT taxcatid,
                    taxcatname, 
                    flagdefault 
            FROM taxcategories 
            ORDER BY taxcatname';
    $result = DB_query($sql, $db);
    echo'<td><select name="TaxCat">';
    while ($myrow = DB_fetch_array($result)) {
        if ($_POST['TaxCat'] == $myrow['taxcatid'] ){
            echo '<option selected value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
        } else {
            if($_POST['TaxCat'] <> $myrow['taxcatid'] and $myrow['flagdefault'] == 1 ){
                echo '<option selected value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
            }else{
                echo '<option value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
            }
        }
    }
    echo '</select></td></tr>';
    echo "<tr><td  style='text-align:right'>" . _('Aplica Parcialidades') . ":</td>";
    $sqlpar = "	SELECT AplPagosTimbrar.idaplpagostimbrar,
                        AplPagosTimbrar.descripcion,
                        AplPagosTimbrar.flagsdefault
                From AplPagosTimbrar";
    $resultpar = DB_query($sqlpar, $db);
    echo '<td><select name="aplparcial">';
    while($myrowpar = DB_fetch_array($resultpar)){
        if($_POST['aplparcial'] == $myrowpar['idaplpagostimbrar']){
            echo '<option selected value="'.$myrowpar['idaplpagostimbrar'].'">'.$myrowpar['descripcion'].'</option>';
        }else{
            if($_POST['aplparcial'] == "" and $myrowpar['idaplpagostimbrar'] == 1){
                echo '<option selected value="'.$myrowpar['idaplpagostimbrar'].'">'.$myrowpar['descripcion'].'</option>';
            }else{
                echo '<option value="'.$myrowpar['idaplpagostimbrar'].'">'.$myrowpar['descripcion'].'</option>';
            }
      	}
    }
    echo '</select></td></tr>';
    if (!isset($_POST['noparcialidad'])){
        $_POST['noparcialidad']=1;
    }
    echo '<tr>';
    echo '<td style="text-align:right">';
    echo '<font size=2 face="arial">';
    echo _('Numero Parcialidad'). ' :';
    echo '</font>';//
    echo '</td>';
    echo '<td>';
    echo '<input class="number" type="text" name="noparcialidad" class="Number" style="text-align:right" value="'.$_POST['noparcialidad'].'" size=9 maxlength=15>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    if($_POST['GeneraAnt'] <> ""){
        $_POST['GeneraAnt'] == 0;
    }
    echo '<td style="text-align:right" nowrap>' . _('Generar Anticipo') . ':</td>';
    echo '<td><input type="checkbox" name="GeneraAnt" value="1"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td></td><td>';
    echo "<input type='button' onclick='evento()' name='procesar' value=' PROCESAR '>";
    echo "<input type='hidden' name='procesar' id='procesar' value=''>";
    echo '</td>';
    echo '</tr>';  
    echo '</table>';
    echo '</form>';
}

echo "<br>";

include('includes/footer.inc');

?>
<script>
    var ventana = null;
    var ventanaBig = null;

    function openNewWindow(url){
        if (ventana==null || ventana.closed)
            ventana = window.open(url,'','width=650,height=200'); //     500 y 200
        else
            alert('Esta funcion ya se esta ejecuntando, favor de cerrarl la ventana antes de abrir otra');	
    }

    function openNewWindowBig(url){
        if (ventanaBig==null || ventanaBig.closed)
            ventana = window.open(url,'','width=500,height=600'); 
        else
            alert('Esta funcion ya se esta ejecuntando, favor de cerrarl la ventana antes de abrir otra');	
	}
    function evento()
    {   
        //document.getElementsByName(\"FromYear\").value,document.getElementsByName(\"FromMes\").value,document.getElementsByName(\"FromDia\").value;)
        
        var obj2=document.getElementById("FromYear").value;
        var obj3=document.getElementById("FromMes").value;
        obj3=obj3.replace(' ','');
        var obj4=document.getElementById("FromDia").value;
        var monto=document.getElementById("monto").value;
        var fechaapli=obj2+'-'+obj3+'-'+obj4;
        var monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        var result=confirm('¿Esta seguro de realizar el pago? Fecha de poliza: '+fechaapli + ' Periodo: ' + monthNames[obj3-1] + '  Monto: $'+monto);
        if(result)
        {
            document.getElementById('procesar').value='PROCESAR';
            document.getElementById('form1').submit();
        }
        else
        {
            
        }
    }
    
</script>   