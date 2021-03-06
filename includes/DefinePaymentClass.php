<?php
/* $Revision: 1.6 $ */
/* definition of the Payment class */

Class Payment {

	var $GLItems; /*array of objects of Payment class - id is the pointer */
	var $Account; /*Bank account GL Code Paid from */
	var $AccountCurrency; /*Bank account currency */
	var $BankAccountName; /*Bank account name */
	var $DatePaid; /*Date the batch of Payments was Paid */
	var $ExRate; /*Exchange rate between the payment and the account currency*/
	var $FunctionalExRate; /*Ex rate between the account currency and functional currency */
	var $Currency; /*Currency being Paid - defaulted to bank account currency */
	var $SupplierID; /* supplier code */
	var $SuppName;
	var $Address1;
	var $Address2;
	var $Address3;
	var $Address4;
	var $Address5;
	var $Address6;
	var $Discount;
	var $Amount;
	var $Narrative;
	var $GLItemCounter; /*Counter for the number of GL accounts being posted to by the Payment */
	var $Beneficiary;
	var $Iva;
	var $Discountsupp;
	var $RetencionIVA;
	var $RetencionISR;
	var $RetencionxCedular;
	var $RetencionxFletes;
	var $RetencionxComisiones;
	var $RetencionxArrendamiento;
	var $AmountTax;
	var $taxrate;
	var $payapplies;
	var $GLTypeIetu;
	var $GLTypeDIOT;
	var $TaxCat;
	var $TaxId; // rfc del proveedor
	
	function Payment(){
	/*Constructor function initialises a new Payment batch */
		$this->GLItems = array();
		$this->GLItemCounter=0;
		$this->SupplierID ="";
		$this->SuppName ="";
		$this->Address1 ="";
		$this->Address2 ="";
		$this->Address3 ="";
		$this->Address4 ="";
		$this->Address5 ="";
		$this->Address6 ="";
		$this->TaxId ="";

	}

	function Add_To_GLAnalysis($Amount, $Narrative, $GLCode, $GLActName, $tag, $cheque,$Beneficiary,$payapplies,$GLTypeIetu,$GLTypeDIOT){
		if (isset($GLCode) AND $Amount!=0){
			$this->GLItems[$this->GLItemCounter] = new PaymentGLAnalysis($Amount, $Narrative, $this->GLItemCounter, $GLCode, $GLActName, $tag, $cheque,$Beneficiary,$payapplies,$GLTypeIetu,$GLTypeDIOT);
			$this->GLItemCounter++;
			Return 1;
		}
		Return 0;
	}


	function Add_To_GLAnalysisAddSupp($Amount, $Narrative, $GLCode, $GLActName, $tag, $cheque,$Beneficiary,$Iva,$SuppName,$SupplierID,$Discountsupp,$RetencionIVA,$RetencionISR,$RetencionxCedular,$RetencionxFletes,$RetencionxComisiones,$RetencionxArrendamiento,$AmountTax,$taxrate,$TaxCat){
		if (isset($GLCode) AND $Amount!=0){
			$this->GLItems[$this->GLItemCounter] = new PaymentGLAnalysisAddSupp($Amount, $Narrative, $this->GLItemCounter, $GLCode, $GLActName, $tag, $cheque,$Beneficiary,$Iva,$SuppName,$SupplierID,$Discountsupp,$RetencionIVA,$RetencionISR,$RetencionxCedular,$RetencionxFletes,$RetencionxComisiones,$RetencionxArrendamiento,$AmountTax,$taxrate,$TaxCat);
			$this->GLItemCounter++;
			Return 1;
		}
		Return 0;
	}


	function remove_GLItem($GL_ID){
		unset($this->GLItems[$GL_ID]);
	}

} /* end of class defintion */

Class PaymentGLAnalysis {

	var $Amount;	/* in currency of the payment*/
	var $Narrative;
	var $GLCode;
	var $GLActName;
	var $ID;
	var $tag;
	var $cheque;
	var $Beneficiary;
	var $payapplies;
	var $GLTypeIetu;
	var $GLTypeDIOT;
	function PaymentGLAnalysis ($Amt, $Narr, $id, $GLCode, $GLActName, $tag, $cheque,$Beneficiary,$payapplies,$GLTypeIetu,$GLTypeDIOT){

/* Constructor function to add a new PaymentGLAnalysis object with passed params */
		$this->Amount =$Amt;
		$this->Narrative = $Narr;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->ID = $id;
		$this->tag = $tag;
		$this->cheque = $cheque;
		$this->Beneficiary=$Beneficiary;
		$this->payapplies=$payapplies;
		$this->GLTypeIetu=$GLTypeIetu;
		$this->GLTypeDIOT=$GLTypeDIOT;
	}
}



Class PaymentGLAnalysisAddSupp {

	var $Amount;	/* in currency of the payment*/
	var $Narrative;
	var $GLCode;
	var $GLActName;
	var $ID;
	var $tag;
	var $cheque;
	var $Beneficiary;
	var $Iva;
	var $SuppName;
	var $SupplierID;
	var $Discountsupp;
	var $RetencionIVA;
	var $RetencionISR;
	var $RetencionxCedular;
	var $RetencionxFletes;
	var $RetencionxComisiones;
	var $RetencionxArrendamiento;
	var $AmountTax;
	var $taxrate;
	var $payapplies;
	var $TaxCat;
	function PaymentGLAnalysisAddSupp ($Amt, $Narr, $id, $GLCode, $GLActName, $tag, $cheque,$Beneficiary,$Iva,$SuppName,$SupplierID,$Discountsupp,$RetencionIVA,$RetencionISR,$RetencionxCedular,$RetencionxFletes,$RetencionxComisiones,$RetencionxArrendamiento,$AmountTax,$taxrate,$TaxCat){

/* Constructor function to add a new PaymentGLAnalysis object with passed params */
		$this->Amount =$Amt;
		$this->Narrative = $Narr;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->ID = $id;
		$this->tag = $tag;
		$this->cheque = $cheque;
		$this->Beneficiary=$Beneficiary;
		$this->Iva = $Iva;
		$this->SuppName=$SuppName;
		$this->SupplierID=$SupplierID;
		$this->Discountsupp=$Discountsupp; 
		$this->RetencionIVA=$RetencionIVA;
		$this->RetencionISR=$RetencionISR;
		$this->RetencionxCedular=$RetencionxCedular;
		$this->RetencionxFletes=$RetencionxFletes;
		$this->RetencionxComisiones=$RetencionxComisiones;
		$this->RetencionxArrendamiento=$RetencionxArrendamiento;
		$this->AmountTax=$AmountTax;
		$this->taxrate=$taxrate;
		$this->TaxCat=$TaxCat;

	}
}


?>