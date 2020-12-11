<script>
function Procesarfa()
{
	
	FDatosB.buscaMovsERP.value='Buscar Movimientos ERP';
	//alert(FDatosB.buscaMovsERP.value);
		FDatosB.submit();

}
//alert('entra');
</script>

<?php

/*
 *	14/SEPT/2012 -desarrollo- Agregue condicion de  and (batchconciliacion <> -1 OR batchconciliacion is null), no se estaban mostrando algunos movimientos...
 *	
 *	04/SEPT/2012 -desarrollo- Agregue condicion de  and batchconciliacion <> -1 a todos los queries para que no desplieguen movimientos
 *				que manualmente se eliminaron de la conciliacion.
 
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION:09-MAR-2012
 CAMBIOS: 
	1. SE CAMBIO NOMBRE DE VARIABLES YA QUE AL HACER EL POST NO TERMINABA DE ENVIARSE LOS DATOS
	ConciliadoBanKAmtClear_ cambio por MConAmtCl_
	ConciliadoBanKBankTrans_ cambio por MConBBT_
	ConciliadoBankTrans_ cambio por MCBT_
	BanKBankTrans_ cambio por BBT_
	BanKAmtClear_ cambio por BAC_
	BanKClear_ cambio por BKC_
	BankTrans_ cambio por BT_

 FIN DE CAMBIOS

 
 
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION:24-FEB-2012
 CAMBIOS: 
	1. Se agrego campo de num de autorizacion a tabla de banktrans para agilizar busquedas
	ALTER TABLE `banktrans` 
	ADD COLUMN `numautorizacion` VARCHAR(45) NOT NULL DEFAULT ' ' 
	COMMENT 'Numero de referencia de Corte de caja' AFTER `matchperiodno`;
 FIN DE CAMBIOS
 

  desarrollo- 22/NOVIEMBRE/2011 - Correccion que no se pudiera quitar match de un solo movimiento ya conciliado.
				Mejore funcionalidad para busquedas de chequera con os
				
  desarrollo- 06/JULIO/2011 - Candado para que no se puedan conciliar movimientos si no cuadran los montos de un lado y del otro asi como que
				no se pueden quitar de la conciliacion si no se seleccionan montos iguales de cada lado.
			
			  Ahora solo incrementa el id de la transaccion si es una conciliacion valida
   
  desarrollo- 14/ABRIL/2011 - Se cambio que consulta de edo cuenta banco sea por periodo y no por fecha, tambien se actualiza en la tabla de transacciones bancarias el periodo
			en el que se identifica el movimiento en el estado de cuenta bancario
			
			ALTER TABLE `banktrans` ADD COLUMN `matchperiodno` int NOT NULL AFTER `batchconciliacion`;			
			insert into `systypes` ( `typename`, `typeid`) values ( 'Partidas de Conciliacion Bancaria', '600');
			insert into `systypesinvtrans` ( `typename`, `typeid`,typeno) values ( 'Partidas de Conciliacion Bancaria', '600',0);
			INSERT INTO `sec_functions` VALUES  
			('2200', '7', 'CONCILIACION BANCARIA DIARIA', '1', 'BankMatchingV2_0.php', '1', 'Conciliacion Bancaria Diaria', '1', 'Conciliaciones Diarias', '')
			
	JAHEPI - 13/NOV/2012 Agregué botón para exportar a excel
*/

$PageSecurity = 7;

include("includes/session.inc");

if(!isset($_POST['excel'])) {
	$title = _('Conciliacion Bancaria');
	include('includes/header.inc');
}

$funcion=2200;

include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

if ((isset($_GET["Type"]) and $_GET["Type"]=='Receipts') OR (isset($_POST["Type"]) and $_POST["Type"]=='Receipts')){
	$Type = 'Receipts';
	$TypeName =_('Receipts');
//echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/currency.gif" title="' . _('Conciliacion Bancaria') . '" alt="">' . ' ' . _('Conciliacion Bancaria - Depositos') . '</p>';
} elseif ((isset($_GET["Type"]) and $_GET["Type"]=='Payments') OR (isset($_POST["Type"]) and $_POST["Type"]=='Payments')) {
	$Type = 'Payments';
	$TypeName =_('Payments');
//echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/currency.gif" title="' . _('Conciliacion Bancaria') . '" alt="">' . ' ' . _('Conciliacion Bancaria - Pagos') . '</p>';
} else {
	$Type = '*';
	$TypeName = '*';
//echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/currency.gif" title="' . _('Conciliacion Bancaria') . '" alt="">' . ' ' . _('Conciliacion Bancaria') . '</p>';

}


    if (isset($_POST['FromYear'])) {
        $FromYear=$_POST['FromYear'];
    } else {
        $FromYear=date('Y');
    }

    if (isset($_POST['FromMes'])) {
        $FromMes=$_POST['FromMes'];
    } else {
        $FromMes=date('m');
    }
     
     
    if (isset($_POST['FromDia'])) {
        $FromDia=$_POST['FromDia'];
    } else {
	$FromDia=1;
    }
    
     $fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
     $fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
     $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);
     
     $_POST['BeforeDate'] = $fechainic;
    
     $InputError = 0;
     
	$sql = "SELECT periodno, lastdate_in_period
				FROM periods
				WHERE YEAR(lastdate_in_period) = ". $FromYear . " AND MONTH(lastdate_in_period) = ". $FromMes;
				
	$ErrMsg =  _('Could not retrieve transaction information');
	$result = DB_query($sql,$db,$ErrMsg);
	$myrow=DB_fetch_array($result);
	$periododeMatch = $myrow[0];


if (isset($_POST['Update']) AND $_POST['RowCounter']>1){
	
	$Total_AmountCleared = 0;
	for ($Counter=1;$Counter <= $_POST['RowCounter']; $Counter++){
		if (isset($_POST["Clear_" . $Counter]) and $_POST["Clear_" . $Counter]==True){
			/*Get amount to be cleared */
			$sql = "SELECT amount, 
						exrate,transdate
					FROM banktrans
					WHERE banktransid=" . $_POST["BT_" . $Counter];
		//	echo '<pre>sql2:'.$sql;
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			//$_POST["BTContable_" . $Counter]=$myrow[2];
			$lastFechaContable=$myrow[2];
			$Total_AmountCleared = $Total_AmountCleared + $AmountCleared;
		}
		
	}
}

if (isset($_POST['Update']) AND $_POST['BankRowCounter']>1){
	
	for ($Counter=1;$Counter <= $_POST['BankRowCounter']; $Counter++){
		if (isset($_POST["BKC_" . $Counter]) and $_POST["BKC_" . $Counter]==True){
			
			/*Get amount to be cleared */
			$sql = "SELECT Retiros-depositos, 1,Fecha
					FROM estadoscuentabancarios
					WHERE banktransid=" . $_POST["BBT_" . $Counter];
			//echo '<pre>sql2:'.$sql;
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			$lastFechaBanco=$myrow[2];
			//$_POST["BBTBanco_" . $Counter]= $myrow[2];
			$Total_AmountCleared = $Total_AmountCleared + $AmountCleared;
		}
		
	}
}

if (abs($Total_AmountCleared) > 1) {
	prnMsg(_('ERROR 4, MOVIMIENTOS NO CUADRAN EN MONTOS !') . '! ' . $Total_AmountCleared . _(' - Favor de seleccionar montos a conciliar que cuadren'),'error');
} else {
	/* OBTENGO  FECHAS */

	if (isset($_POST['Update']) AND ($_POST['RowCounter']>1 OR $_POST['BankRowCounter']>1)){
		$TransNo = GetNextTransNo(600, $db);
		
		
	}	
}

if (isset($_POST['Update']) AND $_POST['RowCounter']>1 AND abs($Total_AmountCleared) <= 1){
	
	
	for ($Counter=1;$Counter <= $_POST['RowCounter']; $Counter++){
		if (isset($_POST["Clear_" . $Counter]) and $_POST["Clear_" . $Counter]==True){
			/*Get amount to be cleared */
			$sql = "SELECT amount, 
						exrate 
					FROM banktrans
					WHERE banktransid=" . $_POST["BT_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			/*Update the banktrans recoord to match it off */
			$sql = "UPDATE banktrans SET amountcleared= ". $AmountCleared .
					",usuario = '".$_SESSION['UserID']."',
					fechacambio = NOW(),
					batchconciliacion= ".$TransNo.",
					matchperiodno = ".$periododeMatch.",
					fechabanco='".$lastFechaBanco."'
					WHERE banktransid=" . $_POST["BT_" . $Counter];
			//echo '<br><pre>sql:'.$Counter.$sql;
			$ErrMsg =  _('Could not match off this payment because');
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["AmtClear_" . $Counter]) and is_numeric((float) $_POST["AmtClear_" . $Counter]) AND 
			((isset($_POST["AmtClear_" . $Counter]) and $_POST["AmtClear_" . $Counter]<0 AND $Type=='Payments') OR 
			($Type=='Receipts' AND (isset($_POST["AmtClear_" . $Counter]) and $_POST["AmtClear_" . $Counter]>0)))){
			/*if the amount entered was numeric and negative for a payment or positive for a receipt */
			$sql = "UPDATE banktrans 
					SET amountcleared=" .  $_POST["AmtClear_" . $Counter] . ",
						usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
						batchconciliacion= ".$TransNo.",
						fechabanco='".$lastFechaBanco."',
						matchperiodno = ".$periododeMatch."
					 WHERE banktransid=" . $_POST["BT_" . $Counter];
			//echo '<br><pre>sql dos:'.$Counter.$sql;
			$ErrMsg = _('Could not update the amount matched off this bank transaction because');
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["Unclear_" . $Counter]) and $_POST["Unclear_" . $Counter]==True){
			$sql = "UPDATE banktrans 
					SET amountcleared = 0, 
					usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
					batchconciliacion=0,
					fechabanco =null
					matchperiodno = ".$periododeMatch."
					 WHERE banktransid=" . $_POST["BT_" . $Counter];
			$ErrMsg =  _('Could not unclear this bank transaction because');
			$result = DB_query($sql,$db,$ErrMsg);
		}
		//$Total_AmountCleared = $Total_AmountCleared + $AmountCleared;
	}

	/*Show the updated position with the same criteria as previously entered*/
	$_POST["ShowTransactions"] = True;
}

if (isset($_POST['Update']) AND $_POST['BankRowCounter']>1 AND abs($Total_AmountCleared) <= 1){
	
	
	for ($Counter=1;$Counter <= $_POST['BankRowCounter']; $Counter++){
		if (isset($_POST["BKC_" . $Counter]) and $_POST["BKC_" . $Counter]==True){
			
			/*Get amount to be cleared */
			$sql = "SELECT Retiros-depositos, 1
					FROM estadoscuentabancarios
					WHERE banktransid=" . $_POST["BBT_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			/*Update the banktrans recoord to match it off */
			$sql = "UPDATE estadoscuentabancarios SET conciliado= ". $AmountCleared . ",
					usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
					tagref = '".$_POST['tag']."',
					fechacontable='".$lastFechaContable."',
					batchconciliacion= ".$TransNo."
					 WHERE banktransid=" . $_POST["BBT_" . $Counter];
			$ErrMsg =  _('Could not match off this payment because');
			
			//echo $sql;
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["BAC_" . $Counter]) and is_numeric((float) $_POST["BAC_" . $Counter]) AND 
			((isset($_POST["BAC_" . $Counter]) and $_POST["BAC_" . $Counter]<0 AND $Type=='Payments') OR 
			($Type=='Receipts' AND (isset($_POST["BAC_" . $Counter]) and $_POST["BAC_" . $Counter]>0)))){
			/*if the amount entered was numeric and negative for a payment or positive for a receipt */
			$sql = "UPDATE estadoscuentabancarios SET conciliado=" .  $_POST["BAC_" . $Counter] . "*-1,
					usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
					tagref = '".$_POST['tag']."',
					fechacontable='".$lastFechaContable."',
					batchconciliacion= ".$TransNo."
					 WHERE banktransid=" . $_POST["BBT_" . $Counter];

			$ErrMsg = _('Could not update the amount matched off this bank transaction because');
			
			//echo $sql;
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["BanKUnclear_" . $Counter]) and $_POST["BanKUnclear_" . $Counter]==True){
			$sql = "UPDATE estadoscuentabancarios SET conciliado = 0, usuario = '".$_SESSION['UserID']."',
					fechacambio = NOW(),
					tagref = '',
					fechacontable=null
					batchconciliacion=0
					 WHERE banktransid=" . $_POST["BBT_" . $Counter];
			$ErrMsg =  _('Could not unclear this bank transaction because');
			
			//echo $sql;
			$result = DB_query($sql,$db,$ErrMsg);
		}
		//$Total_AmountCleared = $Total_AmountCleared + $AmountCleared;
	}
	
	/*Show the updated position with the same criteria as previously entered*/
	$_POST["ShowTransactions"] = True;
}

if (isset($_POST['Update']) AND $_POST['ConciliadoBankRowCounter']>1){
	
	for ($Counter=1;$Counter <= $_POST['ConciliadoBankRowCounter']; $Counter++){
		if (isset($_POST["ConciliadoBKC_" . $Counter]) and $_POST["ConciliadoBKC_" . $Counter]==True){
			
			/*Get amount to be cleared */
			$sql = "SELECT Retiros-depositos, 1,Fecha
					FROM estadoscuentabancarios
					WHERE banktransid=" . $_POST["MConBBT_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			$lastFechaBanco=$myrow[2];			
			//$lastFechaContable=$myrow[2];
			
			$Total_AmountCleared = $Total_AmountCleared + $AmountCleared;
			//echo "ENTRO".$Total_AmountCleared."<br>";
		} elseif (isset($_POST["MConAmtCl_" . $Counter]) and is_numeric((float) $_POST["MConAmtCl_" . $Counter]) AND 
			((isset($_POST["MConAmtCl_" . $Counter]) and $_POST["MConAmtCl_" . $Counter]<0 AND $Type=='Payments') OR 
			($Type=='Receipts' AND (isset($_POST["MConAmtCl_" . $Counter]) and $_POST["MConAmtCl_" . $Counter]>0)))){
			
			/*Get amount to be cleared */
			$sql = "SELECT Retiros-depositos, 1,Fecha
					FROM estadoscuentabancarios
					WHERE banktransid=" . $_POST["MConBBT_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);			
			$lastFechaBanco=$myrow[2];
			$Total_AmountCleared = $Total_AmountCleared + $AmountCleared;
			//echo "ENTRO".$Total_AmountCleared."<br>";
		
		} elseif (isset($_POST["ConciliadoBanKUnclear_" . $Counter]) and $_POST["ConciliadoBanKUnclear_" . $Counter]==True){
			
			/*Get amount to be cleared */
			$sql = "SELECT Retiros-depositos, 1,Fecha
					FROM estadoscuentabancarios
					WHERE banktransid=" . $_POST["MConBBT_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);			
			$lastFechaBanco=$myrow[2];
			$Total_AmountCleared = $Total_AmountCleared + $AmountCleared;
			//echo "ENTRO".$Total_AmountCleared."<br>";
			
		}
		
	}
}

//$Total_AmountCleared = 0;
if (abs($Total_AmountCleared) > 1) {
	prnMsg(_('ERROR 5, MOVIMIENTOS NO CUADRAN EN MONTOS !') . '! ' . _('Favor de seleccionar montos a conciliar que cuadren'),'error');
}

//$Total_AmountCleared = 0;
if (isset($_POST['Update']) AND $_POST['ConciliadoRowCounter']>1  AND abs($Total_AmountCleared) <= 1){
	
	for ($Counter=1;$Counter <= $_POST['ConciliadoRowCounter']; $Counter++){
		if (isset($_POST["ConciliadoClear_" . $Counter]) and $_POST["ConciliadoClear_" . $Counter]==True){
			/*Get amount to be cleared */
			$sql = "SELECT amount, 
						exrate ,transdate
					FROM banktrans
					WHERE banktransid=" . $_POST["MCBT_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			/*Update the banktrans recoord to match it off */
			
			$sql = "UPDATE banktrans SET amountcleared= ". $AmountCleared .
					",usuario = '".$_SESSION['UserID']."',
					fechacambio = NOW(),
					batchconciliacion= ".$TransNo.",
					fechabanco= '".$lastFechaBanco."',
					matchperiodno = ".$periododeMatch." WHERE banktransid=" . $_POST["MCBT_" . $Counter];
			$ErrMsg =  _('Could not match off this payment because');
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["ConciliadoAmtClear_" . $Counter]) and is_numeric((float) $_POST["ConciliadoAmtClear_" . $Counter]) AND 
			((isset($_POST["ConciliadoAmtClear_" . $Counter]) and $_POST["ConciliadoAmtClear_" . $Counter]<0 AND $Type=='Payments') OR 
			($Type=='Receipts' AND (isset($_POST["ConciliadoAmtClear_" . $Counter]) and $_POST["ConciliadoAmtClear_" . $Counter]>0)))){
			/*if the amount entered was numeric and negative for a payment or positive for a receipt */
			$sql = "UPDATE banktrans SET amountcleared=" .  $_POST["ConciliadoAmtClear_" . $Counter] . ",usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
					batchconciliacion= ".$TransNo.",
					fechabanco= '".$lastFechaBanco."',
					matchperiodno = ".$periododeMatch."
					 WHERE banktransid=" . $_POST["MCBT_" . $Counter];

			$ErrMsg = _('Could not update the amount matched off this bank transaction because');
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["ConciliadoUnclear_" . $Counter]) and $_POST["ConciliadoUnclear_" . $Counter]==True){
			
			
			$sql = "UPDATE banktrans SET amountcleared = 0, usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
					batchconciliacion=0,
					fechabanco= null,
					matchperiodno = ".$periododeMatch."
					 WHERE batchconciliacion=" . $_POST["MCBT_" . $Counter];
			$ErrMsg =  _('Could not unclear this bank transaction because');
			$result = DB_query($sql,$db,$ErrMsg);
			
			$sql = "UPDATE estadoscuentabancarios 
					SET conciliado = 0, usuario = '".$_SESSION['UserID']."',
					fechacambio = NOW(),
					tagref = '',
					batchconciliacion=0,
					fechacontable=null
					 WHERE batchconciliacion=" . $_POST["MCBT_" . $Counter];
			$ErrMsg =  _('Could not unclear this bank transaction because');
			$result = DB_query($sql,$db,$ErrMsg);
		}
	}

	/*Show the updated position with the same criteria as previously entered*/
	$_POST["ShowTransactions"] = True;
}

if (isset($_POST['Update']) AND $_POST['ConciliadoBankRowCounter']>1 AND abs($Total_AmountCleared) <= 1){
	
	for ($Counter=1;$Counter <= $_POST['ConciliadoBankRowCounter']; $Counter++){
		if (isset($_POST["ConciliadoBKC_" . $Counter]) and $_POST["ConciliadoBKC_" . $Counter]==True){
			
			/*Get amount to be cleared */
			$sql = "SELECT Retiros-depositos, 1
					FROM estadoscuentabancarios
					WHERE banktransid=" . $_POST["MConBBT_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			/*Update the banktrans recoord to match it off */
			$sql = "UPDATE estadoscuentabancarios SET conciliado= ". $AmountCleared . ",
					usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
					tagref = '".$_POST['tag']."',
					fechacontable='".$lastFechaContable."',
					batchconciliacion= ".$TransNo."
					 WHERE banktransid=" . $_POST["MConBBT_" . $Counter];
			$ErrMsg =  _('Could not match off this payment because');
			
			//echo $sql;
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["MConAmtCl_" . $Counter]) and is_numeric((float) $_POST["MConAmtCl_" . $Counter]) AND 
			((isset($_POST["MConAmtCl_" . $Counter]) and $_POST["MConAmtCl_" . $Counter]<0 AND $Type=='Payments') OR 
			($Type=='Receipts' AND (isset($_POST["MConAmtCl_" . $Counter]) and $_POST["MConAmtCl_" . $Counter]>0)))){
			/*if the amount entered was numeric and negative for a payment or positive for a receipt */
			$sql = "UPDATE estadoscuentabancarios SET conciliado=" .  $_POST["MConAmtCl_" . $Counter] . "*-1,
					usuario = '".$_SESSION['UserID']."', fechacambio = NOW(),
					tagref = '".$_POST['tag']."',
					fechacontable='".$lastFechaContable."',
					batchconciliacion= ".$TransNo."
					 WHERE banktransid=" . $_POST["MConBBT_" . $Counter];

			$ErrMsg = _('Could not update the amount matched off this bank transaction because');
			
			//echo $sql;
			$result = DB_query($sql,$db,$ErrMsg);

		} 
	}
	/*Show the updated position with the same criteria as previously entered*/
	$_POST["ShowTransactions"] = True;
}


// ocupa mucho espacio y quiero que las pantallas de matcheo se vean sin necesidad de usar scroll en pagina principal

//echo '<div class="page_help_text">' . _('Utilice esta pantalla para conciliar depositos y pagos de
//					su estado de cuenta contra el auxiliar de bancos del ERP.
//					Verifica movimientos del estado de cuenta y haz click cuando
//					encuentres las transacciones en ambos lados que coincidan.') . '</div><br>';


//echo "<form name='FDatosB' action='". $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

if(!isset($_POST['excel'])) {
	
//
	echo '<FORM name="FDatosB" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
	
	
	echo '<table border = 0>
		<tr><td colspan=5 align=center style="background-color:yellow; text-align:center;">Conciliacion y Cierre Diario de Cuentas de Cheques !</td></tr>';
	echo '<tr>';
	echo '<td class=number nowrap  >'._('Razon Social:').'</td><td colspan=2 nowrap><select name="legalid">';
	
	///Pinta las razones sociales//
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY legalbusinessunit.legalname";
	
	$result=DB_query($SQL,$db);
	echo '<option selected value="-1">'._('Todas Las Razones Sociales...').'</option>';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>'  .$myrow['legalname'];
		}
	}
	echo '</select>';
		echo "<input type=submit name='selLegalid' VALUE='" . _('->') . "'>";
	echo '</td>';
	
	
	
	echo '<td class=number >';
	
		
	
	
	//Select the tag
		echo ' ' . _('Unidad Negocio') . ':</td><td><select name="tag">';
	
		///Pinta las unidades de negocio por usuario	
		$SQL = "SELECT t.tagref, t.tagdescription";
		$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
		$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
		$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID']."'" ;

		if(isset($_POST['legalid']) && $_POST['legalid']!='-1'){
			$SQL = $SQL ." AND legalid=".$_POST['legalid'];
		}
		
		$SQL = $SQL ."  ORDER BY t.tagdescription, t.tagref";
			
		
		$result=DB_query($SQL,$db);
		echo '<option selected value="0">Todas Las Unidades de Negocio...';
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['tag']) and $_POST['tag']==$myrow['ta		gref']){
				echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
			} else {
				echo '<option value=' . $myrow['tagref'] . '>'  . $myrow['tagdescription'];
			}
		}
		echo '</select></td></tr>';
		// End select tag
		echo '<tr>';
		
		echo '<td align=left nowrap>' . _('Cuenta Bancaria') . ':</td><td colspan=2><select tabindex="1" name="BankAccount">';
		
		$sql = "SELECT accountcode, bankaccountname FROM bankaccounts";
		
		$sql = 'SELECT bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode
			FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
			WHERE bankaccounts.accountcode=chartmaster.accountcode and
				bankaccounts.accountcode = tagsxbankaccounts.accountcode and
				tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
				sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"';
		if(isset($_POST['legalid'])  && $_POST['legalid']!='-1'){
			$sql = $sql ." AND tagsxbankaccounts.tagref in (select tagref from tags where legalid=".$_POST['legalid']." )";
		}
		$sql = $sql .' GROUP BY bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode';
			
		$resultBankActs = DB_query($sql,$db);
		while ($myrow=DB_fetch_array($resultBankActs)){
			if (isset($_POST['BankAccount']) and $myrow["accountcode"]==$_POST['BankAccount']){
				echo "<option selected Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
			} else {
				echo "<option Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
			}
		}
		
		echo '</select>';
		echo "<input type=hidden Name=Type Value=$Type>
		</td>";
		echo '</tr>';
	//Select the tag
	echo '<tr>';
	echo '<td>' . _('Conciliacion') . '</td>
		<td>' . _('Movimientos') . ':</td><td><select name="Type">';
	echo '<option ';
	if ($_POST['Type'] == "*")
		echo 'selected';
	echo ' value="*">Todos los movimientos';
		echo '</option>';	
		echo '<option ';
		if ($_POST['Type'] == "Receipts")
			echo 'selected';
		echo ' value="Receipts">Conciliar Ingresos y Depositos';
		echo '</option>';
		echo '<option ';
		if ($_POST['Type'] == "Payments")
			echo 'selected';
		echo ' value="Payments">Conciliar Egresos y Pagos';
		echo '</option>';
		echo '</select>';
		// End select tag
	//echo '</tr>';
	
	
	//echo '<tr>';
	      echo '</td><td nowrap >
		    ';
				echo '' . _('Del:') . '
				<select Name="FromDia">';
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
				     
				echo '</select>';
				if(!isset($_POST['ShowTransactions'])){
					$_POST['xmes']='on';
				}
				if (isset($_POST['xmes']))
					echo '<input type="checkbox" name="xmes" checked>';
				else
					echo '<input type="checkbox" name="xmes">';
				
				
						
				echo '<select Name="FromMes">';
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
					  echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'></td>';	
					 
	      echo '<td><input tabindex="6" type=submit style="background-color:lightgreen" name="ShowTransactions" VALUE="' . _('Muestra Movimientos') .'">
					  		<input tabindex="6" type=submit name="excel" style="background-color:lightgreen" value="Imprimir Excel" /></td>';	
	   echo '</tr>';
	echo '</table>';
	
	echo '<hr>';

}

/* DESPLIEGA TRANSACCIONES A CONCILIAR DE BANKTRANS */
//echo 'erp:'.$InputError.'banco:'.$_POST["BankAccount"].'transa:'.$_POST["ShowTransactions"];
//echo '<br>'.var_dump($_POST).'banco'.$_POST['buscaERPBANCO'].'auto:'.$_POST['FindAuto_'];

if ($InputError !=1 AND isset($_POST["BankAccount"]) AND $_POST["BankAccount"]!="" AND (isset($_POST["ShowTransactions"]) OR isset($_POST['buscaMovsERP']) OR isset($_POST['buscaERPBANCO']) OR isset($_POST['FindAuto_'])) or isset($_POST['excel'])){
//echo 'entra';

	if (isset($_POST['xmes'])) {
		$SQLBeforeDate = $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-31';
		$SQLAfterDate = $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-01';	
	} else {
		$SQLBeforeDate = $fechaini;
		$SQLAfterDate = $fechaini;	
	}
	
	if(isset($_POST['excel'])) {
		header("Content-type: application/ms-excel");
		header("Content-Disposition: attachment; filename=Reporte.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo 'si';
	}
	
	echo '<table BORDER=4 VALIGN="top" >';
	
	
	/*****************************************************************************************************************/
	/***************************          INICIO DE TABLA DEL ESTADO DE CUENTA BANCARIO        ***********************/
	
	echo '<tr><td colspan=11>';
	echo 'VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
	echo '</td></tr>';
	
	if (!isset($_POST['searchtextBANCO']))
		$_POST['searchtextBANCO'] = '*';
	
	if (!isset($_POST['buscamontoBANCO']))
		$_POST['buscamontoBANCO'] = '0.00';
		
	if (!isset($_POST['rangomontoBANCO']))
		$_POST['rangomontoBANCO'] = '9999999';
				  
	if (empty($_POST['rangodiasBANCO']))
		$_POST['rangodiasBANCO'] = 0;
				  
	echo '<tr><td colspan=11>';
	echo '	buscar x referencia:<input name="searchtextBANCO" type="text" size="20" value="'.$_POST['searchtextBANCO'].'">
		buscar x monto:<input name="buscamontoBANCO" class=number type="text" size="8" value='.$_POST['buscamontoBANCO'].'>
		+/-:<input name="rangomontoBANCO" class=number type="text" size="8" value='.$_POST['rangomontoBANCO'].'>
		
		';
		

	
	echo '	+/- dias:<input name="rangodiasBANCO" type="text" class=number size="3" value='.$_POST['rangodiasBANCO'].'>';
	      
	if (isset($_POST['chkall']))	        
		echo '<input type=checkbox checked name=chkall> CHECK TODAS  --- ';
	else
		echo '<input type=checkbox name=chkall> CHECK TODAS  --- ';

	if(!isset($_POST['excel'])) {
		echo '<input name="buscaERPBANCO" type="submit" style="background-color:orange" class=number size="3" value="Buscar Movimientos Bancos">';
	}
	
	echo '    </td></tr>';
	
	echo '<tr><td colspan=11>';
	echo 'VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
	echo '</td></tr>';
	echo  '  <tr><td colspan=11 style="text-align:center; background-color:darkgray">'. _('BUSQUEDA DE MOVIMIENTOS ERP'). '</td></tr>';
	//echo '<tr><td colspan=11>';
	//echo 'BUSQUEDA DE MOVIMIENTOS ERP';
	//echo '</td></tr>';
	
	if (!isset($_POST['searchtext']))
		$_POST['searchtext'] = '*';
	
	if (!isset($_POST['buscamonto']))
		$_POST['buscamonto'] = '0.00';
		
	if (!isset($_POST['rangomonto']))
		$_POST['rangomonto'] = '9999999';
				  
	if (!isset($_POST['rangodias']))
		$_POST['rangodias'] = 0;
	elseif (!is_numeric($_POST['rangodias']))
		$_POST['rangodias'] = 0;
	
	echo '<tr><td colspan=11 nowrap >';
	echo '	buscar x referencia:<input name="searchtext" type="text" size="20" value='.$_POST['searchtext'].'>
		buscar x monto:<input name="buscamonto" class=number type="text" size="8" value='.$_POST['buscamonto'].'>
		+/-:<input name="rangomonto" class=number type="text" size="8" value='.$_POST['rangomonto'].'>
		x tipo trans:
		';
		
	$sqltype = 'select banktrans.type, systypescat.typename
			from banktrans LEFT JOIN systypescat ON banktrans.type = systypescat.typeid
			group by banktrans.type';
	
	echo '<select Name="FromTipoTrans">';
	echo '<option  VALUE="-1" selected>Todos los tipos</option>';
	$tipos = DB_query($sqltype,$db);
	while ($myrowType=DB_fetch_array($tipos,$db)){
	    if ($_POST['FromTipoTrans']==$myrowType['type']){ 
		echo '<option  VALUE="' . $myrowType['type'] .  '" selected>' .$myrowType['typename'] .'</option>';
	    }else{
		echo '<option  VALUE="' . $myrowType['type'] .  '" >' .$myrowType['typename']. '</option>';
	    }
	}
	echo '</select>';
	echo '	+/- dias:<input name="rangodias" type="text" class=number size="3" value='.$_POST['rangodias'].'>';
	
	//echo '<input name="buscaMovsERP" type="button" class=number size="3" value="Buscar Movimientos" onClick="Procesarfa();">';
	echo '<input tabindex="6" onClick="Procesarfa();" type=button style="background-color:lightgreen" name="buscaMovsERPv2" VALUE="' . _('Buscar Movimientos ERP') .'">';	
	echo '<input tabindex="6" type=hidden  name="buscaMovsERP" >';	
	echo '    </td></tr>';	
	echo '<tr><td colspan=11>';
	echo 'VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
	echo '</td></tr>';
	
	$TableHeader2 = '<tr height=100%>
			<th>'. _('Sucursal'). '</th>
			<th>'. _('Concepto'). '</th>
			<th nowrap>' . _('Fecha') . '</th>
			<th>' . _('Retiros') . '</th>
			<th>' . _('Depositos') . '</th>
			<th>' . _('Outstanding') . '</th>
			<th colspan=3>' . _('Confirmar') . ' / ' . _('Limpiar') . '</th>
			<th>'. _('Usuario'). '</th>
			<th>'. _('Fecha Cambio'). '</th>
			</tr>';
	
	echo  '  <tr><td colspan=11 style="text-align:center; background-color:darkgray">'. _('MOVIMIENTOS DEL ESTADO DE CUENTA BANCARIO'). '</td></tr>';
	//echo $TableHeader2;
	echo '	<tr><td colspan=11>
	
		<div style="width: 1100px; height: 150px; overflow: auto; border: 2px dotted gray; background-color: #FFFFFF;">
		<table cellpadding=2 BORDER=0>';
		echo $TableHeader2;
		if ($Type=='Payments'){
			$sql = "SELECT estadoscuentabancarios.banktransid,
					estadoscuentabancarios.Concepto,
					estadoscuentabancarios.Retiros,
					estadoscuentabancarios.depositos,
					estadoscuentabancarios.conciliado,
					estadoscuentabancarios.Fecha,
					estadoscuentabancarios.legalid,
					IFNULL(estadoscuentabancarios.tagref,'') as tagref,
					estadoscuentabancarios.fechacambio,
					estadoscuentabancarios.usuario,
					tags.tagname
				FROM estadoscuentabancarios LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql." WHERE Retiros > 0
				AND ADDDATE(Fecha,".$_POST["rangodiasBANCO"].") >= '". $SQLAfterDate . "'
				AND SUBDATE(Fecha,".$_POST["rangodiasBANCO"].") <= '" . $SQLBeforeDate . "'
				
				AND ABS(depositos-Retiros) >= (" . ($_POST['buscamontoBANCO']). "+(" . ($_POST['rangomontoBANCO']*-1). ")) AND ABS(depositos-Retiros) <= (" . ($_POST['buscamontoBANCO']). "+". $_POST['rangomontoBANCO']. ")
			
				AND trim(cuenta)='" .$_POST["BankAccount"] . "'
				AND (estadoscuentabancarios.tagref = '".$_POST['tag']."' OR IFNULL(estadoscuentabancarios.tagref,'0') = '0'  OR IFNULL(estadoscuentabancarios.tagref,'') = '' OR  '".$_POST['tag']."' = '0')
				AND  ABS(depositos-Retiros+conciliado) > 0.009  and batchconciliacion <> -1";
			
				
				$manyfinds = array();
				$manyfinds = explode(' o ',$_POST["searchtextBANCO"]);
				
				if ($_POST["searchtextBANCO"] != '*') {
					
					$sql = $sql." AND (";
					$primcond = 0;
					for ($mfind = 0;$mfind < count($manyfinds);$mfind++) {
						$primcond = $primcond + 1;
						if ($primcond == 1)
							$sql = $sql."Concepto like '%".TRIM($manyfinds[$mfind])."%'";
						else
							$sql = $sql." OR Concepto like '%".TRIM($manyfinds[$mfind])."%'";
					}
					$sql = $sql.")";
				}
				
				
				$sql = $sql." ORDER BY depositos-Retiros,Fecha";
				//echo $sql;
				
		} elseif ($Type=='*') {
			$sql = "SELECT estadoscuentabancarios.banktransid,
					estadoscuentabancarios.Concepto,
					estadoscuentabancarios.Retiros,
					estadoscuentabancarios.depositos,
					estadoscuentabancarios.conciliado,
					estadoscuentabancarios.Fecha,
					estadoscuentabancarios.legalid,
					IFNULL(estadoscuentabancarios.tagref,'') as tagref,
					estadoscuentabancarios.fechacambio,
					estadoscuentabancarios.usuario,
					tags.tagname
				FROM estadoscuentabancarios LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				$sql=$sql." WHERE ADDDATE(Fecha,".$_POST["rangodiasBANCO"].") >= '". $SQLAfterDate . "'
				AND SUBDATE(Fecha,".$_POST["rangodiasBANCO"].") <= '" . $SQLBeforeDate . "'
				
				AND ABS(depositos-Retiros) >= (" . ($_POST['buscamontoBANCO']). "+(" . ($_POST['rangomontoBANCO']*-1). ")) AND ABS(depositos-Retiros) <= (" . ($_POST['buscamontoBANCO']). "+". $_POST['rangomontoBANCO']. ")
				
				AND trim(cuenta)='" .$_POST["BankAccount"] . "'
				AND (estadoscuentabancarios.tagref = '".$_POST['tag']."' OR IFNULL(estadoscuentabancarios.tagref,'0') = '0'  OR IFNULL(estadoscuentabancarios.tagref,'') = '' OR  '".$_POST['tag']."' = '0')
				AND  ABS(depositos-Retiros+conciliado) > 0.009  and batchconciliacion <> -1";
				
				$manyfinds = array();
				$manyfinds = explode(' o ',$_POST["searchtextBANCO"]);
				
				if ($_POST["searchtextBANCO"] != '*') {
					
					$sql = $sql." AND (";
					$primcond = 0;
					for ($mfind = 0;$mfind < count($manyfinds);$mfind++) {
						$primcond = $primcond + 1;
						if ($primcond == 1)
							$sql = $sql."Concepto like '%".TRIM($manyfinds[$mfind])."%'";
						else
							$sql = $sql." OR Concepto like '%".TRIM($manyfinds[$mfind])."%'";
					}
					$sql = $sql.")";
				}
				
				$sql = $sql."
				ORDER BY Retiros-depositos,Fecha";
		}else { /* Type must == Receipts */
			$sql = "SELECT estadoscuentabancarios.banktransid,
					estadoscuentabancarios.Concepto,
					estadoscuentabancarios.Retiros,
					estadoscuentabancarios.depositos,
					estadoscuentabancarios.conciliado,
					estadoscuentabancarios.Fecha,
					estadoscuentabancarios.legalid,
					IFNULL(estadoscuentabancarios.tagref,'') as tagref,
					estadoscuentabancarios.fechacambio,
					estadoscuentabancarios.usuario,
					tags.tagname
				FROM estadoscuentabancarios LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
				WHERE depositos > 0
				AND ADDDATE(Fecha,".$_POST["rangodiasBANCO"].") >= '". $SQLAfterDate . "'
				AND SUBDATE(Fecha,".$_POST["rangodiasBANCO"].") <= '" . $SQLBeforeDate . "'
				
				AND ABS(depositos-Retiros) >= (" . ($_POST['buscamontoBANCO']). "+(" . ($_POST['rangomontoBANCO']*-1). ")) AND ABS(depositos-Retiros) <= (" . ($_POST['buscamontoBANCO']). "+". $_POST['rangomontoBANCO']. ")
				AND trim(cuenta)='" .$_POST["BankAccount"] . "
				AND (estadoscuentabancarios.tagref = '".$_POST['tag']."' OR IFNULL(estadoscuentabancarios.tagref,'0') = '0'  OR IFNULL(estadoscuentabancarios.tagref,'') = '' OR  '".$_POST['tag']."' = '0')
				AND  ABS(depositos-Retiros+conciliado) > 0.009  and batchconciliacion <> -1
				";
				
				$manyfinds = array();
				$manyfinds = explode(' o ',$_POST["searchtextBANCO"]);
				
				if ($_POST["searchtextBANCO"] != '*') {
					
					$sql = $sql." AND (";
					$primcond = 0;
					for ($mfind = 0;$mfind < count($manyfinds);$mfind++) {
						$primcond = $primcond + 1;
						if ($primcond == 1)
							$sql = $sql."Concepto like '%".TRIM($manyfinds[$mfind])."%'";
						else
							$sql = $sql." OR Concepto like '%".TRIM($manyfinds[$mfind])."%'";
					}
					$sql = $sql.")";
				}
				
				$sql = $sql."
				ORDER BY Retiros-depositos,Fecha";
		}

	//echo $sql;
	$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
	$PaymentsResult2 = DB_query($sql, $db, $ErrMsg);
	
	//echo $sql;
	
	$j = 1;  //page length counter
	$k=0; //row colour counter
	$banki = 1; //no of rows counter
	
	if (isset($_POST["chkall"]))
		$chkd = 'checked';
	else $chkd = '';
	
	while ($myrow=DB_fetch_array($PaymentsResult2)) {

		$DisplayTranDate = ConvertSQLDate($myrow['Fecha']);
		$Outstanding = ($myrow['depositos'] - $myrow['Retiros']) + $myrow['conciliado'];
		if (ABS($Outstanding)<0.009){ /*the payment is cleared dont show the check box*/

			$fechabanco="<input type=hidden name='BBTBanco_".$banki." VALUE='".$myrow['Fecha']."'>";
			
			printf("<tr bgcolor='#DDFFDD'>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small' nowrap>%s</td>
				<td class=number style='font-size:xx-small'>%s</td>
				<td class=number style='font-size:xx-small'>%s</td>
				<td class=number style='font-size:xx-small'>%s</td>
				<td colspan=2 style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>
					<input type='checkbox' ".$chkd." name='BanKUnclear_%s'>
						".$fechabanco." 
					<input type=hidden name='BBT_%s' VALUE=%s>
					
					<input type='submit' name='FindAuto_' value='->%s'>
				</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				</tr>",
				$myrow['tagname'],
				$myrow['Concepto'],
				$myrow['Fecha'],
				number_format($myrow['Retiros'],2),
				number_format($myrow['depositos'],2),
				number_format($Outstanding,2),
				_('Unclear'),
				$banki,
				$banki,
				$myrow['banktransid'],
				$banki,
				$myrow['usuario'],
				$myrow['fechacambio']
					
					);

		} else{
			
			
			if (substr($_POST['FindAuto_'],5) == $banki) {
				echo "<tr bgcolor='yellow'>";
				
				/* ACTUALIZA LOS CAMPOS PARA BUSQUEDA EN ERP DE ESTA LINEA */
				
				
				$_POST['searchtext'] = '*';			
				$_POST['buscamonto'] = round(abs($myrow['depositos']-$myrow['Retiros']));
				$_POST['rangomonto'] = round(abs(($myrow['Retiros']+$myrow['depositos']))*0.1);
				$_POST['rangodias'] = 5;
				
				
			}elseif ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			$fechabanco="<input type=hidden name='BBTBanco_".$banki." VALUE='".$myrow['Fecha']."'>";
			
			printf("<td>%s</td>
			       <td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small' nowrap>%s</td>
				<td style='font-size:xx-small' class=number>%s</td>
				<td style='font-size:xx-small' class=number>%s</td>
				<td style='font-size:xx-small' class=number>%s</td>
				<td style='font-size:xx-small'>
						".$fechabanco." 
					<input type='checkbox' ".$chkd." name='BKC_%s'><input type=hidden name='BBT_%s' VALUE=%s>
					<input type='submit' name='FindAuto_' value='->%s'>
				</td>
				<td style='font-size:xx-small' colspan=2><input type='text' maxlength=15 size=10 class=number name='BAC_%s'></td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small' nowrap>%s</td>
				</tr>",
				$myrow['tagname'],
				$myrow['Concepto'],
				$myrow['Fecha'],
				number_format($myrow['Retiros'],2),
				number_format($myrow['depositos'],2),
				number_format($Outstanding,2),
				$banki,
				$banki,
				$myrow['banktransid'],
				$banki,
				$banki,
				$myrow['usuario'],
				$myrow['fechacambio']);
		}

		$j++;
		If ($j == 5){
			$j=1;
			echo $TableHeader2;
		}
		//end of page full new headings if
		$banki++;
	}
	//end of while loop
	
	echo '</table></td></tr>
		</div>
		</td></tr>';
		
	echo '<tr><td colspan=11>';
	echo 'VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
	echo '</td></tr>';

	/*if (!isset($_POST['searchtext']))
		$_POST['searchtext'] = '*';
	
	if (!isset($_POST['buscamonto']))
		$_POST['buscamonto'] = '0.00';
		
	if (!isset($_POST['rangomonto']))
		$_POST['rangomonto'] = '1000';
				  
	if (empty($_POST['rangodias']))
		$_POST['rangodias'] = 5;
				  
	echo '<tr><td colspan=11 nowrap >';
	echo '	buscar x referencia:<input name="searchtext" type="text" size="20" value='.$_POST['searchtext'].'>
		buscar x monto:<input name="buscamonto" class=number type="text" size="8" value='.$_POST['buscamonto'].'>
		+/-:<input name="rangomonto" class=number type="text" size="6" value='.$_POST['rangomonto'].'>
		x tipo trans:
		';
		
	$sqltype = 'select banktrans.type, systypescat.typename
			from banktrans LEFT JOIN systypescat ON banktrans.type = systypescat.typeid
			group by banktrans.type';
	
	echo '<select Name="FromTipoTrans">';
	echo '<option  VALUE="-1" selected>Todos los tipos</option>';
	$tipos = DB_query($sqltype,$db);
	while ($myrowType=DB_fetch_array($tipos,$db)){
	    if ($_POST['FromTipoTrans']==$myrowType['type']){ 
		echo '<option  VALUE="' . $myrowType['type'] .  '" selected>' .$myrowType['typename'] .'</option>';
	    }else{
		echo '<option  VALUE="' . $myrowType['type'] .  '" >' .$myrowType['typename']. '</option>';
	    }
	}
	echo '</select>';
	echo '	+/- dias:<input name="rangodias" type="text" class=number size="3" value='.$_POST['rangodias'].'>';
	
	//echo '<input name="buscaMovsERP" type="button" class=number size="3" value="Buscar Movimientos" onClick="Procesarfa();">';
	echo '<input tabindex="6" onClick="Procesarfa();" type=button style="background-color:lightgreen" name="buscaMovsERPv2" VALUE="' . _('Buscar Movimientos ERP') .'">';	
	echo '<input tabindex="6" type=hidden  name="buscaMovsERP" >';	
	echo '    </td></tr>';	
	*/
	
	$TableHeader = '<tr>
			<th>'. _('Sucursal'). '</th>
			<th>' . _('Fecha') . '</th>
			<th>' . _('ID ERP') . '</th>
			<th>'. _('Beneficiario'). '</th>
			<th>'. _('Ref/Aut'). '</th>
			<th>' . $TypeName . '</th>
			<th>' . _('Cheque') . '</th>
			<th>' . _('Monto') . '</th>
			<th>' . _('Pendiente') . '</th>
			<th colspan=3>' . _('Clear') . ' / ' . _('Unclear') . '</th>
		</tr>';
		
	echo  '<tr> <td colspan=11 style="text-align:center; background-color:darkgray">'. _('MOVIMIENTOS GENERADOS EN EL SISTEMA ERP'). '</td>
			</tr>';
	$chk="";
	$cond = "and (batchconciliacion <> -1 OR batchconciliacion is null)";
	if ($_POST['movxconciliar']==1){
		$chk="checked";
		$cond = "and (batchconciliacion = 0 or batchconciliacion is null)";
	}
	echo  '<tr> <td colspan=11 ">'. _('Mostrar solo movimientos sin conciliar '). '&nbsp;<input type="checkbox" name="movxconciliar" value="1" '.$chk.'></td>
			</tr>';
	echo '	<tr VALIGN="top" >
		<td colspan=11>
		
		<div style="width: 1100px; height: 200px; overflow: auto; border: 2px dotted gray; background-color: #FFFFDD;">
	
		<table cellpadding=2 BORDER=0>';
		
	if ($Type=='Payments'){
		$sql = "SELECT banktransid,
				case when numautorizacion='' then ref else numautorizacion end as ref,
				amountcleared,
				transdate,
				amount/exrate as amt,
				banktranstype,
				chequeno,
				beneficiary,
				usuario,
				fechacambio,
				IFNULL(tags.tagname,'SIN SUCURSAL ASIGNADA') as tagname,
				transno,
				batchconciliacion
			FROM banktrans LEFT JOIN tags ON banktrans.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
							$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			WHERE amount < 0 
				$cond
				AND ADDDATE(transdate,".$_POST["rangodias"].") >= '". $SQLAfterDate . "'
				AND SUBDATE(transdate,".$_POST["rangodias"].") <= '" . $SQLBeforeDate . "'
				AND bankact = '" .$_POST["BankAccount"] . "'
				AND abs(amount/exrate) >= (" . ($_POST['buscamonto']*-1). "+(" . ($_POST['rangomonto']*-1). ")) AND abs(amount/exrate) <= (" . ($_POST['buscamonto']*-1). "+". $_POST['rangomonto']. ")
				AND (banktrans.tagref = '".$_POST["tag"]."' OR '0'='".$_POST["tag"]."')  ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
							$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
				AND (banktrans.type = '".$_POST["FromTipoTrans"]."' OR '-1' = '".$_POST["FromTipoTrans"]."')";
			
			$manyfinds = array();
			$manyfinds = explode(' o ',$_POST["searchtext"]);
			
			if ($_POST["searchtext"] != '*') {
				$sql = $sql." AND (";
				$primcond = 0;
				for ($mfind = 0;$mfind < count($manyfinds);$mfind++) {
					$primcond = $primcond + 1;
					if ($primcond == 1)
						$sql = $sql."ref like '%".TRIM($manyfinds[$mfind])."%'";
					else
						$sql = $sql." OR ref like '%".TRIM($manyfinds[$mfind])."%'";
				}
				$sql = $sql.")";
			}
				
			$sql = $sql. " ORDER BY amt,transdate"; //AND  ABS(amountcleared - (amount / exrate)) > 0.009
	} elseif ($Type=='*'){
		//echo "entraaaa".$_POST['legalid'];
		$sql = "SELECT banktransid,
				case when numautorizacion='' then ref else numautorizacion end as ref,
				amountcleared,
				transdate,
				(amount/exrate) as amt,
				banktranstype,
				chequeno,
				beneficiary,
				usuario,
				fechacambio,
				IFNULL(tags.tagname,'SIN SUCURSAL ASIGNADA') as tagname,
				transno,
				batchconciliacion
			FROM banktrans LEFT JOIN tags ON banktrans.tagref = tags.tagref ";
				if($_POST['legalid']!='-1'){
					$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			WHERE amount <> 0  
			$cond
			AND ADDDATE(transdate,".$_POST["rangodias"].") >= '". $SQLAfterDate . "'
			AND SUBDATE(transdate,".$_POST["rangodias"].") <= '" . $SQLBeforeDate . "'
			AND bankact='" .$_POST["BankAccount"] . "'
			AND abs(amount/exrate) >= (" . $_POST['buscamonto']. "+(" . $_POST['rangomonto']. ")*-1) AND abs(amount/exrate) <= (" . $_POST['buscamonto']. "+". $_POST['rangomonto']. ")
			AND (banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."') ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
							$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			AND (banktrans.type = '".$_POST["FromTipoTrans"]."' OR '-1' = '".$_POST["FromTipoTrans"]."')";
			
			$manyfinds = array();
			$manyfinds = explode(' o ',$_POST["searchtext"]);
			
			if ($_POST["searchtext"] != '*') {
				$sql = $sql." AND (";
				$primcond = 0;
				for ($mfind = 0;$mfind < count($manyfinds);$mfind++) {
					$primcond = $primcond + 1;
					if ($primcond == 1)
						$sql = $sql." case when numautorizacion='' then ref else numautorizacion end like '%".TRIM($manyfinds[$mfind])."%'";
					else
						$sql = $sql." OR case when numautorizacion='' then ref else numautorizacion end like '%".TRIM($manyfinds[$mfind])."%'";
				}
				$sql = $sql.")";
			}
			
			$sql = $sql. " ORDER BY amt desc,transdate"; //AND  ABS(amountcleared - (amount / exrate)) > 0.009
			//echo '<pre>sql2:'.$sql;
	}else { /* Type must == Receipts */
		$sql = "SELECT banktransid,
				case when numautorizacion='' then ref else numautorizacion end as ref,
				amountcleared,
				transdate,
				(amount/exrate) as amt,
				banktranstype,
				chequeno,
				beneficiary,
				usuario,
				fechacambio,
				IFNULL(tags.tagname,'SIN SUCURSAL ASIGNADA') as tagname,
				transno,
				batchconciliacion
			FROM banktrans LEFT JOIN tags ON banktrans.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			WHERE amount >0 
			$cond
			AND ADDDATE(transdate,".$_POST["rangodias"].") >= '". $SQLAfterDate . "'
			AND SUBDATE(transdate,".$_POST["rangodias"].") <= '" . $SQLBeforeDate . "'
			AND bankact='" .$_POST["BankAccount"] . "'
			AND abs(amount/exrate) >= (" . $_POST['buscamonto']. "+(" . $_POST['rangomonto']. "*-1)) AND abs(amount/exrate) <= (" . $_POST['buscamonto']. "+". $_POST['rangomonto']. ")
			AND (banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."') ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
							$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			AND (banktrans.type = '".$_POST["FromTipoTrans"]."' OR '-1' = '".$_POST["FromTipoTrans"]."')";
			
			$manyfinds = array();
			$manyfinds = explode(' o ',$_POST["searchtext"]);
			
			if ($_POST["searchtext"] != '*') {
				$sql = $sql." AND (";
				$primcond = 0;
				for ($mfind = 0;$mfind < count($manyfinds);$mfind++) {
					$primcond = $primcond + 1;
					if ($primcond == 1)
						$sql = $sql." case when numautorizacion='' then ref else numautorizacion end like '%".TRIM($manyfinds[$mfind])."%'";
					else
						$sql = $sql." OR case when numautorizacion='' then ref else numautorizacion end like '%".TRIM($manyfinds[$mfind])."%'";
				}
				$sql = $sql.")";
			}
			
			$sql = $sql. " ORDER BY amt desc,transdate"; //AND  ABS(amountcleared - (amount / exrate)) > 0.009
	}
	
//	echo '<pre><br>sql:'.$sql;
	
	$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
	$PaymentsResult = DB_query($sql, $db, $ErrMsg);


	echo $TableHeader;
	
	$j = 1;  //page length counter
	$k=0; //row colour counter
	$i = 1; //no of rows counter
	$ingresos = 0;
	$egresos = 0;
	
	while ($myrow=DB_fetch_array($PaymentsResult) AND (isset($_POST['buscaMovsERP'])  OR isset($_POST['FindAuto_']))) {

		$DisplayTranDate = ConvertSQLDate($myrow['transdate']);
		$Outstanding = $myrow['amt']- $myrow['amountcleared'];
		
		if($myrow['amt'] >= 0) {
			$ingresos += $myrow['amt'];
		} else {
			$egresos += abs($myrow['amt']);
		}
		$FechaContable="<input type=hidden name='BTContable_".$i." VALUE='".$myrow['transdate']."'>";
		
		if (ABS($Outstanding)<0.009){ /*the payment is cleared dont show the check box*/

			printf("<tr bgcolor='#DDFFDD'>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td class=number style='font-size:xx-small'>%s</td>
				<td class=number style='font-size:xx-small'>%s</td>
				<td colspan=2 style='font-size:xx-small'>%s<br>%s</td>
				<td style='font-size:xx-small'><input type='hidden' name='Unclear_%s'><input type=hidden name='BT_%s' VALUE=%s><br>%s</td>
				</tr>",
				$myrow['tagname'],
				$DisplayTranDate,
				$myrow['transno'],
				$myrow['beneficiary'],
				substr($myrow['ref'],0,90),
				$myrow['banktranstype'],
				$myrow['chequeno'],
				number_format($myrow['amt'],2),
				number_format($Outstanding,2),
				_('Unclear').'('.$myrow['batchconciliacion'].')',
				$myrow['usuario'],
				$i,
				$i,
				$myrow['banktransid'],
				$myrow['fechacambio']);

		} else{
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			printf("<td style='font-size:xx-small'>%s</td>
			        <td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small' class=number>%s</td>
				<td style='font-size:xx-small' class=number>%s</td>
				<td style='font-size:xx-small'><input type='checkbox' name='Clear_%s'><input type=hidden name='BT_%s' VALUE=%s></td>
				<td style='font-size:xx-small' colspan=2><input type='text' maxlength=15 size=15 class=number name='AmtClear_%s'></td>
				</tr>",
				$myrow['tagname'],
				$DisplayTranDate,
				$myrow['transno'],
				$myrow['beneficiary'],
				substr($myrow['ref'],0,90),
				$myrow['banktranstype'],
				$myrow['chequeno'],
				number_format($myrow['amt'],2),
				number_format($Outstanding,2),
				$i,
				$i,
				$myrow['banktransid'],
				$i
			);
		}

		$j++;
		If ($j == 7){
			$j=1;
			echo $TableHeader;
		}
	//end of page full new headings if
		$i++;
	}
	//end of while loop


	echo "<tr><td colspan='12' style='font-weight:bold; font-size:1em; text-align:right'>Ingresos:" . number_format($ingresos, 2) . ", Egresos: " . number_format($egresos, 2) . "</td></tr>";
	
	echo '</table>';
	echo '</div>';
	echo '</td></tr>';
	
	echo '	<tr><td colspan=12>
	<div class="centre"><input type=hidden name="RowCounter" value=' . $i . '>
	<input type=hidden name="BankRowCounter" value=' . $banki . '>';
	
	if(!isset($_POST['excel'])) {
		echo '<input type=submit name="Update" style="background-color:orange" VALUE="' . _('Actualiza Conciliacion') . '">';
	}
	
	echo '</div>
			</td></tr>';
	
	echo '	<tr><td colspan=11>
			<br><br>
		</td></tr>';
		
	echo  ' <tr><td colspan=11 style="text-align:center; background-color:darkgray">'. _('MOVIMIENTOS YA CONCILIADOS EN ESTE PERIODO (').$periododeMatch. ')</td></tr>';

	echo '	<tr><td colspan=11>
		<div style="width: 1100px; height: 250px; overflow: auto; border: 2px dotted gray; background-color: #FFFFDD;">
		<table cellpadding=2 BORDER=0>';
		
	
	/*****************************************************************************************************************/
	/*****************************************************************************************************************/
	/*****************************************************************************************************************/
	/***************************          NUEVA PANTALLA DE CONCILIADOS                        ***********************/
	/*****************************************************************************************************************/
	/*****************************************************************************************************************/
	/*****************************************************************************************************************/
	
	if (isset($_POST['xmes'])) {
		$SQLBeforeDate = $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-31';
		$SQLAfterDate = $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-01';	
	} else {
		$SQLBeforeDate = $fechaini;
		$SQLAfterDate = $fechaini;	
	}

	$sql = "SELECT periodno, lastdate_in_period
				FROM periods
				WHERE YEAR(lastdate_in_period) = ". $FromYear . " AND MONTH(lastdate_in_period) = ". $FromMes;
				
	$ErrMsg =  _('Could not retrieve transaction information');
	$resultCC = DB_query($sql,$db,$ErrMsg);
	$myrowCC=DB_fetch_array($resultCC);
	$periododeMatch = $myrowCC[0];

	if ($Type=='Payments'){

		$sql = "SELECT  banktrans.banktransid as ERP_banktransid, banktrans.ref, 
					banktrans.amountcleared, DATE_FORMAT(banktrans.transdate,'%Y/%m/%d') as transdate, 
					banktrans.amount/banktrans.exrate as amt, 
					banktrans.banktranstype, banktrans.chequeno, banktrans.beneficiary, banktrans.usuario, 
					banktrans.fechacambio, banktrans.tagref, IFNULL(tags.tagname,'SIN SUCURSAL ASIGNADA') as tagname,
					IFNULL(banktrans.batchconciliacion,0) as batch,IFNULL(sec_unegsxuser.userid,'') as unegasig
					
			FROM banktrans JOIN (
			
				select batchconciliacion, @rownum:=@rownum+1 as rank FROM (
					select banktrans.batchconciliacion, sum(banktrans.amount) as monto
				
					FROM banktrans LEFT JOIN tags ON banktrans.tagref = tags.tagref
					
				
					WHERE banktrans.batchconciliacion > 0 and banktrans.amount <0 AND (banktrans.matchperiodno = '". $periododeMatch . "' OR (year(banktrans.transdate) = ".$FromYear." and month(banktrans.transdate) = ".$FromMes.") ) AND 
						banktrans.bankact='" .$_POST["BankAccount"] . "' AND banktrans.batchconciliacion <> -1 AND
						(banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."')
						
					group by banktrans.batchconciliacion
					ORDER BY monto desc
				) user_rank, (SELECT @rownum:=0) r
			
			) as ranking ON banktrans.batchconciliacion = ranking.batchconciliacion
				LEFT JOIN tags ON banktrans.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
				LEFT JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			
			WHERE banktrans.batchconciliacion > 0 and banktrans.amount <0 AND (banktrans.matchperiodno = '". $periododeMatch . "' OR (year(banktrans.transdate) = ".$FromYear." and month(banktrans.transdate) = ".$FromMes.")) AND 
					banktrans.bankact='" .$_POST["BankAccount"] . "' AND banktrans.batchconciliacion <> -1 AND
					(banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."')";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			
			ORDER BY ranking.rank, banktrans.batchconciliacion, amt, banktrans.transdate";
			
	} elseif($Type=='*'){
		$sql = "SELECT  banktrans.banktransid as ERP_banktransid, banktrans.ref, 
					banktrans.amountcleared, DATE_FORMAT(banktrans.transdate,'%Y/%m/%d') as transdate, 
					banktrans.amount/banktrans.exrate as amt, 
					banktrans.banktranstype, banktrans.chequeno, banktrans.beneficiary, banktrans.usuario, 
					banktrans.fechacambio, banktrans.tagref, IFNULL(tags.tagname,'SIN SUCURSAL ASIGNADA') as tagname,
					IFNULL(banktrans.batchconciliacion,0) as batch, IFNULL(sec_unegsxuser.userid,'') as unegasig
			
			FROM banktrans JOIN (
			select batchconciliacion, @rownum:=@rownum+1 as rank FROM (
					select banktrans.batchconciliacion, sum(banktrans.amount) as monto
				
					FROM banktrans LEFT JOIN tags ON banktrans.tagref = tags.tagref
					
				
					WHERE banktrans.batchconciliacion > 0 and banktrans.amount <>0 AND (banktrans.matchperiodno = '". $periododeMatch . "' OR (year(banktrans.transdate) = ".$FromYear." and month(banktrans.transdate) = ".$FromMes.") ) AND 
						banktrans.bankact='" .$_POST["BankAccount"] . "' AND banktrans.batchconciliacion <> -1 AND
						(banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."')
						
					group by banktrans.batchconciliacion
					ORDER BY monto desc
				) user_rank, (SELECT @rownum:=0) r
			
			) as ranking ON banktrans.batchconciliacion = ranking.batchconciliacion
				LEFT JOIN tags ON banktrans.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
				LEFT JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE banktrans.batchconciliacion > 0 AND banktrans.amount <>0 AND (banktrans.matchperiodno = '". $periododeMatch . "' OR (year(banktrans.transdate) = ".$FromYear." and month(banktrans.transdate) = ".$FromMes.")) AND 
					banktrans.bankact='" .$_POST["BankAccount"] . "' AND  banktrans.batchconciliacion <> -1 AND
					(banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."') ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			
			ORDER BY ranking.rank, banktrans.batchconciliacion, amt,banktrans.transdate";
	} else { /* Type must == Receipts */
			
		$sql = "SELECT  banktrans.banktransid as ERP_banktransid, banktrans.ref, 
					banktrans.amountcleared, DATE_FORMAT(banktrans.transdate,'%Y/%m/%d') as transdate, 
					banktrans.amount/banktrans.exrate as amt, 
					banktrans.banktranstype, banktrans.chequeno, banktrans.beneficiary, banktrans.usuario, 
					banktrans.fechacambio, banktrans.tagref, IFNULL(tags.tagname,'SIN SUCURSAL ASIGNADA') as tagname,
					IFNULL(banktrans.batchconciliacion,0) as batch, IFNULL(sec_unegsxuser.userid,'') as unegasig
			
			FROM banktrans JOIN (
			select batchconciliacion, @rownum:=@rownum+1 as rank FROM (
					select banktrans.batchconciliacion, sum(banktrans.amount) as monto
				
					FROM banktrans LEFT JOIN tags ON banktrans.tagref = tags.tagref
					
				
					WHERE banktrans.batchconciliacion > 0 and banktrans.amount >0 AND (banktrans.matchperiodno = '". $periododeMatch . "' OR (year(banktrans.transdate) = ".$FromYear." and month(banktrans.transdate) = ".$FromMes.")) AND 
						banktrans.bankact='" .$_POST["BankAccount"] . "' AND banktrans.batchconciliacion <> -1 AND
						(banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."')
						
					group by banktrans.batchconciliacion
					ORDER BY monto desc
				) user_rank, (SELECT @rownum:=0) r
			
			) as ranking ON banktrans.batchconciliacion = ranking.batchconciliacion
				LEFT JOIN tags ON banktrans.tagref = tags.tagref ";
				/*if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				*/
				$sql= $sql."
				LEFT JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE banktrans.batchconciliacion > 0 AND banktrans.amount >0 AND (banktrans.matchperiodno = '". $periododeMatch . "' OR (year(banktrans.transdate) = ".$FromYear." and month(banktrans.transdate) = ".$FromMes.")) AND 
					banktrans.bankact='" .$_POST["BankAccount"] . "' AND banktrans.batchconciliacion <> -1 AND
					(banktrans.tagref = '".$_POST["tag"]."' OR '0' = '".$_POST["tag"]."') ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
						ORDER BY ranking.rank, banktrans.batchconciliacion, amt,banktrans.transdate";
	}

	//echo "<pre>$sql";exit;
	
	$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
	//echo "<pre>$sql";
	$PaymentsResult = DB_query($sql, $db, $ErrMsg);

	$TableHeader1 = '<tr>
			<th colspan=10><b>'. _('MOVIMIENTOS CONCILIADOS'). '</b></th>
			</tr>';
		
	$TableHeader = '<tr>
			<th><b>'. _('BCH'). '</th>
			<th><b>'. _('Suc'). '</th>
			<th><b>' . _('Fecha') . '</th>
			<th><b>'. _('Para'). '</th>
			<th><b>'. _('Ref'). '</th>
			<th><b>' . $TypeName . '</th>
			<th><b>' . _('Chq') . '</th>
			<th><b>' . _('Monto') . '</th>
			<th><b>' . _('Eliminar') . '</th>			
			<th><b>'. _('Cambio'). '</th>
		</tr>';
		
	echo '<tr VALIGN="top" ><td><table cellpadding=2 BORDER=1>' . $TableHeader1. $TableHeader;


	$j = 1;  //page length counter
	$k=0; //row colour counter
	$i = 1; //no of rows counter
	
	$acumbatch = 0;
	$acumbatch2 = 0;
			
	$antbatch = '0';
	$acumbatch = 0;
	while ($myrow=DB_fetch_array($PaymentsResult)) {

		$DisplayTranDate = $myrow['transdate'];
		$Outstanding = $myrow['amt']- $myrow['amountcleared'];
		
		if ($antbatch != $myrow['batch'] AND $antbatch > '0') {
				   
			$sql = "SELECT  estadoscuentabancarios.banktransid as CTA_banktransid, estadoscuentabancarios.Concepto, estadoscuentabancarios.Retiros, 
					estadoscuentabancarios.depositos, estadoscuentabancarios.conciliado, DATE_FORMAT(estadoscuentabancarios.Fecha,'%Y/%m/%d') as Fecha,
					estadoscuentabancarios.legalid, IFNULL(estadoscuentabancarios.tagref,'') as EDOCTA_tagref, estadoscuentabancarios.fechacambio, 
					estadoscuentabancarios.usuario as usuario, tags.tagname as EDOCTA_tagname, estadoscuentabancarios.batchconciliacion as EDOCTA_batch,
					IFNULL(s.userid,0) as asignado
				
				FROM estadoscuentabancarios
					LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
					LEFT JOIN sec_unegsxuser s ON tags.tagref = s.tagref and s.userid = '" . $_SESSION['UserID'] . "'
				WHERE estadoscuentabancarios.batchconciliacion = '".$antbatch."' AND estadoscuentabancarios.batchconciliacion <> -1
				ORDER BY estadoscuentabancarios.batchconciliacion /*, estadoscuentabancarios.Fecha*/";
			$ErrMsg = _('Movimientos del Estado de cuenta no pudieron ser recuperados');
			$PaymentsResult2 = DB_query($sql, $db, $ErrMsg);
			while ($myrow2=DB_fetch_array($PaymentsResult2)) {
				
				
				printf("<tr bgcolor='#00dd6f'>
				        <td style='font-size:xx-small'>--></td>
					<td style='font-size:xx-small'></td>
					<td style='font-size:xx-small'>%s</td>
					<td style='font-size:xx-small' colspan=2>%s</td>
					<td style='font-size:xx-small'></td>
					<td style='font-size:xx-small'></td>
					<td style='font-size:xx-small'>%s</td>
					
					<td style='font-size:xx-small' class=number></td>
					<td style='font-size:xx-small'></td>
					</tr>
					",
					$myrow2['Fecha'],
					substr($myrow2['Concepto'],0,90),
					number_format($myrow2['depositos']-$myrow2['Retiros'],2)
				);
				$acumbatch = $acumbatch + $myrow2['conciliado'];
				
			}
		//	echo '<pre><br>ac1:'.$antbatch.' XXXX:'.$acumbatch;
			
			printf("<tr bgcolor='#FFFFFF'>
				<td style='font-size:xx-small'></td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:meddium;text-align:right'><b>%s</b></td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:meddium;text-align:right'><b>%s</b></td>
				<td colspan=2 style='font-size:xx-small'></td>
				</tr>
				",
				$antbatch,
				number_format($acumbatch,2));
			
			echo $TableHeader;
		
			
			$acumbatch = 0;
			$acumbatch2 = 0;
		}
		
		if (ABS($Outstanding)<0.009){ /*the payment is cleared dont show the check box*/
			/*
			printf("<tr bgcolor='#ffb366'>
				<td style='font-size:meddium'><b>%s</b></td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>%s</td>
				<td class=number style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small'>**<input type='checkbox' name='ConciliadoUnclear_%s'><input type=hidden name='MCBT_%s' VALUE=%s><br>%s</td>
				",
				$myrow['batch'].'<br>ERP:'.$myrow['ERP_banktransid'],
				$myrow['tagname'],
				$DisplayTranDate,
				substr($myrow['beneficiary'],0,90),
				substr($myrow['ref'],0,90),
				$myrow['banktranstype'],
				$myrow['chequeno'],
				number_format($myrow['amt'],2),
				$i,
				$i,
				$myrow['batch'],
				'');
			*/
			
			echo "<tr bgcolor='#ffb366'>";
			echo "<td style='font-size:meddium'><b>" . $myrow['batch'].'<br>ERP:'.$myrow['ERP_banktransid'] . "</b></td>";
			echo "<td style='font-size:xx-small'>" . $myrow['tagname'] . "</td>";
			echo "<td style='font-size:xx-small'>" . $DisplayTranDate . "</td>";
			echo "<td style='font-size:xx-small'>" . substr($myrow['beneficiary'],0,90) . "</td>";
			echo "<td style='font-size:xx-small'>" . substr($myrow['ref'],0,90) . "</td>";
			echo "<td style='font-size:xx-small'>" . $myrow['banktranstype'] . "</td>";
			echo "<td style='font-size:xx-small'>" . $myrow['chequeno'] . "</td>";
			echo "<td class=number style='font-size:xx-small'>" . number_format($myrow['amt'],2) . "</td>";
			
			if ($myrow['unegasig'] != ''){
				echo "<td style='font-size:xx-small'><input type='checkbox' name='ConciliadoUnclear_" . $i . "'><input type=hidden name='MCBT_" . $i . "' VALUE='" . $myrow['batch'] . "'><br>" . '' . "</td>";
			}else{
				if (!Havepermission($_SESSION['UserID'],553, $db)){
					echo "<td style='font-size:xx-small'>&nbsp;</td>";
				}else{
					echo "<td style='font-size:xx-small'><input type='checkbox' name='ConciliadoUnclear_" . $i . "'><input type=hidden name='MCBT_" . $i . "' VALUE='" . $myrow['batch'] . "'><br>" . '' . "</td>";	
				}	
			}
			
			
			
			printf("
				<td style='font-size:xx-small'>%s</td>
				</tr>",
				$myrow['usuario'].'<br>'.$myrow['fechacambio']);

		} 

		$antbatch = $myrow['batch'];
		
		$antERPid = $myrow['ERP_banktransid'];
		//$antCTAid = $myrow['CTA_banktransid'];
		
		$acumbatch = $acumbatch + $myrow['amt'];
		$acumbatch2 = $acumbatch2 + $myrow['conciliado'];
			
		
		
		//end of page full new headings if
		$i++;
	}
	
	//end of while loop
	
	if ($antbatch > '0') {
			   
		$sql = "SELECT  	estadoscuentabancarios.banktransid as CTA_banktransid, estadoscuentabancarios.Concepto, estadoscuentabancarios.Retiros, 
					estadoscuentabancarios.depositos, estadoscuentabancarios.conciliado, DATE_FORMAT(estadoscuentabancarios.Fecha,'%Y/%m/%d') as Fecha,
					estadoscuentabancarios.legalid, IFNULL(estadoscuentabancarios.tagref,'') as EDOCTA_tagref, estadoscuentabancarios.fechacambio, 
					estadoscuentabancarios.usuario as usuario, tags.tagname as EDOCTA_tagname, estadoscuentabancarios.batchconciliacion as EDOCTA_batch 
			
			FROM estadoscuentabancarios LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
				if($_POST['legalid']!=-1 and isset($_POST['legalid'])){
						$sql=$sql." AND tags.legalid=".$_POST['legalid'];
				}
				
				$sql= $sql."
			
			
			WHERE estadoscuentabancarios.batchconciliacion = '".$antbatch."' AND estadoscuentabancarios.batchconciliacion <> -1 
			ORDER BY estadoscuentabancarios.batchconciliacion/*, estadoscuentabancarios.Fecha*/";
		
		$ErrMsg = _('Movimientos del Estado de cuenta no pudieron ser recuperados');
		$PaymentsResult2 = DB_query($sql, $db, $ErrMsg);
		while ($myrow2=DB_fetch_array($PaymentsResult2)) {
			
			
			printf("<tr bgcolor='#00dd6f'>
				<td style='font-size:xx-small'>--></td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:xx-small'>%s</td>
				<td style='font-size:xx-small' colspan=2>%s</td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:xx-small'></td>
				<td style='font-size:xx-small'>%s</td>
				
				<td style='font-size:xx-small' class=number></td>
				<td style='font-size:xx-small'></td>
				</tr>
				",
				$myrow2['Fecha'],
				$myrow2['Concepto'],
				number_format($myrow2['depositos']-$myrow2['Retiros'],2)
			);
			$acumbatch = $acumbatch + $myrow2['conciliado'];
			
		}
		//echo '<pre><br>ac2:'.$antbatch.'XXX:'.$acumbatch;
		if($acumbatch!=0){
			$bgcolorx='orange';
		}else{
			$bgcolorx='#FFFFFF';
		}
		printf("<tr bgcolor='$bgcolorx'>
			<td style='font-size:xx-small'></td>
			<td style='font-size:xx-small'></td>
			<td style='font-size:xx-small'></td>
			<td style='font-size:xx-small'></td>
			<td style='font-size:xx-small'></td>
			<td style='font-size:meddium;text-align:right'><b>%s</b></td>
			<td style='font-size:xx-small'></td>
			<td style='font-size:meddium;text-align:right'><b>%s</b></td>
			<td colspan=2 style='font-size:xx-small'></td>
			</tr>
			",
			$antbatch,
			number_format($acumbatch,2));//
	}
	
	echo '</table>';
	echo '</td></tr>';
	
	echo '</table>';
	echo '</div>';
	
	echo '</td></tr>';
	
	echo '<tr><td colspan=11><div class="centre"><input type=hidden name="ConciliadoRowCounter" value=' . $i . '>
		<input type=hidden name="ConciliadoBankRowCounter" value=' . $banki . '>';
	
	if(!isset($_POST['excel'])) {
		echo '<input type=submit name="Update" style="background-color:orange" VALUE="' . _('Elimina Conciliaciones Seleccionadas') . '">';
	}
	
	
	echo '</div></td></tr></table>';
		
	
}

echo '</form>';
if(!isset($_POST['excel'])) {
	include('includes/footer.inc');
}
?>
