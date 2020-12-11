<?php

/* $Revision: 1.26 $ */
/* CGM - 21/Diciembre/2012 - modifique formato cuando se trata de movimientos por cuenta contable*/

$PageSecurity = 2;
include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');
$PaperSize = 'letter';
include ('includes/PDFStarter.php');

if (! isset ( $_GET ['iddocto'] ) && ! isset ( $_POST ['iddocto'] )) {
	$title = _ ( 'Select a Purchase Order' );
	include ('includes/header.inc');
	echo '<div class="centre"><br><br><br>';
	prnMsg ( _ ( 'Seleccione un numero de Orden de Factura de Compra antes de seleccionar esta pagina...' ), 'error' );
	echo '<br><br><br><table class="table_index">
		<tr><td class="menu_group_item">
                <li><a href="' . $rootpath . '/MantenimientoInvoiceSuppliers.php?' . SID . '">' . _ ( 'Facturas de Proveedor' ) . '</a></li>
                </td></tr></table></div><br><br><br>';
	include ('includes/footer.inc');
	exit ();
	
	echo '<div class="centre"><br><br><br>' . _ ( 'This page must be called with a purchase order number to print' );
	echo '<br><a href="' . $rootpath . '/index.php?' . SID . '">' . _ ( 'Regresar a Menu Principal' ) . '</a></div>';
	exit ();
}

if (isset ( $_GET ['iddocto'] )) {
	$OrderNo = $_GET ['iddocto'];
} elseif (isset ( $_POST ['iddocto'] )) {
	$OrderNo = $_POST ['iddocto'];
}

$title = _ ( 'Imprimir Factura de Compra No.' ) . ' ' . $OrderNo;

$MakePDFThenDisplayIt = True;
$ShowAmounts = 'Yes';

if (isset ( $MakePDFThenDisplayIt ) or isset ( $MakePDFThenEmailIt )) {
	
	$PaperSize = 'Letter';
	
	// include('includes/PDFStarter.php');
	
	$pdf->addinfo ( 'Title', _ ( 'Factura de Compra' ) );
	$pdf->addinfo ( 'Subject', _ ( 'Factura de Compra No.' ) . ' ' . $OrderNo );
	
	$line_height = 14;
	
	/*
	 * Then there's an order to print and its not been printed already (or its been flagged for reprinting) Now ... Has it got any line items
	 */
	
	$PageNumber = 1;
	$ErrMsg = _ ( 'There was a problem retrieving the line details for order number' ) . ' ' . $OrderNo . ' ' . _ ( 'from the database' );
	
	$sql = "SELECT *
			FROM supptrans
			WHERE id =" . $OrderNo;
	
	$result = DB_query ( $sql, $db );
	
	$SQL = "SELECT
			supptrans.supplierno,
			suppliers.taxid as taxid,
			suppliers.suppname,
			suppliers.address1,
			suppliers.address2,
			suppliers.address3,
			suppliers.address4,
			supptrans.transtext AS comments,
			supptrans.origtrandate as orddate,
			1/supptrans.rate as tcfactura,
			supptrans.origtrandate as dateprinted,
			tags.address1 as deladd1,
			tags.address2 as deladd2,
			tags.address3 as deladd3,
			tags.address4 as deladd4,
			tags.address5 as deladd5,
			tags.address6 as deladd6,
			0 as allowprint,
			supptrans.transno as requisitionno,
			supptrans.currcode,
			case when supptransdetails.stockid is null then 'Cuenta' else supptransdetails.stockid  end  as itemcode,
			case when supptransdetails.description is null then 'Factura con movimiento contable' else supptransdetails.description  end as itemdescription,
			case when supptransdetails.qty is null then 1 else supptransdetails.qty end as qty,
			case when supptransdetails.price is null then supptrans.ovamount else supptransdetails.price end  as unitprice,
			supptrans.origtrandate as deliverydate,
			case when purchorderdetails.unitprice is null then 0 else purchorderdetails.unitprice end as preciocompra,
			case when grns.stdcostunit is null then 0 else grns.stdcostunit end as preciorecepcion,
			case when purchorderdetails.orderno is null then 0 else purchorderdetails.orderno end as ordencompra,
	   		case when grns.rategr is null then 0 else 1/grns.rategr end as tcrecep,
	   		supptransdetails.grns as norecep,grns.androidreference
			
	   		
		FROM supptrans left JOIN supptransdetails ON supptransdetails.supptransid=supptrans.id
			left join grns on grns.grnno=supptransdetails.grns and grns.itemcode=supptransdetails.stockid
			left join purchorderdetails on supptransdetails.orderno=purchorderdetails.orderno and 
			purchorderdetails.itemcode=supptransdetails.stockid and grns.podetailitem=purchorderdetails.podetailitem
			INNER JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
			INNER JOIN tags ON tags.tagref=supptrans.tagref
		WHERE supptrans.id  = " . $OrderNo;
	
	$result = DB_query ( $SQL, $db );
	
	if (DB_num_rows ( $result ) > 0) {
		/* Yes there are line items to start the ball rolling with a page header */
		
		include ('includes/PO_PDFSupplierInvoicePageHeader.inc');
		
		$YPos -= $line_height - 10;
		
		$OrderTotal = 0;
		
		while ( $POLine = DB_fetch_array ( $result ) ) {
			$ItemDescription = $POLine ['descriprod'];
			$SQL = "SELECT
			supptrans.supplierno,
			suppliers.taxid as taxid,
			suppliers.suppname,
			suppliers.address1,
			suppliers.address2,
			suppliers.address3,
			suppliers.address4,
			supptrans.transtext AS comments,
			supptrans.origtrandate as orddate,
			1/supptrans.rate as tcfactura,
			supptrans.rate,
			supptrans.origtrandate as dateprinted,
			tags.address1 as deladd1,
			tags.address2 as deladd2,
			tags.address3 as deladd3,
			tags.address4 as deladd4,
			tags.address5 as deladd5,
			tags.address6 as deladd6,
			0 as allowprint,
			supptrans.transno as requisitionno,
			supptrans.currcode,
			supptransdetails.stockid as itemcode
		FROM supptrans left JOIN supptransdetails ON supptransdetails.supptransid=supptrans.id
			INNER JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
			INNER JOIN tags ON tags.tagref=supptrans.tagref
		WHERE supptrans.id  = " . $OrderNo;
			// echo $SQL;
			$POHeade = DB_query ( $SQL, $db );
			while ( $myrowc = DB_fetch_array ( $POHeade ) ) {
				$supplierno = $myrowc ['supplierno'];
				$currcode = $myrowc ['currcode'];
				$sql = "SELECT supplierdescription 
				FROM purchdata 
				WHERE stockid='" . $POLine ['itemcode'] . "' 
				AND supplierno ='" . $supplierno . "'";
				$SuppDescRslt = DB_query ( $sql, $db );
				if ($mysqlsupp = DB_fetch_array ( $SuppDescRslt )) {
					$ItemDescription = $mysqlsupp ['supplierdescription'];
				}
				
				$taxid = $myrowc ['taxid'];
			}
			
			$androidreference = $POLine['androidreference'];
			
			$d1 = $POLine ['discountpercent1'];
			$d2 = $POLine ['discountpercent2'];
			$d3 = $POLine ['discountpercent3'];
			
			if (DB_error_no ( $db ) == 0) {
				if (DB_num_rows ( $SuppDescRslt ) == 1) {
					$SuppDescRow = DB_fetch_row ( $SuppDescRslt );
					if (strlen ( $SuppDescRow [0] ) > 2) {
						$ItemDescription = $SuppDescRow [0];
					}
				}
			}
			if (strlen ( $ItemDescription ) >= 2) {
				$ItemDescription = $ItemDescription . ' - ' . $POLine ['itemdescription'];
			} else {
				$ItemDescription = $POLine ['itemdescription'];
			}
			
			$ItemDescription=$ItemDescription.' '.$androidreference;
			
			$DisplayQty = number_format ( $POLine ['qty'], $POLine ['decimalplaces'] );
			if ($ShowAmounts == 'Yes') {
				$DisplayPrice = number_format ( $POLine ['unitprice'], 2 );
			} else {
				$DisplayPrice = "----";
			}
			$DisplayDelDate = ConvertSQLDate ( $POLine ['deliverydate'], 2 );
			if ($ShowAmounts == 'Yes') {
				$DisplayLineTotal = ($POLine ['unitprice'] * $POLine ['qty']) * (1 - ($d1 / 100)) * (1 - ($d2 / 100)) * (1 - ($d3 / 100));
				$OrderTotal += $DisplayLineTotal;
				$DisplayLineTotal = number_format ( $DisplayLineTotal, 2 );
				
				$DisplayLineTax = ($POLine ['unitprice'] * $POLine ['quantityord']) * (1 - ($d1 / 100)) * (1 - ($d2 / 100)) * (1 - ($d3 / 100)) * $POLine ['taxrate'];
				$OrderTax += $DisplayLineTax;
				$DisplayLineTax = number_format ( $DisplayLineTax, 2 );
			} else {
				$DisplayLineTotal = "----";
			}
			
			// $OrderTotal += ($POLine['unitprice']*$POLine['quantityord']);
			
			$FontSize = 7;
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1, $YPos, 80, $FontSize, $POLine ['itemcode'], 'left' );
			$FontSize = 8;
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 30 + 145, $YPos, 70, $FontSize, $DisplayQty, 'right' );
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 46 + 100 + 85 + 5, $YPos, 50, $FontSize, $POLine ['units'], 'right' );
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 70 + 100 + 85 + 3 + 37, $YPos, 54, $FontSize, $DisplayDelDate, 'right' );
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 40 + 80 + 95 + 40 + 60, $YPos, 85, $FontSize, '$ ' . $DisplayPrice, 'right' );
			// if($_SESSION['UserID']=='desarrollo' or $_SESSION['UserID']=='csanchez' or $_SESSION['UserID']=='eruiz'){
			// }
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 360, $YPos, 85, $FontSize, $POLine ['ordencompra'], 'right' );
			
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 390, $YPos, 85, $FontSize, $POLine ['norecep'], 'right' );
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 425, $YPos, 85, $FontSize, number_format ( $POLine ['tcrecep'], 2 ), 'right' );
			
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 180 + 80 + 55 + 20 + 60 + 82, $YPos, 85, $FontSize, '$ ' . $DisplayLineTotal, 'right' );
			// $LeftOvers = $pdf->addTextWrap($Left_Margin+1+80+70+85+40+60+85,$YPos,270,$FontSize,$d1.'%', 'left');
			
			// $LeftOvers = $pdf->addTextWrap($Left_Margin+1+115+70+85+40+60+85,$YPos,270,$FontSize,$d2.'%', 'left');
			// $LeftOvers = $pdf->addTextWrap($Left_Margin+1+145+70+85+40+60+85,$YPos,270,$FontSize,$d3.'%', 'left');
			$FontSize = 6.5;
			$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 74, $YPos, 160, $FontSize, $ItemDescription, 'left' );
			// $LeftOvers = $pdf->addTextWrap($Left_Margin+1+54,$YPos,600,$FontSize,$POLine['narrative'], 'left');
			if (strlen ( $LeftOvers ) > 1) {
				$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 74, $YPos - $line_height, 160, $FontSize, $LeftOvers, 'left' );
				$YPos -= $line_height;
			}
			
			// $POLine->Desc2
			// $POLine->Desc3
			
			if (strlen ( $LeftOvers ) > 1) {
				$LeftOvers = $pdf->addTextWrap ( $Left_Margin, $YPos - $line_height, 70, $FontSize, $LeftOvers, 'left' );
				$YPos -= $line_height;
			}
			
			if ($YPos - $line_height <= $Bottom_Margin) {
				/* We reached the end of the page so finsih off the page and start a newy */
				$PageNumber ++;
				include ('includes/PO_PDFSupplierInvoicePageHeader.inc');
			} // end if need a new page headed up
			
			/* increment a line down for the next line item */
			$YPos -= $line_height;
			
			/* DESPLEGAR NARRATIVA SI EXISTE */
			
			if (strlen ( $POLine ['narrative'] ) > 0) {
				$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 54, $YPos, 600, $FontSize, $POLine ['narrative'], 'left' );
				
				while ( strlen ( $LeftOvers ) > 1 ) {
					$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 54, $YPos - $line_height / 1.5, 600, $FontSize, '             ' . $LeftOvers, 'left' );
					$YPos -= $line_height / 1.5;
					
					if ($YPos - $line_height / 1.5 <= $Bottom_Margin) {
						/* We reached the end of the page so finsih off the page and start a newy */
						$PageNumber ++;
						include ('includes/PO_PDFSupplierInvoicePageHeader.inc');
					} // end if need a new page headed up
				}
				/* increment a line down for the next line item */
				$YPos -= $line_height;
			}
			
			/* DESPLEGAR JUSTIFICACIONES EN CASO DE QUE ESTE PENDIENTE DE AUTORIZACION */
			
			if ($OrderStatus == 'Pending' or $OrderStatus == 'Cancelled' or $OrderStatus == 'Rejected') {
				
				$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 40, $YPos, 500, $FontSize, 'JUSTIFICACION:' . $POLine ['justification'], 'left' );
				
				while ( strlen ( $LeftOvers ) > 1 ) {
					$LeftOvers = $pdf->addTextWrap ( $Left_Margin + 1 + 40, $YPos - $line_height / 1.5, 500, $FontSize, '             ' . $LeftOvers, 'left' );
					$YPos -= $line_height / 1.5;
					
					if ($YPos - $line_height / 1.5 <= $Bottom_Margin) {
						/* We reached the end of the page so finsih off the page and start a newy */
						$PageNumber ++;
						include ('includes/PO_PDFSupplierInvoicePageHeader.inc');
					} // end if need a new page headed up
				}
				
				$pdf->line ( $XPos + 263 - 10, $YPos, $XPos + 10, $YPos ); // linea arriba
				
				/* increment a line down for the next line item */
				$YPos -= $line_height;
			}
		} // end while there are line items to print out
		
		if ($YPos - $line_height <= $Bottom_Margin) { // need to ensure space for totals
			$PageNumber ++;
			include ('includes/PO_PDFSupplierInvoicePageHeader.inc');
		} // end if need a new page headed up
		
		if ($ShowAmounts == 'Yes') {
			$DisplayOrderTotal = number_format ( $OrderTotal, 2 );
			$DisplayOrderTax = number_format ( $OrderTax, 2 );
		} else {
			$DisplayOrderTotal = "----";
			$DisplayOrderTax = "----";
		}
		
		$sql = "SELECT * 
				FROM supptrans 
				WHERE id='" . $OrderNo . "'";
		$SuppDescRslt = DB_query ( $sql, $db );
		if ($mysqlsupp = DB_fetch_array ( $SuppDescRslt )) {
			$DisplayOrderTotal = $mysqlsupp ['ovamount'];
			$DisplayOrderTax = $mysqlsupp ['ovgst'];
			$totaldetotal = $mysqlsupp ['ovamount'] + $mysqlsupp ['ovgst'];
		} else {
			$DisplayOrderTotal = 0;
			$DisplayOrderTax = 0;
			$totaldetotales = 0;
		}
		
		$DisplayOrderTax = number_format ( $DisplayOrderTax, 2 );
		$YPos = $Bottom_Margin + $line_height;
		$pdf->addText ( 400, $YPos + 5, 10, _ ( 'SubTotal' ) );
		$pdf->addText ( 400, $YPos - 10, 10, _ ( 'IVA' ) );
		$pdf->addText ( 400, $YPos - 24, 10, _ ( 'Total' ) );
		$LeftOvers = $pdf->addTextWrap ( 1 + 94 + 120 + 85 + 40 + 60 + 70, $YPos + 5, 100, 10, '$' . $DisplayOrderTotal, 'right' );
		$LeftOvers = $pdf->addTextWrap ( 1 + 94 + 120 + 85 + 40 + 60 + 70, $YPos - 10, 100, 10, '$' . $DisplayOrderTax, 'right' );
		// $totaldetotal=$OrderTotal+$OrderTax;
		$totaldetotales = number_format ( $totaldetotal, 2 );
		$LeftOvers = $pdf->addTextWrap ( 1 + 94 + 120 + 85 + 40 + 60 + 70, $YPos - 25, 100, 10, '$' . $totaldetotales, 'right' );
	} /* end if there are order details to show on the order */
	// } /* end of check to see that there was an order selected to print */
	
	// failed var to allow us to print if the email fails.
	$failed = false;
	if ($MakePDFThenDisplayIt) {
		
		$buf = $pdf->output ();
		$len = strlen ( $buf );
		header ( 'Content-type: application/pdf' );
		header ( 'Content-Length: ' . $len );
		header ( 'Content-Disposition: inline; filename=PurchaseOrder.pdf' );
		header ( 'Expires: 0' );
		header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header ( 'Pragma: public' );
		
		$pdf->stream ();
	} else { /* must be MakingPDF to email it */
		
		$pdfcode = $pdf->output ();
		$fp = fopen ( $_SESSION ['reports_dir'] . '/PurchOrder.pdf', 'wb' );
		fwrite ( $fp, $pdfcode );
		fclose ( $fp );
		
		include ('includes/htmlMimeMail.php');
		
		$mail = new htmlMimeMail ();
		$attachment = $mail->getFile ( $_SESSION ['reports_dir'] . '/PurchOrder.pdf' );
		$mail->setText ( _ ( 'Please find herewith our purchase order number' ) . ' ' . $OrderNo );
		$mail->setSubject ( _ ( 'Orden de Compra No. ' ) . ' ' . $OrderNo );
		$mail->addAttachment ( $attachment, 'PurchOrder.pdf', 'application/pdf' );
		$mail->setFrom ( $_SESSION ['CompanyRecord'] ['coyname'] . "<" . $_SESSION ['CompanyRecord'] ['email'] . ">" );
		$result = $mail->send ( array (
				$_POST ['EmailTo'] 
		) );
		if ($result == 1) {
			$failed = false;
			echo '<p>';
			prnMsg ( _ ( 'Purchase order' ) . ' ' . $OrderNo . ' ' . _ ( 'has been emailed to' ) . ' ' . $_POST ['EmailTo'] . ' ' . _ ( 'as directed' ), 'success' );
		} else {
			$failed = true;
			echo '<p>';
			prnMsg ( _ ( 'Emailing Purchase order' ) . ' ' . $OrderNo . ' ' . _ ( 'to' ) . ' ' . $_POST ['EmailTo'] . ' ' . _ ( 'failed' ), 'error' );
		}
	}
	
	if ($ViewingOnly == 0 && ! $failed) {
		$commentsql = 'SELECT initiator,stat_comment FROM purchorders WHERE orderno=' . $OrderNo;
		$commentresult = DB_query ( $commentsql, $db );
		$commentrow = DB_fetch_array ( $commentresult );
		$comment = $commentrow ['stat_comment'];
		$emailsql = 'SELECT email FROM www_users WHERE userid="' . $commentrow ['initiator'] . '"';
		$emailresult = DB_query ( $emailsql, $db );
		$emailrow = DB_fetch_array ( $emailresult );
		$date = date ( $_SESSION ['DefaultDateFormat'] );
		$StatusComment = $date . ' - Printed by <a href="mailto:' . $emailrow ['email'] . '">' . $_SESSION ['UserID'] . '</a><br>' . $comment;
		$sql = "UPDATE purchorders 
			SET allowprint=0, 
				dateprinted='" . Date ( 'Y-m-d' ) . "',
				status='" . _ ( 'Printed' ) . "',
				stat_comment='" . $StatusComment . "' 
			WHERE purchorders.orderno=" . $OrderNo;
		// $result = DB_query($sql,$db);
	}
} /* There was enough info to either print or email the purchase order */
else { /* the user has just gone into the page need to ask the question whether to print the order or email it to the supplier */
	
	include ('includes/header.inc');
	echo '<form action="' . $_SERVER ['PHP_SELF'] . '?' . SID . '" method=post>';
	
	if ($ViewingOnly == 1) {
		echo '<input type=hidden name="ViewingOnly" VALUE=1>';
	}
	echo '<br><br>';
	echo '<input type=hidden name="OrderNo" VALUE="' . $OrderNo . '">';
	echo '<table><tr><td>' . _ ( 'Imprimir o Enviar por Email' ) . '</td><td>
		<select name="PrintOrEmail">';
	
	if (! isset ( $_POST ['PrintOrEmail'] )) {
		$_POST ['PrintOrEmail'] = 'Print';
	}
	
	if ($_POST ['PrintOrEmail'] == 'Print') {
		echo '<option selected VALUE="Print">' . _ ( 'Print' );
		echo '<option VALUE="Email">' . _ ( 'Email' );
	} else {
		echo '<option VALUE="Print">' . _ ( 'Print' );
		echo '<option selected VALUE="Email">' . _ ( 'Email' );
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _ ( 'Desplegar Montos en la Orden' ) . '</td><td>
		<select name="ShowAmounts">';
	
	if (! isset ( $ShowAmounts )) {
		$ShowAmounts = 'Yes';
	}
	
	if ($ShowAmounts == 'Yes') {
		echo '<option selected VALUE="Yes">' . _ ( 'Yes' );
		echo '<option VALUE="No">' . _ ( 'No' );
	} else {
		echo '<option VALUE="Yes">' . _ ( 'Yes' );
		echo '<option selected VALUE="No">' . _ ( 'No' );
	}
	
	echo '</select></td></tr>';
	if ($_POST ['PrintOrEmail'] == 'Email') {
		$ErrMsg = _ ( 'There was a problem retrieving the contact details for the supplier' );
		$SQL = "SELECT suppliercontacts.contact,
				suppliercontacts.email
			FROM suppliercontacts INNER JOIN purchorders
			ON suppliercontacts.supplierid=purchorders.supplierno
			WHERE purchorders.orderno=$OrderNo";
		$ContactsResult = DB_query ( $SQL, $db, $ErrMsg );
		
		if (DB_num_rows ( $ContactsResult ) > 0) {
			echo '<tr><td>' . _ ( 'Email to' ) . ':</td><td><select name="EmailTo">';
			while ( $ContactDetails = DB_fetch_array ( $ContactsResult ) ) {
				if (strlen ( $ContactDetails ['email'] ) > 2 and strpos ( $ContactDetails ['email'], '@' ) > 0) {
					if ($_POST ['EmailTo'] == $ContactDetails ['email']) {
						echo '<option selected VALUE="' . $ContactDetails ['email'] . '">' . $ContactDetails ['Contact'] . ' - ' . $ContactDetails ['email'];
					} else {
						echo '<option VALUE="' . $ContactDetails ['email'] . '">' . $ContactDetails ['contact'] . ' - ' . $ContactDetails ['email'];
					}
				}
			}
			echo '</select></td></tr></table>';
		} else {
			echo '</table><br>';
			prnMsg ( _ ( 'There are no contacts defined for the supplier of this order' ) . '. ' . _ ( 'You must first set up supplier contacts before emailing an order' ), 'error' );
			echo '<br>';
		}
	} else {
		echo '</table>';
	}
	echo '<br><div class="centre"><input type=submit name="DoIt" VALUE="' . _ ( 'OK' ) . '"></div>';
	echo '</form>';
	include ('includes/footer.inc');
}
?>
