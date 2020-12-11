<?php
/*
	Generar OC para una Orden de trabajo desde la pagina WorkOrderIssueV2 y wocontrolpanel

*/
$ProveedorCompraANT = '';
$firstTimeFlag = true;
$currcode_ = $_SESSION['CurrAbrev'];
$wo = $_SESSION['purchorderWO']['wo'];
$loccode = $_SESSION['purchorderWO']['loccode'];
$OrderLine = 0;
$nivel = $_POST['Component'];

$qry="Select startdate from workorders
		where wo = $wo";
$res = DB_query($qry,$db);
$reg = DB_fetch_array($res);
$startdate = $reg['startdate'];

$qry="Select tagref FROM locations
		WHERE loccode = '$loccode'";
$rs = DB_query($qry,$db);
$row = DB_fetch_array($rs);
$tagref = $row['tagref'];


$qry = "Select legalid FROM tags WHERE tagref = '".$tagref."'";
$rtag = DB_query($qry,$db);
$rowtag = DB_fetch_array($rtag);
$legalid=$rowtag[0];

$arrDiasEntregaOC=array();
$generaOC= true;

// Validar todos los productos antes de guardar orden de compra.
foreach ($_SESSION['purchorderWO']['items'] as $arritems) {
	$stkid= $arritems['stockid'];
	
	$SQL = "SELECT supplierno, leadtime, conversionfactor, pcurrcode
            FROM purchdata
        	INNER JOIN suppliers
        	ON suppliers.supplierid = purchdata.supplierno
        	WHERE stockid = '" . $stkid . "' 
        	AND preferred = 1";
	
		$ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $stkid  . ' ' . _('no hay resultados');
		$DbgMsg = _('El SQL utilizado es');
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
        
        if(DB_num_rows($Result) != 0) {
        	$CompraRow= DB_fetch_array($Result);
        	$ProveedorCompra = $CompraRow[0];
        } 
        else {
	    	$SQL="SELECT supplierno, pcurrcode,leadtime
		          FROM purchdata where stockid='$stkid'";
	    	
	       	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	        
	        if (DB_num_rows($Result)>0){
	        	$CompraRow= DB_fetch_array($Result);
	        	$ProveedorCompra = $CompraRow[0];
	        }else{
			    // Proveedor con Tipo de Moneda MXN
	        	$ProveedorCompra = $_SESSION['SupplierDefault'];
	        }
       	}
      
      	if ($ProveedorCompra==""){
       		$SQL="SELECT purchorders.supplierno, purchorders.currcode
		              FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
		              WHERE purchorders.wo='".$wo."'
		              		AND purchorders.status<>'Cancelled'
		                    AND purchorderdetails.itemcode='".$stkid."'";
       		
       		$Resultverificacomprauno = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
       		// si existe busca proveedor
       		if (DB_num_rows($Resultverificacomprauno)==0){
       			prnMsg($ErrMsg);
       			$generaOC= false;
       			break;
       		}
       	}       	

	// revisar si el producto tiene categoria
  	$sqlCAT = "SELECT stockcategory.stockact,stockmaster.mbflag,longdescription
				FROM stockmaster,stockcategory
				WHERE stockcategory.categoryid = stockmaster.categoryid
	            	AND stockmaster.stockid='" . $stkid . "'";
  	
    $ErrMsg = _('El producto no tiene categoria configurada') . ': ' . $stkid  . ' ' . _('no hay resultados');
    $DbgMsg = _('El SQL utilizado es');
   	$ResultProveedorcompraCAT = DB_query($sqlCAT,$db,$ErrMsg,$DbgMsg);
   	
   	if(DB_num_rows($ResultProveedorcompraCAT)==0){
   		$generaOC= false;
   		prnMsg($ErrMsg);
   		break;
   	}
}

// si no cumple con las validaciones para generar OC no va a generar nada.
if (!$generaOC){
	$_SESSION['purchorderWO']['items']= array();
}

foreach ($_SESSION['purchorderWO']['items'] as $arritems) {
    $checcompraactivo=true;
    if ($checcompraactivo==true){
 	    $stkid = $arritems['stockid'];
		$required = $arritems['required'];
		//echo '<br>codigo:'.$stkid.' req:'.$required;
		$flagoc= $arritems['flagoc'];
		//busca si el usuario cambio la cantidad por defecto del producto
		
		foreach($_POST['arrstkid'] as $idprod => $value){
			if ($value > 0){
				if ($idprod == $stkid and $value < $required){
						$required = $value;
					break;
				} elseif ($idprod == $stkid and $permisoOCExtra==1){
						$required = $value;
						break;
				}
			}
		}
		
		// Pasar a la otra iteracion si la cantidad de la caja de texto es menor o igual a 0.
		$inputQty = $_POST['arrstkid'][$stkid];
		if ($inputQty <= 0) {
			continue;
		} 
		if ($flagoc ==false) {
			continue;
		}
		
		$conversionfactor=1;
		$leadtime = 0;
		//extrae el proveedor preferente por producto
    	$SQL = "
        	SELECT supplierno, leadtime, conversionfactor, pcurrcode
            FROM purchdata
        	INNER JOIN suppliers
        	ON suppliers.supplierid = purchdata.supplierno
        	WHERE stockid = '" . $stkid . "' 
        	AND preferred = 1
        ";
    	
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
        $leadtime = 0;
        
        if(DB_num_rows($Result) > 0) {
        	
            $CompraRow = DB_fetch_row($Result);
            $ProveedorCompra = $CompraRow[0];
            $leadtime = $CompraRow[1];
            $conversionfactor = $CompraRow[2];
            $currcode_ = $CompraRow[3];
            
        } 
        else {
        		
	        		$SQL="SELECT supplierno, pcurrcode,leadtime
		              FROM purchdata where stockid='$stkid'";
	        		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	        		if (DB_num_rows($Result)>0){
	        			$CompraRow = DB_fetch_row($Result);
	        			$ProveedorCompra = $CompraRow[0];
	        			$currcode_ = $CompraRow[1];
	        			$leadtime = $CompraRow[2];
	        		}else{
			        		// Proveedor con Tipo de Moneda MXN
			        		$ProveedorCompra = $_SESSION['SupplierDefault'];
			        		$currcode_="MXN";
	        		}
        		
       	}
        if($currcode_==''){
        	$currcode_=$_SESSION['CountryOfOperation'];
        }
        //calcular fecha de entrega del producto
       	$deliverydate = $startdate;
       	if ($leadtime > 0){
	        $sql = "Select date_add('$startdate',INTERVAL -$leadtime DAY)";
	        $rsdd = DB_query($sql,$db);
	        $regdd = DB_fetch_row($rsdd);
	        $deliverydate = $regdd[0];
       	}
	
		
        // valida que la hora no halla pasdo la hora de req.
        if (Date('H')>$_SESSION['HourReq']){
            $fechasolicito=date("Y/m/d");
	    	$fechasolicito = dateadd_dias($fechasolicito,1,0,0,0,0,0);
            $fechasolicito="'".$fechasolicito."'";
            
        }else{
            $fechasolicito='now()';
        }
	
		if ($ProveedorCompra==""){
			$SQL="SELECT purchorders.supplierno, purchorders.currcode
		              FROM purchorders INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
		              WHERE purchorders.wo='".$wo."'
		              		AND purchorders.status<>'Cancelled'
		                    AND purchorderdetails.itemcode='".$stkid."'";
		        $Resultverificacomprauno = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		        // si existe busca proveedor
		        if (DB_num_rows($Resultverificacomprauno)!=0){
			   		$myrowcomprauno = DB_fetch_row($Resultverificacomprauno);
		            $ProveedorCompra=$myrowcomprauno[0];
		            $currcode_ = $myrowcomprauno[1];
				}
		}
		
       		$Result = DB_Txn_Begin($db);
        	// extrae los datos de la localidad de almacen
        	$SQL="select * from locations where loccode='".$loccode."'";
            $Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
            $DelAdd1 = $CompraAlmacenRow[2];
            $DelAdd2 = $CompraAlmacenRow[3];
            $DelAdd3 = $CompraAlmacenRow[4];
            $DelAdd4 = $CompraAlmacenRow[5];
            $DelAdd5 = $CompraAlmacenRow[6];
            $DelAdd6 = $CompraAlmacenRow[7];
            if ($currcode_ != $_SESSION['CountryOfOperation']) {
            	$fechaemisionpago = date('Y-m-d');
            	$ratep = GetCurrencyRateByDate($fechaemisionpago, $currcode_, $db);
            	if($ratep == 0) {
            		//prnMsg( _('No ha ,') . ' ' . _('no ha sido posible facturar, intentelo nuevamente'), 'error');
            		$ratep =1;
            	}
            } else {            	
            	$ratep = 1;          	
            }
            
             if ($firstTimeFlag) {
                //inserta la orden de compra nueva para este proveedor
                $sql = "INSERT INTO purchorders (
                        supplierno,
                        comments,
                        orddate,
                        rate,
                        initiator,
                        wo,
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
                        '" . 'Compra automatica generada por '.$_SESSION['UserID'] . "',
                        ".$fechasolicito.",
                         ".$ratep.",
                        '" . $_SESSION['UserID'] . "',
                        '" . $wo. "',
                        '" . $loccode. "',
                        '" . $DelAdd1 . "',
                        '" . $DelAdd2 . "',
                        '" . $DelAdd3 . "',
                        '" . $DelAdd4 . "',
                        '" . $DelAdd5 . "',
                        '" . $DelAdd6 . "',
                        '0',			
                        '" . $wo. "',
                        now(),";
                $sql.="'" . 'Pending'. "',";
                $sql.="		'Compra automatica',
                        'now()',
                        ".$tagref.",
                        '".$currcode_."'		
                )";
               // echo $sql;
                $Result = DB_query($sql, $db);
                $OrderNoCompra = DB_Last_Insert_ID($db,'purchorders','orderno');
                $firstTimeFlag = false;
            }
            
            //extrae referencia de orden de compra
            $sql = "SELECT
						stockmaster.description,
						purchdata.suppliers_partno,
						1 as pkg_type, /*stockmaster.pkg_type,*/
						stockmaster.units,
						stockmaster.netweight,		
						stockmaster.kgs,
						stockmaster.volume,
			                        stockcategory.stockact,
			                        stockmaster.manufacturer,
			                        purchdata.price
						FROM purchdata INNER JOIN stockmaster
						ON purchdata.stockid=stockmaster.stockid,stockcategory
			                        
						WHERE stockcategory.categoryid = stockmaster.categoryid
			                            AND purchdata.stockid='" . $stkid . "'
			                            AND purchdata.supplierno='".$ProveedorCompra."'";
		
			$ErrMsg = _('El producto no tiene proveedor configurado') . ': ' .$stkid  . ' ' . _('no hay resultados');
			$DbgMsg = _('El SQL utilizado es');
			$ResultProveedorcompra =DB_query($sql,$db,$ErrMsg,$DbgMsg);
			$myrows = DB_fetch_row($ResultProveedorcompra);
	
			$_POST['ItemDescription'] = $myrows[0];
			$_POST['suppliers_partno'] = $myrows[1];
			$_POST['package'] = 1; //$myrow[2];
			$_POST['uom'] = $myrows[3];
			$_POST['nw'] = $myrows[4];
			$_POST['gw'] = $myrows[5];
			$_POST['cuft'] = $myrows[6];
	
	         $sqlCAT = "SELECT stockcategory.stockact,stockmaster.mbflag,longdescription
				FROM stockmaster,stockcategory 
				WHERE stockcategory.categoryid = stockmaster.categoryid
	                            AND stockmaster.stockid='" . $stkid . "'";
		
			$ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $stkid  . ' ' . _('no hay resultados');
			$DbgMsg = _('El SQL utilizado es');
			$ResultProveedorcompraCAT = DB_query($sqlCAT,$db,$ErrMsg,$DbgMsg);
			
			//echo '<pre>'.$sqlCAT.'<br>';
			$myrowCAT = DB_fetch_row($ResultProveedorcompraCAT);
			$CompraStockidglcode=$myrowCAT[0];
			$mbflag=$myrowCAT[1];
			$_POST['ItemDescription']=$myrowCAT[2];
		       // echo '<br>cuenta:'.$CompraStockidglcode.'<br>';
			$price=$myrows[9];
			
			//obtener precio de ultimo costo
			$qry = "select lastcost from stockcostsxlegal
		   			 where stockid='".$OrderLine->StockID ."'
					    and legalid = '$legalid'";
			
			$res = DB_query($qry,$db);
			$reg = DB_fetch_array($res);
			
			if ($reg[0] > 0)
				$price = $reg[0];
			
            //inserta detalle de orden de compra
            $cantidadsol=$required;
            
            // AGREGAR EL DESCUENTO Y % DE DEVOLUCION
            // verifica el porcetaje de devolucion
            
            $DefaultSalesType = $_SESSION['DefaultPriceList']; //****************** BUSCAR ESTO********************
            
            $percentdevolucion=TraePercentDevXSupplier($ProveedorCompra,$stkid,$myrows[8],$DefaultSalesType,$db);
            //echo $percentdevolucion;
            $separa = explode('|',$percentdevolucion);
            $Devolucion = $separa[0]/100;
            $Discount=$separa[1]/100;
            $totalpurch=$separa[2];
            
            if ($conversionfactor > 1){
            	 
            	$cantidadsol = $cantidadsol/$conversionfactor;
            
            	$qry = "select lastcost from stockcostsxlegal
		            	where stockid='".$stkid ."'
            		          and legalid = '$legalid'";
            	$res = DB_query($qry,$db);
            	$reg = DB_fetch_array($res);
            	if ($reg[0] > 0)
            		$price = $reg[0]*$conversionfactor;
            }
            if($inputQty!=0 and $required==0){
            	$cantidadsol=$inputQty;
            }
            
            
            if ($mbflag=='B' or $mbflag=='D') {
            	
            	// Inicio
            	// Parte que actualizar las cantidades de la orden de compra por nivel
            	$cantidadsolTemp = $cantidadsol;
            	$sql = "SELECT (qtypu - purchqty) AS qtyleft, worequirements_id 
            			FROM woreq_purchorders 
            			WHERE wo = '$wo' AND stockid = '$stkid' AND stockidnivel = '$nivel'";
            	$rsReq = DB_query($sql, $db);
            	
            	while ($rowReq = DB_fetch_array($rsReq)) {
            		
            		$qtyToUpdate = 0;
            		$idReq = $rowReq['worequirements_id'];
            		$qtyLeft = $rowReq['qtyleft'];
            		
            		if ($cantidadsolTemp <= 0) {
            			// Salir del ciclo si no hay mas cantidad por emitir
            			//Actualiza cantidad excedente
            			
            			$sql = "UPDATE woreq_purchorders SET purchqty = purchqty + $qtyToUpdate WHERE worequirements_id = '$idReq'";
            			$Result=DB_query($sql, $db);
            			
            			break;	
            		}
            		
            		if ($cantidadsolTemp <= $qtyLeft) {
            			$qtyToUpdate = $cantidadsolTemp;
            			$cantidadsolTemp = 0;
            		} else {
            			$qtyToUpdate = $qtyLeft;
            			$cantidadsolTemp -= $qtyLeft;
            		}
            		$totalqtyUpdate=$totalqtyUpdate+$qtyToUpdate;
            		$lastidreq=$idReq;
            		$sql = "UPDATE woreq_purchorders SET purchqty = purchqty + $qtyToUpdate WHERE worequirements_id = '$idReq'";
            		
            		$Result=DB_query($sql, $db);
            	}
            	if($cantidadsolTemp>0){
            		$sql = "UPDATE woreq_purchorders SET purchqty_exc = purchqty_exc + $cantidadsolTemp WHERE worequirements_id = '$lastidreq'";
            		$Result=DB_query($sql, $db);
            	}
            	// Parte que actualiza las cantidades de la orden de compra por nivel
            	// Fin
            	
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
	            			wo,
			    			qtywo,
			    			womasterid,
			    			wocomponent
	                        )
	                    VALUES (
	                        " . $OrderNoCompra . ",
	                        '" . DB_escape_string($stkid) . "',
	                        '".$deliverydate."',
	                        '" . $_POST['ItemDescription'] . "',
	                        '" . $CompraStockidglcode . "',
	                        '" . ($price). "',
	                        " . ($cantidadsol). ",
	                        0,
	                        0,
	                        '" . DB_escape_string($_POST['ItemDescription']) . "',
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
	                        '" . "" . "',
	                        'Orden de compra automatica',
	                        '" . ($Devolucion) . "',
	                        '" . $OrderLine. "',
	                        '" . $wo. "',
	                         " . ($cantidadsol). ",
	                        '".$_POST['MasterID']."',
	                        '".$_POST['Component']."'
	                        )";
			//echo '<pre>'.$sql;
	                $ErrMsg =_('No se pudo insertar el detalle de la orden de compra');
	                $DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
	                $Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	                
	                $OrderLine++;
		    }
               
        $Result = DB_Txn_Commit($db);
        
        //guardar dato de proveedor y dias de entrega para la orden de compra
        if ($OrderNoCompra > 0){
	        if (array_key_exists($OrderNoCompra, $arrDiasEntregaOC)){
	        	if ($arrDiasEntregaOC[$OrderNoCompra] < $leadtime)
	        		$arrDiasEntregaOC[$OrderNoCompra] = $leadtime;
	        }
	        else 
	        	$arrDiasEntregaOC[$OrderNoCompra] = $leadtime;
        }
        	
        $ProveedorCompraANT=$ProveedorCompra;
    }

    // VALIDA SI HAY PARTIDAS EN OC, EN CASO DE NO HABER ELIMINA LA OC
	
	//enviar mail a personas de compras 
	$emails = $_SESSION['POReceiveEmail'];
	$arremails = explode(",",$emails);
	foreach($arremails as $email){
		if (IsEmailAddress($email)){

			$subject = "Orden de compra";
			$message = "Se ha generado la Orden de Compra no: ".$OrderNoCompra.", para la Orden e Trabajo no: ".$wo.".\r\n Favor de darle seguimiento.";
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
			//$header .= "Content-Type: application/pdf; name=\"reporte.pdf\"\r\n";
			//$header .= "Content-Transfer-Encoding: base64\r\n";
			//$header .= "Content-Disposition: attachment; filename=\"reporte.pdf\"\r\n\r\n";
			//$header .= $content."\r\n\r\n";
			//$header .= "--".$uid."--";
			if (mail($email, $subject, "", $header)) {
				prnMsg( _('Se ha enviado notificacion a: ').$email,'sucess');
			} 
			
		}
	}
	
	
    prnMsg( _('Se ha realizado de manera exitosa la orden de compra:').$OrderNoCompra.' para la OT '.$wo,'sucess');
    
    if (Havepermission($_SESSION['UserID'], 29, $db) == 1) { // Tiene el permiso de hacer compras
    	
    	echo '<div style="text-align:center; margin:0 auto"><br /><a href="' . $rootpath . '/PO_Header.php?&ModifyOrderNumber=' . $OrderNoCompra . '&TieToOrderNumber=0&' . SID . '">' . _('MODIFICAR ORDEN DE COMPRA NO.') . ' ' . $OrderNoCompra . '</a><br /><br /></div>';
    }
}

foreach($arrDiasEntregaOC as $oc => $dias){
	//echo $startdate;
	if($startdate=='0000-00-00'){
		$startdate=Date('Y-m-d');
	}
	$dias += $_SESSION['MargenEntregaOC'];
	
	$qry = "UpDate purchorders
			set	orddate = if(date_add('$startdate',INTERVAL -$dias DAY) < now(),now(),date_add('$startdate',INTERVAL -$dias DAY)) 
			WHERE orderno = $oc";
	//echo "<pre>$qry";
	$r = DB_query($qry,$db);
	
	$qry = "Update purchorders
			set orddate = if(time(orddate) > time('".$_SESSION['HourReq'].":00:00'),date_add(DATE_FORMAT(orddate,'%Y-%m-%d'),INTERVAL 1 DAY),orddate)
			WHERE orderno = $oc		
			";
	//echo "<pre>$qry";
	$r = DB_query($qry,$db);
	
	//reservar productos si es necesario
	$qry = "select if(DATEDIFF(orddate,current_date) < 8 ,1,0) as sameweek
			FROM purchorders
			WHERE orderno = $oc";
	$res = DB_query($qry,$db);
	$regsResult = DB_fetch_array($res);
	if ($regsResult['sameweek'] == 1){
		foreach ($_SESSION['purchorderWO']['itemsreserv'] as $arritems) {
				$stkid = $arritems['stockid'];
				$required = $arritems['required'];
				//mover cantidades en el locstock
				$qry = "UpDate locstock
						Set quantityprod = quantityprod + if((quantity - $required) < 0,quantity,(quantity - $required)),
							quantity = if((quantity - $required) < 0,0,(quantity - $required))
						WHERE stockid = '$stkid'
						and loccode = '$loccode'	
						";
				//echo "<pre>$qry";
				//se comenta porque esta quitando existencias y no se pueden facturar los productos
				//$r = DB_query($qry,$db);
				 
		}
	}
	
}

//unset($_POST['AutomaticCompra']);
?>