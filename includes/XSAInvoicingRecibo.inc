<?php 
function XSAInvoicingRecibo($InvoiceNo, $orderno, $debtorno, $TypeInvoice,$tag,$serie, $folio, &$db)
{
	$charelectronic='01';
	$charelectronic=$charelectronic.'|'.$serie.$folio;
	$serieelect=$serie;
	$folioelect=$folio;
	$charelectronic=$charelectronic.'|'.$serieelect;
	$charelectronic=$charelectronic.'|'.$folioelect;
	//consultar el paymentname y nocuneta del debtortrans
	$metodoPago = "No Identificado";
	$sql = "Select * from debtortrans
			where type=".$TypeInvoice."
			AND transno=" . $InvoiceNo . "";
	$rs = DB_query($sql,$db);
	if (DB_num_rows($rs)>0) {
		$reg = DB_fetch_array($rs);
		$metodoPago = $reg['paymentname'];
		if ($metodoPago=="")
			$metodoPago = "No Identificado";
			
		$nocuenta = $reg['nocuenta'];
	}

	// consulto datos de la factura
	$SQLInvoice = "SELECT
			replace(origtrandate,'-','/') as origtrandate,
			SUM(abs(debtortransmovs.ovamount)) AS ovamount,
			SUM(abs(debtortransmovs.ovgst)) AS ovgst,
			debtortransmovs.currcode,
			debtortrans.rate as cambio,
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
			www_users.realname,
			custbranch.brnumext, custbranch.brnumint
			FROM debtortransmovs,debtorsmaster,custbranch, www_users,
			(select rate from debtortrans where type=".$TypeInvoice." and transno = " . $InvoiceNo . " and tagref=" . $tag . ") as debtortrans
			WHERE debtortransmovs.type=".$TypeInvoice."
			AND debtortransmovs.transno=" . $InvoiceNo . "
			AND debtortransmovs.tagref=" . $tag . "
			AND debtortransmovs.debtorno=debtorsmaster.debtorno
			AND debtortransmovs.debtorno=custbranch.debtorno
			AND debtortransmovs.branchcode=custbranch.branchcode
			AND www_users.userid = debtortransmovs.userid
			AND debtortransmovs.reference <> '0'
			GROUP BY debtortransmovs.currcode,
			debtortransmovs.rate,
			debtortransmovs.order_,
			debtortransmovs.debtorno,
			custbranch.taxid,
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
			www_users.realname,
			custbranch.brnumext, custbranch.brnumint";
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
			$charelectronic=$charelectronic.'|'.$total;
			// total de iva
			$iva=number_format($myrow['ovgst'],2,'.','');
			// transladado
			$charelectronic=$charelectronic.'|'.$iva;
			// retenido
			$ivaret=0;
			$charelectronic=$charelectronic.'|'.$ivaret;
			//descuento
			$descuento=number_format(0,2,'.','');
			$charelectronic=$charelectronic.'|'.$descuento;
			//motivo descuento
			$charelectronic=$charelectronic.'|';
			// tipo de moneda
			$moneda=$myrow['currcode'];
			// CANTIDAD CON LETRAS
			$totaletras=$myrow['ovamount']+$myrow['ovgst'];
			$separa=explode(".",$totaletras);
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
			$observaciones3='Id Recibo:'.$InvoiceNo;//' '.$comments .' usr:'.$UserRegister;
				
				
			$TypeInvoice."
			AND debtortransmovs.transno=" . $InvoiceNo . "
			AND debtortransmovs.tagref=" . $tag .
				
			/*
			 $XSQL = "SELECT *
			FROM gltrans
			WHERE gltrans.tag = " . $tag . "
			and gltrans.type = " . $TypeInvoice . "
			and gltrans.typeno = " . $InvoiceNo;
			*/
				
			$XSQL = "SELECT gltrans.*
			FROM gltrans, chartmaster, accountgroups,companies
			WHERE gltrans.tag = " . $tag . "
			and gltrans.type = " . $TypeInvoice . "
			and gltrans.typeno = " . $InvoiceNo . "
			and gltrans.amount > 0
			and gltrans.account = chartmaster.accountcode
			and chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
			and (chartmaster.accountcode = companies.gltempcashpayment or
				chartmaster.accountcode = companies.gltempcheckpayment or
				chartmaster.accountcode = companies.gltempccpayment or
			chartmaster.accountcode = companies.gltemptransferpayment)";

				
			$resultxsql=DB_query($XSQL,$db);
			$detallerecibos = "";
			$x = 1;
			while ($xmyrow = DB_fetch_array($resultxsql)){
				$x = $x + 1;
				$detallerecibos = $detallerecibos . " - " . $xmyrow['account'] . "-> " . abs($xmyrow['amount']);
			}
				
			$observaciones3 =  $observaciones3 . $detallerecibos . "; " . "Este comprobante es complementario a los expedidos en la fecha y folios descritos en cada partida detallada arriba";
				
			$charelectronic=$charelectronic.'|'.$observaciones3 . $detallerecibos;
				
			//SE AGREGA NUEVO CAMPO INFORMACION EXTRA
			$charelectronic=$charelectronic . '|' . '';
				
			// datos de la forma de pago
			$charelectronic=$charelectronic.'|'.chr(13).chr(10).'02';
			//$terminospago="Una Exhibicion";
			$Tipopago="Pago en una sola exhibicion";

			//$Tipopago=$Tipopago.' '.$terminospago;
			//$Tipopago=$terminospago;
			$charelectronic=$charelectronic.'|'.$Tipopago;
			// condiciones de pago
			$charelectronic=$charelectronic.'|---';
			// metodo de pago
			$metodopago='No Identificado';
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
			$pais="Mexico";//$myrow['name'];
			$charelectronic=$charelectronic.'|'.$pais;
				
		}
		{
			$calle="";
			if ($imprimepublico==0){
				$calle=$myrow['address1'];
			}
			$charelectronic=$charelectronic.'|'.$calle;
			//$noext="";
			$noext=$myrow['brnumext'];
			$charelectronic=$charelectronic.'|'.$noext;
			//$noint="";
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
			$pais="Mexico";
			$charelectronic=$charelectronic.'|'.$pais;
			$calle=$myrow['braddress1'];
			$charelectronic=$charelectronic.'|'.$calle;
			//$noext="";
			$noext=$myrow['brnumext'];
			$charelectronic=$charelectronic.'|'.$noext;
			//$noint="";
			$noint=$myrow['brnumint'];
			$charelectronic=$charelectronic.'|'.$noint;
			$colonia=$myrow['braddress2'];
			$charelectronic=$charelectronic.'|'.$colonia;
			$localidad="";
			$charelectronic=$charelectronic.'|'.$localidad;
			$referenciacalle="";
			$charelectronic=$charelectronic.'|'.$referenciacalle;
			$municipio=$myrow['braddress3'];;
			$charelectronic=$charelectronic.'|'.$municipio;
			$edo=$myrow['braddress4'];
			$charelectronic=$charelectronic.'|'.$edo;
			$cp=$myrow['braddress5'];
			$charelectronic=$charelectronic.'|'.$cp;

		}


	}
	// cadena para datos de los productos
	// productos vendidos

	$charelectronicdetail='';
	$charelectronicinidet='|'.chr(13).chr(10).'05';
	// datos de ivas
	$charelectronictaxs='';
	$charelectronictaxsini='|'.chr(13).chr(10).'06';

	/*
	 $sqldetails = "SELECT debtortransmovs.id,
	debtortransmovs.reference as identificador,
	1 as cantidad,
	case when debtortransmovs.reference in ('80','90','21')
	then debtortransmovs.invtext
	else concat(debtortransmovs.invtext, ' (Complementario al Folio: ', replace(IFNULL(debtortrans.folio,''),'|','-'), ' con fecha: ') end  as descripcion,
			abs(debtortransmovs.ovamount) as valorunitario,
			abs(debtortransmovs.ovamount) as importe,
			'unidad' as unidademedida,
			systypescat.typename as categoria,
			debtortransmovs.order_ as ordencompra,
			abs(debtortransmovs.ovgst) as iva,
			DAY(debtortrans.origtrandate) AS diaorigtrandate,
			MONTH(debtortrans.origtrandate) AS mesorigtrandate,
			YEAR(debtortrans.origtrandate) AS anioorigtrandate,
			debtortransmovs.invtext as descripcion2
			FROM debtortransmovs, systypescat, debtortrans
			WHERE debtortransmovs.type=".$TypeInvoice."
			AND debtortransmovs.transno=" . $InvoiceNo . "
			AND debtortransmovs.tagref=" . $tag . "
			AND (
					(SUBSTRING(debtortransmovs.reference, 1, LOCATE(' - ', debtortransmovs.reference)-1) = systypescat.typeid
							AND SUBSTRING(debtortransmovs.reference, 1, LOCATE(' - ', debtortransmovs.reference)-1) = debtortrans.type
							AND SUBSTRING(debtortransmovs.reference, LOCATE(' - ', debtortransmovs.reference)+3) = debtortrans.transno)
					OR
					(debtortransmovs.reference = systypescat.typeid
							AND debtortransmovs.reference = debtortrans.type
							AND debtortransmovs.transno = debtortrans.transno
							AND debtortransmovs.reference in (80,90,21))
			)";
			*/



	$sqldetails = "SELECT d1.id,
			CONCAT(d2.type, ' -  ', d2.transno) as identificador,
			1 as cantidad,
			case when d2.type in ('80','90','21')
				then d1.invtext
				else concat('AFECTO DOCUMENTO ', d2.transno, ' (Complementario al Folio: ', replace(IFNULL(d3.folio,''),'|','-'), ' con fecha: ') end  as descripcion,
		
			c.amt / (1+ ((d2.ovgst*1)/d2.ovamount)) as valorunitario,
			c.amt / (1+ ((d2.ovgst*1)/d2.ovamount)) as importe,

			'unidad' as unidademedida,
			t.typename as categoria,
			d2.order_  as ordencompra,
			c.amt - (c.amt / (1+ ((d2.ovgst*1)/d2.ovamount))) as iva,
			DAY(d2.origtrandate) AS diaorigtrandate,
			MONTH(d2.origtrandate) AS mesorigtrandate,
			YEAR(d2.origtrandate) AS anioorigtrandate,
			d2.invtext as descripcion2
			FROM debtortrans d1
				left join custallocns c ON d1.id= c.transid_allocfrom
				left join debtortrans d2 ON c.transid_allocto  = d2.id
				left join debtortrans d3 ON d2.order_  = d3.transno and d3.type=10
				left join systypescat t ON d2.type = t.typeid
			WHERE d1.type=" .$TypeInvoice."
			AND d1.transno=" . $InvoiceNo;
		
		
	//echo 	"<br>" . $sqldetails;
	$resultdetails=DB_query($sqldetails,$db);
	while ($myrow2=DB_fetch_array($resultdetails)){
		
// 		$sql= "Select folio
// 				From debtortrans
// 				Where transno=".$myrow2["ordencompra"]." and type=10";
		
// 		$resultfolio= DB_query($sql,$db);
// 		$registro= DB_fetch_array($resultfolio);
// 		$folio_ordencompra=  $registro["folio"];
		
		if ($imprimepublico == 0){
			$stockid=$myrow2['id'];
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['identificador'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$cantidad = $myrow2['cantidad'];
			$stockcantidad=number_format($cantidad,4,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
				
			if (!is_null($myrow2['descripcion'])){
				$diaorigtrandate = $myrow2['diaorigtrandate'];
				$mesorigtrandate = $myrow2['mesorigtrandate'];
				$anioorigtrandate = $myrow2['anioorigtrandate'];
				$stockdescrip=$myrow2['descripcion'] . $diaorigtrandate . "-" . nombremescorto($mesorigtrandate) . "-" . $anioorigtrandate . ")";
			}else{
				$stockdescrip = $myrow2['descripcion2'];
			}
				
			$charelectronicdetail=$charelectronicdetail.'|'.$stockdescrip;
			$stockprecio=number_format(abs($myrow2['valorunitario']),2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=number_format(abs($myrow2['importe']),2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits= $myrow2['unidademedida'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoria'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra=$myrow2['ordencompra'];
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
			//DESCUENTO 1
			$charelectronicdetail = $charelectronicdetail . '|' . 0;
			//DESCUENTO 2
			$charelectronicdetail = $charelectronicdetail . '|' . 0;
			//DESCUENTO 3
			$charelectronicdetail = $charelectronicdetail . '|' . 0;
			//SUBTOTAL
			$charelectronicdetail = $charelectronicdetail . '|' . number_format(abs($myrow2['importe']),2,'.','');
			//ADUANA
			$charelectronicdetail = $charelectronicdetail . '|' . '';
			//NUMERO DE ADUANA
			$charelectronicdetail = $charelectronicdetail . '|' . '';
			//FECHA DE INGRESO A ADUANA
			$charelectronicdetail = $charelectronicdetail . '|' . '';
			$impuesto="IVA";
			$charelectronictaxs=$charelectronictaxs.$charelectronictaxsini.'|'.$impuesto;
			//$taxrate=number_format(.15*100,2,'.','');
			$taxrate = number_format((((abs($myrow2['iva'])+ abs($myrow2['importe'])) / abs($myrow2['importe']))-1),2);
			$taxrate = $taxrate * 100;
			$charelectronictaxs=$charelectronictaxs.'|'.$taxrate;
			$taxratetotal=number_format(abs($myrow2['iva']),2,'.','');
			$charelectronictaxs=$charelectronictaxs.'|'.$taxratetotal;
		}else{
			$stockid=$myrow2['id'];
			$charelectronicdetail=$charelectronicdetail.$charelectronicinidet.'|'.$stockid;
			$stockid=$myrow2['identificador'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockid;
			$cantidad = $myrow2['cantidad'];
			$stockcantidad=number_format($cantidad,4,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcantidad;
				
			if (!is_null($myrow2['descripcion'])){
				$diaorigtrandate = $myrow2['diaorigtrandate'];
				$mesorigtrandate = $myrow2['mesorigtrandate'];
				$anioorigtrandate = $myrow2['anioorigtrandate'];

				$stockdescrip=$myrow2['descripcion'] . $diaorigtrandate . "-" . nombremescorto($mesorigtrandate) . "-" . $anioorigtrandate . ")";
			}else{
				$stockdescrip = $myrow2['descripcion2'];
			}
				
				
			$charelectronicdetail=$charelectronicdetail.'|'.$stockdescrip;
			$stockprecio=number_format(abs($myrow2['valorunitario'] + $myrow2['iva']),2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockprecio;
			$stockneto=number_format(($cantidad * abs($myrow2['valorunitario'] + $myrow2['iva'])),2,'.','');
			$charelectronicdetail=$charelectronicdetail.'|'.$stockneto;
			$stockunits= $myrow2['unidademedida'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockunits;
			$stockcat=$myrow2['categoria'];
			$charelectronicdetail=$charelectronicdetail.'|'.$stockcat;
			$ordencompra=$myrow2['ordencompra'];
			$charelectronicdetail=$charelectronicdetail.'|'.$ordencompra;
		}



	}
	// ivas retenidos
	$charelectronictaxsret='|'.chr(13).chr(10);//.'07|ISR|0.00';
	if ($charelectronictaxs != ""){
		return $charelectronic.$charelectronicdetail.$charelectronictaxs.$charelectronictaxsret;
	}else{
		return $charelectronic.$charelectronicdetail.$charelectronictaxsret;
	}

}

function nombremescorto($idmes){
	$nombremescorto = "";
	switch ($idmes) {
		case 1:
			$nombremescorto = "ENE";
			break;
		case 2:
			$nombremescorto = "FEB";
			break;
		case 3:
			$nombremescorto = "MAR";
			break;
		case 4:
			$nombremescorto = "ABR";
			break;
		case 5:
			$nombremescorto = "MAY";
			break;
		case 6:
			$nombremescorto = "JUN";
			break;
		case 7:
			$nombremescorto = "JUL";
			break;
		case 8:
			$nombremescorto = "AGO";
			break;
		case 9:
			$nombremescorto = "SEP";
			break;
		case 10:
			$nombremescorto = "OCT";
			break;
		case 11:
			$nombremescorto = "NOV";
			break;
		case 12:
			$nombremescorto = "DIC";
			break;

	}
	return $nombremescorto;
}
?>