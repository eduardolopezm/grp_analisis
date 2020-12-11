<?php
/**
 * Reversar recepción de productos
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 28/11/2017
 * Fecha Modificación: 28/11/2017
 * Realiza la reversa de movimientos de una recepción de productos
 */

$PageSecurity = 11;

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 

$PageSecurity = 5;
$funcion = 185;
include('includes/DefineSerialItems.php');
include('includes/Jquery.inc');
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//*********** P E R M I S O S *****************
$permiso_reversar_en_fecha_diferente=  Havepermission($_SESSION['UserID'], 1486, $db);
//*********************************************

if (isset($_POST['SelectedSupplier'])) {
    $SelectedSupplier = $_POST['SelectedSupplier'];
} else {
    $SelectedSupplier = $_GET['SelectedSupplier'];
}

if (isset($_POST['ordenno'])) {
    $ordenno = $_POST['ordenno'];
} else {
    $ordenno = $_GET['ordenno'];
}

if (isset($_POST['tagref'])) {
    $tagref = $_POST['tagref'];
} else {
    $tagref = $_GET['tagref'];
}

/*
if ($_SESSION['SelectedSupplier']!="" AND isset($_SESSION['SelectedSupplier']) AND !isset($_POST['SelectedSupplier']) OR $_POST['SelectedSupplier']==""){
	$SelectedSupplier=$_SESSION['SelectedSupplier'];
}


if (!isset($SelectedSupplier) OR $SelectedSupplier==""){
	echo '<br>' . _('This page is expected to be called after a supplier has been selected');
	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/SelectSupplier.php?' . SID . "'>";
	exit;
} elseif (!isset($_POST['SuppName']) or $_POST['SuppName']=="") {

*/
    $sql = "SELECT suppname FROM suppliers WHERE SupplierId='" . $SelectedSupplier . "'";
    $SuppResult = DB_query($sql, $db, _('Could not retrieve the supplier name for') . ' ' . $_SESSION['SelectedSupplier']);
    $SuppRow = DB_fetch_row($SuppResult);
    $_POST['SuppName'] = $SuppRow[0];

// }

echo '<div align="center"><h3>Productos Recibidos de '.$_POST['SuppName'].'</h3></div>';
//echo '<div align="center"><a href="panel_ordenes_compra.php?" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> '.traeNombreFuncion(1371, $db).'</a></div><br>';

if (isset($_GET['GRNNo']) and isset($SelectedSupplier)) {
    $clavepresupuestal = "";
    $realorderno = "";
    $SQL = "SELECT grns.podetailitem,
	grns.grnbatch,
	grns.itemcode,
	grns.itemdescription,
	grns.deliverydate,
	DATE_FORMAT(grns.deliverydate,'%d/%m/%Y') as deliverydatedos,
	purchorderdetails.factorconversion,
	purchorderdetails.glcode,
	purchorders.orderno,
	purchorderdetails.podetailitem as orderdetail,
	grns.qtyrecd,
	grns.quantityinv,
	purchorderdetails.stdcostunit/purchorderdetails.factorconversion as stdcostunit,
	purchorders.intostocklocation,
	purchorders.orderno,
	tags.legalid,
    purchorderdetails.clavepresupuestal,
    purchorders.realorderno
	FROM grns, purchorderdetails, purchorders 
    JOIN tags ON purchorders.tagref = tags.tagref
	WHERE grns.podetailitem=purchorderdetails.podetailitem
	AND purchorders.orderno = purchorderdetails.orderno
	AND grnno='" . (int) $_GET['GRNNo'] . "'";
    //echo '<BR>COMPRA:'.$SQL.'<BR>';

    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not get the details of the GRN selected for reversal because') . ' ';
    $DbgMsg = _('The following SQL to retrieve the GRN details was used') . ':';

    $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg);

    $GRN = DB_fetch_array($Result);
    $QtyToReverse = ($GRN['qtyrecd'] - $GRN['quantityinv'])*$GRN['factorconversion'];
    $factorconversion=$GRN['factorconversion'];
    $realorderno=$GRN['realorderno'];
    $clavepresupuestal=$GRN['clavepresupuestal'];

    if ($QtyToReverse == 0) {
        prnMsg(_('The GRN'). " ".$_GET['GRNNo']." ". _('has already been reversed or fully invoiced by the supplier - it cannot be reversed - stock quantities must be corrected by stock adjustments - the stock is paid for'), 'warn');
        include('includes/footer_Index.inc');
        exit;
    }
    
    $SQL= "SELECT quantity
			FROM locstock
			WHERE stockid='" . $GRN['itemcode'] . "'
			AND loccode= '" . $GRN['intostocklocation'] . "'";
        
    $Result = DB_query($SQL, $db, _('Could not get the quantity on hand of the item before the reversal was processed'), _('The SQL that failed was'), true);
    
    if (DB_num_rows($Result)>0) {
        $registro= DB_fetch_array($Result);
        $cantidad= $registro["quantity"];
        
        if ($QtyToReverse > $cantidad) {
            echo "<br><br>";
            prnMsg(_('La cantidad a reversar del producto'). " ".$GRN['itemcode']." ". _('es mayor o igual a la existente en almacen, por lo que no se puede efectuar este movimiento.'));
            include('includes/footer_Index.inc');
            exit;
        }
    }

    $SQL = "SELECT stockmaster.controlled 
			FROM stockmaster WHERE stockid ='" . $GRN['itemcode'] . "'";
    $CheckControlledResult = DB_query($SQL, $db, '<br>' . _('Could not determine if the item was controlled or not because') . ' ');
    $ControlledRow = DB_fetch_row($CheckControlledResult);
    
    if ($ControlledRow[0]==1) {
        $Controlled = true;
        $SQL = "SELECT stockserialmoves.serialno, 
				stockserialmoves.moveqty
		        FROM stockmoves INNER JOIN stockserialmoves 
				ON stockmoves.stkmoveno= stockserialmoves.stockmoveno
				INNER JOIN grns ON stockmoves.transno= grns.grnbatch and grns.itemcode=stockmoves.stockid
				and grns.qtyrecd=stockmoves.qty
			WHERE stockmoves.stockid='" . $GRN['itemcode'] . "'
			AND stockmoves.type =25
			AND grnno=".(int) $_GET['GRNNo']."
			AND stockserialmoves.orderdetailno=".$GRN['orderdetail']."
			AND stockserialmoves.orderno=".$GRN['orderno']."
			AND grns.podetailitem=".$GRN['podetailitem']."
			AND stockmoves.transno=" . $GRN['grnbatch'];
        $GetStockMoveResult = DB_query($SQL, $db, _('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
        //echo '<BR>SERIALS:'.$SQL.'<BR>';
        while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)) {
            $SQL = "SELECT stockserialitems.quantity
			        FROM stockserialitems
				WHERE stockserialitems.stockid='" . $GRN['itemcode'] . "'
				AND stockserialitems.loccode ='" . $GRN['intostocklocation'] . "'
				AND stockserialitems.serialno ='" . $SerialStockMoves['serialno'] . "'";
        
            $GetQOHResult = DB_query($SQL, $db, _('Unable to retrieve the quantity on hand of') . ' ' . $GRN['itemcode'] . ' ' . _('for Serial No') . ' ' . $SerialStockMoves['serialno']);
            $GetQOH = DB_fetch_row($GetQOHResult);
            if ($GetQOH[0] < $SerialStockMoves['moveqty']) {
                prnMsg(_('Dessafortunadamente el numero de serie original') . ' (' . $SerialStockMoves['moveqty'] . ') ' . _('que se recibio con numero de serie') . ' ' . $SerialStockMoves['serialno'] . ' ' . _('solo existen') . ' ' . $GetQOH[0] . ' ' . _('unidades') . '. ' . _('La reversa de producto serailizados solo es viable cuando los numeros de serie existen aun en el lugar en el que fueron solicitados'), 'error');
                include('includes/footer_Index.inc');
                exit;
            }
        }
        DB_data_seek($GetStockMoveResult, 0);
    } else {
        $Controlled = false;
    }

    //$Result = DB_query("BEGIN",$db);
    $Result = DB_Txn_Begin($db);
    if ($_GET['reversedate'] != null) {
        $Date_ArrayRe = explode('/', $_GET['reversedate']);
        $GRN['deliverydate'] = $Date_ArrayRe[2].'/'.$Date_ArrayRe[1].'/'.$Date_ArrayRe[0];
    } else {
        $GRN['deliverydate']=Date('Y-m-d');
    }
    
    $PeriodNo = GetPeriodXLegal(ConvertSQLDate($GRN['deliverydate']), $GRN['legalid'], $db);
    
    $TransNo=GetNextTransNo(800, $db);
    $_POST['DefaultReceivedDate']= $GRN['deliverydate'];//$GRN['deliverydatedos'];

    //$PeriodNo = GetPeriod($_POST['DefaultReceivedDate'], $db, $tagref);
    //echo 'P:'.$PeriodNo.'fecha:'.$_POST['DefaultReceivedDate'];
    $SQL = "SELECT stockmaster.controlled
		FROM stockmaster
		WHERE stockmaster.stockid = '" . $GRN['itemcode'] . "'";
    $Result = DB_query($SQL, $db, _('Could not determine if the item exists because'), '<br>' . _('The SQL that failed was') . ' ', true);

    if (DB_num_rows($Result)==1) {
        $StkItemExists = DB_fetch_row($Result);
        $Controlled = $StkItemExists[0];
        $SQL="SELECT quantity
			FROM locstock
			WHERE stockid='" . $GRN['itemcode'] . "'
			AND loccode= '" . $GRN['intostocklocation'] . "'";
            
        $Result = DB_query($SQL, $db, _('Could not get the quantity on hand of the item before the reversal was processed'), _('The SQL that failed was'), true);
        
        if (DB_num_rows($Result)==1) {
            $LocQtyRow = DB_fetch_row($Result);
            $QtyOnHandPrior = $LocQtyRow[0];
        } else {
            $QtyOnHandPrior = 0;
        }

        $SQL = "UPDATE locstock
			SET quantity = quantity - " . $QtyToReverse . "
			WHERE stockid = '" . $GRN['itemcode'] . "'
			AND loccode = '" . $GRN['intostocklocation'] . "'";

        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
        $DbgMsg = _('The following SQL to update the location stock record was used');
        $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        $SQL = "INSERT INTO stockmoves (
				stockid,
				type,
				transno,
				loccode,
				trandate,
				prd,
				reference,
				qty,
				standardcost,
				newqoh,
				tagref
				)
			VALUES (
				'" . $GRN['itemcode'] . "',
				800,
				" . $TransNo . ",
				'" . $GRN['intostocklocation'] . "',
				'" . $GRN['deliverydate'] . "',
				" . $PeriodNo . ", 
				'" . _('Reversal Proveedor:') . ' - ' . $SelectedSupplier . ' - OC:' . $GRN['orderno'] .' - REC:'. $_GET['GRNNo']. "',
				" . -$QtyToReverse . ',
				' . $GRN['stdcostunit'] . ',
				' . ($QtyOnHandPrior - $QtyToReverse) . ',
				' . $tagref . '
				)';

        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
        $DbgMsg = _('The following SQL to insert the stock movement records was used');
        $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        $StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');

        if ($Controlled==true) {
            while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)) {
                $SQL = "INSERT INTO stockserialmoves (
						stockmoveno,
						stockid,
						serialno,
						moveqty)
					VALUES (
						" . $StkMoveNo . ",
						'" . $GRN['itemcode'] . "',
						'" . $SerialStockMoves['serialno'] . "',
						" . -$SerialStockMoves['moveqty'] . ")";
                $result = DB_query($SQL, $db, _('Could not insert the reversing stock movements for the batch/serial numbers'), _('The SQL used but failed was') . ':', true);

                $SQL = "UPDATE stockserialitems
					SET quantity=quantity - " . $SerialStockMoves['moveqty'] . "
					WHERE stockserialitems.stockid='" . $GRN['itemcode'] . "'
					AND stockserialitems.loccode ='" . $GRN['intostocklocation'] . "'
					AND stockserialitems.serialno = '" . $SerialStockMoves['serialno'] . "'";
                $result = DB_query($SQL, $db, _('Could not update the batch/serial stock records'), _('The SQL used but failed was') . ':', true);
            }
        }
    }

    /*******************************************************************************************************************/
    /***** cambio de actualizacion para considerar no halla negativos en ordenes de compra******************************/
    /******************************************************************************************************************/

    $SQL = "UPDATE purchorders
		SET status='Authorised'
		WHERE orderno = " . $GRN['orderno'];

    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase order detail record could not be updated with the quantity reversed because');
    $DbgMsg = _('The following SQL to update the purchase order detail record was used');
    $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

    $SQL = "UPDATE purchorderdetails
		SET quantityrecd = quantityrecd - " . ($QtyToReverse/$factorconversion) . ",
		completed=0
		
		WHERE purchorderdetails.podetailitem = " . $GRN['podetailitem'];

    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase order detail record could not be updated with the quantity reversed because');
    $DbgMsg = _('The following SQL to update the purchase order detail record was used');
    $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

    /*******************************************************************************************************************/
    /***** Cancelacion de recepcion de productos para requisiciones de compra *****************************************/
    /******************************************************************************************************************/
    $StockID=$GRN['itemcode'];
    $OrderNoReq=$GRN['orderno'];
    $GRNReq=$_GET['GRNNo'];
    //include('includes/ProcessRequisitionsReverseCompras.inc');

    /******************************************************************************************************************/
    //$PeriodNo = GetPeriod($_POST['DefaultReceivedDate'], $db);
    $cantidadreversa=($QtyToReverse/$factorconversion);
    $SQL = "UPDATE grns
		SET qtyrecd = qtyrecd - $cantidadreversa
		WHERE grns.grnno=" . $_GET['GRNNo'];

    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN record could not be updated') . '. ' . _('This reversal of goods received has not been processed because');
    $DbgMsg = _('The following SQL to insert the GRN record was used');
    $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    //	echo '<pre>sql reversa:<br>'.$SQL;
    /******************************************************************************************************************/

    if ($_SESSION['CompanyRecord']['gllink_stock']==1 and $GRN['glcode'] !=0 and $GRN['stdcostunit']!=0) {
        if ($permiso_reversar_en_fecha_diferente and isset($_GET['reversedate'])) {
            $dia=substr($_GET['reversedate'], 0, 2);
            $mes=substr($_GET['reversedate'], 3, 2);
            $anio=substr($_GET['reversedate'], 6, 4);
            $fechagltrans=$anio.'-'.$mes.'-'.$dia;
        } else {
            $fechagltrans=$GRN['deliverydate'];
        }
                
        $SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag
				)
			VALUES (
				800,
				" .$TransNo. ",
				'" . ConvertSQLDate($fechagltrans) . "',
				" . $PeriodNo . ",
				" . $GRN['glcode'] . ", 
				'" . _('GRN Reversal for PO') .": " . $GRN['orderno'] . " " . $SelectedSupplier . " - " . $GRN['itemcode'] . "-" . $GRN['itemdescription'] . " x " . $QtyToReverse . " @ " . number_format($GRN['stdcostunit'], 2) . "',
				" . -($GRN['stdcostunit'] * $QtyToReverse) . ",
				" . $tagref . "
				)";

        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase GL posting could not be inserted for the reversal of the received item because');
        $DbgMsg = _('The following SQL to insert the purchase GLTrans record was used');
        //$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        $montocontable=-($GRN['stdcostunit'] * $QtyToReverse);
        $Narrative= _('GRN Reversal for PO') .": " . $GRN['orderno'] . " " . $SelectedSupplier . " - " . $GRN['itemcode'] . "-" . $GRN['itemdescription'] . " x " . $QtyToReverse ;

        $ISQL = Insert_Gltrans(
            800,
            $TransNo,
            $fechagltrans,
            $PeriodNo,
            $GRN['glcode'],
            $Narrative,
            $tagref,
            $_SESSION['UserID'],
            1,
            '',
            '',
            $GRN['itemcode'],
            $QtyToReverse,
            $_GET['GRNNo'],
            $GRN['intostocklocation'],
            $GRN['stdcostunit'],
            $SelectedSupplier,
            $GRN['orderno'],
            $montocontable,
            $db,
            '',
            'COSTO INVENTARIO'
        );
        if ($_SESSION['UserID'] == "desarrollo") {
            //		 echo '<br>sql1 <pre>'.$ISQL."<br>";
        }
        $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
        
        //echo '<pre><br>'.$SQL;
        
        $SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag)
			VALUES (
				800,
				" . $TransNo . ",
				'" . ConvertSQLDate($fechagltrans). "',
				" . $PeriodNo . ",
				" . $_SESSION['CompanyRecord']['grnact'] . ", '"
                . _('GRN Reversal PO') . ': ' . $GRN['orderno'] . " " . $SelectedSupplier . " - " . $GRN['itemcode'] . "-" . $GRN['itemdescription'] . " x " . $QtyToReverse . " @ " . number_format($GRN['stdcostunit'], 2) . "',
				" . $GRN['stdcostunit'] * $QtyToReverse . ",
				" . $tagref . "
				)";
        
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN suspense side of the GL posting could not be inserted because');
        $DbgMsg = _('The following SQL to insert the GRN Suspense GLTrans record was used');
        //$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        $montocontable=($GRN['stdcostunit'] * $QtyToReverse);
        $Narrative= _('GRN Reversal for PO') .": " . $GRN['orderno'] . " " . $SelectedSupplier . " - " . $GRN['itemcode'] . "-" . $GRN['itemdescription'] . " x " . $QtyToReverse ;

        $cuenta_proveedor= traeCuentaProveedor($SelectedSupplier, $db);

        $ISQL = Insert_Gltrans(
            800,
            $TransNo,
            $fechagltrans,
            $PeriodNo,
            $cuenta_proveedor, //$_SESSION['CompanyRecord']['grnact']
            $Narrative,
            $tagref,
            $_SESSION['UserID'],
            1,
            '',
            '',
            $GRN['itemcode'],
            $QtyToReverse,
            $_GET['GRNNo'],
            $GRN['intostocklocation'],
            $GRN['stdcostunit'],
            $SelectedSupplier,
            $GRN['orderno'],
            $montocontable,
            $db,
            '',
            'INVENTARIO'
        );
        $Result = DB_query($ISQL, $db, $ErrMsg, $DbgMsg, true);
               // COMPROMETIDO
               // POREJERCER
        if ($_SESSION['UserID'] == "desarrollo") {
            //echo '<br>sql1 <pre>'.$ISQL."<br>";
        }
        //$Result= GeneraMovimientoContablePresupuesto(800, "COMPROMETIDO", "POREJERCER", $TransNo, $PeriodNo,
        //$montocontable, $tagref,$fechagltrans, $db);
    }

    $resultado= GeneraMovimientoContablePresupuesto(
        800,
        "DEVENGADO",
        "COMPROMETIDO",
        $TransNo,
        $PeriodNo,
        $montocontable,
        $tagref,
        $fechagltrans,
        $clavepresupuestal,
        $realorderno,
        $db,
        false,
        '',
        ''
    );

    // Log Presupuesto
    $descriptionLog = "Rechazar Recepción Orden de Compra";
    $agregoLog = fnInsertPresupuestoLog($db, 800, $TransNo, $tagref, $clavepresupuestal, $PeriodNo, ($montocontable) * -1, 260, "", $descriptionLog); // Abono
    $agregoLog = fnInsertPresupuestoLog($db, 800, $TransNo, $tagref, $clavepresupuestal, $PeriodNo, ($montocontable), 259, "", $descriptionLog); // Cargo

    $SQL="COMMIT";
    //$Result = DB_query($SQL,$db);
    $Result = DB_Txn_Commit($db);

    // echo "<br>realorderno: ".$realorderno;
    // echo "<br>clavepresupuestal: ".$clavepresupuestal;
    // echo "<br>type: 800";
    // echo "<br>TransNo: ".$TransNo;

    //echo '<br><b>' . _('SE REALIZO EXITOSAMENTE LA OPERACION DE REVERSA NO.') . ' ' . $_GET['GRNNo'] . ' ' . _('DE ') . ' ' . $QtyToReverse . ' UNIDADES DEL PRODUCTO ' . $GRN['itemcode'] . ' - ' . $GRN['itemdescription'] . '</b><br>';
    
    prnMsg('Orden de Compra '.$realorderno.'. Se rechazaron '.$QtyToReverse.' Unidades del Producto '.$GRN['itemcode'].' - '.$GRN['itemdescription'], 'success');

    unset($_GET['GRNNo']);

    //echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedSupplier='.$SelectedSupplier.'&ordenno='.$ordenno.'"><b>' . _('Realizar otra operacion de Reversa') . '</b></a>';
}
    
echo '<form name="FDatosB" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

if (!isset($_POST['RecdAfterDate']) or !Is_Date($_POST['RecdAfterDate'])) {
    $_POST['RecdAfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date("m")-3, Date("d"), Date("Y")));
}
echo '<input type=hidden name="reversar" id="reversar" />';
echo '<input type=hidden name="SelectedSupplier" id="SelectedSupplier" VALUE="' . $SelectedSupplier . '" />';
echo '<input type=hidden name="SuppName" id="SuppName" VALUE="' . $_POST['SuppName'] . '" />';

#echo _('Mostrar todos los productos recibidos despues del') . ': <input type=text class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="RecdAfterDate" Value="' . $_POST['RecdAfterDate'] . '" MAXLENGTH =10 size=10>
#        <input type=submit name="ShowGRNS" VALUE=' . _('Mostrar') . '>';

if (!empty($_GET['reversedatediff'])) {
    //$LinkToRevGRN='<input type="button" id="datepicker" name="reversedate'.$ListNoReg.'" value="'.$DisplayDateDel.'" READONLY=false></input>';
    if (isset($_GET['SoloGRNXReversar'])) {
            $pagina="ReverseGRN.php?SoloGRNXReversar=Y";//$_SERVER['PHP_SELF'] . '?' . SID ;
            $GRNNo=$myrow['grnno'];
            $LinkToRevGRN .= "<a href=javascript:onclick=confirmar('".$pagina."','".$GRNNo."','".$SelectedSupplier."','".$ordenno."','".$ListNoReg."') >" . _('REVERSAR') . "</a>";
    } else {
            $pagina="ReverseGRN.php?";
            $GRNNo=$myrow['grnno'];
            $LinkToRevGRN .= "<a href=javascript:onclick=confirmar('".$pagina."','".$GRNNo."','".$SelectedSupplier."','".$tagref."','".$ordenno."','".$ListNoReg."') >" . _('REVERSAR') . "</a>";
    }
                                            
                                            
    $diadis=  substr($_GET['DisplayDateDel'], 0, 2);
    $mesdis=  substr($_GET['DisplayDateDel'], 3, 2);
    $aniodis=  substr($_GET['DisplayDateDel'], 6, 4);
    $fechadis= $mesdis.'/'.$diadis.'/'.$aniodis;

    echo '<table>';
    echo '<tr><td colspan=4><font size=3 color:blue>'._('&iquest;Esta seguro de reversar el producto?').'</font></td></tr>';
    echo '<tr><td> </td></tr>';
    echo '<tr>';
    echo '<td>';
    echo 'Modificar fecha:';
    echo '</td>';

    echo'<td><select Name="ToDia" id="ToDia">';
    $sql = "SELECT * FROM cat_Days";
    $Todias = DB_query($sql, $db);
    while ($myrowTodia=DB_fetch_array($Todias, $db)) {
            $Todiabase=$myrowTodia['DiaId'];
        if ($diadis==rtrim(intval($Todiabase))) {
                echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" selected>' .$myrowTodia['Dia'];
        } else {
                echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
        }
    }
    echo'</select>';
    echo '</td>';

    echo '<td>';
    echo'';
    echo'<select Name="ToMes" id="ToMes">';
    $sql = "SELECT * FROM cat_Months";
    $ToMeses = DB_query($sql, $db);
    while ($myrowToMes=DB_fetch_array($ToMeses, $db)) {
            $ToMesbase=$myrowToMes['u_mes'];
        if ($mesdis==rtrim(intval($ToMesbase))) {
                echo '<option  VALUE="' . trim($myrowToMes['u_mes']) .  '" selected>' .$myrowToMes['mes'];
        } else {
                echo '<option  VALUE="' . trim($myrowToMes['u_mes']) .  '" >' .$myrowToMes['mes'];
        }
    }
    echo '</select>';
    echo '</td>';

    echo '<td>';
    echo '&nbsp;<input name="ToYear"  id="ToYear" type="text" size="4" value='.$aniodis.'>';
    echo '</td>';
    echo '<tr><td> </td></tr>';
    echo'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan=2 style="text-align:center">';
    echo "<a href=javascript:onclick=confirmar('".$_GET['pagina']."','".$_GET['grnno']."','".$_GET['selectedsupplier']."','".$_GET['tagref']."','".$ordenno."','".$_GET['listnoreg']."') >";
    //echo "<input type='button' value='CONTINUAR'>";
    echo "<img src='".$rootpath."/images/procesar_25.png'";
    echo "</a>";
    echo '</td>';
    echo '<td colspan=2 style="text-align:center">';
    echo "<a href=".$rootpath."/ReverseGRN.php?&SelectedSupplier=".$SelectedSupplier."&ordenno=".$ordenno.">" ;
    //echo '<input type="button" value="CANCELAR">';
    echo "<img src='".$rootpath."/images/cancelar_25.png'";
    echo "</a>";
    echo '</td>';
    echo '</tr>';
    echo '</table>';
}
    #if (isset($_POST['ShowGRNS'])){

if (isset($SelectedSupplier)) {
    $whereSoloXReversar = " AND (qtyrecd-quantityinv) >=0 ";
        
    if (isset($_GET['SoloGRNXReversar'])) {
        $whereSoloXReversar = " AND (qtyrecd-quantityinv) >0 ";
    }
    if ($_SESSION['ReversePurchOrders']==0) {
        $sql = "SELECT  grnno,
		po.orderno,
		grns.itemcode,
		grns.itemdescription,
		grns.deliverydate,
		qtyrecd,
		quantityinv,
		qtyrecd-quantityinv AS qtytoreverse,
		po.tagref,
		grns.grnbatch,
		pd.factorconversion,
		po.realorderno
		FROM grns INNER JOIN purchorderdetails pd ON pd.podetailitem=grns.podetailitem
		INNER JOIN purchorders po ON po.orderno = pd.orderno
		LEFT JOIN locstock  ON po.intostocklocation = locstock.loccode and locstock.stockid=pd.itemcode and locstock.quantity>0
		WHERE grns.SupplierId = '" . $SelectedSupplier . "'
		".$whereSoloXReversar."
		AND qtyrecd>0
		";
    } else {
        $sql = "SELECT  grnno,
		po.orderno,
		grns.itemcode,
		grns.itemdescription,
		grns.deliverydate,
		qtyrecd,
		quantityinv,
		qtyrecd-quantityinv AS qtytoreverse,
		po.tagref,
		pd.factorconversion,
		po.realorderno
		FROM grns INNER JOIN purchorderdetails pd ON pd.podetailitem=grns.podetailitem
		INNER JOIN purchorders po ON po.orderno = pd.orderno
		WHERE grns.SupplierId = '" . $SelectedSupplier . "'
		".$whereSoloXReversar."
		AND qtyrecd>0
		";
    }
    //echo '<pre>sql:'.$sql;
    if (isset($ordenno) && !empty($ordenno)) {
        $sql .= " AND po.orderno = '" . $ordenno . "' ";
    }
            
    $sql .= " ORDER BY po.orderno,pd.podetailitem";
    //echo "<br><pre>sql:<br>".$sql;
    $ErrMsg = _('An error occurred in the attempt to get the outstanding GRNs for') . ' ' . $_POST['SuppName'] . '. ' . _('The message was') . ':';
    $DbgMsg = _('The SQL that failed was') . ':';
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    //echo $sql;
    if (DB_num_rows($result) ==0) {
        prnMsg(_('Hay productos que no se han recibido aún, no se ha facturado a ') . ' ' . $_POST['SuppName'] . '.', 'warn');
    } else if (empty($_GET['reversedatediff'])) {
        echo '<table class="table table-bordered">';
        $TableHeader = '<tr class="header-verde">
					<th>' . _('Orden') . '<br>' . _('Compra') . '</th>
					<th>' . _('Código') . '</th>
					<th>' . _('Descripción') . '</th>
					<th>' . _('Fecha') . '<br>' . _('Recepción') . '</th>
					<th>' . _('Cantidad') . '<br>' . _('Recibida') . '</th>
					<th>' . _('Cantidad') . '<br>' . _('Ordenada') . '</th>
					<th>' . _('Cantidad') . '<br>' . _('Rechazar') . '</th>
					<th></th>
					</tr>';

        echo $TableHeader;
        $RowCounter =0;
        $k=0;
                    $ListNoReg=0;
        while ($myrow=DB_fetch_array($result)) {
            $DisplayQtyRecd = $myrow['qtyrecd']* $myrow['factorconversion'];
            $DisplayQtyInv = $myrow['quantityinv']* $myrow['factorconversion'];
            $DisplayQtyRev = $myrow['qtytoreverse']* $myrow['factorconversion'];
            $tagref_=$myrow['tagref'];
            $DisplayDateDel = ConvertSQLDate($myrow['deliverydate']);
            //verifica que esta compra no haya generado una transferencia de almacen
            $elimina=true;
            /*$SQL="Select transferpurchno
                FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
                WHERE statustransfer<>'Cancel' and requisitionorderdetails.grnbatch='".$myrow['grnbatch']."' and transferrequistions.purchorderno='".$ordenno."' and itemcode='".$myrow['itemcode']."'";
            $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            //echo $SQL;//
            if (DB_num_rows($Result)>0){
                //echo "ENTRA";
                $LocQtyRow = DB_fetch_row($Result);
                $transferpurchno = $LocQtyRow[0];
                $SQL="Select * from loctransfers  where reference='".$transferpurchno."'";
                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                if (DB_num_rows($Result)>0){
                    $elimina=false;
                }
				
            }*/
            if ($DisplayQtyRev != 0) {
                $LinkToRevGRN='';
                $ListNoReg++;
                if ($elimina==true) {
                    if ($permiso_reversar_en_fecha_diferente and empty($_GET['reversedatediff'])) {
                        $LinkToRevGRN = "<a href=".$rootpath."/ReverseGRN.php?&link3&SelectedSupplier=".$SelectedSupplier."&ordenno=".$myrow['orderno']."&reversedatediff=yes" ."&pagina="."ReverseGRN.php?"."&grnno=".$myrow['grnno']."&selectedsupplier=".$SelectedSupplier."&tagref=".$tagref_."&ordenno=".$myrow['orderno']."&listnoreg=".$ListNoReg."&reversedatediff=true&DisplayDateDel=$DisplayDateDel>". _('Rechazar') . "</a>";
                    } else {
                        if (isset($_GET['SoloGRNXReversar'])) {
                            $pagina="ReverseGRN.php?SoloGRNXReversar=Y";//$_SERVER['PHP_SELF'] . '?' . SID ;
                            $GRNNo=$myrow['grnno'];
                            $LinkToRevGRN = "<a href=javascript:onclick=confirmar('".$pagina."','".$GRNNo."','".$SelectedSupplier."','".$ordenno."','".$ListNoReg."') >" . _('Rechazar') . "</a>";
                        } else {
                            $pagina="ReverseGRN.php?";
                            $GRNNo=$myrow['grnno'];
                            $LinkToRevGRN = "<a href=javascript:onclick=confirmar('".$pagina."','".$GRNNo."','".$SelectedSupplier."','".$tagref_."','".$ordenno."','".$ListNoReg."') >" . _('Rechazar 123') . "</a>";
                        }
                    }

                    $enc = new Encryption;
                    $url = "&GRNNo=>".$GRNNo."&SelectedSupplier=>".$SelectedSupplier."&tagref=>".$tagref_."&ordenno=>".$ordenno."&noline=>".$ListNoReg;
                    $url = $enc->encode($url);
                    $liga= "URL=" . $url;

                    $LinkToRevGRN = '<a type="button" id="enlaceReversar_'.$GRNNo.'" name="enlaceReversar_'.$GRNNo.'" href="ReverseGRN.php?'.$liga.'" title="Rechazar" style="display: none;"></a>';

                    // Se agrega para funcion de confirmacion
                    $LinkToRevGRN .= '<component-button type="button" id="btnReversar_'.$GRNNo.'" name="btnReversar_'.$GRNNo.'" value="Rechazar" onclick="fnConfirmacionReversa(\'enlaceReversar_'.$GRNNo.'\')"></component-button>';
                } else {
                    $LinkToRevGRN ='Genero Transferencia';
                }
                echo '<tr>';
                echo '<td>'.$myrow['realorderno'].'</td>';
                echo '<td>'.$myrow['itemcode'].'</td>';
                echo '<td>'.$myrow['itemdescription'].'</td>';
                echo '<td>'.$DisplayDateDel.'</td>';
                echo '<td>'.$DisplayQtyRecd.'</td>';
                echo '<td>'.$DisplayQtyInv.'</td>';
                echo '<td>'.$DisplayQtyRev.'</td>';
                echo '<td>'.$LinkToRevGRN.'</td>';
                echo '</tr>';

                $RowCounter++;
                if ($RowCounter >20) {
                    $RowCounter =0;
                    //echo $TableHeader;
                }
            }
        }
        echo '</table>';
    }
}

echo '</form>';

include('includes/footer_Index.inc');
?>
<script type="text/javascript">
function fnConfirmacionReversa(nombreLink) {
    //alert("nombreLink: "+nombreLink);
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    var mensaje = "Rechazar la recepción de productos";
    muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnEjecutarEnlaceReversa('"+nombreLink+"')");
}
function fnEjecutarEnlaceReversa(nombreLink) {
    //alert("nombreLink: "+nombreLink);
    var btnEnlaceRev = document.getElementById(nombreLink);
    btnEnlaceRev.click();
}
function confirmar(pagina,gnr,suplier,tag,ordenno,no)
{
    var commsg='';
    if (document.getElementById('ToMes')!==null) {
                    var noline;
                    noline=no;
                    var mes=document.getElementById('ToMes').value;
                    var meses=['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Junio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    commsg="con la fecha "+("0" + document.getElementById('ToDia').value).slice (-2)+' de '+meses[mes-1]+' de '+document.getElementById('ToYear').value;
    }
          
    if (confirm("Esta seguro de Reversar el Producto "+commsg+"?"))
    {       if (document.getElementById('ToMes')!==null) {
     
                document.location.href = pagina+"&link1&GRNNo="+gnr+"&SelectedSupplier="+suplier+"&tagref="+tag+"&ordenno="+ordenno+"&noline="+noline+"&reversedate="+("0"+ document.getElementById('ToDia').value).slice (-2)+'/'+("0" + document.getElementById('ToMes').value).slice (-2)+'/'+("0" + document.getElementById('ToYear').value).slice (-4);
            }
            else{
                document.location.href = pagina+"&GRNNo="+gnr+"&SelectedSupplier="+suplier+"&tagref="+tag+"&ordenno="+ordenno+"&noline="+noline;
            }
    }
    else
    {
            return ;
    }   
}

</script>