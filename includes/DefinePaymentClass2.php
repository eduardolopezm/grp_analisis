<?php
/* $Revision: 1.6 $ */
/* definition of the Payment class */
/*desarrollo26/FEB/2013 - Agregue unidad de negocio por partida asi como corregi parametro faltante en Edit_To_GLAnalysisAddSupp ($ctaproveedor)*/

Class Payment {

	var $GLItems; /*array of objects of Payment class - id is the pointer */
	var $SupplierID; /* supplier code */
	var $SuppName;
	var $tagref;
	var $GLCode;
	var $trandate;
	var $currency;
	var $paymenttype;
	var $cheque;
	var $Beneficiary;
	var $Narrative;
	var $GLItemCounter; /*Counter for the number of GL accounts being posted to by the Payment */
	var $tagname;
	var $acreedor;
	var $rate;

	function Payment(){
	/*Constructor function initialises a new Payment batch */
		$this->GLItems = array();
		$this->GLItemCounter=0;
		

	}

	function Add_To_GLAnalysis($tagref, $GLCode, $trandate, $currency, $paymenttype, $cheque, $Beneficiary, $Narrative, $tagname, $acreedor, $rate){
		if (isset($GLCode)){
			$this->tagref = $tagref;
			$this->GLCode = $GLCode;
			$this->trandate = $trandate;
			$this->currency = $currency;
			$this->paymenttype = $paymenttype;
			$this->cheque = $cheque;
			$this->Beneficiary = $Beneficiary;
			$this->Narrative = $Narrative;
			$this->tagname = $tagname;
			$this->acreedor = $acreedor;
			$this->rate = $rate;
			
		}
		Return 0;
	}


	
	
					   
	function Add_To_GLAnalysisAddSupp($SupplierID, $SuppName, $taxrate, $AmountTax, $Amount, $Discountsupp, $RetencionIVA, $RetencionISR, $RetencionxArrendamiento,
					   $RetencionxComisiones, $RetencionxFletes, $RetencionxCedular, $descripciongasto, $referencia, $ctaproveedor, $tagMovto,$wo){
		if ($Amount!=0){
			
			
			$this->GLItems[$this->GLItemCounter] = new PaymentGLAnalysisAddSupp($this->GLItemCounter, $SupplierID, $SuppName, $taxrate, $AmountTax, $Amount, $Discountsupp, $RetencionIVA,
											$RetencionISR, $RetencionxArrendamiento,$RetencionxComisiones, $RetencionxFletes,
											$RetencionxCedular, $descripciongasto, $referencia, $ctaproveedor, $tagMovto,$wo);
			$this->GLItemCounter++;
			
			Return 1;
		}
		Return 0;
	}

	function Edit_To_GLAnalysisAddSupp($ID, $SupplierID, $SuppName, $taxrate, $AmountTax, $Amount, $Discountsupp, $RetencionIVA, $RetencionISR, $RetencionxArrendamiento,
					   $RetencionxComisiones, $RetencionxFletes, $RetencionxCedular, $descripciongasto, $referencia, $ctaproveedor, $tagMovto,$wo){
		
			
			
			$this->GLItems[$ID]->SupplierID = $SupplierID;
			$this->GLItems[$ID]->SuppName =$SuppName;
			$this->GLItems[$ID]->taxrate =$taxrate;
			$this->GLItems[$ID]->AmountTax =$AmountTax;
			$this->GLItems[$ID]->Amount =$Amount;
			$this->GLItems[$ID]->Discountsupp =$Discountsupp;
			$this->GLItems[$ID]->RetencionIVA =$RetencionIVA;
			$this->GLItems[$ID]->RetencionISR =$RetencionISR;
			$this->GLItems[$ID]->RetencionxArrendamiento =$RetencionxArrendamiento;
			$this->GLItems[$ID]->RetencionxComisiones =$RetencionxComisiones;
			$this->GLItems[$ID]->RetencionxFletes =$RetencionxFletes;
			$this->GLItems[$ID]->RetencionxCedular =$RetencionxCedular;
			$this->GLItems[$ID]->descripciongasto =$descripciongasto;
			$this->GLItems[$ID]->referencia =$referencia;
			$this->GLItems[$ID]->tagMovto =$tagMovto;
			$this->GLItems[$ID]->wo =$wo;
			
			Return 1;
		
	}

	function remove_GLItem($GL_ID){
		unset($this->GLItems[$GL_ID]);
	}

} /* end of class defintion */

Class PaymentGLAnalysis {

	var $tagref;
	var $GLCode;
	var $trandate;
	var $currency;
	var $paymenttype;
	var $cheque;
	var $Beneficiary;
	var $Narrative;
	var $ID;
	//var $Amount;
	//var $GLActName;
	
	
	function PaymentGLAnalysis ($tagref, $GLCode, $trandate, $currency, $paymenttype, $cheque, $Beneficiary, $Narrative, $ID){
		$this->tagref = $tagref;
		$this->GLCode = $GLCode;
		$this->trandate = $trandate;
		$this->currency = $currency;
		$this->paymenttype = $paymenttype;
		$this->cheque = $cheque;
		$this->Beneficiary=$Beneficiary;
		$this->Narrative = $Narrative;
		$this->ID = $id;
		//$this->Amount =$Amt;
		//$this->GLActName = $GLActName;
	}
}



Class PaymentGLAnalysisAddSupp {

	var $ID;
	var $SupplierID;
	var $SuppName;
	var $taxrate;
	var $AmountTax;
	var $Amount;
	var $Discountsupp;
	var $RetencionIVA;
	var $RetencionISR;
	var $RetencionxArrendamiento;
	var $RetencionxComisiones;
	var $RetencionxFletes;
	var $RetencionxCedular;
	var $descripciongasto;
	var $referencia;
	var $ctaproveedor;
	var $tagMovto;
	var $wo;
	//var $Iva;
	
	function PaymentGLAnalysisAddSupp ($ID, $SupplierID, $SuppName, $taxrate, $AmountTax, $Amount, $Discountsupp, $RetencionIVA, $RetencionISR, $RetencionxArrendamiento,
					   $RetencionxComisiones, $RetencionxFletes, $RetencionxCedular, $descripciongasto, $referencia, $ctaproveedor, $tagMovto, $wo){

/* Constructor function to add a new PaymentGLAnalysis object with passed params */
		$this->ID = $ID;
		$this->SupplierID = $SupplierID;
		$this->SuppName = $SuppName;
		$this->taxrate = $taxrate;
		$this->AmountTax = $AmountTax;
		$this->Amount = $Amount;
		$this->Discountsupp = $Discountsupp; 
		$this->RetencionIVA = $RetencionIVA;
		$this->RetencionISR = $RetencionISR;
		$this->RetencionxArrendamiento=$RetencionxArrendamiento;
		$this->RetencionxComisiones=$RetencionxComisiones;
		$this->RetencionxFletes=$RetencionxFletes;
		$this->RetencionxCedular=$RetencionxCedular;
		$this->descripciongasto = $descripciongasto;
		$this->referencia = $referencia;
		$this->ctaproveedor = $ctaproveedor;
		$this->tagMovto = $tagMovto;
		$this->wo=$wo;
		
	}
}


?>