<?php

	function Entradas($legalid,$loccode,$stkid,$cost,$qty,$type,$ref,$db){
		//buscar si existe nivel para el costo que ingresaa
		if ($type=="")
			$type=0;
		
		if ($ref == "")
			$ref = 0;
		
		$qry = "Select nivel,unitcost,quantity,id
				FROM costo_capas
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				and type = '$type'
				and reference = '$ref'
				Order By nivel desc
				limit 1
				";
		$rs = DB_query($qry,$db);
		$crearnivel = true;
		$nivel = 0;
		if (DB_num_rows($rs) > 0){
			$row = DB_fetch_array($rs);
			$unitcost = $row['unitcost'];	
			$nivel = $row['nivel'];
			$id = $row['id'];
			$quantity = $row['quantity'];
			
			if ($quantity > 0 and $unitcost == $cost)
				$crearnivel = false;
		}
		
		$qry="";
		if ($crearnivel){
			$nivel++;
			
			$qry = "INSERT INTO costo_capas (nivel,legalid,stockid,begin_date,unitcost,quantity,type,reference)
					VALUES ('$nivel','$legalid','$stkid',current_date,'$cost','$qty','$type','$ref')";
			
			
		}
		
		else{
			
			$qry = "UpDate costo_capas
						Set quantity = quantity + $qty,
						last_date = current_date
					WHERE id = '$id'
					";
			
		}
		$r = DB_query($qry,$db);
		
		//actualizar tabla de costos x razon social
		$qry = "Select * FROM stockcostsxlegalnew
				WHERE stockid = '$stkid'
				and legalid = '$legalid'";
		$res = DB_query($qry,$db);
		$sql = "";
		if (DB_num_rows($res) > 0){
			$row = DB_fetch_array($res);
			
			$avgcost = $row['avgcost'];
			
			$avgcostANT = $row['avgcost'];
			// si no viene asociado con una referencia se calcula costo promedio
			if ($ref == 0)
				$avgcost = (($row['avgcost']*$row['qty']) + ($cost*$qty))/($row['qty']+$qty);
			
			if($avgcost==''){
				$avgcost=$avgcostANT;
			}
			
			
			$sql = "Update stockcostsxlegalnew
					Set avgcost = $avgcost,
						lastcost = $cost,
						pepscost = (select sum(unitcost*quantity)/sum(quantity) from costo_capas WHERE stockid = '$stkid' and legalid = '$legalid'),
						highercost = (select max(unitcost) from costo_capas WHERE quantity > 0 and  stockid = '$stkid' and legalid = '$legalid'),
						nivelmax = $nivel,
						qty = qty + $qty,
						nivelmin = (select nivel from costo_capas WHERE quantity > 0 and stockid = '$stkid' and legalid = '$legalid' Order by nivel limit 1),
						lastupdatedate = current_date
						
					WHERE stockid = '$stkid'
					and legalid = '$legalid'";
			
		}
		else{
			$sql = "INSERT INTO stockcostsxlegalnew (legalid,stockid,qty,lastcost,avgcost,pepscost,highercost,trandate,lastupdatedate,nivelmin,nivelmax)
					VALUES ('$legalid','$stkid','$qty','$cost','$cost','$cost','$cost',current_date,current_date,'$nivel','$nivel')
					";
			
		}
		
		$r = DB_query($sql,$db);
		return 1;
		
	}
	
	function EntradasXCancelacion($legalid,$loccode,$stkid,$cost,$qty,$db){
		//buscar primer nivel de ese producto
		$qry = "Select id,unitcost,nivel
				FROM costo_capas
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				Order By nivel
				";
		$rs = DB_query($qry,$db);
		$crearnivel = true;
		$nivel = 0;
		if (DB_num_rows($rs) > 0){
			while ($row = DB_fetch_array($rs)){
				$unitcost = $row['unitcost'];
				$nivel = $row['nivel'];
				$id = $row['id'];
				if ($unitcost == $cost){
					$crearnivel = false;
					break;
				}
			}
		}
			
		$sql="";
		if ($crearnivel){
			//buscar el nivel menor
			$rs = DB_query($qry,$db);
			$row = DB_fetch_array($rs);
			$nivel = $row['nivel'];
			if ($nivel=="")
				$nivel=2;
			
			$nivel--;
			
			$sql = "INSERT INTO costo_capas (nivel,legalid,stockid,begin_date,unitcost,quantity)
					VALUES ('$nivel','$legalid','$stkid',current_date,'$cost',$qty)";
				
			
		}
		else{
			$sql = "UpDate costo_capas
					Set quantity = quantity + $qty ,
						last_date = current_date
					WHERE id = '$id'
					";
				
		}
		$r = DB_query($sql,$db);
		
		//actualizar tabla de costos x razon social
		$qry = "Select * FROM stockcostsxlegalnew
				WHERE stockid = '$stkid'
				and legalid = '$legalid'";
		$res = DB_query($qry,$db);
		$sql = "";
		if (DB_num_rows($res) > 0){
			$row = DB_fetch_array($res);
							
			$avgcost = (($row['avgcost']*$row['qty']) + ($cost*$qty))/($row['qty']+$qty);
							
			$sql = "Update stockcostsxlegalnew
						Set avgcost = $avgcost,
						lastcost = $cost,
						pepscost = (select sum(unitcost*quantity)/sum(quantity) from costo_capas WHERE stockid = '$stkid' and legalid = '$legalid'),
						highercost = (select max(unitcost) from costo_capas WHERE quantity > 0 and  stockid = '$stkid' and legalid = '$legalid'),
						nivelmax = (select nivel from costo_capas WHERE quantity > 0 and stockid = '$stkid' and legalid = '$legalid' Order by nivel desc limit 1),
						qty = qty + $qty,
						nivelmin = (select nivel from costo_capas WHERE quantity > 0 and stockid = '$stkid' and legalid = '$legalid' Order by nivel limit 1),
						lastupdatedate = current_date
					WHERE stockid = '$stkid'
					and legalid = '$legalid'";
							
		}
		else{
			$sql = "INSERT INTO stockcostsxlegalnew (legalid,stockid,qty,lastcost,avgcost,pepscost,highercost,trandate,lastupdatedate,nivelmin,nivelmax)
					VALUES ('$legalid','$stkid','$qty','$cost','$cost','$cost','$cost',current_date,current_date,'$nivel','$nivel')
					";
				
		}
		
		$r = DB_query($sql,$db);
		return 1;
		
	}
	
	function Salidas($legalid,$loccode,$stkid,$qty,$type,$ref,$db){
		//buscar primeros niveles con cantidad > 0
		$qry = "Select nivel,unitcost,quantity,id
				FROM costo_capas
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				and quantity > 0
				Order By nivel
				";
		$rs = DB_query($qry,$db);
		if (DB_num_rows($rs) > 0){
			$totdesc = $qty;
			$cantAdescontar = $qty;
			while ($rows=DB_fetch_array($rs) and $cantAdescontar > 0){
				$quantity = $rows['quantity'];
				$id = $rows['id'];
				if ($cantAdescontar > $quantity)
					$cantAdescontar = $quantity;
				
				$qry = "UpDate costo_capas
						 Set quantity = quantity - $cantAdescontar,
						 last_date = current_date
						WHERE  id = '$id'
						";
				$r = DB_query($qry,$db);
				
				$totdesc -= $cantAdescontar;
				$cantAdescontar = $totdesc;
			}
			
			//actualizar tabla de costos x razon social
			$qry = "Select * FROM stockcostsxlegalnew
					WHERE stockid = '$stkid'
					and legalid = '$legalid'";
			$res = DB_query($qry,$db);
	
			if (DB_num_rows($res) > 0){
				$row = DB_fetch_array($res);
										
				$sql = "Update stockcostsxlegalnew
						Set pepscost = (select sum(unitcost*quantity)/sum(quantity) from costo_capas WHERE stockid = '$stkid' and legalid = '$legalid'),
							highercost = (select max(unitcost) from costo_capas WHERE quantity > 0 and  stockid = '$stkid' and legalid = '$legalid'),
							nivelmax = (select nivel from costo_capas WHERE quantity > 0 and stockid = '$stkid' and legalid = '$legalid' Order by nivel desc limit 1),
							qty = qty - $qty,
							nivelmin = (select nivel from costo_capas WHERE quantity > 0 and stockid = '$stkid' and legalid = '$legalid' Order by nivel limit 1),
							lastupdatedate = current_date
						WHERE stockid = '$stkid'
						and legalid = '$legalid'";

				$r = DB_query($sql,$db);
			}
			
			
			return 1;
		}
		else
			return -1;
	}
	
	function SalidasXCancelacion($legalid,$loccode,$stkid,$cost,$qty,$db){
		if ($type=="")
			$type=0;
		
		if ($ref == "")
			$ref = 0;
		//buscar ultimos niveles con cantidades > 0
		$qry = "Select nivel,unitcost,quantity,id
				FROM costo_capas
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				and unitcost = '$cost'
				and type = '$type'
				and reference = '$ref'
				and quantity > 0
				Order By nivel desc
				";
		
		$rs = DB_query($qry,$db);
		if (DB_num_rows($rs) > 0){
			$totdesc = $qty;
			$cantAdescontar = $qty;
			while ($rows=DB_fetch_array($rs) and $cantAdescontar > 0){
				$quantity = $rows['quantity'];
				$id = $rows['id'];
				if ($cantAdescontar > $quantity)
					$cantAdescontar = $quantity;
			
				$qry = "UpDate costo_capas
						Set quantity = quantity - $cantAdescontar,
							last_date = current_date
						WHERE  id = '$id'
							";
				
				$r = DB_query($qry,$db);
			
				$totdesc -= $cantAdescontar;
				$cantAdescontar = $totdesc;
			}
				
			//actualizar tabla de costos x razon social
			$qry = "Select * FROM stockcostsxlegalnew
					WHERE stockid = '$stkid'
					and legalid = '$legalid'";
			$res = DB_query($qry,$db);
			
			if (DB_num_rows($res) > 0){
				$row = DB_fetch_array($res);
		
				$sql = "Update stockcostsxlegalnew
						Set pepscost = (select sum(unitcost*quantity)/sum(quantity) from costo_capas WHERE stockid = '$stkid' and legalid = '$legalid'),
							highercost = (select max(unitcost) from costo_capas WHERE quantity > 0 and  stockid = '$stkid' and legalid = '$legalid'),
							nivelmax = (select nivel from costo_capas WHERE quantity > 0 and stockid = '$stkid' and legalid = '$legalid' Order by nivel desc limit 1),
							qty = qty - $qty,
							nivelmin = (select nivel from costo_capas WHERE quantity > 0 and stockid = '$stkid' and legalid = '$legalid' Order by nivel limit 1),
							lastupdatedate = current_date
						WHERE stockid = '$stkid'
						and legalid = '$legalid'";
					
				$r = DB_query($sql,$db);
			}
			
				
			return 1;
				
		}
		else 
			return -1;
		
	}
	
	function getAVGCost($legalid,$stkid,$db){
		$qry = "Select avgcost
				FROM stockcostsxlegalnew
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				";
		$rs = DB_query($qry,$db);
		$row = DB_fetch_array($rs);
		
		return $row['avgcost'];
		
	}
	
	function getPEPSCost($legalid,$stkid,$db){
		$qry = "Select pepscost
				FROM stockcostsxlegalnew
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				";
		$rs = DB_query($qry,$db);
		$row = DB_fetch_array($rs);
	
		return $row['pepscost'];
	
	}
	
	function getLastCost($legalid,$stkid,$db){
		$qry = "Select lastcost
				FROM stockcostsxlegalnew
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				";
		$rs = DB_query($qry,$db);
		$row = DB_fetch_array($rs);
	
		return $row['lastcost'];
	
	}
	
	function getHigherCost($legalid,$stkid,$db){
		$qry = "Select highercost
				FROM stockcostsxlegalnew
				WHERE stockid = '$stkid'
				and legalid = '$legalid'
				";
		$rs = DB_query($qry,$db);
		$row = DB_fetch_array($rs);
	
		return $row['highercost'];
	}

?>