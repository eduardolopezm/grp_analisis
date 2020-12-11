<?php
/* $Revision: 1.7 $
 * SE AGREGARON LOS SIGUIENTES CAMPOS JAHEPI 05-02-2013
   ALTER TABLE `stockserialitems`
   ADD COLUMN `customs`  varchar(150) NULL COMMENT 'ADUANA',
   ADD COLUMN `customs_number`  int NULL COMMENT 'NUMERO DE ADUANA' AFTER `customs`,
   ADD COLUMN `customs_date`  date NULL COMMENT 'FECHA DE ADUANA' AFTER `customs_number`;
 *  
 *  */

function ValidBundleRef ($StockID, $LocCode, $BundleRef){
	global $db;

	$SQL = "SELECT quantity
				FROM stockserialitems 
				WHERE stockid='" . $StockID . "' 
				AND loccode ='" . $LocCode . "' 
				AND serialno='" . $BundleRef . "'";
	$Result = DB_query($SQL, $db);
	
	if (DB_num_rows($Result)==0){
		return 0;
	} else {
		$myrow = DB_fetch_row($Result);
		return $myrow[0]; /*The quantity in the bundle */
	}
}


function ValidBundleRefCost ($StockID, $LocCode, $BundleRef){
	global $db;

	$SQL = "SELECT  standardcost
				FROM stockserialitems 
				WHERE stockid='" . $StockID . "' 
				AND loccode ='" . $LocCode . "' 
				AND serialno='" . $BundleRef . "'";
	$Result = DB_query($SQL, $db);
	
	if (DB_num_rows($Result)==0){
		return 0;
	} else {
		$myrow = DB_fetch_row($Result);
		return $myrow[0]; /*The quantity in the bundle */
	}
}

class SerialItem {

	var $BundleRef;
	var $BundleQty;
	var $CostSerialItem;
	var $StockIDParent;
	var $EntryPort;
	// Aduana
	var $Customs;
	var $CustomsNumber;
	var $CustomsDate;

	//Constructor
	function SerialItem($BundleRef, $BundleQty,$CostSerialItem,$StockIDParent='', $Customs = '', $CustomsNumber = '', $CustomsDate = '', $EntryPort = '') {

		$this->BundleRef = $BundleRef;
		$this->BundleQty = $BundleQty;
		$this->CostSerialItem = $CostSerialItem;
		$this->StockIDParent=$StockIDParent;
		$this->Customs =$Customs;
		$this->CustomsNumber =$CustomsNumber;
		$this->CustomsDate =$CustomsDate;
		$this->EntryPort = $EntryPort;
		
	}
}//class SerialItem
?>
