<?php
// primero ordeno los proveedores para hacer las compras
$Total=$_POST['Total'];
$proveedores = array();
$NewItem_array=array();
$indice=0;
for ($i=1;$i <= $Total;$i++){
    if($_POST['Sel' . $i]==true ){
	if ($_POST['CantidadC_'.$i]>0 and strlen($_POST['CantidadC_'.$i])>0){
	    $proveedores[$indice]['supplier']=$_POST['supplierid_' . $i];
	    //$NewItem_array[$key] = $_POST['StockLocationDist_'.$i];
	    $proveedores[$indice]['Cantidad']=$_POST['CantidadC_'.$i];
	    $proveedores[$indice]['Almacen']=$_POST['StockLocationDist_' . $i];
	    $proveedores[$indice]['Producto']=$_POST['itemcode_' . $i];
	    $proveedores[$indice]['Descripcion']=$_POST['itemdescription_' . $i];
	    $proveedores[$indice]['Req']=$_POST['requisitionno_' . $i];
	    $proveedores[$indice]['Solicitadas']=$_POST['solicitadas_' . $i];
	    $proveedores[$indice]['Ordenadas']=$_POST['Ordenadas_' . $i];
	    $proveedores[$indice]['AlmacenReq']=$_POST['LocationReq_' . $i];
	    $indice=$indice+1;
	}
    }
}
//ordena el arreglo por proveedor que es el primer indice
sort($proveedores);
$ProveedorCompraANT=0;
$OrderNoCompra=0;
$entra=false;
$entramail=false;
$Result = DB_Txn_Begin($db);
//enviar email de comparas a proveedor

foreach ($proveedores as $key => $val) {
    $ProveedorCompra=$val['supplier'];
    //valido que no se compre mas de las que se estan solicitando
    $totalacomprar=$val['Ordenadas']-$val['Solicitadas'];
    if($totalacomprar>0){
	
	if($entra==false){
	    $entra=true;
	}
	//verifica el proveedor para generar una ordern de compra
	if ($ProveedorCompraANT!=$ProveedorCompra){
	
	    //enviar email cuando es mas de un producto y no ha entrado
	    if ($OrderNoCompra>0){
		//include con datos para envio de email a proveedor pero hasta q se hace la autorizacion se envia al proveedor
		//include('SendEmailCompra.inc');
	    }
	    $entramail=true;
	    // extrae los datos de la localidad de almacen
	    $SQL="select * from locations where loccode='".$val['Almacen']."'";
	  // echo $ProveedorCompra;
	    $Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	    $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
	    $DelAdd1 = $CompraAlmacenRow[2];
	    $DelAdd2 = $CompraAlmacenRow[3];
	    $DelAdd3 = $CompraAlmacenRow[4];
	    $DelAdd4 = $CompraAlmacenRow[5];
	    $DelAdd5 = $CompraAlmacenRow[6];
	    $DelAdd6 = $CompraAlmacenRow[7];
	    $Tagref = $CompraAlmacenRow[14];
	    
	    $SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice
			       FROM legalbusinessunit l, tags t
			       WHERE l.legalid=t.legalid AND tagref='".$Tagref."'";
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
			//****//
			$InvoiceNoTAG = DocumentNext(30, $Tagref,$area,$legaid, $db);
			$separa = explode('|',$InvoiceNoTAG);
			$serie = $separa[1];
			$folio = $separa[0];
			$foliocompra=$serie.'|'.$folio ;
	      //inserta la orden de compra nueva para este proveedor
	    $sql = "INSERT INTO purchorders (
		    supplierno,
		    foliopurch,
		    comments,
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
		    tagref
		    )
	    VALUES(
		    '" . $ProveedorCompra. "',
		     '" . $foliocompra. "',
		    '" . 'Compra automatica generada por '.$_SESSION['UserID'] . "',
		    date_format(now(),'%Y-%m-%d'),
		    '',
		    '" . $_SESSION['UserID'] . "',
		    '0',
		    '" . $val['Almacen'] . "',
		    '" . $DelAdd1 . "',
		    '" . $DelAdd2 . "',
		    '" . $DelAdd3 . "',
		    '" . $DelAdd4 . "',
		    '" . $DelAdd5 . "',
		    '" . $DelAdd6 . "',
		    '0',			
		    '0',
		    now(),";
	    $sql.="'" . 'Pending'. "',";
	    $sql.="		'Compra Revisada por requisicion',
		    now(),
		    ".$Tagref."
	    )";
	    //echo $sql;
	    $ErrMsg =  _('The purchase order header record could not be inserted into the database because');
	    $DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
	    $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	    $OrderNoCompra = DB_Last_Insert_ID($db,'purchorders','orderno');
	}
	// valida si el producto no se ha comprado con ese proveedor
	$sql="SELECT *
	      FROM purchorderdetails inner join purchorders on purchorderdetails.orderno=purchorders.orderno
	      WHERE purchorderdetails.orderno=".$OrderNoCompra."
		    AND itemcode='".$val['Producto']."'
		    AND supplierno='".$ProveedorCompra."'";
	$ErrMsg =  _('No se pudieron obtener los datos del producto');
	$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
	$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Result)==0){
	//extrae referencia de orden de compra
	    $sql = "SELECT
			stockmaster.description,
			purchdata.suppliers_partno,
			stockmaster.pkg_type,
			stockmaster.units,
			stockmaster.netweight,		
			stockmaster.kgs,
			stockmaster.volume,
			stockcategory.stockact,
			stockmanufacturer.manufacturerid,
			purchdata.price
			FROM purchdata INNER JOIN stockmaster
			ON purchdata.stockid=stockmaster.stockid
			 left join stockmanufacturer on stockmanufacturer.manufacturerid=stockmaster.manufacturer
			,stockcategory
		       
			WHERE stockcategory.categoryid = stockmaster.categoryid
			    AND purchdata.stockid='" . $val['Producto']. "'
			    AND purchdata.supplierno='".$ProveedorCompra."'";
	    $ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $val['Producto']  . ' ' . _('no hay resultados');
	    $DbgMsg = _('El SQL utilizado es');
	    $ResultProveedorcompra =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	    $myrow = DB_fetch_row($ResultProveedorcompra);
	    $_POST['ItemDescription'] = $myrow[0];
	    $_POST['suppliers_partno'] = $myrow[1];
	    $_POST['package'] = 1;//$myrow[2];
	    $_POST['uom'] = $myrow[3];
	    $_POST['nw'] = $myrow[4];
	    $_POST['gw'] = $myrow[5];
	    $_POST['cuft'] = $myrow[6];
	    $CompraStockidglcode=$myrow[7];
	    $price=$myrow[9];
	    //inserta detalle de orden de compra
	    $cantidadsol=$val['Cantidad'];
	    // AGREGAR EL DESCUENTO Y % DE DEVOLUCION
	    // verifica el porcetaje de devolucion
	    $percentdevolucion=TraePercentDevXSupplier($ProveedorCompra,$val['Producto'],$myrow[8],$_SESSION['Items'.$identifier]->DefaultSalesType,$db);
	    //echo $percentdevolucion;
	    $separa = explode('|',$percentdevolucion);
	    $Devolucion = $separa[0]/100;
	    $Discount=$separa[1]/100;
	    $totalpurch=$separa[2];
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
			refundpercent
			)
		    VALUES (
			" . $OrderNoCompra . ",
			'" .$val['Producto'] . "',
			now(),
			'" . $val['Descripcion'] . "',
			'" . $CompraStockidglcode . "',
			'" .$price. "',
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
			'" . ($Devolucion) . "'
			)";
		$ErrMsg =_('No se pudo insertar el detalle de la orden de compra');
		$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
		$Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		//echo $sql;
	}else{
		    // ya existe orden de compra con este proveedor y este producto
		    $cantidadsol=$val['Cantidad'];
		    //actualiza la cantidad de compra para este producto
		    if($cantidadsol>0){
			$sql="UPDATE purchorderdetails
			     SET quantityord=quantityord+".($cantidadsol)."
			     WHERE orderno=".$OrderNoCompra." AND itemcode='".$val['Producto']."'";
			$ErrMsg =_('No se pudo actualizar el detalle de la orden de compra');
			$DbgMsg =_('El SQL es');
			$Result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		    }
	}
	// Actualizaciones de las requisiciones
	if($val['Cantidad']>0 and strlen($val['Cantidad'])>0){
	    // se actualiza la requisicion con el numero de traspaso
	    $sql = "UPDATE requisitionorderdetails
		    SET statusdetail='En Compra',
			quantitypurch='" .  $val['Cantidad'] ."'
		    WHERE podetailitem='" . $val['Req'] . "'";
	    $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	    
	    //Actualizar el status de la orden de requisicion
	    // se actualiza la requisicion con el numero de compra
	    $sql = "INSERT INTO transferrequistions (norequisition,
				    purchorderno,
				    type,
				    quantityord,
				    quantityrecd,
				    intolocation,
				    fromlocation,
				    statustransfer
				    )
		    VALUES ('".$val['Req']."',
			    '" .  $OrderNoCompra."',
			    'Compra',
			    '" .$val['Cantidad']. "',
			    0,
			    '" .$val['AlmacenReq']. "',
			    '" .$val['Almacen']. "',
			    'En Compra'
			    )";
	    $ErrMsg = _('No se puede insertar en el log');
	    $DbgMsg = _('El SQL es');
	    $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	    
	    //se actualiza el log de transferencia
	    $sql = "INSERT INTO logrequisitionorders (norequisition,
				    itemcode,
				    status,
				    comments,
				    userid,
				    datetransaction
				    )
		    VALUES ('".$val['Req']."',
				    '" .   $val['Producto'] . "',
				    'En Compra',
				    'Se Genero Compra del Producto ". $val['Producto'] .'X la Cantidad de '. $val['Cantidad'] .' En El Almacen:'.$val['Almacen']  ."',
				    '" .  $_SESSION['UserID'] . "',
				    now()
			    )";
	    $ErrMsg = _('No se puede insertar en el log');
	    $DbgMsg = _('El SQL es');
	    $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	    $NoRequisition=$val['Req'];
	    $productoReq=$val['Producto'];
	    $statusReq='En Compra';
	    $comentariosReq="Se Genero Compra del Producto ". $val['Producto'] .'X la Cantidad de '. $val['Cantidad'] .' En El Almacen:'.$val['Almacen'];
	    $UsuarioReq=$_SESSION['UserID'];
	    //enviar email a los que participan en la requisicion de la orden de compra
	    include('SendEmailChangeRequisitions.inc');
	    
	}
    }//fin de validacion
    $ProveedorCompraANT=$ProveedorCompra;
}
$Result = DB_Txn_Commit($db);
//enviar email cuando es mas de un producto y no ha entrado
if ($OrderNoCompra>0){
    //include con datos para envio de email a proveedor
  //  include('SendEmailCompra.inc');
}

if($entra==true){
    prnMsg( _('Se han realizado de manera exitosa las ordenes de compra'),'sucess');
}else{
    prnMsg( _('Los productos seleccionados no generan orden de compra por tener operaciones en transito pendientes.'),'error');
}
?>