<?php
function insertagltrans($db, $type, $typeno, $periodo, $account, $narrative, $tag, $amount){
	$ISQL="INSERT INTO gltrans (type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			tag,
			amount)
		VALUES (" . $type . ",
			" . $typeno . ",
			Now(),
			" . $periodo . ",
			'" . $account . "',
			'" . $narrative . "',
			" . $tag . ",
			" . $amount . ')';
			
	$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
	$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
	$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
	$IDgltrans = DB_Last_Insert_ID($db,'gltrans','counterindex');
	return $IDgltrans;
}


function insertadebtortrans($db, $trasno, $tagref, $type, $debtorno, $branchcode,  $prd, $reference, $tpe, $order_, $ovamount,$ovgst, $ovfreight,$rate,$invtext,$shipvia,$consignment,$alloc,$currcode){
	
	$ISQL = "INSERT INTO debtortrans (
			transno,
			tagref,
			type,
			debtorno,
			branchcode,
			trandate,
			prd,
			reference,
			tpe,
			order_,
			ovamount,
			ovgst,
			ovfreight,
			rate,
			invtext,
			shipvia,
			consignment,
			alloc,
			origtrandate,
			userid,
			currcode
			)
 		VALUES (
			". $trasno . ",
			". $tagref . ",
			" . $type . ",
			'" . $debtorno . "',
			'" . $branchcode . "',
			Now(),
			" . $prd . ",
			'" . $reference . "',
			'" . $tpe . "',
			'" .  $order_ . "',
			" . $ovamount . ",
			" . $ovgst . ", 
			" . $ovfreight . ",
			" . $rate . ",
			'" . $invtext . "',
			'" . $shipvia ."',
			'" . $consignment . "',
			" . $alloc . ",
			Now(),
			'" . $_SESSION['UserID'] . "',
			'" . $currcode . "'
		)";

	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla debtortrans debido a ');
	$DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
	$Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);

	$DebtorTransIDAbono = DB_Last_Insert_ID($db,'debtortrans','id');
				
	return $DebtorTransIDAbono;
}

/*INICIO
**CHECO QUE EXISTA UN REGISTRO POR FECHA Y POR TAG QUE LE CORRESPONDE  A CADA USUARIO EN LA TABLA DE usrCortedeCaja, 
**SI NO EXISTE SE INSERTA.
*/
{
	$TSQL = "SELECT T.tagref, T.tagdescription, T.areacode
			FROM tags T, sec_unegsxuser S
			WHERE T.tagref = S.tagref
			and S.userid = '" . $_SESSION['UserID'] . "'";
			
	$ErrMsg = _('La consulta no arrojo resultados');
	$TTransResult = DB_query($TSQL,$db,$ErrMsg);
	while ($tmyrow=DB_fetch_array($TTransResult)) {
		$FSQL = "SELECT u_cortecaja, fechacorte, u_status, tag
		FROM usrcortecaja
		WHERE fechacorte = STR_TO_DATE('" . date('m') . "/" . date('d') . "/" . date('Y') . "','%m/%d/%Y')
		and tag = '"  . $tmyrow['tagref'] . "'";
		$ErrMsg = _('No transactions were returned by the SQL because');
		$TransResult = DB_query($FSQL,$db,$ErrMsg);
		if (DB_num_rows($TransResult) == 0){
			$ISQL="INSERT INTO usrcortecaja (fechacorte,
					u_status,
					tag)
				VALUES (STR_TO_DATE('" . date('m') . "/" . date('d') . "/" . date('Y') . "','%m/%d/%Y'),
					0,
					" . $tmyrow['tagref'] . ")";
			$DbgMsg = _('El SQL fallo al insertar la transaccion:');
			$ErrMsg = _('No se pudo insertar la Transaccion Contable');
			$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
		}	
	}
}
/*FIN*/
$BatchNo = GetNextTransNo(12,$db);
$PeriodNo = GetPeriod(date("d/m/Y"),$db);
$tipocliente=ExtractTypeClient($_SESSION['Items'.$identifier]->DebtorNo,$db);
$noaccountdos=ClientAccount($tipocliente,'gl_categorydiscount',$db);//$_SESSION['CompanyRecord']['gllink_notesdebtors'];
$noaccount=ClientAccount($tipocliente,'gl_accountcontado',$db);
//INSERTA PAGO EN EFECTIVO
$montoparaiva=($montofacturatotal+$totaldesccategory);
$TaxTotalSD=($totaldesccategory*$TaxTotal)/($montoparaiva);
$TaxTotalSD=$TaxTotalSD/$_SESSION['CurrencyRate'];
$TaxTotalD=$TaxTotal-$TaxTotalSD;

$x = insertagltrans($db, 12, $BatchNo, $PeriodNo, 102003, 'Remision @'. $debtorno . " @ ".$_SESSION['Items'.$identifier]->CustomerName, $_SESSION['Tagref'], $montofacturatotal+$TaxTotalD/$_SESSION['CurrencyRate']);
$y = insertagltrans($db, 12, $BatchNo, $PeriodNo, $_SESSION['CompanyRecord']['gltempcheckpostpayment'], 'RemisionD @ '.$debtorno, $_SESSION['Tagref'], $totaldesccategory+$TaxTotalSD/$_SESSION['CurrencyRate']);
//$noaccount = $_SESSION['CompanyRecord']['gllink_Invoice'];
$referenciatxt = 'ABONO A FACTURA DE CONTADO';
//$noaccountdos = $_SESSION['CompanyRecord']['gllink_customersdiscount'];
$IDgltrans =  insertagltrans($db, 12, $BatchNo, $PeriodNo, $noaccount, $referenciatxt, $_SESSION['Tagref'], -(($montofacturatotal+$TaxTotalD)/$_SESSION['CurrencyRate']));
$referenciatxt = 'ABONO A FACTURA DE CONTADO DESCUENTO';
$IDgltransdos =  insertagltrans($db, 12, $BatchNo, $PeriodNo, $noaccountdos, $referenciatxt, $_SESSION['Tagref'], -(($totaldesccategory+$TaxTotalSD)/$_SESSION['CurrencyRate']));

$IDfromtrans = insertadebtortrans($db, $BatchNo, $_SESSION['Tagref'], 12, $_SESSION['Items'.$identifier]->DebtorNo, $_SESSION['Items'.$identifier]->Branch,  $PeriodNo, '', '', 0, -($montofacturatotal+$totaldesccategory+$TaxTotalD+$TaxTotalSD),0, 0,$_SESSION['CurrencyRate'],$referencia,'','',-($montofacturatotal+$totaldesccategory+$TaxTotalD+$TaxTotalSD),$_SESSION['CurrAbrev']);
//*********INSERTA EN CUENTA DE IVAS**********
//***INSERTA EL IVA PROPORCIONAL EN CUENTA DE IVAS POR COBRAR E IVAS PAGADOS
//***INSERTA EN CUENTA IVAS POR COBRAR
/*Obtiene  cta de ivas pagados e ivas por cobrar*/
$SSQL = "SELECT taxglcodepaid,taxglcode,taxglcodediscountpaid,taxglcodediscount
FROM taxauthorities
WHERE taxid = 1";
$ErrMsg = _('Esta consulta no arrojo resultados');
$STransResult = DB_query($SSQL,$db,$ErrMsg);
while ($smyrow=DB_fetch_array($STransResult)) {
$ctaivaspagados =  $smyrow['taxglcodepaid'];
$ctaivaporcobrar = $smyrow['taxglcode'];
$ctaivaspagadosdiscount =  $smyrow['taxglcodediscountpaid'];
$ctaivaporcobrardiscount = $smyrow['taxglcodediscount'];
}
//$TaxAmountD=$totaldesccategory*$TaxAmount/$_SESSION['Items'.$identifier]->total;

//***INSERTA EN CUENTA IVAS POR COBRAR
$x = insertagltrans($db, 12, $BatchNo, $PeriodNo, $ctaivaporcobrar, 'ABONO IVA PROPORCIONAL IC Remision', $_SESSION['Tagref'], $TaxTotalD/$_SESSION['CurrencyRate']);
//***INSERTA EN CUENTA IVAS PAGADOS
$x = insertagltrans($db, 12, $BatchNo, $PeriodNo, $ctaivaspagados, 'ABONO IVA PROPORCIONAL IP Remision ', $_SESSION['Tagref'], -$TaxTotalD/$_SESSION['CurrencyRate']);

//***************movimientos de ivas por descuento

//***INSERTA EN CUENTA IVAS POR COBRAR
$yiva = insertagltrans($db, 12, $BatchNo, $PeriodNo, $ctaivaporcobrardiscount, 'ABONO IVA PROPORCIONAL IC de RemisionD ', $_SESSION['Tagref'], $TaxTotalSD/$_SESSION['CurrencyRate']);
//***INSERTA EN CUENTA IVAS PAGADOS
$ziva = insertagltrans($db, 12, $BatchNo, $PeriodNo, $ctaivaspagadosdiscount, 'ABONO IVA PROPORCIONAL IP de RemisionD ', $_SESSION['Tagref'], -$TaxTotalSD/$_SESSION['CurrencyRate']);




//echo "monto factura:".$montofacturatotal."<br>";
$montoalloc=$montofacturatotal+$totaldesccategory+$TaxTotalD+$TaxTotalSD;
$MSQLx = "UPDATE debtortrans
	SET diffonexch=0,
	alloc = " . $montoalloc . ",
	settled = 1
	WHERE id = " . $DebtorTransID;
//echo $MSQLx;
$Result = DB_query($MSQLx,$db,$ErrMsg,$DbgMsg,true);
//inserta en la tabla de custallocns

$CASQL = "INSERT INTO
	custallocns (
	datealloc,
	amt,
	transid_allocfrom,
	transid_allocto
	) VALUES ('" . FormatDateForSQL(date('d/m/Y')) . "',
	" . $montoalloc . ',
	' . $IDfromtrans . ',
	' . $DebtorTransID . ')';
$Result = DB_query($CASQL,$db,$ErrMsg,$DbgMsg,true);
$ISQL = "INSERT INTO debtortransmovs (
		transno,
		type,
		debtorno,
		branchcode,
		trandate,
		prd,
		reference,
		tpe,
		order_,
		ovamount,
		ovgst,
		ovfreight,
		rate,
		invtext,
		shipvia,
		consignment,
		alloc,
		tagref,
		origtrandate,
		idgltrans,
		userid,
		currcode
		)
	VALUES (
		" . $BatchNo . ",
		" . "12"  .", 
		'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
		'" . $_SESSION['Items'.$identifier]->Branch . "',
		Now(),
		" . $PeriodNo . ",
		'125 - " . $InvoiceNo . "',
		'',
		0,
		" . -($montoalloc-$TaxTotal) . ",
		" . -$TaxTotal . ",
		0,
		" . 1 . ",
		'AFECTO DOCUMENTO " .  $InvoiceNo . " (" . str_replace("|","-",$InvoiceNoTAG) . ")" . "',
		'',
		'',
		" . $montoalloc. ",
		" . $_SESSION['Tagref']. ",
		Now(),
		" . $IDgltrans . ",
		'" . $_SESSION['UserID'] . "',
		'" . $_SESSION['CurrAbrev'] . "'
	)";
	//echo $ISQL;
	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE ESTE ERROR Y BUSQUE AYUDA') . ': ' . _('El registro de transaccion de cliente no se pudo insertar');
	$DbgMsg = _('El siguiente SQL fue utilizado');
	$Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
	
	$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice
	       FROM legalbusinessunit l, tags t
	       WHERE l.legalid=t.legalid AND tagref='".$_SESSION['Tagref']."'";
	$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Result)==1) {
		$myrowtags = DB_fetch_array($Result);
		$rfc=trim($myrowtags['taxid']);
		$keyfact=$myrowtags['address5'];
		$nombre=$myrowtags['tagname'];
		$area=$myrowtags['areacode'];
		$legaid=$myrowtags['legalid'];
		$tipofacturacionxtag=$myrowtags['typeinvoice'];
	}
		//****//
	$InvoiceNoTAGR = DocumentNext(12, $_SESSION['Tagref'],$area,$legaid, $db);
	$MSQL = "UPDATE debtortrans
		SET folio = '" . $InvoiceNoTAGR . "' 
		WHERE type = 12
		and transno = " . $BatchNo . "
		and tagref = " . $_SESSION['Tagref'];
	$Result= DB_query($MSQL,$db,$ErrMsg,$DbgMsg,true);
	$Result=DB_Txn_Commit($db);
	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/CustomerReceiptFacturaContado.php?imprimir=yes&transnofac=' . $InvoiceNo . '&debtorno=' . $_SESSION['Items'.$identifier]->DebtorNo . '&branchcode=' .  $_SESSION['Items'.$identifier]->Branch . '&tag=' . $_SESSION['Tagref'] .'&foliorecibo=' . $InvoiceNoTAGR . '&typeinvoice=125&transnorecibo='  . $BatchNo . '">';
?>