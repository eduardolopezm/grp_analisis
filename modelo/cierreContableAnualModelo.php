<?php
/**
 * Resguardo de activo fijo.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /resguardo_detalles_modelo.php
 * Fecha Creación: 05.04.18
 * Se genera el presente programa para la visualización de la información
 * del detalle de los resguardos.
 */
//

$PageSecurity = 15;
$PathPrefix = '../';
$funcion=1999;

//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option =$_POST['option'];
$enc = new Encryption;

$oficinaCentral = '09';

if($option == "obtenerAniosCierre"){

	$SQL = "SELECT distinct date_format(lastdate_in_period,'%Y') as anio
		FROM periods 
		WHERE date_format(lastdate_in_period,'%Y') <= date_format(Now(),'%Y')
		ORDER BY date_format(lastdate_in_period,'%Y') desc";
	
    $ErrMsg = "No se obtuvo anios de cierre";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if (DB_num_rows($TransResult) > 0) {
        while ($row = DB_fetch_array($TransResult)) {
            //$contenido[] = [ 'val'=>$row['anio'], 'text'=>$row['anio']];
             $info[] = array( 'val' => $row ['anio'], 'text' => $row ['anio']);
        }
        $result = true;
    } else {
        $result = false;
    }
    $contenido = array('datos' => $info);

}

if($option == "cierreContable"){
	$Mensaje="";

	$PeriodNo = GetPeriod('31/12/'.$_POST['anio'], $db);

	$ur = $_POST['ur'];
	
	$ue = $_POST['ue'];

	$nu_folio_ue = "999999".$ue;

	$montoCierre=0;

	if($PeriodNo != -999){
		$PeriodNoant = $PeriodNo;
		$PeriodNo = $PeriodNo+.5;

		$sqlp = "SELECT *
				FROM periods
				WHERE periodno = " . $PeriodNo;
		$resultp = DB_query($sqlp, $db);
		if ($myrowp = DB_fetch_array($resultp)){

		}else{
			$isql = "INSERT INTO periods(periodno, lastdate_in_period) 
					VALUES('" . $PeriodNo . "','" . ($_POST['anio'] ."-12-31") . "')";
			$ErrMsg = _('No se pudo insertar el periodo ' . $PeriodNo . 'en la tabla de periodos');
			$DbgMsg = _('El sql ejecutado es: ');
			$result = DB_query($isql,$db,$ErrMsg,$DbgMsg,true);
		}

		$SQL="DELETE FROM gltrans 
			  WHERE type in (999,990,991,992,993,994,995,996,997) AND periodno in (".$PeriodNo.",".$PeriodNoant.") 
			  		and narrative not like '%CONSOLIDACION%'
			  		AND tag = '".$_POST['ur']."' and ln_ue = '".$ue."' ";
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		if($result){
			$PeriodApertura = GetPeriod('01/01/'.($_POST['anio'] + 1), $db);
			$SQL="DELETE FROM  gltrans WHERE type = 0  AND narrative LIKE 'POLIZA DE TRASPASO DEL RESULTADO DEL EJERCICIO A RESULTADO DEL EJERCICIOS ANTERIORES DEL%' AND periodno = '".$PeriodApertura."' AND tag = '".$_POST['ur']."' and ln_ue = '".$ue."';";
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			$SQL="DELETE FROM  gltrans WHERE type = 0  AND narrative LIKE 'POLIZA DE APERTURA%' AND periodno = '".$PeriodApertura."' AND tag = '".$_POST['ur']."' and ln_ue = '".$ue."';";
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

		//$result = DB_Txn_Begin($db);
		

		$procesoIngreso = "";
		$montoIngreso  = 0;
		$msjIngreso = "";
		list($procesoIngreso, $montoIngreso, $msjIngreso) =   fnCierreIngreso($_POST['ur'], $_POST['ue'], $PeriodNo, $_POST['anio'], $db);

		$procesoGasto = "";
		$montoGasto  = 0;
		$msjGasto = "";
		list($procesoGasto, $montoGasto, $msjGasto) =   fnCierreGasto($_POST['ur'], $_POST['ue'], $PeriodNo, $_POST['anio'], $db);

		$procesoTraspaso = "";
		$montoTraspaso  = 0;
		$msjTraspaso = "";
		list($procesoTraspaso, $montoTraspaso, $msjTraspaso) =   fnTraspasoResultado($_POST['ur'], $_POST['ue'], $PeriodNo, ($montoIngreso + $montoGasto), $_POST['anio'], $db);

		$procesoTraspasoBalance = "";
		$montoTraspasoBalance  = 0;
		$msjTraspasoBalance = "";
		list($procesoTraspasoBalance, $montoTraspasoBalance, $msjTraspasoBalance) =   fnTraspasoResultadoBalance($_POST['ur'], $_POST['ue'], $PeriodNo, ($montoIngreso + $montoGasto), $_POST['anio'], $db);


		$procesoBalance = "";
		$montoBalance  = 0;
		$msjBalance = "";
		list($procesoBalance, $montoBalance, $msjBalance) = fnCierreAnualDeCuentas($_POST['ur'], $_POST['ue'], $PeriodNo, $_POST['anio'], $db, 'balance', '',994, '99994'.$_POST['ue'], 'POLIZA DE CIERRE DE BALANCE DEL AÑO '. $_POST['anio']);

		$procesoApertura = "";
		$montoApertura  = 0;
		$msjApertura = "";
		list($procesoApertura, $montoApertura, $msjApertura) = fnPolizasApertura($_POST['ur'], $_POST['ue'], $PeriodNo, $_POST['anio'], $db, 'apertura', '',995, '99995'.$_POST['ue'], 'POLIZA DE APERTURA DEL AÑO '. ($_POST['anio'] + 1));


		$procesoAperturaTraspaso = "";
		$montoAperturaTraspaso  = 0;
		$msjAperturaTraspaso = "";
		list($procesoAperturaTraspaso, $montoAperturaTraspaso, $msjAperturaTraspaso) = fnPolizasAperturaResultadoEjercicio($_POST['ur'], $_POST['ue'], $PeriodNo,($montoIngreso + $montoGasto), $_POST['anio'], $db, '', '',996, '99996'.$_POST['ue'], '');



		$Mensaje .= $msjIngreso . $msjGasto. $msjTraspaso . $msjTraspasoBalance . $msjBalance . $msjApertura . $msjAperturaTraspaso;
		
	}else{
		$Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El periodo para al año ' . $_POST['anio'] . ' se encuentra cerrado.</p>';
		$TransResult=true;
	}	
}

function fnCierreIngreso($ur, $ue, $PeriodNo, $anio, $db){
	$blnProceso = false; 
	$dblMonto=0;

	$SQL = "SELECT gltrans.account, gltrans.tag, sum(gltrans.amount) as amount
			FROM gltrans 
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode 
			JOIN periods ON gltrans.periodno = periods.periodno
			JOIN tags ON gltrans.tag = tags.tagref	
			WHERE   gltrans.tag = '".$ur."' 
				AND gltrans.ln_ue = '".$ue."'
				AND chartmaster.nu_nivel = 9 and chartmaster.ln_clave = '".$ue."'
			 	AND date_format(periods.lastdate_in_period,'%Y') = '".$anio."' 
				AND gltrans.account like '4%'
			GROUP BY gltrans.account, gltrans.tag
			HAVING sum(gltrans.amount)!=0
			ORDER BY gltrans.tag, gltrans.account";
	
	$TransResult = DB_query($SQL,$db);

	if(DB_num_rows($TransResult) > 0){
		
		$TransNo = GetNextTransNo(990, $db);
		$TipoPoliza = 990;
		$nu_folio_ue = "999990".$ue;
		$accountAcumuladora = "6.1.1.2.1.".$ue.".0001.0001.0001";

		while ($ChartRow = DB_fetch_array($TransResult)) {
			$dblMonto +=$ChartRow['amount'];
			/*MOVIMIENTO INVERSO AL SALDO*/
			$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$TipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $ChartRow['account'] . "',
								'POLIZA DE CIERRE ANUAL DE INGRESO DEL AO ".$anio."',
								". ($ChartRow['amount'] * -1) .",
								'".$ChartRow['tag']."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$nu_folio_ue."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la polliza de cierre anual de ingresos');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result = DB_query($SQL,$db);

			if($result){

				/*CUENTA ACUMULADORA*/
				$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$TipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $accountAcumuladora . "',
								'POLIZA DE CIERRE ANUAL DE INGRESO DEL AO ".$anio."',
								". ($ChartRow['amount']) .",
								'".$ChartRow['tag']."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$nu_folio_ue."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de cierre anual de ingresos');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result2 = DB_query($SQL,$db);
			}

			$Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> Se genero la Poliza de cierre anual de ingresos con folio:  <b>'.$TransNo.'</b> .</p>';
			$blnProceso = true;

		}
	}else{
		$Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontraron movimientos contables para la poliza de cierre anual de ingresos.</p>';
	}

	return array($blnProceso, $dblMonto, $Mensaje);

}


function fnCierreGasto($ur, $ue, $PeriodNo, $anio, $db){
	$blnProceso = false; 
	$dblMonto=0;

	$SQL = "SELECT gltrans.account, gltrans.tag, sum(gltrans.amount) as amount
			FROM gltrans 
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode 
			JOIN periods ON gltrans.periodno = periods.periodno
			JOIN tags ON gltrans.tag = tags.tagref	
			WHERE   gltrans.tag = '".$ur."' 
				AND gltrans.ln_ue = '".$ue."'
				AND chartmaster.nu_nivel = 9 and chartmaster.ln_clave = '".$ue."'
			 	AND date_format(periods.lastdate_in_period,'%Y') = '".$anio."' 
				AND gltrans.account like '5%'
			GROUP BY gltrans.account, gltrans.tag
			HAVING sum(gltrans.amount)!=0
			ORDER BY gltrans.tag, gltrans.account";
	
	$TransResult = DB_query($SQL,$db);

	if(DB_num_rows($TransResult) > 0){
		
		$TransNo = GetNextTransNo(991, $db);
		$TipoPoliza = 991;
		$nu_folio_ue = "999991".$ue;
		$accountAcumuladora = "6.1.1.2.1.".$ue.".0001.0001.0001";

		while ($ChartRow = DB_fetch_array($TransResult)) {
			$dblMonto +=$ChartRow['amount'];
			/*MOVIMIENTO INVERSO AL SALDO*/
			$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$TipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $ChartRow['account'] . "',
								'POLIZA DE CIERRE ANUAL DE INGRESO DEL AO ".$anio."',
								". ($ChartRow['amount'] * -1) .",
								'".$ChartRow['tag']."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$nu_folio_ue."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la polliza de cierre anual de ingresos');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result = DB_query($SQL,$db);

			if($result){

				/*CUENTA ACUMULADORA*/
				$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$TipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $accountAcumuladora . "',
								'POLIZA DE CIERRE ANUAL DE INGRESO DEL AO ".$anio."',
								". ($ChartRow['amount']) .",
								'".$ChartRow['tag']."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$nu_folio_ue."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de cierre anual de ingresos');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result2 = DB_query($SQL,$db);
			}

			$Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> Se genero la Poliza de cierre anual de gasto con folio:  <b>'.$TransNo.'</b> .</p>';
			$blnProceso = true;

		}
	}else{
		$Mensaje .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontraron movimientos contables para la poliza de cierre anual de gastos.</p>';
	}

	return array($blnProceso, $dblMonto, $Mensaje);

}

function fnTraspasoResultado($ur, $ue, $PeriodNo,$monto, $anio, $db){
	$TransNo = GetNextTransNo(992, $db);
	$TipoPoliza = 992;
	$nu_folio_ue = "999992".$ue;

	$accountAcumuladora = "6.1.1.2.1.".$ue.".0001.0001.0001";
	$accountAcreedora = "6.2.1.2.1.".$ue.".0001.0001.0001";
	$accountDeudora = "6.3.1.2.1.".$ue.".0001.0001.0001";

	$cuentaFinal=$accountAcreedora;

	if($monto > 0){
		$cuentaFinal=$accountDeudora;
	}

	$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$TipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $accountAcumuladora . "',
								'POLIZA DE TRASPASO DE RESULTADO DE EJERCICIO DEL AÑO ".$anio."',
								". ($monto * -1) .",
								'".$ur."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$nu_folio_ue."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de traspaso del resultado del ejercicio');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result2 = DB_query($SQL,$db);


	$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$TipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $cuentaFinal . "',
								'POLIZA DE TRASPASO DE RESULTADO DE EJERCICIO DEL AÑO ".$anio."',
								". ($monto) .",
								'".$ur."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$nu_folio_ue."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de traspaso del resultado del ejercicio');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result2 = DB_query($SQL,$db);

	$Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> Se genero la Poliza de traspaso de resultado del ejercicio con folio:  <b>'.$TransNo.'</b> .</p>';
	$blnProceso = true;

	return array($blnProceso, $dblMonto, $Mensaje);
}



function fnTraspasoResultadoBalance($ur, $ue, $PeriodNo,$monto, $anio, $db){
	$blnProceso = false;
	$TransNo = GetNextTransNo(993, $db);
	$TipoPoliza = 993;
	$nu_folio_ue = "999993".$ue;
	$dblMonto=0;
	$accountResultadoBalance = "3.2.1.1.1.".$ue.".0001.0001.0001";
	$accountAcreedora = "6.2.1.2.1.".$ue.".0001.0001.0001";
	$accountDeudora = "6.3.1.2.1.".$ue.".0001.0001.0001";

	$cuenta1="";
	$cuenta2="";

	if( ($monto) >0){
		$cuenta2=$accountDeudora;
		$cuenta1=$accountResultadoBalance;
	}else{
		$cuenta1=$accountAcreedora;
		$cuenta2=$accountResultadoBalance;
	}


	$SQL = 'INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						userid,
						posted,
						ln_ue,
						nu_folio_ue) ';
		$SQL= $SQL . "VALUES (
						".$TipoPoliza.",
						". $TransNo .",
						'" . $anio ."-12-31',
						" . ($PeriodNo) . ",
						'" . $cuenta1 . "',
						'POLIZA DE TRASPASO DE RESULTADO DE EJERCICIO A BALANCE DEL AÑO ".$anio."',
						". ($monto) .",
						'".$ur."',
						'".$_SESSION['UserID']."',
						'1',
						'".$ue."',
						'".$nu_folio_ue."'
					)";
	$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de traspaso del resultado del ejercicio');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	$result2 = DB_query($SQL,$db);

	$SQL = 'INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						userid,
						posted,
						ln_ue,
						nu_folio_ue) ';
		$SQL= $SQL . "VALUES (
						".$TipoPoliza.",
						". $TransNo .",
						'" . $anio ."-12-31',
						" . ($PeriodNo) . ",
						'" . $cuenta2 . "',
						'POLIZA DE TRASPASO DE RESULTADO DE EJERCICIO A BALANCE DEL AÑO ".$anio."',
						". ($monto * -1) .",
						'".$ur."',
						'".$_SESSION['UserID']."',
						'1',
						'".$ue."',
						'".$nu_folio_ue."'
					)";
	$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de traspaso del resultado del ejercicio');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	$result2 = DB_query($SQL,$db);


	$Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> Se genero la Poliza de traspaso de resultado del ejercicio a balance con folio:  <b>'.$TransNo.'</b> .</p>'; 

	$blnProceso = true;

	return array($blnProceso, $dblMonto, $Mensaje);
}


function fnCierreAnualDeCuentas($ur, $ue, $PeriodNo, $anio, $db, $tipoCierre, $cuentaAcumuladora,$tipoPoliza, $folioUE, $narrativa){
	$cuentas = "";
	$blnProceso = false;
	$Mensaje="";

	switch ($tipoCierre) {
	    case 'ingreso':
	        $cuentas = " AND gltrans.account like '4%' ";
	        break;
	    case 'gasto':
	        $cuentas = " AND gltrans.account like '5%' ";
	        break;
	    case 'balance':
	        $cuentas = "AND (gltrans.account like '1%' or gltrans.account like '2%' or gltrans.account like '3%' or gltrans.account like '7%' )";
	        break;
	}

	$SQL = "SELECT gltrans.account, gltrans.tag, sum(gltrans.amount) as amount
			FROM gltrans 
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode 
			JOIN periods ON gltrans.periodno = periods.periodno
			JOIN tags ON gltrans.tag = tags.tagref	
			WHERE   gltrans.tag = '".$ur."' 
				AND gltrans.ln_ue = '".$ue."'
				AND chartmaster.nu_nivel = 9 and chartmaster.ln_clave = '".$ue."'
			 	AND date_format(periods.lastdate_in_period,'%Y') = '".$anio."' 
				".$cuentas."
			GROUP BY gltrans.account, gltrans.tag
			HAVING sum(gltrans.amount)!=0
			ORDER BY gltrans.tag, gltrans.account";
	
	$TransResult = DB_query($SQL,$db);

	if(DB_num_rows($TransResult) > 0){
		$TransNo = GetNextTransNo($tipoPoliza, $db);

		while ($ChartRow = DB_fetch_array($TransResult)) {
			$dblMonto +=$ChartRow['amount'];
			/*MOVIMIENTO INVERSO AL SALDO*/
			$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$tipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $ChartRow['account'] . "',
								'".$narrativa."',
								". ($ChartRow['amount'] * -1) .",
								'".$ChartRow['tag']."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$folioUE."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la polliza de cierre anual de ingresos');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result = DB_query($SQL,$db);

			if($cuentaAcumuladora !=""){

				/*CUENTA ACUMULADORA*/
				$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								".$TipoPoliza.",
								". $TransNo .",
								'" . $anio ."-12-31',
								" . ($PeriodNo) . ",
								'" . $cuentaAcumuladora . "',
								'".$narrativa."',
								". ($ChartRow['amount']) .",
								'".$ChartRow['tag']."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$folioUE."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento del cierre anual');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result2 = DB_query($SQL,$db);
			}

			$blnProceso = true;
			switch ($tipoCierre) {
			    case 'ingreso':
			        
			        break;
			    case 'gasto':
			        
			        break;
			    case 'balance':
			        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> Se genero la Poliza de cierre anual de balance con folio:  <b>'.$TransNo.'</b> .</p>';
			        break;
			}

		}
	}

	return array($blnProceso, $dblMonto, $Mensaje);
}


function fnPolizasApertura($ur, $ue, $PeriodNo, $anio, $db, $tipoCierre, $cuentaAcumuladora,$tipoPoliza, $folioUE, $narrativa){
	$cuentas = "";
	$blnProceso = false;
	$Mensaje="";

	switch ($tipoCierre) {
	    case 'ingreso':
	        $cuentas = " AND gltrans.account like '4%' ";
	        break;
	    case 'gasto':
	        $cuentas = " AND gltrans.account like '5%' ";
	        break;
	    case 'apertura':
	        $cuentas = "AND (gltrans.account like '1%' or gltrans.account like '2%' or gltrans.account like '3%' or gltrans.account like '7%' )";
	        break;
	}

	$SQL = "SELECT gltrans.account, gltrans.tag, sum(gltrans.amount) as amount
			FROM gltrans 
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode 
			JOIN periods ON gltrans.periodno = periods.periodno
			JOIN tags ON gltrans.tag = tags.tagref	
			WHERE   gltrans.tag = '".$ur."' 
				AND gltrans.ln_ue = '".$ue."'
				AND chartmaster.nu_nivel = 9 and chartmaster.ln_clave = '".$ue."'
			 	AND date_format(periods.lastdate_in_period,'%Y') = '".$anio."' 
			 	AND gltrans.periodno = ".$PeriodNo."
			 	AND gltrans.type = 994
				".$cuentas."

			GROUP BY gltrans.account, gltrans.tag
			HAVING sum(gltrans.amount)!=0
			ORDER BY gltrans.tag, gltrans.account";
	
	$TransResult = DB_query($SQL,$db);

	if(DB_num_rows($TransResult) > 0){
		$TransNo = GetNextTransNo($tipoPoliza, $db);
		
		$PeriodNoApertura = GetPeriod('01/01/'.($anio + 1), $db);
		$sqlp = "SELECT *
				FROM periods
				WHERE periodno = " . $PeriodNoApertura;
		$resultp = DB_query($sqlp, $db);
		if ($myrowp = DB_fetch_array($resultp)){

		}else{
			$isql = "INSERT INTO periods(periodno, lastdate_in_period) 
					VALUES('" . $PeriodNoApertura . "','" . (($anio+1) ."-01-01") . "')";
			$ErrMsg = _('No se pudo insertar el periodo ' . $PeriodNoApertura . 'en la tabla de periodos');
			$DbgMsg = _('El sql ejecutado es: ');
			$result = DB_query($isql,$db,$ErrMsg,$DbgMsg,true);
		}


		$folioUE = fnObtenerFolioUeGeneral($db, $ur,$ue, 0);
		

		while ($ChartRow = DB_fetch_array($TransResult)) {
			$dblMonto +=$ChartRow['amount'];
			/*MOVIMIENTO INVERSO AL SALDO*/
			$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								tag,
								userid,
								posted,
								ln_ue,
								nu_folio_ue) ';
				$SQL= $SQL . "VALUES (
								0,
								". $TransNo .",
								'" . ($anio + 1) ."-01-01',
								" . ($PeriodNoApertura) . ",
								'" . $ChartRow['account'] . "',
								'".$narrativa."',
								". ($ChartRow['amount'] * -1) .",
								'".$ChartRow['tag']."',
								'".$_SESSION['UserID']."',
								'1',
								'".$ue."',
								'".$folioUE."'
							)";
		
				$ErrMsg =_('Problemas al insertar moviemiento de la polliza de cierre anual de ingresos');
				$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
				$result = DB_query($SQL,$db);

			$blnProceso = true;
			switch ($tipoCierre) {
			    case 'ingreso':
			        
			        break;
			    case 'gasto':
			        
			        break;
			    case 'balance':
			        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> Se genero la Poliza de apertura para el año <b>'.($anio + 1).'</b> con folio:  <b>'.$TransNo.'</b>.</p>';
			        break;
			}

		}
	}

	return array($blnProceso, $dblMonto, $Mensaje);
}


function fnPolizasAperturaResultadoEjercicio($ur, $ue, $PeriodNo,$monto, $anio, $db, $tipoCierre, $cuentaAcumuladora,$tipoPoliza, $folioUE, $narrativa){
	
	$blnProceso = false;
	$TransNo = GetNextTransNo($tipoPoliza, $db);
	$dblMonto=0;
	$accountResultadoBalance = "3.2.1.1.1.".$ue.".0001.0001.0001";
	$accountAsiganada = "3.2.2.1.1.".$ue.".0001.0001.0001";

	$cuenta1="";
	$cuenta2="";


	if( ($monto) >0){
		$cuenta2=$accountResultadoBalance;
		$cuenta1=$accountAsiganada;
	}else{
		$cuenta1=$accountResultadoBalance;
		$cuenta2=$accountAsiganada;
	}


	$PeriodNoApertura = GetPeriod('01/01/'.($anio + 1), $db);
	$sqlp = "SELECT *
			FROM periods
			WHERE periodno = " . $PeriodNoApertura;
	$resultp = DB_query($sqlp, $db);
	if ($myrowp = DB_fetch_array($resultp)){

	}else{
		$isql = "INSERT INTO periods(periodno, lastdate_in_period) 
				VALUES('" . $PeriodNoApertura . "','" . (($anio+1) ."-01-01") . "')";
		$ErrMsg = _('No se pudo insertar el periodo ' . $PeriodNoApertura . 'en la tabla de periodos');
		$DbgMsg = _('El sql ejecutado es: ');
		$result = DB_query($isql,$db,$ErrMsg,$DbgMsg,true);
	}

	$folioUE = fnObtenerFolioUeGeneral($db, $ur,$ue, 0);



	$SQL = 'INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						userid,
						posted,
						ln_ue,
						nu_folio_ue) ';
		$SQL= $SQL . "VALUES (
						0,
						". $TransNo .",
						'" . ($anio + 1) ."-01-01',
						" . ($PeriodNoApertura) . ",
						'" . $cuenta1 . "',
						'POLIZA DE TRASPASO DEL RESULTADO DEL EJERCICIO A RESULTADO DEL EJERCICIOS ANTERIORES DEL AÑO  ".($anio +1)."',
						". ($monto) .",
						'".$ur."',
						'".$_SESSION['UserID']."',
						'1',
						'".$ue."',
						'".$folioUE."'
					)";
	$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de traspaso del resultado del ejercicio');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	$result2 = DB_query($SQL,$db);

	$SQL = 'INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						userid,
						posted,
						ln_ue,
						nu_folio_ue) ';
		$SQL= $SQL . "VALUES (
						0,
						". $TransNo .",
						'" . ($anio + 1) ."-01-01',
						" . ($PeriodNoApertura) . ",
						'" . $cuenta2 . "',
						'POLIZA DE TRASPASO DEL RESULTADO DEL EJERCICIO A RESULTADO DEL EJERCICIOS ANTERIORES DEL AÑO  ".($anio + 1)."',
						". ($monto * -1) .",
						'".$ur."',
						'".$_SESSION['UserID']."',
						'1',
						'".$ue."',
						'".$folioUE."'
					)";
	$ErrMsg =_('Problemas al insertar moviemiento de la cuenta acumuladora polliza de traspaso del resultado del ejercicio');
	$DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
	$result2 = DB_query($SQL,$db);


	$Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> Se genero la Poliza de traspaso del resultado del ejercicio a resultado de ejercicios anteriores para el año <b>'.($anio + 1).'</b> con folio:  <b>'.$TransNo.'</b>.</p>'; 

	$blnProceso = true;

	return array($blnProceso, $dblMonto, $Mensaje);

}



$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>