<?php

$sql = "Select *
	  from salesorders
	  where debtorno='" . $_SESSION['Items' . $identifier]->DebtorNo . "'
	  	and branchcode='" . $_SESSION['Items' . $identifier]->Branch . "'
	  order by orderno desc
	  limit 1";
$ErrMsg         = _('No existen ordenes de venta');
$GetOrderResult = DB_query($sql, $db, $ErrMsg);
if (DB_num_rows($GetOrderResult) > 0) {
    $myrowx  = DB_fetch_array($GetOrderResult);
    $orderno = $myrowx[0];
    if (isset($_SESSION['Items' . $identifier])) {
        unset($_SESSION['Items' . $identifier]->LineItems);
        unset($_SESSION['Items' . $identifier]);
    }
    $_SESSION['RequireCustomerSelection'] = 0;
    $_SESSION['Items' . $identifier]      = new cart;
    $OrderHeaderSQL                       = 'SELECT salesorders.debtorno,
								debtorsmaster.name,
								salesorders.branchcode,
								salesorders.customerref,
								salesorders.comments,
								salesorders.puestaenmarcha,
								salesorders.orddate,
								salesorders.ordertype,
								salestypes.sales_type,
								salesorders.shipvia,
								salesorders.deliverto,
								salesorders.deladd1,
								salesorders.deladd2,
								salesorders.deladd3,
								salesorders.deladd4,
								salesorders.deladd5,
								salesorders.deladd6,
								salesorders.contactphone,
								salesorders.contactemail,
								salesorders.freightcost,
								salesorders.deliverydate,
								paymentterms.terms,
								salesorders.fromstkloc,
								salesorders.printedpackingslip,
								salesorders.datepackingslipprinted,
								salesorders.quotation,
								salesorders.deliverblind,
								debtorsmaster.customerpoline,
								locations.locationname,
								custbranch.estdeliverydays,
								salesorders.salesman,
								locations.taxprovinceid,
								custbranch.taxgroupid,
								salesorders.placa,
								salesorders.serie,
								salesorders.kilometraje,
								salesorders.tagref,
								salesorders.currcode,
								salesorders.quotation,
								salesorders.paytermsindicator,
								custbranch.taxid as rfc,
								salesorders.vehicleno as vehiculo,
								idprospect,
								descclientecomercial,
								descclienteop,
								discount

					FROM salesorders left join locations on locations.loccode=salesorders.fromstkloc
						JOIN debtorsmaster on  salesorders.debtorno = debtorsmaster.debtorno
						JOIN salestypes ON salesorders.ordertype=salestypes.typeabbrev
						JOIN custbranch ON salesorders.debtorno = custbranch.debtorno AND salesorders.branchcode = custbranch.branchcode
						JOIN paymentterms ON salesorders.paytermsindicator=paymentterms.termsindicator
					WHERE salesorders.orderno = ' . $_GET['ModifyOrderNumber'];
    if ($debuggaz == 1) {
        echo $OrderHeaderSQL;
    }

    $ErrMsg          = _('La orden de venta no se puede  recuperar por que');
    $GetOrdHdrResult = DB_query($OrderHeaderSQL, $db, $ErrMsg);
    if (DB_num_rows($GetOrdHdrResult) == 1) {
        $myrow                                                = DB_fetch_array($GetOrdHdrResult);
        $_SESSION['Items' . $identifier]->Tagref              = $myrow['tagref'];
        $_SESSION['Items' . $identifier]->CurrAbrev           = $myrow['currcode'];
        $_SESSION['Items' . $identifier]->SelectedVehicle     = $myrow['vehiculo'];
        $_SESSION['Items' . $identifier]->prospectid          = $myrow['idprospect'];
        $_SESSION['Items' . $identifier]->OrderNo             = 0;
        $_SESSION['Items' . $identifier]->DebtorNo            = $myrow['debtorno'];
        $_SESSION['Items' . $identifier]->Branch              = $myrow['branchcode'];
        $_SESSION['Items' . $identifier]->CustomerName        = $myrow['name'];
        $_SESSION['Items' . $identifier]->CustRef             = $myrow['customerref'];
        $_SESSION['Items' . $identifier]->Comments            = stripcslashes($myrow['comments']);
        $_SESSION['Items' . $identifier]->PuestaEnMarcha      = stripcslashes($myrow['puestaenmarcha']);
        $_SESSION['Items' . $identifier]->PaymentTerms        = $myrow['paytermsindicator'];
        $_SESSION['Items' . $identifier]->DefaultSalesType    = $myrow['ordertype'];
        $_SESSION['Items' . $identifier]->SalesTypeName       = $myrow['sales_type'];
        $_SESSION['Items' . $identifier]->DefaultCurrency     = $myrow['currcode'];
        $_SESSION['Items' . $identifier]->ShipVia             = $myrow['shipvia'];
        $_SESSION['Items' . $identifier]->DeliverTo           = $myrow['deliverto'];
        $_SESSION['Items' . $identifier]->DeliveryDate        = ConvertSQLDate($myrow['deliverydate']);
        $_SESSION['Items' . $identifier]->DelAdd1             = $myrow['deladd1'];
        $_SESSION['Items' . $identifier]->DelAdd2             = $myrow['deladd2'];
        $_SESSION['Items' . $identifier]->DelAdd3             = $myrow['deladd3'];
        $_SESSION['Items' . $identifier]->DelAdd4             = $myrow['deladd4'];
        $_SESSION['Items' . $identifier]->DelAdd5             = $myrow['deladd5'];
        $_SESSION['Items' . $identifier]->DelAdd6             = $myrow['deladd6'];
        $_SESSION['Items' . $identifier]->PhoneNo             = $myrow['contactphone'];
        $_SESSION['Items' . $identifier]->Email               = $myrow['contactemail'];
        $_SESSION['Items' . $identifier]->Location            = $myrow['fromstkloc'];
        $_SESSION['Items' . $identifier]->LocationName        = $myrow['locationname'];
        $_SESSION['Items' . $identifier]->Quotation           = $myrow['quotation'];
        $_SESSION['Items' . $identifier]->FreightCost         = $myrow['freightcost'];
        $_SESSION['Items' . $identifier]->Orig_OrderDate      = $myrow['orddate'];
        $_SESSION['Items' . $identifier]->Quotation           = $myrow['quotation'];
        $_SESSION['Items' . $identifier]->Salesman            = $myrow['salesman'];
        $_SESSION['Items' . $identifier]->DeliverBlind        = $myrow['deliverblind'];
        $_SESSION['Items' . $identifier]->DefaultPOLine       = $myrow['customerpoline'];
        $_SESSION['Items' . $identifier]->DeliveryDays        = $myrow['estdeliverydays'];
        $_SESSION['Items' . $identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
        $_SESSION['Items' . $identifier]->TaxGroup            = $myrow['taxgroupid'];
        $_SESSION['Items' . $identifier]->CostumerRFC         = $myrow['rfc'];
        $_SESSION['Items' . $identifier]->placa               = $myrow['placa'];
        $_SESSION['Items' . $identifier]->serie               = $myrow['serie'];
        $_SESSION['Items' . $identifier]->kilometraje         = $myrow['kilometraje'];

        $_SESSION['Items' . $identifier]->deccomercial = $myrow['descclientecomercial'];
        $_SESSION['Items' . $identifier]->descCliente2 = $myrow['descclienteop'];
        $_SESSION['Items' . $identifier]->descCliente1 = $myrow['discount'];

        if ($_SESSION['Items' . $identifier]->descCliente1 == "") {
            $_SESSION['Items' . $identifier]->descCliente1 = 0;
        }

        if ($_SESSION['Items' . $identifier]->deccomercial == "") {
            $_SESSION['Items' . $identifier]->deccomercial = 0;
        }

        if ($_SESSION['Items' . $identifier]->descCliente2 == "") {
            $_SESSION['Items' . $identifier]->descCliente2 = 0;
        }

        $_SESSION['Items' . $identifier]->deccomercial = $_SESSION['Items' . $identifier]->deccomercial / 100;

        $SQLCurrency = "SELECT c.rate
			       FROM currencies c
			       WHERE c.currabrev='" . $_SESSION['Items' . $identifier]->CurrAbrev . "'";
        $ErrMsg      = _('No se pudo recuperar el valor de la moneda ');
        $GetCurrency = DB_query($SQLCurrency, $db, $ErrMsg);
        if (DB_num_rows($GetCurrency) == 1) {
            $myrowCurrency                                 = DB_fetch_array($GetCurrency);
            $_SESSION['Items' . $identifier]->CurrencyRate = $myrowCurrency['rate'];
        } else {
            $_SESSION['Items' . $identifier]->CurrencyRate = 1;
        }
        $LineItemsSQL = "SELECT salesorderdetails.orderlineno,
								salesorderdetails.stkcode,
								stockmaster.description,
								stockmaster.volume,
								stockmaster.kgs,
								stockmaster.units,
								stockmaster.serialised,
								stockmaster.controlled,
								stockmaster.nextserialno,
								stockmaster.eoq,
								salesorderdetails.unitprice,
								salesorderdetails.quantity,
								salesorderdetails.discountpercent,
								salesorderdetails.discountpercent1,
								salesorderdetails.discountpercent2,
								salesorderdetails.actualdispatchdate,
								salesorderdetails.qtyinvoiced,
								salesorderdetails.narrative,
								salesorderdetails.itemdue,
								salesorderdetails.poline,
								(locstock.quantity-locstock.ontransit-locstock.qtybysend) as qohatloc,
								stockmaster.mbflag,
								stockmaster.taxcatid,
								stockmaster.discountcategory,
								stockmaster.decimalplaces,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
								salesorderdetails.completed,
								locstock.loccode as fromstkloc1,
								locations.locationname as locationname1,
								salesorderdetails.quantity,
								salesorderdetails.warranty,
								stockcategory.redinvoice,
								stockcategory.disabledprice,
								stockmaster.taxcatidret,
								salesorderdetails.pocost,
								salesorderdetails.servicestatus,
								stockmaster.manufacturer,
								salesorderdetails.quantitydispatched,
								salesorderdetails.salestype,
								salesorderdetails.showdescrip,
								salesorderdetails.readOnlyValues,
							    salesorderdetails.modifiedpriceanddiscount,
								salesorderdetails.stkmovid,
								stockmaster.flagadvance
							FROM salesorderdetails
									INNER JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
									INNER JOIN locstock ON locstock.stockid = stockmaster.stockid AND locstock.loccode=salesorderdetails.fromstkloc
									INNER JOIN locations ON locstock.loccode=locations.loccode
									INNER JOIN stockcategory ON stockcategory.categoryid=stockmaster.categoryid
							WHERE  salesorderdetails.completed=0
									AND salesorderdetails.orderno =" . $_GET['ModifyOrderNumber'] . "
							ORDER BY salesorderdetails.orderlineno";
        if ($debuggaz == 1) {
            echo '<br>LINES: ' . $LineItemsSQL;
        }

        $ErrMsg          = _('Las partidas de la orden no se pueden recuperar, por que');
        $LineItemsResult = db_query($LineItemsSQL, $db, $ErrMsg);
        if (db_num_rows($LineItemsResult) > 0) {
            while ($myrow = db_fetch_array($LineItemsResult)) {
                if ($myrow['completed'] == 0) {
                    if ($_SESSION['TypeCostStock'] == 1) {
                        $EstimatedAvgCost = ExtractAvgCostXtag($_SESSION['Items' . $identifier]->Tagref, $myrow['stkcode'], $db);
                    } else {
                        $legalid          = ExtractLegalid($_SESSION['Items' . $identifier]->Tagref, $db);
                        $EstimatedAvgCost = ExtractAvgCostXlegal($legalid, $myrow['stkcode'], $db);
                    }
                    $existenciasalma   = ExistenciasXAlmacen($myrow['fromstkloc1'], $_GET['ModifyOrderNumber'], $myrow['stkcode'], $db);
                    $myrow['qohatloc'] = $myrow['qohatloc'] - $existenciasalma;
                    $unitprice         = $myrow['unitprice'];
                    if ($_SESSION['GetPriceFromPO'] == 1) {
                        $ratecompra = GetCurrencyRateByDate(date('Y-m-d'), $_SESSION['DefaultCurrencySale'], $db);
                        if ($ratecompra == '') {
                            $ratecompra = 1;
                        }
                        $ssql = 'SELECT CASE WHEN o.currcode!= "' . $_SESSION['Items' . $identifier]->CurrAbrev . '" then
											CASE WHEN actprice>0 THEN
												((actprice* (1+(margenaut/100))) + ((estimated_cost/quantityord)+((estimated_cost/quantityord)* ((margenaut/100)))))/' . $ratecompra . '
											ELSE
												((unitprice* (1+(margenaut/100))) +  ((estimated_cost/quantityord)+((estimated_cost/quantityord)* ((margenaut/100)))))/' . $ratecompra . '
											END
										ELSE
											CASE WHEN actprice>0 THEN
												(actprice* (1+(margenaut/100))) + ((estimated_cost/quantityord)+((estimated_cost/quantityord)* ((margenaut/100))))
											ELSE
												(unitprice* (1+(margenaut/100))) +  ((estimated_cost/quantityord)+((estimated_cost/quantityord)* ((margenaut/100))))
											END
										END,
										CASE WHEN o.currcode!= "' . $_SESSION['Items' . $identifier]->CurrAbrev . '" then
											CASE WHEN actprice>0 THEN
												((actprice) + ((estimated_cost/quantityord)))/' . $ratecompra . '
											ELSE
												((unitprice) +  ((estimated_cost/quantityord)))/' . $ratecompra . '
										END
										ELSE
											CASE WHEN actprice>0 THEN
												(actprice) + ((estimated_cost/quantityord))
											ELSE
												(unitprice) +  ((estimated_cost/quantityord))
											END
										END,tags.areacode';
                        $ssql .= ' FROM purchorders o JOIN suppliers on o.supplierno=suppliers.supplierid
									INNER JOIN purchorderdetails d ON o.orderno=d.orderno
									INNER JOIN locations l ON  l.loccode=o.intostocklocation
									INNER JOIN tags on tags.tagref=l.tagref
									INNER JOIN stockmaster s ON s.stockid=d.itemcode
									INNER JOIN stockcategory c ON s.categoryid=c.categoryid ';
                        $ssql .= ' WHERE s.stockid="' . $myrow['stkcode'] . '"';
                        $ssql .= ' 	AND requisitionno="' . $_GET['ModifyOrderNumber'] . '"';
                        $resultorders = DB_query($ssql, $db);
                        if (DB_num_rows($resultorders) > 0) {
                            $myrowdetails = DB_fetch_array($resultorders);
                            $Pricelista   = GetPriceWNoTAX($myrow['stkcode'], $_SESSION['Items' . $identifier]->DebtorNo,
                                $_SESSION['Items' . $identifier]->DefaultSalesType, $_SESSION['Items' . $identifier]->CurrAbrev,
                                $myrowdetails[2], $db);
                            $separa2       = explode('|', $Pricelista);
                            $bgcolorlista2 = $separa2[1];
                            $Pricelista    = $separa2[0];
                            if ($Pricelista < $myrowdetails[0]) {
                                if ($myrowdetails[0] > $unitprice) {
                                    $unitprice = $myrowdetails[0];
                                }
                                $myrow['pocost'] = $myrowdetails[1];
                            } else {
                                if ($myrowdetails[0] > $unitprice) {
                                    $unitprice = $myrowdetails[0];
                                }
                                $myrow['pocost'] = $myrowdetails[1];
                            }
                        }
                    }
                    $Discount = $myrow['discountpercent'];

                    if (($myrow['quantity'] - $myrow['qtyinvoiced']) < $myrow['quantitydispatched']) {
                        $myrow['quantitydispatched'] = $myrow['quantity'] - $myrow['qtyinvoiced'];
                    }

                    $sql = "Select discountInPriceListOnPrice,discountInComercialOnPrice
							FROM stockcategory
							INNER JOIN stockmaster
							ON stockcategory.categoryid = stockmaster.categoryid
							WHERE stockmaster.stockid = '" . $myrow['stkcode'] . "'";
                    $rsdisc                 = DB_query($sql, $db);
                    $directpriceInComercial = 0;
                    $directpriceInPriceList = 0;
                    if ($regdisc = DB_fetch_array($rsdisc)) {
                        $directpriceInComercial = $regdisc['discountInComercialOnPrice'];
                        $directpriceInPriceList = $regdisc['discountInPriceListOnPrice'];
                    }
                    $sql       = "Select * FROM salesorderdetailsgroups WHERE orderlineno = " . $myrow['orderlineno'] . " and orderno = " . $_GET['ModifyOrderNumber'];
                    $rsg       = DB_query($sql, $db);
                    $groupname = "";
                    if (DB_num_rows($rsg) > 0) {
                        $reg       = DB_fetch_array($rsg);
                        $groupname = $reg['groupname'];
                    }
                    $_SESSION['Items' . $identifier]->add_to_cart($myrow['stkcode'],
                        $myrow['quantity'],
                        $myrow['description'],
                        $unitprice,
                        $Discount,
                        $myrow['units'],
                        $myrow['volume'],
                        $myrow['kgs'],
                        $myrow['qohatloc'],
                        $myrow['mbflag'],
                        $myrow['actualdispatchdate'],
                        $myrow['qtyinvoiced'],
                        $myrow['discountcategory'],
                        $myrow['controlled'],
                        $myrow['serialised'],
                        $myrow['decimalplaces'],
                        $myrow['narrative'],
                        'No',
                        $myrow['orderlineno'],

                        $myrow['taxcatid'],
                        '',
                        ConvertSQLDate($myrow['itemdue']),
                        $myrow['poline'],
                        $EstimatedAvgCost,
                        $myrow['eoq'],
                        $myrow['nextserialno'],
                        $myrow['fromstkloc1'],
                        $myrow['locationname1'],
                        $myrow['discountpercent1'],
                        $myrow['discountpercent2'],
                        $myrow['warranty'],
                        0,
                        $myrow['redinvoice'],
                        $myrow['pocost'],
                        $myrow['disabledprice'],
                        $myrow['servicestatus'],
                        $Devolucion,
                        $totalsale,
                        1,
                        $myrow['salestype'],
                        $myrow['showdescrip'],
                        $_SESSION['Items' . $identifier]->deccomercial,
                        $directpriceInComercial,
                        $directpriceInPriceList,
                        $myrow['readOnlyValues'],
                        $_SESSION['Items' . $identifier]->descCliente1,
                        $_SESSION['Items' . $identifier]->descCliente2,
                        $myrow['modifiedpriceanddiscount'],
                        $groupname,
                        $myrow['flagadvance'],
                        $myrow['stkmovid']
                    );

                    if ($_SESSION['MultipleBilling'] == 1) {

                        $_SESSION['loccode'] = $myrow['fromstkloc1'];
                    }
                }

                if ($debuggaz == 1) {
                    echo '<br>LINEAXX: ' . $myrow['orderlineno'];
                }

                $LastLineNo = $myrow['orderlineno'];

                foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
                    if ($myrow['orderlineno'] == $OrderLine->LineNumber) {
                        $_SESSION['Items' . $identifier]->update_cart_item(
                            $OrderLine->LineNumber,
                            $OrderLine->Quantity,
                            $OrderLine->Price,
                            ($OrderLine->DiscountPercent),
                            $OrderLine->Narrative,
                            'Yes',
                            $OrderLine->ItemDue,
                            $OrderLine->POLine,
                            ($OrderLine->DiscountPercent1),
                            ($OrderLine->DiscountPercent2),
                            $OrderLine->AlmacenStock,
                            $OrderLine->warranty,
                            $OrderLine->servicestatus,
                            $myrow['quantitydispatched'],
                            0,
                            $OrderLine->showdescription,
                            $OrderLine->modifiedpriceanddiscount
                        );
                    }
                }

                $_SESSION['Items' . $identifier]->GetTaxes($LastLineNo);
                if ($myrow['taxcatidret'] > 0) {
                    $_SESSION['Items' . $identifier]->GetTaxesRet($LastLineNo);

                }
            }
            $_SESSION['Items' . $identifier]->LineCounter = $LastLineNo + 1;
        }
    }

}
