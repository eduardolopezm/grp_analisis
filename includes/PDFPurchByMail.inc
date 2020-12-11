<?php

	//$PaperSize = 'letter_landscape';
	$PaperSize = 'A4_Landscape';

	//include('includes/PDFStarter.php');

	$pdf->addinfo('Title', _('Orden de Compra') );
	$pdf->addinfo('Subject', _('Orden de Compra No.').' ' . $OrderNo);

	$line_height=14;
	
	/* Then there's an order to print and its not been printed already (or its been flagged for reprinting)
	Now ... Has it got any line items */

	   $PageNumber = 1;
	   $ErrMsg = _('There was a problem retrieving the line details for order number') . ' ' . $OrderNo . ' ' .
			_('from the database');
			
	   $sql = "SELECT itemcode,
	   			CASE purchorderdetails.deliverydate
				WHEN '0000-00-00' THEN purchorders.orddate
				ELSE purchorderdetails.deliverydate
				END AS deliverydate,
				itemdescription,
				unitprice,
				units,
				quantityord,
				decimalplaces,
				purchorderdetails.discountpercent1,
				purchorderdetails.discountpercent2,
				purchorderdetails.discountpercent3,
				taxrate,
				narrative,
				justification,
				barcode,stockautor,stockmanufacturer.manufacturer
			FROM purchorderdetails 
	   		LEFT JOIN stockmaster
			ON purchorderdetails.itemcode=stockmaster.stockid
	   		LEFT JOIN purchorders 
	   		ON purchorders.orderno = purchorderdetails.orderno
			LEFT JOIN stockmanufacturer
			ON stockmanufacturer.manufacturerid=stockmaster.manufacturer
			LEFT JOIN taxauthrates 
			ON stockmaster.taxcatid=taxauthrates.taxcatid
			WHERE purchorderdetails.orderno =" . $OrderNo;
				
	   
	   $result=DB_query($sql,$db);

	   if (DB_num_rows($result)>0){
	   /*Yes there are line items to start the ball rolling with a page header */

		include('includes/PO_PDFOrderPageHeader.inc');

		$YPos-=$line_height-10;

		$OrderTotal = 0;

		while ($POLine=DB_fetch_array($result)){
		$SQL = "SELECT
			purchorders.supplierno,
			suppliers.taxid as taxid,
			suppliers.suppname,
			suppliers.address1,
			suppliers.address2,
			suppliers.address3,
			suppliers.address4,
			purchorders.comments,
			purchorders.orddate,
			purchorders.rate,
			purchorders.dateprinted,
			purchorders.deladd1,
			purchorders.deladd2,
			purchorders.deladd3,
			purchorders.deladd4,
			purchorders.deladd5,
			purchorders.deladd6,
			purchorders.allowprint,
			purchorders.requisitionno,
			purchorders.initiator,
			suppliers.currcode
		FROM purchorders INNER JOIN suppliers
			ON purchorders.supplierno = suppliers.supplierid
		WHERE purchorders.orderno = " . $OrderNo;
		 $POHeade=DB_query($SQL,$db);
		while ($myrowc=DB_fetch_array($POHeade)) {
			$supplierno=$myrowc['supplierno'];
			$currcode=$myrowc['currcode'];
			$sql = "SELECT supplierdescription 
				FROM purchdata 
				WHERE stockid='" . $POLine['itemcode'] . "' 
				AND supplierno ='" . $supplierno . "'";
			$SuppDescRslt = DB_query($sql,$db);
			if ($mysqlsupp = DB_fetch_array($SuppDescRslt)){
				$ItemDescription=$mysqlsupp['supplierdescription'];	
			}else{
				$ItemDescription='';
			}
			
			$taxid=$myrowc['taxid'];
		}
		
			$d1=$POLine['discountpercent1'];
			$d2=$POLine['discountpercent2'];
			$d3=$POLine['discountpercent3'];		
		
			if (DB_error_no($db)==0){
				if (DB_num_rows($SuppDescRslt)==1){
					$SuppDescRow = DB_fetch_row($SuppDescRslt);
					if (strlen($SuppDescRow[0])>2){
						$ItemDescription = $SuppDescRow[0];
					}
				}
			}
			if (strlen($ItemDescription)>=2){
				$ItemDescription = $ItemDescription.' - '.$POLine['itemdescription'];	
			}else{
				$ItemDescription = $POLine['itemdescription'];	
			}
			
			$DisplayQty = number_format($POLine['quantityord'],$POLine['decimalplaces']);
			if ($ShowAmounts=='Yes'){
				$DisplayPrice = number_format($POLine['unitprice'],4);
			} else {
				$DisplayPrice = "----";
			}
			$DisplayDelDate = ConvertSQLDate($POLine['deliverydate'],4);
			if ($ShowAmounts=='Yes'){
				$DisplayLineTotal = ($POLine['unitprice']*$POLine['quantityord'])*(1-($d1/100))*(1-($d2/100))*(1-($d3/100));
				$OrderTotal += $DisplayLineTotal;
				$DisplayLineTotal = number_format($DisplayLineTotal,4);
				
				$DisplayLineTax = ($POLine['unitprice']*$POLine['quantityord'])*(1-($d1/100))*(1-($d2/100))*(1-($d3/100))*$POLine['taxrate'];
				$OrderTax += $DisplayLineTax;
				$DisplayLineTax = number_format($DisplayLineTax,4);
				
			} else {
				$DisplayLineTotal = "----";
			}
			
			#$OrderTotal += ($POLine['unitprice']*$POLine['quantityord']);
			
			$FontSize=8;
			$LeftOvers = $pdf->addTextWrap(45,$YPos,80,$FontSize,$POLine['itemcode'], 'left');
			$Left_Marginx=130;
			$space=50;
			$LeftOvers = $pdf->addTextWrap($Left_Marginx+146+$space,$YPos,70,$FontSize,$DisplayQty, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Marginx+230+$space,$YPos,50,$FontSize,$POLine['units'], 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Marginx+306+$space,$YPos,54,$FontSize, $DisplayDelDate, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Marginx+341+$space,$YPos,85,$FontSize,'$ '.$DisplayPrice, 'right');
			
			$LeftOvers = $pdf->addTextWrap($Left_Marginx+496+$space,$YPos,85,$FontSize,'$ '.$DisplayLineTotal, 'left');//528
			$LeftOvers = $pdf->addTextWrap($Left_Marginx+461+$space,$YPos,270,$FontSize,$d1.'%', 'left');
			//$LeftOvers = $pdf->addTextWrap($Left_Marginx+496+$space,$YPos,270,$FontSize,$d2.'%', 'left');
			//$LeftOvers = $pdf->addTextWrap($Left_Marginx+540+$space,$YPos,270,$FontSize,$d3.'%', 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,180,$FontSize, $POLine['barcode'], 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+155,$YPos,180,$FontSize, $POLine['itemdescription'], 'left');
			
			while(strlen($LeftOvers) > 1) {
				$LeftOvers = $pdf->addTextWrap($Left_Margin+155, $YPos-$line_height/1.5, 180, $FontSize, $LeftOvers, 'left');
				$YPos-=$line_height/1.5;
			}
			
			if ($YPos-$line_height <= $Bottom_Margin){
		        /* We reached the end of the page so finsih off the page and start a newy */
				$PageNumber++;
				include ('includes/PO_PDFOrderPageHeader.inc');
			} //end if need a new page headed up

			
			/*increment a line down for the next line item */
			$YPos -= $line_height;
			/* DESPLEGAR NARRATIVA SI EXISTE */
			
			if (strlen($POLine['narrative']) > 0 and $_SESSION['MostrarExtraCompra']==1) {
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1+154,$YPos,600,$FontSize,$POLine['narrative'], 'left');
				
				while (strlen($LeftOvers)>1){
					$LeftOvers = $pdf->addTextWrap($Left_Margin+155,$YPos-$line_height/1.5,600,$FontSize,'             '.$LeftOvers, 'left');
					$YPos-=$line_height/1.5;
					
					if ($YPos-$line_height/1.5 <= $Bottom_Margin){
					
						$PageNumber++;
						include ('includes/PO_PDFOrderPageHeader.inc');
					} //end if need a new page headed up
				}
				
				$YPos -= $line_height;
			}
			
			/* DESPLEGAR JUSTIFICACIONES EN CASO DE QUE ESTE PENDIENTE DE AUTORIZACION */
			
			if ($OrderStatus == 'Pending' OR $OrderStatus == 'Cancelled' OR $OrderStatus == 'Rejected') {
						
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1+40,$YPos,500,$FontSize,'JUSTIFICACION:'.$POLine['justification'], 'left');
				
				while (strlen($LeftOvers)>1){
					$LeftOvers = $pdf->addTextWrap($Left_Margin+1+40,$YPos-$line_height/1.5,500,$FontSize,'             '.$LeftOvers, 'left');
					$YPos-=$line_height/1.5;
					
					if ($YPos-$line_height/1.5 <= $Bottom_Margin){
					/* We reached the end of the page so finsih off the page and start a newy */
						$PageNumber++;
						include ('includes/PO_PDFOrderPageHeader.inc');
					} //end if need a new page headed up
				}
				
				$pdf->line($XPos+263-10, $YPos,$XPos+10, $YPos);//linea arriba
				
			}
			
		} //end while there are line items to print out

		if ($YPos-$line_height <= $Bottom_Margin){ // need to ensure space for totals
		        $PageNumber++;
			include ('includes/PO_PDFOrderPageHeader.inc');
		} //end if need a new page headed up


		if ($ShowAmounts=='Yes'){
			$DisplayOrderTotal = number_format($OrderTotal,2);
			$DisplayOrderTax = number_format($OrderTax,2);
		} else {
			$DisplayOrderTotal = "----";
			$DisplayOrderTax = "----";
		}
		
		$DisplayOrderTax = number_format($OrderTax,2);
	
		$YPos = $Bottom_Margin + $line_height;
		
		$sql="select legalbusinessunit.address1 as calle,legalbusinessunit.address2 as colonia,
			legalbusinessunit.address3 as region,legalbusinessunit.address4 as estado,
			legalbusinessunit.address5 as cp , legalbusinessunit.logo, legalbusinessunit.legalname,
			legalbusinessunit.telephone, legalbusinessunit.fax,legalbusinessunit.taxid,tags.pofootertext,legalbusinessunit.legalurl
			from purchorders
			inner join tags ON purchorders.tagref = tags.tagref
			inner join legalbusinessunit on legalbusinessunit.legalid=tags.legalid
			WHERE purchorders.orderno = '$OrderNo'";
			
		$resultlegaltmp=DB_query($sql,$db);
		$myrowlegaltmp=DB_fetch_array($resultlegaltmp);
		
		
		$leyenda = $myrowlegaltmp['pofootertext'];
		$posY = $YPos+5;
		$LeftOvers = $pdf->addTextWrap(60,$posY,300,8,$leyenda,'left');
		$posY-=$line_height/1.5;
		while (strlen($LeftOvers)>1){
			$LeftOvers = $pdf->addTextWrap(60,$posY,300,8,'             '.$LeftOvers, 'left');
			$posY-=$line_height/1.5;
		}		
		
		$pdf->addText(400,$YPos+5, 10, _('SubTotal'));
		$pdf->addText(400,$YPos-10, 10, _('IVA'));
		$pdf->addText(400,$YPos-24, 10, _('Total'));
		$LeftOvers = $pdf->addTextWrap(1+94+120+85+40+60+70,$YPos+5,100,10,'$'.$DisplayOrderTotal,'right');
		$LeftOvers = $pdf->addTextWrap(1+94+120+85+40+60+70,$YPos-10,100,10,'$'.$DisplayOrderTax,'right');
		$totaldetotal=$OrderTotal+$OrderTax;
		$totaldetotales=number_format($totaldetotal,2);
		$LeftOvers = $pdf->addTextWrap(1+94+120+85+40+60+70,$YPos-25,100,10,'$'.$totaldetotales,'right');
	} /*end if there are order details to show on the order*/
    
	
?>