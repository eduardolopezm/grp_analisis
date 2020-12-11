<?php
/*desarrollo- 17 SEPTIEMBRE 2013 - Se corrigio la desaplicacion para que solo tomara los movimientos de utilidad o perdida cambiaria y no toda la poliza
 * 							- Tambien se corrigio para que desaplicara en la misma fecha y periodo original del pago */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
fecha: 09/12/2009
2.- se arreglo la variable $funcion*/
/*
	This page can be called with...
	1. A SuppTrans TransNo and Type
	The page will then show potential allocations for the transaction called with,
	this page can be called from the supplier enquiry to show the make up and to modify
	existing allocations

	2. A SupplierID
	The page will show all outstanding payments or credits yet to be allocated for the supplier selected

	3. No parameters
	The page will show all outstanding supplier credit notes and payments yet to be
	allocated 
*/
/*
 * AHA 13-Nov-2014
 * Cambio del nombre del boton aceptar por buscar
 *///

include('includes/DefineSuppAllocsClass.php');

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Desaplicaciones de Pagos y Notas de Credito de Proveedores');

include('includes/header.inc');
$funcion=999;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
if (isset($_POST['FromYear'])) {
	$FromYear=$_POST['FromYear'];
} elseif(isset($_GET['FromYear'])) {
        $FromYear=$_GET['FromYear'];
}else{
        $FromYear=date('Y');
}
if (isset($_POST['FromMes'])) {
    $FromMes=$_POST['FromMes'];
}elseif(isset($_GET['FromMes'])) {
    $FromMes=$_GET['FromMes'];
} else {
    $FromMes=date('m');
}
    
if (isset($_POST['FromDia'])) {
    $FromDia=$_POST['FromDia'];
}elseif(isset($_GET['FromDia'])) {
    $FromDia=$_GET['FromDia'];
} else {
    $FromDia=date('d');
    }

if (isset($_POST['ToYear'])) {
    $ToYear=$_POST['ToYear'];
}elseif(isset($_GET['ToYear'])) {
    $ToYear=$_GET['ToYear'];
} else {
    $ToYear=date('Y');
    }

if (isset($_POST['ToMes'])) {
    $ToMes=$_POST['ToMes'];
} elseif(isset($_GET['ToMes'])) {
    $ToMes=$_GET['ToMes'];
}else {
    $ToMes=date('m');
    }
if (isset($_POST['ToDia'])) {
    $ToDia=$_POST['ToDia'];
} elseif(isset($_GET['ToDia'])) {
    $ToDia=$_GET['ToDia'];
}else {
    $ToDia=date('d');
}

if(isset($_POST['selProv'])) {
    $selProv = $_POST['selProv'];
} else {
	$selProv = "";
}

$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
$fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia);
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Desaplicaciones de Pagos a Proveedores') . '" alt="">' . ' ' . _('Desaplicaciones de Pagos a Proveedores') . '</p>';
echo '<p style="text-align:center;"><a href="'.$rootpath.'/SupplierAllocations.php" target="_blank">Ir a Aplicacion de Pagos</a></p>';
echo '<p style="text-align:center;"><a href="'.$rootpath.'/SupplierDesAllocations.php" target="_parent">Regresar</a></p>';


if (isset($_POST['UpdateDatabase']) OR isset($_POST['RefreshAllocTotal'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	if (!isset($_SESSION['Alloc'])){
		prnMsg( _('Desaplicaciones de Pagos a Proveedores no se pueden procesar nuevamente') . '. ' . _('Si refrescas la pagina despues de procesar una asignacion de pago') . ', ' . _('trata de usar las ligas provistas en la pagina para evitar este mensaje'),'warn');
		include('includes/footer.inc');
		exit;
	}

    
/*1st off run through and update the array with the amounts allocated
	This works because the form has an input field called the value of
	AllocnItm->ID for each record of the array - and PHP sets the value of
	the form variable on a post*/

	$TotalAllocated = 0;
	$TotalDiffOnExch = 0;
	//echo "<pre>";
	//print_r($_SESSION['Alloc']);
	
	$espagousd=true;
	if ( $_SESSION['Alloc']->TransExRate==1)
		$espagousd=false;
	
	for ($AllocCounter=0; $AllocCounter < $_POST['TotalNumberOfAllocs']; $AllocCounter++){

		if (!is_numeric($_POST['Amt' . $AllocCounter])){
		      $_POST['Amt' . $AllocCounter] = 0;
		}
		
		if ($_POST['Amt' . $AllocCounter] < 0){
		     prnMsg(_('El monto de la desaplicacion es negativo') . '. ' . _('Un numero positivo es necesario'),'error');
		       $_POST['Amt' . $AllocCounter] = 0;
		}

		if ($_POST['All' . $AllocCounter] == True){
			$_POST['Amt' . $AllocCounter] = $_POST['YetToAlloc' . $AllocCounter];

		}
		
		  /*Now check to see that the AllocAmt is no greater than the
		 amount left to be allocated against the transaction under review */

		if ($_POST['Amt' . $AllocCounter] > $_POST['YetToAlloc' . $AllocCounter]){
		     $_POST['Amt' . $AllocCounter] = $_POST['YetToAlloc' . $AllocCounter];
		}

		$esusd = true;
		if($AllocnItem->ExRate == 1)
			$esusd=false;
		
		
		$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = $_POST['Amt' . $AllocCounter];

		
		 /*recalcuate the new difference on exchange
		 (a +positive amount is a gain -ve a loss)*/
		if ($_POST['Amt' . $AllocCounter]>0){
			
			if ($esusd && !$espagousd){
				$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch = ($_POST['Amt' . $AllocCounter] / $_SESSION['Alloc']->rate) -
				($_POST['Amt' . $AllocCounter]/ $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->ExRate);
			}
			
		}
		$TotalDiffOnExch = $TotalDiffOnExch + $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
		
		if (!$espagousd && $esusd)
			$TotalAllocated = $TotalAllocated + ($_POST['Amt' . $AllocCounter]/$_SESSION['Alloc']->rate);
		else
			if ($espagousd && !$esusd)
			$TotalAllocated = $TotalAllocated + ($_POST['Amt' . $AllocCounter]*$_SESSION['Alloc']->TransExRate);
		else
			$TotalAllocated = $TotalAllocated + $_POST['Amt' . $AllocCounter];
		

	} /*end of the loop to set the new allocation amounts,
	recalc diff on exchange and add up total allocations */

	$TotalDifAllocAmt = 0;
	if (($TotalAllocated - (-$_SESSION['Alloc']->TransAmt)) > 0)
		$TotalDifAllocAmt = $TotalAllocated - (-$_SESSION['Alloc']->TransAmt);
		
	$TotalAllocated -= $TotalDifAllocAmt;
		
	if ($TotalAllocated + $_SESSION['Alloc']->TransAmt > 0.05){
		echo '<br><hr>';
		prnMsg(_('Estas asignaciones no pueden ser procesadas porque el monto desaplicado es mayor al monto de') .
			  ' ' . $_SESSION['Alloc']->TransTypeName  . ' ' . _('siendo aplicado') . '<br>' . _('Total Aplicado') . ' = ' . 			$TotalAllocated . ' ' . _('and the total amount of the Credit/payment was') . ' ' . -$_SESSION['Alloc']->TransAmt,'error');
		echo '<br><hr>';
		$InputError = 1;
	}

}


if (isset($_POST['UpdateDatabase'])){
    $permiso = Havepermission($_SESSION['UserID'], 1527, $db);
   
	if ($InputError == 0){ /* ie all the traps were passed */

	/* actions to take having checked that the input is sensible
	1st set up a transaction on this thread*///

		//$SQL = 'BEGIN';

		$ErrMsg = _('Error Critico') . '! ' . _('El inicio de la TRANSACCION obtuvo error');
		$DbgMsg = _('El SQL que fallo fue');

		//$Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg);
		$Result=DB_Txn_Begin($db);
		$TotalAdesaplicar=0;
		
		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {

			if ($AllocnItem->OrigAlloc >0 AND ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt)){
				/*Orignial allocation was not 0 and it has now changed
				need to delete the old allocation record */
				$SQL = 'DELETE FROM suppallocs WHERE id = ' . $AllocnItem->PrevAllocRecordID;
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The existing allocation for') . ' ' . $AllocnItem->TransType .' ' . $AllocnItem->TypeNo . ' ' . _('could not be deleted because');
				$DbgMsg = _('The following SQL to delete the allocation record was used');
				//$Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				
				//echo "<br>1.- " . $SQL;
			}

			
			//if ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt){
				/*Only when there has been a change to the allocated amount
				do we need to insert a new allocation record and update
				the transaction with the new alloc amount and diff on exch */
            
				//if ($AllocnItem->AllocAmt > 0){
					/*
					$SQL = "INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto) VALUES ('" . FormatDateForSQL(date($_SESSION['DefaultDateFormat'])) . "', 
						" . $AllocnItem->AllocAmt . ', 
						' . $_SESSION['Alloc']->AllocTrans . ', 
						' . $AllocnItem->ID . ')';
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  _('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' ._('could not be inserted because');
					$DbgMsg = _('The following SQL to insert the allocation record was used');
					$Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);*/
					  
					$ssql = "select day(datealloc) as dia, month(datealloc) as mes, year(datealloc) as anio,amt
                            from suppallocs
					where transid_allocfrom=" . $_SESSION['Alloc']->AllocTrans .
					" and transid_allocto= " . $AllocnItem->ID;

					$ErrMsg = _('No se recupero ninguna transaccion por SQL porque');
					$sResult = DB_query($ssql,$db,$ErrMsg);
            
                    $montoaplicado = 0;
            
                    if (DB_num_rows($sResult) > 0) {
                        $smyrow= DB_fetch_array($sResult);
                        
                        $diaaplicacion = $smyrow['dia'];
						$mesaplicacion = $smyrow['mes'];
						$anioaplicacion = $smyrow['anio'];
						$montoaplicado = $smyrow['amt'];
                    }
            
					$esusd = true;
					if($AllocnItem->ExRate == 1)
						$esusd=false;
					
					if ($esusd and !$espagousd)
						$TotalAdesaplicar+= $montoaplicado/$_SESSION['Alloc']->rate;
					else 
						if (!$esusd and $espagousd)
							$TotalAdesaplicar+=$montoaplicado*$_SESSION['Alloc']->TransExRate;
						else 
							$TotalAdesaplicar+=$montoaplicado;
						
					//$SQL="UPDATE supptrans set ref2=CONCAT_WS(',','".$AllocnItem->ID."',ref2) WHERE id='".$_SESSION['Alloc']->AllocTrans."'";
                                        //$Result=  DB_query($SQL, $db);//
					$SQL= "Delete from suppallocs
					where transid_allocfrom=".$_SESSION['Alloc']->AllocTrans."
					and transid_allocto= ".$AllocnItem->ID;
					
					//echo "<br>2.-" . $SQL;
					if( !$Result = DB_query($SQL,$db)){
						$error = 'No pude cambiar registro de aplicacion';
					}
					$AllAllocations = $AllAllocations + $montoaplicado;
					$Settled = 0;
                                        $Pendiente = 2;
                                        
                                        //permiso para definir el estatus de la orden
                                        if($permiso == 1){
                                            $Pendiente = 0; 
                                        }
                                        
					$SQL = 'UPDATE supptrans
							SET diffonexch=diffonexch - ' . $AllocnItem->DiffOnExch . ',
							alloc = alloc-' .  $montoaplicado . ',
							settled = ' . $Settled . ',
                                                        hold = '.$Pendiente.'
							WHERE id = ' . $AllocnItem->ID;
					//2
					if( !$Result = DB_query($SQL,$db)){
						$error = 'No pude aplicar diferencia cambiaria';
					}  
					//echo "<br>3.-" . $SQL;
				//}
            
				$NewAllocTotal = $AllocnItem->PrevAlloc + $montoaplicado;
				
				/* $SQL = 'UPDATE supptrans SET diffonexch=' . $AllocnItem->DiffOnExch . ', 
					alloc = ' .  $NewAllocTotal . ', 
					settled = ' . $Settled . ' 
					WHERE id = ' . $AllocnItem->ID;
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
					$DbgMsg = _('The following SQL to update the debtor transaction record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);*/

			//} /*end if the new allocation is different to what it was before */

		}  /*end of the loop through the array of allocations made */

		/*Now update the payment or credit note with the amount allocated
		and the new diff on exchange *///

		
		if ($_SESSION['Alloc']->TransType == 121){
			//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
			$Transtype = 123;
			$TransNo = GetNextTransNo($Transtype, $db);
			$fecha = Date($_SESSION['DefaultDateFormat']);
			
			$PeriodNo = GetPeriod($fecha, $db, $_SESSION['Alloc']->TransTag);
			$tipoproveedor = ExtractTypeSupplier($_SESSION['Alloc']->SupplierID,$db);
			$ctaanticipoproveedor = SupplierAccount($tipoproveedor,"gl_debtoradvances",$db);
			$ctaproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
			
			$reference = $_SESSION['Alloc']->SupplierID . "@" . $TransNo  . "@ANTICIPO DESAPLICADO: " . $TotalAllocated;
			
                        //si no tiene el permiso insertará los movimentos con estatus autorizado// 
                        if($permiso == 0){ 
                            
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
                                            " . -$TotalAdesaplicar . ",
                                            " . $_SESSION['Alloc']->TransTag . ",
                                            '0'
                                    )";

                            $ErrMsg = _('EL SQL FALLO DEBIDO A ');
                            $DbgMsg = _('ES SQL UTILIZADO ES');
                            $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
                                            " . $TotalAdesaplicar . ",
                                            " . $_SESSION['Alloc']->TransTag . ",
                                            '0'
                                    )";

                            $ErrMsg = _('EL SQL FALLO DEBIDO A ');
                            $DbgMsg = _('ES SQL UTILIZADO ES');
                            $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


                        }
			
		}
				
		
		$Settled = 0;
		$TotalAdesaplicar-=$TotalDifAllocAmt; //alloc+' .  $TotalAdesaplicar . ',
		$SQL = 'UPDATE supptrans
			SET alloc = 0  , 
				diffonexch = diffonexch +' . $TotalDiffOnExch . ', 
				settled=' . $Settled . ',
                                hold = 2 
			WHERE id = ' . $_SESSION['AllocTrans'];
		
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
					 _('The supplier payment or credit note transaction could not be modified for the new allocation and exchange difference because');
		$DbgMsg = _('The following SQL to update the payment or credit note was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		//echo "<br>4.-" . $SQL;
		
		/*Almost there ... if there is a change in the total diff on exchange
		 and if the GLLink to debtors is active - need to post diff on exchange to GL */
		
		/* CRITERIO DE DESAPLICAR EN MISMO PERIODO DEL PAGO ... */

		$MovtInDiffOnExch = $_SESSION['Alloc']->PrevDiffOnExch + $TotalDiffOnExch;
		if ($MovtInDiffOnExch !=0 ){
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1){
				$PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate, $db, $_SESSION['Alloc']->TransTag);
				$_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);
				$PeriodNo = GetPeriod(date("d/m/Y"), $db, $_SESSION['Alloc']->TransTag);
				$csql = "select type, typeno
					from gltrans 
					where type =". $_SESSION['Alloc']->TransType."
					and typeno = " . $_SESSION['Alloc']->TransNo . "
					and chequeno = '0' 
					group by type, typeno";
				$ErrMsg = _('No se recupero ninguna transaccion por SQL porque');
				$cResult = DB_query($csql,$db,$ErrMsg);
				//echo "<br>5.-" . $csql;
				while ($cmyrow=DB_fetch_array($cResult)) {	
					if ((saldo - abs($AllocnItem->AllocAmt)) < 0.01){
						$isql  = "insert into gltrans (counterindex, type, typeno, chequeno, trandate, periodno,
						account, narrative, amount, posted, jobref, tag)
						select NULL, type, typeno, chequeno, trandate, periodno, account,
						concat(narrative,' DESAPLICACION DE PAGOS'), (amount*-1), posted, jobref, tag
						from gltrans
						where type = '" . $cmyrow['type'] . "'
						and typeno = '" . $cmyrow['typeno'] . "'
						and chequeno ='0' and narrative like '%UTIL/PERD CAMBIARIA%'
						order by counterindex desc limit 4 ";
						$ErrMsg = _('No se recupero ninguna transaccion por SQL porque');
						$iResult = DB_query($isql,$db,$ErrMsg);
						//echo "<br>6.-" . $isql;
						
					}
				}	
			}
		}
                
	 /* OK Commit the transaction */

		//$SQL = 'COMMIT';

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
					 _('The updates and insertions arising from this allocation could not be committed to the database');

		$DbgMsg = _('The COMMIT SQL failed');

		//$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
		$Result = DB_Txn_Commit($db);
	/*finally delete the session variables holding all the previous data */

		unset($_SESSION['AllocTrans']);
		unset($_SESSION['Alloc']);
		unset($_POST['AllocTrans']);

	} /* end of processing required if there were no input errors trapped */
}

/*The main logic determines whether the page is called with a Supplier code
a specific transaction or with no parameters ie else
If with a supplier code show just that supplier's payments and credits for allocating
If with a specific payment or credit show the invoices and credits available
for allocating to  */

echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

if (isset($_POST['SupplierID'])){
 	$_GET['SupplierID'] = $_POST['SupplierID'];
	echo "<input type='hidden' name='SupplierID' VALUE='" . $_POST["SupplierID"] . "'>";
}

If (isset($_GET['AllocTrans'])){

	/*page called with a specific transaction ID for allocating
	SupplierID may also be set but this is the logic to follow
	the SupplierID logic is only for showing the payments and credits to allocate*/


	/*The logic is:
	- read in the transaction into a session class variable
	- read in the invoices available for allocating to into a session array of allocs object
	- Display the supplier name the transaction being allocated amount and trans no
	- Display the invoices for allocating to with a form entry for each one
	for the allocated amount to be entered */


	$_SESSION['Alloc'] = new Allocation();

	/*The session varibale AllocTrans is set from the passed variable AllocTrans
	on the first pass */

	/* OBTIENE DE LIGA DE LA PAGINA EL NUMERO DE TRANSACCION DEL PAGO A ASIGNAR*/
	$_SESSION['AllocTrans'] = $_GET['AllocTrans'];
	$_POST['AllocTrans'] = $_GET['AllocTrans'];

	/*********************************************************************/
	/* BUSCAR EN BASE DE DATOS LOS DETALLES DEL PAGO QUE VAMOS A ASIGNAR */
	$SQL= 'SELECT systypescat.typename, 
			supptrans.type, 
			supptrans.transno, 
			supptrans.trandate, 
			supptrans.supplierno,
			suppliers.suppname, 
			rate, 
			(supptrans.ovamount+supptrans.ovgst) AS total, 
			supptrans.diffonexch,
			supptrans.alloc,
			supptrans.tagref
		    FROM supptrans, 
		    	systypescat, 
			suppliers
		    WHERE supptrans.type = systypescat.typeid
		    AND supptrans.supplierno = suppliers.supplierid
		    AND supptrans.id=' . $_SESSION['AllocTrans'];

	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result) != 1){
	echo _('There was a problem retrieving the information relating the transaction selected') . '. ' . _('Allocations are unable to proceed');
	if ($debug == 1){
		echo '<br>' . _('The SQL that was used to retrieve the transaction information was') . " :<br>$SQL";
	}
		exit;
	}

	$myrow = DB_fetch_array($Result);

	$_SESSION['Alloc']->AllocTrans = $_SESSION['AllocTrans'];
	$_SESSION['Alloc']->SupplierID = $myrow['supplierno'];
	$_SESSION['Alloc']->SuppName = $myrow['suppname'];;
	$_SESSION['Alloc']->TransType = $myrow['type'];
	$_SESSION['Alloc']->TransTypeName = $myrow['typename'];
	$_SESSION['Alloc']->TransNo = $myrow['transno'];
	$_SESSION['Alloc']->TransExRate = $myrow['rate'];
	$_SESSION['Alloc']->TransAmt = $myrow['total'];
	$_SESSION['Alloc']->PrevDiffOnExch = $myrow['diffonexch'];
	$_SESSION['Alloc']->TransDate = ConvertSQLDate($myrow['trandate']);
	$_SESSION['Alloc']->TransTag = $myrow['tagref'];
	/*********************************************************************/
	//buscar tipo de cambio por la fecha del pago
	$rate="";
	$qry = "Select * FROM tipocambio
			WHERE fecha = '".ConvertSQLDate($_SESSION['Alloc']->TransDate)."'";
	$rstc = DB_query($qry,$db);
	if (DB_num_rows($rstc) > 0){
		$reg = DB_fetch_array($rstc);
		$rate = $reg['rate'];
	}
	else{
		$qry = "Select * FROM tipocambio
				WHERE fecha = current_date";
		$rstc = DB_query($qry,$db);
		if (DB_num_rows($rstc) > 0){
			$reg = DB_fetch_array($rstc);
			$rate = $reg['rate'];
		}
	
	}
	$_SESSION['Alloc']->rate = $rate;

	
	/* Now populate the array of possible (and previous actual) allocations for this supplier */
	/*First get the transactions that have outstanding balances ie Total-Alloc >0 */

	/***************************************************************************************/
	/* AHORA OBTIENE TRANSACCIONES QUE TIENEN SALDO PENDIENTE DE APLICAR DE ESTE PROVEEDOR */
	$SQL= "SELECT supptrans.id, 
			typename, 
			transno, 
			trandate, 
			suppreference, 
			rate,
			ovamount+ovgst AS total, 
			diffonexch, 
			alloc,
			tagref
		FROM supptrans, 
			systypescat
		WHERE supptrans.type = systypescat.typeid
		AND supptrans.settled=0
		AND abs(ovamount+ovgst-alloc)>0.009
		AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'";

	$ErrMsg = _('There was a problem retrieving the transactions available to allocate to');

	$DbgMsg = _('The SQL that was used to retrieve the transaction information was');

	//$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

	/*while ($myrow=DB_fetch_array($Result)){
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
							$myrow['tagref']);
	}*/
	/***************************************************************************************/
	
	/* Now get trans that might have previously been allocated to by this trans
	NB existing entries where still some of the trans outstanding entered from
	above logic will be overwritten with the prev alloc detail below */

	/***************************************************************************************/
	/* AHORA OBTIENE TRANSACCIONES QUE HAYAN SIDO SALDADAS CON ESTE PAGO PREVIAMENTE       */
	$SQL = "SELECT supptrans.id, 
			typename, 
			transno, 
			trandate, 
			suppreference, 
			rate,
			ovamount+ovgst AS total, 
			diffonexch, 
			supptrans.alloc-suppallocs.amt AS prevallocs,
			amt, 
			suppallocs.id AS allocid,
			tagref,
			(case when rate = 1 then 'MXN' ELSE 'USD' end) as currcode
			  FROM supptrans, 
			  	systypescat, 
				suppallocs
			  WHERE supptrans.type = systypescat.typeid
			  AND supptrans.id=suppallocs.transid_allocto
			  AND suppallocs.transid_allocfrom=" . $_SESSION['AllocTrans'];
        if ($_SESSION['Alloc']->TransType!=24) {
            $SQL.=" AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'";
        }
			  $SQL.=" AND supplierno='" . $_SESSION['Alloc']->SupplierID . "'";
//var_dump($SQL);
                          
	$ErrMsg = _('There was a problem retrieving the previously allocated transactions for modification');

	$DbgMsg = _('The SQL that was used to retrieve the previously allocated transaction information was');

	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

	while ($myrow = DB_fetch_array($Result)){

		$DiffOnExchThisOne = ($myrow['amt']/$myrow['rate']) - ($myrow['amt']/$_SESSION['Alloc']->TransExRate);

		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'], $myrow['typename'], $myrow['transno'],
								ConvertSQLDate($myrow['trandate']), $myrow['suppreference'], $myrow['amt'],
								$myrow['total'], 
								$myrow['rate'], 
								$DiffOnExchThisOne,
								($myrow['diffonexch'] - $DiffOnExchThisOne), 
								$myrow['prevallocs'], 
								$myrow['allocid'],
								$myrow['tagref'],
								$myrow['currcode']);
		
		
		
	}
}

if (isset($_POST['AllocTrans'])){
//Extrae la razon social del abono

$LegalAbono=ExtractLegalid($_SESSION['Alloc']->TransTag ,$db);
	echo "<input type='hidden' name='AllocTrans' VALUE='" . $_POST["AllocTrans"] . "'>";

	/*Show the transaction being allocated and the potential trans it could be allocated to
        and those where there is already an existing allocation */

        echo '<hr><div class="centre"><font color=BLUE>' . _('Desaplicacion de Pagos a Proveedores') . ' ' .
        		 $_SESSION['Alloc']->TransTypeName . ' ' . _('numero') . ' ' .
        		 $_SESSION['Alloc']->TransNo . ' ' . _('desde') . ' ' .
        		 $_SESSION['Alloc']->SupplierID . ' - <b>' .
        		 $_SESSION['Alloc']->SuppName . '</b>, ' . _('dated') . ' ' .
        		 $_SESSION['Alloc']->TransDate;

        if ($_SESSION['Alloc']->TransExRate != 1){
	     	  echo '<br>' . _("Monto en moneda del proveedor"). ' <b>' .
	     	  		 number_format(-$_SESSION['Alloc']->TransAmt,2) . '</b><i> (' .
	     	  		 _('converted into local currency at an exchange rate of') . ' ' .
	     	  		 $_SESSION['Alloc']->TransExRate . ')</i><p>';

        } else {

		     echo '<br>' . _('Transaccion total') . ': <b>' . -$_SESSION['Alloc']->TransAmt . '</b></div>';
        }

        echo '<hr>';

   /*Now display the potential and existing allocations put into the array above *///

        echo '<table cellpadding=2 colspan=7 BORDER=0>';
	  	  $TableHeader = "<tr><th>" . _('Tipo') . "</th>
		 			<th>" . _('Desaplicacion') . '<br>' . _('Numero') . "</th>
					<th>" . _('desaplicacion') .'<br>' . _('Fecha') . "</th>
					<th>" . _('Prov') . '<br>' . _('Ref') . "</th>
					<th>" . _('Total') . '<br>' . _('Monto') .	"</th>
					<th>" . _('Por') . '<br>' . _(' DesAplicar') . "</th>
					<th>" . _('Esta') . '<br>' . _('Desaplicacion') . '</th></tr>';
        $k = 0;
	$Counter = 0;
	$RowCounter = 0;
        $TotalAllocated = 0;

        foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {
        	
        	$esusd=false;
        	if($AllocnItem->currcode!="MXN"){
        		$esusd=true;
        	}

	    /*Alternate the background colour for each potential allocation line */

	    if ($k == 1){
		    echo '<tr class="EvenTableRows">';
		    $k = 0;
	    } else {
		    echo '<tr class="OddTableRows">';
		    $k = 1;
	    }
	    $RowCounter++;

	    if ($RowCounter == 15){

		/*Set up another row of headings to ensure always a heading on the screen of potential allocns*/

			echo $TableHeader;

			$RowCounter = 1;

	    }

	    $YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);

	    echo "<td>$AllocnItem->TransType</td>
	    		<td>$AllocnItem->TypeNo</td>
			<td>$AllocnItem->TransDate</td>
	    		<td>$AllocnItem->SuppRef</td>
			<td align=right>" . number_format($AllocnItem->TransAmount,2) . '</td>
	    		<td align=right>' . number_format($YetToAlloc,2) . "<input type=hidden name='YetToAlloc" .
	    		 $Counter . "' VALUE=" . $YetToAlloc . '></td>';

	    echo "<td align=right><input type='checkbox' name='All" .  $Counter . "'";
	    $LegalCargo=ExtractLegalid($AllocnItem->tagref ,$db);
	    if (ABS($AllocnItem->AllocAmt-$YetToAlloc) < 0.01){
			
			if ($LegalAbono ==$LegalCargo){
			echo ' VALUE=' . True . '>';
			}else{
				echo '>';		
			}
	    } else {
	    	echo '>';
	    }
	    if ($LegalAbono ==$LegalCargo){
       		echo "<input type=text class='number' name='Amt" . $Counter ."' maxlength=12 size=13 VALUE=" .
       		number_format($YetToAlloc,2) . " readonly><input type=hidden name='AllocID" . $Counter .
       		"' VALUE=" . $AllocnItem->ID . '></td></tr>';
	    }else{
		//echo $LegalCargo;
		echo "
		
		<input class='number' maxlength=12 size=13 type=text disabled name='Amtx" . $Counter ."' VALUE=0>
		<input type=hidden name='AllocID" . $Counter ."' VALUE=" . $AllocnItem->ID . ">
		<input type=hidden class='number' name='Amt" . $Counter ."' maxlength=12 size=13 VALUE=0>
		</td></tr>";
	    }
	    

	    if (!$usdpago && $esusd)
		 	$TotalAllocated = $TotalAllocated + ($AllocnItem->AllocAmt/$_SESSION['Alloc']->rate);
		else
			if ($usdpago && !$esusd)	
			 	$TotalAllocated = $TotalAllocated + ($AllocnItem->AllocAmt*$_SESSION['Alloc']->TransExRate);
			else	
				$TotalAllocated = $TotalAllocated + $AllocnItem->AllocAmt ;

	    $Counter++;

   }

   $TotalDifAllocAmt = 0;
   if (($TotalAllocated - (-$_SESSION['Alloc']->TransAmt)) > 0)
   		$TotalDifAllocAmt = $TotalAllocated - (-$_SESSION['Alloc']->TransAmt);
   
   $TotalAllocated -= $TotalDifAllocAmt;
   
   
   echo '<tr><td colspan=5 align=right><b><U>' . _('Total Allocated') . ':</U></b></td>
   		<td align=right><b><U>' .  number_format($TotalAllocated,2) . '</U></b></td></tr>';

   echo '<tr><td colspan=5 align=right><b>' . _('Left to allocate') . '</b></td><td align=right><b>' .
     		number_format(-$_SESSION['Alloc']->TransAmt - $TotalAllocated,2) . '</b></td></tr></table>';

   echo "<div class='centre'><input type=hidden name='TotalNumberOfAllocs' VALUE=$Counter>";

   echo "<input type=submit name='RefreshAllocTotal' VALUE='" . _('Recalcula Total a Desaplicar') . "'>";
   echo "<input type=submit name=UpdateDatabase VALUE='" . _('Procesa Desaplicaciones') . "'></div>";

} elseif(((isset($_POST['SupplierID']) and strlen($_POST['SupplierID'])>0) or isset($_GET['SupplierID']) and !isset($_POST['selProv'])) or ($_POST['selProv']==0)){

  /*page called with a supplier code  so show the transactions to allocate
  specific to the supplier selected */

  
  
        echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post name="form">';
	
//Select the tag





	echo '<table>';
	echo '<tr><td colspan=2>';
	echo '<table border = 0> <tr>
		    <td style="text-align:right;">' . _('Desde:') . '</td>
		    <td><select Name="FromDia">';
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
	            echo'</select>'; 
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
		echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
				      
		      echo '</td>
		    <td>
			 &nbsp;
	            </td>
	            <td style="text-align:right;">' . _('Hasta:') . '</td>';
		    echo'<td><select Name="ToDia">';
			      $sql = "SELECT * FROM cat_Days";
			      $Todias = DB_query($sql,$db);
			      while ($myrowTodia=DB_fetch_array($Todias,$db)){
				  $Todiabase=$myrowTodia['DiaId'];
				  if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
				      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
				  }else{
				      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
				  }
			      }
		    echo'</select>'; 
		    echo'';
			 echo'<select Name="ToMes">';
			 $sql = "SELECT * FROM cat_Months";
			 $ToMeses = DB_query($sql,$db);
			 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
			     $ToMesbase=$myrowToMes['u_mes'];
			     if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
			     }else{
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
			     }
			 }
			 echo '</select>';
			 echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
			 
		    echo'</td>
	       </tr>';
	 echo '</table>';
	 echo '</td>';
	 echo '</tr>';
	 
	echo '<tr>
		<td>'
			._('Seleccione Una Razon Social:').'
		</td>
		<td>';
			///Pinta las razones sociales
			$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
			$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
			$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
			$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
					  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		
			echo '<select name="legalid">';		
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
					echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				} else {
					echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
	
		echo '<tr><td>'._('Unidad de Negocios:').'</td><td><select name="tag">';
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
		
	$result=DB_query($SQL,$db);
	echo '<option value="*">Todas a las que tengo acceso...';
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
		<td  colspan=3>';
		echo "<select Name='tipodocumento' value='tipodocumento'><br>";
		$sqlr = "SELECT DISTINCT typeid, typename
			FROM systypescat
			WHERE typeid in (21,22,24,32,33,37,121,480,490,501,124)
			order by typename";
		$grupor= DB_query($sqlr,$db);
		echo '<option  VALUE="" selected>Seleccionar';
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
	
// End select tag

	echo '
	<tr><td>'._('Todos los proveedores:').'</td><td><select name="selProv">';
	if($selProv === "1") {
		echo '<option selected="selected" value=1>Todos los proveedores...';
	} else {
		echo '<option value=1>Todos los proveedores...';
	}
	if($selProv === "0") {
		echo '<option selected="selected" value=0>Proveedor Seleccionado...';
	} else {
		echo '<option value=0>Proveedor Seleccionado...';
	}
	echo '</select></td></tr>';
	if(isset($_GET['SupplierID'])){
		$SupplierID=$_GET['SupplierID'];
	}elseif(isset($_POST['SupplierID'])){
		$SupplierID=$_POST['SupplierID'];
	}
	echo '<tr><td>'._('Clave proveedor:').'</td>';
	echo'<td><input type=text name=SupplierID VALUE="' . $SupplierID . '"></td></tr>';
	
// End select tag

	echo '<tr><td></td><td>';
	//echo "<input type=hidden name=SupplierID VALUE='" . $_GET['SupplierID'] . "'>";
        echo "<input type=submit name=Process value='" . _('Buscar') . "'></td></tr></table>";

  /*Clear any previous allocation records */

  unset($_SESSION['Alloc']);

  $sql = "SELECT id, 
  		transno, 
		typename, 
		type, 
		suppliers.supplierid, 
		suppname, 
		trandate,
  		suppreference, 
		rate, 
		ovamount+ovgst AS total, 
		alloc,
		supptrans.tagref
  	FROM supptrans, 
		suppliers, 
		systypescat
  	WHERE supptrans.type=systypescat.typeid
  	AND supptrans.supplierno=suppliers.supplierid
  	AND suppliers.supplierid='" . $_GET['SupplierID'] ."'
	AND trandate between '".$fechaini."' AND '".$fechafin." 23:59:00'
	AND type  in (21,22,24,32,33,37,121,480,490,501,124) AND settled=1 ORDER BY id";
  

	//echo $sql;
	
  $result = DB_query($sql, $db);
  if (DB_num_rows($result) == 0){
	prnMsg(_('There are no outstanding payments or credits yet to be allocated for this supplier'),'info');
	include('includes/footer.inc');
	exit;
  }
  echo '<table>';

  $TableHeader = "<tr><th>" . _('Unidad Negocio') .
		"</th><th>" . _('Tipo Docto') .
		"</th><th>" . _('Proveedor') .
		"</th><th>" . _('Numero') .
		"</th><th>" . _('Fecha') .
		"</th><th>" . _('Total') .
		"</th><th>" . _('Aplicado') . "</th></tr>\n";

  echo $TableHeader;

  /* set up table of TransType - Supplier - Trans No - Date - Total - Left to alloc  */

  $RowCounter = 0;
  $k = 0; //row colour counter
  while ($myrow = DB_fetch_array($result)) {
	if ($k == 1){
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td align=right>%0.2f</td>
		<td align=right>%0.2f</td>
		<td><a href='%sAllocTrans=%s'>" . _('DesAplicar') .'</td>
		</tr>',
		$myrow['tagref'],
		$myrow['typename'],
		$myrow['suppname'],
		$myrow['transno'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['total'],
		$myrow['total']-$myrow['alloc'],
		$_SERVER['PHP_SELF'] . "?" . SID,
		$myrow['id']);

  }

} else { /* show all outstanding payments and credits to be allocated */

  /*Clear any previous allocation records */

  unset($_SESSION['Alloc']->Allocs);
  unset($_SESSION['Alloc']);
  
        echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post name="form">';
	echo '<table>';
	echo '<tr><td colspan=2>';
	echo '<table border = 0> <tr>
		    <td style="text-align:right;">' . _('Desde:') . '</td>
		    <td><select Name="FromDia">';
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
	            echo'</select>'; 
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
		echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
				      
		      echo '</td>
		    <td>
			 &nbsp;
	            </td>
	            <td style="text-align:right;">' . _('Hasta:') . '</td>';
		    echo'<td><select Name="ToDia">';
			      $sql = "SELECT * FROM cat_Days";
			      $Todias = DB_query($sql,$db);
			      while ($myrowTodia=DB_fetch_array($Todias,$db)){
				  $Todiabase=$myrowTodia['DiaId'];
				  if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
				      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
				  }else{
				      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
				  }
			      }
		    echo'</select>'; 
		    echo'';
			 echo'<select Name="ToMes">';
			 $sql = "SELECT * FROM cat_Months";
			 $ToMeses = DB_query($sql,$db);
			 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
			     $ToMesbase=$myrowToMes['u_mes'];
			     if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
			     }else{
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
			     }
			 }
			 echo '</select>';
			 echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
			 
		    echo'</td>
	       </tr>';
	 echo '</table>';
	 echo '</td>';
	 echo '</tr>';
	
	echo'<tr>
		<td>'
			._('Seleccione Una Razon Social:').'
		</td>
		<td>';
			///Pinta las razones sociales
			$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
			$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
			$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
			$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
					  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		
			echo '<select name="legalid">';		
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
					echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				} else {
					echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
//Select the tag
	echo '
		<tr><td>'._('Unidad de Negocios:').'</td><td><select name="tag">';
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
		
	$result=DB_query($SQL,$db);
	echo '<option value="*">Todas a las que tengo acceso...';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo '</select></td>';
// End select tag


echo '<tr>
		<td style="text-align:right;">' . _('X Tipo de Documento') . ':</td>
		<td  colspan=3>';
		echo "<select Name='tipodocumento' value='tipodocumento'><br>";
		$sqlr = "SELECT DISTINCT typeid, typename
			FROM systypescat
			WHERE typeid  in (21,22,24,32,33,37,121,480,490,501,124)
			order by typename";
		$grupor= DB_query($sqlr,$db);
		echo '<option  VALUE="" selected>Seleccionar';
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
echo '
	<tr><td>'._('Todos los proveedores:').'</td><td><select name="selProv">';
	if($selProv === "1") {
		echo '<option selected="selected" value=1>Todos los proveedores...';
	} else {
		echo '<option value=1>Todos los proveedores...';
	}
	if($selProv === "0") {
		echo '<option selected="selected" value=0>Proveedor Seleccionado...';
	} else {
		echo '<option value=0>Proveedor Seleccionado...';
	}
	echo '</select></td></tr>';
	if(isset($_GET['SupplierID'])){
		$SupplierID=$_GET['SupplierID'];
	}elseif(isset($_POST['SupplierID'])){
		$SupplierID=$_POST['SupplierID'];
	}
	echo '<tr><td>'._('Clave proveedor:').'</td>';
	echo'<td><input type=text name=SupplierID VALUE="' . $SupplierID . '"></td></tr>';
// End select tag

	echo '<tr><td></td><td>';

        echo "<input type=submit name=Process value='" . _('Buscar') . "'></td></tr></table>";

  if ($_POST['tag']=!"*" and !empty($_POST['tag'])) {
	$sql = "SELECT id, 
  		transno, 
		typename, 
		type, 
		suppliers.supplierid, 
		suppname, 
		trandate,
  		suppreference, 
		rate, 
		ovamount+ovgst AS total, 
		alloc,
		supptrans.tagref,
		tags.tagdescription
  	FROM supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "', 
		suppliers, 
		systypescat,
		tags
  	WHERE supptrans.type=systypescat.typeid
  	AND supptrans.supplierno=suppliers.supplierid
	AND trandate between '".$fechaini."' AND '".$fechafin." 23:59:00'
  	AND type  in (21,22,24,32,33,37,121,480,490,501,124) ";
	if (strlen($_POST['SupplierID'])>0){
		$sql=$sql."AND (suppliers.supplierid='" . $_POST['SupplierID'] ."' OR '" . $_POST['selProv'] ."' = 0)";
	}
	$sql=$sql." AND (supptrans.tagref = '".$_POST['tag']."' OR '".$_POST['tag']."' = '0')";
	if (strlen($_POST['legalid'])>0){
		$sql=$sql." AND (tags.legalid = '".$_POST['legalid']."')";
	}
	if (strlen($_POST['tipodocumento'])>0){
		$sql=$sql." AND (systypescat.typeid = '".$_POST['tipodocumento']."')";
	}
	$sql=$sql." AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
	AND alloc <> 0
	AND supptrans.tagref = tags.tagref 
	ORDER BY supptrans.transno";
	
  	//ORDER BY id";
	
  } else {

  $sql = "SELECT id, 
        transno,
		typename, 
		type, 
		suppliers.supplierid, 
		suppname, 
		trandate,
  		suppreference, 
		rate, 
		ovamount+ovgst AS total, 
		alloc,
		supptrans.tagref,
		tagdescription
  	FROM supptrans,
		tags,
		sec_unegsxuser,
		suppliers, 
		systypescat
  	WHERE supptrans.type=systypescat.typeid
  	AND supptrans.supplierno=suppliers.supplierid
  	AND type in (21,22,24,32,33,116,121,124)
  	AND alloc <> 0
	AND supptrans.tagref = tags.tagref
	AND trandate between '".$fechaini."' AND '".$fechafin." 23:59:00'
	and tags.tagref  = sec_unegsxuser.tagref";
	
	if (strlen($_POST['legalid'])>0){
		$sql=$sql." AND (tags.legalid = '".$_POST['legalid']."')";
	}
	
	if (strlen($_POST['tipodocumento'])>0){
		$sql=$sql." AND (systypescat.typeid = '".$_POST['tipodocumento']."')";
	}
	$sql=$sql." AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
	ORDER BY supptrans.transno";
	
	
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
	
  }
  
// echo "sql-><pre>" . $sql;
  $result = DB_query($sql, $db);

  echo "<table border='0' cellpadding='3' cellspacing='3'>";
  $TableHeader = "<tr><th>" . _('Unidad') . "<br>" . _('Negocio') . "</th>
		<th>" . _('Tipo') . "</th>
  		<th>" . _('Proveedor') . "</th>
  		<th>" . _('Numero') . "</th>
  		<th>" . _('Fecha') . "</th>
  		<th>" . _('Total') . "</th>
  		<th>" . _('Aplicado') . "</th>
		<th>" . _('Mas Info') . "</th></tr>\n";

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

	printf("<td style='font-size:7pt;'>%s</td>
		<td style='font-size:8pt;'>%s</td>
		<td style='font-size:7pt;'>%s</td>
		<td style='font-size:8pt; text-align:right;'>%s</td>
		<td style='font-size:8pt;'>%s</td>
		<td style='font-size:8pt; text-align:right;' align=right>%0.2f</td>
		<td style='font-size:8pt; text-align:right;' align=right>%0.2f</td>
		<td style='font-size:8pt;'><a href='%sAllocTrans=%s'>" . _('Desaplicar') . '</td>
		</tr>',
		substr($myrow['tagdescription'],stripos($myrow['tagdescription']," ")),
		$myrow['typename'],
		$myrow['suppname'],
		$myrow['transno'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['total'],
		$myrow['alloc'],
		$_SERVER['PHP_SELF'] . "?" . SID,
		$myrow['id']);


  }  //END WHILE LIST LOOP

  echo "</table>";

  if (DB_num_rows($result) == 0) {
	prnMsg(_('No hay Desaplicaciones por desaplicar...'),'info');
  }

} /* end of else if not a SupplierID or transaction called with the URL */

echo '</form>';
include('includes/footer.inc');
?>
