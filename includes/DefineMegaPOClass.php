<?php
/* $Revision: 1.4 $ */
/* definition of the mega PO class */

Class megaPO {

	var $ItemEntries; /*array of objects of POItems class - id is the pointer */
	var $PODate; /*Date the PO to be processed */
	var $POItemCounter; /*Counter for the number of GL entires being posted to by the journal */
	var $POItemID;
	var $SupplierID; /*Clave de Proveedor*/
	var $LocCode; /*Almacen de Recepcion de Productos */
	var $Initiator; /*Usuario que crea la orden*/
	var $SupplierContact;		 
	var $DelAdd1;
	var $DelAdd2;
	var $DelAdd3;
	var $DelAdd4;
	var $DelAdd5;
	var $DelAdd6;
	var $tel;
	var $contact;
	var $CurrCode;
	var $OrderNo;
	var $ExRate;
		
	function megaPO(){
	/*Constructor function initialises a new journal */
		$this->ItemEntries = array();
		$this->POItemCounter=0;
		$this->POItemID=0;
		$this->SupplierID = 0;
		$this->LocCode = 0;
		$this->ExRate = 1;
	}

	function Add_To_PO($stockid, $nom, $qty, $price, $desc1, $desc2, $desc3, $glcode){
		if (isset($stockid) AND $qty!=0){
			
			$this->ItemEntries[$this->POItemID] = new POItems($stockid, $nom, $qty, $price, $desc1, $desc2, $desc3, $glcode);
			$this->POItemCounter++;
			$this->POItemID++;
			Return 1;
		}
		Return 0;
	}

} /* end of class defintion */

Class POItems {

	Var $StockID;
	Var $ItemDescription;
	Var $qty;
	Var $price;
	Var $desc1;
	Var $desc2;
	Var $desc3;
	var $glcode;

	function POItems ($pstockid, $pItemDescription, $pqty, $pprice, $pdesc1, $pdesc2, $pdesc3, $glcode){
		
		/* Constructor function to add a new JournalGLAnalysis object with passed params */
		
		$this->StockID = $pstockid;
		$this->ItemDescription = $pItemDescription;
		$this->qty     = $pqty;
		$this->price   = $pprice;
		$this->desc1   = $pdesc1;
		$this->desc2   = $pdesc2;
		$this->desc3   = $pdesc3;
		$this->glcode = $glcode;
	}
}

?>
