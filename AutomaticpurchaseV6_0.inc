<?php



$ProveedorCompraANT='';
$sendEmailCompras = false;
$currcode_ = $_SESSION['Items'.$identifier]->CurrAbrev;
$arrOrdenesGeneradas=array();
$arrEnviarmail=array();
$demandQtyQuotations = $_SESSION['DemandQtyQuotations'];
if(empty($demandQtyQuotations)) {
	$demandQtyQuotations = "0"; // Por defecto pedidos cerrados
}

foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
    //valida si el checkbox esta activo
	
	$checcompraactivo=$_POST['itemordencompra_' . $OrderLine->LineNumber];
    $conprioridad = $_POST['itemordencompraprioridad_'.$OrderLine->LineNumber];
    if ($conprioridad)
    	$sendEmailCompras = true;
    if ($checcompraactivo==true){
    	
        //extrae el proveedor preferente por producto y moneda de pedido
    	$SQL = "SELECT supplierno
            	FROM purchdata INNER JOIN suppliers ON suppliers.supplierid = purchdata.supplierno
        		WHERE stockid = '" . $OrderLine->StockID . "' 
        			AND preferred = 1
        			AND pcurrcode = '$currcode_'";
    	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
        if(DB_num_rows($Result) > 0) {
            $CompraRow = DB_fetch_row($Result);
            $ProveedorCompra = $CompraRow[0];
        } else {
            // En caso de que no exista proveedor preferente para esa moneda trae el proveedor preferente y extrae la moneda a la que debe generar la compra
       		$SQL="SELECT supplierno,pcurrcode
              	  FROM purchdata 
       			  WHERE stockid='".$OrderLine->StockID."'
              		AND preferred = 1";
            $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
             if (DB_num_rows($Result)>0){
                $CompraRow = DB_fetch_row($Result);
                $ProveedorCompra = $CompraRow[0];
                $currcode_=$CompraRow[1];
            }
        }// fin de trae informacion de proveedor preferente
        
        // Si no encontro proveedor preferente, realiza compra con default de acuerdo a moneda
		if($ProveedorCompra==''){
	    	if($currcode_ != $_SESSION['CountryOfOperation']) {
        		// Proveedor con Tipo de Moneda USD
        		$ProveedorCompra = $_SESSION['SupplierDefaultUSD'];
	    	} else {
		    	// Proveedor con Tipo de Moneda MXN
		    	$ProveedorCompra = $_SESSION['SupplierDefault'];
	    	}
	    	prnMsg(_('El producto no tiene proveedor asignado favor de vefificar el codigo: ') . $OrderLine->StockID . _(' La orden sera generada con el proveedor defecto ' . $ProveedorCompra), 'sucess');
		}
		
        // valida que la hora no halla pasdo la hora de req.
        if (Date('H')>$_SESSION['HourReq']){
            $fechasolicito=date("Y/m/d");
	    $fechasolicito = dateadd_dias($fechasolicito,1,0,0,0,0,0);
            $fechasolicito="'".$fechasolicito."'";
        }else{
            $fechasolicito='now()';
        }
		$SQL="UPDATE salesorders
			  SET quotedate=".$fechasolicito."
			  WHERE orderno=".$_SESSION['Items'.$identifier]->OrderNo;
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		$SQL="SELECT purchorders.supplierno
              FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
              WHERE purchorders.requisitionno='".$_SESSION['Items'.$identifier]->OrderNo."'
                    AND purchorderdetails.itemcode='".$OrderLine->StockID."'";
        $Resultverificacomprauno = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        // si existe busca proveedor para ese pedido de venta
        if (DB_num_rows($Resultverificacomprauno)!=0){
	    	$myrowcomprauno = DB_fetch_row($Resultverificacomprauno);
            $ProveedorCompra=$myrowcomprauno[0];
		}
		
        //valida si no existe orden de compra para este producto con esta orden de venta con este provedor
        $SQL="SELECT purchorderdetails.orderno
              FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
              WHERE purchorders.requisitionno='".$_SESSION['Items'.$identifier]->OrderNo."'
                    AND purchorders.supplierno='".$ProveedorCompra."'
                    AND purchorderdetails.itemcode='".$OrderLine->StockID."'
                    AND purchorderdetails.completed=0 
                    AND purchorders.currcode='".$currcode_."'";
        
        $Resultverificacompra = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        // si no existe
	if (DB_num_rows($Resultverificacompra)==0){
		
    	$Result = DB_Txn_Begin($db);
            // extrae los datos de la localidad de almacen
		$SQL="select * from locations where loccode='".$OrderLine->AlmacenStock."'";
		$Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		$CompraAlmacenRow = DB_fetch_row($Resultalmacen);
		$DelAdd1 = $CompraAlmacenRow[2];
		$DelAdd2 = $CompraAlmacenRow[3];
		$DelAdd3 = $CompraAlmacenRow[4];
		$DelAdd4 = $CompraAlmacenRow[5];
		$DelAdd5 = $CompraAlmacenRow[6];
            $DelAdd6 = $CompraAlmacenRow[7];
            
            $sql = "SELECT  if(pcurrcode is null, currcode, pcurrcode) as pcurrcode
					FROM purchdata
					INNER JOIN suppliers ON suppliers.supplierid = purchdata.supplierno
					WHERE stockid = '".$OrderLine->StockID."'";
            $Resultverificacomprados = DB_query($sql, $db);
            if(DB_num_rows($Resultverificacomprados) > 0){
            	$rows = DB_fetch_array($Resultverificacomprados);
            	if($rows['pcurrcode'] == "MXN"){
            		$moneda = $rows['pcurrcode'];
            		$rate = 1;
            	}else{
            		$sql = "SELECT rate
							FROM tipocambio
							WHERE currency = '".$rows['pcurrcode']."'
							ORDER BY fecha desc
							LIMIT 1";
					$Resultverificacomprados2 = DB_query($sql, $db);
					$rows2 = DB_fetch_array($Resultverificacomprados2);
					$rate = $rows2['rate'];
					$moneda = $rows['pcurrcode'];
            	}
            }else{
            	
            	if($_SESSION['Items'.$identifier]->CurrencyRate == 1){
            		$moneda = "MXN";
            		$rate = 1;
            	}else{
            		$rate = $_SESSION['Items'.$identifier]->CurrencyRate;
            		$moneda = "USD"; 
            	}
            }
           
            
            $SQL="SELECT purchorderdetails.orderno
              	  FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
            		AND (quantityord-quantityrecd)>0
              	  WHERE purchorders.requisitionno='".$_SESSION['Items'.$identifier]->OrderNo."'
                  	AND purchorders.supplierno='".$ProveedorCompra."'";
            
            $SQL=" SELECT purchorderdetails.orderno,recepcion
            	   FROM purchorders INNER JOIN (
            			SELECT sum(quantityrecd) as recepcion,orderno
            			FROM purchorderdetails
            			GROUP BY orderno
            		) as purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
            		WHERE purchorders.requisitionno='".$_SESSION['Items'.$identifier]->OrderNo."'
            			AND recepcion=0
                  		AND purchorders.supplierno='".$ProveedorCompra."'";
            	
             $Resultverificacomprados = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
             if (DB_num_rows($Resultverificacomprados)==0){
             	//inserta la orden de compra nueva para este proveedor
             	$sql = "INSERT INTO purchorders (
                        supplierno,
                       servicetype,
                        orddate,
                        rate,
                        initiator,
                        requisitionno,
                        intostocklocation,
                        deladd1,
                        deladd2,
                        deladd3,
                        deladd4,
                        deladd5,
                        deladd6,
                        version,
                        realorderno,
                        revised,
                        status,
                        stat_comment,
                        deliverydate,
                        tagref,
                		currcode
                        )
                		VALUES(
                        '" . $ProveedorCompra. "',
                   		2,
                        ".$fechasolicito.",
                        '".$rate."',
                        '" . $_SESSION['UserID'] . "',
                        '" .$_SESSION['Items'.$identifier]->OrderNo. "',
                        '" . $OrderLine->AlmacenStock . "',
                        '" . $DelAdd1 . "',
                        '" . $DelAdd2 . "',
                        '" . $DelAdd3 . "',
                        '" . $DelAdd4 . "',
                        '" . $DelAdd5 . "',
                        '" . $DelAdd6 . "',
                        '0',			
                        '" . $_SESSION['Items'.$identifier]->OrderNo. "',
                        now(),";
                		$sql.="'" . 'Pending'. "',";
                		$sql.="		'Compra automatica',
                        'now()',
                        ".$_SESSION['Items'.$identifier]->Tagref.",
                        '".$moneda."'		
                		)";
                		
                $ErrMsg =  _('The purchase order header record could not be inserted into the database because');
                $DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
               
                $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                $OrderNoCompra = DB_Last_Insert_ID($db,'purchorders','orderno');
                $arrOrdenesGeneradas[] = $OrderNoCompra;
                if ($sendEmailCompras)
                	$arrEnviarmail[$OrderNoCompra] = 1;
                else
                	$arrEnviarmail[$OrderNoCompra] = 0;
                	$validaentra=false;
            	}else{
                	$myrowcompra = DB_fetch_row($Resultverificacomprados);
              		$OrderNoCompra=$myrowcompra[0];
               	 	$validaentra=true;
                if (!in_array($OrderNoCompra,$arrOrdenesGeneradas)){
                	$arrOrdenesGeneradas[] = $OrderNoCompra;
                 	if ($sendEmailCompras)
                 		$arrEnviarmail[$OrderNoCompra] = 1;
                 	else
                 		$arrEnviarmail[$OrderNoCompra] = 0;
                 }
            }//Fin de alta de oc
            $sendEmailCompras=false;
            //extrae referencia de orden de compra
            $sql = "SELECT stockmaster.description,
						   purchdata.suppliers_partno,
						   1 as pkg_type, /*stockmaster.pkg_type,*/
						   stockmaster.units,
						   stockmaster.netweight,		
						   stockmaster.kgs,
						   stockmaster.volume,
                           stockcategory.stockact,
                           stockmaster.manufacturer,
                           purchdata.price
					FROM purchdata INNER JOIN stockmaster ON purchdata.stockid=stockmaster.stockid,stockcategory
					WHERE stockcategory.categoryid = stockmaster.categoryid
                    	AND purchdata.stockid='" . $OrderLine->StockID . "'
                        AND purchdata.supplierno='".$ProveedorCompra."'
                        AND purchdata.pcurrcode='".$currcode_."'";	$ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $OrderLine->StockID  . ' ' . _('no hay resultados');
			$DbgMsg = _('El SQL utilizado es');
			$ResultProveedorcompra =DB_query($sql,$db,$ErrMsg,$DbgMsg);
			$myrow = DB_fetch_row($ResultProveedorcompra);
			$_POST['ItemDescription'] = $myrow[0];
			$_POST['suppliers_partno'] = $myrow[1];
			$_POST['package'] = $myrow[2];
			$_POST['uom'] = $myrow[3];
			$_POST['nw'] = $myrow[4];
			$_POST['gw'] = $myrow[5];
			$_POST['cuft'] = $myrow[6];
         	$sqlCAT = "SELECT stockcategory.stockact,stockmaster.mbflag
					   FROM stockmaster,stockcategory 
					   WHERE stockcategory.categoryid = stockmaster.categoryid
            				AND stockmaster.stockid='" . $OrderLine->StockID . "'";
			$ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $OrderLine->StockID  . ' ' . _('no hay resultados');
			$DbgMsg = _('El SQL utilizado es');
			$ResultProveedorcompraCAT =DB_query($sqlCAT,$db,$ErrMsg,$DbgMsg);
			$myrowCAT = DB_fetch_row($ResultProveedorcompraCAT);
			$CompraStockidglcode=$myrowCAT[0];
			$mbflag=$myrowCAT[1];
      		$price=$myrow[9];
            //Bandera de solo comprar faltante y no la totalidad del pedido
            if($_SESSION['Autoqtypurch']==1){
            	$sqlLL = "SELECT ifnull(sum(ifnull(salesorderdetails.quantity-salesorderdetails.qtyinvoiced,0)),0) AS dem
						  FROM salesorderdetails INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
					      WHERE salesorderdetails.fromstkloc='" . $OrderLine->AlmacenStock  . "' 
					      		AND salesorderdetails.completed=0 
					      		AND salesorders.quotation in ($demandQtyQuotations)
					      		AND salesorders.orderno<>".$_SESSION['Items'.$identifier]->OrderNo." 
					      		AND salesorderdetails.stkcode='" . $OrderLine->StockID . "'";
            	$ordenescerradas=false;
            	$ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
            	$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
            	if (DB_num_rows($DemandResult)==1){
            		$DemandRow = DB_fetch_row($DemandResult);
            		$DemandQtySales =  $DemandRow[0];
            		$ordenescerradas=true;
            	} else {
            		$DemandQtySales =0;
            	}
            	$SQL = "SELECT stockmaster.description,
							(locstock.quantity-locstock.ontransit)-".$DemandQtySales." as quantity ,
							stockmaster.mbflag
		 				FROM locstock INNER JOIN stockmaster ON stockmaster.stockid=locstock.stockid
						WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
							AND locstock.loccode='" . $OrderLine->AlmacenStock . "'";
            	$ErrMsg = _('No se puede recuperar la cantidad para facturar');
            	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            	$CheckNegRow = DB_fetch_array($Result);
            	$cantidaddisponible=$CheckNegRow['quantity'];
            	$cantidadsol=$OrderLine->Quantity-$cantidaddisponible;
            	if($cantidadsol<=0){
            		$cantidadsol=$OrderLine->Quantity;
            	}
            }else{
	            $cantidadsol=$OrderLine->Quantity;
	            if($cantidadsol<=0){
	                $cantidadsol=$OrderLine->Quantity;
	            }
            }
            // AGREGAR EL DESCUENTO Y % DE DEVOLUCION
            // verifica el porcetaje de devolucion
            $percentdevolucion=TraePercentDevXSupplier($ProveedorCompra,$OrderLine->StockID,$myrow[8],$_SESSION['Items'.$identifier]->DefaultSalesType,$db);
            //echo $percentdevolucion;
            $separa = explode('|',$percentdevolucion);
            $Devolucion = $separa[0]/100;
            $Discount=$separa[1]/100;
            $totalpurch=$separa[2];
            // Agregue validacion para que si ya tenia una orden de compra asociada la misma partida la nueva oc se genere solo por la cantidad faltante
            $SQL="SELECT sum(quantityord)
              	  FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
              	  WHERE purchorders.requisitionno='".$_SESSION['Items'.$identifier]->OrderNo."'
                    AND purchorders.supplierno='".$ProveedorCompra."'
                    AND purchorderdetails.itemcode='".$OrderLine->StockID."'
                    AND purchorderdetails.completed=1 ";
            $Resulttraecompras= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            $myrowcompraAnt = DB_fetch_row($Resulttraecompras);
            $CompradasANT=$myrowcompraAnt[0];
            if($CompradasANT>0){
            	if(($cantidadsol-$CompradasANT)>0){
           			$cantidadsol=$cantidadsol-$CompradasANT;
            	}
            }
            if ($mbflag=='B'){
            	$sql = "INSERT INTO purchorderdetails (
	                        orderno,
	                        itemcode,
	                        deliverydate,
	                        itemdescription,
	                        glcode,
	                        unitprice,
	                        quantityord,
	                        shiptref,
	                        jobref,
	                        itemno,
	                        uom,
	                        suppliers_partno,
	                        subtotal_amount,
	                        package,
	                        pcunit,
	                        nw,
	                        gw,
	                        cuft,
	                        total_quantity,
	                        total_amount,
	                        discountpercent1,
	                        discountpercent2,
	                        discountpercent3,
	                        narrative,
	                        justification,
	                        refundpercent,
	            			orderlineno_,
	            			saleorderno_
	                     )
	                    VALUES (
                        " . $OrderNoCompra . ",
                        '" . $OrderLine->StockID . "',
                        now(),
                        '" . DB_escape_string($OrderLine->ItemDescription) . "',
                        '" . $CompraStockidglcode . "',
                        '" . ($price). "',
                        " . ($cantidadsol). ",
                        0,
                        0,
                        '" . $_POST['ItemDescription'] . "',
                        '" . $_POST['uom'] . "',
                        '" . $_POST['suppliers_partno'] . "',
                        '" . $POLine->subtotal_amount . "',
                        '" . $_POST['package']. "',
                        '" . $POLine->pcunit . "',
                        '" . $_POST['nw'] . "',
                        '" . $_POST['gw'] . "',
                        '" . $_POST['cuft'] . "',					
                        '" . ($cantidadsol) . "',
                        '0',
                        '0',
                        '0',
                        '0',
                        '" . trim(DB_escape_string((htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES)))) . "',
                        'Orden de compra automatica',
                        '" . ($Devolucion) . "',
                        '" . $OrderLine->LineNumber . "',
                        '" . $_SESSION['Items'.$identifier]->OrderNo. "'
                        )";
        	$ErrMsg =_('No se pudo insertar el detalle de la orden de compra');
            $DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
            $Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	    }
	}else{
            // ya existe orden de compra con este proveedor y este producto
            $myrowcompra = DB_fetch_row($Resultverificacompra);
            
            // estrae la cantidad que se solicitara en este pedido de compra             
            $sql="SELECT sum(quantity)
                  FROM salesorderdetails
                  WHERE orderno='".$_SESSION['Items'.$identifier]->OrderNo."'
                  AND stkcode='". $OrderLine->StockID."'";
            $ErrMsg =_('No se pudo obtener el detalle de la orden de venta');
            $DbgMsg =_('EL SQL es');
            $result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
            $myrow = DB_fetch_row($result);
            $ordenadas= $myrow[0];
            
            if($_SESSION['Autoqtypurch']==1){
            	$sqlLL = "SELECT ifnull(sum(ifnull(salesorderdetails.quantity-salesorderdetails.qtyinvoiced,0)),0) AS dem
						  FROM salesorderdetails INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
						  WHERE salesorderdetails.fromstkloc='" . $OrderLine->AlmacenStock  . "' 
						  	AND salesorderdetails.completed=0 
							AND salesorders.quotation in ($demandQtyQuotations)
					 		AND salesorders.orderno<>".$_SESSION['Items'.$identifier]->OrderNo." 
					 		AND salesorderdetails.stkcode='" . $OrderLine->StockID . "'";
            	$ordenescerradas=false;
            	$ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
            	$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
            	if (DB_num_rows($DemandResult)==1){
            		$DemandRow = DB_fetch_row($DemandResult);
            		$DemandQtySales =  $DemandRow[0];
            		$ordenescerradas=true;
            	} else {
            		$DemandQtySales =0;
            		 
            	}
            	$SQL = "SELECT stockmaster.description,
								(locstock.quantity-locstock.ontransit)-".$DemandQtySales." as quantity ,
								stockmaster.mbflag
		 				FROM locstock INNER JOIN stockmaster ON stockmaster.stockid=locstock.stockid
						WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
							AND locstock.loccode='" . $OrderLine->AlmacenStock . "'";
            	$ErrMsg = _('No se puede recuperar la cantidad para facturar');
            	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            	$CheckNegRow = DB_fetch_array($Result);
            	$cantidaddisponible=$CheckNegRow['quantity'];
            	$ordenadas=$OrderLine->Quantity-$cantidaddisponible;
            	if($ordenadas<=0){
            		$ordenadas=$OrderLine->Quantity;
           	 	}
            }
            //actualiza compra
            $sql="UPDATE purchorders
				  SET status='"._('Authorised')."'
				  WHERE orderno=".$myrowcompra[0];
            $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
            $SQL="SELECT sum(quantityord)
              	  FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
              	  WHERE purchorders.requisitionno='".$_SESSION['Items'.$identifier]->OrderNo."'
                  	AND purchorders.supplierno='".$ProveedorCompra."'
                    AND purchorderdetails.itemcode='".$OrderLine->StockID."'
                    AND purchorderdetails.completed=1 ";
           	$Resulttraecompras= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            $myrowcompraAnt = DB_fetch_row($Resulttraecompras);
            $CompradasANT=$myrowcompraAnt[0];
            if($CompradasANT>0){
            	if(($ordenadas-$CompradasANT)>0){
            		$ordenadas=$ordenadas-$CompradasANT;
            	}
            }
            //actualiza la cantidad de compra para este producto
            $sql="UPDATE purchorderdetails
                  SET quantityord=".($ordenadas)."
                  WHERE orderno=".$myrowcompra[0]." 
                  	AND itemcode='".$OrderLine->StockID."'
                 	AND completed=0";
            $ErrMsg =_('No se pudo actualizar el detalle de la orden de compra');
            $DbgMsg =_('El SQL es');
            $Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
            $OrderNoCompra = $myrowcompra[0];
            if (!in_array($OrderNoCompra,$arrOrdenesGeneradas)){
            	$arrOrdenesGeneradas[] = $OrderNoCompra;
            	if ($sendEmailCompras)
            		$arrEnviarmail[$OrderNoCompra] = 1;
            	else
            		$arrEnviarmail[$OrderNoCompra] = 0;
            	$sendEmailCompras=false;
            }
        }
        $Result = DB_Txn_Commit($db);
        $ProveedorCompraANT=$ProveedorCompra;
    }
	//Envio de email 
    $okMails = array();
    $okOrders = array();
    $emails = $_SESSION['POReceiveEmail'];
    $arremails = explode(",", $emails);
    foreach($arremails as $email) {
    	if (IsEmailAddress($email)){
    		$okMails[] = $email;
    	}
    }
    
    foreach ($arrOrdenesGeneradas as $orderno) {
    	if ($arrEnviarmail[$orderno]==1){
    		$okOrders[] = $orderno;
    	}
    }
	
    // Clean orders array if its already sent ...
    foreach($okOrders as $index => $okOrder) {
    	if(in_array($okOrder, $ordersSent)) {
    		unset($okOrders[$index]);
    	}
    }
    
	if(empty($okOrders) == false) {
		$subject = "Orden de Compra con Prioridad Alta";
		$message = "Se ha generado la Ordenes de Compra no: " . implode(',', $okOrders) . ", para el pedido de venta no: ".$_SESSION['Items'.$identifier]->OrderNo.".\r\n Favor de darle seguimiento pues es de alta prioridad.";
		$from_name = ucwords($_SESSION['DatabaseName']);
		$from_mail = $_SESSION['FactoryManagerEmail'];
		$replyto = $from_mail;
		$uid = md5(uniqid(time()));
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		
		foreach($okOrders as $okOrder) {
			$ordersSent[] = $okOrder;
		}
		
		if (mail(implode(',', $okMails), $subject, "", $header)) {
			prnMsg( _('Se ha enviado notificacion a: ') . implode(',', $okMails), 'sucess');
		}	
	}
	
	prnMsg( _('Se ha(n) realizado de manera exitosa la orden(es) de compra: ').implode(",", $arrOrdenesGeneradas),'sucess');
}

unset($_POST['AutomaticCompra']);
?>