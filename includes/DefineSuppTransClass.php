<?php
/* $Revision: 1.10 $ */
/* Definition of the Supplier Transactions class to hold all the information for an accounts payable invoice or credit note
*/

Class SuppTrans {

	var $GRNs; /*array of objects of class GRNs using the GRN No as the pointer */
	var $GLCodes; /*array of objects of class GLCodes using a counter as the pointer */
	var $Shipts;  /*array of objects of class Shipments using a counter as the pointer */
	var $SupplierID;
	var $SupplierName;
	var $CurrCode;
	var $TermsDescription;
	var $Terms;
	var $GLLink_Creditors;
	var $GRNAct;
	var $CreditorsAct;
	var $InvoiceOrCredit;
	var $ExRate;
	var $Comments;
	var $TranDate;
	var $DueDate;
	var $SuppReference;
	var $OvAmount;
	var $OvGST;
	var $GLCodesCounter=0;
	var $ShiptsCounter=0;
	var $TaxGroup;
	var $LocalTaxProvince;
	var $TaxGroupDescription;
	var $Taxes;
	var $tagref;
	var $stkmoveno;
	var $location;

	function SuppTrans(){
	/*Constructor function initialises a new Supplier Transaction object */
		$this->GRNs = array();
		$this->GLCodes = array();
		$this->Shipts = array();
		$this->Taxes = array();
	}
	
	function GetTaxes () {
		
		global $db;
		
		/*Gets the Taxes and rates applicable to the tax group of the supplier 
		and SESSION['DefaultTaxCategory'] and the taxprovince of the location that the user is setup to use*/

		$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.purchtaxglaccount,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates INNER JOIN taxgrouptaxes ON
				taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				INNER JOIN taxauthorities ON
				taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid=" . $this->TaxGroup . " 
			AND taxauthrates.dispatchtaxprovince=" . $this->LocalTaxProvince . " 
			AND taxauthrates.taxcatid = " . $_SESSION['DefaultTaxCategory'] . "
			ORDER BY taxgrouptaxes.calculationorder";
		//echo $SQL;

		$ErrMsg = _('The taxes and rates for this item could not be retrieved because');
		$GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg);
		
		while ($myrow = DB_fetch_array($GetTaxRatesResult)){
		
			$this->Taxes[$myrow['calculationorder']] = new Tax($myrow['calculationorder'],
											$myrow['taxauthid'],
											$myrow['description'],
											$myrow['taxrate'],
											$myrow['taxontax'],
											$myrow['purchtaxglaccount']);
		}
	} //end method GetTaxes()
	
	
	function Add_GRN_To_Trans($GRNNo, 
					$PODetailItem, 
					$ItemCode, 
					$ItemDescription, 
					$QtyRecd, 
					$Prev_QuantityInv, 
					$This_QuantityInv, 
					$OrderPrice,
					$Desc1,
					$Desc2,
					$Desc3,
					$ChgPrice, 
					$Complete, 
					$StdCostUnit=0, 
					$ShiptRef=0, 
					$JobRef, 
					$GLCode,
					$PONo,
					$tagref=0,
					$stkmoveno=0,
					$location=''
					){
	//echo 'localidad:'.$location;
		if ($This_QuantityInv!=0 AND isset($This_QuantityInv)){
			$this->GRNs[$GRNNo] = new GRNs($GRNNo, 
							$PODetailItem, 
							$ItemCode,
							$ItemDescription, 
							$QtyRecd, 
							$Prev_QuantityInv, 
							$This_QuantityInv, 
							$OrderPrice,
							$Desc1,
							$Desc2,
							$Desc3,
							$ChgPrice, 
							$Complete, 
							$StdCostUnit, 
							$ShiptRef, 
							$JobRef, 
							$GLCode,
							$PONo,
							$tagref,
							$stkmoveno,
							$location
							);
			Return 1;
		}
		Return 0;
	}

	function Modify_GRN_To_Trans($GRNNo, 
					$PODetailItem, 
					$ItemCode, 
					$ItemDescription, 
					$QtyRecd, 
					$Prev_QuantityInv,
					$This_QuantityInv,
					$OrderPrice,
					$Desc1,
					$Desc2,
					$Desc3,
					$ChgPrice,
					$Complete,
					$StdCostUnit,
					$ShiptRef,
					$JobRef,
					$GLCode){

		if ($This_QuantityInv!=0 && isset($This_QuantityInv)){
			$this->GRNs[$GRNNo]->Modify($PODetailItem,
							$ItemCode,
							$ItemDescription,
							$QtyRecd,
							$Prev_QuantityInv,
							$This_QuantityInv,
							$OrderPrice,
							$Desc1,
							$Desc2,
							$Desc3,
							$ChgPrice,
							$Complete,
							$StdCostUnit,
							$ShiptRef,
							$JobRef,
							$GLCode
							);
			Return 1;
		}
		Return 0;
	}

	function Copy_GRN_To_Trans($GRNSrc){
		if ($GRNSrc->This_QuantityInv!=0 && isset($GRNSrc->This_QuantityInv)){
			
			$this->GRNs[$GRNSrc->GRNNo] = new GRNs($GRNSrc->GRNNo,
								$GRNSrc->PODetailItem, 
								$GRNSrc->ItemCode, 
								$GRNSrc->ItemDescription, 
								$GRNSrc->QtyRecd, 
								$GRNSrc->Prev_QuantityInv, 
								$GRNSrc->This_QuantityInv, 
								$GRNSrc->OrderPrice,
								$GRNSrc->Desc1,
								$GRNSrc->Desc2,
								$GRNSrc->Desc3, 
								$GRNSrc->ChgPrice, 
								$GRNSrc->Complete, 
								$GRNSrc->StdCostUnit, 
								$GRNSrc->ShiptRef, 
								$GRNSrc->JobRef, 
								$GRNSrc->GLCode,
								$GRNSrc->PONo,
								$GRNSrc->tagref,
								$GRNSrc->stkmoveno,
								$GRNSrc->location
								);
			Return 1;
		}
		Return 0;
	}

	function Add_GLCodes_To_Trans($GLCode, $GLActName, $Amount, $JobRef, $Narrative,$tagref){
		if ($Amount!=0 AND isset($Amount)){
			$this->GLCodes[$this->GLCodesCounter] = new GLCodes($this->GLCodesCounter, 
										$GLCode, 
										$GLActName, 
										$Amount,
										$JobRef, 
										$Narrative,
										$tagref);
			$this->GLCodesCounter++;
			Return 1;
		}
		Return 0;
	}

	function Add_Shipt_To_Trans($ShiptRef, $Concepto, $Amount,$tagref){
		if ($Amount!=0){
			$this->Shipts[$this->ShiptCounter] = new Shipment($this->ShiptCounter, 
										$ShiptRef,
										$Concepto,
										$Amount,
										$tagref
									);
			$this->ShiptCounter++;
			Return 1;
		}
		Return 0;
	}

	function Remove_GRN_From_Trans(&$GRNNo){
	     unset($this->GRNs[$GRNNo]);
	}
	function Remove_GLCodes_From_Trans(&$GLCodeCounter){
	     unset($this->GLCodes[$GLCodeCounter]);
	}
	function Remove_Shipt_From_Trans(&$ShiptCounter){
	     unset($this->Shipts[$ShiptCounter]);
	}

} /* end of class defintion */

Class GRNs {

/* Contains relavent information from the PurchOrderDetails as well to provide in cached form,
all the info to do the necessary entries without looking up ie additional queries of the database again */

	var $GRNNo;
	var $PODetailItem;
	var $ItemCode;
	var $ItemDescription;
	var $QtyRecd;
	var $Prev_QuantityInv;
	var $This_QuantityInv;
	var $OrderPrice;
	var $Desc1;
	var $Desc2;
	var $Desc3;
	var $ChgPrice;
	var $Complete;
	var $StdCostUnit;
	var $ShiptRef;
	var $JobRef;
	var $GLCode;
	Var $PONo;
	var $tagref;
	var $stkmoveno;
	var $location;

	function GRNs ($GRNNo,
			$PODetailItem,
			$ItemCode,
			$ItemDescription,
			$QtyRecd,
			$Prev_QuantityInv,
			$This_QuantityInv,
			$OrderPrice,
			$Desc1,
			$Desc2,
			$Desc3,
			$ChgPrice,
			$Complete,
			$StdCostUnit=0,
			$ShiptRef,
			$JobRef,
			$GLCode,
			$PONo,
			$tagref=0,
			$stkmoveno,
			$location
			){

	/* Constructor function to add a new GRNs object with passed params */
		$this->GRNNo = $GRNNo;
		
		$this->PODetailItem = $PODetailItem;
		$this->ItemCode = $ItemCode;
		$this->ItemDescription = $ItemDescription;
		$this->QtyRecd = $QtyRecd;
		$this->Prev_QuantityInv = $Prev_QuantityInv;
		$this->This_QuantityInv = $This_QuantityInv;
		$this->OrderPrice =$OrderPrice;
		$this->Desc1 =$Desc1;
		$this->Desc2 =$Desc2;
		$this->Desc3 =$Desc3;
		$this->ChgPrice = $ChgPrice;
		$this->Complete = $Complete;
		$this->StdCostUnit = $StdCostUnit;
		$this->ShiptRef = $ShiptRef;
		$this->JobRef = $JobRef;
		$this->GLCode = $GLCode;
		$this->PONo = $PONo;
		$this->tagref = $tagref;
		$this->stkmoveno =$stkmoveno;
		$this->location =$location;
		
	}

	function Modify ($PODetailItem,
				$ItemCode,
				$ItemDescription,
				$QtyRecd,
				$Prev_QuantityInv,
				$This_QuantityInv,
				$OrderPrice,
				$Desc1,
				$Desc2,
				$Desc3,
				$ChgPrice,
				$Complete,
				$StdCostUnit,
				$ShiptRef,
				$JobRef,
				$GLCode){

	/* Modify function to edit a GRNs object with passed params */
		$this->PODetailItem = $PODetailItem;
		$this->ItemCode = $ItemCode;
		$this->ItemDescription = $ItemDescription;
		$this->QtyRecd = $QtyRecd;
		$this->Prev_QuantityInv = $Prev_QuantityInv;
		$this->This_QuantityInv = $This_QuantityInv;
		$this->OrderPrice =$OrderPrice;
		$this->Desc1 =$Desc1;
		$this->Desc2 =$Desc2;
		$this->Desc3 =$Desc3;
		$this->ChgPrice = $ChgPrice;
		$this->Complete = $Complete;
		$this->StdCostUnit = $StdCostUnit;
		$this->ShiptRef = $ShiptRef;
		$this->JobRef = $JobRef;
		$this->GLCode = $GLCode;
	}
}


Class GLCodes {

	Var $Counter;
	Var $GLCode;
	Var $GLActName;
	Var $Amount;
	Var $JobRef;
	Var $Narrative;
	Var $tagref;

	function GLCodes ($Counter, $GLCode, $GLActName, $Amount, $JobRef, $Narrative,$tagref){

	/* Constructor function to add a new GLCodes object with passed params */
		$this->Counter = $Counter;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->Amount = $Amount;
		$this->JobRef = $JobRef;
		$this->Narrative= $Narrative;
		$this->tagref= $tagref;
		
	}
}

Class Shipment {

	Var $Counter;
	Var $ShiptRef;
	Var $Concepto;
	Var $Amount;
	Var $tagref;	
	function Shipment ($Counter, $ShiptRef, $Concepto, $Amount,$tagref){
		$this->Counter = $Counter;
		$this->ShiptRef = $ShiptRef;
		$this->Concepto = $Concepto;
		$this->Amount = $Amount;
		$this->tagref = $tagref;
	}
}

Class Tax {
	Var $TaxCalculationOrder;  /*the index for the array */
	Var $TaxAuthID;
	Var $TaxAuthDescription;
	Var $TaxRate;
	Var $TaxOnTax;
	Var $TaxGLCode;
	Var $TaxOvAmount;
		
	function Tax ($TaxCalculationOrder, 
			$TaxAuthID, 
			$TaxAuthDescription, 
			$TaxRate, 
			$TaxOnTax, 
			$TaxGLCode){
			
		$this->TaxCalculationOrder = $TaxCalculationOrder;
		$this->TaxAuthID = $TaxAuthID;
		$this->TaxAuthDescription = $TaxAuthDescription;
		$this->TaxRate =  $TaxRate;
		$this->TaxOnTax = $TaxOnTax;
		$this->TaxGLCode = $TaxGLCode;
	}
}
?>
