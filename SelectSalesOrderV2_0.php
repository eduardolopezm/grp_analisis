<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
/*
 CGM 13/06/2013
 Agregue campos para ventas perdidas
 
 ALTER TABLE `salesorderdetails` 
 ADD COLUMN `qtylost` float AFTER `quantitydispatched`, 
 ADD COLUMN `datelost` datetime AFTER `qtylost`;
 
 CGM 17/05/2013 se agrego status de levantamiento

INSERT INTO `sec_functions` VALUES
('738', '1', 'Levantar Pedido', '1', '', '1', 'Levantar Pedido', '9', '', '');

SP 11/03/2013 Cambien el formato de Cotizacion con la nueva version y modifique el link de acceso para que se muestre el nuevo: PDFCotizacionTemplateV2.php
			Si desean cambiar al formato anterior poner en el link PDFCotizacionTemplate.php

SP 15/02/2013 Se creo permiso 677 para habilitar la cancelacion de pedidos cerrados
*/

/* $Revision: 4.0 $
  
ARCHIVO MODIFICADO POR: FRUEBEL CANTERA
FECHA DE MODIFICACION: 30-Mar-2011
 CAMBIOS:
   1. AGREGUE LA CONDICION (debtortrans.ovamount <> 0) EN LAS CONSULTAS DE BUSQUEDA DE PEDIDOS DONDE HACE REFERENCIA A
   LA TABLA debtortrans, POR QUE ESTA REGRESANDO REGISTROS CANCELADOS AL MOMENTO DE CONSULTAR PEDIDOS FACTURADOS.

ARCHIVO MODIFICADO POR: CARMEN GARCIA
FECHA DE MODIFICACION: 04-Mar-2011
 CAMBIOS:
   1. VALIDACION DE PERIODO CONTABLE PARA CANCELACION DE DOCUMENTOS
FIN DE CAMBIOS

FECHA DE MODIFICACION: 08-feb-2011
GONZALO ALVAREZ Z
 CAMBIOS:
   1. AGREGUE CLAVE DEL CLIENTE Y ORDEN POR FOLIO FISCAL PRIMERO Y DESPUES POR ORDEN
   2. AGREGUE BUSQUEDA POR FOLIO FISCAL Y ELIMINE LA CONDICION DE FECHAS SI BUSCAS DIRECTO X ORDEN O FOLIO FISCAL
   
FIN DE CAMBIOS


*/

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Busqueda de Pedidos de Venta');

unset($_SESSION['ExistingOrder']);
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$funcion=602;
include('includes/SecurityFunctions.inc');

include ('Numbers/Words.php');
include ('includes/XSAInvoicing.inc');
//para cuentas referenciadas
include('includes/Functions.inc');
//include ('XSAInvoicing3.inc');
include ('includes/SendInvoicing.inc');
// Incluye la funci�n emailNotifier
require_once('includes/DeliveryEmailNotifier.inc');

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

$paginapedidos=HavepermissionURL($_SESSION['UserID'],4, $db);
//$paginapedidos='SelectOrderItemsV4_0.php';
$oportunidadprospectoPermiso=Havepermission($_SESSION['UserID'],2210, $db);


$oportunidadprospecto=HavepermissionURL($_SESSION['UserID'],2210, $db);

if (Havepermission($_SESSION['UserID'],171, $db)==1) {
	echo '<p><a href="' . $rootpath . '/'.$paginapedidos.'?' . SID . '&NewOrder=Yes"><font size=2><b>' . _('Nuevo Pedido de Venta') . '</a>';
}


$permisoremision=Havepermission($_SESSION['UserID'],224, $db);

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
    
     $fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
     //$fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia);
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

echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';
echo '<input type=hidden name="fechaini" VALUE="' . $fechaini . '">
              <input type=hidden name="fechafin" VALUE="' . $fechafin . '">';

If (isset($_POST['ResetPart'])){
     unset($_REQUEST['SelectedStockItem']);
}

echo '<p><div class="centre">';
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

if(isset($orden) and empty($folio) and empty($ventaperdida)) {
	prnMsg(_('El sistema no permite cancelaciones desde esta pagina, entrar a la modificacion del pedido para cancelarlo.'), 'error');
}

if (isset($orden) and isset($ventaperdida) and $ventaperdida=='yes'){
	
     $SQL = "UPDATE salesorders
     SET quotation =4
     WHERE orderno= " .  $orden;
     $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
     $DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
     $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
     
     $SQL = "UPDATE salesorderdetails
     SET qtylost=(quantity-qtyinvoiced),
	 quantity=qtyinvoiced,
	 datelost=now()
     WHERE (quantity-qtyinvoiced)>0 and orderno= " .  $orden;
     $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
     $DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
     $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    // echo $SQL;
	prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha convertido a facturado y la cantidad restante de los productos se considerara como venta perdida'),'success');
	
     echo '<meta http-equiv="Refresh" content="2; url=' . $rootpath .
			'/SelectSalesOrderV2_0.php?OrderNumber=' . $orden . '&UnidNeg=' . $tagrefen 
			. '&FromDia=' . $FromDia 
			. '&FromMes=' . $FromMes 
			. '&FromYear=' . $FromYear 
			. '&ToDia=' . $ToDia
			. '&ToMes=' . $ToMes
			. '&ToYear=' . $ToYear
			. '&Quotations=4' 
			.'">';
     
	
	
	
	
}

if (isset($orden) and !isset($folio)){
   /*  $SQL = "UPDATE salesorders
     SET quotation =3
     WHERE orderno= " .  $orden;
     $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
     $DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
     $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
     prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha cancelado'),'success');
     
     emailNotifier('Cancelar Pedido', $orden, $db);*/
     $SQL = "Select *
     		 From salesorders
     		 where salesorders.orderno = '".$orden."'
     		 And salesorders.quotation = 0";
     $result = DB_query($SQL, $db);
     
     if(DB_num_rows($result) > 0){
     	
     	$myrow = DB_fetch_array($result);
     	$qry = "Select wo FROM workorders
						WHERE orderno = '".$orden."'";
     	
     	$rsw = DB_query($qry,$db);
     	if (Db_num_rows($rsw) > 0){
     	
     		$resultw = DB_Txn_Begin($db);
     		while ($regs = DB_fetch_array($rsw)){
     			
     			$sql = "select stockmaster.description as producto, qtyreqd, qtyrecd
								from woitems
								inner join stockmaster
								ON woitems.stockid = stockmaster.stockid
								where wo = ".$regs[0]."
								and qtyrecd = 0";
     			
     			$rsx = DB_query($sql,$db);
     			if (DB_num_rows($rsx) > 0){
     				
     				$sqlcalendar = "Select *
									From mrp_calendar_AM_detail
									Where mrp_calendar_AM_detail.serie = '".$regs[0]."'";
     				//echo '<pre>'.$sqlcalendar;//
     				//exit;
     				$resultcalendar = DB_query($sqlcalendar, $db);
     				if(DB_num_rows($resultcalendar) > 0){
     					while($myrowesquema = DB_fetch_array($resultcalendar)){
     							
     						$sqlesquematizado = "Delete
											 	From mrp_calendar_AM_detail
												Where mrp_calendar_AM_detail.serie = '".$myrowesquema['serie']."'";
     						$resultesquematizado = DB_query($sqlesquematizado, $db);
     					}//
     				}
     				$qry1 = "DELETE FROM workorders WHERE wo = ".$regs[0];
     				//echo '<pre>1'.$qry1;
     				$r = DB_query($qry1,$db);
     				//echo '<pre>2'.$qry1;
     				$qry1 = "DELETE FROM woitems WHERE wo = ".$regs[0];
     				$r = DB_query($qry1,$db);
     				//echo '<pre>'.$qry1;
     				$qry1 = "DELETE FROM worequirements WHERE wo = ".$regs[0];
     				$r = DB_query($qry1,$db);
     				$resultw = DB_Txn_Commit($db);
     				exit;
     			}
     	
     		}
     	}	
     }
     
    
     
}elseif(isset($orden) and isset($folio) and isset($iddocto)){
    	//regenerar XML
	$SQL="SELECT debtortrans.*, year(origtrandate) as aniodocto
	      FROM debtortrans
	      WHERE id=".$iddocto;
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
		#echo '<pre><br>fact inv:<br>'.$factelectronica;
		#exit;
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
			
			$XMLElectronico=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);
			if($_SESSION['UserID']=='desarrollo'){
				echo '<pre>'.htmlentities($XMLElectronico);
				echo '<br>'.($factelectronica);
				exit;
			}
			echo '<meta http-equiv="Refresh" content="2; url=' . $rootpath .
			'/SelectSalesOrderV2_0.php?OrderNumber=' . $orden . '&UnidNeg=' . $tagrefen
			. '&FromDia=' . $FromDia
			. '&FromMes=' . $FromMes
			. '&FromYear=' . $FromYear
			. '&ToDia=' . $ToDia
			. '&ToMes=' . $ToMes
			. '&ToYear=' . $ToYear
			. '&Quotations=4'
					.'">';
			
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
			$XMLElectronico = generaXMLCFDI($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);
			$XMLElectronico = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $XMLElectronico);
			
			include_once 'timbradores/TimbradorFactory.php';
			$timbrador = TimbradorFactory::getTimbrador($config);
			if($timbrador != null) {
				$timbrador->setRfcEmisor($rfc);
				$timbrador->setDb($db);
				$cfdi = $timbrador->timbrarDocumento($XMLElectronico);
				$success = ($timbrador->tieneErrores() == false);
				foreach($timbrador->getErrores() as $error) {
					prnMsg($error, 'error');
				}
			} else {
				prnMsg(_('No hay un timbrador configurado en el sistema'), 'error');
			}
		
			if($success) {
				//echo 'entra';
				//leemos la informacion del cfdi en un arreglo
				$DatosCFDI = TraeTimbreCFDI($cfdi);
				if (strlen($DatosCFDI['FechaTimbrado'])>0){
					$cadenatimbre='||1.0|'.$DatosCFDI['UUID'].'|'.$DatosCFDI['FechaTimbrado'].'|'.$DatosCFDI['selloCFD'].'|'.$DatosCFDI['noCertificadoSAT'].'||';
						
					// guardamos el timbre fiscal en la base de datos para efectos de impresion de datos
					$sql="UPDATE debtortrans
					      SET fechatimbrado='".$DatosCFDI['FechaTimbrado']."',
						uuid='".$DatosCFDI['UUID']."',
						timbre='".$DatosCFDI['selloSAT']."',
						cadenatimbre='".$cadenatimbre."'
						where id=".$DebtorTransID;
					$ErrMsg=_('El Sql que fallo fue');
					$DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
					$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
						
					$XMLElectronico = $cfdi;
							
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
		}else{
		
			//echo $factelectronica;
			if ($aniodocto>=2011){
				$XMLElectronico=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);
			}else{
				//echo '<br>entra................';
				$XMLElectronico=generaXML2010($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);
			}
		}
	
		//echo htmlentities($XMLElectronico);
		//exit;
	}
}

//aqui empieza la seleccion de periodo desde hasta
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
				 echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
			     }else{
				 echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
			     }
			 }
			 
			 echo '</select>';
			 echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
				 
		 echo '
		    &nbsp;
	       </td>
	       <td colspan=2>' . _('Hasta:') . '';
	       echo'<select Name="ToDia">';
			 $sql = "SELECT * FROM cat_Days";
			 $Todias = DB_query($sql,$db,'','');
			 while ($myrowTodia=DB_fetch_array($Todias,$db)){
			     $Todiabase=$myrowTodia['DiaId'];
			     if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
				 echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
			     }else{
				 echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
			     }
			 }
		echo '</select>';
	       echo '';
	       echo'';
		    echo'<select Name="ToMes">';
		    $sql = "SELECT * FROM cat_Months";
		    $ToMeses = DB_query($sql,$db,'','');
		    
		    while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
			$ToMesbase=$myrowToMes['u_mes'];
			if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
			    echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
			}else{
			    echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
			}
		    }
		    echo '</select>';
		    echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
		    
	       echo'</td>
	  </tr>
     ';
     echo "<tr><td></td></tr>";

If (isset($_REQUEST['FolioFiscal']) AND $_REQUEST['FolioFiscal']!='') {
	
	$_REQUEST['FolioFiscal'] = trim($_REQUEST['FolioFiscal']);
	
	echo '<font size=4>'._('Folio Fiscal ') . ' No. ' . $_REQUEST['FolioFiscal'];
	echo'</font>';
	
} elseif (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!='') {
	
	$_REQUEST['OrderNumber'] = trim($_REQUEST['OrderNumber']);
	if (!is_numeric($_REQUEST['OrderNumber'])){
		  echo '<br><b>' . _('El Numero de Pedido de Venta debe ser num�rico') . '</b><br>';
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
				//echo "<br>entra";
				$Name = $TaxCatRow['name'];
				
			}
		
		echo _('Para el Cliente') . ': ' . $_REQUEST['SelectedCustomer'] .' - '. $Name.'<br>';
		echo "<input type=hidden name='SelectedCustomer' value=" . $_REQUEST['SelectedCustomer'] . '><br>';
	}
	If (isset($_REQUEST['SelectedStockItem'])) {
		 echo _('para el producto no.') . ': ' . $_REQUEST['SelectedStockItem'] . ' ' . _(' y ') . " <input type=hidden name='SelectedStockItem' value='" . $_REQUEST['SelectedStockItem'] . "'>";
	}
}




if (isset($_POST['SearchParts'])){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString . substr($_POST['Keywords'],$i).'%';

		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND stockmaster.categoryid='" . $_POST['StockCat']. "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid='" . $_POST['StockCat'] ."'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	 }

	$ErrMsg =  _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

 }

if (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {

     /* Not appropriate really to restrict search by date since may miss older
     ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
     */
		echo "<tr><td>";
		echo ''._('Pedido de Venta No.') . ": </td><td><input type=text name='OrderNumber' maxlength=8 size=9 value='".$_POST['OrderNumber']."'></td>";
		echo '<td>'._('Folio Fiscal.') . ":</td><td> <input type=text name='FolioFiscal' maxlength=18 size=9 value='".$_POST['FolioFiscal']."'> </td> </tr>";
		
		
		/*
		    echo _('Almacen') . ":<select name='StockLocation'> ";
		    $sql="SELECT locations.loccode,
					 locationname
			 FROM locations, sec_loccxusser
			 WHERE locations.loccode=sec_loccxusser.loccode
			      AND sec_loccxusser.userid='".$_SESSION['UserID']."'
			ORDER BY locationname";
		    $resultStkLocs = DB_query($sql,$db);
		    echo "<option selected Value=''>" . "TODOS...";
		    while ($myrow=DB_fetch_array($resultStkLocs)){
			
			    if (isset($_POST['StockLocation'])){
				    if ($myrow['loccode'] == $_POST['StockLocation']){
					 echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				    } else {
					 echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				    }
			    } elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				     echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			    } else {
				     echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			    }
		    }
		    echo '</select> &nbsp&nbsp';
	       */
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
			      }
			      else
			      {
				   echo "<option Value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] .'</option>';
			      }
		    }
		    echo '</select></td>';
		    
		    
		    
		if (isset($_GET['Quotations'])){
			$_POST['Quotations']=$_GET['Quotations'];
		}elseif(isset($_POST['Quotations'])){
		    $_POST['Quotations']=$_POST['Quotations'];
		}else{
		    $_POST['Quotations']='1';
		}
		
		echo "<td>"._('Estatus') . ' :</td><td> <select name="Quotations">';
		
		if ($_POST['Quotations']=='7'){
			if (Havepermission($_SESSION['UserID'],738, $db)==1){
				echo '<option selected VALUE="7">' . _('Levantamiento');
			}
				
			if (Havepermission($_SESSION['UserID'],174, $db)==1){
				echo '<option  VALUE="1">' . _('Cotizaciones');
			}
			if (Havepermission($_SESSION['UserID'],176, $db)==1){
				echo '<option VALUE="0">' . _('Pedidos de Venta Cerrados');
			}
			if (Havepermission($_SESSION['UserID'],175, $db)==1){
				echo '<option VALUE="2">' . _('Pedidos de Venta Abiertos');
			}
		
			if (Havepermission($_SESSION['UserID'],196, $db)==1){
				echo '<option VALUE="3">' . _('Pedidos de Venta Cancelados');
			}
		
			if (Havepermission($_SESSION['UserID'],204, $db)==1){
				echo '<option VALUE="4">' . _('Pedidos de Venta Facturados');
			}
			if (Havepermission($_SESSION['UserID'],146, $db)==1){
				echo '<option VALUE="5">' . _('Pedidos de Venta Remisionados');
			}
			if (Havepermission($_SESSION['UserID'],204, $db)==1){
				echo '<option VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
			}
		}
		elseif ($_POST['Quotations']=='1'){
			if (Havepermission($_SESSION['UserID'],738, $db)==1){
				echo '<option  VALUE="7">' . _('Levantamiento');
			}
			
			if (Havepermission($_SESSION['UserID'],174, $db)==1){
			echo '<option selected VALUE="1">' . _('Cotizaciones');
		    }
		    if (Havepermission($_SESSION['UserID'],176, $db)==1){
			echo '<option VALUE="0">' . _('Pedidos de Venta Cerrados');
		    }
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Pedidos de Venta Abiertos');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){	
			echo '<option VALUE="3">' . _('Pedidos de Venta Cancelados');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="4">' . _('Pedidos de Venta Facturados');
		    }
		     if (Havepermission($_SESSION['UserID'],146, $db)==1){	
			echo '<option VALUE="5">' . _('Pedidos de Venta Remisionados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
		    }
		} elseif ($_POST['Quotations']=='0') {
			if (Havepermission($_SESSION['UserID'],738, $db)==1){
				echo '<option  VALUE="7">' . _('Levantamiento');
			}
		    if (Havepermission($_SESSION['UserID'],174, $db)==1){
		    	echo '<option  VALUE="1">' . _('Cotizaciones');
		    }
		    if (Havepermission($_SESSION['UserID'],176, $db)==1){
			echo '<option selected VALUE="0">' . _('Pedidos de Venta Cerrados');
		    }
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Pedidos de Venta Abiertos');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){	
			echo '<option VALUE="3">' . _('Pedidos de Venta Cancelados');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="4">' . _('Pedidos de Venta Facturados');
		    }
		    if (Havepermission($_SESSION['UserID'],146, $db)==1){	
			echo '<option VALUE="5">' . _('Pedidos de Venta Remisionados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
		    }
	       } elseif ($_POST['Quotations']=='3') {
		    if (Havepermission($_SESSION['UserID'],174, $db)==1){
		    	echo '<option  VALUE="1">' . _('Cotizaciones');
		    }
		    if (Havepermission($_SESSION['UserID'],176, $db)==1){
			echo '<option  VALUE="0">' . _('Pedidos de Venta Cerrados');
		    }
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Pedidos de Venta Abiertos');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){	
			echo '<option selected VALUE="3">' . _('Pedidos de Venta Cancelados');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="4">' . _('Pedidos de Venta Facturados');
		    }
		     if (Havepermission($_SESSION['UserID'],146, $db)==1){	
			echo '<option VALUE="5">' . _('Pedidos de Venta Remisionados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
		    }
		} elseif ($_POST['Quotations']=='4') {
			if (Havepermission($_SESSION['UserID'],738, $db)==1){
				echo '<option  VALUE="7">' . _('Levantamiento');
			}
		    if (Havepermission($_SESSION['UserID'],174, $db)==1){
		    	echo '<option  VALUE="1">' . _('Cotizaciones');
		    }
		    if (Havepermission($_SESSION['UserID'],176, $db)==1){
			echo '<option  VALUE="0">' . _('Pedidos de Venta Cerrados');
		    }
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Pedidos de Venta Abiertos');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){	
			echo '<option VALUE="3">' . _('Pedidos de Venta Cancelados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option selected VALUE="4">' . _('Pedidos de Venta Facturados');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],146, $db)==1){	
			echo '<option VALUE="5">' . _('Pedidos de Venta Remisionados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
		    }
	   }elseif ($_POST['Quotations']=='5') {
	       	if (Havepermission($_SESSION['UserID'],738, $db)==1){
	       		echo '<option  VALUE="7">' . _('Levantamiento');
	       	}
		    if (Havepermission($_SESSION['UserID'],174, $db)==1){
		    	echo '<option  VALUE="1">' . _('Cotizaciones');
		    }
		    if (Havepermission($_SESSION['UserID'],176, $db)==1){
			echo '<option  VALUE="0">' . _('Pedidos de Venta Cerrados');
		    }
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Pedidos de Venta Abiertos');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){	
			echo '<option VALUE="3">' . _('Pedidos de Venta Cancelados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option  VALUE="4">' . _('Pedidos de Venta Facturados');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],146, $db)==1){	
			echo '<option selected VALUE="5">' . _('Pedidos de Venta Remisionados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
		    }
	       }elseif ($_POST['Quotations']=='6') {
	       	if (Havepermission($_SESSION['UserID'],738, $db)==1){
	       		echo '<option  VALUE="7">' . _('Levantamiento');
	       	}
		    if (Havepermission($_SESSION['UserID'],174, $db)==1){
		    	echo '<option  VALUE="1">' . _('Cotizaciones');
		    }
		    if (Havepermission($_SESSION['UserID'],176, $db)==1){
			echo '<option  VALUE="0">' . _('Pedidos de Venta Cerrados');
		    }
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Pedidos de Venta Abiertos');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){	
			echo '<option VALUE="3">' . _('Pedidos de Venta Cancelados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option  VALUE="4">' . _('Pedidos de Venta Facturados');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],146, $db)==1){	
			echo '<option  VALUE="5">' . _('Pedidos de Venta Remisionados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option selected VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
		    }
	       } else {
		    if (Havepermission($_SESSION['UserID'],174, $db)==1){
			echo '<option  VALUE="1">' . _('Cotizaciones');
		    }
		    if (Havepermission($_SESSION['UserID'],176, $db)==1){
			echo '<option VALUE="0">' . _('Pedidos de Venta Cerrados');
		    }
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){	
			echo '<option selected VALUE="2">' . _('Pedidos de Venta Abiertos');
		    }
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){	
			echo '<option VALUE="3">' . _('Pedidos de Venta Cancelados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="4">' . _('Pedidos de Venta Facturados');
		    }
		    if (Havepermission($_SESSION['UserID'],146, $db)==1){	
			echo '<option VALUE="5">' . _('Pedidos de Venta Remisionados');
		    }
		    if (Havepermission($_SESSION['UserID'],204, $db)==1){	
			echo '<option VALUE="6">' . _('Pedidos de Venta Parcialmente Facturados');
		    }
	       }
		echo '</select> </td></tr>';
		
		echo "<tr><td></td></tr>";
		if (Havepermission($_SESSION['UserID'],860, $db)==1)
		{
			
		}else{
			$UserName = $_SESSION['UserID'];
		}
		
		echo "<tr><td>"._('Vendedor') . ":</td><td><select name='SalesMan'> ";
		$sql="SELECT distinct salesmanname,salesmancode
			FROM salesman as sm
			LEFT JOIN areas as ar
			ON sm.area=ar.areacode
		     JOIN tags as tg
			ON ar.areacode=tg.areacode
			 JOIN sec_unegsxuser as u
			ON u.tagref = tg.tagref
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
		$sql="SELECT userid, realname FROM www_users";
		$result = DB_query($sql,$db,'','');
		//if (Havepermission($_SESSION['UserID'],860, $db)==1){
			echo "<option selected Value=''>" . "TODOS...";
		//}
		
		while ($myrow=DB_fetch_array($result))
		{
			if ($myrow['userid'] == $UserName)
			{
				echo "<option selected Value='" . $myrow['userid'] . "'>" . $myrow['realname'] .'</option>';
			}
			else
			{
				if (Havepermission($_SESSION['UserID'],860, $db)==1)
				{
				echo "<option Value='" . $myrow['userid'] . "'>" . $myrow['realname'] .'</option>';
				}
			}
		}
		echo '</select></td></tr>';
		
		echo "<tr><td colspan=4></td></tr>";
		
		//agrega busqueda por no. cliente
		echo "<tr><td>No. Cliente</td><td><input type='text' name='nocliente' size='10' value='".$_POST['nocliente']."'></td><td>&nbsp;</td></td><td>&nbsp;</td></tr>";
		
		echo "<tr><td colspan=4></td></tr>";
		
		echo "<tr><td colspan=4 align=center style='text-align:center'><br><input type=submit name='SearchOrders' VALUE='" . _('Buscar Ordenes o Facturas...') . "'></td></tr>";

	echo "
      </table>
      <hr>";
	
  if (isset($_POST['btnUnificar'])){ // se unifica tomando los datos del primer pedido seleccionado
  	$arrdata = $_POST['selectedorders'];
  	$arritems = array();
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
  				$HeaderSQL = "INSERT INTO salesorders ( orderno,
							debtorno,
							branchcode,
							customerref,
							comments,
							orddate,
							ordertype,
							shipvia,
							deliverto,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							contactphone,
							contactemail,
							freightcost,
							fromstkloc,
							deliverydate,
							quotedate,
							confirmeddate,
							quotation,
							deliverblind,
							salesman,
							placa,
							serie,
							kilometraje,
							tagref,
							taxtotal,
							totaltaxret,
							currcode,
							paytermsindicator,
							advance,
							UserRegister,
							puestaenmarcha,
							paymentname,
							nocuenta,
							extratext,
							nopedido,
							noentrada,
							noremision,
							idprospect,
							contid,
							typeorder,
							deliverytext
						       )
					Select ".$neworder.",
							debtorno,
							branchcode,
							customerref,
							comments,
							current_date,
							ordertype,
							shipvia,
							deliverto,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							contactphone,
							contactemail,
							freightcost,
							fromstkloc,
							current_date,
							current_date,
							current_date,
							1,
							deliverblind,
							salesman,
							placa,
							serie,
							kilometraje,
							tagref,
							taxtotal,
							totaltaxret,
							currcode,
							paytermsindicator,
							advance,
							'".$_SESSION['UserID']."',
							puestaenmarcha,
							paymentname,
							nocuenta,
							extratext,
							nopedido,
							noentrada,
							noremision,
							idprospect,
							contid,
							typeorder,
							deliverytext
					FROM salesorders
					WHERE orderno = ".$orderNo;
  				$r = DB_query($HeaderSQL,$db);
  				
  				$headerdone = true;
  			}
  			
  			//insertar partidas
  			  $qrypart = "Select 
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
							From salesorderdetails
							WHERE orderno = ".$orderNo;
  			  $rspart = DB_query($qrypart,$db);
  			  while ($rowspart = DB_fetch_array($rspart)){
  			  	
  			  		if (in_array($rowspart['stkcode'],$arritems)){
  			  			//upadte
  			  			$DetailSQL = "UpDate salesorderdetails
  			  						  Set quantity = quantity + ".$rowspart['quantity']."
  			  						  WHERE orderno = $neworder
  			  						  and stkcode = '".$rowspart['stkcode']."'
  			  						  ";
  			  		}
  			  		else{
  			  			
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
									  			  					($itemlineno,
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
  			  		
  			  		$r = DB_query($DetailSQL,$db);
  			  }
  			
  		}
  		
  		//crear oportunidad
  		$SQL = "INSERT INTO prospect_movimientos (
														areacod,
														debtorno,
														u_proyecto,
														dia,
														mes,
														anio,
														concepto,
														descripcion,
														u_user,
														cargo,
														prioridad,
														referencia,
														periodo_dev,
														erp,
														TipoMovimientoId,
														estimado,
														fecha,
														currcode,
														branchcode,
														fecha_compromiso,
														grupo_contable,
														confirmado,
														activo,
														u_entidad,
														catcode,
														idstatus,
														UserId,
														fecha_alta,
														clientcontactid
														)
												Select areacod,
														debtorno,
														u_proyecto,
														'".date("d")."',
														'".date("m")."',
														'".date("Y")."',
														concepto,
														descripcion,
														'".$_SESSION['UserID']."',
														cargo,
														prioridad,
														referencia,
														periodo_dev,
														erp,
														TipoMovimientoId,
														estimado,
														current_date,
														currcode,
														branchcode,
														fecha_compromiso,
														grupo_contable,
														confirmado,
														activo,
														u_entidad,
														catcode,
														idstatus,
														'".$_SESSION['UserID']."',
  														current_date,
  														clientcontactid
  												FROM prospect_movimientos
  												WHERE u_movimiento = '$umov'
  													";
  																	
  		$r = DB_query($SQL,$db);
  		$prospectid = DB_Last_Insert_ID($db,'prospect_movimientos','u_movimiento');
  		
		$sql2="INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
  					VALUES ('".$prospectid ."','Alta de oportunidad:".$_SESSION['UserID']."@: GENERADA POR UNIFICAR COTIZACIONES',Now(),0,'1','".$_SESSION['UserID']."','".$_SESSION['UserID']."','alta')";
  						
  		$result2= DB_query($sql2,$db);
  		
  		//actualizo pedido con id de prospecto y taxtotal
		$SQL = "UPDATE salesorders
  					SET idprospect=".$prospectid.",
  						taxtotal = ".$taxtotal."	
  				    WHERE orderno= " .  $neworder;
  		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de servicio de venta no se pudo actualizar');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
  		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
  		
  		prnMsg('Se han unificado las cotizaciones en la cotizacion No '.$neworder);
  	}
  }	
	
 if (isset($_POST['btnDuplicar'])){
 	$arrdata = $_POST['selectedorders'];
 	if (count($arrdata) > 0){
 		$ordenesDuplicadas="";
 		foreach($arrdata as $value){
 			$orderNo = $value;
 			
 			$sql = "Select idprospect FROM salesorders
 					WHERE orderno = $orderNo" ;
 			$res = DB_query($sql,$db);
 			$reg = DB_fetch_array($res);
 			$umov = $reg[0];
 			
 			$neworder = GetNextTransNo(30, $db);
 			$ordenesDuplicadas.=$neworder.",";
 			
 			$HeaderSQL = "INSERT INTO salesorders ( orderno,
							debtorno,
							branchcode,
							customerref,
							comments,
							orddate,
							ordertype,
							shipvia,
							deliverto,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							contactphone,
							contactemail,
							freightcost,
							fromstkloc,
							deliverydate,
							quotedate,
							confirmeddate,
							quotation,
							deliverblind,
							salesman,
							placa,
							serie,
							kilometraje,
							tagref,
							taxtotal,
							totaltaxret,
							currcode,
							paytermsindicator,
							advance,
							UserRegister,
							puestaenmarcha,
							paymentname,
							nocuenta,
							extratext,
							nopedido,
							noentrada,
							noremision,
							idprospect,
							contid,
							typeorder,
							deliverytext
						       )
					Select ".$neworder.",
							debtorno,
							branchcode,
							customerref,
							comments,
							current_date,
							ordertype,
							shipvia,
							deliverto,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							contactphone,
							contactemail,
							freightcost,
							fromstkloc,
							current_date,
							current_date,
							current_date,
							1,
							deliverblind,
							salesman,
							placa,
							serie,
							kilometraje,
							tagref,
							taxtotal,
							totaltaxret,
							currcode,
							paytermsindicator,
							advance,
							'".$_SESSION['UserID']."',
							puestaenmarcha,
							paymentname,
							nocuenta,
							extratext,
							nopedido,
							noentrada,
							noremision,
							idprospect,
							contid,
							typeorder,
							deliverytext	
					FROM salesorders
					WHERE orderno = ".$orderNo;					
			$r = DB_query($HeaderSQL,$db);

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
								)
						  Select orderlineno,
								".$neworder.",
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
							From salesorderdetails			
							WHERE orderno = ".$orderNo;
			
			$r = DB_query($DetailSQL,$db);
			
			//crear oportunidad
			$SQL = "INSERT INTO prospect_movimientos (
														areacod,
														debtorno,
														u_proyecto,
														dia,
														mes,
														anio,
														concepto,
														descripcion,
														u_user,
														cargo,
														prioridad,
														referencia,
														periodo_dev,
														erp,
														TipoMovimientoId,
														estimado,
														fecha,
														currcode,
														branchcode,
														fecha_compromiso,
														grupo_contable,
														confirmado,
														activo,
														u_entidad,
														catcode,
														idstatus,
														UserId,
														fecha_alta,
														clientcontactid
														)
												Select areacod,
														debtorno,
														u_proyecto,
														'".date("d")."',
														'".date("m")."',
														'".date("Y")."',
														concepto,
														descripcion,
														'".$_SESSION['UserID']."',
														cargo,
														prioridad,
														referencia,
														periodo_dev,
														erp,
														TipoMovimientoId,
														estimado,
														current_date,
														currcode,
														branchcode,
														fecha_compromiso,
														grupo_contable,
														confirmado,
														activo,
														u_entidad,
														catcode,
														idstatus,
														'".$_SESSION['UserID']."',
														current_date,
														clientcontactid
												FROM prospect_movimientos
												WHERE u_movimiento = '$umov'
									";
			
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
 	}
 }	
	

If (isset($StockItemsResult)) {

}

//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available
	if (isset($_POST['Quotations'])){
		$Quotations = $_POST['Quotations'];
	} else {
	  $Quotations=1;
	}
	
	echo"<table>";
	       echo"<tr>";
		    if ($Quotations==4) {
			 echo"<td><font size=3>Pedidos de Venta Facturados</td>";
		    }elseif ($Quotations==7) {
			 echo"<td><font size=3>Pedidos de Venta en Levantamiento</td>";
		    } elseif ($Quotations==3) {
			 echo"<td><font size=3>Pedidos de Venta Cancelados</td>";
		    } elseif ($Quotations==2) {
			 echo"<td><font size=3>Pedidos de Venta Abiertos</td>";
		    } elseif ($Quotations==1) {
			 echo"<td><font size=3>Cotizaciones</td>";
		    } elseif ($Quotations==0) {
			 echo"<td><font size=3>Pedidos de Venta Cerrados</td>";
		    } elseif ($Quotations==5) {
			 echo"<td><font size=3>Pedidos de Venta Remisionados</td>";
		    }elseif ($Quotations==6) {
			 echo"<td><font size=3>Pedidos de Venta Parcialmente Facturados</td>";
		    }
	       echo"</tr>";
	echo"</table>";
	
	if(!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}
	
	if (isset($_REQUEST['FolioFiscal']) && $_REQUEST['FolioFiscal'] !='') {
	    
			
			$SQL = "SELECT distinct salesorders.orderno,debtortrans.folio,
					debtorsmaster.name,
					shippers.shippername,
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
					salesorders.debtorno,";
			
			if($Quotations != 4) {
				$SQL .= "(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2)))+ (salesorders.taxtotal +salesorders.totaltaxret) AS ordervalue,";
			} else {
				$SQL .= "debtortrans.ovamount+debtortrans.ovgst AS ordervalue,";
			}
			
			$SQL .=	"tagdescription,
					salesorders.nopedido,
					salesorders.tagref,
					salesorders.idprospect,
					debtortrans.noremisionf
				FROM salesorders join debtortrans ON salesorders.orderno = debtortrans.order_ and debtortrans.type in (10,110,111,119,410,66)
					left join salesorderdetails on salesorders.orderno = salesorderdetails.orderno
					left join locations on salesorderdetails.fromstkloc=locations.loccode
					left join salesman ON salesman.salesmancode = salesorders.salesman
					left join shippers on shippers.shipper_id = debtortrans.shipvia,
					debtorsmaster,
					custbranch, paymentterms, sec_unegsxuser, tags
				WHERE ";
				
				if ($Quotations == 4 or $Quotations == 5){
					//$SQL = $SQL . " debtortrans.ovamount <> 0 and " ;
					$SQL = $SQL . " debtortrans.invtext not like '%cancela%' and " ;
				}
				
				$SQL = $SQL . " salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno";
				
				if ($_POST['nocliente'])
					$SQL.=" AND salesorders.debtorno = '".$_POST['nocliente']."'";
				
				$SQL=$SQL." AND (debtortrans.folio like '%". $_REQUEST['FolioFiscal'] ."%' OR  replace('|','',debtortrans.folio) like '%" .$_REQUEST['FolioFiscal'] . "%')
				AND paymentterms.termsindicator=salesorders.paytermsindicator
			        
				-- AND (locations.tagref=sec_unegsxuser.tagref or salesorders.tagref=sec_unegsxuser.tagref )
				AND (case when locations.tagref=sec_unegsxuser.tagref then locations.tagref else salesorders.tagref end  )=locations.tagref
				AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='".$_SESSION['UserID']."')	
			        AND salesorders.tagref=tags.tagref						
				AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
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
				
     } elseif (isset($_REQUEST['OrderNumber']) && $_REQUEST['OrderNumber'] !='' ) {
	    
			
			$SQL = "SELECT distinct salesorders.orderno,
					debtorsmaster.name,
					shippers.shippername,
					salesorders.UserRegister,
					salesman.salesmanname,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.quotation as estatus,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					salesorders.currcode,
					paymentterms.type,
					debtortrans.id,
					debtortrans.prd,
					debtortrans.origtrandate,
					debtortrans.type,
					debtortrans.transno,
					salesorders.debtorno,
					(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2)))+ (salesorders.taxtotal +salesorders.totaltaxret) AS ordervalue
					,tagdescription,
					salesorders.nopedido,salesorders.tagref,
					salesorders.idprospect,
					debtortrans.folio,
					debtortrans.noremisionf
				FROM salesorders 
					left join salesorderdetails on salesorders.orderno = salesorderdetails.orderno
					left join locations on salesorderdetails.fromstkloc=locations.loccode
					left join salesman ON salesman.salesmancode = salesorders.salesman
					left join debtortrans ON salesorders.orderno = debtortrans.order_ and debtortrans.type in (10,110,111,119,410)
					left join shippers on shippers.shipper_id = debtortrans.shipvia,
					debtorsmaster,
					custbranch, paymentterms, sec_unegsxuser, tags
				WHERE 
				 salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno";
				
				if ($_POST['nocliente'])
					$SQL.=" AND salesorders.debtorno = '".$_POST['nocliente']."'";
			
				$SQL=$SQL." AND salesorders.orderno=". $_REQUEST['OrderNumber'] ."
				
				-- AND salesorders.quotation =" .$Quotations . "
				AND paymentterms.termsindicator=salesorders.paytermsindicator
			        
				-- AND (locations.tagref=sec_unegsxuser.tagref or salesorders.tagref=sec_unegsxuser.tagref )
				AND (case when locations.tagref=sec_unegsxuser.tagref then locations.tagref else salesorders.tagref end  )=locations.tagref
				AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='".$_SESSION['UserID']."')		
			        AND salesorders.tagref=tags.tagref						
				AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
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
				
     } else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */
		
	       if (isset($_REQUEST['SelectedCustomer'])) {


			//echo "consulta";
			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT distinct salesorders.orderno,
						debtorsmaster.name,
						salesorders.UserRegister,
						salesman.salesmanname,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
						salesorders.quotation as estatus,
					        salesorders.printedpackingslip,
						salesorders.currcode,
						paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,
						(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + (salesorders.taxtotal +salesorders.totaltaxret) AS ordervalue
						,tagdescription,
						salesorders.nopedido,salesorders.tagref,
					    salesorders.idprospect
					FROM salesorders left join salesorderdetails on salesorders.orderno = salesorderdetails.orderno
					left join locations on salesorderdetails.fromstkloc=locations.loccode
					left join salesman ON salesman.salesmancode = salesorders.salesman,
						debtorsmaster,
						custbranch, paymentterms,  tags , sec_unegsxuser
					WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					-- AND (locations.tagref=sec_unegsxuser.tagref or salesorders.tagref=sec_unegsxuser.tagref )
					AND (case when locations.tagref=sec_unegsxuser.tagref then locations.tagref else salesorders.tagref end  )=locations.tagref
					AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='".$_SESSION['UserID']."')	
					AND salesorders.tagref=tags.tagref						
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					
					AND salesorders.branchcode = custbranch.branchcode";
					if ($Quotations!=4 and $Quotations!=5){
					    //$SQL=$SQL." AND salesorderdetails.completed=0";
					    $SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					
					if ($_POST['nocliente'])
						$SQL.=" AND salesorders.debtorno = '".$_POST['nocliente']."'";
					
					$SQL=$SQL." AND paymentterms.termsindicator=salesorders.paytermsindicator
					AND salesorders.quotation =" .$Quotations . "
					/*AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] ."'*/
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] ."'";
				if (strlen($_POST['StockLocation'])>0){	
				   $SQL= $SQL. " AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
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

				
				$SQL=$SQL." ORDER BY salesorders.orderno, tagdescription ";		
			} else {
				//echo "consulta";
				$SQL = "SELECT distinct debtortrans.folio,
						shippers.shippername,
						 salesorders.orderno,
						debtorsmaster.name,
						debtortrans.id,
						salesorders.UserRegister,
						salesorders.quotation as estatus,
						salesman.salesmanname,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					    salesorders.printedpackingslip,
						salesorders.deliverydate,
						salesorders.currcode,
						paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,
						debtortrans.noremisionf";
					
					if($Quotations != 4) {
						$SQL .= "(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2)))+ (salesorders.taxtotal +salesorders.totaltaxret) AS ordervalue,";
					} else {
						$SQL .= "case when debtortrans.type=111 then 0 else debtortrans.ovamount+debtortrans.ovgst end  AS ordervalue,";
					}
					
					$SQL .=	",tagdescription,
						salesorders.nopedido,salesorders.tagref,
					salesorders.idprospect
					FROM salesorders left join salesorderdetails on salesorders.orderno = salesorderdetails.orderno
					left join locations on salesorderdetails.fromstkloc=locations.loccode
					left join salesman ON salesman.salesmancode = salesorders.salesman
					left join debtortrans ON salesorders.orderno = debtortrans.order_ and debtortrans.type in (10,110,111,119,410)
					left join shippers on shippers.shipper_id = debtortrans.shipvia,
						debtorsmaster,
						
						custbranch, paymentterms, sec_unegsxuser, tags
					WHERE ";
					
					if ($Quotations == 4 or $Quotations == 5){
						//$SQL = $SQL . " debtortrans.ovamount <> 0 and " ;
						$SQL = $SQL . " debtortrans.invtext not like '%cancela%' and " ;
					}
					
					if ($_POST['nocliente'])
						$SQL.=" AND salesorders.debtorno = '".$_POST['nocliente']."'";
					
					$SQL = $SQL . " salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					-- AND (locations.tagref=sec_unegsxuser.tagref or salesorders.tagref=sec_unegsxuser.tagref )
					AND (case when locations.tagref=sec_unegsxuser.tagref then locations.tagref else salesorders.tagref end  )=locations.tagref
					AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='".$_SESSION['UserID']."')	
					AND salesorders.tagref=tags.tagref						
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorders.quotation =" .$Quotations ;
					if ($Quotations!=4 and $Quotations!=5){
					    //$SQL=$SQL." AND salesorderdetails.completed=0";
					    $SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					$SQL=$SQL." AND paymentterms.termsindicator=salesorders.paytermsindicator
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] . "'";
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
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
					
				   $SQL= $SQL."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						salesorders.debtorno,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.nopedido
					ORDER BY debtortrans.folio, salesorders.orderno, tagdescription ";

			 }
	       } else { //no customer selected
		
			if (isset($_REQUEST['SelectedStockItem'])) {
		
				$SQL = "SELECT distinct salesorders.orderno,
						debtorsmaster.name,
						salesorders.UserRegister,
						salesman.salesmanname,
						-- debtortrans.id,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.quotation as estatus,
					  	salesorders.printedpackingslip,
						salesorders.deliverydate,
						salesorders.currcode,
						paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,
						(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + (salesorders.taxtotal +salesorders.totaltaxret) AS ordervalue
						,tagdescription,
						salesorders.nopedido,salesorders.tagref,
					    salesorders.idprospect
					FROM salesorders left join salesorderdetails on salesorders.orderno = salesorderdetails.orderno
					left join locations on salesorderdetails.fromstkloc=locations.loccode
					left join salesman ON salesman.salesmancode = salesorders.salesman,
						debtorsmaster,
						custbranch, paymentterms,  tags , sec_unegsxuser
					WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					-- AND (locations.tagref=sec_unegsxuser.tagref or salesorders.tagref=sec_unegsxuser.tagref )
					AND (case when locations.tagref=sec_unegsxuser.tagref then locations.tagref else salesorders.tagref end  )=locations.tagref
					AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='".$_SESSION['UserID']."')	
					AND salesorders.tagref=tags.tagref						
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'

					
					AND salesorders.branchcode = custbranch.branchcode";
					if ($Quotations!=4 and $Quotations!=5){
					    //$SQL=$SQL." AND salesorderdetails.completed=0";
					    $SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					
					if ($_POST['nocliente'])
						$SQL.=" AND salesorders.debtorno = '".$_POST['nocliente']."'";
					
					$SQL=$SQL." AND paymentterms.termsindicator=salesorders.paytermsindicator
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] . "'";
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
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
					
					$SQL=$SQL."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip,
						salesorders.nopedido
					ORDER BY salesorders.orderno, tagdescription ";
			 } else {
			    	//echo "consulta";
				$SQL = "SELECT  debtortrans.folio,
						if(SUBSTRING(debtortrans.folio , LOCATE('|', debtortrans.folio )+1) = '', SUBSTRING(debtortrans.folio , 1,LOCATE('|', debtortrans.folio )-1), SUBSTRING(debtortrans.folio , LOCATE('|', debtortrans.folio )+1)) AS folioFiltrado,
						salesorders.orderno,
						debtorsmaster.name,
						salesorders.UserRegister,
						salesorders.quotation as estatus,
						salesorders.currcode,
						salesman.salesmanname,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						debtortrans.id,
						salesorders.deliverto,
						salesorders.deliverydate,
					        salesorders.printedpackingslip,
					        paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,";
						
					if($Quotations != 4) {	    
					    $SQL .= "(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2)))+ (salesorders.taxtotal +salesorders.totaltaxret) AS ordervalue,";
					} else {
					    //$SQL .= "debtortrans.ovamount+debtortrans.ovgst AS ordervalue,";
					    $SQL .= "case when debtortrans.type=111 then 0 else debtortrans.ovamount+debtortrans.ovgst end  AS ordervalue,";
					
					}
					
					$SQL .=	"
						tagdescription,
						tags.tagref,
						salesorders.nopedido,
						debtortrans.type,
						debtortrans.tagref,
						debtortrans.prd,
						debtortrans.origtrandate,
						debtortrans.transno,salesorders.tagref,
					    salesorders.idprospect,
						debtortrans.noremisionf,
						shippers.shippername
					FROM salesorders left join debtortrans ON salesorders.orderno = debtortrans.order_ and debtortrans.type in (10,110,111,119,410  ,66)
					left join salesorderdetails on salesorders.orderno = salesorderdetails.orderno
					left join locations on salesorderdetails.fromstkloc=locations.loccode
					left join salesman ON salesman.salesmancode = salesorders.salesman
					left join shippers on shippers.shipper_id = debtortrans.shipvia,
						debtorsmaster,
						custbranch, paymentterms, sec_unegsxuser, tags
					WHERE ";
					
					if ($Quotations == 4 or $Quotations == 5  or $Quotations == 6){
						//$SQL = $SQL . " debtortrans.ovamount <> 0 and " ;
						$SQL = $SQL . " debtortrans.invtext not like '%cancela%' and " ;
					}
					
					$SQL = $SQL . " salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					-- AND (locations.tagref=sec_unegsxuser.tagref or salesorders.tagref=sec_unegsxuser.tagref )
					AND (case when locations.tagref=sec_unegsxuser.tagref then locations.tagref else salesorders.tagref end  )=locations.tagref
					AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='".$_SESSION['UserID']."')	
					AND salesorders.tagref=tags.tagref						
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					
					AND salesorders.branchcode = custbranch.branchcode";
					
					if ($_POST['nocliente'])
						$SQL.=" AND salesorders.debtorno = '".$_POST['nocliente']."'";
					
					if ($Quotations!=4 and $Quotations!=5){
            					
						$SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
						//$SQL=$SQL." AND salesorderdetails.completed=0";
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					$SQL=$SQL." AND paymentterms.termsindicator=salesorders.paytermsindicator
					AND salesorders.quotation =" .$Quotations ;
					
					if (strlen($_POST['UnidNeg'])>0){	
					   $SQL= $SQL. " AND salesorders.tagref = '". $_POST['UnidNeg'] . "'";
					}
					
					if (strlen($SalesMan) > 0) {
						$SQL= $SQL. " AND salesorders.salesman = '". $SalesMan . "'";
					}
					
					if (strlen($UserName) > 0) {
						$SQL= $SQL. " AND salesorders.UserRegister = '". $UserName . "'";
					}
					
					if (strlen($_POST['OrderNumber'])>0){	
						$SQL=$SQL." AND salesorders.orderno=". $_REQUEST['OrderNumber'] ;
					}
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
					}
					$SQL=$SQL."
					GROUP BY debtortrans.folio,
						debtortrans.id,
						debtortrans.type,
						debtortrans.tagref,
						debtortrans.transno,
						debtortrans.origtrandate,
						debtortrans.prd,
						salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip,
						salesorders.nopedido
					ORDER BY 0+folioFiltrado,salesorders.orderno, tagdescription ";
					//echo '<br>'.$SQL.'<br>';
			 }

	       } //end selected customer
     } //end not order number selected
	$ErrMsg = _('No hay pedidos de venta o cotizaciones pendientes');
	//$SQL=$SQL.'limit 10';
	//if ($_SESSION['UserID']=='admin') echo '<pre>'.$SQL.'<BR>';
	
	//echo "<pre>$SQL";
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg,'');
        
	/*show a table of the orders returned by the SQL */
	echo '<table cellpadding=2 colspan=7 WIDTH=100%>';
	
        $fecha_actual = strtotime(date("d-m-Y H:i:00",time()));

	  if (isset($_POST['Quotations']) and $_POST['Quotations']=='0'){
		$tableheader = "<tr>
				<th nowrap>" . _('Sel') . "</th>
			        <th nowrap>" . _('Modificar') . "</th>
				<th nowrap>" . _('Factura') . "</th>
				<th nowrap>" . _('Folio') . "</th>
				<th nowrap>" . _('Disp. Note') . "</th>
			        <th nowrap>" . _('Termino<br>Pago') . "</th>				
				<th nowrap>" . _('Cliente') . "</th>
				<th nowrap>" . _('Pedido') . " #</th>
				<th nowrap>" . _('Fecha') . "</th>
				<th nowrap>" . $_SESSION['LabelText1'] . "</th>
				<th nowrap>" . _('Usuario') . "</th>
				<th nowrap>" . _('Vendedor') . "</th>
				<th nowrap>" . _('Moneda') . "</th>
				<th nowrap>" . _('Total') . "</th>";
	       if ($Quotations!=4){		
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){
			$tableheader=$tableheader."<th>" . _('Cancelar') . "</th>";
		    }else{
			$tableheader=$tableheader."<th></th>";
		    }
		    if (Havepermission($_SESSION['UserID'],203, $db)==1)
		    {
			$tableheader=$tableheader."<th>" . _('Venta<br>Perdida') . "</th>";
		    }else{
			$tableheader=$tableheader."<th></th>";
		    }
	       } elseif ($Quotations==4 or $Quotations==5) {
		    if (Havepermission($_SESSION['UserID'],177, $db)==1){
		    	$tableheader=$tableheader."<th>" . _('Cancelar Factura') . "</th>";
		    }else{
			$tableheader=$tableheader."<th></th>";
		    }
		    if (Havepermission($_SESSION['UserID'],302, $db)==1)
		    {
			$tableheader=$tableheader."<th>" . _('Mod<br>Trab') . "</th>";
		    }else{
			$tableheader=$tableheader."<th></th>";
		    }
		    if (Havepermission($_SESSION['UserID'],3014, $db)==1)
		    {
		    	$tableheader=$tableheader."<th>" . _('Mod<br>Fact') . "</th>";
		    }else{
		    	$tableheader=$tableheader."<th></th>";
		    }
	       }
	       $tableheader=$tableheader."<th nowrap>Pagares</th><th>" . _('U. Negocio') . "</th>";
	       if($oportunidadprospectoPermiso==1){
		   		$tableheader=$tableheader."<th>". _('Oportunidad') ."</th>";		   							
	       }else{
	       		$tableheader=$tableheader."<th></th>";
	       	 
	       }
		   $tableheader=$tableheader."</tr>";
	  } else {
		$tableheader = "<tr>
				<th nowrap>" . _('Sel') . "</th>
		                <th nowrap>" . _('Modificar') . "</th>
				<th nowrap>" . _('Folio') . "</th>
			    <th nowrap>" . _('Imprimir') . "</th>
			    <th nowrap>" . _('Termino<br>Pago') . "</th>
			    <th nowrap>" . _('Metodo<br>Envio') . "</th>
				<th nowrap>" . _('Cliente') . "</th>
				<th nowrap>" . _('N. Pedido OC') . "</th>
				<th nowrap>" . _('Referencia') . " #</th>
				<th nowrap>" . _('Fecha') . "</th>
				<th nowrap>" . _('Usuario') . "</th>
				<th nowrap>" . _('Vendedor') . "</th>
				<th nowrap>" . _('Moneda') . "</th>
				<th nowrap>" . _('Total') . "</th>";
			      if ($Quotations!=4){
				   if (Havepermission($_SESSION['UserID'],196, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Cancelar') . "</th>";
				   }else{
					$tableheader=$tableheader."<th></th>";
				   }
				   if (Havepermission($_SESSION['UserID'],203, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Venta<br>Perdida') . "</th>";
				   }else{
					$tableheader=$tableheader."<th></th>";
				   }
			      } else{
				   if (Havepermission($_SESSION['UserID'],177, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Cancelar Factura') . "</th>";
				   }else{
					$tableheader=$tableheader."<th></th>";
				   }
				   if (Havepermission($_SESSION['UserID'],302, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Mod<br>Trab') . "</th>";
				   }else{
					$tableheader=$tableheader."<th></th>";
				   }
			      }
			      if (Havepermission($_SESSION['UserID'],3014, $db)==1)
			       {
			      $tableheader=$tableheader."<th>" . _('Mod<br>Fact') . "</th>";
			      }else{
			      $tableheader=$tableheader."<th></th>";
			      }
/*esto le aumente un th */  $tableheader=$tableheader."<th nowrap>Pagares</th><th>" . _('U. Negocio') . "</th>";
			       if (Havepermission($_SESSION['UserID'],277, $db)==1){
			         $tableheader=$tableheader."<th>" . _('Modificar Vend.') . "</th>";
			       }else{
				 $tableheader=$tableheader."<th></th>";
			       }
			    if ($permisoremision==1){
				$tableheader=$tableheader."<th>" . _('Remision/Ticket') . "</th>";
			    }else{
					$tableheader=$tableheader."<th></th>";
				   }
	   			if($oportunidadprospectoPermiso==1){
		   			$tableheader=$tableheader."<th>". _('Oportunidad') ."</th>";		   							
	       		}else{
	       			$tableheader=$tableheader."<th></th>"; 
	    	   }
			$tableheader=$tableheader."</tr>";
	  }
	
     echo $tableheader;
     $fechanio=date('Y');
     $fechames=date('m');
     $fechadia=date('d');
     $fechahoy=$fechanio.'-'.$fechames.'-'.$fechadia;
     $j = 1;
     $k=0; //row colour counter
    $montoTotal = 0;
    $permisocerrado=Havepermission($_SESSION['UserID'],217, $db);
    $fixedQuotation= $Quotations;
    
     while ($myrow=DB_fetch_array($SalesOrdersResult)) {
	$typedoc='';
	$numorden='';
	
	$foliox='';
	$PrintDispatchNote='&nbsp;';
	  $orddate=$myrow['orddate'];
	  $orderno=$myrow['orderno'];
	  $escredito =$myrow['numberofpayments'];
	  $generapagares =$myrow['generatecreditnote'];
	  $tagref =$myrow['tagref'];
	   $tagrefcot =$myrow['tagref'];
	  $ReenviarXSA='';
	  $serie='';
	  $folio ='';
	   $folio_elec = $myrow['folio'];
	   $myrow['ordervalue']=0;
	   
	   if ($myrow['estatus']!="")
			$Quotations = $myrow['estatus'];
	   else
	   		$Quotations = $fixedQuotation;
	   
	    //aqui se recupera la fecha con la que sera comparada la fecha actual para poder dar permiso o no de cancelar la factura
		if ($Quotations==4 or $Quotations==5 or $Quotations==6){
		    $sql="select type,transno,folio,origtrandate,tagref,id,prd,ovamount+ovgst as orderval
			 from debtortrans
			 where type in (110,10,119,111,410,125,109,66) and folio='".$folio_elec."' and order_=".$orderno;
		 
		   
		    $Fechacomparacion = DB_query($sql,$db,'','');
	
		    while ($myrowcomparacion=DB_fetch_array($Fechacomparacion)) {
				if ($myrowcomparacion[0]<>'0')
				{
				     $typedoc=$myrowcomparacion[0];
				     $numorden = $myrowcomparacion[1];
				     //$folio_elec = $myrowcomparacion[2];
				     $orddate =$myrowcomparacion[3];
				     $fechaemision=$myrowcomparacion[3];
				     $idfactura=$myrowcomparacion[5];
				     $myrow['ordervalue']=$myrowcomparacion[6];
				}
				else
				{
				     $typedoc='';
				     $numorden='';
				     $folio_elec = '&nbsp;';
				}
				 $transno=$myrowcomparacion['transno'];
				 $periodocontable=$myrowcomparacion['prd'];
				
				 $typedeb=$myrowcomparacion['type'];
				 $tagref=$myrowcomparacion['tagref'];
				 $foliox=$myrowcomparacion['folio'];
				 $orddate=$myrowcomparacion['origtrandate'];
				  $myrow['ordervalue']=$myrowcomparacion['orderval'];
				 $separa = explode('|',$foliox);
				 $serie = $separa[0];
				 if ($typedeb==410){
					$folio = $separa[0];
				 }else{
					$folio = $separa[1];
				 }
				 
				 // trae el key y rfc de la empresa
				 $SQLkey=" SELECT l.taxid,l.address5,t.tagdescription,t.typeinvoice,t.datechange,l.legalid,l.legalname
				       FROM legalbusinessunit l, tags t
				       WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
				
				       
				 $Resultag= DB_query($SQLkey,$db,'','');
				 if (DB_num_rows($Resultag)==1) {
				 	$myrowtags = DB_fetch_array($Resultag);
				 	$rfc=trim($myrowtags['taxid']);
				 	$keyfact=$myrowtags['address5'];
					$tipofacturacionxtag=$myrowtags['typeinvoice'];
					$fechacambio=$myrowtags['datechange'];
					$legalid=$myrowtags['legalid'];
					$legalname=$myrowtags['legalname'];
				      }
				 //echo $periodocontable.' '.$legalid;
				 $statusPeriodo= TraestatusPeriod($legalid,$periodocontable,$db);
				 //$liga = GetUrlToPrint($tagref,$typeinvoice,$db);
				 
				 $sql="select date(fechacorte) as fechacorte
				      from usrcortecaja
				      where u_status=0 and tag=".$tagref;
				      $Fechacorte = DB_query($sql,$db,'','');
				 while ($myrowcorte=DB_fetch_array($Fechacorte)) {
				      $fechacorte=$myrowcorte['fechacorte'];
				 }
		    }
		    
	       }else{
			if ($Quotations==6){
				$folio_elec = $myrow['folio'];
				$typedoc=$myrow['type'];
				$numorden=$myrow['orderno'];
				$tagref=$myrow['tagref'];
				$periodocontable=$myrow['prd'];
				$fechaemision=$myrow['origtrandate'];
				$transno=$myrow['transno'];
				$typedeb=$myrow['type'];
				$idfactura=$myrow['id'];
				// trae el key y rfc de la empresa
			 $SQLkey=" SELECT l.taxid,l.address5,t.tagdescription,t.typeinvoice,t.datechange,l.legalid
			       FROM legalbusinessunit l, tags t
			       WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
			       
			 $Resultag= DB_query($SQLkey,$db,'','');
			 if (DB_num_rows($Resultag)==1) {
			 	$myrowtags = DB_fetch_array($Resultag);
			 	$rfc=trim($myrowtags['taxid']);
			 	$keyfact=$myrowtags['address5'];
				$tipofacturacionxtag=$myrowtags['typeinvoice'];
				$fechacambio=$myrowtags['datechange'];
				$legalid=$myrowtags['legalid'];
			      }
			 //echo $periodocontable.' '.$legalid;
			 $statusPeriodo= TraestatusPeriod($legalid,$periodocontable,$db);
			}else{ 
				$typedoc='';
				$numorden='';
				$folio_elec = '&nbsp;';
			}
	       }
		
	if (in_array($myrow['tagdescription'],$listaloccxbussines)){
	    
	
	       if ($k==1){
		    echo '<tr class="EvenTableRows">';
		    $k=0;
	       } else {
		    echo '<tr class="OddTableRows">';
		    $k++;
	       }
	}else{
	      echo '<tr bgcolor="beige">';
	    
	}
	
	echo '<td style="text-align:center"><input type="checkbox" name="selectedorders[]" value="'.$myrow['orderno'].'"></td>';
	
	
            // echo $Quotations;
			
				$ModifyPage = $myrow['orderno'];
			
				if ($Quotations!=4 and $Quotations!=5 ){
					//echo "entra";
					if ($Quotations==0 and $permisocerrado==0){
						$ModifyPage = "";//$myrow['orderno'];
					}else{
						//echo "entra aqui";
						$ModifyPage = $rootpath . "/".$paginapedidos."?" . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
					}
				
				} else {
					$ModifyPage = $myrow['orderno'];
				}
			
	       
	       
	       $skipToDelivery = "";
	       if(empty($_SESSION['SkipToDeliveryDetails']) == FALSE) {
	           $skipToDelivery = "&skipToDeliveryDetails=1";
	       }
	       
	       $Confirm_Invoice = $rootpath . '/'.$paginapedidos.'?' . SID . '&ModifyOrderNumber=' .$myrow['orderno'] . $skipToDelivery;
	       
	       $liga = "";
		   $liga2 = "";
	        //aqui es para la impresion de la factura
	        if ($_SESSION['PackNoteFormat']==1 and $Quotations!=4 and $Quotations!=5 and $Quotations!=6){ //Laser printed A4 default
			// $PrintDispatchNote = $rootpath . '/PDFSalesOrderQuotePageHeader.php?' . SID . 'TransNo=' . $myrow['orderno'];
			if ($_SESSION['TypeQuotation']==0){
				if($_SESSION['PDFCotizacionBD']==1){
					if($Quotations==0){
						$liga='PDFCotizacionTemplateV2.php?tipodocto=3&';
						$liga2 = 'PDFCotizacionTemplateV2.php?tipodocto=3&';
					}else{
						$liga='PDFCotizacionTemplateV2.php?tipodocto=3&';
						$liga2 = 'PDFCotizacionTemplateV2.php?tipodocto=2&';
						
					}
					
				}else{
					$liga='PDFSalesOrderQuotePageHeader.php?';
				}
				
				$PrintDispatchNote = $rootpath . '/'.$liga . SID . 'TransNo=' . $myrow['orderno'].'&Tagref='.$tagrefcot;
				$PrintQuotation = $rootpath . '/'.$liga . SID . 'TransNo=' . $myrow['orderno'].'&Tagref='.$tagrefcot;
				$liga2 = $rootpath . '/'.$liga2 . SID . 'TransNo=' . $myrow['orderno'].'&Tagref='.$tagrefcot;
				//echo 'entraaa:'.$tagrefcot;
			}else{
				$liga = GetUrlToPrint2($tagref,10,$db);
				if($_SESSION['PDFCotizacionBD']==1){
					//$liga='PDFCotizacionTemplateV2.php?tipodocto=1';
					//$liga2 = 'PDFCotizacionTemplateV2.php?tipodocto=2&';
					
					if($Quotations==0){
						$liga='PDFCotizacionTemplateV2.php?tipodocto=3&';
						$liga2 = 'PDFCotizacionTemplateV2.php?tipodocto=3&';
					}else{
						$liga='PDFCotizacionTemplateV2.php?tipodocto=3&';
						$liga2 = 'PDFCotizacionTemplateV2.php?tipodocto=2&';
					
					}
					
				}
				
				$PrintQuotation = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='. $myrow['orderno'].'&Tagref='.$tagrefcot;
				$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='. $myrow['orderno'].'&Tagref='.$tagrefcot;
				$liga2 = $rootpath . '/' . $liga2  . '&' . SID . 'PrintPDF=Yes&TransNo='. $myrow['orderno'].'&Tagref='.$tagrefcot;
			}
			
	        } elseif($Quotations==4 or $Quotations==5 or $Quotations==6) { //pre-printed stationery default			
	        	
				if ($tipofacturacionxtag==1){
				    $PrintDispatchNote=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
				} elseif($tipofacturacionxtag==2){
					
				    if ($fechaemision<$fechacambio){
						if($_SESSION['EnvioXSA']==0){
						//$tagrefx=$tagref*-1;
					    	$liga = 'PDFInvoice.php';//GetUrlToPrint($tagref,10,$db);
					    	if ($_SESSION['DatabaseName']=="erpatm")
					    		$liga = "PDFFactura.php";
				    
					    	$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='.$myrow['orderno'].'&Tagref='.$tagref;
						}else{
					    	$PrintDispatchNote=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
						}
			   		 }else{
			   		 	$liga = 'PDFInvoice.php';
			   		 	if ($_SESSION['DatabaseName']=="erpatm")
			   		 		$liga = "PDFFactura.php";
			   		 	
						if($typedoc==111){
							$PrintDispatchNote=$rootpath . '/PDFInternalInvoice.php?OrderNo='.$myrow['orderno'].'&TransNo=' . $transno .'&Type='.$typedeb.'&Tagref='.$tagref;
						}else{
							$PrintDispatchNote=$rootpath . '/'.$liga.'?OrderNo='.$myrow['orderno'].'&TransNo=' . $transno .'&Type='.$typedeb;
							if($typedoc==119){
								$PrintDispatchNote=$rootpath . '/'.$liga.'?OrderNo='.$myrow['orderno'].'&TransNo=' . $transno .'&Type='.$typedeb;
						}
					}
			    }
			}elseif($tipofacturacionxtag==3){
			    if ($typedeb!=119){
				$typeinvoice=10;
				$liga="PDFInvoiceTemplate.php";
			    }else{
				$typeinvoice=119;
				$liga="PDFRemisionTemplate.php";
			    }
			    $PrintDispatchNote=$rootpath . '/'.$liga.'?OrderNo='.$myrow['orderno'].'&TransNo=' . $transno .'&Type='.$typedeb;
			}else{
			    $liga = $liga = 'PDFInvoice.php';//GetUrlToPrint($tagref,10,$db);
			     if($tipofacturacionxtag==5){
					$liga = 'PDFInvoice.php';
			    	$PrintDispatchNote = $rootpath . '/' . $liga  . '?OrderNo='.$myrow['orderno'].'&TransNo='.$transno.'&Type='.$typedeb;
			     } else if($tipofacturacionxtag==4) {
			     	
			     	$liga = 'PDFInvoice.php';
			     	$PrintDispatchNote = $rootpath . '/' . $liga  . '?OrderNo='.$myrow['orderno'].'&TransNo='.$transno.'&Type='.$typedeb;
			     } else {
			    	$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='.$myrow['orderno'].'&Tagref='.$tagref;
			     }
			}
		}
		
		//echo $PrintDispatchNote;
		if ($escredito<>'0')
		{
		    $termpago ='CR';
		}
		else
		{
		    $termpago ='CO';
		}
		
		
		if (Havepermission($_SESSION['UserID'],302, $db)==1 and $typedoc<>'' and ($Quotations==4 or $Quotations==5 or $Quotations==6)){
		    
			# Si tiene permiso y el tipo de documento es una factura con folio gerenado
			//$ssql = "select count(*) from salesstockproperties where orderno='" . $myrow['orderno'] . "'";
		    
			/* SOLO MOSTRAR EL ICONO DE TRABAJADORES SI NO SE HAN DEFINIDO AL FACTURAR. UNA
			VEZ QUE YA SE SELECCIONARON NO SE PUEDEN CAMBIAR
			*/
		    
			$ssql = "select salesorderdetails.orderno, sum(salesstockproperties.valor IS NULL)  as porcapturar  
				from salesorderdetails JOIN stockmaster ON  salesorderdetails.stkcode = stockmaster.stockid  
				LEFT JOIN stockcatproperties ON  stockmaster.categoryid =  stockcatproperties.categoryid 
				LEFT JOIN salesstockproperties ON salesstockproperties.orderno =  salesorderdetails.orderno
				where salesorderdetails.orderno=" . $myrow['orderno'] . " and stockcatproperties.reqatsalesorder = 1
				GROUP BY salesorderdetails.orderno";
			    
			#echo $ssql;

			$resultProp = DB_query($ssql ,$db,'','');
			$myrowProp = DB_fetch_row($resultProp);
		
			if (intval($myrowProp[1])>0) # si tiene productos con categoorias a los cuales se asigna trabajadores
			{
				$link_modTrabajadores = "<a href='ChangePropertiesInvoice.php?orderno=". $myrow['orderno'] ."&folio=". $folio_elec ."' title='Modificar Trabajadores'><img src='part_pics/Users-2.png' border=0></a>";
			} elseif(Havepermission($_SESSION['UserID'],985, $db)==1){
				$link_modTrabajadores = "<a href='ChangePropertiesInvoice.php?orderno=". $myrow['orderno'] ."&folio=". $folio_elec ."' title='Modificar Trabajadores'><img src='part_pics/Users-2.png' border=0></a>";
			}else{
				$link_modTrabajadores ="&nbsp;";
			}
			
		} else {
			$link_modTrabajadores ="&nbsp;";
		}
		if (Havepermission($_SESSION['UserID'],3014, $db)==1){
			$modfactr=$rootpath . '/modifyInvoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&type='.$typedeb;
			$modfactr="<a target='_blank' href='".$modfactr."'><img src='images/Edit.ico' border=0></a>";
		}else{
			$modfactr="&nbsp;";
		}
		if ($permisoremision==1 and $Quotations==4){
			$SQL="select * from debtortrans where idinvoice='$idfactura'";
			$resultRem = DB_query($SQL ,$db,'','');
			if (DB_num_rows($resultRem)>0){
				$liga=  "PDFRemisionMultipleTemplate.php?" . SID . "&PrintPDF=Yes&IdRemision=" . $idfactura ;
				//$liga=  "PDFRemisionTemplate.php?" . SID . "&PrintPDF=Yes&IdRemision=" . $idfactura ;//cambio
				$tieneremision="<a target='_blank' href='" . $rootpath . "/". $liga ."'>Imprimir<br>Rem/Tickets</a>";
			}else{
				$tieneremision="";
			}
			
		}
		if ($generapagares!='0' and strlen($transno)>0 ){
			if ($tipofacturacionxtag!=0){
				$liga=  "PDFPagarePage.php?" . SID . "&PrintPDF=Yes&type=10&TransNo=" . $transno ;
				$tienepagares="<a target='_blank' href='" . $rootpath . "/". $liga ."'>Imprimir<br>Pagares</a>";
			}else{
				$liga = GetUrlToPrint($tagref,70,$db);
				$tienepagares="<a target='_blank' href='" . $rootpath . "/".$liga . SID . "&TransNo=" . $transno ."&tagref=".$tagref."'>Imprimir<br>Pagares</a>";
							
	/*esto lo aumente*/	$liga2 = GetUrlToPrint2($tagref,70,$db);
	/*esto lo aumente*/	$tienepagares2="<a target='_blank' href='" . $rootpath . "/".$liga2 . SID . "&TransNo=" . $transno ."&tagref=".$tagref."'>Ficha<br>Deposito</a>";
		
			}
			//$tienepagares = "<a target='_blank' href='" . $rootpath . "/".$liga . SID . "&TransNo=" . $transno ."&tagref=".$tagref."'>Imprimir<br>Pagares</a>";
		}else{
		    $tienepagares = "&nbsp;";
/*esto lo aumente*/ $tienepagares2 = "&nbsp;";
		}



	       //aqui pone la imagen y el redireccionamoento para cancelar
	    $Cancelar="";  
		if ($Quotations!=4 and $Quotations!=5 and $Quotations!=6){
			if (Havepermission($_SESSION['UserID'],196, $db)==1){
				
				if ($Quotations==0 && Havepermission($_SESSION['UserID'],677, $db)==1){
					$Cancelar = $rootpath . '/SelectSalesOrderV2_0.php?orderno='. $myrow['orderno'];
					$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
				
				}
					else
						if ($Quotations!=0 and $Quotations!=2){
							$Cancelar = $rootpath . '/SelectSalesOrderV2_0.php?orderno='. $myrow['orderno'];
							$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
						}
				
			}
		    
		} elseif (($Quotations==4 or $Quotations==5 or $Quotations==6)and $fechahoy==$fechacorte){//aqui es la condicion de los pedidos facturados
			//si es un pedido facturado y la fecha de corte de caja es el mismo dia que en el que desean cancelar
			//te aparece la imagen con la que podras cancelar si no te aparece la casilla vacia
			//HAGO UNA CONSULTA PARA SABER SI LA FACTURA TIENE TIENE RECIBOS DE PAGO SIN CANCELAR.
		    
			if (strlen($typedeb)>0){
				/*$XSQL = "SELECT dm.id
				FROM debtortransmovs dm
				WHERE SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = " . $typedeb . "
				AND SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = " . $transno  . "
				UNION
				SELECT d.transno
				FROM debtortrans d
				WHERE d.type = 70 and d.reference = " . $transno . " and d.alloc <> 0";
				*/
			
				if ($typedeb==10){
				    /*$XSQL = "SELECT dm.id
					FROM debtortransmovs dm
					WHERE SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = " . $typedeb . "
					AND SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = " . $transno . "
					UNION
					SELECT d.transno
					FROM debtortrans d
					WHERE d.type = 70 and d.reference = " . $transno . " and d.alloc <> 0";*/
				      $XSQL="SELECT DISTINCT d2.id
				FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
				INNER JOIN debtortrans d2 ON d2.type=70 and d2.order_=debtortrans.transno
				LEFT JOIN custallocns C2 ON d2.id=C2.transid_allocto
				WHERE  debtortrans.id='".$idfactura."'
				AND C2.amt  IS NOT NULL
				union
				SELECT debtortrans.id
				FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
					INNER JOIN debtortrans d2 ON custallocns.transid_allocfrom=d2.id and d2.type<>70
				WHERE  debtortrans.id='$idfactura'";
				}else{
					 $XSQL="SELECT debtortrans.id
					FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
					WHERE  debtortrans.id='$idfactura'";
				}
			
				$tienerecibos = DB_query($XSQL ,$db,'','');
			
				if (intval(DB_num_rows($tienerecibos)) > intval(0)){
				    $Cancelar="Tiene<br>Recibos";
				}else{
					$myrowtienerecibos = DB_fetch_row($tienerecibos);
					if (Havepermission($_SESSION['UserID'],177, $db)==1){
						//$Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
					    
						if ($statusPeriodo==0){
						    $Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.trim($myrow['debtorno']).'&type='.$typedeb.'&tagref='.$tagref;
						    $Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
						}else{
						    $Cancelar='<b>'._('Contabilidad').'<br>'._('Cerrada').'</b>';
						}
					}else{
						$Cancelar="&nbsp;&nbsp;";
					}
				}
			}else{
				$Cancelar="Verifique<br>Folio uno ";
			}

		} else {
			//HAGO UNA CONSULTA PARA SABER SI LA FACTURA TIENE TIENE RECIBOS DE PAGO SIN CANCELAR.
			if (strlen($typedeb)>0){
				if ($typedeb==10){
					/*$XSQL = "SELECT dm.id
					    FROM debtortransmovs dm
					    WHERE SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = " . $typedeb . "
					    AND SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = " . $transno . "
					    UNION
					    SELECT d.transno
					    FROM debtortrans d
					    WHERE d.type = 70 and d.reference = " . $transno . " and d.alloc <> 0";
					*/
					$XSQL="SELECT DISTINCT d2.id
					       FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
					       INNER JOIN debtortrans d2 ON d2.type=70 and d2.order_=debtortrans.transno
					       LEFT JOIN custallocns C2 ON d2.id=C2.transid_allocto
					       WHERE  debtortrans.id='".$idfactura."'
					       AND C2.amt  IS NOT NULL";
				}else{
					/* $XSQL = "SELECT dm.id
					 FROM debtortransmovs dm
					 WHERE SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = " . $typedeb . "
					 AND SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = " . $transno ;
				       */
			  
					//echo $XSQL;
					$XSQL="SELECT debtortrans.id
						FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
						WHERE  debtortrans.id='$idfactura'";
				}
			
				$tienerecibos = DB_query($XSQL ,$db,'','');
			
				if (intval(DB_num_rows($tienerecibos)) > intval(0)){
					$Cancelar="Tiene<br>Recibos";
				}else{
					$myrowtienerecibos = DB_fetch_row($tienerecibos);
					if (Havepermission($_SESSION['UserID'],177, $db)==1){
					    //$Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
					    
					    if ($statusPeriodo==0){
					    $Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&type='.$typedeb.'&tagref='.$tagref;
					    $Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
					    }else{
						    $Cancelar='<b>'._('Contabilidad').'<br>'._('Cerrada').'</b>';
					    }
					}
				}
			}else{
				$Cancelar="Verifique<br>Folio dos ";
			}
		    
		}
		
		if (Havepermission($_SESSION['UserID'],203, $db)==1 and $Quotations==6 ){
			//$VentaPerdida = $rootpath . '/SelectSalesOrderV2_0.php?orderno='. $myrow['orderno'];
			$VentaPerdida= $rootpath . '/SelectSalesOrderV2_0.php?ventaperdida=yes&orderno='.
			$myrow['orderno'].
			'&tagrefen='.$tagref.
			'&iddocto='.$idfactura.
			'&serie='.$serie.
			'&folio='.$folio.
			'&debtorno='.$debtorno.
			'&tipo='.$typedoc.'&transno='.$transno
			. '&FromDia=' . $FromDia 
			. '&FromMes=' . $FromMes 
			. '&FromYear=' . $FromYear 
			. '&ToDia=' . $ToDia
			. '&ToMes=' . $ToMes
			. '&ToYear=' . $ToYear
			;
			$VentaPerdida="<a href=".$VentaPerdida."><img src='part_pics/Delete.png' border=0>".('Venta Perdida')."</a>";
		} else {
			$VentaPerdida ="&nbsp;";
		}
		$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
		//$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderDate = ConvertSQLDate($orddate);
		   

		$arrayFecha=explode("/",$FormatedOrderDate);
		$dia_pedido = $arrayFecha[0];
		$mes_pedido = $arrayFecha[1];
		$anio_pedido = $arrayFecha[2];
     
		$fecha_pedido = strtotime($dia_pedido."-". $mes_pedido . "-" .$anio_pedido." 23:59:59");		
     
		$tienepermiso="1";
		    
		if ($fecha_pedido<$fecha_actual){
		      $tienepermiso=Havepermission($_SESSION['UserID'],202,$db);
		}
		//echo $tienepermiso;
		if($myrow['ordervalue']==0){
			$sql="
			select
			(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2)))+ (salesorders.taxtotal +salesorders.totaltaxret) AS ordervalue
			
			from salesorders inner join salesorderdetails on salesorderdetails.orderno=salesorders.orderno
			where salesorders.orderno=".$orderno;
			 $Resultag= DB_query($sql,$db,'','');
			 if (DB_num_rows($Resultag)>0) {
			 	$myrowtags = DB_fetch_array($Resultag);
			 	$myrow['ordervalue']=trim($myrowtags[0]);
			 	
			}
			
		}
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);
		$montoTotal = $montoTotal + $myrow['ordervalue'];
		
		if ($myrow['printedpackingslip']==0) {
			$PrintText = _('Levantamiento.');
		} else {
			$PrintText = _('Reimprimir');
		}
			
		if ($tipofacturacionxtag==2 or $tipofacturacionxtag==1 or $tipofacturacionxtag==4 ) {
			$ReenvioXSA= $rootpath . '/SelectSalesOrderV2_0.php?orderno='.
			$myrow['orderno'].
			'&tagrefen='.$tagref.
			'&iddocto='.$idfactura.
			'&serie='.$serie.
			'&folio='.$folio.
			'&debtorno='.$debtorno.
			'&tipo='.$typedoc.'&transno='.$transno
			. '&FromDia=' . $FromDia 
			. '&FromMes=' . $FromMes 
			. '&FromYear=' . $FromYear 
			. '&ToDia=' . $ToDia
			. '&ToMes=' . $ToMes
			. '&ToYear=' . $ToYear
			;
			$ReenviarXSA="&nbsp;&nbsp;&nbsp;<a href=".$ReenvioXSA."><img src='part_pics/Mail-Forward.png' alt='Reenviar SAT' border=0></a>";
			$RecapturaAddenda= $rootpath . '/Z_RecapturaAddenda.php?'.'&iddocto='.$idfactura;
			$RecapturaAddenda="&nbsp;&nbsp;&nbsp;<a href=".$RecapturaAddenda."><img src='images/Edit.ico' alt='Recaptura datos addenda' border=0></a>";
			
			
		} else {
			$ReenviarXSA="";
		}
		
		if (strlen($folio)==0 and $Quotations==4){
			$PrintDispatchNote='';
			$PrintText='';
			$ReenviarXSA='';
			$Cancelar='';
		}
		if($Quotations==0){
			//echo "entra";
			if ($permisocerrado==0)	{
				$linkordenes="%s%s";
				//echo "entra aqui";
			}else{
				$linkordenes="<a href='%s'>%s</a>";	
			}
		}else{
			$linkordenes="<a href='%s'>%s</a>";	
		}
		if ((Havepermission($_SESSION['UserID'],277, $db)==1)){
			$link_modVendedores = "<a target='_blank' href='ChangeSalesman.php?orderno=". $myrow['orderno'] ."&type=".$typedeb."&folio=". $folio_elec ."'
			title='Modificar Vendedor'><img src='".$rootpath."/css/".$theme."/images/user.png' TITLE='Modificar Vendedores' ALT='" . _('Modificar Vendedores') . "'></a>";
		     }
		//echo $PrintDispatchNote;
		
		$email_link ="<a target='_blank' href='SendEmail.php?tagref=$tagref&transno=".$myrow['orderno']."&debtorno=".$myrow['orderno']."'><img src='part_pics/Mail-Forward.png' border=0></a>";
		/*
		$link="";
		if ($liga2!=""){
			if ($Quotations=='0'){
				//echo "<pre>entrooooo";
				$liga2 = "PDFPreInvoice.php?OrderNo=".$myrow['orderno'];
				$link = "<br><br><a target='_blank' href='$liga2'>Pre-Factura</a>";
			}
			 else
				$link = "<br><br><a target='_blank' href='$liga2'>Simple</a>";
		}*/
		$link="";
		if ($liga2!=""){
			if ($_POST['Quotations']=='0'){
			// echo "<pre>entrooooo";
			//$liga2 = "PDFPreInvoice.php?OrderNo=".$myrow['orderno'];
			$link = "<br><br><a target='_blank' href='$liga'>Pedido</a>";
			}
			else
			$link = "<br><br><a target='_blank' href='$liga2'>Cotizacion</a>";
		}
		if($myrow['idprospect']>0){
			$oportunidad ="<a target='_blank' href='prospect_ControlV1_0.php?u_movimiento=".$myrow['idprospect']."'><img src='images/user_24x32.gif' border=0></a>";
		}else{
			$oportunidad=' ';
		}
		if ($myrow['estatus'] == 4 or $myrow['estatus'] == 6){
			
		}else{
			
		}
		$txtventa=_('Facturar');
		if($permisocerrado==0){
			$Confirm_Invoice=' ';
			$txtventa=' ';
		}
		
		if ($Quotations=='0'){
			//echo 'folioelec'.$Confirm_Invoice;
			//echo 'modify'.$ModifyPage;
			printf("<td>".$linkordenes."</td>
			   <td><a href='%s'>" .  $txtventa . "</a></td>
				<td style='text-align:center;'>%s</td>
				<td><a target='_blank' href='%s'>" . $PrintText . "</a>%s</td>
				<td style='text-align:center;'>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td style='text-align:right;'>%s</td>
				<td nowrap style='text-align:right;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'><font size=1>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				</tr>",
				/*esto aumente la ultima linea del printf*/
				$ModifyPage,
				$myrow['orderno'],
				$Confirm_Invoice,
				$folio_elec,
				$PrintDispatchNote,
				$link,
				$termpago,
				$myrow['debtorno'].'-'.$myrow['name'],
				$myrow['nopedido'],
				$FormatedOrderDate,
				$myrow['customerref'],
				$myrow['UserRegister'],
				$myrow['salesmanname'],
				$myrow['currcode'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$VentaPerdida,
				$modfactr2,
				$myrow['tagdescription'],
				//$link_modTrabajadores,
				//$email_link,
				$oportunidad
				);
		} elseif($Quotations==4 or $Quotations==5) {
			//$ligar1="PDFRemisionTemplate.php?";
			//$ligar=$rootpath . '/' . $ligar1  . '' . SID . 'Orderno='.$myrow['orderno'].'&TransNo='.$transno.'&Type='.$typedoc;
			//$ligar=$rootpath . '/' . $ligar1  . '&' . SID . 'Orderno='.$myrow['orderno'].'&TransNo='.$transno.'&Type='.$typedoc;
			//echo 'entra';
			
			//----------------GENERACION DEL XML-----------------
			$XML="";
			
			$tipofac=$myrow['type'];
			$foliox=$myrow['folio'];
			$separa = explode('|',$foliox);
			if ($tipofac=='12'){
				$serie = $separa[1];
				$folio = $separa[0];
			}else{
				$serie = $separa[0];
				$folio = $separa[1];
			}
			
			$verfact=0;
			if ($tipofac=='10' or $tipofac=='110' or $tipofac=='11' or $tipofac=='13' or $tipofac=='21' or $tipofac == '410' or $tipofac == '109' or $tipofac == '66')
			{
				$verfact=1;
			}		
			
			if (Havepermission($_SESSION['UserID'],331, $db)==1  and $verfact==1)
			{
				if ($fechaemision<$fechacambio){
					$pagina=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=" . $serie . "&
					folio=" . $folio . "&tipo=XML&rfc=" . $rfc . "&key=" . $keyfact ;
					$XML= "<a href='".$pagina."'>";
					//$XML.= "XML</a>";
				}else{
					if ($tipofac=='12'){
						$folder="Recibo";
					}elseif($tipofac=='10' or $tipofac=='110' or $tipofac=='109' or $tipofac=='66' ){
						$folder="Facturas";
					}elseif($tipofac=='13'){
						$folder="NCreditoDirect";
					}elseif($tipofac=='21'){
						$folder="NCargo";
					}else{
						$folder="NCredito";
					}
					$direccion="/erpdistribucion/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/";
					$pagina=$direccion.'XML/'.$folder.'/'.$serie.$folio.'.xml';
					$XML= "<a target=_blank href='".$pagina."'>";
					$XML.= "XML </a>";
				}
			}
			//---------------------------------------------------
			$mes = substr($fechaemision, 5, 2);
			$mesactual = date("m");
			if (Havepermission($_SESSION['UserID'],880, $db)==1) {
				if ($mes == $mesactual){
					$Carta = $PrintText .(' Carta'). "</a>"."<!--<br><a target='_blank' href='%s'>" . $PrintText.(' Ticket') . "</a>--> ".$ReenviarXSA." ".$RecapturaAddenda ." "." $XML</td>";
				}else {
					$Carta = $PrintText .(' Carta'). "</a>"."<!--<br><a target='_blank' href='%s'>" . $PrintText.(' Ticket') . "</a>--> ".$ReenviarXSA." ".$RecapturaAddenda ." "."</td>";
				}
			}else{
				if ($mes == $mesactual){
					$Carta = $PrintText .(' Carta'). "</a>"."<!--<br><a target='_blank' href='%s'>" . $PrintText.(' Ticket') . "</a>--> ".$ReenviarXSA." $XML</td>";
				}else {
					$Carta = $PrintText .(' Carta'). "</a>"."<!--<br><a target='_blank' href='%s'>" . $PrintText.(' Ticket') . "</a>--> ".$ReenviarXSA."</td>";
				}
			}
			
			if($myrow['noremisionf'] <> ""){
				$remision = $myrow['noremisionf'];
				$color = "bgcolor='yellow'";
				
			}else{
				$remision = $myrow['nopedido'];
				$color = "";
			}
			printf("<td>%s</td>
			        <td style='text-align:center;'>%s</td>
				
				<td nowrap><a target='_blank' href='%s'>" . $Carta."
				<td style='text-align:center;'>%s</td>
				<td><font size=1>%s</td>
				<td ".$color."><font size=1>%s</td>
				<td ><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td nowrap style='text-align:right;'><font size=1>%s</td>
				<td style='text-align:center;'><font size=1>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</a></td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				</tr>",
				/*esto aumente la ultima linea del printf*/
				$myrow['orderno'],
				$folio_elec,
				$PrintDispatchNote,
				$PrintDispatchNote.'&printdoc=yes',
				$termpago ,
				$myrow['shippername'],
				$myrow['debtorno'].'-'.$myrow['name'],
				$remision,
				$myrow['customerref'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				$myrow['salesmanname'],
				$myrow['currcode'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$link_modTrabajadores,
				$modfactr,
				//$tienepagares,
/*esto lo aumente*/		$tienepagares2,
				
				$myrow['tagdescription'],
				$link_modVendedores,
				$tieneremision,
				$oportunidad
				);
		}elseif($Quotations==6) {
			if($myrow['noremisionf'] <> ""){
				exit;
				$remision = $myrow['noremisionf'];
				$color = "bgcolor='yellow'";
			}else{
				$remision = $myrow['nopedido'];
				$color = "";
			}
			printf("<td><a href='%s'>%s</a></td>
			        <td style='text-align:center;'>%s</td>
				<td nowrap><a target='_blank' href='%s'>" . $PrintText .(' Carta'). "</a>"."<!--<br><a target='_blank' href='%s'>" . $PrintText.(' Ticket') . "</a> -->".$ReenviarXSA."</td>
				<td style='text-align:center;'>%s</td>
				<td><font size=1>%s</td>
				<td".$color."><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td nowrap style='text-align:right;'><font size=1>%s</td>
				<td style='text-align:center;'><font size=1>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</a></td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				</tr>",
				/*esto aumente la ultima linea del printf*/
				$ModifyPage,
				$myrow['orderno'],
				$folio_elec,
				$PrintDispatchNote,
				$PrintDispatchNote.'&printdoc=yes',
				$termpago,
				$myrow['shippername'],
				$myrow['debtorno'].'-'.$myrow['name'],
				$remision,
				$myrow['customerref'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				$myrow['salesmanname'],
				$myrow['currcode'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$VentaPerdida,
				$link_modTrabajadores,
				$modfactr,
/*esto lo aumente*/		$tienepagares2,
				
				$myrow['tagdescription'],
				$link_modVendedores,
				$tieneremision,
				$oportunidad
				
				);
		
		
		} else { /*must be quotes only */
			  
			  
			  
		//echo "entra";
		
			if ($tienepermiso=="1") {
				
				printf("<td style='text-align:center;'><a href='%s'>%s</a></td>
					<td style='text-align:center;'>%s</td>
					<td><a target='_blank' href='%s'>" . $PrintText . "</a>%s</td>
					<td style='text-align:center;'>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td nowrap style='text-align:right;'><font size=1>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</a></td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					</tr>",
					/*esto aumente la ultima linea del printf*/
					$ModifyPage,
					$myrow['orderno'],
					$folio_elec,
					$PrintQuotation,
					$link,
					$termpago,
					$myrow['shippername'],
					$myrow['debtorno'].'-'.$myrow['name'],
					$myrow['nopedido'],
					$myrow['customerref'],
					$FormatedOrderDate,
					$myrow['UserRegister'],
					$myrow['salesmanname'],
					$myrow['currcode'],
					'$ '.$FormatedOrderValue,
					$Cancelar,
					$VentaPerdida,
					$modfactr,
					$tienepagares,
					
					$myrow['tagdescription'],
					$link_modTrabajadores,
					$link_modVendedores,
					$oportunidad
					);
			} else {
				
				printf("<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td><a href='%s'>" . $PrintText . "</a></td>
					<td style='text-align:center;'>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td><font size=1>%s</td>
					<td nowrap style='text-align:right;'><font size=1>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</a></td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>
					<td style='text-align:center;'>%s</td>		
					</tr>",
					/*esto aumente la ultima linea del printf*/
					$myrow['orderno'],
					$folio_elec,
					$PrintQuotation,
					$termpago,
					$myrow['shippername'],
					$myrow['debtorno'].'-'.$myrow['name'],
					$myrow['nopedido'],
					$myrow['customerref'],
					$FormatedOrderDate,
					$myrow['UserRegister'],
					$myrow['salesmanname'],
					$myrow['currcode'],
					'$ '.$FormatedOrderValue,
					$Cancelar,
					$VentaPerdida,
					$modfactr,
					//$tienepagares,
	/*esto lo aumente*/		$tienepagares2,
					$myrow['tagdescription'],
					//$link_modTrabajadores,
					$link_modVendedores,
					$remision,
					$oportunidad
					);			 
			}
		}
		
		$j++;
		If ($j == 12){
			$j=1;
		#	echo $tableheader;
		}
	//end of page full new headings if
	}
     
     printf("<td></td>
			        <td style='text-align:center;'>TOTAL</td>
				<td></td>
				<td style='text-align:center;'></td>
				<td><font size=1></td>
				<td><font size=1></td>
				<td><font size=1></td>
				<td><font size=1></td>
     			<td><font size=1></td>
				<td nowrap style='text-align:right;'><font size=1></td>
				<td style='text-align:center;'><font size=1>%s</td>
				<td style='text-align:center;'></td>
				<td style='text-align:center;'></a></td>
				<td style='text-align:center;'></td>
				<td style='text-align:center;'> </td>
     			<td style='text-align:center;'> </td>
				<td style='text-align:center;'>%s</td>
				</tr>",
				$montoTotal
				
				);
     
	//end of while loop
     echo '<tr><td colspan=15>&nbsp;</td></tr>';
	echo '<tr><td colspan=15 style="text-align:center">
				<input title="Crea cotoizacion nueva por cada seleccion" type="submit" name="btnDuplicar" value="Duplicar Seleccion">&nbsp;&nbsp;&nbsp;&nbsp;
				<input title="Unifica las cotizaciones seleccionads en una nueva" type="submit" name="btnUnificar" value="Unificar Seleccion">
			</td></tr>';
	echo '</table>';
}

?>
</form>

<?php } //end StockID already selected

include('includes/footer.inc');
?>