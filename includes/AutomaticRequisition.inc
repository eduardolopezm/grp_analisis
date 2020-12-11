<?php
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
    //valida si el checkbox esta activo
    $checcompraactivo=$_POST['itemordencompra_' . $OrderLine->LineNumber];
    $checcompraprioridad=$_POST['itemordencompraprioridad_' . $OrderLine->LineNumber];
    
    if ($checcompraactivo==true){
        if($checcompraprioridad==true){
            $prioridadcompra=1;
        }else{
            $prioridadcompra=0;
        }
        //extrae el proveedor preferente por producto
        $SQL="SELECT supplierno
              FROM purchdata where stockid='".$OrderLine->StockID."' and preferred=1";
        $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        if (DB_num_rows($Result)>0){
            $CompraRow = DB_fetch_row($Result);
            $ProveedorCompra = $CompraRow[0];
        }else{
            $SQL="SELECT supplierno
              FROM purchdata where stockid='".$OrderLine->StockID."'";
            $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
             if (DB_num_rows($Result)>0){
                $CompraRow = DB_fetch_row($Result);
                $ProveedorCompra = $CompraRow[0];
            }else{
                prnMsg( _('El producto no tiene proveedor asignado favor de vefificar el codigo: ').$OrderLine->StockID._(' La orden sera generada con el proveedor default.'),'sucess');
               $ProveedorCompra=$_SESSION['SupplierDefault'];
            }
        }
        // valida que la hora no halla pasdo la hora de req.
        if (Date('H')>$_SESSION['HourReq']){
            $fechasolicito=date("Y/m/d");
	    $fechasolicito = dateadd_dias($fechasolicito,1,0,0,0,0,0);
            $fechasolicito="'".$fechasolicito."'";
            
        }else{
            $fechasolicito='now()';
        }
        $entracompra=true;
        $CantidadReq=$OrderLine->Quantity;
        //valida si no existe orden de compra para este producto con esta orden de venta con este provedor
        $SQL="SELECT requisitionorderdetails.podetailitem, requisitionorderdetails.statusdetail
              FROM requisitionorderdetails
              WHERE requisitionorderdetails.requisitionno='".$_SESSION['ExistingOrder']."'
                    AND requisitionorderdetails.supplierno='".$ProveedorCompra."'
                    AND requisitionorderdetails.itemcode='".$OrderLine->StockID."'
             order by requisitionorderdetails.podetailitem desc ";
       
        $Resultverificacompra = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        // verifica si existe el registro en la base de datos y aun se encuentra en solicitud
        if (DB_num_rows($Resultverificacompra)>0){
            $myrowcompra = DB_fetch_row($Resultverificacompra);
            $statusdetail=$myrowcompra[1];
             //echo $SQL;
            //echo $statusdetail;
            if($statusdetail<>'Solicitud'){
                
                // SON LAS QUE SE HAN ORDENADO PARA ESTE PEDIDO
                $sql="SELECT sum(quantity)
                  FROM salesorderdetails
                  WHERE orderno='".$_SESSION['ExistingOrder']."'
                  AND stkcode='". $OrderLine->StockID."'";
                //  echo $sql;
                  $ErrMsg =_('No se pudo obtener el detalle de la orden de venta');
                  $DbgMsg =_('EL SQL es');
                  $result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
                  $myrow = DB_fetch_row($result);
                  $ordenadas= $myrow[0];
                // LAS QUE SE HAN SOLICITADO EN LA REQUISICION
                  $sql="SELECT sum(quantityord)
                  FROM requisitionorderdetails
                  WHERE requisitionno='".$_SESSION['ExistingOrder']."'
                  AND itemcode='". $OrderLine->StockID."'";
                //  echo $sql;
                  $ErrMsg =_('No se pudo obtener el detalle de la orden de venta');
                  $DbgMsg =_('EL SQL es');
                  $result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
                  $myrow = DB_fetch_row($result);
                  $solicitadas= $myrow[0]; 
                  $CantidadReq=$ordenadas-$solicitadas;
                  if ($CantidadReq>0){
                    $entracompra=true;
                  }else{
                    $entracompra=false;
                  }
            }else{
                $entracompra=false;
            }
        }else{
            $entracompra=true;    
        }
         // ya existe orden de compra con este proveedor y este producto
        
        
        
            
        if ($entracompra==true){
            $Result = DB_Txn_Begin($db);
            // extrae los datos de la localidad de almacen virtual
            $SQL="select * from locations where loccode='".$OrderLine->AlmacenStock."'";
            $Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
            $AlmacenStock = $CompraAlmacenRow[0];
            $DelAdd1 = $CompraAlmacenRow[2];
            $DelAdd2 = $CompraAlmacenRow[3];
            $DelAdd3 = $CompraAlmacenRow[4];
            $DelAdd4 = $CompraAlmacenRow[5];
            $DelAdd5 = $CompraAlmacenRow[6];
            $DelAdd6 = $CompraAlmacenRow[7];
                            
                //extrae referencia de orden de compra
                $sql = "SELECT
                            stockmaster.description,
                            purchdata.suppliers_partno,
                            stockmaster.pkg_type,
                            stockmaster.units,
                            stockmaster.netweight,		
                            stockmaster.kgs,
                            stockmaster.volume,
                            stockcategory.stockact
                            FROM purchdata INNER JOIN stockmaster
                            ON purchdata.stockid=stockmaster.stockid,stockcategory
                            
                            WHERE stockcategory.categoryid = stockmaster.categoryid
                                AND purchdata.stockid='" . $OrderLine->StockID . "'
                                AND purchdata.supplierno='".$ProveedorCompra."'";
                $ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $OrderLine->StockID  . ' ' . _('no hay resultados');
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
                $CompraStockidglcode=$myrow[7];
                //inserta detalle de orden de compra
                $sql = "INSERT INTO requisitionorderdetails (
                            supplierno,
                            orderno,
                            orddate,
                            initiator,
                            requisitionno,
                            intostocklocation,
                            fromstocklocation,
                            tagref,
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
                            priority,
                            total_quantity,
                            total_amount,
                            discountpercent1,
                            discountpercent2,
                            discountpercent3,
                            narrative,
                            justification,
                            
                            statusdetail
                            )
                        VALUES (
                            '" . $ProveedorCompra . "',
                            " . $_SESSION['ExistingOrder'] . ",
                            now(),
                            '" . $_SESSION['UserID'] . "',
                            " . $_SESSION['ExistingOrder'] . ",
                            '" . $AlmacenStock . "',
                            '" . $_SESSION['AlmacenRequisition'] . "',
                            " . $_SESSION['Tagref'] . ",
                            
                            '" . $OrderLine->StockID . "',
                            " . $fechasolicito . ",
                            '" . $OrderLine->ItemDescription . "',
                            '" . $CompraStockidglcode . "',
                            0,
                            " . ($CantidadReq). ",
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
                            '" . $prioridadcompra . "',
                            '" . ($CantidadReq) . "',
                            '0',
                            '0',
                            '0',
                            '0',
                            '" . trim(DB_escape_string((htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES)))) . "',
                            'Orden de compra automatica',
                            'Solicitud'
                            )";
                    $ErrMsg =_('No se pudo insertar el detalle de la orden de compra');
                    $DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
                  //  echo $sql;
                    $Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
                 $OrderNoCompra = DB_Last_Insert_ID($db,'requisitionorderdetails','podetailitem');
                //inserta en la requisicion en log de compra
                 $sql = "INSERT INTO logrequisitionorders (norequisition,
                                   itemcode,
                                   status,
                                   comments,
                                   userid,
                                   datetransaction
                                   )
                        VALUES ('".$OrderNoCompra."',
                                   '" .  $OrderLine->StockID . "',
                                   'Solicitud',
                                   'Se Genero Orden para el Producto ".$OrderLine->StockID."',
                                   '" .  $_SESSION['UserID'] . "',
                                   now()
                                )";
                $ErrMsg = _('No se puede insertar en los paquetes');
                $DbgMsg = _('El SQL es');
                $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                
               
        }else{
            $Resultverificacompra = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            // ya existe orden de compra con este proveedor y este producto
            $myrowcompra = DB_fetch_row($Resultverificacompra);
            $OrderNoCompra=$myrowcompra[0];
            // estrae la cantidad que se solicitara en este pedido de compra             
            $sql="SELECT sum(quantity)
                  FROM salesorderdetails
                  WHERE orderno='".$_SESSION['ExistingOrder']."'
                  AND stkcode='". $OrderLine->StockID."'";
          //  echo $sql;
            $ErrMsg =_('No se pudo obtener el detalle de la orden de venta');
            $DbgMsg =_('EL SQL es');
            $result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
            $myrow = DB_fetch_row($result);
            $ordenadas= $myrow[0];
            
             // LAS QUE SE HAN SOLICITADO EN LA REQUISICION
                  $sql="SELECT sum(quantityord)
                  FROM requisitionorderdetails
                  WHERE requisitionno='".$_SESSION['ExistingOrder']."'
                  AND itemcode='". $OrderLine->StockID."' and podetailitem<>".$myrowcompra[0];
                //  echo $sql;
                  $ErrMsg =_('No se pudo obtener el detalle de la orden de venta');
                  $DbgMsg =_('EL SQL es');
                  $result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
                  $myrow = DB_fetch_row($result);
                  $solicitadas= $myrow[0]; 
                  $CantidadReq=$ordenadas-$solicitadas;
            //actualiza la cantidad de compra para este producto
            $sql="UPDATE requisitionorderdetails
                 SET quantityord=".($CantidadReq)."
                 WHERE podetailitem=".$myrowcompra[0];
            $ErrMsg =_('No se pudo actualizar el detalle de la orden de compra');
            $DbgMsg =_('El SQL es');
            $Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	  //  echo $sql;
            
        }
        $Result = DB_Txn_Commit($db);	
        prnMsg( _('Se ha realizado de manera exitosa la orden de requision de compra:').$OrderNoCompra,'sucess');
    }
    unset($_POST['AutomaticRequisicion']);
}

?>