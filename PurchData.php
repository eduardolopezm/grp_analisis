<?php

/* $Revision: 1.24 $ */
/*Cambios:
1.- Se agrego el  include('includes/SecurityFunctions.inc');
fecha: 09/12/2009
2.- se arreglo la variable $funcion*/
$PageSecurity = 4;

include('includes/session.inc');

$title = _('Datos de Compra');

include('includes/header.inc');
$funcion=43;
include('includes/SecurityFunctions.inc');

if (isset($_GET['SupplierID'])){
	$SupplierID = trim(strtoupper($_GET['SupplierID']));
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = trim(strtoupper($_POST['SupplierID']));
}

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}
/*echo "<div class='centre' align='center'>";
echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'><img src='images/b_regresar_25.png' title='"._("REGRESAR A PAGINA DE PRODUCTO")."'></a><br>";
echo "</div>";*/
if( isset($_POST['SupplierDescription']) ) {
    $_POST['SupplierDescription'] = trim($_POST['SupplierDescription']);
}


if (isset($SupplierID) AND $SupplierID!=''){			   /*NOT EDITING AN EXISTING BUT SUPPLIER selected OR ENTERED*/
   $sql = "SELECT suppliers.suppname, suppliers.currcode FROM suppliers WHERE supplierid='$SupplierID'";

   $ErrMsg = _('The supplier details for the selected supplier could not be retrieved because');
   $DbgMsg = _('The SQL that failed was');
   $SuppSelResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

   if (DB_num_rows($SuppSelResult) ==1){
		$myrow = DB_fetch_array($SuppSelResult);
		$SuppName = $myrow['suppname'];
		$CurrCode = $myrow['currcode'];
   } else {
		prnMsg( _('El C&oacute;digo del proveedor ') . ' ' . $SupplierID . ' ' . _('no existe en la base de datos') . '. ' . _('Debe ingresar un c&oacute;digo de proveedor valido'),'error');
		unset($SupplierID);
   }
}

if ((isset($_POST['AddRecord']) OR isset($_POST['UpdateRecord'])) AND isset($SupplierID)){	      /*Validate Inputs */
   $InputError = 0; /*Start assuming the best */
   if ($StockID=='' OR !isset($StockID)){
      $InputError=1;
      prnMsg( _('Introdizca el c&oacute;digo de producto'),'error');
   }
   if (! is_numeric($_POST['Price'])){
      $InputError =1;
      unset($_POST['Price']);
      prnMsg( _('El precio no es num&eacute;rico') . ' (' . _('debe introducir un n&uacute;mero') . ') - ' . _('no se han realizado los cambios en la base de datos'),'error');
   }
   if (! is_numeric($_POST['LeadTime'])){
      $InputError =1;
      unset($_POST['LeadTime']);
      prnMsg( _('El plazo no es n&uacute;merico') . ' (' . _('debe introducir un n&uacute;mero') . ') - ' . _('no se han realizado los cambios en la base de datos'),'error');
   }
   if (!is_numeric($_POST['ConversionFactor'])){
      $InputError =1;
      unset($_POST['ConversionFactor']);
      prnMsg( _('El factor de converci&oacute;n no es un numero') . ' (' . _('debe introducir un n&uacute;mero') . '). ' . _('El factor de conversi&oacute;n es el n&uacute;mero entre el el precio sera dividido para conseguir el precio por unidad de acuerdo a la unidad de medida') . '. <br>' . ' ' ,'error');
   }

	if($_POST['Price'] <= 0){
		$InputError =1;
		unset($_POST['ConversionFactor']);
		prnMsg( _('El precio debe ser mayor a 0 ') . '. <br>' . ' ' ,'error');
	}
   
   if ($InputError==0 AND isset($_POST['AddRecord'])){
	$sql = "Select *
			FROM purchdata
			WHERE purchdata.supplierno = '".$SupplierID."'
				AND purchdata.stockid = '".$StockID."'
				AND purchdata.pcurrcode = '".$_POST['CurrCode']."'";
	$result = DB_query($sql, $db);
   	if(DB_num_rows($result) > 0){
   		prnMsg( _('Ya se hab&iacute;a agregado con este proveedor y mismo tipo de moneda el producto'),'error');
   	}else{
   		$sql = "INSERT INTO purchdata (supplierno,
					stockid,
					price,
					effectivefrom,
					suppliersuom,
					conversionfactor,
					supplierdescription,
					leadtime,
					preferred,
      				pcurrcode)
			VALUES ('" . $SupplierID . "',
				'" . $StockID . "',
				" . $_POST['Price'] . ",
				'" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
				'" . $_POST['SuppliersUOM'] . "',
				" . $_POST['ConversionFactor'] . ",
				'" . $_POST['SupplierDescription'] . "',
				" . $_POST['LeadTime'] . ",
				" . $_POST['Preferred'] . ",
				'" .$_POST['CurrCode']."')";
   		
   		$ErrMsg = _('Los detalles de la compra del proveedor no se podr�a agregar a la base de datos porque');
   		$DbgMsg = _('El SQL utilizado es');
   		$AddResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
   		
   		prnMsg( _('Los datos de la orden de compra del proveedor se han agregado a la base de datos'),'success');
   	}


   }
   if ($InputError==0 AND isset($_POST['UpdateRecord'])){

      $sql = "UPDATE purchdata SET
			    price=" . $_POST['Price'] . ",
			    effectivefrom='" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
				suppliersuom='" . $_POST['SuppliersUOM'] . "',
				conversionfactor=" . $_POST['ConversionFactor'] . ",
				supplierdescription='" . $_POST['SupplierDescription'] . "',
				leadtime=" . $_POST['LeadTime'] . ",
				preferred=" . $_POST['Preferred'] . ",
				pcurrcode='".$_POST['CurrCode']."'
		WHERE purchdata.stockid='$StockID'
		AND purchdata.supplierno='$SupplierID'
		AND purchdata.effectivefrom='" . $_POST['WasEffectiveFrom'] . "'";


     $ErrMsg = _('Los detalles de la compra del proveedor no se pudo actualizar porque');
     $DbgMsg = _('El SQL utilizado es');

     $UpdResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

     prnMsg (_('Los datos de la orden de copra se han actualizado'),'success');

   }

   if ($InputError==0 AND (isset($_POST['UpdateRecord']) OR isset($_POST['AddRecord']))){
      /*update or insert took place and need to clear the form  */
      unset($SupplierID);
      unset($_POST['Price']);
      unset($CurrCode);
      unset($_POST['SuppliersUOM']);
      unset($_POST['EffectiveFrom']);
      unset($_POST['ConversionFactor']);
      unset($_POST['SupplierDescription']);
      unset($_POST['LeadTime']);
      unset($_POST['Preferred']);
   }
}


if (isset($_GET['Delete'])){

   $sql = "DELETE FROM purchdata 
   				WHERE purchdata.supplierno='$SupplierID' 
   				AND purchdata.stockid='$StockID'
   				AND purchdata.effectivefrom='" . $_GET['EffectiveFrom'] . "'";
   $ErrMsg =  _('The supplier purchasing details could not be deleted because');
   $DelResult=DB_query($sql,$db,$ErrMsg);

   prnMsg( _('El registro de los datos de compra de proveedor se han eliminado de manera satisfactoria'),'success');
   unset ($SupplierID);
}
//Tabla para el titulo de la pagina
echo "<table border=0 align='center'; width:0; background-color:#ffff;' border=0 width=100% nowrap>";
echo '<tr>
			<td align="center" colspan=2 class="fecha_titulo">
				<p align="center">
				<img src="images/compras_30.png" title="' . _('Datos de compra') . '" alt="">' . ' ' . $title . '<br>
			</td>
	  	  </tr>';
echo '</table>';
echo '<fieldset style="width:40%; margin:auto; color:#092304; border:2px solid #c9ccc9">'; 
if (isset($StockID)){
	$result = DB_query("SELECT stockmaster.description, 
								stockmaster.units, 
								stockmaster.mbflag 
						FROM stockmaster 
						WHERE stockmaster.stockid='$StockID'",$db);
	$myrow = DB_fetch_row($result);
echo '	<table align=center>
			<tr bgcolor="#eeeeee">';
	if (DB_num_rows($result)==1){
   		if ($myrow[2]=='D' OR $myrow[2]=='A' OR $myrow[2]=='K'){
			prnMsg( $StockID . ' - ' . $myrow[0] . '<td> ' . _('Este producto es ensamblado o pertenece a un paquete') . ' - ' . _('no es posible agregarlo en los datos de compra del proveedor') . '. ' . _('Ingreso de la informaci&oacute;n incorrecto'),'warn');
echo '												</td>';
			include('includes/footer.inc');
			exit;
		} else {
			echo '<td class="texto_normal2"><font color=#515457>' . $StockID . ' - ' . $myrow[0] . '   (' . _('En unidades de') . ' ' . $myrow[1] . ' )</font></td>';
echo '		</tr>';
   		}
	} else {
  		prnMsg( _('El c&oacute;digo del producto') . ' - ' . $StockID . ' ' . _('no existe en la base de datos'), 'warn');
	}
}

if (!isset($StockID)) {
	$StockID='';
}




echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post style="text-align:center" class="texto_lista">';
echo '		<tr>
				<td class="texto_lista">';
echo  				_('C&oacute;digo del producto:') . '<input type=text name="StockID" value="' . $StockID . '" size=21 maxlength=20>';
echo '			</td>
			</tr>';
echo '</table>';
echo '</fieldset>';

echo '<table align=center>
		<tr>
			<td>';
echo '    		<button type=submit name="ShowSupplierDetails" style="cursor:pointer; border:0; background-color:transparent;">
					<img src="images/buscar_25.png" title="MOSTRAR PROVEEDORES">
				</button>';
echo '    		<a href="' . $rootpath . '/SelectProduct.php?' . SID . '"><img src="images/agregar_pro30.png" height="25" title="SELECCIONAR PRODUCTO"></a>';
echo '		</td>
		</tr>
	</table>';

if (!isset($_GET['Edit'])){
   $sql = "SELECT  purchdata.supplierno,
					suppliers.suppname,
					purchdata.price,
					suppliers.currcode,
   					purchdata.pcurrcode,	
					purchdata.effectivefrom,
					purchdata.suppliersuom,
					purchdata.supplierdescription,
					purchdata.leadtime,
					purchdata.preferred
			FROM purchdata INNER JOIN suppliers
				ON purchdata.supplierno=suppliers.supplierid
			WHERE purchdata.stockid = '" . $StockID . "' 
			ORDER BY purchdata.effectivefrom DESC";

   $ErrMsg =  _('Los detalles de la orden de compra de proveedor para este producto no se pudieron recuperar por que:');
   $PurchDataResult = DB_query($sql, $db,$ErrMsg);


   if (DB_num_rows($PurchDataResult)==0){
      	prnMsg( _('No existen datos de la orden de compra del proveedor'),'info');
   } else {

     echo '<br><table cellspacing=0 border=1 align="center" bordercolor=lightgray cellpadding=3>';
     $TableHeader = '	<tr>
     						<th class="titulos_principales">' . _('Supplier') . '</th>
	     					<th class="titulos_principales">' . _('Precio') . '</th>
							<th class="titulos_principales">' . _('Moneda') . '</th>
							<th class="titulos_principales">' . _('Efectiva de') . '</th>
							<th class="titulos_principales">' . _('Unidad del Proveedor') . '</th>
							<th class="titulos_principales">' . _('Tiempo de Entrega') . '</th>
							<th class="titulos_principales">' . _('Proveedor Preferente') . '</th>
							<th class="titulos_principales" colspan=2></th>
						</tr>';

     echo $TableHeader;

     $CountPreferreds =0;
     $k=0; //row colour counter
	$flagerrormoenda = 0;
	$falginicialval = 1;
     while ($myrow=DB_fetch_array($PurchDataResult)) {
     	if(empty($myrow['pcurrcode'])){
     		echo '<tr bgcolor=red>';
     		$flagerrormoenda = 1;
     	}else{
     		if ($myrow['preferred']==1){
     			echo '<tr bgcolor=#eeeeee>';
     		} else {
     			echo '<tr bgcolor=#ffffff>';
     			$k++;
     		}
     	}
	
	if ($myrow['preferred']==1){
	  $DisplayPreferred= _('Yes');
	  $CountPreferreds++;
	} else {
	  $DisplayPreferred=_('No');
	}//

					printf("<td class='texto_normal2'>%s</td>
					        <td class='numero_celda'>%s</td>
							<td class='texto_normal2'>%s</td>
							<td class='numero_normal'>%s</td>
							<td class='texto_normal2'>%s</td>
							<td class='texto_normal2'>%s " . _('days') . "</td>
							<td class='numero_normal'>%s</td>
							<td class='numero_normal'><a href='%s?%s&StockID=%s&SupplierID=%s&Edit=1&EffectiveFrom=%s'><img src='images/lapiz_25.png' width=20 title='EDITAR'></a></td>
							<td class='numero_normal'><a href='%s?%s&StockID=%s&SupplierID=%s&Delete=1&EffectiveFrom=%s' onclick=\"return confirm('" . _('Are you sure you wish to delete this suppliers price?') . "');\"><img src='images/eliminar.png' width=20 title='ELIMINAR'></a></td>
						</tr>",
			$myrow['suppname'],
			number_format($myrow['price'],3),
			$myrow['pcurrcode'],
			ConvertSQLDate($myrow['effectivefrom']),
			$myrow['suppliersuom'],
			$myrow['leadtime'],
			$DisplayPreferred,
			$_SERVER['PHP_SELF'],
			SID,
			$StockID,
			$myrow['supplierno'],
			$myrow['effectivefrom'],
			$_SERVER['PHP_SELF'],
			SID,
			$StockID,
			$myrow['supplierno'],
			$myrow['effectivefrom']
			);

    } //end of while loop
    if($flagerrormoenda == 1){
    	echo "			<tr>
    						<td colspan=9 bgcolor=#ba565e>* Si el producto aparece seleccionado completamente en rojo, tiene configurado erronamente la moneda favor de verificarlo</td>
    					</tr>";
    }
    echo '			</table>';
    if ($CountPreferreds>1){
	      prnMsg( _('Hay ahora') . ' ' . $CountPreferreds . ' ' . _('proveedores preferentes establecidos para') . ' ' . $StockID . ' ' . _('debe editar los datos de compra de proveedores para hacer solo proveedor, el proveedor preferido'),'warn');
    } elseif($CountPreferreds==0){
	      prnMsg( _('No hay proveedores preferentes establecidos para') . ' ' . $StockID . ' ' . _('usted debe hacer un proveedor s&oacute;lo en el proveedor preferido'),'warn');
    }
  } // end of there are purchsing data rows to show
  echo '<br><hr width=70%>';
} /* Only show the existing purchasing data records if one is not being edited */


/*Show the input form for new supplier purchasing details */

if (isset($_GET['Edit'])){

	$sql = "SELECT purchdata.supplierno,
				suppliers.suppname,
				purchdata.price,
				purchdata.effectivefrom,
				suppliers.currcode,
				purchdata.pcurrcode,
				purchdata.suppliersuom,
				purchdata.supplierdescription,
				purchdata.leadtime,
				purchdata.conversionfactor,
				purchdata.preferred,
				purchdata.pcurrcode
		FROM purchdata INNER JOIN suppliers
			ON purchdata.supplierno=suppliers.supplierid
		WHERE purchdata.supplierno='$SupplierID'
		AND purchdata.stockid='$StockID'
		AND purchdata.effectivefrom='" . $_GET['EffectiveFrom'] . "'";

	$ErrMsg = _('The supplier purchasing details for the selected supplier and item could not be retrieved because');
	$EditResult = DB_query($sql, $db, $ErrMsg);

	$myrow = DB_fetch_array($EditResult);

	$SuppName = $myrow['suppname'];
	$_POST['Price'] = $myrow['price'];
	$_POST['EffectiveFrom']=ConvertSQLDate($myrow['effectivefrom']);
	$CurrCode = $myrow['pcurrcode'];
	$_POST['SuppliersUOM'] = $myrow['suppliersuom'];
	$_POST['SupplierDescription'] = $myrow['supplierdescription'];
	$_POST['LeadTime'] = $myrow['leadtime'];
	$_POST['ConversionFactor'] = $myrow['conversionfactor'];
	$_POST['Preferred'] = $myrow['preferred'];
	$_POST['currcode'] = $myrow['currcode'];
	$_POST['CurrCode'] = $myrow['pcurrcode'];

}

echo '	<table align="center" bgcolor="#eeeeee">';

if (!isset($SupplierID)) {
	$SupplierID = '';
}

if (isset($_GET['Edit'])){
    echo '	<tr>
    			<td class="texto_lista3">' . _('C&oacute;digo Proveedor') . ':</td>
    			<td><input type=hidden name="SupplierID" VALUE="' . $SupplierID . '">' . $SupplierID . ' - ' . $SuppName . '<input type=hidden name="WasEffectiveFrom" VALUE="' . $myrow['effectivefrom'] . '"></td>
    		</tr>';
} else {
    echo '	<tr>
    			<td class="texto_lista3">' . _('C&oacute;digo Proveedor') . ':</td>
    			<td><input type=TEXT name="SupplierID" maxlength=10 size=11 VALUE="' . $SupplierID . '">';
    if (!isset($SuppName) OR $SuppName=""){
	echo '<font size=1>' . '(' . _('Busqueda rapida') . ')';
    } else {
	echo $SuppName;
    }
    echo '		</td>
    		</tr>';
}

if (!isset($CurrCode)) {
	$CurrCode = '';
}

if (!isset($_POST['Price'])) {
	$_POST['Price'] = 0;
}
if (!isset($_POST['EffectiveFrom'])) {
	$_POST['EffectiveFrom'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['SuppliersUOM'])) {
	$_POST['SuppliersUOM'] = '';
}

if (!isset($_POST['SupplierDescription'])) {
	$_POST['SupplierDescription'] = '';
}

echo '		<tr>
				<td class="texto_lista3">' . _('Moneda') . ':</td>';
$sql2 = "SELECT currencies.currabrev,
				currencies.currency
		 FROM currencies";
$result2 = DB_query($sql2, $db);
echo "<td><select name='CurrCode'>";
while($myrow2 = DB_fetch_array($result2)){
	if($myrow2['currabrev'] == $CurrCode){
		echo "<option selected value='".$myrow2['currabrev']."'>".$myrow2['currency']."</option>";
	}else{
		echo "<option value='".$myrow2['currabrev']."'>".$myrow2['currency']."</option>";
	}
}
echo '</select>';
echo '			</td>
			</tr>';
//	<td><input type=hidden name="CurrCode" VALUE="' . $CurrCode . '">' . $CurrCode . '</td></tr>';
echo '		<tr>
				<td class="texto_lista3">' . _('Precio') . ' (' . _('Con el tipo de moneda del proveedor') . '):</td>
				<td><input type=TEXT class=number name="Price" maxlength=12 size=12 VALUE=' . $_POST['Price'] . '></td>
			</tr>';
echo '		<tr>
				<td class="texto_lista3">' . _('Fecha Actualizaci&oacute;n') . ':</td>
				<td><input type=TEXT class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="EffectiveFrom" maxlength=10 size=12 VALUE="' . $_POST['EffectiveFrom'] . '"></td></tr>';
echo '		<tr>
				<td class="texto_lista3">' . _('Unidad de Medida del Proveedor') . ':</td>
				<td><input type=TEXT name="SuppliersUOM" maxlength=50 size=51 VALUE="' . $_POST['SuppliersUOM'] . '"></td>
			</tr>';
if (!isset($_POST['ConversionFactor']) OR $_POST['ConversionFactor']==""){
   $_POST['ConversionFactor']=1;
}
echo '		<tr>
				<td class="texto_lista3">' . _('Factor de Conversi&oacute;n (a nuestro UOM)') . ':</td>
				<td><input type=TEXT class=number name="ConversionFactor" maxlength=12 size=12 VALUE=' . $_POST['ConversionFactor'] . '></td>
			</tr>';
echo '		<tr>
				<td class="texto_lista3">' . _('C&oacute;digo o Descripci&oacute;n de Proveedor') . ':</td>
				<td><input type=TEXT name="SupplierDescription" maxlength=50 size=51 VALUE="' . $_POST['SupplierDescription'] . '"></td>
			</tr>';
if (!isset($_POST['LeadTime']) OR $_POST['LeadTime']==""){
   $_POST['LeadTime']=1;
}
echo '		<tr>
				<td class="texto_lista3">' . _('Tiempo de Entrega') . ' (' . _('Determinadas en d&iacute;as despues de la orden de compra') . '):</td>
				<td><input type=TEXT class=number name="LeadTime" maxlength=10 size=11 VALUE=' . $_POST['LeadTime'] . '></td>
			</tr>';
echo '		<tr>
				<td class="texto_lista3">' . _('Proveedor Preferente') . ':</td>
				<td><select name="Preferred">';

if ($_POST['Preferred']==1){
	echo '<option selected VALUE=1>' . _('Yes');
	echo '<option VALUE=0>' . _('No');
} else {
	echo '<option VALUE=1>' . _('Yes');
	echo '<option selected VALUE=0>' . _('No');
}
echo '</select></td>
			</tr>
		</table>
	<div class="centre">';

if (isset($_GET['Edit'])){
   echo '	<button type=submit name="UpdateRecord" style="cursor:pointer; border:0; background-color:transparent;">
				<img src="images/bactualizar_25.png" title=' ._('ACTUALIZAR').'>
			</button>';
} else {
   echo '	<button type=submit name="AddRecord" style="cursor:pointer; border:0; background-color:transparent;">
				<img src="images/agregar2_25.png" title=' ._('AGREGAR').'>
			</button>';
}

echo '</div><br><hr width=70%><br>';

if (isset($_POST['SearchSupplier'])){

	If (isset($_POST['Keywords']) AND isset($_POST['SupplierCode'])) {
		$msg=_('Supplier Name keywords have been used in preference to the Supplier Code extract entered') . '.';
	}
	If ($_POST['Keywords']=="" AND $_POST['SupplierCode']=="") {
		$msg=_('At least one Supplier Name keyword OR an extract of a Supplier Code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

			$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.currcode,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3
					FROM suppliers WHERE suppliers.suppname " . LIKE . " '$SearchString'";

		} elseif (strlen($_POST['SupplierCode'])>0){
			$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.currcode,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3
				FROM suppliers
				WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'";
		}

		$ErrMsg = _('The suppliers matching the criteria entered could not be retrieved because');
		$DbgMsg =  _('The SQL to retrieve supplier details that failed was');
		$SuppliersResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	} //one of keywords or SupplierCode was more than a zero length string
} //end of if search

$msg = '';

if (strlen($msg)>1){
	 prnMsg($msg,'warn');
}

?>
<fieldset style="width:40%; margin:auto; color:#092304; border:2px solid #c9ccc9">
<table align=center cellpadding=0 colspan=4>
	<tr>
		<td class='texto_lista'><?php echo _('Proveedor'); ?><?php echo _(' ')?><?php echo _('Nombre'); ?>:</td>
		<td><input type="Text" name="Keywords" size=20 maxlength=25></td>
		<td></td>
		<td class='texto_lista'><?php echo _('Proveedor'); ?><?php echo _(' ')?><?php echo _('C&oacute;digo'); ?>:</td>
		<td><input type="Text" name="SupplierCode" size=15 maxlength=18></td>
	</tr>
</table>
</fieldset>
<div class='centre'>
	<button type=submit name="SearchSupplier" style='cursor:pointer; border:0; background-color:transparent;'>
		<img src='images/buscar_25.png' title='BUSCAR PROVEEDORES'>
	</button>
	<button type=submit action=RESET style='cursor:pointer; border:0; background-color:transparent;'>
		<img src='images/reiniciar_25.png' title='REINICIAR'>
	</button>
</div>


<?php

If (isset($SuppliersResult)) {

	echo '<br>';
	echo '<table cellspacing=0 border=1 align="center" bordercolor=lightgray cellpadding=3>';
	$TableHeader = '<tr>
						<th class="titulos_principales">' . _('C&oacute;digo') . '</th>
	               		<th class="titulos_principales">' . _('Nombre Proveedor') . '</th>
						<th class="titulos_principales">' . _('Moneda') . '</th>
						<th class="titulos_principales">' . _('Calle ') . '</th>
						<th class="titulos_principales">' . _('Colonia') . '</th>
						<th class="titulos_principales">' . _('Municipio/Estado') . '</th>
					</tr>';
	echo $TableHeader;

	$j = 1;

	while ($myrow=DB_fetch_array($SuppliersResult)) {

		printf("	<tr>
						<td class='numero_normal'><input type=submit style='font-size:10px;width:70px;background-color:#ba565e;color:#ffffff;border-width:medium;border-style:outset' name='SupplierID' VALUE='%s'</td>
						<td class='texto_normal2'>%s</td>
						<td class='texto_normal2'>%s</td>
						<td class='texto_normal2'>%s</td>
						<td class='texto_normal2'>%s</td>
						<td class='texto_normal2'>%s</td>
					</tr>",
			$myrow['supplierid'],
			$myrow['suppname'],
			$myrow['currcode'],
			$myrow['address1'],
			$myrow['address2'],
			$myrow['address3']
			);

		$j++;
		If ($j == 11){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if results to show

echo '<div class="centre">';
if (isset($StockLocation) and isset($StockID) AND strlen($StockID)!=0){
   echo '<br><a href="' . $rootpath . '/StockStatus.php?' . SID . '&StockID=' . $StockID . '">' . _('Mostrar status de inventario') . '</a>';
   echo '<br><a href="' . $rootpath . '/StockMovements.php?' . SID . '&StockID=' . $StockID . '&StockLocation=' . $StockLocation . '">' . _('Mostrar movimientos de inventario') . '</a>';
   echo '<br><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '&StockLocation=' . $StockLocation . '">' . _('Buscar ordenes de venta pendientes') . '</a>';
   echo '<br><a href="' . $rootpath . '/SelectCompletedOrder.php?' .SID . '&SelectedStockItem=' . $StockID . '">' . _('Busqueda de ordenes cerradas') . '</a>';
}

echo '</form></div>';
include('includes/footer.inc');
?>
