<?Php
//
function calculate_cost_FACTORNIVEL($db, $Parent, $Component, $qty, $legalid, $echoOn=0) {
        
        /* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
        $sql = "SELECT bom.component as component,
				stockmaster.description as description,
				workcentres.description as workcenter,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockcostsxlegal.avgcost,
				bom.percent,
				bom.categoryid,
				bom.factorTiempo,
				stockcategory.categorydescription,
                                workcentres.legalid,
				bom.costoUnitario
			FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
				 JOIN stockmaster ON bom.component=stockmaster.stockid
				 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
				 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							AND stockcostsxlegal.legalid = workcentres.legalid
				
			WHERE bom.parent = '$Parent' AND  bom.component = '$Component'";
        $ErrMsg = _('No se puede recuperar la lista de componentes');
        $DbgMsg = _('El SQL es');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
	$unidadesComponente = 'zzzzzz';
        if ($myrow = DB_fetch_array($result)) {
		$unidadesComponente = 	$myrow['units'];
	}
	
	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
        $sql = "SELECT bom.component as component,
				stockmaster.description as description,
				workcentres.description as workcenter,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockcostsxlegal.avgcost,
				bom.percent,
				bom.categoryid,
				bom.factorTiempo,
				stockcategory.categorydescription,
                                workcentres.legalid,
				bom.costoUnitario
			FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
				 JOIN stockmaster ON bom.component=stockmaster.stockid
				 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
				 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							AND stockcostsxlegal.legalid = workcentres.legalid
				
			WHERE bom.parent = '$Parent' AND (bom.percent = 0 OR bom.percent is null OR stockmaster.units < '".$unidadesComponente."')";
        $ErrMsg = _('No se puede recuperar la lista de componentes');
        $DbgMsg = _('El SQL es');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
	//echo '<pre><br><br>sqlfactor:'.$sql."<br>";
	
	$CostoTotalDelEnsamble = 0;
        while ($myrow = DB_fetch_array($result)) {
		if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
			
			/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
			if (!is_numeric($myrow['percent']) OR $myrow['percent'] == 0) {
				$cantidadFactor = $myrow['quantity'];
				$costoFactor = $myrow['costoUnitario'];
				
				if ($cantidadFactor == 0 AND $echoOn == 1)
					prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene factor de cantidad configurado...'),'warn');
				if ($costoFactor == 0 AND $echoOn == 1)
					prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene costo unitario configurado...'),'warn');
					
				if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;	
			} else {
				if ($myrow['categoryid'] == 'All') {
					//echo "calculate_cost_FACTORNIVEL:".$Parent.",".$myrow['component'].",".$myrow['quantity'].":".$myrow['percent']."<br>";
					$costoTodasCat = calculate_cost_FACTORNIVEL($db, $Parent,$myrow['component'],$myrow['quantity'],0);
					//echo "Costo ALL1:".$costoTodasCat."<br>";
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoTodasCat*$myrow['percent'];
				} else {
					//echo "entroCostoXCAT:".$myrow['categoryid'].":".strlen($myrow['categoryid'])."<br>";
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + calculate_cost_CAT($db, $myrow['categoryid'],$Parent,$myrow['component'],$myrow['quantity'],0)*$myrow['percent'];
				}
			}
			
		} else {
			
			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockcostsxlegal.avgcost,
						bom.percent,
						bom.categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid
						
					WHERE bom.parent = '".$myrow['component']."'";
					
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
			//echo $sql;
			
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				
				while ($row_C = DB_fetch_array($result_C)) {
					if ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D') {
						/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
						$cantidadFactor = $row_C['quantity']*$myrow['quantity'];
						$costoFactor = $row_C['costoUnitario'];
						
						if ($cantidadFactor == 0 AND $echoOn == 1)
							prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene factor de cantidad configurado...'),'warn');
						if ($costoFactor == 0 AND $echoOn == 1)
							prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene costo unitario configurado...'),'warn');
							
						if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
							$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;
						
					} else {
						//echo "entro:calculate_cost(".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")<br>";
						$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + calculate_cost($db, $myrow['component'], $row_C['component'], ($row_C['quantity']), $row_C['legalid'])*$myrow['quantity'];	
					}
				}
				
			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}
		}
		
	}
	//echo "+salio:catid:".$categoryid."/".$Parent."/".$Component."/(".$qty.")/".$legalid."==".$CostoTotalDelEnsamble."<br>";
	return $CostoTotalDelEnsamble;
}

function calculate_cost_CAT($db, $categoryid, $Parent, $Component, $qty, $legalid, $echoOn=0) {
        
	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
        $sql = "SELECT bom.component as component,
				stockmaster.description as description,
				workcentres.description as workcenter,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockcostsxlegal.avgcost,
				bom.percent,
				bom.categoryid,
				bom.factorTiempo,
				stockcategory.categorydescription,
                                workcentres.legalid,
				bom.costoUnitario
			FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
				 JOIN stockmaster ON bom.component=stockmaster.stockid
				 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
				 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							AND stockcostsxlegal.legalid = workcentres.legalid
				
			WHERE bom.parent = '$Parent' AND stockmaster.categoryid = '$categoryid'";
        $ErrMsg = _('No se puede recuperar la lista de componentes');
        $DbgMsg = _('El SQL es');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
	//echo '<pre>sql comp:'.$sql."<br>";
	
	$CostoTotalDelEnsamble = 0;
        while ($myrow = DB_fetch_array($result)) {
		if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
			
			/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
			$cantidadFactor = $myrow['quantity'];
			$costoFactor = $myrow['costoUnitario'];
			
			if ($cantidadFactor == 0 AND $echoOn == 1)
				prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene factor de cantidad configurado...'),'warn');
			if ($costoFactor == 0 AND $echoOn == 1)
				prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene costo unitario configurado...'),'warn');
				
			if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
				$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;
			
		} else {
			
			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockcostsxlegal.avgcost,
						bom.percent,
						bom.categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid
						
					WHERE bom.parent = '".$myrow['component']."'";
					
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
			//echo $sql;
			
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				
				while ($row_C = DB_fetch_array($result_C)) {
					if ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D') {
						/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
						$cantidadFactor = $row_C['quantity']*$myrow['quantity'];
						$costoFactor = $row_C['costoUnitario'];
						
						if ($cantidadFactor == 0 AND $echoOn == 1)
							prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene factor de cantidad configurado...'),'warn');
						if ($costoFactor == 0 AND $echoOn == 1)
							prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene costo unitario configurado...'),'warn');
							
						if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
							$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;
						
					} else {
						//echo "entro:calculate_cost(".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")<br>";
						$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + calculate_cost($db, $myrow['component'], $row_C['component'], ($row_C['quantity']), $row_C['legalid'])*$myrow['quantity'];	
					}
				}
				
			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}
		}
		
	}
	//echo "+salio:catid:".$categoryid."/".$Parent."/".$Component."/(".$qty.")/".$legalid."==".$CostoTotalDelEnsamble."<br>";
	return $CostoTotalDelEnsamble;
}

function calculate_cost($db, $Parent, $Component, $qty, $legalid, $echoOn=0) {
        
        //echo "+entro:".$Parent."/".$Component."/(".$qty.")/".$legalid."<br>";
        
	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
        $sql = "SELECT bom.component as component,
				stockmaster.description as description,
				workcentres.description as workcenter,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockcostsxlegal.avgcost,
				bom.percent,
				IFNULL(bom.categoryid,'') as categoryid,
				bom.factorTiempo,
				stockcategory.categorydescription,
                                workcentres.legalid,
				bom.costoUnitario
			FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
				 JOIN stockmaster ON bom.component=stockmaster.stockid
				 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
				 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							AND stockcostsxlegal.legalid = workcentres.legalid
				
			WHERE bom.parent = '$Parent' AND (bom.component = '$Component' OR '$Component'='')";
        $ErrMsg = _('No se puede recuperar la lista de componentes');
        $DbgMsg = _('El SQL es');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	

	
	$CostoTotalDelEnsamble = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
		//	echo 'SQL:'.$sql."<br>";
			/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
			
			if (!is_numeric($myrow['percent']) OR $myrow['percent'] == 0) {
				$cantidadFactor = $myrow['quantity'];
				$costoFactor = $myrow['costoUnitario'];
				
				if ($cantidadFactor == 0 AND $echoOn == 1)
					prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene factor de cantidad configurado...'),'warn');
				if ($costoFactor == 0 AND $echoOn == 1)
					prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene costo unitario configurado...'),'warn');
					
				//echo "(".$myrow['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+";
				
				if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;
					
				//echo $cantidadFactor*$costoFactor."=".$CostoTotalDelEnsamble."<br>";
			} else {
				//echo "(".$myrow['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+";
				
				if ($myrow['categoryid'] == 'All') {
					//echo "calculate_cost_FACTORNIVEL:".$Parent.",".$myrow['component'].",".$myrow['quantity'].":".$myrow['percent']."<br>";
					$costoTodasCat = calculate_cost_FACTORNIVEL($db, $Parent,$myrow['component'],$myrow['quantity'],0);
					//echo "Costo ALL1:".$costoTodasCat."<br>";
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoTodasCat*$myrow['percent'];
					
					//echo $costoTodasCat*$myrow['percent']."=".$CostoTotalDelEnsamble."<br>";
				} else {
					//echo "entroCostoXCAT:".$myrow['categoryid'].":".strlen($myrow['categoryid'])."<br>";
					$costoEstaCat = calculate_cost_CAT($db, $myrow['categoryid'],$Parent,$myrow['component'],$myrow['quantity'],0);
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoEstaCat*$myrow['percent'];
					//echo $costoEstaCat*$myrow['percent']."=".$CostoTotalDelEnsamble."<br>";
				}
			}
			
		} else {
			
			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.categoryid,
						stockcostsxlegal.avgcost,
						bom.percent,
						IFNULL(bom.categoryid,'') as categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid
						
					WHERE bom.parent = '".$myrow['component']."'";
					
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
			//echo $sql;
			
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				
				while ($row_C = DB_fetch_array($result_C)) {
					if ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D') {
						/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
						
						if (!is_numeric($row_C['percent']) OR $row_C['percent'] == 0) {
							$cantidadFactor = $row_C['quantity']*$myrow['quantity'];
							$costoFactor = $row_C['costoUnitario'];
							
							if ($cantidadFactor == 0 AND $echoOn == 1)
								prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene factor de cantidad configurado...'),'warn');
							if ($costoFactor == 0 AND $echoOn == 1)
								prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene costo unitario configurado...'),'warn');
								
							//echo "D(".$row_C['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+";
							
							if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
								$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;
							
							//echo $cantidadFactor*$costoFactor."=".$CostoTotalDelEnsamble."<br>";
							
						} else {
							//echo "D(".$row_C['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+";
							
							if ($row_C['categoryid'] == 'All') {
								//echo "entro:calculate_cost_FACTORNIVEL(".$row_C['categoryid'].",".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")*".$row_C['percent'].":".strlen($row_C['categoryid'])."<br>";
								$costoTodasCat = calculate_cost_FACTORNIVEL($db, $myrow['component'],$row_C['component'],$row_C['quantity'],0);
								//echo "COSTO ALL:".$costoTodasCat."<br>";
								$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoTodasCat*$row_C['percent']*$myrow['quantity'];
								
								//echo $costoTodasCat*$row_C['percent']*$myrow['quantity']."=".$CostoTotalDelEnsamble."<br>";
							} else {
								//echo "entro:calculate_cost_CAT(".$row_C['categoryid'].",".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")*".$row_C['percent'].":".strlen($row_C['categoryid'])."<br>";
								$costoEstaCatego = calculate_cost_CAT($db, $row_C['categoryid'],$myrow['component'],$row_C['component'],$row_C['quantity'],0);
								$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoEstaCatego*$row_C['percent']*$myrow['quantity'];
								//echo $costoEstaCatego*$row_C['percent']*$myrow['quantity']."=".$CostoTotalDelEnsamble."<br>";
							}
						}
						
					} else {
						//echo "entro:calculate_costDENTRO(".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")<br>";
						//echo "D(".$row_C['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+";
						
						$costoEsteSubConcepto = calculate_cost($db, $myrow['component'], $row_C['component'], $row_C['quantity'], $row_C['legalid']);
						
						$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoEsteSubConcepto*$myrow['quantity'];
						
						//echo $costoEsteSubConcepto."=".$CostoTotalDelEnsamble."<br>";
						
						
					}
				}
				
			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}
		}
		
	}
	
	//echo "SALIENDO:".$Parent.",".$Component."=".$CostoTotalDelEnsamble."<br>";
	return $CostoTotalDelEnsamble;
	
}

// funcion para calcular costo de unificacion mano de obra

function calculate_cost_UpdateMO($db, $Parent, $Component, $qty, $legalid, $echoOn=0,$categoryid,$CostoT) {
        
       // echo "<br>+entro:".$Parent."/".$Component."/(".$qty.")/".$CostoT."<br>";
        if($qty==''){
        	$qty=1;
        }
        $CostoTotalDelEnsamble=$CostoT;
        
	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
        $sql = "SELECT bom.component as component,
				stockmaster.description as description,
				workcentres.description as workcenter,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockcostsxlegal.avgcost,
				bom.percent,
				IFNULL(bom.categoryid,'') as categoryid,
				bom.factorTiempo,
				stockcategory.categorydescription,
                                workcentres.legalid,
				bom.costoUnitario,
				stockmaster.categoryid as catprod
			FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
				 JOIN stockmaster ON bom.component=stockmaster.stockid
				 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
				 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							AND stockcostsxlegal.legalid = workcentres.legalid
				
			WHERE bom.parent = '$Parent' AND (bom.component = '$Component' OR '$Component'='')";
        $ErrMsg = _('No se puede recuperar la lista de componentes');
        $DbgMsg = _('El SQL es');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
       // echo '<pre><br>sql:'.$sql;
	
	//$CostoTotalDelEnsamble = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
		//	echo 'SQL:'.$sql."<br>";
			/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
			if($myrow['catprod']==$categoryid /*or $myrow['categoryid']==$categoryid*/ ){
				if (!is_numeric($myrow['percent']) OR $myrow['percent'] == 0) {
					$cantidadFactor = $myrow['quantity'];
					$costoFactor = $myrow['costoUnitario'];
					
					if ($cantidadFactor == 0 AND $echoOn == 1)
						prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene factor de cantidad configurado...'),'warn');
					if ($costoFactor == 0 AND $echoOn == 1)
						prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene costo unitario configurado...'),'warn');
						
					//echo "(".$myrow['component']."):CostoTotalDelEnsamble 1=".$CostoTotalDelEnsamble."+";
					
					if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
						$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;
						
					//echo '<br>costo dos:'.$cantidadFactor*$costoFactor."=".$CostoTotalDelEnsamble."<br>";
				} else {
					//echo "(".$myrow['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+";
					
					if ($myrow['categoryid'] == 'All') {
						//echo "calculate_cost_FACTORNIVEL:".$Parent.",".$myrow['component'].",".$myrow['quantity'].":".$myrow['percent']."<br>";
						$costoTodasCat = calculate_cost_FACTORNIVEL($db, $Parent,$myrow['component'],$myrow['quantity'],0);
						//echo "Costo ALL1:".$costoTodasCat."<br>";
						$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoTodasCat*$myrow['percent'];
						
						echo $costoTodasCat*$myrow['percent']."=".$CostoTotalDelEnsamble."<br>";
					} else {
						//echo "entroCostoXCAT:".$myrow['categoryid'].":".strlen($myrow['categoryid'])."<br>";
						$costoEstaCat = calculate_cost_CAT($db, $myrow['categoryid'],$Parent,$myrow['component'],$myrow['quantity'],0);
						
						$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + (($costoEstaCat*$row_C['percent'])*($myrow['quantity']));
						//$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoEstaCat*$myrow['percent'];
						echo $costoEstaCat*$myrow['percent']."=".$CostoTotalDelEnsamble."<br>";
					}
				}
			}
			
		} else {
			
			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.categoryid,
						stockcostsxlegal.avgcost,
						bom.percent,
						IFNULL(bom.categoryid,'') as categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario,
					 stockmaster.categoryid as catprod
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid
						
					WHERE bom.parent = '".$myrow['component']."'";
					
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
			//echo '<pre><br>sql2:'.$sql;
			
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				
				while ($row_C = DB_fetch_array($result_C)) {
					if ( ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D' )) {
						/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
						if($row_C['catprod']==$categoryid){
							if (!is_numeric($row_C['percent']) OR $row_C['percent'] == 0) {
								$cantidadFactor = $row_C['quantity']*$myrow['quantity']*$qty;
								$costoFactor = $row_C['costoUnitario'];
								
								if ($cantidadFactor == 0 AND $echoOn == 1)
									prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene factor de cantidad configurado...'),'warn');
								if ($costoFactor == 0 AND $echoOn == 1)
									prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene costo unitario configurado...'),'warn');
									
								//echo "D(".$row_C['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+ cantidad:".$qty;
								//echo "<br>AAA:".$cantidadFactor.'costoensamble:'.$CostoTotalDelEnsamble."costo factor:".$costoFactor."cantodad FActor:".$row_C['quantity'].' SSS:'.$myrow['quantity'].'AC:'.$qty;
								
								if (is_numeric($cantidadFactor) AND is_numeric($costoFactor))
									$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cantidadFactor*$costoFactor;
								
								//echo '<bR>Despues:'.($cantidadFactor*$costoFactor)."=".$CostoTotalDelEnsamble."XXXXXXXXXX<br>";
								
							} else {
								//echo "D(".$row_C['component']."):CostoTotalDelEnsamble=".$CostoTotalDelEnsamble."+";
								
								if ($row_C['categoryid'] == 'All') {
									//echo "entro:calculate_cost_FACTORNIVEL(".$row_C['categoryid'].",".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")*".$row_C['percent'].":".strlen($row_C['categoryid'])."<br>";
									$costoTodasCat = calculate_cost_FACTORNIVEL($db, $myrow['component'],$row_C['component'],$row_C['quantity'],0);
									//echo "COSTO ALL:".$costoTodasCat."<br>";
									$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $costoTodasCat*$row_C['percent']*$myrow['quantity'];
									
									//echo $costoTodasCat*$row_C['percent']*$myrow['quantity']."=".$CostoTotalDelEnsamble."<br>";
								} else {
									//echo "entro:calculate_cost_CAT(".$row_C['categoryid'].",".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")*".$row_C['percent'].":".strlen($row_C['categoryid'])."<br>";
									$cantidadFactor = $myrow['quantity']*$qty;
									$costoEstaCatego = calculate_cost_CAT($db, $row_C['categoryid'],$myrow['component'],$row_C['component'],1,0);
									$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + (($costoEstaCatego*$cantidadFactor)*$row_C['percent']);
									//echo 'costo cat:'.$costoEstaCatego.'sss'.$myrow['quantity'].'XXX'.$qty.'ZZZ'.$cantidadFactor."=".$CostoTotalDelEnsamble."<br>";
								}
							}
						}
						
					} else {
					//	echo "<br>entro:calculate_costDENTRO(".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")<br>";
						//                         
						//echo "<br>D(".$row_C['component']."):CostoTotalDelEnsamble Acumulado=".$CostoT."+";
						$CostoT=$CostoTotalDelEnsamble;
						$costoEsteSubConcepto = calculate_cost_UpdateMO($db, $myrow['component'], $row_C['component'], $myrow['quantity']*$qty, $row_C['legalid'],'',$categoryid,$CostoT);
						
						// echo "<br>D(".$row_C['component']."):CostoTotalDelEnsamble subconcepto=".$costoEsteSubConcepto."+";
						
						$CostoTotalDelEnsamble = $costoEsteSubConcepto;//$CostoTotalDelEnsamble + $costoEsteSubConcepto;//*($myrow['quantity']);
						
						//echo '<br>costo subconcepto:'.$costoEsteSubConcepto."=".$CostoTotalDelEnsamble;
						
						
					}
				}
				
			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}
		}
		
	}
	return $CostoTotalDelEnsamble;
	
}

// Funcion para autoemision en recepcion de compras




/*****************************************/
// Funcion para unificacion de manufactura

function UpdatBomMO($db,$ubom, $masterparentid,$Parent,$Component,$Categoryid) {

	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
	$sql = "SELECT bom.component as component,
	stockmaster.description as description,
	workcentres.description as workcenter,
	bom.quantity,
	bom.effectiveafter,
	bom.effectiveto,
	stockmaster.mbflag,
	bom.autoissue,
	stockmaster.controlled,
	stockmaster.decimalplaces,
	stockmaster.units,
	stockcostsxlegal.avgcost,
	bom.percent,
	IFNULL(bom.categoryid,'') as categoryid,
	bom.factorTiempo,
	stockcategory.categorydescription,
	workcentres.legalid,
	bom.costoUnitario,
	bom.u_bom,
	stockmaster.categoryid AS Catprod
	FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
	JOIN stockmaster ON bom.component=stockmaster.stockid
	LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
	LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
	AND stockcostsxlegal.legalid = workcentres.legalid
	
	WHERE 
	 bom.parent = '$Parent' /* AND (bom.component = '$Component' OR '$Component'='')*/";
// echo '<pre>sql uno delete:'.$sql;
	$ErrMsg = _('No se puede recuperar la lista de componentes');
	$DbgMsg = _('El SQL es');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$CostoTotalDelEnsamble = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
		//	echo '<br>catbom:'.$myrow['categoryid']. 'cat: '.$Categoryid.'cat prod:'.$myrow['Catprod'];
			if($myrow['categoryid'] !=$Categoryid){
				UpdateBom($db,$myrow['u_bom'],$Parent,$myrow['component'],$masterparentid,$Categoryid,$myrow['mbflag'],$myrow['Catprod']);
			}else{
				
				UpdateBom($db,$myrow['u_bom'],$Parent,$myrow['component'],$masterparentid,$Categoryid,$myrow['mbflag'],$myrow['categoryid']);
				
			}
				
		} else {
				
			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.categoryid AS Catprod,
						stockcostsxlegal.avgcost,
						bom.percent,
						IFNULL(bom.categoryid,'') as categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid
	
					WHERE  bom.parent = '".$myrow['component']."'";
			//echo '<br><pre>sql dos delete:'.$sql;	
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				while ($row_C = DB_fetch_array($result_C)) {
					if ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D') {
						
						UpdateBom($db,$myrow['u_bom'],$myrow['component'],$row_C['component'],$masterparentid,$Categoryid,$row_C['mbflag'],$row_C['Catprod']);
					} else {
						UpdatBomMO($db,$ubom,$masterparentid,$myrow['component'],$row_C['component'],$Categoryid);
					}
				}
	
			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}// fin de manufacturado
		}
	
	}// fin de actualizacion
	
}
/*************************************/
/* Actualizacion de tabla de bom *****/
/*************************************/

function UpdateBom($db,$ubom,$ParentID,$Component,$MasterID,$Categoryid,$mbflag,$catprod){
	global $topproduct;
	if($mbflag!='M'){
		if($catprod==$Categoryid){
			$sql="DELETE from  bom where parent='".$ParentID. "' AND component='".$Component."'";
		//	echo $sql;
			$ErrMsg = _('No se pudo eliminar este paquete');
			$DbgMsg = _('El SQL es');
			$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg);
		}
	}else{
	/*
		$sql="";
		$qry = "SELECT quantity, costoUnitario
				FROM bom
				WHERE parent='$MasterID'
				AND component='$Component'";
		echo '<pre><br>'.$qry;
		$r = DB_query($qry,$db);
		if (DB_num_rows($r)==0){
			$SQL="INSERT bom(parent,component,workcentreadded,loccode,effectiveafter,effectiveto,quantity,autoissue,categoryid,percent,factortiempo,costoUnitario,rendimiento)
				  		  SELECT '".$MasterID."',component,workcentreadded,loccode,effectiveafter,effectiveto,quantity,autoissue,bom.categoryid,percent,factortiempo,costoUnitario,rendimiento
				          FROM bom inner join stockmaster on stockmaster.stockid=bom.component and stockmaster.categoryid='".$Categoryid."'
				  		  WHERE parent='".$ParentID."' AND component='".$Component."'";
			//echo '<br>'.$SQL;
			$Result = DB_query($SQL,$db);
	
		}*/
		// ELIMINO DE TABLA LOS COMPONENTES DE MANO DE OBRA
		
		//Recorre productos manufacturados y elimina de tabla de bom
		$SQL="SELECT *
			  FROM bom inner join stockmaster on stockmaster.stockid=bom.component 
				and (stockmaster.categoryid='".$Categoryid."' or bom.categoryid='".$Categoryid."')
			  WHERE parent='".$ParentID."' AND component='".$Component."'";
	//	echo '<pre>sql2:'.$SQL;
		$result_C = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($result_C) > 0) {
			/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
			while ($row_C = DB_fetch_array($result_C)) {
				//echo '<pre>sql2:'.$SQL;
				$sql="DELETE FROM bom WHERE u_bom=".$row_C[0];
				$ErrMsg = _('No se pudo eliminar este paquete');
				$DbgMsg = _('El SQL es');
				$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg);
			}
		}
		// borro el componente manufacturado si es que ya no tiene productos asociados
		
		$SQL="SELECT *
			  FROM bom inner join stockmaster on stockmaster.stockid=bom.component
				and (stockmaster.categoryid='".$Categoryid."' or bom.categoryid='".$Categoryid."')
			  WHERE parent='".$ParentID."' AND component='".$Component."'";
		//	echo '<pre>sql2:'.$SQL;
		$result_C = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($result_C) == 0) {
			/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
			
			$SQL="DELETE FROM bom WHERE parent='".$ParentID."' AND component='".$Component."'";
			$ErrMsg = _('No se pudo eliminar este paquete');
			$DbgMsg = _('El SQL es');
			$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg);
		}
		
		
	}
	

}
/*****************************************/
// Actualizacion de bom para autoemision
/*****************************************/

function UpdatBomFlagAutoemision($db, $masterparentid,$Parent,$Component) {
//echo '<br>master:'.$masterparentid;
	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
	$sql = "SELECT bom.component as component,
	stockmaster.description as description,
	workcentres.description as workcenter,
	bom.quantity,
	bom.effectiveafter,
	bom.effectiveto,
	stockmaster.mbflag,
	bom.autoissue,
	stockmaster.controlled,
	stockmaster.decimalplaces,
	stockmaster.units,
	stockcostsxlegal.avgcost,
	bom.percent,
	IFNULL(bom.categoryid,'') as categoryid,
	bom.factorTiempo,
	stockcategory.categorydescription,
	workcentres.legalid,
	bom.costoUnitario,
	bom.u_bom,
	stockmaster.categoryid AS Catprod
	FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
	JOIN stockmaster ON bom.component=stockmaster.stockid
	LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
	LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
	AND stockcostsxlegal.legalid = workcentres.legalid

	WHERE
	bom.parent = '$Parent' /* AND (bom.component = '$Component' OR '$Component'='')*/";
	//echo '<pre>sql:'.$sql;
	$ErrMsg = _('No se puede recuperar la lista de componentes');
	$DbgMsg = _('El SQL es');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$CostoTotalDelEnsamble = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
			if($masterparentid==$myrow['component']){	
				$SQL="UPDATE bom
			  		   SET flagautoemision=1
			  		   WHERE u_bom='".$myrow['u_bom']."'";
			//	echo '<pre>sql:'.$SQL;
				$res = DB_query($SQL,$db);
			}
		} else {

			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.categoryid,
						stockcostsxlegal.avgcost,
						bom.percent,
						IFNULL(bom.categoryid,'') as categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario,
					bom.u_bom
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid

					WHERE  bom.parent = '".$myrow['component']."'";
			//echo '<br><pre>'.$sql;
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				while ($row_C = DB_fetch_array($result_C)) {
					if ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D') {
						if($masterparentid==$row_C['component']){
							$SQL="UPDATE bom
			  		   			  SET flagautoemision=1
			  		   			  WHERE u_bom='".$row_C['u_bom']."'";
							$res = DB_query($SQL,$db);
						}
					} else {
						UpdatBomFlagAutoemision($db, $masterparentid,$myrow['component'],$row_C['component']);
					}
				}

			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}// fin de manufacturado
		}
	}// fin de actualizacion

}




// FUNCIONES PARA EL INSERT en worequirements
global $topproduct;

function insertRequirements($db,$wo, $Parent, $Component, $qty, $legalid, $echoOn=0,$masterparentid,$stockidnivel='',$noemision=0,$stocknivelAnt='',$nonivel,$ArrayWO,$bomcategory=0) {
	global $topproduct;

	if ($Component==""){
		$topproduct = $Parent;
	}
	
	if ($stockidnivel==""){
		$stockidnivel = $Parent;
	}
	
	if ($nonivel==""){
		$nonivel = 0;
	}
	//echo '<br><br>nivel entraaa:'. $Parent.'cantidad:'.$qty;
	//echo '<br>master:'.$masterparentid;
	
	
	/*************************************************/
	// INSERTA EN TABLA DE CONTROL DE COMPRAS
	/*************************************************/
	// Valida si ya se dio de alta el producto en tabla
	$qrybom = "SELECT *
					FROM bom_workorders
					WHERE wo = '".$wo."'
						AND parent='".$Parent."'";
	//echo '<pre><br>'.$qrybom;
	$resbom = DB_query($qrybom,$db);
	if (DB_num_rows($resbom)==0){
		// Inserta en tabla de compras para ot
		$SQL="INSERT bom_workorders(wo,parent,component,workcentreadded,loccode,effectiveafter,effectiveto,quantity,autoissue,categoryid,percent,factortiempo,costoUnitario,rendimiento,flagautoemision)
			  		  SELECT ".$wo." ,parent,component,workcentreadded,loccode,effectiveafter,effectiveto,quantity*".$qty.",autoissue,categoryid,percent,factortiempo,costoUnitario,rendimiento,flagautoemision
			          FROM bom
			  		  WHERE parent='".$Parent."'";
		$Result = DB_query($SQL,$db);
	}
	//Reinicio de numero de nivel para compras
	//	echo '<br>no emision:'.$noemision;
	if (in_array($Parent,$ArrayWO)){
		$noemision=1;
		
	}
		//echo '<br>no emision dos:'.$stocknivelAnt;
	
	
	
	
	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
        $sql = "SELECT bom.component as component,
				stockmaster.description as description,
				workcentres.description as workcenter,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
        		bom.bom_category_id,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockcostsxlegal.avgcost,
				bom.percent,
				IFNULL(bom.categoryid,'') as categoryid,
				bom.factorTiempo,
				stockcategory.categorydescription,
                                workcentres.legalid,
				bom.costoUnitario,
				bom.flagautoemision,
        		".$nonivel." as numnivel
			FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
				 JOIN stockmaster ON bom.component=stockmaster.stockid
				 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
				 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
							AND stockcostsxlegal.legalid = workcentres.legalid
				
			WHERE bom.parent = '$Parent' AND (bom.component = '$Component' OR '$Component'='')";
        
		//
		
		//echo '<pre>sql1:'.$sql."<br>No emision:".$noemision."Stockid nivel ant:".$stocknivelAnt.'cat:'.$bomcategory;
	
	$ErrMsg = _('No se puede recuperar la lista de componentes');
    $DbgMsg = _('El SQL es');
    $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	 $CostoTotalDelEnsamble = 0;
     while ($myrow = DB_fetch_array($result)) {
     	if($myrow['bom_category_id']==0){
     		//$myrow['bom_category_id']=$bomcategory;
     	}else{
     		$bomcategory=$myrow['bom_category_id'];
     	}
       	if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
       		
			//echo '<br>Padre:'.$Parent.'   Hijo:'.$myrow['component'].'   No emision:'.$noemision;
			/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
			if (!is_numeric($myrow['percent']) OR $myrow['percent'] == 0) {
				$cantidadFactor = $myrow['quantity'];
				$costoFactor = $myrow['costoUnitario'];
				if ($cantidadFactor == 0 AND $echoOn == 1)
					prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene factor de cantidad configurado...'),'warn');
				if ($costoFactor == 0 AND $echoOn == 1)
					prnMsg (_('componente')."(".$myrow['component'].")"._(' de la parte ')."(".$Parent.")"._('no tiene costo unitario configurado...'),'warn');
					
				if (is_numeric($cantidadFactor) AND is_numeric($costoFactor)){
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + ($cantidadFactor*$costoFactor);
					//echo "<pre>insertReq Comp: ".$myrow['component']. "   Parent: ".$Parent."  Cant: ".$cantidadFactor."   Cost: ".$costoFactor;
					//Agregue insert multiplicado por cantidad
					$cantidadFactor=$cantidadFactor*$qty;
					// inserta registro en workorderreq
					insertReq($db,$wo,$myrow['component'],$Parent,$cantidadFactor,$costoFactor,'',$masterparentid,$stockidnivel,$myrow['flagautoemision'],$noemision+1,$stocknivelAnt,$bomcategory);
					
				}

			} else {
				
				if ($myrow['categoryid'] == 'All') {
					//echo "<pre>calculate_cost_FACTORNIVEL:".$Parent.",".$myrow['component'].",".$myrow['quantity'].":".$myrow['percent']."<br>";
					$costoTodasCat = calculate_cost_FACTORNIVEL($db, $Parent,$myrow['component'],$myrow['quantity'],0);
					$costocat1=$costoTodasCat;
					//echo "Costo ALL1:".$costoTodasCat."<br>";
					$cost = $costoTodasCat*$myrow['percent'];
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cost;
					//echo "<pre>insertReq Comp: ".$myrow['component']. "   Parent: ".$Parent."  Cant: ".$myrow['percent']."   Cost: ".$costoTodasCat;
					$myrow['percent']=$myrow['percent']*$qty;
					// inserta registro en workorderreq
					
					//echo "Costo ALL2:".$costocat1.'->CostoTotalDelEnsamble'.$CostoTotalDelEnsamble."<br>";
					insertReq($db,$wo,$myrow['component'],$Parent,$myrow['percent'],$costocat1,1,$masterparentid,$stockidnivel,$myrow['flagautoemision'],$noemision+1,$stocknivelAnt,$bomcategory);
					//echo "Costo ALL3:".$myrow['percent'];
						
				} else {
					//echo "<pre>entroCostoXCAT:".$myrow['categoryid'].":".strlen($myrow['categoryid'])."<br>";
					$cost = calculate_cost_CAT($db, $myrow['categoryid'],$Parent,$myrow['component'],$myrow['quantity'],0);					
					$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + ($cost*$myrow['percent']);
					//echo "<pre>insertReq Comp: ".$myrow['component']. "   Parent: ".$Parent."  Cant: ".$myrow['percent']."   Cost: ".$cost;
					$myrow['percent']=$myrow['percent']*$qty;
					
					insertReq($db,$wo,$myrow['component'],$Parent,$myrow['percent'],$cost,1,$masterparentid,$stockidnivel,$myrow['flagautoemision'],$noemision+1,$stocknivelAnt,$bomcategory);
					
					
				}
			}
			
		} else {
			
			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						bom.bom_category_id,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.categoryid,
						stockcostsxlegal.avgcost,
						bom.percent,
						IFNULL(bom.categoryid,'') as categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario,
						flagautoemision
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid
						
					WHERE bom.parent = '".$myrow['component']."'";
					
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			
			if (in_array($myrow['component'],$ArrayWO)){
				$noemision=1;
				//$pos=array_search($Parent,$ArrayWO);
				//echo '<br>posicion:'.$pos.' :'.$Parent;
				$stocknivelAnt=$myrow['component'];
			}
			
		//	echo '<pre>sql2:'.$sql."<br>No emision:".$noemision.'nivel dos:'.$stocknivelAnt;
			
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				
				$noemision=$noemision+1;
				while ($row_C = DB_fetch_array($result_C)) {
					/*$bomcategory
					if($row_C['bom_category_id']==0){
						
						if($myrow['bom_category_id']==0){
							$row_C['bom_category_id']=$bomcategory;
						}else{
							$row_C['bom_category_id']=$myrow['bom_category_id'];
						}
					}*/
					
					/*************************************************/
					// INSERTA EN TABLA DE CONTROL DE COMPRAS
					/*************************************************/
					// Valida si ya se dio de alta el producto en tabla
					$qrybom = "SELECT *
								   FROM bom_workorders
								   WHERE wo = '".$wo."'
										AND parent='". $myrow['component']."'";
					$resbom = DB_query($qrybom,$db);
					//echo '<pre><br>'.$qrybom;
					if (DB_num_rows($resbom)==0){
					// Inserta en tabla de compras para ot
						$SQL="INSERT bom_workorders(wo,parent,component,workcentreadded,loccode,effectiveafter,effectiveto,quantity,autoissue,categoryid,percent,factortiempo,costoUnitario,rendimiento)
			  		 			  SELECT ".$wo." ,parent,component,workcentreadded,loccode,effectiveafter,effectiveto,quantity*".$qty.",autoissue,categoryid,percent,factortiempo,costoUnitario,rendimiento
			          			  FROM bom
			  		  			  WHERE parent='". $myrow['component']."'";
					    $Result = DB_query($SQL,$db);
					}
					
					if ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D') {
						//echo 'entra compra';
						/* COMPONENTE NO ES ENSAMBLADO NI FABRICADO, ENTONCES SOLO CALCULA COSTO */
						
						if (!is_numeric($row_C['percent']) OR $row_C['percent'] == 0) {
							$cantidadFactor = $row_C['quantity']*$myrow['quantity']*$qty;
							$costoFactor = $row_C['costoUnitario'];
							
							if ($cantidadFactor == 0 AND $echoOn == 1)
								prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene factor de cantidad configurado...'),'warn');
							if ($costoFactor == 0 AND $echoOn == 1)
								prnMsg (_('componente')."(".$row_C['component'].")"._(' de la parte ')."(".$myrow['component'].")"._('no tiene costo unitario configurado...'),'warn');
								
							if (is_numeric($cantidadFactor) AND is_numeric($costoFactor)){
								$cost = $cantidadFactor*$costoFactor;
								$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cost;
							  //  echo "<pre>insertReq Comp uno: ".$row_C['component']. "   Parent: ".$myrow['component']."  Cant: ".$cantidadFactor."   Cost: ".$costoFactor;
								insertReq($db,$wo,$row_C['component'],$myrow['component'],$cantidadFactor,$costoFactor,'',$masterparentid,$myrow['component'],$myrow['flagautoemision'],$noemision,$stocknivelAnt,$bomcategory);
								
							}
						} else {
							
							if ($row_C['categoryid'] == 'All') {
								//echo "<pre>calculate_cost_FACTORNIVEL(".$row_C['categoryid'].",".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")*".$row_C['percent'].":".strlen($row_C['categoryid'])."<br>";
								$costoTodasCat = calculate_cost_FACTORNIVEL($db,$myrow['component'],$row_C['component'],$row_C['quantity'],0);
								$costocat1=$costoTodasCat;
								//echo "COSTO ALL:".$costoTodasCat."<br>";
								$cost = $costoTodasCat*$row_C['percent']*$myrow['quantity']*$qty;
								$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cost;
								$row_C['percent']=($row_C['percent']*$myrow['quantity'])*$qty;
								//echo "<pre>insertReq Comp dos: ".$row_C['component']. "   Parent: ".$myrow['component']."  Cant: ".($row_C['percent'])."   Cost: ".$costoTodasCat;
								insertReq($db,$wo,$row_C['component'],$myrow['component'],($row_C['percent']),$costocat1,1,$masterparentid,$myrow['component'],$myrow['flagautoemision'],$noemision,$stocknivelAnt,$bomcategory);
								//insertReq($db,$wo,$row_C['component'],$myrow['component'],($row_C['percent']*$myrow['quantity']),$costoTodasCat,1);
									
							} else {
								//echo "<pre>calculate_cost_CAT(".$row_C['categoryid'].",".$myrow['component'].",".$row_C['component'].",".($row_C['quantity']*$myrow['quantity']).",".$row_C['legalid'].")*".$row_C['percent'].":".strlen($row_C['categoryid'])."<br>";
								$cost = calculate_cost_CAT($db, $row_C['categoryid'],$myrow['component'],$row_C['component'],$row_C['quantity'],0);
								//echo '<br>costo perc:'.$cost.'canthijo:'.$row_C['quantity'].'percent:'.$row_C['percent'].' cantidad padre'.$myrow['quantity'].'cantidadini:'.$qty;
								//echo '<br>costo antes:'.$CostoTotalDelEnsamble;
								$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + (($cost*$row_C['percent'])*($myrow['quantity']*$qty));
								//echo '<br>costo despues:'.$CostoTotalDelEnsamble;
								//echo "<pre>insertReq Comp tres : ".$row_C['component']. "   Parent: ".$myrow['component']."  Cant: ".($row_C['percent']*$myrow['quantity'])."   Cost: ".$cost;
								insertReq($db,$wo,$row_C['component'],$myrow['component'],($row_C['percent']*($myrow['quantity']*$qty)),$cost,1,$masterparentid,$myrow['component'],$myrow['flagautoemision'],$noemision,$stocknivelAnt,$bomcategory);
								//insertReq($db,$wo,$row_C['component'],$myrow['component'],($row_C['percent']*$myrow['quantity']),$cost,1);
							}
						}
						
					} else {
						//$noemision=$noemision+1;
						//echo '<br>cant3:'.$row_C['quantity'].'cant2:'.$myrow['quantity'].' cant uno:'.$qty;
						
						$cost = insertRequirements($db,$wo, $row_C['component'],'', ($row_C['quantity']*$myrow['quantity']*$qty), $row_C['legalid'],0,$masterparentid,$myrow['component'],$noemision,$stocknivelAnt,0,$ArrayWO,$bomcategory);
						      //  insertRequirements($db,$wo, $Parent, $Component, $qty, $legalid, $echoOn=0,$masterparentid) 						
						//$cost = insertRequirements($db,$wo, $row['component'],$row_C['component'], ($row_C['quantity']*$myrow['quantity']), $row_C['legalid']);
						$CostoTotalDelEnsamble = $CostoTotalDelEnsamble + $cost;	
					}
				}
				
				$noemision=$noemision-1;
			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}
		}
		
	}// fin de alta de workorderreq
	
	
	
	
	return $CostoTotalDelEnsamble;
}

function insertReq($db,$wo,$stkid,$ParentID,$Qty,$cost,$esporc=0,$masterparentid='',$stockidnivel='',$flagautoemision=0,$noemision=0,$stocknivelAnt='', $bomCategory=''){
	global $topproduct;
	
	
	$qry = "Select autoissue 
			from bom 
			where component='".$stkid."' 
				and parent = '".$topproduct."' limit 1";
	$r = DB_query($qry, $db);
	$rows = DB_fetch_array($r);
	$autoissue = $rows['autoissue'];
	if(empty($autoissue) == true){
		$autoissue = 0;
	} 
	
	if ($ParentID=="")
		$ParentID = $stkid;
	
	
	//echo "<pre>Qty: $Qty	Cost: $cost";
	
	$sql="";
/*	$qry = "Select * FROM worequirements
			WHERE wo = '$wo'
			and parentstockid='$topproduct'
			and stockid='$stkid'
			";*/
	
	$qry = "Select * FROM worequirements
	WHERE wo = '$wo'
	and masterparentid='$masterparentid'
	and stockid='$stkid'
	and bom_category_id='$bomCategory'
	";
	
	//echo '<br>sql:'.$qry;
	$r = DB_query($qry,$db);
	if (DB_num_rows($r)==0){		
		//echo '<br>qty1:'.$Qty.'->cost:'.$cost;
		if ($esporc){
			$cost = $Qty*$cost;
			$Qty = 1;
		}
		//echo '<br>qty:'.$Qty.'->cost:'.$cost;
		
		$sql = "INSERT INTO worequirements (wo,
					parentstockid,
					stockid,
					qtypu,
					stdcost,
					autoissue,
					directparentid,
					masterparentid,
					ispercent,
					flagautoemision,
					bom_category_id
				) VALUES ( 
					'".$wo."',
					'".$topproduct."',
					'".$stkid."',
					".$Qty.",
					'".$cost."',
					'".$autoissue."',
					'".$ParentID."',
					'".$masterparentid."',
					'".$esporc."',
					'".$flagautoemision."',
					'" . $bomCategory . "'
				)";
	//	echo '<br><pre>qty:'.$sql.'<br>cost:'.$cost;
		$r = DB_query($sql,$db);
		
	
	}
	else{
		
		if ($esporc){
			
			$cost = $Qty*$cost;
			
			$sql = "UpDate worequirements
				Set stdcost = stdcost + ".$cost."
				WHERE wo = ".$wo."
				and masterparentid = '".$masterparentid."'
				and bom_category_id = '".$bomCategory."'
				and stockid = '".$stkid."'
				" ;
			$r = DB_query($sql,$db);
		}
		else{
			//echo '<br>cantidad:'.$Qty.'<br>costo:'.$cost;
			$sql = "UpDate worequirements
					Set stdcost = ((qtypu*stdcost) + (".($Qty*$cost)."))/(qtypu + ".$Qty.")
				WHERE wo = ".$wo."
				and masterparentid = '".$masterparentid."'
				and bom_category_id = '".$bomCategory."'
				and stockid = '".$stkid."'
				" ;
			$r = DB_query($sql,$db);
			
			
			$sql = "UpDate worequirements
					Set qtypu = qtypu + ".$Qty."
				WHERE wo = ".$wo."
				and masterparentid = '".$masterparentid."'
				and bom_category_id = '".$bomCategory."'
				and stockid = '".$stkid."'
				" ;
			$r = DB_query($sql,$db);
					
		}
	
		
	}
	
	//echo "<pre> <BR>REQ:".$sql;
	//$r = DB_query($sql,$db);
	
	// se agrega tabla para explosion de compras
	
 	if($noemision>=3){
		$stockidnivel=$stocknivelAnt;
		//echo "<pre>Parent:". $topproduct."	stockid:". $stkid.'No emision:'.$noemision.'stocknivel:'.$stockidnivel.'Anterior:'.$stocknivelAnt;
		
	}
	$sql="";
	$qry = "Select * FROM woreq_purchorders
	WHERE wo = '$wo'
	and parentstockid='$topproduct'
	and stockidnivel='$stockidnivel'
	and stockid='$stkid'
	";
	$r = DB_query($qry,$db);
	if (DB_num_rows($r)==0){
	
	if ($esporc){
	$cost = $Qty*$cost;
	$Qty = 1;
	}
	
	$sql = "INSERT INTO woreq_purchorders (wo,
					parentstockid,
					stockid,
					qtypu,
					stdcost,
					autoissue,
					directparentid,
					masterparentid,
					stockidnivel,
					ispercent,
					flagautoemision)
				VALUES ( '".$wo."',
						'".$topproduct."',
						'".$stkid."',
						".$Qty.",
						'".$cost."',
						(Select autoissue from bom where component='".$stkid."' limit 1),
						'".$ParentID."',
						'".$masterparentid."',
						'".$stockidnivel."',
						'".$esporc."',
						'".$flagautoemision."'
				 )";
	}else{
	
		if ($esporc){
				
			$cost = $Qty*$cost;
				
			$sql = "UpDate woreq_purchorders
				Set stdcost = stdcost + ".$cost."
				WHERE wo = ".$wo."
				and parentstockid = '".$topproduct."'
				and stockid = '".$stkid."'
				" ;
	
		}
		else{
			$sql = "UpDate woreq_purchorders
				Set stdcost = ((qtypu*stdcost) + (".($Qty*$cost)."))/(qtypu + ".$Qty."),
					qtypu = qtypu + ".$Qty."
			
				WHERE wo = ".$wo."
				and parentstockid = '".$topproduct."'
				and stockid = '".$stkid."'
				" ;
	
		}
	
	
	}
	$r = DB_query($sql,$db);

}

// agrega funcion para ot manuales

function makeWorkOrderManual($db,$arritems,$location,$startdate,$requiredat,$wo=0){
		
	$Result = DB_Txn_Begin($db);
	$u_user = "";
	if ($_SESSION['DefaultUserForWorkOrder']!="")
		$u_user=$_SESSION['DefaultUserForWorkOrder'];
	//elimino datos de wo_req
	$sql="DELETE FROM worequirements WHERE wo='".$wo."'";
	$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
	$sql="DELETE FROM woreq_purchorders WHERE wo='".$wo."'";
	$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
	$sql="select * 
		  from workorders inner join woitems on woitems.wo=workorders.wo
			AND workorders.wo=".$wo;
	$ErrMsg = _('No se puede recuperar la lista de componentes');
	$DbgMsg = _('El SQL es');
	$resultx = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$CostoTotalDelEnsamble = 0;
	while ($myrow = DB_fetch_array($resultx)) {
		//insertRequirements($db,$wo,$stkid,'',$EOQ,0,0,$stkid);
		$noemision=1;
		//Trae arreglo de productos unicial
		unset($Arrayitems);
		$Arrayitems=TraePrincipalesWO($db,$myrow['stockid'],'');
		//var_dump($Arrayitems);
		
		//function TraePrincipalesWO($db,$Parent,$Component){
		$noemision=0;
		insertRequirements($db,$wo,$myrow['stockid'],'',$myrow['qtyreqd'],0,0,$myrow['stockid'],'',$noemision,'',1,$Arrayitems);
	}
	$Result = DB_Txn_Commit($db);
}

function UpdateWorkOrderQty($db,$wo=0){

	
	
	$sql="select *
		  from workorders inner join woitems on woitems.wo=workorders.wo
			AND workorders.wo=".$wo;
	$ErrMsg = _('No se puede recuperar la lista de componentes');
	$DbgMsg = _('El SQL es');
	$resultx = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$CostoTotalDelEnsamble = 0;
	while ($myrow = DB_fetch_array($resultx)) {
		$Qty=$myrow['qtyreqd'];
		$product=$myrow['stockid'];
		$sql = "UPDATE worequirements
				Set qtypu = qtypu / ".$Qty."
				WHERE wo = ".$wo."
				and masterparentid = '".$product."'" ;
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
		$sql = "UPDATE woitems,(
					select  (sum(qtypu*stdcost)*".$Qty.") as costo,wo as ot,masterparentid
					from worequirements
					where  wo = ".$wo."
					and masterparentid = '".$product."'
				) as x
				Set stdcost = x.costo
				WHERE ot=wo and masterparentid=stockid 
					AND wo = ".$wo."
					AND stockid = '".$product."'" ;
		echo $sql;
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	}
	
	
	
}


/***************************************************************/
// FUNCION PARA REEMPLAZAR CODIGOS DE MANUFACTURA
/***************************************************************/

function ReplaceBOMMO($db,$ubom, $masterparentid,$Parent,$Component,$Categoryid,$codigoReplace) {

	/* OBTIENE DATOS DE COMBINACION DE PADRE Y COMPONENTE */
	$sql = "SELECT bom.component as component,
	stockmaster.description as description,
	workcentres.description as workcenter,
	bom.quantity,
	bom.effectiveafter,
	bom.effectiveto,
	stockmaster.mbflag,
	bom.autoissue,
	stockmaster.controlled,
	stockmaster.decimalplaces,
	stockmaster.units,
	stockcostsxlegal.avgcost,
	bom.percent,
	IFNULL(bom.categoryid,'') as categoryid,
	bom.factorTiempo,
	stockcategory.categorydescription,
	workcentres.legalid,
	bom.costoUnitario,
	bom.u_bom,
	stockmaster.categoryid AS Catprod
	FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
	JOIN stockmaster ON bom.component=stockmaster.stockid
	LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
	LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
	AND stockcostsxlegal.legalid = workcentres.legalid

	WHERE
	bom.parent = '$Parent' /* AND (bom.component = '$Component' OR '$Component'='')*/";
	// echo '<pre>sql uno delete:'.$sql;
	$ErrMsg = _('No se puede recuperar la lista de componentes');
	$DbgMsg = _('El SQL es');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$CostoTotalDelEnsamble = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['mbflag']=='B' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='D') {
			//	echo '<br>catbom:'.$myrow['categoryid']. 'cat: '.$Categoryid.'cat prod:'.$myrow['Catprod'];
			if($myrow['Catprod'] ==$Categoryid){
				ReplaceUpdateBom($db,$myrow['u_bom'],$Parent,$myrow['component'],$masterparentid,$Categoryid,$myrow['mbflag'],$myrow['Catprod'],$codigoReplace);
			}

		} else {

			/* AHORA VERIFICA SI COMPONENTE ES FABRICADO Y TIENE COMPONENTES */
			$sql = "SELECT bom.component,
						stockmaster.description as description,
						workcentres.description as workcenter,
						bom.component,
						bom.quantity,
						bom.effectiveafter,
						bom.effectiveto,
						stockmaster.mbflag,
						bom.autoissue,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.categoryid AS Catprod,
						stockcostsxlegal.avgcost,
						bom.percent,
						IFNULL(bom.categoryid,'') as categoryid,
						bom.factorTiempo,
						stockcategory.categorydescription,
						workcentres.legalid,
						bom.costoUnitario,bom.u_bom
					FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
						 JOIN stockmaster ON bom.component=stockmaster.stockid
						 LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
						 LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
									AND stockcostsxlegal.legalid = workcentres.legalid

					WHERE  bom.parent = '".$myrow['component']."'";
			//echo '<br><pre>sql dos delete:'.$sql;
			$ErrMsg = _('No se puede recuperar la lista de componentes');
			$DbgMsg = _('El SQL es');
			$result_C = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($result_C) > 0) {
				/* PRODUCTO SI TIENE COMPONENTES ....  COMO ES DEBIDO.... */
				while ($row_C = DB_fetch_array($result_C)) {
					if ($row_C['mbflag']=='B' OR $row_C['mbflag']=='K' OR $row_C['mbflag']=='D') {
						if($row_C['Catprod'] ==$Categoryid){
							ReplaceUpdateBom($db,$row_C['u_bom'],$myrow['component'],$row_C['component'],$masterparentid,$Categoryid,$row_C['mbflag'],$row_C['Catprod'],$codigoReplace);
						}
					} else {
						//ReplaceBOMMO($db,$ubom, $masterparentid,$Parent,$Component,$Categoryid,$codigoReplace)($db,$ubom,$masterparentid,$myrow['component'],$row_C['component'],$Categoryid,$codigoReplace);
						//echo 'entraaaa';
						ReplaceBOMMO($db,$myrow['u_bom'], $myrow['component'],$myrow['component'],$row_C['component'],$Categoryid,$codigoReplace);
						
						//ReplaceBOMMO($db,$ubom, $masterparentid,$Parent,$Component,$Categoryid,$codigoReplace)
					}
				}

			} else {
				if ($echoOn == 1)
					prnMsg (_('parte fabricada o ensamblada')."(".$myrow['component'].")"._('no tiene componentes...'),'warn');
			}// fin de manufacturado
		}

	}// fin de actualizacion

}
/*********************************/

/*************************************************************/
/* Actualizacion de tabla de BOM para reemplazar claves *****/
/*************************************************************/

function ReplaceUpdateBom($db,$ubom,$ParentID,$Component,$MasterID,$Categoryid,$mbflag,$catprod,$codigoReplace){
	
	$sql="UPDATE bom 
		  SET component='".$codigoReplace."',
		  		flagunifica=1
		  WHERE percent=0 and u_bom=".$ubom;
	//echo '<pre><br>'.$sql.' flag:'.$mbflag;
	$ErrMsg = _('No se pudo eliminar este paquete');
	$DbgMsg = _('El SQL es');
	$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
}
//trae componentes principales para niveles de OC
function TraePrincipalesWO($db,$Parent,$Component){

	$array=array();
	
$sql = "SELECT bom.component as component,
				stockmaster.description as description,
				workcentres.description as workcenter,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockcostsxlegal.avgcost,
				bom.percent,
				IFNULL(bom.categoryid,'') as categoryid,
				bom.factorTiempo,
				stockcategory.categorydescription,
                                workcentres.legalid,
				bom.costoUnitario,
				bom.flagautoemision
			FROM bom JOIN workcentres ON bom.workcentreadded=workcentres.code
        		JOIN stockmaster ON bom.component=stockmaster.stockid
        		LEFT JOIN stockcategory ON bom.categoryid = stockcategory.categoryid
        		LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
        		AND stockcostsxlegal.legalid = workcentres.legalid
        	WHERE bom.parent = '$Parent' AND (bom.component = '$Component' OR '$Component'='')";
$ErrMsg = _('No se puede recuperar la lista de componentes');
$DbgMsg = _('El SQL es');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$Contador = 0;
while ($myrow = DB_fetch_array($result)) {
	$array[$Contador]=$myrow['component'];
	$Contador=$Contador+1;
}

	return $array;

}//

function  ValidaCancelarWO($db,$wo){
	$flagcancelar = 0;
	
	$sql = "SELECT *
			FROM purchorders
			INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno
			WHERE purchorders.wo = '".$wo."'
			AND purchorders.status = 'Authorised'
			AND purchorderdetails.quantityrecd < purchorderdetails.quantityord";
	$result = DB_query($sql, $db);
	if(DB_num_rows($result) > 0){
			
		$flagcancelar = 1;
	
	}
	if($flagcancelar == 0){
		
		$sql = "SELECT *
				FROM stockmoves
				WHERE reference = '".$wo."'
				AND type = 28";
		$ErrMsg = _('No se puede validar la orden de trabajo');
		$DbgMsg = _('El SQL es');
		$result = DB_query($sql, $db,$ErrMsg,$DbgMsg);
		if(DB_num_rows($result) > 0){
			
			$flagcancelar = 3;
		
		}
	}
	
	return $flagcancelar;
}

function CancelarWo($db,$wo){
	
	$result = DB_Txn_Begin($db);
	
	$sql = " SELECT idstatus
			FROM wocontrolpanel_status
			WHERE flagcancelado = 1";
	$Result = DB_query($sql, $db);
	$myrow = DB_fetch_array($Result);
	$idstatus = $myrow['idstatus'];
	
	/* INICIA DESLINDA O.C DE LA O.T. */ 
	$sql = "SELECT distinct purchorders.orderno
			FROM purchorders
			INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno
			WHERE purchorderdetails.quantityord = purchorderdetails.quantityrecd 
				AND purchorders.wo = '".$wo."'";
	$Result = DB_query($sql, $db);
	if(DB_num_rows($Result) > 0){
		while ($myrow = DB_fetch_array($Result)){
			$SQL = "UPDATE purchorders
					SET wo = NULL
					WHERE orderno = '".$myrow['orderno']."'";
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($SQL, $db,$ErrMsg,$DbgMsg);
		}
	}
	
	$sql = "SELECT orderno
			FROM purchorders
			WHERE status in('Delivered', 'Pending', 'Printed')
				AND wo = '".$wo."'";
	$Result = DB_query($sql, $db);
	if(DB_num_rows($Result) > 0){
		while ($myrow = DB_fetch_array($Result)){
			$SQL = "UPDATE purchorders
					SET wo = NULL
					WHERE orderno = '".$myrow['orderno']."'";
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($SQL, $db,$ErrMsg,$DbgMsg);
		}
	}
	/* TERMINA DESLINDA O.C DE LA O.T. */
	
	// Incia actualizacion del locstock para cuando se reversa el autoemitidos */
	$sql = " SELECT  stockmoves.stockid,
					 stockmoves.loccode,
					stockmoves.qty,
					stockmoves.standardcost,
					stockmoves.tagref
			FROM stockmoves
			WHERE type = 28
			AND reference = '".$wo."'";
	//echo '<pre>'.$sql;
	$Result = DB_query($sql, $db);
	$type = 815; // equivalente al tipo 28 pero se genero documento para la cancelacion de los documentos de tipo 28//
	$TransNo = GetNextTransNo($type, $db);
	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$SQLInvoiceDate = Date('Y-m-d');
	if(DB_num_rows($Result) > 0){
		while($myrow = DB_fetch_array($Result)){
			$qty = $myrow['qty'];
			$stockid = $myrow['stockid'];
			$loccode = $myrow['loccode'];
			$tagref = $myrow['tagref'];
			$avgcost =  $myrow['standardcost'];
			$sql = "UPDATE locstock
				SET quantityprod = locstock.quantityprod + " . $qty . "
				WHERE locstock.stockid = '" . $stockid . "'
					AND loccode = '" . $loccode . "'";
			$Result = DB_query($sql, $db,$ErrMsg,$DbgMsg);
		
			$unitsXLegal=StockUnitsXLegal($stockid,$tagref,$db);
			$estavgcostXlegal=StockAvgcostXLegal($stockid,$tagref,$db);
			$lastcostant=StockLastCostXLegal($stockid,$tagref,$db);
			$EstimatedAvgCostXlegal=EstimatedAvgCostXLegal($stockid,$tagref,$unitsXLegal,$estavgcostXlegal,abs($qty),$avgcost,$lastcostant,0, $db);
			$Narrative = "@".$type."@.".$TransNo;
			$rate = 1;
			$montocontable = $qty * $EstimatedAvgCostXlegal;
			$StockGLCode = GetStockGLCode($stockid,$db);
			$SQL = "INSERT INTO stockmoves (stockid,
                                            type,
                                            transno,
                                            loccode,
                                            trandate,
                                            prd,
                                            reference,
                                            price,
                                            qty,
            								tagref,
                                            standardcost,
                                            narrative)
                        VALUES ('" . $stockid . "',
                            '".$type."',
                            " . $TransNo . ",
                            '" . $loccode . "',
                            '" . Date('Y-m-d') . "',
                            " . $PeriodNo . ",
                            '" . $wo . "',
                            " . $EstimatedAvgCostXlegal . ",
                            " . $qty*-1 . ",
                            ". $tagref .",
                            " . $EstimatedAvgCostXlegal . ",
                            '".$Narrative."')";
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($SQL, $db,$ErrMsg,$DbgMsg);
		
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
											WHERE wo='" . $wo."'
													AND stockmaster.stockid = '".$stockid."'";
			$r = DB_query($qry, $db);
			$rows = DB_fetch_array($r);
		
		
			$ISQL = Insert_Gltrans($type,$TransNo,$SQLInvoiceDate,$PeriodNo, $rows['wipact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
					'','',$stockid,$qty,0,$loccode,$EstimatedAvgCostXlegal,"",0, $montocontable,$db,'','INV PROD EN PROCESO',$wo);
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($ISQL, $db,$ErrMsg,$DbgMsg);
		
			$ISQL = Insert_Gltrans($type,$TransNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
					'','',$stockid,$qty,0,$loccode,$EstimatedAvgCostXlegal,"",0, $montocontable*-1,$db,'','INVENTARIOS',$wo);
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($ISQL, $db,$ErrMsg,$DbgMsg);
		
		}
	}
	/* Termina actualizacion del locstock para cuando se reversa el autoemitidos*/
	
	// Incia actualizacion del locstock para cuando se reversa el autoemitidos */
	$sql = " SELECT  stockmoves.stockid,
					 stockmoves.loccode,
					stockmoves.qty,
					stockmoves.standardcost,
					stockmoves.tagref
			FROM stockmoves
			WHERE type = 26
			AND reference = '".$wo."'";
	$Result = DB_query($sql, $db);
	$type = 816; // equivalente al tipo 28 pero se genero documento para la cancelacion de los documentos de tipo 28
	$TransNo = GetNextTransNo($type, $db);
	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$SQLInvoiceDate = Date('Y-m-d');
	if(DB_num_rows($Result) > 0){
		
		while($myrow = DB_fetch_array($Result)){
			$qty = $myrow['qty'];
			$stockid = $myrow['stockid'];
			$loccode = $myrow['loccode'];
			$tagref = $myrow['tagref'];
		
			$tagref = $myrow['tagref'];
			$avgcost =  $myrow['standardcost'];
			$sql = "UPDATE locstock
                SET quantity = locstock.quantity + " . $qty . "
                WHERE locstock.stockid = '" . $stockid . "'
                AND loccode = '" . $loccode . "'";
			$Result = DB_query($sql, $db,$ErrMsg,$DbgMsg);
		
		
			$unitsXLegal=StockUnitsXLegal($stockid,$tagref,$db);
			$estavgcostXlegal=StockAvgcostXLegal($stockid,$tagref,$db);
			$lastcostant=StockLastCostXLegal($stockid,$tagref,$db);
			$EstimatedAvgCostXlegal=EstimatedAvgCostXLegal($stockid,$tagref,$unitsXLegal,$estavgcostXlegal,abs($qty),$avgcost,$lastcostant,0, $db);
			$Narrative = "@".$type."@.".$TransNo;
			$rate = 1;
			$montocontable = $qty * $EstimatedAvgCostXlegal;
			$StockGLCode = GetStockGLCode($stockid,$db);
		
			$SQL = "INSERT INTO stockmoves (stockid,
										type,
										transno,
										loccode,
										trandate,
										price,
										prd,
										reference,
										qty,
										tagref,
										standardcost,
										narrative)
				VALUES ('" . $stockid . "',
						 '".$type."',
                         '" . $TransNo . "',
						'" . $loccode . "',
						'" . Date('Y-m-d') . "',
						'" . $EstimatedAvgCostXlegal . "',
						'" . $PeriodNo . "',
						'" . $wo . "',
						'" . $qty . "',
						'".$tagref."',
						'" .$EstimatedAvgCostXlegal . "',
						'" . $Narrative . "')";
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($SQL, $db,$ErrMsg,$DbgMsg);
			$ISQL = Insert_Gltrans($type,$TransNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['wipact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
					'','',$stockid,$qty,0,$loccode,$EstimatedAvgCostXlegal,"",0, $montocontable*-1,$db,'','INV PROD EN PROCESO',$wo);
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($ISQL, $db,$ErrMsg,$DbgMsg);
		
			$ISQL = Insert_Gltrans($type,$TransNo,$SQLInvoiceDate,$PeriodNo, $StockGLCode['stockact'],$Narrative,$tagref ,$_SESSION['UserID'],$rate,
					'','',$stockid,$qty,0,$loccode,$EstimatedAvgCostXlegal,"",0, $montocontable,$db,'','INVENTARIOS',$wo);
			$ErrMsg = _('No se puede cancelar la orden de trabajo');
			$DbgMsg = _('El SQL es');
			$result = DB_query($ISQL, $db,$ErrMsg,$DbgMsg);
		
		}
	}

	
	/* Termina actualizacion del locstock para cuando se reversa el autoemitidos*/
	
	$sql = "UPDATE workorders
			SET idstatus = '".$idstatus."'
			WHERE wo = '".$wo."'";
	$ErrMsg = _('No se puede cancelar la orden de trabajo');
	$DbgMsg = _('El SQL es');
	$result = DB_query($sql, $db,$ErrMsg,$DbgMsg);
	
	
	$sql = "SELECT workorders.wo,
					workorders.wodescription,
					workorders.idstatus,
					workorders.loccode
			FROM workorders
			WHERE wo = '".$wo."'";
	$Result = DB_query($sql, $db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_array($Result);
	
	$sql = "INSERT INTO wologcancelacion (wo, 
											wodescription,
											idstatus, 
											loccode, 
											userid, 
											fecha)
			VALUE ('".$wo."',
					'".$myrow['wodescription']."',
					'".$myrow['idstatus']."',
					'".$myrow['loccode']."',
					'".$_SESSION['UserID']."',
					Now())";
	$result = DB_query($sql, $db,$ErrMsg,$DbgMsg);//
	
	$result = DB_Txn_Commit($db);
	$flaginserto = 1;
	return $flaginserto;
	
}


/**
 * 
 * @param $ordentrabajo integer numero de la orden de trabajo
 * @return string Mensaje para usuario de la ejecucion de el cierre	
 * 
 */
// Funcion general para cerrar una Orden de Trabajo
function CerrarOrdenTrabajo($wo)
{
	global $db;
	
	// bloque para validar la orden de trabajo antes de poder continuar con el proceso
	$sql = "Select *
			From workorders
			Where wo= '".$wo."'";
	
	$resultado = DB_query($sql, $db);
	
	if (DB_num_rows($resultado) > 0) 
	{
		$registro= DB_fetch_array($resultado);
	
		if ($registro["closed"]== "1"){
			return "La Orden de Trabajo ".$wo." ya esta Cerrada, verifique !!!";
		}
	} else {
		return "La Orden de Trabajo ".$wo." No existe, verifique !!!";
	}
	
	// consultar datos generales de la orden de trabajo
	$sql = "SELECT locations.tagref,
					workorders.loccode, t.legalid
			FROM locations
				INNER JOIN workorders ON locations.loccode = workorders.loccode
				Inner Join tags t on locations.tagref= t.tagref
			WHERE wo = ".$wo;
	
	$rs = DB_query($sql,$db);
	$row = DB_fetch_array($rs);
	
	$tagref= $row[0];
	$loccode= $row[1];
	$legalid= $row[2];
	
	
	// bloque donde se calculan las varianzas de los usado vs lo requerido
	$sqlRequRes="SELECT t.wostdcost,
					t.worequirements_id, t.stockid, t.description,t.units, t.decimalplaces,
					(t.requiredqty) as requiredqty,
					(t.expectedcost) as expectedcost,
					(t.stdcost) AS costperqty,
					t.mbflag,
					t.categoryid,
					t.categorydescription
			FROM (	SELECT
							worequirements.worequirements_id,
							worequirements.stdcost AS wostdcost,
							worequirements.stockid,
							stockmaster.longdescription as description ,
							stockmaster.decimalplaces,
							stockmaster.mbflag,
							stockmaster.units,
							(stockcostsxlegal.avgcost) as stdcost,
							stockmaster.categoryid,
							stockcategory.categorydescription,
							SUM(if(worequirements.ispercent=1,0,worequirements.qtypu)) AS requiredqty,
							SUM(worequirements.stdcost*worequirements.qtypu) AS expectedcost,
							AVG(worequirements.qtypu) as qtypu
					FROM worequirements
						INNER JOIN stockmaster ON worequirements.stockid=stockmaster.stockid
						INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
						INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = ".$legalid."
						INNER JOIN woitems ON woitems.stockid=worequirements.masterparentid
					WHERE worequirements.wo=" . $wo . " and woitems.wo=worequirements.wo
						AND stockmaster.mbflag <> 'M'
						/*AND stockmaster.stockid='DOR1-13'*/
						GROUP BY worequirements.stockid) AS t
				GROUP BY t.stockid
				ORDER BY t.categorydescription";
	
	$RequirementsResult = DB_query($sqlRequRes, $db);
	
	$TotalUsageVar =0;
	$TotalCostVar =0;
	$TotalIssuedCost=0;
	
	while ($RequirementsRow = DB_fetch_array($RequirementsResult))
	{
		$SQL2="SELECT trandate,
			(qty + Case When movs.cantidad Is Null Then 0 Else movs.cantidad End) as qty,
			standardcost
		FROM stockmoves
		Left Join (Select stockid, reference, sum(qty) as cantidad
			From stockmoves
			Where type= 815 Group by stockid, reference) as movs On stockmoves.stockid= movs.stockid and stockmoves.reference= movs.reference
		WHERE stockmoves.type=28
		AND stockmoves.reference = '" . $wo . "'
		AND stockmoves.stockid = '" . $RequirementsRow['stockid'] . "'";
		
		$IssuesResult = DB_query($SQL2,$db,_('Could not retrieve the issues of the item because:'));
		
		$IssueQty =0;
		$IssueCost=0;
		
		if (DB_num_rows($IssuesResult)>0)
		{
			while ($IssuesRow = DB_fetch_array($IssuesResult))
			{
				$IssueQty -= $IssuesRow['qty'];// because qty for the stock movement will be negative
				$IssueCost -= ($IssuesRow['qty']*$IssuesRow['standardcost']);
			}
		}
		
		if ($IssueQty != 0){
			// Modifique la forma de traer el costo por varianza, de acuerdo a documentacion de tarea 24398
			$CostVar= $IssueQty* (($IssueCost/$IssueQty)-($RequirementsRow['expectedcost']/$RequirementsRow['requiredqty']));
		} else {
			$CostVar = 0;
		}
		
		$UsageVar=($IssueQty-$RequirementsRow['requiredqty'])*($RequirementsRow['expectedcost']/$RequirementsRow['requiredqty']);
		
		// Acumular varianzas
		$TotalUsageVar += $UsageVar;
		$TotalCostVar += $CostVar;
		$TotalIssuedCost += $IssueCost;
	}
	
	//Now need to run through the issues to the work order that weren't in the requirements
	$sql = "SELECT stockmoves.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				trandate,
				qty,
				stockmoves.standardcost
		FROM stockmoves INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
		WHERE stockmoves.type=28
			AND reference = " . $wo . "
			AND stockmoves.stockid NOT IN (SELECT worequirements.stockid
											FROM worequirements
											WHERE worequirements.wo=" . $wo . ")";
	
	$WOIssuesResult = DB_query($sql,$db,_('Could not get issues that were not required by the BOM because'));
	
	if (DB_num_rows($WOIssuesResult)>0)
	{
		while ($WOIssuesRow = DB_fetch_array($WOIssuesResult))
		{
			$TotalUsageVar += ($WOIssuesRow['qty']*$WOIssuesRow['standardcost']);
		}
	}
	
	
	// Bloque de codigo donde se ejecuta el cierre de la orden de trabajo
	$SQL= "SELECT woitems.stockid,
			stockmaster.longdescription as description ,
			stockmaster.decimalplaces,
			stockmaster.units,
			stockcostsxlegal.lastcost AS currcost,
			woitems.qtyreqd,
			woitems.qtyrecd,
			woitems.stdcost,
			stockcategory.materialuseagevarac,
			stockcategory.purchpricevaract,
			stockcategory.wipact,
			stockcategory.stockact,
			woitems.numeroDias,
			workorders.orderno
		FROM workorders INNER JOIN woitems ON workorders.wo = woitems.wo
			INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
			INNER JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid AND stockcostsxlegal.legalid = ".$legalid."
			INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
		WHERE woitems.wo=". $wo;
	
	$WOItemsResult = DB_query($SQL,$db,$ErrMsg);
	
	$TotalStdValueRecd =0;
	
	while ($WORow = DB_fetch_array($WOItemsResult)){
		$TotalStdValueRecd += ($WORow['stdcost']*$WORow['qtyrecd']);
	}
	
	DB_data_seek($WOItemsResult, 0);
	$NoItemsOnWO = DB_num_rows($WOItemsResult);
	$TotalVariance = $TotalUsageVar + $TotalCostVar;
	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db, $tagref);
	$WOCloseNo = GetNextTransNo(29, $db);
	
	$TransResult = DB_Txn_Begin($db);  // inicio modulo de transacciones
	
	while ($WORow = DB_fetch_array($WOItemsResult))
	{
		if ($TotalStdValueRecd==0){
			$ShareProportion = 1/$NoItemsOnWO;
		} else {
			$ShareProportion = ($WORow['stdcost']*$WORow['qtyrecd'])/$TotalStdValueRecd;
		}
		
		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $TotalUsageVar!=0)
		{
			$SQL = "INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag)
					VALUES (29,
					" . $WOCloseNo . ",
					'" . Date('Y-m-d') . "',
					" . $PeriodNo . ",
					" . $WORow['materialuseagevarac'] . ",
					'" . $wo . ' - ' . $WORow['stockid'] . ' ' . _('share of usage variance') . "',
					" . ($TotalUsageVar*$ShareProportion) . ",
					".$tagref.")";
	
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the material usage variance could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$TransResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
			$SQL = "INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag)
					VALUES (29,
					" . $WOCloseNo . ",
					'" . Date('Y-m-d') . "',
					" . $PeriodNo . ",
					" . $WORow['wipact'] . ",
					'" . $wo . ' - ' . $WORow['stockid'] . ' ' . _('share of usage variance') . "',
					" . (-$TotalUsageVar*$ShareProportion) . ",
					".$tagref.")";
	
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the WIP side of the usage variance posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$TransResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
			$tipodocto=29;
			// Agrega movimiento en kardex de producto
			$isql = "INSERT INTO stockmoves(stkmoveno,
											stockid,
											type,
											transno,
											loccode,
											trandate,
											debtorno,
											branchcode,
											price,
											prd,
											reference,
											qty,
											discountpercent,
											standardcost,
											show_on_inv_crds,
											newqoh,
											hidemovt,
											narrative,
											warranty,
											tagref,
											discountpercent1,
											discountpercent2,
											totaldescuento,
											avgcost,
											standardcostv2)
									VALUES(NULL,
											'" . $WORow['stockid'] . "',
											'" . $tipodocto . "',
											'" . $WOCloseNo . "',
											'" . $loccode  . "',
											Now(),
											'',
											'',
											'". abs($TotalUsageVar*$ShareProportion) . "',
											'" . $PeriodNo . "',
											'Variacion de uso por cierre de orden de trabajo: ".$WOCloseNo."',
											'0',
											'0',
											'" . abs($TotalUsageVar*$ShareProportion) . "',
											'1',
											'0',
											'0',
											'Variacion de uso por cierre de orden de trabajo: ".$WOCloseNo."',
											'0',
											'" .$tagref . "',
											'0',
											'0',
											'0',
											'0',
											'0')";
			$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
			$DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
			$TransResult = DB_query($isql,$db,$ErrMsg,$DbgMsg,true);
	
		}//end if gl-stock linked and a usage variance exists
	
		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $TotalCostVar!=0){
	
			$SQL = "INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag)
					VALUES (29,
					" . $WOCloseNo . ",
					'" . Date('Y-m-d') . "',
					" . $PeriodNo . ",
					" . $WORow['purchpricevaract'] . ",
					'" . $wo . ' - ' . $WORow['stockid'] . ' ' . _('share of cost variance') . "',
					" . ($TotalCostVar*$ShareProportion) . ",
					".$tagref.")";
	
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the cost variance could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$TransResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
			$SQL = "INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag)
					VALUES (29,
					" . $WOCloseNo . ",
					'" . Date('Y-m-d') . "',
					" . $PeriodNo . ",
					'" . $WORow['wipact'] . "',
					'" . $wo . ' - ' . $WORow['stockid'] . ' ' . _('share of cost variance') . "',
					" . -($TotalCostVar*$ShareProportion) . ",
					".$tagref.")";
	
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the WIP side of the cost variance posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$TransResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
			$tipodocto=29;
			// Agrega movimiento en kardex de producto
			$isql = "INSERT INTO stockmoves(stkmoveno,
											stockid,
											type,
											transno,
											loccode,
											trandate,
											debtorno,
											branchcode,
											price,
											prd,
											reference,
											qty,
											discountpercent,
											standardcost,
											show_on_inv_crds,
											newqoh,
											hidemovt,
											narrative,
											warranty,
											tagref,
											discountpercent1,
											discountpercent2,
											totaldescuento,
											avgcost,
											standardcostv2)
					VALUES(NULL,
							'" . $WORow['stockid'] . "',
							'" . $tipodocto . "',
							'" . $WOCloseNo . "',
							'" . $loccode  . "',
							Now(),
							'',
							'',
							'". abs($TotalCostVar*$ShareProportion) . "',
							'" . $PeriodNo . "',
							'Variacion de costo por cierre de orden de trabajo ".$WOCloseNo."',
							'0',
							'0',
							'" . abs($TotalCostVar*$ShareProportion) . "',
							'1',
							'0',
							'0',
							'Variacion de costo por cierre de orden de trabajo ".$WOCloseNo."','0','" .
								$tagref . "',
							'0',
							'0',
							'0',
							'0',
							'0')";
			
			$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTA ESTE ERROR Y BUSCA SOPORTE TECNICO') . ': ' . _('El registro no pudo ser insertado en la tabla stockmoves debido a ');
			$DbgMsg = _('La siguiente  sentencia SQL fue utilizada para la transaccion..');
			$TransResult = DB_query($isql,$db,$ErrMsg,$DbgMsg,true);
		} //end of if gl-stock integrated and there's a cost variance
		// } //end of standard costing section
		// Linea de arriba comentada ya que no aplicara costo afectaciones de costo promedio en el cierre de la OT
	} // end loop around the items on the work order
	
	$TransResult = DB_query('UPDATE workorders SET closed=1 WHERE wo=' .$wo, $db, _('Could not update the work order to closed because:'),_('The SQL used to close the work order was:'),true);
	$TransResult = DB_query('DELETE FROM woserialnos WHERE wo=' . $wo, $db,_('Could not delete the predefined work order serial numbers'),_('The SQL used to delete the predefined serial numbers was:'),true);
	$TransResult = DB_Txn_Commit($db);
	
	return _('La Orden de Trabajo '.$wo.' ha sido Cerrada');
	
}

// agrego funcion de precio en base a costo
function bomToArray($parent, &$details, $pricelist, $currency, &$db) {

	$sql = "SELECT parent, component FROM bom WHERE parent = '$parent'";
	$rs = DB_query($sql, $db);
	while ($row = DB_fetch_array($rs)) {
		$component = $row['component'];
		$innerSql = "SELECT parent, component FROM bom WHERE parent = '$component'";
		$innerRs = DB_query($innerSql, $db);
		if ($innerRow = DB_fetch_array($innerRs)) {
			bomToArray($component, $details, $pricelist, $currency, $db);
		} else {
				
			$category = "";
			$desc = "";
			$units = "";
			$cost = 0;
			$qty = 0;
			$pricelistcost = 0;
				
			$sqlCat = "SELECT categorydescription, description, units, lastcost
			FROM stockmaster INNER JOIN stockcategory USING(categoryid)
			WHERE stockid = '$component'";
			$rsCat = DB_query($sqlCat, $db);
			if ($rowCat = DB_fetch_array($rsCat)) {
				$category = $rowCat['categorydescription'];
				$desc = $rowCat['description'];
				$units = $rowCat['units'];
			}
			$sqlBom = "SELECT SUM(quantity) AS qty, costoUnitario FROM bom WHERE parent = '$parent' AND component = '$component'";
			$rsBom = DB_query($sqlBom, $db);
			if ($rowBom = DB_fetch_array($rsBom)) {
				$qty = $rowBom['qty'];
				$cost = $rowBom['costoUnitario'];
			}
			$sqlBom = "SELECT price FROM prices WHERE typeabbrev = '$pricelist'
			AND stockid = '$component' AND currabrev = '$currency'";
			$rsBom = DB_query($sqlBom, $db);
			if ($rowBom = DB_fetch_array($rsBom)) {
				$pricelistcost = $rowBom['price'];
			}
			if (!isset($details[$category])) {
				$details[$category] = array();
			}
			if (!isset($details[$category][$component])) {
				$details[$category][$component] = array(
						"code" => $component,
						"description" => strtoupper($desc),
						"units" => strtoupper($units),
						"cost" => $cost,
						"qty" => $qty,
						"pricelistcost" => $pricelistcost
				);
			} else {
				$details[$category][$component]['qty'] += $qty;
			}
		}
	}
}

?>
