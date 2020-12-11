<?php

//
/* $Revision: 1.24 $ 	
ABM 10/09/2013 Se crearon funciones para alta de movimientos contables y de inventarios para version 6.0
*/
//

/************************/
// Funciones de version 6.0
/************************/
// Genera cadena para insert en tabla de movimientos
function CategoryAccount($category, $campo, $db){
	$sql = "SELECT ".$campo." as cuenta
			FROM stockcategory
			WHERE stockcategory.categoryid ='".$category."'";
	$result = DB_query($sql, $db);
	$row = DB_fetch_array($result);
	$cuenta = $row['cuenta'];
	return $cuenta;
}



function Insert_Stockmoves($Stockid,$Type,$Transno,$Loccode,$Trandate,$Debtorno='',$Branch='',
		$Prd=0,$Narrative,$Qty,$Cost,$Devolucion=0,$Show_on_inv_crds,$Warranty=0,
		$Tagref,$Rate=1,$Oc,$Userid,$EstimatedAvgCost,$Showdescription=0,$Price,
		$Discountpercent,$Discountpercent1,$Discountpercent2,$Totaldescuento,$valor="",$Reference,$orderlineno,$db){
	
	$SQL = "INSERT INTO stockmoves (
									stockid,
									type,
									transno,
									loccode,
									trandate,
									debtorno,
									branchcode,
									prd,
									qty,
									standardcost,
									refundpercentmv,
									show_on_inv_crds,
									warranty,
									tagref,
									avgcost,
									ref1,
									useridmov,
									ratemov,
									price,
									discountpercent,
									discountpercent1,
									discountpercent2,
									totaldescuento,
									narrative,
									reference,
									showdescription,
									ref4
			) VALUES (
						'" . $Stockid . "',
						" . $Type . ",
					 	" . $Transno . ",
					 	'" . $Loccode. "',
						'" . $Trandate . "',
						'" . $Debtorno . "',
						'" . $Branch . "',
						" . $Prd . ",
						" . formatearNumero($Qty) . ",
						'" . formatearNumero($EstimatedAvgCost) . "',
						'" . formatearNumero($Devolucion) . "',
						" . $Show_on_inv_crds . ",
						" . $Warranty . ",
						" . $Tagref . ",
						'" . formatearNumero($EstimatedAvgCost) . "',		
						'" . $Oc . "',
						'" . $Userid . "',
						'" . formatearNumero($Rate, 6) . "',
						'" . formatearNumero($Price) . "',
						'" . formatearNumero($Discountpercent) . "',
						'" . formatearNumero($Discountpercent1) . "',
						'" . formatearNumero($Discountpercent2) . "',
						'" . formatearNumero($Totaldescuento) . "',		
						'" . DB_escape_string($Narrative) . "',	
						'" . $Reference . "',		
						'".$Showdescription."',
						'".$orderlineno."'
			)";
	//echo '<br><pre>sql:'.$SQL;
	return $SQL;
}

// Genera cadena para insert en tabla de contabilidad//
/**
 * 
 * @param unknown $Type
 * @param unknown $Transno
 * @param unknown $Trandate
 * @param unknown $Prd
 * @param unknown $Account
 * @param unknown $Narrative
 * @param unknown $Tag
 * @param unknown $Userid
 * @param number $Rate
 * @param string $Debtorno
 * @param string $Branch
 * @param string $Stockid
 * @param number $Qty
 * @param number $Grns
 * @param string $Loccode
 * @param unknown $EstimatedAvgCost
 * @param unknown $Suppno
 * @param unknown $Purchno
 * @param unknown $Amount
 * @param unknown $db
 * @param string $ChequeNo
 * @param string $catcuenta
 * @param number $jobref
 * @param string $bancodestino
 * @param string $rfcdestino
 * @param string $cuentadestino
 * @param unknown $db - variable de conexion
 * @return string $ISQL - Cadena completa con la instruccion Insert
 */
function Insert_Gltrans($Type,$Transno,$Trandate,$Prd,$Account,$Narrative,$Tag,$Userid,$Rate=1,
		$Debtorno='',$Branch='',$Stockid='',$Qty=0,$Grns=0,$Loccode='',$EstimatedAvgCost,
		$Suppno,$Purchno=0,$Amount,$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, 
		$rfcdestino=null, $cuentadestino=null, $posteo=1, $ue='',$folio_ue=0){
	
	$Narrative=str_replace('&lt;strong&gt;', ' ' , $Narrative);
	$Narrative=str_replace('&lt;/strong&gt;', ' ' , $Narrative);
	if(empty($ChequeNo)){
			$ChequeNo='0';
	}
	if(empty($Purchno)){
			$Purchno='0';
	}
	$sql = "SHOW COLUMNS FROM gltrans Where field In('bancodestino', 'cuentadestino', 'rfcdestino')";
	$rs = DB_query($sql, $db);
	
	if (DB_num_rows($rs)==3) 
	{
		$ISQL="INSERT INTO gltrans (type,
			typeno,
			trandate,
			cat_cuenta,
			periodno,
			account,
			narrative,
			tag,
			userid,
			rate,
			debtorno,
			branchno,
			stockid,
			qty,
			grns,
			standardcost,
			loccode,
			dateadded,
			suppno,
			purchno,
			chequeno,
			amount,
			jobref,
			bancodestino,
			rfcdestino,
			cuentadestino, posted, ln_ue,
			nu_folio_ue
			)
		VALUES (" . $Type . ",
			" . $Transno . ",
			'".  $Trandate . "',
			'".  $catcuenta . "',
		
			".  $Prd . ",
			'" . $Account . "',
			'" . DB_escape_string($Narrative) . "',
			'" . $Tag . "',
			'" . $Userid . "',
			'" . formatearNumero($Rate, 6) . "',
			'" . $Debtorno . "',
			'" . $Branchno . "',
			'" . $Stockid . "',
			'" . formatearNumero($Qty) . "',
			'" . $Grns . "',
			'" . formatearNumero($EstimatedAvgCost) . "',
			'" . $Loccode . "',
			now(),
			'" . $Suppno . "',
			'" . $Purchno . "',
			'" . $ChequeNo . "',
			" . formatearNumero($Amount) . ',
			"'.$jobref.'",
			"'.$bancodestino.'",
			"'.$rfcdestino.'",
			"'.$cuentadestino.'", "'.$posteo.'", "'.$ue.'", "'.$folio_ue.'")';		
	}
	else 
	{
		$ISQL="INSERT INTO gltrans (type,
				typeno,
				trandate,
				cat_cuenta,
				periodno,
				account,
				narrative,
				tag,
				userid,
				rate,
				debtorno,
				branchno,
				stockid,
				qty,
				grns,
				standardcost,
				loccode,
				dateadded,
				suppno,
				purchno,
				chequeno,
				amount,
				jobref,posted, ln_ue,
				nu_folio_ue
				)
			VALUES (" . $Type . ",
				" . $Transno . ",
				'".  $Trandate . "',
				'".  $catcuenta . "',
				".  $Prd . ",
				'" . $Account . "',
				'" . DB_escape_string($Narrative) . "',
				" . $Tag . ",
				'" . $Userid . "',
				'" . formatearNumero($Rate, 6) . "',
				'" . $Debtorno . "',
				'" . $Branchno . "',
				'" . $Stockid . "',
				'" . formatearNumero($Qty) . "',
				'" . $Grns . "',
				'" . formatearNumero($EstimatedAvgCost) . "',
				'" . $Loccode . "',
				now(),
				'" . $Suppno . "',
				'" . $Purchno . "',
				'" . $ChequeNo . "',
				" . formatearNumero($Amount) . ',
				"'.$jobref.'", "'.$posteo.'", "'.$ue.'", "'.$folio_ue.'")';
	}
	//echo '<pre>'; echo $ISQL;
	return $ISQL;

}

//Concentrado de Reembolsos
function InsertaSupptranConcentrado($typeorderno,$conorderno,$typecargo,$transnocargo,$typeabono,$transnoabono,$account,$monto,$catcuenta,$supplierno){
	$SQL = "INSERT INTO supptrans_concentrado (
												typeorderno, 
												conorderno, 
												typecargo, 
												transnocargo, 
												typeabono, 
												transnoabono,
												account, 
												amount, 
												cat_cuenta, 
												supplierno)
			VALUES (
					'".$typeorderno."',
					'".$conorderno."',
					'".$typecargo."',
					'".$transnocargo."',
					'".$typeabono."',
					'".$transnoabono."',
					'".$account."',
					'".$monto."',
					'".$catcuenta."',
					'".$supplierno."'
			)";//
	return $SQL;
	
}
// valida que cuenta contable exista en catalogo
function TraeCuentaContable($cuentainventario,$db){

	$SQL="select * from chartmaster where accountcode='".$cuentainventario."'";
	$ErrMsg = _('No se recupero el margen negativo que permite la venta');
	$DbgMsg= _('El sql que fallo fue:');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($result)==0) {
		return 0;
	}
	return 1;
}

/*****************************************/



//valida fecha segun calendario programado
function getDateFromCalendar($db,$fecha){
	
	$qry = "Select date_add('$fecha',INTERVAL '-".$_SESSION['HourTimeToGetInProduction']."' HOUR_MINUTE) as fecha";
	
	$rlastfecha = DB_query($qry,$db);
	$rowlastfecha = DB_fetch_array($rlastfecha);
	$lastfecha = $rowlastfecha[0];
	
	
	$sql = "Select concat(calendardate,' ',horaf,':00') as ultimahora FROM mrpcalendar
			WHERE calendardate = DATE_FORMAT('$lastfecha','%Y-%m-%d') and manufacturingflag=1";
	$rsh = DB_query($sql,$db);
	$rowh = DB_fetch_array($rsh);
	$ultimahora = $rowh[0];
	
	
	$sql = "Select *, DATE_FORMAT('$fecha','%H') as horafecha 
			from mrpcalendar
			where calendardate  = DATE_FORMAT('$fecha','%Y-%m-%d') and manufacturingflag=1
			";
	
	$rs = DB_query($sql,$db);
	if (DB_num_rows($rs) > 0){
		$row = DB_fetch_array($rs);
		
		$horai = $row['horai'];
		$horaf = $row['horaf'];
		$horafecha = $row['horafecha'];
		$fechacalendar = $row['fecha'];
		
		if ($horafecha >= $horai and $horafecha < $horaf)
			return $fecha;
		else{
			//buscar cuantas horas faltan de la fecha anterior a la hora de fin de la jornada laboral
			$qry="Select TIMEDIFF('".$_SESSION['HourTimeToGetInProduction']."',TIMEDIFF('$ultimahora','$lastfecha')) as difhoras
			";
			
			$rsd = DB_query($qry,$db);
			$rowd = DB_fetch_array($rsd);
			$difhoras = substr($rowd[0],0,5);
			
			//buscar fecha siguiente que sea dia habil del calendario
			$sql = "Select date_add(concat(calendardate,' ',horai,':00'),INTERVAL '$difhoras' HOUR_MINUTE) as fecha
					FROM mrpcalendar
					WHERE calendardate > '$fecha'
					and manufacturingflag = 1
					Order by calendardate
					limit 1";
			//echo "<pre>$sql";
			$rsnew = DB_query($sql,$db);
			if (DB_num_rows($rsnew) > 0){
				$reg = DB_fetch_array($rsnew);
				$fechanew = $reg['fecha'];
				
				return $fechanew;
			}
			else
				return false;
			
		}
		
	}
	else {
		
		//buscar cuantas horas faltan de la fecha anterior a la hora de fin de la jornada laboral
		$qry="Select TIMEDIFF('".$_SESSION['HourTimeToGetInProduction']."',TIMEDIFF('$ultimahora','$lastfecha')) as difhoras
		";
		
		$rsd = DB_query($qry,$db);
		$rowd = DB_fetch_array($rsd);
		$difhoras = substr($rowd[0],0,5);
		
		//buscar fecha siguiente que sea dia habil del calendario
		$sql = "Select date_add(concat(calendardate,' ',horai,':00'),INTERVAL '$difhoras' HOUR_MINUTE) as fecha
				FROM mrpcalendar
				WHERE calendardate > '$fecha'
				and manufacturingflag = 1
				Order by calendardate
				limit 1";
		//echo "<pre>$sql";
		$rsnew = DB_query($sql,$db);
		if (DB_num_rows($rsnew) > 0){
			$reg = DB_fetch_array($rsnew);
			$fechanew = $reg['fecha'];
			
			return $fechanew;
		}
		else
			return false;
	}
		
	
}

//obtiene la fecha para la orden de trabajo segun plan calendarizado
function getNextDateTimeToWorkOrder($db){

	$fecha = date("Y-m-d H:i");
	//buscar sigte fecha tomando la ultima orden de trabajo en estatus 1
	$sql = "Select DATE_ADD(startdate,INTERVAL '".$_SESSION['HourTimeToGetInProduction']."' HOUR_MINUTE) as fecha
			FROM workorders
			WHERE idstatus=1
			Order By startdate desc
			limit 1";
	$rs = DB_query($sql,$db);
	if (DB_num_rows($rs) > 0){
		$reg = DB_fetch_array($rs);
		$fecha = $reg['fecha'];

	}
	else{
		//buscar si existe alguna orden en la fase E1 (idstatus=2)
		$sql = "Select wo FROM workorders
				WHERE idstatus=2";
		$res = DB_query($sql,$db);
		if (DB_num_rows($res) > 0){
			$reg = DB_fetch_array($res);
			$wo = $reg['wo'];
				
			//busca la fecha en que entro a E1 y le agrega 2 horas
			$sql="Select DATE_ADD(fecha,INTERVAL '".$_SESSION['HourTimeToGetInProduction']."' HOUR_MINUTE) as fecha
			from wocontrolpanel_comentarios
			WHERE wo = $wo
			and operacion='cambio estatus'
			and idstatus=2
			Order By fecha desc
			limit 1
			";
			$result = DB_query($sql,$db);
			$row = DB_fetch_array($result);
			$fecha = $row['fecha'];
		
		}
	}

	$fecha = getDateFromCalendar($db,$fecha);

	return $fecha;
}

function makeWorkOrderCalendar($db,$arritems,$location,$startdate,$requiredat,$orderno=0,$onewoforqty=0,$status){

	include_once('ManufacturedStockUnitcost.inc');

	$Resultx = DB_Txn_Begin($db);

	$u_user = "";
	/*if ($_SESSION['DefaultUserForWorkOrder']!="")
		$u_user=$_SESSION['DefaultUserForWorkOrder'];*/
	$u_user = $_SESSION['UserID'];//
	foreach($arritems as $arrdata){
	
		$stkid = $arrdata['stockid'];
		$qty = $arrdata['qty'];
		$orderline = $arrdata['orderline'];
		$fecha=	$arrdata['fecha'];
		$SQL = "SELECT idstatus,
								panelcontrolcategoria.userid,
								stockcategory.CodigoPanelControl,
								panelcontrolcategoria.flagcantidad
				FROM wocontrolpanel_status
					INNER JOIN stockcategory ON stockcategory.CodigoPanelControl = wocontrolpanel_status.panelcontrol
					INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
					INNER JOIN panelcontrolcategoria ON stockcategory.CodigoPanelControl = panelcontrolcategoria.CodigoPanelControl
				WHERE active = 1
						AND  wocontrolpanel_status.idstatus = panelcontrolcategoria.estatusinicial
						AND stockmaster.stockid = '".$stkid."'";
		$rss = DB_query($SQL,$db);
		$regs = DB_fetch_array($rss);
		$ids = $regs['idstatus'];
		$user = $regs['userid'];
		$PanelControl = $regs['CodigoPanelControl'];
		$flagcantidad = $regs['flagcantidad'];
		
		// genera una orden de trabajo por cantidad
		for($i=1;$i<=$qty;$i++){
			$wo = GetNextTransNo(40,$db);
			$startdate =$fecha;
			$EOQ=1;
			$qry = "INSERT INTO workorders 
						(wo,
						 loccode,
						 requiredby,
						 startdate,
						 orderno,
						 lineno,
						 idstatus,
						 fecha_alta,
						 u_user)
					VALUES (" . $wo . ",
							'" . $locationot . "',
							'" . $startdate. "',
							'" . $startdate . "',
							'".$orderno."',
							'".$orderline."',
							'".$ids."',
							now(),
							'$u_user'
						)";
			$Resultx = DB_query($qry,$db);
		    //Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
			insertRequirements($db,$wo,$stkid,'',$EOQ,0,0,$stkid);
			$v = round(calculate_cost($db,$stkid,'',1,0),2);
			$Cost=0;
			if ($v > 0)
				$Cost = $v;
			
			$sql = "INSERT INTO woitems (wo,
									stockid,
									qtyreqd,
									stdcost,
									orderline
									)
									VALUES ( " . $wo . ",
											'" . $stkid . "',
											" . $EOQ . ",
											" . $Cost . ",
											" . $orderline. ")";
			$ErrMsg = _('The work order item could not be added');
		 	$Resultx = DB_query($sql,$db,$ErrMsg);
		}// fin de for de cantidad
		// Actualizo la partida con la ultima ot generada
		$sqldetail = "UPDATE salesorderdetails
					  SET salesorderdetails.woline = '".$wo."'
					  WHERE salesorderdetails.orderno = '".$orderno."'
							AND salesorderdetails.orderlineno = '".$orderline."'";
		$resultdetail = DB_query($sqldetail, $db);
		// Actualizo la partida para que no genere en adelante mas ot
		$sqldetail = "UPDATE salesstockpropertiescalendar
					  SET salesstockpropertiescalendar.flagmakewo =1
					  WHERE salesorderno = '".$orderno."'
							AND orderline = '".$orderline."'";
		$resultdetail = DB_query($sqldetail, $db);
	}// Fin de array
	$Resultx = DB_Txn_Commit($db);
}// fin de funcion
function makeWorkOrder($db,$arritems,$location,$startdate,$requiredat,$orderno=0,$onewoforqty=0,$status){
	
	include_once('ManufacturedStockUnitcost.inc');
	
	$Result = DB_Txn_Begin($db);
	
	$u_user = "";
	if ($_SESSION['DefaultUserForWorkOrder']!=""){
		$u_user=$_SESSION['DefaultUserForWorkOrder'];
	}
	if($orderno == ""){
		
		prnMsg('No se guardo correctamente el pedido porque has perdido seccion ','error');
		echo '<p><a href="' . $rootpath . '/SelectSalesOrderV2_0.php?"><b>'. _('Busca el pedido para verificar si esta correcto.').'</a>';
		include('includes/footer_Index.inc');
		exit;
	}else{
		
		if ($onewoforqty==1){
			
			foreach($arritems as $arrdata){
				
				$stkid = $arrdata['stockid'];
				$qty = $arrdata['qty'];
				$orderline = $arrdata['orderline'];
				
				$SQL = "SELECT idstatus, 
								panelcontrolcategoria.userid,
								stockcategory.CodigoPanelControl,
								panelcontrolcategoria.flagcantidad
						FROM wocontrolpanel_status
							INNER JOIN stockcategory ON stockcategory.CodigoPanelControl = wocontrolpanel_status.panelcontrol
							INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid 
							INNER JOIN panelcontrolcategoria ON stockcategory.CodigoPanelControl = panelcontrolcategoria.CodigoPanelControl
						WHERE active = 1
							AND  wocontrolpanel_status.idstatus = panelcontrolcategoria.estatusinicial
							AND stockmaster.stockid = '".$stkid."'";
				$rss = DB_query($SQL,$db);
				$regs = DB_fetch_array($rss);
				$ids = $regs['idstatus'];
				$user = $regs['userid'];
				$PanelControl = $regs['CodigoPanelControl'];
				$flagcantidad = $regs['flagcantidad'];	
				
				$qry = "Select salesorderdetails.fromstkloc
						FROM salesorderdetails
						WHERE salesorderdetails.orderlineno = $orderline
						AND salesorderdetails.orderno = $orderno";
				$rsrow = DB_query($qry,$db);
				$row = DB_fetch_array($rsrow);
				$locationot = $row[0];
				
				if(empty($_SESSION['DefaultWOLocation']) == false) {
					$locationot = $_SESSION['DefaultWOLocation']; 
				}
				
				if ($_SESSION['WorkOrderInService']==1){

					//utilizar fecha del item de la cotizacion  
					$qry="SELECT salesstockproperties.valor
							FROM salesorderdetails
								INNER JOIN salesstockproperties ON salesorderdetails.orderno = salesstockproperties.orderno and salesorderdetails.orderlineno = salesstockproperties.orderlineno
								INNER JOIN  stockcatproperties on stockcatproperties.stkcatpropid=salesstockproperties.stkcatpropid
							WHERE stockcatproperties.controltype=3 AND salesorderdetails.orderlineno = $orderline AND salesorderdetails.orderno = $orderno";
					$rsrow = DB_query($qry,$db);
					if (DB_num_rows($rsrow) > 0){
						
						$row = DB_fetch_array($rsrow);
						$arrdata = explode("/",$row[0]);
						$mes = $arrdata[1];
						$dia = $arrdata[2];
						$anio = $arrdata[0];
						//echo $locationot;	
						$startdate =$anio."-".$mes."-".$dia;
						$requiredat = $startdate;
						$u_user=$_SESSION['WorkOrderUser'];
						
						if($u_user==''){
							$u_user=$_SESSION['UserID'];
						}
						
					}
		
				}
	
				if ($stkid!=""){
					
					$qry = "SELECT tags.legalid,
									tags.tagref
							FROM locations 
								JOIN tags ON locations.tagref = tags.tagref
							WHERE loccode = '".$location."'";
					$LocResult = DB_query($qry,$db);
					$LegalRefRow = DB_fetch_row($LocResult);
					
					if (is_null($LegalRefRow[0]) OR $LegalRefRow[0]==0){
						
						$legalid = 0;
						$tag = 0;
						
					} else {
						
						$legalid = $LegalRefRow[0];
						$tag = $LegalRefRow[1];
						
					}
	
					$sql = "SELECT mbflag,
									eoq,
									controlled
							FROM stockmaster
							WHERE stockid='" . $stkid . "'";
					$CheckItemResult = DB_query($sql,$db);
					
					if (DB_num_rows($CheckItemResult)==1){
						
						$CheckItemRow = DB_fetch_array($CheckItemResult);
						$EOQ = 1;
						
						if ($CheckItemRow['controlled']==1 && $_SESSION['DefineControlledOnWOEntry']==1){ //need to add serial nos or batches to determine quantity
						
							$EOQ = 0;
						}
						
						if ($CheckItemRow['mbflag']=='M'){
							
							$sql2 = "SELECT SUM((avgcost)*bom.quantity) AS cost
									FROM stockmaster
										JOIN bom ON stockmaster.stockid=bom.component
										JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = ".$legalid."
									WHERE bom.parent='" . $stkid . "'";
							$CostResult = DB_query($sql2, $db);
							$CostRow = DB_fetch_row($CostResult);
							
							if (is_null($CostRow[0]) || $CostRow[0]==0){
									
								if ($_SESSION['TypeCostStock']==1){
									
									$Cost = StockAvgcost($stkid, $tag, $db);
									
								}
								
								if ($_SESSION['TypeCostStock']==2){
									$Cost = StockAvgcostXLegal($stkid, $tag, $db);
								}
	
							}else{
								
								$Cost = $CostRow[0];
								
							}
									
						}
	
						if($Cost==''){
								
							$Cost=0;
						}
								
						if($flagcantidad ==1){
									
							$MSG = "Para el panel de suscripciones solo se agrega una orden con la cantidad agregada";
							$selectot = "SELECT *
										FROM salesorderdetails
										WHERE salesorderdetails.orderno = '".$orderno."'
											AND salesorderdetails.orderlineno = '".$orderline."'";
							if ($_SESSION['WorkOrderInService']==1){
								$selectot =$selectot . " and salesorderdetails.woline is null";
							}
							
							$resultot = DB_query($selectot, $db);
							
							if(DB_num_rows($resultot) > 0){
								
								$wo = GetNextTransNo(40,$db);
								
								if ($_SESSION['WorkOrderInService']!=1){
												
									$startdate = getNextDateTimeToWorkOrder($db);
									
									if(empty($startdate) == true){
										$startdate = date("Y-m-d");
									}
								}
									if($user <> ""){
										
										$u_user = $user;
									
									}
									
									$qry = "INSERT INTO workorders (wo,
																	loccode,
																	requiredby,
																	startdate,
																	orderno,
																	lineno,
																	idstatus,
																	fecha_alta,
																	u_user)
											VALUES (" . $wo . ",
													'" . $locationot . "',
													'" . $requiredat. "',
													'" . $startdate . "',
													'".$orderno."',
													'".$orderline."',
													'".$ids."',
													now(),
													'$u_user')";
									
									$InsWOResult = DB_query($qry,$db);
									insertRequirements($db,$wo,$stkid,'',$qty,0,0,$stkid);
									$v = round(calculate_cost($db,$stkid,'',1,0),2);
									
									if ($v > 0){
										
										$Cost = $v;
									
									}
									
									$sql = "INSERT INTO woitems (wo,
																stockid,
																qtyreqd,
																stdcost,
																orderline)
											VALUES ( " . $wo . ",
													'" . $stkid . "',
													" . $qty . ",
													" . $Cost . ",
													" . $orderline. ")";
									$ErrMsg = _('The work order item could not be added');
									
									$result = DB_query($sql,$db,$ErrMsg);
									
									$sqldetail = "UPDATE salesorderdetails
													SET salesorderdetails.woline = '".$wo."'
													WHERE salesorderdetails.orderno = '".$orderno."'
														AND salesorderdetails.orderlineno = '".$orderline."'";
									$resultdetail = DB_query($sqldetail, $db);
										
									//buscar componentes autoissue
									
									$qry = "SELECT worequirements.stockid,
													autoissue,
													qtypu,
													stockmaster.controlled,
													stockmaster.serialised,
													mbflag,
													stockcategory.wipact
											FROM worequirements
												INNER JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
							                    INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
											WHERE wo=" . $wo;
									$rs = DB_query($qry,$db);
									while ($rows = DB_fetch_array($rs)){
													
										if ($rows['autoissue']==1){
									
											$requerido = $EOQ*$rows['qtypu'];
											$qry = "SELECT quantity 
													FROM locstock
													WHERE loccode = '$location'
														AND stockid = '".$rows['stockid']."'";
											$rsdisp = DB_query($qry,$db);
											$reg = DB_fetch_array($rsdisp);
											$disp = $reg['quantity'];
											
											if ($disp==""){
														
												$disp=0;
												
											}

											if ($disp < $requerido ){ //poner producto como no autoissue
									
												$qry = "UPDATE worequirements
														SET autoissue = 0
														WHERE autoissue = 1
															AND stockid = '".$rows['stockid']."'
															AND wo = $wo";
					                    		$r = DB_query($qry,$db);
					                    		prnMsg("El producto ".$rows['stockid']." no tiene disponibilidad, hacer la descarga de almacen manualmente","warning");
					                    		
											}else{
													
												//generar mov de almacen y contable
												$Result = DB_Txn_Begin($db);
												/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
												$WOIssueNo = GetNextTransNo(28, $db);
												$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
												$SQLIssuedDate = date("Y-m-d");
												$StockGLCode = GetStockGLCode($rows['stockid'],$db);
												$NewQtyOnHand = 0;
												
												if ($rows['mbflag']=='M' OR $rows['mbflag']=='B'){
															 
															
													$qry = "UPDATE locstock
															SET quantity = locstock.quantity - " . $requerido . "
															WHERE locstock.stockid = '" . $rows['stockid'] . "'
																	AND loccode = '$location'";
													$r = DB_query($qry,$db);
															 
													$SQL="SELECT locstock.quantity
															FROM locstock
															WHERE locstock.stockid='" . $rows['stockid'] . "'
																AND loccode= '" . $location . "'";
													$Res = DB_query($SQL, $db);
															 		
													if (DB_num_rows($Res)==1){
															 		
														$LocQtyRow = DB_fetch_row($Res);
														$NewQtyOnHand = $LocQtyRow[0];
													
													}else{
					                    					 			
					                    				/*There must actually be some error this should never happen */
					                    				$NewQtyOnHand = 0;
													}
												}
															 		 
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
																				tagref)
														VALUES ('" . $rows['stockid'] . "',
						                            			28,
															 	" . $WOIssueNo . ",
															 	'" . $location . "',
															 	'" . Date('Y-m-d') . "',
															 	" . $Cost . ",
															 	" . $PeriodNo . ",
															 	'" . $wo . "',
															 	" . -$requerido . ",
															 	" . $Cost . ",
															 	'" . $NewQtyOnHand . "',
															 	'".$tag."')";
													$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
													$DbgMsg =  _('The following SQL to insert the stock movement records was used');
													$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
									
					                    			/*Get the ID of the StockMove... */
					                    			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
					                    			/* Do the Controlled Item INSERTS HERE *///
					           
					           						if ($_SESSION['CompanyRecord']['gllink_stock']==1){
					           							
					                    				/*GL integration with stock is activated so need the GL journals to make it so */
					            						/*first the debit the WIP of the item being manufactured from the WO
															the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
															it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/
									
														$SQL = "INSERT INTO gltrans (type,
															 						typeno,
															 						trandate,
															 						periodno,
															 						account,
															 						narrative,
															 						amount)
															 	VALUES (28,
							                        					" . $WOIssueNo . ",
																		'" . Date('Y-m-d') . "',
															 			" . $PeriodNo . ",
																		'" . $rows['wipact'] . "',
																		'" . $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
																		" . ($Cost * $requerido) . ")";
														$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The issue of the item to the work order GL posting could not be inserted because');
														$DbgMsg = _('The following SQL to insert the work order issue GLTrans record was used');
														
														$tipodocto = 28;
														$SQLInvoiceDate = Date('Y-m-d');
														$Narrative =  $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
														$rate = 1;
														$montocontable = ($Cost * $requerido);
														$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $rows['wipact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
																'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INV PROD EN PROCESO',$wo);
														
														$Result = DB_query($ISQL,$db,$ErrMsg, $DbgMsg, true);
														
														/*now the credit Stock entry*/
														$SQL = "INSERT INTO gltrans (type,
																					typeno,
																					trandate,
																					periodno,
																					account,
																					narrative,
																					amount)
																VALUES (28,
																		" . $WOIssueNo . ",
																		'" . Date('Y-m-d') . "',
																		" . $PeriodNo . ",
																		'" . $StockGLCode['stockact'] . "',
																		'" . $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
																		" . -($Cost * $requerido) . ")";
														$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock account credit on the issue of items to a work order GL posting could not be inserted because');
					                    				$DbgMsg =  _('The following SQL to insert the stock GLTrans record was used');
														
					                    				$tipodocto = 28;
					                    				$SQLInvoiceDate = Date('Y-m-d');
					                    				$Narrative =  $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
					                    				$rate = 1;
					                    				$montocontable = -($Cost * $requerido);
					                    				$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
					                    						'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INVENTARIOS',$wo);
					                    				
					                    				$Result = DB_query($ISQL,$db, $ErrMsg, $DbgMsg,true);
									
													} /* end of if GL and stock integrated and standard cost !=0 */
													
													$Result = DB_Txn_Commit($db);
												}//mov de almacen y contable
										}//autoissue
															         
									}//while buscar componentes autoissue
							}
														
						}else{
							
							$MSG = "Para el panel de publicaciones se agrega varias ordenes de trabajo dependiendo de la cantidad";
							for($i=1;$i<=$qty;$i++){
								
								//echo 'entro funcion 8';
								$selectot = "SELECT *
											FROM salesorderdetails
											WHERE salesorderdetails.orderno = '".$orderno."'
											AND salesorderdetails.orderlineno = '".$orderline."'";
								
								if ($_SESSION['WorkOrderInService']==1){
									$selectot =$selectot . " and salesorderdetails.woline is null";
								}
										
								$resultot = DB_query($selectot, $db);
									
								if(DB_num_rows($resultot) > 0){
									
									$wo = GetNextTransNo(40,$db);
									
									if ($_SESSION['WorkOrderInService']!=1)
									{
											
										$startdate = getNextDateTimeToWorkOrder($db);
										if(empty($startdate) == true){
											$startdate = date("Y-m-d");
										}
									}
										//
									if($user <> ""){
												
										$u_user = $user;//
											
									}
									
									$qry = "INSERT INTO workorders (wo,
																	loccode,
																	requiredby,
																	startdate,
																	orderno,
																	lineno,
																	idstatus,
																	fecha_alta,
																	u_user)
											VALUES (" . $wo . ",
													'" . $locationot . "',
													'" . $requiredat. "',
													'" . $startdate . "',
													'".$orderno."',
													'".$orderline."',
													'".$ids."',
													now(),
													'$u_user')";
									$InsWOResult = DB_query($qry,$db);
									//
									//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
									insertRequirements($db,$wo,$stkid,'',$EOQ,0,0,$stkid);
									//function insertRequirements($db,$wo, $Parent, $Component, $qty, $legalid, $echoOn=0,$masterparentid) {
									//echo 'entraaaa';
									$v = round(calculate_cost($db,$stkid,'',1,0),2);
									
									if ($v > 0){
												
										$Cost = $v;
										
									}	
									
									$sql = "INSERT INTO woitems (wo,
																stockid,
																qtyreqd,
																stdcost,
																orderline)
											VALUES ( " . $wo . ",
													'" . $stkid . "',
													" . $EOQ . ",
													" . $Cost . ",
													" . $orderline. ")";
									$ErrMsg = _('The work order item could not be added');
									$result = DB_query($sql,$db,$ErrMsg);
									
									$sqldetail ="UPDATE salesorderdetails
												SET salesorderdetails.woline = '".$wo."'
												WHERE salesorderdetails.orderno = '".$orderno."'
													AND salesorderdetails.orderlineno = '".$orderline."'";
									$resultdetail = DB_query($sqldetail, $db);
									
									$qry = "SELECT worequirements.stockid,
													autoissue,
													qtypu,
													stockmaster.controlled,
													stockmaster.serialised,
													mbflag,
													stockcategory.wipact
			                    			FROM worequirements
												INNER JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
					                    		INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					                    	WHERE wo=" . $wo;
									
									$rs = DB_query($qry,$db);
									while ($rows = DB_fetch_array($rs)){
										
										if ($rows['autoissue']==1){
											
											$requerido = $EOQ*$rows['qtypu'];
											//ver si hay disponibilidad
											$qry = "SELECT quantity 
													FROM locstock
													WHERE loccode = '$location'
														AND stockid = '".$rows['stockid']."'";
											$rsdisp = DB_query($qry,$db);
											$reg = DB_fetch_array($rsdisp);
											$disp = $reg['quantity'];
									
											if ($disp==""){
														
												$disp=0;
											
											}
													
											if ($disp < $requerido ){ //poner producto como no autoissue
												
												$qry = "UPDATE worequirements
														SET autoissue = 0
														WHERE autoissue = 1
															AND stockid = '".$rows['stockid']."'
															AND wo = $wo";
					                    		$r = DB_query($qry,$db);
					                    		prnMsg("El producto ".$rows['stockid']." no tiene disponibilidad, hacer la descarga de almacen manualmente","warning");
											
											}else{
													
												//generar mov de almacen y contable
												$Result = DB_Txn_Begin($db);
												/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
												$WOIssueNo = GetNextTransNo(28, $db);
												$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
												$SQLIssuedDate = date("Y-m-d");
												$StockGLCode = GetStockGLCode($rows['stockid'],$db);
												$NewQtyOnHand = 0;
												
												if ($rows['mbflag']=='M' OR $rows['mbflag']=='B'){
													
													$qry = "UPDATE locstock
															SET quantity = locstock.quantity - " . $requerido . "
															WHERE locstock.stockid = '" . $rows['stockid'] . "'
																	AND loccode = '$location'";
													$r = DB_query($qry,$db);
															 
													$SQL="SELECT locstock.quantity
															FROM locstock
															 WHERE locstock.stockid='" . $rows['stockid'] . "'
															 AND loccode= '" . $location . "'";
													$Res = DB_query($SQL, $db);
													
													if (DB_num_rows($Res)==1){
															 		
														$LocQtyRow = DB_fetch_row($Res);
														$NewQtyOnHand = $LocQtyRow[0];
													}else{
					                    				
					                    				/*There must actually be some error this should never happen */
					                    				$NewQtyOnHand = 0;
													}
												}
															 		 
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
																				tagref)
														VALUES ('" . $rows['stockid'] . "',
						                            			28,
															 	" . $WOIssueNo . ",
															 	'" . $location . "',
															 	'" . Date('Y-m-d') . "',
															 	" . $Cost . ",
															 	" . $PeriodNo . ",
															 	'" . $wo . "',
															 	" . -$requerido . ",
															 	" . $Cost . ",
															 	'" . $NewQtyOnHand . "',
															 	'".$tag."')";
												$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
												$DbgMsg =  _('The following SQL to insert the stock movement records was used');
												$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
												
									
					                    		/*Get the ID of the StockMove... */
					                    		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
					                    		/* Do the Controlled Item INSERTS HERE */
					           
					           					if ($_SESSION['CompanyRecord']['gllink_stock']==1){
					                    					 	
					                    			/*GL integration with stock is activated so need the GL journals to make it so */
					            					/*first the debit the WIP of the item being manufactured from the WO
														the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
														it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/
									
													$SQL = "INSERT INTO gltrans (type,
															 			typeno,
															 			trandate,
															 			periodno,
															 			account,
															 			narrative,
															 			amount)
															 VALUES (28,
							                       					" . $WOIssueNo . ",
																	'" . Date('Y-m-d') . "',
															 		" . $PeriodNo . ",
																	'" . $rows['wipact'] . "',
																	'" . $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
																	" . ($Cost * $requerido) . ")";
													$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The issue of the item to the work order GL posting could not be inserted because');
													$DbgMsg = _('The following SQL to insert the work order issue GLTrans record was used');
													
													$tipodocto = 28;
													$SQLInvoiceDate = Date('Y-m-d');
													$Narrative =  $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
													$rate = 1;
													$montocontable = ($Cost * $requerido);
													$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $rows['wipact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
															'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INV PROD EN PROCESO',$wo);
													
													$Result = DB_query($ISQL,$db,$ErrMsg, $DbgMsg, true);
													
													/*now the credit Stock entry*/
													$SQL = "INSERT INTO gltrans (type,
																				typeno,
																				trandate,
																				periodno,
																				account,
																				narrative,
																				amount)
															VALUES (28
																	" . $WOIssueNo . ",
																	'" . Date('Y-m-d') . "',
																	" . $PeriodNo . ",
																	'" . $StockGLCode['stockact'] . "',
																	'" . $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
																	" . -($Cost * $requerido) . ")";
													$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock account credit on the issue of items to a work order GL posting could not be inserted because');
					                    			$DbgMsg =  _('The following SQL to insert the stock GLTrans record was used');
													
					                    			$tipodocto = 28;
					                    			$SQLInvoiceDate = Date('Y-m-d');
					                    			$Narrative = $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
					                    			$rate = 1;
					                    			$montocontable = -($Cost * $requerido);
					                    			$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
					                    					'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INVENTARIOS',$wo);
					                    			
					                    			$Result = DB_query($ISQL,$db, $ErrMsg, $DbgMsg,true);
					           					
					           					} /* end of if GL and stock integrated and standard cost !=0 */
												
					           					$Result = DB_Txn_Commit($db);
																								 
											}//mov de almacen y contable
										}//autoissue
									}//while buscar componentes autoissue	
								}
							}
						}
						
						prnMsg($MSG, "info");
					}
				}
			}
		}elseif($onewoforqty==2){
			$flagcantidad = 1;

			$qry = "SELECT salesorderdetails.woline
					FROM salesorderdetails
					WHERE woline IS NOT NULL
						AND orderno = '".$orderno."'
					ORDER BY orderno desc
					limit 1";
			
			$rsrow = DB_query($qry,$db);
			if(DB_num_rows($rsrow) > 0){
				
				$qry = "SELECT tags.legalid,
								tags.tagref
						FROM locations JOIN tags ON locations.tagref = tags.tagref
						WHERE loccode = '".$location."'";
				$LocResult = DB_query($SQL4,$db);
				$LegalRefRow = DB_fetch_row($LocResult);
				if (is_null($LegalRefRow[0]) OR $LegalRefRow[0]==0){
						
					$legalid = 0;
					$tag = 0;
						
				}else{
						
					$legalid = $LegalRefRow[0];
					$tag = $LegalRefRow[1];
				}
				
				$row2 = DB_fetch_array($rsrow);
				$wo = $row2['woline'];
					
				$qry1 = "SELECT *
						FROM salesorderdetails
						WHERE woline IS NULL
						AND orderno = '".$orderno."'
						ORDER BY orderno desc
						limit 1";
					$rsrow1 = DB_query($qry1,$db);
					
					while ($myrow = DB_fetch_array($rsrow1)){
						
						$stkid = $myrow['stkcode'];
						$qty = $myrow['quantity'];
						$orderline = $myrow['orderlineno'];
						if ($stkid!="" && $qty > 0){
						
							$sql = "SELECT mbflag,
										eoq,
										controlled
								FROM stockmaster
								WHERE stockid='" . $stkid . "'";
							$CheckItemResult = DB_query($sql,$db);
								
							if (DB_num_rows($CheckItemResult)==1){
						
								$CheckItemRow = DB_fetch_array($CheckItemResult);
								$EOQ = $qty;
						
								if ($CheckItemRow['controlled']==1 && $_SESSION['DefineControlledOnWOEntry']==1){ //need to add serial nos or batches to determine quantity
										
									$EOQ = 0;
								}
						
								if ($CheckItemRow['mbflag']=='M'){
						
									$sql2 = "SELECT SUM((avgcost)*bom.quantity) AS cost
								 		FROM stockmaster
								 			JOIN bom ON stockmaster.stockid=bom.component
										 	JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = ".$legalid."
										 WHERE bom.parent='" . $stkid . "'";
									$CostResult = DB_query($sql2, $db);
									$CostRow = DB_fetch_row($CostResult);
						
									if (is_null($CostRow[0]) || $CostRow[0]==0){
						
										if ($_SESSION['TypeCostStock']==1){
												
											$Cost = StockAvgcost($stkid, $tag, $db);
						
										}
						
										if ($_SESSION['TypeCostStock']==2){
												
											$Cost = StockAvgcostXLegal($stkid, $tag, $db);
						
										}
						
									}else{
						
										$Cost = $CostRow[0];
						
									}
						
						
									//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
									//insertRequirements($db,$wo,$stkid,'',$EOQ,$stkid);
									insertRequirements($db,$wo,$stkid,'',$EOQ,0,0,$stkid);//
									$v = round(calculate_cost($db,$stkid,'',1,0),2);
									if ($v > 0){
						
										$Cost = $v;
						
									}
										
									// insert parent item info
									$sql = "INSERT INTO woitems (wo,
															stockid,
															qtyreqd,
															stdcost,
															orderline)
										 VALUES ( " . $wo . ",
												 '" . $stkid . "',
												 " . $EOQ . ",
												 " . $Cost . ",
												 " .$orderline.")";
									$ErrMsg = _('The work order item could not be added');
									$result = DB_query($sql,$db,$ErrMsg);
								}
							}
						}
					}
					$qry1 = "SELECT *
						FROM salesorderdetails
						WHERE woline IS NULL
						AND orderno = '".$orderno."'
						ORDER BY orderno desc
						limit 1";
					$rsrow1 = DB_query($qry1,$db);
					if(DB_num_rows($rsrow1) >0){
						
					
						//buscar componentes autoissue
						$qry = "SELECT worequirements.stockid,
					      			autoissue,
					       			qtypu,
									stockmaster.controlled,
									stockmaster.serialised,
									mbflag,
									stockcategory.wipact
					 		FROM worequirements
								INNER JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
								INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
							WHERE autoissue = 1
								AND  wo=" . $wo;
						$rs = DB_query($qry,$db);
						while ($rows = DB_fetch_array($rs)){
								
							if ($rows['autoissue']==1){
									
								$requerido = $EOQ*$rows['qtypu'];
								//ver si hay disponibilidad
								$qry = "SELECT quantity
										FROM locstock
										WHERE loccode = '$location'
										AND stockid = '".$rows['stockid']."'";
								$rsdisp = DB_query($qry,$db);
								$reg = DB_fetch_array($rsdisp);
								$disp = $reg['quantity'];
						
								if ($disp==""){
									$disp=0;
								}
								
								if ($disp < $requerido ){ //poner producto como no autoissue
								
									$qry = "UPDATE worequirements
											SET autoissue = 0
											WHERE autoissue = 1
											AND stockid = '".$rows['stockid']."'";
											$r = DB_query($qry,$db);
									prnMsg("El producto ".$rows['stockid']." no tiene disponibilidad, hacer la descarga de almacen manualmente","warning");
								}else{
				
									//generar mov de almacen y contable
									$Result = DB_Txn_Begin($db);
									/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
								
									$QRY = "SELECT type,
													transno
											FROM stockmoves
											WHERE reference = '".$wo."'";
									$Resultry = DB_query($QRY, $db);
									$rowry = DB_fetch_array($Resultry);
									$type = $rowry['type'];
									$WOIssueNo = $rowry['transno'];
									$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
									$SQLIssuedDate = date("Y-m-d");
									$StockGLCode = GetStockGLCode($rows['stockid'],$db);
									
									if ($rows['mbflag']=='M' OR $rows['mbflag']=='B'){
									
										$qry = "UPDATE locstock
												SET quantity = locstock.quantity - " . $requerido . "
												WHERE locstock.stockid = '" . $rows['stockid'] . "'
												AND loccode = '$location'";
										$r = DB_query($qry,$db);
						
										$SQL="SELECT locstock.quantity
												FROM locstock
												WHERE locstock.stockid='" . $rows['stockid'] . "'
												AND loccode= '" . $location . "'";
										$Res = DB_query($SQL, $db);
				
										if (DB_num_rows($Res)==1){
				
											$LocQtyRow = DB_fetch_row($Res);
											$NewQtyOnHand = $LocQtyRow[0];
				
										}else{
											/*There must actually be some error this should never happen */
											
											$NewQtyOnHand = 0;
						
										}
												
									}else{
										
										$NewQtyOnHand = 0;
									}
									
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
																	tagref)
											VALUES ('" . $rows['stockid'] . "',
													'".$type."',
													" . $WOIssueNo . ",
													'" . $location . "',
													'" . Date('Y-m-d') . "',
													" . $Cost . ",
													" . $PeriodNo . ",
													'" . $wo . "',
													" . -$requerido . ",
													" . $Cost . ",
													'" . $NewQtyOnHand . "',
													'".$tag."')";
					
									$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
									$DbgMsg =  _('The following SQL to insert the stock movement records was used');
									
									$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				
									/*Get the ID of the StockMove... */
									$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
																
									if ($_SESSION['CompanyRecord']['gllink_stock']==1){
						
										/*GL integration with stock is activated so need the GL journals to make it so */
										/*first the debit the WIP of the item being manufactured from the WO
										the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
										it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/
				
										$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
												VALUES ('".$type."',
														" . $WOIssueNo . ",
														'" . Date('Y-m-d') . "',
														" . $PeriodNo . ",
														'" . $rows['wipact'] . "',
														'" . $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
														" . ($Cost * $requerido) . ")";
										$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The issue of the item to the work order GL posting could not be inserted because');
										$DbgMsg = _('The following SQL to insert the work order issue GLTrans record was used');
						
																$tipodocto = 28;
										$SQLInvoiceDate = Date('Y-m-d');
										$Narrative = $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
										$rate = 1;
										$montocontable = ($Cost * $requerido);
										$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $rows['wipact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
															'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INV PROD EN PROCESO',$wo);
										
										$Result = DB_query($ISQL,$db,$ErrMsg, $DbgMsg, true);
															
										/*now the credit Stock entry*/
										$SQL = "INSERT INTO gltrans (type,
																	typeno,
																	trandate,
																	periodno,
																	account,
																	narrative,
																	amount)
												VALUES ('".$type."',
														" . $WOIssueNo . ",
														'" . Date('Y-m-d') . "',
														" . $PeriodNo . ",
														'" . $StockGLCode['stockact'] . "',
														'" . $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
														" . -($Cost * $requerido) . ")";
										$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock account credit on the issue of items to a work order GL posting could not be inserted because');
										$DbgMsg =  _('The following SQL to insert the stock GLTrans record was used');
						
										$tipodocto = $type;
										$SQLInvoiceDate = Date('Y-m-d');
										$Narrative = $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
										$rate = 1;
										$montocontable = -($Cost * $requerido);
										$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
												'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INVENTARIOS',$wo);
										
										$Result = DB_query($ISQL,$db, $ErrMsg, $DbgMsg,true);
									}/* end of if GL and stock integrated and standard cost !=0 */
								}//mov de almacen y contable
							}//autoissue
						}//while buscar componentes autoissue
						$SQL = "UPDATE salesorderdetails
								SET woline = '".$wo."'
								WHERE orderno = '".$orderno."'
									AND woline IS NULL";
						$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
						prnMsg(_('Se genero la Orden de Trabajo No. ') . $wo,'success');
					}
					
			}else{
				$flagcantidad = 1;
				$contadorimpr = 0;
				
				foreach($arritems as $arrdata){
					$contadorimpr = $contadorimpr + 1;
					
					$orderline = $arrdata['orderline'];
					$stkid = $arrdata['stockid'];
					if($flagcantidad == 1){
						$wo = GetNextTransNo(40,$db);
					}
					$qry = "SELECT salesorderdetails.fromstkloc
							FROM salesorderdetails
							WHERE salesorderdetails.orderlineno = $orderline
								AND salesorderdetails.orderno = $orderno";
					$rsrow = DB_query($qry,$db);
					$row = DB_fetch_array($rsrow);
					$locationot = $row[0];
						
					if(empty($_SESSION['DefaultWOLocation']) == false) {
						$locationot = $_SESSION['DefaultWOLocation'];
					}
				
					$qry="SELECT salesstockproperties.valor
						FROM salesorderdetails
							INNER JOIN salesstockproperties ON salesorderdetails.orderno = salesstockproperties.orderno AND salesorderdetails.orderlineno = salesstockproperties.orderlineno
							INNER JOIN  stockcatproperties on stockcatproperties.stkcatpropid=salesstockproperties.stkcatpropid
						WHERE stockcatproperties.controltype=3 AND salesorderdetails.orderlineno = $orderline
							AND salesorderdetails.orderno = $orderno";
					$rsrow = DB_query($qry,$db);
				
					if (DB_num_rows($rsrow) > 0){
						$row = DB_fetch_array($rsrow);
						$arrdata = explode("/",$row[0]);
						$mes = $arrdata[1];
						$dia = $arrdata[2];
						$anio = $arrdata[0];
						$startdate =$anio."-".$mes."-".$dia;
					}
				
					$SQL = "SELECT idstatus,
							 	panelcontrolcategoria.userid
							FROM wocontrolpanel_status
								INNER JOIN stockcategory ON stockcategory.CodigoPanelControl = wocontrolpanel_status.panelcontrol
								INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
								INNER JOIN panelcontrolcategoria ON stockcategory.CodigoPanelControl = panelcontrolcategoria.CodigoPanelControl
							WHERE active = 1
								AND  wocontrolpanel_status.idstatus = panelcontrolcategoria.estatusinicial
								AND stockmaster.stockid = '".$stkid."'";
					$rss = DB_query($SQL,$db);
					$regs = DB_fetch_array($rss);
					$ids = $regs['idstatus'];
					$user = $regs['userid'];
				
					if($user <> ""){
							
						$u_user = $user;
				
					}
					// Envio de notificacion para usuario que recibe ot
					$SQLuser = "SELECT * FROM www_users where userid= '".$u_user."'";
					$regsmail = DB_query($SQLuser,$db);
					$rowregs = DB_fetch_array($regsmail);
					$mailuser = $rowregs['email'];
					$textomail="Se genero nueva orden de trabajo numero: ".$wo;
					$SQL2 = "SELECT longdescription
							FROM stockmaster
							WHERE stockid = '".$stkid."'";
					$rss2 = DB_query($SQL2, $db);
					$regs2 = DB_fetch_array($rss2);
					$desproducto = $regs2['longdescription'];
					$textomail.="<br>Producto ".$stkid." ".$desproducto;
					$SQL2 = "SELECT debtorsmaster.name,
									www_users.realname
							FROM salesorders
								INNER JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
								INNER JOIN www_users ON salesorders.UserRegister = www_users.userid
							WHERE orderno = '".$orderno."'";
					$rss2 = DB_query($SQL2, $db);
					$regs2 = DB_fetch_array($rss2);
					$cliente = $regs2['name'];
					$vendedor = $regs2['realname'];//
					$textomail.="<br>Cliente ".$cliente."<br>Vendedor ".$vendedor;
					$headers = "MIME-Version: 1.0\r\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
					$headers .= "From: Notificacion de Orden de Trabajo <juan.mendoza@tecnoaplicada.com>\r\n";
					$arraymails=explode(',',$mailuser);
					// envia a funcion por email digitado
					for($cad=0;$cad<=count($arraymails)-1;$cad++){
						$mailuser=$arraymails[$cad];
						mail($mailuser,'Nueva Orden de trabajo en '.$_SESSION['DatabaseName'],$textomail,$headers) ;
					}
					
					
					
					if($flagcantidad == 1){
						$qry = "INSERT INTO workorders (wo,
										   			loccode,
										   			requiredby,
										   			startdate,
										  			orderno,
										   			idstatus,
										  			fecha_alta,
													u_user)
							VALUES (" . $wo . ",
									'" . $locationot . "',
									'" . $requiredat. "',
									'" . $startdate . "',
									'".$orderno."',
									'".$ids."',
												now(),
												'$u_user')";
						$InsWOResult = DB_query($qry,$db);
						$flagcantidad = 0;
					}
				
					$qry = "SELECT tags.legalid,
								tags.tagref
							FROM locations JOIN tags ON locations.tagref = tags.tagref
							WHERE loccode = '".$location."'";
					$LocResult = DB_query($SQL4,$db);
					$LegalRefRow = DB_fetch_row($LocResult);
					if (is_null($LegalRefRow[0]) OR $LegalRefRow[0]==0){
				
						$legalid = 0;
						$tag = 0;
				
					}else{
						
						$legalid = $LegalRefRow[0];
						$tag = $LegalRefRow[1];
					}
				
					//foreach($arritems as $arrdata){
				
						$stkid = $arrdata['stockid'];
						$qty = $arrdata['qty'];
						$orderline = $arrdata['orderline'];
					
						if ($stkid!="" && $qty > 0){
				
							$sql = "SELECT mbflag,
											eoq,
											controlled
									FROM stockmaster
									WHERE stockid='" . $stkid . "'";
							$CheckItemResult = DB_query($sql,$db);
							
							if (DB_num_rows($CheckItemResult)==1){
				
								$CheckItemRow = DB_fetch_array($CheckItemResult);
								$EOQ = $qty;
				
								if ($CheckItemRow['controlled']==1 && $_SESSION['DefineControlledOnWOEntry']==1){ //need to add serial nos or batches to determine quantity
									
									$EOQ = 0;
								}
				
								if ($CheckItemRow['mbflag']=='M'){
				
									$sql2 = "SELECT SUM((avgcost)*bom.quantity) AS cost
								 			FROM stockmaster
								 				JOIN bom ON stockmaster.stockid=bom.component
										 		JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = ".$legalid."
											 WHERE bom.parent='" . $stkid . "'";
									$CostResult = DB_query($sql2, $db);
									$CostRow = DB_fetch_row($CostResult);
				
									if (is_null($CostRow[0]) || $CostRow[0]==0){
				
										if ($_SESSION['TypeCostStock']==1){
											
											$Cost = StockAvgcost($stkid, $tag, $db);
				
										}
				
										if ($_SESSION['TypeCostStock']==2){
											
											$Cost = StockAvgcostXLegal($stkid, $tag, $db);
				
										}
										
									}else{
				
										$Cost = $CostRow[0];
										
									}
				
				
									//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
									//insertRequirements($db,$wo,$stkid,'',$EOQ,$stkid);
									insertRequirements($db,$wo,$stkid,'',$EOQ,0,0,$stkid);//
									$v = round(calculate_cost($db,$stkid,'',1,0),2);
									if ($v > 0){
				
										$Cost = $v;
				
									}
									
									// insert parent item info
									$sql = "INSERT INTO woitems (wo,
															stockid,
															qtyreqd,
															stdcost,
															orderline)
											 VALUES ( " . $wo . ",
													 '" . $stkid . "',
													 " . $EOQ . ",
													 " . $Cost . ",
													 " .$orderline.")";
									$ErrMsg = _('The work order item could not be added');
									$result = DB_query($sql,$db,$ErrMsg);
								}
							}
						}
					//}
				
					//buscar componentes autoissue
					$qry = "SELECT worequirements.stockid,
				      				autoissue,
				       				qtypu,
									stockmaster.controlled,
									stockmaster.serialised,
									mbflag,
									stockcategory.wipact
				 			FROM worequirements
								INNER JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
								INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
							WHERE wo=" . $wo;
					$rs = DB_query($qry,$db);
					while ($rows = DB_fetch_array($rs)){
					
						if ($rows['autoissue']==1){
					
							$requerido = $EOQ*$rows['qtypu'];
							//ver si hay disponibilidad
							$qry = "SELECT quantity
									FROM locstock
									WHERE loccode = '$location'
									AND stockid = '".$rows['stockid']."'";
							$rsdisp = DB_query($qry,$db);
							$reg = DB_fetch_array($rsdisp);
							$disp = $reg['quantity'];
								
							if ($disp==""){
								
								$disp=0;
								
						}
							
						if ($disp < $requerido ){ //poner producto como no autoissue
					
							$qry = "UPDATE worequirements
									SET autoissue = 0
									WHERE autoissue = 1
										AND stockid = '".$rows['stockid']."'";
							$r = DB_query($qry,$db);
							prnMsg("El producto ".$rows['stockid']." no tiene disponibilidad, hacer la descarga de almacen manualmente","warning");
						}else{
					
								//generar mov de almacen y contable
							$Result = DB_Txn_Begin($db);
							/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
							$WOIssueNo = GetNextTransNo(28, $db);
							$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
							$SQLIssuedDate = date("Y-m-d");
							$StockGLCode = GetStockGLCode($rows['stockid'],$db);
					
							if ($rows['mbflag']=='M' OR $rows['mbflag']=='B'){
					
								$qry = "UPDATE locstock
										SET quantity = locstock.quantity - " . $requerido . "
										WHERE locstock.stockid = '" . $rows['stockid'] . "'
											AND loccode = '$location'";
								$r = DB_query($qry,$db);
																
								$SQL="SELECT locstock.quantity
										FROM locstock
										WHERE locstock.stockid='" . $rows['stockid'] . "'
											AND loccode= '" . $location . "'";
								$Res = DB_query($SQL, $db);
					
								if (DB_num_rows($Res)==1){
					
									$LocQtyRow = DB_fetch_row($Res);
									$NewQtyOnHand = $LocQtyRow[0];
					
								}else{
									/*There must actually be some error this should never happen */
													
									$NewQtyOnHand = 0;
													 
								}
													
							}else{
								$NewQtyOnHand = 0;
							}
					
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
															tagref)
									VALUES ('" . $rows['stockid'] . "',
											28,
											" . $WOIssueNo . ",
											'" . $location . "',
											'" . Date('Y-m-d') . "',
											" . $Cost . ",
											" . $PeriodNo . ",
											'" . $wo . "',
											" . -$requerido . ",
											" . $Cost . ",
											'" . $NewQtyOnHand . "',
											'".$tag."')";
							
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
							$DbgMsg =  _('The following SQL to insert the stock movement records was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					
							/*Get the ID of the StockMove... */
							$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
					
							if ($_SESSION['CompanyRecord']['gllink_stock']==1){
						
								/*GL integration with stock is activated so need the GL journals to make it so */
								/*first the debit the WIP of the item being manufactured from the WO
									the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
								 it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/
					
								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (28,
												" . $WOIssueNo . ",
												'" . Date('Y-m-d') . "',
												" . $PeriodNo . ",
												'" . $rows['wipact'] . "',
												'" . $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
												" . ($Cost * $requerido) . ")";
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The issue of the item to the work order GL posting could not be inserted because');
								$DbgMsg = _('The following SQL to insert the work order issue GLTrans record was used');
						
								$tipodocto = 28;
								$SQLInvoiceDate = Date('Y-m-d');
								$Narrative = $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
								$rate = 1;
								$montocontable = ($Cost * $requerido);
								$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $rows['wipact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
														'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INV PROD EN PROCESO',$wo);
								
								$Result = DB_query($ISQL,$db,$ErrMsg, $DbgMsg, true);
					
								/*now the credit Stock entry*/
								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (28,
												" . $WOIssueNo . ",
												'" . Date('Y-m-d') . "',
												" . $PeriodNo . ",
												'" . $StockGLCode['stockact'] . "',
												'" . $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
												" . -($Cost * $requerido) . ")";
								$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock account credit on the issue of items to a work order GL posting could not be inserted because');
								$DbgMsg =  _('The following SQL to insert the stock GLTrans record was used');
						
								$tipodocto = 28;
								$SQLInvoiceDate = Date('Y-m-d');
								$Narrative = $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
								$rate = 1;
								$montocontable = -($Cost * $requerido);
								$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
											'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INVENTARIOS',$wo);

								$Result = DB_query($ISQL,$db, $ErrMsg, $DbgMsg,true);
							}/* end of if GL and stock integrated and standard cost !=0 */
						}//mov de almacen y contable
					}//autoissue
				}//while buscar componentes autoissue
				
				$SQL = "UPDATE salesorderdetails
						SET woline = '".$wo."'
						WHERE orderno = '".$orderno."'";
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
				prnMsg(_('Se genero la Orden de Trabajo No. ') . $wo,'success');
				//echo "<br>Consultar Orden de Trabajo No. <a target='_blank' href='WorkOrderCosting.php?&WO=$wo'> $wo</a>";
				}
			}
		}elseif (count($arritems)>0){
		
			$flagcantidad = 1;
			//$startdate = getNextDateTimeToWorkOrder($db); // Se comento para que generara las ordenes de trabajo con el dia actual
			
			foreach($arritems as $arrdata){
				$orderline = $arrdata['orderline'];
				$stkid = $arrdata['stockid'];
				if($flagcantidad == 1){
					$wo = GetNextTransNo(40,$db);
				}
				$qry = "SELECT salesorderdetails.fromstkloc
						FROM salesorderdetails
						WHERE salesorderdetails.orderlineno = $orderline
						AND salesorderdetails.orderno = $orderno";
				$rsrow = DB_query($qry,$db);
				$row = DB_fetch_array($rsrow);
				$locationot = $row[0];
			
				if(empty($_SESSION['DefaultWOLocation']) == false) {
					$locationot = $_SESSION['DefaultWOLocation'];
				}
		
				$qry="SELECT salesstockproperties.valor
						FROM salesorderdetails
							INNER JOIN salesstockproperties ON salesorderdetails.orderno = salesstockproperties.orderno AND salesorderdetails.orderlineno = salesstockproperties.orderlineno
							INNER JOIN  stockcatproperties on stockcatproperties.stkcatpropid=salesstockproperties.stkcatpropid
						WHERE stockcatproperties.controltype=3 AND salesorderdetails.orderlineno = $orderline
							AND salesorderdetails.orderno = $orderno";
				$rsrow = DB_query($qry,$db);
		
				if (DB_num_rows($rsrow) > 0){
					$row = DB_fetch_array($rsrow);
					$arrdata = explode("/",$row[0]);
					$mes = $arrdata[1];
					$dia = $arrdata[2];
					$anio = $arrdata[0];
					$startdate =$anio."-".$mes."-".$dia;
				}
		
				$SQL = "SELECT idstatus,
						 	panelcontrolcategoria.userid
						FROM wocontrolpanel_status
							INNER JOIN stockcategory ON stockcategory.CodigoPanelControl = wocontrolpanel_status.panelcontrol
							INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
							INNER JOIN panelcontrolcategoria ON stockcategory.CodigoPanelControl = panelcontrolcategoria.CodigoPanelControl
						WHERE active = 1
							AND  wocontrolpanel_status.idstatus = panelcontrolcategoria.estatusinicial
							AND stockmaster.stockid = '".$stkid."'";
				$rss = DB_query($SQL,$db);
				$regs = DB_fetch_array($rss);
				$ids = $regs['idstatus'];
				$user = $regs['userid'];
				
				if($user <> ""){
					
					$u_user = $user;
				
				}
				if($flagcantidad == 1){
					$qry = "INSERT INTO workorders (wo,
									   			loccode,
									   			requiredby,
									   			startdate,
									  			orderno,
									   			idstatus,
									  			fecha_alta,
												u_user)
						VALUES (" . $wo . ",
								'" . $locationot . "',
								'" . $requiredat. "',
								'" . $startdate . "',
								'".$orderno."',
								'".$ids."',
									now(),
								'$u_user')";
					$InsWOResult = DB_query($qry,$db);
					$flagcantidad = 0;
				}
				
			}

			$qry = "SELECT tags.legalid,
							tags.tagref
					FROM locations JOIN tags ON locations.tagref = tags.tagref
					WHERE loccode = '".$location."'";
			$LocResult = DB_query($SQL4,$db);
			$LegalRefRow = DB_fetch_row($LocResult);
			if (is_null($LegalRefRow[0]) OR $LegalRefRow[0]==0){
				
				$legalid = 0;
				$tag = 0;
				
			}else{
			
				$legalid = $LegalRefRow[0];
				$tag = $LegalRefRow[1];
			}
	
			foreach($arritems as $arrdata){
	
				$stkid = $arrdata['stockid'];
				$qty = $arrdata['qty'];
				$orderline = $arrdata['orderline'];
		
				if ($stkid!="" && $qty > 0){
	
					$sql = "SELECT mbflag,
									eoq,
									controlled
							FROM stockmaster
							WHERE stockid='" . $stkid . "'";
					$CheckItemResult = DB_query($sql,$db);
					
					if (DB_num_rows($CheckItemResult)==1){

						$CheckItemRow = DB_fetch_array($CheckItemResult);
						$EOQ = $qty;

						if ($CheckItemRow['controlled']==1 && $_SESSION['DefineControlledOnWOEntry']==1){ //need to add serial nos or batches to determine quantity
					
							$EOQ = 0;
						}
				
						if ($CheckItemRow['mbflag']=='M'){
	
							$sql2 = "SELECT SUM((avgcost)*bom.quantity) AS cost
							 		FROM stockmaster
							 			JOIN bom ON stockmaster.stockid=bom.component
									 	JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = ".$legalid."
									 WHERE bom.parent='" . $stkid . "'";
							$CostResult = DB_query($sql2, $db);
							$CostRow = DB_fetch_row($CostResult);
	
							if (is_null($CostRow[0]) || $CostRow[0]==0){
										  				
								if ($_SESSION['TypeCostStock']==1){
									
									$Cost = StockAvgcost($stkid, $tag, $db);
								
								}		  					
										  				
								if ($_SESSION['TypeCostStock']==2){
										  					
									$Cost = StockAvgcostXLegal($stkid, $tag, $db);
								
								}
							
							}else{
										
								$Cost = $CostRow[0];
							
							}
	
	
							//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
							//insertRequirements($db,$wo,$stkid,'',$EOQ,$stkid);
							insertRequirements($db,$wo,$stkid,'',$EOQ,0,0,$stkid);//
							$v = round(calculate_cost($db,$stkid,'',1,0),2);
							if ($v > 0){
								
								$Cost = $v;
								
							}
							
							// insert parent item info
							$sql = "INSERT INTO woitems (wo,
														stockid,
														qtyreqd,
														stdcost,
														orderline)
									 VALUES ( " . $wo . ",
											 '" . $stkid . "',
											 " . $EOQ . ",
											 " . $Cost . ",
											 " .$orderline.")";
							$ErrMsg = _('The work order item could not be added');
							$result = DB_query($sql,$db,$ErrMsg);
						}
					}
				}
			}
	
			//buscar componentes autoissue
			$qry = "SELECT worequirements.stockid,
			      			autoissue,
			       			qtypu,
							stockmaster.controlled,
							stockmaster.serialised,
							mbflag,
							stockcategory.wipact
			 		FROM worequirements
						INNER JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
						INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					WHERE wo=" . $wo;
			$rs = DB_query($qry,$db);
			while ($rows = DB_fetch_array($rs)){
				
				if ($rows['autoissue']==1){
								
					$requerido = $EOQ*$rows['qtypu'];
					//ver si hay disponibilidad
					$qry = "SELECT quantity 
							FROM locstock
							WHERE loccode = '$location'
								AND stockid = '".$rows['stockid']."'";
					$rsdisp = DB_query($qry,$db);
					$reg = DB_fetch_array($rsdisp);
					$disp = $reg['quantity'];
			
					if ($disp==""){
					
						$disp=0;
					
					}		
					
					if ($disp < $requerido ){ //poner producto como no autoissue
	
							$qry = "UPDATE worequirements
									SET autoissue = 0
									WHERE autoissue = 1
									AND stockid = '".$rows['stockid']."'";
								$r = DB_query($qry,$db);
								prnMsg("El producto ".$rows['stockid']." no tiene disponibilidad, hacer la descarga de almacen manualmente","warning");
					}else{
					
						//generar mov de almacen y contable
						$Result = DB_Txn_Begin($db);
						/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
						$WOIssueNo = GetNextTransNo(28, $db);
						$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
						$SQLIssuedDate = date("Y-m-d");
						$StockGLCode = GetStockGLCode($rows['stockid'],$db);
						
						if ($rows['mbflag']=='M' OR $rows['mbflag']=='B'){
	
							$qry = "UPDATE locstock
									SET quantity = locstock.quantity - " . $requerido . "
									WHERE locstock.stockid = '" . $rows['stockid'] . "'
									AND loccode = '$location'";
							$r = DB_query($qry,$db);
							$SQL="SELECT locstock.quantity
									FROM locstock
									WHERE locstock.stockid='" . $rows['stockid'] . "'
										AND loccode= '" . $location . "'";
							$Res = DB_query($SQL, $db);
										
							if (DB_num_rows($Res)==1){
								
								$LocQtyRow = DB_fetch_row($Res);
								$NewQtyOnHand = $LocQtyRow[0];
								
							}else{
							
								/*There must actually be some error this should never happen */
								$NewQtyOnHand = 0;
							}
						}else{
							$NewQtyOnHand = 0;
						}
	
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
														tagref)
								VALUES ('" . $rows['stockid'] . "',
										28,
										" . $WOIssueNo . ",
										'" . $location . "',
										'" . Date('Y-m-d') . "',
										" . $Cost . ",
										" . $PeriodNo . ",
										'" . $wo . "',
										" . -$requerido . ",
							   			 " . $Cost . ",
									    '" . $NewQtyOnHand . "',
									    '".$tag."')";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
						$DbgMsg =  _('The following SQL to insert the stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
												
						/*Get the ID of the StockMove... */
						$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
						
						if ($_SESSION['CompanyRecord']['gllink_stock']==1){
							
							/*GL integration with stock is activated so need the GL journals to make it so */
							/*first the debit the WIP of the item being manufactured from the WO
							the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
							it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/
		
							$SQL = "INSERT INTO gltrans (type,
														typeno,
														trandate,
														periodno,
														account,
														narrative,
														amount)
									VALUES (28,
											" . $WOIssueNo . ",
											'" . Date('Y-m-d') . "',
											" . $PeriodNo . ",
											'" . $rows['wipact'] . "',
											'" . $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
											" . ($Cost * $requerido) . ")";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The issue of the item to the work order GL posting could not be inserted because');
							$DbgMsg = _('The following SQL to insert the work order issue GLTrans record was used');
							
							$tipodocto = 28;
							$SQLInvoiceDate = Date('Y-m-d');
							$Narrative = $wo . " " . $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
							$rate = 1;
							$montocontable = ($Cost * $requerido);
							$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $rows['wipact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
									'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INV PROD EN PROCESO',$wo);
							
							$Result = DB_query($ISQL,$db,$ErrMsg, $DbgMsg, true);
		
							/*now the credit Stock entry*/
							$SQL = "INSERT INTO gltrans (type,
							                            typeno,
														trandate,
														periodno,
														account,
														narrative,
														amount)
									VALUES (28,
											" . $WOIssueNo . ",
											'" . Date('Y-m-d') . "',
											" . $PeriodNo . ",
											'" . $StockGLCode['stockact'] . "',
											'" . $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2) . "',
											" . -($Cost * $requerido) . ")";
							$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock account credit on the issue of items to a work order GL posting could not be inserted because');
							$DbgMsg =  _('The following SQL to insert the stock GLTrans record was used');
							
							$tipodocto = 28;
							$SQLInvoiceDate = Date('Y-m-d');
							$Narrative = $wo . " " .  $rows['stockid'] . ' x ' . $requerido . " @ " . number_format($Cost,2);
							$rate = 1;
							$montocontable = -($Cost * $requerido);
							$ISQL = Insert_Gltrans($tipodocto,$WOIssueNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tag ,$_SESSION['UserID'],$rate,
									'','',$rows['stockid'],$requerido,0,$location,$Cost,"",0, $montocontable,$db,'','INVENTARIOS',$wo);
							$Result = DB_query($ISQL,$db, $ErrMsg, $DbgMsg,true);
						}/* end of if GL and stock integrated and standard cost !=0 */
					}//mov de almacen y contable
				}//autoissue
			}//while buscar componentes autoissue
		
			prnMsg(_('Se genero la Orden de Trabajo No. ') . $wo,'success');
			//echo "<br>Consultar Orden de Trabajo No. <a target='_blank' href='WorkOrderCosting.php?&WO=$wo'> $wo</a>";
		}
	}
	
	$result = DB_Txn_Commit($db);
}




/*FUNCION PARA EXTRAER LA CUENTA CONTABLE POR TIPO DE CLIENTE*/

function ClientAccount($typecliente,$field,$db){
// 0 es cuenta tipo abono
// 1 es cuenta tipo cargo

//gl_accountsreceivable = CUENTAS POR COBRAR.
//gl_notesreceivable = DOCUMENTOS POR COBRAR
//gl_accountcontado = FACTURAS DE CONTADO
//gl_debtoradvances = ANTICIPOS CLIENTES
//gl_taxdebtoradvances = IVA ANTICIPOS CLIENTES
//gl_debtormoratorio = MORATORIOS
	$SQLClientAccount="SELECT ".$field." as campo
	      FROM chartdebtortype 
	      WHERE typedebtorid = ".  $typecliente ; 
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	if (DB_num_rows($ResultTypeClient)>0) {
		$myrowtype = DB_fetch_array($ResultTypeClient);
		$cuenta=$myrowtype[0];
	}
	return $cuenta;
	
	
}

function GetNextChequeNo ($accountcode, &$db){ 
	DB_query("LOCK TABLES bankaccounts WRITE",$db);
	$SQL = "SELECT chequeno FROM bankaccounts WHERE accountcode = '" . $accountcode . "'";
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('El siguiente folio de transaccion no pudo ser obtenido de la base de datos porque');
	$DbgMsg =  _('El siguiente SQL se utilizo para obtener el numero de folio');
	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($GetTransNoResult);
	$SQL = "UPDATE bankaccounts SET chequeno = " . ($myrow[0] + 1) . " WHERE accountcode = '" . $accountcode . "'";
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': ' . _('El numero de folio no se pudo incrementar');
	$DbgMsg =  _('El siguiente SQL se utilizo para incrementar el numero de folio');
	$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	DB_query("UNLOCK TABLES",$db);
	return $myrow[0] + 1;        
}



/*Extrae tipo cliente*/
function ExtractTypeClient($debtorno,$db){
// 0 es cuenta tipo abono
// 1 es cuenta tipo cargo
	$SQLClient="SELECT typeid
		    FROM debtorsmaster
		    WHERE debtorno='".$debtorno."'";
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener el tipo de cliente es:');
	$result_typeclient = DB_query($SQLClient,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($result_typeclient)==1) {
		$myrowtype = DB_fetch_array($result_typeclient);
		$tipocliente=$myrowtype[0];
	}else{
		$tipocliente=0;
	}
	return $tipocliente;
	
}

/*FUNCION PARA EXTRAER LA CUENTA CONTABLE POR TIPO DE PROVEEDOR*/

function SupplierAccount($typesupplier,$field,$db){
// 0 es cuenta tipo abono
// 1 es cuenta tipo cargo

//gl_accountsreceivable  - CUENTA CONTABLE DE CUENTAS X COBRAR 
//gl_notesreceivable  - CUENTA CONTABLE DE DOCUMENTOS X COBRAR
//gl_debtoradvances - CUENTA CONTABLE DE ANTICIPOS DE A PROVEEDORES
//gl_taxdebtoradvances - CUENTA CONTABLE IVA DE ANTICIPOS
//gl_accountcontado - CUENTA CONTABLE DE FACTURAS DE CONTADO
//gl_debtormoratorio - INTERES MORATORIO

	$SQLSupplierAccount="SELECT ".$field." as campo
	      FROM chartsupplierstype 
	      WHERE typedebtorid = '".  $typesupplier . "'" ;
	      //echo "<br>->" . $SQLSupplierAccount;
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeSupplier = DB_query($SQLSupplierAccount,$db);
	if (DB_num_rows($ResultTypeSupplier)>0) {
		$myrowtype = DB_fetch_array($ResultTypeSupplier);
		$cuenta=$myrowtype[0];
	}
	return $cuenta;
	
	
}

/*Extrae tipo proveedor*/
function ExtractTypeSupplier($supplierno,$db){
// 0 es cuenta tipo abono
// 1 es cuenta tipo cargo
	$SQLSupplier="SELECT IFNULL(typeid,'0') as typeid
		    FROM suppliers
		    WHERE supplierid='".$supplierno."'";
	//echo "<br>" . $SQLSupplier;
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener el tipo de cliente es:');
	$result_typesupplier = DB_query($SQLSupplier,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($result_typesupplier)==1) {
		$myrowtype = DB_fetch_array($result_typesupplier);
		$tiposupplier=$myrowtype[0];
	}else{
		$tiposupplier=0;
	}
	return $tiposupplier;
	
}


/***********************************************************************************************************/
/*Extrae limite de credito x depto*/
function LimitXDeptoYClient($cliente,$tagref,$db){

	$SQLClientAccount="	SELECT  holdreasons.validalimite
					   FROM debtorsmaster inner join holdreasons on holdreasons.reasoncode=debtorsmaster.holdreason
			 		  WHERE debtorno='".  $cliente. "'";
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
	$DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	$myrowtype = DB_fetch_array($ResultTypeClient);
	$cuenta=$myrowtype[0];
	IF($cuenta==0){
		return 10000000000000000;
	}
	
	$SQLClientAccount="SELECT  DISTINCT limitecredit,id_cliente 
			   FROM diasxcliente 
			   INNER JOIN tags  ON tags.u_department=diasxcliente.id_depto
			   WHERE id_cliente='".  $cliente. "'
				AND tagref=".$tagref;
	
			
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	if (DB_num_rows($ResultTypeClient)>0) {
		$myrowtype = DB_fetch_array($ResultTypeClient);
		$cuenta=$myrowtype[0];
	}else{
		$cuenta=0;
	}
	return $cuenta;
	
	
}

function LimitDayXDeptoYClient($cliente,$tagref,$db){

	$SQLClientAccount="SELECT  DISTINCT numdias,id_cliente 
			   FROM diasxcliente 
			   INNER JOIN tags  ON tags.u_department=diasxcliente.id_depto
			   WHERE id_cliente='".  $cliente. "'
				AND tagref=".$tagref;
				
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	if (DB_num_rows($ResultTypeClient)>0) {
		$myrowtype = DB_fetch_array($ResultTypeClient);
		$cuenta=$myrowtype[0];
	}else{
		$cuenta=0;
	}
	return $cuenta;
}


/*Extrae saldo de cliente x depto*/
function SaldoClientxDepto($cliente,$depto,$dataware,$db){

	/*$SQLClientAccount="SELECT  sum(case when saldo<0 then 0 else saldo end )
			   FROM ".$dataware.".DWH_AntiguedadSaldosNew INNER JOIN tags on tags.tagref=".$dataware.".DWH_AntiguedadSaldosNew.tagref
			   WHERE tags .u_department=".$depto."
				AND ".$dataware.".DWH_AntiguedadSaldosNew.nocliente='".$cliente."'";*/
	$SQLClientAccount = "select sum(((ovamount + ovgst+interesdevengado+taxinteresdevengado) - alloc)/rate) as saldo
		from debtortrans INNER JOIN tags on tags.tagref=debtortrans.tagref
		where debtorno = '" . $cliente . "'
			and tags.u_department=".$depto;
				
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	if (DB_num_rows($ResultTypeClient)>0) {
		$myrowtype = DB_fetch_array($ResultTypeClient);
		$saldo=$myrowtype[0];
	}else{
		$saldo=0;
	}
	return $saldo;
	
	
}
/************valida si hay mas de un docto vencido para este cliente*/
 
function Validadoctovencido($debtorno,$dias,$db,$claveid){
	
	
	$qry = "SELECT *
	FROM creditauthorization
	WHERE claveid = '$claveid' and datevalidto >= current_date
	AND debtorno = '".$debtorno."'
	Order by registerdate desc
			limit 1";
	$rsclave = DB_query($qry,$db);
	if (DB_num_rows($rsclave) > 0){
		$rowclve = DB_fetch_array($rsclave);
		return 0;
	}
		

	$SQLClientAccount="SELECT  max(numdias) as numdias,id_cliente
			   FROM diasxcliente
			   INNER JOIN tags  ON tags.u_department=diasxcliente.id_depto
			   WHERE id_cliente='".  $debtorno. "'"
				;
	//echo 'dias :'.$SQLClientAccount;
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
	$DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	if (DB_num_rows($ResultTypeClient)>0) {
		$myrowtype = DB_fetch_array($ResultTypeClient);
		$cuentadias=$myrowtype[0];
	}else{
		$cuentadias=0;
	}
	$cuentadias=number_format($cuentadias/7,2);
	$dias=$dias+$cuentadias;
	$montosaldar=$_SESSION['MaxAmountVencido'];
	if(strlen(trim($montosaldar))==0 or $montosaldar==0 ){
		$montosaldar=1;
	}
	$SQLClientAccount="SELECT  sum(((dt.ovamount + dt.ovgst+dt.interesdevengado+dt.taxinteresdevengado) - dt.alloc)/rate) as saldo,debtorno
			   FROM debtortrans dt
			   WHERE (case when DATEDIFF(now(),trandate)<0 then -1 else (DATEDIFF(now(),trandate)+ case when discountpercent is null then 0 else discountpercent end) end)>".$dias."
			    AND type in (10,21,70,110,113,400,410,440,560,96,119,66)
				and debtorno ='".$debtorno."'
			    group by debtorno
			   HAVING abs(saldo)>".$montosaldar;
	//echo '<pre>sql:'.$SQLClientAccount;
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	$cuenta=0;
	if (DB_num_rows($ResultTypeClient)>0) {
		$cuenta=1;
		
	}
//
	return $cuenta;
	
	
	
}

/*extrae id departamento*/
function DeptoXtag($tagdebto,$db){

	$SQLClientAccount="SELECT u_department from tags where tagref=".$tagdebto;
				
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('No se ha configurado la cuenta por tipo de cliente');
        $DbgMsg =  _('El siguiente SQL se utilizo para obtener la cuenta por tipo de cliente es:');
	$ResultTypeClient = DB_query($SQLClientAccount,$db);
	if (DB_num_rows($ResultTypeClient)>0) {
		$myrowtype = DB_fetch_array($ResultTypeClient);
		$depto=$myrowtype[0];
	}else{
		$depto=0;
	}
	return $depto;
}


/***********************************************************************************************************/
/**limite de mosnot para notas de credito*/

function LimitXUserXtagNC($tagrefNC,$moneda,$usuario,$db){

	$sql=" select authlevel
		from Notesordersauth
		WHERE userid='".$usuario."'
		AND currabrev='".$moneda."'
		AND tagref='".$tagrefNC."'";
	$ErrMsg = _('No existe limite de monto para nota de credito');
	$resultunitB=DB_query($sql,$db,$ErrMsg);
	if (DB_num_rows($resultunitB)==0){
		
		$sql=" select authlevel
			from Notesordersauth
			WHERE userid='".$usuario."'
			AND currabrev='".$moneda."'
			AND tagref=0";
		$ErrMsg = _('No existe limite de monto para nota de credito');
		$ResultNC=DB_query($sql,$db,$ErrMsg);
		if (DB_num_rows($ResultNC)>0) {
			$myrowMNC = DB_fetch_array($ResultNC);
			$cuenta=$myrowMNC[0];
		}else{
			$cuenta=0;
		}
	}else{
		$myrowtype = DB_fetch_array($resultunitB);
		$cuenta=$myrowtype[0];
		
	}
	return $cuenta;
}
/***********************************************************************************************************/

// funcion de validacion para propiedades de venta
function ValidaSalesProperty($stockid,$Orden,$ordenline,$valor,$tipo,$db){
	$sql="SELECT count(*) as conta
	      FROM salesstockproperties 
	      WHERE stkcatpropid = '".  $stockid . "' 
			and typedocument = " . $tipo ."
			and orderno = " . $Orden ."
			and orderlineno = " . $ordenline ."
			and valor = '" . $valor ."'";
	$Result = DB_query($sql,$db,'','');
	$myrowResult = DB_fetch_array($Result);
	$cuenta=$myrowResult['conta'];
	if ($cuenta==0){
		return 0;
	}else{
		return 1;
	}
}

/************************************************************************************************************/

Function GetNextTransNo ($TransType, $db){
        if ($TransType==10 or $TransType==70 or $TransType==110  or $TransType==66  or $TransType==105 or $TransType==65 or $TransType==111 or $TransType==125 or $TransType==118  or $TransType==119 or $TransType==109 or $TransType==107){
          
            DB_query("LOCK TABLES systypesinvoice WRITE",$db);
            $SQL = "SELECT typeno FROM systypesinvoice WHERE typeid = " . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('El siguiente folio de transaccion no pudo ser obtenido de la base de datos porque');
            $DbgMsg =  _('El siguiente SQL se utilizo para obtener el numero de folio');
            $GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            $myrow = DB_fetch_row($GetTransNoResult);
            $SQL = 'UPDATE systypesinvoice SET typeno = ' . ($myrow[0] + 1) . ' WHERE typeid = ' . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': ' . _('El numero de folio no se pudo incrementar');
            $DbgMsg =  _('El siguiente SQL se utilizo para incrementar el numero de folio');
            $UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            DB_query("UNLOCK TABLES",$db);
          
            return $myrow[0] + 1;       
        }elseif ($TransType==12 or $TransType==500 or $TransType==124){
            
            DB_query("LOCK TABLES systypescusttrans WRITE",$db);
            $SQL = "SELECT typeno FROM systypescusttrans WHERE typeid = " . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('El siguiente folio de transaccion no pudo ser obtenido de la base de datos porque');
            $DbgMsg =  _('El siguiente SQL se utilizo para obtener el numero de folio');
            $GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            $myrow = DB_fetch_row($GetTransNoResult);
            $SQL = 'UPDATE systypescusttrans SET typeno = ' . ($myrow[0] + 1) . ' WHERE typeid = ' . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': ' . _('El numero de folio no se pudo incrementar');
            $DbgMsg =  _('El siguiente SQL se utilizo para incrementar el numero de folio');
            $UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            DB_query("UNLOCK TABLES",$db);
            return $myrow[0] + 1;
        }elseif (($TransType==30 or $TransType==120 or $TransType==16) and ($_SESSION['DatabaseName']=='servillantas' or $_SESSION['DatabaseName']=='gruposervillantas_DES' or  $_SESSION['DatabaseName']=='gruposervillantas' or  $_SESSION['DatabaseName']=='gruposervillantas_CAPA')){
	    DB_query("LOCK TABLES systypesother WRITE",$db);
            $SQL = "SELECT typeno FROM systypesother WHERE typeid = " . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('El siguiente folio de transaccion no pudo ser obtenido de la base de datos porque');
            $DbgMsg =  _('El siguiente SQL se utilizo para obtener el numero de folio');
            $GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            $myrow = DB_fetch_row($GetTransNoResult);
            $SQL = 'UPDATE systypesother SET typeno = ' . ($myrow[0] + 1) . ' WHERE typeid = ' . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': ' . _('El numero de folio no se pudo incrementar');
            $DbgMsg =  _('El siguiente SQL se utilizo para incrementar el numero de folio');
            $UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            DB_query("UNLOCK TABLES",$db);
            return $myrow[0] + 1;
		
	}else{
            
            DB_query("LOCK TABLES systypesinvtrans WRITE",$db);
            $SQL = "SELECT typeno FROM systypesinvtrans WHERE typeid = " . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('El siguiente folio de transaccion no pudo ser obtenido de la base de datos porque');
            $DbgMsg =  _('El siguiente SQL se utilizo para obtener el numero de folio');
            $GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            $myrow = DB_fetch_row($GetTransNoResult);
            $SQL = 'UPDATE systypesinvtrans SET typeno = ' . ($myrow[0] + 1) . ' WHERE typeid = ' . $TransType;
            $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': ' . _('El numero de folio no se pudo incrementar');
            $DbgMsg =  _('El siguiente SQL se utilizo para incrementar el numero de folio');
            $UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
            DB_query("UNLOCK TABLES",$db);
            //$type_no=$myrow[0];
            
            return $myrow[0] + 1;
            
        }
}

Function GetNextDocIndexNo ($TransType, $TagRef, &$db){

/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
	Also updates the transaction number
	
	10 sales invoice
	11 sales credit note
	12 sales receipt
	
	TagRef es la unidad de negocio
*/
	DB_query("LOCK TABLES sysDocumentIndex WRITE",$db);

	$SQL = "SELECT typeno FROM sysDocumentIndex WHERE typeid = " . $TransType . " and Tagref = " . $TagRef;

	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': <BR>' . _('El siguiente folio de transaccion no pudo ser obtenido de la base de datos porque');
	$DbgMsg =  _('El siguiente SQL se utilizo para obtener el numero de folio');
	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	$myrow = DB_fetch_row($GetTransNoResult);

	$SQL = 'UPDATE sysDocumentIndex SET typeno = ' . ($myrow[0] + 1) . ' WHERE typeid = ' . $TransType . ' and Tagref = ' . $TagRef;
	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR AYUDA DEL ADMINISTRADOR DEL SISTEMA') . ': ' . _('El numero de folio no se pudo incrementar');
	$DbgMsg =  _('El siguiente SQL se utilizo para incrementar el numero de folio');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	DB_query("UNLOCK TABLES",$db);

	return $myrow[0] + 1;
}


Function GetStockGLCode ($StockID, &$db){
// se agrego campo para movimientos de cuentas puente en embarque
/*Gets the GL Codes relevant to the stock item account from the stock category record */
	$QuerySQL = "SELECT stockact,
				adjglact,
				purchpricevaract,
				materialuseagevarac,
				wipact,
				adjglacttransf,
				internaluse,
				stockconsignmentact,
				glcodebydelivery,
				stockshipty,
				accounttransfer
			FROM stockmaster,
				stockcategory
			WHERE stockmaster.categoryid=stockcategory.categoryid
			AND stockmaster.stockid = '" . $StockID . "'";

	$ErrMsg =  _('The stock GL codes could not be retreived because');
	$GetStkGLResult = DB_query($QuerySQL, $db, $ErrMsg);

	$myrow = DB_fetch_array($GetStkGLResult);
	return $myrow;
}

Function GetTaxRate ($TaxAuthority, $DispatchTaxProvince, $TaxCategory, &$db){

/*Gets the Tax rate applicable to an item from the TaxAuthority of the branch and TaxLevel of the item */

	$QuerySQL = "SELECT taxrate
			FROM taxauthrates
			WHERE taxauthority=" . $TaxAuthority . "
			AND dispatchtaxprovince=" . $DispatchTaxProvince . "
			AND taxcatid = " . $TaxCategory;

	$ErrMsg = _('The tax rate for this item could not be retreived because');
	$GetTaxRateResult = DB_query($QuerySQL,$db,$ErrMsg);

	if (DB_num_rows($GetTaxRateResult)==1){
		$myrow = DB_fetch_row($GetTaxRateResult);
		return $myrow[0];
	} else {
		/*The tax rate is not defined for this Tax Authority and Dispatch Tax Authority */
		return 0;
	}

}

Function GetTaxes ($TaxGroup, $DispatchTaxProvince, $TaxCategory, &$db) {

	$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates INNER JOIN taxgrouptaxes ON
				taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				INNER JOIN taxauthorities ON
				taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid='" . $TaxGroup . "'
			AND taxauthrates.dispatchtaxprovince='" . $DispatchTaxProvince . "'
			AND taxauthrates.taxcatid = '" . $TaxCategory . "'
			ORDER BY taxgrouptaxes.calculationorder";


	$ErrMsg = _('The taxes and rate for this tax group could not be retreived because');
	$GetTaxesResult = DB_query($SQL,$db,$ErrMsg);

	if (DB_num_rows($GetTaxesResult)>=1){
		return $GetTaxesResult;
	} else {
		/*The tax group is not defined with rates */
		return 0;
	}
}



Function GetCreditAvailable($DebtorNo,&$db) {

	$sql = "SELECT debtorsmaster.debtorno,
			debtorsmaster.creditlimit,
			SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc) as balance
		FROM debtorsmaster INNER JOIN debtortrans
			ON debtorsmaster.debtorno=debtortrans.debtorno
		WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
				and debtortrans.ovamount >0
		GROUP BY debtorsmaster.debtorno,
			debtorsmaster.creditlimit";
	//echo '<pre>sql 1 ...:'.$sql;
	$ErrMsg =  _('The current account balance of the customer could not be retrieved because');
	$GetAccountBalanceResult = DB_query($sql, $db, $ErrMsg);

	if (DB_num_rows($GetAccountBalanceResult)==1){

		$myrow = DB_fetch_array($GetAccountBalanceResult);
		$CreditAvailable = $myrow['creditlimit'] - $myrow['balance'];
	} else {
		$sql = "SELECT creditlimit
				FROM debtorsmaster
				WHERE debtorno='" . $DebtorNo . "'";
		$GetAccountBalanceResult = DB_query($sql, $db, $ErrMsg);
		$myrow = DB_fetch_array($GetAccountBalanceResult);
		$CreditAvailable = $myrow['creditlimit'];
	}
	//echo '<br><pre>sql 1 ...:'.$CreditAvailable;
	/*Take into account the value of outstanding sales orders too */
	$sql = "SELECT SUM(salesorderdetails.unitprice *
				(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) *
				(1 - salesorderdetails.discountpercent)) AS ordervalue
				FROM salesorders,
					salesorderdetails
				WHERE salesorders.orderno = salesorderdetails.orderno
				AND salesorders.debtorno = '" . $DebtorNo . "'
				AND salesorderdetails.completed = 0
				AND salesorders.quotation = 0";

	$ErrMsg =  _('The value of outstanding orders for the customer could not be retrieved because');
	$GetOSOrdersResult = DB_query($sql, $db, $ErrMsg);

	$myrow = DB_fetch_array($GetOSOrdersResult);
	//echo '<pre>sql 2 ...:'. $myrow['ordervalue'];//
	/*Take into account the value of outstanding sales orders too */
	$CreditAvailable -= $myrow['ordervalue'];
	$sql = "SELECT  SUM(ABS(ABS(debtortrans.ovamount  + debtortrans.ovgst ) - ABS(debtortrans.alloc))) as saldopendiente
			FROM debtortrans
			WHERE debtortrans.ovamount < 0
			AND ABS(ABS(debtortrans.ovamount  + debtortrans.ovgst ) - ABS(debtortrans.alloc)) > 0.1
 			AND debtortrans.debtorno = '" . $DebtorNo . "'";
	$ErrMsg =  _('The value of outstanding orders for the customer could not be retrieved because');
	$GetOSOrdersResult = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_array($GetOSOrdersResult);
	$CreditAvailable = $CreditAvailable + $myrow['saldopendiente'];
	
	Return $CreditAvailable;
}

function ItemCostUpdateGL($db, $StockID, $NewCost, $OldCost, $QOH) {

		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $QOH!=0){

			$CostUpdateNo = GetNextTransNo(35, $db);
			$PeriodNo = GetPeriod(Date('d/m/Y'), $db);
			$StockGLCode = GetStockGLCode($StockID,$db);

			$ValueOfChange = $QOH * ($NewCost - $OldCost);
                        
                        $SQL="SELECT tagref,SUM(quantity) as cantidad,
                        				locations.loccode
                              FROM locstock inner join locations on locations.loccode=locstock.loccode
                              WHERE locstock.quantity>0
                                    AND locstock.stockid='" . $StockID . "'
                              GROUP BY tagref ";
			$ErrMsg =  _('No obtuvo resultados de la consulta..');      
                        $Result = DB_query($SQL, $db, $ErrMsg);
                        while ($myrowunit=DB_fetch_array($Result)){
                            $unidadesXTAG=$myrowunit['cantidad'];
                            $tagref=$myrowunit['tagref'];
			    			$location = $myrowunit['loccode'];
                            $montotag=($unidadesXTAG*$ValueOfChange)/$QOH;
				if ($NewCost > $OldCost){
					$montoinventario = $montotag;		//$StockGLCode['stockact']
					$montocosto = (-1)*$montotag;		//$StockGLCode['adjglact']
				}else{
					$montoinventario = (-1)*$montotag;	//$StockGLCode['stockact']
					$montocosto = $montotag;		//$StockGLCode['adjglact']
				}
				
                                $SQL = "INSERT INTO gltrans (type,
                                                                typeno,
                                                                trandate,
                                                                periodno,
                                                                account,
                                                                narrative,
                                                                amount,
								tag)
                                                        VALUES (35,
                                                                " . $CostUpdateNo . ",
                                                                '" . Date('Y-m-d') . "',
                                                                " . $PeriodNo . ",
                                                                " . $StockGLCode['adjglact'] . ",
                                                                '" . $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG . "',
                                                                " . $montocosto .", 
                                                                " . $tagref .")";
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de la cuenta de credito no se realizo por que');
                                $DbgMsg = _('EL SQL utilizado es');
                                
                                $tipodocto = 35;
                                $SQLInvoiceDate = Date('Y-m-d');
                                $rate = 1;
                                $Narrative = $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG;
                                $ISQL = Insert_Gltrans($tipodocto,$CostUpdateNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['adjglact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
                                		'','',$StockID,$unidadesXTAG,0,$location,$OldCost,"",0, $montocosto,$db,'','ACT COSTO',0);
                                
                                $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
                               
                                $SQL = "INSERT INTO gltrans (type,
                                                                typeno,
                                                                trandate,
                                                                periodno,
                                                                account,
                                                                narrative,
                                                                amount,
								tag)
                                                        VALUES (35,
                                                                " . $CostUpdateNo . ",
                                                                '" . Date('Y-m-d') . "',
                                                                " . $PeriodNo . ",
                                                                " . $StockGLCode['stockact'] . ",
                                                                '" . $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG . "',
                                                                " . $montoinventario .",
                                                                " . $tagref .")";
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de la cuenta de debito no se realizo por que');
                                $DbgMsg = _('EL SQL utilizado es');
                                
                                $tipodocto = 35;
                                $SQLInvoiceDate = Date('Y-m-d');
                                $rate = 1;
                                $Narrative =  $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG;
                                $ISQL = Insert_Gltrans($tipodocto,$CostUpdateNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
                                		'','',$StockID,$unidadesXTAG,0,$location,$OldCost,"",0, $montoinventario,$db,'','INVENTARIOS',0);
                                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                        }
		}
}



function ItemCostUpdateGLXLegal($db, $StockID, $NewCost, $OldCost, $QOH, $legalid) {

		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $QOH!=0){
			$ErrMsg = _('Los detalles del costo del Producto no se han podido obtener');
			$DbgMsg = _('El SQL que fallo fue:');
			$CostUpdateNo = GetNextTransNo(35, $db);
			$PeriodNo = GetPeriod(Date('d/m/Y'), $db);
			
			$StockGLCode = GetStockGLCode($StockID,$db);

			$ValueOfChange = ($NewCost - $OldCost);
                        
                        $SQL="SELECT tags.legalid,tags.tagref,SUM(quantity) as cantidad, locations.loccode
                              FROM locations inner join  locstock on locations.loccode=locstock.loccode
				inner join tags on tags.tagref=locations.tagref
                              WHERE locstock.quantity>0
                                    AND locstock.stockid='" . $StockID . "'
				    AND tags.legalid='" . $legalid . "'
                              GROUP BY tags.legalid,tags.tagref ";
			
                        $AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			while ($myrowunit = DB_fetch_array($AssResult,$db)){
                            $unidadesXTAG=$myrowunit['cantidad'];
                            $tagref=$myrowunit['tagref'];
                            $montotag=($unidadesXTAG*$ValueOfChange);
                            $location = $myrowunit['loccode'];
							if ($ValueOfChange>0){
								$montoinventario = $montotag;		//$StockGLCode['stockact']
								$montocosto = ($montotag*-1);		//$StockGLCode['adjglact']
							}else{
								$montoinventario = ($montotag);	//$StockGLCode['stockact']
								$montocosto = $montotag*-1;		//$StockGLCode['adjglact']
							}                
							$SQL = "INSERT INTO gltrans (type,
                                                                typeno,
                                                                trandate,
                                                                periodno,
                                                                account,
                                                                narrative,
                                                                amount,
								tag)
                                                        VALUES (35,
                                                                " . $CostUpdateNo . ",
                                                                '" . Date('Y-m-d') . "',
                                                                " . $PeriodNo . ",
                                                                " . $StockGLCode['adjglact'] . ",
                                                                '" . $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG . "',
                                                                " . $montocosto .", 
                                                                " . $tagref .")";
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de la cuenta de credito no se realizo por que');
                                $DbgMsg = _('EL SQL utilizado es');
                                
                                $tipodocto = 35;
                                $SQLInvoiceDate = Date('Y-m-d');
                                $rate = 1;
                                $Narrative = $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG;
                                $ISQL = Insert_Gltrans($tipodocto,$CostUpdateNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['adjglact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
                                		'','',$StockID,$unidadesXTAG,0,$location,0,$OldCost,0, $montocosto,$db,'','ACT COSTO',0);
                                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                
                               // echo '<pre><br>'.$SQL;
				
                                $SQL = "INSERT INTO gltrans (type,
                                                                typeno,
                                                                trandate,
                                                                periodno,
                                                                account,
                                                                narrative,
                                                                amount,
								tag)
                                                        VALUES (35,
                                                                " . $CostUpdateNo . ",
                                                                '" . Date('Y-m-d') . "',
                                                                " . $PeriodNo . ",
                                                                " . $StockGLCode['stockact'] . ",
                                                                '" . $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG . "',
                                                                " . $montoinventario .",
                                                                " . $tagref .")";
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de la cuenta de debito no se realizo por que');
                                $DbgMsg = _('EL SQL utilizado es');
                                
                                $tipodocto = 35;
                                $SQLInvoiceDate = Date('Y-m-d');
                                $rate = 1;
                                $Narrative = $StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG;
                                $ISQL = Insert_Gltrans($tipodocto,$CostUpdateNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
                                		'','',$StockID,$unidadesXTAG,0,$location,0,$NewCost,0, $montoinventario,$db,'','INVENTARIOS',0);
                                
                                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                
                                $isql = "insert into stockmoves(stkmoveno,stockid,type,transno,loccode,trandate,debtorno,
										branchcode,price,prd,reference,qty,discountpercent,standardcost,show_on_inv_crds,newqoh,
										hidemovt,narrative,warranty,tagref,discountpercent1,discountpercent2,totaldescuento,avgcost,standardcostv2)
										SELECT NULL,'" . $StockID . "','35','" . $CostUpdateNo . "','All',Now(),'','','". $NewCost . "','"
				                               . $PeriodNo . "','AJUSTE DE COSTO MANUAL','0','0','" . $montoinventario . "','1','0','0','AJUSTE COSTO MANUAL','0',t.tagref,'0','0','0','0','0'
										FROM tags t, locations l
										WHERE t.legalid = " . $legalid . " and t.tagref  = l.tagref limit 1";
                                $ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
                                $DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
                                $iResult = DB_query($isql,$db,$ErrMsg,$DbgMsg,true);
				
                        }
		}
}





function ItemCostUpdateGLXTag($db, $StockID, $NewCost, $OldCost, $QOH, $tagref,$serial) {

		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $QOH!=0){
			$ErrMsg = _('Los detalles del costo del Producto no se han podido obtener');
			$DbgMsg = _('El SQL que fallo fue:');

			$CostUpdateNo = GetNextTransNo(35, $db);
			$PeriodNo = GetPeriod(Date('d/m/Y'), $db);
			$StockGLCode = GetStockGLCode($StockID,$db);

			$ValueOfChange = $QOH * ($NewCost - $OldCost);
                        
                        $SQL="SELECT SUM(quantity) as cantidad,tagref
                              FROM locstock inner join locations on locations.loccode=locstock.loccode
                              WHERE locstock.quantity>0
                                    AND locstock.stockid='" . $StockID . "'
				    AND locations.tagref='" . $tagref . "'
				group by tagref";
                        $AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			while ($myrowunit = DB_fetch_array($AssResult,$db)){
                            $unidadesXTAG=$myrowunit['cantidad'];
                            $tag=$myrowunit['tagref'];
                            $montotag=($unidadesXTAG*$ValueOfChange)/$QOH;
			    $narrative=$StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG ; 
			    if ($serial!=''){
				$montotag = $NewCost - $OldCost;	//$StockGLCode['stockact']
				$narrative=$StockID . ' ' . _('el costo era de') . ' ' . $OldCost . ' ' . _('cambio a') . ' ' . $NewCost . ' x ' . _('cantidad en demanda') . ' ' . $unidadesXTAG. ' @ Numero de serie:'.$serial ;
			     }
				if ($NewCost > $OldCost){
					$montoinventario = $montotag;		//$StockGLCode['stockact']
					$montocosto = (-1)*$montotag;		//$StockGLCode['adjglact']
				}else{
					$montoinventario = (-1)*$montotag;	//$StockGLCode['stockact']
					$montocosto = $montotag;		//$StockGLCode['adjglact']
				}
				
                                $SQL = "INSERT INTO gltrans (type,
                                                                typeno,
                                                                trandate,
                                                                periodno,
                                                                account,
                                                                narrative,
                                                                amount,
								tag)
                                                        VALUES (35,
                                                                " . $CostUpdateNo . ",
                                                                '" . Date('Y-m-d') . "',
                                                                " . $PeriodNo . ",
                                                                " . $StockGLCode['adjglact'] . ",
                                                                '" . $narrative ."',
                                                                " . $montocosto .", 
                                                                " . $tag .")";
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de la cuenta de credito no se realizo por que');
                                $DbgMsg = _('EL SQL utilizado es');
                                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                $SQL = "INSERT INTO gltrans (type,
                                                                typeno,
                                                                trandate,
                                                                periodno,
                                                                account,
                                                                narrative,
                                                                amount,
								tag)
                                                        VALUES (35,
                                                                " . $CostUpdateNo . ",
                                                                '" . Date('Y-m-d') . "',
                                                                " . $PeriodNo . ",
                                                                " . $StockGLCode['stockact'] . ",
                                                                '" . $narrative . "',
                                                                " . $montoinventario .",
                                                                " . $tag .")";
                                $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La transaccion de la cuenta de debito no se realizo por que');
                                $DbgMsg = _('EL SQL utilizado es');
                                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				//echo 'sql:'.$SQL;
                        }
		}
}


 


/* Calculates the material cost of a bill of materials, given parent code*/
function BomMaterialCost($Parent, $LocCode, $db) {
	$SQL = "SELECT avgcost from stockmaster
		LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
		LEFT JOIN tags ON stockcostsxlegal.legalid = tags.legalid
		LEFT JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
		where stockid='".$Parent."'";
	$result1 = DB_query($SQL,$db);
	$MyRow1 = DB_fetch_row($result1);
	$OldCost = $MyRow1[0];
	$SQL = "SELECT sum(quantity) as qoh
			from locstock where stockid='".$Parent."' AND loccode = '" . $LocCode . "'";
			
	$result1 = DB_query($SQL,$db);
	$MyRow1 = DB_fetch_row($result1);
	$QOH = $MyRow1[0];
	$SQL = "SELECT Sum(avgcost*bom.quantity) AS SumOfmaterialcost
		FROM bom LEFT JOIN stockmaster ON bom.component = stockmaster.stockid
				LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				LEFT JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				LEFT JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
			 WHERE bom.parent='". $Parent . "'";
	$result = DB_query($SQL,$db);
	$MyRow = DB_fetch_row($result);
	$MaterialCost = $MyRow[0];
	
	$Result = DB_Txn_Begin($db);
	ItemCostUpdateGL($db, $Parent, $MaterialCost, $OldCost, $QOH);
	$Result = DB_Txn_Commit($db);
	return $MaterialCost;
}

/* Accepts work order information and iterates through the bom, inserting real components (dissolving phantom assemblies) */
function WoRealRequirements($db, $WO, $LocCode, $StockID, $Qty=1, $ParentID='') {

	//echo "WoRealRequirements(".$WO.",".$LocCode.",".$StockID.",".$Qty.",".$ParentID.")<br>";
	// remember, 'G' is for ghost (phantom part type)

	// all components should be referenced to the initial parent
	if ($ParentID == '') {
		$ParentID = $StockID;
	}
	
	if ($ParentID != $StockID) { //CAST('NODE-1' AS CHAR) as parentstockid
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							bom.quantity*".$Qty." AS qtypu,
							avgcost AS stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							".$Qty." as qtypu,
							bom.quantity*costototal.costointegrado stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
							,(SELECT sum(qtypu*stdcost) as costointegrado
								FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
								WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND stockmaster.units <> '(%)') as costototal
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.stdcost=g.stdcost/(worequirements.qtypu+g.qtypu),
						worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
	}
	
	// insert new real immediate components of this item
	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				bom.quantity*".$Qty.",
				avgcost,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);
	//echo $sql."<br>";
	
	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				".$Qty.",
				bom.quantity*costototal.costointegrado,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
				,(SELECT sum(qtypu*stdcost) as costointegrado
					FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
					WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND LEFT(stockmaster.units,3) <> '(%)') as costototal
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);
	
	//echo $sql."<br>";
	// combine real immediate components of this item with other occurrences in this work order
	// otherwise, we could encounter a uniqueness violation:
	//     - the same component could occur in multiple dissolved phantom assemblies
	//     - need to sum quantities of multiple component occurrences
	

	// dissolve phantom assemblies
	$sql = "SELECT
				bom.component,
				bom.quantity
			FROM bom INNER JOIN stockmaster
			ON bom.component=stockmaster.stockid
			WHERE parent='" . $StockID . "'
			/*AND loccode ='" . $LocCode . "'*/
			AND (stockmaster.mbflag='G')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID);
	}
	
	// dissolve phantom assemblies
	$sql = "SELECT
			bom.component,
			bom.quantity
		FROM bom INNER JOIN stockmaster
		ON bom.component=stockmaster.stockid
		WHERE parent='" . $StockID . "'
		/*AND loccode ='" . $LocCode . "'*/
		AND (stockmaster.mbflag='M')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID); //$StockID
	}

}

function WoRealRequirements_V6_0($db, $WO, $LocCode, $StockID, $Qty=1, $ParentID='') {

	//echo "WoRealRequirements(".$WO.",".$LocCode.",".$StockID.",".$Qty.",".$ParentID.")<br>";
	// remember, 'G' is for ghost (phantom part type)

	// all components should be referenced to the initial parent
	if ($ParentID == '') {
		$ParentID = $StockID;
	}

	if ($ParentID != $StockID) { //CAST('NODE-1' AS CHAR) as parentstockid
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							bom.quantity*".$Qty." AS qtypu,
							avgcost AS stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							".$Qty." as qtypu,
							bom.quantity*costototal.costointegrado stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
							,(SELECT sum(qtypu*stdcost) as costointegrado
								FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
								WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND stockmaster.units <> '(%)') as costototal
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.stdcost=g.stdcost/(worequirements.qtypu+g.qtypu),
						worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
	}

	// insert new real immediate components of this item
	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				bom.quantity*".$Qty.",
				avgcost,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);
	//echo $sql."<br>";

	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				".$Qty.",
				bom.quantity*costototal.costointegrado,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
				,(SELECT sum(qtypu*stdcost) as costointegrado
					FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
					WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND LEFT(stockmaster.units,3) <> '(%)') as costototal
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);

	//echo $sql."<br>";
	// combine real immediate components of this item with other occurrences in this work order
	// otherwise, we could encounter a uniqueness violation:
	//     - the same component could occur in multiple dissolved phantom assemblies
	//     - need to sum quantities of multiple component occurrences


	// dissolve phantom assemblies
	$sql = "SELECT
				bom.component,
				bom.quantity
			FROM bom INNER JOIN stockmaster
			ON bom.component=stockmaster.stockid
			WHERE parent='" . $StockID . "'
			/*AND loccode ='" . $LocCode . "'*/
			AND (stockmaster.mbflag='G')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements_V6_0($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID);
	}

	// dissolve phantom assemblies
	$sql = "SELECT
			bom.component,
			bom.quantity
		FROM bom INNER JOIN stockmaster
		ON bom.component=stockmaster.stockid
		WHERE parent='" . $StockID . "'
		/*AND loccode ='" . $LocCode . "'*/
		AND (stockmaster.mbflag='M')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements_V6_0($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID); //$StockID
	}

}

//**********************************************************************************************************
// FUNCION DE COSTO EN ORDENES DE TRABAJO EN BASE A FUNCION DE BOM CUANDO SON MANUALES
function WoBOMRequirements($db, $WO, $LocCode, $StockID, $Qty=1, $ParentID='') {

	//echo "WoRealRequirements(".$WO.",".$LocCode.",".$StockID.",".$Qty.",".$ParentID.")<br>";
	// remember, 'G' is for ghost (phantom part type)

	// all components should be referenced to the initial parent
	if ($ParentID == '') {
		$ParentID = $StockID;
	}

	if ($ParentID != $StockID) { //CAST('NODE-1' AS CHAR) as parentstockid
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							bom.quantity*".$Qty." AS qtypu,
							costoUnitario as  stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							".$Qty." as qtypu,
							bom.quantity*costototal.costointegrado stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
							,(SELECT sum(qtypu*stdcost) as costointegrado
								FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
								WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND stockmaster.units <> '(%)') as costototal
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.stdcost=g.stdcost/(worequirements.qtypu+g.qtypu),
						worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
	}

	// insert new real immediate components of this item
	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				bom.quantity*".$Qty.",
				costoUnitario,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);
	//echo $sql."<br>";

	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				".$Qty.",
				bom.quantity*costototal.costointegrado,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
				,(SELECT sum(qtypu*stdcost) as costointegrado
					FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
					WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND stockmaster.units <> '(%)') as costototal
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);
	//echo '<br><br>'.$sql."<br>";
	
	// combine real immediate components of this item with other occurrences in this work order
	// otherwise, we could encounter a uniqueness violation:
	//     - the same component could occur in multiple dissolved phantom assemblies
	//     - need to sum quantities of multiple component occurrences


	// dissolve phantom assemblies
	$sql = "SELECT
				bom.component,
				bom.quantity
			FROM bom INNER JOIN stockmaster
			ON bom.component=stockmaster.stockid
			WHERE parent='" . $StockID . "'
			/*AND loccode ='" . $LocCode . "'*/
			AND (stockmaster.mbflag='G')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID);
	}

	// dissolve phantom assemblies
	$sql = "SELECT
			bom.component,
			bom.quantity
		FROM bom INNER JOIN stockmaster
		ON bom.component=stockmaster.stockid
		WHERE parent='" . $StockID . "'
		/*AND loccode ='" . $LocCode . "'*/
		AND (stockmaster.mbflag='M')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID); //$StockID
	}

}

function WoBOMRequirements_V6_0($db, $WO, $LocCode, $StockID, $Qty=1, $ParentID='') {

	//echo "WoRealRequirements(".$WO.",".$LocCode.",".$StockID.",".$Qty.",".$ParentID.")<br>";
	// remember, 'G' is for ghost (phantom part type)

	// all components should be referenced to the initial parent
	if ($ParentID == '') {
		$ParentID = $StockID;
	}

	if ($ParentID != $StockID) { //CAST('NODE-1' AS CHAR) as parentstockid
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							bom.quantity*".$Qty." AS qtypu,
							costoUnitario as  stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
		$sql = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							'".$ParentID."' as parentstockid,
							bom.component AS stockid,
							".$Qty." as qtypu,
							bom.quantity*costototal.costointegrado stdcost,
							bom.autoissue
						FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
							INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
							INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '" . $LocCode . "'
							,(SELECT sum(qtypu*stdcost) as costointegrado
								FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
								WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND stockmaster.units <> '(%)') as costototal
						WHERE bom.parent='" . $StockID . "'
						/*AND bom.loccode ='" . $LocCode . "'*/
						AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
						AND bom.component IN (
							SELECT stockid
							FROM worequirements
							WHERE wo = '" . $WO . "'
							AND parentstockid = '". $ParentID . "'
						)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.stdcost=g.stdcost/(worequirements.qtypu+g.qtypu),
						worequirements.qtypu=worequirements.qtypu+g.qtypu";
		$result = DB_query($sql, $db);
		//echo $sql."<br>";
	}

	// insert new real immediate components of this item
	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				bom.quantity*".$Qty.",
				costoUnitario,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) <> '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);
	//echo $sql."<br>";

	$sql = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue,
				directparentid)
			SELECT '".$WO."',
				'".$ParentID."',
				bom.component,
				".$Qty.",
				bom.quantity*costototal.costointegrado,
				bom.autoissue,
				'".$StockID."'
			FROM bom INNER JOIN stockmaster ON bom.component=stockmaster.stockid
				INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
				INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
				INNER JOIN locations ON locations.tagref = tags.tagref AND locations.loccode = '".$LocCode."'
				,(SELECT sum(qtypu*stdcost) as costointegrado
					FROM worequirements JOIN stockmaster ON worequirements.stockid = stockmaster.stockid
					WHERE wo = '".$WO."' AND directparentid = '".$StockID."' AND stockmaster.units <> '(%)') as costototal
			WHERE bom.parent='".$StockID."'
			/*AND bom.loccode ='".$LocCode."'*/
			AND stockmaster.mbflag<>'G' AND LEFT(stockmaster.units,3) = '(%)'
			AND bom.component NOT IN (
				SELECT stockid
				FROM worequirements
				WHERE wo = '".$WO."'
				AND parentstockid = '".$ParentID."'
			)";
	$result = DB_query($sql, $db);

	//echo $sql."<br>";
	// combine real immediate components of this item with other occurrences in this work order
	// otherwise, we could encounter a uniqueness violation:
	//     - the same component could occur in multiple dissolved phantom assemblies
	//     - need to sum quantities of multiple component occurrences


	// dissolve phantom assemblies
	$sql = "SELECT
				bom.component,
				bom.quantity
			FROM bom INNER JOIN stockmaster
			ON bom.component=stockmaster.stockid
			WHERE parent='" . $StockID . "'
			/*AND loccode ='" . $LocCode . "'*/
			AND (stockmaster.mbflag='G')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID);
	}

	// dissolve phantom assemblies
	$sql = "SELECT
			bom.component,
			bom.quantity
		FROM bom INNER JOIN stockmaster
		ON bom.component=stockmaster.stockid
		WHERE parent='" . $StockID . "'
		/*AND loccode ='" . $LocCode . "'*/
		AND (stockmaster.mbflag='M')";
	$result = DB_query($sql, $db);
	while ($MyRow=DB_fetch_array($result)) {
		WoRealRequirements_V6_0($db, $WO, $LocCode, $MyRow['component'], $MyRow['quantity']*$Qty, $ParentID); //$StockID
	}

}



// ************************************************************************************************************
// Costo preomedio por LEGALID
// ************************************************************************************************************



function StockAvgUnitsXLegal($stock,$tag, $db) {
	$legalid=ExtractLegalid($tag,$db);
	
	/* ESTE QUERY NO TOMABA EN CUENTA EXISTENCIAS PENDIENTES POR FACTURAR QUE NO ESTABAN AFECTANDO TODAVIA AL COSTO PROMEDIO
	
	$SQL = "select sum(quantity) from locstock
		WHERE locstock.stockid = '" . $stock . "'
		AND loccode in (
			select loccode
			from locations , tags
			where tags.tagref=locations.tagref and tags.legalid='" . $legalid. "')";
	*/
	
	// $SQL ="select sum(quantity)-pend.pendientes as exist
	// from locstock, (select sum(grns.qtyrecd - grns.quantityinv) as pendientes 
	// from grns JOIN purchorderdetails ON purchorderdetails.podetailitem = grns.podetailitem
	// JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
	// JOIN tags ON purchorders.tagref = tags.tagref
	// where grns.itemcode = '" . $stock . "' and tags.legalid = '" . $legalid. "') as pend
	// WHERE locstock.stockid = '" . $stock . "'
	// AND loccode in (select loccode from locations , tags where tags.tagref=locations.tagref and tags.legalid='" . $legalid. "')";

	$SQL ="select sum(quantity) - CASE WHEN pend.pendientes <> '' THEN pend.pendientes ELSE 0 END as exist, locstock.stockid
	from locstock, (select sum(grns.qtyrecd - grns.quantityinv) as pendientes 
	from grns JOIN purchorderdetails ON purchorderdetails.podetailitem = grns.podetailitem
	JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
	JOIN tags ON purchorders.tagref = tags.tagref
	where grns.itemcode = '".$stock."' and tags.legalid = '".$legalid."') as pend
	WHERE locstock.stockid = '".$stock."'
	AND loccode in (select loccode from locations , tags where tags.tagref=locations.tagref and tags.legalid='".$legalid."')
	group by locstock.stockid, pend.pendientes ";
	
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$unit = $MyRow[0];
	if (is_null($unit) == true){
		$unit=0;
	}
	
	return abs($unit);
}

/*unidades x razon socila sin considerar unidades pendientes*/


function StockUnitsXLegal($stock,$tag, $db) {
	$legalid=ExtractLegalid($tag,$db);
	
	$SQL = "select sum(quantity-qtybysend) from locstock
		WHERE locstock.stockid = '" . $stock . "'
		AND loccode in (
			select loccode
			from locations , tags
			where tags.tagref=locations.tagref and tags.legalid='" . $legalid. "')";
	
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$unit = $MyRow[0];
	if (is_null($unit) == true){
		$unit=0;
	}
	
	return $unit;
}

function StockAvgcostXLegal($stock,$tag, $db) {
	$legalid=ExtractLegalid($tag,$db);
	
	$SQL = "SELECT avgcost
		FROM stockcostsxlegal 
		WHERE stockid = '" . $stock . "'
		     AND legalid='" . $legalid. "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	
	$cost = $MyRow[0];
	if (is_null($cost) == true){
		$cost=0;
	}
	
	return $cost;
}



function StockLastCostXLegal($stock,$tag, $db) {
	$legalid=ExtractLegalid($tag,$db);
	$SQL = "SELECT lastcost
		FROM stockcostsxlegal
		WHERE legalid = '" . $legalid . "'
			And stockid = '" . $stock . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$lastcost= $MyRow[0];
	if (is_null($lastcost) == true){
		$lastcost=0;
	}
	return $lastcost;
}

/*************************************************************************************/
/*********************elimina prod comprados de la orden de compra********************/
function StockUnitsXPurch($stockid,$ordencompra,$qtycompra, $db) {
	
	$SQL = "SELECT (".$qtycompra."-salesorderdetails.quantity) as cantcompraf
		FROM purchorders
			INNER JOIN   purchorderdetails ON purchorders.orderno=purchorderdetails.orderno 
			INNER JOIN   salesorderdetails ON salesorderdetails.orderno=purchorders.requisitionno and salesorderdetails.stkcode=purchorderdetails.itemcode
		AND purchorders.orderno= '" . $ordencompra . "'
			AND purchorderdetails.itemcode = '" . $stockid . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$qtycompraf= $MyRow[0];
	if (is_null($qtycompraf) == true){
		$SQL = "SELECT (".$qtycompra."-purchorderdetails.quantityrecd) as cantcompraf
		FROM purchorders
			INNER JOIN   purchorderdetails ON purchorders.orderno=purchorderdetails.orderno 
			INNER JOIN   salesorderdetails ON salesorderdetails.orderno=purchorders.requisitionno 
		AND purchorders.orderno= '" . $ordencompra . "'
			AND purchorderdetails.itemcode = '" . $stockid . "'";
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
		$DbgMsg =  _('El SQL utiliado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		$MyRow = DB_fetch_row($Result);
		$qtycompraf= $MyRow[0];
		if (is_null($qtycompraf) == true){
			if (is_null($qtycompraf) == true){
				$qtycompraf=0;
			}
			$qtycompraf= $qtycompra;
			$qtycompraf=$qtycompraf.'|'.'0';
		}else{
			$qtycompraf=0;
			$qtycompraf=$qtycompraf.'|'.'1';
		}
		
	}else{
		$qtycompraf=$qtycompraf.'|'.'1';
		
	}
	return $qtycompraf;
}

function StockUnitsXPurchSalesF($stockid,$ordencompra,$qtycompra, $db) {
	
	$SQL = "SELECT salesorderdetails.qtyinvoiced as cantcompraf
		FROM purchorders
			INNER JOIN   purchorderdetails ON purchorders.orderno=purchorderdetails.orderno 
			INNER JOIN   salesorderdetails ON salesorderdetails.orderno=purchorders.requisitionno and salesorderdetails.stkcode=purchorderdetails.itemcode
		AND purchorders.orderno= '" . $ordencompra . "'
			AND purchorderdetails.itemcode = '" . $stockid . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$qtycompraf= $MyRow[0];
	if (is_null($qtycompraf) == true){
		//$qtycompraf= 0.'|'.0;
		$qtycompraf=$qtycompraf.'|'.'0';
	}else{
		$qtycompraf=$qtycompraf.'|'.'1';
		
	}
	return $qtycompraf;
}

function StockCostXPurchXSales($stockid,$ordencompra,$qtycompra, $db) {
	
	$SQL = "SELECT stockmoves.standardcost 
		FROM purchorders
			INNER JOIN   purchorderdetails ON purchorders.orderno=purchorderdetails.orderno 
			INNER JOIN   salesorderdetails ON salesorderdetails.orderno=purchorders.requisitionno		
			INNER JOIN   stockmoves ON stockmoves.reference=salesorderdetails.orderno and salesorderdetails.stkcode=stockmoves.stockid
			AND purchorderdetails.itemcode=stockmoves.stockid AND type in (10,110,119)
		WHERE purchorders.orderno= '" . $ordencompra . "'
			AND purchorderdetails.itemcode = '" . $stockid . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$costventa= $MyRow[0];
	if (is_null($costventa) == true){
		$costventa=0;
	}
	return $costventa;
}

function StockCostXPurch($stockid,$ordencompra,$qtycompra,$costprov, $db) {
	
	$SQL = "SELECT (".($costprov*$qtycompra).")-
		(purchorderdetails.stdcostunit*".$qtycompra.")
		FROM purchorders
			INNER JOIN   purchorderdetails ON purchorders.orderno=purchorderdetails.orderno 
			INNER JOIN   salesorderdetails ON salesorderdetails.orderno=purchorders.requisitionno		
			INNER JOIN   stockmoves ON stockmoves.reference=salesorderdetails.orderno and salesorderdetails.stkcode=stockmoves.stockid
			AND purchorderdetails.itemcode=stockmoves.stockid AND type in (10,110,119)
		WHERE purchorders.orderno= '" . $ordencompra . "'
			AND purchorderdetails.itemcode = '" . $stockid . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$costofacturacommpra= $MyRow[0];
	if (is_null($costofacturacommpra) == true){
		$costofacturacommpra=0;
	}
	return $costofacturacommpra;
}
/*************************************************************************************/

function EstimatedAvgCostXLegal($stock,$tag,$existingstocks,$avgcost,$purchstocks,$costpurch,$lastcost,$tipo=0, $db) {
	
	//echo $stock.' | '.$tag.' | '.$existingstocks.' | '.$avgcost.' | '.$purchstocks.' | '.$costpurch.' | '.$lastcost;
	
	if (($existingstocks+$purchstocks)==0){
		if ($avgcost>0){
			$avgcostnew=$avgcost;//(($existingstocks*$avgcost)+($purchstocks*$costpurch))/($existingstocks+$purchstocks);
		}else{
			$avgcostnew=$costpurch;//(($existingstocks*$avgcost)+($purchstocks*$costpurch))/($existingstocks+$purchstocks);
			$lastcost = $costpurch;
		}
	}else{
		/*
		 * FCC: AGREGUE ESTA CONDICION PARA CUANDO EL COSTO EXISTENTE ESTA EN CERO, NO LO TOME EN CUENTA Y EL COSTO PROMEDIO
		 SEA EL COSTO DEL ULTIMO MOVIMIENTO.
		*/
		if (($existingstocks*$avgcost) == 0){
			$avgcostnew = $costpurch;
			$lastcost = $costpurch;
		} else {
			if(($purchstocks*$costpurch)<=0){
				if ($avgcost<=0) {
					$avgcost=$costpurch;
				}
				$avgcostnew=$avgcost;
				$lastcost = $costpurch;
			}else{
				$avgcostnew=(($existingstocks*$avgcost)+($purchstocks*$costpurch))/($existingstocks+$purchstocks);
				$lastcost = $costpurch;
			}
		}
		
	}
	
	$legalid=ExtractLegalid($tag,$db);
	
	$fechapurch=Date('Y/m/d',CalcEarliestDispatchDate());
	// Consulto si existe el costo por producto y unidad de negocio
	$SQL=" select *
	       from stockcostsxlegal
	       where stockid='".$stock."'
	       and legalid='".$legalid."'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvo la actualizacion de costo promedio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);       
	if (DB_num_rows($Result)!=0)
	{
		// si existe la actualizo
		$myrowcost = DB_fetch_array($Result);
		// si el tipo de movimiento no es compra extraigo la ultima fecha de compra
		if ($tipo==0){
			$fechapurch=$myrowcost['trandate'];
		}
		$highercost=$myrowcost['highercost'];
		
		$SQL="UPDATE stockcostsxlegal
		      SET avgcost= '".$avgcostnew."',
			  lastcost='".$lastcost."',
			  lastupdatedate=DATE_FORMAT(now(),'%Y-%m-%d'),
			  lastpurchaseqty='".$purchstocks."',
			  lastpurchase='".$fechapurch."'
		      where stockid='".$stock."'
		      and legalid='".$legalid."'";
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
		$DbgMsg =  _('El SQL utiliado es');
		//echo $SQL;
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);  
		//Actualiza el campo de costo mas alto de la tabla de costo promedio
		if($lastcost>$highercost){
			$SQL="UPDATE stockcostsxlegal
		      	  SET highercost= '".$lastcost."'
			 		WHERE stockid='".$stock."'
		      			AND legalid='".$legalid."'";
			$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
			$DbgMsg =  _('El SQL utiliado es');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);  
		}          
	}else{
		// en caso de no existir este producto en la tabla lo inserto
		$SQL = "INSERT INTO stockcostsxlegal(
			legalid,
			stockid,
			lastcost,
			avgcost,
			trandate,
			lastpurchase,
			lastpurchaseqty,
				highercost,
			lastupdatedate
			) values(
				'" . $legalid . "',
				'" . $stock . "',
				'" . $lastcost . "',
				'" . $avgcostnew . "',
				DATE_FORMAT(now(),'%Y-%m-%d'),
				'" . $fechapurch . "',
				'" . $purchstocks . "',
				'" . $lastcost . "',
				DATE_FORMAT(now(),'%Y-%m-%d')
			)";	
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
		$DbgMsg =  _('El SQL utiliado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	}
	// inserto en historico de costos por legal
	$SQL = "INSERT INTO hs_stockcostsxlegal(
		legalid,
		stockid,
		lastcost,
		avgcost,
		trandate,
		lastpurchase,
		lastpurchaseqty,
		lastupdatedate
	) values(
		'" . $legalid . "',
		'" . $stock . "',
		'" . $lastcost . "',
		'" . $avgcostnew . "',
		DATE_FORMAT(now(),'%Y-%m-%d'),
		'" . $fechapurch . "',
		'" . $purchstocks . "',
		DATE_FORMAT(now(),'%Y-%m-%d')
	)";	
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	
	return $avgcostnew;
}


/*************************************************************************************/
// Costo preomedio por Razon Social en embarque
/*************************************************************************************/


function EstimatedAvgCostXLegalByEmbarque($stock,$tag,$existingstocks,$avgcost,$qtyship,$variacioncosto,$lastcost,$tipo=0, $db) {
	$lastcost=$lastcost+$variacioncosto;
	// cuando es tipo 31 es por que la existencia actual es menor a la del cierre de embarque
	/*echo 'variables de calculo:<br>';
	echo '<br>Existencia:'.$existingstocks;
	echo '<br>Costo promedio:'.$avgcost;
	echo '<br>Variacion:'.$variacioncosto;
	echo '<br>Unidades Embarque:'.$qtyship;*/
	
			
	if($tipo==31){
		$avgcostbyexisting=($existingstocks)*$avgcost;
		$variacioncosto=($variacioncosto*$qtyship);
		$avgcostbyship=($avgcostbyexisting+$variacioncosto);
		$avgcostnew=$avgcostbyship/($existingstocks);
		
	}else{
		$avgcostbyexisting=($existingstocks+$qtyship)*$avgcost;
		//$variacioncosto=($variacioncosto*($existingstocks+$qtyship));
		$variacioncosto=($variacioncosto*$qtyship);
		$avgcostbyship=($avgcostbyexisting+$variacioncosto);
		$avgcostnew=$avgcostbyship/($existingstocks+$qtyship);
	}
	
	$legalid=ExtractLegalid($tag,$db);

	$fechapurch=Date('Y/m/d',CalcEarliestDispatchDate());
	// Consulto si existe el costo por producto y unidad de negocio
	$SQL=" select *
	       from stockcostsxlegal
	       where stockid='".$stock."'
	       and legalid='".$legalid."'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvo la actualizacion de costo promedio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	if (DB_num_rows($Result)!=0)
	{
		// si existe la actualizo
		$myrowcost = DB_fetch_array($Result);
		// si el tipo de movimiento no es compra extraigo la ultima fecha de compra
		if ($tipo==0){
			$fechapurch=$myrowcost['trandate'];
		}

		$SQL="UPDATE stockcostsxlegal
		      SET avgcost= '".$avgcostnew."',
			  lastcost='".$lastcost."',
			  lastupdatedate=DATE_FORMAT(now(),'%Y-%m-%d'),
			  lastpurchaseqty='".$purchstocks."',
			  lastpurchase='".$fechapurch."'
		      where stockid='".$stock."'
		      and legalid='".$legalid."'";
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
		$DbgMsg =  _('El SQL utiliado es');
		//echo $SQL;
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	}else{
		// en caso de no existir este producto en la tabla lo inserto
		$SQL = "INSERT INTO stockcostsxlegal(
			legalid,
			stockid,
			lastcost,
			avgcost,
			trandate,
			lastpurchase,
			lastpurchaseqty,
			lastupdatedate
			) values(
				'" . $legalid . "',
				'" . $stock . "',
				'" . $lastcost . "',
				'" . $avgcostnew . "',
				DATE_FORMAT(now(),'%Y-%m-%d'),
				'" . $fechapurch . "',
				'" . $purchstocks . "',
				DATE_FORMAT(now(),'%Y-%m-%d')
			)";
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
		$DbgMsg =  _('El SQL utiliado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	}
	// inserto en historico de costos por legal
	$SQL = "INSERT INTO hs_stockcostsxlegal(
		legalid,
		stockid,
		lastcost,
		avgcost,
		trandate,
		lastpurchase,
		lastpurchaseqty,
		lastupdatedate
	) values(
		'" . $legalid . "',
		'" . $stock . "',
		'" . $lastcost . "',
		'" . $avgcostnew . "',
		DATE_FORMAT(now(),'%Y-%m-%d'),
		'" . $fechapurch . "',
		'" . $purchstocks . "',
		DATE_FORMAT(now(),'%Y-%m-%d')
	)";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

	return $avgcostnew;
}



// ************************************************************************************************************
// Costo preomedio por TAGREF
// ************************************************************************************************************
function StockAvgUnits($stock,$tag, $db) {
	
	$SQL = "select sum(quantity) from locstock
		WHERE locstock.stockid = '" . $stock . "'
			AND loccode in ( select loccode from locations where tagref = '" . $tag. "')";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$unit = $MyRow[0];
	
	if (is_null($unit) == true){
		$unit=0;
	}
	return $unit;
}

function StockUnitsXTag($stock,$tag, $db) {
	
	$SQL = "select sum(quantity) from locstock
		WHERE locstock.stockid = '" . $stock . "'
			AND loccode in ( select loccode from locations where tagref = '" . $tag. "')";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$unit = $MyRow[0];
	
	if (is_null($unit) == true){
		$unit=0;
	}
	return $unit;
}


function StockAvgcost($stock,$tag, $db) {
	
	$SQL = "SELECT avgcost
		FROM stockcostsxtag
		WHERE stockid = '" . $stock . "'
			AND tagref='" . $tag. "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	
	$cost = $MyRow[0];
	if (is_null($cost) == true){
		$cost=0;
	}
	
	return $cost;
}

function StockLastCost($stock,$tag, $db) {
	
	$SQL = "SELECT lastcost
		FROM stockcostsxtag
		WHERE tagref = '" . $tag . "'
			And stockid = '" . $stockid . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$lastcost= $MyRow[0];
	if (is_null($lastcost) == true){
		$lastcost=0;
	}
	return $lastcost;
}



function EstimatedAvgCost($stock,$tag,$existingstocks,$avgcost,$purchstocks,$costpurch,$lastcost,$tipo=0, $db) {
	//$avgcostnew=(($existingstocks*$avgcost)+($purchstocks*$costpurch))/($existingstocks+$purchstocks);
	if (($existingstocks+$purchstocks)==0){
		$avgcostnew=$avgcost;//(($existingstocks*$avgcost)+($purchstocks*$costpurch))/($existingstocks+$purchstocks);
	}else{
		/*FCC: AGREGUE ESTA CONDICION PARA CUANDO EL COSTO EXISTENTE ESTA EN CERO, NO LO TOME EN CUENTA Y EL COSTO PROMEDIO
		 SEA EL COSTO DEL ULTIMO MOVIMIENTO.
		*/
		if (($existingstocks*$avgcost) == 0){
			$avgcostnew = $costpurch;
			$lastcost = $costpurch;
		} else {
			if(($purchstocks*$costpurch)<=0){
				$avgcostnew=$avgcost;
				$lastcost = $costpurch;
			}else{
				$avgcostnew=(($existingstocks*$avgcost)+($purchstocks*$costpurch))/($existingstocks+$purchstocks);
				$lastcost = $costpurch;
			}
		}
		
	}
	
	$fechapurch=Date('Y/m/d',CalcEarliestDispatchDate());
	// Consulto si existe el costo por producto y unidad de negocio
	$SQL=" select *
	       from stockcostsxtag
	       where stockid='".$stock."'
	       and tagref='".$tag."'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvo la actualizacion de costo promedio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);       
	if (DB_num_rows($Result)!=0)
	{
		// si existe la actualizo
		$myrowcost = DB_fetch_array($Result);
		// si el tipo de movimiento no es compra extraigo la ultima fecha de compra
		if ($tipo==0){
			$fechapurch=$myrowcost['trandate'];
		}
		
		$SQL="UPDATE stockcostsxtag
		      SET avgcost= '".$avgcostnew."',
			  lastcost='".$lastcost."',
			  lastupdatedate=DATE_FORMAT(now(),'%Y-%m-%d'),
			  lastpurchaseqty='".$purchstocks."',
			  lastpurchase='".$fechapurch."'
		      where stockid='".$stock."'
		      and tagref='".$tag."'";
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
		$DbgMsg =  _('El SQL utiliado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);            
	}else{
		// en caso de no existir este producto en la tabla lo inserto
		$SQL = "insert into stockcostsxtag(
			tagref,
			stockid,
			lastcost,
			avgcost,
			trandate,
			lastpurchase,
			lastpurchaseqty,
			lastupdatedate
			) values(
				'" . $tag . "',
				'" . $stock . "',
				'" . $lastcost . "',
				'" . $avgcostnew . "',
				DATE_FORMAT(now(),'%Y-%m-%d'),
				'" . $fechapurch . "',
				'" . $purchstocks . "',
				DATE_FORMAT(now(),'%Y-%m-%d')
			)";	
		$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
		$DbgMsg =  _('El SQL utiliado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	}
	//inserto en tabla historica
	$SQL = "insert into hs_stockcostsxtag(
		tagref,
		stockid,
		lastcost,
		avgcost,
		trandate,
		lastpurchase,
		lastpurchaseqty,
		lastupdatedate
	) values(
		'" . $tag . "',
		'" . $stock . "',
		'" . $lastcost . "',
		'" . $avgcostnew . "',
		DATE_FORMAT(now(),'%Y-%m-%d'),
		'" . $fechapurch . "',
		'" . $purchstocks . "',
		DATE_FORMAT(now(),'%Y-%m-%d')
	)";	
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion de costo promedio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	return $avgcostnew;
}


function ExtractLegalid($tag,$db) {
	$SQL = "SELECT legalid
		FROM tags
		WHERE tagref = '" . $tag . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvo la empresa de la unidad de negocio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	
	$MyRow = DB_fetch_row($Result);
	$legalid = $MyRow[0];
	return $legalid;
	
}


function ExtractTagrefXLoc($loccode,$db) {
	$SQL = "SELECT tagref
		FROM locations
		WHERE loccode = '" . $loccode . "'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvo la empresa de la unidad de negocio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$MyRow = DB_fetch_row($Result);
	$tagref = $MyRow[0];
	return $tagref;
}

function ExtractAvgCostXlegal($legalid,$stockid,$db) {
	$SQL = "SELECT avgcost
		FROM stockcostsxlegal
		WHERE legalid = '" . $legalid . "'
			And stockid = '" . $stockid . "'
		ORDER BY trandate desc";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvo la empresa de la unidad de negocio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	
	$MyRow = DB_fetch_row($Result);
	$avgcost = $MyRow[0];
	if (is_null($avgcost) == true){
		$avgcost=0;
	}
	return $avgcost;
	
}

function ExtractAvgCostXtag($tag,$stockid,$db) {
	$SQL = "SELECT avgcost
		FROM stockcostsxtag
		WHERE tagref = '" . $tag . "'
			And stockid = '" . $stockid . "'
		ORDER BY trandate desc";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvo la empresa de la unidad de negocio');
	$DbgMsg =  _('El SQL utiliado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	
	$MyRow = DB_fetch_row($Result);
	$avgcost = $MyRow[0];
	if (is_null($avgcost) == true){
		$avgcost=0;
	}
	return $avgcost;
	
}

/*Iterates through the levels of the bom, recalculating each bom it meets*/
function UpdateCost($db, $item) {
	$SQL = "SELECT parent FROM bom where component = '" . $item . "'";
	$result = DB_query($SQL, $db);
	while ($MyRow=DB_fetch_array($result)){
		$NewParent = $MyRow['parent'];
		$MaterialCost = BomMaterialCost($NewParent, $db);
		$SQL = 'UPDATE stockmaster SET materialcost=' . $MaterialCost . " WHERE stockid='" . $NewParent . "'";
		$result1 = DB_query($SQL,$db);
		UpdateCost($db, $NewParent);
	}
}


/**************************************************************************************************************/
//Calcula el tiempo que lleva ejecutar una tarea
/**************************************************************************************************************/
function CalculaTiempo($OrderNo,$tipodefacturacion,$InvoiceNo,$db)
{
	$sql="	SELECT (sum(qty)*-1) AS cantidad,stockid as prod,loccode
		FROM stockmoves
		WHERE type=".$tipodefacturacion."
		AND transno=".$InvoiceNo."
		GROUP BY stockid,loccode";
	$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);	
	while($myrowtime = DB_fetch_array($Result)){
		//extrae el tiempo de 
		$SQL="SELECT *
		      FROM locstock
		      WHERE stockid='".$myrowtime['prod']."'
		      AND loccode='".$myrowtime['loccode']."'";
		$ResultDelay= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		if (DB_num_rows($ResultDelay)>0) {
			$myrowdelay = DB_fetch_array($ResultDelay);
			$tiempo=trim($myrowdelay['timefactor']);
			$delay=trim($myrowdelay['delay']);
			if (is_null($tiempo) == true){
				$tiempo=0;
			}
			if (is_null($delay) == true){
				$delay=0;
			}
		}else{
			
			$tiempo=0;
			$delay=0;
		}
		$Cantidad=$Cantidad+($myrowtime['cantidad']*$tiempo);
		$Cantidad=$Cantidad+$delay;
	}
	$SQL="SELECT max(hour) as horafin,min(hour) as horainicio
		FROM cat_Hours
		ORDER BY hour asc
		LIMIT 1";
	$ResultDelay= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($ResultDelay)>0) {
		$myrowdelay = DB_fetch_array($ResultDelay);
		$horainicio=trim($myrowdelay['horainicio']);
		$horainicial=trim($myrowdelay['horainicio']);
		$horafin=trim($myrowdelay['horafin']);
	}
	$Cantidad=$Cantidad/60;
	$Cantidaddos=$Cantidad/8;
	$Cantidadias=number_format($Cantidaddos,0);
	$Cantidadias=floor($Cantidaddos);
	$horasresta=$Cantidad-($Cantidadias*8);
	if ($horasresta<0){
		//$Cantidadias=0;
		$horasresta=$Cantidad;
	}
	//if ($Cantidadias==0){
	$horainicio=Date('H');
	//}
	$horadia=number_format($horainicio+$horasresta,0);
	if($horadia>$horafin){
		$Cantidadias=$Cantidadias+1;
		$horafaltante=$horadia-$horafin;
		$horadia=$horainicial+$horafaltante;
	}
	if (strlen($horadia)==1){
		$horadia='0'.$horadia;	
	}
	$fechavencimiento=date("Y/m/d");
	$fechavence = dateadd_dos($fechavencimiento,$Cantidadias,0,0,0,0,0);
	$separa = explode('/',$fechavence);
	$mesvence = $separa[1];
	$aniovence = $separa[0];
	$diavence=$separa[2];
	$fechavence=$aniovence.'-'.$mesvence.'-'.$diavence.' '.$horadia.':00:00';
	
	$sqlupdate= "UPDATE salesorders
		SET deliverydate='".$fechavence."'
		WHERE orderno='".$OrderNo."'";
	$ResultDelay= DB_query($sqlupdate,$db,$ErrMsg,$DbgMsg,true);
	//echo "<br>consulta2".$sqlupdate;
	return $fechavence.'|'.$Cantidadias.'|'.number_format($Cantidad,0);

}


function dateadd_tarea($date, $dd=0, $mm=0, $yy=0, $hh=0, $mn=0, $ss=0){
	$date_r = getdate(strtotime($date)); 
	$date_result = date("Y/m/d ", mktime(($date_r["hours"]+$hh),($date_r["minutes"]+$mn),($date_r["seconds"]+$ss),($date_r["mon"]+$mm),($date_r["mday"]+$dd),($date_r["year"]+$yy)));
	return $date_result;
}



/**************************************************************************************************************/



///Inserta una tarea en tabla prdtareas, solo si la categoria del producto facturado es un servicio //////

function InsertTareas($orderno,$tipodefacturacion,$descripcion,$fechavence,$diasmax,$horasest,$db)
{
	if ($tipodefacturacion!=11){
		$sqlorden="select usertask,tags.tagref from salesorders
				inner join tags on salesorders.tagref=tags.tagref
				inner join areas on tags.areacode=areas.areacode
		where orderno=".$orderno;
		$Result= DB_query($sqlorden,$db,$ErrMsg,$DbgMsg,true);
		if (DB_num_rows($Result)==1) {
			$myrowuserid = DB_fetch_array($Result);
			$userprop=trim($myrowuserid['usertask']);
			$tagref=trim($myrowuserid['tagref']);
		}else{
			
			$userprop=$_SESSION['UserID'];
			$tagref=1;
		}
		
	}else{
		$userprop=$_SESSION['UserID'];
		$tagref=1;
	}
	$sqlorden="select * from prdproyectos where nombre like '%produccion%' and tagref=".$tagref;
	$Result= DB_query($sqlorden,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Result)==1) {
		$myrowproy = DB_fetch_array($Result);
		$idproyecto=trim($myrowproy['idproyecto']);
	}else{
		$idproyecto=14;
	}
	
	
	$ssql = "select salesorderdetails.orderno,stockcategory.categoryid as categoria ,sum(salesstockproperties.valor IS NULL)  as porcapturar ,
			    salesstockproperties.valor as trabajador, stockcategory.idflujo , prdconceptos.idconcepto,salesstockproperties.InvoiceValue as valorinvo
			    from salesorderdetails JOIN stockmaster ON  salesorderdetails.stkcode = stockmaster.stockid  
			    LEFT JOIN stockcatproperties ON  stockmaster.categoryid =  stockcatproperties.categoryid and stockcatproperties.reqatsalesorder = 1 
			    LEFT JOIN salesstockproperties ON salesstockproperties.orderno =  salesorderdetails.orderno
			    and salesstockproperties.typedocument='" . $tipodefacturacion . "'
			    LEFT JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid 
			    LEFT JOIN prdconceptos ON stockcategory.idflujo=prdconceptos.idflujo
			    where salesorderdetails.orderno='" . $orderno . "'
			    and prdconceptos.tipoconcepto='3'
			    GROUP BY salesorderdetails.orderno";
			 
		$resultman = DB_query($ssql ,$db,'','');
		//echo "<br>consulta".$ssql;
			while($myrowman = DB_fetch_array($resultman)){
				if($myrowman['trabajador']!= ''){
					$trabajador=$myrowman['trabajador'].''.$myrowman['valorinvo'];
					
				}else{
					$trabajador='';
				}
				$idprioridad = 2; //importante
				$sql = "INSERT INTO prdtareas (idconcepto,nombre,descripcion,idprioridad,
					     fechaalta,fechalimite,estimado,durmaxdias,
					     avance,idstatus,userid,fechaultimaactualizacion,
					     urecurso,idproyecto,idflujo, idpropietario,ordenprioridad)
					     VALUES ('" . $myrowman['idconcepto'] . "','".$descripcion.'asignado a '.$trabajador."','".$descripcion."',
					     '" . $idprioridad . "',now(),'".$fechavence."','".$horasest."','".$diasmax."',0,'1','".$_SESSION['UserID']."',now(),'". $userprop ."','".$idproyecto."','".$myrowman['idflujo']."','".$userprop."','100')";
			      $resultinsert = DB_query($sql,$db);
			  //    echo "<br>consulta2".$sql;
			      $PrdTareasID = DB_Last_Insert_ID($db,'prdtareas','idtarea');
			      $sqlup= "UPDATE salesorderdetails
				       SET idtarea='".$PrdTareasID."'
				       WHERE orderno='".$orderno."'";//
				       $resultup = DB_query($sqlup,$db);
				       
			     
			}	
	
}

/**************************************************************************************************************/
/********************************DEMANDA DE PRODUCTOS POR ALMACEN ********************************************/
/**************************************************************************************************************/
function ExistenciasXAlmacen($AlmacenStock,$OrderNo,$StockID,$db)
{
	
	$demandQtyQuotations = $_SESSION['DemandQtyQuotations'];
	if(empty($demandQtyQuotations)) {
		$demandQtyQuotations = "0"; // Por defecto pedidos cerrados
	}
	
    $sqlLL = "SELECT ifnull(sum(ifnull(salesorderdetails.quantity-salesorderdetails.qtyinvoiced,0)),0) AS dem
		FROM salesorderdetails,
		     salesorders
		WHERE salesorders.orderno = salesorderdetails.orderno AND
		salesorderdetails.fromstkloc='" . $AlmacenStock . "' AND
		salesorderdetails.completed=0 AND
		salesorders.quotation in($demandQtyQuotations) AND salesorders.orderno<>".$OrderNo." AND
		salesorderdetails.stkcode='" . $StockID . "'";
	
	if($_SESSION['UserID'] == 'admin') {
		//echo $sqlLL;
	}
    
    $ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
	$DbgMsg=_('El SQL utilizado es: ');
	$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQtySales =  $DemandRow[0];
	} else {
	$DemandQtySales =0;
	}
	return $DemandQtySales;
}
/**************************************************************************************************************/

/**************************************************************************************************************/
/******************************************DESCUENTO DE CATEGORIA DE INVENTARIO*******************************/
/**************************************************************************************************************/

function GetPercentDiscount($StockID,$db)
{

    $sqlLL = "SELECT stockcategory.cashdiscount
	      FROM stockmaster INNER JOIN stockcategory ON stockcategory.categoryid=stockmaster.categoryid
			AND stockmaster.stockid='" . $StockID . "'";
	$ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
	$DbgMsg=_('El SQL utilizado es: ');
	$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQtySales =  $DemandRow[0];
	} else {
		$DemandQtySales =0;
	}
	return $DemandQtySales;
}


/**************************************************************************************************************/
/*******************************STATUS PERIODO CONTABLE*******************************************************/
function TraestatusPeriod($legalid,$prd,$db)
{

    $sqlLL = "SELECT status
	      FROM periodsXlegal
		WHERE periodno=".$prd."
		AND legalid='" . $legalid . "'";
	$ErrMsg = _('No fue posible extraer el estatus del periodo contable') ;
	$DbgMsg=_('El SQL utilizado es: ');
	$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
	//echo $sqlLL;
	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQtySales =  $DemandRow[0];
	} else {
		$DemandQtySales =0;
	}
	return $DemandQtySales;
}
/**************************************************************************************************************/


/***************************************************************************************************************/
/*******************************PORCENTAJE DE DEVOLUCION*******************************************************/
function TraePercentDevXDebtorno($DebtorNo,$Stockid,$Manufacturer,$DefaultSalesType,$db)
{

    $sqlLL = "SELECT refundpercent,discountpercent,totalsale
	      FROM salesagreement
	      WHERE debtorno='".$DebtorNo."'
		AND manufacturerid='" . $Manufacturer . "'
		AND stockid='" . $Stockid . "'
		AND typeabbrev='" . $DefaultSalesType . "'
		AND effectiveto > '" . Date('Y-m-d') . "'
		AND effectiveafter < '" . Date('Y-m-d') . "'
	    ORDER BY totalsale desc";
	$ErrMsg = _('No fue posible extraer el porcentaje de devolucion para el producto') ;
	$DbgMsg=_('El SQL utilizado es: ');
	$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
	//echo $sqlLL;
	if (DB_num_rows($DemandResult)>0){
		$DemandRow = DB_fetch_row($DemandResult);
		$Devolucion =  $DemandRow[0];
		$Descuento =  $DemandRow[1];
		$totalsale=$DemandRow[2];
	} else {
		$sqlLL = "SELECT refundpercent,discountpercent,totalsale
			  FROM salesagreement
			  WHERE debtorno='".$DebtorNo."'
				AND manufacturerid='" . $Manufacturer . "'
				AND stockid='All'
				AND typeabbrev='" . $DefaultSalesType . "'
				AND effectiveto > '" . Date('Y-m-d') . "'
				AND effectiveafter < '" . Date('Y-m-d') . "'
			  ORDER BY totalsale desc";
		$ErrMsg = _('No fue posible extraer el porcentaje de devolucion para el producto') ;
		$DbgMsg=_('El SQL utilizado es: ');
		$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($DemandResult)>0){
			$DemandRow = DB_fetch_row($DemandResult);
			$Devolucion =  $DemandRow[0];
			$Descuento =  $DemandRow[1];
			$totalsale=$DemandRow[2];
		} else {
			$Devolucion =  0;
			$Descuento =  0;
			$totalsale=0;
		}
	}
	return $Devolucion.'|'.$Descuento.'|'.$totalsale;

}


/***************************************************************************************************************/
/*******************************PORCENTAJE DE DEV PROV*********************************************************/
function TraePercentDevXSupplier($SupplierNO,$Stockid,$Manufacturer,$DefaultSalesType,$db)
{

    $sqlLL = "SELECT refundpercent,discountpercent,totalpurchase
	      FROM purchaseagreement
	      WHERE suplierid='".$SupplierNO."'
		AND manufacturerid='" . $Manufacturer . "'
		AND stockid='" . $Stockid . "'
		AND typeabbrev='" . $DefaultSalesType . "'
		AND effectiveto > '" . Date('Y-m-d') . "'
		AND effectiveafter < '" . Date('Y-m-d') . "'
	    ORDER BY totalpurchase desc";
	$ErrMsg = _('No fue posible extraer el porcentaje de devolucion para el producto') ;
	$DbgMsg=_('El SQL utilizado es: ');
	$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
	//echo $sqlLL;
	if (DB_num_rows($DemandResult)>0){
		$DemandRow = DB_fetch_row($DemandResult);
		$Devolucion =  $DemandRow[0];
		$Descuento =  $DemandRow[1];
		$totalsale=$DemandRow[2];
	} else {
		$sqlLL = "SELECT refundpercent,discountpercent,totalpurchase
			  FROM purchaseagreement
			  WHERE suplierid='".$SupplierNO."'
				AND manufacturerid='" . $Manufacturer . "'
				AND stockid='All'
				AND typeabbrev='" . $DefaultSalesType . "'
				AND effectiveto > '" . Date('Y-m-d') . "'
				AND effectiveafter < '" . Date('Y-m-d') . "'
			  ORDER BY totalpurchase desc";
		$ErrMsg = _('No fue posible extraer el porcentaje de devolucion para el producto') ;
		$DbgMsg=_('El SQL utilizado es: ');
		$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($DemandResult)>0){
			$DemandRow = DB_fetch_row($DemandResult);
			$Devolucion =  $DemandRow[0];
			$Descuento =  $DemandRow[1];
			$totalsale=$DemandRow[2];
		} else {
			$Devolucion =  0;
			$Descuento =  0;
			$totalsale=0;
		}
	}
	return $Devolucion.'|'.$Descuento.'|'.$totalsale;
}

/**************************************************************************************************************/
/**************************************************************************************************************/

/**AUTORIZACION DE NOTAS DE CREDITO**/
/******************************************************************************************************************/
function InsertaNCDirect($OrdernoNC,$DebtorNo,$valor=0,$tagref,$type,$montosiniva,$montoiva,$moneda,$rate,$cuenta_notacredito, $usuario,$quotation,$fechanc,$concepto,$taxcat,$db){
	
	$SQL="insert into notescreditorders(
		noteno,
		debtorno,
		branchcode,
		type,
		tagref,
		ovamount,
		ovgst,
		currcode,
		rate,
		account,
		userregister,
		quotation,
		origtrandate,
		invtext,
		taxcat
		)
		values('".$OrdernoNC."',
		'".$DebtorNo."',
		'".$DebtorNo."',
		'".$type."',
		'".$tagref."',
		'".$montosiniva."',
		'".$montoiva."',		
		'".$moneda."',		
		'".$rate."',		
		'".$cuenta_notacredito."',		
		'".$usuario."',		
		'".$quotation."',		
		'".$fechanc."',
		'".$concepto."',
		'".$taxcat."'	
		)";
	$ErrMsg = _('La insercion de la orden de NC no se realizo');
	$DbgMsg=_('El SQL utilizado es: ');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	//echo $SQL;
	return $OrdernoNC;
}

/******************************************************************************************************************/
/**AUTORIZACION DE NOTAS DE CREDITO**/
/******************************************************************************************************************/
function ActualizarNCDirect($OrdernoNC,$DebtorNo,$valor=0,$tagref,$type,$montosiniva,$montoiva,$moneda,$rate,$cuenta_notacredito, $usuario,$quotation,$fechanc,$concepto,$taxcat,$db){
	
	$SQL="UPDATE notescreditorders
	      SET debtorno='".$DebtorNo."',
		  branchcode='".$DebtorNo."',
		  tagref='".$tagref."',
		  type='".$type."',
		  ovamount='".$montosiniva."',
		  ovgst='".$montoiva."',
		  currcode='".$moneda."',
		  rate='".$rate."',
		  account='".$cuenta_notacredito."',
		  quotation='".$quotation."',
		  invtext='".$concepto."',
		  taxcat='".$taxcat."',
		  origtrandate='".$fechanc."'
	     WHERE noteno='".$OrdernoNC."'";
	//echo $SQL;
	$ErrMsg = _('La insercion de la orden de NC no se realizo');
	$DbgMsg=_('El SQL utilizado es: ');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	return $OrdernoNC;
}

//extrae el tipo de almacen
Function GetTypeLocation ($location, &$db){

/*Gets the GL Codes relevant to the stock item account from the stock category record */
	$QuerySQL = "SELECT temploc
		     FROM locations
		     WHERE loccode='" . $location . "'";

	$ErrMsg = _('No fue posible extraer el tipo de almacen') ;
	$DbgMsg=_('El SQL utilizado es: ');
	$DemandResult = DB_query($QuerySQL,$db,$ErrMsg,$DbgMsg);
	$DemandRow = DB_fetch_row($DemandResult);
	$TipoLocation =  $DemandRow[0];
	return $TipoLocation;
}

function formatearNumero($numero = 0, $decimales = 2) {
	// return truncarNumero($numero, $decimales);
	return $numero;
}

function truncarNumero($numero, $decimales) {
	
	$cantidadDecimales = "";
	$signoDecimal = ".";

	if ($decimales == 0) {
		$signoDecimal = "";
	}

	if (empty($numero)) {
		return "0" . $signoDecimal . str_pad($cantidadDecimales, $decimales, 0, STR_PAD_RIGHT);
	}

	if (strpos($numero, ".") !== false) {
		$parts = explode(".", $numero);
		$cantidadDecimales = substr($parts[1], 0, $decimales);
		$numero = $parts[0] . $signoDecimal . str_pad($cantidadDecimales, $decimales, 0, STR_PAD_RIGHT);
	} else {
		$numero = $numero . $signoDecimal . str_pad($cantidadDecimales, $decimales, 0, STR_PAD_RIGHT);
	}

	return $numero;
}

function updateGlTransAccountingInfo($type, $typeno, &$db) {

	$sql = "SHOW COLUMNS FROM gltrans LIKE 'uuid'";
	$rs = DB_query($sql, $db);
	if (DB_fetch_array($rs)) {
		$sql = "SELECT debtortrans.uuid, custbranch.taxid FROM debtortrans
			LEFT JOIN custbranch
			ON custbranch.branchcode = debtortrans.branchcode 
			AND custbranch.debtorno = debtortrans.debtorno
			WHERE debtortrans.type = '$type' AND debtortrans.transno = '$typeno'";
		$rs = DB_query($sql, $db);
		
		if ($row = DB_fetch_array($rs)) {
			$sql = "UPDATE gltrans SET uuid = '{$row['uuid']}', rfcdestino = '{$row['taxid']}' 
				WHERE type = '$type' AND typeno = '$typeno'";
			if (DB_query($sql, $db)) {
				return true;
			}
		}
	}
	return false;
}

/**
 * @param unknown $tipomovimiento
 * @param unknown $tipo_abono
 * @param unknown $tipo_cargo
 * @param unknown $transno
 * @param unknown $periodo
 * @param unknown $total
 * @param unknown $unidadnegocio
 * @param unknown $fecha
 * @param unknown $clave
 * @param unknown $db
 * @param unknown $tablaalterna
 * @param unknown $idsupplier
 * @param unknown $descripcion
 */

function GeneraMovimientoContablePresupuesto($tipomovimiento, $tipo_abono, $tipo_cargo, $transno, $periodo, $total, $unidadnegocio, $fecha, $clavepresupuestal= "", $referencia= "", &$db, $tablaalterna=false, $idsupplier='', $descripcion='', $narrativaAlter="", 
	$ue="", $posteo=1, $chequeno=0, $nu_folio_ue=0, $nu_supptrans_detailid = 0) 
{

    if($tablaalterna==false) {
      $tabla='gltrans'; 
      $camposupl='';
      $value='';
    } else {
    	//$tabla='gltrans';
      $tabla='gltrans_polisa';
      $camposupl=',supplier';
      $value=",'".$idsupplier."'";
    }
    
    if(empty($descripcion))
      $descripcion='';

    $resultado= "";
    $arreglo_cuentaspresupuesto= array(); // arreglo para guardar las cuentas contables asociadas el tipo del movimiento

	$consulta = "SELECT gllink_presupuestalingreso,
	gllink_presupuestalingresoEjecutar,
	gllink_presupuestalingresoModificado,
	gllink_presupuestalingresoDevengado,
	gllink_presupuestalingresoRecaudado,
	gllink_presupuestalegreso, 
	gllink_presupuestalegresoEjercer, 
	gllink_presupuestalegresoModificado,
	gllink_presupuestalegresocomprometido, 
	gllink_presupuestalegresodevengado, 
	gllink_presupuestalegresoejercido,
	gllink_presupuestalegresopagado
	FROM companies
	ORDER BY coycode
	LIMIT 1";

	$consulta = "SELECT 
	ln_presupuestalingreso,
	ln_presupuestalingresoEjecutar,
	ln_presupuestalingresoModificado,
	ln_presupuestalingresoDevengado,
	ln_presupuestalingresoRecaudado,
	ln_presupuestalegreso,
	ln_presupuestalegresoEjercer,
	ln_presupuestalegresoModificado,
	ln_presupuestalegresocomprometido,
	ln_presupuestalegresodevengado,
	ln_presupuestalegresoejercido,
	ln_presupuestalegresopagado
	FROM tb_momentos_presupuestales
	WHERE
	tb_momentos_presupuestales.ln_ur = '".$unidadnegocio."'
	AND tb_momentos_presupuestales.ln_ue = '".$ue."'";

    $resultado = DB_query($consulta, $db);

    if ($renglon = DB_fetch_array($resultado)) {
        $arreglo_cuentaspresupuesto["INGRESO_APROBADO"]= $renglon["ln_presupuestalingreso"];
        $arreglo_cuentaspresupuesto["INGRESO_EJECUTAR"]= $renglon["ln_presupuestalingresoEjecutar"];
        $arreglo_cuentaspresupuesto["INGRESO_DEVENGADO"]= $renglon["ln_presupuestalingresoDevengado"];
        $arreglo_cuentaspresupuesto["INGRESO_RECAUDADO"]= $renglon["ln_presupuestalingresoRecaudado"];
        $arreglo_cuentaspresupuesto["INGRESO_MODIFICADO"]= $renglon["ln_presupuestalingresoModificado"];
        $arreglo_cuentaspresupuesto["APROBADO"]= $renglon["ln_presupuestalegreso"];
        $arreglo_cuentaspresupuesto["POREJERCER"]= $renglon["ln_presupuestalegresoEjercer"];
        $arreglo_cuentaspresupuesto["MODIFICADO"]= $renglon["ln_presupuestalegresoModificado"];
        $arreglo_cuentaspresupuesto["COMPROMETIDO"]= $renglon["ln_presupuestalegresocomprometido"];
        $arreglo_cuentaspresupuesto["DEVENGADO"]= $renglon["ln_presupuestalegresodevengado"];
        $arreglo_cuentaspresupuesto["EJERCIDO"]= $renglon["ln_presupuestalegresoejercido"];
        $arreglo_cuentaspresupuesto["PAGADO"]= $renglon["ln_presupuestalegresopagado"];
    } else {
        // return "No existen preferencias de empresa configuradas...";
    }

    // consulta para obtener el nombre de la cuenta de abono
    $consulta= "SELECT accountname FROM chartmaster WHERE accountcode='".$arreglo_cuentaspresupuesto[$tipo_abono]."'";
    $resultado = DB_query($consulta, $db);
    $renglon= DB_fetch_array($resultado);
    $nombrecuentaabono= $renglon["accountname"];
    $cadenaref=  explode("->$%&<-", $referencia);
//    echo '<br>'.$referencia;	
//    print_r($cadenaref);
    if (count($cadenaref)>1) {
        $compref=" @".$cadenaref[1];
    }else{
        $compref='';
    }
    $trans=$cadenaref[0];
 
    // consulta para obtener el nombre de la cuenta de cargo
    $consulta= "SELECT accountname FROM chartmaster WHERE accountcode='".$arreglo_cuentaspresupuesto[$tipo_cargo]."'";

    $resultado = DB_query($consulta, $db);
    $renglon= DB_fetch_array($resultado);
    $nombrecuentacargo= $renglon["accountname"];

    $narrativa = "Ref-".$trans."@ Cargo a la cuenta de presupuesto".$arreglo_cuentaspresupuesto[$tipo_cargo]." - ".$nombrecuentacargo.$compref.",".$obgasto." ".$nomiobgasto."";

    if (!empty($narrativaAlter)) {
    	// Cambiar narrativa por default
    	$narrativa = $narrativaAlter;
    }

    // Cargo a la Cuenta de Presupuesto
    $consulta = "INSERT INTO ".$tabla." (type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    lasttrandate,
                    userid,
                    rate,
                    clavepresupuestal,
                    dateadded, 
                    descripcion,
                    supplier, 
                    posted, 
                    ln_ue, 
                    chequeno, 
                    nu_folio_ue,
                    nu_supptrans_detailid)
                VALUES (".$tipomovimiento.",
                    '".$transno."',
                    '".$fecha."',
                    '".$periodo."',
                    '".$arreglo_cuentaspresupuesto[$tipo_cargo]."',
                    '".$narrativa."',
                    ".$total.",
                    '".$unidadnegocio."',
                    '".$fecha."',
                    '".$_SESSION['UserID']."',
                    1, '".$clavepresupuestal."',
                    NOW(),
                    '".$descripcion."',
                    '".$idsupplier."', 
                    '".$posteo."',
                    '".$ue."',
                    '".$chequeno."', 
                    '".$nu_folio_ue."',
                	'".$nu_supptrans_detailid."')";

    $resultado = DB_query($consulta, $db);


    if($_SESSION['UserID'] != "admin"){
     //  echo '<br><pre>MovCon2'.$consulta;
    }
    /// invertir 
       // Abono a la Cuenta de Presupuesto
    
    $obgasto = 'SELECT g_cat_objeto_gasto.partida_esp, nombre FROM g_cat_objeto_gasto 
    			INNER JOIN chartdetailsbudgetbytag ON g_cat_objeto_gasto.partida_esp = chartdetailsbudgetbytag.partida_esp 
    			WHERE accountcode="'.$clavepresupuestal.'"';
    			
    $resobgasto = DB_query($obgasto, $db);
    list($obgasto, $nomiobgasto) = DB_fetch_array($resobgasto);
    
    $narrativa = "Ref-".$trans."@ Abono a la cuenta de presupuesto ".$arreglo_cuentaspresupuesto[$tipo_abono]." - ".$nombrecuentaabono.$compref.",".$obgasto." ".$nomiobgasto."";

    if (!empty($narrativaAlter)) {
    	// Cambiar narrativa por default
    	$narrativa = $narrativaAlter;
    }

  	$consulta = "INSERT INTO ".$tabla." (type,
                    typeno,
                    trandate,
                    periodno,
                    account,
                    narrative,
                    amount,
                    tag,
                    lasttrandate,
                    userid,
                    rate,
                    clavepresupuestal,
                    dateadded,
                    descripcion,
                    supplier, 
                    posted, 
                    ln_ue, 
                    chequeno, 
                    nu_folio_ue,
                    nu_supptrans_detailid)
                VALUES (".$tipomovimiento.",
                    '".$transno."',
                    '".$fecha."',
                    '".$periodo."',
                    '".$arreglo_cuentaspresupuesto[$tipo_abono]."',
                    '".$narrativa."',
                    -".$total.",
                    '".$unidadnegocio."',
                    '".$fecha."',
                    '".$_SESSION['UserID']."',
                    1, '".$clavepresupuestal."',
                    NOW(),
                    '".$descripcion."',
                    '".$idsupplier."', 
                    '".$posteo."', 
                    '".$ue."',
                    '".$chequeno."',
                    '".$nu_folio_ue."',
                	'".$nu_supptrans_detailid."')";
    $resultado = DB_query($consulta, $db);
	
    if($_SESSION['UserID'] != "admin"){
       // echo '<br><pre>MovCon1'.$consulta;
    }
    

    return $resultado;
}

/**
 * 
 * @param unknown $clave
 * @param unknown $unidadnegocio
 * @param unknown $periodo
 * @param unknown $db
 * @return Ambigous <number, unknown>
 */
function TraePresupuestoDisponible($clave, $unidadnegocio, $periodo, &$db) 
{
	//digitos a truncar
	if(isset($_SESSION['TruncarDigitos']))
	{
		$digitos=$_SESSION['TruncarDigitos'];
	}else{
		$digitos=4;
	}
	
    $prespuestodisponible= 0;
  
    $consulta= "SELECT budgetid, accountcode, tagref, budget, period, Case When modified Is Null Then 0 Else modified End as modified 
                FROM chartdetailsbudgetbytag 
                WHERE accountcode= '".$clave."' AND period= '".$periodo."' AND tagref= '".$unidadnegocio."'";

    $resultado= DB_query($consulta, $db);
    
    if ($_SESSION["UserID"] == "desarrollo") {
		//         	echo "<pre>".$consulta;
    }
    
    $prespuestodisponible=0;
    
    //Si el presupuesto existe ir por disponible
    	
    if ($renglon= DB_fetch_array($resultado)) {
        $prespuestodisponible= $renglon["budget"]+$renglon["modified"];
        unset($resultado);
    }
    
//     echo 'UnidadNegocio'.$unidadnegocio;
//     echo 'Tipo:'.$_GET['tipo'];
    if ($unidadnegocio==2 or $unidadnegocio=="02" and empty($_GET['tipo'])) {
    	$_GET['tipo']=2;
    }
    // buscar movimientos contables que restan al presupuesto
    $consulta = "SELECT CASE WHEN SUM(amount) IS NULL THEN 0 ELSE SUM(amount) END AS total_ejecutado
                 FROM gltrans  
                 WHERE clavepresupuestal = '".$clave."'";
                if ($_GET['tipo']==2) {
                    $consulta.="AND account IN ('8.2.2.1.1')";// -- Cuenta para calcular el egreso disponible";
                }else{
                    $consulta.="AND  account IN ('8.1.5.1.1')";// -- Cuenta para calcular el ingreso disponible";
                }
                $consulta.=" AND periodno= '".$periodo."'";
//    echo '<br><pre>'.$consulta;
    $resultado= DB_query($consulta, $db);               
    
    if ($renglon = DB_fetch_array($resultado)) {
		if ($_GET['tipo']==2) {  // disponible Egreso
	        	$prespuestodisponible= $renglon["total_ejecutado"];            //$consulta.="AND account IN ('8.2.2.1.1')";      
		}  
		if ($_GET['tipo']==1) {  // disponible Ingreso
			$prespuestodisponible=$prespuestodisponible-ABS($renglon["total_ejecutado"]); 
		}
	    	//Truncado de digitos
    	$prespuestodisponible = truncateFloat($prespuestodisponible, $digitos);
    }
    
    
    //Modo de claculo anterior
//    if ($renglon = DB_fetch_array($resultado)) {
//        $prespuestodisponible-=ABS($renglon["total_ejecutado"]);
//    }
//    if ($_GET['tipo']==2) {
//        $prespuestodisponible= $renglon["total_ejecutado"];
//    }
//    $prespuestodisponible = truncateFloat($prespuestodisponible, $digitos);
    		
    return $prespuestodisponible;
}

/**
 * Función para obtener la información de la retencion
 * @param  [type]  $db    Base de datos
 * @param  integer $idRet Id de la retención
 * @return [type]         [description]
 */
function fnObtenerInfoRetencion($db, $type = 0)
{
    // Obtener informacion del tipo de documento
    $SQL = "SELECT * FROM tb_retenciones WHERE id = '".$type."'";
    $ErrMsg = "No se pudo obtener la información de la retención ".$type;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($TransResult);
    return $myrow;
}

function fnInfoPresupuesto($db, $clavepresupuestal, $period="", $tipo_abono="", $legalid="", $datosClave=0, $datosClaveAdecuacion=0, $tipoAfectacion="", $type="", $transno="", $tipoMovimiento="", $folioTranfer="", $unitBusiness="", $unitExecuting="", $compromisoMes = 0, $nu_id_devengado = "", $nu_id_compromiso = "", $nu_idret = 0, $type_Refund = 0, $clcSiaff = "", $clcGRP = "", $clcSicop = "", $sequence_siaff = 0,$typeSearchRefund = 1,$typesPayments = 0) {
	$datosPresupuesto = array();
	$info = array();

	//	echo "\n\n\n";
	//  echo "\n Type: ". $typesPayments;
	/* echo "\n transno: ". $transno;
	echo "\n tipoMovimiento: ". $tipoMovimiento;
	echo "\n folio: ". $folioTranfer;
	echo "\n ur: ". $unitBusiness;
	echo "\n ue: ". $unitExecuting;
	echo "\n\n\n";*/
	//exit();

	//print_r($compromisoMes);

	// Obtener periodo actual para compromisos
	$periodoActual = GetPeriod(date('d/m/y'), $db);

	$SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetbytag.*
            FROM chartdetailsbudgetbytag
            JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE chartdetailsbudgetbytag.accountcode = '".$clavepresupuestal."'";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	// Datos generales
    	$datosPresupuesto['budgetid'] = $myrow ['budgetid'];
    	$datosPresupuesto['accountcode'] = $myrow ['accountcode'];
    	$datosPresupuesto['legalid'] = $myrow ['legalid'];
    	$datosPresupuesto['tagref'] = $myrow ['tagref'];
    	$datosPresupuesto['budget'] = $myrow ['budget'];
    	$datosPresupuesto['period'] = $myrow ['period'];
    	$datosPresupuesto['modified'] = $myrow ['modified'];
    	$datosPresupuesto['fecha_modificacion'] = $myrow ['fecha_modificacion'];
    	$datosPresupuesto['idClavePresupuesto'] = $myrow ['idClavePresupuesto'];
    	$datosPresupuesto['año'] = $myrow ['anho'];
    	$datosPresupuesto['mes'] = date('m');
    	$datosPresupuesto['tipoMovimiento'] = $tipoMovimiento;
    	$datosPresupuesto['partida_esp'] = $myrow ['partida_esp'];
    	$datosPresupuesto['noDevengado'] = $nu_id_devengado;
    	$datosPresupuesto['noCompromiso'] = $nu_id_compromiso;
    	$datosPresupuesto['noRetencion'] = $nu_idret;
        $datosPresupuesto['folioTranfer'] = $folioTranfer;
        $datosPresupuesto['typeRefund'] = $type_Refund;
        $datosPresupuesto['ln_clcGRP'] = $clcGRP;
        $datosPresupuesto['ln_clcSicop'] = $clcSicop;
        $datosPresupuesto['ln_clcSiaff'] = $clcSiaff;
        $datosPresupuesto['activo'] = "inactivo";
    	$nombreRetencion = fnObtenerInfoRetencion($db, $nu_idret);
    	$nombreRetencion = $nombreRetencion['txt_descripcion'];
    	if (empty($nombreRetencion)) {
    		$nombreRetencion = "";
    	}
    	$datosPresupuesto['nombreRetencion'] = $nombreRetencion;

    	$datosPresupuesto['tipoAfectacion'] = "0";
		if (!empty($tipoAfectacion)) {
	    	$SQL = "SELECT nu_afectacion FROM tb_tipo_afectacion WHERE nu_afectacion = '".($tipoAfectacion)."'";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$datosPresupuesto['tipoAfectacion'] = $row['nu_afectacion'];
			}
		}

		if ($myrow ['anho'] != date('Y')) {
			// si no es el año actual tomar diciembre
			$periodoActual = GetPeriod(date('d').'/12/'.$myrow ['anho'], $db);
		}

		if ($_SESSION['UserID'] == 'user.autorizador') {
			// echo "\n periodoActual: ".$periodoActual."\n";
		}

    	// Datos de los meses+
    	$sqlWhere = "";
    	if (!empty($period)) {
    		$sqlWhere = " AND periods.periodno = '".$period."' ";
    	}
    	$SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
				FROM periods 
				LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
				WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%'
				AND LOCATE('.', periods.periodno) = '0' ".$sqlWhere." 
				ORDER BY periods.lastdate_in_period asc";
		$resultPeriods = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowPeriods = DB_fetch_array ( $resultPeriods ) ) {
			//Obtener presupuesto por mes
			$disponible = 0;
			$disponibleSel = 0;
			$disponibleAcumulado = 0;
			$disponibleCompromiso = 0;
            $disponibleReintegro = 0;
            $disponibleRetenciones = 0;
			// GROUP BY chartdetailsbudgetlog.period, mesName
			$SQL = "SELECT
					chartdetailsbudgetlog.period,
					cat_Months.mes as mesName,
					periods.lastdate_in_period,
					SUM(chartdetailsbudgetlog.qty) as total,
					(
					SELECT
					SUM(log.qty) as total
					FROM chartdetailsbudgetlog log
					WHERE 
					log.cvefrom = chartdetailsbudgetlog.cvefrom
					AND log.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible = 1)
					AND log.sn_disponible = 1
					AND log.period <= periods.periodno
					) as acumulado
					FROM chartdetailsbudgetlog
					LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
					LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
					WHERE 
					chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."'
					AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible = 1)
					AND chartdetailsbudgetlog.sn_disponible = 1
					AND periods.periodno = '".$rowPeriods['periodno']."'
					GROUP BY chartdetailsbudgetlog.period, mesName, periods.lastdate_in_period
					ORDER BY periods.lastdate_in_period ASC";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$disponible = $row['total'];
			}

			// Obtener acomulado
			$SQL = "SELECT
					chartdetailsbudgetlog.period,
					cat_Months.mes as mesName,
					periods.lastdate_in_period,
					SUM(chartdetailsbudgetlog.qty) as total
					FROM chartdetailsbudgetlog
					LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
					LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
					WHERE 
					chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."'
					AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible = 1)
					AND chartdetailsbudgetlog.sn_disponible = 1
					AND periods.periodno <= '".$rowPeriods['periodno']."'";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				// Mostrar acomulado
				$disponibleAcumulado = $row['total'];
			}

			$totalCompra = 0;


			if ((!empty($type) and !empty($transno) and !empty($tipoMovimiento)) || (!empty($folioTranfer) and !empty($unitBusiness) and !empty($unitExecuting))) {

               /* echo "\n\n\n";
                echo "\n Type: ". $type;
                echo "\n transno: ". $transno;
                echo "\n tipoMovimiento: ". $tipoMovimiento;
                echo "\n folio: ". $folioTranfer;
                echo "\n ur: ". $unitBusiness;
                echo "\n ue: ". $unitExecuting;
                echo "\n\n\n";
				*/

				$sqlWhere = " AND qty < 0 "; // Reduccion
			    if ($tipoMovimiento == "Ampliacion") {
			        $sqlWhere = " AND qty > 0 "; // Ampliacion
			    }

			    if ($type == '250') {
			    	// Si es adecuacion presupuestal
			    	if ($tipoMovimiento != "Ampliacion") {
			    		$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 256 "; // Reduccion
			    	} else if ($tipoMovimiento == "Ampliacion") {
			    		$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 257 "; // Ampliacion
			    	}
			    }

			    if ($type == '263') {
			    	// Si es suficiencia presupuestaria
			    	$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 263 "; // Reduccion
			    }


                if ($type == '293') {
                    // Si es Reintegro

                    if($type_Refund == 1){
                        $sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 268 "; // Reintegros Ministrado
                    }else{
                        if($type_Refund == 3){
                            $sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 265 "; // Reintegros Proveedores
                        }else{
                            if($type_Refund == 2){
                                $sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 271 "; // Reintegros Radicado
                            }
                        }
                    }
                }

                if ($type == '294' || $type == '295' || $type == '296' || $type == '297' || $type == '298' || $type == '299') {
                	// Si es pago
                	$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 260 "; // PAgo
                }

                if ($type == '298' || $type == '299') {
                    // Si es pago de retenciones
                    $sqlWhere .= " AND chartdetailsbudgetlog.nu_idret = '".$nu_idret."' "; // Reintegros
                }

                if ($type == '22' || $type == '304') {
                	// Si es pagado
                	$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 265 "; // PAgo
                }

			    $estatusOperacion = "";
			    $perdidoInfo = 0;
				$SQL = "SELECT abs(chartdetailsbudgetlog.qty) as total, chartdetailsbudgetlog.estatus, chartdetailsbudgetlog.period, chartdetailsbudgetlog.nu_secuencia_siaff
						FROM chartdetailsbudgetlog 
						WHERE 
						chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."'
						AND chartdetailsbudgetlog.type = '".$type."'
						AND chartdetailsbudgetlog.transno = '".$transno."' 
						AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ".$sqlWhere;

				if ($_SESSION['UserID'] == 'user.autorizador') {
					// echo "<br> SQL: ".$SQL;
					// echo "<br>";
				}
				
				if (($type == '259' || $type == '294' || $type == '295' || $type == '296' || $type == '297' || $type == '298' || $type == '293' || $type == '22' || $type == '304') && $rowPeriods['periodno'] == $periodoActual) {
                    // Si es compromiso, mostrar acomulado en el mes actual
                    $SQL = "SELECT SUM(abs(chartdetailsbudgetlog.qty)) as total, chartdetailsbudgetlog.estatus, chartdetailsbudgetlog.period, chartdetailsbudgetlog.nu_secuencia_siaff
						    FROM chartdetailsbudgetlog 
						    WHERE chartdetailsbudgetlog.cvefrom = '" . $clavepresupuestal . "'
                            AND chartdetailsbudgetlog.type = '" . $type . "'
                            AND chartdetailsbudgetlog.transno = '" . $transno . "' 
                            AND chartdetailsbudgetlog.period <= '" . $rowPeriods['periodno'] . "' " . $sqlWhere;
                }

                if ($_SESSION['UserID'] == 'user.autorizador') {
					// echo "\n <pre>SQL: ".$SQL."\n";
				}

				$result = DB_query ( $SQL, $db, $ErrMsg );
				while ( $row = DB_fetch_array ( $result ) ) {
					// Total seleccionado
					$disponibleSel = $row['total'];
					$estatusOperacion = $row['estatus'];
					$perdidoInfo = $row['period'];
					$secuenciaSiaff =  $row['nu_secuencia_siaff'];
				}
				
				if (($type == '22' || $type == '304') && $rowPeriods['periodno'] != $periodoActual) {
					// Si es pago en rectifiaciones, solo mostrar en el periodo actual la informacion
					$disponibleSel = 0;
				}

				if ($_SESSION['UserID'] == 'user.autorizador') {
					// echo "\n **************";
					// echo "\n type: ".$type;
					// echo "\n transno: ".$transno;
					// echo "\n nu_id_compromiso: ".$nu_id_compromiso;
				}

				if ($type == '259' || $type == '295' || $type == '296' || ($type == '304' && !empty($nu_id_compromiso))) {
					// Si es compromiso o pago de compromiso, obtener total del compromiso
					$SQL = "SELECT
					tb_compromiso.nu_id_compromiso
					FROM tb_compromiso
					WHERE
					tb_compromiso.nu_type = '".$type."'
					AND tb_compromiso.nu_transno = '".$transno."'";
					if ($type == '295' || $type == '296') {
						// pago de compromiso
						$SQL = "SELECT
						tb_pagos.nu_id_compromiso
						FROM tb_pagos
						WHERE
						tb_pagos.nu_type = '".$type."'
						AND tb_pagos.nu_transno = '".$transno."'";
					}
					$result = DB_query ( $SQL, $db, $ErrMsg );
					$rowCompromiso = DB_fetch_array ( $result );

					if ($type == '304') {
						$rowCompromiso['nu_id_compromiso'] = $nu_id_compromiso;
					}

					$sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
	                if ($periodoActual == $rowPeriods['periodno'] && $compromisoMes == '0') {
	                    // Mes actual es acomulado
	                    $sqlWhere = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
	                }

	                $SQL = "SELECT 
	                chartdetailsbudgetlog.cvefrom,
	                chartdetailsbudgetlog.period,
	                SUM(chartdetailsbudgetlog.qty) as qty
	                FROM chartdetailsbudgetlog
	                WHERE
	                chartdetailsbudgetlog.sn_disponible = 1
	                AND chartdetailsbudgetlog.nu_id_compromiso = '".$rowCompromiso['nu_id_compromiso']."'
	                AND chartdetailsbudgetlog.nu_tipo_movimiento = 259
	                AND chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'".$sqlWhere;
	                $resultTotal = DB_query($SQL, $db, $ErrMsg);
	                while ($rowTotal = DB_fetch_array($resultTotal)) {
	                    if (!empty($rowTotal['qty']) && $rowTotal['qty'] < 0) {
	                        $disponibleCompromiso = abs($rowTotal['qty']);
	                    }
	                }
				}

				if ($type == '501' || $type == '297') {
					// Obtener total del oficio de comisión
					$sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
	                if ($periodoActual == $rowPeriods['periodno'] && $compromisoMes == '0') {
	                    // Mes actual es acomulado
	                    $sqlWhere = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
	                }

	                $type2 = $type;
	                $transno2 = $transno;

	                if ($type == '297') {
	                	// Pagos de viaticos
	                	$SQL = "SELECT tb_pagos.sn_folio_solicitud, tb_viaticos.systypeno
						FROM tb_pagos
						JOIN tb_viaticos ON tb_viaticos.sn_folio_solicitud = tb_pagos.sn_folio_solicitud
						WHERE
						tb_pagos.nu_type = '".$type."'
						AND tb_pagos.nu_transno = '".$transno."'";
						$result = DB_query ( $SQL, $db, $ErrMsg );
						$rowCompromiso = DB_fetch_array ( $result );

						$type2 = 501;
	                	$transno2 = $rowCompromiso['systypeno'];
	                }

					$SQL = "SELECT 
	                chartdetailsbudgetlog.cvefrom,
	                chartdetailsbudgetlog.period,
	                SUM(chartdetailsbudgetlog.qty) as qty
	                FROM chartdetailsbudgetlog
	                WHERE
	                chartdetailsbudgetlog.sn_disponible = 1
	                AND chartdetailsbudgetlog.nu_tipo_movimiento = 259
	                AND chartdetailsbudgetlog.type = '".$type2."'
	                AND chartdetailsbudgetlog.transno = '".$transno2."'
	                AND chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'".$sqlWhere;
	                $resultTotal = DB_query($SQL, $db, $ErrMsg);
	                while ($rowTotal = DB_fetch_array($resultTotal)) {
	                    if (!empty($rowTotal['qty']) && $rowTotal['qty'] < 0) {
	                        $disponibleCompromiso = abs($rowTotal['qty']);
	                    }
	                }
				}

                //Reintegros

                if($type == '293'){

                    if($type_Refund == 1){


                        $sqlQuery = "";
                        $sqlQueryWhere = "";
                        $totalG = 0;
                        $totalQTY = 0;

                        if($unitBusiness != ''){
                            $sqlQuery .= " tb_ministracion.ln_ur = '".$unitBusiness."' ";
                        }

                        if($folioTranfer != ''){
                            $sqlQuery .= " AND tb_ministracion.folio = ".$folioTranfer." ";
                        }


                        $sqlQueryWhere = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
                        if ($compromisoMes == '1') {
                            // Si llevar uno validar contra el mes actual
                            //$sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
                            $sqlQueryWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
                        }


                        /*       echo $queryHeaderSQL;
                               echo "\n Query_SQL: ".$queryHeaderSQL;
                               echo "\n\n\n";

                       */

                        $queryHeaderSQL =  "SELECT tb_ministracion.id, tb_ministracion.folio, tb_ministracion.ln_clcSiaff, tb_ministracion.ln_clcGRP, tb_ministracion.ln_clcSicop, tb_ministracion_detalle.presupuesto, tb_ministracion_detalle.autorizado, 
                                            chartdetailsbudgetlog.transno, chartdetailsbudgetlog.type, SUM(chartdetailsbudgetlog.qty) AS QTYR, chartdetailsbudgetlog.period, chartdetailsbudgetlog.nu_secuencia_siaff FROM tb_ministracion
                                            JOIN tb_ministracion_detalle ON tb_ministracion.id = tb_ministracion_detalle.idMinistracion
                                            JOIN chartdetailsbudgetlog ON tb_ministracion.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 291 AND chartdetailsbudgetlog.qty > 0 AND tb_ministracion_detalle.presupuesto = chartdetailsbudgetlog.cvefrom 
                                            WHERE ".$sqlQuery." ".$sqlQueryWhere." AND tb_ministracion.estatus = 5 AND tb_ministracion_detalle.presupuesto = '".$clavepresupuestal."' AND tb_ministracion.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                            GROUP BY tb_ministracion_detalle.presupuesto" ;



                        $queryHeaderRefunds = "SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.ue_id, tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.status_refund,
                                            chartdetailsbudgetlog.type, chartdetailsbudgetlog.estatus, SUM(chartdetailsbudgetlog.qty) AS Vqty, chartdetailsbudgetlog.period FROM tb_refunds_notice
                                            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                                            WHERE chartdetailsbudgetlog.type = 293 AND chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."'
                                            AND tb_refunds_notice.status_refund = 4 AND tb_refunds_notice.folio_invoice_transfer LIKE '%".$folioTranfer."%' AND tb_refunds_notice.ur_id = '".$unitBusiness."' AND chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                            GROUP BY chartdetailsbudgetlog.estatus";


                        // print_r($queryHeaderSQL);

                        // print_r($queryHeaderRefunds);
                        // exit();

                        $resultQueryQTYSQL = DB_query($queryHeaderSQL, $db, $ErrMsg);

                        if(DB_num_rows($resultQueryQTYSQL) > 0){

                            while ($rowResultQTYSQL = DB_fetch_array($resultQueryQTYSQL)){
                                $totalQTY = $rowResultQTYSQL['QTYR'];

                            }


                        }else{

                            $totalQTY = 0;
                        }

                        /*++++++*/
                        $rQTY = DB_query($queryHeaderRefunds, $db, $ErrMsg);

                        $cont = DB_num_rows($rQTY);

                        if($cont > 0){

                            while ($rowResultQTY = DB_fetch_array($rQTY)){
                                $totalG = $rowResultQTY['Vqty'];
                            }

                        }else{
                            if($cont <= 0){
                                $totalG = 0;
                            }

                        }


                        $disponibleReintegro = $totalQTY - $totalG;

                        // echo "\n Cantidad_Dispo: ".$disponibleReintegro;


                    }else{
                        if($type_Refund == 3){

                            $sqlQuery="";
                            $totalG = 0;
                            $totalQTY = 0;
                            $rectiTotal = 0;

                            if($folioTranfer != ''){
                                $sqlQuery .= " AND supptrans.suppreference = ".$folioTranfer." ";
                            }
                            if($unitBusiness != ''){
                                $sqlQuery .= " AND supptrans.tagref = '".$unitBusiness."' ";
                            }

                            if($unitExecuting != ''){
                                $sqlQuery .= " AND supptrans.ln_ue = ".$unitExecuting." ";
                            }


                            $sqlQueryWhere = " AND supptransdetails.period <= '".$rowPeriods['periodno']."' ";
                            if ($compromisoMes == '1') {
                                // Si llevar uno validar contra el mes actual
                                //$sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
                                $sqlQueryWhere = " AND supptransdetails.period = '".$rowPeriods['periodno']."' ";
                            }


                            /* Cambio Rectificaciones */

                            $searchRecti="SELECT tb_rectificaciones.nu_type, tb_rectificaciones.nu_transno, tb_rectificaciones.sn_tagref, 
                                          tb_rectificaciones.ln_ue, tb_rectificaciones.nu_type_pago, tb_rectificaciones.nu_folio_pago, chartdetailsbudgetlog.transno,
                                          chartdetailsbudgetlog.type, 
                                          CAST(SUM(chartdetailsbudgetlog.qty) as decimal(16,4)) AS total_rectifi, chartdetailsbudgetlog.cvefrom, 
                                          chartdetailsbudgetlog.partida_esp
                                          FROM tb_rectificaciones
                                          LEFT JOIN chartdetailsbudgetlog ON tb_rectificaciones.nu_type = chartdetailsbudgetlog.type AND tb_rectificaciones.nu_transno = chartdetailsbudgetlog.transno
                                          WHERE tb_rectificaciones.nu_type_pago = 22 AND tb_rectificaciones.nu_folio_pago = ".$folioTranfer."
                                          AND tb_rectificaciones.sn_tagref = '".$unitBusiness."' AND tb_rectificaciones.ln_ue = ".$unitExecuting." AND chartdetailsbudgetlog.qty < 0
                                          AND tb_rectificaciones.nu_estatus = 4 AND chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."' AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                          GROUP BY chartdetailsbudgetlog.cvefrom";

                            //  $ErrMsg = "No se realizo la Consulta";
                            $resultRectificacion = DB_query($searchRecti, $db, $ErrMsg);




                            $searchRectiPLUS="SELECT tb_rectificaciones.nu_type, tb_rectificaciones.nu_transno, tb_rectificaciones.sn_tagref, 
                                          tb_rectificaciones.ln_ue, tb_rectificaciones.nu_type_pago, tb_rectificaciones.nu_folio_pago, chartdetailsbudgetlog.transno,
                                          chartdetailsbudgetlog.type, 
                                          CAST(SUM(chartdetailsbudgetlog.qty) as decimal(16,4)) AS total_rectifi, chartdetailsbudgetlog.cvefrom, 
                                          chartdetailsbudgetlog.partida_esp
                                          FROM tb_rectificaciones
                                          LEFT JOIN chartdetailsbudgetlog ON tb_rectificaciones.nu_type = chartdetailsbudgetlog.type AND tb_rectificaciones.nu_transno = chartdetailsbudgetlog.transno
                                          WHERE tb_rectificaciones.nu_type_pago = 22 AND tb_rectificaciones.nu_folio_pago = ".$folioTranfer."
                                          AND tb_rectificaciones.sn_tagref = '".$unitBusiness."' AND tb_rectificaciones.ln_ue = ".$unitExecuting." AND chartdetailsbudgetlog.qty > 0
                                          AND tb_rectificaciones.nu_estatus = 4 AND chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."' AND tb_rectificaciones.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                          GROUP BY chartdetailsbudgetlog.cvefrom";

                            //  $ErrMsg = "No se realizo la Consulta";
                            $resultRectificacionPLUS = DB_query($searchRectiPLUS, $db, $ErrMsg);


                            /* Cambio Rectificaciones */


                            $querySearchRefund = "SELECT supptrans.id, supptrans.tagref, supptrans.type, supptrans.transno, supptrans.ln_ue, suppallocs.transid_allocfrom, suppallocs.transid_allocto,
                                                supptransdetails.supptransid, supptransdetails.description, supptransdetails.price, supptransdetails.qty, supptransdetails.clavepresupuestal, supptransdetails.period,
                                                CAST(SUM(supptransdetails.qty * supptransdetails.price) as decimal(16,4)) AS totalGeneral, SUM(supptransdetails.price) AS Prices, tags.legalid FROM supptrans 
                                                JOIN suppallocs ON suppallocs.transid_allocfrom = supptrans.id
                                                JOIN supptransdetails ON suppallocs.transid_allocto = supptransdetails.supptransid                 
                                                JOIN tags ON tags.tagref = supptrans.tagref
                                                WHERE supptrans.type = 22 ".$sqlQuery." ".$sqlQueryWhere."  AND supptransdetails.clavepresupuestal = '".$clavepresupuestal."' AND supptrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                                GROUP BY supptransdetails.clavepresupuestal";

                            //                            AND supptransdetails.period = '".$rowPeriods['periodno']."'
                            // JOIN supptrans supptransDocPago ON supptransDocPago.id = supptransdetails.supptransid
                            //AND supptransDocPago.type = ".$typesPayments."
                            $resultQuery = DB_query($querySearchRefund, $db, $ErrMsg);

                            while ($rowResult = DB_fetch_array($resultQuery)) {
                                $totalG = $rowResult['totalGeneral'];

                            }

                            if($typeSearchRefund == 1){

                                $queryHeaderRefunds = "SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.ue_id, tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.status_refund,
                                            chartdetailsbudgetlog.type, chartdetailsbudgetlog.estatus, SUM(chartdetailsbudgetlog.qty) AS QTYR, chartdetailsbudgetlog.period FROM tb_refunds_notice
                                            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                                            WHERE tb_refunds_notice.folio_invoice_transfer LIKE '%".$folioTranfer."%' AND chartdetailsbudgetlog.type = 293 AND chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."'
                                            AND tb_refunds_notice.status_refund = 4 AND tb_refunds_notice.ur_id = '".$unitBusiness."' AND tb_refunds_notice.ue_id = '".$unitExecuting."' 
                                            AND chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                            GROUP BY chartdetailsbudgetlog.estatus";

                            }else{

                                $queryHeaderRefunds = "SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.ue_id, tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.status_refund,
                                            chartdetailsbudgetlog.type, chartdetailsbudgetlog.estatus, SUM(chartdetailsbudgetlog.qty) AS QTYR, chartdetailsbudgetlog.period FROM tb_refunds_notice
                                            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                                            WHERE tb_refunds_notice.folio_invoice_transfer = '".$folioTranfer."' AND chartdetailsbudgetlog.type = 293 AND chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."'
                                            AND tb_refunds_notice.status_refund = 4 AND tb_refunds_notice.ur_id = '".$unitBusiness."' AND tb_refunds_notice.ue_id = '".$unitExecuting."' 
                                            AND chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                            GROUP BY chartdetailsbudgetlog.estatus";

                            }


                            $resultQueryQTY = DB_query($queryHeaderRefunds, $db, $ErrMsg);

                            $cont = DB_num_rows($resultQueryQTY);

                            if($cont > 0){

                                while ($rowResultQTY = DB_fetch_array($resultQueryQTY)){
                                    $totalQTY = $rowResultQTY['QTYR'];
                                }

                            }else{
                                if($cont <= 0){
                                    $totalQTY = 0;
                                }
                            }


                            /* Cambio Rectificaciones */

                            if(DB_num_rows($resultRectificacion) > 0){

                                while($rowRectificacion = DB_fetch_array($resultRectificacion)){


                                    $rectiTotal = $rowRectificacion['total_rectifi'];


                                    if($rectiTotal < 0){
                                        $totalG = $totalG + $rectiTotal;
                                        $totalG = $totalG *(-1);


                                    }else if($rectiTotal > 0 ){
                                        $totalG = $totalG - $rectiTotal;
                                    }
                                    else{
                                        $totalG + $rectiTotal;
                                    }

                                }

                            }


                            if(DB_num_rows($resultRectificacionPLUS) > 0){

                                while($rowRectificacion = DB_fetch_array($resultRectificacionPLUS)){

                                    $rectiTotal = $rowRectificacion['total_rectifi'];


                                    if($rectiTotal < 0){
                                        $totalG = $totalG + $rectiTotal;
                                        $totalG = $totalG *(-1);

                                        // print_r($totalG);
                                        // exit();
                                    }else if($rectiTotal > 0 ){
                                        $totalG = $totalG - $rectiTotal;
                                    }
                                    else{
                                        $totalG + $rectiTotal;
                                    }


                                }

                            }


                            /* Cambio Rectificaciones */

                            // $disponibleReintegro = $totalG - $totalQTY;
                            $disponibleReintegro = (float)$totalG - (float)$totalQTY;

                        }else{
                            if($type_Refund == 2){


                                $sqlQuery = "";
                                $sqlQueryWhere = "";
                                $totalG = 0;
                                $totalQTY = 0;


                                if($unitBusiness != ''){
                                    $sqlQuery .= " tb_radicacion.ln_ur = '".$unitBusiness."' ";
                                }

                                if($unitExecuting != ''){
                                    $sqlQuery .= " AND tb_radicacion.ln_ue = ".$unitExecuting." ";
                                }

                                if($folioTranfer != ''){
                                    $sqlQuery .= " AND tb_radicacion.folio = ".$folioTranfer." ";
                                }


                                $sqlQueryWhere = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
                                if ($compromisoMes == '1') {
                                    // Si llevar uno validar contra el mes actual
                                    //$sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
                                    $sqlQueryWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
                                }


                                $queryHeaderSQL = "SELECT tb_radicacion.id,tb_radicacion.ln_ur,tb_radicacion.ln_ue,tb_radicacion.folio,tb_radicacion.estatus,tb_radicacion_detalle.presupuesto,tb_radicacion_detalle.autorizado,
                                                   chartdetailsbudgetlog.transno, chartdetailsbudgetlog.type, SUM(chartdetailsbudgetlog.qty) AS QTYR, chartdetailsbudgetlog.period FROM tb_radicacion
                                                   JOIN tb_radicacion_detalle ON tb_radicacion.id = tb_radicacion_detalle.idRadicacion
                                                   JOIN chartdetailsbudgetlog ON tb_radicacion.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 292 AND chartdetailsbudgetlog.qty > 0 AND tb_radicacion_detalle.presupuesto = chartdetailsbudgetlog.cvefrom
                                                   WHERE ".$sqlQuery." ".$sqlQueryWhere."  AND chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."' AND tb_radicacion.estatus = 5 AND tb_radicacion.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                                   GROUP BY tb_radicacion_detalle.presupuesto";


                                // echo $queryHeaderSQL;
                                // echo "\n Query_SQL: ".$queryHeaderSQL;
                                // echo "\n\n\n";


                                $queryHeaderRefunds = "SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.ue_id, tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.status_refund,
                                                        chartdetailsbudgetlog.type, chartdetailsbudgetlog.estatus, SUM(chartdetailsbudgetlog.qty) AS Vqty, chartdetailsbudgetlog.period FROM tb_refunds_notice
                                                        JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.transno = tb_refunds_notice.id
                                                        WHERE chartdetailsbudgetlog.type = 293 AND chartdetailsbudgetlog.estatus = 4 AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."'
                                                        AND tb_refunds_notice.status_refund = 4 AND tb_refunds_notice.folio_invoice_transfer LIKE '%".$folioTranfer."%' AND tb_refunds_notice.ur_id = '".$unitBusiness."' 
                                                        AND tb_refunds_notice.ue_id = '".$unitExecuting."' AND chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."' AND tb_refunds_notice.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                                                        GROUP BY chartdetailsbudgetlog.estatus";


                                $resultQueryQTYSQL = DB_query($queryHeaderSQL, $db, $ErrMsg);

                                if(DB_num_rows($resultQueryQTYSQL) > 0){

                                    while ($rowResultQTYSQL = DB_fetch_array($resultQueryQTYSQL)){
                                        $totalQTY = $rowResultQTYSQL['QTYR'];
                                    }


                                }else{

                                    $totalQTY = 0;
                                }



                                $rQTY = DB_query($queryHeaderRefunds, $db, $ErrMsg);

                                $cont = DB_num_rows($rQTY);

                                if($cont > 0){

                                    while ($rowResultQTY = DB_fetch_array($rQTY)){
                                        $totalG = $rowResultQTY['Vqty'];
                                    }

                                }else{
                                    if($cont <= 0){
                                        $totalG = 0;
                                    }

                                }



                                $disponibleReintegro = $totalQTY - $totalG;

                                // $disponibleReintegro = $totalQTY;

                            }
                        }
                    }

                }
                // REintegros

				$tipoSuficiencia = 0;
				if ($perdidoInfo == $rowPeriods['periodno']) {
					// Mostrar lo seleccionado en la fecha del precomprometido
					$SQL = "SELECT
					SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) AS total,
					purchorderdetails.clavepresupuestal
					FROM purchorders
					JOIN purchorderdetails ON purchorderdetails.orderno = purchorders.orderno
					WHERE purchorders.requisitionno IN (
					SELECT 
					purchorders.requisitionno 
					FROM tb_suficiencias
					JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
					WHERE 
					tb_suficiencias.nu_type = '".$type."'
					AND tb_suficiencias.nu_transno = '".$transno."'
					)
					AND purchorderdetails.clavepresupuestal in ('".$clavepresupuestal."')
					GROUP BY clavepresupuestal";
					$result = DB_query ( $SQL, $db, $ErrMsg );
					while ( $row = DB_fetch_array ( $result ) ) {
						$totalCompra = $row['total'];
					}

					$SQL = "SELECT nu_tipo FROM tb_suficiencias WHERE tb_suficiencias.nu_type = '".$type."' AND tb_suficiencias.nu_transno = '".$transno."' ";
					$result = DB_query ( $SQL, $db, $ErrMsg );
					while ( $row = DB_fetch_array ( $result ) ) {
						$tipoSuficiencia = $row['nu_tipo'];
					}
				}

				if (
					$tipoMovimiento != "Ampliacion" 
					&& $estatusOperacion != '0' 
					&& (($type=='250' && $estatusOperacion != '7') || ($type=='263' && $estatusOperacion != '4'))
					&& $tipoSuficiencia != 2
				) {
					// Si es Reduccion agregar el seleccionado al disponible es para, Adecuacion y Suficiencia
					$disponible = $disponible + $disponibleSel;
				}
			}

			if ($type == '298' && $nu_id_devengado != '' && $nu_idret != 0) {
				// Si es pago de impuestos y trae devengado
				$sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
				$sqlWhereDoc = " AND supptransdetails.period = '".$rowPeriods['periodno']."' ";
                if ($periodoActual == $rowPeriods['periodno'] && $compromisoMes == '0') {
                    // Mes actual es acomulado
                    $sqlWhere = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
                    $sqlWhereDoc = " AND supptransdetails.period <= '".$rowPeriods['periodno']."' ";
                }
				$SQL = "SELECT
				SUM(chartdetailsbudgetlog.qty) as qty,
				chartdetailsbudgetlog.period,
				chartdetailsbudgetlog.cvefrom,
				chartdetailsbudgetlog.ln_ue,
				chartdetailsbudgetlog.nu_id_devengado
				FROM chartdetailsbudgetlog
				WHERE 
				chartdetailsbudgetlog.nu_idret = '".$nu_idret."'
				AND chartdetailsbudgetlog.sn_disponible = 1
				AND chartdetailsbudgetlog.nu_tipo_movimiento = 260
				AND chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'
				AND chartdetailsbudgetlog.nu_id_devengado = '".$nu_id_devengado."'".$sqlWhere;
                $resultTotal = DB_query($SQL, $db, $ErrMsg);
                while ($rowTotal = DB_fetch_array($resultTotal)) {
                    if (!empty($rowTotal['qty']) && $rowTotal['qty'] < 0) {
                        $disponibleRetenciones = abs($rowTotal['qty']);
                    }
                }

                // Restar documentos de pago pendientes o programados
                $documentosPendientes = 0;
                $SQL = "SELECT
				SUM(supptransdetails.price * supptransdetails.qty) as qty
				FROM supptransdetails
				JOIN supptrans ON supptrans.id = supptransdetails.supptransid
				WHERE 
				(supptrans.hold = 0 or supptrans.hold = 1)
				AND supptransdetails.nu_idret = '".$nu_idret."'
				AND supptransdetails.clavepresupuestal = '".$myrow['accountcode']."'
				AND supptransdetails.nu_id_devengado = '".$nu_id_devengado."'".$sqlWhereDoc;
				$resultTotal = DB_query($SQL, $db, $ErrMsg);
                while ($rowTotal = DB_fetch_array($resultTotal)) {
                    if (!empty($rowTotal['qty'])) {
                        $documentosPendientes = abs($rowTotal['qty']);
                    }
                }

                $disponibleRetenciones = $disponibleRetenciones - $documentosPendientes;
			}

			$disponibleDevengado = 0;
			if ($nu_id_devengado != '') {
				// Obtener disponible del devengado para decremento de pago
				$sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
                if ($periodoActual == $rowPeriods['periodno'] && $compromisoMes == '0') {
                    // Mes actual es acomulado
                    $sqlWhere = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
                }
				$SQL = "SELECT
				SUM(chartdetailsbudgetlog.qty) as qty,
				chartdetailsbudgetlog.period,
				chartdetailsbudgetlog.cvefrom,
				chartdetailsbudgetlog.ln_ue,
				chartdetailsbudgetlog.nu_id_devengado
				FROM chartdetailsbudgetlog
				WHERE 
				chartdetailsbudgetlog.nu_idret = '".$nu_idret."'
				AND chartdetailsbudgetlog.sn_disponible = 1
				AND chartdetailsbudgetlog.nu_tipo_movimiento = 260
				AND chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'
				AND chartdetailsbudgetlog.nu_id_devengado = '".$nu_id_devengado."'".$sqlWhere;
                $resultTotal = DB_query($SQL, $db, $ErrMsg);
                while ($rowTotal = DB_fetch_array($resultTotal)) {
                    if (!empty($rowTotal['qty']) && $rowTotal['qty'] < 0) {
                        $disponibleDevengado = abs($rowTotal['qty']);
                    }
                }
			}

            $disponiblePago = 0;
            if ($type == '22' || $type == '304') {
                // total del pago
                $typePago = $type;
                $transnoPago = $transno;
                if ($type == '304') {
                    $SQL = "SELECT nu_type_pago, nu_transno_pago 
                    FROM tb_rectificaciones 
                    WHERE nu_type = '".$type."'
                    AND nu_transno = '".$transno."'";
                    $resultTotal = DB_query($SQL, $db, $ErrMsg);
                    while ($rowTotal = DB_fetch_array($resultTotal)) {
                        $typePago = $rowTotal['nu_type_pago'];
                        $transnoPago = $rowTotal['nu_transno_pago'];
                    }
                }
                // echo "\n **************";
                // echo "\n nu_id_devengado: ".$nu_id_devengado;
                // echo "\n nu_id_compromiso: ".$nu_id_compromiso;
                // echo "\n nu_idret: ".$nu_idret;
                // echo "\n typePago: ".$typePago;
                // echo "\n transnoPago: ".$transnoPago;
                $sqlWhere = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
                if ($periodoActual == $rowPeriods['periodno'] && $compromisoMes == '0') {
                    // Mes actual es acomulado
                    $sqlWhere = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
                }
                $SQL = "SELECT
                SUM(chartdetailsbudgetlog.qty) as qty,
                chartdetailsbudgetlog.period,
                chartdetailsbudgetlog.cvefrom,
                chartdetailsbudgetlog.ln_ue,
                chartdetailsbudgetlog.nu_id_compromiso,
                chartdetailsbudgetlog.nu_id_devengado,
                chartdetailsbudgetlog.nu_idret
                FROM chartdetailsbudgetlog
                WHERE
                chartdetailsbudgetlog.sn_disponible = 1
                AND chartdetailsbudgetlog.nu_tipo_movimiento = 265
                AND chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'
                AND chartdetailsbudgetlog.nu_id_compromiso = '".$nu_id_compromiso."'
                AND chartdetailsbudgetlog.nu_id_devengado = '".$nu_id_devengado."'
                AND chartdetailsbudgetlog.nu_idret = '".$nu_idret."'
                ".$sqlWhere."
                ORDER BY idmov ASC";
                // AND chartdetailsbudgetlog.type = '".$typePago."'
                // AND transno = '".$transnoPago."'
                if ($periodoActual == $rowPeriods['periodno']) {
                	// echo "\n SQL: <pre>".$SQL;
                }
                $resultTotal = DB_query($SQL, $db, $ErrMsg);
                while ($rowTotal = DB_fetch_array($resultTotal)) {
                    if (!empty($rowTotal['qty']) && $rowTotal['qty'] < 0) {
                        $disponiblePago = abs($rowTotal['qty']);
                    }
                }
            }

			// Diponible de la Clave Presupuestal
			$datosPresupuesto[$rowPeriods['mesName']] = number_format($disponible, $_SESSION['DecimalPlaces'], '.', '');//$disponible;
			// Seleccion para Adecuacion y/o Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Sel"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total en Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Suficiencia"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total de Orden de Compra
			$datosPresupuesto[$rowPeriods['mesName']."Compra"] = number_format($totalCompra, $_SESSION['DecimalPlaces'], '.', '');//$totalCompra;
			// Total de disponible acomulado
			$datosPresupuesto[$rowPeriods['mesName']."Acomulado"] = number_format($disponibleAcumulado, $_SESSION['DecimalPlaces'], '.', '');//$disponibleAcumulado;
			$datosPresupuesto[$rowPeriods['mesName']."Compromiso"] = number_format($disponibleCompromiso, $_SESSION['DecimalPlaces'], '.', '');//$disponibleCompromiso;
            $datosPresupuesto[$rowPeriods['mesName']."Reintegro"] = number_format($disponibleReintegro, $_SESSION['DecimalPlaces'], '.', '');//$disponibleReintegro;
            $datosPresupuesto[$rowPeriods['mesName']."Retenciones"] = number_format($disponibleRetenciones, $_SESSION['DecimalPlaces'], '.', '');//$disponibleRetenciones;
            $datosPresupuesto[$rowPeriods['mesName']."Devengado"] = number_format($disponibleDevengado, $_SESSION['DecimalPlaces'], '.', '');//$disponibleDevengado;
           // $datosPresupuesto[$rowPeriods['mesName']."Devengado"] = number_format($disponibleDevengado, $_SESSION['DecimalPlaces'], '.', '');//$disponibleDevengado;
            $datosPresupuesto[$rowPeriods['mesName']."Pago"] = number_format($disponiblePago, $_SESSION['DecimalPlaces'], '.', '');//$disponiblePago;

		}

		if ($datosClave == 1) {
			// Datos de la clave por separado
			$sqlWhere = "";
			if ($datosClaveAdecuacion == 1) {
				$sqlWhere = " AND adecuacion_presupuesto = 1 ";
			}
			$claveSeparada = array();
			$SQL = "SELECT campoPresupuesto, nombre, orden, tabla, campo, nu_programatica, nu_programatica_orden, nu_economica, nu_economica_orden, nu_administrativa, nu_administrativa_orden, nu_relacion_partida, nu_relacion_partida_orden, sn_clave_corta, nu_clave_corta_orden, sn_clave_larga, nu_clave_larga_orden, nu_tam_est_ejer
			FROM budgetConfigClave
			WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' ".$sqlWhere."
			ORDER BY orden ASC";
			$resultClave = DB_query ( $SQL, $db, $ErrMsg );
			while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
				$arr = array(
						"nombreCampo" => $rowClave ['campoPresupuesto'],
						"nombre" => $rowClave ['nombre'],
						"valor" => $myrow [$rowClave ['campoPresupuesto']],
						"sn_clave_corta" => $rowClave ['sn_clave_corta'],
						"nu_clave_corta_orden" => $rowClave ['nu_clave_corta_orden'],
						"sn_clave_larga" => $rowClave ['sn_clave_larga'],
						"nu_clave_larga_orden" => $rowClave ['nu_clave_larga_orden'],
						"nu_tam_est_ejer" => $rowClave ['nu_tam_est_ejer'],
						"tablaValidar" => $rowClave ['tabla'],
						"campoValidar" => $rowClave ['campo'],
						"nu_programatica" => $rowClave ['nu_programatica'],
						"nu_programatica_orden" => $rowClave ['nu_programatica_orden'],
						"nu_economica" => $rowClave ['nu_economica'],
						"nu_economica_orden" => $rowClave ['nu_economica_orden'],
						"nu_administrativa" => $rowClave ['nu_administrativa'],
						"nu_administrativa_orden" => $rowClave ['nu_administrativa_orden'],
						"nu_relacion_partida" => $rowClave ['nu_relacion_partida'],
						"nu_relacion_partida_orden" => $rowClave ['nu_relacion_partida_orden']
					);

				array_push($claveSeparada, $arr);
			}

			$datosPresupuesto['datosClave'] = $claveSeparada;
		}

		$claveCorta = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveCorta)) {
				$claveCorta = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveCorta .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveCorta'] = $claveCorta;

		$claveLarga = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveLarga)) {
				$claveLarga = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveLarga .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveLarga'] = $claveLarga;
        $datosPresupuesto['sequence_siaff'] = $secuenciaSiaff;

    	$info[] = $datosPresupuesto;
    }

    return $info;
}

function fnInfoPresupuestoMinistracion($db, $clavepresupuestal, $period="", $tipo_abono="", $legalid="", $datosClave=0, $datosClaveAdecuacion=0, $tipoAfectacion="", $type="", $transno="", $tipoMovimiento="", $compromisoMes = 0) {
	$datosPresupuesto = array();
	$info = array();

	$SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetbytag.*
            FROM chartdetailsbudgetbytag
            JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE chartdetailsbudgetbytag.accountcode in (".$clavepresupuestal.") ORDER BY chartdetailsbudgetbytag.partida_esp";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	// Datos generales
    	$datosPresupuesto['budgetid'] = $myrow ['budgetid'];
    	$datosPresupuesto['accountcode'] = $myrow ['accountcode'];
    	$datosPresupuesto['legalid'] = $myrow ['legalid'];
    	$datosPresupuesto['tagref'] = $myrow ['tagref'];
    	$datosPresupuesto['budget'] = $myrow ['budget'];
    	$datosPresupuesto['period'] = $myrow ['period'];
    	$datosPresupuesto['modified'] = $myrow ['modified'];
    	$datosPresupuesto['fecha_modificacion'] = $myrow ['fecha_modificacion'];
    	$datosPresupuesto['idClavePresupuesto'] = $myrow ['idClavePresupuesto'];
    	$datosPresupuesto['año'] = $myrow ['anho'];
    	$datosPresupuesto['mes'] = date('m');
    	$datosPresupuesto['tipoMovimiento'] = $tipoMovimiento;
    	$datosPresupuesto['partida_esp'] = $myrow ['partida_esp'];

    	$datosPresupuesto['tipoAfectacion'] = "0";
		if (!empty($tipoAfectacion)) {
	    	$SQL = "SELECT nu_afectacion FROM tb_tipo_afectacion WHERE nu_afectacion = '".($tipoAfectacion)."'";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$datosPresupuesto['tipoAfectacion'] = $row['nu_afectacion'];
			}
		}

    	// Datos de los meses+
    	$sqlWhere = "";
    	if (!empty($period)) {
    		$sqlWhere = " AND periods.periodno = '".$period."' ";
    	}
    	$SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
				FROM periods 
				LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
				WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' ".$sqlWhere." 
				ORDER BY periods.lastdate_in_period asc";
		$resultPeriods = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowPeriods = DB_fetch_array ( $resultPeriods ) ) {
			//Obtener presupuesto por mes
			$disponible = 0;
			$disponibleSel = 0;
			$totalMes = 0;
			// GROUP BY chartdetailsbudgetlog.period, mesName
			$sqlWhereTotal = " AND periods.periodno <= '".$rowPeriods['periodno']."' ";
			if ($compromisoMes == 1) {
				// Validar solo compromiso actual
				$sqlWhereTotal = " AND periods.periodno = '".$rowPeriods['periodno']."' ";
			}
			$SQL = "SELECT 
						(ROUND(CASE WHEN totalModificado.qty <> '' THEN totalModificado.qty ELSE '0' END, 2))
						+
						ROUND(CASE WHEN totalPorLiberar.qty <> '' THEN totalPorLiberar.qty ELSE '0' END, 2) as total,
						ROUND(CASE WHEN totalModificado.qty <> '' THEN totalModificado.qty ELSE '0' END, 2)
						+
						ROUND(CASE WHEN totalPorLiberar.qty <> '' THEN totalPorLiberar.qty ELSE '0' END, 2) as totalMes,
						periods.periodno
					FROM chartdetailsbudgetbytag
					JOIN periods ON DATE_FORMAT(periods.lastdate_in_period, '%Y') = chartdetailsbudgetbytag.anho
					LEFT JOIN (
								SELECT
								SUM(chartdetailsbudgetlog.qty) as qty,
								chartdetailsbudgetlog.cvefrom,
								chartdetailsbudgetlog.period as periodo
								FROM chartdetailsbudgetlog
								WHERE chartdetailsbudgetlog.sn_disponible = '1'
								AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_modificado = 1)
								GROUP BY cvefrom, periodo
								) as totalModificado ON 
								totalModificado.cvefrom = chartdetailsbudgetbytag.accountcode 
								AND totalModificado.periodo = periods.periodno
					LEFT JOIN (
								SELECT
								SUM(chartdetailsbudgetlog.qty) as qty,
								chartdetailsbudgetlog.cvefrom,
								chartdetailsbudgetlog.period as periodo
								FROM chartdetailsbudgetlog
								WHERE chartdetailsbudgetlog.sn_disponible = '1'
								AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_usar_por_liberar = 1)
								GROUP BY cvefrom, periodo
								) as totalPorLiberar ON 
								totalPorLiberar.cvefrom = chartdetailsbudgetbytag.accountcode 
								AND totalPorLiberar.periodo = periods.periodno
					WHERE
					chartdetailsbudgetbytag.accountcode = '".$myrow['accountcode']."' ".$sqlWhereTotal."
					GROUP BY periods.periodno";

			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$disponible = $disponible + $row['total'];
				$totalMes = $totalMes + $row['totalMes'];
			}

			$totalCompra = 0;

			if (!empty($type) and !empty($transno)) {
				$sqlWhere = " AND qty <= 0 "; // Reduccion

				$sqlWhereTotal = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
				if ($compromisoMes == 1) {
					// Validar solo compromiso actual
					$sqlWhereTotal = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
				}

			    $estatusOperacion = "";
			    $perdidoInfo = 0;
				$SQL = "SELECT SUM(abs(chartdetailsbudgetlog.qty)) as total, chartdetailsbudgetlog.estatus, chartdetailsbudgetlog.period
						FROM chartdetailsbudgetlog 
						WHERE 
						chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'
						AND chartdetailsbudgetlog.nu_tipo_movimiento = 266
						AND chartdetailsbudgetlog.type = '".$type."'
						AND chartdetailsbudgetlog.transno = '".$transno."' ".$sqlWhere." ".$sqlWhereTotal;
				
				$result = DB_query ( $SQL, $db, $ErrMsg );
				while ( $row = DB_fetch_array ( $result ) ) {
					$disponibleSel = $row['total'];
					$estatusOperacion = $row['estatus'];
					$perdidoInfo = $row['period'];
				}

				$tipoSuficiencia = 0;

				if ($estatusOperacion != '5') {
					// Si es Reduccion agregar el seleccionado al disponible es para, Adecuacion y Suficiencia
					$disponible = $disponible + $disponibleSel;
				}
			}

			// Diponible de la Clave Presupuestal
			$datosPresupuesto[$rowPeriods['mesName']] = number_format($disponible, $_SESSION['DecimalPlaces'], '.', '');//$disponible;
			// Seleccion para Adecuacion y/o Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Sel"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total en Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Suficiencia"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total de Orden de Compra
			$datosPresupuesto[$rowPeriods['mesName']."Compra"] = number_format($totalCompra, $_SESSION['DecimalPlaces'], '.', '');//$totalCompra;
			$datosPresupuesto[$rowPeriods['mesName']."totalMes"] = number_format($totalMes, $_SESSION['DecimalPlaces'], '.', '');//$totalCompra;
		}

		if ($datosClave == 1) {
			// Datos de la clave por separado
			$sqlWhere = "";
			if ($datosClaveAdecuacion == 1) {
				$sqlWhere = " AND adecuacion_presupuesto = 1 ";
			}
			$claveSeparada = array();
			$SQL = "SELECT campoPresupuesto, nombre, orden, tabla, campo, nu_programatica, nu_programatica_orden, nu_economica, nu_economica_orden, nu_administrativa, nu_administrativa_orden, nu_relacion_partida, nu_relacion_partida_orden, sn_clave_corta, nu_clave_corta_orden, sn_clave_larga, nu_clave_larga_orden
			FROM budgetConfigClave
			WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' ".$sqlWhere."
			ORDER BY orden ASC";
			$resultClave = DB_query ( $SQL, $db, $ErrMsg );
			while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
				$arr = array(
						"nombreCampo" => $rowClave ['campoPresupuesto'],
						"nombre" => $rowClave ['nombre'],
						"valor" => $myrow [$rowClave ['campoPresupuesto']],
						"sn_clave_corta" => $rowClave ['sn_clave_corta'],
						"nu_clave_corta_orden" => $rowClave ['nu_clave_corta_orden'],
						"sn_clave_larga" => $rowClave ['sn_clave_larga'],
						"nu_clave_larga_orden" => $rowClave ['nu_clave_larga_orden'],
						"nu_tam_est_ejer" => $rowClave ['nu_tam_est_ejer'],
						"tablaValidar" => $rowClave ['tabla'],
						"campoValidar" => $rowClave ['campo'],
						"nu_programatica" => $rowClave ['nu_programatica'],
						"nu_programatica_orden" => $rowClave ['nu_programatica_orden'],
						"nu_economica" => $rowClave ['nu_economica'],
						"nu_economica_orden" => $rowClave ['nu_economica_orden'],
						"nu_administrativa" => $rowClave ['nu_administrativa'],
						"nu_administrativa_orden" => $rowClave ['nu_administrativa_orden'],
						"nu_relacion_partida" => $rowClave ['nu_relacion_partida'],
						"nu_relacion_partida_orden" => $rowClave ['nu_relacion_partida_orden']
					);

				array_push($claveSeparada, $arr);
			}

			$datosPresupuesto['datosClave'] = $claveSeparada;
		}

		$claveCorta = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveCorta)) {
				$claveCorta = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveCorta .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveCorta'] = $claveCorta;

		$claveLarga = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveLarga)) {
				$claveLarga = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveLarga .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveLarga'] = $claveLarga;

    	$info[] = $datosPresupuesto;
    }

    return $info;
}


function fnInfoPresupuestoRadicacion($db, $clavepresupuestal, $period="", $tipo_abono="", $legalid="", $datosClave=0, $datosClaveAdecuacion=0, $tipoAfectacion="", $type="", $transno="", $tipoMovimiento="", $compromisoMes = 0) {
	$datosPresupuesto = array();
	$info = array();

	$SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetbytag.*
            FROM chartdetailsbudgetbytag
            JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE chartdetailsbudgetbytag.accountcode in (".$clavepresupuestal.") ORDER BY chartdetailsbudgetbytag.partida_esp";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	// Datos generales
    	$datosPresupuesto['budgetid'] = $myrow ['budgetid'];
    	$datosPresupuesto['accountcode'] = $myrow ['accountcode'];
    	$datosPresupuesto['legalid'] = $myrow ['legalid'];
    	$datosPresupuesto['tagref'] = $myrow ['tagref'];
    	$datosPresupuesto['budget'] = $myrow ['budget'];
    	$datosPresupuesto['period'] = $myrow ['period'];
    	$datosPresupuesto['modified'] = $myrow ['modified'];
    	$datosPresupuesto['fecha_modificacion'] = $myrow ['fecha_modificacion'];
    	$datosPresupuesto['idClavePresupuesto'] = $myrow ['idClavePresupuesto'];
    	$datosPresupuesto['año'] = $myrow ['anho'];
    	$datosPresupuesto['mes'] = date('m');
    	$datosPresupuesto['tipoMovimiento'] = $tipoMovimiento;
    	$datosPresupuesto['partida_esp'] = $myrow ['partida_esp'];

    	$datosPresupuesto['tipoAfectacion'] = "0";
		if (!empty($tipoAfectacion)) {
	    	$SQL = "SELECT nu_afectacion FROM tb_tipo_afectacion WHERE nu_afectacion = '".($tipoAfectacion)."'";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$datosPresupuesto['tipoAfectacion'] = $row['nu_afectacion'];
			}
		}

    	// Datos de los meses+
    	$sqlWhere = "";
    	if (!empty($period)) {
    		$sqlWhere = " AND periods.periodno = '".$period."' ";
    	}
    	$SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
				FROM periods 
				LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
				WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' ".$sqlWhere." 
				ORDER BY periods.lastdate_in_period asc";
		$resultPeriods = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowPeriods = DB_fetch_array ( $resultPeriods ) ) {
			//Obtener presupuesto por mes
			$disponible = 0;
			$disponibleSel = 0;
			// GROUP BY chartdetailsbudgetlog.period, mesName
			$sqlWhereTotal = " AND periods.periodno <= '".$rowPeriods['periodno']."' ";
			if ($compromisoMes == 1) {
				// Validar solo compromiso actual
				$sqlWhereTotal = " AND periods.periodno = '".$rowPeriods['periodno']."' ";
			}
			$SQL = "SELECT
					chartdetailsbudgetlog.period,
					cat_Months.mes as mesName,
					SUM(qty * systypescat.naturalezacontable) as total, 
					periods.lastdate_in_period
					FROM chartdetailsbudgetlog
					LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
					LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
					LEFT JOIN systypescat ON chartdetailsbudgetlog.nu_tipo_movimiento = systypescat.typeid 
					WHERE 
					chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'
					AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where  nu_usar_por_radicar = 1)
					AND chartdetailsbudgetlog.sn_disponible = 1 ".$sqlWhereTotal."
					GROUP BY chartdetailsbudgetlog.cvefrom
					ORDER BY periods.lastdate_in_period ASC";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$disponible = $row['total'];
			}

			$totalCompra = 0;

			if (!empty($type) and !empty($transno)) {
				$sqlWhere = " AND qty <= 0 "; // Reduccion

				$sqlWhereTotal = " AND chartdetailsbudgetlog.period <= '".$rowPeriods['periodno']."' ";
				if ($compromisoMes == 1) {
					// Validar solo compromiso actual
					$sqlWhereTotal = " AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ";
				}
			
			    $estatusOperacion = "";
			    $perdidoInfo = 0;
				$SQL = "SELECT SUM(abs(chartdetailsbudgetlog.qty)) as total, chartdetailsbudgetlog.estatus, chartdetailsbudgetlog.period
						FROM chartdetailsbudgetlog 
						WHERE 
						chartdetailsbudgetlog.cvefrom = '".$myrow['accountcode']."'
						AND chartdetailsbudgetlog.type = '".$type."'
						AND chartdetailsbudgetlog.transno = '".$transno."' ".$sqlWhere.$sqlWhereTotal;
				$result = DB_query ( $SQL, $db, $ErrMsg );
				while ( $row = DB_fetch_array ( $result ) ) {
					$disponibleSel = $row['total'];
					$estatusOperacion = $row['estatus'];
					$perdidoInfo = $row['period'];
				}

				$tipoSuficiencia = 0;

				if ($estatusOperacion != '5') {
					
					$disponible = $disponible + $disponibleSel;
				}
			}

			// Diponible de la Clave Presupuestal
			$datosPresupuesto[$rowPeriods['mesName']] = number_format($disponible, $_SESSION['DecimalPlaces'], '.', '');//$disponible;
			// Seleccion para Adecuacion y/o Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Sel"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total en Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Suficiencia"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total de Orden de Compra
			$datosPresupuesto[$rowPeriods['mesName']."Compra"] = number_format($totalCompra, $_SESSION['DecimalPlaces'], '.', '');//$totalCompra;
		}

		if ($datosClave == 1) {
			// Datos de la clave por separado
			$sqlWhere = "";
			if ($datosClaveAdecuacion == 1) {
				$sqlWhere = " AND adecuacion_presupuesto = 1 ";
			}
			$claveSeparada = array();
			$SQL = "SELECT campoPresupuesto, nombre, orden, tabla, campo, nu_programatica, nu_programatica_orden, nu_economica, nu_economica_orden, nu_administrativa, nu_administrativa_orden, nu_relacion_partida, nu_relacion_partida_orden, sn_clave_corta, nu_clave_corta_orden, sn_clave_larga, nu_clave_larga_orden
			FROM budgetConfigClave
			WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' ".$sqlWhere."
			ORDER BY orden ASC";
			$resultClave = DB_query ( $SQL, $db, $ErrMsg );
			while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
				$arr = array(
						"nombreCampo" => $rowClave ['campoPresupuesto'],
						"nombre" => $rowClave ['nombre'],
						"valor" => $myrow [$rowClave ['campoPresupuesto']],
						"sn_clave_corta" => $rowClave ['sn_clave_corta'],
						"nu_clave_corta_orden" => $rowClave ['nu_clave_corta_orden'],
						"sn_clave_larga" => $rowClave ['sn_clave_larga'],
						"nu_clave_larga_orden" => $rowClave ['nu_clave_larga_orden'],
						"nu_tam_est_ejer" => $rowClave ['nu_tam_est_ejer'],
						"tablaValidar" => $rowClave ['tabla'],
						"campoValidar" => $rowClave ['campo'],
						"nu_programatica" => $rowClave ['nu_programatica'],
						"nu_programatica_orden" => $rowClave ['nu_programatica_orden'],
						"nu_economica" => $rowClave ['nu_economica'],
						"nu_economica_orden" => $rowClave ['nu_economica_orden'],
						"nu_administrativa" => $rowClave ['nu_administrativa'],
						"nu_administrativa_orden" => $rowClave ['nu_administrativa_orden'],
						"nu_relacion_partida" => $rowClave ['nu_relacion_partida'],
						"nu_relacion_partida_orden" => $rowClave ['nu_relacion_partida_orden']
					);

				array_push($claveSeparada, $arr);
			}

			$datosPresupuesto['datosClave'] = $claveSeparada;
		}

		$claveCorta = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveCorta)) {
				$claveCorta = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveCorta .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveCorta'] = $claveCorta;

		$claveLarga = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveLarga)) {
				$claveLarga = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveLarga .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveLarga'] = $claveLarga;

    	$info[] = $datosPresupuesto;
    }

    return $info;
}

function fnInfoPresupuestoRadicado($db, $clavepresupuestal, $period="", $tipo_abono="", $legalid="", $datosClave=0, $datosClaveAdecuacion=0, $tipoAfectacion="", $type="", $transno="", $tipoMovimiento="") {
	$datosPresupuesto = array();
	$info = array();

	$SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetbytag.*
            FROM chartdetailsbudgetbytag
            JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE chartdetailsbudgetbytag.accountcode = '".$clavepresupuestal."'";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	// Datos generales
    	$datosPresupuesto['budgetid'] = $myrow ['budgetid'];
    	$datosPresupuesto['accountcode'] = $myrow ['accountcode'];
    	$datosPresupuesto['legalid'] = $myrow ['legalid'];
    	$datosPresupuesto['tagref'] = $myrow ['tagref'];
    	$datosPresupuesto['budget'] = $myrow ['budget'];
    	$datosPresupuesto['period'] = $myrow ['period'];
    	$datosPresupuesto['modified'] = $myrow ['modified'];
    	$datosPresupuesto['fecha_modificacion'] = $myrow ['fecha_modificacion'];
    	$datosPresupuesto['idClavePresupuesto'] = $myrow ['idClavePresupuesto'];
    	$datosPresupuesto['año'] = $myrow ['anho'];
    	$datosPresupuesto['mes'] = date('m');
    	$datosPresupuesto['tipoMovimiento'] = $tipoMovimiento;
    	$datosPresupuesto['partida_esp'] = $myrow ['partida_esp'];

    	$datosPresupuesto['tipoAfectacion'] = "0";
		if (!empty($tipoAfectacion)) {
	    	$SQL = "SELECT nu_afectacion FROM tb_tipo_afectacion WHERE nu_afectacion = '".($tipoAfectacion)."'";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$datosPresupuesto['tipoAfectacion'] = $row['nu_afectacion'];
			}
		}

    	// Datos de los meses+
    	$sqlWhere = "";
    	if (!empty($period)) {
    		$sqlWhere = " AND periods.periodno = '".$period."' ";
    	}
    	$SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
				FROM periods 
				LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
				WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' ".$sqlWhere." 
				ORDER BY periods.lastdate_in_period asc";
		$resultPeriods = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowPeriods = DB_fetch_array ( $resultPeriods ) ) {
			//Obtener presupuesto por mes
			$disponible = 0;
			$disponibleSel = 0;
			// GROUP BY chartdetailsbudgetlog.period, mesName
			$SQL = "SELECT
					chartdetailsbudgetlog.period,
					cat_Months.mes as mesName,
					SUM(qty) as total, 
					periods.lastdate_in_period
					FROM chartdetailsbudgetlog
					LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
					LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
					WHERE 
					chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."'
					AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible_radicado = 1)
					AND chartdetailsbudgetlog.sn_disponible = 1
					AND periods.periodno = '".$rowPeriods['periodno']."'
					GROUP BY chartdetailsbudgetlog.period, mesName, periods.lastdate_in_period
					ORDER BY periods.lastdate_in_period ASC";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$disponible = $row['total'];
			}

			$totalCompra = 0;

			if (!empty($type) and !empty($transno) and !empty($tipoMovimiento)) {
				$sqlWhere = " AND qty < 0 "; // Reduccion
			    if ($tipoMovimiento == "Ampliacion") {
			        $sqlWhere = " AND qty > 0 "; // Ampliacion
			    }

			    if ($type == '250') {
			    	// Si es adecuacion presupuestal
			    	if ($tipoMovimiento != "Ampliacion") {
			    		$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 256 "; // Reduccion
			    	} else if ($tipoMovimiento == "Ampliacion") {
			    		$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 257 "; // Ampliacion
			    	}
			    }

			    if ($type == '263') {
			    	// Si es suficiencia presupuestaria
			    	$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 263 "; // Reduccion
			    }

			    $estatusOperacion = "";
				$SQL = "SELECT abs(chartdetailsbudgetlog.qty) as total, chartdetailsbudgetlog.estatus
						FROM chartdetailsbudgetlog 
						WHERE 
						chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."'
						AND chartdetailsbudgetlog.type = '".$type."'
						AND chartdetailsbudgetlog.transno = '".$transno."' 
						AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ".$sqlWhere;
				$result = DB_query ( $SQL, $db, $ErrMsg );
				while ( $row = DB_fetch_array ( $result ) ) {
					$disponibleSel = $row['total'];
					$estatusOperacion = $row['estatus'];
				}

				$SQL = "
				SELECT 
				SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) AS total,
				purchorderdetails.clavepresupuestal
				-- purchorderdetails.*
				FROM purchorderdetails
				JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
				JOIN tb_suficiencias ON tb_suficiencias.sn_orderno = purchorders.orderno
				JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_suficiencias.nu_type and chartdetailsbudgetlog.transno = tb_suficiencias.nu_transno
				WHERE 
				tb_suficiencias.nu_type = '".$type."'
				AND tb_suficiencias.nu_transno = '".$transno."'
				AND purchorderdetails.clavepresupuestal = '".$clavepresupuestal."'
				AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."'
				AND chartdetailsbudgetlog.sn_disponible = 1
				GROUP BY clavepresupuestal
				";
				$result = DB_query ( $SQL, $db, $ErrMsg );
				while ( $row = DB_fetch_array ( $result ) ) {
					$totalCompra = $row['total'];
				}

				if (
					$tipoMovimiento != "Ampliacion" 
					&& $estatusOperacion != '0' 
					&& (($type=='250' && $estatusOperacion != '7') || ($type=='263' && $estatusOperacion != '4'))
				) {
					// Si es Reduccion agregar el seleccionado al disponible es para, Adecuacion y Suficiencia
					$disponible = $disponible + $disponibleSel;
				}
			}

			// Diponible de la Clave Presupuestal
			$datosPresupuesto[$rowPeriods['mesName']] = number_format($disponible, $_SESSION['DecimalPlaces'], '.', '');//$disponible;
			// Seleccion para Adecuacion y/o Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Sel"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total en Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Suficiencia"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total de Orden de Compra
			$datosPresupuesto[$rowPeriods['mesName']."Compra"] = number_format($totalCompra, $_SESSION['DecimalPlaces'], '.', '');//$totalCompra;
		}

		if ($datosClave == 1) {
			// Datos de la clave por separado
			$sqlWhere = "";
			if ($datosClaveAdecuacion == 1) {
				$sqlWhere = " AND adecuacion_presupuesto = 1 ";
			}
			$claveSeparada = array();
			$SQL = "SELECT campoPresupuesto, nombre, orden
					FROM budgetConfigClave
					WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' ".$sqlWhere."
					ORDER BY orden ASC";
			$resultClave = DB_query ( $SQL, $db, $ErrMsg );
			while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
				$arr = array(
						"nombre" => $rowClave ['nombre'],
						"valor" => $myrow [$rowClave ['campoPresupuesto']]
					);

				array_push($claveSeparada, $arr);
			}

			$datosPresupuesto['datosClave'] = $claveSeparada;
		}

		$claveCorta = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveCorta)) {
				$claveCorta = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveCorta .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveCorta'] = $claveCorta;

		$claveLarga = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveLarga)) {
				$claveLarga = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveLarga .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveLarga'] = $claveLarga;

    	$info[] = $datosPresupuesto;
    }

    return $info;
}

function fnInfoPresupuestoIngreso($db, $clavepresupuestal, $period="", $tipo_abono="", $legalid="", $datosClave=0, $datosClaveAdecuacion=0, $tipoAfectacion="", $type="", $transno="", $tipoMovimiento="") {
	$datosPresupuesto = array();
	$info = array();

	$SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetbytag.*
            FROM chartdetailsbudgetbytag
            JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE chartdetailsbudgetbytag.accountcode = '".$clavepresupuestal."'";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	// Datos generales
    	$datosPresupuesto['budgetid'] = $myrow ['budgetid'];
    	$datosPresupuesto['accountcode'] = $myrow ['accountcode'];
    	$datosPresupuesto['legalid'] = $myrow ['legalid'];
    	$datosPresupuesto['tagref'] = $myrow ['tagref'];
    	$datosPresupuesto['budget'] = $myrow ['budget'];
    	$datosPresupuesto['period'] = $myrow ['period'];
    	$datosPresupuesto['modified'] = $myrow ['modified'];
    	$datosPresupuesto['fecha_modificacion'] = $myrow ['fecha_modificacion'];
    	$datosPresupuesto['1idClavePresupuesto'] = $myrow ['idClavePresupuesto'];
    	$datosPresupuesto['año'] = $myrow ['anho'];
    	$datosPresupuesto['mes'] = date('m');
    	$datosPresupuesto['tipoMovimiento'] = $tipoMovimiento;
    	$datosPresupuesto['partida_esp'] = $myrow ['partida_esp'];

    	$datosPresupuesto['tipoAfectacion'] = "0";
		if (!empty($tipoAfectacion)) {
	    	$SQL = "SELECT nu_afectacion FROM tb_tipo_afectacion WHERE nu_afectacion = '".($tipoAfectacion)."'";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$datosPresupuesto['tipoAfectacion'] = $row['nu_afectacion'];
			}
		}

    	// Datos de los meses+
    	$sqlWhere = "";
    	if (!empty($period)) {
    		$sqlWhere = " AND periods.periodno = '".$period."' ";
    	}
    	$SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
				FROM periods 
				LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
				WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' ".$sqlWhere." 
				ORDER BY periods.lastdate_in_period asc";
		$resultPeriods = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowPeriods = DB_fetch_array ( $resultPeriods ) ) {
			//Obtener presupuesto por mes
			$disponible = 0;
			$disponibleSel = 0;
			// GROUP BY chartdetailsbudgetlog.period, mesName
			$SQL = "SELECT
					chartdetailsbudgetlog.period,
					cat_Months.mes as mesName,
					SUM(qty) as total, 
					periods.lastdate_in_period
					FROM chartdetailsbudgetlog
					LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
					LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
					WHERE 
					chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."'
					AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto_ingreso = 1 AND nu_usar_disponible_ingreso = 1)
					AND chartdetailsbudgetlog.sn_disponible = 1
					AND periods.periodno = '".$rowPeriods['periodno']."'
					GROUP BY chartdetailsbudgetlog.period, mesName, periods.lastdate_in_period
					ORDER BY periods.lastdate_in_period ASC";
			$result = DB_query ( $SQL, $db, $ErrMsg );
			while ( $row = DB_fetch_array ( $result ) ) {
				$disponible = $row['total'];
			}

			$totalCompra = 0;

			if (!empty($type) and !empty($transno) and !empty($tipoMovimiento)) {
				$sqlWhere = " AND qty < 0 "; // Reduccion
			    if ($tipoMovimiento == "Ampliacion") {
			        $sqlWhere = " AND qty > 0 "; // Ampliacion
			    }

			    if ($type == '250') {
			    	// Si es adecuacion presupuestal
			    	if ($tipoMovimiento != "Ampliacion") {
			    		$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 256 "; // Reduccion
			    	} else if ($tipoMovimiento == "Ampliacion") {
			    		$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 257 "; // Ampliacion
			    	}
			    }

			    if ($type == '263') {
			    	// Si es suficiencia presupuestaria
			    	$sqlWhere .= " AND chartdetailsbudgetlog.nu_tipo_movimiento = 263 "; // Reduccion
			    }

			    $estatusOperacion = "";
				$SQL = "SELECT abs(chartdetailsbudgetlog.qty) as total, chartdetailsbudgetlog.estatus
						FROM chartdetailsbudgetlog 
						WHERE 
						chartdetailsbudgetlog.cvefrom = '".$clavepresupuestal."'
						AND chartdetailsbudgetlog.type = '".$type."'
						AND chartdetailsbudgetlog.transno = '".$transno."' 
						AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."' ".$sqlWhere;
				$result = DB_query ( $SQL, $db, $ErrMsg );
				while ( $row = DB_fetch_array ( $result ) ) {
					$disponibleSel = $row['total'];
					$estatusOperacion = $row['estatus'];
				}

				$SQL = "
				SELECT 
				SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) AS total,
				purchorderdetails.clavepresupuestal
				-- purchorderdetails.*
				FROM purchorderdetails
				JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
				JOIN tb_suficiencias ON tb_suficiencias.sn_orderno = purchorders.orderno
				JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_suficiencias.nu_type and chartdetailsbudgetlog.transno = tb_suficiencias.nu_transno
				WHERE 
				tb_suficiencias.nu_type = '".$type."'
				AND tb_suficiencias.nu_transno = '".$transno."'
				AND purchorderdetails.clavepresupuestal = '".$clavepresupuestal."'
				AND chartdetailsbudgetlog.period = '".$rowPeriods['periodno']."'
				AND chartdetailsbudgetlog.sn_disponible = 1
				GROUP BY clavepresupuestal
				";
				$result = DB_query ( $SQL, $db, $ErrMsg );
				while ( $row = DB_fetch_array ( $result ) ) {
					$totalCompra = $row['total'];
				}

				if (
					$tipoMovimiento != "Ampliacion" 
					&& $estatusOperacion != '0' 
					&& (($type=='250' && $estatusOperacion != '7') || ($type=='263' && $estatusOperacion != '4'))
				) {
					// Si es Reduccion agregar el seleccionado al disponible es para, Adecuacion y Suficiencia
					$disponible = $disponible + $disponibleSel;
				}
			}

			// Diponible de la Clave Presupuestal
			$datosPresupuesto[$rowPeriods['mesName']] = number_format($disponible, $_SESSION['DecimalPlaces'], '.', '');//$disponible;
			// Seleccion para Adecuacion y/o Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Sel"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total en Suficiencia
			$datosPresupuesto[$rowPeriods['mesName']."Suficiencia"] = number_format($disponibleSel, $_SESSION['DecimalPlaces'], '.', '');//$disponibleSel;
			// Total de Orden de Compra
			$datosPresupuesto[$rowPeriods['mesName']."Compra"] = number_format($totalCompra, $_SESSION['DecimalPlaces'], '.', '');//$totalCompra;
		}

		if ($datosClave == 1) {
			// Datos de la clave por separado
			$sqlWhere = "";
			if ($datosClaveAdecuacion == 1) {
				$sqlWhere = " AND adecuacion_presupuesto = 1 ";
			}
			$claveSeparada = array();
			$SQL = "SELECT campoPresupuesto, nombre, orden
					FROM budgetConfigClave
					WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' ".$sqlWhere."
					ORDER BY orden ASC";
			$resultClave = DB_query ( $SQL, $db, $ErrMsg );
			while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
				$arr = array(
						"nombre" => $rowClave ['nombre'],
						"valor" => $myrow [$rowClave ['campoPresupuesto']]
					);

				array_push($claveSeparada, $arr);
			}

			$datosPresupuesto['datosClave'] = $claveSeparada;
		}

		$claveCorta = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveCorta)) {
				$claveCorta = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveCorta .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveCorta'] = $claveCorta;

		$claveLarga = "";
		$SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			if (empty($claveLarga)) {
				$claveLarga = $myrow [$rowClave ['campoPresupuesto']];
			}else{
				$claveLarga .= "-".$myrow [$rowClave ['campoPresupuesto']];
			}
		}

		$datosPresupuesto['claveLarga'] = $claveLarga;

    	$info[] = $datosPresupuesto;
    }

    return $info;
}

/**
 * Función para generar los registros presupuestales por log acomulado, toma en cuenta el disponible o ministración
 * @param  [type]  $db                   Base de datos
 * @param  [type]  $type                 Tipo de operación
 * @param  [type]  $transno              Folio de la operación
 * @param  [type]  $tagref               Unidad responsable
 * @param  [type]  $clave                Clave presupuestal
 * @param  [type]  $periodo              Periodo contable
 * @param  integer $cantidad             Cantidad total acomulada (Positiva o Negativa), depende de la operación
 * @param  integer $tipoMovimiento       Tipo de movimiento presupuestal
 * @param  string  $partida_esp          Partida especifica
 * @param  string  $description          Descripción del log
 * @param  integer $sn_disponible        Si se toma en cuenta el registro 1 si no 0 es como captura
 * @param  string  $statusid             Estatus del registro
 * @param  integer $sn_funcion_id        Función de la operación
 * @param  string  $ue                   Unidad ejecutora
 * @param  string  $inicio               Donde comienza a hacer la operacion, Mes actual hacia atras DESC si no ASC
 * @param  string  $movimientoDisponible Que tomar en cuenta 'disponible' o 'ministracion'
 * @param  string  $movimientoTipo       Tipo de operacion para validacion
 * @return [type]                        [description]
 */
function fnInsertPresupuestoLogAcomulado($db, $type, $transno, $tagref, $clave, $periodo, $cantidad = 0, $tipoMovimiento = 0, $partida_esp = "", $description = "", $sn_disponible = 1, $statusid = "", $sn_funcion_id = 0, $ue = "", $inicio = 'DESC', $movimientoDisponible = 'disponible', $movimientoTipo = '', $movimientoAmpRed = '', $compromisoMes = 0, $noDevengado = "", $noCompromiso = "", $noRetencion = 0, $foliotransfer="", $typeRefund="", $secuenciaSIAFF = 0, $folioTranfer='',$typeSearchRefund = 0,$typesPayments = 0)
{
    $respuesta = true;

    // $inicio = ASC o DESC, depende de donde inicie
    
    $cantidadVal = abs($cantidad);

   /* echo "\n ".$type;
    echo "\n ".$transno;
    echo "\n ".$tagref;
    echo "\n ".$clave;
    echo "\n ".$periodo;
    echo "\n ".$cantidad;
    echo "\n ".$tipoMovimiento;
    echo "\n ".$partida_esp;
    echo "\n ".$description;
    echo "\n ".$sn_disponible;
    echo "\n ".$statusid;
    echo "\n ".$sn_funcion_id;
    echo "\n ".$ue;
    echo "\n ".$inicio;
    echo "\n ".$movimientoDisponible;
    echo "\n ".$movimientoTipo;
    echo "\n ".$movimientoAmpRed;
    echo "\n ".$compromisoMes;
    echo "\n ".$noDevengado;
    echo "\n ".$noCompromiso;
    echo "\n Retencion".$noRetencion;
    echo "\n ".$foliotransfer;
    echo "\n ".$typeRefund;
    echo "\n ".$secuenciaSIAFF;
    echo "\n\n";

    exit();*/

    // Obtener periodos para comenzar la separacion de montos
    $SQL = "SELECT periods.periodno, cat_Months.mes FROM chartdetailsbudgetbytag
    JOIN periods ON YEAR(periods.lastdate_in_period) = chartdetailsbudgetbytag.anho
    JOIN cat_Months ON u_mes = MONTH(periods.lastdate_in_period)
    WHERE chartdetailsbudgetbytag.accountcode = '".$clave."'
    AND periods.periodno <= '".$periodo."'
    ORDER BY periods.periodno ".$inicio;

    $ErrMsg = "No se obtuvieron los periodos de la clave ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        // Validar y generar registros en log
        if ($movimientoDisponible == 'disponible') {
            // Usar el diponible
            $disponible = fnInfoPresupuesto($db, $clave, $myrow['periodno'], '', '', 0, 0, '', $type, $transno, $movimientoAmpRed, $foliotransfer, $tagref, $ue, $compromisoMes, $noDevengado, $noCompromiso, $noRetencion, $typeRefund,'','','',0,$typeSearchRefund,$typesPayments);

        } else if ($movimientoDisponible == 'ministracion') {
            // Usar la ministracion
            $disponible = fnInfoPresupuestoMinistracion($db, "'".$clave."'", $myrow['periodno'], "", "", 0, 0, "", "", "", "", 1);
        } else if ($movimientoDisponible == 'radicacion') {
            // Usar la radicado
            // $disponible = fnInfoPresupuestoRadicacion($db, "'".$clave."'", $myrow['periodno']);
            $disponible = fnInfoPresupuestoRadicacion($db, "'".$clave."'", $myrow['periodno'], "", "", 0, 0, "", "", "", "", 1);
        }

        if ($_SESSION['UserID'] == 'user.autorizador') {
			// echo "\n Tipo: ". $type;
			// echo "\n Transno: ".$transno;
			// echo "\n\n";
			// print_r($disponible);
			// exit();
        }
        
        foreach ($disponible as $dispo) {
            if (abs($cantidadVal) == 0) {
                // Terminar operaciones
                break;
            }


			/*  var_dump($disponible);
			echo "\n ".$myrow['mes'].": ".$dispo[$myrow['mes'].$movimientoTipo];
			echo "\n movimientoTipo: ".$movimientoTipo;
			echo "\n compromisoMes: ".$compromisoMes;
			echo "\n Periodo: ". $myrow['periodno'];
			echo "\n Clave: ". $clave;
			echo "\n Tipo: ". $type;
			echo "\n Transno: ".$transno;
			echo "\n Movimiento: ".$movimientoAmpRed;
			echo "\n FolioTranfer: ".$foliotransfer;
			echo "\n Tagref: ".$tagref;
			echo "\n Tipo_reintegro: ".$typeRefund;
			echo "\n\n";
			*/
            
            if ($dispo[$myrow['mes'].$movimientoTipo] > abs(0)) {
                // Si tiene disponible registrar en el log
                $cantidadRegistro = 0;
                if ($dispo[$myrow['mes'].$movimientoTipo] >= abs($cantidadVal)) {
                    // Registrar cantidad y ya que se tiene mas disponible
                    $cantidadRegistro = abs($cantidadVal);
                } else if (abs($cantidadVal) >= $dispo[$myrow['mes'].$movimientoTipo]) {
                    // Registrar disponible ya que es mayor la cantidad
                    $cantidadRegistro = abs($dispo[$myrow['mes'].$movimientoTipo]);
                }

                $cantidadVal = abs($cantidadVal) - abs($cantidadRegistro);

                if ($cantidad < 0) {
                    // Reigstro en negativo
                    $cantidadRegistro = $cantidadRegistro * -1;
                }

                $agregoLog = fnInsertPresupuestoLog($db, $type, $transno, $tagref, $clave, $myrow['periodno'], $cantidadRegistro, $tipoMovimiento, $partida_esp, $description, $sn_disponible, $statusid, $sn_funcion_id, $ue, $noCompromiso, $noDevengado, $noRetencion, $secuenciaSIAFF, $folioTranfer);

                if (!$agregoLog) {
                    $respuesta = false;
                }
            }
        }

        if (abs($cantidadVal) == 0) {
            // Terminar operaciones
            break;
        }
    }

    return $respuesta;
}

/**
 * Función que genera los movimientos contrarios para registros del log presupuestal
 * @param  string $db      Conexíon a la base de datos
 * @param  string $type    Tipo de Documento
 * @param  string $transno Número de Operación
 * @param  string $typeNuevo Tipo de Documento de los movimientos contrarios
 * @param  string $transnoNuevo Número de Operación de los movimientos contrarios
 * @return boolean Regresa true al generar movimientos
 */
function fnInsertPresupuestoLogMovContrarios($db, $type, $transno, $typeNuevo = 0, $transnoNuevo = 0) {
	// Función que genera los movimientos contrarios del registro
	$typeAct = $type;
	$transnoAct = $transno;

	if ($typeNuevo != 0 && $transnoNuevo != 0) {
		// Agregar movimientos contrarios con otro tipo y folio diferentes
		$typeAct = $typeNuevo;
		$transnoAct = $transnoNuevo;
	}

	$SQL = "INSERT INTO chartdetailsbudgetlog
	(
	datemov, userid, qty, description, cvefrom, cveto, folio, type, transno, numero_oficio, 
	estatus, fecha_captura, account, tagref, period, nu_afectacion, sn_adecuacion, partida_esp, sn_disponible, dtm_aplicacion, 
	txt_dictamen_upi, txt_control_interno, txt_justificacion, nu_tipo_solicitud, nu_centro_contable, nu_tipo_reg, nu_cat_jusr, nu_tipo_movimiento, nu_r23, txt_proceso_sicop, 
	txt_folio_map, sn_funcion_id, sn_tagref_receptora, ln_ue, sn_reglas_validadas, nu_id_compromiso, nu_id_devengado, nu_idret
	)
	SELECT 
	NOW(), '".$_SESSION['UserID']."', (qty * -1), description, cvefrom, cveto, folio, '".$typeAct."', '".$transnoAct."', numero_oficio, 
	estatus, NOW(), account, tagref, period, nu_afectacion, sn_adecuacion, partida_esp, sn_disponible, NOW(), 
	txt_dictamen_upi, txt_control_interno, txt_justificacion, nu_tipo_solicitud, nu_centro_contable, nu_tipo_reg, nu_cat_jusr, nu_tipo_movimiento, nu_r23, txt_proceso_sicop, 
	txt_folio_map, sn_funcion_id, sn_tagref_receptora, ln_ue, sn_reglas_validadas, nu_id_compromiso, nu_id_devengado, nu_idret
	FROM chartdetailsbudgetlog 
	WHERE 
	chartdetailsbudgetlog.type = '".$type."'
	AND chartdetailsbudgetlog.transno = '".$transno."'";
	$ErrMsg = "No se genero los movimientos contrarios del presupuesto";
	$transResult = DB_query ( $SQL, $db, $ErrMsg );

	return true;
}

/**
 * Función que genera los movimientos contrarios para registros de una póliza contable
 * @param  string $db      Conexíon a la base de datos
 * @param  string $type    Tipo de Documento
 * @param  string $transno Número de Operación
 * @param  string $typeNuevo Tipo de Documento de los movimientos contrarios
 * @param  string $transnoNuevo Número de Operación de los movimientos contrarios
 * @return boolean Regresa true al generar movimientos
 */
function fnInsertPolizaMovContrarios($db, $type, $transno, $typeNuevo = 0, $transnoNuevo = 0, $folioPolizaUe = 0) {
	// Función que genera los movimientos contrarios del registro
	$typeAct = $type;
	$transnoAct = $transno;

	if ($typeNuevo != 0 && $transnoNuevo != 0) {
		// Agregar movimientos contrarios con otro tipo y folio diferentes
		$typeAct = $typeNuevo;
		$transnoAct = $transnoNuevo;
	}

	$PeriodNo = GetPeriod(date('d/m/Y'), $db);

	$SQL = "INSERT INTO gltrans
	(
	type, typeno, chequeno, trandate, periodno, account, narrative, amount, posted, jobref, tag, lasttrandate, amountpaid, branchno, userid, rate, complemento, cat_cuenta, loccode, flagdiot, typediot, debtorno, dolares, percentpaid, payapplies, typepaid, suppno, grns, purchno, stockid, qty, standardcost, lastusermod, lastdatemod, dateadded, cuentadestino, bancodestino, rfcdestino, clavepresupuestal, uuid, supplier, cuentabanco, descripcion, ln_ue, nu_folio_ue
	)
	SELECT 
	'".$typeAct."', '".$transnoAct."', chequeno, NOW(), '".$PeriodNo."', account, narrative, (amount * -1), posted, jobref, tag, NOW(), amountpaid, branchno, '".$_SESSION['UserID']."', rate, complemento, cat_cuenta, loccode, flagdiot, typediot, debtorno, dolares, percentpaid, payapplies, typepaid, suppno, grns, purchno, stockid, qty, standardcost, lastusermod, NOW(), NOW(), cuentadestino, bancodestino, rfcdestino, clavepresupuestal, uuid, supplier, cuentabanco, descripcion, ln_ue, '".$folioPolizaUe."'
	FROM gltrans 
	WHERE 
	gltrans.type = '".$type."'
	AND gltrans.typeno = '".$transno."'";
	$ErrMsg = "No se genero los movimientos contrarios de la póliza";
	$transResult = DB_query ( $SQL, $db, $ErrMsg );

	return true;
}

function fnInsertPresupuestoLog($db, $type, $transno, $tagref, $clave, $periodo, $cantidad=0, $tipoMovimiento=0, $partida_esp="", $description="", $sn_disponible = 1, $statusid = "", $sn_funcion_id = 0, $ue="", $nu_id_compromiso = "", $nu_id_devengado = "", $nu_idret = 0, $seqSIAFF = 0, $FolioTranfer = "")
{

    /*echo "\n ".$type;
    echo "\n ".$transno;
    echo "\n ".$tagref;
    echo "\n ".$clave;
    echo "\n ".$periodo;
    echo "\n ".$cantidad;
    echo "\n ".$tipoMovimiento;
    echo "\n ".$partida_esp;
    echo "\n ".$description;
    echo "\n ".$sn_disponible;
    echo "\n ".$statusid;
    echo "\n ".$sn_funcion_id;
    echo "\n ".$ue;
    echo "\n ".$nu_id_compromiso;
    echo "\n ".$nu_id_devengado;
    echo "\n ".$nu_idret;
    echo "\n ".$seqSIAFF;
    echo "\n ".$FolioTranfer;
    echo "\n\n";

    exit();*/

    if (empty(trim($partida_esp))) {
		$SQL = "SELECT partida_esp FROM chartdetailsbudgetbytag WHERE accountcode = '".$clave."'";
		$resultClave = DB_query ( $SQL, $db, $ErrMsg );
		while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
			$partida_esp = $rowClave ['partida_esp'];
		}
	}
	if (empty(trim($partida_esp))) {
		$partida_esp = 0;
	}

	if (empty($ue)) {
		// Si no trae ue
		$ue = fnObtenerUnidadEjecutoraClave($db, $clave);
	}

    // Agregar datos
    $SQL = "INSERT INTO chartdetailsbudgetlog
            (datemov, 
            userid, 
            qty, 
            description, 
            cvefrom,
            folio, 
            type, 
            transno, 
            fecha_captura, 
            dtm_aplicacion, 
            tagref,
            period,
            partida_esp,
            nu_tipo_movimiento,
            sn_disponible,
            estatus,
            sn_funcion_id,
            ln_ue,
            nu_id_compromiso,
            nu_id_devengado,
            nu_idret,
            nu_secuencia_siaff)
            VALUES
            (NOW(), 
            '".$_SESSION['UserID']."', 
            '".$cantidad."', 
            '".$description."', 
            '".$clave."',
            '".$FolioTranfer."', 
            '".$type."', 
            '".$transno."', 
            NOW(), 
            NOW(), 
            '".$tagref."',
            '".$periodo."',
            '".$partida_esp."',
        	'".$tipoMovimiento."',
        	'".$sn_disponible."',
        	'".$statusid."',
        	".$sn_funcion_id.",
        	'".$ue."',
        	'".$nu_id_compromiso."',
        	'".$nu_id_devengado."',
        	'".$nu_idret."',
        	 ".$seqSIAFF."
        )";



    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);



    return true;
}

/**
 * Función para validar si se puede recibir la orden
 * @param  [type] $db      Concexión a la base de datos
 * @param  [type] $orderno Orden de Compra
 * @return [type]          [description]
 */
function fnValidarRecepcionOC($db, $orderno)
{
    // Recibir
    $SQL = "SELECT 
    purchorders.status,
    purchorderdetails.quantityord,
    purchorderdetails.qtyinvoiced,
    purchorderdetails.completed
    FROM purchorders
    INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3) AND purchorderdetails.completed = 0
    WHERE purchorders.orderno = '".$orderno."'
    AND (purchorders.status = 'Authorised' || purchorders.status = 'Pending')";
    $ErrMsg = "No se obtuvieron los registros del Orden ".$orderno;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        return false;
    } else {
        return true;
    }
}

/**
 * Función para validar si se puede facturar la orden
 * @param  [type] $db      Concexión a la base de datos
 * @param  [type] $orderno Orden de Compra
 * @return [type]          [description]
 */
function fnValidarFacturaOC($db, $orderno)
{
    $SQL = "SELECT 
    purchorders.status,
    purchorderdetails.quantityord,
    purchorderdetails.qtyinvoiced,
    purchorderdetails.completed
    FROM purchorders
    INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3) AND purchorderdetails.completed = 1
    WHERE purchorders.orderno = '".$orderno."'
    AND purchorders.status = 'Completed'
    AND purchorderdetails.quantityord <> purchorderdetails.qtyinvoiced";
    $ErrMsg = "No se obtuvieron los registros del Orden ".$orderno;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        return false;
    } else {
        return true;
    }
}

function fnInsertPruebasDocumentos($db, $nombreInterno, $tipoArchivo, $linkDescarga, $type, $funcion, $transno, $nombreComponente, $eliminar=1,$soyLayout=1) {
	$SQL = "SELECT txt_url FROM tb_archivos 
	WHERE nu_tipo_sys = '".$type."' AND nu_trasnno = '".$transno."' AND nu_funcion = '".$funcion."' AND ind_permiso_active = '".$eliminar."' AND txt_url = '".$linkDescarga."'";
	$ErrMsg = "No se agrego la Información del Layout";
    $transResult = DB_query ( $SQL, $db, $ErrMsg );
    if (DB_num_rows($transResult)==0) {
    	$SQL = "UPDATE tb_archivos SET 
    	ind_active = '0'
    	WHERE nu_tipo_sys = '".$type."' AND nu_trasnno = '".$transno."' AND nu_funcion = '".$funcion."' AND ind_permiso_active = '".$eliminar."'";
		$ErrMsg = "No se agrego la Información del Layout";
		$transResult = DB_query ( $SQL, $db, $ErrMsg );

		$SQL = "INSERT INTO `tb_archivos` (
		`ln_userid`, `sn_tipo`, `txt_descripcion`, `txt_url`, `nu_tipo_sys`, 
		`nu_funcion`, `nu_trasnno`, `ln_nombre`, `ind_permiso_active`, `ind_active`, 
		`ln_nombre_interno_archivo`,`ind_es_layout`)
		VALUES
		('".$_SESSION['UserID']."', '".$tipoArchivo."', NULL, '".$linkDescarga."', '".$type."', 
		'".$funcion."', '".$transno."', '".$nombreComponente."', 0, 1,
		'".$nombreInterno."',
		'".$soyLayout."')";
		$ErrMsg = "No se agrego la Información del Layout";
		$transResult = DB_query ( $SQL, $db, $ErrMsg );
    }else{
    	$SQL = "UPDATE tb_archivos SET 
    	ind_active = '0'
    	WHERE nu_tipo_sys = '".$type."' AND nu_trasnno = '".$transno."' AND nu_funcion = '".$funcion."' AND ind_permiso_active = '".$eliminar."' AND txt_url != '".$linkDescarga."'";
		$ErrMsg = "No se agrego la Información del Layout";
		$transResult = DB_query ( $SQL, $db, $ErrMsg );		

		$SQL = "UPDATE tb_archivos SET 
    	ind_active = '1'
    	WHERE nu_tipo_sys = '".$type."' AND nu_trasnno = '".$transno."' AND nu_funcion = '".$funcion."' AND ind_permiso_active = '".$eliminar."' AND txt_url = '".$linkDescarga."'";
		$ErrMsg = "No se agrego la Información del Layout";
		$transResult = DB_query ( $SQL, $db, $ErrMsg );	
    }

    return true;
}

function fnValidaLayoutPresupuesto($jsonData,$db) {


$info =array('mensaje' =>'requisición', 'validacion' => true);

	return $info;
}

function fnValidaLayoutRequisicion($arreglo,$db,$idanexoGlobal,$urGlobal,$tipoGlobal,$idrequisicionGlobal) {
	//echo $idrequisicionGlobal;
    /*$anexo = $idanexoGlobal;
    $tagref = 110;
    $type = 19;
    $id_requisicion = 1002;*/
	/*$SQL ="";
	$ErrMsg="";
	$TransResult = "";*/
	//print_r($arreglo);
	//echo "arregloBng". $arreglo[0];
	$info = array();
	$x = 0;
	foreach ($arreglo as $clave => $valor) {
		$x = $x + 1;
		//print_r("<br>");
        //print_r("<br>");
        /*$anexo = 2;
        $tagref = 110;
        $type = 19;
        $id_requisicion = 1002;*/
        $area = $arreglo[$clave][0];
        $noProceso = $arreglo[$clave][1];
        $partida_esp = $arreglo[$clave][2];
        $visto_buneo = $arreglo[$clave][3];
        $vobo_requiriente = $arreglo[$clave][4];
        $firmas = $arreglo[$clave][5];
        $txt_info_creacion = $arreglo[$clave][6];
        $revisado_por = $arreglo[$clave][7];
        $autorizado_por = $arreglo[$clave][8];
        $txt_desc_antecedentes = $arreglo[$clave][9];
        $txt_justificacion = $arreglo[$clave][10];
        $ln_viabilidad = $arreglo[$clave][11];
        $txt_bien_serevicio = $arreglo[$clave][12];
        $txt_desc_bien_serevicio = $arreglo[$clave][13];
        $nu_cantidad = $arreglo[$clave][14];

        //print_r($arreglo);
		if($x > 2){
			 $SQL = "INSERT INTO tb_cnfg_anexo_tecnico (nu_anexo, nu_tagref, nu_type, nu_requisicion, sn_area, nu_proceso, nu_partida, dt_fecha_creacion, ln_visto_bueno, ln_vobo_requiriente, sn_firma, txt_informacion_creacion, sn_revisado_por, sn_autorizado_por, txt_descripcion_antecedentes, txt_justificacion, ln_viabilidad, txt_bien_serevicio, txt_desc_bien_serevicio, nu_cantidad )
			VALUES ('$idanexoGlobal','$urGlobal','$tipoGlobal', '$idrequisicionGlobal', '$area', '$noProceso', '$partida_esp', current_timestamp(), '$visto_buneo', '$vobo_requiriente', '$firmas', '$txt_info_creacion', '$revisado_por' , '$autorizado_por' , '$txt_desc_antecedentes', '$txt_justificacion', '$ln_viabilidad', '$txt_bien_serevicio' , '$txt_desc_bien_serevicio', '$nu_cantidad')";
			$ErrMsg = "No se agrego la información del anexo"; 
            $TransResult = DB_query ( $SQL, $db, $ErrMsg );
            //$myrow = DB_fetch_array ( $TransResult );
	    
		}
   } // fin foreach
	/*for($f=0;$f<count($arreglo);$f++) {
	    	echo "<tr>";
	    	for($c=0;$c<count($arreglo[$f]);$c++) {
	        echo"<td>". $arreglo[$f][$c]."</td>";
             //print_r("uno: ".$arreglo);
             //print_r("dos: ".$arreglo[$f]);
             echo "<br>";
             //print_r("tres: ".$arreglo[$c]);
	    	}
    	echo "</tr>";
    }*/	

	//$info['mensaje'] = 'Archivos de requisición';
	//$info['validacion'] = true;
//$info =array('mensaje' =>'Layout para Anexo Técnio subido con éxito.', 'validacion' => true);
$info =array('mensaje' =>'Ánexo subido correctamente', 'validacion' => true);
//'Anexo subido correctamente'
	return $info; 
}

/**
 * Muestra en pantalla la consulta y la linea
 * @param  string  		$consulta 	Valor a mostrar
 * @param  constante  	$linea    	Constante php __LINE__
 * @param  bolean 		$ejecutar 	Bandera para controlar la ejecucion
 */
function debug_sql($consulta, $linea, $ejecutar = false, $file='NO_NAME',$color="black") {
	if($ejecutar) {
		echo '<FONT FACE="arial" SIZE=3 COLOR='.$color.'>Achivo: ' . $file . '<br/>';
		echo 'Linea: ' . $linea . '<br/><pre>'; 
	    var_dump($consulta);
	    echo '</pre><br/></FONT>';
	}
}
/**
    Funcion que regresa la descripcion de acuerdo al numero de funcion de la pagina
*/
function traeNombreFuncionGeneral($funcion, $db,$ponerNombre='0') {
    $descripcion= "";
    if($ponerNombre=='0'){
    if (!empty($funcion)) {
        $SQL = "SET NAMES 'utf8'";
        $TransResult = DB_query ( $SQL, $db );
        // consultar dato de funcion
        $consulta= "SELECT functionid, title FROM sec_functions_new WHERE functionid='".$funcion."'";
        $resultado= DB_query($consulta, $db);

        while ($registro= DB_fetch_array($resultado)) {
            $descripcion= $registro["title"];
        }

    }
        }else{
            $descripcion=$ponerNombre;
        }
    return $descripcion;
}

/**
Funcion que regresa el estatus siguiente o anterior
$txtEstatus - parametro con el nombre del estatus a buscar
$intMovimiento - parametro con el numero del tipo de movimiento (0-Anterior, 1-Siguiente)
$intFuncion - parametro con el numero de funcion de los estatus
*/
function traeCambioEstatus($txtEstatus, $intMovimiento=1, $intFuncion, $db) {
	$campo_movimiento= "sn_estatus_siguiente";

	if ($intMovimiento==0) {
		$campo_movimiento= "sn_estatus_anterior";
	}

	// consultar el estatus de acuerdo al tipo de movimiento
	$consulta= "SELECT statusname
				FROM tb_botones_status
				WHERE id IN (SELECT ".$campo_movimiento." FROM tb_botones_status 
				WHERE statusname='".$txtEstatus."' AND sn_funcion_id= '".$intFuncion."')";

	$resultado= DB_query($consulta, $db);

	if ($registro= DB_fetch_array($resultado)) {
		return $registro["statusname"];
	} else {
		return $txtEstatus;
	}
}

function traeCuentaProveedor($txtProveedorId, $db) {
	$consulta="SELECT * FROM accountxsupplier WHERE supplierid= '".$txtProveedorId."'";
	$resultado= DB_query($consulta, $db);
	if ($registro= DB_fetch_array($resultado)) {
		// return $registro["accountcode"];
		// Validar que la cuenta se encuentre en la matriz
		$SQL = "SELECT accountegreso FROM stockcategory WHERE accountegreso = '".$registro["accountcode"]."'";
		$resultCuenta = DB_query($SQL, $db);
		if (DB_num_rows($resultCuenta) > 0) {
			return $registro["accountcode"];
		} else {
			return "";
		}
	} else {
		return "";
	}
}

function fnAgregarPresupuestoGeneral($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $estatus, $tipoAfectacion, $tipoMovimiento, $partida_esp, $tipoMovGenReduccion, $tipoMovGenAmpliacion, $descripcion = "")
{
    // Reduccion
    $tipoMovClave = $tipoMovGenReduccion;
    $sqlWhere = " AND (qty < 0 or nu_tipo_movimiento = '".$tipoMovClave."') ";
    if ($tipoMovimiento == 'Ampliacion') {
        // Ampliacion
        $tipoMovClave = $tipoMovGenAmpliacion;
        $sqlWhere = " AND (qty > 0 or nu_tipo_movimiento = '".$tipoMovClave."') ";
    }

    $ln_ue = fnObtenerUnidadEjecutoraClave($db, $clave);

    $sqlOperacion = 0;
    $SQL = "SELECT * FROM chartdetailsbudgetlog 
            WHERE 
            type = '".$type."' 
            AND transno = '".$transno."' 
            AND cvefrom = '".$clave."' 
            AND period = '".$periodo."' ".$sqlWhere;
    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $sqlOperacion = 1;
    } else {
        $sqlOperacion = 2;
    }
    if ($sqlOperacion == 1) {
        // Agregar datos
        $SQL = "INSERT INTO `chartdetailsbudgetlog` 
                (`datemov`, 
                `userid`, 
                `qty`, 
                `description`, 
                `cvefrom`, 
                `type`, 
                `transno`, 
                `estatus`, 
                `fecha_captura`, 
                `dtm_aplicacion`, 
                `tagref`,
                `period`,
                `nu_afectacion`,
                `partida_esp`,
                `nu_tipo_movimiento`,
                `ln_ue`,
                `nu_idret`,
                `nu_anio_fiscal`)
                VALUES
                (NOW(), 
                '".$_SESSION['UserID']."', 
                '".$cantidad."', 
                '".$descripcion."', 
                '".$clave."', 
                '".$type."', 
                '".$transno."', 
                '".$estatus."', 
                NOW(), 
                NOW(), 
                '".$tagref."',
                '".$periodo."',
                '".$tipoAfectacion."',
                '".$partida_esp."',
                '".$tipoMovClave."',
            	'".$ln_ue."',
            	'0',
            	'".$_SESSION['ejercicioFiscal']."')";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else if ($sqlOperacion == 2) {
        // Actualizar datos      
        if ($tipoMovClave == '263') {
        	// Si es de Suficiencia Automatica tambien afecta precomprometido
        	$SQL = "UPDATE chartdetailsbudgetlog SET 
	                `datemov` = NOW(),
	                `userid` = '".$_SESSION['UserID']."', 
	                `qty` = '".$cantidad."', 
	                `estatus` = '".$estatus."', 
	                `ln_ue` = '".$ln_ue."',
	                `fecha_captura` = NOW(),
	                `nu_anio_fiscal` = '".$_SESSION['ejercicioFiscal']."'
	                WHERE 
	                type = '".$type."' 
	                AND transno = '".$transno."' 
	                AND cvefrom = '".$clave."' 
	                AND period = '".$periodo."' ";
	        $ErrMsg = "No se pudo almacenar la información";
	        $TransResult = DB_query($SQL, $db, $ErrMsg);
        }else{
        	$SQL = "UPDATE chartdetailsbudgetlog SET 
	                `datemov` = NOW(),
	                `userid` = '".$_SESSION['UserID']."', 
	                `qty` = '".$cantidad."', 
	                `description` = '".$descripcion."',
	                `cvefrom` = '".$clave."', 
	                `type` = '".$type."', 
	                `transno` = '".$transno."', 
	                `estatus` = '".$estatus."', 
	                `nu_afectacion` = '".$tipoAfectacion."',
	                `partida_esp` = '".$partida_esp."',
	                `nu_tipo_movimiento` = '".$tipoMovClave."',
	                `ln_ue` = '".$ln_ue."',
	                `fecha_captura` = NOW(),
	                `nu_anio_fiscal` = '".$_SESSION['ejercicioFiscal']."'
	                WHERE 
	                type = '".$type."' 
	                AND transno = '".$transno."' 
	                AND cvefrom = '".$clave."' 
	                AND period = '".$periodo."' ".$sqlWhere;
	        $ErrMsg = "No se pudo almacenar la información";
	        $TransResult = DB_query($SQL, $db, $ErrMsg);
        }
    }
}

function fnAgregarSuficienciaGeneral($db, $type, $transno, $sn_description = "", $nu_estatus = "", $sn_tagref = "", $nu_tipo = 0, $sn_funcion_id = 0, $sn_orderno=0, $ue = "")
{
    $SQL = "SELECT * FROM tb_suficiencias 
            WHERE 
            nu_type = '".$type."' 
            AND nu_transno = '".$transno."' ";
    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $sqlOperacion = 1;
    } else {
        $sqlOperacion = 2;
    }
    if ($sqlOperacion == 1) {
        // Agregar datos
        $SQL = "INSERT INTO `tb_suficiencias` 
                (`dtm_fecha`, 
                `sn_userid`, 
                `sn_description`, 
                `nu_type`, 
                `nu_transno`, 
                `nu_estatus`, 
                `sn_tagref`, 
                `nu_tipo`,
                `sn_funcion_id`,
                `sn_orderno`,
                `ln_ue`,
                `nu_anio_fiscal`
                )
                VALUES
                (NOW(), 
                '".$_SESSION['UserID']."', 
                '".$sn_description."', 
                '".$type."', 
                '".$transno."', 
                '".$nu_estatus."', 
                '".$sn_tagref."', 
                '".$nu_tipo."',
            	".$sn_funcion_id.",
            	".$sn_orderno.",
            	'".$ue."',
            	'".$_SESSION['ejercicioFiscal']."')";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else if ($sqlOperacion == 2) {
        // Actualizar datos
        $SQL = "UPDATE tb_suficiencias SET 
                `dtm_fecha` = NOW(),
                `sn_userid` = '".$_SESSION['UserID']."', 
                `sn_description` = '".$sn_description."', 
                `nu_estatus` = '".$nu_estatus."',
                `sn_tagref` = '".$sn_tagref."',
                `nu_tipo` = '".$nu_tipo."',
                `sn_funcion_id` = ".$sn_funcion_id.",
                `sn_orderno` = ".$sn_orderno.",
                `ln_ue` = '".$ue."'
                WHERE 
                nu_type = '".$type."' 
                AND nu_transno = '".$transno."'";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }
}

function fechaParaSQL($aReemplazar1, $aReemplazar2, $aFecha)
{
	$FechaSeparada = explode($aReemplazar2, $aFecha);
	return $FechaSeparada[2].$aReemplazar2.$FechaSeparada[1].$aReemplazar2.$FechaSeparada[0];
}

function fechaParaComparar($aReemplazar1, $aReemplazar2, $aFecha)
{
	$FechaSeparada = explode($aReemplazar2, $aFecha);
	return $FechaSeparada[2].$FechaSeparada[1].$FechaSeparada[0];
}

/**
 * Funcion para mostrar mensaje general por paginas antiguas (submit)
 * @param  int $procesoterminado  Encabezado 
 * @param  string $mensaje_emergente Mensaje 
 */
function fnmuestraModalGeneral($procesoterminado, $mensaje_emergente)
{
    $titulo = "";
    switch ($procesoterminado) {
        case '1':
            $titulo= '<i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;<span>Proceso Exitoso</span>';
            break;
        case '2':
            $titulo= '<i class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp;<span>Informaci&oacute;n de Proceso</span>';
            break;
        case '1001':
            $titulo= '<i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;<span>Error de Proceso</span>';
            break;
        default:
            $titulo= '<i class="fa fa-times-circle" aria-hidden="true"></i>&nbsp;<span>Error de Proceso</span>';
            break;
    }
    
    //$mensaje_emergente= "<h4>".$mensaje_emergente."</h4>";
    $mensaje_emergente= $mensaje_emergente;

    echo "<script>";
    echo 'muestraModalGeneral(3, \''.$titulo.'\', \''.$mensaje_emergente.'\');';
    //echo '$(":checkbox").attr("checked", false);';
    echo "</script>";
}

function fnObtenerTotalSuficienciaAuto($db, $type, $transno, $tipoMovimiento=0, $clave='') {
	$sqlWhere = "";
	if ($tipoMovimiento != 0) {
		$sqlWhere .= " and chartdetailsbudgetlog.nu_tipo_movimiento = '".$tipoMovimiento."' ";
	}
	if (!empty($clave)) {
		$sqlWhere .= " and chartdetailsbudgetlog.cvefrom = '".$clave."' ";
	}
	$consulta= "SELECT 
	SUM(chartdetailsbudgetlog.qty) as total
	FROM chartdetailsbudgetlog 
	WHERE 
	chartdetailsbudgetlog.type = '".$type."' 
	and chartdetailsbudgetlog.transno = '".$transno."' ".$sqlWhere;
    $resultado= DB_query($consulta, $db);
    
    if ($registro= DB_fetch_array($resultado)) {
		return $registro["total"];
	} else {
		return 0;
	}
}

function fnObtenerUnidadEjecutoraClave($db, $clave) {
    // Obtener informacion para identificador Inicio
    $cppt = "";
    $SQL = "SELECT 
    tb_cat_unidades_ejecutoras.ue as ue
    FROM chartdetailsbudgetbytag 
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE chartdetailsbudgetbytag.accountcode = '".$clave."'
    ";
    $ErrMsg = "No se encontro el Identificar de la Clave ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $cppt = $myrow['ue'];
    }
    return $cppt;
    // Obtener informacion para identificador Fin
}

function fnGetDatosRequi($requi,$db){
   $infoDatosRequi=array();
                            $SQL="SELECT 
                             purchorderdetails.orderno AS idRequisicion, 
                            tb_partida_articulo.partidaEspecifica AS idPartida, 
                            tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartida,
                            purchorderdetails.itemcode AS idItem, 
                            purchorderdetails.itemdescription AS descItem, 
                            stockmaster.units AS unidad, 
                            stockmaster.mbflag AS tipo,
                            purchorderdetails.unitprice AS precio, 
                            purchorderdetails.quantityord AS cantidad,
                            purchorderdetails.total_quantity AS total, 
                            -- if(almacen.existencia = 0,'No Disponible','Disponible') AS existencia,
                            almacen.existencia AS existencia,
                            purchorderdetails.orderlineno_ AS orden, 
                            purchorderdetails.clavepresupuestal AS clavePresupuestal, 
                            purchorderdetails.sn_descripcion_larga AS descLarga,
                            purchorderdetails.renglon AS renglon, purchorders.tagref
                        FROM purchorderdetails 
                        INNER JOIN purchorders ON  purchorderdetails.orderno= purchorders.orderno
                        JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
                        JOIN tb_partida_articulo on (tb_partida_articulo.eq_stockid = stockmaster.eq_stockid)
                        JOIN tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
                        LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='desarrollo'
                        GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
                       /* WHERE purchorderdetails.orderno = '997' AND purchorderdetails.status ='2' */
                        where purchorders.requisitionno ='".$requi ."' AND purchorderdetails.status ='2'
                        ORDER BY orden;";
                   
                        $ErrMsg = "No se obtuvo datos";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);

                        while ($myrow = DB_fetch_array($TransResult)) {
                             $infoDatosRequi[] = array( 

                              'idPartida' => $myrow ['idPartida'],
                              'descPartida' => $myrow ['descPartida'],
                              'idItem' => $myrow ['idItem'], 
                              'descItem' => $myrow ['descItem'],
                              'unidad' => $myrow ['unidad'],
                              'tipo' => $myrow ['tipo'],
                              'precio' => $myrow ['precio'],
                              'cantidad' => $myrow ['cantidad'],
                              'total' => $myrow ['total'],
                              'existencia' => $myrow ['existencia'],
                              'orden' => $myrow ['orden'],
                              'clavePresupuestal'=> $myrow ['clavePresupuestal'],
                              'descLarga' => $myrow ['descLarga'],
                              'renglon' => $myrow ['renglon'] 

                                           );
                 
                           
                        }
                        return $infoDatosRequi;
}
function fnGetProvsugeridos($partidas,$db){

            //$partidas=$_POST['partidas'];
            $infoPartidasProv=array();
            $ErrMsg = "No se obtuvo datos";
            $valPartidas='';

            for ($a=0; $a <count($partidas) ; $a++) { 
              $valPartidas.="'".$partidas[$a]."',";

            }
             $valPartidas=substr($valPartidas, 0, -1);

            $SQL="SELECT  ln_partida_especifica,ln_supplierid,ln_suppname, suppliers.email  FROM tb_partidas_proveedores INNER JOIN suppliers 
            ON tb_partidas_proveedores.ln_supplierid = suppliers.supplierid  WHERE   ln_partida_especifica IN ($valPartidas) ORDER BY ln_partida_especifica ASC ; ";
           // exit();
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        
            while ($myrow = DB_fetch_array($TransResult)) {
               /* $infoPartidasProv[] = array( 

                  //'partida' => $myrow ['ln_partida_especifica'],
                  'idsup' =>   $myrow ['ln_supplierid']
                  /*'nombre' =>  $myrow ['ln_suppname'],
                  'email'=>    $myrow ['email'] */
                             /*  );*/
                             $infoPartidasProv[]=$myrow ['ln_supplierid'];
            } 

            return  $infoPartidasProv;

}
function fnGetMes($numeroMes){
$mes='';
if(strlen($numeroMes)==1){
    if (strpos($numeroMes, '0') == false) {
        $numeroMes='0'.$numeroMes;
    }
}

    switch($numeroMes){

        case '01':
        $mes='Enero';
        break;
        
        case '02':
        $mes='Febrero';
        break;
        
        case '03':
        $mes='Marzo';
        break;

        case '04':
        $mes='Abril';
        break;
 
        case '05':
        $mes='Mayo';
        break;

        case '06':
        $mes='Junio';
        break;
        
        case '07':
        $mes='Julio';
        break;

        case '08':
        $mes='Agosto';
        break;

        case '09':
        $mes='Septiembre';
        break;
        
        case '10':
        $mes='Octubre';
        break;

        case '11':
        $mes='Noviembre';
        break;

        case '12':
        $mes='Diciembre';
        break;
        
    }
return  $mes;
}

/**
 * Obtiene una unidad responsable por default para el identifiador en matrices y plan de cuentas
 * @param  [type] $db [description]
 * @return string     Código de UR
 */
function fnURDefaultIdentificador($db) {
	$UR = "";
	$SQL = "SELECT tagref FROM sec_unegsxuser WHERE userid = '".$_SESSION['UserID']."' LIMIT 1";
	$TransResult = DB_query($SQL, $db);
	while ($myrow = DB_fetch_array($TransResult)) {
		$UR = $myrow['tagref'];
	}
	return $UR;
}
/**
 * Función para la comprobación de la existencia
 * de una determinada clave programatica
 * @date: 29.03.18
 * @author Desarrollo
 * @param  {DB} 	$db    	Entidad de base de datos
 * @param  {String} $clave 	Clave que sera ebaluada
 * @return {Integer}        Cantidad de registros encontrados en base
 */
function compruebaClaveProgramatica($db,$clave)
{
	$sql = "SELECT CONCAT(id_nu_ur,'-',id_nu_fi,'-',id_nu_fu,'-',id_nu_sf,'-',id_nu_rg,'-',id_nu_ai,'-', id_nu_pp,'-', ln_anexo) 
	FROM `tb_cat_estructura_programatica` 
	WHERE `ind_activo`='1' AND CONCAT(id_nu_ur,'-',id_nu_fi,'-',id_nu_fu,'-',id_nu_sf,'-',id_nu_rg,'-',id_nu_ai,'-', id_nu_pp,'-', ln_anexo) = '$clave'";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}
/**
 * Función para la comprobación de la existencia
 * de una determinada clave economica
 * @date: 29.03.18
 * @author Desarrollo
 * @param  {DB} 	$db    	Entidad de base de datos
 * @param  {String} $clave 	Clave que sera ebaluada
 * @return {Integer}        Cantidad de registros encontrados en base
 */
function compruebaClaveEconomica($db,$clave)
{
	$sql = "SELECT CONCAT(`id_nu_partida`,'-',`id_nu_tg`,'-',`id_nu_ff`) 
		FROM `tb_cat_estructura_economica` WHERE `ind_activo`='1' AND CONCAT(`id_nu_partida`,'-',`id_nu_tg`,'-',`id_nu_ff`) = '$clave'";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}
/**
 * Función para la comprobación de la existencia
 * de una determinada clave administrativa
 * @date: 29.03.18
 * @author Desarrollo
 * @param  {DB} 	$db    	Entidad de base de datos
 * @param  {String} $clave 	Clave que sera ebaluada
 * @return {Integer}        Cantidad de registros encontrados en base
 */
function compruebaClaveAdministrativa($db,$clave)
{
	$sql = "SELECT CONCAT(`id_nu_ur`,'-',`id_nu_auxiliar`,'-',`id_nu_ef`) 
	FROM `tb_cat_estructura_administrativa` WHERE `ind_activo`='1' AND CONCAT(`id_nu_ur`,'-',`id_nu_auxiliar`,'-',`id_nu_ef`) = '$clave'";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}

/**
 * Función para la comprobación de la existencia
 * de una determinada clave relacion pp partida
 * @date: 12.04.18
 * @author Desarrollo
 * @param  {DB} 	$db    	Entidad de base de datos
 * @param  {String} $clave 	Clave que sera ebaluada
 * @return {Integer}        Cantidad de registros encontrados en base
 */
function compruebaClaveRelacionPpPartida($db,$clave)
{
	$sql = "SELECT CONCAT(`id_nu_pp`,'-',`id_nu_partida`) 
		FROM `tb_cat_relacion_pp_partida` WHERE `ind_activo`='1' AND CONCAT(`id_nu_pp`,'-',`id_nu_partida`) = '$clave'";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}

/**
 * Función para obtener el número de niveles de una cuenta contable
 * @date: 12.04.18
 * @param  [type] $db          Base de Datos
 * @param  string $accountcode Cuenta Contable
 * @return [type]              Número de niveles
 */
function fnNivelesCuentaContableGeneral($db, $accountcode = '')
{
    // Se regresa el numero de niveles
    return count(explode('.', $accountcode));
}

/**
 * Función para obtener la partida especifica de la clave presupuestal
 * @date: 14.05.18
 * @param  [type] $db          Base de Datos
 * @param  string $clave 	   Clave Presupuestal
 * @return [type]              Partida Específica
 */
function fnObtenerPartidaClavePresupuestal($db, $clave = '') {
	$partida_esp = '0';
	$SQL = "SELECT partida_esp FROM chartdetailsbudgetbytag WHERE accountcode = '".$clave."'";
	$resultClave = DB_query ( $SQL, $db, $ErrMsg );
	while ( $rowClave = DB_fetch_array ( $resultClave ) ) {
		$partida_esp = $rowClave ['partida_esp'];
	}

	return $partida_esp;
}

/**
 * Función para obtener el folio por unidad ejecutora
 * para las póliza contables
 * @param  [type] $db     Base de Datos
 * @param  [type] $tagref Unidad Responsable
 * @param  [type] $ue     Unidad Ejecutora
 * @return [type]         Folio de la póliza
 */
function fnObtenerFolioUeGeneral($db, $tagref=0, $ue=0, $type=0)
{
    // Agregar unidades ejecutoras agregadas
    $SQL = "INSERT INTO tb_ue_folio_poliza (tagref, ue, ln_aux1, nu_tipo_poliza, typeno)
	SELECT tb_cat_unidades_ejecutoras.ur, tb_cat_unidades_ejecutoras.ue, tb_cat_unidades_ejecutoras.ln_aux1, tb_cat_poliza_visual.id, 0
	FROM tb_cat_unidades_ejecutoras
	JOIN tb_cat_poliza_visual
	WHERE tb_cat_unidades_ejecutoras.active = 1
	AND CONCAT(tb_cat_unidades_ejecutoras.ln_aux1, '_', tb_cat_poliza_visual.id) NOT IN (SELECT CONCAT(ln_aux1, '_', nu_tipo_poliza) FROM tb_ue_folio_poliza)";
    $ErrMsg = 'Agregar Unidades Ejecutoras faltantes';
    $GetTransNoResult = DB_query($SQL, $db, $ErrMsg);

	// Si no tiene actualización
	$SQL = "SELECT 
	dtm_aplicacion
	FROM tb_ue_folio_poliza_actualizacion
	WHERE DATE_FORMAT(dtm_aplicacion,'%m/%Y') = '".date('m/Y')."'";
	$ErrMsg = 'Validación para reinicio de folios';
    $GetTransNoResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($GetTransNoResult) == 0) {
    	// Si no tiene reinicio de folio
    	$SQL = "UPDATE tb_ue_folio_poliza SET typeno = 0";
    	$ErrMsg = 'Reiniciar folio por unidad ejecutora para pólizas';
    	$GetTransNoResult = DB_query($SQL, $db, $ErrMsg);

    	$SQL = "INSERT INTO tb_ue_folio_poliza_actualizacion (dtm_aplicacion) VALUES (NOW())";
    	$ErrMsg = 'Registro de reinicio de folios';
    	$GetTransNoResult = DB_query($SQL, $db, $ErrMsg);
    }

    // Obtener tipo de póliza visual de la transaccion
    $SQL = "SELECT nu_poliza_visual FROM systypescat WHERE typeid = '".$type."'";
    $ErrMsg = 'Al obtener número de la póliza visual de '.$type;
    $GetTransNoResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array ( $GetTransNoResult );
    $polizaVisual = $myrow['nu_poliza_visual'];

    // Obtener folio de la unidad ejecutora
    DB_query("LOCK TABLES tb_ue_folio_poliza WRITE", $db);
    
    $SQL = "SELECT typeno FROM tb_ue_folio_poliza 
    WHERE tagref = '".$tagref."' AND ue = '".$ue."' AND nu_tipo_poliza = '".$polizaVisual."'";
    $ErrMsg = 'No se obtuvo el folio para la póliza por unidad ejecutora';
    $GetTransNoResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_row($GetTransNoResult);
    
    $SQL = "UPDATE tb_ue_folio_poliza SET typeno = " . ($myrow[0] + 1) . " 
    WHERE tagref = '".$tagref."' AND ue = '".$ue."' AND nu_tipo_poliza = '".$polizaVisual."'";
    $ErrMsg = 'No se actualizó el folio para la póliza por unidad ejecutora';
    $GetTransNoResult = DB_query($SQL, $db, $ErrMsg);
    
    DB_query("UNLOCK TABLES", $db);

    return $myrow[0] + 1;
}

/**
 * Función para obtener el identificador de la clave presupuesal
 * @param  [type] $db    Base de datos
 * @param  [type] $clave Clave presupuestal
 * @return [type]        Identificador
 */
function fnObtenerIdentificadorClavePrespuesto($db, $clave)
{
    // Obtener informacion para identificador Inicio
    $cppt = "";
    $SQL = "SELECT 
    chartdetailsbudgetbytag.tagref,
    tb_cat_unidades_ejecutoras.ue,
    chartdetailsbudgetbytag.cppt
    FROM chartdetailsbudgetbytag
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE chartdetailsbudgetbytag.accountcode = '".$clave."'
    ";
    $ErrMsg = "No se encontro el Identificar de la Clave ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $cppt = $myrow['tagref']."-".$myrow['ue']."-".$myrow['cppt'];
    }
    return $cppt;
    // Obtener informacion para identificador Fin
}

/**
 * Función para obtener la información del tipo de documento
 * @param  [type] $db   Base de Datos
 * @param  string $type Tipo de Documento
 * @return [type]       Información del tipo de documento
 */
function fnInformacionTipoDocumento($db, $type = '')
{
    // Obtener informacion del tipo de documento
    $SQL = "SELECT * FROM systypescat WHERE typeid = '".$type."'";
    $ErrMsg = "No se pudo obtener la información del tipo de documento ".$type;
    return DB_fetch_array( DB_query($SQL, $db, $ErrMsg) );
}

/**
 * Función para validar periodo de diciembre mes ejercicio fiscal
 * @param  [type] $db Base de Datos
 * @return [type]     [description]
 */
function fnValPeriodoEjercicioFiscal($db, $clavepresupuestal = array()) {
    // Validar peridos por clave
    $respuesta['result'] = true;
    $respuesta['mensaje'] = '';
    $respuesta['periodo'] = '';
    $respuesta['fecha'] = date('Y-m-d');

    $claves = "";
    foreach ($clavepresupuestal as $datos) {
        if ($claves == "") {
            $claves .= "'".$datos['accountcode']."'";
        } else {
            $claves .= ", '".$datos['accountcode']."'";
        }
    }

    $SQL = "SELECT
    chartdetailsbudgetbytag.anho,
    periods.periodno as periodnoDic,
    chartdetailsbudgetbytag.tagref,
    tags.legalid
    FROM chartdetailsbudgetbytag
    LEFT JOIN periods ON YEAR(periods.lastdate_in_period) = chartdetailsbudgetbytag.anho AND LOCATE('.', periods.periodno) = '0' AND MONTH(periods.lastdate_in_period) = '12'
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    WHERE
    chartdetailsbudgetbytag.accountcode IN (".$claves.")
    ";
    $ErrMsg = "No se obtuvo el periodo de diciembre";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $periodoValidar = '';
        $fechaValidar = '';
        $anioVal = '';
        if ($myrow['anho'] == date('Y')) {
            // Validar periodo actual
            $periodoValidar = GetPeriod(date('d/m/y'), $db);
            $fechaValidar = date('Y-m-d');
            $anioVal = date('Y');
        } else {
            // Validar periodo de diciembre
            $periodoValidar = $myrow['periodnoDic'];
            $fechaValidar = $myrow['anho'].'-12-31';
            $anioVal = $myrow['anho'];
        }

        if (empty($periodoValidar)) {
            // Si no tiene perido
            $respuesta['result'] = false;
            $respuesta['mensaje'] .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo periodo contable para realizar el proceso</p>';
        } else {
            // Validar si el periodo esta abierto
            $SQL = "SELECT 
            periodsXlegal.status,
            cat_Months.mes as mesName
            FROM periodsXlegal 
            JOIN periods ON periodsXlegal.periodno = periods.periodno
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periodsXlegal.periodno = '".$periodoValidar."' 
            AND periodsXlegal.legalid = '".$myrow['legalid']."'";
            $TransResult2 = DB_query($SQL, $db, $ErrMsg);
            $myrowPeriodo = DB_fetch_array($TransResult2);
            if ($myrowPeriodo['status'] == 1) {
                // Periodo cerrado
                $respuesta['result'] = false;
                $respuesta['mensaje'] .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El periodo contable de '.$myrowPeriodo['mesName'].' del '.$anioVal.' se encuentra cerrado</p>';
            }
        }

        $respuesta['periodo'] = $periodoValidar;
        $respuesta['fecha'] = $fechaValidar;
    }

    return $respuesta;
}

/**
 * Función para validar si existe la cuenta contable
 * @param  [type] $db         Conexión Base de Datos
 * @param  string $accuntcode Cuenta Contable
 * @return [type]             Existe = true, No Existe = false
 */
function fnValidarCuentaContable($db, $accuntcode = '')
{
    $respuesta = array();
    $SQL = "SELECT accountcode FROM chartmaster WHERE accountcode = '".$accuntcode."'";
    $ErrMsg = "No se obtuvo la Cuenta Contable";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $respuesta['success'] = false;
        $respuesta['mensaje'] = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe '.$accuntcode.' en el Plan de Cuentas</p>';
    } else {
        $respuesta['success'] = true;
        $respuesta['mensaje'] = '';
    }
    return $respuesta;
}

/**
 * Función para validar si existe un proveedor
 * @param  [type] $db         Conexión Base de Datos
 * @param  string $supplierid Código del Proveedor
 * @return [type]             Existe = true, No Existe = false
 */
function fnValidarProveedor($db, $supplierid = '')
{
    $respuesta = array();
    $SQL = "SELECT supplierid FROM suppliers WHERE supplierid = '".$supplierid."'";
    $ErrMsg = "No se obtuvo el Proveedor";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $respuesta['success'] = false;
        $respuesta['mensaje'] = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe '.$supplierid.' en los Proveedores</p>';
    } else {
        $respuesta['success'] = true;
        $respuesta['mensaje'] = '';
    }
    return $respuesta;
}

/**
 * Función para obtener anticipos de clientes, tipo 130
 * @param  [type]  $db         Conexión Base de Datos
 * @param  string  $debtorno   Código de Cliente
 * @param  string  $currcode   Moneda
 * @param  string  $tagref     Unidad de Negocio
 * @param  integer $idAnticipo Id de anticipo
 * @return [type]              [description]
 */
function fnObtenerAnticiposCliente($db, $debtorno = '', $currcode = '', $tagref = '', $idAnticipo = 0) {
	// **************************** //
	// No dejar echo en la funcion //
	// Afecta al punto de venta   //
	// ************************* //
	
	$sqlWhere = "";

	if (!empty($idAnticipo)) {
		$sqlWhere = " AND debtortrans.id = '".$idAnticipo."'";
	}
	
	$SQL = "SELECT   DISTINCT debtortrans.trandate,  abs(debtortrans.ovamount) - CASE WHEN abs(ovgst)>0 THEN abs(debtortrans.alloc/1.16) ELSE   abs(debtortrans.alloc) END AS pendiente,
	debtortrans.transno,
	systypescat.typename,
	debtortrans.type,
	debtortrans.invtext,
	abs(debtortrans.ovamount) AS monto,
	(SELECT d.folio from debtortrans d WHERE d.id=debtortrans.ref1 ) AS factura,
	if(abs(ovgst)>0,1,0) AS IVA,
	debtortrans.id,
	debtortrans.ref1 AS idfactura
	FROM debtortrans        
	INNER JOIN systypescat ON systypescat.typeid = debtortrans.type     
	WHERE  
	( abs(debtortrans.ovamount + debtortrans.ovgst) - abs(debtortrans.alloc)) > .01
	AND debtortrans.type = '130'
	AND debtortrans.tagref in ('".$tagref."')
	AND debtortrans.currcode = '" . $currcode . "' 
	AND debtortrans.debtorno = '". $debtorno ."'".$sqlWhere;
	
	return  $SQL;
}

$mensajeToken = "";

if (isset($dbType)) {
	if($dbType=="mysqli"){
		if(isset($_POST)&&is_array($_POST)&&count($_POST)){
			$noEsModelo = true;
			$errorToken = array();
			$errorToken[1] = 0;
			$errorToken[2] = 0;
			$errorToken[3] = 0;
			$_POST = limpiaArreglo($_POST);
			//$_POST = reestableceArreglos($_POST);

			// Condición que valida que el Token de la sesión coincida con el de la BDD 
			if(!VerifyCsrfToken($db)){
				$errorToken[1] = 1;
			}

			// Condición que valida que sólo se ejecute cuando un modelo (en la carpeta /modelo) mande llamar a includes/SQL_CommonFunctions.inc
			if(substr(dirname($_SERVER['SCRIPT_FILENAME']),strlen(dirname($_SERVER['SCRIPT_FILENAME']))-7)=="/modelo"){
				$noEsModelo = false;

				// Condición que valida que el Token exista en el header que recibió el modelo
				if(!isset(apache_request_headers()['X-CSRF-TOKEN'])){
					$errorToken[2] = 1;
				}else{
					// Condición que valida que el Token en el Header coincida con el de la sesión
					if($_SESSION['csrf']['send_message_token']['token']!=apache_request_headers()['X-CSRF-TOKEN']){
						$errorToken[3] = 1;
					}
				}
			}

			if(( ($errorToken[1] * 1)+($errorToken[2] * 2)+($errorToken[3] * 4) )){
				$mensajeError = "Error de seguridad #".( ($errorToken[1] * 1)+($errorToken[2] * 2)+($errorToken[3] * 4) ).".";
				/*
					Errores de seguridad:
						1	El Token de sesión no coincide con el Token almacenado en la BDD
						2	No existe el Token en el Header
						3	El Token de sesión no coincide con el Token almacenado en la BDD y No existe el Token en el Header
						4	El Token en el Header no coincide con el Token de sesión
						5	El Token de sesión no coincide con el Token almacenado en la BD y El Token en el Header no coincide con el Token de sesión
						6	No existe el Token en el Header y El Token en el Header no coincide con el Token de sesión
						7	El Token de sesión no coincide con el Token almacenado en la BDD, No existe el Token en el Header y El Token en el Header no coincide con el Token de sesión
				*/
				if($noEsModelo){
					$mensajeToken = 'muestraModalGeneral(3, \'<i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;<span>Error de Proceso</span>\', \''.$mensajeError.'\');';
				}else{
					echo $mensajeError;
				}
				exit();
			}
		}
		if(isset($_GET)&&is_array($_GET)&&count($_GET)){
			$_GET = limpiaArreglo($_GET);
			//$_GET = reestableceArreglos($_GET);
		}
	}
}

/*
    VALIDACIONES CFDI 3.3
    Inicio de Funciones
*/
function ValidacionesCFDI3_3($orderno, $TipoComprobante="", $UsoCfdi="", $c_paymentid="", $total=0, $ClaveFactura="", $db){
	// **************************** //
	// No dejar echo en la funcion //
	// Afecta al punto de venta   //
	// **************************** //
	
	$success = true;
	$message = "";
	$response = array();
	$error = array();

	//Regiemen Fiscal de Razon Social, Codigo Categoria de Inventario y Codigo Unidad de Medida
	$sql = "SELECT
			legalbusinessunit.legalname,
			legalbusinessunit.taxid,
			legalbusinessunit.c_RegimenFiscal,
			legalbusinessunit.c_Regimen,
			
			unitsofmeasure.unitname,
			unitsofmeasure.c_ClaveUnidad,
			taxauthorities.description,
			taxauthorities.c_Impuesto,
			taxcategories.taxcatname,
			taxcategories.id_Tasa
			FROM salesorderdetails
			LEFT JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
			LEFT JOIN tags ON tags.tagref = salesorders.tagref
			LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
			LEFT JOIN stockmaster ON stockmaster.stockid = salesorderdetails.stkcode
			LEFT JOIN unitsofmeasure ON unitsofmeasure.unitname = stockmaster.units
			LEFT JOIN taxauthrates ON taxauthrates.taxcatid = stockmaster.taxcatid
			LEFT JOIN taxauthorities ON taxauthorities.taxid = taxauthrates.taxauthority
			LEFT JOIN taxcategories ON taxcategories.taxcatid = stockmaster.taxcatid
			WHERE salesorderdetails.orderno = '".$orderno."'";
	$result = DB_query($sql,$db);
	// stockcategory.categorydescription,
	// stockcategory.c_ClaveProdServ,
	// LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
	if (DB_num_rows ( $result ) > 0) {
		$regimen = 0;
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if (empty($myrow['c_RegimenFiscal']) and strpos($message, $myrow['legalname']) === false) {
				$regimen = 1;
				$success = false;
				$message .= "No se encuentra configurado el regimen fiscal de ".$myrow['legalname']." rfc: ".$myrow['taxid'].". Configuracion en funcion 0168 - Abc Razones Sociales<br>";
			}
			// No tomar en cuenta configuración de categorias de inventario
			// if (empty($myrow['c_ClaveProdServ']) and strpos($message, $myrow['categorydescription']) === false) {
			// 	$success = false;
			// 	$message .= "No se encuentra configurada la categoria de inventario ".$myrow['categorydescription'].". Configuracion en funcion 0137 - Manto. Categorias Inventarios<br>";
			// }
			if (empty($myrow['c_ClaveUnidad']) and strpos($message, $myrow['unitname']) === false) {
				$success = false;
				$message .= "No se encuentra configurada la unidad de medida ".$myrow['unitname'].". Configuracion en funcion 0141 - Unidades De Medida<br>";
			}
			if (empty($myrow['c_Impuesto']) and strpos($message, $myrow['description']) === false) {
				$success = false;
				$message .= "No se encuentra condigurado el tipo de impuesto ".$myrow['description'].". Configuracion en funcion 0096 - Tasas Y Autoridades Impuestos<br>";
			}
			if (empty($myrow['id_Tasa']) and strpos($message, $myrow['taxcatname']) === false) {
				$success = false;
				$message .= "No se encuentra configurado la categoria de impuesto ".$myrow['taxcatname'].". Configuracion en funcion 0099 - Categorias De Impuestos<br>";
			}
			if (empty($myrow['c_Regimen']) and strpos($message, 'Tipo de Persona') === false) {
				$success = false;
				$message .= "No se encuentra configurado el Tipo de Persona de ".$myrow['legalname']." rfc: ".$myrow['taxid'].". Configuracion en funcion 0168 - Abc Razones Sociales<br>";
			}

		}
	}else{
		//No es posible verificar Regimen Fiscal
		$success = false;
		$message .= "No se obtuvo informacion de facturacion (Razon Social, Unidad de Medida y Categoria). <br>";
	}

	//Tipo de comprobante
	if (empty($TipoComprobante)) {
		$success = false;
		$message .= "No se encontro informacion del tipo de comprobante. <br>";
	}

	//Uso de CFDI
	if (empty($UsoCfdi)) {
		$success = false;
		$message .= "No se encontro informacion del uso del CDFI. <br>";
	}

	//Metodo de Pago
	if (empty($c_paymentid)) {
		$success = false;
		$message .= "No se encontro informacion del metodo de pago. <br>";
	}

	//Validar total con clave
	/*$FacMax = '20000000';
	if ($total > $FacMax and empty($ClaveFactura)) {
		$success = false;
		$message .= "El total de la factura es mas del permitido por el SAT, solicitar la clave al PAC y agregarla para poder facturar. <br>";
	}

	if (!empty($ClaveFactura) and $total <= $FacMax) {
		$success = false;
		$message .= "El total no requiere de una clave de confirmacion. <br>";
	}*/

	$response['success'] = $success;
	$response['message'] = $message;

	return $response;
}

/**
 * Función para reemplazar caracteres especiales del xml
 * @param  [type] $xml Xml de factura
 * @return [type]      Xml
 */
function caracteresEspecialesFactura($xml){
	//Minusculas
	$xml = str_replace("&#xE1;", "á", $xml);
	$xml = str_replace("&#xE9;", "é", $xml);
	$xml = str_replace("&#xED;", "í", $xml);
	$xml = str_replace("&#xF3;", "ó", $xml);
	$xml = str_replace("&#xFA;", "ú", $xml);
	//Mayusculas
	$xml = str_replace("&#xC1;", "Á", $xml);
	$xml = str_replace("&#xC9;", "É", $xml);
	$xml = str_replace("&#xCD;", "Í", $xml);
	$xml = str_replace("&#xD3;", "Ó", $xml);
	$xml = str_replace("&#xDA;", "Ú", $xml);
	//Letra ñ Ñ
	$xml = str_replace("&#xF1;", "ñ", $xml);
	$xml = str_replace("&#xD1;", "Ñ", $xml);
	$xml = str_replace("Ã", "Ñ", $xml);
	//Comilla "
	$xml = str_replace("&amp;quot;", "&quot;", $xml);

	return $xml;
}

/**
 * Función para reemplazar caracteres especiales del xml
 * @param  [type] $xml Xml de factura
 * @return [type]      Xml
 */
function caracteresEspecialesFacturaAntes($xml){
    //Minusculas
    $xml = str_replace("á", "&#xE1;", $xml);
    $xml = str_replace("é", "&#xE9;", $xml);
    $xml = str_replace("í", "&#xED;", $xml);
    $xml = str_replace("ó", "&#xF3;", $xml);
    $xml = str_replace("ú", "&#xFA;", $xml);
    //Mayusculas
    $xml = str_replace("Á", "&#xC1;", $xml);
    $xml = str_replace("É", "&#xC9;", $xml);
    $xml = str_replace("Í", "&#xCD;", $xml);
    $xml = str_replace("Ó", "&#xD3;", $xml);
    $xml = str_replace("Ú", "&#xDA;", $xml);
    //Letra ñ
    $xml = str_replace("ñ", "&#xF1;", $xml);
    $xml = str_replace("Ñ", "&#xD1;", $xml);
    //letra e codificada
    $xml = str_replace("Ã‰", "&#xC9;", $xml);

    return $xml;
}

/**
 * Función para almacenar la cantidad por forma de pago del recibo
 * @param  [type]  $db           Base de datos
 * @param  integer $nu_type      Tipo de documento
 * @param  integer $nu_transno   Número de operación
 * @param  integer $nu_cantidad  Cantida de la forma de pago
 * @param  string  $ln_paymentid Código de la forma de pago
 * @return [type]                [description]
 */
function fnInsertFormaPagoDeb($db, $nu_type = 0, $nu_transno = 0, $nu_cantidad = 0, $ln_paymentid = "") {
	$ISQL = "INSERT INTO tb_debtortrans_forma_pago (
	dtm_fecha,
	sn_userid,
	nu_type,
	nu_transno,
	ln_paymentid,
	nu_cantidad
	)
	VALUES (
	Now(),
	'" . $_SESSION['UserID'] . "',
	'" . $nu_type . "',
	'" . $nu_transno . "',
	'" . $ln_paymentid . "',
	'" . $nu_cantidad . "'
	)";

	$ErrMsg = _ ( 'No se pudo insertar la Transaccion para la forma de pago' );
	$result = DB_query ( $ISQL, $db, $ErrMsg, '', true );
	$idReg = DB_Last_Insert_ID ( $db, 'tb_debtortrans_forma_pago', 'nu_mov' );
	return $idReg;
}

// Función para limpieza de campos
function limpiaEntrada($entrada){
	global $db;

	//$entrada = htmlspecialchars($entrada,ENT_QUOTES);
	$entrada = mysqli_real_escape_string($db,$entrada);
	$entrada = str_replace('\\',"",$entrada);

	return $entrada;
}

// Función para limpieza de arreglos, manda llamar a la función "limpiaEntrada"
function limpiaArreglo($arreglo){
	$nuevoArreglo = false;

	if(isset($arreglo)&&is_array($arreglo)){
		$nuevoArreglo = array();
		foreach($arreglo as $id => $valor){
			$nuevoArreglo[$id] = ( is_array($valor) ? limpiaArreglo($valor) : limpiaEntrada($arreglo[$id]) );
		}
	}

	return $nuevoArreglo;
}

function reestableceArreglos($arreglo){
	if(isset($arreglo)&&is_array($arreglo)){
		foreach($arreglo as $id => $valor){
			if(is_array($valor)){
				reestableceArreglos($valor);
			}else{
				if(substr($valor,0,0)=="["&&substr($valor,strlen($valor)-1,1)=="]"){
				//$valor = str_replace("[","",$valor);
				//$valor = str_replace("]","",$valor);

				$valor = str_replace("\\'","",$valor);
				//$valor = explode(",",$valor);

					$arreglo[$id] = $valor;
				}
			}
		}
	}

	return $arreglo;
}

function VerifyCsrfToken($dbs){

	if(!isset($_SESSION['csrf']['send_message_token'])){
		return false;
	}

	if($_SESSION['csrf']['send_message_token']['token'] == ''){
		return false;
	}

	$SQL = "SELECT `ln_token` FROM `www_users` WHERE `userid` = '$_SESSION[UserID]'";
	$ErrMsg = "No se realizó la consulta";
	$ResultSearch = DB_query($SQL, $dbs, $ErrMsg);

	while ($row_token = DB_fetch_array($ResultSearch)){
		$_token = $row_token['ln_token'];
	}

	if($_SESSION['csrf']['send_message_token']['token']==$_token){
		$SQL = "UPDATE `www_users` SET `dtm_ultimo_acceso_token` = '".date('Y-m-d H:i:s')."' WHERE `userid` = '$_SESSION[UserID]'";
		$ErrMsg = "No se realizó la consulta";
		$ResultSearch = DB_query($SQL, $dbs, $ErrMsg);
		return true;
	}

}

?>