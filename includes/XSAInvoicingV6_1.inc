<?php

//FCC
//NUEVA FUNCION DocumentNext, QUE TOMA EN CUENTA COMO PARAMETRO EL DEPARTAMENTO.
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
	//echo "<br>" . $SQL;

	$result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]==0)
	{
	    $SQL = "SELECT typename FROM sysDocumentIndex WHERE typeid = " . $TransType;
	    //echo "<br>" . $SQL;
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
	 // echo "<br>" . $SQL;
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
	//echo '<pre><br>'.$SQL;
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
	//echo '<pre><br>'.$SQL;
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

function XSAInvoicing($InvoiceNo, $orderno, $debtorno, $TypeInvoice, $tag, $serie, $folio, &$db)
{
	$charelectronic='01';
	$charelectronicEmbarque='';
	$charelectronic=$charelectronic.'|'.$serie;
	$serieelect=$serie;
	//$serieelect=$TypeInvoice.'-'.$_SESSION['Tagref'];
	$folioelect=$folio;//$InvoiceNoTAG;
	//$folioelect=$InvoiceNo;
	$charelectronic=$charelectronic.'|'.$serieelect;
	$charelectronic=$charelectronic.'|'.$folioelect;
	// consulto datos de la factura
	$imprimepublico=0;
	if ($TypeInvoice == 11) {
		$tabla = 'notesorders';
	}else{
		$tabla = 'salesorders';
	}
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
			tags.typegroup,debtortrans.id
		FROM debtortrans 
			left join shippers on debtortrans.shipvia=shippers.shipper_id 
			join tags on tags.tagref=debtortrans.tagref,
			debtorsmaster,custbranch,".$tabla." as salesorders ,
			salesman,locations,paymentterms

		WHERE debtortrans.order_ = salesorders.orderno

		AND debtortrans.type=".$TypeInvoice."
		AND debtortrans.transno=" . $InvoiceNo . "
		AND debtortrans.tagref=" . $tag . "

		AND debtortrans.debtorno=debtorsmaster.debtorno
		AND salesorders.paytermsindicator=paymentterms.termsindicator
		AND debtortrans.debtorno=custbranch.debtorno
		AND debtortrans.branchcode=custbranch.branchcode
		AND salesorders.salesman=salesman.salesmancode
		AND salesorders.fromstkloc=locations.loccode ";
	if ($_SESSION['UserID']=='admin') {

		//echo '<pre>sql:<br>'.$SQLInvoice.'<br>';
	}
	$Result=DB_query($SQLInvoice,$db);
	if (DB_num_rows($Result)>0) {
		$myrow = DB_fetch_array($Result);
		//impuestos federales
		$totretencionfederal=0;
		//$charelectronictaxslocal='|'.chr(13).chr(10);
		$qry = "Select debtortranstaxesclient.*,sec_taxes.nametax from debtortranstaxesclient, sec_taxes
				where debtortranstaxesclient.idtax = sec_taxes.idtax
				AND sec_taxes.typetax=1
				and debtortranstaxesclient.iddoc = ".$myrow['id'];
			
		$rs = DB_query($qry,$db);
		$totalImpuestosLocales=0;
		if (DB_num_rows($rs) > 0){
			$charelectronictaxsfederal='';
			$charelectronictaxsfederalini='|'.chr(13).chr(10).'06';
			while ($regs = DB_fetch_array($rs)){
				$charelectronictaxsfederal.=$charelectronictaxsfederalini."|".$regs['nametax']."|".$regs['percent']."|".$regs['amount'];
				$totretencionfederal=$totretencionfederal+$regs['amount'];
			}
		}
		
		//impuestos locales
		$totalretencionlocal=0;
		//$charelectronictaxslocal='|'.chr(13).chr(10);
		$qry = "Select debtortranstaxesclient.*,sec_taxes.nametax from debtortranstaxesclient, sec_taxes
				where debtortranstaxesclient.idtax = sec_taxes.idtax
				AND sec_taxes.typetax=2
				and debtortranstaxesclient.iddoc = ".$myrow['id'];
			
		$rs = DB_query($qry,$db);
		$totalImpuestosLocales=0;
		if (DB_num_rows($rs) > 0){
			$charelectronictaxslocal.='08';
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

		$metodoPago = $myrow['paymentname'];
		if ($metodoPago=="")
			$metodoPago = "No Identificado";

		$nocuenta = $myrow['nocuenta'];
		if ($nocuenta=="")
			$nocuenta = "No Identificado";

		// subtotal
		$rfccliente=str_replace("-","",$myrow['rfc']);
		$rfccliente=str_replace(" ","",$rfccliente);
		$nombre=$myrow['name'];
		$subtotal= FormatNumberERP($myrow['ovamount']);
		if (strlen($rfccliente)<12){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= FormatNumberERP($myrow['ovamount']+$myrow['ovgst']);
			$imprimepublico=1;
		}elseif(strlen($rfccliente)>=14){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= FormatNumberERP($myrow['ovamount']+$myrow['ovgst']);
			$imprimepublico=1;
		}
		$charelectronic=$charelectronic.'|'.$subtotal;
		// total factura
		$total=FormatNumberERP($myrow['ovamount']+$myrow['ovgst']+$totalretencion);
		$charelectronic=$charelectronic.'|'.$total;
		// total de iva
		$iva=FormatNumberERP($myrow['ovgst']);
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
				$descuento=FormatNumberERP($myrowdis['descuento']);
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
				$descuento=FormatNumberERP($myrowdis['descuento']);
			}	
		}
		$descuento=FormatNumberERP($descuento);
		$charelectronic=$charelectronic.'|'.$descuento;
		//motivo descuento
		$charelectronic=$charelectronic.'|';
		// tipo de moneda
		$moneda=$myrow['currcode'];
		// CANTIDAD CON LETRAS
		$totaletras=($myrow['ovamount']+$myrow['ovgst'])+$totalretencion;
		$totalx=str_replace(',', '', FormatNumberERP($totaletras));
		$separa=explode(".",$totalx);
		$montoletra = $separa[0];
		$separa2=explode(".", FormatNumberERP($total));
		$montoctvs2 = $separa2[1];
		
		
		$montoletra=Numbers_Words::toWords($montoletra,'es');
		
		$zeroPad = "";
		if (empty($_SESSION['Decimals'])) {
			$zeroPad = str_pad($zeroPad, 4, 0, STR_PAD_RIGHT);
		} else {
			$zeroPad = str_pad($zeroPad, $_SESSION['Decimals'], 0, STR_PAD_RIGHT);
		}
		
		if ($moneda=='MXN') {
			$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ."/1$zeroPad M.N.";
		} else {
			$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/1$zeroPad USD";
		}
		$charelectronic=$charelectronic.'|'.$montoletra;
		// tipo moneda
		$charelectronic=$charelectronic.'|'.$moneda;
		// tipo de cambio
		$rate=FormatRateNumberERP(1/$myrow['cambio']);
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
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)>0) {
			while ($myrowpagos=DB_fetch_array($Result)){
				$numpago=$numpago+1;
				$fechapago=ConvertSQLDate($myrowpagos['trandate']);
				$montopago=$myrowpagos['monto'];
				$montopago=FormatNumberERP($montopago);
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
		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
			$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		}else{
			$pais="Mexico";
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
			$referenciacalle="Telefono. ".$myrow['telofi'];
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
		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
			$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		}else{
			$pais="Mexico";
		}
		//$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
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
		$municipio=$myrow['braddress2'];;
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
		
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr2'];//colonia
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr3'];//municipio
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr4'];//cp
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr5'];//estado
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr6'];//pais
		$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr7'];
		
	}
	// cadena para datos de los productos
	// productos vendidos
	$charelectronicdetail='';
	$charelectronicinidet='|'.chr(13).chr(10).'05';
	// datos de ivas
	$charelectronictaxs='';
	$charelectronictaxsini='|'.chr(13).chr(10).'07';
	$decimalplaces=6;

	
	if ($rfccliente!="XAXX010101000"){//
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
						stockcategory.MensajePV
				FROM stockmoves,stockmaster,stockcategory 
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
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.categoryid,
						stockmoves.loccode  as almacen,
						stockmoves.showdescription,
						stockcategory.MensajePV
				FROM stockmoves,stockmaster,stockcategory
				WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type='.$TypeInvoice.'
					AND stockmoves.transno=' . $InvoiceNo . '
					AND stockmoves.tagref=' . $tag . '
					AND stockmoves.show_on_inv_crds=1
					AND stockcategory.categoryid = stockmaster.categoryid';
		}
		$resultdetails=DB_query($sqldetails,$db);
		$nolinea=0;
		$descrprop="";
	
		while ($myrow2=DB_fetch_array($resultdetails)){
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$stockcantidad=FormatNumberERP($myrow2['quantity']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
			if($_SESSION['DescrLargaFact'] == $myrow2['mbflag']){
					$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'].' '.$myrow2['longdescription'];
				}else{
					$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
				}
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT distinct p.InvoiceValue as val,sp.label
			     FROM salesstockproperties p,  stockcatproperties sp, stockmaster sm
			     WHERE p.stkcatpropid=sp.stkcatpropid
			           AND sp.categoryid=sm.categoryid
					   AND sp.reqatprint=1
				   AND p.orderno=" . $orderno . "
				   AND p.typedocument=" . $TypeInvoice . "
				   AND sm.stockid='" . $stockid . "'";
			$resultpropertyes=DB_query($sqlpropertyes,$db);
			$xt=0;
			while ($myrowprop=DB_fetch_array($resultpropertyes)){
// 				$traba=explode(" ",$myrowprop['val']);
// 				$trab=$traba[0];
				$trab=$myrowprop['val'];
				if ($xt==0){

					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				}else{
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
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
			$descrnarrative="";
			
			$descrnarrative=str_replace('\r','',str_replace('\n','',$myrow2['narrative']));
			$descrnarrative=str_replace('|', '', $descrnarrative);
			
			/*
			if ($TypeInvoice==11){
				$tablades=0;
			}else{
				$tipodoc=70;
			}
			
			$sqlnarrative="SELECT narrative
				FROM salesorderdetails p
				WHERE p.orderno=" . $orderno . "
				   AND p.stkcode='" . $stockid . "'";
			$resultnarrative=DB_query($sqlnarrative,$db);
			while ($myrownarrative=DB_fetch_array($resultnarrative)){
				$descrnarrative=str_replace('\r','',str_replace('\n','',$myrownarrative['narrative']));
				$descrnarrative=str_replace('|', '', $descrnarrative);
			}
			if (strlen($descrnarrative)==0){
				$descrnarrative="";
			}*/
			//$descrnarrative="";
			// consulta de aduana
			$stkmoveno=$myrow2['stkmoveno'];
			$almacen=$myrow2['almacen'];
			$numberserie="";
			$xserie=0;
			$sqlserials="SELECT stockserialitems.serialno as serie
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
			/**/
			$charelectronicdetail=$charelectronicdetail.'|'.$stockdescripuno;
			$stockprecio=FormatNumberERP($myrow2['fxprice']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=FormatNumberERP($myrow2['fxnet']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=FormatNumberERP($myrow2['discountpercent']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=FormatNumberERP($myrow2['discountpercent1']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=FormatNumberERP($myrow2['discountpercent2']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			$subtotal=FormatNumberERP($myrow2['subtotal']);
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
			$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;
			$nolinea=$nolinea+1;
			
			
			$sqlstockmovestaxes="SELECT stkmoveno,taxauthid,taxrate,
						taxcalculationorder,
						taxontax,
						taxauthorities.description
					     FROM  stockmovestaxes,taxauthorities
					     WHERE taxauthorities.taxid=stockmovestaxes.taxauthid
						   AND stkmoveno=".$stkmoveno;
			
			$resultstockmovestaxes=DB_query($sqlstockmovestaxes,$db);
			while ($myrow3=DB_fetch_array($resultstockmovestaxes)){
				$impuesto=$myrow3['description'];
				$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
				$taxrate=FormatNumberERP($myrow3['taxrate']*100);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				if($TipoAgrupacionXTag==1){
					$taxratetotal=FormatNumberERP((($myrow3['taxrate']*($myrow2['subtotal']))*$myrow2['quantity']));
				}else{
					$taxratetotal=FormatNumberERP($myrow3['taxrate']*($myrow2['subtotal']));
				}
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
			}

		}
	}else{
		if($TipoAgrupacionXTag==1){
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
						stockcategory.MensajePV
				FROM stockmoves,stockmaster
					  left JOIN stockmovestaxes ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno ,
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
		}
		
		$resultdetails=DB_query($sqldetails,$db);
		while ($myrow2=DB_fetch_array($resultdetails)){
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['stockid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$stockcantidad=FormatNumberERP($myrow2['quantity']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
			$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT distinct p.InvoiceValue as val,sp.label
			     FROM salesstockproperties p,  stockcatproperties sp, stockmaster sm
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
				if ($xt==0){

					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				}else{
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
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
			$stockprecio=FormatNumberERP($myrow2['fxprice']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=FormatNumberERP($myrow2['fxnet']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=FormatNumberERP($myrow2['discountpercent']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=FormatNumberERP($myrow2['discountpercent1']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=FormatNumberERP($myrow2['discountpercent2']*100);
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
				$taxrate=FormatNumberERP($myrow3['taxrate']*100);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				$taxratetotal=FormatNumberERP($myrow3['taxrate']*($myrow2['subtotal']));
				//$taxtotalratex=$myrow3['taxrate']*($myrow2['fxnet']*$myrow2['fxprice']);
				$taxtotalratex=$myrow3['taxrate']*($myrow2['subtotal']);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
			}

			$subtotal=FormatNumberERP($myrow2['subtotal']+$taxtotalratex);
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			$subtotal=FormatNumberERP($myrow2['subtotal']+$taxtotalratex);
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

			if($myrow['type'] == 66) { // Si es factura remision

				$ordernotmp = $myrow['orderno'];

				$SQL = "
					SELECT GROUP_CONCAT(debtortrans.order_) AS order_
					FROM invoicetoremision
					INNER JOIN debtortrans
					ON debtortrans.id = invoicetoremision.idremision
					WHERE invoicetoremision.idinvoice = '{$myrow['iddocto']}'
				";

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
			$charelectronicdetail=$charelectronicdetail.'|'.$fechaaduana;

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
	}
	
	// ivas retenidos
//	$charelectronictaxsret='|'.chr(13).chr(10);//.'07|ISR|0.00';
	if ($rfccliente=="XAXX010101000"){
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsret.$charelectronictaxsfederal.$charelectronictaxslocal.$charelectronicEmbarque;
		$cadenaenviada=$cadenaenvio;//retornarString($cadenaenvio);
	}else{
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsfederal.$charelectronictaxs.$charelectronictaxslocal.$charelectronicEmbarque;
		$cadenaenviada=$cadenaenvio;//retornarString($cadenaenvio);

	}
	//echo $cadenaenviada;
	return $cadenaenviada;
}
//
//***************************************************************************************************************************
//********************************************* FIN DE FUNCION PARA FACTURACION *********************************************

//***************************************************************************************************************************
//********************************************* INICIO DE FUNCION PARA NOTAS CREDITO ****************************************
//***************************************************************************************************************************
// CON MOVIMIENTOS EN STOCKMOVES

function XSACreditNote($InvoiceNo, $orderno, $debtorno, $TypeInvoice,$tag,$serie,$folio, &$db)
{
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
				debtorsmaster.currcode,
				custbranch.defaultlocation,
				custbranch.brnumext,
				custbranch.brnumint,
				";
	$InvoiceHeaderSQL = $InvoiceHeaderSQL . "
				locations.taxprovinceid,
				debtortrans.tagref,
				custbranch.phoneno as telofi,
				debtortrans.paymentname,
			debtortrans.nocuenta
			FROM debtortrans INNER JOIN debtorsmaster ON
				debtortrans.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch ON
				debtortrans.branchcode = custbranch.branchcode
				AND debtortrans.debtorno = custbranch.debtorno
				INNER JOIN currencies ON
				debtorsmaster.currcode = currencies.currabrev
				INNER JOIN stockmoves ON
				stockmoves.transno=debtortrans.transno
				INNER JOIN locations ON
				stockmoves.loccode = locations.loccode
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
		$subtotal= FormatNumberERP($myrow['ovamount']);
		if (strlen($rfccliente)<12){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= FormatNumberERP($myrow['ovamount']+$myrow['ovgst']);
			$imprimepublico=1;
		}elseif(strlen($rfccliente)>=14){
			$rfccliente="XAXX010101000";
			$nombre="Publico en General";
			$subtotal= FormatNumberERP($myrow['ovamount']+$myrow['ovgst']);
			$imprimepublico=1;
		}
		$charelectronic=$charelectronic.'|'.$subtotal;
		// total factura
		$total=FormatNumberERP($myrow['ovamount']+$myrow['ovgst']);
		$charelectronic=$charelectronic.'|'.$total;
		// total de iva
		$iva=FormatNumberERP($myrow['ovgst']);
		// transladado
		$charelectronic=$charelectronic.'|'.$iva;
		// retenido
		$ivaret=0;
		$charelectronic=$charelectronic.'|'.$ivaret;
		//descuento
		$descuento=FormatNumberERP($myrow['ovdiscount']);
		$charelectronic=$charelectronic.'|'.$descuento;
		//motivo descuento
		$charelectronic=$charelectronic.'|';
		// tipo de moneda
		$moneda=$myrow['currcode'];
		// CANTIDAD CON LETRAS
		$totaletras=$myrow['ovamount']+$myrow['ovgst'];
		$totalx=str_replace(',' ,'', FormatNumberERP($totaletras));
		$separa=explode(".",$totalx);
		$montoletra = $separa[0];
		$separa2=explode(".", FormatNumberERP($total));
		$montoctvs2 = $separa2[1];
		$montoletra=Numbers_Words::toWords($montoletra, 'es');
		
		$zeroPad = "";
		if (empty($_SESSION['Decimals'])) {
			$zeroPad = str_pad($zeroPad, 4, 0, STR_PAD_RIGHT);
		} else {
			$zeroPad = str_pad($zeroPad, $_SESSION['Decimals'], 0, STR_PAD_RIGHT);
		}
		
		if ($moneda == 'MXN') {
			$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ."/1$zeroPad M.N.";
		} else {
			$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/1$zeroPad USD";
		}
		$charelectronic=$charelectronic.'|'.$montoletra;
		// tipo moneda
		$charelectronic=$charelectronic.'|'.$moneda;
		// tipo de cambio
		$rate=FormatRateNumberERP($myrow['cambio']);
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
			$metodopago = "No Identificado";
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
		
		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0){
			$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));//"Mexico";//$myrow['name'];
		}else{
			$pais="Mexico";
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
		
		if (strlen(str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi']))))))>0) {
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
			$stockcantidad=FormatNumberERP($myrow2['quantity']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
			$stockdescrip=$myrow2['description'];
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT distinct p.InvoiceValue as val,sp.label
			     FROM salesstockproperties p,  stockcatproperties sp, stockmaster sm
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
				if ($xt==0){

					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				}else{
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
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
			}
			if (strlen($descrnarrative)==0){
				$descrnarrative="";
			}

			$charelectronicdetail=$charelectronicdetail.'|'.$stockdescrip.$descrprop.$descrnarrative;
			$stockprecio=FormatNumberERP($myrow2['fxprice']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=FormatNumberERP($myrow2['fxnet']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=FormatNumberERP($myrow2['discountpercent']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=FormatNumberERP($myrow2['discountpercent1']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=FormatNumberERP($myrow2['discountpercent2']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount3;
			//subtotal
			$subtotal=FormatNumberERP($myrow2['subtotal']);
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
				$taxrate=FormatNumberERP($myrow3['taxrate']*100);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				$taxratetotal=FormatNumberERP($myrow3['taxrate']*($myrow2['subtotal']));
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
			$stockcantidad=FormatNumberERP($myrow2['quantity']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
			$stockdescrip=$myrow2['description'].' '.$myrow['ordertype'];
			// valores de los vendedorfees por linea
			$descrprop=" (";
			$sqlpropertyes="SELECT distinct p.InvoiceValue as val,sp.label
			     FROM salesstockproperties p,  stockcatproperties sp, stockmaster sm
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
				if ($xt==0){
						
					$descrprop=$descrprop.' '.$myrowprop['label'].':'.$trab;
				}else{
					$descrprop=$descrprop." , ".$myrowprop['label'].':'.$trab;
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
			$stockprecio=FormatNumberERP($myrow2['fxprice']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=FormatNumberERP($myrow2['fxnet']);
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits=$myrow2['units'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoryid'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra="";
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			// descuentos
			$discount1=FormatNumberERP($myrow2['discountpercent']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount1;
			$discount2=FormatNumberERP($myrow2['discountpercent1']*100);
			$charelectronicdetail=$charelectronicdetail.'|'.$discount2;
			$discount3=FormatNumberERP($myrow2['discountpercent2']*100);
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
				$taxrate=FormatNumberERP($myrow3['taxrate']*100);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
				$taxratetotal=FormatNumberERP($myrow3['taxrate']*($myrow2['subtotal']));
				//$taxtotalratex=$myrow3['taxrate']*($myrow2['fxnet']*$myrow2['fxprice']);
				$taxtotalratex=$myrow3['taxrate']*($myrow2['subtotal']);
				$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
			}
				
			$subtotal=FormatNumberERP($myrow2['subtotal']);
			$charelectronicdetail=$charelectronicdetail.'|'.$subtotal;
			$subtotal=FormatNumberERP($myrow2['subtotal']);
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
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxsret.$charelectronicEmbarque;
		$cadenaenviada=retornarString($cadenaenvio);
	}else{
		$cadenaenvio=$charelectronic.$charelectronicdetail.$charelectronictaxs.$charelectronictaxsret.$charelectronicEmbarque;
		$cadenaenviada=retornarString($cadenaenvio);

	}
	//echo '<pre><br>'.$cadenaenviada;
	return $cadenaenviada;
}

//***************************************************************************************************************************
//********************************************* INICIO DE FUNCION PARA NOTA DE CREDITO DIRECTAS******************************
//***************************************************************************************************************************

function XSAInvoicingCreditdirect($InvoiceNo, $orderno, $debtorno, $TypeInvoice,$tag,$serie,$folio, &$db)
{
	$charelectronic='01';
	$charelectronic=$charelectronic.'|'.$serie.$folio;
	$serieelect=$serie;
	$folioelect=$folio;
	$charelectronic=$charelectronic.'|'.$serieelect;
	$charelectronic=$charelectronic.'|'.$folioelect;
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
			debtortransmovs.reference,
			custbranch.phoneno as telofi,
			debtortrans.paymentname,
			debtortrans.nocuenta,
			debtortrans.observf
			FROM debtortransmovs
			INNER JOIN debtortrans ON debtortrans.type = debtortransmovs.type
			AND debtortrans.transno = debtortransmovs.transno
			,debtorsmaster,custbranch, www_users
			WHERE debtortransmovs.type=$TypeInvoice
			AND debtortransmovs.transno=$InvoiceNo
			AND debtortransmovs.tagref=$tag
			AND debtortransmovs.debtorno=debtorsmaster.debtorno
			AND debtortransmovs.debtorno=custbranch.debtorno
			AND debtortransmovs.branchcode=custbranch.branchcode
			AND www_users.userid = debtortransmovs.userid
			GROUP BY debtortransmovs.transno";
	
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
			$subtotal= FormatNumberERP($myrow['ovamount']);
			if ((strlen($rfccliente)<12) OR (strlen($rfccliente)>=14)){
				$rfccliente="XAXX010101000";
				$nombre="Publico en General";
				$subtotal= FormatNumberERP($myrow['ovamount']+$myrow['ovgst']);
				$imprimepublico=1;
			}
			$charelectronic=$charelectronic.'|'.$subtotal;
			// total factura
			$total=FormatNumberERP($myrow['ovamount']+$myrow['ovgst']);
			$charelectronic=$charelectronic.'|'.abs($total);
			// total de iva
			$iva=FormatNumberERP($myrow['ovgst']);
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
			$montoctvs2 = $separa[1];
			$montoctvs1 = $separa[0];
			if ($montoctvs2>995){
				$montoctvs1=$montoctvs1+1;
			}
			$montoletra=Numbers_Words::toWords($montoctvs1,'es');
			$totaletras=FormatNumberERP($totaletras);
			$separa=explode(".",$totaletras);
			$montoctvs2 = $separa[1];
			if ($montoctvs2>995){
				$montoctvs2=0;
			}
			$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
			
			$zeroPad = "";
			if (empty($_SESSION['Decimals'])) {
				$zeroPad = str_pad($zeroPad, 4, 0, STR_PAD_RIGHT);
			} else {
				$zeroPad = str_pad($zeroPad, $_SESSION['Decimals'], 0, STR_PAD_RIGHT);
			}
			
			if ($moneda == 'MXN') {
				$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ."/1$zeroPad M.N.";
			} else {
				$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/1$zeroPad USD";
			}
			$charelectronic=$charelectronic.'|'.$montoletra;
			// tipo moneda
			$charelectronic=$charelectronic.'|'.$moneda;
			// tipo de cambio
			$rate=FormatRateNumberERP($myrow['cambio']);
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

			$metodopago = $myrow['paymentname'];
			if ($metodopago=="") {
				$metodopago = "No Identificado";
			}

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
			if(strlen($myrow['observf'])>0){
				
				$terminospago=$myrow['observf'];
			}else{
				$terminospago="Pago en una sola exhibicion";
			}
			//echo 'terminos:'.$terminospago.' obs:'.$myrow['observf'].'<br><br>';
			$Tipopago=$Tipopago.' '.$terminospago;
			//$Tipopago=$terminospago;
			$charelectronic=$charelectronic.'|'.$Tipopago;
			// condiciones de pago
			$charelectronic=$charelectronic.'|---';
			// metodo de pago
			//$metodopago='Varios';
			$charelectronic=$charelectronic.'|'.$metodopago;
			// fecha vencimiento
			$fechavence=$myrow['trandate'];
			$charelectronic=$charelectronic.'|'.$fechavence;
			// observaciones 4
			$observaciones4=$nocuenta;
			$charelectronic=$charelectronic.'|'.$observaciones4;
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
			$municipio=$myrow['braddress2'];;
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
	
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr2'];//colonia
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr3'];//municipio
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr4'];//cp
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr5'];//estado
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr6'];//pais
	$charelectronicEmbarque=$charelectronicEmbarque.'|'.$myrow['brpostaddr7'];
	
	// cadena para datos de los productos
	// productos vendidos

	$charelectronicdetail='';
	$charelectronicinidet='|'.chr(13).chr(10).'05';
	// datos de ivas
	$charelectronictaxs='';
	$charelectronictaxsini='|'.chr(13).chr(10).'07';
	$stockid='Nota '.$myrow['transno'];
	$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
	$charelectronicdetail=$charelectronicdetail.'|'.$myrow['transno'];
	$cantidad = 1;
	$stockcantidad=FormatNumberERP($cantidad);
	$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
	$stockdescrip=$myrow['reference'];
	$charelectronicdetail=$charelectronicdetail.'|'.$stockdescrip;
	$stockprecio=FormatNumberERP(abs($myrow['ovamount']));
	$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
	$stockneto=FormatNumberERP(abs($myrow['ovamount']));
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
	$charelectronicdetail = $charelectronicdetail . '|' . FormatNumberERP(abs($myrow['ovamount']));
	//ADUANA
	$charelectronicdetail = $charelectronicdetail . '|' . '';
	//NUMERO DE ADUANA
	$charelectronicdetail = $charelectronicdetail . '|' . '';
	//FECHA DE INGRESO A ADUANA
	$charelectronicdetail = $charelectronicdetail . '|' . '';
	if ($imprimepublico==0){
		$impuesto="IVA";
		$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
		$totalcred=(abs($myrow['ovgst'])/abs($myrow['ovamount']));


		//$totalcred=$totalcred-1;
		$taxrate=FormatNumberERP($totalcred*100);
		$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
		$taxratetotal=FormatNumberERP(abs($myrow['ovgst']));
		$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
	}

	// ivas retenidos
	//$charelectronictaxsret='|'.chr(13).chr(10);//.'07|ISR|0.00';

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
