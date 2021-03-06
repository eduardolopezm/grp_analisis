<?php
/* $Revision: 1.22 $ */

/*
$PARCHE 10.1.1

ARCHIVO MODIFICADO POR: Desarrollador
FECHA DE MODIFICACION: 21/OCTUBRE/2010
DESCRIPCION: AGREGUE UN MOVIMIENTO CONTABLE PARA QUE MANDE DIFERENCIA PRECIO SI HAY Y CONTRA CUENTA
		PUENTE DE INVENTARIOS LAS UNIDADES POR SU MONTO EN FACTURA ORIGINAL.
		
		CORREGI QUE TE DEJABA PROCESAR SIN UNIDAD DE NEGOCIO, CORREGIDO, AHORA TE MANDA UN ERROR AL PROCESAR LA NOTA DE CREDITO.
		
		SI SE VA A MODIFICAR POR FAVOR CONSULTARLO CONMIGO YA QUE ES MUY COMPLICADA ESTA LOGICA DE APLIC. CONTABLES !.
*/

/*This page is very largely the same as the SupplierInvoice.php script
the same result could have been acheived by using if statements in that script and just having the one
SupplierTransaction.php script. However, to aid readability - variable names have been changed  -
and reduce clutter (in the form of a heap of if statements) two separate scripts have been used, 
both with very similar code.

This does mean that if the logic is to be changed for supplier transactions then it needs to be changed
in both scripts.

This is widely considered poor programming but in my view, much easier to read for the uninitiated

*/

/*The supplier transaction uses the SuppTrans class to hold the information about the credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

/*
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 10-NOV-2009 
 CAMBIOS:
	 1.- TRADUCCION A ESPA�OL
	 2.- CAMBIO DE UserStockLocation por unidad de negocio
 FIN DE CAMBIOS
*/


include('includes/DefineSuppTransClass.php');

$PageSecurity = 5;

/* Session started in header.inc for password checking and authorisation level check */

include('includes/session.inc');

$title = _('Nota de Credito de Proveedor');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Regresar a Proveedores') . '</a><br>';


if (isset($_POST['unidadnegocio'])){
	$unidadnegocio = $_POST['unidadnegocio'];
	$ShiptChg->tagref = $unidadnegocio;
} elseif (isset($_GET['unidadnegocio'])) {
	$unidadnegocio = $_GET['unidadnegocio'];
	$ShiptChg->tagref = $unidadnegocio;
} else {
	$unidadnegocio = $ShiptChg->tagref;
	$_POST['unidadnegocio'] = $unidadnegocio;
}

$tagrefdos = $unidadnegocio;

if (isset($_GET['SupplierID'])){
 /*It must be a new credit note entry - clear any existing credit note details from the SuppTrans object and initiate a newy*/
	if (isset($_SESSION['SuppTrans'])){
		unset ($_SESSION['SuppTrans']->GRNs);
		unset ($_SESSION['SuppTrans']->Shipts);
		unset ($_SESSION['SuppTrans']->GLCodes);
		unset ($_SESSION['SuppTrans']);
	}
	 $_SESSION['SuppTrans'] = new SuppTrans;
/*Now retrieve supplier information - name, currency, default ex rate, terms, tax rate etc */
	 $sql = "SELECT suppliers.suppname,
	 		paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
	 		suppliers.currcode,
			currencies.rate AS exrate,
			suppliers.taxgroupid,
			taxgroups.taxgroupdescription
	 	FROM suppliers,
			taxgroups,
			currencies,
			paymentterms,
			taxauthorities
	 	WHERE suppliers.taxgroupid=taxgroups.taxgroupid
		AND suppliers.currcode=currencies.currabrev
	 	AND suppliers.paymentterms=paymentterms.termsindicator
	 	AND suppliers.supplierid = '" . $_GET['SupplierID'] . "'";
	 $ErrMsg = _('El proveedor seleccionado') . ': ' . $_GET['SupplierID'] . ' ' . _('no se puede recuperar, por que');
	 $DbgMsg = _('El SQL utilizada para recuperar los detalles de proveedores');
	 $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	 $myrow = DB_fetch_array($result);
	 $_SESSION['SuppTrans']->SupplierName = $myrow['suppname'];
	 $_SESSION['SuppTrans']->TermsDescription = $myrow['terms'];
	 $_SESSION['SuppTrans']->CurrCode = $myrow['currcode'];
	 $_SESSION['SuppTrans']->ExRate = $myrow['exrate'];
	$_SESSION['SuppTrans']->TaxGroup = $myrow['taxgroupid'];
	$_SESSION['SuppTrans']->TaxGroupDescription = $myrow['taxgroupdescription'];
	 if ($myrow['daysbeforedue'] == 0){
		 $_SESSION['SuppTrans']->Terms = '1' . $myrow['dayinfollowingmonth'];
	} else {
		 $_SESSION['SuppTrans']->Terms = '0' . $myrow['daysbeforedue'];
	}
	$_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];
	$LocalTaxProvinceResult = DB_query("SELECT taxprovinceid 
						FROM locations 
						WHERE tagref = '" . $_SESSION['DefaultUnidad'] . "'", $db);
	if(DB_num_rows($LocalTaxProvinceResult)==0){
		prnMsg(_('La unidad de negocio de impuestos asociados con su cuenta de usuario no se ha establecido en esta base de datos. El Calculo de impuestos se basa en el grupo fiscal del proveedor y de la unidad de negocio de impuestos de los usuarios se introduzcan en la factura. El administrador del sistema debe redefinir su cuenta con una unidad de negocio predeterminada valida y este lugar debe referirse a una unidad fiscal valida.'),'error');
		include('includes/footer.inc');
		exit;
	}
	$LocalTaxProvinceRow = DB_fetch_row($LocalTaxProvinceResult);
	$_SESSION['SuppTrans']->LocalTaxProvince = $LocalTaxProvinceRow[0];
	$_SESSION['SuppTrans']->GetTaxes();
	$_SESSION['SuppTrans']->GLLink_Creditors = $_SESSION['CompanyRecord']['gllink_creditors'];
	$_SESSION['SuppTrans']->GRNAct = $_SESSION['CompanyRecord']['grnact'];
	$_SESSION['SuppTrans']->CreditorsAct = $_SESSION['CompanyRecord']['creditorsact'];
	$_SESSION['SuppTrans']->InvoiceOrCredit = 'Credit Note';

} elseif (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('Para ingresar nota de credito, primero seleccione el proveedor'),'warn');
	echo "<br><a href='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Seleccionar un proveedor para introducir una nota de credito ') . '</a>';
	exit;
	/*It all stops here if there aint no supplier selected */
}

/* Set the session variables to the posted data from the form if the page has called itself */
if (isset($_POST['ExRate'])){
	$_SESSION['SuppTrans']->ExRate = $_POST['ExRate'];
	$_SESSION['SuppTrans']->Comments = $_POST['Comments'];
	$_SESSION['SuppTrans']->TranDate = $_POST['TranDate'];
	if (substr( $_SESSION['SuppTrans']->Terms,0,1)=='1') { /*Its a day in the following month when due */
		$_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')+1, substr( $_SESSION['SuppTrans']->Terms,1),Date('y')));
	} else { /*Use the Days Before Due to add to the invoice date */
		$_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d') + (int) substr( $_SESSION['SuppTrans']->Terms,1),Date('y')));
	}
	$_SESSION['SuppTrans']->SuppReference = $_POST['SuppReference'];
	if (!isset($_POST['OvAmount'])) {
		$_POST['OvAmount'] = 0;
	}
	if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){
/*The link to GL from creditors is active so the total should be built up from GLPostings and GRN entries
if the link is not active then OvAmount must be entered manually. */
		$_SESSION['SuppTrans']->OvAmount = 0; /* for starters */
		if (count($_SESSION['SuppTrans']->GRNs) > 0){
			foreach ( $_SESSION['SuppTrans']->GRNs as $GRN){
				$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + ($GRN->This_QuantityInv * $GRN->ChgPrice);
			}
		}
		if (count($_SESSION['SuppTrans']->GLCodes) > 0){
			foreach ( $_SESSION['SuppTrans']->GLCodes as $GLLine){
				$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + $GLLine->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Shipts) > 0){
			foreach ( $_SESSION['SuppTrans']->Shipts as $ShiptLine){
				$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + $ShiptLine->Amount;
			}
		}
/*OvAmount must be entered manually */
		$_SESSION['SuppTrans']->OvAmount = round($_POST['OvAmount'],2);
	} else {
/*OvAmount must be entered manually */
		 $_SESSION['SuppTrans']->OvAmount = round($_POST['OvAmount'],2);
	}
}

if (isset($_POST['GRNS']) and $_POST['GRNS'] == _('Ingresa contra Productos')){
	/*This ensures that any changes in the page are stored in the session before calling the grn page */
	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SuppCreditGRNs.php?" . SID . "&unidadnegocio=".$unidadnegocio."'>";
	echo '<p>' . _('Debe ser redireccionado a la pagina de entrada de las notas de credito contra productos recibidos') . '. ' .
						_('si no es asi') . ' (' . _('tu navegador no soporta la actualizacion automatica') . ') ' .
						"<a href='" . $rootpath . "/SuppCreditGRNs.php?" . SID . "&unidadnegocio=".$unidadnegocio."'>" .
						_('click') . '</a> ' . _('para continuar') . '.<br>';
	include('includes/footer.inc');
	exit;
}
if (isset($_POST['Shipts'])){

	/*This ensures that any changes in the page are stored in the session before calling the shipments page */

	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SuppShiptChgs.php?" . SID . "&unidadnegocio=".$unidadnegocio."'>";
	echo '<p>' . _('Debe de ser redireccionado a la pagina de notas de credito contra translados') . '. ' .
						_('si no es asi') . ' (' . _('tu navegador no soporta la actualizacion automatica') . ') ' .
						"<a href='" . $rootpath . "/SuppShiptChgs.php?" . SID . "&unidadnegocio=".$unidadnegocio."'>" .
						_('click') . '</a> ' . _('para continuar') . '.<br>';
	include('includes/footer.inc');
	exit;
}
if (isset($_POST['GL']) and $_POST['GL'] == _('Ingresa contra contabilidad')){

	/*This ensures that any changes in the page are stored in the session before calling the shipments page */

	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "&unidadnegocio=".$unidadnegocio."'>";
	echo '<p>' . _('Debe ser automaticamente transmitida a la captura de nota de credito contra movimientos contables') . '. ' .
						_('si no es asi') . ' (' . _('tu navegador no soporta la actualizacion automatica') . ') ' .
						"<a href='" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "&unidadnegocio=".$unidadnegocio."'>" .
						_('click') . '</a> ' . _('para continuar') . '.<br>';
	include('includes/footer.inc');
	exit;
}
/* everything below here only do if a Supplier is selected
   fisrt add a header to show who we are making an credit note for */

echo "<table BORDER=2 colspan=4><tr><th>" . _('Proveedor') . "</th>
				<th>" . _('Moneda') . "</th>
				<th>" . _('Termino de pago') . "</th>
				<th>" . _('Grupo de Impuestos') . '</th></tr>';

echo '<tr><td><font color=blue><b>' . $_SESSION['SuppTrans']->SupplierID . ' - ' .
	  $_SESSION['SuppTrans']->SupplierName . '</b></font></td>
	  <th><font color=blue><b>' .  $_SESSION['SuppTrans']->CurrCode . '</b></font></th>
	  <td><font color=blue><b>' . $_SESSION['SuppTrans']->TermsDescription . '</b></font></td>
	  <td><font color=blue><b>' . $_SESSION['SuppTrans']->TaxGroupDescription . '</b></font></td>
	  </tr>
	  </table>';

echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post name=form1>";

echo '<table width=80% cellspacing=0 style="text-align:center;">';
	
	echo '<tr><td>'. _('Unidad de Negocio') . '<br>';
	
	/* 21-12-2009 -desarrollo- AQUI AGREGUE EL CODIGO PARA QUE SI LA UNIDAD DE NEGOCIO YA ESTA FIJA, NO DE POSIBILIDAD A CAMBIARLA */
	if (isset($_POST['unidadnegocio']) or isset($_GET['unidadnegocio'])) {
		echo '<select name=unidadnegocio>';

		$sql = 'SELECT distinct t.tagref, 
			tagdescription 
			FROM tags t
			where t.tagref = '. $unidadnegocio;

		$LocnResult = DB_query($sql,$db);
	
		echo "<option value='0'>SELECCIONA UNA...</option>";
		
		while ($LocnRow=DB_fetch_array($LocnResult)){
			echo "<option value='" . $LocnRow['tagref'] . "'";
			 if (intval($LocnRow['tagref'])==intval($unidadnegocio))
			 {
				echo ' selected';
			 }
			
			echo ">" . $LocnRow['tagdescription'];
		}
		
		echo '</select></td>';
	} else {
		echo '<select name=unidadnegocio>';

		echo "<option value='0'>SELECCIONA UNA...</option>";

		$sql = 'SELECT distinct t.tagref, 
			tagdescription 
			FROM tags t, sec_unegsxuser uxs
			where t.tagref=uxs.tagref
			and uxs.userid="'.$_SESSION['UserID'].'"';

			
			
			
		$LocnResult = DB_query($sql,$db);
	
		while ($LocnRow=DB_fetch_array($LocnResult)){
			echo "<option value='" . $LocnRow['tagref'] . "'";
			 if (intval($LocnRow['tagref'])==intval($unidadnegocio))
			 {
				echo ' selected';
			 }
			
			echo ">" . $LocnRow['tagdescription'];
		}
		
		echo '</select></td>';
		
	}
	/* FIN CAMBIOdesarrollo*/
	
echo '<tr><td><font color=red>' . _('Referencia de la nota de credito') . ":</font></td>
	<td><font size=2><input type=TEXT size=20 maxlength=20 name=SuppReference VALUE='" . $_SESSION['SuppTrans']->SuppReference . "'></td></tr>";

if (!isset($_SESSION['SuppTrans']->TranDate)){
	//$_SESSION['SuppTrans']->TranDate= Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')-1,Date('y')));
	//$_SESSION['SuppTrans']->TranDate
	$_SESSION['SuppTrans']->TranDate=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
	
}
echo '<tr><td><font color=red>' . _('Fecha Nota de Credito') . ' (' . _('en formato') . ' ' . $_SESSION['DefaultDateFormat'] . ") :</font></td>
		<td><input type=TEXT class='date' alt='".$_SESSION['DefaultDateFormat']. "' size=11 maxlength=10 name='TranDate' VALUE=" . $_SESSION['SuppTrans']->TranDate  . '></td></tr>';
echo '<tr><td><font color=red>' . _('Tipo de Cambio') . ":</font></td>
		<td><input type=TEXT class='number' size=11 maxlength=10 name='ExRate' VALUE=" . $_SESSION['SuppTrans']->ExRate . '></td></tr>';
echo '</table>';

echo "<br><div class='centre'><input type=submit name='GRNS' VALUE='" . _('Ingresa contra Productos') . "'> ";
echo "<input type=submit name='Shipts' VALUE='" . _('Ingresa contra envio') . "'> ";
if ( $_SESSION['SuppTrans']->GLLink_Creditors ==1){
	//echo "<input type=submit name='GL' VALUE='" . _('Ingresa contra contabilidad') . "'></div>";
} else {
	echo '</div>';
}

if (count($_SESSION['SuppTrans']->GRNs)>0){   /*if there are some GRNs selected for crediting then */

	/*Show all the selected GRNs so far from the SESSION['SuppInv']->GRNs array
	Note that the class for carrying GRNs refers to quantity invoiced read credited in this context*/

	echo '<table cellpadding=2>';
	$TableHeader = "<tr><th>" . _('GRN') . "</th>
				<th>" . _('Codigo') . "</th>
				<th>" . _('Descripcion') . "</th>
				<th>" . _('Cantidad') . '<br>' . _('Credited') . "</th>
				<th>" . _('Precio Acreditado') . '<br>' . _('en') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
				<th>" . _('Total') . '<br>' . _('en') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th></tr>';
	echo $TableHeader;
	$TotalGRNValue=0;

	foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
		echo '<tr><td>' . $EnteredGRN->GRNNo . '</td>
			<td>' . $EnteredGRN->ItemCode . '</td>
			<td>' . $EnteredGRN->ItemDescription . '</td>
			<td align=right>' . number_format($EnteredGRN->This_QuantityInv,2) . '</td>
			<td align=right>' . number_format($EnteredGRN->ChgPrice,2) . '</td>
			<td align=right>' . number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . '</td>
			<td></tr>';

		$TotalGRNValue = $TotalGRNValue + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);
		$TotalCantidad=$EnteredGRN->This_QuantityInv;

	}

	echo '<tr><td colspan=5 align=right><font color=red>' . _('Valor Total Acreditado') . ':</font></td>
		<td align=right><font color=red><U>' . number_format($TotalGRNValue,2) . '</U></font></td></tr>';
	echo '</table>';
}

if (count($_SESSION['SuppTrans']->Shipts)>0){   /*if there are any Shipment charges on the credit note*/

	echo "<table cellpadding=2><tr><th>" . _('Envio') . "</th>
				<th>" .  _('Monto Acreditado') . '</b></th></tr>';

	$TotalShiptValue=0;
	
	$i=0;
	
	foreach ($_SESSION['SuppTrans']->Shipts as $EnteredShiptRef){

		echo '<tr><td>' . $EnteredShiptRef->ShiptRef.'tag:'.$EnteredShiptRef->tagref. '</td><td align=right>' .
				number_format($EnteredShiptRef->Amount,2) . '</td></tr>';

		$TotalShiptValue = $TotalShiptValue + $EnteredShiptRef->Amount;

	}

	echo '<tr><td colspan=2 align=right><font size=4 color=red>' . _('Total de envio acreditado') .  ':</font></td>
		<td align=right><font size=4 color=red><U>' . number_format($TotalShiptValue,2) .  '</U></font></td></tr>';
}

if ($_SESSION['SuppTrans']->GLLink_Creditors ==1){

	if (count($_SESSION['SuppTrans']->GLCodes)>0){
		echo '<table cellpadding=2>';
		$TableHeader = "<tr><th>" . _('Cuenta') . "</th>
					<th>" . _('Nombre') . "</th>
					<th>" . _('Monto') . '<br>' . _('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
					<th>" . _('Envio') . "</th>
					<th>" . _('Trabajo') . "</th>
					<th>" . _('Comentario') . '</th></tr>';
		echo $TableHeader;

		$TotalGLValue=0;

		foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

			echo '<tr><td>' . $EnteredGLCode->GLCode . '</td>
				<td>' . $EnteredGLCode->GLActName . '</td>
				<td align=right>' . number_format($EnteredGLCode->Amount,2) . '</td>
				<td>' . $EnteredGLCode->ShiptRef . '</td>
				<td>' . $EnteredGLCode->JobRef . '</td>
				<td>' . $EnteredGLCode->Narrative . '</td></tr>';

			$TotalGLValue = $TotalGLValue + $EnteredGLCode->Amount;

			$i++;
			if ($i>15){
				$i=0;
				echo $TableHeader;
			}
		}

		echo '<tr><td colspan=2 align=right><font size=4 color=red>' . _('Total') . ':</font></td>
			<td align=right><font size=4 color=red><U>' . number_format($TotalGLValue,2) . '</U></font></td>
			</tr></table>';
	}
	
	if (!isset($TotalGRNValue)) {
		$TotalGRNValue=0;
	}
	if (!isset($TotalGLValue)) {
		$TotalGLValue=0;
	}
	if (!isset($TotalShiptValue)) {
		$TotalShiptValue=0;
	}

	$_SESSION['SuppTrans']->OvAmount = round($TotalGRNValue + $TotalGLValue + $TotalShiptValue,2);
	echo '<table><tr><td><font color=red>' . _('Monto de credito en moneda') . ':</font></td>
			<td colspan=2 align=right>' . number_format($_SESSION['SuppTrans']->OvAmount,2) . '</td></tr>';
} else {
	echo '<table><tr><td><font color=red>' . _("Monto de credito en moneda") .
		  ':</font></td>
		  	<td colspan=2 align=right><input type=TEXT size=12 maxlength=10 name=OvAmount VALUE=' . number_format($_SESSION['SuppTrans']->OvAmount,2) . '></td></tr>';
}

echo "<tr><td colspan=2><input type=Submit name='ToggleTaxMethod'VALUE='" . _('Cambiar metodo de calculo de impuesto') .
	  "'></td><td><select name='OverRideTax' onChange='ReloadForm(form1.ToggleTaxMethod)'>";

if ($_POST['OverRideTax']=='Man'){
	echo "<option VALUE='Auto'>" . _('Automatico') . "<option selected VALUE='Man'>" . _('Entrada Manual');
} else {
	echo "<option selected VALUE='Auto'>" . _('Automatico') . "<option VALUE='Man'>" . _('Entrada Manual');
}

echo '</select></td></tr>';
$TaxTotal =0; //initialise tax total

foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
	
	echo '<tr><td>'  . $Tax->TaxAuthDescription . '</td><td>';
	
	/*Set the tax rate to what was entered */
	if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
		$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = $_POST['TaxRate'  . $Tax->TaxCalculationOrder]/100;
	}
	
	/*If a tax rate is entered that is not the same as it was previously then recalculate automatically the tax amounts */
	
	if (!isset($_POST['OverRideTax']) or $_POST['OverRideTax']=='Auto'){
	
		echo  ' <input type=TEXT class="number" name=TaxRate' . $Tax->TaxCalculationOrder . ' maxlength=4 size=4 VALUE=' . $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100 . '>%';
		
		/*Now recaluclate the tax depending on the method */
		if ($Tax->TaxOnTax ==1){
			
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);
		
		} else { /*Calculate tax without the tax on tax */
			
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;
		
		}
		
		
		echo '<input type=hidden name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE=' . round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,2) . '>';
		
		echo '</td><td align=right>' . number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,2);
		
	} else { /*Tax being entered manually accept the taxamount entered as is*/
		$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_POST['TaxAmount'  . $Tax->TaxCalculationOrder];
				
		echo  ' <input type=hidden name=TaxRate' . $Tax->TaxCalculationOrder . ' VALUE=' . $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100 . '>';
		
				
		echo '</td><td><input type=TEXT class="number" size=12 maxlength=12 name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE=' . round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,2) . '>';
		
	}
	
	$TaxTotal += $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount;
	
	
	echo '</td></tr>';	
}

$DisplayTotal = number_format($_SESSION['SuppTrans']->OvAmount + $TaxTotal,2);

echo '<tr><td><font color=red>' . _('Total') . '</font></td><td colspan=2 align=right><b>' .
	  $DisplayTotal. '</b></td></tr></table>';

echo '<table><tr><td><font color=red>' . _('Comentarios') . '</font></td><td><textarea name=Comments cols=40 rows=2>' .
	  $_SESSION['SuppTrans']->Comments . '</textarea></td></tr></table>';

echo "<p><div class='centre'><input type=submit name='PostCreditNote' VALUE='" . _('Ingresar Nota de Credito') . "'></div>";


if (isset($_POST['PostCreditNote'])){

/*First do input reasonableness checks
then do the updates and inserts to process the credit note entered */

	$InputError = False;
	if ( $TaxTotal + $_SESSION['SuppTrans']->OvAmount <= 0 and round($TotalCantidad,2)<=0){
		$InputError = True;
		prnMsg(_('La nota de credito no puede ser procesado porque el importe total de la nota de credito es inferior o igual a 0') . '. ' . 	_('Ingrese cantidades positivas'),'warn');
	} elseif (strlen($_SESSION['SuppTrans']->SuppReference) < 1){
		$InputError = True;
		prnMsg(_('No ha ingresado la referencia del proveedor para la nota de credito') . '. ' . _('debe ingresar el numero de la referencia'),'error');
	} elseif (!is_date($_SESSION['SuppTrans']->TranDate)){
		$InputError = True;
		prnMsg(_('El formato de la fecha debe ser ') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	} elseif (DateDiff(Date($_SESSION['DefaultDateFormat']), $_SESSION['SuppTrans']->TranDate, "d") < 0){
		$InputError = True;
		prnMsg(_('La nota de credito no puede ser emitida con fecha posterior') . '. ' . _('Las ordenes de compra deben de tener una fecha anterior a la actual'),'error');
	}elseif ($_SESSION['SuppTrans']->ExRate <= 0){
		$InputError = True;
		prnMsg(_('El tipo de cambio debe ser mayor a cero') . '. ' . _('El tipo de cambio esta definido en el proveedor y de acuerdo a las divisas locales'),'warn');
	}elseif ($_SESSION['SuppTrans']->OvAmount < round($TotalShiptValue + $TotalGLValue + $TotalGRNValue,2)){
		prnMsg(_('El monto de la nota de credito es menor a la carga de productos') . ', ' . _('la contabilidad no sera igual') . '. ' . _('Existe un error ') . ', ' . _('la nota de credito no sera procesada'),'error');
		$InputError = True;
	}elseif ($_POST['unidadnegocio'] == 0){
		prnMsg(_('La unidad de negocios tiene que estar seleccionada') . _('la nota de credito no sera procesada'),'error');
		$InputError = True;
	} else {

	/* SQL to process the postings for purchase credit note */

	/*Start an SQL transaction */
		$Result = DB_Txn_Begin($db);
		//$SQL = 'BEGIN';
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
		$DbgMsg = _('El SQL utilizado es');

		//$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

		/*Get the next transaction number for internal purposes and the period to post GL transactions in based on the credit note date*/

		$CreditNoteNo = GetNextTransNo(37, $db);
		$PeriodNo = GetPeriod($_SESSION['SuppTrans']->TranDate, $db);
		$SQLCreditNoteDate = FormatDateForSQL($_SESSION['SuppTrans']->TranDate);


		if ($_SESSION['SuppTrans']->GLLink_Creditors == 1){

		/*Loop through the GL Entries and create a debit posting for each of the accounts entered */

			$LocalTotal = 0;

			/*the postings here are a little tricky, the logic goes like this:

			> if its a shipment entry then the cost must go against the GRN suspense account defined in the company record

			> if its a general ledger amount it goes straight to the account specified

			> if its a GRN amount credited then there are two possibilities:

			1 The PO line is on a shipment.
			The whole charge goes to the GRN suspense account pending the closure of the
			shipment where the variance is calculated on the shipment as a whole and the clearing entry to the GRN suspense
			is created. Also, shipment records are created for the charges in local currency.

			2. The order line item is not on a shipment
			The whole amount of the credit is written off to the purchase price variance account applicable to the
			stock category record of the stock item being credited.
			Or if its not a stock item but a nominal item then the GL account in the orignal order is used for the
			price variance account.
			*/

			foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

			/*GL Items are straight forward - just do the credit postings to the GL accounts specified -
			the debit is to creditors control act  done later for the total credit note value + tax*/

				$SQL = 'INSERT INTO gltrans (type,
								typeno,
								tag,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								jobref)
						 	VALUES (37,
								' . $CreditNoteNo . ',
								' . $EnteredGLCode->tagref . ",
								'" . $SQLCreditNoteDate . "',
								" . $PeriodNo . ',
								' . $EnteredGLCode->GLCode . ",
								'" . $_SESSION['SuppTrans']->SupplierID . " " . $EnteredGLCode->Narrative . "',
						 		" . round(-$EnteredGLCode->Amount/$_SESSION['SuppTrans']->ExRate,2) .
						 ", '" . $EnteredGLCode->JobRef . "'
						 		)";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
				$DbgMsg = _('El SQL utilizado es');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += ($EnteredGLCode->Amount/$_SESSION['SuppTrans']->ExRate);
				if (strlen($EnteredGLCode->tagref)>0){
				   $tagrefdos= $EnteredGLCode->tagref;
				}

			}

			foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			/*shipment postings are also straight forward - just do the credit postings to the GRN suspense account
			these entries are reversed from the GRN suspense when the shipment is closed - entries only to open shipts*/

				$SQL = 'INSERT INTO gltrans (type,
								typeno,
								tag,
								trandate,
								periodno,
								account,
								narrative,
								amount
								)
							VALUES (37,
								' . $CreditNoteNo . ',
								' . $unidadnegocio . ",   
								'" . $SQLCreditNoteDate . "',
								" . $PeriodNo . ',
								' . $_SESSION['SuppTrans']->GRNAct . ",
								'" . $_SESSION['SuppTrans']->SupplierID . ' ' .	 _('Referencia de Credito en envio') . ' ' . $ShiptChg->ShiptRef . "',
								" . round(-$ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate,2) . '
								)';
					            /*$ShiptChg->tagref. ",*/
					            
						    
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
				$DbgMsg = _('El SQL utilizado es');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += round($ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate,2);
				if (strlen($ShiptChg->tagref)>0){
				   $tagrefdos= $ShiptChg->tagref;
				}

			}

			foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
			        if (strlen($EnteredGRN->tagref)>0){
				   $tagrefdos= $EnteredGRN->tagref ; 
				}
				
				$tagrefdos = $unidadnegocio;
				 
				if (strlen($EnteredGRN->ShiptRef)==0 OR $EnteredGRN->ShiptRef=="" OR $EnteredGRN->ShiptRef==0){ /*so its not a shipment item */

						
					$CreditedPurchPrice = round($EnteredGRN->This_QuantityInv * ($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate),2);
					
					$OrigPurchPrice =  round($EnteredGRN->This_QuantityInv *
									($EnteredGRN->OrderPrice*(1-$EnteredGRN->Desc1/100)
									 *(1-$EnteredGRN->Desc2/100)*(1-$EnteredGRN->Desc3/100)
									 / $_SESSION['SuppTrans']->ExRate),2);
					
					$PurchPriceVar = $OrigPurchPrice - $CreditedPurchPrice;

					/*Yes but where to post this difference to - if its a stock item the variance account must be retrieved from the stock category record
					if its a nominal purchase order item with no stock item then  post it to the account specified in the purchase order detail record */

					if ($PurchPriceVar !=0){ /* don't bother with this lot if there is no value to post ! */
						if (strlen($EnteredGRN->ItemCode)>0 OR $EnteredGRN->ItemCode!=""){
						 /*so it is a stock item */
						 

							/*need to get the stock category record for this stock item - this is function in SQL_CommonFunctions.inc */

							$StockGLCode = GetStockGLCode($EnteredGRN->ItemCode,$db);

							$SQL = 'INSERT INTO gltrans (type,
											typeno,
											tag,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									 VALUES (37,
									 	' . $CreditNoteNo . ',
										' . $tagrefdos . ",
										'" . $SQLCreditNoteDate . "',
										" . $PeriodNo . ',
										' . $StockGLCode['purchpricevaract'] . ",
										'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN Credit Note') . ' ' .
											$EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' .
											$EnteredGRN->This_QuantityInv . ' x  ' . number_format($OrigPurchPrice,2)  . ' diff vs '. number_format($CreditedPurchPrice,2) ." = ',
										" . ($PurchPriceVar) . ')';

							$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
							$DbgMsg = _('El SQL utilizado es');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

						} else {

						/* its a nominal purchase order item that is not on a shipment so post the whole lot to the GLCode specified in the order, the purchase price var is actually the diff between the
						order price and the actual credit note price since the std cost was made equal to the order price in local currency at the time
						the goods were received */

							$SQL = 'INSERT INTO gltrans (type,
											typeno,
											tag,
											trandate,
											periodno,
											account,
											narrative,
											amount)
						
									VALUES (37,
										' . $CreditNoteNo . ',
										' . $tagrefdos. ",
										'" . $SQLCreditNoteDate . "',
										" . $PeriodNo . ',
										' . $EnteredGRN->GLCode . ",
										'" . $_SESSION['SuppTrans']->SupplierID . ' - ' .
									_('GRN Nota de Credito') . ' ' . $EnteredGRN->GRNNo . " - " . $EnteredGRN->ItemDescription . ' x ' . $EnteredGRN->This_QuantityInv . ' x ' . _('precio a') . ' ' . number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate),2) . "',
									" . (-$PurchPriceVar) . ')';

							$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
							$DbgMsg = _('El SQL utilizado es');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

						}

					}
					
					/* Envia el total original a la cuenta puente de inventarios */
					  
					$SQL = 'INSERT INTO gltrans (type,
									typeno,
									tag,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							 VALUES (37,
							 	' . $CreditNoteNo . ',
								' . $tagrefdos . ",
								'" . $SQLCreditNoteDate . "',
								" . $PeriodNo . ',
								' . $_SESSION['SuppTrans']->GRNAct . ",
								'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') .' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' .
									$EnteredGRN->This_QuantityInv . ' @ ' . $_SESSION['SuppTrans']->CurrCode . ' ' . $EnteredGRN->OrderPrice .
									' D1:'. $EnteredGRN->Desc1. ' D2:'. $EnteredGRN->Desc2. ' D3:'. $EnteredGRN->Desc3 .
									' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate . "',
								" . -$OrigPurchPrice . '
								)';
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
					$DbgMsg = _('El SQL utilizado es');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				} else {

					/*then its a purchase order item on a shipment - whole charge amount to GRN suspense pending closure of the shipment	when the variance is calculated and the GRN act cleared up for the shipment */
					  
					$SQL = 'INSERT INTO gltrans (type,
									typeno,
									tag,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							 VALUES (37,
							 	' . $CreditNoteNo . ',
								' . $tagrefdos . ",
								'" . $SQLCreditNoteDate . "',
								" . $PeriodNo . ',
								' . $_SESSION['SuppTrans']->GRNAct . ",
								'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') .' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' @ ' . $_SESSION['SuppTrans']->CurrCode . $EnteredGRN->ChgPrice . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate . "',
								" . round(-$EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) / $_SESSION['SuppTrans']->ExRate . '
								)';
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
					$DbgMsg = _('El SQL utilizado es');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				}

				$LocalTotal += round(($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) / $_SESSION['SuppTrans']->ExRate,2);

			} /* end of GRN postings */

			if ($debug == 1 AND abs(($_SESSION['SuppTrans']->OvAmount/ $_SESSION['SuppTrans']->ExRate) - $LocalTotal)>0.004){

				prnMsg(_('El total de la nota de credito es ') . ' ' . $LocalTotal . ' ' . _('la suma del monto convertida a tipo') . " = " . ($_SESSION['SuppTrans']->OvAmount / $_SESSION['SuppTrans']->ExRate),'error');
			}

			foreach ($_SESSION['SuppTrans']->Taxes as $Tax){
				/* Now the TAX account */

				$SQL = 'INSERT INTO gltrans (type, 
								typeno,
								tag,
								trandate, 
								periodno, 
								account, 
								narrative, 
								amount
								) 
						VALUES (37, ' .
						 	$CreditNoteNo . ',
							' . $tagrefdos . ",
						 	'" . $SQLCreditNoteDate . "', 
							" . $PeriodNo . ', 
							' . $Tax->TaxGLCode . ", 
						 	'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Credit note') . ' ' .
						 $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode .
						 $Tax->TaxOvAmount  . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate .
						 "', 
						 	" . round(-$Tax->TaxOvAmount/ $_SESSION['SuppTrans']->ExRate,2) . ')';
					  $ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
					  $DbgMsg = _('El SQL utilizado es');
					  $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			} /*end of loop to post the tax */
			/* Now the control account */
			//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
			$tipoproveedor = ExtractTypeSupplier($_SESSION['SuppTrans']->SupplierID,$db);
			$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
			//$_SESSION['SuppTrans']->CreditorsAct

			$SQL = 'INSERT INTO gltrans (type,
							typeno,
							tag,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					 VALUES (37,
					 	' . $CreditNoteNo . ',
						' . $tagrefdos . ",
						'" . $SQLCreditNoteDate . "',
						" . $PeriodNo . ',
						' . $ctaxtipoproveedor . ",
						'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Credit Note') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' .  $_SESSION['SuppTrans']->CurrCode . number_format($_SESSION['SuppTrans']->OvAmount + $_SESSION['SuppTrans']->OvGST,2)  . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate .  "',
						" . round($LocalTotal + ($TaxTotal / $_SESSION['SuppTrans']->ExRate),2) . ')';
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		} /*Thats the end of the GL postings */

	/*Now insert the credit note into the SuppTrans table*/

		$SQL = 'INSERT INTO supptrans (transno,
						tagref,
						type,
						supplierno,
						suppreference,
						trandate,
						duedate,
						ovamount,
						ovgst,
						rate,
						transtext,
						origtrandate
						)
			VALUES ('. $CreditNoteNo . ',
				' . $tagrefdos . ",
				37,
				'" . $_SESSION['SuppTrans']->SupplierID . "',
				'" . $_SESSION['SuppTrans']->SuppReference . "',
				'" . $SQLCreditNoteDate . "',
				'" . FormatDateForSQL($_SESSION['SuppTrans']->DueDate) . "',
				" . round(-$_SESSION['SuppTrans']->OvAmount,2) . ',
				' .round(-$TaxTotal,2) . ',
				' . $_SESSION['SuppTrans']->ExRate . ",
				'" . $_SESSION['SuppTrans']->Comments . "', now() )";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
		$DbgMsg = _('El SQL utilizado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');
		
		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['SuppTrans']->Taxes AS $TaxTotals) {
	
			$SQL = 'INSERT INTO supptranstaxes (supptransid,
							taxauthid,
							taxamount)
				VALUES (' . $SuppTransID . ',
					' . $TaxTotals->TaxAuthID . ',
					' . -$TaxTotals->TaxOvAmount . ')';
		
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
 			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		
		/* Now update the GRN and PurchOrderDetails records for amounts invoiced */

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

			$SQL = 'UPDATE purchorderdetails SET qtyinvoiced = qtyinvoiced - ' .
					 $EnteredGRN->This_QuantityInv . ' WHERE podetailitem = ' . $EnteredGRN->PODetailItem;

			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			$SQL = 'UPDATE grns SET quantityinv = quantityinv - ' .
					 $EnteredGRN->This_QuantityInv . ' WHERE grnno = ' . $EnteredGRN->GRNNo;
	
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			/*Update the shipment's accum value for the total local cost of shipment items being credited
			the total value credited against shipments is apportioned between all the items on the shipment
			later when the shipment is closed*/

			if (strlen($EnteredGRN->ShiptRef)>0 AND $EnteredGRN->ShiptRef!=0){

				/* and insert the shipment charge records */
				$SQL = 'INSERT INTO shipmentcharges (shiptref,
									transtype,
									transno,
									stockid,
									value)
							VALUES (' . $EnteredGRN->ShiptRef . ',
								37,
								' . $CreditNoteNo . ",
								'" . $EnteredGRN->ItemCode . "',
								" . round(-$EnteredGRN->This_QuantityInv * $EnteredGRN->ChgPrice / $_SESSION['SuppTrans']->ExRate,2) . '
								)';
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
				$DbgMsg = _('El SQL utilizado es');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			}

		} /* end of the loop to do the updates for the quantity of order items the supplier has credited */

		/*Add shipment charges records as necessary */

		foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			$SQL = 'INSERT INTO shipmentcharges (shiptref,
								transtype,
								transno,
								value)
							VALUES (' . $ShiptChg->ShiptRef . ',
								37,
								' . $CreditNoteNo . ',
								' . (-$ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate) . '
								)';
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		}

		$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
		$DbgMsg = _('El SQL commit utilizado es');
		//$SQL='COMMIT';
		//$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
		$Result = DB_Txn_Commit($db);	

		unset($_SESSION['SuppTrans']->GRNs);
		unset($_SESSION['SuppTrans']->Shipts);
		unset($_SESSION['SuppTrans']->GLCodes);
		unset($_SESSION['SuppTrans']);

		prnMsg(_('Numero de nota de credito para proveedor ') . ' ' . $CreditNoteNo . ' ' . _('se ha procesado'),'success');

		echo "<p><a href='$rootpath/SelectSupplier.php'>" . _('Ingresar Otra Nota de Credito') . '</a>';
	}

} /*end of process credit note */

echo '</form>';
include('includes/footer.inc');
?>