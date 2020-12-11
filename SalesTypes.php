<?php
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 5;
$funcion = 116;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

if (isset($_POST['SelectedType'])){
	$SelectedType = strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = strtoupper($_GET['SelectedType']);
}

if (isset($_POST['submit'])) {
	$InputError = 0;
	$i=1;

	if (strlen($_POST['TypeAbbrev']) > 2) {
		$InputError = 1;
		prnMsg(_('El código debe ser de 1 o 2 caracteres'),'error');
		$i++;
	} elseif (empty($_POST['TypeAbbrev'])) {
		$InputError = 1;
		prnMsg(_('El código se encuentra vacío'),'error');
		$i++;
	} elseif (strlen($_POST['Sales_Type']) > 20) {
		$InputError = 1;
		echo prnMsg(_('El nombre no puede ser mayor a 20 caracteres'),'error');
		$i++;
	} elseif ($_POST['TypeAbbrev']=='AN'){
		$InputError = 1;
		prnMsg (_('El código no puede ser AN'),'error');
		$i++;
	} elseif (empty($_POST['anio'])) {
		$InputError = 1;
		prnMsg(_('El año se encuentra vacío'),'error');
		$i++;
	}

	if (isset($SelectedType) AND $InputError !=1) {
		$sql = "UPDATE salestypes
		SET sales_type = '".$_POST['Sales_Type']."', 
		anio = '".$_POST['anio']."'
		WHERE typeabbrev = '$SelectedType'";

		$msg = _('El Registro ' . $SelectedType . ' ha sido actualizado');
	} elseif ( $InputError !=1 ) {
		$checkSql = "SELECT count(*) FROM salestypes WHERE typeabbrev = '" . $_POST['TypeAbbrev'] . "'";
		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ($checkrow[0] > 0) {
			$InputError = 1;
			prnMsg( _('El código ') . $_POST['TypeAbbrev'] . _(' ya existe'),'error');
		} else {
			$sql = "INSERT INTO salestypes
			(typeabbrev,
			sales_type,
			anio)
			VALUES ('" . str_replace(' ', '', $_POST['TypeAbbrev']) . "',
			'" . $_POST['Sales_Type'] . "',
			'" . $_POST['anio'] . "')";

			$msg = _('El Registro' . $_POST["Sales_Type"] . ' ha sido creado');
			$checkSql = "SELECT count(typeabbrev)
			     FROM salestypes";
			$result = DB_query($checkSql, $db);
			$row = DB_fetch_row($result);
			
			$sqlseguridad="insert into sec_pricelist (userid,pricelist)";
			$sqlseguridad=$sqlseguridad." values('".$_SESSION['UserID']."','".str_replace(' ', '', $_POST['TypeAbbrev'])."')";
		}
	}

	if ( $InputError !=1) {
		$result = DB_query($sql,$db);
		if (strlen($sqlseguridad)>0){
			$result = DB_query($sqlseguridad,$db);
		}
		$sql = "SELECT confvalue FROM config WHERE confname='DefaultPriceList'";
		$result = DB_query($sql,$db);
		$PriceListRow = DB_fetch_row($result);
		$DefaultPriceList = $PriceListRow[0];

		$checkSql = "SELECT count(*) FROM salestypes WHERE typeabbrev = '" . $DefaultPriceList . "'";
		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ($checkrow[0] == 0) {
			$sql = "UPDATE config
			SET confvalue='".$_POST['TypeAbbrev']."'
			WHERE confname='DefaultPriceList'";
			$result = DB_query($sql,$db);
			$_SESSION['DefaultPriceList'] = $_POST['TypeAbbrev'];
		}

		prnMsg($msg,'success');

		unset($SelectedType);
		unset($_POST['TypeAbbrev']);
		unset($_POST['Sales_Type']);
		unset($_POST['anio']);
	}

} elseif ( isset($_GET['delete']) ) {
	$sql= "SELECT COUNT(*)FROM debtortrans WHERE debtortrans.tpe='$SelectedType'";
	$ErrMsg = _('The number of transactions using this customer/sales/pricelist type could not be retrieved');
	$result = DB_query($sql,$db,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('No se puede eliminar '.$SelectedType.' tiene '.$myrow[0].' transacciones generadas'),'error');
	} else {
		$sql = "SELECT COUNT(*) FROM debtorsmaster WHERE salestype='$SelectedType'";
		$ErrMsg = _('The number of transactions using this Sales Type record could not be retrieved because');
		$result = DB_query($sql,$db,$ErrMsg);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg(_('No se puede eliminar '.$SelectedType.' tiene '.$myrow[0].' contribuyentes asignados'),'error');
		} else {

			$sql="DELETE FROM salestypes WHERE typeabbrev='$SelectedType'";
			$ErrMsg = _('The Sales Type record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('El Registro ' . $_POST["Sales_Type"] . ' ha sido eliminado') ,'success');

			$sql ="DELETE FROM prices WHERE prices.typeabbrev='SelectedType'";
			$ErrMsg =  _('The Sales Type prices could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);

			unset($SelectedType);
			unset($_POST['TypeAbbrev']);
			unset($_POST['Sales_Type']);
			unset($_POST['anio']);
		}
	}
}

if (!isset($SelectedType)){

	$sql = 'SELECT * FROM salestypes';
	$result = DB_query($sql,$db);

	echo '<table class="table table-bordered">';
	echo "<tr class='tableHeaderVerde'>
	<th style='text-align: center;'>" . _('Código') . "</th>
	<th style='text-align: center;'>" . _('Nombre') . "</th>
	<th style='text-align: center;'>" . _('Año') . "</th>
	<th style='text-align: center;'>" . _('Modificar') . "</th>
	<th style='text-align: center;'>" . _('Eliminar') . "</th>
	</tr>";

	while ($myrow = DB_fetch_row($result)) {
		echo '<tr>';

		echo "<td style='text-align: center;'>".$myrow[0]."</td>";
		echo "<td style='text-align: center;'>".$myrow[1]."</td>";
		echo "<td style='text-align: center;'>".$myrow[5]."</td>";
		echo "<td style='text-align: center;'>
			<a href='".$_SERVER['PHP_SELF']."?SelectedType=".$myrow[0]."'><span class='glyphicon glyphicon-edit'></span></a>
		</td>";
		echo "<td style='text-align: center;'>
			<a href='".$_SERVER['PHP_SELF']."?SelectedType=".$myrow[0]."&delete=yes' onclick=\"return confirm('" . _("Desea eliminar ".$myrow[0]." - ".$myrow[1]." ?") . "');\"><span class='glyphicon glyphicon-trash'></span></a>
		</td>";
	}

	echo '</table>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

if (!isset($_POST['Sales_Type'])) {
	$_POST['Sales_Type']='';
}

$readonly = '';
if ( isset($SelectedType) AND $SelectedType != '' ) {
	$readonly = 'readonly="true"';
	$sql = "SELECT typeabbrev, sales_type, anio
	FROM salestypes
	WHERE typeabbrev='$SelectedType'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['TypeAbbrev'] = $myrow['typeabbrev'];
	$_POST['Sales_Type']  = $myrow['sales_type'];
	$_POST['anio']  = $myrow['anio'];

	echo "<input type=hidden name='SelectedType' VALUE=" . $SelectedType . ">";
}

if (! isset ( $_GET ['delete'] )) {
	?>
	<div align="left">
		<!--Panel Busqueda-->
		<div class="panel panel-default">
			<div class="panel-heading" role="tab" id="headingOne">
				<h4 class="panel-title row">
					<a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
					Información Agregar/Modificar
					</a>
				</h4>
			</div>
			<div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
				<div class="panel-body">
					<div class="col-md-4 col-xs-12">
						<component-text-label label="Código:" id="TypeAbbrev" name="TypeAbbrev" placeholder="Código" title="Código" value="<?php echo $_POST['TypeAbbrev']; ?>" maxlength="2" <?php echo $readonly; ?>></component-text-label>
					</div>
					<div class="col-md-4 col-xs-12">
						<component-text-label label="Nombre:" id="Sales_Type" name="Sales_Type" placeholder="Nombre" title="Nombre" value="<?php echo $_POST['Sales_Type']; ?>" maxlength="20"></component-text-label>
					</div>
					<div class="col-md-4 col-xs-12">
						<component-number-label label="Año:" id="anio" name="anio" placeholder="Año" title="Año" value="<?php echo $_POST['anio']; ?>" maxlength="4"></component-number-label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div align="center">
		<component-button type="submit" id="submit" name="submit" class="glyphicon glyphicon-floppy-disk" value="Guardar"></component-button>
		<?php if (isset($SelectedType)): ?>
			<a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="SalesTypes.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
		<?php endif ?>
		<br><br>
	</div>
	<?php
}

echo '</form>';

include 'includes/footer_Index.inc';
?>