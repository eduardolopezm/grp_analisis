<?php

/* $Revision: 4.0 $ 
   ARCHIVO MODIFICADO POR: Desarrollador
FECHA DE MODIFICACION: 17-DIC-2009 
 CAMBIOS:
   1. CAMBIO DE por tipo de cotizacion y unidades de negocio por usuario
FECHA DE MODIFICACION: 30-DIC-2009 
   4. Se aumento la variable type que manda el valor por medio de GET a ConfirmCancel_Invoice
 FIN DE CAMBIOS
 FECHA DE MODIFICACION: 03-FEBRERO-2010 
 1.- Se agrego la variable liga para la impresion de los formatos preimpresos
 apartir de la linea 786 a la 797
 FIN DE CAMBIOS
*/
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Busqueda de Notas de Credito De Proveedor');
unset($_SESSION['ExistingOrder']);
include('includes/header.inc');
$funcion=990;
include('includes/SecurityFunctions.inc');
include ('Numbers/Words.php');
include ('includes/XSAInvoicing.inc');
// para el llamado al webservice
require_once('lib/nusoap.php');


if (Havepermission($_SESSION['UserID'],171, $db)==1){
    //echo '<p><a href="' . $rootpath . '/SelectSuppNoteItems.php?' . SID . '&NewOrder=Yes"><font size=2><b>' . _('Nuevo Pedido de Venta') . '</a>';
}	


echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Notas de Credito De Proveedor') . '" alt="">' . ' ' . _('BUSQUEDA DE NOTAS DE CREDITO DE PROVEEDOR') . '</p> ';

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
     $fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia);
     
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
$tagrefen=$_GET['tagrefen'];
$folio=$_GET['folio'];
//echo "folio:".$folio;
$serie=$_GET['serie'];

//echo "serie:".$serie;
$debtorno=$_GET['debtorno'];
$tipo=$_GET['tipo'];
$transno=$_GET['transno'];

if (isset($orden) and !isset($folio)){
     $SQL = "UPDATE suppnotesorders
     SET quotation =3
     WHERE orderno= " .  $orden;
     $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El pedido no se pudo cancelar');
     $DbgMsg = _('El siguiente SQL se utilizo para actualizar el pedido de venta');
     $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
     prnMsg(_('El Numero de Nota de Credito') . ' ' . $orden . ' ' . _('se ha cancelado'),'success');
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
		}
		$factelectronica= XSACreditNote($transno, $orden, $debtorno, $tipo ,$tagrefen ,$serie,$folio, $db);
		
		// Envia  los datos al archivooooo
		$myfile="NC_11_".$tagrefen.'_'.$transno.".txt"; //Ponele el nombre que quieras al archivo
		$factelectronica=utf8_encode($factelectronica);
		$empresa=trim($keyfact.'-'.$rfc);
		$nombre=trim($nombre);
		//$tipo=trim('Notas de Credito');
		$tipo=trim('Notas de Credito');
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
		'/SelectNotesOrder.php?OrderNumber=' . $orden . '&UnidNeg=' . $tagrefen 
		. '&FromDia=' . $FromDia 
		. '&FromMes=' . $FromMes 
		. '&FromYear=' . $FromYear 
		. '&ToDia=' . $ToDia
		. '&ToMes=' . $ToMes
		. '&ToYear=' . $ToYear
		. '&Quotations=2' 
		.'">';
		
}
//aqui empieza la seleccion de periodo desde hasta
     echo '<table>
	       <tr>
		    <td>' . _('Desde:') . '</td>
		    <td><select Name="FromDia">';
			 $sql = "SELECT * FROM cat_Days";
			 $dias = DB_query($sql,$db);
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
			      $Meses = DB_query($sql,$db);
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
			      $Todias = DB_query($sql,$db);
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
			 $ToMeses = DB_query($sql,$db);
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
		  echo '<br><b>' . _('El Numero de Nota de Credito debe ser numérico') . '</b><br>';
		  unset ($_REQUEST['OrderNumber']);
		  include('includes/footer.inc');
		  exit;
	} else {
		echo '<font size=4>'._('Nota de Credito ') . ' No. ' . $_REQUEST['OrderNumber'];
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

	$ErrMsg =  _('No existen productos');
	$DbgMsg = _('El SQL utilizado fue');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

 }

if (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {
	    echo '<br>'._('Nota de Credito No.') . ": <input type=text name='OrderNumber' maxlength=8 size=9>&nbsp ";
		    echo _('Unidad de Negocio') . ":<select name='UnidNeg'> ";
		    $sql="SELECT t.tagref, t.tagdescription
			 FROM tags t, sec_unegsxuser uxu
			 WHERE t.tagref=uxu.tagref
			      AND uxu.userid='".$_SESSION['UserID']."'
			ORDER BY tagdescription";
		    $resultTags = DB_query($sql,$db);
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
		    echo '</select> &nbsp&nbsp';		
		
		if (isset($_GET['Quotations'])){
			$_POST['Quotations']=$_GET['Quotations'];
		}elseif(isset($_POST['Quotations'])){
		    $_POST['Quotations']=$_POST['Quotations'];
		}else{
		    $_POST['Quotations']='1';
		}
		
		echo _('Estatus') . ' : <select name="Quotations">';
		
		
		if ($_POST['Quotations']=='1'){
		    if (Havepermission($_SESSION['UserID'],334, $db)==1){
			echo '<option selected VALUE="1">' . _('Solicitudes');
		    }
		    if (Havepermission($_SESSION['UserID'],335, $db)==1){
			echo '<option VALUE="0">' . _('Notas de Credito Autorizadas');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],336, $db)==1){	
			echo '<option VALUE="3">' . _('Notas de Credito Canceladas');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],337, $db)==1){	
			echo '<option VALUE="2">' . _('Notas de Credito Procesadas');
		    }
			
		} elseif ($_POST['Quotations']=='0') {
		    if (Havepermission($_SESSION['UserID'],334, $db)==1){
		    	echo '<option  VALUE="1">' . _('Solicitudes');
		    }
		    if (Havepermission($_SESSION['UserID'],335, $db)==1){
			echo '<option selected VALUE="0">' . _('Notas de Credito Autorizadas');
		    }/*
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Notas de Credito Pendientes');
		    }
		    */
		    if (Havepermission($_SESSION['UserID'],336, $db)==1){	
			echo '<option VALUE="3">' . _('Notas de Credito Canceladas');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],337, $db)==1){	
			echo '<option VALUE="2">' . _('Notas de Credito Procesadas');
		    }
	       } elseif ($_POST['Quotations']=='3') {
		    if (Havepermission($_SESSION['UserID'],334, $db)==1){
		    	echo '<option  VALUE="1">' . _('Solicitudes');
		    }
		    if (Havepermission($_SESSION['UserID'],335, $db)==1){
			echo '<option  VALUE="0">' . _('Notas de Credito Autorizadas');
		    }/*
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Notas de Credito Pendientes');
		    }*/
		    
		    if (Havepermission($_SESSION['UserID'],336, $db)==1){	
			echo '<option selected VALUE="3">' . _('Notas de Credito Canceladas');
		    }
		    
		    if (Havepermission($_SESSION['UserID'],337, $db)==1){	
			echo '<option VALUE="2">' . _('Notas de Credito Procesadas');
		    }
		} elseif ($_POST['Quotations']=='2') {
		    if (Havepermission($_SESSION['UserID'],334, $db)==1){
		    	echo '<option  VALUE="1">' . _('Solicitudes');
		    }
		    if (Havepermission($_SESSION['UserID'],335, $db)==1){
			echo '<option  VALUE="0">' . _('Notas de Credito Autorizadas');
		    }/*
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){
			echo '<option VALUE="2">' . _('Notas de Credito Autorizadas');
		    }*/
		    
		    if (Havepermission($_SESSION['UserID'],336, $db)==1){	
			echo '<option VALUE="3">' . _('Notas de Credito Canceladas');
		    }
		    if (Havepermission($_SESSION['UserID'],337, $db)==1){	
			echo '<option selected VALUE="2">' . _('Notas de Credito Procesadas');
		    }
	       } else {
		    if (Havepermission($_SESSION['UserID'],334, $db)==1){
			echo '<option  VALUE="1">' . _('Solicitudes');
		    }
		    if (Havepermission($_SESSION['UserID'],335, $db)==1){
			echo '<option VALUE="0">' . _('Notas de Credito Autorizadas');
		    }
		    /*
		    if (Havepermission($_SESSION['UserID'],175, $db)==1){	
			echo '<option selected VALUE="2">' . _('Notas de Credito Pendientes');
		    }*/
		    if (Havepermission($_SESSION['UserID'],336, $db)==1){	
			echo '<option VALUE="3">' . _('Notas de Credito Canceladas');
		    }
		    if (Havepermission($_SESSION['UserID'],337, $db)==1){	
			echo '<option VALUE="2">' . _('Notas de Credito Procesadas');
		    }
	       }
		echo '</select> &nbsp&nbsp;';
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
			 echo"<td><font size=3>Notas de Credito Procesadas</td>";
		    } elseif ($Quotations==3) {
			 echo"<td><font size=3>Notas de Credito Canceladas</td>";
		    } elseif ($Quotations==2) {
			 echo"<td><font size=3>Notas de Credito Procesadas</td>";
		    } elseif ($Quotations==1) {
			 echo"<td><font size=3>Solicitudes</td>";
		    } elseif ($Quotations==0) {
			 echo"<td><font size=3>Notas de Credito Cerradas</td>";
		    }
	       echo"</tr>";
	echo"</table>";
	
	if(!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}
	if (isset($_REQUEST['OrderNumber']) && $_REQUEST['OrderNumber'] !='') {
			$SQL = "SELECT suppnotesorders.orderno,
					suppliers.suppname as name,
					suppnotesorders.UserRegister,
					suppliers.taxid as brname,
					
					suppnotesorders.orddate,
					suppnotesorders.deliverydate,
					
					
					suppnotesorders.supplierno as debtorno,
					(SUM(suppnotesorderdetails.unitprice*suppnotesorderdetails.quantity*(1-suppnotesorderdetails.discountpercent)* (1-suppnotesorderdetails.discountpercent1)*(1-suppnotesorderdetails.discountpercent2))) + suppnotesorders.taxtotal AS ordervalue
					,tagdescription
				FROM suppnotesorders,
					suppnotesorderdetails,
					debtorsmaster,
					custbranch, paymentterms, sec_unegsxuser, tags
				WHERE suppnotesorders.orderno = suppnotesorderdetails.orderno
				AND suppnotesorders.orddate>= '".$fechaini."' and suppnotesorders.orddate<='".$fechafin."'
				AND suppnotesorders.branchcode = custbranch.branchcode
				AND suppnotesorders.supplierno = suppliers.supplierid";
				if ($Quotations!=4){
					$SQL=$SQL." AND suppnotesorderdetails.completed=0";
				}
				$SQL=$SQL." AND suppnotesorders.orderno=". $_REQUEST['OrderNumber'] ."
				AND suppnotesorders.quotation =" .$Quotations . "
				
			        AND suppnotesorders.tagref=tags.tagref	
			        AND sec_unegsxuser.tagref=suppnotesorders.tagref
			        AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
				GROUP BY suppnotesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					 
					suppnotesorders.orddate,
					suppnotesorders.deliverydate
					
					 
				ORDER BY tagdescription, suppnotesorders.orderno";
				echo "1".$SQL;
				
     } else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

	       if (isset($_REQUEST['SelectedCustomer'])) {

			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT suppnotesorders.orderno,
						suppliers.suppname as name,
						suppnotesorders.UserRegister,
						suppliers.taxid as brname,
						
						suppnotesorders.orddate,
						suppnotesorders.deliverydate,
					
					
						'CREDITO' as type,
						1  asnumberofpayments,
						1 as generatecreditnote,
						suppnotesorders.supplierno as debtorno,
						(SUM(suppnotesorderdetails.unitprice*suppnotesorderdetails.quantity*(1-suppnotesorderdetails.discountpercent)* (1-suppnotesorderdetails.discountpercent1)*(1-suppnotesorderdetails.discountpercent2))) + suppnotesorders.taxtotal AS ordervalue
						,tagdescription,tags.tagref
					FROM suppnotesorders,
						suppnotesorderdetails,
						suppliers,
						sec_unegsxuser, tags
					WHERE suppnotesorders.orderno = suppnotesorderdetails.orderno
					AND suppnotesorders.orddate>= '".$fechaini."' and suppnotesorders.orddate<='".$fechafin."'
					AND suppnotesorders.supplierno = suppliers.supplierid
					
					AND sec_unegsxuser.tagref=tags.tagref	
					AND suppnotesorders.tagref=tags.tagref		
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					";
					$SQL=$SQL." 
					AND suppnotesorders.quotation =" .$Quotations . "
					AND suppnotesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] ."'
					AND suppnotesorders.debtorno='" . $_REQUEST['SelectedCustomer'] ."'";
					if (strlen($_POST['StockLocation'])>0){	
					   $SQL= $SQL. " AND suppnotesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
					}
					if (strlen($_POST['UnidNeg'])>0){	
					   $SQL= $SQL. " AND suppnotesorders.tagref = '". $_POST['UnidNeg'] . "'";
					}
					    $SQL=$SQL." ORDER BY tagdescription, suppnotesorders.orderno";
					    echo"2".$SQL;
			} else {
				$SQL = "SELECT suppnotesorders.orderno,
						suppliers.suppname as name,
						suppnotesorders.UserRegister,
						suppliers.taxid as brname,
						
						suppnotesorders.orddate,
						suppnotesorders.deliverydate,
					
					    
						'CREDITO' as type,
						1  asnumberofpayments,
						1 as generatecreditnote,
						suppnotesorders.supplierno as debtorno,
						(SUM(suppnotesorderdetails.unitprice*suppnotesorderdetails.quantity*(1-suppnotesorderdetails.discountpercent)* (1-suppnotesorderdetails.discountpercent1)*(1-suppnotesorderdetails.discountpercent2))) + suppnotesorders.taxtotal AS ordervalue
						,tagdescription,tags.tagref
					FROM suppnotesorders,
						suppnotesorderdetails,
						suppliers,
						sec_unegsxuser, tags
					WHERE suppnotesorders.orderno = suppnotesorderdetails.orderno
					AND suppnotesorders.orddate>= '".$fechaini."' and suppnotesorders.orddate<='".$fechafin."'
					AND suppnotesorders.supplierno = suppliers.supplierid
					
					AND sec_unegsxuser.tagref=tags.tagref	
					AND suppnotesorders.tagref=tags.tagref
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					
					AND suppnotesorders.quotation =" .$Quotations ;
					$SQL=$SQL." AND paymentterms.termsindicator=suppnotesorders.paytermsindicator
					AND suppnotesorders.supplierno='" . $_REQUEST['SelectedCustomer'] . "'";
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND suppnotesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
					}
					if (strlen($_POST['UnidNeg'])>0){	
					   $SQL= $SQL. " AND suppnotesorders.tagref = '". $_POST['UnidNeg'] . "'";
					}					
						
				   $SQL= $SQL."
					GROUP BY suppnotesorders.orderno,
						debtorsmaster.name,
						suppnotesorders.debtorno,
						custbranch.brname,
						 
						suppnotesorders.orddate,
					
						suppnotesorders.deliverydate
					ORDER BY tagdescription, suppnotesorders.orderno";
					echo"3".$SQL;
			 }
	       } else { //no customer selected
			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT suppnotesorders.orderno,
						suppliers.suppname as name,
						suppnotesorders.UserRegister,
						suppliers.taxid as brname,
						
						suppnotesorders.orddate,
						suppnotesorders.deliverydate,
					
					
						'CREDITO' as type,
						1  asnumberofpayments,
						1 as generatecreditnote,
						suppnotesorders.supplierno as debtorno,
						(SUM(suppnotesorderdetails.unitprice*suppnotesorderdetails.quantity*(1-suppnotesorderdetails.discountpercent)* (1-suppnotesorderdetails.discountpercent1)*(1-suppnotesorderdetails.discountpercent2))) + suppnotesorders.taxtotal AS ordervalue
						,tagdescription,tags.tagref
					FROM suppnotesorders,
						suppnotesorderdetails,
						suppliers,
						sec_unegsxuser, tags
					WHERE suppnotesorders.orderno = suppnotesorderdetails.orderno
					AND suppnotesorders.orddate>= '".$fechaini."' and suppnotesorders.orddate<='".$fechafin."'
					AND suppnotesorders.supplierno = suppliers.supplierid
					AND tags.tagref=suppnotesorders.tagref	
					AND sec_unegsxuser.tagref=tags.tagref	
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'";
					$SQL=$SQL." 
					AND suppnotesorders.quotation =" .$Quotations . "
					AND suppnotesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] . "'";
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND suppnotesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
					}
					if (strlen($_POST['UnidNeg'])>0){	
					   $SQL= $SQL. " AND suppnotesorders.tagref = '". $_POST['UnidNeg'] . "'";
					}					
					$SQL=$SQL."
					GROUP BY suppnotesorders.orderno,
						suppliers.taxid,
						suppliers.suppname,
						 
						suppnotesorders.orddate,
					
						suppnotesorders.deliverydate
						 
					ORDER BY tagdescription, suppnotesorders.orderno";
					echo"4".$SQL;
			 } else {
				$SQL = "SELECT suppnotesorders.orderno,
						suppliers.suppname as name,
						suppnotesorders.UserRegister,
						suppliers.taxid as brname,
						
						suppnotesorders.orddate,
						suppnotesorders.deliverydate,
						
					         
						'CREDITO' as type,
						1  asnumberofpayments,
						1 as generatecreditnote,
						suppnotesorders.supplierno as debtorno,
						(SUM(suppnotesorderdetails.unitprice*suppnotesorderdetails.quantity*(1-suppnotesorderdetails.discountpercent)* (1-suppnotesorderdetails.discountpercent1)*(1-suppnotesorderdetails.discountpercent2))) + suppnotesorders.taxtotal AS ordervalue
						,tagdescription,tags.tagref
					FROM suppnotesorders,
						suppnotesorderdetails,
						suppliers,
						sec_unegsxuser, tags
					WHERE suppnotesorders.orderno = suppnotesorderdetails.orderno
					/*AND suppnotesorders.orddate>= '".$fechaini."' and suppnotesorders.orddate<='".$fechafin."'*/
					AND suppnotesorders.supplierno = suppliers.supplierid
					
					AND sec_unegsxuser.tagref=tags.tagref	
					AND tags.tagref=suppnotesorders.tagref
					AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
					";
					$SQL=$SQL." 
					AND suppnotesorders.quotation =" .$Quotations ;
					if (strlen($_POST['UnidNeg'])>0){	
					   $SQL= $SQL. " AND suppnotesorders.tagref = '". $_POST['UnidNeg'] . "'";
					}
					if (strlen($_POST['StockLocation'])>0){	
					     $SQL= $SQL. " AND suppnotesorders.fromstkloc = '". $_POST['StockLocation'] . "'";
					}
					$SQL=$SQL."
					GROUP BY suppnotesorders.orderno,
						suppliers.taxid,
						suppliers.suppname,
						 
						suppnotesorders.orddate,
					
						suppnotesorders.deliverydate
						 
					ORDER BY tagdescription, suppnotesorders.orderno";
					//echo"4".$SQL;
			 }
	       } //end selected customer
     } //end not order number selected
	$ErrMsg = _('No hay solicitudes o notas de credito');
	
	$suppnotesordersResult = DB_query($SQL,$db,$ErrMsg);
	/*show a table of the orders returned by the SQL */
	
	echo '<table cellpadding=2 colspan=7 WIDTH=100%>';
	
     $fecha_actual = strtotime(date("d-m-Y H:i:00",time()));

	  if (isset($_POST['Quotations']) and $_POST['Quotations']!='2'){
		$tableheader = "<tr>
			        <th nowrap>" . _('Modificar') . "</th>
				<th nowrap>" . _('Imprimir') . "</th>
				<th nowrap>" . _('Proveedor') . "</th>
				<th nowrap>" . _('Pedido') . " #</th>
				<th nowrap>" . _('Fecha') . "</th>
				<th nowrap>" . _('Usuario') . "</th>
				<th nowrap>" . _('Total') . "</th>";
	       if ($Quotations!=4){		
		    if (Havepermission($_SESSION['UserID'],336, $db)==1){
			     $tableheader=$tableheader."<th>" . _('Cancelar') . "</th>";
		    }
	       } elseif ($Quotations==4) {
		    /*if (Havepermission($_SESSION['UserID'],177, $db)==1){
		    	$tableheader=$tableheader."<th>" . _('Cancelar Factura') . "</th>";
		    }*/
		  
	       }
	       $tableheader=$tableheader."<th>" . _('U. Negocio') . "</th></tr>";
	  } else {
		$tableheader = "<tr>
		                <th nowrap>" . _('Modificar') . "</th>
				<th nowrap>" . _('Folio') . "</th>
			        <th nowrap>" . _('Imprimir') . "</th>
				<th nowrap>" . _('Proveedor') . "</th>
				<th nowrap>" . _('Referencia') . " #</th>
				<th nowrap>" . _('Fecha') . "</th>
				<th nowrap>" . _('Usuario') . "</th>
				<th nowrap>" . _('Total') . "</th>";
			      if ($Quotations!=4){
				   if (Havepermission($_SESSION['UserID'],336, $db)==1)
				   {
					$tableheader=$tableheader."<th>" . _('Cancelar') . "</th>";
				   }
			      } 
			      $tableheader=$tableheader."<th>" . _('U. Negocio') . "</th></tr>";
	  }
	
     echo $tableheader;
     $fechanio=date('Y');
     $fechames=date('m');
     $fechadia=date('d');
     $fechahoy=$fechanio.'-'.$fechames.'-'.$fechadia;
     $j = 1;
     $k=0; //row colour counter
     while ($myrow=DB_fetch_array($suppnotesordersResult)) {
	  $orddate=$myrow['orddate'];
	  $orderno=$myrow['orderno'];
	  $escredito =$myrow['numberofpayments'];
	  $tagordenado=$myrow['tagref'];
	  $debtorno=$myrow['debtorno'];
	  $generapagares =$myrow['generatecreditnote'];
	  $sqlorden = "select type, transno, folio,
			case when alloc <> 0 then 0 else 1 end  as cancelar, id
			from supptrans where transtext not like '%CANCELADA%'
			and type=33
			and order_=".$orderno;
			//echo "<br>" . $sqlorden;
	   if (strlen($_POST['UnidNeg'])>0){
		$sqlorden=$sqlorden. " and tagref=".$_POST['UnidNeg'];
	   }
		
			
	  #echo $sqlorden;
	  $Resultorden = DB_query($sqlorden,$db,$ErrMsg);
	  if (DB_num_rows($Resultorden)>0)
	  {
	       $myrowOrden=DB_fetch_row($Resultorden);
	       if ($myrowOrden[0]<>'0')
	       {
		    $typedoc=$myrowOrden[0];
		    $numorden = $myrowOrden[1];
		    $folio_elec = $myrowOrden[2];
		    $cancelar = $myrowOrden[3];
		    $iddocto=$myrowOrden[4];
	       }
	       else
	       {
		    $typedoc='';
		    $numorden='';
		    $folio_elec = '&nbsp;';
		    $cancelar = 0;
		    $iddocto=0;
	       }
	  }     
	  else
	  {
	       $typedoc='';
	       $numorden='';
	       $folio_elec = '&nbsp;';
	       $cancelar = 0;
	       $iddocto=0;
	  }

	       //aqui se recupera la fecha con la que sera comparada la fecha actual para poder dar permiso o no de cancelar la factura
	       if ($Quotations==2){
		    $sql="select transno,type,tagref,folio,id,abs(ovamount+ovgst) as total
			 from supptrans
			 where type in (33) and order_=".$orderno;
		    $Fechacomparacion = DB_query($sql,$db);
		    while ($myrowcomparacion=DB_fetch_array($Fechacomparacion)) {
			 $transno=$myrowcomparacion['transno'];
			 $typedeb=$myrowcomparacion['type'];
			 $tagref=$myrowcomparacion['tagref'];
			 $iddocto=$myrowcomparacion['id'];
			$total=$myrowcomparacion['total'];
			 $foliox=$myrowcomparacion['folio'];
			 $separa = explode('|',$foliox);
			 $serie = $separa[0];
			 $folio = $separa[1];
			 // trae el key y rfc de la empresa
			 $SQLkey=" SELECT l.taxid,l.address5,t.tagdescription
			       FROM legalbusinessunit l, tags t
			       WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
			       
			 $Resultag= DB_query($SQLkey,$db);
			 if (DB_num_rows($Resultag)==1) {
			 	$myrowtags = DB_fetch_array($Resultag);
			 	$rfc=trim($myrowtags['taxid']);
			 	$keyfact=$myrowtags['address5'];
			      }
			 $sql="select date(fechacorte) as fechacorte
			      from usrcortecaja
			      where u_status=0 and tag=".$tagref;
			      $Fechacorte = DB_query($sql,$db);
			 while ($myrowcorte=DB_fetch_array($Fechacorte)) {
			      $fechacorte=$myrowcorte['fechacorte'];
			 }
		    }
		    
	       }
	       if ($k==1){
		    echo '<tr class="EvenTableRows">';
		    $k=0;
	       } else {
		    echo '<tr class="OddTableRows">';
		    $k++;
	       }
             
	       if ($Quotations!=2){
		    $ModifyPage = $rootpath . "/SelectSuppNoteItems.php?" . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
	       } else {
		    $ModifyPage = $myrow['orderno'];
	       }
	       $Confirm_Invoice = $rootpath . '/SelectSuppNoteItems.php?' . SID . '&ModifyOrderNumber=' .$myrow['orderno'];
	       //aqui es para la impresion de la factura
	        if ($_SESSION['PackNoteFormat']==1 and $Quotations!=2) { //Laser printed A4 default 
		    $PrintDispatchNote = $rootpath . '/PDFNoteOrdersSuppliers.php?' . SID . 'TransNo=' . $myrow['orderno'];
		    //$liga = GetUrlToPrint($tagref,11,$db);
		    //$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'TransNo=' .$myrow['orderno'] ;
	        } elseif($Quotations==2) { //pre-printed stationery default
		    if ($_SESSION['EnvioXSA']==1) {
			$PrintDispatchNote=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
			
		    } else {
				$liga = GetUrlToPrint($myrow['tagref'],11,$db);
				$liga="PDFNoteOrdersSuppliers.php";
				$PrintDispatchNote = $rootpath . '/' . $liga  . '?' . SID . 'PrintPDF=Yes&TransNo='.$myrow['orderno'].'&Tagref='.$myrow['tagref'];
		  }
	        }
		$PrintQuotation = $rootpath . '/PDFNoteOrdersSuppliers.php?' . SID . 'TransNo=' . $myrow['orderno'];
	
		 $ReenviarXSA='';
	       //aqui pone la imagen y el redireccionamoento para cancelar
	       if ($Quotations!=2){
		    if ((Havepermission($_SESSION['UserID'],196, $db)==1)){
			 $Cancelar = $rootpath . '/SelectNotesOrderSuppliers.php?orderno='. $myrow['orderno'];
			 $Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
		    }
	       }else{
		$SQL="select * from suppallocs where transid_allocfrom=".$iddocto;
		//echo $SQL.'<br>';
		    $ResultConsultados = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		    if (DB_num_rows($ResultConsultados)==0) {
			$permiso = Havepermission($_SESSION['UserID'],372, $db);
			if ($permiso==1){
			    $Cancelar = $rootpath . '/ConfirmCancel_SuppNoteCreditReturn.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&type='.$typedeb.'&tagref='.$tagref;
			    $Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
			}else{
			    $Cancelar="&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			// $Cancelar="&nbsp;&nbsp;&nbsp;&nbsp;";
		    }else{
			$Cancelar=_('Aplicada');
		    }
		
		/*
		    if ((Havepermission($_SESSION['UserID'],372, $db)==1) and ($cancelar == 1)){
			
			$Cancelar = $rootpath . '/ConfirmCancel_NoteCreditReturn.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&type='.$typedeb.'&tagref='.$tagref;
			$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
		    }
	*/
	       }
	       
//	       $Cancelar = $rootpath . '/ConfirmCancel_NoteCreditReturn.php?TransNo='.$transno.'&OrderNumber='. $myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&type='.$typedeb.'&tagref='.$tagref;
//			$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
			
	       $FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
	       $FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
	       $arrayFecha=explode("/",$FormatedOrderDate);
	       $dia_pedido = $arrayFecha[0];
	       $mes_pedido = $arrayFecha[1];
	       $anio_pedido = $arrayFecha[2]; 
	       $fecha_pedido = strtotime($dia_pedido."-". $mes_pedido . "-" .$anio_pedido." 23:59:59");		
	       $FormatedOrderValue = number_format($myrow['ordervalue'],2);
	  
	       if ($myrow['printedpackingslip']==0) {
		    $PrintText = _('Imprimir');
		} else {
		    $PrintText = _('Reimprimir');
		}
		//echo $Quotations;
	       if ($Quotations!=2){
			printf("<td><a href='%s'>%s</a></td>
			        <td><a target='_blank' href='%s'>" . $PrintText . "</a></td>
				<td><font size=1>%s</td>
				<td style='text-align:center;'><font size=1>%s</td>
				<td ><font size=1>%s</td>
				<td style='text-align:right;'>%s</td>
				<td nowrap style='text-align:right;'>%s</td>
				<td style='text-align:center;'>%s</td>
				<td style='text-align:center;'>%s</td>
				</tr>",
				$ModifyPage,
				$myrow['orderno'],
				$PrintDispatchNote,
				$myrow['name'],
				$myrow['orderno'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$myrow['tagdescription']
				);
	       } elseif($Quotations==2) {
		
		    printf("<td>%s</td>
			        <td style='text-align:center;'>%s</td>
				<td><a target='_blank' href='%s'>" . $PrintText . "</a>".$ReenviarXSA."</td>
				<td >%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td><font size=1>%s</td>
				<td nowrap style='text-align:center;'><font size=1>%s</td>
				<td >%s</td>
				</tr>",
				$myrow['orderno'],
				$folio_elec,
				$PrintDispatchNote,
				$myrow['name'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$myrow['UserRegister'],
				'$ '.$FormatedOrderValue,
				$Cancelar,
				$myrow['tagdescription']
				);
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
