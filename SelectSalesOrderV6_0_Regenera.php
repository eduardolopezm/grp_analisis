<?php
/*


CGM 12/09/2013 Alta de version con redise�o de manejo de status, fechas de actualizacion, 
				se agrega formato impreso con uso de xml e interpretacion de jasper


*/
//error_reporting(E_ALL);
//ini_set('display_errors', '1');//

if($_SESSION['UserID'] == 'admin'){
 	error_reporting(E_ALL);
 	ini_set('display_errors', '1');
}

$XSAInvoicing="XSAInvoicing.inc";
$SendInvoicing="SendInvoicingV6_0.php";
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Busqueda de Pedidos de Venta');
/*****************************************/
//Archivos utilizados en esta pagina
/*****************************************/
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$funcion=602;
include('includes/SecurityFunctions.inc');
include ('Numbers/Words.php');
//para cuentas referenciadas
include('includes/Functions.inc');
include ('includes/'.$XSAInvoicing);
include ('includes/'.$SendInvoicing);
//regeneracion de XML
//include('jasper/JasperTemplate.php');
// Incluye la funci���������������������������n emailNotifier
require_once('includes/DeliveryEmailNotifier.inc');
/**************************************/
//Permisos utilizados en esta pagina
/**************************************/
$altapedido=Havepermission($_SESSION['UserID'],171, $db);
$paginapedidos=HavepermissionURL($_SESSION['UserID'],4, $db);

$paginabusquedapedidos=HavepermissionURL($_SESSION['UserID'],602, $db);
$oportunidadprospectoPermiso=Havepermission($_SESSION['UserID'],2210, $db);
$oportunidadprospecto=HavepermissionURL($_SESSION['UserID'],2210, $db);
$permisouser=Havepermission($_SESSION['UserID'],860, $db);
$permisoremision=Havepermission($_SESSION['UserID'],224, $db);
$permisocancelar=Havepermission($_SESSION['UserID'],196, $db);
$enviaventaperdida=Havepermission($_SESSION['UserID'],203, $db);
$cancelarfactura=Havepermission($_SESSION['UserID'],177, $db);
$modificartrabajadores=Havepermission($_SESSION['UserID'],302, $db);
$modificarvendedores=Havepermission($_SESSION['UserID'],985, $db);
$permisoaperturacerrado=Havepermission($_SESSION['UserID'],217, $db);
$add_datosfactura=Havepermission($_SESSION['UserID'],3014, $db);
$mod_datosfactura = Havepermission($_SESSION['UserID'],3014, $db);

/**************************************/

if ($altapedido==1) {
	echo '<p><a href="' . $rootpath . '/'.$paginapedidos.'?' . SID . '&NewOrder=Yes"><font size=2><b>' . _('Nuevo Pedido de Venta') . '</a>';
}
unset($listaloccxbussines);
/*verifica sus unidades de negocio*/
$sql="SELECT t.tagref, t.tagdescription
		FROM tags t, sec_unegsxuser uxu
		WHERE t.tagref=uxu.tagref
		AND uxu.userid='".$_SESSION['UserID']."'
	   ORDER BY tagdescription";
$resultTags = DB_query($sql,$db,'','');
$listaloccxbussines=array();
$counter_bussines=0;
while ($myrow_bussines = DB_fetch_array($resultTags)) {
	$listaloccxbussines[$counter_bussines]=$myrow_bussines['tagdescription'];
	$counter_bussines=$counter_bussines + 1;
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Pedidos de Ventas') . '" alt="">' . ' ' . _('BUSQUEDA DE PEDIDOS DE VENTA') . '</p> ';
if (isset($_POST['FromYear'])) {
	$FromYear=$_POST['FromYear'];
} elseif(isset($_GET['FromYear'])) {
	$FromYear=$_GET['FromYear'];
}else{
	$FromYear=date('Y');
}
if (isset($_POST['FromMes'])) {
	$FromMes=$_POST['FromMes'];
} elseif(isset($_GET['FromMes'])) {
	$FromMes=$_GET['FromMes'];
}else{
	$FromMes=date('m');
}
if (isset($_GET['FromDia'])) {
	$FromDia=$_GET['FromDia'];
}elseif(isset($_POST['FromDia'])) {
	$FromDia=$_POST['FromDia'];
}else{
	$FromDia=date('d');
}
if (isset($_POST['ToYear'])) {
	$ToYear=$_POST['ToYear'];
} elseif(isset($_GET['ToYear'])) {
	$ToYear=$_GET['ToYear'];
}else{
	$ToYear=date('Y');
}
if (isset($_POST['ToMes'])) {
	$ToMes=$_POST['ToMes'];
} elseif(isset($_GET['ToMes'])) {
	$ToMes=$_GET['ToMes'];
}else{
	$ToMes=date('m');
}
if (isset($_GET['ToDia'])) {
	$ToDia=$_GET['ToDia'];
} elseif(isset($_POST['ToDia'])) {
	$ToDia=$_POST['ToDia'];
}else{
	$ToDia=date('d');
}
if (isset($_POST['UnidNeg'])) {
	$UnidNeg=$_POST['UnidNeg'];
} elseif(isset($_GET['UnidNeg'])) {
	$UnidNeg=$_GET['UnidNeg'];
}
if (isset($_POST['SalesMan'])) {
	$SalesMan=$_POST['SalesMan'];
} elseif(isset($_GET['SalesMan'])) {
	$SalesMan=$_GET['SalesMan'];
}
if (isset($_POST['UserName'])) {
	$UserName=$_POST['UserName'];
} elseif(isset($_GET['UserName'])) {
	$UserName=$_GET['UserName'];
}
if (isset($_POST['Quotations'])){
	$Quotations = $_POST['Quotations'];
} else {
	$Quotations=1;
}
$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
$fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
$fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
$fechafinc=mktime(0,0,0,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
$InputError = 0;
if ($fechainic > $fechafinc){
	$InputError = 1;
	prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
} else {
	$InputError = 0;
}
$orden=$_GET['orderno'];
$orden=$_GET['orderno'];
$tagrefen=$_GET['tagrefen'];
$folio=$_GET['folio'];
$serie=$_GET['serie'];
$debtorno=$_GET['debtorno'];
$tipo=$_GET['tipo'];
$transno=$_GET['transno'];
$iddocto=$_GET['iddocto'];
$ventaperdida=$_GET['ventaperdida'];
/*****************************************/
echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';
echo '<input type=hidden name="fechaini" VALUE="' . $fechaini . '">
		<input type=hidden name="fechafin" VALUE="' . $fechafin . '">';
echo '<p><div class="centre">';
/*****************************************/
//Operaciones en base de datos
/*****************************************/
if (isset($orden) and isset($ventaperdida) and $ventaperdida=='yes'){
	// Inicializo transaccion
	$Result = DB_Txn_Begin($db);
	//Actualizo pedio de venta a facturado
	$SQL = "UPDATE salesorders
			SET quotation =4
			WHERE orderno= " .  $orden;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	//Actualizo cantidades de venta perdida
	$SQL = "UPDATE salesorderdetails
			SET qtylost=(quantity-qtyinvoiced),
			quantity=qtyinvoiced,
			datelost=now()
			WHERE (quantity-qtyinvoiced)>0 and orderno= " .  $orden;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	//Finaliza transaccion
	$Result = DB_Txn_Commit($db);
	prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha convertido a facturado y la cantidad restante de los productos se considerara como venta perdida'),'success');
	//Redirecciono a pagina de pedidos
	echo '<meta http-equiv="Refresh" content="2; url=' . $rootpath .
	'/'.$paginabusquedapedidos.'?OrderNumber=' . $orden . '&UnidNeg=' . $tagrefen
	. '&FromDia=' . $FromDia
	. '&FromMes=' . $FromMes
	. '&FromYear=' . $FromYear
	. '&ToDia=' . $ToDia
	. '&ToMes=' . $ToMes
	. '&ToYear=' . $ToYear
	. '&Quotations=4'
			.'">';
}
//Cancelacion de pedido de venta
if (isset($orden) and !isset($folio)){
	$SQL = "Select *
			From salesorders
			where salesorders.orderno = '".$orden."'
					And salesorders.quotation = 0";
	
	$result = DB_query($SQL, $db);
	if(DB_num_rows($result) > 0){
		//
		// Inicializo transaccion
		$Result = DB_Txn_Begin($db);
		$myrow = DB_fetch_array($result);
		$qry = "Select wo FROM workorders WHERE orderno = '".$orden."'";
		$rsw = DB_query($qry,$db);
		if (Db_num_rows($rsw) > 0){
			
			$resultw = DB_Txn_Begin($db);
			while ($regs = DB_fetch_array($rsw)){
				
				$sql = "select stockmaster.description as producto, qtyreqd, qtyrecd
						from woitems inner join stockmaster ON woitems.stockid = stockmaster.stockid
						where wo = ".$regs[0]." and (qtyrecd = 0 or qtyrecd is null)";
				$rsx = DB_query($sql,$db);
				if (DB_num_rows($rsx) > 0){
					
					$sqlcalendar = "Select *
							From mrp_calendar_AM_detail
							Where mrp_calendar_AM_detail.serie = '".$regs[0]."'";
					$resultcalendar = DB_query($sqlcalendar, $db);
					if(DB_num_rows($resultcalendar) > 0){
						
						while($myrowesquema = DB_fetch_array($resultcalendar)){
							
							$sqlesquematizado = "Delete
									From mrp_calendar_AM_detail
									Where mrp_calendar_AM_detail.serie = '".$myrowesquema['serie']."'";
							$Result = DB_query($sqlesquematizado, $db);
						}//
					}
					$qry1 = "DELETE FROM workorders WHERE wo = ".$regs[0];
					$Result = DB_query($qry1,$db);
					$qry1 = "DELETE FROM woitems WHERE wo = ".$regs[0];
					$Result = DB_query($qry1,$db);
					$qry1 = "DELETE FROM worequirements WHERE wo = ".$regs[0];//
					$Result = DB_query($qry1,$db);
					$resultw = DB_Txn_Commit($db);
					// Actualiza estatus de pedido
					$SQL = "UPDATE salesorders
							SET quotation =3
							WHERE orderno= " .  $orden;
					$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
					$DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					$SQL = "UPDATE salesdate
							SET fecha_cancelado = NOW(),
								usercancelado = '".$_SESSION['UserID']."'
							WHERE orderno= " .  $orden;
					$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
					$DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha cancelado'),'success');
					emailNotifier('Cancelar Pedido', $orden, $db);
				}

			}
		}else{
			
			$SQL = "UPDATE salesorders
					SET quotation =3
					WHERE orderno= " .  $orden;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha cancelado'),'success');
			emailNotifier('Cancelar Pedido', $orden, $db);
		}
		$Result = DB_Txn_Commit($db);
		prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha convertido a facturado y la cantidad restante de los productos se considerara como venta perdida'),'success');
		//Redirecciono a pagina de pedidos
		
		echo '<meta http-equiv="Refresh" content="2; url=' . $rootpath .
		'/'.$paginabusquedapedidos.'?OrderNumber=' . $orden . '&UnidNeg=' . $tagrefen
		. '&FromDia=' . $FromDia
		. '&FromMes=' . $FromMes
		. '&FromYear=' . $FromYear
		. '&ToDia=' . $ToDia
		. '&ToMes=' . $ToMes
		. '&ToYear=' . $ToYear
		. '&Quotations=3'
				.'">';
	}
}elseif(isset($orden) and isset($folio) and isset($iddocto)){
	$SQL="SELECT debtortrans.*, year(origtrandate) as aniodocto, systypescat.EnvioFiscal
			FROM debtortrans inner join systypescat ON systypescat.typeid=debtortrans.type
			WHERE id=".$iddocto;
	if($_SESSION['UserID'] == "admin"){
		echo $SQL;
	}
	$ResultDatos=DB_query($SQL,$db,$ErrMsg,'',false,true);
	while ($MyrowDatos=DB_fetch_array($ResultDatos)) {
		$InvoiceNo=$MyrowDatos['transno'];
		$OrderNox=$MyrowDatos['order_'];
		$DebtorNo=$MyrowDatos['debtorno'];
		$tipodefacturacion=$MyrowDatos['type'];
		$Tagref=$MyrowDatos['tagref'];
		$InvoiceNoTAG=$MyrowDatos['folio'];
		$separa = explode('|',$InvoiceNoTAG);
		$DebtorTransID=$MyrowDatos['id'];
		$serie = $separa[0];
		$folio = $separa[1];
		$factelectronica= XSAInvoicing($InvoiceNo, $OrderNox, $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
		$factelectronica=utf8_encode($factelectronica);
		$enviofiscal=$MyrowDatos['EnvioFiscal'];
		//	echo '<pre><br>'.$factelectronica;

		$aniodocto=$MyrowDatos['aniodocto'];
		$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice,l.keysend,l.legalname
				FROM legalbusinessunit l, tags t
				WHERE l.legalid=t.legalid AND tagref='".$Tagref."'";
		$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$rfc=trim($myrowtags['taxid']);
			$keyfact=$myrowtags['keysend'];
			$nombre=$myrowtags['tagname'];
			$area=$myrowtags['areacode'];
			$legaid=$myrowtags['legalid'];
			$tipofacturacionxtag=$myrowtags['typeinvoice'];
			$legalname=$myrowtags['legalname'];
		}
		$param=array('in0'=>$empresa, 'in1'=>$nombre,'in2'=>$tipo,'in3'=>$myfile,'in4'=>$factelectronica);
		if($tipofacturacionxtag==2){
			//	echo "Generando XMLS...";
			$arrayGeneracion=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);

			$XMLElectronico=$arrayGeneracion["xml"];
			//echo '<pre>xml:<br>'. htmlentities($XMLElectronico);
			//exit;
			//Se agrega la generacion de xml_intermedio
			$array=generaXMLIntermedio($factelectronica,$XMLElectronico,$arrayGeneracion["cadenaOriginal"],$arrayGeneracion["cantidadLetra"],$OrderNox,$db,1,$Tagref,$tipodefacturacion,$transno);
			$xmlImpresion= $array["xmlImpresion"];
			$rfcEmisor=$array["rfcEmisor"];
			$fechaEmision=$array["fechaEmision"];
			// Inicializo transaccion
			$Result = DB_Txn_Begin($db);
			if($transno==null || empty($transno)){
				$transno=$InvoiceNo;
			}
			$query="SELECT idXml from Xmls  where transNo=".$transno ." and type=".$tipodefacturacion;
			$result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);
			if (DB_num_rows($result)>0) {
				$query="UPDATE Xmls SET xmlImpresion='".($xmlImpresion)."' where transNo=".$transno ." and type=".$tipodefacturacion;
			}else {
				$query="INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion,fiscal) VALUES(".$transno.",".$tipodefacturacion.",'".$rfcEmisor."','".$fechaEmision."','".$XMLElectronico."','".$xmlImpresion."','".$enviofiscal."');";
			}
			
			$Result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);
			// Finalizo transaccion
			$Result = DB_Txn_Commit($db);
		}if($tipofacturacionxtag==1){
			try{
				$client = new SoapClient($_SESSION['XSA']."xsamanager/services/FileReceiverService?wsdl");
				$codigo=$client->guardarDocumento($param);
				$paramx=array('in0'=>$rfc, 'in1'=>$myfile,'in2'=>$keyfact);
				while ($mensajeestatus=='ENPROCESO'){
					sleep(5);
					$codigofact=$client->obtenerEstadoDocumento($paramx);
					$mensajeestatus=$codigofact->out;
					$cuentastatus= $cuentastatus+1;
				}
			}catch (SoapFault $exception) {
				$errorMessage = $exception->getMessage();
				echo 'error:'.$errorMessage;
			}
			//exit;
		}elseif($tipofacturacionxtag==4){
			$success 	    = false;
			$config			= $_SESSION;
			
			
			
			$existeXML = 0;
			
			
			if($existeXML == 0){
				
				$arrayGeneracion=generaXMLCFDI($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);
				$XMLElectronico = $arrayGeneracion['xml'];
				if($_SESSION['UserID'] == 'admin'){
					echo htmlentities($arrayGeneracion['xml']);
				}
				$XMLElectronico = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $XMLElectronico);
				include_once 'timbradores/TimbradorFactory.php';
				
				$timbrador = TimbradorFactory::getTimbrador($config);
				if($timbrador != null) {
					echo 'rfc:'.$rfc;
					$timbrador->setRfcEmisor($rfc);
					$timbrador->setDb($db);
					$cfdi = $timbrador->timbrarDocumento($XMLElectronico);
					if($_SESSION['UserID']=='admin'){
						//echo '<br>'.$factelectronica;
						//echo '<pre><br>'.htmlentities($XMLElectronico);
					}
					
					$success = ($timbrador->tieneErrores() == false);
					foreach($timbrador->getErrores() as $error) {
						prnMsg($error, 'error');
					}
				} else {
					prnMsg(_('No hay un timbrador configurado en el sistema'), 'error');
				}
			}
		   if($success) {
				$DatosCFDI = TraeTimbreCFDI($cfdi);
				if($_SESSION['UserID']=='admin'){
					//echo '<pre>'.htmlentities($cfdi);
				}
				if (strlen($DatosCFDI['FechaTimbrado'])>0){
					if($existeXML == 0){
						$cadenatimbre='||1.0|'.$DatosCFDI['UUID'].'|'.$DatosCFDI['FechaTimbrado'].'|'.$DatosCFDI['selloCFD'].'|'.$DatosCFDI['noCertificadoSAT'].'||';
						// guardamos el timbre fiscal en la base de datos para efectos de impresion de datos
						// Inicializo transaccion
						$Result = DB_Txn_Begin($db);
						$sql="UPDATE debtortrans
							SET fechatimbrado='".$DatosCFDI['FechaTimbrado']."',
							  uuid='".$DatosCFDI['UUID']."',
							  		timbre='".$DatosCFDI['selloSAT']."',
							  				cadenatimbre='".$cadenatimbre."'
							  						WHERE id=".$DebtorTransID;
						$ErrMsg=_('El Sql que fallo fue');
						$DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
						$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
						// Finalizo transaccion
						$Result = DB_Txn_Commit($db);
						$XMLElectronico = $cfdi;
						//echo '<pre><br>xmluno   :<br>'.htmlentities($XMLElectronico);
					}

					$array=generaXMLIntermedio($factelectronica,$XMLElectronico,$cadenatimbre,$arrayGeneracion["cantidadLetra"],$OrderNox,$db,1,$Tagref,$tipodefacturacion,$transno);
					$xmlImpresion= $array["xmlImpresion"];
					$rfcEmisor=$array["rfcEmisor"];
					$fechaEmision=$array["fechaEmision"];
					// Inicializo transaccion
					$Result = DB_Txn_Begin($db);
					if($transno==null || empty($transno)){
						$transno=$InvoiceNo;
					}
					$query="SELECT idXml from Xmls  where transNo=".$transno ." and type=".$tipodefacturacion;
					$result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);
					if (DB_num_rows($result)>0) {
						$query="UPDATE Xmls SET xmlImpresion='".$xmlImpresion."' where transNo=".$transno ." and type=".$tipodefacturacion;
					}else {
						$query="INSERT INTO Xmls(transNo,type,rfcEmisor,fechaEmision,xmlSat,xmlImpresion) VALUES(".$transno.",".$tipodefacturacion.",'".$rfcEmisor."','".$fechaEmision."','".$XMLElectronico."','".$xmlImpresion."');";
					}
					$Result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);
					// Finalizo transaccion
					$Result = DB_Txn_Commit($db);
					//echo '<pre><br>xml:<br>'.htmlentities($xmlImpresion);
					
					//exit;

					//Guardamos el XML una vez que se agrego el timbre fiscal
					$carpeta='Facturas';
					$dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/XML/".$carpeta."/";
					$nufa = $serie.$folio;
					$mitxt=$dir.$nufa.".xml";
					unlink($mitxt);
					$fp = fopen($mitxt,"w");
					fwrite($fp,$XMLElectronico);
					fclose($fp);
					$fp = fopen($mitxt . '.COPIA',"w");
					fwrite($fp,$XMLElectronico);
					fclose($fp);
					$liga="PDFInvoice.php?&clave=chequepoliza_sefia";
					$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a target="_blank" target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo='.$OrderNox.'&TransNo=' . $InvoiceNo .'&Type='.$tipodefacturacion.'&Tagref='.$Tagref.'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
				}else{
					prnMsg(_('No fue posible realizar el timbrado del documento, verifique con el administrador; el numero de error es:').$cfdi,'error');
					//exit;
				}
			}
		}
	}
}
//Unifiacion de pedido de venta
// se unifica tomando los datos del primer pedido seleccionado
if (isset($_POST['btnUnificar'])){
	$arrdata = $_POST['selectedorders'];
	$arritems = array();
	// Inicializo transaccion
	$Result = DB_Txn_Begin($db);
	if (count($arrdata) > 0){
		$neworder = GetNextTransNo(30, $db);
		$headerdone = false;
		$itemlineno=0;
		$taxtotal=0;

		foreach($arrdata as $value){
			$orderNo = $value;
			//buscar el taxtotal de cada orden a unificar
			$sql = "Select taxtotal FROM salesorders
					WHERE orderno = ".$orderNo;
			$rtax = DB_query($sql,$db);
			$rowtax = DB_fetch_array($rtax);
			$taxtotal+= $rowtax[0];
			if (!$headerdone){
				//buscar oprtunidad
				$sql = "Select idprospect FROM salesorders
				WHERE orderno = $orderNo" ;
				$res = DB_query($sql,$db);
				$reg = DB_fetch_array($res);
				$umov = $reg[0];
				//insertar encabezado de la nueva orden
				$HeaderSQL = "INSERT INTO salesorders ( orderno,debtorno,branchcode,customerref,comments,
						orddate,ordertype,shipvia,deliverto,deladd1,deladd2,
						deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
						freightcost,fromstkloc,deliverydate,quotedate,confirmeddate,
						quotation,deliverblind,salesman,placa,serie,kilometraje,tagref,
						taxtotal,totaltaxret,currcode,paytermsindicator,advance,UserRegister,
						puestaenmarcha,paymentname,nocuenta,extratext,nopedido,noentrada,
						noremision,idprospect,contid,typeorder,deliverytext
						)
						SELECT  ".$neworder.",debtorno,branchcode,customerref,comments,
								current_date,ordertype,shipvia,deliverto,deladd1,deladd2,
								deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
								freightcost,fromstkloc,deliverydate,quotedate,confirmeddate,
								quotation,deliverblind,salesman,placa,serie,kilometraje,tagref,
								taxtotal,totaltaxret,currcode,paytermsindicator,advance,'".$_SESSION['UserID']."',
									 puestaenmarcha,paymentname,nocuenta,extratext,nopedido,noentrada,
									 noremision,idprospect,contid,typeorder,deliverytext
										FROM salesorders
										WHERE orderno = ".$orderNo;
				$ErrMsg=_('El Sql que fallo fue');
				$DbgMsg=_('No se pudo actualizar el encabezado del pedido que desea unificar');
				$Result= DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
				$headerdone = true;
			}

			//insertar partidas
			$qrypart = "Select stkcode,
					unitprice,
					quantity,
					discountpercent,
					discountpercent1,
					discountpercent2,
					narrative,
					poline,
					itemdue,
					fromstkloc,
					salestype,
					warranty,
					servicestatus,
					refundpercent,
					quantitydispatched,
					showdescrip
					From salesorderdetails
					WHERE orderno = ".$orderNo;
			$rspart = DB_query($qrypart,$db);
			while ($rowspart = DB_fetch_array($rspart)){

				if (in_array($rowspart['stkcode'],$arritems)){
					//upadte
					$DetailSQL = "UPDATE salesorderdetails
							SET quantity = quantity + ".$rowspart['quantity']."
							WHERE orderno = $neworder
							AND stkcode = '".$rowspart['stkcode']."'";
				}else{
					$arritems[] =  $rowspart['stkcode'];
					$DetailSQL = "INSERT INTO salesorderdetails (
												orderlineno,
												orderno,
												stkcode,
												unitprice,
												quantity,
												discountpercent,
												discountpercent1,
												discountpercent2,
												narrative,
												poline,
												itemdue,
												fromstkloc,
												salestype,
												warranty,
												servicestatus,
												refundpercent,
												quantitydispatched,
												showdescrip
												) VALUES
												(	$itemlineno,
													$neworder,
													'".$rowspart['stkcode']."',
													'".$rowspart['unitprice']."',
													'".$rowspart['quantity']."',
													'".$rowspart['discountpercent']."',
													'".$rowspart['discountpercent1']."',
													'".$rowspart['discountpercent2']."',
													'".$rowspart['narrative']."',
													'".$rowspart['poline']."',
													'".$rowspart['itemdue']."',
													'".$rowspart['fromstkloc']."',
													'".$rowspart['salestype']."',
													'".$rowspart['warranty']."',
													'".$rowspart['servicestatus']."',
													'".$rowspart['refundpercent']."',
													'".$rowspart['quantitydispatched']."',
													'".$rowspart['showdescrip']."'
												)";
					$itemlineno++;
				}

				$Result= DB_query($DetailSQL,$db,$ErrMsg,$DbgMsg,true);
			}

		}// Fin de recorrido de pedidos a unificar
		//crear oportunidad
		$SQL = "INSERT INTO prospect_movimientos (areacod,debtorno,u_proyecto,dia,mes,
				anio,concepto,descripcion,u_user,
				cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,
				estimado,fecha,currcode,branchcode,fecha_compromiso,
				grupo_contable,confirmado,activo,u_entidad,catcode,idstatus,
				UserId,fecha_alta,clientcontactid
				)
				Select areacod,debtorno,u_proyecto,'".date("d")."','".date("m")."',
						'".date("Y")."',concepto,descripcion,'".$_SESSION['UserID']."',
								cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,
								estimado,current_date,currcode,branchcode,fecha_compromiso,
								grupo_contable,confirmado,activo,u_entidad,catcode,idstatus,
								'".$_SESSION['UserID']."',current_date,clientcontactid
								FROM prospect_movimientos
								WHERE u_movimiento = '$umov'";
		$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		$prospectid = DB_Last_Insert_ID($db,'prospect_movimientos','u_movimiento');

		$sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
				VALUES ('".$prospectid ."','Alta de oportunidad:".$_SESSION['UserID']."@: GENERADA POR UNIFICAR COTIZACIONES',Now(),0,'1','".$_SESSION['UserID']."','".$_SESSION['UserID']."','alta')";
		$Result= DB_query($sql2,$db,$ErrMsg,$DbgMsg,true);
		//Actualiza num oportunidad y total de impuesto de nuevo pedido
		$SQL = "UPDATE salesorders
				SET idprospect=".$prospectid.",
						taxtotal = ".$taxtotal."
								WHERE orderno= " .  $neworder;
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de servicio de venta no se pudo actualizar');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		prnMsg('Se han unificado las cotizaciones en la cotizacion No '.$neworder);
	}//Fin de validacion
	// Finalizo transaccion
	$Result = DB_Txn_Commit($db);
}//Fin de unificacion

if (isset($_POST['btnDuplicar'])){
	$arrdata = $_POST['selectedorders'];
	if (count($arrdata) > 0){
		$ordenesDuplicadas="";
		// Inicializo transaccion
		$Result = DB_Txn_Begin($db);
		foreach($arrdata as $value){
			$orderNo = $value;
			$sql = "Select idprospect,quotation
			FROM salesorders
			WHERE orderno = $orderNo" ;
			$res = DB_query($sql,$db);
			$reg = DB_fetch_array($res);
			$umov = $reg[0];
			$quotation=$reg[1];

			$neworder = GetNextTransNo(30, $db);
			$ordenesDuplicadas.=$neworder.",";

			//Inserta encabezado de pedido
			$HeaderSQL = "INSERT INTO salesorders (orderno,debtorno,branchcode,customerref,comments,orddate,
					ordertype,shipvia,deliverto,deladd1,deladd2,deladd3,deladd4,
					deladd5,deladd6,contactphone,contactemail,freightcost,fromstkloc,
					deliverydate,quotedate,confirmeddate,deliverblind,salesman,
					placa,serie,kilometraje,tagref,taxtotal,totaltaxret,currcode,paytermsindicator,
					advance,UserRegister,puestaenmarcha,paymentname,nocuenta,extratext,nopedido,
					noentrada,noremision,idprospect,contid,typeorder,deliverytext
					)
					Select ".$neworder.",debtorno,branchcode,customerref,comments,orddate,
							ordertype,shipvia,deliverto,deladd1,deladd2,deladd3,deladd4,
							deladd5,deladd6,contactphone,contactemail,freightcost,fromstkloc,
							deliverydate,quotedate,confirmeddate,deliverblind,salesman,
							placa,serie,kilometraje,tagref,taxtotal,totaltaxret,currcode,paytermsindicator,
							advance,'".$_SESSION['UserID']."',puestaenmarcha,paymentname,nocuenta,extratext,nopedido,
									noentrada,noremision,idprospect,contid,typeorder,deliverytext
									FROM salesorders
									WHERE orderno = ".$orderNo;
			$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
			//Inserta partidas de pedido
			$DetailSQL = "INSERT INTO salesorderdetails (orderlineno,orderno,stkcode,unitprice,quantity,discountpercent,
					discountpercent1,discountpercent2,narrative,poline,itemdue,fromstkloc,
					salestype,warranty,servicestatus,refundpercent,quantitydispatched,showdescrip
					)
					Select orderlineno,".$neworder.",stkcode,unitprice,quantity,discountpercent,
							discountpercent1,discountpercent2,narrative,poline,itemdue,fromstkloc,
							salestype,warranty,servicestatus,refundpercent,quantitydispatched,showdescrip
							From salesorderdetails
							WHERE orderno = ".$orderNo;
			$Result = DB_query($DetailSQL,$db,$ErrMsg,$DbgMsg,true);
			
			//Inserta propiedades de pedido anterior
			$DetailSQL = "INSERT INTO salesstockproperties (stkcatpropid,orderno,valor,orderlineno,InvoiceValue,typedocument)
						  Select stkcatpropid,".$neworder.",valor,orderlineno,InvoiceValue,typedocument
						  From salesstockproperties
						  WHERE orderno = ".$orderNo;
			$Result = DB_query($DetailSQL,$db,$ErrMsg,$DbgMsg,true);
			
			//Agregar a tabla de fechas de pedidos
			$qry = "INSERT INTO salesdate(orderno,fecha_solicitud,usersolicitud)
					VALUES(".$neworder.",now(),'".$_SESSION['UserID']."')";
			//echo $qry.'<br>';
			$Result = DB_query($qry,$db);
			// Actualizo pedido a status inicial
			
			$sql="select * from salesfielddate where statusid=".$_SESSION['QuotationInicial'];
			$resultstatus = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			while ($RowOrders = DB_fetch_array($resultstatus)){
				$sql="UPDATE salesdate";
				if($RowOrders[2]==1){
					$sql=$sql." SET ".$RowOrders[0]."=now() ";
				}else{
					$sql=$sql." SET ".$RowOrders[0]."='".$_SESSION['UserID']."'";
				}
				$sql=$sql." WHERE orderno= ".  $neworder;
				if($RowOrders[3]==1){
					$sql=$sql." AND ".  $RowOrders[0] ." is null";
				}
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			}
			// Actualizo pedido recien creado
			
			$SQL = "UPDATE salesorders 
					SET quotation=".$_SESSION['QuotationInicial']." 
					WHERE orderno= " .   $neworder;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			
			
			// Actualiza informacion de fechas
			/************************************************************************/
			$sql="select * from salesfielddate where statusid=".$quotation;
			$resultstatus = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			while ($RowOrders = DB_fetch_array($resultstatus)){
				$sql="UPDATE salesdate";
				if($RowOrders[2]==1){
					$sql=$sql." SET ".$RowOrders[0]."=now() ";
				}else{
					$sql=$sql." SET ".$RowOrders[0]."='".$_SESSION['UserID']."'";
				}
				$sql=$sql." WHERE orderno= ".  $neworder;
				if($RowOrders[3]==1){
					$sql=$sql." AND ".  $RowOrders[0] ." is null";
				}
				//echo '<pre><br>'.$sql;
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			}
			/************************************************************************/


			//crear oportunidad
			$SQL = "INSERT INTO prospect_movimientos (areacod,debtorno,u_proyecto,dia,mes,anio,concepto,descripcion,
					u_user,cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,estimado,
					fecha,currcode,branchcode,fecha_compromiso,grupo_contable,confirmado,
					activo,u_entidad,catcode,idstatus,UserId,fecha_alta,clientcontactid
					)
					Select areacod,debtorno,u_proyecto,'".date("d")."','".date("m")."','".date("Y")."',concepto,descripcion,
							'".$_SESSION['UserID']."',cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,estimado,
									current_date,currcode,branchcode,fecha_compromiso,grupo_contable,confirmado,
									activo,u_entidad,catcode,idstatus,'".$_SESSION['UserID']."',current_date,clientcontactid
									FROM prospect_movimientos
									WHERE u_movimiento = '$umov'";
			$r = DB_query($SQL,$db);
			$prospectid = DB_Last_Insert_ID($db,'prospect_movimientos','u_movimiento');

			$sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
					VALUES ('".$prospectid ."','Alta de oportunidad:".$_SESSION['UserID']."@: GENERADA POR DUPLICAR COTIZACION $orderNo',Now(),0,'1','".$_SESSION['UserID']."','".$_SESSION['UserID']."','alta')";
			$result2= DB_query($sql2,$db);
			//actualizo pedido con id de prospecto
			$SQL = "UPDATE salesorders
					SET idprospect=".$prospectid."
							WHERE orderno= " .  $neworder;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de servicio de venta no se pudo actualizar');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		$ordenesDuplicadas = substr($ordenesDuplicadas,0,strlen($ordenesDuplicadas)-1);
		prnMsg('Se ha(n) generado la(s) cotizacion(es) No '.$ordenesDuplicadas);
		// Finalizo transaccion
		$Result = DB_Txn_Commit($db);

	}// fin de cuenta seleccion de pedidos de venta
}// Fin de duplicidad de ordenes de venta

/*********************************************/
// Criterios de consulta
/*********************************************/
echo '<table border=0>
		<tr>
		<td colspan=2>' . _('Desde:') . '
				<select Name="FromDia">';
$sql = "SELECT * FROM cat_Days";
$dias = DB_query($sql,$db,'','');
while ($myrowdia=DB_fetch_array($dias,$db)){
	$diabase=$myrowdia['DiaId'];
	if (rtrim(intval($FromDia))==rtrim(intval($diabase))){
		echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
	}else{
		echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
	}
}
echo '</select>';
echo '<select Name="FromMes">';
$sql = "SELECT * FROM cat_Months";
$Meses = DB_query($sql,$db,'','');
while ($myrowMes=DB_fetch_array($Meses,$db)){
	$Mesbase=$myrowMes['u_mes'];
	if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){
		echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" selected>' .$myrowMes['mes'];
	}else{
		echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
	}
}
echo '</select>';
echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'></td>';
echo '<td colspan=2>' . _('Hasta:') . '';
echo'<select Name="ToDia">';
$sql = "SELECT * FROM cat_Days";
$Todias = DB_query($sql,$db,'','');
while ($myrowTodia=DB_fetch_array($Todias,$db)){
	$Todiabase=$myrowTodia['DiaId'];
	if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){
		echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" selected>' .$myrowTodia['Dia'];
	}else{
		echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
	}
}
echo '</select>';
echo'<select Name="ToMes">';
$sql = "SELECT * FROM cat_Months";
$ToMeses = DB_query($sql,$db,'','');
while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
	$ToMesbase=$myrowToMes['u_mes'];
	if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){
		echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" selected>' .$myrowToMes['mes'];
	}else{
		echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
	}
}
echo '</select>';
echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'></td>';
echo'</tr>';
echo "<tr><td></td></tr>";
If (isset($_REQUEST['FolioFiscal']) AND $_REQUEST['FolioFiscal']!='') {
	$_REQUEST['FolioFiscal'] = trim($_REQUEST['FolioFiscal']);
	echo '<font size=4>'._('Folio Fiscal ') . ' No. ' . $_REQUEST['FolioFiscal'];
	echo'</font>';
} elseif (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!='') {
	$_REQUEST['OrderNumber'] = trim($_REQUEST['OrderNumber']);
	if (!is_numeric($_REQUEST['OrderNumber'])){
		echo '<br><b>' . _('El Numero de Pedido de Venta debe ser num���������������������������rico') . '</b><br>';
		unset ($_REQUEST['OrderNumber']);
		include('includes/footer.inc');
		exit;
	} else {
		echo '<font size=4>'._('Pedido de Venta ') . ' No. ' . $_REQUEST['OrderNumber'];
		echo'</font>';
	}
} else {
	If (isset($_REQUEST['SelectedCustomer'])) {
		$SQL="select * from debtorsmaster where debtorno='".$_REQUEST['SelectedCustomer']."'";
		$TaxCatQuery = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		if ($TaxCatRow = DB_fetch_array($TaxCatQuery)) {
			$Name = $TaxCatRow['name'];
		}
		echo _('Para el Cliente') . ': ' . $_REQUEST['SelectedCustomer'] .' - '. $Name.'<br>';
		echo "<input type=hidden name='SelectedCustomer' value=" . $_REQUEST['SelectedCustomer'] . '><br>';
	}
}

echo "<tr><td>";
echo ''._('Pedido de Venta No.') . ": </td><td><input type=text name='OrderNumber' maxlength=8 size=9 value='".$_POST['OrderNumber']."'></td>";
echo '<td>'._('Folio Fiscal.') . ":</td><td> <input type=text name='FolioFiscal' maxlength=18 size=9 value='".$_POST['FolioFiscal']."'> </td>
		</tr>";
echo "<tr><td></td></tr>";
echo "<tr><td>"._('Unidad de Negocio') . ":</td><td><select name='UnidNeg'> ";
$sql="SELECT t.tagref, t.tagdescription
		FROM tags t, sec_unegsxuser uxu
		WHERE t.tagref=uxu.tagref
		AND uxu.userid='".$_SESSION['UserID']."'
				ORDER BY tagdescription";
$resultTags = DB_query($sql,$db,'','');
echo "<option selected Value=''>" . "TODAS...";
while ($myrow=DB_fetch_array($resultTags))
{
	if ($myrow['tagref'] == $UnidNeg)
	{
		echo "<option selected Value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] .'</option>';
	}else{
		echo "<option Value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] .'</option>';
	}
}
echo '</select></td>';

echo "<td>"._('Estatus') . ":</td><td><select name='Quotations'> ";
$sql="SELECT *
		FROM  salesstatus
		ORDER BY ordenby asc";
$resultTags = DB_query($sql,$db,'','');
//echo "<option selected Value='-1'>" . "TODAS...";
while ($myrow=DB_fetch_array($resultTags))
{
	if (Havepermission($_SESSION['UserID'],$myrow['showfunctionid'], $db)==1){
		if ($_POST['Quotations'] == $myrow['statusid'])
		{
			echo "<option selected Value='" . $myrow['statusid'] . "'>" . $myrow['statusname'] .'</option>';
		}else{
			echo "<option Value='" . $myrow['statusid'] . "'>" . $myrow['statusname'] .'</option>';
		}
	}
}
echo '</select></td>';
echo "<tr><td>"._('Vendedor') . ":</td><td><select name='SalesMan'> ";
$sql="SELECT distinct salesmanname,salesmancode
		FROM salesman as sm LEFT JOIN areas as ar ON sm.area=ar.areacode
		JOIN tags as tg ON ar.areacode=tg.areacode
		JOIN sec_unegsxuser as u ON u.tagref = tg.tagref
		WHERE u.userid='".$_SESSION['UserID']."'
		  ORDER BY tg.tagref";
$result = DB_query($sql,$db,'','');
echo "<option selected Value=''>" . "TODOS...";
while ($myrow=DB_fetch_array($result))
{
	if ($myrow['salesmancode'] == $SalesMan)
	{
		echo "<option selected Value='" . $myrow['salesmancode'] . "'>" . $myrow['salesmanname'] .'</option>';
	}
	else
	{
		echo "<option Value='" . $myrow['salesmancode'] . "'>" . $myrow['salesmanname'] .'</option>';
	}
}
echo '</select></td>';
echo "<td>"._('Usuario') . ":</td><td><select name='UserName'> ";
$sql="SELECT userid, realname
		FROM www_users";
if ($permisouser==0){
	$sql=$sql." WHERE userid='".$_SESSION['UserID']."'";
}else{
	echo "<option selected Value=''>" . "TODOS...";
}
$result = DB_query($sql,$db,'','');

while ($myrow=DB_fetch_array($result))
{
	if ($myrow['userid'] == $_SESSION['UserID'] and $permisouser==0 ){
		echo "<option selected Value='" . $myrow['userid'] . "'>" . $myrow['realname'] .'</option>';
	}elseif($myrow['userid'] == $_POST['UserName']){
		echo "<option selected Value='" . $myrow['userid'] . "'>" . $myrow['realname'] .'</option>';
	}else{
		echo "<option  Value='" . $myrow['userid'] . "'>" . $myrow['realname'] .'</option>';
	}
}
echo '</select></td></tr>';
echo "<tr><td colspan=4></td></tr>";
//agrega busqueda por no. cliente y nombre de cliente
echo "<tr><td>"._('No. Cliente')."</td><td><input type='text' name='nocliente' size='10' value='".$_POST['nocliente']."'></td>
		<td>"._('Nombre Cliente')."</td></td><td><input type='text' name='cliente' size='25' value='".$_POST['cliente']."'></td></tr>";
echo "<tr><td colspan=4></td></tr>";
echo "<tr><td colspan=4 align=center style='text-align:center'><br><input type=submit name='SearchOrders' VALUE='" . _('Buscar Ordenes o Facturas...') . "'></td></tr>";
echo "</table><hr>";
/*********************************************/

/*********************************************/
// Muestra resultados de consulta
/*********************************************/

if(isset($_POST['SearchOrders'])){
	// Trae fechas por status
	$sql="SELECT *
			FROM  salesfielddate
			WHERE flagupdate=1 and flagdate=1
			AND statusid='".$Quotations."'
					order by statusid";

	//echo '<pre><br>sql1:'.$sql.'<br>';
	$rstxt = DB_query($sql,$db);
	$reg = DB_fetch_array($rstxt);
	$salesfield = $reg['salesfield'];
	if($salesfield==''){
		$salesfield='fecha_solicitud';
	}

	$sql="SELECT *
			FROM  salesstatus
			WHERE statusid='".$Quotations."'";
	$rstxt = DB_query($sql,$db);
	$reg = DB_fetch_array($rstxt);
	$salesname = $reg['statusname'];
	$flagCxC= $reg['invoice'];
	if($salesfield==''){
		$salesname=_('Todos');
	}

	$SQL = "SELECT distinct salesorders.orderno,debtortrans.folio,
			debtorsmaster.name,
			salesorders.UserRegister,
			salesman.salesmanname,
			custbranch.brname,
			salesorders.customerref,
			salesorders.orddate,
			salesorders.deliverydate,
			salesorders.currcode,
			salesorders.deliverto,
			salesorders.quotation as estatus,
			debtortrans.id,
			salesorders.printedpackingslip,
			paymentterms.type,
			salesorders.debtorno,
			tagdescription,
			salesorders.nopedido,
			salesorders.tagref,
			salesorders.idprospect,
			debtortrans.noremisionf,
			salesstatus.statusname,
			salesstatus.invoice,
			salesdate.".$salesfield." as fecha,
					tags.tagdescription,
					case when debtortrans.folio is null then '' else folio end as folio,
					paymentterms.type as tipopago,
					openfunctionid,
					salesstatus.invoice,
					salesstatus.flagopen,
					salesstatus.templateid,
					salesstatus.templateidadvance,
					salesorders.nopedido,
					salesorders.placa,
					case when debtortrans.folio is null then (SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + (salesorders.taxtotal +salesorders.totaltaxret) else debtortrans.ovamount+debtortrans.ovgst end AS ordervalue,
					debtortrans.type,
					debtortrans.transno	,
					case when debtortrans.id is null then 0 else  debtortrans.id end as idfactura,
					tags.legalid,
					case when debtortrans.id is null then-1 else debtortrans.prd end as prd,
					cancelfunctionid,
					cancelextrafunctionid,
					flagcancel,
					flagelectronic

					FROM salesorders left join debtortrans ON salesorders.orderno = debtortrans.order_
					AND debtortrans.type in (10,110,111,119,410,66)
					JOIN salesstatus ON salesstatus.statusid=salesorders.quotation
					JOIN salesdate ON salesdate.orderno=salesorders.orderno
					LEFT JOIN salesorderdetails on salesorders.orderno = salesorderdetails.orderno
					LEFT JOIN locations on salesorderdetails.fromstkloc=locations.loccode
					LEFT JOIN salesman ON salesman.salesmancode = salesorders.salesman
					LEFT JOIN tags on tags.tagref=salesorders.tagref
					JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
					JOIN custbranch ON  debtorsmaster.debtorno = custbranch.debtorno AND salesorders.branchcode = custbranch.branchcode
					JOIN  paymentterms ON paymentterms.termsindicator=salesorders.paytermsindicator
					JOIN  sec_loccxusser ON salesorderdetails.fromstkloc=sec_loccxusser.loccode and  sec_loccxusser.userid ='".$_SESSION['UserID']."'
							AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='".$_SESSION['UserID']."')

									WHERE   1=1 ";
	$validafecha=true;
	if (isset($_POST['FolioFiscal']) and strlen($_POST['FolioFiscal'])>0){
		$SQL=$SQL." AND (debtortrans.folio like '%". $_REQUEST['FolioFiscal'] ."%'
				OR replace(debtortrans.folio,'|','') like '%" .$_REQUEST['FolioFiscal'] . "%')";
		$validafecha=false;
	}

	if (strlen($_POST['OrderNumber'])>0){
		$SQL=$SQL." AND salesorders.orderno=". $_REQUEST['OrderNumber'] ;
		$validafecha=false;
	}

	/*if (isset($_POST['FolioFiscal']) and strlen($_POST['FolioFiscal'])>0){
		$SQL=$SQL." AND (debtortrans.folio like '%". $_REQUEST['FolioFiscal'] ."%'
				OR  replace('|','',debtortrans.folio) like '%" .$_REQUEST['FolioFiscal'] . "%')";
		$validafecha=false;
	}*/

	if($Quotations==-1){
		if($validafecha==true){
			$SQL.=" AND salesdate.".$salesfield.">= '".$fechaini."' and salesdate.".$salesfield."<='".$fechafin."'";
		}else{
			$SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
		}
	}else{
		if($validafecha==true){
			$SQL.=" AND salesdate.".$salesfield.">= '".$fechaini."' and salesdate.".$salesfield."<='".$fechafin."'";
		}
		if (strlen($_POST['OrderNumber'])==0){
			$SQL.=" AND salesorders.quotation=".$Quotations;
		}
	}

	if (strlen($_POST['UnidNeg'])>0){
		$SQL= $SQL. " AND salesorders.tagref = '". $_POST['UnidNeg'] . "'";
	}

	if (strlen($SalesMan) > 0) {
		$SQL= $SQL. " AND salesorders.salesman = '". $SalesMan . "'";
	}

	if (strlen($UserName) > 0) {
		$SQL= $SQL. " AND salesorders.UserRegister = '". $UserName . "'";
	}

	if (isset($_POST['nocliente']) and strlen($_POST['nocliente'])>0){
		$SQL=$SQL." AND salesorders.debtorno like '%".$_POST['nocliente']."%'";
	}
	if (isset($_POST['cliente']) and strlen($_POST['cliente'])>0 ) {
		$SQL.=" AND custbranch.brname like '%".$_POST['cliente']."%'";
	}
	$SQL=$SQL."
			GROUP BY salesorders.orderno,
			debtorsmaster.name,
			custbranch.brname,
			salesorders.customerref,
			salesorders.orddate,
			salesorders.deliverydate,
			salesorders.deliverto,
			salesorders.printedpackingslip,
			salesorders.nopedido
			ORDER BY salesorders.orderno, tagdescription ";
	/*if($_SESSION['UserID']=='desarrollo'){
            echo '<pre><br>sql:'.$SQL;
		
	}*/
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg,'');
	if (db_num_rows($SalesOrdersResult)>0) {

		echo '<table>';
		echo '<tr><td colspan=1 style="text-align:center"><b>'.$salesname.'</b></td></tr>';
		echo '<tr><td>';
		echo '<table cellpadding=2 colspan=7 WIDTH=100%>';

		$tableheader = "<tr>
						<th nowrap>" . _('Sel') . "</th>
						<th nowrap>" . _('No') . "</th>
						<th nowrap>" . _('Fecha') . "</th>
						<th nowrap>" . _('Unidad<br>Negocio') . "</th>
						<th nowrap>" . _('Folio') . "</th>
						<th nowrap>" . _('Imprimir') . "</th>
						<th nowrap>" . _('Termino<br>Pago') . "</th>
						<th nowrap>" . _('Cliente') . "</th>
						<th nowrap>" . _('O.C. Cliente') . " #</th>
						<th nowrap>" . $_SESSION['LabelText1'] . "</th>
						<th nowrap>" . _('Usuario') . "</th>
						<th nowrap>" . _('Vendedor') . "</th>
						<th nowrap>" . _('Moneda') . "</th>
						<th nowrap>" . _('Total') . "</th>";
		if($_SESSION['ShowProspect']==1 and $oportunidadprospectoPermiso==1){
			$tableheader=$tableheader."<th>". _('Oportunidad') ."</th>";
		}
		if ($permisocancelar==1){
			$tableheader=$tableheader."<th>" . _('Cancelar') . "</th>";
		}
		if ($modificarvendedores==1)
		{
			$tableheader=$tableheader."<th>" . _('Mod<br>Vend') . "</th>";
		}
		if ($modificartrabajadores==1)
		{
			$tableheader=$tableheader."<th>" . _('Mod<br>Trab') . "</th>";
		}
		if ($add_datosfactura==1)
		{
			$tableheader=$tableheader."<th colspan=3>" . _('Mod<br>Inf Factura') . "</th>";
		}
		$tableheader=$tableheader.'</tr>';
		echo $tableheader;
		$j = 1;
		$k=0; //row colour counter
		$montoTotal = 0;
		$indextable=0;
		while ($myrow=DB_fetch_array($SalesOrdersResult)) {
			if ($indextable == 0) {
				echo '<tr class="EvenTableRows">';
				$indextable = 1;
			} else {
				echo  '<tr class="OddTableRows">';
				$indextable = 0;
			}


			echo '<td style="text-align:center"><input type="checkbox" name="selectedorders[]" value="'.$myrow['orderno'].'"></td>';

			$ModifyPage = $rootpath . "/".$paginapedidos."?" . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
			// si tiene permiso para abrir pedido y el pedido aun cuenta con atributo de modificacion
			if(Havepermission($_SESSION['UserID'],$myrow['openfunctionid'], $db)==1 and $myrow['flagopen']==1){
				echo '<td><a href='.$ModifyPage.'> '.$myrow['orderno'].'</a></td>';
			}else{
				echo '<td>'.$myrow['orderno'].'</td>';
			}
			echo '<td nowrap>'.$myrow['fecha'].'</td>';
			echo '<td>'.$myrow['tagdescription'].'</td>';
			$tagref = $myrow['tagref'];
			if(($myrow['idfactura'])>0){
				$linkfactura='PDFInvoice.php';
				$linkfactura=$rootpath . '/'.$linkfactura.'?tipodocto=1&OrderNo='.$myrow['orderno'].'&TransNo=' . $myrow['transno'] .'&Type='.$myrow['type'].'&tagref='.$tagref;
				echo '<td nowrap ><a target="_blank"  href='.$linkfactura.'><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">'.$myrow['folio'].'</a></td>';
			}else{
				$email_link ="<a target='_blank' href='SendEmail.php?tagref=$tagref&transno=".$myrow['orderno']."&debtorno=".$myrow['debtorno']."'><img src='part_pics/Mail-Forward.png' border=0></a>";

				echo '<td>'.$myrow['folio'].' '.$email_link.'</td>';
			}
			//Links de impresion
			$liga='PDFCotizacionTemplateV2.php?tipodocto='.$myrow['templateid'].'&';
			$liga2 = 'PDFCotizacionTemplateV2.php?tipodocto='.$myrow['templateidadvance'].'&';
			$PrintQuotation = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='. $myrow['orderno'].'&Tagref='. $myrow['tagref'];
			$PrintDispatchNote = $rootpath . '/' . $liga2  . '&' . SID . 'PrintPDF=Yes&TransNo='. $myrow['orderno'].'&Tagref='. $myrow['tagref'];
			if($myrow['templateid']==$myrow['templateidadvance']){
				echo '<td><a target="_blank"  href='.$PrintQuotation.'><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt=""></a>'._('Imprimir').'</td>';
			}else{
				echo '<td><a target="_blank" href='.$PrintQuotation.'><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">'._('Imprimir (Simple)').'</a>
						<br><a target="_blank" href='.$PrintDispatchNote.'><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">'._('Imprimir').'</a></td>';
			}
			echo '<td>'.$myrow['tipopago'].'</td>';
			echo '<td>'.$myrow['debtorno'].' '.$myrow['brname'].'</td>';
			echo '<td>'.$myrow['nopedido'].'</td>';
			echo '<td>'.$myrow['placa'].'</td>';
			echo '<td>'.$myrow['UserRegister'].'</td>';
			echo '<td>'.$myrow['salesmanname'].'</td>';
			echo '<td>'.$myrow['currcode'].'</td>';
			echo '<td>$'.number_format($myrow['ordervalue'],2).'</td>';
			$montoTotal=$montoTotal+$myrow['ordervalue'];
			if($myrow['idprospect']>0){
				$oportunidad ="<a target='_blank' href='".$oportunidadprospecto."?u_movimiento=".$myrow['idprospect']."'><img src='images/user_24x32.gif' border=0></a>";
			}else{
				$oportunidad=' ';
			}
			$Cancelar="&nbsp;&nbsp;";
			if($_SESSION['ShowProspect']==1 and $oportunidadprospectoPermiso==1){
				echo '<td style="text-align:center">'.$oportunidad.'</td>';
			}
			//Validaciones para cancelar documento cxc y/o pedidos
			if (($myrow['idfactura'])>0){
				$statusPeriodo= TraestatusPeriod($myrow['legalid'],$myrow['prd'],$db);
				//Verificar si se utiliza...
				$sql="select date(fechacorte) as fechacorte
						from usrcortecaja
						where u_status=0 and tag=".$myrow['tagref'];
				$Fechacorte = DB_query($sql,$db,'','');
				while ($myrowcorte=DB_fetch_array($Fechacorte)) {
					$fechacorte=$myrowcorte['fechacorte'];
				}

				if ($myrow['type']==10){
					$XSQL="SELECT DISTINCT d2.id
							FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
							INNER JOIN debtortrans d2 ON d2.type=70 and d2.order_=debtortrans.transno
							LEFT JOIN custallocns C2 ON d2.id=C2.transid_allocto
							WHERE  debtortrans.id='".$myrow['idfactura']."'
									AND C2.amt  IS NOT NULL
									union
									SELECT debtortrans.id
									FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
									INNER JOIN debtortrans d2 ON custallocns.transid_allocfrom=d2.id and d2.type<>70
									WHERE  debtortrans.id='".$myrow['idfactura']."'";
				}else{
					$XSQL="SELECT debtortrans.id
							FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
							WHERE  debtortrans.id='".$myrow['idfactura']."'";
				}
				$tienerecibos = DB_query($XSQL ,$db,'','');
				if (intval(DB_num_rows($tienerecibos)) > intval(0)){
					$Cancelar="Tiene<br>Recibos";
				}else{
					$myrowtienerecibos = DB_fetch_row($tienerecibos);
					if (Havepermission($_SESSION['UserID'],$myrow['cancelfunctionid'], $db)==1){
						//$Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];

						if ($statusPeriodo==0){
							$Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo='.$myrow['transno'].'&OrderNumber='. $myrow['orderno'].'&debtorno='.trim($myrow['debtorno']).'&type='.$myrow['type'].'&tagref='.$myrow['tagref'];
							$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
						}else{
							$Cancelar='<b>'._('Contabilidad').'<br>'._('Cerrada').'</b>';
						}
					}else{
						$Cancelar="&nbsp;&nbsp;";
					}
				}

			}else{
				if (Havepermission($_SESSION['UserID'],$myrow['cancelfunctionid'], $db)==1){
					if ($myrow['flagcancel']==0 && Havepermission($_SESSION['UserID'],$myrow['cancelextrafunctionid'], $db)==1){
						$Cancelar = $rootpath . '/'.$paginabusquedapedidos.'?orderno='. $myrow['orderno'];
						$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
					}else{
						$Cancelar = $rootpath . '/'.$paginabusquedapedidos.'?orderno='. $myrow['orderno'];
						$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
					}
				}
			}


			echo '<td style="text-align:center">'.$Cancelar.'</td>';

			if (($myrow['idfactura'])>0){

				if (($modificarvendedores==1)){
					$link_modVendedores = "<a target='_blank' href='ChangeSalesman.php?orderno=". $myrow['orderno'] ."&type=".$myrow['type']."&folio=". $myrow['folio'] ."'
							title='Modificar Vendedor'><img src='".$rootpath."/css/".$theme."/images/user.png' TITLE='"._('Modificar Vendedores')." ALT='" . _('Modificar Vendedores') . "'></a>";
				}else{
					$link_modVendedores='';
				}

			}else{
				$link_modVendedores='';
			}
			echo '<td style="text-align:center">'.$link_modVendedores.'</td>';

			if (($myrow['idfactura'])>0){
				//Modificar trabajadores
				$ssql = "select salesorderdetails.orderno, sum(salesstockproperties.valor IS NULL)  as porcapturar
						from salesorderdetails JOIN stockmaster ON  salesorderdetails.stkcode = stockmaster.stockid
						LEFT JOIN stockcatproperties ON  stockmaster.categoryid =  stockcatproperties.categoryid
						LEFT JOIN salesstockproperties ON salesstockproperties.orderno =  salesorderdetails.orderno
						where salesorderdetails.orderno=" . $myrow['orderno'] . " and stockcatproperties.reqatsalesorder = 1
								GROUP BY salesorderdetails.orderno";
				$resultProp = DB_query($ssql ,$db,'','');
				$myrowProp = DB_fetch_row($resultProp);
				if (intval($myrowProp[1])>0) # si tiene productos con categoorias a los cuales se asigna trabajadores
				{
					$link_modTrabajadores = "<a href='ChangePropertiesInvoice.php?orderno=". $myrow['orderno'] ."&folio=". $folio_elec ."' title='Modificar Trabajadores'><img src='part_pics/Users-2.png' border=0></a>";
				} elseif($modificartrabajadores==1){
					$link_modTrabajadores = "<a href='ChangePropertiesInvoice.php?orderno=". $myrow['orderno'] ."&folio=". $folio_elec ."' title='Modificar Trabajadores'><img src='part_pics/Users-2.png' border=0></a>";
				}else{
					$link_modTrabajadores ="&nbsp;";
				}

			}else{
				$link_modTrabajadores='';
			}
			if ($modificartrabajadores==1)
			{
				echo '<td style="text-align:center">'.$link_modTrabajadores.'</td>';
			}
			if (Havepermission($_SESSION['UserID'],3014, $db)==1){
				$modfactr=$rootpath . '/modifyInvoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&type='.$typedeb;
				$modfactr="<a target='_blank' href='".$modfactr."'><img src='images/Edit.ico' border=0></a>";
			}else{
				$modfactr="&nbsp;";
			}




			if (($myrow['idfactura'])>0){
				$ReenvioXSA= $rootpath . '/'.$paginabusquedapedidos.'?orderno='.$myrow['orderno'].'&tagrefen='.$myrow['tagref'].
				'&iddocto='.$myrow['idfactura'].
				'&serie='.$serie.
				'&folio='.$myrow['folio'].
				'&debtorno='.$myrow['debtorno'].
				'&tipo='.$myrow['type'].'&transno='.$transno
				. '&FromDia=' . $FromDia
				. '&FromMes=' . $FromMes
				. '&FromYear=' . $FromYear
				. '&ToDia=' . $ToDia
				. '&ToMes=' . $ToMes
				. '&ToYear=' . $ToYear
				;
				// 				$ReenvioXSA='';
				if($myrow['flagelectronic']==1 or $myrow['type']=='119' ){
					$ReenviarXSA="&nbsp;&nbsp;&nbsp;<a href='".$ReenvioXSA."'><img src='part_pics/Mail-Forward.png' alt='"._('Reenviar SAT')."' border=0>"._('Reimpresion')."</a>";
				}else{
					$ReenviarXSA='';
				}
				$RecapturaAddenda= $rootpath . '/Z_RecapturaAddenda.php?'.'&iddocto='.$myrow['idfactura'];
				$RecapturaAddenda="&nbsp;&nbsp;&nbsp;<a href=".$RecapturaAddenda."><img src='images/Edit.ico' alt='"._('Recaptura datos addenda')."' border=0></a>";


				if (($add_datosfactura==1)){
					$RecapturaAddenda= $rootpath . '/Z_RecapturaAddenda.php?'.'&iddocto='.$idfactura;
					$RecapturaAddenda="&nbsp;&nbsp;&nbsp;<a href=".$RecapturaAddenda."><img src='images/Edit.ico' alt='"._('Recaptura datos addenda')."' border=0>"._('Addenda')."</a>";
				}else{
					$RecapturaAddenda='';
				}

				if ($mod_datosfactura==1){
					$modfactr=$rootpath . '/modifyInvoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&type='.$myrow['type'];
					$modfactr="<a target='_blank' href='".$modfactr."'><img src='images/Edit.ico' border=0>"._('Inf. Partidas')."</a>";
				}else{
					$modfactr="&nbsp;";
				}

			}else{
				$ReenviarXSA='';
				$RecapturaAddenda='';
				$modfactr="&nbsp;";
			}
			if ($add_datosfactura==1)
			{
				echo '<td style="text-align:center">'.$ReenviarXSA.'</td><td style="text-align:center"> '.$RecapturaAddenda.'</td><td style="text-align:center"> '.$modfactr.'</td>';
			}
			//echo '<td style="text-align:center">'.$RecapturaAddenda.' '.$modfactr.' --- '.$ReenviarXSA.'</td>';
			echo '</tr>';
		}
		echo '<tr>';
		echo ' <th colspan=13 style="text-align:right;">'._('TOTAL').'</th>';
		echo ' <th colspan=1 style="text-align:center;">$'.number_format($montoTotal,2).'</th>';
		echo ' <th colspan=7 style="text-align:center;"></th>';
		echo '</tr>';
		echo '<tr><td colspan=15>&nbsp;</td></tr>';
		if($flagCxC==0){
			echo '<tr><td colspan=15 style="text-align:center">
					<input title="'._('Crea cotoizacion nueva por cada seleccion').'" type="submit" name="btnDuplicar" value="'._('Duplicar Seleccion').'">&nbsp;&nbsp;&nbsp;&nbsp;
							</td></tr>';
		}

	}


}
echo '</table>';
echo '</td><tr></table>';
// 	<input title="'._('Unifica las cotizaciones seleccionads en una nueva').'" type="submit" name="btnUnificar" value="'._('Unificar Seleccion').'">

include('includes/footer.inc');
echo '</form>'
?>
