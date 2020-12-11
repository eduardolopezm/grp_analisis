<?php

/* $Revision: 4.0 $ 
   ARCHIVO MODIFICADO POR: CARMEN GARCIA
FECHA DE MODIFICACION: 17-DIC-2009 
 CAMBIOS:
   1. CAMBIO DE por tipo de cotizacion y unidades de negocio por usuario
FECHA DE MODIFICACION: 30-DIC-2009 
   4. Se aumento la variable type que manda el valor por medio de GET a ConfirmCancel_Invoice
 FIN DE CAMBIOS
 FECHA DE MODIFICACION: 03-FEB-2010
 1.- se modifico la liga para el pagare
 FIN DE CAMBIOS
 
FECHA DE MODIFICACION: 17-MARZO-2010
   1. Se aumento la liga para poder imprimir la ficha de deposito 	
FIN DE CAMBIOS 
*/
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Busqueda de Pedidos de Venta');
unset($_SESSION['ExistingOrder']);
include('includes/header.inc');
$funcion=4;
include('includes/SecurityFunctions.inc');
include ('Numbers/Words.php');
include ('includes/XSAInvoicing.inc');
// para el llamado al webservice
include_once('lib/nusoap.php');




if (Havepermission($_SESSION['UserID'],171, $db)==1) {
    echo '<p><a href="' . $rootpath . '/SelectOrderItems.php?' . SID . '&NewOrder=Yes"><font size=2><b>' . _('Nuevo Pedido de Venta') . '</a>';
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


if (isset($orden) and !isset($folio)){
     $SQL = "UPDATE salesorders
     SET quotation =3
     WHERE orderno= " .  $orden;
     $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
     $DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
     $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
     prnMsg(_('El Numero de Pedido') . ' ' . $orden . ' ' . _('se ha cancelado'),'success');
}elseif(isset($orden) and isset($folio)){
    // enviar a XSA
		$SQL=" SELECT l.taxid,l.address5,t.tagname,l.legalname
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='".$tagrefen."'";
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$rfc=trim($myrowtags['taxid']);
			$keyfact=trim($myrowtags['address5']);
			$nombre=trim($myrowtags['tagname']);
			//$nombre='SERVILLANTAS DE QUERETARO S.A. DE C.V.';
		}
		$factelectronica= XSAInvoicing($transno,
					       $orden,
					       $debtorno,
					       $tipo ,
					       $tagrefen ,
					       $serie,
					       $folio, $db);
		
		// Envia  los datos al archivooooo
		$myfile="Fact".$tipo."_".$tagrefen.'_'.$transno.".txt"; //Ponele el nombre que quieras al archivo
		$factelectronica=utf8_encode($factelectronica);
		$empresa=trim($keyfact.'-'.$rfc);
		$nombre=trim($nombre);
		$tipo='Factura';
		$myfile=trim($myfile);
		$factelectronica=trim($factelectronica);
		if ($_SESSION['EnvioXSA']==1){
			$param=array('in0'=>$empresa, 'in1'=>$nombre,'in2'=>$tipo,'in3'=>$myfile,'in4'=>$factelectronica);
			try{	
				$client = new SoapClient($_SESSION['XSA']."xsamanager/services/FileReceiverService?wsdl");
				$codigo=$client->guardarDocumento($param);
			}catch (SoapFault $exception) {
				$errorMessage = $exception->getMessage();
			}
		}
		
		echo '<meta http-equiv="Refresh" content="2; url=' . $rootpath .
		'/SelectSalesOrder.php?OrderNumber=' . $orden . '&UnidNeg=' . $tagrefen 
		. '&FromDia=' . $FromDia 
		. '&FromMes=' . $FromMes 
		. '&FromYear=' . $FromYear 
		. '&ToDia=' . $ToDia
		. '&ToMes=' . $ToMes
		. '&ToYear=' . $ToYear
		. '&Quotations=4' 
		.'">';
		
}
//aqui empieza la seleccion de periodo desde hasta
     echo '<table>
	       <tr>
		    <td>' . _('Desde:') . '</td>
		    <td><select Name="FromDia">';
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
	            echo'</td>'; 
		    echo '<td><select Name="FromMes">';
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
				      
		      echo '</td>
		    <td>
			 &nbsp;
	            </td>
	            <td>' . _('Hasta:') . '</td>';
		    echo'<td><select Name="ToDia">';
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
		    echo '</td>';
		    echo'<td>';
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
	  </table>';

If (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!='') {
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
		echo _('For customer') . ': ' . $_REQUEST['SelectedCustomer'] . ' ' . _('and') . ' ';
		echo "<input type=hidden name='SelectedCustomer' value=" . $_REQUEST['SelectedCustomer'] . '>';
	}
	If (isset($_REQUEST['SelectedStockItem'])) {
		 echo _('for the part') . ': ' . $_REQUEST['SelectedStockItem'] . ' ' . _('and') . " <input type=hidden name='SelectedStockItem' value='" . $_REQUEST['SelectedStockItem'] . "'>";
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
		echo '<br>'._('Pedido de Venta No.') . ": <input type=text name='OrderNumber' maxlength=8 size=9>&nbsp; ";
		
		
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
		
		
		    echo _('Unidad de Negocio') . ":<select name='UnidNeg'> ";
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
		    echo '</select> &nbsp;&nbsp;';
		    
		if (isset($_GET['Quotations'])){
			$_POST['Quotations']=$_GET['Quotations'];
		}elseif(isset($_POST['Quotations'])){
		    $_POST['Quotations']=$_POST['Quotations'];
		}else{
		    $_POST['Quotations']='1';
		}
		
		echo _('Estatus') . ' : <select name="Quotations">';
		
		
		if ($_POST['Quotations']=='1'){
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
			
		} elseif ($_POST['Quotations']=='0') {
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
		} elseif ($_POST['Quotations']=='4') {
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
	       }
		echo '</select> &nbsp;&nbsp;';
		echo "<input type=submit name='SearchOrders' VALUE='" . _('Buscar') . "'>";

	echo "
      </table>
      <hr>";

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
		    } elseif ($Quotations==3) {
			 echo"<td><font size=3>Pedidos de Venta Cancelados</td>";
		    } elseif ($Quotations==2) {
			 echo"<td><font size=3>Pedidos de Venta Abiertos</td>";
		    } elseif ($Quotations==1) {
			 echo"<td><font size=3>Cotizaciones</td>";
		    } elseif ($Quotations==0) {
			 echo"<td><font size=3>Pedidos de Venta Cerrados</td>";
		    }
	       echo"</tr>";
	echo"</table>";
	
	if(!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}
	if (isset($_REQUEST['OrderNumber']) && $_REQUEST['OrderNumber'] !='') {
	    
			
			$SQL = "SELECT salesorders.orderno,
					debtorsmaster.name,
					salesorders.UserRegister,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					paymentterms.type,
					salesorders.debtorno,
					(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal AS ordervalue
					,tagdescription
				FROM salesorders,
					salesorderdetails,
					debtorsmaster,
					custbranch, paymentterms, sec_unegsxuser, tags
				WHERE salesorders.orderno = salesorderdetails.orderno
				AND salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno";
					if ($Quotations!=4){
					$SQL=$SQL." AND salesorderdetails.completed=0";
					$SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					$SQL=$SQL." AND salesorders.orderno=". $_REQUEST['OrderNumber'] ."
				AND salesorders.quotation =" .$Quotations . "
				AND paymentterms.termsindicator=salesorders.paytermsindicator
				
			        AND salesorders.tagref=tags.tagref	
			        AND sec_unegsxuser.tagref=salesorders.tagref
				
			        AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
				
				GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip
				ORDER BY salesorders.orderno, tagdescription ";
				
     } else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */
		
	       if (isset($_REQUEST['SelectedCustomer'])) {


			//echo "consulta";
			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						salesorders.UserRegister,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
					        salesorders.printedpackingslip,
						paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,
						(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal AS ordervalue
						,tagdescription
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch, paymentterms, sec_unegsxuser, tags
					WHERE salesorders.orderno = salesorderdetails.orderno
	
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					
					AND sec_unegsxuser.tagref=tags.tagref	
					AND salesorders.tagref=tags.tagref		
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					
					AND salesorders.branchcode = custbranch.branchcode";
					if ($Quotations!=4){
					    $SQL=$SQL." AND salesorderdetails.completed=0";
					    $SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					
					
					$SQL=$SQL." AND paymentterms.termsindicator=salesorders.paytermsindicator
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] ."'
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] ."'";
				if (strlen($_POST['StockLocation'])>0){	
				   $SQL= $SQL. " AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
				}
				
				if (strlen($_POST['UnidNeg'])>0){	
				   $SQL= $SQL. " AND salesorders.tagref = '". $_POST['UnidNeg'] . "'";
				}

				
				$SQL=$SQL." ORDER BY salesorders.orderno, tagdescription ";		
			} else {
				//echo "consulta";
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						salesorders.UserRegister,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					        salesorders.printedpackingslip,
						salesorders.deliverydate,
						paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,
						(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal AS ordervalue
						,tagdescription
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch, paymentterms, sec_unegsxuser, tags
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					
					AND sec_unegsxuser.tagref=tags.tagref	
					AND salesorders.tagref=tags.tagref
					
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorders.quotation =" .$Quotations ;
					if ($Quotations!=4){
					    $SQL=$SQL." AND salesorderdetails.completed=0";
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
						
				   $SQL= $SQL."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						salesorders.debtorno,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate
					ORDER BY salesorders.orderno, tagdescription ";

			 }
	       } else { //no customer selected
		
			if (isset($_REQUEST['SelectedStockItem'])) {
		
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						salesorders.UserRegister,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  	salesorders.printedpackingslip,
						salesorders.deliverydate,
						paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,
						(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal AS ordervalue
						,tagdescription
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch, paymentterms, sec_unegsxuser, tags
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					
					AND tags.tagref=salesorders.tagref	
					AND sec_unegsxuser.tagref=tags.tagref	
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'

					
					AND salesorders.branchcode = custbranch.branchcode";
					if ($Quotations!=4){
					    $SQL=$SQL." AND salesorderdetails.completed=0";
					    $SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					$SQL=$SQL." AND paymentterms.termsindicator=salesorders.paytermsindicator
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] . "'";
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
					}

					if (strlen($_POST['UnidNeg'])>0){	
					   $SQL= $SQL. " AND salesorders.tagref = '". $_POST['UnidNeg'] . "'";
					}					
					
					$SQL=$SQL."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno, tagdescription ";
			 } else {
			    	//echo "consulta";
				$SQL = "SELECT  salesorders.orderno,
						debtorsmaster.name,
						salesorders.UserRegister,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
					        salesorders.printedpackingslip,
					        paymentterms.type,
						paymentterms.numberofpayments,
						paymentterms.generatecreditnote,
						salesorders.debtorno,
						(SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal AS ordervalue
						,tagdescription
						,tags.tagref
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch, paymentterms, sec_unegsxuser, tags
					WHERE salesorders.orderno = salesorderdetails.orderno

					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					
					AND sec_unegsxuser.tagref=tags.tagref	
					AND tags.tagref=salesorders.tagref
					
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					
					AND salesorders.branchcode = custbranch.branchcode";
					if ($Quotations!=4){
            					$SQL=$SQL." AND salesorderdetails.completed=0";
						$SQL=$SQL." AND salesorders.orddate>= '".$fechaini."' and salesorders.orddate<='".$fechafin."'";
					}else{
					    $SQL=$SQL." AND salesorders.confirmeddate>= '".$fechaini."' and salesorders.confirmeddate<='".$fechafin."'";
					}
					$SQL=$SQL." AND paymentterms.termsindicator=salesorders.paytermsindicator
					AND salesorders.quotation =" .$Quotations ;
					
					if (strlen($_POST['UnidNeg'])>0){	
					   $SQL= $SQL. " AND salesorders.tagref = '". $_POST['UnidNeg'] . "'";
					}
					
					
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
					}
					$SQL=$SQL."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno, tagdescription ";
			 }

	       } //end selected customer
     } //end not order number selected
	$ErrMsg = _('No hay pedidos de venta o cotizaciones pendientes');
	
	//echo $SQL.'<BR>';
	
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg,'');
        
	/*show a table of the orders returned by the SQL */
	echo '<table cellpadding=2 colspan=7 WIDTH=100%>';
	
        $fecha_actual = strtotime(date("d-m-Y H:i:00",time()));

	  if (isset($_POST['Quotations']) and $_POST['Quotations']=='0'){
		$tableheader = "<tr>
			        <th nowrap>" . _('Modificar') . "</th>
				<th nowrap>" . _('Factura') . "</th>
				<th nowrap>" . _('Folio') . "</th>
				<th nowrap>" . _('Disp. Note') . "</th>
			        <th nowrap>" . _('Termino<br>Pago') . "</th>				
				<th nowrap>" . _('Cliente') . "</th>
				<th nowrap>" . _('Pedido') . " #</th>
				<th nowrap>" . _('Fecha') . "</th>
				<th nowrap>" . _('Usuario') . "</th>
				<th nowrap>" . _('Total') . "</th>";
	       if ($Quotations!=4){		
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){
			     $tableheader=$tableheader."<th>" . _('Cancelar') . "</th>";
		    }
		    if (Havepermission($_SESSION['UserID'],203, $db)==1)
		    {
			 $tableheader=$tableheader."<th>" . _('Venta<br>Perdida') . "</th>";
		    }
	       } elseif ($Quotations==4) {
		    if (Havepermission($_SESSION['UserID'],177, $db)==1){
		    	$tableheader=$tableheader."<th>" . _('Cancelar Factura') . "</th>";
		    }
		    if (Havepermission($_SESSION['UserID'],302, $db)==1)
		    {
			 $tableheader=$tableheader."<th>" . _('Mod<br>Trab') . "</th>";
		    }
	       }
	       $tableheader=$tableheader."<th nowrap>Pagares</th><th>" . _('U. Negocio') . "</th></tr>";
	  } else {
		$tableheader = "<tr>
		                <th nowrap>" . _('Modificar') . "</th>
				<th nowrap>" . _('Folio') . "</th>
			        <th nowrap>" . _('Imprimir') . "</th>
				<th nowrap>" . _('Termino<br>Pago') . "</th>
				<th nowrap>" . _('Cliente') . "</th>
				<th nowrap>" . _('Referencia') . " #</th>
				<th nowrap>" . _('Fecha') . "</th>
				<th nowrap>" . _('Usuario') . "</th>
				<th nowrap>" . _('Total') . "</th>";
			      if ($Quotations!=4){
				   if (Havepermission($_SESSION['UserID'],196, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Cancelar') . "</th>";
				   }
				   if (Havepermission($_SESSION['UserID'],203, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Venta<br>Perdida') . "</th>";
				   }
			      } else{
				   if (Havepermission($_SESSION['UserID'],177, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Cancelar Factura') . "</th>";
				   }
				   if (Havepermission($_SESSION['UserID'],302, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Mod<br>Trab') . "</th>";
				   }

			      }
/*esto le aumente un th */  $tableheader=$tableheader."<th nowrap>Pagares</th><th nowrap></th><th>" . _('U. Negocio') . "</th></tr>";
	  }
	
     echo $tableheader;
     $fechanio=date('Y');
     $fechames=date('m');
     $fechadia=date('d');
     $fechahoy=$fechanio.'-'.$fechames.'-'.$fechadia;
     $j = 1;
     $k=0; //row colour counter

     while ($myrow=DB_fetch_array($SalesOrdersResult)) {
	$typedoc='';
	$numorden='';
	$folio_elec = '&nbsp;';
	$foliox='';
	$PrintDispatchNote='&nbsp;';
	  $orddate=$myrow['orddate'];
	  $orderno=$myrow['orderno'];
	  $escredito =$myrow['numberofpayments'];
	  $generapagares =$myrow['generatecreditnote'];
	  $tagref =$myrow['tagref'];
	  $ReenviarXSA='';
	  $serie='';
	  $folio ='';
	  
	  $sqlorden = "select type, transno, folio,origtrandate
	  	       from debtortrans where invtext not like '%CANCELADA%' and order_=".$orderno;
	  //echo $sqlorden;

	  //echo $sqlorden;
	  
	  /*$Resultorden = DB_query($sqlorden,$db,$ErrMsg,'');
	  if (DB_num_rows($Resultorden)>0)
	  {
	       $myrowOrden=DB_fetch_row($Resultorden);
	       if ($myrowOrden[0]<>'0')
	       {
		    $typedoc=$myrowOrden[0];
		    $numorden = $myrowOrden[1];
		    $folio_elec = $myrowOrden[2];
		    $orddate =$myrowOrden[3];
	       }
	       else
	       {
		    $typedoc='';
		    $numorden='';
		    $folio_elec = '&nbsp;';
	       }
	  }     
	  else
	  {
	       $typedoc='';
	       $numorden='';
	       $folio_elec = '&nbsp;';
	  }*/

	       //aqui se recupera la fecha con la que sera comparada la fecha actual para poder dar permiso o no de cancelar la factura
	       if ($Quotations==4){
		    $sql="select type,transno,folio,origtrandate,tagref
			 from debtortrans
			 where type in (110,10) and order_=".$orderno;
		    $Fechacomparacion = DB_query($sql,$db,'','');
	
		    while ($myrowcomparacion=DB_fetch_array($Fechacomparacion)) {
			if ($myrowcomparacion[0]<>'0')
			{
			     $typedoc=$myrowcomparacion[0];
			     $numorden = $myrowcomparacion[1];
			     $folio_elec = $myrowcomparacion[2];
			     $orddate =$myrowcomparacion[3];
			}
			else
			{
			     $typedoc='';
			     $numorden='';
			     $folio_elec = '&nbsp;';
			}
			 $transno=$myrowcomparacion['transno'];
			 $typedeb=$myrowcomparacion['type'];
			 $tagref=$myrowcomparacion['tagref'];
			 $foliox=$myrowcomparacion['folio'];
			 $orddate=$myrowcomparacion['origtrandate'];
			 $separa = explode('|',$foliox);
			 $serie = $separa[0];
			 $folio = $separa[1];
			 // trae el key y rfc de la empresa
			 $SQLkey=" SELECT l.taxid,l.address5,t.tagdescription
			       FROM legalbusinessunit l, tags t
			       WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
			       
			 $Resultag= DB_query($SQLkey,$db,'','');
			 if (DB_num_rows($Resultag)==1) {
			 	$myrowtags = DB_fetch_array($Resultag);
			 	$rfc=trim($myrowtags['taxid']);
			 	$keyfact=$myrowtags['address5'];
			      }
			 
			 
			 $sql="select date(fechacorte) as fechacorte
			      from usrcortecaja
			      where u_status=0 and tag=".$tagref;
			      $Fechacorte = DB_query($sql,$db,'','');
			 while ($myrowcorte=DB_fetch_array($Fechacorte)) {
			      $fechacorte=$myrowcorte['fechacorte'];
			 }
		    }
		    
	       }else{
		    
		   $typedoc='';
		   $numorden='';
		   $folio_elec = '&nbsp;';
		}
	       if ($k==1){
		    echo '<tr class="EvenTableRows">';
		    $k=0;
	       } else {
		    echo '<tr class="OddTableRows">';
		    $k++;
	       }
             
	     
	       if ($Quotations!=4){
		    $ModifyPage = $rootpath . "/SelectOrderItems.php?" . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
	       } else {
		    $ModifyPage = $myrow['orderno'];
	       }
	       $Confirm_Invoice = $rootpath . '/SelectOrderItems.php?' . SID . '&ModifyOrderNumber=' .$myrow['orderno'];
	       $liga = "";
	        //aqui es para la impresion de la factura
	        if ($_SESSION['PackNoteFormat']==1 and $Quotations!=4){ //Laser printed A4 default 
		    $PrintDispatchNote = $rootpath . '/PDFSalesOrderQuotePageHeader.php?' . SID . 'TransNo=' . $myrow['orderno'];
	        } elseif($Quotations==4) { //pre-printed stationery default
			if ($_SESSION['EnvioXSA']==1){
				$PrintDispatchNote=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
			} else {
			    $liga = GetUrlToPrint($tagref,10,$db);
			    $PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='.$myrow['orderno'].'&Tagref='.$tagref;
			}	
		}
		$PrintQuotation = $rootpath . '/PDFSalesOrderQuotePageHeader.php?' . SID . 'TransNo=' . $myrow['orderno'].'&Tagref='.$tagref;
		
		if ($escredito<>'0')
		{
		    $termpago ='CR';
		}
		else
		{
		    $termpago ='CO';
		}
		
		
	       if (Havepermission($_SESSION['UserID'],302, $db)==1 and $typedoc<>'' and $Quotations==4){
		    
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
		    } else {
			$link_modTrabajadores ="&nbsp;";
		    }
	       } else {
		    $link_modTrabajadores ="&nbsp;";
	       }

		if ($generapagares!='0' and strlen($transno)>0 )
		{
		    if ($_SESSION['EnvioXSA']==1){
			$liga=  "PDFPagarePage.php?" . SID . "&PrintPDF=Yes&TransNo=" . $transno ;
			$tienepagares="<a target='_blank' href='" . $rootpath . "/". $liga ."'>Imprimir<br>Pagares</a>";
		    }else{
			$liga = GetUrlToPrint($tagref,70,$db);
			$tienepagares="<a target='_blank' href='" . $rootpath . "/".$liga . SID . "&TransNo=" . $transno ."&tagref=".$tagref."'>Imprimir<br>Pagares</a>";
			
/*esto lo aumente*/	$liga2 = GetUrlToPrint2($tagref,70,$db);
/*esto lo aumente*/	$tienepagares2="<a target='_blank' href='" . $rootpath . "/".$liga2 . SID . "&TransNo=" . $transno ."&tagref=".$tagref."'>Ficha<br>Deposito</a>";
		
		    }
		    
		    //$tienepagares = "<a target='_blank' href='" . $rootpath . "/".$liga . SID . "&TransNo=" . $transno ."&tagref=".$tagref."'>Imprimir<br>Pagares</a>";
		}
		else
		{
		    $tienepagares = "&nbsp;";
/*esto lo aumente*/ $tienepagares2 = "&nbsp;";
		}



	       //aqui pone la imagen y el redireccionamoento para cancelar
	       
	       if ($Quotations!=4){
		    if (Havepermission($_SESSION['UserID'],196, $db)==1){
			 $Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
			 $Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
		    }
		    
	       } elseif ($Quotations==4 and $fechahoy==$fechacorte){//aqui es la condicion de los pedidos facturados
		    //si es un pedido facturado y la fecha de corte de caja es el mismo dia que en el que desean cancelar
		    //te aparece la imagen con la que podras cancelar si no te aparece la casilla vacia
		    //HAGO UNA CONSULTA PARA SABER SI LA FACTURA TIENE TIENE RECIBOS DE PAGO SIN CANCELAR.
		    $XSQL = "SELECT dm.id
			FROM debtortransmovs dm
			WHERE SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = " . $typedeb . "
			AND SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = " . $transno  . "
			UNION
			SELECT d.transno
			FROM debtortrans d
			WHERE d.type = 70 and d.reference = " . $transno . " and d.alloc <> 0";
			$tienerecibos = DB_query($XSQL ,$db,'','');
		    
		    if (intval(DB_num_rows($tienerecibos)) > intval(0)){
			$Cancelar="Tiene<br>Recibos";
		    }else{
			    $myrowtienerecibos = DB_fetch_row($tienerecibos);
			    if (Havepermission($_SESSION['UserID'],177, $db)==1){
				//$Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
				$Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&type='.$typedeb.'&tagref='.$tagref;
				$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
			    }
	    	    }
		    
	       } else {
		    //HAGO UNA CONSULTA PARA SABER SI LA FACTURA TIENE TIENE RECIBOS DE PAGO SIN CANCELAR.
		    $XSQL = "SELECT dm.id
			FROM debtortransmovs dm
			WHERE SUBSTRING(dm.reference, 1, LOCATE(' - ', dm.reference)-1) = " . $typedeb . "
			AND SUBSTRING(dm.reference, LOCATE(' - ', dm.reference)+3 ) = " . $transno . "
			UNION
			SELECT d.transno
			FROM debtortrans d
			WHERE d.type = 70 and d.reference = " . $transno . " and d.alloc <> 0";
		    $tienerecibos = DB_query($XSQL ,$db,'','');
		    
		    if (intval(DB_num_rows($tienerecibos)) > intval(0)){
			$Cancelar="Tiene<br>Recibos";
		    }else{
			$myrowtienerecibos = DB_fetch_row($tienerecibos);
			if (Havepermission($_SESSION['UserID'],177, $db)==1){
			    //$Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
			    $Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&type='.$typedeb.'&tagref='.$tagref;
			    $Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
			}
			
		    }
	       }
		
	       if (Havepermission($_SESSION['UserID'],203, $db)==1){
		    $VentaPerdida = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
		    #$VentaPerdida="<a href=".$VentaPerdida."><font size=1>Venta Perdida</a>";
		    $VentaPerdida ="&nbsp;";
	       } else {
		    $VentaPerdida ="&nbsp;";
	       }
	       $FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
	       $FormatedOrderDate = ConvertSQLDate($myrow['orddate']);


	       $arrayFecha=explode("/",$FormatedOrderDate);
	       $dia_pedido = $arrayFecha[0];
	       $mes_pedido = $arrayFecha[1];
	       $anio_pedido = $arrayFecha[2];
     
		    $fecha_pedido = strtotime($dia_pedido."-". $mes_pedido . "-" .$anio_pedido." 23:59:59");		
     
		    $tienepermiso="1";
		    
		    if ($fecha_pedido<$fecha_actual)
		    {
		      $tienepermiso=Havepermission("admin",202,$db);
		    }
    
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);
	  
	       if ($myrow['printedpackingslip']==0) {
		    $PrintText = _('Imprimir');
		} else {
		    $PrintText = _('Reimprimir');
		}
			
		if ($_SESSION['EnvioXSA']==1) {
		    $ReenvioXSA= $rootpath . '/SelectSalesOrder.php?orderno='.
		    $myrow['orderno'].
		    '&tagrefen='.$tagref.
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
		} else {
		    $ReenviarXSA='';
		}
		if (strlen($folio)==0 and $Quotations==4){
		    $PrintDispatchNote='';
		    $PrintText='';
		    $ReenviarXSA='';
		    $Cancelar='';
		}
		
		//$Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&type='.$typedeb.'&tagref='.$tagref;
		//$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
		
	       if ($_POST['Quotations']=='0'){
			printf("<td><a href='%s'>%s</a></td>
			        <td style='text-align:center;'>%s</td>
				<td><a href='%s'>" . _('Facturar') . "</a></td>
				<td><a target='_blank' href='%s'>" . $PrintText . "</a></td>
				<td style='text-align:center;'>%s</td>
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
				$folio_elec,
				$Confirm_Invoice,
				$PrintDispatchNote,
				$termpago,
				$myrow['name'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$VentaPerdida,
				$link_modTrabajadores,
				$tienepagares,
/*esto lo aumente*/		$tienepagares2,
				$myrow['tagdescription']
				);
	       } elseif($Quotations==4) {
		    printf("<td>%s</td>
			        <td style='text-align:center;'>%s</td>
				<td><a target='_blank' href='%s'>" . $PrintText . "</a>".$ReenviarXSA."</td>
				<td style='text-align:center;'>%s</td>
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
				</tr>",
				/*esto aumente la ultima linea del printf*/
				$myrow['orderno'],
				$folio_elec,
				$PrintDispatchNote,
				$termpago,
				$myrow['name'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$link_modTrabajadores,
				$tienepagares,
/*esto lo aumente*/		$tienepagares2,
				$myrow['tagdescription']
				);
	       } else { /*must be quotes only */
		
		    if ($tienepermiso=="1") {
			printf("<td style='text-align:center;'><a href='%s'>%s</a></td>
			        <td style='text-align:center;'>%s</td>
		        	<td><a href='%s'>" . $PrintText . "</a></td>
				<td style='text-align:center;'>%s</td>
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
				</tr>",
				/*esto aumente la ultima linea del printf*/
				$ModifyPage,
				$myrow['orderno'],
				$folio_elec,
				$PrintQuotation,
				$termpago,
				$myrow['name'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$VentaPerdida,
				$link_modTrabajadores,
				$tienepagares,
/*esto lo aumente*/		$tienepagares2,
				$myrow['tagdescription']
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
				<td nowrap style='text-align:right;'><font size=1>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</a></td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				</tr>",
				/*esto aumente la ultima linea del printf*/
				$myrow['orderno'],
				$folio_elec,
				$PrintQuotation,
				$termpago,
				$myrow['name'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$VentaPerdida,
				$link_modTrabajadores,
				$tienepagares,
/*esto lo aumente*/		$tienepagares2,
				$myrow['tagdescription']
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
	//end of while loop

	echo '</table>';
}

?>
</form>

<?php } //end StockID already selected

include('includes/footer.inc');
?>
