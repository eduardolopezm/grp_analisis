<?php 

// La variable $identifier viene del php PO_items.php

if(empty($_POST['partidas']) == FALSE) {
	
	$debug = 0;
	$newOrderNo = 0;
	$insertarCabecera = TRUE;
	$partidas = $_POST['partidas'];
	$orderno = $_SESSION['ExistingPurchOrder'];
	$partidasObj = & $_SESSION['PO' . $identifier];
	$tienePartidas = FALSE;
	
	DB_Txn_Begin($db);
	
	foreach($partidas as $partida) {	
				
		if($insertarCabecera) {
			
			$sql = "
				INSERT INTO purchorders (
					supplierno,
					comments,
					orddate,
					rate,
					foliopurch,
					dateprinted,
					allowprint,
					initiator,
					requisitionno,
					intostocklocation,
					deladd1,
					deladd2,
					deladd3,
					deladd4,
					deladd5,
					deladd6,
					contact,
					version,
					revised,
					realorderno,
					deliveryby,
					deliverydate,
					status,
					stat_comment,
					tagref,
					refundpercentpurch,
					totalrefundpercentpurch,
					systypeorder,
					currcode,
					wo
				) SELECT
					supplierno,
					comments,
					orddate,
					rate,
					foliopurch,
					dateprinted,
					allowprint,
					initiator,
					requisitionno,
					intostocklocation,
					deladd1,
					deladd2,
					deladd3,
					deladd4,
					deladd5,
					deladd6,
					contact,
					version,
					revised,
					realorderno,
					deliveryby,
					deliverydate,
					'Pending',
					stat_comment,
					tagref,
					refundpercentpurch,
					totalrefundpercentpurch,
					0,
					currcode,
					wo
				FROM purchorders
				WHERE orderno = '$orderno'		
			";
			
			$ErrMsg = _('La orden de compra no pudo ser insertada');
			$DbgMsg = _('El SQL que fallo fue:');
			
			if($debug) {
				echo "<pre>$sql";
			} else {
				
				$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
			}
				
			$newOrderNo = DB_Last_Insert_ID($db, 'purchorders', 'orderno');
			$insertarCabecera = FALSE;
		}
		
		$sql = "
			INSERT INTO purchorderdetails (
				orderno,
				itemcode,
				deliverydate,
				itemdescription,
				glcode,
				qtyinvoiced,
				unitprice,
				actprice,
				stdcostunit,
				quantityord,
				quantityrecd,
				shiptref,
				jobref,
				completed,
				itemno,
				uom,
				subtotal_amount,
				package,
				pcunit,
				nw,
				suppliers_partno,
				gw,
				cuft,
				total_quantity,
				total_amount,
				discountpercent1,
				discountpercent2,
				discountpercent3,
				narrative,
				justification,
				refundpercent,
				totalrefundpercent,
				estimated_cost,
				wo,qtywo,
				womasterid,
				wocomponent
			) SELECT
				'$newOrderNo',
				itemcode,
				deliverydate,
				itemdescription,
				glcode,
				qtyinvoiced,
				unitprice,
				actprice,
				stdcostunit,
				quantityord,
				quantityrecd,
				shiptref,
				jobref,
				completed,
				itemno,
				uom,
				subtotal_amount,
				package,
				pcunit,
				nw,
				suppliers_partno,
				gw,
				cuft,
				total_quantity,
				total_amount,
				discountpercent1,
				discountpercent2,
				discountpercent3,
				narrative,
				justification,
				refundpercent,
				totalrefundpercent,
				estimated_cost,
				wo,qtywo,
				womasterid,
				wocomponent
			FROM purchorderdetails
			WHERE podetailitem = '" . $partida . "'
		";
		
		$ErrMsg = _('El detalle de la orden de compra no pudo ser insertada');
		$DbgMsg = _('El SQL que fallo fue:');
		
		if($debug) {
			echo "<pre>$sql";
		} else {
			
			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
		}
		
		$poLine = NULL;
		foreach($partidasObj->LineItems as &$poLineTmp) {
			if($poLineTmp->PODetailRec == $partida) {
				$poLine = &$poLineTmp;
				$_SESSION['PO'.$identifier]->LineItems[$poLine->LineNo]->Deleted=True;
			}
		}
		
		// Si esta partida no tiene recepiones, borrarla.
		if($poLine != NULL) {
			if($poLine->QtyReceived == 0 AND $poLine->QtyInvoiced == 0) {
				
				$sql = "
					DELETE FROM purchorderdetails 
					WHERE podetailitem='" . $poLine->PODetailRec . "'
				";
				
				$ErrMsg = _('La orden de compra no se pudo eliminar porque');
				$DbgMsg = _('La instrucción SQL utilizada para eliminar del detalle de orden de compra, FALLO');
				
				if($debug) {
					echo "<pre>$sql";
				} else {
					//echo '<pre>'.$sql;
					$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
					//$poLine->Deleted = TRUE;
					
					
				}
				
			} else {
				prnMsg(_("La partida $partida no puedo ser eliminada porque ya tiene recepciones o esta facturada."), 'error');
			}
		}	
	}
	
	$ModifyPage = "<a href='".$rootpath . "/PO_Header.php?" . SID . "&ModifyOrderNumber=" . $newOrderNo."'><font size=1>".$newOrderNo.'</a>';
	
	prnMsg(_("La orden de compra No.". $ModifyPage ." ha sido creada!."), 'info');
	
	
	DB_Txn_Commit($db);
	$_SESSION['ExistingPurchOrder']=$orderno;
	$_GET['ModifyOrderNumber']=$orderno;
	include ('PO_ReadInOrder.inc');
	
	$arrfecha = explode("/",$_SESSION['PO'.$identifier]->Orig_OrderDate);
	//echo $_SESSION['PO'.$identifier]->Orig_OrderDate;
	$FromDia = $arrfecha[2];
	$FromMes = $arrfecha[1];
	$FromAnio = $arrfecha[1];
	
	
} else {
	prnMsg(_("No hay partidas seleccionadas!"), 'error');
}
?>