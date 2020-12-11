<?php
include('includes/DefineStockAdjustment.php');
include('includes/DefineSerialItems.php');

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Ajustes de Inventario');

include('includes/header.inc');
$funcion = 48;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');


$permisocambiarfecha = Havepermission($_SESSION['UserID'], 1622, $db);
$permisovermotivodeajuste = Havepermission($_SESSION ['UserID'], 1656, $db) ;
if (isset($_POST['FromYear'])) {
    $FromYear2=$_POST['FromYear'];
} elseif (isset($_GET['FromYear'])) {
    $FromYear2=$_GET['FromYear'];
} else {
    $FromYear2=date('Y');
}

if (isset($_POST['FromMes'])) {
    $FromMes2=$_POST['FromMes'];
} elseif (isset($_GET['FromMes'])) {
    $FromMes2=$_GET['FromMes'];
} else {
    $FromMes2=date('m');
}

if (isset($_GET['FromDia'])) {
    $FromDia2=$_GET['FromDia'];
} elseif (isset($_POST['FromDia'])) {
    $FromDia2=$_POST['FromDia'];
} else {
    $FromDia2=date('d');
}

$fechaSeleccionada = trim($FromYear2) . '-' . (strlen(trim($FromMes2)) == 1 ? '0' : '') . trim($FromMes2) . '-' . (strlen(trim($FromDia2)) == 1 ? '0' : '') . trim($FromDia2);
$fechaSeleccionada2 = (strlen(trim($FromDia2)) == 1 ? '0' : '') . trim($FromDia2) . '/' . (strlen(trim($FromMes2)) == 1 ? '0' : '') . trim($FromMes2) . '/' . trim($FromYear2);

//echo 'POST: '.var_dump($_POST).'<br><br>';
//echo '<br>Variable Adjustment:  '.var_dump($_SESSION['Adjustment']);echo '<br>';
$NewAdjustment = false;
if (isset($_POST['CheckCode'])) {
    if (isset($_POST['StockID']) and $_POST['StockID'] != $_SESSION['Adjustment']->StockID) {
        $NewAdjustment = true;
        $_SESSION['Adjustment']->StockID = trim(strtoupper($_POST['StockID']));
        $_SESSION['Adjustment']->StockLocation = $_POST['StockLocation'];
    }
    if ($_POST['StockLocation'] != $_SESSION['Adjustment']->StockLocation) {
        $_SESSION['Adjustment']->StockLocation = $_POST['StockLocation'];
    }
}
if (isset($_POST['StockID']) and isset($_SESSION['Adjustment']->StockID) and $_POST['StockID'] != $_SESSION['Adjustment']->StockID) {
    $NewAdjustment = true;
    
    //$_SESSION['Adjustment']->StockID = trim(strtoupper($_POST['StockID']));
    //$_SESSION['Adjustment']->StockLocation=$_POST['StockLocation'];
}

if (empty($_SESSION['Adjustment']->StockID)) {
    $_SESSION['Adjustment']->StockID= $_POST['StockID'];
}

if (isset($_POST['OrdernoAjuste'])) {
    $OrdernoAjuste = $_POST['OrdernoAjuste'];
} elseif (isset($_GET['OrdernoAjuste'])) {
    $OrdernoAjuste = $_GET['OrdernoAjuste'];
    $SQL = "SELECT inventoryadjustmentorders.*,
			       day(inventoryadjustmentorders.origtrandate) AS dia,
			       month(inventoryadjustmentorders.origtrandate) AS mes,
			       year(inventoryadjustmentorders.origtrandate) AS anio,
			       quotation,
    		reasonid
			FROM inventoryadjustmentorders
			WHERE orderno=" . $OrdernoAjuste;
    
    //echo $SQL;
    $ErrMsg = _('La orden de nota no se encontro');
    $DbgMsg = _('El SQL utilizado es: ');
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    $myrowOrden = DB_fetch_array($Result);
    
    //$myrowtype = DB_fetch_array($result_typeclient);
    $NewAdjustment = true;
    $OrdernoAjuste = $myrowOrden['orderno'];
    $_SESSION['Adjustment']->StockID = $myrowOrden['stockid'];
    $_SESSION['Adjustment']->Narrative = $myrowOrden['narrative'];
    $_SESSION['Adjustment']->StockLocation = $myrowOrden['loccode'];
    $_SESSION['Adjustment']->Quantity = $myrowOrden['qty'];
    
    $StockLocation = $myrowOrden['loccode'];
    $Narrative = $myrowOrden['narrative'];
    $Quantity = $myrowOrden['qty'];
    $FromYear = $myrowOrden['anio'];
    $FromMes = $myrowOrden['mes'];
    $FromDia = $myrowOrden['dia'];
    $quotation = $myrowOrden['quotation'];
    $_POST['reasonid'] = $myrowOrden['reasonid'];
} else {
    $OrdernoAjuste = 0;
    $quotation = 0;
}

if (isset($_GET['StockID'])) {
    $StockID = trim(strtoupper($_GET['StockID']));
    $_SESSION['Adjustment']->StockID = trim(strtoupper($_GET['StockID']));
    $NewAdjustment = true;
}

if ($NewAdjustment) {
    $sql = "SELECT description,
				units,
				mbflag,
				materialcost+labourcost+overheadcost as standardcost,
				controlled,
				serialised,
				decimalplaces
			FROM stockmaster, stockcategory sto
			WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'			 
			 AND sto.categoryid=stockmaster.categoryid";
    
    $ErrMsg = _('Unable to load StockMaster info for part') . ':' . $_SESSION['Adjustment']->StockID;
    $result = DB_query($sql, $db, $ErrMsg);
    $myrow = DB_fetch_row($result);
    
    if (DB_num_rows($result) == 0) {
        prnMsg(_('Unable to locate Stock Code') . ' ' . $_SESSION['Adjustment']->StockID, 'error');
        unset($_SESSION['Adjustment']);
    } elseif (DB_num_rows($result) > 0) {
        $_SESSION['Adjustment']->ItemDescription = $myrow[0];
        $_SESSION['Adjustment']->PartUnit = $myrow[1];
        $_SESSION['Adjustment']->StandardCost = $myrow[3];
        $_SESSION['Adjustment']->Controlled = $myrow[4];
        $_SESSION['Adjustment']->Serialised = $myrow[5];
        $_SESSION['Adjustment']->DecimalPlaces = $myrow[6];
        
        //$_SESSION['Adjustment']->SerialItems = array();
        //prnMsg( _('Las propiedades de este producto permiten este tipo de operacion'),'warn');
        echo '<hr>';
        if ($myrow[2] == 'D' or $myrow[2] == 'A' or $myrow[2] == 'K') {
            prnMsg(_('Las propiedades de este producto no permiten este tipo de operacion'), 'error');
            echo '<hr>';
            echo '<a href="' . $rootpath . '/StockAdjustments.php?' . SID . '">' . _('Ingrese otro tipo de ajuste') . '</a>';
            unset($_SESSION['Adjustment']);
            include('includes/footer.inc');
            exit;
        }
    }
}

$InputError = false;

// insertar en ordenes de ajuste de inventario
if ($OrdernoAjuste == 0 and isset($_POST['EnterAdjustment']) && $_POST['EnterAdjustment'] != '') {
    $quotation = 1;
    $url = 'StockAdjustments_Magic.php';
    $result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'", $db);
    $myrow = DB_fetch_row($result);

    if (DB_num_rows($result) == 0) {
        prnMsg(_('El codigo ingresado no existe'), 'error');
        $InputError = true;
    } elseif (!is_numeric($_POST['Quantity'])) {
        prnMsg(_('La cantidad debe ser un numero'), 'error');
        $InputError = true;
    } elseif ($_POST['Quantity'] == 0) {
        prnMsg(_('La cantidad a ajustar no debe ser cero') . '. ' . _('por lo tanto no se realizara la operacion'), 'error');
        $InputError = true;
    }
    
    if ($InputError == false) {
        if ($_SESSION['DatabaseName'] == "gruposervillantas" or $_SESSION['DatabaseName'] == "gruposervillantas_CAPA" or $_SESSION['DatabaseName'] == "gruposervillantas_DES") {
            $tipoajuste = 42;
        } else {
            $tipoajuste = 41;
        }
        $SQL = "INSERT INTO inventoryadjustmentorders( stockid, loccode, location, origtrandate, narrative, qty, quotation, userregister, url, TYPE,reasonid )
				VALUES('" . $_SESSION['Adjustment']->StockID . "',
                 	   '" . $_POST['StockLocation'] . "',
                       '" . $_SESSION['Adjustment']->StockSectorLocation ."',
                       '" . $fechaSeleccionada . "',
                       '" . $_POST['Narrative'] . "',
                       '" . $_POST['Quantity'] . "',
                       '" . $quotation . "',
                  	   '" . $_SESSION['UserID'] . "',
                       '" . $url . "',
                       '" . $tipoajuste . "',
                       '".$_POST['motivo']."' )";
        $ErrMsg = _('La insercion de la orden de NC no se realizo');
        $DbgMsg = _('El SQL utilizado es: ');
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
        $OrdernoAjuste = DB_Last_Insert_ID($db, 'inventoryadjustmentorders', 'orderno');
    }
}

if (isset($_POST['EnterAdjustment']) and $_POST['EnterAdjustment'] == _('Solicitar') and $InputError == false) {
    prnMsg(_('La solicitud de ajuste de inventario para el producto ') . ' ' . $_SESSION['Adjustment']->StockID . ' -  ' . $_SESSION['Adjustment']->ItemDescription . ' ' . _('se realizo con exito') . ' ' . $_SESSION['Adjustment']->StockLocation . ' ' . _('por la cantidad de ') . ' ' . $_POST['Quantity'], 'success');
    include('includes/footer.inc');
    unset($_SESSION['Adjustment']);
    exit;
}

// actualizar el status de la orden de ajuste de inventario
if (isset($_POST['EnterAdjustment']) and $_POST['EnterAdjustment'] == _('Autorizar') and $InputError == false) {
    $quotation = 2;
    $SQL = "UPDATE inventoryadjustmentorders
			SET loccode='" . $_POST['StockLocation'] . "',
                location='" . $_SESSION['Adjustment']->StockSectorLocation . "',
			    narrative='" . $_POST['Narrative'] . "',
			    qty='" . $_POST['Quantity'] . "',
			    quotation='" . $quotation . "',
			    userauthorized='" . $_SESSION['UserID'] . "',
			    reasonid ='" . $_POST['motivo'] . "'
			WHERE orderno='" . $OrdernoAjuste . "'";
    
    //echo $SQL;
    $ErrMsg = _('La insercion de la orden de NC no se realizo');
    $DbgMsg = _('El SQL utilizado es: ');
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    
    prnMsg(_('La autorizacion de ajuste de inventario para el producto ') . ' ' . $_SESSION['Adjustment']->StockID . ' -  ' . $_SESSION['Adjustment']->ItemDescription . ' ' . _('se realizo con exito') . ' ' . $_SESSION['Adjustment']->StockLocation . ' ' . _('por la cantidad de ') . ' ' . $_SESSION['Adjustment']->Quantity, 'success');
    include('includes/footer.inc');
    unset($_SESSION['Adjustment']);
    exit;
}

if (isset($_POST['EnterAdjustment']) && $_POST['EnterAdjustment'] == _('Procesar') and $InputError == false) {
    $InputError = false;
     /*Start by hoping for the best */
    $_SESSION['Adjustment']->Quantity = $_POST['Quantity'];
    
    $result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'", $db);
    $myrow = DB_fetch_row($result);
    if (DB_num_rows($result) == 0) {
        prnMsg(_('El codigo ingresado no existe'), 'error');
        $InputError = true;
    } elseif (!is_numeric($_SESSION['Adjustment']->Quantity)) {
        prnMsg(_('La cantidad debe ser un numero'), 'error');
        $InputError = true;
    } elseif ($_SESSION['Adjustment']->Quantity == 0) {
        //prnMsg( _('The quantity entered cannot be zero') . '. ' . _('There would be no adjustment to make'),'error');
        prnMsg(_('La cantidad a ajustar no debe ser cero') . '. ' . _('por lo tanto no se realizara la operacion'), 'error');
        $InputError = true;
    } elseif ($_SESSION['Adjustment']->Controlled == 1 and count($_SESSION['Adjustment']->SerialItems) == 0) {
        prnMsg(_('El producto es serializado por ello debe dar de alta las series que desea ajustar'), 'error');
        echo '<br>Serializado: ' . $_SESSION['Adjustment']->Controlled;
        echo '<br>Cuenta de Serial Items: ' . count($_SESSION['Adjustment']->SerialItems);
        $InputError = true;
    }
    $_SESSION['Adjustment']->StockLocation = $_POST['StockLocation'];
    
    //echo  $_SESSION['Adjustment']->StockID ;
    //exit;
    if ($_SESSION['ProhibitNegativeStock'] == 1) {
        $SQL = "SELECT quantity FROM locstock
				WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'
				AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'";
        $CheckNegResult = DB_query($SQL, $db);
        $CheckNegRow = DB_fetch_array($CheckNegResult);

        if ($CheckNegRow['quantity'] + $_SESSION['Adjustment']->Quantity < 0) {
            $InputError = true;
            prnMsg(_('Los parametros del sistema no permiten productos en negativo.Este ajuste no se realizara.'), 'error');
        }
    }

    // if(isset($_POST['StockSectorLocation']) && $_POST['StockSectorLocation'] == '')  // comentado por el mana
    // {
    //      prnMsg(_('Seleccione una Localidad'), 'error');
    //        $InputError = true;
    // }
    
    if (!$InputError) {
        $Result = DB_Txn_Begin($db);
        
        $quotation = 4;
        $SQL = "UPDATE inventoryadjustmentorders
				SET loccode='" . $_POST['StockLocation'] . "',
                    location='" . $_SESSION['Adjustment']->StockSectorLocation . "',
				    narrative='" . $_POST['Narrative'] . "',
				    qty='" . $_POST['Quantity'] . "',
				    quotation='" . $quotation . "',
				    trandate='" . $fechaSeleccionada . "',
				    userprocess='" . $_SESSION['UserID'] . "',
				    		 reasonid ='" . $_POST['motivo'] . "'
			    WHERE orderno='" . $OrdernoAjuste . "'";
        
        //echo $SQL;
        $ErrMsg = _('La insercion de la orden de NC no se realizo');
        $DbgMsg = _('El SQL utilizado es: ');
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
        
        
        $tipoajuste = 41;
        $AdjustmentNumber = GetNextTransNo($tipoajuste, $db);

        // Se comenta para usar fecha de combo por permiso en otro caso se usa la
        // fecha en que se genera el movimiento
        // $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
        // $SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

        $PeriodNo = GetPeriod($fechaSeleccionada2, $db);
        $SQLAdjustmentDate = $fechaSeleccionada;
        
        // Need to get the current location quantity will need it later for the stock movement
        $SQL = "SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_SESSION['Adjustment']->StockID . "'
			AND loccode= '" . $_SESSION['Adjustment']->StockLocation . "'";
        $Result = DB_query($SQL, $db);
        if (DB_num_rows($Result) == 1) {
            $LocQtyRow = DB_fetch_row($Result);
            $QtyOnHandPrior = $LocQtyRow[0];
        } else {
            // There must actually be some error this should never happen
            $QtyOnHandPrior = 0;
        }
        
        // Need to get the current location quantity will need it later for the stock movement
        $SQL = "SELECT locations.tagref
			FROM locations
			WHERE locations.loccode= '" . $_SESSION['Adjustment']->StockLocation . "'";
        $Result = DB_query($SQL, $db);
        if (DB_num_rows($Result) == 1) {
            $LocQtyRow = DB_fetch_row($Result);
            $LocTagRef = $LocQtyRow[0];
        } else {
            // There must actually be some error this should never happen
            $LocTagRef = 0;
        }
        $sqli = "SELECT Concat(CODE,' - ',description)AS descripcion FROM adjustmentreasons WHERE id='".$_POST['motivo']."'";
        $resultmotivo = DB_query($sqli, $db);
        $rowi=DB_fetch_array($resultmotivo);
        $descripcion=$rowi['descripcion'];
        
        $legalid = ExtractLegalid($LocTagRef, $db);
        $EstimatedAvgCostXlegal = ExtractAvgCostXlegal($legalid, $_SESSION['Adjustment']->StockID, $db);
        if (DB_num_rows(DB_query("SHOW COLUMNS FROM stockmoves LIKE 'reasonid' ", $db)) == 1 and $permisovermotivodeajuste ==1) {//-->RECH Agregar campo a consulta si existe en la tabla   ALTER TABLE `stockmoves` ADD `reasonid` VARCHAR(150)  NULL  DEFAULT NULL  COMMENT 'Campo para guardar motivos de ajustes '  AFTER `register`;
             
                    $SQL = "INSERT INTO stockmoves (
				stockid,
				type,
				transno,
				loccode,
                localidad,
				trandate,
				prd,
				narrative,
				reference,
				qty,
				newqoh,
				tagref,
				standardcost,
        		reasonid,
        			register,useridmov)
			VALUES (
				'" . $_SESSION['Adjustment']->StockID . "',
				'" . $tipoajuste . "',
				'" . $AdjustmentNumber . "',
				'" . $_SESSION['Adjustment']->StockLocation . "',
                '" . $_POST['StockSectorLocation'] . "',
				'" . $SQLAdjustmentDate . "',
				'" . $PeriodNo . "',
				'" . $_POST['Narrative'] . " Usuario:" . $_SESSION['UserID'] . "',
				'" . $OrdernoAjuste ." Motivo: ". $descripcion. "',
				'" . $_SESSION['Adjustment']->Quantity . "',
				'" . ($QtyOnHandPrior + $_SESSION['Adjustment']->Quantity) . "',
				'" . $LocTagRef . "',
				'" . $EstimatedAvgCostXlegal . "',
			    '".$_POST['motivo']."',
			     NOW(), '".$_SESSION['UserID']."'
			)";
        } else {
               $SQL = "INSERT INTO stockmoves (
				stockid,
				type,
				transno,
				loccode,
                localidad,
				trandate,
				prd,
				narrative,
				reference,
				qty,
				newqoh,
				tagref,
				standardcost,
        		register, useridmov)
			VALUES (
				'" . $_SESSION['Adjustment']->StockID . "',
				'" . $tipoajuste . "',
				'" . $AdjustmentNumber . "',
				'" . $_SESSION['Adjustment']->StockLocation . "',
                '" . $_POST['StockSectorLocation'] . "',
				'" . $SQLAdjustmentDate . "',
				'" . $PeriodNo . "',
				'" . $_POST['Narrative'] . " Usuario:" . $_SESSION['UserID'] . "',
				'" . $OrdernoAjuste . "',
				'" . $_SESSION['Adjustment']->Quantity . "',
				'" . ($QtyOnHandPrior + $_SESSION['Adjustment']->Quantity) . "',
				'" . $LocTagRef . "',
				'" . $EstimatedAvgCostXlegal . "',
			     NOW(), '".$_SESSION['UserID']."'
			)";
        }
       //  echo "<br>SQL1: <pre>".$SQL;
        
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
        $DbgMsg = _('The following SQL to insert the stock movement record was used');
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
        
        /*Get the ID of the StockMove... */
        $StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');
        
        /*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
        
        if ($_SESSION['Adjustment']->Controlled == 1) {
            foreach ($_SESSION['Adjustment']->SerialItems as $Item) {
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
                $Result = DB_query($SQL, $db, $ErrMsg);
                $SerialItemExistsRow = DB_fetch_row($Result);
                
                if ($SerialItemExistsRow[0] == 1) {
                    $SQL = "UPDATE stockserialitems SET
						quantity= quantity + " . $Item->BundleQty . "
						WHERE
						stockid='" . $_SESSION['Adjustment']->StockID . "'
						AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'
						AND serialno='" . $Item->BundleRef . "'";
                    
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
                    $DbgMsg = _('The following SQL to update the serial stock item record was used');
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
						'" . $Item->CostSerialItem . "',
						'',
						" . $Item->BundleQty . ")";
                    
                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
                    $DbgMsg = _('The following SQL to update the serial stock item record was used');
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
							 " . $Item->BundleQty . ",'";
                if (empty($Item->CostSerialItem)) {
                    $SQL.= "0";
                } else {
                    $SQL.= $Item->CostSerialItem;
                }
                $SQL.= "')";
                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
                $DbgMsg = _('The following SQL to insert the serial stock movement records was used');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                $costoserie = $costoserie + $Item->CostSerialItem;
            }

           
             /* foreach controlled item in the serialitems array */
            $SQL = "UPDATE stockmoves
			         SET standardcost= " . $costoserie . "
			        WHERE stkmoveno='" . $StkMoveNo . "'";
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            $EstimatedAvgCostXlegal = $costoserie / $_SESSION['Adjustment']->Quantity;
        }
         /*end if the adjustment item is a controlled item */
        
        $SQL = "UPDATE locstock SET quantity = quantity + " . $_SESSION['Adjustment']->Quantity . "
				WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'
				AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'";
        
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
        $DbgMsg = _('The following SQL to update the stock record was used');

        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
        
        $Result = DB_Txn_Commit($db);
        
        prnMsg(_('Se realizo con éxito el ajuste para ') . ' ' . $_SESSION['Adjustment']->StockID . ' -  ' . $_SESSION['Adjustment']->ItemDescription . ' ' . _('en el almacen') . ' ' . $_SESSION['Adjustment']->StockLocation . ' ' . _('por la cantidad de ') . ' ' . $_SESSION['Adjustment']->Quantity, 'success');
        
        unset($_SESSION['Adjustment']);
        //include ('includes/footer.inc');
        //exit;
    }
     /* end if there was no input error */
}
 /* end if the user hit enter the adjustment */

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

if (!isset($_SESSION['Adjustment'])) {
    $StockID = '';
    $Controlled = 0;
    $Quantity = 0;
} else {
    $StockID = isset($_SESSION['Adjustment']->StockID) ? $_SESSION['Adjustment']->StockID : '';
    $Controlled = isset($_SESSION['Adjustment']->Controlled) ? $_SESSION['Adjustment']->Controlled : 0;
    $Quantity = isset($_SESSION['Adjustment']->Quantity) ? $_SESSION['Adjustment']->Quantity : 0;
}
echo '<div class="centre">';
echo '<br><b>' . _('Ajustes de Inventario Sin Contabilidad') . '</b><br>';
echo '</div>';

echo '<table><tr><td>' . _('Codigo de Producto') . ':</td><td><input type=text name="StockID" size=21 value="' . $StockID . '" maxlength=20> <input type=submit name="CheckCode" VALUE="' . _('Verifica Codigo de Producto') . '"></td></tr>';

if ($permisocambiarfecha == 1) {
    echo '<tr>
			<td>Fecha:</td>
			<td>
				<table align="left">
				<tr>
					<td><select Name="FromDia">';
                    $sql = "SELECT * FROM cat_Days";
                    $dias = DB_query($sql, $db);
    while ($myrowdia = DB_fetch_array($dias, $db)) {
        $diabase = $myrowdia['DiaId'];
        if (rtrim(intval($FromDia2)) == rtrim(intval($diabase))) {
            echo '<option  value="' . $myrowdia['DiaId'] . '  " selected>' . $myrowdia['Dia'] . '</option>';
        } else {
            echo '<option  value="' . $myrowdia['DiaId'] . '" >' . $myrowdia['Dia'] . '</option>';
        }
    }
                    echo '</select></td>';
                    echo '<td><select Name="FromMes">';
                    $sql = "SELECT * FROM cat_Months";
                    $Meses = DB_query($sql, $db);
    while ($myrowMes = DB_fetch_array($Meses, $db)) {
        $Mesbase = $myrowMes['u_mes'];
        if (rtrim(intval($FromMes2)) == rtrim(intval($Mesbase))) {
            echo '<option  value="' . $myrowMes['u_mes'] . '  " selected>' . $myrowMes['mes'] . '</option>';
        } else {
            echo '<option  value="' . $myrowMes['u_mes'] . '" >' . $myrowMes['mes'] . '</option>';
        }
    }
    
    echo '</select>';
    echo '&nbsp;<input name="FromYear" type="text" size="4" value=' . $FromYear2 . '>';
    
    echo '</td>
				</tr>
			</table>
		</td>
	</tr>';
}


if (isset($_SESSION['Adjustment']->ItemDescription) and strlen($_SESSION['Adjustment']->ItemDescription) > 1) {
    echo '<tr><td colspan=3><font color=BLUE size=3>' . $_SESSION['Adjustment']->ItemDescription . ' (' . _('In Units of') . ' ' . $_SESSION['Adjustment']->PartUnit . ' ) - ' . _('Unit Cost') . ' = ' . $_SESSION['Adjustment']->StandardCost . '</font></td></tr>';
}

echo '<tr>';

//echo '<br>Stockloction: '.$_SESSION['Adjustment']->StockLocation;
echo '<td>' . _('Ajustes de Inventario en Almacen') . ':</td><td><select name="StockLocation"> ';
//$sql = 'SELECT loccode, locationname FROM locations';

$sql = 'SELECT l.loccode, locationname FROM locations l, sec_loccxusser lxu where l.loccode=lxu.loccode and userid="' . $_SESSION['UserID'] . '"';

$resultStkLocs = DB_query($sql, $db);

while ($myrow = DB_fetch_array($resultStkLocs)) {
    if (isset($_SESSION['Adjustment']->StockLocation)) {
        if (isset($_POST['StockLocation']) and $myrow['loccode'] == $_POST['StockLocation']) {
            echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
        } else {
            echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
        }
    } elseif (isset($_POST['StockLocation']) and $myrow['loccode'] == $_POST['StockLocation']) {
        echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
        $_POST['StockLocation'] = $myrow['loccode'];
    } else {
        echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
    }
}

echo '</select>&nbsp;&nbsp;<input type="submit" value="->" /></td></tr>';
//////////////////////////////////////////////////////////////
//////////////////////      AJUSTES DE LOCALIDAD    ////////// 24/03/2017
//////////////////////////////////////////////////////////////
///
///
    // echo '<tr><td>' . _('Ajustes de Inventario en Localidad') . ':</td><td><select name="StockSectorLocation"> ';

    // $sql = "SELECT seccode, location, typeinventoryuse FROM sectorlocations WHERE loccode = '" . $_POST['StockLocation']. "' ORDER BY location ASC";

    // $resultStkSecLocs = DB_query($sql, $db);
    
    // if(DB_num_rows($resultStkSecLocs) > 0) {
    //     while ($myrow = DB_fetch_array($resultStkSecLocs)) {
    //         if ($myrow['seccode'] == $_SESSION['Adjustment']->StockSectorLocation) {
    //             echo '<option selected value="' . $myrow['seccode'] . '">' . $myrow['location'] . '</option>';
    //         } elseif($myrow['typeinventoryuse'] == 0){
    //             echo '<option selected value="' . $myrow['seccode'] . '">' . $myrow['location'] . '</option>';
    //         } else {
    //             echo '<option value="' . $myrow['seccode'] . '">' . $myrow['location'] . '</option>';
    //         }
    //     }
    // } else {
    //     echo '<option value="">Selecciona Localidad</option>';
    // }

    // echo '</select>&nbsp;&nbsp;<input type="submit" value="->" /></td></tr>';

///
//////////////////////////////////////////////////////////////
/////////////////   TERMINAN AJUSTES DE LOCALIDAD   //////////
//////////////////////////////////////////////////////////////

if (!isset($_SESSION['Adjustment']->Narrative)) {
    $_SESSION['Adjustment']->Narrative = '';
}

//<-------------------------------------- Fin motivos de ajuste-------------------------------------
if ($permisovermotivodeajuste ==1) {
/*$SQL="SELECT reasonid FROM stockmoves WHERE stockmoves.`stkmoveno`='".$_SESSION['Adjustment']->StockID."'";
$ResultIndex=DB_query($SQL, $db);
if(DB_num_rows($SQL,$db)>0 and !isset($_POST['motivo']))
{
	$ro=DB_fetch_array($ResultIndex);
	$_POST['motivo'] = $ro['reasonid'];
}*/
    $sqli = "SELECT id,Concat(CODE,' - ',description)AS descripcion FROM adjustmentreasons";
    $resultmotivo = DB_query($sqli, $db);
    echo '<tr><td>' . _('Motivo de Ajuste') . ':</td><td><select name="motivo">';
    while ($myrow = DB_fetch_array($resultmotivo)) {
        if ($myrow['id'] == $_POST['motivo']) {
            echo '<option selected value="' . $myrow['id'] . '">' . $myrow['descripcion'];
        } else {
            echo '<option value="' . $myrow['id'] . '">' . $myrow['descripcion'];
        }
    }
    echo '</select></td></tr>';
}
//<-------------------------------------- Fin motivos de ajuste-------------------------------------


echo '<tr><td>' . _('Comentarios de la Razon') . ':</td><td>';

//echo '<input type=text name="Narrative" size=32 maxlength=30 value="' . $_SESSION['Adjustment']->Narrative . '">'
echo "<textarea name='Narrative' cols='40' rows='3'>" . $_SESSION['Adjustment']->Narrative . "</textarea>";

echo '</td></tr>';

echo '<tr><td>' . _('Cantidad a Ajustar') . ':</td>';

echo '<td>';
if ($Controlled == 1) {
    if ($_SESSION['Adjustment']->StockLocation != '') {
        echo '<input type="HIDDEN" name="Quantity" Value="' . $_SESSION['Adjustment']->Quantity . '">
				' . $_SESSION['Adjustment']->Quantity . ' &nbsp; &nbsp; &nbsp; &nbsp;
				[<a href="' . $rootpath . '/StockAdjustmentsControlled_Magic.php?AdjType=REMOVE&' . SID . '">' . _('Remove') . '</a>]
				[<a href="' . $rootpath . '/StockAdjustmentsControlled_Magic.php?AdjType=ADD&' . SID . '">' . _('Add') . '</a>]';
    } else {
        prnMsg(_('Please select a location and press') . ' "' . _('Enter Stock Adjustment') . '" ' . _('below to enter Controlled Items'), 'info');
    }
} else {
    echo '<input type=TEXT class="number" name="Quantity" size=12 maxlength=12 Value="' . $Quantity . '">';
}
echo '</td></tr>';

echo '</table>';
echo '<div class="centre">';
if (Havepermission($_SESSION['UserID'], 423, $db) == 1) {
    //echo '<input type=submit name="EnterAdjustment" VALUE="' . _('Solicitar') . '">';
}
if (Havepermission($_SESSION['UserID'], 424, $db) == 1) {
    //echo '<input type=submit name="EnterAdjustment" VALUE="' . _('Autorizar') . '">';
}

//if (Havepermission($_SESSION['UserID'], 426, $db) == 1 /*and $quotation==2*/) {
    echo '<input type=submit name="EnterAdjustment" VALUE="' . _('Procesar') . '">';
//}

echo '</div>';
echo '<input type="hidden" name="OrdernoAjuste" value="' . $OrdernoAjuste . '">';
echo '<hr><br>';

if (!isset($_POST['StockLocation'])) {
    $_POST['StockLocation'] = '';
}

//echo '<a href="' . $rootpath . '/StockStatus.php?' . SID . '&StockID=' . $StockID . '">' . _('Mostrar estado de Existencias') . '</a>';
//echo '<br><a href="' . $rootpath . '/StockMovements.php?' . SID . '&StockID=' . $StockID . '">' . _('Mostrar Movimientos') . '</a>';
//echo '<br><a href="' . $rootpath . '/StockUsage.php?' . SID . '&StockID=' . $StockID . '&StockLocation=' . $_POST['StockLocation'] . '">' . _('Mostrar Uso del Producto') . '</a>';
//echo '<br><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '&StockLocation=' . $_POST['StockLocation'] . '">' . _('Buscar Ordenes de Venta Pendientes') . '</a>';
//echo '<br><a href="' . $rootpath . '/SelectCompletedOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Buscar Ordenes de Ventas Completadas') . '</a>';

echo '</div></form>';

//var_dump($_SESSION['Adjustment']);
include('includes/footer.inc');
