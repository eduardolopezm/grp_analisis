<?php
if (isset($_SESSION['ExistingOrder']) and $_SESSION['ExistingOrder']!=0){
    $OrderNo=$_SESSION['ExistingOrder'];
}else{
    $_SESSION['quotationANT']=1;
}
include('RegistraSecondCredit.inc');

$remisionmake=0;
$totaldesccategory=0;
$tipodefacturacion=14;
	
//variables de configuracion para segunda factura
$OrderNoInicial=$OrderNo;
// registro de ventas en service

// extrae la unidad de negocio y localidad de alamcen que le corresponde a esa unidad de negocio de acuerdo al area de negocio
$qohsql = "SELECT  tagdebtorno 
		   FROM tags
		   WHERE   tagref = '" . $_SESSION['Tagref'] . "'";
$qohresult =  DB_query($qohsql,$db);
$qohrow = DB_fetch_row($qohresult);
$codigoarea=$qohrow[0];


$BranchCode = $qohrow[0];

$OrderNoS = GetNextTransNo(95, $db);
// registrar pedido de venta de service
/*$HeaderSQL = "INSERT INTO notesorders (orderno,debtorno,branchcode,customerref,comments,orddate,ordertype,shipvia,
					deliverto,deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
					freightcost,fromstkloc,deliverydate,quotedate,confirmeddate,quotation,deliverblind,
					salesman,tagref,taxtotal,currcode,paytermsindicator,advance,UserRegister)			
		Select ".$OrderNoS.",debtorno,branchcode,customerref,comments,orddate,ordertype,shipvia,
					deliverto,deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
					freightcost,fromstkloc,'".$FechaEntrega."','".$Fecha."','".$Fecha."',2,deliverblind,
					salesman,tagref,taxtotal,currcode,paytermsindicator,advance,'".$_SESSION['UserID']."'			
		FROM salesorders 
		WHERE orderno='".$OrdenVenta."'";
$ErrMsg='Error al insertar el encabezado de la NC';
$DbgMsg='El SQL es';
$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
*/
$FechaEntrega=$DefaultDispatchDate;//date('Y-m-d');
$Fecha=$DefaultDispatchDate;//date('Y-m-d');
$Fecha=$DefaultDispatchDate;//date('Y-m-d');

 $HeaderSQL = "INSERT INTO notesorders (orderno,debtorno,branchcode,customerref,comments,orddate,ordertype,shipvia,
					    deliverto,deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
					    freightcost,fromstkloc,deliverydate,quotedate,confirmeddate,quotation,deliverblind,
					    salesman,tagref,taxtotal,currcode,paytermsindicator,advance,UserRegister)
		    SELECT ".$OrderNoS.", custbranch.debtorno,custbranch.branchcode,custbranch.taxid,'Automatica',now(),debtorsmaster.salestype,0,custbranch.brname,
				custbranch.braddress1,custbranch.braddress2,custbranch.braddress3,custbranch.braddress4,custbranch.braddress5,custbranch.braddress6,
				custbranch.phoneno,custbranch.email,0,'".$LocCode."','".$FechaEntrega."','".$Fecha."','".$Fecha."',1,0,custbranch.salesman,".$_SESSION['Tagref'].",
				0,'".$_SESSION['CurrAbrev']."',debtorsmaster.paymentterms,0,'admin'
		    FROM custbranch inner join debtorsmaster on debtorsmaster.debtorno=custbranch.debtorno
		    WHERE custbranch.branchcode='".$BranchCode."'";
    $ErrMsg='Error al insertar el encabezado de la venta';
    $DbgMsg='El SQL es';
  //  echo $HeaderSQL;
       // return $HeaderSQL;
    $Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
 


$StartOf_LineItemsSQL = "INSERT INTO notesorderdetails (orderlineno,orderno,stkcode,unitprice,quantity,discountpercent,
							discountpercent1,discountpercent2,narrative,poline,itemdue,
							fromstkloc,warranty)
			SELECT 0,".$OrderNoS.",stockid,'".$_SESSION['Items'.$identifier]->total."',1,0,
							0,0,'".$NarrativeNC."',0,now(),
							'".$LocCode."',0
			FROM stockmaster
			WHERE stockid='ventasocio'";
							
$ErrMsg='Error al insertar el detalle de la NC';
$DbgMsg='El SQL es';
$Result = DB_query($StartOf_LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
  //echo $StartOf_LineItemsSQL;
  
 /**************************************************************/
 /*********REGISTRA NC SERVICE*****************************/
 /**************************************************************/
 $DebtorTransIDRep=$CreditTransID;
$Facturaventa=RegistrarNCredito($OrderNoS,14,$db);
$separa = explode('|',$Facturaventa);
$InvoiceNo = $separa[0];
$tipodefacturacion = $separa[1];
if ($InvoiceNo>0){
	$qohsql = "SELECT  tagref
			   FROM notesorders
			   WHERE   orderno = '" . $OrderNoS . "'";
	$qohresult =  DB_query($qohsql,$db);
	$qohrow = DB_fetch_row($qohresult);
	$TagRef=$qohrow[0];
	
    
	$qohsql = "SELECT  areacode,legalid 
			   FROM tags 
			   WHERE   tagref = '" . $TagRef . "'";
	$qohresult =  DB_query($qohsql,$db);
	$qohrow = DB_fetch_row($qohresult);
	$Area=$qohrow[0];
	$legaid=$qohrow[1];
		
	$InvoiceNoTAG = DocumentNext(14, $TagRef,$Area,$legaid, $db);
	$separa = explode('|',$InvoiceNoTAG);
	$serie = $separa[1];
	$folio = $separa[0];
	
	
	$SQL = "UPDATE notesorders SET quotation=5, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNoS;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
	$codigoref=strtoupper($RFC);
	$tipoarea='01';//add_cerosstring($area,2);
	$translegal='01';//add_cerosstring($legaid,2);
	$cuentareferenciada=$translegal.$tipoarea.$codigoref;
	
	//extrae el id del docto x cobrar
	$qohsql = "SELECT  id,ovamount,ovgst
		    FROM debtortrans WHERE   transno = '" . $InvoiceNo . "' and type=".$tipodefacturacion;
	$qohresult =  DB_query($qohsql,$db);
	$qohrow = DB_fetch_row($qohresult);
	$DebtorTransID=$qohrow[0];
	$Montofactura=$qohrow[1];
	$IVAFactura=$qohrow[2];
	
	//extrae banco activo para cuentas referenciadas
	$sql="Select * from bancosreferencia where active=1";
	$result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($result)!=0)
	{
		while ($myrowcuenta = DB_fetch_array($result,$db)){
			$bankid=$myrowcuenta['bancoid'];
			// genera digito verificador
			$cuentaref = strtoupper($cuentareferenciada.GeneraCuentaReferenciada($db,$cuentareferenciada,$bankid));
			// inserta en tabla de referencias bancarias
			$insertarefe=InsertaCuentaBank($cuentaref,$DebtorTransID,$bankid,$db);				
		}
	}
	//Actualizar el documento para folio
	$SQLfactura="UPDATE debtortrans
	      SET folio='" . $serie.'|'.$folio . "',
		    ref1='" . $cuentaref . "',
		    idinvoice=".$DebtorTransIDRep."
	      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
	$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
	$Result = DB_query($SQLfactura,$db,$ErrMsg,$DbgMsg,true);
	
	
	$SQLfactura="UPDATE notesorders
	      SET taxtotal='" . ($IVAFactura*-1) . "'
	      WHERE orderno=".$OrderNoS;
	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
	$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
	$Result = DB_query($SQLfactura,$db,$ErrMsg,$DbgMsg,true);
	
 }
 prnMsg(_('<B>LA ORDEN DE NC NO.') . ' ' . $OrderNo . ' ' . _('HA SIDO REALIZADA DE MANERA EXITOSA con el siguiente Folio: '.$serie.'|'.$folio),'success');
/***************************************************************************************************************************************/
//******************************************************************************************************************************	
$Result = DB_Txn_Commit($db);
unset($_SESSION['Items'.$identifier]->LineItems);
unset($_SESSION['Items'.$identifier]);
unset($_SESSION['CurrAbrev']);
unset($_SESSION['Tagref']);

include('includes/footer.inc');
exit;


?>