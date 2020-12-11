<?php
/* $Revision: 1.6 $ */

/*Class to hold stock transfer records */

class StockTransfer {

	Var $TrfID;
	Var $StockLocationFrom;
	Var $StockLocationFromName;
	Var $TemplocFrom;
	Var $StockLocationTo;
	Var $StockLocationToName;
	Var $TemplocTo;
	Var $TranDate;
	Var $TransferItem; /*Array of LineItems */
	Var $Podetailitem;
	

	function StockTransfer($TrfID,
				$StockLocationFrom,
				$StockLocationFromName,
				$StockLocationTo,
				$StockLocationToName,
				$TranDate,
				$Podetailitem=0,
				$templocFrom=0,
				$templocTo=0
				)	{

		$this->TrfID = $TrfID;
		$this->StockLocationFrom = $StockLocationFrom;
		$this->StockLocationFromName = $StockLocationFromName;
		$this->TemplocFrom = $templocFrom;
		$this->StockLocationTo =$StockLocationTo;
		$this->StockLocationToName =$StockLocationToName;
		$this->TemplocTo = $templocTo;
		$this->TranDate = $TranDate;
		$this->Podetailitem=$Podetailitem;
		
		$this->TransferItem=array(); /*Array of LineItem s */
	}
}

class LineItem {
	var $StockID;
	var $ItemDescription;
	Var $ShipQty;
	Var $PrevRecvQty;
	Var $Quantity;
	Var $PartUnit;
	var $Controlled;
	var $Serialised;
	Var $DecimalPlaces;
	Var $SerialNo;
	var $SerialItems; /*array to hold controlled items*/
	Var $Podetailitem;
	Var $barcode;
	var $transferline;
//Constructor
	function LineItem($StockID,
			$ItemDescription,
			$Quantity,
			$PartUnit,
			$Controlled,
			$Serialised,
			$DecimalPlaces,
			$SerialNo,
			$Podetailitem=0,
			$barcode='',
			$transferline
			){

		$this->StockID = $StockID;
		$this->ItemDescription = $ItemDescription;
		$this->PartUnit = $PartUnit;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->ShipQty = $Quantity;
		$this->SerialNo = $SerialNo;
		$this->Podetailitem=$Podetailitem;
		$this->barcode=$barcode;
		$this->transferline = $transferline;
		if ($this->Controlled==1){
			$this->Quantity = 1;
		} else {
			$this->Quantity = $Quantity;
		}
		$this->SerialItems = array();
	}
}
?>
