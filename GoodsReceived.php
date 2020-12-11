<?php
/**
 * Recepcción de Productos
 *
 * @category Clase
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * Proceso de recepcción de productos
 */

/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/<error_log class="txt"></error_log>');
*/

include "includes/SecurityUrl.php";
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');
include('includes/session.inc');
/*$funcion=2313;
$title = _('Recibir Productos de Órdenes de Compra'); */
$funcion = 2313;
$title= '';//traeNombreFuncion($funcion, $db, "Recibir bienes y servicios de Órdenes de Compra");
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

include('includes/FreightCalculation.inc');
include('includes/GetSalesTransGLCodes.inc');
include('Numbers/Words.php');

//para cuentas referenciadas
include('includes/Functions.inc');
include('includes/XSAInvoicing.inc');
include('includes/SendInvoicing.inc');

// para el llamado al webservice
require_once('lib/nusoap.php');
//funciones PEPS
include_once('includes/CostHandle.inc');

$mensaje_emergente= "";
$procesoterminado= 0;

// Habilitar recepcion por orden de compra proveedor si se le pasa por url la variable SupplierOrderNo
if (isset($_POST['SupplierOrderNo'])) {
    $supplierOrderNo = $_POST['SupplierOrderNo'];
} else if (isset($_GET['SupplierOrderNo'])) {
    $supplierOrderNo = $_GET['SupplierOrderNo'];
} else {
    $supplierOrderNo = '';
}
if (isset($_POST['DefaultReceivedDate'])) {
    $fechactual = $_POST['DefaultReceivedDate'];
    $fechactual = date_create($fechactual);
    $fechactual = date_format($fechactual, 'Y-m-d');
    $_POST['DefaultReceivedDate'] = $fechactual;
} else {
    $fechactual = date("Y-m-d");
}
if (empty($supplierOrderNo) == false) {
    $rs = DB_query("SELECT orderno FROM purchorders WHERE supplierorderno = '$supplierOrderNo'", $db);
    if ($row = DB_fetch_array($rs)) {
        $_GET['PONumber'] = $row['orderno'];
    } else {
        prnMsg(_('No se puede recibir en esta orden de compra proveedor porque no se encontró en el sistema'), 'error');
    }
}

if (isset($_POST['TieToOrderNumber'])) {
    $TieToOrderNumber = $_POST['TieToOrderNumber'];
} else {
    $TieToOrderNumber = $_GET['TieToOrderNumber'];
};

if (isset($_GET['PONumber']) and $_GET['PONumber']<=0 and !isset($_SESSION['PO'])) {
    echo '<div class="centre"><a href= "' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">'.
        _('Select a purchase order to receive').'</a></div>';
    echo '<br>'. _('This page can only be opened if a purchase order has been selected') . '. ' . _('Please select a purchase order first');
    include('includes/footer_Index.inc');
    exit;
} elseif (isset($_GET['PONumber']) and !isset($_POST['Update'])) {
    $_GET['ModifyOrderNumber'] = $_GET['PONumber'];
    include('includes/PO_ReadInOrderReceived.inc');

    $fechactual = date_create($_SESSION['PO']->deliverydate);
    $fechactual = date_format($fechactual, 'Y-m-d');

    $fechactual = date_create(date('Y-m-d'));
    $fechactual = date_format($fechactual, 'Y-m-d');

    $_SESSION['PO']->SupplierOrderNo = $supplierOrderNo;
} elseif (isset($_POST['Update']) or isset($_POST['ProcessGoodsReceived'])) {
    foreach ($_SESSION['PO']->LineItems as $Line) {
        $RecvQty = $_POST['RecvQty_' . $Line->LineNo];
        
        
        if (!is_numeric($RecvQty)) {
            $RecvQty = 0;
        }
        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $RecvQty;
        //echo "<br>" . $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty;
    }
} elseif (isset($_POST['UpdateER'])) {
    for ($i=1; $i<=10; $i++) {
        $Encontro=0;
        $codigo=$_POST['partuno_' . $i];
        //echo 'codigo'.$codigo.'<br><br>';
        foreach ($_SESSION['PO']->LineItems as $Line) {
            if ($Line->barcode ==$codigo) {
                $Encontro=1;
                //echo 'entra dos:'.$_POST['partuno_' . $i].'<br>';
                $RecvQty=$_POST['qtyuno_' . $i];
                if (!is_numeric($RecvQty)) {
                    $RecvQty = 0;
                }
                $pendientes=$_SESSION['PO']->LineItems[$Line->LineNo]->Quantity-$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty;
                
                if ($pendientes>0) {
                    if ($RecvQty<=$pendientes) {
                        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty=$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty+$RecvQty;
                        $_POST['qtyuno_' . $i]=0;
                    } else {
                        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty=$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty+$pendientes;
                        $_POST['qtyuno_' . $i]=$RecvQty-$pendientes;
                    }
                } else {
                    $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty;
                }
            }
        }
        if ($Encontro==0 and strlen($codigo)>0) {
            prnMsg(_('El Código ') .$codigo. '. ' . _('No se encuentra en esta orden de compra. Por favor verifique e intente de nuevo'), 'error');
        }
        /*if($_POST['qtyuno_' . $i]>0){
			prnMsg( _('El Codigo ') .$codigo. '. ' . _('Sobrepasa X ').$_POST['qtyuno_' . $i]._(' las cantidades por enviar en las requisiciones'), 'error' );	
		}*/
    }
    
    for ($i=1; $i<=10; $i++) {
        $Encontro=0;
        $codigo=$_POST['partdos_' . $i];
        //echo 'codigo'.$codigo.'<br><br>';
        foreach ($_SESSION['PO']->LineItems as $Line) {
            if ($Line->barcode ==$codigo) {
                $Encontro=1;
                //echo 'entra dos:'.$_POST['partuno_' . $i].'<br>';
                $RecvQty=$_POST['qtydos_' . $i];
                if (!is_numeric($RecvQty)) {
                    $RecvQty = 0;
                }
                $pendientes=$_SESSION['PO']->LineItems[$Line->LineNo]->Quantity-$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty;
                
                if ($pendientes>0) {
                    if ($RecvQty<=$pendientes) {
                        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty=$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty+$RecvQty;
                        $_POST['qtydos_' . $i]=0;
                    } else {
                        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty=$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty+$pendientes;
                        $_POST['qtydos_' . $i]=$RecvQty-$pendientes;
                    }
                } else {
                    $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty;
                }
            }
        }
        if ($Encontro==0 and strlen($codigo)>0) {
            prnMsg(_('El Código ') .$codigo. '. ' . _('No se encuentra en esta orden de compra. Por favor verifique e intente de nuevo'), 'error');
        }
        /*if($_POST['qtyuno_' . $i]>0){
			prnMsg( _('El Codigo ') .$codigo. '. ' . _('Sobrepasa X ').$_POST['qtydos_' . $i]._(' las cantidades por enviar en las requisiciones'), 'error' );	
		}*/
    }
    
    for ($i=1; $i<=10; $i++) {
        $Encontro=0;
        $codigo=$_POST['parttres_' . $i];
        //echo 'codigo'.$codigo.'<br><br>';
        foreach ($_SESSION['PO']->LineItems as $Line) {
            if ($Line->barcode ==$codigo) {
                $Encontro=1;
                //echo 'entra dos:'.$_POST['partuno_' . $i].'<br>';
                $RecvQty=$_POST['qtytres_' . $i];
                if (!is_numeric($RecvQty)) {
                    $RecvQty = 0;
                }
                $pendientes=$_SESSION['PO']->LineItems[$Line->LineNo]->Quantity-$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty;
                
                if ($pendientes>0) {
                    if ($RecvQty<=$pendientes) {
                        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty=$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty+$RecvQty;
                        $_POST['qtytres_' . $i]=0;
                    } else {
                        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty=$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty+$pendientes;
                        $_POST['qtytres_' . $i]=$RecvQty-$pendientes;
                    }
                } else {
                    $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty;
                }
            }
        }
        if ($Encontro==0 and strlen($codigo)>0) {
            prnMsg(_('El Código ') .$codigo. '. ' . _('No se encuentra en esta orden de compra. Por favor verifique e intente de nuevo'), 'error');
        }
        /*if($_POST['qtyuno_' . $i]>0){
			prnMsg( _('El Codigo ') .$codigo. '. ' . _('Sobrepasa X ').$_POST['qtytres_' . $i]._(' las cantidades por enviar en las requisiciones'), 'error' );	
		}*/
    }
    /*
	
	
	foreach ($_SESSION['PO']->LineItems as $Line) {
		for ($i=1;$i<=10;$i++){
			$codigo=$_POST['partuno_' . $i];
			if ($Line->barcode ==$codigo){
				$RecvQty=$_POST['qtyuno_' . $i];
				if (!is_numeric($RecvQty)){
					$RecvQty = 0;
				}
				$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty +$RecvQty;
			}else{
				//imprime mensaje de que el codigo no existe en esa linea
				if (strlen($codigo)>0){
					prnMsg( _('El Codigo ') .$codigo. '. ' . _('NO se encuentra en esta orden de compra. Por favor verifique e intentelo de nuevo'), 'error' );
				}
			}
		}
		//la segunda carga
		for ($i=1;$i<=10;$i++){
			$codigo=$_POST['partdos_' . $i];
			if ($Line->barcode ==$codigo){
				$RecvQty=$_POST['qtydos_' . $i];
				if (!is_numeric($RecvQty)){
					$RecvQty = 0;
				}
				$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty +$RecvQty;
			}else{
				//imprime mensaje de que el codigo no existe en esa linea
				if (strlen($codigo)>0){
					prnMsg( _('El Codigo ') .$codigo. '. ' . _('NO se encuentra en esta orden de compra. Por favor verifique e intentelo de nuevo'), 'error' );
				}
			}
		}
		// la tercera carga
		for ($i=1;$i<=10;$i++){
			$codigo=$_POST['parttres_' . $i];
			if ($Line->barcode ==$codigo){
				$RecvQty=$_POST['qtytres_' . $i];
				if (!is_numeric($RecvQty)){
					$RecvQty = 0;
				}
				$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty +$RecvQty;
			}else{
				//imprime mensaje de que el codigo no existe en esa linea
				if (strlen($codigo)>0){
					prnMsg( _('El Codigo ') .$codigo. '. ' . _('NO se encuentra en esta orden de compra. Por favor verifique e intentelo de nuevo'), 'error' );
				}
			}
		}
	}
*/
} elseif (isset($_POST['Reset'])) {
    foreach ($_SESSION['PO']->LineItems as $Line) {
        $_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = 0;
    }
}

$mensaje_proveedor= "";
$mensajeCuentas = '';

if (empty(traeCuentaProveedor($_SESSION['PO']->SupplierID, $db))) {
    $mensajeCuentas .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El proveedor no tiene cuenta asignada</p>';
}

foreach ($_SESSION['PO']->LineItems as $OrderLine) {
    // $OrderLine->GLCode !=0
    if (trim($OrderLine->GLCode) == '') {
        $mensajeCuentas .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$OrderLine->StockID.' No esta configurado en la matriz del devengado</p>';
    }
}

if ($_SESSION['UserID'] == 'desarrollo') {
    // print_r($_SESSION['PO']->LineItems);
}

echo '<div class="panel panel-default pull-right col-lg-12 col-md-12 col-sm-12 p0 m0">
            <div class="panel-heading" role="tab" id="headingOne">
              <h4 class="panel-title row">
                <div class="col-md-6 col-xs-6 text-left">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelInfoCompra" aria-expanded="true" aria-controls="collapseOne">
                    <b>Información de la Recepción</b>
                  </a>
                </div>
              </h4>
            </div>
            <div id="PanelInfoCompra" name="PanelInfoCompra" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body" align="left">';
?>
<div class="col-md-6 col-xs-12">
    <!-- <component-label-text label="Dependencia:" id="txtNombreDependencia" name="txtNombreDependencia" value="<?php echo $_SESSION['PO']->legalname; ?>"></component-label-text> -->
    <component-label-text label="UR:" id="txtNombreUR" name="txtNombreUR" value="<?php echo $_SESSION['PO']->tagname; ?>"></component-label-text>
</div>
<div class="col-md-6 col-xs-12">
    <component-label-text label="UE:" id="txtNombreUE" name="txtNombreUE" value="<?php echo $_SESSION['PO']->unidadEjecutoraNombre; ?>"></component-label-text>
</div>
<div class="col-md-6 col-xs-12">
    <component-label-text label="Orden Compra:" id="txtOrdenCompraVer" name="txtOrdenCompraVer" value="<?php echo $_SESSION['PO']->OrderNo2; ?>"></component-label-text>
</div>
<div class="col-md-6 col-xs-12">
    <component-label-text label="Proveedor:" id="txtNombreProveedor" name="txtNombreProveedor" value="<?php echo $_SESSION['PO']->SupplierID . ' - ' . $_SESSION['PO']->SupplierName; ?>"></component-label-text>
    <?php
    if (!empty($mensaje_proveedor)) {
        echo $mensaje_proveedor;
    }
    ?>
</div>
<div class="col-md-6 col-xs-12">
    <component-label-text label="Requisición:" id="txtReqVer" name="txtReqVer" value="<?php echo $_SESSION['PO']->RequisitionNo; ?>"></component-label-text>
</div>
<div class="row"></div>
<div class="col-md-12 col-xs-12">
    <?php echo $mensajeCuentas; ?>
</div>
<div class="col-md-6 col-xs-12">
</div>
<div class="col-md-6 col-xs-12">
  <!--  <component-label-text label="Moneda:" id="txtMoneda" name="txtMoneda" value="<?php// echo $_SESSION['PO']->CurrCode; ?>"></component-label-text>
</div>
<div class="col-md-6 col-xs-12">
    <component-label-text label="T.C.:" id="txtTipoDeCambio" name="txtTipoDeCambio" value="<?php //echo number_format(1/$_SESSION['PO']->ExRate, 2); ?>"></component-label-text> -->
</div>
<?php

// Mensaje datos para operación
// $mensaje_emergente .= '<h4><strong>Dependencia: </strong> '.$_SESSION['PO']->legalname.'</h4>';
$mensaje_emergente .= '<p><strong>UR: </strong> '.$_SESSION['PO']->tagname.'</p>';
$mensaje_emergente .= '<p><strong>UE: </strong> '.$_SESSION['PO']->unidadEjecutoraNombre.'</p>';
$mensaje_emergente .= '<p><strong>Orden Compra: </strong> '.$_SESSION['PO']->OrderNo2.'</p>';
$mensaje_emergente .= '<p><strong>Proveedor: </strong> '.$_SESSION['PO']->SupplierID . ' - ' . $_SESSION['PO']->SupplierName.'</p>';

echo '      </div>
        </div>
    </div>';
echo '<div class="row"></div>';
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post name="FDatosB" id="FDatosB" onkeypress="if(event.keyCode==13) return false;" >';

echo '<input type=hidden name="TieToOrderNumber" value="'.$TieToOrderNumber.'"/><br>';

// Mostrar informacion en pantalla
if (!isset($_POST['ProcessGoodsReceived'])) {
    // recorrer elementos para saber si las partidas en su totalidad son servicios y cambiar las columnas
    $solo_servicios= true;
    foreach ($_SESSION['PO']->LineItems as $OrderLine) {
        if ($OrderLine->mbflag != "D") {
            $solo_servicios= false;
            break;
        }
    }

    // <th>' . _('Bienes') . '</th>
    // <th>' . _('Servicios') . '</th>
    if ($solo_servicios) {
        echo '<table class="table table-bordered" cellpadding="2">
                <tr class="header-verde">
                    <th style="text-align: center; display: none;">' . _('Seleccionar') .'<br><input type=checkbox name="All" onclick="javascript:selAll(this);" /></th>
                    <th style="text-align: center;">' . _('Código') . '</th>
                    <th style="text-align: center;">' . _('Descripción') . '</th>
                    <th style="text-align: center;">' . _('Cantidad') . '<br>' . _('Ordenada') . '</th>
                    <th style="display: none;">' . _('Recibidas') . '</th>
                    <th style="display: none;">' . _('Cantidad') . '<br>' . _('en esta Entrega') . '</th>
                    <th style="text-align: center;">' . _('Precio') . '</th>
                    <th style="text-align: center;">' . _('Valor Total') . '</th>
                </tr>';
    } else {
        echo '<table class="table table-bordered" cellpadding="2">
                <tr class="header-verde">
                    <th style="text-align: center; display: none;">' . _('Seleccionar') .'<br><input type=checkbox name="All" onclick="javascript:selAll(this);" /></th>
                    <th style="text-align: center;">' . _('Código') . '</th>
                    <th style="display: none;">' . _('Código Barra') . '</th>
                    <th style="text-align: center;">' . _('Descripción') . '</th>
                    <th style="text-align: center;">' . _('Cantidad') . '<br>' . _('Ordenada') . '</th>
                    <th style="text-align: center;">' . _('Unidad') . '</th>
                    <th style="text-align: center;">' . _('Recibidas') . '</th>
                    <th style="text-align: center;">' . _('Cantidad') . '<br>' . _('en esta Entrega') . '</th>
                    <th style="text-align: center;">' . _('Precio') . '</th>
                    <th style="display: none;">' . _('Descuento 1') . '</th>
                    <th style="display: none;">' . _('Descuento 2') . '</th>
                    <th style="display: none;">' . _('Descuento 3') . '</th>
                    <th style="text-align: center;">' . _('Valor Total') . '</th>
                </tr>';
    }

    $_SESSION['PO']->total = 0;
    $k=0; //row colour counter
    $I = 0;
    if (count($_SESSION['PO']->LineItems)>0) {
        foreach ($_SESSION['PO']->LineItems as $OrderLine) {
            if ($OrderLine->Controlled ==1 or $OrderLine->Serialised) {
                if (!isset($OrderLine->SerialItems)) {
                    $totalserie=0;
                } else {
                    $totalserie=0;
                    foreach ($OrderLine->SerialItems as $Item) {
                        if (count($Item->BundleQty)>0) {
                            $totalserie=$totalserie+$Item->BundleQty;
                        } else {
                            $totalserie=0;
                        }
                    }
                }
            //	echo '<br>serie:'.$totalserie.' lineno:'.$OrderLine->LineNo;
                $_SESSION['PO']->LineItems[$OrderLine->LineNo]->ReceiveQty = $totalserie;
            //echo $_SESSION['PO']->LineItems[$OrderLine->LineNo]->ReceiveQty ;
            }
        }
    }

    if (count($_SESSION['PO']->LineItems)>0) {
        foreach ($_SESSION['PO']->LineItems as $LnItm) {
            //echo $LnItm->stockupdate;
            if ($LnItm->stockupdate==1) {
                //echo '<tr bgcolor="Yellow">';
            } else {
                if ($k==1) {
                    //echo '<tr class="EvenTableRows">';
                    $k=0;
                } else {
                    //echo '<tr class="OddTableRows">';
                    $k=1;
                }
            }
            echo '<tr>';
            if (($LnItm->ReceiveQty==0) and (!isset($_POST['Update'])) and (!isset($_POST['UpdateER'])) and (!isset($_POST['ProcessGoodsReceived']))) {   /*If no quantities yet input default the balance to be received*/
                $LnItm->ReceiveQty = $LnItm->Quantity - $LnItm->QtyReceived;
            }
            
            if ((isset($_POST['Reset']))) {
                $LnItm->ReceiveQty = 0;
            }
            
            if ($LnItm->Controlled ==1 or $LnItm->Serialised) {
                if (!isset($OrderLine->SerialItems)) {
                    $totalserie=0;
                } else {
                    $totalserie=0;
                    foreach ($LnItm->SerialItems as $Item) {
                        if (count($Item->BundleQty)>0) {
                            $totalserie=$totalserie+$Item->BundleQty;
                        } else {
                            $totalserie=0;
                        }
                    }
                }
                $LnItm->ReceiveQty=$totalserie;
                //echo '<br>serie:'.$LnItm->ReceiveQty.' lineno:'.$LnItm->LineNo;
            }

            $LineTotal = ($LnItm->ReceiveQty * $LnItm->Price ) * (1-($LnItm->Desc1/100)) * (1-($LnItm->Desc2/100)) * (1-($LnItm->Desc3/100));
            
            $_SESSION['PO']->total = $_SESSION['PO']->total + $LineTotal;
            $DisplayQtyOrd = number_format($LnItm->Quantity, 0); // $LnItm->DecimalPlaces
            $DisplayQtyRec = number_format($_POST['RecvQty_' . $LnItm->LineNo], 0); // $LnItm->DecimalPlaces
            $DisplayLineTotal = number_format($LineTotal, 2);
            $DisplayPrice = number_format($LnItm->Price, 2);
            $DisplayDesc1 = number_format($LnItm->Desc1, 2);
            $DisplayDesc2 = number_format($LnItm->Desc2, 2);
            $DisplayDesc3 = number_format($LnItm->Desc3, 2);
            
            $uomsql="SELECT conversionfactor, suppliersuom
    				FROM purchdata
    				WHERE supplierno='".$_SESSION['PO']->SupplierID."'
    				AND stockid='".$LnItm->StockID."'";

            $uomresult=DB_query($uomsql, $db);
            if (DB_num_rows($uomresult)>0) {
                $uomrow=DB_fetch_array($uomresult);
                if (strlen($uomrow['suppliersuom'])>0) {
                    $uom=$uomrow['suppliersuom'];
                } else {
                    $uom=$LnItm->Units;
                }
            } else {
                $uom=$LnItm->Units;
            }
            $paginaupdate= '<a  href="' . $rootpath . '/Stocks.php?' . SID . '&StockID=' . $LnItm->StockID .'&PONumber='.$_SESSION['PO']->OrderNo.'&frompage=GoodsReceived.php' . '">';

            echo '<td style="text-align: center; display: none;"><font size=2><input type="checkbox" id="chk'.$I.'" name="ItemwithPurch_' . $LnItm->LineNo .'" '.$checkwithtax .'></font></td>';
            $I = $I + 1;
            
            // echo '<td><font size=2>' . $paginaupdate.$LnItm->StockID . '</a></font></td>';
            echo '<td style="text-align: center;"><font size=2>' . $LnItm->StockID . '</td>';
            
            if (!$solo_servicios) {
                echo '<td style="display: none;"><font size=2>' . $LnItm->barcode . '</td>';
            }

            echo '<td><font size=2>' . $LnItm->ItemDescription . '</td>';
            echo '<td class=number style="text-align: center;"><font size=2>' . $DisplayQtyOrd . '<input type=hidden name="txtCantidadOrdenada_'.$LnItm->LineNo.'" id="txtCantidadOrdenada_'.$LnItm->LineNo.'" value="'.$LnItm->Quantity.'"></td>';
            
            if (!$solo_servicios) {
                echo '<td style="text-align: center;"><font size=2>' . $uom . '</td>';
            }

            // Obtener Tipo de Articulo
            $tipoArticuloBien = "X";
            $tipoArticuloServicio = "";
            $styleColum = 'style="text-align: center;"';
            if ($LnItm->mbflag == 'D') {
                $tipoArticuloBien = "";
                $tipoArticuloServicio = "X";
                $styleColum = ' style="display: none;" ';
            }
            
            // echo '<td style="text-align: center;"><font size=2>' . $tipoArticuloBien . '</td>';
            // echo '<td style="text-align: center;"><font size=2>' . $tipoArticuloServicio . '</td>';
            // Obtener Tipo de Articulo
            
            echo '<td class=number '.$styleColum.'><font size=2>' . $LnItm->QtyReceived . '<input type=hidden name="txtCantidadRecibida_'.$LnItm->LineNo.'" id="txtCantidadRecibida_'.$LnItm->LineNo.'" value="'.$LnItm->QtyReceived.'" /></font></td>';
            
            echo '<td class=number '.$styleColum.'><font size=2>';

            if ($LnItm->Controlled == 1) {
                echo '<input type=hidden name="RecvQty_' . $LnItm->LineNo . '" id="RecvQty_' . $LnItm->LineNo . '" value="' . $LnItm->ReceiveQty . '"><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">' . number_format($LnItm->ReceiveQty, $LnItm->DecimalPlaces) . '</a></font></td>';
            } else {
                if ($solo_servicios) {
                    echo '<input type=text name="RecvQty_' . $LnItm->LineNo . '" id="RecvQty_' . $LnItm->LineNo . '" maxlength=10 size=10 onKeyPress="return soloNumeros(event)" value="' . $LnItm->ReceiveQty . '" style="text-align: right; display: none;"></font></td>';
                } else {
                    echo '<input type=text name="RecvQty_' . $LnItm->LineNo . '" id="RecvQty_' . $LnItm->LineNo . '" maxlength=10 size=10 onKeyPress="return soloNumeros(event)" value="' . $LnItm->ReceiveQty . '" style="text-align: right;"></font></td>';
                }
            }

            echo '<td class=number style="text-align: right;"><font size=2>$ ' . $DisplayPrice . '</td>';
            echo '<td class=number style="display: none;"><font size=2> ' . $DisplayDesc1 . '%</td>';
            echo '<td class=number style="display: none;"><font size=2> ' . $DisplayDesc2 . '%</td>';
            echo '<td class=number style="display: none;"><font size=2> ' . $DisplayDesc3 . '%</td>';
            echo '<td class=number style="text-align: right;"><font size=2>$ ' . $DisplayLineTotal . '</font></td>';

            if ($LnItm->Controlled == 1) {
                if ($LnItm->Serialised==1) {
                    echo '<td><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">'.
                        _('Seleccione Series'). '</a></td>';
                } else {
                    echo '<td><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">'.
                        _('Seleccione Lotes'). '</a></td>';
                }
            }
            echo '</tr>';
            
            // captura de informacion por categoria de inventario
            $lineaorden=$LnItm->LineNo;
            $lineaordenx=$LnItm->LineNo;
            $lineaordeny=$LnItm->PODetailRec;
            if ($lineaordeny=='') {
                $lineaordeny=0;
            }
            $ordenexiste=$_SESSION['PO']->OrderNo;
            $StockID_Prop=$LnItm->StockID;
            $typesales=30;
            // echo '<tr style="align:left" valign="top">';
            // echo '<td valign="top" colspan=2 style="align:left">';
            // echo '</td>';
            // echo '<td valign="top" colspan=4 style="align:left">';
            // include('includes/Show_Stockcatproperties.php');
            // echo '</td>';
            // echo '</tr>';
        }
    }
    echo '<input type="hidden" id="I" value="' . $I . '">';
    $DisplayTotal = number_format($_SESSION['PO']->total, 2);
    if ($solo_servicios) {
        echo '<tr>
            <td colspan=4 class=number style="text-align: right;"><b>' . _('Valor total de servicios recibidos'). '</b>
            </td>
            <td class=number style="text-align: right;"><font size=2><b>$ '. $DisplayTotal. '</b></font>
            </td>
            </tr>';
    } else {
        echo '<tr>
            <td colspan=7 class=number style="text-align: right;"><b>' . _('Valor Total de productos recibidos'). '</b>
            </td>
            <td class=number style="text-align: right;"><font size=2><b>$ '. $DisplayTotal. '</b></font>
            </td>
            </tr>';
    }
    

    echo '</table>';
}

$SomethingReceived = 0;
if (count($_SESSION['PO']->LineItems)>0) {
    foreach ($_SESSION['PO']->LineItems as $OrderLine) {
        if ($OrderLine->ReceiveQty>0) {
            $SomethingReceived =1;
        }
    }
}

$DeliveryQuantityTooLarge = 0;
$NegativesFound = false;
$InputError = false;

if (count($_SESSION['PO']->LineItems)>0) {
    foreach ($_SESSION['PO']->LineItems as $OrderLine) {
        /*if ($_SESSION['UserID']=='admin'){
			echo '<br>ReceiveQty'.$OrderLine->ReceiveQty.'<br>';
			echo '<br>ReceiveQty'.$OrderLine->QtyReceived.'<br>';
			echo $suma = $OrderLine->ReceiveQty + $OrderLine->QtyReceived;
			echo '<br>suma'.$suma;
			echo '<br>';
			echo '<br>Quantity'.$OrderLine->Quantity.'<br>';
			echo '<br>Session'.$_SESSION['OverReceiveProportion'].'</br>';
			echo '<br>resultado'.$OrderLine->Quantity * (1+ ($_SESSION['OverReceiveProportion'] / 100));
		}*/
        if ($OrderLine->ReceiveQty+$OrderLine->QtyReceived > $OrderLine->Quantity * (1+ ($_SESSION['OverReceiveProportion'] / 100))) {
            $DeliveryQuantityTooLarge =1;
            $InputError = true;
        }
        if ($OrderLine->ReceiveQty < 0 and $_SESSION['ProhibitNegativeStock']==1) {
            $SQL = "SELECT locstock.quantity FROM
					locstock WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['PO']->Location . "'";
            $CheckNegResult = DB_query($SQL, $db);
            $CheckNegRow = DB_fetch_row($CheckNegResult);
            if ($CheckNegRow[0]+$OrderLine->ReceiveQty<0) {
                $NegativesFound=true;
                prnMsg(_('No se permite agregar cantidades en negativo'), 'error', $OrderLine->StockID . ' no puede ir negativo');
            }
        }
    }
}

if ($SomethingReceived==0 and isset($_POST['ProcessGoodsReceived'])) {
    prnMsg(_('') . '. ' . _('La cantidad debe ser mayor a cero'), 'warn');
    echo '<div class="centre"><input type=submit name=Update Value=' . _('Update') . '></div>';
} elseif ($NegativesFound) {
    prnMsg(_('Corregir la cantidad para realizar el proceso'), 'error');
    echo '<div class="centre"><input type=submit name=Update Value=' . _('Update') . '>';
} elseif ($DeliveryQuantityTooLarge==1 and isset($_POST['ProcessGoodsReceived'])) {
    prnMsg(_('Las cantidades de entrega sobrepasan las solicitadas en la orden de compra'), 'error');
    echo '<br>';
    prnMsg(_('Modificar la cantidad si se desea agregar una cantidad mayor'), 'info');
    echo '<div class="centre"><input type=submit name=Update Value=' . _('Update') . '>';
} elseif (isset($_POST['ProcessGoodsReceived']) and $SomethingReceived==1 and $InputError == false) {
    $mailMessage = "";
    $mailMessage .= "<h2>Orden Número: " . $_SESSION['PO']->OrderNo . "</h2>";
    $mailMessage .= "<h4>Detalles de la Recepción</h4>";
    $mailMessage .= "<table border='1'>";
    $mailMessage .= "<tr style='text-align:left; font-size:1.2em; font-weight:bold'>";
    $mailMessage .= "<th style='padding:.2em'>ID Producto</th>";
    $mailMessage .= "<th style='padding:.2em'>Descripción</th>";
    $mailMessage .= "<th style='padding:.2em'>Cantidad Total</th>";
    $mailMessage .= "<th style='padding:.2em'>Cantidad Recibida</th>";
    $mailMessage .= "<th style='padding:.2em'>Precio</th>";
    $mailMessage .= "</tr>";

    if ($_SESSION['CompanyRecord']==0) {
        prnMsg(_('La información y las preferencias de la compañía no se pudieron recuperar') . ' - ' . _('Contactar al Administrador'), 'error');
        include('includes/footer_Index.inc');
        exit();
    }

    $SQL = 'SELECT itemcode,
			glcode,
			quantityord,
			quantityrecd,
			qtyinvoiced,
			shiptref,
			jobref,
            podetailitem
		FROM purchorderdetails
		WHERE orderno=' . (int) $_SESSION['PO']->OrderNo . '
		AND completed=0 AND purchorderdetails.status=2
		ORDER BY podetailitem';
    //echo "<br>1. -" . $SQL;
    $ErrMsg = _('Error') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check that the details of the purchase order had not been changed by another user because'). ':';
    $DbgMsg = _('The following SQL to retrieve the purchase order details was used');
    $Result=DB_query($SQL, $db, $ErrMsg, $DbgMsg);

    $Changes=0;
    $LineNo=1;

    while ($myrow = DB_fetch_array($Result)) {
        // $_SESSION['PO']->LineItems[$LineNo]->GLCode != $myrow['glcode'] or
        if ($_SESSION['PO']->LineItems[$LineNo]->ShiptRef != $myrow['shiptref'] or
            $_SESSION['PO']->LineItems[$LineNo]->JobRef != $myrow['jobref'] or
            $_SESSION['PO']->LineItems[$LineNo]->QtyInv != $myrow['qtyinvoiced'] or
            $_SESSION['PO']->LineItems[$LineNo]->StockID != $myrow['itemcode'] or
            $_SESSION['PO']->LineItems[$LineNo]->Quantity != $myrow['quantityord'] or
            $_SESSION['PO']->LineItems[$LineNo]->QtyReceived != $myrow['quantityrecd']) {
            prnMsg(_('Este pedido ya ha sido modificado o facturado'), 'warn');

            if ($debug==1) {
                echo '<table border="1"  style="text-align:center; margin:0 auto;">';
                echo '<tr><td>' . _('GL Code of the Line Item') . ':</td>
						<td>' . $_SESSION['PO']->LineItems[$LineNo]->GLCode . '</td>
						<td>' . $myrow['glcode'] . '</td></tr>';
                echo '<tr><td>' . _('ShiptRef of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->ShiptRef . '</td>
					<td>' . $myrow['shiptref'] . '</td></tr>';
                echo '<tr><td>' . _('Contract Reference of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->JobRef . '</td>
					<td>' . $myrow['jobref'] . '</td>
					</tr>';
                echo '<tr><td>' . _('Quantity Invoiced of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->QtyInv . '</td>
					<td>' . $myrow['qtyinvoiced'] . '</td></tr>';
                echo '<tr><td>' . _('Stock Code of the Line Item') . ':</td>
					<td>'. $_SESSION['PO']->LineItems[$LineNo]->StockID . '</td>
					<td>' . $myrow['itemcode'] . '</td></tr>';
                echo '<tr><td>' . _('Order Quantity of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->Quantity . '</td>
					<td>' . $myrow['quantityord'] . '</td></tr>';
                echo '<tr><td>' . _('Quantity of the Line Item Already Received') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->QtyReceived . '</td>
					<td>' . $myrow['quantityrecd'] . '</td></tr>';
                echo '</table>';
            }
            
            if (empty($_SESSION['PO']->SupplierOrderNo)) {
                // echo "<div class='centre'><a href='$rootpath/GoodsReceived.php?" . SID . '&PONumber=' .
                //     $_SESSION['PO']->OrderNumber . '">'. _('Vuelve a actualizar la orden de compra para recibir'). '</a></div>';
            } else {
                // echo "<div class='centre'><a href='$rootpath/GoodsReceived.php?" . SID . '&SupplierOrderNo=' .
                //         $_SESSION['PO']->SupplierOrderNo . '">'. _('Vuelve a actualizar la orden de compra proveedor para recibir'). '</a></div>';
            }
            unset($_SESSION['PO']->LineItems);
            unset($_SESSION['PO']);
            unset($_POST['ProcessGoodsReceived']);
            include("includes/footer_Index.inc");
            exit();
        }
        $LineNo++;
    }
       
    DB_free_result($Result);

    $QuantityControlled=true;
    $totalserie=0;
    $recibidas=0;
    foreach ($_SESSION['PO']->LineItems as $OrderLine) {
        if ($OrderLine->Controlled ==1 or $OrderLine->Serialised) {
            if (!isset($OrderLine->SerialItems)) {
                        $QuantityControlled=false;
            } else {
                $totalserie=0;
                $recibidas=0;
                foreach ($OrderLine->SerialItems as $Item) {
                    if (count($Item->BundleQty)>0) {
                        $totalserie=$totalserie+$Item->BundleQty;
                    } else {
                        $QuantityControlled=false;
                        break;
                    }
                }
                $recibidas=$OrderLine->ReceiveQty;
                if ($totalserie!=$recibidas) {
                    $QuantityControlled=false;
                    break;
                }
            }
        }
    }
    if ($QuantityControlled==false) {
        prnMsg(_('No ha ingresado los numeros de serie por lo tanto,') . ' ' . _('no ha sido posible la transaccion'), 'error');
        //echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'&ModifyOrderNumber=' . $OrderNo  .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
        echo '<a href="'. $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '"><b>' . _('Regresar a Búsqueda de Ordenes de Compra'). '</b></a><br>';
        include('includes/footer_Index.inc');
        exit();
    }
    
    $Result = DB_Txn_Begin($db);
    $sql='SELECT locations.tagref,locations.temploc,locations.areacod,tags.legalid
		  FROM locations inner join tags
			ON locations.tagref = tags.tagref
		  WHERE loccode="'.$_SESSION['PO']->Location.'"';
    $result=DB_query($sql, $db);
    if (DB_num_rows($result)>0) {
        $myrow=DB_fetch_array($result);
        $unidaddenegocio=$myrow['tagref'];
        $typelocation=$myrow['temploc'];
        $areacodeloc=$myrow['areacod'];
        $legalid = $myrow['legalid'];
    } else {
        $unidaddenegocio=0;
        $typelocation=0;
        $areacode=0;
        $legalid=0;
    }
    if (strpos($_SESSION['DefaultDateFormat'], '/')) {
        $flag = "/";
    } elseif (strpos($_SESSION['DefaultDateFormat'], '-')) {
        $flag = "-";
    } elseif (strpos($_SESSION['DefaultDateFormat'], '.')) {
        $flag = ".";
    }
    $diafecha = substr($_POST['DefaultReceivedDate'], 8, 2);
    $mesfecha = substr($_POST['DefaultReceivedDate'], 5, 2);
    $aniofecha = substr($_POST['DefaultReceivedDate'], 0, 4);
    $fechaentrega = $diafecha.$flag.$mesfecha.$flag.$aniofecha;
     //$fecha = explode('-',$_POST['DefaultReceivedDate']);
     //$fechaperiod = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];
    //$PeriodNo = GetPeriod($fechaentrega, $db,$unidaddenegocio);
    $PeriodNo = GetPeriod($fechaentrega, $db, $unidaddenegocio);
    /*if($_SESSION['UserID']=='desarrollo'){
		echo '<br>P:'.GetPeriod($_POST['DefaultReceivedDate'], $db);
		echo '<br>P:'.$PeriodNo;
		echo '<br>Fecha:'.$_POST['DefaultReceivedDate'];
		exit;  
	}*/
    //$_POST['DefaultReceivedDate'] = FormatDateForSQL($_POST['DefaultReceivedDate']);
    $GRN = GetNextTransNo(25, $db);
    // Folio de la poliza por unidad ejecutora
    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $unidaddenegocio, $_SESSION['PO'.$identifier]->unidadEjecutora, 25);
    
    //Variable para el alta de propiedades de categoria de inventario
    $tipodefacturacion=25;
    
    if ($typelocation==3) {
        // viene de requisiscion y hay q hacer compra y venta
        //include('includes/ProcessTraspasosRecepcionDirecta.inc');
        $_SESSION['PO']->Location=$almacenCompra;
    }
    //$GRN = GetNextTransNo(25, $db);
    
    
    foreach ($_SESSION['PO']->LineItems as $OrderLine) {
        //consulta el nuevo codigo de acuerdo a la categoria de inventario del producto
        
        if ($OrderLine->ReceiveQty !=0 and $OrderLine->ReceiveQty!='' and isset($OrderLine->ReceiveQty)) {
            if ($_SESSION['PO']->ExRate=='') {
                $_SESSION['PO']->ExRate=1;
            }
            
            //buscar margen automatico para costo en categoria de inventario
            $qry = "Select margenautcost 
					FROM stockcategory 
						INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
					WHERE stockmaster.stockid = '".$OrderLine->StockID."'";
            $rsm = DB_query($qry, $db);
            $rowm = DB_fetch_array($rsm);
            $margenautcost = $rowm['margenautcost']/100;
            
            $OrderLine->Price += ($OrderLine->Price*$margenautcost);
            
            $LocalCurrencyPrice = ($OrderLine->Price / $_SESSION['PO']->ExRate);
            
            //echo "entra";

            if ($OrderLine->StockID!='') {
                /**
                 *$SQL = "SELECT materialcost + labourcost + overheadcost as stdcost
                        FROM stockmaster
                        WHERE stockid='" . $OrderLine->StockID . "'";

                    $ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo standard no se recupero');
                    $DbgMsg = _('El SQL utilizado es');
                    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                    $myrow = DB_fetch_row($Result);
                **/
                
                //$avgcost = $myrow[0]; //ASI LO RECUPERBA DE LA CONSULTA ARRIBA COMENTADA
                $avgcost = 0;
                
                
                /*
				if ($_SESSION['TypeCostStock']==1){
					$EstimatedAvgCostXtag=ExtractAvgCostXtag($unidaddenegocio,$OrderLine->StockID, $db);
					$avgcost = $EstimatedAvgCostXtag;
				}else{
					$legalid=ExtractLegalid($unidaddenegocio,$db);
					$EstimatedAvgCostXlegal=ExtractAvgCostXlegal($legalid,$OrderLine->StockID, $db);
					$avgcost = $EstimatedAvgCostXlegal;
				}
				*/
                
                /* SIEMPRE RECIBE LOS PRODUCTOS A EL COSTO DE LA ORDEN DE COMPRA Y COSTEALOS ASI */
                $avgcost = $LocalCurrencyPrice;
                // echo $avgcost . "<br/>";
                $avgcost = $avgcost - ($avgcost*($OrderLine->Desc1/100));
                $avgcost = $avgcost - ($avgcost*($OrderLine->Desc2/100));
                $avgcost = $avgcost - ($avgcost*($OrderLine->Desc3/100));
                
                $purchdatasql='SELECT conversionfactor,price
								FROM purchdata
								WHERE purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
								AND purchdata.stockid="'. $OrderLine->StockID . '"';
                    
                $rsm = DB_query($purchdatasql, $db);
                $rowm = DB_fetch_array($rsm);
                    
                //echo $purchdatasql;
                $factordeConversion = 1;
                if (is_numeric($rowm['conversionfactor'])) {
                    $factordeConversion = $rowm['conversionfactor'];
                }
                
                //$LocalCurrencyPrice = $LocalCurrencyPrice/$factordeConversion;
                //$avgcost = $avgcost/$factordeConversion;
                //costo PEPS
                //echo "EstimatedAvgCostXLegal, factor".$factordeConversion."/".$rowm['conversionfactor']."<br>";
                
                if ($OrderLine->StockID !='') {
                    //echo "EstimatedAvgCostXLegal, paso<br>";
                    $type = 30;
                    $ref = $_SESSION['PO']->RequisitionNo;
                    if (strlen($_SESSION['PO']->RequisitionNo) != 0) {
                        $type=0;
                        $ref=0;
                    }
                    
                    $resf = Entradas($legalid, $_SESSION['PO']->Location, $OrderLine->StockID, $avgcost/$factordeConversion, $OrderLine->ReceiveQty*$factordeConversion, $type, $ref, $db);
                    
                    $EstimatedAvgCost = getAVGCost($legalid, $OrderLine->StockID, $db);
                    
                    /* NO USAR PEPS HASTA QUE ESTE PROBADO...
					if ($_SESSION['TypeCostStock']==3)
						$EstimatedAvgCost = getPEPSCost($legalid,$OrderLine->StockID);
						*/
                }
                // RECALCULA COSTO PROMEDIO EN CASO DE QUE NO VENGA DE UN PEDIDO DE VENTA
                //echo 'orden venta:'.$_SESSION['PO']->RequisitionNo;
                if (strlen($_SESSION['PO']->RequisitionNo) != 0) {
                    //echo 'entraaaaaa';
                    // CALCULA COSTO PROMEDIO A NIVEL RAZON SOCIAL
                    $unitsXLegal=StockUnitsXLegal($OrderLine->StockID, $unidaddenegocio, $db);
                    $estavgcostXlegal=StockAvgcostXLegal($OrderLine->StockID, $unidaddenegocio, $db);
                    $lastcostant=StockLastCostXLegal($OrderLine->StockID, $unidaddenegocio, $db);
                    
                    $EstimatedAvgCostXlegal=EstimatedAvgCostXLegal($OrderLine->StockID, $unidaddenegocio, $unitsXLegal, $estavgcostXlegal, $OrderLine->ReceiveQty*$factordeConversion, $avgcost/$factordeConversion, $lastcostant, 20, $db);
                    //echo "EstimatedAvgCostXLegal:".$OrderLine->StockID."/".$unidaddenegocio."/".$unitsXLegal."/".$estavgcostXlegal."/".$OrderLine->ReceiveQty*$factordeConversion."/".$avgcost/$factordeConversion."/".$lastcostant;
                    //echo '<br><br>nuevo costo: '.$EstimatedAvgCostXlegal;
                    // CALCULAR COSTO PROMEDIO A NIVEL UNIDAD DE NEGOCIO
                    //$unitscostXtag=StockAvgUnits($OrderLine->StockID,$unidaddenegocio,$db);
                    $estavgcostXtag=StockAvgcost($OrderLine->StockID, $unidaddenegocio, $db);
                    $lastcostantXtag=StockLastCost($OrderLine->StockID, $unidaddenegocio, $db);
                    $unitscostXtag=StockUnitsXTag($OrderLine->StockID, $unidaddenegocio, $db);
                    $EstimatedAvgCostXtag=EstimatedAvgCost($OrderLine->StockID, $unidaddenegocio, $unitscostXtag, $estavgcostXtag, $OrderLine->ReceiveQty*$factordeConversion, $avgcost/$factordeConversion, $lastcostantXtag, 20, $db);
                }
            
                if ($OrderLine->QtyReceived==0) { //its the first receipt against this line
                    $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
                }
                $CurrentStandardCost = $avgcost;

                /*Set the purchase order line stdcostunit = weighted average standard cost used for all receipts of this line
				 This assures that the quantity received against the purchase order line multiplied by the weighted average of standard
				 costs received = the total of standard cost posted to GRN suspense*/
                //$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = (($CurrentStandardCost * $OrderLine->ReceiveQty) + ($_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost *$OrderLine->QtyReceived)) / ($OrderLine->ReceiveQty + $OrderLine->QtyReceived);
                
                /* ESTA LINEA NULIFICA LA ANTERIOR*/
                $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $CurrentStandardCost;
            } elseif ($OrderLine->QtyReceived==0 and $OrderLine->StockID=="") {
                $avgcost = $LocalCurrencyPrice;
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc1/100));
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc2/100));
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc3/100));
                
                $CurrentStandardCost = $avgcost;

                $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
            }

            if ($OrderLine->StockID=='') {
                $avgcost = $LocalCurrencyPrice;
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc1/100));
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc2/100));
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc3/100));
                
                $CurrentStandardCost = $avgcost;
                $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
            }
            
            if ($OrderLine->ReceiveQty >= ($OrderLine->Quantity - $OrderLine->QtyReceived)) {
                $SQL = "UPDATE purchorderdetails SET
							quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
							stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							completed=1
					WHERE podetailitem = " . $OrderLine->PODetailRec;
            } else {
                $SQL = "UPDATE purchorderdetails SET
							quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
							stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							completed=0
					WHERE podetailitem = " . $OrderLine->PODetailRec;
            }
            //echo "<br>2.-" . $SQL;
            $ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El detalle de la orden de compra no se realizo');
            $DbgMsg = _('El SQL utilizado es');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            
            if ($OrderLine->StockID !='') {
                $UnitCost = $CurrentStandardCost;
            } else {
                $UnitCost = $LocalCurrencyPrice;// $OrderLine->Price / $_SESSION['PO']->ExRate ;
                
                $avgcost = $UnitCost;
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc1/100));
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc2/100));
                $avgcost = $avgcost - ($avgcost*($LnItm->Desc3/100));
                
                $UnitCost = $avgcost;
            }
            //********************************* CAMPOS EXTRA **********************************************
            // Actualizacion de datos de categoria de inventario
            //*********************************************************************************************
            $lineaorden='_'.$OrderLine->LineNo;
            
            $totalcampos=$_POST['TotalPropDefault_'.$OrderLine->LineNo];
                $campouno='';
                $campodos='';
            if ($totalcampos>0) {
                for ($i=0; $i<$totalcampos; $i++) {
                    $stockid=$_POST['PropDefaultval'.$lineaorden.'_'.$i];
                    $valorstock=$_POST['PropDefault'.$lineaorden.'_'.$i];
                    $tipoobj=$_POST['tipoobjeto'.$lineaorden.'_'.$i];
                    $consulta=$_POST['consulta'.$lineaorden.'_'.$i];
                    $campo=$_POST['campo'.$lineaorden.'_'.$i];
                    $classe = $_POST['class'.$lineaorden.'_'.$i];
                    $required = $_POST['required'.$lineaorden.'_'.$i];
                    $requiredtoday = $_POST['requiredtoday'.$lineaorden.'_'.$i];
                    $labelprop = $_POST['label'.$lineaorden.'_'.$i];
                    $reqatprint= $_POST['reqatprint'.$lineaorden.'_'.$i];
                        
                        
                    if ($tipoobj=='checkbox') {
                        if (isset($_POST['PropDefault'.$lineaorden.'_'.$i])) {
                            $valorstock="SI";
                        } else {
                            $valorstock="NO";
                        }
                    }
                    $valorbase=$valorstock;
                    if (strlen($consulta)>5) {
                        $sqlcampos=$consulta.' and '. $campo.' = "'.$valorstock.'"';
                        //echo $sqlcampos;
                        $DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                        $ErrMsg = _('No se pudo obtener el valor, por que');
                        $Result = DB_query($sqlcampos, $db, $ErrMsg, $DbgMsg, true);
                        $Rowcampos = DB_fetch_array($Result);
                        $valorbase=$Rowcampos[1];
                    }
                    if ($valorstock=="0" and strlen($consulta)>5 and $tipoobjeto == 5) {
                        $sqlcampos=$consulta.' and salesmanname like "%sin trabajador%" and tags.tagref= '.$_SESSION['Items'.$identifier]->Tagref.' limit 1';
                        $DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                        $ErrMsg = _('No se pudo obtener el valor, por que');
                        $Result = DB_query($sqlcampos, $db, $ErrMsg, $DbgMsg, true);
                        $Rowcampos = DB_fetch_array($Result);
                        $valorbase=$Rowcampos[1];
                        $valorstock=$Rowcampos[0];
                    }
                    if ($valorstock!="0" and $valorstock != "") {
                        //$existeprop=ValidaSalesProperty($stockid,$_SESSION['PO'.$identifier]->OrderNo,$POLine->PODetailRec,trim($valorstock),$tipodefacturacion,$db);
                        $existeprop=0;
                        if ($existeprop==0) {
                            if ($reqatprint==1) {
                                if ($campouno!='') {
                                    if ($campodos=='') {
                                        $campodos=$valorbase;
                                    }
                                }
                                if ($campouno=='') {
                                    $campouno=$valorbase;
                                }
                            }
                            
                            
                            $SQL = "INSERT INTO salesstockproperties (
									stkcatpropid,
									orderno,
									orderlineno,
									valor,
									InvoiceValue,
									typedocument
								)
								VALUES
								(
									". $stockid . ",
									" . $GRN. ",
									" . $OrderLine->PODetailRec . ",
									'" . trim($valorstock) . "',
									'" . trim($valorbase) . "',
									". $tipodefacturacion . "
								)";
                            $ErrMsg="Error al insertar los valores extra";
                            // $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            //echo '<br>sql:'.$SQL;
                        }
                    }
                    //***********************+Campo de Categoria********************************
                    $stockid1 = explode("/", $_POST['txtRuta'.$StockItem->LineNumber]);
                    foreach ($stockid1 as $arrCategorias) {
                        $categoriadetail=explode(".", $arrCategorias);
                        if ($categoriadetail[0]!='') {
                            $SQL = "INSERT INTO salesstockproperties (
									stkcatpropid,
									orderno,
									orderlineno,
									valor,
									InvoiceValue,
									typedocument
		
								)
								VALUES
								(
									". $categoriadetail[0]. ",
									" . $GRN. ",
									" . $OrderLine->PODetailRec. ",
									'" . trim($categoriadetail[1]) . "',
									'" . trim($categoriadetail[1]) . "',
									". $tipodefacturacion . "
								)";
                            $ErrMsg="Error al insertar los valores extra";
                            // $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        }//Fin de insert de propiedades en menu desplegable
                    }//Fin de categorias de producto en menu desplegable
                } //Fin de recorrido de partidas de pedido
            }
            //*********************************************************************************************
            //*********************************************************************************************
            $grnno = 0;
            if ($OrderLine->Controlled !=1) {
                $CurrentStandardCostCompra = $OrderLine->Price; /* AL PARECER LO ESTA HACIENDO DOS VECES + ($OrderLine->Price*$margenautcost)  ;*/
                $CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc1/100));
                $CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc2/100));
                $CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc3/100));
                //echo 'costo compra'.$CurrentStandardCostCompra;
                $SQL = "INSERT INTO grns (grnbatch,
							podetailitem,
							itemcode,
							itemdescription,
							deliverydate,
							qtyrecd,
							supplierid,
							rategr,
							textgr,
							textgr1,
							stdcostunit,
                            ln_ue)
					VALUES (" . $GRN . ",
						" . $OrderLine->PODetailRec . ",
						'" . $OrderLine->StockID . "',
						'" . DB_escape_string($OrderLine->ItemDescription) . "',
						'" . $_POST['DefaultReceivedDate'] . "',
						" . $OrderLine->ReceiveQty . ",
						'" . $_SESSION['PO']->SupplierID . "',
						'" . $_SESSION['PO']->ExRate . "',
						'" . DB_escape_string($campouno) . "',
						'" . DB_escape_string($campodos) . "',
						" . $CurrentStandardCostCompra . ",
                        '" . $_SESSION['PO']->unidadEjecutora . "')";
                //echo '<br><pre>sql:<br>'.$SQL;
                $ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la transaccion');
                $DbgMsg =  _('El SQL utilizado es');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                $grnno = DB_Last_Insert_ID($db, 'grns', 'grnno');
            }
            //echo "<br>3.-" . $SQL;

            if ($OrderLine->StockID!='') {
                $SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['PO']->Location . "'";
                $result = DB_query($SQL, $db);
                if (DB_num_rows($result)==1) {
                    $LocQtyRow = DB_fetch_row($result);
                    $QtyOnHandPrior = $LocQtyRow[0];
                } else {
                    $QtyOnHandPrior = 0;
                }
                $sql='SELECT conversionfactor
					FROM purchdata
					WHERE supplierno="'.$_SESSION['PO']->SupplierID.'"
					AND stockid="'.$OrderLine->StockID.'"';
                $result=DB_query($sql, $db);
                if (DB_num_rows($result)>0) {
                    $myrow=DB_fetch_array($result);
                    if (($myrow['conversionfactor'] == 0) or ($myrow['conversionfactor'] == '')) {
                        $conversionfactor=1;
                    } else {
                        $conversionfactor=$myrow['conversionfactor'];
                    }
                } else {
                    $conversionfactor=1;
                }
                $OrderLine->ReceiveQty=$OrderLine->ReceiveQty*$conversionfactor;
                $LocalCurrencyPrice = $LocalCurrencyPrice/$conversionfactor;
                $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost / $conversionfactor;
                $UnitCost = $UnitCost/$conversionfactor;
                $CurrentStandardCost = $CurrentStandardCost/$conversionfactor;
                /*
				// funcion de avgcost por tagref
				$EstimatedAvgCostXtag=ExtractAvgCostXtag($unidaddenegocio,$OrderLine->StockID, $db);
				// funcion de avgcost por legalid 
				$legalid=ExtractLegalid($unidaddenegocio,$db);
				$EstimatedAvgCostXlegal=ExtractAvgCostXlegal($legalid,$OrderLine->StockID, $db);
				if ($_SESSION['TypeCostStock']==1){
					$unitscost=$unitscostXtag;
					$estavgcost=$estavgcostXtag;
					$EstimatedAvgCost=$EstimatedAvgCostXtag;
					$lastcost=$lastcostXtag;
				}else{
					$unitscost=$unitscostXlegal;
					$estavgcost=$estavgcostXlegal;
					$EstimatedAvgCost=$EstimatedAvgCostXlegal;
					$lastcost=$lastcostXlegal;
				}
				
				if ($EstimatedAvgCost==0 and $OrderLine->Controlled ==0){
					$EstimatedAvgCost = $LocalCurrencyPrice;
					$EstimatedAvgCost = $EstimatedAvgCost - ($EstimatedAvgCost*($LnItm->Desc1/100));
					$EstimatedAvgCost = $EstimatedAvgCost - ($EstimatedAvgCost*($LnItm->Desc2/100));
					$EstimatedAvgCost = $EstimatedAvgCost - ($EstimatedAvgCost*($LnItm->Desc3/100));
				}elseif($OrderLine->Controlled ==1){
					$EstimatedAvgCost = $LocalCurrencyPrice;
					$EstimatedAvgCost = $EstimatedAvgCost - ($EstimatedAvgCost*($LnItm->Desc1/100));
					$EstimatedAvgCost = $EstimatedAvgCost - ($EstimatedAvgCost*($LnItm->Desc2/100));
					$EstimatedAvgCost = $EstimatedAvgCost - ($EstimatedAvgCost*($LnItm->Desc3/100));
					$LocalCurrencyPrice=$EstimatedAvgCost;
					$CurrentStandardCost=$EstimatedAvgCost;
				}
				*/
                
                $SQL = "UPDATE locstock
					SET quantity = locstock.quantity + " . $OrderLine->ReceiveQty . "
					WHERE locstock.stockid = '" . $OrderLine->StockID . "'
					AND loccode = '" . $_SESSION['PO']->Location . "'";
                    //echo "<br>4.-" . $SQL;
                $ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
                $DbgMsg =  _('El SQL utiliado es');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                
                $refSalesOrder = "";
                if (empty($_SESSION['PO']->RequisitionNo) == false) {
                    $refSalesOrder = " - Requisición: " . $_SESSION['PO']->RequisitionNo;
                }
                
                if ($OrderLine->Controlled !=1) {
                    /*************************************************************/
                    // Aqui va seccion de requisiciones
                    /*************************************************************/
                        //include('includes/ProcessTraspasosCompras.inc');
                    /*************************************************************/
                    
                    $mailMessage .= "<tr style='text-align:left; font-size:1.2em;'>";
                    $mailMessage .= "<td style='padding:.2em'>" . $OrderLine->StockID . "</td>";
                    $mailMessage .= "<td style='padding:.2em'>" . $OrderLine->ItemDescription . "</td>";
                    $mailMessage .= "<td style='padding:.2em'>" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->Quantity . "</td>";
                    $mailMessage .= "<td style='padding:.2em'>" . $OrderLine->ReceiveQty . "</td>";
                    $mailMessage .= "<td style='padding:.2em'>$" . number_format($_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost) . "</td>";
                    $mailMessage .= "</tr>";
                    
                    $SQL = "INSERT INTO stockmoves (stockid,
									type,
									transno,
									loccode,
									trandate,
									price,
									prd,
									reference,
									qty,
									standardcost,
									newqoh,
									discountpercent,
									discountpercent1,
									discountpercent2,
									tagref,
									avgcost,
                                    ln_ue
									)
						VALUES ('" . $OrderLine->StockID . "',
							25,
							" . $GRN . ", '" . $_SESSION['PO']->Location . "',
							'" . $_POST['DefaultReceivedDate'] . "',
							'" . $LocalCurrencyPrice . "',
							" . $PeriodNo . ",
							'" . $_SESSION['PO']->SupplierID . " (" . $_SESSION['PO']->SupplierName . ") - ORDEN DE COMPRA: " .$_SESSION['PO']->OrderNo2 . $refSalesOrder . "',
							" . $OrderLine->ReceiveQty . ",
							" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							" . ($QtyOnHandPrior + $OrderLine->ReceiveQty) . ",
							" . $OrderLine->Desc1 . ",
							" . $OrderLine->Desc2 . ",
							" . $OrderLine->Desc3 . ",
							'" . $unidaddenegocio . "',
							'" . $EstimatedAvgCost . "',
                            '" . $_SESSION['PO']->unidadEjecutora . "')";
                    //echo "<br>5.-" . $SQL;
                    $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realice el movimiento de inventario');
                    $DbgMsg =  _('El SQL utilizado es');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                    $StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');
                }
                if ($OrderLine->Controlled ==1) {
                    foreach ($OrderLine->SerialItems as $Item) {
                        $GRN = GetNextTransNo(25, $db);
                        $CurrentStandardCostCompra = ($OrderLine->Price / $_SESSION['PO']->ExRate);
                        $CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc1/100));
                        $CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc2/100));
                        $CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc3/100));
                        
                        $CurrentStandardCostCompraControl = ($OrderLine->Price);
                        $CurrentStandardCostCompraControl = $CurrentStandardCostCompraControl - ($CurrentStandardCostCompraControl*($OrderLine->Desc1/100));
                        $CurrentStandardCostCompraControl = $CurrentStandardCostCompraControl - ($CurrentStandardCostCompraControl*($OrderLine->Desc2/100));
                        $CurrentStandardCostCompraControl = $CurrentStandardCostCompraControl - ($CurrentStandardCostCompraControl*($OrderLine->Desc3/100));
                        
                        $SQL = "INSERT INTO grns (grnbatch,
							podetailitem,
							itemcode,
							itemdescription,
							deliverydate,
							qtyrecd,
							supplierid,
							rategr,
							stdcostunit,
                            ln_ue)
							VALUES (" . $GRN . ",
								" . $OrderLine->PODetailRec . ",
								'" . $OrderLine->StockID . "',
								'" . $OrderLine->ItemDescription . "',
								'" . $_POST['DefaultReceivedDate'] . "',
								" . ($Item->BundleQty) . ",
								'" . $_SESSION['PO']->SupplierID . "',
								'" . $_SESSION['PO']->ExRate . "',
								" . $CurrentStandardCostCompraControl . ",
                                '" . $_SESSION['PO']->unidadEjecutora . "')";
                        $ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la transaccion');
                        $DbgMsg =  _('El SQL utilizado es');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        
                        $SQL = "INSERT INTO stockmoves (stockid,
									type,
									transno,
									loccode,
									trandate,
									price,
									prd,
									reference,
									qty,
									standardcost,
									newqoh,
									discountpercent,
									discountpercent1,
									discountpercent2,
									tagref,
									narrative,
									avgcost,
                                    ln_ue
									)
						VALUES ('" . $OrderLine->StockID . "',
							25,
							" . $GRN . ", '" . $_SESSION['PO']->Location . "',
							'" . $_POST['DefaultReceivedDate'] . "',
							" . $CurrentStandardCostCompra . ",
							" . $PeriodNo . ",
							'" . $_SESSION['PO']->SupplierID . " (" . $_SESSION['PO']->SupplierName . ") - ORDEN DE COMPRA: " .$_SESSION['PO']->OrderNo2 . $refSalesOrder . "',
							" . ($Item->BundleQty*$conversionfactor) . ",
							" . $CurrentStandardCost . ",
							" . ($QtyOnHandPrior + $Item->BundleQty ) . ",
							" . $OrderLine->Desc1 . ",
							" . $OrderLine->Desc2 . ",
							" . $OrderLine->Desc3 . ",
							'" . $unidaddenegocio . "',
							'" . $Item->BundleRef . "',
							'" . $CurrentStandardCost . "',
                            '" . $_SESSION['PO']->unidadEjecutora . "')";
                        //echo "<br>5.-" . $SQL;
                        $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realice el movimiento de inventario');
                        $DbgMsg =  _('El SQL utilizado es');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        /*Get the ID of the StockMove... */
                        $StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');

                            $SQL = "SELECT COUNT(*) FROM stockserialitems
									WHERE stockid='" . $OrderLine->StockID . "'
									AND loccode = '" . $_SESSION['PO']->Location . "'
									AND serialno = '" . $Item->BundleRef . "'";
                            $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a batch or lot stock item already exists because');
                            $DbgMsg =  _('The following SQL to test for an already existing controlled but not serialised stock item was used');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            $AlreadyExistsRow = DB_fetch_row($result);
                        if (trim($Item->BundleRef) != "") {
                            list($mes,$dia,$anio) = explode('/', $Item->CustomsDate);
                            $customsDate = "$anio-$mes-$dia";
                                
                            if ($AlreadyExistsRow[0]>0) {
                                if ($OrderLine->Serialised == 1) {
                                    $SQL = "UPDATE stockserialitems 
												SET quantity = " . $Item->BundleQty . " , 
												   standardcost=" .$CurrentStandardCost . ", 
												   customs = '" . $Item->Customs . "', 
												   pedimento = '".$Item->CustomsNumber."',		
												   customs_number = '" . $Item->CustomsNumber . "', 
												   customs_date = '" . $customsDate . "'";
                                } else {
                                    $SQL = "UPDATE stockserialitems 
												SET quantity = quantity + " . ($Item->BundleQty*$conversionfactor) . " , 
													standardcost=" .$CurrentStandardCost . ", 
													customs = '" . $Item->Customs . "', 
													pedimento = '".$Item->CustomsNumber."',
													customs_number = '" . $Item->CustomsNumber . "', 
													customs_date = '" . $customsDate . "'";
                                }
                                $SQL .= "WHERE stockid='" . $OrderLine->StockID . "'
											 AND loccode = '" . $_SESSION['PO']->Location . "'
											 AND serialno = '" . $Item->BundleRef . "'";
                            } else {
                                $SQL = "INSERT INTO stockserialitems (stockid,
												loccode,
												serialno,
												qualitytext,
												quantity,
												standardcost,
												customs,
												customs_number,
												customs_date,
												pedimento
												)
											VALUES ('" . $OrderLine->StockID . "',
												'" . $_SESSION['PO']->Location . "',
												'" . $Item->BundleRef . "',
												'" . $puertoentrada . "',
												" . ($Item->BundleQty*$conversionfactor) . ",
												" . $CurrentStandardCost . ",
												'" . $Item->Customs . "',
												'" . $Item->CustomsNumber . "',
												'" . $customsDate . "',
												'".$Item->CustomsNumber."'		
											)";
                            }
                            $ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La actualizacion de los numero de serie no se realizo');
                            $DbgMsg =  _('El SQL utilizado es');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                            $SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty,
											standardcost,
											orderno,
											orderdetailno
											)
									VALUES (" . $StkMoveNo . ",
										'" . $OrderLine->StockID . "',
										'" . $Item->BundleRef . "',
										" . ($Item->BundleQty*$conversionfactor) . ",
										" . $CurrentStandardCost .",
										" . (int) $_SESSION['PO']->OrderNo .",
										" . $OrderLine->PODetailRec .")";
                            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El movimiento de los numeros de serie no se realizo');
                            $DbgMsg = _('El SQL utilizado es');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        }
                        
                        $LocCode=$_SESSION['PO']->Location;
                    }
                }
            }

            if ($_SESSION['PO']->GLLink==1 and $OrderLine->GLCode !=0) {
                $SQL = "INSERT INTO gltrans (type,
                typeno,
                trandate,
                periodno,
                account,
                narrative,
                amount,
                tag,
                dateadded,
                userid,
                posted,
                ln_ue,
                purchno,
                stockid,
                grns,
                nu_folio_ue,
                nu_devengado)
                VALUES (25,
                '" . $GRN . "',
                '" . $_POST['DefaultReceivedDate'] . "',
                '" . $PeriodNo . "',
                '" . $OrderLine->GLCode . "',
                '" . $_SESSION['PO']->Comments  . "',
                '" . $CurrentStandardCost * $OrderLine->ReceiveQty . "',
                '" . $unidaddenegocio . "',"
                . "NOW(),"
                . "'".$_SESSION['UserID']."',
                '1',
                '".$_SESSION['PO']->unidadEjecutora."',
                '".$_SESSION['PO']->OrderNo."',
                '".$OrderLine->StockID."',
                '".$grnno."',
                '".$folioPolizaUe."',
                '1')";
                //echo "<br>6.-" . $SQL;
                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de las cuentas contables para la orden de compra no se realizo');
                $DbgMsg = _('El SQL utilizado es');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                $cuenta_proveedor= traeCuentaProveedor($_SESSION['PO']->SupplierID, $db);

                $SQL = "INSERT INTO gltrans (type,
                typeno,
                trandate,
                periodno,
                account,
                narrative,
                amount,
                tag,
                dateadded,
                userid,
                posted,
                ln_ue,
                purchno,
                stockid,
                grns,
                nu_folio_ue,
                nu_devengado)
                VALUES (25,
                '" . $GRN . "',
                '" . $_POST['DefaultReceivedDate'] . "',
                '" . $PeriodNo . "',
                '" . $OrderLine->cuentaProveedorRecepcion . "', 
                '" . $_SESSION['PO']->Comments . "',
                '" . -$UnitCost * $OrderLine->ReceiveQty . "',
                '" . $unidaddenegocio . "',"
                . "NOW(),"
                . "'".$_SESSION['UserID']."',
                '1',
                '".$_SESSION['PO']->unidadEjecutora."',
                '".$_SESSION['PO']->OrderNo."',
                '".$OrderLine->StockID."',
                '".$grnno."',
                '".$folioPolizaUe."',
                '1'
                )";
                //echo "<br>7.-" . $SQL;
                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de reverso no se realizo');
                $DbgMsg = _('El SQL utilizado es');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            }

            // $resultado= GeneraMovimientoContablePresupuesto(
            //     25,
            //     "COMPROMETIDO",
            //     "DEVENGADO",
            //     $GRN,
            //     $PeriodNo,
            //     $UnitCost * $OrderLine->ReceiveQty,
            //     $unidaddenegocio,
            //     $_POST['DefaultReceivedDate'],
            //     $OrderLine->clavepresupuestal,
            //     $_SESSION['PO'.$identifier]->OrderNo2,
            //     $db,
            //     false,
            //     '',
            //     '',
            //     '',
            //     $_SESSION['PO']->unidadEjecutora,
            //     1,
            //     0,
            //     $folioPolizaUe
            // );

            // // Log Presupuesto
            // $descriptionLog = "Recepción Orden de Compra ".$_SESSION['PO']->OrderNo2.". Requisición ".$_SESSION['PO']->RequisitionNo;
            // $agregoLog = fnInsertPresupuestoLog($db, 25, $GRN, $unidaddenegocio, $OrderLine->clavepresupuestal, $PeriodNo, ($UnitCost * $OrderLine->ReceiveQty), 259, "", $descriptionLog, 1, '', 0, $_SESSION['PO']->unidadEjecutora); // Abono
            // $agregoLog = fnInsertPresupuestoLog($db, 25, $GRN, $unidaddenegocio, $OrderLine->clavepresupuestal, $PeriodNo, ($UnitCost * $OrderLine->ReceiveQty) * -1, 260, "", $descriptionLog, 1, '', 0, $_SESSION['PO']->unidadEjecutora); // Cargo
        }
    }

    $completedsql="SELECT SUM(completed) as completedlines,
						COUNT(podetailitem) as alllines
					FROM purchorderdetails 
					WHERE orderno='".$_SESSION['PO']->OrderNo."' and status = 2 ";
    $completedresult=DB_query($completedsql, $db);
    $mycompletedrow=DB_fetch_array($completedresult);
    $status=$mycompletedrow['alllines']-$mycompletedrow['completedlines'];
    if ($status==0) {
        $sql='SELECT stat_comment FROM purchorders WHERE orderno='.$_SESSION['PO']->OrderNo;
        $result=DB_query($sql, $db);
        $myrow=DB_fetch_array($result);
        $comment=$myrow['stat_comment'];
        $date = date($_SESSION['DefaultDateFormat']);
        $StatusComment=$date.' - Order Completed'.'<br>'.$comment;
        $sql="UPDATE purchorders 
				SET status='"._('Completed')."',
				stat_comment='".$StatusComment."'
				WHERE orderno=".$_SESSION['PO']->OrderNo;
        //$result=DB_query($sql,$db);
        $Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
        //echo "<br>8.-" . $SQL;
    } else {
        // Por estatus de recepcion paracial
        $sql="UPDATE purchorders 
                SET status='"._('Pending')."'
                WHERE orderno=".$_SESSION['PO']->OrderNo;
        $Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
    }
    $PONo = $_SESSION['PO']->OrderNo;
    $SupplierPONo = $_SESSION['PO']->SupplierOrderNo;
    
    
    // genera nueva orden de compra para que esta quede completa de acuerdo a los check seleccionados por el usuario
    include('includes/GeneraNewPurchOrder.inc');
    // si viene de orden de trabajo
    if ($_SESSION['PO']->Wo>0) {
        include('includes/AutomaticEmisionWo.inc');
    }
    $Result = DB_Txn_Commit($db);
        
    /*************************************************************************************************/
    //alta de la factura en automatico para que no tenga q hacer el proceso de carga de los productos
    // include('includes/DefineSuppTransClassFacComV1.php');
    // include('includes/AddSupplierInvoice.inc');
    
    /*************************************************************************************************/
    /*
	//genera la factura de cliente
	$qohsql = "SELECT  systypeorder,tagref
			   FROM purchorders
			   WHERE   orderno = '" . $PONo . "'";
	$qohresult =  DB_query($qohsql,$db);
	$qohrow = DB_fetch_row($qohresult);
	$systypeorder=$qohrow[0];
	$tagorder=$qohrow[1];
	if($systypeorder==2){
		$Result = DB_Txn_Begin($db);
		$qohsql = "SELECT  tagdebtorno,typepack
			   FROM tags
			   WHERE   tagref = '" . $tagorder . "'";
		$qohresult =  DB_query($qohsql,$db);
		$qohrow = DB_fetch_row($qohresult);
		$Debtorno=$qohrow[0];
		$tagorder=$qohrow[1];
		
		$qohsql = "SELECT  sum(qty*price),loccode
			   FROM stockmoves
			   WHERE   transno= '" . $GRN . "' and type=25";
		$qohresult =  DB_query($qohsql,$db);
		$qohrow = DB_fetch_row($qohresult);
		$unitprice=$qohrow[0];
		$LocCode=$qohrow[1];
		$OrderNo = GetNextTransNo(30, $db);         
		$hora=date('H');
		$minuto=date('i');
		$segundo=date('s');
		$FechaEntrega=Date('Y-m-d').' '.$hora.':'.$minuto.':'.$segundo;
		$_SESSION['CurrAbrev']='MXN';
		$HeaderSQL = "INSERT INTO salesorders (orderno,debtorno,branchcode,customerref,comments,orddate,ordertype,shipvia,
						  deliverto,deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contactphone,contactemail,
						  freightcost,fromstkloc,deliverydate,quotedate,confirmeddate,quotation,deliverblind,
						  salesman,tagref,taxtotal,totaltaxret,currcode,paytermsindicator,advance,UserRegister)
			  SELECT ".$OrderNo.", custbranch.debtorno,custbranch.branchcode,custbranch.taxid,'Automatica',now(),debtorsmaster.salestype,0,custbranch.brname,
				      custbranch.braddress1,custbranch.braddress2,custbranch.braddress3,custbranch.braddress4,custbranch.braddress5,custbranch.braddress6,
				      custbranch.phoneno,custbranch.email,0,'".$LocCode."','".$FechaEntrega."','".$Fecha."','".$Fecha."',1,0,custbranch.salesman,".$tagorder.",
				      0,0,'".$_SESSION['CurrAbrev']."',debtorsmaster.paymentterms,0,'admin'
			  FROM custbranch inner join debtorsmaster on debtorsmaster.debtorno=custbranch.debtorno
			  WHERE custbranch.branchcode='".$Debtorno."'";
		$ErrMsg='Error al insertar el encabezado de la venta';
		$DbgMsg='El SQL es';
		//echo '<pre>'.$HeaderSQL;
	     // return $HeaderSQL;
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);	
		// genera la venta para todos los productos recibidos, saco los productos del alamcen de recepcion directa
		
		$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (orderlineno,orderno,stkcode,unitprice,quantity,discountpercent,
								discountpercent1,discountpercent2,narrative,poline,itemdue,
								fromstkloc,warranty)";
		$orderlineno=0;
		foreach ($_SESSION['PO']->LineItems as $OrderLine) {
		
			$LineItemsSQL = $StartOf_LineItemsSQL .
						$orderlineno . ',
						' . $OrderNo . ',
						'."'" . $OrderLine->StockID . "'".',
						'. $OrderLine->Price . ',
						' . $OrderLine->Quantity . ',
						' . floatval($OrderLine->DiscountPercent) . ',
						' . floatval($OrderLine->DiscountPercent1) . ',
						' . floatval($OrderLine->DiscountPercent2) . ',
						'."'" . trim(DB_escape_string(htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES))) . "'".',
						'."'" . FormatDateForSQL($OrderLine->ItemDue) . "'".',
						'."'" . $fromstkloc . "'".',
						'."'" . $PriceST . "'".',
						'."'" . $StockItem->showdescription . "'".',
						'. $StockItem->warranty.'
					)';
			$ErrMsg = _('No se puede agregar el producto a la orden por que');
			$Result = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
			$orderlineno=$orderlineno+1;
		}
		
		 require_once('includes/RegistraFacturaSocio.inc');
		
		$Facturaventa=RegistrarFacturaPedido($OrderNo,10,$db);
		
		$separa = explode('|',$Facturaventa);
		$InvoiceNo = $separa[0];		
		$tipodefacturacion = $separa[1];
		if ($InvoiceNo>0){
			$qohsql = "SELECT  areacode,legalid ,typeinvoice
					   FROM tags 
					   WHERE   tagref = '" . $tagorder . "'";
			$qohresult =  DB_query($qohsql,$db);
			$qohrow = DB_fetch_row($qohresult);
			$Area=$qohrow[0];
			$legaid=$qohrow[1];
				
			$InvoiceNoTAG = DocumentNext(10, $tagorder,$Area,$legaid, $db);
			$separa = explode('|',$InvoiceNoTAG);
			$serie = $separa[1];
			$folio = $separa[0];
			
			
			$SQL = "UPDATE salesorders SET quotation=4, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNo;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$RFC='';
			$codigoref=strtoupper($RFC);
			$tipoarea='01';//add_cerosstring($area,2);
			$translegal='01';//add_cerosstring($legaid,2);
			$cuentareferenciada=$translegal.$tipoarea.$codigoref;
			
			//extrae el id del docto x cobrar
			$qohsql = "SELECT  id,ovamount,ovgst
				    FROM debtortrans WHERE   transno = '" . $InvoiceNo . "' and type=".$tipodefacturacion;
			$qohresult =  DB_query($qohsql,$db);
			$qohrow = DB_fetch_row($qohresult);
			$DebtorTransID=$qohrow[0];
			$Montofactura=$qohrow[1];
			$IVAFactura=$qohrow[2];
			
			//extrae banco activo para cuentas referenciadas
			$sql="Select * from bancosreferencia where active=1";
			$result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			if (DB_num_rows($result)!=0)
			{
				while ($myrowcuenta = DB_fetch_array($result,$db)){
					$bankid=$myrowcuenta['bancoid'];
					// genera digito verificador
					$cuentaref = strtoupper($cuentareferenciada.GeneraCuentaReferenciada($db,$cuentareferenciada,$bankid));
					// inserta en tabla de referencias bancarias
					$insertarefe=InsertaCuentaBank($cuentaref,$DebtorTransID,$bankid,$db);				
				}
			}
			//Actualizar el documento para folio
			$SQLfactura="UPDATE debtortrans
			      SET folio='" . $serie.'|'.$folio . "',
				    ref1='" . $cuentaref . "'
			      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
			$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
			$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
			$Result = DB_query($SQLfactura,$db,$ErrMsg,$DbgMsg,true);
			
			$SQLfactura="UPDATE salesorders
			      SET taxtotal='" . $IVAFactura . "'
			      WHERE orderno=".$OrderNo;
			$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
			$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
			$Result = DB_query($SQLfactura,$db,$ErrMsg,$DbgMsg,true);
			
			$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='".$tagorder."'";
			$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			if (DB_num_rows($Result)==1) {
				$myrowtags = DB_fetch_array($Result);
				$rfc=trim($myrowtags['taxid']);
				$keyfact=$myrowtags['address5'];
				$nombre=$myrowtags['tagname'];
				$area=$myrowtags['areacode'];
				$legaid=$myrowtags['legalid'];
				$tipofacturacionxtag=$myrowtags['typeinvoice'];
			}
			$Result = DB_Txn_Commit($db);
			$factelectronica= XSAInvoicing($InvoiceNo, $OrderNo, $Debtorno, $tipodefacturacion,$tagorder,$serie,$folio, $db);
			
			$param=array('in0'=>$empresa, 'in1'=>$nombre,'in2'=>$tipo,'in3'=>$myfile,'in4'=>$factelectronica);
			if ($tipofacturacionxtag==1){
				try{	
					$client = new SoapClient($_SESSION['XSA']."xsamanager/services/FileReceiverService?wsdl");
					$codigo=$client->guardarDocumento($param);
				}catch (SoapFault $exception) {
					$errorMessage = $exception->getMessage();
				}
				$liga=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
				$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' .$liga . '">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
			} elseif($tipofacturacionxtag==2){
				$XMLElectronico=generaXML($factelectronica,'ingreso',$tagorder,$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
				$liga="PDFInvoice.php?&clave=chequepoliza_sefia";
				$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo='.$OrderNo.'&TransNo=' . $InvoiceNo .'&Type='.$tipodefacturacion.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
			} elseif($tipofacturacionxtag==3){
				//$XMLElectronico=generaXML($factelectronica,'ingreso',$_SESSION['Tagref'],$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
				$liga="PDFInvoiceTemplate.php?clave=x";
				$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFInvoiceTemplate.php?OrderNo='.$OrderNo.'&TransNo=' . $InvoiceNo .'&Type='.$tipodefacturacion.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
			}else{
					$liga = GetUrlToPrintNu($_SESSION['Tagref'],$area,$legaid,10,$db);
					$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID .'&identifier='.$identifier . '&OrderNo='.$InvoiceNo.'&TransNo=' . $OrderNo .'&verplaca='.$verplaca .'&verkilometraje='.$verkm .'&verserie='.$verserie .'&vercomentarios='.$vercomentarios.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
			}
			
			echo $liga;
		 }
		
	}*/
    
    //echo '<meta http-equiv="Refresh" content="0; url="SupplierInvoice.php?' . SID .'&SupplierID='.$_SESSION['PO']->SupplierID.'&unidaddenegocio='.$unidaddenegocio.'">';
    //echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SupplierInvoice.php?GoodRecived=YES&SupplierID=".$_SESSION['PO']->SupplierID."&unidadnegocio=" . $unidaddenegocio."'>";
    //echo '<b>IR A ALTA DE FACTURA</a><br><br>';
        
    // Agregar aqui las opciones finales del proceso
    echo '<table class="table table-bordered" cellpadding=1 width="40%" border=1 style="border-collapse: collapse; border-color:lightgray;align:center;margin-left:auto;margin-right:auto;">';
    echo '<tr class="header-verde">';
    echo '<th align="center" width="18%" colspan=1 style="text-align:center;"><b>'._("Productos Recibidos").'</b></th>';
    //echo '<th align="center" width="18%" colspan=1 style="text-align:center;"><b>'._("Póliza").'</b></th>';
    echo '<th align="center" width="18%" colspan=1 style="text-align:center;"><b>'._("Acciones Siguientes").'</b></th>';
    echo '</tr>';
    echo '<tr>';

    $enc = new Encryption;
    $url = "&GRNNo=>".$GRN."&PONo=>".$PONo."&SupplierOrderNo=>".$SupplierPONo;
    $url = $enc->encode($url);
    $ligaImpresion= "URL=" . $url;

    echo '<td align="center" width="8%" style="text-align:center;">';
    echo '<a href="PDFGrn2.php?'.$ligaImpresion.'" target="_blank">Imprimir<img src="'.$rootpath.'/images/printer.png" title="Imprimir Comprobante de Productos Recibidos"></a>';
    echo '</td>';

    $enc = new Encryption;
    $url="FromCust=>1&ToCust=>1&PrintPDF=>Yes&type=>25" . "&TransNo=>" . $GRN . "&periodo=>" . "&trandate=>";
    $url = $enc->encode($url);
    $liga= "URL=" . $url;

    //echo "<td align='center' width='8%' style='text-align:center;'><a TARGET='_blank' href='PrintJournal.php?" . $liga . "'>" . _('') . "<img src='".$rootpath."/images/printer.png' title='" . _('Póliza') . "' alt=''></a></td>";
    
    $url = "&SupplierID=>".$_SESSION['PO']->SupplierID."&unidadnegocio=>".$unidaddenegocio;
    if ($_SESSION['AutoInvoiceSupplier']==1) {
        //echo '<td><a href="SupplierInvoice.php?' . SID .'&SupplierID='.$_SESSION['PO']->SupplierID.'&unidadnegocio='.$unidaddenegocio.'"><b>Alta de Factura</a></td>';
    } else {
        $url .= "&GoodRecived=>YES";
        //echo '<td><a href="SupplierInvoice.php?GoodRecived=YES' . SID .'&SupplierID='.$_SESSION['PO']->SupplierID.'&unidadnegocio=' . $unidaddenegocio.'"><b>Alta de Factura</a></td>';
    }
    $enc = new Encryption;
    //$url = "&ModifyOrderNumber=>" . $_SESSION['PO'.$identifier]->OrderNo;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
    
    //echo '<td><a href="SupplierInvoice.php?' . $liga . '"><b>Alta de Factura</a></td>';
    echo '<td><a style="text-align:center; margin:0 auto;" href="'. $rootpath . '/panel_recepcion_compra.php?' . SID . '"><b>' . _('Búsqueda de Ordenes de Compra'). '</b></a></td>';
    echo '</tr>';

    //Redireccionar  a Recibir productos- > No Disponible
    // echo '<tr>';
    // echo '<td></td>';
    // echo '<td></td>';
    // echo '<td><a style="text-align:center; margin:0 auto;" href="'. $rootpath . '/GoodsReceived.php?' . SID . '&PONumber='.$PONo.'"><b>' . _('Recibir Productos'). '</b></a></td>';
    // echo '</tr>';
    /*echo '<tr>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td><a style="text-align:center; margin:0 auto;" href="'. $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '"><b>' . _('Búsqueda de Ordenes de Compra'). '</b></a></td>';
    echo '</tr>';*/
    echo '</table>';
    
    //echo "<a href='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "'>" . _('Buscar otra Orden de Compra para recibir productos'). '</a>';
    
    if (isset($TieToOrderNumber) and $TieToOrderNumber<>'') {
        $paginapedidos=HavepermissionURL($_SESSION['UserID'], 4, $db);
        // echo '<a href="'.$paginapedidos.'?' . SID .'&ModifyOrderNumber='.$TieToOrderNumber.'"><b>REGRESAR AL PEDIDO DE VENTA NO. '.$TieToOrderNumber.'</b></a><br><br>';
    };

    $mailMessage .='</table>';
    $mailMessage .="<h3>Numero de Recepcion: $GRN</h3>";
    require_once('./includes/mail.php');
    if (empty($_SESSION['POReceiveEmail']) == false) {
        $mail   = new Mail();
        $to     = $_SESSION['POReceiveEmail'];
        $mail->setTo($to);
        $mail->setFrom("soporte@tecnoaplicada.com");
        $mail->setSender("Soporte");
        $mail->setSubject("Nueva Recepcion No. " . $GRN . ", Orden: " . $_SESSION['PO']->OrderNo);
        $mail->setHtml($mailMessage);
        $mail->send();
    }
    if (empty($_SESSION['ReceiveProdOCEmail']) == false) {
        //require_once('./includes/mail.php');//
        $mail   = new Mail();
        $to     = $_SESSION['ReceiveProdOCEmail'];
        $mail->setTo($to);
        $mail->setFrom("soporte@tecnoaplicada.com");
        $mail->setSender("Soporte");
        $mail->setSubject("Nueva Recepción No. " . $GRN . ", Orden: " . $_SESSION['PO']->OrderNo);
        $mail->setHtml($mailMessage);
        $mail->send();
    }
    unset($_SESSION['PO']->LineItems);
    unset($_SESSION['PO']);
    unset($_POST['ProcessGoodsReceived']);
    
    include('includes/footer_Index.inc');

    ?>
    <script type="text/javascript">
        /**
         * Función para regresar al panel
         * @return {[type]} [description]
         */
        function fnRegresarPanel() {
            window.open("panel_recepcion_compra.php", "_self");
        }
    </script>
    <?php
    if ($mensaje_emergente != "") {
        ?>
        <script type="text/javascript">
            var mensajeMod = '<?php echo $mensaje_emergente; ?>';
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, mensajeMod+'<p><a href=\'PDFGrn2.php?<?php echo $ligaImpresion; ?>\' target=\'_blank\'>Imprimir <img src=\'images/printer.png\' title=\'Imprimir Comprobante de Productos Recibidos\'></a></p>', '', 'fnRegresarPanel()');
        </script>
        <?php
    }

    exit();
} else {
    //var_dump('val:'.$TieToOrderNumber);
    // if (!isset($_POST['DefaultReceivedDate'])) {
    //     $_POST['DefaultReceivedDate'] = Date($_SESSION['DefaultDateFormat']);
    // }
    // //echo 'P:'.GetPeriod($_POST['DefaultReceivedDate'], $db);
    // echo '<table style="text-align:center; margin:0 auto;"><tr><td>'. _('Fecha Productos/Servicios Recibidos'). ':</td>';
    // echo '<td>';
    // //echo '<input type=hidden class=date alt="'.$_SESSION['DefaultDateFormat'] .'" maxlength=10 size=10 onChange="return isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" name=DefaultReceivedDate value="' . $_POST['DefaultReceivedDate'] . '">'.$_POST['DefaultReceivedDate'];

    // echo '<input type="text" id="datepicker" name="DefaultReceivedDate" value="' . $fechactual . '" READONLY=false> yyyy-mm-dd</input>';
    // echo '</td></tr>';
    // echo '</table><br>';

    echo '<div class="row"></div>';
    echo '<div class="col-md-4 col-xs-12">';
    echo '</div>';
    echo '<div class="col-md-4 col-xs-12" style="display: none;">';
    $fechactualFormat = date_create($fechactual);
    echo '<component-date-label label="Fecha Recepción: " id="DefaultReceivedDate" name="DefaultReceivedDate" value="'.date_format($fechactualFormat, 'd-m-Y').'"></component-date-label>';
    //echo '<component-label-text label="Fecha Recepción:" id="txtNombreUR" name="txtNombreUR" value="'.date_format($fechactualFormat, 'd-m-Y').'"></component-label-text>';
    echo '</div>';
    echo '<div class="col-md-4 col-xs-12">';
    echo '</div>';
    echo '<div class="row"></div>';

    //echo '<div class="centre"><input type=submit name=UpdateEnter Value=' . _('Rapida') . '><p>';
    
    //echo '<div class="centre" style="font-face:verdana;font-size:12px;color:#8A0808"><b>'. _('Nota: Si cambia las cantidades de recepción, <br> es necesario dar clic en el botón de Actualizar').'</b></div><br>';
    if (Havepermission($_SESSION['UserID'], 1432, $db) == 1) {
        // echo '<div class="centre" style="font-face:verdana;font-size:16px;color:#8A0808"><b>'.'<input type=checkbox name="GeneraCompraAuto" >'. _('Generar Nueva Orden de Compra').'</b></div><br>';//
    }
       
    //echo '<div class="centre" style="font-face:verdana;font-size:16px;color:#8A0808"><b>'. _('Cerrar Esta Orden De Compra').'</b></div><br>';

    echo '<div class="centre" style="font-face:verdana;font-size:14px;color:#8A0808">';
    if (abs($_SESSION['PO']->ExRate)==0) {
        prnMsg(_('La compra no tiene asignado tipo de cambio, por lo tanto no es posible realizar la recepción. Favor de verificar'), 'error');
    } else {
        //echo '<input type=submit name="ProcessGoodsReceived" style="font-weight:bold;" Value="' . _('Procesar Productos Recibidos') . '">';
        if (!empty(traeCuentaProveedor($_SESSION['PO']->SupplierID, $db)) && $mensajeCuentas == '') {
            if ($solo_servicios) {
                echo '<component-button type="submit" id="ProcessGoodsReceived" name="ProcessGoodsReceived" value="Recibir Servicio" onclick="if (validaRecepcion()) { return true; } else { return false; }"></component-button>';
            } else {
                echo '<component-button type="submit" id="ProcessGoodsReceived" name="ProcessGoodsReceived" value="Recibir Bienes" onclick="if (validaRecepcion()) { return true; } else { return false; }"></component-button>';
            }
        }

        echo '<component-button type="submit" id="btnRegresar" name="btnRegresar" value="Regresar" onclick="fnRegresar(); return false;"></component-button>';
    }
    //echo '<component-button type="submit" id="Update" name="Update" value="Actualizar"></component-button>';
    //echo '<component-button type="submit" id="Reset" name="Reset" value="Poner en Cero"></component-button>';
    echo '</div>';
    
    // echo "<br><div class='centre'><a href='$rootpath/PO_Header.php?" . SID . "&ModifyOrderNumber=".$_SESSION['PO']->OrderNo."'><b>" . _('Modificar Orden de Compra'). '</b></a></div>';
    // echo "<br><div class='centre'><a href='$rootpath/SelectOrderItemsV6_0.php?&ModifyOrderNumber=".$TieToOrderNumber."'><b>" . _('Ir a Pedido de Venta: ').$TieToOrderNumber. '</b></a></div>';
    
    //seccion de tablas para entrada rapida
    echo '<table border="0"  style="text-align:center; margin:0 auto; display: none;">';
        echo '<tr>';
            echo '<th colspan=3>';
                echo '<b>'._('Entrada Rapida').'</b>';
            echo '<th>';
        echo '</tr>';
        echo'<tr>
			<td>';
                echo '<table border=1>
					<tr>';
                    echo '<th>' . _('Codigo') . '</th>
					      <th>' . _('Cantidad') . '</th>
					</tr>';
    for ($i=1; $i<=10; $i++) {
        echo '<tr class="OddTableRow">';
        echo '		<td><input type="text" name="partuno_' . $i . '" size=21 maxlength=20></td>
							<td>
								<input class="number" onKeyPress="return restrictToNumbers(this, event)" type="text" name="qtyuno_' . $i . '" size=6 maxlength=6>
							</td>
							';
        echo'</tr>';
    }
                echo '</table>
			</td>
		';
        echo'
			<td>';
                echo '<table border=1>
					<tr>';
                    echo '<th>' . _('Codigo') . '</th>
					      <th>' . _('Cantidad') . '</th>
					</tr>';
    for ($i=1; $i<=10; $i++) {
        echo '<tr class="OddTableRow">';
        echo '		<td><input type="text" name="partdos_' . $i . '" size=21 maxlength=20></td>
							<td>
								<input class="number" onKeyPress="return restrictToNumbers(this, event)" type="text" name="qtydos_' . $i . '" size=6 maxlength=6>
							</td>
							';
        echo'</tr>';
    }
                echo '</table>
			</td>
		';
        echo'
			<td>';
                echo '<table border=1>
					<tr>';
                    echo '<th>' . _('Codigo') . '</th>
					      <th>' . _('Cantidad') . '</th>
					</tr>';
                    
    for ($i=1; $i<=10; $i++) {
        echo '<tr class="OddTableRow">';
        echo '		<td><input type="text" name="parttres_' . $i . '" size=21 maxlength=20></td>
							<td>
								<input class="number" onKeyPress="return restrictToNumbers(this, event)" type="text" name="qtytres_' . $i . '" size=6 maxlength=6>
								
							</td>
							';
        echo'</tr>';
    }
                echo '</table>
			</td>
		</tr>';
    echo '</table>';
    
    echo '<div class="centre" style="display: none;">';
        echo '<input style="font-size:12px;height:25px;width:180px;" type=submit name="UpdateER" VALUE="' . _('Actualizar Entrada Rapida') . '">';
        echo '<input style="font-size:12px;height:25px;width:180px;" type=submit name="Reset" VALUE="' . _('Reset') . '">';
    echo '</div>';
}
echo '</form>';

include('includes/footer_Index.inc');

// Nombre de la pagina
$tituloPagina = '';
if ($solo_servicios) {
    $tituloPagina = 'Recepción de Servicios';
} else {
    $tituloPagina = 'Recepción de Bienes';
}

// var_dump($_SESSION['PO']);
?>
<script>
$('#txtTituloEncabezadoPagina').empty();
$('#txtTituloEncabezadoPagina').append('<?php echo $tituloPagina; ?>');
function SelectCheckAuto(a)
{
    if (a==true){
        for (i=0;i<document.FDatosB.elements.length;i++) {
              if(document.FDatosB.elements[i].type == "checkbox") {
               tipo =document.FDatosB.elements[i].getAttribute('name');
            document.FDatosB.elements[i].checked=1;
              }
        }
    }else{
        for (i=0;i<document.FDatosB.elements.length;i++) {
              if(document.FDatosB.elements[i].type == "checkbox") {
               tipo =document.FDatosB.elements[i].getAttribute('name');
            document.FDatosB.elements[i].checked=0;
              }
        }   
    }
}
</script>
<script type="text/javascript">

function selAll(obj){
    var I = document.getElementById('I').value;
    //alert("valor de :" + I);
    for(i=0;i<I;i++){
        concatenar = "chk" + i
        chkobj = document.getElementById(concatenar);
        if(chkobj != null){
            chkobj.checked = obj.checked;
        }
        
    }
}

function validaRecepcion () {
    var linea= 0;
    var pendiente= 0;
    var cadenamensaje= "";

    $("input[id*=txtCantidadOrdenada_]").each(function(index, el) {
        linea= index+1;
        pendiente= parseFloat(this.value) - parseFloat($("#txtCantidadRecibida_"+linea).val());

        //alert("pendiente:"+ pendiente+ " recibir: "+$("#RecvQty_"+linea).val());

        if ($("#RecvQty_"+linea).val() > pendiente) {
            cadenamensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;En el renglon '+ linea +' esta capturando una cantidad mayor a lo pendiente por recibir.</p>'; 
        }
    });

    if (cadenamensaje != "") {
        muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', cadenamensaje);

        return false;
    }

    return true;
}

function fnRegresar () {
    window.open("panel_recepcion_compra.php", "_self");
}

</script>
