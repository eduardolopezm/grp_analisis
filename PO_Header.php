<?php


// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 4;
include "includes/SecurityUrl.php";
include 'includes/DefinePOClass.php';
include 'includes/session.inc';


//ModifyOrderNumber=868&idrequisicion=105
$title = _('Orden de Compra');
$funcion = 29;
include 'includes/header.inc';
include('javascripts/libreriasGrid.inc');
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$ocultaBuscarEntrada = ' style="display: none;" '; // style="display: none;"

if (empty($_GET['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_GET['identifier'];
}

$TieToOrderNumber= 0;

if (isset($_POST['TieToOrderNumber'])) {
    $TieToOrderNumber = $_POST['TieToOrderNumber'];
} else if (isset($_GET['TieToOrderNumber'])) {
    $TieToOrderNumber = $_GET['TieToOrderNumber'];
}

$panelCompraGen = 0;
if (isset($_GET['panelCompraGen'])) {
    $panelCompraGen = $_GET['panelCompraGen'];
}

if (!isset($_POST['Keywords'])) {
    $_POST['Keywords']= "";
}

if (!isset($_POST['SuppCode'])) {
    $_POST['SuppCode']= "";
}

if (!isset($_POST['SuppTaxid'])) {
    $_POST['SuppTaxid']= "";
}

if (isset($_GET['NewOrder']) and isset($_SESSION['PO' . $identifier])) {
    unset($_SESSION['PO' . $identifier]);
    $_SESSION['ExistingPurchOrder'] = 0;
} elseif (isset($_GET['NewOrder']) and !isset($_GET['TieToOrderNumber'])) {
    unset($_SESSION['loccode']);
}
//echo 'almacen:'.$_SESSION['loccode'];
if (isset($_GET['ModifyOrderNumber'])) {
} else {
}

if (isset($_POST['Select']) and empty($_POST['SupplierContact'])) {
    $sql = "SELECT contact
			FROM suppliercontacts
			WHERE supplierid='" . $_POST['Select'] . "'";

    $SuppCoResult = DB_query($sql, $db);
    if (DB_num_rows($SuppCoResult) > 0) {
        $myrow                    = DB_fetch_row($SuppCoResult);
        $_POST['SupplierContact'] = $myrow[0];
    } else {
        $_POST['SupplierContact'] = '';
    }
}
//}
if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
    $_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
    $_POST['Go']         = '';
}

if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset'] == 0) {
        $_POST['PageOffset'] = 1;
    }
}
if (isset($_SESSION['PO' . $identifier]->Orig_OrderDate)) {
    $_POST['fechacompra'] = $_SESSION['PO' . $identifier]->Orig_OrderDate;
}

/*if (isset($_POST['fechacompra'])){
$_POST['fechacompra']=$_POST['fechacompra'];
}else{
$_POST['fechacompra']=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
}
 */

# **********************************************************************
# ***** RECUPERA VALORES DE FECHAS *****

if (isset($_POST['FromYear'])) {
    $FromYear = $_POST['FromYear'];
} else {
    $FromYear = date('Y');
}
;

if (isset($_POST['FromMes'])) {
    $FromMes = $_POST['FromMes'];
} else {
    $FromMes = date('m');
}
;

if (isset($_POST['FromDia'])) {
    $FromDia = $_POST['FromDia'];
} else {
    $FromDia = date('d');
}
;

if (strlen($FromMes) == 1) {
    $FromMes = "0" . $FromMes;
}

if (strlen($FromDia) == 1) {
    $FromDia = "0" . $FromDia;
}

$_POST['fechacompra'] = $FromYear . "-" . $FromMes . "-" . $FromDia;

if (!isset($_POST['Initiator'])) {
    $_POST['Initiator']= "";
}

if (!isset($_POST['Requisition'])) {
    $_POST['Requisition']= "";
}

if (!isset($_POST['version'])) {
    $_POST['version']= "";
}

if (!isset($_POST['deliverydate'])) {
    $_POST['deliverydate']= "";
}

if (!isset($_POST['revised'])) {
    $_POST['revised']= "";
}

if (!isset($_POST['deliveryby'])) {
    $_POST['deliveryby']= "";
}

if (!isset($_POST['StatComments'])) {
    $_POST['StatComments']= "";
}

if (!isset($_POST['typeorder'])) {
    $_POST['typeorder']= "";
}

if (isset($_POST['UpdateStat']) and $_POST['UpdateStat'] != '') {
    /*The cancel button on the header screen - to delete order */
    $OK_to_updstat = 1;
    $OldStatus     = $_SESSION['PO' . $identifier]->Stat;
    $NewStatus     = $_POST['Stat'];
    $emailsql      = 'SELECT email FROM www_users WHERE userid="' . $_SESSION['PO' . $identifier]->Initiator . '"';
    $emailresult   = DB_query($emailsql, $db);
    $emailrow      = DB_fetch_array($emailresult);
    $date          = date($_SESSION['DefaultDateFormat']);
    if ($OldStatus != $NewStatus) {
        /* assume this in the first instance */
        $authsql = 'SELECT authlevel
			FROM purchorderauth
			WHERE userid="' . $_SESSION['UserID'] . '"
			AND currabrev="' . $_SESSION['PO' . $identifier]->CurrCode . '"';
//echo $authsql;
        $authresult     = DB_query($authsql, $db);
        $myrow          = DB_fetch_array($authresult);
        $AuthorityLevel = $myrow['authlevel'];
        $OrderTotal     = $_SESSION['PO' . $identifier]->Order_Value();
        if ($_POST['StatComments'] != '') {
            $_POST['StatComments'] = ' - ' . $_POST['StatComments'];
        }

        if ($NewStatus == _('Authorised')) {
            if ($AuthorityLevel > $OrderTotal) {
                $StatusComment = $date . ' - Authorised by <a href="mailto:' . $emailrow['email'] . '">' . $_SESSION['UserID'] .
                    '</a>' . $_POST['StatComments'] . '<br>' . $_POST['statcommentscomplete'];
                $_SESSION['PO' . $identifier]->StatComments = $StatusComment;
                $_SESSION['PO' . $identifier]->Stat         = $NewStatus;
            } else {
                $OK_to_updstat = 0;
                prnMsg(_('Usted no tiene permiso para autorizar esta orden de compra') . '.<br>' . _('Esta orden es para') . ' ' .
                    $_SESSION['PO' . $identifier]->CurrCode . ' ' . $OrderTotal . '. ' .
                    _('Solo se puede autorizar hasta') . ' ' . $_SESSION['PO' . $identifier]->CurrCode . ' ' . $AuthorityLevel . '.<br>' .
                    _('Si usted piensa que esto es un error, favor de ponerse en contacto con el administrador de sistemas'), 'warn');
            }
        }

        if ($NewStatus == _('Cancelled') and $OK_to_updstat == 1) {
            if ($AuthorityLevel > $OrderTotal or $_SESSION['UserID'] == $_SESSION['PO' . $identifier]->Initiator) {
                $StatusComment = $date . ' - Cancelled by <a href="mailto:' . $emailrow['email'] . '">' . $_SESSION['UserID'] .
                    '</a>' . $_POST['StatComments'] . '<br>' . $_POST['statcommentscomplete'];
                $_SESSION['PO' . $identifier]->StatComments = $StatusComment;
                $_SESSION['PO' . $identifier]->Stat         = $NewStatus;
            } else {
                $OK_to_updstat = 0;
                prnMsg(_('Usted no tiene permiso para cancelar esta orden de compra') . '.<br>' . _('Esta orden es para') . ' ' .
                    $_SESSION['PO' . $identifier]->CurrCode . ' ' . $OrderTotal . '. ' .
                    _('Su limite de autorizaci�n se establece en') . ' ' . $_SESSION['PO' . $identifier]->CurrCode . ' ' . $AuthorityLevel . '.<br>' .
                    _('Si usted piensa que esto es un error, favor de ponerse en contacto con el administrador de sistemas'), 'warn');
            }
        }

        if ($NewStatus == _('Rejected') and $OK_to_updstat == 1) {
            if ($AuthorityLevel > $OrderTotal) {
                $StatusComment = $date . ' - Rejected by <a href="mailto:' . $emailrow['email'] . '">' . $_SESSION['UserID'] .
                    '</a>' . $_POST['StatComments'] . '<br>' . $_POST['statcommentscomplete'];
                $_SESSION['PO' . $identifier]->StatComments = $StatusComment;
                $_SESSION['PO' . $identifier]->Stat         = $NewStatus;
            } else {
                $OK_to_updstat = 0;
                prnMsg(_('Usted no tiene permiso para rechazar esta orden de compra') . '.<br>' . _('Esta orden es para') . ' ' .
                    $_SESSION['PO' . $identifier]->CurrCode . ' ' . $OrderTotal . '. ' .
                    _('Su limite de autorizaci�n se establece en') . ' ' . $_SESSION['PO' . $identifier]->CurrCode . ' ' . $AuthorityLevel . '.<br>' .
                    _('Si usted piensa que esto es un error, favor de ponerse en contacto con el administrador de sistemas'), 'warn');
            }
        }

        if ($NewStatus == _('Pending') and $OK_to_updstat == 1) {
            if ($AuthorityLevel > $OrderTotal or $_SESSION['UserID'] == $_SESSION['PO' . $identifier]->Initiator) {
                $StatusComment = $date . ' - Returned to Pending status by <a href="mailto:' . $emailrow['email'] . '">' . $_SESSION['UserID'] .
                    '</a>' . $_POST['StatComments'] . '<br>' . $_POST['statcommentscomplete'];
                $_SESSION['PO' . $identifier]->StatComments = $StatusComment;
                $_SESSION['PO' . $identifier]->Stat         = $NewStatus;
            } else {
                $OK_to_updstat = 0;
                prnMsg(_('Usted no tiene permiso para cambiar el estado de esta orden de compra') . '.<br>' . _('Esta orden es para') . ' ' .
                    $_SESSION['PO' . $identifier]->CurrCode . ' ' . $OrderTotal . '. ' .
                    _('Su limite de autorizaci�n se establece en') . ' ' . $_SESSION['PO' . $identifier]->CurrCode . ' ' . $AuthorityLevel . '.<br>' .
                    _('Si usted piensa que esto es un error, favor de ponerse en contacto con el administrador de sistemas'), 'warn');
            }
        }

        if ($OK_to_updstat == 1) {
//            unset($_SESSION['PO'.$identifier]->LineItems);
            //            unset($_SESSION['PO'.$identifier]);
            //            $_SESSION['PO'.$identifier] = new PurchOrder;
            //            $_SESSION['RequireSupplierSelection'] = 1;

            if ($_SESSION['ExistingPurchOrder'] != 0) {
                $SQL = "UPDATE purchorders SET
				status='" . $_POST['Stat'] . "',
				stat_comment='" . $StatusComment . "'
				WHERE purchorders.orderno =" . $_SESSION['ExistingPurchOrder'];

                $ErrMsg    = _('El estado de la orden no se pudo actualizar porque');
                $DelResult = DB_query($SQL, $db, $ErrMsg);

//                $SQL = 'DELETE FROM purchorders WHERE purchorders.orderno=' . $_SESSION['ExistingPurchOrder'] ;
                //                $ErrMsg = _('The order header could not be deleted because');
                //                $DelResult=DB_query($SQL,$db,$ErrMsg);
            } else {
                prnMsg(_('Se trata de una nueva orden. Debe ser creada antes de que pueda cambiar el estado'), 'warn');
            }
        }
    }
}

if (isset($_GET['NewOrder']) and isset($_GET['StockID']) and isset($_GET['SelectedSupplier'])) {
    $_SESSION['ExistingPurchOrder'] = 0;
    unset($_SESSION['PO' . $identifier]);
    
    $_SESSION['PO' . $identifier] = new PurchOrder;
    
    $_SESSION['PO' . $identifier]->AllowPrintPO = 1;
     
    $_SESSION['PO' . $identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];
    
    $_SESSION['PO' . $identifier]->SupplierID = $_GET['SelectedSupplier'];
    
    $_SESSION['RequireSupplierSelection'] = 0;
    
    $_POST['Select'] = $_GET['SelectedSupplier'];

    $purch_item = $_GET['StockID'];
}

if (isset($_POST['EnterLines']) and $_POST['tag'] > 0) {
    $_SESSION['PO' . $identifier]->Location         = $_POST['StkLocation'];
    $_SESSION['PO' . $identifier]->SupplierContact  = $_POST['SupplierContact'];
    $_SESSION['PO' . $identifier]->DelAdd1          = $_POST['DelAdd1'];
    $_SESSION['PO' . $identifier]->DelAdd2          = $_POST['DelAdd2'];
    $_SESSION['PO' . $identifier]->DelAdd3          = $_POST['DelAdd3'];
    $_SESSION['PO' . $identifier]->DelAdd4          = $_POST['DelAdd4'];
    $_SESSION['PO' . $identifier]->DelAdd5          = $_POST['DelAdd5'];
    $_SESSION['PO' . $identifier]->DelAdd6          = $_POST['DelAdd6'];
    $_SESSION['PO' . $identifier]->Initiator        = $_POST['Initiator'];
    $_SESSION['PO' . $identifier]->RequisitionNo    = $_POST['Requisition'];
    $_SESSION['PO' . $identifier]->version          = $_POST['version'];
    $_SESSION['PO' . $identifier]->deliverydate     = $_POST['deliverydate'];
    $_SESSION['PO' . $identifier]->revised          = $_POST['revised'];
    $_SESSION['PO' . $identifier]->ExRate           = $_POST['ExRate'];
    $_SESSION['PO' . $identifier]->Comments         = $_POST['Comments'];
    $_SESSION['PO' . $identifier]->deliveryby       = $_POST['deliveryby'];
    $_SESSION['PO' . $identifier]->StatusMessage    = $_POST['StatComments'];
    $_SESSION['PO' . $identifier]->Orig_OrderDate   = $_POST['fechacompra'];
    $_SESSION['PO' . $identifier]->PorcDevTot       = $_POST['PorcDevTot'];
    $_SESSION['PO' . $identifier]->Typeorder        = $_POST['typeorder'];
    $_SESSION['PO' . $identifier]->contact          = $_POST['Contact'];
    $_SESSION['PO' . $identifier]->telephoneContact = $_POST['tel'];
    $_SESSION['PO' . $identifier]->tag              = $_POST['tag'];
    if ($_SESSION['PO' . $identifier]->CurrCode != "MXN") {
        $fecha                                = date("Y-m-d");
        $moneda                               = $_SESSION['PO' . $identifier]->CurrCode;
        $_SESSION['PO' . $identifier]->ExRate = GetCurrencyRateByDate($fecha, $moneda, $db);
    }
    if (isset($_POST['RePrint']) and $_POST['RePrint'] == 1) {
        $_SESSION['PO' . $identifier]->AllowPrintPO = 1;

        $sql = 'UPDATE purchorders
			SET purchorders.allowprint=1
			WHERE purchorders.orderno=' . $_SESSION['PO' . $identifier]->OrderNo;

        $ErrMsg       = _('An error occurred updating the purchase order to allow reprints') . '. ' . _('The error says');
        $updateResult = DB_query($sql, $db, $ErrMsg);
    }

    $enc = new Encryption;
    $url = "&identifier=>" . $identifier . "&supplierid=>" . $_POST['Select'] . "&TieToOrderNumber=>" . $TieToOrderNumber;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
    //$liga = "&identifier=" . $identifier . "&supplierid=" . $_POST['Select'] . "&TieToOrderNumber=" . $TieToOrderNumber;

    $_SESSION['PO'.$identifier]->LineItems = $_SESSION['LineItemsDatos'];
    $_SESSION['PO'.$identifier]->OrderNo = $_SESSION['OrderNoDatos'];
    $_SESSION['PO'.$identifier]->LinesOnOrder = $_SESSION['LinesOnOrderDatos'];
    unset($_SESSION['LineItemsDatos']);
    unset($_SESSION['OrderNoDatos']);
    unset($_SESSION['LinesOnOrderDatos']);

    echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/PO_Items.php?' . $liga . "'>";
    // echo '<p>';
    // prnMsg(_('Automaticamente deberan ser remitidas a la entrada de la pagina de elementos de linea de pedido') . '. ' .
    //     _('Si esto no sucede') . ' (' . _('si el navegador no soporta META actualice') . ') ' .
    //     "<a href='$rootpath/PO_Items.php?" . SID . "'>" . _('haga clic aqui') . '</a> ' . _('para continuar'), 'info');

    // echo '<script type="text/javascript">
    //         window.open("PO_Items.php?'.$liga.'");
    //     </script>';

    die();
} elseif (isset($_POST['EnterLines'])) {
    prnMsg(_('Favor de Seleccionar Unidad Resposable para Avanzar...'), 'info');
} /* end of if isset _POST'EnterLines' */

/*echo '<br><table align="left" width="385">
<tr>
<td style="text-align:right">';
echo '            <a href="'. $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . "identifier=".$identifier.'"><img src="images/b_regresar_25.png" height=20 title="Ir a Busqueda de Orden de Compra"></a><br>';
echo '        </td>
</tr>
</table><br>';*/
/*The page can be called with ModifyOrderNumber=x where x is a purchase
 * order number. The page then looks up the details of order x and allows
 * these details to be modified */

if (isset($_GET['ModifyOrderNumber'])) {
    include 'includes/PO_ReadInOrder.inc';

    $_SESSION['LineItemsDatos'] = $_SESSION['PO'.$identifier]->LineItems;
    $_SESSION['OrderNoDatos'] = $_SESSION['PO'.$identifier]->OrderNo;
    $_SESSION['LinesOnOrderDatos'] = $_SESSION['PO'.$identifier]->LinesOnOrder;

    $arrfecha = explode("/", $_SESSION['PO' . $identifier]->Orig_OrderDate);
    //echo $_SESSION['PO'.$identifier]->Orig_OrderDate;
    $FromDia  = $arrfecha[2];
    $FromMes  = $arrfecha[1];
    $FromAnio = $arrfecha[1];

    // redireccionamos a la pagina de poitems.php
    if ($_GET['back'] != 1 && $panelCompraGen == 0) {
        echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/PO_Items.php?' . SID . 'identifier=' . $identifier . "&supplierid=" . $_SESSION['PO' . $identifier]->SupplierID . "&TieToOrderNumber=" . $TieToOrderNumber . "'>";
        echo '<p>';
        prnMsg(_('Automaticamente deberan ser remitidas a la entrada de la pagina de elementos de linea de pedido') . '. ' . _('Si esto no sucede') . ' (' . _('si el navegador no soporta META actualice') . ') ' .
            "<a href='$rootpath/PO_Items.php?" . SID . "'>" . _('haga clic aqui') . '</a> ' . _('para continuar'), 'info');
        exit;
    }
}

if (isset($_POST['CancelOrder']) and $_POST['CancelOrder'] != '') {
    $OK_to_delete = 1;

    if (!isset($_SESSION['ExistingPurchOrder']) or $_SESSION['ExistingPurchOrder'] != 0) {
        if ($_SESSION['PO' . $identifier]->Any_Already_Received() == 1) {
            $OK_to_delete = 0;
            prnMsg(_('Este orden no se puede cancelar debido a que algunas ya se han recibido') . '. ' .
                _('Las cantidades de articulos de linea se pueden modificar') . '. ' .
                _('Los precios no pueden ser alterados por las l�neas que ya han sido recibidas') . ' ' .
                _('y las cantidades no se pueden reducir por debajo de la cantidad ya recibida'), 'warn');
        }
    }

    if ($OK_to_delete == 1) {
        $emailsql      = 'SELECT email FROM www_users WHERE userid="' . $_SESSION['PO' . $identifier]->Initiator . '"';
        $emailresult   = DB_query($emailsql, $db);
        $emailrow      = DB_fetch_array($emailresult);
        $StatusComment = date($_SESSION['DefaultDateFormat']) .
            ' - Order Cancelled by <a href="mailto:' . $emailrow['email'] . '">' . $_SESSION['UserID'] . '</a><br>' . $_POST['statcommentscomplete'];
        unset($_SESSION['PO' . $identifier]->LineItems);
        unset($_SESSION['PO' . $identifier]);
        $_SESSION['PO' . $identifier]         = new PurchOrder;
        $_SESSION['RequireSupplierSelection'] = 1;

        if ($_SESSION['ExistingPurchOrder'] != 0) {
            $sql = 'UPDATE purchorderdetails
				SET completed=1
				WHERE purchorderdetails.orderno =' . $_SESSION['ExistingPurchOrder'];
            $ErrMsg    = _('The order detail lines could not be deleted because');
            $DelResult = DB_query($sql, $db, $ErrMsg);

            $sql = "UPDATE purchorders
				SET status='Cancelled',
				stat_comment='" . $StatusComment . "'
				WHERE orderno=" . $_SESSION['ExistingPurchOrder'];

            $ErrMsg    = _('La cabecera de la orden no se pudo eliminar porque');
            $DelResult = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('Numero de orden') . ' ' . $_SESSION['ExistingPurchOrder'] . ' ' . _('ha sido cancelada'), 'success');
            unset($_SESSION['PO' . $identifier]);
            unset($_SESSION['ExistingPurchOrder']);
        } else {
            // Re-Direct to right place
            unset($_SESSION['PO' . $identifier]);
            prnMsg(_('La creacion de la nueva orden ha sido cancelada'), 'success');
        }
    }
}

if (!isset($_SESSION['PO' . $identifier])) {
    $_SESSION['ExistingPurchOrder']             = 0;
    $_SESSION['PO' . $identifier]               = new PurchOrder;
    $_SESSION['PO' . $identifier]->AllowPrintPO = 1;
    $_SESSION['PO' . $identifier]->GLLink       = $_SESSION['CompanyRecord']['gllink_stock'];

    if ($_SESSION['PO' . $identifier]->SupplierID == '' or !isset($_SESSION['PO' . $identifier]->SupplierID)) {
        $_SESSION['RequireSupplierSelection'] = 1;
    } else {
        $_SESSION['RequireSupplierSelection'] = 0;
    }
}

if (isset($_POST['ChangeSupplier'])) {
    if ($_SESSION['PO' . $identifier]->Stat == _('Pending') and $_SESSION['UserID'] == $_SESSION['PO' . $identifier]->Initiator) {
        if ($_SESSION['PO' . $identifier]->Any_Already_Received() == 0) {
            $emailsql                             = 'SELECT email FROM www_users WHERE userid="' . $_SESSION['PO' . $identifier]->Initiator . '"';
            $emailresult                          = DB_query($emailsql, $db);
            $emailrow                             = DB_fetch_array($emailresult);
            $date                                 = date($_SESSION['DefaultDateFormat']);
            $_SESSION['RequireSupplierSelection'] = 1;
            $_SESSION['PO' . $identifier]->Stat   = _('Pending');
            $StatusComment                        = $date . ' - Supplier changed by <a href="mailto:' . $emailrow['email'] . '">' . $_SESSION['UserID'] .
                '</a> - ' . $_POST['StatComments'] . '<br>' . $_POST['statcommentscomplete'];
            $_SESSION['PO' . $identifier]->StatComments = $StatusComment;
        } else {
            echo '<br><br>';
            prnMsg(_('No se puede modificar el proveedor de la orden una vez que la orden ha sido recibida'), 'warn');
        }
    }
}

$msg = '';
if (isset($_POST['SearchSuppliers']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
    if (strlen($_POST['Keywords']) > 0 and strlen($_SESSION['PO' . $identifier]->SupplierID) > 0) {
        $msg = _('Supplier name keywords have been used in preference to the supplier code extract entered');
    }
    if ($_POST['Keywords'] == '' and $_POST['SuppCode'] == '' and strlen($_POST['SuppTaxid']) == 0) {
        $msg = _('No ha propocionado criterios de consulta para mostrar proveedores');
    } else {
        if (strlen($_POST['Keywords']) > 0) {
            $i            = 0;
            $SearchString = '%';
            while (strpos($_POST['Keywords'], ' ', $i)) {
                $wrdlen       = strpos($_POST['Keywords'], ' ', $i) - $i;
                $SearchString = $SearchString . substr($_POST['Keywords'], $i, $wrdlen) . '%';
                $i            = strpos($_POST['Keywords'], ' ', $i) + 1;
            }
            $SearchString = $SearchString . substr($_POST['Keywords'], $i) . '%';
            $SQL          = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode,
					suppliers.taxid
				FROM suppliers INNER JOIN supplierstype ON supplierstype.typeid=suppliers.typeid
					       INNER JOIN sec_supplierxuser ON sec_supplierxuser.typeid=supplierstype.typeid AND sec_supplierxuser.userid='" . $_SESSION['UserID'] . "'
				WHERE suppliers.suppname " . LIKE . " '$SearchString'
				ORDER BY suppliers.suppname";
        } elseif (strlen($_POST['SuppCode']) > 0) {
            $SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode,
					suppliers.taxid
				FROM suppliers INNER JOIN supplierstype ON supplierstype.typeid=suppliers.typeid
					       INNER JOIN sec_supplierxuser ON sec_supplierxuser.typeid=supplierstype.typeid AND sec_supplierxuser.userid='" . $_SESSION['UserID'] . "'
				WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SuppCode'] . "%'
				ORDER BY suppliers.supplierid";
        } elseif (strlen($_POST['SuppTaxid']) > 0) {
            $SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode,
					suppliers.taxid
				FROM suppliers INNER JOIN supplierstype ON supplierstype.typeid=suppliers.typeid
					       INNER JOIN sec_supplierxuser ON sec_supplierxuser.typeid=supplierstype.typeid AND sec_supplierxuser.userid='" . $_SESSION['UserID'] . "'
				WHERE suppliers.taxid " . LIKE . " '%" . $_POST['SuppTaxid'] . "%'
				ORDER BY suppliers.supplierid";
        }
        $ErrMsg = _('The searched supplier records requested cannot be retrieved because');
        if ($_SESSION['UserID'] == 'desarrollo') {
            //echo "<br><pre>$SQL";
        }
        $result_SuppSelect = DB_query($SQL, $db, $ErrMsg);

        if (DB_num_rows($result_SuppSelect) == 1) {
            $myrow           = DB_fetch_array($result_SuppSelect);
            $_POST['Select'] = $myrow['supplierid'];
        } elseif (DB_num_rows($result_SuppSelect) == 0) {
            prnMsg(_('No hay registros de proveedores en 	el texto seleccionado') . ' - ' .
                _('por favor modifique su criterio de busqueda e intentelo de nuevo'), 'info');
        }
    }
}

if ((!isset($_POST['SearchSuppliers']) or $_POST['SearchSuppliers'] == '') and
    (isset($_SESSION['PO' . $identifier]->SupplierID) and $_SESSION['PO' . $identifier]->SupplierID != '')) {
    $_POST['SupplierID']   = $_SESSION['PO' . $identifier]->SupplierID;
    $_POST['SupplierName'] = $_SESSION['PO' . $identifier]->SupplierName;
    $_POST['CurrCode']     = $_SESSION['PO' . $identifier]->CurrCode;
    $_POST['ExRate']       = $_SESSION['PO' . $identifier]->ExRate;
    $_POST['paymentterms'] = $_SESSION['PO' . $identifier]->paymentterms;
    $_POST['DelAdd1']      = $_SESSION['PO' . $identifier]->DelAdd1;
    $_POST['DelAdd2']      = $_SESSION['PO' . $identifier]->DelAdd2;
    $_POST['DelAdd3']      = $_SESSION['PO' . $identifier]->DelAdd3;
    $_POST['DelAdd4']      = $_SESSION['PO' . $identifier]->DelAdd4;
    $_POST['DelAdd5']      = $_SESSION['PO' . $identifier]->DelAdd5;
    $_POST['DelAdd6']      = $_SESSION['PO' . $identifier]->DelAdd6;
}

$from= "";

if (isset($_GET['from'])) {
    $from = $_GET['from'];
}

if ($from != "") {
    $_POST['Select'] = $_GET['Select'];
    //echo  $_POST['Select'];
}

if (isset($_POST['Select'])) {
    $sql = 'SELECT currcode
			FROM suppliers
			where supplierid="' . $_POST['Select'] . '"
					and active = 1';
    $result           = DB_query($sql, $db);
    $myrow            = DB_fetch_array($result);
    $SupplierCurrCode = $myrow['currcode'];

    $authsql = 'SELECT cancreate
			FROM purchorderauth
			WHERE userid="' . $_SESSION['UserID'] . '"
			AND currabrev="' . $SupplierCurrCode . '"';

    $authresult = DB_query($authsql, $db);

    $sql = "SELECT suppliers.suppname,
		suppliers.currcode,
		currencies.rate,
		suppliers.paymentterms,
                        suppliers.address1,
                        suppliers.address2,
                        suppliers.address3,
                        suppliers.address4,
                        suppliers.address5,
                        suppliers.address6,
                        suppliers.phn,
                        suppliers.port
		FROM suppliers INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE supplierid='" . $_POST['Select'] . "'";

    $ErrMsg = _('El registro del proveedor seleccionado') . ': ' . $_POST['Select'] . ' ' .
    _('no puede ser recuperada porque');
    $DbgMsg       = _('El SQL utilizado para recuperar los detalles del proveedor no fue');
    $result       = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrow        = DB_fetch_row($result);
    $SupplierName = $myrow[0];

    if ($authmyrow = DB_fetch_array($authresult) and $authmyrow[0] == 0) {
        $_POST['SupplierName'] = $myrow[0];
        $_POST['CurrCode']     = $myrow[1];
        $_POST['ExRate']       = $myrow[2];
        $_POST['paymentterms'] = $myrow[3];
        $_POST['suppDelAdd1']  = $myrow[4];
        $_POST['suppDelAdd2']  = $myrow[5];
        $_POST['suppDelAdd3']  = $myrow[6];
        $_POST['suppDelAdd4']  = $myrow[7];
        $_POST['suppDelAdd5']  = $myrow[8];
        $_POST['suppDelAdd6']  = $myrow[9];
        $_POST['supptel']      = $myrow[10];
        $_POST['port']         = $myrow[11];

        $_SESSION['PO' . $identifier]->SupplierID   = $_POST['Select'];
        $_SESSION['RequireSupplierSelection']       = 0;
        $_SESSION['PO' . $identifier]->SupplierName = $_POST['SupplierName'];

        if ($_SESSION['PO' . $identifier]->CurrCode == "") {
            $_SESSION['PO' . $identifier]->CurrCode = $_POST['CurrCode'];
        }

        $_SESSION['PO' . $identifier]->ExRate       = $_POST['ExRate'];
        $_SESSION['PO' . $identifier]->paymentterms = $_POST['paymentterms'];
        $_SESSION['PO' . $identifier]->suppDelAdd1  = $_POST['suppDelAdd1'];
        $_SESSION['PO' . $identifier]->suppDelAdd2  = $_POST['suppDelAdd2'];
        $_SESSION['PO' . $identifier]->suppDelAdd3  = $_POST['suppDelAdd3'];
        $_SESSION['PO' . $identifier]->suppDelAdd4  = $_POST['suppDelAdd4'];
        $_SESSION['PO' . $identifier]->suppDelAdd5  = $_POST['suppDelAdd5'];
        $_SESSION['PO' . $identifier]->suppDelAdd6  = $_POST['suppDelAdd6'];
        $_SESSION['PO' . $identifier]->supptel      = $_POST['supptel'];
        $_SESSION['PO' . $identifier]->port         = $_POST['port'];
    } else {
        prnMsg(_('Usted NO tiene la autoridad para generar Ordenes de Compra de ') .
            '<br>' . $SupplierName . '. ' . _('Por favor, consulte a su administrador del sistema para obtener mas informacion') . '. '
            . "<br>" . _('Puede configurar las autorizaciones ') . '<a href=PO_AuthorisationLevels.php>Presionando Esta Liga: </a>', 'warn');
        include 'includes/footer_Index.inc';
        exit;
    }
} else {
    $_POST['Select'] = $_SESSION['PO' . $identifier]->SupplierID;
    $sql             = "SELECT suppliers.suppname,
			suppliers.currcode,
			suppliers.paymentterms,
			suppliers.address1,
			suppliers.address2,
			suppliers.address3,
			suppliers.address4,
			suppliers.address5,
			suppliers.address6,
			suppliers.phn,
			suppliers.port
		FROM suppliers INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE supplierid='" . $_POST['Select'] . "'";

    $ErrMsg = _('El registro del proveedor seleccionado') . ': ' . $_POST['Select'] . ' ' .
    _('no puede ser recuperada porque');
    $DbgMsg = _('El SQL utilizado para recuperar los detalles del proveedor no fue');
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

    $myrow = DB_fetch_row($result);

    $_POST['SupplierName'] = $myrow[0];
    $_POST['CurrCode']     = $myrow[1];
    $_POST['paymentterms'] = $myrow[2];
    $_POST['suppDelAdd1']  = $myrow[3];
    $_POST['suppDelAdd2']  = $myrow[4];
    $_POST['suppDelAdd3']  = $myrow[5];
    $_POST['suppDelAdd4']  = $myrow[6];
    $_POST['suppDelAdd5']  = $myrow[7];
    $_POST['suppDelAdd6']  = $myrow[8];
    $_POST['supptel']      = $myrow[9];
    $_POST['port']         = $myrow[10];

    if (!isset($_POST['ExRate'])) {
        $_POST['ExRate']= 0;
    }

    if (!isset($_POST['tag'])) {
        $_POST['tag']= "";
    }

    $_SESSION['PO' . $identifier]->SupplierID   = $_POST['Select'];
    $_SESSION['RequireSupplierSelection']       = 0;
    $_SESSION['PO' . $identifier]->SupplierName = $_POST['SupplierName'];

    if ($_SESSION['PO' . $identifier]->CurrCode == "") {
        $_SESSION['PO' . $identifier]->CurrCode = $_POST['CurrCode'];
    }

    $_SESSION['PO' . $identifier]->ExRate       = $_POST['ExRate'];
    $_SESSION['PO' . $identifier]->paymentterms = $_POST['paymentterms'];
    $_SESSION['PO' . $identifier]->suppDelAdd1  = $_POST['suppDelAdd1'];
    $_SESSION['PO' . $identifier]->suppDelAdd2  = $_POST['suppDelAdd2'];
    $_SESSION['PO' . $identifier]->suppDelAdd3  = $_POST['suppDelAdd3'];
    $_SESSION['PO' . $identifier]->suppDelAdd4  = $_POST['suppDelAdd4'];
    $_SESSION['PO' . $identifier]->suppDelAdd5  = $_POST['suppDelAdd5'];
    $_SESSION['PO' . $identifier]->suppDelAdd6  = $_POST['suppDelAdd6'];
    $_SESSION['PO' . $identifier]->supptel      = $_POST['supptel'];
    $_SESSION['PO' . $identifier]->port         = $_POST['port'];
    $_SESSION['PO' . $identifier]->tag          = $_POST['tag'];
}

//echo $_SESSION['RequireSupplierSelection'] . "<br>";
//echo "ddff" . !isset($_SESSION['PO'.$identifier]->SupplierID) . "<br>";
//echo "dd" . $_SESSION['PO'.$identifier]->SupplierID;

if ((($from == "") && ($_SESSION['RequireSupplierSelection'] == 1 or !isset($_SESSION['PO' . $identifier]->SupplierID) or
    $_SESSION['PO' . $identifier]->SupplierID == '')) || $panelCompraGen == 1) {
//if (true) {
    $enc = new Encryption;
    $url = "&identifier=>" . $identifier;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;

    echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . $liga . "' method=post name='choosesupplier'>";
    echo "<input type=hidden name='TieToOrderNumber' value=" . $TieToOrderNumber . ">";
    if (strlen($msg) > 1) {
        prnMsg($msg, 'warn');
    }

    ?>
    <!--<table align=left width=50%>
            <tr>
                <td class="fecha_titulo">
                    <img src="images/proveedor_bus_30.png" title="' . _('Orden de Compra') . '" alt="">Ordenes de Compra: Selecciona Proveedor
            </td>
            </tr>
          </table>

          <br>-->
<script type="text/javascript" src="javascripts/PO_Compra.js"></script>
<div id="lblMensajeRFC" name="lblMensajeRFC"></div>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title text-left"><b>Buscar proveedor para Orden de Compra</b></h3>
        </div>
        <div class="panel-body">
            <div class="col-md-6 text-left">
                <label>&nbsp;Nombre o Dependencia:</label>
                <component-text id="Keywords" name="Keywords" placeholder="Nombre Proveedor" value="<?php echo $_POST['Keywords']; ?>"></component-text>
            </div>
            <div class="col-md-2 text-left">
                <label>&nbsp;Código Proveedor:</label>
                <component-text id="SuppCode" name="SuppCode" placeholder="Código Proveedor" value="<?php echo $_POST['SuppCode']; ?>"></component-text>
            </div>
            <div class="col-md-2 text-left">
                <label>&nbsp;RFC:</label>
                <component-text id="SuppTaxid" name="SuppTaxid" placeholder="RFC" maxlength="13" value="<?php echo $_POST['SuppTaxid']; ?>"></component-text>
            </div>
            <div class="col-md-2">
                <br>
                <component-button onclick="fnMuestraProveedores()" id="SearchSuppliers" name="SearchSuppliers" value="Buscar" class="glyphicon glyphicon-search"></component-button>
            </div>
        </div>
    </div>
    <div id="datosProveedor" class="hide">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingTwo">
          <h4 class="panel-title row">
            <div class="col-md-6 col-xs-6 text-left">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelDatosPorveedor" aria-expanded="true" aria-controls="collapseOne">
                <b>Datos de proveedor</b>
              </a>
            </div>
          </h4>
        </div>
        <div id="PanelDatosPorveedor" name="PanelDatosPorveedor" class="panel-collapse collapse in p5" role="tabpanel" aria-labelledby="headingTwo">
          <div name="divTabla2" id="divTabla2" class="hide">
              <div name="divProveedorTabla" id="divProveedorTabla"></div>
          </div>
        </div>
      </div>
    </div>
</div>

<?php

    //echo '<script  type="text/javascript">defaultControl(document.forms[0].Keywords);</script>';

if (isset($result_SuppSelect) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
    $ListCount   = DB_num_rows($result_SuppSelect);
    $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
    if (isset($_POST['Next'])) {
        if ($_POST['PageOffset'] < $ListPageMax) {
            $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
        }
    }

    if (isset($_POST['Previous'])) {
        if ($_POST['PageOffset'] > 1) {
            $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
        }
    }

    echo "<input type=\"hidden\" name=\"PageOffset\" VALUE=\"" . $_POST['PageOffset'] . "\"/>";

    if ($ListPageMax > 1) {
        echo "<p><div class=centre>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ': ';

        echo '<select name="PageOffset1" id="PageOffset1" class="PageOffset1">';

        $ListPage = 1;
        while ($ListPage <= $ListPageMax) {
            if ($ListPage == $_POST['PageOffset']) {
                echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
            } else {
                echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
            }
            $ListPage++;
        }
        echo '</select>';
        // echo '<input type=submit name="Go1" VALUE="' . _('Ir') . '">
        // <input type=submit name="Previous" VALUE="' . _('Anterior') . '">
        // <input type=submit name="Next" VALUE="' . _('Siguiente') . '">';
        ?>
        <component-button type="submit" id="Go1" name="Go1" value="Ir"></component-button>
        <component-button type="submit" id="Previous" name="Previous" value="Anterior"></component-button>
        <component-button type="submit" id="Next" name="Next" value="Siguiente"></component-button>
        <?php
        echo '</div>';
    }

    echo '<br>';

    echo '<table class="table table-bordered" colspan=7>';
    $tableheader = "<tr class='header-verde'>
				<th>" . _('#') . "</th>
				<th>" . _('Código') . "</th>
				<th>" . _('Nombre') . "</th>
				<th>" . _('RFC') . "</th>
				<th>" . _('Dirección') . "</th>
				<th>" . _('Moneda') . '</th>
				</tr>';

    echo $tableheader;

    $j    = 1;
    $k    = 0;
    $cont = 0;
    $RowIndex= 0;

    //if($myrow1['active'] == 1){

    DB_data_seek($result_SuppSelect, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);

    while (($myrow = DB_fetch_array($result_SuppSelect)) and ($RowIndex != $_SESSION['DisplayRecordsMax'])) {
        $cont = $cont + 1;
        if ($k == 1) {
            echo '<tr>';
            $k = 0;
        } else {
            echo '<tr>';
            $k++;
        }
        echo "<td>" . $cont . "</td>
            <td>
                <input type='submit'  name='Select' id='Select' class='btn btn-default botonVerde' value='" . $myrow['supplierid'] . "' >
            </td>
			<td>" . $myrow['suppname'] . "</td>
			<td>" . $myrow['taxid'] . "</td><td>";

        for ($i = 1; $i <= 6; $i++) {
            if ($myrow['address' . $i] != '') {
                echo $myrow['address' . $i] . '<br>';
            }
        }
        echo "</td>
        <td>" . $myrow['currcode'] . "</td></tr>";

        $RowIndex++;
    }

    echo '</table>';
}
} else {
    $enc = new Encryption;
    $url = "&identifier=>" . $identifier;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;

    echo "<form name='form1' action='" . $_SERVER['PHP_SELF'] . '?' . $liga . "' method=post>";
    echo "<input type='hidden' name=Select value='" . $_POST['Select'] . "'>";
    echo "<input type=hidden name='TieToOrderNumber' value=" . $TieToOrderNumber . ">";

    $SQL = "SELECT supplierid, suppname, address1, address2, address3, address4,p.terms,taxid FROM suppliers s, paymentterms p WHERE p.termsindicator=s.paymentterms AND supplierid='" . $_POST['Select'] . "'";
    //echo $SQL;
    $ErrMsg = _('El nombre del proveedor solicitado no puede ser recuperado porque');
    $result = DB_query($SQL, $db, $ErrMsg);

    if ($myrow = DB_fetch_row($result)) {
        $SuppName     = $myrow[1];
        $SupplierName = $myrow[0] . ' - ' . $myrow[7] . ' - ' . $myrow[1];
        $address      = $myrow[2] . ' ' . $myrow[3] . ' ' . $myrow[4] . ' ' . $myrow[5];
        $terminospago = $myrow[6];
    }
    unset($result);

    if ($_SESSION['ExistingPurchOrder']) {
        //echo '<tr><td valign="top"><b>';
        //echo _(' Modificar Orden de Compra No.') . ' ' . $_SESSION['PO' . $identifier]->OrderNo . '<br>';
        //echo '</td></tr>';
    }

    if (isset($purch_item)) {
        prnMsg(_('Compra de articulo(s) con este código') . ': ' . $purch_item, 'info');
        echo "<div class='centre'>";
        echo '<br><table class="table_index"><tr><td class="menu_group_item">';

        echo '<li><a href="' . $rootpath . '/PO_Items.php?' . SID . 'NewItem=' . $purch_item . "?identifier=" . $identifier . '">' .
        _('Enter Line Item to this purchase order') . '</a></li>';
        echo "</td></tr></table></div><br>";
    }

    if (!isset($_POST['LookupDeliveryAddress']) and (!isset($_POST['StkLocation']) or $_POST['StkLocation'])
        and (isset($_SESSION['PO' . $identifier]->Location) and $_SESSION['PO' . $identifier]->Location != '')) {
        $_POST['StkLocation']     = $_SESSION['PO' . $identifier]->Location;
        $_POST['SupplierContact'] = $_SESSION['PO' . $identifier]->SupplierContact;
        $_POST['DelAdd1']         = $_SESSION['PO' . $identifier]->DelAdd1;
        $_POST['DelAdd2']         = $_SESSION['PO' . $identifier]->DelAdd2;
        $_POST['DelAdd3']         = $_SESSION['PO' . $identifier]->DelAdd3;
        $_POST['DelAdd4']         = $_SESSION['PO' . $identifier]->DelAdd4;
        $_POST['DelAdd5']         = $_SESSION['PO' . $identifier]->DelAdd5;
        $_POST['DelAdd6']         = $_SESSION['PO' . $identifier]->DelAdd6;
        $_POST['Initiator']       = $_SESSION['PO' . $identifier]->Initiator;
        $_POST['Requisition']     = $_SESSION['PO' . $identifier]->RequisitionNo;
        $_POST['version']         = $_SESSION['PO' . $identifier]->version;
        $_POST['deliverydate']    = $_SESSION['PO' . $identifier]->deliverydate;
        $_POST['revised']         = $_SESSION['PO' . $identifier]->revised;
        $_POST['ExRate']          = $_SESSION['PO' . $identifier]->ExRate;
        $_POST['Comments']        = $_SESSION['PO' . $identifier]->Comments;
        $_POST['deliveryby']      = $_SESSION['PO' . $identifier]->deliveryby;
        $_POST['tag']             = $_SESSION['PO' . $identifier]->tag;
    }

    /* AQUI VA EL CODIGO DE DETALLES DE LA ORDEN DE COMPRA */
    /*******************************************************/

    echo '<tr><td>';

    echo '<div class="container">
            <div class="panel panel-default col-lg-12 col-md-12 col-sm-12 p0 m0">
            <div class="panel-heading" role="tab" id="headingOne">
              <h4 class="panel-title row">
                <div class="col-md-6 col-xs-6 text-left">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelDatos" aria-expanded="true" aria-controls="collapseOne">
                    <b>Información del proveedor</b>
                  </a>
                </div>
              </h4>
            </div>
            <div id="PanelDatos" name="PanelDatos" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">';
            echo '<component-label-text label="Proveedor:" value="'.$SupplierName.'" style="text-align: left;"></component-label-text>';
            echo '<component-label-text label="Direcci&oacute;n:" value="'.$address.'" style="text-align: left;"></component-label-text>';
            echo '<component-label-text label="Términos de pago:" value="'.$terminospago.'" style="text-align: left;"></component-label-text>';

            /*echo '<table class="table">';
            echo '<tr><td nowrap>' . _(' Proveedor') . ':</td><td>' . $SupplierName . '</td></tr>';
            echo '<tr><td nowrap>' . _(' Direccion') . ':</td><td>' . $address . '</td></tr>';
            echo '<tr><td nowrap>' . _(' Terminos de pago') . ':</td><td>' . $terminospago . '</td></tr>';
            /*$ssql = 'Select sum(ovamount+ovgst)';
            $ssql .= ' FROM supptrans';
            $ssql .= ' WHERE supplierno="' . $_POST['Select'] . '"';

            $result = DB_query($ssql, $db, $ErrMsg);
            $myrow  = DB_fetch_array($result);
            
            $saldo  = $myrow[0];

            echo '<tr>';
            echo '<td style="text-align:right" nowrap class="titulos_principales2">';
            echo 'Saldo:';
            echo '</td>';
            echo '<td class="texto_normal3">$' . number_format($saldo, 2) . '</td>';
            echo '</tr>';
            echo '</table>*/
            echo '</div>
        </div>
    </div>';

    echo "<div class='col-lg-12 col-md-12 col-sm-12 p0 m0'>&nbsp;</div>";

    echo '<div class="panel panel-default pull-right col-lg-12 col-md-12 col-sm-12 p0 m0">
            <div class="panel-heading" role="tab" id="headingOne">
              <h4 class="panel-title row">
                <div class="col-md-6 col-xs-6 text-left">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAlmacenEntrega" aria-expanded="true" aria-controls="collapseOne">
                    <b>Datos de Entrega</b>
                  </a>
                </div>
              </h4>
            </div>
            <div id="PanelAlmacenEntrega" name="PanelAlmacenEntrega" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">';
                
                //echo '<table class="table">';

                $typeSelButtonInPOHeader = $_SESSION['typeSelButtonInPOHeader'];

    if (empty($TieToOrderNumber) == false && empty($_POST['flagAutoSelectOff'])) {
        $sql = "SELECT salesorders.fromstkloc, tags.tagref, tags.areacode 
                            FROM salesorders
                            INNER JOIN tags ON tags.tagref = salesorders.tagref WHERE orderno = '$TieToOrderNumber'";
                    
        $rs = DB_query($sql, $db);

        // echo $sql;
        if ($row = DB_fetch_array($rs)) {
            $_POST['area']        = $row["areacode"];
            $_POST['StkLocation'] = $row["fromstkloc"];
            $_POST['tag']         = $row["tagref"];
            echo "<input type='hidden' name='flagAutoSelectOff' value='1' />";
        }
    }

                $SQL = "SELECT distinct areas.areacode,areas.areadescription ";
                $SQL = $SQL . " FROM areas inner join tags t on t.areacode=areas.areacode, sec_unegsxuser u ";
                $SQL = $SQL . " WHERE u.tagref = t.tagref ";
                $SQL = $SQL . " and t.tagref in (select tagref from locations where temploc in (0,5)) ";

                $SQL = $SQL . " and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY areas.areadescription";
                
    if (!isset($_POST['area'])) {
        $_POST['area']= "";
    }

                $resultarea = DB_query($SQL, $db);
                            
                echo "<select name='area' style='font-size:8pt; display: none;'>";

    while ($myrowarea = DB_fetch_array($resultarea)) {
        if ($_POST['area'] == $myrowarea['areacode']) {
            echo '<option selected value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'] . '</option>';
        } else {
            echo '<option  value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'] . '</option>';
        }
    }

                echo '</select>&nbsp;&nbsp;';
                echo '<input type="' . $typeSelButtonInPOHeader . '" value="->" name="btnArea" style="display: none;">';

                $wcond = "";
    if ($_POST['area']) {
        $wcond = "AND t.areacode = '" . $_POST['area'] . "'";
    }
    $sqlWhere = "";
    if (!empty($_SESSION['PO' . $identifier]->tag) && $_SESSION['PO' . $identifier]->tag != '-1') {
        $sqlWhere = " AND t.tagref= '".$_SESSION['PO' . $identifier]->tag."' ";
    }

                $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
                FROM sec_unegsxuser u, tags t 
                WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere."
                ORDER BY t.tagref ";
                
                echo '<div class="col-md-4">
                        <div class="form-inline row">
                          <div class="col-md-3" style="vertical-align: middle;">
                              <span><label>UR: </label></span>
                          </div>
                          <div class="col-md-9">
                            <select id="tag" name="tag" class="form-control" onchange=mysubmit("")>';
    if (empty($_SESSION['PO' . $identifier]->tag) || $_SESSION['PO' . $identifier]->tag == '-1') {
        echo '<option value="-1">Seleccionar...</option>';
    }
    $result = DB_query($SQL, $db);
    while ($myrow = DB_fetch_array($result)) {
        if (isset($_POST['tag']) and $_POST['tag'] == $myrow['tagref']) {
            echo '<option selected value=' . $myrow['tagref'] . '>' . ($myrow['tagdescription']) . "</option>";
        } else {
            echo '<option value=' . $myrow['tagref'] . '>' . ($myrow['tagdescription']) . "</option>";
        }
    }
                        echo '</select>';
                    echo "</div>";
                echo '</div>
                    </div>';

                /*echo '<tr><td class="texto_lista">' . _('Unidad de Negocio') . ':</td><td><select name="tag" onchange=mysubmit("")>';
                echo '<option selected value="0">Seleccionar una Unidad de Negocio</option>';

                $result = DB_query($SQL, $db);

                while ($myrow = DB_fetch_array($result)) {
                    if (isset($_POST['tag']) and $_POST['tag'] == $myrow['tagref']) {
                        echo '<option selected value=' . $myrow['tagref'] . '>' . utf8_encode($myrow['tagdescription']) . "</option>";
                    } else {
                        echo '<option value=' . $myrow['tagref'] . '>' . utf8_encode($myrow['tagdescription']) . "</option>";
                    }
                }
                echo '</select>';
                //echo '<input type="' . $typeSelButtonInPOHeader . '" value="->" name="btnTag"></td>';
                echo '</tr>';*/

                $wcond = "";

    if (isset($_SESSION['PO' . $identifier]->tag)) {
        $wcond .= " AND l.tagref= '" . $_SESSION['PO' . $identifier]->tag . "'";
    }

    if (isset($_POST['area'])) {
        //$wcond .= " AND l.areacod = '" . $_POST['area'] . "'";
    }


                $sql = "SELECT l.loccode,
                             l.locationname
                         FROM  sec_loccxusser uxu ,locations l
                         WHERE l.loccode=uxu.loccode 
                        AND uxu.userid='" . $_SESSION['UserID'] . "'";
                $sql = $sql . "ORDER BY l.locationname ";

                echo '<div class="col-md-4">
                        <div class="form-inline row">
                          <div class="col-md-3" style="vertical-align: middle;">
                              <span><label>Almacén: </label></span>
                          </div>
                          <div class="col-md-9">
                            <select name="StkLocation" id="StkLocation" onChange="ReloadForm(form1.LookupDeliveryAddress)" class="StkLocation">';
                                $LocnResult = DB_query($sql, $db);

    $primero= true;
    $almacen= "";

    while ($LocnRow = DB_fetch_array($LocnResult)) {
        if ($primero) {
            $almacen= $LocnRow['loccode'];
            $primero= false;
        }

        if (isset($_POST['StkLocation']) and ($_POST['StkLocation'] == $LocnRow['loccode'] or
            ($_POST['StkLocation'] == '' and $LocnRow['loccode'] == $_SESSION['UserStockLocation']))) {
            echo "<option selected value='" . $LocnRow['loccode'] . "'>" . ($LocnRow['locationname']);
        } else {
            echo "<option value='" . $LocnRow['loccode'] . "'>" . ($LocnRow['locationname']);
        }
    }
                        echo '</select>';
                    echo "</div>";
                echo '</div>
                    </div>';

                echo '<div class="col-md-4">';
                    echo '<input class="btn btn-default botonVerde" type="submit" name="LookupDeliveryAddress" id="LookupDeliveryAddress" value="' . _('Refresca') . '">';
                echo '</div>';

            /* If this is the first time
             * the form loaded set up defaults */

    if (!isset($_POST['StkLocation'])) {
        $_POST['StkLocation'] = $almacen;

        $sql = "SELECT deladd1,
                            deladd2,
                            deladd3,
                            deladd4,
                            deladd5,
                            deladd6,
                            tel,
                            contact
                        FROM locations l, sec_loccxusser lxu
                        WHERE l.loccode=lxu.loccode
                        AND userid='" . $_SESSION['UserID'] . "'
                        AND l.loccode = '" . $_POST['StkLocation'] . "'";

        $LocnAddrResult = DB_query($sql, $db);

        $LocnRow          = DB_fetch_row($LocnAddrResult);
        $_POST['DelAdd1'] = $LocnRow[0];
        $_POST['DelAdd2'] = $LocnRow[1];
        $_POST['DelAdd3'] = $LocnRow[2];
        $_POST['DelAdd4'] = $LocnRow[3];
        $_POST['DelAdd5'] = $LocnRow[4];
        $_POST['DelAdd6'] = $LocnRow[5];
        $_POST['tel']     = $LocnRow[6];
        if ($_SESSION['PO' . $identifier]->contact == "" and $LocnRow[7] != "") {
            $_POST['Contact'] = $LocnRow[7];
        }

        $_SESSION['PO' . $identifier]->Location        = $_POST['StkLocation'];
        $_SESSION['PO' . $identifier]->SupplierContact = $_POST['SupplierContact'];
        $_SESSION['PO' . $identifier]->DelAdd1         = $_POST['DelAdd1'];
        $_SESSION['PO' . $identifier]->DelAdd2         = $_POST['DelAdd2'];
        $_SESSION['PO' . $identifier]->DelAdd3         = $_POST['DelAdd3'];
        $_SESSION['PO' . $identifier]->DelAdd4         = $_POST['DelAdd4'];
        $_SESSION['PO' . $identifier]->DelAdd5         = $_POST['DelAdd5'];
        //$_POST['tag']=$_SESSION['PO'.$identifier]->tag;
        $_SESSION['PO' . $identifier]->DelAdd6 = $_POST['DelAdd6'];
        $_SESSION['PO' . $identifier]->tel     = $_POST['tel'];
        $_POST['tag']                          = $_SESSION['PO' . $identifier]->tag;
        $_SESSION['PO' . $identifier]->tag     = $_POST['tag'];
                    
        if (isset($_POST['Contact'])) {
            $_SESSION['PO' . $identifier]->contact = $_POST['Contact'];
        }
    } elseif (isset($_POST['LookupDeliveryAddress']) or isset($_POST['StkLocation'])) {
        //echo "fin";

        $sql = "SELECT deladd1,
                            deladd2,
                            deladd3,
                            deladd4,
                            deladd5,
                            deladd6,
                            tel,
                            contact
                        FROM locations l, sec_loccxusser lxu
                        WHERE lxu.userid='" . $_SESSION['UserID'] . "' AND l.loccode=lxu.loccode 
                        and l.loccode='" . $_POST['StkLocation'] . "'";

        $LocnAddrResult = DB_query($sql, $db);

        if (DB_num_rows($LocnAddrResult) == 1) {
            $LocnRow          = DB_fetch_row($LocnAddrResult);
            $_POST['DelAdd1'] = $LocnRow[0];
            $_POST['DelAdd2'] = $LocnRow[1];
            $_POST['DelAdd3'] = $LocnRow[2];
            $_POST['DelAdd4'] = $LocnRow[3];
            $_POST['DelAdd5'] = $LocnRow[4];
            $_POST['DelAdd6'] = $LocnRow[5];
            $_POST['tel']     = $LocnRow[6];

            if ($_SESSION['PO' . $identifier]->contact == "" and $LocnRow[7] != "") {
                $_POST['Contact'] = $LocnRow[7];
            }

            $_SESSION['PO' . $identifier]->Location = $_POST['StkLocation'];
            $_SESSION['PO' . $identifier]->DelAdd1  = $_POST['DelAdd1'];
            $_SESSION['PO' . $identifier]->DelAdd2  = $_POST['DelAdd2'];
            $_SESSION['PO' . $identifier]->DelAdd3  = $_POST['DelAdd3'];
            $_SESSION['PO' . $identifier]->DelAdd4  = $_POST['DelAdd4'];
            $_SESSION['PO' . $identifier]->DelAdd5  = $_POST['DelAdd5'];
            $_SESSION['PO' . $identifier]->DelAdd6  = $_POST['DelAdd6'];
            $_SESSION['PO' . $identifier]->tel      = $_POST['tel'];
            $_SESSION['PO' . $identifier]->tag      = $_POST['tag'];
            if ($_POST['Contact']) {
                $_SESSION['PO' . $identifier]->contact = $_POST['Contact'];
            }
        }
    }

    if (isset($_POST['tel'])) {
        $_SESSION['PO' . $identifier]->telephoneContact = $_POST['tel'];
    }
    
    echo '<div class="col-md-4">';
        echo '<component-text-label label="Contacto: " id="Contact" name="Contact" value="'.$_SESSION['PO' . $identifier]->contact.'"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="Ciudad: " id="DelAdd3" name="DelAdd3" value="'.$_POST['DelAdd3'].'"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="Dirección Extra: " id="DelAdd6" name="DelAdd6" value="'.$_POST['DelAdd6'].'"></component-text-label>';
    echo '</div>';
    echo '<div class="col-md-4">';
        echo '<component-text-label label="Calle: " id="DelAdd1" name="DelAdd1" value="'.$_POST['DelAdd1'].'"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="Estado: " id="DelAdd4" name="DelAdd4" value="'.$_POST['DelAdd4'].'"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="Teléfono: " id="tel" name="tel" value="'.$_SESSION['PO' . $identifier]->telephoneContact.'"></component-text-label>';
    echo '</div>';
    echo '<div class="col-md-4">';
        echo '<component-text-label label="Colonia: " id="DelAdd2" name="DelAdd2" value="'.$_POST['DelAdd2'].'"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="C.P: " id="DelAdd5" name="DelAdd5" value="'.$_POST['DelAdd5'].'"></component-text-label>';
    echo '</div>';
    
    echo '<div class="row"></div>';

/*
    if (Havepermission($_SESSION['UserID'], 8, $db) == 1) {
        echo '<div class="col-md-1">';
        echo '<label>Fecha:</label>';
        echo '</div>';
        echo '<div class="col-md-1">';
        echo "<select Name='FromDia' id='FromDia' class='FromDia'>";
        $sql    = "SELECT * FROM cat_Days";
        $Todias = DB_query($sql, $db);
        while ($myrowTodia = DB_fetch_array($Todias, $db)) {
            $Todiabase = $myrowTodia['DiaId'];
            if (rtrim(intval($FromDia)) == rtrim(intval($Todiabase))) {
                echo "<option  VALUE='" . $myrowTodia['Dia'] . "' selected>" . $myrowTodia['Dia'];
            } else {
                echo "<option  VALUE='" . $myrowTodia['Dia'] . "'>" . $myrowTodia['Dia'];
            }
        }
        echo "</select>";
        echo '</div>';
        echo '<div class="col-md-1">';
        echo "<select Name='FromMes' id='FromMes' class='FromMes'>";
        $sql     = "SELECT * FROM cat_Months";
        $ToMeses = DB_query($sql, $db);
        while ($myrowToMes = DB_fetch_array($ToMeses, $db)) {
            $ToMesbase = $myrowToMes['u_mes'];
            if (rtrim(intval($FromMes)) == rtrim(intval($ToMesbase))) {
                echo "<option  VALUE='" . $myrowToMes['u_mes'] . "' selected>" . $myrowToMes['mes'] . "</option>";
            } else {
                echo "<option  VALUE='" . $myrowToMes['u_mes'] . "'>" . $myrowToMes['mes'] . "</option>";
            }
        }
        echo "</select>";
        echo '</div>';
        echo '<div class="col-md-1">';
        echo '<component-text name="FromYear" id="FromYear" maxlength="4" value="'.$FromYear.'"></component-text>';
        echo '</div>';
    } else {
        echo "<input type='hidden' name='FromDia' id='FromDia' value='" . $FromDia . "'>";
        echo "<input type='hidden' name='FromMes' id='FromMes' value='" . $FromMes . "'>";
        echo "<input type='hidden' name='FromAnio' name='FromAnio' value='" . $FromYear . "'>";
    }

    echo '<div class="col-md-4">
            <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Enviar por: </label></span>
              </div>
              <div class="col-md-9">
                <select name="deliveryby" id="deliveryby" class="deliveryby">';
    $sql           = 'SELECT shipper_id, shippername FROM shippers';
    $shipperResult = DB_query($sql, $db);
    while ($shipperRow = DB_fetch_array($shipperResult)) {
        if (isset($_POST['deliveryby']) and ($_POST['deliveryby'] == $shipperRow['shipper_id'])) {
            echo "<option selected value='" . $shipperRow['shipper_id'] . "'>" . $shipperRow['shippername'];
        } else {
            echo "<option value='" . $shipperRow['shipper_id'] . "'>" . $shipperRow['shippername'];
        }
    }
            echo '</select>';
        echo "</div>";
    echo '</div>
        </div>';

    echo '<div class="col-md-4">
            <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Tipo de orden: </label></span>
              </div>
              <div class="col-md-9">
                <select name="typeorder" id="typeorder" class="typeorder">';
    $sql = "SELECT typeid,typename FROM systypesorders where showtype=1 ";
    $typeResult = DB_query($sql, $db);

    while ($typerows = DB_fetch_array($typeResult)) {
        if (isset($_POST['typeorder']) and ($_POST['typeorder'] == $typerows['typeid'])) {
            echo "<option selected value='" . $typerows['typeid'] . "'>" . $typerows['typename'];
        } else {
            echo "<option value='" . $typerows['typeid'] . "'>" . $typerows['typename'];
        }
    }
            echo '</select>';
        echo "</div>";
    echo '</div>
        </div>';*/

        
    echo '
                </div>
            </div>
        </div>';
    
    echo '<div class="panel panel-default pull-right col-lg-12 col-md-12 col-sm-12 p0 m0" '.$ocultaBuscarEntrada.'>
            <div class="panel-heading" role="tab" id="headingOne">
              <h4 class="panel-title row">
                <div class="col-md-6 col-xs-6 text-left">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelDatosProveedor" aria-expanded="true" aria-controls="collapseOne">
                    <b>Datos del Proveedor</b>
                  </a>
                </div>
              </h4>
            </div>
            <div id="PanelDatosProveedor" name="PanelDatosProveedor" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">';

    echo '<input type=hidden name=Keywords value="' . $SuppName . '">';
    echo '<div class="col-md-4">
            <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Contacto: </label></span>
              </div>
              <div class="col-md-9">
                <select name="SupplierContact" id="SupplierContact" class="SupplierContact">';
    $sql = "SELECT contact FROM suppliercontacts WHERE supplierid='" . $_POST['Select'] . "'";

    $SuppCoResult = DB_query($sql, $db);

    while ($SuppCoRow = DB_fetch_array($SuppCoResult)) {
        if ($_POST['SupplierContact'] == $SuppCoRow['contact'] or ($_POST['SupplierContact'] == ''
            and $SuppCoRow['contact'] == $_SESSION['SupplierContact'])) {
            //if (1) {
            echo "<option selected value='" . $SuppCoRow['contact'] . "'>" . $SuppCoRow['contact'];
        } else {
            echo "<option value='" . $SuppCoRow['contact'] . "'>" . $SuppCoRow['contact'];
        }
    }
            echo '</select>';
        echo "</div>";
    echo '</div>';
    echo '<br>';
    echo '<component-text-label label="Ciudad: " id="suppDelAdd3" name="suppDelAdd3" value="'.$_POST['suppDelAdd3'].'" maxlength="40"></component-text-label>';
    echo '<br>';
    echo '<component-text-label label="Dirección Extra: " id="suppDelAdd6" name="suppDelAdd6" value="'.$_POST['suppDelAdd6'].'" maxlength="40"></component-text-label>';
    echo '<br>';
    $result        = DB_query("SELECT loccode, locationname FROM locations WHERE loccode='" . $_POST['port'] . "'", $db);
    $myrow         = DB_fetch_array($result);
    $_POST['port'] = $myrow['locationname'];
    echo '<component-text-label label="Entregar a: " id="port" name="port" value="'.$_POST['port'].'"></component-text-label>';
    echo '</div>';

    echo '<div class="col-md-4">';
        echo '<component-text-label label="Calle: " id="suppDelAdd1" name="suppDelAdd1" value="'.$_POST['suppDelAdd1'].'" maxlength="40"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="Estado: " id="suppDelAdd5" name="suppDelAdd5" value="'.$_POST['suppDelAdd4'].'" maxlength="40"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="Teléfono: " id="supptel" name="supptel" value="'.$_POST['supptel'].'" maxlength="30"></component-text-label>';
        echo '<br>';
        echo '<component-number-label label="% Devolución: " id="PorcDevTot" name="PorcDevTot" value="'.$_SESSION['PO' . $identifier]->PorcDevTot.'"></component-number-label>';
    echo '</div>';
    echo '<div class="col-md-4">';
        echo '<component-text-label label="Colonia: " id="suppDelAdd2" name="suppDelAdd2" value="'.$_POST['suppDelAdd2'].'" maxlength="40"></component-text-label>';
        echo '<br>';
        echo '<component-text-label label="C.P.: " id="suppDelAdd4" name="suppDelAdd4" value="'.$_POST['suppDelAdd5'].'" maxlength="40"></component-text-label>';
        echo '<br>';
        echo '<div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                      <span><label>Condiciones de Pago: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select name="PaymentTerms" id="PaymentTerms" class="PaymentTerms">';
        $result = DB_query('SELECT terms, termsindicator FROM paymentterms', $db);

    while ($myrow = DB_fetch_array($result)) {
        if ($myrow['termsindicator'] == $_SESSION['PO' . $identifier]->paymentterms) {
            echo "<option selected value='" . $myrow['termsindicator'] . "'>" . $myrow['terms'];
        } else {
            echo "<option value='" . $myrow['termsindicator'] . "'>" . $myrow['terms'];
        } //end while loop
    }
                echo '</select>';
            echo "</div>";
        echo '</div>';
    if ($_SESSION['PO' . $identifier]->CurrCode != $_SESSION['CompanyRecord']['currencydefault']) {
        echo '<br>';
        echo '<component-number-label label="Tipo de Cambio: " id="ExRate" name="ExRate" value="'.$_POST['ExRate'].'"></component-number-label>';
    } else {
        echo '<input type=hidden name="ExRate" value="1">';
    }
    echo '</div>';

    echo '<div class="col-md-12">';
    $Default_Comments = '';
    if (!isset($_POST['Comments'])) {
        $_POST['Comments'] = $Default_Comments;
    }
        echo '<br>';
        echo '<component-textarea-label label="Comentarios: " id="Comments" name="Comments" 
        placeholder="Comentarios" title="Comentarios" cols="3" rows="4" 
        value="'.$_POST['Comments'].'" ></component-textarea-label>';
    echo '</div>';
    echo '      </div>
            </div>
        </div>';

    echo '</div>';
   
    echo '</td></tr>';

    echo "<input type=hidden name='TieToOrderNumber' value=" . $TieToOrderNumber . ">";

    echo '<br>';
    echo '<br>';

    if (isset($_GET['ModifyOrderNumber'])) {
        echo "<div align='center'>";
        // echo '<button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="EnterLines" value="Buscar">
        // 		<img src="images/modificar_prod_25.png" title="MODIFICAR PRODUCTOS DE LA ORDEN DE COMPRA">
        // 	</button>';
        echo '<component-button type="submit" id="EnterLines" name="EnterLines" value="Modificar" class="glyphicon glyphicon-edit"></component-button>';
        echo "</div>";
    } else {
        echo "<div align='center'>";
        // echo '<button type="submit" style="cursor:pointer; border:0; background-color:transparent;" name="EnterLines" value="Buscar">
        // 		<img src="images/agregar_25.png" title="AGREGAR PRODUCTOS A LA ORDEN DE COMPRA">
        // 	</button>';
        echo '<component-button type="submit" id="EnterLines" name="EnterLines" value="Agregar" class="glyphicon glyphicon-plus"></component-button>';
        echo "</div>";
    }

    // Delete PO when necessrary

    /*
    // move apart by Hudson
    echo '<br><table border=0 width=80%>
    <tr>
    <td><font color=Darkblue size=2><b>' . _('Detalles de la Orden de Compra') . '</b></font></td>
    </tr>
    <tr><td style="width:50%">';

    echo '<table>';

    if($_SESSION['ExistingPurchOrder'] !=0 and $_SESSION['PO'.$identifier]->Stat==_('Printed')){
    echo '<tr><td><a href="' .$rootpath . "/GoodsReceived.php?" . SID . "&PONumber=" .
    $_SESSION['PO'.$identifier]->OrderNo . "&identifier=".$identifier.'"><b>'._('IR A RECIBIR PRODUCTOS').'</b></a></td></tr>';
    }

    echo '<td>' . _('Estatus') . ' :  </td><td><select name=Stat>';

    switch ($_SESSION['PO'.$identifier]->Stat) {
    case '':
    $StatusList=array(_('New Order'));
    break;
    case _('Pending'):
    $StatusList=array(_('Pending'), _('Authorised'), _('Rejected'), _('Cancelled'));
    break;
    case _('Authorised'):
    $StatusList=array(_('Pending'), _('Authorised'), _('Cancelled'));
    break;
    case _('Rejected'):
    $StatusList=array(_('Pending'), _('Authorised'), _('Rejected'), _('Cancelled'));
    break;
    case _('Cancelled'):
    $StatusList=array(_('Pending'), _('Cancelled'));
    break;
    case _('Printed'):
    $StatusList=array(_('Pending'), _('Printed'), _('Cancelled'));
    break;
    case _('Completed'):
    $StatusList=array(_('Completed'));
    break;
    default:
    $StatusList=array(_('New Order'), _('Pending'), _('Authorised'), _('Rejected'), _('Cancelled'));
    break;
    }

    foreach ($StatusList as $Status) {
    if ($_SESSION['PO'.$identifier]->Stat==$Status){
    echo '<option selected value='.$Status.'>' . $Status;
    } else {
    echo '<option value='.$Status.'>' . $Status;
    }
    }
    echo '</select>&nbsp;<input type="submit" name=UpdateStat value="' . _("Actualizar") .'"></td>';

    $date = date($_SESSION['DefaultDateFormat']);

    if (isset($_GET['ModifyOrderNumber']) && $_GET['ModifyOrderNumber'] != '') {
    $_SESSION['PO'.$identifier]->version += 1;
    $_POST['version'] =  $_SESSION['PO'.$identifier]->version;
    } elseif (isset($_SESSION['PO'.$identifier]->version) and $_SESSION['PO'.$identifier]->version != '') {
    $_POST['version'] =  $_SESSION['PO'.$identifier]->version;
    } else {
    $_POST['version']='1';
    }

    if (!isset($_POST['deliverydate'])) {
    $_POST['deliverydate']= date($_SESSION['DefaultDateFormat']);
    }

    if (!isset($_POST['Initiator'])) {
    $_POST['Initiator'] = $_SESSION['UserID'];
    $_POST['Requisition'] = '';
    }

    echo '<td>' . _('Fecha de Entrega') . ':</td><td>';
    echo "<input type='hidden' name='version' size=16 maxlength=15 value='" . $_POST['version'] . "'>
    <input type='hidden' name='Initiator' size=11 maxlength=10 value=" . $_POST['Initiator'] . ">

    <input type='text' class=date alt='".$_SESSION['DefaultDateFormat'].
    "' name='deliverydate' size=11 value=" . $_POST['deliverydate'] . '>'."</td></tr>";

    if (isset($TieToOrderNumber)) {
    echo '<tr><td>' . _('REFERENCIA PEDIDO VENTA') . ":</td><td><input type='text' disabled name='Requisition' size=16
    maxlength=15 value=" . $TieToOrderNumber . '></td>';
    } else {
    echo '<tr><td>' . _('Referencia REQ.') . ":</td><td><input type='text' name='Requisition' size=16
    maxlength=15 value=" . $_POST['Requisition'] . '></td>';
    }
    echo '<td>' . _('Fecha Impresi�n') . ':</td><td>';

    if (isset($_SESSION['PO'.$identifier]->DatePurchaseOrderPrinted) AND strlen($_SESSION['PO'.$identifier]->DatePurchaseOrderPrinted)>6){
    echo ConvertSQLDate($_SESSION['PO'.$identifier]->DatePurchaseOrderPrinted);
    $Printed = True;
    } else {
    $Printed = False;
    echo _('NO IMPRESA');
    }

    if (isset($_POST['AllowRePrint'])) {
    $sql='UPDATE purchorders SET allowprint=1 WHERE orderno='.$_SESSION['PO'.$identifier]->OrderNo;
    $result=DB_query($sql, $db);
    }

    if ($_SESSION['PO'.$identifier]->AllowPrintPO==0 AND $_POST['RePrint']!=1){
    echo '<tr><td>' . _('Allow Reprint') . ":</td><td><select name='RePrint' onChange='ReloadForm(form1.AllowRePrint)'><option selected value=0>" .
    _('No') . "<option value=1>" . _('Yes') . '</select></td>';
    echo '<td><input type=submit name="AllowRePrint" value="Update"></td></tr>';
    } elseif ($Printed) {
    echo "<tr><td colspan=2><a target='_blank'  href='$rootpath/PO_PDFPurchOrder.php?" .
    SID . "OrderNo=" . $_SESSION['ExistingPurchOrder']  . "&identifier=".$identifier. "'>" . _('Reprint Now') . '</a></td></tr>';
    }

    echo '</table>';
     */

    echo '<tr><td align=center></td></tr>';
    echo '</table>';
}

echo '</form>';

include 'includes/footer_Index.inc';
?>

<script language="javascript" type="text/javascript">
function mysubmit(labelanchor){
    document.forms['form1'].action = "<?php echo $_SERVER['PHP_SELF'] . '?' . SID ?>#" + labelanchor;
    document.forms['form1'].submit();

}

// Aplicar formato del SELECT
//fnFormatoSelectGeneral(".PageOffset1");
fnFormatoSelectGeneral(".StkLocation");
fnFormatoSelectGeneral(".FromDia");
fnFormatoSelectGeneral(".FromMes");
fnFormatoSelectGeneral(".deliveryby");
fnFormatoSelectGeneral(".typeorder");
fnFormatoSelectGeneral(".SupplierContact");
fnFormatoSelectGeneral(".PaymentTerms");
fnFormatoSelectGeneral("#tag");
</script>