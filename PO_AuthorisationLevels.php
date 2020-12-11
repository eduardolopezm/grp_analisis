<?php
$PageSecurity=15;

include('includes/session.inc');

$title = _('Autorizaciones en Ordenes de Compra');
include('includes/header.inc');
$funcion=123;
include('includes/SecurityFunctions.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p><br>';
$User='';
$Currency='';
$CanCreate=0;
$AuthLevel=0;
if (isset($_POST['Submit'])) {

	if ($_POST['cancreate']=='on') {
		$cancreate=0;
	} else {
		$cancreate=1;
	}
	if ($_POST['canauthorise']=='on') {
		$canauthorise=0;
	} else {
		$canauthorise=1;
	}
	if ($_POST['cancancell']=='on') {
		$cancancell=0;
	} else {
		$cancancell=1;
	}
	if ($_POST['cancomplete']=='on') {
		$cancomplete=0;
	} else {
		$cancomplete=1;
	}

	$sql='INSERT INTO purchorderauth (userid,currabrev,cancreate,authlevel,category,account,canauthorise,cancancell,cancomplete)
			VALUES(
		"'.$_POST['userid'].'",
		"'.$_POST['currabrev'].'",
		'.$cancreate.',
		'.$_POST['authlevel'].',
		"'.$_POST['StockCat'].'",
		"'.$_POST['AccountCode'].'",
		'.$canauthorise.',
		'.$cancomplete.',
		'.$cancancell.')';
	$ErrMsg = _('Los detalles de autenticacion no se pueden insertar porque');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_POST['Update'])) {

	if ($_POST['cancreate']=='on') {
		$cancreate=0;
	} else {
		$cancreate=1;
	}
	if ($_POST['canauthorise']=='on') {
		$canauthorise=0;
	} else {
		$canauthorise=1;
	}
	if ($_POST['cancancell']=='on') {
		$cancancell=0;
	} else {
		$cancancell=1;
	}
	if ($_POST['cancomplete']=='on') {
		$cancomplete=0;
	} else {
		$cancomplete=1;
	}
	$sql='UPDATE purchorderauth SET
			cancreate='.$cancreate.',
			canauthorise='.$canauthorise.',
			cancancell='.$cancancell.',
			cancomplete='.$cancomplete.',
			authlevel='.$_POST['authlevel'].',
			category="'.$_POST['StockCat'].'",
			account ="'.$_POST['AccountCode'].'"
		WHERE userid="'.$_POST['useridorig'].'"
		AND currabrev="'.$_POST['currabrevorig'].'"
		AND category="'.$_POST['StockCatorig'].'"
		AND account="'.$_POST['AccountCodeorig'].'"';
	//echo $sql;

	$ErrMsg = _('Los detalles de autenticacion no se puede actualizar porque');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_GET['Delete'])) {
	$sql='DELETE FROM purchorderauth
		WHERE userid="'.$_GET['UserID'].'"
		AND currabrev="'.$_GET['Currency'].'"
		AND category="'.$_GET['StockCat'].'"
		AND account="'.$_GET['AccountCode'].'"';

	$ErrMsg = _('Los detalles de autenticaci—n no se pueden eliminar porque');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_GET['Edit'])) {
	$sql='SELECT cancreate,
				canauthorise,
				cancancell,
				authlevel,
				category,
				account,
				cancomplete
			FROM purchorderauth
		WHERE userid="'.$_GET['UserID'].'"
		AND currabrev="'.$_GET['Currency'].'"
		AND category="'.$_GET['StockCat'].'"
		AND account="'.$_GET['AccountCode'].'"';
	$ErrMsg = _('Los detalles de autenticaci—n no se pueden recuperar porque');
	$result=DB_query($sql,$db,$ErrMsg);
	$myrow=DB_fetch_array($result);
	$UserID=$_GET['UserID'];
	$Currency=$_GET['Currency'];
	$CanCreate=$myrow['cancreate'];
	$CanAuth=$myrow['canauthorise'];
	$CanCancell=$myrow['cancancell'];
	$CanComplete=$myrow['cancomplete'];
	$AuthLevel=$myrow['authlevel'];
	$Category=$myrow['category'];
	$AccountCode=$myrow['account'];
	$_POST['StockCat'] = $Category;
	$_POST['AccountCode'] = $AccountCode;
}

$sql='SELECT
	purchorderauth.userid,
	www_users.realname,
	currencies.currabrev,
	currencies.currency,
	purchorderauth.cancreate,
	purchorderauth.canauthorise,
	purchorderauth.cancancell,
	purchorderauth.authlevel,
	purchorderauth.category,
	purchorderauth.account,
	stockcategory.categorydescription,
	chartmaster.accountname,
	purchorderauth.cancomplete
	FROM (purchorderauth
	LEFT JOIN www_users ON purchorderauth.userid=www_users.userid)
	LEFT JOIN currencies ON purchorderauth.currabrev=currencies.currabrev
	LEFT JOIN stockcategory ON stockcategory.categoryid = purchorderauth.category
	LEFT JOIN chartmaster ON chartmaster.accountcode = purchorderauth.account
	';

$ErrMsg = _('Los detalles de autenticaci—n no se pueden recuperar porque');
$Result=DB_query($sql,$db,$ErrMsg);

echo '<table class="table table-striped table-bordered "><thead class="bgc8" style="color:#fff">';
echo '<th>'._('Clave Usuario').'</th>';
echo '<th>'._('Nombre Usuario').'</th>';
echo '<th>'._('Moneda').'</th>';
echo '<th>'._('Crear Ordenes').'</th>';
echo '<th>'._('Autorizar').'</th>';
echo '<th>'._('Cancelar').'</th>';
echo '<th>'._('Surtir Req.').'</th>';
echo '<th>'._('Categoria').'</th>';
echo '<th>'._('Cuenta Contable').'</th>';
echo '<th>'._('Monto Autorizaciones').'</th>';
echo '<th colspan="2"> </th>';
echo '</thead>';

while ($myrow=DB_fetch_array($Result)) {
	if ($myrow['cancreate']==1) {
		$cancreate=_('No');
	} else {
		$cancreate=_('Si');
	}
	if ($myrow['canauthorise']==1) {
		$canauthorise=_('No');
	} else {
		$canauthorise=_('Si');
	}
	if ($myrow['cancancell']==1) {
		$cancancell=_('No');
	} else {
		$cancancell=_('Si');
	}
	if ($myrow['cancomplete']==1) {
		$cancomplete=_('No');
	} else {
		$cancomplete=_('Si');
	}
	echo '<tr><td>'.$myrow['userid'].'</td>';
	echo '<td>'.$myrow['realname'].'</td>';
	echo '<td>'.$myrow['currency'].'</td>';
	echo '<td>'.$cancreate.'</td>';
	echo '<td>'.$canauthorise.'</td>';
	echo '<td>'.$cancancell.'</td>';
	echo '<td>'.$cancomplete.'</td>';
	if ($myrow['categorydescription'] == null)
		echo '<td>'.$myrow['category'].'</td>';
	else
		echo '<td>'.$myrow['category'].' '.$myrow['categorydescription'].'</td>';

	if ($myrow['accountname'] == null)
		echo '<td>'.$myrow['account'].'</td>';
	else
		echo '<td>'.$myrow['account'].' '.$myrow['accountname'].'</td>';

	echo '<td class="number">'.number_format($myrow['authlevel'],2).'</td>';
	echo '<td><a href="'.$rootpath.'/PO_AuthorisationLevels.php?' . SID . 'Edit=Yes&UserID=' . $myrow['userid'] .
	 '&Currency='.$myrow['currabrev'].'&StockCat='.$myrow['category'].'&AccountCode='.$myrow['account'].'">'._('Modificar').'</td>';
	echo '<td><a href="'.$rootpath.'/PO_AuthorisationLevels.php?' . SID . 'Delete=Yes&UserID=' . $myrow['userid'] .
	 '&Currency='.$myrow['currabrev'].'&StockCat='.$myrow['category'].'&AccountCode='.$myrow['account'].'">'._('Eliminar').'</td></tr>';
}

echo '</table><br><br>';
echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post name='form1'>";
echo '<table class="table table-striped table-bordered ">';
/*echo '
<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta Contable de Retención de IVA HONORARIOS: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="gllink_retencioniva" tabindex="13" Name=gllink_retencioniva class="ivahonorarios">';*/

//echo '<tr><td>'._('Clave Usuario').'</td><td><select name=userid>';
//echo '<tr><td>'._('Clave Usuario').'</td><td>';
echo '<tr><td> <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>'._('Clave Usuario').'</label></span>
              </div></td><td>
              <div class="col-md-9">
                  <select id="userid" tabindex="13" Name=userid class="userid"> ';

$usersql='SELECT userid FROM www_users ORDER BY userid';
$userresult=DB_query($usersql,$db);
while ($myrow=DB_fetch_array($userresult)) {
	if ($myrow['userid']==$UserID) {
		echo '<option selected value="'.$myrow['userid'].'">'.$myrow['userid'].'</option>';
	} else {
		echo '<option value="'.$myrow['userid'].'">'.$myrow['userid'].'</option>';
	}
}
//echo '</select>';
echo '</select></div></div> <br></td>';
echo "<input type='hidden' name='useridorig' value='" . $UserID . "'>";
echo '</td></tr>';

//echo '<tr><td>'._('Moneda').'</td><td><select name=currabrev>';
echo '<tr><td> <div class="form-inline row">
              <div class="col-md-3">
                  <span>'._('Moneda').'</span>
              </div></td><td>
              <div class="col-md-9">
                  <select id="currabrev" tabindex="13" Name=currabrev class="currabrev">';

$currencysql='SELECT currabrev,currency FROM currencies';
$currencyresult=DB_query($currencysql,$db);
while ($myrow=DB_fetch_array($currencyresult)) {
	if ($myrow['currabrev']==$Currency) {
		echo '<option selected value="'.$myrow['currabrev'].'">'.$myrow['currency'].'</option>';
	} else {
		echo '<option value="'.$myrow['currabrev'].'">'.$myrow['currency'].'</option>';
	}
}
//echo '</select>';
echo '</select></div></div><br></td> ';
echo "<div class='text-center'><input type='hidden' name='currabrevorig' value='" . $Currency . "'> </div>";
echo '</td></tr>';

echo '<tr><td>'._('Usuario puede crear Ordenes').'</td>';
if ($CanCreate==1) {
	echo '<td><div class="text-center"><input type=checkbox name=cancreate ></div></td</tr>';
} else {
	echo '<td><div class="text-center"><input type=checkbox checked name=cancreate></div></td</tr>';
}

echo '<tr><td>'._('Usuario puede Autorizar Ordenes').'</td>';
if ($CanAuth==1) {
	echo '<td><div class="text-center"><input type=checkbox name=canauthorise></div></td</tr>';
} else {
	echo '<td><div class="text-center"><input type=checkbox checked name=canauthorise></div></td</tr>';
}

echo '<tr><td>'._('Usuario puede Cancelar Ordenes').'</td>';
if ($CanCancell==1) {
	echo '<td><div class="text-center"><input type=checkbox name=cancancell></div></td</tr>';
} else {
	echo '<td><div class="text-center"><input type=checkbox checked name=cancancell></div></td</tr>';
}

echo '<tr><td>'._('Usuario puede Surtir Requisiciones').'</td>';
if ($CanComplete==1) {
	echo '<td><div class="text-center"><input type=checkbox name=cancomplete></div></td</tr>';
} else {
	echo '<td><div class="text-center"><input type=checkbox checked name=cancomplete></div></td</tr>';
}

echo '<tr><td>'._('Usuario puede autorizar Ordenes con montos hasta:').'</td>';
//echo '<td><div class="text-center"><input type=input name=authlevel size=11 class=number value='.$AuthLevel.'></div></td</tr>';
echo '<td><div class="col-md-6 col-xs-12"><component-text  id="authlevel" name="authlevel" placeholder="" title="" value='.$AuthLevel.'></component-text></div> </td>';

$sql='SELECT sto.categoryid, categorydescription FROM stockcategory sto, sec_stockcategory sec WHERE stocktype<>"L" AND stocktype<>"D" AND sto.categoryid=sec.categoryid AND userid="'.$_SESSION['UserID'].'" ORDER BY categorydescription';

$ErrMsg = _('Los detalles de categoria proveedor no podran ser recuperados porque');
$DbgMsg = _('El SQL fue utilizado para recuperar los detalles de categor’a, pero fracaso');
$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);


//echo "<tr><td>Categoría :</td><td><select name='StockCat'>";
echo '<tr><td> <div class="form-inline row">
              <div class="col-md-3">
                  <span>'._('Categoría').'</span>
              </div></td><td>
              <div class="col-md-9">
                  <select id="StockCat" tabindex="13" Name=StockCat class="StockCat">';

if (isset($_POST['StockCat']) and $_POST['StockCat']=='All')
	echo "<option selected value='All'>" . _('TODOS');
else
	echo "<option value='All'>" . _('TODOS');

if (isset($_POST['StockCat']) and $_POST['StockCat']=='None')
	echo "<option selected value='None'>" . _('NINGUNA');
else
	echo "<option value='None'>" . _('NINGUNA');

while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['StockCat']) and $_POST['StockCat']==$myrow1['categoryid']){
		echo "<option selected value=". $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
	} else {
		echo "<option value=". $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
	}
}
//echo '</select>';
echo '</select></div></div><br></td> ';

echo "<div class='text-center'><input type='hidden' name='StockCatorig' value='" . $_POST['StockCat'] . "'> </div>";
echo '</td></tr>';

$sql='select accountcode, accountname from chartmaster where tipo = 4 and naturaleza = 1';

$ErrMsg = _('Los detalles de la categoria proveedor no podran ser recuperados porque');
$DbgMsg = _('El SQL fue utilizado para recuperar los detalles de categor’a, pero fracaso');
$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

//echo "<tr><td>Cuenta Contable Gastos :</td><td><select name='AccountCode'>";
echo '<tr><td> <div class="form-inline row">
              <div class="col-md-3">
                  <span>'.('Cuenta Contable Gastos').'</span>
              </div></td><td>
              <div class="col-md-9">
                  <select id="AccountCode" tabindex="13" Name=AccountCode class="AccountCode">';

if (isset($_POST['AccountCode']) and $_POST['AccountCode']=='All')
	echo "<option selected value='All'>" . _('TODAS');
else
	echo "<option value='All'>" . _('TODAS');

if (isset($_POST['AccountCode']) and $_POST['AccountCode']=='None')
	echo "<option selected value='None'>" . _('NINGUNA');
else
	echo "<option value='None'>" . _('NINGUNA');

while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['AccountCode']) and $_POST['AccountCode']==$myrow1['accountcode']){
		echo "<option selected value=". $myrow1['accountcode'] . '>' . $myrow1['accountname'];
	} else {
		echo "<option value=". $myrow1['accountcode'] . '>' . $myrow1['accountname'];
	}
}
//echo '</select>';
echo '</select></div></div><br></td> ';
echo "<input type='hidden' name='AccountCodeorig' value='" . $_POST['AccountCode'] . "'>";
echo '</td></tr>';

echo '</table>';

if (isset($_GET['Edit'])) {
	echo '<br><div  class="centre"><input class="btn bgc8" type=submit name="Update" style="color:#fff" value="'._('Actualizar').'"></div></form>';
} else {
	echo '<br><div class="centre"><input type=submit name="Submit" value="'._('Procesar').'" class="btn bgc8" style="color:#fff"></div></form>';
}
include('includes/footer_Index.inc');
?>
<script type="text/javascript">
	fnFormatoSelectGeneral(".userid");
	fnFormatoSelectGeneral(".currabrev");
	fnFormatoSelectGeneral(".StockCat");
    fnFormatoSelectGeneral(".AccountCode");
</script>
