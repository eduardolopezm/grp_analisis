<?php

if (isset($_GET['verplaca'])){
	$verplaca=$_GET['verplaca'];
}

if (isset($_GET['verserie'])){
	$verserie=$_GET['verserie'];
}

if (isset($_GET['verkilometraje'])){
	$verkilometraje=$_GET['verkilometraje'];
}

if (isset($_GET['vercomentarios'])){
	$vercomentarios=$_GET['vercomentarios'];
}


If (!isset($_GET['TransNo']) OR $_GET['TransNo']==""){
        $title = _('Impresion de Orden');
        include('includes/header.inc');
        echo '<div class=centre><br><br><br>';
		prnMsg( _('Seleccione un numero de orden') , 'error');
		echo '<br><br><br>
			<table class="table_index">
				<tr>
					<td class="menu_group_item">
						<li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Ordenes de venta pendientes') . '</a></li>
					</td>
				</tr>
			</table>
	      </div><br><br><br>';
        include('includes/footer.inc');
        exit();
}
$PaperSize = 'letter';
include('includes/PDFStarter.php');

//aqui empieza la consulta que arroja los resultados de la orden elegida
for ($i=1;$i<=1;$i++){//este for imprime dos veces una para la oficina y la otra para el cliente
		if ($i==2){
		$pdf->newPage();
		}
		$line_height=18;
		$PageNumber = 1;
		$sql="SELECT salesorderdetails.unitprice as precio,
		     (salesorderdetails.quantity*salesorderdetails.unitprice) as importe,
		     salesorderdetails.stkcode as codigo,
		     salesorderdetails.quantity as cantidad,
		     salesorderdetails.discountpercent as descu,
		     salesorderdetails.discountpercent1 as desc1,
		     salesorderdetails.discountpercent2 as desc2,
		     stockmaster.description as descripcion,
		     debtortrans.folio as folio,
			 debtortrans.paymentname,
			 debtortrans.nocuenta,
		     salesorders.customerref,
		     salesorders.confirmeddate,
		     salesorders.deliverydate,
		     trim(substring(salesorders.comments,1,LOCATE('Inv', salesorders.comments)-1)) as comenrenglon,
		     salesorders.quotation as title,
		     salesorders.deliverto as nombrefrom,
		     salesorders.deladd1 as dirfrom1,
		     salesorders.deladd2 as dirfrom2,
		     salesorders.deladd3 as dirfrom3,
		     salesorders.deladd4 as dirfrom4,
		     salesorders.deladd5 as dirfrom5,
		     salesorders.contactphone as tel,
		     salesorders.confirmeddate as fecha,
		     salesorders.taxtotal as iva,
		     salesorders.UserRegister as userr,
		     salesorders.paytermsindicator as termino,
		     salesorders.placa as placas,
		     salesorders.serie as serie,
		     salesorders.kilometraje as kilometraje,
		     salesman.salesmanname as vendedor,
		     debtorsmaster.debtorno as cliente,
		     debtorsmaster.name as nombre,
		     debtorsmaster.address1 as dir1,
		     debtorsmaster.address2 as dir2,
		     debtorsmaster.address3 as dir3,
		     debtorsmaster.address4 as dir4,
		     debtorsmaster.address5 as dir5,
		     '' as canletra,
		     debtortrans.currcode as moneda,
		     debtortrans.rate as tipocambio,
		     legalbusinessunit.comments as bancos,
		     custbranch.brname,
		     custbranch.taxid as RFC,debtortrans.ref1 as ref1,
		     salesorderdetails.narrative as comentarios,
		     concat(stockmaster.longdescription,'|', debtortrans.transno ,'|', debtortrans.type) as descripcionlarga,
		     salesorders.comments as comentariosrenglon,
		     salesorders.orderno as orderno  ,
		     (taxrate*100) as poriva,debtortrans.sello as sello,debtortrans.cadena as cadena ,
		     debtortrans.origtrandate as fechafact,
		     a.NoCertificado as certificado,
		     legalbusinessunit.legalname,
			 legalbusinessunit.regimenfiscal,
		     legalbusinessunit.address1 AS calleempre,
		     legalbusinessunit.address2 as coloniaempre,
		     legalbusinessunit.address3 as Noextempre,
		     legalbusinessunit.address4 as cdempre,
		     legalbusinessunit.address5 as edoempre,
		     legalbusinessunit.telephone as phoneempre,
		     legalbusinessunit.fax as faxempre,
		     legalbusinessunit.email as emailempre,
		     legalbusinessunit.taxid as rfcempre,
			 legalbusinessunit.cedula,
		     tags.address1 AS calleexpe,
		     tags.address2 as Noextexpe,
		     tags.address3 as coloniaexpe,
		     tags.address4 as cdexpe,
		     tags.address5 as edoexpe,
		     stockmaster.decimalplaces,
		     concat(a.Noaprobacion,'/',a.Anioaprobacion) as numdigital,
		     salesorders.ordertype,
		     salesorderdetails.orderlineno,
		     locations.locationname,
		     taxauthrates.taxrate,
		     (salesorderdetails.quantity*salesorderdetails.unitprice)-(((salesorderdetails.quantity*salesorderdetails.unitprice)*(1-salesorderdetails.discountpercent))*(1-salesorderdetails.discountpercent1))*(1-salesorderdetails.discountpercent2) as totaldesc
	      FROM salesorderdetails INNER JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
			INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman
			INNER JOIN locations ON locations.loccode = salesorderdetails.fromstkloc
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salesorders.debtorno
			INNER JOIN custbranch ON custbranch.branchcode = salesorders.branchcode
			INNER JOIN salestypes ON salestypes.typeabbrev = salesorders.ordertype
			INNER JOIN debtortrans ON debtortrans.order_=salesorders.orderno
			INNER JOIN tags ON debtortrans.tagref=tags.tagref
			INNER JOIN areas a ON tags.areacode=a.areacode
			INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			INNER JOIN taxauthrates ON stockmaster.taxcatid=taxauthrates.taxcatid
		WHERE salesorderdetails.stkcode = stockmaster.stockid
			AND salesorders.orderno = salesorderdetails.orderno
			AND salesman.salesmancode = salesorders.salesman
			AND debtorsmaster.debtorno = salesorders.debtorno
			AND custbranch.branchcode = salesorders.branchcode
			AND salestypes.typeabbrev = salesorders.ordertype
			AND salesorders.tagref=debtortrans.tagref
			AND salesorderdetails.orderno =".$_GET['OrderNo']."
			AND debtortrans.type =".$_GET['Type']."
			AND debtortrans.transno =".$_GET['TransNo']."
		ORDER BY salesorderdetails.orderlineno";
		
		
		$sql=" SELECT stockmoves.price as precio,
		     (stockmoves.qty*stockmoves.price)*-1 as importe2,
		     (((stockmoves.qty*-1)*stockmoves.price)+(((stockmoves.qty*-1)*stockmoves.price)*taxrate)) as importe,
		     stockmoves.stockid as codigo,
		     stockmoves.qty*-1 as cantidad,
		     stockmoves.discountpercent as descu,
		     stockmoves.discountpercent1 as desc1,
		     stockmoves.discountpercent2 as desc2,
		     stockmaster.description as descripcion,
		     debtortrans.folio as folio,
			 debtortrans.paymentname,
			 debtortrans.nocuenta,
			 debtortrans.tagref as tagbank,
		     salesorders.customerref,
		     salesorders.confirmeddate,
		     salesorders.deliverydate,
		     /*salesorders.comments as comenrenglon,*/
		     trim(substring(salesorders.comments,1,LOCATE('Inv', salesorders.comments)-1))  as comenrenglon,
		     salesorders.quotation as title,
		     salesorders.deliverto as nombrefrom,
		     custbranch.braddress1 as dirfrom1,
		     custbranch.braddress2 as dirfrom2,
		     custbranch.braddress3 as dirfrom3,
		     custbranch.braddress4  as dirfrom4,
		     custbranch.braddress5  as dirfrom5,
				  custbranch.braddress6  as dirfrom6,
		     salesorders.contactphone as tel,
		     salesorders.confirmeddate as fecha,
		     debtortrans.ovgst as iva,
		     salesorders.UserRegister as userr,
		     salesorders.paytermsindicator as termino,
		     salesorders.placa as placas,
		     salesorders.serie as serie,
		     salesorders.kilometraje as kilometraje,
		     salesman.salesmanname as vendedor,
		     debtorsmaster.debtorno as cliente,
		     debtorsmaster.name as nombre,
		     debtorsmaster.address1 as dir1,
		     debtorsmaster.address2 as dir2,
		     debtorsmaster.address3 as dir3,
		     debtorsmaster.address4 as dir4,
		     debtorsmaster.address5 as dir5,
		     '' as canletra,
		     debtortrans.currcode as moneda,
		     debtortrans.rate as tipocambio,
		     legalbusinessunit.comments as bancos,
		     custbranch.brname,
		     custbranch.taxid as RFC,debtortrans.ref1 as ref1,
		     stockmoves.narrative as infext,
		     concat(stockmaster.longdescription,'|', debtortrans.transno ,'|', debtortrans.type) as descripcionlarga,
		     salesorders.comments as comentariosrenglon,
		     salesorders.orderno as orderno  ,
		     (taxrate) as poriva,debtortrans.sello as sello,debtortrans.cadena as cadena ,
		     debtortrans.origtrandate as fechafact,
		      debtortrans.trandate as fechavencimiento,
		     a.NoCertificado as certificado,
		     legalbusinessunit.legalname,
			 legalbusinessunit.logo,
			 legalbusinessunit.regimenfiscal,
		     legalbusinessunit.address1 AS calleempre,
		     legalbusinessunit.address2 as coloniaempre,
		     legalbusinessunit.address3 as Noextempre,
		     legalbusinessunit.address4 as cdempre,
		     legalbusinessunit.address5 as edoempre,
		     legalbusinessunit.telephone as phoneempre,
		     legalbusinessunit.fax as faxempre,
		     legalbusinessunit.email as emailempre,
		     legalbusinessunit.taxid as rfcempre,
		     tags.address1 AS calleexpe,
		     tags.address2 as Noextexpe,
		     tags.address3 as coloniaexpe,
		     tags.address4 as cdexpe,
		     tags.address5 as edoexpe,
		     concat(a.Noaprobacion,'/',a.Anioaprobacion) as numdigital,
		     salesorders.ordertype,
		     stockmoves.stkmoveno as orderlineno,
		     locations.locationname,
		     stockmaster.units,
		     (stockmoves.qty*stockmoves.price)-(((stockmoves.qty*stockmoves.price)*(1-stockmoves.discountpercent))*(1-stockmoves.discountpercent1))*(1-stockmoves.discountpercent2) as totaldesc
	      FROM stockmoves INNER JOIN stockmaster ON stockmoves.stockid = stockmaster.stockid
			INNER JOIN locations ON locations.loccode = stockmoves.loccode
			INNER JOIN debtortrans ON stockmoves.type=debtortrans.type and stockmoves.transno=debtortrans.transno
			INNER JOIN salesorders ON salesorders.orderno = debtortrans.order_
			INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salesorders.debtorno
			INNER JOIN custbranch ON custbranch.branchcode = salesorders.branchcode
			INNER JOIN salestypes ON salestypes.typeabbrev = salesorders.ordertype
			INNER JOIN tags ON debtortrans.tagref=tags.tagref
			INNER JOIN areas a ON tags.areacode=a.areacode
			INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			INNER JOIN taxauthrates ON stockmaster.taxcatid=taxauthrates.taxcatid
		WHERE stockmoves.stockid = stockmaster.stockid
			AND salesorders.orderno = debtortrans.order_
			AND salesman.salesmancode = salesorders.salesman
			AND debtorsmaster.debtorno = salesorders.debtorno
			AND custbranch.branchcode = salesorders.branchcode
			AND salestypes.typeabbrev = salesorders.ordertype
			AND salesorders.tagref=debtortrans.tagref AND salesorders.orderno =".$_GET['OrderNo']."
			AND debtortrans.type =".$_GET['Type']."
			AND debtortrans.transno =".$_GET['TransNo']."
		ORDER BY stockmoves.stkmoveno ";
		
	$result=DB_query($sql,$db, $ErrMsg);
	//echo $sql;
	if (DB_num_rows($result)==0){
		$title = _('Print Packing Slip Error');
		include('includes/header.inc');
		echo '<div class=centre><br><br><br>';
			prnMsg( _('No se encuentra la orden') . ' : ' . $_GET['OrderNo'] . ' ', 'error');
			echo '<br><br><br>
			<table class="table_index">
				<tr>
					<td class="menu_group_item">
						<LI><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'">' . _('Ordenes Pendientes') . '</a></LI>
					</td>
				</tr>
			</table>
		      </div><br><br><br>';
		include('includes/footer.inc');
		exit();
	} elseif (DB_num_rows($result)>0) { 
		$FontSize=12;
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addinfo('Title', _('Impresion de Orden') );
		$pdf->addinfo('Subject', _('Lista de orden') . ' ' . $_GET['TransNo']);
	
			$ErrMsg = _('Ha habido un problema al intentar recuperar los detalles de la orden') . ' ' .
				$_GET['TransNo'] . ' ' . _('de la base de datos');
			$x=0;
			if (DB_num_rows($result)>0){
				$myrow3=DB_fetch_array($result);
				$result=DB_query($sql,$db, $ErrMsg);
				$FirstPage=1;
				include('PDFInvoiceHeaderPlacacentro.inc');
				
				$count = 0;
				while ($myrow=DB_fetch_array($result)){
					$sello=$myrow['sello'];
					$cadena=$myrow['cadena'];
					$FontSize=8;
					
					$x=$x+1;
					$posyven=330;
					
					  
					$precio = number_format($myrow['precio'],2);
					$iva = number_format($myrow['iva'],2);
					$descu = number_format($myrow['descu']*100);
					$desc1 = number_format($myrow['desc1']*100);
					$desc2 = number_format($myrow['desc2']*100);
					$importe=number_format(($myrow['cantidad']*$myrow['precio']),2);
					$importex=($myrow['cantidad']*$myrow['precio'])-($myrow['totaldesc']*-1);
					$total=number_format($importex+($importex*$myrow['taxrate']),2);
					$FontSize=7;
					$pdf->addTextWrap(40,$YPos-78,50,$FontSize,number_format($myrow['cantidad'],$myrow['decimalplaces']));
					$pdf->addTextWrap(360,$YPos-78,90,$FontSize,'$ '.$precio,'right');
					$pdf->addTextWrap(350,$YPos-78,20,$FontSize,$myrow['units']);
					if ($_SESSION['ShowPriceList']==1){
						$pdf->addTextWrap(400,$YPos-78,20,$FontSize,$myrow['ordertype']);
					}
					$pdf->addTextWrap(435,$YPos-78,45,$FontSize,$descu.'%','right');
					if ($_SESSION['Showdiscount1']==1){
						$pdf->addTextWrap(375,$YPos-78,45,$FontSize,$desc1.'%','right');
					}
					if ($_SESSION['Showdiscount2']==1){
						$pdf->addTextWrap(510,$YPos-78,45,$FontSize,$desc2.'%','right');
					}
					//$pdf->addTextWrap(430,$YPos-78,90,$FontSize,'$ '.$importe,'right');
					$pdf->addTextWrap(465,$YPos-78,90,$FontSize,'$ '.$total,'right');
					
					$count ++;
					$daco=$myrow['codigo'];
					$LeftOversdc = $pdf->addTextWrap(70,$YPos-80,40,$FontSize,$daco);
					while (strlen($LeftOversdc) > 1 ) {
						//
						$YPos -= 1*$line_height*0.40;
						//
						$LeftOversdc = $pdf->addTextWrap(30,$YPos-80,40,$FontSize,$LeftOversdc);
					}
					
					
					
					$datos='';
					/*
					$coments='sssssss'.trim($myrow['comentarios']);
					$LeftOvers = $pdf->addTextWrap(120,$YPos-90,150,$FontSize,trim($coments));
					while (strlen($LeftOvers) > 1 ) {
						$YPos -= 1*$line_height*0.60;
						$LeftOvers = $pdf->addTextWrap(120,$YPos-90,150,$FontSize,$LeftOvers);
						//$YPos -=1*$line_height*0.60;
					}*/
					//$YPos -= 10;
					$coments='';
					
					if (strlen(trim($myrow['infext'])) >0) {
						//$YPos -= 10;
						//$LeftOvers = $pdf->addTextWrap(120,$YPos-80,180,$FontSize, $myrow['infext']);
					}
					if ($_SESSION['ShowWorkers']==1){
						$sqli="select stockcatproperties.label,salesstockproperties.valor 
							from salesstockproperties,stockcatproperties 
							where salesstockproperties.stkcatpropid=stockcatproperties.stkcatpropid
							and  salesstockproperties.orderno=".$_GET['OrderNo']."
							and orderlineno=".$myrow['orderlineno'].
							" and salesstockproperties.typedocument=".$_GET['Type'];
						$resulti=DB_query($sqli,$db, $ErrMsg);
						
						//$datos = '';
						if (DB_num_rows($resulti)>0) {
							while ($myrowi=DB_fetch_array($resulti)){
								if (strlen(trim($myrowi['valor'])) > 0) {
									$datos.=' '.$myrowi['label'].' : '.$myrowi['valor'];
								}
							}
						}
					}
					// Alamcen de venta
					
					
					if (strlen($datos) > 0) {
						//$YPos -= 10;
						$LeftOvers = $pdf->addTextWrap(240,$YPos-80,70,$FontSize,$datos);
						while (strlen($LeftOvers) > 1 ) {
							$YPos -=1*$line_height*0.60;
							$LeftOvers = $pdf->addTextWrap(150,$YPos-80,100,$FontSize,$LeftOvers);
						}
					}
					
					$datos=trim($myrow['descripcion']).'                                         '.trim($myrow['infext']);
					//$datos.=' Almacen: '.$myrow['locationname'];
					$LeftOvers = $pdf->addTextWrap(120,$YPos-80,200,$FontSize,trim($datos));//
					while (strlen($LeftOvers) > 1 ) {
						$YPos -= 1*$line_height*0.40;
						$LeftOvers = $pdf->addTextWrap(120,$YPos-80,200,$FontSize,$LeftOvers);
					}
					
					if ($count >= 18){
						// We reached the end of the page so finsih off the page and start a newy 
							$PageNumber++;
							$pdf->newPage();
							include('PDFInvoiceHeaderPlacacentro.inc');
							$count = 0;
					}/*else {
							//increment a line down for the next line item 
							$YPos -=1*$line_height*0.60;
					}*/
					$YPos -=1*$line_height*0.60;
					
				}//fin del while del result
				//$pdf->addTextWrap(170,530,225,$FontSize,_('Descripcion de ').$title,'right');
				$FontSize=10;
				$YPos=100;
				//$pdf->addTextWrap(20,$YPos,900,$FontSize, _('Las Condiciones de Precio Pueden Variar Sin Previo Aviso...'));
			}//fin del if extractor de datos de result
			
			//$pdf->addTextWrap($xpos+180,115,600,$FontSize,$sello,'left');
			$FontSize=7;
			$pdf->addTextWrap($xpos+405,262,170,8,'$ '.$iva,'right');
			$YPos=$YPos+65;
			$FontSize=6;
			$LeftOvers = $pdf->addTextWrap($xpos+40,$YPos,520,$FontSize,$sello);
			while (strlen($LeftOvers) > 1 ) {
				//
				$YPos -= 1*$line_height*0.60;
				//
				//$LeftOvers =pdf->addTextWrap(120,$YPos-80,500,$FontSize,$LeftOvers);
				
				$LeftOvers=$pdf->addTextWrap($xpos+40,$YPos,520,$FontSize,$LeftOvers);
			}
			$YPos=$YPos-20;
			$FontSize=5;
			$LeftOvers = $pdf->addTextWrap($xpos+40,$YPos,520,$FontSize,$xpos.$cadena);
			while (strlen($LeftOvers) > 1 ) {
				//
				$YPos -= 1*$line_height*0.40;
				//
				//$LeftOvers =pdf->addTextWrap(120,$YPos-80,500,$FontSize,$LeftOvers);
				$FontSize=5;
				$LeftOvers=$pdf->addTextWrap($xpos+40,$YPos,520,$FontSize,$LeftOvers);
			}
			$FontSize=8;
			
			//aqui empieza la consulta para sacar el subtotal y el total
			$sql2="select salesorders.currcode,
				(sum((salesorderdetails.quantity*salesorderdetails.unitprice)-((salesorderdetails.quantity*salesorderdetails.unitprice)-(((salesorderdetails.quantity*salesorderdetails.unitprice)*(1-salesorderdetails.discountpercent))*(1-salesorderdetails.discountpercent1))*(1-salesorderdetails.discountpercent2))))as subtotal,
				(sum(((salesorderdetails.unitprice*salesorderdetails.quantity)*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal) AS total
			from salesorderdetails,
				salesorders,debtorsmaster
			where salesorders.orderno = salesorderdetails.orderno AND debtorsmaster.debtorno = salesorders.debtorno
				AND salesorders.orderno=" .$_GET['OrderNo'] ." GROUP by currcode ";
				
				
			$sql2="SELECT debtortrans.currcode,ovamount as subtotal ,ovgst+ovamount as total
				FROM debtortrans
				WHERE type=".$_GET['Type']." and transno=".$_GET['TransNo'];
				
			$result2=DB_query($sql2,$db, $ErrMsg);
			if (DB_num_rows($result2)>0){
				while ($myrow2=DB_fetch_array($result2)){
					$totaletras=abs($myrow2['total']);
					$separa=explode(".",$totaletras);
					$montoctvs2 = $separa[1];
					$montoctvs1 = $separa[0];
					//$montoctvs1x= $separa[1];
					if (left($montoctvs2,3)>995){
						$montoctvs1=$montoctvs1+1;
						//$montoctvs1x='entra';
					}
					$montoletra=Numbers_Words::toWords($montoctvs1,'es');
					$totaletras=number_format($totaletras,2);
					$separa=explode(".",$totaletras);
					$montoctvs2 = $separa[1];
					if (left($montoctvs2,3)>995){
						$montoctvs2=0;
					}
					$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
					if ($myrow2['currcode']=='MXN'){
						$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
					}
					else
					{
						$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
					}
					$pdf->addTextWrap($xpos+405,290,170,8,'$ '.number_format($myrow2['subtotal'],2),'right');
					//aqui le pongo la coma con el number_format para que se separe la cantida de los decimales
					//532648 queda 532,648.00//
					$total=number_format($myrow2['total'],2);
					$pdf->addTextWrap($xpos+405,224,170,8,'$ '.$total,'right');
					$YPos=365;
						//aqui lo que se realiza es la cantidad en letra
						//esta linea guarda en una lista los valores enteros en $montoctvs1 y los decimales en $montoctvs2
						//el explode sirve para encontrar los valores despues del punto
						//list($montoctvs1,$montoctvs2)=explode(".",$myrow2['total']);
						//aqui reasigna a $montoletra la cantidad entera
						//la funcion Numbers_Words::toWords convierte los numeros en letras
						//$montoletra=Numbers_Words::toWords($montoctvs1,'es');
						//aqui reasigna a $montocentavos la cantidad decimal
						//$montocentavos=left($montoctvs2,2);//Numbers_Words::toWords($montoctvs2,'es');
						if ($myrow2['currcode']=='MXN'){
							$pdf->addText($Page_Width-$Right_Margin-540,$YPos-75,$FontSize, " ( ".$montoletra." ) ".$montoctvs1x,'right');	
						}
						else
						{		
							$pdf->addText($Page_Width-$Right_Margin-540, $YPos-75,$FontSize, " ( ".$montoletra." ) " ,'right');				
						}
				}//fin del while reult2 de subtotal y total
				//esta consulta sirve para extraer la informacion complementaria
		                 
			}//fin del if extractor de datos del result2 que saca el subtotal y el total
			 
		}//fin del elseif result cuando la consulta si arroja registros
	$Copy='Customer';
}//fin del for que imprime dos veces

$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=20){
        $title = _('Imprimir lista de empaque de error');
        include('includes/header.inc');
        echo '<p>'. _('No había temas pendientes de la orden') . '. ' . _('No se puede imprimir').
                '<br><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Imprimir otra orden').
                '</a>' . '<br>'. '<a href="' . $rootpath . '/index.php?' . SID . '">' . _('Regresar al menu') . '</a>';
        include('includes/footer.inc');
	exit;
} else {
	
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=PackingSlip.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	$pdf->Stream();
	$sql = "UPDATE salesorders SET printedpackingslip=1, datepackingslipprinted='" . Date('Y-m-d') . "' WHERE salesorders.orderno=" .$_GET['TransNo'];
	$result = DB_query($sql,$db);
}
?>