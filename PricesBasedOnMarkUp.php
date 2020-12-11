<?php

/*SECCION DE COMENTARIOS
22-NOV-2011 FCC
- SE AGREGO CHECKBOX EN CADA LISTA DE PRECIO, PARA SELECCIONAR SOLO LOS PRECIOS QUE SE REQUIEREN MODIFICAR.
*/


/* $Revision: 1.9 $ */
/*cambios
 3/FEB/2011  CAMBIO IDIOMA Y CAMBIOS DE ACTUALIZACION DE PRECIOS
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 11-MAR-2013
 CAMBIOS: 
	1. Se agrego combo multiseleccion y se valido cambio de precios en base de capacitacion.

 FIN DE CAMBIOS
 */
$PageSecurity=2;

include('includes/session.inc');
$title=_('Actualizacion de costos');
include('includes/header.inc');
$funcion=85;
include('includes/SecurityFunctions.inc');

echo '<br>' . _('Esta pagina actualiza precios a una lista de precios y moneda ya existente; de acuerdo a la categoria de productos, sobre un porcentaje de costo promedio o por ultima compra de proveedor');

$valores = "";
$xclase="";
foreach($_POST['xClase'] as $value)
   if ($value!=0)
   $valores .= $value.",";

if ($valores){
   $valores = substr($valores,0,strlen($valores)-1);

   $xclase = "AND stockmaster.idclassproduct in ($valores)";
}

echo "<form method='POST' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";


echo '<p><table>';
                        

$SQL = "SELECT distinct areas.areacode,areas.areadescription as description
	FROM areas
	 INNER  JOIN tags ON tags.areacode=areas.areacode
	  INNER  JOIN sec_unegsxuser ON sec_unegsxuser.tagref=tags.tagref
		AND sec_unegsxuser.userid='".$_SESSION['UserID']."'";
$PricesResultArea = DB_query($SQL,$db);

echo "<tr><td>" . _('Area de Negocio') . ":</td>
                <td><select name='AreaCode'>
		<option value='0'>TODAS LAS AREAS..</option>";
while ($myrow=DB_fetch_array($PricesResultArea)){
	if ($myrow['areacode']==$_POST['AreaCode']){
		echo "<option selected VALUE='". $myrow['areacode'] . "'>" . $myrow['areacode'] . ' - ' . $myrow['description'];
	} else {
		echo "<option VALUE='". $myrow['areacode'] . "'>"  . $myrow['areacode'] . ' - ' . $myrow['description'];
	}
}
echo '</select></td></tr>';




$SQL = 'SELECT currency, currabrev FROM currencies';
$result = DB_query($SQL,$db);

echo '<tr>
        <td>' . _('Seleccione la moneda de la lista de precios para actualizar') . ':</td>
                            <td><select name="CurrCode">';
if (!isset($_POST['CurrCode'])){
	$_POST['CurrCode']='MXN';
}

if (!isset($_POST['CurrCode'])){
	echo '<option selected value=0>' . _('Seleccione tipo de moneda');
}

while ($Currencies=DB_fetch_array($result)){
	if ($Currencies['currabrev']==$_POST['CurrCode']){
		echo '<option selected value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	} else {
		echo '<option value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	}
}

echo '</select></td></tr>';

if ($_SESSION['WeightedAverageCosting']==1){
	$CostingBasis = _('Costo Promedio');
} else {
	$CostingBasis = _('Ultimo Costo');
}
if (!isset($_POST['CostType'])){
	$_POST['CostType']='LastCost';
}


echo '<tr><td>' . _('Costo/Datos del proveedor preferente') . ':</td>
                <td><select name="CostType">';
if ($_POST['CostType']=='PreferredSupplier'){
     echo ' <option selected value="PreferredSupplier">' . _('Datos del costo de proveedor preferente') . '</option>
            <option value="StandardCost">' . $CostingBasis . '</option>
            <option value="LastCost">' . _('Ultimo Costo') . '</option>';
}elseif ($_POST['CostType']=='StandardCost'){
	 echo ' <option value="PreferredSupplier">' . _('Ultimo costo') . '</option>
            <option selected value="StandardCost">' . $CostingBasis . '</option>
	    <option value="LastCost">' . _('Ultimo Costo') . '</option>';
} else {
	echo ' <option value="PreferredSupplier">' . _('Costo de proveedor preferente') . '</option>
            <option value="StandardCost">' . $CostingBasis . '</option>
            <option selected value="LastCost">' . _('Ultimo Costo') . '</option>';
}
echo '</select></td></tr>';

//DB_data_seek($PricesResult,0);

if ($_POST['CostType']=='OtherPriceList'){
     echo '<tr><td>' . _('Seleccione lista de precios') . ':</td>
                            <td><select name="BasePriceList">';

	if (!isset($_POST['BasePriceList']) OR $_POST['BasePriceList']=='0'){
		echo '<option selected VALUE=0>' . _('Seleccione Lista de Precios');
	}
	while ($PriceLists=DB_fetch_array($PricesResult)){
		if ($_POST['BasePriceList']==$PriceLists['typeabbrev']){
			echo "<option selected value='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'] . '</option>';
		} else {
			echo "<option value='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'] . '</option>';
		}
	}
	echo '</select></td></tr>';
}

echo '<tr><td>' . _('Categoria de productos de inicio') . ':</td>
                <td><select name="StkCatFrom">';

#$sql = 'SELECT categoryid, categorydescription FROM stockcategory';
$sql='SELECT sto.categoryid, categorydescription FROM stockcategory sto, sec_stockcategory sec WHERE sto.categoryid=sec.categoryid AND userid="'.$_SESSION['UserID'].'" ORDER BY categorydescription';
$ErrMsg = _('Las categorias de inventario no se pudieron recuperar');
$DbgMsg = _('El SQL que fallo es');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

while ($myrow=DB_fetch_array($result)){
	if ($myrow['categoryid']==$_POST['StkCatFrom']){
		echo "<option selected VALUE='". $myrow['categoryid'] . "'>" . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	} else {
		echo "<option VALUE='". $myrow['categoryid'] . "'>"  . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	}
}
echo '</select></td></tr>';

DB_data_seek($result,0);

echo '<tr><td>' . _('Categoria de productos de fin') . ':</td>
                <td><select name="StkCatTo">';

while ($myrow=DB_fetch_array($result)){
	if ($myrow['categoryid']==$_POST['StkCatTo']){
		echo "<option selected VALUE='". $myrow['categoryid'] . "'>" . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	} else {
		echo "<option VALUE='". $myrow['categoryid'] . "'>"  . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	}
}
echo '</select></td></tr>';

/************************************/
/* SELECCION DEL CLASES DE PRODUCTOS */
echo '<tr><td>' . _('X Clase') . ':' . "</td>
	<td><select tabindex='4' name='xClase[]' size=5 multiple>";

	$sql="SELECT idclassproduct, classdescription FROM classproduct  where idclassproduct <> '000'";

	$result=DB_query($sql,$db);
	
	if ( in_array(0,$_POST['xClase']) || count($_POST['xClase'])==0)
		echo "<option selected  value='0'>Todas las clases...</option>";
	else
		echo "<option  value='0'>Todas las clases...</option>";
	
	while ($myrow=DB_fetch_array($result)){
		if (in_array($myrow['idclassproduct'],$_POST['xClase'])){
			echo "<option selected value='" . $myrow["idclassproduct"] . "'>" . $myrow['classdescription'];
		} else {
			echo "<option value='" . $myrow['idclassproduct'] . "'>" . $myrow['classdescription'];
		}
	}
echo '</select></td></tr>';
/************************************/

/************************************/
	/* SELECCION DE Marca */
	echo '<tr><td>' . _('X Marca') . ':' . "</td>
		<td><select tabindex='4' name='xMarca'>";
		$sql='SELECT distinct manufacturer
			from stockmaster where manufacturer<>""
			ORDER BY manufacturer';
		$result=DB_query($sql,$db);
		echo "<option selected value='0'>Todas las marcas...</option>";
		while ($myrow=DB_fetch_array($result)){
			if ($myrow['manufacturer'] == $_POST['xMarca']){
				echo "<option selected value='" . $myrow["manufacturer"] . "'>" . $myrow['manufacturer'];
			} else {
				echo "<option value='" . $myrow['manufacturer'] . "'>" . $myrow['manufacturer'];
			}
		}
		echo '</select></td>
	</tr>';
/************************************/


if (!isset($_POST['RoundingFactor'])){
	$_POST['RoundingFactor']=1;
}

echo '<tr><td>' . _('Factor de redondeo') . ':</td>
                <td><input type=text class=number name="RoundingFactor" size="6" maxlength="6" value=' . $_POST['RoundingFactor'] . '></td></tr>';

if (!isset($_POST['IncreasePercent'])){
	$_POST['IncreasePercent']=0;
}

$SQL = 'SELECT sales_type, typeabbrev FROM salestypes order by orden, sales_type asc ';
$PricesResult = DB_query($SQL,$db);

echo '<tr>
                            <td colspan=2 style=text-align:center;><b>' . _('Listas de precios Aumento Porcentual (positivo) o decremento (negativo) de 0 a 100') .'</b></td>';
echo '<table border=1>';
echo '<tr align=center style="background-color:#f2fcbd;text-align:center;">';
while ($PriceLists=DB_fetch_array($PricesResult)){
	echo "<td style='text-align:center;' title='".$PriceLists['sales_type']."'>";
	if ($_POST['lista_' . $PriceLists['typeabbrev']] == 1){
		echo "<input type='checkbox' name='lista_" . $PriceLists['typeabbrev'] . "' value='1' checked>";	
	}else{
		echo "<input type='checkbox' name='lista_" . $PriceLists['typeabbrev'] . "' value='1'>";	
	}
	echo $PriceLists['typeabbrev'];
	echo "</td>";
}
DB_data_seek($PricesResult,0);
echo '</tr>';
echo '<tr>';
while ($PriceLists=DB_fetch_array($PricesResult)){
	if (!isset($_POST[$PriceLists['typeabbrev']]))
	{
		$_POST[$PriceLists['typeabbrev']]=0;
	}
	echo "<td><input type=text name='".$PriceLists['typeabbrev']."' class=number size=4 maxlength=4 VALUE=" . $_POST[$PriceLists['typeabbrev']] . "></td>";
}
DB_data_seek($PricesResult,0);
echo '</tr>';


echo '</table>';
echo '</td></tr>';
echo "</tr></table>";

/*
 echo '<tr><td>' . _('Aumento Porcentual (positivo) o decremento (negativo)') . "</td>
                <td><input type=text name='IncreasePercent' class=number size=4 maxlength=4 VALUE=" . $_POST['IncreasePercent'] . "></td></tr></table>";
*/

echo "<p><div class='centre'><input type=submit name='UpdatePrices' VALUE='" . _('Actualizar precios') . '\'  onclick="return confirm(\'' . _('Esta seguro que desea actualizar todos los precios de acuerdo a los criterios seleccionados?') . '\');"></div>';

echo '</form>';

$InputError =0; //assume the best
/*if (isset($_POST['UpdatePrices']) AND $_POST['PriceList']=='0'){
	prnMsg(_('No ha seleccionado la lista de precios, la actualizacion no se llevara a cabo'),'error');
	$InputError =1;
}*/
if (isset($_POST['UpdatePrices']) AND $_POST['CurrCode']=='0'){
	prnMsg(_('No ha seleccionado la moneda, la actualizacion no se llevara a cabo'),'error');
	$InputError =1;
}

if (isset($_POST['UpdatePrices']) AND $_POST['StkCatTo']<$_POST['StkCatFrom']){
	prnMsg(_('No ha seleccionado la categoria de productos de inicio, la actualizacion no se llevara a cabo'),'error');
	$InputError =1;
}
if (isset($_POST['UpdatePrices']) AND $_POST['CostType']=='OtherPriceList' AND $_POST['BasePriceList']=='0'){
	echo '<br>Base price list selected: ' .$_POST['BasePriceList'];
	prnMsg(_('Cuando se va a actualizar los precios basada en otra lista de precios - la lista de precios también deberán ser seleccionados. La actuyalizacion no se llevara a cabo'),'error');
	$InputError =1;
}
if (isset($_POST['UpdatePrices']) AND $_POST['CostType']=='OtherPriceList' AND $_POST['BasePriceList']==$_POST['PriceList']){
	prnMsg(_('Cuando se va a actualizar los precios basada en otra lista de precios - la lista de precios de otros no puede ser la misma que la lista de precios se utiliza para el cálculo. La actualizacion no se llevara a cabo' ),'error');
	$InputError =1;
}

if (isset($_POST['UpdatePrices']) AND $InputError==0) {
	if ($_POST['xMarca']=='0'){
		$lmarca = "Todas las marcas";
	}else{
		$lmarca =$_POST['xMarca'];
	}
	echo '<br>' . _('Esta usando una lista de precios y tipo de las ventas de') .' : ' . $_POST['PriceList'];
	echo '<br>' . _('Actualizando los precios con moneda en ') . ' : ' . $_POST['CurrCode'];
	echo '<br>' . _('del area de negocio ') . ' : ' . $_POST['AreaCode'];
	echo '<br>' . _('de la categoria') . ' : ' . $_POST['StkCatFrom'] . ' ' . _('a') . ' ' . $_POST['StkCatTo'];
	echo '<br>' . _('de la marca') . ' : ' . $lmarca;
	echo '<br>' . _('y aplicando un porcentaje') . ' : ' . $_POST['IncreasePercent'];
	//echo '<br>' . _('against') . ' ';

	if ($_POST['CostType']=='PreferredSupplier'){
		echo _('Preferred Supplier Cost Data');
	} elseif ($_POST['CostType']=='OtherPriceList') {
		echo _('Price List')  . ' ' . $_POST['BasePriceList'];
	} else {
		echo $CostingBasis;
	}

	if ($_POST['PriceList']=='0'){
		echo '<br>' . _('La lista de precios y tipo de ventas a actualizar debe estar seleccionada');
		include ('includes/footer.inc');
		exit;
	}
	if ($_POST['CurrCode']=='0'){
		echo '<br>' . _('La moneda debe estar seleccionada');
		include ('includes/footer.inc');
		exit;
	}
	// cambiar por costo promedio y ultimo costo
	if ($_POST['AreaCode'] == '0'){
		$legalid=0;
	}else{
		$sql = "SELECT tags.legalid
		FROM areas  inner join tags on tags.areacode=areas.areacode
		WHERE tags.areacode='" . $_POST['AreaCode'] . "'";
		$ErrMsg = _('no se puede recuperar la razon social');
		$BasePriceResult = DB_query($sql,$db,$ErrMsg);
		if (DB_num_rows($BasePriceResult)==0){
			prnMsg(_('No se puede recuperar la razon social'),'warn');
			$legalid=0;
		} else {
			$BasePriceRow = DB_fetch_row($BasePriceResult);
			$legalid = $BasePriceRow[0];
		}	
	}
	
	$sql = "SELECT stockid,
			 materialcost+labourcost+overheadcost AS cost
			FROM stockmaster
			WHERE categoryid>='" . $_POST['StkCatFrom'] . "'
			AND categoryid <='" . $_POST['StkCatTo'] . "'
			$xclase
			";
	if ($_POST['xMarca'] != '0'){
		$sql = $sql . " and manufacturer = '" . $_POST['xMarca'] . "'";
	}
	
	//echo "<br><pre>" . $sql;
	$PartsResult = DB_query($sql,$db);
	//$IncrementPercentage = $_POST['IncreasePercent']/100;

	$CurrenciesResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_POST['CurrCode'] . "'",$db);
	$CurrencyRow = DB_fetch_row($CurrenciesResult);
	$CurrencyRate = $CurrencyRow[0];

	while ($myrow=DB_fetch_array($PartsResult)){

//Figure out the cost to use
		$bandera=0;
		//entra while para todos las listas de precios
		DB_data_seek($PricesResult,0);
		while ($PriceLists=DB_fetch_array($PricesResult)){
			$_POST['PriceList']=$PriceLists['typeabbrev'];
			if ($_POST['lista_' . $PriceLists['typeabbrev']] == 1){
				$IncrementPercentage=$_POST[$PriceLists['typeabbrev']]/100;
				if ($IncrementPercentage>0){
					if ($_POST['CostType']=='PreferredSupplier'){
						$sql = "SELECT purchdata.price/purchdata.conversionfactor/currencies.rate AS cost
									FROM purchdata INNER JOIN suppliers
										ON purchdata.supplierno=suppliers.supplierid
										INNER JOIN currencies
										ON suppliers.currcode=currencies.currabrev
									WHERE purchdata.preferred=1 AND purchdata.stockid='" . $myrow['stockid'] ."'";
						$ErrMsg = _('Could not get the supplier purchasing information for a preferred supplier for the item') . ' ' . $myrow['stockid'];
						$PrefSuppResult = DB_query($sql,$db,$ErrMsg);
						if (DB_num_rows($PrefSuppResult)==0){
							prnMsg(_('There is no preferred supplier data for the item') . ' ' . $myrow['stockid'] . ' ' . _('prices will not be updated for this item'),'warn');
							$Cost = 0;
						} elseif(DB_num_rows($PrefSuppResult)>1) {
							prnMsg(_('There is more than a single preferred supplier data for the item') . ' ' . $myrow['stockid'] . ' ' . _('prices will not be updated for this item'),'warn');
							$Cost = 0;
						} else {
							$PrefSuppRow = DB_fetch_row($PrefSuppResult);
							$Cost = $PrefSuppRow[0];
						}
					} elseif ($_POST['CostType']=='OtherPriceList'){
						if ($_POST['AreaCode'] == '0'){
							$sql = "SELECT max(price) as price
								FROM prices
								WHERE typeabbrev= '" . $_POST['BasePriceList'] . "'
								AND currabrev='" . $_POST['CurrCode'] . "'
								AND stockid='" . $myrow['stockid'] . "'";
						}else{
							$sql = "SELECT price
								FROM prices
								WHERE typeabbrev= '" . $_POST['BasePriceList'] . "'
								AND currabrev='" . $_POST['CurrCode'] . "'
								AND areacode='" . $_POST['AreaCode'] . "'
								AND stockid='" . $myrow['stockid'] . "'";
						}	
						$ErrMsg = _('No se pudo extraer la lista de precios') . ' ' . $myrow['stockid'] . _('de la lista de precios') . ' ' . $_POST['BasePriceList'];
						$BasePriceResult = DB_query($sql,$db,$ErrMsg);
						if (DB_num_rows($BasePriceResult)==0){
							prnMsg(_('No se encuentra definido el precio para ') . ' ' . $myrow['stockid'] . ' ' . _('. El precio de este producto no se realizo.'),'warn');
							$Cost = 0;
						} else {
							$BasePriceRow = DB_fetch_row($BasePriceResult);
							$Cost = $BasePriceRow[0];
						}
					} elseif ($_POST['CostType']=='LastCost'){
						if ($_SESSION['TypeCostStock']!=1){
							//$Cost = $myrow['cost'];
							if ($_POST['AreaCode'] == '0'){
								$sql = "SELECT max(lastcost) as lastcost
									FROM stockcostsxlegal
									WHERE stockid='" . $myrow['stockid'] . "'";
							}else{
								$sql = "SELECT lastcost FROM
									stockcostsxlegal
									WHERE legalid= '" . $legalid . "'
									AND stockid='" . $myrow['stockid'] . "'";
							}
							$ErrMsg = _('No se pudo extraer el costo promedio para ') . ' ' . $myrow['stockid'] . _('de la razon social') . ' ' . $legalid;
							$BasePriceResult = DB_query($sql,$db,$ErrMsg);
							if (DB_num_rows($BasePriceResult)==0){
								prnMsg(_('No existe precio promedio para ') . ' ' . $myrow['stockid'] . ' ' . _('El precio no sera actualizado'),'warn');
								$Cost = 0;
							} else {
								$BasePriceRow = DB_fetch_row($BasePriceResult);
								$Cost = $BasePriceRow[0];
							}
							
							
							
						}else{
							//$Cost = $myrow['cost'];
							$bandera=1;
						}
				
						if ($Cost<=0){
							prnMsg(_('El producto') . ' ' . $myrow['stockid']. _(' es de cero, por lo tanto el precio es de cero'),'warn');
						}
					
					}else { //Must be using standard/weighted average costs
						if ($_SESSION['TypeCostStock']!=1){
							//$Cost = $myrow['cost'];
							if ($_POST['AreaCode'] == '0'){
								$sql = "SELECT max(avgcost) as avgcost
									FROM stockcostsxlegal
									WHERE stockid='" . $myrow['stockid'] . "'";
							}else{
								$sql = "SELECT avgcost FROM
									stockcostsxlegal
									WHERE legalid= '" . $legalid . "'
									AND stockid='" . $myrow['stockid'] . "'";
							}
							$ErrMsg = _('No se pudo extraer el costo promedio para ') . ' ' . $myrow['stockid'] . _('de la razon social') . ' ' . $legalid;
							$BasePriceResult = DB_query($sql,$db,$ErrMsg);
							if (DB_num_rows($BasePriceResult)==0){
								prnMsg(_('No existe precio promedio para ') . ' ' . $myrow['stockid'] . ' ' . _('El precio no sera actualizado'),'warn');
								$Cost = 0;
							} else {
								$BasePriceRow = DB_fetch_row($BasePriceResult);
								$Cost = $BasePriceRow[0];
							}
							
							
							
						}else{
							//$Cost = $myrow['cost'];
							$bandera=1;
						}
				
						if ($Cost<=0){
							prnMsg(_('El producto') . ' ' . $myrow['stockid']. _(' es de cero, por lo tanto el precio es de cero'),'warn');
						}
					}
			
					if ($_POST['CostType']!='OtherPriceList'){
						$RoundedPrice = round(($Cost * (1+ $IncrementPercentage) * $CurrencyRate+($_POST['RoundingFactor']/2))/$_POST['RoundingFactor']) * $_POST['RoundingFactor'];
						if ($RoundedPrice <=0){
							$RoundedPrice = $_POST['RoundingFactor'];
						}
					} else {
						$RoundedPrice = round(($Cost * (1+ $IncrementPercentage)+($_POST['RoundingFactor']/2))/$_POST['RoundingFactor']) * $_POST['RoundingFactor'];
						if ($RoundedPrice <=0){
							$RoundedPrice = $_POST['RoundingFactor'];
						}
					}
			
					if ($Cost > 0 and $bandera==0) {
						$sql2 = "SELECT price
							FROM prices
							WHERE typeabbrev= '" . $_POST['PriceList'] . "'
								AND currabrev='" . $_POST['CurrCode'] . "'";
							if ($_POST['AreaCode'] !='0'){
								$sql2 = $sql2 . " AND areacode='" . $_POST['AreaCode'] . "'";
							}
							$sql2 = $sql2 . " AND stockid='" . $myrow['stockid'] . "'";
							//echo "<br>" . $sql2;
						$CurrentPriceResult = DB_query($sql2,$db);
						if (DB_num_rows($CurrentPriceResult)>0){
							$sql = "UPDATE prices SET price=" . $RoundedPrice . "
									WHERE typeabbrev='" . $_POST['PriceList'] . "'
									AND currabrev='" . $_POST['CurrCode'] . "'";
									if ($_POST['AreaCode'] !='0'){
										$sql = $sql . " AND areacode='" . $_POST['AreaCode'] . "'";
									}
								$sql = $sql . " AND stockid='" . $myrow['stockid'] . "'";
								
							$ErrMsg =_('Error updating prices for') . ' ' . $myrow['stockid'] . ' ' . _('because');
							//echo "<br>" . $sql;
							$result = DB_query($sql,$db,$ErrMsg);
							prnMsg(_('Actualizando precios para ') . ' ' . $myrow['stockid'] . ' ' . _(' con precio de ') . ' ' . $RoundedPrice,'info');
						} else {
							if ($_POST['AreaCode'] !='0'){
								$sql = "INSERT INTO prices (stockid,typeabbrev,currabrev,areacode,
												price)
												VALUES ('" . $myrow['stockid'] . "',
														'" . $_POST['PriceList'] . "',
														'" . $_POST['CurrCode'] . "',
														'" . $_POST['AreaCode'] . "',
														" . $RoundedPrice . ")";
							}else{
								$sql = "INSERT INTO prices (stockid,typeabbrev,currabrev,areacode,price)
								SELECT '" . $myrow['stockid'] . "','" . $_POST['PriceList'] . "','"
									. $_POST['CurrCode'] . "',areacode," . $RoundedPrice . "
								FROM areas";
							}
							//echo "<br>" . $sql;
							$ErrMsg =_('Error al insertar el precio para ') . ' ' . $myrow['stockid'] . ' ' . _(' por que');
							$result = DB_query($sql,$db,$ErrMsg);
							prnMsg(_('Insertando nuevo precio para ') . ' ' . $myrow['stockid'] . ' ' . _(' con precio de ') . ' ' . $RoundedPrice,'info');
							
						} //end if update or insert
						//echo "sql:".$sql;
					}// end if cost > 0
				}
			}
		}// fin de lista de precios
		//echo "aqui otra";
	}//end while loop around items in the category
}
include('includes/footer.inc');
?>