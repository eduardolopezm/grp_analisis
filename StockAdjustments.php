<?php

include('includes/DefineStockAdjustment.php');
include('includes/DefineSerialItems.php');

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Ajustes de Inventario');

include('includes/header.inc');
$funcion=48;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
/*
if (isset($_GET['NewAdjustment'])){
     unset($_SESSION['Adjustment']);
     $_SESSION['Adjustment'] = new StockAdjustment;
}

if (!isset($_SESSION['Adjustment'])){
     $_SESSION['Adjustment'] = new StockAdjustment;
}




if (isset($_GET['StockID'])){
	$_SESSION['Adjustment']->StockID = trim(strtoupper($_GET['StockID']));
	$NewAdjustment = true;
} elseif (isset($_POST['StockID'])){
	if ($_POST['StockID'] != $_SESSION['Adjustment']->StockID){
		$NewAdjustment = true;
		$_SESSION['Adjustment']->StockID = trim(strtoupper($_POST['StockID']));
	}
	$_SESSION['Adjustment']->Narrative = $_POST['Narrative'];
	$_SESSION['Adjustment']->StockLocation = $_POST['StockLocation'];
	if ($_POST['Quantity']=='' or !is_numeric($_POST['Quantity'])){
		$_POST['Quantity']=0;
	}
	$_SESSION['Adjustment']->Quantity = $_POST['Quantity'];
}

if()
*/

if (isset($_POST['FromYear'])) {
	$FromYear2=$_POST['FromYear'];
} elseif(isset($_GET['FromYear'])) {
	$FromYear2=$_GET['FromYear'];
}else{
	$FromYear2=date('Y');
}

if (isset($_POST['FromMes'])) {
	$FromMes2=$_POST['FromMes'];
} elseif(isset($_GET['FromMes'])) {
	$FromMes2=$_GET['FromMes'];
}else{
	$FromMes2=date('m');
}

if (isset($_GET['FromDia'])) {
	$FromDia2=$_GET['FromDia'];
}elseif(isset($_POST['FromDia'])) {
	$FromDia2=$_POST['FromDia'];
}else{
	$FromDia2=date('d');
}

$fechaSeleccionada = trim($FromYear2) . '-' . (strlen(trim($FromMes2)) == 1 ? '0' : '') . trim($FromMes2) . '-' . (strlen(trim($FromDia2)) == 1 ? '0' : '') . trim($FromDia2);
$fechaSeleccionada2 = (strlen(trim($FromDia2)) == 1 ? '0' : '') . trim($FromDia2) . '/' . (strlen(trim($FromMes2)) == 1 ? '0' : '') . trim($FromMes2) . '/' . trim($FromYear2);

$NewAdjustment = false;
if(isset($_POST['CheckCode'])){
     if ($_POST['StockID'] != $_SESSION['Adjustment']->StockID){
		$NewAdjustment = true;
		$_SESSION['Adjustment']->StockID = trim(strtoupper($_POST['StockID']));
		$_SESSION['Adjustment']->StockLocation=$_POST['StockLocation'];
     }
     if ($_POST['StockLocation'] != $_SESSION['Adjustment']->StockLocation){
		$_SESSION['Adjustment']->StockLocation=$_POST['StockLocation'];
     }
}
if ($_POST['StockID'] != $_SESSION['Adjustment']->StockID){
		$NewAdjustment = true;
		$_SESSION['Adjustment']->StockID = trim(strtoupper($_POST['StockID']));
		$_SESSION['Adjustment']->StockLocation=$_POST['StockLocation'];
     }
     
if (isset($_POST['OrdernoAjuste']))
{
  $OrdernoAjuste = $_POST['OrdernoAjuste'];
}elseif(isset($_GET['OrdernoAjuste']))
{
  $OrdernoAjuste = $_GET['OrdernoAjuste'];
  $SQL="select inventoryadjustmentorders.*,
	      day(inventoryadjustmentorders.origtrandate) as dia,
	      month(inventoryadjustmentorders.origtrandate) as mes,
	      year(inventoryadjustmentorders.origtrandate) as anio,quotation
	from inventoryadjustmentorders where orderno=".$OrdernoAjuste;
  //echo $SQL;
  $ErrMsg = _('La orden de nota no se encontro');
  $DbgMsg=_('El SQL utilizado es: ');
  $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
  $myrowOrden = DB_fetch_array($Result);
  //$myrowtype = DB_fetch_array($result_typeclient);
  $NewAdjustment = true;
  $OrdernoAjuste =$myrowOrden['orderno'] ;
  $_SESSION['Adjustment']->StockID=$myrowOrden['stockid'] ;
  $_SESSION['Adjustment']->Narrative = $myrowOrden['narrative'];
  $_SESSION['Adjustment']->StockLocation = $myrowOrden['loccode'];
  $_SESSION['Adjustment']->Quantity = $myrowOrden['qty'];
  
  $StockLocation=$myrowOrden['loccode'] ;
  $Narrative=$myrowOrden['narrative'];
  $Quantity=$myrowOrden['qty']; 
  $FromYear=$myrowOrden['anio'];
  $FromMes=$myrowOrden['mes'];
  $FromDia=$myrowOrden['dia'];
  $quotation=$myrowOrden['quotation'];
}else{
  $OrdernoAjuste =0;
  $quotation=0;
}

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
	$_SESSION['Adjustment']->StockID = trim(strtoupper($_GET['StockID']));
	
	$NewAdjustment = true;
}

if ($NewAdjustment){

	$sql ="SELECT description,
				units,
				mbflag,
				materialcost+labourcost+overheadcost as standardcost,
				controlled,
				serialised,
				decimalplaces
			FROM stockmaster, stockcategory sto, sec_stockcategory sec 
			WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'
			 AND sto.categoryid=sec.categoryid
			 AND sto.categoryid=stockmaster.categoryid
			 AND userid='".$_SESSION['UserID']."'";
			 
			 
			 
		//echo $sql;	 
	$ErrMsg = _('El código no existe'). ':' . $_SESSION['Adjustment']->StockID;
	$result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_row($result);

	if (DB_num_rows($result)==0){
                prnMsg( _('El código no existe').' '.$_SESSION['Adjustment']->StockID, 'error' );
				unset($_SESSION['Adjustment']);
	} elseif (DB_num_rows($result)>0){

		$_SESSION['Adjustment']->ItemDescription = $myrow[0];
		$_SESSION['Adjustment']->PartUnit = $myrow[1];
		$_SESSION['Adjustment']->StandardCost = $myrow[3];
		$_SESSION['Adjustment']->Controlled = $myrow[4];
		$_SESSION['Adjustment']->Serialised = $myrow[5];
		$_SESSION['Adjustment']->DecimalPlaces = $myrow[6];
		$_SESSION['Adjustment']->SerialItems = array();

		if ($myrow[2]=='D' OR $myrow[2]=='A' OR $myrow[2]=='K'){
			prnMsg( _('Las propiedades de este producto no permiten este tipo de operacion'),'error');
			echo '<hr>';
			echo '<a href="'. $rootpath .'/StockAdjustments.php?' . SID .'">'. _('Ingrese otro tipo de ajuste'). '</a>';
			unset ($_SESSION['Adjustment']);
			include ('includes/footer_Index.inc');
			exit;
		}
	}
}
$InputError=false;
// insertar en ordenes de ajuste de inventario
if ($OrdernoAjuste==0 and isset($_POST['EnterAdjustment']) && $_POST['EnterAdjustment']!=''){
     $quotation=1;
     $url='StockAdjustments.php';
     $result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		prnMsg( _('El código ingresado no existe'),'error');
		$InputError = true;
	}elseif (!is_numeric($_POST['Quantity'])){
		prnMsg( _('La cantidad debe ser un numero'),'error');
		$InputError = true;
	} elseif ($_POST['Quantity']==0){
		prnMsg( _('La cantidad a ajustar no debe ser cero') . '. ' . _('por lo tanto no se realizara la operacion'),'error');
		$InputError = true;
	}
	
     if($InputError==false){
	  
	  $SQL="insert into inventoryadjustmentorders(
		stockid,
		loccode,
		origtrandate,
		narrative,
		qty,
		quotation,
		userregister,
		url,
		type
		
		)
		values('".$_SESSION['Adjustment']->StockID ."',
		'".$_POST['StockLocation']."',
		now(),
		'".$_POST['Narrative']."',
		
		'".$_POST['Quantity']."',
		'".$quotation."',
		'".$_SESSION['UserID']."',
		'".$url."',
		17
		)";
	 // echo $SQL.'<br>';
	$ErrMsg = _('La insercion de la orden de NC no se realizo');
	$DbgMsg=_('El SQL utilizado es: ');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	$OrdernoAjuste=DB_Last_Insert_ID($db,'inventoryadjustmentorders','orderno');
     }
     
}
if($_POST['EnterAdjustment']==_('Solicitar')  and $InputError==false){
     prnMsg( _('La solicitud de ajuste de inventario para el producto '). ' ' . $_SESSION['Adjustment']->StockID . ' -  ' . $_SESSION['Adjustment']->ItemDescription . ' '._('se realizo con exito').' ' . $_SESSION['Adjustment']->StockLocation .
		 ' '. _('por la cantidad de ') . ' ' . $_POST['Quantity'],'success');
     $SQL="UPDATE inventoryadjustmentorders
	      SET loccode='".$_POST['StockLocation']."',
		  narrative='".$_POST['Narrative']."',
		  qty='".$_POST['Quantity']."',
		  userregister='".$_SESSION['UserID']."'
		WHERE orderno='".$OrdernoAjuste."'";
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
     echo '<table>';
	  echo '<tr><td text-align:center;><a href="'. $rootpath. '/StockAdjustments.php?' . SID . '">'._('Regresar Ajuste de Existencias').'</a></td></tr>';
	  echo '<tr><td text-align:center;><a href="'. $rootpath. '/MantenimientoInventoryAdjustment.php?' . SID . '">'._('Ir a Mantenimiento de Ajuste de Existencias').'</a></td></tr>';
	  $liga="PDFInventoryAdjustment.php";
	  $liga = $rootpath . "/" . $liga . "?OrdernoAjuste=".$OrdernoAjuste;
	  $reimprimir = "<a TARGET='_blank' href='" . $liga . "'><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir') . "' alt=''>Imprimir</a>";
	  echo '<tr><td text-align:right;>'.$reimprimir.'</td></tr>';
     echo '</table>';
     
     include('includes/footer_Index.inc');
     unset ($_SESSION['Adjustment']);
     exit;
     
     
}
// actualizar el status de la orden de ajuste de inventario

if($_POST['EnterAdjustment']==_('Autorizar')and $InputError==false){
      $quotation=2;
     $SQL="UPDATE inventoryadjustmentorders
	      SET loccode='".$_POST['StockLocation']."',
		  narrative='".$_POST['Narrative']."',
		  qty='".$_POST['Quantity']."',
		  quotation='".$quotation."',
	          userauthorized='".$_SESSION['UserID']."'
	     WHERE orderno='".$OrdernoAjuste."'";
	//echo $SQL;
	$ErrMsg = _('La insercion de la orden de NC no se realizo');
	$DbgMsg=_('El SQL utilizado es: ');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	
     prnMsg( _('La autorizacion de ajuste de inventario para el producto '). ' ' . $_SESSION['Adjustment']->StockID . ' -  ' . $_SESSION['Adjustment']->ItemDescription . ' '._('se realizo con exito').' ' . $_SESSION['Adjustment']->StockLocation .
	    ' '. _('por la cantidad de ') . ' ' . $_SESSION['Adjustment']->Quantity,'success');
     echo '<table>';
	  //echo '<tr><td text-align:center;><a href="'. $rootpath. '/StockAdjustments.php?' . SID . '">'._('Regresar Ajuste de Existencias').'</a></td></tr>';
	  echo '<tr><td text-align:center;><a href="'. $rootpath. '/MantenimientoInventoryAdjustment.php?' . SID . '">'._('Ir a Mantenimiento de Ajuste de Existencias').'</a></td></tr>';
	  $liga="PDFInventoryAdjustment.php";
	  $liga = $rootpath . "/" . $liga . "?OrdernoAjuste=".$OrdernoAjuste;
	  $reimprimir = "<a TARGET='_blank' href='" . $liga . "'><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir') . "' alt=''>Imprimir</a>";
	  echo '<tr><td text-align:right;>'.$reimprimir.'</td></tr>';
     echo '</table>';
     include('includes/footer_Index.inc');
     unset ($_SESSION['Adjustment']);
     exit;
}

if (isset($_POST['EnterAdjustment']) && $_POST['EnterAdjustment']==_('Procesar') and $InputError==false){
     

	$InputError = false; /*Start by hoping for the best */
        $_SESSION['Adjustment']->Quantity=$_POST['Quantity'];
	$result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		prnMsg( _('El código ingresado no existe'),'error');
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Adjustment']->Quantity)){
		prnMsg( _('La cantidad debe ser un numero'),'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment']->Quantity==0){
		//prnMsg( _('The quantity entered cannot be zero') . '. ' . _('There would be no adjustment to make'),'error');
		prnMsg( _('La cantidad a ajustar no debe ser cero') . '. ' . _('por lo tanto no se realizara la operacion'),'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment']->Controlled==1 AND count($_SESSION['Adjustment']->SerialItems)==0) {
		prnMsg( _('El producto es serializado por ello debe dar de alta las series que desea ajustar'),'error');
		$InputError = true;
	}
	$_SESSION['Adjustment']->StockLocation=$_POST['StockLocation'];
//echo  $_SESSION['Adjustment']->StockID ;
//exit;
	if ($_SESSION['ProhibitNegativeStock']==1){
		$SQL = "SELECT quantity FROM locstock
				WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'
				AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'";
		$CheckNegResult=DB_query($SQL,$db);
		$CheckNegRow = DB_fetch_array($CheckNegResult);
		if ($CheckNegRow['quantity']+$_SESSION['Adjustment']->Quantity <0){
			$InputError=true;
			prnMsg(_('Los parametros del sistema no permiten productos en negativo.Este ajuste no se realizara.'),'error');
		}
	}

	if (!$InputError) {
	       $Result = DB_Txn_Begin($db);
	  
	       $quotation=4;
	       $SQL="UPDATE inventoryadjustmentorders
			SET loccode='".$_POST['StockLocation']."',
			    narrative='".$_POST['Narrative']."',
			    qty='".$_POST['Quantity']."',
			    quotation='".$quotation."',
			    trandate=now(),
			    userprocess='".$_SESSION['UserID']."'
		       WHERE orderno='".$OrdernoAjuste."'";
	       //echo $SQL;
	       $ErrMsg = _('La insercion de la orden de NC no se realizo');
	       $DbgMsg=_('El SQL utilizado es: ');
	       $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$AdjustmentNumber = GetNextTransNo(17,$db);
		//$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
		// $SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
		$PeriodNo = GetPeriod ($fechaSeleccionada2, $db);
		$SQLAdjustmentDate = $fechaSeleccionada;
		

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_SESSION['Adjustment']->StockID . "'
			AND loccode= '" . $_SESSION['Adjustment']->StockLocation . "'";
		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}
		
		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locations.tagref
			FROM locations
			WHERE locations.loccode= '" . $_SESSION['Adjustment']->StockLocation . "'";
		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$LocTagRef = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$LocTagRef = 0;
		}
		
	       $legalid=ExtractLegalid($LocTagRef,$db);
	       $EstimatedAvgCostXlegal=ExtractAvgCostXlegal($legalid,$_SESSION['Adjustment']->StockID, $db);

		$SQL = "INSERT INTO stockmoves (
				stockid,
				type,
				transno,
				loccode,
				trandate,
				prd,
				narrative,
				reference,
				qty,
				newqoh,
				tagref,
				standardcost)
			VALUES (
				'" . $_SESSION['Adjustment']->StockID . "',
				17,
				" . $AdjustmentNumber . ",
				'" . $_SESSION['Adjustment']->StockLocation . "',
				'" . $SQLAdjustmentDate . "',
				" . $PeriodNo . ",
				'" . $_POST['Narrative']." Usuario:".$_SESSION['UserID']."',
				'" . $OrdernoAjuste ."',
				" . $_SESSION['Adjustment']->Quantity . ",
				" . ($QtyOnHandPrior + $_SESSION['Adjustment']->Quantity) . ",
				" . $LocTagRef .",
				" . $EstimatedAvgCostXlegal ."
			)";


		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg =  _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Adjustment']->Controlled ==1){
			foreach($_SESSION['Adjustment']->SerialItems as $Item){
			/*We need to add or update the StockSerialItem record and
			The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not */
				$SQL = "SELECT COUNT(*)
					FROM stockserialitems
					WHERE
					stockid='" . $_SESSION['Adjustment']->StockID . "'
					AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'
					AND serialno='" . $Item->BundleRef . "'";
				$ErrMsg = _('Unable to determine if the serial item exists');
				$Result = DB_query($SQL,$db,$ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0]==1){

					$SQL = "UPDATE stockserialitems SET
						quantity= quantity + " . $Item->BundleQty . "
						WHERE
						stockid='" . $_SESSION['Adjustment']->StockID . "'
						AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'
						AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg =  _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
									loccode,
									serialno,
								        standardcost,
									qualitytext,
									quantity)
						VALUES ('" . $_SESSION['Adjustment']->StockID . "',
						'" . $_SESSION['Adjustment']->StockLocation . "',
						'" . $Item->BundleRef . "',
						 '" .  $Item->CostSerialItem  . "',
						'',
						" . $Item->BundleQty . ")";

					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg =  _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}


				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno,
									stockid,
									serialno,
									moveqty,
									standardcost)
						VALUES ( " . $StkMoveNo . ",
							'" . $_SESSION['Adjustment']->StockID . "',
							'" . $Item->BundleRef . "',
							 " . $Item->BundleQty . ",
							 " .  $Item->CostSerialItem  .")";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			      $costoserie=$costoserie+$Item->CostSerialItem;
			}/* foreach controlled item in the serialitems array */
			 $SQL = "UPDATE stockmoves
			         SET standardcost= " . $costoserie . "
			        WHERE stkmoveno='" . $StkMoveNo . "'";
			 $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			 $EstimatedAvgCostXlegal=$costoserie/$_SESSION['Adjustment']->Quantity;
		} /*end if the adjustment item is a controlled item */



		$SQL = "UPDATE locstock SET quantity = quantity + " . $_SESSION['Adjustment']->Quantity . "
				WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'
				AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the stock record was used');

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $EstimatedAvgCostXlegal > 0){

			$StockGLCodes = GetStockGLCode($_SESSION['Adjustment']->StockID,$db);

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							amount,
							narrative,
							tag)
					VALUES (17,
						" .$AdjustmentNumber . ",
						'" . $SQLAdjustmentDate . "',
						" . $PeriodNo . ",
						" .  $StockGLCodes['adjglact'] . ",
						" . $EstimatedAvgCostXlegal * -($_SESSION['Adjustment']->Quantity) . ",
						'" . $_SESSION['Adjustment']->StockID . " x " . $_SESSION['Adjustment']->Quantity . " @ " . $EstimatedAvgCostXlegal . " " . $_SESSION['Adjustment']->Narrative . "',
						" . $LocTagRef .")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);


			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							amount,
							narrative,
							tag)
					VALUES (17,
						" .$AdjustmentNumber . ",
						'" . $SQLAdjustmentDate . "',
						" . $PeriodNo . ",
						" .  $StockGLCodes['stockact'] . ",
						" . $EstimatedAvgCostXlegal * $_SESSION['Adjustment']->Quantity . ",
						'" . $_SESSION['Adjustment']->StockID . " x " . $_SESSION['Adjustment']->Quantity . " @ " . $EstimatedAvgCostXlegal . " " . $_SESSION['Adjustment']->Narrative . "',
						" . $LocTagRef .")";

			$Errmsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
		}

		$Result = DB_Txn_Commit($db);

		prnMsg( _('Se realizo con exito el ajuste para '). ' ' . $_SESSION['Adjustment']->StockID . ' -  ' . $_SESSION['Adjustment']->ItemDescription . ' '._('en el almacén').' ' . $_SESSION['Adjustment']->StockLocation .' '. _('por la cantidad de ') . ' ' . $_SESSION['Adjustment']->Quantity,'success');
	       echo '<table>';
	       	    echo '<tr><td text-align:center;><a href="'. $rootpath. '/StockAdjustments.php?' . SID . '&StockID='. $_SESSION['Adjustment']->StockID . '">'._('Regresar Ajuste de Existencias').'</a></td></tr>';
		    echo '<tr><td text-align:center;><a href="'. $rootpath. '/MantenimientoInventoryAdjustment.php?' . SID . '">'._('Ir a Mantenimiento de Ajuste de Existencias').'</a></td></tr>';
		    $liga="PDFInventoryAdjustment.php";
		    $liga = $rootpath . "/" . $liga . "?OrdernoAjuste=".$OrdernoAjuste;
		    $reimprimir = "<a TARGET='_blank' href='" . $liga . "'><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir') . "' alt=''>Imprimir</a>";
		    echo '<tr><td text-align:right;>'.$reimprimir.'</td></tr>';
	       echo '</table>';
				
		unset ($_SESSION['Adjustment']);
		include('includes/footer_Index.inc');
		exit;
	} /* end if there was no input error */

}/* end if the user hit enter the adjustment */

echo '<form action="'. $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

if (!isset($_SESSION['Adjustment'])) {
	$StockID='';
	$Controlled= 0;
	$Quantity = 0;
} else {
	$StockID = $_SESSION['Adjustment']->StockID;
	$Controlled = $_SESSION['Adjustment']->Controlled;
	$Quantity = $_SESSION['Adjustment']->Quantity;
}
echo '<div class="centre mb10">';
     echo '<br><b>'._('Ajustes de Inventario').'</b><br>';
echo '</div>';


echo '<table><tr><td class="pb50"><span class="generalSpan">'. _('Código de Producto'). ':</span></td><td class="pr5"><input class="form-control mb10" type=text name="StockID" size=21 value="' . $StockID . '" maxlength=20> <input class="botonVerde mb10 mr5 form-control" type=submit name="CheckCode" VALUE="'._('Verifica Código de Producto').'"></td></tr>';

if(Havepermission($_SESSION['UserID'], 219, $db) == 1) {
	echo '<tr>
			<td>Fecha:</td>
			<td>
				<table align="left">
				<tr>
					<td><select Name="FromDia">';
					 $sql = "SELECT * FROM cat_Days";
					 $dias = DB_query($sql,$db);
					 while ($myrowdia=DB_fetch_array($dias,$db)){
						 $diabase=$myrowdia['DiaId'];
						 if (rtrim(intval($FromDia2))==rtrim(intval($diabase))){ 
						 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
						 }else{
						 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
						 }
					 }
					echo'</td>'; 
					echo '<td><select Name="FromMes">';
					$sql = "SELECT * FROM cat_Months";
					$Meses = DB_query($sql,$db);
					while ($myrowMes=DB_fetch_array($Meses,$db)){
						$Mesbase=$myrowMes['u_mes'];
						if (rtrim(intval($FromMes2))==rtrim(intval($Mesbase))){ 
							echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
						}else{
							echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
						}
					}
					  
					echo '</select>';
					echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear2.'>';
						  
					echo '</td>
				</tr>
			</table>
		</td>
	</tr>';
}

if (isset($_SESSION['Adjustment']) and strlen($_SESSION['Adjustment']->ItemDescription)>1){
	echo '<tr><td colspan=3><font color=BLUE size=3>' . $_SESSION['Adjustment']->ItemDescription . ' ('._('In Units of').' ' . $_SESSION['Adjustment']->PartUnit . ' ) - ' . _('Unit Cost').' = ' . $_SESSION['Adjustment']->StandardCost . '</font></td></tr>';
}
//echo 'ssss'.$_SESSION['Adjustment']->StockLocation;
echo '<tr><td><span class="generalSpan mb10">'. _('Ajustes de Inventario en Almacén').':</span></td><td><select class="mb10 form-control" name="StockLocation"> ';

#$sql = 'SELECT loccode, locationname FROM locations';

$sql = 'SELECT l.loccode, locationname FROM locations l, sec_loccxusser lxu where l.loccode=lxu.loccode and userid="'.$_SESSION['UserID'].'"';

$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_SESSION['Adjustment']->StockLocation)){
		if ($myrow['loccode'] == $_SESSION['Adjustment']->StockLocation){
		     echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
		     echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
}

echo '</select></td></tr>';
if (!isset($_SESSION['Adjustment']->Narrative)) {
	$_SESSION['Adjustment']->Narrative = '';
}

echo '<tr><td><span class="generalSpan">'. _('Comentarios de la Dependencia').':</span></td><td>';

//echo '<input type=text name="Narrative" size=32 maxlength=30 value="' . $_SESSION['Adjustment']->Narrative . '">'
echo "<textarea class='form-control mb10' name='Narrative' cols='40' rows='3'>" . $_SESSION['Adjustment']->Narrative . "</textarea>";

echo '</td></tr>';

echo '<tr><td><span class="generalSpan mb10">'._('Cantidad a Ajustar').':</span></td>';

echo '<td>';
if ($Controlled==1){
		if ($_SESSION['Adjustment']->StockLocation != ''){
			echo '<input type="HIDDEN" name="Quantity" Value="' . $_SESSION['Adjustment']->Quantity . '">
				'.$_SESSION['Adjustment']->Quantity.' &nbsp; &nbsp; &nbsp; &nbsp;
				[<a href="'.$rootpath.'/StockAdjustmentsControlled.php?AdjType=REMOVE&' . SID . '">'._('Remove').'</a>]
				[<a href="'.$rootpath.'/StockAdjustmentsControlled.php?AdjType=ADD&' . SID . '">'._('Add').'</a>]';
		} else {
			prnMsg( _('Please select a location and press') . ' "' . _('Enter Stock Adjustment') . '" ' . _('below to enter Controlled Items'), 'info');
		}
} else {
	echo '<input type=TEXT class="number form-control mb10" name="Quantity" size=12 maxlength=12 Value="' . $Quantity . '">';
}
echo '</td></tr>';

echo '</table>';
echo '<div class="centre">';
 if (Havepermission($_SESSION['UserID'],423, $db)==1){
     echo '<input type=submit name="EnterAdjustment" VALUE="'. _('Solicitar'). '">';
 }
 if (Havepermission($_SESSION['UserID'],424, $db)==1){
     echo '<input type=submit name="EnterAdjustment" VALUE="'. _('Autorizar'). '">';
 }
 if (Havepermission($_SESSION['UserID'],426, $db)==1 and $quotation==2){
     echo '<input type=submit name="EnterAdjustment" VALUE="'. _('Procesar'). '">';
 }

echo '</div>';
 echo '<input type="hidden" name="OrdernoAjuste" value="'.$OrdernoAjuste.'">';
echo '<hr><br>';

if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation']='';
}

echo '<a href="'. $rootpath. '/StockStatus.php?' . SID . '&StockID='. $StockID . '">'._('Mostrar estado de Existencias').'</a>';
//echo '<br><a href="'.$rootpath.'/StockMovements.php?' . SID . '&StockID=' . $StockID . '">'._('Mostrar Movimientos').'</a>';
echo '<br><a href="'.$rootpath.'/StockMovements.php?' . SID . '&StockID=' . $StockID . '">'._('Movimientos del Producto').'</a>';
echo '<br><a href="'.$rootpath.'/StockUsage.php?' . SID . '&StockID=' . $StockID . '&StockLocation=' . $_POST['StockLocation'] . '">'._('Mostrar Uso del Producto').'</a>';
echo '<br><a href="'.$rootpath.'/SelectSalesOrder.php?' . SID . '&SelectedStockItem='. $StockID .'&StockLocation=' . $_POST['StockLocation'] . '">'. _('Buscar Ordenes de Venta Pendientes').'</a>';
echo '<br><a href="'.$rootpath.'/SelectCompletedOrder.php?' . SID . '&SelectedStockItem=' . $StockID .'">'._('Buscar Ordenes de Ventas Completadas').'</a>';

echo '</div></form>';
include('includes/footer_Index.inc');
?>