<?php
/* 
  CGM - 05/NOV/2013 - Nueva version de aplicacion de pagos a proveedores
*/
include('includes/DefineSuppAllocsClass.php');
$PageSecurity = 5;
include('includes/session.inc');
$title = _('Aplicacion de Pagos y Notas de Credito Proveedores');
include('includes/header.inc');
$funcion=31;//
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title . '" alt="">' . ' ' . $title . '</p>';
echo '<p style="text-align:center;"><a href="'.$rootpath.'/SupplierDesAllocations.php" target="_blank">Ir a Desaplicacion de Pagos</a></p>';

if(isset($_GET['SupplierID'])){
    $SupplierID=$_GET['SupplierID'];
}elseif(isset($_POST['SupplierID'])){
    $SupplierID=$_POST['SupplierID'];
}

if(isset($_GET['noaplicacion'])){
    $noaplicacion=$_GET['noaplicacion'];
}elseif(isset($_POST['noaplicacion'])){
    $noaplicacion=$_POST['noaplicacion'];
}

if(isset($_GET['tag'])){
    $_POST['tag'] = $_GET['tag'];
}elseif(isset($_POST['tag'])){
    $_POST['tag'] = $_POST['tag'];
}

if(isset($_GET['NewAplication'])){
    unset($_SESSION['AllocTrans']);
    unset($_SESSION['Alloc']);
    unset($_POST['AllocTrans']);
}

/*
if(!isset($_POST['Process'])){
	unset($_SESSION['AllocTrans']);
	unset($_SESSION['Alloc']);
	unset($_POST['AllocTrans']);
}
*/
////
/****************************************************************************************************/
/****************************************************************************************************/
/************************************        U P D A T E S       ************************************/
/****************************************************************************************************/
// Calculo de monto aplicado, perdida y/o utilidad cambiaria
if (isset($_POST['UpdateDatabase']) OR isset($_POST['RefreshAllocTotal']) OR isset($_POST['btnTC'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	
        if (!isset($_SESSION['Alloc'])){
            prnMsg( _('Aplicaciones de Pagos a Proveedores no se pueden procesar nuevamente') . '. ' . _('Si refrescas la pagina despues de procesar una asignacion de pago') . ', ' . _('trata de usar las ligas provistas en la pagina para evitar este mensaje'),'warn');
            include('includes/footer_Index.inc');
            exit;
	}
        
	$TotalAllocated = 0;
	$TotalDiffOnExch = 0;
	$TotalDifAllocAmt = 0;
	$saldardocumento=false;
	
	for ($AllocCounter=0; $AllocCounter < $_POST['TotalNumberOfAllocs']; $AllocCounter++){
		if (!is_numeric($_POST['Amt' . $AllocCounter])){
			$_POST['Amt' . $AllocCounter] = 0;
		}
		if ($_POST['Amt' . $AllocCounter] < 0){
			prnMsg(_('El monto de la aplicacion es negativo') . '. ' . _('Un numero positivo es necesario'),'error');
			$_POST['Amt' . $AllocCounter] = 0;
		}
		
		$id = $_POST['AllocID'.$AllocCounter];
		$pendiente= ($_SESSION['Alloc']->Allocs[$id]->TransAmount - $_SESSION['Alloc']->Allocs[$id]->PrevAlloc) + .01;

		if ($_POST['Amt' . $AllocCounter] > $pendiente && $_POST['Amt' . $AllocCounter]!= 0){
		    prnMsg(_("El monto de ".$_POST['Amt' . $AllocCounter]." a aplicar no puede ser mayor a lo pendiente, capture un monto menor o igual"),"error");
		    $InputError = 1;	
		}

		//echo "valor ". $id. " : ". $_POST['Amt' . $AllocCounter]. " aplicado: ".$_SESSION['Alloc']->Allocs[$id]->PrevAlloc. " Total: ".$_SESSION['Alloc']->Allocs[$id]->TransAmount. "<br>";
		
		// Si la moneda que va a aplicar es pesos  a peso no calcula perdida o utilidad cambiaria
		if ($_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] ){
			$montopago=$_SESSION['Alloc']->TransAmt;
			if ($_POST['All' . $AllocCounter] == True){
				$v = $_POST['YetToAlloc' . $AllocCounter];
				if ( $v  > (-$_SESSION['Alloc']->TransAmt - $TotalAllocated)){
					$v = (-$_SESSION['Alloc']->TransAmt - $TotalAllocated);
				}
				$_POST['Amt' . $AllocCounter] = round($v,2);
			}
			// Inicializo valores de aplicacion y perdida/utilidad
			$_SESSION['Alloc']->Allocs[$id]->DifAllocAmt = 0 ;
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch=0;
			
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = $_POST['Amt' . $AllocCounter];
			$TotalDiffOnExch += $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
			$TotalAllocated = $TotalAllocated + $_POST['Amt' . $AllocCounter];
			
		}elseif ($_SESSION['Alloc']->CurrAplica==$_SESSION['Alloc']->CurrcodePay && $_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] ){
			// Si no es de moneda extranjera a moneda extranjera
			$montopago=$_SESSION['Alloc']->TransAmt;
			$_SESSION['Alloc']->Allocs[$id]->DifAllocAmt = 0 ;
			if ($_POST['All' . $AllocCounter] == True){
				$v = $_POST['YetToAlloc' . $AllocCounter];
				if ( $v  > (-$_SESSION['Alloc']->TransAmt - $TotalAllocated)){
					$v = (-$_SESSION['Alloc']->TransAmt - $TotalAllocated);
				}
				$_POST['Amt' . $AllocCounter] = round($v,2);
			}
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = $_POST['Amt' . $AllocCounter];
				
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch=0;
			// Calcula perdia o utilidad cambiaria
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch=($_POST['Amt' . $AllocCounter]*(1/$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->ExRate))-$_POST['Amt' . $AllocCounter]*(1/$_SESSION['Alloc']->TransExRate);
			$TotalDiffOnExch += $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
			$TotalAllocated = $TotalAllocated + $_POST['Amt' . $AllocCounter];
		}elseif($_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica!=$_SESSION['CountryOfOperation']){
			// Aplicacion de peso a dolar
			
			$ratepago=GetCurrencyRateByDate($fechapago,$_SESSION['Alloc']->CurrAplica,$db);
			if(isset($_POST['manualtc']) && $_POST['manualtc']>1 ){
				$tc=$_POST['manualtc'];
			}else{
				$tc=1/$ratepago;
			}
			
			$montopago=$_SESSION['Alloc']->TransAmt/$tc;
			
			$_SESSION['Alloc']->Allocs[$id]->DifAllocAmt = 0 ;
			if ($_POST['All' . $AllocCounter] == True){
				$v = $_POST['YetToAlloc' . $AllocCounter];
				if ( $v  > (-($_SESSION['Alloc']->TransAmt/$tc) - $TotalAllocated)){
					$v = (-($_SESSION['Alloc']->TransAmt/$tc) - $TotalAllocated);
				}
				$_POST['Amt' . $AllocCounter] = round($v,2);
			}
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = $_POST['Amt' . $AllocCounter];
			
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch=0;
			// Calcula perdia o utilidad cambiaria
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch=($_POST['Amt' . $AllocCounter]*(1/$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->ExRate))-($_POST['Amt' . $AllocCounter]*$tc);
			$TotalDiffOnExch += $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
			$TotalAllocated = $TotalAllocated + $_POST['Amt' . $AllocCounter];
		}elseif($_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation']){
			//Pago de dolar a peso
			$tc=(1/$_SESSION['Alloc']->TransExRate);
			$montopago=$_SESSION['Alloc']->TransAmt*$tc ;
			$_SESSION['Alloc']->Allocs[$id]->DifAllocAmt = 0 ;
			if ($_POST['All' . $AllocCounter] == True){
				$v = $_POST['YetToAlloc' . $AllocCounter];
				if ( $v  > (-$_SESSION['Alloc']->TransAmt*$tc - $TotalAllocated)){
					$v = (-$_SESSION['Alloc']->TransAmt*$tc - $TotalAllocated);
				}
				$_POST['Amt' . $AllocCounter] = round($v,2);
				
			}
			
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = $_POST['Amt' . $AllocCounter];
				
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch=0;
			// Calcula perdia o utilidad cambiaria
			//$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch=($_POST['Amt' . $AllocCounter]*(1/$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->ExRate))-($_POST['Amt' . $AllocCounter]*$tc);
			$TotalDiffOnExch += $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
			$TotalAllocated = $TotalAllocated + $_POST['Amt' . $AllocCounter];
			
		}	
		
	}	
	if ($TotalAllocated + $montopago >= 0.5){
		echo '<br><hr>';
		prnMsg(_('Estas asignaciones no pueden ser procesadas porque el monto aplicado es mayor al monto de') .
		' ' . $_SESSION['Alloc']->TransTypeName  . ' ' . _('siendo aplicado') . '<br>' . _('Total Aplicado') . ' = ' . 			$TotalAllocated . ' ' . _('y el total del pago es de ') . ' ' . -$montopago,'error');
		echo '<br><hr>';
		$InputError = 1;
	}

}
// Actualizacion a la base de datos
if (isset($_POST['UpdateDatabase'])){
	if ($InputError == 0){ /* ie all the traps were passed */
		/* actions to take having checked that the input is sensible
		 1st set up a transaction on this thread*/
		$Result = DB_Txn_Begin($db);
		$TotalAllocated=0;
		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {
			// Si la moneda que va a aplicar es pesos  a peso realiza la aplicacion directa
			if ($AllocnItem->AllocAmt > 0){
				// Inserta registro en tabla de aplicaciones
				$SQL = "INSERT INTO suppallocs (datealloc,
											amt,
											transid_allocfrom,
											rate_from,
								 			currcode_from,
											transid_allocto,
											rate_to,
											currcode_to,
											ratealloc,
											diffonexch_alloc
								)
							VALUES ('" . FormatDateForSQL(date($_SESSION['DefaultDateFormat'])) . "',
							'" . ($AllocnItem->AllocAmt) . "',
							'" . $_SESSION['Alloc']->AllocTrans . "',
							'" . $_SESSION['Alloc']->TransExRate . "',
							'" . $_SESSION['Alloc']->CurrcodePay . "',
							'" . $AllocnItem->ID ."',
							'" . $AllocnItem->ExRate ."',
							'" . $AllocnItem->currcode ."',
							'" . $AllocnItem->ExRate ."',
							'" . $AllocnItem->DiffOnExch ."'
									)";					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  _('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' ._('could not be inserted because');
				$DbgMsg = _('The following SQL to insert the allocation record was used');
				$Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				// Actualiza documento de cargo
				$NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt +  $AllocnItem->DifAllocAmt;
				if (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.01){
					$Settled = 1;
				} else {
					$Settled = 0;
				}
					
				$SQL = 'UPDATE supptrans
						SET diffonexch=' . $AllocnItem->DiffOnExch . ',
									alloc = ' .  $NewAllocTotal . ',
									settled = ' . $Settled . '
						WHERE id = ' . $AllocnItem->ID;
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
				$DbgMsg = _('The following SQL to update the debtor transaction record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			}// Fin de la aplicacion
			
			// conversiones de totales aplicados para documento de abono
			if ($_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] ){
					$TotalAllocated += $AllocnItem->AllocAmt;
					$TotalAllocatedanticipo=$TotalAllocated;
			}// fin de pesos a pesos
			// de moneda extranjera a moneda extranjera
			if ($_SESSION['Alloc']->CurrAplica==$_SESSION['Alloc']->CurrcodePay && $_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] ){
					$TotalAllocated += $AllocnItem->AllocAmt;
					$TotalAllocatedanticipo=$TotalAllocated/ $_SESSION['Alloc']->TransExRate;
			}// fin de MExt a Mext
			
			// de moneda extranjera a moneda nacional
			if ($_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] ){
					//$TotalAllocated += $AllocnItem->AllocAmt;
					$TotalAllocated = $TotalAllocated + ($AllocnItem->AllocAmt/(1/$_SESSION['Alloc']->TransExRate));
					$TotalAllocatedanticipo=$TotalAllocated*(1/$_SESSION['Alloc']->TransExRate);
					
			}// fin de MExt a Mext
			//Aplicacion de pesos a Moneda extranjera
			if($_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica!=$_SESSION['CountryOfOperation']){
				// Aplicacion de peso a dolar
				$ratepago=GetCurrencyRateByDate($fechapago,$_SESSION['Alloc']->CurrAplica,$db);
				if(isset($_POST['manualtc']) && $_POST['manualtc']>1 ){
					$tc=$_POST['manualtc'];
				}else{
					$tc=1/$ratepago;
				}	
				$TotalAllocated += $AllocnItem->AllocAmt*$tc;
				$TotalAllocatedanticipo=$TotalAllocated;
			}// fin de MNal a Mext
			
		}// Fin de recorrido de aplicaciones
		
		
		if (-$_SESSION['Alloc']->TransAmt - $TotalAllocated < 1){
			$TotalAllocated = -1*$_SESSION['Alloc']->TransAmt;
		}
		
		if (abs($TotalAllocated + $_SESSION['Alloc']->TransAmt) < 0.01){
			$Settled = 1;
		} else {
			$Settled = 0;
		}
		// mov de perdida y/o utilidad cambiaria
		$MovtInDiffOnExch=$TotalDiffOnExch;
		if ($MovtInDiffOnExch !=0){
			if (($MovtInDiffOnExch) >= 0) {
		
				$ctautilidadperdida = $_SESSION['CompanyRecord']['purchasesexchangediffact'];
			}else{
				$ctautilidadperdida = $_SESSION['CompanyRecord']['gllink_purchasesexchangediffactutil'];
			}
				
			$PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate, $db, $_SESSION['Alloc']->tagref);
			$_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);
		
			$reference = $_SESSION['Alloc']->SupplierID . "@UTIL/PERD CAMBIARIA@" . $MovtInDiffOnExch. ' @TC:'.(1/$_SESSION['Alloc']->TransExRate);
			// extrae porcentaje de iva
			$MovtInDiffOnExchIMpuesto=($MovtInDiffOnExch/(1+$_SESSION['Alloc']->PercentImpuesto)*$_SESSION['Alloc']->PercentImpuesto);
			//$MovtInDiffOnExch=$MovtInDiffOnExch-$MovtInDiffOnExchIMpuesto;
				
			$SQL = 'INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag
							)
						VALUES (' . $_SESSION['Alloc']->TransType . ',
							' . $_SESSION['Alloc']->TransNo . ",
							'" . $_SESSION['Alloc']->TransDate . "',
							" . $PeriodNo . ',
							' . $ctautilidadperdida . ",
							'". $reference . "',
							" . ($MovtInDiffOnExch*-1) . ",
							'" . $_SESSION['Alloc']->tagref . "')";
		
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
					_('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			
			$tipoproveedor = ExtractTypeSupplier($_SESSION['Alloc']->SupplierID,$db);
			$ctaproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
		
			$SQL = 'INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag)
						VALUES (' . $_SESSION['Alloc']->TransType . ',
							' . $_SESSION['Alloc']->TransNo . ",
							'" . $_SESSION['Alloc']->TransDate . "',
							" . $PeriodNo . ',
							' . $ctaproveedor . ",
							'" . $reference . "',
							" . ($MovtInDiffOnExch) . ",
							'" . $_SESSION['Alloc']->tagref . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ' : ' .
					_('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				
			// inserta movimientos de impuestos
			if (abs($MovtInDiffOnExchIMpuesto)>0){
				$SQL = 'select * from taxauthorities where taxid=1';
				$result2 = DB_query($SQL,$db);
				$TaxAccs = DB_fetch_array($result2);
		
				$reference = $_SESSION['Alloc']->SupplierID . "@UTIL/PERD CAMBIARIA@Impuesto " . $MovtInDiffOnExchIMpuesto. ' @TC:'.(1/$_SESSION['Alloc']->TransExRate);
		
				$SQL = 'INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag
							)
						VALUES (' . $_SESSION['Alloc']->TransType . ',
							' . $_SESSION['Alloc']->TransNo . ",
							'" . $_SESSION['Alloc']->TransDate . "',
							" . $PeriodNo . ',
							' . $TaxAccs['purchtaxglaccount'] . ",
							'". $reference . "',
							" . -($MovtInDiffOnExchIMpuesto) . ",
							'" . $_SESSION['Alloc']->tagref . "')";
		
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
						_('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		
		
				$SQL = 'INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag)
						VALUES (' . $_SESSION['Alloc']->TransType . ',
							' . $_SESSION['Alloc']->TransNo . ",
							'" . $_SESSION['Alloc']->TransDate . "',
							" . $PeriodNo . ',
							' . $TaxAccs['purchtaxglaccountPaid'] . ",
							'" . $reference . "',
							" . ($MovtInDiffOnExchIMpuesto) . ",
							'" . $_SESSION['Alloc']->tagref . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ' : ' .
						_('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		
		
			}
		}
		
		//SI EL DOCUMENTO ES UN ANTICIPO DE PROVEEDOR GENERA MOVIMIENTOS CONTABLES DE ANTICIPOS -> PROVEEDPRES
		if ($_SESSION['Alloc']->TransType == 121){
			//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
			$Transtype = 122;
			$TransNo = GetNextTransNo($Transtype, $db);
			$fecha = Date($_SESSION['DefaultDateFormat']);
			$PeriodNo = GetPeriod($fecha, $db, $_SESSION['Alloc']->tagref);
			$tipoproveedor = ExtractTypeSupplier($_SESSION['Alloc']->SupplierID,$db);
			$ctaanticipoproveedor = SupplierAccount($tipoproveedor,"gl_debtoradvances",$db);
			$ctaproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
			$reference = $_SESSION['Alloc']->SupplierID . "@" . $TransNo  . "@ANTICIPO APLICADO: " . $TotalAllocated;
            
			$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
			$SQL=$SQL . "VALUES (
							'" . $Transtype . "',
							" . $TransNo . ",
							'" . FormatDateForSQL($fecha) . "',
							" . $PeriodNo . ",
							" . $ctaproveedor . ",
							'" . $reference . "',
							" . $TotalAllocatedanticipo . ",
							" . $_SESSION['Alloc']->tagref . ",
							'0'
						)";
			$ErrMsg = _('EL SQL FALLO DEBIDO A ');
			$DbgMsg = _('ES SQL UTILIZADO ES');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									tag,
									chequeno) ";
			$SQL=$SQL . "VALUES (
								'" . $Transtype . "',
								" . $TransNo . ",
								'" . FormatDateForSQL($fecha) . "',
								" . $PeriodNo . ",
								" . $ctaanticipoproveedor . ",
								'" . $reference . "',
								" . -$TotalAllocatedanticipo . ",
								" . $_SESSION['Alloc']->tagref . ",
								'0'
							)";
			$ErrMsg = _('EL SQL FALLO DEBIDO A ');
			$DbgMsg = _('ES SQL UTILIZADO ES');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}// Fin de movimientos contables para cuentas de anticipos
		
		// asigna aplicacion a mov de abono
		$SQL = 'UPDATE supptrans
						SET alloc = alloc + ' .  -$TotalAllocated . ',
							diffonexch = ' . -$TotalDiffOnExch . ',
							settled=' . $Settled . '
						WHERE id = ' . $_SESSION['Alloc']->AllocTrans;
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
				_('The supplier payment or credit note transaction could not be modified for the new allocation and exchange difference because');
		$DbgMsg = _('The following SQL to update the payment or credit note was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		$Result = DB_Txn_Commit($db);
		/*finally delete the session variables holding all the previous data */
		unset($_SESSION['AllocTrans']);
		unset($_SESSION['Alloc']);
		unset($_POST['AllocTrans']);
	}// Fin de validacion de input error
}// Fin de actualizacion a base de datos

/****************************************************************************************************/
/****************************************************************************************************/
/************************************        U P D A T E S       ************************************/
/****************************************************************************************************/

echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post name="form">';
// Entra si ya se selecciono el documento a aplicar
If (isset($_GET['AllocTrans'])){
	//Inicializa la clase
	$_SESSION['Alloc'] = new Allocation();
	/* OBTIENE DE LIGA DE LA PAGINA EL NUMERO DE TRANSACCION DEL PAGO A ASIGNAR*/
	$_SESSION['Alloc']->AllocTrans =$_GET['AllocTrans'];
	/*********************************************************************/
	/* BUSCAR EN BASE DE DATOS LOS DETALLES DEL PAGO QUE VAMOS A ASIGNAR */
	$SQL= 'SELECT systypescat.typename,
			supptrans.type,
			supptrans.id,
			supptrans.transno,
			supptrans.trandate,
			supptrans.supplierno,
			suppliers.suppname,
			rate,
			(supptrans.ovamount+supptrans.ovgst)-alloc AS total,
			(supptrans.ovgst/supptrans.ovamount) as perimpuesto,
			supptrans.diffonexch,
			supptrans.alloc,
			supptrans.tagref,
			supptrans.currcode
		FROM supptrans,
			systypescat,
			suppliers
		WHERE supptrans.type = systypescat.typeid
			AND supptrans.supplierno = suppliers.supplierid
			AND supptrans.id=' .$_GET['AllocTrans'];
	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result) != 1){
		echo _('There was a problem retrieving the information relating the transaction selected') . '. ' . _('Allocations are unable to proceed');
		if ($debug == 1){
			echo '<br>' . _('The SQL that was used to retrieve the transaction information was') . " :<br>$SQL";
		}
		exit;
	}
	$myrow = DB_fetch_array($Result);
	$_SESSION['Alloc']->SupplierID = $myrow['supplierno'];
	$_SESSION['Alloc']->SuppName = $myrow['suppname'];;
	$_SESSION['Alloc']->TransType = $myrow['type'];
	$_SESSION['Alloc']->TransTypeName = $myrow['typename'];
	$_SESSION['Alloc']->TransNo = $myrow['transno'];
	// rate al que se realiza el pago
	$_SESSION['Alloc']->TransExRate = $myrow['rate'];
	$_SESSION['Alloc']->TransAmt = $myrow['total'];
	$_SESSION['Alloc']->PrevDiffOnExch = $myrow['diffonexch'];
	$_SESSION['Alloc']->TransDate = ConvertSQLDate($myrow['trandate']);
	$_SESSION['Alloc']->tagref = $myrow['tagref'];
	$_SESSION['Alloc']->TransTag = $myrow['tagref'];
	//Moneda del pago
	$_SESSION['Alloc']->CurrcodePay= $myrow['currcode'];
	$legalidabono = ExtractLegalid($_SESSION['Alloc']->tagref ,$db);
	$_SESSION['Alloc']->PercentImpuesto=$myrow['perimpuesto'];
	$fechapago=$myrow['trandate'];
	$_SESSION['Alloc']->CurrAplica=$_GET['CurrAplica'];
	$_SESSION['Alloc']->rate = $myrow['rate'];
	/***************************************************************************************/
	/* AHORA OBTIENE TRANSACCIONES QUE TIENEN SALDO PENDIENTE DE APLICAR DE ESTE PROVEEDOR  EN LA MONEDA QUE EL USUARIO SELECCIONO*/
	$_SESSION['Alloc']->existe_currext=false;
	$SQL= "SELECT supptrans.id,
			typename,
			transno,
			trandate,
			suppreference,
			rate,
			ovamount+ovgst AS total,
			diffonexch,
			alloc,
			supptrans.tagref,
			case when supptrans.currcode is null then (case when rate = 1 then 'MXN' else 'USD' end) else supptrans.currcode end  as currcode,
			tags.tagname
		FROM supptrans,
			systypescat,
			tags
		WHERE supptrans.type = systypescat.typeid
		AND supptrans.settled=0
		AND abs(ovamount+ovgst-alloc)>0.009
		AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'
		AND supptrans.tagref = tags.tagref
		AND case when supptrans.currcode is null then (case when rate = 1 then 'MXN' else 'USD' end) else supptrans.currcode end ='".$_SESSION['Alloc']->CurrAplica."'
		AND tags.legalid = '" . $legalidabono."'
		AND (supptrans.tagref = '".$_POST['tag']."' OR '".$_POST['tag']."' = '-999')";
	
	$ErrMsg = _('There was a problem retrieving the transactions available to allocate to');
	$DbgMsg = _('The SQL that was used to retrieve the transaction information was');
	
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	while ($myrow=DB_fetch_array($Result)){
		
		if ($_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation']){
			$tcdiadocto=GetCurrencyRateByDate($myrow['trandate'],$_SESSION['Alloc']->CurrcodePay,$db);
		}else{
			$tcdiadocto=1;
		}
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'],
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['suppreference'],
				0,
				$myrow['total'],
				$myrow['rate'],
				$myrow['diffonexch'],
				$myrow['diffonexch'],
				$myrow['alloc'],
				'NA',
				$myrow['tagref'],
				$myrow['currcode'],
				0,
				$tcdiadocto,0,$myrow['tagname']
		);
	}
}// Fin de agregar documentos a clase para ser saldados
/***************************************************************************************/
// Muestra documentos para aplicacion de pagos 
if(isset($_SESSION['Alloc'])){
	//Extrae la razon social del abono
	$LegalAbono=ExtractLegalid($_SESSION['Alloc']->TransTag ,$db);
	echo "<input type='hidden' name='AllocTrans' VALUE='" . $_POST["AllocTrans"] . "'>";
	echo '<table style="margin-left: auto; margin-right: auto; width: 70%" border=0 cellpadding=1 cellspacing=1 >';
	echo '<tr><th colspan=4 style="text-align:center;"><b>'.('Informacion Documento').'</b></th></tr>';
	echo '<tr><td style="text-align:right;" nowrap><b>'._(' Codigo Proveedor').' :</b></td>';
	echo '<td>'.$_SESSION['Alloc']->SupplierID .'</td>';
	echo '<td nowrap style="text-align:right;" ><b>'._('Nombre Proveedor').' :</b></td>';
	echo '<td nowrap >'.$_SESSION['Alloc']->SuppName .'</td>';
	echo '</tr>';
	echo '<tr><td style="text-align:right;"><b>'._('Tipo').' :</b></td>';
	echo '<td nowrap>'.$_SESSION['Alloc']->TransTypeName .'</td>';
	echo '<td style="text-align:right;"><b>'._('Numero').' :</b></td>';
	echo '<td>'.$_SESSION['Alloc']->TransNo .'</td>';
	echo '</tr>';
	echo '<tr><td style="text-align:right;"><b>'._('Fecha').' :</b></td>';
	echo '<td>'.$_SESSION['Alloc']->TransDate .'</td>';
	echo '<td style="text-align:right;"><b>'._('Monto Por aplicar').' :</b></td>';
	echo '<td>'.$_SESSION['Alloc']->TransAmt*-1 .'</td>';
	echo '</tr>';
	echo '<tr><td style="text-align:right;"><b>'._('Moneda').' :</b></td>';
	echo '<td>'.$_SESSION['Alloc']->CurrcodePay .'</td>';
	echo '<td style="text-align:right;"><b>'._('Tipo de Cambio').' :</b></td>';
	echo '<td>'.number_format(1/$_SESSION['Alloc']->TransExRate,4) .'</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th colspan=4 style="font-size:7pt;">';
	echo '<div  class="centre"><b>' . _('***SOLO SE MUESTRAN DOCUMENTOS CON MONEDA ').$_SESSION['Alloc']->CurrAplica.(' Y QUE SON DE LA MISMA RAZON SOCIAL A LA QUE PERTENECE EL ABONO...') . '</b></div>';
	echo '</th>';
	echo '</tr>';
	echo '</table>';
	echo '<hr>';
	
        // muestra registros para realizar la aplicacion 
	echo '<table cellpadding=2 colspan=8 BORDER=1>';
	if ($_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] ){
		$TableHeader = "<tr><th>" . _('Tipo') . "</th>
								<th>" . _('Unidad de') . '<br>' . _('Negocio') . "</th>
								<th>" . _('Aplicacion') . '<br>' . _('Numero') . "</th>
								<th>" . _('Orden') . '<br>' . _('Compra') . "</th>
								<th>" . _('Prov') . '<br>' . _('Ref') . "</th>
								<th>" . _('Fecha') .'<br>' . _('Documento') . "</th>
								<th>" . _('Moneda Documento') . "</th>
								<th>" . _('TC Documento') . "</th>
								<th>" . _('Monto') . '<br>' . _('Documento') .	"</th>
								<th>" . _('Pendiente') . '<br>' . _(' de Aplicar') . "</th>
								<th>" . _('Aplicar') . "</th>
								</tr>";
	}elseif ($_SESSION['Alloc']->CurrAplica==$_SESSION['Alloc']->CurrcodePay && $_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] ){
		$TableHeader = "<tr><th>" . _('Tipo') . "</th>
								<th>" . _('Unidad de') . '<br>' . _('Negocio') . "</th>
								<th>" . _('Aplicacion') . '<br>' . _('Numero') . "</th>
								<th>" . _('Orden') . '<br>' . _('Compra') . "</th>
								<th>" . _('Prov') . '<br>' . _('Ref') . "</th>
								<th>" . _('Fecha') .'<br>' . _('Documento') . "</th>
								<th>" . _('Moneda Documento') . "</th>
								<th>" . _('TC Documento') . "</th>
								<th>" . _('Monto') . '<br>' . _('Documento') .	"</th>
								<th>" . _('Pendiente') . '<br>' . _(' de Aplicar') . "</th>
								<th>" . _('Aplicar') . "</th>
								<th>" . _('Dif') . '<br>' . _('Cambiaria') . "</th>
								</tr>";
	}else{
		if($_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica!=$_SESSION['CountryOfOperation']){
			$ratepago=GetCurrencyRateByDate($fechapago,$_SESSION['Alloc']->CurrAplica,$db);
			if(isset($_POST['manualtc']) && $_POST['manualtc']>1 ){
				$tc=$_POST['manualtc'];
			}else{
				$tc=1/$ratepago;
			}
			echo '<tr>';
			echo '<td colspan=15>';
			echo '<div  class="centre"><b>' . _('Modificar tasa de cambio de aplicacion de pago ') . '</b><input type="txt" class="numeric" name="manualtc" value="'.round($tc,4).'">&nbsp;<input type="submit" name="btnTC" value="Cambiar"></div>';
			echo '</td>';
			echo '</tr>';
			
			$TableHeader = "<tr><th>" . _('Tipo') . "</th>
								<th>" . _('Unidad de') . '<br>' . _('Negocio') . "</th>
								<th>" . _('Aplicacion') . '<br>' . _('Numero') . "</th>
								<th>" . _('Orden') . '<br>' . _('Compra') . "</th>
								<th>" . _('Prov') . '<br>' . _('Ref') . "</th>
								<th>" . _('Fecha') .'<br>' . _('Documento') . "</th>
								<th>" . _('Moneda Documento') . "</th>
								<th>" . _('TC Documento') . "</th>
								<th>" . _('Monto') . '<br>' . _('Documento') .	"</th>
								<th>" . _('Pendiente') . '<br>' . _(' de Aplicar') . "</th>
								<th>" . _('Aplicar') . "</th>
								<th>" . _('Dif') . '<br>' . _('Cambiaria') . "</th>
							</tr>";
		}elseif($_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation']){
			$TableHeader = "<tr><th>" . _('Tipo') . "</th>
								<th>" . _('Unidad de') . '<br>' . _('Negocio') . "</th>
								<th>" . _('Aplicacion') . '<br>' . _('Numero') . "</th>
								<th>" . _('Orden') . '<br>' . _('Compra') . "</th>
								<th>" . _('Prov') . '<br>' . _('Ref') . "</th>
								<th>" . _('Fecha') .'<br>' . _('Documento') . "</th>
								<th>" . _('Moneda Documento') . "</th>
								<th>" . _('TC Documento') . "</th>
								<th>" . _('Monto') . '<br>' . _('Documento') .	"</th>
								<th>" . _('Pendiente') . '<br>' . _(' de Aplicar') . "</th>
								<th>" . _('Aplicar') . "</th>
							</tr>";
		
		
		}	
		
		
	}
	$k = 0;
	$Counter = 0;
	$RowCounter = 0;
	$TotalAllocated = 0;
	echo $TableHeader;
	
	$TotalDiffOnExch=0;
	foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {
		if ($k == 1){
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		echo "<td>$AllocnItem->TransType</td>
			  <td>$AllocnItem->tagname</td>
		 	  <td>$AllocnItem->TypeNo</td>";
			$Compra="";
			$SQL="select * from supptransdetails where supptransid=".$AllocnItem->ID;
			$resultsupp = DB_query($SQL,$db);
			$myrowsupp = DB_fetch_array($resultsupp);
			$Compra = 'OC: '.$myrowsupp['orderno'];
			echo '<td nowrap class=peque >'.substr($Compra,0,20).'</td>';
			echo "<td>$AllocnItem->SuppRef</td>
				  <td>$AllocnItem->TransDate</td>
				  <td>$AllocnItem->currcode</td>
			      <td>".number_format((1/$AllocnItem->ExRate),4)."</td>
			      <td align=right>" . number_format($AllocnItem->TransAmount,2) . '</td>';
			// Informacion de solo moneda de operacion, generalmente pesos
			if ($_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] ){
				echo '<td>'.number_format(($AllocnItem->TransAmount-$AllocnItem->PrevAlloc),2) .'';
				$YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);
				echo  "<input type=hidden name='YetToAlloc" .$Counter . "' VALUE=" . $YetToAlloc . '></td>';
				echo "<td align=right nowrap><input type='checkbox' name='All" .  $Counter . "' value=True >";
				echo "<input type=text class='number' name='Amt" . $Counter ."' maxlength=12 size=13 VALUE=" .$AllocnItem->AllocAmt . ">
					  <input type=hidden name='AllocID" . $Counter . "' VALUE=" . $AllocnItem->ID . '></td>';
				$totdoctos=$totdoctos+$AllocnItem->TransAmount;
				$totPendienteaplicar=$totPendienteaplicar+($AllocnItem->TransAmount-$AllocnItem->PrevAlloc);
				$TotalAllocated = $TotalAllocated + $AllocnItem->AllocAmt;
				$totaplicando=$totaplicando + $AllocnItem->AllocAmt;
			}elseif ($_SESSION['Alloc']->CurrAplica==$_SESSION['Alloc']->CurrcodePay && $_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] ){
				echo '<td>'.number_format(($AllocnItem->TransAmount-$AllocnItem->PrevAlloc),2) .'';
				$YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);
				echo  "<input type=hidden name='YetToAlloc" .$Counter . "' VALUE=" . $YetToAlloc . '></td>';
				echo "<td align=right nowrap><input type='checkbox' name='All" .  $Counter . "' value=True >";
				echo "<input type=text class='number' name='Amt" . $Counter ."' maxlength=12 size=13 VALUE=" .$AllocnItem->AllocAmt . ">
					  <input type=hidden name='AllocID" . $Counter . "' VALUE=" . $AllocnItem->ID . '></td>';
				$TotalAllocatedpartida=($AllocnItem->AllocAmt*(1/$AllocnItem->ExRate))-$AllocnItem->AllocAmt*(1/$_SESSION['Alloc']->TransExRate);
				echo "<td>".number_format($TotalAllocatedpartida,2)."</td>";
				$totdoctos=$totdoctos+$AllocnItem->TransAmount;
				$totPendienteaplicar=$totPendienteaplicar+($AllocnItem->TransAmount-$AllocnItem->PrevAlloc);
				$TotalAllocated = $TotalAllocated + $AllocnItem->AllocAmt;
				$totaplicando=$totaplicando + $AllocnItem->AllocAmt;
				$TotalDiffOnExch=$TotalDiffOnExch+$TotalAllocatedpartida;
			}elseif($_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica!=$_SESSION['CountryOfOperation']){
				//pago de peso a moneda extranjera
				echo '<td>'.number_format(($AllocnItem->TransAmount-$AllocnItem->PrevAlloc),2) .'';
				$YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);
				echo  "<input type=hidden name='YetToAlloc" .$Counter . "' VALUE=" . $YetToAlloc . '></td>';
				echo "<td align=right nowrap><input type='checkbox' name='All" .  $Counter . "' value=True >";
				echo "<input type=text class='number' name='Amt" . $Counter ."' maxlength=12 size=13 VALUE=" .$AllocnItem->AllocAmt . ">
					  <input type=hidden name='AllocID" . $Counter . "' VALUE=" . $AllocnItem->ID . '></td>';
				$TotalAllocatedpartida=$AllocnItem->AllocAmt*(1/$AllocnItem->ExRate)-($AllocnItem->AllocAmt*($tc));
				//echo '<td align=right><b><input type="checkbox" name="saldardocumento"></b></td>';
				echo "<td>".number_format($TotalAllocatedpartida,2)."</td>";
				$totdoctos=$totdoctos+$AllocnItem->TransAmount;
				$totPendienteaplicar=$totPendienteaplicar+($AllocnItem->TransAmount-$AllocnItem->PrevAlloc);
				$TotalAllocated = $TotalAllocated + $AllocnItem->AllocAmt;
				$totaplicando=$totaplicando + $AllocnItem->AllocAmt;
				$TotalDiffOnExch=$TotalDiffOnExch+$TotalAllocatedpartida;
			}elseif($_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation']){
				//Pago de dolar a peso
				echo '<td>'.number_format(($AllocnItem->TransAmount-$AllocnItem->PrevAlloc),2) .'';
				$YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);
				echo  "<input type=hidden name='YetToAlloc" .$Counter . "' VALUE=" . $YetToAlloc . '></td>';
				echo "<td align=right nowrap><input type='checkbox' name='All" .  $Counter . "' value=True >";
				echo "<input type=text class='number' name='Amt" . $Counter ."' maxlength=12 size=13 VALUE=" .$AllocnItem->AllocAmt . ">
					  <input type=hidden name='AllocID" . $Counter . "' VALUE=" . $AllocnItem->ID . '></td>';
				$TotalAllocatedpartida=0;
				//echo '<td align=right><b><input type="checkbox" name="saldardocumento"></b></td>';
				
				$totdoctos=$totdoctos+$AllocnItem->TransAmount;
				$totPendienteaplicar=$totPendienteaplicar+($AllocnItem->TransAmount-$AllocnItem->PrevAlloc);
				$TotalAllocated = $TotalAllocated + ($AllocnItem->AllocAmt/(1/$_SESSION['Alloc']->TransExRate));
				$totaplicando=$totaplicando + $AllocnItem->AllocAmt;
				$TotalDiffOnExch=$TotalDiffOnExch+$TotalAllocatedpartida;
				
			}
		echo '</tr>';
		$Counter++;
	}
	//if ($_SESSION['Alloc']->CurrAplica==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] ){
		$colspan=8;
		echo '<tr><td colspan='.$colspan.' align=right><b><U>' . _('Totales') . ':</U></b></td>';
		echo '<td  class=number><b>' . number_format($totdoctos,2) . '</b></td>';
		echo '<td  class=number><b>' . number_format($totPendienteaplicar,2) . '</b></td>';
		echo '<td  class=number><b>' . number_format($totaplicando,2) . '</b></td>';
		$colspan=8;
		if (-$_SESSION['Alloc']->TransAmt - $TotalAllocated < 1){
			$TotalAllocated = -1*$_SESSION['Alloc']->TransAmt;
		}
		
		if ($_SESSION['Alloc']->CurrAplica==$_SESSION['Alloc']->CurrcodePay && $_SESSION['Alloc']->CurrcodePay!=$_SESSION['CountryOfOperation'] ){
			echo '<td  class=number><b>' . number_format($TotalDiffOnExch,2) . '</b></td>';
		}
		if($_SESSION['Alloc']->CurrcodePay==$_SESSION['CountryOfOperation'] && $_SESSION['Alloc']->CurrAplica!=$_SESSION['CountryOfOperation']){
			
			echo '<td  class=number><b>' . number_format($TotalDiffOnExch,2) . '</b></td>';
			$colspan=8;
			
				$TotalAllocated = $TotalAllocated*$tc;
			
		}
		echo '</tr>';
		$colspan=8;
		/*
		echo '<tr><td colspan='.$colspan.' align=right><b><U>' . _('Saldar Documento') . ': </U></b></td>
			<td align=right><b><input type="checkbox" name="saldardocumento"></b></td><td colspan=4></td></tr>';
		*/
		echo '<tr><td colspan='.$colspan.' align=right><b><U>' . _('Total Aplicado ') . ':</U></b></td>
			<td align=right><b><U>' .  number_format($TotalAllocated,2) . '</U></b></td><td colspan=4></td></tr>';
		echo '<tr><td colspan='.$colspan.' align=right><b>' . _('Pendiente de Aplicar ') . '</b></td><td align=right><b>' .
				number_format(-$_SESSION['Alloc']->TransAmt - $TotalAllocated,2) . '</b></td><td colspan=4></td></tr>
			<tr><td colspan='.$colspan.' align=right><b><U>' . _('Total Dif Cambiaria') . _(' en ').$_SESSION['CountryOfOperation']. ':</U></b></td>
			<td align=right><b><U>' .  number_format($TotalDiffOnExch,2) .'</U></b></td><td colspan=4></td></tr>
			';
	//}
	echo '</table>';
	echo "<div class='centre'><input type=hidden name='TotalNumberOfAllocs' VALUE=$Counter>";
	echo "<input type=submit name='RefreshAllocTotal' VALUE='" . _('Recalcula Total a Aplicar') . "'>";
	echo "<input type=submit name=UpdateDatabase VALUE='" . _('Procesa Aplicaciones') . "'></div>";
}
/********************************************************************/
//Muestra documentos pendientes de aplicar////
/********************************************************************/
if(!isset($_SESSION['Alloc'])){
	/* Elimina Variables Previamente Utilizadas */
	unset($_SESSION['Alloc']->Allocs);
	unset($_SESSION['Alloc']);
	echo '<table style="margin-left: auto; margin-right: auto; width: 70%" border=0>';
	echo '<tr>
			<td style="text-align:right;">'
			._('Seleccione Una Razon Social:').'
			</td>
			<td>';
	///Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
						  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY legalbusinessunit.legalname";
	echo '<select name="legalid">';
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>'  .$myrow['legalname'];
		}
	}
	echo '</select>
			</td>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<td style="text-align:right;">' . _('Area') . ':</td> ';
	$SQL=" SELECT *
					 FROM areas
					 ORDER BY areadescription";
	$resultarea = DB_query($SQL,$db);
	echo "<td ><select name='area' style='font-size:8pt'>";
	echo '<option selected value="-999">Todas</option>';
	while ($myrowarea = DB_fetch_array($resultarea)) {
		if ($_POST['area']==$myrowarea['areacode']){
			echo '<option selected value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
		} else {
			echo '<option  value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
		}
	}
	echo '</select>&nbsp;&nbsp;';
	echo '<input type="submit" value="->" name="btnArea"></td>';
	echo '</tr>';
	$wcond ="";
	if ($_POST['area'] and $_POST['area']!=-999){
		$wcond = "AND t.areacode = '".$_POST['area']."'";
	}
	echo '<tr><td style="text-align:right;">'._('Unidad de Negocios:').'</td><td><select name="tag">';
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref  $wcond";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
	
	$result=DB_query($SQL,$db);
	echo '<option value=-999>Todas a las que tengo acceso...';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
			<td style="text-align:right;">' . _('X Tipo de Documento') . ':</td>
			<td  >';
	echo "<select Name='tipodocumento' value='tipodocumento'><br>";
	$sqlr = "SELECT DISTINCT typeid, typename
				FROM systypescat
				WHERE typeid in (21,22,32,33,37,121,480,490,501,116)
				order by typename";
	$grupor= DB_query($sqlr,$db);
	echo '<option  VALUE="-999" selected>Seleccionar';
	while ($myrowgrupor=DB_fetch_array($grupor,$db)) {
		$grupobaser=$myrowgrupor['typeid'];
		if (trim($_POST['tipodocumento'])==$grupobaser) {
			echo '<option  VALUE="' . $myrowgrupor['typeid'] . ' " selected>' .$myrowgrupor['typename'];
		} else {
			echo '<option  VALUE="' . $myrowgrupor['typeid'] . '" >' .$myrowgrupor['typename'];
		}
	}
	echo'</td>';
	echo"</tr>";
	
	echo '<tr><td  style="text-align:right;">'._('Codigo Proveedor').':</td><td>';
	echo "<input type=text name=SupplierID VALUE='" . $SupplierID . "'>";
	echo '</td></tr>';
	
	echo '<tr><td  style="text-align:right;">'._('No Operacion').':</td><td>';
	echo "<input type=text name=noaplicacion VALUE='" . $noaplicacion. "'>";
	echo '</td></tr>';
	echo '<tr><td  style="text-align:center;" colspan=2>';
	echo "<input type=submit name=Process value='" . _('Recuperar Documentos Pendientes de Aplicar...') . "'></td></tr></table>";
	$sql = "SELECT id,
				transno,
				typename,
				type,
				suppliers.supplierid,
				suppname,
				trandate,
				supptrans.transtext,
				suppreference,
				rate,
				ovamount+ovgst AS total,
				alloc,
				supptrans.tagref,
				tags.tagdescription,
				case when supptrans.currcode is null then (case when rate = 1 then 'MXN' else 'USD' end) else supptrans.currcode end  as currcode
			FROM supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
						JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
						JOIN systypescat ON supptrans.type=systypescat.typeid
						JOIN tags ON supptrans.tagref = tags.tagref
						JOIN areas ON tags.areacode = areas.areacode
			WHERE type in (21,22,24,32,33,37,121,480,490,501,116)
						AND (suppliers.supplierid='" . $SupplierID ."' OR '" . $SupplierID ."' = '')
						AND (supptrans.tagref = '".$_POST['tag']."' OR '".$_POST['tag']."' = '-999')
						AND (areas.areacode='".$_POST['area']."' OR '".$_POST['area']."' = '-999')
						AND (tags.legalid = '".$_POST['legalid']."' OR '".$_POST['legalid']."'='-999')
						AND (systypescat.typeid = '".$_POST['tipodocumento']."' OR '".$_POST['tipodocumento']."'='-999')
					    AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'";
		if(strlen(trim($noaplicacion))>0){
			$sql=$sql." AND supptrans.transno=".$noaplicacion;
		}
		$sql=$sql." AND supptrans.transtext not like '%cancel%'
						AND settled=0
				ORDER BY supptrans.transno"; 
	$result = DB_query($sql, $db);
	// trae todas las monedas que tiene dada de alta la implementacion
	$SQL='SELECT *
	  	  FROM currencies
		  ORDER BY currabrev';
	$result_tag = DB_query($SQL,$db);
	$listamoneda=array();
	$counter=0;
	while ($myrow_bussines = DB_fetch_array($result_tag)) {
		$listamoneda[$counter]=$myrow_bussines['currabrev'];
		$counter=$counter + 1;
	}
	echo "<table border='0' cellpadding='3' cellspacing='3'>";
	$TableHeader = "<tr><th>" . _('Unidad') . "<br>" . _('Negocio') . "</th>
					<th>" . _('Tipo') . "</th>
					<th>" . _('Proveedor') . "</th>
					<th>" . _('Numero') . "</th>
					<th>" . _('Fecha') . "</th>
					<th>" . _('Moneda') . "</th>
					<th>" . _('Total') . "</th>
					<th>" . _('A Aplicar') . "</th>
					<th colspan=".$counter.">" . _('Documentos') . "</th></tr>\n";
	echo $TableHeader;
	/* set up table of Tran Type - Supplier - Trans No - Date - Total - Left to alloc  */
	$k = 0; //row colour counter
	$RowCounter = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1){
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		echo '<td>'.substr($myrow['tagdescription'],stripos($myrow['tagdescription']," ")).'</td>';
		echo '<td>'.$myrow['typename'].'</td>';
		echo '<td>'.$myrow['supplierid'].' - '.$myrow['suppname'].'</td>';
		echo '<td>'.$myrow['transno'].'</td>';
		echo '<td>'.ConvertSQLDate($myrow['trandate']).'</td>';
		echo '<td>'.$myrow['currcode'].'</td>';
		echo '<td>'.number_format($myrow['total'],2).'</td>';
		echo '<td>'.number_format(($myrow['total']-$myrow['alloc']),2).'</td>';
		// muestra link para aplicacion de acuerdo a tipo de moneda
		for($i=0;$i<$counter;$i++){
			echo '<td nowrap style="font-size:7pt;"><a href='.$_SERVER['PHP_SELF'] . "?AllocTrans=".$myrow['id'].'&CurrAplica='.$listamoneda[$i].'&tag='.$_POST['tag'].'>'._('Aplicar en ').$listamoneda[$i].'</a></td>';
		}
	}  //END WHILE LIST LOOP
	echo "</table>";
	if (DB_num_rows($result) == 0) {
		prnMsg(_('No hay aplicaciones por aplicar...'),'info');
	}
}// Fin de validacion de seleccion de documento a aplicar
echo '</form>';
include('includes/footer_Index.inc');
?>
