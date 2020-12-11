<?php
/* $Revision: 1.6 $ */
/* definition of the ReceiptBatch class */

/* ARCHIVO MODIFICADO POR: FRUEBEL CANTERA*/
/* FECHA DE MODIFICACION: 14-DIC-2009 */
/* CAMBIOS:*/
/* 1.- MUCHOS... */
/* FIN DE CAMBIOS*/

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

	function add_payment($Amount, $glt, $ref, $cur, $leg, $tipocambio, $conversion, $tag){
		$this->Items[$this->ItemCounter] = new Payment($Amount, $glt, $ref, $cur, $leg, $this->ItemCounter,$tipocambio,$conversion,$tag);
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
	var $tipocambio;
	var $conversion;
	var $tag;
	var $ID;
	

	function Payment ($Amt, $glt, $ref, $cur, $leg, $id, $tipocambio, $conversion, $tag){

/* Constructor function to add a new Receipt object with passed params */
		$this->Amount =$Amt;
		$this->gltemp = $glt;
		$this->reference = $ref;
		$this->currency = $cur;
		$this->legalname = $leg;
		$this->ID = $id;
		$this->tipocambio = $tipocambio;
		$this->conversion = $conversion;
		$this->tag = $tag;
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


/*INICIO
 ***CREACION DE NUEVA CLASE PARA EL MANEJO DE RAZONES SOCIALES, DOCUMENTOS
 ***22/DIC/2009
 */
Class RazonSocial {

	var $Documentos;
	var $Anticipos;
	var $legalid; 
	var $legalname;
	var $tag;
	var $tagdescription;
	//var $Anticipos;
	//var $Ingresos;
	var $MontoxPagos; //Monto por pagos (efectivo, tdc, cheques, transferencia)
	var $MontoxAnticipos; //Monto por anticipos
	var $MontoTotalAbono; // Suma del monto de pagos  + monto por anticipos
	var $MontoTotalAsignado; //Monto total asignado para el pago de documentos
	var $MontoPorAsignar; //Monto que falta por asignar
	var $MontoTotal; //Monto total de los documentos a pagar
	var $DocContador; //Numero de documentos
	var $AntContador; //Numero de documentos

	function RazonSocial($legalid, $legalname, $tag, $tagdescription){
	/*Constructor function initialises a new receipt batch */
		$this->Documentos = array();
		$this->Anticipos = array();
		$this->legalid = $legalid;
		$this->legalname = $legalname;
		$this->tag = $tag;
		$this->tagdescription = $tagdescription;
		$this->DocContador = 0;
		$this->AntContador = 0;
		$this->MontoxPagos = 0;
		$this->MontoxAnticipos = 0;
		$this->MontoTotalAbono = 0;
		$this->MontoTotalAsignado = 0;
		$this->MontoPorAsignar = 0;
		$this->MontoTotal = 0;
		$this->MontoTotalAnticipos = 0;
	}

	function add_to_docs($id,$type,$typename,$transno,$branchcode,$trandate,$reference,$invtext,$order_,$rate,$tagref, $totalamount,
			 $allocated,$diffonexch,$ovgst,$ovamount, $porpagar,$diasvencimiento,$aplicarmoratorios,$moratorios, $abono,
			 $aplicartodo,$numcuenta,$folio,$uso,$moneda,$porpagarconv,$moratoriosconv,$tagdescription)
	{
		$this->Documentos[$this->DocContador] = new Doctos($id,$type,$typename,$transno,$branchcode,$trandate,$reference,$invtext,$order_,$rate,$tagref, $totalamount,
			 $allocated,$diffonexch,$ovgst,$ovamount, $porpagar,$diasvencimiento,$aplicarmoratorios,$moratorios,$abono,$aplicartodo,
			 $numcuenta,$folio,$uso,$moneda,$porpagarconv,$moratoriosconv,$porpagarconvorig,$moratoriosconvorig,$tagdescription,$this->DocContador);
		
		$this->DocContador++;
		$this->MontoTotal = $this->MontoTotal + $porpagar;
		Return 1;
	}
	
	function add_to_ants($id,$type,$typename,$transno,$branchcode,$trandate,$reference,$invtext,$order_,$rate,$tagref, $totalamount,
			 $allocated,$diffonexch,$ovgst,$ovamount, $porpagar,$diasvencimiento,$aplicarmoratorios,$moratorios, $abono,
			 $aplicartodo,$numcuenta,$folio,$uso,$moneda,$porpagarconv,$moratoriosconv,$tagdescription)
	{
		$this->Anticipos[$this->AntContador] = new Anticipos($id,$type,$typename,$transno,$branchcode,$trandate,$reference,$invtext,$order_,$rate,$tagref, $totalamount,
			 $allocated,$diffonexch,$ovgst,$ovamount, $porpagar,$diasvencimiento,$aplicarmoratorios,$moratorios,$abono,$aplicartodo,
			 $numcuenta,$folio,$uso,$moneda,$porpagarconv,$moratoriosconv,$tagdescription,$this->DocContador);
		$this->AntContador++;
		$this->MontoTotalAnticipos = $this->MontoTotalAnticipos + $porpagar;
		Return 1;
	}
	
} /* end of class defintion */

Class Doctos {
	var $id;
	var $type;
	var $typename;
	var $transno;
	var $branchcode;
	var $trandate;
	var $reference;
	var $invtext;
	var $order_;
	var $rate;
	var $tagref;
	var $totalamount;
	var $allocated;
	var $diffonexch;
	var $ovgst;
	var $ovamount;
	var $porpagar;
	var $diasvencimiento;
	var $aplicarmoratorios;
	var $moratorios;
	var $abono;
	var $aplicartodo;
	var $uso; /*se utiliza para determinar si es documento por pagar, o anticipo*/
	var $numcuenta;
	var $folio;
	var $moneda;
	var $porpagarconv;
	var $moratoriosconv;
	var $porpagarconvorig;
	var $moratoriosconvorig;
	var $porcdocporpagar;
	var $porcganporpagar;
	var $porcdocmoratorios;
	var $porcganmoratorios;
	var $tagdescription;
	var $indice;
	

	function Doctos ($id,$type,$typename,$transno,$branchcode,$trandate,$reference,$invtext,$order_,$rate,$tagref, $totalamount,
			 $allocated,$diffonexch,$ovgst,$ovamount, $porpagar,$diasvencimiento,$aplicarmoratorios,$moratorios, $abono,
			 $aplicartodo,$numcuenta,$folio,$uso,$moneda,$porpagarconv,$moratoriosconv,$porpagarconvorig,$moratoriosconvorig,
			 $tagdescription, $indice){

		$this->id = $id;
		$this->type = $type;
		$this->typename = $typename;
		$this->transno = $transno;
		$this->branchcode = $branchcode;
		$this->trandate = $trandate;
		$this->reference = $reference;
		$this->invtext = $invtext;
		$this->order_ = $order_;
		$this->rate = $rate;
		$this->tagref = $tagref;
		$this->totalamount = $totalamount;
		$this->allocated = $allocated;
		$this->diffonexch = $diffonexch;
		$this->ovgst = $ovgst;
		$this->ovamount = $ovamount;
		$this->porpagar = $porpagar;
		$this->diasvencimiento = $diasvencimiento;
		$this->aplicarmoratorios = $aplicarmoratorios;
		$this->moratorios = $moratorios;
		$this->abono = $abono;
		$this->aplicartodo = $aplicartodo;
		$this->numcuenta = $numcuenta;
		$this->folio = $folio;
		$this->uso = $uso;
		$this->moneda = $moneda;
		$this->porpagarconv = $porpagarconv;
		$this->moratoriosconv = $moratoriosconv;
		$this->porpagarconvorig = $porpagarconvorig;
		$this->moratoriosconvorig = $moratoriosconvorig;
		$this->porcdocporpagar = 0;
		$this->porcganporpagar = 0;
		$this->porcdocmoratorios = 0;
		$this->porcganmoratorios = 0;
		$this->tagdescription = $tagdescription;
		$this->indice = $indice;
		
	}
}

Class Anticipos {
	var $id;
	var $type;
	var $typename;
	var $transno;
	var $branchcode;
	var $trandate;
	var $reference;
	var $invtext;
	var $order_;
	var $rate;
	var $tagref;
	var $totalamount;
	var $allocated;
	var $diffonexch;
	var $ovgst;
	var $ovamount;
	var $porpagar;
	var $diasvencimiento;
	var $aplicarmoratorios;
	var $moratorios;
	var $abono;
	var $aplicartodo;
	var $uso; /*se utiliza para determinar si es documento por pagar, o anticipo*/
	var $numcuenta;
	var $folio;
	var $moneda;
	var $porpagarconv;
	var $moratoriosconv;
	var $tagdescription;
	var $indice;
	
	function Anticipos ($id,$type,$typename,$transno,$branchcode,$trandate,$reference,$invtext,$order_,$rate,$tagref, $totalamount,
			 $allocated,$diffonexch,$ovgst,$ovamount, $porpagar,$diasvencimiento,$aplicarmoratorios,$moratorios, $abono,
			 $aplicartodo,$numcuenta,$folio,$uso,$moneda,$porpagarconv,$moratoriosconv, $tagdescription,$indice){

		$this->id = $id;
		$this->type = $type;
		$this->typename = $typename;
		$this->transno = $transno;
		$this->branchcode = $branchcode;
		$this->trandate = $trandate;
		$this->reference = $reference;
		$this->invtext = $invtext;
		$this->order_ = $order_;
		$this->rate = $rate;
		$this->tagref = $tagref;
		$this->totalamount = $totalamount;
		$this->allocated = $allocated;
		$this->diffonexch = $diffonexch;
		$this->ovgst = $ovgst;
		$this->ovamount = $ovamount;
		$this->porpagar = $porpagar;
		$this->diasvencimiento = $diasvencimiento;
		$this->aplicarmoratorios = $aplicarmoratorios;
		$this->moratorios = $moratorios;
		$this->abono = $abono;
		$this->aplicartodo = $aplicartodo;
		$this->numcuenta = $numcuenta;
		$this->folio = $folio;
		$this->uso = $uso;
		$this->moneda = $moneda;
		$this->porpagarconv = $porpagarconv;
		$this->moratoriosconv = $moratoriosconv;
		$this->tagdescription = $tagdescription;
		$this->indice = $indice;
	}
}


/*FIN
 ***CREACION DE NUEVA CLASE PARA EL MANEJO DE RAZONES SOCIALES, DOCUMENTOS
 ***22/DIC/2009
 */


//***FUNCIONES DE INSERCION EN GLTRANS, DEBTORTRANS, DEBTORTRANSMOVS

function insertagltrans($db, $type, $typeno, $periodo, $account, $narrative, $tag, $amount){
	$ISQL="INSERT INTO gltrans (type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			tag,
			amount)
		VALUES (" . $type . ",
			" . $typeno . ",
			Now(),
			" . $periodo . ",
			'" . $account . "',
			'" . $narrative . "',
			" . $tag . ",
			" . $amount . ')';
			
	$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
	$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
	$Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
	$IDgltrans = DB_Last_Insert_ID($db,'gltrans','counterindex');
	return $IDgltrans;
}


function insertadebtortrans($db, $trasno, $tagref, $type, $debtorno, $branchcode,  $prd, $reference, $tpe, $order_, $ovamount,$ovgst, $ovfreight,$rate,$invtext,$shipvia,$consignment,$alloc,$currcode){
	
	$ISQL = "INSERT INTO debtortrans (
			transno,
			tagref,
			type,
			debtorno,
			branchcode,
			trandate,
			prd,
			reference,
			tpe,
			order_,
			ovamount,
			ovgst,
			ovfreight,
			rate,
			invtext,
			shipvia,
			consignment,
			alloc,
			origtrandate,
			currcode
			)
 		VALUES (
			". $trasno . ",
			". $tagref . ",
			" . $type . ",
			'" . $debtorno . "',
			'" . $branchcode . "',
			Now(),
			" . $prd . ",
			'" . $reference . "',
			'" . $tpe . "',
			'" .  $order_ . "',
			" . $ovamount . ",
			" . $ovgst . ", 
			" . $ovfreight . ",
			" . $rate . ",
			'" . $invtext . "',
			'" . $shipvia ."',
			'" . $consignment . "',
			" . $alloc . ",
			Now(),
			'" . $currcode . "'
		)";

	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla debtortrans debido a ');
	$DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
	$Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);

	$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');
				
	return $DebtorTransID;
}

function insertadebtortransmovs($db, $trasno, $tagref, $type, $debtorno, $branchcode,  $prd, $reference, $tpe, $order_, $ovamount,$ovgst, $ovfreight,$rate,$invtext,$shipvia,$consignment,$alloc,$currcode,$idgltrans,$userid){
	$ISQL = "INSERT INTO debtortransmovs (
			transno,
			tagref,
			type,
			debtorno,
			branchcode,
			trandate,
			prd,
			reference,
			tpe,
			order_,
			ovamount,
			ovgst,
			ovfreight,
			rate,
			invtext,
			shipvia,
			consignment,
			alloc,
			origtrandate,
			currcode,
			idgltrans,
			userid
			)
 		VALUES (
			". $trasno . ",
			". $tagref . ",
			" . $type . ",
			'" . $debtorno . "',
			'" . $branchcode . "',
			Now(),
			" . $prd . ",
			'" . $reference . "',
			'" . $tpe . "',
			'" .  $order_ . "',
			" . $ovamount . ",
			" . $ovgst . ", 
			" . $ovfreight . ",
			" . $rate . ",
			'" . $invtext . "',
			'" . $shipvia ."',
			'" . $consignment . "',
			" . $alloc . ",
			Now(),
			'" . $currcode . "',
			" . $idgltrans . ",
			'" . $userid . "'
		)";

	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla debtortrans debido a ');
	$DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
	$Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);

	//$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');
				
	//return $DebtorTransID;
}

function convmoneda($cantidad, $currorig, $rateorig, $currdest, $ratedest){
	if (($currorig == 'MXN') and ($currdest=='USD')){
		$tipocambio = ($rateorig / $ratedest) - (($rateorig / $ratedest ) * 0.05);
	}elseif (($currorig == 'USD') and ($currdest=='MXN')){
		$tipocambio = ($rateorig / $ratedest ) - (($rateorig / $ratedest ) * 0.05);
	}else{
		$tipocambio = ($rateorig / $ratedest);
	}
	$arrconversiones = array();
	$convorig = $cantidad/($rateorig / $ratedest);
	
	if ($currorig == $currdest){
		$convorig = $cantidad;
	}else{
		$convorig = $cantidad/($rateorig / $ratedest);
	}

	if (($currorig == 'MXN') and ($currdest=='USD')){
		$conversion = ceil($cantidad / $tipocambio);
	}elseif (($currorig == 'USD') and ($currdest=='MXN')){
		$conversion = floor($cantidad / $tipocambio);
	}elseif ($currorig == $currdest){
		$conversion = $cantidad;
	}else{
		$conversion = $cantidad / $tipocambio;
	}
	
	if ($currorig == $currdest){
		$porcdoc = 1;
		$porcgan = 0;
	}else{
		if ($conversion != 0){
			$porcdoc = (100 - ((($conversion - $convorig) * 100) / $conversion))/100;
			$porcgan = ((($conversion - $convorig) * 100) / $conversion)/100;
		}else{
			$porcdoc = 0;
			$porcgan = 0;
		}	
	}

	$arrconversiones[1] = $convorig;
	$arrconversiones[2] = $conversion;
	$arrconversiones[3] = $porcdoc;
	$arrconversiones[4] = $porcgan;
	return $arrconversiones;
}


function convmonedaabono($porpagar, $porpagarconv, $abono, $abonoconv){
//en moneda original, en moneda conv, en moneda conv, en moneda original	
	$abonoconv = ($porpagar*$abono) / $porpagarconv;
	return $abonoconv;
}

?>
