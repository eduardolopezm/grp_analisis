<?php
/* $Revision: 1.3 $ */

$PageSecurity = 2;

include('includes/session.inc');
//$title = _('View Currency Trends');
$title = _('Ver tendencias de las divisas');

include('includes/header.inc');
//include('includes/SecurityFunctions.inc');
//include('includes/SQL_CommonFunctions.inc');

$graph = '/graph120.png';

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if ( isset($_GET['CurrencyToShow']) ){
    $CurrencyToShow = $_GET['CurrencyToShow'];
} elseif ( isset($_POST['CurrencyToShow']) ) {
	$CurrencyToShow = $_POST['CurrencyToShow'];
}

// ************************
// SHOW OUR MAIN INPUT FORM
// ************************

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	//echo '<p><div class="centre"><font size=4 color=BLUE><b><U>' . _('View Currency Trend') . '</b></U></font></div>';
	echo '<p><div class="centre"><font size=4 color=BLUE><b><U>' . _('Ver tendencias de las divisas') . '</b></U></font></div>';
	echo '<table>'; // First column

	$SQL = 'SELECT * FROM currencies';
	$result=DB_query($SQL,$db);


	// CurrencyToShow Currency Picker
	echo '<tr><td><select id="idCurrencyToShow" class="form-control mb10" name="CurrencyToShow">';

		DB_data_seek($result,0);
		while ($myrow=DB_fetch_array($result)) {
			if ($myrow['currabrev']!=$_SESSION['CompanyRecord']['currencydefault']){
				if ( $CurrencyToShow==$myrow['currabrev'] )	{
					echo '<option selected value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')';
				} else {
					echo '<option value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')';
				}
			}
		}
		echo '</select></td></tr>';

   	echo '</table>'; /*close off the table in the third column */


	echo '<p><div class="centre"><input class="botonVerde" type=submit name=submit VALUE="' . _('Aceptar') . '"></div>';
   	echo '</form>';



// **************
// SHOW OUR GRAPH
// **************


	$graph = $CurrencyToShow. '/' . $FunctionalCurrency . $graph;
	$image = 'http://www.x-rates.com/d/' . $graph;

	echo '<p><div class="centre"><font size=4 color=BLUE><b><U>' . $FunctionalCurrency . ' / ' . $CurrencyToShow . '</b></U></font>';
	echo '<p></div><table class="tableHeaderVerde" border=1>';
	echo '<tr><td><img src=' . $image . ' alt="Tendencia no Disponible Actualmente"></td></tr>';
	echo '</table>';


include('includes/footer.inc');
?>
<script language=javascript>
fnFormatoSelectGeneral("#idCurrencyToShow");
</script> 