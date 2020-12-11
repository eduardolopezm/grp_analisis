<?php
/* $Revision: 1.6 $ */
/* definition of the ReceiptBatch class */

Class Receipt_Batch {

	var $Items; /*array of objects of Receipt class - id is the pointer */
	var $BatchNo; /*Batch Number*/
	var $Account; /*Bank account GL Code banked into */
	var $AccountCurrency; /*Bank Account Currency */
	var $BankAccountName; /*Bank account name */
	var $DateBanked; /*Date the batch of receipts was banked */
	var $ExRate; /*Exchange rate conversion between currency received and bank account currency */
	var $FunctionalExRate; /* Exchange Rate between Bank Account Currency and Functional(business reporting) currency */
	var $Currency; /*Currency being banked - defaulted to company functional */
	var $Narrative;
	var $ReceiptType;  /*Type of receipt ie credit card/cash/cheque etc - array of types defined in config.php*/
	var $total;	  /*Total of the batch of receipts in the currency of the company*/
	var $ItemCounter; /*Counter for the number of customer receipts in the batch */

	function Receipt_Batch(){
	/*Constructor function initialises a new receipt batch */
		$this->Items = array();
		$this->ItemCounter=0;
		$this->total=0;
	}

	function add_to_batch($Amount, $Customer, $Discount, $Narrative, $GLCode, $PayeeBankDetail, $CustomerName, $tag){
		if ((isset($Customer)||isset($GLCode)) && ($Amount + $Discount) !=0){
			$this->Items[$this->ItemCounter] = new Receipt($Amount, $Customer, $Discount, $Narrative, $this->ItemCounter, $GLCode, $PayeeBankDetail, $CustomerName, $tag);
			$this->ItemCounter++;
			$this->total = $this->total + ($Amount + $Discount) / $this->ExRate;
			Return 1;
		}
		Return 0;
	}

	function remove_receipt_item($RcptID){

		$this->total = $this->total - ($this->Items[$RcptID]->Amount + $this->Items[$RcptID]->Discount) / $this->ExRate;
		unset($this->Items[$RcptID]);

	}

} /* end of class defintion */

Class Receipt {
	Var $Amount;	/*in currency of the customer*/
	Var $Customer; /*customer code */
	Var $CustomerName;
	Var $Discount;
	Var $Narrative;
	Var $GLCode; //Cuenta de Clientes CXC
	Var $PayeeBankDetail;
	Var $ID;
	var $tag;

	function Receipt ($Amt, $Cust, $Disc, $Narr, $id, $GLCode, $PayeeBankDetail, $CustomerName, $tag){

/* Constructor function to add a new Receipt object with passed params */
		$this->Amount =$Amt;
		$this->Customer = $Cust;
		$this->CustomerName = $CustomerName;
		$this->Discount = $Disc;
		$this->Narrative = $Narr;
		$this->GLCode = $GLCode;
		$this->PayeeBankDetail=$PayeeBankDetail;
		$this->ID = $id;
		$this->tag = $tag;
	}
}

//***************************INICIO******************************************
//***************NUEVA CLASE PARA EL MANEJO DE CLIENTES**********************
//**************Y DEPOSITOS DEL MISMO****************************************
//***************************************************************************

Class Customer_Branch {

	var $Items; /*arreglo de pagos del cliente - id es el indice*/
	var $debtorno; /*id del cliente*/
	var $branchcode; /*id de la sucursal*/
	var $total;	  /*total de depositos*/
	var $ItemCounter; /*contador del numero de depositos */
	var $ItemsDeptorTrans; /*arreglo para estado de cuenta*/
	var $ItemCounterDT; /*contador del numero de registros del edo de cuenta*/

	function Customer_Branch($debtorno){
	/*Constructor function initialises a new receipt batch */
		$this->Items = array();
		$this->ItemCounter=0;
		$this->total=0;
		$this->debtorno = $debtorno;
		$this->branchcode = '11';
		$this->ItemsDeptorTrans = array();
		$this->ItemCounterDT = 0;
	}

	function add_payment($Amount, $glt, $ref, $cur, $leg){
		$this->Items[$this->ItemCounter] = new Payment($Amount, $glt, $ref, $cur, $leg, $this->ItemCounter);
		$this->ItemCounter++;
		$this->total = $this->total + $Amount;
		Return 1;
	}

	function remove_payment($RcptID){
		$this->total = $this->total - $this->Items[$RcptID]->Amount;
		unset($this->Items[$RcptID]);
	}

	function add_DeptorTrans($legalid, $transno, $totalamount, $allocated, $total){
		$this->ItemsDeptorTrans[$this->ItemCounterDT] = new DeptorTrans($legalid, $transno, $totalamount, $allocated, $total, $this->ItemCounterDT);
		$this->ItemCounterDT++;
		Return 1;
	}

	function remove_DeptorTrans($RcptID){
		unset($this->ItemsDeptorTrans[$RcptID]);
	}


} /* end of class defintion */

Class Payment {
	var $Amount;	/*cantidad de deposito*/
	var $gltemp; /*cuenta puente */
	var $reference; /*referencia bancaria*/
	var $currency;
	var $legalname;
	var $ID;

	function Payment ($Amt, $glt, $ref, $cur, $leg, $id){

/* Constructor function to add a new Receipt object with passed params */
		$this->Amount =$Amt;
		$this->gltemp = $glt;
		$this->reference = $ref;
		$this->currency = $cur;
		$this->legalname = $leg;
		$this->ID = $id;
	}
}

//***************************FIN******************************************
//***************NUEVA CLASE PARA EL MANEJO DE CLIENTES**********************
//**************Y DEPOSITOS DEL MISMO****************************************
//***************************************************************************

//***************************INICIO******************************************
//***************NUEVA CLASE PARA EL MANEJO DE DOCUMENTOS********************
//***************************************************************************
Class DeptorTrans{
	var $legalid;
	var $transno;
	var $totalamount;
	var $allocated;
	var $total;
	var $id;
	
	function DeptorTrans($legalid,$transno, $totalamount, $allocated, $total,$id){
		$this->legalid = $legalid;
		$this->transno = $transno;
		$this->totalamount = $totalamount;
		$this->allocated = $allocated;
		$this->total = $total;
		$this->id = $id;
	}
}

//*****************************FIN*******************************************
//***************NUEVA CLASE PARA EL MANEJO DE DOCUMENTOS********************
//***************************************************************************
?>
