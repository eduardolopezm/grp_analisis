<?php
//
//TRUE - Muestra todos los echos en la pagina**************
// Se hicieron modificaciones para los anticipos
// Se hicieron modificaciones para la sustitucion
//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
// A producción de anticipos
// Cambios  facturacion de anticipos 01/12/2018
$debug_sql=false;
/* $Revision: 4.0 $
MODIFICADO POR: CARMEN GARCIA
FECHA: 29/DIC/2009
CAMBIOS:
	1.- GENERA CADENA DE FACTURACION
FECHA: 07/ENE/2010
CAMBIOS:
	1.- GENERA CADENA DE NOTAS DE CREDITO
FIN DE CAMBIOS

MODIFICADO POR FRUEBEL CANTERA
FECHA 03/01/2009
CAMBIOS:
DUPLIQUE FUNCION XSAInvoicing, Para utilizacion en recibos como XSAInvoicingRecibo
*/
//////
/*
function DocumentNext ($TransType, $tag, $area,$legaid ,&$db) {

	$SQL = "SELECT count(*) FROM sysDocumentIndex WHERE typeid = " . $TransType . " and tagref in ('" . $tag ."','0')
	and areacode in('".$area."','0') and legalid in('".$legaid."','0')";
	$result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]==0)
	{
	    $SQL = "SELECT typename FROM sysDocumentIndex WHERE typeid = " . $TransType;
	    $result = DB_query($SQL,$db);
	    if (DB_num_rows($result)!=0)
	    {
	      $myrowType = DB_fetch_row($result);
	      $typename = $myrowType[0];
	    }
	    else
	    {
	      $typename = "NOMBRE NO ASIGNADO";
	    }

	  $SQL = 'INSERT INTO sysDocumentIndex (`typeid`,`legalid`,`typename`,`areacode`,`tagref`,`loccode`,`typeabbrev`,`typeno`)';
	  $SQL .= ' VALUES ('.$TransType.',"'.$legaid.'","' .$typename. '","'.$area.'",' .$tag. ',"ANY","AN",0)';
	  $result = DB_query($SQL,$db);
	}
	DB_query("LOCK TABLES sysDocumentIndex WRITE",$db);
	$SQL = "SELECT typeno,folio FROM sysDocumentIndex WHERE typeid = " . $TransType . " and tagref in('" .$tag."',0) and areacode in('".$area."','0') and legalid in('".$legaid."','0')";
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($GetTransNoResult);
	$SQL = 'UPDATE sysDocumentIndex SET typeno = ' . ($myrow[0] + 1) . '
	WHERE typeid = ' . $TransType . ' and tagref in('.$tag.',0) and areacode in("'.$area.'",0)
	and legalid in('.$legaid.',0)';
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
	$DbgMsg =  _('The following SQL to increment the transaction number was used');
	$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	DB_query("UNLOCK TABLES",$db);
	$valordev=$myrow[0] + 1;
	$valordev=$valordev.'|'.$myrow[1];
	return $valordev;
}
*/
//FCC
//NUEVA FUNCION DocumentNext, QUE TOMA EN CUENTA COMO PARAMETRO EL DEPARTAMENTO.
//
// Se agregó el tipo de relación 04 para la factura de sustitucion
/*
 DGJ: 04/07/2019 TAREA 77965 a produccion 
 */
/*
 DGJ: 25/07/2019 Factoraje
 */

function DocumentNext ($TransType, $tag, $area,$legaid ,&$db) {
	$dsql = "SELECT u_department FROM tags WHERE tagref = '" . $tag . "'";
	//echo "<br>dsql: " . $dsql;
	$dresult = DB_query($dsql,$db);
	if ($dmyrow=DB_fetch_array($dresult,$db)){
		$u_department = $dmyrow['u_department'];
	}else{
		$u_department = 0;
	}

	if (($TransType == 10) and ($legaid==4)){      
		$SQL = "SELECT count(*) FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in ('" . $tag ."','0')
		and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')
		and u_department in ('" . $u_department . "','0')";
	}else{
		$SQL = "SELECT count(*) FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in ('" . $tag ."','0')
		and areacode in('".$area."','0') and legalid in('".$legaid."','0')";
	}
	// echo "<br>" . $SQL;
	// echo 'sysDocumentIndex  ->'.$SQL;
	$result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]==0)
	{
	    $SQL = "SELECT typename FROM sysDocumentIndex WHERE typeid = " . $TransType;
	    // echo "<br>" . $SQL;
	    $result = DB_query($SQL,$db);
	    if (DB_num_rows($result)!=0)
	    {
	      $myrowType = DB_fetch_row($result);
	      $typename = $myrowType[0];
	    }
	    else
	    {
	      $typename = "NOMBRE NO ASIGNADO";
	    }

	  $SQL = 'INSERT INTO sysDocumentIndex (`typeid`,`legalid`,`typename`,`areacode`,`tagref`,`loccode`,`typeabbrev`,`typeno`)';
	  $SQL .= ' VALUES ('.$TransType.',"'.$legaid.'","' .$typename. '","'.$area.'",' .$tag. ',"ANY","AN",0)';
	  $result = DB_query($SQL,$db);
	  // echo "<br>" . $SQL;//
	}

	DB_query("LOCK TABLES sysDocumentIndex WRITE",$db);
	if (($TransType == 10) and ($legaid==4)){
		$SQL = "SELECT typeno,folio FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in('" . $tag . "',0) and areacode in('" . $area . "','0')
		and legalid in('" . $legaid . "','0')
		and u_department in ('" . $u_department . "','0')";
	}else{
		$SQL = "SELECT typeno,folio FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in('" . $tag . "',0) and areacode in('" . $area . "','0')
		and legalid in('" . $legaid . "','0')";
	}
	//echo "<br>" . $SQL;
	// echo '<pre><br>'.$SQL;
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($GetTransNoResult);

	if (($TransType == 10) and ($legaid==4)){
		$SQL = "UPDATE sysDocumentIndex SET typeno = " . ($myrow[0] + 1) . "
		WHERE typeid = '" . $TransType . "' and tagref in ('" . $tag . "','0')
		and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')
		and u_department in ('" . $u_department . "', '0')";
	}else{
		$SQL = "UPDATE sysDocumentIndex SET typeno = " . ($myrow[0] + 1) . "
		WHERE typeid = '" . $TransType . "' and tagref in ('" . $tag . "','0')
		and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')";
	}
	// echo '<pre><br>'.$SQL;
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
	$DbgMsg =  _('The following SQL to increment the transaction number was used');
	$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	DB_query("UNLOCK TABLES",$db);
	$valordev=$myrow[0] + 1;
	$valordev=$valordev.'|'.$myrow[1];
	return $valordev;

}

function DocumentNextByType ($TransType, $tag, $area,$legaid ,&$db) {
	if ($TransType == 10){
		$tablaconsulta = "sysDocumentIndexInv";
	}elseif ($TransType == 12){
		$tablaconsulta = "sysDocumentIndexRec";
	}else{
		$tablaconsulta = "sysDocumentIndexOth";
	}

	DB_query("LOCK TABLES " .  $tablaconsulta . " WRITE",$db);
	$SQL = "SELECT typeno,folio FROM " . $tablaconsulta . " WHERE typeid = " . $TransType . " and tagref in('" . $tag."',0) and areacode in('".$area."','0') and legalid in('".$legaid."','0')";
	//echo $SQL;
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($GetTransNoResult);
	$SQL = "UPDATE " . $tablaconsulta . " SET typeno = " . ($myrow[0] + 1) . "
	WHERE typeid = " . $TransType . " and tagref in(" . $tag . ",0) and areacode in('" . $area . "',0)
	and legalid in(" . $legaid . ",0)";
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
	$DbgMsg =  _('The following SQL to increment the transaction number was used');
	$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	DB_query("UNLOCK TABLES",$db);
	$valordev=$myrow[0] + 1;
	$valordev=$valordev.'|'.$myrow[1];
	return $valordev;
}


//***************************************************************************************************************************
//********************************************* INICIO DE FUNCION PARA FACTURACION ******************************************
//***************************************************************************************************************************
/*
	Funcion con datos del CFDI 3.2
*/
function XSAInvoicing_ANT32($InvoiceNo, $orderno, $debtorno, $TypeInvoice,$tag,$serie,$folio, &$db,$nivelfacturacion=2,$descripcionOT='',$OT=''){
	// **************************** //
	// No dejar echo en la funcion //
	// Afecta al punto de venta   //
	// **************************** //

    $debug_sql = false;
	$charelectronic='01';
	$charelectronicEmbarque='';
	$charelectronic=$charelectronic.'|'.$serie;
	$serieelect=$serie;
        $pedimento="";

	//$serieelect=$TypeInvoice.'-'.$_SESSION['Tagref'];
    $folioelect="";//$folio;//$InvoiceNoTAG;
    if ($TypeInvoice == 66) {
    	$folioelect = $folio;
    }
	//$folioelect=$InvoiceNo;
	$charelectronic=$charelectronic.'|'.$serieelect;
	$charelectronic=$charelectronic.'|'.$folioelect;
	$charelectronictaxsfederal='';
	$charelectronictaxslocal='';
	// consulto datos de la factura
	$imprimepublico=0;
	$rfccliente = "";
	$TipoAgrupacionXTag = "";
        $branch= "";
        
	if ($TypeInvoice==11){
		$tabla='notesorders';
	}else{
		$tabla='salesorders';
	}
	
	$decimalplaces=6;
	
	if ($_SESSION['DecimalPlacesInvoice']==''){
		$_SESSION['DecimalPlacesInvoice']=6;
	}
	$decimalplaces=$_SESSION['DecimalPlacesInvoice'];
	
	$SQLInvoice = "SELECT replace(debtortrans.trandate,'-','/') as trandate,debtortrans.ovamount,debtortrans.ovdiscount,
			debtortrans.ovfreight,debtortrans.ovgst,debtortrans.rate as cambio,
			debtortrans.order_,debtortrans.invtext,	debtortrans.consignment,
			debtortrans.id AS iddocto, debtortrans.type,
			debtorsmaster.name,debtorsmaster.address1,debtorsmaster.address2,
			debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,
			debtorsmaster.address6,debtorsmaster.invaddrbranch,
			custbranch.taxid as rfc,paymentterms.terms,salesorders.deliverto,
			salesorders.deladd1,salesorders.deladd2,salesorders.deladd3,
			salesorders.deladd4,salesorders.deladd5,salesorders.deladd6,
			salesorders.customerref,salesorders.orderno,salesorders.orddate,
			locations.locationname,	shippers.shippername,custbranch.brname,
			custbranch.braddress1,custbranch.braddress2,custbranch.braddress3,
			custbranch.braddress4,custbranch.braddress5,custbranch.braddress6,
			custbranch.brpostaddr1,custbranch.brpostaddr2,custbranch.brpostaddr3,
			custbranch.brpostaddr4,custbranch.brpostaddr5,custbranch.brpostaddr6,
			salesman.salesmanname,debtortrans.debtorno,debtortrans.branchcode,debtortrans.folio,
			replace(origtrandate,'-','/') as origtrandate,debtortrans.currcode,custbranch.branchcode,
			salesorders.salesman,salesorders.UserRegister,custbranch.phoneno as telofi,
			salesorders.placa, salesorders.serie,salesorders.kilometraje,salesorders.comments,debtortrans.ref1,
			salesorders.ordertype,debtortrans.paymentname,debtortrans.nocuenta,
			custbranch.brnumext, custbranch.brnumint,custbranch.specialinstructions,
			tags.typegroup,debtortrans.id, custbranch.custpais, IFNULL(paymentmethods.codesat,'') as codesat,
			debtortrans.codesat as cadenacodesat, debtortrans.order_
		FROM debtortrans
			LEFT JOIN paymentmethods ON debtortrans.paymentname = paymentmethods.paymentname
			left join shippers on debtortrans.shipvia=shippers.shipper_id 
			join tags on tags.tagref=debtortrans.tagref,
			debtorsmaster,custbranch,".$tabla." as salesorders  
			left join locations on salesorders.fromstkloc=locations.loccode ,
			salesman,paymentterms 

		WHERE debtortrans.order_ = salesorders.orderno
			AND debtortrans.type='".$TypeInvoice."'
			AND debtortrans.transno='" . $InvoiceNo . "'
			AND debtortrans.tagref='" . $tag . "'
			AND debtortrans.debtorno=debtorsmaster.debtorno
			AND salesorders.paytermsindicator=paymentterms.termsindicator
			AND debtortrans.debtorno=custbranch.debtorno
			AND debtortrans.branchcode=custbranch.branchcode
			AND salesorders.salesman=salesman.salesmancode";
	
    if($_SESSION['UserID'] == "admin"){
        //echo '<pre>get > 1 :<br>'.$SQLInvoice.'<br>';
	}
	//echo '<pre>get > 1 :<br>'.$SQLInvoice.'<br>';
	//echo '<pre>get > 1 :<br>'.$SQLInvoice.'<br>';
	//echo '<pre>SQL Principal: <br>'.$SQLInvoice;

    //debug_sql($SQLInvoice, __LINE__,true,__FILE__);
	$Result=DB_query($SQLInvoice,$db);
	
	if (DB_num_rows($Result)>0) {
		$myrow = DB_fetch_array($Result);
		//impuestos federales
		$totretencionfederal=0;
		//$charelectronictaxslocal='|'.chr(13).chr(10);
		if(isset($_SESSION['ordenaimpuestos']) AND $_SESSION['ordenaimpuestos']==1 ){
            $qry = "Select debtortranstaxesclient.*,sec_taxes.nametax, sec_taxes.flagimpuestolocal 
            		from debtortranstaxesclient, sec_taxes
					where debtortranstaxesclient.idtax = sec_taxes.idtax
						AND sec_taxes.typetax=1 and orden!=0
						and debtortranstaxesclient.iddoc = ".$myrow['id']. " ORDER BY orden ASC";
		}else{
            $qry = "Select debtortranstaxesclient.*,sec_taxes.nametax, sec_taxes.flagimpuestolocal 
            		from debtortranstaxesclient, sec_taxes
					where debtortranstaxesclient.idtax = sec_taxes.idtax
						AND sec_taxes.typetax=1
						and debtortranstaxesclient.iddoc = ".$myrow['id'];
		}
            
        debug_sql($qry, __LINE__,$debug_sql,__FILE__);
		$rs = DB_query($qry,$db);//
		$totalImpuestosLocales=0;
		if (DB_num_rows($rs) > 0){
			//$charelectronictaxsfederalini='|'.chr(13).chr(10).'06';
			while ($regs = DB_fetch_array($rs)){
                if($regs['flagimpuestolocal']==0){
                    $charelectronictaxsfederal.='|'.chr(13).chr(10).'06'."|".$regs['nametax']."|".$regs['percent']."|".$regs['amount']."|".$regs['flagimpuestolocal'];
				$totretencionfederal=$totretencionfederal+$regs['amount'];
                }
                if($regs['flagimpuestolocal']==1){
                    $charelectronictaxsfederal.='|'.chr(13).chr(10).'08'."|".$regs['nametax']."|".$regs['percent']."|".$regs['amount']."|".$regs['flagimpuestolocal'];
				$totretencionfederal=$totretencionfederal+$regs['amount'];
                }
			}
		}

		//impuestos locales
		$totalretencionlocal=0;
		//$charelectronictaxslocal='|'.chr(13).chr(10);
		$qry = "Select debtortranstaxesclient.*,sec_taxes.nametax from debtortranstaxesclient, sec_taxes
				where debtortranstaxesclient.idtax = sec_taxes.idtax
				AND sec_taxes.typetax=2
				and debtortranstaxesclient.iddoc = ".$myrow['id'];
		debug_sql($qry, __LINE__,$debug_sql,__FILE__);
		$rs = DB_query($qry,$db);
		$totalImpuestosLocales=0;
		if (DB_num_rows($rs) > 0){
			$charelectronictaxslocal='08';
			while ($regs = DB_fetch_array($rs)){
				$charelectronictaxslocal.="|".$regs['nametax']."|".$regs['percent']."|".$regs['amount'];
				$totalretencionlocal=$totalretencionlocal+$regs['amount'];
			}
			$charelectronictaxslocal.=chr(13).chr(10);
		}

		$totalretencion=$totretencionfederal+$totalretencionlocal;
		//Tipo agrupacion
		$TipoAgrupacionXTag=$myrow['typegroup'];
		// fecha emision
		$fechainvoice=$myrow['origtrandate'];
		$UserRegister=$myrow['UserRegister'];
		$charelectronic=$charelectronic.'|'.$fechainvoice;

		/********
		$metodoPago = $myrow['paymentname'];
		if ($metodoPago=="")
			$metodoPago = "No Identificado";
		*/
		
		$metodoPago = "";

		if (trim($myrow['cadenacodesat'] != "")) {
			$metodoPago = $myrow['cadenacodesat'];
		}else{
			$metodoPago = $myrow['codesat'];
		
			if($metodoPago == "") {
				$metodoPago = "99";
			}
		}
		
		$nocuenta = $myrow['nocuenta'];
		if ($nocuenta=="")
			$nocuenta = "No Identificado";
		
		// subtotal
		$rfccliente=str_replace("-","",$myrow['rfc']);
		$rfccliente=str_replace(" ","",$rfccliente);
		$nombre=$myrow['name'];
		$subtotal= number_format($myrow['ovamount'], $decimalplaces, '.', '');
		
		if (strlen($rfccliente)<12){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= number_format($myrow['ovamount']+$myrow['ovgst'], $decimalplaces, '.', '');
			$imprimepublico=1;
		}elseif(strlen($rfccliente)>=14){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= number_format($myrow['ovamount']+$myrow['ovgst'], $decimalplaces, '.', '');
			$imprimepublico=1;
		}
		
		$charelectronic=$charelectronic.'|'.$subtotal;
		// total factura
		$decimalplacesuno=6;
		if($TypeInvoice==66 or $TypeInvoice==10 or $TypeInvoice==110){
			$decimalplacesuno=4;
		}
		$total= number_format($myrow['ovamount']+$myrow['ovgst']+$totalretencion,$decimalplaces,'.','');
        $total= number_format(round($myrow['ovamount'],2) + round($myrow['ovgst'],2) + round($totalretencion, 2), $decimalplaces, '.', '');
		
		$charelectronic=$charelectronic.'|'.$total;
		// total de iva
		$iva=number_format($myrow['ovgst'],$decimalplaces,'.','');
		// transladado
		$charelectronic=$charelectronic.'|'.$iva;
		// retenido
		$ivaret=0;
		$charelectronic=$charelectronic.'|'.$ivaret;

		//descuento trae desde el stockmoves
		if ($rfccliente!="XAXX010101000" OR $_SESSION['DesgloseIVA']==1){
			$sqldiscount = 'SELECT sum(totaldescuento) as descuento
				       FROM stockmoves
				       WHERE stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND stockmoves.show_on_inv_crds=1';
                        debug_sql($sqldiscount, __LINE__,$debug_sql,__FILE__);
			$Result= DB_query($sqldiscount,$db);
			if (DB_num_rows($Result)==0) {
				$descuento=0;
			}else{
				$myrowdis = DB_fetch_array($Result);
				$descuento=number_format($myrowdis['descuento'],$decimalplaces,'.','');
			}
		}else{
			$sqldiscount = 'SELECT sum(totaldescuento+(IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)*totaldescuento)) as descuento
			       			FROM stockmoves inner join stockmaster on stockmaster.stockid=stockmoves.stockid
							-- LEFT JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
							LEFT JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno
					
					  		WHERE stockmoves.type='.$TypeInvoice.'
								AND stockmoves.transno=' . $InvoiceNo . '
								AND stockmoves.tagref=' . $tag . '
								AND stockmoves.show_on_inv_crds=1';
                        debug_sql($sqldiscount, __LINE__,$debug_sql,__FILE__);
			$Result= DB_query($sqldiscount,$db);
			if (DB_num_rows($Result)==0) {
				$descuento=0;
			}else{
				$myrowdis = DB_fetch_array($Result);
				$descuento=number_format($myrowdis['descuento'],2,'.','');
			}	
		}

		$descuento=number_format($descuento,$decimalplaces,'.','');
		$charelectronic=$charelectronic.'|'.$descuento;
		//motivo descuento
		$charelectronic=$charelectronic.'|';
		// tipo de moneda
		$moneda=$myrow['currcode'];
		// CANTIDAD CON LETRAS
		$totaletras=($myrow['ovamount']+$myrow['ovgst'])+$totalretencion;
		/*$decimalplacesuno=4;
		if($totaletras<0 and ($TypeInvoice==66 or $TypeInvoice==10 or $TypeInvoice==110)){
			$decimalplacesuno=2;
		}
		//$totalx=str_replace(',','',number_format($totaletras,$decimalplacesuno,'.',''));
		//$totalx = str_replace(',','',$total);
		$separa2=explode(".",$totalx,4);
		//$separa=explode(".",$totalx);//////
		$montoletra = $separa[0];
		$separa2=explode(".",number_format($totalx2,$decimalplacesuno));
		
		if($totaletras<0 and ($TypeInvoice==66 or $TypeInvoice==10 or $TypeInvoice==110)){
			$montoctvs2="0.00";
		}else{
			$montoctvs2 = $separa2[1];
		}
		
		//$montoletra = Numbers_Words::toWords($montoletra,'es');
		//$montocentavos=$montoctvs2;
		$montoletra=Numbers_Words::toWords($montoletra,'es');
		//$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
		if ($moneda=='MXN'){
			$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";

		}
		else
		{
			$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
		}
		$charelectronic=$charelectronic.'|'.$montoletra;*/
		$totalx=str_replace(',','',$total);
		
		$separa=explode(".",$totalx);
		$montoletra = $separa[0];
		//
		//$separa2=explode(".",$totalx);//
        if(isset($_SESSION['DecimalPlacesLetra']) and $_SESSION['DecimalPlacesLetra'] <> ""){
            $montoctvs2 = substr($separa[1], 0,$_SESSION['DecimalPlacesLetra']);
        }else{
            $montoctvs2 = substr($separa[1], 0,6);
        }
		
		//$montoctvs2 = $separa[1];//
        
		$montoletra = Numbers_Words::toWords($montoletra,'es');
		//$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
		if ($moneda=='MXN'){
			$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
		}elseif ($moneda=='EUR'){
			$montoletra=ucwords($montoletra) . " Euros ". $montoctvs2 ." /100 EUR";
		}else{
			$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
		}

		$charelectronic=$charelectronic.'|'.$montoletra;
		// tipo moneda
		$charelectronic=$charelectronic.'|'.$moneda;
		// tipo de cambio
		$rate=number_format((1/$myrow['cambio']),$decimalplaces,'.','');
		$charelectronic=$charelectronic.'|'.$rate;
		// numero de orden para referencia
		$ordenref=$myrow['orderno'];
		$charelectronic=$charelectronic.'|'.$ordenref;
		// observaciones 1: vendedores
		$vendedor="";
		$SQL=" SELECT *
		       FROM salesman
		       WHERE salesmancode='".$myrow['salesman']."'";
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowsales = DB_fetch_array($Result);
			$vendedor=$myrowsales['salesmanname'];
		}
		$observaciones1='Vendedor: '.$myrow['salesman'].' '.$vendedor;
		$charelectronic=$charelectronic.'|'.$observaciones1;
		// observaciones 2
		$SQL=" SELECT l.telephone,l.comments,t.tagdescription,t.legalid
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='".$tag."'";
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$telephone=trim($myrowtags['telephone']);
			$comments=trim($myrowtags['comments']);
		}
          //      $legal = DB_fetch_array($Result);
		$observaciones2=" Atencion a clientes " .$telephone;
		$charelectronic=$charelectronic.'|'.$observaciones2;
		// observaciones 3
		$comments=$comments.' Cuenta de Referencia: '.$myrow['ref1'];
		$observaciones3='Id Factura:'.$InvoiceNo. '. La tenencia de esta factura no acredita su pago si no se justifica con el comprobante respectivo. '.$comments .' usr:'.$UserRegister;
		$charelectronic=$charelectronic.'|'.$observaciones3;
		// informacionExtra
		$infoextra="";
		$numpago=0;
		$cadenapagares="";
		if ($TypeInvoice==11){
			$tipodoc=0;
		}else{
			$tipodoc=70;
		}
		
		$SQL=" SELECT trandate,case when ovamount<0 then (ovamount+ovgst)*-1 else ovamount+ovgst end  as monto
		       FROM  debtortrans
		       WHERE debtortrans.type=".$tipodoc."
			AND debtortrans.order_=" . $InvoiceNo ;
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)>0) {
			while ($myrowpagos=DB_fetch_array($Result)){
				$numpago=$numpago+1;
				$fechapago=ConvertSQLDate($myrowpagos['trandate']);
				$montopago=$myrowpagos['monto'];
				$montopago=number_format($montopago,$decimalplaces,'.','');
				$cadenapagares=$cadenapagares." No:".$numpago." Fecha: ".$fechapago." Monto: ".$montopago .' '.$moneda;
			}
		}
		$placa= $_SESSION['LabelText1'].' : '.$myrow['placa'];
		$serie=$_SESSION['LabelText3'].' : '.$myrow['serie'];
		$kilometraje=$_SESSION['LabelText2'].' : '.$myrow['kilometraje'];
		$comments1=' Extra: '.$myrow['comments'];
		$infoextra=$placa.' '.$serie.' '.$kilometraje.' '.$comments1.' Pagares: '. $cadenapagares;


		$charelectronic=$charelectronic.'|'.$infoextra;

		//cometarios nivel factura
		$charelectronic=$charelectronic.'|'.$myrow['invtext'];

		// datos de la forma de pago
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'02';
		$terminospago=$myrow['terms'];
		$SQL=" SELECT count(*) as pagares
		       FROM  debtortrans
		       WHERE debtortrans.type=70
			AND debtortrans.order_=" . $InvoiceNo ;
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==0) {
			$Tipopago="Pago en una sola exhibicion";
		}else{
			$myrowpag = DB_fetch_array($Result);
			$numpagares=intval($myrowpag['pagares']);
			if ($numpagares<=1){
				$Tipopago="Pago en una sola exhibicion";
			}else{
				$Tipopago="Parcialidades";
			}
		}
		if ($TypeInvoice==11){
			$Tipopago="Pago en una sola exhibicion";
		}
		
		$Tipopago=$Tipopago;//.' '.$terminospago;
		//$Tipopago=$terminospago;
		$charelectronic=$charelectronic.'|'.$Tipopago;
		// condiciones de pago
		$charelectronic=$charelectronic.'|'.$terminospago;
		// metodo de pago
		$metodopago=$metodoPago;
		$charelectronic=$charelectronic.'|'.$metodopago;
		// fecha vencimiento
		$fechavence=$myrow['trandate'];
		$charelectronic=$charelectronic.'|'.$fechavence;
		// observaciones 4 (no de cuenta)
		$observaciones4=$nocuenta;
		$charelectronic=$charelectronic.'|'.$observaciones4;
		// datos del cliente
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'03';
		$branch=$myrow['debtorno'];
		$charelectronic=$charelectronic.'|'.$branch;
		$charelectronic=$charelectronic.'|'.$rfccliente;
		$charelectronic=$charelectronic.'|'.$nombre;
		
		//                if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
		//                    $pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))); //"Mexico";//$myrow['name'];
		//		}else{
		//                    $pais="Mexico";
		//		}
                
        $pais= $myrow["custpais"];
        if (empty($myrow["custpais"])){
            $pais= "Mexico";
        }                
                
		$charelectronic=$charelectronic.'|'.$pais;
		$calle="";
		if ($imprimepublico==0){
			$calle=$myrow['address1'];
		}
		$charelectronic=$charelectronic.'|'.$calle;
		$noext=$myrow['brnumext'];
		$charelectronic=$charelectronic.'|'.$noext;
		$noint=$myrow['brnumint'];
		$charelectronic=$charelectronic.'|'.$noint;
		$colonia="";
		if ($imprimepublico==0){
			$colonia=$myrow['address2'];
		}
		$charelectronic=$charelectronic.'|'.$colonia;
		$localidad="";
		$charelectronic=$charelectronic.'|'.$localidad;
		$referenciacalle="";
		if ($imprimepublico==0){
			$referenciacalle=$myrow['telofi'];
		}
		$charelectronic=$charelectronic.'|'.$referenciacalle;
		$municipio="";
		if ($imprimepublico==0){
			$municipio=$myrow['address3'];
		}
		$charelectronic=$charelectronic.'|'.$municipio;
		$edo="";
		if ($imprimepublico==0){
			$edo=$myrow['address4'];
		}
		$charelectronic=$charelectronic.'|'.$edo;
		$cp="";
		if ($imprimepublico==0){
			$cp=$myrow['address5'];
		}
		$charelectronic=$charelectronic.'|'.$cp;
		// datos del custbranch
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'04';
		$branch=$myrow['branchcode'];
		$charelectronic=$charelectronic.'|'.$branch;
		//$rfc=$myrow['rfc'];
		//$charelectronic=$charelectronic.'|'.$rfccliente;
		$nombre=$myrow['name'];
		$charelectronic=$charelectronic.'|'.$nombre;
		//		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
		//			$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		//		}else{
		//			$pais="Mexico";
		//		}
                
        $pais= $myrow["custpais"];
        if (empty($myrow["custpais"])){
            $pais= "Mexico";
        }      
                
		//$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		$charelectronic=$charelectronic.'|'.$pais;
		$calle=$myrow['braddress1'];
		$charelectronic=$charelectronic.'|'.$calle;
		$noext=$myrow['brnumext'];
		$charelectronic=$charelectronic.'|'.$noext;
		$noint=$myrow['brnumint'];
		$charelectronic=$charelectronic.'|'.$noint;
		
		//$colonia=$myrow['braddress6'];
        $colonia=$myrow['braddress6'];
		$charelectronic=$charelectronic.'|'.$colonia;
		$localidad="";
		$charelectronic=$charelectronic.'|'.$localidad;
		$referenciacalle="";
		$charelectronic=$charelectronic.'|'.$referenciacalle;
		$municipio=$myrow['braddress2'];
		$charelectronic=$charelectronic.'|'.$municipio;
		$edo=$myrow['braddress3'];
		$charelectronic=$charelectronic.'|'.$edo;
		$cp=$myrow['braddress4'];
		$charelectronic=$charelectronic.'|'.$cp;
		
		
		$charelectronicEmbarque='|'.chr(13).chr(10).'09';
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['specialinstructions'];
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr1'];//calle
		$charelectronicEmbarque=$charelectronicEmbarque.'|';//noext
		$charelectronicEmbarque=$charelectronicEmbarque.'|';//no int
		
		/*charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr2'];//colonia
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr3'];//municipio
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr4'];//cp
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr5'];//estado
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr6'];//pais*/
                
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr6'];//colonia
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr2'];//municipio
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr3'];//cp
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr4'];//estado
        //$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['custpais'];//pais
        
        if (!empty($myrow['brpostaddr7'])){
            $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr7'];
        } else {
            $charelectronicEmbarque=$charelectronicEmbarque.'|0';
        }
	}

	// cadena para datos de los productos
	// productos vendidos
	$charelectronicdetail='';
	$charelectronicinidet='|'.chr(13).chr(10).'05';
	// datos de ivas
	$charelectronictaxs='';
	$charelectronictaxsini='|'.chr(13).chr(10).'07';
    debug_sql($descripcionOT, __LINE__,false,__FILE__,'blue');
    debug_sql($nivelfacturacion, __LINE__,false,__FILE__,'blue');
    debug_sql($OT, __LINE__,false,__FILE__,'blue');


    // validar si existen las tablas de activos
    $consulta_col= "SHOW TABLES LIKE '%leasingCharges%'";
    $resultado_col= DB_query($consulta_col, $db);
    $cols_activo= "";
    $joins_activo= "";
    $usaactivos= false;
    
    if (DB_fetch_array($resultado_col)){
        $usaactivos= true;
        $cols_activo= ", leasingCharges.barcode,
        				leasingCharges.serialno,
                        fixedassets.description AS desc_activo,
                        CASE WHEN leasingCharges.hours IS NULL OR leasingCharges.hours= 0 THEN leasingCharges.days ELSE leasingCharges.hours END cantidad_activo,
                        CASE WHEN leasingCharges.hours IS NULL OR leasingCharges.hours= 0 THEN 'Dias' ELSE 'Horas' END unidad_activo";
        
        $joins_activo= " LEFT JOIN leasingCharges ON stockmoves.ref2 = leasingCharges.idleasincharges 
                         LEFT JOIN fixedassets ON leasingCharges.serialno = fixedassets.serialno AND leasingCharges.barcode= fixedassets.barcode ";
    }
    
	if ($rfccliente!="XAXX010101000" OR $_SESSION['DesgloseIVA']==1){
		if($TipoAgrupacionXTag==1 and $TypeInvoice <> 111){
			$sqldetails = 'SELECT stockmoves.stkmoveno,
						-- stockmoves.stockid,
					    ProdLine.prodLineId as stockid,
						stockmoves.warranty,
						-- stockmaster.description,
						ProdLine.Description as description,
						/*stockmaster.serialised,
						stockmaster.controlled,*/
						0 as serialised,
						0 as controlled,
						sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						sum((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxnet,
						sum((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)-totaldescuento)/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)  as subtotal,
						-- sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)/case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
						stockcategory.MensajePV,
					sum(stockmoves.totaldescuento) as totaldescuento,
					stockmoves.ref4 as movorderline,
					stockmaster.flagadvance,
					stockmoves.ref2 ' . $cols_activo . '
				FROM stockmoves ' .  $joins_activo . ',stockmaster,stockcategory 
					 	INNER  JOIN ProdLine ON ProdLine.prodLineId=stockcategory.prodLineId
				WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid
				GROUP BY ProdLine.Description,ProdLine.prodLineId';
		}else{//
			//                    echo '<br>START<br>';
			$sqldetails = 'SELECT stockmoves.stkmoveno,
						stockmoves.stockid,
						stockmoves.warranty,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mbflag,
						stockmaster.serialised,
						stockmaster.controlled,
						(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)) AS fxnet,
						((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price))-totaldescuento as subtotal,
						(stockmoves.price) AS fxprice,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
						stockcategory.MensajePV,
						stockmoves.totaldescuento,
						stockmaster.flagadvance,
						stockmoves.ref4 as movorderline, stockmoves.ref2 ' . $cols_activo . '
				FROM stockmoves' .  $joins_activo . ',stockmaster,stockcategory
				WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND (stockmoves.show_on_inv_crds=1 or stockmoves.stockid ="facturaremision")
					AND stockcategory.categoryid = stockmaster.categoryid';
		}
		
		debug_sql($sqldetails, __LINE__,$debug_sql,__FILE__);
		$resultdetails=DB_query($sqldetails,$db);
		$nolinea=0;
		$descrprop="";////
	
		while ($myrow2=DB_fetch_array($resultdetails)){
			$stockid = "";
			$stockcantidad = 0;
			$stockcantidadCompleta = 0;
			$stocknegativo = 0; //obtener si es negativo para precio
            $movorderlineno = $myrow2['movorderline'];
            if ($usaactivos && !empty($myrow2['ref2'])){
            	$stockid = $myrow2['barcode']; 
            	$stockcantidad=number_format($myrow2['cantidad_activo'],$decimalplaces,'.','');
            }else{
            	$stockid = $myrow2['stockid']; 
            	$stockcantidad=number_format($myrow2['quantity'],$decimalplaces,'.','');
            	$stockcantidadCompleta = $myrow2['quantity'];
            }
            if ($stockcantidad < 0) {
            	//Cantidad en negativo
            	$stocknegativo = 1;
            }
			if($_SESSION['DescrLargaFact'] == $myrow2['mbflag']){
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'].' '.$myrow2['longdescription'];
				$stockdescrip=$myrow2['description'].' '.$myrow2['longdescription'].' ';
			}else{
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
				$stockdescrip=$myrow2['description'].' ';
			}
			if ($usaactivos && !empty($myrow2['ref2'])){
				$stockdescrip=$myrow2['desc_activo'];
			}
			
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			//$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			
			/*if ($_SESSION['UserID'] == 'desarrollo' or $_SESSION['UserID'] == 'iortiz') {
				$stockcantidad = abs($stockcantidad);
			}*/

			$charelectronicdetail=$charelectronicdetail.'|'.abs($stockcantidad); //Cantidad como este en DB + o -
			
			if($TypeInvoice == 111){
				
				$SQL="SELECT chartmaster.accountcode,
							chartmaster.accountname
					FROM gltrans
						INNER JOIN chartmaster 	ON gltrans.account = chartmaster.accountcode
					WHERE gltrans.type =  '".$TypeInvoice."'
						AND gltrans.typeno = '".$InvoiceNo."'
						AND gltrans.stockid = '".$stockid."'
						AND gltrans.amount > 0";
				debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
				$Result= DB_query($SQL,$db);
				if (DB_num_rows($Result)==1) {
					$myrowcuentas = DB_fetch_array($Result);
					$cuenta = " Cuenta ".$myrowcuentas['accountcode']." ".$myrowcuentas['accountname'];
				}
				$stockdescrip = $stockdescrip.$cuenta;
			}
			
			// valores de los vendedorfees por linea//
			$descrprop=" (";
			$sqlpropertyes="SELECT DISTINCT p.InvoiceValue AS val,
                                            p.complemento,
                                            sp.label
                                        FROM salesstockproperties p,
                                        stockcatproperties sp,
                                        stockmaster sm
                                        WHERE p.stkcatpropid=sp.stkcatpropid
                                          AND sp.categoryid=sm.categoryid
                                          AND sp.reqatprint=1
                                          AND p.orderno=" . $orderno . "
                                          AND p.typedocument IN(10,110, '" . $TypeInvoice . "')
                                          AND sm.stockid='" . $stockid . "'
                                          AND p.orderlineno = '".$movorderlineno."'";
                        
                        //echo $sqlpropertyes;
			
			$resultpropertyes=DB_query($sqlpropertyes,$db);
                        
                        $xt=0; 
                        
			while ($myrowprop=DB_fetch_array($resultpropertyes)){
				// 				$traba=explode(" ",$myrowprop['val']);
				// 				$trab=$traba[0];
				// 				$trab=$myrowprop['val'];****
				if ($xt==0) {
					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$myrowprop['val'];
				} else {
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$myrowprop['val'];
				}

				switch ($myrowprop['val']) {
					case 'Interno':
						$sqlbomb = "SELECT class FROM stockclass WHERE idclass = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['class'];
						
						break;
					case 'Externo':
						$sqlbomb = "  SELECT suppname FROM suppliers WHERE supplierid = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['suppname'];
						break;
					default:
						break;
				}

				$xt=$xt+1;
			}
			$descrprop=$descrprop." )";
			if ($xt==0){
				$descrprop="";
			}
			if($myrow2['MensajePV'] <> ""){
				$descrprop = $descrprop.$myrow2['MensajePV'];
			}


			//Se ha eliminado el trabajador de la descripcion del XML
			//printecho("entroant", "echo");

			$numberserie="";
			$xserie= 0;

            if (strpos("@".$_SESSION ['DatabaseName'], "gruposervillantas")) {
                $sqlserials="SELECT DISTINCT stockserialitems.serialno AS serie
							FROM workorders 
							INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
							WHERE orderno= '".$orderno."'
							AND stockserialitems.customs= '".$stockid."' 
							AND qualitytext NOT LIKE '%rechaz%' AND stockserialitems.serialno NOT LIKE 'R-%'
							AND stockserialitems.stockid= 'cascli'";

				//echo "series 2: ".$sqlserials;		

				$resultado= DB_query($sqlserials,$db);
				
				while ($myrownseries=DB_fetch_array($resultado)) {
					if ($stockid!='cascli'){
						$consulta= "SELECT DISTINCT stockserialitems.serialno AS serie
									FROM workorders 
									INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
									WHERE orderno= '".$orderno."'
									AND stockserialitems.customs= '".$stockid."'
									AND qualitytext LIKE '%rechaz%' 
									AND stockserialitems.serialno LIKE '%".$myrownseries['serie']."%';";

						$resultado2= DB_query($consulta, $db);

						if ($registro=DB_fetch_array($resultado2)){
							continue;
						}
					}						
					
					if ($xserie==0){
						$numberserie=' Series: '.$myrownseries['serie'];
					}else{
						$numberserie=$numberserie.', '.$myrownseries['serie'];
					}

					$xserie++;
				}
            }         

			$descrnarrative="";
                        //
            if($_SESSION ['FormatoDeComentarioEnPV']==1){
               	$descrnarrative=str_replace('\r','',str_replace('\n', chr(60).'br'.chr(62),$myrow2['narrative']));
				$descrnarrative=str_replace('|', '', $descrnarrative); 
				$descrnarrative=str_replace(chr(10), chr(60).'br'.chr(62), $descrnarrative);
            }else{
                $descrnarrative=str_replace('\r','',str_replace('\n','',$myrow2['narrative']));
				$descrnarrative=str_replace('|', '', $descrnarrative);
				$descrnarrative=str_replace(chr(10), chr(60).'br'.chr(62), $descrnarrative);
            }		
			/*
			if ($TypeInvoice==11){
				$tablades=0;
			}else{
				$tipodoc=70;
			}
			*/
			$sqlnarrative="SELECT narrative
							FROM salesorderdetails 
							WHERE salesorderdetails.orderno=" . $orderno . "
				   				AND salesorderdetails.stkcode='" . $stockid . "'
				   				AND salesorderdetails.orderlineno = '" . $movorderlineno . "'";

			$resultnarrative=DB_query($sqlnarrative,$db);

			while ($myrownarrative=DB_fetch_array($resultnarrative)){
				if ($myrownarrative['narrative'] != $myrow2['narrative']) {
					$descrnarrative=str_replace('\r','',str_replace('\n','',$myrownarrative['narrative']));
					$descrnarrative=str_replace('|', '', $descrnarrative);
				}
			}

			if (strlen($descrnarrative)==0){
				$descrnarrative = "";
			}

			//$descrnarrative="";
			// consulta de aduana
			$stkmoveno=$myrow2['stkmoveno'];
			$almacen=$myrow2['almacen'];			
			$xserie=0;
			/*$sqlserials="SELECT stockserialitems.serialno as serie
				    FROM stockserialmoves , stockmoves , stockserialitems
				    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
					AND stockserialmoves.stockid=stockserialitems.stockid
					AND stockserialmoves.serialno=stockserialitems.serialno
					AND stockserialitems.loccode='".$almacen."'
					AND stockmoves.stkmoveno=".$stkmoveno."
					AND stockmoves.stockid='".$stockid."'";
			//echo '<pre><br>'.$sqlserials;
			$Result= DB_query($sqlserials,$db);
			if (DB_num_rows($Result)==0) {
				$numberserie="";
			}else{
				while ($myrownseries=DB_fetch_array($Result)){
					if ($xserie==0){
						$numberserie=' Series: '.$myrownseries['serie'];
					}else{
						$numberserie=$numberserie.', '.$myrownseries['serie'];
					}
				}
			}
			if (strlen($numberserie)==0){
				$numberserie="";
			}*/


			if($myrow2['showdescription']==1){
				$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
			}else{
				if (strlen($descrnarrative)==0){
					$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
				}else{
					$stockdescripuno=$descrnarrative.$numberserie;
				}
			}//while

			/******INICIO AGREGA LA DESCRIPCION DE LOS ACTIVOS FIJO FACTURADOS
			if($_SESSION ['DatabaseName'] == "erpgosea" or $_SESSION ['DatabaseName'] == "erpgosea_CAPA" or $_SESSION ['DatabaseName'] == "erpgosea_DES"){
				$descfixedassets = "";
				$sqldescfassets = "SELECT CONCAT('Cod.:',l.barcode, ' Serie: ', l.serialno, ' ', f.longdescription) as descfixedassets
					FROM leasingCharges l
						LEFT JOIN fixedassets f ON l.serialno = f.serialno and l.barcode = f.barcode
					WHERE l.salesordernumber = " . $orderno;
				echo "<br>DESC:" . $sqldescfassets;
				$resultdescfassets = DB_query($sqldescfassets,$db);
				while ($myrowfassets = DB_fetch_array($resultdescfassets)){
					if ($descfixedassets == ""){
						$descfixedassets = $myrowfassets['descfixedassets'];
					}else{
						$descfixedassets = $descfixedassets . "<br>" . $myrowfassets['descfixedassets'];
					}
					
				}

				if ($descfixedassets != ""){
					$stockdescripuno = $descfixedassets;
				}
			}*/

			/******INICIO AGREGA LA DESCRIPCION DE LOS ACTIVOS FIJO FACTURADOS*/


			/* Gosea pidio que se agregara una leyenda en la descripcion al inicio 'Renta de' o 'Servicio de' se agrego tambien el numero de serie */
			if($_SESSION ['DatabaseName'] == "erpgosea" or $_SESSION ['DatabaseName'] == "erpgosea_CAPA" or $_SESSION ['DatabaseName'] == "erpgosea_DES"){
				if (isset($tag) and $tag!=''){
					$SQL="SELECT tagref,legalid,legend FROM tagslegend where tagref = " . $tag;
					$resulttaglegend=DB_query($SQL,$db);
					$rowtaglegend = DB_fetch_array($resulttaglegend);
					if($rowtaglegend['legend'] !=''){
						$charelectronicdetail=$charelectronicdetail.'|'.$rowtaglegend['legend'] ." ".$stockdescripuno . ", No. Serie ". $myrow2['serialno'];
					}else{
						$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno. ", No. Serie ". $myrow2['serialno'];
					}
				}else{
					$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno. ", No. Serie ". $myrow2['serialno'];
				}
			}else{
				$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			}
			
			if ($usaactivos && !empty($myrow2['ref2'])){
				$stockprecio=number_format(($myrow2['fxprice']/$myrow2['cantidad_activo']),$decimalplaces,'.','');
			}else{
				$stockprecio=number_format($myrow2['fxprice'],$decimalplaces,'.','');
			}

			if ($usaactivos && !empty($myrow2['ref2'])){
				$stockneto=number_format(($myrow2['fxnet']/$myrow2['cantidad_activo']),$decimalplaces,'.','');
			}else{
				$stockneto=number_format($myrow2['fxnet'],$decimalplaces,'.','');
			}

			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockcantidad: ".$stockcantidad." - stockcantidadCompleta ".$stockcantidadCompleta." - stockprecio: ".$stockprecio." - stockneto: ".$stockneto."<br>";
			}
			
			if ($myrow2['flagadvance'] == 1) {
				//Obtener total de la partida, si existe diferencia obtener precio unitario
				$totalPar = number_format($stockcantidad * $stockprecio,$decimalplaces,'.','');
				//echo "<br>totalPar1: ".$totalPar."<br>";
				if (abs($totalPar) != abs($stockneto)) {
					$stockprecio = number_format($stockneto/$stockcantidad,$decimalplaces,'.','');
				}

				if ($stockcantidad < 0 and $stockprecio > 0) {
					$stockprecio = $stockprecio * (-1);
				}
			}

			if ($stocknegativo == 1 and $stockprecio > 0) {
				//Si cantidad es negativo, poner precio en negativo para operacion
				$stockprecio = $stockprecio * (-1);
			}

			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockprecio change: ".$stockprecio."<br>";
			}
			
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;

			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			if ($usaactivos && !empty($myrow2['ref2'])){
				$stockunits=$myrow2['unidad_activo'];
			}else{
				$stockunits=$myrow2['units'];
			}
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			$subtotal=number_format($myrow2['totaldescuento'],$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;


			$sqladuana="SELECT stockserialitems.customs as aduana,customs_number as noaduana,
						pedimento, DATE_FORMAT(customs_date,'%Y-%m-%d') as fechaaduana
				    FROM stockserialmoves , stockmoves , stockserialitems
				    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
					AND stockserialmoves.stockid=stockserialitems.stockid
					AND stockserialmoves.serialno=stockserialitems.serialno
					AND stockserialitems.loccode='".$almacen."'
					AND stockmoves.stkmoveno=".$stkmoveno."
					AND stockmoves.stockid='".$stockid."'";
                      
			debug_sql($sqladuana, __LINE__,false,__FILE__);
			$Result= DB_query($sqladuana,$db);
			if (DB_num_rows($Result)==0) {
				$nameaduana="";
				$numberaduana="";
				$fechaaduana="";
			}else{
				$myrowaduana = DB_fetch_array($Result);
				$nameaduana=$myrowaduana['aduana'];
				$numberaduana=$myrowaduana['noaduana'];
				$fechaaduana=$myrowaduana['fechaaduana'];
				//$separaaduana=explode("|",$aduana);
				//$nameaduana = $separaaduana[0];
				//$numberaduana= $separaaduana[1];
				//$fechaaduana= $separaaduana[2];
				if (strlen($nameaduana)==0 or strlen($numberaduana)==0 /*or !Is_Date($fechaaduana)*/){
					$nameaduana="";
					$numberaduana="";
					$fechaaduana="";
				}
				$nameaduana = $myrowaduana['aduana'];
				$numberaduana = $myrowaduana['noaduana'];
				$fechaaduana = $myrowaduana['fechaaduana'];
				$pedimento = $myrowaduana['pedimento'];
			}
                       
			if($myrow['type'] == 66) { // Si es factura remision

				$ordernotmp = $myrow['orderno'];

				$SQL = "
					SELECT GROUP_CONCAT(debtortrans.order_) AS order_
					FROM invoicetoremision
					INNER JOIN debtortrans
					ON debtortrans.id = invoicetoremision.idremision
					WHERE invoicetoremision.idinvoice = '".$myrow['iddocto']."'
				";
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);

				$rstmp      = DB_query($SQL, $db);
				$rowtmp     = DB_fetch_array($rstmp);
				$ordernotmp = $rowtmp['order_'];

				if(empty($ordernotmp) == FALSE) {

					$SQL = "
						SELECT
						DATE_FORMAT(purchorderdetails.datecustoms,'%Y-%m-%d') AS fechaaduana,
						pedimento,
						customs AS aduana
						FROM purchorders
						INNER JOIN purchorderdetails
						ON purchorderdetails.orderno = purchorders.orderno
						WHERE purchorders.requisitionno IN ($ordernotmp)
						AND purchorderdetails.itemcode = '$stockid'
					";
					debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
					$rstmp = DB_query($SQL, $db);

					if($rowtmp = DB_fetch_array($rstmp)) {
						$fechaaduana = $rowtmp['fechaaduana'];
						$numberaduana = $rowtmp['pedimento'];
						$nameaduana = $rowtmp['aduana'];
					}
				}
			}

			$charelectronicdetail=$charelectronicdetail.'|'.$nameaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$numberaduana;
			//$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$almacen;
			
			if($TipoAgrupacionXTag==1 and $TypeInvoice <> 111){
				
				$sqlstockmovestaxes="SELECT stockmovestaxes.stkmoveno,
											taxauthid,taxrate,
											taxcalculationorder,
											taxontax,
											taxauthorities.description,
  											sum(stockmovestaxes.taxrate*(((stockmoves.price*(stockmoves.qty*-1))-(CASE WHEN stockmoves.type=11 then stockmoves.totaldescuento*-1 else stockmoves.totaldescuento end)))) as iva
	 								FROM  stockmovestaxes inner join stockmoves on stockmoves.stkmoveno=stockmovestaxes.stkmoveno
										LEFT join stockmaster on stockmaster.stockid=stockmoves.stockid
										LEFT  JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid 
										LEFT  JOIN taxauthorities ON  taxauthorities.taxid=stockmovestaxes.taxauthid
					     WHERE type=".$TypeInvoice. " AND transno=".$InvoiceNo." And stockcategory.prodLineId='".$myrow2['stockid']."'";
			}else{
			
				$sqlstockmovestaxes="SELECT stkmoveno,taxauthid,taxrate,
						taxcalculationorder,
						taxontax,
						taxauthorities.description
					     FROM  stockmovestaxes
                                             LEFT JOIN taxauthorities ON taxauthorities.taxid=stockmovestaxes.taxauthid
					     WHERE stkmoveno=".$stkmoveno;
			}
               
            debug_sql($sqlstockmovestaxes, __LINE__,$debug_sql,__FILE__);
			$resultstockmovestaxes=DB_query($sqlstockmovestaxes,$db);
			while ($myrow3=DB_fetch_array($resultstockmovestaxes)){
                if(empty($myrow3['description']) == false){
                    $impuesto=$myrow3['description'];
                }else{
                    $impuesto="IVA";
                }
				
				$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
				$taxrate=number_format($myrow3['taxrate']*100,$decimalplaces,'.','');
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				if($TipoAgrupacionXTag==1){
					//$taxratetotal=number_format(abs($myrow3['iva']),$decimalplaces,'.','');
					$taxratetotal = abs($myrow3['iva']);
					$taxratetotal = number_format($taxratetotal,$decimalplaces,'.','');
				}else{
					//$taxratetotal=number_format($myrow3['taxrate']*($subtot),$decimalplaces,'.','');
					$taxratetotal = $myrow3['taxrate'] * $myrow2['subtotal'];
					$taxratetotal = number_format($taxratetotal,$decimalplaces,'.','');
				}
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
			}
                ///***************************************///
                $TotGeneralCantEmision = 0;
                if ($TypeInvoice == 119) {
                    //*****se agrega cantidad emision solo en remisiones  *///
                    $wo = 'select wo from workorders where orderno="' . ($myrow['orderno']-1) . '"';
                    if ($_SESSION['UserID'] == "desarrollo" and $debug) {
                    	//echo '<pre>Busca OT'.$wo;
                    }
                    debug_sql($wo, __LINE__,$debug_sql,__FILE__);
                    $rwo = DB_query($wo, $db);
                    if(DB_num_rows($rwo) > 0){
                        $fwo = DB_fetch_array($rwo);
                        $woo=$fwo['wo'];
                        $SQL=" SELECT legalid
                               FROM tags 
                               WHERE tagref='".$tag."'";
                        debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
                        $legal = DB_query($SQL, $db);
                        list($legalid) = DB_fetch_array($legal);

                        $sqlRequRes = "SELECT t.wostdcost, 
                                                t.worequirements_id, t.stockid, t.description,t.units, t.decimalplaces,
                                                (t.requiredqty) as requiredqty,
                                                (t.expectedcost) as expectedcost,
                                                (t.stdcost) AS costperqty,
                                                t.mbflag,
                                                t.categoryid,
                                                t.categorydescription,
                                                t.taxrate
                                FROM (	SELECT
                                                                worequirements.worequirements_id,
                                                                worequirements.stdcost AS wostdcost,
                                                                worequirements.stockid,
                                                                stockmaster.longdescription as description ,
                                                                stockmaster.decimalplaces,
                                                                stockmaster.mbflag,
                                                                stockmaster.units,
                                                                (stockcostsxlegal.avgcost) as stdcost,
                                                                stockmaster.categoryid,
                                                                stockcategory.categorydescription,
                                                                SUM(if(worequirements.ispercent=1,0,worequirements.qtypu)) AS requiredqty,
                                                                SUM(worequirements.stdcost*worequirements.qtypu) AS expectedcost,
                                                                AVG(worequirements.qtypu) as qtypu,
                                                                stockmaster.taxcatid,
                                                                taxauthrates.taxrate
                                                FROM worequirements
                                                        INNER JOIN stockmaster ON worequirements.stockid=stockmaster.stockid
                                                        INNER JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                                                        INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
                                                        INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = " .$legalid. "
                                                        INNER JOIN woitems ON woitems.stockid=worequirements.masterparentid
                                                WHERE worequirements.wo='" . $woo . "' and woitems.wo=worequirements.wo
                                                        AND stockmaster.mbflag <> 'M'
                                                        /*AND stockmaster.stockid='DOR1-13'*/
                                                        GROUP BY worequirements.stockid) AS t
                                        GROUP BY t.stockid
                                        ORDER BY t.categorydescription";

                        if ($_SESSION['UserID'] == "desarrollo" and $debug) {
                        	//echo '<pre>GET ReqRes:'.$sqlRequRes;
                        }
                    
                        debug_sql($sqlRequRes, __LINE__,$debug_sql,__FILE__);
                        $RequirementsResult = DB_query($sqlRequRes, $db);
                        $TotGeneralCantEmision = 0;
                        while ($RequirementsRow = DB_fetch_array($RequirementsResult)) {
                            $SQL2 = "SELECT trandate,
                                (qty + Case When movs.cantidad Is Null Then 0 Else movs.cantidad End) as qty,
                                standardcost
                                FROM stockmoves
                                Left Join (Select ref4, stockid, reference, sum(qty) as cantidad
                                        From stockmoves
                                        Where type= 815 Group by ref4, stockid, reference) as movs On stockmoves.stockid= movs.stockid 
                                                and stockmoves.reference= movs.reference 
                                                and stockmoves.stkmoveno= movs.ref4
                                WHERE stockmoves.type=28
                                AND stockmoves.reference = '" . $woo['wo'] . "'
                                AND stockmoves.stockid = '" . $RequirementsRow['stockid'] . "'";
							debug_sql($SQL2, __LINE__,$debug_sql,__FILE__);
                            $IssuesResult = DB_query($SQL2, $db, _('Could not retrieve the issues of the item because:'));
                            if ($_SESSION['UserID'] == "desarrollo" and $debug) {
								//echo '<pre>ok'.$SQL2;
								//die('END');
                            }
                            if (DB_num_rows($IssuesResult) > 0) {
                                while ($IssuesRow = DB_fetch_array($IssuesResult)) {
                                    $IssueQty -= $IssuesRow['qty']; // because qty for the stock movement will be negative
                                    $IssueCost -= ($IssuesRow['qty'] * $IssuesRow['standardcost']);
                                }
                            }
                            if($IssueQty>0){
                            	$TotGeneralCantEmision = $IssueQty;
                            }else{
                            	$TotGeneralCantEmision = 0;
                            }
                        }
						//$charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                	}else{
						//$charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                	}
                    
                    $SQLemision="SELECT sum(plcdata.value) AS total,folio
                    FROM worequirements
                    INNER JOIN workorders ON workorders.wo = worequirements.wo
                    INNER JOIN plcdata ON plcdata.stockid = worequirements.stockid AND  plcdata.wo = worequirements.wo
                    INNER JOIN stockmaster ON stockmaster.stockid = worequirements.stockid
                    INNER JOIN stockmaster AS encabezado ON encabezado.stockid = worequirements.parentstockid
                    INNER JOIN debtortrans ON workorders.orderno = debtortrans.order_ AND debtortrans.type = 119 AND debtortrans.transno='".$InvoiceNo."' AND  plcdata.transno = debtortrans.transno
                    WHERE folio IN (SELECT folio FROM debtortrans WHERE order_='".$myrow['orderno']."') GROUP BY folio;";
                    //echo '<br>EmisionAnt'.$TotGeneralCantEmision;
                    
                    debug_sql($SQLemision, __LINE__,$debug_sql,__FILE__);
                    $resultEmision=  DB_query($SQLemision, $db);
                    //echo '<pre>SQLEmision: '.$SQLemision;
                    $myrowEmision=  DB_fetch_array($resultEmision);
                    $TotGeneralCantEmision = 0;
                    $TotGeneralCantEmision=$myrowEmision['total'];
                    //echo '<br>Emision: '.$TotGeneralCantEmision;
                    //$charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                    //echo '<br>Cadena: '.$charelectronicdetail; 
					//echo '<br>END</br>';
                }
                $charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                $charelectronicdetail=$charelectronicdetail.'|'.$movorderlineno;
                $charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
                $charelectronicdetail=$charelectronicdetail.'|'.$pedimento;
                $charelectronicdetail=$charelectronicdetail.'';
				$nolinea=$nolinea+1;
	            /**FIN DE CALCULO DE EMISIONES***/
        }
	}else{//
		if($TipoAgrupacionXTag==1 and $TypeInvoice <> 111){
			$sqldetails = 'SELECT stockmoves.stkmoveno,
						-- stockmoves.stockid,
					    ProdLine.prodLineId as stockid,
						stockmoves.warranty,
						-- stockmaster.description,
						ProdLine.Description as description,
						/*stockmaster.serialised,
						stockmaster.controlled,*/
						0 as serialised,
						0 as controlled,
						sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						sum((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price*IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxnet,
						sum((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price*IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))-totaldescuento)/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)  as subtotal,
						-- sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)/case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						sum((stockmoves.price*IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
						stockcategory.MensajePV,
                        stockmoves.ref4 as movorderline,
                        stockmaster.flagadvance
				FROM stockmoves left JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno, stockmaster,
					stockcategory
					 	INNER  JOIN ProdLine ON ProdLine.prodLineId=stockcategory.prodLineId
						
				WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid
				GROUP BY ProdLine.Description,ProdLine.prodLineId';
		}else{
			$sqldetails = 'SELECT stockmoves.stkmoveno,
						stockmoves.stockid,
						stockmoves.warranty,
						stockmaster.description,
						stockmaster.serialised,
						stockmaster.controlled,
						(case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)))) AS fxnet,
						((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))))-totaldescuento as subtotal,
						(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))) AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
					    stockcategory.MensajePV,
						stockmaster.longdescription,
						stockmaster.mbflag,
                        stockmoves.ref4 as movorderline,
                        stockmaster.flagadvance
					FROM stockmoves inner join stockmaster ON stockmoves.stockid = stockmaster.stockid
								inner join stockcategory on stockcategory.categoryid = stockmaster.categoryid
								left JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno 
				WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid';
		}
		//echo "<pre>".$sqldetails;
        debug_sql($sqldetails, __LINE__,$debug_sql,__FILE__);
        //DB_query("SET NAMES 'utf8';",$db);
		$resultdetails=DB_query($sqldetails,$db);
		while ($myrow2=DB_fetch_array($resultdetails)){
			$stocknegativo = 0; //obtener si es negativo para precio
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$stockcantidad=number_format($myrow2['quantity'],$decimalplaces,'.','');
			$stockcantidadCompleta = $myrow2['quantity'];

			if ($stockcantidad < 0) {
            	//Cantidad en negativo
            	$stocknegativo = 1;
            }

			/*if ($_SESSION['UserID'] == 'desarrollo' or $_SESSION['UserID'] == 'iortiz') {
				$stockcantidad = abs($stockcantidad);
			}*/

			$charelectronicdetail=$charelectronicdetail.'|'.abs($stockcantidad); //Cantidad como este en DB + o -
			$stockdescrip=$myrow2['description'];
			$totalCantEmision = 0;
                        $movorderlineno = $myrow2['movorderline'];
			if($_SESSION['DescrLargaFact'] == $myrow2['mbflag']){
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'].' '.$myrow2['longdescription'];
				$stockdescrip=$myrow2['description'].' '.$myrow2['longdescription'].' ';
			}else{
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
				$stockdescrip=$myrow2['description'].' ';
			}
			
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT DISTINCT p.InvoiceValue AS val,
							                p.complemento,
							                sp.label
							FROM salesstockproperties p,
							     stockcatproperties sp,
							     stockmaster sm
							WHERE p.stkcatpropid=sp.stkcatpropid
							  AND sp.categoryid=sm.categoryid
							  AND p.orderno=" . $orderno . "
							  AND p.typedocument IN(110, '" . $TypeInvoice . "')
							  AND sp.reqatprint=1
							  AND sm.stockid='" . $stockid . "'";
                        
                        //echo $sqlpropertyes;
                        
			$resultpropertyes=DB_query($sqlpropertyes,$db);
			$xt=0;
			while ($myrowprop=DB_fetch_array($resultpropertyes)){
				$traba=explode(" ",$myrowprop['val']);
				$trab=$traba[0];
				if ($xt==0) {
					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				} else {
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
				}

				switch ($myrowprop['val']) {
					case 'Interno':
						$sqlbomb = "SELECT class FROM stockclass WHERE idclass = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['class'];
						break;
					case 'Externo':
						$sqlbomb = "  SELECT suppname FROM suppliers WHERE supplierid = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['suppname'];
						break;
					default:
						
						break;
				}

				$xt=$xt+1;
			}
			$descrprop=$descrprop." )";
			if ($xt==0){//
				$descrprop="";
			}
			if($myrow2['MensajePV'] <> ""){
				$descrprop = $descrprop.$myrow2['MensajePV'];
			}

			/*if ($_SESSION["desarrollo"]){
				echo "series 1: "."SELECT DISTINCT stockserialitems.serialno as serie
						    FROM stockserialmoves , stockmoves , stockserialitems
						    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
							AND stockserialmoves.stockid=stockserialitems.stockid
							AND stockserialmoves.serialno=stockserialitems.serialno
							AND stockserialitems.loccode='".$myrow2['almacen']."' 
							AND qualitytext NOT LIKE '%rechaz%' 
							AND stockmoves.stockid='CASCLI'";		
			}*/

			$numberserie= "";
			$xserie=0;

			if (strpos("@".$_SESSION ['DatabaseName'], "gruposervillantas")) {
                $sqlserials="SELECT DISTINCT stockserialitems.serialno AS serie
							FROM workorders 
							INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
							WHERE orderno= '".$orderno."'
							AND stockserialitems.customs= '".$stockid."' 
							AND qualitytext NOT LIKE '%rechaz%' AND stockserialitems.serialno NOT LIKE 'R-%'
							AND stockserialitems.stockid= 'cascli'";

				//echo "series 2: ".$sqlserials;		

				$resultado= DB_query($sqlserials,$db);
				
				while ($myrownseries=DB_fetch_array($resultado)) {
					if ($stockid!='cascli'){
						$consulta= "SELECT DISTINCT stockserialitems.serialno AS serie
									FROM workorders 
									INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
									WHERE orderno= '".$orderno."'
									AND stockserialitems.customs= '".$stockid."'
									AND qualitytext LIKE '%rechaz%' 
									AND stockserialitems.serialno LIKE '%".$myrownseries['serie']."%'";

						$resultado2= DB_query($consulta, $db);

						if ($registro=DB_fetch_array($resultado2)){
							continue;
						}
					}						
					
					if ($xserie==0){
						$numberserie=' Series: '.$myrownseries['serie'];
					}else{
						$numberserie=$numberserie.', '.$myrownseries['serie'];
					}

					$xserie++;
				}
            }

			$descrnarrative="";
			/*$sqlnarrative="SELECT narrative
				FROM salesorderdetails p
				WHERE p.orderno=" . $orderno . "
				   AND p.stkcode='" . $stockid . "'";
			$resultnarrative=DB_query($sqlnarrative,$db);
			while ($myrownarrative=DB_fetch_array($resultnarrative)){
				$descrnarrative=$myrownarrative['narrative'];
				$descrnarrative=str_replace('|', '', $descrnarrative);
			}*/
			
			$descrnarrative=str_replace('\r','',str_replace('\n','',$myrow2['narrative']));
			$descrnarrative=str_replace('|', '', $descrnarrative);
			
			if (strlen($descrnarrative)==0){
				$descrnarrative="";
			}
                        
			if($myrow2['showdescription']==1){
				$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
			}else{
                if (strlen($descrnarrative)==0){
                    $stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
                }else{
                    $stockdescripuno=$descrnarrative.$numberserie;
                }
			}

			if($_SESSION ['DatabaseName'] == "erpgosea" or $_SESSION ['DatabaseName'] == "erpgosea_CAPA" or $_SESSION ['DatabaseName'] == "erpgosea_DES"){
				if ($myrow2['stockid'] =='10001'){
					$charelectronicdetail=$charelectronicdetail.'|'."RENTA DE ".$stockdescripuno;
				}else{
					$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
				}
			}else{
				$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			}
                        
			// Se QUITO POR EL DE ARRIBA $charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			$stockprecio=number_format($myrow2['fxprice'],$decimalplaces,'.','');
			$stockneto=number_format($myrow2['fxnet'],$decimalplaces,'.','');

			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockcantidad: ".$stockcantidad." - stockcantidadCompleta ".$stockcantidadCompleta." - stockprecio: ".$stockprecio." - stockneto: ".$stockneto."<br>";
			}
			
			if ($myrow2['flagadvance'] == 1) {
				//Obtener total de la partida, si existe diferencia obtener precio unitario
				$totalPar = number_format($stockcantidad * $stockprecio,$decimalplaces,'.','');
				//echo "<br>totalPar1: ".$totalPar."<br>";
				if (abs($totalPar) != abs($stockneto)) {
					$stockprecio = number_format($stockneto/$stockcantidad,$decimalplaces,'.','');
				}

				if ($stockcantidad < 0 and $stockprecio > 0) {
					$stockprecio = $stockprecio * (-1);
				}
			}
			
			if ($stocknegativo == 1 and $stockprecio > 0) {
				//Si cantidad es negativo, poner precio en negativo para operacion
				$stockprecio = $stockprecio * (-1);
			}

			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockprecio change: ".$stockprecio."<br>";
			}

			$charelectronicdetail=$charelectronicdetail.'|'.($stockprecio);
			$charelectronicdetail=$charelectronicdetail.'|'.($stockneto);
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			//$subtotal=number_format($myrow2['subtotal'],2,'.','');
			//$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			// consulta de aduana
			$stkmoveno=$myrow2['stkmoveno'];
			$almacen=$myrow2['almacen'];

			$sqlstockmovestaxes="SELECT stkmoveno,taxauthid,taxrate,taxcalculationorder,taxontax,taxauthorities.description
					     FROM  stockmovestaxes,taxauthorities
					     WHERE taxauthorities.taxid=stockmovestaxes.taxauthid
						   AND stkmoveno=".$stkmoveno;
			
            debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
            debug_sql($sqlstockmovestaxes, __LINE__,$debug_sql,__FILE__);
			$resultstockmovestaxes=DB_query($sqlstockmovestaxes,$db);
			while ($myrow3=DB_fetch_array($resultstockmovestaxes)){
				$impuesto=$myrow3['description'];
				$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
				$taxrate=number_format($myrow3['taxrate']*100,$decimalplaces,'.','');
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				//$subtot = number_format($myrow2['subtotal'], 2); //Siempre se manejo en 2
				//$taxratetotal=number_format($myrow3['taxrate']*($subtot),$decimalplaces,'.','');
				
				$taxratetotal = $myrow3['taxrate'] * $myrow2['subtotal'];
				$taxratetotal = number_format($taxratetotal, $decimalplaces,'.','');

				//$taxtotalratex=$myrow3['taxrate']*($myrow2['fxnet']*$myrow2['fxprice']);
				$taxtotalratex=$myrow3['taxrate']*($myrow2['subtotal']);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
			}

			//$subtotal=number_format($myrow2['subtotal']+$taxtotalratex,$decimalplaces,'.','');
			$subtotal = $myrow2['subtotal'] + $taxtotalratex;
			$subtotal = number_format($subtotal,$decimalplaces,'.','');
			
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			//$subtotal=number_format($myrow2['subtotal']+$taxtotalratex,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;


			$sqladuana="SELECT stockserialitems.customs as aduana,customs_number as noaduana,
					pedimento, DATE_FORMAT(customs_date,'%Y-%m-%d') as fechaaduana
				    FROM stockserialmoves , stockmoves , stockserialitems
				    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
					AND stockserialmoves.stockid=stockserialitems.stockid
					AND stockserialmoves.serialno=stockserialitems.serialno
					AND stockserialitems.loccode='".$almacen."'
					AND stockmoves.stkmoveno=".$stkmoveno."
					AND stockmoves.stockid='".$stockid."'";
			 debug_sql($sqladuana, __LINE__,$debug_sql,__FILE__);
			$Result= DB_query($sqladuana,$db);
			if (DB_num_rows($Result)==0) {
				$nameaduana="";
				$numberaduana="";
				$fechaaduana="";
			}else{
				$myrowaduana = DB_fetch_array($Result);
				$aduana = $myrowaduana['aduana'];
				$separaaduana=explode("|",$aduana);
				$nameaduana = $separaaduana[0];
				$numberaduana= $separaaduana[1];
				$fechaaduana= $separaaduana[2];
				if (strlen($nameaduana)==0 or strlen($numberaduana)==0 /*or !Is_Date($fechaaduana)*/){
					$nameaduana="";
					$numberaduana="";
					$fechaaduana="";
				}
				$nameaduana = $myrowaduana['aduana'];
				$numberaduana = $myrowaduana['noaduana'];
				$fechaaduana = $myrowaduana['fechaaduana'];
				$pedimento = $myrowaduana['pedimento'];
				
			}

			if($myrow['type'] == 66) { // Si es factura remision

				$ordernotmp = $myrow['orderno'];

				$SQL = "
					SELECT GROUP_CONCAT(debtortrans.order_) AS order_
					FROM invoicetoremision
					INNER JOIN debtortrans
					ON debtortrans.id = invoicetoremision.idremision
					WHERE invoicetoremision.idinvoice = '{$myrow['iddocto']}'
				";
				debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
				$rstmp      = DB_query($SQL, $db);
				$rowtmp     = DB_fetch_array($rstmp);
				$ordernotmp = $rowtmp['order_'];

				if(empty($ordernotmp) == FALSE) {

					$SQL = "
						SELECT
						DATE_FORMAT(purchorderdetails.datecustoms,'%Y-%m-%d') AS fechaaduana,
						pedimento,
						customs AS aduana
						FROM purchorders
						INNER JOIN purchorderdetails
						ON purchorderdetails.orderno = purchorders.orderno
						WHERE purchorders.requisitionno IN ($ordernotmp)
						AND purchorderdetails.itemcode = '$stockid'
					";
					debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
					$rstmp = DB_query($SQL, $db);

					if($rowtmp = DB_fetch_array($rstmp)) {
						$fechaaduana = $rowtmp['fechaaduana'];
						$numberaduana = $rowtmp['pedimento'];
						$nameaduana = $rowtmp['aduana'];
					}
					
				}
			}
            
			$charelectronicdetail=$charelectronicdetail.'|'.$nameaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$numberaduana;
			//$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana.'aqui';
			$charelectronicdetail=$charelectronicdetail.'|'.$almacen;
			$charelectronicdetail=$charelectronicdetail.'|'.$totalCantEmision;
			$charelectronicdetail=$charelectronicdetail.'|'.$movorderlineno;
			
			$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$pedimento;

			/*$stockprecio=number_format($myrow2['fxnet']+$taxtotalratex,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=number_format($myrow2['fxnet']+$taxtotalratex,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal*/

		}
                /*if($_SESSION['UserID'] == "desarrollo"){
                    echo '<br><pre>$charelectronicdetail: '.$charelectronicdetail;
                }*/
                
	}
	// complemento INE
    $charelectronicdetailINEComplements="";
    $permiso_complementos = 'SELECT typecomplement FROM custbranch WHERE debtorno="'.$branch.'"';
	$resul_permiso = DB_query($permiso_complementos,$db);
	list($permiso_complemento) = DB_fetch_array($resul_permiso);
	if($permiso_complemento==1){
		$Select_INE = "SELECT typeprocess,
							typecommittee,
							entity, 
							idaccounting, 
							orderno,
							IFNULL(scope,'') as scope 
						FROM complementsine 
						WHERE orderno = '" . $orderno ."'";
		$Result_INE = DB_query($Select_INE, $db);
		$charelectronicdetailINEComplements = '|'.chr(13).chr(10).'010';
        while($ine = DB_fetch_array($Result_INE)){
        	$tipoproceso = utf8_decode($ine['typeprocess']);

        	$tipoproceso =  str_replace(chr(63),chr(241), $tipoproceso);
			$detallesINE .= $tipoproceso . '|'.$ine['typecommittee'].'|'.$ine['entity'].'|'.$ine['idaccounting'].'|'.$ine['orderno'].'|'.$ine['scope'];
		}
		$charelectronicdetailINEComplements=$charelectronicdetailINEComplements.'|'.$detallesINE;
		if ($_SESSION ['UserID'] == 'saplicaciones' ) {
			echo "<br>" . $charelectronicdetailINEComplements;
		}
    }
	// ivas retenidos

	if($_SESSION['UserID'] == "desarrollo"){
		//	echo __line__.'<br><pre>$charelectronic: '.$charelectronic;
		//	echo __line__.'<br><pre>$charelectronicdetail: '.$charelectronicdetail;
		//		echo __line__.'<br><pre>$charelectronictaxsret: '.$charelectronictaxsret;
		//		echo __line__.'<br><pre>$charelectronictaxsfederal: '.$charelectronictaxsfederal;
		//		echo __line__.'<br><pre>$charelectronictaxslocal: '.$charelectronictaxslocal;
		//		echo __line__.'<br><pre>$charelectronicEmbarque: '.$charelectronicEmbarque;
		//		echo __line__.'<br><pre>$totalCantEmision: '.$totalCantEmision;
		//                echo __line__.'<br><pre>$charelectronicdetailINEComplements: '.$charelectronicdetailINEComplements;
	}
        if(empty($totalCantEmision) == true){
            $totalCantEmision = 0;
        }
        if($nivelfacturacion==1){
                            debug_sql('TIPO OT', __LINE__,$debug_sql,__FILE__);
                          $stockid=$OT;  
                          $stockcantidad = 1;
                          $stockdescrip = $descripcionOT;
                        }elseif($nivelfacturacion==2){
                            debug_sql('TIPO CONCEPTOS', __LINE__,$debug_sql,__FILE__);
                        
                        }
                        elseif($nivelfacturacion==3){
                            debug_sql('Productos manufacturacios', __LINE__,$debug_sql,__FILE__);
                        }
	// 	 die("END XSA");

	if ($rfccliente=="XAXX010101000" && $_SESSION['DesgloseIVA']!=1){
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsret.$charelectronictaxsfederal.$charelectronictaxslocal.$charelectronicEmbarque.$totalCantEmision.$charelectronicdetailINEComplements;
		$cadenaenviada=$cadenaenvio;//retornarString($cadenaenvio);
	}else{
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsfederal.$charelectronictaxs.$charelectronictaxslocal.$charelectronicEmbarque.$totalCantEmision.$charelectronicdetailINEComplements;
		$cadenaenviada=$cadenaenvio;//retornarString($cadenaenvio);
	}
        
    //debug_sql('cadena original <br>'.$cadenaenviada, __LINE__,true,__FILE__,'blue');
    //echo "<br> XSAInvoicing: ".$cadenaenviada."<br>";
    return $cadenaenviada;
}
/*
	Funcion con datos del CFDI 3.2 y 3.3
*/
//
function XSAInvoicing($InvoiceNo, $orderno, $debtorno, $TypeInvoice,$tag,$serie,$folio, &$db,$nivelfacturacion=2,$descripcionOT='',$OT=''){
	// *********************************************** //
    // ********************************************** //
    // ******** No dejar echo en la funcion *********//
    // ******** Declarar Variables a usar ********  //
    // ******** Afecta al punto de venta ********* //
    // ****************************************** //
    // ***************************************** //
	
    $debug_sql = false;
	$charelectronic='01'; //Cad 01, pos 0
	$charelectronicEmbarque='';
	$charelectronic=$charelectronic.'|'.$serie; //Cad 01, pos 1
	$serieelect=$serie;
    $pedimento="";
    $subtotalInvoice=0;
    $versionAddenda=0;
    
    if(isset($_GET['versionAddenda'])){
        $versionAddenda = $_GET['versionAddenda'];
    }

    if(isset($_POST['versionAddenda'])){
        $versionAddenda = $_POST['versionAddenda'];
    }

	//$serieelect=$TypeInvoice.'-'.$_SESSION['Tagref'];
    $folioelect="";//$folio;//$InvoiceNoTAG;
    if ($TypeInvoice == 66) {
    	$folioelect = $folio;
    }
	//$folioelect=$InvoiceNo;
	$charelectronic=$charelectronic.'|'.$serieelect; //Cad 01, pos 2
	$charelectronic=$charelectronic.'|'.$folioelect; //Cad 01, pos 3
	$charelectronictaxsfederal='';
	$charelectronictaxslocal='';
	$charelectronictaxsret = '';
	// consulto datos de la factura
	$imprimepublico=0;
	$rfccliente = "";
	$TipoAgrupacionXTag = "";
    $branch= "";
    $descrestante = "";

	if ($TypeInvoice==11){
		$tabla='notesorders';
	}else{
		$tabla='salesorders';
	}
	
	$decimalplaces=6;
	
	if ($_SESSION['DecimalPlacesInvoice']==''){
		$_SESSION['DecimalPlacesInvoice']=6;
	}
	$decimalplaces=$_SESSION['DecimalPlacesInvoice'];

	$arrayBDBombeo = array('erptycqsa','erptycqsa_CAPA','erptycqsa_DES' );
	$strFiltroBombeo="";
	if(in_array($_SESSION['DatabaseName'], $arrayBDBombeo)){
		if($TypeInvoice=='66'){
			$strFiltroBombeo=" AND stockmoves.stockid != 'SB'";
		}
		
	}

	//Agregar datos de CFDI 3.3
	$SQLInvoice = "SELECT replace(debtortrans.trandate,'-','/') as trandate,debtortrans.ovamount,debtortrans.ovdiscount,
			debtortrans.ovfreight,debtortrans.ovgst,debtortrans.rate as cambio,
			debtortrans.order_,debtortrans.invtext,	debtortrans.consignment,
			debtortrans.id AS iddocto, debtortrans.type,
			debtorsmaster.name,debtorsmaster.address1,debtorsmaster.address2,
			debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,
			debtorsmaster.address6,debtorsmaster.invaddrbranch,
			custbranch.taxid as rfc,paymentterms.terms,salesorders.deliverto,
			salesorders.deladd1,salesorders.deladd2,salesorders.deladd3,
			salesorders.deladd4,salesorders.deladd5,salesorders.deladd6,
			salesorders.customerref,salesorders.orderno,salesorders.orddate,
			locations.locationname,	shippers.shippername,custbranch.brname,
			custbranch.braddress1,custbranch.braddress2,custbranch.braddress3,
			custbranch.braddress4,custbranch.braddress5,custbranch.braddress6,
			custbranch.brpostaddr1,custbranch.brpostaddr2,custbranch.brpostaddr3,
			custbranch.brpostaddr4,custbranch.brpostaddr5,custbranch.brpostaddr6,
			salesman.salesmanname,debtortrans.debtorno,debtortrans.branchcode,debtortrans.folio,
			replace(origtrandate,'-','/') as origtrandate,debtortrans.currcode,custbranch.branchcode,
			salesorders.salesman,salesorders.UserRegister,custbranch.phoneno as telofi,
			salesorders.placa, salesorders.serie,salesorders.kilometraje,salesorders.comments,debtortrans.ref1,
			salesorders.ordertype,debtortrans.paymentname,debtortrans.nocuenta,
			custbranch.brnumext, custbranch.brnumint,custbranch.specialinstructions,
			tags.typegroup,debtortrans.id, custbranch.custpais, IFNULL(paymentmethods.codesat,'') as codesat,
			debtortrans.codesat as cadenacodesat, debtortrans.order_,
			debtortrans.c_TipoDeComprobante,
			debtortrans.c_UsoCFDI,
			debtortrans.c_paymentid,
			debtortrans.claveFactura,
			currencies.decimalplaces,
			coalesce(log_cancelacion_sustitucion.id,0) as idsustitucion
		FROM debtortrans
			LEFT JOIN log_cancelacion_sustitucion ON  debtortrans.id = log_cancelacion_sustitucion.id
			LEFT JOIN currencies ON currencies.currabrev = debtortrans.currcode
			LEFT JOIN paymentmethods ON debtortrans.paymentname = paymentmethods.paymentname
			left join shippers on debtortrans.shipvia=shippers.shipper_id 
			join tags on tags.tagref=debtortrans.tagref,
			debtorsmaster,custbranch,".$tabla." as salesorders 
			left join salesman on salesorders.salesman=salesman.salesmancode 
			left join locations on salesorders.fromstkloc=locations.loccode ,
			paymentterms 

		WHERE debtortrans.order_ = salesorders.orderno
			AND debtortrans.type='".$TypeInvoice."'
			AND debtortrans.transno='" . $InvoiceNo . "'
			AND debtortrans.tagref='" . $tag . "'
			AND debtortrans.debtorno=debtorsmaster.debtorno
			AND salesorders.paytermsindicator=paymentterms.termsindicator
			AND debtortrans.debtorno=custbranch.debtorno
			AND debtortrans.branchcode=custbranch.branchcode";
	
    if($_SESSION['UserID'] == "desarrollo"){
         echo '<pre> SQLInvoice 1 :<br>'.$SQLInvoice.'<br>';

    	// echo "<br>SQL XSAInvoicing: <pre>";
    	// 	print_r($SQLInvoice);
    	// echo "</pre><br>";

	}
	//echo '<pre>get > 1 :<br>'.$SQLInvoice.'<br>';
	//echo '<pre>get > 1 :<br>'.$SQLInvoice.'<br>';
	//echo '<pre>SQL Principal: <br>'.$SQLInvoice;

    //debug_sql($SQLInvoice, __LINE__,true,__FILE__);
	$Result=DB_query($SQLInvoice,$db);
	$blnFacturaAnticipo3_3 = false;
	$blnFlagAnticipo3_3 = false;
	$strLegendAnticipo="";
	$dblImporteDescuentoAnticipo=0;
	//$strSearchAnticipo="";
	$strSQLAnticipo="";
	$queryFacturaAnticipo="";
	$montoNotesorders_invoice = 0;
	
	$categoriaAnticipo="ANT";

	$strSearchAnticipo="";
	$myrow['type'] = "";
	$myrow['orderno'] = "";
	$myrow['iddocto'] = "";
	if (DB_num_rows($Result)>0) {
		$myrow = DB_fetch_array($Result);
		$myrow['type'] = $myrow['type'];
		$myrow['orderno'] = $myrow['orderno'];
		$myrow['iddocto'] = $myrow['iddocto'];
		//impuestos federales
		$totretencionfederal=0;
		//$charelectronictaxslocal='|'.chr(13).chr(10);
		if(isset($_SESSION['ordenaimpuestos']) AND $_SESSION['ordenaimpuestos']==1 ){
            $qry = "Select debtortranstaxesclient.*, sec_taxes.nametax, sec_taxes.flagimpuestolocal, sec_taxes.c_Impuesto, sec_taxes.c_TipoFactor, sec_taxes.percent
            		from debtortranstaxesclient, sec_taxes
					where debtortranstaxesclient.idtax = sec_taxes.idtax
						AND sec_taxes.typetax=1 and orden!=0
						and debtortranstaxesclient.iddoc = ".$myrow['id']. " ORDER BY orden ASC";
		}else{
            $qry = "Select debtortranstaxesclient.*, sec_taxes.nametax, sec_taxes.flagimpuestolocal, sec_taxes.c_Impuesto, sec_taxes.c_TipoFactor, sec_taxes.percent
            		from debtortranstaxesclient, sec_taxes
					where debtortranstaxesclient.idtax = sec_taxes.idtax
						AND sec_taxes.typetax=1
						and debtortranstaxesclient.iddoc = ".$myrow['id'];
		}
            
        debug_sql($qry, __LINE__,$debug_sql,__FILE__);
		$rs = DB_query($qry,$db);//
		$totalImpuestosLocales=0;
		if (DB_num_rows($rs) > 0){
			//$charelectronictaxsfederalini='|'.chr(13).chr(10).'06';
			while ($regs = DB_fetch_array($rs)){
                if($regs['flagimpuestolocal']==0){
                    $charelectronictaxsfederal.='|'.chr(13).chr(10).'06'."|".$regs['nametax']."|".$regs['percent']."|".$regs['amount']."|".$regs['flagimpuestolocal']."|".$regs['c_Impuesto']."|".$regs['c_TipoFactor']."|".$regs['percent'];
				$totretencionfederal=$totretencionfederal+$regs['amount'];
                }
                if($regs['flagimpuestolocal']==1){
                    $charelectronictaxsfederal.='|'.chr(13).chr(10).'08'."|".$regs['nametax']."|".$regs['percent']."|".$regs['amount']."|".$regs['flagimpuestolocal']."|".$regs['c_Impuesto']."|".$regs['c_TipoFactor']."|".$regs['percent'];
				$totretencionfederal=$totretencionfederal+$regs['amount'];
                }
			}
		}

		//impuestos locales
		$totalretencionlocal=0;
		//$charelectronictaxslocal='|'.chr(13).chr(10);
		$qry = "Select debtortranstaxesclient.*, sec_taxes.nametax, sec_taxes.flagimpuestolocal, sec_taxes.c_Impuesto, sec_taxes.c_TipoFactor, sec_taxes.percent
				from debtortranstaxesclient, sec_taxes
				where debtortranstaxesclient.idtax = sec_taxes.idtax
				AND sec_taxes.typetax=2
				and debtortranstaxesclient.iddoc = ".$myrow['id'];
		debug_sql($qry, __LINE__,$debug_sql,__FILE__);
		$rs = DB_query($qry,$db);
		$totalImpuestosLocales=0;
		if (DB_num_rows($rs) > 0){
			$charelectronictaxslocal='08';
			while ($regs = DB_fetch_array($rs)){
				$charelectronictaxslocal.="|".$regs['nametax']."|".$regs['percent']."|".$regs['amount']."|".$regs['flagimpuestolocal']."|".$regs['c_Impuesto']."|".$regs['c_TipoFactor']."|".$regs['percent'];
				$totalretencionlocal=$totalretencionlocal+$regs['amount'];
			}
			$charelectronictaxslocal.=chr(13).chr(10);
		}

		$totalretencion=$totretencionfederal+$totalretencionlocal;
		//Tipo agrupacion
		$TipoAgrupacionXTag=$myrow['typegroup'];
		// fecha emision
		$fechainvoice=$myrow['origtrandate'];
		
		if($myrow['idsustitucion'] > 0){
			//Para las factuas de sustitucion que tome la fecha actual
			
			$fechainvoice = date('Y/m/d h:i:s');
		}

		$UserRegister=$myrow['UserRegister'];
		$charelectronic=$charelectronic.'|'.$fechainvoice; //Cad 01, pos 4

		/********
		$metodoPago = $myrow['paymentname'];
		if ($metodoPago=="")
			$metodoPago = "No Identificado";
		*/
		
		$metodoPago = "";

		if (trim($myrow['cadenacodesat'] != "")) {
			$metodoPago = $myrow['cadenacodesat'];

			$metodosDePagos = explode(",", $myrow['cadenacodesat']);
			$metodoPago=$metodosDePagos[0];
		}else{
			$metodoPago = $myrow['codesat'];
		
			if($metodoPago == "") {
				$metodoPago = "99";
			}
		}
		
		$nocuenta = $myrow['nocuenta'];
		if ($nocuenta=="")
			$nocuenta = "No Identificado";
	

		// subtotal
		$rfccliente=str_replace("-","",$myrow['rfc']);
		$rfccliente=str_replace(" ","",$rfccliente);
		$nombre=$myrow['name'];
		$subtotal= number_format($myrow['ovamount'], $decimalplaces, '.', '');
		$subtotalInvoice = $subtotal;

		if (strlen($rfccliente)<12){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= number_format($myrow['ovamount']+$myrow['ovgst'], $decimalplaces, '.', '');
			$imprimepublico=1;
		}elseif(strlen($rfccliente)>=14){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= number_format($myrow['ovamount']+$myrow['ovgst'], $decimalplaces, '.', '');
			$imprimepublico=1;
		}

		$charelectronic=$charelectronic.'|'.$subtotal; //Cad 01, pos 5
		
		// total factura
		$decimalplacesuno=6;
		if($TypeInvoice==66 or $TypeInvoice==10 or $TypeInvoice==110){
			$decimalplacesuno=4;
		}
		$total= number_format($myrow['ovamount']+$myrow['ovgst']+$totalretencion,$decimalplaces,'.','');
        $total= number_format(round($myrow['ovamount'],2) + round($myrow['ovgst'],2) + round($totalretencion, 2), $decimalplaces, '.', '');
		
		if($TypeInvoice=='11'){
			$total = abs($total);
		}
		$charelectronic=$charelectronic.'|'.$total; //Cad 01, pos 6
		// total de iva
		$iva=number_format($myrow['ovgst'],$decimalplaces,'.','');
		// transladado
		$charelectronic=$charelectronic.'|'.$iva; //Cad 01, pos 7
		// retenido
		$ivaret=0;
		$charelectronic=$charelectronic.'|'.$ivaret; //Cad 01, pos 8

		//descuento trae desde el stockmoves
		if ($rfccliente!="XEXX010101000" OR $_SESSION['DesgloseIVA']==1){
			$sqldiscount = 'SELECT sum(totaldescuento) as descuento
				       FROM stockmoves
				       WHERE stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref="' . $tag . '"
					AND stockmoves.show_on_inv_crds=1';
                        debug_sql($sqldiscount, __LINE__,$debug_sql,__FILE__);
			$Result= DB_query($sqldiscount,$db);
			if (DB_num_rows($Result)==0) {
				$descuento=0;
			}else{
				$myrowdis = DB_fetch_array($Result);
				$descuento=number_format($myrowdis['descuento'],$decimalplaces,'.','');
			}
		}else{
			$sqldiscount = 'SELECT sum(totaldescuento+(IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)*totaldescuento)) as descuento
			       			FROM stockmoves inner join stockmaster on stockmaster.stockid=stockmoves.stockid
							-- LEFT JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
							LEFT JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno
					
					  		WHERE stockmoves.type='.$TypeInvoice.'
								AND stockmoves.transno=' . $InvoiceNo . '
								AND stockmoves.tagref="' . $tag . '"
								AND stockmoves.show_on_inv_crds=1';
                        debug_sql($sqldiscount, __LINE__,$debug_sql,__FILE__);
			$Result= DB_query($sqldiscount,$db);
			if (DB_num_rows($Result)==0) {
				$descuento=0;
			}else{
				$myrowdis = DB_fetch_array($Result);
				$descuento=number_format($myrowdis['descuento'],2,'.','');
			}	
		}

		$descuento=number_format($descuento,$decimalplaces,'.','');

		

		// if($_SESSION['DatabaseName'] == "erppisumma" or $_SESSION['DatabaseName'] == "erppisumma_CAPA" or $_SESSION['DatabaseName'] == "erppisumma_DES"){
		// 	$categoriaAnticipo="AN1";
		// }

		if ($_SESSION['FacturaVersion'] == "3.3") {
			//Obtener si es una factura de anticipo
			$queryFacturaAnticipo="SELECT debtortrans.id,debtortrans.folio				
								   FROM stockmaster 
										INNER JOIN stockmoves on stockmoves.stockid=stockmaster.stockid 
										INNER JOIN debtortrans ON debtortrans.type=stockmoves.type and debtortrans.transno=stockmoves.transno
										LEFT JOIN (
											SELECT ca1.transid_allocto, (sum(amt)/1.16) as monto
											FROM debtortrans dt1 
												INNER JOIN debtorsmaster dm ON dt1.debtorno = dm.debtorno 
												INNER JOIN custallocns ca1 ON dt1.id = ca1.transid_allocfrom 
												WHERE dt1.id='".$myrow['id']."'
												GROUP BY ca1.transid_allocto
										) as x ON debtortrans.id = x.transid_allocto
									WHERE stockmaster.flagadvance=1 AND stockmoves.qty<0
										AND (
											(debtortrans.invtext NOT LIKE '%cancel%')
											or (debtortrans.invtext LIKE '%cancel%' and debtortrans.ovamount <> 0)
											)
										AND	debtortrans.id='".$myrow['id']."';";
    		if($_SESSION['UserID'] == "desarrollo"){
    			//echo "<br>sql ANTICIPO1: ".$queryFacturaAnticipo;
    		}
										
			$resultFacturaAnticipo = DB_query($queryFacturaAnticipo,$db);

			if(DB_num_rows($resultFacturaAnticipo)<=0){
				if($_SESSION['DatabaseName'] == "erpgruposii" or $_SESSION['DatabaseName'] == "erpgruposii_CAPA" or $_SESSION['DatabaseName'] == "erpgruposii_DES"){
					$strSearchAnticipo=" AND stockmaster.categoryid != 55";
					$categoriaAnticipo="55";
				}else{
					// if($_SESSION['DatabaseName'] == "erppisumma" or $_SESSION['DatabaseName'] == "erppisumma_CAPA" or $_SESSION['DatabaseName'] == "erppisumma_DES"){
					// 	$strSearchAnticipo=" AND stockmaster.categoryid != 'AN1'";
					// 	$categoriaAnticipo="AN1";
					// }else{
					// 	$strSearchAnticipo=" AND stockmaster.categoryid != 'ANT'";
					// }
					$strSearchAnticipo=" AND stockmaster.categoryid != 'ANT'";
					
				}
				
				//$strLegendAnticipo="'CFDI por remanente de un anticipo'";
			}else{
				$blnFacturaAnticipo3_3 = true;

			}

			if($TypeInvoice == 11 OR $TypeInvoice=='119'){
				$strSearchAnticipo="";
				$categoriaAnticipo="";
				$blnFacturaAnticipo3_3 = true;
			}
			//blnFacturaAnticipo3_3 ES UNA FACTURA DE REMISION O NO LO ES
			if (!$blnFacturaAnticipo3_3) {
				$strSQLAnticipo="SELECT stockmoves.transno,
			 			stockmoves.type,
						stockmaster.categoryid,
						sum((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)) AS fxnet,
						sum((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)) AS subtotal,
						sum(stockmoves.price) AS fxprice
					FROM stockmoves
					INNER JOIN stockmaster on stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=".$TypeInvoice."
					AND stockmoves.transno=". $InvoiceNo ."
					AND stockmoves.tagref='". $tag ."'
					AND stockmaster.categoryid='". $categoriaAnticipo ."'
					AND stockmaster.units ='Actividad'
					AND (stockmoves.show_on_inv_crds=1 or stockmoves.stockid ='facturaremision')
					GROUP BY stockmoves.transno,stockmoves.type,stockmoves.tagref;";
					if($_SESSION['UserID']=="desarrollo"){
						echo "<br>sql ANTICIPO: ".$strSQLAnticipo;
					}
					
				$resultAnticipo=DB_query($strSQLAnticipo,$db);
				if (DB_num_rows($resultAnticipo)>0) {
					$blnFlagAnticipo3_3 = true;
					$strLegendAnticipo="CFDI por remanente de un anticipo";
					$myRowAnticipo=DB_fetch_array($resultAnticipo);
					$dblImporteDescuentoAnticipo=$myRowAnticipo['subtotal'];
					$dblImporteDescuentoAnticipo=abs($dblImporteDescuentoAnticipo);
				}
			}
			//**** SE COMENTA POR QUE ESTA DUPLICANDO DESCUENTO ***************
			// if($TypeInvoice==66){
			// 	$queryCFDI="SELECT SUM(monto) as m
			// 				FROM notesorders_invoice 
			// 				LEFT JOIN debtortrans on notesorders_invoice.transid_relacion = debtortrans.id
			// 				WHERE notesorders_invoice.transid = '".$myrow['id']."'";
			// 	// echo "<br>QuerySql; <pre>";
			// 	// 	print_r($queryCFDI);
			// 	// echo "</pre><br>";
			// 	$resultCFDI=DB_query($queryCFDI,$db);
			// 	$myrowCFDI = DB_fetch_array($resultCFDI);
			// 	$dblImporteDescuentoAnticipo += $myrowCFDI['m'];
			// 	$blnFlagAnticipo3_3 = true;
			// 	$blnFacturaAnticipo3_3 = true;
			// 	//echo "<br>Monto; ".$dblImporteDescuentoAnticipo;
			// }
				//<ANTICIPOS/>
			//blnFlagAnticipo3_3  ES UNA APLICACION DE ANTYICIPO
			if($blnFlagAnticipo3_3){
				$charelectronic=$charelectronic.'|'.($descuento + $dblImporteDescuentoAnticipo ) ; //Cad 01, pos 9
			}else{
				$charelectronic=$charelectronic.'|'.$descuento ; //Cad 01, pos 9
			}
		}else{
			$charelectronic=$charelectronic.'|'.$descuento ; //Cad 01, pos 9
		}
		
		//motivo descuento
		$charelectronic=$charelectronic.'|'; //Cad 01, pos 10
		// tipo de moneda
		$moneda=$myrow['currcode'];
		// CANTIDAD CON LETRAS
		$totaletras=($myrow['ovamount']+$myrow['ovgst'])+$totalretencion;
		/*$decimalplacesuno=4;
		if($totaletras<0 and ($TypeInvoice==66 or $TypeInvoice==10 or $TypeInvoice==110)){
			$decimalplacesuno=2;
		}
		//$totalx=str_replace(',','',number_format($totaletras,$decimalplacesuno,'.',''));
		//$totalx = str_replace(',','',$total);
		$separa2=explode(".",$totalx,4);
		//$separa=explode(".",$totalx);//////
		$montoletra = $separa[0];
		$separa2=explode(".",number_format($totalx2,$decimalplacesuno));
		
		if($totaletras<0 and ($TypeInvoice==66 or $TypeInvoice==10 or $TypeInvoice==110)){
			$montoctvs2="0.00";
		}else{
			$montoctvs2 = $separa2[1];
		}
		
		//$montoletra = Numbers_Words::toWords($montoletra,'es');
		//$montocentavos=$montoctvs2;
		$montoletra=Numbers_Words::toWords($montoletra,'es');
		//$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
		if ($moneda=='MXN'){
			$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";

		}
		else
		{
			$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
		}
		$charelectronic=$charelectronic.'|'.$montoletra;*/
		$totalx=str_replace(',','',$total);
		
		$separa=explode(".",$totalx);
		$montoletra = $separa[0];
		//
		//$separa2=explode(".",$totalx);//
        if(isset($_SESSION['DecimalPlacesLetra']) and $_SESSION['DecimalPlacesLetra'] <> ""){
            $montoctvs2 = substr($separa[1], 0,$_SESSION['DecimalPlacesLetra']);
        }else{
            $montoctvs2 = substr($separa[1], 0,6);
        }
		
		//$montoctvs2 = $separa[1];//
        
		$montoletra = Numbers_Words::toWords($montoletra,'es');
		//$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
		if ($moneda=='MXN'){
			$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
		}elseif ($moneda=='EUR'){
			$montoletra=ucwords($montoletra) . " Euros ". $montoctvs2 ." /100 EUR";
		}else{
			$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
		}

		$charelectronic=$charelectronic.'|'.$montoletra; //Cad 01, pos 11
		// tipo moneda
		$charelectronic=$charelectronic.'|'.$moneda; //Cad 01, pos 12
		// tipo de cambio
		$rate=number_format((1/$myrow['cambio']),$decimalplaces,'.','');
		$charelectronic=$charelectronic.'|'.$rate;
		// numero de orden para referencia
		$ordenref=$myrow['orderno'];
		$charelectronic=$charelectronic.'|'.$ordenref; //Cad 01, pos 13
		// observaciones 1: vendedores
		$vendedor="";
		$SQL=" SELECT *
		       FROM salesman
		       WHERE salesmancode='".$myrow['salesman']."'";
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowsales = DB_fetch_array($Result);
			$vendedor=$myrowsales['salesmanname'];
		}
		$observaciones1='Vendedor: '.$myrow['salesman'].' '.$vendedor;
		$charelectronic=$charelectronic.'|'.$observaciones1; //Cad 01, pos 14
		// observaciones 2
		$SQL=" SELECT l.telephone,l.comments,t.tagdescription,t.legalid
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='".$tag."'";
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$telephone=trim($myrowtags['telephone']);
			$comments=trim($myrowtags['comments']);
		}
          //      $legal = DB_fetch_array($Result);
		$observaciones2=" Atencion a clientes " .$telephone;
		$charelectronic=$charelectronic.'|'.$observaciones2; //Cad 01, pos 15
		// observaciones 3
		$comments=$comments.' Cuenta de Referencia: '.$myrow['ref1'];
		$observaciones3='Id Factura:'.$InvoiceNo. '. La tenencia de esta factura no acredita su pago si no se justifica con el comprobante respectivo. '.$comments .' usr:'.$UserRegister;
		$charelectronic=$charelectronic.'|'.$observaciones3; //Cad 01, pos 16
		// informacionExtra
		$infoextra="";
		$numpago=0;
		$cadenapagares="";
		if ($TypeInvoice==11){
			$tipodoc=0;
		}else{
			$tipodoc=70;
		}
		
		$SQL=" SELECT trandate,case when ovamount<0 then (ovamount+ovgst)*-1 else ovamount+ovgst end  as monto
		       FROM  debtortrans
		       WHERE debtortrans.type=".$tipodoc."
			AND debtortrans.order_=" . $InvoiceNo ;
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)>0) {
			while ($myrowpagos=DB_fetch_array($Result)){
				$numpago=$numpago+1;
				$fechapago=ConvertSQLDate($myrowpagos['trandate']);
				$montopago=$myrowpagos['monto'];
				$montopago=number_format($montopago,$decimalplaces,'.','');
				$cadenapagares=$cadenapagares." No:".$numpago." Fecha: ".$fechapago." Monto: ".$montopago .' '.$moneda;
			}
		}
		$placa= $_SESSION['LabelText1'].' : '.$myrow['placa'];
		$serie=$_SESSION['LabelText3'].' : '.$myrow['serie'];
		$kilometraje=$_SESSION['LabelText2'].' : '.$myrow['kilometraje'];
		$comments1=' Extra: '.$myrow['comments'];
		$infoextra=$placa.' '.$serie.' '.$kilometraje.' '.$comments1.' Pagares: '. $cadenapagares;


		$charelectronic=$charelectronic.'|'.$infoextra; //Cad 01, pos 17

		//cometarios nivel factura
		$charelectronic=$charelectronic.'|'.$myrow['invtext']; //Cad 01, pos 18

		//Agregar datos de CFDI 3.3
		$charelectronic=$charelectronic.'|'.$myrow['c_TipoDeComprobante']; // TipoDeComprobante //Cad 01, pos 20
		$charelectronic=$charelectronic.'|'.$myrow['c_UsoCFDI']; // UsoCFDI //Cad 01, pos 21
		$charelectronic=$charelectronic.'|'.$myrow['c_paymentid']; // MetodoPago //Cad 01, pos 22
		$charelectronic=$charelectronic.'|'.$myrow['claveFactura']; // Confirmacion //Cad 01, pos 23
		$charelectronic=$charelectronic.'|'.$myrow['decimalplaces']; // decimalplaces //Cad 01, pos 24

		// datos de la forma de pago
		//$charelectronic=$charelectronic.$charelectronicdetailUUID;

		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'02';
		$terminospago=$myrow['terms'];
		$SQL=" SELECT count(*) as pagares
		       FROM  debtortrans
		       WHERE debtortrans.type=70
			AND debtortrans.order_=" . $InvoiceNo ;
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==0) {
			$Tipopago="Pago en una sola exhibicion";
		}else{
			$myrowpag = DB_fetch_array($Result);
			$numpagares=intval($myrowpag['pagares']);
			if ($numpagares<=1){
				$Tipopago="Pago en una sola exhibicion";
			}else{
				$Tipopago="Parcialidades";
			}
		}

		if ($TypeInvoice==11){
			$Tipopago="Pago en una sola exhibicion";
		}
		
		$Tipopago=$Tipopago;//.' '.$terminospago;
		//$Tipopago=$terminospago;
		$charelectronic=$charelectronic.'|'.$Tipopago;
		// condiciones de pago
		$charelectronic=$charelectronic.'|'.$terminospago;
		// metodo de pago
		$metodopago=$metodoPago;
		$charelectronic=$charelectronic.'|'.$metodopago;
		// fecha vencimiento
		$fechavence=$myrow['trandate'];
		$charelectronic=$charelectronic.'|'.$fechavence;
		// observaciones 4 (no de cuenta)
		$observaciones4=$nocuenta;
		$charelectronic=$charelectronic.'|'.$observaciones4;

		//DATOS DE ANTICIPO
		$strSQLNotaCredito="";
		$resultNotaCredito="";
		//echo "facturaversion:".$_SESSION['FacturaVersion'];
		if ($_SESSION['FacturaVersion'] == "3.3") {
			//<ANTICIPOS>
			//Si no es una factura de anticipo obtenemos uuid relacionados(donde se esta aplicando)
			//
            if($_SESSION['UserID']=="desarrollo"){
                echo "<br> Si no es una factura de anticipo obtenemos uuid relacionados(donde se esta aplicando)";
                echo "<br/>blnFacturaAnticipo3_3: ".$blnFacturaAnticipo3_3;
                echo "<br/>TypeInvoice: ".$TypeInvoice;
            }
            $charRelacion = "";
			if (!$blnFacturaAnticipo3_3 and $TypeInvoice!=66) {
                if($_SESSION['UserID']=="desarrollo"){
                    echo "<br>iNGRESO 1";
                }
				//$blnFlagAnticipo3_3 = true;		
				/*DB_data_seek($resultAnticipo,0);
				$myRowAnticipo=DB_fetch_array($resultAnticipo);
				$dblImporteDescuentoAnticipo=$myRowAnticipo['subtotal'];
				$dblImporteDescuentoAnticipo=abs($dblImporteDescuentoAnticipo);*/

                $strSQLUUID = " SELECT UUID FROM log_cancelacion_sustitucion WHERE transNo='".$InvoiceNo."' AND type ='".$TypeInvoice."'";
                if($_SESSION['UserID']=="desarrollo"){
                    echo "<br> SQL sustitucion: ".$strSQLUUID;
                }
                $resultSQLUUID=DB_query($strSQLUUID,$db);
                if(DB_num_rows($resultSQLUUID)>0){
                    while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
                        $charelectronic=$charelectronic.'|'.chr(13).chr(10).'011';
                        $charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
                    }

                }else{
                    $strSQLUUID="SELECT debtortrans.uuid
                        FROM stockmoves
                        INNER JOIN (SELECT stockmoves.stkmovid
                            FROM stockmoves
                            INNER JOIN debtortrans on stockmoves.type = debtortrans.type and stockmoves.transno = debtortrans.transno and stockmoves.tagref = debtortrans.tagref
                            inner join stockmaster on stockmoves.stockid = stockmaster.stockid
                            AND stockmoves.type=". $TypeInvoice ."
                            AND stockmoves.transno=". $InvoiceNo ."
                            AND stockmoves.tagref=". $tag ."
                            AND stockmaster.categoryid='". $categoriaAnticipo ."'
                            AND stockmaster.units ='Actividad'
                            AND (stockmoves.show_on_inv_crds=1 or stockmoves.stockid ='facturaremision')) dtstkmovid on stockmoves.stkmoveno = dtstkmovid.stkmovid
                        INNER JOIN debtortrans on stockmoves.type = debtortrans.type and stockmoves.transno = debtortrans.transno and stockmoves.tagref = debtortrans.tagref ;";
                    if($_SESSION['UserID']=="desarrollo"){
                        echo "<br><br><pre>: ANTICIPOS".$strSQLUUID."<br>";
                    }
                    //********************** NUEVA CONSULTA PARA LOS ANTICIPOS **************************
                     $strSQLUUID = "SELECT DISTINCT debtortrans.folio, debtortrans.transno, debtortrans.type, transidanticipo, d.ref1 , (SELECT uuid FROM debtortrans d1 WHERE d1.id = d.ref1)  AS uuid
                                FROM salesinvoiceadvance 
                                INNER JOIN debtortrans ON debtortrans.id = salesinvoiceadvance.transidinvoice
                                INNER JOIN debtortrans d ON d.id = salesinvoiceadvance.transidanticipo
                                WHERE debtortrans.type = ". $TypeInvoice ."
                                AND debtortrans.transno = '". $InvoiceNo ."';";

                                if($_SESSION['UserID']=="desarrollo"){
                                    echo "<br><br><pre>: ANTICIPOS2: ".$strSQLUUID."<br>";
                                }
                    //********************** NUEVA CONSULTA PARA LOS ANTICIPOS **************************
                    
                    $resultSQLUUID=DB_query($strSQLUUID,$db);
                    while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
                        $charelectronic=$charelectronic.'|'.chr(13).chr(10).'011';
                        $charelectronic=$charelectronic.'|07|'.$myrowUUID['uuid'];
                        
                    }
                }

			}
            if($blnFacturaAnticipo3_3==true and $TypeInvoice!=66) {
                $strSQLUUID = " SELECT UUID FROM log_cancelacion_sustitucion WHERE transNo='".$InvoiceNo."' AND type ='".$TypeInvoice."'";
                if($_SESSION['UserID']=="desarrollo"){
                    echo "<br> SQL sustitucion FLAGADVANCE: ".$strSQLUUID;
                }
                $resultSQLUUID=DB_query($strSQLUUID,$db);
                while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
                    $charelectronic=$charelectronic.'|'.chr(13).chr(10).'011';
                    $charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
                }
            }

			if(isset($TypeInvoice) and $TypeInvoice==66){
                if($_SESSION['UserID']=="desarrollo"){
                    echo "<br>iNGRESO 2";
                }

                $strSQLUUID = " SELECT UUID FROM log_cancelacion_sustitucion WHERE transNo='".$InvoiceNo."' AND type ='".$TypeInvoice."'";
                if($_SESSION['UserID']=="desarrollo"){
                    echo "<br> SQL sustitucion: ".$strSQLUUID;
                }
                $resultSQLUUID=DB_query($strSQLUUID,$db);
                if(DB_num_rows($resultSQLUUID)>0){
					while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
    					$charelectronic=$charelectronic.'|'.chr(13).chr(10).'011';
    					$charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
    					
    				}
                }else{
                    $queryCFDI="SELECT  '07' as c_TipoRelacion,debtortrans.uuid
                            FROM notesorders_invoice 
                            LEFT JOIN debtortrans ON notesorders_invoice.transid_relacion = debtortrans.id
                            WHERE notesorders_invoice.transid = '".$myrow['id']."'";
                    $queryCFDI = "SELECT DISTINCT '07' as c_TipoRelacion, debtortrans.folio, debtortrans.transno, debtortrans.type, transidanticipo, d.ref1 , (SELECT uuid FROM debtortrans d1 WHERE d1.id = d.ref1)  AS uuid
                                FROM salesinvoiceadvance 
                                INNER JOIN debtortrans ON debtortrans.id = salesinvoiceadvance.transidinvoice
                                INNER JOIN debtortrans d ON d.id = salesinvoiceadvance.transidanticipo
                                WHERE debtortrans.type = ". $TypeInvoice ."
                                AND debtortrans.transno = '". $InvoiceNo ."';";
                    $resultCFDI=DB_query($queryCFDI,$db);
                     //echo "<pre>sql:".$queryCFDI;
                    while ($myrowCFDI = DB_fetch_array($resultCFDI)) {
                        $charelectronic=$charelectronic.'|'.chr(13).chr(10).'011';
                        $charelectronic=$charelectronic.'|'.$myrowCFDI['c_TipoRelacion'];
                        $charelectronic=$charelectronic.'|'.$myrowCFDI['uuid'];
                    }

                }
			}


			//<ANTICIPOS/>

			//<NOTACREDITO>
			//echo "<br>ver: ".$TypeInvoice;
			if(isset($TypeInvoice) and $TypeInvoice==11 ){
				$strSQLNotaCredito="SELECT  debtortrans.id,debtortrans.uuid,'03' AS c_TipoRelacion
					FROM notesorderdetails
					LEFT JOIN debtortrans ON notesorderdetails.transid_invoice = debtortrans.id
					WHERE notesorderdetails.orderno = ".$myrow['order_']."
					GROUP BY  debtortrans.id
					ORDER BY debtortrans.id ";
				
				$resultNotaCredito=DB_query($strSQLNotaCredito,$db);
				while ($myrowNCUUID=DB_fetch_array($resultNotaCredito)) {
					//validar si tiene relacion
					//if(isset($myrowNCUUID['uuid'])){
					$charelectronic=$charelectronic.'|'.chr(13).chr(10).'012';
					$charelectronic=$charelectronic.'|'.$myrowNCUUID['c_TipoRelacion'].'|'.$myrowNCUUID['uuid'];
					
					//}
					
				}

			}
			//<NOTACREDITO/>


		}
		//$dblImporteDescuentoAnticipo=$descuento + $dblImporteDescuentoAnticipo;
		$subtotalInvoice = $subtotalInvoice + $dblImporteDescuentoAnticipo + $montoNotesorders_invoice;
		$charelectronicdetailUUID='';

		// datos del cliente
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'03';
		$branch=$myrow['debtorno'];
		$charelectronic=$charelectronic.'|'.$branch;
		$charelectronic=$charelectronic.'|'.$rfccliente;
		$charelectronic=$charelectronic.'|'.$nombre;
		
		//                if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
		//                    $pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))); //"Mexico";//$myrow['name'];
		//		}else{
		//                    $pais="Mexico";
		//		}     
        $pais= $myrow["custpais"];
        if (empty($myrow["custpais"])){
            $pais= "Mexico";
        }                
                
		$charelectronic=$charelectronic.'|'.$pais;
		$calle="";
		if ($imprimepublico==0){
			$calle=$myrow['address1'];
		}
		$charelectronic=$charelectronic.'|'.$calle;
		$noext=$myrow['brnumext'];
		$charelectronic=$charelectronic.'|'.$noext;
		$noint=$myrow['brnumint'];
		$charelectronic=$charelectronic.'|'.$noint;
		$colonia="";
		if ($imprimepublico==0){
			//$colonia=$myrow['address2'];
			$colonia=$myrow['braddress6'];
		}
		$charelectronic=$charelectronic.'|'.$colonia;
		$localidad="";
		$charelectronic=$charelectronic.'|'.$localidad;
		$referenciacalle="";
		if ($imprimepublico==0){
			$referenciacalle=$myrow['telofi'];
		}
		$charelectronic=$charelectronic.'|'.$referenciacalle;
		$municipio="";
		if ($imprimepublico==0){
			$municipio=$myrow['address3'];
		}
		$charelectronic=$charelectronic.'|'.$municipio;
		$edo="";
		if ($imprimepublico==0){
			$edo=$myrow['address4'];
		}
		$charelectronic=$charelectronic.'|'.$edo;
		$cp="";
		if ($imprimepublico==0){
			$cp=$myrow['address5'];
		}
		$charelectronic=$charelectronic.'|'.$cp;
		// datos del custbranch
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'04';
		$branch=$myrow['branchcode'];
		$charelectronic=$charelectronic.'|'.$branch;
		//$rfc=$myrow['rfc'];
		//$charelectronic=$charelectronic.'|'.$rfccliente;
		$nombre=$myrow['name'];
		$charelectronic=$charelectronic.'|'.$nombre;
		//		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
		//			$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		//		}else{
		//			$pais="Mexico";
		//		}
                
        $pais= $myrow["custpais"];
        if (empty($myrow["custpais"])){
            $pais= "Mexico";
        }      
                
		//$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		$charelectronic=$charelectronic.'|'.$pais;
		$calle=$myrow['braddress1'];
		$charelectronic=$charelectronic.'|'.$calle;
		$noext=$myrow['brnumext'];
		$charelectronic=$charelectronic.'|'.$noext;
		$noint=$myrow['brnumint'];
		$charelectronic=$charelectronic.'|'.$noint;
		
		//$colonia=$myrow['braddress6'];
        $colonia=$myrow['braddress6'];
		$charelectronic=$charelectronic.'|'.$colonia;
		$localidad="";
		$charelectronic=$charelectronic.'|'.$localidad;
		//$referenciacalle="";
		$charelectronic=$charelectronic.'|'.$referenciacalle;
		$municipio=$myrow['braddress2'];
		$charelectronic=$charelectronic.'|'.$municipio;
		$edo=$myrow['braddress3'];
		$charelectronic=$charelectronic.'|'.$edo;
		$cp=$myrow['braddress4'];
		$charelectronic=$charelectronic.'|'.$cp;
		
		
		$charelectronicEmbarque='|'.chr(13).chr(10).'09';
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['specialinstructions'];
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr1'];//calle
		$charelectronicEmbarque=$charelectronicEmbarque.'|';//noext
		$charelectronicEmbarque=$charelectronicEmbarque.'|';//no int
		
		/*charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr2'];//colonia
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr3'];//municipio
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr4'];//cp
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr5'];//estado
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr6'];//pais*/
                
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr6'];//colonia
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr2'];//municipio
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr3'];//cp
        $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr4'];//estado
        //$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['custpais'];//pais
        
        if (!empty($myrow['brpostaddr7'])){
            $charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr7'];
        } else {
            $charelectronicEmbarque=$charelectronicEmbarque.'|0';
        }
	}

	// cadena para datos de los productos
	// productos vendidos
	$charelectronicdetail='';
	$charelectronicinidet='|'.chr(13).chr(10).'05';


	// datos de ivas
	$charelectronictaxs='';
	$charelectronictaxsini='|'.chr(13).chr(10).'07';
    debug_sql($descripcionOT, __LINE__,false,__FILE__,'blue');
    debug_sql($nivelfacturacion, __LINE__,false,__FILE__,'blue');
    debug_sql($OT, __LINE__,false,__FILE__,'blue');


    // validar si existen las tablas de activos
    $consulta_col= "SHOW TABLES LIKE '%leasingCharges%'";
    $resultado_col= DB_query($consulta_col, $db);
    $cols_activo= "";
    $joins_activo= "";
    $usaactivos= false;
    
    if (DB_fetch_array($resultado_col)){
		$usaactivos= true;

		if(($versionAddenda == 2 ) AND ($_SESSION['DatabaseName'] == 'erppisumma' OR $_SESSION['DatabaseName'] == 'erppisumma_CAPA' OR $_SESSION['DatabaseName'] == 'erppisumma_DES' ) ){

			$addendaLowes = "LEFT JOIN stockaddendahomedepot ON stockaddendahomedepot.stockid = stockmoves.stockid AND stockaddendahomedepot.addendaid = 2 ";
			$upc = ",stockaddendahomedepot.upc_ean ";
		}else{
			$upc = "";
			$addendaLowes = "";
		}

        $cols_activo= ", leasingCharges.barcode,
        				leasingCharges.serialno,
                        fixedassets.description AS desc_activo,
                        CASE WHEN leasingCharges.hours IS NULL OR leasingCharges.hours= 0 THEN leasingCharges.days ELSE leasingCharges.hours END cantidad_activo,
                        CASE WHEN leasingCharges.hours IS NULL OR leasingCharges.hours= 0 THEN 'Dias' ELSE 'Horas' END unidad_activo ".$upc;
		
		$joins_activo .= " LEFT JOIN leasingCharges ON stockmoves.ref2 = leasingCharges.idleasincharges ";
		$joins_activo .= $addendaLowes;
        $joins_activo .= "LEFT JOIN fixedassets ON leasingCharges.serialno = fixedassets.serialno AND leasingCharges.barcode= fixedassets.barcode ";
	}
	

    $totalRowAnticipo=0;
    //
    if($_SESSION['UserID'] =="desarrollo"){
    	//echo "<br>".$rfccliente ." ".$_SESSION['DesgloseIVA'];
    }
    //Se cambio el RFC ya que tenia el generico local como generico extranjero
	if ($rfccliente!="XEXX010101000" OR $_SESSION['DesgloseIVA']==1){
		if($TipoAgrupacionXTag==1 and $TypeInvoice <> 111){
			$sqldetails = 'SELECT distinct stockmoves.stkmoveno,
			
						-- stockmoves.stockid,
					    ProdLine.prodLineId as stockid,
						stockmoves.warranty,
						-- stockmaster.description,
						ProdLine.Description as description,
						/*stockmaster.serialised,
						stockmaster.controlled,*/
						0 as serialised,
						0 as controlled,
						sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						sum((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxnet,
						sum((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)-totaldescuento)/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)  as subtotal,
						-- sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)/case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
						stockcategory.MensajePV,
					sum(stockmoves.totaldescuento) as totaldescuento,
					stockmoves.ref4 as movorderline,
					stockmaster.flagadvance,
					stockmoves.ref2 ' . $cols_activo . '
				FROM stockmoves ' .  $joins_activo . ',stockmaster,stockcategory 
					 	INNER  JOIN ProdLine ON ProdLine.prodLineId=stockcategory.prodLineId
				WHERE stockmoves.stockid = stockmaster.stockid ' . $strFiltroBombeo .'
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref="' . $tag .'" '. $strSearchAnticipo . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid
				GROUP BY ProdLine.Description,ProdLine.prodLineId';
		}else{//
			//                    echo '<br>START<br>';
			$sqldetails = 'SELECT distinct stockmoves.stkmoveno,
						stockmoves.stockid,
						stockmoves.warranty,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mbflag,
						stockmaster.serialised,
						stockmaster.controlled,
						(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)) AS fxnet,
						((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price))-totaldescuento as subtotal,
						(stockmoves.price) AS fxprice,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
						stockcategory.MensajePV,
						stockmoves.totaldescuento,
						stockmaster.flagadvance,
						stockmoves.ref4 as movorderline,stockmoves.anticipo, stockmoves.ref2 ' . $cols_activo . '
				FROM stockmoves' .  $joins_activo . ',stockmaster,stockcategory
				WHERE stockmoves.stockid = stockmaster.stockid ' . $strFiltroBombeo .'
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref="' . $tag .'" '. $strSearchAnticipo . '
					AND (stockmoves.show_on_inv_crds=1 or stockmoves.stockid ="facturaremision")
					AND stockcategory.categoryid = stockmaster.categoryid
					';
		}

		if ($_SESSION['UserID'] == "desarrollo") {
			echo "<pre>sqldetails: 29147 ".$sqldetails;
		}
		
		debug_sql($sqldetails, __LINE__,$debug_sql,__FILE__);
		$resultdetails=DB_query($sqldetails,$db);
		$nolinea=0;
		$descrprop="";
		if($TypeInvoice == 11){
            	$sqlAnt =" SELECT abs(sum(qty* price)) totalAnt FROM stockmoves WHERE type = 11 AND transno = '".$InvoiceNo."' AND qty<0 ";

            	//echo "SQL: ".$sql;
            	$ResultAnt = DB_query($sqlAnt, $db);
            	if(DB_num_rows($ResultAnt)>0){
            		$blnFlagAnticipo3_3 = true;
            		$rowAnt = DB_fetch_array($ResultAnt);
            		$dblImporteDescuentoAnticipo = $rowAnt['totalAnt'];
            	}
            	
        }
	
		while ($myrow2=DB_fetch_array($resultdetails)){
			if(($TypeInvoice == '11' AND $myrow2['quantity'] >0) OR $TypeInvoice != '11'){// PARA NOTAS DE CREDITO CON ANT PROVISIONAL

			$stockid = "";
			$stockcantidad = 0;
			$stockcantidadCompleta = 0;
			$stocknegativo = 0; //obtener si es negativo para precio
            $movorderlineno = $myrow2['movorderline'];

            //echo "<br>".$subtotal. " * ". $dblImporteDescuentoAnticipo . " / ".$subtotalInvoice;
            //echo "<br>".$myrow2['subtotal']. " * ". $dblImporteDescuentoAnticipo . " / ".$subtotalInvoice;
            //dblImporteDescuentoAnticipo saber cuanto sobro de anticipo
            //PROVISIONAL PARA NOTAS DE CREDITO
            
            //PROVISIONAL PARA NOTAS DE CREDITO
            if($_SESSION['FacturaVersion'] == "3.3" and $blnFlagAnticipo3_3==true){
            	if($myrow2['subtotal'] >=1.1 ){
            		//Se omite opcion por el prorrateo
            		//$totalRowAnticipo=(($myrow2['subtotal'] * $dblImporteDescuentoAnticipo) / $subtotalInvoice);
            		$subPartida=$myrow2['subtotal'];
            		if( $subPartida <= $dblImporteDescuentoAnticipo){
            			$totalRowAnticipo = $subPartida -1;
            			$dblImporteDescuentoAnticipo -= $totalRowAnticipo;
            		}elseif ($subPartida > $dblImporteDescuentoAnticipo) {
            			if(($subPartida - $dblImporteDescuentoAnticipo) < 0.1){
            				$totalRowAnticipo = $subPartida -1;
            				$dblImporteDescuentoAnticipo -= $totalRowAnticipo;
            			}else{
            				$totalRowAnticipo = $dblImporteDescuentoAnticipo;
            				$dblImporteDescuentoAnticipo -= $totalRowAnticipo;
            			}
            		}
            	}else{
            		$totalRowAnticipo='0.00';
            	}
	        }
	        if($_SESSION['FacturaVersion'] == "3.3" and $blnFlagAnticipo3_3==true AND $TypeInvoice ='11'){ 
                // PARA NOTA DE CREDITO

	        }else{
	        	$totalRowAnticipo = $myrow2['anticipo'];
	        }
	        

			if(($versionAddenda == 2) AND ($_SESSION['DatabaseName'] == 'erppisumma' OR $_SESSION['DatabaseName'] == 'erppisumma_CAPA' OR $_SESSION['DatabaseName'] == 'erppisumma_DES' ) ){
				$stockid = $myrow2['upc_ean']; 

				if ($usaactivos && !empty($myrow2['ref2'])){
					$stockcantidad=number_format($myrow2['cantidad_activo'],$decimalplaces,'.','');
				}else{
					$stockcantidad=number_format($myrow2['quantity'],$decimalplaces,'.','');
					$stockcantidadCompleta = $myrow2['quantity'];
				}
			}
            else if ($usaactivos && !empty($myrow2['ref2'])){
            	$stockid = $myrow2['barcode']; 
            	//$stockcantidad=number_format($myrow2['cantidad_activo'],$decimalplaces,'.','');
            	$stockcantidad=number_format($myrow2['quantity'],$decimalplaces,'.','');
            	$stockcantidadCompleta = $myrow2['quantity'];
            }else{
            	$stockid = $myrow2['stockid']; 
            	$stockcantidad=number_format($myrow2['quantity'],$decimalplaces,'.','');
            	$stockcantidadCompleta = $myrow2['quantity'];
            }
            if ($stockcantidad < 0) {
            	//Cantidad en negativo
            	$stocknegativo = 1;
            }
			if($_SESSION['DescrLargaFact'] == $myrow2['mbflag']){
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'].' '.$myrow2['longdescription'];
				$stockdescrip=$myrow2['description'].' '.$myrow2['longdescription'].' '.$strLegendAnticipo.' ';
			}else{
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
				$stockdescrip=$myrow2['description'].' '.$strLegendAnticipo.' ';
			}
			if ($usaactivos && !empty($myrow2['ref2'])){
				$stockdescrip=$myrow2['desc_activo'].' '.$strLegendAnticipo.' ';
			}
			// if (!empty($myrow2['upc_ean'])){
			// 	$UPCAddenda =$myrow2['upc_ean'];
			// }
			


			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid ;

			
			//$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			
			/*if ($_SESSION['UserID'] == 'desarrollo' or $_SESSION['UserID'] == 'iortiz') {
				$stockcantidad = abs($stockcantidad);
			}*/

			$charelectronicdetail=$charelectronicdetail.'|'.abs($stockcantidad); //Cantidad como este en DB + o -
			
			if($TypeInvoice == 111){
				
				$SQL="SELECT chartmaster.accountcode,
							chartmaster.accountname
					FROM gltrans
						INNER JOIN chartmaster 	ON gltrans.account = chartmaster.accountcode
					WHERE gltrans.type =  '".$TypeInvoice."'
						AND gltrans.typeno = '".$InvoiceNo."'
						AND gltrans.stockid = '".$stockid."'
						AND gltrans.amount > 0";
				debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
				$Result= DB_query($SQL,$db);
				if (DB_num_rows($Result)==1) {
					$myrowcuentas = DB_fetch_array($Result);
					$cuenta = " Cuenta ".$myrowcuentas['accountcode']." ".$myrowcuentas['accountname'];
				}
				$stockdescrip = $stockdescrip.$cuenta;
			}
			
			// valores de los vendedorfees por linea//
			$descrprop=" (";
			$sqlpropertyes="SELECT DISTINCT p.InvoiceValue AS val,
                                            p.complemento,
                                            sp.label
                                        FROM salesstockproperties p,
                                        stockcatproperties sp,
                                        stockmaster sm
                                        WHERE p.stkcatpropid=sp.stkcatpropid
                                          AND sp.categoryid=sm.categoryid
                                          AND sp.reqatprint=1
                                          AND p.orderno=" . $orderno . "
                                          AND p.typedocument IN(10,110, '" . $TypeInvoice . "')
                                          AND sm.stockid='" . $stockid . "'
                                          AND p.orderlineno = '".$movorderlineno."'";
                        
            // echo "sqlpropertyes: ".$sqlpropertyes;
			
			$resultpropertyes=DB_query($sqlpropertyes,$db);
                        
            $xt=0; 
                        
			while ($myrowprop=DB_fetch_array($resultpropertyes)){
				// 				$traba=explode(" ",$myrowprop['val']);
				// 				$trab=$traba[0];
				// 				$trab=$myrowprop['val'];****
				if ($xt==0) {
                    if (strpos("@".$_SESSION["DatabaseName"], "servillantas")) {
                        $traba=explode(" ",$myrowprop['val']);
                        $noTraba=$traba[0];
                        $descrprop=$descrprop.' '.$myrowprop['label'].':'.$noTraba;
                    }else{
                        $descrprop=$descrprop.' '.$myrowprop['label'].':'.$myrowprop['val'];
                    }
				} else {
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$myrowprop['val'];
				}

				switch ($myrowprop['val']) {
					case 'Interno':
						$sqlbomb = "SELECT class FROM stockclass WHERE idclass = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['class'];
						
						break;
					case 'Externo':
						$sqlbomb = "  SELECT suppname FROM suppliers WHERE supplierid = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['suppname'];
						break;
					default:
						break;
				}

				$xt=$xt+1;
			}
			$descrprop=$descrprop." )";
			if ($xt==0){
				$descrprop="";
			}
			if($myrow2['MensajePV'] <> ""){
				$descrprop = $descrprop.$myrow2['MensajePV'];
			}


			//Se ha eliminado el trabajador de la descripcion del XML
			//printecho("entroant", "echo");

			$numberserie="";
			$xserie= 0;

            /*if (strpos("@".$_SESSION ['DatabaseName'], "gruposervillantas")) {
                $sqlserials="SELECT DISTINCT stockserialitems.serialno AS serie
							FROM workorders 
							INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
							WHERE orderno= '".$orderno."'
							AND stockserialitems.customs= '".$stockid."' 
							AND qualitytext NOT LIKE '%rechaz%' AND stockserialitems.serialno NOT LIKE 'R-%'
							AND stockserialitems.stockid= 'cascli'";

				$resultado= DB_query($sqlserials,$db);
				
				while ($myrownseries=DB_fetch_array($resultado)) {
					if ($stockid!='cascli'){
						$consulta= "SELECT DISTINCT stockserialitems.serialno AS serie
									FROM workorders 
									INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
									WHERE orderno= '".$orderno."'
									AND stockserialitems.customs= '".$stockid."'
									AND qualitytext LIKE '%rechaz%' 
									AND stockserialitems.serialno LIKE '%".$myrownseries['serie']."%';";
						$resultado2= DB_query($consulta, $db);

						if ($registro=DB_fetch_array($resultado2)){
							continue;
						}
					}						
					
					if ($xserie==0){
						$numberserie=' Series: '.$myrownseries['serie'];
					}else{
						$numberserie=$numberserie.', '.$myrownseries['serie'];
					}

					$xserie++;
				}
            }  */          

			$descrnarrative="";
                        //
            if($_SESSION ['FormatoDeComentarioEnPV']==1){
               	$descrnarrative=str_replace('\r','',str_replace('\n', chr(60).'br'.chr(62),$myrow2['narrative']));
				$descrnarrative=str_replace('|', '', $descrnarrative); 
				$descrnarrative=str_replace(chr(10), chr(60).'br'.chr(62), $descrnarrative);
            }else{
                $descrnarrative=str_replace('\r','',str_replace('\n','',$myrow2['narrative']));
				$descrnarrative=str_replace('|', '', $descrnarrative);
				$descrnarrative=str_replace(chr(10), chr(60).'br'.chr(62), $descrnarrative);
            }		
			/*
			if ($TypeInvoice==11){
				$tablades=0;
			}else{
				$tipodoc=70;
			}
			*/
			$sqlnarrative="SELECT narrative
							FROM salesorderdetails 
							WHERE salesorderdetails.orderno=" . $orderno . "
				   				AND salesorderdetails.stkcode='" . $stockid . "'
				   				AND salesorderdetails.orderlineno = '" . $movorderlineno . "'";

			$resultnarrative=DB_query($sqlnarrative,$db);

			while ($myrownarrative=DB_fetch_array($resultnarrative)){
				if ($myrownarrative['narrative'] != $myrow2['narrative']) {
					$descrnarrative=str_replace('\r','',str_replace('\n','',$myrownarrative['narrative']));
					$descrnarrative=str_replace('|', '', $descrnarrative);
				}
			}

			if (strlen($descrnarrative)==0){
				$descrnarrative = "";
			}

			//$descrnarrative="";
			// consulta de aduana
			$stkmoveno=$myrow2['stkmoveno'];
			$almacen=$myrow2['almacen'];			
			$xserie=0;
			/*$sqlserials="SELECT stockserialitems.serialno as serie
				    FROM stockserialmoves , stockmoves , stockserialitems
				    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
					AND stockserialmoves.stockid=stockserialitems.stockid
					AND stockserialmoves.serialno=stockserialitems.serialno
					AND stockserialitems.loccode='".$almacen."'
					AND stockmoves.stkmoveno=".$stkmoveno."
					AND stockmoves.stockid='".$stockid."'";
			//echo '<pre><br>'.$sqlserials;
			$Result= DB_query($sqlserials,$db);
			if (DB_num_rows($Result)==0) {
				$numberserie="";
			}else{
				while ($myrownseries=DB_fetch_array($Result)){
					if ($xserie==0){
						$numberserie=' Series: '.$myrownseries['serie'];
					}else{
						$numberserie=$numberserie.', '.$myrownseries['serie'];
					}
				}
			}
			if (strlen($numberserie)==0){
				$numberserie="";
			}*/
			// para tyqsa;
			if($TypeInvoice == 119 AND ($_SESSION['DatabaseName']=='erptycqsa' OR $_SESSION['DatabaseName']=='erptycqsa_CAPA') ){
				$sqlrestante = "SELECT quantity ordenada, concat(' Cant. Restante: ',quantity-abs(sum(qty) ),'  M3' )remisionada 
						FROM debtortrans 
						INNER JOIN stockmoves ON stockmoves.type = debtortrans.type AND debtortrans.transno = stockmoves.transno
						INNER JOIN salesorderdetails ON salesorderdetails.orderno = debtortrans.order_ AND stockmoves.stockid= salesorderdetails.stkcode
						WHERE debtortrans.order_='".$orderno."' AND stockmoves.type =119 AND stockmoves.transno <='".$InvoiceNo."'  AND stockmoves.stockid='".$stockid."' AND debtortrans.tagref IN (5,6,16);";
				//echo "<br> calculo restante= ".$sqlrestante;
				$resultrestante=DB_query($sqlrestante,$db);
				while ($myrowrestante=DB_fetch_array($resultrestante)){
					$descrestante = $myrowrestante['remisionada'];
				}
			}
			
			$descrnarrative=str_replace('\r','',str_replace('\n','',$myrow2['narrative']));
			$descrnarrative=str_replace('|', '', $descrnarrative);
			
			if (strlen($descrnarrative)==0){
				$descrnarrative="";
			}

			if($myrow2['showdescription']==1){
				$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie.$descrestante;
			}else{
				if (strlen($descrnarrative)==0){
					$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie.$descrestante;
				}else{
					$stockdescripuno=$descrnarrative.$numberserie.$descrestante;
				}
			}//while

			/******INICIO AGREGA LA DESCRIPCION DE LOS ACTIVOS FIJO FACTURADOS
			if($_SESSION ['DatabaseName'] == "erpgosea" or $_SESSION ['DatabaseName'] == "erpgosea_CAPA" or $_SESSION ['DatabaseName'] == "erpgosea_DES"){
				$descfixedassets = "";
				$sqldescfassets = "SELECT CONCAT('Cod.:',l.barcode, ' Serie: ', l.serialno, ' ', f.longdescription) as descfixedassets
					FROM leasingCharges l
						LEFT JOIN fixedassets f ON l.serialno = f.serialno and l.barcode = f.barcode
					WHERE l.salesordernumber = " . $orderno;
				echo "<br>DESC:" . $sqldescfassets;
				$resultdescfassets = DB_query($sqldescfassets,$db);
				while ($myrowfassets = DB_fetch_array($resultdescfassets)){
					if ($descfixedassets == ""){
						$descfixedassets = $myrowfassets['descfixedassets'];
					}else{
						$descfixedassets = $descfixedassets . "<br>" . $myrowfassets['descfixedassets'];
					}
					
				}

				if ($descfixedassets != ""){
					$stockdescripuno = $descfixedassets;
				}
			}*/

			/******INICIO AGREGA LA DESCRIPCION DE LOS ACTIVOS FIJO FACTURADOS*/


			/* Gosea pidio que se agregara una leyenda en la descripcion al inicio 'Renta de' o 'Servicio de' se agrego tambien el numero de serie */

			if($_SESSION ['DatabaseName'] == "erpgosea" or $_SESSION ['DatabaseName'] == "erpgosea_CAPA" or $_SESSION ['DatabaseName'] == "erpgosea_DES"){
				if (isset($tag) and $tag!=''){
					$SQL="SELECT tagref,legalid,legend FROM tagslegend where tagref = " . $tag;
					$resulttaglegend=DB_query($SQL,$db);
					$rowtaglegend = DB_fetch_array($resulttaglegend);
					if($rowtaglegend['legend'] !=''){
						$charelectronicdetail=$charelectronicdetail.'|'.$rowtaglegend['legend'] ." ".$stockdescripuno . ", No. Serie ". $myrow2['serialno'];
					}else{
						$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno. ", No. Serie ". $myrow2['serialno'];
					}
				}else{
					$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno. ", No. Serie ". $myrow2['serialno'];
				}
			}else{
				$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			}
			
			if ($usaactivos && !empty($myrow2['ref2'])){
				//$stockprecio=number_format(($myrow2['fxprice']/$myrow2['cantidad_activo']),$decimalplaces,'.','');
				$stockprecio=number_format($myrow2['fxprice'],$decimalplaces,'.','');
			}else{
				$stockprecio=number_format($myrow2['fxprice'],$decimalplaces,'.','');
			}

			if($_SESSION['FacturaVersion'] == "3.3"){
				if ($usaactivos && !empty($myrow2['ref2'])){
					//	$stockneto=number_format(($myrow2['fxnet']/$myrow2['cantidad_activo']),$decimalplaces,'.','');
					$stockneto=number_format($myrow2['subtotal'],$decimalplaces,'.','');
				}else{
					if($blnFlagAnticipo3_3){
						//echo "<br>((".$myrow2['subtotal']." * ".$dblImporteDescuentoAnticipo.")/".$subtotalInvoice.")";
						$stockneto=number_format($myrow2['subtotal'] - $totalRowAnticipo,$decimalplaces,'.','');
					}else{
						$stockneto=number_format($myrow2['subtotal'],$decimalplaces,'.','');
					}
					
				}
			}else{
				if ($usaactivos && !empty($myrow2['ref2'])){
					$stockneto=number_format(($myrow2['fxnet']/$myrow2['cantidad_activo']),$decimalplaces,'.','');
				}else{
					$stockneto=number_format($myrow2['fxnet'],$decimalplaces,'.','');
				}				
			}


			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockcantidad: ".$stockcantidad." - stockcantidadCompleta ".$stockcantidadCompleta." - stockprecio: ".$stockprecio." - stockneto: ".$stockneto."<br>";
			}
			
			if ($myrow2['flagadvance'] == 1) {	
				//Obtener total de la partida, si existe diferencia obtener precio unitario
				$totalPar = number_format($stockcantidad * $stockprecio,$decimalplaces,'.','');
				//echo "<br>totalPar1: ".$totalPar."<br>";
				if (abs($totalPar) != abs($stockneto)) {
					$stockprecio = number_format($stockneto/$stockcantidad,$decimalplaces,'.','');
				}

				if ($stockcantidad < 0 and $stockprecio > 0) {
					$stockprecio = $stockprecio * (-1);
				}
			}


			if ($stocknegativo == 1 and $stockprecio > 0) {
				//Si cantidad es negativo, poner precio en negativo para operacion
				$stockprecio = $stockprecio * (-1);
			}

			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockprecio change: ".$stockprecio."<br>";
			}
			
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;

			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			if ($usaactivos && !empty($myrow2['ref2'])){
				$stockunits=$myrow2['unidad_activo'];
			}else{
				$stockunits=$myrow2['units'];
			}
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			
			$subtotal=number_format($myrow2['totaldescuento'] + $totalRowAnticipo ,$decimalplaces,'.','');

			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal ;
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;


			/*$sqladuana="SELECT stockserialitems.customs as aduana,customs_number as noaduana,
						pedimento, DATE_FORMAT(customs_date,'%Y-%m-%d') as fechaaduana
				    FROM stockserialmoves , stockmoves , stockserialitems
				    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
					AND stockserialmoves.stockid=stockserialitems.stockid
					AND stockserialmoves.serialno=stockserialitems.serialno
					AND stockserialitems.loccode='".$almacen."'
					AND stockmoves.stkmoveno=".$stkmoveno."
					AND stockmoves.stockid='".$stockid."'";*/

			//Validación de No. pedimento
			if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA'){
				$sqladuana="SELECT nopedimento
						FROM pediment_aduanas 
						WHERE orderno='" . $orderno . "'
			   				AND stockid='" . $stockid . "'
			   				AND orderlineno = '" . $movorderlineno . "'";
	            if ($_SESSION['UserID'] == "desarrollo"){
					//echo '<br/> sql aduanas: '.$sqladuana;
				}          
				debug_sql($sqladuana, __LINE__,false,__FILE__);
				$Result= DB_query($sqladuana,$db);
				if (DB_num_rows($Result)==0) {
					$nameaduana="";
					//$numberaduana="";
					$fechaaduana="";
					$nopedimento="";
				}else{
					$myrowaduana = DB_fetch_array($Result);
					$nopedimento=$myrowaduana['nopedimento'];
					$nameaduana="";
					$fechaaduana="";
					
					//$numberaduana=$myrowaduana['noaduana'];
					//$separaaduana=explode("|",$aduana);
					//$nameaduana = $separaaduana[0];
					//$numberaduana= $separaaduana[1];
					//$fechaaduana= $separaaduana[2];
					/*if (strlen($nameaduana)==0 or strlen($numberaduana)==0 /*or !Is_Date($fechaaduana)*){
						$nameaduana="";
						$numberaduana="";
						$fechaaduana="";
					}

					$nameaduana = $myrowaduana['aduana'];
					$numberaduana = $myrowaduana['noaduana'];
					$fechaaduana = $myrowaduana['fechaaduana'];
					$pedimento = $myrowaduana['pedimento'];*/
				}
			}else{
				$nameaduana="";
				$fechaaduana="";
				$nopedimento="";
			}
                       
			if($myrow['type'] == 66) { 
                // Si es factura remision
				$ordernotmp = $myrow['orderno'];
				$SQL = "
					SELECT GROUP_CONCAT(debtortrans.order_) AS order_
					FROM invoicetoremision
					INNER JOIN debtortrans
					ON debtortrans.id = invoicetoremision.idremision
					WHERE invoicetoremision.idinvoice = '".$myrow['iddocto']."'
				";
                debug_sql($SQL, __LINE__,$debug_sql,__FILE__);

				$rstmp      = DB_query($SQL, $db);
				$rowtmp     = DB_fetch_array($rstmp);
				$ordernotmp = $rowtmp['order_'];

				if(empty($ordernotmp) == FALSE) {
					//Validación de No. pedimento
					if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA'){
						$sqladuana="SELECT nopedimento
								FROM pediment_aduanas 
								WHERE orderno='" . $myrow['orderno'] . "'
					   				AND stockid='" . $stockid . "'
					   				AND orderlineno = '" . $movorderlineno . "'";
			            if ($_SESSION['UserID'] == "desarrollo"){
							echo '<br/> sql aduanas 66-1: '.$sqladuana;
						}
						debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
						$rstmp = DB_query($SQL, $db);

						if($rowtmp = DB_fetch_array($rstmp)) {
							$fechaaduana = '';
							$nopedimento = $rowtmp['nopedimento'];
							$nameaduana = '';
						}
					}else{
						$SQL = "
							SELECT
							DATE_FORMAT(purchorderdetails.datecustoms,'%Y-%m-%d') AS fechaaduana,
							pedimento,
							customs AS aduana
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorderdetails.orderno = purchorders.orderno
							WHERE purchorders.requisitionno IN ($ordernotmp)
							AND purchorderdetails.itemcode = '$stockid'
						";

						debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
						$rstmp = DB_query($SQL, $db);

						if($rowtmp = DB_fetch_array($rstmp)) {
							$fechaaduana = $rowtmp['fechaaduana'];
							$nopedimento = $rowtmp['pedimento'];
							$nameaduana = $rowtmp['aduana'];
						}
					}
				}
			}

			$charelectronicdetail=$charelectronicdetail.'|'.$nameaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$nopedimento;
			//$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$almacen;
			
			if($TipoAgrupacionXTag==1 and $TypeInvoice <> 111){
				
				$sqlstockmovestaxes="SELECT stockmovestaxes.stkmoveno,
											taxauthid,taxrate,
											taxcalculationorder,
											taxontax,
											taxauthorities.description,
  											sum(stockmovestaxes.taxrate*(((stockmoves.price*(stockmoves.qty*-1))-(CASE WHEN stockmoves.type=11 then stockmoves.totaldescuento*-1 else stockmoves.totaldescuento end)))) as iva,
  											taxauthorities.c_Impuesto
	 								FROM  stockmovestaxes inner join stockmoves on stockmoves.stkmoveno=stockmovestaxes.stkmoveno
										LEFT join stockmaster on stockmaster.stockid=stockmoves.stockid
										LEFT  JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid 
										LEFT  JOIN taxauthorities ON  taxauthorities.taxid=stockmovestaxes.taxauthid
					     WHERE type=".$TypeInvoice. " AND transno=".$InvoiceNo." And stockcategory.prodLineId='".$myrow2['stockid']."'";
			}else{
			
				$sqlstockmovestaxes="SELECT stkmoveno,taxauthid,taxrate,
									taxcalculationorder,
									taxontax,
									taxauthorities.description,
									taxauthorities.c_Impuesto
									FROM  stockmovestaxes
									LEFT JOIN taxauthorities ON taxauthorities.taxid=stockmovestaxes.taxauthid
									WHERE stkmoveno=".$stkmoveno;
			}

			if($_SESSION['UserID'] =="desarrollo"){
				echo "<br>sqlstockmovestaxes:<pre>". $sqlstockmovestaxes ."</pre>";
			}

            
            debug_sql($sqlstockmovestaxes, __LINE__,$debug_sql,__FILE__);
			$resultstockmovestaxes=DB_query($sqlstockmovestaxes,$db);
			if ($_SESSION['UserID'] == "admin") {
				//echo "<br>sqlstockmovestaxes 1: ".$sqlstockmovestaxes;
			}
			//Agregar datos de CFDI 3.3
			if(DB_num_rows($resultstockmovestaxes) > 0){
				while ($myrow3=DB_fetch_array($resultstockmovestaxes)){
	                if(empty($myrow3['description']) == false){
	                    $impuesto=$myrow3['description'];
	                }else{
	                    $impuesto="IVA";
	                }
					
					$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
					$taxrate=number_format($myrow3['taxrate']*100,$decimalplaces,'.','');
					$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
					if($TipoAgrupacionXTag==1){
						//$taxratetotal=number_format(abs($myrow3['iva']),$decimalplaces,'.','');
						$taxratetotal = abs($myrow3['iva']);
						$taxratetotal = number_format($taxratetotal,$decimalplaces,'.','');
					}else{
						//$taxratetotal=number_format($myrow3['taxrate']*($subtot),$decimalplaces,'.','');
						if($_SESSION['FacturaVersion'] == "3.3"){
							if($blnFlagAnticipo3_3){
								$taxratetotal = $myrow3['taxrate'] * ($myrow2['subtotal'] - $totalRowAnticipo);
								//$taxratetotal = $myrow3['taxrate'] * ($myrow2['subtotal'] - (($myrow2['subtotal'] * $dblImporteDescuentoAnticipo) / $subtotalInvoice));
							}else{
								$taxratetotal = $myrow3['taxrate'] * ($myrow2['subtotal']);
							}
						}else{
							$taxratetotal = $myrow3['taxrate'] * ($myrow2['subtotal']);
						}
						
						$taxratetotal = number_format($taxratetotal,$decimalplaces,'.','');
					}

					$taxratetotal=number_format($taxratetotal,$decimalplaces,'.','');
					
					$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;

					$charelectronictaxs=$charelectronictaxs.'|'.$myrow3['c_Impuesto']; //Codigo de impuesto

					//Obtener factor
					$c_TipoFactor = "";
					$sqlFactor = "SELECT sat_tasas.c_TipoFactor
								FROM stockmaster
								LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
								LEFT JOIN sat_tasas ON sat_tasas.id = taxcategories.id_Tasa
								WHERE stockmaster.stockid = '".$myrow2['stockid']."'";
					if($_SESSION['UserID'] == 'desarrollo'){
						//echo "<br><pre>$sqlFactor</pre>";
					}			
					$resultFactor=DB_query($sqlFactor,$db);
					if(DB_num_rows($resultFactor) > 0){
						$rowFactor = DB_fetch_array($resultFactor);
						$c_TipoFactor = $rowFactor['c_TipoFactor']; //Factor de impuesto
					}
					$charelectronictaxs=$charelectronictaxs.'|'.$c_TipoFactor; //Factor de impuesto
				}
			}else {
				$sqlImp = "SELECT 
                        taxauthorities.c_Impuesto,
                        taxauthrates.taxrate,
                        sat_tasas.c_TipoFactor, taxauthorities.description
                        FROM stockmaster
                        LEFT JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                        LEFT JOIN taxauthorities ON taxauthorities.taxid = taxauthrates.taxauthority
                        LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
                        LEFT JOIN sat_tasas ON sat_tasas.id = taxcategories.id_Tasa
                        WHERE stockmaster.stockid = '".$myrow2['stockid']."'";
                $ErrMsg = _('El Sql que fallo fue');
                $DbgMsg = _('No se pudo obtener el impuesto del articulo '.trim($datos [2]));
                //echo "<br>".$sqlAnt."<br>";
                $resultImp = DB_query($sqlImp, $db, $ErrMsg, $DbgMsg, true);
                //$importe = $importe - trim(number_format(($descuentoRow), $decimalplaces, '.', ''));

                if (DB_num_rows($resultImp) == 1) {
                    $rowImp = DB_fetch_array($resultImp);
                    $Impuesto = $rowImp['c_Impuesto'];
                    $TipoFactor = $rowImp['c_TipoFactor'];
                    $TasaOCuota = $rowImp['taxrate'];
                    $impuesto=$rowImp['description'];
                    if($TasaOCuota == 0) {
                        $TasaOCuota = '0.000000';
                    }
                }
                $taxratetotal = $TasaOCuota * $myrow2['subtotal'];
				$taxratetotal = number_format($taxratetotal, $decimalplaces,'.','');
                //$taxrate=number_format($myrow3['taxrate']*100,$decimalplaces,'.','');
				$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
				$charelectronictaxs=$charelectronictaxs.'|'.$TasaOCuota;
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
				$charelectronictaxs=$charelectronictaxs.'|'.$Impuesto;
				$charelectronictaxs=$charelectronictaxs.'|'.$TipoFactor;
			}


                ///***************************************///
                $TotGeneralCantEmision = 0;
                if ($TypeInvoice == 119) {
                    //*****se agrega cantidad emision solo en remisiones  *///
                    $wo = 'SELECT wo FROM workorders WHERE orderno="' . ($myrow['orderno']-1) . '"';
                    if ($_SESSION['UserID'] == "desarrollo" and $debug) {
                    	//echo '<pre>Busca OT'.$wo;
                    }
                    debug_sql($wo, __LINE__,$debug_sql,__FILE__);
                    $rwo = DB_query($wo, $db);
                    if(DB_num_rows($rwo) > 0){
                        $fwo = DB_fetch_array($rwo);
                        $woo=$fwo['wo'];
                        $SQL=" SELECT legalid
                               FROM tags 
                               WHERE tagref='".$tag."'";
                        debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
                        $legal = DB_query($SQL, $db);
                        list($legalid) = DB_fetch_array($legal);

                        $sqlRequRes = "SELECT t.wostdcost, 
                                                t.worequirements_id, t.stockid, t.description,t.units, t.decimalplaces,
                                                (t.requiredqty) as requiredqty,
                                                (t.expectedcost) as expectedcost,
                                                (t.stdcost) AS costperqty,
                                                t.mbflag,
                                                t.categoryid,
                                                t.categorydescription,
                                                t.taxrate
                                FROM (	SELECT
                                            worequirements.worequirements_id,
                                            worequirements.stdcost AS wostdcost,
                                            worequirements.stockid,
                                            stockmaster.longdescription as description ,
                                            stockmaster.decimalplaces,
                                            stockmaster.mbflag,
                                            stockmaster.units,
                                            (stockcostsxlegal.avgcost) as stdcost,
                                            stockmaster.categoryid,
                                            stockcategory.categorydescription,
                                            SUM(if(worequirements.ispercent=1,0,worequirements.qtypu)) AS requiredqty,
                                            SUM(worequirements.stdcost*worequirements.qtypu) AS expectedcost,
                                            AVG(worequirements.qtypu) as qtypu,
                                            stockmaster.taxcatid,
                                            taxauthrates.taxrate
                                        FROM worequirements
                                            INNER JOIN stockmaster ON worequirements.stockid=stockmaster.stockid
                                            INNER JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
                                            INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
                                            INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = " .$legalid. "
                                            INNER JOIN woitems ON woitems.stockid=worequirements.masterparentid
                                        WHERE worequirements.wo='" . $woo . "' and woitems.wo=worequirements.wo
                                            AND stockmaster.mbflag <> 'M'
                                            /*AND stockmaster.stockid='DOR1-13'*/
                                            GROUP BY worequirements.stockid) AS t
                                        GROUP BY t.stockid
                                        ORDER BY t.categorydescription";

                        if ($_SESSION['UserID'] == "desarrollo" and $debug) {
                        	//echo '<pre>GET ReqRes:'.$sqlRequRes;
                        }
                    
                        debug_sql($sqlRequRes, __LINE__,$debug_sql,__FILE__);
                        $RequirementsResult = DB_query($sqlRequRes, $db);
                        $TotGeneralCantEmision = 0;
                        while ($RequirementsRow = DB_fetch_array($RequirementsResult)) {
                            $SQL2 = "SELECT trandate,
                                (qty + Case When movs.cantidad Is Null Then 0 Else movs.cantidad End) as qty,
                                standardcost
                                FROM stockmoves
                                Left Join (Select ref4, stockid, reference, sum(qty) as cantidad
                                        From stockmoves
                                        Where type= 815 Group by ref4, stockid, reference) as movs On stockmoves.stockid= movs.stockid 
                                                and stockmoves.reference= movs.reference 
                                                and stockmoves.stkmoveno= movs.ref4
                                WHERE stockmoves.type=28
                                AND stockmoves.reference = '" . $woo['wo'] . "'
                                AND stockmoves.stockid = '" . $RequirementsRow['stockid'] . "'";
							debug_sql($SQL2, __LINE__,$debug_sql,__FILE__);
                            $IssuesResult = DB_query($SQL2, $db, _('Could not retrieve the issues of the item because:'));
                            if ($_SESSION['UserID'] == "desarrollo" and $debug) {
								//echo '<pre>ok'.$SQL2;
								//die('END');
                            }
                            if (DB_num_rows($IssuesResult) > 0) {
                                while ($IssuesRow = DB_fetch_array($IssuesResult)) {
                                    $IssueQty -= $IssuesRow['qty']; // because qty for the stock movement will be negative
                                    $IssueCost -= ($IssuesRow['qty'] * $IssuesRow['standardcost']);
                                }
                            }
                            if($IssueQty>0){
                            	$TotGeneralCantEmision = $IssueQty;
                            }else{
                            	$TotGeneralCantEmision = 0;
                            }
                        }
						//$charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                	}else{
						//$charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                	}
                    
                    $SQLemision="SELECT sum(plcdata.value) AS total,folio
                        FROM worequirements
                        INNER JOIN workorders ON workorders.wo = worequirements.wo
                        INNER JOIN plcdata ON plcdata.stockid = worequirements.stockid AND  plcdata.wo = worequirements.wo
                        INNER JOIN stockmaster ON stockmaster.stockid = worequirements.stockid
                        INNER JOIN stockmaster AS encabezado ON encabezado.stockid = worequirements.parentstockid
                        INNER JOIN debtortrans ON workorders.orderno = debtortrans.order_ AND debtortrans.type = 119 AND debtortrans.transno='".$InvoiceNo."' AND  plcdata.transno = debtortrans.transno
                        WHERE folio IN (SELECT folio FROM debtortrans WHERE order_='".$myrow['orderno']."') GROUP BY folio;";
                    //echo '<br>EmisionAnt'.$TotGeneralCantEmision;
                    
                    debug_sql($SQLemision, __LINE__,$debug_sql,__FILE__);
                    $resultEmision=  DB_query($SQLemision, $db);
                    //echo '<pre>SQLEmision: '.$SQLemision;
                    $myrowEmision=  DB_fetch_array($resultEmision);
                    $TotGeneralCantEmision = 0;
                    $TotGeneralCantEmision=$myrowEmision['total'];
                    //echo '<br>Emision: '.$TotGeneralCantEmision;
                    //$charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                    //echo '<br>Cadena: '.$charelectronicdetail; 
					//echo '<br>END</br>';
                }
                $charelectronicdetail=$charelectronicdetail.'|'.$TotGeneralCantEmision;
                $charelectronicdetail=$charelectronicdetail.'|'.$movorderlineno;
                $charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
                $charelectronicdetail=$charelectronicdetail.'|'.$nopedimento;
                $charelectronicdetail=$charelectronicdetail.''."|".$descrestante;
				$nolinea=$nolinea+1;
	            /**FIN DE CALCULO DE EMISIONES***/

			}// FIN NOTAS CREDITO ANT PROVISIONAL 

        
        }

        //************************************************************************************************
        // Al momento de aplicar el descuento tenemos un sobrante regresarselo a la(s) factura de anticipo
        //************************************************************************************************
        if($dblImporteDescuentoAnticipo >0){
        	$sqlDevolucionAnticipo="SELECT stockmoves.stkmoveno as stkmoveno1, stkmove2.stkmoveno,stkmove2.price,stkmove2.qtyinvoiced
									FROM stockmoves
									INNER JOIN stockmaster on stockmoves.stockid = stockmaster.stockid
									AND stockmoves.type='". $TypeInvoice ."'
									AND stockmoves.transno='". $InvoiceNo ."'
									AND stockmoves.tagref='". $tag ."'
									AND stockmaster.categoryid='". $categoriaAnticipo ."'
									AND stockmaster.units ='Actividad'
									AND (stockmoves.show_on_inv_crds=1 or stockmoves.stockid ='facturaremision')
									inner join stockmoves stkmove2 on stkmove2.stkmoveno =stockmoves.stkmovid
									group by stkmove2.stkmoveno
									order by stkmove2.stkmoveno desc;";
									//echo "<br>sql anrin:<pre>".$sqlDevolucionAnticipo;
			$rsDevolucionAnticipo=DB_query($sqlDevolucionAnticipo,$db);

			$dblPendienteAplicar=$dblImporteDescuentoAnticipo;
			while ($myrowDevolucionAnticipo = DB_fetch_array($rsDevolucionAnticipo)) {

				$Aplicado=($myrowDevolucionAnticipo['price'] * $myrowDevolucionAnticipo['qtyinvoiced']);
				//echo "<br>plicado: ".$Aplicado;
				//echo "<br>dblPendienteAplicar: ".$dblPendienteAplicar;
				if($dblPendienteAplicar > abs($Aplicado)){
					$dblPendienteAplicar = $Aplicado;
				}

				if($dblImporteDescuentoAnticipo >0){
					$sqlAplicarRegresoFactura="UPDATE stockmoves SET qtyinvoiced = (qtyinvoiced + ".$dblPendienteAplicar / $myrowDevolucionAnticipo['price'].") WHERE stkmoveno='".$myrowDevolucionAnticipo['stkmoveno']."'";
					//$rs =DB_query($sqlAplicarRegresoFactura,$db);
					//echo "<br>SQL<pre>". $sqlAplicarRegresoFactura;

					$sqlAplicarRegresoAplicacion="UPDATE stockmoves SET qty = (qty - ".$dblPendienteAplicar / $myrowDevolucionAnticipo['price'].") WHERE stkmoveno='".$myrowDevolucionAnticipo['stkmoveno1']."'";
					//echo "<br>SQL<pre>". $sqlAplicarRegresoAplicacion;
					//$rs =DB_query($sqlAplicarRegresoAplicacion,$db);

				}

				$dblImporteDescuentoAnticipo -= $dblPendienteAplicar;
				$dblPendienteAplicar=$dblImporteDescuentoAnticipo;

			}
        }

	}else{//
		//echo "<br> XAXX010101000: ".$TipoAgrupacionXTag;
		if($TipoAgrupacionXTag==1 and $TypeInvoice <> 111){
			$sqldetails = 'SELECT distinct stockmoves.stkmoveno,
						-- stockmoves.stockid,
					    ProdLine.prodLineId as stockid,
						stockmoves.warranty,
						-- stockmaster.description,
						ProdLine.Description as description,
						/*stockmaster.serialised,
						stockmaster.controlled,*/
						0 as serialised,
						0 as controlled,
						sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						/*sum((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price*IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxnet,
						sum((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price*IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))-totaldescuento)/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)  as subtotal,
						-- sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)/case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						sum((stockmoves.price*IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,*/
						sum((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxnet,
						sum((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)-totaldescuento)/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)  as subtotal,
						-- sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)/case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						sum((stockmoves.price*case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end))/sum(case when stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end) AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
						stockcategory.MensajePV,
						sum(stockmoves.totaldescuento) as totaldescuento,
                        stockmoves.ref4 as movorderline,
                        stockmaster.flagadvance
				FROM stockmoves left JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno, stockmaster,
					stockcategory
					 	INNER  JOIN ProdLine ON ProdLine.prodLineId=stockcategory.prodLineId
						
				WHERE stockmoves.stockid = stockmaster.stockid '. $strFiltroBombeo .'
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref="' . $tag .'" '. $strSearchAnticipo . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid
				GROUP BY ProdLine.Description,ProdLine.prodLineId';
		}else{
			$sqldetails = 'SELECT distinct stockmoves.stkmoveno,
						stockmoves.stockid,
						stockmoves.warranty,
						stockmaster.description,
						stockmaster.serialised,
						stockmaster.controlled,
						(case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						/*((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)))) AS fxnet,
						((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))))-totaldescuento as subtotal,
						(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))) AS fxprice,*/
						((case when  stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price)) AS fxnet,
						((case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)*(stockmoves.price))-totaldescuento as subtotal,
						(stockmoves.price) AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
					    stockcategory.MensajePV,
					    sum(stockmoves.totaldescuento) as totaldescuento,
						stockmaster.longdescription,
						stockmaster.mbflag,
                        stockmoves.ref4 as movorderline,
                        stockmaster.flagadvance
					FROM stockmoves inner join stockmaster ON stockmoves.stockid = stockmaster.stockid
								inner join stockcategory on stockcategory.categoryid = stockmaster.categoryid
								left JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno 
				WHERE stockmoves.stockid = stockmaster.stockid '. $strFiltroBombeo .'
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref="' . $tag .'" '. $strSearchAnticipo . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid
					GROUP BY stockmoves.stkmoveno,
						stockmoves.stockid,
						stockmoves.warranty,
						stockmaster.description,
						stockmaster.serialised,
						stockmaster.controlled,
						(case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end)  ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)))) ,
						((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))))-totaldescuento ,
						(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))) ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode,
						stockmoves.showdescription,
					    stockcategory.MensajePV,
					    
						stockmaster.longdescription,
						stockmaster.mbflag,
                        stockmoves.ref4,
                        stockmaster.flagadvance';
		}
		if ($_SESSION['UserID'] == "desarrollo") {
			//echo "<pre>sqldetails: 3684 ".$sqldetails;
		}
        debug_sql($sqldetails, __LINE__,$debug_sql,__FILE__);
        $resultdetails=DB_query($sqldetails,$db);
		while ($myrow2=DB_fetch_array($resultdetails)){
			$stocknegativo = 0; //obtener si es negativo para precio
			$stockid=$myrow2['stockid'];

			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$stockcantidad=number_format($myrow2['quantity'],$decimalplaces,'.','');
			$stockcantidadCompleta = $myrow2['quantity'];

			if ($stockcantidad < 0) {
            	//Cantidad en negativo
            	$stocknegativo = 1;
            }

			/*if ($_SESSION['UserID'] == 'desarrollo' or $_SESSION['UserID'] == 'iortiz') {
				$stockcantidad = abs($stockcantidad);
			}*/


			$charelectronicdetail=$charelectronicdetail.'|'.abs($stockcantidad); //Cantidad como este en DB + o -
			$stockdescrip=$myrow2['description'];
			$totalCantEmision = 0;
            $movorderlineno = $myrow2['movorderline'];

            if($_SESSION['FacturaVersion'] == "3.3" and $blnFlagAnticipo3_3==true){
            	if($myrow2['subtotal'] >=1.1){
            		//Se omite opcion por el prorrateo
            		//$totalRowAnticipo=(($myrow2['subtotal'] * $dblImporteDescuentoAnticipo) / $subtotalInvoice);
            		$subPartida=$myrow2['subtotal'];
            		if( $subPartida <= $dblImporteDescuentoAnticipo){
            			$totalRowAnticipo = $subPartida -1;
            			$dblImporteDescuentoAnticipo -= $totalRowAnticipo;
            		}elseif ($subPartida > $dblImporteDescuentoAnticipo) {
            			if(($subPartida - $dblImporteDescuentoAnticipo) < 0.1){
            				$totalRowAnticipo = $subPartida -1;
            				$dblImporteDescuentoAnticipo -= $totalRowAnticipo;
            			}else{
            				$totalRowAnticipo = $dblImporteDescuentoAnticipo;
            				$dblImporteDescuentoAnticipo -= $totalRowAnticipo;
            			}
            		}
            	}else{
            		$totalRowAnticipo='0.00';
            	}
	        }

			if($_SESSION['DescrLargaFact'] == $myrow2['mbflag']){
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'].' '.$myrow2['longdescription'];
				$stockdescrip=$myrow2['description'].' '.$myrow2['longdescription'].' ';
			}else{
				//$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
				$stockdescrip=$myrow2['description'].' ';
			}
			
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT DISTINCT p.InvoiceValue AS val,
							                p.complemento,
							                sp.label
							FROM salesstockproperties p,
							     stockcatproperties sp,
							     stockmaster sm
							WHERE p.stkcatpropid=sp.stkcatpropid
							  AND sp.categoryid=sm.categoryid
							  AND p.orderno=" . $orderno . "
							  AND p.typedocument IN(110, '" . $TypeInvoice . "')
							  AND sp.reqatprint=1
							  AND sm.stockid='" . $stockid . "'";
                        
                        //echo $sqlpropertyes;
                        
			$resultpropertyes=DB_query($sqlpropertyes,$db);
			$xt=0;
			while ($myrowprop=DB_fetch_array($resultpropertyes)){
				$traba=explode(" ",$myrowprop['val']);
				$trab=$traba[0];
				if ($xt==0) {
					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				} else {
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
				}

				switch ($myrowprop['val']) {
					case 'Interno':
						$sqlbomb = "SELECT class FROM stockclass WHERE idclass = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['class'];
						break;
					case 'Externo':
						$sqlbomb = "  SELECT suppname FROM suppliers WHERE supplierid = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['suppname'];
						break;
					default:
						
						break;
				}

				$xt=$xt+1;
			}
			$descrprop=$descrprop." )";
			if ($xt==0){//
				$descrprop="";
			}
			if($myrow2['MensajePV'] <> ""){
				$descrprop = $descrprop.$myrow2['MensajePV'];
			}

			/*if ($_SESSION["desarrollo"]){
				echo "series 1: "."SELECT DISTINCT stockserialitems.serialno as serie
						    FROM stockserialmoves , stockmoves , stockserialitems
						    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
							AND stockserialmoves.stockid=stockserialitems.stockid
							AND stockserialmoves.serialno=stockserialitems.serialno
							AND stockserialitems.loccode='".$myrow2['almacen']."' 
							AND qualitytext NOT LIKE '%rechaz%' 
							AND stockmoves.stockid='CASCLI'";		
			}*/

			$numberserie= "";
			$xserie=0;

			if (strpos("@".$_SESSION ['DatabaseName'], "gruposervillantas")) {
                $sqlserials="SELECT DISTINCT stockserialitems.serialno AS serie
							FROM workorders 
							INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
							WHERE orderno= '".$orderno."'
							AND stockserialitems.customs= '".$stockid."' 
							AND qualitytext NOT LIKE '%rechaz%' AND stockserialitems.serialno NOT LIKE 'R-%'
							AND stockserialitems.stockid= 'cascli'";
				$resultado= DB_query($sqlserials,$db);
				
				while ($myrownseries=DB_fetch_array($resultado)) {
					if ($stockid!='cascli'){
						$consulta= "SELECT DISTINCT stockserialitems.serialno AS serie
									FROM workorders 
									INNER JOIN stockserialitems ON workorders.wo= stockserialitems.wo
									WHERE orderno= '".$orderno."'
									AND stockserialitems.customs= '".$stockid."'
									AND qualitytext LIKE '%rechaz%' 
									AND stockserialitems.serialno LIKE '%".$myrownseries['serie']."%'";
						$resultado2= DB_query($consulta, $db);

						if ($registro=DB_fetch_array($resultado2)){
							continue;
						}
					}						
					
					if ($xserie==0){
						$numberserie=' Series: '.$myrownseries['serie'];
					}else{
						$numberserie=$numberserie.', '.$myrownseries['serie'];
					}

					$xserie++;
				}

				
            }
			

			$descrnarrative="";
			/*$sqlnarrative="SELECT narrative
				FROM salesorderdetails p
				WHERE p.orderno=" . $orderno . "
				   AND p.stkcode='" . $stockid . "'";
			$resultnarrative=DB_query($sqlnarrative,$db);
			while ($myrownarrative=DB_fetch_array($resultnarrative)){
				$descrnarrative=$myrownarrative['narrative'];
				$descrnarrative=str_replace('|', '', $descrnarrative);
			}*/
			//para tyqsa                      
			if($myrow2['showdescription']==1){
				$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
			}else{
                if (strlen($descrnarrative)==0){
                    $stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
                }else{
                    $stockdescripuno=$descrnarrative.$numberserie;
                }
			}

			if($_SESSION ['DatabaseName'] == "erpgosea" or $_SESSION ['DatabaseName'] == "erpgosea_CAPA" or $_SESSION ['DatabaseName'] == "erpgosea_DES"){
				if ($myrow2['stockid'] =='10001'){
					$charelectronicdetail=$charelectronicdetail.'|'."RENTA DE ".$stockdescripuno;
				}else{
					$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
				}
			}else{
				$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			}
                        
			// Se QUITO POR EL DE ARRIBA $charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			$stockprecio=number_format($myrow2['fxprice'],$decimalplaces,'.','');
			
			//$stockneto=number_format($myrow2['fxnet'],$decimalplaces,'.','');
			if($_SESSION['FacturaVersion'] == "3.3"){
				if($blnFlagAnticipo3_3){
					//echo "<br>((".$myrow2['subtotal']." * ".$dblImporteDescuentoAnticipo.")/".$subtotalInvoice.")";
					$stockneto=number_format($myrow2['subtotal'] - $totalRowAnticipo,$decimalplaces,'.','');
				}else{
					$stockneto=number_format($myrow2['subtotal'],$decimalplaces,'.','');
				}
			}else{
				$stockneto=number_format($myrow2['fxnet'],$decimalplaces,'.','');			
			}


			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockcantidad: ".$stockcantidad." - stockcantidadCompleta ".$stockcantidadCompleta." - stockprecio: ".$stockprecio." - stockneto: ".$stockneto."<br>";
			}
			
			if ($myrow2['flagadvance'] == 1) {
				//Obtener total de la partida, si existe diferencia obtener precio unitario
				$totalPar = number_format($stockcantidad * $stockprecio,$decimalplaces,'.','');
				//echo "<br>totalPar1: ".$totalPar."<br>";
				if (abs($totalPar) != abs($stockneto)) {
					$stockprecio = number_format($stockneto/$stockcantidad,$decimalplaces,'.','');
				}

				if ($stockcantidad < 0 and $stockprecio > 0) {
					$stockprecio = $stockprecio * (-1);
				}
			}
			
			if ($stocknegativo == 1 and $stockprecio > 0) {
				//Si cantidad es negativo, poner precio en negativo para operacion
				$stockprecio = $stockprecio * (-1);
			}

			if ($_SESSION['UserID'] == 'desarrollo') {
				//echo "<br>stockprecio change: ".$stockprecio."<br>";
			}

			$charelectronicdetail=$charelectronicdetail.'|'.($stockprecio);
			$charelectronicdetail=$charelectronicdetail.'|'.($stockneto);
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			//$subtotal=number_format($myrow2['subtotal'],2,'.','');
			//$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			// consulta de aduana
			$stkmoveno=$myrow2['stkmoveno'];
			$almacen=$myrow2['almacen'];

			$sqlstockmovestaxes="SELECT stkmoveno,taxauthid,taxrate,taxcalculationorder,taxontax,taxauthorities.description, taxauthorities.c_Impuesto
					     FROM  stockmovestaxes,taxauthorities
					     WHERE taxauthorities.taxid=stockmovestaxes.taxauthid
						   AND stkmoveno=".$stkmoveno;
			
            debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
            debug_sql($sqlstockmovestaxes, __LINE__,$debug_sql,__FILE__);
			$resultstockmovestaxes=DB_query($sqlstockmovestaxes,$db);
			if ($_SESSION['UserID'] == "desarrollo") {
				echo "<br>sqlstockmovestaxes 2: ".$sqlstockmovestaxes;
			}
			//Agregar datos de CFDI 3.3
			if(DB_num_rows($resultstockmovestaxes)>0){
				while ($myrow3=DB_fetch_array($resultstockmovestaxes)){
					$impuesto=$myrow3['description'];
					$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
					$taxrate=number_format($myrow3['taxrate']*100,$decimalplaces,'.','');
					$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
					//$subtot = number_format($myrow2['subtotal'], 2); //Siempre se manejo en 2
					//$taxratetotal=number_format($myrow3['taxrate']*($subtot),$decimalplaces,'.','');
					
					$taxratetotal = $myrow3['taxrate'] * $myrow2['subtotal'];
					$taxratetotal = number_format($taxratetotal, $decimalplaces,'.','');

					//$taxtotalratex=$myrow3['taxrate']*($myrow2['fxnet']*$myrow2['fxprice']);
					$taxtotalratex=$myrow3['taxrate']*($myrow2['subtotal']);
					$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;

					$charelectronictaxs=$charelectronictaxs.'|'.$myrow3['c_Impuesto']; //Codigo de impuesto

					//Obtener factor
					$c_TipoFactor = "";
					$sqlFactor = "SELECT sat_tasas.c_TipoFactor
								FROM stockmaster
								LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
								LEFT JOIN sat_tasas ON sat_tasas.id = taxcategories.id_Tasa
								WHERE stockmaster.stockid = '".$myrow2['stockid']."'";
					if ($_SESSION['UserID'] == "desarrollo") {
						echo "<br>sqlstockmovestaxes 2: ".$sqlFactor;
					}

					


					$resultFactor=DB_query($sqlFactor,$db);
					if(DB_num_rows($resultFactor) > 0){
						$rowFactor = DB_fetch_array($resultFactor);
						$c_TipoFactor = $rowFactor['c_TipoFactor']; //Factor de impuesto
					}
					$charelectronictaxs=$charelectronictaxs.'|'.$c_TipoFactor; //Factor de impuesto
				}
			}
			



			//$subtotal=number_format($myrow2['subtotal']+$taxtotalratex,$decimalplaces,'.','');
			$subtotal = $myrow2['subtotal'] + $taxtotalratex;
			$subtotal = number_format($subtotal,$decimalplaces,'.','');

			$subtotal=number_format($myrow2['totaldescuento'] + $totalRowAnticipo ,$decimalplaces,'.','');

			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal ;
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			
			/*$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			//$subtotal=number_format($myrow2['subtotal']+$taxtotalratex,$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;*/


			/*$sqladuana="SELECT stockserialitems.customs as aduana,customs_number as noaduana,
					pedimento, DATE_FORMAT(customs_date,'%Y-%m-%d') as fechaaduana
				    FROM stockserialmoves , stockmoves , stockserialitems
				    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
					AND stockserialmoves.stockid=stockserialitems.stockid
					AND stockserialmoves.serialno=stockserialitems.serialno
					AND stockserialitems.loccode='".$almacen."'
					AND stockmoves.stkmoveno=".$stkmoveno."
					AND stockmoves.stockid='".$stockid."'";*/

			//Validación de No. pedimento

            if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA'){
				$sqladuana="SELECT nopedimento
							FROM pediment_aduanas 
							WHERE orderno=" . $orderno . "
				   				AND stockid='" . $myrow2['stockid'] . "'
				   				AND orderlineno = '" . $movorderlineno . "'";

				if ($_SESSION['UserID'] == "desarrollo"){
					//echo '<br/> sql aduanas 2: '.$sqladuana;
				}
				 debug_sql($sqladuana, __LINE__,$debug_sql,__FILE__);
				$Result= DB_query($sqladuana,$db);
				if (DB_num_rows($Result)==0) {
					$nameaduana="";
					$nopedimento="";
					$fechaaduana="";
				}else{
					$myrowaduana = DB_fetch_array($Result);
					$nopedimento= $myrowaduana['nopedimento'];
					
					$nameaduana = '';
					$numberaduana = '';
					$fechaaduana = '';
				}
			}else{
				$nameaduana="";
				$nopedimento="";
				$fechaaduana="";
			}

			if($myrow['type'] == 66) { // Si es factura remision

				$ordernotmp = $myrow['orderno'];

				$SQL = "
					SELECT GROUP_CONCAT(debtortrans.order_) AS order_
					FROM invoicetoremision
					INNER JOIN debtortrans
					ON debtortrans.id = invoicetoremision.idremision
					WHERE invoicetoremision.idinvoice = '{$myrow['iddocto']}'
				";
				debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
				$rstmp      = DB_query($SQL, $db);
				$rowtmp     = DB_fetch_array($rstmp);
				$ordernotmp = $rowtmp['order_'];

				if(empty($ordernotmp) == FALSE) {
					//Validación de No. pedimento
					if($_SESSION['DatabaseName'] == 'erppisumma_DES' || $_SESSION['DatabaseName'] == 'erppisumma' || $_SESSION['DatabaseName'] == 'erppisumma_CAPA'){
						$sqladuana="SELECT nopedimento
								FROM pediment_aduanas 
								WHERE orderno='" . $ordernotmp . "'
					   				AND stockid='" . $stockid . "'
					   				AND orderlineno = '" . $movorderlineno . "'";
			            if ($_SESSION['UserID'] == "desarrollo"){
							echo '<br/> sql aduanas 66-2: '.$sqladuana;
						}
						debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
						$rstmp = DB_query($SQL, $db);

						if($rowtmp = DB_fetch_array($rstmp)) {
							$fechaaduana = '';
							$nopedimento = $rowtmp['nopedimento'];
							$nameaduana = '';
						}
					}else{

						$SQL = "
							SELECT
							DATE_FORMAT(purchorderdetails.datecustoms,'%Y-%m-%d') AS fechaaduana,
							pedimento,
							customs AS aduana
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorderdetails.orderno = purchorders.orderno
							WHERE purchorders.requisitionno IN ($ordernotmp)
							AND purchorderdetails.itemcode = '$stockid'
						";
						debug_sql($SQL, __LINE__,$debug_sql,__FILE__);
						$rstmp = DB_query($SQL, $db);

						if($rowtmp = DB_fetch_array($rstmp)) {
							$fechaaduana = $rowtmp['fechaaduana'];
							$nopedimento = $rowtmp['pedimento'];
							$nameaduana = $rowtmp['aduana'];
						}
					}
				}
			}
            
			$charelectronicdetail=$charelectronicdetail.'|'.$nameaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$numberaduana;
			//$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana.'aqui';
			$charelectronicdetail=$charelectronicdetail.'|'.$almacen;


			$charelectronicdetail=$charelectronicdetail.'|'.$totalCantEmision;
			$charelectronicdetail=$charelectronicdetail.'|'.$movorderlineno;
			
			$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$nopedimento;

			/*$stockprecio=number_format($myrow2['fxnet']+$taxtotalratex,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=number_format($myrow2['fxnet']+$taxtotalratex,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal*/

		}
                /*if($_SESSION['UserID'] == "desarrollo"){
                    echo '<br><pre>$charelectronicdetail: '.$charelectronicdetail;
                }*/
                
	}
	// complemento INE
    $charelectronicdetailINEComplements="";
    $permiso_complementos = 'SELECT typecomplement FROM custbranch WHERE debtorno="'.$branch.'"';
	$resul_permiso = DB_query($permiso_complementos,$db);
	list($permiso_complemento) = DB_fetch_array($resul_permiso);
	if($permiso_complemento==1){
		$Select_INE = "SELECT typeprocess,
							typecommittee,
							entity, 
							idaccounting, 
							orderno,
							IFNULL(scope,'') as scope 
						FROM complementsine 
						WHERE orderno = '" . $orderno ."'";
		$Result_INE = DB_query($Select_INE, $db);
		$charelectronicdetailINEComplements = '|'.chr(13).chr(10).'010';
        while($ine = DB_fetch_array($Result_INE)){
        	$tipoproceso = utf8_decode($ine['typeprocess']);

        	$tipoproceso =  str_replace(chr(63),chr(241), $tipoproceso);
			$detallesINE .= $tipoproceso . '|'.$ine['typecommittee'].'|'.$ine['entity'].'|'.$ine['idaccounting'].'|'.$ine['orderno'].'|'.$ine['scope'];
		}
		$charelectronicdetailINEComplements=$charelectronicdetailINEComplements.'|'.$detallesINE;
		if ($_SESSION ['UserID'] == 'saplicaciones' ) {
			echo "<br>" . $charelectronicdetailINEComplements;
		}
    }
	// ivas retenidos

	if($_SESSION['UserID'] == "desarrollo"){
		//	echo __line__.'<br><pre>$charelectronic: '.$charelectronic;
		//	echo __line__.'<br><pre>$charelectronicdetail: '.$charelectronicdetail;
		//		echo __line__.'<br><pre>$charelectronictaxsret: '.$charelectronictaxsret;
		//		echo __line__.'<br><pre>$charelectronictaxsfederal: '.$charelectronictaxsfederal;
		//		echo __line__.'<br><pre>$charelectronictaxslocal: '.$charelectronictaxslocal;
		//		echo __line__.'<br><pre>$charelectronicEmbarque: '.$charelectronicEmbarque;
		//		echo __line__.'<br><pre>$totalCantEmision: '.$totalCantEmision;
		//                echo __line__.'<br><pre>$charelectronicdetailINEComplements: '.$charelectronicdetailINEComplements;
	}
        if(empty($totalCantEmision) == true){
            $totalCantEmision = 0;
        }
        if($nivelfacturacion==1){
                            debug_sql('TIPO OT', __LINE__,$debug_sql,__FILE__);
                          $stockid=$OT;  
                          $stockcantidad = 1;
                          $stockdescrip = $descripcionOT;
        }elseif($nivelfacturacion==2){
                            debug_sql('TIPO CONCEPTOS', __LINE__,$debug_sql,__FILE__);
                        
        }elseif($nivelfacturacion==3){
                            debug_sql('Productos manufacturacios', __LINE__,$debug_sql,__FILE__);
                        }
	// 	 die("END XSA");

	if ($rfccliente=="XEXX010101000" && $_SESSION['DesgloseIVA']!=1){
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsret.$charelectronictaxsfederal.$charelectronictaxs.$charelectronictaxslocal.$charelectronicEmbarque.$totalCantEmision.$charelectronicdetailINEComplements;
		$cadenaenviada=$cadenaenvio;//retornarString($cadenaenvio);
	}else{
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsfederal.$charelectronictaxs.$charelectronictaxslocal.$charelectronicEmbarque.$totalCantEmision.$charelectronicdetailINEComplements;
		$cadenaenviada=$cadenaenvio;//retornarString($cadenaenvio);
	}
        
    //debug_sql('cadena origsinal <br>'.$cadenaenviada, __LINE__,true,__FILE__,'blue');
    
    if ($_SESSION['UserID'] == "desarrollo") {
    	echo "<pre><br> Cadena Original: ".$cadenaenviada."<br><pre/>";	
    }
    
    return $cadenaenviada;
}// TERMINA XSAINVOINVING 

//
//**************************************************************************************************
//********************************************* FIN DE FUNCION PARA FACTURACION *********************************************

//************************************************************************************
//********************************************* INICIO DE FUNCION PARA NOTAS CREDITO ****************************************
//******************************************
// CON MOVIMIENTOS EN STOCKMOVES

function XSACreditNote($InvoiceNo, $orderno, $debtorno, $TypeInvoice,$tag,$serie,$folio, &$db)
{
	$arrBDAplicar = array('erpplacacentro','erpplacacentro_CAPA','erpplacacentro_DES' );
	$charelectronic='01';
	$charelectronic=$charelectronic.'|'.$serie;
	$serieelect=$serie;
	//$serieelect=$TypeInvoice.'-'.$_SESSION['Tagref'];
	$folioelect=$folio;//$InvoiceNoTAG;
	//$folioelect=$InvoiceNo;
	$charelectronic=$charelectronic.'|'.$serieelect;
	$charelectronic=$charelectronic.'|'.$folioelect;
	// consulto datos de la factura
	$imprimepublico=0;
	$InvoiceHeaderSQL = "SELECT DISTINCT
				replace(debtortrans.trandate,'-','/') as trandate,
				(debtortrans.ovamount*-1) as ovamount,
				(debtortrans.ovdiscount*-1) as ovdiscount,
				(debtortrans.ovgst*-1) as ovgst,
				debtorsmaster.name,
				debtorsmaster.address1,debtorsmaster.address2,
				debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,
				debtorsmaster.address6,debtorsmaster.invaddrbranch,
				custbranch.taxid as rfc,
				custbranch.brname,
				custbranch.braddress1,custbranch.braddress2,custbranch.braddress3,
				custbranch.braddress4,custbranch.braddress5,custbranch.braddress6,
				custbranch.brpostaddr1,custbranch.brpostaddr2,custbranch.brpostaddr3,
				custbranch.brpostaddr4,custbranch.brpostaddr5,custbranch.brpostaddr6,
				debtortrans.debtorno,debtortrans.branchcode,debtortrans.folio,
				replace(origtrandate,'-','/') as origtrandate,
				debtortrans.currcode,
				custbranch.branchcode,
				debtortrans.tpe,
				debtortrans.shipvia,
				(debtortrans.ovfreight*-1) as ovfreight,
				debtortrans.rate AS cambio,
				debtorsmaster.currcode as moneda,
				custbranch.defaultlocation,
				custbranch.brnumext,
				custbranch.brnumint,
				";
	$InvoiceHeaderSQL = $InvoiceHeaderSQL . "
				locations.taxprovinceid,
				debtortrans.tagref,
				custbranch.phoneno as telofi,
				IFNULL(paymentmethods.codesat,'') as paymentname,
			debtortrans.nocuenta
			FROM debtortrans 
				LEFT JOIN paymentmethods ON debtortrans.paymentname = paymentmethods.paymentname
				INNER JOIN debtorsmaster ON debtortrans.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch ON debtortrans.debtorno = custbranch.branchcode
				INNER JOIN currencies ON debtorsmaster.currcode = currencies.currabrev
				INNER JOIN stockmoves ON stockmoves.transno=debtortrans.transno
				INNER JOIN locations ON stockmoves.loccode = locations.loccode
			WHERE debtortrans.transno = " . $InvoiceNo . "
				AND debtortrans.type=11
				AND stockmoves.type=11";
        
	$Result=DB_query($InvoiceHeaderSQL,$db);
	if (DB_num_rows($Result)>0) {
		$myrow = DB_fetch_array($Result);
		// fecha emision
		$fechainvoice=$myrow['origtrandate'];
		$UserRegister=$myrow['UserRegister'];
		$charelectronic=$charelectronic.'|'.$fechainvoice;
		// subtotal
		$rfccliente=str_replace("-","",$myrow['rfc']);
		$rfccliente=str_replace(" ","",$rfccliente);
		$nombre=$myrow['name'];
		$subtotal= number_format($myrow['ovamount'], 2, '.', '');
		if (strlen($rfccliente)<12){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= number_format($myrow['ovamount']+$myrow['ovgst'], 2, '.', '');
			$imprimepublico=1;
		}elseif(strlen($rfccliente)>=14){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= number_format($myrow['ovamount']+$myrow['ovgst'], 2, '.', '');
			$imprimepublico=1;
		}
		$charelectronic=$charelectronic.'|'.$subtotal;
		// total factura
		$total=number_format($myrow['ovamount']+$myrow['ovgst'],2,'.','');

		$charelectronic=$charelectronic.'|'.$total;
		// total de iva
		$iva=number_format($myrow['ovgst'],'.','');
		// transladado
		$charelectronic=$charelectronic.'|'.$iva;
		// retenido
		$ivaret=0;
		$charelectronic=$charelectronic.'|'.$ivaret;
		//descuento trae desde el stockmoves
		if ($rfccliente!="XAXX010101000"){
			$sqldiscount = 'SELECT sum(totaldescuento) as descuento
				       FROM stockmoves
				       WHERE stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND stockmoves.show_on_inv_crds=1';
			$Result= DB_query($sqldiscount,$db);
			if (DB_num_rows($Result)==0) {
				$descuento=0;
			}else{
				$myrowdis = DB_fetch_array($Result);
				$descuento=number_format($myrowdis['descuento'],2,'.','');
			}
		}else{
			$sqldiscount = 'SELECT sum(totaldescuento+(IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)*totaldescuento)) as descuento
			       			FROM stockmoves inner join stockmaster on stockmaster.stockid=stockmoves.stockid
							-- LEFT JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
							LEFT JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno
		
					  		WHERE stockmoves.type='.$TypeInvoice.'
								AND stockmoves.transno=' . $InvoiceNo . '
								AND stockmoves.tagref=' . $tag . '
								AND stockmoves.show_on_inv_crds=1';
			$Result= DB_query($sqldiscount,$db);
			if (DB_num_rows($Result)==0) {
				$descuento=0;
			}else{
				$myrowdis = DB_fetch_array($Result);
				$descuento=number_format($myrowdis['descuento'],2,'.','');
			}
		}
		//descuento
		$descuento=number_format($descuento,2,'.','');
		$charelectronic=$charelectronic.'|'.$descuento;
		//motivo descuento
		$charelectronic=$charelectronic.'|';
		// tipo de moneda
                
		$moneda=$myrow['currcode'];
                
		// CANTIDAD CON LETRAS
		$amount = number_format($myrow['ovamount'],3);
		$ovgst = number_format($myrow['ovgst'],2);
		$rountotal = $amount + $ovgst ;
		$rountotal = number_format($rountotal,2);
		$totaletras=round($myrow['ovamount']+$myrow['ovgst'],2);

		$totalx=str_replace(',','',$total);

		$separa=explode(".",$totalx);
		$montoletra = $separa[0];

		$separa2=explode(".",$totalx,4);//
		$montoctvs2 = $separa2[1];
		$montoletra=Numbers_Words::toWords($montoletra,'es');
		//$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
		if ($moneda=='MXN'){
			$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
		}//
		else
		{
			$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
		}
		$charelectronic=$charelectronic.'|'.$montoletra;
		// tipo moneda
		$charelectronic=$charelectronic.'|'.$moneda;
		// tipo de cambio
		$rate=number_format($myrow['cambio'],6,'.','');
		$charelectronic=$charelectronic.'|'.$rate;
		// numero de orden para referencia
		$ordenref=$myrow['orderno'];
		$charelectronic=$charelectronic.'|'.$ordenref;
		// observaciones 1: vendedores
		$vendedor="";
		$SQL=" SELECT *
		       FROM salesman
		       WHERE salesmancode='".$myrow['salesman']."'";
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowsales = DB_fetch_array($Result);
			$vendedor=$myrowsales['salesmanname'];
		}
		$observaciones1='Vendedor: '.$myrow['salesman'].' '.$vendedor;
		$charelectronic=$charelectronic.'|'.$observaciones1;
		// observaciones 2
		$SQL=" SELECT l.telephone,l.comments,t.tagdescription
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='".$tag."'";
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$telephone=trim($myrowtags['telephone']);
			$comments=trim($myrowtags['comments']);
		}
		$observaciones2=" Atencion a clientes " .$telephone;
		$charelectronic=$charelectronic.'|'.$observaciones2;
		$metodopago = $myrow['paymentname'];
		if ($metodopago=="") {
			$metodopago = "99";
		}
		
		$nocuenta = $myrow['nocuenta'];
		if ($nocuenta=="") {
			$nocuenta = "No Identificado";
		}
		
		
		// observaciones 3
		$observaciones3='Id Nota de Credito:'.$InvoiceNo.' '.$comments .' usr:'.$UserRegister;
		$charelectronic=$charelectronic.'|'.$observaciones3;
		
		//$observaciones3='Id Nota de Credito:'.$InvoiceNo.' '.$comments .' usr:'.$UserRegister;
		$charelectronic=$charelectronic.'|'.$nocuenta;
		// datos de la forma de pago
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'02';
		
		$terminospago=$myrow['terms'];
		$SQL=" SELECT *
		       FROM  debtortrans
		       WHERE debtortrans.type=70
			AND debtortrans.order_=" . $InvoiceNo ;
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==0) {
			$Tipopago="Pago en una sola exhibicion";
		}else{
			$Tipopago="Parcialidades";
		}
		$Tipopago=$Tipopago.' '.$terminospago;
		//$Tipopago=$terminospago;
		$charelectronic=$charelectronic.'|'.$Tipopago;
		// condiciones de pago
		$charelectronic=$charelectronic.'|Genera Interes Moratorio 3 % mensual/credito';
		// metodo de pago
	//	$metodopago='varios';
		$charelectronic=$charelectronic.'|'.$metodopago;
		// fecha vencimiento
		$fechavence=$myrow['trandate'];
		$charelectronic=$charelectronic.'|'.$fechavence;
		// observaciones 4
		$observaciones4=$observaciones3;
		$charelectronic=$charelectronic.'|'.$observaciones4;
		// datos del cliente
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'03';
		$branch=$myrow['debtorno'];
		$charelectronic=$charelectronic.'|'.$branch;
		$charelectronic=$charelectronic.'|'.$rfccliente;
		$charelectronic=$charelectronic.'|'.$nombre;
		//$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
			$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		}else{
			$pais="Mexico";
		}

		if (in_array($_SESSION['DatabaseName'], $arrBDAplicar)) {
			$charelectronic=$charelectronic.'|';
		}
		else{
			$charelectronic=$charelectronic.'|'.$pais;
		}
		
		$calle="";
		if ($imprimepublico==0){
			$calle=$myrow['address1'];
		}
		$charelectronic=$charelectronic.'|'.$calle;
		$noext=$myrow['brnumext'];
		$charelectronic=$charelectronic.'|'.$noext;
		$noint=$myrow['brnumint'];
		$charelectronic=$charelectronic.'|'.$noint;
		$colonia="";

		if ($imprimepublico==0 || in_array($_SESSION['DatabaseName'], $arrBDAplicar)){
                    $colonia=$myrow['address2'];
		}
		$charelectronic=$charelectronic.'|'.$colonia;
		$localidad="";
		$charelectronic=$charelectronic.'|'.$localidad;
		$referenciacalle="";
		$charelectronic=$charelectronic.'|'.$referenciacalle;
		$municipio="";
		if ($imprimepublico==0){
			$municipio=$myrow['address3'];
		}
		$charelectronic=$charelectronic.'|'.$municipio;
		$edo="";
		if ($imprimepublico==0){
			$edo=$myrow['address4'];
		}
		$charelectronic=$charelectronic.'|'.$edo;
		$cp="";
		if ($imprimepublico==0){
			$cp=$myrow['address5'];
		}
		$charelectronic=$charelectronic.'|'.$cp;
		// datos del custbranch
		$charelectronic=$charelectronic.'|'.chr(13).chr(10).'04';
		$branch=$myrow['branchcode'];
		$charelectronic=$charelectronic.'|'.$branch;
		//$rfc=$myrow['rfc'];
		//$charelectronic=$charelectronic.'|'.$rfccliente;
		$nombre=$myrow['name'];
		$charelectronic=$charelectronic.'|'.$nombre;
		//$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));
		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
			$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		}else{
			$pais="Mexico";
		}
		$charelectronic=$charelectronic.'|'.$pais;
		$calle=$myrow['braddress1'];
		$charelectronic=$charelectronic.'|'.$calle;
		$noext=$myrow['brnumext'];
		$charelectronic=$charelectronic.'|'.$noext;
		$noint=$myrow['brnumint'];
		$charelectronic=$charelectronic.'|'.$noint;
		$colonia=$myrow['braddress6'];
		$charelectronic=$charelectronic.'|'.$colonia;
		$localidad=$myrow['braddress2'];
		$charelectronic=$charelectronic.'|'.$localidad;
		$referenciacalle="";
		$charelectronic=$charelectronic.'|'.$referenciacalle;
		$municipio=$myrow['braddress3'];
		$charelectronic=$charelectronic.'|'.$municipio;
		$edo=$myrow['braddress3'];
		$charelectronic=$charelectronic.'|'.$edo;
		$cp=$myrow['braddress4'];
		$charelectronic=$charelectronic.'|'.$cp;

	}
	// cadena para datos de los productos
	// productos vendidos
	$charelectronicdetail='';
	$charelectronicinidet='|'.chr(13).chr(10).'05';
	// datos de ivas
	$charelectronictaxs='';
	$charelectronictaxsini='|'.chr(13).chr(10).'07';
	if ($_SESSION['DecimalPlacesInvoice']==''){
		$_SESSION['DecimalPlacesInvoice']=6;
	}
	$decimalplaces=$_SESSION['DecimalPlacesInvoice'];
	if ($rfccliente!="XAXX010101000"){
		$sqldetails = 'SELECT stockmoves.stkmoveno,
					stockmoves.stockid,
					stockmoves.warranty,
					stockmaster.description,
					stockmaster.serialised,
					stockmaster.controlled,
					(stockmoves.qty) as quantity ,
					stockmoves.narrative,
					stockmaster.units,
					stockmaster.decimalplaces,
					stockmoves.discountpercent,
					stockmoves.discountpercent1,
					stockmoves.discountpercent2,
					((stockmoves.qty)*(stockmoves.price)) AS fxnet,
					((stockmoves.qty)*(stockmoves.price))- totaldescuento AS subtotal,
					(stockmoves.price) AS fxprice,
					stockmoves.narrative,
					stockmaster.units,
					stockmaster.categoryid
			FROM stockmoves,stockmaster
			WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmoves.type='.$TypeInvoice.'
				AND stockmoves.transno=' . $InvoiceNo . '
				AND stockmoves.tagref=' . $tag . '
				AND stockmoves.show_on_inv_crds=1';

		$resultdetails=DB_query($sqldetails,$db);
		$nolinea=0;
		$descrprop="";
		while ($myrow2=DB_fetch_array($resultdetails)){
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$stockcantidad=number_format($myrow2['quantity'],4,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
			$stockdescrip=$myrow2['description'];
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT DISTINCT p.InvoiceValue AS val,
							                p.complemento,
							                sp.label
							FROM salesstockproperties p,
							     stockcatproperties sp,
							     stockmaster sm
							WHERE p.stkcatpropid=sp.stkcatpropid
							  AND sp.categoryid=sm.categoryid
							  AND p.orderno=" . $orderno . "
							  AND p.typedocument=11
							  AND sp.reqatprint=1
							  AND sm.stockid='" . $stockid . "'";
			$resultpropertyes=DB_query($sqlpropertyes,$db);
			$xt=0;
			while ($myrowprop=DB_fetch_array($resultpropertyes)){
				$traba=explode(" ",$myrowprop['val']);
				$trab=$traba[0];
				if ($xt==0) {
					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				} else {
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
				}

				switch ($myrowprop['val']) {
					case 'Interno':
						$sqlbomb = "SELECT class FROM stockclass WHERE idclass = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['class'];
						break;
					case 'Externo':
						$sqlbomb = "  SELECT suppname FROM suppliers WHERE supplierid = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['suppname'];
						break;
					default:
						
						break;
				}

				$xt=$xt+1;
			}
			$descrprop=$descrprop." )";
			if ($xt==0){
				$descrprop="";
			}

			$descrnarrative="";
			$sqlnarrative="SELECT narrative
				FROM notesorderdetails p
				WHERE p.orderno=" . $orderno . "
				   AND p.stkcode='" . $stockid . "'";
			$resultnarrative=DB_query($sqlnarrative,$db);
			while ($myrownarrative=DB_fetch_array($resultnarrative)){
				$descrnarrative=str_replace('\r','',str_replace('\n','',$myrownarrative['narrative']));
                                $descrnarrative = str_replace('|', '-', $descrnarrative);
			}
			if (strlen($descrnarrative)==0){
				$descrnarrative="";
			}
			// consultar cantidad restante para tyqsa en remisones contra orden de trabajo
			/*$sqlnarrative="SELECT narrative
				FROM notesorderdetails p
				WHERE p.orderno=" . $orderno . "
				   AND p.stkcode='" . $stockid . "'";*/
			

			$charelectronicdetail=$charelectronicdetail.'|'.$stockdescrip.$descrprop.$descrnarrative;
			
			//IF($_SESSION)
			//ECHO $charelectronicdetail;
			$stockprecio=number_format($myrow2['fxprice'],6,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=number_format($myrow2['fxnet'],6,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			$subtotal=number_format($myrow2['subtotal'],2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			$nameaduana='';
			$numberaduana='';
			$fechaaduana='';
			$charelectronicdetail=$charelectronicdetail.'|'.$nameaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$numberaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
			$stkmoveno=$myrow2['stkmoveno'];
			$nolinea=$nolinea+1;
			$sqlstockmovestaxes="SELECT stkmoveno,taxauthid,taxrate,
						taxcalculationorder,taxontax,
						taxauthorities.description
					     FROM  stockmovestaxes,taxauthorities
					     WHERE taxauthorities.taxid=stockmovestaxes.taxauthid
						   AND stkmoveno=".$stkmoveno;
			$resultstockmovestaxes=DB_query($sqlstockmovestaxes,$db);
			while ($myrow3=DB_fetch_array($resultstockmovestaxes)){
				$impuesto=$myrow3['description']; 
				$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
				$taxrate=number_format($myrow3['taxrate']*100,2,'.','');
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				$taxratetotal=number_format($myrow3['taxrate']*($myrow2['subtotal'] - $totalRowAnticipo),6,'.','');
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;

			}

		}
	}else{
		$sqldetails = 'SELECT stockmoves.stkmoveno,
					stockmoves.stockid,
					stockmoves.warranty,
					stockmaster.description,
					stockmaster.serialised,
					stockmaster.controlled,
					(stockmoves.qty) as quantity ,
					stockmoves.narrative,
					stockmaster.units,
					stockmaster.decimalplaces,
					stockmoves.discountpercent,
					stockmoves.discountpercent1,
					stockmoves.discountpercent2,
					((stockmoves.qty)*(stockmoves.price)) AS fxnet,
					((stockmoves.qty)*(stockmoves.price))-totaldescuento AS subtotal,
					(stockmoves.price) AS fxprice,
					stockmoves.narrative,
					stockmaster.units,
					stockmaster.categoryid
			FROM stockmoves,stockmaster
			WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmoves.type='.$TypeInvoice.'
				AND stockmoves.transno=' . $InvoiceNo . '
				AND stockmoves.tagref=' . $tag . '
				AND stockmoves.show_on_inv_crds=1';
		
		$sqldetails = 'SELECT stockmoves.stkmoveno,
						stockmoves.stockid,
						stockmoves.warranty,
						stockmaster.description,
						stockmaster.serialised,
						stockmaster.controlled,
						(case when stockmoves.type not in (11,65) then stockmoves.qty*-1 else qty end) as quantity ,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmoves.discountpercent,
						stockmoves.discountpercent1,
						stockmoves.discountpercent2,
						((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate)))) AS fxnet,
						((case when  stockmoves.type not in (11,65)  then stockmoves.qty*-1 else qty end)*(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))))-totaldescuento as subtotal,
						(stockmoves.price + (stockmoves.price * IF(stockmovestaxes.taxrate IS NULL, 0, stockmovestaxes.taxrate))) AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
					    stockcategory.MensajePV
					FROM stockmoves inner join stockmaster ON stockmoves.stockid = stockmaster.stockid
								inner join stockcategory on stockcategory.categoryid = stockmaster.categoryid
								left JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno
				WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid';
		
		$resultdetails=DB_query($sqldetails,$db);
		while ($myrow2=DB_fetch_array($resultdetails)){
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$stockcantidad=number_format($myrow2['quantity'],$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
			$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT DISTINCT p.InvoiceValue AS val,
							                p.complemento,
							                sp.label
							FROM salesstockproperties p,
							     stockcatproperties sp,
							     stockmaster sm
							WHERE p.stkcatpropid=sp.stkcatpropid
							  AND sp.categoryid=sm.categoryid
							  AND p.orderno=" . $orderno . "
							  AND p.typedocument=" . $TypeInvoice . "
							  AND sp.reqatprint=1
							  AND sm.stockid='" . $stockid . "'";
			$resultpropertyes=DB_query($sqlpropertyes,$db);
			$xt=0;
			while ($myrowprop=DB_fetch_array($resultpropertyes)){
				$traba=explode(" ",$myrowprop['val']);
				$trab=$traba[0];
				if ($xt==0) {
					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				} else {
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
				}

				switch ($myrowprop['val']) {
					case 'Interno':
						$sqlbomb = "SELECT class FROM stockclass WHERE idclass = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['class'];
						break;
					case 'Externo':
						$sqlbomb = "  SELECT suppname FROM suppliers WHERE supplierid = '" . $myrowprop['complemento'] . "'";
						$rsqlbomb = DB_query($sqlbomb, $db);
						$rowbom = DB_fetch_array($rsqlbomb);
						$descrprop .= " - Proveedor : " . $rowbom['suppname'];
						break;
					default:
						
						break;
				}
			
				$xt=$xt+1;
			}
			$descrprop=$descrprop." )";
			if ($xt==0){//
				$descrprop="";
			}
			if($myrow2['MensajePV'] <> ""){
				$descrprop = $descrprop.$myrow2['MensajePV'];
			}
			$descrnarrative="";
			/*$sqlnarrative="SELECT narrative
			 FROM salesorderdetails p
			WHERE p.orderno=" . $orderno . "
			AND p.stkcode='" . $stockid . "'";
			$resultnarrative=DB_query($sqlnarrative,$db);
			while ($myrownarrative=DB_fetch_array($resultnarrative)){
			$descrnarrative=$myrownarrative['narrative'];
			$descrnarrative=str_replace('|', '', $descrnarrative);
			}*/
				
			$descrnarrative=str_replace('\r','',str_replace('\n','',$myrow2['narrative']));
			$descrnarrative=str_replace('|', '', $descrnarrative);
				
			if (strlen($descrnarrative)==0){
				$descrnarrative="";
			}
			if($myrow2['showdescription']==1){
				$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
			}else{
				if (strlen($descrnarrative)==0){
					$stockdescripuno=$stockdescrip.$descrprop.$descrnarrative.$numberserie;
				}else{
					$stockdescripuno=$descrnarrative;
			
				}
			}
			
			$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			$stockprecio=number_format($myrow2['fxprice'],$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=number_format($myrow2['fxnet'],$decimalplaces,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=number_format($myrow2['discountpercent']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=number_format($myrow2['discountpercent1']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=number_format($myrow2['discountpercent2']*100,2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			//$subtotal=number_format($myrow2['subtotal'],2,'.','');
			//$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			// consulta de aduana
			$stkmoveno=$myrow2['stkmoveno'];
			$almacen=$myrow2['almacen'];
			
			$sqlstockmovestaxes="SELECT stkmoveno,taxauthid,taxrate,taxcalculationorder,taxontax,taxauthorities.description
					     FROM  stockmovestaxes,taxauthorities
					     WHERE taxauthorities.taxid=stockmovestaxes.taxauthid
						   AND stkmoveno=".$stkmoveno;
			$resultstockmovestaxes=DB_query($sqlstockmovestaxes,$db);
			while ($myrow3=DB_fetch_array($resultstockmovestaxes)){
				$impuesto=$myrow3['description'];
				$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
				$taxrate=number_format($myrow3['taxrate']*100,'.','');
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				$taxratetotal=number_format($myrow3['taxrate']*($myrow2['subtotal']),6,'.','');
				//$taxtotalratex=$myrow3['taxrate']*($myrow2['fxnet']*$myrow2['fxprice']);
				$taxtotalratex=number_format($myrow3['taxrate']*($myrow2['subtotal']),6);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
			}
			
			$subtotal=number_format($myrow2['subtotal'],2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			$subtotal=number_format($myrow2['subtotal'],2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			
			$sqladuana="SELECT stockserialitems.customs as aduana,customs_number as noaduana,
					pedimento, DATE_FORMAT(customs_date,'%Y-%m-%d') as fechaaduana
				    FROM stockserialmoves , stockmoves , stockserialitems
				    WHERE stockmoves.stkmoveno=stockserialmoves.stockmoveno
					AND stockserialmoves.stockid=stockserialitems.stockid
					AND stockserialmoves.serialno=stockserialitems.serialno
					AND stockserialitems.loccode='".$almacen."'
					AND stockmoves.stkmoveno=".$stkmoveno."
					AND stockmoves.stockid='".$stockid."'";
			$Result= DB_query($sqladuana,$db);
			if (DB_num_rows($Result)==0) {
				$nameaduana="";
				$numberaduana="";
				$fechaaduana="";
			}else{
				$myrowaduana = DB_fetch_array($Result);
				$aduana=$myrowaduana['aduana'];
				$separaaduana=explode("|",$aduana);
				$nameaduana = $separaaduana[0];
				$numberaduana= $separaaduana[1];
				$fechaaduana= $separaaduana[2];
				if (strlen($nameaduana)==0 or strlen($numberaduana)==0 /*or !Is_Date($fechaaduana)*/){
					$nameaduana="";
					$numberaduana="";
					$fechaaduana="";
				}
			}
				
			$charelectronicdetail=$charelectronicdetail.'|'.$nameaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$numberaduana;
			$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
		}
	}
	// ivas retenidos
	$charelectronictaxsret='|'.chr(13).chr(10);//.'07|ISR|0.00';
	if ($rfccliente=="XAXX010101000"){
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsret.$charelectronicEmbarque.$totalCantEmision;
		$cadenaenviada=retornarString($cadenaenvio);
	}else{
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxs.$charelectronictaxsret.$charelectronicEmbarque.$totalCantEmision;
		$cadenaenviada=retornarString($cadenaenvio);

	}
	
	//echo '<pre><br>'.$cadenaenviada; 
	return $cadenaenviada;
}

//***************************************************************************************************************************
//
//
//*********************************** INICIO DE FUNCION PARA NOTA DE CREDITO DIRECTAS******************************
//***************************************************************************************************************************

function XSAInvoicingCreditdirect($InvoiceNo, $orderno, $debtorno, $TypeInvoice,$tag,$serie,$folio, &$db)
{
	// *********************************************** //
    // ********************************************** //
    // ******** No dejar echo en la funcion *********//
    // ******** Declarar Variables a usar ********  //
    // ******** Afecta al punto de venta ********* //
    // ****************************************** //
    // ***************************************** //
    
	//inicializo la variable $imprimepublico
	$imprimepublico=0;	
	$charelectronic='01';
	$charelectronic=$charelectronic.'|'.$serie.$folio;
	$serieelect=$serie;
	$folioelect=$folio;
	$charelectronic=$charelectronic.'|'.$serieelect;
	$charelectronic=$charelectronic.'|'.$folioelect;
	$detallerecibos = "";
	// consulto datos de la factura
	$SQLInvoice = "SELECT
				replace(debtortransmovs.origtrandate,'-','/') as origtrandate,
				SUM(abs(debtortransmovs.ovamount)) AS ovamount,
				SUM(abs(debtortransmovs.ovgst)) AS ovgst,
				debtortransmovs.currcode,
				debtortransmovs.rate as cambio,
				debtortransmovs.order_,
				replace(debtortransmovs.trandate,'-','/') as trandate,
				debtortransmovs.debtorno,
				IF(relacion_factoraje.iddebtortrans IS NOT NULL, suppliers.taxid,custbranch.taxid) as rfc,
				IF(relacion_factoraje.iddebtortrans IS NOT NULL, suppliers.suppname,debtorsmaster.name) as name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtortransmovs.branchcode,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.brnumint,
				custbranch.brnumext,
				custbranch.specialinstructions,
				custbranch.brpostaddr1,
				custbranch.brpostaddr2,
				custbranch.brpostaddr3,
				custbranch.brpostaddr4,
				custbranch.brpostaddr5,
				custbranch.brpostaddr6,
				custpais as brpostaddr7,
				www_users.realname,
				debtortransmovs.transno,
				debtortrans.reference,
				custbranch.phoneno as telofi,
				debtortrans.paymentname,
				IFNULL(debtortrans.nocuenta,IFNULL(custbranch.nocuenta,'')) as nocuenta,
				debtortrans.observf,
				IFNULL(paymentmethods.codesat,'') as codesat,
				debtortrans.id,
				debtortrans.invtext,
				custbranch.custpais
			FROM debtortransmovs
				INNER JOIN debtortrans ON debtortrans.type = debtortransmovs.type AND debtortrans.transno = debtortransmovs.transno
				LEFT JOIN paymentmethods ON debtortrans.paymentname = paymentmethods.paymentname
				LEFT JOIN relacion_factoraje ON relacion_factoraje.iddebtortrans = debtortrans.id
				LEFT JOIN suppliers ON  suppliers.supplierid = relacion_factoraje.supplierid
				,debtorsmaster,custbranch, www_users
			WHERE debtortransmovs.type=$TypeInvoice
				AND debtortransmovs.transno=$InvoiceNo
				AND debtortransmovs.tagref=$tag
				AND debtortransmovs.debtorno=debtorsmaster.debtorno
				AND debtortransmovs.debtorno=custbranch.debtorno
				AND debtortransmovs.branchcode=custbranch.branchcode
				AND www_users.userid = debtortransmovs.userid
			GROUP BY debtortransmovs.transno";


			// echo "<br> <pre>".$SQLInvoice;


	$Result=DB_query($SQLInvoice,$db);
	if (DB_num_rows($Result)>0) {
		{
			$myrow = DB_fetch_array($Result);
			// fecha emision
			$fechainvoice=$myrow['origtrandate'];
			$UserRegister="";//$myrow['UserRegister'];
			$charelectronic=$charelectronic.'|'.$fechainvoice;
		
			// subtotal
			$rfccliente=str_replace("-","",$myrow['rfc']);
			$rfccliente=str_replace(" ","",$rfccliente);
			$nombre=$myrow['name'];

			
			$subtotal= number_format($myrow['ovamount'], 2, '.', '');
			if ((strlen($rfccliente)<12) OR (strlen($rfccliente)>=14)){
				$rfccliente="XAXX010101000";
				$nombre="Publico en General";
				$subtotal= number_format($myrow['ovamount']+$myrow['ovgst'], 2, '.', '');
				$imprimepublico=1;
			}
			$charelectronic=$charelectronic.'|'.$subtotal;
			// total factura
			$total=number_format($myrow['ovamount']+$myrow['ovgst'],2,'.','');
			$charelectronic=$charelectronic.'|'.abs($total);
			// total de iva
			$iva=number_format($myrow['ovgst'],2,'.','');
			// transladado
			$charelectronic=$charelectronic.'|'.abs($iva);
			// retenido
			$ivaret=0;
			$charelectronic=$charelectronic.'|'.abs($ivaret);
			//descuento
			//$descuento=number_format(0,2,'.','');
			$descuento='0.00';
			$charelectronic=$charelectronic.'|'.($descuento);
			//motivo descuento
			$charelectronic=$charelectronic.'|';
			// tipo de moneda
			$moneda=$myrow['currcode'];
			// CANTIDAD CON LETRAS
			$totaletras=abs($myrow['ovamount']+$myrow['ovgst']);
			$separa=explode(".",$totaletras);
			if (count($separa) == 1) {
                $separa[1] = 0;
            }
			$montoctvs2 = $separa[1];
			$montoctvs1 = $separa[0];
			if ($montoctvs2>995){
				$montoctvs1=$montoctvs1+1;
			}
			$montoletra=Numbers_Words::toWords($montoctvs1,'es');
			$totaletras=number_format($totaletras,2);
			$separa=explode(".",$totaletras);
			$montoctvs2 = $separa[1];
			if ($montoctvs2>995){
				$montoctvs2=0;
			}
			$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
			if ($moneda=='MXN'){
				$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
			}
			else
			{
				$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
			}
			$charelectronic=$charelectronic.'|'.$montoletra;
			// tipo moneda
			$charelectronic=$charelectronic.'|'.$moneda;
			// tipo de cambio
			$rate=number_format($myrow['cambio'],4,'.','');
			$charelectronic=$charelectronic.'|'.$rate;
			// numero de orden para referencia
			$ordenref=$myrow['order_'];
			$charelectronic=$charelectronic.'|'.$ordenref;
			// observaciones 1: vendedores
			$vendedor=$myrow['realname'];
			$observaciones1='Vendedor: '.' '.$vendedor;
			$charelectronic=$charelectronic.'|'.$observaciones1;
			
                        // observaciones 2
			$SQL=" SELECT l.telephone,l.comments,t.tagdescription
			       FROM legalbusinessunit l, tags t
			       WHERE l.legalid=t.legalid AND tagref='".$tag."'";
			
                        $Result= DB_query($SQL,$db);
                        
			if (DB_num_rows($Result)==1) {
				$myrowtags = DB_fetch_array($Result);
				$telephone=trim($myrowtags['telephone']);
				$comments=trim($myrowtags['comments']);
			}
			$observaciones2=" Atencion a clientes " .$telephone;
			$charelectronic=$charelectronic.'|'.$observaciones2;

			$metodoPago = trim($myrow['codesat']);
		
			if($metodoPago == "") {
				$metodoPago = "99";
			}

			/*
			$metodopago = $myrow['paymentname'];
			if ($metodopago=="") {
				$metodopago = "No Identificado";
			}
			*/

			$nocuenta = $myrow['nocuenta'];
			if ($nocuenta=="") {
				$nocuenta = "No Identificado";
			}

			// observaciones 3
			$observaciones3='Id Nota :'.$InvoiceNo;//' '.$comments .' usr:'.$UserRegister;
			$TypeInvoice."
			AND debtortransmovs.transno=" . $InvoiceNo . "
			AND debtortransmovs.tagref=" . $tag .

			$observaciones3 =  $observaciones3 . $detallerecibos . "; " ;// "Este comprobante es complementario a los expedidos en la fecha y folios descritos en cada partida detallada arriba";

			$charelectronic=$charelectronic.'|'.$observaciones3 . $detallerecibos;

			//SE AGREGA NUEVO CAMPO INFORMACION EXTRA
			$charelectronic=$charelectronic . '|' . '';

			// datos de la forma de pago
			$charelectronic=$charelectronic.'|'.chr(13).chr(10).'02';
			
			$terminospago="Pago en una sola exhibicion";
			if(strlen($myrow['observf'])>0){
				$terminospago=$myrow['observf'];
			}

			//echo 'terminos:'.$terminospago.' obs:'.$myrow['observf'].'<br><br>';
			// $Tipopago=$Tipopago.' '.$terminospago;
			$Tipopago=$terminospago;
			$charelectronic=$charelectronic.'|'.$Tipopago;
			// condiciones de pago
			$charelectronic=$charelectronic.'|---';
			// metodo de pago
			//$metodopago='Varios';
			$charelectronic=$charelectronic.'|'.$metodoPago;
			// fecha vencimiento
			$fechavence=$myrow['trandate'];
			$charelectronic=$charelectronic.'|'.$fechavence;
			// observaciones 4
			$observaciones4=$nocuenta;
			$charelectronic=$charelectronic.'|'.$observaciones4;

			//<CFDI:Relacionados> Se solicita subirlo a MG
			$queryCFDI=""; 
			$c_TipoRelacion="";
			$pagare_fact="";
			if($_SESSION['FacturaVersion'] == "3.3"){
				if($TypeInvoice == 13){
					$c_TipoRelacion="01";
				}
                if($TypeInvoice == 200){
                    $c_TipoRelacion="08";
                }

				$queryCFDI="SELECT DISTINCT if (tiporelacion_relacion IS NULL, '".$c_TipoRelacion."',tiporelacion_relacion ) as c_TipoRelacion,debtortrans.uuid,debtortrans.type,debtortrans.id, substring(debtortrans.cadena, 3, 3) as version
							FROM notesorders_invoice 
							LEFT JOIN debtortrans on notesorders_invoice.transid_relacion = debtortrans.id
							WHERE notesorders_invoice.transid = '".$myrow['id']."'";
				$resultCFDI=DB_query($queryCFDI,$db);
				if($_SESSION['UserID'] == 'desarrollo'){
                        echo "Relacion:".$queryCFDI;
                 }

				if($TypeInvoice == '200'){
                	// para sustitucion
                	/*$sql = "SELECT '04' as c_TipoRelacion ,UUID AS uuid, id FROM log_cancelacion_sustitucion WHERE id ='".$myrow['id']."'; ";

                	//echo "<br> SQL Relacion: ".$sql;
                	$resultadoSus = DB_query($sql,$db);
                	if(DB_num_rows($resultadoSus)>0){
                		$resultCFDI  = $resultadoSus;
                	}*/

                	$strSQLUUID = " SELECT '04' as c_TipoRelacion ,UUID AS uuid FROM log_cancelacion_sustitucion WHERE transNo='".$InvoiceNo."' AND type ='".$TypeInvoice."'";
		            $charelectronicdetuuid= '';
		            //echo "<br>UUID: ".$strSQLUUID;
		            $resultSQLUUID=DB_query($strSQLUUID,$db);
		            if(DB_num_rows($resultSQLUUID)>0){
		            	$resultCFDI = $resultSQLUUID;
		                /*while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
		                    $charelectroniuid = '|' . chr(13) . chr(10) . '012';
		                    //$charelectronicdetuuid = "";
		                   // $charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
		                    $charelectronicdetuuid = $charelectronicdetuuid . '|' .$charelectroniuid . '|04|' . $myrowUUID['UUID'];
		                    
		                }*/

		            }else{
		                $strSQLUUID= "SELECT log_complemento_sustitucion.folio, log_complemento_sustitucion.type, 
		                log_complemento_sustitucion.uuid, log_complemento_sustitucion.transno, '04' as c_TipoRelacion
		                FROM log_complemento_sustitucion 
		                INNER JOIN debtortrans ON log_complemento_sustitucion.sustitucion_from = debtortrans.id
		                WHERE debtortrans.transno='".$InvoiceNo."' AND debtortrans.type ='".$TypeInvoice."'";

		                $resultSQLUUID=DB_query($strSQLUUID,$db);
		                if(DB_num_rows($resultSQLUUID) >0){
		                	$resultCFDI = $resultSQLUUID;
		                    /*while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
		                        $charelectroniuid = '|' . chr(13) . chr(10) . '012';
		                        //$charelectronicdetuuid = "";
		                        // $charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
		                        $charelectronicdetuuid = $charelectronicdetuuid . '|' .$charelectroniuid . '|04|' . $myrowUUID['uuid']; 


		                    }*/
		                }

		            }

                
                }
				if($_SESSION['UserID'] == 'desarrollo'){
                    //echo "string1:".$queryCFDI;
                }
				while ($myrowCFDI = DB_fetch_array($resultCFDI)) {

					if($myrowCFDI['type'] =='70'){
						$pagare_fact.="'".$myrowCFDI['id'] ."',";
					}else{
						if( $myrowCFDI['c_TipoRelacion'] == '08' AND $myrowCFDI['version'] == '3.3'){
							$myrowCFDI['c_TipoRelacion'] = '';
						}
						$charelectronic=$charelectronic.'|'.chr(13).chr(10).'012';
						$charelectronic=$charelectronic.'|'.$myrowCFDI['c_TipoRelacion'];
						$charelectronic=$charelectronic.'|'.$myrowCFDI['uuid'];
					}
					
				}
                if($TypeInvoice == 200){
                    $c_TipoRelacionCP="08";
                }else{
                    $c_TipoRelacionCP="01"; 
                }

				if($pagare_fact!=""){
					$pagare_fact=$pagare_fact . "'-3'";

					$queryCFDI="SELECT '".$c_TipoRelacionCP."' as c_TipoRelacion,debtortrans.uuid , substring(debtortrans.cadena, 3, 3) as version
								FROM debtortrans  
								INNER JOIN custallocns ON custallocns.transid_allocto = debtortrans.id
								WHERE custallocns.transid_allocfrom IN (".$pagare_fact.")
								GROUP BY debtortrans.uuid;";
                    if($_SESSION['UserID'] == 'desarrollo'){
                        echo "stringPAgo: ".$queryCFDI;
                    }
                    $resultCFDI=DB_query($queryCFDI,$db);
                    
					
					while ($myrowCFDI = DB_fetch_array($resultCFDI)) {
						
						if( $myrowCFDI['c_TipoRelacion'] == '08' AND $myrowCFDI['version'] == '3.3'){
							$myrowCFDI['c_TipoRelacion'] = '';
						}
						$charelectronic=$charelectronic.'|'.chr(13).chr(10).'012';
						$charelectronic=$charelectronic.'|'.$myrowCFDI['c_TipoRelacion'];
						$charelectronic=$charelectronic.'|'.$myrowCFDI['uuid'];
					}

				}
			}
			//</CFDI:Relacionados>

			// datos del cliente
			$charelectronic=$charelectronic.'|'.chr(13).chr(10).'03';
			$branch=$myrow['debtorno'];
			$charelectronic=$charelectronic.'|'.$branch;
			$charelectronic=$charelectronic.'|'.$rfccliente;
			$charelectronic=$charelectronic.'|'.$nombre;
			//$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));
			if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
				$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
			}else{
				$pais="Mexico";
			}
			$charelectronic=$charelectronic.'|'.$pais;
		}
		{
			$calle="";
			if ($imprimepublico==0){
				$calle=$myrow['address1'];
			}
			$charelectronic=$charelectronic.'|'.$calle;
			$noext=$myrow['brnumext'];
			$charelectronic=$charelectronic.'|'.$noext;
			$noint=$myrow['brnumint'];
			$charelectronic=$charelectronic.'|'.$noint;
			$colonia="";
			if ($imprimepublico==0){
				$colonia=$myrow['address2'];
			}
			$charelectronic=$charelectronic.'|'.$colonia;
			$localidad="";
			$charelectronic=$charelectronic.'|'.$localidad;
			$referenciacalle="";
			$charelectronic=$charelectronic.'|'.$referenciacalle;
			$municipio="";
			if ($imprimepublico==0){
				$municipio=$myrow['address3'];
			}
			$charelectronic=$charelectronic.'|'.$municipio;
			$edo="";
			if ($imprimepublico==0){
				$edo=$myrow['address4'];
			}
			$charelectronic=$charelectronic.'|'.$edo;
			$cp="";
			if ($imprimepublico==0){
				$cp=$myrow['address5'];
			}
		}
		{
			$charelectronic=$charelectronic.'|'.$cp;
			// datos del custbranch
			$charelectronic=$charelectronic.'|'.chr(13).chr(10).'04';
			$branch=$myrow['branchcode'];
			$charelectronic=$charelectronic.'|'.$branch;
			//$rfc=$myrow['rfc'];
			//$charelectronic=$charelectronic.'|'.$rfccliente;
			$nombre=$myrow['name'];
			$charelectronic=$charelectronic.'|'.$nombre;
			//$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));
			if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
				$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
			}else{
				$pais="Mexico";
			}
			$charelectronic=$charelectronic.'|'.$pais;
			$calle=$myrow['braddress1'];
			$charelectronic=$charelectronic.'|'.$calle;
			$noext=$myrow['brnumext'];
			$charelectronic=$charelectronic.'|'.$noext;
			$noint=$myrow['brnumint'];
			$charelectronic=$charelectronic.'|'.$noint;
			$colonia=$myrow['braddress6'];
			$charelectronic=$charelectronic.'|'.$colonia;
			$localidad="";
			$charelectronic=$charelectronic.'|'.$localidad;
			$referenciacalle="";
			$charelectronic=$charelectronic.'|'.$referenciacalle;
			$municipio=$myrow['braddress2'];
			$charelectronic=$charelectronic.'|'.$municipio;
			$edo=$myrow['braddress3'];
			$charelectronic=$charelectronic.'|'.$edo;
			$cp=$myrow['braddress4'];
			$charelectronic=$charelectronic.'|'.$cp;
		}
	}
	
	$charelectronicEmbarque='|'.chr(13).chr(10).'09';
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['specialinstructions'];
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr1'];//calle
	$charelectronicEmbarque=$charelectronicEmbarque.'|';//noext
	$charelectronicEmbarque=$charelectronicEmbarque.'|';//no int
	
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr6'];//colonia
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr3'];//municipio
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr4'];//cp
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr5'];//estado
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['custpais'];//pais
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr7'];
	
	$SQLInvoice = "SELECT
	replace(debtortransmovs.origtrandate,'-','/') as origtrandate,
	SUM(abs(debtortransmovs.ovamount)) AS ovamount,
	SUM(abs(debtortransmovs.ovgst)) AS ovgst,
	Round(abs(debtortransmovs.ovgst) / abs(debtortransmovs.ovamount), 2)  as porcentaje,
	debtortransmovs.currcode,
	debtortransmovs.rate as cambio,
	debtortransmovs.order_,
	replace(debtortransmovs.trandate,'-','/') as trandate,
	debtortransmovs.debtorno,
	custbranch.taxid as rfc,
	debtorsmaster.name,
	debtorsmaster.address1,
	debtorsmaster.address2,
	debtorsmaster.address3,
	debtorsmaster.address4,
	debtorsmaster.address5,
	debtortransmovs.branchcode,
	custbranch.braddress1,
	custbranch.braddress2,
	custbranch.braddress3,
	custbranch.braddress4,
	custbranch.braddress5,
	custbranch.braddress6,
	custbranch.brnumint,
	custbranch.brnumext,
	custbranch.specialinstructions,
	custbranch.brpostaddr1,
	custbranch.brpostaddr2,
	custbranch.brpostaddr3,
	custbranch.brpostaddr4,
	custbranch.brpostaddr5,
	custbranch.brpostaddr6,
	custpais as brpostaddr7,
	www_users.realname,
	debtortransmovs.transno,
	debtortrans.reference,
	custbranch.phoneno as telofi,
	debtortrans.paymentname,
	IFNULL(debtortrans.nocuenta,IFNULL(custbranch.nocuenta,'')) as nocuenta,
	debtortrans.observf,
	IFNULL(paymentmethods.codesat,'') as codesat,
	debtortrans.id,
	debtortrans.invtext,
	custbranch.custpais
	FROM debtortransmovs
	INNER JOIN debtortrans ON debtortrans.type = debtortransmovs.type AND debtortrans.transno = debtortransmovs.transno
	LEFT JOIN paymentmethods ON debtortrans.paymentname = paymentmethods.paymentname
	,debtorsmaster,custbranch, www_users
	WHERE debtortransmovs.type=$TypeInvoice
	AND debtortransmovs.transno=$InvoiceNo
	AND debtortransmovs.tagref=$tag
	AND debtortransmovs.debtorno=debtorsmaster.debtorno
	AND debtortransmovs.debtorno=custbranch.debtorno
	AND debtortransmovs.branchcode=custbranch.branchcode
	AND www_users.userid = debtortransmovs.userid
	GROUP BY Round(abs(debtortransmovs.ovgst) / abs(debtortransmovs.ovamount), 2)";
	// Consulta agrupada por porcentaje de impuestos
	$Result=DB_query($SQLInvoice,$db);

	// cadena para datos de los productos
	// productos vendidos
	$charelectronicdetail='';
	// datos de ivas
	$charelectronictaxs='';
	while($myrow = DB_fetch_array($Result)) {
		$charelectronicinidet='|'.chr(13).chr(10).'05';
	
		$charelectronictaxsini='|'.chr(13).chr(10).'07';
		$stockid='Nota '.$myrow['transno'];
		$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
		$charelectronicdetail=$charelectronicdetail.'|'.$myrow['transno'];
		$cantidad = 1;
		$stockcantidad=number_format($cantidad,4,'.','');
		$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
		$stockdescrip=$myrow['reference'] .' '. $myrow['invtext'];
		$charelectronicdetail=$charelectronicdetail.'|'.$stockdescrip;
		$stockprecio=number_format(abs($myrow['ovamount']),2,'.','');
		$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
		$stockneto=number_format(abs($myrow['ovamount']),2,'.','');
		$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
		$stockunits= '';
		$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
		$stockcat='NC';
		$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
		$ordencompra='';
		$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
		//DESCUENTO 1
		$charelectronicdetail = $charelectronicdetail . '|' . '0.00';
		//DESCUENTO 2
		$charelectronicdetail = $charelectronicdetail . '|' . '0.00';
		//DESCUENTO 3
		$charelectronicdetail = $charelectronicdetail . '|' . '0.00';
		//SUBTOTAL
		$charelectronicdetail = $charelectronicdetail . '|' . number_format(abs($myrow['ovamount']),2,'.','');
		
		$taxrate = 0;
		$taxratetotal = 0;
		if ($imprimepublico==0){
			$impuesto="IVA";

			//Agregar Atributos del 3.3
			//*************************
			$c_Impuestos="002";
			$c_TipoFactor="Tasa";
			//*************************
			$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
			$totalcred=(abs($myrow['ovgst'])/abs($myrow['ovamount']));

			//$totalcred=$totalcred-1;
			$taxrate=number_format($totalcred*100,6,'.','');
			$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
			$taxratetotal=number_format(abs($myrow['ovgst']),6,'.','');
			$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal.'|'.$c_Impuestos.'|'.$c_TipoFactor;
		}

		//ADUANA
		$charelectronicdetail = $charelectronicdetail . '|' . $taxrate; // 14 - Se agregan datos para impuestos notas de credito
		//NUMERO DE ADUANA
		$charelectronicdetail = $charelectronicdetail . '|' . $taxratetotal; // 15 - Se agregan datos para impuestos notas de credito
		//FECHA DE INGRESO A ADUANA
		$charelectronicdetail = $charelectronicdetail . '|' . $myrow['porcentaje']; // 16 - porcentaje de impuestos
	}

	// ivas retenidos
	//$charelectronictaxsret='|'.chr(13).chr(10);//.'07|ISR|0.00';
	$charelectronictaxsret = "";
	if ($charelectronictaxs != ""){
		return $charelectronic.$charelectronicdetail.$charelectronictaxs.$charelectronictaxsret.$charelectronicEmbarque;
	}else{
		return $charelectronic.$charelectronicdetail.$charelectronictaxsret.$charelectronicEmbarque;
	}

}

function BuscaAprobacion($serieap,$folioap, &$db)
{
	$SQLAprobacion="SELECT  concat(anioAprobacion,noAprobacion) as aprobacion
	      FROM AprobacionFolios
	      WHERE serie='".$serieap."'
	      AND ".$folioap." BETWEEN Inicial AND final";
	//echo $SQLAprobacion;
	$ResultAprobacion=DB_query($SQLAprobacion,$db);
	if (DB_num_rows($ResultAprobacion)>0) {
		$myrowaprobacion = DB_fetch_array($ResultAprobacion);
		$aprobacion=$myrowaprobacion['aprobacion'];
	}
	return $aprobacion;
}

function retornarString($cadena)
{
    $login = $cadena;
    $b     = array("�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�");
    $c     = array("a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","A","E","I","O","U","A","E","I","O","U");
    $login = str_replace($b,$c,$login);
    return $login;
}
?>
