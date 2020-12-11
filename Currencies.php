<?php
/* $Revision: 1.20 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');*/
$PageSecurity = 9;

include('includes/session.inc');
$title = _('Mantenimiento de Tipo de Moneda');


include('includes/header.inc');
$funcion=95;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');



if (isset($_GET['SelectedCurrency'])){
    $SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])){
    $SelectedCurrency = $_POST['SelectedCurrency'];
}

$ForceConfigReload = true;
include('includes/GetConfig.php');

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;

    /* actions to take once the user has clicked the submit button
    ie the page has called itself with some user input */

    //first off validate inputs are sensible
	$i=1;

	$sql="SELECT count(currabrev)
			FROM currencies WHERE currabrev='".$_POST['Abbreviation']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and !isset($SelectedCurrency)) {
		$InputError = 1;
		prnMsg( _('The currency already exists in the database'),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
	}
    if (strlen($_POST['Abbreviation']) > 3) {
        $InputError = 1;
        //prnMsg(_('The currency abbreviation must be 3 characters or less long and for automated currency updates to work correctly be one of the ISO4217 currency codes'),'error');
        prnMsg(_('La abreviacón de la moneda debe ser de maximo 3 caracteres de longitud y para códigos que se ocupen actualizar con respecto a lo establecido en el ISO4217 '),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
    }
	if (!is_numeric($_POST['ExchangeRate'])){
        $InputError = 1;
       //prnMsg(_('The exchange rate must be numeric'),'error');
       prnMsg(_('El tipo de cambio debe ser numerico'),'error');
		$Errors[$i] = 'ExchangeRate';
		$i++;
    }
    if (strlen($_POST['CurrencyName']) > 20) {
        $InputError = 1;
        //prnMsg(_('The currency name must be 20 characters or less long'),'error');
		prnMsg(_('El nombre de la moneda debe ser maximo de 20 caracteres'),'error');
        $Errors[$i] = 'CurrencyName';
		$i++;
    }
    if (strlen($_POST['Country']) > 50) {
        $InputError = 1;
        //prnMsg(_('The currency country must be 50 characters or less long'),'error');
		prnMsg(_('El pais de la moneda debe ser de maximo 50 caracteres'),'error');
        $Errors[$i] = 'Country';
		$i++;
    }
    if (strlen($_POST['HundredsName']) > 15) {
        $InputError = 1;
        //prnMsg(_('The hundredths name must be 15 characters or less long'),'error');
		prnMsg(_('El nombre de la fracción debe ser de maximo 15 caracteres'),'error');
        $Errors[$i] = 'HundredsName';
		$i++;
    }
    if (($FunctionalCurrency != '') and (isset($SelectedCurrency) and $SelectedCurrency==$FunctionalCurrency)){
        $InputError = 1;
        //prnMsg(_('The functional currency cannot be modified or deleted'),'error');
        prnMsg(_('La moneda Funcional no debe ser modificada o eliminada'),'error');
    }
    if (strstr($_POST['Abbreviation'],"'") OR strstr($_POST['Abbreviation'],'+') OR strstr($_POST['Abbreviation'],"\"") OR strstr($_POST['Abbreviation'],'&') OR strstr($_POST['Abbreviation'],' ') OR strstr($_POST['Abbreviation'],"\\") OR strstr($_POST['Abbreviation'],'.') OR strstr($_POST['Abbreviation'],'"')) {
		$InputError = 1;
		//prnMsg( _('The currency code cannot contain any of the following characters') . " . - ' & + \" " . _('or a space'),'error');
        prnMsg( _('El código de la moneda no puede contener ninguno de los siquientes caracteres') . " . - ' & + \" " . _('o un espacio'),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
	}

    if (isset($SelectedCurrency) AND $InputError !=1) {

        /*SelectedCurrency could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
        $sql = "UPDATE currencies SET
					currency='" . $_POST['CurrencyName'] . "',
					country='". $_POST['Country']. "',
					hundredsname='" . $_POST['HundredsName'] . "',
					rate=" .$_POST['ExchangeRate'] . "
					WHERE currabrev = '" . $SelectedCurrency . "'";

        //$msg = _('The currency definition record has been updated');
        $msg = _('EL registro de la definicion de la moneda ha sido actualizado');
    } else if ($InputError !=1) {

    /*Selected currencies is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */
    	$sql = "INSERT INTO currencies (currency,
    					currabrev,
						country,
						hundredsname,
						rate)
				VALUES ('" . $_POST['CurrencyName'] . "',
					'" . $_POST['Abbreviation'] . "',
					'" . $_POST['Country'] . "',
					'" . $_POST['HundredsName'] .  "',
					" . $_POST['ExchangeRate'] . ")";

    	//$msg = _('The currency definition record has been added');
        $msg = _('EL registro de la definición de la moneda ha sido agregado');
    }
    //run the SQL from either of the above possibilites
    $result = DB_query($sql,$db);
    if ($InputError!=1){
    	prnMsg( $msg,'success');
    }
    unset($SelectedCurrency);
    unset($_POST['CurrencyName']);
    unset($_POST['Country']);
    unset($_POST['HundredsName']);
    unset($_POST['ExchangeRate']);
    unset($_POST['Abbreviation']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

    $sql= "SELECT COUNT(*) FROM debtorsmaster WHERE debtorsmaster.currcode = '" . $SelectedCurrency . "'";
    $result = DB_query($sql,$db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0] > 0)
    {
        /*prnMsg(_('Cannot delete this currency because customer accounts have been created referring to this currency') .
         	'<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer accounts that refer to this currency'),'warn');*/
        prnMsg(_('No debe ser eliminada esta moneda, por que hay informacion creada que hace referencia a dicha moneda.') .
            '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('cuentas de clientes que hacen referencia a dicha moneda.'),'warn');
    } else {
        $sql= "SELECT COUNT(*) FROM suppliers WHERE suppliers.currcode = '$SelectedCurrency'";
        $result = DB_query($sql,$db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0] > 0)
        {
            /*prnMsg(_('Cannot delete this currency because supplier accounts have been created referring to this currency')
             . '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('supplier accounts that refer to this currency'),'warn');*/
             prnMsg(_('No debe ser elminada esta moneda por que hay proveedores que hacen referencia a dicha moneda.')
             . '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('cuentas de proveedores que hacen referencia a dicha moneda.'),'warn');
        } else {
            $sql= "SELECT COUNT(*) FROM banktrans WHERE banktrans.currcode = '" . $SelectedCurrency . "'";
            $result = DB_query($sql,$db);
            $myrow = DB_fetch_row($result);
            if ($myrow[0] > 0){
                /*prnMsg(_('Cannot delete this currency because there are bank transactions that use this currency') .
                '<br>' . ' ' . _('There are') . ' ' . $myrow[0] . ' ' . _('bank transactions that refer to this currency'),'warn');*/
                prnMsg(_('No debe ser eliminada esta moneda por que tiene transacciones bancarias realacionadas a la moneda.') .
                '<br>' . ' ' . _('Hay') . ' ' . $myrow[0] . ' ' . _('transacciones bancarias relacionadas a la moneda'),'warn');
            } elseif ($FunctionalCurrency==$SelectedCurrency){
                //prnMsg(_('Cannot delete this currency because it is the functional currency of the company'),'warn');
                prnMsg(_('No se debe eliminar esta moneda por que es la moneda principal de la compañia'),'warn');
            } else {
                //only delete if used in neither customer or supplier, comp prefs, bank trans accounts
                $sql="DELETE FROM currencies WHERE currabrev='" . $SelectedCurrency . "'";
                $result = DB_query($sql,$db);
                //prnMsg(_('The currency definition record has been deleted'),'success');
                prnMsg(_('El registro de la definición de la moneda ha sido eliminado'),'success');
            }
        }
    }
    //end if currency used in customer or supplier accounts
}

if (!isset($SelectedCurrency)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCurrency will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

    $sql = 'SELECT currency, currabrev, country, hundredsname, rate FROM currencies';
    $result = DB_query($sql, $db);

    echo '<table id="idTableCurrenvies" class="tableHeaderVerde" border=1>';
    echo '<tr><th></th>
    		<th>' . _('ISO4217 Code') . '</th>
    		<th>' . _('Moneda') . '</th>
			<th>' . _('Pais') . '</th>
			<th>' . _('Fracción de la Moneda') . '</th>
			<th>' . _('Tipo de Cambio') . '</th>
			<th colspan="4">' . _('Tipo de Cambio Previo - ECB') .'</th>
			</tr>';

    $k=0; //row colour counter
    /*Get published currency rates from Eurpoean Central Bank */
    $CurrencyRatesArray = GetECBCurrencyRates();

    while ($myrow = DB_fetch_row($result)) {
        if ($myrow[1]==$FunctionalCurrency){
            //echo '<tr bgcolor=#FFbbbb>';
            echo '<tr>';
        } elseif ($k==1){
            echo '<tr class="EvenTableRows">';
            $k=0;
        } else {
            echo  '<tr class="OddTableRows">';;
            $k++;
        }
        // Lets show the country flag
        $ImageFile = 'images/flags/' . strtoupper($myrow[1]) . '.gif';

		if(!file_exists($ImageFile)){
			$ImageFile =  'images/flags/blank.gif';
		}

        if ($myrow[1]!=$FunctionalCurrency){
            printf("<td><img src=\"%s\"></td>
            		<td>%s</td>
	    			<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class=number>%s</td>
					<td class=number>%s</td>
					<td><a href=\"%s&SelectedCurrency=%s\">%s</a></td>
					<td><a href=\"%s&SelectedCurrency=%s&delete=1\">%s</a></td>
					<td><a href=\"%s/ExchangeRateTrend.php?%s\">" . _('Grafica') . "</a></td>
					</tr>",
					$ImageFile,
					$myrow[1],
					$myrow[0],
					$myrow[2],
					$myrow[3],
					number_format($myrow[4],5),
					number_format(GetCurrencyRate($myrow[1],$CurrencyRatesArray),5),
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow[1],
					_('Editar'),
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow[1],
					_('Eliminar'),
					$rootpath,
					SID . '&CurrencyToShow=' . $myrow[1]);
        } else {
            printf("<td><img src=\"%s\"></td>
            		<td>%s</td>
	    			<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class=number>%s</td>
					<td colspan=4>%s</td>
					</tr>",
					$ImageFile,
					$myrow[1],
					$myrow[0],
					$myrow[2],
					$myrow[3],
					1,
					_('Moneda Funcional'));
        }

    } //END WHILE LIST LOOP
    echo '</table><br>';
} //end of ifs and buts!


if (isset($SelectedCurrency)) {
   // echo '<div class="centre"><a href=' .$_SERVER['PHP_SELF']  . '?' . SID. '>'._('Show all currency definitions').'</a></div>';
    echo '<div class="centre"><a href=' .$_SERVER['PHP_SELF']  . '?' . SID. '>'._('Mostrar todas las definiciones existentes').'</a></div>';
}

echo '<br>';

if (!isset($_GET['delete'])) {

    echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

    if (isset($SelectedCurrency) AND $SelectedCurrency!='') {
        //editing an existing payment terms

        $sql = "SELECT currency,
				currabrev,
				country,
				hundredsname,
				rate
				FROM currencies
				WHERE currabrev='" . $SelectedCurrency . "'";

        //$ErrMsg = _('An error occurred in retrieving the currency information');
        $ErrMsg = _('Ha ocurrido un error en la información');
		$result = DB_query($sql, $db, $ErrMsg);

        $myrow = DB_fetch_array($result);

        $_POST['Abbreviation'] = $myrow['currabrev'];
        $_POST['CurrencyName']  = $myrow['currency'];
        $_POST['Country']  = $myrow['country'];
        $_POST['HundredsName']  = $myrow['hundredsname'];
        $_POST['ExchangeRate']  = $myrow['rate'];



        echo '<input type="hidden" name="SelectedCurrency" VALUE="' . $SelectedCurrency . '">';
        echo '<input type="hidden" name="Abbreviation" VALUE="' . $_POST['Abbreviation'] . '">';
        echo '<table><tr>
			<td>' . _('ISO 4217 Código de la moneda').':</td>
			<td>';
        echo $_POST['Abbreviation'] . '</td></tr>';

    } else { //end of if $SelectedCurrency only do the else when a new record is being entered
		if (!isset($_POST['Abbreviation'])) {$_POST['Abbreviation']='';}
        echo '<table><tr>
			<td><span class="generalSpan mr5">' ._('Abreviación de la moneda') . ':</span></td>
			<td><input ' . (in_array('Abbreviation',$Errors) ?  'class="inputerror form-control mb10"' : 'class="form-control mb10"' ) .' type="Text" name="Abbreviation" value="' . $_POST['Abbreviation'] . '" size=4 maxlength=3></td></tr>';
    }

    echo '<tr><td><span class="generalSpan mr5">'._('Moneda').':</span></td>';
    echo '<td>';
	if (!isset($_POST['CurrencyName'])) {$_POST['CurrencyName']='';}
    echo '<input ' . (in_array('CurrencyName',$Errors) ?  'class="inputerror form-control mb10"' : 'class="form-control mb10"' ) .' type="text" name="CurrencyName" size=20 maxlength=20 VALUE="' . $_POST['CurrencyName'] . '">';
    echo '</td></tr>';
    echo '<tr><td><span class="generalSpan mr5">'._('Pais').':</span></td>';
    echo '<td>';
	if (!isset($_POST['Country'])) {$_POST['Country']='';}
    echo '<input ' . (in_array('Country',$Errors) ?  'class="inputerror form-control mb10"' : 'class="form-control mb10"' ) .' type="text" name="Country" size=30 maxlength=50 VALUE="' . $_POST['Country'] . '">';
    echo '</td></tr>';
    echo '<tr><td><span class="generalSpan mr5">'._('Fracción de la moneda').':</span></td>';
    echo '<td>';
	if (!isset($_POST['HundredsName'])) {$_POST['HundredsName']='';}
    echo '<input ' . (in_array('HundredsName',$Errors) ?  'class="inputerror form-control mb10"' : 'class="form-control mb10"' ) .' type="text" name="HundredsName" size=10 maxlength=15 VALUE="'. $_POST['HundredsName'].'">';
    echo '</td></tr>';
    echo '<tr><td><span class="generalSpan mr5">'._('Tipo de Cambio').':</span></td>';
    echo '<td>';
	if (!isset($_POST['ExchangeRate'])) {$_POST['ExchangeRate']='';}
    echo '<input ' . (in_array('ExchangeRate',$Errors) ?  'class="inputerror form-control mb10"' : 'class="form-control mb10"' ) .' type="text" class=number name="ExchangeRate" size=10 maxlength=9 VALUE='. $_POST['ExchangeRate'].'>';
    echo '</td></tr>';
    echo '</table>';

    echo '<div class="centre"><input class="botonVerde" type="Submit" name="submit" value='._('Enter Information').'></div>';

    echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer_Index.inc');
?>
