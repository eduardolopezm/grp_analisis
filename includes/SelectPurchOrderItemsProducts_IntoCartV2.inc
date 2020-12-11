<?php
/*
desarrollo- 09/SEPTIEMBRE/2013 - Correccion de calculo de precio a partir de ultimo costo, estaba multiplicando en vez de dividir los pesos a dolares  
/////
 */

$sql = 'SELECT legalid FROM tags,locations where tags.tagref=locations.tagref and loccode="'.$_SESSION['PO'.$identifier]->Location.'"';
$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
$myrowloc = DB_fetch_array($result);
$legalid = $myrowloc['legalid'];

if($_SESSION['UserID'] == "admin"){
    //echo 'entro1';
}

while ($i<=$_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i]!='') 
{
		
    $ItemCode = $_POST['part_' . $i];

    if(!isset($_POST['QuickEntry']) and isset($_POST['part_' . $i]) and $_POST['part_' . $i]!=''){
        $Quantity = 1;
    }else{
        $Quantity = $_POST['qty_' . $i];
    }

    $QuickEntryLoc =$_POST['Stock_' . $i];
    $NewPrecio =$_POST['price_' . $i];
    //echo 'precio'.$NewPrecio;
    $NewDesc1 =$_POST['desc1_' . $i];
    $NewDesc2 =$_POST['desc2_' . $i];
    $NewDesc3 =$_POST['desc3_' . $i];
    $NewEstimatedCost =$_POST['estimated_cost_' . $i];

    $NewDescXmonto = $_POST['descmonto_'.$i];
    //echo 'descuento'.$NewDescXmonto;
    if (is_numeric($NewDescXmonto)) {
        $NewDesc1 = $NewDescXmonto/$NewPrecio*100;

        $NewDesc2 = 0;
        $NewDesc3 = 0;

        $_POST['desc1_'.$i] = $NewDesc1;
        $_POST['desc2_'.$i] = $NewDesc2;
        $_POST['desc3_'.$i] = $NewDesc3;
    }

    if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false){
        if (count($_SESSION['PO'.$identifier]->LineItems)!=0){
            foreach ($_SESSION['PO'.$identifier]->LineItems AS $OrderItem) {
                /* do a loop round the items on the order to see that the item
                is not already on this order */
                if (($OrderItem->StockID == $ItemCode) and ($OrderItem->Deleted==false)) {
                    $AlreadyOnThisOrder = 1;
                    prnMsg( _('El Codigo') . ' ' . $ItemCode . ' ' . _('existe ya en esta orden') . '. ' . _('El sistema no permite el mismo codigo en la misma orden') . '. ' . _('Sin embargo puede modificar la cantidad de la partida'),'error');
                }
            } /* end of the foreach loop to look for preexisting items of the same code */
        }
    }
    
    // se agrega consulta para extraer el codigo de producto por codigo de barras
    if ($_SESSION['SearchBarcode']==0){
            $SQL="SELECT stockid,manufacturer FROM stockmaster where stockid='".$ItemCode."'";
            $ErrMsg = _('No se encontraron resgitros para el codigo') . ': ' . $ItemCode ;
            $DbgMsg = _('El SQL utilizado es');
            $result =DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            $myrowstock = DB_fetch_row($result);
            $ItemCode= $myrowstock[0];
            $manufacturer= $myrowstock[0];
    }

    if ($AlreadyOnThisOrder!=1 and $Quantity>0)
    {
        $purchdatasql='SELECT COUNT(supplierno)
                        FROM purchdata
                        WHERE purchdata.supplierno = "' . $_SESSION['PO'.$identifier]->SupplierID . '"
                        AND purchdata.stockid="'. $ItemCode . '"';
        
        //echo $purchdatasql;
        $purchdataresult=DB_query($purchdatasql, $db);
        $myrow=DB_fetch_row($purchdataresult);

        //SI tiene definicion de datos de compra del proveedor
        if ($myrow[0]>0) {
            $sql = "SELECT stockmaster.description,
                    stockmaster.stockid,
                    stockmaster.units,
                    stockmaster.decimalplaces,
                    stockmaster.kgs,
                    stockmaster.netweight,
                    stockcategory.stockact,
                    chartmaster.accountname,
                    purchdata.price,
                    purchdata.conversionfactor,
                    purchdata.supplierno,			
                    purchdata.supplierdescription,
                    purchdata.suppliersuom,
                    purchdata.suppliers_partno,
                    purchdata.leadtime,
                    stockmaster.serialised,
                    stockmaster.controlled,
                    stockmaster.barcode
            FROM stockcategory,
                    chartmaster,
                    stockmaster LEFT JOIN purchdata
            ON stockmaster.stockid = purchdata.stockid
            AND purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
            WHERE chartmaster.accountcode = stockcategory.stockact
                    AND stockcategory.categoryid = stockmaster.categoryid
                    AND stockmaster.stockid = '". $ItemCode . "'
                    AND purchdata.effectivefrom = 
                            (SELECT max(effectivefrom) 
                                    FROM purchdata 
                                    WHERE purchdata.stockid='". $ItemCode . "'
                                    AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "')";
                
            if ($_SESSION['ProhibitPurchD']==1){
                $sql = $sql. " AND stockmaster.mbflag!='D'";
             }
        } else {
            $sql='SELECT stockmaster.description,
                    stockmaster.stockid,
                    stockmaster.units,
                    stockmaster.decimalplaces,
                    stockmaster.kgs,
                    stockmaster.netweight,
                    stockcategory.stockact,
                    chartmaster.accountname,
                    stockmaster.serialised,
                    stockmaster.controlled,
                    stockmaster.barcode						
            FROM stockcategory,
                    chartmaster,
                    stockmaster
            WHERE chartmaster.accountcode = stockcategory.stockact
                    AND stockcategory.categoryid = stockmaster.categoryid';
            if ($_SESSION['SearchBarcode']==0){
                $sql=$sql.' AND stockmaster.stockid = "'. $ItemCode . '"';
            }else{
                $sql = $sql ." AND (stockmaster.barcode LIKE  '%". $ItemCode ."%' or stockmaster.stockid LIKE  '%". $ItemCode ."%')";
            }

            if ($_SESSION['ProhibitPurchD']==1){
                $sql = $sql. " AND stockmaster.mbflag!='D'";
            }
        }

        $ErrMsg = _('Los datos de compra del proveedor para el producto:') . ' ' . $ItemCode . ' ' . _('no pudieron recuperarse');
        $DbgMsg = _('El SQL FALLO...');
        $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

        if ($myrow = DB_fetch_array($result1))
        {
            if ($_SESSION['AplicaDevolucion']==1){
                // AGREGAR EL DESCUENTO Y % DE DEVOLUCION
                $percentdevolucion=TraePercentDevXSupplier($_SESSION['PO'.$identifier]->SupplierID,$myrow['stockid'],$myrow['manufacturer'],$_SESSION['PO'.$identifier]->DefaultSalesType,$db);
                $separa = explode('|',$percentdevolucion);
                $Devolucion = $separa[0]/100;
                $NewDesc1=$separa[1]/100;
                $totalpurch=$separa[2];
            }else{
                $Devolucion=0;
            }

            //CAPTURA FACTOR DE CONVERSION...
            $factordeConversion = $myrow['conversionfactor'];
            
            if (!is_numeric($factordeConversion)) 
                $factordeConversion = 1;

            if (is_numeric($myrow['price']))
            {
                //SOLO ENTRA AQUI EN CASO DE HABER ENCONTRADO DATOS DE COMPRA...
                $units = $myrow['units'];

                //Si el precio lo capturo el usuario, nos vamos con este... si no tomamos
                // el ultimo costo y despues el precio de datos de compra de este proveedor...
                if ($NewPrecio == "" || $NewPrecio==0) 
                {
                    $isLastCost = false;

                    //buscar ultimo costo
                    $qry = "Select lastcost FROM stockcostsxlegalnew
                            WHERE stockid='$ItemCode'
                            AND legalid=$legalid";
                    
                    $rlast = DB_query($qry,$db);
                    if($lastcost = DB_fetch_array($rlast)) {
                        $stkprice = $lastcost[0]*$factordeConversion;
                        if($lastcost[0] > 0) {
                            $isLastCost = true;
                        }
                    }

                    // Obtener el costo de la ultima compra si viene por variable de configuracion ...
                    if (empty($_SESSION['GetLastCostFromPurchOrder']) == false) {
                        $sqlPo = "SELECT unitprice FROM purchorderdetails WHERE itemcode = '$ItemCode' ORDER BY podetailitem DESC LIMIT 1";
                        $rsPo = DB_query($sqlPo, $db);
                        if ($rowPo = DB_fetch_array($rsPo)) {
                            $stkprice = $rowPo['unitprice'];
                            $isLastCost = false;
                        }
                    }

                    if ($stkprice == 0 || $stkprice=="")
                        $stkprice = $myrow['price'];

                    # ############################################ #
                    # CAMBIOS DE ACUERDO A LA MONEDA DEL PROVEEDOR #
                    # ############################################ #

                    // Dejar por defecto la moneda que tiene la orden de compra en la moneda del proveedor
                    $suppCurrcode = $_SESSION['PO'.$identifier]->CurrCode;
                    
                    // Obtenemos la moneda del proveedor preferente
                    $sqlSupp = "SELECT currcode FROM suppliers WHERE supplierid = '{$myrow['supplierno']}'";
                    $rsSupp = DB_query($sqlSupp, $db);
                    
                    if($rowSupp = DB_fetch_array($rsSupp)) {
                        if(empty($rowSupp['currcode']) == false) {
                            $suppCurrcode = $rowSupp['currcode'];
                        }
                    }

                    if($_SESSION['UserID'] == 'admin') {
//                        echo "Moneda OC:" . $_SESSION['PO'.$identifier]->CurrCode . "<br/>";
//                        echo "Moneda PRO:" . $suppCurrcode . "<br/>";
                    }

                    // Si el precio viene del ultimo costo calcular de esta forma
                    if($isLastCost) {
                        $tc = 1 / $_SESSION['PO'.$identifier]->ExRate;
                        $stkprice = $stkprice / $tc;
                    } else {
                        // Si viene el precio de los datos de compra
                        // Si la moneda de la orden de compra no es pesos mexicanos y la moneda del
                        // proveedor es pesos mexicanos hacer la conversion por division
                        if($_SESSION['PO'.$identifier]->CurrCode != 'MXN' && $suppCurrcode == 'MXN') {
                            $tc = 1 / $_SESSION['PO'.$identifier]->ExRate;
                            $stkprice = $stkprice / $tc;
                        }

                        // Si la moneda de la orden de compra es pesos mexicanos y la moneda del
                        // proveedor no es pesos mexicanos hacer la conversion por multiplicacion
                        if($_SESSION['PO'.$identifier]->CurrCode == 'MXN' && $suppCurrcode != 'MXN') {
                            $rateSupp = $_SESSION['PO'.$identifier]->ExRate;
                            $sql = "SELECT rate FROM tipocambio WHERE currency = '$suppCurrcode' ORDER BY fecha DESC LIMIT 1";
                            $rsTc = DB_query($sql, $db);
                            
                            if($rowTc = DB_fetch_array($rsTc)) {
                                $rateSupp = number_format($rowTc['rate'], 6);
                            }

                            $tc = 1 / $rateSupp;
                            $stkprice = $stkprice * $tc;
                        }
                    }

                } else {
                    $stkprice = $NewPrecio;
                }
                
                if($_SESSION['UserID'] == "admin"){
                        //echo '<br><pre> metodo   '.$myrow['stockact'];
                }
                
                $_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
                        $myrow['stockid'],
                        $myrow['serialised'], /*Serialised */
                        $myrow['controlled'], /*Controlled */
                        $Quantity, /* Qty */
                        $myrow['description'],
                        $stkprice,
                        0,
                        0,
                        0,
                        $myrow['units'],
                        $myrow['stockact'],
                        $_SESSION['PO'.$identifier]->deliverydate,
                        0,
                        0,
                        0,
                        0,
                        0,
                        $myrow['accountname'],
                        $myrow['decimalplaces'],
                        $ItemCode,
                        $myrow['suppliersuom'],
                        $myrow['suppliers_partno'],
                        $Quantity*$stkprice,
                        $myrow['leadtime'],
                        '',
                        $myrow['netweight'],
                        $myrow['kgs'],
                        '',
                        $Quantity,
                        $Quantity*$stkprice,
                        '', 
                        '',
                        $myrow['barcode'],
                        $Devolucion,
                        $totalpurch,
                        0,
                        $NewEstimatedCost
                        );
            } else { /*There was no supplier purchasing data for the item selected so enter a purchase order line with zero price */

                if ($NewPrecio=="" || $NewPrecio==0){
                    //buscar ultimo costo
                    $qry = "Select lastcost FROM stockcostsxlegalnew
                            WHERE stockid='$ItemCode'
                            and legalid=$legalid";
                    
                    $rlast = DB_query($qry,$db);
                    $lastcost = DB_fetch_array($rlast);
                    $NewPrecio = $lastcost[0];

                    // Obtener el costo de la ultima compra si viene por variable de configuracion ...
                    if (empty($_SESSION['GetLastCostFromPurchOrder']) == false) {
                        $sqlPo = "SELECT unitprice FROM purchorderdetails WHERE itemcode = '$ItemCode' ORDER BY podetailitem DESC LIMIT 1";
                        $rsPo = DB_query($sqlPo, $db);
                        if ($rowPo = DB_fetch_array($rsPo)) {
                            $NewPrecio = $rowPo['unitprice'];
                        }
                    }

                    if ($_SESSION['PO'.$identifier]->CurrCode!="MXN"){
                        $NewPrecio *= $_SESSION['PO'.$identifier]->ExRate;
                    }
                }

                if($_SESSION['UserID'] == "admin"){
                    //echo '<br><pre> metodop   '.$myrow['stockact'];
                }
            
                $_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
                        $myrow['stockid'],
                        $myrow['serialised'], /*Serialised */
                        $myrow['controlled'], /*Controlled */
                        $Quantity, /* Qty */
                        $myrow['description'],
                        $NewPrecio,
                        $NewDesc1,
                        $NewDesc2,
                        $NewDesc3,
                        $myrow['units'],
                        $myrow['stockact'],
                        $_SESSION['PO'.$identifier]->deliverydate,
                        0,
                        0,
                        0,
                        0,
                        0,
                        $myrow['accountname'],
                        $myrow['decimalplaces'],
                        $ItemCode,
                        '',
                        '',
                        0,
                        0,
                        '',
                        0,
                        0,
                        0,
                        $NewPrecio,
                        $NewPrecio*$Quantity,
                        '', 
                        '',
                        $myrow['barcode'],
                        $Devolucion,
                        $totalpurch,
                        0,
                        $NewEstimatedCost
                        );
            }

        } else {
            prnMsg (_('Este codigo') . ' ' . $ItemCode . ' ' . _('no existe en la base de datos o bien es un producto que no esta permitido en compras.Verifique'),'error');            
        }
    } /* end of if not already on the order */
    $i++;
}

unset($_POST['EnterLine']);
unset($_POST['NewItem']);

?>