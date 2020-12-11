<?php
/**
 * 
 * @param sales order number $orderNo
 * @param database reference $db
 */
function createFutureSaleOrdersByCategory($orderNo, &$db) {
	
	$categories = array();
	$orders = array();
	
	$result = DB_Txn_Begin($db);
	
	$sql = "SELECT stockcategory.categoryid, stockcategory.categorydescription, diascaducidad AS days FROM salesorderdetails 
		INNER JOIN stockmaster ON stockmaster.stockid = salesorderdetails.stkcode
		INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
		WHERE stockcategory.diascaducidad>0 and salesorderdetails.orderno = '$orderNo' GROUP BY stockcategory.categoryid";
	
	$rs = DB_query($sql, $db);
	while ($row = DB_fetch_array($rs)) {
		$categories[] = array(
			'id' => $row['categoryid'],
			'name' => $row['categorydescription'],
			'days' => $row['days'],
		);
	}
	
	$sql = "SELECT idprospect,quotation FROM salesorders WHERE orderno = $orderNo" ;
	$result = DB_query($sql, $db);
	$umov = 0;
	$quotation = 0;
	if ($row = DB_fetch_array($result)) {
		$umov = $row[0];
		$quotation = $row[1];
	}
	
	foreach ($categories as $category) {
		
		$days = $category["days"];
		if (empty($days)) {
			$days = 0;
		}
		
		if (empty($_SESSION['disableFutureOrderCreation'])) {
			
			$newOrder = GetNextTransNo(30, $db);
			$orders[] = $newOrder;
			
			//Inserta encabezado de pedido
			$sql = "INSERT INTO salesorders (orderno,debtorno,branchcode,customerref,comments,orddate,
				ordertype,shipvia,deliverto,deladd1,deladd2,deladd3,deladd4,
				deladd5,deladd6,contactphone,contactemail,freightcost,fromstkloc,
				deliverydate,quotedate,confirmeddate,deliverblind,salesman,
				placa,serie,kilometraje,tagref,taxtotal,totaltaxret,currcode,paytermsindicator,
				advance,UserRegister,puestaenmarcha,paymentname,nocuenta,extratext,nopedido,
				noentrada,noremision,idprospect,contid,typeorder,deliverytext)
				SELECT $newOrder,debtorno,branchcode,customerref,comments,DATE_ADD(orddate, INTERVAL $days DAY),
				ordertype,shipvia,deliverto,deladd1,deladd2,deladd3,deladd4,
				deladd5,deladd6,contactphone,contactemail,freightcost,fromstkloc,
				deliverydate,quotedate,confirmeddate,deliverblind,salesman,
				placa,serie,kilometraje,tagref,0,totaltaxret,currcode,paytermsindicator,
				advance,'{$_SESSION['UserID']}',puestaenmarcha,paymentname,nocuenta,extratext,nopedido,
				noentrada,noremision,idprospect,contid,typeorder,deliverytext
				FROM salesorders WHERE orderno = $orderNo";
			
			$result = DB_query($sql, $db);
			
			//Inserta partidas de pedido
			$sql = "INSERT INTO salesorderdetails (orderlineno,orderno,stkcode,unitprice,quantity,discountpercent,
				discountpercent1,discountpercent2,narrative,poline,itemdue,fromstkloc,
				salestype,warranty,servicestatus,refundpercent,quantitydispatched,showdescrip)
				SELECT orderlineno,$newOrder,stkcode,unitprice,quantity,discountpercent,
				discountpercent1,discountpercent2,narrative,poline,itemdue,fromstkloc,
				salestype,warranty,servicestatus,refundpercent,quantitydispatched,showdescrip
				FROM salesorderdetails
				INNER JOIN stockmaster ON stockmaster.stockid = salesorderdetails.stkcode
				INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
				WHERE orderno = $orderNo AND stockcategory.categoryid = '{$category["id"]}'";
			
			$result = DB_query($sql, $db);
			
			//Inserta propiedades de pedido anterior
			$sql = "INSERT INTO salesstockproperties (stkcatpropid,orderno,valor,orderlineno,InvoiceValue,typedocument)
				SELECT stkcatpropid,$newOrder,valor,salesstockproperties.orderlineno,InvoiceValue,typedocument
				FROM salesstockproperties 
				INNER JOIN salesorderdetails ON salesorderdetails.orderno = salesstockproperties.orderno 
				AND salesorderdetails.orderlineno = salesstockproperties.orderlineno
				INNER JOIN stockmaster ON stockmaster.stockid = salesorderdetails.stkcode
				WHERE salesstockproperties.orderno = '$orderNo' AND stockmaster.categoryid = '{$category["id"]}'";
			
			$result = DB_query($sql, $db);
				
			//Agregar a tabla de fechas de pedidos
			$sql = "INSERT INTO salesdate(orderno,fecha_solicitud,usersolicitud)
				VALUES($newOrder,now(),'{$_SESSION['UserID']}')";
	
			$result = DB_query($sql, $db);
			
			// Actualizo pedido a status inicial
			$sql = "SELECT salesfield, statusid, flagdate, flagupdate FROM salesfielddate 
				WHERE statusid = " . $_SESSION['QuotationInicial'];
			$result = DB_query($sql, $db);
			while ($RowOrders = DB_fetch_array($result)) {
				$sql = "UPDATE salesdate";
				if ($RowOrders['flagdate'] == 1){
					$sql .= " SET {$RowOrders['salesfield']} = DATE_ADD(NOW(), INTERVAL $days DAY) ";
				} else {
					$sql .= " SET {$RowOrders['salesfield']} = '{$_SESSION['UserID']}'";
				}
				$sql .= " WHERE orderno = " .  $newOrder;
				if ($RowOrders['flagupdate'] == 1) {
					$sql .= " AND {$RowOrders['salesfield']} IS NULL";
				}
				DB_query($sql, $db);
			}
			
			// Actualizo pedido recien creado	
			$sql = "UPDATE salesorders
				SET quotation = {$_SESSION['QuotationInicial']}
				WHERE orderno= $newOrder";
			$result = DB_query($sql, $db);
			
			// Actualiza informacion de fechas
			$sql = "SELECT salesfield, statusid, flagdate, flagupdate FROM salesfielddate WHERE statusid = $quotation";
			$result = DB_query($sql, $db);
			while ($RowOrders = DB_fetch_array($result)) {
				$sql = "UPDATE salesdate";
				if ($RowOrders['flagdate'] == 1){
					$sql .= " SET {$RowOrders['salesfield']} = DATE_ADD(NOW(), INTERVAL $days DAY) ";
				} else {
					$sql .= " SET {$RowOrders['salesfield']} = '{$_SESSION['UserID']}'";
				}
				$sql .= " WHERE orderno = $newOrder";
				if($RowOrders['flagupdate'] == 1) {
					$sql .= " AND {$RowOrders['salesfield']} IS NULL";
				}
				DB_query($sql, $db);
			}
		}
		
		//crear oportunidad
		$sql = "INSERT INTO prospect_movimientos (areacod,debtorno,u_proyecto,dia,mes,anio,concepto,descripcion,
			u_user,cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,estimado,
			fecha,currcode,branchcode,fecha_compromiso,grupo_contable,confirmado,
			activo,u_entidad,catcode,idstatus,UserId,fecha_alta,clientcontactid,orderno)
			SELECT areacod,debtorno,u_proyecto,'" . date("d") . "','" . date("m") . "','" . date("Y") . "',concepto,descripcion,
			'{$_SESSION['UserID']}',cargo,prioridad,referencia,periodo_dev,erp,TipoMovimientoId,estimado,
			DATE_ADD(CURRENT_DATE, INTERVAL $days DAY),currcode,branchcode,DATE_ADD(fecha_compromiso, INTERVAL $days DAY),grupo_contable,confirmado,
			activo,u_entidad,catcode,idstatus,'{$_SESSION['UserID']}',DATE_ADD(CURRENT_DATE, INTERVAL $days DAY), clientcontactid, $orderNo
			FROM prospect_movimientos
			WHERE u_movimiento = '$umov'";
		
		$result = DB_query($sql, $db);
		$prospectid = DB_Last_Insert_ID($db, 'prospect_movimientos', 'u_movimiento');
		
		$sql = "INSERT INTO prospect_comentarios (idtarea,comentario,fecha,avance,idstatus,urecurso,userid,operacion)
			VALUES ('$prospectid', 'Alta de oportunidad: {$_SESSION['UserID']}@: GENERADA A FUTURO $orderNo', Now(), 0, '1', '{$_SESSION['UserID']}', '{$_SESSION['UserID']}', 'alta')";
		$result = DB_query($sql, $db);
		
		if (empty($_SESSION['disableFutureOrderCreation'])) {
			//actualizo pedido con id de prospecto
			$sql = "UPDATE salesorders SET idprospect = '$prospectid'
				WHERE orderno = $newOrder";
			$result = DB_query($sql, $db);
		}
	}
	
	$result = DB_Txn_Commit($db);
	return $orders;
}